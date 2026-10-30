<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/* Safe to include more than once; see the note in attachmentguard.php. */
if (class_exists('JSSTattachmentserver')) {
    return;
}

/**
 * Sends a stored attachment to the browser. (5.0.3)
 *
 * Every attachment download and view goes through send(), after the caller has
 * decided the visitor may have the file. The file is read with plain PHP, not
 * WP_Filesystem: it sits in the uploads folder PHP itself wrote, and on a host
 * whose filesystem method is not "direct" WP_Filesystem() fails and the file
 * was never sent - the request fell through into WordPress, which answered
 * "Sorry, you are not allowed to access this page". The file is streamed, so a
 * large video does not have to fit in memory, and the request always ends here.
 */
class JSSTattachmentserver {

    /** Types that may be shown in the page; everything else is downloaded. */
    private static $jsst_inline_types = array(
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/quicktime',
        'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/x-wav',
        'application/pdf',
    );

    /** The folder every ticket, reply and note attachment lives under. */
    public static function attachmentRoot() {
        $jsst_upload = wp_upload_dir();
        return $jsst_upload['basedir'] . '/' . jssupportticket::$_config['data_directory'] . '/attachmentdata';
    }

    /**
     * Send $jsst_file and end the request.
     *
     * $jsst_args:
     *  - base:    folder the file must be inside (default: attachmentRoot())
     *  - inline:  show it in the page when the type allows (default false)
     *  - name:    file name offered to the browser (default: the file's own)
     *  - deleted: the attachment was removed by automatic clean-up
     *  - after:   callable run once the file has been sent (temporary files)
     */
    public static function send($jsst_file, $jsst_args = array()) {
        $jsst_args = wp_parse_args($jsst_args, array(
            'base' => self::attachmentRoot(),
            'inline' => false,
            'name' => '',
            'deleted' => false,
            'after' => null,
        ));
        if (!empty($jsst_args['deleted'])) {
            self::stop('purged', $jsst_file);
        }
        $jsst_realbase = realpath($jsst_args['base']);
        $jsst_realfile = realpath($jsst_file);
        if ($jsst_realbase === false || $jsst_realfile === false || !is_file($jsst_realfile)) {
            self::stop('missing', $jsst_file);
        }
        // realpath() answers in the system's separator - backslashes on Windows.
        if (strpos($jsst_realfile, rtrim($jsst_realbase, '/' . chr(92)) . DIRECTORY_SEPARATOR) !== 0) {
            JSSTincluder::notFound();
        }
        if (!is_readable($jsst_realfile)) {
            self::stop('unreadable', $jsst_file);
        }

        $jsst_name = $jsst_args['name'] !== '' ? $jsst_args['name'] : wp_basename($jsst_realfile);
        $jsst_type = $jsst_args['inline'] ? self::inlineType($jsst_realfile, $jsst_name) : '';
        $jsst_size = filesize($jsst_realfile);
        list($jsst_start, $jsst_end) = self::range($jsst_size);

        while (ob_get_level()) {
            ob_end_clean();
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        nocache_headers();
        header('Content-Type: ' . ($jsst_type !== '' ? $jsst_type : 'application/octet-stream'));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: ' . ($jsst_type !== '' ? 'inline' : 'attachment') . '; ' . self::fileNameHeader($jsst_name));
        header('Accept-Ranges: bytes');
        if ($jsst_start > 0 || $jsst_end < $jsst_size - 1) {
            status_header(206);
            header('Content-Range: bytes ' . $jsst_start . '-' . $jsst_end . '/' . $jsst_size);
        }
        header('Content-Length: ' . ($jsst_size > 0 ? $jsst_end - $jsst_start + 1 : 0));

        $jsst_handle = fopen($jsst_realfile, 'rb'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streams the file; WP_Filesystem would read it all into memory
        if ($jsst_handle) {
            fseek($jsst_handle, $jsst_start);
            $jsst_left = $jsst_size > 0 ? $jsst_end - $jsst_start + 1 : 0;
            while ($jsst_left > 0 && !feof($jsst_handle) && !connection_aborted()) {
                $jsst_chunk = fread($jsst_handle, (int) min(8192, $jsst_left)); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
                if ($jsst_chunk === false || $jsst_chunk === '') {
                    break;
                }
                echo $jsst_chunk; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- file bytes
                $jsst_left -= strlen($jsst_chunk);
                flush();
            }
            fclose($jsst_handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        }
        if (is_callable($jsst_args['after'])) {
            call_user_func($jsst_args['after']);
        }
        exit;
    }

    /**
     * The type to show the file as, or '' to download it.
     *
     * Images are confirmed by their header; other types by the file's content
     * where the server can read it, and always by a name that matches it. A
     * renamed file, SVG, HTML or anything not on the list is downloaded.
     */
    private static function inlineType($jsst_file, $jsst_name) {
        $jsst_check = wp_check_filetype($jsst_name);
        $jsst_type = (string) $jsst_check['type'];
        if (strpos($jsst_type, 'image/') === 0) {
            // Only the head of the file: getimagesize() on a large file that is
            // not really an image can read all of it and run out of memory.
            $jsst_head = (string) file_get_contents($jsst_file, false, null, 0, 262144); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            $jsst_image = $jsst_head !== '' ? @getimagesizefromstring($jsst_head) : false;
            if (!empty($jsst_image['mime']) && in_array($jsst_image['mime'], self::$jsst_inline_types, true)) {
                return $jsst_image['mime'];
            }
            return '';
        }
        if ($jsst_type === '' || !in_array($jsst_type, self::$jsst_inline_types, true)) {
            return '';
        }
        if (function_exists('finfo_open')) {
            $jsst_finfo = finfo_open(FILEINFO_MIME_TYPE);
            $jsst_real = $jsst_finfo ? (string) finfo_file($jsst_finfo, $jsst_file) : '';
            if ($jsst_finfo) {
                finfo_close($jsst_finfo);
            }
            // Same family is enough: servers disagree on wav, mov and the like.
            if ($jsst_real === '' || strtok($jsst_real, '/') !== strtok($jsst_type, '/')) {
                return '';
            }
        }
        return $jsst_type;
    }

    /** The byte range asked for (one range only), or the whole file. */
    private static function range($jsst_size) {
        $jsst_start = 0;
        $jsst_end = max(0, $jsst_size - 1);
        $jsst_header = isset($_SERVER['HTTP_RANGE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_RANGE'])) : '';
        if ($jsst_size > 0 && preg_match('/^bytes=(\d*)-(\d*)$/', $jsst_header, $jsst_m) && ($jsst_m[1] !== '' || $jsst_m[2] !== '')) {
            if ($jsst_m[1] === '') {
                $jsst_start = max(0, $jsst_size - (int) $jsst_m[2]);
            } else {
                $jsst_start = (int) $jsst_m[1];
                if ($jsst_m[2] !== '') {
                    $jsst_end = min($jsst_end, (int) $jsst_m[2]);
                }
            }
            if ($jsst_start > $jsst_end) {
                status_header(416);
                header('Content-Range: bytes */' . $jsst_size);
                exit;
            }
        }
        return array($jsst_start, $jsst_end);
    }

    /** filename="..." plus filename*=UTF-8''... for names that are not plain ASCII. */
    private static function fileNameHeader($jsst_name) {
        $jsst_name = str_replace(array('"', chr(92), "\r", "\n"), '', wp_basename($jsst_name));
        $jsst_ascii = preg_replace('/[^\x20-\x7E]/u', '_', $jsst_name);
        if ($jsst_ascii === null) {
            $jsst_ascii = preg_replace('/[^\x20-\x7E]/', '_', $jsst_name);
        }
        return 'filename="' . $jsst_ascii . '"; filename*=UTF-8\'\'' . rawurlencode($jsst_name);
    }

    /** True for the people who look after tickets; they get the details. */
    private static function isStaff() {
        if (!is_user_logged_in()) {
            return false;
        }
        if (current_user_can('manage_options') || current_user_can('jsst_support_ticket_tickets')) {
            return true;
        }
        return in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff();
    }

    /**
     * Say why the file was not sent, log it, and end the request.
     *
     * Staff are told what is wrong and where; a customer only that the file
     * is not available, without the server path.
     */
    private static function stop($jsst_reason, $jsst_file) {
        $jsst_relative = ltrim(str_replace(wp_normalize_path(self::attachmentRoot()), '', wp_normalize_path($jsst_file)), '/');
        if ($jsst_reason !== 'purged') {
            error_log('JS Help Desk: attachment ' . $jsst_reason . ' - ' . $jsst_file); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        }
        $jsst_status = 404;
        if ($jsst_reason === 'purged') {
            $jsst_message = __('This attachment was removed automatically to save space.', 'js-support-ticket');
        } elseif (!self::isStaff()) {
            $jsst_message = __('This file is no longer available.', 'js-support-ticket');
        } elseif ($jsst_reason === 'unreadable') {
            $jsst_status = 403;
            $jsst_message = sprintf(
                /* translators: %s: the file's path inside the help desk attachment folder */
                __('The server does not allow WordPress to read this attachment\'s file: %s', 'js-support-ticket'),
                $jsst_relative
            );
        } else {
            $jsst_message = sprintf(
                /* translators: %s: the file's path inside the help desk attachment folder */
                __('This attachment\'s file is missing on the server: %s', 'js-support-ticket'),
                $jsst_relative
            );
        }
        $jsst_back = wp_get_referer();
        wp_die(
            esc_html($jsst_message),
            esc_html__('Attachment not available', 'js-support-ticket'),
            array('response' => $jsst_status, 'link_url' => $jsst_back ? esc_url_raw($jsst_back) : '', 'link_text' => $jsst_back ? esc_html__('Back to the ticket', 'js-support-ticket') : '')
        );
    }

    /** The named files that are really inside $jsst_dir and readable, as full paths. */
    public static function existingFiles($jsst_dir, $jsst_names) {
        $jsst_realdir = realpath($jsst_dir);
        if ($jsst_realdir === false) {
            return array();
        }
        $jsst_prefix = rtrim($jsst_realdir, '/' . chr(92)) . DIRECTORY_SEPARATOR;
        $jsst_files = array();
        foreach ((array) $jsst_names as $jsst_name) {
            $jsst_real = realpath($jsst_prefix . wp_basename((string) $jsst_name));
            if ($jsst_real !== false && strpos($jsst_real, $jsst_prefix) === 0 && is_file($jsst_real) && is_readable($jsst_real)) {
                $jsst_files[$jsst_real] = $jsst_real;
            }
        }
        return array_values($jsst_files);
    }

    /** A "download all" with no file left to put in the zip; ends the request. */
    public static function nothingToZip() {
        $jsst_back = wp_get_referer();
        wp_die(
            esc_html__('None of these attachments are available any more.', 'js-support-ticket'),
            esc_html__('Attachment not available', 'js-support-ticket'),
            array('response' => 404, 'link_url' => $jsst_back ? esc_url_raw($jsst_back) : '', 'link_text' => $jsst_back ? esc_html__('Back to the ticket', 'js-support-ticket') : '')
        );
    }

    /** Make a folder for a zip download, with plain PHP so it works on every host. */
    public static function makeTempFolder($jsst_folder) {
        if (!is_dir($jsst_folder)) {
            wp_mkdir_p($jsst_folder);
        }
        if (is_dir($jsst_folder) && !file_exists($jsst_folder . '/index.html')) {
            @file_put_contents($jsst_folder . '/index.html', ''); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        }
    }

    /**
     * The real path of a ticket attachment folder, or of a file in it - only
     * when it is inside the ticket attachment folder. Folder and file are
     * reduced to bare names first, so "../" in a stored value goes nowhere.
     */
    private static function insideTicketFolder($jsst_folder, $jsst_file = null) {
        $jsst_folder = wp_basename((string) $jsst_folder);
        if ($jsst_folder === '' || $jsst_folder === '.' || $jsst_folder === '..') {
            return false;
        }
        $jsst_path = self::attachmentRoot() . '/ticket/' . $jsst_folder;
        if ($jsst_file !== null) {
            $jsst_file = wp_basename((string) $jsst_file);
            if ($jsst_file === '' || $jsst_file === '.' || $jsst_file === '..') {
                return false;
            }
            $jsst_path .= '/' . $jsst_file;
        }
        $jsst_base = realpath(self::attachmentRoot() . '/ticket');
        $jsst_real = realpath($jsst_path);
        if ($jsst_base === false || $jsst_real === false) {
            return false;
        }
        return (strpos($jsst_real, rtrim($jsst_base, '/' . chr(92)) . DIRECTORY_SEPARATOR) === 0) ? $jsst_real : false;
    }

    /**
     * Delete one attachment file of a ticket. Plain PHP: on a host without
     * direct file access the global $wp_filesystem is an FTP object that never
     * connected, and calling it ended the request with a fatal error.
     *
     * @return bool True when the file is gone (deleted now, or not there).
     */
    public static function deleteTicketFile($jsst_folder, $jsst_file) {
        $jsst_real = self::insideTicketFolder($jsst_folder, $jsst_file);
        if ($jsst_real === false) {
            return false;
        }
        if (is_file($jsst_real)) {
            wp_delete_file($jsst_real);
        }
        return !file_exists($jsst_real);
    }

    /**
     * Delete a ticket's attachment folder and everything in it. Plain PHP,
     * for the same reason as deleteTicketFile().
     *
     * @return bool True when the folder is gone.
     */
    public static function deleteTicketFolder($jsst_folder) {
        $jsst_real = self::insideTicketFolder($jsst_folder);
        if ($jsst_real === false || !is_dir($jsst_real)) {
            return false;
        }
        $jsst_items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($jsst_real, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($jsst_items as $jsst_item) {
            if ($jsst_item->isDir() && !$jsst_item->isLink()) {
                @rmdir($jsst_item->getPathname()); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- uploads folder; WP_Filesystem fails on non-direct hosts
            } else {
                wp_delete_file($jsst_item->getPathname());
            }
        }
        @rmdir($jsst_real); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- uploads folder; WP_Filesystem fails on non-direct hosts
        return !is_dir($jsst_real);
    }

    /** Remove a temporary folder made for a zip download; plain PHP, like the rest. */
    public static function removeTempFolder($jsst_folder) {
        $jsst_realbase = realpath(JSST_PLUGIN_PATH . 'zipdownloads');
        $jsst_real = realpath($jsst_folder);
        if ($jsst_realbase === false || $jsst_real === false || strpos($jsst_real, rtrim($jsst_realbase, '/' . chr(92)) . DIRECTORY_SEPARATOR) !== 0) {
            return;
        }
        foreach ((array) glob(trailingslashit($jsst_real) . '*') as $jsst_item) {
            if (is_file($jsst_item)) {
                wp_delete_file($jsst_item);
            }
        }
        @rmdir($jsst_real); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
    }
}
