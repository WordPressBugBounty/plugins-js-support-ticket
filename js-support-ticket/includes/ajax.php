<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTajax {

    function __construct() {
        add_action("wp_ajax_jsticket_ajax", array($this, "ajaxhandler")); // when user is login
        add_action("wp_ajax_nopriv_jsticket_ajax", array($this, "ajaxhandler")); // when user is not login
    }

    function ajaxhandler() {
        $jsst_functions_allowed = array('DataForDepandantField','subscribeForNotifications','unsubscribeFromNotifications','getDownloadById','getAllDownloads','sendTestEmail','getuserlistajax','getmultiformlistajax','getFieldsForComboByFieldFor','getSectionToFillValues','updateUserDevice','checkParentType','checkChildType','makeParentOfType','getTypeForByParentId','getusersearchstaffreportajax','getusersearchuserreportajax','getusersearchajax','saveuserprofileajax','getHelpTopicByDepartment','getpremadeajax','getPremadeByDepartment','getTicketsForMerging','getLatestReplyForMerging','previewMergeSelection','getReplyDataByID','getTimeByReplyID','getTimeByNoteID','readEmailsAjax','getOptionsForFieldEdit','storePrivateCredentials','getFormForPrivteCredentials', 'getPrivateCredentials', 'removePrivateCredential','getWcOrderProductsAjax','markUnmarkTicketNonPremiumAjax','linkTicketPaidSupportAjax','getEDDOrderProductsAjax','getEDDProductlicensesAjax','uploadStaffImage','installPluginFromAjax','activatePluginFromAjax','listEmailTemplate','deleteEmailTemplate','getDefaultEmailTemplate','hidePopupFromAdmin','getHtmlForMoreEmail','getHtmlForMoreConditions','getOperatorsByTitleForCombobox','getValuesByTitleForCombobox','getChildForVisibleCombobox','getConditionsForVisibleCombobox','deleteSupportCustomImage','reviewBoxAction','isFieldRequired','getOptionsForEditSlug','getHtmlForORRow','getHtmlForANDRow','checkAIReplyTicketsBySubject','getAiSuggestedReplies','markedAsAiPoweredReply','getFilteredReplies','saveApiKey','syncDataBundle','ajaxGetWrappers','generateReply','savePreferences','pgGetCategories','pgGetUseCases','pgGetWrappers','pgGetSchema','pgGetBlockTemplates','pgGetLanguages','pgGetModels','pgExecute','getInstantResolveSearch','logInstantResolveEvent','refreshTicketFormNonce','copilotRun');
        $jsst_task = JSSTrequest::getVar('task');
        $this->refuseUnsafeAddonTask($jsst_task);
        if($jsst_task != '' && in_array($jsst_task, $jsst_functions_allowed)){
            $jsst_module = JSSTrequest::getVar('jstmod');
			$jsst_module = jssupportticketphplib::JSST_clean_file_path($jsst_module);
            $jsst_result = JSSTincluder::getJSModel($jsst_module)->$jsst_task();
            echo wp_kses($jsst_result, JSST_ALLOWED_TAGS);
            die();
        }else{
            die(esc_html__( 'Not Allowed!', 'js-support-ticket' ));
        }
    }

    /**
     * Requests refused here, before any add-on code runs, so that sites still
     * on an older add-on are protected by updating the help desk alone. (5.0.7)
     *
     *  - uploadStaffImage (Agents pack 1.0.3 and older, and the old Agent
     *    add-on) used a staff id from the request as a folder name and
     *    emptied that folder without a sign-in or a nonce: "999/../../.."
     *    deleted files anywhere the web server could write. It now needs a
     *    signed-in user, and an id, if one is sent, has to be a plain number
     *    and the user's own staff record (or the user a site administrator).
     */
    private function refuseUnsafeAddonTask($jsst_task) {
        if ('uploadStaffImage' !== $jsst_task) {
            return;
        }
        $jsst_refuse = !is_user_logged_in();
        $jsst_id = (string) JSSTrequest::getVar('id');
        if (!$jsst_refuse && $jsst_id !== '') {
            if (!ctype_digit($jsst_id)) {
                $jsst_refuse = true;
            } elseif (!current_user_can('manage_options')) {
                $jsst_own = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                    "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_staff` WHERE uid = %d",
                    (int) JSSTincluder::getObjectClass('user')->uid()
                ));
                $jsst_refuse = ((string) $jsst_own !== $jsst_id);
            }
        }
        if ($jsst_refuse) {
            wp_die(
                esc_html__( 'Security check Failed', 'js-support-ticket' ),
                esc_html__( 'Security Error', 'js-support-ticket' ),
                array( 'response' => 403 )
            );
        }
    }

}

$jsst_jsajax = new JSSTajax();
?>
