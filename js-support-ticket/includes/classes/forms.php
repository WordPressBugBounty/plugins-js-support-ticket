<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path. Any route
 * that reaches this file by a second spelling of the same path would otherwise
 * redeclare the class and take the site down. (Roadmap 4.0-CORE-19)
 *
 * The guard carries a second job since 5.5. This class used to live in the
 * Customer Experience add-on and now lives here, because the ticket form is
 * core's - the add-on's contribution is that a desk may have more than one of
 * them, not a different editor for the one it has. Add-ons are loaded before
 * core, so on a site still running Customer Experience 1.2.0 the add-on's copy
 * is already declared by the time this file is read and this returns, leaving
 * that site exactly as it was. When the add-on drops its copy, this one answers
 * for both. Neither version may declare anything the other does not.
 * (Roadmap 6.5-FORM-02)
 */
if (class_exists('JSSTforms')) {
    return;
}

/**
 * The ticket form: what it asks, in what order, and under what conditions.
 * (Roadmap 5.0-FORM-01)
 *
 * What was here before this class is worth writing down, because it explains
 * every decision below. A form's fields live in `js_ticket_fieldsordering`, one
 * row per field per form, and they were edited across two screens: a list that
 * reorders and toggles, and a separate page for adding a custom field. What
 * that arrangement could not say is anything about a field in relation to
 * another field — and it is the relations that people ask for: ask this only
 * when that was answered, insist on an address that looks like an address, show
 * me what the form actually looks like before I put it in front of a customer.
 *
 * So this class does not replace the table. It reads it as one description and
 * adds the four things it cannot express:
 *
 *  - **Conditions.** "Show Order number when Type of problem is Billing",
 *    stored per form. Enforced in two places on purpose: the browser hides the
 *    field, and the server refuses to insist on a field it has just decided is
 *    hidden. A condition that only hides is a decoration; a condition that only
 *    validates is invisible.
 *  - **Validation.** A pattern and a length per field, checked on the server.
 *    Which turns out to matter more than it sounds: **the required flag on a
 *    custom field has only ever been enforced in the browser.** A form posted
 *    with JavaScript off, or by anything that is not a browser, saves with
 *    every required field empty and nothing says a word.
 *  - **Versions.** Every save snapshots the form first, so the answer to "who
 *    took the phone number off the contact form last Tuesday" is a list rather
 *    than a database restore.
 *  - **What is actually being answered.** How many submissions each field has
 *    an answer in, which is the only honest way to decide whether a question is
 *    worth asking. Read from the tickets themselves — custom answers are JSON
 *    on `js_ticket_tickets.params`, keyed by field name.
 *
 * **Validation runs only for a real form post.** A ticket raised by the REST
 * API, by e-mail piping, by a recurring schedule or by an automation rule is
 * not somebody filling in a form, and refusing those because a form asks for a
 * purchase reference would break every one of them. The test is the form's own
 * `form_request` marker, which only a browser posting the form sends.
 */
class JSSTforms {

    /** Conditions, per form. */
    const OPT_LOGIC = 'jsst_form_logic';

    /** Validation rules, per form and field. */
    const OPT_RULES = 'jsst_form_validation';

    /** `fieldfor` in the table: 1 is the ticket form, 2 is the feedback form. */
    /**
     * This copy understands grouped conditions.
     *
     * A marker rather than a version number, and it earns its keep: the
     * Customer Experience add-on shipped its own copy of this class, add-ons
     * load first, and on a site still running 1.2.0 that copy is the one that
     * answers. It evaluates a single condition per question and knows nothing
     * of `groups`. Anything that writes grouped rules therefore has to ask
     * whether the class that will read them back can, and the migration refuses
     * to run when it cannot - migrating into a shape the running code ignores
     * would turn working rules into silent no-ops. (Roadmap 6.5-FORM-04)
     */
    const LOGIC_GROUPS = 1;

    const FOR_TICKET = 1;
    const FOR_FEEDBACK = 2;

    /* =====================================================================
     * The forms and their fields
     * ================================================================== */

    /** Every form on the site, with what it asks and how much it is used. */
    /**
     * Whether this desk can have more than one form.
     *
     * The register of forms is the Customer Experience add-on's table, and the
     * only thing about forms that is. Every desk has a form - its fields are in
     * `js_ticket_fieldsordering` under a `multiformid`, and core has defaulted
     * that to 1 since long before the add-on existed. So a desk without the
     * add-on is not a desk without a form; it is a desk with exactly one, and
     * the screen should say so rather than refuse to draw. (Roadmap 6.5-FORM-02)
     */
    public static function many() {
        /* Both halves are asked, and the second is the one that matters. A desk
           that deactivates the add-on keeps its table and its rows - nothing is
           dropped - so a table test on its own would go on reporting several
           forms to a screen that has just stopped offering any way to reach
           them. The module's presence is the question; the table is only
           checked because a module can be active before its table is built. */
        if (!in_array('multiform', jssupportticket::$_active_addons)) {
            return false;
        }
        return self::register();
    }

    /** Whether the register of forms has been created yet. */
    private static function register() {
        static $jsst_has = null;
        if ($jsst_has === null) {
            $jsst_table = jssupportticket::$_db->prefix . 'js_ticket_multiform';
            $jsst_has = (jssupportticket::$_db->get_var(
                jssupportticket::$_db->prepare('SHOW TABLES LIKE %s', $jsst_table)) === $jsst_table);
        }
        return $jsst_has;
    }

    /**
     * Which form this desk means when it says "the form".
     *
     * Read from the register where there is one, because a desk that has been
     * running the add-on may have made some other form the default, and its
     * fields and its tickets are under that id. Only a desk that never had the
     * register falls back to 1, which is what core has written into every field
     * row since before forms could be numbered.
     */
    private static function defaultFormId() {
        if (self::register()) {
            $jsst_id = (int) jssupportticket::$_db->get_var(
                'SELECT id FROM `' . jssupportticket::$_db->prefix . 'js_ticket_multiform`
                  WHERE is_default = 1 ORDER BY id ASC LIMIT 1');
            if ($jsst_id > 0) {
                return $jsst_id;
            }
        }
        return 1;
    }

    /**
     * The rows the register holds, or the one form every desk has.
     *
     * The stand-in is shaped like a row of that table and carries the id core
     * has always used, so everything downstream - the field reader, the
     * conditions, the versions - goes on asking the same question of the same
     * column and neither knows nor cares which desk it is on.
     */
    private static function formRows() {
        if (self::many()) {
            return (array) jssupportticket::$_db->get_results(
                'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_multiform` ORDER BY ordering ASC, id ASC', ARRAY_A);
        }
        return array(array(
            'id'          => self::defaultFormId(),
            'title'       => esc_html(__('Ticket form', 'js-support-ticket')),
            'description' => esc_html(__('What a customer is asked when they raise a ticket.', 'js-support-ticket')),
            'is_default'  => 1,
            'published'   => 1,
            'departmentid'=> '',
            'ordering'    => 1,
        ));
    }

    public static function forms() {
        $jsst_rows = self::formRows();
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_id = (int) $jsst_row['id'];
            $jsst_row['fields'] = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_fieldsordering`
                  WHERE multiformid = %d AND fieldfor = %d AND published = 1', $jsst_id, self::FOR_TICKET));
            $jsst_row['tickets'] = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets` WHERE multiformid = %d', $jsst_id));
            $jsst_row['conditions'] = count(self::logic($jsst_id));
            $jsst_out[$jsst_id] = $jsst_row;
        }
        return $jsst_out;
    }

    public static function form($jsst_formid) {
        $jsst_forms = self::forms();
        return isset($jsst_forms[(int) $jsst_formid]) ? $jsst_forms[(int) $jsst_formid] : false;
    }


    /**
     * Questions this site cannot ask, because what answers them is not here.
     *
     * The order and licence-key questions are answered by WooCommerce, Easy
     * Digital Downloads and Envato. The ticket form templates have always
     * checked for those before drawing one - `addticket.php` and
     * `staffaddticket.php` both do, at eight places between them - so on a site
     * without the integration the question simply never appears.
     *
     * The Forms screen did not check, and the screen it replaced did: the old
     * `admin_fieldordering.php` skipped exactly these rows. Losing that was not
     * only untidy. The screen offered a switch for a question that cannot be
     * drawn, and "Needed" beside it - and `validate()` reads the same list, so
     * an administrator who ticked Needed on an order number, on a site with no
     * shop, got a server that refused every ticket for the want of an answer
     * the form had never asked for and could not have shown.
     *
     * Both halves of the check are kept. The add-on being active and the
     * plugin it integrates with being installed are two different facts, and a
     * site can easily have the first without the second - which is exactly the
     * state this was found in. (Roadmap 5.0-FORM-01)
     */
    public static function unavailable($jsst_field) {
        switch ($jsst_field) {
            case 'wcorderid':
            case 'wcproductid':
            case 'wcitemid':
                return (!in_array('woocommerce', (array) jssupportticket::$_active_addons)
                        || !class_exists('WooCommerce'));
            case 'eddorderid':
            case 'eddproductid':
                return (!in_array('easydigitaldownloads', (array) jssupportticket::$_active_addons)
                        || !class_exists('Easy_Digital_Downloads'));
            case 'eddlicensekey':
                return (!in_array('easydigitaldownloads', (array) jssupportticket::$_active_addons)
                        || !class_exists('Easy_Digital_Downloads')
                        || !class_exists('EDD_Software_Licensing'));
            case 'envatopurchasecode':
                return !in_array('envatovalidation', (array) jssupportticket::$_active_addons);
        }
        return false;
    }


    /**
     * Rows that are on the ticket but are not questions the form asks.
     *
     * Status, Assign to and Due date are set on a ticket by whoever is working
     * it - from the ticket screen, from a rule, from automatic assignment -
     * rather than answered by the person raising it. The screen this one
     * replaced left them out of the list for that reason, and they are left out
     * again here at the desk's request.
     *
     * `wcitemid` is in the same list because the old screen had it there. No
     * row of that name exists in `js_ticket_fieldsordering` any more, so the
     * entry costs nothing and means the two lists can still be read against
     * each other.
     *
     * This changes what the Forms screen offers and nothing about the ticket.
     * The agent's form draws these three from
     * `jssupportticket::$jsst_data['fieldordering']` - the fieldordering
     * model's own query, not this list - so Status, Assign to and Due date go
     * on appearing there exactly as before. What goes away is the ability to
     * reorder them or mark them Needed from the Forms screen, which is what
     * was asked for. (Roadmap 5.0-FORM-01)
     */
    public static function notAsked($jsst_field) {
        return in_array($jsst_field, array('status', 'assignto', 'duedate', 'wcitemid'), true);
    }

    /**
     * One form's fields, in order, as one description.
     *
     * Everything a screen needs about a field in one shape, whether it is one
     * of the product's own or one somebody added: what it is called, what kind
     * of control it is, who sees it, whether it is insisted on, and the rules
     * this class keeps beside it. Read straight from the table every time -
     * thirty rows is not worth a cache, and a stale form description is a form
     * that lies.
     */
    public static function fields($jsst_formid, $jsst_fieldfor = self::FOR_TICKET) {
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_fieldsordering`
              WHERE multiformid = %d AND fieldfor = %d ORDER BY ordering ASC, id ASC',
            (int) $jsst_formid, (int) $jsst_fieldfor), ARRAY_A);
        $jsst_rules = self::validation($jsst_formid);
        $jsst_out = array();
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_field = $jsst_row['field'];
            /* Left out of the list entirely rather than shown and disabled: a
               question whose integration is not installed is not a question
               this desk has, and everything downstream of here - the Forms
               screen, validate(), the conditions and the length rules - should
               agree about that. See unavailable(). */
            if (self::unavailable($jsst_field) || self::notAsked($jsst_field)) {
                continue;
            }
            $jsst_out[$jsst_field] = array(
                'id'          => (int) $jsst_row['id'],
                'field'       => $jsst_field,
                'title'       => $jsst_row['fieldtitle'],
                'ordering'    => (int) $jsst_row['ordering'],
                'custom'      => !empty($jsst_row['isuserfield']),
                'type'        => self::typeOf($jsst_row),
                'options'     => self::optionsOf($jsst_row),
                /* The product's rule, not the row's value, for the questions
                   that have one. A consent tick whose `required` has drifted to
                   0 - which the Forms screen used to allow - would otherwise go
                   on being reported as optional here, and validate() reads this
                   array rather than the column, so the form would go on
                   accepting it unticked until somebody happened to save the
                   screen. Saying it here fixes the live form on the next
                   request; saveFields() writes it back on the next save. */
                'required'      => self::alwaysRequired($jsst_field) ? true : !empty($jsst_row['required']),
                'alwaysrequired'=> self::alwaysRequired($jsst_field),
                'published'   => !empty($jsst_row['published']),
                'forvisitors' => !self::staffOnly($jsst_field) && !empty($jsst_row['isvisitorpublished']),
                'adminonly'   => !empty($jsst_row['adminonly']),
                'readonly'    => !empty($jsst_row['readonly']),
                'system'      => !empty($jsst_row['sys']),
                'locked'      => !empty($jsst_row['cannotunpublish']),
                'placeholder' => $jsst_row['placeholder'],
                'description' => $jsst_row['description'],
                'rules'       => isset($jsst_rules[$jsst_field]) ? $jsst_rules[$jsst_field] : self::blankRule(),
            );
        }
        return $jsst_out;
    }

    /**
     * What kind of control a row is.
     *
     * A custom field says so itself in `userfieldtype`. One of the product's
     * own does not say anywhere, because the template knows: `email` is an
     * e-mail box because addticket.php draws one. So the handful that are not
     * plain text are named here, and everything else is text - which is what
     * the template does too.
     */
    public static function typeOf($jsst_row) {
        if (!empty($jsst_row['isuserfield']) && !empty($jsst_row['userfieldtype'])) {
            return $jsst_row['userfieldtype'];
        }
        /* The names here are the ones this table actually uses, which are not
           the ticket table's column names: the field is `department`, the
           column is `departmentid`, and `issuesummary` is the message box. Read
           off the live rows rather than guessed, because a wrong type here
           draws the wrong control in the preview and offers the wrong
           validation on the form editor. */
        $jsst_known = array(
            'email'              => 'email',
            'issuesummary'       => 'textarea',
            'subject'            => 'text',
            'department'         => 'combo',
            'priority'           => 'combo',
            'helptopic'          => 'combo',
            'product'            => 'combo',
            'premade'            => 'combo',
            'users'              => 'combo',
            'assignto'           => 'combo',
            'status'             => 'combo',
            'attachments'        => 'file',
            'captcha'            => 'captcha',
            'duedate'            => 'date',
            'internalnotetitle'  => 'textarea',
        );
        if (isset($jsst_known[$jsst_row['field']])) {
            return $jsst_known[$jsst_row['field']];
        }
        /* The three consent ticks are numbered rather than named. */
        if (strpos($jsst_row['field'], 'termsandconditions') === 0) {
            return 'termsandconditions';
        }
        return 'text';
    }

    /**
     * Questions that may be switched off, but never made optional.
     *
     * The consent ticks, and only those. Unpublish one and it is not asked at
     * all, which is a legitimate thing to want - a desk that does not ask
     * somebody to accept terms is simply a desk that does not ask. But a tick
     * that is shown and may be left unticked is not a weaker version of that,
     * it is a worse one: the form records the customer as having been asked,
     * the ticket is accepted either way, and the agreement the box refers to
     * was never actually given. So the answer is asked-and-required, or not
     * asked.
     *
     * A different rule from `locked` (`cannotunpublish`) on purpose, and the
     * two must not be merged into one "important field" flag. `locked` says a
     * question cannot be switched off and says nothing about whether it may be
     * left blank - the satisfaction rating is locked and is deliberately
     * optional, because a customer may return a survey without scoring it. This
     * says the opposite: switch it off freely, but not halfway.
     *
     * Named by prefix, which is how typeOf() above identifies the same three
     * rows, so there is one definition of what a consent tick is.
     */
    /**
     * Questions only the staff ticket forms draw (wp-admin and the agent desk).
     *
     * A visitor never sees them whatever `isvisitorpublished` says, so that
     * flag is reported and stored as off for them - a ticked "visitors" box on
     * a question no visitor is ever asked is a setting that lies.
     */
    public static function staffOnly($jsst_field) {
        return in_array((string) $jsst_field, array('users', 'premade', 'internalnotetitle', 'assignto', 'duedate', 'status'), true);
    }

    public static function alwaysRequired($jsst_field) {
        return strpos((string) $jsst_field, 'termsandconditions') === 0;
    }

    /** The choices a chooser offers, where the row carries them. */
    public static function optionsOf($jsst_row) {
        if (empty($jsst_row['userfieldparams'])) {
            return array();
        }
        $jsst_params = json_decode($jsst_row['userfieldparams'], true);
        if (!is_array($jsst_params)) {
            return array();
        }
        /* The add-field screen stores its choices under one of two keys
           depending on the plugin's age; both are read rather than one being
           declared correct, because a site upgraded from an old version has
           rows in the old shape and they are still its live form. */
        /* A consent tick has no choices. What it stores under this column is the
           sentence it shows and how that sentence links to the policy, and the
           flat read below was offering the link type - a bare "3" - as though it
           were something a customer could pick. The sentence is the one part of
           it worth showing. */
        if (isset($jsst_params['termsandconditions_text'])) {
            $jsst_text = trim((string) $jsst_params['termsandconditions_text']);
            return ($jsst_text === '') ? array() : array($jsst_text);
        }
        foreach (array('options', 'values', 'fieldvalues') as $jsst_key) {
            if (!empty($jsst_params[$jsst_key]) && is_array($jsst_params[$jsst_key])) {
                return self::flattenOptions($jsst_params[$jsst_key]);
            }
        }
        return self::flattenOptions($jsst_params);
    }

    /**
     * The choices in a stored parameter block, however deeply it nests them.
     *
     * A dependent list does not store a list. It stores the parent's answers
     * as keys and its own choices underneath each one - {"radio1":["radio1
     * child"], ...} - so the flat read this used to do called strval() on an
     * array, which is an "Array to string conversion" warning per choice and
     * the word "Array" in place of every chip on the screen.
     *
     * The leaves are returned, not the keys: the keys belong to the question
     * this one follows, and what a reader wants to know here is what this
     * question can offer. Duplicates are collapsed because two parent answers
     * commonly lead to the same choice, and a chip strip repeating it says
     * nothing. (Roadmap 5.0-FORM-01)
     */
    private static function flattenOptions($jsst_params) {
        $jsst_out = array();
        /* Held in a variable: array_walk_recursive() takes its subject by
           reference, and a cast is not something a reference can be taken of. */
        $jsst_walk = (array) $jsst_params;
        array_walk_recursive($jsst_walk, function ($jsst_value) use (&$jsst_out) {
            if (is_scalar($jsst_value)) {
                $jsst_value = trim((string) $jsst_value);
                if ($jsst_value !== '') {
                    $jsst_out[] = $jsst_value;
                }
            }
        });
        return array_values(array_unique($jsst_out));
    }

    /** The kinds of control, in the words the screens use. */
    public static function types() {
        return array(
            'text'              => __('A line of text', 'js-support-ticket'),
            'textarea'          => __('Several lines', 'js-support-ticket'),
            'email'             => __('An e-mail address', 'js-support-ticket'),
            'combo'             => __('A list to choose from', 'js-support-ticket'),
            'multiple'          => __('A list, more than one allowed', 'js-support-ticket'),
            'radio'             => __('One of a few', 'js-support-ticket'),
            'checkbox'          => __('Ticks', 'js-support-ticket'),
            'date'              => __('A date', 'js-support-ticket'),
            'file'              => __('A file', 'js-support-ticket'),
            'captcha'           => __('A captcha', 'js-support-ticket'),
            'termsandconditions'=> __('A consent tick', 'js-support-ticket'),
            'depandant_field'   => __('A list that depends on another', 'js-support-ticket'),
        );
    }

    public static function typeLabel($jsst_type) {
        $jsst_types = self::types();
        return isset($jsst_types[$jsst_type]) ? $jsst_types[$jsst_type] : $jsst_type;
    }

    /* =====================================================================
     * Editing the shape of a form
     * ================================================================== */

    /**
     * Write the order and the switches for a whole form in one go.
     *
     * One statement per row rather than one clever one, because the rows are
     * dozens rather than thousands and a readable update is worth more than a
     * saved query here. A field the form does not know about is ignored rather
     * than created: this screen changes a form, it does not invent fields -
     * that is what the add-field screen is for.
     */
    public static function saveFields($jsst_formid, $jsst_rows, $jsst_fieldfor = self::FOR_TICKET) {
        $jsst_formid = (int) $jsst_formid;
        $jsst_known = self::fields($jsst_formid, $jsst_fieldfor);
        if (!$jsst_known) {
            return esc_html__('That form has no fields to save.', 'js-support-ticket');
        }
        $jsst_order = 0;
        foreach ((array) $jsst_rows as $jsst_field => $jsst_row) {
            if (!isset($jsst_known[$jsst_field])) {
                continue;
            }
            $jsst_order++;
            $jsst_set = array(
                'ordering'  => $jsst_order,
                'adminonly' => empty($jsst_row['adminonly']) ? 0 : 1,
            );
            /* A field the product marks as un-unpublishable keeps its published
               flags AND its "Needed" whatever the form posts: the ticket form
               without a subject is not a form, and neither is one whose subject
               may be left empty.

               "Needed" was missing from this guard, and that is a regression
               against the screen this one replaces: the Field Ordering screen
               changes `required` with `WHERE id = %d AND cannotunpublish = 0`
               (see modules/fieldordering/model.php), so on the old screen these
               fields could not be made optional at all. Here the checkbox was
               live and the save wrote whatever it posted, so one click made the
               subject of every new ticket optional - and, because `required` is
               also what the server-side validation reads, nothing downstream
               objected. The one flag governs both answers, exactly as it did
               before. */
            if (self::alwaysRequired($jsst_field)) {
                /* Forced rather than merely frozen, and that is the difference
                   between this and `locked`. A consent tick has one correct
                   value for this flag, so a row that already drifted to 0 is
                   repaired by the next save instead of being locked into the
                   wrong answer - which is what freezing it would do, with no
                   way back through the screen. Its published flags are left to
                   the guard below: these may be switched off. */
                $jsst_set['required'] = 1;
            } elseif (empty($jsst_known[$jsst_field]['locked'])) {
                $jsst_set['required'] = empty($jsst_row['required']) ? 0 : 1;
            }
            if (empty($jsst_known[$jsst_field]['locked'])) {
                $jsst_set['published'] = empty($jsst_row['published']) ? 0 : 1;
                $jsst_set['isvisitorpublished'] = (self::staffOnly($jsst_field) || empty($jsst_row['forvisitors'])) ? 0 : 1;
            }
            jssupportticket::$_db->update(
                jssupportticket::$_db->prefix . 'js_ticket_fieldsordering',
                $jsst_set,
                array('id' => (int) $jsst_known[$jsst_field]['id']));
        }
        return true;
    }

    /**
     * Copy the custom fields of one form onto another. (Reusable field schemas)
     *
     * Only the ones somebody added: the product's own fields are already on
     * every form, and copying them would mean two rows for the same question. A
     * field the target form already has under the same name is left alone
     * rather than duplicated or overwritten, because the target's own
     * arrangement is the one somebody made deliberately.
     */
    public static function copyFields($jsst_from, $jsst_to, $jsst_fieldfor = self::FOR_TICKET) {
        $jsst_from = (int) $jsst_from;
        $jsst_to = (int) $jsst_to;
        if ($jsst_from <= 0 || $jsst_to <= 0 || $jsst_from === $jsst_to) {
            return esc_html__('Choose two different forms.', 'js-support-ticket');
        }
        $jsst_source = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_fieldsordering`
              WHERE multiformid = %d AND fieldfor = %d AND isuserfield = 1 ORDER BY ordering ASC',
            $jsst_from, (int) $jsst_fieldfor), ARRAY_A);
        if (!$jsst_source) {
            return esc_html__('That form has no fields of its own to copy — only the ones every form has.', 'js-support-ticket');
        }
        $jsst_existing = self::fields($jsst_to, $jsst_fieldfor);
        $jsst_next = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT MAX(ordering) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_fieldsordering`
              WHERE multiformid = %d AND fieldfor = %d', $jsst_to, (int) $jsst_fieldfor));
        $jsst_copied = 0;
        $jsst_skipped = array();
        foreach ($jsst_source as $jsst_row) {
            if (isset($jsst_existing[$jsst_row['field']])) {
                $jsst_skipped[] = $jsst_row['fieldtitle'];
                continue;
            }
            unset($jsst_row['id']);
            $jsst_row['multiformid'] = $jsst_to;
            $jsst_row['ordering'] = ++$jsst_next;
            jssupportticket::$_db->insert(jssupportticket::$_db->prefix . 'js_ticket_fieldsordering', $jsst_row);
            $jsst_copied++;
        }
        return array('copied' => $jsst_copied, 'skipped' => $jsst_skipped);
    }

    /* =====================================================================
     * Conditions
     * ================================================================== */

    /** The conditions on one form. */
    public static function logic($jsst_formid) {
        $jsst_all = get_option(self::OPT_LOGIC, array());
        $jsst_all = is_array($jsst_all) ? $jsst_all : array();
        return isset($jsst_all[(int) $jsst_formid]) ? (array) $jsst_all[(int) $jsst_formid] : array();
    }

    public static function saveLogic($jsst_formid, $jsst_rules) {
        $jsst_all = get_option(self::OPT_LOGIC, array());
        $jsst_all = is_array($jsst_all) ? $jsst_all : array();
        $jsst_clean = self::cleanLogic($jsst_formid, $jsst_rules);
        if ($jsst_clean) {
            $jsst_all[(int) $jsst_formid] = $jsst_clean;
        } else {
            unset($jsst_all[(int) $jsst_formid]);
        }
        update_option(self::OPT_LOGIC, $jsst_all, false);
        return $jsst_clean;
    }

    /**
     * A condition is: show THIS field when THAT field answers so.
     *
     * Deliberately one condition per field rather than a tree. Every support
     * form I have looked at says "ask for the order number when it is about
     * billing", and an editor that can express three levels of nesting is an
     * editor nobody finishes filling in. A field with two conditions on it is
     * the case for two fields.
     */
    public static function cleanLogic($jsst_formid, $jsst_rules) {
        $jsst_fields = self::fields($jsst_formid);
        $jsst_operators = self::operators();
        $jsst_clean = array();
        foreach ((array) $jsst_rules as $jsst_rule) {
            /* The editor posts groups and nothing else, because asking it to
               keep a duplicate of the first condition in `when`/`op`/`value` in
               step with whatever somebody drags, deletes or retypes is a piece
               of bookkeeping that only has to slip once to save a rule that
               says something nobody wrote. The duplicate still exists - every
               reader older than grouping needs it - but it is derived here,
               from the groups, where it cannot drift.

               A post that carries `when` itself is left exactly as it is: that
               is the pre-6.5 shape, and it still arrives from saved schedules
               and from anything outside this screen. (Roadmap 6.5-FORM-04) */
            if (is_array($jsst_rule) && empty($jsst_rule['when'])
                    && !empty($jsst_rule['groups']) && is_array($jsst_rule['groups'])) {
                $jsst_lead = reset($jsst_rule['groups']);
                $jsst_lead = is_array($jsst_lead) ? reset($jsst_lead) : false;
                if (is_array($jsst_lead) && !empty($jsst_lead['when'])) {
                    $jsst_rule['when']  = $jsst_lead['when'];
                    $jsst_rule['op']    = isset($jsst_lead['op']) ? $jsst_lead['op'] : 'is';
                    $jsst_rule['value'] = isset($jsst_lead['value']) ? $jsst_lead['value'] : '';
                }
            }
            if (!is_array($jsst_rule) || empty($jsst_rule['show']) || empty($jsst_rule['when'])) {
                continue;
            }
            $jsst_show = sanitize_text_field($jsst_rule['show']);
            $jsst_when = sanitize_text_field($jsst_rule['when']);
            if (!isset($jsst_fields[$jsst_show]) || !isset($jsst_fields[$jsst_when]) || $jsst_show === $jsst_when) {
                continue;
            }
            $jsst_op = isset($jsst_rule['op']) ? sanitize_key($jsst_rule['op']) : 'is';
            if (!isset($jsst_operators[$jsst_op])) {
                $jsst_op = 'is';
            }
            $jsst_entry = array(
                'show'  => $jsst_show,
                'when'  => $jsst_when,
                'op'    => $jsst_op,
                'value' => sanitize_text_field(isset($jsst_rule['value']) ? $jsst_rule['value'] : ''),
            );
            /* The further conditions, where there are any. The first one stays
               in `when`/`op`/`value` as well as in the groups, so a reader that
               predates grouping - the add-on's own copy of this class, on a site
               that has not updated it - still finds a rule it understands rather
               than none at all. It will be a narrower rule than the one that was
               written, which is the honest failure: it asks the question in a
               few cases too many rather than never asking it. */
            $jsst_groups = self::cleanGroups($jsst_rule, $jsst_fields, $jsst_show, $jsst_entry);
            if (count($jsst_groups) > 1 || (isset($jsst_groups[0]) && count($jsst_groups[0]) > 1)) {
                $jsst_entry['groups'] = $jsst_groups;
            }
            $jsst_clean[$jsst_show] = $jsst_entry;
        }
        return $jsst_clean;
    }

    /**
     * The condition groups a submitted rule carries, cleaned.
     *
     * Shape in and out is the same one the per-field visibility used before
     * this replaced it: a list of groups, every group ANDed with the next, the
     * conditions inside a group ORed with each other. A condition naming a
     * question this form does not ask, or naming the question being shown, is
     * dropped - both are rules that could never come true - and a group left
     * empty by that goes with it rather than silently passing.
     */
    private static function cleanGroups($jsst_rule, $jsst_fields, $jsst_show, $jsst_first) {
        $jsst_raw = isset($jsst_rule['groups']) && is_array($jsst_rule['groups'])
            ? $jsst_rule['groups'] : array(array($jsst_first));
        $jsst_operators = self::operators();
        $jsst_out = array();
        foreach ($jsst_raw AS $jsst_group) {
            $jsst_kept = array();
            foreach ((array) $jsst_group AS $jsst_one) {
                if (!is_array($jsst_one) || empty($jsst_one['when'])) {
                    continue;
                }
                $jsst_when = sanitize_text_field($jsst_one['when']);
                if (!isset($jsst_fields[$jsst_when]) || $jsst_when === $jsst_show) {
                    continue;
                }
                $jsst_op = isset($jsst_one['op']) ? sanitize_key($jsst_one['op']) : 'is';
                $jsst_kept[] = array(
                    'when'  => $jsst_when,
                    'op'    => isset($jsst_operators[$jsst_op]) ? $jsst_op : 'is',
                    'value' => sanitize_text_field(isset($jsst_one['value']) ? $jsst_one['value'] : ''),
                );
            }
            if ($jsst_kept) {
                $jsst_out[] = $jsst_kept;
            }
        }
        return $jsst_out ? $jsst_out : array(array($jsst_first));
    }

    public static function operators() {
        return array(
            'is'       => __('is', 'js-support-ticket'),
            'isnot'    => __('is not', 'js-support-ticket'),
            'contains' => __('contains', 'js-support-ticket'),
            'notcontains' => __('does not contain', 'js-support-ticket'),
            'filled'   => __('has been answered', 'js-support-ticket'),
            'empty'    => __('has been left empty', 'js-support-ticket'),
        );
    }

    /**
     * Is this field shown, given what has been answered so far?
     *
     * The same function answers it for the browser and for the server, which is
     * the only way the two can agree about a field the customer never saw.
     */
    public static function isVisible($jsst_field, $jsst_logic, $jsst_answers) {
        if (!isset($jsst_logic[$jsst_field])) {
            return true;
        }
        $jsst_rule = $jsst_logic[$jsst_field];
        /* Groups are AND-ed and the conditions inside one are OR-ed, which is
           the shape the per-field visibility this replaced already used and the
           shape its browser half already evaluated: every group must pass, and
           a group passes as soon as one of its conditions does. A rule with no
           groups is the single-condition shape and falls through below. */
        if (!empty($jsst_rule['groups']) && is_array($jsst_rule['groups'])) {
            foreach ($jsst_rule['groups'] AS $jsst_group) {
                $jsst_grouppassed = false;
                foreach ((array) $jsst_group AS $jsst_one) {
                    if (self::conditionHolds($jsst_one, $jsst_answers)) {
                        $jsst_grouppassed = true;
                        break;
                    }
                }
                if (!$jsst_grouppassed) {
                    return false;
                }
            }
            return true;
        }
        return self::conditionHolds($jsst_rule, $jsst_answers);
    }

    /** One condition, against the answers as they stand. */
    private static function conditionHolds($jsst_rule, $jsst_answers) {
        if (empty($jsst_rule['when'])) {
            return true;
        }
        $jsst_rule += array('op' => 'is', 'value' => '');
        $jsst_actual = self::answerFor($jsst_rule['when'], $jsst_answers);
        if (is_array($jsst_actual)) {
            $jsst_actual = implode(', ', $jsst_actual);
        }
        $jsst_actual = trim((string) $jsst_actual);
        /* "WC Product" is answered with the order line; a condition names the
           product, so compare the product behind it. (6 Oct 2026) */
        if ($jsst_rule['when'] === 'wcproductid' && ctype_digit($jsst_actual) && function_exists('wc_get_order_item_meta')) {
            $jsst_pid = (int) wc_get_order_item_meta((int) $jsst_actual, '_product_id', true);
            if ($jsst_pid > 0) {
                $jsst_actual = (string) $jsst_pid;
            }
        }
        $jsst_wanted = trim((string) $jsst_rule['value']);
        switch ($jsst_rule['op']) {
            case 'isnot':
                return strcasecmp($jsst_actual, $jsst_wanted) !== 0;
            case 'contains':
                return ($jsst_wanted !== '' && stripos($jsst_actual, $jsst_wanted) !== false);
            /* As 4.0's Not Contain read it: ignoring case, and an answer left
               empty does not contain the value. */
            case 'notcontains':
                return ($jsst_wanted !== '' && stripos($jsst_actual, $jsst_wanted) === false);
            case 'filled':
                return ($jsst_actual !== '');
            case 'empty':
                return ($jsst_actual === '');
            default:
                return strcasecmp($jsst_actual, $jsst_wanted) === 0;
        }
    }

    /**
     * What was posted for one field.
     *
     * The table's field names and the form's input names are not the same, and
     * never have been: the field is `department` and the input is
     * `departmentid`, the field is `fullname` and the input is `name`, the
     * field is `issuesummary` and the message arrives as `jsticket_message`
     * before the model renames it to `message`. Validation that did not know
     * that would refuse every submission on this site, because Priority is a
     * required field whose name nothing posts.
     *
     * The condition editor reads answers through the same function, so a
     * condition written against Department works whichever spelling the page
     * happens to use.
     */
    /**
     * The questions whose answer is an id, not the thing the id names.
     *
     * Derived from the alias table below and kept beside it deliberately: these
     * are exactly the fields whose posted value is a number, so a condition on
     * one of them is compared against `3` rather than against "Billing" or
     * "Jane Smith". Where such a field has a choice list the editor offers it
     * and the point never arises - the option's label is the name and its value
     * is the id, so nobody types either. Where it has not - `users` and
     * `assignto`, whose lists have no ceiling and are picked through a search
     * popup everywhere else in this product - the editor has to say so, because
     * a free-text box invites a name and a name can never match.
     * (Roadmap 6.5-FORM-04)
     */
    public static function idBacked() {
        return array('department', 'priority', 'helptopic', 'product', 'assignto', 'users');
    }

    /**
     * What a question is called on the form, where that is not its own key.
     *
     * The Forms screen works in `js_ticket_fieldsordering.field` - `priority`,
     * `department`, `fullname` - and the templates name several of those
     * controls something else, because the control carries an id and the
     * question does not: `priorityid`, `departmentid`, and `name` for the
     * requester. Everything that has to find a question's control or its
     * answer has to go through here, and there is one copy of the map so the
     * server and the browser cannot disagree about what a condition points at.
     * (Roadmap 5.0-FORM-01)
     */
    public static function controlAliases() {
        return array(
            'department'  => array('departmentid'),
            'priority'    => array('priorityid'),
            'helptopic'   => array('helptopicid'),
            'product'     => array('productid'),
            'fullname'    => array('name'),
            'issuesummary'=> array('message', 'jsticket_message'),
            'assignto'    => array('staffid'),
            'users'       => array('uid'),
        );
    }

    public static function answerFor($jsst_field, $jsst_answers) {
        $jsst_aliases = self::controlAliases();
        if (isset($jsst_answers[$jsst_field]) && $jsst_answers[$jsst_field] !== '') {
            return $jsst_answers[$jsst_field];
        }
        if (isset($jsst_aliases[$jsst_field])) {
            foreach ($jsst_aliases[$jsst_field] as $jsst_alias) {
                if (isset($jsst_answers[$jsst_alias]) && $jsst_answers[$jsst_alias] !== '') {
                    return $jsst_answers[$jsst_alias];
                }
            }
        }
        return isset($jsst_answers[$jsst_field]) ? $jsst_answers[$jsst_field] : '';
    }

    /* =====================================================================
     * Validation
     * ================================================================== */

    public static function validation($jsst_formid) {
        $jsst_all = get_option(self::OPT_RULES, array());
        $jsst_all = is_array($jsst_all) ? $jsst_all : array();
        return isset($jsst_all[(int) $jsst_formid]) ? (array) $jsst_all[(int) $jsst_formid] : array();
    }

    public static function saveValidation($jsst_formid, $jsst_rules) {
        self::$jsst_straightened = array();
        $jsst_fields = self::fields($jsst_formid);
        $jsst_patterns = self::patterns();
        $jsst_clean = array();
        foreach ((array) $jsst_rules as $jsst_field => $jsst_rule) {
            if (!isset($jsst_fields[$jsst_field]) || !is_array($jsst_rule)) {
                continue;
            }
            $jsst_pattern = isset($jsst_rule['pattern']) ? sanitize_key($jsst_rule['pattern']) : '';
            if (!isset($jsst_patterns[$jsst_pattern])) {
                $jsst_pattern = '';
            }
            $jsst_min = max(0, (int) (isset($jsst_rule['min']) ? $jsst_rule['min'] : 0));
            $jsst_max = max(0, (int) (isset($jsst_rule['max']) ? $jsst_rule['max'] : 0));
            /* A minimum above the maximum is a rule nothing can satisfy, and it
               does not fail loudly: the question simply stops accepting every
               answer, on the customer's form, with a refusal that reads as a
               bug in the desk rather than as a setting somebody typed. The
               screen asks for it as "Length [ ] to [ ] characters", where a
               reversed pair has no second reading to weigh against a
               transposition - so the pair is put back in the order the screen
               already says it is in. (Roadmap 5.0-FORM-01) */
            if ($jsst_min > 0 && $jsst_max > 0 && $jsst_min > $jsst_max) {
                $jsst_swap = $jsst_min;
                $jsst_min  = $jsst_max;
                $jsst_max  = $jsst_swap;
                /* Recorded so the screen can say it happened. Straightening an
                   admin's numbers behind their back would leave them looking at
                   a form they did not configure and no way to tell why. */
                self::$jsst_straightened[] = isset($jsst_fields[$jsst_field]['title'])
                    ? $jsst_fields[$jsst_field]['title'] : $jsst_field;
            }
            if ($jsst_pattern === '' && $jsst_min === 0 && $jsst_max === 0) {
                continue;   /* a rule that asks for nothing is not a rule */
            }
            $jsst_clean[$jsst_field] = array('pattern' => $jsst_pattern, 'min' => $jsst_min, 'max' => $jsst_max);
        }
        $jsst_all = get_option(self::OPT_RULES, array());
        $jsst_all = is_array($jsst_all) ? $jsst_all : array();
        if ($jsst_clean) {
            $jsst_all[(int) $jsst_formid] = $jsst_clean;
        } else {
            unset($jsst_all[(int) $jsst_formid]);
        }
        update_option(self::OPT_RULES, $jsst_all, false);
        return $jsst_clean;
    }

    /** Questions whose length pair the last save had to put back in order. */
    private static $jsst_straightened = array();

    /**
     * What the last `saveValidation()` straightened, for the screen to report.
     * Field titles, in the order they were met.
     */
    public static function straightened() {
        return self::$jsst_straightened;
    }

    private static function blankRule() {
        return array('pattern' => '', 'min' => 0, 'max' => 0);
    }

    /**
     * The shapes an answer can be asked to have.
     *
     * A short list of the ones support forms actually use, rather than a box
     * for a regular expression: a form editor that asks somebody for a regex is
     * a form editor that gets a broken regex, and the failure lands on the
     * customer rather than on the person who typed it.
     */
    public static function patterns() {
        return array(
            ''         => __('anything', 'js-support-ticket'),
            'email'    => __('an e-mail address', 'js-support-ticket'),
            'url'      => __('a web address', 'js-support-ticket'),
            'number'   => __('a number', 'js-support-ticket'),
            'digits'   => __('digits only — an order or invoice number', 'js-support-ticket'),
            'letters'  => __('letters and numbers, nothing else', 'js-support-ticket'),
            'nolinks'  => __('no web addresses — for a field that keeps attracting spam', 'js-support-ticket'),
        );
    }

    /**
     * Check one answer against one field's rules.
     *
     * Returns '' when it is fine, or the sentence to show the person filling
     * the form in. The sentences name the field, because "invalid input" on a
     * form of fifteen questions is an insult.
     */
    public static function checkValue($jsst_field, $jsst_value) {
        $jsst_value = is_array($jsst_value) ? implode(', ', $jsst_value) : (string) $jsst_value;
        $jsst_trimmed = trim($jsst_value);
        if ($jsst_trimmed === '') {
            return '';      /* emptiness is the required flag's business, not this */
        }
        $jsst_rules = $jsst_field['rules'];
        $jsst_title = $jsst_field['title'];
        if ($jsst_rules['min'] > 0 && mb_strlen($jsst_trimmed) < $jsst_rules['min']) {
            /* translators: 1: the field's name, 2: a number of characters. */
            return sprintf(__('%1$s needs to be at least %2$d characters.', 'js-support-ticket'), $jsst_title, $jsst_rules['min']);
        }
        if ($jsst_rules['max'] > 0 && mb_strlen($jsst_trimmed) > $jsst_rules['max']) {
            /* translators: 1: the field's name, 2: a number of characters. */
            return sprintf(__('%1$s can be at most %2$d characters.', 'js-support-ticket'), $jsst_title, $jsst_rules['max']);
        }
        switch ($jsst_rules['pattern']) {
            case 'email':
                return is_email($jsst_trimmed) ? ''
                    /* translators: %s is the field's name. */
                    : sprintf(__('%s does not look like an e-mail address.', 'js-support-ticket'), $jsst_title);
            case 'url':
                return (filter_var($jsst_trimmed, FILTER_VALIDATE_URL) !== false) ? ''
                    /* translators: %s: form field label. */
                    : sprintf(__('%s does not look like a web address.', 'js-support-ticket'), $jsst_title);
            case 'number':
                return is_numeric($jsst_trimmed) ? ''
                    /* translators: %s: form field label. */
                    : sprintf(__('%s has to be a number.', 'js-support-ticket'), $jsst_title);
            case 'digits':
                return preg_match('/^[0-9]+$/', $jsst_trimmed) ? ''
                    /* translators: %s: form field label. */
                    : sprintf(__('%s can only contain digits.', 'js-support-ticket'), $jsst_title);
            case 'letters':
                return preg_match('/^[\p{L}\p{N} _\-]+$/u', $jsst_trimmed) ? ''
                    /* translators: %s: form field label. */
                    : sprintf(__('%s can only contain letters, numbers and spaces.', 'js-support-ticket'), $jsst_title);
            case 'nolinks':
                return preg_match('#(https?://|www\.)#i', $jsst_trimmed)
                    /* translators: %s: form field label. */
                    ? sprintf(__('%s cannot contain a web address.', 'js-support-ticket'), $jsst_title) : '';
        }
        return '';
    }

    /**
     * Everything this form insists on, checked against what was posted.
     *
     * Returns '' or the first refusal. First rather than all of them because
     * the form redirects with one message; listing five would mean a message
     * nobody reads to the end.
     */
    public static function validate($jsst_formid, $jsst_answers) {
        $jsst_fields = self::fields($jsst_formid);
        if (!$jsst_fields) {
            return '';
        }
        $jsst_logic = self::logic($jsst_formid);
        $jsst_guest = class_exists('JSSTincluder') && JSSTincluder::getObjectClass('user')->isguest();
        foreach ($jsst_fields as $jsst_name => $jsst_field) {
            if (!$jsst_field['published'] && !($jsst_guest && $jsst_field['forvisitors'])) {
                continue;
            }
            if ($jsst_guest && !$jsst_field['forvisitors']) {
                continue;
            }
            if ($jsst_field['adminonly'] && !is_admin()) {
                continue;
            }
            /* Three kinds of answer never arrive as a posted value this class
               could check: an attachment is a file upload, a captcha has its
               own check earlier in the same request, and a consent tick posts
               under a name the form builds. Insisting on them here would refuse
               submissions that are perfectly good. */
            if (in_array($jsst_field['type'], array('file', 'captcha', 'termsandconditions'), true)) {
                continue;
            }
            /* A field the conditions have hidden is not asked about at all -
               neither insisted on nor checked. Insisting on the answer to a
               question the customer was never shown is the single worst thing
               conditional logic can do. */
            if (!self::isVisible($jsst_name, $jsst_logic, $jsst_answers)) {
                continue;
            }
            $jsst_value = self::answerFor($jsst_name, $jsst_answers);
            $jsst_filled = is_array($jsst_value) ? (bool) array_filter($jsst_value, 'strlen') : (trim((string) $jsst_value) !== '');
            if ($jsst_field['required'] && !$jsst_filled) {
                /* translators: %s is the field's name. */
                return sprintf(__('%s is needed.', 'js-support-ticket'), $jsst_field['title']);
            }
            $jsst_said = self::checkValue($jsst_field, $jsst_value);
            if ($jsst_said !== '') {
                return $jsst_said;
            }
        }
        return '';
    }
    /* =====================================================================
     * What is actually being answered
     * ================================================================== */

    /**
     * How the form is being filled in, read from the tickets it produced.
     *
     * The answers to custom questions are JSON on the ticket row, so a field's
     * answer rate is a scan of that column rather than a count on a table of
     * its own. Bounded to the most recent tickets on purpose: this is a shape,
     * not an accounting record, and a desk with two hundred thousand tickets
     * should not pay for a full scan to be told that nobody fills in question
     * six.
     */
    public static function analytics($jsst_formid, $jsst_limit = 500) {
        $jsst_formid = (int) $jsst_formid;
        $jsst_fields = self::fields($jsst_formid);
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT id, created, params, subject, email FROM `' . jssupportticket::$_db->prefix . 'js_ticket_tickets`
              WHERE multiformid = %d ORDER BY id DESC LIMIT %d', $jsst_formid, max(1, (int) $jsst_limit)), ARRAY_A);
        $jsst_answered = array();
        $jsst_bymonth = array();
        foreach ($jsst_fields as $jsst_name => $jsst_field) {
            $jsst_answered[$jsst_name] = 0;
        }
        foreach ((array) $jsst_rows as $jsst_row) {
            $jsst_params = json_decode((string) $jsst_row['params'], true);
            $jsst_params = is_array($jsst_params) ? $jsst_params : array();
            foreach ($jsst_fields as $jsst_name => $jsst_field) {
                $jsst_value = '';
                if (array_key_exists($jsst_name, $jsst_row)) {
                    $jsst_value = $jsst_row[$jsst_name];
                } elseif (isset($jsst_params[$jsst_name])) {
                    $jsst_value = $jsst_params[$jsst_name];
                }
                if (is_array($jsst_value)) {
                    $jsst_value = implode('', $jsst_value);
                }
                if (trim((string) $jsst_value) !== '') {
                    $jsst_answered[$jsst_name]++;
                }
            }
            $jsst_month = substr((string) $jsst_row['created'], 0, 7);
            if ($jsst_month !== '') {
                $jsst_bymonth[$jsst_month] = isset($jsst_bymonth[$jsst_month]) ? $jsst_bymonth[$jsst_month] + 1 : 1;
            }
        }
        ksort($jsst_bymonth);
        $jsst_seen = count($jsst_rows);
        $jsst_report = array();
        foreach ($jsst_fields as $jsst_name => $jsst_field) {
            /* A field is only reported on where it was actually being asked:
               counting a question added last week against a year of tickets
               would say nobody answers it, which is true and useless. */
            $jsst_report[$jsst_name] = array(
                'title'    => $jsst_field['title'],
                'custom'   => $jsst_field['custom'],
                'shown'    => $jsst_field['published'],
                'required' => $jsst_field['required'],
                'answered' => $jsst_answered[$jsst_name],
                'share'    => $jsst_seen > 0 ? (int) round(($jsst_answered[$jsst_name] / $jsst_seen) * 100) : null,
            );
        }
        return array(
            'looked'  => $jsst_seen,
            'months'  => $jsst_bymonth,
            'fields'  => $jsst_report,
        );
    }

    /* =====================================================================
     * Plumbing
     * ================================================================== */

    public static function registerHooks() {
        add_filter('jsst_validate_ticket_form', array(__CLASS__, 'onValidate'), 10, 2);
        /* The browser half of a condition. Printed in the footer of whichever
           desk the form is on, and only where the site has written a condition
           at all, so a site that uses none carries nothing. */
        add_action('wp_footer', array(__CLASS__, 'printConditions'));
        add_action('admin_footer', array(__CLASS__, 'printConditions'));
        /* The browser half of a length rule. Same two footers and the same
           "only where the site has written one" rule as the conditions above. */
        add_action('wp_footer', array(__CLASS__, 'printRules'));
        add_action('admin_footer', array(__CLASS__, 'printRules'));
    }

    /**
     * Hide the questions a condition holds back, in the page.
     *
     * Written against the form's own markup rather than added to the template:
     * `addticket.php` is three and a half thousand lines that draw the same
     * wrapper around every field, and finding a control by its name and hiding
     * the wrapper it sits in works for all of them - the product's own fields
     * and the ones somebody added - without a line changing in there.
     *
     * With the script removed, every question is shown. That is the right
     * failure: a customer sees one question too many rather than a form that
     * refuses to submit because a hidden field is empty, and the server has
     * already been told not to insist on anything it considers hidden.
     */
    public static function printConditions() {
        $jsst_all = get_option(self::OPT_LOGIC, array());
        if (!is_array($jsst_all) || !$jsst_all) {
            return;
        }
        ?>
        <script type="text/javascript">
        (function () {
            var jsst_logic = <?php echo wp_json_encode($jsst_all, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var jsst_form = document.querySelector('input[name="multiformid"]');
            if (!jsst_form) { return; }
            var jsst_rules = jsst_logic[jsst_form.value] || jsst_logic[String(parseInt(jsst_form.value, 10))];
            if (!jsst_rules) { return; }

            /* A question's key is not always what its control is called:
               `priority` is rendered as `priorityid`, `fullname` as `name`.
               The server already resolved conditions through this same map
               (`answerFor()`); the browser did not, so every condition written
               against one of those questions found no control, read its answer
               as empty, and hid the dependent field for good. (Roadmap 5.0-FORM-01) */
            var jsst_alias = <?php echo wp_json_encode(self::controlAliases(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            /* Searched inside the ticket form, not the whole page. The wp-admin
               screen carries a second form of its own - the "find a customer"
               popup - whose box is called `name`, which is exactly what the
               `fullname` question is called once the alias map has resolved it.
               Unscoped, a condition on the requester's name would have read
               whatever an administrator had typed into that search box. */
            var jsst_scope = jsst_form.form || document;
            function jsst_all_named(jsst_name) {
                var jsst_try = [jsst_name].concat(jsst_alias[jsst_name] || []);
                for (var jsst_t = 0; jsst_t < jsst_try.length; jsst_t++) {
                    var jsst_hit = jsst_scope.querySelectorAll('[name="' + jsst_try[jsst_t] + '"], [name="' + jsst_try[jsst_t] + '[]"]');
                    if (jsst_hit.length) { return jsst_hit; }
                }
                return [];
            }
            function jsst_control(jsst_name) {
                var jsst_hit = jsst_all_named(jsst_name);
                return jsst_hit.length ? jsst_hit[0] : null;
            }
            /* Both spellings of "the box this question sits in". The desk's
               own forms - the customer's `addticket` and the agent's
               `staffaddticket` - wrap a field in `js-ticket-from-field-wrp`;
               the wp-admin form is older markup and wraps it in
               `js-form-wrapper`. Only the first was looked for, so every
               condition on this site worked on the desk and did nothing at all
               in wp-admin: no wrapper found, the question skipped, and an
               administrator looking at the same form the customer sees
               conditioned saw all of it. (Roadmap 5.0-FORM-01) */
            function jsst_wrapper(jsst_el) {
                while (jsst_el && jsst_el !== document.body) {
                    if (jsst_el.classList && (jsst_el.classList.contains('js-ticket-from-field-wrp')
                            || jsst_el.classList.contains('js-form-wrapper'))) { return jsst_el; }
                    jsst_el = jsst_el.parentNode;
                }
                return null;
            }
            function jsst_answer(jsst_name) {
                var jsst_boxes = jsst_all_named(jsst_name);
                var jsst_out = [];
                for (var jsst_i = 0; jsst_i < jsst_boxes.length; jsst_i++) {
                    var jsst_box = jsst_boxes[jsst_i];
                    if ((jsst_box.type === 'checkbox' || jsst_box.type === 'radio') && !jsst_box.checked) { continue; }
                    /* An option that names its product (WC Product) answers with it. */
                    if (jsst_box.tagName === 'SELECT' && jsst_box.selectedIndex > -1
                            && jsst_box.options[jsst_box.selectedIndex].getAttribute('data-jsst-product')) {
                        jsst_out.push(jsst_box.options[jsst_box.selectedIndex].getAttribute('data-jsst-product'));
                        continue;
                    }
                    if (jsst_box.value !== '') { jsst_out.push(jsst_box.value); }
                }
                return jsst_out.join(', ');
            }
            function jsst_holds(jsst_one) {
                if (!jsst_one || !jsst_one.when) { return true; }
                var jsst_actual = String(jsst_answer(jsst_one.when) || '').trim();
                var jsst_wanted = String(jsst_one.value || '').trim();
                switch (jsst_one.op) {
                    case 'isnot':    return jsst_actual.toLowerCase() !== jsst_wanted.toLowerCase();
                    case 'contains': return jsst_wanted !== '' && jsst_actual.toLowerCase().indexOf(jsst_wanted.toLowerCase()) !== -1;
                    case 'notcontains': return jsst_wanted !== '' && jsst_actual.toLowerCase().indexOf(jsst_wanted.toLowerCase()) === -1;
                    case 'filled':   return jsst_actual !== '';
                    case 'empty':    return jsst_actual === '';
                    default:         return jsst_actual.toLowerCase() === jsst_wanted.toLowerCase();
                }
            }
            /* The same walk isVisible() does on the server, and deliberately
               the same shape of code: every group must pass, a group passes as
               soon as one of its conditions does. The two halves disagreeing
               about a field is the one failure conditional forms cannot have -
               the customer never sees the question and the server then insists
               on it. A rule with no groups is the single-condition shape.
               (Roadmap 6.5-FORM-04) */
            function jsst_matches(jsst_rule) {
                if (jsst_rule && jsst_rule.groups && jsst_rule.groups.length) {
                    for (var jsst_g = 0; jsst_g < jsst_rule.groups.length; jsst_g++) {
                        var jsst_group = jsst_rule.groups[jsst_g] || [];
                        var jsst_passed = false;
                        for (var jsst_c = 0; jsst_c < jsst_group.length; jsst_c++) {
                            if (jsst_holds(jsst_group[jsst_c])) { jsst_passed = true; break; }
                        }
                        if (!jsst_passed) { return false; }
                    }
                    return true;
                }
                return jsst_holds(jsst_rule);
            }
            function jsst_paint() {
                for (var jsst_name in jsst_rules) {
                    if (!Object.prototype.hasOwnProperty.call(jsst_rules, jsst_name)) { continue; }
                    var jsst_target = jsst_wrapper(jsst_control(jsst_name));
                    if (!jsst_target) { continue; }
                    if (jsst_matches(jsst_rules[jsst_name])) {
                        jsst_target.style.display = '';
                        /* Clearing the inline style only uncovers whatever the
                           stylesheet says, and for a question carrying the old
                           field-level `visible` class that is
                           `.jsst-main-up-wrapper .visible {display:none}` - a
                           class whose whole job is to hide until the legacy
                           script shows it. So a condition that was satisfied
                           from the moment the form opened appeared not to have
                           been applied, and only started working once the
                           legacy script ran on the first change. Where a Forms
                           screen condition governs a question it owns whether
                           that question is shown, so the older marker is taken
                           off rather than fought with. (Roadmap 6.5-FORM-04) */
                        if (jsst_target.classList) { jsst_target.classList.remove('visible'); }
                    } else {
                        jsst_target.style.display = 'none';
                    }
                }
            }
            document.addEventListener('change', jsst_paint);
            document.addEventListener('keyup', jsst_paint);
            jsst_paint();
        })();
        </script>
        <?php
    }

    /**
     * What a length rule reads as, when an answer breaks it.
     *
     * Only ever shown on failure - there is deliberately no line under the box
     * announcing the rule in advance. These questions already carry their own
     * description, and a second sentence under every one of them saying "has to
     * be at most 10 characters" crowded the form to tell most people something
     * they were never going to run into. The refusal is what somebody needs,
     * and only at the moment they need it.
     *
     * The library's own wording is "The input value must be between 5-10
     * characters", which names neither the question nor the unit the way the
     * server's refusal does. Saying it once, here, is what keeps the browser
     * and the server telling somebody the same thing. (Roadmap 5.0-FORM-01)
     */
    public static function lengthMessage($jsst_min, $jsst_max) {
        $jsst_min = (int) $jsst_min;
        $jsst_max = (int) $jsst_max;
        if ($jsst_min > 0 && $jsst_max > 0) {
            /* translators: 1: fewest characters, 2: most characters. */
            return sprintf(__('Has to be between %1$d and %2$d characters.', 'js-support-ticket'), $jsst_min, $jsst_max);
        }
        if ($jsst_min > 0) {
            /* translators: %d is a number of characters. */
            return sprintf(__('Has to be at least %d characters.', 'js-support-ticket'), $jsst_min);
        }
        if ($jsst_max > 0) {
            /* translators: %d is a number of characters. */
            return sprintf(__('Has to be at most %d characters.', 'js-support-ticket'), $jsst_max);
        }
        return '';
    }

    /**
     * The browser's half of a length rule.
     *
     * Until this, `min` and `max` were checked on the server and nowhere else:
     * nothing stopped the answer being typed, and the first anybody heard of
     * the limit was their submission coming back refused. The `required` flag
     * had already been through exactly this and got both halves; the length
     * rules only ever got the server one. (Roadmap 5.0-FORM-01)
     *
     * Deliberately only on failure. An earlier version of this also printed a
     * line under every ruled question announcing the limit in advance, and on a
     * form whose questions already carry their own descriptions that was a
     * second sentence under each one telling most people something they were
     * never going to run into. The refusal is what somebody needs, at the
     * moment they need it.
     *
     * `pattern` has no browser half here. The three shapes the library could
     * check - an e-mail address, a web address, a number - are already applied
     * by the templates where they matter, and the other three have no clean
     * equivalent; all six are still checked on the server.
     *
     * Written against the form's own markup for the reason `printConditions()`
     * gives above - `addticket.php` and `staffaddticket.php` between them draw
     * these fields in something like forty places and all of them put the
     * control inside the same wrapper, so finding it by name reaches the
     * product's own questions and the ones this site added without a line
     * changing in either template.
     *
     * It adds to `data-validation` rather than replacing it, because the fields
     * that carry a rule are exactly the fields most likely to already be
     * `required` or `email`, and overwriting that would turn this into a way of
     * switching off the check that was already working.
     *
     * With the script removed nothing is lost but the warning: the server still
     * refuses, with the same sentence. That is the right direction for a
     * fallback - the rule is still enforced, and only the courtesy is missing.
     */
    public static function printRules() {
        $jsst_all = get_option(self::OPT_RULES, array());
        if (!is_array($jsst_all) || !$jsst_all) {
            return;
        }
        /* Shipped with the sentences already built, rather than with the
           numbers and a template to assemble them in JavaScript: these are
           translated strings, and the translations live here. */
        $jsst_out = array();
        foreach ($jsst_all as $jsst_formid => $jsst_fields) {
            foreach ((array) $jsst_fields as $jsst_name => $jsst_rule) {
                $jsst_rule = (array) $jsst_rule + self::blankRule();
                $jsst_min = (int) $jsst_rule['min'];
                $jsst_max = (int) $jsst_rule['max'];
                $jsst_len = '';
                $jsst_why = '';
                if ($jsst_min > 0 && $jsst_max > 0) {
                    $jsst_len = $jsst_min . '-' . $jsst_max;
                } elseif ($jsst_min > 0) {
                    $jsst_len = 'min' . $jsst_min;
                } elseif ($jsst_max > 0) {
                    $jsst_len = 'max' . $jsst_max;
                }
                if ($jsst_len === '') {
                    continue;   /* a pattern alone has no browser half; the server still checks it */
                }
                $jsst_why = self::lengthMessage($jsst_min, $jsst_max);
                $jsst_out[(int) $jsst_formid][(string) $jsst_name] = array(
                    'len' => $jsst_len,
                    'why' => $jsst_why,
                );
            }
        }
        if (!$jsst_out) {
            return;
        }
        ?>
        <script type="text/javascript">
        (function () {
            var jsst_all = <?php echo wp_json_encode($jsst_out, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var jsst_form = document.querySelector('input[name="multiformid"]');
            if (!jsst_form) { return; }
            var jsst_rules = jsst_all[jsst_form.value] || jsst_all[String(parseInt(jsst_form.value, 10))];
            if (!jsst_rules) { return; }

            /* Through the alias map, for the reason printConditions() gives:
               a rule on `fullname` has to reach a control called `name`. */
            var jsst_alias = <?php echo wp_json_encode(self::controlAliases(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            /* Inside the ticket form only - see printConditions(): wp-admin's
               customer-search popup has a box called `name` too. */
            var jsst_scope = jsst_form.form || document;
            function jsst_control(jsst_name) {
                var jsst_try = [jsst_name].concat(jsst_alias[jsst_name] || []);
                for (var jsst_t = 0; jsst_t < jsst_try.length; jsst_t++) {
                    var jsst_hit = jsst_scope.querySelector('[name="' + jsst_try[jsst_t] + '"], [name="' + jsst_try[jsst_t] + '[]"]');
                    if (jsst_hit) { return jsst_hit; }
                }
                return null;
            }
            for (var jsst_name in jsst_rules) {
                if (!Object.prototype.hasOwnProperty.call(jsst_rules, jsst_name)) { continue; }
                var jsst_rule = jsst_rules[jsst_name];
                var jsst_el = jsst_control(jsst_name);
                if (!jsst_el || !jsst_rule.len) { continue; }

                jsst_el.setAttribute('data-validation-length', jsst_rule.len);
                if (jsst_rule.why) {
                    jsst_el.setAttribute('data-validation-error-msg-length', jsst_rule.why);
                }
                /* Added to what is already there. An empty answer stays the
                   required flag's business: `length` counts characters and
                   would otherwise refuse a blank optional field for being
                   shorter than the minimum. */
                var jsst_has = (jsst_el.getAttribute('data-validation') || '').split(/\s+/);
                if (jsst_has.indexOf('length') === -1) {
                    jsst_has.push('length');
                    jsst_el.setAttribute('data-validation', jsst_has.join(' ').replace(/^\s+/, ''));
                }
                if (!jsst_el.hasAttribute('data-validation-optional')
                        && !/(^|\s)required(\s|$)/.test(jsst_el.getAttribute('data-validation') || '')) {
                    jsst_el.setAttribute('data-validation-optional', 'true');
                }
            }
        })();
        </script>
        <?php
    }

    /**
     * The server's answer to a posted ticket form.
     *
     * Only for a real form post. A ticket raised by the REST API, by e-mail
     * piping, by a recurring schedule or by an automation rule is not somebody
     * filling in a form, and refusing those because the form asks for a
     * purchase reference would break every one of them. `form_request` is the
     * marker only a browser posting this plugin's own form sends.
     */
    public static function onValidate($jsst_refusal, $jsst_data) {
        if ($jsst_refusal !== '' || !is_array($jsst_data)) {
            return $jsst_refusal;
        }
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- reading a marker, not acting on it; the form's own handler verifies.
        $jsst_isform = (isset($_POST['form_request']) && $_POST['form_request'] === 'jssupportticket');
        // phpcs:enable WordPress.Security.NonceVerification.Missing
        if (!$jsst_isform) {
            return '';
        }
        $jsst_formid = isset($jsst_data['multiformid']) ? (int) $jsst_data['multiformid'] : 0;
        if ($jsst_formid <= 0) {
            $jsst_formid = (int) JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId();
        }
        return self::validate($jsst_formid, $jsst_data);
    }

    /** For System Status and the forms list. */
    public static function summary() {
        $jsst_forms = self::forms();
        $jsst_conditions = 0;
        $jsst_rules = 0;
        foreach ($jsst_forms as $jsst_form) {
            $jsst_conditions += (int) $jsst_form['conditions'];
            $jsst_rules += count(self::validation((int) $jsst_form['id']));
        }
        return array(
            'forms'      => count($jsst_forms),
            'conditions' => $jsst_conditions,
            'rules'      => $jsst_rules,
        );
    }
}
