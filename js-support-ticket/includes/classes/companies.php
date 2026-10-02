<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path. Any route
 * reaching this file by a second spelling would redeclare the class and take
 * the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTcompanies')) {
    return;
}

/**
 * A customer's employer, as a record rather than as a guess. (Roadmap 5.5-COM-06)
 *
 * v4.5 put companies on the Customers screen by taking the part of an e-mail
 * address after the @ and grouping on it, and said in the code that this was a
 * placeholder: "a real company entity - with its own name, its own contacts and
 * its own SLA - is a schema change and belongs with the customer work in a later
 * release, not smuggled in behind a SUBSTRING_INDEX". This is that release, and
 * this is that schema change.
 *
 * The reason to do it now rather than later is that three things arriving in
 * this release cannot be built on a domain string. An entitlement is held by
 * somebody: a quota of forty tickets a month belongs to Acme, not to
 * `acme.example`, and Acme has four domains. A contract has dates and a
 * reference. And a supervisor is a person who may read a colleague's ticket,
 * which is a permission - and a permission derived from a substring of an
 * e-mail address is a permission anybody with a matching address can grant
 * themselves by registering.
 *
 * So a company here is:
 *
 *  - **A record with a name**, which is what appears on a report. Renaming Acme
 *    to Acme Holdings changes one row and every screen agrees, where renaming a
 *    derived company was impossible.
 *  - **A set of domains**, because one company writes in from four of them, and
 *    a public mail provider is never one of them - {@see publicDomains()} is
 *    refused on the way in rather than producing an eight-thousand-person
 *    "gmail.com" nobody can use.
 *  - **A set of people**, named individually, each either a contact or a
 *    supervisor. A person named individually beats the domain rule, in both
 *    directions: a contractor on a personal address belongs to the company that
 *    named them, and somebody the company has removed does not come back
 *    because their address still ends the right way.
 *  - **A contract**, which is a reference, a start and an end, and nothing
 *    else. This product does not bill anybody; what the dates are for is
 *    answering "were they entitled to that on the day they asked?".
 *
 * What it deliberately is not:
 *
 *  - **Not a department.** A department is where a ticket goes and who works
 *    it. A company is who it came from. They are joined on no screen.
 *  - **Not a customer tier.** The tier a service-level policy matches on lives
 *    with the policies, in the Ticket Overdue add-on, because that is where the
 *    promise it changes lives. A company *names* its tier here so a policy can
 *    ask; it does not define one.
 *  - **Not automatic.** Nothing creates a company on its own. A desk that has
 *    never opened this screen behaves exactly as it did in v4.5 - the Customers
 *    screen still groups by domain, and {@see derivedCompanies()} is still what
 *    it reads. Records take over one company at a time, for the companies
 *    somebody actually cared enough to write down.
 *
 * The last of those is the one that keeps upgrades quiet. There is no migration
 * from the derived list, and there is deliberately no button offering to make
 * one company per domain: on a site with nine thousand customers that produces
 * four thousand company records, of which eleven are companies.
 */
class JSSTcompanies {

    /** Bumped when either table below changes shape. */
    const SCHEMA_VERSION = '550-COM06';

    /** Where the schema version is recorded. */
    const OPT_SCHEMA = 'jsst_companies_schema';

    /* ---------------------------------------------------------------------
     * What somebody is to a company.
     * ------------------------------------------------------------------ */

    /** Writes in, sees their own tickets, and nothing else changes for them. */
    const ROLE_CONTACT = 'contact';

    /**
     * Reads their colleagues' tickets as well as their own.
     *
     * This is the only thing on this screen that widens anybody's access, which
     * is why it is a named person on a named company and never a domain rule.
     */
    const ROLE_SUPERVISOR = 'supervisor';

    /** Company is live. */
    const STATUS_ACTIVE = 1;

    /** Kept for its history; matches nothing new. */
    const STATUS_ARCHIVED = 0;

    /** Resolution answers memoed per request: a queue asks this per row. */
    private static $jsst_resolved = array();

    /** all() memoed per request for the same reason. */
    private static $jsst_catalogue = null;

    /* =====================================================================
     * Schema
     * ================================================================== */

    /**
     * Two tables: the company, and who is in it.
     *
     * Membership is its own table rather than a column on the users table
     * because a person can be named by a company they do not have an account
     * on this site for - somebody who has only ever written in by e-mail has a
     * ticket and an address, and no `js_ticket_users` row at all. So the
     * membership row carries the address as the identity and the account id as
     * an optimisation, and every lookup works from the address.
     */
    public static function ensureSchema() {
        if (!JSSTschemaguard::needsRun(self::OPT_SCHEMA, self::SCHEMA_VERSION, array(
                'js_ticket_companies' => array('name', 'slug', 'domains', 'tier', 'contractref',
                    'contractstart', 'contractend', 'notes', 'status', 'created', 'updated'),
                'js_ticket_company_people' => array('companyid', 'email', 'uid', 'personrole', 'created'),
            ))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        $jsst_prefix = jssupportticket::$_db->prefix;

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_prefix . "js_ticket_companies` (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(190) NOT NULL DEFAULT '',
                slug varchar(190) NOT NULL DEFAULT '',
                domains text,
                tier varchar(100) NOT NULL DEFAULT '',
                contractref varchar(190) NOT NULL DEFAULT '',
                contractstart date DEFAULT NULL,
                contractend date DEFAULT NULL,
                notes text,
                status tinyint(1) NOT NULL DEFAULT 1,
                created datetime DEFAULT NULL,
                updated datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_slug (slug),
                KEY jsst_status (status)
            ) " . $jsst_charset);

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_prefix . "js_ticket_company_people` (
                id int(11) NOT NULL AUTO_INCREMENT,
                companyid int(11) NOT NULL DEFAULT 0,
                email varchar(190) NOT NULL DEFAULT '',
                uid int(11) NOT NULL DEFAULT 0,
                personrole varchar(20) NOT NULL DEFAULT 'contact',
                created datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY jsst_company (companyid),
                KEY jsst_email (email),
                KEY jsst_uid (uid)
            ) " . $jsst_charset);

        update_option(self::OPT_SCHEMA, self::SCHEMA_VERSION, false);
    }

    /** True where this desk has at least one company written down. */
    public static function inUse() {
        self::ensureSchema();
        return (self::countAll() > 0);
    }

    /** How many companies exist, archived ones included. */
    public static function countAll() {
        self::ensureSchema();
        return (int) jssupportticket::$_db->get_var(
            'SELECT COUNT(id) FROM `' . jssupportticket::$_db->prefix . 'js_ticket_companies`');
    }

    /* =====================================================================
     * Reading
     * ================================================================== */

    /**
     * Every company, keyed by id.
     *
     * Read whole and memoed rather than queried per lookup: resolution runs
     * once per ticket row on a queue, and the catalogue on a real site is tens
     * of rows, not thousands. A site that grows past that is a site whose
     * company list has stopped being a company list.
     */
    public static function all($jsst_includearchived = true) {
        if (self::$jsst_catalogue === null) {
            self::ensureSchema();
            $jsst_rows = jssupportticket::$_db->get_results(
                'SELECT * FROM `' . jssupportticket::$_db->prefix . 'js_ticket_companies` ORDER BY name ASC');
            self::$jsst_catalogue = array();
            if (is_array($jsst_rows)) {
                foreach ($jsst_rows AS $jsst_row) {
                    self::$jsst_catalogue[(int) $jsst_row->id] = $jsst_row;
                }
            }
        }
        if ($jsst_includearchived) {
            return self::$jsst_catalogue;
        }
        $jsst_live = array();
        foreach (self::$jsst_catalogue AS $jsst_id => $jsst_row) {
            if ((int) $jsst_row->status === self::STATUS_ACTIVE) {
                $jsst_live[$jsst_id] = $jsst_row;
            }
        }
        return $jsst_live;
    }

    /** One company, or false. */
    public static function get($jsst_id) {
        $jsst_all = self::all();
        $jsst_id = (int) $jsst_id;
        return isset($jsst_all[$jsst_id]) ? $jsst_all[$jsst_id] : false;
    }

    /** Forget what was read, after a write. */
    public static function flush() {
        self::$jsst_catalogue = null;
        self::$jsst_resolved = array();
    }

    /** A company's domains, lower-cased, as an array. */
    public static function domainsOf($jsst_row) {
        if (!is_object($jsst_row) || !isset($jsst_row->domains)) {
            return array();
        }
        $jsst_list = preg_split('/[\s,;]+/', (string) $jsst_row->domains);
        $jsst_out = array();
        foreach ((array) $jsst_list AS $jsst_one) {
            $jsst_one = strtolower(trim($jsst_one));
            if ($jsst_one !== '') {
                $jsst_out[] = $jsst_one;
            }
        }
        return array_values(array_unique($jsst_out));
    }

    /**
     * The addresses no company may claim.
     *
     * A domain rule saying `gmail.com` does not describe a company; it
     * describes a mail provider, and accepting one would put every customer on
     * a free address inside somebody's account - including, where that company
     * has a supervisor, inside their reading. So these are refused on the way
     * in, with the reason, rather than accepted and regretted.
     *
     * Filterable because the list is a judgement about the public internet and
     * a site with its own idea of it should be able to say so.
     */
    public static function publicDomains() {
        return apply_filters('jsst_company_public_domains', array(
            'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'yahoo.co.in',
            'hotmail.com', 'hotmail.co.uk', 'outlook.com', 'live.com', 'msn.com',
            'aol.com', 'icloud.com', 'me.com', 'mac.com', 'gmx.com', 'gmx.de',
            'mail.com', 'mail.ru', 'yandex.com', 'yandex.ru', 'protonmail.com',
            'proton.me', 'zoho.com', 'qq.com', '163.com', '126.com',
            'rediffmail.com', 'example.com', 'example.org', 'test.com',
        ));
    }

    /** The part of an address after the @, lower-cased, or ''. */
    public static function domainOf($jsst_email) {
        $jsst_email = strtolower(trim((string) $jsst_email));
        $jsst_at = strrpos($jsst_email, '@');
        if ($jsst_at === false) {
            return '';
        }
        return substr($jsst_email, $jsst_at + 1);
    }

    /* =====================================================================
     * Resolution: which company is this person in?
     * ================================================================== */

    /**
     * The company an address belongs to, or false.
     *
     * Two rules, in this order, and the order is the whole design:
     *
     *  1. **Named individually.** A membership row wins outright. It is how a
     *     contractor on a personal address joins Acme, and - because a company
     *     with a matching domain is only reached by rule 2 - it is also how
     *     somebody is deliberately kept out of one: a membership row on another
     *     company answers first.
     *  2. **The domain.** Only for active companies, and never for a public
     *     provider, which cannot be stored in the first place.
     *
     * A person in no company is not an error and gets `false`. That is the
     * normal state of most of a desk's customers and every screen here copes
     * with it.
     */
    public static function forEmail($jsst_email) {
        $jsst_email = strtolower(trim((string) $jsst_email));
        if ($jsst_email === '') {
            return false;
        }
        if (isset(self::$jsst_resolved[$jsst_email])) {
            return self::$jsst_resolved[$jsst_email];
        }
        self::ensureSchema();

        $jsst_answer = false;
        $jsst_named = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT companyid, personrole FROM `' . jssupportticket::$_db->prefix
            . 'js_ticket_company_people` WHERE email = %s ORDER BY id ASC LIMIT 1', $jsst_email));
        if ($jsst_named && (int) $jsst_named->companyid > 0) {
            $jsst_answer = self::get($jsst_named->companyid);
        }
        if ($jsst_answer === false) {
            $jsst_domain = self::domainOf($jsst_email);
            if ($jsst_domain !== '') {
                foreach (self::all(false) AS $jsst_row) {
                    if (in_array($jsst_domain, self::domainsOf($jsst_row), true)) {
                        $jsst_answer = $jsst_row;
                        break;
                    }
                }
            }
        }
        self::$jsst_resolved[$jsst_email] = $jsst_answer;
        return $jsst_answer;
    }

    /** The same question asked about a plugin user id. */
    public static function forUid($jsst_uid) {
        $jsst_uid = (int) $jsst_uid;
        if ($jsst_uid <= 0) {
            return false;
        }
        $jsst_email = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT user_email FROM `' . jssupportticket::$_wpprefixforuser . 'js_ticket_users` WHERE id = %d', $jsst_uid));
        return ($jsst_email === null) ? false : self::forEmail($jsst_email);
    }

    /** What this address is to its company: a role constant, or ''. */
    public static function roleOf($jsst_email) {
        $jsst_email = strtolower(trim((string) $jsst_email));
        if ($jsst_email === '') {
            return '';
        }
        self::ensureSchema();
        $jsst_role = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT personrole FROM `' . jssupportticket::$_db->prefix
            . 'js_ticket_company_people` WHERE email = %s ORDER BY id ASC LIMIT 1', $jsst_email));
        return ($jsst_role === null) ? '' : (string) $jsst_role;
    }

    /**
     * May this person read their colleagues' tickets?
     *
     * Named individually, on an active company, and nothing else - a domain
     * match never makes anybody a supervisor. The whole point of the record is
     * that reading somebody else's support conversation is a decision an
     * administrator took about a person, not a consequence of where they work.
     */
    public static function isSupervisor($jsst_email) {
        $jsst_company = self::forEmail($jsst_email);
        if (!$jsst_company || (int) $jsst_company->status !== self::STATUS_ACTIVE) {
            return false;
        }
        return (self::roleOf($jsst_email) === self::ROLE_SUPERVISOR);
    }

    /* =====================================================================
     * People
     * ================================================================== */

    /** Everybody named on a company, contacts and supervisors together. */
    public static function people($jsst_companyid) {
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            'SELECT * FROM `' . jssupportticket::$_db->prefix
            . 'js_ticket_company_people` WHERE companyid = %d ORDER BY personrole ASC, email ASC',
            (int) $jsst_companyid));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Name somebody, or change what they already are.
     *
     * One membership per address across the whole desk, on purpose: two
     * companies claiming the same person is a question with no correct answer
     * at the moment a ticket arrives, and the screen that would resolve it does
     * not exist. Naming somebody who is already named moves them.
     *
     * @return true|string True, or why not.
     */
    public static function addPerson($jsst_companyid, $jsst_email, $jsst_role = self::ROLE_CONTACT) {
        self::ensureSchema();
        $jsst_companyid = (int) $jsst_companyid;
        $jsst_email = strtolower(trim((string) $jsst_email));
        if (!self::get($jsst_companyid)) {
            return esc_html__('That company no longer exists.', 'js-support-ticket');
        }
        if (!is_email($jsst_email)) {
            return esc_html__('That is not an e-mail address.', 'js-support-ticket');
        }
        $jsst_role = ($jsst_role === self::ROLE_SUPERVISOR) ? self::ROLE_SUPERVISOR : self::ROLE_CONTACT;
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_uid = (int) jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            'SELECT id FROM `' . jssupportticket::$_wpprefixforuser . 'js_ticket_users` WHERE user_email = %s LIMIT 1', $jsst_email));
        $jsst_existing = jssupportticket::$_db->get_row(jssupportticket::$_db->prepare(
            'SELECT id FROM `' . $jsst_prefix . 'js_ticket_company_people` WHERE email = %s LIMIT 1', $jsst_email));

        if ($jsst_existing) {
            jssupportticket::$_db->update($jsst_prefix . 'js_ticket_company_people', array(
                'companyid'  => $jsst_companyid,
                'uid'        => $jsst_uid,
                'personrole' => $jsst_role,
            ), array('id' => (int) $jsst_existing->id));
        } else {
            jssupportticket::$_db->insert($jsst_prefix . 'js_ticket_company_people', array(
                'companyid'  => $jsst_companyid,
                'email'      => $jsst_email,
                'uid'        => $jsst_uid,
                'personrole' => $jsst_role,
                'created'    => current_time('mysql'),
            ));
        }
        self::flush();
        return true;
    }

    /** Stop naming somebody. The domain rule may still reach them. */
    public static function removePerson($jsst_companyid, $jsst_email) {
        self::ensureSchema();
        jssupportticket::$_db->delete(jssupportticket::$_db->prefix . 'js_ticket_company_people', array(
            'companyid' => (int) $jsst_companyid,
            'email'     => strtolower(trim((string) $jsst_email)),
        ));
        self::flush();
        return true;
    }

    /* =====================================================================
     * Writing
     * ================================================================== */

    /**
     * Create or update a company.
     *
     * Everything that can be refused is refused before anything is written: a
     * company with no name, a domain that belongs to a mail provider, and a
     * domain another company already claims. That last one is not tidiness -
     * two companies claiming `acme.example` makes {@see forEmail()} answer
     * whichever row sorted first, which is a silent and permanent wrong answer.
     *
     * @return int|string The company id, or why not.
     */
    public static function save($jsst_values) {
        self::ensureSchema();
        $jsst_id = isset($jsst_values['id']) ? (int) $jsst_values['id'] : 0;
        $jsst_name = trim(wp_strip_all_tags((string) (isset($jsst_values['name']) ? $jsst_values['name'] : '')));
        if ($jsst_name === '') {
            return esc_html__('A company needs a name — it is what every report and every ticket will show.', 'js-support-ticket');
        }

        $jsst_domains = array();
        $jsst_public = self::publicDomains();
        $jsst_raw = preg_split('/[\s,;]+/', (string) (isset($jsst_values['domains']) ? $jsst_values['domains'] : ''));
        foreach ((array) $jsst_raw AS $jsst_one) {
            $jsst_one = strtolower(trim($jsst_one));
            if ($jsst_one === '') {
                continue;
            }
            /* Somebody pasting a whole address into the domains box means the
               domain, and refusing them over an @ helps nobody. */
            if (strpos($jsst_one, '@') !== false) {
                $jsst_one = self::domainOf($jsst_one);
            }
            $jsst_one = preg_replace('/[^a-z0-9\.\-]/', '', $jsst_one);
            if ($jsst_one === '' || strpos($jsst_one, '.') === false) {
                continue;
            }
            if (in_array($jsst_one, $jsst_public, true)) {
                return sprintf(
                    /* translators: %s: an e-mail domain such as gmail.com */
                    esc_html__('%s is a mail provider, not a company. Everybody on the site with an address there would be put inside this account — and where it has a supervisor, inside their reading. Name those people individually instead.', 'js-support-ticket'),
                    esc_html($jsst_one));
            }
            $jsst_domains[] = $jsst_one;
        }
        $jsst_domains = array_values(array_unique($jsst_domains));

        $jsst_taken = self::domainOwner($jsst_domains, $jsst_id);
        if ($jsst_taken !== false) {
            return sprintf(
                /* translators: 1: an e-mail domain, 2: a company name */
                esc_html__('%1$s already belongs to %2$s. One domain answers for one company, or a ticket arriving from it belongs to whichever row sorted first.', 'js-support-ticket'),
                esc_html($jsst_taken['domain']), esc_html($jsst_taken['name']));
        }

        $jsst_row = array(
            'name'          => mb_substr($jsst_name, 0, 190),
            'slug'          => self::uniqueSlug($jsst_name, $jsst_id),
            'domains'       => implode("\n", $jsst_domains),
            'tier'          => mb_substr(trim(wp_strip_all_tags((string) (isset($jsst_values['tier']) ? $jsst_values['tier'] : ''))), 0, 100),
            'contractref'   => mb_substr(trim(wp_strip_all_tags((string) (isset($jsst_values['contractref']) ? $jsst_values['contractref'] : ''))), 0, 190),
            'contractstart' => self::cleanDate(isset($jsst_values['contractstart']) ? $jsst_values['contractstart'] : ''),
            'contractend'   => self::cleanDate(isset($jsst_values['contractend']) ? $jsst_values['contractend'] : ''),
            'notes'         => wp_kses_post((string) (isset($jsst_values['notes']) ? $jsst_values['notes'] : '')),
            'status'        => (isset($jsst_values['status']) && (int) $jsst_values['status'] === self::STATUS_ARCHIVED)
                                ? self::STATUS_ARCHIVED : self::STATUS_ACTIVE,
            'updated'       => current_time('mysql'),
        );

        $jsst_prefix = jssupportticket::$_db->prefix;
        if ($jsst_id > 0 && self::get($jsst_id)) {
            jssupportticket::$_db->update($jsst_prefix . 'js_ticket_companies', $jsst_row, array('id' => $jsst_id));
        } else {
            $jsst_row['created'] = current_time('mysql');
            jssupportticket::$_db->insert($jsst_prefix . 'js_ticket_companies', $jsst_row);
            $jsst_id = (int) jssupportticket::$_db->insert_id;
        }
        self::flush();
        return $jsst_id;
    }

    /**
     * Delete a company and the memberships that pointed at it.
     *
     * Tickets are not touched and never were: the company was never written on
     * to one. A ticket's company is answered by asking this class about the
     * address, so deleting the record correctly turns a company ticket back
     * into an ordinary one rather than leaving a dangling id on ten thousand
     * rows.
     */
    public static function delete($jsst_id) {
        self::ensureSchema();
        $jsst_id = (int) $jsst_id;
        if ($jsst_id <= 0) {
            return false;
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        jssupportticket::$_db->delete($jsst_prefix . 'js_ticket_company_people', array('companyid' => $jsst_id));
        jssupportticket::$_db->delete($jsst_prefix . 'js_ticket_companies', array('id' => $jsst_id));
        self::flush();
        return true;
    }

    /** Which company already claims one of these domains, if any. */
    private static function domainOwner($jsst_domains, $jsst_exceptid = 0) {
        if (empty($jsst_domains)) {
            return false;
        }
        foreach (self::all() AS $jsst_row) {
            if ((int) $jsst_row->id === (int) $jsst_exceptid) {
                continue;
            }
            $jsst_have = self::domainsOf($jsst_row);
            foreach ($jsst_domains AS $jsst_one) {
                if (in_array($jsst_one, $jsst_have, true)) {
                    return array('domain' => $jsst_one, 'name' => $jsst_row->name);
                }
            }
        }
        return false;
    }

    /** A slug nothing else is using. */
    private static function uniqueSlug($jsst_name, $jsst_id = 0) {
        $jsst_base = sanitize_title($jsst_name);
        if ($jsst_base === '') {
            $jsst_base = 'company';
        }
        $jsst_try = $jsst_base;
        $jsst_n = 2;
        $jsst_taken = array();
        foreach (self::all() AS $jsst_row) {
            if ((int) $jsst_row->id !== (int) $jsst_id) {
                $jsst_taken[] = $jsst_row->slug;
            }
        }
        while (in_array($jsst_try, $jsst_taken, true)) {
            $jsst_try = $jsst_base . '-' . $jsst_n;
            $jsst_n++;
        }
        return $jsst_try;
    }

    /** A Y-m-d date, or null. */
    private static function cleanDate($jsst_value) {
        $jsst_value = trim((string) $jsst_value);
        if ($jsst_value === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $jsst_value)) {
            return null;
        }
        return $jsst_value;
    }

    /* =====================================================================
     * The contract
     * ================================================================== */

    /**
     * Is the contract in force today?
     *
     * A company with no dates is in force: most desks never fill these in, and
     * treating an empty contract as an expired one would refuse everybody.
     * Returns one of `none`, `active`, `pending` or `expired`.
     */
    public static function contractState($jsst_company, $jsst_when = null) {
        if (!is_object($jsst_company)) {
            return 'none';
        }
        $jsst_start = isset($jsst_company->contractstart) ? $jsst_company->contractstart : null;
        $jsst_end = isset($jsst_company->contractend) ? $jsst_company->contractend : null;
        if (empty($jsst_start) && empty($jsst_end)) {
            return 'none';
        }
        $jsst_day = ($jsst_when === null) ? current_time('Y-m-d') : gmdate('Y-m-d', (int) $jsst_when);
        if (!empty($jsst_start) && $jsst_day < $jsst_start) {
            return 'pending';
        }
        if (!empty($jsst_end) && $jsst_day > $jsst_end) {
            return 'expired';
        }
        return 'active';
    }

    /* =====================================================================
     * What a company is worth, and how it is doing
     * ================================================================== */

    /**
     * The account-level numbers behind one company.
     *
     * Every figure is counted from the tickets when the screen is drawn, the
     * same way v5.0's analytics are, and for the same reason: a company's
     * totals kept in a column of their own would be wrong the first time
     * somebody was moved between companies, and nobody would ever find out.
     *
     * Spend is the exception, because this plugin has no idea what anybody paid.
     * It is asked for through a filter, which is how the WooCommerce and EDD
     * connectors answer it in this same release; with neither installed the
     * figure is `null` and every screen prints a dash rather than a zero. A
     * zero would be a claim, and it would be false.
     */
    public static function summary($jsst_companyid) {
        self::ensureSchema();
        $jsst_company = self::get($jsst_companyid);
        if (!$jsst_company) {
            return false;
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_where = self::ticketWhere($jsst_company, 'ticket');
        $jsst_clauses = class_exists('JSSTqueue') ? JSSTqueue::countClauses() : array('openticket' => '1=0');

        $jsst_row = jssupportticket::$_db->get_row(
            'SELECT COUNT(ticket.id) AS tickets, '
            . 'COUNT(DISTINCT ticket.email) AS people, '
            . 'SUM(CASE WHEN ' . $jsst_clauses['openticket'] . ' THEN 1 ELSE 0 END) AS openticket, '
            /* A ticket nobody has touched carries 0000-00-00 in updated and
               lastreply; taking the latest of the three with created keeps
               that zero date from being read as the last activity. */
            . 'MIN(ticket.created) AS firstseen, '
            . 'MAX(GREATEST(ticket.created, COALESCE(ticket.updated, ticket.created), COALESCE(ticket.lastreply, ticket.created))) AS lastactivity '
            . 'FROM `' . $jsst_prefix . 'js_ticket_tickets` AS ticket ' . $jsst_where);

        $jsst_out = array(
            'company'      => $jsst_company,
            'tickets'      => $jsst_row ? (int) $jsst_row->tickets : 0,
            'people'       => $jsst_row ? (int) $jsst_row->people : 0,
            'openticket'   => $jsst_row ? (int) $jsst_row->openticket : 0,
            'firstseen'    => $jsst_row ? $jsst_row->firstseen : null,
            'lastactivity' => $jsst_row ? $jsst_row->lastactivity : null,
            'named'        => count(self::people($jsst_companyid)),
            'contract'     => self::contractState($jsst_company),
            /* Null, not zero: nothing here knows what anybody paid. */
            'spend'        => apply_filters('jsst_company_spend', null, $jsst_company),
            'currency'     => apply_filters('jsst_company_currency', '', $jsst_company),
        );
        return $jsst_out;
    }

    /**
     * The WHERE that selects a company's tickets.
     *
     * Named people by address, plus everybody on a claimed domain, and the
     * people another company has named taken back out again - otherwise a
     * contractor moved from Acme to Globex would show on both accounts, since
     * their address still ends in Acme's domain.
     *
     * Built as SQL rather than as an id list because the callers are queue
     * queries counting tens of thousands of rows, and every one of them already
     * takes a clause.
     */
    public static function ticketWhere($jsst_company, $jsst_alias = 'ticket') {
        $jsst_alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $jsst_alias);
        if ($jsst_alias === '') {
            $jsst_alias = 'ticket';
        }
        $jsst_clause = self::ticketClause($jsst_company, $jsst_alias);
        return ($jsst_clause === '') ? ' WHERE 1 = 0 ' : ' WHERE ' . $jsst_clause . ' ';
    }

    /** The same condition without the WHERE, for callers that already have one. */
    public static function ticketClause($jsst_company, $jsst_alias = 'ticket') {
        if (!is_object($jsst_company)) {
            return '';
        }
        $jsst_alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $jsst_alias);
        if ($jsst_alias === '') {
            $jsst_alias = 'ticket';
        }
        $jsst_prefix = jssupportticket::$_db->prefix;
        $jsst_id = (int) $jsst_company->id;
        $jsst_parts = array();

        $jsst_parts[] = 'LOWER(' . $jsst_alias . '.email) IN ('
            . 'SELECT LOWER(cp.email) FROM `' . $jsst_prefix . 'js_ticket_company_people` AS cp '
            . 'WHERE cp.companyid = ' . $jsst_id . ')';

        $jsst_domains = self::domainsOf($jsst_company);
        if (!empty($jsst_domains)) {
            $jsst_quoted = array();
            foreach ($jsst_domains AS $jsst_one) {
                $jsst_quoted[] = "'" . esc_sql($jsst_one) . "'";
            }
            $jsst_parts[] = '('
                . "LOWER(SUBSTRING_INDEX(" . $jsst_alias . ".email, '@', -1)) IN (" . implode(',', $jsst_quoted) . ') '
                . 'AND LOWER(' . $jsst_alias . '.email) NOT IN ('
                . 'SELECT LOWER(cp2.email) FROM `' . $jsst_prefix . 'js_ticket_company_people` AS cp2 '
                . 'WHERE cp2.companyid != ' . $jsst_id . '))';
        }

        return '(' . implode(' OR ', $jsst_parts) . ')';
    }

    /* =====================================================================
     * Shared tickets
     * ================================================================== */

    /**
     * What a supervisor may see beyond their own tickets. (Roadmap 5.5-COM-06)
     *
     * Returned as an addition to the SCOPE_OWN clause rather than as a
     * replacement for it, and only for a supervisor on an active company. Every
     * other customer - including every contact - is answered exactly as they
     * were before this release: `uid = me`.
     *
     * There is one thing this deliberately does not do. It widens what a
     * supervisor may *read*; it does not let them reply to, close or reopen a
     * colleague's ticket. Those are checked separately, against the ticket's own
     * owner, and nothing here touches them - being the person who signs the
     * contract does not make somebody a party to their colleague's conversation
     * with support.
     */
    public static function sharedScopeClause($jsst_alias, $jsst_actor) {
        if (!is_array($jsst_actor) || empty($jsst_actor['email'])) {
            return '';
        }
        if (!self::isSupervisor($jsst_actor['email'])) {
            return '';
        }
        $jsst_company = self::forEmail($jsst_actor['email']);
        if (!$jsst_company) {
            return '';
        }
        $jsst_clause = self::ticketClause($jsst_company, $jsst_alias);
        return ($jsst_clause === '') ? '' : $jsst_clause;
    }

    /**
     * The row-at-a-time twin of sharedScopeClause(): may this supervisor read
     * the ticket raised from that address? Reading only - replying, closing and
     * reopening are still checked against the ticket's own owner.
     */
    public static function supervisorReads($jsst_email, $jsst_ticketemail) {
        if (!self::isSupervisor($jsst_email)) {
            return false;
        }
        $jsst_mine = self::forEmail($jsst_email);
        $jsst_theirs = self::forEmail($jsst_ticketemail);
        return ($jsst_mine && $jsst_theirs && (int) $jsst_mine->id === (int) $jsst_theirs->id);
    }

    /* =====================================================================
     * The Customers screen's rail
     * ================================================================== */

    /**
     * The companies to put above the customer list.
     *
     * Records first, then the derived domains for everybody the records do not
     * cover - so a desk that has written down its four important accounts sees
     * those four named, and still sees the rest of its world grouped the way
     * v4.5 grouped it. A desk that has written down nothing sees exactly what it
     * saw before.
     *
     * A derived row is marked `derived => true`, and the screen says so, because
     * "acme.example" and "Acme Holdings Ltd" are different kinds of claim and
     * pretending otherwise is how somebody comes to believe they have set up a
     * company account they have not.
     */
    public static function rail($jsst_derived, $jsst_limit = 12) {
        self::ensureSchema();
        $jsst_out = array();
        $jsst_claimed = array();
        foreach (self::all(false) AS $jsst_row) {
            $jsst_summary = self::summary($jsst_row->id);
            if (!$jsst_summary) {
                continue;
            }
            $jsst_out[] = array(
                'id'           => (int) $jsst_row->id,
                'company'      => $jsst_row->name,
                'derived'      => false,
                'people'       => $jsst_summary['people'],
                'tickets'      => $jsst_summary['tickets'],
                'openticket'   => $jsst_summary['openticket'],
                'lastactivity' => $jsst_summary['lastactivity'],
            );
            foreach (self::domainsOf($jsst_row) AS $jsst_domain) {
                $jsst_claimed[] = $jsst_domain;
            }
        }
        foreach ((array) $jsst_derived AS $jsst_row) {
            if (!is_object($jsst_row) || !isset($jsst_row->company)) {
                continue;
            }
            if (in_array(strtolower($jsst_row->company), $jsst_claimed, true)) {
                continue;
            }
            $jsst_out[] = array(
                'id'           => 0,
                'company'      => $jsst_row->company,
                'derived'      => true,
                'people'       => isset($jsst_row->people) ? (int) $jsst_row->people : 0,
                'tickets'      => isset($jsst_row->tickets) ? (int) $jsst_row->tickets : 0,
                'openticket'   => isset($jsst_row->openticket) ? (int) $jsst_row->openticket : 0,
                'lastactivity' => isset($jsst_row->lastactivity) ? $jsst_row->lastactivity : null,
            );
        }
        return array_slice($jsst_out, 0, max(1, (int) $jsst_limit));
    }

    /* =====================================================================
     * Hooks
     * ================================================================== */

    /**
     * What this class tells the rest of the desk.
     *
     * Only two things, and both are questions other code was already asking.
     * A company does not subscribe to events, does not send anything and does
     * not change any ticket - it is a fact about a customer, and facts do not
     * have side effects.
     */
    public static function registerHooks() {
        /* A ticket that arrives from a company carries its name into anything
           reading the ticket - the timeline, an export, a webhook payload. */
        add_filter('jsst_ticket_company', array(__CLASS__, 'companyNameFor'), 10, 2);
        /* And automation can ask about it. Three fields rather than one,
           because the three questions a rule actually asks are different:
           "is this Acme?", "what did Acme agree to?" and "is that agreement
           still in force?". The last one is the reason the contract dates
           exist at all - a rule that routes expired contracts to a different
           department is the whole point of writing them down.
           (Roadmap 5.5-COM-06) */
        add_filter('jsst_workflow_fields', array(__CLASS__, 'workflowFields'));
        add_filter('jsst_workflow_snapshot', array(__CLASS__, 'workflowSnapshot'), 10, 2);
    }

    /** What a rule may ask about the company behind a ticket. */
    public static function workflowFields($jsst_fields) {
        $jsst_fields['company'] = array(
            'label' => __('Company', 'js-support-ticket'), 'type' => 'text', 'group' => 'customer');
        $jsst_fields['companytier'] = array(
            'label' => __('Company tier', 'js-support-ticket'), 'type' => 'text', 'group' => 'customer');
        $jsst_fields['companycontract'] = array(
            'label' => __('Company contract', 'js-support-ticket'), 'type' => 'text', 'group' => 'customer');
        return $jsst_fields;
    }

    /** The answers, for one ticket. */
    public static function workflowSnapshot($jsst_snapshot, $jsst_ticket) {
        $jsst_snapshot['company'] = '';
        $jsst_snapshot['companytier'] = '';
        /* 'none' rather than '' for a company with no dates, because a rule
           written as "contract is not active" must not fire on every ticket
           from a company nobody has filled the dates in for. */
        $jsst_snapshot['companycontract'] = '';
        $jsst_email = isset($jsst_ticket['email']) ? $jsst_ticket['email'] : '';
        $jsst_company = self::forEmail($jsst_email);
        if ($jsst_company) {
            $jsst_snapshot['company'] = $jsst_company->name;
            $jsst_snapshot['companytier'] = (string) $jsst_company->tier;
            $jsst_snapshot['companycontract'] = self::contractState($jsst_company);
        }
        return $jsst_snapshot;
    }

    /** The company name behind a ticket's address, or ''. */
    public static function companyNameFor($jsst_default, $jsst_email) {
        $jsst_company = self::forEmail($jsst_email);
        return $jsst_company ? $jsst_company->name : $jsst_default;
    }
}
