jQuery(document).ready(function(n){
    jQuery('.specialClass').closest("div.js-form-custm-flds-wrp").removeClass('visible');
    jQuery('.specialClass').closest("div.js-ticket-from-field-wrp").removeClass('visible');
});

function fillSpaces(string){
	string = string.replace(" ", "%20");
	return string;
}

/**
 * What the parent question is currently answered as.
 *
 * Asked of the control that is actually on the page rather than of a flag
 * passed in from PHP. The flag said "1 for a select, 2 for radios", which meant
 * a rendering decision taken in customfields.php had to be kept in step with a
 * number written a few lines away from it - and it was not: the ticket listing
 * filter draws a radio question as a dropdown, because radios with a dozen
 * options make a mess of a filter bar, but went on passing 2. So it looked for
 * `input[name=...]:checked`, found nothing on a page where the control is a
 * select, sent an undefined value, and the dependent question simply stopped
 * filling in - with nothing anywhere saying why.
 *
 * The DOM already knows which control it is. `type` is still accepted so that
 * every existing caller keeps working, and is deliberately ignored.
 */
function jsstDepandantParentValue(parentf) {
    var sel = jQuery("select[name='" + parentf + "']");
    if (sel.length) {
        var got = sel.val();
        return jQuery.isArray(got) ? got.join(', ') : got;
    }
    /* Radios and tick boxes: one checked value, or all of them where the
       question takes more than one answer. */
    var checked = jQuery("input[name='" + parentf + "']:checked, input[name='" + parentf + "[]']:checked");
    if (checked.length) {
        return checked.map(function () { return jQuery(this).val(); }).get().join(', ');
    }
    var any = jQuery("[name='" + parentf + "']").not(':checkbox').not(':radio');
    return any.length ? any.val() : '';
}

function getDataForDepandantField(wpnonce, parentf, childf, type) {
    var val = jsstDepandantParentValue(parentf);

    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'fieldordering', task: 'DataForDepandantField', fvalue: val, child: childf, '_wpnonce':wpnonce}, function (data) {
        if (data) {
            var d = jQuery.parseJSON(data);
            /* By id and by name: the id is what this has always used, and the
               name is what survives a control being redrawn by something that
               did not set one. */
            jQuery("select#" + childf + ", select[name='" + childf + "']").first().replaceWith(jsstDecodeHTML(d));
        }
    });
}


function getDataForVisibleField(wpnonce, val, fieldname, conditionGroups) {
    // var childs = fieldname.split(",");
    var field_type = 'required';
    var finalShow = false;

    jQuery.each(conditionGroups, function(conditionGroupIndex, conditionGroup) {
        jQuery.each(conditionGroup, function(orConditionIndex, orCondition) {
            var childs = orCondition[0].visibleParentField.split(",");
            // old code start
            jQuery.each(childs, function(childi, childf) {
                var type = jQuery('[name="'+childf+'"]').attr("type");

                // Reset the field value
                if (type == 'text' || type == 'email' || type == 'password' || type == 'file') {
                    jQuery('[name="'+childf+'"]').val('');
                } else if (type == 'checkbox') {
                    jQuery('[name="'+childf+'[]"]').prop('checked', false);
                    jQuery('[name="'+childf+'"]').prop('checked', false);
                } else if (type == 'radio') {
                    jQuery('[name="'+childf+'"]').prop('checked', false);
                } else if (jQuery('[name="'+childf+'"]').hasClass("js-ticket-custom-textarea")) {
                    jQuery('[name="'+childf+'"]').val("");
                } else if (jQuery('[name="'+childf+'"]').hasClass("js-ticket-custom-select")) {
                    jQuery('[name="'+childf+'"]').prop('selectedIndex', 0);
                } else {
                    if (jQuery('[name="'+childf+'[]"]').attr("type") == 'checkbox') {
                        field_type = 'notRequired';
                    }
                    type = "checkboxOrMultiple";
                    if (jQuery('[name="'+childf+'[]"]').attr("multiple")) {
                        jQuery('[name="'+childf+'[]"]').children().prop('selected', false);
                        jQuery('[name="'+childf+'[]"]').prop('selectedIndex', 0);
                    } else {
                        jQuery('[name="'+childf+'[]"]').prop('checked', false);
                    }
                }

                
                if (val.length != 0) {
                    if (conditionGroups.hasOwnProperty(childf)) {
                        var conditionsArray = conditionGroups[childf];
                        // code start
                        finalShow = false; // Will become true if all groups pass

                        if (conditionsArray.length > 0) {
                            var allGroupsPass = true;

                            jQuery.each(conditionsArray, function(groupIndex, group) {
                                var groupPass = false; // Assume group fails unless a condition is true

                                jQuery.each(group, function(conditionIndex, condition) {
                                    console.log(condition);
                                    var result = false;
                                    var isUserField = condition.visibleParent.indexOf('ufield_') !== -1;
                                    let selector;

                                    if (condition.visibleCondition === "1" || condition.visibleCondition === "0") {
                                        // Select field
                                        selector = isUserField
                                            ? "select#" + condition.visibleParent
                                            : "select#" + condition.visibleParent + "id";

                                        $jsst_field = jQuery(selector);
                                        // If not found, fallback to checkbox group selector
                                        if ($jsst_field.length === 0) {
                                            $jsst_field = jQuery("input[type='checkbox'][id^='" + condition.visibleParent + "_']");
                                        }
                                        // If not found, fallback to radiobutton group selector
                                        if ($jsst_field.length === 0) {
                                            $jsst_field = jQuery("input[type='radio'][id^='" + condition.visibleParent + "_']");
                                        }
                                        // If not found, fallback to multiselect group selector
                                        if ($jsst_field.length === 0) {
                                            $jsst_field = jQuery("select[id^='" + condition.visibleParent + "[]']");
                                        }
                                        // If not found, fallback to multiselect group selector
                                        if ($jsst_field.length === 0) {
                                            $jsst_field = false;
                                        }
                                        
                                        let fieldval = null;

                                        if ($jsst_field. length > 0) {
                                            var tag = $jsst_field.prop("tagName").toLowerCase();
                                            var type = $jsst_field.attr("type");

                                            if (tag === "select") {
                                                // Handles both single and multi-select dropdowns
                                                var isMultiSelect = $jsst_field.prop("multiple") === true;
                                                if (isMultiSelect) {
                                                    fieldval = [];
                                                    $jsst_field.find("option:selected").each(function () {
                                                        fieldval.push(this.value);
                                                    });
                                                } else {
                                                    fieldval = $jsst_field.val(); // jQuery returns array for multi-select
                                                }
                                            } else if (type === "checkbox") {
                                                // Handle checkbox group (collect all checked values)
                                                fieldval = [];
                                                $jsst_field.filter(":checked").each(function () {
                                                    fieldval.push(this.value);
                                                });
                                            } else if (type === "radio") {
                                                // Handle radio button group
                                                fieldval = jQuery("input[name='" + condition.visibleParent + "']:checked").val();
                                            } else {
                                                // Fallback for other input types
                                                fieldval = $jsst_field.val();
                                            }
                                        }

                                        if (condition.visibleCondition === "1") {
                                            result = Array.isArray(fieldval) 
                                                ? fieldval.includes(condition.visibleValue) 
                                                : fieldval == condition.visibleValue;
                                        } else {
                                            if (Array.isArray(fieldval)) {
                                                // Prevent condition from being true when nothing is selected
                                                result = fieldval.length > 0 && !fieldval.includes(condition.visibleValue);
                                            } else {
                                                result = fieldval != condition.visibleValue;
                                            }
                                        }
                                    } else if (condition.visibleCondition === "2" || condition.visibleCondition === "3") {
                                        // Input field (no 'id' suffix regardless of isUserField)
                                        selector = "#" + condition.visibleParent;
                                        if (selector == '#fullname') {
                                            selector = '.js-support-ticket-form #name';
                                        }
                                        fieldval = jQuery(selector).val();

                                        if (fieldval !== undefined && fieldval !== null) {
                                            let fieldvalLower = decodeStoredValue(fieldval).toLowerCase();
                                            let valueLower = decodeStoredValue(condition.visibleValue).toLowerCase();
                                            
                                            if (condition.visibleCondition === "2") {
                                                result = fieldvalLower.indexOf(valueLower) !== -1;  // contains
                                            } else {
                                                result = fieldvalLower.indexOf(valueLower) === -1;  // does not contain
                                            }
                                        } else {
                                            result = false;
                                        }

                                    } else {
                                        result = false; // default/fallback
                                    }

                                    // Since inside a group we want OR relation
                                    if (result) {
                                        groupPass = true; // If any condition passes, the group passes
                                        return false; // Break inner loop
                                    }
                                });

                                // If any group fails, final result is false
                                if (!groupPass) {
                                    allGroupsPass = false;
                                    return false; // Break outer loop
                                }
                            });

                            finalShow = allGroupsPass;
                        }

                        // Based on finalShow, show or hide the field

                        if (finalShow) {
                            if (type == 'checkboxOrMultiple') {
                                jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                                jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                            } else {
                                jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                                jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                            }
                            isFieldRequired(field_type, childf, 'show', wpnonce);
                        } else {
                            if (type == 'checkboxOrMultiple') {
                                jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                                jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                            } else {
                                jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                                jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                            }
                            isFieldRequired(field_type, childf, 'hide', wpnonce);
                        }
                        // code end
                    } else {
                        if (type == 'checkboxOrMultiple') {
                            jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                            jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                        } else {
                            jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                            jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                        }
                    }

                } else {
                    // If no value is selected, show or hide based on the field type
                    if (type == 'checkboxOrMultiple') {
                        jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                        jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    } else {
                        jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                        jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    }
                    isFieldRequired(field_type, childf, 'hide', wpnonce);
                }
            });
            // old code end
        });
    });
}

function decodeStoredValue(encoded) {
    try {
        // Step 1: Decode HTML entities like &quot;
        const textarea = document.createElement("textarea");
        textarea.innerHTML = encoded;
        let decoded = textarea.value;

        // Step 2: Decode \u4f60\u597d to real characters
        // Wrap in double quotes and parse
        decoded = JSON.parse('"' + decoded.replace(/\\/g, '\\\\').replace(/"/g, '\\"') + '"');

        return decoded;
    } catch (e) {
        return encoded; // fallback
    }
}

function deleteCutomUploadedFile (field1) {
    jQuery("input#"+field1).val(1);
    jQuery("span."+field1).hide();
    
}

function isFieldRequired (field_type, field, state, wpnonce) {
    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'ticket', task: 'isFieldRequired', field:field, '_wpnonce':wpnonce}, function (data) {
        if (data) {
            if (data == 1 && state == 'show' && field_type == 'required') {
                jQuery('[name="'+field+'"]').attr('data-validation', 'required');
                jQuery('[name="'+field+'[]"]').attr('data-validation', 'required');
            } else if(data == 1 && state == 'hide') {
                jQuery('[name="'+field+'"]').attr('data-validation', '');
                jQuery('[name="'+field+'[]"]').attr('data-validation', '');
            }
        }
    });
    
}

function jsstDecodeHTML(html) {
    var txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
}

function jsReplyShowLoading(){
    jQuery('div#black_wrapper_ai_reply').show();
    jQuery('div#js_ai_reply_loading').show();
}

function jsReplyHideLoading(){
    jQuery('div#black_wrapper_ai_reply').hide();
    jQuery('div#js_ai_reply_loading').hide();
}

/*
 * One click, one submission. Customers on slow connections pressed Submit again
 * while the first request was still uploading, and got two tickets or two
 * replies. Once a ticket or reply form really submits - after every other
 * handler has run, so a form the validator stopped is left alone - its submit
 * buttons are disabled until the page changes. The server refuses a repeat
 * anyway (JSSTsubmitguard); this spares the customer the wait and the doubt.
 * Buttons come back after 20 seconds in case the page never changes (a
 * download, a network error), and when the page is shown again from the
 * browser's back-forward cache.
 */
(function (jQuery) {
    if (!jQuery) {
        return;
    }
    var selector = 'form.js-support-ticket-form, form.js-det-tkt-form, form#adminTicketform';
    var buttons = 'button[type="submit"], input[type="submit"], button:not([type])';

    function release(form) {
        jQuery(form).removeAttr('aria-busy').find(buttons).filter('[data-jsst-busy]').each(function () {
            jQuery(this).prop('disabled', false).removeAttr('data-jsst-busy');
        });
    }

    jQuery(document).on('submit', selector, function (event) {
        var form = this;
        setTimeout(function () {
            var prevented = event.isDefaultPrevented() || (event.originalEvent && event.originalEvent.defaultPrevented);
            if (prevented || jQuery(form).attr('aria-busy') === 'true') {
                return;
            }
            jQuery(form).attr('aria-busy', 'true').find(buttons).each(function () {
                if (!this.disabled) {
                    jQuery(this).prop('disabled', true).attr('data-jsst-busy', '1');
                }
            });
            setTimeout(function () { release(form); }, 20000);
        }, 0);
    });

    window.addEventListener('pageshow', function () {
        jQuery(selector).each(function () { release(this); });
    });
})(window.jQuery);

/**
 * Leave the Overdue series out of a report chart on a desk without the Service
 * Levels pack, which is what marks tickets overdue: without it the series is
 * always nothing, and a chart line for a feature the site does not have reads
 * as something missing. Removes the column named Overdue (and its colour), or
 * the row named Overdue in a chart that lists statuses as rows. (6 Oct 2026)
 */
function jsstDropOverdue(data, options) {
    if (typeof jsstOverdue === 'undefined' || !jsstOverdue.off || !data) {
        return;
    }
    for (var c = data.getNumberOfColumns() - 1; c >= 1; c--) {
        if (data.getColumnLabel(c) === jsstOverdue.label) {
            data.removeColumn(c);
            if (options && options.colors && options.colors.length >= c) {
                options.colors.splice(c - 1, 1);
            }
        }
    }
    if (data.getNumberOfColumns() > 0 && data.getColumnType(0) === 'string') {
        for (var r = data.getNumberOfRows() - 1; r >= 0; r--) {
            if (data.getValue(r, 0) === jsstOverdue.label) {
                data.removeRow(r);
            }
        }
    }
}
