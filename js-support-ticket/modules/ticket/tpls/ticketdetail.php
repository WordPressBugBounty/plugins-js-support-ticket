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
        if (in_array('aipoweredreply', jssupportticket::$_active_addons)){
            $jstmod = 'aipoweredreply';
            $jstreplymod = 'aipoweredreply';
        } else {
            $jstmod = 'ticket';
            $jstreplymod = 'reply';
        }
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
                    jQuery('div#userpopupforchangestatus').slideUp('slow');
                    jQuery('div#userpopupforchangepriority').slideUp('slow');
                    jQuery('div#popupfordepartmenttransfer').slideUp('slow');
                    jQuery('#usercredentailspopup').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div#userpopupblack').hide();
                    }, 700);
                });

                jQuery('a#changestatus').click(function (e) {
                    e.preventDefault();
                    jQuery('div#userpopupforchangestatus').slideDown('slow');
                    jQuery('#userpopupblack').show();
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
                jQuery(document).delegate('div#userpopupforchangestatus .popup-header-close-img', 'click', function (e) {
                    jQuery('div#userpopupforchangestatus').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });
                jQuery(document).delegate('div#popupfordepartmenttransfer .popup-header-close-img', 'click', function (e) {
                    jQuery('div#popupfordepartmenttransfer').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });
                jQuery(document).delegate('div#popupforinternalnote .internalnote-popup-header-close-img', 'click', function (e) {
                    jQuery('div#popupforinternalnote').slideUp('slow');
                    jQuery('div#popup-record-data').html('');
                });

                jQuery('a#internalnotebtn').click(function (e) {
                    e.preventDefault();
                    jQuery('div#popupforinternalnote').slideDown('slow');
                    jQuery('.internalnote-popup-background').show();
                });

                jQuery(document).delegate('#close-pop, img.close-merge', 'click', function (e) {
                    jQuery('div#mergeticketselection').fadeOut();
                    jQuery('div#popup-record-data').html('');
                });

                jQuery('div.popup-header-close-img,div.jsst-popup-background,input#cancele,input#cancelee,input#canceleee,input#canceleeee,input#canceleeeee,input#canceleeeeee').click(function (e) {
                    jQuery('div.jsst-popup-wrapper').slideUp('slow');
                    jQuery('div#popupforagenttransfer').slideUp('slow');
                    jQuery('div.jsst-merge-popup-wrapper').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div.jsst-popup-background').hide();
                        jQuery('div.jsst-popup-wrapper').hide();
                        jQuery('#userpopupblack').hide();
                    }, 700);
                });

                jQuery('div.internalnote-popup-header-close-img,div.internalnote-popup-background').click(function (e) {
                    jQuery('div#popupforinternalnote').slideUp('slow');
                    setTimeout(function () {
                        jQuery('div.internalnote-popup-background').hide();
                    }, 700);
                });

                jQuery(document).delegate('#ticketpopupsearch','submit', function (e) {
                    var ticketid = jQuery('#ticketidformerge').val();
                    var nonce = jQuery('#nonce').val();
                    e.preventDefault();
                    var name = jQuery('input#name').val();
                    var email = jQuery('input#email').val();
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'mergeticket', task: 'getTicketsForMerging', name: name, email: email,ticketid:ticketid, '_wpnonce': nonce}, function (data) {
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

            function getmergeticketid(mergeticketid, mergewithticketid, mergeNonce){
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
                getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce);
            }

            function getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'mergeticket', task: 'getLatestReplyForMerging', mergeid:mergeticketid,mergewith:mergewithticketid, '_wpnonce': mergeNonce}, function (data) {
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
                jQuery('div#userpopupforchangestatus').slideUp('slow');
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

            function resetMergeFrom(nonce) {
                var ticketid = jQuery('#ticketidformerge').val();
                var name = '';
                var email = '';
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'mergeticket', task: 'getTicketsForMerging', name: name, email: email,ticketid:ticketid, '_wpnonce': nonce}, function (data) {
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
        $jssupportticket_js ='
            // AI-Powered Reply
            // Temporary storage for the current tickets replies for filtering
            let currentTicketAllReplies = [];
            jQuery(document).ready(function(){
                // Get DOM elements with new prefixed IDs using jQuery selectors
                const replyTextarea = jQuery("#js-ticket-reply-textarea");
                const matchingTicketsSection = jQuery("#js-ticket-matching-tickets-section");
                const matchingTicketsList = jQuery("#js-ticket-matching-tickets-list");
                const selectedTicketRepliesSection = jQuery("#js-ticket-selected-ticket-replies-section");
                const selectedTicketRepliesContent = jQuery("#js-ticket-selected-ticket-replies-content");
                const messageModal = jQuery("#js-ticket-message-modal");

                jQuery(".js-ticket-info-icon-wrapper").hover(
                    function(e){
                        jQuery(this).addClass("tooltip-active");
                    },
                    function(e){
                        jQuery(this).removeClass("tooltip-active");
                    }
                );
                
                // Function to show custom modal
                function showModal(message) {
                    jQuery("#js-ticket-modal-message").text(message);
                    messageModal.removeClass("js-ticket-hidden");
                }

                // Function to hide custom modal
                jQuery("#js-ticket-modal-close-btn").on("click", function(e) {
                    e.preventDefault();
                    jsReplyHideLoading();
                    messageModal.addClass("js-ticket-hidden");
                    jQuery("div#multiformpopupblack").hide();
                });

                // Function to copy text to clipboard (works in iframes)
                function copyToClipboard(text) {
                    const tempTextArea = document.createElement("textarea");
                    tempTextArea.value = text;
                    document.body.appendChild(tempTextArea);
                    tempTextArea.select();
                    try {
                        const successful = document.execCommand("copy");
                        console.log(successful);
                        if(successful) {
                            showModal("'.__("Copied to clipboard!", "js-support-ticket").'");    
                        } else {
                            showModal("'.__("Failed to copy!", "js-support-ticket").'");
                        }
                    } catch (err) {
                        showModal("'.__("Failed to copy to clipboard. Please copy manually.", "js-support-ticket").'");
                    }
                    document.body.removeChild(tempTextArea);
                }

                // Function to append text to reply area
                function appendToReplyArea(textToAppend) {
                    let currentContent = replyTextarea.val();
                    let newContent = currentContent + "\n" + textToAppend; // Append with a newline

                    // Check for TinyMCE or similar rich text editor
                    if (typeof tinyMCE !== "undefined" && tinyMCE.get("jsticket_message") && !jQuery("#wp-jsticket_message-wrap").hasClass("html-active")) {
                        // Assuming "jsticket_message" is the ID of your TinyMCE textarea
                        const editor = tinyMCE.get("jsticket_message");
                        editor.execCommand("mceInsertContent", false, textToAppend);
                    } else {
                        replyTextarea.val(newContent);
                    }
                    showModal("'.__("Reply content appended!", "js-support-ticket").'");
                }

                // Function to filter and display replies based on dropdown selection
                function displayFilteredReplies(ticket, filterType) {
                    console.log(ticket);
                    console.log(filterType);

                    let filteredReplies = [];
                    if (filterType === "marked") {
                        filteredReplies = currentTicketAllReplies.filter(reply => reply.isMarked);
                    } else { // "all"
                        filteredReplies = currentTicketAllReplies;
                    }
                    displayTicketReplies(ticket, filteredReplies);
                }

                // Event listener for Replies Filter dropdown
                jQuery("#js-ticket-replies-filter").on("change", function() {
                    const selectedFilter = jQuery(this).val();
                    const activeTicketItem = matchingTicketsList.find(".js-ticket-list-item.active");
                    
                    if (!activeTicketItem.length) {
                        showModal("'.__("No ticket selected!", "js-support-ticket").'");
                        return;
                    }
                    
                    const ticketId = activeTicketItem.data("ticket-id");
                    const ticketTitle = activeTicketItem.find(".js-ticket-title").text();
                    
                    // Show loading message
                    jsReplyShowLoading();
                    
                    // Fetch replies based on filter and ticket ID
                    jQuery.post(ajaxurl, {
                        action: "jsticket_ajax",
                        jstmod: "'.$jstreplymod.'",
                        task: "getFilteredReplies",
                        ticket_id: ticketId,
                        filter: selectedFilter,
                        "_wpnonce": "'. esc_attr(wp_create_nonce("get-filtered-replies")).'"
                    }, function(data) {
                        jsReplyHideLoading();
                        
                        if (data.success) {
                            const ticket = {
                                id: ticketId,
                                text: ticketTitle
                            };
                            displayTicketReplies(ticket, data.data.replies);
                        } else {
                            showModal(data.message || "'.__("Error fetching replies.", "js-support-ticket").'");
                        }
                    }).fail(function() {
                        jsReplyHideLoading();
                        showModal("'.__("Failed to fetch replies. Please try again.", "js-support-ticket").'");
                    });
                });

                // Modify the ticket click handler to set active state and store ticket ID
                matchingTicketsList.on("click", ".js-ticket-list-item", function() {
                    // Remove active class from all items
                    matchingTicketsList.find(".js-ticket-list-item").removeClass("active");
                    
                    // Add active class to clicked item
                    const listItem = jQuery(this);
                    listItem.addClass("active");
                    
                    // const ticketId1 = activeTicketItem.data("ticket-id");
                    const ticketId = listItem.data("ticket-id");
                    const ticketTitle = listItem.find(".js-ticket-title").text();
                    
                    // Show loading message
                    jsReplyShowLoading();
                    
                    // Reset filter to "all" when selecting a new ticket
                    jQuery("#js-ticket-replies-filter").val("all");
                    
                    // Fetch all replies initially
                    jQuery.post(ajaxurl, {
                        action: "jsticket_ajax",
                        jstmod: "'.$jstreplymod.'",
                        task: "getFilteredReplies",
                        ticket_id: ticketId,
                        filter: "all",
                        "_wpnonce": "'. esc_attr(wp_create_nonce("get-filtered-replies")).'"
                    }, function(data) {
                        jsReplyHideLoading();
                        
                        if (data.success) {
                            const ticket = {
                                id: ticketId,
                                text: ticketTitle
                            };
                            displayTicketReplies(ticket, data.data.replies);
                        } else {
                            showModal(data.message || "'.__("Error fetching replies.", "js-support-ticket").'");
                        }
                    }).fail(function() {
                        jsReplyHideLoading();
                        showModal("'.__("Failed to fetch replies. Please try again.", "js-support-ticket").'");
                    });
                });

                jQuery(".js-ticket-segmented-control-option").on("click", function(e) {
                    var actionType = jQuery(this).data("type");
                    var selectedValue = jQuery(this).data("value"); // Get the "data-value" attribute (default, enable, disable).
                    var selectedId = jQuery(this).data("id");
                    
                    // Remove the "active" class from all segmented control options.
                    // jQuery("#js-ticket-ai-reply-status-control").find(".js-ticket-segmented-control-option").removeClass("active");
                    jQuery(this).closest("#js-ticket-ai-reply-status-control")
                   .find(".js-ticket-segmented-control-option")
                   .removeClass("active");

                    // Add the "active" class to the currently clicked option.
                    jQuery(this).addClass("active");

                    // Update the value of the hidden input field.
                    jQuery("#js-ticket-ai-reply-status-hidden").val(selectedValue);

                    // Perform the AJAX request using jQuery.ajax().
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "reply", task: "markedAsAiPoweredReply", status:selectedValue, id: selectedId, type: actionType, "_wpnonce":"'.esc_attr(wp_create_nonce("ai-powered-reply")).'"}, function (data) {
                        if (data) {
                            jQuery(".jssupportticket-review-box-popup").remove();
                            jQuery(".jssupportticket-premio-review-box").remove();
                        }
                    });
                });

                // Event listener for AI-Powered Reply button
                jQuery("#js-ticket-ai-reply-btn").on("click", function (e) {
                    e.preventDefault();
                    // Show loading message
                    jsReplyShowLoading();

                    const currentTitle = jQuery(".js-ticket-current-ticket-title").text();
                    const currentTicketId = jQuery(".js-ticket-current-ticket-id").text();
                    const tickets = fetchTicketsFromPHP(currentTicketId, currentTitle, "all");
                });

                // Event listener for Replies Filter dropdown
                jQuery("#js-ticket-tickets-filter").on("change", function(e) {
                    e.preventDefault();
                    const selectedFilter = jQuery(this).val();
                    const currentTitle = jQuery(".js-ticket-current-ticket-title").text();
                    const currentTicketId = jQuery(".js-ticket-current-ticket-id").text();

                    const tickets = fetchTicketsFromPHP(currentTicketId, currentTitle, selectedFilter); 
                });

                function fetchTicketsFromPHP(ticketId, ticketSubject, selectedFilter) {
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", ticketSubject: ticketSubject, ticketId: ticketId, filter: selectedFilter, jstmod: "'.$jstmod.'", task: "checkAIReplyTicketsBySubject", "_wpnonce":"'. esc_attr(wp_create_nonce("check-smart-reply")).'"}, function (data) {
                        if(data) {
                            displayMatchingTickets(data);
                        } else {
                            showModal(`'.__('Error fetching matching tickets:', 'js-support-ticket').'`);
                            return [];
                            jQuery(".smartReplyTickets").hide();
                        }
                    });
                }

                // Function to display matching tickets
                function displayMatchingTickets(matchingTickets) {
                    // Parse if it is a string
                    if (typeof matchingTickets === "string") {
                        try {
                            matchingTickets = JSON.parse(matchingTickets);
                        } catch (e) {
                            console.error("Failed to parse matchingTickets:", e);
                            matchingTickets = [];
                        }
                    }
                    
                    matchingTicketsList.empty(); // Clear previous list
                    selectedTicketRepliesSection.addClass("js-ticket-hidden"); // Hide replies section if open
                    jQuery("#js-ticket-replies-filter").val("all"); // Reset filter when showing new tickets

                    jQuery(".js-ticket-container").show();

                    if (matchingTickets.length === 0) {
                        matchingTicketsList.html(`<p class="js-ticket-id">'.__("No matching tickets found.", "js-support-ticket").'</p>`);
                        matchingTicketsSection.removeClass("js-ticket-hidden");
                        jsReplyHideLoading();
                        matchingTicketsSection.removeClass("js-ticket-hidden");
                        return;
                    }

                    jQuery.each(matchingTickets, (index, ticket) => {
                        const listItem = jQuery("<li></li>")
                            .addClass("js-ticket-list-item")
                            .data("ticket-id", ticket.id) // Store ticket ID in data attribute
                            .html(`<p class="js-ticket-id">'.__("Ticket ID:", "js-support-ticket").'`+ticket.ticketid+`</p><p class="js-ticket-title">`+ticket.text+`</p><p class="js-ticket-id">`+ticket.message+`</p>`);
                        matchingTicketsList.append(listItem);
                    });
                    jsReplyHideLoading();
                    matchingTicketsSection.removeClass("js-ticket-hidden");
                }

                function escapeHtml(unsafe) {
                    return unsafe
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                }

                // Function to display replies of a selected ticket
                function displayTicketReplies(ticket, replies) {
                    // Initialize replies as empty array if undefined
                    if (typeof replies === "undefined") {
                        replies = [];
                    }
                    
                    // Parse if it is a string
                    if (typeof replies === "string") {
                        try {
                            replies = JSON.parse(replies);
                            // Ensure it is always an array after parsing
                            if (!Array.isArray(replies)) {
                                replies = [];
                            }
                        } catch (e) {
                            console.error("Failed to parse replies:", e);
                            replies = [];
                        }
                    }
                    
                    // Additional type checking
                    if (!Array.isArray(replies)) {
                        console.error("Replies is not an array:", replies);
                        replies = [];
                    }

                    jQuery("#js-ticket-selected-ticket-replies-title").text(`'.__("Replies for:", "js-support-ticket").' `+escapeHtml(ticket.text));
                    selectedTicketRepliesContent.empty(); // Clear previous replies

                    // Now safe to check length
                    if (replies.length === 0) {
                        selectedTicketRepliesContent.html(`<p class="js-ticket-id">'.__("No replies found for this ticket.", "js-support-ticket").'</p>`);
                    } else {
                        jQuery.each(replies, (index, reply) => {
                            // Add null checks for reply properties
                            const replyId = reply?.id || __("N/A", "js-support-ticket");
                            const replyText = reply?.text || __("No content", "js-support-ticket");
                            const replyName = reply?.name || __("No content", "js-support-ticket");
                            const replyTimestamp = reply?.timestamp ? new Date(reply.timestamp).toLocaleString() : __("No date", "js-support-ticket");

                            const replyDiv = jQuery("<div></div>")
                                .addClass("js-ticket-reply-item")
                                .html(`
                                    <div class="js-ticket-reply-header">
                                        <span class="js-ticket-reply-id">'.__("Reply By:", "js-support-ticket").' `+escapeHtml(replyName)+`</span>
                                        <span class="js-ticket-reply-timestamp">`+replyTimestamp+`</span>
                                    </div>
                                    <div class="js-ticket-reply-text">
                                        `+replyText+`
                                    </div>
                                    <div class="js-ticket-reply-actions">
                                        <button class="js-ticket-reply-action-btn copy-btn" data-reply-content="`+escapeHtml(replyText)+`">'.__('Copy', 'js-support-ticket').'</button>
                                        <button class="js-ticket-reply-action-btn append-btn" data-reply-content="`+escapeHtml(replyText)+`">'.__('Append', 'js-support-ticket').'</button>
                                    </div>
                                `);
                            selectedTicketRepliesContent.append(replyDiv);
                        });

                        // Attach event listeners
                        selectedTicketRepliesContent.find(".copy-btn").on("click", function(e) {
                            e.preventDefault();
                            copyToClipboard(jQuery(this).data("reply-content"));
                        });
                        
                        selectedTicketRepliesContent.find(".append-btn").on("click", function(e) {
                            e.preventDefault();
                            appendToReplyArea(jQuery(this).data("reply-content"));
                        });
                    }

                    matchingTicketsSection.addClass("js-ticket-hidden");
                    selectedTicketRepliesSection.removeClass("js-ticket-hidden");
                }

                // Event listener for Close Replies button
                jQuery("#js-ticket-close-replies-btn").on("click", function(e) {
                    e.preventDefault();
                    selectedTicketRepliesSection.addClass("js-ticket-hidden");
                    matchingTicketsSection.removeClass("js-ticket-hidden"); // Show matching tickets again
                });

                // Event listener for Close Tickets button
                jQuery("#js-ticket-close-tickets-btn").on("click", function(e) {
                    e.preventDefault();
                    matchingTicketsList.empty(); // Clear previous list
                    jQuery("#js-ticket-tickets-filter").val("all"); // Reset filter when showing new tickets
                    selectedTicketRepliesSection.addClass("js-ticket-hidden"); // Hide replies section if open
                    jQuery("#js-ticket-replies-filter").val("all"); // Reset filter when showing new tickets
                    jQuery(".js-ticket-container").hide();
                    matchingTicketsSection.addClass("js-ticket-hidden");
                });
            });';
        $jssupportticket_js .="
            jQuery(document).ready(function(){
                jQuery(document).on('submit','#js-ticket-usercredentails-form',function(e){
                    e.preventDefault(); // avoid to execute the actual submit of the form.
                    var fdata = jQuery(this).serialize(); // serializes the form's elements.
                    var nonce = jQuery('#nonce').val();
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'storePrivateCredentials',formdata_string:fdata, '_wpnonce': nonce}, function (data) {
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
                });
                jQuery('span.js-ticket-thread-read-status-wrp').hover(
                    function(e){
                        jQuery(this).find('span.js-ticket-thread-read-status-detail').css('display','inline-block');
                    },
                    function(e){
                        jQuery(this).find('span.js-ticket-thread-read-status-detail').css('display','none');
                    }
                );
            });

            function addEditCredentail(nonce, ticketid, uid, cred_id = 0, cred_data = ''){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'getFormForPrivteCredentials', ticketid: ticketid, cred_id: cred_id, cred_data: cred_data, uid: uid, '_wpnonce':nonce}, function (data) {
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

            function getCredentails(ticketid, nonce){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'getPrivateCredentials',ticketid:ticketid, '_wpnonce': nonce}, function (data) {
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

            function removeCredentail(cred_id, nonce){
                var params = {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'removePrivateCredential',cred_id:cred_id, '_wpnonce': nonce};
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
            function closeCredentailsForm(ticketid, nonce){
                getCredentails(ticketid, nonce);
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
                    if(in_array('privatecredentials',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6 ){
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
                                <?php $nonce = wp_create_nonce('get-form-for-privte-credentials-'.jssupportticket::$_data[0]->id); ?>
                                <button class="js-ticket-usercredentail-data-add-new-button" onclick="addEditCredentail('<?php echo esc_js($nonce);?>',<?php echo esc_js(jssupportticket::$_data[0]->id);?>,<?php echo esc_js(JSSTincluder::getObjectClass('user')->uid());?>);" >
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
                $field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, jssupportticket::$_data[0]->multiformid);
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
													echo wp_kses_post(jssupportticketphplib::JSST_preg_replace("/\([^)]+\)/","( ".__("Agent")." )",$historymessage));
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
                    <?php
                } else { ?>
                    <div class="js-ticket-empty-msg"><?php
                    echo esc_html(__('No Record Found','js-support-ticket')); ?></div>
                    <?php
                } ?>
            </div>
        </div>

        <?php
        $jssupportticket_js ="
            function showPopupAndFillValues(id,pfor,nonce) {
                jQuery('div.edit-time-popup').hide();
                if(pfor == 1){
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'reply', task: 'getReplyDataByID', '_wpnonce': nonce}, function (data) {
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
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'timetracking', task: 'getTimeByReplyID', '_wpnonce': nonce}, function (data) {
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
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: id, jstmod: 'note', task: 'getTimeByNoteID', '_wpnonce': nonce}, function (data) {
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
                    jQuery.post(ajaxurl, {action: 'jsticket_ajax', ticketid: id, jstmod: 'mergeticket', task: 'getTicketsForMerging', '_wpnonce': nonce}, function (data) {
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
            function updateticketlist(pagenum,ticketid,nonce){
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'mergeticket', task: 'getTicketsForMerging', ticketid:ticketid,ticketlimit:pagenum, '_wpnonce': nonce}, function (data) {
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
        <div id="black_wrapper_ai_reply" style="display:none;"></div>
        <div id="js_ai_reply_loading">
            <img alt="<?php echo esc_html(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
        </div>
        <span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
        <span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
        <div class="jsst-popup-background" style="display:none" ></div>
        <div class="internalnote-popup-background" style="display:none" ></div>
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
            <form id="jsst-reply-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedreply&action=jstask"),"save-edited-reply-".jssupportticket::$_data[0]->id)); ?>" >
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
                <form id="jsst-time-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedtime&action=jstask"),"save-edited-time-".jssupportticket::$_data[0]->id)); ?>" >
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
                <form id="jsst-note-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=note&task=saveeditedtime&action=jstask"),"save-edited-time-".jssupportticket::$_data[0]->id)); ?>" >
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
        if($printflag == false && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6){
            ?>

            <?php if(in_array('actions',jssupportticket::$_active_addons)){ 
				if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer')){
				?>
				<div id="popupfordepartmenttransfer" style="display:none" >
					<div class="jsst-popup-header" >
						<div class="popup-header-text" >
							<?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])) ." ". esc_html(__('Transfer', 'js-support-ticket')); ?>
						</div>
						<div class="popup-header-close-img" >
						</div>
					</div>
					<div>
						<form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'transferdepartment')),"transfer-department-".jssupportticket::$_data[0]->id)); ?>" enctype="multipart/form-data">
							<div class="js-ticket-premade-msg-wrp"><!-- Select Department Wrapper -->
								<div class="js-ticket-premade-field-title"><?php echo esc_html(__('Select', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?></div>
								<div class="js-ticket-premade-field-wrp">
									<?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), isset(jssupportticket::$_data[0]->departmentid) ? jssupportticket::$_data[0]->departmentid : '', esc_html(__('Select', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($field_array['department'])), array('class' => 'js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>

								</div>
							</div>
							<?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
								<div class="js-ticket-text-editor-wrp">
									<div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Type Note for', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?></div>
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
						<form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'assigntickettostaff')),"assign-ticket-to-staff-".jssupportticket::$_data[0]->id)); ?>" enctype="multipart/form-data">
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
                    <div class="internalnote-popup-header-close-img" >
                    </div>
                </div>
                <div>  <!--  postinternalnote Area   -->
                    <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'note','task'=>'savenote')),"save-note-".jssupportticket::$_data[0]->id)); ?>" enctype="multipart/form-data">
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
                            <div class="js-attachment-field-title"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></div>
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
                $cur_uid = JSSTincluder::getObjectClass('user')->uid();
                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff']) {
                    $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'actionticket')),"action-ticket-".jssupportticket::$_data[0]->id);
                } else {
                    $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply-".jssupportticket::$_data[0]->id);
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
                                    <?php /* if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->staffphotophoto) { ?>
                                        <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                    <?php } else { */
                                        echo wp_kses(jsst_get_avatar(jssupportticket::$_data[0]->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                    // } ?>
                                </div>
                                <div class="js-tkt-det-user-cnt"><!-- Right Side -->
                                    <?php
                                    if(!empty($field_array['fullname'])) { ?>
                                        <div class="js-tkt-det-user-data name">
                                            <?php echo esc_html(jssupportticket::$_data[0]->name); ?>
                                        </div>
                                        <?php
                                    } ?>
                                    <div class="js-tkt-det-user-data subject">
                                       <?php echo esc_html(jssupportticket::$_data[0]->subject); ?>
                                    </div>
                                    <?php
                                    if(!empty($field_array['email'])) { ?>
                                        <div class="js-tkt-det-user-data email">
                                            <?php echo esc_html(jssupportticket::$_data[0]->email); ?>
                                        </div>
                                        <?php 
                                    }
                                    if(!empty($field_array['phone'])) { ?>
                                        <div class="js-tkt-det-user-data number">
                                            <?php echo esc_html(jssupportticket::$_data[0]->phone); ?>
                                        </div>
                                        <?php 
                                    } ?>
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
                            <!-- Ticket Detail Message -->
                            <!-- Removed to avoid duplicate display; shown below in the ticket thread. -->
                            <?php /* echo wp_kses_post(jssupportticket::$_data[0]->message); */ ?>

                            <?php
                            jssupportticket::$_data['custom']['ticketid'] = jssupportticket::$_data[0]->id;
                            $customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, null, jssupportticket::$_data[0]->multiformid);
                            if (!empty($customfields)){ ?>
                                <div class="js-tkt-det-tkt-msg">
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
                                </div>
                                <?php
                            } ?>
                            <div class="js-tkt-det-actn-btn-wrp"> <!-- Ticket Action Button -->
                                <?php if ($printflag == false){
                                        $printpermission = false;
                                        $mergepermission = false;
                                    if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 6 ) {
                                        $printpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Print Ticket');
                                        $mergepermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Merge');

                                        ?>
                                        <a class="js-tkt-det-actn-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffaddticket','jssupportticketid'=>jssupportticket::$_data[0]->id))); ?>" title="<?php echo esc_html(__('Edit Ticket', 'js-support-ticket')); ?>">
                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit.png" title="<?php echo esc_html(__('Edit', 'js-support-ticket')); ?>" />
                                            <span><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></span>
                                        </a>
                                        <?php if (jssupportticket::$_data[0]->status != 5) { ?>
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
                                            if (jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6) {
                                                $nonce = wp_create_nonce("get-tickets-for-merging-".jssupportticket::$_data[0]->id) ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="mergeticket" onclick="return showPopupAndFillValues(<?php echo esc_js(jssupportticket::$_data[0]->id);?>,4, '<?php echo esc_js($nonce);?>')">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/merge-ticket.png" title="<?php echo esc_html(__('Merge', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Merge', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php }/*Merge Ticket*/
                                        } ?>
                                        <?php if(in_array('actions',jssupportticket::$_active_addons)){ ?>
                                            <?php if($printpermission && jssupportticket::$_data[0]->status != 6) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                    <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_html(__('Print', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                                </a>
                                                <!-- Print Ticket -->
                                            <?php } ?>
                                        <?php } ?>
                                        <?php $deletepermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
                                        if($deletepermission) { ?>
                                            <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete this ticket', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket-'.jssupportticket::$_data[0]->id)); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/delete.png" title= "<?php echo esc_html(__('Delete', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></span>
                                            </a>
                                            <?php
                                        }
                                        $credentialpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Credentials');
                                        if(in_array('privatecredentials',jssupportticket::$_active_addons) && $credentialpermission){ ?>
                                            <?php $nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$_data[0]->id) ?>
                                            <a class="js-tkt-det-actn-btn" href="javascript:return false;" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$_data[0]->id); ?>, '<?php echo esc_js($nonce); ?>')">
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
                                            <?php if (jssupportticket::$_data[0]->status != 6) { ?>
                                                <?php if (jssupportticket::$_data[0]->status != 5) { ?>
                                                    <a onclick="return confirm('<?php echo esc_html(__('Are you sure to close this ticket', 'js-support-ticket')); ?>');" class="js-tkt-det-actn-btn" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'closeticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),"close-ticket-".jssupportticket::$_data[0]->id)); ?>">
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
                                                            $link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'reopenticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id,'jsstpageid'=>get_the_ID())),"reopen-ticket-".jssupportticket::$_data[0]->id); ?>
                                                            <a class="js-tkt-det-actn-btn" href="<?php echo esc_url($link); ?>" title="<?php echo esc_html(__('Reopen Ticket', 'js-support-ticket')); ?>">
                                                                <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" title="<?php echo esc_html(__('Reopen', 'js-support-ticket')); ?>" />
                                                                <span><?php echo esc_html(__('Reopen', 'js-support-ticket')); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (jssupportticket::$_config['show_ticket_delete_button'] == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_html(__('Are you sure you want to delete this ticket', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket-'.jssupportticket::$_data[0]->id)); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$_data[0]->id); ?>">
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
                                            if(in_array('privatecredentials',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6){ ?>
                                                <?php $nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$_data[0]->id) ?>
                                                <a class="js-tkt-det-actn-btn" href="javascript:return false;" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$_data[0]->id); ?>, '<?php echo esc_js($nonce); ?>')">
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
                                        <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 6) { ?>
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
                                                <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $note->staffphoto) { ?>
                                                    <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $note->staff_id ,'jsstpageid'=>get_the_ID()))); ?>">
                                                <?php } else { */
                                                    if (isset($note->userid) && !empty($note->userid)) {
                                                        echo wp_kses(jsst_get_avatar($note->userid), JSST_ALLOWED_TAGS);
                                                    } else { ?>
                                                        <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/ticketmanbig.png'; ?>" />
                                                    <?php } ?>
                                                <?php /* } */ ?>
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
                                                    <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$_data[0]->status != 6){
                                                        $nonce = wp_create_nonce('get-time-by-note-id-'.$note->id); ?>
                                                        <a class="js-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($note->id);?>,3, '<?php echo esc_js($nonce);?>')" >
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
                                <?php /* if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data[0]->staffphotophoto) { ?>
                                    <img alt="image" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                <?php } else { */
                                    echo wp_kses(jsst_get_avatar(jssupportticket::$_data[0]->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                // } ?>
                            </div>
                            <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
                                <?php
                                    if(!empty($field_array['fullname'])) { ?>
                                    <div class="js-ticket-thread-data">
                                        <span class="js-ticket-thread-person">
                                            <?php echo esc_html(jssupportticket::$_data[0]->name); ?>
                                        </span>
                                    </div>
                                    <?php 
                                }
                                if(!empty($field_array['email'])) { ?>
                                    <div class="js-ticket-thread-data">
                                        <span class="js-ticket-thread-email">
                                            <?php echo esc_html(jssupportticket::$_data[0]->email); ?>
                                        </span>
                                    </div>
                                    <?php 
                                } ?>
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
                                                        if(jssupportticketphplib::JSST_strpos($type, "image") !== false) {
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
                                            <?php /* if (in_array('agent',jssupportticket::$_active_addons) &&  $reply->staffphoto) { ?>
                                                <img  class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $reply->staffid ,'jsstpageid'=>get_the_ID()))); ?>">
                                            <?php } else { */
                                                echo wp_kses(jsst_get_avatar($reply->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                            // } ?>
                                        </div>
                                        <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
											<div class="js-ticket-thread-data">
                                                <?php
                                                if(!empty($field_array['fullname'])) { ?>
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
                                                    <?php 
                                                } ?>
                                                <?php 
                                                if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                    <?php if($reply->staffid != 0){
                                                        $hours = floor($reply->time / 3600);
                                                        $mins = floor($reply->time / 60);
                                                        $mins = floor($mins % 60);
                                                        $secs = floor($reply->time % 60);
                                                        $time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($hours), esc_html($mins), esc_html($secs));
                                                        ?>
                                                        <span class="js-ticket-thread-time"><?php echo esc_html($time); ?></span>
                                                    <?php } ?>
                                                <?php 
                                                }
                                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                                    $configname = 'agent';
                                                } else {
                                                    $configname = 'user';
                                                }
                                                if (jssupportticket::$_config['show_read_receipt_to_' . $configname . '_on_reply'] == 1 && !empty($reply->viewed_by) && (($configname == 'user' && jssupportticket::$_data[0]->uid == $reply->uid) || ($configname == 'agent' && $cur_uid == $reply->uid))) { ?>
                                                    <span class="js-ticket-thread-read-status-wrp">
                                                        <span class="js-ticket-thread-read-status-btn">
                                                           <img alt="<?php echo esc_html(__('View Image','js-support-ticket')) ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/view.png" />
                                                        </span>
                                                        <span class="js-ticket-thread-read-status-detail">
                                                            <span class="js-ticket-thread-read-status-row">
                                                                <?php 
                                                                echo '<b>'.esc_html(__('Viewed By','js-support-ticket').': ').'</b>';
                                                                if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
                                                                    echo esc_html(jssupportticket::$_config['title']);
                                                                }else{
                                                                    echo esc_html($reply->viewername); 
                                                                } ?>
                                                            </span>
                                                            <span class="js-ticket-thread-read-status-row">
                                                                <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($reply->viewed_on))); ?>
                                                            </span>
                                                        </span>
                                                    </span>
                                                    <?php 
                                                }
                                                 ?>
											</div>
                                            <?php
											if (jssupportticket::$_config['show_email_on_ticket_reply'] == 1 && !empty($field_array['email'])) {
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
                                                                        if(jssupportticketphplib::JSST_strpos($type, "image") !== false) {
                                                                            $path = JSSTincluder::getJSModel('attachment')->getAttachmentImage($attachment->id);
                                                                            echo '<a data-gall="gallery-'.esc_attr($reply->replyid).'" class="js-download-button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_attr($path) .'"  target="_blank">
                                                                            <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                                </a>';
                                                                        }
                                                                echo '</div>';
                                                                }
                                                            $nonce = wp_create_nonce("download-all-for-reply-".$reply->replyid);
                                                            echo wp_kses('
                                                                <a class="js-all-download-button" target="_blank" href="' . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'downloadallforreply', 'action'=>'jstask', 'downloadid'=>$reply->replyid, '_wpnonce'=>$nonce , 'jsstpageid'=>get_the_ID()))) . '" onclick="" target="_blank">'. esc_html(__('Download All', 'js-support-ticket')) . '</a>', JSST_ALLOWED_TAGS);?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                                <?php if (in_array('agent',jssupportticket::$_active_addons) &&  jssupportticket::$_data['user_staff']) {
                                                    ?>
                                                        <div class="js-ticket-thread-cnt-btm">
                                                            <?php
                                                            if (
                                                                JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Set AI Reply Mode for Reply') &&
                                                                in_array('aipoweredreply', jssupportticket::$_active_addons) && 
                                                                jssupportticket::$_data[0]->uid != $reply->uid && 
                                                                $reply->uid != 0) { ?>
                                                                <!-- This section contains the AI Reply Feature -->
                                                                <div class="js-ticket-ai-reply-status-wrapper">
                                                                    <label for="js-ticket-ai-reply-status-control">
                                                                        <?php echo esc_html__('AI-Powered Reply Mode', 'js-support-ticket').':'; ?>
                                                                    </label>
                                                                    <div class="js-ticket-info-icon-wrapper">
                                                                        <span class="js-ticket-info-icon" data-tooltip="<?php echo esc_html(__("Control how this individual reply influences the AI search and response generation process for future queries.",'js-support-ticket')); ?>">
                                                                            <img alt="<?php echo esc_html(__('Info','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/info-icon.png" />
                                                                        </span>
                                                                    </div>
                                                                    <div id="js-ticket-ai-reply-status-control" class="js-ticket-segmented-control">
                                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-default <?php echo ($reply->aireplymode == 0) ? 'active' : ''; ?>" data-value="0" data-type="reply" data-id="<?php echo $reply->replyid;?>" title="<?php echo __("Default: reply included in all AI search queries.", "js-support-ticket"); ?>">
                                                                            <?php echo esc_html__('Default', 'js-support-ticket'); ?>
                                                                        </button>
                                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-enable <?php echo ($reply->aireplymode == 1) ? 'active' : ''; ?>" data-value="1" data-type="reply" data-id="<?php echo $reply->replyid;?>" title="<?php echo __("Enable: reply used in AI queries only when the Enable Tickets filter is active.", "js-support-ticket"); ?>">
                                                                            <?php echo esc_html__('Enable', 'js-support-ticket'); ?>
                                                                        </button>
                                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-disable <?php echo ($reply->aireplymode == 2) ? 'active' : ''; ?>" data-value="2" data-type="reply" data-id="<?php echo $reply->replyid;?>">
                                                                            <?php echo esc_html__('Disable', 'js-support-ticket'); ?>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <?php
                                                            } ?>
                                                            <div class="js-ticket-thread-date">
                                                                <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($reply->created))); ?>
                                                            </div>
                                                            <div class="js-ticket-thread-actions">
                                                                <?php 
                                                                if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Reply') && jssupportticket::$_data[0]->status != 6){
                                                                    $nonce = wp_create_nonce('get-reply-data-by-id-'.$reply->replyid); ?>
                                                                    <a class="js-ticket-thread-actn-btn ticket-edit-reply-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($reply->replyid);?>,1, '<?php echo esc_js($nonce);?>')" >
                                                                        <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                                        <?php echo esc_html(__('Edit Reply','js-support-ticket'));?>
                                                                    </a>
                                                                    <?php
                                                                }
                                                                if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                                    if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$_data[0]->status != 6){
                                                                        $nonce = wp_create_nonce('get-time-by-reply-id-'.$reply->replyid); ?>
                                                                        <a class="js-ticket-thread-actn-btn ticket-edit-time-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($reply->replyid);?>,2, '<?php echo esc_js($nonce);?>')" >
                                                                            <img alt="image" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                                            <?php echo esc_html(__('Edit Time','js-support-ticket'));?>
                                                                        </a>
                                                                        <?php
                                                                	}
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
                                    if (jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->lock != 1 && jssupportticket::$_data[0]->status != 6): ?>
                                        <div class="js-ticket-reply-forms-heading"><?php echo esc_html(__('Reply a message', 'js-support-ticket')); ?></div>
                                        <div id="postreply" class="js-ticket-post-reply">
                                            <div class="js-ticket-reply-field-wrp">
                                                <div class="js-ticket-reply-field"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                            </div>
                                            <div class="js-ticket-reply-attachments"><!-- Attachments -->
                                                <div class="js-attachment-field-title"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></div>
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
                                <?php if (jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6) { ?>
                                    <div id="postreply" class="js-det-tkt-rply-frm"><!-- Post Reply Area -->
                                        <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply-".jssupportticket::$_data[0]->id)); ?>" enctype="multipart/form-data">
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
                                            <?php
                                            if(isset($field_array['premade']) && in_array('cannedresponses', jssupportticket::$_active_addons)){ ?>
                                                <div class="js-ticket-premade-msg-wrp"><!-- Premade Message Wrapper -->
                                                    <div class="js-ticket-premade-field-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['premade'])); ?>&nbsp;<?php echo esc_html(__('Message','js-support-ticket')); ?></div>
                                                    <div class="js-ticket-premade-field-wrp">
                                                        <?php echo wp_kses(JSSTformfield::select('premadeid', JSSTincluder::getJSModel('cannedresponses')->getPreMadeMessageForCombobox(), isset(jssupportticket::$_data[0]->premadeid) ? jssupportticket::$_data[0]->premadeid : '', esc_html(__('Select', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($field_array['premade'])), array('class' => 'js-ticket-premade-select', 'onchange' => 'getpremade(this.value);')), JSST_ALLOWED_TAGS); ?>
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
                                            <?php
                                            if (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Use AI Powered Reply Feature')) { ?>
                                                <div class="js-ticket-ai-reply-button-wrp"><!-- AI-Powered Reply -->
                                                    <div class="js-ticket-ai-powered-reply-wrapper">
                                                        <div class="js-ticket-ai-powered-reply-icon">
                                                            <img alt="<?php echo esc_html(__('AI Icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ai-icon.png" />
                                                        </div>
                                                        <div class="js-ticket-ai-powered-reply-content">
                                                            <div class="js-ticket-ai-powered-reply-title">
                                                                <?php echo esc_html__('AI-Powered Reply', 'js-support-ticket'); ?>
                                                            </div>
                                                            <div class="js-ticket-ai-powered-reply-text">
                                                                <?php echo esc_html__('Get context-aware suggestions for your response.', 'js-support-ticket'); ?>
                                                            </div>
                                                        </div>
                                                        <div id="js-ticket-ai-reply-btn" class="js-ticket-ai-powered-reply-action">
                                                            <a href="#" class="js-ticket-ai-powered-reply-button">
                                                                <?php echo esc_html__('Suggested Response', 'js-support-ticket'); ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <span class="js-ticket-current-ticket-title"><?php echo jssupportticket::$_data[0]->subject ?></span>
                                                    <span class="js-ticket-current-ticket-id"><?php echo jssupportticket::$_data[0]->id ?></span>
                                                    <div class="js-ticket-container">
                                                        <!-- Matching Tickets Section -->
                                                        <div id="js-ticket-matching-tickets-section" class="js-ticket-section js-ticket-matching-tickets-section js-ticket-hidden">
                                                            <div class="js-ticket-selected-tickets-header">
                                                                <div class="js-ticket-section-heading"><?php echo esc_html__('Matching Tickets', 'js-support-ticket'); ?></div>
                                                                <?php if(in_array('aipoweredreply', jssupportticket::$_active_addons)){ ?>
                                                                    <div class="js-ticket-filter-group">
                                                                        <label for="js-ticket-tickets-filter" class="js-ticket-filter-label"><?php echo esc_html__('Filter', 'js-support-ticket').': '; ?></label>
                                                                        <select id="js-ticket-tickets-filter" class="js-ticket-filter-select">
                                                                            <option value="all"><?php echo esc_html__('All Tickets', 'js-support-ticket'); ?></option>
                                                                            <option value="marked"><?php echo esc_html__('Enable Tickets', 'js-support-ticket'); ?></option>
                                                                        </select>
                                                                    </div>
                                                                <?php } ?>
                                                                <button id="js-ticket-close-tickets-btn" class="js-ticket-close-button">
                                                                    <?php echo esc_html__('Close', 'js-support-ticket'); ?>
                                                                </button>
                                                            </div>
                                                            <ul id="js-ticket-matching-tickets-list" class="js-ticket-list">
                                                                <!-- Matching tickets will be dynamically inserted here -->
                                                            </ul>
                                                        </div>

                                                        <!-- Selected Ticket Replies Section -->
                                                        <div id="js-ticket-selected-ticket-replies-section" class="js-ticket-section js-ticket-selected-replies-section js-ticket-hidden">
                                                            <div class="js-ticket-selected-replies-header">
                                                                <h2 class="js-ticket-section-heading" id="js-ticket-selected-ticket-replies-title"></h2>
                                                                <?php if(in_array('aipoweredreply', jssupportticket::$_active_addons)){ ?>
                                                                    <div class="js-ticket-filter-group">
                                                                        <label for="js-ticket-replies-filter" class="js-ticket-filter-label"><?php echo esc_html__('Filter', 'js-support-ticket').': '; ?></label>
                                                                        <select id="js-ticket-replies-filter" class="js-ticket-filter-select">
                                                                            <option value="all"><?php echo esc_html__('All Replies', 'js-support-ticket'); ?></option>
                                                                            <option value="marked"><?php echo esc_html__('Enable Replies', 'js-support-ticket'); ?></option>
                                                                        </select>
                                                                    </div>
                                                                <?php } ?>
                                                                <button id="js-ticket-close-replies-btn" class="js-ticket-close-button">
                                                                    <?php echo esc_html__('Close', 'js-support-ticket'); ?>
                                                                </button>
                                                            </div>
                                                            <div id="js-ticket-selected-ticket-replies-content" class="js-ticket-replies-content reply-content">
                                                                <!-- Replies from selected ticket will be dynamically inserted here -->
                                                            </div>
                                                        </div>

                                                        <!-- Custom Modal for Messages -->
                                                        <div id="js-ticket-message-modal" class="js-ticket-modal js-ticket-hidden">
                                                            <div class="js-ticket-modal-content">
                                                                <p id="js-ticket-modal-message" class="js-ticket-modal-message"></p>
                                                                <button id="js-ticket-modal-close-btn" class="js-ticket-modal-close-button">
                                                                    <?php echo esc_html__('OK', 'js-support-ticket'); ?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php 
                                            } ?>
                                            <div class="js-ticket-reply-attachments"><!-- Attachments -->
                                                <div class="js-attachment-field-title"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></div>
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
                                                    <?php
                                                    if (!empty($field_array['department'])) { ?>
                                                        <div class="js-ticket-signature-radio-box">
                                                            <?php echo wp_kses(JSSTformfield::checkbox('departmentsignature', array('1' => esc_html(jssupportticket::JSST_getVarValue($field_array['department'])) ." ". esc_html(__('Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                                        </div>
                                                        <?php
                                                    } ?>
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
                                if (jssupportticket::$_data[0]->status == 1) {
                                    $ticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                    $bgcolor = '#5bb12f';
                                    $color = '#FFFFFF';
                                } else {
                                    $ticketmessage = esc_html(jssupportticket::$_data[0]->statustitle);
                                    $bgcolor = jssupportticket::$_data[0]->statusbgcolour;
                                    $color = jssupportticket::$_data[0]->statuscolour;
                                }
                            ?>
                            <div class="js-tkt-det-status" style="background-color:<?php echo esc_attr($bgcolor);?>;color :<?php echo esc_attr($color);?>;">
                                <?php echo esc_html($ticketmessage); ?>
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
                                <?php
                                // check if the department is publish or not
                                if (isset($field_array['department'])) { ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->departmentname)); ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                    $configname = 'agent';
                                } else {
                                    $configname = 'user';
                                }
                                if (jssupportticket::$_config['show_closedby_on_' . $configname . '_tickets'] == 1 && jssupportticket::$_data[0]->status == 5) { ?>
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
                                <?php
                                if(isset($field_array['helptopic']) && in_array('helptopic', jssupportticket::$_active_addons)){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['helptopic'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->helptopic)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php
                                if(isset($field_array['product'])){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['product'])); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->producttitle)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Status', 'js-support-ticket')); ?><?php echo esc_html(__(' :','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php
                                            if (jssupportticket::$_data[0]->status == 5 || jssupportticket::$_data[0]->status == 6 ||
                                                jssupportticket::$_data[0]->status == 3) {
                                                $ticketmessage = esc_html(jssupportticket::$_data[0]->statustitle);
                                            } else {
                                                $ticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                            }
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
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Status -->
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(__('Status','js-support-ticket')); ?>
                                </div>
                                <?php
                                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 6) {
                                    if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Status')){
									?>
										<a class="js-tkt-det-hdg-btn" href="#" id="changestatus">
											<?php echo esc_html(__('Change','js-support-ticket')); ?>
										</a>
                                        <div id="userpopupforchangestatus" style="display:none" >
                                            <div class="jsst-popup-header" >
                                                <div class="popup-header-text" >
                                                    <?php echo esc_html(__('Change Status','js-support-ticket')); ?>
                                                </div>
                                                <div class="popup-header-close-img" >
                                                </div>
                                            </div>
                                            <div>
                                                <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'changestatus')),"change-status-".jssupportticket::$_data[0]->id)); ?>" enctype="multipart/form-data">
                                                    <div class="js-ticket-premade-msg-wrp"><!-- Select Status Wrapper -->
                                                        <div class="js-ticket-premade-field-title"><?php echo esc_html(__('Select Status', 'js-support-ticket')); ?></div>
                                                        <div class="js-ticket-premade-field-wrp">
                                                            <?php echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForCombobox(), jssupportticket::$_data[0]->status, esc_html(__('Select Status', 'js-support-ticket')), array('class' => 'js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>
                                                        </div>
                                                    </div>
                                                    <div class="js-ticket-reply-form-button-wrp">
                                                        <?php echo wp_kses(JSSTformfield::submitbutton('changestatus', esc_html(__('Change Status', 'js-support-ticket')), array('class' => 'button js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_changestatus'), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                                </form>
                                            </div> <!-- end of changestatus div -->
                                        </div>
                                    <?php
									}
                                }
                                ?>
                            </div>
                            <?php
                            if (!empty(jssupportticket::$_data[0]->status)) { ?>
                                <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$_data[0]->statusbgcolour);?>;color:<?php echo esc_attr(jssupportticket::$_data[0]->statuscolour);?>;">
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->statustitle)); ?>
                                </div>
                                <?php
                            } ?>
                        </div>
                        <?php
                        if(
                            in_array('agent',jssupportticket::$_active_addons) &&  
                            jssupportticket::$_data['user_staff'] &&
                            in_array('aipoweredreply', jssupportticket::$_active_addons) &&
                            JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Set AI Reply Mode for Ticket')){ ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Status -->
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <label class="js-ticket-ai-reply-status-control-label" for="js-ticket-ai-reply-status-control">
                                            <?php echo esc_html__('AI-Powered Reply Mode', 'js-support-ticket'); ?>
                                        </label>
                                        <div class="js-ticket-info-icon-wrapper">
                                            <span class="js-ticket-info-icon" data-tooltip="<?php echo esc_html(__("Control how this ticket and its replies influence AI search and response generation for future queries.",'js-support-ticket')); ?>">
                                                <img alt="<?php echo esc_html(__('Info','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/info-icon.png" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="js-tkt-det-tkt-prty-txt js-ticket-segmented-control-wrp">
                                    <div id="js-ticket-ai-reply-status-control" class="js-ticket-segmented-control">
                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-default <?php echo (jssupportticket::$_data[0]->aireplymode == 0) ? 'active' : ''; ?>" data-value="0" data-type="ticket" data-id="<?php echo jssupportticket::$_data[0]->id;?>" title="<?php echo __("Default: ticket and replies included in all AI queries.", "js-support-ticket"); ?>">
                                            <?php echo esc_html__('Default', 'js-support-ticket'); ?>
                                        </button>
                                        <button data-type="ticket" type="button" class="js-ticket-segmented-control-option js-ticket-enable <?php echo (jssupportticket::$_data[0]->aireplymode == 1) ? 'active' : ''; ?>" data-value="1" data-type="ticket" data-id="<?php echo jssupportticket::$_data[0]->id;?>" title="<?php echo __("Enable: ticket and replies used only when the “Enable Tickets” filter is active.", "js-support-ticket"); ?>">
                                            <?php echo esc_html__('Enable', 'js-support-ticket'); ?>
                                        </button>
                                        <button data-type="ticket" type="button" class="js-ticket-segmented-control-option js-ticket-disable <?php echo (jssupportticket::$_data[0]->aireplymode == 2) ? 'active' : ''; ?>" data-value="2" data-type="ticket" data-id="<?php echo jssupportticket::$_data[0]->id;?>" title="<?php echo __("Disable: ticket and replies excluded from AI queries.", "js-support-ticket"); ?>">
                                            <?php echo esc_html__('Disable', 'js-support-ticket'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        if(!empty($field_array['priority'])) { ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Priority -->
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <!-- Display heading based on field order  -->
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['priority'])); ?>
                                    </div>
                                    <?php
                                    if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$_data['user_staff'] && jssupportticket::$_data[0]->status != 6) {
                                        if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority')){
                                        ?>
                                            <a class="js-tkt-det-hdg-btn" href="#" id="changepriority">
                                                <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                            </a>
                                            <div id="userpopupforchangepriority" style="display:none;">
                                                <div class="js-ticket-priorty-header">
                                                    <?php echo esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($field_array['priority'])); ?>
                                                    <span class="close-history"></span>
                                                </div>
                                                <div class="js-ticket-priorty-fields-wrp">
                                                    <div class="js-ticket-select-priorty">
                                                        <?php echo wp_kses(JSSTformfield::select('prioritytemp', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$_data[0]->priorityid, esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($field_array['priority'])), array()), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                </div>
                                                <div class="js-ticket-priorty-btn-wrp">
                                                    <?php echo wp_kses(JSSTformfield::button('changepriority', esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($field_array['priority'])), array('class' => 'js-ticket-priorty-save', 'onclick' => 'actionticket(1);')), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::button('cancelee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                                if (!empty(jssupportticket::$_data[0]->priority)) { ?>
                                    <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$_data[0]->prioritycolour);?>; color:#ffffff;">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$_data[0]->priority)); ?>
                                    </div>
                                    <?php
                                } else { ?>
                                    <div class="js-tkt-det-tkt-prty-error-txt">
                                        <?php
                                        echo esc_html(__('No','js-support-ticket'))." ".esc_html(jssupportticket::JSST_getVarValue($field_array['priority']))." ".esc_html(__('set','js-support-ticket')); ?>
                                    </div>
                                    <?php
                                } ?>
                            </div>
                        <?php } ?>
                        <?php
                        $agentflag = in_array('agent', jssupportticket::$_active_addons) && $printflag == false && jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6;
                        $departmentflag = in_array('actions', jssupportticket::$_active_addons) && $printflag == false && jssupportticket::$_data[0]->status != 5 && jssupportticket::$_data[0]->status != 6 && isset($field_array['department']);
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
                                                    echo wp_kses(jsst_get_avatar(jssupportticket::$_data[0]->staffuid), JSST_ALLOWED_TAGS);
                                                    /* ?>
                                                    <img alt="<?php echo esc_attr(__('staff photo','js-support-ticket')); ?>" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=>jssupportticket::$_data[0]->staffid, 'jsstpageid'=>jssupportticket::getPageid()))); ?>">
                                                    <?php */
                                                } else { ?>
                                                    <img alt="<?php echo esc_attr(__('staff photo','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/user.png'; ?>" />
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
													<span class="js-tkt-det-trsfer-dep-txt-tit"><?php echo esc_html(jssupportticket::JSST_getVarValue($field_array['department'])).' : '; ?> </span>
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
                                    <?php
                                    if(!empty($field_array['fullname'])) {
                                        echo esc_html(jssupportticket::$_data[0]->name).' '. esc_html(__('Tickets','js-support-ticket'));
                                    } else {
                                        echo esc_html(__('Other Tickets','js-support-ticket'));
                                    } ?>
                                </div>
                            </div>
                            <div class="js-tkt-det-usr-tkt-list">
                                <?php
                                $fields_array = array(); // Array for form fields
                                foreach(jssupportticket::$_data['usertickets'] as $userticket){
                                    // Check if the form fields are already array
                                    if (!isset($fields_array[$userticket->multiformid])) {
                                        $fields_array[$userticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $userticket->multiformid);
                                    }
                                    // Now use the cached field array
                                    $ticket_field_array = $fields_array[$userticket->multiformid];
                                    ?>
                                    <div class="js-tkt-det-user">
                                        <div class="js-tkt-det-user-image">
                                            <?php echo wp_kses(jsst_get_avatar(jssupportticket::$_data[0]->uid), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-tkt-det-user-cnt">
                                            <div class="js-tkt-det-user-data name">
                                                <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->subject)); ?></span>
                                            </div>
                                            <?php
                                            // check if the department is publish or not
                                            if (isset($ticket_field_array['department'])) { ?>
                                                <div class="js-tkt-det-user-data">
                                                    <span class="js-tkt-det-user-tit"><?php echo esc_html(jssupportticket::JSST_getVarValue($ticket_field_array['department'])). ' : '; ?></span>
                                                    <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->departmentname)); ?></span>
                                                </div>
                                                <?php
                                            } ?>
                                            <div class="js-tkt-det-user-data">
                                                <?php
                                                if(!empty($ticket_field_array['priority'])) { ?>
                                                    <span class="js-tkt-det-prty" style="background:<?php echo esc_html($userticket->prioritycolour);?>;">
                                                       <?php echo esc_html(jssupportticket::JSST_getVarValue($userticket->priority)); ?>
                                                    </span>
                                                    <?php 
                                                } ?>
                                                <span class="js-tkt-det-status">
                                                    <?php
                                                        if ($userticket->status == 5 || 
                                                            $userticket->status == 6 ||
                                                            $userticket->status == 3) {
                                                            $userticketmessage = esc_html($userticket->statustitle);
                                                        } else {
                                                            $userticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                                        }
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
                            if($orderid != '' && ((isset($field_array['eddlicensekey']) && class_exists('EDD_Software_Licensing')) || isset($field_array['eddorderid']) || isset($field_array['eddorderid']))){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-edd">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Easy Digital Downloads",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <?php 
                                        if (isset($field_array['eddorderid'])) {  ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($field_array['eddorderid']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($orderid); ?></div>
                                            </div>
                                            <?php
                                        }
                                        if (isset($field_array['eddorderid'])) {  ?>
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
                                            <?php
                                        }
                                        if(isset($field_array['eddlicensekey']) && class_exists('EDD_Software_Licensing')){ ?>
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
                        if(isset($field_array['envatopurchasecode']) && in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$_data[0]->envatodata)){
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
