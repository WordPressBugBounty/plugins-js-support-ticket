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

function getDataForVisibleField(wpnonce, val, parentf, child, pvalue, cond) {
    var childs = child.split(",");
    var field_type = 'required';
    jQuery.each(childs, function(childi,childf){
    var type = jQuery('[name="'+childf+'"]').attr("type");
    if (type == 'text' ||type == 'email' || type == 'password' || type == 'file') {
        jQuery('[name="'+childf+'"]').val('');
    }
    else if (type == 'checkbox') {
        jQuery('[name="'+childf+'[]"]').removeAttr('checked');
        jQuery('[name="'+childf+'"]').removeAttr('checked');
    }
    else if (type == 'radio') {
        jQuery('[name="'+childf+'"]').prop('checked', false);
    }
    else if (jQuery('[name="'+childf+'"]').hasClass("js-ticket-custom-textarea")) {
        jQuery('[name="'+childf+'"]').val("");
    }
    else if (jQuery('[name="'+childf+'"]').hasClass("js-ticket-custom-select")) {
        jQuery('[name="'+childf+'"]').prop('selectedIndex', 0);
    }
    else{
        if (jQuery('[name="'+childf+'[]"]').attr("type") == 'checkbox') {
            field_type = 'notRequired';
        }
        type = "checkboxOrMultiple"
        if (jQuery('[name="'+childf+'[]"]').attr("multiple")) {
            jQuery('[name="'+childf+'[]"]').children().prop('selected', false);
            jQuery('[name="'+childf+'[]"]').prop('selectedIndex', 0);
        } else {
            jQuery('[name="'+childf+'[]"]').removeAttr('checked');
        }
    }
    if (val.length != 0){
        if (type == 'checkboxOrMultiple') {
            if (cond == 1) {
                if (pvalue == val) {
                    if (childi == 0) {
                        jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                        jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                        isFieldRequired (field_type, childf, 'show', wpnonce);
                    }
                }else{
                    jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                    jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    isFieldRequired (field_type, childf, 'hide', wpnonce);
                }
            }else if (cond ==0) {
                if (pvalue != val) {
                    if (childi == 0) {
                        jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                        jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                        isFieldRequired (field_type, childf, 'show', wpnonce);
                    }
                }else{
                    jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                    jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    isFieldRequired (field_type, childf, 'hide', wpnonce);
                }
            }
        }else{
            if (cond == 1) {
                if (pvalue == val) {
                    if (childi == 0) {
                        jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                        jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                        isFieldRequired (field_type, childf, 'show', wpnonce);
                    }
                }else{
                    jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                    jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    isFieldRequired (field_type, childf, 'hide', wpnonce);
                }
            }else if (cond ==0) {
                if (pvalue != val) {
                    if (childi == 0) {
                        jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").removeClass('visible');
                        jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").removeClass('visible');
                        isFieldRequired (field_type, childf, 'show', wpnonce);
                    }
                }else{
                    jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
                    jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
                    isFieldRequired (field_type, childf, 'hide', wpnonce);
                }
            }
        }
    }
    else{
        if (type == 'checkboxOrMultiple') {
            jQuery('[name="'+childf+'[]"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
            jQuery('[name="'+childf+'[]"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
        } else {
            jQuery('[name="'+childf+'"]').closest("div.js-form-custm-flds-wrp").addClass('visible');
            jQuery('[name="'+childf+'"]').closest("div.js-ticket-from-field-wrp").addClass('visible');
        }
        isFieldRequired (field_type, childf , 'hide', wpnonce);
    }
    });
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
