<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class JSSTpremiumpluginController {

    function __construct() {
        self::handleRequest();
    }

    function handleRequest() {
        $jsst_module = "premiumplugin";
        if ($this->canAddLayout()) {
            $jsst_layout = JSSTrequest::getLayout('jstlay', null, 'step1');

            /* The old installer (steps 1-3) and the old Update Key screen are
               gone: both sent keys to jshelpdesk.com/setup/, which does not
               know a 5.0.0 licence. Their addresses are kept as redirects,
               ahead of the old key check below, so a bookmark or a support
               reply still lands on the screen that now does the job - and
               without first asking /setup/ about keys on the way there.
               (Roadmap 6.5-ECO-02) */
            if (in_array($jsst_layout, array('admin_step1', 'admin_step2', 'admin_step3'), true)) {
                JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket&jstlay=license'));
                return;
            }
            if ('admin_updatekey' === $jsst_layout) {
                JSSTincluder::refuse(admin_url('admin.php?page=jssupportticket&jstlay=license'));
                return;
            }

            switch ($jsst_layout) {
                case 'admin_addonfeatures':
                    break;
                case 'admin_missingaddon':
                    break;
                case 'missingaddon':
                    break;
                default:
                    /* The layout belongs to somebody else. This controller is
                       reached two ways: as `page=premiumplugin`, where an
                       unknown layout is a bad URL, and as the includer's
                       fallback for a module whose own controller is not
                       installed - `page=overdue&jstlay=sla` on a site with the
                       old add-on and not the bundle that now carries the
                       screen. `exit` answered both with a white page, which is
                       the worst thing to hand a customer who has just clicked a
                       menu entry: nothing rendered, nothing logged, and no way
                       to tell a missing add-on from a broken site.

                       The menu no longer draws those links - see
                       JSSTincluder::screenExists() - so what is left here is the
                       bookmark, the redirect and the typo. They get the screen
                       that names the add-on and says it is not active, which is
                       what this controller is for - the admin one in wp-admin
                       and the front-end one on the portal, the same split
                       JSSTrequest::getLayout() makes a line earlier.
                       (Roadmap 6.5-ECO-01) */
                    $jsst_layout = is_admin() ? 'admin_missingaddon' : 'missingaddon';
                    break;
            }
            $jsst_module =  'premiumplugin';
            JSSTincluder::include_file($jsst_layout, $jsst_module);
        }
    }

    function canAddLayout() {
        if (isset($_POST['form_request']) && $_POST['form_request'] == 'jssupportticket')
            return false;
        elseif (isset($_GET['action']) && $_GET['action'] == 'jstask')
            return false;
        else
            return true;
    }

}
$JSSTpremiumpluginController = new JSSTpremiumpluginController();
?>
