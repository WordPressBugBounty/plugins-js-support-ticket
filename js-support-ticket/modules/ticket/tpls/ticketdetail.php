<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if(isset(jssupportticket::$_data['error_message'])){
        if(jssupportticket::$_data['error_message'] == 1){
            JSSTlayout::getUserGuest();
        }elseif(jssupportticket::$_data['error_message'] == 2){
            JSSTlayout::getYouAreNotAllowedToViewThisPage();
        }
    }elseif (JSSTincluder::getObjectClass('user')->uid() != 0 || jssupportticket::$_config['visitor_can_create_ticket'] == 1) {
        JSSTmessage::getMessage();

        $printflag = false;
        if(isset(jssupportticket::$_data['print']) && jssupportticket::$_data['print'] == 1){
            $printflag = true;
        }
        if($printflag == true){
            wp_head();
        }

        if($printflag == false){
            //JSSTbreadcrumbs::getBreadcrumbs();
            include_once(JSST_PLUGIN_PATH . 'includes/header.php');
        }

        if (jssupportticket::$_data['permission_granted'] == true) {
        if (!empty(jssupportticket::$_data[0])) {

        wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery.cluetip.min.js', JSST_PLUGIN_URL . 'includes/js/jquery.cluetip.min.js');
        wp_enqueue_script( 'hoverIntent' );
        wp_enqueue_style('jquery.cluetip', JSST_PLUGIN_URL . 'includes/css/jquery.cluetip.css');
        wp_enqueue_script('timer.js', JSST_PLUGIN_URL . 'includes/js/timer.jquery.js');
        wp_enqueue_style('jssupportticket-venobox-css', JSST_PLUGIN_URL . 'includes/css/venobox.css');
        wp_enqueue_script('venoboxjs',JSST_PLUGIN_URL.'includes/js/venobox.js');
        $jssupportticket_js ="
            var timer_flag = 0;
            var seconds = 0;
            function getpremade(val) {
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'cannedresponses', task: 'getpremadeajax', '_wpnonce':'". esc_attr(wp_create_nonce('get-premade-ajax')) ."'}, function (data) {
                    if (data) {
                        var append = jQuery('input#append_premade1:checked').length;
                        if (append == 1) {
                            var content = tinyMCE.get('jsticket_message').getContent();
                            content = content + data;
                            tinyMCE.get('jsticket_message').execCommand('mceSetContent', false, content);
                        } else {
                            tinyMCE.get('jsticket_message').execCommand('mceSetContent', false, data);
                        }

                    }
                });
            }

            function changeTimerStatus(val) {
                if(timer_flag == 2){// to handle stopped timer
                        return;
                }
                if(!jQuery('span.timer-button.cls_'+val).hasClass('selected')){
                    jQuery('span.timer-button').removeClass('selected');
                    jQuery('span.timer-button.cls_'+val).addClass('selected');
                    if(val == 1){
                        if(timer_flag == 0){
                            jQuery('div.timer').timer({format: '%H:%M:%S'});
                        }
                        timer_flag = 1;
                        jQuery('div.timer').timer('resume');
                    }else if(val == 2) {
                         jQuery('div.timer').timer('pause');
                    }else{
                         jQuery('div.timer').timer('remove');
                        timer_flag = 2;
                    }
                }
            }

            jQuery(document).ready(function(){
              changeIconTabs();
              jQuery('.venobox').venobox({
                    infinigall: true,
                    framewidth: 850,
                    titleattr: 'data-title',
                });
            });


            jQuery(function(){
                jQuery('ul li a').click(function (e) {
                    var imgID= jQuery(this).find('img').attr('id');
                    changeIconTabs(imgID);
                  });
            });

            function changeIconTabs(tabValue = ''){
                jQuery(document).ready(function(){
                    if(tabValue == ''){
                        tabValue = jQuery('#ul-nav .ui-tabs-active > a > img').attr('id');
                    }
                    if(tabValue == 'post-reply'){
                        jQuery('#internal-note').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-black.png');
                        jQuery('#dept-transfer').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-black.png');
                        jQuery('#assign-staff').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-black.png');
                        jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-white.png');
                    }else if(tabValue == 'internal-note'){
                        jQuery('#dept-transfer').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-black.png');
                        jQuery('#assign-staff').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-black.png');
                        jQuery('#post-reply').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-black.png');
                        jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-white.png');
                    }else if(tabValue == 'dept-transfer'){
                        jQuery('#internal-note').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-black.png');
                        jQuery('#assign-staff').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-black.png');
                        jQuery('#post-reply').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-black.png');
                        jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-white.png');
                    }else if(tabValue == 'assign-staff'){
                        jQuery('#dept-transfer').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-black.png');
                        jQuery('#internal-note').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-black.png');
                        jQuery('#post-reply').attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-black.png');
                        jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-white.png');
                    }

                });
            }
            function changeIconTabsOnMouseover(){
                jQuery(document).ready(function(){
                    jQuery('ul li').hover(function (e) {
                        var imgID= jQuery(this).find('img').attr('id');
                        tabValue=imgID;
                        if(tabValue == ''){
                            tabValue = jQuery('#ul-nav .ui-tabs-active > a > img').attr('id');
                        }
                        if(tabValue == 'post-reply'){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-white.png');
                        }else if(tabValue == 'internal-note'){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-white.png');
                        }else if(tabValue == 'dept-transfer'){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-white.png');
                        }else if(tabValue == 'assign-staff'){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-white.png');
                        }
                    });
                });
            }
            function changeIconTabsOnMouseOut(){
                jQuery(document).ready(function(){
                    jQuery('ul li').hover(function (e) {
                        var imgID= jQuery(this).find('img').attr('id');
                        tabValue=imgID;
                        if(tabValue == ''){
                            tabValue = jQuery('#ul-nav .ui-tabs-active > a > img').attr('id');
                        }
                        if(tabValue == 'post-reply' && !jQuery(this).hasClass('ui-tabs-active')){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/post-reply-black.png');
                        }else if(tabValue == 'internal-note' && !jQuery(this).hasClass('ui-tabs-active')){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/internal-reply-black.png');
                        }else if(tabValue == 'dept-transfer' && !jQuery(this).hasClass('ui-tabs-active')){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/department-transfer-black.png');
                        }else if(tabValue == 'assign-staff' && !jQuery(this).hasClass('ui-tabs-active')){
                            jQuery('#'+tabValue).attr('src','". esc_url(JSST_PLUGIN_URL) ."includes/images/ticketdetailicon/assign-staff-black.png');
                        }
                    });
                });
            }
            function showEditTimerPopup(){
                jQuery('form#jsst-time-edit-form').hide();
                jQuery('form#jsst-reply-form').hide();
                jQuery('form#jsst-note-edit-form').hide();
                jQuery('div.edit-time-popup').show();
                jQuery('span.timer-button').removeClass('selected');
                if(timer_flag != 0){
                    jQuery('div.timer').timer('pause');
                }
                ex_val = jQuery('div.timer').html();
                jQuery('input#edited_time').val('');
                jQuery('input#edited_time').val(ex_val.trim());
                jQuery('div.jsst-popup-background').show();
                jQuery('div#jsst-popup-wrapper').slideDown('slow');
            }
            function updateTimerFromPopup(){
                val = jQuery('input#edited_time').val();
                arr = val.split(':', 3);
                jQuery('div.timer').html(val);
                jQuery('div.jsst-popup-background').hide();
                jQuery('div.jsst-popup-wrapper').slideUp('slow');
                seconds = parseInt(arr[0])*3600 + parseInt(arr[1])*60 + parseInt(arr[2]);
                if(seconds < 0){
                    seconds = 0;
                }
                jQuery('div.timer').timer('remove');
                jQuery('div.timer').timer({
                    format: '%H:%M:%S',
                    seconds: seconds,
                });
                jQuery('div.timer').timer('pause');
                timer_flag = 1;
                desc = jQuery('textarea#t_desc').val();
                jQuery('input#timer_edit_desc').val(desc);
            }
            jQuery(document).ready(function ($) {
                //$('img.tooltip').cluetip({splitTitle: '|'});
                jQuery( 'form' ).submit(function(e) {
                    if(timer_flag != 0){
                        jQuery('input#timer_time_in_seconds').val(jQuery('div.timer').data('seconds'));
                    }
                });
                jQuery('div#action-div a.button').click(function (e) {
                    e.preventDefault();
                });
                ";
                if($printflag != true){
                    $jssupportticket_js .="jQuery('#tabs').tabs();";
                }
                $jssupportticket_js .="

                jQuery('#tk_attachment_add').click(function () {
                    var obj = this;
                    var att_flag = jQuery(this).attr('data-ident');
                    var parentElement = jQuery(this).closest('.js-attachment-field');
                    jQuery(parentElement).addClass('js-attachment-field-selected');
                    var current_files = jQuery('div.js-attachment-field-selected').find('.tk_attachment_value_text').length;
                    var total_allow =". esc_attr(jssupportticket::$_config['no_of_attachement']) ."
                    var append_text = '<span class=\'tk_attachment_value_text\'><input name=\'filename[]\' type=\'file\' onchange=\'uploadfile(this,\'". esc_js(jssupportticket::$_config['file_maximum_size']) ."\',\'". esc_js(jssupportticket::$_config['file_extension']) ."\');\' size=\'20\'  /><span  class=\'tk_attachment_remove\'></span></span>';
                    if (current_files < total_allow) {
                        jQuery('.tk_attachment_value_wrapperform.'+att_flag).append(append_text);
                    } else if ((current_files === total_allow) || (current_files > total_allow)) {
                        alert('". esc_html(__('File upload limit exceeds', 'js-support-ticket')) ."');
                    }
                });
                jQuery(document).delegate('.tk_attachment_remove', 'click', function (e) {
                    jQuery(this).parent().remove();
                    var current_files = jQuery('input[type=\'file\']').length;
                    var total_allow =". esc_attr(jssupportticket::$_config['no_of_attachement']) .";
                    if (current_files < total_allow) {
                        jQuery('#tk_attachment_add').show();
                    }
                });
                jQuery('a#showhidedetail').click(function (e) {
                    e.preventDefault();
                    var divid = jQuery(this).attr('data-divid');
                    jQuery('div#' + divid).slideToggle();
                    jQuery(this).find('img').toggleClass('js-hidedetail');
                });

                jQuery('a#showhistory').click(function (e) {
                    e.preventDefault();
                    jQuery('div#userpopup').slideDown('slow');
                    jQuery('div#userpopupblack').show();
                });
                jQuery('a#changepriority').click(function (e) {
                    e.preventDefault();
                    jQuery('div#userpopupforchangepriority').slideDown('slow');
                    jQuery('div#userpopupblack').show();
                });

                jQuery('div#userpopupblack,span.close-history,span.close-credentails').click(function (e) {
                    jQuery('div#userpopup').slideUp('slow');
                    jQuery('div#userpopupforchangepriority').slideUp('slow');
                    jQuery('div#popupfordepartmenttransfer').slideUp('slow');
                    jQuery('#usercredentailspopup').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div#userpopupblack').hide();
                    }, 700);
                });

                jQuery('a#departmenttransfer').click(function (e) {
                    e.preventDefault();
                    jQuery('div#popupfordepartmenttransfer').slideDown('slow');
                    jQuery('#userpopupblack').show();
                });

                jQuery('a#agenttransfer').click(function (e) {
                    e.preventDefault();
                    jQuery('div#popupforagenttransfer').slideDown('slow');
                    jQuery('.jsst-popup-background').show();
                });
                jQuery(document).delegate('div#popupforagenttransfer .popup-header-close-img', 'click', function (e) {
                    jQuery('div#popupforagenttransfer').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });
                jQuery(document).delegate('div#popupfordepartmenttransfer .popup-header-close-img', 'click', function (e) {
                    jQuery('div#popupfordepartmenttransfer').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });
                jQuery(document).delegate('div#popupforinternalnote .popup-header-close-img', 'click', function (e) {
                    jQuery('div#popupforinternalnote').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });

                jQuery('a#internalnotebtn').click(function (e) {
                    e.preventDefault();
                    jQuery('div#popupforinternalnote').slideDown('slow');
                    jQuery('.jsst-popup-background').show();
                });

                jQuery(document).delegate('#close-pop, img.close-merge', 'click', function (e) {
                    jQuery('div#mergeticketselection').fadeOut();
                    jQuery('div#popup-record-data').html('');
                });

                jQuery('div.popup-header-close-img,div.jsst-popup-background,input#cancele,input#cancelee,input#canceleee,input#canceleeee,input#canceleeeee,input#canceleeeeee').click(function (e) {
                    jQuery('div.jsst-popup-wrapper').slideUp('slow');
                    jQuery('div#popupforinternalnote').slideUp('slow');
                    jQuery('div#popupforagenttransfer').slideUp('slow');
                    jQuery('div.jsst-merge-popup-wrapper').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div.jsst-popup-background').hide();
                        jQuery('div.jsst-popup-wrapper').hide();
                        jQuery('#userpopupblack').hide();
                    }, 700);
                });

                jQuery(document).delegate('#ticketpopupsearch','submit', function (e) {
                    var ticketid = jQuery('#ticketidformerge').val();
                    e.preventDefault();
                    var name = jQuery('input#name').val();
                    var email = jQuery('input#email').val();
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'mergeticket', task: 'getTicketsForMerging', name: name, email: email,ticketid:ticketid, '_wpnonce':'". esc_attr(wp_create_nonce('get-tickets-for-merging')) ."'}, function (data) {
                        data=jQuery.parseJSON(data);
                       if(data !== 'undefined') {
							if(data !== '') {
								jQuery('div#popup-record-data').html('');
								jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
							}else{
								jQuery('div#popup-record-data').html('');
							}
                        }else{
                            jQuery('div#popup-record-data').html('');
                        }
                    });//jquery closed
                });

                jQuery('a#print-link').click(function (e) {
                    e.preventDefault();
                    var href = '". jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'printticket','jssupportticketid'=>jssupportticket::$_data[0]->id)) ."';
                    print = window.open(href, 'print_win', 'width=1024, height=800, scrollbars=yes');
                });

                //non premium support function
                jQuery('#nonpreminumsupport').change(function(){
                    if(jQuery(this).is(':checked')){
                        if(1 || confirm('". esc_html(__('Are you sure to mark this ticket non-preminum?','js-support-ticket')) ."')){
                            markUnmarkTicketNonPremium(1);
                        }else{
                            jQuery(this).removeAttr('checked');
                        }
                    }else{
                        markUnmarkTicketNonPremium(0);
                    }
                });

                jQuery('#paidsupportlinkticketbtn').click(function(){
                    var ticketid = jQuery('#ticketid').val();
                    var paidsupportitemid = jQuery('#paidsupportitemid').val();
                    if(paidsupportitemid > 0){
                        jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'paidsupport', task: 'linkTicketPaidSupportAjax', ticketid: ticketid, paidsupportitemid:paidsupportitemid, '_wpnonce':'". esc_attr(wp_create_nonce('link-ticket-paidsupport-ajax')) ."'}, function (data) {
                            window.location.reload();
                        });
                    }
                });

            });

            function markUnmarkTicketNonPremium(mark){
                var ticketid = jQuery('#ticketid').val();
                var paidsupportitemid = jQuery('#paidsupportitemid').val();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'paidsupport', task: 'markUnmarkTicketNonPremiumAjax', status: mark, ticketid: ticketid, paidsupportitemid:paidsupportitemid, '_wpnonce':'". esc_attr(wp_create_nonce('mark-unmark-ticket-nonpremium-ajax')) ."'}, function (data) {
                    window.location.reload();
                });
            }

            function actionticket(action) {
                /*  Action meaning
                 * 1 -> Change Priority
                 * 2 -> Close Ticket
                 * 2 -> Reopen Ticket
                 */
                if(action == 1){
                    jQuery('#priority').val(jQuery('#prioritytemp').val());
                }
                jQuery('input#actionid').val(action);
                jQuery('form#adminTicketform').submit();
            }

            function getmergeticketid(mergeticketid, mergewithticketid){
                if(mergewithticketid == 0){
                    mergewithticketid =  jQuery('#mergeticketid').val();
                }else{
                    jQuery('#mergeticketid').val(mergewithticketid);
                }
                if(mergeticketid == mergewithticketid){
                    alert('Primary id must be differ from merge ticket id');
                    return false;
                }
                jQuery('#mergeticketselection').hide();
                getTicketdataForMerging(mergeticketid,mergewithticketid);
            }

            function getTicketdataForMerging(mergeticketid,mergewithticketid){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'mergeticket', task: 'getLatestReplyForMerging', mergeid:mergeticketid,mergewith:mergewithticketid, '_wpnonce':'".  esc_attr(wp_create_nonce('get-latest-reply-for-merging')) ."'}, function (data) {
                    if(data){
                        data = jQuery.parseJSON(data);
                        jQuery('div#popup-record-data').html('');
                        jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                    }
                });
            }
            function closePopup(){
                setTimeout(function () {
                    jQuery('div.jsst-popup-background').hide();
                    jQuery('div#userpopupblack').hide();
                    }, 700);

                jQuery('div.jsst-popup-wrapper').slideUp('slow');
                jQuery('div#userpopupforchangepriority').slideUp('slow');
                jQuery('div#popupfordepartmenttransfer').slideUp('slow');
                jQuery('div#userpopup').slideUp('slow');

            }

            function checktinymcebyid(id) {
                var content = tinymce.get(id).getContent({format: 'text'});
                if (jQuery.trim(content) == '')
                {
                    alert('". esc_html(__('Some values are not acceptable please retry', 'js-support-ticket')) ."');
                    return false;
                }
                return true;
            }

            jQuery(document).delegate('#ticketidcopybtn', 'click', function(){
                var temp = jQuery('<input>');
                jQuery('body').append(temp);
                temp.val(jQuery('#ticketrandomid').val()).select();
                document.execCommand('copy');
                temp.remove();
                jQuery('#ticketidcopybtn').text(jQuery('#ticketidcopybtn').attr('success'));
            });

            function resetMergeFrom() {
                var ticketid = jQuery('#ticketidformerge').val();
                var name = '';
                var email = '';
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'mergeticket', task: 'getTicketsForMerging', name: name, email: email,ticketid:ticketid, '_wpnonce':'". esc_attr(wp_create_nonce('get-tickets-for-merging')) ."'}, function (data) {
                    data=jQuery.parseJSON(data);
                   if(data !== 'undefined') {
                        if(data !== '') {
                            jQuery('div#popup-record-data').html('');
                            jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                        }else{
                            jQuery('div#popup-record-data').html('');
                        }
                    }else{
                        jQuery('div#popup-record-data').html('');
                    }
                });//jquery closed
            }
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        $yesno = array(
            (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
            (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
        );
        ?>
        <div id="userpopupblack" style="display:none;"> </div>
        <?php
        $jssupportticket_js ="
            jQuery(document).ready(function(){
                jQuery(document).on('submit','#js-ticket-usercredentails-form',function(e){
                    e.preventDefault(); // avoid to execute the actual submit of the form.
                    var fdata = jQuery(this).serialize(); // serializes the form's elements.
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'storePrivateCredentials',formdata_string:fdata, '_wpnonce':'". esc_attr(wp_create_nonce('store-private-credentials')) ."'}, function (data) {
                        if(data){ // ajax executed
                            var return_data = jQuery.parseJSON(data);
                            if(return_data.status == 1){
                                jQuery('.js-ticket-usercredentails-wrp').show();
                                jQuery('.js-ticket-usercredentails-form-wrap').hide();
                                jQuery('.js-ticket-usercredentails-credentails-wrp').append(jsstDecodeHTML(return_data.content));
                            }else{
                                alert(return_data.error_message);
                            }
                        }
                    });
                })
            });

            function addEditCredentail(ticketid, uid, cred_id = 0, cred_data = ''){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'getFormForPrivteCredentials', ticketid: ticketid, cred_id: cred_id, cred_data: cred_data, uid: uid, '_wpnonce':'". esc_attr(wp_create_nonce('get-form-for-privte-credentials')) ."'}, function (data) {
                    if(data){ // ajax executed
                        var return_data = jQuery.parseJSON(data);
                        jQuery('.js-ticket-usercredentails-wrp').hide();
                        jQuery('.js-ticket-usercredentails-form-wrap').show();
                        jQuery('.js-ticket-usercredentails-form-wrap').html(jsstDecodeHTML(return_data));
                        if(cred_id != 0){
                            jQuery('#js-ticket-usercredentails-single-id-'+cred_id).remove();
                        }
                    }
                });
            }

            function getCredentails(ticketid){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'getPrivateCredentials',ticketid:ticketid, '_wpnonce':'". esc_attr(wp_create_nonce('get-private-credentials')) ."'}, function (data) {
                    if(data){ // ajax executed
                        var return_data = jQuery.parseJSON(data);
                        if(return_data.status == 1){
                            jQuery('#userpopupblack').show();
                            jQuery('#usercredentailspopup').slideDown('slow');
                            jQuery('.js-ticket-usercredentails-wrp').slideDown('slow');
                            jQuery('.js-ticket-usercredentails-form-wrap').hide();
                            if(return_data.content != ''){
                                jQuery('.js-ticket-usercredentails-credentails-wrp').html('');
                                jQuery('.js-ticket-usercredentails-credentails-wrp').append(jsstDecodeHTML(return_data.content));
                            }
                        }
                    }
                });
                return false;
            }

            function removeCredentail(cred_id){
                var params = {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'removePrivateCredential',cred_id:cred_id, '_wpnonce':'". esc_attr(wp_create_nonce('remove-private-credential')) ."'};
                ";
                if(JSSTincluder::getObjectClass('user')->isguest() && isset(jssupportticket::$_data[0]->id)){
                    $jssupportticket_js .='
                    params.email = "'. esc_attr(jssupportticket::$_data[0]->email) .'";
                    params.ticketrandomid = "'. esc_attr(jssupportticket::$_data[0]->ticketid) .'";
                    ';
                }
                $jssupportticket_js .="
                jQuery.post(ajaxurl, params, function (data) {
                    if(data){ // ajax executed
                        if(cred_id != 0){
                            jQuery('#js-ticket-usercredentails-single-id-'+cred_id).remove();
                        }
                    }
                });
                return false;
            }
            function closeCredentailsForm(ticketid){
                getCredentails(ticketid);
            }
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
        <div id="usercredentailspopup" style="display: none;">
            <div class="js-ticket-usercredentails-header">
                <?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?><span class="close-credentails"></span>
            </div>
            <div class="js-ticket-usercredentails-wrp" style="display: none;">
                <div class="js-ticket-usercredentails-credentails-wrp">
                </div>
                <?php
                    if(in_array('privatecredentials',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5 ){
                        $credential_add_permission = false;
                        if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                            $credential_add_permission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Add Credentials');
                        }elseif(current_user_can('manage_options')){
                            $credential_add_permission = true;
                        }elseif (JSSTincluder::getObjectClass('user')->uid() != 0) {
                            if(JSSTincluder::getObjectClass('user')->uid() == jssupportticket::$_data[0]->uid){
                                $credential_add_permission = true;
                            }
                        }elseif(JSSTincluder::getObjectClass('user')->uid() == 0){
                            $credential_add_permission = true;
                        }
                        if($credential_add_permission){ ?>
                            <div class="js-ticket-usercredentail-data-add-new-button-wrap" >
                                <button class="js-ticket-usercredentail-data-add-new-button" onclick="addEditCredentail(<?php echo esc_js(jssupportticket::$_data[0]->id);?>,<?php echo esc_js(JSSTincluder::getObjectClass('user')->uid());?>);" >
                                    <?php echo esc_html(__("Add New Credential","js-support-ticket")); ?>
                                </button>
                            </div><?php
                        }
                    }
                    ?>
            </div>
            <div class="js-ticket-usercredentails-form-wrap" >
            </div>
        </div>

        <div id="userpopup" style="display:none;"><!-- Ticket History popup -->
            <div class="js-row js-ticket-popup-row">
                <form id="userpopupsearch">
                    <div class="search-center-history"><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?><span class="close-history"></span></div>
                </form>
            </div>
            <div id="records">
                <?php // data[5] holds the tickect history
                $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
                if ((!empty(jssupportticket::$_data[5]))) {
                    ?>
                    <div class="js-ticket-history-table-wrp">
                        <table class="js-table js-table-striped">
                            <thead>
                              <tr>
                                <th class="js-ticket-textalign-center"><?php echo esc_html(__('Date','js-support-ticket'));?></th>
                                <th class="js-ticket-textalign-center"><?php echo esc_html(__('Time','js-support-ticket'));?></th>
                                <th class=""><?php echo esc_html(__('Message Logs','js-support-ticket'));?></th>
                              </tr>
                            </thead>
                            <tbody class="js-ticket-ticket-history-body">
                                <?php foreach (jssupportticket::$_data[5] AS $history) { ?>
                                  <tr>
                                    <td class="js-ticket-textalign-center"><?php echo esc_html(date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($history->datetime))); ?></td>
                                    <td class="js-ticket-textalign-center"><?php echo esc_html(date_i18n('H:i:s', jssupportticketphplib::JSST_strtotime($history->datetime))); ?></td>
                                    <?php
                                        if (is_super_admin($history->uid)) {
                                            $message = 'admin';
                                        } elseif ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff($history->uid)) {
                                            $message = 'agent';
                                        } else {
                                            $message = 'member';
                                        }
                                        ?>
                                    <td class=""><?php 
										if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){ //agent
											echo wp_kses_post($history->message); 
										}else{
											if($message == 'member'){ // message by the user, so show full message to user
												echo wp_kses_post($history->message); 
											}else{
												if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) { 
													$historymessage = $history->message;
													echo wp_kses_post(preg_replace("/\([^)]+\)/","( ".__("Agent")." )",$historymessage));
												}else{
													echo wp_kses_post($history->message); 
												}
											}
										}											
									?></td>
                                  </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="js-ticket-priorty-btn-wrp">
                            <?php echo wp_kses(JSSTformfield::button('canceleee', esc_html(__('Close', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php
        $jssupportticket_js ="
            function showPopupAndFillValues(id,pfor) {
                jQuery('div.edit-time-popup').hide();
                if(pfor == 1){
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'reply', task: 'getReplyDataByID', '_wpnonce':'". esc_attr(wp_create_nonce('get-reply-data-by-id')) ."'}, function (data) {
                        if (data) {
                            jQuery('div.popup-header-text').html('". esc_html(__('Edit Reply','js-support-ticket')) ."');
                            d = jQuery.parseJSON(data);
                            tinyMCE.get('jsticket_replytext').execCommand('mceSetContent', false, jsstDecodeHTML(d.message));
                            jQuery('div.edit-time-popup').hide();
                            jQuery('form#jsst-time-edit-form').hide();
                            jQuery('form#jsst-note-edit-form').hide();
                            jQuery('form#jsst-reply-form').show();
                            jQuery('input#reply-replyid').val(id);
                            jQuery('div.jsst-popup-background').show();
                            jQuery('div#jsst-popup-wrapper').slideDown('slow');
                        }
                    });
                }else if(pfor == 2){
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'timetracking', task: 'getTimeByReplyID', '_wpnonce':'". esc_attr(wp_create_nonce('get-time-by-reply-id')) ."'}, function (data) {
                        if (data) {
                            jQuery('div.popup-header-text').html('". esc_html(__('Edit Time','js-support-ticket')) ."');
                            d = jQuery.parseJSON(data);
                            jQuery('div.edit-time-popup').hide();
                            jQuery('form#jsst-reply-form').hide();
                            jQuery('form#jsst-note-edit-form').hide();
                            jQuery('div.system-time-div').hide();
                            jQuery('form#jsst-time-edit-form').show();
                            jQuery('input#reply-replyid').val(id);
                            jQuery('div.jsst-popup-background').show();
                            jQuery('div#jsst-popup-wrapper').slideDown('slow');
                            jQuery('input#edited_time').val(d.time);
                            jQuery('textarea#edit_reason').text(jsstDecodeHTML(d.desc));
                            if(d.conflict == 1){
                                jQuery('div.system-time-div').show();
                                jQuery('input#time-confilct').val(d.conflict);
                                jQuery('input#systemtime').val(d.systemtime);
                                jQuery('select#time-confilct-combo').val(0);
                            }
                        }
                    });
                }else if(pfor == 3){
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'note', task: 'getTimeByNoteID', '_wpnonce':'". esc_attr(wp_create_nonce('get-time-by-note-id')) ."'}, function (data) {
                        if (data) {
                            jQuery('div.popup-header-text').html('". esc_html(__('Edit Time','js-support-ticket')) ."');
                            d = jQuery.parseJSON(data);
                            jQuery('div.edit-time-popup').hide();
                            jQuery('form#jsst-reply-form').hide();
                            jQuery('form#jsst-note-edit-form').show();
                            jQuery('form#jsst-time-edit-form').hide();
                            jQuery('div.system-time-div').hide();
                            jQuery('input#note-noteid').val(id);
                            jQuery('div.jsst-popup-background').show();
                            jQuery('div#jsst-popup-wrapper').slideDown('slow');
                            jQuery('input#edited_time').val(d.time);
                            jQuery('textarea#edit_reason').text(jsstDecodeHTML(d.desc));
                            if(d.conflict == 1){
                                jQuery('div.system-time-div').show();
                                jQuery('input#time-confilct').val(d.conflict);
                                jQuery('input#systemtime').val(d.systemtime);
                                jQuery('select#time-confilct-combo').val(0);
                            }
                        }
                    });
                }else if(pfor == 4){
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', ticketid: id, jstmod: 'mergeticket', task: 'getTicketsForMerging', '_wpnonce':'". esc_attr(wp_create_nonce('get-tickets-for-merging')) ."'}, function (data) {
                        if (data) {
                            jQuery('div.popup-header-text').html('". esc_html(__('Merge Ticket','js-support-ticket')) ."');
                            data=jQuery.parseJSON(data);
                            jQuery('div#popup-record-data').html('');
                            jQuery('div#popup-record-data').slideDown('slow');
                            jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                        }
                    });
                }
                return false;
            }
            function updateticketlist(pagenum,ticketid){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'mergeticket', task: 'getTicketsForMerging', ticketid:ticketid,ticketlimit:pagenum, '_wpnonce':'". esc_attr(wp_create_nonce('get-tickets-for-merging')) ."'}, function (data) {
                    if(data){
                        data=jQuery.parseJSON(data);
                            jQuery('div#popup-record-data').html('');
                            jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                    }
                });
            }
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
        ?>
        <span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
        <span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
        <div class="jsst-popup-background" style="display:none" ></div>
        <div id="popup-record-data" style="display:inline-block;width:100%;"></div>
        <div id="jsst-popup-wrapper" class="jsst-popup-wrapper" style="display:none" ><!-- Js Ticket Edit Time Popups -->
            <div class="jsst-popup-header" >
                <div class="popup-header-text" >
                    <?php echo esc_html(__('Edit Timer','js-support-ticket')); ?>
                </div>
                <div class="popup-header-close-img" >
                </div>
            </div>
            <div class="edit-time-popup" style="display:none;" >
                <div class="js-ticket-edit-form-wrp">
                    <div class="js-ticket-edit-field-title">
                        <?php echo esc_html(__('Time', 'js-support-ticket')); ?>&nbsp;<span style="color: red">*</span>
                    </div>
                    <div class="js-ticket-edit-field-wrp">
                        <?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox js-ticket-edit-field-input')), JSST_ALLOWED_TAGS) ?>
                    </div>
                    <div class="js-ticket-edit-field-title">
                        <?php echo esc_html(__('Reason For Editing the timer', 'js-support-ticket')); ?>
                    </div>
                    <div class="js-ticket-edit-field-wrp">
                        <?php echo wp_kses(JSSTformfield::textarea('t_desc', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <div class="js-ticket-priorty-btn-wrp">
                        <?php echo wp_kses(JSSTformfield::submitbutton('pok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save','onclick' => 'updateTimerFromPopup();')), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::button('canceleeee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                    </div>
                </div>
            </div>

            <form id="jsst-reply-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedreply&action=jstask"),"save-edited-reply")); ?>" >
                <div class="js-ticket-edit-form-wrp">
                    <div class="js-ticket-form-field-wrp">
                        <?php wp_editor('', 'jsticket_replytext', array('media_buttons' => false,'editor_height' => 200, 'textarea_rows' => 20,)); ?>
                    </div>
                </div>
                <div class="js-ticket-priorty-btn-wrp">
                    <?php echo wp_kses(JSSTformfield::submitbutton('ppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save')), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::button('canceleeeee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>

                <form id="jsst-time-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedtime&action=jstask"),"save-edited-time")); ?>" >
                    <div class="js-ticket-edit-form-wrp">
                        <div class="js-ticket-edit-field-title">
                            <?php echo esc_html(__('Time', 'js-support-ticket')); ?>&nbsp;<span style="color: red">*</span>
                        </div>
                        <div class="js-ticket-edit-field-wrp">
                            <?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox js-ticket-edit-field-input')), JSST_ALLOWED_TAGS) ?>
                        </div>
                        <div class="js-ticket-edit-field-title">
                            <?php echo esc_html(__('System Time', 'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-edit-field-wrp">
                            <?php echo wp_kses(JSSTformfield::text('systemtime', '', array('class' => 'inputbox js-ticket-edit-field-input','disabled'=>'disabled')), JSST_ALLOWED_TAGS) ?>
                        </div>
                        <div class="js-ticket-edit-field-title">
                            <?php echo esc_html(__('Reason For Editing', 'js-support-ticket')); ?>
                        </div>
                        <div class="js-ticket-edit-field-wrp">
                            <?php echo wp_kses(JSSTformfield::textarea('edit_reason', '', array('class' => 'inputbox js-ticket-edit-field-input')), JSST_ALLOWED_TAGS) ?>
                        </div>
                        <div class="js-form-wrapper system-time-div" style="display:none;" >
                            <div class="js-form-title"><?php echo esc_html(__('Resolve conflict', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $yesno, ''), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                        <div class="js-ticket-priorty-btn-wrp">
                            <?php echo wp_kses(JSSTformfield::submitbutton('pppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save','onclick' => 'updateTimerFromPopup();')), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::button('canceleeeeee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('time-confilct',''), JSST_ALLOWED_TAGS); ?>
                </form>

                <form id="jsst-note-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=note&task=saveeditedtime&action=jstask"),"save-edited-time")); ?>" >
                    <div class="js-col-md-12 js-form-wrapper">
                        <div class="js-col-md-12 js-form-title"><?php echo esc_html(__('Time', 'js-support-ticket')); ?></div>
                        <div class="js-col-md-12 js-form-value"><?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
                    </div>
                    <div class="js-col-md-12 js-form-wrapper system-time-div" style="display:none;" >
                        <div class="js-col-md-12 js-form-title"><?php echo esc_html(__('System Time', 'js-support-ticket')); ?></div>
                        <div class="js-col-md-12 js-form-value"><?php echo wp_kses(JSSTformfield::text('systemtime', '', array('class' => 'inputbox','disabled'=>'disabled')), JSST_ALLOWED_TAGS) ?></div>
                    </div>
                    <div class="js-col-md-12 js-form-wrapper">
                        <div class="js-col-md-12 js-form-title"><?php echo esc_html(__('Reason For Editing', 'js-support-ticket')); ?></div>
                        <div class="js-col-md-12 js-form-value"><?php echo wp_kses(JSSTformfield::textarea('edit_reason', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
                    </div>
                    <div class="js-col-md-12 js-form-wrapper system-time-div" style="display:none;" >
                        <div class="js-col-md-12 js-form-title"><?php echo esc_html(__('Resolve conflict', 'js-support-ticket')); ?></div>
                        <div class="js-col-md-12 js-form-value"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $yesno, ''), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-col-md-12 js-form-button-wrapper">
                        <?php echo wp_kses(JSSTformfield::submitbutton('ppppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button')),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::button('cancele', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'button', 'onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('note-noteid', ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('note-tikcetid',jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('time-confilct',''), JSST_ALLOWED_TAGS); ?>
                </form>
            <?php } ?>
        </div>
        <div class="jsst-popup-wrapper jsst-merge-popup-wrapper" style="display:none" >
            <div class="jsst-popup-header" >
                <div class="popup-header-text" >
                    <?php echo esc_html(__('Edit Timer','js-support-ticket')); ?>
                </div>
                <div class="popup-header-close-img" >
                </div>
            </div>
        </div>

        <?php
        if($printflag == false && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5){
            ?>

            <?php if(in_array('actions',jssupportticket::$_active_addons)){ 
				if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer')){
				?>
				<div id="popupfordepartmenttransfer" style="display:none" >
					<div class="jsst-popup-header" >
						<div class="popup-header-text" >
							<?php echo esc_html(__('Department Transfer', 'js-support-ticket')); ?>
						</div>
						<div class="popup-header-close-img" >
						</div>
					</div>
					<div>
						<form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'transferdepartment')),"transfer-department")); ?>" enctype="multipart/form-data">
							<div class="js-ticket-premade-msg-wrp"><!-- Select Department Wrapper -->
								<div class="js-ticket-premade-field-title"><?php echo esc_html(__('Select Department', 'js-support-ticket')); ?></div>
								<div class="js-ticket-premade-field-wrp">
									<?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), isset(jssupportticket::$_data[0]->departmentid) ? jssupportticket::$_data[0]->departmentid : '', esc_html(__('Select Department', 'js-support-ticket')), array('class' => 'js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>

								</div>
							</div>
							<?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
								<div class="js-ticket-text-editor-wrp">
									<div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Type Note for Department', 'js-support-ticket')); ?></div>
									<div class="js-ticket-text-editor-field"><?php wp_editor('', 'departmenttranfernote', array('media_buttons' => false)); ?></div>
								</div>
							<?php } ?>
							<div class="js-ticket-reply-form-button-wrp">
								<?php echo wp_kses(JSSTformfield::submitbutton('departmenttransferbutton', esc_html(__('Transfer', 'js-support-ticket')), array('class' => 'button js-ticket-save-button', 'onclick' => "return checktinymcebyid('departmenttranfernote');")), JSST_ALLOWED_TAGS); ?>
							</div>
							<?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_transferdepartment'), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
						</form>
					</div> <!-- end of departmenttransfer div -->
				</div>
				<?php } ?>
            <?php } ?>

            <?php if(in_array('agent',jssupportticket::$_active_addons)){ 
				if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Assign Ticket To Agent')){
				?>
				<div id="popupforagenttransfer" style="display:none" >
					<div class="jsst-popup-header" >
						<div class="popup-header-text" >
							<?php echo esc_html(__('Assign To Agent', 'js-support-ticket')); ?>
						</div>
						<div class="popup-header-close-img" >
						</div>
					</div>
					<div>
						<form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'assigntickettostaff')),"assign-ticket-to-staff")); ?>" enctype="multipart/form-data">
							<div class="js-ticket-premade-msg-wrp"><!-- Select Department Wrapper -->
								<div class="js-ticket-premade-field-title"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></div>
								<div class="js-ticket-premade-field-wrp">
									<?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), jssupportticket::$_data[0]->staffid, esc_html(__('Select Agent', 'js-support-ticket')), array('class' => 'inputbox js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>
								</div>
							</div>
							<?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
								<div class="js-ticket-text-editor-wrp">
									<div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Assigning Note', 'js-support-ticket')); ?></div>
									<div class="js-ticket-text-editor-field"><?php wp_editor('', 'assignnote', array('media_buttons' => false)); ?></div>
								</div>
							<?php } ?>
							<div class="js-ticket-reply-form-button-wrp">
								<?php echo wp_kses(JSSTformfield::submitbutton('assigntostaff', esc_html(__('Assign', 'js-support-ticket')), array('class' => 'button js-ticket-save-button', 'onclick' => "return checktinymcebyid('assignnote');")), JSST_ALLOWED_TAGS); ?>
							</div>
							<?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_assigntickettostaff'), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
							<?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
						</form>
					</div> <!-- end of assigntostaff div -->
				</div>
				<?php } ?>
            <?php } ?>

            <?php if(in_array('note',jssupportticket::$_active_addons)){ ?>
            <div id="popupforinternalnote" style="display:none" >
                <div class="jsst-popup-header" >
                    <div class="popup-header-text" >
                        <?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?>
                    </div>
                    <div class="popup-header-close-img" >
                    </div>
                </div>
                <div>  <!--  postinternalnote Area   -->
                    <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'note','task'=>'savenote')),"save-note")); ?>" enctype="multipart/form-data">
                        <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                            <div class="jsst-ticket-detail-timer-wrapper"> <!-- Top Timer Section -->
                                <div class="timer-left" >
                                <?php echo esc_html(__('Time Track','js-support-ticket')); ?>
                                </div>
                                <div class="timer-right" >
                                    <div class="timer-total-time" >
                                        <?php
                                            $hours = floor(jssupportticket::$_data['time_taken'] / 3600);
                                            $mins = floor(jssupportticket::$_data['time_taken'] / 60);
                                            $mins = floor($mins % 60);
                                            $secs = floor(jssupportticket::$_data['time_taken'] % 60);
                                            echo esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                        ?>
                                    </div>
                                    <div class="timer" >
                                        00:00:00
                                    </div>
                                    <div class="timer-buttons" >
                                        <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time')){ ?>
                                            <span class="timer-button" onclick="showEditTimerPopup()" >
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/timer-edit.png"/>
                                            </span>
                                        <?php } ?>
                                        <span class="timer-button cls_1" onclick="changeTimerStatus(1)" >
                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play.png"/>
                                        </span>
                                        <span class="timer-button cls_2" onclick="changeTimerStatus(2)" >
                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/pause.png"/>
                                        </span>
                                        <span class="timer-button cls_3" onclick="changeTimerStatus(3)" >
                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/stop.png"/>
                                        </span>
                                    </div>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>

                                <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                            </div>
                        <?php } ?>
                        <div class="js-ticket-internalnote-wrp"><!-- Ticket Tittle -->
                            <div class="js-ticket-internalnote-field-title"><?php echo esc_html(__('Title', 'js-support-ticket')); ?></div>
                            <div class="js-ticket-internalnote-field-wrp">
                            <?php echo wp_kses(JSSTformfield::text('internalnotetitle', '', array('class' => 'inputbox js-ticket-internalnote-input')), JSST_ALLOWED_TAGS) ?>
                            </div>
                        </div>
                        <div class="js-ticket-text-editor-wrp">
                            <div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Type Internal Note', 'js-support-ticket')); ?></div>
                            <div class="js-ticket-text-editor-field"><?php wp_editor('', 'internalnote', array('media_buttons' => false)); ?></div>
                        </div>
                        <div class="js-ticket-reply-attachments"><!-- Attachments -->
                            <div class="js-attachment-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['attachments'])); ?></div>
                            <div class="js-attachment-field">
                                <div class="tk_attachment_value_wrapperform tk_attachment_staff_reply_wrapper">
                                    <span class="tk_attachment_value_text">
                                        <input type="file" class="inputbox js-attachment-inputbox" name="note_attachment" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" />
                                        <span class='tk_attachment_remove'></span>
                                    </span>
                                </div>
                                <span class="tk_attachments_configform">
                                    <?php echo esc_html(__('Maximum File Size', 'js-support-ticket'));
                                          echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php echo esc_html(__('File Extension Type', 'js-support-ticket'));
                                          echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="js-ticket-closeonreply-wrp">
                            <div class="js-ticket-closeonreply-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></div>
                            <div class="replyFormStatus js-form-title-position-reletive-left">
                                <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close on reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <div class="js-ticket-reply-form-button-wrp">
                            <?php echo wp_kses(JSSTformfield::submitbutton('postinternalnote', esc_html(__('Post Internal Note', 'js-support-ticket')), array('class' => 'button js-ticket-save-button', 'onclick' => "return checktinymcebyid('internalnote');")), JSST_ALLOWED_TAGS); ?>
                        </div>

                        <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('action', 'note_savenote'), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    </form>
                </div> <!-- end of postinternalnote div -->
            </div>
            <?php } ?>

            <?php
        }
        ?>



        <?php
            jssupportticket::$_data['custom']['ticketid'] = jssupportticket::$_data[0]->id;
                if (jssupportticket::$_data[0]->lock == 1) {
                    $style = "darkred";
                    $status = esc_html(__('Lock', 'js-support-ticket'));
                } elseif (jssupportticket::$_data[0]->status == 0) {
                    $style = "#5bb12f";
                    $status = esc_html(__('New', 'js-support-ticket'));
                } elseif (jssupportticket::$_data[0]->status == 1) {
                    $style = "#28abe3";
                    $status = esc_html(__('Waiting Reply', 'js-support-ticket'));
                } elseif (jssupportticket::$_data[0]->status == 2) {
                    $style = "#69d2e7";
                    $status = esc_html(__('In Progress', 'js-support-ticket'));
                } elseif (jssupportticket::$_data[0]->status == 3) {
                    $style = "#FFB613";
                    $status = esc_html(__('Replied', 'js-support-ticket'));
                } elseif (jssupportticket::$_data[0]->status == 4) {
                    $style = "#ed1c24";
                    $status = esc_html(__('Closed', 'js-support-ticket'));
                }
                $cur_uid = JSSTincluder::getObjectClass('user')->uid();
                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']) {
                    $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'actionticket')),"action-ticket");
                } else {
                    $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply");
                }
                ?>
                <div class="js-ticket-ticket-detail-wrapper">
                   <?php if($printflag != true){?>
                        <form method="post" action="<?php echo esc_url($link); ?>" id="adminTicketform" enctype="multipart/form-data">
                    <?php } ?>
                    <!-- Ticket Detail Left -->
                    <div class="js-tkt-det-left">
                        <div class="js-tkt-det-cnt js-tkt-det-info-wrp"><!-- Ticket Detail Info Wrp -->
                            <div class="js-tkt-det-user"><!-- Ticket Detail Box -->
                                <div class="js-tkt-det-user-image"><!-- Left Side Image -->
                                    <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->staffphotophoto) { ?>
                                        <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                    <?php } else {
                                        echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById(jssupportticket::$_data[0]->uid), 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                    } ?>
                                </div>
                                <div class="js-tkt-det-user-cnt"><!-- Right Side -->
                                    <div class="js-tkt-det-user-data name">
                                        <?php echo esc_html(jssupportticket::$_data[0]->name); ?>
                                    </div>
                                    <div class="js-tkt-det-user-data subject">
                                       <?php echo esc_html(jssupportticket::$_data[0]->subject); ?>
                                    </div>
                                    <div class="js-tkt-det-user-data email">
                                        <?php echo esc_html(jssupportticket::$_data[0]->email); ?>
                                    </div>
                                    <div class="js-tkt-det-user-data number">
                                        <?php echo esc_html(jssupportticket::$_data[0]->phone); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="js-tkt-det-other-tkt"><!-- Ticket Detail View Btn -->
                                <?php
                                if(isset(jssupportticket::$_data['nticket'])){
                                    if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                                        $url = jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffmyticket','uid'=>jssupportticket::$_data[0]->uid));
                                    }else{
                                        $url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'));
                                    }
                                    ?>
                                    <a class="js-tkt-det-other-tkt-btn" href="<?php echo esc_url($url); ?>">
                                        <?php
                                        if(in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']){
											echo esc_html(__('View all','js-support-ticket')).' '.esc_html(jssupportticket::$_data['nticket']).' '. esc_html(__('tickets by','js-support-ticket')).' '.esc_html(jssupportticket::$_data[0]->name);
                                        }else{
											echo esc_html(__('View all','js-support-ticket')).' '.esc_html(jssupportticket::$_data['nticket']).' '. esc_html(__('tickets','js-support-ticket'));
                                        }
                                        ?>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="js-tkt-det-tkt-msg"><!-- Ticket Detail Message -->
                                <?php echo wp_kses_post(jssupportticket::$_data[0]->message); ?>

                                <?php
                                jssupportticket::$_data['custom']['ticketid'] = jssupportticket::$_data[0]->id;
                                $customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, null, jssupportticket::$_data[0]->multiformid);
                                if (!empty($customfields)){
                                    ?>
                                    <div class="js-tkt-det-tkt-custm-flds">
                                        <?php
                                        foreach ($customfields as $field) {
                                            $ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($field,2, jssupportticket::$_data[0]->params);
                                            ?>
                                            <div class="js-tkt-det-info-data">
                                                <div class="js-tkt-det-info-tit">
                                                    <?php echo wp_kses($ret['title'], JSST_ALLOWED_TAGS).' : '; ?>
                                                </div>
                                                <div class="js-tkt-det-info-val">
                                                    <?php echo wp_kses($ret['value'], JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="js-tkt-det-actn-btn-wrp"> <!-- Ticket Action Button -->
                                <?php if ($printflag == false){
                                        $printpermission = false;
                                        $mergepermission = false;
                                    if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 5 ) {
                                        $printpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Print Ticket');
                                        $mergepermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Merge');

                                        ?>
                                        <a class="js-tkt-det-actn-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffaddticket','jssupportticketid'=>jssupportticket::$_data[0]->id))); ?>" title="<?php echo esc_html(__('Edit Ticket', 'js-support-ticket')); ?>">
                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit.png" title="<?php echo esc_html(__('Edit', 'js-support-ticket')); ?>" />
                                            <span><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></span>
                                        </a>
                                        <?php if (jssupportticket::$_data[0]->status != 4) { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(2);" title="<?php echo esc_html(__('Close Ticket', 'js-support-ticket')); ?>">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/close.png" title="<?php echo esc_html(__('Close', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Close', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } else { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(3);" title="<?php echo esc_html(__('Reopen Ticket', 'js-support-ticket')); ?>">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" title="<?php echo esc_html(__('Reopen', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Reopen', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } ?>
                                        <?php if(in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                                            <a class="js-tkt-det-actn-btn" href="#" id="showhistory">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" title="<?php echo esc_html(__('History', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php }?>
                                        <?php if(in_array('mergeticket',jssupportticket::$_active_addons) && $mergepermission) {
                                            if (jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="mergeticket" onclick="return showPopupAndFillValues(<?php echo esc_js(jssupportticket::$_data[0]->id);?>,4)">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/merge-ticket.png" title="<?php echo esc_html(__('Merge', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Merge', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php }/*Merge Ticket*/
                                        } ?>
                                        <?php if(in_array('actions',jssupportticket::$_active_addons)){ ?>
                                            <?php if($printpermission && jssupportticket::$_data[0]->status != 5) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_html(__('Print', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                                </a>
                                                <!-- Print Ticket -->
                                            <?php } ?>
                                        <?php } ?>
                                        <?php $deletepermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
                                        if($deletepermission) { ?>
                                            <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete this ticket', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket')); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/delete.png" title= "<?php echo esc_html(__('Delete', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></span>
                                            </a>
                                            <?php
                                        }
                                        $credentialpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Credentials');
                                        if(in_array('privatecredentials',jssupportticket::$_active_addons) && $credentialpermission){ ?>
                                            <a class="js-tkt-det-actn-btn" href="javascript:return false;" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$_data[0]->id); ?>)">
                                                <?php $query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = ".esc_sql(jssupportticket::$_data[0]->id);
                                                $cred_count = jssupportticket::$_db->get_var($query);
                                                if ($cred_count>0) {
                                                    $img_name = 'private-credentials-exist.png';
                                                } else {
                                                    $img_name = 'private-credentials.png';
                                                } ?>
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/<?php echo esc_attr($img_name);?>" title= "<?php echo esc_html(__('Print', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></span>
                                            </a>
                                            <?php
                                        }
                                    } else { ?>
                                            <?php if (jssupportticket::$_data[0]->status != 5) { ?>
                                                <?php if (jssupportticket::$_data[0]->status != 4) { ?>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure to close this ticket', 'js-support-ticket')); ?>');" class="js-tkt-det-actn-btn" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'closeticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),"close-ticket")); ?>">
                                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/close.png" title="<?php echo esc_html(__('Close', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Close', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php if(in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                                                        <a class="js-tkt-det-actn-btn js-margin-right" href="#" id="showhistory">
                                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" title="<?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?>" />
                                                            <span><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                                                        </a>
                                                    <?php } ?>
                                                <?php } else {
                                                        if (JSSTincluder::getJSModel('ticket')->checkCanReopenTicket(jssupportticket::$_data[0]->id)) {
                                                            $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'reopenticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id,'jsstpageid'=>get_the_ID())),"reopen-ticket"); ?>
                                                            <a class="js-tkt-det-actn-btn" href="<?php echo esc_url($link); ?>" title="<?php echo esc_html(__('Reopen Ticket', 'js-support-ticket')); ?>">
                                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" title="<?php echo esc_html(__('Reopen', 'js-support-ticket')); ?>" />
                                                                <span><?php echo esc_html(__('Reopen', 'js-support-ticket')); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (jssupportticket::$_config['show_ticket_delete_button'] == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete this ticket', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket')); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/delete.png" title= "<?php echo esc_html(__('Delete', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                            <?php
                                            if(jssupportticket::$_config['print_ticket_user'] == 1 ){
                                                if(in_array('actions',jssupportticket::$_active_addons)){ ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_html(__('Print', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php
                                                }
                                            }
                                            if(in_array('privatecredentials',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5){ ?>
                                                <a class="js-tkt-det-actn-btn" href="javascript:return false;" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$_data[0]->id); ?>)">
                                                    <?php $query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = ".esc_sql(jssupportticket::$_data[0]->id);
                                                    $cred_count = jssupportticket::$_db->get_var($query);
                                                    if ($cred_count>0) {
                                                        $img_name = 'private-credentials-exist.png';
                                                    } else {
                                                        $img_name = 'private-credentials.png';
                                                    } ?>
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/<?php echo esc_attr($img_name);?>" title= "<?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></span>
                                                </a>
                                                <?php
                                            }
                                        } ?>
                                        <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 5) { ?>
                                        <?php if (in_array('actions',jssupportticket::$_active_addons)) { ?>
                                            <?php if (jssupportticket::$_data[0]->lock == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(5);" title="<?php echo esc_html(__('Unlock Ticket', 'js-support-ticket')); ?>">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/unlock.png" title="<?php echo esc_html(__('Unlock', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Unlock', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(4);" title="<?php echo esc_html(__('Lock Ticket', 'js-support-ticket')); ?>">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/lock.png" title="<?php echo esc_html(__('Lock', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Lock', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if(in_array('banemail', jssupportticket::$_active_addons)){ ?>
                                            <?php
                                                if (JSSTincluder::getJSModel('banemail')->isEmailBan(jssupportticket::$_data[0]->email)) { ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(7);">
                                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-ban.png" title="<?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?></span>
                                                    </a>
                                                <?php } else { ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(6);">
                                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban.png" title="<?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?></span>
                                                    </a>
                                                <?php } ?>
                                        <?php } ?>
                                        <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                                            <?php if (jssupportticket::$_data[0]->isoverdue == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(11);">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-over-due.png" title="<?php echo esc_html(__('Unmark Overdue', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Unmark Overdue', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(8);">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/over-due.png" title="<?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (in_array('actions',jssupportticket::$_active_addons)) { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(9);">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/in-progress.png'; ?>" title="<?php echo esc_html(__('Mark in Progress', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Mark in Progress', 'js-support-ticket'));?></span>
                                            </a>
                                        <?php } ?>
                                        <?php if(in_array('banemail', jssupportticket::$_active_addons)){ ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(10);">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/ban-email-close-ticket.png'; ?>" title="<?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Ban Email and Close Ticket', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } ?>
                                <?php } ?>
                                <?php } else { ?>
                                    <a class="js-tkt-det-actn-btn" href="javascript:window.print();">
                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_html(__('Print', 'js-support-ticket')); ?>" />
                                        <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                        if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']) {
                            if (in_array('note', jssupportticket::$_active_addons)) {
                                ?>
                                <!-- Ticket Detail Internal Note -->
                                <div class="js-tkt-det-title">
                                    <?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?>
                                </div> <!-- Heading -->
                                <?php
                                foreach (jssupportticket::$_data[6] AS $note) {
                                    ?>
                                    <div class="js-ticket-detail-box js-ticket-post-reply-box"><!-- Ticket Detail Box -->
                                        <div class="js-ticket-detail-left js-ticket-white-background"><!-- Left Side Image -->
                                            <div class="js-ticket-user-img-wrp">
                                                <?php if (in_array('agent',jssupportticket::$_active_addons) && $note->staffphoto) { ?>
                                                    <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $note->staff_id ,'jsstpageid'=>get_the_ID()))); ?>">
                                                <?php } else {
                                                    if (isset(jssupportticket::$_data[0]->uid) && !empty(jssupportticket::$_data[0]->uid)) {
                                                        echo wp_kses(get_avatar($note->uid), JSST_ALLOWED_TAGS);
                                                    } else { ?>
                                                        <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/ticketmanbig.png'; ?>" />
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-detail-right js-ticket-background"><!-- Right Side Ticket Data -->
                                            <div class="js-ticket-rows-wrapper">
                                                <div class="js-ticket-rows-wrp">
                                                    <div class="js-ticket-field-value name">
                                                        <?php echo !empty($note->staffname) ? esc_html($note->staffname) : esc_html($note->display_name); ?>
                                                    </div>
                                                </div>
                                                <?php if (isset($note->title) && $note->title != '') { ?>
                                                    <div class="js-ticket-rows-wrp" >
                                                        <div class="js-ticket-field-value">
                                                            <span class="js-ticket-field-value-t"><?php echo esc_html($field_array['subject']).' : '; ?></span><?php echo esc_html($note->title); ?></div>
                                                    </div>
                                                <?php } ?>
                                                <div class="js-ticket-rows-wrp" >
                                                    <div class="js-ticket-row">
                                                        <div class="js-ticket-field-value">
                                                           <?php echo wp_kses_post($note->note); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                                if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                <div class="js-ticket-edit-options-wrp" >
                                                    <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$_data[0]->status != 5){ ?>
                                                        <a class="js-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($note->id);?>,3)" >
                                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                            <?php echo esc_html(__('Edit Time','js-support-ticket'));?>
                                                        </a>
                                                    <?php
                                                    }
                                                    $hours = floor($note->usertime / 3600);
                                                    $mins = floor($note->usertime / 60);
                                                    $mins = floor($mins % 60);
                                                    $secs = floor($note->usertime % 60);
                                                    $time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                                    ?>
                                                    <span class="js-ticket-thread-time"><?php echo esc_html($time); ?></span>
                                                </div>
                                                <?php } ?>

                                                <?php
                                                if($note->filesize > 0 && !empty($note->filename)){ ?>
                                                    <div class="js-ticket-attachments-wrp">
                                                        <div class="js_ticketattachment">
                                                            <span class="js-ticket-download-file-title">
                                                                <?php echo esc_html($note->filename); echo '(' . esc_html($note->filesize / 1024) . ')'; ?>
                                                            </span>
                                                            <a class="js-download-button" target="_blank" href="<?php echo esc_url(admin_url('?page=note&action=jstask&task=downloadbyid&id='.esc_attr($note->id))); ?>">
                                                                <img alt="image" class="js-ticket-download-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/ticket-detail/download.png">
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="js-ticket-time-stamp-wrp">
                                                <span class="js-ticket-ticket-created-date">
                                                    <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($note->created))); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="js-ticket-thread-add-btn">
                                    <a href="#" id="internalnotebtn" class="js-ticket-thread-add-btn-link">
                                        <img alt="<?php echo esc_html(__('Post New Internal Note','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png" />
                                        <?php echo esc_html(__("Internal Note",'js-support-ticket')); ?>
                                    </a>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Ticket Detail Thread -->
                        <div class="js-tkt-det-title thread">
                            <?php echo esc_html(__('Ticket Thread', 'js-support-ticket')); ?>
                        </div> <!-- Heading -->
                        <div class="js-ticket-thread internal-note"><!-- Ticket Detail Box -->
                            <div class="js-ticket-thread-image"><!-- Left Side Image -->
                                <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->staffphotophoto) { ?>
                                    <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                <?php } else {
                                    echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById(jssupportticket::$_data[0]->uid), 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                } ?>
                            </div>
                            <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
                                <div class="js-ticket-thread-data">
                                    <span class="js-ticket-thread-person">
                                        <?php echo esc_html(jssupportticket::$_data[0]->name); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-thread-data">
                                    <span class="js-ticket-thread-email">
                                        <?php echo esc_html(jssupportticket::$_data[0]->email); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-thread-data note-msg">
                                    <?php echo wp_kses_post(jssupportticket::$_data[0]->message); ?>
                                    <?php
                                     if (!empty(jssupportticket::$_data['ticket_attachment'])) { ?>
                                         <div class="js-ticket-attachments-wrp">
                                             <?php foreach (jssupportticket::$_data['ticket_attachment'] AS $attachment) {
                                                    $path = jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'downloadbyid','action'=>'jstask','id'=> $attachment->id ,'jsstpageid'=>get_the_ID()));
                                                    $data = wp_check_filetype($attachment->filename);
                                                    $type = $data['type'];

                                                    echo '
                                                    <div class="js_ticketattachment">
                                                        <span class="js_ticketattachment_fname">
                                                            ' . esc_html($attachment->filename) . '
                                                        </span>
                                                        <a class="js-download-button" target="_blank" href="' . esc_url($path) . '">'
                                                            . esc_html(__('Download', 'js-support-ticket')).'
                                                        </a>';
                                                        if(strpos($type, "image") !== false) {
                                                            echo '<a data-gall="gallery-ticket-thread" class="js-download-button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_url(JSSTincluder::getJSModel('attachment')->getAttachmentImage($attachment->id)) .'"  target="_blank">
                                                            <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                </a>';
                                                        }
                                                    echo '</div>';

                                                }
                                                echo '<a class="js-all-download-button" target="_blank" href="' . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'downloadall', 'action'=>'jstask', 'downloadid'=>jssupportticket::$_data[0]->id , 'jsstpageid'=>get_the_ID()))) . '" >'. esc_html(__('Download All', 'js-support-ticket')) . '</a>';?>
                                         </div>
                                     <?php } ?>
                                </div>
                                <div class="js-ticket-thread-cnt-btm">
                                    <span class="js-ticket-thread-date">
                                         <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime(jssupportticket::$_data[0]->created))); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- User post Reply Section -->
                        <?php if (!empty(jssupportticket::$_data[4]))
                            foreach (jssupportticket::$_data[4] AS $reply):
                                if ($cur_uid == $reply->uid) ?>
                                    <div class="js-ticket-thread"><!-- Ticket Detail Box -->
                                        <div class="js-ticket-thread-image"><!-- Left Side Image -->
                                            <?php if (in_array('agent',jssupportticket::$_active_addons) &&  $reply->staffphoto) { ?>
                                                <img  class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $reply->staffid ,'jsstpageid'=>get_the_ID()))); ?>">
                                            <?php } else {
                                                echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById($reply->uid), 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                            } ?>
                                        </div>
                                        <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
											<div class="js-ticket-thread-data">
                                                    <span class="js-ticket-thread-person">
                                                        <?php
                                                        if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
                                                            if(jssupportticket::$_data[0]->uid  != $reply->uid){ //reply by staff, need anonymous
                                                                echo esc_html(jssupportticket::$_config['title']);
                                                            }else{ // reply by user   
																if($reply->name == ""){
																	// name field value is empty in some old tickets
																	$replyname = JSSTincluder::getJSModel('reply')->getUserNameFromReplyById($reply->replyid);
																	echo esc_html($replyname); 
																}else{
																	echo esc_html($reply->name); 
																}
                                                            }
                                                        }elseif(jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 2){
																if($reply->name == ""){
																	// name field value is empty in some old tickets
																	$replyname = JSSTincluder::getJSModel('reply')->getUserNameFromReplyById($reply->replyid);
																	echo esc_html($replyname); 
																}else{
																	echo esc_html($reply->name); 
																}
                                                        }
                                                        ?>
                                                    </span>
                                            <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                    <?php if($reply->staffid != 0){
                                                        $hours = floor($reply->time / 3600);
                                                        $mins = floor($reply->time / 60);
                                                        $mins = floor($mins % 60);
                                                        $secs = floor($reply->time % 60);
                                                        $time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                                        ?>
                                                        <span class="js-ticket-thread-time"><?php echo esc_html($time); ?></span>
                                                    <?php } ?>
                                            <?php } ?>
											</div>
                                            <?php
											if (jssupportticket::$_config['show_email_on_ticket_reply'] == 1) {
                                                if(isset($reply->staffemail)){ ?>
													<div class="js-ticket-thread-data">
														<span class="js-ticket-thread-email"><?php echo esc_html($reply->staffemail); ?></span>
													</div>
                                                    <?php
                                                } elseif(isset($reply->useremail)){ ?>
                                                    <div class="js-ticket-thread-data">
                                                        <span class="js-ticket-thread-email"><?php echo esc_html($reply->useremail); ?></span>
                                                    </div>
                                                    <?php
                                                }
										    }	
											?>
                                            <div class="js-ticket-thread-data">
                                                <?php echo ($reply->ticketviaemail == 1) ? esc_html(__('Created via Email', 'js-support-ticket')) : ''; ?>
                                            </div>
                                            <div class="js-ticket-thread-data note-msg">
                                                <?php echo wp_kses_post(html_entity_decode($reply->message)); ?>
                                                <?php if (!empty($reply->attachments)) { ?>
                                                    <div class="js-ticket-attachments-wrp">
                                                        <?php foreach ($reply->attachments AS $attachment) {
                                                                $path = jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'downloadbyid','action'=>'jstask','id'=> $attachment->id ,'jsstpageid'=>get_the_ID()));
                                                                $data = wp_check_filetype($attachment->filename);
                                                                $type = $data['type'];
                                                                echo wp_kses('
                                                                    <div class="js_ticketattachment">
                                                                        <span class="js-ticket-download-file-title">
                                                                            ' . esc_html($attachment->filename) . ' ( ' . esc_html(round($attachment->filesize,2)) . ' kb) ' . '
                                                                        </span>
                                                                        <a class="js-download-button" target="_blank" href="' . esc_url($path) . '">'
                                                                            . esc_html(__('Download', 'js-support-ticket')) .'
                                                                        </a>',JSST_ALLOWED_TAGS);
                                                                        if(strpos($type, "image") !== false) {
                                                                            $path = JSSTincluder::getJSModel('attachment')->getAttachmentImage($attachment->id);
                                                                            echo '<a data-gall="gallery-'.esc_attr($reply->replyid).'" class="js-download-button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_url($path) .'"  target="_blank">
                                                                            <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                                </a>';
                                                                        }
                                                                echo '</div>';
                                                                }
                                                            $nonce = esc_attr(wp_create_nonce("download-all-for-reply"));
                                                            echo wp_kses('
                                                                <a class="js-all-download-button" target="_blank" href="' . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'downloadallforreply', 'action'=>'jstask', 'downloadid'=>$reply->replyid, '_wpnonce'=>$nonce , 'jsstpageid'=>get_the_ID()))) . '" onclick="" target="_blank">'. esc_html(__('Download All', 'js-support-ticket')) . '</a>', JSST_ALLOWED_TAGS);?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                                <?php if (in_array('agent',jssupportticket::$_active_addons) &&  jssupportticket::$_data['user_staff']) {
                                                    ?>
                                                        <div class="js-ticket-thread-cnt-btm">
                                                            <div class="js-ticket-thread-date">
                                                                <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($reply->created))); ?>
                                                            </div>
                                                            <div class="js-ticket-thread-actions">
                                                                <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Reply') && jssupportticket::$_data[0]->status != 5){ ?>
                                                                        <a class="js-ticket-thread-actn-btn ticket-edit-reply-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($reply->replyid);?>,1)" >
                                                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                                            <?php echo esc_html(__('Edit Reply','js-support-ticket'));?>
                                                                        </a>
                                                                <?php }
                                                                if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                                    if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$_data[0]->status != 5){ ?>
                                                                        <a class="js-ticket-thread-actn-btn ticket-edit-time-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($reply->replyid);?>,2)" >
                                                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                                            <?php echo esc_html(__('Edit Time','js-support-ticket'));?>
                                                                        </a>
                                                                <?php
                                                                	}?>
                                                    		<?php
                                                    			}
                                                			?>
                                                            </div>
                                                        </div>
                                                <?php } ?>
											<div class="js-ticket-thread-cnt-btm">
												<span class="js-ticket-thread-date">
													 <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($reply->created))); ?>
												</span>
											</div>

                                        </div>
                                    </div>
                        <?php endforeach; ?>
                        <!-- User post Reply Form Section -->
                        <div class="js-ticket-reply-forms-wrapper"><!-- Ticket Reply Forms Wrapper -->
                            <?php if($printflag == false){
                                if (!jssupportticket::$_data['user_staff']) {
                                    if (jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->lock != 1 && jssupportticket::$_data[0]->status != 5): ?>
                                        <div class="js-ticket-reply-forms-heading"><?php echo esc_html(__('Reply a message', 'js-support-ticket')); ?></div>
                                        <div id="postreply" class="js-ticket-post-reply">
                                            <div class="js-ticket-reply-field-wrp">
                                                <div class="js-ticket-reply-field"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                            </div>
                                            <div class="js-ticket-reply-attachments"><!-- Attachments -->
                                                <div class="js-attachment-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['attachments'])); ?></div>
                                                <div class="js-attachment-field">
                                                    <div class="tk_attachment_value_wrapperform tk_attachment_user_reply_wrapper">
                                                        <span class="tk_attachment_value_text">
                                                            <input type="file" class="inputbox js-attachment-inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" />
                                                            <span class='tk_attachment_remove'></span>
                                                        </span>
                                                    </div>
                                                    <span class="tk_attachments_configform">
                                                        <?php echo esc_html(__('Maximum File Size', 'js-support-ticket'));
                                                              echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php echo esc_html(__('File Extension Type', 'js-support-ticket'));
                                                              echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?>
                                                    </span>
                                                    <span id="tk_attachment_add" data-ident="tk_attachment_user_reply_wrapper" class="tk_attachments_addform"><?php echo esc_html(__('Add more','js-support-ticket')); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="js-ticket-closeonreply-wrp">
                                            <div class="js-ticket-closeonreply-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></div>
                                            <div class="replyFormStatus js-form-title-position-reletive-left">
                                                <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close on reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-reply-form-button-wrp">
                                            <?php echo wp_kses(JSSTformfield::submitbutton('postreplybutton', esc_html(__('Post Reply', 'js-support-ticket')), array('class' => 'button js-ticket-save-button', 'onclick' => "return checktinymcebyid('message');")), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('actionid', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('priority', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$_data[0]->created), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketrandomid', jssupportticket::$_data[0]->ticketid), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('hash', jssupportticket::$_data[0]->hash), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('updated', jssupportticket::$_data[0]->updated), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                </div>
                                </form>

                                <?php
                                }else {
                                    ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('actionid', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('priority', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$_data[0]->created), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('updated', jssupportticket::$_data[0]->updated), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                </div>
                                </form>
                                <?php if (jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5) { ?>
                                    <div id="postreply" class="js-det-tkt-rply-frm"><!-- Post Reply Area -->
                                        <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply")); ?>" enctype="multipart/form-data">
                                            <div class="js-tkt-det-title">
                                                <?php echo esc_html(__('Post Reply','js-support-ticket')); ?>
                                            </div>
                                            <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                <div class="jsst-ticket-detail-timer-wrapper"> <!-- Timer Wrapper -->
                                                    <div class="timer-left" >
                                                        <div class="timer-total-time" >
                                                            <?php
                                                                $hours = floor(jssupportticket::$_data['time_taken'] / 3600);
                                                                $mins = floor(jssupportticket::$_data['time_taken'] / 60);
                                                                $mins = floor($mins % 60);
                                                                $secs = floor(jssupportticket::$_data['time_taken'] % 60);
                                                                echo esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="timer-right" >
                                                        <div class="timer" >
                                                            00:00:00
                                                        </div>
                                                        <div class="timer-buttons" >
                                                            <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Own Time')){ ?>
                                                                <span class="timer-button" onclick="showEditTimerPopup()" >
                                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/timer-edit.png"/>
                                                                </span>
                                                            <?php } ?>
                                                            <span class="timer-button cls_1" onclick="changeTimerStatus(1)" >
                                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play.png"/>
                                                            </span>
                                                            <span class="timer-button cls_2" onclick="changeTimerStatus(2)" >
                                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/pause.png"/>
                                                            </span>
                                                            <span class="timer-button cls_3" onclick="changeTimerStatus(3)" >
                                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/stop.png"/>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>

                                                    <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            <?php } ?>
                                            <?php if(in_array('cannedresponses', jssupportticket::$_active_addons)){ ?>
                                                <div class="js-ticket-premade-msg-wrp"><!-- Premade Message Wrapper -->
                                                    <div class="js-ticket-premade-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['premade'])); ?>&nbsp;<?php echo esc_html(__('Message','js-support-ticket')); ?></div>
                                                    <div class="js-ticket-premade-field-wrp">
                                                        <?php echo wp_kses(JSSTformfield::select('premadeid', JSSTincluder::getJSModel('cannedresponses')->getPreMadeMessageForCombobox(), isset(jssupportticket::$_data[0]->premadeid) ? jssupportticket::$_data[0]->premadeid : '', esc_html(__('Select Canned Response', 'js-support-ticket')), array('class' => 'js-ticket-premade-select', 'onchange' => 'getpremade(this.value);')), JSST_ALLOWED_TAGS); ?>
                                                        <span class="js-ticket-apend-radio-btn">
                                                            <?php echo wp_kses(JSSTformfield::checkbox('append_premade', array('1' => esc_html(__('Append', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-premade-radiobtn')), JSST_ALLOWED_TAGS); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="js-ticket-text-editor-wrp">
                                                <div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Type Message','js-support-ticket')); ?></div>
                                                <div class="js-ticket-text-editor-field"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                            </div>
                                            <div class="js-ticket-reply-attachments"><!-- Attachments -->
                                                <div class="js-attachment-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['attachments'])); ?></div>
                                                <div class="js-attachment-field">
                                                    <div class="tk_attachment_value_wrapperform tk_attachment_staff_reply_wrapper">
                                                        <span class="tk_attachment_value_text">
                                                            <input type="file" class="inputbox js-attachment-inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" />
                                                            <span class='tk_attachment_remove'></span>
                                                        </span>
                                                    </div>
                                                    <span class="tk_attachments_configform">
                                                        <?php echo esc_html(__('Maximum File Size', 'js-support-ticket'));
                                                              echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php echo esc_html(__('File Extension Type', 'js-support-ticket'));
                                                              echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?>
                                                    </span>
                                                    <span id="tk_attachment_add" data-ident="tk_attachment_staff_reply_wrapper" class="tk_attachments_addform"><?php echo esc_html(__('Add more','js-support-ticket')); ?></span>
                                                </div>
                                            </div>
                                            <div class="js-ticket-append-signature-wrp"><!-- Append Signature -->
                                                <div class="js-ticket-append-field-title"><?php echo esc_html(__('Append Signature','js-support-ticket')); ?></div>
                                                <div class="js-ticket-append-field-wrp">
                                                    <div class="js-ticket-signature-radio-box">
                                                        <?php echo wp_kses(JSSTformfield::checkbox('ownsignature', array('1' => esc_html(__('Own Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <div class="js-ticket-signature-radio-box">
                                                        <?php echo wp_kses(JSSTformfield::checkbox('departmentsignature', array('1' => esc_html(__('Department Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <div class="js-ticket-signature-radio-box">
                                                        <?php echo wp_kses(JSSTformfield::checkbox('nonesignature', array('1' => esc_html(__('None', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            if(in_array('agent',jssupportticket::$_active_addons)){
                                                $staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
                                                if (jssupportticket::$_data[0]->staffid != $staffid) {
                                                    ?>
                                                    <div class="js-ticket-assigntome-wrp">
                                                        <div class="js-ticket-assigntome-field-title"><?php echo esc_html(__('Assign Ticket','js-support-ticket')); ?></div>
                                                        <div class="js-ticket-assigntome-field-wrp">
                                                            <?php
                                                                if(jssupportticket::$_data[0]->staffid){
                                                                    $checked = '';
                                                                }else{
                                                                    $checked = 1;
                                                                }
                                                                echo wp_kses(JSSTformfield::checkbox('assigntome', array('1' => esc_html(__('Assign to me', 'js-support-ticket'))), $checked, array('class' => 'radiobutton js-ticket-assigntome-checkbox')), JSST_ALLOWED_TAGS);
                                                            ?>
                                                        </div>
                                                    </div><!-- Assign to me -->
                                            <?php }
                                        } ?>
                                            <div class="js-ticket-closeonreply-wrp">
                                                <div class="js-ticket-closeonreply-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></div>
                                                <div class="replyFormStatus js-form-title-position-reletive-left">
                                                    <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close on reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                            <div class="js-ticket-reply-form-button-wrp">
                                                <?php echo wp_kses(JSSTformfield::submitbutton('postreply', esc_html(__('Post Reply', 'js-support-ticket')), array('class' => 'button js-ticket-save-button', 'onclick' => "return checktinymcebyid('message');")), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                            <?php echo wp_kses(JSSTformfield::hidden('departmentid', jssupportticket::$_data[0]->departmentid), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('ticketrandomid',jssupportticket::$_data[0]->ticketid), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('hash', jssupportticket::$_data[0]->hash), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('action', 'reply_savereply'), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                        </form>
                                    </div>
                                <?php } ?>
                            <?php
                            }
                        }
                        ?>
                    </div>
                    <?php if($printflag == true){?>
                        </div> <!-- extra div for print -->
                    <?php } ?>
                    <!-- Ticket Detail Right -->
                    <div class="js-tkt-det-right">
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-info"> <!-- Ticket Info -->
                            <?php
                                if (jssupportticket::$_data[0]->status == 0) {
                                    $color = "#5bb12f;";
                                    $ticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                } elseif (jssupportticket::$_data[0]->status == 1) {
                                    $color = "#28abe3;";
                                    $ticketmessage = esc_html(__('On Waiting', 'js-support-ticket'));
                                } elseif (jssupportticket::$_data[0]->status == 2) {
                                    $color = "#69d2e7;";
                                    $ticketmessage = esc_html(__('In Progress', 'js-support-ticket'));
                                } elseif (jssupportticket::$_data[0]->status == 3) {
                                    $color = "#FFB613;";
                                    $ticketmessage = esc_html(__('Replied', 'js-support-ticket'));
                                } elseif (jssupportticket::$_data[0]->status == 4) {
                                    $color = "#ed1c24;";
                                    $ticketmessage = esc_html(__('Closed', 'js-support-ticket'));
                                } elseif (jssupportticket::$_data[0]->status == 5) {
                                    $color = "#dc2742;";
                                    $ticketmessage = esc_html(__('Close and merge', 'js-support-ticket'));
                                }
                            ?>
                            <div class="js-tkt-det-status" style="background-color:<?php echo esc_attr($color);?>;">
                                <?php echo esc_attr($ticketmessage); ?>
                            </div>
                            <div class="js-tkt-det-info-cnt">
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Created','js-support-ticket')) . ': '; ?>
                                    </div>
                                    <div class="js-tkt-det-info-val" title="<?php echo esc_html(date_i18n("d F, Y, H:i:s A", jssupportticketphplib::JSST_strtotime(jssupportticket::$_data[0]->created))); ?>">
                                       <?php echo esc_html(human_time_diff(strtotime(jssupportticket::$_data[0]->created),strtotime(date_i18n("Y-m-d H:i:s")))).' '. esc_html(__('ago', 'js-support-ticket')); ?>
                                    </div>
                                </div>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Last Reply','js-support-ticket')); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php if (empty(jssupportticket::$_data[0]->lastreply) || jssupportticket::$_data[0]->lastreply == '0000-00-00 00:00:00') echo esc_html(__('No Last Reply', 'js-support-ticket'));
                                            else echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$_data[0]->lastreply))); ?>
                                    </div>
                                </div>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->departmentname)); ?>
                                    </div>
                                </div>
                                <?php
                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                    $configname = 'agent';
                                } else {
                                    $configname = 'user';
                                }
                                if (jssupportticket::$_config['show_closedby_on_' . $configname . '_tickets'] == 1 && jssupportticket::$_data[0]->status == 4) { ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed By','js-support-ticket')). ' : '; ?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy(jssupportticket::$_data[0]->closedby)); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed On','js-support-ticket')). ' : '; ?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$_data[0]->closed))); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php echo esc_html(jssupportticket::$_data[0]->ticketid); ?>
                                       <a title="<?php echo esc_html(__('Copy','js-support-ticket')); ?>" class="js-tkt-det-copy-id" id="ticketidcopybtn" success="<?php echo esc_html(__('Copied','js-support-ticket')); ?>"><?php echo esc_html(__('Copy','js-support-ticket')); ?></a>
                                    </div>
                                </div>
                                <?php  if(in_array('helptopic', jssupportticket::$_active_addons)){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['helptopic'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->helptopic)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['status'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php
                                            if (jssupportticket::$_data[0]->status == 4 || jssupportticket::$_data[0]->status == 5 )
                                                $ticketmessage = esc_html(__('Closed', 'js-support-ticket'));
                                            elseif (jssupportticket::$_data[0]->status == 2)
                                                $ticketmessage = esc_html(__('In Progress', 'js-support-ticket'));
                                            else
                                                $ticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                            $printstatus = 1;
                                            if (jssupportticket::$_data[0]->lock == 1) {
                                                echo '<div class="js-ticket-status-note">' . esc_html(__('Lock', 'js-support-ticket')).' '. esc_html(__(',', 'js-support-ticket')) . '</div>';
                                                $printstatus = 0;
                                            }
                                            if (jssupportticket::$_data[0]->isoverdue == 1) {
                                                echo '<div class="js-ticket-status-note">' . esc_html(__('Overdue', 'js-support-ticket')) . '</div>';
                                                $printstatus = 0;
                                            }
                                            if ($printstatus == 1) {
                                                echo esc_html($ticketmessage);
                                            }
                                        ?>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Priority -->
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(__('Priority','js-support-ticket')); ?>
                                </div>
                                <?php
                                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 5) {
                                    if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority')){
									?>
										<a class="js-tkt-det-hdg-btn" href="#" id="changepriority">
											<?php echo esc_html(__('Change','js-support-ticket')); ?>
										</a>
										<div id="userpopupforchangepriority" style="display:none;">
											<div class="js-ticket-priorty-header">
												<?php echo esc_html(__('Change Priority', 'js-support-ticket')); ?>
												<span class="close-history"></span>
											</div>
											<div class="js-ticket-priorty-fields-wrp">
												<div class="js-ticket-select-priorty">
													<?php echo wp_kses(JSSTformfield::select('prioritytemp', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$_data[0]->priorityid, esc_html(__('Change Priority', 'js-support-ticket')), array()), JSST_ALLOWED_TAGS); ?>
												</div>
											</div>
											<div class="js-ticket-priorty-btn-wrp">
												<?php echo wp_kses(JSSTformfield::button('changepriority', esc_html(__('Change Priority', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save', 'onclick' => 'actionticket(1);')), JSST_ALLOWED_TAGS); ?>
												<?php echo wp_kses(JSSTformfield::button('cancelee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
											</div>
										</div>
                                    <?php
									}
                                }
                                ?>
                            </div>
                            <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$_data[0]->prioritycolour);?>; color:#ffffff;">
                                <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->priority)); ?>
                            </div>
                        </div>

                        <?php
                        $agentflag = in_array('agent', jssupportticket::$_active_addons) && $printflag == false && jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5;
                        $departmentflag = in_array('actions', jssupportticket::$_active_addons) && $printflag == false && jssupportticket::$_data[0]->status != 4 && jssupportticket::$_data[0]->status != 5;
                        if($agentflag || $departmentflag){
                            ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-assign"> <!-- Ticket Assign -->
                                <?php if($agentflag){ ?>
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <?php echo esc_html(__('Assigned To Agent','js-support-ticket')); ?>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="js-tkt-det-tkt-asgn-cnt">
                                    <?php if($agentflag){ ?>
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php
                                            if(jssupportticket::$_data[0]->staffid > 0){
                                                echo esc_html(__('Ticket assigned to','js-support-ticket'));
                                            }else{
                                                echo esc_html(__('Not assigned to agent','js-support-ticket'));
                                            }
                                            ?>
                                        </div>
                                        <?php if(jssupportticket::$_data['user_staff']){ 
												if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Assign Ticket To Agent')){
												?>
													<a class="js-tkt-det-hdg-btn" href="#" id="agenttransfer">
														<?php echo esc_html(__('Change','js-support-ticket')); ?>
													</a>
												<?php } ?>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                    <div class="js-tkt-det-info-wrp">
                                        <?php if($agentflag && jssupportticket::$_data[0]->staffid > 0){ ?>
                                        <div class="js-tkt-det-user">
                                            <div class="js-tkt-det-user-image">
                                                <?php
                                                if(jssupportticket::$_data[0]->staffphoto && jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 2){
                                                    ?>
                                                    <img alt="<?php echo esc_html(__('staff photo','js-support-ticket')); ?>" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=>jssupportticket::$_data[0]->staffid, 'jsstpageid'=>jssupportticket::getPageid()))); ?>">
                                                    <?php
                                                } else { ?>
                                                    <img alt="<?php echo esc_html(__('staff photo','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/user.png'; ?>" />
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="js-tkt-det-user-cnt">
                                                <div class="js-tkt-det-user-data"><?php 
													if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
														echo esc_html(jssupportticket::$_config['title']);
													}else{
														echo esc_html(jssupportticket::$_data[0]->staffname); 
													}
												?></div>
                                                <div class="js-tkt-det-user-data agent-email"><?php 
												if (jssupportticket::$_config['show_email_on_ticket_reply'] == 1) {
													echo esc_html(jssupportticket::$_data[0]->staffemail); 
												}
												?></div>
                                                <div class="js-tkt-det-user-data"><?php 
													if (jssupportticket::$_config['show_email_on_ticket_reply'] == 2) {
														echo esc_html(jssupportticket::$_data[0]->staffphone);
													}
												?></div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if($departmentflag){ ?>
											<div class="js-tkt-det-trsfer-dep">
												<div class="js-tkt-det-trsfer-dep-txt">
													<span class="js-tkt-det-trsfer-dep-txt-tit"><?php echo esc_html(__('Department','js-support-ticket')).' : '; ?> </span>
													<?php echo esc_html(jssupportticket::$_data[0]->departmentname); ?>
												</div>
												<?php if(jssupportticket::$_data['user_staff']){ 
														if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer')){
													?>
															<a title="<?php echo esc_html(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="departmenttransfer">
																<?php echo esc_html(__('Change','js-support-ticket')); ?>
															</a>
													<?php } ?>
												<?php } ?>
											</div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <?php if(in_array('timetracking', jssupportticket::$_active_addons) && isset(jssupportticket::$_data['time_taken'])){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-time-tracker"> <!-- Time Tracker -->
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(__('Total Time Taken','js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="js-tkt-det-timer-wrp"> <!-- Timer Wrapper -->
                                <div class="timer-total-time" >
                                    <?php
                                    $hours = floor(jssupportticket::$_data['time_taken'] / 3600);
                                    $mins = floor(jssupportticket::$_data['time_taken'] / 60);
                                    $mins = floor($mins % 60);
                                    $secs = floor(jssupportticket::$_data['time_taken'] % 60);
                                    $time =  sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                    ?>
                                    <div class="timer-total-time-value">
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $hours)); ?>
                                        </span>
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $mins)); ?>
                                        </span>
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $secs)); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <?php } ?>

                         <!-- User Tickets -->
                        <?php if(isset(jssupportticket::$_data['usertickets']) && !empty(jssupportticket::$_data['usertickets'])){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-user-tkts">
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(jssupportticket::$_data[0]->name).' '. esc_html(__('Tickets','js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="js-tkt-det-usr-tkt-list">
                                <?php
                                foreach(jssupportticket::$_data['usertickets'] as $userticket){
                                    ?>
                                    <div class="js-tkt-det-user">
                                        <div class="js-tkt-det-user-image">
                                            <?php echo wp_kses(jsst_get_avatar(JSSTincluder::getJSModel('jssupportticket')->getWPUidById(jssupportticket::$_data[0]->uid)), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-tkt-det-user-cnt">
                                            <div class="js-tkt-det-user-data name">
                                                <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->subject)); ?></span>
                                            </div>
                                            <div class="js-tkt-det-user-data">
                                                <span class="js-tkt-det-user-tit"><?php echo esc_html(__("Department",'js-support-ticket')). ' : '; ?></span>
                                                <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->departmentname)); ?></span>
                                            </div>
                                            <div class="js-tkt-det-user-data">
                                                <span class="js-tkt-det-prty" style="background:<?php echo esc_html($userticket->prioritycolour);?>;">
                                                   <?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->priority)); ?>
                                                </span>
                                                <span class="js-tkt-det-status">
                                                    <?php
                                                        if ($userticket->status == 4 || $userticket->status == 5 )
                                                            $userticketmessage = esc_html(__('Closed', 'js-support-ticket'));
                                                        elseif ($userticket->status == 2)
                                                            $userticketmessage = esc_html(__('In Progress', 'js-support-ticket'));
                                                        else
                                                            $userticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                                        $userticketprintstatus = 1;
                                                        if ($userticket->lock == 1) {
                                                            echo wp_kses('<span class="js-ticket-status-note">' . esc_html(__('Lock', 'js-support-ticket')).' '. esc_html(__(',', 'js-support-ticket')) . '</span>', JSST_ALLOWED_TAGS);
                                                            $userticketprintstatus = 0;
                                                        }
                                                        if ($userticket->isoverdue == 1) {
                                                            echo wp_kses('<span class="js-ticket-status-note">' . esc_html(__('Overdue', 'js-support-ticket')) . '</span>', JSST_ALLOWED_TAGS);
                                                            $userticketprintstatus = 0;
                                                        }
                                                        if ($userticketprintstatus == 1) {
                                                            echo esc_html($userticketmessage);
                                                        }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Woocomerece -->
						<?php apply_filters( 'js_support_ticket_admin_details_right_middle', jssupportticket::$_data[0]->id ); ?>
                        <?php
                        if( class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){
                            $order = wc_get_order(jssupportticket::$_data[0]->wcorderid);
                            $order_itemid = jssupportticket::$_data[0]->wcproductid;
                            if($order){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-woocom">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Woocommerce Order",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['wcorderid']); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($order->get_id()); ?></div>
                                        </div>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Status",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo wp_kses(wc_get_order_status_name($order->get_status()), JSST_ALLOWED_TAGS); ?></div>
                                        </div>
                                        <?php
                                        if($order_itemid){
                                            $item = new WC_Order_Item_Product($order_itemid);
                                            if($item){
                                                ?>
                                                <div class="js-tkt-wc-order-item">
                                                    <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['wcproductid']); ?>:</div>
                                                    <div class="js-tkt-wc-order-item-value"><?php echo esc_html($item->get_name()); ?></div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Created",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($order->get_date_created()->date_i18n(wc_date_format())); ?></div>
                                        </div>
                                        <?php
                                        if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                                            do_action('jsst_woocommerce_order_detail_agent', $order, $order_itemid);
                                        }
                                        ?>
                                        <?php
                                        if(jssupportticket::$_data[0]->uid == JSSTincluder::getObjectClass('user')->uid()){
                                            ?>
                                            <a href="<?php echo esc_url(wc_get_endpoint_url('orders','',wc_get_page_permalink('myaccount'))); ?>" class="js-tkt-wc-order-item-link">
                                                <?php echo esc_html(__("View all orders",'js-support-ticket')); ?>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Easy Digital Downloads -->
                        <?php
                        if( class_exists('Easy_Digital_Downloads') && in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                            $orderid = jssupportticket::$_data[0]->eddorderid;
                            $order_product = jssupportticket::$_data[0]->eddproductid;
                            $order_license = jssupportticket::$_data[0]->eddlicensekey;
                            if($orderid != ''){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-edd">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Easy Digital Downloads",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['eddorderid']); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($orderid); ?></div>
                                        </div>

                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['eddproductid']); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php
                                                if(is_numeric($order_product)){
                                                    $download = new EDD_Download($order_product);
                                                    echo wp_kses($download->post_title, JSST_ALLOWED_TAGS);
                                                }else{
                                                    echo '-----------';
                                                }?>
                                            </div>
                                        </div>
                                        <?php if(class_exists('EDD_Software_Licensing')){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['eddlicensekey']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php
                                                    if($order_license != ''){
                                                        $license = EDD_Software_Licensing::instance();
                                                        $licenseid = $license->get_license_by_key($order_license);
                                                        $result = $license->get_license_status($licenseid);
                                                        if($result == 'expired'){
                                                            $result_color = 'red';
                                                        }elseif($result == 'inactive'){
                                                            $result_color = 'orange';
                                                        }else{
                                                            $result_color = 'green';
                                                        }
                                                        echo wp_kses($order_license.'&nbsp;&nbsp;(<span style="color:'.esc_attr($result_color).';font-weight:bold;text-transform:uppercase;padding:0 3px;">'.wp_kses($result, JSST_ALLOWED_TAGS).'</span>)', JSST_ALLOWED_TAGS);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Envato Validation -->
                        <?php
                        if(in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data[0]->envatodata)){
                            $envlicense = jssupportticket::$_data[0]->envatodata;
                            if(!empty($envlicense)){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-env">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Envato License",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <?php if(!empty($envlicense['itemname']) && !empty($envlicense['itemid'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Item",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($envlicense['itemname']).' (#'.esc_html($envlicense['itemid']).')'; ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($envlicense['buyer'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Buyer",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($envlicense['buyer']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($envlicense['licensetype'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License Type",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($envlicense['licensetype']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($envlicense['license'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($envlicense['license']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($envlicense['purchasedate'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Purchase Date",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($envlicense['purchasedate']))); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($envlicense['supporteduntil'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Supported Until",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y", jssupportticketphplib::JSST_strtotime($envlicense['supporteduntil']))); ?></div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Paid Support -->
                        <?php
                        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce')){
                            $linktickettoorder = true;
                            if(jssupportticket::$_data[0]->paidsupportitemid > 0){
                                $paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportDetails(jssupportticket::$_data[0]->paidsupportitemid);
                                if($paidsupport){
                                    $linktickettoorder = false;
                                    $nonpreminumsupport = in_array(jssupportticket::$_data[0]->id,$paidsupport['ignoreticketids']) ? 1 : 0;
                                    $agentallowed = in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark Non Premium');
                                    ?>
                                    <div class="js-tkt-det-cnt js-tkt-det-pdsprt">
                                        <?php if(!$nonpreminumsupport || $agentallowed){ ?>
                                        <div class="js-tkt-det-hdg">
                                            <div class="js-tkt-det-hdg-txt">
                                                <?php echo esc_html(__("Paid Support Details",'js-support-ticket')); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!$nonpreminumsupport){ ?>
                                        <div class="js-tkt-wc-order-box">
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Order",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($paidsupport['orderid']); ?></div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Product Name",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php echo esc_html($paidsupport['itemname']); ?></div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Total Tickets",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php 
                                                        if($paidsupport['totalticket']==-1){
                                                            echo esc_html(__("Unlimited",'js-support-ticket'));
                                                        } else {
                                                            echo esc_html($paidsupport['totalticket']);
                                                        }
                                                    ?>
                                                    </div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Remaining Tickets",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                        if($paidsupport['totalticket']==-1){
                                                            echo esc_html(__("Unlimited",'js-support-ticket'));
                                                        } else {
                                                            echo esc_html($paidsupport['remainingticket']);
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php if(isset($paidsupport['subscriptionid'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Subscription",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($paidsupport['subscriptionid']); ?></div>
                                            </div>
                                            <?php } ?>
                                            <?php if(isset($paidsupport['subscriptionstartdate'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Subscribed On",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($paidsupport['subscriptionstartdate']))); ?></div>
                                            </div>
                                            <?php } ?>
                                            <?php if(isset($paidsupport['expiry'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Support Expiry",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                        if($paidsupport['expiry']){
                                                            echo esc_html(date_i18n("F d, Y", jssupportticketphplib::JSST_strtotime($paidsupport['expiry'])));
                                                        } else {
                                                            echo esc_html(__("No expiration",'js-support-ticket'));
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>

                                        <?php
                                        // non-premium section
                                        // show only if agent and has permission to mark ticket as non-premium
                                        if($agentallowed){
                                            ?>
                                            <div class="js-tkt-wc-order-box">
                                                <div class="js-tkt-wc-order-item">
                                                    <label>
                                                        <input type="checkbox" id="nonpreminumsupport" <?php if($nonpreminumsupport) echo 'checked'; ?>>
                                                        <b><?php echo esc_html(__("Non-preminum support",'js-support-ticket')); ?></b>
                                                    </label>
                                                    <?php echo wp_kses(JSSTformfield::hidden('paidsupportitemid',jssupportticket::$_data[0]->paidsupportitemid), JSST_ALLOWED_TAGS) ?>
                                                    <div>
                                                        <small><i><?php echo esc_html(__("Check this box if this ticket should NOT apply against the paid support",'js-support-ticket')); ?></i></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php
                                }
                            }
                            if($linktickettoorder && in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Link To Paid Support')){
                                $paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(jssupportticket::$_data[0]->uid);
                                $paidsupportlist = array();
                                foreach($paidsupportitems as $row){
                                    $paidsupportlist[] = (object) array(
                                        'id' => $row->itemid,
                                        'text' => esc_html(__("Order",'js-support-ticket')).' #'.$row->orderid.', '.$row->itemname.', '. esc_html(__("Remaining",'js-support-ticket')).':'.$row->remaining.' '. esc_html(__("Out of",'js-support-ticket')).':'.$row->total,
                                    );
                                }
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-pdsprt">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Paid Support",'js-support-ticket')).': '. esc_html(__("Link ticket to paid support",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <div class="js-tkt-wc-order-item">
                                            <?php echo wp_kses(JSSTformfield::select('paidsupportitemid',$paidsupportlist,null,esc_html(__("Select",'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                                            <button type="button" class="btn" id="paidsupportlinkticketbtn"><?php echo esc_html(__("Link",'js-support-ticket')); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
						<?php apply_filters('js_support_ticket_admin_details_right_last', jssupportticket::$_data[0]->id); ?>
                    </div>
                </div>
                <?php
            } else { // Record Not FOund
                JSSTlayout::getNoRecordFound();
            }
        } else {// User is permission
            JSSTlayout::getPermissionNotGranted();
        }
    } else {// User is guest
        $redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail'));
        $redirect_url = jssupportticketphplib::JSST_safe_encoding($redirect_url);
        JSSTlayout::getUserGuest($redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
