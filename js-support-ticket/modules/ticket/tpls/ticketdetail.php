<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div class="jsst-main-up-wrapper">
<?php
if (jssupportticket::$_config['offline'] == 2) {
    if(isset(jssupportticket::$jsst_data['error_message'])){
        if(jssupportticket::$jsst_data['error_message'] == 1){
            JSSTlayout::getUserGuest();
        }elseif(jssupportticket::$jsst_data['error_message'] == 2){
            JSSTlayout::getYouAreNotAllowedToViewThisPage();
        }
    }elseif (JSSTincluder::getObjectClass('user')->uid() != 0 || jssupportticket::$_config['visitor_can_create_ticket'] == 1) {
        JSSTmessage::getMessage();

        $jsst_printflag = false;
        if(isset(jssupportticket::$jsst_data['print']) && jssupportticket::$jsst_data['print'] == 1){
            $jsst_printflag = true;
        }
        if($jsst_printflag == true){
            wp_head();
        }

        if($jsst_printflag == false){
            //JSSTbreadcrumbs::getBreadcrumbs();
            include_once(JSST_PLUGIN_PATH . 'includes/header.php');
        }

        if (jssupportticket::$jsst_data['permission_granted'] == true) {
        if (!empty(jssupportticket::$jsst_data[0])) {
        /* A company supervisor reading a colleague's ticket sees it, and
           nothing that acts on it. (Roadmap 5.5-COM-06) */
        $jsst_companyreadonly = !empty(jssupportticket::$jsst_data['company_readonly']);

        wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js', array(), jssupportticket::$_config['productversion'], true);
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery.cluetip.min.js', JSST_PLUGIN_URL . 'includes/js/jquery.cluetip.min.js', array(), jssupportticket::$_config['productversion'], true);
        wp_enqueue_script( 'hoverIntent' );
    wp_enqueue_style('jquery.cluetip', JSST_PLUGIN_URL . 'includes/css/jquery.cluetip.css', array(), jssupportticket::$_config['productversion']);
        wp_enqueue_script('timer.js', JSST_PLUGIN_URL . 'includes/js/timer.jquery.js', array(), jssupportticket::$_config['productversion'], true);
        wp_enqueue_style('jssupportticket-venobox-css', JSST_PLUGIN_URL . 'includes/css/venobox.css', array(), jssupportticket::$_config['productversion']);
        wp_enqueue_script('venoboxjs',JSST_PLUGIN_URL.'includes/js/venobox.js', array(), jssupportticket::$_config['productversion'], true);
        /* Always core's own modules now. (Roadmap 6.0-AI-01) The add-on's
           model and core's are the same two functions, and routing to the
           add-on whenever it happened to be active meant a site that had it
           ran a copy nobody had been fixing. JSSTmergedaddon::coreOwns()
           makes core the owner outright, so this picks core unconditionally
           rather than asking which copy exists. */
        $jsst_jstmod = 'ticket';
        $jsst_jstreplymod = 'reply';
        $jsst_jssupportticket_js ="
            /* The timer the Edit Time popup was opened from. */
            var jsstEditTimer = null;
            var seconds = 0;
            function getpremade(val) {
                /* The picker's own first option is the placeholder, and choosing it
                   is not a request for anything. The endpoint already answers an
                   empty string to a non-numeric id, so this changes nothing an agent
                   sees - it just stops the round trip being made to find that out. */
                if (!val) { return; }
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'cannedresponses', task: 'getpremadeajax', ticketid: '". esc_js(jssupportticket::$jsst_data[0]->id) ."', '_wpnonce':'". esc_attr(wp_create_nonce('get-premade-ajax')) ."'}, function (data) {
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

            /* The reply and the internal note each have their own timer, and
               every function here works on the one next to the button that was
               pressed. They used to select every timer on the page, so starting
               one started both, and a reply could post the time an agent had
               started for a note. A timer keeps its own state: 0 not started,
               1 running or paused, 2 stopped. */
            function jsstTimerBox(el) {
                return jQuery(el).closest('.timer-right');
            }

            function changeTimerStatus(val, el) {
                var box = jsstTimerBox(el);
                var timer = box.find('div.timer');
                var state = timer.data('jsstTimerState') || 0;
                if(state == 2){// to handle stopped timer
                        return;
                }
                var buttons = box.find('span.timer-button');
                if(!buttons.filter('.cls_'+val).hasClass('selected')){
                    buttons.removeClass('selected');
                    buttons.filter('.cls_'+val).addClass('selected');
                    if(val == 1){
                        if(state == 0){
                            timer.timer({format: '%H:%M:%S'});
                        }
                        timer.data('jsstTimerState', 1);
                        timer.timer('resume');
                    }else if(val == 2) {
                         timer.timer('pause');
                    }else{
                         timer.timer('remove');
                        timer.data('jsstTimerState', 2);
                    }
                }
            }

            jQuery(document).ready(function(){
              jQuery('.venobox').venobox({
                    infinigall: true,
                    framewidth: 850,
                    titleattr: 'data-title',
                });
            });


            jQuery(function(){
                jQuery('ul li a').click(function (e) {
                    var imgID= jQuery(this).find('img').attr('id');
                  });
            });
            function showEditTimerPopup(el){
                var box = jsstTimerBox(el);
                jsstEditTimer = box.find('div.timer');
                jQuery('form#jsst-time-edit-form').hide();
                jQuery('form#jsst-reply-form').hide();
                jQuery('form#jsst-note-edit-form').hide();
                jQuery('div.edit-time-popup').show();
                box.find('span.timer-button').removeClass('selected');
                if((jsstEditTimer.data('jsstTimerState') || 0) != 0){
                    jsstEditTimer.timer('pause');
                }
                ex_val = jsstEditTimer.html();
                jQuery('input#edited_time').val('');
                jQuery('input#edited_time').val(ex_val.trim());
                jQuery('div.jsst-popup-background').show();
                jQuery('div#jsst-popup-wrapper').slideDown('slow');
            }
            function updateTimerFromPopup(){
                var timer = jsstEditTimer ? jsstEditTimer : jQuery('div.timer').first();
                val = jQuery('input#edited_time').val();
                arr = val.split(':', 3);
                timer.html(val);
                jQuery('div.jsst-popup-background').hide();
                jQuery('div.jsst-popup-wrapper').slideUp('slow');
                seconds = parseInt(arr[0])*3600 + parseInt(arr[1])*60 + parseInt(arr[2]);
                if(seconds < 0){
                    seconds = 0;
                }
                timer.timer('remove');
                timer.timer({
                    format: '%H:%M:%S',
                    seconds: seconds,
                });
                timer.timer('pause');
                timer.data('jsstTimerState', 1);
                desc = jQuery('textarea#t_desc').val();
                timer.closest('form').find('input[name=timer_edit_desc]').val(desc);
            }
            jQuery(document).ready(function ($) {
                //$('img.tooltip').cluetip({splitTitle: '|'});
                jQuery( 'form' ).submit(function(e) {
                    /* A form posts the time from its own timer, and only once
                       that timer has been started or edited - never another
                       form's. */
                    var form = jQuery(this);
                    var timer = form.find('div.timer').first();
                    if (timer.length && (timer.data('jsstTimerState') || 0) != 0) {
                        form.find('input[name=timer_time_in_seconds]').val(timer.data('seconds'));
                    }
                });
                jQuery('div#action-div a.button').click(function (e) {
                    e.preventDefault();
                });
                ";
                if($jsst_printflag != true){
                    $jsst_jssupportticket_js .="jQuery('#tabs').tabs();";
                }
                $jsst_jssupportticket_js .="

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
                    var href = '". jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'printticket','jssupportticketid'=>jssupportticket::$jsst_data[0]->id)) ."';
                    print = window.open(href, 'print_win', 'width=1024, height=800, scrollbars=yes');
                });

                //non premium support function
                jQuery('#nonpreminumsupport').change(function(){
                    if(jQuery(this).is(':checked')){
                        if(1 || confirm('". esc_html(__('Are you sure to mark this ticket non-premium?','js-support-ticket')) ."')){
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
                    alert('". esc_js(__('The primary ticket must be different from the ticket being merged.', 'js-support-ticket')) ."');
                    return false;
                }
                jQuery('#mergeticketselection').hide();
                getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce);
            }

            /* Multi-select merge: every ticket ticked in the candidate list is
               merged into the ticket being viewed. (Roadmap 4.0-CORE-04) */
            function jsstPreviewMergeSelection(primaryticket, previewNonce){
                var picked = [];
                jQuery('.jsst-merge-source:checked').each(function(){
                    picked.push(jQuery(this).val());
                });
                if(picked.length === 0){
                    alert('". esc_js(__('Tick at least one ticket to merge into this one.', 'js-support-ticket')) ."');
                    return false;
                }
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'mergeticket', task: 'previewMergeSelection', primaryticket: primaryticket, sources: picked.join(','), '_wpnonce': previewNonce}, function (data) {
                    if(data){
                        data = jQuery.parseJSON(data);
                        jQuery('div#popup-record-data').html('');
                        jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                    }
                });
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
                    alert('". esc_js(__('Write a message before posting.', 'js-support-ticket')) ."');
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
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        $jsst_yesno = array(
            (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
            (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
        );
        ?>
        <div id="userpopupblack" style="display:none;"> </div>
        <?php
        $jsst_jssupportticket_js ='
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

                jQuery(".js-ticket-info-icon-wrapper").hover(
                    function(e){
                        jQuery(this).addClass("tooltip-active");
                    },
                    function(e){
                        jQuery(this).removeClass("tooltip-active");
                    }
                );
                
                /* The canned response picker: type to narrow, arrows to choose.
                   (Roadmap 4.0-CORE-03) The same control the backend thread
                   draws, for the same reason - a select can only be searched by
                   its first letter. Everything it needs is already in the page,
                   so this is a filter over the DOM and no request is made until
                   an agent picks one. */
                (function () {
                    var box = jQuery("#js-ticket-canned-search");
                    if (!box.length) {
                        return;
                    }
                    var list = jQuery("#js-ticket-canned-list");
                    var none = list.find(".js-ticket-canned-none");
                    var options = list.find(".js-ticket-canned-option");
                    var at = -1;

                    function visible() {
                        return options.filter(":visible");
                    }

                    function open() {
                        list.prop("hidden", false);
                        box.attr("aria-expanded", "true");
                    }

                    function close() {
                        list.prop("hidden", true);
                        box.attr("aria-expanded", "false").removeAttr("aria-activedescendant");
                        options.removeClass("is-at").attr("aria-selected", "false");
                        at = -1;
                    }

                    /* Held as a position in the visible set rather than as an
                       index into every option, because the set changes under it
                       on every keystroke. */
                    function highlight(next) {
                        var shown = visible();
                        options.removeClass("is-at").attr("aria-selected", "false");
                        if (!shown.length) {
                            at = -1;
                            box.removeAttr("aria-activedescendant");
                            return;
                        }
                        at = (next + shown.length) % shown.length;
                        var one = shown.eq(at).addClass("is-at").attr("aria-selected", "true");
                        box.attr("aria-activedescendant", one.attr("id"));
                        var el = one[0];
                        var top = el.offsetTop;
                        var bottom = top + el.offsetHeight;
                        var view = list[0];
                        if (top < view.scrollTop) {
                            view.scrollTop = top;
                        } else if (bottom > view.scrollTop + view.clientHeight) {
                            view.scrollTop = bottom - view.clientHeight;
                        }
                    }

                    function filter() {
                        var typed = jQuery.trim(box.val()).toLowerCase();
                        var found = 0;
                        options.each(function () {
                            var one = jQuery(this);
                            /* Anywhere in the title, not only at the front. */
                            var hit = typed === "" || one.text().toLowerCase().indexOf(typed) > -1;
                            one.toggle(hit);
                            if (hit) { found++; }
                        });
                        none.prop("hidden", found > 0);
                        open();
                        highlight(0);
                    }

                    function choose(one) {
                        if (!one || !one.length) {
                            return;
                        }
                        box.val(one.text());
                        close();
                        getpremade(one.data("id"));
                    }

                    box.on("focus click", filter);
                    box.on("input", filter);
                    box.on("keydown", function (e) {
                        if (e.key === "ArrowDown" || e.key === "ArrowUp") {
                            e.preventDefault();
                            if (list.prop("hidden")) { filter(); return; }
                            highlight(at + (e.key === "ArrowDown" ? 1 : -1));
                        } else if (e.key === "Enter") {
                            /* Only when the list is open with something on it.
                               Otherwise Enter belongs to the reply form this box
                               sits inside, and swallowing it would stop the
                               reply being sent. */
                            if (!list.prop("hidden") && at > -1) {
                                e.preventDefault();
                                choose(visible().eq(at));
                            }
                        } else if (e.key === "Escape") {
                            if (!list.prop("hidden")) {
                                e.stopPropagation();
                                close();
                            }
                        }
                    });

                    /* mousedown, not click: a click fires after the input has
                       already lost focus, and the blur below would have closed
                       the list out from under the pointer. */
                    list.on("mousedown", ".js-ticket-canned-option", function (e) {
                        e.preventDefault();
                        choose(jQuery(this));
                    });

                    jQuery(document).on("mousedown", function (e) {
                        if (!jQuery(e.target).closest(".js-ticket-canned").length) {
                            close();
                        }
                    });
                    box.on("blur", function () {
                        setTimeout(function () {
                            if (!list.is(":hover")) { close(); }
                        }, 120);
                    });
                })();

                /* Saying what happened, without stopping to be told it happened.
                   (Roadmap 6.0-AI-01) The backend thread was changed first and
                   this is the same change: a success is confirmed on the
                   control that did it and takes its own label back two seconds
                   later, a failure goes to a status line that stays until
                   something replaces it. Neither blocks, so neither has to be
                   dismissed. What was here instead was a fixed overlay across
                   the page with an OK button, drawn to report that a line of
                   text had gone into a box the agent was looking at. */
                function jsstSuggestNote(message) {
                    jQuery("#js-ticket-suggest-status").text(message || "");
                }

                function jsstConfirmOn(button, label) {
                    jsstSuggestNote("");
                    if (!button || !button.length) {
                        return;
                    }
                    /* The label the button came with, kept the first time it is
                       borrowed, so a second click while the confirmation shows
                       does not save "Copied" as the thing to go back to. */
                    if (typeof button.data("jsstLabel") === "undefined") {
                        button.data("jsstLabel", button.text());
                    }
                    clearTimeout(button.data("jsstTimer"));
                    button.addClass("is-done").text(label);
                    button.data("jsstTimer", setTimeout(function () {
                        button.removeClass("is-done").text(button.data("jsstLabel"));
                    }, 2000));
                }

                // Function to copy text to clipboard (works in iframes)
                function copyToClipboard(text, button) {
                    const tempTextArea = document.createElement("textarea");
                    tempTextArea.value = text;
                    document.body.appendChild(tempTextArea);
                    tempTextArea.select();
                    try {
                        const successful = document.execCommand("copy");
                        console.log(successful);
                        if(successful) {
                            jsstConfirmOn(button, "'.esc_js(__("Copied", "js-support-ticket")).'");
                        } else {
                            jsstSuggestNote("'.esc_js(__("Nothing was copied. Select the text and copy it yourself.", "js-support-ticket")).'");
                        }
                    } catch (err) {
                        jsstSuggestNote("'.esc_js(__("Nothing was copied. Select the text and copy it yourself.", "js-support-ticket")).'");
                    }
                    document.body.removeChild(tempTextArea);
                }

                // Function to append text to reply area
                function appendToReplyArea(textToAppend, button) {
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
                    jsstConfirmOn(button, "'.esc_js(__("Added", "js-support-ticket")).'");
                }

                // Event listener for Replies Filter dropdown
                jQuery("#js-ticket-replies-filter").on("change", function() {
                    const selectedFilter = jQuery(this).val();
                    const activeTicketItem = matchingTicketsList.find(".js-ticket-list-item.active");
                    
                    if (!activeTicketItem.length) {
                        jsstSuggestNote("'.esc_js(__("Choose a ticket first.", "js-support-ticket")).'");
                        return;
                    }
                    
                    const ticketId = activeTicketItem.data("ticket-id");
                    const ticketTitle = activeTicketItem.find(".js-ticket-title").text();
                    
                    // Show loading message
                    jsReplyShowLoading();
                    
                    // Fetch replies based on filter and ticket ID
                    jQuery.post(ajaxurl, {
                        action: "jsticket_ajax",
                        jstmod: "'.$jsst_jstreplymod.'",
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
                            jsstSuggestNote(data.message || "'.esc_js(__("Those replies could not be read.", "js-support-ticket")).'");
                        }
                    }).fail(function() {
                        jsReplyHideLoading();
                        jsstSuggestNote("'.esc_js(__("Those replies could not be read. Try again.", "js-support-ticket")).'");
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
                        jstmod: "'.$jsst_jstreplymod.'",
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
                            jsstSuggestNote(data.message || "'.esc_js(__("Those replies could not be read.", "js-support-ticket")).'");
                        }
                    }).fail(function() {
                        jsReplyHideLoading();
                        jsstSuggestNote("'.esc_js(__("Those replies could not be read. Try again.", "js-support-ticket")).'");
                    });
                });

                /* The per-reply toggle, same as the admin thread. Two states, so it
                   posts 0 or 2 and swaps its own label in place. Delegated,
                   because the thread is re-rendered by ajax. */
                jQuery(document).on("click", ".js-ticket-ai-example", function(e) {
                    e.preventDefault();
                    var btn = jQuery(this);
                    var wasOff = btn.hasClass("js-ticket-ai-example-off");
                    btn.toggleClass("js-ticket-ai-example-off", !wasOff)
                       .attr("aria-checked", wasOff ? "true" : "false")
                       .find("span").text(wasOff
                           ? "'.esc_js(__('Used by AI', 'js-support-ticket')).'"
                           : "'.esc_js(__('Not used by AI', 'js-support-ticket')).'");
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "reply",
                        task: "markedAsAiPoweredReply", status: (wasOff ? 0 : 2),
                        id: btn.data("id"), type: "reply",
                        "_wpnonce":"'.esc_attr(wp_create_nonce("ai-powered-reply")).'"});
                });

                /* The ticket-level mode, now a select. One change event instead
                   of three click handlers, and the value posted is the option
                   the agent actually read rather than a data attribute on a
                   button. (Roadmap 6.0-AI-01) */
                jQuery(document).on("change", ".js-ticket-ai-mode-select", function() {
                    var sel = jQuery(this);
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "reply",
                        task: "markedAsAiPoweredReply", status: parseInt(sel.val(), 10),
                        id: sel.data("id"), type: sel.data("type"),
                        "_wpnonce":"'.esc_attr(wp_create_nonce("ai-powered-reply")).'"});
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
                    /* The answers, in one step. (Roadmap 6.0-AI-01) This asked
                       for matching *tickets* and made the agent pick one,
                       read its replies in a second panel, then come back -
                       three steps to reach the sentence they wanted, the first
                       of them a guess made from a reference number. The
                       backend thread stopped doing that; this is the same
                       endpoint and the same list, so the two workspaces answer
                       the same question the same way. (Roadmap 4.5-FE-01) */
                    jQuery.post(ajaxurl, {action: "jsticket_ajax", ticketSubject: ticketSubject, ticketId: ticketId, filter: selectedFilter, jstmod: "'.$jsst_jstmod.'", task: "getAiSuggestedReplies", "_wpnonce":"'. esc_attr(wp_create_nonce("check-smart-reply")).'"}, function (data) {
                        if(data) {
                            displaySuggestedReplies(data);
                        } else {
                            /* This branch never cleared the spinner: the OK
                               button on the modal did, so the panel sat
                               spinning until somebody dismissed the message
                               about it. */
                            jsReplyHideLoading();
                            jsstSuggestNote("'.esc_js(__("No suggestions could be fetched. Try again.", "js-support-ticket")).'");
                        }
                    });
                }

                /* One list, of answers, drawn exactly as the backend thread
                   draws it. (Roadmap 6.0-AI-01) */
                function displaySuggestedReplies(suggestions) {
                    if (typeof suggestions === "string") {
                        try { suggestions = JSON.parse(suggestions); } catch (e) { suggestions = []; }
                    }
                    if (!Array.isArray(suggestions)) { suggestions = []; }

                    matchingTicketsList.empty();
                    selectedTicketRepliesSection.addClass("js-ticket-hidden");
                    jQuery(".js-ticket-container").show();
                    /* The ticket filter only means anything when the answers
                       came from tickets. With the corpus answering it was
                       still on screen saying "All Tickets" above a passage out
                       of the knowledge base. */
                    var fromTickets = suggestions.length ? (suggestions[0].from !== "corpus") : true;
                    jQuery("#js-ticket-tickets-filter").closest(".js-ticket-filter-group").toggle(fromTickets);

                    if (suggestions.length === 0) {
                        matchingTicketsList.html("<p class=\"js-ticket-id\">'.esc_js(__("Nothing similar has been answered here yet.", "js-support-ticket")).'</p>");
                        jsReplyHideLoading();
                        matchingTicketsSection.removeClass("js-ticket-hidden");
                        return;
                    }

                    jQuery.each(suggestions, function (i, s) {
                        /* Where it came from, in words. The retriever hands
                           back source_type ("kb") and ref ("KB-1"); neither
                           belongs in front of somebody choosing a sentence to
                           send. The source is named, the document is named,
                           and the document links to itself where it has an
                           address, so it can be read in full before it is
                           trusted. */
                        var when = s.created ? new Date(String(s.created).replace(" ", "T")).toLocaleDateString() : "";
                        var origin = s.source || "";
                        if (s.reference) { origin += " #" + s.reference; }
                        if (when) { origin += (origin ? " \u00b7 " : "") + when; }
                        /* Deliberately NOT js-ticket-list-item: that class
                           carries the old click handler - pick a ticket, fetch
                           its replies - and a suggestion is not a ticket. */
                        var item = jQuery("<li></li>").addClass("js-ticket-suggestion");
                        var body = jQuery("<div></div>").addClass("js-ticket-suggestion-body").text(s.plain);
                        var from = jQuery("<div></div>").addClass("js-ticket-suggestion-meta");
                        from.append(jQuery("<span></span>").addClass("js-ticket-suggestion-origin").text(origin));
                        var titled = jQuery("<span></span>").addClass("js-ticket-suggestion-from");
                        if (s.url) {
                            titled.append(jQuery("<a></a>").attr({href: s.url, target: "_blank", rel: "noopener"}).text(s.subject));
                        } else {
                            titled.text(s.subject);
                        }
                        from.append(titled);
                        var acts = jQuery("<div></div>").addClass("js-ticket-suggestion-actions");
                        var use = jQuery("<button></button>").attr("type", "button")
                            .addClass("js-ticket-reply-action-btn js-ticket-suggestion-use")
                            .text("'.esc_js(__("Use this", "js-support-ticket")).'");
                        var cop = jQuery("<button></button>").attr("type", "button")
                            .addClass("js-ticket-reply-action-btn js-ticket-suggestion-copy")
                            .text("'.esc_js(__("Copy", "js-support-ticket")).'");
                        use.on("click", function (e) { e.preventDefault(); appendToReplyArea(s.text, use); });
                        cop.on("click", function (e) { e.preventDefault(); copyToClipboard(s.plain, cop); });
                        acts.append(use).append(cop);
                        item.append(body).append(from).append(acts);
                        matchingTicketsList.append(item);
                    });
                    jsReplyHideLoading();
                    matchingTicketsSection.removeClass("js-ticket-hidden");
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
                            .html(`<p class="js-ticket-id">'.__("Ticket ID", "js-support-ticket").': '.'`+ticket.ticketid+`</p><p class="js-ticket-title">`+ticket.text+`</p><p class="js-ticket-id">`+ticket.message+`</p>`);
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

                    jQuery("#js-ticket-selected-ticket-replies-title").text(`'.__("Replies for:", "js-support-ticket").' `+ticket.text);
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
                                        <span class="js-ticket-reply-id">'.__("Reply By", "js-support-ticket").': `+escapeHtml(replyName)+`</span>
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
        $jsst_jssupportticket_js .="
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
                if(JSSTincluder::getObjectClass('user')->isguest() && isset(jssupportticket::$jsst_data[0]->id)){
                    $jsst_jssupportticket_js .='
                    params.email = "'. esc_attr(jssupportticket::$jsst_data[0]->email) .'";
                    params.ticketrandomid = "'. esc_attr(jssupportticket::$jsst_data[0]->ticketid) .'";
                    ';
                }
                $jsst_jssupportticket_js .="
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
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
        <div id="usercredentailspopup" style="display: none;">
            <div class="js-ticket-usercredentails-header">
                <?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?><span class="close-credentails"></span>
            </div>
            <div class="js-ticket-usercredentails-wrp" style="display: none;">
                <div class="js-ticket-usercredentails-credentails-wrp">
                </div>
                <?php
                    /* A closed ticket says so, rather than showing nothing.
                       (Roadmap 5.5-SEC-01)

                       Closing a ticket destroys its credentials - closeTicket()
                       calls deleteCredentialsOnCloseTicket(), which blanks the
                       encrypted data and marks the row - so there is deliberately
                       no Add button here afterwards. But an agent reaches this
                       popup on a closed ticket, because the button that opens it
                       is gated on the View Credentials permission and not on the
                       status: they are meant to still be able to read "these were
                       removed when the ticket closed".

                       On a ticket that never had any, that left an empty box with
                       no button and nothing said - which reads as a broken popup
                       rather than as a rule. Same argument getPrivateCredentials()
                       makes about refusing to leave a gap where a credential was:
                       say it in place of the control instead of removing both. */
                    if(in_array('privatecredentials',jssupportticket::$_active_addons)
                            && (jssupportticket::$jsst_data[0]->status == 5 || jssupportticket::$jsst_data[0]->status == 6)){ ?>
                        <div class="js-ticket-usercredentail-data-add-new-button-wrap jsst-credential-closed">
                            <?php echo esc_html(__('This ticket is closed. Credentials are removed when a ticket closes, and cannot be added to a closed ticket.', 'js-support-ticket')); ?>
                        </div><?php
                    }
                    if(in_array('privatecredentials',jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 ){
                        $jsst_credential_add_permission = false;
                        if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                            $jsst_credential_add_permission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Add Credentials');
                        }elseif(current_user_can('manage_options')){
                            $jsst_credential_add_permission = true;
                        }elseif (JSSTincluder::getObjectClass('user')->uid() != 0) {
                            if(JSSTincluder::getObjectClass('user')->uid() == jssupportticket::$jsst_data[0]->uid){
                                $jsst_credential_add_permission = true;
                            }
                        }elseif(JSSTincluder::getObjectClass('user')->uid() == 0){
                            $jsst_credential_add_permission = true;
                        }
                        if($jsst_credential_add_permission){ ?>
                            <div class="js-ticket-usercredentail-data-add-new-button-wrap" >
                                <?php $jsst_nonce = wp_create_nonce('get-form-for-privte-credentials-'.jssupportticket::$jsst_data[0]->id); ?>
                                <button class="js-ticket-usercredentail-data-add-new-button" onclick="addEditCredentail('<?php echo esc_js($jsst_nonce);?>',<?php echo esc_js(jssupportticket::$jsst_data[0]->id);?>,<?php echo esc_js(JSSTincluder::getObjectClass('user')->uid());?>);" >
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
                $jsst_field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, jssupportticket::$jsst_data[0]->multiformid);
                if ((!empty(jssupportticket::$jsst_data[5]))) {
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
                                <?php foreach (jssupportticket::$jsst_data[5] AS $jsst_history) { ?>
                                  <tr>
                                    <td class="js-ticket-textalign-center"><?php echo esc_html(date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_history->datetime))); ?></td>
                                    <td class="js-ticket-textalign-center"><?php echo esc_html(date_i18n('H:i:s', jssupportticketphplib::JSST_strtotime($jsst_history->datetime))); ?></td>
                                    <?php
                                        if (is_super_admin($jsst_history->uid)) {
                                            $jsst_message = 'admin';
                                        } elseif ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff($jsst_history->uid)) {
                                            $jsst_message = 'agent';
                                        } else {
                                            $jsst_message = 'member';
                                        }
                                        ?>
                                    <td class=""><?php 
                                        if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){ //agent
                                            echo wp_kses_post($jsst_history->message); 
                                        }else{
                                            if($jsst_message == 'member'){ // message by the user, so show full message to user
                                                echo wp_kses_post($jsst_history->message); 
                                            }else{
                                                if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) { 
                                                    $jsst_historymessage = $jsst_history->message;
                                                    echo wp_kses_post(jssupportticketphplib::JSST_preg_replace("/\([^)]+\)/","( ".__("Agent", "js-support-ticket")." )",$jsst_historymessage));
                                                }else{
                                                    echo wp_kses_post($jsst_history->message); 
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
        $jsst_jssupportticket_js ="
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
                /* The search terms go with the page number; see the same
                   function in admin_ticketdetail.php for what leaving them out
                   did. */
                var name = jQuery('input#name').val() || '';
                var email = jQuery('input#email').val() || '';
                jQuery.post(ajaxurl, {action: 'jsticket_ajax',jstmod: 'mergeticket', task: 'getTicketsForMerging', name: name, email: email, ticketid:ticketid,ticketlimit:pagenum, '_wpnonce': nonce}, function (data) {
                    if(data){
                        data=jQuery.parseJSON(data);
                            jQuery('div#popup-record-data').html('');
                            jQuery('div#popup-record-data').html(jsstDecodeHTML(data['data']));
                    }
                });
            }
        ";
        wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
        ?>
        <div id="black_wrapper_ai_reply" style="display:none;"></div>
        <div id="js_ai_reply_loading">
            <img alt = "<?php echo esc_attr(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
        </div>
        <span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
        <span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
        <div class="jsst-popup-background" style="display:none" ></div>
        <div class="internalnote-popup-background" style="display:none" ></div>
        <div id="popup-record-data" style="display:flex;flex-wrap:wrap; width:100%;"></div>
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
                        <?php echo esc_html(__('Reason For Editing The Timer', 'js-support-ticket')); ?>
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
            <form id="jsst-reply-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'saveeditedreply')),"save-edited-reply-".jssupportticket::$jsst_data[0]->id)); ?>" >
                <div class="js-ticket-edit-form-wrp">
                    <div class="js-ticket-form-field-wrp">
                        <?php wp_editor('', 'jsticket_replytext', array('media_buttons' => false,'editor_height' => 200, 'textarea_rows' => 20,)); ?>
                    </div>
                </div>
                <div class="js-ticket-priorty-btn-wrp">
                    <?php echo wp_kses(JSSTformfield::submitbutton('ppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save')), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::button('canceleeeee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('action', 'reply_saveeditedreply'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                <form id="jsst-time-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'saveeditedtime')),"save-edited-time-".jssupportticket::$jsst_data[0]->id)); ?>" >
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
                            <div class="js-form-title"><?php echo esc_html(__('Resolve Conflict', 'js-support-ticket')); ?></div>
                            <div class="js-form-value"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $jsst_yesno, ''), JSST_ALLOWED_TAGS); ?></div>
                        </div>
                        <div class="js-ticket-priorty-btn-wrp">
                            <?php echo wp_kses(JSSTformfield::submitbutton('pppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save','onclick' => 'updateTimerFromPopup();')), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::button('canceleeeeee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                        </div>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'reply_saveeditedtime'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('time-confilct',''), JSST_ALLOWED_TAGS); ?>
                </form>
                <form id="jsst-note-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'note','task'=>'saveeditedtime')),"save-edited-time-".jssupportticket::$jsst_data[0]->id)); ?>" >
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
                        <div class="js-col-md-12 js-form-title"><?php echo esc_html(__('Resolve Conflict', 'js-support-ticket')); ?></div>
                        <div class="js-col-md-12 js-form-value"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $jsst_yesno, ''), JSST_ALLOWED_TAGS); ?></div>
                    </div>
                    <div class="js-col-md-12 js-form-button-wrapper">
                        <?php echo wp_kses(JSSTformfield::submitbutton('ppppok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button')),JSST_ALLOWED_TAGS); ?>
                        <?php echo wp_kses(JSSTformfield::button('cancele', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'button', 'onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                    </div>
                    <?php echo wp_kses(JSSTformfield::hidden('action', 'note_saveeditedtime'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('note-noteid', ''), JSST_ALLOWED_TAGS); ?>
                    <?php echo wp_kses(JSSTformfield::hidden('note-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
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
        if($jsst_printflag == false && jssupportticket::$jsst_data['user_staff'] && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6){
            ?>

            <?php if(!empty($jsst_field_array['department']) && JSSTmergedaddon::featureEnabled('actions')){ 
                if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer')){
                ?>
                <div id="popupfordepartmenttransfer" style="display:none" >
                    <div class="jsst-popup-header" >
                        <div class="popup-header-text" >
                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ". esc_html(__('Transfer', 'js-support-ticket')); ?>
                        </div>
                        <div class="popup-header-close-img" >
                        </div>
                    </div>
                    <div>
                        <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'transferdepartment')),"transfer-department-".jssupportticket::$jsst_data[0]->id)); ?>" enctype="multipart/form-data">
                            <div class="js-ticket-premade-msg-wrp"><!-- Select Department Wrapper -->
                                <div class="js-ticket-premade-field-title"><?php echo esc_html(__('Select', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?></div>
                                <div class="js-ticket-premade-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), isset(jssupportticket::$jsst_data[0]->departmentid) ? jssupportticket::$jsst_data[0]->departmentid : '', esc_html(__('Select', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])), array('class' => 'js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>

                                </div>
                            </div>
                            <?php if(JSSTmergedaddon::featureEnabled('note')){ ?>
                                <div class="js-ticket-text-editor-wrp">
                                    <div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Reason For', 'js-support-ticket')) ." ". esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ". esc_html(__('Transfer', 'js-support-ticket')); ?> <span class="jsst-optional">(<?php echo esc_html(__('optional', 'js-support-ticket')); ?>)</span></div>
                                    <div class="js-ticket-text-editor-field"><?php wp_editor('', 'departmenttranfernote', array('media_buttons' => false)); ?></div>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-reply-form-button-wrp">
                                <?php echo wp_kses(JSSTformfield::submitbutton('departmenttransferbutton', esc_html(__('Transfer', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
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
                        <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'assigntickettostaff')),"assign-ticket-to-staff-".jssupportticket::$jsst_data[0]->id)); ?>" enctype="multipart/form-data">
                            <div class="js-ticket-premade-msg-wrp"><!-- Select Department Wrapper -->
                                <div class="js-ticket-premade-field-title"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></div>
                                <div class="js-ticket-premade-field-wrp">
                                    <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getStaffForCombobox(), jssupportticket::$jsst_data[0]->staffid, esc_html(__('Select Agent', 'js-support-ticket')), array('class' => 'inputbox js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <?php if(JSSTmergedaddon::featureEnabled('note')){ ?>
                                <div class="js-ticket-text-editor-wrp">
                                    <div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Assigning Note', 'js-support-ticket')); ?> <span class="jsst-optional">(<?php echo esc_html(__('optional', 'js-support-ticket')); ?>)</span></div>
                                    <div class="js-ticket-text-editor-field"><?php wp_editor('', 'assignnote', array('media_buttons' => false)); ?></div>
                                </div>
                            <?php } ?>
                            <div class="js-ticket-reply-form-button-wrp">
                                <?php echo wp_kses(JSSTformfield::submitbutton('assigntostaff', esc_html(__('Assign', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_assigntickettostaff'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div> <!-- end of assigntostaff div -->
                </div>
                <?php } ?>
            <?php } ?>

            <?php if(JSSTmergedaddon::featureEnabled('note')){ ?>
            <div id="popupforinternalnote" style="display:none" >
                <div class="jsst-popup-header" >
                    <div class="popup-header-text" >
                        <?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?>
                    </div>
                    <div class="internalnote-popup-header-close-img" >
                    </div>
                </div>
                <div>  <!--  postinternalnote Area   -->
                    <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'note','task'=>'savenote')),"save-note-".jssupportticket::$jsst_data[0]->id)); ?>" enctype="multipart/form-data">
                        <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                            <div class="jsst-ticket-detail-timer-wrapper"> <!-- Top Timer Section -->
                                <div class="timer-left" >
                                <?php echo esc_html(__('Time Track','js-support-ticket')); ?>
                                </div>
                                <div class="timer-right" >
                                    <div class="timer-total-time" >
                                        <?php
                                            $jsst_hours = floor(jssupportticket::$jsst_data['time_taken'] / 3600);
                                            $jsst_mins = floor(jssupportticket::$jsst_data['time_taken'] / 60);
                                            $jsst_mins = floor($jsst_mins % 60);
                                            $jsst_secs = floor(jssupportticket::$jsst_data['time_taken'] % 60);
                                            echo esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                        ?>
                                    </div>
                                    <div class="timer" >
                                        00:00:00
                                    </div>
                                    <div class="timer-buttons" >
                                        <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time')){ ?>
                                            <span class="timer-button" onclick="showEditTimerPopup(this)" >
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/timer-edit.png"/>
                                            </span>
                                        <?php } ?>
                                        <span class="timer-button cls_1" onclick="changeTimerStatus(1, this)" >
                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play.png"/>
                                        </span>
                                        <span class="timer-button cls_2" onclick="changeTimerStatus(2, this)" >
                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/pause.png"/>
                                        </span>
                                        <span class="timer-button cls_3" onclick="changeTimerStatus(3, this)" >
                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/stop.png"/>
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
                                <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close On Reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <div class="js-ticket-reply-form-button-wrp">
                            <?php echo wp_kses(JSSTformfield::submitbutton('postinternalnote', esc_html(__('Post Internal Note', 'js-support-ticket')), array('class' => 'js-ticket-save-button', 'onclick' => "return checktinymcebyid('internalnote');")), JSST_ALLOWED_TAGS); ?>
                        </div>

                        <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
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
            jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                $jsst_cur_uid = JSSTincluder::getObjectClass('user')->uid();
                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff']) {
                    $jsst_link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'actionticket')),"action-ticket-".jssupportticket::$jsst_data[0]->id);
                } else {
                    $jsst_link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply-".jssupportticket::$jsst_data[0]->id);
                }
                ?>
                <div class="js-ticket-ticket-detail-wrapper">
                   <?php if($jsst_printflag != true){?>
                        <form method="post" action="<?php echo esc_url($jsst_link); ?>" id="adminTicketform" enctype="multipart/form-data">
                    <?php } ?>
                    <!-- Ticket Detail Left -->
                    <div class="js-tkt-det-left">
                        <div class="js-tkt-det-cnt js-tkt-det-info-wrp"><!-- Ticket Detail Info Wrp -->
                            <div class="js-tkt-det-user"><!-- Ticket Detail Box -->
                                <div class="js-tkt-det-user-image"><!-- Left Side Image -->
                                    <?php /* if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->staffphotophoto) { ?>
                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$jsst_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                    <?php } else { */
                                        echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                    // } ?>
                                </div>
                                <div class="js-tkt-det-user-cnt"><!-- Right Side -->
                                    <?php
                                    if(!empty($jsst_field_array['fullname'])) { ?>
                                        <div class="js-tkt-det-user-data name">
                                            <?php echo esc_html(jssupportticket::$jsst_data[0]->name); ?>
                                        </div>
                                        <?php
                                    } ?>
                                    <div class="js-tkt-det-user-data subject">
                                       <?php echo esc_html(jssupportticket::$jsst_data[0]->subject); ?>
                                    </div>
                                    <?php
                                    if(!empty($jsst_field_array['email'])) { ?>
                                        <div class="js-tkt-det-user-data email">
                                            <?php echo esc_html(jssupportticket::$jsst_data[0]->email); ?>
                                        </div>
                                        <?php 
                                    }
                                    if(!empty($jsst_field_array['phone'])) { ?>
                                        <div class="js-tkt-det-user-data number">
                                            <?php echo esc_html(jssupportticket::$jsst_data[0]->phone); ?>
                                        </div>
                                        <?php 
                                    } ?>
                                </div>
                            </div>
                            <?php
                            if(isset(jssupportticket::$jsst_data['nticket'])){ ?>
                                <div class="js-tkt-det-other-tkt"><!-- Ticket Detail View Btn -->
                                    <?php
                                    if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                                        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffmyticket','uid'=>jssupportticket::$jsst_data[0]->uid));
                                    }else{
                                        $jsst_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'myticket'));
                                    }
                                    ?>
                                    <a class="js-tkt-det-other-tkt-btn" href="<?php echo esc_url($jsst_url); ?>">
                                        <?php
                                        if(in_array('agent', jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff']){
                                            echo esc_html(__('View all','js-support-ticket')).' '.esc_html(jssupportticket::$jsst_data['nticket']).' '. esc_html(__('tickets by','js-support-ticket')).' '.esc_html(jssupportticket::$jsst_data[0]->name);
                                        }else{
                                            echo esc_html(__('View all','js-support-ticket')).' '.esc_html(jssupportticket::$jsst_data['nticket']).' '. esc_html(__('tickets','js-support-ticket'));
                                        }
                                        ?>
                                    </a>
                                </div>
                                <?php
                            } ?>
                            <!-- Ticket Detail Message -->
                            <!-- Removed to avoid duplicate display; shown below in the ticket thread. -->
                            <?php /* echo wp_kses_post(jssupportticket::$jsst_data[0]->message); */ ?>

                            <?php
                            jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                            $jsst_customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, null, jssupportticket::$jsst_data[0]->multiformid);
                            if (!empty($jsst_customfields)){ ?>
                                <div class="js-tkt-det-tkt-msg">
                                    <div class="js-tkt-det-tkt-custm-flds">
                                        <?php
                                        foreach ($jsst_customfields as $jsst_field) {
                                            $jsst_ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($jsst_field,2, jssupportticket::$jsst_data[0]->params);
                                            ?>
                                            <div class="js-tkt-det-info-data">
                                                <div class="js-tkt-det-info-tit">
                                                    <?php echo wp_kses($jsst_ret['title'], JSST_ALLOWED_TAGS).': '; ?>
                                                </div>
                                                <div class="js-tkt-det-info-val">
                                                    <?php echo wp_kses($jsst_ret['value'], JSST_ALLOWED_TAGS); ?>
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
                                <?php if ($jsst_printflag == false){
                                        $jsst_printpermission = false;
                                        $jsst_mergepermission = false;
                                    if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff'] && jssupportticket::$jsst_data[0]->status != 6 ) {
                                        $jsst_printpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Print Ticket');
                                        // Same answer the merge action itself uses, so the button
                                        // cannot offer what the action will refuse. (Roadmap 4.0-CORE-04)
                                        $jsst_mergepermission = JSSTroles::canMergeTickets();

                                        ?>
                                        <a class="js-tkt-det-actn-btn" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','jstlay'=>'staffaddticket','jssupportticketid'=>jssupportticket::$jsst_data[0]->id))); ?>" title="<?php echo esc_attr(__('Edit Ticket', 'js-support-ticket')); ?>">
                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit.png" title="<?php echo esc_attr(__('Edit', 'js-support-ticket')); ?>" />
                                            <span><?php echo esc_html(__('Edit', 'js-support-ticket')); ?></span>
                                        </a>
                                        <?php if (jssupportticket::$jsst_data[0]->status != 5) { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(2);" title="<?php echo esc_attr(__('Close Ticket', 'js-support-ticket')); ?>">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/close.png" title="<?php echo esc_attr(__('Close', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Close', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } else { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(3);" title="<?php echo esc_attr(__('Reopen Ticket', 'js-support-ticket')); ?>">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" title="<?php echo esc_attr(__('Reopen', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Reopen', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } ?>
                                        <?php if(JSSTmergedaddon::featureEnabled('tickethistory')){ ?>
                                            <a class="js-tkt-det-actn-btn" href="#" id="showhistory">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" title="<?php echo esc_attr(__('History', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php }?>
                                        <?php if(in_array('mergeticket', jssupportticket::$_active_addons) && $jsst_mergepermission) {
                                            if (jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6) {
                                                $jsst_nonce = wp_create_nonce("get-tickets-for-merging-".jssupportticket::$jsst_data[0]->id) ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="mergeticket" onclick="return showPopupAndFillValues(<?php echo esc_js(jssupportticket::$jsst_data[0]->id);?>,4, '<?php echo esc_js($jsst_nonce);?>')">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/merge-ticket.png" title="<?php echo esc_attr(__('Merge', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Merge', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php }/*Merge Ticket*/
                                        } ?>
                                        <?php if(JSSTmergedaddon::featureEnabled('actions')){ ?>
                                            <?php if($jsst_printpermission && jssupportticket::$jsst_data[0]->status != 6) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_attr(__('Print', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                                </a>
                                                <!-- Print Ticket -->
                                            <?php } ?>
                                        <?php } ?>
                                        <?php $jsst_deletepermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Delete Ticket');
                                        if($jsst_deletepermission) { ?>
                                            <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$jsst_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket-'.jssupportticket::$jsst_data[0]->id)); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/delete.png" title= "<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></span>
                                            </a>
                                            <?php
                                        }
                                        $jsst_credentialpermission = JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('View Credentials');
                                        if(in_array('privatecredentials',jssupportticket::$_active_addons) && $jsst_credentialpermission){ ?>
                                            <?php $jsst_nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$jsst_data[0]->id) ?>
                                            <a class="js-tkt-det-actn-btn" href="javascript:void(0);" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$jsst_data[0]->id); ?>, '<?php echo esc_js($jsst_nonce); ?>')">
                                                <?php $jsst_query = jssupportticket::$_db->prepare("SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = %d", jssupportticket::$jsst_data[0]->id);
                                                $jsst_cred_count = jssupportticket::$_db->get_var($jsst_query);
                                                if ($jsst_cred_count>0) {
                                                    $jsst_img_name = 'private-credentials-exist.png';
                                                } else {
                                                    $jsst_img_name = 'private-credentials.png';
                                                } ?>
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/<?php echo esc_attr($jsst_img_name);?>" title= "<?php echo esc_attr(__('Private Credentials', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></span>
                                            </a>
                                            <?php
                                        }
                                    } else { ?>
                                            <?php if (jssupportticket::$jsst_data[0]->status != 6 && !$jsst_companyreadonly) { ?>
                                                <?php if (jssupportticket::$jsst_data[0]->status != 5) { ?>
                                                    <a onclick="return confirm('<?php echo esc_js(__('Are you sure to close this ticket', 'js-support-ticket')); ?>');" class="js-tkt-det-actn-btn" href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'closeticket','action'=>'jstask','ticketid'=> jssupportticket::$jsst_data[0]->id ,'jsstpageid'=>get_the_ID())),"close-ticket-".jssupportticket::$jsst_data[0]->id)); ?>">
                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/close.png" title="<?php echo esc_attr(__('Close', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Close', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php if(JSSTmergedaddon::featureEnabled('tickethistory')){ ?>
                                                        <a class="js-tkt-det-actn-btn js-margin-right" href="#" id="showhistory">
                                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" title="<?php echo esc_attr(__('Ticket History', 'js-support-ticket')); ?>" />
                                                            <span><?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?></span>
                                                        </a>
                                                    <?php } ?>
                                                <?php } else {
                                                        if (JSSTincluder::getJSModel('ticket')->checkCanReopenTicket(jssupportticket::$jsst_data[0]->id)) {
                                                            $jsst_link = wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'reopenticket','action'=>'jstask','ticketid'=> jssupportticket::$jsst_data[0]->id,'jsstpageid'=>get_the_ID())),"reopen-ticket-".jssupportticket::$jsst_data[0]->id); ?>
                                                            <a class="js-tkt-det-actn-btn" href="<?php echo esc_url($jsst_link); ?>" title="<?php echo esc_attr(__('Reopen Ticket', 'js-support-ticket')); ?>">
                                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" title="<?php echo esc_attr(__('Reopen', 'js-support-ticket')); ?>" />
                                                                <span><?php echo esc_html(__('Reopen', 'js-support-ticket')); ?></span>
                                                            </a>
                                                        <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (jssupportticket::$_config['show_ticket_delete_button'] == 1 && !$jsst_companyreadonly) { ?>
                                                <a class="js-tkt-det-actn-btn" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');"  href="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'deleteticket','action'=>'jstask','ticketid'=> jssupportticket::$jsst_data[0]->id ,'jsstpageid'=>get_the_ID())),'delete-ticket-'.jssupportticket::$jsst_data[0]->id)); ?>" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/delete.png" title= "<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Delete', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                            <?php
                                            if(jssupportticket::$_config['print_ticket_user'] == 1 ){
                                                if(JSSTmergedaddon::featureEnabled('actions')){ ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_attr(__('Print', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php
                                                }
                                            }
                                            if(in_array('privatecredentials',jssupportticket::$_active_addons) && !$jsst_companyreadonly && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6){ ?>
                                                <?php $jsst_nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$jsst_data[0]->id) ?>
                                                <a class="js-tkt-det-actn-btn" href="javascript:void(0);" id="private-credentials-button" onclick="getCredentails(<?php echo esc_js(jssupportticket::$jsst_data[0]->id); ?>, '<?php echo esc_js($jsst_nonce); ?>')">
                                                    <?php $jsst_query = jssupportticket::$_db->prepare("SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = %d", jssupportticket::$jsst_data[0]->id);
                                                    $jsst_cred_count = jssupportticket::$_db->get_var($jsst_query);
                                                    if ($jsst_cred_count>0) {
                                                        $jsst_img_name = 'private-credentials-exist.png';
                                                    } else {
                                                        $jsst_img_name = 'private-credentials.png';
                                                    } ?>
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/<?php echo esc_attr($jsst_img_name);?>" title= "<?php echo esc_attr(__('Private Credentials', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></span>
                                                </a>
                                                <?php
                                            }
                                        } ?>
                                        <?php if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff'] && jssupportticket::$jsst_data[0]->status != 6) { ?>
                                        <?php if (JSSTmergedaddon::featureEnabled('actions')) { ?>
                                            <?php if (jssupportticket::$jsst_data[0]->lock == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(5);" title="<?php echo esc_attr(__('Unlock Ticket', 'js-support-ticket')); ?>">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/unlock.png" title="<?php echo esc_attr(__('Unlock', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Unlock', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(4);" title="<?php echo esc_attr(__('Lock Ticket', 'js-support-ticket')); ?>">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/lock.png" title="<?php echo esc_attr(__('Lock', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Lock', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if(JSSTmergedaddon::featureEnabled('banemail')){ ?>
                                            <?php
                                                $jsst_manageoptions = current_user_can('manage_options');
                                                /* Asked once and kept: the combined "Ban Email And
                                                   Close Ticket" button below needs the same answer,
                                                   and this is a query per call. */
                                                $jsst_emailbanned = JSSTincluder::getJSModel('banemail')->isEmailBan(jssupportticket::$jsst_data[0]->email);
                                                if ($jsst_emailbanned) {
                                                    if ($jsst_manageoptions || JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Unban Email')) { ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(7);">
                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-ban.png" title="<?php echo esc_attr(__('Unban Email', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Unban Email', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php }
                                                } else {
                                                    if ($jsst_manageoptions || JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket')) { ?>
                                                    <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(6);">
                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban.png" title="<?php echo esc_attr(__('Ban Email', 'js-support-ticket')); ?>" />
                                                        <span><?php echo esc_html(__('Ban Email', 'js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php }
                                                } ?>
                                        <?php } ?>
                                        <?php if(in_array('overdue', jssupportticket::$_active_addons)){ ?>
                                            <?php if (jssupportticket::$jsst_data[0]->isoverdue == 1) { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(11);">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-over-due.png" title="<?php echo esc_attr(__('Unmark Overdue', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Unmark Overdue', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(8);">
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/over-due.png" title="<?php echo esc_attr(__('Mark Overdue', 'js-support-ticket')); ?>" />
                                                    <span><?php echo esc_html(__('Mark Overdue', 'js-support-ticket')); ?></span>
                                                </a>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if (JSSTmergedaddon::featureEnabled('actions') && ( current_user_can('manage_options') || JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark In Progress') ) ) { ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(9);">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/in-progress.png'; ?>" title="<?php echo esc_attr(__('Mark In Progress', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Mark In Progress', 'js-support-ticket'));?></span>
                                            </a>
                                        <?php } ?>
                                        <?php /* Only while there is something left for it to do.
                                           This button does two things, and it was offered whenever
                                           the permission was held - so on a ticket that was already
                                           closed with an already-banned sender it sat there
                                           promising to ban an address that is banned and close a
                                           ticket that is closed. Both halves have their own button
                                           in this same bar for the partial cases: Unban/Ban above,
                                           Close/Reopen further up. So there is nothing to reach
                                           only through this one, and hiding it when either half is
                                           already done takes no action away. (5 is the closed
                                           status, as in the Close/Reopen pair above.) */
                                        if(JSSTmergedaddon::featureEnabled('banemail')
                                                && empty($jsst_emailbanned)
                                                && jssupportticket::$jsst_data[0]->status != 5
                                                && ( current_user_can('manage_options') || JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket') ) ){ ?>
                                            <a class="js-tkt-det-actn-btn" href="#" onclick="actionticket(10);">
                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/ban-email-close-ticket.png'; ?>" title="<?php echo esc_attr(__('Ban Email And Close Ticket', 'js-support-ticket')); ?>" />
                                                <span><?php echo esc_html(__('Ban Email And Close Ticket', 'js-support-ticket')); ?></span>
                                            </a>
                                        <?php } ?>
                                <?php } ?>
                                <?php } else { ?>
                                    <a class="js-tkt-det-actn-btn" href="javascript:window.print();">
                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" title= "<?php echo esc_attr(__('Print', 'js-support-ticket')); ?>" />
                                        <span><?php echo esc_html(__('Print', 'js-support-ticket')); ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                        if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff']) {
                            if (JSSTmergedaddon::featureEnabled('note')) {
                                ?>
                                <!-- Ticket Detail Internal Note -->
                                <div class="js-tkt-det-title">
                                    <?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?>
                                </div> <!-- Heading -->
                                <?php
                                foreach (jssupportticket::$jsst_data[6] AS $jsst_note) {
                                    ?>
                                    <div class="js-ticket-detail-box js-ticket-post-reply-box"><!-- Ticket Detail Box -->
                                        <div class="js-ticket-detail-left js-ticket-white-background"><!-- Left Side Image -->
                                            <div class="js-ticket-user-img-wrp">
                                                <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $jsst_note->staffphoto) { ?>
                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $jsst_note->staff_id ,'jsstpageid'=>get_the_ID()))); ?>">
                                                <?php } else { */
                                                    if (isset($jsst_note->userid) && !empty($jsst_note->userid)) {
                                                        echo wp_kses(jsst_get_avatar($jsst_note->userid), JSST_ALLOWED_TAGS);
                                                    } else { ?>
                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-ticket-staff-img" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/ticketmanbig.png'; ?>" />
                                                    <?php } ?>
                                                <?php /* } */ ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-detail-right js-ticket-background"><!-- Right Side Ticket Data -->
                                            <div class="js-ticket-rows-wrapper">
                                                <div class="js-ticket-rows-wrp">
                                                    <div class="js-ticket-field-value name">
                                                        <?php echo !empty($jsst_note->staffname) ? esc_html($jsst_note->staffname) : esc_html($jsst_note->display_name); ?>
                                                    </div>
                                                </div>
                                                <?php if (isset($jsst_note->title) && $jsst_note->title != '') { ?>
                                                    <div class="js-ticket-rows-wrp" >
                                                        <div class="js-ticket-field-value">
                                                            <span class="js-ticket-field-value-t"><?php echo esc_html($jsst_field_array['subject']).': '; ?></span><?php echo esc_html($jsst_note->title); ?></div>
                                                    </div>
                                                <?php } ?>
                                                <div class="js-ticket-rows-wrp" >
                                                    <div class="js-ticket-row">
                                                        <div class="js-ticket-field-value">
                                                           <?php echo wp_kses_post($jsst_note->note); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                                if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                <div class="js-ticket-edit-options-wrp" >
                                                    <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$jsst_data[0]->status != 6){
                                                        $jsst_nonce = wp_create_nonce('get-time-by-note-id-'.$jsst_note->id); ?>
                                                        <a class="js-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_note->id);?>,3, '<?php echo esc_js($jsst_nonce);?>')" >
                                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                            <?php echo esc_html(__('Edit Time','js-support-ticket'));?>
                                                        </a>
                                                    <?php
                                                    }
                                                    $jsst_hours = floor($jsst_note->usertime / 3600);
                                                    $jsst_mins = floor($jsst_note->usertime / 60);
                                                    $jsst_mins = floor($jsst_mins % 60);
                                                    $jsst_secs = floor($jsst_note->usertime % 60);
                                                    $jsst_time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                    ?>
                                                    <span class="js-ticket-thread-time"><?php echo esc_html($jsst_time); ?></span>
                                                </div>
                                                <?php } ?>
                                                    <?php
                                                    if ( $jsst_note->filedeleted == 1 ) { ?>
                                                        <div class="jsst-attachment-purged">
                                                            <svg class="jsst-purged-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                <path d="M6.854 7.146a.5.5 0 1 0-.708.708L7.293 9l-1.147 1.146a.5.5 0 0 0 .708.708L8 9.707l1.146 1.147a.5.5 0 0 0 .708-.708L8.707 9l1.147-1.146a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146z"/>
                                                                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                                            </svg>
                                                            <span>
                                                                <span class="jsst-purged-filename"><?php echo esc_html( $jsst_note->filename ); ?></span>
                                                                <span class="jsst-purged-reason"><?php echo esc_html__( '(Removed automatically to save space)', 'js-support-ticket' ); ?></span>
                                                            </span>
                                                        </div>
                                                        <?php
                                                    } elseif($jsst_note->filesize > 0 && !empty($jsst_note->filename)){ ?>
                                                        <div class="js-ticket-attachments-wrp">
                                                            <div class="js_ticketattachment">
                                                                <span class="js-ticket-download-file-title">
                                                                    <?php echo esc_html($jsst_note->filename); echo '(' . esc_html($jsst_note->filesize / 1024) . ')'; ?>
                                                                </span>
                                                                <a class="js-download-button" target="_blank" href="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'note','task'=>'downloadbyid','action'=>'jstask','id'=> $jsst_note->id, '_wpnonce'=> wp_create_nonce('download-note-attachment-'.$jsst_note->id), 'jsstpageid'=>get_the_ID()))); ?>">
                                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-ticket-download-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>/includes/images/ticket-detail/download.png">
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } ?>
                                            </div>
                                            <div class="js-ticket-time-stamp-wrp">
                                                <span class="js-ticket-ticket-created-date">
                                                    <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_note->created))); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                if(jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6){ ?>
                                    <div class="js-ticket-thread-add-btn">
                                        <a href="#" id="internalnotebtn" class="js-ticket-thread-add-btn-link">
                                            <img alt = "<?php echo esc_attr(__('Post New Internal Note','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png" />
                                            <?php echo esc_html(__("Internal Note",'js-support-ticket')); ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                        }
                        ?>

                        <!-- Ticket Detail Thread -->
                        <div class="js-tkt-det-title thread">
                            <?php echo esc_html(__('Ticket Thread', 'js-support-ticket')); ?>
                        </div> <!-- Heading -->
                        <div class="js-ticket-thread internal-note"><!-- Ticket Detail Box -->
                            <div class="js-ticket-thread-image"><!-- Left Side Image -->
                                <?php /* if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->staffphotophoto) { ?>
                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> jssupportticket::$jsst_data[0]->staffphotoid ,'jsstpageid'=>get_the_ID()))); ?>">
                                <?php } else { */
                                    echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                // } ?>
                            </div>
                            <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
                                <?php
                                    if(!empty($jsst_field_array['fullname'])) { ?>
                                    <div class="js-ticket-thread-data">
                                        <span class="js-ticket-thread-person">
                                            <?php echo esc_html(jssupportticket::$jsst_data[0]->name); ?>
                                        </span>
                                    </div>
                                    <?php 
                                }
                                if(!empty($jsst_field_array['email'])) { ?>
                                    <div class="js-ticket-thread-data">
                                        <span class="js-ticket-thread-email">
                                            <?php echo esc_html(jssupportticket::$jsst_data[0]->email); ?>
                                        </span>
                                    </div>
                                    <?php 
                                } ?>
                                <div class="js-ticket-thread-data note-msg">
                                    <?php echo wp_kses_post(jssupportticket::$jsst_data[0]->message); ?>
                                    <?php
                                    if (!empty(jssupportticket::$jsst_data['ticket_attachment'])) { ?>
                                        <div class="js-ticket-attachments-wrp">
                                            <?php 
                                            $jsst_ticketdata = '';
                                            $jsst_active_count = 0;
                                            foreach (jssupportticket::$jsst_data['ticket_attachment'] AS $jsst_attachment) {
                                                // Check if the file was deleted by the Auto Cleanup Cron
                                                if ( $jsst_attachment->deleted == 1 ) {
                                                    $jsst_ticketdata .= '<div class="jsst-attachment-purged">';
                                                        $jsst_ticketdata .= '<svg class="jsst-purged-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">';
                                                            $jsst_ticketdata .= '<path d="M6.854 7.146a.5.5 0 1 0-.708.708L7.293 9l-1.147 1.146a.5.5 0 0 0 .708.708L8 9.707l1.146 1.147a.5.5 0 0 0 .708-.708L8.707 9l1.147-1.146a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146z"/>';
                                                            $jsst_ticketdata .= '<path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>';
                                                        $jsst_ticketdata .= '</svg>';
                                                        $jsst_ticketdata .= '<span>';
                                                            $jsst_ticketdata .= '<span class="jsst-purged-filename">'. esc_html( $jsst_attachment->filename ) .'</span>';
                                                            $jsst_ticketdata .= '<span class="jsst-purged-reason">'. esc_html__( '(Removed automatically to save space)', 'js-support-ticket' ) .'</span>';
                                                        $jsst_ticketdata .= '</span>';
                                                    $jsst_ticketdata .= '</div>';
                                                } else {
                                                    $jsst_active_count++;
                                                    $jsst_path = jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'downloadbyid','action'=>'jstask','id'=> $jsst_attachment->id ,'jsstpageid'=>get_the_ID()));
                                                    $jsst_data = wp_check_filetype($jsst_attachment->filename);
                                                    $jsst_type = $jsst_data['type'];

                                                    $jsst_ticketdata .= '
                                                    <div class="js_ticketattachment">
                                                        <span class="js_ticketattachment_fname">
                                                            ' . esc_html($jsst_attachment->filename) . '
                                                        </span>
                                                        <a class="js-download-button" target="_blank" href="' . esc_url($jsst_path) . '">'
                                                            . esc_html(__('Download', 'js-support-ticket')).'
                                                        </a>';
                                                        if(jssupportticketphplib::JSST_strpos($jsst_type, "image") !== false) {
                                                            $jsst_ticketdata .= '<a data-gall="gallery-ticket-thread" class="js-download-button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_url(JSSTincluder::getJSModel('attachment')->getAttachmentImage($jsst_attachment->id)) .'"  target="_blank">
                                                            <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                </a>';
                                                        }
                                                        $jsst_ticketdata .= '
                                                    </div>';
                                                }
                                            }
                                            // Only show "Download All" if there is at least one active file
                                            if ( $jsst_active_count > 0 ) {
                                                $jsst_nonce = wp_create_nonce("download-all-".jssupportticket::$jsst_data[0]->id);
                                                $jsst_ticketdata .= '<a class="js-all-download-button" target="_blank" href="' . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'downloadall', 'action'=>'jstask', 'downloadid'=>jssupportticket::$jsst_data[0]->id, '_wpnonce'=>$jsst_nonce , 'jsstpageid'=>get_the_ID()))) . '" >'. esc_html(__('Download All', 'js-support-ticket')) . '</a>';
                                            }
                                            // Echo the entire sanitized string at the end
                                            echo wp_kses($jsst_ticketdata, JSST_ALLOWED_TAGS); 
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="js-ticket-thread-cnt-btm">
                                    <span class="js-ticket-thread-date">
                                         <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->created))); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- User post Reply Section -->
                        <?php if (!empty(jssupportticket::$jsst_data[4]))
                            foreach (jssupportticket::$jsst_data[4] AS $jsst_reply):
                                if ($jsst_cur_uid == $jsst_reply->uid) ?>
                                    <div class="js-ticket-thread"><!-- Ticket Detail Box -->
                                        <div class="js-ticket-thread-image"><!-- Left Side Image -->
                                            <?php /* if (in_array('agent',jssupportticket::$_active_addons) &&  $jsst_reply->staffphoto) { ?>
                                                <img  class="js-ticket-staff-img" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=> $jsst_reply->staffid ,'jsstpageid'=>get_the_ID()))); ?>">
                                            <?php } else { */
                                                echo wp_kses(jsst_get_avatar($jsst_reply->uid, 'js-ticket-staff-img'), JSST_ALLOWED_TAGS);
                                            // } ?>
                                        </div>
                                        <div class="js-ticket-thread-cnt"><!-- Right Side Ticket Data -->
                                            <div class="js-ticket-thread-data">
                                                <?php
                                                if(!empty($jsst_field_array['fullname'])) { ?>
                                                    <span class="js-ticket-thread-person">
                                                        <?php
                                                        if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
                                                            if(jssupportticket::$jsst_data[0]->uid  != $jsst_reply->uid){ //reply by staff, need anonymous
                                                                echo esc_html(jssupportticket::$_config['title']);
                                                            }else{ // reply by user   
                                                                if($jsst_reply->name == ""){
                                                                    // name field value is empty in some old tickets
                                                                    $jsst_replyname = JSSTincluder::getJSModel('reply')->getUserNameFromReplyById($jsst_reply->replyid);
                                                                    echo esc_html($jsst_replyname); 
                                                                }else{
                                                                    echo esc_html($jsst_reply->name); 
                                                                }
                                                            }
                                                        }elseif(jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 2){
                                                            if($jsst_reply->name == ""){
                                                                // name field value is empty in some old tickets
                                                                $jsst_replyname = JSSTincluder::getJSModel('reply')->getUserNameFromReplyById($jsst_reply->replyid);
                                                                echo esc_html($jsst_replyname); 
                                                            }else{
                                                                echo esc_html($jsst_reply->name); 
                                                            }
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php 
                                                } ?>
                                                <?php 
                                                if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                    <?php if($jsst_reply->staffid != 0){
                                                        $jsst_hours = floor($jsst_reply->time / 3600);
                                                        $jsst_mins = floor($jsst_reply->time / 60);
                                                        $jsst_mins = floor($jsst_mins % 60);
                                                        $jsst_secs = floor($jsst_reply->time % 60);
                                                        $jsst_time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                        ?>
                                                        <span class="js-ticket-thread-time"><?php echo esc_html($jsst_time); ?></span>
                                                    <?php } ?>
                                                <?php 
                                                }
                                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                                    $jsst_configname = 'agent';
                                                } else {
                                                    $jsst_configname = 'user';
                                                }
                                                if (jssupportticket::$_config['show_read_receipt_to_' . $jsst_configname . '_on_reply'] == 1 && !empty($jsst_reply->viewed_by) && (($jsst_configname == 'user' && jssupportticket::$jsst_data[0]->uid == $jsst_reply->uid) || ($jsst_configname == 'agent' && $jsst_cur_uid == $jsst_reply->uid))) { ?>
                                                    <span class="js-ticket-thread-read-status-wrp">
                                                        <span class="js-ticket-thread-read-status-btn">
                                                           <img alt = "<?php echo esc_attr(__('View Image','js-support-ticket')) ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/view.png" />
                                                        </span>
                                                        <span class="js-ticket-thread-read-status-detail">
                                                            <span class="js-ticket-thread-read-status-row">
                                                                <?php 
                                                                echo '<b>'.esc_html(__('Viewed By','js-support-ticket').': ').'</b>';
                                                                if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
                                                                    echo esc_html(jssupportticket::$_config['title']);
                                                                }else{
                                                                    echo esc_html($jsst_reply->viewername); 
                                                                } ?>
                                                            </span>
                                                            <span class="js-ticket-thread-read-status-row">
                                                                <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_reply->viewed_on))); ?>
                                                            </span>
                                                        </span>
                                                    </span>
                                                    <?php 
                                                }
                                                 ?>
                                                            <?php
                                                            if (
                                                                JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Set AI Reply Mode for Reply') &&
                                                                JSSTaipolicy::onsiteFeature('aipoweredreply') && 
                                                                jssupportticket::$jsst_data[0]->uid != $jsst_reply->uid && 
                                                                $jsst_reply->uid != 0) { ?>
                                                            <?php /* Whether this reply may be offered to the AI as an example.
                                                               (Roadmap 6.0-AI-01)

                                                               A setting on the reply rather than something to do to it,
                                                               so it sits in the reply's header as a labelled switch and
                                                               is always shown. It used to be a button among Edit Reply
                                                               and Edit Time that stayed invisible until the reply was
                                                               hovered, which read as one more action and was easy to
                                                               miss altogether.

                                                               Off is 2 - excluded from the search - and on is 0. The
                                                               gates above it are the portal's own: staff only, and only
                                                               an agent whose role grants "Set AI Reply Mode for Reply".
                                                               A customer reading their own ticket never sees it. */
                                                            $jsst_ai_off = ((int) $jsst_reply->aireplymode === 2); ?>
                                                            <button type="button"
                                                                class="js-ticket-ai-example<?php echo $jsst_ai_off ? ' js-ticket-ai-example-off' : ''; ?>"
                                                                data-type="reply" data-id="<?php echo esc_attr($jsst_reply->replyid); ?>"
                                                                role="switch"
                                                                aria-checked="<?php echo $jsst_ai_off ? 'false' : 'true'; ?>"
                                                                title="<?php echo esc_attr__('Whether the AI may offer this reply as an example when answering a similar ticket. Nothing leaves your site: the suggestions are a search of your own past replies.', 'js-support-ticket'); ?>">
                                                                <span><?php echo $jsst_ai_off
                                                                    ? esc_html__('Not used by AI', 'js-support-ticket')
                                                                    : esc_html__('Used by AI', 'js-support-ticket'); ?></span>
                                                            </button>
                                                            <?php } ?>
                                            </div>
                                            <?php
                                            if (jssupportticket::$_config['show_email_on_ticket_reply'] == 1 && !empty($jsst_field_array['email'])) {
                                                if(isset($jsst_reply->staffemail)){ ?>
                                                    <div class="js-ticket-thread-data">
                                                        <span class="js-ticket-thread-email"><?php echo esc_html($jsst_reply->staffemail); ?></span>
                                                    </div>
                                                    <?php
                                                } elseif(isset($jsst_reply->useremail)){ ?>
                                                    <div class="js-ticket-thread-data">
                                                        <span class="js-ticket-thread-email"><?php echo esc_html($jsst_reply->useremail); ?></span>
                                                    </div>
                                                    <?php
                                                }
                                            }   
                                            ?>
                                            <div class="js-ticket-thread-data">
                                                <?php echo ($jsst_reply->ticketviaemail == 1) ? esc_html(__('Created via Email', 'js-support-ticket')) : ''; ?>
                                            </div>
                                            <div class="js-ticket-thread-data note-msg">
                                                <?php // A ticket link in a stored reply belongs to whoever is reading it. (Roadmap 4.0-CORE-01)
                                                echo wp_kses_post(JSSTticketlink::resolve(html_entity_decode($jsst_reply->message)));
                                                $jsst_active_count = 0; // Counter for active attachments
                                                ?>
                                                <?php if (!empty($jsst_reply->attachments)) { ?>
                                                    <div class="js-ticket-attachments-wrp">
                                                        <?php foreach ($jsst_reply->attachments AS $jsst_attachment) {
                                                            // Check if the file was deleted by the Auto Cleanup Cron
                                                            if ( $jsst_attachment->deleted == 1 ) { ?>
                                                                <div class="jsst-attachment-purged">
                                                                    <svg class="jsst-purged-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                                        <path d="M6.854 7.146a.5.5 0 1 0-.708.708L7.293 9l-1.147 1.146a.5.5 0 0 0 .708.708L8 9.707l1.146 1.147a.5.5 0 0 0 .708-.708L8.707 9l1.147-1.146a.5.5 0 0 0-.708-.708L8 8.293 6.854 7.146z"/>
                                                                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                                                    </svg>
                                                                    <span>
                                                                        <span class="jsst-purged-filename"><?php echo esc_html( $jsst_attachment->filename ); ?></span>
                                                                        <span class="jsst-purged-reason"><?php echo esc_html__( '(Removed automatically to save space)', 'js-support-ticket' ); ?></span>
                                                                    </span>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                $jsst_active_count++; // Increment the active counter
                                                                // Render Standard Downloadable File
                                                                $jsst_path = jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'downloadbyid','action'=>'jstask','id'=> $jsst_attachment->id ,'jsstpageid'=>get_the_ID()));
                                                                $jsst_data = wp_check_filetype($jsst_attachment->filename);
                                                                $jsst_type = $jsst_data['type'];
                                                                echo wp_kses('
                                                                    <div class="js_ticketattachment">
                                                                        <span class="js-ticket-download-file-title">
                                                                            ' . esc_html($jsst_attachment->filename) . ' ( ' . esc_html(round($jsst_attachment->filesize,2)) . ' kb) ' . '
                                                                        </span>
                                                                        <a class="js-download-button" target="_blank" href="' . esc_url($jsst_path) . '">'
                                                                            . esc_html(__('Download', 'js-support-ticket')) .'
                                                                        </a>',JSST_ALLOWED_TAGS);
                                                                        if(jssupportticketphplib::JSST_strpos($jsst_type, "image") !== false) {
                                                                            $jsst_path = JSSTincluder::getJSModel('attachment')->getAttachmentImage($jsst_attachment->id);
                                                                            echo '<a data-gall="gallery-'.esc_attr($jsst_reply->replyid).'" class="js-download-button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_attr($jsst_path) .'"  target="_blank">
                                                                            <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                                </a>';
                                                                        }
                                                                echo '</div>';
                                                                }
                                                            }
                                                            // Only show "Download All" if there is at least one active file
                                                            if ( $jsst_active_count > 0 ) {
                                                                $jsst_nonce = wp_create_nonce("download-all-for-reply-".$jsst_reply->replyid);
                                                                echo wp_kses('
                                                                <a class="js-all-download-button" target="_blank" href="' . esc_url(jssupportticket::makeUrl(array('jstmod'=>'ticket', 'task'=>'downloadallforreply', 'action'=>'jstask', 'downloadid'=>$jsst_reply->replyid, '_wpnonce'=>$jsst_nonce , 'jsstpageid'=>get_the_ID()))) . '" onclick="" target="_blank">'. esc_html(__('Download All', 'js-support-ticket')) . '</a>', JSST_ALLOWED_TAGS);
                                                            } ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                                <?php if (in_array('agent',jssupportticket::$_active_addons) &&  jssupportticket::$jsst_data['user_staff']) {
                                                    ?>
                                                        <div class="js-ticket-thread-cnt-btm">
                                                            <div class="js-ticket-thread-date">
                                                                <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_reply->created))); ?>
                                                            </div>
                                                            <div class="js-ticket-thread-actions">
                                                                <?php 
                                                                if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Reply') && jssupportticket::$jsst_data[0]->status != 6){
                                                                    $jsst_nonce = wp_create_nonce('get-reply-data-by-id-'.$jsst_reply->replyid); ?>
                                                                    <a class="js-ticket-thread-actn-btn ticket-edit-reply-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_reply->replyid);?>,1, '<?php echo esc_js($jsst_nonce);?>')" >
                                                                        <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                                        <?php echo esc_html(__('Edit Reply','js-support-ticket'));?>
                                                                    </a>
                                                                    <?php
                                                                }
                                                                if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                                    if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time') && jssupportticket::$jsst_data[0]->status != 6){
                                                                        $jsst_nonce = wp_create_nonce('get-time-by-reply-id-'.$jsst_reply->replyid); ?>
                                                                        <a class="js-ticket-thread-actn-btn ticket-edit-time-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_reply->replyid);?>,2, '<?php echo esc_js($jsst_nonce);?>')" >
                                                                            <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time-1.png" />
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
                                                     <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_reply->created))); ?>
                                                </span>
                                            </div>

                                        </div>
                                    </div>
                        <?php endforeach; ?>
                        <?php
                        // Agent-side extras after the thread, matching
                        // admin_ticketdetail.php. This template is shared by the
                        // customer and the agent, so the hook is fired only for
                        // staff - a customer must never be shown an AI reply
                        // that is still being held back for review.
                        $jsst_ir_is_staff = current_user_can('manage_options')
                            || (in_array('agent', jssupportticket::$_active_addons)
                                && JSSTincluder::getJSModel('agent')->isUserStaff());

                        if ($jsst_ir_is_staff) {
                            do_action('jsst_after_ticket_replies', jssupportticket::$jsst_data[0]->id);
                        }
                        ?>
                        <!-- User post Reply Form Section -->
                        <div class="js-ticket-reply-forms-wrapper"><!-- Ticket Reply Forms Wrapper -->
                            <?php if($jsst_printflag == false){
                                if (!jssupportticket::$jsst_data['user_staff'] && $jsst_companyreadonly) { ?>
                                    <div class="js-ticket-reply-forms-heading"><?php echo esc_html(__('Read only', 'js-support-ticket')); ?></div>
                                    <p class="js-ticket-company-readonly"><?php echo esc_html(sprintf(
                                        /* translators: %s: the name of the colleague who raised the ticket */
                                        __('You can read this ticket because you supervise your company\'s tickets. Only %s, who raised it, can reply to, close or reopen it.', 'js-support-ticket'),
                                        jssupportticket::$jsst_data[0]->name !== '' ? jssupportticket::$jsst_data[0]->name : jssupportticket::$jsst_data[0]->email)); ?></p>
                                </div>
                                </form>
                                <?php /* The same two closes the customer and agent branches end
                                         with: the reply-forms wrapper, then the reply form opened
                                         above the left column. Without them the right column was
                                         drawn inside the left one. */ ?>
                                <?php } elseif (!jssupportticket::$jsst_data['user_staff']) {
                                    if (jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->lock != 1 && jssupportticket::$jsst_data[0]->status != 6): ?>
                                        <div class="js-ticket-reply-forms-heading"><?php echo esc_html(__('Reply A Message', 'js-support-ticket')); ?></div>
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
                                                    <span id="tk_attachment_add" data-ident="tk_attachment_user_reply_wrapper" class="tk_attachments_addform"><?php echo esc_html(__('Add More','js-support-ticket')); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="js-ticket-closeonreply-wrp">
                                            <div class="js-ticket-closeonreply-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></div>
                                            <div class="replyFormStatus js-form-title-position-reletive-left">
                                                <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close On Reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                        </div>
                                        <div class="js-ticket-reply-form-button-wrp">
                                            <?php echo wp_kses(JSSTformfield::submitbutton('postreplybutton', esc_html(__('Post Reply', 'js-support-ticket')), array('class' => 'js-ticket-save-button', 'onclick' => "return checktinymcebyid('jsticket_message');")), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('actionid', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('priority', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$jsst_data[0]->created), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketrandomid', jssupportticket::$jsst_data[0]->ticketid), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('hash', jssupportticket::$jsst_data[0]->hash), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('updated', jssupportticket::$jsst_data[0]->updated), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                </div>
                                </form>

                                <?php
                                }else {
                                    ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('actionid', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('priority', ''), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('created', jssupportticket::$jsst_data[0]->created), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('updated', jssupportticket::$jsst_data[0]->updated), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                    <?php echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                </div>
                                </form>
                                <?php if (jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6) { ?>
                                    <div id="postreply" class="js-det-tkt-rply-frm"><!-- Post Reply Area -->
                                        <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'reply','task'=>'savereply')),"save-reply-".jssupportticket::$jsst_data[0]->id)); ?>" enctype="multipart/form-data">
                                            <div class="js-tkt-det-title">
                                                <?php echo esc_html(__('Post Reply','js-support-ticket')); ?>
                                            </div>
                                            <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                                <div class="jsst-ticket-detail-timer-wrapper"> <!-- Timer Wrapper -->
                                                    <div class="timer-left" >
                                                        <div class="timer-total-time" >
                                                            <?php
                                                                $jsst_hours = floor(jssupportticket::$jsst_data['time_taken'] / 3600);
                                                                $jsst_mins = floor(jssupportticket::$jsst_data['time_taken'] / 60);
                                                                $jsst_mins = floor($jsst_mins % 60);
                                                                $jsst_secs = floor(jssupportticket::$jsst_data['time_taken'] % 60);
                                                                echo esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <div class="timer-right" >
                                                        <div class="timer" >
                                                            00:00:00
                                                        </div>
                                                        <div class="timer-buttons" >
                                                            <?php if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Own Time')){ ?>
                                                                <span class="timer-button" onclick="showEditTimerPopup(this)" >
                                                                    <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/timer-edit.png"/>
                                                                </span>
                                                            <?php } ?>
                                                            <span class="timer-button cls_1" onclick="changeTimerStatus(1, this)" >
                                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play.png"/>
                                                            </span>
                                                            <span class="timer-button cls_2" onclick="changeTimerStatus(2, this)" >
                                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/pause.png"/>
                                                            </span>
                                                            <span class="timer-button cls_3" onclick="changeTimerStatus(3, this)" >
                                                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/stop.png"/>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>

                                                    <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            <?php } ?>
                                            <?php
                                            /* One row, above the box, for every way of not writing this
                                               reply from scratch. (Roadmap 6.0-AI-01, 4.5-FE-01)

                                               The two were in different places and in different visual
                                               languages: canned responses in a titled block above the
                                               editor, and the AI suggestions below it inside a bordered
                                               card with an icon, a product name and a sentence of
                                               marketing. Nothing said they were alternatives for the same
                                               job, and the one that reads an answer *before* you type was
                                               the one placed after the box you type in.

                                               Both sit above the editor now, under one sentence, in the
                                               same shape the backend thread uses - the two agent
                                               workspaces disagreeing about the same desk is the failure
                                               this release exists to end. The row draws itself only if it
                                               has something to offer: a desk with no canned responses for
                                               this department and an agent without the AI permission get
                                               nothing at all rather than a heading over an empty strip. */
                                            $jsst_showai = JSSTincluder::getJSModel('userpermissions')
                                                ->checkPermissionGrantedForTask('Use AI Powered Reply Feature');
                                            $jsst_cannedresponses = array();
                                            if (isset($jsst_field_array['premade']) && JSSTmergedaddon::featureEnabled('cannedresponses')) {
                                                /* This ticket's department, the same as the backend thread.
                                                   The list was every canned response on the desk here too,
                                                   and the add-ticket screens have always narrowed it.
                                                   (Roadmap 4.0-CORE-03) */
                                                $jsst_ticketdept = isset(jssupportticket::$jsst_data[0]->departmentid)
                                                    ? jssupportticket::$jsst_data[0]->departmentid : '';
                                                $jsst_cannedresponses = JSSTincluder::getJSModel('cannedresponses')
                                                    ->getPreMadeMessageForCombobox($jsst_ticketdept);
                                            }
                                            if ($jsst_showai || !empty($jsst_cannedresponses)) { ?>
                                                <div class="js-ticket-suggest">
                                                    <span class="js-ticket-suggest-lead"><?php echo esc_html__('Write this reply with help:', 'js-support-ticket'); ?></span>
                                                    <?php if ($jsst_showai) { ?>
                                                        <?php /* A button, not a card. "Suggested Response" sat under
                                                           "AI Powered Reply" and "Get context-aware suggestions for
                                                           your response." - a product name and a sentence selling
                                                           it, on the screen of somebody who has already decided to
                                                           use it. Named for what it does instead, with the
                                                           magnifier that says this one looks something up. Drawn
                                                           inline rather than from a dashicon, which is a wp-admin
                                                           font and is not loaded out here. */ ?>
                                                        <button type="button" id="js-ticket-ai-reply-btn" class="js-ticket-help-btn js-ticket-help-btn-primary"
                                                                title="<?php echo esc_attr__('Finds answers already written here \u2014 your knowledge base, FAQs and past replies.', 'js-support-ticket'); ?>">
                                                            <svg class="js-ticket-help-btn-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                                                            <?php echo esc_html__('AI Agent', 'js-support-ticket'); ?>
                                                        </button>
                                                    <?php } ?>
                                                    <?php /* And the canned responses, as a box you can type in.
                                                       (Roadmap 4.0-CORE-03) A select can only be searched by its
                                                       first letter, and the word an agent remembers is usually
                                                       from the middle of the title. Filtering is done against the
                                                       options already on the page, so typing costs no request. */
                                                    if (!empty($jsst_cannedresponses)) { ?>
                                                        <span class="js-ticket-canned">
                                                            <span class="js-ticket-canned-box">
                                                                <input type="text" id="js-ticket-canned-search" class="js-ticket-canned-search"
                                                                       autocomplete="off" role="combobox" aria-expanded="false"
                                                                       aria-controls="js-ticket-canned-list" aria-autocomplete="list"
                                                                       placeholder="<?php echo esc_attr__('Canned response', 'js-support-ticket'); ?>"
                                                                       aria-label="<?php echo esc_attr__('Search canned responses', 'js-support-ticket'); ?>" />
                                                                <ul id="js-ticket-canned-list" class="js-ticket-canned-list" role="listbox"
                                                                    aria-label="<?php echo esc_attr__('Canned responses', 'js-support-ticket'); ?>" hidden>
                                                                    <?php foreach ($jsst_cannedresponses as $jsst_premade) { ?>
                                                                        <li class="js-ticket-canned-option" role="option" aria-selected="false"
                                                                            id="js-ticket-canned-opt-<?php echo esc_attr($jsst_premade->id); ?>"
                                                                            data-id="<?php echo esc_attr($jsst_premade->id); ?>"><?php
                                                                            echo esc_html(jssupportticket::JSST_getVarValue($jsst_premade->text)); ?></li>
                                                                    <?php } ?>
                                                                    <li class="js-ticket-canned-none" hidden><?php
                                                                        echo esc_html__('Nothing matches', 'js-support-ticket'); ?></li>
                                                                </ul>
                                                            </span>
                                                            <?php /* What the checkbox does, said in the checkbox. It read
                                                               "Append", which names the branch rather than the choice,
                                                               and the unticked branch is the one worth warning about:
                                                               picking a response with this off replaces everything in
                                                               the editor, including what the agent had already typed. */ ?>
                                                            <span class="js-ticket-canned-append">
                                                                <?php echo wp_kses(JSSTformfield::checkbox('append_premade',
                                                                    array('1' => esc_html(__('Add to what I have written', 'js-support-ticket'))), '',
                                                                    array('class' => 'radiobutton js-ticket-premade-radiobtn',
                                                                          'title' => esc_attr__('Off, the canned response replaces everything in the box.', 'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                                                            </span>
                                                        </span>
                                                    <?php } ?>
                                                    <?php /* Where anything that went wrong is said. Empty almost
                                                       always, and :empty takes it out of the row when it is. */ ?>
                                                    <span class="js-ticket-suggest-status" id="js-ticket-suggest-status" role="status" aria-live="polite"></span>
                                                </div>
                                            <?php }
                                            if ($jsst_showai) { ?>
                                                <span class="js-ticket-current-ticket-title"><?php echo esc_html( jssupportticket::$jsst_data[0]->subject ); ?></span>
                                                <span class="js-ticket-current-ticket-id"><?php echo esc_html( jssupportticket::$jsst_data[0]->id ); ?></span>
                                                <div class="js-ticket-container">
                                                    <div id="js-ticket-matching-tickets-section" class="js-ticket-section js-ticket-matching-tickets-section js-ticket-hidden">
                                                        <div class="js-ticket-selected-tickets-header">
                                                            <div class="js-ticket-section-heading"><?php echo esc_html__('Suggested replies', 'js-support-ticket'); ?></div>
                                                            <?php /* The filter is shown only when tickets are what
                                                               answered - the script hides it when the suggestions
                                                               came from the knowledge base, where "Everything"
                                                               describes nothing. */
                                                            if(JSSTaipolicy::onsiteFeature('aipoweredreply')){ ?>
                                                                <div class="js-ticket-filter-group">
                                                                    <label for="js-ticket-tickets-filter" class="js-ticket-filter-label"><?php echo esc_html__('Show', 'js-support-ticket'); ?></label>
                                                                    <select id="js-ticket-tickets-filter" class="js-ticket-filter-select">
                                                                        <option value="all"><?php echo esc_html__('Everything', 'js-support-ticket'); ?></option>
                                                                        <option value="marked"><?php echo esc_html__('Only preferred tickets', 'js-support-ticket'); ?></option>
                                                                    </select>
                                                                </div>
                                                            <?php } ?>
                                                            <button type="button" id="js-ticket-close-tickets-btn" class="js-ticket-close-button">
                                                                <?php echo esc_html__('Hide', 'js-support-ticket'); ?>
                                                            </button>
                                                        </div>
                                                        <ul id="js-ticket-matching-tickets-list" class="js-ticket-list">
                                                        </ul>
                                                    </div>

                                                    <div id="js-ticket-selected-ticket-replies-section" class="js-ticket-section js-ticket-selected-replies-section js-ticket-hidden">
                                                        <div class="js-ticket-selected-replies-header">
                                                            <h2 class="js-ticket-section-heading" id="js-ticket-selected-ticket-replies-title"></h2>
                                                            <?php if(JSSTaipolicy::onsiteFeature('aipoweredreply')){ ?>
                                                                <div class="js-ticket-filter-group">
                                                                    <label for="js-ticket-replies-filter" class="js-ticket-filter-label"><?php echo esc_html__('Filter', 'js-support-ticket').': '; ?></label>
                                                                    <select id="js-ticket-replies-filter" class="js-ticket-filter-select">
                                                                        <option value="all"><?php echo esc_html__('All Replies', 'js-support-ticket'); ?></option>
                                                                        <option value="marked"><?php echo esc_html__('Enable Replies', 'js-support-ticket'); ?></option>
                                                                    </select>
                                                                </div>
                                                            <?php } ?>
                                                            <button type="button" id="js-ticket-close-replies-btn" class="js-ticket-close-button">
                                                                <?php echo esc_html__('Close', 'js-support-ticket'); ?>
                                                            </button>
                                                        </div>
                                                        <div id="js-ticket-selected-ticket-replies-content" class="js-ticket-replies-content reply-content">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="js-ticket-text-editor-wrp">
                                                <div class="js-ticket-text-editor-field-title"><?php echo esc_html(__('Type Message','js-support-ticket')); ?></div>
                                                <div class="js-ticket-text-editor-field"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                            </div>
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
                                                    <span id="tk_attachment_add" data-ident="tk_attachment_staff_reply_wrapper" class="tk_attachments_addform"><?php echo esc_html(__('Add More','js-support-ticket')); ?></span>
                                                </div>
                                            </div>
                                            <div class="js-ticket-append-signature-wrp"><!-- Append Signature -->
                                                <div class="js-ticket-append-field-title"><?php echo esc_html(__('Append Signature','js-support-ticket')); ?></div>
                                                <div class="js-ticket-append-field-wrp">
                                                    <div class="js-ticket-signature-radio-box">
                                                        <?php echo wp_kses(JSSTformfield::checkbox('ownsignature', array('1' => esc_html(__('Own Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <?php
                                                    if (!empty($jsst_field_array['department'])) { ?>
                                                        <div class="js-ticket-signature-radio-box">
                                                            <?php echo wp_kses(JSSTformfield::checkbox('departmentsignature', array('1' => esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ". esc_html(__('Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-append-radio-btn')), JSST_ALLOWED_TAGS); ?>
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
                                                $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
                                                if (jssupportticket::$jsst_data[0]->staffid != $jsst_staffid) {
                                                    ?>
                                                    <div class="js-ticket-assigntome-wrp">
                                                        <div class="js-ticket-assigntome-field-title"><?php echo esc_html(__('Assign Ticket','js-support-ticket')); ?></div>
                                                        <div class="js-ticket-assigntome-field-wrp">
                                                            <?php
                                                                if(jssupportticket::$jsst_data[0]->staffid){
                                                                    $jsst_checked = '';
                                                                }else{
                                                                    $jsst_checked = 1;
                                                                }
                                                                echo wp_kses(JSSTformfield::checkbox('assigntome', array('1' => esc_html(__('Assign To Me', 'js-support-ticket'))), $jsst_checked, array('class' => 'radiobutton js-ticket-assigntome-checkbox')), JSST_ALLOWED_TAGS);
                                                            ?>
                                                        </div>
                                                    </div><!-- Assign To Me -->
                                            <?php }
                                        } ?>
                                            <div class="js-ticket-closeonreply-wrp">
                                                <div class="js-ticket-closeonreply-title"><?php echo esc_html(__('Ticket Status','js-support-ticket')); ?></div>
                                                <div class="replyFormStatus js-form-title-position-reletive-left">
                                                    <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close On Reply', 'js-support-ticket'))), '', array('class' => 'radiobutton js-ticket-closeonreply-checkbox')), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                            <div class="js-ticket-reply-form-button-wrp">
                                                <?php echo wp_kses(JSSTformfield::submitbutton('postreply', esc_html(__('Post Reply', 'js-support-ticket')), array('class' => 'js-ticket-save-button', 'onclick' => "return checktinymcebyid('jsticket_message');")), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                            <?php echo wp_kses(JSSTformfield::hidden('departmentid', jssupportticket::$jsst_data[0]->departmentid), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('ticketrandomid',jssupportticket::$jsst_data[0]->ticketid), JSST_ALLOWED_TAGS); ?>
                                            <?php echo wp_kses(JSSTformfield::hidden('hash', jssupportticket::$jsst_data[0]->hash), JSST_ALLOWED_TAGS); ?>
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
                    <?php if($jsst_printflag == true){?>
                        </div> <!-- extra div for print -->
                    <?php } ?>
                    <!-- Ticket Detail Right -->
                    <div class="js-tkt-det-right">
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-info"> <!-- Ticket Info -->
                            <?php
                                if (jssupportticket::$jsst_data[0]->status == 1) {
                                    $jsst_ticketmessage = __('Open', 'js-support-ticket');
                                    $jsst_bgcolor = '#438323';
                                    $jsst_color1 = '#FFFFFF';
                                } else {
                                    $jsst_ticketmessage = jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->statustitle);
                                    $jsst_bgcolor = jssupportticket::$jsst_data[0]->statusbgcolour;
                                    $jsst_color1 = jssupportticket::$jsst_data[0]->statuscolour;
                                }
                            ?>
                            <div class="js-tkt-det-status" style="background-color:<?php echo esc_attr($jsst_bgcolor);?>;color :<?php echo esc_attr($jsst_color1);?>;">
                                <?php echo esc_html($jsst_ticketmessage); ?>
                            </div>
                            <div class="js-tkt-det-info-cnt">
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Created','js-support-ticket')) . ': '; ?>
                                    </div>
                                    <div class="js-tkt-det-info-val" title="<?php echo esc_attr(date_i18n("d F, Y, H:i:s A", jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->created))); ?>">
                                       <?php echo esc_html(human_time_diff(strtotime(jssupportticket::$jsst_data[0]->created),strtotime(date_i18n("Y-m-d H:i:s")))).' '. esc_html(__('ago', 'js-support-ticket')); ?>
                                    </div>
                                </div>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Last Reply','js-support-ticket')); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php if (empty(jssupportticket::$jsst_data[0]->lastreply) || jssupportticket::$jsst_data[0]->lastreply == '0000-00-00 00:00:00') echo esc_html(__('No Last Reply', 'js-support-ticket'));
                                            else echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->lastreply))); ?>
                                    </div>
                                </div>
                                <?php
                                // check if the department is publish or not
                                if (isset($jsst_field_array['department'])) { ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->departmentname)); ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                                if (in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()) {
                                    $jsst_configname = 'agent';
                                } else {
                                    $jsst_configname = 'user';
                                }
                                if (jssupportticket::$_config['show_closedby_on_' . $jsst_configname . '_tickets'] == 1 && jssupportticket::$jsst_data[0]->status == 5) { ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed By','js-support-ticket')). ': '; ?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy(jssupportticket::$jsst_data[0]->closedby)); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed On','js-support-ticket')). ': '; ?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->closed))); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Ticket ID', 'js-support-ticket')); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php echo esc_html(jssupportticket::$jsst_data[0]->ticketid); ?>
                                       <a title="<?php echo esc_attr(__('Copy','js-support-ticket')); ?>" class="js-tkt-det-copy-id" id="ticketidcopybtn" success="<?php echo esc_attr(__('Copied','js-support-ticket')); ?>"><?php echo esc_html(__('Copy','js-support-ticket')); ?></a>
                                    </div>
                                </div>
                                <?php
                                if(isset($jsst_field_array['helptopic']) && JSSTmergedaddon::featureEnabled('helptopic')){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['helptopic'])); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->helptopic)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php
                                // Ticket tags are an internal classification, so the portal
                                // shows them to staff only — a customer never sees how their
                                // ticket was filed. (Roadmap 4.0-CORE-17)
                                if (!empty(jssupportticket::$jsst_data['user_staff'])) {
                                    $jsst_tags = isset(jssupportticket::$jsst_data['tags']) ? jssupportticket::$jsst_data['tags'] : array();
                                    $jsst_tagnames = array();
                                    foreach ($jsst_tags AS $jsst_tag) {
                                        $jsst_tagnames[] = $jsst_tag->name;
                                    }
                                    $jsst_cantag = (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Ticket') == 1);
                                    ?>
                                    <div class="js-tkt-det-info-data jsst-ticket-tags">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Tags','js-support-ticket')); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php
                                            if (!$jsst_cantag) { ?>
                                                <span class="jsst-tag-list">
                                                    <?php if (empty($jsst_tagnames)) { ?>
                                                        <span class="jsst-tag-empty"><?php echo esc_html(__('None','js-support-ticket')); ?></span>
                                                    <?php } else {
                                                        foreach ($jsst_tagnames AS $jsst_tagname) { ?>
                                                            <span class="jsst-tag-chip"><?php echo esc_html($jsst_tagname); ?></span>
                                                        <?php }
                                                    } ?>
                                                </span>
                                            <?php } else { ?>
                                                <details class="jsst-tagbox">
                                                    <summary class="jsst-tagbox-summary">
                                                        <span class="jsst-tag-list">
                                                            <?php foreach ($jsst_tagnames AS $jsst_tagname) { ?>
                                                                <span class="jsst-tag-chip"><?php echo esc_html($jsst_tagname); ?></span>
                                                            <?php } ?>
                                                        </span>
                                                        <span class="jsst-tag-chip jsst-tag-add"><?php
                                                            echo esc_html(empty($jsst_tagnames)
                                                                ? __('Add tags','js-support-ticket')
                                                                : __('Edit','js-support-ticket'));
                                                        ?></span>
                                                    </summary>
                                                    <form class="jsst-tag-form" method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'savetickettags')),"ticket-tags-".jssupportticket::$jsst_data[0]->id)); ?>">
                                                        <div class="jsst-tag-row">
                                                            <input type="text" id="jsst-tag-input" name="tickettags" class="inputbox jsst-tag-input" value="<?php echo esc_attr(implode(', ', $jsst_tagnames)); ?>" placeholder="<?php echo esc_attr(__('billing, urgent, refund','js-support-ticket')); ?>" list="jsst-tag-suggestions" autocomplete="off" />
                                                            <?php echo wp_kses(JSSTformfield::submitbutton('savetickettags', esc_html(__('Save','js-support-ticket')), array('class' => 'button jsst-tag-save')), JSST_ALLOWED_TAGS); ?>
                                                        </div>
                                                        <datalist id="jsst-tag-suggestions">
                                                            <?php foreach (JSSTincluder::getJSModel('tag')->getTagsForCombobox() AS $jsst_known) { ?>
                                                                <option value="<?php echo esc_attr($jsst_known->text); ?>"></option>
                                                            <?php } ?>
                                                        </datalist>
                                                        <p class="jsst-tag-hint"><?php echo esc_html(__('Separate with commas. Saving replaces the whole list, so keep the ones you want.','js-support-ticket')); ?></p>
                                                        <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                                        <?php
                                                        echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                                                        <?php
                                                        echo wp_kses(JSSTformfield::hidden('jsstpageid', get_the_ID()), JSST_ALLOWED_TAGS); ?>
                                                    </form>
                                                </details>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php
                                if(isset($jsst_field_array['product'])){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <div class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['product'])); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                        </div>
                                        <div class="js-tkt-det-info-val">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->producttitle)); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <div class="js-tkt-det-info-tit">
                                       <?php echo esc_html(__('Status', 'js-support-ticket')); ?><?php echo esc_html(__(': ','js-support-ticket'));?>
                                    </div>
                                    <div class="js-tkt-det-info-val">
                                       <?php
                                            if (jssupportticket::$jsst_data[0]->status == 5 || jssupportticket::$jsst_data[0]->status == 6 ||
                                                jssupportticket::$jsst_data[0]->status == 3) {
                                                $jsst_ticketmessage = jssupportticket::$jsst_data[0]->statustitle;
                                            } else {
                                                $jsst_ticketmessage = __('Open', 'js-support-ticket');
                                            }
                                            /* Lock and Overdue are two notes shown in place of the
                                               status. The comma separates them, so it belongs between
                                               them and not after the last one — a ticket that is locked
                                               but not overdue used to read "Lock ,". */
                                            $jsst_statusnotes = array();
                                            if (jssupportticket::$jsst_data[0]->lock == 1) {
                                                $jsst_statusnotes[] = __('Lock', 'js-support-ticket');
                                            }
                                            if (jssupportticket::$jsst_data[0]->isoverdue == 1) {
                                                $jsst_statusnotes[] = __('Overdue', 'js-support-ticket');
                                            }
                                            $jsst_printstatus = empty($jsst_statusnotes) ? 1 : 0;
                                            $jsst_lastnote = count($jsst_statusnotes) - 1;
                                            foreach ($jsst_statusnotes AS $jsst_noteindex => $jsst_statusnote) {
                                                echo '<div class="js-ticket-status-note">' . esc_html($jsst_statusnote) . (($jsst_noteindex < $jsst_lastnote) ? esc_html(',') : '') . '</div>';
                                            }
                                            if ($jsst_printstatus == 1) {
                                                echo esc_html($jsst_ticketmessage);
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
                                if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff'] && jssupportticket::$jsst_data[0]->status != 6) {
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
                                                <form method="post" action="<?php echo esc_url(wp_nonce_url(jssupportticket::makeUrl(array('jstmod'=>'ticket','task'=>'changestatus')),"change-status-".jssupportticket::$jsst_data[0]->id)); ?>" enctype="multipart/form-data">
                                                    <div class="js-ticket-premade-msg-wrp"><!-- Select Status Wrapper -->
                                                        <div class="js-ticket-premade-field-title"><?php echo esc_html(__('Select Status', 'js-support-ticket')); ?></div>
                                                        <div class="js-ticket-premade-field-wrp">
                                                            <?php echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForCombobox(), jssupportticket::$jsst_data[0]->status, esc_html(__('Select Status', 'js-support-ticket')), array('class' => 'js-ticket-premade-select')), JSST_ALLOWED_TAGS); ?>
                                                        </div>
                                                    </div>
                                                    <div class="js-ticket-reply-form-button-wrp">
                                                        <?php echo wp_kses(JSSTformfield::submitbutton('changestatus', esc_html(__('Change Status', 'js-support-ticket')), array('class' => 'js-ticket-save-button')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
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
                            if (!empty(jssupportticket::$jsst_data[0]->status)) { ?>
                                <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]->statusbgcolour);?>;color:<?php echo esc_attr(jssupportticket::$jsst_data[0]->statuscolour);?>;">
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->statustitle)); ?>
                                </div>
                                <?php
                            } ?>
                        </div>
                        <?php
                        if(
                            in_array('agent',jssupportticket::$_active_addons) &&  
                            jssupportticket::$jsst_data['user_staff'] &&
                            JSSTaipolicy::onsiteFeature('aipoweredreply') &&
                            JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Set AI Reply Mode for Ticket')){ ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Status -->
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <?php /* One control that says what it does, the same one wp-admin
                                           draws. (Roadmap 6.0-AI-01, 4.5-FE-01)

                                           Three buttons labelled Default, Enable and Disable, with the
                                           explanation hidden inside an info icon, asked somebody to guess
                                           what was being enabled and then hover to find out - and hover is
                                           not a thing a phone has, which is where half of this workspace
                                           is read. The words are the setting now, each option a sentence
                                           about this ticket, and the note under it is the sentence that
                                           was in the tooltip, said once and visible.

                                           A select rather than buttons: one tab stop instead of three, the
                                           chosen value announced to a screen reader without a role="group"
                                           and an aria-label doing that by hand, and the native list on a
                                           phone. */ ?>
                                        <label class="js-ticket-ai-mode-label" for="js-ticket-ai-ticket-mode"><?php
                                            echo esc_html__('AI suggestions from this ticket', 'js-support-ticket'); ?></label>
                                    </div>
                                </div>

                                <div class="js-tkt-det-tkt-prty-txt js-ticket-ai-reply-status-wrapper">
                                    <select id="js-ticket-ai-ticket-mode" class="js-ticket-ai-mode-select"
                                            data-type="ticket" data-id="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                        <option value="0" <?php selected((int) jssupportticket::$jsst_data[0]->aireplymode, 0); ?>><?php
                                            echo esc_html__('Use it when it matches', 'js-support-ticket'); ?></option>
                                        <option value="1" <?php selected((int) jssupportticket::$jsst_data[0]->aireplymode, 1); ?>><?php
                                            echo esc_html__('Prefer it — a good example', 'js-support-ticket'); ?></option>
                                        <option value="2" <?php selected((int) jssupportticket::$jsst_data[0]->aireplymode, 2); ?>><?php
                                            echo esc_html__('Never use it', 'js-support-ticket'); ?></option>
                                    </select>
                                    <p class="js-ticket-ai-mode-note"><?php echo esc_html__('Whether this ticket and its replies may be offered as examples when answering a similar ticket. Nothing leaves your site.', 'js-support-ticket'); ?></p>
                                </div>
                            </div>
                            <?php
                        }
                        if(!empty($jsst_field_array['priority'])) { ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty"> <!-- Ticket Priority -->
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <!-- Display heading based on field order  -->
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                                    </div>
                                    <?php
                                    if (in_array('agent',jssupportticket::$_active_addons) && jssupportticket::$jsst_data['user_staff'] && jssupportticket::$jsst_data[0]->status != 6) {
                                        if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Change Ticket Priority')){
                                        ?>
                                            <a class="js-tkt-det-hdg-btn" href="#" id="changepriority">
                                                <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                            </a>
                                            <div id="userpopupforchangepriority" style="display:none;">
                                                <div class="js-ticket-priorty-header">
                                                    <?php echo esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                                                    <span class="close-history"></span>
                                                </div>
                                                <div class="js-ticket-priorty-fields-wrp">
                                                    <div class="js-ticket-select-priorty">
                                                        <?php echo wp_kses(JSSTformfield::select('prioritytemp', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$jsst_data[0]->priorityid, esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($jsst_field_array['priority'])), array()), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                </div>
                                                <div class="js-ticket-priorty-btn-wrp">
                                                    <?php echo wp_kses(JSSTformfield::button('changepriority', esc_html(__('Change', 'js-support-ticket').' '.jssupportticket::JSST_getVarValue($jsst_field_array['priority'])), array('class' => 'js-ticket-priorty-save', 'onclick' => 'actionticket(1);')), JSST_ALLOWED_TAGS); ?>
                                                    <?php echo wp_kses(JSSTformfield::button('cancelee', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                                if (!empty(jssupportticket::$jsst_data[0]->priority)) { ?>
                                    <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]->prioritycolour);?>; color:#ffffff;">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->priority)); ?>
                                    </div>
                                    <?php
                                } else { ?>
                                    <div class="js-tkt-det-tkt-prty-error-txt">
                                        <?php
                                        echo esc_html(__('No','js-support-ticket'))." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority']))." ".esc_html(__('set','js-support-ticket')); ?>
                                    </div>
                                    <?php
                                } ?>
                            </div>
                        <?php } ?>
                        <?php
                        $jsst_agentflag = in_array('agent', jssupportticket::$_active_addons) && $jsst_printflag == false && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6;
                        $jsst_departmentflag = JSSTmergedaddon::featureEnabled('actions') && $jsst_printflag == false && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 && isset($jsst_field_array['department']);
                        if($jsst_agentflag || $jsst_departmentflag){
                            ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-assign"> <!-- Ticket Assign -->
                                <?php if($jsst_agentflag){ ?>
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <?php echo esc_html(__('Assigned To Agent','js-support-ticket')); ?>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="js-tkt-det-tkt-asgn-cnt">
                                    <?php if($jsst_agentflag){ ?>
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php
                                            if(jssupportticket::$jsst_data[0]->staffid > 0){
                                                echo esc_html(__('Ticket assigned to','js-support-ticket'));
                                            }else{
                                                echo esc_html(__('Not assigned to agent','js-support-ticket'));
                                            }
                                            ?>
                                        </div>
                                        <?php if(jssupportticket::$jsst_data['user_staff']){ 
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
                                        <?php if($jsst_agentflag && jssupportticket::$jsst_data[0]->staffid > 0){ ?>
                                        <div class="js-tkt-det-user">
                                            <div class="js-tkt-det-user-image">
                                                <?php
                                                if(jssupportticket::$jsst_data[0]->staffphoto && jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 2){
                                                    echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->staffuid), JSST_ALLOWED_TAGS);
                                                    /* ?>
                                                    <img alt="<?php echo esc_attr(__('agent photo','js-support-ticket')); ?>" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=>jssupportticket::$jsst_data[0]->staffid, 'jsstpageid'=>jssupportticket::getPageid()))); ?>">
                                                    <?php */
                                                } else { ?>
                                                    <img alt="<?php echo esc_attr(__('agent photo','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/user.png'; ?>" />
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="js-tkt-det-user-cnt">
                                                <div class="js-tkt-det-user-data"><?php 
                                                    if (jssupportticket::$_config['anonymous_name_on_ticket_reply'] == 1) {
                                                        echo esc_html(jssupportticket::$_config['title']);
                                                    }else{
                                                        echo esc_html(jssupportticket::$jsst_data[0]->staffname); 
                                                    }
                                                ?></div>
                                                <div class="js-tkt-det-user-data agent-email"><?php 
                                                if (jssupportticket::$_config['show_email_on_ticket_reply'] == 1) {
                                                    echo esc_html(jssupportticket::$jsst_data[0]->staffemail); 
                                                }
                                                ?></div>
                                                <div class="js-tkt-det-user-data"><?php 
                                                    if (jssupportticket::$_config['show_email_on_ticket_reply'] == 2) {
                                                        echo esc_html(jssupportticket::$jsst_data[0]->staffphone);
                                                    }
                                                ?></div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if($jsst_departmentflag){ ?>
                                            <div class="js-tkt-det-trsfer-dep">
                                                <div class="js-tkt-det-trsfer-dep-txt">
                                                    <span class="js-tkt-det-trsfer-dep-txt-tit"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])).': '; ?> </span>
                                                    <?php echo esc_html(jssupportticket::$jsst_data[0]->departmentname); ?>
                                                </div>
                                                <?php if(jssupportticket::$jsst_data['user_staff']){ 
                                                        if(JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ticket Department Transfer')){
                                                    ?>
                                                            <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="departmenttransfer">
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

                        <?php if(in_array('timetracking', jssupportticket::$_active_addons) && isset(jssupportticket::$jsst_data['time_taken'])){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-time-tracker"> <!-- Time Tracker -->
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(__('Total Time Taken','js-support-ticket')); ?>
                                </div>
                            </div>
                            <div class="js-tkt-det-timer-wrp"> <!-- Timer Wrapper -->
                                <div class="timer-total-time" >
                                    <?php
                                    $jsst_hours = floor(jssupportticket::$jsst_data['time_taken'] / 3600);
                                    $jsst_mins = floor(jssupportticket::$jsst_data['time_taken'] / 60);
                                    $jsst_mins = floor($jsst_mins % 60);
                                    $jsst_secs = floor(jssupportticket::$jsst_data['time_taken'] % 60);
                                    $jsst_time =  sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                    ?>
                                    <div class="timer-total-time-value">
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $jsst_hours)); ?>
                                        </span>
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $jsst_mins)); ?>
                                        </span>
                                        <span class="timer-box">
                                            <?php echo esc_html(sprintf('%02d', $jsst_secs)); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </div>
                        <?php } ?>
                        <!-- User Tickets -->
                        <?php if(isset(jssupportticket::$jsst_data['usertickets']) && !empty(jssupportticket::$jsst_data['usertickets'])){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-user-tkts">
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php
                                    if(!empty($jsst_field_array['fullname'])) {
                                        echo esc_html(jssupportticket::$jsst_data[0]->name).' '. esc_html(__('Tickets','js-support-ticket'));
                                    } else {
                                        echo esc_html(__('Other Tickets','js-support-ticket'));
                                    } ?>
                                </div>
                            </div>
                            <div class="js-tkt-det-usr-tkt-list">
                                <?php
                                $jsst_fields_array = array(); // Array for form fields
                                foreach(jssupportticket::$jsst_data['usertickets'] as $jsst_userticket){
                                    // Check if the form fields are already array
                                    if (!isset($jsst_fields_array[$jsst_userticket->multiformid])) {
                                        $jsst_fields_array[$jsst_userticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $jsst_userticket->multiformid);
                                    }
                                    // Now use the cached field array
                                    $jsst_ticket_field_array = $jsst_fields_array[$jsst_userticket->multiformid];
                                    ?>
                                    <div class="js-tkt-det-user">
                                        <div class="js-tkt-det-user-image">
                                            <?php echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="js-tkt-det-user-cnt">
                                            <div class="js-tkt-det-user-data name">
                                                <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_userticket->subject)); ?></span>
                                            </div>
                                            <?php
                                            // check if the department is publish or not
                                            if (isset($jsst_ticket_field_array['department'])) { ?>
                                                <div class="js-tkt-det-user-data">
                                                    <span class="js-tkt-det-user-tit"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket_field_array['department'])). ': '; ?></span>
                                                    <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_userticket->departmentname)); ?></span>
                                                </div>
                                                <?php
                                            } ?>
                                            <div class="js-tkt-det-user-data">
                                                <?php
                                                if(!empty($jsst_ticket_field_array['priority'])) { ?>
                                                    <span class="js-tkt-det-prty" style="background:<?php echo esc_html($jsst_userticket->prioritycolour);?>;">
                                                       <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_userticket->priority)); ?>
                                                    </span>
                                                    <?php 
                                                } ?>
                                                <span class="js-tkt-det-status" style="background-color:<?php echo esc_attr($jsst_bgcolor);?>;color :<?php echo esc_attr($jsst_color1);?>;">
                                                    <?php
                                                        if ($jsst_userticket->status == 5 || 
                                                            $jsst_userticket->status == 6 ||
                                                            $jsst_userticket->status == 3) {
                                                            $jsst_userticketmessage = $jsst_userticket->statustitle;
                                                        } else {
                                                            $jsst_userticketmessage = __('Open', 'js-support-ticket');
                                                        }
                                                        // Same separator rule as the ticket's own status above.
                                                        $jsst_usernotes = array();
                                                        if ($jsst_userticket->lock == 1) {
                                                            $jsst_usernotes[] = __('Lock', 'js-support-ticket');
                                                        }
                                                        if ($jsst_userticket->isoverdue == 1) {
                                                            $jsst_usernotes[] = __('Overdue', 'js-support-ticket');
                                                        }
                                                        $jsst_userticketprintstatus = empty($jsst_usernotes) ? 1 : 0;
                                                        $jsst_userlastnote = count($jsst_usernotes) - 1;
                                                        foreach ($jsst_usernotes AS $jsst_usernoteindex => $jsst_usernote) {
                                                            echo wp_kses('<span class="js-ticket-status-note">' . esc_html($jsst_usernote) . (($jsst_usernoteindex < $jsst_userlastnote) ? esc_html(',') : '') . '</span>', JSST_ALLOWED_TAGS);
                                                        }
                                                        if ($jsst_userticketprintstatus == 1) {
                                                            echo esc_html($jsst_userticketmessage);
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
                        <?php apply_filters( 'js_support_ticket_admin_details_right_middle', jssupportticket::$jsst_data[0]->id ); ?>
                        <?php
                        if( class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){
                            $jsst_order = wc_get_order(jssupportticket::$jsst_data[0]->wcorderid);
                            $jsst_order_itemid = jssupportticket::$jsst_data[0]->wcproductid;
                            if($jsst_order){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-woocom">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Woocommerce Order",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['wcorderid']); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($jsst_order->get_id()); ?></div>
                                        </div>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Status",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo wp_kses(wc_get_order_status_name($jsst_order->get_status()), JSST_ALLOWED_TAGS); ?></div>
                                        </div>
                                        <?php
                                        if($jsst_order_itemid){
                                            $jsst_item = new WC_Order_Item_Product($jsst_order_itemid);
                                            if($jsst_item){
                                                ?>
                                                <div class="js-tkt-wc-order-item">
                                                    <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['wcproductid']); ?>:</div>
                                                    <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_item->get_name()); ?></div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Created",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_order->get_date_created()->date_i18n(wc_date_format())); ?></div>
                                        </div>
                                        <?php
                                        if(in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff()){
                                            do_action('jsst_woocommerce_order_detail_agent', $jsst_order, $jsst_order_itemid);
                                        }
                                        ?>
                                        <?php
                                        if(jssupportticket::$jsst_data[0]->uid == JSSTincluder::getObjectClass('user')->uid()){
                                            ?>
                                            <a href="<?php echo esc_url(wc_get_endpoint_url('orders','',wc_get_page_permalink('myaccount'))); ?>" class="js-tkt-wc-order-item-link">
                                                <?php echo esc_html(__("View All Orders",'js-support-ticket')); ?>
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
                            $jsst_orderid = jssupportticket::$jsst_data[0]->eddorderid;
                            $jsst_order_product = jssupportticket::$jsst_data[0]->eddproductid;
                            $jsst_order_license = jssupportticket::$jsst_data[0]->eddlicensekey;
                            if($jsst_orderid != '' && ((isset($jsst_field_array['eddlicensekey']) && class_exists('EDD_Software_Licensing')) || isset($jsst_field_array['eddorderid']) || isset($jsst_field_array['eddorderid']))){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-edd">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Easy Digital Downloads",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <?php 
                                        if (isset($jsst_field_array['eddorderid'])) {  ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['eddorderid']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($jsst_orderid); ?></div>
                                            </div>
                                            <?php
                                        }
                                        if (isset($jsst_field_array['eddorderid'])) {  ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['eddproductid']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php
                                                    if(is_numeric($jsst_order_product)){
                                                        $jsst_download = new EDD_Download($jsst_order_product);
                                                        echo wp_kses($jsst_download->post_title, JSST_ALLOWED_TAGS);
                                                    }else{
                                                        echo '-----------';
                                                    }?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        if(isset($jsst_field_array['eddlicensekey']) && class_exists('EDD_Software_Licensing')){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['eddlicensekey']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php
                                                    if($jsst_order_license != ''){
                                                        $jsst_license = EDD_Software_Licensing::instance();
                                                        $jsst_licenseid = $jsst_license->get_license_by_key($jsst_order_license);
                                                        $jsst_result = $jsst_license->get_license_status($jsst_licenseid);
                                                        if($jsst_result == 'expired'){
                                                            $jsst_result_color = 'red';
                                                        }elseif($jsst_result == 'inactive'){
                                                            $jsst_result_color = 'orange';
                                                        }else{
                                                            $jsst_result_color = 'green';
                                                        }
                                                        echo wp_kses($jsst_order_license.'&nbsp;&nbsp;(<span style="color:'.esc_attr($jsst_result_color).';font-weight:bold;text-transform:uppercase;padding:0 3px;">'.wp_kses($jsst_result, JSST_ALLOWED_TAGS).'</span>)', JSST_ALLOWED_TAGS);
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
                        if(isset($jsst_field_array['envatopurchasecode']) && in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$jsst_data[0]->envatodata)){
                            $jsst_envlicense = jssupportticket::$jsst_data[0]->envatodata;
                            if(!empty($jsst_envlicense)){
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-env">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Envato License",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <?php if(!empty($jsst_envlicense['itemname']) && !empty($jsst_envlicense['itemid'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Item",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_envlicense['itemname']).' (#'.esc_html($jsst_envlicense['itemid']).')'; ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['buyer'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Buyer",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_envlicense['buyer']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['licensetype'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License Type",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_envlicense['licensetype']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['license'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_envlicense['license']); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['purchasedate'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Purchase Date",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_envlicense['purchasedate']))); ?></div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['supporteduntil'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Supported Until",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y", jssupportticketphplib::JSST_strtotime($jsst_envlicense['supporteduntil']))); ?></div>
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
                            $jsst_linktickettoorder = true;
                            if(jssupportticket::$jsst_data[0]->paidsupportitemid > 0){
                                $jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportDetails(jssupportticket::$jsst_data[0]->paidsupportitemid);
                                if($jsst_paidsupport){
                                    $jsst_linktickettoorder = false;
                                    $jsst_nonpreminumsupport = in_array(jssupportticket::$jsst_data[0]->id,$jsst_paidsupport['ignoreticketids']) ? 1 : 0;
                                    $jsst_agentallowed = in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark Non Premium');
                                    ?>
                                    <div class="js-tkt-det-cnt js-tkt-det-pdsprt">
                                        <?php if(!$jsst_nonpreminumsupport || $jsst_agentallowed){ ?>
                                        <div class="js-tkt-det-hdg">
                                            <div class="js-tkt-det-hdg-txt">
                                                <?php echo esc_html(__("Paid Support Details",'js-support-ticket')); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!$jsst_nonpreminumsupport){ ?>
                                        <div class="js-tkt-wc-order-box">
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Order",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($jsst_paidsupport['orderid']); ?></div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Product Name",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_paidsupport['itemname']); ?></div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Credits left",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                    /* The customer's balance from the credits ledger - the
                                                       number the new-ticket form shows them - rather than
                                                       this order line's own old counter, which the two
                                                       would disagree with. */
                                                    if ($jsst_paidsupport['totalticket']==-1) {
                                                        echo esc_html(__("Unlimited",'js-support-ticket'));
                                                    } elseif (class_exists('JSSTsupportcredits')) {
                                                        echo esc_html(number_format_i18n(JSSTsupportcredits::credits(jssupportticket::$jsst_data[0]->email)));
                                                    } else {
                                                        echo esc_html($jsst_paidsupport['remainingticket']);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php if(isset($jsst_paidsupport['subscriptionid'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Subscription",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($jsst_paidsupport['subscriptionid']); ?></div>
                                            </div>
                                            <?php } ?>
                                            <?php if(isset($jsst_paidsupport['subscriptionstartdate'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Subscribed On",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php echo esc_html(date_i18n("F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_paidsupport['subscriptionstartdate']))); ?></div>
                                            </div>
                                            <?php } ?>
                                            <?php if(isset($jsst_paidsupport['expiry'])){ ?>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Support Expiry",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                        if($jsst_paidsupport['expiry']){
                                                            echo esc_html(date_i18n("F d, Y", jssupportticketphplib::JSST_strtotime($jsst_paidsupport['expiry'])));
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
                                        if($jsst_agentallowed){
                                            ?>
                                            <div class="js-tkt-wc-order-box">
                                                <div class="js-tkt-wc-order-item">
                                                    <label>
                                                        <input type="checkbox" id="nonpreminumsupport" <?php if($jsst_nonpreminumsupport) echo 'checked'; ?>>
                                                        <b><?php echo esc_html(__("Non-premium support",'js-support-ticket')); ?></b>
                                                    </label>
                                                    <?php echo wp_kses(JSSTformfield::hidden('paidsupportitemid',jssupportticket::$jsst_data[0]->paidsupportitemid), JSST_ALLOWED_TAGS) ?>
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
                            if($jsst_linktickettoorder && in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Link To Paid Support')){
                                $jsst_paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(jssupportticket::$jsst_data[0]->uid);
                                $jsst_paidsupportlist = array();
                                foreach($jsst_paidsupportitems as $jsst_row){
                                    $jsst_paidsupportlist[] = (object) array(
                                        'id' => $jsst_row->itemid,
                                        'text' => esc_html(__("Order",'js-support-ticket')).' #'.$jsst_row->orderid.', '.$jsst_row->itemname.', '. esc_html(__("Remaining",'js-support-ticket')).':'.$jsst_row->remaining.' '. esc_html(__("Out of",'js-support-ticket')).':'.$jsst_row->total,
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
                                            <?php echo wp_kses(JSSTformfield::select('paidsupportitemid',$jsst_paidsupportlist,null,esc_html(__("Select",'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                                            <button type="button" class="btn" id="paidsupportlinkticketbtn"><?php echo esc_html(__("Link",'js-support-ticket')); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <?php
                        /* The same place on the other desk. The panel drawn
                           here decides for itself whether whoever is looking is
                           an agent - this template is also the customer's own
                           view of their ticket, and a company supervisor's view
                           of a colleague's. (Roadmap 5.5-COM-01) */
                        JSSTincluder::prunedAction('jsst_after_ticket_details', jssupportticket::$jsst_data[0]);
                        ?>
                        <?php apply_filters('js_support_ticket_admin_details_right_last', jssupportticket::$jsst_data[0]->id); ?>
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
        $jsst_redirect_url = jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'ticketdetail'));
        $jsst_redirect_url = jssupportticketphplib::JSST_safe_encoding($jsst_redirect_url);
        JSSTlayout::getUserGuest($jsst_redirect_url);
    }
} else { // System is offline
    JSSTlayout::getSystemOffline();
}
?>
</div>
