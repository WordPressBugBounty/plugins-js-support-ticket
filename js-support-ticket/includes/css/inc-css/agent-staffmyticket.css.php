<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly 
// if header is calling later
JSSTincluder::getJSModel('jssupportticket')->checkIfMainCssFileIsEnqued();
JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();

$color1 = jssupportticket::$_colors['color1'];
$color2 = jssupportticket::$_colors['color2'];
$color3 = jssupportticket::$_colors['color3'];
$color4 = jssupportticket::$_colors['color4'];
$color5 = jssupportticket::$_colors['color5'];
$color6 = jssupportticket::$_colors['color6'];
$color7 = jssupportticket::$_colors['color7'];
$color8 = jssupportticket::$_colors['color8'];
$color9 = jssupportticket::$_colors['color9'];

$jssupportticket_css = '';

/*Code for Css*/
$jssupportticket_css .= '

/* Top Circle Count Boxes */
div.js-ticket-top-cirlce-count-wrp{
    float: left;
    margin-bottom: 30px;
    padding: 15px 10px;
    border: 1px solid ' . $color5 . ';
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
    border: 1px solid ' . $color5 . ';
    border-radius: 10px; /* Rounded corners for individual links */
    transition: all 0.3s ease; /* Smooth transitions */
    background-color: ' . $color3 . ';
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
    border: 1px solid ' . $color5 . ';
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
    border-bottom: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
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
            border: 2px solid ' . $color5 . '; /* Light gray border */
            border-radius: 0.375rem; /* Rounded corners (md) */
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease-in-out;
            vertical-align: middle; /* Align with text */
            margin-right: 0.5rem; /* Space between checkbox and label */
        }
.js-filter-wrapper input[type="text"]::placeholder{color:' . $color4 . ';}
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
            border: 1px solid ' . $color5 . '; /* border-gray-300 */
        }

        /* Style the checkbox indicator when checked */
        .checkbox-item input[type="checkbox"]:checked ~ .checkbox-indicator {
            background-color: '. $color1 .'; /* bg-blue-500 */
            border-color: '. $color1 .';
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
            border: 1px solid ' . $color5 . ';
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
            color:' . $color4 . ';
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp{
            width:100%;
            height: auto; /* Auto height for better responsiveness */
            line-height: normal;
            background-color: #ffffff;
            color: ' . $color4 . ';
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
        border: 1px solid ' . $color5 . '; /* Border with secondary color */
        border-radius: 2px; /* Slightly more rounded */
        background-color: #fff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp .js-ticket-check-box input[type="checkbox"]:checked, div.jsst-main-up-wrapper .js-ticket-assigned-tome input[type="checkbox"]:checked {
        background-color: ' . $color1 . '; /* Primary color fill when checked */
        border-color: ' . $color1 . ';
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
    background: ' . $color3 . '; /* Light background for form fields */
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
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea{
    border-radius: 5px; /* Slightly rounded input fields */
    width:100%;
    padding: 12px 15px;
    height: auto; /* Auto height for better responsiveness */
    line-height: normal;
    background-color: #ffffff;
    border: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
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
    border: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
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
    border-color: ' . $color1 . ';
    box-shadow: 0 0 0 3px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.2); /* Focus ring */
    outline: none;
}

div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input#jsst-datestart,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input#jsst-dateend{
    background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($color4, 1) . '\'%3E%3Cpath d=\'M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z\'/%3E%3C/svg%3E"); /* Modern calendar icon */
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
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid,
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select{
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($color4, 1) . '\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E") no-repeat right 15px center / 20px; /* Modern dropdown arrow */
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
    background-color: ' . $color1 . ';
    color: ' . $color7 . ';
    border: 1px solid ' . $color1 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: inherit;
}
.js_ticketattachment:hover .button {
    color: ' . $color7 . ';
    background-color: ' . $color2 . ';
    border-color: ' . $color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}
.js_ticketattachment {
    border: 1px solid ' . $color5 . ';
    text-align: center;
    padding: 7px 0px;
}
.js_ticketattachment {
    border: 1px solid ' . $color5 . ';
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
    color: ' . $color4 . ';
    border: 1px solid ' . $color5 . ';
    background: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp .js-search-filter-btn:hover {
    border-color: ' . $color1 . ';
    background-color: #f0f2f5;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn{
    background-color: ' . $color1 . ';
    color: ' . $color7 . ';
    border: 1px solid ' . $color1 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-search-btn:hover{
    background-color: ' . $color2 . ';
    border-color: ' . $color2 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn{
    background-color: #f5f2f5; /* A neutral reset button */
    color: ' . $color4 . ';
    border: 1px solid ' . $color5 . ';
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-button-wrp input.js-ticket-reset-btn:hover{
    background-color: '. $color2 .';
    color: ' . $color7 . ';
    border-color: '. $color5 .';
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
    background-color: ' . $color1 . '; /* Match primary button color */
    color: ' . $color7 . ';
    border-radius: 8px;
    margin-top: 10px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}
div#js-filter-wrapper-toggle-plus:hover,
div#js-filter-wrapper-toggle-minus:hover{
    background-color: ' . $color2 . ';
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
            align-items: center; /* Vertically align image and text */
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 31, 63, 0.08);
            padding: 15px;
            margin: 0 auto;
           margin-bottom: 15px;
            border: 1px solid '. $color5 .';
            width: 100%;
            transition: all 0.3s ease-in-out;
            padding-bottom: 25px;
            border: 1px solid ' . $color5 . ';
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
        .js-ticket-wrapper:hover {border:1px solid' . $color1 . ';}
        div.js-ticket-body-data-elipses a:hover {color:' . $color1 . ';}
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
            padding-left: 12px;
            padding-right: 0px;
            border-left: 1px solid ' . $color5 . ';
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
            color: '. $color4 .';
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
    background: ' . $color2 . '; /* Darker background for sorting */
    color: ' . $color7 . ';
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
    color: ' . $color7 . ';
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
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23' . substr($color4, 1) . '\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E") no-repeat right 15px center / 20px;
    border: 1px solid ' . $color5 . ';
    color: ' . $color4 . ';
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    margin-bottom:0;
    line-height: 1.2;
    margin-bottom:0;
    line-height: 1.2;
}
div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select:focus {
    border-color: ' . $color1 . ';
    box-shadow: 0 0 0 3px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.2);
    outline: none;
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn {
    float: none;
    padding: 10px 12px;
    line-height: normal;
    height: 45px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid ' . $color5 . ';
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn:hover {
    background-color: #f0f2f5;
    border-color: ' . $color1 . ';
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn img {
    height: 20px;
    width: 29px;
}

select ::-ms-expand {display:none !important;}
select{-webkit-appearance:none !important;}
div.js-ticket-sorting{border: 1px solid ' . $color5 . ';width:calc(100% - 40px);margin-left:20px;margin-right:20px;}

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
        border-top: 1px solid ' . $color5 . ';
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
        border-top: 1px solid ' . $color5 . ';
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
$jssupportticket_css .= '
/* My Tickets */
    /* Top Circle Count Box*/
        div.js-ticket-top-cirlce-count-wrp {border:1px solid' . $color5 . ';}
        div.js-myticket-link a.js-myticket-link{border:1px solid' . $color5 . ';}
        div.js-myticket-link a.js-myticket-link:hover{box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); background-color: #f8f9fa; transform: translateY(-3px);}
        .js-ticket-answer{background-color:#F7B731;} /* Vibrant Orange */
        .js-ticket-close{background-color:#E74C3C;} /* Strong Red */
        .js-ticket-allticket{background-color:#3498DB;} /* Bright Blue */
        .js-ticket-open{background-color:#2ECC71;} /* Emerald Green */
        .js-ticket-overdue{background-color:#E67E22;} /* Carrot Orange */
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-blue{color:#F7B731;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-red{color:#E74C3C;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-orange{color:#3498DB;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-green{color:#2ECC71;}
        div.js-myticket-link a.js-myticket-link span.js-ticket-circle-count-text.js-ticket-pink{color:#E67E22;}
        div.js-myticket-link a.js-myticket-link div.progress::after {border: 25px solid #e0e0e0;} /* Lighter grey for progress background */
        div.js-myticket-link a.js-myticket-link.js-ticket-green.active{border-color:#2ECC71; box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-blue.active{border-color:#F7B731; box-shadow: 0 4px 10px rgba(247, 183, 49, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-red.active{border-color:#E74C3C; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-orange.active{border-color:#3498DB; box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-pink.active{border-color:#E67E22; box-shadow: 0 4px 10px rgba(230, 126, 34, 0.3);}
        div.js-myticket-link a.js-myticket-link.js-ticket-green:hover{border-color:#2ECC71;}
        div.js-myticket-link a.js-myticket-link.js-ticket-blue:hover{border-color:#F7B731;}
        div.js-myticket-link a.js-myticket-link.js-ticket-red:hover{border-color:#E74C3C;}
        div.js-myticket-link a.js-myticket-link.js-ticket-orange:hover{border-color:#3498DB;}
        div.js-myticket-link a.js-myticket-link.js-ticket-pink:hover{border-color:#E67E22;}
        div.js-myticket-link a.js-myticket-link.js-ticket-brown.active{border-color:#6C7A89; box-shadow: 0 4px 10px rgba(108, 122, 137, 0.3);} /* A neutral brown/grey for All Tickets */
        div.js-myticket-link a.js-myticket-link.js-ticket-brown:hover{border-color:#6C7A89;}

    /* Search Ticket Form*/
        div.js-ticket-search-wrp{border:1px solid' . $color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-search-heading{background-color:#eef2f7;border-bottom:1px solid' . $color5 . '; color:' . $color4 . '}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper {background: #fcfdfe;}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-form-fields-wrp input.js-ticket-input-field{background-color:#fff;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div#js-filter-wrapper-toggle-ticketid input.js-ticket-input-field{background-color:#fff;border:1px solid' . $color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.js-ticket-input-field{background-color:#fff;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp .js-form-cust-ckb-fld-wrp{background-color:#fff;color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp input.inputbox{background-color:#fff;border:1px solid' . $color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-departmentid{background-color:#fff !important;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-helptopicid{background-color:#fff !important;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-priorityid{background-color:#fff !important;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-productid{background-color:#fff !important;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#jsst-status{background-color:#fff !important;border:1px solid' . $color5 . ';color: ' . $color4 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-field-wrp select#staffid{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value input.js-ticket-input-field{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-departmentid{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-helptopicid{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-priorityid{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-area div.js-filter-wrapper div.js-filter-value select#jsst-productid{background-color:#fff;border:1px solid' . $color5 . ';}
        div#js-filter-wrapper-toggle-plus{background-color:' . $color1 . ';}
        div#js-filter-wrapper-toggle-minus{background-color:' . $color1 . ';}
    /* Search Ticket Form*/
    /* My Tickets $ Staff My Tickets*/
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value select{background-color:#fff;border: 1px solid ' . $color5 . ';}
        div.js-ticket-search-wrp div.js-ticket-form-wrp form.js-filter-form div.js-filter-wrapper div.js-filter-value textarea{background-color:#fff;border: 1px solid ' . $color5 . ';}
        div.js-ticket-wrapper div.js-ticket-pic{min-width: 80px;height: 80px;max-width: fit-content;}
        div.js-ticket-wrapper div.js-ticket-pic img{width:80px;height:80px;}
        div.js-ticket-wrapper div.js-ticket-data .name span.js-ticket-value {color:' . $color4 . ';}
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-title{color:' . $color2 . ';}
        div.js-ticket-wrapper div.js-ticket-data span.js-ticket-value{color:' . $color4 . ';}
        div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-tit {color:' . $color2 . ';}
        div.js-ticket-wrapper div.js-ticket-data1 div.js-ticket-data-row .js-ticket-data-val {color:' . $color4 . ';}
        div.js-ticket-sorting {color: ' . $color7 . ';} /* Use primary color for sorting section */
        div.js-ticket-sorting span.js-ticket-sorting-link a{background:rgba(255,255,255,0.15);color:' . $color7 . ';}
        div.js-ticket-sorting span.js-ticket-sorting-link a.selected,
        div.js-ticket-sorting span.js-ticket-sorting-link a:hover{background:' . $color2 . ';}
        div.js-ticket-sorting-right div.js-ticket-sort select.js-ticket-sorting-select {background: #fff;color: ' . $color4 . ';border: 1px solid ' . $color5 . ';}
        div.js-ticket-sorting-right div.js-ticket-sort a.js-admin-sort-btn {background: #fff;}

    /* My Tickets $ Staff My Tickets*/
/* My Tickets */';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
