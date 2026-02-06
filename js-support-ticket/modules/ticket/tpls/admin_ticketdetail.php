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
if (in_array('aipoweredreply', jssupportticket::$_active_addons)){
    $jsst_jstmod = 'aipoweredreply';
    $jsst_jstreplymod = 'aipoweredreply';
} else {
    $jsst_jstmod = 'ticket';
    $jsst_jstreplymod = 'reply';
}
$jsst_jssupportticket_js ="
    var timer_flag = 0;
            var seconds = 0;
    function checktinymcebyid(id) {
        var content = tinymce.get(id).getContent({format: 'text'});
        if (jQuery.trim(content) == '')
        {
            alert('". esc_html(__('Some values are not acceptable please retry', 'js-support-ticket'))  ."');
            return false;
        }
        return true;
    }
	function getpremade(val) {
        jQuery.post(ajaxurl, {action: 'jsticket_ajax', val: val, jstmod: 'cannedresponses', task: 'getpremadeajax', '_wpnonce':'". esc_attr(wp_create_nonce('get-premade-ajax')) ."'}, function (data) {
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
            if(timer_flag != 0){
                jQuery('input#timer_time_in_seconds').val(jQuery('div.timer').data('seconds'));
            }
        });
        jQuery('#tabs').tabs();
        jQuery('#tk_attachment_add').click(function () {
            var obj = this;
            var current_files = jQuery('input[type=\'file\']').length;
            var total_allow =". esc_attr(jssupportticket::$_config['no_of_attachement']) .";
            var append_text = '<span class=\'tk_attachment_value_text\'><input name=\'filename[]\' type=\'file\' onchange=\'uploadfile(this,\'". esc_js(jssupportticket::$_config['file_maximum_size']) ."\',\'". esc_js(jssupportticket::$_config['file_extension']) ."\');\' size=\'20\' maxlenght=\'30\'  /><span  class=\'tk_attachment_remove\'></span></span>';
            if (current_files < total_allow) {
                jQuery('.tk_attachment_value_wrapperform').append(append_text);
            } else if ((current_files === total_allow) || (current_files > total_allow)) {
                alert('". esc_html(__('File upload limit exceeds', 'js-support-ticket')) ."');
                obj.hide();
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

        var height = jQuery(window).height();
        jQuery('a#showhistory').click(function (e) {
            e.preventDefault();
            jQuery('div#userpopup').slideDown('slow');
            jQuery('div#userpopupblack').show();
        });
        jQuery('a#int-note').click(function (e) {
            e.preventDefault();
            jQuery('div#internalnotes-popup').slideDown('slow');
            jQuery('div#internalnotespopupblack').show();
        });
        jQuery('.internalnotespopup-close, div#internalnotespopupblack').click(function (e) {
            jQuery('div#internalnotes-popup').slideUp('slow', function () {
                jQuery('div#internalnotespopupblack').hide();
            });

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
            jQuery('div#userpopup').slideUp('slow');
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
            messageModal.addClass("js-ticket-hidden");
            jsReplyHideLoading();
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
            if (typeof tinyMCE !== "undefined" && tinyMCE.get("jsticket_message") && !$("#wp-jsticket_message-wrap").hasClass("html-active")) {
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
            jQuery.post(ajaxurl, {action: "jsticket_ajax", ticketSubject: ticketSubject, ticketId: ticketId, filter: selectedFilter, jstmod: "'.$jsst_jstmod.'", task: "checkAIReplyTicketsBySubject", "_wpnonce":"'. esc_attr(wp_create_nonce("check-smart-reply")).'"}, function (data) {
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

            $.each(matchingTickets, (index, ticket) => {
                const listItem = $("<li></li>")
                    .addClass("js-ticket-list-item")
                    .data("ticket-id", ticket.id) // Store ticket ID in data attribute
                    .html(`<p class="js-ticket-id">'.__("Ticket ID:", "js-support-ticket").' ${(ticket.ticketid)}</p><p class="js-ticket-title">${(ticket.text)}</p><p class="js-ticket-id">${(ticket.message)}</p>`);
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
                                <span class="js-ticket-reply-id">'.__("Reply By:", "js-support-ticket").' ${escapeHtml(replyName)}</span>
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
            alert("Primary id must be differ from merge ticket id");
            return false;
        }
        jQuery("#mergeticketselection").hide();
        getTicketdataForMerging(mergeticketid,mergewithticketid,mergeNonce);
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
        jQuery.post(ajaxurl, {action: "jsticket_ajax",jstmod: "mergeticket", task: "getTicketsForMerging", ticketid:ticketid,ticketlimit:pagenum, "_wpnonce": nonce}, function (data) {
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

    function changeTimerStatus(val) {
        if(timer_flag == 2){// to handle stopped timer
                return;
        }
        if(!jQuery("span.timer-button.cls_"+val).hasClass("selected")){
            jQuery("span.timer-button").removeClass("selected");
            jQuery("span.timer-button.cls_"+val).addClass("selected");
            if(val == 1){
                if(timer_flag == 0){
                    jQuery("div.timer").timer({format: "%H:%M:%S"});
                }
                timer_flag = 1;
                jQuery("div.timer").timer("resume");
            }else if(val == 2) {
                 jQuery("div.timer").timer("pause");
            }else{
                 jQuery("div.timer").timer("remove");
                timer_flag = 2;
            }
        }
    }

    function showEditTimerPopup(){
        jQuery("form#jsst-time-edit-form").hide();
        jQuery("form#jsst-reply-form").hide();
        jQuery("form#jsst-note-edit-form").hide();
        jQuery("div.edit-time-popup").show();
        jQuery("span.timer-button").removeClass("selected");
        if(timer_flag != 0){
            jQuery("div.timer").timer("pause");
        }
        ex_val = jQuery("div.timer").html();
        jQuery("input#edited_time").val("");
        jQuery("input#edited_time").val(ex_val.trim());
        jQuery("div.jsst-popup-background").show();
        jQuery("div.jsst-merge-popup-wrapper").slideDown("slow");
        jQuery("div.jsst-merge-popup-wrapper div.userpopup-heading").html("'. esc_html(__("Edit Time","js-support-ticket")) .'");
    }

    function updateTimerFromPopup(){
        val = jQuery("input#edited_time").val();
        arr = val.split(":", 3);
        jQuery("div.timer").html(val);
        jQuery("div.jsst-popup-background").hide();
        jQuery("div.jsst-popup-wrapper").slideUp("slow");
        seconds = parseInt(arr[0])*3600 + parseInt(arr[1])*60 + parseInt(arr[2]);
        if(seconds < 0){
            seconds = 0;
        }
        jQuery("div.timer").timer("remove");
        jQuery("div.timer").timer({
            format: "%H:%M:%S",
            seconds: seconds,
        });
        jQuery("div.timer").timer("pause");
        timer_flag = 1;
        desc = jQuery("textarea#t_desc").val();
        jQuery("input#timer_edit_desc").val(desc);
    }

    jQuery("div.popup-header-close-img,div.jsst-popup-background,input#cancel").click(function (e) {
        jQuery("div.jsst-popup-wrapper:not(#internalnotes-popup)").slideUp("slow");
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
                    <?php echo esc_html(__('Reason For Editing the timer', 'js-support-ticket')); ?>
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
                <div class="js-form-title-popup"><?php echo esc_html(__('Resolve conflict', 'js-support-ticket')); ?></div>
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
        <?php if(in_array('note', jssupportticket::$_active_addons) && in_array('timetracking', jssupportticket::$_active_addons)){ ?>
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
                <div class="js-form-title-popup"><?php echo esc_html(__('Resolve conflict', 'js-support-ticket')); ?></div>
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
    <div id="jsstadmin-leftmenu">
        <?php
        if(current_user_can('jsst_support_ticket')){
            JSSTincluder::getClassesInclude('jsstadminsidemenu');
        }
        ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Ticket Detail','js-support-ticket')); ?></li>
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
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                $jsst_cur_uid = JSSTincluder::getObjectClass('user')->uid();
                ?>

                <div id="userpopupblack" style="display:none;"> </div>
                <div id="internalnotespopupblack" style="display:none;"> </div>
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
                <div id="userpopupblack" style="display:none;"></div>
                <div id="userpopup" class="srch-hist-popup" style="display:none;">
                    <div class="userpopup-top">
                        <div class="userpopup-heading">
                            <?php echo esc_html(__('Ticket History', 'js-support-ticket')); ?>
                        </div>
                        <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="close-history userpopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                    </div>
                    <div id="userpopup-records-wrp">
                        <div id="userpopup-records">
                            <div class="userpopup-search-history">
                                <?php // data[5] holds the tickect history
                                    $jsst_field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, jssupportticket::$jsst_data[0]->multiformid);
                                if ((!empty(jssupportticket::$jsst_data[5]))) {
                                    ?>
                                    <?php foreach (jssupportticket::$jsst_data[5] AS $jsst_history) { ?>
                                        <div class="userpopup-search-history-row">
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
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- inrternal notes popup -->
                <div id="internalnotes-popup" class="jsst-popup-wrapper" style="display: none;">
                    <?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
                        <div class="userpopup-top">
                            <div class="userpopup-heading">
                                <?php echo esc_html(__('Post New Internal Note','js-support-ticket')); ?>
                            </div>
                            <img alt = "<?php echo esc_attr(__('Close','js-support-ticket')); ?>" class="internalnotespopup-close" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/close-icon-white.png" />
                        </div>
                        <div class="js-admin-popup-cnt">  <!--  postinternalnote Area   -->
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
                                                    <span class="timer-button" onclick="showEditTimerPopup()" >
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time-1.png"/>
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png"/>
                                                    </span>
                                                <?php } ?>
                                                <span class="timer-button cls_1" onclick="changeTimerStatus(1)" >
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time.png"/>
                                                </span>
                                                <span class="timer-button cls_2" onclick="changeTimerStatus(2)" >
                                                    <img alt = "<?php echo esc_attr(__('pause','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('pause','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time.png"/>
                                                </span>
                                                <span class="timer-button cls_3" onclick="changeTimerStatus(3)" >
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
                                    <div class="js-form-value"><?php wp_editor('', 'internalnote', array('media_buttons' => false)); ?></div>
                                </div>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Ticket', 'js-support-ticket')); echo ' '; echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                    <div class="js-form-value">
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close on reply', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
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
                    <?php if ( in_array('actions',jssupportticket::$_active_addons)) { ?>
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
                                <?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Reason For', 'js-support-ticket')) ." ".esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ".esc_html(__('Transfer', 'js-support-ticket')); ?></label></div>
                                        <div class="js-form-value"><?php wp_editor('', 'departmenttranfernote', array('media_buttons' => false)); ?></div>
                                    </div>
                                <?php } ?>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('departmenttransfer', esc_html(__('Transfer','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block', 'onclick' => "return checktinymcebyid('departmenttranfernote');")), JSST_ALLOWED_TAGS); ?>
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
                                <?php if(in_array('note', jssupportticket::$_active_addons)){ ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?></label></div>
                                        <div class="js-form-value"><?php wp_editor('', 'assignnote', array('media_buttons' => false)); ?></div>
                                    </div>
                                <?php } ?>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('assigntostaff', esc_html(__('Assign','js-support-ticket')), array('class' => 'button js-admin-pop-btn-block', 'onclick' => "return checktinymcebyid('assignnote');")), JSST_ALLOWED_TAGS); ?>
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
                            <div class="js-tkt-det-actn-btn-wrp">
                                <a title="<?php echo esc_attr(__('Edit Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="?page=ticket&jstlay=addticket&jssupportticketid=<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                    <img alt = "<?php echo esc_attr(__('Edit Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit.png" />
                                    <span><?php echo esc_html(__('Edit Ticket','js-support-ticket')); ?></span>
                                </a>
                                <?php if(in_array('tickethistory', jssupportticket::$_active_addons)){ ?>
                                    <a title="<?php echo esc_attr(__('Show History','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="showhistory">
                                        <img alt = "<?php echo esc_attr(__('Show History','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/history.png" />
                                        <span><?php echo esc_html(__('Show History','js-support-ticket')); ?></span>
                                    </a>
                                <?php } ?>
                                <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=ticket&task=actionticket"),"action-ticket-".jssupportticket::$jsst_data[0]->id)); ?>" id="adminTicketform" enctype="multipart/form-data">
                                    <?php
                                        if (jssupportticket::$jsst_data[0]->status != 6) { // merged closed ticket can not be reopend.
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
                                    <?php if (  in_array('actions',jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 ) { ?>
                                        <a title="<?php echo esc_attr(__('Print Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="print-link" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>">
                                            <img alt = "<?php echo esc_attr(__('Print Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/print.png" />
                                            <span><?php echo esc_html(__('Print Ticket','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php if (  in_array('mergeticket',jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->status != 5 && jssupportticket::$jsst_data[0]->status != 6 ) {
                                        $jsst_nonce = wp_create_nonce("get-tickets-for-merging-".jssupportticket::$jsst_data[0]->id) ?>
                                        <a title="<?php echo esc_attr(__('Merge Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" id="mergeticket" data-ticketid="<?php echo esc_attr(jssupportticket::$jsst_data[0]->id); ?>" onclick="return showPopupAndFillValues(<?php echo esc_js(jssupportticket::$jsst_data[0]->id) ?>,4, '<?php echo esc_js($jsst_nonce);?>')" >
                                            <img alt = "<?php echo esc_attr(__('Merge Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/merge-ticket.png" />
                                            <span><?php echo esc_html(__('Merge Ticket','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php if (in_array('privatecredentials',jssupportticket::$_active_addons)) { ?>
                                        <?php $jsst_nonce = wp_create_nonce('get-private-credentials-'.jssupportticket::$jsst_data[0]->id) ?>
                                    <a title="<?php echo esc_attr(__('Private Credentials','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="javascript:return false;" id="privatecredentials" onclick="getCredentails(<?php echo esc_js(jssupportticket::$jsst_data[0]->id); ?>, '<?php echo esc_js($jsst_nonce); ?>')" >
                                        <?php $jsst_query = "SELECT count(id) FROM `" . jssupportticket::$_db->prefix . "js_ticket_privatecredentials` WHERE status = 1 AND ticketid = ".esc_sql(jssupportticket::$jsst_data[0]->id);
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
                                        if(in_array('actions', jssupportticket::$_active_addons)){
                                            if (jssupportticket::$jsst_data[0]->lock == 1) { ?>
                                                <a title="<?php echo esc_attr(__('Unlock Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(5);">
                                                    <img alt = "<?php echo esc_attr(__('Unlock Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/unlock.png" />
                                                    <span><?php echo esc_html(__('Unlock Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Lock Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(4);">
                                                    <img alt = "<?php echo esc_attr(__('Lock Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/lock.png" />
                                                    <span><?php echo esc_html(__('Lock Ticket','js-support-ticket')); ?></span>
                                                </a>
                                            <?php }
                                        }
                                        if(in_array('banemail', jssupportticket::$_active_addons)){
                                            if (JSSTincluder::getJSModel('banemail')->isEmailBan(jssupportticket::$jsst_data[0]->email)) { ?>
                                                <a title="<?php echo esc_attr(__('Unban Email','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(7);">
                                                    <img alt = "<?php echo esc_attr(__('Unban Email','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-ban.png" />
                                                    <span><?php echo esc_html(__('Unban Email','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Ban Email','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(6);">
                                                    <img alt = "<?php echo esc_attr(__('Ban Email','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban.png" />
                                                    <span><?php echo esc_html(__('Ban Email','js-support-ticket')); ?></span>
                                                </a>
                                            <?php
                                            }
                                        }
                                        if(in_array('overdue', jssupportticket::$_active_addons)){
                                            if (jssupportticket::$jsst_data[0]->isoverdue == 1) { ?>
                                                <a title="<?php echo esc_attr(__('Unmark Overdue','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(11);">
                                                    <img alt = "<?php echo esc_attr(__('Unmark Overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/un-over-due.png" />
                                                    <span><?php echo esc_html(__('Unmark Overdue','js-support-ticket')); ?></span>
                                                </a>
                                            <?php } else { ?>
                                                <a title="<?php echo esc_attr(__('Mark overdue','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(8);">
                                                    <img alt = "<?php echo esc_attr(__('Mark overdue','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/over-due.png" />
                                                    <span><?php echo esc_html(__('Mark Overdue','js-support-ticket')); ?></span>
                                                </a>
                                            <?php }
                                        }
                                    ?>
                                    <?php if(in_array('actions', jssupportticket::$_active_addons)){ ?>
                                        <a title="<?php echo esc_attr(__('Mark in Progress','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(9);">
                                            <img alt = "<?php echo esc_attr(__('Mark in Progress','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/in-progress.png" />
                                            <span><?php echo esc_html(__('Mark in Progress','js-support-ticket')); ?></span>
                                        </a>
                                    <?php } ?>
                                    <?php
                                        if(in_array('banemail', jssupportticket::$_active_addons)){ ?>
                                            <a title="<?php echo esc_attr(__('Ban Email and Close Ticket','js-support-ticket')); ?>" class="js-tkt-det-actn-btn" href="#" onclick="actionticket(10);">
                                                <img alt = "<?php echo esc_attr(__('Ban Email and Close Ticket','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ban-email-close-ticket.png" />
                                                <span><?php echo esc_html(__('Ban Email and Close Ticket','js-support-ticket')); ?></span>
                                            </a>
                                    <?php } ?>
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
                        <!-- Tickect internal Note Area -->
                        <?php
                            $jsst_color1ed = "colored";
                            if(in_array('note', jssupportticket::$_active_addons)){ ?>
                                <div class="js-tkt-det-title"><?php echo esc_html(__('Internal Note', 'js-support-ticket')); ?></div>
                                <?php if (!empty(jssupportticket::$jsst_data[6])) {
                                    foreach (jssupportticket::$jsst_data[6] AS $jsst_note) {
                                        if ($jsst_cur_uid == isset($jsst_note->uid))
                                            $jsst_color1ed = '';?>
                                        <div class="js-ticket-thread">
                                            <div class="js-ticket-thread-image">
                                                <?php /* if (in_array('agent',jssupportticket::$_active_addons) && $jsst_note->staffphoto) { ?>
                                                    <img alt = "<?php echo esc_attr(__('agent image','js-support-ticket')); ?>" src="<?php echo esc_url(jssupportticket::makeUrl(array('jstmod'=>'agent','task'=>'getStaffPhoto','action'=>'jstask','jssupportticketid'=>$jsst_note->staff_id, 'jsstpageid'=>jssupportticket::getPageid()))); ?>">
                                                <?php } else { */
                                                    echo wp_kses(jsst_get_avatar($jsst_note->userid), JSST_ALLOWED_TAGS);
                                                // } ?>
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
                                                    if($jsst_note->filesize > 0 && !empty($jsst_note->filename)){
                                                        echo wp_kses('<div class="js_ticketattachment">
                                                                <span class="js_ticketattachment_fname">'
                                                                    . esc_html($jsst_note->filename) . /*' (' . ($jsst_note->filesize / 1024 ) . ')&nbsp;&nbsp*/'
                                                                </span>
                                                                <a title="'. esc_html(__('Download','js-support-ticket')).'" class="button" target="_blank" href="'.admin_url('?page=note&action=jstask&task=downloadbyid&id='.esc_attr($jsst_note->id)).'">'. esc_html(__('Download','js-support-ticket')).'</a>
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
                                <div class="js-ticket-thread-add-btn">
                                    <a title="<?php echo esc_attr(__('Post New Internal Note','js-support-ticket')); ?>" href="#" class="js-ticket-thread-add-btn-link" id="int-note">
                                        <img alt = "<?php echo esc_attr(__('Post New Internal Note','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png" />
                                        <span><?php echo esc_html(__('Post New Internal Note','js-support-ticket')); ?></span>
                                    </a>
                                </div>
                            <?php } ?>
                        <!-- Tickect  Reply  Area -->
                        <div class="js-tkt-det-title"><?php echo esc_html(__('Ticket Thread', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-thread">
                            <div class="js-ticket-thread-image">
                                <?php /* if ( in_array('agent',jssupportticket::$_active_addons) &&  jssupportticket::$jsst_data[0]->staffphotophoto) { ?>
                                    <img alt = "<?php echo esc_attr(__('agent image','js-support-ticket')); ?>" src="<?php echo esc_url(admin_url('?page=agent&action=jstask&task=getStaffPhoto&jssupportticketid='.jssupportticket::$jsst_data[0]->staffphotoid )); ?>">
                                <?php } else { */
                                    echo wp_kses(jsst_get_avatar(jssupportticket::$jsst_data[0]->uid), JSST_ALLOWED_TAGS);
                                // } ?>
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
                                    if (!empty(jssupportticket::$jsst_data['ticket_attachment'])) {
                                        $jsst_datadirectory = jssupportticket::$_config['data_directory'];
                                        $jsst_maindir = wp_upload_dir();
                                        $jsst_path = $jsst_maindir['baseurl'];

                                        $jsst_path = $jsst_path .'/' . $jsst_datadirectory;
                                        $jsst_path = $jsst_path . '/attachmentdata';
                                        $jsst_path = $jsst_path . '/ticket/ticket_' . jssupportticket::$jsst_data[0]->id . '/';
                                        foreach (jssupportticket::$jsst_data['ticket_attachment'] AS $jsst_attachment) {
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
                                foreach (jssupportticket::$jsst_data[4] AS $jsst_reply) {
                                if ($jsst_cur_uid == $jsst_reply->uid)
                                    $jsst_color1ed = '';
                                ?>
                                <div class="js-ticket-thread">
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
                                        </div>
                                        <div class="js-ticket-thread-data">
                                            <span class="js-ticket-via-email">
                                                <?php echo ($jsst_reply->ticketviaemail == 1) ? esc_html(__('Created via Email', 'js-support-ticket')) : ''; ?>
                                            </span>
                                        </div>
                                        <div class="js-ticket-thread-data note-msg">
                                            <?php echo wp_kses_post(html_entity_decode($jsst_reply->message)); ?>
                                        </div>
                                        <?php
                                            if (!empty($jsst_reply->attachments)) {
                                                foreach ($jsst_reply->attachments AS $jsst_attachment) {
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
                                        ?>
                                        <div class="js-ticket-thread-cnt-btm">
                                            <?php
                                            if (in_array('aipoweredreply', jssupportticket::$_active_addons) && jssupportticket::$jsst_data[0]->uid != $jsst_reply->uid && $jsst_reply->uid != 0) { ?>
                                                <!-- This section contains the AI Reply Feature -->
                                                <div class="js-ticket-ai-reply-status-wrapper">
                                                    <label for="js-ticket-ai-reply-status-control">
                                                        <?php echo esc_html__('AI-Powered Reply Mode', 'js-support-ticket').':'; ?>
                                                    </label>
                                                    <div class="js-ticket-info-icon-wrapper">
                                                        <span class="js-ticket-info-icon" data-tooltip = "<?php echo esc_attr(__("Control how this individual reply influences the AI search and response generation process for future queries.",'js-support-ticket')); ?>">
                                                            <img alt = "<?php echo esc_attr(__('Info','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/info-icon.png" />
                                                        </span>
                                                    </div>
                                                    <div id="js-ticket-ai-reply-status-control" class="js-ticket-segmented-control">
                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-default <?php echo ( intval( $jsst_reply->aireplymode ) === 0 ) ? 'active' : ''; ?>" data-value="0" data-type="reply" data-id="<?php echo esc_attr( $jsst_reply->replyid ); ?>" title="<?php echo esc_attr(__( "Default: reply included in all AI search queries.", "js-support-ticket" )); ?>">
                                                            <?php echo esc_html__('Default', 'js-support-ticket'); ?>
                                                        </button>
                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-enable <?php echo ( $jsst_reply->aireplymode == 1 ) ? 'active' : ''; ?>" data-value="1" data-type="reply" data-id="<?php echo esc_attr( $jsst_reply->replyid ); ?>" title="<?php echo esc_attr(__( "Enable: reply used in AI queries only when the Enable Tickets filter is active.", "js-support-ticket" )); ?>">
                                                            <?php echo esc_html__('Enable', 'js-support-ticket'); ?>
                                                        </button>
                                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-disable <?php echo ( $jsst_reply->aireplymode == 2 ) ? 'active' : ''; ?>" data-value="2" data-type="reply" data-id="<?php echo esc_attr( $jsst_reply->replyid ); ?>" title="<?php echo esc_attr(__( "Disable: reply excluded from AI queries.", "js-support-ticket" ) ); ?>">
                                                            <?php echo esc_html__('Disable', 'js-support-ticket'); ?>
                                                        </button>
                                                    </div>
                                                    <!-- Hidden input to hold the current selected value -->
                                                    <input type="hidden" name="js_ticket_ai_reply_status" id="js-ticket-ai-reply-status-hidden" value="<?php echo esc_attr( $jsst_reply->aireplymode ); ?>" />
                                                </div>
                                                <?php
                                            } ?>
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
                                                if($jsst_reply->staffid != 0){
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
                        <?php } ?>
                        <!-- Post Reply Area -->
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
                                                    <span class="timer-button" onclick="showEditTimerPopup()" >
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time-1.png"/>
                                                        <img alt = "<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/edit-time.png"/>
                                                    </span>
                                                <?php } ?>
                                                <span class="timer-button cls_1" onclick="changeTimerStatus(1)" >
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time-1.png"/>
                                                    <img alt = "<?php echo esc_attr(__('play','js-support-ticket')); ?>" class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/play-time.png"/>
                                                </span>
                                                <span class="timer-button cls_2" onclick="changeTimerStatus(2)" >
                                                    <img <?php echo esc_html(__('pause','js-support-ticket')); ?> class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time-1.png"/>
                                                    <img <?php echo esc_html(__('pause','js-support-ticket')); ?> class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/pause-time.png"/>
                                                </span>
                                                <span class="timer-button cls_3" onclick="changeTimerStatus(3)" >
                                                    <img <?php echo esc_html(__('stop','js-support-ticket')); ?> class="default-show" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time-1.png"/>
                                                    <img <?php echo esc_html(__('stop','js-support-ticket')); ?> class="default-hide" alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/stop-time.png"/>
                                                </span>
                                            </div>
                                        </div>
                                        <?php echo wp_kses(JSSTformfield::hidden('timer_time_in_seconds',''), JSST_ALLOWED_TAGS); ?>
                                        <?php echo wp_kses(JSSTformfield::hidden('timer_edit_desc',''), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                <?php } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><label id="responcemsg" for="responce"><?php echo esc_html(__('Response', 'js-support-ticket')); ?><span style="color: red;" >*</span></label></div>
                                    <div class="js-form-value"><?php wp_editor('', 'jsticket_message', array('media_buttons' => false)); ?></div>
                                </div>
                                <div class="js-form-wrapper">
                                    <div class="js-ticket-ai-powered-reply-wrapper">
                                        <div class="js-ticket-ai-powered-reply-icon">
                                            <img alt = "<?php echo esc_attr(__('AI Icon','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/ai-icon.png" />
                                        </div>
                                        <div class="js-ticket-ai-powered-reply-content">
                                            <div class="js-ticket-ai-powered-reply-title">
                                                <?php echo esc_html__('AI-Powered Reply', 'js-support-ticket'); ?>
                                            </div>
                                            <div class="js-ticket-ai-powered-reply-text">
                                                <?php echo esc_html__('Get context-based suggestions to effortlessly create clear and relevant replies.', 'js-support-ticket'); ?>
                                            </div>
                                        </div>
                                        <div id="js-ticket-ai-reply-btn" class="js-ticket-ai-powered-reply-action">
                                            <a href="#" class="js-ticket-ai-powered-reply-button">
                                                <?php echo esc_html__('Suggested Response', 'js-support-ticket'); ?>
                                            </a>
                                        </div>
                                    </div>
                                    <span class="js-ticket-current-ticket-title"><?php echo esc_html( jssupportticket::$jsst_data[0]->subject ); ?></span>
                                    <span class="js-ticket-current-ticket-id"><?php echo esc_html( jssupportticket::$jsst_data[0]->id ); ?></span>
                                    <div class="js-ticket-container">
                                        <!-- Matching Tickets Section -->
                                        <div id="js-ticket-matching-tickets-section" class="js-ticket-section js-ticket-matching-tickets-section js-ticket-hidden">
                                            <div class="js-ticket-selected-tickets-header">
                                                <h2 class="js-ticket-section-heading"><?php echo esc_html__('Matching Tickets', 'js-support-ticket'); ?></h2>
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
                                if(in_array('cannedresponses', jssupportticket::$_active_addons)){
                                    $jsst_cannedresponses = JSSTincluder::getJSModel('cannedresponses')->getPreMadeMessageForCombobox();
                                    ?>
                                    <div class="js-form-wrapper">
                                        <div class="js-form-value">
                                            <?php
                                            foreach($jsst_cannedresponses as $jsst_premade){
                                                ?>
                                                <div class="js-tkt-det-perm-msg" onclick="getpremade(<?php echo esc_js($jsst_premade->id); ?>);">
                                                    <a href="javascript:void(0);" title="<?php echo esc_attr(__('premade','js-support-ticket')); ?>"><?php echo esc_html($jsst_premade->text); ?></a>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <div class="js-ticket-detail-append-signature-xs">
                                                <?php echo wp_kses(JSSTformfield::checkbox('append_premade', array('1' => esc_html(__('Append', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
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
                                    <div class="js-form-value">
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('ownsignature', array('1' => esc_html(__('Own Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('departmentsignature', array('1' => esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])) ." ". esc_html(__('Signature', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('nonesignature', array('1' => esc_html(__('None', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
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
                                        <div class="js-form-title"><?php echo esc_html(__('Assign to me', 'js-support-ticket')); ?></div>
                                        <div class="jsst-formfield-radio-button-wrap">
                                            <?php echo wp_kses(JSSTformfield::checkbox('assigntome', array('1' => esc_html(__('Assign to me', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                        </div>
                                    </div>
                                    <?php }
                                } ?>
                                <div class="js-form-wrapper">
                                    <div class="js-form-title"><?php echo esc_html(__('Ticket', 'js-support-ticket')); echo ' '; echo esc_html(__('Status', 'js-support-ticket')); ?></div>
                                    <div class="jsst-formfield-radio-button-wrap">
                                        <?php echo wp_kses(JSSTformfield::checkbox('closeonreply', array('1' => esc_html(__('Close on reply', 'js-support-ticket'))), '', array('class' => 'radiobutton')), JSST_ALLOWED_TAGS); ?>
                                    </div>
                                </div>
                                <div class="js-form-button">
                                    <?php echo wp_kses(JSSTformfield::submitbutton('postreply', esc_html(__('Post Reply','js-support-ticket')), array('class' => 'button js-form-save', 'onclick' => "return checktinymcebyid('message');")), JSST_ALLOWED_TAGS); ?>
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
                    <!-- ticket detail right side -->
                    <div class="js-tkt-det-right">
                        <!-- ticket detail info -->
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-info">
                            <?php
                            if (jssupportticket::$jsst_data[0]->status == 5 || 
                                jssupportticket::$jsst_data[0]->status == 3 || 
                                jssupportticket::$jsst_data[0]->status == 6) {
                                $jsst_stylecolor = jssupportticket::$jsst_data[0]->statuscolour;
                                $jsst_stylebgcolor = jssupportticket::$jsst_data[0]->statusbgcolour;
                                $jsst_ticketmessage = esc_html(jssupportticket::$jsst_data[0]->statustitle);
                            } else {
                                $jsst_ticketmessage = esc_html(__('Open', 'js-support-ticket'));
                                $jsst_stylecolor = '#FFFFFF';
                                $jsst_stylebgcolor = '#5bb12f';
                            } ?>
                            <div class="js-tkt-det-status" style="background-color:<?php echo esc_attr($jsst_stylebgcolor)?>;color:<?php echo esc_attr($jsst_stylecolor);?>;">
                                <?php
                                    jssupportticket::$jsst_data['custom']['ticketid'] = jssupportticket::$jsst_data[0]->id;
                                    echo esc_html($jsst_ticketmessage);
                                ?>
                            </div>
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
                                <?php if(in_array('helptopic', jssupportticket::$_active_addons)){ ?>
                                    <div class="js-tkt-det-info-data">
                                        <span class="js-tkt-det-info-tit">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['helptopic'])). ' : '; ?>
                                        </span>
                                        <span class="js-tkt-det-info-val">
                                            <?php if(in_array('helptopic',jssupportticket::$_active_addons)){ ?>
                                                <?php 
                                                    if (!empty(jssupportticket::$jsst_data[0]) && isset(jssupportticket::$jsst_data[0]->helptopic)) {
                                                        echo wp_kses_post(jssupportticket::$jsst_data[0]->helptopic);
                                                    }
                                                ?>
                                            <?php } ?>
                                        </span>
                                    </div>
                                <?php } ?>
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
                                <div class="js-tkt-det-info-data">
                                    <span class="js-tkt-det-info-tit">
                                        <?php echo esc_html(__('Status', 'js-support-ticket')). ' : '; ?>
                                    </span>
                                    <span class="js-tkt-det-info-val">
                                        <?php
                                            $jsst_printstatus = 1;
                                            if (jssupportticket::$jsst_data[0]->lock == 1) {
                                                echo '<span>' . esc_html(__('Lock', 'js-support-ticket')) . '</span>';
                                                $jsst_printstatus = 0;
                                            }
                                            if (jssupportticket::$jsst_data[0]->isoverdue == 1) {
                                                echo '<span>' . esc_html(__('Overdue', 'js-support-ticket')) . '</span>';
                                                $jsst_printstatus = 0;
                                            }
                                            if ($jsst_printstatus == 1) {
                                                echo wp_kses_post($jsst_ticketmessage);
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- ticket detail status -->
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-prty">
                            <div class="js-tkt-det-hdg">
                                <div class="js-tkt-det-hdg-txt">
                                    <?php echo esc_html(__('Status','js-support-ticket')); ?>
                                </div>
                                <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="chng-status">
                                    <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                </a>
                            </div>
                            <?php
                                if (!empty(jssupportticket::$jsst_data[0]->status)) { ?>
                                    <div class="js-tkt-det-tkt-prty-txt" style="background : <?php echo esc_attr(jssupportticket::$jsst_data[0]->statusbgcolour); ?>;color : <?php echo esc_attr(jssupportticket::$jsst_data[0]->statuscolour); ?>;">
                                        <?php
                                        echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->statustitle)); ?>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                        <!-- ticket detail status -->
                        <?php if(in_array('aipoweredreply', jssupportticket::$_active_addons)){ ?>
                            <div class="js-tkt-det-cnt js-tkt-det-tkt-prty">
                                <div class="js-tkt-det-hdg">
                                    <div class="js-tkt-det-hdg-txt">
                                        <label for="js-ticket-ai-reply-status-control">
                                            <?php echo esc_html__('AI-Powered Reply Mode', 'js-support-ticket'); ?>
                                        </label>
                                        <div class="js-ticket-info-icon-wrapper">
                                            <span class="js-ticket-info-icon" data-tooltip = "<?php echo esc_attr(__("Control how this ticket and its replies influence AI search and response generation for future queries.",'js-support-ticket')); ?>">
                                                <img alt = "<?php echo esc_attr(__('Info','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/ticket-detail/info-icon.png" />
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- This section contains the AI Reply Feature -->
                                <div class="js-ticket-ai-reply-status-wrapper">
                                    <div id="js-ticket-ai-reply-status-control" class="js-ticket-segmented-control">
                                        <button type="button" class="js-ticket-segmented-control-option js-ticket-default <?php echo ( jssupportticket::$jsst_data[0]->aireplymode == 0 ) ? 'active' : ''; ?>" data-value="0" data-type="ticket" data-id="<?php echo esc_attr( jssupportticket::$jsst_data[0]->id ); ?>" title="<?php echo esc_attr(__( "Default: ticket and replies included in all AI queries.", "js-support-ticket" ) ); ?>">
                                            <?php echo esc_html__('Default', 'js-support-ticket'); ?>
                                        </button>
                                        <button data-type="ticket" type="button" class="js-ticket-segmented-control-option js-ticket-enable <?php echo ( jssupportticket::$jsst_data[0]->aireplymode == 1 ) ? 'active' : ''; ?>" data-value="1" data-type="ticket" data-id="<?php echo esc_attr( jssupportticket::$jsst_data[0]->id ); ?>" title="<?php echo esc_attr(__( "Enable: ticket and replies used only when the “Enable Tickets” filter is active.", "js-support-ticket" ) ); ?>">
                                            <?php echo esc_html__('Enable', 'js-support-ticket'); ?>
                                        </button>
                                        <button data-type="ticket" type="button" class="js-ticket-segmented-control-option js-ticket-disable <?php echo ( jssupportticket::$jsst_data[0]->aireplymode == 2 ) ? 'active' : ''; ?>" data-value="2" data-type="ticket" data-id="<?php echo esc_attr( jssupportticket::$jsst_data[0]->id ); ?>" title="<?php echo esc_attr(__( "Disable: ticket and replies excluded from AI queries.", "js-support-ticket" )); ?>">
                                            <?php echo esc_html__('Disable', 'js-support-ticket'); ?>
                                        </button>
                                    </div>
                                    <!-- Hidden input to hold the current selected value -->
                                    <input type="hidden" name="js_ticket_ai_reply_status" id="js-ticket-ai-reply-status-hidden" value="<?php echo esc_attr( jssupportticket::$jsst_data[0]->aireplymode ); ?>" />
                                </div>
                            </div>
                        <?php } ?>
                        <!-- ticket detail priority -->
                        <div class="js-tkt-det-cnt js-tkt-det-tkt-prty">
                            <div class="js-tkt-det-hdg">
                                <a target="blank" href="https://www.youtube.com/watch?v=8Fz-expKJLE" class="js-tkt-det-hdg-img js-cp-video-priority">
                                    <img title="<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
                                </a>
                                <div class="js-tkt-det-hdg-txt">
                                    <!-- Display heading based on field order  -->
                                    <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?>
                                </div>
                                <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="chng-prority">
                                    <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                </a>
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
                        $jsst_departmentflag = in_array('actions', jssupportticket::$_active_addons);
                        if($jsst_agentflag || $jsst_departmentflag){
                            ?>
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
                                            <img title="<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
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
                                        <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="asgn-staff">
                                            <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                        </a>
                                    </div>
                                    <?php } ?>
                                    <div class="js-tkt-det-info-wrp">
                                        <?php if(jssupportticket::$jsst_data[0]->staffid > 0){ ?>
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
                                                <img title="<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" alt = "<?php echo esc_attr(__('watch video','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . '/includes/images/watch-video-icon.png'; ?>" />
                                            </a>
                                            <div class="js-tkt-det-trsfer-dep-txt">
                                                <!-- Display heading based on field order  -->
                                                <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?>: <?php echo esc_html(jssupportticket::JSST_getVarValue(jssupportticket::$jsst_data[0]->departmentname)); ?>
                                            </div>
                                            <a title="<?php echo esc_attr(__('Change','js-support-ticket')); ?>" href="#" class="js-tkt-det-hdg-btn" id="chng-dept">
                                                <?php echo esc_html(__('Change','js-support-ticket')); ?>
                                            </a>
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
                                                        <a title="<?php echo esc_attr(__('view ticket','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid='.esc_attr($jsst_usertickets->id))); ?>">
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
                                                                $jsst_ticketmessage = esc_html($jsst_usertickets->statustitle);
                                                            } else {
                                                                $jsst_ticketmessage = esc_html(__('Open', 'js-support-ticket'));
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
								}else{ ?>
									<div class="js-tkt-det-cnt js-tkt-det-woocom">
										<div class="js-tkt-wc-order-box">
										<?php
										do_action('jsst_woocommerce_order_detail_admin', $jsst_order, $jsst_order_productid,jssupportticket::$jsst_data[0]->uid);
										?>
										</div>
									</div>
								<?php
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
                            if(!empty($jsst_envlicense)){ ?>
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
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Total Tickets",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                    if ($jsst_paidsupport['totalticket']==-1) {
                                                        echo esc_html(__("Unlimited",'js-support-ticket'));
                                                    } else {
                                                        echo esc_html($jsst_paidsupport['totalticket']);
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="js-tkt-wc-order-item">
                                                <div class="js-tkt-wc-order-item-title"><?php echo esc_html(__("Remaining Tickets",'js-support-ticket')); ?>:</div>
                                                <div class="js-tkt-wc-order-item-value">
                                                    <?php
                                                    if ($jsst_paidsupport['totalticket']==-1) {
                                                        echo esc_html(__("Unlimited",'js-support-ticket'));
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
