<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');

$jsst_jssupportticket_js ="
    function resetFrom() {
        document.getElementById('email').value = '';
        document.getElementById('jssupportticketform').submit();
    }
";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('System Emails', 'js-support-ticket'),
            'count'   => isset(jssupportticket::$jsst_data['total']) ? (int) jssupportticket::$jsst_data['total'] : 0,
            'actions' => array(
                array('text' => __('Watch Video', 'js-support-ticket'), 'url' => 'https://www.youtube.com/watch?v=4_wrnx8ka0E', 'style' => 'ghost', 'target' => '_blank'),
                array('text' => __('Add Email', 'js-support-ticket'), 'url' => admin_url('admin.php?page=email&jstlay=addemail'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <p class="jsst-lede"><?php echo esc_html(__('System email used for sending email', 'js-support-ticket')); ?></p>
            <form class="jsst-filterbar" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url("admin.php?page=email&jstlay=emails"),"emails")); ?>">
                <div class="jsst-search">
                    <span class="jsst-search-icon" aria-hidden="true"></span>
                    <?php echo wp_kses(JSSTformfield::text('email', jssupportticket::$jsst_data['filter']['email'], array('placeholder' => esc_html(__('Search emails', 'js-support-ticket')),'class' => 'jsst-search-input')), JSST_ALLOWED_TAGS); ?>
                </div>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'jsst-btn jsst-btn-primary')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button('reset', esc_html(__('Reset', 'js-support-ticket')), array('class' => 'jsst-btn', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
            </form>
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Email Address', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Auto Response', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Created', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_email) {
                            $jsst_editurl = admin_url('admin.php?page=email&jstlay=addemail&jssupportticketid=' . $jsst_email->id);
                            ?>
                            <tr>
                                <th scope="row" class="jsst-col-name"><a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_email->email); ?></a></th>
                                <td class="jsst-col-fit">
                                    <?php if ($jsst_email->autoresponse == 1) { ?>
                                        <span class="jsst-mark jsst-mark-on"><?php echo esc_html(__('On', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-mark"><?php echo esc_html(__('Off', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="jsst-col-fit"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_email->created))); ?></td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=email&task=deleteemail&action=jstask&emailid=' .esc_attr($jsst_email->id),'delete-email-'.$jsst_email->id)); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php JSSTlayout::adminPager(jssupportticket::$jsst_data[1]); ?>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No system emails found.', 'js-support-ticket'),
                        __('A system email is an address the help desk sends from. Each department picks one.', 'js-support-ticket'),
                        __('Add email', 'js-support-ticket'),
                        admin_url('admin.php?page=email&jstlay=addemail')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
