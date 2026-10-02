<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
JSSTmessage::getMessage();
wp_enqueue_script('file_validate.js', JSST_PLUGIN_URL . 'includes/js/file_validate.js', array(), jssupportticket::$_config['productversion'], true);
wp_enqueue_script('jquery-ui-tabs');
wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
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

/* The ticket this page is about, or 0 when there is no ticket.

   This template builds an inline script long before it reaches the
   `isset(jssupportticket::$jsst_data[0])` guard that the rest of it uses, and
   that script interpolated `$jsst_data[0]->id` unconditionally. Opening a
   ticket the reader may not see - which is exactly what a notification link
   does when somebody is put on a ticket outside their scope - therefore
   printed two PHP warnings above the page before the polite "No Record Found"
   ever rendered:

       Undefined array key 0
       Attempt to read property "id" on null

   Resolved once here so the script has something safe to interpolate. */
$jsst_detail_ticketid = (isset(jssupportticket::$jsst_data[0]) && !empty(jssupportticket::$jsst_data[0]->id))
    ? (int) jssupportticket::$jsst_data[0]->id : 0;

// --- ZYWRAP GLOBAL SETUP ---
/* A key is no longer enough on its own. (Roadmap 4.0-AI-04) These buttons post
   to the hosted engine, so they answer to the site's AI switches like everything
   else - and the important half is that they go grey rather than firing and
   coming back with a refusal, because a button that looks live and then does
   nothing is how a working desk gets reported as broken. */
$zywrap_api_key = get_option('jsst_zywrap_api_key', '');
$zywrap_is_active = (!empty($zywrap_api_key)
    && (!class_exists('JSSTaipolicy') || JSSTaipolicy::allows(JSSTaipolicy::LANE_HOSTED)));
$zywrap_default_lang = get_option('jsst_zywrap_default_lang', 'English');
// ---------------------------

$jsst_jssupportticket_js ="
    /* The timer the Edit Time popup was opened from. */
    var jsstEditTimer = null;
            var seconds = 0;
    function checktinymcebyid(id) {
        /* The Code tab has no TinyMCE instance, and calling getContent on
           nothing threw instead of checking; read the textarea then. */
        var editor = (typeof tinymce !== 'undefined') ? tinymce.get(id) : null;
        var content = (editor && !editor.isHidden()) ? editor.getContent({format: 'text'}) : jQuery('#' + id).val();
        if (jQuery.trim(content || '') == '')
        {
            alert('". esc_html(__('Write something before posting.', 'js-support-ticket'))  ."');
            return false;
        }
        return true;
    }
	function getpremade(val) {
        /* The picker's own first option is the placeholder, and choosing it is
           not a request for anything. The endpoint already answers an empty
           string to a non-numeric id, so this changes nothing an agent sees -
           it just stops the round trip being made to find that out. */
        if (!val) { return; }
        jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'cannedresponses', task: 'getpremadeajax', ticketid: '". esc_js($jsst_detail_ticketid) ."', '_wpnonce':'". esc_attr(wp_create_nonce('get-premade-ajax')) ."'}, function (data) {
            if (data) {
                var append = jQuery('input#append_premade1:checked').length;
                if (append == 1) {
                    if(jQuery('#wp-jsticket_message-wrap').hasClass('html-active')){
                        var content = jQuery('#jsticket_message').val();
                        content = content + data;
                        jQuery('#jsticket_message').val(content);
                    }else{
                        var content = tinyMCE.get('jsticket_message').getContent();
                        content = content + data;
                        tinyMCE.get('jsticket_message').execCommand('mceSetContent', true, content);
                    }


                } else {
                    if(jQuery('#wp-jsticket_message-wrap').hasClass('html-active')){
                        jQuery('#jsticket_message').val(data);
                    }else{
                        tinyMCE.get('jsticket_message').execCommand('mceSetContent', true, data);
                    }
                }

            }
        });
    }
    // Temporary storage for the current ticket's replies for filtering
    let currentTicketAllReplies = [];
    jQuery(document).ready(function ($) {
        jQuery( 'form' ).submit(function(e) {
            /* A form posts the time from its own timer, and only once that
               timer has been started or edited - never another form's. */
            var form = jQuery(this);
            var timer = form.find('div.timer').first();
            if (timer.length && (timer.data('jsstTimerState') || 0) != 0) {
                form.find('input[name=timer_time_in_seconds]').val(timer.data('seconds'));
            }
        });
        jQuery('#tabs').tabs();
        /* The attachment counter used to be jQuery('input[type=file]').length,
           which counts every file field on the page - and this page also carries
           the internal note composer's hidden one. The append was just as wide:
           jQuery('.tk_attachment_value_wrapperform') matches both wrappers, so
           every click added a field to the note composer too and the count grew
           by two at a time. A limit of five therefore stopped the reply form at
           three. Both are scoped to the wrapper that belongs to the clicked
           button now, so the configured number is the number the admin gets. */
        jQuery('#tk_attachment_add').click(function () {
            var obj = this;
            var wrapper = jQuery(obj).closest('.js-form-field').find('.tk_attachment_value_wrapperform').first();
            var current_files = wrapper.find('input[type=\'file\']').length;
            var total_allow = ". absint(jssupportticket::$_config['no_of_attachement']) .";
            /* The onchange attribute was quoted with single quotes and its
               arguments were too, so the browser ended the attribute at the
               first inner quote and every added field lost its size and
               extension check. Double quotes outside, single quotes inside. */
            var append_text = '<span class=\"tk_attachment_value_text\"><input class=\"inputbox\" name=\"filename[]\" type=\"file\" onchange=\"uploadfile(this, \'". esc_js(jssupportticket::$_config['file_maximum_size']) ."\', \'". esc_js(jssupportticket::$_config['file_extension']) ."\');\" size=\"20\" /><span class=\"tk_attachment_remove\"></span></span>';
            if (current_files < total_allow) {
                wrapper.append(append_text);
                if ((current_files + 1) >= total_allow) {
                    jQuery(obj).hide();
                }
            } else {
                alert('". esc_html(__('File upload limit exceeds', 'js-support-ticket')) ."');
                jQuery(obj).hide();
            }
        });
        jQuery(document).delegate('.tk_attachment_remove', 'click', function (e) {
            var wrapper = jQuery(this).closest('.tk_attachment_value_wrapperform');
            jQuery(this).parent().remove();
            var current_files = wrapper.find('input[type=\'file\']').length;
            var total_allow = ". absint(jssupportticket::$_config['no_of_attachement']) .";
            if (current_files < total_allow) {
                wrapper.closest('.js-form-field').find('#tk_attachment_add').show();
            }
        });
        jQuery('a#showhidedetail').click(function (e) {
            e.preventDefault();
            var divid = jQuery(this).attr('data-divid');
            jQuery('div#' + divid).slideToggle();
            jQuery(this).find('img').toggleClass('js-hidedetail');
        });

        var height = jQuery(window).height();
        jQuery('a#showhistory').click(function (e) {
            e.preventDefault();
            jQuery('div#jsst-history-popup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        /* The Post New Internal Note link no longer opens a modal — it switches
           the composer into its internal mode and puts the caret in it. The link
           and its id are kept so the n shortcut and any existing markup still
           find it. (Roadmap 4.0-UX-03) */
        jQuery('a#int-note').click(function (e) {
            e.preventDefault();
            jsstSetComposerMode('internal', true);
        });
        jQuery('a#chng-status').click(function (e) {
            e.preventDefault();
            jQuery('div#changestatus-popup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        jQuery('a#chng-prority').click(function (e) {
            e.preventDefault();
            jQuery('div#changepriority-popup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        jQuery('.userpopup-close, div#userpopupblack').click(function (e) {
            jQuery('div#changestatus-popup').slideUp('slow', function () {
                jQuery('div#userpopupblack').hide();
            });
            jQuery('div#changepriority-popup').slideUp('slow', function () {
                jQuery('div#userpopupblack').hide();
            });
        });
        jQuery('a#chng-dept').click(function (e) {
            e.preventDefault();
            jQuery('div#changedept-popup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        jQuery('.userpopup-close, div#userpopupblack').click(function (e) {
            jQuery('div#changedept-popup').slideUp('slow', function () {
                jQuery('div#userpopupblack').hide();
            });

        });
        jQuery('a#asgn-staff').click(function (e) {
            e.preventDefault();
            jQuery('div#assignstaff-popup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        jQuery('.userpopup-close, div#userpopupblack').click(function (e) {
            jQuery('div#assignstaff-popup').slideUp('slow', function () {
                jQuery('div#userpopupblack').hide();
            });

        });
        jQuery(document).delegate('.close-merge', 'click', function (e) {
            jQuery('div#mergeticketselection').fadeOut();
            jQuery('div#popup-record-data').html('');
        });

        jQuery('div#userpopupblack,div.jsst-popup-background,.close-history,.close-credentails').click(function (e) {
            jQuery('div#userpopup, div#jsst-history-popup').slideUp('slow');
            jQuery('#usercredentailspopup').slideUp('slow');
            setTimeout(function () {
                jQuery('div#userpopupblack').hide();
                jQuery('div.jsst-popup-background').hide();
            }, 700);
        });
        ";

        //print code
        if(isset(jssupportticket::$jsst_data[0])){
            $jsst_jssupportticket_js .="
            jQuery('a#print-link').click(function (e) {
                e.preventDefault();
                var href = '". jssupportticket::makeUrl(array('jstmod'=>'ticket','jstlay'=>'printticket','jssupportticketid'=>jssupportticket::$jsst_data[0]->id,'jsstpageid'=>jssupportticket::getPageid())) ."';
                print = window.open(href, 'print_win', 'width=1024, height=800, scrollbars=yes');
            });
            ";
        }
        /* Presence and shortcut sentences, prepared here rather than inline in
           the script string below, so every placeholder can carry the
           translators note on the line above it. */
        /* translators: %s: display name of the agent viewing the ticket. */
        $jsst_presence_one = __('%s is viewing this ticket.', 'js-support-ticket');
        /* translators: %s: display name of the agent writing a reply. */
        $jsst_presence_one_replying = __('%s is replying to this ticket.', 'js-support-ticket');
        /* translators: 1: display name of an agent, 2: display name of a second agent. */
        $jsst_presence_two = __('%1$s and %2$s have this ticket open.', 'js-support-ticket');
        /* translators: 1: display name of an agent, 2: number of further agents with the ticket open. */
        $jsst_presence_many = __('%1$s and %2$s others have this ticket open.', 'js-support-ticket');
        /* translators: %s: label of the ticket action being confirmed, such as Lock or Close. */
        $jsst_shortcut_ask = __('%s now? The customer is notified by e-mail.', 'js-support-ticket');

        $jsst_jssupportticket_js .='
        jQuery(document).delegate("#ticketpopupsearch","submit", function (e) {
            var ticketid = jQuery("#ticketidformerge").val();
            var nonce = jQuery("#nonce").val();
            e.preventDefault();
            var name = jQuery("input#name").val();
            var email = jQuery("input#email").val();
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "mergeticket", task: "getTicketsForMerging", name: name, email: email,ticketid:ticketid, "_wpnonce": nonce}, function (data) {
                data=jQuery.parseJSON(data);
               if(data !== "undefined" && data !== "") {
                    jQuery("div#popup-record-data").html("");
                    jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));
                }else{
                    jQuery("div#popup-record-data").html("");
                }
            });//jquery closed
        });

        jQuery(document).delegate("#ticketidcopybtn", "click", function(){
            var temp = jQuery("<input>");
            jQuery("body").append(temp);
            temp.val(jQuery("#ticketrandomid").val()).select();
            document.execCommand("copy");
            temp.remove();
            jQuery("#ticketidcopybtn").text(jQuery("#ticketidcopybtn").attr("success"));
        });

        //non premium support function
        jQuery("#nonpreminumsupport").change(function(){
            if(jQuery(this).is(":checked")){
                if(1 || confirm("'. esc_html(__("Are you sure to mark this ticket non-premium?","js-support-ticket")) .'")){
                    markUnmarkTicketNonPremium(1);
                }else{
                    jQuery(this).removeAttr("checked");
                }
            }else{
                markUnmarkTicketNonPremium(0);
            }
        });

        jQuery("#paidsupportlinkticketbtn").click(function(){
            var ticketid = jQuery("#ticketid").val();
            var paidsupportitemid = jQuery("#paidsupportitemid").val();
            if(paidsupportitemid > 0){
                jQuery.post(ajaxurl, {action: "jsticket_ajax",jstmod: "paidsupport", task: "linkTicketPaidSupportAjax", ticketid: ticketid, paidsupportitemid:paidsupportitemid, "_wpnonce":"'. esc_attr(wp_create_nonce("link-ticket-paidsupport-ajax")) .'"}, function (data) {
                    window.location.reload();
                });
            }
        }); 

        // AI-Powered Reply

        // Get DOM elements with IDs using jQuery selectors
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
           (Roadmap 4.0-CORE-03)

           Everything it needs is already in the page - the titles are the list
           items - so this is a filter over the DOM and nothing else. No
           request is made until an agent actually picks one, which is the same
           moment the select made one.

           Written to the combobox pattern rather than to a div that looks like
           one: the input owns `aria-activedescendant` while the list is open,
           so a screen reader reads the highlighted option as the arrow keys
           move over it, and the input keeps focus throughout - which is what
           lets typing carry on refining after arrowing partway down. */
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

            /* Which one the arrows are on. Held as a position in the *visible*
               set rather than as an index into every option, because the set
               changes under it on every keystroke. */
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
                /* Only when it has gone out of sight: scrolling a list that is
                   already showing the item makes it jump under the pointer. */
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
                    /* Anywhere in the title, not only at the front. Matching the
                       start is what the select already did and what sent
                       somebody to this box in the first place. */
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
                    /* Only when the list is open with something on it. Otherwise
                       Enter belongs to the form this box sits inside, and
                       swallowing it would stop the reply being sent. */
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

            /* mousedown, not click: a click fires after the input has already
               lost focus, and the blur below would have closed the list out
               from under the pointer. */
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
                /* After the list has had its chance at the event. */
                setTimeout(function () {
                    if (!list.is(":hover")) { close(); }
                }, 120);
            });
        })();

        /* Saying what happened, without stopping to be told it happened.
           (Roadmap 6.0-AI-01)

           Every one of these messages used to be a modal: a full-screen dark
           overlay, a 384px white card and an OK button, drawn over the whole of
           wp-admin to report that a line of text had been put in a box the
           agent was looking at. Confirming a success costs the same click as
           the action did, and an agent working a queue pays it on every
           suggestion they take.

           So the two are separated by what the person needs. **A success is
           confirmed on the control that did it** - the button says what it did
           and goes back to its own label two seconds later, which is the
           pattern the per-reply toggle below already uses, and the change of
           label on the focused button is what a screen reader reads out. **A
           failure goes to a status line** that stays until something replaces
           it, because a message that removes itself is no use for something
           that needs acting on. The line is a live region, so it is announced
           when it appears rather than only when somebody happens to look.

           Nothing here blocks, so nothing here has to be dismissed. */
        function jsstSuggestNote(message) {
            jQuery("#js-ticket-suggest-status").text(message || "");
        }

        function jsstConfirmOn(button, label) {
            jsstSuggestNote("");
            if (!button || !button.length) {
                return;
            }
            /* The label the button came with, kept the first time it is
               borrowed. A
               second click while the confirmation is showing must not save
               "Copied" as the thing to go back to. */
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
            if (typeof tinyMCE !== "undefined" && tinyMCE.get("jsticket_message") && !$("#wp-jsticket_message-wrap").hasClass("html-active")) {
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
            console.log(selectedFilter);
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

            /* The per-reply toggle. Two states, so it posts 0 or 2 and swaps its
               own label in place - the only thing that changes is whether this
               reply may be offered as an example later, so there is nothing to
               reload. Delegated, because the thread is re-rendered by ajax. */
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

        /* The ticket-level mode, now a select. One change event instead of
           three click handlers, and the value posted is the option the agent
           actually read rather than a data attribute on a button. */
        jQuery(document).on("change", ".js-ticket-ai-mode-select", function() {
            var sel = jQuery(this);
            jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "reply",
                task: "markedAsAiPoweredReply", status: parseInt(sel.val(), 10),
                id: sel.data("id"), type: sel.data("type"),
                "_wpnonce":"'.esc_attr(wp_create_nonce("ai-powered-reply")).'"});
        });

        /* The three-button segmented control this handled is gone from both
           ticket-detail screens - wp-admin lost it under 6.0-AI-01 and the
           portal under 4.5-FE-01, both replaced by .js-ticket-ai-mode-select
           above. Nothing in core or in any add-on renders the class any more,
           so the handler went with the markup rather than sitting here binding
           to nothing. */

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
            jQuery.post(ajaxurl, {action: "jsticket_ajax", ticketSubject: ticketSubject, ticketId: ticketId, filter: selectedFilter, jstmod: "'.$jsst_jstmod.'", task: "getAiSuggestedReplies", "_wpnonce":"'. esc_attr(wp_create_nonce("check-smart-reply")).'"}, function (data) {
                if(data) {
                    displaySuggestedReplies(data);
                } else {
                    /* This branch never cleared the spinner: the OK button on
                       the modal did, which is why the panel sat spinning until
                       somebody dismissed the message about it. */
                    jsReplyHideLoading();
                    jsstSuggestNote("'.esc_js(__("No suggestions could be fetched. Try again.", "js-support-ticket")).'");
                }
            });
        }

        // Function to display matching tickets
            /* One list, of answers. (Roadmap 6.0-AI-01)
             *
             * The panel used to show matching *tickets* - a reference and a
             * subject, which an agent had to choose from before seeing a word
             * of any answer, then read in a second panel, then append. The
             * search returns the replies now, each carrying the ticket it came
             * from as its context, so choosing and reading are one step and
             * there is nothing to go back from.
             *
             * displayMatchingTickets() and displayTicketReplies() below are
             * left as they are: the ask-a-question flow still uses both. */
            function displaySuggestedReplies(suggestions) {
                if (typeof suggestions === "string") {
                    try { suggestions = JSON.parse(suggestions); } catch (e) { suggestions = []; }
                }
                if (!Array.isArray(suggestions)) { suggestions = []; }

                matchingTicketsList.empty();
                selectedTicketRepliesSection.addClass("js-ticket-hidden");
                jQuery(".js-ticket-container").show();
                /* The ticket filter only means anything when the answers came
                   from tickets. With the corpus answering it was still on
                   screen saying "From any ticket" above a passage out of the
                   knowledge base - a control that described the wrong thing
                   and did nothing to what was shown. */
                var fromTickets = suggestions.length ? (suggestions[0].from !== "corpus") : true;
                jQuery("#js-ticket-tickets-filter").closest(".js-ticket-filter-group").toggle(fromTickets);

                if (suggestions.length === 0) {
                    matchingTicketsList.html("<p class=\"js-ticket-id\">'.esc_js(__("Nothing similar has been answered here yet.", "js-support-ticket")).'</p>");
                    jsReplyHideLoading();
                    matchingTicketsSection.removeClass("js-ticket-hidden");
                    return;
                }

                jQuery.each(suggestions, function (i, s) {
                    /* Where it came from, in words. The retriever hands back
                       `source_type` ("kb") and `ref` ("KB-1"); neither belongs
                       in front of somebody choosing a sentence to send. The
                       source is named, the document is named, and the document
                       links to itself where it has an address, so it can be
                       read in full before it is trusted. */
                    var when = s.created ? new Date(String(s.created).replace(" ", "T")).toLocaleDateString() : "";
                    var origin = s.source || "";
                    if (s.reference) { origin += " #" + s.reference; }
                    if (when) { origin += (origin ? " · " : "") + when; }
                    /* Deliberately NOT js-ticket-list-item. That class carries the
                       old click handler - pick a ticket, fetch its replies - and a
                       suggestion is not a ticket: clicking one asked the server for
                       the replies of a knowledge base article and put "Error
                       fetching replies." on the screen. It also carries the card
                       styling this flat list replaced. */
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

            $.each(matchingTickets, (index, ticket) => {
                const listItem = $("<li></li>")
                    .addClass("js-ticket-list-item")
                    .data("ticket-id", ticket.id) // Store ticket ID in data attribute
                    .html(`<p class="js-ticket-id">'.__("Ticket ID", "js-support-ticket").': '.' ${(ticket.ticketid)}</p><p class="js-ticket-title">${(ticket.text)}</p><p class="js-ticket-id">${(ticket.message)}</p>`);
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

            jQuery("#js-ticket-selected-ticket-replies-title").text(`'.__("Replies for:", "js-support-ticket").' ${ticket.text}`);
            selectedTicketRepliesContent.empty(); // Clear previous replies

            // Now safe to check length
            if (replies.length === 0) {
                selectedTicketRepliesContent.html(`<p class="js-ticket-id">'.__("No replies found for this ticket.", "js-support-ticket").'</p>`);
            } else {
                $.each(replies, (index, reply) => {
                    // Add null checks for reply properties
                    const replyId = reply?.id || __("N/A", "js-support-ticket");
                    const replyText = reply?.text || __("No content", "js-support-ticket"); 
                    const replyName = reply?.name || __("No content", "js-support-ticket");
                    const replyTimestamp = reply?.timestamp ? new Date(reply.timestamp).toLocaleString() : __("No date", "js-support-ticket");

                    const replyDiv = $("<div></div>")
                        .addClass("js-ticket-reply-item")
                        .html(`
                            <div class="js-ticket-reply-header">
                                <span class="js-ticket-reply-id">'.__("Reply By", "js-support-ticket").': ${escapeHtml(replyName)}</span>
                                <span class="js-ticket-reply-timestamp">${replyTimestamp}</span>
                            </div>
                            <div class="js-ticket-reply-text">
                                ${(replyText)}
                            </div>
                            <div class="js-ticket-reply-actions">
                                <button class="js-ticket-reply-action-btn copy-btn" data-reply-content="${escapeHtml(replyText)}">'.__('Copy', 'js-support-ticket').'</button>
                                <button class="js-ticket-reply-action-btn append-btn" data-reply-content="${escapeHtml(replyText)}">'.__('Append', 'js-support-ticket').'</button>
                            </div>
                        `);
                    selectedTicketRepliesContent.append(replyDiv);
                });

                // Attach event listeners
                selectedTicketRepliesContent.find(".copy-btn").on("click", function(e) {
                    e.preventDefault();
                    copyToClipboard($(this).data("reply-content"));
                });
                
                selectedTicketRepliesContent.find(".append-btn").on("click", function(e) {
                    e.preventDefault();
                    appendToReplyArea($(this).data("reply-content"));
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
    });

    function markUnmarkTicketNonPremium(mark){
        var ticketid = jQuery("#ticketid").val();
        var paidsupportitemid = jQuery("#paidsupportitemid").val();
        jQuery.post(ajaxurl, {action: "jsticket_ajax",jstmod: "paidsupport", task: "markUnmarkTicketNonPremiumAjax", status: mark, ticketid: ticketid, paidsupportitemid:paidsupportitemid, "_wpnonce":"'. esc_attr(wp_create_nonce("mark-unmark-ticket-nonpremium-ajax")) .'"}, function (data) {
            window.location.reload();
        });
    }

    function actionticket(action) {
        /*  Action meaning
         * 1 -> Change Priority
         * 2 -> Close Ticket
         */
        if(action == 1){
            jQuery("#priority").val(jQuery("#prioritytemp").val());
        }
        jQuery("input#actionid").val(action);
        jQuery("form#adminTicketform").submit();
    }
    function getmergeticketid(mergeticketid, mergewithticketid, mergeNonce){
        if(mergewithticketid == 0){
            mergewithticketid =  jQuery("#mergeticketid").val();
        }else{
            jQuery("#mergeticketid").val(mergewithticketid);
        }
        if(mergeticketid == mergewithticketid){
            alert("' . esc_js(__('The primary ticket must be different from the ticket being merged.', 'js-support-ticket')) . '");
            return false;
        }
        jQuery("#mergeticketselection").hide();
        getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce);
    }
    /* The two composer modes. (Roadmap 4.0-UX-03)

       Declared as a global function because the internal-note link, the n
       shortcut and the mode tabs all reach for it, and they are bound in
       different ready() blocks.

       Switching mode only ever shows a different panel. Neither form is touched,
       so nothing an agent has already typed into the other one is lost - and
       because they are two separate forms posting to two separate actions, text
       written in internal mode cannot be submitted as a public reply. That
       separation is the safety property; the colour is what makes it visible. */
    function jsstSetComposerMode(mode, focusEditor) {
        var composer = jQuery("#jsst-composer");
        if (!composer.length) { return; }
        if (mode !== "internal" || !composer.find(".jsst-composer-panel-internal").length) { mode = "public"; }
        composer.removeClass("jsst-composer-public jsst-composer-internal").addClass("jsst-composer-" + mode);
        composer.find(".jsst-composer-mode").each(function(){
            var btn = jQuery(this);
            var on = btn.attr("data-mode") === mode;
            btn.toggleClass("active", on).attr("aria-selected", on ? "true" : "false");
        });
        if (focusEditor) {
            /* Scroll the composer into view first: in internal mode especially,
               an agent needs to see the banner they are about to type under. */
            var node = composer.get(0);
            if (node && node.scrollIntoView) {
                var host = node.parentNode;
                if (host && host.style) {
                    var room = Math.ceil(window.innerHeight - node.getBoundingClientRect().height - 40);
                    host.style.paddingBottom = (room > 0) ? (room + "px") : "";
                }
                node.scrollIntoView({block: "start"});
            }
            var editorid = (mode === "internal") ? "internalnote" : "jsticket_message";
            if (window.tinymce && window.tinymce.get(editorid)) {
                window.tinymce.get(editorid).focus();
            } else {
                jQuery("#" + editorid).focus();
            }
        }
    }
    jQuery(document).ready(function(){
        jQuery("#jsst-composer").on("click", ".jsst-composer-mode", function(){
            jsstSetComposerMode(jQuery(this).attr("data-mode"), true);
        });
        /* Read the starting mode off the element the server rendered rather than
           assuming public: a light agent has no public panel, and the composer
           comes back marked internal. (Roadmap 4.0-SEC-04) */
        jsstSetComposerMode(jQuery("#jsst-composer").hasClass("jsst-composer-internal") ? "internal" : "public", false);
    });

    /* What is currently written in one of the two editors.

       Shared, because the drafts autosave and the presence heartbeat both need
       it and TinyMCE is the reason it is not one line: while the visual editor
       is active the textarea holds whatever was there when it was initialised,
       not what the agent has typed. (Roadmap 4.0-UX-04, 4.0-UX-05) */
    function jsstEditorBody(id){
        if (window.tinymce && window.tinymce.get(id)) { return window.tinymce.get(id).getContent(); }
        var el = document.getElementById(id);
        return el ? el.value : "";
    }

    /* Drafts. (Roadmap 4.0-UX-04)

       Two layers with different failure modes. The browser copy is written on
       every keystroke pause and survives a crash or a stray back button; the
       server copy goes every 20 seconds and is the only one that survives the
       browser itself.

       Nothing is ever pasted into the composer automatically. A recovered draft
       is offered and the agent chooses, because silently restoring abandoned
       text is how a half-finished reply gets sent. */
    jQuery(document).ready(function(){
        var composer = jQuery("#jsst-composer");
        if (!composer.length) { return; }
        var ticketid = jQuery("#jsst-draft-ticketid").val();
        var nonce = jQuery("#jsst-draft-nonce").val();
        if (!ticketid) { return; }
        var editors = {"public": "jsticket_message", "internal": "internalnote"};
        var localKey = function(mode){ return "jsstDraft:" + ticketid + ":" + mode; };
        var lastSent = {"public": null, "internal": null};

        function bodyOf(mode){ return jsstEditorBody(editors[mode]); }
        function setBody(mode, html){
            var id = editors[mode];
            if (window.tinymce && window.tinymce.get(id)) { window.tinymce.get(id).setContent(html); return; }
            var el = document.getElementById(id);
            if (el) { el.value = html; }
        }
        function keepLocally(mode){
            try { window.localStorage.setItem(localKey(mode), bodyOf(mode)); } catch (err) {}
        }
        function keepOnServer(mode){
            var body = bodyOf(mode);
            if (body === lastSent[mode]) { return; }
            lastSent[mode] = body;
            jQuery.post(ajaxurl, {action: "jsst_save_draft", ticketid: ticketid, mode: mode, body: body, _wpnonce: nonce});
        }
        function currentMode(){ return composer.hasClass("jsst-composer-internal") ? "internal" : "public"; }

        /* The browser copy, on a short idle after typing stops. */
        var typingTimer = null;
        jQuery(document).on("keyup change", "#jsticket_message, #internalnote", function(){
            var mode = currentMode();
            window.clearTimeout(typingTimer);
            typingTimer = window.setTimeout(function(){ keepLocally(mode); }, 800);
        });
        /* TinyMCE writes into an iframe, so its own event is the only reliable one. */
        if (window.tinymce) {
            jQuery.each(editors, function(mode, id){
                var ed = window.tinymce.get(id);
                if (ed) { ed.on("keyup change", function(){ keepLocally(mode); }); }
            });
        }

        /* The server copy. */
        window.setInterval(function(){
            keepOnServer("public");
            if (composer.find(".jsst-composer-panel-internal").length) { keepOnServer("internal"); }
        }, 20000);

        /* Offer, never apply. */
        composer.on("click", ".jsst-draft-restore-btn", function(){
            var row = jQuery(this).closest(".jsst-draft-restore");
            var mode = row.attr("data-mode");
            jsstSetComposerMode(mode, true);
            setBody(mode, row.attr("data-body"));
            row.remove();
        });
        composer.on("click", ".jsst-draft-discard-btn", function(){
            var row = jQuery(this).closest(".jsst-draft-restore");
            var mode = row.attr("data-mode");
            jQuery.post(ajaxurl, {action: "jsst_discard_draft", ticketid: ticketid, mode: mode, _wpnonce: nonce});
            try { window.localStorage.removeItem(localKey(mode)); } catch (err) {}
            row.remove();
        });

        /* Submitting means it is no longer a draft. The server side clears its
           own copy; this clears the browser copy so a refresh afterwards does
           not offer the text back. */
        composer.on("submit", "form", function(){
            try {
                window.localStorage.removeItem(localKey("public"));
                window.localStorage.removeItem(localKey("internal"));
            } catch (err) {}
        });
    });

    /* Who else is on this ticket. (Roadmap 4.0-UX-05)

       Two warnings, because they catch different collisions. The bar says who
       else has the ticket open and whether they are writing - that is a warning
       in time to stop. The stale notice says a reply landed after this page was
       drawn - that is the one that matters, because it catches the colleague who
       arrived, answered and left inside the gap between two heartbeats, and the
       agent who has had the tab open since this morning.

       Nothing here blocks the reply. An agent who has read the warning and still
       means to send is usually right, and a confirm() on every submit would be
       ignored within a week. */
    jQuery(document).ready(function(){
        var composer = jQuery("#jsst-composer");
        var nonceEl = jQuery("#jsst-presence-nonce");
        var ticketid = jQuery("#jsst-draft-ticketid").val();
        /* Absent for anyone not allowed to be told, so this simply does not run
           rather than heartbeating its way to a 403 every half minute. */
        if (!composer.length || !nonceEl.length || !ticketid) { return; }

        var nonce = nonceEl.val();
        var bar = composer.find(".jsst-presence").not(".jsst-presence-stale").first();
        var stale = composer.find(".jsst-presence-stale").first();
        var drawnAt = parseInt(jQuery("#jsst-presence-latestreply").val(), 10) || 0;
        var warned = false;
        var editors = {"public": "jsticket_message", "internal": "internalnote"};

        /* Whole sentences rather than assembled fragments, because word order is
           not the same in every language. Two people are named; three or more
           become a name and a count, since a bar listing six names is no longer
           a bar. */
        var tplOne = "'. esc_js($jsst_presence_one) .'";
        var tplOneReplying = "'. esc_js($jsst_presence_one_replying) .'";
        var tplTwo = "'. esc_js($jsst_presence_two) .'";
        var tplMany = "'. esc_js($jsst_presence_many) .'";
        var tplAlsoReplying = "'. esc_js(__('One of them is replying.', 'js-support-ticket')) .'";

        /* Function replacements, so a display name containing a dollar sign is
           not read as a backreference. */
        function fill(tpl, values){
            var out = tpl;
            jQuery.each(values, function(i, value){
                out = out.replace(i === 0 ? /%(1\$)?s/ : /%2\$s/, function(){ return value; });
            });
            return out;
        }

        /* Writing, not merely present. Tags are stripped because an empty
           TinyMCE still reports a paragraph and a line break. */
        function isReplying(){
            var mode = composer.hasClass("jsst-composer-internal") ? "internal" : "public";
            var text = jsstEditorBody(editors[mode]).replace(/<[^>]*>/g, "").replace(/&nbsp;/g, " ");
            return jQuery.trim(text) !== "";
        }

        function render(others){
            if (!others.length) { bar.hide().empty(); return; }
            var names = [];
            var replying = false;
            jQuery.each(others, function(i, other){
                names.push(other.name);
                if (other.state === "replying") { replying = true; }
            });
            var text;
            if (names.length === 1) {
                text = fill(replying ? tplOneReplying : tplOne, [names[0]]);
            } else {
                text = (names.length === 2)
                    ? fill(tplTwo, [names[0], names[1]])
                    : fill(tplMany, [names[0], names.length - 1]);
                if (replying) { text = text + " " + tplAlsoReplying; }
            }
            bar.text(text).toggleClass("jsst-presence-replying", replying).show();
        }

        function beat(){
            /* A background tab is not somebody about to reply. Skipping the beat
               lets the entry expire, so they drop off the bar everybody else
               sees. */
            if (document.hidden) { return; }
            jQuery.post(ajaxurl, {
                action: "jsst_presence",
                ticketid: ticketid,
                state: isReplying() ? "replying" : "viewing",
                _wpnonce: nonce
            }, function(res){
                if (!res || !res.success || !res.data) { return; }
                render(res.data.others || []);
                if (!warned && (parseInt(res.data.latestReply, 10) || 0) > drawnAt) {
                    warned = true;
                    stale.show();
                }
            });
        }

        beat();
        window.setInterval(beat, 30000);

        /* Coming back to the tab beats at once rather than waiting out the rest
           of the interval. A ticket opened in a background tab and read half a
           minute later would otherwise show nothing at the moment the agent
           starts reading it, which is exactly the moment it has to be right. */
        jQuery(document).on("visibilitychange", function(){
            if (!document.hidden) { beat(); }
        });

        stale.on("click", ".jsst-presence-reload", function(){ window.location.reload(); });

        /* Leaving the page. sendBeacon because an ordinary request issued during
           unload is cancelled as often as it is delivered; if the browser has no
           sendBeacon the entry expires on its own in a little over a minute. */
        jQuery(window).on("beforeunload", function(){
            if (!navigator.sendBeacon || !window.FormData) { return; }
            var payload = new FormData();
            payload.append("action", "jsst_presence");
            payload.append("ticketid", ticketid);
            payload.append("leaving", "1");
            payload.append("_wpnonce", nonce);
            navigator.sendBeacon(ajaxurl, payload);
        });
    });

    /* i and l take effect the moment the key is pressed, and both send the
       customer an e-mail - lock/unlock through mail template 6/7, in progress
       through 9. Every other shortcut only opens a panel, where the agent still
       has to confirm. The ticket state is easy to put back; the e-mail already
       sitting in the customer inbox is not, so these two ask first.
       Clicking the button is a deliberate act and stays unconfirmed.
       (Roadmap 4.0-UX-03) */
    function jsstConfirmShortcut(selector){
        var button = jQuery(selector).first();
        /* Nothing on screen means the action is not available to this agent or
           not valid for this ticket. Stay silent rather than ask about an
           action that would not happen anyway. */
        if (!button.length) { return; }
        /* Named from the button that actually rendered, so the question reads
           correctly for lock and unlock without hardcoding either. */
        var label = jQuery.trim(button.attr("title") || button.find("span").first().text());
        var ask = "' . esc_js($jsst_shortcut_ask) . '".replace("%s", label);
        if (window.confirm(ask)) { button.click(); }
    }

    /* Keyboard access to the ticket actions. (Roadmap 4.0-CORE-05)
       Single keys, ignored while the agent is typing and while a popup is open,
       so they never fight with the reply editor. Each shortcut activates the
       control that is actually on screen, so a key does nothing when the action
       is not available to this agent or not valid for this ticket. */
    jQuery(document).ready(function(){
        var shortcuts = {
            /* r and n are now the two halves of the same question - which mode
               is the composer in - rather than one focusing a box and the other
               opening a modal. (Roadmap 4.0-UX-03) */
            r: function(){ jsstSetComposerMode("public", true); },
            /* The only two that act at once rather than opening a panel, so
               they are the only two that ask first. */
            i: function(){ jsstConfirmShortcut(".jsst-shortcut-inprogress"); },
            l: function(){ jsstConfirmShortcut(".jsst-shortcut-lock"); },
            h: function(){ jQuery("a#showhistory").first().click(); },
            n: function(){ jQuery("a#int-note").first().click(); },
            m: function(){ jQuery("a#mergeticket").first().click(); },
            p: function(){ jQuery("a#chng-prority").first().click(); },
            s: function(){ jQuery("a#chng-status").first().click(); }
        };
        jQuery(document).on("keydown", function(e){
            if (e.ctrlKey || e.metaKey || e.altKey) { return; }
            var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : "";
            if (tag === "input" || tag === "textarea" || tag === "select" || (e.target && e.target.isContentEditable)) { return; }
            if (jQuery(".jsst-popup-wrapper:visible, #userpopup:visible").length) { return; }
            var key = (e.key || "").toLowerCase();
            if (shortcuts[key]) {
                e.preventDefault();
                shortcuts[key]();
            }
        });
    });
    /* Multi-select merge: every ticket ticked in the candidate list is merged
       into the ticket being viewed. (Roadmap 4.0-CORE-04) */
    function jsstPreviewMergeSelection(primaryticket, previewNonce){
        var picked = [];
        jQuery(".jsst-merge-source:checked").each(function(){
            picked.push(jQuery(this).val());
        });
        if(picked.length === 0){
            alert("' . esc_js(__('Tick at least one ticket to merge into this one.', 'js-support-ticket')) . '");
            return false;
        }
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "mergeticket", task: "previewMergeSelection", primaryticket: primaryticket, sources: picked.join(","), isadmin: 1, "_wpnonce": previewNonce}, function (data) {
            if(data){
                data=jQuery.parseJSON(data);
                jQuery("div#popup-record-data").html("");
                jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));
            }
        });
    }

    function getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce){
        jQuery.post(ajaxurl, {action: "jsticket_ajax",jstmod: "mergeticket", task: "getLatestReplyForMerging", mergeid:mergeticketid,mergewith:mergewithticketid,isadmin:1, "_wpnonce": mergeNonce}, function (data) {
            if(data){
                data=jQuery.parseJSON(data);
                jQuery("div#popup-record-data").html("");
                jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));
            }
        });
    }

    function closePopup(){
        setTimeout(function () {
            jQuery("div.jsst-popup-background").hide();
            jQuery("div#userpopupblack").hide();
            }, 700);

        jQuery("div.jsst-popup-wrapper").slideUp("slow");
        jQuery("div#userpopupforchangestatus").slideUp("slow");
        jQuery("div#userpopupforchangepriority").slideUp("slow");
        jQuery("div#userpopup").slideUp("slow");


    }
    function updateticketlist(pagenum,ticketid,nonce){
        /* The search terms go with the page number. They were left out, so
           page 2 of a filtered list was a request with no filter in it at
           all - the model read no name and no email, answered with every
           mergeable ticket, and re-rendered the two search boxes from the
           same empty values, so the filter did not merely stop applying, it
           visibly cleared. Read from the inputs rather than passed down
           through the pagination links, because those are built by the
           model and would have to carry the terms through the HTML to get
           here; the boxes are on screen and already hold what was typed. */
        var name = jQuery("input#name").val() || "";
        var email = jQuery("input#email").val() || "";
        jQuery.post(ajaxurl, {action: "jsticket_ajax",jstmod: "mergeticket", task: "getTicketsForMerging", name: name, email: email, ticketid:ticketid,ticketlimit:pagenum, "_wpnonce": nonce}, function (data) {
            if(data){
                console.log(data);
                data=jQuery.parseJSON(data);
                jQuery("div#popup-record-data").html("");
                jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));
            }
        });
    }

    function showPopupAndFillValues(id,pfor,nonce) {
        if(pfor == 1){
            jQuery.post(ajaxurl, {action: "jsticket_ajax", val: id, jstmod: "reply", task: "getReplyDataByID", "_wpnonce": nonce}, function (data) {
                if (data) {
                    d = jQuery.parseJSON(data);
                    tinyMCE.get("jsticket_replytext").execCommand("mceSetContent", false, jsstDecodeHTML(d.message));
                    jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Edit Reply","js-support-ticket")) .'");
                    jQuery("form#jsst-time-edit-form").hide();
                    jQuery("form#jsst-note-edit-form").hide();
                    jQuery("div.edit-time-popup").hide();
                    jQuery("form#jsst-reply-form").show();
                    jQuery("input#reply-replyid").val(id);
                    jQuery("div.jsst-popup-background").show();
                    jQuery("div.jsst-merge-popup-wrapper").slideDown("slow");
                }
            });
        }else if(pfor == 2){
            jQuery.post(ajaxurl, {action: "jsticket_ajax", val: id, jstmod: "timetracking", task: "getTimeByReplyID", "_wpnonce": nonce}, function (data) {
                if (data) {
                    d = jQuery.parseJSON(data);
                    jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Edit Time","js-support-ticket")) .'");
                    jQuery("form#jsst-reply-form").hide();
                    jQuery("form#jsst-note-edit-form").hide();
                    jQuery("div.system-time-div").hide();
                    jQuery("div.edit-time-popup").hide();
                    jQuery("form#jsst-time-edit-form").show();
                    jQuery("input#reply-replyid").val(id);
                    jQuery("div.jsst-popup-background").show();
                    jQuery("div.jsst-merge-popup-wrapper").slideDown("slow");
                    jQuery("input#edited_time").val(d.time);
                    jQuery("textarea#edit_reason").text(jsstDecodeHTML(d.desc));
                    if(d.conflict == 1){
                        jQuery("div.system-time-div").show();
                        jQuery("input#time-confilct").val(d.conflict);
                        jQuery("input#systemtime").val(d.systemtime);
                        jQuery("select#time-confilct-combo").val(0);
                    }
                }
            });
        }else if(pfor == 3){
            jQuery.post(ajaxurl, {action: "jsticket_ajax", val: id, jstmod: "note", task: "getTimeByNoteID", "_wpnonce": nonce}, function (data) {
                if (data) {
                    d = jQuery.parseJSON(data);
                    jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Edit Time","js-support-ticket")) .'");
                    jQuery("form#jsst-reply-form").hide();
                    jQuery("form#jsst-note-edit-form").show();
                    jQuery("form#jsst-time-edit-form").hide();
                    jQuery("div.system-time-div").hide();
                    jQuery("div.edit-time-popup").hide();
                    jQuery("input#note-noteid").val(id);
                    jQuery("div.jsst-popup-background").show();
                    jQuery("div.jsst-merge-popup-wrapper").slideDown("slow");
                    jQuery("input#edited_time").val(d.time);
                    jQuery("textarea#edit_reason").text(jsstDecodeHTML(d.desc));
                    if(d.conflict == 1){
                        jQuery("div.system-time-div").show();
                        jQuery("input#time-confilct").val(d.conflict);
                        jQuery("input#systemtime").val(d.systemtime);
                        jQuery("select#time-confilct-combo").val(0);
                    }
                }
            });
        }else if(pfor == 4){
            jQuery.post(ajaxurl, {action: "jsticket_ajax", ticketid: id, jstmod: "mergeticket", task: "getTicketsForMerging", "_wpnonce": nonce}, function (data) {
                if (data) {
                    data=jQuery.parseJSON(data);
                    jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Merge Ticket","js-support-ticket")) .'");
                    jQuery("div#popup-record-data").html("");
                    jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));

                }
            });
        }

         return false;
    }

    /* The reply and the internal note each have their own timer, and every
       function here works on the one next to the button that was pressed.
       They used to select every timer on the page, so starting one started
       both, and a reply could post the time an agent had started for a note.
       A timer keeps its own state: 0 not started, 1 running or paused, 2
       stopped. */
    function jsstTimerBox(el) {
        return jQuery(el).closest(".timer-right");
    }

    function changeTimerStatus(val, el) {
        var box = jsstTimerBox(el);
        var timer = box.find("div.timer");
        var state = timer.data("jsstTimerState") || 0;
        if(state == 2){// to handle stopped timer
                return;
        }
        var buttons = box.find("span.timer-button");
        if(!buttons.filter(".cls_"+val).hasClass("selected")){
            buttons.removeClass("selected");
            buttons.filter(".cls_"+val).addClass("selected");
            if(val == 1){
                if(state == 0){
                    timer.timer({format: "%H:%M:%S"});
                }
                timer.data("jsstTimerState", 1);
                timer.timer("resume");
            }else if(val == 2) {
                 timer.timer("pause");
            }else{
                 timer.timer("remove");
                timer.data("jsstTimerState", 2);
            }
        }
    }

    function showEditTimerPopup(el){
        var box = jsstTimerBox(el);
        jsstEditTimer = box.find("div.timer");
        jQuery("form#jsst-time-edit-form").hide();
        jQuery("form#jsst-reply-form").hide();
        jQuery("form#jsst-note-edit-form").hide();
        jQuery("div.edit-time-popup").show();
        box.find("span.timer-button").removeClass("selected");
        if((jsstEditTimer.data("jsstTimerState") || 0) != 0){
            jsstEditTimer.timer("pause");
        }
        ex_val = jsstEditTimer.html();
        jQuery("input#edited_time").val("");
        jQuery("input#edited_time").val(ex_val.trim());
        jQuery("div.jsst-popup-background").show();
        jQuery("div.jsst-merge-popup-wrapper").slideDown("slow");
        jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Edit Time","js-support-ticket")) .'");
    }

    function updateTimerFromPopup(){
        var timer = jsstEditTimer ? jsstEditTimer : jQuery("div.timer").first();
        val = jQuery("input#edited_time").val();
        arr = val.split(":", 3);
        timer.html(val);
        jQuery("div.jsst-popup-background").hide();
        jQuery("div.jsst-popup-wrapper").slideUp("slow");
        seconds = parseInt(arr[0])*3600 + parseInt(arr[1])*60 + parseInt(arr[2]);
        if(seconds < 0){
            seconds = 0;
        }
        timer.timer("remove");
        timer.timer({
            format: "%H:%M:%S",
            seconds: seconds,
        });
        timer.timer("pause");
        timer.data("jsstTimerState", 1);
        desc = jQuery("textarea#t_desc").val();
        timer.closest("form").find("input[name=timer_edit_desc]").val(desc);
    }

    /* Delegated, not bound directly. There are three `.jsst-popup-background`
       divs on this page and `.click()` at load time only ever reached the two
       that existed then - the third is added with the merge dialog, so it was
       the ONE backdrop a reader can actually click and the only one with no
       handler. Clicking it did nothing: the dialog stayed open. Delegating
       from `document` catches backdrops added at any time.
       (The internal-note popup is gone; there is nothing to exclude any
       more. Roadmap 4.0-UX-03) */
    jQuery(document).on("click", "div.popup-header-close-img,div.jsst-popup-background,input#cancel", function (e) {
        jQuery("div.jsst-popup-wrapper").slideUp("slow");
        jQuery("div.jsst-merge-popup-wrapper").slideUp("slow");
        setTimeout(function () {
            jQuery("div.jsst-popup-background").hide();
        }, 700);
    });

    function resetMergeFrom(nonce) {
        var ticketid = jQuery("#ticketidformerge").val();
        var name = "";
        var email = "";
        jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "mergeticket", task: "getTicketsForMerging", name: name, email: email,ticketid:ticketid, "_wpnonce": nonce}, function (data) {
            data=jQuery.parseJSON(data);
           if(data !== "undefined" && data !== "") {
                jQuery("div#popup-record-data").html("");
                jQuery("div#popup-record-data").html(jsstDecodeHTML(data["data"]));
            }else{
                jQuery("div#popup-record-data").html("");
            }
        });//jquery closed
    }

    // smooth scroll
    jQuery(document).ready(function(){
        jQuery("a.smooth-scroll").on("click", function(e) {
            e.preventDefault();
            var anchor = jQuery(this);
            jQuery("html, body").stop().animate({
                scrollTop: jQuery(anchor.attr("href")).offset().top - 10
            }, 1000);
        });
        jQuery("span.js-ticket-thread-read-status-wrp").hover(
            function(e){
                jQuery(this).find("span.js-ticket-thread-read-status-detail").css("display","inline-block");
            },
            function(e){
                jQuery(this).find("span.js-ticket-thread-read-status-detail").css("display","none");
            }
        );
    })
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);

$jsst_yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '0', 'text' => esc_html(__('No', 'js-support-ticket')))
);
?>
<div id="black_wrapper_ai_reply" style="display:none;"></div>
<!-- add loading multiform -->
<div id="js_ai_reply_loading">
    <img alt = "<?php echo esc_attr(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
</div>
<span style="display:none" id="filesize"><?php echo esc_html(__('Error file size too large', 'js-support-ticket')); ?></span>
<span style="display:none" id="fileext"><?php echo esc_html(__('The uploaded file extension not valid', 'js-support-ticket')); ?></span>
<div class="jsst-popup-background" style="display:none" ></div>
<div id="popup-record-data" style="display:inline-block;width:100%;"></div>
<div id="userpopup" class="jsst-popup-wrapper jsst-merge-popup-wrapper" style="display:none" >
    <div class="userpopup-top" >
        <div class="userpopup-heading" >
            <?php echo esc_html(__('Edit Reply','js-support-ticket')); ?>
        </div>
        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="close-history userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
    </div>
    <div class="js-admin-popup-cnt">
    <div class="edit-time-popup" style="display:none;" >
        <div class="js-ticket-edit-form-wrp">
            <div class="js-ticket-edit-form-row">
                <div class="js-ticket-edit-field-title">
                    <?php echo esc_html(__('Time', 'js-support-ticket')); ?>&nbsp;<span style="color: red;" >*</span>
                </div>
                <div class="js-ticket-edit-field-wrp">
                    <?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox js-ticket-edit-field-input')), JSST_ALLOWED_TAGS) ?>
                </div>
            </div>
            <div class="js-ticket-edit-form-row">
                <div class="js-ticket-edit-field-title">
                    <?php echo esc_html(__('Reason For Editing The Timer', 'js-support-ticket')); ?>
                </div>
                <div class="js-ticket-edit-field-wrp">
                    <?php echo wp_kses(JSSTformfield::textarea('t_desc', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                </div>
            </div>
            <div class="js-ticket-priorty-btn-wrp">
                <?php echo wp_kses(JSSTformfield::submitbutton('ok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'js-ticket-priorty-save','onclick' => 'updateTimerFromPopup();')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('cancel', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'js-ticket-priorty-cancel','onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
            </div>
        </div>
    </div>
    <form id="jsst-reply-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedreply&action=jstask"),"save-edited-reply-".jssupportticket::$jsst_data[0]->id)); ?>" >
        <div class="js-form-wrapper-popup">
            <div class="js-form-title-popup"><?php echo esc_html(__('Reply', 'js-support-ticket')); ?></div>
            <div class="js-form-field-popup"><?php wp_editor('', 'jsticket_replytext', array('media_buttons' => false,'editor_height' => 200, 'textarea_rows' => 20,)); ?></div>
        </div>
        <div class="js-col-md-12 js-form-button-wrapper">
            <?php echo wp_kses(JSSTformfield::submitbutton('ok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button')), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::button('cancel', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'button', 'onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
        </div>
        <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>

        <?php
        if(isset(jssupportticket::$jsst_data[0])){
            echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS);
        } ?>
    </form>
    <?php
    if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
        <form id="jsst-time-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=saveeditedtime&action=jstask"),"save-edited-time-".jssupportticket::$jsst_data[0]->id)); ?>" >
            <div class="js-form-wrapper-popup">
                <div class="js-form-title-popup"><?php echo esc_html(__('Time', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup system-time-div" style="display:none;" >
                <div class="js-form-title-popup"><?php echo esc_html(__('System Time', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::text('systemtime', '', array('class' => 'inputbox','disabled'=>'disabled')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup">
                <div class="js-form-title-popup"><?php echo esc_html(__('Reason For Editing', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::textarea('edit_reason', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup system-time-div" style="display:none;" >
                <div class="js-form-title-popup"><?php echo esc_html(__('Resolve Conflict', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $jsst_yesno, ''), JSST_ALLOWED_TAGS); ?></div>
            </div>
            <div class="js-col-md-12 js-form-button-wrapper">
                <?php echo wp_kses(JSSTformfield::submitbutton('ok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('cancel', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'button', 'onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
            </div>
            <?php echo wp_kses(JSSTformfield::hidden('reply-replyid', ''), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('reply-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('time-confilct',''), JSST_ALLOWED_TAGS); ?>
        </form>
        <?php if(JSSTmergedaddon::featureEnabled('note') && in_array('timetracking', jssupportticket::$_active_addons)){ ?>
        <form id="jsst-note-edit-form" style="display:none" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=note&task=saveeditedtime&action=jstask"),"save-edited-time-".jssupportticket::$jsst_data[0]->id)); ?>" >
            <div class="js-form-wrapper-popup">
                <div class="js-form-title-popup"><?php echo esc_html(__('Time', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::text('edited_time', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup system-time-div" style="display:none;" >
                <div class="js-form-title-popup"><?php echo esc_html(__('System Time', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::text('systemtime', '', array('class' => 'inputbox','disabled'=>'disabled')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup">
                <div class="js-form-title-popup"><?php echo esc_html(__('Reason For Editing', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::textarea('edit_reason', '', array('class' => 'inputbox')), JSST_ALLOWED_TAGS) ?></div>
            </div>
            <div class="js-form-wrapper-popup system-time-div" style="display:none;" >
                <div class="js-form-title-popup"><?php echo esc_html(__('Resolve Conflict', 'js-support-ticket')); ?></div>
                <div class="js-form-field-popup"><?php echo wp_kses(JSSTformfield::select('time-confilct-combo', $jsst_yesno, ''), JSST_ALLOWED_TAGS); ?></div>
            </div>
            <div class="js-col-md-12 js-form-button-wrapper">
                <?php echo wp_kses(JSSTformfield::submitbutton('ok', esc_html(__('Save', 'js-support-ticket')), array('class' => 'button')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('cancel', esc_html(__('Cancel', 'js-support-ticket')), array('class' => 'button', 'onclick'=>'closePopup();')), JSST_ALLOWED_TAGS); ?>
            </div>
            <?php echo wp_kses(JSSTformfield::hidden('note-noteid', ''), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('note-tikcetid',jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('time-confilct',''), JSST_ALLOWED_TAGS); ?>
        </form>
    <?php } ?>
<?php }?>
    </div>
</div>
<div id="jsstadmin-wrapper">
    <?php
    /* The full side menu is administration, so agents do not get it — they get
       their own short menu, and the container collapses when there is nothing
       at all to show. JSSTsidemenu holds that decision for every screen.
       (Roadmap 4.0-SEC-04) */
    JSSTsidemenu::render();
    ?>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Ticket Details','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text">
                <?php echo isset(jssupportticket::$jsst_data[0]->subject) ? esc_html(jssupportticket::$jsst_data[0]->subject) : esc_html(__('Ticket Details', 'js-support-ticket')); ?>
            </h1>
        </div>
        <?php /* `jsst-skin` is the only edit made to this file by the design
                 system work: a scope for the cosmetic layer in admincss.css that
                 brings this screen's panels, headings, buttons and fields onto the
                 same tokens as the rest of the admin. No structure, no markup and
                 no behaviour changed; removing this one class reverts the whole
                 appearance change. */ ?>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n jsst-skin">
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                $jsst_cur_uid = JSSTincluder::getObjectClass('user')->uid();
                ?>

                <div id="userpopupblack" style="display:none;"> </div>
                <?php
                $jsst_jssupportticket_js ="
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
                        })

                        jQuery('.venobox').venobox({
                            infinigall: true,
                            framewidth: 850,
                            titleattr: 'data-title',
                        });
                    });

                    function addEditCredentail(nonce, ticketid, uid, cred_id = 0, cred_data = ''){
                        jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'getFormForPrivteCredentials', ticketid: ticketid, cred_id: cred_id, cred_data: cred_data, uid: uid, '_wpnonce': nonce}, function (data) {
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
                        jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'privatecredentials', task: 'removePrivateCredential',cred_id:cred_id, '_wpnonce': nonce}, function (data) {
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
                <div id="usercredentailspopup" class="jsst-popup-wrapper" style="display: none;">
                    <div class="userpopup-top">
                        <div class="userpopup-heading">
                            <?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?>
                        </div>
                        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="close-credentails userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                    </div>
                    <div class="js-ticket-usercredentails-wrp" style="display: none;">
                        <div class="js-ticket-usercredentails-credentails-wrp">
                        </div>
                        <?php if(jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6){ ?>
                            <div class="js-ticket-usercredentail-data-add-new-button-wrap" >
                                <?php $jsst_nonce = wp_create_nonce('get-form-for-privte-credentials-'.jssupportticket::$jsst_data[0]->id); ?>
                                <button type="button" class="js-ticket-usercredentail-data-add-new-button" onclick="addEditCredentail('<?php echo esc_js($jsst_nonce);?>', <?php echo esc_js(jssupportticket::$jsst_data[0]->id);?>,<?php echo esc_js(JSSTincluder::getObjectClass('user')->uid());?>);" >
                                    <?php echo esc_html(__("Add New Credential","js-support-ticket")); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="js-ticket-usercredentails-form-wrap" >
                    </div>
                </div>
                <div id="jsst-history-popup" class="jsst-popup-wrapper srch-hist-popup" style="display:none;">
                    <div class="userpopup-top">
                        <div class="userpopup-heading">
                            <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?>
                        </div>
                        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="close-history userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                    </div>
                    <div id="userpopup-records-wrp">
                        <div id="userpopup-records">
                            <?php
                            // Timeline filters: event type and source. Filtering happens in the
                            // browser so the popup stays open and the page is not reloaded.
                            // (Roadmap 4.0-CORE-01)
                            $jsst_history_filters = isset(jssupportticket::$jsst_data['history_filters']) ? jssupportticket::$jsst_data['history_filters'] : array('eventtype' => array(), 'source' => array());
                            if (!empty($jsst_history_filters['eventtype']) || !empty($jsst_history_filters['source'])) { ?>
                                <div class="jsst-timeline-filters">
                                    <span class="jsst-timeline-filter-field">
                                        <label for="jsst-timeline-event"><?php echo esc_html(__('Event', 'js-support-ticket')); ?></label>
                                        <select id="jsst-timeline-event" class="jsst-timeline-filter" data-filter="event">
                                            <option value=""><?php echo esc_html(__('All events', 'js-support-ticket')); ?></option>
                                            <?php foreach ($jsst_history_filters['eventtype'] AS $jsst_eventtype) { ?>
                                                <option value="<?php echo esc_attr($jsst_eventtype); ?>"><?php echo esc_html($jsst_eventtype); ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                    <span class="jsst-timeline-filter-field">
                                        <label for="jsst-timeline-source"><?php echo esc_html(__('Source', 'js-support-ticket')); ?></label>
                                        <select id="jsst-timeline-source" class="jsst-timeline-filter" data-filter="source">
                                            <option value=""><?php echo esc_html(__('All sources', 'js-support-ticket')); ?></option>
                                            <?php foreach ($jsst_history_filters['source'] AS $jsst_source) { ?>
                                                <option value="<?php echo esc_attr($jsst_source); ?>"><?php echo esc_html($jsst_source); ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                    <?php // Both only appear once a filter is actually narrowing the list. ?>
                                    <button type="button" class="jsst-timeline-clear" style="display:none;"><?php echo esc_html(__('Clear', 'js-support-ticket')); ?></button>
                                    <span class="jsst-timeline-shown" role="status" aria-live="polite"></span>
                                </div>
                            <?php } ?>
                            <div class="userpopup-search-history">
                                <?php // data[5] holds the tickect history
                                    $jsst_field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, jssupportticket::$jsst_data[0]->multiformid);
                                if ((!empty(jssupportticket::$jsst_data[5]))) { ?>
                                    <div class="userpopup-search-history-row userpopup-search-history-head" aria-hidden="true">
                                        <div class="userpopup-search-history-col date"><?php echo esc_html(__('Date', 'js-support-ticket')); ?></div>
                                        <div class="userpopup-search-history-col time"><?php echo esc_html(__('Time', 'js-support-ticket')); ?></div>
                                        <div class="userpopup-search-history-col msg"><?php echo esc_html(__('Event', 'js-support-ticket')); ?></div>
                                    </div>
                                    <?php foreach (jssupportticket::$jsst_data[5] AS $jsst_history) {
                                        $jsst_event_type = isset($jsst_history->eventtype) ? $jsst_history->eventtype : '';
                                        $jsst_event_source = isset($jsst_history->source) ? $jsst_history->source : '';
                                        $jsst_actor_name = isset($jsst_history->actorname) ? $jsst_history->actorname : '';
                                        ?>
                                        <div class="userpopup-search-history-row jsst-timeline-row" data-event="<?php echo esc_attr($jsst_event_type); ?>" data-source="<?php echo esc_attr($jsst_event_source); ?>">
                                            <div class="userpopup-search-history-col date">
                                                <?php echo esc_html(date_i18n('Y-m-d', jssupportticketphplib::JSST_strtotime($jsst_history->datetime))); ?>
                                            </div>
                                            <div class="userpopup-search-history-col time">
                                            <?php echo esc_html(date_i18n('H:i:s', jssupportticketphplib::JSST_strtotime($jsst_history->datetime))); ?>
                                            </div>
                                            <?php
                                            if (is_super_admin($jsst_history->uid)) {
                                                $jsst_message = 'admin';
                                            } elseif ( in_array('agent',jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff($jsst_history->uid)) {
                                                $jsst_message = 'agent';
                                            } else {
                                                $jsst_message = 'member';
                                            }
                                            ?>
                                            <div class="userpopup-search-history-col msg <?php echo esc_attr($jsst_message); ?>">
                                                <?php echo wp_kses_post($jsst_history->message); ?>
                                                <?php
                                                // Field diffs are shown as their own line so a value
                                                // change is readable without parsing the message.
                                                if (isset($jsst_history->fieldname) && $jsst_history->fieldname != '') { ?>
                                                    <span class="jsst-timeline-diff">
                                                        <span class="jsst-timeline-field"><?php echo esc_html($jsst_history->fieldname); ?></span>
                                                        <span class="jsst-timeline-old"><?php echo esc_html($jsst_history->oldvalue); ?></span>
                                                        <span class="jsst-timeline-arrow">&rarr;</span>
                                                        <span class="jsst-timeline-new"><?php echo esc_html($jsst_history->newvalue); ?></span>
                                                    </span>
                                                <?php } ?>
                                                <?php if ($jsst_actor_name != '' || $jsst_event_source != '') { ?>
                                                    <span class="jsst-timeline-meta">
                                                        <?php if ($jsst_actor_name != '') { ?>
                                                            <span class="jsst-timeline-actor"><?php echo esc_html($jsst_actor_name); ?></span>
                                                        <?php } ?>
                                                        <?php if ($jsst_event_source != '') { ?>
                                                            <span class="jsst-timeline-source"><?php echo esc_html($jsst_event_source); ?></span>
                                                        <?php } ?>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else { ?>
                                    <div class="userpopup-records-desc"><?php echo esc_html(__('Nothing has happened on this ticket yet.', 'js-support-ticket')); ?></div>
                                <?php } ?>
                            </div>
                            <p class="jsst-timeline-empty" style="display:none;"><?php echo esc_html(__('No events match the selected filters.', 'js-support-ticket')); ?></p>
                        </div>
                    </div>
                    <script>
                    (function(){
                        var filters = document.querySelectorAll('.jsst-timeline-filter');
                        if (!filters.length) { return; }
                        function apply(){
                            var event = document.getElementById('jsst-timeline-event');
                            var source = document.getElementById('jsst-timeline-source');
                            var wantEvent = event ? event.value : '';
                            var wantSource = source ? source.value : '';
                            var rows = document.querySelectorAll('.jsst-timeline-row');
                            var shown = 0;
                            for (var i = 0; i < rows.length; i++) {
                                var row = rows[i];
                                var ok = (!wantEvent || row.getAttribute('data-event') === wantEvent)
                                      && (!wantSource || row.getAttribute('data-source') === wantSource);
                                row.style.display = ok ? '' : 'none';
                                if (ok) { shown++; }
                            }
                            var empty = document.querySelector('.jsst-timeline-empty');
                            if (empty) { empty.style.display = shown ? 'none' : ''; }

                            /* Say so when the list is narrowed. A filtered timeline that
                               looks exactly like a complete one is how an agent concludes
                               a ticket has no history. */
                            var on = wantEvent || wantSource;
                            for (var f = 0; f < filters.length; f++) {
                                filters[f].className = 'jsst-timeline-filter' + (filters[f].value ? ' jsst-timeline-filter-on' : '');
                            }
                            var clear = document.querySelector('.jsst-timeline-clear');
                            if (clear) { clear.style.display = on ? '' : 'none'; }
                            var shownEl = document.querySelector('.jsst-timeline-shown');
                            if (shownEl) {
                                shownEl.textContent = on
                                    ? '<?php echo esc_js(__('Showing', 'js-support-ticket')); ?> ' + shown + ' / ' + rows.length
                                    : '';
                            }
                        }
                        for (var i = 0; i < filters.length; i++) {
                            filters[i].addEventListener('change', apply);
                        }
                        var clearBtn = document.querySelector('.jsst-timeline-clear');
                        if (clearBtn) {
                            clearBtn.addEventListener('click', function(){
                                for (var c = 0; c < filters.length; c++) { filters[c].value = ''; }
                                apply();
                            });
                        }
                        apply();
                    })();
                    </script>
                </div>
                <!-- inrternal notes popup -->
                <?php /* The internal-note form is no longer a modal — it is the
                   composer's internal mode. (Roadmap 4.0-UX-03) */ ?>
                <!-- change status popup -->
                <div id="changestatus-popup" class="jsst-popup-wrapper" style="display: none;">
                    <div class="userpopup-top">
                        <div class="userpopup-heading">
                            <!-- Display heading based on field order  -->
                            <?php echo esc_html(__('Change Status','js-support-ticket')); ?>
                        </div>
                        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                    </div>
                    <div class="js-admin-popup-cnt">
                        <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=changestatus"),"change-status-".jssupportticket::$jsst_data[0]->id)); ?>">
                            <div class="js-form-wrapper">
                                <div class="js-form-title">
                                    <?php echo esc_html(__('Select Status','js-support-ticket')); ?>
                                </div>
                                <div class="js-form-value">
                                    <?php echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForCombobox(), jssupportticket::$jsst_data[0]->status, '', array('class' => 'inputbox js-admin-popup-select-field')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <div class="js-form-button">
                                <?php echo wp_kses(JSSTformfield::submitbutton('changestatus', esc_html(__('Change Status','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block')), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_changestatus'), JSST_ALLOWED_TAGS); ?>
                            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
                <!-- change priority popup -->
                <div id="changepriority-popup" class="jsst-popup-wrapper" style="display: none;">
                    <div class="userpopup-top">
                        <div class="userpopup-heading">
                            <!-- Display heading based on field order  -->
                            <?php echo esc_html(__('Change','js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                        </div>
                        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                    </div>
                    <div class="js-admin-popup-cnt">
                        <form class="js-det-tkt-form" method="post" action="#">
                            <div class="js-form-wrapper">
                                <div class="js-form-title">
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                                </div>
                                <div class="js-form-value">
                                    <?php echo wp_kses(JSSTformfield::select('prioritytemp', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$jsst_data[0]->priorityid, esc_html(__('Change', 'js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])), array('class' => 'inputbox js-admin-popup-select-field')), JSST_ALLOWED_TAGS); ?>
                                </div>
                            </div>
                            <div class="js-form-button">
                                <?php echo wp_kses(JSSTformfield::button('changepriority', esc_html(__('Change', 'js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])), array('class' => 'button js-admin-pop-btn-block changeprioritybutton', 'onclick' => 'actionticket(1);')), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <?php //echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                            <?php //echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                            <?php //echo wp_kses(JSSTformfield::hidden('action', 'note_savenote'), JSST_ALLOWED_TAGS); ?>
                            <?php //echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                        </form>
                    </div>
                </div>
                <!-- change department popup -->
                <div id="changedept-popup" class="jsst-popup-wrapper" style="display: none;">
                    <?php if ( JSSTmergedaddon::featureEnabled('actions')) { ?>
                        <div class="userpopup-top">
                            <div class="userpopup-heading">
                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ".esc_html(__('Transfer','js-support-ticket')); ?>
                            </div>
                            <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                        </div>
                        <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=transferdepartment"),"transfer-department-".jssupportticket::$jsst_data[0]->id)); ?>"  enctype="multipart/form-data">
                            <div class="js-admin-popup-cnt">
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?></div>
                                    <div class="js-form-value">
                                        <?php echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$jsst_data[0]->departmentid, esc_html(__('Select', 'js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])), array('class' => 'inputbox js-admin-popup-select-field')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <?php if(JSSTmergedaddon::featureEnabled('note')){ ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Reason For', 'js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ".esc_html(__('Transfer', 'js-support-ticket')); ?> <span class="jsst-optional"><?php echo esc_html(__('optional', 'js-support-ticket')); ?></span></label></div>
                                        <div class="js-form-value"><?php wp_editor('', 'departmenttranfernote', array('media_buttons' => false)); ?></div>
                                    </div>
                                <?php } ?>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('departmenttransfer', esc_html(__('Transfer','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block')), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_transferdepartment'), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            </div>
                        </form>
                    <?php } ?>
                </div>
                <!-- assign to staff popup -->
                <div id="assignstaff-popup" class="jsst-popup-wrapper" style="display: none;">
                    <?php if ( in_array('agent',jssupportticket::$_active_addons)) { ?>
                        <div class="userpopup-top">
                            <div class="userpopup-heading">
                                <?php echo esc_html(__('Assign To Agent','js-support-ticket')); ?>
                            </div>
                            <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                        </div>
                        <div class="js-admin-popup-cnt">
                            <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=assigntickettostaff"),"assign-ticket-to-staff-".jssupportticket::$jsst_data[0]->id)); ?>"  enctype="multipart/form-data">
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></div>
                                    <div class="js-form-value">
                                         <?php echo wp_kses(JSSTformfield::select('staffid', JSSTincluder::getJSModel('agent')->getstaffForCombobox(), jssupportticket::$jsst_data[0]->staffid, esc_html(__('Select Agent', 'js-support-ticket')), array('class' => 'inputbox js-admin-popup-select-field','required' => true)), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <?php
                                /* Who the desk would pick, and why - offered
                                   beside the list rather than applied to it.
                                   The person assigning keeps the decision;
                                   this only means they are not making it
                                   blind, and it is the same ranking the
                                   automatic engine in 5.0 will act on, so what
                                   is shown here is what would happen there.
                                   (Roadmap 4.5-FE-07) */
                                if (class_exists('JSSTworkload')) {
                                    $jsst_suggest = JSSTworkload::explainAssignment((int) jssupportticket::$jsst_data[0]->id);
                                    if (is_array($jsst_suggest) && $jsst_suggest['chosen'] > 0) { ?>
                                        <div class="js-form-wrapper">
                                            <div class="js-form-title"><?php echo esc_html(__('Suggested', 'js-support-ticket')); ?></div>
                                            <div class="js-form-value">
                                                <div class="jsst-verdict">
                                                    <p class="jsst-verdict-text"><?php echo esc_html($jsst_suggest['headline']); ?></p>
                                                </div>
                                                <ul class="jsst-reasons">
                                                    <?php foreach (array_slice($jsst_suggest['lines'], 0, 6) AS $jsst_line) { ?>
                                                        <li class="<?php echo $jsst_line['chosen'] ? 'jsst-reason-on' : ''; ?>"><?php echo esc_html($jsst_line['text']); ?></li>
                                                    <?php } ?>
                                                </ul>
                                                <?php if (!empty($jsst_suggest['wouldmove'])) { ?>
                                                    <p class="jsst-field-note"><?php echo esc_html(__('The agent holding this ticket is not the one it would go to now — which is worth knowing before you move it, and is not by itself a reason to.', 'js-support-ticket')); ?></p>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php }
                                } ?>
                                <?php if(JSSTmergedaddon::featureEnabled('note')){ ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?> <span class="jsst-optional"><?php echo esc_html(__('optional', 'js-support-ticket')); ?></span></label></div>
                                        <div class="js-form-value"><?php wp_editor('', 'assignnote', array('media_buttons' => false)); ?></div>
                                    </div>
                                <?php } ?>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('assigntostaff', esc_html(__('Assign','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block')), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('action', 'ticket_assigntickettostaff'), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            </form>
                        </div>
                    <?php } ?>
                </div>
                <!-- ticket detail -->
                <div class="js-ticket-detail-wrapper">
                    <div class="js-tkt-det-left">
                        <!-- ticket top info -->
                        <div class="js-tkt-det-cnt js-tkt-det-info-wrp">
                            <div class="js-tkt-det-user">
                                <div class="js-tkt-det-user-image">
                                    <?php echo wp_kses_post(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid)); ?>
                                </div>
                                <div class="js-tkt-det-user-cnt">
                                    <div class="js-tkt-det-user-data name"><?php echo esc_html(jssupportticket::$jsst_data[0]->name); ?></div>
                                    <div class="js-tkt-det-user-data email"><?php echo esc_html(jssupportticket::$jsst_data[0]->email); ?></div>
                                    <div class="js-tkt-det-user-data number"><?php echo esc_html(jssupportticket::$jsst_data[0]->phone); ?></div>
                                    <?php
                                    /* The company behind this address, if the desk has one
                                       written down. Resolved once here and reused by the
                                       Company box on the right. (Roadmap 5.5-COM-06) */
                                    $jsst_tktcompany = class_exists('JSSTcompanies') ? JSSTcompanies::forEmail(jssupportticket::$jsst_data[0]->email) : false;
                                    if ($jsst_tktcompany) { ?>
                                        <div class="js-tkt-det-user-data company">
                                            <span class="js-tkt-det-user-tit"><?php echo esc_html(__('Company', 'js-support-ticket')); ?>:</span>
                                            <a class="js-tkt-det-user-val" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=addcompany&companyid=' . (int) $jsst_tktcompany->id)); ?>"><?php echo esc_html($jsst_tktcompany->name); ?></a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php if(isset(jssupportticket::$jsst_data['nticket'])){ ?>
                            <div class="js-tkt-det-other-tkt">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=tickets&uid='.jssupportticket::$jsst_data[0]->uid)); ?>" class="js-tkt-det-other-tkt-btn">
                                    <?php echo esc_html(__('View all','js-support-ticket')).' '.esc_html(jssupportticket::$jsst_data['nticket']).' '. esc_html(__('tickets by','js-support-ticket')).' '.esc_html(jssupportticket::$jsst_data[0]->name); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=tickets&uid='.jssupportticket::$jsst_data[0]->uid)); ?>" class="js-tkt-det-other-tkt-img">
                                    <img alt = "<?php echo esc_attr(__('Edit Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/new-window.png" />
                                </a>
                            </div>
                            <?php } ?>
                            <div class="js-tkt-det-tkt-msg">
                                <?php echo wp_kses_post(jssupportticket::$jsst_data[0]->message); ?>
                            </div>
                            <?php
                            $jsst_formid = jssupportticket::$jsst_data[0]->multiformid;
                            jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                            $jsst_customfields = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, null, $jsst_formid);
                            if (!empty($jsst_customfields)){
                                ?>
                                <div class="js-tkt-det-tkt-custm-flds">
                                    <?php
                                    foreach ($jsst_customfields as $jsst_field) {
                                        $jsst_ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($jsst_field,2, jssupportticket::$jsst_data[0]->params);
                                        ?>
                                        <div class="js-tkt-det-tkt-custm-flds-rec">
                                            <span class="js-tkt-det-tkt-custm-flds-tit">
                                                <?php echo esc_html($jsst_ret['title']).' : '; ?>
                                            </span>
                                            <span class="js-tkt-det-tkt-custm-flds-val">
                                                <?php echo wp_kses($jsst_ret['value'], JSST_ALLOWED_TAGS); ?>
                                            </span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                            <?php /* What the desk worked out before anybody opened this, when
                                     the site has switched that on; nothing at all otherwise.
                                     Under the customer's own message rather than in the reply
                                     form, where it used to sit after the whole thread - a panel
                                     headed "Read before you opened it" is only worth anything
                                     if it is seen on opening. (Roadmap 6.0-AI-13) */
                            if (class_exists('JSSTaitriage')) {
                                echo wp_kses_post(JSSTaitriage::panel(jssupportticket::$jsst_data[0]->id));
                            } ?>
                            <div class="js-tkt-det-actn-btn-wrp">
                                <?php /* The button used to render for anybody who could open the ticket,
                                         while the backend refused the edit — so an agent got a button
                                         that answered "You are not allowed to edit this ticket". */ ?>
                                <?php /* Every action used to be one of eleven identical buttons, so
                                         Ban Email sat beside Print and looked just like it. They are
                                         drawn into buffers here and laid out once the form is open:
                                         the everyday ones in a row, the rest under More actions, and
                                         the two that ban a customer apart and in red. */
                                      ob_start(); ?>
                                <?php if (JSSTroles::canEditTicketContent()) { ?>
                                <a title="<?php echo esc_attr(__('Edit Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="?page=ticket&jstlay=addticket&jssupportticketid=<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                    <img alt = "<?php echo esc_attr(__('Edit Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit.png" />
                                    <span><?php echo esc_html(__('Edit Ticket','js-support-ticket')); ?></span>
                                </a>
                                <?php } ?>
                                <?php $jsst_act_edit = ob_get_clean(); ob_start(); ?>
                                <?php if(JSSTmergedaddon::featureEnabled('tickethistory')){ ?>
                                    <a title="<?php echo esc_attr(__('Show History','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="showhistory">
                                        <img alt = "<?php echo esc_attr(__('Show History','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" />
                                        <span><?php echo esc_html(__('Show History','js-support-ticket')); ?></span>
                                    </a>
                                <?php } ?>
                                <?php $jsst_act_history = ob_get_clean(); ?>
                                <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=actionticket"),"action-ticket-".jssupportticket::$jsst_data[0]->id)); ?>" id="adminTicketform" enctype="multipart/form-data">
                                    <?php
                                    // Optional reason, recorded on the timeline with whichever
                                    // action is taken from this form. (Roadmap 4.0-CORE-05)
                                    ?>
                                    <div class="jsst-action-reason">
                                        <?php /* The explanation sits on the label line, once. It used to
                                                 be said three times - placeholder, note and label - in a
                                                 tinted box that read as a separate form. */ ?>
                                        <div class="jsst-action-reason-head">
                                            <label class="jsst-action-reason-label" for="jsst-action-reason">
                                                <?php echo esc_html(__('Reason for the action', 'js-support-ticket')); ?>
                                                <span class="jsst-action-reason-optional"><?php echo esc_html(__('optional', 'js-support-ticket')); ?></span>
                                            </label>
                                            <span class="jsst-action-reason-note" id="jsst-action-reason-note"><?php echo esc_html(__('Saved to the ticket history with the action you pick below.', 'js-support-ticket')); ?></span>
                                        </div>
                                        <div class="jsst-action-reason-field">
                                            <input type="text" class="inputbox" id="jsst-action-reason" name="actionreason" maxlength="500" value="" aria-describedby="jsst-action-reason-note" placeholder="<?php echo esc_attr(__('e.g. Customer confirmed the fix works', 'js-support-ticket')); ?>" />
                                            <span class="jsst-action-reason-count" aria-hidden="true">500</span>
                                        </div>
                                    </div>
                                    <script>
                                    /* Remaining characters, so the 500 limit is visible rather than
                                       something an agent discovers by being cut off. */
                                    (function(){
                                        var field = document.getElementById("jsst-action-reason");
                                        if (!field) { return; }
                                        var count = field.parentNode.querySelector(".jsst-action-reason-count");
                                        if (!count) { return; }
                                        var max = parseInt(field.getAttribute("maxlength"), 10) || 500;
                                        var sync = function(){
                                            var left = max - field.value.length;
                                            count.textContent = left;
                                            count.className = "jsst-action-reason-count" + (left <= 50 ? " jsst-action-reason-count-low" : "");
                                        };
                                        field.addEventListener("input", sync);
                                        sync();
                                    })();
                                    </script>
                                    <?php ob_start(); ?>
                                    <?php
                                        /* Closing and reopening are state changes, so the buttons
                                           follow the same capability the server enforces. A light
                                           agent is not shown a control that would be refused.
                                           (Roadmap 4.0-SEC-04) */
                                        if (JSSTroles::canChangeTicketState() && jssupportticket::$jsst_data[0]->status != 6) { // merged closed ticket can not be reopend.
                                            if (jssupportticket::$jsst_data[0]->status != 5) { ?>
                                                <a title="<?php echo esc_attr(__('Close Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(2);">
                                                    <img alt = "<?php echo esc_attr(__('Close Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/close.png" />
                                                    <span><?php echo esc_html(__('Close Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Reopen Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(3);">
                                                    <img alt = "<?php echo esc_attr(__('Reopen Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/reopen.png" />
                                                    <span><?php echo esc_html(__('Reopen Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php }
                                        }
                                        jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                                    ?>
                                    <?php $jsst_act_close = ob_get_clean(); ob_start(); ?>
                                    <?php if (  JSSTmergedaddon::featureEnabled('actions') && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 ) { ?>
                                        <a title="<?php echo esc_attr(__('Print Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                            <img alt = "<?php echo esc_attr(__('Print Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" />
                                            <span><?php echo esc_html(__('Print Ticket','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php $jsst_act_print = ob_get_clean(); ob_start(); ?>
                                    <?php // The admin screen is reachable by an agent too, so it asks the same
                                          // question as the action. (Roadmap 4.0-CORE-04)
                                          if (  in_array('mergeticket', jssupportticket::$_active_addons) && JSSTroles::canMergeTickets() && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 ) {
                                        $jsst_nonce = wp_create_nonce("get-tickets-for-merging-".jssupportticket::$jsst_data[0]->id) ?>
                                        <a title="<?php echo esc_attr(__('Merge Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="mergeticket" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>" onclick="return showPopupAndFillValues(<?php echo esc_js(jssupportticket::$jsst_data[0]->id) ?>,4, '<?php echo esc_js($jsst_nonce);?>')" >
                                            <img alt = "<?php echo esc_attr(__('Merge Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/merge-ticket.png" />
                                            <span><?php echo esc_html(__('Merge Ticket','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php $jsst_act_merge = ob_get_clean(); ob_start(); ?>
                                    <?php if (in_array('privatecredentials',jssupportticket::$_active_addons)) { ?>
                                        <?php $jsst_nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$jsst_data[0]->id) ?>
                                    <a title="<?php echo esc_attr(__('Private Credentials','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="javascript:void(0);" id="privatecredentials" onclick="getCredentails(<?php echo esc_js(jssupportticket::$jsst_data[0]->id); ?>, '<?php echo esc_js($jsst_nonce); ?>')" >
                                        <?php $jsst_query = jssupportticket::$_db->prepare("SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = %d", jssupportticket::$jsst_data[0]->id);
                                        $jsst_cred_count = jssupportticket::$_db->get_var($jsst_query);
                                        if ($jsst_cred_count>0) {
                                            $jsst_img_name = 'private-credentials-exist.png';
                                        } else {
                                            $jsst_img_name = 'private-credentials.png';
                                        } ?>
                                        <img alt = "<?php echo esc_attr(__('Private Credentials','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/<?php echo esc_attr($jsst_img_name);?>"  />
                                        <span><?php echo esc_html(__('Private Credentials','js-support-ticket')); ?></span>
                                    </a>
                                    <?php } ?>
                                    <?php
                                        if(JSSTmergedaddon::featureEnabled('actions')){
                                            if (jssupportticket::$jsst_data[0]->lock == 1) { ?>
                                                <a title="<?php echo esc_attr(__('Unlock Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn jsst-shortcut-lock" aria-keyshortcuts="l" href="#" onclick="actionticket(5);">
                                                    <img alt = "<?php echo esc_attr(__('Unlock Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/unlock.png" />
                                                    <span><?php echo esc_html(__('Unlock Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Lock Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn jsst-shortcut-lock" aria-keyshortcuts="l" href="#" onclick="actionticket(4);">
                                                    <img alt = "<?php echo esc_attr(__('Lock Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/lock.png" />
                                                    <span><?php echo esc_html(__('Lock Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php }
                                        }
                                        $jsst_act_more = ob_get_clean(); ob_start();
                                        if(JSSTmergedaddon::featureEnabled('banemail')){
                                            $jsst_manageoptions = current_user_can('manage_options');
                                            $jsst_isagentstaff = in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff();
                                            if (JSSTincluder::getJSModel('banemail')->isEmailBan(jssupportticket::$jsst_data[0]->email)) {
                                                $jsst_canunban = $jsst_manageoptions || ($jsst_isagentstaff && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Unban Email'));
                                                if ($jsst_canunban) { ?>
                                                <a title="<?php echo esc_attr(__('Unban Email','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(7);">
                                                    <img alt = "<?php echo esc_attr(__('Unban Email','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-ban.png" />
                                                    <span><?php echo esc_html(__('Unban Email','js-support-ticket')); ?></span>
                                                </a>
                                                <?php }
                                            } else {
                                                $jsst_canban = $jsst_manageoptions || ($jsst_isagentstaff && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket'));
                                                if ($jsst_canban) { ?>
                                                <a title="<?php echo esc_attr(__('Ban Email','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(6);">
                                                    <img alt = "<?php echo esc_attr(__('Ban Email','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban.png" />
                                                    <span><?php echo esc_html(__('Ban Email','js-support-ticket')); ?></span>
                                                </a>
                                            <?php
                                            }
                                            }
                                        }
                                        $jsst_act_ban = ob_get_clean(); ob_start();
                                        if(in_array('overdue', jssupportticket::$_active_addons)){
                                            if (jssupportticket::$jsst_data[0]->isoverdue == 1) { ?>
                                                <a title="<?php echo esc_attr(__('Unmark Overdue','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(11);">
                                                    <img alt = "<?php echo esc_attr(__('Unmark Overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-over-due.png" />
                                                    <span><?php echo esc_html(__('Unmark Overdue','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Mark Overdue','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(8);">
                                                    <img alt = "<?php echo esc_attr(__('Mark Overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/over-due.png" />
                                                    <span><?php echo esc_html(__('Mark Overdue','js-support-ticket')); ?></span>
                                                </a>
                                            <?php }
                                        }
                                    ?>
                                    <?php $jsst_act_overdue = ob_get_clean(); ob_start(); ?>
                                    <?php
                                        $jsst_canmarkinprogress = JSSTroles::canChangeTicketState() || ( in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Mark In Progress') );
                                        if(JSSTmergedaddon::featureEnabled('actions') && $jsst_canmarkinprogress){ ?>
                                        <a title="<?php echo esc_attr(__('Mark In Progress','js-support-ticket')); ?>" class="js-tkt-det-actn-btn jsst-shortcut-inprogress" aria-keyshortcuts="i" href="#" onclick="actionticket(9);">
                                            <img alt = "<?php echo esc_attr(__('Mark In Progress','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/in-progress.png" />
                                            <span><?php echo esc_html(__('Mark In Progress','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php $jsst_act_progress = ob_get_clean(); ob_start(); ?>
                                    <?php
                                        $jsst_canbanandclose = current_user_can('manage_options') || ( in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('agent')->isUserStaff() && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Ban Email And Close Ticket') );
                                        if(JSSTmergedaddon::featureEnabled('banemail') && $jsst_canbanandclose){ ?>
                                            <a title="<?php echo esc_attr(__('Ban Email And Close Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(10);">
                                                <img alt = "<?php echo esc_attr(__('Ban Email And Close Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban-email-close-ticket.png" />
                                                <span><?php echo esc_html(__('Ban Email And Close Ticket','js-support-ticket')); ?></span>
                                            </a>
                                    <?php } ?>
                                    <?php $jsst_act_banclose = ob_get_clean();
                                        // Already escaped as they were drawn above.
                                        $jsst_act_more_all = trim($jsst_act_history . $jsst_act_print . $jsst_act_more . $jsst_act_overdue);
                                        $jsst_act_danger = trim($jsst_act_ban . $jsst_act_banclose); ?>
                                    <div class="jsst-det-actions">
                                        <?php echo $jsst_act_edit . $jsst_act_close . $jsst_act_progress . $jsst_act_merge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        <?php if ($jsst_act_more_all !== '' || $jsst_act_danger !== '') { ?>
                                            <details class="jsst-det-more">
                                                <summary class="js-tkt-det-actn-btn"><span><?php echo esc_html(__('More actions','js-support-ticket')); ?></span></summary>
                                                <div class="jsst-det-more-menu">
                                                    <?php echo $jsst_act_more_all; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                    <?php if ($jsst_act_danger !== '') { ?>
                                                        <div class="jsst-det-more-danger">
                                                            <?php echo $jsst_act_danger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </details>
                                        <?php } ?>
                                    </div>
                                    <p class="jsst-shortcut-hint">
                                        <?php echo esc_html(__('Keyboard: R reply · I in progress · L lock · P priority · S status · N note · H history · M merge', 'js-support-ticket')); ?>
                                    </p>
                                    <?php
                                        echo wp_kses(JSSTformfield::hidden('actionid', ''), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('priority', ''), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS);
                                         echo wp_kses(JSSTformfield::hidden('action', 'reply_savereply'),JSST_ALLOWED_TAGS);
                                        echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS);
                                    ?>
                                </form>
                            </div>
                        </div>
                        <?php
                        // Tickets merged into this one. Listed here so a merge is never a
                        // dead end: each source stays reachable and can be separated
                        // Provided by the Merge Ticket add-on.
                        if (in_array('mergeticket', jssupportticket::$_active_addons)) {
                            $jsst_merged_sources = JSSTincluder::getJSModel('mergeticket')->getMergedSources(jssupportticket::$jsst_data[0]->id);
                            if (!empty($jsst_merged_sources)) { ?>
                                <div class="js-tkt-det-title">
                                    <?php echo esc_html(__('Merged Tickets', 'js-support-ticket')); ?>
                                    <span class="jsst-merged-count"><?php echo esc_html(count($jsst_merged_sources)); ?></span>
                                </div>
                                <div class="jsst-merged-sources">
                                    <div class="jsst-merged-sources-intro">
                                        <?php echo esc_html(__('These tickets were merged into this one. Each keeps its own replies and attachments, and can be separated again.', 'js-support-ticket')); ?>
                                    </div>
                                    <?php foreach ($jsst_merged_sources AS $jsst_merged_source) {
                                        $jsst_unmerge_url = wp_nonce_url(
                                            admin_url('admin.php?page=ticket&task=unmergeticket&action=jstask&sourceticket=' . (int) $jsst_merged_source->id . '&primaryticket=' . (int) jssupportticket::$jsst_data[0]->id),
                                            'unmerge-ticket-' . (int) $jsst_merged_source->id
                                        ); ?>
                                        <div class="jsst-merged-source">
                                            <span class="jsst-merged-source-icon" aria-hidden="true">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M6 21V9a9 9 0 0 0 9 9"></path></svg>
                                            </span>
                                            <span class="jsst-merged-source-body">
                                                <a class="jsst-merged-source-link" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_merged_source->id)); ?>">
                                                    <span class="jsst-merged-source-id">#<?php echo esc_html($jsst_merged_source->ticketid); ?></span>
                                                    <span class="jsst-merged-source-subject"><?php echo esc_html($jsst_merged_source->subject); ?></span>
                                                </a>
                                                <span class="jsst-merged-source-meta">
                                                <?php
                                                if ($jsst_merged_source->mergedbyname != '') {
                                                    echo esc_html(sprintf(
                                                        /* translators: 1: user name, 2: date */
                                                        __('merged by %1$s on %2$s', 'js-support-ticket'),
                                                        $jsst_merged_source->mergedbyname,
                                                        date_i18n(get_option('date_format'), jssupportticketphplib::JSST_strtotime($jsst_merged_source->mergedate))
                                                    ));
                                                } else {
                                                    echo esc_html(sprintf(
                                                        /* translators: %s: date */
                                                        __('merged on %s', 'js-support-ticket'),
                                                        date_i18n(get_option('date_format'), jssupportticketphplib::JSST_strtotime($jsst_merged_source->mergedate))
                                                    ));
                                                }
                                                ?>
                                                </span>
                                            </span>
                                            <a class="jsst-merged-source-undo" href="<?php echo esc_url($jsst_unmerge_url); ?>" title="<?php echo esc_attr(__('Separate this ticket from this one', 'js-support-ticket')); ?>" onclick="return confirm('<?php echo esc_js(__('Separate this ticket again? It will reopen with the status it had before the merge.', 'js-support-ticket')); ?>');"><?php echo esc_html(__('Unmerge', 'js-support-ticket')); ?></a>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php }
                        }
                        ?>
                        <!-- Tickect internal Note Area -->
                        <?php
                            $jsst_color1ed = "colored";
                            if(JSSTmergedaddon::featureEnabled('note')){ ?>
                                <?php if (!empty(jssupportticket::$jsst_data[6])) { ?>
                                    <?php /* Only above notes that exist: its add button is hidden, so
                                             on a ticket with none the heading stood over nothing. New
                                             notes are written from the composer's Internal note tab. */ ?>
                                    <div class="js-tkt-det-title">
                                        <?php echo esc_html(__('Internal Notes', 'js-support-ticket')); ?>
                                        <span class="jsst-title-count"><?php echo esc_html(count(jssupportticket::$jsst_data[6])); ?></span>
                                    </div>
                                    <?php foreach (jssupportticket::$jsst_data[6] AS $jsst_note) {
                                        if ($jsst_cur_uid == isset($jsst_note->uid))
                                            $jsst_color1ed = '';?>
                                        <div class="js-ticket-thread">
                                            <div class="js-ticket-thread-image">
                                                <?php 
                                                    echo wp_kses(jsst_get_avatar($jsst_note->userid), JSST_ALLOWED_TAGS);
                                                ?>
                                            </div>
                                            <div class="js-ticket-thread-cnt">
                                                <div class="js-ticket-thread-data">
                                                    <span class="js-ticket-thread-person">
                                                        <?php
                                                        if(isset($jsst_note->staffname)){
                                                            echo esc_html($jsst_note->staffname);
                                                        }elseif(isset($jsst_note->display_name)){
                                                            echo esc_html($jsst_note->display_name);
                                                        }else{
                                                            echo '--------';
                                                        }
                                                        ?>
                                                    </span>
                                                    <?php
                                                        if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                            $jsst_hours = floor($jsst_note->usertime / 3600);
                                                            $jsst_mins = floor($jsst_note->usertime / 60);
                                                            $jsst_mins = floor($jsst_mins % 60);
                                                            $jsst_secs = floor($jsst_note->usertime % 60);
                                                            $jsst_time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                        ?>
                                                        <span class="js-ticket-thread-time"><?php echo esc_html($jsst_time); ?></span>
                                                    <?php } ?>
                                                </div>
                                                <?php if (isset($jsst_note->title) && $jsst_note->title != '') { ?>
                                                    <div class="js-ticket-thread-data">
                                                        <span class="js-ticket-thread-note"><?php echo esc_html($jsst_note->title); ?></span>
                                                    </div>
                                                <?php } ?>
                                                <div class="js-ticket-thread-data note-msg">
                                                <?php
                                                    echo wp_kses_post($jsst_note->note);
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
                                                    } elseif($jsst_note->filesize > 0 && !empty($jsst_note->filename)){
                                                        echo wp_kses('<div class="js_ticketattachment">
                                                                <span class="js_ticketattachment_fname">'
                                                                    . esc_html($jsst_note->filename) . /*' (' . ($jsst_note->filesize / 1024 ) . ')&nbsp;&nbsp*/'
                                                                </span>
                                                                <a title="'. esc_html(__('Download','js-support-ticket')).'" class="button" target="_blank" href="'.esc_url(wp_nonce_url(admin_url('?page=note&action=jstask&task=downloadbyid&id='.$jsst_note->id), 'download-note-attachment-'.$jsst_note->id)).'">'. esc_html(__('Download','js-support-ticket')).'</a>
                                                            </div>', JSST_ALLOWED_TAGS);
                                                    }
                                                ?>
                                                </div>
                                                <div class="js-ticket-thread-cnt-btm">
                                                    <div class="js-ticket-thread-date"><?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_note->created))); ?></div>
                                                    <div class="js-ticket-thread-actions">
                                                        <?php
                                                        if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                            $jsst_hours = floor($jsst_note->usertime / 3600);
                                                            $jsst_mins = floor($jsst_note->usertime / 60);
                                                            $jsst_mins = floor($jsst_mins % 60);
                                                            $jsst_secs = floor($jsst_note->usertime % 60);
                                                            $jsst_time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                            $jsst_nonce = wp_create_nonce("get-time-by-note-id-".$jsst_note->id); ?>
                                                            <a title="<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="js-ticket-thread-actn-btn ticket-edit-time-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_note->id);?>,3, '<?php echo esc_js($jsst_nonce);?>')" >
                                                                <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                            </a>
                                                            <?php
                                                        } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                                <?php /* Hidden: the composer below already has an Internal note
                                         tab, so this was a second way to reach the same editor.
                                         Kept in the page for the N shortcut. */ ?>
                                <div class="js-ticket-thread-add-btn" hidden>
                                    <a title="<?php echo esc_attr(__('Post New Internal Note','js-support-ticket')); ?>" href="#" class="js-ticket-thread-add-btn-link" id="int-note">
                                        <img alt = "<?php echo esc_attr(__('Post New Internal Note','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png" />
                                        <span><?php echo esc_html(__('Post New Internal Note','js-support-ticket')); ?></span>
                                    </a>
                                </div>
                            <?php } ?>
                        <!-- Tickect  Reply  Area -->
                        <div class="js-tkt-det-title">
                            <?php echo esc_html(__('Ticket Thread', 'js-support-ticket')); ?>
                            <?php /* The opening message plus every reply. */ ?>
                            <span class="jsst-title-count"><?php echo esc_html(1 + (!empty(jssupportticket::$jsst_data[4]) ? count(jssupportticket::$jsst_data[4]) : 0)); ?></span>
                        </div>
                        <div class="js-ticket-thread">
                            <div class="js-ticket-thread-image">
                                <?php 
                                    echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid), JSST_ALLOWED_TAGS);
                                ?>
                            </div>
                            <div class="js-ticket-thread-cnt">
                                <div class="js-ticket-thread-data">
                                    <span class="js-ticket-thread-person">
                                        <?php echo esc_html(jssupportticket::$jsst_data[0]->name); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-thread-data">
                                    <span class="js-ticket-thread-email">
                                        <?php echo esc_html(jssupportticket::$jsst_data[0]->email); ?>
                                    </span>
                                </div>
                                <div class="js-ticket-thread-data note-msg">
                                    <?php echo wp_kses_post(jssupportticket::$jsst_data[0]->message);
                                    ?>
                                </div>

                                <?php
                                //zywrap ai
                                // We check if the user is a customer so we don't put buttons on our own replies
                                // Handle logic before output
                                $is_customer_check = isset($is_customer) ? $is_customer : true;
                                $is_latest_check   = isset($is_latest_overall) ? $is_latest_overall : (isset($is_latest) ? $is_latest : false);

                                // Only where the AI add-on is set up: without it these icons did nothing.
                                if ($is_customer_check && !empty($zywrap_is_active)) : 
                                    $active_flag = $zywrap_is_active ? '1' : '0';
                                    ?>
                                    <div style="display: none;" class="js-ticket-zywrap-inline-actions">

                                        <?php if ($is_latest_check) : ?>
                                            <button type="button" class="js-ticket-zywrap-open-tab-btn js-ticket-zywrap-btn-primary" data-tab="compose" data-active="<?php echo esc_attr($active_flag); ?>">
                                                <span class="dashicons dashicons-edit"></span> 
                                                <?php echo esc_html(__('Reply with Co-Pilot', 'js-support-ticket')); ?>
                                            </button>
                                            
                                            <button type="button" class="js-ticket-zywrap-open-tab-btn js-ticket-zywrap-btn-icon" data-tab="ask_info" data-active="<?php echo esc_attr($active_flag); ?>" title="<?php echo esc_attr__('Ask for Info', 'js-support-ticket'); ?>">
                                                <span class="dashicons dashicons-format-chat"></span>
                                            </button>
                                            
                                            <div class="js-ticket-zywrap-divider"></div>
                                        <?php endif; ?>

                                        <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="ts_support_ticket_condensed_summary_base" data-active="<?php echo esc_attr($active_flag); ?>" title="<?php echo esc_attr__('Summarize', 'js-support-ticket'); ?>">
                                            <span class="dashicons dashicons-text-page"></span>
                                        </button>
                                        
                                        <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="ee_support_ticket_detail_extraction_base" data-active="<?php echo esc_attr($active_flag); ?>" title="<?php echo esc_attr__('Extract Details', 'js-support-ticket'); ?>">
                                            <span class="dashicons dashicons-search"></span>
                                        </button>
                                        
                                        <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="tl_supp_tick_tran_loca_926d_base" data-lang="<?php echo esc_attr($zywrap_default_lang); ?>" data-active="<?php echo esc_attr($active_flag); ?>" title="<?php echo esc_attr__('Translate to', 'js-support-ticket'); ?> <?php echo esc_attr($zywrap_default_lang); ?>">
                                            <span class="dashicons dashicons-translation"></span>
                                        </button>
                                    </div>
                                    <div class="js-ticket-zywrap-inline-result" style="display:none;"></div>
                                <?php endif; ?>

                                <?php
                                    if (!empty(jssupportticket::$jsst_data['ticket_attachment'])) {
                                        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
                                        $jsst_maindir = wp_upload_dir();
                                        $jsst_path = $jsst_maindir['baseurl'];

                                        $jsst_path = $jsst_path .'/' . $jsst_datadirectory;
                                        $jsst_path = $jsst_path . '/attachmentdata';
                                        $jsst_path = $jsst_path . '/ticket/ticket_' . jssupportticket::$jsst_data[0]->id . '/';
                                        foreach (jssupportticket::$jsst_data['ticket_attachment'] AS $jsst_attachment) {
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
                                                $jsst_path = admin_url("?page=ticket&action=jstask&task=downloadbyid&id=".esc_attr($jsst_attachment->id));
                                                echo wp_kses('
                                                <div class="js_ticketattachment">
                                                    <span class="js_ticketattachment_fname">
                                                      ' . esc_html($jsst_attachment->filename) . /*' ( ' . esc_html($jsst_attachment->filesize) . ' ) ' . */'
                                                    </span>
                                                    <a title="'. esc_html(__('Download','js-support-ticket')).'" class="button" target="_blank" href="' . esc_url($jsst_path) . '">' . esc_html(__('Download', 'js-support-ticket')) . '</a>
                                                </div>', JSST_ALLOWED_TAGS);
                                            }
                                        }
                                    }
                                ?>
                                <div class="js-ticket-thread-cnt-btm">
                                    <div class="js-ticket-thread-date">
                                        <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->created))); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tickect  Reply  Area -->
                        <?php
                            $jsst_color1ed = "colored";
                            if (!empty(jssupportticket::$jsst_data[4]))
                                foreach (jssupportticket::$jsst_data[4] AS $key => $jsst_reply) {
                                if ($jsst_cur_uid == $jsst_reply->uid)
                                    $jsst_color1ed = '';
                                ?>
                                <?php
                                /* A withdrawn automatic answer. (Roadmap 6.0-AI-03)
                                   Marked rather than removed, and marked here so it is
                                   marked wherever the thread is read: the customer has
                                   the e-mail either way, and a ticket that no longer
                                   contains what they were sent is a ticket nobody can
                                   reason about - it reads as the desk hiding something.
                                   isRetracted() answers from one query per request, not
                                   one per reply. */
                                $jsst_reply_pulled = class_exists('JSSTaireview')
                                    && JSSTaireview::isRetracted($jsst_reply->id);
                                ?>
                                <div class="js-ticket-thread<?php echo $jsst_reply_pulled ? ' jsst-reply-withdrawn' : ''; ?>">
                                    <div class="js-ticket-thread-image">
                                        <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $jsst_reply->staffphoto) { ?>
                                            <img alt = "<?php echo esc_attr(__('agent image','js-support-ticket')); ?>"  src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=>$jsst_reply->staffid,'jsstpageid'=>jssupportticket::getPageid()))); ?>">
                                        <?php } else { */
                                            echo wp_kses(jsst_get_avatar($jsst_reply->uid), JSST_ALLOWED_TAGS);
                                        // } ?>
                                    </div>
                                    <div class="js-ticket-thread-cnt">
                                        <div class="js-ticket-thread-data">
                                            <span class="js-ticket-thread-person"><?php echo esc_html($jsst_reply->name); ?></span>
                                            <?php if ($jsst_reply_pulled) { ?>
                                                <span class="jsst-pill jsst-pill-bad"><span class="jsst-dot"></span><?php echo esc_html(__('Withdrawn', 'js-support-ticket')); ?></span>
                                            <?php } ?>
                                            <?php
                                            if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                if($jsst_reply->time > 0 ){
                                                   $jsst_hours = floor($jsst_reply->time / 3600);
                                                   $jsst_mins = floor($jsst_reply->time / 60);
                                                   $jsst_mins = floor($jsst_mins % 60);
                                                   $jsst_secs = floor($jsst_reply->time % 60);
                                                   $jsst_time = esc_html(__('Time Taken','js-support-ticket')).':&nbsp;'.sprintf('%02d:%02d:%02d', esc_html($jsst_hours), esc_html($jsst_mins), esc_html($jsst_secs));
                                                    ?>
                                                    <span class="js-ticket-thread-time"><?php echo esc_html($jsst_time); ?></span>
                                                    <?php
                                                }
                                            }
                                            if (jssupportticket::$_config['show_read_receipt_to_admin_on_reply'] == 1 && !empty($jsst_reply->viewed_by) && $jsst_cur_uid == $jsst_reply->uid) { ?>
                                                <span class="js-ticket-thread-read-status-wrp">
                                                    <span class="js-ticket-thread-read-status-btn">
                                                       <img alt = "<?php echo esc_attr(__('View Image','js-support-ticket')) ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/view.png" />
                                                    </span>
                                                    <span class="js-ticket-thread-read-status-detail">
                                                        <span class="js-ticket-thread-read-status-row">
                                                            <?php 
                                                            echo '<b>'.esc_html(__('Viewed By','js-support-ticket').': ').'</b>';
                                                            if ($jsst_reply->viewed_by == -1) {
                                                                echo esc_html(__('Guest', 'js-support-ticket'));
                                                            } else {
                                                                echo esc_html($jsst_reply->viewername);
                                                            }
                                                            ?>
                                                        </span>
                                                        <span class="js-ticket-thread-read-status-row">
                                                            <?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_reply->viewed_on))); ?>
                                                        </span>
                                                    </span>
                                                </span>
                                                <?php 
                                            } ?>
                                            <?php /* Whether this reply may be offered to the AI as an
                                               example. (Roadmap 6.0-AI-01)

                                               A setting on the reply rather than something to do to
                                               it, so it sits in the reply's header as a labelled
                                               switch and is always shown. It used to be a button in
                                               the actions row that stayed invisible until the reply
                                               was hovered, which read as one more action and was easy
                                               to miss.

                                               Two states, not three, because the third never meant
                                               anything for a reply: the search excludes 2 and takes
                                               everything else, so Enable and Default produced
                                               identical results. A reply already stored as Enable
                                               reads as on and keeps its stored value. The
                                               ticket-level control keeps all three, where Enable is
                                               what the Enable Tickets filter looks for. */
                                            if (JSSTaipolicy::onsiteFeature('aipoweredreply')
                                                    /* Role-based, as it is on the portal. An agent whose role
                                                       does not grant "Set AI Reply Mode for Reply" does not get
                                                       the toggle - this screen checked only the lane, so the
                                                       same agent was refused in one workspace and offered it in
                                                       the other. Administrators are not staff rows and are
                                                       through, which is how every other check here reads. */
                                                    && (!in_array('agent', jssupportticket::$_active_addons)
                                                        || !JSSTincluder::getJSModel('agent')->isUserStaff()
                                                        || JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Set AI Reply Mode for Reply'))
                                                    && jssupportticket::$jsst_data[0]->uid != $jsst_reply->uid
                                                    && $jsst_reply->uid != 0) {
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
                                        <div class="js-ticket-thread-data">
                                            <span class="js-ticket-via-email">
                                                <?php echo ($jsst_reply->ticketviaemail == 1) ? esc_html(__('Created via Email', 'js-support-ticket')) : ''; ?>
                                            </span>
                                        </div>
                                        <div class="js-ticket-thread-data note-msg">
                                            <?php // A ticket link in a stored reply belongs to whoever is reading it. (Roadmap 4.0-CORE-01)
                                            echo wp_kses_post(JSSTticketlink::resolve(html_entity_decode($jsst_reply->message))); ?>
                                        </div>
                                        <?php
                                        // Zywrap AI Inline Actions (Threaded Replies)
                                        $js_ticket_is_customer = ($jsst_reply->uid == jssupportticket::$jsst_data[0]->uid);
                                        $js_ticket_is_latest   = ($key == count(jssupportticket::$jsst_data[4]) - 1);

                                        // Upsell Logic: Only hide if it's NOT a customer. If it is a customer, show buttons but track active state.
                                        if ($js_ticket_is_customer && !empty($zywrap_is_active)) :
                                            $js_ticket_active_flag = $zywrap_is_active ? '1' : '0';
                                            ?>
                                            <div style="display: none;" class="js-ticket-zywrap-inline-actions">

                                                <?php if ($js_ticket_is_latest) : ?>
                                                    <button type="button" class="js-ticket-zywrap-open-tab-btn js-ticket-zywrap-btn-primary" data-tab="compose" data-active="<?php echo esc_attr($js_ticket_active_flag); ?>">
                                                        <span class="dashicons dashicons-edit"></span> 
                                                        <?php echo esc_html(__('Reply with Co-Pilot', 'js-support-ticket')); ?>
                                                    </button>
                                                    
                                                    <button type="button" class="js-ticket-zywrap-open-tab-btn js-ticket-zywrap-btn-icon" data-tab="ask_info" data-active="<?php echo esc_attr($js_ticket_active_flag); ?>" title="<?php echo esc_attr(__('Ask for Info', 'js-support-ticket')); ?>">
                                                        <span class="dashicons dashicons-format-chat"></span>
                                                    </button>
                                                    
                                                    <div class="js-ticket-zywrap-divider"></div>
                                                <?php endif; ?>

                                                <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="ts_support_ticket_condensed_summary_base" data-active="<?php echo esc_attr($js_ticket_active_flag); ?>" title="<?php echo esc_attr(__('Summarize', 'js-support-ticket')); ?>">
                                                    <span class="dashicons dashicons-text-page"></span>
                                                </button>
                                                
                                                <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="ee_support_ticket_detail_extraction_base" data-active="<?php echo esc_attr($js_ticket_active_flag); ?>" title="<?php echo esc_attr(__('Extract Details', 'js-support-ticket')); ?>">
                                                    <span class="dashicons dashicons-search"></span>
                                                </button>
                                                
                                                <button type="button" class="js-ticket-zywrap-inline-ai-btn js-ticket-zywrap-btn-icon" data-wrapper="tl_supp_tick_tran_loca_926d_base" data-lang="<?php echo esc_attr($zywrap_default_lang); ?>" data-active="<?php echo esc_attr($js_ticket_active_flag); ?>" title="<?php echo esc_attr(__('Translate to', 'js-support-ticket')); ?> <?php echo esc_attr($zywrap_default_lang); ?>">
                                                    <span class="dashicons dashicons-translation"></span>
                                                </button>
                                            </div>

                                            <div class="js-ticket-zywrap-inline-result" style="display:none;"></div>
                                        <?php endif; ?>
                                        <?php
                                            if (!empty($jsst_reply->attachments)) {
                                                foreach ($jsst_reply->attachments AS $jsst_attachment) {
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
                                                        // Render Standard Downloadable File
                                                        $jsst_imgpath = $jsst_attachment->filename;
                                                        $jsst_data = wp_check_filetype($jsst_attachment->filename);
                                                        $jsst_type = $jsst_data['type'];
                                                        $jsst_count = 0;
                                                        $jsst_path = esc_url(admin_url("?page=ticket&action=jstask&task=downloadbyid&id=".esc_attr($jsst_attachment->id)));
                                                        echo wp_kses('
                                                        <div class="js_ticketattachment">
                                                            <span class="js_ticketattachment_fname">
                                                            ' . esc_html($jsst_attachment->filename) . /*' ( ' . esc_html($jsst_attachment->filesize) . ' ) ' .*/ '
                                                            </span>
                                                            <a title="'. esc_html(__('Download','js-support-ticket')).'" class="button" target="_blank" href="' . esc_url($jsst_path) . '">' . esc_html(__('Download', 'js-support-ticket')) . '</a>', JSST_ALLOWED_TAGS);
                                                            if(jssupportticketphplib::JSST_strpos($jsst_type, "image") !== false) {
                                                                $jsst_path = JSSTincluder::getJSModel('attachment')->getAttachmentImage($jsst_attachment->id);
                                                                echo wp_kses('<a data-gall="gallery-'.esc_attr($jsst_reply->replyid).'" class="button venobox" data-vbtype="image" title="'. esc_html(__('View','js-support-ticket')).'" href="'. esc_attr($jsst_path) .'"  target="_blank">
                                                                    <img alt="'. esc_html(__('View Image','js-support-ticket')).'" src="' . esc_url(JSST_PLUGIN_URL) . 'includes/images/ticket-detail/view.png" />
                                                                </a>', JSST_ALLOWED_TAGS);
                                                            }
                                                        echo '</div>';
                                                    }
                                                }
                                            }
                                        ?>
                                        <div class="js-ticket-thread-cnt-btm">
                                            <div class="js-ticket-thread-date"><?php echo esc_html(date_i18n("l F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_reply->created))); ?></div>
                                            <div class="js-ticket-thread-actions">
                                               <?php
                                               if(in_array('timetracking', jssupportticket::$_active_addons)){
                                                    if($jsst_reply->time > 0 ){
                                                        $jsst_nonce = wp_create_nonce("get-time-by-reply-id-".$jsst_reply->replyid); ?>
                                                        <a title="<?php echo esc_attr(__('Edit Time','js-support-ticket')); ?>" class="js-ticket-thread-actn-btn ticket-edit-time-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_reply->replyid);?>,2, '<?php echo esc_js($jsst_nonce);?>')" >
                                                           <img alt = "<?php echo esc_attr(__('Edit Time','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                           <span><?php echo esc_html(__('Edit Time','js-support-ticket')); ?></span>
                                                        </a>
                                                    <?php
                                                    }
                                                }
                                                if($jsst_reply->staffid != 0 && JSSTroles::canEditReply()){
                                                    $jsst_nonce = wp_create_nonce('get-reply-data-by-id-'.$jsst_reply->replyid); ?>
                                                    <a title="<?php echo esc_attr(__('Edit Reply','js-support-ticket')); ?>" class="js-ticket-thread-actn-btn ticket-edit-reply-button" href="#" onclick="return showPopupAndFillValues(<?php echo esc_js($jsst_reply->replyid);?>,1, '<?php echo esc_js($jsst_nonce);?>')" >
                                                       <img alt = "<?php echo esc_attr(__('Edit Reply','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-reply.png" />
                                                       <span><?php echo esc_html(__('Edit Reply','js-support-ticket')); ?></span>
                                                    </a>
                                                    <?php
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            do_action('jsst_after_ticket_replies', jssupportticket::$jsst_data[0]->id); ?>
                        <!-- Post Reply Area -->
                        <?php
                        /* One composer, two modes. (Roadmap 4.0-UX-03)
                         *
                         * Posting an internal note used to mean opening a modal that looked nothing
                         * like the reply box and sat on top of the conversation. Two different-looking
                         * writing surfaces for the same act of typing into a ticket is how a note
                         * meant for colleagues gets sent to the customer — the trust incident this
                         * screen exists to avoid.
                         *
                         * Both forms are exactly the ones that were there before: same action URLs,
                         * same nonces, same field names, same submit handlers. Only where they sit and
                         * how loudly they say what they are has changed. The mode is stated three ways
                         * at once — the selected tab, a banner, and the colour of the whole composer —
                         * because one of them alone is the sort of thing an agent stops seeing.
                         */
                        $jsst_notes_enabled = JSSTmergedaddon::featureEnabled('note');
                        /* A light agent may not answer the customer, so the public
                           tab and its form are not rendered for them at all — the
                           server refuses the post either way, and offering a box
                           that will be rejected is not a permission model.
                           (Roadmap 4.0-SEC-04) */
                        $jsst_can_reply = JSSTroles::canReplyPublicly();
                        $jsst_composer_mode = $jsst_can_reply ? 'public' : 'internal';
                        ?>
                        <div id="jsst-composer" class="jsst-composer jsst-composer-<?php echo esc_attr($jsst_composer_mode); ?>">
                        <?php // What the draft script needs. (Roadmap 4.0-UX-04) ?>
                        <input type="hidden" id="jsst-draft-ticketid" value="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>" />
                        <input type="hidden" id="jsst-draft-nonce" value="<?php echo esc_attr(wp_create_nonce('jsst-draft')); ?>" />
                        <?php
                        /* Who else is here. (Roadmap 4.0-UX-05)
                         *
                         * Printed empty and filled in by the heartbeat, so a ticket
                         * nobody else is on shows nothing at all and the composer keeps
                         * the height it has always had. The reply id is stamped now,
                         * while the page is being built, because the whole point of the
                         * second signal is to compare what the agent is looking at
                         * against what the ticket has since become.
                         */
                        if (class_exists('JSSTpresence') && JSSTpresence::mayWatch()) { ?>
                            <input type="hidden" id="jsst-presence-nonce" value="<?php echo esc_attr(wp_create_nonce('jsst-presence')); ?>" />
                            <input type="hidden" id="jsst-presence-latestreply" value="<?php echo esc_attr(JSSTpresence::latestReplyId(jssupportticket::$jsst_data[0]->id)); ?>" />
                            <div class="jsst-presence" role="status" aria-live="polite" style="display:none"></div>
                            <div class="jsst-presence jsst-presence-stale" role="alert" style="display:none">
                                <span class="jsst-presence-text"><?php echo esc_html(__('Somebody else replied to this ticket while you were writing. Reload before you send, or the customer gets two answers.', 'js-support-ticket')); ?></span>
                                <button type="button" class="button jsst-presence-reload"><?php echo esc_html(__('Reload the ticket', 'js-support-ticket')); ?></button>
                            </div>
                        <?php } ?>
                            <div class="jsst-composer-modes" role="tablist" aria-label="<?php echo esc_attr(__('What kind of message', 'js-support-ticket')); ?>">
                                <?php if ($jsst_can_reply) { ?>
                                    <button type="button" class="jsst-composer-mode jsst-composer-mode-public" data-mode="public" role="tab" aria-selected="true">
                                        <?php echo esc_html(__('Public reply', 'js-support-ticket')); ?>
                                    </button>
                                <?php } ?>
                                <?php if ($jsst_notes_enabled) { ?>
                                    <button type="button" class="jsst-composer-mode jsst-composer-mode-internal" data-mode="internal" role="tab" aria-selected="false">
                                        <?php echo esc_html(__('Internal note', 'js-support-ticket')); ?>
                                    </button>
                                <?php } ?>
                            </div>
                            <?php if ($jsst_can_reply) { ?>
                                <div class="jsst-composer-banner jsst-composer-banner-public" role="status" aria-live="polite">
                                    <?php echo esc_html(__('The customer will see this reply and be notified of it.', 'js-support-ticket')); ?>
                                </div>
                            <?php } ?>
                            <?php if ($jsst_notes_enabled) { ?>
                                <div class="jsst-composer-banner jsst-composer-banner-internal" role="status" aria-live="polite">
                                    <strong><?php echo esc_html(__('Internal note — the customer cannot see this.', 'js-support-ticket')); ?></strong>
                                    <?php echo esc_html(__('It is visible to agents only, and no notification is sent to the customer.', 'js-support-ticket')); ?>
                                </div>
                            <?php } ?>
                            <?php
                        // A draft the server kept, offered rather than applied.
                        // (Roadmap 4.0-UX-04)
                        $jsst_draft_ticketid = (int) jssupportticket::$jsst_data[0]->id;
                        $jsst_drafts = array();
                        if (class_exists('JSSTdraft')) {
                            foreach (array('public', 'internal') as $jsst_dmode) {
                                $jsst_d = JSSTdraft::get(get_current_user_id(), $jsst_draft_ticketid, $jsst_dmode);
                                if (!empty($jsst_d['body'])) {
                                    $jsst_drafts[$jsst_dmode] = $jsst_d;
                                }
                            }
                        }
                        foreach ($jsst_drafts AS $jsst_dmode => $jsst_d) { ?>
                            <div class="jsst-draft-restore" data-mode="<?php echo esc_attr($jsst_dmode); ?>" data-body="<?php echo esc_attr($jsst_d['body']); ?>">
                                <span class="jsst-draft-text"><?php echo esc_html(sprintf(
                                    /* translators: 1: public reply or internal note, 2: when it was saved */
                                    __('You have an unsent %1$s from %2$s.', 'js-support-ticket'),
                                    ($jsst_dmode === 'internal') ? __('internal note', 'js-support-ticket') : __('reply', 'js-support-ticket'),
                                    date_i18n(jssupportticket::$_config['date_format'] . ' H:i', (int) $jsst_d['saved'])
                                )); ?></span>
                                <button type="button" class="button jsst-draft-restore-btn"><?php echo esc_html(__('Restore it', 'js-support-ticket')); ?></button>
                                <button type="button" class="button jsst-draft-discard-btn"><?php echo esc_html(__('Discard', 'js-support-ticket')); ?></button>
                            </div>
                        <?php } ?>
                        <?php if ($jsst_can_reply) { ?>
                        <div class="jsst-composer-panel jsst-composer-panel-public">
                        <div id="postreply" class="js-det-tkt-rply-frm">
                            <form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=reply&task=savereply"),"save-reply-".jssupportticket::$jsst_data[0]->id)); ?>"  enctype="multipart/form-data">
                                <div class="js-tkt-det-title"><?php echo esc_html(__('Post Reply', 'js-support-ticket')); ?></div>
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
                                                <?php if(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Own Time')){ ?>
                                                    <span class="timer-button" onclick="showEditTimerPopup(this)" >
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time-1.png"/>
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png"/>
                                                    </span>
                                                <?php } ?>
                                                <span class="timer-button cls_1" onclick="changeTimerStatus(1, this)" >
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time.png"/>
                                                </span>
                                                <span class="timer-button cls_2" onclick="changeTimerStatus(2, this)" >
                                                    <img <?php echo esc_html(__('pause','js-support-ticket')); ?> class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time-1.png"/>
                                                    <img <?php echo esc_html(__('pause','js-support-ticket')); ?> class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time.png"/>
                                                </span>
                                                <span class="timer-button cls_3" onclick="changeTimerStatus(3, this)" >
                                                    <img <?php echo esc_html(__('stop','js-support-ticket')); ?> class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time-1.png"/>
                                                    <img <?php echo esc_html(__('stop','js-support-ticket')); ?> class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time.png"/>
                                                </span>
                                            </div>
                                        </div>
                                        <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>
                                        <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                <?php } ?>
                                <?php /* The Copilot's buttons. Draws nothing without an
                                         engine that can answer. */
                                if (class_exists('JSSTcopilot')) {
                                    JSSTcopilot::renderPanel(jssupportticket::$jsst_data[0]->id);
                                } ?>
                                <?php /* And the other ways this customer has
                                         reached you, when there is more than
                                         one. Nothing on a desk with one channel.
                                         (Roadmap 6.0-CH-02) */
                                if (class_exists('JSSTchannels')) {
                                    echo wp_kses_post(JSSTchannels::panel(jssupportticket::$jsst_data[0]->id));
                                } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title" style="padding-bottom:10px;">
                                        <label id="responcemsg" for="responce"><?php echo esc_html(__('Response', 'js-support-ticket')); ?><span style="color: red;" >*</span></label>
                                    </div>
                            <?php /* Suggested replies, rebuilt. (Roadmap 6.0-AI-01)

                               What was here: a bordered card with an icon, a
                               product name, a sentence of marketing and a
                               button - below the editor - which opened a
                               second bordered panel containing a third set of
                               bordered cards, with a filter and a Close.

                               What it is now: a link beside the Response
                               label, and a plain list where the answers appear
                               above the box they are going into. One trigger,
                               one list, one action per answer.

                               Position matters as much as the chrome did.
                               Suggestions belong between deciding to write and
                               writing: above the editor, so an answer is read
                               and then lands in the box directly beneath it.
                               Below the editor they were something you found
                               after you had already started typing. */ ?>
                            <div class="js-ticket-suggest">
                                <span class="js-ticket-suggest-lead"><?php echo esc_html__('Write this reply with help:', 'js-support-ticket'); ?></span>
                                <?php /* What each one does is on the button rather than under
                                   the pair. The sentence that was here named both buttons a
                                   second time, in the order they already appear, and it was
                                   flex: 1 1 100% - a permanent second line, above the editor,
                                   on the screen an agent opens more often than any other. Read
                                   once on the first ticket and furniture from the second. A
                                   title is the same words at the moment somebody is actually
                                   choosing between the two. */ ?>
                                <button type="button" id="js-ticket-ai-reply-btn" class="js-ticket-help-btn js-ticket-help-btn-primary"
                                        title="<?php echo esc_attr__('Finds answers already written here — your knowledge base, FAQs and past replies.', 'js-support-ticket'); ?>">
                                    <?php /* A magnifier against the Copilot pencil: this one looks
                                       something up, that one writes something down. The pair is
                                       readable before either label is. */ ?>
                                    <span class="dashicons dashicons-search" aria-hidden="true"></span>
                                    <?php /* Named for what it does, and against what sits beside it:
                                       this one finds an answer somebody here has already
                                       written, the Copilot writes a new one. "Suggest a
                                       reply" said neither, and repeated the sentence it
                                       was already sitting under. */
                                    echo esc_html__('AI Agent', 'js-support-ticket'); ?>
                                </button>
                                <?php /* And the Copilot only where there is one to open. It used
                                   to be drawn whatever the site had, carrying data-active="0",
                                   and clicking it put a browser alert on the screen saying the
                                   product is not configured - the ordinary state of every desk
                                   that has not bought it. Same reasoning as the triage panel
                                   above: a control advertising a switched-off feature on the
                                   screen an agent uses most is worse than no control. The
                                   thread below still offers the Copilot on every customer
                                   reply, which is where the upsell was deliberately put. */
                                if ($zywrap_is_active) { ?>
                                    <button type="button" class="zywrap-open-tab-btn js-ticket-help-btn" data-tab="compose" data-active="1"
                                            title="<?php echo esc_attr__('Writes new text for this reply.', 'js-support-ticket'); ?>">
                                        <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                        <?php echo esc_html(__('AI Copilot','js-support-ticket')); ?>
                                    </button>
                                <?php } ?>
                                <?php /* The third way of getting text into this box, and until
                                   now the only one below it. (Roadmap 4.0-CORE-03)

                                   It was a bare row of links under the editor - every canned
                                   response on the desk, unlabelled, alphabetised, with no
                                   heading and no way to narrow them. Everything said about the
                                   AI pair applies to it word for word: a pre-written answer is
                                   read *before* you start typing, so it belongs above the box
                                   it lands in, under the same sentence saying what these
                                   controls are for.

                                   A select rather than more buttons, because this is the one of
                                   the three whose length is the customer's to decide. Two AI
                                   controls are two controls forever; canned responses are three
                                   on one desk and forty on the next, and forty links between
                                   the editor and the attachment field is what was there. The
                                   model function is called `...ForCombobox` because a combobox
                                   is what it was written for; every other screen in the product
                                   already draws it as one.

                                   Drawn only when this department has something to offer - an
                                   empty picker beside a live one reads as a broken control. */
                                if (JSSTmergedaddon::featureEnabled('cannedresponses')) {
                                    /* The ticket's own department. This screen has always known
                                       it and has never used it: the add-ticket screens narrow
                                       the list to the department being chosen, and the screen
                                       where somebody answers showed every department's. */
                                    $jsst_ticketdept = isset(jssupportticket::$jsst_data[0]->departmentid)
                                        ? jssupportticket::$jsst_data[0]->departmentid : '';
                                    $jsst_cannedresponses = JSSTincluder::getJSModel('cannedresponses')
                                        ->getPreMadeMessageForCombobox($jsst_ticketdept);
                                    if (!empty($jsst_cannedresponses)) { ?>
                                        <span class="js-ticket-canned">
                                            <?php /* A box you can type in, and a list that narrows as you
                                               do. (Roadmap 4.0-CORE-03)

                                               A `<select>` was the first version of this and it is what
                                               the rest of the product still draws, but a select can only
                                               be searched by its first letter, and the thing an agent
                                               remembers about a canned response is a word from the
                                               middle of its title. Written out rather than reached for
                                               off a shelf: the whole behaviour is a filter, a highlight
                                               and four keys, and every autocomplete library on the page
                                               would be a download to do that.

                                               The options are rendered here, all of them, already
                                               narrowed to this ticket's department. Filtering is done
                                               against text that is already on the page, so typing costs
                                               no request and works with the network off. */ ?>
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
                                               the editor, including whatever the agent had already
                                               typed. It also sat in a wrapper classed
                                               `js-ticket-detail-append-signature-xs` - the signature
                                               block's class, on the canned-response control. */ ?>
                                            <span class="js-ticket-canned-append">
                                                <?php echo wp_kses(JSSTformfield::checkbox('append_premade',
                                                    array('1' => esc_html(__('Add to what I have written', 'js-support-ticket'))), '',
                                                    array('class' => 'radiobutton',
                                                          'title' => esc_attr__('Off, the canned response replaces everything in the box.', 'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                                            </span>
                                        </span>
                                    <?php }
                                } ?>
                                <?php /* Where anything that went wrong is said. Empty almost
                                   always, and `:empty` takes it out of the row when it is, so
                                   it costs no space until it has something to say. A live
                                   region, because the thing it replaced announced itself by
                                   covering the screen. */ ?>
                                <span class="js-ticket-suggest-status" id="js-ticket-suggest-status" role="status" aria-live="polite"></span>
                            </div>
                            <span class="js-ticket-current-ticket-title"><?php echo esc_html( jssupportticket::$jsst_data[0]->subject ); ?></span>
                            <span class="js-ticket-current-ticket-id"><?php echo esc_html( jssupportticket::$jsst_data[0]->id ); ?></span>
                            <div class="js-ticket-container">
                                <div id="js-ticket-matching-tickets-section" class="js-ticket-section js-ticket-matching-tickets-section js-ticket-hidden">
                                    <div class="js-ticket-selected-tickets-header">
                                        <h2 class="js-ticket-section-heading"><?php echo esc_html__('Suggested replies', 'js-support-ticket'); ?></h2>
                                        <?php /* The filter is shown only when tickets are what answered -
                                           the script hides it when the suggestions came from the
                                           knowledge base, where "From any ticket" describes nothing. */
                                        if(JSSTaipolicy::onsiteFeature('aipoweredreply')){ ?>
                                            <div class="js-ticket-filter-group">
                                                <label for="js-ticket-tickets-filter" class="js-ticket-filter-label"><?php echo esc_html__('Show', 'js-support-ticket'); ?></label>
                                                <select id="js-ticket-tickets-filter" class="js-ticket-filter-select">
                                                    <option value="all"><?php echo esc_html__('Everything', 'js-support-ticket'); ?></option>
                                                    <option value="marked"><?php echo esc_html__('Only preferred tickets', 'js-support-ticket'); ?></option>
                                                </select>
                                            </div>
                                        <?php } ?>
                                        <button id="js-ticket-close-tickets-btn" class="js-ticket-close-button">
                                            <?php echo esc_html__('Hide', 'js-support-ticket'); ?>
                                        </button>
                                    </div>
                                    <ul id="js-ticket-matching-tickets-list" class="js-ticket-list">
                                    </ul>
                                </div>

                                        <!-- Selected Ticket Replies Section -->
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
                                                <button id="js-ticket-close-replies-btn" class="js-ticket-close-button">
                                                    <?php echo esc_html__('Close', 'js-support-ticket'); ?>
                                                </button>
                                            </div>
                                            <div id="js-ticket-selected-ticket-replies-content" class="js-ticket-replies-content reply-content">
                                                <!-- Replies from selected ticket will be dynamically inserted here -->
                                            </div>
                                        </div>

                                    </div>
                                    <div class="js-form-value"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                </div>

                                <?php 
                                // Include the Modal UI at the bottom of the file
                                $modal_path = JSST_PLUGIN_PATH . 'modules/zywrap/tpls/admin_modal.php';
                                if(file_exists($modal_path)) {
                                    include_once($modal_path);
                                }
                                ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></div>
                                    <div class="js-form-field">
                                        <div class="tk_attachment_value_wrapperform">
                                            <span class="tk_attachment_value_text">
                                                <input type="file" class="inputbox" name="filename[]" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" maxlenght='30'/>
                                                <span class='tk_attachment_remove'></span>
                                            </span>
                                        </div>
                                        <span class="tk_attachments_configform">
                                            <small><?php esc_html(__('Maximum File Size','js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php esc_html(__('File Extension Type','js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?></small>
                                        </span>
                                        <span id="tk_attachment_add" class="tk_attachments_addform jsst-button-bg-link"><?php echo esc_html(__('Add More File','js-support-ticket')); ?></span>
                                    </div>
                                </div>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Append Signature','js-support-ticket')); ?></div>
                                    <div class="js-form-value jsst-sig-choice" role="radiogroup" aria-label="<?php echo esc_attr(__('Append Signature','js-support-ticket')); ?>">
                                        <?php /* One signature per reply, so one choice: real radio
                                                 buttons sharing a name. They were checkboxes that a
                                                 script un-ticked, which looked like "pick any" and
                                                 worked like "pick one". The reply handler turns
                                                 `signaturechoice` back into the three flags it reads.
                                                 None is the default - the same result as the old
                                                 nothing-ticked form. */
                                        $jsst_sigchoices = array(
                                            'own'        => __('Own Signature', 'js-support-ticket'),
                                            'department' => jssupportticket::JSST_getVarValue($jsst_field_array['department']) . ' ' . __('Signature', 'js-support-ticket'),
                                            'none'       => __('None', 'js-support-ticket'),
                                        );
                                        foreach ($jsst_sigchoices as $jsst_sigkey => $jsst_siglabel) { ?>
                                            <div class="jsst-formfield-radio-button-wrap">
                                                <label class="jsst-sig-option" for="signaturechoice-<?php echo esc_attr($jsst_sigkey); ?>">
                                                    <input type="radio" name="signaturechoice" id="signaturechoice-<?php echo esc_attr($jsst_sigkey); ?>" value="<?php echo esc_attr($jsst_sigkey); ?>" <?php checked($jsst_sigkey, 'none'); ?> />
                                                    <span><?php echo esc_html($jsst_siglabel); ?></span>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php
                                    $jsst_signature = get_user_meta(JSSTincluder::getObjectClass('user')->uid(), 'jsst_signature', true);
                                    if(!$jsst_signature){
                                        ?>
                                        <a class="js-add-signature" target= "_blank" href="<?php echo esc_url(admin_url('profile.php#jsstsignature')); ?>"><?php echo esc_html(__("Add Signature",'js-support-ticket')); ?></a>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                if ( in_array('agent',jssupportticket::$_active_addons) ) {
                                    $jsst_staffid = JSSTincluder::getJSModel('agent')->getStaffId(JSSTincluder::getObjectClass('user')->uid());
                                    if (jssupportticket::$jsst_data[0]->staffid != $jsst_staffid && $jsst_staffid != '') {?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><?php echo esc_html(__('Assign To Me', 'js-support-ticket')); ?></div>
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('assigntome', array('1' => esc_html(__('ASsign To Me', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <?php }
                                } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Ticket', 'js-support-ticket')); echo ' '; echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                    <div class="jsst-formfield-radio-button-wrap">
                                        <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close On Reply', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('postreply', esc_html(__('Post Reply','js-support-ticket')), array('class' => 'button js-form-save', 'onclick' => "return checktinymcebyid('jsticket_message');")), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('departmentid', jssupportticket::$jsst_data[0]->departmentid), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('ticketrandomid', jssupportticket::$jsst_data[0]->ticketid), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('hash', jssupportticket::$jsst_data[0]->hash), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('action', 'reply_savereply'), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            </form>
                        </div> <!-- end of postreply div -->
                            </div>
                            <?php } ?>
                            <?php if ($jsst_notes_enabled) { ?>
                                <div class="jsst-composer-panel jsst-composer-panel-internal">
<form class="js-det-tkt-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=note&task=savenote"),"save-note-".jssupportticket::$jsst_data[0]->id)); ?>"  enctype="multipart/form-data">
                                <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                                    <div class="jsst-ticket-detail-timer-wrapper"> <!-- Top Timer Section -->
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
                                                <?php if(in_array('agent', jssupportticket::$_active_addons) && JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Time')){ ?>
                                                    <span class="timer-button" onclick="showEditTimerPopup(this)" >
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time-1.png"/>
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png"/>
                                                    </span>
                                                <?php } ?>
                                                <span class="timer-button cls_1" onclick="changeTimerStatus(1, this)" >
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time.png"/>
                                                </span>
                                                <span class="timer-button cls_2" onclick="changeTimerStatus(2, this)" >
                                                    <img alt = "<?php echo esc_attr(__('pause','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('pause','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time.png"/>
                                                </span>
                                                <span class="timer-button cls_3" onclick="changeTimerStatus(3, this)" >
                                                    <img alt = "<?php echo esc_attr(__('stop','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('stop','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time.png"/>
                                                </span>
                                            </div>
                                        </div>
                                        <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>

                                        <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                <?php } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Note Title', 'js-support-ticket')); ?></div>
                                    <div class="js-form-value"><?php echo wp_kses(JSSTformfield::text('internalnotetitle', '', array('class' => 'inputbox js-admin-popup-input-field')), JSST_ALLOWED_TAGS) ?></div>
                                </div>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?></label></div>
                                    <div class="js-form-value">
                                        <?php wp_editor('', 'internalnote', array('media_buttons' => false)); ?>
                                        <?php if (class_exists('JSSTcollab')) { ?>
                                            <p class="jsst-field-note"><?php echo esc_html(__('Write @ and somebody\'s name to ask them by name. They are told, and they start following the ticket.', 'js-support-ticket')); ?></p>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                                /* Who the note is for. (Roadmap 4.5-FE-08)
                                 *
                                 * Off by default and it stays off unless
                                 * somebody ticks it, so every note written
                                 * before and after this shipped behaves the
                                 * same way. The warning about administrators
                                 * is on the screen rather than in the manual:
                                 * somebody restricting a note is deciding what
                                 * to write, and they need the limits before
                                 * they type, not after.
                                 */
                                if (class_exists('JSSTcollab')) {
                                    $jsst_noteagents = in_array('agent', jssupportticket::$_active_addons)
                                        ? JSSTincluder::getJSModel('agent')->getStaffForCombobox() : array(); ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><?php echo esc_html(__('Who can read it', 'js-support-ticket')); ?></div>
                                        <div class="js-form-value">
                                            <label class="jsst-check">
                                                <input type="checkbox" name="noterestrict" value="1" id="jsst-note-restrict" />
                                                <span><?php echo esc_html(__('Only the people I name below', 'js-support-ticket')); ?></span>
                                            </label>
                                            <?php /* The names are shown from the start, so "below" points at
                                                     something, and greyed out and DISABLED until the checkbox is
                                                     ticked.

                                                     It used to be neither. Every agent was listed, tickable,
                                                     always - and ticking them did nothing at all, because
                                                     storeTicketInternalNote() only calls restrictNote() when
                                                     `noterestrict` is set. So the one way to get this wrong
                                                     was the obvious one: name the two people you meant, miss
                                                     the checkbox above them, and post a note you believe is
                                                     private to every agent on the desk. Disabled inputs cannot
                                                     be ticked and are not submitted, which makes the two states
                                                     of this control mean exactly what they look like.

                                                     It was then hidden until the checkbox was ticked, which
                                                     kept that promise but left a label saying "below" over
                                                     nothing, so the names looked missing rather than waiting. */ ?>
                                            <?php if (!empty($jsst_noteagents)) { ?>
                                                <div class="jsst-checkgrid jsst-checkgrid-off" id="jsst-note-audience" aria-disabled="true">
                                                    <?php foreach ($jsst_noteagents AS $jsst_na) { ?>
                                                        <label class="jsst-checkcell">
                                                            <input type="checkbox" name="noteaudience[]" value="<?php echo esc_attr($jsst_na->id); ?>" disabled />
                                                            <span><?php echo esc_html($jsst_na->text); ?></span>
                                                        </label>
                                                    <?php } ?>
                                                </div>
                                            <?php } else { ?>
                                                <p class="jsst-field-note"><?php echo esc_html(__('There are no other agents to name. Restricted, the note is readable by you and by administrators.', 'js-support-ticket')); ?></p>
                                            <?php } ?>
                                            <p class="jsst-field-note"><?php echo esc_html(__('Left unticked, a note is what it has always been: readable by every agent who can see the ticket. Restricted, it is readable by the people named, by whoever wrote it, and by administrators — this is a courtesy between colleagues, not a secure channel, and a help desk an administrator cannot read is one nobody can support.', 'js-support-ticket')); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                    $jsst_jssupportticket_js = "
                                        jQuery(document).ready(function(){
                                            var box = jQuery('#jsst-note-restrict');
                                            var grid = jQuery('#jsst-note-audience');
                                            if (!box.length || !grid.length) { return; }
                                            function sync(){
                                                var on = box.is(':checked');
                                                grid.toggleClass('jsst-checkgrid-off', !on).attr('aria-disabled', on ? 'false' : 'true');
                                                /* Disabled, not merely hidden: a hidden input still posts,
                                                   and a name ticked before the reader changed their mind
                                                   would restrict a note they had decided to leave open. */
                                                grid.find('input[type=checkbox]').prop('disabled', !on);
                                                if (!on) { grid.find('input[type=checkbox]').prop('checked', false); }
                                            }
                                            box.on('change', sync);
                                            sync();
                                        });
                                    ";
                                    wp_add_inline_script('js-support-ticket-main-js', $jsst_jssupportticket_js);
                                    ?>
                                <?php } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Ticket', 'js-support-ticket')); echo ' '; echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                    <div class="js-form-value">
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close On Reply', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></div>
                                    <div class="js-form-value">
                                        <div class="tk_attachment_value_wrapperform">
                                            <span class="tk_attachment_value_text">
                                                <input type="file" class="inputbox" name="note_attachment" onchange="uploadfile(this, '<?php echo esc_js(jssupportticket::$_config['file_maximum_size']); ?>', '<?php echo esc_js(jssupportticket::$_config['file_extension']); ?>');" size="20" maxlenght='30'/>
                                                <span class='tk_attachment_remove'></span>
                                            </span>
                                        </div>
                                        <span class="tk_attachments_configform">
                                            <small><?php esc_html(__('Maximum File Size','js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_maximum_size']); ?>KB)<br><?php esc_html(__('File Extension Type','js-support-ticket'));
                                            echo ' (' . esc_html(jssupportticket::$_config['file_extension']) . ')'; ?></small>
                                        </span>
                                    </div>
                                </div>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('postinternalnote', esc_html(__('Post Internal Note','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block', 'onclick' => "return checktinymcebyid('internalnote');")), JSST_ALLOWED_TAGS); ?>
                                </div>
                                <?php echo wp_kses(JSSTformfield::hidden('ticketid', jssupportticket::$jsst_data[0]->id), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('uid', JSSTincluder::getObjectClass('user')->uid()), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('action', 'note_savenote'), JSST_ALLOWED_TAGS); ?>
                                <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                            </form>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- ticket detail right side -->
                    <div class="js-tkt-det-right">
                        <!-- ticket detail info -->
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-info">
                            <?php
                            /* One status, shown once: the ticket's real status in its own
                               colours. This block used to say "Open" for anything that was
                               not closed, while the Status box under it said "Replied" -
                               two answers to one question. The Change link that box carried
                               lives here now. */
                            $jsst_ticketmessage = jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->statustitle);
                            $jsst_stylecolor = jssupportticket::$jsst_data[0]->statuscolour;
                            $jsst_stylebgcolor = jssupportticket::$jsst_data[0]->statusbgcolour;
                            if ($jsst_ticketmessage == '') {
                                $jsst_ticketmessage = __('Open', 'js-support-ticket');
                                $jsst_stylecolor = '#FFFFFF';
                                $jsst_stylebgcolor = '#438323';
                            } ?>
                            <div class="js-tkt-det-status" style="background-color:<?php echo esc_attr($jsst_stylebgcolor)?>;color:<?php echo esc_attr($jsst_stylecolor);?>;">
                                <?php
                                    jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                                    echo esc_html($jsst_ticketmessage);
                                ?>
                            </div>
                            <?php if (JSSTroles::canChangeTicketState()) { ?>
                                <a title="<?php echo esc_attr(__('Change status','js-support-ticket')); ?>" href="#" class="jsst-det-status-change" id="chng-status"><?php echo esc_html(__('Change status','js-support-ticket')); ?></a>
                            <?php } ?>
                            <div class="js-tkt-det-info-cnt">
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Created','js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val" title="<?php echo esc_attr(date_i18n("d F, Y, H:i:s A", jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->created))); ?>">
                                        <?php echo esc_html(human_time_diff(strtotime(jssupportticket::$jsst_data[0]->created),strtotime(date_i18n("Y-m-d H:i:s")))).' '. esc_html(__('ago', 'js-support-ticket')); ?>
                                    </span>
                                </div>
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Last Reply', 'js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php
                                            if (empty(jssupportticket::$jsst_data[0]->lastreply) || jssupportticket::$jsst_data[0]->lastreply == '0000-00-00 00:00:00') echo esc_html(__('No Last Reply', 'js-support-ticket'));
                                            else echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->lastreply)));
                                        ?>
                                    </span>
                                </div>
                                <?php /*
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['duedate'])). ' : ' ; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php
                                            if (empty(jssupportticket::$jsst_data[0]->duedate) || jssupportticket::$jsst_data[0]->duedate == '0000-00-00 00:00:00') echo esc_html(__('Not Given', 'js-support-ticket'));
                                            else echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->duedate)));
                                        ?>
                                    </span>
                                </div> */?>
                                <?php if(JSSTmergedaddon::featureEnabled('helptopic') && !empty(jssupportticket::$jsst_data[0]->helptopic)){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <span class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['helptopic'])). ' : '; ?>
                                        </span>
                                        <span class="js-tkt-det-info-val">
                                            <?php if(JSSTmergedaddon::featureEnabled('helptopic')){ ?>
                                                <?php 
                                                    if (!empty(jssupportticket::$jsst_data[0]) && isset(jssupportticket::$jsst_data[0]->helptopic)) {
                                                        echo wp_kses_post(jssupportticket::$jsst_data[0]->helptopic);
                                                    }
                                                ?>
                                            <?php } ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                <?php
                                    // Ticket tags. Read-only for anyone who cannot edit the
                                    // ticket, editable inline for those who can.
                                    // (Roadmap 4.0-CORE-17)
                                    $jsst_tags = isset(jssupportticket::$jsst_data['tags']) ? jssupportticket::$jsst_data['tags'] : array();
                                    $jsst_cantag = JSSTroles::canChangeTicketState();
                                    if (!$jsst_cantag && !empty(jssupportticket::$jsst_data['user_staff'])) {
                                        $jsst_cantag = (JSSTincluder::getJSModel('userpermissions')->checkPermissionGrantedForTask('Edit Ticket') == 1);
                                    }
                                    $jsst_tagnames = array();
                                    foreach ($jsst_tags AS $jsst_tag) {
                                        $jsst_tagnames[] = $jsst_tag->name;
                                    }
                                ?>
                                <div class="js-tkt-det-info-data jsst-ticket-tags">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Tags','js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val jsst-tagbox-val">
                                        <?php
                                        /* The tags themselves are the resting state; the field that
                                           edits them is behind a <details>. Showing both at once
                                           printed every tag twice — once as a chip and again as
                                           comma text in the box — which reads as two different
                                           values rather than one. <details> rather than script so
                                           it still opens with JavaScript off. */
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
                                                <form class="jsst-tag-form" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=savetickettags&action=jstask"),"ticket-tags-".jssupportticket::$jsst_data[0]->id)); ?>">
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
                                                </form>
                                            </details>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php // Blank rows are hidden: a label with nothing after it reads as missing data.
                                if (!empty(jssupportticket::$jsst_data[0]->producttitle)) { ?>
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['product'])). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php 
                                            if (!empty(jssupportticket::$jsst_data[0]) && isset(jssupportticket::$jsst_data[0]->producttitle)) {
                                                echo wp_kses_post(jssupportticket::$jsst_data[0]->producttitle);
                                            }
                                        ?>
                                    </span>
                                </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php echo esc_html(jssupportticket::$jsst_data[0]->departmentname); ?>
                                    </span>
                                </div>
                                <?php if (jssupportticket::$_config['show_closedby_on_admin_tickets'] == 1 && jssupportticket::$jsst_data[0]->status == 5) { ?>
                                    <div class="js-tkt-det-info-data">
                                        <span class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed By','js-support-ticket')). ' : '; ?>
                                        </span>
                                        <span class="js-tkt-det-info-val">
                                            <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy(jssupportticket::$jsst_data[0]->closedby)); ?>
                                        </span>
                                    </div>
                                    <div class="js-tkt-det-info-data">
                                        <span class="js-tkt-det-info-tit">
                                            <?php echo esc_html(__('Closed On','js-support-ticket')). ' : '; ?>
                                        </span>
                                        <span class="js-tkt-det-info-val">
                                            <?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime(jssupportticket::$jsst_data[0]->closed))); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Ticket ID', 'js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php echo esc_html(jssupportticket::$jsst_data[0]->ticketid); ?>
                                        <a href="javascript:void(0)" title="<?php echo esc_attr(__('Copy','js-support-ticket')); ?>" class="js-tkt-det-copy-id" id="ticketidcopybtn" success="<?php echo esc_attr(__('Copied','js-support-ticket')); ?>"><?php echo esc_html(__('Copy','js-support-ticket')); ?></a>
                                    </span>
                                </div>
                                <?php if (jssupportticket::$jsst_data[0]->lock == 1 || jssupportticket::$jsst_data[0]->isoverdue == 1) { ?>
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Flags', 'js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val jsst-det-flags">
                                        <?php if (jssupportticket::$jsst_data[0]->lock == 1) { ?>
                                            <span class="jsst-pill jsst-pill-locked"><?php echo esc_html(__('Locked', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                        <?php if (jssupportticket::$jsst_data[0]->isoverdue == 1) { ?>
                                            <span class="jsst-pill jsst-pill-overdue"><?php echo esc_html(__('Overdue', 'js-support-ticket')); ?></span>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- ticket detail status -->
                        <?php if(JSSTaipolicy::onsiteFeature('aipoweredreply')){ ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty">
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                                    <?php /* One control that says what it does. (Roadmap 6.0-AI-01)

                                                       Three buttons labelled Default, Enable and Disable, with the
                                                       explanation hidden inside an info icon, asked somebody to
                                                       guess what was being enabled and then hover to find out. The
                                                       words are the setting now - each option is a sentence about
                                                       this ticket - and the note under it is the sentence that was
                                                       in the tooltip, said once and visible.

                                                       A select rather than buttons: it is one tab stop instead of
                                                       three, it announces the chosen value to a screen reader
                                                       without a `role="group"` and an aria-label doing that work by
                                                       hand, and on a phone it opens the native list. */ ?>
                                                    <label class="js-ticket-ai-mode-label" for="js-ticket-ai-ticket-mode"><?php
                                                        echo esc_html__('AI suggestions from this ticket', 'js-support-ticket'); ?></label>
                                                </div>
                                            </div>
                                            <div class="js-ticket-ai-reply-status-wrapper">
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
                        <?php } ?>
                        <!-- ticket detail priority -->
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-prty">
                            <div class="js-tkt-det-hdg">
                                <a target="blank" href="https://www.youtube.com/watch?v=8Fz-expKJLE" class="js-tkt-det-hdg-img js-cp-video-priority">
                                    <img title="<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
                                </a>
                                <div class="js-tkt-det-hdg-txt">
                                    <!-- Display heading based on field order  -->
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                                </div>
                                <?php if (JSSTroles::canChangeTicketState()) { ?>
                                <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="chng-prority">
                                    <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                </a>
                                <?php } ?>
                            </div>
                            
                                <?php
                                    if (!empty(jssupportticket::$jsst_data[0]->priority)) { ?>
                                        <div class="js-tkt-det-tkt-prty-txt" style="background:<?php echo esc_attr(jssupportticket::$jsst_data[0]->prioritycolour); ?>;">
                                            <?php
                                            echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->priority)); ?>
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
                        <!-- ticket detail assign to staff -->
                        <?php
                        $jsst_agentflag = in_array('agent', jssupportticket::$_active_addons);
                        $jsst_departmentflag = JSSTmergedaddon::featureEnabled('actions');
                        if($jsst_agentflag || $jsst_departmentflag){
                            ?>
                            <?php
                            /* Working on it together. (Roadmap 4.5-FE-08)
                             *
                             * Placed above assignment because the question it
                             * answers - who else is on this - is the one asked
                             * first, and because handing a ticket over is the
                             * thing you do after reading it rather than before.
                             */
                            if (class_exists('JSSTcollab') && $jsst_agentflag) {
                                $jsst_cticketid = (int) jssupportticket::$jsst_data[0]->id;
                                $jsst_cactor = JSSTcapability::actor();
                                $jsst_cme = isset($jsst_cactor['staffid']) ? (int) $jsst_cactor['staffid'] : 0;
                                $jsst_cwatchers = JSSTcollab::watchers($jsst_cticketid);
                                $jsst_cdrafts = JSSTcollab::draftsFor($jsst_cticketid);
                                $jsst_chandoff = JSSTcollab::handoffLines($jsst_cticketid);
                                $jsst_cagents = in_array('agent', jssupportticket::$_active_addons)
                                    ? JSSTincluder::getJSModel('agent')->getStaffForCombobox() : array();
                                $jsst_caction = wp_nonce_url(admin_url('admin.php?page=ticket&task=savecollab&action=jstask'), 'jsst-collab-' . $jsst_cticketid);
                                $jsst_ccanassign = JSSTcapability::can(JSSTcapability::TICKET_ASSIGN, array('ticket' => $jsst_cticketid), $jsst_cactor);
                                /* Reading the ticket is what marks a mention
                                   answered. Anything else means a badge that
                                   never clears, which teaches people to stop
                                   looking at it. */
                                if ($jsst_cme > 0) {
                                    JSSTcollab::acknowledgeMentions($jsst_cme, $jsst_cticketid);
                                }
                                ?>
                                <div class="js-tkt-det-cnt js-tkt-det-tkt-collab">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt"><?php echo esc_html(__('Who else is on this', 'js-support-ticket')); ?></div>
                                    </div>
                                    <div class="jsst-collab">
                                        <?php if (empty($jsst_cwatchers)) { ?>
                                            <p class="jsst-field-note"><?php echo esc_html(__('Nobody is following this ticket yet.', 'js-support-ticket')); ?></p>
                                        <?php } else { ?>
                                            <div class="jsst-chips">
                                                <?php foreach ($jsst_cwatchers AS $jsst_w) { ?>
                                                    <span class="jsst-chip <?php echo ($jsst_w->collabrole === JSSTcollab::ROLE_SECONDARY) ? 'jsst-chip-lead' : ''; ?>">
                                                        <?php echo esc_html($jsst_w->staffname ? $jsst_w->staffname : ('#' . $jsst_w->staffid)); ?>
                                                        <?php if ($jsst_w->collabrole === JSSTcollab::ROLE_SECONDARY) { echo ' · ' . esc_html(__('second pair of hands', 'js-support-ticket')); } ?>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>

                                        <form method="post" action="<?php echo esc_url($jsst_caction); ?>" class="jsst-collab-form">
                                            <input type="hidden" name="ticketid" value="<?php echo esc_attr($jsst_cticketid); ?>" />
                                            <div class="jsst-actions">
                                                <?php if ($jsst_cme > 0) { ?>
                                                    <button type="submit" name="collabfollow" value="1" class="button">
                                                        <?php echo esc_html(JSSTcollab::isWatching($jsst_cticketid, $jsst_cme)
                                                            ? __('Stop following', 'js-support-ticket')
                                                            : __('Follow this ticket', 'js-support-ticket')); ?>
                                                    </button>
                                                <?php } else { ?>
                                                    <?php /* Said rather than left out. Somebody who can administer the
                                                       site but is on nobody's agent list has no record to hang a
                                                       watch on, and a button that is simply absent reads as a bug.
                                                       (Roadmap 4.5-FE-08) */ ?>
                                                    <span class="jsst-actions-note"><?php echo esc_html(__('You are not on the agent list, so there is no record to follow this against — you can still put other people on it.', 'js-support-ticket')); ?></span>
                                                <?php } ?>
                                            </div>
                                        </form>

                                        <?php /* A SECOND form, deliberately.

                                                 All five controls used to share one, and `collabfollow` was
                                                 first in tree order - so pressing Enter after choosing
                                                 somebody in `Bring somebody in` did not add them, it
                                                 silently toggled your OWN following. Implicit submission
                                                 fires the first submit button in the form, and that is not
                                                 something markup can vary by section. Verified in the page:
                                                 submit order was collabfollow > collabadd > collabremove.

                                                 Following yourself and putting other people on the ticket
                                                 are two different decisions, so they are two forms, and
                                                 Enter in this one now does what the reader is looking at. */ ?>
                                        <?php if (($jsst_ccanassign && !empty($jsst_cagents))
                                                  || (!empty($jsst_cwatchers) && ($jsst_ccanassign || $jsst_cme > 0))) { ?>
                                        <?php /* The occasional sections fold away, so the card is the
                                                 people and the follow button until somebody needs more. */ ?>
                                        <details class="jsst-collab-more">
                                        <summary><?php echo esc_html(__('Add or remove people', 'js-support-ticket')); ?></summary>
                                        <form method="post" action="<?php echo esc_url($jsst_caction); ?>" class="jsst-collab-form">
                                            <input type="hidden" name="ticketid" value="<?php echo esc_attr($jsst_cticketid); ?>" />
                                            <?php if ($jsst_ccanassign && !empty($jsst_cagents)) { ?>
                                                <div class="jsst-fields">
                                                    <div class="jsst-field">
                                                        <label for="collabstaffid"><?php echo esc_html(__('Bring somebody in', 'js-support-ticket')); ?></label>
                                                        <?php echo wp_kses(JSSTformfield::select('collabstaffid', $jsst_cagents, '', esc_html(__('Choose an agent', 'js-support-ticket')), array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                                                    </div>
                                                    <div class="jsst-field">
                                                        <label for="collabrole"><?php echo esc_html(__('As', 'js-support-ticket')); ?></label>
                                                        <select name="collabrole" id="collabrole" class="inputbox">
                                                            <option value="watcher"><?php echo esc_html(__('Someone to keep informed', 'js-support-ticket')); ?></option>
                                                            <option value="secondary"><?php echo esc_html(__('A second pair of hands', 'js-support-ticket')); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="jsst-actions">
                                                    <button type="submit" name="collabadd" value="1" class="button"><?php echo esc_html(__('Add', 'js-support-ticket')); ?></button>
                                                    <span class="jsst-actions-note"><?php echo esc_html(__('A second pair of hands works on it; somebody kept informed only gets updates.', 'js-support-ticket')); ?></span>
                                                </div>
                                            <?php } ?>
                                            <?php if (!empty($jsst_cwatchers) && ($jsst_ccanassign || $jsst_cme > 0)) { ?>
                                                <div class="jsst-actions">
                                                    <?php foreach ($jsst_cwatchers AS $jsst_w) {
                                                        if (!$jsst_ccanassign && (int) $jsst_w->staffid !== $jsst_cme) { continue; } ?>
                                                        <button type="submit" name="collabremove" value="<?php echo esc_attr($jsst_w->staffid); ?>" class="jsst-act jsst-act-danger">
                                                            <?php
                                                            /* translators: %s: an agent's name */
                                                            echo esc_html(sprintf(__('Remove %s', 'js-support-ticket'), $jsst_w->staffname ? $jsst_w->staffname : ('#' . $jsst_w->staffid))); ?>
                                                        </button>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </form>
                                        </details>
                                        <?php } ?>

                                        <?php if (!empty($jsst_chandoff)) { ?>
                                            <details class="jsst-collab-more">
                                                <summary><?php echo esc_html(__('If you are picking this up', 'js-support-ticket')); ?></summary>
                                                <ul class="jsst-reasons">
                                                    <?php foreach ($jsst_chandoff AS $jsst_line) { ?>
                                                        <li><?php echo esc_html($jsst_line); ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </details>
                                        <?php } ?>

                                        <?php if (!empty($jsst_cdrafts)) { ?>
                                            <div class="jsst-collab-block">
                                                <p class="jsst-collab-title"><?php echo esc_html(__('Written but not sent', 'js-support-ticket')); ?></p>
                                                <?php foreach ($jsst_cdrafts AS $jsst_d) { ?>
                                                    <div class="jsst-collab-draft">
                                                        <p class="jsst-collab-draft-head">
                                                            <?php
                                                            $jsst_statuslabels = array(
                                                                JSSTcollab::DRAFT_SHARED   => __('Shared with the desk', 'js-support-ticket'),
                                                                JSSTcollab::DRAFT_AWAITING => __('Waiting for approval', 'js-support-ticket'),
                                                                JSSTcollab::DRAFT_APPROVED => __('Approved, not yet sent', 'js-support-ticket'),
                                                                JSSTcollab::DRAFT_REJECTED => __('Sent back', 'js-support-ticket'),
                                                            );
                                                            $jsst_pillclass = ($jsst_d->status === JSSTcollab::DRAFT_APPROVED) ? 'jsst-pill-ok'
                                                                : (($jsst_d->status === JSSTcollab::DRAFT_REJECTED) ? 'jsst-pill-bad'
                                                                    : (($jsst_d->status === JSSTcollab::DRAFT_AWAITING) ? 'jsst-pill-warn' : 'jsst-pill-info'));
                                                            ?>
                                                            <span class="jsst-pill <?php echo esc_attr($jsst_pillclass); ?>"><?php
                                                                echo esc_html(isset($jsst_statuslabels[$jsst_d->status]) ? $jsst_statuslabels[$jsst_d->status] : $jsst_d->status); ?></span>
                                                            <span class="jsst-collab-draft-by"><?php
                                                                /* translators: %s: an agent's name */
                                                                echo esc_html(sprintf(__('by %s', 'js-support-ticket'), $jsst_d->authorname ? $jsst_d->authorname : ('#' . $jsst_d->authorid)));
                                                                if ((int) $jsst_d->approverid > 0) {
                                                                    /* translators: %s: an agent's name */
                                                                    echo ' ' . esc_html(sprintf(__('for %s', 'js-support-ticket'), $jsst_d->approvername ? $jsst_d->approvername : ('#' . $jsst_d->approverid)));
                                                                }
                                                            ?></span>
                                                        </p>
                                                        <div class="jsst-collab-draft-body"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($jsst_d->body), 45)); ?></div>
                                                        <?php if ($jsst_d->decidednote !== '' && $jsst_d->decidednote !== null) { ?>
                                                            <p class="jsst-field-note"><?php echo esc_html($jsst_d->decidednote); ?></p>
                                                        <?php } ?>
                                                        <form method="post" action="<?php echo esc_url($jsst_caction); ?>">
                                                            <input type="hidden" name="ticketid" value="<?php echo esc_attr($jsst_cticketid); ?>" />
                                                            <input type="hidden" name="collabdraft" value="<?php echo esc_attr($jsst_d->id); ?>" />
                                                            <div class="jsst-actions">
                                                                <?php if ($jsst_d->status === JSSTcollab::DRAFT_AWAITING && (int) $jsst_d->approverid === $jsst_cme) { ?>
                                                                    <input type="text" name="collabdecidenote" class="inputbox jsst-inline-input" placeholder="<?php echo esc_attr(__('A word back, optional', 'js-support-ticket')); ?>" />
                                                                    <button type="submit" name="collabdecide" value="approve" class="button button-primary"><?php echo esc_html(__('Approve', 'js-support-ticket')); ?></button>
                                                                    <button type="submit" name="collabdecide" value="reject" class="button"><?php echo esc_html(__('Send back', 'js-support-ticket')); ?></button>
                                                                <?php } ?>
                                                                <?php if ((int) $jsst_d->authorid === $jsst_cme || $jsst_cactor['kind'] === JSSTcapability::ACTOR_ADMIN) { ?>
                                                                    <button type="submit" name="collabdiscard" value="<?php echo esc_attr($jsst_d->id); ?>" class="button-link"><?php echo esc_html(__('Discard', 'js-support-ticket')); ?></button>
                                                                <?php } ?>
                                                            </div>
                                                        </form>
                                                    </div>
                                                <?php } ?>
                                                <p class="jsst-field-note"><?php echo esc_html(__('Approving does not send. Whoever wrote it still presses the button, because they are the one who knows what else was going with it.', 'js-support-ticket')); ?></p>
                                            </div>
                                        <?php } ?>

                                        <?php if (JSSTcapability::can(JSSTcapability::TICKET_REPLY, array('ticket' => $jsst_cticketid), $jsst_cactor)) { ?>
                                            <details class="jsst-collab-more">
                                                <summary><?php echo esc_html(__('Have somebody look at a reply first', 'js-support-ticket')); ?></summary>
                                                <form method="post" action="<?php echo esc_url($jsst_caction); ?>">
                                                    <input type="hidden" name="ticketid" value="<?php echo esc_attr($jsst_cticketid); ?>" />
                                                    <textarea name="collabbody" class="inputbox" rows="4" placeholder="<?php echo esc_attr(__('What you would send', 'js-support-ticket')); ?>"></textarea>
                                                    <div class="jsst-fields">
                                                        <div class="jsst-field">
                                                            <label for="collabapprover"><?php echo esc_html(__('Ask', 'js-support-ticket')); ?></label>
                                                            <?php echo wp_kses(JSSTformfield::select('collabapprover', $jsst_cagents, '', esc_html(__('Nobody — just share it', 'js-support-ticket')), array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                                                        </div>
                                                    </div>
                                                    <div class="jsst-actions">
                                                        <button type="submit" name="collabshare" value="1" class="button"><?php echo esc_html(__('Share it', 'js-support-ticket')); ?></button>
                                                        <span class="jsst-actions-note"><?php echo esc_html(__('Nothing goes to the customer.', 'js-support-ticket')); ?></span>
                                                    </div>
                                                </form>
                                            </details>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-assign">
                                <?php if($jsst_agentflag){ ?>
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <?php echo esc_html(__('Ticket Assign and Transfer','js-support-ticket')); ?>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="js-tkt-det-tkt-asgn-cnt">
                                    <?php if($jsst_agentflag){ ?>
                                    <div class="js-tkt-det-hdg">
                                        <a target="blank" href="https://www.youtube.com/watch?v=ZtCivvtAURU" class="js-tkt-det-hdg-img js-cp-video-assign">
                                            <img title="<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
                                        </a>
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php
                                            if(jssupportticket::$jsst_data[0]->staffid > 0){
                                                echo esc_html(__('Ticket assigned to','js-support-ticket'));
                                            }else{
                                                echo esc_html(__('Not assigned to agent','js-support-ticket'));
                                            }
                                            ?>
                                        </div>
                                        <?php if (JSSTroles::canChangeTicketState()) { ?>
                                        <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="asgn-staff">
                                            <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                        </a>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                    <div class="js-tkt-det-info-wrp">
                                        <?php /* The agent flag as well as the id, which is what the
                                           front-end copy of this block has always asked. Three of the
                                           four fields printed below - the agent's WordPress id, e-mail
                                           and phone - are added to the query by the Agents addon, not
                                           by core; without it a ticket that still carries an assignee
                                           printed four PHP warnings into the page. */ ?>
                                        <?php if($jsst_agentflag && jssupportticket::$jsst_data[0]->staffid > 0){ ?>
                                        <div class="js-tkt-det-user">
                                            <div class="js-tkt-det-user-image">
                                                <?php echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->staffuid), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                            <div class="js-tkt-det-user-cnt">
                                                <div class="js-tkt-det-user-data"><?php echo esc_html(jssupportticket::$jsst_data[0]->staffname); ?></div>
                                                <div class="js-tkt-det-user-data"><?php echo esc_html(jssupportticket::$jsst_data[0]->staffemail); ?></div>
                                                <div class="js-tkt-det-user-data"><?php echo esc_html(jssupportticket::$jsst_data[0]->staffphone); ?></div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if($jsst_departmentflag){ ?>
                                        <div class="js-tkt-det-trsfer-dep">
                                            <a target="blank" href="https://www.youtube.com/watch?v=hewCQ0S37V8" class="js-tkt-det-hdg-img js-cp-video-department">
                                                <img title="<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('Watch Video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
                                            </a>
                                            <div class="js-tkt-det-trsfer-dep-txt">
                                                <!-- Display heading based on field order  -->
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?>: <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->departmentname)); ?>
                                            </div>
                                            <?php if (JSSTroles::canChangeTicketState()) { ?>
                                            <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="chng-dept">
                                                <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                            </a>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <!-- ticket detail time tracking -->
                        <?php if(in_array('timetracking', jssupportticket::$_active_addons)){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-time-tracker">
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
                        <!-- ticket detail company -->
                        <?php if (!empty($jsst_tktcompany)) {
                            $jsst_cosummary = JSSTcompanies::summary($jsst_tktcompany->id);
                            $jsst_corole = JSSTcompanies::roleOf(jssupportticket::$jsst_data[0]->email);
                            $jsst_cocontract = JSSTcompanies::contractState($jsst_tktcompany);
                            $jsst_cocontractlabels = array(
                                'none'    => __('No dates set', 'js-support-ticket'),
                                'active'  => __('In force', 'js-support-ticket'),
                                'pending' => __('Not started yet', 'js-support-ticket'),
                                'expired' => __('Expired', 'js-support-ticket'),
                            );
                            $jsst_coticketsurl = wp_nonce_url(admin_url('admin.php?page=ticket&jstlay=tickets&JSST_form_search=JSST_SEARCH&list=' . JSSTqueue::LIST_ALL . '&companyid=' . (int) $jsst_tktcompany->id), 'my-ticket'); ?>
                        <div class="js-tkt-det-cnt js-tkt-det-company">
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt"><?php echo esc_html(__('Company', 'js-support-ticket')); ?></div>
                            </div>
                            <div class="js-tkt-wc-order-box">
                                    <div class="js-tkt-wc-order-item">
                                        <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('Name', 'js-support-ticket')); ?>:</div>
                                        <div class="js-tkt-wc-order-item-value"><a href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=addcompany&companyid=' . (int) $jsst_tktcompany->id)); ?>"><?php echo esc_html($jsst_tktcompany->name); ?></a><?php if ((int) $jsst_tktcompany->status !== JSSTcompanies::STATUS_ACTIVE) { ?> (<?php echo esc_html(__('archived', 'js-support-ticket')); ?>)<?php } ?></div>
                                    </div>
                                    <div class="js-tkt-wc-order-item">
                                        <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('This person', 'js-support-ticket')); ?>:</div>
                                        <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_corole === JSSTcompanies::ROLE_SUPERVISOR
                                            ? __('Supervisor', 'js-support-ticket')
                                            : ($jsst_corole === JSSTcompanies::ROLE_CONTACT ? __('Contact', 'js-support-ticket') : __('Member by e-mail domain', 'js-support-ticket'))); ?></div>
                                    </div>
                                    <?php if ((string) $jsst_tktcompany->tier !== '') { ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('Customer tier', 'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_tktcompany->tier); ?></div>
                                        </div>
                                    <?php } ?>
                                    <div class="js-tkt-wc-order-item">
                                        <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('Contract', 'js-support-ticket')); ?>:</div>
                                        <div class="js-tkt-wc-order-item-value"><?php
                                            echo esc_html($jsst_cocontractlabels[$jsst_cocontract]);
                                            if ((string) $jsst_tktcompany->contractref !== '') {
                                                echo ' · ' . esc_html($jsst_tktcompany->contractref);
                                            }
                                            if (!empty($jsst_tktcompany->contractend)) {
                                                echo ' · ' . esc_html(sprintf(
                                                    /* translators: %s: contract end date */
                                                    __('ends %s', 'js-support-ticket'),
                                                    date_i18n(jssupportticket::$_config['date_format'], strtotime($jsst_tktcompany->contractend))));
                                            } ?></div>
                                    </div>
                                    <?php if ($jsst_cosummary) { ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('Tickets', 'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(sprintf(
                                                /* translators: 1: total tickets, 2: open tickets, 3: people writing in */
                                                _n('%1$d in total, %2$d open, from %3$d person', '%1$d in total, %2$d open, from %3$d people', (int) $jsst_cosummary['people'], 'js-support-ticket'),
                                                (int) $jsst_cosummary['tickets'], (int) $jsst_cosummary['openticket'], (int) $jsst_cosummary['people'])); ?></div>
                                        </div>
                                    <?php } ?>
                                    <?php if ((string) $jsst_tktcompany->notes !== '') { ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__('Notes', 'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_tktcompany->notes); ?></div>
                                        </div>
                                    <?php } ?>
                                    <div class="js-tkt-wc-order-item">
                                        <a href="<?php echo esc_url($jsst_coticketsurl); ?>"><?php echo esc_html(__('View all tickets from this company', 'js-support-ticket')); ?></a>
                                    </div>
                            </div>
                        </div>
                        <?php } ?>
                        <!-- ticket detail user tickets -->
                        <?php if(isset(jssupportticket::$jsst_data['usertickets']) && !empty(jssupportticket::$jsst_data['usertickets'])){ ?>
                        <div class="js-tkt-det-cnt js-tkt-det-user-tkts" id="usr-tkt">
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
                                <?php foreach (jssupportticket::$jsst_data['usertickets'] AS $jsst_usertickets) { ?>
                                        <div class="js-tkt-det-user">
                                            <div class="js-tkt-det-user-image">
                                                <?php echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                            <div class="js-tkt-det-user-cnt">
                                                <div class="js-tkt-det-user-data name">
                                                    <span id="usr-tkts">
                                                        <a title="<?php echo esc_attr(__('View Ticket','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid='.esc_attr($jsst_usertickets->id))); ?>">
                                                            <span class="js-tkt-det-user-val"><?php echo esc_html($jsst_usertickets->subject); ?></span>
                                                        </a>
                                                    </span>
                                                </div>
                                                <div class="js-tkt-det-user-data">
                                                    <span class="js-tkt-det-user-tit">
                                                        <!-- Display heading based on field order  -->
                                                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])). ' : '; ?>
                                                    </span>
                                                    <span class="js-tkt-det-user-val"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_usertickets->departmentname)); ?></span>
                                                </div>
                                                <div class="js-tkt-det-user-data">
                                                    <span class="js-tkt-det-prty" style="background: <?php echo esc_attr($jsst_usertickets->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_usertickets->priority)); ?></span>
                                                    <span class="js-tkt-det-status">
                                                        <?php
                                                            if ($jsst_usertickets->status == 5 || 
                                                                $jsst_usertickets->status == 3 || 
                                                                $jsst_usertickets->status == 6) {
                                                                $jsst_ticketmessage = $jsst_usertickets->statustitle;
                                                            } else {
                                                                $jsst_ticketmessage = __('Open', 'js-support-ticket');
                                                            }
                                                            echo esc_html($jsst_ticketmessage);
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php apply_filters( 'js_support_ticket_frontend_details_right_middle', jssupportticket::$jsst_data[0]->id); ?>
                        <!-- ticket detail woocomerece -->
                        <?php
                            if( class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){
                                $jsst_order = wc_get_order(jssupportticket::$jsst_data[0]->wcorderid);
                                $jsst_order_productid = jssupportticket::$jsst_data[0]->wcproductid;
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
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['wcorderid']). ' : '; ?></div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <a title="<?php echo esc_attr(__('Order','js-support-ticket')). ' : '; ?>" href="<?php echo esc_url($jsst_order->get_edit_order_url()); ?>">
                                                    #<?php echo esc_html($jsst_order->get_id()); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Status",'js-support-ticket')). ' : '; ?></div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html(wc_get_order_status_name($jsst_order->get_status())); ?></div>
                                        </div>
                                        <?php
                                        if($jsst_order_productid){
												//$jsst_item = new WC_Order_Item_Product($jsst_order_productid); this line generate error if product changed
											$jsst_items = $jsst_order->get_items();
											foreach ( $jsst_items as $jsst_item ) { // get the user select product
												if($jsst_item->get_product_id() == $jsst_order_productid){
													$jsst_product_name = $jsst_item->get_name();
												}
											}
											if($jsst_product_name == ""){ // product not matched, product changed in order
												if(count($jsst_items) == 1){ // order have one product
													foreach ( $jsst_items as $jsst_item ) {
														$jsst_product_name = $jsst_item->get_name();
													}
												}												
											}
											if($jsst_product_name != ""){
													?>
													<div class="js-tkt-wc-order-item">
														<div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['wcproductid']). ' : '; ?></div>
														<div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_product_name); ?></div>
													</div>
													<?php
													
                                            }
                                        }?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Created",'js-support-ticket')). ' : '; ?></div>
                                            <div class="js-tkt-wc-order-item-value"><?php echo esc_html($jsst_order->get_date_created()->date_i18n(wc_date_format())); ?></div>
                                        </div>
                                        <?php do_action('jsst_woocommerce_order_detail_admin', $jsst_order, $jsst_order_productid); ?>
                                    </div>
                                </div>
								<?php
								}else{
									/* No order on the ticket: a card only if something hooked
									   in has anything to say, not an empty box. */
									ob_start();
									do_action('jsst_woocommerce_order_detail_admin', $jsst_order, $jsst_order_productid,jssupportticket::$jsst_data[0]->uid);
									$jsst_wc_hooked = trim(ob_get_clean());
									if ($jsst_wc_hooked !== '') { ?>
									<div class="js-tkt-det-cnt js-tkt-det-woocom">
										<div class="js-tkt-wc-order-box">
										<?php echo $jsst_wc_hooked; // phpcs:ignore WordPress.Security.EscapeOutput -- markup from the hook's own listeners ?>
										</div>
									</div>
								<?php
									}
								}
                            }
                        ?>
                        <!-- ticket detail easy digital downloads -->
                        <?php
                            if( class_exists('Easy_Digital_Downloads') && in_array('easydigitaldownloads', jssupportticket::$_active_addons)){
                                $jsst_orderid = jssupportticket::$jsst_data[0]->eddorderid;
                                $jsst_order_product = jssupportticket::$jsst_data[0]->eddproductid;
                                $jsst_order_license = jssupportticket::$jsst_data[0]->eddlicensekey;
                                if($jsst_orderid != ''){ ?>
                                    <div class="js-tkt-det-cnt js-tkt-det-edd">
                                        <div class="js-tkt-det-hdg">
                                            <div class="js-tkt-det-hdg-txt">
                                                <?php echo esc_html(__("Easy Digital Downloads",'js-support-ticket')); ?>
                                            </div>
                                        </div>
                                        <div class="js-tkt-wc-order-box">
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['eddorderid']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">#<?php echo esc_html($jsst_orderid); ?></div>
                                            </div>

                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html($jsst_field_array['eddproductid']); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value"><?php
                                                    if(is_numeric($jsst_order_product)){
                                                        $jsst_download = new EDD_Download($jsst_order_product);
                                                        echo wp_kses_post($jsst_download->post_title);
                                                    }else{
                                                        echo '-----------';
                                                    }?>
                                                </div>
                                            </div>
                                            <?php if(class_exists('EDD_Software_Licensing')){ ?>
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
                                                            echo wp_kses($jsst_order_license.'&nbsp;&nbsp;(<span style="color:'.esc_attr($jsst_result_color).';font-weight:bold;text-transform:uppercase;padding:0 3px;">'.esc_html($jsst_result).'</span>)', JSST_ALLOWED_TAGS);
                                                        }
                                                         ?>
                                                    </div>
                                                </div>
                                            <?php }?>
                                        </div>
                                    </div><?php
                                }
                            }
                        ?>
                        <!-- ticket detail envato validation -->
                        <?php
                        
                        if(in_array('envatovalidation', jssupportticket::$_active_addons) && !empty(jssupportticket::$jsst_data[0]->envatodata)){
                            $jsst_envlicense = jssupportticket::$jsst_data[0]->envatodata;
                            if (is_string($jsst_envlicense)) {
                                $jsst_envdecoded = json_decode($jsst_envlicense, true);
                                $jsst_envlicense = is_array($jsst_envdecoded) ? $jsst_envdecoded : array();
                            }
                            // Only when the licence says something: a bare "true" or an empty
                            // lookup drew the heading over nothing.
                            if(is_array($jsst_envlicense) && count(array_filter($jsst_envlicense)) > 0){ ?>
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
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html($jsst_envlicense['itemname']).' (#'.esc_html($jsst_envlicense['itemid']).')'; ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['buyer'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Buyer",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html($jsst_envlicense['buyer']); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['licensetype'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License Type",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html($jsst_envlicense['licensetype']); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['license'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("License",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html($jsst_envlicense['license']); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['purchasedate'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Purchase Date",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html(date_i18n("F d, Y, H:i:s", jssupportticketphplib::JSST_strtotime($jsst_envlicense['purchasedate']))); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <?php if(!empty($jsst_envlicense['supporteduntil'])){ ?>
                                        <div class="js-tkt-wc-order-item">
                                            <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Supported Until",'js-support-ticket')); ?>:</div>
                                            <div class="js-tkt-wc-order-item-value">
                                                <?php echo esc_html(date_i18n("F d, Y", jssupportticketphplib::JSST_strtotime($jsst_envlicense['supporteduntil']))); ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div><?php
                            }
                        }
                        ?>
                        <!-- ticket detail paid support -->
                        <?php
                        if(in_array('paidsupport', jssupportticket::$_active_addons) && class_exists('WooCommerce')){
                            $jsst_linktickettoorder = true;
                            if(jssupportticket::$jsst_data[0]->paidsupportitemid > 0){
                                $jsst_paidsupport = JSSTincluder::getJSModel('paidsupport')->getPaidSupportDetails(jssupportticket::$jsst_data[0]->paidsupportitemid);
                                if($jsst_paidsupport){
                                    $jsst_linktickettoorder = false;
                                    $jsst_nonpreminumsupport = in_array(jssupportticket::$jsst_data[0]->id,$jsst_paidsupport['ignoreticketids']) ? 1 : 0;
                                    ?>
                                    <div class="js-tkt-det-cnt js-tkt-det-pdsprt">
                                        <div class="js-tkt-det-hdg">
                                            <div class="js-tkt-det-hdg-txt">
                                                <?php echo esc_html(__("Paid Support Details",'js-support-ticket')); ?>
                                            </div>
                                        </div>
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
                                                    if ($jsst_paidsupport['expiry']) {
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
                                    </div>
                                    <?php
                                }
                            }
                            if($jsst_linktickettoorder){
                                $jsst_paidsupportitems = JSSTincluder::getJSModel('paidsupport')->getPaidSupportList(jssupportticket::$jsst_data[0]->uid);
                                $jsst_paidsupportlist = array();
                                foreach($jsst_paidsupportitems as $jsst_row){
                                    $jsst_paidsupportlist[] = (object) array(
                                        'id' => $jsst_row->itemid,
                                        'text' => __("Order",'js-support-ticket').' #'.$jsst_row->orderid.', '.$jsst_row->itemname.', '. esc_html(__("Remaining",'js-support-ticket')).':'.$jsst_row->remaining.' '. esc_html(__("Out of",'js-support-ticket')).':'.$jsst_row->total,
                                    );
                                }
                                ?>
                                <div class="js-tkt-det-cnt">
                                    <div class="js-tkt-det-hdg">
                                        <div class="js-tkt-det-hdg-txt">
                                            <?php echo esc_html(__("Link ticket to paid support",'js-support-ticket')); ?>
                                        </div>
                                    </div>
                                    <div class="js-tkt-wc-order-box">
                                        <div class="js-tkt-wc-order-item">
                                            <?php echo wp_kses(JSSTformfield::select('paidsupportitemid',$jsst_paidsupportlist,null,esc_html(__("Select",'js-support-ticket'))), JSST_ALLOWED_TAGS); ?>
                                            <button type="button" class="button" id="paidsupportlinkticketbtn"><?php echo esc_html(__("Link",'js-support-ticket')); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <?php
                        /* The end of the right-hand column, and the place an
                           integration puts what it knows about this customer.
                           It fires for every ticket rather than only for one
                           carrying an order id, because the useful half of
                           commerce context - what they have bought before,
                           whether they are subscribed, what they are owed - is
                           about the person and not about the order.

                           This action has been in the WooCommerce add-on since
                           it shipped and was never fired by anything, so the
                           panel it was meant to draw has never appeared on a
                           single site. (Roadmap 5.5-COM-01) */
                        JSSTincluder::prunedAction('jsst_after_ticket_details_admin', jssupportticket::$jsst_data[0]);
                        ?>
                        <?php apply_filters('js_support_ticket_frontend_details_right_last', jssupportticket::$jsst_data[0]->id); ?>
                    </div>
                </div>

                <?php
            } else {
                JSSTlayout::getNoRecordFound();
            }
            ?>
        </div>
    </div>
</div>
