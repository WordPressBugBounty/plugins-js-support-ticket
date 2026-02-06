<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

class JSSTajax {

    function __construct() {
        add_action("wp_ajax_jsticket_ajax", array($this, "ajaxhandler")); // when user is login
        add_action("wp_ajax_nopriv_jsticket_ajax", array($this, "ajaxhandler")); // when user is not login
    }

    function ajaxhandler() {
        $jsst_functions_allowed = array('DataForDepandantField','subscribeForNotifications','unsubscribeFromNotifications','getDownloadById','getAllDownloads','sendTestEmail','getuserlistajax','getmultiformlistajax','getFieldsForComboByFieldFor','getSectionToFillValues','getListTranslations','validateandshowdownloadfilename','getlanguagetranslation','updateUserDevice','checkParentType','checkChildType','makeParentOfType','getTypeForByParentId','getusersearchstaffreportajax','getusersearchuserreportajax','getusersearchajax','saveuserprofileajax','getHelpTopicByDepartment','getpremadeajax','getPremadeByDepartment','getTicketsForMerging','getLatestReplyForMerging','getReplyDataByID','getTimeByReplyID','getTimeByNoteID','readEmailsAjax','getOptionsForFieldEdit','storePrivateCredentials','getFormForPrivteCredentials', 'getPrivateCredentials', 'removePrivateCredential','getWcOrderProductsAjax','markUnmarkTicketNonPremiumAjax','linkTicketPaidSupportAjax','getEDDOrderProductsAjax','getEDDProductlicensesAjax','uploadStaffImage','installPluginFromAjax','activatePluginFromAjax','listEmailTemplate','deleteEmailTemplate','getDefaultEmailTemplate','hidePopupFromAdmin','getHtmlForMoreEmail','getHtmlForMoreConditions','getOperatorsByTitleForCombobox','getValuesByTitleForCombobox','getChildForVisibleCombobox','getConditionsForVisibleCombobox','deleteSupportCustomImage','reviewBoxAction','isFieldRequired','getOptionsForEditSlug','JSSTdownloadandinstalladdonfromAjax','getHtmlForORRow','getHtmlForANDRow','checkAIReplyTicketsBySubject','markedAsAiPoweredReply','getFilteredReplies');
        $jsst_task = JSSTrequest::getVar('task');
        if($jsst_task != '' && in_array($jsst_task, $jsst_functions_allowed)){
            $jsst_module = JSSTrequest::getVar('jstmod');
			$jsst_module = jssupportticketphplib::JSST_clean_file_path($jsst_module);
            $jsst_result = JSSTincluder::getJSModel($jsst_module)->$jsst_task();
            echo wp_kses($jsst_result, JSST_ALLOWED_TAGS);
            die();
        }else{
            die('Not Allowed!');
        }
    }

}

$jsst_jsajax = new JSSTajax();
?>
