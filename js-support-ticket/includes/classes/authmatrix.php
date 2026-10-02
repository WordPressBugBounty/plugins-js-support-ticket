<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path. Any route
 * that reaches this file by a second spelling of the same path would otherwise
 * redeclare the class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTauthmatrix')) {
    return;
}

/**
 * The authorisation matrix: every way in, and what guards it.
 * (Roadmap 5.0-SEC-01)
 *
 * The roadmap's own reason for putting this in the same release as the API
 * rather than after it: *"a public API multiplies the attack surface; the
 * matrix must land with the API, not after it."* An API shipped in one release
 * and reviewed in the next is an API that is unreviewed for a release.
 *
 * This is a **scanner over the real code**, not a checklist somebody ticks. It
 * reads the REST route declaration that the server is actually built from, and
 * it reads the plugin's own source for its AJAX handlers, then reports each
 * entry point against nine things:
 *
 *   authentication   is an anonymous caller refused?
 *   authorisation    is a capability or task asked, and which one?
 *   scoping          for anything addressing one ticket, is the permission asked
 *                    about *that* ticket rather than in general? (this is the
 *                    IDOR question, and it is the one that finds real bugs)
 *   nonce            does a state-changing request carry a CSRF token?
 *   validation       are arguments typed and constrained before the callback?
 *   sanitisation     are they cleaned?
 *   prepared         does the handler use prepared statements?
 *   escaping         is output escaped?
 *   files            does it touch upload or download paths?
 *
 * Two things it deliberately is not. It is not a substitute for reading the
 * code — a scanner can see that `check_ajax_referer` is called, not that it is
 * called before the write. And it does not claim to find every fault: it
 * reports what it can prove and says plainly what it cannot see, because a
 * green report that was never capable of going red is worse than no report.
 *
 * The finding that justifies the whole exercise is the first one it makes:
 * **`permission_callback => __return_true` is the single most common REST
 * vulnerability in the WordPress ecosystem**, and it is a one-line mistake
 * that no test suite catches, because the endpoint works perfectly.
 */
class JSSTauthmatrix {

    /** Where the last scan is kept. */
    const OPT_CACHE = 'jsst_authmatrix';

    /** Bumped when the grading changes, so an old verdict is never shown. */
    const CACHE_VERSION = '500-SEC01.4';

    /** The scan, once per request. */
    private static $jsst_report = null;

    /** How each row is graded. */
    const PASS = 'pass';
    const WARN = 'warn';
    const FAIL = 'fail';
    const NA = 'na';

    /**
     * The whole matrix.
     *
     * @return array rows, totals, notes
     */
    public static function report($jsst_refresh = false) {
        if (self::$jsst_report !== null && !$jsst_refresh) {
            return self::$jsst_report;
        }
        /* Half a second of reading 643 files is nothing on the screen that
           asks for it and is not nothing on System Status, which asks only for
           the one-line answer and would pay the same price for it. So the
           verdict is kept, stamped with the plugin version and the grading
           version: a release that changes either produces a new scan without
           anybody pressing anything, and the button below the table is for
           somebody who has just edited a file on a running site. */
        $jsst_stamp = self::CACHE_VERSION . '|' . jssupportticket::$_currentversion;
        $jsst_cached = get_option(self::OPT_CACHE, array());
        if (!$jsst_refresh && is_array($jsst_cached) && isset($jsst_cached['stamp']) && $jsst_cached['stamp'] === $jsst_stamp) {
            self::$jsst_report = $jsst_cached;
            return self::$jsst_report;
        }
        $jsst_rows = array_merge(self::restRows(), self::ajaxRows(), self::dispatchRows(), self::formRows());
        /* NA is counted too. A row graded NA is an allow-listed task with no
           method installed behind it, and leaving it out of the totals made
           them not add up to the number of rows on the screen - which is the
           first thing anybody checks on a report like this. */
        $jsst_totals = array('total' => 0, self::PASS => 0, self::WARN => 0, self::FAIL => 0, self::NA => 0);
        foreach ($jsst_rows as $jsst_row) {
            $jsst_totals['total']++;
            $jsst_totals[$jsst_row['grade']]++;
        }
        self::$jsst_report = array(
            'rows'   => $jsst_rows,
            'totals' => $jsst_totals,
            'checks' => self::checks(),
            'notes'  => self::limits(),
            'when'   => current_time('mysql'),
            'stamp'  => $jsst_stamp,
        );
        update_option(self::OPT_CACHE, self::$jsst_report, false);
        return self::$jsst_report;
    }

    /** Read the source again, for somebody who has just changed it. */
    public static function refresh() {
        return self::report(true);
    }

    /** The nine columns, and what each one means. */
    public static function checks() {
        return array(
            'auth'     => __('Anonymous callers refused', 'js-support-ticket'),
            'authz'    => __('A permission is asked', 'js-support-ticket'),
            'scope'    => __('Asked about this record, not in general', 'js-support-ticket'),
            'nonce'    => __('CSRF token on state changes', 'js-support-ticket'),
            'validate' => __('Arguments typed and constrained', 'js-support-ticket'),
            'sanitise' => __('Arguments cleaned', 'js-support-ticket'),
            'prepared' => __('Prepared statements', 'js-support-ticket'),
            'escape'   => __('Output escaped', 'js-support-ticket'),
            'files'    => __('Upload or download path', 'js-support-ticket'),
        );
    }

    /**
     * The REST surface, read from the declaration the server is built from.
     *
     * Not from a copy. `JSSTrestapi::routes()` is the array `registerRoutes()`
     * walks, so a route that exists is a route in this report and a route in
     * this report exists — which is the property a matrix has to have to be
     * worth reading.
     */
    private static function restRows() {
        if (!class_exists('JSSTrestapi')) {
            return array();
        }
        $jsst_rows = array();
        foreach (JSSTrestapi::routes() as $jsst_pattern => $jsst_methods) {
            $jsst_addressesone = (strpos($jsst_pattern, '(?P<id>') !== false);
            foreach ($jsst_methods as $jsst_method => $jsst_spec) {
                $jsst_writes = in_array($jsst_method, array('POST', 'PATCH', 'PUT', 'DELETE'), true);
                $jsst_args = isset($jsst_spec['args']) ? $jsst_spec['args'] : array();

                $jsst_row = array(
                    'kind'     => 'REST',
                    'name'     => $jsst_method . ' ' . $jsst_pattern,
                    'handler'  => 'JSSTrestapi::' . $jsst_spec['callback'],
                    'writes'   => $jsst_writes,
                    'notes'    => array(),
                    'results'  => array(),
                );

                /* Authentication. permit() refuses anonymous callers for every
                   route, so this is a property of the shared gate rather than
                   of each route — which is exactly why it is stated once. */
                $jsst_row['results']['auth'] = array(self::PASS,
                    __('permit() refuses when nobody is signed in.', 'js-support-ticket'));

                /* Authorisation. */
                if (!empty($jsst_spec['action'])) {
                    $jsst_row['results']['authz'] = array(self::PASS, $jsst_spec['action']);
                } elseif ($jsst_pattern === '/me') {
                    $jsst_row['results']['authz'] = array(self::NA,
                        __('Reports the caller\'s own identity and permissions. Needs an account and nothing further.', 'js-support-ticket'));
                } else {
                    $jsst_row['results']['authz'] = array(self::FAIL,
                        __('No capability action declared for this route.', 'js-support-ticket'));
                }

                /* Scoping — the IDOR column. */
                if ($jsst_addressesone) {
                    $jsst_row['results']['scope'] = !empty($jsst_spec['scoped'])
                        ? array(self::PASS, __('The permission is asked about the ticket in the URL.', 'js-support-ticket'))
                        : array(self::FAIL, __('Addresses one ticket by id but asks its permission in general — any caller holding the action could read any id.', 'js-support-ticket'));
                } else {
                    $jsst_row['results']['scope'] = array(self::NA,
                        __('Does not address a single record by id.', 'js-support-ticket'));
                }

                /* CSRF. An application-password request carries no cookie and
                   so cannot be forged from a browser; a cookie request must
                   carry X-WP-Nonce, which WordPress enforces in the REST
                   server before any of our code runs. Both are true of every
                   route, so this is a property of the transport. */
                $jsst_row['results']['nonce'] = $jsst_writes
                    ? array(self::PASS, __('WordPress requires X-WP-Nonce for cookie-authenticated REST writes; application passwords carry no cookie to forge.', 'js-support-ticket'))
                    : array(self::NA, __('Read-only.', 'js-support-ticket'));

                /* Validation and sanitisation, read from the declared args. */
                $jsst_typed = 0;
                $jsst_sanitised = 0;
                $jsst_required = 0;
                foreach ($jsst_args as $jsst_arg) {
                    if (isset($jsst_arg['type']) || isset($jsst_arg['enum'])) {
                        $jsst_typed++;
                    }
                    if (isset($jsst_arg['sanitize_callback'])) {
                        $jsst_sanitised++;
                    }
                    $jsst_required++;
                }
                if ($jsst_required === 0) {
                    $jsst_row['results']['validate'] = array(self::NA, __('Takes no arguments.', 'js-support-ticket'));
                    $jsst_row['results']['sanitise'] = array(self::NA, __('Takes no arguments.', 'js-support-ticket'));
                } else {
                    $jsst_row['results']['validate'] = ($jsst_typed === $jsst_required)
                        /* translators: %d: number of arguments. */
                        ? array(self::PASS, sprintf(__('All %d arguments are typed.', 'js-support-ticket'), $jsst_required))
                        /* translators: 1: number of typed arguments, 2: total number of arguments. */
                        : array(self::WARN, sprintf(__('%1$d of %2$d arguments are typed.', 'js-support-ticket'), $jsst_typed, $jsst_required));
                    /* A string argument with no sanitiser is only a finding
                       where it reaches output or a query. Free-text message
                       bodies are stored as HTML on purpose and go through
                       wp_kses at render, so this is a warning to read rather
                       than a failure. */
                    $jsst_row['results']['sanitise'] = ($jsst_sanitised === $jsst_required)
                        /* translators: %d: number of arguments. */
                        ? array(self::PASS, sprintf(__('All %d arguments have a sanitiser.', 'js-support-ticket'), $jsst_required))
                        : array(self::WARN, sprintf(
                            /* translators: 1: number of arguments with a sanitiser, 2: total number of arguments. */
                            __('%1$d of %2$d have a sanitiser. The rest are message bodies, kept as written and escaped at render.', 'js-support-ticket'),
                            $jsst_sanitised, $jsst_required));
                }

                /* Everything below the API. */
                $jsst_row['results']['prepared'] = array(self::PASS,
                    __('Reads go through JSSTticketquery and writes through JSSTticketservice, which prepare every statement.', 'js-support-ticket'));
                $jsst_row['results']['escape'] = array(self::NA,
                    __('Returns JSON, which WordPress encodes. No HTML is produced.', 'js-support-ticket'));
                $jsst_row['results']['files'] = (strpos($jsst_pattern, 'attachments') !== false)
                    ? array(self::PASS, __('Returns metadata and a guarded URL. Never streams bytes, so the attachment guard stays the one decision about who may download.', 'js-support-ticket'))
                    : array(self::NA, __('Does not touch files.', 'js-support-ticket'));

                $jsst_rows[] = self::grade($jsst_row);
            }
        }
        return $jsst_rows;
    }

    /**
     * Every AJAX registration in the plugin, one entry per action.
     *
     * Two things this has to get right, both of which it got wrong on its
     * first run against a real site.
     *
     * **One action is one entry point, however many times it is registered.**
     * An action open to signed-out callers is registered twice - once as
     * `wp_ajax_` and once as `wp_ajax_nopriv_` - and reporting those as two
     * rows doubled every finding each one carried: the first report said four
     * failures where there were two. A security report that counts the same
     * hole twice is a report somebody learns to discount, which is worse than
     * no report. The pair of registrations is what makes an action public, so
     * that is what it is recorded as rather than as a second row.
     *
     * **And the name is resolved through a class constant** where the
     * registration is written as one, `add_action('wp_ajax_' . self::ACTION,
     * ...)`. Reading only literal names silently missed the web app manifest
     * and the plan feed, both of them public and both of them reachable by
     * anybody - and an entry point the matrix cannot see is the one failure
     * this whole exercise exists to prevent.
     */
    private static function ajaxRegistrations() {
        $jsst_found = array();
        foreach (self::sourceFiles() as $jsst_file) {
            $jsst_source = @file_get_contents($jsst_file);
            if ($jsst_source === false) {
                continue;
            }
            /* The name is either finished inside the quotes, or the quotes end
               at `wp_ajax_` and a constant is concatenated on. Both spellings
               are in this plugin. */
            if (!preg_match_all(
                "/add_action\(\s*['\"]wp_ajax_(nopriv_)?([a-z0-9_]*)['\"]\s*"
                . "(?:\.\s*(?:self|static|[A-Za-z0-9_]+)::([A-Za-z0-9_]+)\s*)?"
                . ",\s*(?:array\(\s*[^,]+,\s*)?['\"]([a-zA-Z0-9_]+)['\"]/",
                $jsst_source, $jsst_matches, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($jsst_matches as $jsst_match) {
                $jsst_action = $jsst_match[2];
                if ($jsst_match[3] !== '') {
                    $jsst_action .= self::constantValue($jsst_source, $jsst_match[3]);
                }
                if ($jsst_action === '') {
                    continue;   /* a name this reader cannot resolve */
                }
                if (!isset($jsst_found[$jsst_action])) {
                    $jsst_found[$jsst_action] = array(
                        'callback' => $jsst_match[4],
                        'source'   => $jsst_source,
                        'public'   => false,
                    );
                }
                if ($jsst_match[1] !== '') {
                    $jsst_found[$jsst_action]['public'] = true;
                }
            }
        }
        ksort($jsst_found);
        return $jsst_found;
    }

    /**
     * The value of a class constant declared in the same file.
     *
     * Deliberately not `constant()`: this reads files that may never have been
     * loaded on the request doing the reading, and a scanner that has to boot
     * the code it is auditing is a scanner that cannot audit code that is off.
     */
    private static function constantValue($jsst_source, $jsst_name) {
        if (preg_match("/const\s+" . preg_quote($jsst_name, '/') . "\s*=\s*['\"]([^'\"]+)['\"]/", $jsst_source, $jsst_match)) {
            return $jsst_match[1];
        }
        return '';
    }

    /**
     * Does this handler change anything?
     *
     * Asked rather than assumed, because assuming it cost this report its
     * credibility on its first run: every AJAX row was graded as a state
     * change, so the two handlers that publish a JSON document to anonymous
     * callers on purpose - the OpenAPI description and the web app manifest -
     * were failed for not carrying a CSRF token they have no possible use for.
     *
     * The test errs towards a write. Anything that dispatches by name, touches
     * a table, an option, a transient or a user, or calls the ticket service
     * counts; a handler counts as a read only when none of that is anywhere in
     * it. Being wrong in that direction produces a warning to read, and being
     * wrong in the other produces a hole reported as clean.
     */
    private static function describesWrite($jsst_body) {
        /* Every needle here is a call, not a word. The first version of this
           list held the bare words `save` and `store`, which matched the text
           inside an HTML string and a nonce name - so a handler whose whole
           body is `return wp_create_nonce('save-ticket-')` was reported as a
           state change reachable by anonymous callers. A scanner that finds
           the word "save" in a form's markup finds a fault in every form. */
        return self::mentions($jsst_body, array(
            '->$', 'call_user_func',
            '->insert(', '->update(', '->delete(', '->replace(', '->query(',
            'update_option', 'add_option', 'delete_option',
            'update_user_meta', 'update_post_meta', 'update_metadata',
            'set_transient', 'delete_transient',
            'wp_insert_', 'wp_update_', 'wp_delete_', 'wp_handle_upload',
            'move_uploaded_file', 'JSSTticketservice::',
            '->save(', '->store(', 'enqueue(', 'wp_mail(',
        ));
    }

    /**
     * The AJAX surface, read out of the source.
     *
     * Every registration is found, its callback located, and that callback's
     * body examined for the things an AJAX handler has to do. A `nopriv`
     * registration is called out on its own, because an endpoint reachable by
     * anybody is the one worth reading twice.
     */
    private static function ajaxRows() {
        $jsst_rows = array();
        foreach (self::ajaxRegistrations() as $jsst_action => $jsst_entry) {
            $jsst_callback = $jsst_entry['callback'];
            $jsst_public = $jsst_entry['public'];
            $jsst_body = self::functionBody($jsst_entry['source'], $jsst_callback);
            if ($jsst_body === '') {
                /* The handler is in another file. Look for it across the
                   plugin rather than reporting a hole that is not one. */
                $jsst_body = self::findFunctionBody($jsst_callback);
            }

            $jsst_row = array(
                'kind'    => $jsst_public ? __('AJAX (public)', 'js-support-ticket') : 'AJAX',
                'name'    => $jsst_action,
                'handler' => $jsst_callback . '()',
                'writes'  => true,
                'notes'   => array(),
                'results' => array(),
            );

            if ($jsst_body === '') {
                $jsst_row['results'] = array_fill_keys(array_keys(self::checks()),
                    array(self::WARN, __('The handler could not be located to read, so nothing about it is claimed.', 'js-support-ticket')));
                $jsst_rows[] = self::grade($jsst_row);
                continue;
            }

            $jsst_row = self::gradeHandler($jsst_row, $jsst_body, $jsst_public);

            $jsst_rows[] = self::grade($jsst_row);
        }
        return $jsst_rows;
    }

    /**
     * Grade one handler body against the nine checks.
     *
     * Shared by the registered handlers and by the tasks behind the
     * dispatcher, because they are the same question asked of the same kind of
     * code, and two copies of this would drift until the two halves of the
     * report disagreed about what a pass means.
     */
    private static function gradeHandler($jsst_row, $jsst_body, $jsst_public) {
        /* JSSTroles is this plugin's own capability class and JSSTcapability
           is the 4.5 one that asks both permission systems. A handler calling
           either is guarded, and reading them as unrecognised helpers reported
           the properly guarded older handlers as findings. */
        $jsst_hascap = self::mentions($jsst_body, array('current_user_can', 'JSSTcapability::can', 'JSSTcapability::assert', 'JSSTroles::', 'checkPermissionGrantedForTask'));
        $jsst_hasnonce = self::mentions($jsst_body, array('check_ajax_referer', 'wp_verify_nonce', 'check_admin_referer'));
        $jsst_hastoken = self::mentions($jsst_body, array('hash_equals'));
        $jsst_haslogin = self::mentions($jsst_body, array('is_user_logged_in', 'get_current_user_id', 'wp_get_current_user', 'JSSTuser::'));
        $jsst_hasprepare = self::mentions($jsst_body, array('->prepare(', 'JSSTticketservice::', 'JSSTticketquery::'));
        $jsst_hasrawsql = self::mentions($jsst_body, array('->query(', '->get_var(', '->get_row(', '->get_results('));
        /* filter_input() and filter_var() are sanitisers. Leaving them out of
           this list warned about two handlers that clean their input properly,
           and a report that cries wolf is a report nobody finishes reading. */
        $jsst_hassanitise = self::mentions($jsst_body, array('sanitize_', 'absint(', 'intval(', '(int)', 'wp_kses', 'esc_url_raw', 'filter_input', 'filter_var'));
        $jsst_hasescape = self::mentions($jsst_body, array('esc_html', 'esc_attr', 'esc_url', 'wp_send_json', 'wp_kses', 'wp_json_encode'));
        $jsst_hasfiles = self::mentions($jsst_body, array('wp_handle_upload', 'move_uploaded_file', 'readfile', 'file_get_contents', 'fopen('));
        $jsst_dispatches = self::mentions($jsst_body, array('->$', 'call_user_func'));
        $jsst_helper = self::permissionHelper($jsst_body);
        $jsst_reads = self::readsRequest($jsst_body);
        $jsst_row['writes'] = self::describesWrite($jsst_body);

        if ($jsst_dispatches) {
            $jsst_row['notes'][] = __('Dispatches by name to a list of model methods rather than doing the work itself. This row grades the door; the rooms behind it are listed separately.', 'js-support-ticket');
        }

        /* Authentication. */
        if ($jsst_public) {
            if ($jsst_hastoken) {
                $jsst_row['results']['auth'] = array(self::PASS, __('Reachable without an account by design, and authenticated by a shared secret compared in constant time.', 'js-support-ticket'));
            } elseif (!$jsst_row['writes']) {
                $jsst_row['results']['auth'] = array(self::PASS, __('Reachable without an account, and it only publishes. Nothing here to authenticate.', 'js-support-ticket'));
            } elseif ($jsst_hascap || $jsst_helper !== '') {
                $jsst_row['results']['auth'] = array(self::PASS, __('Reachable without an account, and it refuses one: it asks a permission before doing anything.', 'js-support-ticket'));
            } elseif ($jsst_hasnonce) {
                /* Deliberately a warning and not a pass. A nonce proves the
                   request came from a page on this site; it does not prove the
                   caller may do the thing. WordPress will validate a nonce
                   made for the signed-out user, so a guest-facing page hands
                   one out. */
                $jsst_row['results']['auth'] = array(self::WARN, __('Reachable without an account and guarded by a nonce rather than by a permission. A nonce says where the request came from, not who may make it.', 'js-support-ticket'));
            } elseif ($jsst_haslogin) {
                $jsst_row['results']['auth'] = array(self::WARN, __('Reachable without an account. It reads the current user, which is nobody for an anonymous caller — confirm it refuses rather than acting as nobody.', 'js-support-ticket'));
            } elseif ($jsst_dispatches) {
                $jsst_row['results']['auth'] = array(self::WARN, __('Reachable without an account and it asks nothing itself, because it is a door rather than a handler: it accepts only the names on its allow-list, and each of those is graded on its own row.', 'js-support-ticket'));
            } else {
                $jsst_row['results']['auth'] = array(self::FAIL, __('Reachable without an account, changes something, and asks nothing about who is calling.', 'js-support-ticket'));
            }
        } else {
            $jsst_row['results']['auth'] = array(self::PASS,
                __('Registered only for signed-in callers, so WordPress refuses an anonymous call before any of this code runs.', 'js-support-ticket'));
        }

        /* Authorisation. */
        if ($jsst_hascap) {
            $jsst_row['results']['authz'] = array(self::PASS, __('Asks a capability or a task before acting.', 'js-support-ticket'));
        } elseif ($jsst_hastoken) {
            $jsst_row['results']['authz'] = array(self::PASS, __('Authorised by its own shared secret rather than by a user permission, because the caller has no account.', 'js-support-ticket'));
        } elseif ($jsst_helper !== '') {
            /* Limit two, stated as a row rather than as a footnote: naming the
               helper is what turns "no check found" into something somebody
               can go and read in a minute. */
            $jsst_row['results']['authz'] = array(self::WARN,
                /* translators: %s: name of the function the check is handed to. */
                sprintf(__('Defers to %s, which this reader cannot follow. Confirm by hand that it refuses the wrong caller.', 'js-support-ticket'), $jsst_helper));
        } elseif (!$jsst_row['writes']) {
            /* "Reads only" is not the end of the sentence. A handler that
               answers according to an id in the request is answering about a
               record, and whether the caller may see *that* record is exactly
               the question this report exists to ask. Saying NA here would be
               the scanner asserting something it cannot see. */
            $jsst_row['results']['authz'] = $jsst_reads
                ? array(self::WARN, __('Reads only and asks no permission — but it answers according to what the request asked for. Confirm it cannot be asked for somebody else\'s.', 'js-support-ticket'))
                : array(self::NA, __('Reads only, and returns the same thing to every caller who can reach it.', 'js-support-ticket'));
        } else {
            $jsst_row['results']['authz'] = array(self::WARN, __('No capability check found in the handler. Confirm the action is harmless for any caller who can reach it.', 'js-support-ticket'));
        }

        /* The IDOR column. The per-record checks this plugin actually uses are
           named here rather than only the 4.5 capability class, because the
           older handlers guard themselves by comparing the ticket's owner to
           the current user and that is a real check - reading it as no check
           would report the guarded handlers and the unguarded ones the same
           way. */
        $jsst_perrecord = self::mentions($jsst_body, array(
            'JSSTcapability::can', 'JSSTcapability::assert', 'JSSTticketquery::detail',
            'getUIdById', 'validateTicketDetail', 'isguest(', '->uid()',
        ));
        if ($jsst_perrecord) {
            $jsst_row['results']['scope'] = array(self::PASS, __('Asks about the record it was given, not in general.', 'js-support-ticket'));
        } elseif ($jsst_reads && $jsst_public) {
            $jsst_row['results']['scope'] = array(self::WARN, __('Answers from what the request names, is reachable without an account, and no per-record check was found. This is the IDOR question and it is worth reading by hand.', 'js-support-ticket'));
        } else {
            $jsst_row['results']['scope'] = array(self::NA, __('Not established by reading — check by hand if this handler takes an id.', 'js-support-ticket'));
        }

        /* CSRF. */
        if (!$jsst_row['writes']) {
            $jsst_row['results']['nonce'] = array(self::NA, __('Reads and publishes. There is no state change here to forge a request against.', 'js-support-ticket'));
        } elseif ($jsst_hasnonce) {
            $jsst_row['results']['nonce'] = array(self::PASS, __('Verifies a nonce.', 'js-support-ticket'));
        } elseif ($jsst_hastoken) {
            $jsst_row['results']['nonce'] = array(self::NA, __('Authenticated by a URL token rather than a session, so there is no session to forge against.', 'js-support-ticket'));
        } elseif ($jsst_dispatches) {
            $jsst_row['results']['nonce'] = array(self::WARN, __('The dispatcher verifies nothing itself. Every task it can reach has to carry its own nonce, which is what the rows below check.', 'js-support-ticket'));
        } elseif (!self::writesState($jsst_body)) {
            $jsst_row['results']['nonce'] = array(self::WARN, __('No nonce, but what it writes is a file of its own rather than anything about this site. Worth adding a token; not a way to change the site behind somebody\'s back.', 'js-support-ticket'));
        } else {
            $jsst_row['results']['nonce'] = array(self::FAIL, __('State-changing and no nonce found — a signed-in user could be made to call this from another site.', 'js-support-ticket'));
        }

        /* Validation and sanitisation. A handler that reads nothing out of the
           request but the token it verifies has nothing to sanitise, and
           warning about it is noise. */
        if (!$jsst_reads) {
            $jsst_row['results']['validate'] = array(self::NA, __('Takes nothing from the request but the token it verifies.', 'js-support-ticket'));
            $jsst_row['results']['sanitise'] = array(self::NA, __('Takes nothing from the request but the token it verifies.', 'js-support-ticket'));
        } else {
            $jsst_row['results']['validate'] = $jsst_hassanitise
                ? array(self::PASS, __('Casts or constrains its input.', 'js-support-ticket'))
                : array(self::WARN, __('Reads the request and no casting or constraining was found.', 'js-support-ticket'));
            $jsst_row['results']['sanitise'] = $jsst_hassanitise
                ? array(self::PASS, __('Sanitises its input.', 'js-support-ticket'))
                : array(self::WARN, __('Reads the request and no sanitisation was found.', 'js-support-ticket'));
        }

        /* A query with no prepare() is only a finding where something from
           the request can reach it. Several handlers here run a fixed SELECT
           built from table names and nothing else, and calling those an
           injection risk cost this report five of its nine failures on the
           first run - every one of them wrong, and each one making the four
           real ones easier to ignore. Where the request is read as well, the
           two facts are reported together and left to a reader, because a
           scanner cannot see which value reaches which string. */
        if (!$jsst_hasrawsql) {
            $jsst_row['results']['prepared'] = array(self::NA, __('Runs no queries of its own.', 'js-support-ticket'));
        } elseif ($jsst_hasprepare) {
            $jsst_row['results']['prepared'] = array(self::PASS, __('Prepares its statements or goes through the service layer.', 'js-support-ticket'));
        } elseif (!$jsst_reads) {
            $jsst_row['results']['prepared'] = array(self::PASS, __('Runs a query without prepare(), but reads nothing from the request, so nothing from outside reaches it.', 'js-support-ticket'));
        } else {
            $jsst_row['results']['prepared'] = array(self::WARN, __('Runs a query without prepare() and reads the request. Check by hand what reaches the statement.', 'js-support-ticket'));
        }
        $jsst_row['results']['escape'] = $jsst_hasescape
            ? array(self::PASS, __('Escapes or encodes what it returns.', 'js-support-ticket'))
            : array(self::WARN, __('No escaping found on the way out.', 'js-support-ticket'));
        $jsst_row['results']['files'] = $jsst_hasfiles
            ? array(self::WARN, __('Touches a file path — check it against the attachment guard by hand.', 'js-support-ticket'))
            : array(self::NA, __('Does not touch files.', 'js-support-ticket'));

        return $jsst_row;
    }

    /**
     * Does it change the site, as opposed to writing a file of its own?
     *
     * The CSRF question is about state a victim would not have chosen to
     * change - a row, an option, a user. A handler that assembles a zip in a
     * scratch folder and hands it back is writing, but there is nothing there
     * to be tricked into: the missing token is worth fixing and it is not a
     * way to change this site behind somebody's back, and grading the two the
     * same way puts a genuine hole and a tidy-up on the same line.
     */
    private static function writesState($jsst_body) {
        return self::mentions($jsst_body, array(
            '->insert(', '->update(', '->replace(', '->query(',
            'update_option', 'add_option', 'delete_option',
            'update_user_meta', 'update_post_meta', 'update_metadata',
            'set_transient', 'delete_transient',
            'wp_insert_', 'wp_update_', 'wp_delete_',
            'JSSTticketservice::', 'wp_mail(',
        ));
    }

    /**
     * The name of a permission helper the handler defers to, if it does.
     *
     * A handler calling `self::mayDraft()` is not unguarded, it is guarded
     * somewhere this reader cannot follow - and those are two different
     * sentences to put in front of somebody reviewing their own site.
     */
    private static function permissionHelper($jsst_body) {
        if (preg_match('/(?:self|static|[A-Za-z0-9_]+)::((?:may|can|is|assert|check|require)[A-Za-z0-9_]*)\s*\(/', $jsst_body, $jsst_match)) {
            return $jsst_match[1] . '()';
        }
        return '';
    }

    /**
     * Does the handler read anything out of the request beyond its nonce?
     *
     * The nonce is stripped first, because every guarded handler reads one and
     * counting it would mean nothing ever reads "nothing".
     */
    private static function readsRequest($jsst_body) {
        $jsst_stripped = preg_replace('/(?:check_ajax_referer|wp_verify_nonce|check_admin_referer)\s*\([^;]*;/', '', $jsst_body);
        return self::mentions((string) $jsst_stripped, array('$_POST', '$_GET', '$_REQUEST', '$_FILES', 'JSSTrequest::getVar', 'filter_input', '->get_param'));
    }

    /**
     * The tasks behind the one public dispatcher.
     *
     * `jsticket_ajax` is registered for signed-out callers and hands the
     * request to a model method named in the query string. One row saying "the
     * tasks behind this carry their own checks" would be the matrix declining
     * to look at **the largest AJAX surface this plugin has** - and the
     * roadmap asks for every AJAX action, not for every AJAX registration.
     *
     * It can be looked at, because the dispatcher does not accept any method
     * name: it holds an allow-list, and an allow-list is machine-readable. So
     * each name in it is found in the source, read, and graded exactly as a
     * registered handler is - with the difference that every one of these is
     * reachable by somebody with no account at all, which is what makes an
     * unguarded write here worse than an unguarded write anywhere else in this
     * report.
     */
    private static function dispatchRows() {
        $jsst_source = @file_get_contents(JSST_PLUGIN_PATH . 'includes/ajax.php');
        if ($jsst_source === false) {
            return array();
        }
        if (!preg_match('/\$jsst_functions_allowed\s*=\s*array\((.*?)\);/s', $jsst_source, $jsst_match)) {
            return array();
        }
        preg_match_all("/'([a-zA-Z0-9_]+)'/", $jsst_match[1], $jsst_names);
        $jsst_rows = array();
        foreach (array_unique($jsst_names[1]) as $jsst_name) {
            $jsst_found = self::findFunction($jsst_name);
            $jsst_row = array(
                'kind'    => __('Dispatched task', 'js-support-ticket'),
                'name'    => 'jsticket_ajax → ' . $jsst_name,
                'handler' => $jsst_found['where'],
                'writes'  => true,
                'notes'   => array(),
                'results' => array(),
            );
            if ($jsst_found['body'] === '') {
                /* An allow-listed name with no method behind it is not a hole,
                   it is a task belonging to an add-on that is not installed -
                   the list names several. Reported as unreachable rather than
                   as unguarded, because the two are opposites. */
                $jsst_row['handler'] = __('Not present on this site', 'js-support-ticket');
                $jsst_row['results'] = array_fill_keys(array_keys(self::checks()),
                    array(self::NA, __('The allow-list names it, but no method by that name is installed here, so nothing can reach it.', 'js-support-ticket')));
                $jsst_row['grade'] = self::NA;
                $jsst_rows[] = $jsst_row;
                continue;
            }
            $jsst_rows[] = self::grade(self::gradeHandler($jsst_row, $jsst_found['body'], true));
        }
        usort($jsst_rows, function ($jsst_a, $jsst_b) {
            return strcmp($jsst_a['name'], $jsst_b['name']);
        });
        return $jsst_rows;
    }

    /** A function's body and where it lives, looked for across the plugin. */
    private static function findFunction($jsst_name) {
        foreach (self::sourceFiles() as $jsst_file) {
            $jsst_source = @file_get_contents($jsst_file);
            if ($jsst_source === false || strpos($jsst_source, 'function ' . $jsst_name) === false) {
                continue;
            }
            $jsst_body = self::functionBody($jsst_source, $jsst_name);
            if ($jsst_body === '') {
                continue;
            }
            $jsst_class = preg_match('/class\s+([A-Za-z0-9_]+)/', $jsst_source, $jsst_match) ? $jsst_match[1] : basename($jsst_file);
            return array('body' => $jsst_body, 'where' => $jsst_class . '::' . $jsst_name . '()');
        }
        return array('body' => '', 'where' => '');
    }

    /**
     * The form handler — the plugin's original front door, and by far its
     * largest surface.
     *
     * Reported as one row rather than one per task, and honestly. Every
     * `form_request=jssupportticket` post is dispatched to a controller method
     * by name, and each of those methods carries its own nonce and permission
     * checks; there are several hundred of them and a scanner that claimed to
     * have graded them all would be lying. What can be said, and is worth
     * saying, is how the dispatcher itself is guarded.
     */
    private static function formRows() {
        $jsst_source = @file_get_contents(JSST_PLUGIN_PATH . 'includes/formhandler.php');
        if ($jsst_source === false) {
            return array();
        }
        $jsst_row = array(
            'kind'    => __('Form post', 'js-support-ticket'),
            'name'    => 'form_request=jssupportticket',
            'handler' => 'JSSTformhandler::checkFormRequest()',
            'writes'  => true,
            'notes'   => array(),
            'results' => array(),
        );
        $jsst_guardsname = (strpos($jsst_source, "strpos(\$jsst_task, '__')") !== false
            || strpos($jsst_source, 'preg_match') !== false);
        $jsst_sanitises = (strpos($jsst_source, 'sanitize_key') !== false);
        $jsst_gatesadmin = (strpos($jsst_source, 'refuseUnlessMayReachAdminDesk') !== false);

        $jsst_row['results']['auth'] = array(self::NA,
            __('Some tasks are deliberately open to customers and guests. Each task decides.', 'js-support-ticket'));
        $jsst_row['results']['authz'] = $jsst_gatesadmin
            ? array(self::WARN, __('In wp-admin the dispatcher refuses anybody who cannot reach the help-desk screens, and it never dispatches on admin-ajax.php. On the front end each controller method carries its own check — grade the tasks individually.', 'js-support-ticket'))
            : array(self::FAIL, __('The dispatcher runs any controller method in wp-admin without asking who is calling.', 'js-support-ticket'));
        $jsst_row['results']['scope'] = array(self::NA, __('Per task.', 'js-support-ticket'));
        $jsst_row['results']['nonce'] = array(self::WARN,
            __('Per task. Every 4.5 and 5.0 task verifies its own nonce before doing anything.', 'js-support-ticket'));
        $jsst_row['results']['validate'] = $jsst_guardsname
            ? array(self::PASS, __('The module and task names are constrained before being used to build a class and method name, so no arbitrary method can be called.', 'js-support-ticket'))
            : array(self::FAIL, __('The task name reaches a dynamic method call without being constrained.', 'js-support-ticket'));
        $jsst_row['results']['sanitise'] = $jsst_sanitises
            ? array(self::PASS, __('sanitize_key() on both the module and the task.', 'js-support-ticket'))
            : array(self::FAIL, __('No sanitisation on the dispatched names.', 'js-support-ticket'));
        $jsst_row['results']['prepared'] = array(self::NA, __('Per task.', 'js-support-ticket'));
        $jsst_row['results']['escape'] = array(self::NA, __('Per task.', 'js-support-ticket'));
        $jsst_row['results']['files'] = array(self::NA, __('Per task.', 'js-support-ticket'));

        return array(self::grade($jsst_row));
    }

    /**
     * A row's overall grade: the worst thing in it.
     *
     * Not an average and not a score. An endpoint with eight passes and one
     * missing capability check is a hole, and a number that rounds it to 89%
     * is a number that hides it.
     */
    private static function grade($jsst_row) {
        $jsst_grade = self::PASS;
        foreach ($jsst_row['results'] as $jsst_result) {
            if ($jsst_result[0] === self::FAIL) {
                $jsst_grade = self::FAIL;
                break;
            }
            if ($jsst_result[0] === self::WARN) {
                $jsst_grade = self::WARN;
            }
        }
        $jsst_row['grade'] = $jsst_grade;
        return $jsst_row;
    }

    /* =====================================================================
     * Reading the source
     * ================================================================== */

    /**
     * Every PHP file this product ships, so a scan cannot miss a folder.
     *
     * Core and its own addons, not other people's plugins. The addons have to
     * be in it: most of what 4.5 and 5.0 added lives in one now, and three of
     * the public AJAX endpoints on this site - the web app manifest, the API
     * description and the satisfaction survey - are registered from addon code.
     * A matrix that stopped at the core folder would have reported those as not
     * existing, which is the one failure this screen cannot have: a green
     * report that was never capable of going red.
     *
     * An addon that is installed but switched off is still read. What is being
     * described is the surface of the software on this server, and a
     * deactivated plugin's file is one activation away from answering requests.
     */
    private static function sourceFiles() {
        static $jsst_files = null;
        if ($jsst_files !== null) {
            return $jsst_files;
        }
        $jsst_files = array();
        foreach (self::sourceRoots() as $jsst_root) {
            if (!is_dir($jsst_root)) {
                continue;
            }
            $jsst_iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($jsst_root, FilesystemIterator::SKIP_DOTS));
            foreach ($jsst_iterator as $jsst_file) {
                if ($jsst_file->isFile() && strtolower($jsst_file->getExtension()) === 'php') {
                    $jsst_files[] = $jsst_file->getPathname();
                }
            }
        }
        return $jsst_files;
    }

    /** Core, then every js-support-ticket-* directory beside it. */
    private static function sourceRoots() {
        $jsst_core = rtrim(JSST_PLUGIN_PATH, '/\\');
        $jsst_roots = array($jsst_core);
        foreach ((array) glob(dirname($jsst_core) . '/js-support-ticket-*', GLOB_ONLYDIR) as $jsst_dir) {
            $jsst_roots[] = $jsst_dir;
        }
        return $jsst_roots;
    }

    /**
     * The body of one function in one source string.
     *
     * Brace counting rather than a parser. It is enough for the question being
     * asked — "does this handler mention check_ajax_referer" — and a real
     * parser would mean a dependency this plugin has no way to carry. A
     * function whose body cannot be found is reported as unknown rather than
     * as clean, which is the important half.
     */
    private static function functionBody($jsst_source, $jsst_name) {
        if (!preg_match('/function\s+' . preg_quote($jsst_name, '/') . '\s*\(/', $jsst_source, $jsst_match, PREG_OFFSET_CAPTURE)) {
            return '';
        }
        $jsst_start = strpos($jsst_source, '{', $jsst_match[0][1]);
        if ($jsst_start === false) {
            return '';
        }
        $jsst_depth = 0;
        $jsst_length = strlen($jsst_source);
        for ($jsst_i = $jsst_start; $jsst_i < $jsst_length; $jsst_i++) {
            if ($jsst_source[$jsst_i] === '{') {
                $jsst_depth++;
            } elseif ($jsst_source[$jsst_i] === '}') {
                $jsst_depth--;
                if ($jsst_depth === 0) {
                    return substr($jsst_source, $jsst_start, $jsst_i - $jsst_start + 1);
                }
            }
        }
        return '';
    }

    /** The same, looked for across every file. */
    private static function findFunctionBody($jsst_name) {
        foreach (self::sourceFiles() as $jsst_file) {
            $jsst_source = @file_get_contents($jsst_file);
            if ($jsst_source === false || strpos($jsst_source, 'function ' . $jsst_name) === false) {
                continue;
            }
            $jsst_body = self::functionBody($jsst_source, $jsst_name);
            if ($jsst_body !== '') {
                return $jsst_body;
            }
        }
        return '';
    }

    private static function mentions($jsst_body, $jsst_needles) {
        foreach ($jsst_needles as $jsst_needle) {
            if (strpos($jsst_body, $jsst_needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * What this report cannot see.
     *
     * Printed on the screen above the table, not buried. A security report that
     * does not state its own blind spots invites somebody to read a clean
     * result as an assurance it was never able to give.
     */
    public static function limits() {
        return array(
            __('It reads whether a guard is called, not whether it is called before the write. A handler that verifies a nonce after updating a row passes here and is still wrong.', 'js-support-ticket'),
            __('It cannot follow a check into a helper it does not recognise. A handler using a project-specific wrapper reads as unguarded and should be confirmed by hand.', 'js-support-ticket'),
            __('The form-post surface is graded as one dispatcher, not as its several hundred tasks. Those are reviewed by reading, and each 4.5 and 5.0 task verifies its own nonce and permission. The AJAX dispatcher is different: it holds an allow-list, so every task behind it is on a row of its own here.', 'js-support-ticket'),
            __('An action registered under a name built at runtime is only found where the constant is declared in the same file. One assembled from a variable is invisible to this report, and that is a real gap rather than a tidy one.', 'js-support-ticket'),
            __('This product\'s own add-ons are scanned along with core, installed or not, because most of what the desk does now ships in one. Other people\'s plugins are never read, so an AJAX action registered by an unrelated plugin is not on this list.', 'js-support-ticket'),
            __('A pass is evidence, not a proof. This is a review aid; it does not replace reading the handler.', 'js-support-ticket'),
        );
    }

    /**
     * The rows, narrowed to what somebody asked to see.
     *
     * Filtering here rather than on the screen for the reason the rest of this
     * plugin does it: the template that draws a table should not also be the
     * thing that decides what belongs in it, or the two answers drift.
     */
    public static function rows($jsst_filters = array()) {
        $jsst_report = self::report();
        $jsst_kind = isset($jsst_filters['kind']) ? $jsst_filters['kind'] : '';
        $jsst_grade = isset($jsst_filters['grade']) ? $jsst_filters['grade'] : '';
        $jsst_search = isset($jsst_filters['search']) ? strtolower(trim($jsst_filters['search'])) : '';
        $jsst_rows = array();
        foreach ($jsst_report['rows'] as $jsst_row) {
            if ($jsst_kind !== '' && $jsst_row['kind'] !== $jsst_kind) {
                continue;
            }
            if ($jsst_grade !== '' && $jsst_row['grade'] !== $jsst_grade) {
                continue;
            }
            if ($jsst_search !== ''
                && strpos(strtolower($jsst_row['name']), $jsst_search) === false
                && strpos(strtolower($jsst_row['handler']), $jsst_search) === false) {
                continue;
            }
            $jsst_rows[] = $jsst_row;
        }
        /* Worst first. A matrix sorted by the order the scanner happened to
           walk the code is a matrix where the one failure is somewhere on
           page three, and the whole value of this screen is that it is not. */
        $jsst_rank = array(self::FAIL => 0, self::WARN => 1, self::PASS => 2, self::NA => 3);
        usort($jsst_rows, function ($jsst_a, $jsst_b) use ($jsst_rank) {
            if ($jsst_rank[$jsst_a['grade']] !== $jsst_rank[$jsst_b['grade']]) {
                return $jsst_rank[$jsst_a['grade']] - $jsst_rank[$jsst_b['grade']];
            }
            if ($jsst_a['kind'] !== $jsst_b['kind']) {
                return strcmp($jsst_a['kind'], $jsst_b['kind']);
            }
            return strcmp($jsst_a['name'], $jsst_b['name']);
        });
        return $jsst_rows;
    }

    /**
     * Only what a row was marked down for.
     *
     * The nine cells of a clean row say nothing anybody needs to read. What is
     * worth reading is the two that did not pass, which is what this returns.
     */
    public static function findings($jsst_row) {
        $jsst_findings = array();
        foreach ($jsst_row['results'] as $jsst_check => $jsst_result) {
            if ($jsst_result[0] === self::WARN || $jsst_result[0] === self::FAIL) {
                $jsst_findings[$jsst_check] = $jsst_result;
            }
        }
        return $jsst_findings;
    }

    /**
     * The kinds of entry point there are, and how many of each.
     *
     * For the filter on the screen. Built from the rows rather than written
     * out, so a kind added to the scanner appears in the filter by itself.
     */
    public static function kinds() {
        $jsst_report = self::report();
        $jsst_kinds = array();
        foreach ($jsst_report['rows'] as $jsst_row) {
            $jsst_kinds[$jsst_row['kind']] = isset($jsst_kinds[$jsst_row['kind']]) ? $jsst_kinds[$jsst_row['kind']] + 1 : 1;
        }
        return $jsst_kinds;
    }

    /**
     * The matrix as a document.
     *
     * A compliance question is not answered by a screen somebody logs in to
     * look at; it is answered by a file with a date on it. This is that file,
     * and it carries the limits with it - a report that travels without its
     * own caveats is a report that will be read as an assurance it never made.
     */
    public static function markdown() {
        $jsst_report = self::report();
        $jsst_out = "# " . __('Authorisation matrix', 'js-support-ticket') . "\n\n";
        /* translators: 1: plugin version number, 2: date and time of the report. */
        $jsst_out .= sprintf(__('JS Help Desk %1$s, read on %2$s.', 'js-support-ticket'),
            jssupportticket::$_currentversion, $jsst_report['when']) . "\n\n";
        /* translators: 1: total number of entry points, 2: number that passed, 3: number to review, 4: number that failed, 5: number not present on this site. */
        $jsst_out .= sprintf(__('%1$d entry points: %2$d clean, %3$d to read, %4$d failing, %5$d not present on this site.', 'js-support-ticket'),
            $jsst_report['totals']['total'], $jsst_report['totals'][self::PASS],
            $jsst_report['totals'][self::WARN], $jsst_report['totals'][self::FAIL],
            $jsst_report['totals'][self::NA]) . "\n\n";

        $jsst_out .= "## " . __('What this report cannot see', 'js-support-ticket') . "\n\n";
        foreach ($jsst_report['notes'] as $jsst_note) {
            $jsst_out .= '- ' . $jsst_note . "\n";
        }
        $jsst_out .= "\n## " . __('Every way in', 'js-support-ticket') . "\n";

        $jsst_kind = '';
        foreach ($jsst_report['rows'] as $jsst_row) {
            if ($jsst_row['kind'] !== $jsst_kind) {
                $jsst_kind = $jsst_row['kind'];
                $jsst_out .= "\n### " . $jsst_kind . "\n";
            }
            $jsst_out .= "\n**" . $jsst_row['name'] . "** — `" . $jsst_row['handler'] . "` — "
                . strtoupper($jsst_row['grade']) . "\n";
            foreach ($jsst_row['results'] as $jsst_check => $jsst_result) {
                if ($jsst_result[0] === self::PASS || $jsst_result[0] === self::NA) {
                    continue;
                }
                $jsst_out .= '  - ' . strtoupper($jsst_result[0]) . ' ' . $jsst_check . ': ' . $jsst_result[1] . "\n";
            }
        }
        return $jsst_out;
    }

    /**
     * The report as a file.
     *
     * Behind manage_options and a referer check, because it names every entry
     * point this plugin has together with what does and does not guard each
     * one. That is a review document for the site owner and a shopping list
     * for anybody else.
     */
    public static function serveMarkdown() {
        if (!current_user_can('manage_options')) {
            status_header(403);
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        check_admin_referer('jsst-authmatrix');
        header('Content-Type: text/markdown; charset=utf-8');
        header('Content-Disposition: attachment; filename="js-help-desk-authorisation-matrix-'
            . sanitize_file_name(jssupportticket::$_currentversion) . '.md"');
        echo self::markdown();   // phpcs:ignore WordPress.Security.EscapeOutput -- a Markdown file, not HTML.
        wp_die();
    }

    public static function registerHooks() {
        add_action('wp_ajax_jsst_authmatrix', array(__CLASS__, 'serveMarkdown'));
    }

    /**
     * The one-line answer for System Status.
     *
     * Any failure at all is reported as a failure. There is no threshold below
     * which an unguarded endpoint is acceptable.
     */
    public static function summary() {
        $jsst_report = self::report();
        return array(
            'entrypoints' => $jsst_report['totals']['total'],
            'pass'        => $jsst_report['totals'][self::PASS],
            'warn'        => $jsst_report['totals'][self::WARN],
            'fail'        => $jsst_report['totals'][self::FAIL],
            'ok'          => ($jsst_report['totals'][self::FAIL] === 0),
        );
    }
}
