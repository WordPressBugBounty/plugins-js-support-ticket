<?php
if (!defined('ABSPATH')) die('Restricted Access');

class JSSTzywrapModel {

    function saveApiKey() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'save_api_key')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }
        
        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        $api_key = JSSTrequest::getVar('api_key');
        
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API Key cannot be empty', 'js-support-ticket')));
        }

        update_option('jsst_zywrap_api_key', sanitize_text_field($api_key));
        wp_send_json_success(array('message' => __('API Key saved successfully.', 'js-support-ticket')));
    }

    
    function savePreferences() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'save_preferences')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }
        
        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        // Capture fields
        $default_model = JSSTrequest::getVar('default_model');
        $default_lang = JSSTrequest::getVar('default_lang', 'English');

        // Save safely
        update_option('jsst_zywrap_default_model', sanitize_text_field($default_model));
        update_option('jsst_zywrap_default_lang', sanitize_text_field($default_lang));

        // Return success
        wp_send_json_success(array('message' => __('Preferences saved successfully.', 'js-support-ticket')));
    }

    function syncDataBundle() {
        // NONCE SECURITY CHECK
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'sync_data_bundle')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }

        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        $api_key = get_option('jsst_zywrap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('Please save your API key first.', 'js-support-ticket')));
        }

        @ini_set('memory_limit', '768M');
        @set_time_limit(700);

        // Fetch the local version to see if we qualify for a Delta Update
        $local_version = get_option('jsst_zywrap_data_version', '');
        
        $sync_url = 'https://api.zywrap.com/v1/sdk/v1/sync?fromVersion=' . urlencode($local_version);
        $response = wp_remote_get($sync_url, array(
            'timeout' => 600,
            'sslverify' => false,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Accept' => 'application/json'
            )
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Sync failed', 'js-support-ticket') . ': ' . $response->get_error_message()));
        }

        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            wp_send_json_error(array('message' => __('API Error: Invalid response code ', 'js-support-ticket') . $http_code));
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);
        if (!$json) {
            wp_send_json_error(array('message' => __('Failed to parse Sync JSON data.', 'js-support-ticket')));
        }

        $mode = isset($json['mode']) ? $json['mode'] : 'UNKNOWN';

        if ($mode === 'FULL_RESET') {
            // --- SCENARIO A: FULL RESET (Streaming Download & Replace All) ---
            $download_url = isset($json['wrappers']['downloadUrl']) ? $json['wrappers']['downloadUrl'] : 'https://api.zywrap.com/v1/sdk/v1/download';
            
            // 1. Define safe paths in the uploads directory (Guaranteed write permissions)
            $upload_dir = wp_upload_dir();
            $temp_file = trailingslashit($upload_dir['basedir']) . 'zywrap_bundle_' . time() . '.zip';
            $extract_path = trailingslashit($upload_dir['basedir']) . 'jsst-zywrap-temp';

            // 2. Stream the download directly to the disk (Bypasses RAM limits)
            $zip_response = wp_remote_get($download_url, array(
                'timeout'   => 600, // Generous timeout for large files
                'sslverify' => false,
                'headers'   => array('Authorization' => 'Bearer ' . $api_key),
                'stream'    => true,
                'filename'  => $temp_file
            ));

            if (is_wp_error($zip_response)) {
                // Standardized WordPress way to delete a file
                wp_delete_file($temp_file);
                wp_send_json_error(array('message' => __('Download failed: ', 'js-support-ticket') . $zip_response->get_error_message()));
            }

            $response_code = wp_remote_retrieve_response_code($zip_response);
            if ($response_code !== 200) {
                wp_delete_file($temp_file);
                wp_send_json_error(array('message' => __('Download rejected. HTTP Code:', 'js-support-ticket') . ' ' . $response_code));
            }

            // 3. Initialize WordPress Filesystem
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
            }

            // 4. Prepare extraction folder
            if ($wp_filesystem->exists($extract_path)) {
                $wp_filesystem->rmdir($extract_path, true);
            }
            wp_mkdir_p($extract_path);

            // 5. Unzip the file
            $unzip_result = unzip_file($temp_file, $extract_path);
            
            // Always clean up the temp zip file immediately after extracting
            // Standardized WordPress way to delete a file
            wp_delete_file($temp_file);

            if (is_wp_error($unzip_result)) {
                wp_send_json_error(array('message' => __('Unzip failed:', 'js-support-ticket') . ' ' . $unzip_result->get_error_message()));
            }

            // 6. Process the JSON data
            $json_file = trailingslashit($extract_path) . 'zywrap-data.json';
            if (!file_exists($json_file)) {
                 wp_send_json_error(array('message' => __('zywrap-data.json not found in bundle.', 'js-support-ticket')));
            }

            $json_data = file_get_contents($json_file);
            $data = json_decode($json_data, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(array('message' => __('Failed to parse bundle JSON data.', 'js-support-ticket')));
            }

            // 7. Save to Database
            $this->process_full_sync($data);
            if (isset($data['version'])) {
                update_option('jsst_zywrap_data_version', sanitize_text_field($data['version']));
            }
            
            // Clean up the extraction folder
            $wp_filesystem->rmdir($extract_path, true);

        } elseif ($mode === 'DELTA_UPDATE') {
            // --- SCENARIO B: DELTA UPDATE (Fast Upsert & Reconcile) ---
            $this->process_delta_sync($json);
            if (!empty($json['newVersion'])) {
                update_option('jsst_zywrap_data_version', sanitize_text_field($json['newVersion']));
            }
        } else {
             wp_send_json_error(array('message' => __('Unknown Sync Mode.', 'js-support-ticket')));
        }

        update_option('jsst_zywrap_last_sync', time());

        $clean_mode = str_replace('_', ' ', $mode);
        wp_send_json_success(array('message' => __('AI Data Synced Successfully!', 'js-support-ticket') . ' (' . __('Mode', 'js-support-ticket') . ': ' . $clean_mode . ')'));
    }

    private function process_full_sync($data) {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";

        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_categories`");
        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_use_cases`");
        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_wrappers`");
        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_ai_models`");
        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_languages`");
        jssupportticket::$_db->query("TRUNCATE TABLE `" . $prefix . "zywrap_block_templates`");

        if (!empty($data['categories'])) {
            $cats = $this->extract_tabular($data['categories']);
            foreach ($cats as $c) {
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_categories` (`code`, `name`, `ordering`) VALUES (
                    '" . esc_sql($c['code']) . "', '" . esc_sql($c['name']) . "', " . (int)($c['ordering'] ?? 9999) . "
                )";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        // ---------------------------------------------------------
        // BATCH INSERT: USE CASES
        // ---------------------------------------------------------
        if (!empty($data['useCases'])) {
            $ucs = $this->extract_tabular($data['useCases']);
            $chunk_size = 500; // Safe chunk size to respect MySQL max_allowed_packet
            $chunks = array_chunk($ucs, $chunk_size);
            
            foreach ($chunks as $chunk) {
                $values = array();
                foreach ($chunk as $uc) {
                    $schemaJson = !empty($uc['schema']) ? wp_json_encode($uc['schema']) : null;
                    $values[] = "(
                        '" . esc_sql($uc['code']) . "', 
                        '" . esc_sql($uc['name']) . "', 
                        '" . esc_sql($uc['desc'] ?? '') . "', 
                        '" . esc_sql($uc['cat'] ?? '') . "', 
                        '" . esc_sql($schemaJson) . "', 
                        " . (int)($uc['ordering'] ?? 9999) . "
                    )";
                }
                
                // Construct a single query with multiple values
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_use_cases` (`code`, `name`, `description`, `category_code`, `schema_data`, `ordering`) VALUES " . implode(', ', $values);
                jssupportticket::$_db->query($jsst_query);
            }
        }

        // ---------------------------------------------------------
        // BATCH INSERT: WRAPPERS (Massive Dataset Optimization)
        // ---------------------------------------------------------
        if (!empty($data['wrappers'])) {
            $wrappers = $this->extract_tabular($data['wrappers']);
            $chunk_size = 1000; // Grouping 1000 wrappers per query
            $chunks = array_chunk($wrappers, $chunk_size);
            
            foreach ($chunks as $chunk) {
                $values = array();
                foreach ($chunk as $w) {
                    $values[] = "(
                        '" . esc_sql($w['code']) . "', 
                        '" . esc_sql($w['name']) . "', 
                        '" . esc_sql($w['desc'] ?? '') . "', 
                        '" . esc_sql($w['usecase'] ?? '') . "', 
                        " . (!empty($w['featured']) ? 1 : 0) . ", 
                        " . (!empty($w['base']) ? 1 : 0) . ", 
                        " . (int)($w['ordering'] ?? 9999) . "
                    )";
                }
                
                // Construct a single query with 1000 rows
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_wrappers` (`code`, `name`, `description`, `use_case_code`, `featured`, `base`, `ordering`) VALUES " . implode(', ', $values);
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($data['aiModels'])) {
            $models = $this->extract_tabular($data['aiModels']);
            foreach ($models as $m) {
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_ai_models` (`code`, `name`, `ordering`) VALUES (
                    '" . esc_sql($m['code']) . "', '" . esc_sql($m['name']) . "', " . (int)($m['ordering'] ?? 9999) . "
                )";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($data['languages'])) {
            $langs = $this->extract_tabular($data['languages']);
            foreach ($langs as $l) {
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_languages` (`code`, `name`, `ordering`) VALUES (
                    '" . esc_sql($l['code']) . "', '" . esc_sql($l['name']) . "', " . (int)($l['ordering'] ?? 9999) . "
                )";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($data['templates'])) {
            foreach ($data['templates'] as $type => $tabular) {
                $templates = $this->extract_tabular($tabular);
                foreach ($templates as $t) {
                    $jsst_query = "INSERT INTO `" . $prefix . "zywrap_block_templates` (`type`, `code`, `name`) VALUES (
                        '" . esc_sql($type) . "', '" . esc_sql($t['code']) . "', '" . esc_sql($t['name']) . "'
                    )";
                    jssupportticket::$_db->query($jsst_query);
                }
            }
        }
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
    }

    private function process_delta_sync($json) {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";

        if (!empty($json['metadata']['categories'])) {
            foreach ($json['metadata']['categories'] as $r) {
                $status = (!isset($r['status']) || $r['status']) ? 1 : 0;
                $ordering = $r['position'] ?? $r['displayOrder'] ?? $r['ordering'] ?? 9999;
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_categories` (`code`, `name`, `status`, `ordering`) 
                               VALUES ('" . esc_sql($r['code']) . "', '" . esc_sql($r['name']) . "', " . (int)$status . ", " . (int)$ordering . ") 
                               ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`), `ordering`=VALUES(`ordering`)";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['metadata']['languages'])) {
            foreach ($json['metadata']['languages'] as $r) {
                $status = (!isset($r['status']) || $r['status']) ? 1 : 0;
                $ordering = $r['ordering'] ?? 9999;
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_languages` (`code`, `name`, `status`, `ordering`) 
                               VALUES ('" . esc_sql($r['code']) . "', '" . esc_sql($r['name']) . "', " . (int)$status . ", " . (int)$ordering . ") 
                               ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`), `ordering`=VALUES(`ordering`)";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['metadata']['aiModels'])) {
            foreach ($json['metadata']['aiModels'] as $r) {
                $status = (!isset($r['status']) || $r['status']) ? 1 : 0;
                $ordering = $r['displayOrder'] ?? $r['ordering'] ?? 9999;
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_ai_models` (`code`, `name`, `status`, `ordering`) 
                               VALUES ('" . esc_sql($r['code']) . "', '" . esc_sql($r['name']) . "', " . (int)$status . ", " . (int)$ordering . ") 
                               ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`), `ordering`=VALUES(`ordering`)";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['metadata']['templates'])) {
            foreach ($json['metadata']['templates'] as $type => $items) {
                foreach ($items as $item) {
                    $status = (!isset($item['status']) || $item['status']) ? 1 : 0;
                    $name = $item['label'] ?? $item['name'] ?? '';
                    $jsst_query = "INSERT INTO `" . $prefix . "zywrap_block_templates` (`type`, `code`, `name`, `status`) 
                                   VALUES ('" . esc_sql($type) . "', '" . esc_sql($item['code']) . "', '" . esc_sql($name) . "', " . (int)$status . ") 
                                   ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `status`=VALUES(`status`)";
                    jssupportticket::$_db->query($jsst_query);
                }
            }
        }

        if (!empty($json['useCases']['upserts'])) {
            foreach ($json['useCases']['upserts'] as $uc) {
                $schemaJson = !empty($uc['schema']) ? wp_json_encode($uc['schema']) : null;
                $status = (!isset($uc['status']) || $uc['status']) ? 1 : 0;
                $ordering = $uc['displayOrder'] ?? $uc['ordering'] ?? 9999;
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_use_cases` (`code`, `name`, `description`, `category_code`, `schema_data`, `status`, `ordering`) 
                               VALUES ('" . esc_sql($uc['code']) . "', '" . esc_sql($uc['name']) . "', '" . esc_sql($uc['description'] ?? '') . "', '" . esc_sql($uc['categoryCode'] ?? '') . "', '" . esc_sql($schemaJson) . "', " . (int)$status . ", " . (int)$ordering . ") 
                               ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `category_code`=VALUES(`category_code`), `schema_data`=VALUES(`schema_data`), `status`=VALUES(`status`), `ordering`=VALUES(`ordering`)";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['useCases']['deletes'])) {
            foreach ($json['useCases']['deletes'] as $code) {
                $jsst_query = "DELETE FROM `" . $prefix . "zywrap_use_cases` WHERE `code` = '" . esc_sql($code) . "'";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['wrappers']['upserts'])) {
            foreach ($json['wrappers']['upserts'] as $w) {
                $featured = !empty($w['featured'] ?? $w['isFeatured']) ? 1 : 0;
                $base = !empty($w['base'] ?? $w['isBaseWrapper']) ? 1 : 0;
                $status = (!isset($w['status']) || $w['status']) ? 1 : 0;
                $ordering = $w['displayOrder'] ?? $w['ordering'] ?? 9999;
                $jsst_query = "INSERT INTO `" . $prefix . "zywrap_wrappers` (`code`, `name`, `description`, `use_case_code`, `featured`, `base`, `status`, `ordering`) 
                               VALUES ('" . esc_sql($w['code']) . "', '" . esc_sql($w['name']) . "', '" . esc_sql($w['description'] ?? '') . "', '" . esc_sql($w['useCaseCode'] ?? $w['categoryCode'] ?? '') . "', " . (int)$featured . ", " . (int)$base . ", " . (int)$status . ", " . (int)$ordering . ") 
                               ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `use_case_code`=VALUES(`use_case_code`), `featured`=VALUES(`featured`), `base`=VALUES(`base`), `status`=VALUES(`status`), `ordering`=VALUES(`ordering`)";
                jssupportticket::$_db->query($jsst_query);
            }
        }

        if (!empty($json['wrappers']['deletes'])) {
            foreach ($json['wrappers']['deletes'] as $code) {
                $jsst_query = "DELETE FROM `" . $prefix . "zywrap_wrappers` WHERE `code` = '" . esc_sql($code) . "'";
                jssupportticket::$_db->query($jsst_query);
            }
        }
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
    }


    private function extract_tabular($tabularData) {
        if (empty($tabularData['cols']) || empty($tabularData['data'])) return array();
        $cols = $tabularData['cols'];
        $result = array();
        foreach ($tabularData['data'] as $row) {
            $result[] = array_combine($cols, $row);
        }
        return $result;
    }

    private function log_usage($body_json, $wrapper_code, $latency_ms) {
        $usage = isset($body_json['usage']) ? $body_json['usage'] : array();
        $cost = isset($body_json['cost']) ? $body_json['cost'] : array();
        
        $jsst_query = "INSERT INTO `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs` (
            `trace_id`, `wrapper_code`, `model_code`, `prompt_tokens`, `completion_tokens`, `total_tokens`, `credits_used`, `latency_ms`, `created_at`
        ) VALUES (
            '" . esc_sql($body_json['id'] ?? '') . "',
            '" . esc_sql($wrapper_code) . "',
            '" . esc_sql($body_json['model'] ?? '') . "',
            " . (int)($usage['prompt_tokens'] ?? 0) . ",
            " . (int)($usage['completion_tokens'] ?? 0) . ",
            " . (int)($usage['total_tokens'] ?? 0) . ",
            " . (float)($cost['credits_used'] ?? 0) . ",
            " . (int)$latency_ms . ",
            '" . current_time('mysql', 1) . "'
        )";
        jssupportticket::$_db->query($jsst_query);
    }
    /**
     * Fetch all active AI Wrappers grouped by Category
     */
    /**
     * Fetch ONLY Use Cases for Customer Support
     */
    function getSupportUseCases() {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        $jsst_query = "SELECT code, name FROM `" . $prefix . "zywrap_use_cases` 
                       WHERE category_code = 'customer_support_replies' AND status = 1 
                       ORDER BY ordering ASC, name ASC";
                          
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_results;
    }

    /**
     * Fetch Wrappers (Base + 8 Variations) for a specific Use Case
     */
    function getWrappersByUseCase($use_case_code) {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        $jsst_query = "SELECT code, name, base FROM `" . $prefix . "zywrap_wrappers` 
                       WHERE use_case_code = '" . esc_sql($use_case_code) . "' AND status = 1 
                       ORDER BY base DESC, ordering ASC"; // Base wrapper shows first
                          
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_results;
    }

    /**
     * Fetch all active Tones from Block Templates
     */
    function getDynamicTones() {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        
        $jsst_query = "SELECT code, name FROM `" . $prefix . "zywrap_block_templates` 
                  WHERE type = 'tones' AND status = 1 
                  ORDER BY name ASC";
                          
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_results;
    }

    static function ajaxGetWrappers() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'zywrap_ajax_action')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }

        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        /*
        // SECURITY: ONLY ADMINS OR JSST STAFF CAN ACCESS
        $is_admin = current_user_can('manage_options');
        $is_staff = false;
        
        // Check if the JSST Agent add-on is active and the user is an agent
        if (in_array('agent', jssupportticket::$_active_addons)) {
            $is_staff = JSSTincluder::getJSModel('agent')->isUserStaff();
        }

        if (!$is_admin && !$is_staff) {
            wp_send_json_error(array('message' => 'Security Error: Unauthorized access. Staff members only.'));
            return;
        }
        */

        $use_case_code = JSSTrequest::getVar('use_case_code');
        $ticket_id = JSSTrequest::getVar('ticket_id'); // We now receive the Ticket ID

        $model_instance = new self();
        $wrappers = $model_instance->getWrappersByUseCase($use_case_code);

        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        $jsst_query = "SELECT schema_data FROM `" . $prefix . "zywrap_use_cases` WHERE code = '" . esc_sql($use_case_code) . "'";
        $schema_json = jssupportticket::$_db->get_var($jsst_query);
        $schema = !empty($schema_json) ? json_decode($schema_json, true) : null;

        // Fetch Clean Ticket Data from PHP
        $ticketData = $model_instance->getTicketContext($ticket_id);

        // Native WP JSON sender automatically sets secure headers (no parse errors)
        wp_send_json_success(array(
            'wrappers' => $wrappers,
            'schema' => $schema,
            'ticketData' => $ticketData
        ));
    }

    // --- 3. UPGRADED: Uses native wp_send_json & Zywrap Stream Parser ---
    function generateReply() {
        // 1. Verify Nonce Securely
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'zywrap_ajax_action')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }

        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        /*
        // SECURITY: ONLY ADMINS OR JSST STAFF CAN ACCESS
        $is_admin = current_user_can('manage_options');
        $is_staff = false;
        
        // Check if the JSST Agent add-on is active and the user is an agent
        if (in_array('agent', jssupportticket::$_active_addons)) {
            $is_staff = JSSTincluder::getJSModel('agent')->isUserStaff();
        }

        if (!$is_admin && !$is_staff) {
            wp_send_json_error(array('message' => 'Security Error: Unauthorized access. Staff members only.'));
            return;
        }
        */

        $api_key = get_option('jsst_zywrap_api_key');
        if (empty($api_key)) {
            wp_send_json_error(array('message' => __('API Key is missing.', 'js-support-ticket')));
        }

        // 2. Fetch parameters directly from $_POST to bypass JSST framework stripping
        $wrapper_code = sanitize_text_field(JSSTrequest::getVar('wrapper_code'));
        $prompt       = sanitize_textarea_field(JSSTrequest::getVar('prompt'));
        $model_code   = sanitize_text_field(JSSTrequest::getVar('model_code')); 
        $tone         = sanitize_text_field(JSSTrequest::getVar('tone'));
        $language     = sanitize_text_field(JSSTrequest::getVar('language'));
        
        if (empty($wrapper_code)) {
            wp_send_json_error(array('message' => __('Please select an AI action.', 'js-support-ticket')));
        }

        // 3. Process Dynamic Variables securely
        $variables_json = wp_unslash(JSSTrequest::getVar('variables', '{}'));
        $variables = json_decode($variables_json, true);
        if (!is_array($variables)) { $variables = array(); }

        $sanitized_vars = array();
        foreach ($variables as $k => $v) {
            $sanitized_vars[sanitize_text_field($k)] = sanitize_textarea_field($v);
        }

        // 4. Build Payload exactly matching the Zywrap SDK Docs
        $body = array(
            'wrapperCodes' => array($wrapper_code),
            'source'       => 'js-support-ticket'
        );

        if (!empty($prompt)) {
            $body['prompt'] = $prompt; // The Agent's Extra Instructions
        }
        if (!empty($model_code)) {
            $body['model'] = $model_code;
        }
        if (!empty($tone)) {
            $body['toneCode'] = $tone;
        }
        if (!empty($language)) {
            $body['language'] = $language;
        }
        if (!empty($sanitized_vars)) {
            $body['variables'] = (object)$sanitized_vars;
        }

        
        // (Optional) Catch any other advanced overrides if added to the UI later
        $overrides = ['styleCode', 'formatCode', 'complexityCode', 'lengthCode', 'audienceCode', 'responseGoalCode', 'outputCode'];
        foreach ($overrides as $override) {
            $val = JSSTrequest::getVar($override);
            if (!empty($val)) { $body[$override] = sanitize_text_field($val); }
        }
        
        // 5. Execute API Call
        $api_url = 'https://api.zywrap.com/v1/proxy';
        $start_time = microtime(true);
        
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key, 
                'Content-Type'  => 'application/json'
            ),
            'body'      => wp_json_encode($body),
            'timeout'   => 600, 
            'sslverify' => false,
        ));
        $latency_ms = round((microtime(true) - $start_time) * 1000);

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => __('Connection Error: ', 'js-support-ticket') . $response->get_error_message()));
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $raw_response = wp_remote_retrieve_body($response);

        // 6. ZYWRAP V1 STREAM PARSER (From the PHP SDK)
        $lines = explode("\n", $raw_response);
        $finalJson = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, 'data: ') === 0) {
                $data = json_decode(substr($line, 6), true);
                if ($data && (isset($data['output']) || isset($data['error']))) {
                    $finalJson = substr($line, 6);
                }
            }
        }

        $body_json = $finalJson ? json_decode($finalJson, true) : null;

        // 7. Output Handling
        if ($http_code === 200 && $body_json && isset($body_json['output'])) {
            $this->log_usage($body_json, $wrapper_code, $latency_ms);
            wp_send_json_success(array('output' => $body_json['output']));
        } else {
            $error_msg = __('Unknown API Error', 'js-support-ticket');
            if ($body_json && isset($body_json['error'])) {
                $error_msg = is_string($body_json['error']) ? $body_json['error'] : wp_json_encode($body_json['error']);
            } elseif ($body_json && isset($body_json['message'])) {
                $error_msg = $body_json['message'];
            } elseif (!$body_json && !empty($raw_response)) {
                $error_msg = __('Parse Error:', 'js-support-ticket') . ' ' . substr(wp_strip_all_tags($raw_response), 0, 150);
            }
            wp_send_json_error(array('message' => $error_msg));
        }
    }
    /**
     * Fetch all active AI Models
     */
    function getDynamicModels() {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        $jsst_query = "SELECT code, name FROM `" . $prefix . "zywrap_ai_models` 
                       WHERE status = 1 
                       ORDER BY ordering ASC, name ASC";
                          
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return $jsst_results;
    }

    // --- 1. NEW: Fetch pure ticket data directly from the DB ---
    // --- 1. NEW: Fetch pure ticket data directly from the DB ---
    function getTicketContext($ticket_id) {
        if (!$ticket_id) {
            return array('subject' => '', 'initialMsg' => '', 'fullThread' => '', 'latestCustomerMsg' => '');
        }

        $prefix = jssupportticket::$_db->prefix . "js_ticket_";

        // Fetch Main Ticket
        $jsst_query = "SELECT subject, message, uid FROM `{$prefix}tickets` WHERE id = " . (int)$ticket_id;
        $ticket = jssupportticket::$_db->get_row($jsst_query);
        if (!$ticket) {
            return array('subject' => '', 'initialMsg' => '', 'fullThread' => '', 'latestCustomerMsg' => '');
        }

        $subject = wp_strip_all_tags(strip_shortcodes($ticket->subject));
        $initialMsg = wp_strip_all_tags(strip_shortcodes($ticket->message));
        $latestCustomerMsg = $initialMsg;

        $history = "CUSTOMER (Initial Issue):\n" . $initialMsg . "\n\n";

        // The column name in js_ticket_replies is 'message', not 'reply'
        $jsst_query = "SELECT message, uid FROM `{$prefix}replies` WHERE ticketid = " . (int)$ticket_id . " ORDER BY created ASC";
        $replies = jssupportticket::$_db->get_results($jsst_query);

        if (!empty($replies)) {
            foreach ($replies as $reply) {
                // FIX: Use $reply->message instead of $reply->reply
                $clean_reply = wp_strip_all_tags(strip_shortcodes($reply->message));
                
                // If reply UID matches ticket UID, it is the Customer. Otherwise, Support.
                if ($reply->uid == $ticket->uid) {
                    $author = "CUSTOMER";
                    if (!empty($clean_reply)) {
                        $latestCustomerMsg = $clean_reply; // Track absolute latest
                    }
                } else {
                    $author = "SUPPORT AGENT";
                }

                if (!empty($clean_reply)) {
                    $history .= "--- " . $author . " ---\n" . $clean_reply . "\n\n";
                }
            }
        }

        return array(
            'subject' => $subject,
            'initialMsg' => $initialMsg,
            'fullThread' => trim($history),
            'latestCustomerMsg' => $latestCustomerMsg
        );
    }

    function saveSettings() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'zywrap_ajax_action')) {
            wp_send_json_error(array('message' => __('Security check Failed', 'js-support-ticket')));
        }

        $api_key = sanitize_text_field(JSSTrequest::getVar('api_key'));
        $default_model = sanitize_text_field(JSSTrequest::getVar('default_model'));
        $default_lang = sanitize_text_field(JSSTrequest::getVar('default_lang', 'English'));

        update_option('jsst_zywrap_api_key', $api_key);
        update_option('jsst_zywrap_default_model', $default_model);
        update_option('jsst_zywrap_default_lang', $default_lang);

        wp_send_json_success(array('message' => __('Global AI Settings saved successfully.', 'js-support-ticket')));
    }

    // =========================================================
    // ZYWRAP LOGS & ERRORS PAGES (MODEL)
    // =========================================================

    function getLogs() {
        // Filter variables
        $jsst_trace_id = isset(jssupportticket::$_search['zywrap_logs']) ? jssupportticket::$_search['zywrap_logs']['trace_id'] : '';
        $jsst_pagesize = isset(jssupportticket::$_search['zywrap_logs']) ? jssupportticket::$_search['zywrap_logs']['pagesize'] : 20;

        $jsst_trace_id = jssupportticket::parseSpaces($jsst_trace_id);
        $jsst_inquery = "";
        if ($jsst_trace_id != null) {
            $jsst_inquery .= " WHERE trace_id LIKE '%" . esc_sql($jsst_trace_id) . "%'";
        }

        jssupportticket::$jsst_data['filter']['trace_id'] = $jsst_trace_id;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        // Pagination Limit
        if ($jsst_pagesize) {
            JSSTpagination::setLimit($jsst_pagesize);
        }

        // Get Total Count for Pagination
        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs`";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total, 'zywrap_logs');

        // Get Paginated Data
        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs`";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY id DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function getErrors() {
        // Filter variables (Only pagesize needed for errors usually)
        $jsst_pagesize = isset(jssupportticket::$_search['zywrap_errors']) ? jssupportticket::$_search['zywrap_errors']['pagesize'] : 20;
        jssupportticket::$jsst_data['filter']['pagesize'] = $jsst_pagesize;

        if ($jsst_pagesize) {
            JSSTpagination::setLimit($jsst_pagesize);
        }

        // Force query to only show errors
        $jsst_inquery = " WHERE status = 'error'";

        $jsst_query = "SELECT COUNT(`id`) FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs`";
        $jsst_query .= $jsst_inquery;
        $jsst_total = jssupportticket::$_db->get_var($jsst_query);
        
        jssupportticket::$jsst_data['total'] = $jsst_total;
        jssupportticket::$jsst_data[1] = JSSTpagination::getPagination($jsst_total, 'zywrap_errors');

        $jsst_query = "SELECT * FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs`";
        $jsst_query .= $jsst_inquery;
        $jsst_query .= " ORDER BY id DESC LIMIT " . JSSTpagination::getOffset() . ", " . JSSTpagination::getLimit();
        
        jssupportticket::$jsst_data[0] = jssupportticket::$_db->get_results($jsst_query);
        
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        return;
    }

    function deleteLog($jsst_id) {
        if (!is_numeric($jsst_id)) return false;

        $jsst_query = "DELETE FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs` WHERE id = " . (int)$jsst_id;
        jssupportticket::$_db->query($jsst_query);

        if (jssupportticket::$_db->last_error == null) {
            JSSTmessage::setMessage(esc_html(__('Log deleted successfully.', 'js-support-ticket')), 'updated');
        } else {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
            JSSTmessage::setMessage(esc_html(__('Failed to delete log.', 'js-support-ticket')), 'error');
        }
        return;
    }

    // Required for the native Search/Filter functionality to work
    function getAdminZywrapSearchFormData() {
        $jsst_search_array = array();
        $jsst_layout = JSSTrequest::getVar('jstlay'); // e.g., 'logs' or 'errors'
        
        if ($jsst_layout == 'logs') {
            $jsst_search_array['trace_id'] = jssupportticketphplib::JSST_addslashes(jssupportticketphplib::JSST_trim(JSSTrequest::getVar('trace_id')));
        }
        $jsst_search_array['pagesize'] = absint(JSSTrequest::getVar('pagesize'));
        return $jsst_search_array;
    }

    // =========================================================
    // ADVANCED PLAYGROUND (AJAX ENDPOINTS)
    // =========================================================

    function pgGetCategories() {
        $jsst_query = "SELECT code, name FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_categories` WHERE status = 1 ORDER BY ordering ASC";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($jsst_results);
    }

    function pgGetUseCases() {
        $cat = sanitize_text_field(JSSTrequest::getVar('category'));
        $jsst_query = "SELECT code, name FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_use_cases` WHERE category_code = '" . esc_sql($cat) . "' AND status = 1 ORDER BY ordering ASC";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($jsst_results);
    }

    function pgGetWrappers() {
        $uc = sanitize_text_field(JSSTrequest::getVar('usecase'));
        $jsst_query = "SELECT code, name, featured, base FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_wrappers` WHERE use_case_code = '" . esc_sql($uc) . "' AND status = 1 ORDER BY ordering ASC";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($jsst_results);
    }

    function pgGetSchema() {
        $w = sanitize_text_field(JSSTrequest::getVar('wrapper'));
        $jsst_query = "SELECT uc.schema_data FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_use_cases` uc JOIN `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_wrappers` w ON w.use_case_code = uc.code WHERE w.code = '" . esc_sql($w) . "'";
        $res = jssupportticket::$_db->get_var($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($res ? json_decode($res, true) : null);
    }

    function pgGetLanguages() {
        $jsst_query = "SELECT code, name FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_languages` WHERE status = 1 ORDER BY ordering ASC";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($jsst_results);
    }

    function pgGetModels() {
        $jsst_query = "SELECT code, name FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_ai_models` WHERE status = 1 ORDER BY ordering ASC";
        $jsst_results = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        wp_send_json($jsst_results);
    }

    function pgGetBlockTemplates() {
        $jsst_query = "SELECT type, code, name FROM `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_block_templates` WHERE status = 1 ORDER BY type, name ASC";
        $res = jssupportticket::$_db->get_results($jsst_query);
        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }
        $grouped = [];
        if ($res) {
            foreach ($res as $r) { $grouped[$r->type][] = ['code' => $r->code, 'name' => $r->name]; }
        }
        wp_send_json($grouped);
    }

    function pgExecute() {
        $jsst_nonce = JSSTrequest::getVar('_wpnonce');
        if (!wp_verify_nonce($jsst_nonce, 'zywrap_ajax_action')) wp_send_json_error(['message' => __('Security check Failed', 'js-support-ticket')]);

        // SECURITY: ONLY ADMINISTRATORS CAN ACCESS
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Security Error: Unauthorized access. Administrators only.', 'js-support-ticket')));
            return;
        }

        $apiKey = get_option('jsst_zywrap_api_key', '');
        if (empty($apiKey)) wp_send_json_error(['message' => __('API Key is not configured.', 'js-support-ticket')]);

        $model = sanitize_text_field(JSSTrequest::getVar('model'));
        $wrapperCode = sanitize_text_field(JSSTrequest::getVar('wrapperCode'));
        
        $prompt = wp_unslash(JSSTrequest::getVar('prompt'));
        $language = sanitize_text_field(JSSTrequest::getVar('language'));
        
        $variables = json_decode(wp_unslash(JSSTrequest::getVar('variables', '[]')), true);
        $overrides = json_decode(wp_unslash(JSSTrequest::getVar('overrides', '[]')), true);

        $payloadData = [
            'wrapperCodes' => [$wrapperCode], 
            'source' => 'js-support-ticket-playground'
        ];
        
        if (!empty($model)) $payloadData['model'] = $model;
        if (!empty($prompt)) $payloadData['prompt'] = $prompt;
        if (!empty($variables)) $payloadData['variables'] = $variables;
        if (!empty($language)) $payloadData['language'] = $language;
        if (!empty($overrides)) $payloadData = array_merge($payloadData, $overrides);

        $startTime = microtime(true);
        $ch = curl_init('https://api.zywrap.com/v1/proxy');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // FIX: Prevent SSL blocking on some WordPress hosts
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', 
            'Authorization: Bearer ' . $apiKey
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        // --- IMPROVED ERROR CATCHING & PARSING ---
        if ($rawResponse === false) {
            wp_send_json_error(['message' => __('Server Connection Error: ', 'js-support-ticket') . $curlError]);
        }

        $finalJson = null;
        $responseData = null;

        // 1. Check if the API threw a direct JSON error (e.g. 400 Bad Request)
        $directJson = json_decode($rawResponse, true);
        if ($directJson !== null && isset($directJson['error'])) {
            $responseData = $directJson;
        } else {
            // 2. Parse it as a successful Data Stream (SSE)
            foreach (explode("\n", $rawResponse) as $line) {
                $line = trim($line);
                if (strpos($line, 'data: ') === 0) {
                    $data = json_decode(substr($line, 6), true);
                    if ($data && (isset($data['output']) || isset($data['error']))) {
                        $responseData = $data;
                    }
                }
            }
        }

        // Determine actual status
        $status = ($httpCode === 200 && $responseData && !isset($responseData['error'])) ? 'success' : 'error';
        
        // Extract the exact error message if it failed
        $errorMessage = null;
        if ($status === 'error') {
            if (isset($responseData['error'])) {
                $errorMessage = is_array($responseData['error']) ? json_encode($responseData['error']) : $responseData['error'];
            } else {
                // WordPress standard: Use wp_strip_all_tags instead of strip_tags
                // Also adjusted concatenation to avoid potential translation placeholder issues later
                $errorMessage = __('API Error (HTTP ', 'js-support-ticket') . $httpCode . '): ' . substr(wp_strip_all_tags($rawResponse), 0, 200);
            }
        }

        // --- LOG USAGE ---
        try {
            $jsst_query = "INSERT INTO `" . jssupportticket::$_db->prefix . "js_ticket_zywrap_usage_logs` (
                trace_id, wrapper_code, model_code, prompt_tokens, completion_tokens, total_tokens, credits_used, latency_ms, status, error_message, created_at
            ) VALUES (
                '" . esc_sql($responseData['id'] ?? null) . "', 
                '" . esc_sql($wrapperCode) . "', 
                '" . esc_sql($model ?: 'default') . "',
                " . (int)($responseData['usage']['prompt_tokens'] ?? 0) . ", 
                " . (int)($responseData['usage']['completion_tokens'] ?? 0) . ",
                " . (int)($responseData['usage']['total_tokens'] ?? 0) . ", 
                " . (float)($responseData['cost']['credits_used'] ?? 0) . ",
                " . (int)$latencyMs . ", 
                '" . esc_sql($status) . "', 
                '" . esc_sql($errorMessage) . "', 
                '" . current_time('mysql') . "'
            )";
            jssupportticket::$_db->query($jsst_query);
        } catch (Exception $e) {}

        // --- RESPOND TO FRONTEND ---
        if ($status === 'success') {
            wp_send_json_success($responseData);
        } else {
            wp_send_json_error(['message' => $errorMessage]);
        }
    }
    
    // =========================================================
    // ZYWRAP MAIN DASHBOARD (MODEL)
    // =========================================================
    function getDashboardStats() {
        $prefix = jssupportticket::$_db->prefix . "js_ticket_";
        
        $dashboard_data = array(
            'api_key'     => get_option('jsst_zywrap_api_key', ''),
            'last_sync'   => get_option('jsst_zywrap_last_sync'),
            'total_reqs'  => 0,
            'total_toks'  => 0,
            'total_errs'  => 0,
            'recent_logs' => array()
        );

        try {
            // Aggregate stats
            $jsst_query = "SELECT COUNT(*) as total_reqs, SUM(total_tokens) as total_toks, SUM(CASE WHEN status='error' THEN 1 ELSE 0 END) as errors FROM `{$prefix}zywrap_usage_logs`";
            $stats = jssupportticket::$_db->get_row($jsst_query);
            
            if ($stats) {
                $dashboard_data['total_reqs'] = (int) $stats->total_reqs;
                $dashboard_data['total_toks'] = (int) $stats->total_toks;
                $dashboard_data['total_errs'] = (int) $stats->errors;
            }
            
            // Recent logs
            $jsst_query = "SELECT * FROM `{$prefix}zywrap_usage_logs` ORDER BY id DESC LIMIT 10";
            $dashboard_data['recent_logs'] = jssupportticket::$_db->get_results($jsst_query);
            
        } catch (Exception $e) {
            // Fail gracefully if table doesn't exist yet
        }

        if (jssupportticket::$_db->last_error != null) {
            JSSTincluder::getJSModel('systemerror')->addSystemError();
        }

        // Pass data to the View
        jssupportticket::$jsst_data['dashboard_stats'] = $dashboard_data;
    }
}