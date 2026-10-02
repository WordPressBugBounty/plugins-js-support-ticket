<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php JSSTmessage::getMessage(); ?>
<div id="jsstadmin-wrapper" class="jsstadmin-add-on-page-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Addons List','js-support-ticket')); ?></li>
                    </ul>
                </div>
            </div>
            <div id="jsstadmin-wrapper-top-right">
                <div id="jsstadmin-config-btn">
                    <a title="<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url("admin.php?page=configuration")); ?>">
                        <img alt = "<?php echo esc_attr(__('Configuration','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/config.png" />
                    </a>
                </div>
                <div id="jsstadmin-config-btn" class="jssticketadmin-help-btn">
                    <a href="<?php echo esc_url(admin_url("admin.php?page=jssupportticket&jstlay=help")); ?>" title="<?php echo esc_attr(__('Help','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('Help','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/help.png" />
                    </a>
                </div>
                <div id="jsstadmin-vers-txt">
                    <?php echo esc_html(__("Version",'js-support-ticket')); ?>:
                    <span class="jsstadmin-ver"><?php echo esc_html(JSSTincluder::getJSModel('configuration')->getConfigValue('versioncode')); ?></span>
                </div>
            </div>
        </div>
        <div id="jsstadmin-head">
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__("Addons List", 'js-support-ticket')); ?></h1>
        </div>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n">
            <div class="jsstadmin-add-on-page-wrp">
                <div class="add-on-banner">
                    <img class="add-on-banner-left-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/left-image.png" alt = "<?php echo esc_attr(__('left image','js-support-ticket')); ?>"/>
                    <img class="add-on-banner-center-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/logo.png" alt = "<?php echo esc_attr(__('Logo','js-support-ticket')); ?>" />
                    <img class="add-on-banner-right-img" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/add-on-list/right-image.png" alt = "<?php echo esc_attr(__('right image','js-support-ticket')); ?>" />
                </div>
                <div class="add-on-page-cnt">
                    <div class="add-on-sec-header">
                        <h1 class="add-on-header-tit"><?php echo esc_html(__('Add-On’s For Help Desk','js-support-ticket')); ?></h1>
                        <div class="add-on-header-text"><?php echo esc_html(__('Get trusted WordPress add on’s. Guaranteed to work fast, safe to use, beautifully coded, packed with features and easy to use.','js-support-ticket')); ?></div>
                    </div>
                    <?php /* This used to advertise the bundle pack, which no
                             longer exists — the bundles are retired and every
                             paid plan contains every module. A call to action
                             pointing at something we have withdrawn is worse
                             than none at all, so it now says the thing that is
                             actually true. (Roadmap 4.5-PRO-03) */ ?>
                    <div class="add-on-msg">
                        <h3 class="add-on-msg-txt"><?php echo esc_html(__('Every one of these is included in every paid plan.','js-support-ticket')); ?></h3>
                        <a title="<?php echo esc_attr(__('See the plans','js-support-ticket')); ?>" href="https://jshelpdesk.com/pricing/" class="add-on-msg-btn"><i class="fa fa-cart"></i> <?php echo esc_html(__('see the plans','js-support-ticket')); ?></a>
                    </div>
                    <?php /* Nine tiles, from the one manifest. (Roadmap 6.5-ECO-01)
                             This was twenty-five hand-written tiles, each with its
                             own name, sentence and shop link, and every one of them
                             a product that no longer exists on its own - so the
                             screen whose whole job is to say what you can buy was
                             selling the wrong thing in the wrong words.

                             The names and the sentences are the manifest's, which
                             the dashboard, the add-on status page and the Plugins
                             screen also read, so a description edited once is
                             edited everywhere.

                             A bundle this desk already has says so instead of
                             offering to sell it. */ ?>
                    <div class="add-on-list">
                        <?php foreach (JSSTbundle::catalogue() as $jsst_file => $jsst_bundle) { ?>
                            <div class="add-on-item <?php echo esc_attr($jsst_bundle['slug']); ?>">
                                <img class="add-on-img" src="<?php echo esc_url(JSST_PLUGIN_URL . 'includes/images/add-on-list/' . $jsst_bundle['image']); ?>" alt="" />
                                <div class="add-on-name"><?php echo esc_html($jsst_bundle['title']); ?></div>

                                <div class="add-on-txt"><?php echo esc_html($jsst_bundle['description']); ?></div>
                                <?php if (!empty($jsst_bundle['active'])) { ?>
                                    <span class="add-on-btn add-on-btn-have"><?php echo esc_html(__('installed', 'js-support-ticket')); ?></span>
                                <?php } else { ?>
                                    <a title="<?php echo esc_attr(__('buy now', 'js-support-ticket')); ?>" href="<?php echo esc_url($jsst_bundle['url']); ?>" class="add-on-btn"><?php echo esc_html(__('buy now', 'js-support-ticket')); ?></a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <?php /* ---------------------------------------------------
                       The Basic, Standard and Professional bundles used to be
                       listed here, three columns of which features each one
                       unlocked. They are retired. (Roadmap 4.5-PRO-03)

                       They are not replaced by a shorter version of the same
                       idea: nothing is withheld from a cheaper paid plan any
                       more, so there is no feature column to draw. What goes in
                       their place is the honest comparison — what the free
                       plugin contains and what Pro adds — generated from the
                       manifests rather than typed, so it cannot claim a feature
                       we have not shipped or omit one we have.

                       Customers still holding a retired tier lose nothing:
                       JSSTplans maps every one of them onto Pro, which contains
                       every module all three of them ever did.
                       --------------------------------------------------- */ ?>
                    <?php /* The Free-and-Pro comparison, the plans and the
                       guarantees moved onto the JS Help Desk Pro screen, which
                       is where somebody asks what their licence covers. This
                       page is the catalogue - what each add-on does and where
                       to buy it - and two lists of the same modules on one
                       screen is one list too many.

                       "Publish this table" went with them and is not offered on
                       a customer's screen at all: a JSON feed of our own pricing
                       table is a tool for the shop that sells this product, not
                       for the desk that runs it. JSSTplans::feedUrl() and
                       export() are untouched, so anything already fetching the
                       feed keeps working. (Roadmap 4.5-MKT-02, 4.5-PRO-01) */ ?>
                    <div class="add-on-msg">
                        <h3 class="add-on-msg-txt"><?php echo esc_html(__('What your license covers, and what is installed, is on the License screen.','js-support-ticket')); ?></h3>
                        <a title="<?php echo esc_attr(__('License','js-support-ticket')); ?>" href="<?php echo esc_url(admin_url('admin.php?page=jssupportticket&jstlay=license')); ?>" class="add-on-msg-btn"><?php echo esc_html(__('License','js-support-ticket')); ?></a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
