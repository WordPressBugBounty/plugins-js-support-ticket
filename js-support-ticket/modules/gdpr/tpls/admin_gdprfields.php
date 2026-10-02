<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('GDPR Fields', 'js-support-ticket'),
            'count'   => !empty(jssupportticket::$jsst_data[0]) ? count(jssupportticket::$jsst_data[0]) : 0,
            'actions' => array(
                array('text' => __('Add GDPR Field', 'js-support-ticket'), 'url' => admin_url('admin.php?page=gdpr&jstlay=addgdprfield'), 'icon' => 'plus'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
            <?php if (!empty(jssupportticket::$jsst_data[0])) { ?>
                <div class="jsst-card">
                    <div class="jsst-table-wrap">
                    <table class="jsst-table">
                        <thead>
                        <tr>
                            <th class="jsst-col-name"><?php echo esc_html(__('Field', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Required', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Ordering', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-fit"><?php echo esc_html(__('Link', 'js-support-ticket')); ?></th>
                            <th class="jsst-col-act"><span class="screen-reader-text"><?php echo esc_html(__('Action', 'js-support-ticket')); ?></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (jssupportticket::$jsst_data[0] AS $jsst_field) {
                            $jsst_termsandconditions_text = '';
                            $jsst_termsandconditions_linktype = '';
                            $jsst_page_title_link = '';
                            if(isset($jsst_field->userfieldparams) && $jsst_field->userfieldparams != '' ){
                                $jsst_userfieldparams = json_decode($jsst_field->userfieldparams,true);
                                $jsst_termsandconditions_text = isset($jsst_userfieldparams['termsandconditions_text']) ? $jsst_userfieldparams['termsandconditions_text'] :'' ;
                                $jsst_termsandconditions_linktype = isset($jsst_userfieldparams['termsandconditions_linktype']) ? $jsst_userfieldparams['termsandconditions_linktype'] :'' ;
                                if($jsst_termsandconditions_linktype == 2){
                                    $jsst_page_title_link = get_the_title(isset($jsst_userfieldparams['termsandconditions_page']) ? $jsst_userfieldparams['termsandconditions_page'] : 0);
                                }else{
                                    $jsst_page_title_link = isset($jsst_userfieldparams['termsandconditions_link']) ? $jsst_userfieldparams['termsandconditions_link'] : '';
                                }
                            }
                            if ($jsst_termsandconditions_linktype == 2) {
                                $jsst_linktypetext = __('WordPress Page','js-support-ticket');
                            } elseif ($jsst_termsandconditions_linktype == 1) {
                                $jsst_linktypetext = __('Direct URL','js-support-ticket');
                            } else {
                                $jsst_linktypetext = __('None','js-support-ticket');
                            }
                            $jsst_editurl = admin_url('admin.php?page=gdpr&jstlay=addgdprfield&jssupportticketid=' . $jsst_field->id);
                            ?>
                            <tr>
                                <th scope="row" class="jsst-col-name">
                                    <a class="jsst-table-name" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html($jsst_field->fieldtitle); ?></a>
                                    <span class="jsst-table-sub"><?php echo esc_html($jsst_termsandconditions_text); ?></span>
                                </th>
                                <td class="jsst-col-fit">
                                    <?php if ($jsst_field->required == 1) { ?>
                                        <span class="jsst-mark jsst-mark-on"><?php echo esc_html(__('Required', 'js-support-ticket')); ?></span>
                                    <?php } else { ?>
                                        <span class="jsst-mark"><?php echo esc_html(__('Optional', 'js-support-ticket')); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="jsst-col-fit jsst-num"><?php echo esc_html($jsst_field->ordering); ?></td>
                                <td class="jsst-col-fit">
                                    <?php echo esc_html($jsst_linktypetext); ?>
                                    <?php if ($jsst_page_title_link != '') { ?>
                                        <span class="jsst-table-sub"><?php echo esc_html($jsst_page_title_link); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="jsst-col-act">
                                    <span class="jsst-rowactions">
                                        <a class="jsst-act" href="<?php echo esc_url($jsst_editurl); ?>"><?php echo esc_html(__('Edit','js-support-ticket')); ?></a>
                                        <a class="jsst-act jsst-act-danger" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=gdpr&task=deletegdpr&action=jstask&gdprid='.esc_attr($jsst_field->id),'delete-gdpr')); ?>"><?php echo esc_html(__('Delete','js-support-ticket')); ?></a>
                                    </span>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            <?php } else { ?>
                <div class="jsst-card">
                    <?php JSSTlayout::adminEmpty(
                        __('No GDPR fields.', 'js-support-ticket'),
                        __('A GDPR field is a consent box customers tick before they can open a ticket.', 'js-support-ticket'),
                        __('Add GDPR field', 'js-support-ticket'),
                        admin_url('admin.php?page=gdpr&jstlay=addgdprfield')
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
