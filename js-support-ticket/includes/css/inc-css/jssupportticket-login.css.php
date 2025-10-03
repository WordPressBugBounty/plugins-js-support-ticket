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
/* Login Page Modernized */
div.js-ticket-login-wrapper {
    float: left;
    width: 100%;
    margin: 0 !important;
}

div.js-ticket-login-wrapper div.js-ticket-login {
    float: left;
    width: 100%;
    margin-bottom:30px;
}

/* Modern Form Styling */
form#loginform-custom {
    display: flex;
    flex-wrap: wrap;
    gap: 25px; /* Modern spacing */
    width: 100%;
    padding: 40px;
    margin: 0;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    background:#fff;
    border-radius:20px;
}

/* Make form paragraphs flex items */
form#loginform-custom p {
    margin: 0;
    padding: 0;
    position: relative; /* For error message positioning */
}

/* Two-column layout for username and password */
form#loginform-custom p.login-username,
form#loginform-custom p.login-password {
    flex: 1 1 calc(50% - 12.5px);
}

/* Full-width for remember and submit */
form#loginform-custom p.login-remember,
form#loginform-custom p.login-submit {
    flex: 1 1 100%;
}

/* Field Labels */
form#loginform-custom p.login-username label,
form#loginform-custom p.login-password label,
form#loginform-custom p.login-remember label {
    display: block;
    width: 100%;
    margin-bottom: 10px;
    font-weight: 600;
}

/* Unified Input Fields */
form#loginform-custom p.login-username input#user_login,
form#loginform-custom p.login-password input#user_pass {
    width: 100%;
    border-radius: 10px;
    padding: 12px 18px;
    line-height: normal;
    height: auto;
    min-height: 52px;
    border-width: 1px;
    border-style: solid;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

/* Focus effect for inputs */
form#loginform-custom input#user_login:focus,
form#loginform-custom input#user_pass:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Remember Me Checkbox */
form#loginform-custom p.login-remember {
    margin-top: 0 !important;
}

form#loginform-custom p.login-remember label {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

form#loginform-custom p.login-remember label input#rememberme {
    vertical-align: middle;
    margin-right: 10px;
    width: 18px;
    height: 18px;
}
form#loginform-custom p.login-remember label input#rememberme {
        display: inline-block !important;
        margin: 0 12px 0 0 !important; /* More spacing for checkbox */
        transform: scale(1.3); /* Larger checkbox */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 14px; /* Larger size */
        height: 14px;
        border: 1px solid ' . $color5 . '; /* Border with secondary color */
        border-radius: 4px; /* Slightly more rounded */
        background-color: #fff;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
        transition: all 0.2s ease;
        opacity: 1;
    }
    form#loginform-custom p.login-remember label input#rememberme:checked {
        background-color: ' . $color1 . '; /* Primary color fill when checked */
        border-color: ' . $color1 . ';
    }
    form#loginform-custom p.login-remember label input#rememberme:after {
        content: "";
        position: absolute;
        top: 0px; /* Adjust checkmark position */
        left: 0px; /* Adjust checkmark position */
        width: 12px; /* Larger checkmark */
        height: 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 20 20\' fill=\'%23ffffff\'%3E%3Cpath fill-rule=\'evenodd\' d=\'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z\' clip-rule=\'evenodd\' /%3E%3C/svg%3E"); /* White checkmark SVG */
        background-size: contain;
        background-repeat: no-repeat;
    }

/* Submit Button Container */
form#loginform-custom p.login-submit {
    width: 100%;
    text-align: center;
    padding: 30px 0 10px 0;
    margin-top: 15px !important;
    border-top-width: 1px;
    border-top-style: solid;
}

/* Modern Submit Button */
form#loginform-custom p.login-submit input#wp-submit {
    padding: 16px 30px;
    min-width: 160px;
    border-radius: 10px;
    line-height: initial;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

/* Modern Error Styling */
span.jsst-help-block {
    display: block !important;
    font-size: 14px;
    margin-top: 10px;
    padding: 5px 15px;
    border-width: 1px;
    border-style: solid;
    border-radius: 8px;
    font-weight: 600;
    box-sizing: border-box;
    width: 100%;
    position: relative;
    z-index: 2;
    bottom: 15px; /* Adjusted position */
    color: #e74c3c;
    background-color: #fff0f0;
    border-color: #e74c3c;
}

/* Responsive */
@media (max-width: 768px) {
    form#loginform-custom {
        padding: 25px;
    }
    form#loginform-custom p.login-username,
    form#loginform-custom p.login-password {
        flex: 1 1 100%; /* Single column on smaller screens */
    }
}

';
/*Code For Colors*/
$jssupportticket_css .= '
	/* Login Page Colors */
	form#loginform-custom p.login-username label,
	form#loginform-custom p.login-password label,
	form#loginform-custom p.login-remember label {
		color: ' . $color2 . ';
	}

	form#loginform-custom p.login-submit {
		border-top-color: ' . $color5 . ';
	}

	form#loginform-custom p.login-username input#user_login,
	form#loginform-custom p.login-password input#user_pass {
		background-color: #fcfcfc;
		border:1px solid ' . $color5 . ';
		color:' . $color4 . ';
	}

	form#loginform-custom p.login-submit input#wp-submit {
		background-color: ' . $color1 . ';
		color: ' . $color7 . ';
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color1, 1, 2)) . ', ' . hexdec(substr($color1, 3, 2)) . ', ' . hexdec(substr($color1, 5, 2)) . ', 0.4);
	}

	form#loginform-custom p.login-submit input#wp-submit:hover {
		background-color: ' . $color2 . ';
        transform: translateY(-3px);
        filter: brightness(1.1);
        box-shadow: 0 2px 10px rgba(' . hexdec(substr($color2, 1, 2)) . ', ' . hexdec(substr($color2, 3, 2)) . ', ' . hexdec(substr($color2, 5, 2)) . ', 0.5);
	}
	.js-ticket-login-wrapper a{color:' . $color1 . '!important;}
';


wp_add_inline_style('jssupportticket-main-css', $jssupportticket_css);


?>
