<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/* The desk screens share one themed stylesheet; this is the hook the includer
   already knows how to find. See desk-theme.php for what it emits and why it
   cannot live in style.css. (Roadmap 4.5-FE-02) */
require_once JSST_PLUGIN_PATH . 'includes/css/inc-css/desk-theme.php';
