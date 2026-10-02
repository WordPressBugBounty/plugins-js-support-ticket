<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * Included from the bootstrap, which deduplicates by resolved path. Any route
 * that reaches this file by a second spelling of the same path would otherwise
 * redeclare the class and take the site down. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSThooks')) {
    return;
}

/**
 * What this product offers a developer, written down by reading the source.
 * (Roadmap 4.0-DATA-03)
 *
 * The plugin fires hundreds of actions and filters, defines a versioned event
 * contract, ships a REST API and self-heals a dozen tables. All of that is
 * already a public interface — somebody has already built against it — and none
 * of it was written down anywhere. A hand-written list would be wrong within a
 * release, which is the whole reason this one is generated:
 *
 *  - **The hooks are found by reading the files.** Every `do_action` and
 *    `apply_filters` in this plugin and its add-ons, with the arguments the
 *    call site passes, the file and line it fires from, and the comment above
 *    it where the author left one. A hook that is added, renamed or removed
 *    changes this page the moment the file changes.
 *  - **The event contract comes from JSSTevents' own catalogue**, so the
 *    version, the required payload keys and the legacy hook each event still
 *    fires are the ones the dispatcher actually uses.
 *  - **The schema history comes from the release SQL files and the schema
 *    guards**, which are the two things that really create tables here.
 *
 * Two honest limits, both stated on the screen rather than hidden:
 *
 * **A dynamic hook name is reported as a stem.** `do_action('jsst_event_' .
 * $name)` is listed as `jsst_event_*` — naming every possible expansion would
 * mean executing the code, and pretending the stem is the whole name would be
 * a lie a subscriber would find out about the hard way.
 *
 * **Arguments are the caller's own variable names.** They are what the source
 * says, which is more useful than a guess at their types and less useful than a
 * docblock nobody wrote. Where a comment sits directly above the call it is
 * shown as well, because that is the author's own description.
 */
class JSSThooks {

    /** Where the scan is kept, so a page load does not read four hundred files. */
    const OPT_CACHE = 'jsst_hooks_index';

    /** Bumped when the shape of the cached index changes. */
    const CACHE_VERSION = '500-DATA03.2';

    /** Files bigger than this are skipped: they are minified assets, not source. */
    const MAX_FILE = 2000000;

    private static $jsst_index = null;

    /* =====================================================================
     * The index
     * ================================================================== */

    /**
     * Every hook this product fires.
     *
     * Cached in an option because the scan reads several hundred files, and
     * refreshed when the cache was written by an older build, when the plugin
     * version has moved, or when somebody presses the button. It is not
     * refreshed on a timer: the source cannot change without one of those three
     * things being true.
     */
    public static function index($jsst_refresh = false) {
        if (self::$jsst_index !== null && !$jsst_refresh) {
            return self::$jsst_index;
        }
        $jsst_cached = get_option(self::OPT_CACHE, array());
        $jsst_stamp = self::stamp();
        if (!$jsst_refresh && is_array($jsst_cached)
            && isset($jsst_cached['stamp']) && $jsst_cached['stamp'] === $jsst_stamp) {
            self::$jsst_index = $jsst_cached;
            return self::$jsst_index;
        }
        self::$jsst_index = self::scan();
        self::$jsst_index['stamp'] = $jsst_stamp;
        update_option(self::OPT_CACHE, self::$jsst_index, false);
        return self::$jsst_index;
    }

    /** What the cache is keyed on: this build, and this list of scanned roots. */
    private static function stamp() {
        return self::CACHE_VERSION . '|' . jssupportticket::$_currentversion . '|' . md5(implode('|', array_keys(self::roots())));
    }

    /**
     * The directories to read.
     *
     * Core, plus every add-on directory of this product that is present -
     * whether or not it is switched on, because a developer reading this page
     * is asking what the product offers, not what this site happens to be
     * running today. Other people's plugins are never read.
     */
    public static function roots() {
        $jsst_roots = array();
        $jsst_core = rtrim(JSST_PLUGIN_PATH, '/\\');
        $jsst_roots[$jsst_core] = __('Core', 'js-support-ticket');
        $jsst_parent = dirname($jsst_core);
        $jsst_found = glob($jsst_parent . '/js-support-ticket-*', GLOB_ONLYDIR);
        foreach ((array) $jsst_found as $jsst_dir) {
            $jsst_slug = substr(basename($jsst_dir), strlen('js-support-ticket-'));
            $jsst_roots[$jsst_dir] = $jsst_slug;
        }
        return $jsst_roots;
    }

    /**
     * Read every PHP file under the roots and pull out the hooks.
     *
     * One pass, one regex, and the whole file in memory at a time - which is
     * fine for source files and is why anything unreasonably large is skipped
     * rather than parsed.
     */
    private static function scan() {
        $jsst_hooks = array();
        $jsst_files = 0;
        foreach (self::roots() as $jsst_root => $jsst_label) {
            foreach (self::phpFiles($jsst_root) as $jsst_file) {
                $jsst_files++;
                $jsst_source = @file_get_contents($jsst_file);
                if ($jsst_source === false) {
                    continue;
                }
                self::readFile($jsst_source, $jsst_file, $jsst_root, $jsst_label, $jsst_hooks);
            }
        }
        ksort($jsst_hooks);
        $jsst_actions = 0;
        $jsst_filters = 0;
        $jsst_sites = 0;
        $jsst_foreign = 0;
        foreach ($jsst_hooks as $jsst_hook) {
            $jsst_sites += count($jsst_hook['sites']);
            if (empty($jsst_hook['ours'])) {
                $jsst_foreign++;
                continue;   /* counted, but not as something this product offers */
            }
            if ($jsst_hook['kind'] === 'action') {
                $jsst_actions++;
            } else {
                $jsst_filters++;
            }
        }
        return array(
            'hooks'   => $jsst_hooks,
            'files'   => $jsst_files,
            'actions' => $jsst_actions,
            'filters' => $jsst_filters,
            'sites'   => $jsst_sites,
            'foreign' => $jsst_foreign,
            'when'    => current_time('mysql'),
        );
    }

    /** Every .php file under one directory. */
    private static function phpFiles($jsst_root) {
        $jsst_out = array();
        if (!is_dir($jsst_root)) {
            return $jsst_out;
        }
        try {
            $jsst_walker = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($jsst_root, FilesystemIterator::SKIP_DOTS));
            foreach ($jsst_walker as $jsst_file) {
                if (!$jsst_file->isFile() || strtolower($jsst_file->getExtension()) !== 'php') {
                    continue;
                }
                if ($jsst_file->getSize() > self::MAX_FILE) {
                    continue;
                }
                $jsst_out[] = $jsst_file->getPathname();
            }
        } catch (Exception $jsst_e) {
            /* An unreadable directory costs that directory, not the page. */
            return $jsst_out;
        }
        sort($jsst_out);
        return $jsst_out;
    }

    /** Pull the hooks out of one file's source. */
    private static function readFile($jsst_source, $jsst_file, $jsst_root, $jsst_label, &$jsst_hooks) {
        $jsst_pattern = '/\b(do_action|do_action_ref_array|apply_filters|apply_filters_ref_array)\s*\(\s*'
            . '([\'"])([a-zA-Z0-9_\-\.\/]+)\2(\s*\.\s*[^,\)]+)?/';
        if (!preg_match_all($jsst_pattern, $jsst_source, $jsst_matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return;
        }
        $jsst_relative = ltrim(str_replace(dirname($jsst_root), '', $jsst_file), '/\\');
        foreach ($jsst_matches as $jsst_match) {
            $jsst_call = $jsst_match[1][0];
            $jsst_name = $jsst_match[3][0];
            $jsst_dynamic = isset($jsst_match[4]) && $jsst_match[4][0] !== '';
            $jsst_key = $jsst_dynamic ? ($jsst_name . '*') : $jsst_name;
            $jsst_kind = (strpos($jsst_call, 'do_action') === 0) ? 'action' : 'filter';
            $jsst_offset = $jsst_match[0][1];
            $jsst_line = substr_count(substr($jsst_source, 0, $jsst_offset), "\n") + 1;

            if (!isset($jsst_hooks[$jsst_key])) {
                $jsst_hooks[$jsst_key] = array(
                    'name'    => $jsst_key,
                    'kind'    => $jsst_kind,
                    'dynamic' => $jsst_dynamic,
                    'ours'    => self::isOurs($jsst_name),
                    'args'    => array(),
                    'note'    => '',
                    'sites'   => array(),
                );
            }
            $jsst_args = self::argumentsAt($jsst_source, $jsst_offset);
            if ($jsst_args && !$jsst_hooks[$jsst_key]['args']) {
                $jsst_hooks[$jsst_key]['args'] = $jsst_args;
            }
            $jsst_note = self::commentAbove($jsst_source, $jsst_offset);
            if ($jsst_note !== '' && $jsst_hooks[$jsst_key]['note'] === '') {
                $jsst_hooks[$jsst_key]['note'] = $jsst_note;
            }
            $jsst_hooks[$jsst_key]['sites'][] = array(
                'file'  => $jsst_relative,
                'line'  => $jsst_line,
                'where' => $jsst_label,
            );
        }
    }

    /**
     * Is this one of ours?
     *
     * The scan finds every hook the code fires, which includes other people's:
     * this plugin re-applies `active_plugins`, fires Easy Digital Downloads'
     * own hooks where it extends that plugin's pages, and even fires a
     * competitor's `wpas_replies_post_type` where it reads their data during a
     * migration. Those are real call sites and worth being able to see, but
     * they are not extension points this product offers, and listing them
     * together would misrepresent both.
     *
     * The prefix list is longer than it looks like it should be because this
     * product has spelled its own name four ways over the years -
     * `jssupporticket` with the missing t among them - and a hook named ten
     * years ago is still one of ours.
     */
    private static function isOurs($jsst_name) {
        foreach (array('jsst', 'js_ticket', 'jshd', 'jssupportticket', 'js_support_ticket', 'jssupporticket') as $jsst_prefix) {
            if (stripos($jsst_name, $jsst_prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * The arguments a call site passes, as the source writes them.
     *
     * Read by walking forward from the hook name counting brackets, so a call
     * whose argument is itself a function call is not cut in half at its first
     * comma. A call spread over several lines is read whole; one that runs past
     * the guard is reported with what was found, because a truncated list is
     * more useful than none.
     */
    private static function argumentsAt($jsst_source, $jsst_offset) {
        $jsst_open = strpos($jsst_source, '(', $jsst_offset);
        if ($jsst_open === false) {
            return array();
        }
        $jsst_depth = 0;
        $jsst_buffer = '';
        $jsst_length = strlen($jsst_source);
        for ($jsst_i = $jsst_open; $jsst_i < $jsst_length && ($jsst_i - $jsst_open) < 2000; $jsst_i++) {
            $jsst_char = $jsst_source[$jsst_i];
            if ($jsst_char === '(') {
                $jsst_depth++;
                if ($jsst_depth === 1) {
                    continue;
                }
            } elseif ($jsst_char === ')') {
                $jsst_depth--;
                if ($jsst_depth === 0) {
                    break;
                }
            }
            $jsst_buffer .= $jsst_char;
        }
        /* Split on the commas that belong to this call - not the ones inside an
           array literal or a nested call. */
        $jsst_parts = array();
        $jsst_depth = 0;
        $jsst_current = '';
        $jsst_quote = '';
        $jsst_length = strlen($jsst_buffer);
        for ($jsst_i = 0; $jsst_i < $jsst_length; $jsst_i++) {
            $jsst_char = $jsst_buffer[$jsst_i];
            if ($jsst_quote !== '') {
                $jsst_current .= $jsst_char;
                if ($jsst_char === $jsst_quote && $jsst_buffer[$jsst_i - 1] !== '\\') {
                    $jsst_quote = '';
                }
                continue;
            }
            if ($jsst_char === '"' || $jsst_char === "'") {
                $jsst_quote = $jsst_char;
                $jsst_current .= $jsst_char;
                continue;
            }
            if ($jsst_char === '(' || $jsst_char === '[') {
                $jsst_depth++;
            } elseif ($jsst_char === ')' || $jsst_char === ']') {
                $jsst_depth--;
            }
            if ($jsst_char === ',' && $jsst_depth === 0) {
                $jsst_parts[] = $jsst_current;
                $jsst_current = '';
                continue;
            }
            $jsst_current .= $jsst_char;
        }
        $jsst_parts[] = $jsst_current;
        array_shift($jsst_parts);   /* the first one is the hook's own name */
        $jsst_clean = array();
        foreach ($jsst_parts as $jsst_part) {
            $jsst_part = trim(preg_replace('/\s+/', ' ', $jsst_part));
            if ($jsst_part !== '') {
                $jsst_clean[] = (strlen($jsst_part) > 80) ? (substr($jsst_part, 0, 77) . '...') : $jsst_part;
            }
        }
        return $jsst_clean;
    }

    /**
     * The comment immediately above a call, if the author left one.
     *
     * Only a comment that is directly above it, with nothing but whitespace
     * between: a comment three lines up is about something else, and attaching
     * it here would put a description on a hook it does not describe.
     */
    private static function commentAbove($jsst_source, $jsst_offset) {
        $jsst_before = substr($jsst_source, 0, $jsst_offset);
        $jsst_lines = explode("\n", $jsst_before);
        array_pop($jsst_lines);     /* the hook's own line, up to the call */
        $jsst_collected = array();
        for ($jsst_i = count($jsst_lines) - 1; $jsst_i >= 0 && count($jsst_collected) < 6; $jsst_i--) {
            $jsst_line = trim($jsst_lines[$jsst_i]);
            if ($jsst_line === '') {
                break;
            }
            if (preg_match('#^(//|/\*|\*/?|\#)#', $jsst_line)) {
                $jsst_collected[] = trim(preg_replace('#^(//+|/\*+|\*+/?|\#)\s?#', '', $jsst_line), " \t*/");
                continue;
            }
            break;
        }
        if (!$jsst_collected) {
            return '';
        }
        $jsst_collected = array_reverse($jsst_collected);
        $jsst_note = trim(preg_replace('/\s+/', ' ', implode(' ', $jsst_collected)));
        return (strlen($jsst_note) > 400) ? (substr($jsst_note, 0, 397) . '...') : $jsst_note;
    }

    /* =====================================================================
     * Ways of asking for it
     * ================================================================== */

    /** Hooks, optionally narrowed to actions or filters and to a search term. */
    public static function hooks($jsst_args = array()) {
        $jsst_args = array_merge(array('kind' => '', 'where' => '', 'search' => '', 'scope' => 'ours'), $jsst_args);
        $jsst_index = self::index();
        $jsst_out = array();
        foreach ($jsst_index['hooks'] as $jsst_key => $jsst_hook) {
            if ($jsst_args['scope'] !== 'all' && empty($jsst_hook['ours'])) {
                continue;
            }
            if ($jsst_args['kind'] !== '' && $jsst_hook['kind'] !== $jsst_args['kind']) {
                continue;
            }
            if ($jsst_args['where'] !== '' && !self::firedIn($jsst_hook, $jsst_args['where'])) {
                continue;
            }
            if ($jsst_args['search'] !== ''
                && stripos($jsst_key, $jsst_args['search']) === false
                && stripos($jsst_hook['note'], $jsst_args['search']) === false) {
                continue;
            }
            $jsst_out[$jsst_key] = $jsst_hook;
        }
        return $jsst_out;
    }

    private static function firedIn($jsst_hook, $jsst_where) {
        foreach ($jsst_hook['sites'] as $jsst_site) {
            if ($jsst_site['where'] === $jsst_where) {
                return true;
            }
        }
        return false;
    }

    /** Which plugins fire hooks, with how many each. */
    public static function places() {
        $jsst_index = self::index();
        $jsst_places = array();
        foreach ($jsst_index['hooks'] as $jsst_hook) {
            foreach ($jsst_hook['sites'] as $jsst_site) {
                if (!isset($jsst_places[$jsst_site['where']])) {
                    $jsst_places[$jsst_site['where']] = 0;
                }
                $jsst_places[$jsst_site['where']]++;
            }
        }
        arsort($jsst_places);
        return $jsst_places;
    }

    /**
     * The event contract, from the dispatcher's own catalogue.
     *
     * These are the hooks worth writing against first: they are versioned, they
     * carry a described payload, and unlike the rest of this page they are a
     * promise rather than a description of where the code happens to call out.
     * (Roadmap 4.5-ARCH-03)
     */
    public static function events() {
        if (!class_exists('JSSTevents')) {
            return array();
        }
        $jsst_out = array();
        foreach (JSSTevents::catalogue() as $jsst_name => $jsst_def) {
            $jsst_out[$jsst_name] = array(
                'name'     => $jsst_name,
                'version'  => isset($jsst_def['version']) ? (int) $jsst_def['version'] : 1,
                'label'    => isset($jsst_def['label']) ? $jsst_def['label'] : $jsst_name,
                'required' => isset($jsst_def['required']) ? (array) $jsst_def['required'] : array(),
                'optional' => isset($jsst_def['optional']) ? (array) $jsst_def['optional'] : array(),
                'legacy'   => isset($jsst_def['legacy']) ? $jsst_def['legacy'] : '',
            );
        }
        return $jsst_out;
    }

    /**
     * What each release did to the database, read from the two things that
     * actually do it: the release SQL files, and the self-healing guards.
     *
     * Not a hand-kept list. A release that adds a table without saying so here
     * is impossible, because this is generated from the file that adds it.
     */
    public static function schemaHistory() {
        $jsst_releases = array();
        $jsst_dir = JSST_PLUGIN_PATH . 'includes/updates/sql';
        foreach ((array) glob($jsst_dir . '/*.sql') as $jsst_file) {
            $jsst_code = basename($jsst_file, '.sql');
            $jsst_sql = @file_get_contents($jsst_file);
            if ($jsst_sql === false) {
                continue;
            }
            $jsst_created = array();
            $jsst_altered = array();
            /* "IF NOT EXISTS" is taken out before the table names are read
               rather than skipped in the pattern: an optional group in front of
               a name pattern is matched by the engine as the empty string, and
               every one of these files was reported as creating a table called
               IF. The tables are also written as `#__js_ticket_x`, the
               updater's own prefix placeholder, which is dropped here so the
               name reads the way the rest of the plugin spells it. */
            $jsst_sql = preg_replace('/\bIF\s+NOT\s+EXISTS\b/i', '', $jsst_sql);
            if (preg_match_all('/CREATE\s+TABLE\s+`?(?:#__)?([a-zA-Z0-9_]+)`?/i', $jsst_sql, $jsst_m)) {
                $jsst_created = array_values(array_unique($jsst_m[1]));
            }
            if (preg_match_all('/ALTER\s+TABLE\s+`?(?:#__)?([a-zA-Z0-9_]+)`?/i', $jsst_sql, $jsst_m)) {
                $jsst_altered = array_values(array_diff(array_unique($jsst_m[1]), $jsst_created));
            }
            $jsst_releases[$jsst_code] = array(
                'code'    => $jsst_code,
                'version' => self::readableVersion($jsst_code),
                'created' => $jsst_created,
                'altered' => $jsst_altered,
            );
        }
        /* Releases that touched no table are dropped rather than listed as
           empty rows: this is a schema history, and forty rows saying "nothing"
           bury the eight that say something. */
        foreach ($jsst_releases as $jsst_code => $jsst_release) {
            if (!$jsst_release['created'] && !$jsst_release['altered']) {
                unset($jsst_releases[$jsst_code]);
            }
        }
        krsort($jsst_releases);
        return $jsst_releases;
    }

    /** '450' is how the updater spells 4.5.0. */
    private static function readableVersion($jsst_code) {
        $jsst_digits = preg_replace('/[^0-9]/', '', $jsst_code);
        if (strlen($jsst_digits) < 3) {
            return $jsst_code;
        }
        return implode('.', str_split(substr($jsst_digits, 0, 3)));
    }

    /**
     * The tables that repair themselves, and the version each guard is at.
     *
     * Read from the class constants rather than from a list, because a guard
     * whose version is bumped without this page noticing would be a page that
     * lies about the schema. (Roadmap 4.5-ARCH-05, JSSTschemaguard)
     */
    public static function guards() {
        $jsst_out = array();
        /* Core's classes and every addon's, for the reason the hook scan reads
           both: most of what repairs itself now ships in an addon, and a page
           that listed only core's guards would say the schema repairs less than
           it does - which is worse than saying nothing, because somebody would
           believe it. */
        $jsst_files = array();
        foreach (self::roots() as $jsst_root => $jsst_label) {
            $jsst_files = array_merge(
                $jsst_files,
                (array) glob($jsst_root . '/includes/classes/*.php'),
                (array) glob($jsst_root . '/includes/*.php')
            );
        }
        foreach ($jsst_files as $jsst_file) {
            $jsst_source = @file_get_contents($jsst_file);
            if ($jsst_source === false || strpos($jsst_source, 'SCHEMA_VERSION') === false) {
                continue;
            }
            if (!preg_match('/class\s+(JSST[a-zA-Z]+)/', $jsst_source, $jsst_class)) {
                continue;
            }
            if (!preg_match('/const\s+SCHEMA_VERSION\s*=\s*[\'"]([^\'"]+)[\'"]/', $jsst_source, $jsst_version)) {
                continue;
            }
            $jsst_tables = array();
            if (preg_match_all('/[\'"](js_ticket_[a-z_]+)[\'"]\s*=>/', $jsst_source, $jsst_m)) {
                $jsst_tables = array_values(array_unique($jsst_m[1]));
            }
            $jsst_out[$jsst_class[1]] = array(
                'class'   => $jsst_class[1],
                'version' => $jsst_version[1],
                'tables'  => $jsst_tables,
            );
        }
        ksort($jsst_out);
        return $jsst_out;
    }

    /** The one-line answer for System Status. */
    public static function summary() {
        $jsst_index = self::index();
        return array(
            'actions' => (int) $jsst_index['actions'],
            'filters' => (int) $jsst_index['filters'],
            'sites'   => (int) $jsst_index['sites'],
            'files'   => (int) $jsst_index['files'],
            'events'  => count(self::events()),
            'foreign' => (int) (isset($jsst_index['foreign']) ? $jsst_index['foreign'] : 0),
            'when'    => $jsst_index['when'],
        );
    }

    /* =====================================================================
     * Publishing it
     * ================================================================== */

    /**
     * The whole thing as Markdown, ready to be pasted into a documentation
     * site. Markdown rather than HTML because it survives being pasted
     * anywhere, and generated rather than exported so that the published copy
     * and this screen can never disagree.
     */
    public static function markdown() {
        $jsst_index = self::index();
        $jsst_out = array();
        $jsst_out[] = '# JS Help Desk developer reference';
        $jsst_out[] = '';
        $jsst_out[] = sprintf('Generated from the source of version %s. %d actions and %d filters, fired from %d places in %d files.',
            jssupportticket::$_currentversion, $jsst_index['actions'], $jsst_index['filters'], $jsst_index['sites'], $jsst_index['files']);
        $jsst_out[] = '';
        $jsst_out[] = '## Events';
        $jsst_out[] = '';
        $jsst_out[] = 'Versioned, with a described payload. Subscribe with `add_action(\'jsst_event\', ...)` for all of them, or `jsst_event_<name>` for one.';
        $jsst_out[] = '';
        $jsst_out[] = '| Event | Version | Payload | Also fires |';
        $jsst_out[] = '| --- | --- | --- | --- |';
        foreach (self::events() as $jsst_event) {
            $jsst_out[] = sprintf('| `%s` | %d | %s | %s |',
                $jsst_event['name'], $jsst_event['version'],
                implode(', ', array_merge($jsst_event['required'], $jsst_event['optional'])),
                $jsst_event['legacy'] !== '' ? '`' . $jsst_event['legacy'] . '`' : '—');
        }
        $jsst_out[] = '';
        $jsst_out[] = '## Actions and filters';
        $jsst_out[] = '';
        $jsst_out[] = sprintf('The extension points this product offers. %d further hooks belonging to WordPress and to other plugins are fired from this code and are not listed here.',
            (int) (isset($jsst_index['foreign']) ? $jsst_index['foreign'] : 0));
        $jsst_out[] = '';
        foreach (self::hooks(array('scope' => 'ours')) as $jsst_hook) {
            $jsst_out[] = sprintf('### `%s`', $jsst_hook['name']);
            $jsst_out[] = '';
            $jsst_out[] = sprintf('%s%s',
                ($jsst_hook['kind'] === 'action') ? 'Action' : 'Filter',
                !empty($jsst_hook['dynamic']) ? ', with the rest of the name built at runtime' : '');
            $jsst_out[] = '';
            if ($jsst_hook['args']) {
                $jsst_out[] = '`' . implode('`, `', $jsst_hook['args']) . '`';
                $jsst_out[] = '';
            }
            if ($jsst_hook['note'] !== '') {
                $jsst_out[] = '> ' . $jsst_hook['note'];
                $jsst_out[] = '';
            }
            $jsst_lines = array();
            foreach (array_slice($jsst_hook['sites'], 0, 8) as $jsst_site) {
                $jsst_lines[] = $jsst_site['file'] . ':' . $jsst_site['line'];
            }
            if (count($jsst_hook['sites']) > 8) {
                $jsst_lines[] = sprintf('and %d more', count($jsst_hook['sites']) - 8);
            }
            $jsst_out[] = 'Fired from ' . implode(', ', $jsst_lines) . '.';
            $jsst_out[] = '';
        }
        $jsst_out[] = '## Schema by release';
        $jsst_out[] = '';
        $jsst_out[] = '| Release | Tables created | Tables altered |';
        $jsst_out[] = '| --- | --- | --- |';
        foreach (self::schemaHistory() as $jsst_release) {
            $jsst_out[] = sprintf('| %s | %s | %s |',
                $jsst_release['version'],
                $jsst_release['created'] ? implode(', ', $jsst_release['created']) : '—',
                $jsst_release['altered'] ? implode(', ', $jsst_release['altered']) : '—');
        }
        $jsst_out[] = '';
        return implode("\n", $jsst_out);
    }

    public static function registerHooks() {
        /* Downloadable rather than only readable, because a reference somebody
           has to copy out of a table by hand is one nobody publishes. Behind
           the same capability as the screen: it names file paths and internal
           hook names, which is developer material rather than public material.
           (Roadmap 4.0-DATA-03) */
        add_action('wp_ajax_jsst_hookdocs', array(__CLASS__, 'serveMarkdown'));
    }

    public static function serveMarkdown() {
        if (!current_user_can('manage_options')) {
            status_header(403);
            wp_die(esc_html__('You are not allowed', 'js-support-ticket'));
        }
        check_admin_referer('jsst-hookdocs');
        header('Content-Type: text/markdown; charset=utf-8');
        header('Content-Disposition: attachment; filename="js-help-desk-hooks-'
            . sanitize_file_name(jssupportticket::$_currentversion) . '.md"');
        echo self::markdown();   // phpcs:ignore WordPress.Security.EscapeOutput -- a Markdown file, not HTML.
        wp_die();
    }

    public static function flush() {
        self::$jsst_index = null;
        delete_option(self::OPT_CACHE);
    }
}
