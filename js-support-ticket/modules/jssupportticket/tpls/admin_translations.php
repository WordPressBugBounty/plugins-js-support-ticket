<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

/* Note
* WP auto translate it from it file, no need to add js-support-ticket as text domain
*/
?>
<div id="jssupportticketadmin-wrapper">
    <?php JSSTmessage::getMessage(); ?>

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
                        <li><?php echo esc_html(__('Translations')); // (wp auto translate it) ?></li>
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
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Translations')); ?></h1>
            <a target="blank" href="https://www.youtube.com/watch?v=Nnu2iJQ99Tk" class="jsstadmin-add-link black-bg button js-cp-video-popup" title="<?php echo esc_html(__('Watch Video', 'js-support-ticket')); ?>">
                <img alt="<?php echo esc_html(__('arrow','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/play-btn.png"/>
                <?php echo esc_html(__('Watch Video','js-support-ticket')); ?>
            </a>
        </div>
        <div id="jsstadmin-data-wrp" class="p0">
            <div id="black_wrapper_translation"></div>
            <div id="jstran_loading">
                <img alt="<?php echo esc_html(__('spinning wheel','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/spinning-wheel.gif" />
            </div>

            <div id="js-language-wrapper">
                <div class="jstopheading"><?php echo esc_html(__('Get')).' JS Help Desk '. esc_html(__('Translations')); ?></div>
                <div id="gettranslation" class="gettranslation"><img alt="<?php echo esc_html(__('Download')); ?>" style="width:18px; height:auto;" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/download-icon.png" /><?php echo esc_html(__('Get')).' '. esc_html(__('Translations')); ?></div>
                <div id="js_ddl">
                    <span class="title"><?php echo esc_html(__('Select')).' '. esc_html(__('Translation')); ?>:</span>
                    <span class="combo" id="js_combo"></span>
                    <span class="button" id="jsdownloadbutton"><img alt="<?php echo esc_html(__('Download')); ?>" style="width:14px; height:auto;" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/download-icon.png" /><?php echo esc_html(__('Download')); ?></span>
                    <div id="jscodeinputbox" class="js-some-disc"></div>
                    <div class="js-some-disc"><img alt="<?php echo esc_html(__('info','js-support-ticket')); ?>" style="width:18px; height:auto;" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/info-icon.png" /><?php echo esc_html(__('When WordPress language change to fr, JS Help Desk language will auto change to fr','js-support-ticket')); ?></div>
                </div>
                <div id="js-emessage-wrapper">
                    <img alt="<?php echo esc_html(__('c error','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/c_error.png" />
                    <div id="jslang_em_text"></div>
                </div>
                <div id="js-emessage-wrapper_ok">
                    <img alt="<?php echo esc_html(__('saved','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/saved.png" />
                    <div id="jslang_em_text_ok"></div>
                </div>
            </div>
            <div id="js-lang-toserver">
                <div class="col"><a class="anc one" href="https://www.transifex.com/joom-sky/js-support-ticket" target="_blank" title="<?php echo esc_html(__('Contribute In Translation','js-support-ticket')); ?>"><img alt="<?php echo esc_html(__('translate','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/translation-icon.png" /><?php echo esc_html(__('Contribute In Translation','js-support-ticket')); ?></a></div>
                <div class="col"><a class="anc two" href="http://www.joomsky.com/translations.html" target="_blank" title="<?php echo esc_html(__('Manual Download','js-support-ticket')); ?>"><img alt="<?php echo esc_html(__('Manual Download','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/manual-download.png" /><?php echo esc_html(__('Manual Download','js-support-ticket')); ?></a></div>
            </div>
        </div>
</div>

<?php
$jssupportticket_js ="
    var ajaxurl = '".esc_url(admin_url('admin-ajax.php'))."';
    jQuery(document).ready(function(){
        jQuery('#gettranslation').click(function(){
            jsShowLoading();
            jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getListTranslations', '_wpnonce':'".esc_attr(wp_create_nonce('get-list-translations'))."'}, function (data) {
                if (data) {
                    console.log(data);
                    jsHideLoading();
                    data = JSON.parse(data);
                    if(data['error']){
                        jQuery('#js-emessage-wrapper div').html(data['error']);
                        jQuery('#js-emessage-wrapper').show();
                    }else{
                        jQuery('#js-emessage-wrapper').hide();
                        jQuery('#gettranslation').hide();
                        jQuery('div#js_ddl').show();
                        jQuery('span#js_combo').html(jsstDecodeHTML(data['data']));
                    }
                }
            });
        });

        jQuery(document).on('change', 'select#translations' ,function() {
            var lang_name = jQuery( this ).val();
            if(lang_name != ''){
                jQuery('#js-emessage-wrapper_ok').hide();
                jsShowLoading();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'validateandshowdownloadfilename',langname:lang_name, '_wpnonce':'".esc_attr(wp_create_nonce('validate-and-show-download-filename'))."'}, function (data) {
                    console.log(data);
                    if (data) {
                        jsHideLoading();
                        data = JSON.parse(data);
                        if(data['error']){
                            jQuery('#js-emessage-wrapper div').html(data['error']);
                            jQuery('#js-emessage-wrapper').show();
                            jQuery('#jscodeinputbox').slideUp('400' , 'swing' , function(){
                                jQuery('input#languagecode').val('');
                            });
                        }else{
                            jQuery('#js-emessage-wrapper').hide();
                            jQuery('#jscodeinputbox').html(data['path']+': '+jsstDecodeHTML(data['input']));
                            jQuery('#jscodeinputbox').slideDown();
                        }
                    }
                });
            }
        });

        jQuery('#jsdownloadbutton').click(function(){
            jQuery('#js-emessage-wrapper_ok').hide();
            var lang_name = jQuery('#translations').val();
            var file_name = jQuery('#languagecode').val();
            if(lang_name != '' && file_name != ''){
                jsShowLoading();
                jQuery.post(ajaxurl, {action: 'jsticket_ajax', jstmod: 'jssupportticket', task: 'getlanguagetranslation',langname:lang_name , filename: file_name,langname:lang_name , filename: file_name, '_wpnonce':'". esc_attr(wp_create_nonce('get-language-translation'))."'}, function (data) {
                    if (data) {
                        console.log(data);
                        jsHideLoading();
                        data = JSON.parse(data);
                        if(data['error']){
                            jQuery('#js-emessage-wrapper div').html(data['error']);
                            jQuery('#js-emessage-wrapper').show();
                        }else{
                            jQuery('#js-emessage-wrapper').hide();
                            jQuery('#js-emessage-wrapper_ok div').html(data['data']);
                            jQuery('#js-emessage-wrapper_ok').slideDown();
                        }
                    }
                });
            }
        });
    });

    function jsShowLoading(){
        jQuery('div#black_wrapper_translation').show();
        jQuery('div#jstran_loading').show();
    }

    function jsHideLoading(){
        jQuery('div#black_wrapper_translation').hide();
        jQuery('div#jstran_loading').hide();
    }
    ";
    wp_add_inline_script('js-support-ticket-main-js',$jssupportticket_js);
?>
</div>
</div>
