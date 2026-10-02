<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', JSST_PLUGIN_URL . 'includes/css/jquery-ui-smoothness.css', array(), jssupportticket::$_config['productversion']);
    wp_enqueue_style('status-graph', JSST_PLUGIN_URL . 'includes/css/status_graph.css', array(), jssupportticket::$_config['productversion']);
$jsst_jssupportticket_js ="
    /* Bulk actions: keep the hidden id list in step with the ticked boxes, show
       the value control the chosen action needs, and refuse to submit an empty
       selection. (Roadmap 4.0-CORE-05)

       Fields built by JSSTformfield already carry id=\"<field name>\", so they are
       addressed here by that name — #bulkaction, not #jsst-bulk-action. Passing
       an id of your own to the helper appends a second id attribute, and a
       browser keeps only the first, so the selector silently matches nothing:
       that is what used to make Apply always answer \"Choose an action to
       apply.\" no matter what was chosen. */
    jQuery(document).ready(function(){
        var bar = jQuery('.jsst-bulk-bar');
        if (!bar.length) { return; }
        function selected(){
            return jQuery('.jsst-bulk-ticket:checked').map(function(){ return jQuery(this).val(); }).get();
        }
        function sync(){
            var ids = selected();
            jQuery('#jsst-bulk-ids').val(ids.join(','));
            jQuery('.jsst-bulk-count').text(ids.length ? ids.length + ' ". esc_js(__('selected', 'js-support-ticket')) ."' : '');
            /* Marks the toolbar while something is ticked: Apply is one click
               from changing real tickets, so the bar says so. Styling only. */
            bar.toggleClass('jsst-bulk-active', ids.length > 0);
        }
        jQuery(document).on('change', '.jsst-bulk-ticket', sync);
        jQuery('#jsst-bulk-selectall').on('change', function(){
            jQuery('.jsst-bulk-ticket').prop('checked', jQuery(this).is(':checked'));
            sync();
        });
        jQuery('#bulkaction').on('change', function(){
            var value = jQuery(this).val();
            jQuery('.jsst-bulk-value').hide();
            if (value === 'priority') { jQuery('.jsst-bulk-value-priority').show(); }
            if (value === 'department') { jQuery('.jsst-bulk-value-department').show(); }
        });
        bar.on('submit', function(e){
            sync();
            if (!jQuery('#bulkaction').val()) {
                e.preventDefault();
                alert('". esc_js(__('Choose an action to apply.', 'js-support-ticket')) ."');
                return false;
            }
            if (!selected().length) {
                e.preventDefault();
                alert('". esc_js(__('Select at least one ticket.', 'js-support-ticket')) ."');
                return false;
            }
            return true;
        });
        sync();
    });
    function resetFrom() {
        var form = jQuery('form#jssupportticketform');
        form.find('input[type=text], input[type=email], input[type=password], textarea').val('');
        form.find('input:checkbox').removeAttr('checked');
        form.find('select').prop('selectedIndex', 0);
        form.find('select[multiple]').val([]);
        form.find('input[type=\'radio\']').prop('checked', false);
        document.getElementById('jssupportticketform').submit();
    }
    jQuery(document).ready(function(){
        jQuery(document).on('click', 'a[href=\"#jsst-clear-filters\"]', function(e){ e.preventDefault(); resetFrom(); });
        jQuery('.date,.custom_date').datepicker({dateFormat: 'yy-mm-dd'});
        jQuery('select.js-admin-sort-select').on('change',function(e){
            e.preventDefault();
            var sortby = jQuery('.js-admin-sort-select option:selected').val();
            //alert(sortby);
            jQuery('input#sortby').val(sortby);
            jQuery('form#jssupportticketform').submit();
        });
        jQuery('a.js-admin-sort-btn').on('click',function(e){
            e.preventDefault();
            var sortby = jQuery('.js-admin-sort-select option:selected').val();
            //alert(sortby);
            jQuery('input#sortby').val(sortby);
            jQuery('form#jssupportticketform').submit();
        });
        jQuery('a.js-ticket-link').click(function(e){
            e.preventDefault();
            var list = jQuery(this).attr('data-tab-number');
            jQuery('input#list').val(list);
            jQuery('form#jssupportticketform').submit();
        });
        /* The inbox buttons are the agent filter with three particular values,
           so they set that control and re-run the search the queue already
           has rather than being a second way of asking the same question.
           (Roadmap 4.5-UX-01) */
        jQuery('button.jsst-queue-scope').click(function(e){
            e.preventDefault();
            /* One inbox at a time: an agent filter and a team filter
               both applied would be an AND nobody asked for, so
               setting either clears the other. (Roadmap 4.5-FE-05) */
            var team = jQuery(this).attr('data-teamid');
            jQuery('#staffid').val(team ? '' : jQuery(this).attr('data-staffid'));
            jQuery('#teamid').val(team ? team : '');
            jQuery('form#jssupportticketform').submit();
        });
    });

    /* Who closed the ticket, and when. The name is always shown on a closed
       ticket; the date sits behind it and is revealed on hover, because it is
       the follow-up question rather than the answer. The panel is hidden in CSS,
       so without this it could never be seen. Delegated, and also bound to focus
       so the date is reachable from the keyboard as well as the mouse. */
    jQuery(document).on('mouseenter focusin', 'span.js-ticket-closedby-wrp', function(){
        jQuery(this).find('span.js-ticket-closed-date').css('display', 'inline-block');
    });
    jQuery(document).on('mouseleave focusout', 'span.js-ticket-closedby-wrp', function(){
        jQuery(this).find('span.js-ticket-closed-date').css('display', 'none');
    });

    /* Filtering by what is in a row. (Roadmap 4.0-UX-02)

       These were inline onclick attributes calling two globals. The table puts
       the same affordance on a focusable element instead, so the filter can be
       reached from the keyboard as well as the mouse. */
    jQuery(document).on('click keydown', '.jsst-row-customer, .jsst-row-department', function(e){
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') { return; }
        e.preventDefault();
        var cell = jQuery(this);
        if (cell.hasClass('jsst-row-customer')) {
            jQuery('input#email').val(cell.data('email'));
        } else {
            jQuery('#departmentid').val(cell.data('department'));
        }
        jQuery('form#jssupportticketform').submit();
    });

    /* Row density. (Roadmap 4.0-UX-02)

       Kept in localStorage rather than user meta: it is a display preference,
       not data, and storing it in the browser means no extra write endpoint and
       no permission surface for something that only changes row padding. The
       cost is that an agent who moves to another browser starts on comfortable
       again, which is the right way round for this kind of setting. */
    jQuery(document).ready(function(){
        var table = jQuery('.jsst-queue-cards');
        if (!table.length) { return; }
        var storagekey = 'jsstQueueDensity';
        function applyDensity(density){
            table.removeClass('jsst-density-compact jsst-density-comfortable').addClass('jsst-density-' + density);
            jQuery('.jsst-density-btn').removeClass('active').filter('[data-density=\"' + density + '\"]').addClass('active');
        }
        var saved = 'comfortable';
        try { saved = window.localStorage.getItem(storagekey) || 'comfortable'; } catch (err) {}
        applyDensity(saved === 'compact' ? 'compact' : 'comfortable');
        jQuery('.jsst-density-btn').on('click', function(){
            var density = jQuery(this).attr('data-density');
            applyDensity(density);
            try { window.localStorage.setItem(storagekey, density); } catch (err) {}
        });
    });

    /* Keyboard navigation. (Roadmap 4.0-UX-02)

       j/k move, Enter or o opens, x ticks the row's bulk box — the convention
       agents already know from every other queue. Nothing fires while the caret
       is in the search box or a filter, which is the failure that makes
       single-key shortcuts unusable. The arrow keys only take over once a row
       has been focused, so they still scroll the page until the agent has
       actually engaged with the list. */
    jQuery(document).ready(function(){
        var rows = jQuery('.jsst-queue-cards .jsst-row');
        if (!rows.length) { return; }
        var focused = -1;
        function isTyping(target){
            if (!target) { return false; }
            var tag = (target.tagName || '').toLowerCase();
            return tag === 'input' || tag === 'select' || tag === 'textarea' || target.isContentEditable;
        }
        function focusRow(index){
            if (index < 0 || index >= rows.length) { return; }
            focused = index;
            rows.removeClass('jsst-row-focused');
            var row = rows.eq(index);
            row.addClass('jsst-row-focused');
            row.get(0).focus();
        }
        rows.on('focus', function(){
            focused = rows.index(this);
            rows.removeClass('jsst-row-focused');
            jQuery(this).addClass('jsst-row-focused');
        });
        jQuery(document).on('keydown', function(e){
            if (isTyping(e.target) || e.metaKey || e.ctrlKey || e.altKey) { return; }
            var key = e.key;
            if (key === 'j' || (key === 'ArrowDown' && focused >= 0)) {
                e.preventDefault();
                focusRow(focused + 1);
            } else if (key === 'k' || (key === 'ArrowUp' && focused >= 0)) {
                e.preventDefault();
                focusRow(focused <= 0 ? 0 : focused - 1);
            } else if (focused < 0) {
                return;
            } else if (key === 'Enter' || key === 'o') {
                e.preventDefault();
                window.location.href = rows.eq(focused).attr('data-detail');
            } else if (key === 'x') {
                e.preventDefault();
                var box = rows.eq(focused).find('.jsst-bulk-ticket');
                box.prop('checked', !box.prop('checked')).trigger('change');
            }
        });
    });

    /* Saved views. (Roadmap 4.0-CORE-18)

       Picking a view runs it straight away — a select that needs a second click
       on Search to do anything reads as broken.

       The save form carries a hidden copy of every filter, and those copies are
       refreshed from the live controls on every change. Without that, typing a
       filter and clicking Save view would save whatever the queue was showing
       when the page loaded rather than what is on screen. */
    jQuery(document).ready(function(){
        jQuery('#viewid').on('change', function(){
            /* Clearing the tab is what lets a view open on the tab it was saved
               from. The tab links set this field, so once it carries a number
               that number is the newer instruction and the view keeps only its
               other filters. */
            jQuery('input#list').val('');
            jQuery('form#jssupportticketform').submit();
        });
        var filterform = jQuery('form#jssupportticketform');
        var saveform = jQuery('form.jsst-queue-saveview');
        if (!filterform.length || !saveform.length) { return; }
        function syncViewFilters(){
            saveform.find('.jsst-queue-viewfilter').each(function(){
                var hidden = jQuery(this);
                var live = filterform.find('[name=\"' + hidden.data('filter') + '\"]');
                if (live.length) {
                    hidden.val(live.val());
                }
            });
        }
        /* Custom fields are copied at the moment of saving rather than kept in
           step: their controls come in several shapes - one value, a list, tick
           boxes - and are rebuilt here as one list per field. */
        function syncViewCustomFields(){
            saveform.find('.jsst-queue-viewcf').remove();
            jQuery.each(saveform.data('customfields') || [], function(i, field){
                filterform.find('[name=\"' + field + '\"], [name=\"' + field + '[]\"]').each(function(){
                    var control = jQuery(this);
                    if (control.is(':checkbox, :radio') && !control.is(':checked')) { return; }
                    jQuery.each([].concat(control.val() || []), function(j, value){
                        if (value === '') { return; }
                        jQuery('<input type=\"hidden\" class=\"jsst-queue-viewcf\" />')
                            .attr('name', saveform.data('customkey') + '[' + field + '][]')
                            .val(value)
                            .appendTo(saveform);
                    });
                });
            });
        }
        filterform.on('change input', 'input, select, textarea', syncViewFilters);
        saveform.on('submit', syncViewFilters);
        saveform.on('submit', syncViewCustomFields);
        syncViewFilters();
    });

";
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);
JSSTmessage::getMessage();
?>
<div id="jsstadmin-wrapper">
    <?php JSSTsidemenu::render(); ?>
    <div id="jsstadmin-data">
        <div id="jsstadmin-wrapper-top">
            <div id="jsstadmin-wrapper-top-left">
                <div id="jsstadmin-breadcrunbs">
                    <ul>
                        <li><a href="?page=jssupportticket" title="<?php echo esc_attr(__('Dashboard','js-support-ticket')); ?>"><?php echo esc_html(__('Dashboard','js-support-ticket')); ?></a></li>
                        <li><?php echo esc_html(__('Tickets','js-support-ticket')); ?></li>
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
            <?php 
                $jsst_id='';
                if(in_array('multiform', jssupportticket::$_active_addons) && jssupportticket::$_config['show_multiform_popup'] == 1){
                    $jsst_id="id=multiformpopup";
                }
            ?>
            <h1 class="jsstadmin-head-text"><?php echo esc_html(__('Tickets','js-support-ticket')); ?></h1>
            <a <?php echo esc_attr($jsst_id); ?> title="<?php echo esc_attr(__('Add', 'js-support-ticket')); ?>" class="jsstadmin-add-link button" href="?page=ticket&jstlay=addticket&formid=<?php echo esc_attr(JSSTincluder::getJSModel('ticket')->getDefaultMultiFormId()) ?>"><img alt = "<?php echo esc_attr(__('Add', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/plus-icon.png" /><?php echo esc_html(__('Create Ticket','js-support-ticket')); ?></a>
        </div>
        <?php /* `jsst-skin` is the only edit made to this file by the design
                 system work: a scope for the cosmetic layer in admincss.css that
                 brings this screen's panels, headings, buttons and fields onto the
                 same tokens as the rest of the admin. No structure, no markup and
                 no behaviour changed; removing this one class reverts the whole
                 appearance change. */ ?>
        <div id="jsstadmin-data-wrp" class="p0 bg-n bs-n jsst-skin">
            <?php
            /* The desk's navigation, from the one description of it. The left
               column is wp-admin's; this strip is the desk's, and it is the
               same one the portal queue draws. (Roadmap 4.5-FE-02) */
            if (class_exists('JSSTnavigation')) {
                JSSTnavigation::renderNav();
            }
            $jsst_list = JSSTrequest::getVar('list', null, null);
            if($jsst_list == null){
                $jsst_list = jssupportticket::$_search['ticket']['list'];
            }
            // Arriving on the retired Answered tab highlights the tab that
            // replaced it, so the queue is never drawn with no tab selected.
            $jsst_list = JSSTqueue::normalizeList($jsst_list);
            $jsst_field_array = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1);
            $jsst_search_field_array = JSSTincluder::getJSModel('fieldordering')->getAdminSystemFieldsForSearch();
            /* The tabs and the number on each come from the queue engine, so
               the tab an agent clicks, the count beside it and the list below
               are one definition — and all three are now scoped to what this
               person may see. Two of the tabs, Waiting on Agent and Waiting on
               Customer, are new in 4.0.
               (Roadmap 4.0-CORE-18, 4.5-UX-01, 4.5-FE-04) */
            $jsst_qenginestate = class_exists('JSSTqueueengine')
                ? JSSTqueueengine::state(array('list' => $jsst_list))
                : array('tabs' => array());
            $jsst_tabs = isset($jsst_qenginestate['tabs']) ? $jsst_qenginestate['tabs'] : JSSTqueue::tabs();
            $jsst_alltotal = isset($jsst_tabs[JSSTqueue::LIST_ALL]['total'])
                ? (int) $jsst_tabs[JSSTqueue::LIST_ALL]['total']
                : (isset(jssupportticket::$jsst_data['count']['allticket']) ? (int) jssupportticket::$jsst_data['count']['allticket'] : 0);

            /* The queue engine: which columns this agent wants, and the
               personal and team inboxes. Guarded, because a bootstrap that has
               lost the include should cost the new controls rather than the
               whole queue. (Roadmap 4.5-UX-01) */
            $jsst_qengine    = class_exists('JSSTqueueengine');
            $jsst_qcolumns   = $jsst_qengine ? JSSTqueueengine::visibleColumns() : array();
            $jsst_qcatalogue = $jsst_qengine ? JSSTqueueengine::columns() : array();
            $jsst_qscopes    = ($jsst_qengine && class_exists('JSSTworkspace')) ? JSSTworkspace::scopes() : array();
            /* Two maps rather than one. A field the catalogue has never heard
               of - one an add-on put on the listing screen - must pass through
               untouched, or turning a column off here would silently remove
               somebody else's. Only fields the catalogue knows about are
               filtered by what this agent chose. */
            $jsst_qknown = array();
            $jsst_qwanted = array();
            foreach ($jsst_qcatalogue AS $jsst_qkey => $jsst_qcolumn) {
                if (empty($jsst_qcolumn['field'])) {
                    continue;
                }
                $jsst_qknown[$jsst_qcolumn['field']] = 1;
                if (isset($jsst_qcolumns[$jsst_qkey])) {
                    $jsst_qwanted[$jsst_qcolumn['field']] = 1;
                }
            }
            ?>
            <div class="js-ticket-count">
                <?php foreach ($jsst_tabs AS $jsst_tabkey => $jsst_tab) { ?>
                    <?php
                    // A tab that depends on an add-on is only offered when that
                    // add-on is there to keep its column up to date.
                    if (!empty($jsst_tab['addon']) && !in_array($jsst_tab['addon'], jssupportticket::$_active_addons)) {
                        continue;
                    }
                    $jsst_tabcount = isset($jsst_tab['total'])
                        ? (int) $jsst_tab['total']
                        : (isset(jssupportticket::$jsst_data['count'][$jsst_tab['count']]) ? (int) jssupportticket::$jsst_data['count'][$jsst_tab['count']] : 0);
                    $jsst_tabpercentage = ($jsst_alltotal != 0) ? round(($jsst_tabcount / $jsst_alltotal) * 100) : 0;
                    $jsst_tabactive = ($jsst_list == $jsst_tabkey) ? 'active' : '';
                    ?>
                    <div class="js-ticket-link">
                        <a class="js-ticket-link <?php echo esc_attr($jsst_tabactive); ?> <?php echo esc_attr($jsst_tab['class']); ?>" href="#" data-tab-number="<?php echo esc_attr($jsst_tabkey); ?>" title="<?php echo esc_attr($jsst_tab['title']); ?>">
                            <div class="js-ticket-cricle-wrp" data-per="<?php echo esc_attr($jsst_tabpercentage); ?>">
                                <div class="js-mr-rp" data-progress="<?php echo esc_attr($jsst_tabpercentage); ?>">
                                    <div class="circle">
                                        <div class="mask full">
                                            <div class="fill <?php echo esc_attr($jsst_tab['fill']); ?>"></div>
                                        </div>
                                        <div class="mask half">
                                            <div class="fill <?php echo esc_attr($jsst_tab['fill']); ?>"></div>
                                            <div class="fill fix"></div>
                                        </div>
                                        <div class="shadow"></div>
                                    </div>
                                    <div class="inset">
                                    </div>
                                </div>
                            </div>
                            <div class="js-ticket-link-text <?php echo esc_attr($jsst_tab['class']); ?>">
                                <?php
                                    echo esc_html($jsst_tab['label']);
                                    if(jssupportticket::$_config['count_on_myticket'] == 1)
                                        echo ' ('.esc_html($jsst_tabcount).')';
                                ?>
                            </div>
                        </a>
                    </div>
                <?php } ?>
            </div>
            <?php
            /* Personal and team inboxes. The three questions an agent asks in
               order when they sit down - what is mine, what has nobody picked
               up, what is my team carrying - as one row above the queue rather
               than as a dropdown somebody has to know to use. They are the
               agent filter with three particular values, so they set the
               control below and re-run the search the queue already has.
               (Roadmap 4.5-UX-01) */
            if (!empty($jsst_qscopes) && in_array('agent', jssupportticket::$_active_addons)) {
                $jsst_qcurrentstaff = isset(jssupportticket::$jsst_data['filter']['staffid']) ? (string) jssupportticket::$jsst_data['filter']['staffid'] : '';
                $jsst_qcurrentteam = isset(jssupportticket::$jsst_data['filter']['teamid']) ? (string) (int) jssupportticket::$jsst_data['filter']['teamid'] : '';
                /* The scopes say -1 for "me" so that no caller has to know its
                   own staff id. The agent control on this form is a list of
                   real ids, so the -1 is resolved here and nowhere else. */
                $jsst_qactor = JSSTcapability::actor();
                $jsst_qmine = ((int) $jsst_qactor['staffid'] > 0) ? (string) (int) $jsst_qactor['staffid'] : '';
                ?>
                <div class="jsst-queue-scopes">
                    <span class="jsst-queue-scopes-label"><?php echo esc_html(__('Inbox', 'js-support-ticket')); ?></span>
                    <?php foreach ($jsst_qscopes AS $jsst_qscopekey => $jsst_qscope) {
                        /* "My team" and "everything I can see" are the same
                           queue for an agent whose scope is already their
                           departments - the difference between them is the
                           scope clause, which is applied to every read whether
                           the button is pressed or not. Offering both would be
                           two buttons doing one thing. */
                        $jsst_qcontrol = isset($jsst_qscope['control']) ? $jsst_qscope['control'] : 'staffid';
                        if ($jsst_qcontrol === 'teamid') {
                            /* The team queue. Its own control, because it is
                               not a filter on one agent - the model expands
                               the team id to its members.
                               (Roadmap 4.5-FE-05) */
                            $jsst_qteamvalue = isset($jsst_qscope['value']) ? (string) (int) $jsst_qscope['value'] : '';
                            if ($jsst_qteamvalue === '' || $jsst_qteamvalue === '0') {
                                continue;
                            } ?>
                            <button type="button" class="jsst-queue-scope <?php echo ($jsst_qcurrentteam === $jsst_qteamvalue) ? 'jsst-queue-scope-on' : ''; ?>" data-teamid="<?php echo esc_attr($jsst_qteamvalue); ?>"><?php echo esc_html($jsst_qscope['label']); ?></button>
                            <?php continue;
                        }
                        $jsst_qvalue = isset($jsst_qscope['filters']['staffid']) ? (string) $jsst_qscope['filters']['staffid'] : '';
                        if ($jsst_qvalue === '-1') {
                            /* An administrator who is not on the Agents list
                               has no staff id, so "assigned to me" would filter
                               on nothing and quietly mean "everything" - which
                               is a button that lies rather than a button that
                               is missing. */
                            if ($jsst_qmine === '') {
                                continue;
                            }
                            $jsst_qvalue = $jsst_qmine;
                        }
                        $jsst_qon = ($jsst_qvalue === '' && $jsst_qcurrentstaff === '')
                            || ($jsst_qvalue !== '' && $jsst_qcurrentstaff === $jsst_qvalue);
                        ?>
                        <button type="button" class="jsst-queue-scope <?php echo $jsst_qon ? 'jsst-queue-scope-on' : ''; ?>" data-staffid="<?php echo esc_attr($jsst_qvalue); ?>"><?php echo esc_html($jsst_qscope['label']); ?></button>
                    <?php } ?>
                </div>
            <?php }
            $jsst_uid = JSSTrequest::getVar('uid',null,0);
            if(is_numeric($jsst_uid) && $jsst_uid){
                $jsst_formaction = wp_nonce_url(admin_url("admin.php?page=ticket&jstlay=tickets&uid=".esc_attr($jsst_uid)),"my-ticket");
            }else{
                $jsst_formaction = wp_nonce_url(admin_url("admin.php?page=ticket&jstlay=tickets"),"my-ticket");
            }
            ?>
            <?php
            // One box that searches everything a ticket is made of, including
            // every reply on it. Kept first and full width because it is what an
            // agent reaches for; the field-by-field controls below it are still
            // there for the times you know exactly which field you mean.
            // (Roadmap 4.0-CORE-18)
            /* The views come from the workspace descriptor rather than
               straight from the store, so this desk and the front-end one are
               offering one list built one way - including the ones a colleague
               shared, marked as theirs. (Roadmap 4.5-UX-01) */
            $jsst_savedviews = array();
            foreach (JSSTworkspace::views() AS $jsst_savedview) {
                $jsst_savedviews[] = (object) array(
                    'id'   => $jsst_savedview->id,
                    'text' => empty($jsst_savedview->mine)
                        ? sprintf(
                            /* translators: %s: the name of a saved view somebody else shared */
                            esc_html(__('%s (shared)', 'js-support-ticket')),
                            $jsst_savedview->name
                        )
                        : $jsst_savedview->name,
                );
            }
            $jsst_currentview = jssupportticket::$jsst_data['filter']['viewid'];
            ?>
            <form class="js-filter-form mt0" name="jssupportticketform" id="jssupportticketform" method="post" action="<?php echo esc_url($jsst_formaction); ?>">
                <div class="jsst-queue-search">
                    <label class="screen-reader-text" for="keywords"><?php echo esc_html(__('Search tickets', 'js-support-ticket')); ?></label>
                    <?php echo wp_kses(JSSTformfield::text('keywords', jssupportticket::$jsst_data['filter']['keywords'], array('placeholder' => esc_html(__('Search subject, message, replies, customer name, e-mail or ticket ID', 'js-support-ticket')), 'class' => 'js-form-input-field jsst-queue-keywords')), JSST_ALLOWED_TAGS); ?>
                    <?php if (!empty($jsst_savedviews)) { ?>
                        <span class="jsst-queue-views">
                        <label class="screen-reader-text" for="viewid"><?php echo esc_html(__('Saved view', 'js-support-ticket')); ?></label>
                        <?php echo wp_kses(JSSTformfield::select('viewid', $jsst_savedviews, $jsst_currentview, esc_html(__('Saved views', 'js-support-ticket')), array('class' => 'js-form-select-field jsst-queue-view')), JSST_ALLOWED_TAGS); ?>
                        <?php if (!empty($jsst_currentview) && is_numeric($jsst_currentview)) { ?>
                            <a class="jsst-queue-view-delete" onclick="return confirm('<?php echo esc_js(__('Delete this saved view?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ticket&task=deletequeueview&action=jstask&viewid=' . (int) $jsst_currentview), 'jsst-delete-queue-view-' . (int) $jsst_currentview)); ?>"><?php echo esc_html(__('Delete view', 'js-support-ticket')); ?></a>
                        <?php } ?>
                        </span>
                    <?php } else { ?>
                        <?php echo wp_kses(JSSTformfield::hidden('viewid', ''), JSST_ALLOWED_TAGS); ?>
                    <?php } ?>
                </div>
                <?php
                /* One search box up front, with the agent and status pickers beside
                   it; every narrower field waits behind More filters. They were all
                   drawn at once - a second subject box, a name box, an e-mail box and
                   a ticket-ID box beside a search that already covers all four - so
                   the form read as ten ways to do one thing. It opens by itself when
                   one of those fields is in use, so a filter is never hidden while
                   it is narrowing the list. */
                $jsst_morefilterkeys = array('subject', 'name', 'phone', 'email', 'productid', 'departmentid', 'helptopicid', 'priority', 'datestart', 'dateend', 'ticketid', 'orderid', 'tagid');
                $jsst_morefilterson = 0;
                foreach ($jsst_morefilterkeys AS $jsst_mfkey) {
                    if (isset(jssupportticket::$jsst_data['filter'][$jsst_mfkey]) && jssupportticket::$jsst_data['filter'][$jsst_mfkey] !== '' && jssupportticket::$jsst_data['filter'][$jsst_mfkey] !== null) {
                        $jsst_morefilterson++;
                    }
                }
                ?>
                <div class="jsst-queue-mainfilters">
                <?php if ( in_array('agent',jssupportticket::$_active_addons)) {
                    /* "Nobody" as an entry in the agent list. Until now the
                       queue could be filtered to any agent and not to the
                       absence of one, so the single most useful question a
                       queue is asked - what has nobody picked up - could not be
                       asked at all. It is first because it is the one being
                       looked for. (Roadmap 4.5-UX-01) */
                    $jsst_staffoptions = JSSTincluder::getJSModel('agent')->getStaffForCombobox();
                    if (!is_array($jsst_staffoptions)) {
                        $jsst_staffoptions = array();
                    }
                    array_unshift($jsst_staffoptions, (object) array('id' => '0', 'text' => esc_html(__('Unassigned', 'js-support-ticket'))));
                    echo wp_kses(JSSTformfield::select('staffid', $jsst_staffoptions, jssupportticket::$jsst_data['filter']['staffid'], esc_html(__('Select Agent','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php echo wp_kses(JSSTformfield::select('status', JSSTincluder::getJSModel('status')->getStatusForFilter(), jssupportticket::$jsst_data['filter']['status'], esc_html(__('Select Status','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS); ?>
                <?php
                /* Company. Only once a company exists, so a desk that does not
                   use them never sees an empty control. (Roadmap 5.5-COM-06) */
                $jsst_companyoptions = array();
                if (class_exists('JSSTcompanies') && JSSTcompanies::inUse()) {
                    foreach (JSSTcompanies::all() AS $jsst_corow) {
                        $jsst_companyoptions[] = (object) array('id' => (string) (int) $jsst_corow->id,
                            'text' => $jsst_corow->name . ((int) $jsst_corow->status !== JSSTcompanies::STATUS_ACTIVE ? ' (' . __('archived', 'js-support-ticket') . ')' : ''));
                    }
                }
                if (!empty($jsst_companyoptions)) {
                    echo wp_kses(JSSTformfield::select('companyid', $jsst_companyoptions, isset(jssupportticket::$jsst_data['filter']['companyid']) ? jssupportticket::$jsst_data['filter']['companyid'] : '', esc_html(__('Select Company','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php echo wp_kses(JSSTformfield::submitbutton('go', esc_html(__('Search', 'js-support-ticket')), array('class' => 'button js-form-search')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::button(esc_html(__('Reset', 'js-support-ticket')), esc_html(__('Reset', 'js-support-ticket')), array('class' => 'button js-form-reset', 'onclick' => 'resetFrom();')), JSST_ALLOWED_TAGS); ?>
                </div>
                <details class="jsst-queue-morefilters"<?php echo $jsst_morefilterson ? ' open' : ''; ?>>
                    <summary><?php echo esc_html(__('More filters', 'js-support-ticket')); ?><?php if ($jsst_morefilterson) { ?> <span class="jsst-queue-morefilters-count"><?php echo (int) $jsst_morefilterson; ?></span><?php } ?></summary>
                    <div class="jsst-queue-morefilters-body">
                <?php
                if (!empty($jsst_search_field_array['subject'])) {
                    echo wp_kses(JSSTformfield::text('subject', jssupportticket::$jsst_data['filter']['subject'], array('placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['subject']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if (!empty($jsst_search_field_array['fullname'])) {
                    echo wp_kses(JSSTformfield::text('name', jssupportticket::$jsst_data['filter']['name'], array('placeholder' => esc_html(__('Ticket Creator', 'js-support-ticket')).' '.$jsst_search_field_array['fullname'],'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if (!empty($jsst_search_field_array['phone'])) {
                    echo wp_kses(JSSTformfield::text('phone', jssupportticket::$jsst_data['filter']['phone'], array('placeholder' => esc_html(jssupportticket::JSST_getVarValue($jsst_search_field_array['phone'])),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($jsst_search_field_array['email'])) {
                    echo wp_kses(JSSTformfield::text('email', jssupportticket::$jsst_data['filter']['email'], array('placeholder' => jssupportticket::JSST_getVarValue($jsst_search_field_array['email']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php
                if(!empty($jsst_search_field_array['product'])) { 
                    echo wp_kses(JSSTformfield::select('productid', JSSTincluder::getJSModel('product')->getProductForCombobox(), jssupportticket::$jsst_data['filter']['productid'], esc_html(__('Select','js-support-ticket')).' '.$jsst_search_field_array['product'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($jsst_search_field_array['department'])) { 
                    echo wp_kses(JSSTformfield::select('departmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), jssupportticket::$jsst_data['filter']['departmentid'], esc_html(__('Select','js-support-ticket')).' '.$jsst_search_field_array['department'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($jsst_search_field_array['helptopic']) && JSSTmergedaddon::featureEnabled('helptopic')) { 
                    echo wp_kses(JSSTformfield::select('helptopicid', JSSTincluder::getJSModel('helptopic')->getHelpTopicsForCombobox(), jssupportticket::$jsst_data['filter']['helptopicid'], esc_html(__('Select','js-support-ticket')).' '.$jsst_search_field_array['helptopic'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                }
                if(!empty($jsst_search_field_array['priority'])) { 
                    echo wp_kses(JSSTformfield::select('priority', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), jssupportticket::$jsst_data['filter']['priority'], esc_html(__('Select','js-support-ticket')) .' '.$jsst_search_field_array['priority'], array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                } ?>
                <?php echo wp_kses(JSSTformfield::text('datestart', jssupportticket::$jsst_data['filter']['datestart'], array('placeholder' => esc_html(__('From Date', 'js-support-ticket')), 'class' => 'date js-form-date-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::text('dateend', jssupportticket::$jsst_data['filter']['dateend'], array('placeholder' => esc_html(__('To Date', 'js-support-ticket')), 'class' => 'date js-form-date-field')), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::text('ticketid', jssupportticket::$jsst_data['filter']['ticketid'], array('placeholder' => esc_html(__('Ticket ID', 'js-support-ticket')),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php if(class_exists('WooCommerce') && in_array('woocommerce', jssupportticket::$_active_addons)){  ?>
                    <?php echo wp_kses(JSSTformfield::text('orderid', jssupportticket::$jsst_data['filter']['orderid'], array('placeholder' => jssupportticket::JSST_getVarValue($jsst_field_array['wcorderid']),'class' => 'js-form-input-field')), JSST_ALLOWED_TAGS); ?>
                <?php } ?>
                <?php
                    // Tag filter. Only offered once at least one tag exists, so a site
                    // that does not tag never sees an empty control.
                    // (Roadmap 4.0-CORE-17)
                    $jsst_tagoptions = JSSTincluder::getJSModel('tag')->getTagsForCombobox();
                    if (!empty($jsst_tagoptions)) {
                        echo wp_kses(JSSTformfield::select('tagid', $jsst_tagoptions, jssupportticket::$jsst_data['filter']['tagid'], esc_html(__('Select Tag','js-support-ticket')), array('class' => 'js-form-select-field')), JSST_ALLOWED_TAGS);
                    }
                ?>
                <?php echo wp_kses(JSSTformfield::hidden('JSST_form_search', 'JSST_SEARCH'), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('sortby', jssupportticket::$jsst_data['filter']['sortby']), JSST_ALLOWED_TAGS); ?>
                <?php echo wp_kses(JSSTformfield::hidden('list', $jsst_list), JSST_ALLOWED_TAGS); ?>
                <?php /* The team queue travels with the search like every other
                         filter. (Roadmap 4.5-FE-05) */ ?>
                <?php echo wp_kses(JSSTformfield::hidden('teamid', isset(jssupportticket::$jsst_data['filter']['teamid']) ? jssupportticket::$jsst_data['filter']['teamid'] : ''), JSST_ALLOWED_TAGS); ?>

                <?php
                    $jsst_customfields = JSSTincluder::getObjectClass('customfields')->adminFieldsForSearch(1);
                    foreach ($jsst_customfields as $jsst_field) {
                        JSSTincluder::getObjectClass('customfields')->formCustomFieldsForSearch($jsst_field, $jsst_k, 1);
                    }
                ?>
                    </div>
                </details>
            </form>
            <?php
            /* The column picker. Its own form and its own nonce, posting to a
               task rather than re-running the search: what a person wants to
               see is not a filter, and folding it into the search form would
               mean every search silently re-saved it. (Roadmap 4.5-UX-01) */
            if ($jsst_qengine && !empty($jsst_qcatalogue)) { ?>
                <details class="jsst-queue-columns">
                    <summary><?php echo esc_html(__('Columns', 'js-support-ticket')); ?></summary>
                    <form method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ticket&task=savequeuecolumns&action=jstask'), 'jsst-queue-columns')); ?>">
                        <p class="jsst-queue-columns-note"><?php echo esc_html(__('Yours alone. Everybody else keeps the columns this site is set up with, and turning one off here never hides it from them.', 'js-support-ticket')); ?></p>
                        <?php /* Grouped rather than one flat list. On a site with a dozen
                                 custom fields a single run of ticks is a wall, and the two
                                 halves have different owners besides. (Roadmap 4.5-UX-01) */
                        foreach (JSSTqueueengine::groupedColumns() AS $jsst_qgroup) { ?>
                            <div class="jsst-queue-columns-group">
                                <span class="jsst-queue-columns-grouphead"><?php echo esc_html($jsst_qgroup['label']); ?></span>
                                <div class="jsst-queue-columns-list">
                                    <?php foreach ($jsst_qgroup['columns'] AS $jsst_qkey => $jsst_qcolumn) {
                                        $jsst_qfixed = ($jsst_qkey === 'ticketid' || $jsst_qkey === 'subject'); ?>
                                        <label class="jsst-queue-column">
                                            <input type="checkbox" name="queuecolumns[]" value="<?php echo esc_attr($jsst_qkey); ?>" <?php checked(isset($jsst_qcolumns[$jsst_qkey])); ?> <?php disabled($jsst_qfixed); ?> />
                                            <span><?php echo esc_html($jsst_qcolumn['label']); ?></span>
                                        </label>
                                        <?php if ($jsst_qfixed) { ?>
                                            <input type="hidden" name="queuecolumns[]" value="<?php echo esc_attr($jsst_qkey); ?>" />
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <button type="submit" class="button button-primary"><?php echo esc_html(__('Save columns', 'js-support-ticket')); ?></button>
                        <button type="submit" name="resetcolumns" value="1" class="button"><?php echo esc_html(__('Back to the site default', 'js-support-ticket')); ?></button>
                    </form>
                </details>
            <?php }
            // Saving a view. Its own form, because it posts to a task with its
            // own nonce rather than re-running the search — but it carries a
            // hidden copy of every filter the queue is currently showing, so
            // what gets stored is exactly the queue on screen. The script below
            // keeps those hidden copies in step as the controls above change.
            // (Roadmap 4.0-CORE-18)
            //
            // Offered only once a search has narrowed the queue to something a
            // view can keep, so it reads as the step after searching rather
            // than a form to fill in first. (Roadmap 4.5-UX-01)
            if (JSSTticketaction::canRun('View Ticket') && JSSTqueue::canSaveCurrentSearch()) { ?>
                <form class="jsst-queue-saveview" method="post" data-customfields="<?php echo esc_attr(wp_json_encode(JSSTqueue::customFieldNames())); ?>" data-customkey="<?php echo esc_attr(JSSTqueue::CUSTOM_KEY); ?>" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ticket&task=savequeueview&action=jstask'), 'jsst-queue-view')); ?>">
                    <label class="screen-reader-text" for="jsst-queue-viewname"><?php echo esc_html(__('Name for this view', 'js-support-ticket')); ?></label>
                    <input type="text" class="inputbox jsst-queue-viewname" id="jsst-queue-viewname" name="viewname" maxlength="60" placeholder="<?php echo esc_attr(__('Name this search to come back to it', 'js-support-ticket')); ?>" />
                    <?php /* Sharing puts this view in front of every agent who
                             can open the queue. It shares the question, not the
                             answer: the filters go through the same scope clause
                             as everything else, so a colleague opening it sees
                             their own tickets matching it and never somebody
                             else's. (Roadmap 4.5-UX-01) */
                    if (class_exists('JSSTqueue') && JSSTqueue::canShare()) { ?>
                        <label class="jsst-queue-share">
                            <input type="checkbox" name="viewshared" value="1" />
                            <span title="<?php echo esc_attr(__('Every agent and administrator will see this view in their list. Each of them still sees only the tickets they are allowed to see.', 'js-support-ticket')); ?>"><?php echo esc_html(__('Share with all agents', 'js-support-ticket')); ?></span>
                        </label>
                    <?php } ?>
                    <?php echo wp_kses(JSSTformfield::submitbutton('savequeueview', esc_html(__('Save view', 'js-support-ticket')), array('class' => 'button jsst-queue-saveview-apply')), JSST_ALLOWED_TAGS); ?>
                    <?php
                    // The queue as it stands, one hidden field per filter.
                    foreach (JSSTqueue::viewKeys() AS $jsst_viewkey) {
                        $jsst_viewvalue = ($jsst_viewkey === 'list') ? $jsst_list : (isset(jssupportticket::$jsst_data['filter'][$jsst_viewkey]) ? jssupportticket::$jsst_data['filter'][$jsst_viewkey] : '');
                        if (is_array($jsst_viewvalue)) {
                            continue;
                        }
                        ?>
                        <input type="hidden" class="jsst-queue-viewfilter" data-filter="<?php echo esc_attr($jsst_viewkey); ?>" name="<?php echo esc_attr($jsst_viewkey); ?>" value="<?php echo esc_attr($jsst_viewvalue); ?>" />
                        <?php
                    }
                    ?>
                    <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                </form>
            <?php }
            ?>
            <?php
            $jsst_link = '?page=ticket';
            if (jssupportticket::$_sortorder == 'ASC')
                $jsst_img = "sorting-white-1.png";
            else
                $jsst_img = "sorting-white-2.png";
            ?>
            <div class="js-admin-heading">
                <div class="js-admin-head-txt"><?php echo esc_html(__('All Tickets', 'js-support-ticket')); ?></div>
                <div class="js-admin-sorting">
                    <select class="js-admin-sort-select">
                        <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['subject'])); ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['subject']); ?>" <?php if (jssupportticket::$_sorton == 'subject') echo 'selected' ?>><?php echo esc_html(__("Subject",'js-support-ticket')); ?></option>
                        <?php 
                        if (!empty($jsst_field_array['priority'])) { ?>
                            <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['priority']); ?>"  <?php if (jssupportticket::$_sorton == 'priority') echo 'selected' ?>><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['priority'])); ?></option>
                            <?php
                        } ?>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['ticketid']); ?>"  <?php if (jssupportticket::$_sorton == 'ticketid') echo 'selected' ?>><?php echo esc_html(__("Ticket ID",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['isanswered']); ?>"  <?php if (jssupportticket::$_sorton == 'isanswered') echo 'selected' ?>><?php echo esc_html(__("Answered",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['status']); ?>"  <?php if (jssupportticket::$_sorton == 'status') echo 'selected' ?>><?php echo esc_html(__("Status",'js-support-ticket')); ?></option>
                        <option value="<?php echo esc_attr(jssupportticket::$_sortlinks['created']); ?>"  <?php if (jssupportticket::$_sorton == 'created') echo 'selected' ?>><?php echo esc_html(__("Created",'js-support-ticket')); ?></option>
                    </select>
                    <a href="#" class="js-admin-sort-btn" title="<?php echo esc_attr(__('sort','js-support-ticket')); ?>">
                        <img alt = "<?php echo esc_attr(__('sort','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . 'includes/images/' . esc_attr($jsst_img) ?>">
                    </a>
                </div>
            </div>
            <?php
            if (!empty(jssupportticket::$jsst_data[0])) {
                // Bulk actions. Every ticket runs the same single-ticket path the
                // detail screen uses, and one reason is recorded against each.
                // (Roadmap 4.0-CORE-05)
                /* Which of them this agent may run comes from the workspace
                   layer now, which puts every one to the capability service
                   with the actor as an argument - rather than from
                   JSSTticketaction::canRun(), which answers from whichever of
                   the two permission systems it reaches first. The same list
                   builds the bulk bar on the front-end desk.
                   (Roadmap 4.5-UX-01) */
                $jsst_bulk_allowed = array();
                foreach (JSSTworkspace::queueBulk() AS $jsst_bulk_key => $jsst_bulk_label) {
                    $jsst_bulk_allowed[] = (object) array('id' => $jsst_bulk_key, 'text' => $jsst_bulk_label);
                }
                if (!empty($jsst_bulk_allowed)) { ?>
                    <form class="jsst-bulk-bar jsst-queue-bulk" method="post" action="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ticket&task=bulkaction&action=jstask'), 'jsst-bulk-action')); ?>">
                        <div class="jsst-bulk-row">
                            <label class="jsst-bulk-selectall">
                                <input type="checkbox" id="jsst-bulk-selectall" />
                                <span><?php echo esc_html(__('Select all', 'js-support-ticket')); ?></span>
                            </label>
                            <label class="screen-reader-text" for="bulkaction"><?php echo esc_html(__('Bulk action', 'js-support-ticket')); ?></label>
                            <?php echo wp_kses(JSSTformfield::select('bulkaction', $jsst_bulk_allowed, '', esc_html(__('Bulk action', 'js-support-ticket')), array('class' => 'inputbox jsst-bulk-action')), JSST_ALLOWED_TAGS); ?>
                            <span class="jsst-bulk-value jsst-bulk-value-priority" style="display:none;">
                                <label class="screen-reader-text" for="bulkpriorityid"><?php echo esc_html(__('Priority', 'js-support-ticket')); ?></label>
                                <?php echo wp_kses(JSSTformfield::select('bulkpriorityid', JSSTincluder::getJSModel('priority')->getPriorityForCombobox(), '', esc_html(__('Select Priority', 'js-support-ticket')), array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                            </span>
                            <span class="jsst-bulk-value jsst-bulk-value-department" style="display:none;">
                                <label class="screen-reader-text" for="bulkdepartmentid"><?php echo esc_html(__('Department', 'js-support-ticket')); ?></label>
                                <?php echo wp_kses(JSSTformfield::select('bulkdepartmentid', JSSTincluder::getJSModel('department')->getDepartmentForCombobox(), '', esc_html(__('Select Department', 'js-support-ticket')), array('class' => 'inputbox')), JSST_ALLOWED_TAGS); ?>
                            </span>
                            <label class="screen-reader-text" for="jsst-bulk-reason"><?php echo esc_html(__('Reason', 'js-support-ticket')); ?></label>
                            <input type="text" class="inputbox jsst-bulk-reason" id="jsst-bulk-reason" name="actionreason" maxlength="500" placeholder="<?php echo esc_attr(__('Reason (optional, recorded on each ticket)', 'js-support-ticket')); ?>" />
                            <?php echo wp_kses(JSSTformfield::submitbutton('applybulk', esc_html(__('Apply', 'js-support-ticket')), array('class' => 'button jsst-bulk-apply')), JSST_ALLOWED_TAGS); ?>
                            <span class="jsst-bulk-count" role="status" aria-live="polite"></span>
                        </div>
                        <input type="hidden" name="bulkticketids" id="jsst-bulk-ids" value="" />
                        <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
                    </form>
                <?php } ?>
                <?php
                /* Two things the card loop below needs, worked out once rather
                 * than per row. (Roadmap 4.0-UX-02, 4.0-PERF-01)
                 *
                 * The custom-field list is keyed by the ticket form, not the
                 * ticket, so it is the same for every row on the page — the
                 * original card layout re-ran that query for each ticket.
                 */
                /* The site's switch AND this person's column. The switch says whether
                   this desk shows assignment at all; the column says whether they want
                   to see it. (Roadmap 4.5-UX-01) */
                $jsst_show_assignee = in_array('agent', jssupportticket::$_active_addons)
                    && jssupportticket::$_config['show_assignto_on_admin_tickets'] == 1
                    && (empty($jsst_qcatalogue) || isset($jsst_qcolumns['agent']));
                /* May this reader amend a ticket at all, and may they force one
                   out of existence? Asked once for the page rather than per row:
                   neither answer depends on which ticket it is.

                   `canEditTicketContent()` and not something of this template's
                   own, because it is the exact predicate the addticket
                   controller decides on - so the button appears when, and only
                   when, the screen behind it will open. Drawn unconditionally,
                   it sent a Light Agent - who holds CAP_TICKETS and CAP_NOTE and
                   no CAP_EDIT - to a form with its fields withheld, an error
                   across the top and a Submit button underneath. The ticket
                   detail screen was given this same guard when that bug was
                   found there; this listing was missed. (Roadmap 4.0-SEC-04) */
                $jsst_can_edit_ticket = JSSTroles::canEditTicketContent();
                $jsst_can_force_delete = current_user_can('manage_options');
                /* Only the fallback for a site whose queue engine include is missing;
                   the rows below ask JSSTqueueengine for this ticket's own form. */
                $jsst_customcolumns = JSSTincluder::getObjectClass('customfields')->userFieldsData(1, 1);
                ?>
                <div class="jsst-queue-toolbar">
                    <div class="jsst-density" role="group" aria-label="<?php echo esc_attr(__('Row density', 'js-support-ticket')); ?>">
                        <button type="button" class="button jsst-density-btn" data-density="comfortable"><?php echo esc_html(__('Comfortable', 'js-support-ticket')); ?></button>
                        <button type="button" class="button jsst-density-btn" data-density="compact"><?php echo esc_html(__('Compact', 'js-support-ticket')); ?></button>
                    </div>
                    <span class="jsst-kbd-hint"><?php echo esc_html(__('j / k move · Enter opens · x selects', 'js-support-ticket')); ?></span>
                </div>
                <div class="jsst-queue-cards jsst-density-comfortable">
                <?php
                /* One card per ticket. (Roadmap 4.0-UX-02)
                 *
                 * This was briefly a column table. That was wrong for this
                 * product: any field can be flagged "show on listing", custom
                 * fields included, so the number of things to display is set by
                 * the site and has no upper bound. Columns cannot express that —
                 * every field an administrator ticks makes the table wider until
                 * it scrolls sideways. A card stacks label and value, so twenty
                 * fields cost height and nothing else.
                 *
                 * What the table attempt was right about is kept: the tabs,
                 * search and saved views above, bulk selection, the keyboard
                 * shortcuts and the density switch all work on cards too.
                 */
                $jsst_fields_array = array();          // field titles, per ticket form
                $jsst_show_on_listing_arrays = array(); // configured listing fields, per ticket form
                foreach (jssupportticket::$jsst_data[0] AS $jsst_ticket) {
                    if (!isset($jsst_fields_array[$jsst_ticket->multiformid])) {
                        $jsst_fields_array[$jsst_ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldTitleByFieldfor(1, $jsst_ticket->multiformid);
                    }
                    if (!isset($jsst_show_on_listing_arrays[$jsst_ticket->multiformid])) {
                        $jsst_show_on_listing_arrays[$jsst_ticket->multiformid] = JSSTincluder::getJSModel('fieldordering')->getFieldsForListing(1, $jsst_ticket->multiformid);
                    }
                    $jsst_field_array = $jsst_fields_array[$jsst_ticket->multiformid];
                    $jsst_show_on_listing_array = $jsst_show_on_listing_arrays[$jsst_ticket->multiformid];
                    /* What this site shows, narrowed to what this agent wants
                       to see. Fields the column catalogue has never heard of
                       pass through untouched - they belong to whichever module
                       put them on the listing screen, and dropping them here
                       would be this feature quietly removing somebody else's.
                       (Roadmap 4.5-UX-01) */
                    if (!empty($jsst_qknown)) {
                        foreach ($jsst_show_on_listing_array AS $jsst_listfield => $jsst_liston) {
                            if (isset($jsst_qknown[$jsst_listfield]) && !isset($jsst_qwanted[$jsst_listfield])) {
                                unset($jsst_show_on_listing_array[$jsst_listfield]);
                            }
                        }
                    }

                    $jsst_ticketviamail = '';
                    if ($jsst_ticket->ticketviaemail == 1) {
                        $jsst_ticketviamail = esc_html(__('Created via Email', 'js-support-ticket'));
                    }
                    $jsst_detailurl = '?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_ticket->id;
                    ?>
                    <div class="js-ticket-wrapper jsst-row" tabindex="-1" data-detail="<?php echo esc_url($jsst_detailurl); ?>">
                        <?php if (!empty($jsst_bulk_allowed)) { ?>
                            <label class="jsst-bulk-pick">
                                <input type="checkbox" class="jsst-bulk-ticket" value="<?php echo esc_attr($jsst_ticket->id); ?>" />
                                <span class="screen-reader-text"><?php echo esc_html(sprintf(
                                    /* translators: %s: ticket reference */
                                    __('Select ticket %s', 'js-support-ticket'),
                                    $jsst_ticket->ticketid
                                )); ?></span>
                            </label>
                        <?php } ?>
                        <div class="js-ticket-toparea">
                            <div class="js-ticket-pic">
                                <?php echo wp_kses(jsst_get_avatar($jsst_ticket->uid), JSST_ALLOWED_TAGS); ?>
                            </div>
                            <div class="js-ticket-data">
                                <div class="js-ticket-left">
                                    <?php if (!empty($jsst_show_on_listing_array['fullname'])) { ?>
                                        <div class="js-ticket-data-row">
                                            <span class="js-ticket-user jsst-row-customer" role="button" tabindex="0" data-email="<?php echo esc_attr($jsst_ticket->email); ?>"><?php echo esc_html($jsst_ticket->name); ?></span>
                                            <?php if ($jsst_ticket->status == 5 && jssupportticket::$_config['show_closedby_on_admin_tickets'] == 1) { ?>
                                                <span class="js-ticket-closedby-wrp">
                                                    <span class="js-ticket-closedby" tabindex="0">
                                                        <?php echo esc_html(JSSTincluder::getJSModel('ticket')->getClosedBy($jsst_ticket->closedby)); ?>
                                                    </span>
                                                    <?php if ($jsst_ticket->closed != '0000-00-00 00:00:00') { ?>
                                                        <span class="js-ticket-closed-date">
                                                            <?php echo esc_html(__('Closed on', 'js-support-ticket')) . ' ' . esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->closed))); ?>
                                                        </span>
                                                    <?php } ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                    <div class="js-ticket-data-row">
                                        <a title="<?php echo esc_attr(__('Subject','js-support-ticket')); ?>" class="js-ticket-det-link" href="<?php echo esc_url($jsst_detailurl); ?>"><?php echo esc_html($jsst_ticket->subject); ?></a>
                                    </div>
                                    <?php
                                    /* The company behind the address, linked to this queue
                                       filtered to it. (Roadmap 5.5-COM-06) */
                                    $jsst_rowcompany = !empty($jsst_companyoptions) ? JSSTcompanies::forEmail($jsst_ticket->email) : false;
                                    if ($jsst_rowcompany) {
                                        $jsst_companylink = wp_nonce_url(admin_url('admin.php?page=ticket&jstlay=tickets&JSST_form_search=JSST_SEARCH&list=' . JSSTqueue::LIST_ALL . '&companyid=' . (int) $jsst_rowcompany->id), 'my-ticket'); ?>
                                        <div class="js-ticket-data-row">
                                            <div class="js-ticket-data-row-rec">
                                                <span class="js-ticket-title"><?php echo esc_html(__('Company', 'js-support-ticket')); ?></span>
                                                <a class="js-ticket-value" href="<?php echo esc_url($jsst_companylink); ?>" title="<?php echo esc_attr(__('Show only this company\'s tickets', 'js-support-ticket')); ?>"><?php echo esc_html($jsst_rowcompany->name); ?></a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php
                                    foreach ($jsst_show_on_listing_array AS $jsst_field_field => $jsst_field_title) {
                                        switch ($jsst_field_field) {
                                            case 'department': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['department'])); ?></span>
                                                        <span class="js-ticket-value jsst-row-department" role="button" tabindex="0" data-department="<?php echo esc_attr($jsst_ticket->departmentid); ?>"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->departmentname)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'email': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['email'])); ?></span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->email)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'phone': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['phone'])); ?></span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->phone)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'product': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['product'])); ?></span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->producttitle)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'helptopic':
                                                // Two questions: is the feature on, and did the join
                                                // that carries the topic name actually run. The name
                                                // is contributed by a hook, so checking the feature
                                                // alone was a notice on every row.
                                                // (Roadmap 4.0-CORE-19, 4.0-CORE-20)
                                                if (JSSTmergedaddon::featureEnabled('helptopic') && isset($jsst_ticket->topic)) { ?>
                                                    <div class="js-ticket-data-row">
                                                        <div class="js-ticket-data-row-rec">
                                                            <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['helptopic'])); ?></span>
                                                            <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->topic)); ?></span>
                                                        </div>
                                                    </div>
                                                <?php }
                                                break;
                                            case 'eddorderid': ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['eddorderid'])); ?></span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->eddorderid)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            case 'eddproductid':
                                                if (!in_array('easydigitaldownloads', jssupportticket::$_active_addons) || !class_exists('Easy_Digital_Downloads')) {
                                                    break;
                                                } ?>
                                                <div class="js-ticket-data-row">
                                                    <div class="js-ticket-data-row-rec">
                                                        <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_field_array['eddproductid'])); ?></span>
                                                        <span class="js-ticket-value"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->eddproductid)); ?></span>
                                                    </div>
                                                </div>
                                                <?php
                                                break;
                                            default:
                                                break;
                                        }
                                    }

                                    /* The mood the AI read off this ticket, when the
                                       site switched that on and the agent turned the
                                       column on. One pill: the evidence behind it
                                       belongs on the ticket, where there is room to
                                       read it, and a queue cell carrying a sentence
                                       makes every row a different height.
                                       (Roadmap 6.0-AI-13) */
                                    if (class_exists('JSSTaitriage') && $jsst_qengine && isset($jsst_qcolumns['aimood'])) {
                                        if (!isset($jsst_aimoods)) {
                                            $jsst_aimoods = JSSTaitriage::forTickets(
                                                wp_list_pluck(jssupportticket::$jsst_data[0], 'id'));
                                        }
                                        $jsst_aicell = isset($jsst_aimoods[$jsst_ticket->id])
                                            ? JSSTaitriage::cell($jsst_aimoods[$jsst_ticket->id]) : '';
                                        if ($jsst_aicell !== '') { ?>
                                            <div class="js-ticket-data-row jsst-row-mood">
                                                <div class="js-ticket-data-row-rec">
                                                    <span class="js-ticket-title"><?php echo esc_html(__('Mood','js-support-ticket')); ?></span>
                                                    <span class="js-ticket-value"><?php echo wp_kses_post($jsst_aicell); ?></span>
                                                </div>
                                            </div>
                                        <?php }
                                    } ?>
                                    <?php
                                    // Tags. A core object rather than a form field, so rendered
                                    // outside the configured field order, and only when the ticket
                                    // carries some. (Roadmap 4.0-CORE-17)
                                    $jsst_rowtags = isset(jssupportticket::$jsst_data['ticket_tags'][$jsst_ticket->id]) ? jssupportticket::$jsst_data['ticket_tags'][$jsst_ticket->id] : array();
                                    // Tags are a column like any other now, and one an agent can
                                    // turn off. (Roadmap 4.0-CORE-17, 4.5-UX-01)
                                    if (!empty($jsst_rowtags) && (!$jsst_qengine || isset($jsst_qcolumns['tags']))) { ?>
                                        <div class="js-ticket-data-row">
                                            <div class="js-ticket-data-row-rec">
                                                <span class="js-ticket-title"><?php echo esc_html(__('Tags','js-support-ticket')); ?></span>
                                                <span class="js-ticket-value">
                                                    <?php foreach ($jsst_rowtags AS $jsst_rowtag) { ?>
                                                        <?php
                                                            // Clicking a chip runs the same search the filter
                                                            // control runs, so it needs the search marker and the
                                                            // search nonce — otherwise the request falls through
                                                            // to the saved cookie and the tag is ignored. It lands
                                                            // on All Tickets: a chip is a question about a tag,
                                                            // not about a queue state.
                                                            // (Roadmap 4.0-CORE-17, 4.0-CORE-18)
                                                            $jsst_taglink = wp_nonce_url(admin_url('admin.php?page=ticket&jstlay=tickets&JSST_form_search=JSST_SEARCH&list=' . JSSTqueue::LIST_ALL . '&tagid=' . (int) $jsst_rowtag->id), 'my-ticket');
                                                        ?>
                                                        <a class="jsst-tag-chip" href="<?php echo esc_url($jsst_taglink); ?>"><?php echo esc_html($jsst_rowtag->name); ?></a>
                                                    <?php } ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php }

                                    // Custom fields, whatever the site has defined. The list is the
                                    // same for every row — it is keyed by the ticket form, not the
                                    // ticket — so it is fetched once above and reused here. The old
                                    // card layout re-ran that query for every ticket on the page.
                                    // (Roadmap 4.0-PERF-01)
                                    jssupportticket::$jsst_data['custom']['ticketid'] = $jsst_ticket->id;
                                    /* This ticket's form, and only the custom fields this person
                                       has kept. It used to draw the default form's fields on every
                                       row - so a ticket raised on another form showed that form's
                                       questions, unanswered - and it drew them outside the column
                                       choice, so a custom field turned off in the picker came back
                                       on every row anyway. Still one query per form per page: the
                                       engine memoises it. (Roadmap 4.5-UX-01) */
                                    $jsst_rowcustom = $jsst_qengine
                                        ? JSSTqueueengine::customFieldsFor($jsst_ticket->multiformid, $jsst_qcolumns)
                                        : $jsst_customcolumns;
                                    foreach ($jsst_rowcustom AS $jsst_customfield) {
                                        $jsst_ret = JSSTincluder::getObjectClass('customfields')->showCustomFields($jsst_customfield, 1, $jsst_ticket->params);
                                        ?>
                                        <div class="js-ticket-data-row js-tkt-custm-flds-wrp">
                                            <div class="js-ticket-data-row-rec">
                                                <span class="js-ticket-title"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ret['title'])); ?></span>
                                                <span class="js-ticket-value"><?php echo wp_kses($jsst_ret['value'], JSST_ALLOWED_TAGS); ?></span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="js-ticket-right">
                                    <span class="js-ticket-value js-ticket-creade-via-email-spn"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticketviamail)); ?></span>
                                    <?php
                                    $jsst_counter = 'one';
                                    if ($jsst_ticket->lock == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($jsst_counter); $jsst_counter = 'two'; ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/lock.png"; ?>" alt="<?php echo esc_attr(__('The ticket is locked', 'js-support-ticket')); ?>" title="<?php echo esc_attr(__('The ticket is locked', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <?php if ($jsst_ticket->isoverdue == 1) { ?>
                                        <img class="ticketstatusimage <?php echo esc_attr($jsst_counter); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL) . "includes/images/over-due.png"; ?>" alt="<?php echo esc_attr(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" title="<?php echo esc_attr(__('This ticket is marked as overdue', 'js-support-ticket')); ?>" />
                                    <?php } ?>
                                    <?php /* The status badge is the Status column, drawn as a badge
                                             rather than a labelled line. It used to render whatever the
                                             picker said, which is why unticking every column still left
                                             a status on every row. (Roadmap 4.5-UX-01) */
                                    if (empty($jsst_qcatalogue) || isset($jsst_qcolumns['status'])) { ?>
                                        <span class="js-ticket-status" style="color:<?php echo esc_attr($jsst_ticket->statuscolour); ?>;background:<?php echo esc_attr($jsst_ticket->statusbgcolour); ?>">
                                            <?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->statustitle)); ?>
                                        </span>
                                    <?php } ?>
                                    <?php if (!empty($jsst_show_on_listing_array['priority'])) { ?>
                                        <span class="js-ticket-priority js-ticket-wrapper-textcolor" style="background:<?php echo esc_attr($jsst_ticket->prioritycolour); ?>;"><?php echo esc_html(jssupportticket::JSST_getVarValue($jsst_ticket->priority)); ?></span>
                                    <?php } ?>
                                    <div class="js-ticket-data1">
                                        <div class="js-ticket-data1-row">
                                            <div class="js-ticket-data1-title"><?php echo esc_html(__('Ticket ID', 'js-support-ticket')).':'; ?></div>
                                            <div class="js-ticket-data1-value"><?php echo esc_html($jsst_ticket->ticketid); ?></div>
                                        </div>
                                        <?php /* One slot, two columns - the last reply where there is
                                                 one and the date raised otherwise - so it answers for
                                                 Last reply and for Raised, and it obeyed neither.
                                                 (Roadmap 4.5-UX-01) */
                                        $jsst_qhaslast  = (!empty($jsst_ticket->lastreply) && $jsst_ticket->lastreply != '0000-00-00 00:00:00');
                                        $jsst_qwantlast = (empty($jsst_qcatalogue) || isset($jsst_qcolumns['lastreply']));
                                        $jsst_qwantmade = (empty($jsst_qcatalogue) || isset($jsst_qcolumns['created']));
                                        if ($jsst_qhaslast && $jsst_qwantlast) { ?>
                                            <div class="js-ticket-data1-row">
                                                <div class="js-ticket-data1-title"><?php echo esc_html(__('Last Reply', 'js-support-ticket')).':'; ?></div>
                                                <div class="js-ticket-data1-value"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->lastreply))); ?></div>
                                            </div>
                                        <?php } elseif ($jsst_qwantmade) { ?>
                                            <div class="js-ticket-data1-row">
                                                <div class="js-ticket-data1-title"><?php echo esc_html(__('Created','js-support-ticket')).':'; ?></div>
                                                <div class="js-ticket-data1-value"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->created))); ?></div>
                                            </div>
                                        <?php } ?>
                                        <?php
                                        // The due date and the overdue state, which the card never
                                        // showed. (Roadmap 4.0-UX-02) Under the Due column since
                                        // 4.5-UX-01, like every other thing on this card.
                                        if (!empty($jsst_ticket->duedate) && $jsst_ticket->duedate != '0000-00-00 00:00:00'
                                            && (empty($jsst_qcatalogue) || isset($jsst_qcolumns['duedate']))) { ?>
                                            <div class="js-ticket-data1-row">
                                                <div class="js-ticket-data1-title"><?php echo esc_html(__('Due', 'js-support-ticket')).':'; ?></div>
                                                <div class="js-ticket-data1-value"><?php echo esc_html(date_i18n(jssupportticket::$_config['date_format'], jssupportticketphplib::JSST_strtotime($jsst_ticket->duedate))); ?></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($jsst_show_assignee) { ?>
                                            <div class="js-ticket-data1-row">
                                                <div class="js-ticket-data1-title"><?php echo esc_html(__('Assign To', 'js-support-ticket')).':'; ?></div>
                                                <div class="js-ticket-data1-value"><?php
                                                    echo !empty($jsst_ticket->staffname)
                                                            ? esc_html($jsst_ticket->staffname)
                                                            : esc_html(__('Unassigned', 'js-support-ticket'));
                                                ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php /* Delete is asked per ticket because
                                 TICKET_DELETE is a scoped action - on a desk
                                 running the Agents add-on the answer can
                                 differ from one row to the next. Enforce
                                 delete asks manage_options, which is what
                                 its own task handler asks. */
                        $jsst_can_delete_ticket = class_exists('JSSTcapability')
                            && JSSTcapability::can(JSSTcapability::TICKET_DELETE,
                                    array('ticket' => (int) $jsst_ticket->id)); ?>
                        <?php if ($jsst_can_edit_ticket || $jsst_can_delete_ticket || $jsst_can_force_delete) { ?>
                        <div class="js-ticket-bottom-data-part">
                            <div class="js-ticket-datapart-buttons-action">
                                <?php if ($jsst_can_edit_ticket) { ?>
                                <a class="js-ticket-datapart-action-btn button" title="<?php echo esc_attr(__('Edit Ticket', 'js-support-ticket')); ?>" href="?page=ticket&jstlay=addticket&jssupportticketid=<?php echo esc_attr($jsst_ticket->id); ?>"><img alt="<?php echo esc_attr(__('Edit','js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/edit-2.png" /><?php echo esc_html(__('Edit Ticket', 'js-support-ticket')); ?></a>
                                <?php } ?>
                                <?php if ($jsst_can_delete_ticket) { ?>
                                <a class="js-ticket-datapart-action-btn button" title="<?php echo esc_attr(__('Delete Ticket', 'js-support-ticket')); ?>" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete?', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=ticket&task=deleteticket&action=jstask&ticketid='.esc_attr($jsst_ticket->id),'delete-ticket-'.$jsst_ticket->id));?>">
                                    <img alt="<?php echo esc_attr(__('Delete', 'js-support-ticket')); ?>" src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/delete-2.png" />
                                    <?php echo esc_html(__('Delete Ticket', 'js-support-ticket')); ?></a>
                                <?php } ?>
                                <?php if ($jsst_can_force_delete) { ?>
                                <a title="<?php echo esc_attr(__('Enforce delete', 'js-support-ticket')); ?>" class="js-ticket-datapart-action-btn button" onclick="return confirm('<?php echo esc_js(__('Are you sure to enforce delete', 'js-support-ticket')); ?>');" href="<?php echo esc_url(wp_nonce_url('?page=ticket&task=enforcedeleteticket&action=jstask&ticketid='.esc_attr($jsst_ticket->id),'enforce-delete-ticket-'.$jsst_ticket->id))?>"><img src="<?php echo esc_url(JSST_PLUGIN_URL); ?>includes/images/forced-delete.png" alt="<?php echo esc_attr(__('Enforce delete', 'js-support-ticket')); ?>" /><?php echo esc_html(__('Enforce delete', 'js-support-ticket')); ?></a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php
                }
                ?>
                </div>
                <?php
                if (jssupportticket::$jsst_data[1]) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post(jssupportticket::$jsst_data[1]) . '</div></div>';
                }
            } else {
                if (!empty(jssupportticket::$jsst_data['filter']['keywords']) || !empty($jsst_morefilterson)
                    || (isset(jssupportticket::$jsst_data['filter']['status']) && jssupportticket::$jsst_data['filter']['status'] !== '')
                    || (isset(jssupportticket::$jsst_data['filter']['staffid']) && jssupportticket::$jsst_data['filter']['staffid'] !== '')) {
                    JSSTlayout::getNoRecordFound(
                        __('No tickets match these filters', 'js-support-ticket'),
                        __('Try fewer filters, or', 'js-support-ticket'),
                        '#jsst-clear-filters', // the search is remembered, so clearing goes through the same reset as the button
                        __('clear them all', 'js-support-ticket'));
                } else {
                    JSSTlayout::getNoRecordFound(
                        __('No tickets here', 'js-support-ticket'),
                        __('New tickets show up in this list as soon as they arrive.', 'js-support-ticket'));
                }
            }
            ?>
        </div>
    </div>
</div>
