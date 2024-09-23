<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
JSSTmessage::getMessage();
wp_enqueue_script('jquery-ui-tabs');
$jssupportticket_js ='
    jQuery(document).ready(function ($) {
        jQuery(".tabs").tabs();
    });
';
wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_html(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Cron Job URLs','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt="<?php echo esc_html(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_html(__('Help','js-support-ticket')); ?>">
                        <img alt="<?php echo esc_html(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Cron Job URLs', 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="">
            <!-- ticket via email cron -->
            <div id="cp_wraper">
                <?php $array = array('even', 'odd');
                $k = 0; ?>
                <div id="tabs" class="tabs">
                    <ul>
                        <li><a title="<?php echo esc_html(__('Web Cron Job','js-support-ticket')); ?>" class="selected" data-css="controlpanel" href="#webcrown"><?php echo esc_html(__('Web Cron Job','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('Wget','js-support-ticket')); ?>"  data-css="controlpanel" href="#wget"><?php echo esc_html(__('Wget','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('Curl','js-support-ticket')); ?>"  data-css="controlpanel" href="#curl"><?php echo esc_html(__('Curl','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('PHP Script','js-support-ticket')); ?>"  data-css="controlpanel" href="#phpscript"><?php echo esc_html(__('PHP Script','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('URL','js-support-ticket')); ?>"  data-css="controlpanel" href="#url"><?php echo esc_html(__('URL','js-support-ticket')); ?></a></li>
                    </ul>
                    <div class="tabInner">
                    <div id="webcrown">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Configuration of a backup job with webcron org','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Name of cron job','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right"><?php echo esc_html(__('Ticket via email','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Timeout','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right"><?php echo esc_html(__('180 secs if the does not completely increase it most sites will work with a setting of 180 600','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('URL you want to execute','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid()))); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Login','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(__('Leave this blank','js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Password','js-support-ticket')); ?></span>
                                <span class="crown_text_right"><?php echo esc_html(__('Leave this blank','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Execution time','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(__('That the grid below the other options select when and how','js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Alerts','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                <?php echo esc_html(__('If you have already set up alerts methods in webcron org interface we recommend choosing an alert','js-support-ticket')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="wget">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Cron scheduling using wget','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                <?php echo 'wget --max-redirect=10000 "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid()))) .'" -O - 1>/dev/null 2>/dev/null '; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="curl">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Cron scheduling using Curl','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                <?php echo 'curl "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid()))).'"<br>' . esc_html(__('OR','js-support-ticket')) . '<br>'; ?>
                                <?php echo 'curl -L --max-redirs 1000 -v "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid()))).'" 1>/dev/null 2>/dev/null '; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="phpscript">
                        <div id="cron_job">
                            <span class="crown_text">
                                    <?php echo esc_html(__('Custom PHP script to run the cron job','js-support-ticket')); ?>
                            </span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                    <?php
                                    echo wp_kses('  $curl_handle=curl_init();<br>
                                                curl_setopt($curl_handle, CURLOPT_URL, \'' . jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid())).'\');<br>
                                                curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);<br>
                                                curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);<br>
                                                curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);<br>
                                                $buffer = curl_exec($curl_handle);<br>
                                                curl_close($curl_handle);<br>
                                                if (empty($buffer))<br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;echo "' . esc_html(__('Sorry the cron job didnot work','js-support-ticket')) . '";<br>
                                                else<br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;echo esc_attr($buffer);<br>
                                                ', JSST_ALLOWED_TAGS);
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="url">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('URL for use with your won scripts and third party','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth"><?php echo esc_html(jssupportticket::makeUrl(array('jsstcron'=>'ticketviaemail','jsstpageid'=>jssupportticket::getPageid()))); ?></span>
                            </div>
                        </div>
                    </div>
                    <div id="cron_job">
                        <span class="cron_job_help_txt"><?php echo esc_html(__('Recommended run script hourly','js-support-ticket')); ?></span>
                    </div>
                    </div>
                </div>
            </div>
            <!-- update ticket status cron -->
            <div id="cp_wraper">
                <?php $array = array('even', 'odd');
                $k = 0; ?>
                <div id="tabs" class="tabs">
                    <ul>
                        <li><a title="<?php echo esc_html(__('Web Cron Job','js-support-ticket')); ?>" class="selected" data-css="controlpanel" href="#webcrown"><?php echo esc_html(__('Web Cron Job','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('Wget','js-support-ticket')); ?>"  data-css="controlpanel" href="#wget"><?php echo esc_html(__('Wget','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('Curl','js-support-ticket')); ?>"  data-css="controlpanel" href="#curl"><?php echo esc_html(__('Curl','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('PHP Script','js-support-ticket')); ?>"  data-css="controlpanel" href="#phpscript"><?php echo esc_html(__('PHP Script','js-support-ticket')); ?></a></li>
                        <li><a title="<?php echo esc_html(__('URL','js-support-ticket')); ?>"  data-css="controlpanel" href="#url"><?php echo esc_html(__('URL','js-support-ticket')); ?></a></li>
                    </ul>
                    <div class="tabInner">
                    <div id="webcrown">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Configuration of a backup job with webcron org','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Name of cron job','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right"><?php echo esc_html(__('Update ticket status','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Timeout','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right"><?php echo esc_html(__('180 secs if the does not completely increase it most sites will work with a setting of 180 600','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('URL you want to execute','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid()))); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Login','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(__('Leave this blank','js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Password','js-support-ticket')); ?></span>
                                <span class="crown_text_right"><?php echo esc_html(__('Leave this blank','js-support-ticket')); ?></span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left">
                                    <?php echo esc_html(__('Execution time','js-support-ticket')); ?>
                                </span>
                                <span class="crown_text_right">
                                    <?php echo esc_html(__('That the grid below the other options select when and how','js-support-ticket')); ?>
                                </span>
                            </div>
                            <div id="cron_job_detail_wrapper" class="<?php echo esc_attr($array[$k]);$k = 1 - $k; ?>">
                                <span class="crown_text_left"><?php echo esc_html(__('Alerts','js-support-ticket')); ?></span>
                                <span class="crown_text_right">
                                <?php echo esc_html(__('If you have already set up alerts methods in webcron org interface we recommend choosing an alert','js-support-ticket')); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="wget">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Cron scheduling using wget','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                <?php echo 'wget --max-redirect=10000 "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid()))) .'" -O - 1>/dev/null 2>/dev/null '; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="curl">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('Cron scheduling using Curl','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                <?php echo 'curl "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid()))).'"<br>' . esc_html(__('OR','js-support-ticket')) . '<br>'; ?>
                                <?php echo 'curl -L --max-redirs 1000 -v "' . esc_html(jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid()))).'" 1>/dev/null 2>/dev/null '; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="phpscript">
                        <div id="cron_job">
                            <span class="crown_text">
                                    <?php echo esc_html(__('Custom PHP script to run the cron job','js-support-ticket')); ?>
                            </span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth">
                                    <?php
                                    echo wp_kses('  $curl_handle=curl_init();<br>
                                                curl_setopt($curl_handle, CURLOPT_URL, \'' . jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid())).'\');<br>
                                                curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);<br>
                                                curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);<br>
                                                curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);<br>
                                                $buffer = curl_exec($curl_handle);<br>
                                                curl_close($curl_handle);<br>
                                                if (empty($buffer))<br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;echo "' . esc_html(__('Sorry the cron job didnot work','js-support-ticket')) . '";<br>
                                                else<br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;echo esc_attr($buffer);<br>
                                                ', JSST_ALLOWED_TAGS);
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div id="url">
                        <div id="cron_job">
                            <span class="crown_text"><?php echo esc_html(__('URL for use with your won scripts and third party','js-support-ticket')); ?></span>
                            <div id="cron_job_detail_wrapper" class="even">
                                <span class="crown_text_right fullwidth"><?php echo esc_html(jssupportticket::makeUrl(array('jsstcron'=>'updateticketstatus','jsstpageid'=>jssupportticket::getPageid()))); ?></span>
                            </div>
                        </div>
                    </div>
                    <div id="cron_job">
                        <span class="cron_job_help_txt"><?php echo esc_html(__('Recommended run script daily','js-support-ticket')); ?></span>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
