jQuery(document).ready(function(n){
    jQuery('.specialClass').closest("div.js-form-custm-flds-wrp").removeClass('visible');
    jQuery('.specialClass').closest("div.js-ticket-from-field-wrp").removeClass('visible');
});

function fillSpaces(string){
	string = string.replace(" ", "%20");
	return string;
}

function getDataForDepandantField(wpnonce, parentf, childf, type) {
    if (type == 1) {
        var val = jQuery("select#" + parentf).val();
    } else if (type == 2) {
        var val = jQuery("input[name=\'" + parentf + "\']:checked").val();
    }

    jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'fieldordering', task: 'DataForDepandantField', fvalue: val, child: childf, '_wpnonce':wpnonce}, function (data) {
        if (data) {
            var d = jQuery.parseJSON(data);
            jQuery("select#" + childf).replaceWith(jsstDecodeHTML(d));
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

                                        $field = jQuery(selector);
                                        // If not found, fallback to checkbox group selector
                                        if ($field.length === 0) {
                                            $field = jQuery("input[type='checkbox'][id^='" + condition.visibleParent + "_']");
                                        }
                                        // If not found, fallback to radiobutton group selector
                                        if ($field.length === 0) {
                                            $field = jQuery("input[type='radio'][id^='" + condition.visibleParent + "_']");
                                        }
                                        // If not found, fallback to multiselect group selector
                                        if ($field.length === 0) {
                                            $field = jQuery("select[id^='" + condition.visibleParent + "[]']");
                                        }
                                        // If not found, fallback to multiselect group selector
                                        if ($field.length === 0) {
                                            $field = false;
                                        }
                                        
                                        let fieldval = null;

                                        if ($field. length > 0) {
                                            var tag = $field.prop("tagName").toLowerCase();
                                            var type = $field.attr("type");

                                            if (tag === "select") {
                                                // Handles both single and multi-select dropdowns
                                                var isMultiSelect = $field.prop("multiple") === true;
                                                if (isMultiSelect) {
                                                    fieldval = [];
                                                    $field.find("option:selected").each(function () {
                                                        fieldval.push(this.value);
                                                    });
                                                } else {
                                                    fieldval = $field.val(); // jQuery returns array for multi-select
                                                }
                                            } else if (type === "checkbox") {
                                                // Handle checkbox group (collect all checked values)
                                                fieldval = [];
                                                $field.filter(":checked").each(function () {
                                                    fieldval.push(this.value);
                                                });
                                            } else if (type === "radio") {
                                                // Handle radio button group
                                                fieldval = jQuery("input[name='" + condition.visibleParent + "']:checked").val();
                                            } else {
                                                // Fallback for other input types
                                                fieldval = $field.val();
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
