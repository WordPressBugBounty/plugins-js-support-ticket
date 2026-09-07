<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTattachmentguard')) {
    return;
}


/**
 * Attachment protection. (Roadmap 4.0-SEC-03)
 *
 * Ticket attachments are customer data — screenshots of dashboards, invoices,
 * logs with credentials in them — stored under wp-content/uploads, which is a
 * web-readable directory. Three things keep them from being world-readable, and
 * all three are needed because each one fails on its own:
 *
 *  - A dropped .htaccess / web.config denies direct requests, but only on Apache
 *    and IIS. Nginx reads neither, and no file dropped in a directory can change
 *    that: there is no per-directory configuration to drop.
 *  - So every attachment is served by PHP instead, through a route that checks
 *    who is asking before it reads a byte. That is the protection that works on
 *    every server, and the dropped files are defence in depth behind it.
 *  - And the directory name is unguessable, so a leaked URL for one attachment
 *    does not hand over the rest of the ticket.
 *
 * The other half of the job is what gets stored in the first place: a file is
 * checked by its contents, not by what its name claims, and a scanner can refuse
 * it outright.
 */
class JSSTattachmentguard {

    /** Bumped when the protection files below change. */
    const GUARD_VERSION = '400';

    /** How many bytes of a file are sniffed when looking for embedded code. */
    const SNIFF_BYTES = 4096;

    /**
     * Extensions that are never accepted, whatever the site has configured.
     *
     * An administrator can widen the allowed types; they cannot widen them to
     * include something the server might execute. Note this is not the whole
     * defence — a .php file renamed to .png is caught by the content check
     * below, not by this list.
     */
    public static function forbiddenExtensions() {
        return apply_filters('jsst_attachment_forbidden_extensions', array(
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'phtml', 'phps', 'phar',
            'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'com', 'bat', 'cmd', 'exe', 'dll', 'so',
            'jsp', 'jspx', 'asp', 'aspx', 'ashx', 'asmx', 'cfm',
            'htaccess', 'htpasswd', 'ini', 'conf',
        ));
    }

    /**
     * Byte signatures that mean the file can be executed by something, whatever
     * its extension says.
     */
    private static function dangerousSignatures() {
        return array(
            '<?php'      => 'PHP',
            '<?='        => 'PHP',
            '<%'         => 'ASP',
            "\x7fELF"    => 'ELF',
            "MZ\x90"     => 'Windows executable',
            "\xca\xfe\xba\xbe" => 'Mach-O',
            '#!/'        => 'shell script',
        );
    }

    /**
     * Is this file name one that must never be stored?
     *
     * Catches the plain case and the double-extension case: "invoice.php.png"
     * has a harmless final extension, and on a server configured to hand any
     * path containing .php to the interpreter it is a live script.
     */
    public static function isDangerousName($jsst_name) {
        $jsst_name = strtolower(trim((string) $jsst_name));
        if ($jsst_name === '') {
            return true;
        }
        // A leading dot is a config file, not an attachment.
        if (strpos($jsst_name, '.') === 0) {
            return true;
        }
        $jsst_forbidden = array_map('strtolower', self::forbiddenExtensions());
        foreach (explode('.', $jsst_name) as $jsst_part) {
            if (in_array($jsst_part, $jsst_forbidden, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Read the first bytes of a file.
     *
     * Deliberately not the whole file: the signatures being looked for are at
     * the start, and a 40MB upload should not be pulled into memory to find out
     * it begins with "<?php".
     */
    private static function sniff($jsst_path) {
        if (!is_readable($jsst_path)) {
            return '';
        }
        $jsst_handle = @fopen($jsst_path, 'rb');
        if (!$jsst_handle) {
            return '';
        }
        $jsst_bytes = @fread($jsst_handle, self::SNIFF_BYTES);
        @fclose($jsst_handle);
        return ($jsst_bytes === false) ? '' : $jsst_bytes;
    }

    /**
     * Check an upload before it is stored.
     *
     * @param string $jsst_tmp_path   the uploaded temporary file
     * @param string $jsst_name       the file name as it will be stored
     * @return true|string true when the file may be stored, otherwise the
     *                     reason to show, already escaped for output.
     */
    public static function validate($jsst_tmp_path, $jsst_name) {
        if (self::isDangerousName($jsst_name)) {
            return esc_html(__('That file type is not allowed.', 'js-support-ticket'));
        }
        if (!is_file($jsst_tmp_path)) {
            return esc_html(__('The upload could not be read.', 'js-support-ticket'));
        }

        $jsst_bytes = self::sniff($jsst_tmp_path);
        $jsst_extension = strtolower((string) pathinfo($jsst_name, PATHINFO_EXTENSION));

        // 1. Anything that looks like code, whatever the name claims. This is
        //    what catches a PHP file uploaded as shot.png.
        if ($jsst_bytes !== '') {
            foreach (self::dangerousSignatures() as $jsst_signature => $jsst_label) {
                if (strpos($jsst_bytes, $jsst_signature) === 0 || ($jsst_signature === '<?php' && stripos($jsst_bytes, '<?php') !== false)) {
                    return esc_html(__('That file was refused because it contains program code.', 'js-support-ticket'));
                }
            }
        }

        // 2. An image has to actually be one. getimagesize() reads the header
        //    rather than trusting the extension, so a text file called .png is
        //    refused here even when it contains nothing executable.
        $jsst_imagetypes = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico');
        if (in_array($jsst_extension, $jsst_imagetypes, true)) {
            $jsst_info = @getimagesize($jsst_tmp_path);
            if ($jsst_info === false) {
                return esc_html(__('That file was refused because it is not the kind of image its name claims.', 'js-support-ticket'));
            }
        }

        // 3. SVG is an image that can carry script. It is only accepted when it
        //    carries none, because it is the one image type a browser will
        //    execute.
        if ($jsst_extension === 'svg' || $jsst_extension === 'svgz') {
            if (preg_match('/<script|on[a-z]+\s*=|javascript:|<foreignObject/i', $jsst_bytes)) {
                return esc_html(__('That drawing was refused because it contains script.', 'js-support-ticket'));
            }
        }

        /**
         * Malware scanning hook. (Roadmap 4.0-SEC-03)
         *
         * Return true to accept, or a string to refuse with that reason. The
         * temporary path is passed so a scanner can read the file before it is
         * anywhere permanent; nothing has been moved into place yet.
         */
        $jsst_scan = apply_filters('jsst_attachment_scan', true, $jsst_tmp_path, $jsst_name);
        if ($jsst_scan !== true) {
            return is_string($jsst_scan) && $jsst_scan !== ''
                    ? esc_html($jsst_scan)
                    : esc_html(__('That file was refused by the malware scanner on this site.', 'js-support-ticket'));
        }

        return true;
    }

    /**
     * A directory name that cannot be guessed from the ticket.
     *
     * The old name was seven letters with no repeated character, which is a much
     * smaller space than seven letters sounds, and it is the only thing between
     * a stranger and every attachment on a ticket. Existing tickets keep the
     * name stored on their row — nothing moves — so this only applies to
     * directories created from now on.
     */
    public static function randomFolderName() {
        if (function_exists('random_bytes')) {
            try {
                return bin2hex(random_bytes(16));
            } catch (Exception $jsst_e) {
                // fall through to the WordPress helper
            }
        }
        return strtolower(wp_generate_password(32, false, false));
    }

    /**
     * Is this a name this plugin could have produced for an attachment folder?
     *
     * It lives beside randomFolderName() deliberately. The two are one rule —
     * what we write, and what we are willing to read back — and when they were
     * apart they drifted: the check was written for the old seven-letter name
     * and was never widened when the names became 32 characters, so every
     * ticket created from 4.0 onwards failed a test on a folder this very file
     * had generated.
     *
     * Alphanumerics only, so a name can hold no separator, no dot and no
     * traversal, whatever length it is. Both shapes pass: the seven letters on
     * every existing ticket, and the 32 characters on every new one.
     */
    public static function isValidFolderName($jsst_name) {
        return is_string($jsst_name) && preg_match('/^[A-Za-z0-9]{7,64}$/', $jsst_name) === 1;
    }

    /**
     * The files that make a directory refuse to serve itself.
     *
     * index.php stops a listing on every server. The other two stop direct
     * requests on Apache and IIS; nothing dropped in a directory can do that on
     * Nginx, which is why serving goes through PHP.
     */
    private static function guardFiles() {
        return array(
            '.htaccess'  => "# Added by JS Help Desk. Ticket attachments are served by PHP after a\n"
                          . "# permission check; direct requests are refused. (Roadmap 4.0-SEC-03)\n"
                          . "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n"
                          . "<IfModule !mod_authz_core.c>\nOrder deny,allow\nDeny from all\n</IfModule>\n",
            'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration>\n  <system.webServer>\n"
                          . "    <authorization>\n      <deny users=\"*\" />\n    </authorization>\n"
                          . "  </system.webServer>\n</configuration>\n",
            'index.php'  => "<?php\n// Silence is golden.\n",
        );
    }

    /**
     * Write the protection files into one directory.
     *
     * Existing files are left alone: a site may have its own rules there, and
     * overwriting them would be a surprise with security consequences either
     * way. Uses WP_Filesystem when it is available and plain writes when it is
     * not, because this also runs on activation, where the filesystem API may
     * not be initialised.
     */
    public static function protectDirectory($jsst_dir) {
        if (empty($jsst_dir) || !is_dir($jsst_dir)) {
            return false;
        }
        global $wp_filesystem;
        foreach (self::guardFiles() as $jsst_file => $jsst_contents) {
            $jsst_target = rtrim($jsst_dir, '/\\') . '/' . $jsst_file;
            if (file_exists($jsst_target)) {
                continue;
            }
            if (is_object($wp_filesystem) && method_exists($wp_filesystem, 'put_contents')) {
                $wp_filesystem->put_contents($jsst_target, $jsst_contents, defined('FS_CHMOD_FILE') ? FS_CHMOD_FILE : 0644);
            } else {
                @file_put_contents($jsst_target, $jsst_contents);
            }
        }
        return true;
    }

    /**
     * The directories holding ticket attachments.
     *
     * Only ticket and note attachments. Knowledge-base images, downloads and
     * agent photos live elsewhere under the same data directory and may be
     * referenced by direct URL on purpose — denying those would break pages
     * rather than protect anything, and they are a different question from
     * customer ticket data.
     */
    public static function protectedRoots() {
        $jsst_uploads = wp_upload_dir();
        if (empty($jsst_uploads['basedir'])) {
            return array();
        }
        $jsst_data = isset(jssupportticket::$_config['data_directory']) ? jssupportticket::$_config['data_directory'] : '';
        if ($jsst_data === '') {
            return array();
        }
        $jsst_base = $jsst_uploads['basedir'] . '/' . $jsst_data . '/attachmentdata';
        return apply_filters('jsst_attachment_protected_roots', array(
            $jsst_base . '/ticket',
            $jsst_base . '/note',
        ));
    }

    /**
     * Put the protection in place.
     *
     * Called on activation, on upgrade and from the upload path. The option
     * guard keeps it to one filesystem check per site per release rather than
     * one per upload, but passing true forces it — which is what activation
     * does, so a site that has just changed its data directory is covered.
     */
    public static function ensureProtected($jsst_force = false) {
        if (!$jsst_force && get_option('jsst_attachment_guard') === self::GUARD_VERSION) {
            return;
        }
        foreach (self::protectedRoots() as $jsst_root) {
            if (!is_dir($jsst_root)) {
                wp_mkdir_p($jsst_root);
            }
            self::protectDirectory($jsst_root);
        }
        update_option('jsst_attachment_guard', self::GUARD_VERSION, false);
    }

}
