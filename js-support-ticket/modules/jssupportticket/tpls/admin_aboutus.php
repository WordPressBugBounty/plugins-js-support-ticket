<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
     <?php JSSTlayout::adminPageHeader(array(
         'title' => __('About Us','js-support-ticket'),
     )); ?>
	</div>
</div>
