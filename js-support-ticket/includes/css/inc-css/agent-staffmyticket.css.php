<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();

$jsst_color1 = jssupportticket::$jsst_colors['color1'];
$jsst_color2 = jssupportticket::$jsst_colors['color2'];
$jsst_color3 = jssupportticket::$jsst_colors['color3'];
$jsst_color4 = jssupportticket::$jsst_colors['color4'];
$jsst_color5 = jssupportticket::$jsst_colors['color5'];
$jsst_color6 = jssupportticket::$jsst_colors['color6'];
$jsst_color7 = jssupportticket::$jsst_colors['color7'];
$jsst_color8 = jssupportticket::$jsst_colors['color8'];
$jsst_color9 = jssupportticket::$jsst_colors['color9'];

$jsst_jssupportticket_css = '';

/*Code for Css*/
$jsst_jssupportticket_css .= '

/* Top Circle Count Boxes */
div.js-ticket-top-cirlce-count-wrp{
    float: left;
    margin-bottom: 30px;
    padding: 15px 10px;
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 12px; /* Rounded corners */
    background: white;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08); /* Soft shadow */

}

div.js-myticket-link{
    text-align:center;
    padding-left: 5px;
    padding-right: 5px;
    width: calc(100% / 5);
    box-sizing: border-box; /* Include padding in width */
}
div.js-ticket-myticket-link-myticket{
    width: calc(100% / 4); /* Adjust for 4 columns */
}
div.js-myticket-link a.js-myticket-link{
    display: flex; /* Use flexbox for alignment */
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 0px;
    text-decoration: none;
    min-width: 100%;
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 10px; /* Rounded corners for individual links */
    transition: all 0.3s ease; /* Smooth transitions */
    background-color: ' . $jsst_color3 . ';
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
.js-mr-rp{margin: auto;}
div.js-ticket-cricle-wrp{
    float: none; /* Remove float */
    // width: 80px; /* Fixed size for circles */
    // height: 80px;
    margin-bottom: 15px;
    position: relative;
    border-radius: 50%; /* Make it a perfect circle */
    overflow: hidden; /* Hide overflow for progress */
}

/* Search Ticket Form*/
div.js-ticket-search-wrp{
    float: left;
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 12px; /* Rounded corners */
    background-color: #ffffff;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    margin-bottom:30px;
}
div.js-ticket-search-wrp div.js-ticket-search-heading{
    float: left;
    width: 100%;
    padding: 20px;
    background-color: #f8f9fa; /* Light header background */
    border-bottom: 1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
    font-size: 19px;
    font-weight: 600;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp{
    float: left;
    width: 100%;
}
 input[type=checkbox], input[type=radio] {
    opacity: 1; 
    }
.js-form-cust-rad-fld-wrp.js-form-cust-ckb-fld-wrp { appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            /* Set initial size and style for our custom checkbox */
            width: 1.5rem; /* 24px */
            height: 1.5rem; /* 24px */
            border: 2px solid ' . $jsst_color5 . '; /* Light gray border */
            border-radius: 0.375rem; /* Rounded corners (md) */
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease-in-out;
            vertical-align: middle; /* Align with text */
            margin-right: 0.5rem; /* Space between checkbox and label */
        }
.js-filter-wrapper input[type="text"]::placeholder{color:' . $jsst_color4 . ';}
/* Custom styles for the radio buttons */
        .js-ticket-radio-box {
            /* Flex container for input and label */
            display: flex;
            align-items: center;
            justify-content: center; /* Center content horizontally */
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            border-radius: 0.5rem; /* Rounded corners */
            padding: 0.5rem; /* Padding inside the box */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06); /* Subtle shadow */
            background-color: #f8fafc; /* Light background */
            
            text-align: center; /* Ensure text is centered */
        }

        .js-ticket-radio-box:hover {
            background-color: #e2e8f0; /* Lighter background on hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06); /* Enhanced shadow on hover */
            transform: translateY(-2px); /* Slight lift on hover */
        }

        .js-ticket-radio-btn {
            /* Hide the default radio button */
           
        }

         /* Container for the filter section */
        .filter-section {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.75rem; /* rounded-xl */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-md */
            max-width: 400px;
            width: 100%;
        }

        /* Wrapper for custom radio/checkbox fields */
        .custom-checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 1rem; /* Space between checkbox items */
        }

        /* Individual checkbox item */
        .checkbox-item {
            display: flex;
            align-items: center;
            position: relative;
            cursor: pointer;
            -webkit-user-select: none; /* Prevent text selection */
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            padding-left: 2rem; /* Space for the custom checkbox */
        }

        /* Hide the default browser checkbox */
        .checkbox-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        /* Create a custom checkbox indicator */
        .checkbox-indicator {
            position: absolute;
            top: 0;
            left: 0;
            height: 1.25rem; /* h-5 */
            width: 1.25rem; /* w-5 */
            background-color: #e5e7eb; /* bg-gray-200 */
            border-radius: 0.375rem; /* rounded-md */
            transition: background-color 0.2s, border-color 0.2s;
            border: 1px solid ' . $jsst_color5 . '; /* border-gray-300 */
        }

        /* Style the checkbox indicator when checked */
        .checkbox-item input[type="checkbox"]:checked ~ .checkbox-indicator {
            background-color: '. $jsst_color1 .'; /* bg-blue-500 */
            border-color: '. $jsst_color1 .';
        }

        /* Create the checkmark/icon inside the indicator */
        .checkbox-indicator:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the checkmark when checked */
        .checkbox-item input[type="checkbox"]:checked ~ .checkbox-indicator:after {
            display: block;
        }

        /* Style the checkmark */
        .checkbox-item .checkbox-indicator:after {
            left: 0.4rem; /* Adjusted for better centering */
            top: 0.15rem; /* Adjusted for better centering */
            width: 0.35rem;
            height: 0.7rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Style the label text */
        .checkbox-label {
            color: #374151; /* text-gray-700 */
            font-weight: 500; /* font-medium */
        }

        /* Add hover effect to the custom indicator */
        .checkbox-item:hover input[type="checkbox"] ~ .checkbox-indicator {
            background-color: #d1d5db; /* bg-gray-300 */
        }

        /* Keep checked state color on hover */
        .checkbox-item:hover input[type="checkbox"]:checked ~ .checkbox-indicator {
            background-color: #2563eb; /* bg-blue-600 */
        }



        .js-ticket-radio-box label {
            /* Style the custom radio button appearance */
            display: flex !important; /* Use flex to center text vertically */
            align-items: center;
            width: 100%; /* Make label take full width of its container */
            color: #475569; /* Darker gray text */
            text-overflow: ellipsis; /* Add ellipsis for long text */
            white-space: nowrap; /* Prevent text wrapping */
            overflow: hidden; /* Hide overflowing text */
            
        }

        /* Style for the checked state */
        .js-ticket-radio-btn:checked + label {
            border-radius: 0.375rem; /* Slightly rounded corners for the label */
        }

        /* Ensure the parent container uses flexbox for proper wrapping */
        .js-form-cust-rad-fld-wrp {
            display: flex;
            flex-wrap: wrap; 
            justify-content:space-between; /* Center the radio boxes */
            gap:10px;
        }
        .js-ticket-search-visible{
            display: flex !important;
        }
        div.js-ticket-radio-box{align-items: center;border-radius: 5px;box-shadow:unset;padding:10px;flex:1 1 auto;}
        div.js-ticket-radio-box:hover{background-color:unset;box-shadow:unset;transform:none;}
         /* Container for the filter section */
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box{
            border: 1px solid ' . $jsst_color5 . ';
            padding: 12px 5px 12px 12px;
            border-radius: 5px; /* Slightly rounded input fields */
            display: flex;
            align-items: center;
            min-height: 52px;
            margin:0 !important;
            width:unset !important;
            flex:1 1 auto;

        }
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box input[type="checkbox"] {
            margin-right:10px;
            }
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box label{
            display: inline-block !important;
            align-items: center;
            width: 100%;
            color:' . $jsst_color4 . ';
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp{
            width:100%;
            height: auto; /* Auto height for better responsiveness */
            line-height: normal;
            background-color: #ffffff;
            color: ' . $jsst_color4 . ';
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05); /* Inner shadow for depth */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            margin-bottom:0;
            border:unset;
            gap:10px;

        }
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box input[type="checkbox"], div.jsst-main-up-wrapper .js-ticket-assigned-tome input[type="checkbox"]{
        display: inline-block !important;
        margin: 0 12px 0 0 !important; /* More spacing for checkbox */
        transform: scale(1.3); /* Larger checkbox */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 11px; /* Larger size */
        height: 11px;
        border: 1px solid ' . $jsst_color5 . '; /* Border with secondary color */
        border-radius: 2px; /* Slightly more rounded */
        background-color: #fff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box input[type="checkbox"]:checked, div.jsst-main-up-wrapper .js-ticket-assigned-tome input[type="checkbox"]:checked {
        background-color: ' . $jsst_color1 . '; /* Primary color fill when checked */
        border-color: ' . $jsst_color1 . ';
    }
    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box input[type="checkbox"]:after, div.jsst-main-up-wrapper .js-ticket-assigned-tome input[type="checkbox"]:after {
        content: "";
        position: absolute;
        top: -1px; /* Adjust checkmark position */
        left: -1px; /* Adjust checkmark position */
        width: 11px; /* Larger checkmark */
        height: 11px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
        background-size: contain;
        background-repeat: no-repeat;
    }
div.jsst-main-up-wrapper input, div.jsst-main-up-wrapper button, div.jsst-main-up-wrapper select, div.jsst-main-up-wrapper textarea {
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form{
    display: inline-block;
    width: 100%;
    float: left;
}
    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select[multiple="multiple"]{
      appearance: none;           /* Standard */
  -webkit-appearance: none;   /* Safari / Chrome */
  -moz-appearance: none;      /* Firefox */

  background-image: none;     /* Remove any default arrow background */
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper{
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: 10px;
    padding: 20px;
    background: ' . $jsst_color3 . '; /* Light background for form fields */
    border-radius:11px;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp{
    padding: 0;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp{
    padding: 0 10px 0 0;
    margin-bottom: 15px;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp.js-filter-radio-checkbox-field-wrp .js-ticket-radio-box{
    width:unset !important;
    flex:1 1 auto;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp.js-filter-radio-checkbox-field-wrp{
    width:fit-content !important;
    max-width:100%;
    flex:1 1 auto;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp.js-filter-radio-checkbox-field-wrp .js-filter-value{width:100%;}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field{
    margin-bottom:0;
    height:100%;
    width:100%;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.js-ticket-input-field,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.inputbox,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-departmentid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-helptopicid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-priorityid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-productid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-status,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-tagid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea{
    border-radius: 5px; /* Slightly rounded input fields */
    width:100%;
    padding: 12px 15px;
    height: auto; /* Auto height for better responsiveness */
    line-height: normal;
    background-color: #ffffff;
    border: 1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05); /* Inner shadow for depth */
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    margin-bottom:0;
    height:52px;
    font-weight:500;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select option{
    font-weight:500;
}
div.js-ticket-assigned-tome{
    border-radius: 5px; /* Slightly rounded input fields */
    width:100%;
    padding: 12px 15px;
    height: auto; /* Auto height for better responsiveness */
    line-height: normal;
    background-color: #fff;
    border: 1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05); /* Inner shadow for depth */
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    margin-bottom:0;
    height:53px;
    display: flex;
    align-items: center;
    }
div.js-ticket-search-wrp {border-radius: 11px;}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea {min-height: 52px;max-width:100%;}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.js-ticket-input-field:focus,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select:focus,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea:focus {
    border-color: ' . $jsst_color1 . ';
    box-shadow: 0 0 0 3px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.2); /* Focus ring */
    outline: none;
}

div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input#jsst-datestart,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input#jsst-dateend{
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($jsst_color4, 1) . '\'%3E%3Cpath d=\'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z\'/%3E%3C/svg%3E"); /* Modern calendar icon */
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 24px;
    padding-right: 45px; /* Adjust padding for icon */
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-departmentid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-helptopicid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-priorityid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-productid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-status,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-tagid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select{
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($jsst_color4, 1) . '\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E") no-repeat right 15px center / 20px; /* Modern dropdown arrow */
    -webkit-appearance: none; /* Remove default arrow */
    -moz-appearance: none;
    appearance: none;
    padding-right: 40px; /* Adjust padding for icon */
}

div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-button{
    padding-top: 15px;
    padding-bottom: 15px;
    display: inline-block;
    width: 100%;
    text-align: center;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp{
    padding:0;
    display: flex; /* Use flexbox for buttons */
    gap: 10px; /* Space between buttons */
    justify-content: center;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
}

/* Style for the js_ticketattachment button to match the search button */
.js_ticketattachment .button {
    min-width: 120px;
    border-radius: 8px;
    padding: 4px 20px;
    line-height: 2;
    height: auto;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    display: inline-block;
    background-color: ' . $jsst_color1 . ';
    color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color1 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: inherit;
}
.js_ticketattachment:hover .button {
    color: ' . $jsst_color7 . ';
    background-color: ' . $jsst_color2 . ';
    border-color: ' . $jsst_color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}
.js_ticketattachment {
    border: 1px solid ' . $jsst_color5 . ';
    text-align: center;
    padding: 7px 0px;
}
.js_ticketattachment {
    border: 1px solid ' . $jsst_color5 . ';
    padding: 7px 10px;
    display: flex;
    text-align:left;
    align-items: center;
    width: max-content;
    gap:8px;
    
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn{
    min-width: 120px; /* Minimum width for buttons */
    flex-grow: 1; /* Allow buttons to grow */
    border-radius: 8px; /* Rounded buttons */
    padding: 5px 20px;
    min-height:52px;
    line-height: normal;
    height: auto;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    display: flex; /* Ensure they behave like block elements for width */
    align-items: center;
    justify-content: center;
    text-align: center;
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn {
    color: ' . $jsst_color4 . ';
    border: 1px solid ' . $jsst_color5 . ';
    background: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn:hover {
    border-color: ' . $jsst_color1 . ';
    background-color: #f0f2f5;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn{
    background-color: ' . $jsst_color1 . ';
    color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color1 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn:hover{
    background-color: ' . $jsst_color2 . ';
    border-color: ' . $jsst_color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn{
    background-color: #f5f2f5; /* A neutral reset button */
    color: ' . $jsst_color4 . ';
    border: 1px solid ' . $jsst_color5 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn:hover{
    background-color: '. $jsst_color2 .';
    color: ' . $jsst_color7 . ';
    border-color: '. $jsst_color5 .';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
div#js-filter-wrapper-toggle-plus,
div#js-filter-wrapper-toggle-minus{
    float: left;
    width: 100%;
    cursor: pointer;
    padding: 18px 15px;
    text-align: center;
    background-color: ' . $jsst_color1 . '; /* Match primary button color */
    color: ' . $jsst_color7 . ';
    border-radius: 8px;
    margin-top: 10px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}
div#js-filter-wrapper-toggle-plus:hover,
div#js-filter-wrapper-toggle-minus:hover{
    background-color: ' . $jsst_color2 . ';
}

/* Specific Search Field Visibility */
div#js-filter-wrapper-toggle-search {
    display: block; /* Ensure this is visible by default */
    flex:1 1 auto;
}
div#js-filter-wrapper-toggle-ticketid, /* This class is on the toggle-area div */
div#js-filter-wrapper-toggle-area {
    display:none;
    width: 100%;
    flex-wrap: wrap;
}
/* My Tickets & Staff My Tickets */

 /* --- Main Ticket Container --- */
        .js-ticket-wrapper{
            display: flex;
            flex-wrap:wrap;
            align-items: flex-start;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 31, 63, 0.08);
            padding: 15px;
            margin: 0 auto;
           margin-bottom: 15px;
            border: 1px solid '. $jsst_color5 .';
            width: 100%;
            transition: all 0.3s ease-in-out;
            padding-bottom: 25px;
            border: 1px solid ' . $jsst_color5 . ';
         }
        .js-ticket-toparea {
            display: flex;
            flex-wrap: wrap;
            align-items: center; /* Vertically align image and text */
            width:calc(100% - 150px);
            flex:1 1 auto;
            gap:10px;
        }
        div.js-ticket-wrapper div.js-ticket-data .name span.js-ticket-value::before{
            content: "\1F464"; /* Unicode character for a generic user icon */
            margin-right: 0.3rem; /* Space between icon and text */
            font-size: 17px; /* Adjust size relative to text */
            vertical-align: middle; /* Align icon vertically with text */
        }
        
        .js-ticket-wrapper:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 31, 63, 0.12);
        }
        
        /* --- Column Structure --- */
        /* User Image Column */
        .js-ticket-pic {
            flex: 0 0 auto; 
            }

        .js-col-xs-12.js-col-md-12.js-ticket-toparea .js-col-xs-2{
        	width:auto;
        }
        .js-ticket-wrapper .js-ticket-toparea .js-ticket-pic{
            width: 80px !important;
            height: 80px;
            border-radius: 50%;
            position: relative;
            padding: 0px;
            margin:0 20px;
        }
        .js-ticket-wrapper:hover {border:1px solid' . $jsst_color1 . ';}
        div.js-ticket-body-data-elipses a:hover {color:' . $jsst_color1 . ';}
        .js-ticket-data.js-nullpadding {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .js-ticket-staff-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            
        }
        .js-ticket-wrapper .js-ticket-pic img{
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            max-height: 100%;
        }
        /* Main Ticket Info Column */
        .js-ticket-data {
            flex: 1 1 auto; /* Allow this column to grow and shrink */
            min-width: 250px;
        }
        
        /* Right-side Meta Data Column */
        .jsst-main-up-wrapper .js-ticket-data1 {
            flex: 0 0 240px; /* Fixed width */
            text-align: left; /* Changed from right */
            padding-left: 18px;
            padding-right: 0px;
            border-left: 1px solid ' . $jsst_color5 . ';
        }
        
        .js-nullpadding {
            padding: 0;
        }

        /* --- User Name & Ticket Subject --- */
        .js-ticket-body-data-elipses.name {
            color: #606770;
            margin-bottom: 8px;
        }
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title{
            font-weight:500;
        }
        .js-ticket-title-anchor {
        
            font-weight: 700;
            color: #1a2b47;
            text-decoration: none;
            line-height: 1.3;
        }

        .js-ticket-title-anchor:hover {
            color: #0056b3;
        }
        
        /* --- Status & Priority Badges (Modern Solid Style) --- */
        .prorty, .js-ticket-status {
            display: inline-flex;
            align-items: center;
            padding: 6px 20px;
            border-radius: 20px;
          
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-top: 16px;
            margin-right: 10px;
            color: #fff;
            margin-left: 10px;
        }
        .js-tkt-custm-flds-wrp.js_ticketattachment .js-ticket-status {margin-left: 0px;}

        
        .ticketstatusimage {
            margin:16px 10px 0 10px;
        }
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-closedby {
            display: inline-block;
            color: #463e8f;
            text-transform: capitalize;
            border: 1px solid #817cb3;
            padding: 0 8px;
            cursor: pointer;
            margin-left: 5px;
        }
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-closed-date {
            color: #3f3f41;
            border: 1px solid #e6e5e5;
            padding: 0 8px;
            position: absolute;
            background-image: linear-gradient(to top, #d3d3d2, #f6f6f6);
            top: 30px;
            display: none;
            min-width: 160px;
            z-index: 2147483647;
        }
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-closedby-wrp{
            font-size:15px;
        }
        .js-ticket-data.js-nullpadding{position:relative;}
        .js-ticket-body-data-elipses.name{position:unset;}
        /* --- Secondary Details (Department, etc.) --- */
        .js-ticket-padding-xs {
            
        }

        .js-ticket-body-data-elipses {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        	margin-bottom: 5px;
            color: '. $jsst_color4 .';
        }

        .js-ticket-title {
            color: #8a929c;
        }

        .js-ticket-value {
            color: #4e555f;
        }
        
        .js-ticket-value[onclick]:hover {
            color: #0056b3;
            cursor: pointer;
        }

        /* --- Right Column Rows Styling (UPDATED) --- */
        .js-ticket-data-row {
            margin-bottom: 12px;
        }
        .js-ticket-data-row:last-child {
            margin-bottom: 0;
        }

        .js-ticket-data-tit, .js-ticket-data-val {
            display: inline; /* Display title and value on the same line */
        }

        .js-ticket-data-tit {
           
            color: #606770;
            font-weight: 500;
            text-transform: none; /* Removed uppercase for a cleaner look */
        }

/* Sorting Section */
div.js-ticket-sorting{
    padding: 15px 20px;
    margin-bottom: 30px;
    background: ' . $jsst_color2 . '; /* Darker background for sorting */
    color: ' . $jsst_color7 . ';
    border-radius: 12px; /* Consistent rounded corners */
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
div.js-ticket-sorting span.js-ticket-sorting-link{
    padding: 0;
}
div.js-ticket-sorting span.js-ticket-sorting-link a{
    text-decoration: none;
    display: block;
    padding: 12px 18px;
    text-align:center;
    border-radius: 8px;
    transition: all 0.3s ease;
    background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent white */
    color: ' . $jsst_color7 . ';
}
div.js-ticket-sorting span.js-ticket-sorting-link a img{
    display: inline-block;
    vertical-align: middle;
    margin-left: 5px;
    filter: brightness(0) invert(1); /* Make icons white */
}
div.js-ticket-sorting-left {
    float: none;
    width: auto;
    flex-grow: 1;
}
div.js-ticket-sorting-heading {
    float: none;
    width: 100%;
    padding: 0;
    line-height: normal;
    font-size: 17px;
    font-weight: 600;
}
div.js-ticket-sorting-right {
    float: none;
    width: auto;
}
div.js-ticket-sorting-right div.js-ticket-sort {
    float: none;
    display: flex;
    align-items: center;
    gap: 10px;
}
div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select {
    float: none;
    width: 160px; /* Wider select box */
    height: 45px;
    padding: 10px 15px;
    appearance: none;
    line-height: normal;
    border-radius: 8px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($jsst_color4, 1) . '\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E") no-repeat right 15px center / 20px;
    border: 1px solid ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    margin-bottom:0;
    line-height: 1.2;
    margin-bottom:0;
    line-height: 1.2;
}
div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select:focus {
    border-color: ' . $jsst_color1 . ';
    box-shadow: 0 0 0 3px rgba(' . hexdec(substr($jsst_color1, 1, 2)) . ', ' . hexdec(substr($jsst_color1, 3, 2)) . ', ' . hexdec(substr($jsst_color1, 5, 2)) . ', 0.2);
    outline: none;
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn {
    float: none;
    padding: 10px 12px;
    line-height: normal;
    height: 45px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid ' . $jsst_color5 . ';
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn:hover {
    background-color: #f0f2f5;
    border-color: ' . $jsst_color1 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn img {
    height: 20px;
    width: 29px;
}

select ::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}
div.js-ticket-sorting{border: 1px solid ' . $jsst_color5 . ';width:calc(100% - 40px);margin-left:20px;margin-right:20px;}

/* Responsive Adjustments */
@media (max-width: 991px) {
    div.js-myticket-link{
        width: calc(100% / 2); /* 2 columns on tablet */
        margin-bottom: 15px;
    }
    div.js-ticket-myticket-link-myticket{
        width: calc(100% / 2);
    }
    div.js-ticket-wrapper div.js-ticket-toparea {
        flex-direction: column;
        align-items: flex-start;
    }
    .js-ticket-wrapper .js-ticket-toparea .js-ticket-pic{
        margin-bottom: 15px;
    }
    div.js-ticket-wrapper div.js-ticket-data {
        width: 100%;
        min-width: unset;
    }
    div.js-ticket-wrapper div.js-ticket-data1 {
        width: 100%;
        border-left: none;
        border-top: 1px solid ' . $jsst_color5 . ';
        border-top-right-radius: 0;
        border-bottom-left-radius: 12px;
        padding-left:15px;
    }
    /*div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp,
     div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp {
        padding: 0 5px;
    }*/
        div.js-ticket-wrapper div.js-ticket-data1{padding-top:15px;margin-top:15px;}
        .js-ticket-data1{flex:0 0 auto;}
}
@media (max-width: 768px) {
    .js-ticket-toparea {
        flex-direction: column;
        align-items: flex-start;
    }
    .js-ticket-pic {
        margin-bottom: 16px;
        padding-right: 5px;
    }
    .js-ticket-data {
        padding-right: 0;
        margin-bottom: 24px;
        width: 100%;
    }
    .js-ticket-data1 {
        width: 100%;
        text-align: left;
        padding-left: 10px;
        padding-top: 24px;
        border-left: none;
        border-top: 1px solid ' . $jsst_color5 . ';
    }
    div.js-myticket-link{
        width: 100%; /* Full width on mobile */
    }
    div.js-ticket-myticket-link-myticket{
        width: 100%;
    }
    div.js-ticket-search-wrp div.js-ticket-search-heading {
        padding: 15px;
        font-size: 1.1em;
    }
    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper {
        padding: 15px;
    }
    div.js-ticket-sorting {
        flex-direction: column;
        align-items: flex-start;
    }
    div.js-ticket-sorting-right {
        width: 100%;
    }
    div.js-ticket-sorting-right div.js-ticket-sort {
        width: 100%;
        justify-content: space-between;
    }
    div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select {
        width: calc(100% - 60px); /* Adjust width for button */
    }
                .js-ticket-wrapper{
        align-items:flex-start;
        }
        div.js-ticket-wrapper div.js-ticket-pic{
            margin-top:50px;
        }
}
@media (max-width: 480px) {
.js-col-xs-12.js-col-md-12.js-ticket-toparea .js-col-xs-2 {margin:auto;padding:0;}
div.js-ticket-wrapper div.js-ticket-data {justify-content:center;}
span.js-ticket-wrapper-textcolor {margin-top:0 !important;}
.js-ticket-wrapper{flex-wrap:wrap;}
div.js-ticket-wrapper div.js-ticket-pic{width:100%;max-width:100% !important;text-align:center;display:flex;justify-content:center;}
div.js-ticket-wrapper div.js-ticket-toparea{width:100%;}

}
';
/*Code For Colors*/
$jsst_jssupportticket_css .= '
/* My Tickets */
    /* Top Circle Count Box*/
        div.js-ticket-top-cirlce-count-wrp {border:1px solid' . $jsst_color5 . ';}
        div.js-myticket-link a.js-myticket-link{border:1px solid' . $jsst_color5 . ';}
        div.js-myticket-link a.js-myticket-link:hover{box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); background-color: #f8f9fa; transform: translateY(-3px);}
        /* The ring colour of each tab. Status hues are deliberately literal
           rather than theme colours: green means open and red means closed
           whichever of the seven palettes the site has picked, and a palette
           that happened to be red would make Open read as a problem.
           js-ticket-waitingagent and js-ticket-waitingcustomer are the two
           tabs 4.5-UX-01 added; they had no colour here at all, which is why
           both of their rings drew in the default grey. (Roadmap 4.5-UX-01) */
        .js-ticket-answer{background-color:#F7B731;} /* Vibrant Orange */
        .js-ticket-close{background-color:#E74C3C;} /* Strong Red */
        .js-ticket-allticket{background-color:#3498DB;} /* Bright Blue */
        .js-ticket-open{background-color:#2ECC71;} /* Emerald Green */
        .js-ticket-overdue{background-color:#E67E22;} /* Carrot Orange */
        .js-ticket-waitingagent{background-color:#7C5CE6;} /* Indigo */
        .js-ticket-waitingcustomer{background-color:#17A2B8;} /* Teal */
        /* The label under each ring takes the colour of that ring. These used
           to be shuffled - the blue tab was labelled amber and the orange one
           blue - so a tile said one thing and drew another. */
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-blue{color:#3498DB;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-red{color:#E74C3C;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-orange{color:#E67E22;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-green{color:#2ECC71;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-pink{color:#E67E22;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-purple{color:#7C5CE6;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-teal{color:#17A2B8;}
        div.js-myticket-link a.js-myticket-link div.progress::after {border: 25px solid #e0e0e0;} /* Lighter grey for progress background */
        div.js-myticket-link a.js-myticket-link.js-ticket-green.active{border-color:#2ECC71; box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-blue.active{border-color:#3498DB; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-red.active{border-color:#E74C3C; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-orange.active{border-color:#E67E22; box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-pink.active{border-color:#E67E22; box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-purple.active{border-color:#7C5CE6; box-shadow: 0 4px 10px rgba(124, 92, 230, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-teal.active{border-color:#17A2B8; box-shadow: 0 4px 10px rgba(23, 162, 184, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-green:hover{border-color:#2ECC71;}
        div.js-myticket-link a.js-myticket-link.js-ticket-blue:hover{border-color:#3498DB;}
        div.js-myticket-link a.js-myticket-link.js-ticket-red:hover{border-color:#E74C3C;}
        div.js-myticket-link a.js-myticket-link.js-ticket-orange:hover{border-color:#E67E22;}
        div.js-myticket-link a.js-myticket-link.js-ticket-pink:hover{border-color:#E67E22;}
        div.js-myticket-link a.js-myticket-link.js-ticket-purple:hover{border-color:#7C5CE6;}
        div.js-myticket-link a.js-myticket-link.js-ticket-teal:hover{border-color:#17A2B8;}
        div.js-myticket-link a.js-myticket-link.js-ticket-brown.active{border-color:#6C7A89; box-shadow: 0 4px 10px rgba(108, 122, 137, 0.3);} /* A neutral brown/grey for All Tickets */
        div.js-myticket-link a.js-myticket-link.js-ticket-brown:hover{border-color:#6C7A89;}

    /* Search Ticket Form*/
        div.js-ticket-search-wrp{border:1px solid' . $jsst_color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-search-heading{background-color:#eef2f7;border-bottom:1px solid' . $jsst_color5 . '; color:' . $jsst_color4 . '}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper {background: #fcfdfe;}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field{background-color:#fff;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div#js-filter-wrapper-toggle-ticketid input.js-ticket-input-field{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.js-ticket-input-field{background-color:#fff;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp{background-color:#fff;color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.inputbox{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-departmentid{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-helptopicid{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-priorityid{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-productid{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-status{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-tagid{background-color:#fff !important;border:1px solid' . $jsst_color5 . ';color: ' . $jsst_color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value input.js-ticket-input-field{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-departmentid{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-helptopicid{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-priorityid{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-productid{background-color:#fff;border:1px solid' . $jsst_color5 . ';}
        div#js-filter-wrapper-toggle-plus{background-color:' . $jsst_color1 . ';}
        div#js-filter-wrapper-toggle-minus{background-color:' . $jsst_color1 . ';}
    /* Search Ticket Form*/
    /* My Tickets $ Staff My Tickets*/
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select{background-color:#fff;border: 1px solid ' . $jsst_color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea{background-color:#fff;border: 1px solid ' . $jsst_color5 . ';}
        div.js-ticket-wrapper div.js-ticket-pic{min-width: 80px;height: 80px;max-width: fit-content;margin-top:15px}
        div.js-ticket-wrapper div.js-ticket-pic img{width:80px;height:80px;}
        div.js-ticket-wrapper div.js-ticket-data .name span.js-ticket-value {color:' . $jsst_color4 . ';}
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title{color:' . $jsst_color2 . ';}
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value{color:' . $jsst_color4 . ';}
        div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-tit {color:' . $jsst_color2 . ';}
        div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-val {color:' . $jsst_color4 . ';}
        div.js-ticket-sorting {color: ' . $jsst_color7 . ';} /* Use primary color for sorting section */
        div.js-ticket-sorting span.js-ticket-sorting-link a{background:rgba(255,255,255,0.15);color:' . $jsst_color7 . ';}
        div.js-ticket-sorting span.js-ticket-sorting-link a.selected,
        div.js-ticket-sorting span.js-ticket-sorting-link a:hover{background:' . $jsst_color2 . ';}
        div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select {background: #fff;color: ' . $jsst_color4 . ';border: 1px solid ' . $jsst_color5 . ';}
        div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn {background: #fff;}

    /* My Tickets $ Staff My Tickets*/
/* My Tickets */

    /*
     * =================================================================
     * TICKET TAGS IN THE QUEUE (Roadmap 4.0-CORE-17)
     * =================================================================
     * style.css carries a .jsst-tag-chip in fixed indigo at 12px. That was
     * written before this screen rendered any chips: against a block theme body
     * size it reads as tiny, and the fixed indigo ignores the palette every
     * other control on this page follows. Restated here in theme colours and
     * rem, so the chips scale with the reader rather than the theme prose size.
     */
    .jsst-tag-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .jsst-tag-row .js-ticket-value {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .jsst-tag-row a.jsst-tag-chip {
        display: inline-block;
        margin: 0;
        padding: 3px 12px;
        border: 1px solid ' . esc_attr($jsst_color5) . ';
        border-radius: 12px;
        /* color3, not color7: color7 is the contrast colour for text on dark
           fills (#ffffff by default), which would make the chip white on white. */
        background-color: ' . esc_attr($jsst_color3) . ';
        color: ' . esc_attr($jsst_color1) . ';
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.45;
        text-decoration: none;
        transition: background-color .15s ease, color .15s ease, border-color .15s ease;
    }

    .jsst-tag-row a.jsst-tag-chip:hover,
    .jsst-tag-row a.jsst-tag-chip:focus {
        background-color: ' . esc_attr($jsst_color1) . ';
        border-color: ' . esc_attr($jsst_color1) . ';
        color: #fff;
        text-decoration: none;
    }

    /* A chip is a link that re-runs the queue search, so it has to be visible
       to a keyboard user landing on it. */
    .jsst-tag-row a.jsst-tag-chip:focus-visible {
        outline: 2px solid ' . esc_attr($jsst_color2) . ';
        outline-offset: 2px;
    }
';

/* --------------------------------------------------------------------------
   The queue controls added in 4.5 - saved views, the bulk bar, row selection,
   the tab tiles and the workspace nav strip. (Roadmap 4.5-UX-01, 4.5-FE-02)

   These shipped with class names and no stylesheet behind them, so the front
   end rendered them as raw browser controls: a default grey button, a square
   unstyled input, and two labels meant only for a screen reader showing as
   full-size text. They belong here, in this page's own stylesheet, taking
   their colours from the theme the way every other rule on this page does -
   a hard-coded colour would ignore whichever of the seven palettes the site
   has chosen.
   -------------------------------------------------------------------------- */
$jsst_jssupportticket_css .= '

/* ---------------------------------------------------------------------------
   Clear the floats first.

   This page is built out of floats: the header wrapper, the tile row and the
   search panel are all float:left. A component that arrives after one of them
   and establishes its own formatting context - anything display:flex - is
   laid out BESIDE the float rather than below it, and takes whatever width is
   left over. Beside a float that is already the full width of the page, that
   is nothing: the box is still there, still the right height, and 0px wide.

   That is what the bulk bar did the moment its row stopped carrying an
   explicit width, and what the workspace nav strip was doing on every portal
   screen - an empty bordered panel with six invisible links stacked inside it.
   The strip is cleared in style.css because it is printed on four layouts; the
   rest are this page\'s own and are cleared here.
   --------------------------------------------------------------------------- */
.jsst-queue-scopes,
details.jsst-queue-columns,
.jsst-queue-views,
.jsst-queue-bulk,
div.js-ticket-sorting {
    clear: both;
}

/* ---------------------------------------------------------------------------
   The tab tiles.

   The row was a float grid sized for five tiles - width: calc(100% / 5) - and
   4.5-UX-01 made it six. The sixth wrapped onto a line of its own and, because
   the tiles are not all the same height, it caught on the one above and left a
   hole the height of a tile in the middle of the page. Floats also cannot make
   a row of equal-height cards, which is what this is.

   It is a flex row now. The tiles size themselves from a basis rather than a
   fraction, so adding or removing a tab cannot break the row again: six fit
   across a desk, three across a tablet, two across a phone.

   min-width: 0 matters. A flex item defaults to min-width: auto, which is its
   min-content width, and the ring inside each tile is 150px square - so six
   tiles refused to shrink below 204px each, overflowed, and wrapped anyway.
   --------------------------------------------------------------------------- */
div.js-ticket-top-cirlce-count-wrp {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    justify-content: center;
    float: none;
    width: 100%;
}
div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link {
    display: flex;
    float: none;
    width: auto;
    min-width: 0;
    max-width: 100%;
    flex: 1 1 140px;
    margin: 0 0 10px;
}
div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link > a.js-myticket-link {
    width: 100%;
    min-width: 0;
    padding: 16px 6px;
}

/* The ring is drawn at 150px by status_graph.css out of absolutely positioned
   halves and clip: rect() values, so it cannot be resized by setting a width -
   every one of those rects would have to be rewritten. Scaling from the top
   left corner and giving the wrapper the finished size does it in two lines,
   and keeps the ring out of the tile\'s min-content width. */
div.js-ticket-top-cirlce-count-wrp div.js-ticket-cricle-wrp {
    width: 108px;
    height: 108px;
    margin: 0 auto 12px;
    overflow: visible;
}
div.js-ticket-top-cirlce-count-wrp div.js-ticket-cricle-wrp .js-mr-rp {
    margin: 0;
    -webkit-transform: scale(0.72);
    -ms-transform: scale(0.72);
    transform: scale(0.72);
    -webkit-transform-origin: top left;
    -ms-transform-origin: top left;
    transform-origin: top left;
}
div.js-ticket-top-cirlce-count-wrp span.js-ticket-circle-count-text {
    min-width: 0;
    font-size: 0.7em;
    line-height: 1.35;
    overflow-wrap: break-word;
    word-wrap: break-word;
    text-wrap: balance;
}
/* Never split a count from its brackets. */
div.js-ticket-top-cirlce-count-wrp span.js-ticket-circle-count-num {
    white-space: nowrap;
}

/* For a screen reader only. This class was used in the markup and defined
   nowhere, which is why "Name for this view" and "Select ticket ..." were
   being drawn on the page. Not display:none - that would take them away from
   the screen readers they exist for. */
.js-ticket-screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ---------------------------------------------------------------------------
   The controls.

   Every control this page already had is 52px tall with a white face, a
   ' . $jsst_color5 . ' border, 12px 15px of padding and 5px of radius on a
   field or 8px on a button. The 4.5 controls arrived at 33px, 37px and 44px
   with three different borders, which is the whole of what "it does not look
   like the rest of the page" means.

   They also take their type size from the page rather than naming one. Every
   other control here inherits it, so a theme that sets a 22px body - and the
   one this was reported on does - had 22px text in the search box beside 14px
   text in the bulk bar. font-size: inherit is what the rest of the page does;
   the quieter labels are given a fraction of it so they stay in proportion
   whatever the theme picks.
   --------------------------------------------------------------------------- */
.js-ticket-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    /* 2.5em rather than a pixel floor, for the same reason as the type size:
       the buttons beside these are as tall as the page\'s own text makes them,
       so a fixed 52px is right on a 22px theme and too tall on a 16px one. */
    min-height: 2.5em;
    height: auto;
    padding: 12px 20px;
    background: ' . $jsst_color1 . ';
    border: 1px solid ' . $jsst_color1 . ';
    border-radius: 8px;
    color: ' . $jsst_color7 . ';
    font-size: inherit;
    font-weight: 600;
    line-height: normal;
    text-decoration: none;
    cursor: pointer;
}
.js-ticket-btn:hover, .js-ticket-btn:focus {
    background: ' . $jsst_color7 . ';
    color: ' . $jsst_color1 . ';
}

/* The second button in a pair. "Back to the site default" undoes a choice and
   "Save columns" makes one; drawn identically they read as two equal offers,
   and the destructive-looking one is the one nobody meant to press. This is
   the same pale secondary the Reset button beside the search box uses, so the
   page has one idea of what a secondary button looks like rather than two. */
.js-ticket-btn.js-ticket-btn-quiet {
    background: #f5f2f5;
    border-color: ' . $jsst_color5 . ';
    color: ' . $jsst_color4 . ';
}
.js-ticket-btn.js-ticket-btn-quiet:hover,
.js-ticket-btn.js-ticket-btn-quiet:focus {
    background: ' . $jsst_color2 . ';
    border-color: ' . $jsst_color5 . ';
    color: ' . $jsst_color7 . ';
}

/* The shared field look, given to every 4.5 control on this page at once.
   Named tag-and-class deep enough to beat the plugin\'s own input.inputbox
   rules, which otherwise leave one field in a bar sized differently from the
   one beside it. */
.jsst-queue-views input.inputbox.jsst-queue-viewname,
select.jsst-queue-view,
.jsst-queue-bulk select.inputbox,
.jsst-queue-bulk input[type="text"].inputbox {
    box-sizing: border-box;
    height: auto;
    min-height: 52px;
    padding: 12px 15px;
    background-color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 5px;
    color: ' . $jsst_color4 . ';
    font-size: inherit;
    font-weight: 500;
    line-height: normal;
}
.jsst-queue-viewname::placeholder,
.jsst-queue-bulk input[type="text"].inputbox::placeholder { color: ' . $jsst_color4 . '; }

/* A select on this page draws its own arrow, because the theme takes the
   native one away with appearance:none. Without this the bulk action select
   is a text box that happens to open a menu. The arrow is the same one the
   search panel\'s selects use, in the same place.

   The saved-views picker is here for exactly that reason. The search panel
   styles its own selects by naming each one by id - #jsst-departmentid,
   #jsst-status and the rest - and #viewid is in neither that list nor this
   one, so it kept the theme\'s appearance:none and nothing else: a 102px
   native grey box with square corners and no arrow, sitting among fields that
   are white, 52px and rounded. */
select.jsst-queue-view,
.jsst-queue-bulk select.inputbox {
    background: ' . $jsst_color7 . ' url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($jsst_color4, 1) . '\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E") no-repeat right 15px center / 20px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 45px;
}

/* Saving a search as a view. */
.jsst-queue-views {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
    margin: 12px 0 0;
    padding: 12px 14px;
    background: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 8px;
}
.jsst-queue-views > * {
    min-width: 0;
    margin: 0 10px 0 0;
}
.jsst-queue-viewname {
    flex: 1 1 260px;
    min-width: 0;
    max-width: 420px;
}
/* Drawn as a field of its own - the same white, bordered, 52px box as the
   name beside it - so the checkbox and its words read as one control inside
   the strip rather than loose text floating on the tint. */
.jsst-queue-share {
    display: inline-flex;
    align-items: center;
    box-sizing: border-box;
    min-width: 0;
    min-height: 52px;
    padding: 0 15px;
    background-color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 5px;
    font-size: 0.8em;
    color: ' . $jsst_color4 . ';
    cursor: pointer;
}
.jsst-queue-share input[type="checkbox"] {
    flex: 0 0 auto;
    width: 18px;
    height: 18px;
    margin: 0 8px 0 0;
    accent-color: ' . $jsst_color1 . ';
    cursor: pointer;
}
.jsst-queue-share span { min-width: 0; }
.jsst-queue-view-delete {
    align-self: center;
    font-size: 0.75em;
    color: ' . $jsst_color4 . ';
    text-decoration: underline;
}
.jsst-queue-view-delete:hover, .jsst-queue-view-delete:focus { color: ' . $jsst_color2 . '; }

/* ---------------------------------------------------------------------------
   Choosing a view: a pair on the search panel\'s button row, not a band of
   its own.

   Two earlier attempts at this were both a full-width box holding one small
   select. First the plain .jsst-queue-views card - white, fully bordered, 8px
   radius - which floated between the panel and the save strip and broke the
   flush bottom edge that strip is shaped for. Then the same width as a tinted
   strip above the save strip, which fixed the edge and left the real problem
   untouched: a 1038px band eighty per cent empty, stacked on another band, so
   the panel ended in two grey slabs for two small controls.

   The button row already had an empty right-hand half, and Search and Reset
   are the right company - none of the three is a filter, all three act on the
   search as a whole. `margin-left:auto` beats the row\'s justify-content and
   parks the pair at the far end.
   --------------------------------------------------------------------------- */
.jsst-queue-viewpick {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}
select.jsst-queue-view {
    width: auto;
    min-width: 200px;
    max-width: 260px;
}

/* ---------------------------------------------------------------------------
   Saving one, as the search card\'s footer rather than a card inside it.

   The form sits inside the search panel, and it was drawn with the panel\'s
   own treatment - white, fully bordered, its own radius - so the page showed
   a bordered white box inside a bordered white box, and a second text field
   the same size as the search field directly under it. Two identical fields
   stacked read as two filters, which is not what the second one is.

   As a tinted strip along the bottom of the panel, with a hairline above it
   and the panel\'s own bottom corners, it reads as what it is: something to
   do with the search that has just been typed.
   --------------------------------------------------------------------------- */
.jsst-queue-views.jsst-queue-saveview {
    margin: 0;
    padding: 16px 20px;
    background: ' . $jsst_color3 . ';
    border: 0;
    border-top: 1px solid ' . $jsst_color5 . ';
    border-radius: 0 0 12px 12px;
}
.jsst-queue-saveview-label {
    flex: 0 0 auto;
    min-width: 0;
    font-size: 0.8em;
    font-weight: 600;
    color: ' . $jsst_color4 . ';
    cursor: pointer;
}
/* The name takes whatever the label, the share box and the button leave, so
   the strip is filled edge to edge instead of ending in empty tint. */
.jsst-queue-views.jsst-queue-saveview .jsst-queue-viewname {
    flex: 1 1 220px;
    max-width: none;
}

/* ---------------------------------------------------------------------------
   The bulk bar and the tick on each row.

   The row inside the form is the flex line, not the form itself - the form
   also holds the hidden fields, and laying those out as flex items put gaps
   where nothing was drawn.

   jsst-bulk-selectall is the LABEL around the tick, not the tick: it was being
   given the checkbox\'s 18x18, which squashed "Select all" into a two-line
   sliver overlapping the select beside it. The admin desk has always read it
   as a label; this is the front end catching up with it rather than a new
   idea.
   --------------------------------------------------------------------------- */
.jsst-queue-bulk {
    display: block;
    width: 100%;
    box-sizing: border-box;
    margin: 12px 0;
    padding: 12px 14px;
    background: ' . $jsst_color3 . ';
    border: 1px solid ' . $jsst_color5 . ';
    border-radius: 8px;
}
.jsst-queue-bulk .jsst-bulk-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
}
.jsst-queue-bulk .jsst-bulk-row > * { min-width: 0; margin: 0 10px 0 0; }
.jsst-queue-bulk select.jsst-bulk-action { flex: 0 1 220px; }
.jsst-queue-bulk .jsst-bulk-value { flex: 0 1 200px; }
.jsst-queue-bulk input.jsst-bulk-reason { flex: 1 1 240px; min-width: 160px; }
.jsst-queue-bulk .js-ticket-btn { flex: 0 0 auto; }
.jsst-bulk-selectall {
    display: inline-flex;
    align-items: center;
    flex: 0 0 auto;
    min-width: 0;
    white-space: nowrap;
    font-size: 0.8em;
    color: ' . $jsst_color4 . ';
    cursor: pointer;
}
.jsst-bulk-count {
    flex: 0 0 auto;
    font-size: 0.75em;
    color: ' . $jsst_color1 . ';
}
/* ---------------------------------------------------------------------------
   The checkboxes.

   A native checkbox is drawn by the browser and ignores nearly everything CSS
   says about it, so the tick on each ticket was the operating system\'s own
   square sitting against a card whose every other control has a face of its
   own - and hard against the card\'s left border, because the ticket card
   carries "padding: 15px 0 25px" and has no left padding to sit inside.

   This page already had an answer to that: the "Assigned To Me" box in the
   search panel is appearance:none with a white face, a ' . $jsst_color5 . '
   border, a 2px radius and a white tick on ' . $jsst_color1 . ' when it is
   ticked. The three checkboxes 4.5 added now look like that one rather than
   like three different ideas.

   The picker is also given a real target. It was a bare 18px box, which is
   under half the 44px a finger is measured against, and it had nothing to
   show it was clickable at all.
   --------------------------------------------------------------------------- */
.jsst-bulk-pick {
    display: inline-flex;
    align-self: flex-start;
    flex: 0 0 auto;
    float: none;
    width: 26px;
    min-height: 30px;
    min-width: 0;
    margin: 0 0 0 0px;
    border-radius: 6px;
    cursor: pointer;
}
.jsst-bulk-pick:hover .jsst-bulk-ticket { border-color: ' . $jsst_color1 . '; }
.jsst-bulk-pick .jsst-bulk-ticket { margin: 0; }
div.js-ticket-wrapper .js-ticket-toparea {
    width: auto;
    min-width: 0;
    flex: 1 1 0;
    margin-top: 10px;
}
.jsst-bulk-ticket,
.jsst-bulk-selectall input[type="checkbox"],
.jsst-queue-share input[type="checkbox"],
.jsst-queue-column input[type="checkbox"] {
    position: relative;
    flex: 0 0 auto;
    box-sizing: border-box;
    width: 18px;
    height: 18px;
    margin: 0 8px 0 0;
    padding: 0;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-color: ' . $jsst_color7 . ';
    border: 1px solid ' . $jsst_color5 . ';
    /* Rounded, not square. Everything else on a ticket card is soft - a 16px
       card, a circular avatar, pill-shaped tags and status chips - and a hard
       2px square in the middle of it was the one thing that looked borrowed
       from another product. The inset shadow is the one the search fields on
       this page already carry. */
    border-radius: 5px;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
/* The one on a ticket row is larger than the ones inside a bar: it sits on its
   own beside a 110px avatar rather than next to its own label, and at 18px it
   read as a stray mark rather than a control. */
.jsst-bulk-pick .jsst-bulk-ticket {
    width: 22px;
    height: 22px;
}
.jsst-bulk-pick .jsst-bulk-ticket:checked:after {
    background-size: 15px 15px;
}
.jsst-bulk-ticket:hover,
.jsst-bulk-selectall input[type="checkbox"]:hover,
.jsst-queue-share input[type="checkbox"]:hover,
.jsst-queue-column input[type="checkbox"]:hover:not(:disabled) {
    border-color: ' . $jsst_color1 . ';
}
.jsst-bulk-ticket:checked,
.jsst-bulk-selectall input[type="checkbox"]:checked,
.jsst-queue-share input[type="checkbox"]:checked,
.jsst-queue-column input[type="checkbox"]:checked {
    background-color: ' . $jsst_color1 . ';
    border-color: ' . $jsst_color1 . ';
}
/* The reference and the subject cannot be turned off, so their boxes are
   ticked and disabled. Shown as ticked-but-quiet rather than as an ordinary
   tick, because a control that looks live and refuses to move is worse than
   one that says it is fixed. */
.jsst-queue-column input[type="checkbox"]:disabled {
    background-color: ' . $jsst_color5 . ';
    border-color: ' . $jsst_color5 . ';
    cursor: not-allowed;
}
.jsst-queue-column input[type="checkbox"]:disabled + span { opacity: 0.75; }
/* The tick. Drawn here rather than left to the browser, because appearance:
   none takes the browser\'s own away and a filled square with nothing in it
   is not a ticked checkbox. */
.jsst-bulk-ticket:checked:after,
.jsst-bulk-selectall input[type="checkbox"]:checked:after,
.jsst-queue-share input[type="checkbox"]:checked:after,
.jsst-queue-column input[type="checkbox"]:checked:after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 12px 12px;
}

/* A picked row says so. Progressive enhancement: a browser without :has()
   simply does not draw it, and the tick still reads on its own. */
.js-ticket-wrapper:has(.jsst-bulk-ticket:checked) {
    border-color: ' . $jsst_color1 . ';
    box-shadow: 0 0 0 1px ' . $jsst_color1 . ';
}

/* Where these controls are reached by keyboard, say so. */
.js-ticket-btn:focus-visible,
.jsst-queue-viewname:focus-visible,
.jsst-queue-share input:focus-visible,
.jsst-bulk-ticket:focus-visible,
.jsst-bulk-selectall input:focus-visible,
.jsst-queue-bulk select.inputbox:focus-visible,
.jsst-queue-bulk input[type="text"].inputbox:focus-visible {
    outline: 2px solid ' . $jsst_color1 . ';
    outline-offset: 2px;
}

/* Gap where it is supported, with the margins above as the fallback - Chrome
   before 84 ignores gap on flex and would otherwise pack these together. */
@supports (gap: 1px) {
    .jsst-queue-views, .jsst-queue-bulk .jsst-bulk-row { gap: 10px; }
    .jsst-queue-views > *, .jsst-queue-bulk .jsst-bulk-row > * { margin: 0; }
    div.js-ticket-top-cirlce-count-wrp { gap: 10px; }
    div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link { margin: 0; }
}

/* Three tiles across a tablet. Six at 140px each would fit, but a 108px ring
   in a 140px tile leaves no room for a label like "Waiting on Customer". */
@media (max-width: 991px) {
    div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link { flex: 1 1 30%; }
}

/* On a phone every one of these becomes its own row: a 240px input and a
   button side by side inside a 320px screen is two half-controls. */
@media (max-width: 768px) {
    div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link { flex: 1 1 45%; }
    .jsst-queue-views, .jsst-queue-bulk { display: block; padding: 12px; }
    .jsst-queue-bulk .jsst-bulk-row { display: block; }
    .jsst-queue-views > *,
    .jsst-queue-bulk .jsst-bulk-row > * { display: flex; width: 100%; margin: 0 0 10px; }
    /* Everything in these bars becomes full width on a phone except the label
       that is only there for a screen reader. Given the full width it is a
       597px box hanging off the side of a 430px screen, and the whole page
       scrolls sideways to reach text nobody can see. */
    .jsst-queue-views > .js-ticket-screen-reader-text,
    .jsst-queue-bulk .jsst-bulk-row > .js-ticket-screen-reader-text {
        display: block;
        width: 1px;
        margin: -1px;
    }
    .jsst-queue-viewname,
    select.jsst-queue-view,
    .jsst-queue-bulk select.inputbox,
    .jsst-queue-bulk input[type="text"].inputbox { max-width: 100%; width: 100%; min-width: 0; }
    /* The label above its select rather than beside it, and the pair on its own
       line under the buttons - there is no empty right-hand half to park it in
       on a phone. */
    .jsst-queue-viewpick {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        margin-left: 0;
    }
    .jsst-queue-viewpick > * { width: 100%; }
    .js-ticket-btn { width: 100%; }
    .jsst-queue-view-delete { justify-content: center; }
    .jsst-bulk-count:empty { display: none; }
}

/* The narrowest phones keep two tiles across rather than one: a single tile
   per row turns six tabs into a page of scrolling. */
@media (max-width: 480px) {
    div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link { flex: 1 1 45%; }
    div.js-ticket-top-cirlce-count-wrp span.js-ticket-circle-count-text { font-size: 0.62em; }
}
';

/* The same rules mirrored for right-to-left, which is how this plugin handles
   direction everywhere: the physical margins above are the ones that have to
   swap, along with the side the select draws its arrow on. (Roadmap 4.0-UX-06) */
if (is_rtl()) {
    $jsst_jssupportticket_css .= '
    .jsst-queue-views > *,
    .jsst-queue-bulk .jsst-bulk-row > * { margin: 0 0 0 10px; }
    .jsst-queue-share input[type="checkbox"],
    .jsst-bulk-selectall input[type="checkbox"],
    .jsst-queue-column input[type="checkbox"],
    .jsst-bulk-ticket { margin: 0 0 0 8px; }
    .jsst-bulk-pick { float: none; margin: 0 10px 0 0; }
    .jsst-bulk-pick .jsst-bulk-ticket { margin: 0; }
    .jsst-queue-bulk select.inputbox {
        background-position: left 15px center;
        padding-right: 15px;
        padding-left: 45px;
    }
    @supports (gap: 1px) {
        .jsst-queue-views > *, .jsst-queue-bulk .jsst-bulk-row > * { margin: 0; }
        div.js-ticket-top-cirlce-count-wrp > div.js-myticket-link { margin: 0; }
    }
    @media (max-width: 768px) {
        .jsst-queue-views > *,
        .jsst-queue-bulk .jsst-bulk-row > * { margin: 0 0 10px; }
    }
    ';
}


wp_add_inline_style('jssupportticket-main-css', $jsst_jssupportticket_css);


?>
