<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
if(in_array('notification', jssupportticket::$_active_addons)){
    wp_enqueue_script('ticket-notify-app', JSST_PLUGIN_URL . 'includes/js/firebase-app.js', array(), jssupportticket::$_config['productversion'], true);
    wp_enqueue_script('ticket-notify-message', JSST_PLUGIN_URL . 'includes/js/firebase-messaging.js', array(), jssupportticket::$_config['productversion'], true);
}
JSSTmessage::getMessage();
if(in_array('notification', jssupportticket::$_active_addons)){
    if(jssupportticket::$jsst_data[0]['apiKey_firebase'] != "" && jssupportticket::$jsst_data[0]['databaseURL_firebase'] != "" && jssupportticket::$jsst_data[0]['authDomain_firebase'] != "" && jssupportticket::$jsst_data[0]['projectId_firebase'] != "" && jssupportticket::$jsst_data[0]['storageBucket_firebase'] != "" && jssupportticket::$jsst_data[0]['messagingSenderId_firebase'] != "" && jssupportticket::$jsst_data[0]['server_key_firebase'] != ""){
        do_action('jsst_ticket-notify-generate-token');
    }
}
if(isset(jssupportticket::$jsst_data["jsstconfigid"])){
    $jsst_jsstconfigid = jssupportticket::$jsst_data["jsstconfigid"];
} else {
    $jsst_jsstconfigid =  "";
}
$jsst_jssupportticket_js ='
    jQuery(document).ready(function () {
        jQuery(".js-support-ticket-configurations-toggle").click(function(){
      	    jQuery(".js-support-ticket-configurations .js-support-ticket-configurations-left").toggle();
        });
        jQuery("form.js-support-ticket-configurations").on("submit", function(e) {
            var jsstconfigid = "'. esc_js($jsst_jsstconfigid) .'";
        });';
        $jsst_jssupportticket_js .='

        var jsstconfigid = "'. esc_js($jsst_jsstconfigid) .'";
        if (jsstconfigid == "general") {
            jQuery("#general").css("display","inline-block");
            jQuery("#cn_gen").addClass("active");
        }else if (jsstconfigid == "ticketsettig") {
            jQuery("#ticketsettig").css("display","inline-block");
            jQuery("#cn_ts").addClass("active");
        }else if (jsstconfigid == "defaultemail") {
            jQuery("#defaultemail").css("display","inline-block");
            jQuery("#cn_dm").addClass("active");
        }else if (jsstconfigid == "mailsetting") {
            jQuery("#mailsetting").css("display","inline-block");
            jQuery("#cn_ms").addClass("active");
        }else if (jsstconfigid == "staffmenusetting") {
            jQuery("#staffmenusetting").css("display","inline-block");
            jQuery("#cn_sms").addClass("active");
        }else if (jsstconfigid == "usermenusetting") {
            jQuery("#usermenusetting").css("display","inline-block");
            jQuery("#cn_ums").addClass("active");
        }else if (jsstconfigid == "feedback") {
            jQuery("#feedback").css("display","inline-block");
            jQuery("#cn_fb").addClass("active");
        }else if (jsstconfigid == "sociallogin") {
            jQuery("#sociallogin").css("display","inline-block");
            jQuery("#cn_sl").addClass("active");
        }else if (jsstconfigid == "ticketviaemail") {
            jQuery("#ticketviaemail").css("display","inline-block");
            jQuery("#cn_tve").addClass("active");
        }else if (jsstconfigid == "pushnotification") {
            jQuery("#pushnotification").css("display","inline-block");
            jQuery("#cn_pn").addClass("active");
        }else if (jsstconfigid == "privatecredentials") {
            jQuery("#privatecredentials").css("display","inline-block");
            jQuery("#cn_pc").addClass("active");
        }else if (jsstconfigid == "envatovalidation") {
            jQuery("#envatovalidation").css("display","inline-block");
            jQuery("#cn_ev").addClass("active");
        }else if (jsstconfigid == "mailchimp") {
            jQuery("#mailchimp").css("display","inline-block");
            jQuery("#cn_mc").addClass("active");
        }else if (jsstconfigid == "easydigitaldownloads") {
            jQuery("#easydigitaldownloads").css("display","inline-block");
            jQuery("#cn_edd").addClass("active");
        }else if (jsstconfigid == "captcha") {
            jQuery("#captcha").css("display","inline-block");
            jQuery("#cn_cap").addClass("active");
        }else if (jsstconfigid == "autocleanup") {
            jQuery("#autocleanup").css("display","inline-block");
            jQuery("#cn_ac").addClass("active");
        }else if (jsstconfigid == "aiagent") {
            jQuery("#aiagent").css("display","inline-block");
            jQuery("#cn_ir").addClass("active");
        }else{
            jQuery("#general").css("display","inline-block");
            jQuery("#cn_gen").addClass("active");
        }

        // Tab highlighting is handled by the scrollspy in the config UX script below,
        // which keys off each tab link href instead of the old data-jsst-tab attribute.

        jQuery("select#ticket_overdue_type").change(function(){
            var isselect = jQuery("select#ticket_overdue_type").val();
            if(isselect == 1){
                jQuery("span.ticket_overdue_type_text").html("'. esc_html(__("Days", "js-support-ticket")).'");
            }else{
                jQuery("span.ticket_overdue_type_text").html("'. esc_html(__("Hours", "js-support-ticket")).'");
            }
        });
    });
    function showhidehostname(value){
        if(value == 4){
            jQuery("div#tve_hostname").show();
        }else{
            jQuery("div#tve_hostname").hide();
        }
    }
    function deleteSupportCustomImage(){
       jQuery.post(ajaxurl, {action: "jsticket_ajax", jstmod: "configuration", task: "deleteSupportCustomImage", "_wpnonce":"'.esc_attr(wp_create_nonce("delete-support-customimage")).'"}, function (data) {
        if(data){
          jQuery(".js-ticket-configuration-img").addClass("visible");
        }
      });
    }

    jQuery(document).ready(function () {
        jQuery("select#set_login_link").change(function(){
            var value = jQuery(this).val();
            if (value == 2) {
               jQuery(".loginlink_field").attr("style","display: block");
            } else {
                jQuery(".loginlink_field").attr("style","display: none");
            }
        })

        var value = jQuery("select#set_login_link").val();
        if (value == 2) {
           jQuery(".loginlink_field").attr("style","display: block");
        } else {
            jQuery(".loginlink_field").attr("style","display: none");
        }

        jQuery("select#set_register_link").change(function(){
            var value = jQuery(this).val();
            if (value == 2) {
               jQuery(".registerlink_field").attr("style","display: block");
            } else {
                jQuery(".registerlink_field").attr("style","display: none");
            }
        });

        var value = jQuery("select#set_register_link").val();
        if (value == 2){
           jQuery(".registerlink_field").attr("style","display: block");
        } else {
            jQuery(".registerlink_field").attr("style","display: none");
        }

    });

    // for hide and show baseb on custom fields
    jQuery(document).ready(function () {
        jQuery("select#ticketid_sequence").change(function(){
           var value = jQuery(this).val();
            if (value == 2){
                jQuery(".Ticketid-sequence-custom").slideDown("slow");
            }else{
                jQuery(".Ticketid-sequence-custom").slideUp("slow");
            }
            setpadZerosText();
        });
        var value = jQuery("select#ticketid_sequence").val();
        if (value == 2){
            jQuery(".Ticketid-sequence-custom").css("display","inline-block");
        } else {
            jQuery(".Ticketid-sequence-custom").css("display","none");
        }

        // for prefix and suffix
        jQuery("#padZeros-prefix").text(jQuery("#prefix_ticketid").val());
        jQuery("#padZeros-suffix").text(jQuery("#suffix_ticketid").val());
        jQuery("#prefix_ticketid").on("input", function(){
            jQuery("#padZeros-prefix").text(jQuery(this).val());
        });
        jQuery("#suffix_ticketid").on("input", function(){
            jQuery("#padZeros-suffix").text(jQuery(this).val());
        });

        // for pad zeroes
        jQuery("select#padding_zeros_ticketid").change(function(){
           setpadZerosText();
        });
        setpadZerosText();
        
    });

    function setpadZerosText() {
        var value = jQuery("select#ticketid_sequence").val();
        if (value == 1){
            jQuery("#padZeros").text("xxxxxxx");
        } else {
            var value = jQuery("select#padding_zeros_ticketid").val();
            if (value == 1){
                jQuery("#padZeros").text("1");
            } else if (value == 2) {
                jQuery("#padZeros").text("01");
            } else if (value == 3) {
                jQuery("#padZeros").text("001");
            } else if (value == 4) {
                jQuery("#padZeros").text("0001");
            } else if (value == 5) {
                jQuery("#padZeros").text("00001");
            } else if (value == 6) {
                jQuery("#padZeros").text("000001");
            }
        }
    }
';
wp_add_inline_script('js-support-ticket-main-js',$jsst_jssupportticket_js);

/* Configurations page usability layer: cross-section search, sticky navigation,
   tab scrollspy and unsaved-change tracking. (Roadmap 4.0-UX-01) */
$jsst_config_ux_js = <<<'JSSTUX'
/* JS Help Desk - Configurations page usability layer.
   Search across every setting, sticky navigation, tab scrollspy and an
   unsaved-changes guard. All of it is progressive: if this script does not
   run the page still renders and saves exactly as before. */
(function ($) {
    'use strict';

    var $wrap = $('form.js-support-ticket-configurations');
    if (!$wrap.length) { return; }

    var $sections    = $wrap.find('.jsstadmin-hide-config');
    var $sideItems   = $wrap.find('.js-support-ticket-configurations-left li.treeview');
    var $searchInput = $('#jsst-config-search-input');
    var $searchWrap  = $('.jsst-config-search');
    var $results     = $('#jsst-config-search-results');
    var $clearBtn    = $('.jsst-config-search-clear');
    var $countLabel  = $('.jsst-config-changecount');

    /* ---------- section <-> sidebar map ---------- */

    var sectionMeta = {};   // section id -> {label, $li}

    $sideItems.each(function () {
        var $li   = $(this);
        var href  = $li.children('a').attr('href') || '';
        var match = href.match(/jsstconfigid=([a-z]+)/i);
        if (!match) { return; }
        sectionMeta[match[1]] = {
            label: $.trim($li.children('a').find('.jsst_text').text()),
            $li: $li
        };
    });

    function currentSectionId() {
        var id = 'general';
        $sections.each(function () {
            if ($(this).css('display') !== 'none') { id = this.id; return false; }
        });
        return id;
    }

    /* Show one section without reloading the page, so pending edits survive. */
    function showSection(id, anchor) {
        if (!document.getElementById(id)) { id = 'general'; }

        $sections.each(function () {
            this.style.display = (this.id === id) ? 'inline-block' : '';
        });
        $sideItems.removeClass('active');
        if (sectionMeta[id]) { sectionMeta[id].$li.addClass('active'); }

        try {
            var url = new URL(window.location.href);
            url.searchParams.set('jsstconfigid', id);
            url.hash = anchor ? ('#' + anchor) : '';
            window.history.replaceState(null, '', url.toString());
        } catch (e) { /* older browsers just keep the old URL */ }

        if (anchor) {
            scrollToTarget(document.getElementById(anchor));
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        syncTabs();
    }

    /* The CSS ships sensible defaults for these; measuring keeps the sticky bars
       aligned when the admin bar, the search box or the save bar render at
       another height (zoom, larger base font, a translated label that wraps).
       The save bar's height is what keeps the section menu from ending
       underneath it, which hid its last entry. */
    function syncStickyVars() {
        var bar = document.getElementById('wpadminbar');
        var el  = $wrap[0];
        el.style.setProperty('--jsst-admin-bar', (bar ? bar.offsetHeight : 0) + 'px');
        if ($searchWrap.length) {
            el.style.setProperty('--jsst-search-h', $searchWrap[0].offsetHeight + 'px');
        }
        var $savebar = $wrap.find('.jsst-config-savebar').first();
        if ($savebar.length) {
            el.style.setProperty('--jsst-savebar-h', $savebar[0].offsetHeight + 'px');
        }
    }

    function stickyOffset() {
        var bar  = document.getElementById('wpadminbar');
        var tabs = $('#' + currentSectionId()).find('.config-tabs').first()[0];
        return (bar ? bar.offsetHeight : 0)
             + ($searchWrap.length ? $searchWrap[0].offsetHeight : 0)
             + (tabs ? tabs.offsetHeight : 0) + 12;
    }

    function scrollToTarget(el) {
        if (!el) { return; }
        var top = $(el).offset().top - stickyOffset();
        window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
    }

    function flash(el) {
        if (!el) { return; }
        var $el = $(el);
        $el.addClass('jsst-config-flash');
        window.setTimeout(function () { $el.removeClass('jsst-config-flash'); }, 1600);
    }

    /* Sidebar links switch section in place instead of reloading. */
    $wrap.on('click', '.js-support-ticket-configurations-left a[href*="jsstconfigid="]', function (e) {
        var href  = $(this).attr('href') || '';
        var match = href.match(/jsstconfigid=([a-z]+)/i);
        if (!match) { return; }
        e.preventDefault();
        var hash = href.indexOf('#') > -1 ? href.split('#')[1] : '';
        showSection(match[1], hash);
        $searchWrap.removeClass('jsst-config-search-open');
    });

    /* ---------- tab links + scrollspy ---------- */

    $wrap.on('click', '.jsst_tabs .tab-link a', function (e) {
        var id = ($(this).attr('href') || '').replace('#', '');
        var el = document.getElementById(id);
        if (!el) { return; }
        e.preventDefault();
        scrollToTarget(el);
    });

    function syncTabs() {
        var $section = $('#' + currentSectionId());
        var $links   = $section.find('.jsst_tabs .tab-link');
        if ($links.length < 2) { return; }

        /* offset().top is document-relative, so the reading line has to be too. */
        var line = (window.pageYOffset || document.documentElement.scrollTop) + stickyOffset() + 24;
        var $active = $links.eq(0);

        $links.each(function () {
            var id = ($(this).find('a').attr('href') || '').replace('#', '');
            var el = document.getElementById(id);
            /* offsetParent is null while a section is hidden; measuring it then
               would report top 0 for every heading and always pick the last tab. */
            if (!el || el.offsetParent === null) { return; }
            if ($(el).offset().top <= line) { $active = $(this); }
        });

        $links.removeClass('jsst_current_tab');
        $active.addClass('jsst_current_tab');
    }

    /* ---------- search index ---------- */

    var index = [];

    $sections.each(function () {
        var sectionId    = this.id;
        var sectionLabel = sectionMeta[sectionId] ? sectionMeta[sectionId].label : sectionId;

        $(this).find('.js-ticket-configuration-row, .js-ticket-configuration-row-mail').each(function (i) {
            var $row   = $(this);
            var title  = $.trim($row.find('.js-ticket-configuration-title').first().text());
            if (!title) { return; }

            var $body  = $row.closest('.jsst_gen_body');
            var desc   = $.trim($row.find('.js-ticket-configuration-description').text());
            var group  = $.trim($body.find('h2').first().text());

            if (!$row.attr('id')) { $row.attr('id', 'jsst-cfg-row-' + sectionId + '-' + i); }

            index.push({
                id: $row.attr('id'),
                title: title,
                group: group,
                section: sectionId,
                sectionLabel: sectionLabel,
                haystack: (title + ' ' + group + ' ' + sectionLabel + ' ' + desc).toLowerCase()
            });
        });
    });

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function highlight(text, terms) {
        var out = escapeHtml(text);
        terms.forEach(function (term) {
            if (!term) { return; }
            var re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
            out = out.replace(re, '<mark>$1</mark>');
        });
        return out;
    }

    var MAX_RESULTS = 40;

    function search(query) {
        var terms = query.toLowerCase().split(/\s+/).filter(Boolean);
        if (!terms.length) { return []; }

        var scored = [];
        index.forEach(function (item) {
            var all = terms.every(function (t) { return item.haystack.indexOf(t) > -1; });
            if (!all) { return; }
            /* Name matches rank above description-only matches. */
            var lowerTitle = item.title.toLowerCase();
            var score = terms.every(function (t) { return lowerTitle.indexOf(t) > -1; }) ? 0 : 1;
            if (lowerTitle.indexOf(terms[0]) === 0) { score = -1; }
            scored.push({ item: item, score: score });
        });

        scored.sort(function (a, b) { return a.score - b.score; });
        scored = scored.slice(0, MAX_RESULTS);

        /* Collect each group in the order its best hit appeared, so a heading is
           printed once instead of every time the ranking flips back to it. */
        var order = [], byGroup = {};
        scored.forEach(function (s) {
            var key = s.item.sectionLabel + ' › ' + s.item.group;
            if (!byGroup[key]) { byGroup[key] = []; order.push(key); }
            byGroup[key].push(s.item);
        });

        return order.map(function (key) { return { group: key, items: byGroup[key] }; });
    }

    function renderResults(query) {
        var groups = search(query);
        var terms  = query.toLowerCase().split(/\s+/).filter(Boolean);
        var total  = groups.reduce(function (n, g) { return n + g.items.length; }, 0);

        if (!total) {
            $results.html('<div class="jsst-config-search-empty">%%NORESULTS%%</div>');
        } else {
            var html = '';
            groups.forEach(function (group) {
                html += '<div class="jsst-config-search-group">' + escapeHtml(group.group) + '</div>';
                group.items.forEach(function (item) {
                    html += '<a class="jsst-config-search-hit" role="option" href="#" ' +
                            'data-section="' + escapeHtml(item.section) + '" ' +
                            'data-row="' + escapeHtml(item.id) + '">' +
                            highlight(item.title, terms) + '</a>';
                });
            });
            html += '<div class="jsst-config-search-count">' +
                    escapeHtml(total >= MAX_RESULTS
                        ? '%%TOPN%%'.replace('%d', MAX_RESULTS)
                        : '%%NMATCHES%%'.replace('%d', total)) +
                    '</div>';
            $results.html(html);
        }

        $results.prop('hidden', false);
        $searchInput.attr('aria-expanded', 'true');
        $searchWrap.addClass('jsst-config-search-open');
    }

    function closeResults() {
        $results.prop('hidden', true).empty();
        $searchInput.attr('aria-expanded', 'false');
        $searchWrap.removeClass('jsst-config-search-open');
    }

    function gotoHit($hit) {
        var section = $hit.data('section');
        var rowId   = $hit.data('row');

        if (section !== currentSectionId()) {
            $sections.each(function () {
                this.style.display = (this.id === section) ? 'inline-block' : '';
            });
            $sideItems.removeClass('active');
            if (sectionMeta[section]) { sectionMeta[section].$li.addClass('active'); }
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('jsstconfigid', section);
                window.history.replaceState(null, '', url.toString());
            } catch (e) { /* no-op */ }
        }

        var row = document.getElementById(rowId);
        scrollToTarget(row);
        flash(row);
        closeResults();
        syncTabs();
    }

    var searchTimer = null;

    $searchInput.on('input', function () {
        var value = $.trim(this.value);
        $clearBtn.prop('hidden', value === '');
        $searchInput.closest('.jsst-config-search-box').toggleClass('jsst-has-query', value !== '');
        window.clearTimeout(searchTimer);
        if (value.length < 2) { closeResults(); return; }
        searchTimer = window.setTimeout(function () { renderResults(value); }, 120);
    });

    /* "/" jumps to the search box, the way most search-first UIs behave. */
    $(document).on('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) { return; }
        var tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'select' || tag === 'textarea' || e.target.isContentEditable) { return; }
        e.preventDefault();
        $searchInput.focus();
    });

    /* Enter inside the search box must never submit the configuration form. */
    $searchInput.on('keydown', function (e) {
        var $hits = $results.find('.jsst-config-search-hit');
        var $cur  = $hits.filter('.jsst-config-search-active');

        if (e.key === 'Enter') {
            e.preventDefault();
            if ($cur.length) { gotoHit($cur); }
            else if ($hits.length) { gotoHit($hits.eq(0)); }
            return;
        }
        if (e.key === 'Escape') { closeResults(); this.blur(); return; }
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') { return; }
        if (!$hits.length) { return; }

        e.preventDefault();
        var i = $hits.index($cur);
        i = (e.key === 'ArrowDown') ? (i + 1) % $hits.length
                                    : (i <= 0 ? $hits.length - 1 : i - 1);
        $hits.removeClass('jsst-config-search-active');
        $hits.eq(i).addClass('jsst-config-search-active')[0]
             .scrollIntoView({ block: 'nearest' });
    });

    $results.on('click', '.jsst-config-search-hit', function (e) {
        e.preventDefault();
        gotoHit($(this));
    });

    $clearBtn.on('click', function () {
        $searchInput.val('').focus();
        $clearBtn.prop('hidden', true);
        $searchInput.closest('.jsst-config-search-box').removeClass('jsst-has-query');
        closeResults();
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.jsst-config-search').length) { closeResults(); }
    });

    /* ---------- unsaved-changes tracking ---------- */

    /* TinyMCE-backed textareas are left out: their value only syncs on submit,
       so including them would report edits the admin never made. */
    var $tracked = $wrap.find('input, select, textarea')
        .not('[type="submit"], [type="button"], [type="hidden"], [type="file"], #jsst-config-search-input')
        .filter(function () { return !$(this).closest('.wp-editor-wrap').length; });

    function valueOf(el) {
        if (el.type === 'checkbox' || el.type === 'radio') { return el.checked ? '1' : '0'; }
        return el.value;
    }

    $tracked.each(function () { this.setAttribute('data-jsst-initial', valueOf(this)); });

    var saving = false;

    function refreshDirty() {
        var dirty = 0;
        $tracked.each(function () {
            var changed = valueOf(this) !== this.getAttribute('data-jsst-initial');
            $(this).closest('.js-ticket-configuration-row, .js-ticket-configuration-row-mail')
                   .toggleClass('jsst-config-dirty', changed);
            if (changed) { dirty++; }
        });

        $wrap.toggleClass('jsst-config-has-changes', dirty > 0);
        $countLabel.text(dirty === 0 ? ''
            : (dirty === 1 ? '%%ONECHANGE%%' : '%%NCHANGES%%'.replace('%d', dirty)));
        // The pill can make the save bar taller, and the section menu sizes
        // itself against that height.
        syncStickyVars();
        return dirty;
    }

    $wrap.on('change input', 'input, select, textarea', function () {
        if (this.id === 'jsst-config-search-input') { return; }
        refreshDirty();
    });

    $wrap.on('submit', function () { saving = true; });

    $(window).on('beforeunload', function (e) {
        if (saving || !$wrap.hasClass('jsst-config-has-changes')) { return; }
        e.preventDefault();
        e.originalEvent.returnValue = '';
        return '';
    });

    /* ---------- boot ---------- */

    /* The toolbar only casts a shadow once content scrolls beneath it. */
    function syncStuck() {
        if (!$searchWrap.length) { return; }
        var bar = document.getElementById('wpadminbar');
        var top = $searchWrap[0].getBoundingClientRect().top;
        $searchWrap.toggleClass('jsst-is-stuck', top <= (bar ? bar.offsetHeight : 0) + 1);
    }

    var scrollTimer = null;
    $(window).on('scroll resize', function () {
        syncStuck();
        window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(function () { syncStickyVars(); syncTabs(); }, 60);
    });

    /* The legacy inline script above registers its ready handler first, so this
       one runs after it - by then the jsstconfigid section is actually visible
       and the tab positions can be measured. */
    $(function () {
        var id = currentSectionId();
        if (sectionMeta[id]) { sectionMeta[id].$li.addClass('active'); }
        syncStickyVars();
        syncStuck();
        syncTabs();
        if (window.location.hash) {
            var el = document.getElementById(window.location.hash.replace('#', ''));
            if (el) { scrollToTarget(el); }
        }
    });

}(jQuery));
JSSTUX;

$jsst_config_ux_js = strtr($jsst_config_ux_js, array(
    '%%NORESULTS%%'  => esc_js(__('No setting matches your search.', 'js-support-ticket')),
    /* translators: %d: number of settings matching the search. */
    '%%NMATCHES%%'   => esc_js(__('%d settings found', 'js-support-ticket')),
    /* translators: %d: number of matches shown out of a longer list. */
    '%%TOPN%%'       => esc_js(__('Showing the first %d matches - keep typing to narrow them down', 'js-support-ticket')),
    '%%ONECHANGE%%'  => esc_js(__('1 unsaved change', 'js-support-ticket')),
    /* translators: %d: number of settings changed but not yet saved. */
    '%%NCHANGES%%'   => esc_js(__('%d unsaved changes', 'js-support-ticket')),
));
wp_add_inline_script('js-support-ticket-main-js', $jsst_config_ux_js);

$jsst_owncaptchaoparend = array(
    (object) array('id' => '2', 'text' => '2'),
    (object) array('id' => '3', 'text' => '3')
);
$jsst_owncaptchatype = array(
    (object) array('id' => '0', 'text' => esc_html(__('Any', 'js-support-ticket'))),
    (object) array('id' => '1', 'text' => esc_html(__('Addition', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Subtraction', 'js-support-ticket')))
);
// Human verification providers. (Roadmap 4.0-SEC-01)
$jsst_captcha_providers = array(
    (object) array('id' => 'builtin', 'text' => esc_html(__('Built-in invisible check (no third-party service)', 'js-support-ticket'))),
    (object) array('id' => 'turnstile', 'text' => esc_html(__('Cloudflare Turnstile', 'js-support-ticket'))),
    (object) array('id' => 'hcaptcha', 'text' => esc_html(__('hCaptcha', 'js-support-ticket'))),
    (object) array('id' => 'recaptcha_v3', 'text' => esc_html(__('Google reCAPTCHA v3 (score)', 'js-support-ticket'))),
    (object) array('id' => 'recaptcha_v2', 'text' => esc_html(__('Google reCAPTCHA v2 (checkbox)', 'js-support-ticket'))),
    (object) array('id' => 'none', 'text' => esc_html(__('No verification', 'js-support-ticket')))
);
$jsst_yesno = array(
    (object) array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))
);
$jsst_showhide = array(
    (object) array('id' => '1', 'text' => esc_html(__('Show', 'js-support-ticket'))),
    (object) array('id' => '0', 'text' => esc_html(__('Hide', 'js-support-ticket')))
);
$jsst_defaultcustom = array(
    (object) array('id' => '1', 'text' => esc_html(__('JS Help Desk Login Page', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('WordPress Default Login Page', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Custom', 'js-support-ticket')))
);
$jsst_defaultregisterpage = array(
    (object) array('id' => '1', 'text' => esc_html(__('JS Help Desk Register Page', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('WordPress Default Register Page', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Custom', 'js-support-ticket')))
);
$jsst_screentagposition = array(
    (object) array('id' => '1', 'text' => esc_html(__('Top left', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Top right', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Middle left', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('Middle right', 'js-support-ticket'))),
    (object) array('id' => '5', 'text' => esc_html(__('Bottom left', 'js-support-ticket'))),
    (object) array('id' => '6', 'text' => esc_html(__('Bottom right', 'js-support-ticket')))
);
$jsst_enableddisabled = array(
    (object) array('id' => '1', 'text' => esc_html(__('Enabled', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Disabled', 'js-support-ticket')))
);
$jsst_mailreadtype = array(
    (object) array('id' => '1', 'text' => esc_html(__('Only New Tickets', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Only Replies', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Both', 'js-support-ticket')))
);

$jsst_sequence = array(
    (object) array('id' => '1', 'text' => esc_html(__('Random', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Sequence', 'js-support-ticket')))
);

$jsst_padZeros = array(
    (object) array('id' => '1', 'text' => esc_html('1')),
    (object) array('id' => '2', 'text' => esc_html('2')),
    (object) array('id' => '3', 'text' => esc_html('3')),
    (object) array('id' => '4', 'text' => esc_html('4')),
    (object) array('id' => '5', 'text' => esc_html('5')),
    (object) array('id' => '6', 'text' => esc_html('6'))
);

$jsst_hosttype = array(
    (object) array('id' => '1', 'text' => esc_html(__('Gmail', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Yahoo', 'js-support-ticket'))),
    (object) array('id' => '3', 'text' => esc_html(__('Aol', 'js-support-ticket'))),
    (object) array('id' => '4', 'text' => esc_html(__('Other', 'js-support-ticket')))
);

$jsst_ticketordering = array(
    (object) array('id' => '1', 'text' => esc_html(__('Default', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Created', 'js-support-ticket')))
);

$jsst_repliesordering = array(
    (object) array('id' => 'ASC', 'text' => esc_html(__('Oldest First', 'js-support-ticket'))),
    (object) array('id' => 'DESC', 'text' => esc_html(__('Newest First', 'js-support-ticket')))
);
$jsst_ticketsorting = array(
    (object) array('id' => '1', 'text' => esc_html(__('Ascending', 'js-support-ticket'))),
    (object) array('id' => '2', 'text' => esc_html(__('Descending', 'js-support-ticket')))
);
// Roles offered for self-registration. Anything that can administer the site,
// manage users, publish other people's content or work tickets is left out, and
// the reason is shown under the field. (Roadmap 4.0-CORE-07)
$jsst_userroles = JSSTregistrationrole::options();
$jsst_refusedroles = JSSTregistrationrole::refusedNames();
$jsst_plugin_array = get_option('active_plugins');
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title' => __('Configurations', 'js-support-ticket'),
        )); ?>
        <div id="jsstadmin-data-wrp" class="p0 bs-n bg-n">
            <form method="post" class="js-support-ticket-configurations" action="<?php echo esc_url(wp_nonce_url(admin_url("?page=configuration&task=saveconfiguration"),"save-configuration")); ?>" enctype="multipart/form-data">
              <div class="js-support-ticket-configurations-toggle">
                <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <span class="jsst_text"><?php echo esc_html(__('Select Configuration' , 'js-support-ticket')); ?> </span>
              </div>
            <?php // Search across every setting on the page, in all sections at once. ?>
            <div class="jsst-config-search">
              <div class="jsst-config-search-box">
                <svg class="jsst-config-search-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"></circle><line x1="16.5" y1="16.5" x2="21" y2="21"></line></svg>
                <label class="screen-reader-text" for="jsst-config-search-input"><?php echo esc_html(__('Search settings', 'js-support-ticket')); ?></label>
                <input type="text" id="jsst-config-search-input" class="jsst-config-search-input" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="jsst-config-search-results" placeholder="<?php echo esc_attr(__('Search all settings by name or description…', 'js-support-ticket')); ?>" />
                <kbd class="jsst-config-search-kbd" aria-hidden="true">/</kbd>
                <button type="button" class="jsst-config-search-clear" hidden aria-label="<?php echo esc_attr(__('Clear search', 'js-support-ticket')); ?>">&times;</button>
                <?php // Inside the box so it anchors to the input in both LTR and RTL. ?>
                <div class="jsst-config-search-results" id="jsst-config-search-results" role="listbox" hidden></div>
              </div>
            </div>
            <div class="js-support-ticket-configurations-left">
              <ul class="jsstadmin-sidebar-menu tree accordion" data-widget="tree">
                <li class="treeview" id="cn_gen">
                    <a href="?page=configuration&jsstconfigid=general" title="<?php echo esc_attr(__('General' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('General' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=general"><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#TicketDefault"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#login"><?php echo esc_html(__('Login', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#register"><?php echo esc_html(__('Register', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#SupportIcons"><?php echo esc_html(__('Support Icon', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=general#Offline"><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></a></li>
                      <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $jsst_plugin_array)){ ?>
                        <li><a href="?page=configuration&jsstconfigid=general#PaidSupport"><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></a></li>
                      <?php } ?>
                    </ul>
                </li>
                <li class="treeview" id="cn_ts">
                    <a href="?page=configuration&jsstconfigid=ticketsettig" title="<?php echo esc_attr(__('Ticket Settings' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('Ticket Settings' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig"><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig#TicketListing"><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=ticketsettig#TS_visitorTs"><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_dm">
                    <a href="?page=configuration&jsstconfigid=defaultemail" title="<?php echo esc_attr(__('System Emails' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('System Emails' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=defaultemail"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_cap">
                    <a href="?page=configuration&jsstconfigid=captcha" title="<?php echo esc_attr(__('Captcha' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('Captcha' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=captcha"><?php echo esc_html(__('Captcha', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <li class="treeview" id="cn_ms">
                    <a href="?page=configuration&jsstconfigid=mailsetting" title="<?php echo esc_attr(__('Email Settings' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('Email Settings' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <?php if(isset(jssupportticket::$jsst_data[0]['banemail_mail_to_admin'])){ ?>
                        <li><a href="?page=configuration&jsstconfigid=mailsetting#BanEmailNewTicket"><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></a></li>
                        <?php } ?>
                      <li><a href="?page=configuration&jsstconfigid=mailsetting#TicketOperationsEmailSetting"><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_sms">
                      <a href="?page=configuration&jsstconfigid=staffmenusetting" title="<?php echo esc_attr(__('Agent Menu' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Agent Menu' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=staffmenusetting"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                        <li><a href="?page=configuration&jsstconfigid=staffmenusetting#TopMenuLinks"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <li class="treeview" id="cn_ums">
                    <a href="?page=configuration&jsstconfigid=usermenusetting" title="<?php echo esc_attr(__('User Menu' , 'js-support-ticket')); ?>">
                        <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <span class="jsst_text"><?php echo esc_html(__('User Menu' , 'js-support-ticket')); ?> </span>
                    </a>
                    <ul class="jsstadmin-sidebar-submenu treeview-menu">
                      <li><a href="?page=configuration&jsstconfigid=usermenusetting"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li><a href="?page=configuration&jsstconfigid=usermenusetting#TopMenuLinksUser"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                    </ul>
                </li>
                <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_fb">
                      <a href="?page=configuration&jsstconfigid=feedback" title="<?php echo esc_attr(__('Feedback' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Feedback' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=feedback"><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('emailpiping', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_tve">
                      <a href="?page=configuration&jsstconfigid=ticketviaemail" title="<?php echo esc_attr(__('Email Piping' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Email Piping' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=ticketviaemail"><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('notification', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_pn">
                      <a href="?page=configuration&jsstconfigid=pushnotification" title="<?php echo esc_attr(__('Push Notifications' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Push Notifications' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=pushnotification"><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_pc">
                      <a href="?page=configuration&jsstconfigid=privatecredentials" title="<?php echo esc_attr(__('Private Credentials' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Private Credentials' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=privatecredentials"><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('envatovalidation', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_ev">
                      <a href="?page=configuration&jsstconfigid=envatovalidation" title="<?php echo esc_attr(__('Envato Validation' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Envato Validation' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=envatovalidation"><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('mailchimp', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_mc">
                      <a href="?page=configuration&jsstconfigid=mailchimp" title="<?php echo esc_attr(__('Mailchimp' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Mailchimp' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=mailchimp"><?php echo esc_html(__('Mailchimp', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
                <?php if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){ ?>
                    <li class="treeview" id="cn_edd">
                        <a href="?page=configuration&jsstconfigid=easydigitaldownloads" title="<?php echo esc_attr(__('Easy Digital Downloads' , 'js-support-ticket')); ?>">
                            <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            <span class="jsst_text"><?php echo esc_html(__('Easy Digital Downloads' , 'js-support-ticket')); ?> </span>
                        </a>
                        <ul class="jsstadmin-sidebar-submenu treeview-menu">
                            <li><a href="?page=configuration&jsstconfigid=easydigitaldownloads"><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></a></li>
                        </ul>
                    </li>
                <?php } ?>
                <?php if(JSSTmergedaddon::featureEnabled('autocleanup')){ ?>
                    <li class="treeview" id="cn_ac">
                        <a href="?page=configuration&jsstconfigid=autocleanup" title="<?php echo esc_attr(__('Retention' , 'js-support-ticket')); ?>">
                            <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                            <span class="jsst_text"><?php echo esc_html(__('Retention' , 'js-support-ticket')); ?> </span>
                        </a>
                        <?php /* The single child repeated the parent, which is a
                                 disclosure arrow that reveals the thing you just
                                 clicked. Gone with the rename. */ ?>
                    </li>
                <?php } ?>
                <?php /* Kept in this list, with no children, because the entry is
                         what somebody who knows these settings were here will look
                         for - and the section it opens says where they went. The
                         four sub-links are gone with the four tabs they pointed at;
                         an anchor to a heading that no longer exists just leaves
                         you at the top of the page wondering. (Roadmap 6.0-AI-01) */ ?>
                    <li class="treeview" id="cn_ir">
                        <a href="?page=configuration&jsstconfigid=aiagent" title="<?php echo esc_attr(__('AI Agent' , 'js-support-ticket')); ?>">
                            <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>
                            <span class="jsst_text"><?php echo esc_html(__('AI Agent' , 'js-support-ticket')); ?> </span>
                        </a>
                    </li>
                <?php if(in_array('sociallogin', jssupportticket::$_active_addons)){ ?>
                  <li class="treeview" id="cn_sl" style="display:none;">
                      <a href="?page=configuration&jsstconfigid=sociallogin" title="<?php echo esc_attr(__('Social Login' , 'js-support-ticket')); ?>">
                          <svg class="jsst_menu-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                          <span class="jsst_text"><?php echo esc_html(__('Social Login' , 'js-support-ticket')); ?> </span>
                      </a>
                      <ul class="jsstadmin-sidebar-submenu treeview-menu">
                        <li><a href="?page=configuration&jsstconfigid=sociallogin"><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></a></li>
                        <li><a href="?page=configuration&jsstconfigid=sociallogin#Linkedin"><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></a></li>
                      </ul>
                  </li>
                <?php } ?>
              </ul>
            </div>
            <div class="js-support-ticket-configurations-right">
            <div id="general" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#GeneralSetting"><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#TicketDefault"><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#login"><?php echo esc_html(__('Login', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#register"><?php echo esc_html(__('Register', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#SupportIcons"><?php echo esc_html(__('Support Icon', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#Offline"><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></a></li>
                      <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $jsst_plugin_array)){ ?>
                        <li class="tab-link"><a href="#PaidSupport"><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></a></li>
                      <?php } ?>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="GeneralSetting">
                  <h2><?php echo esc_html(__('General Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['title'])){
                      $jsst_title = esc_html(__('Title', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('title', jssupportticket::$jsst_data[0]['title'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Set the heading of your plugin', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['default_pageid'])){
                      $jsst_title = esc_html(__('Ticket Default Page', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('default_pageid', JSSTincluder::getJSModel('configuration')->getPageList(), jssupportticket::$jsst_data[0]['default_pageid'], esc_html(__('Select Page', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $jsst_description =  esc_html(__('Select JS Help Desk default page, on the action system, will redirect on the selected page. If not select the default page, email links, and support icon might not work.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['data_directory'])){
                      $jsst_title = esc_html(__('Data Directory', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('data_directory', jssupportticket::$jsst_data[0]['data_directory'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Set the name for your data directory', 'js-support-ticket')) .'<br>' . esc_html(__('You need to rename the existing data directory in the file system before changing the data directory name', 'js-support-ticket')) ; ?><?php //echo esc_html(__('You need to rename the existing data directory in the file system before changing the data directory name', 'js-support-ticket')));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['date_format'])){
                      $jsst_title = esc_html(__('Date Format', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('date_format', array((object) array('id' => 'd-m-Y', 'text' => esc_html(__("DD-MM-YYYY", 'js-support-ticket'))), (object) array('id' => 'm-d-Y', 'text' => esc_html(__("MM-DD-YYYY", 'js-support-ticket'))), (object) array('id' => 'Y-m-d', 'text' => esc_html(__("YYYY-MM-DD", 'js-support-ticket')))), jssupportticket::$jsst_data[0]['date_format']);
                      $jsst_description =  esc_html(__('Set the default date format', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['pagination_default_page_size'])){
                      $jsst_title = esc_html(__('Pagination Default Page Size', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('pagination_default_page_size', jssupportticket::$jsst_data[0]['pagination_default_page_size'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Set the no. of record per page', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    /*if(isset(jssupportticket::$jsst_data[0]['show_breadcrumbs'])){
                      $jsst_title = esc_html(__('Breadcrumbs', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_breadcrumbs', $jsst_showhide, jssupportticket::$jsst_data[0]['show_breadcrumbs']);
                      $jsst_description =  esc_html(__('Show hide breadcrumbs', 'js-support-ticket')));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }*/

                    if(isset(jssupportticket::$jsst_data[0]['show_header'])){
                      $jsst_title = esc_html(__('Top Header', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_header', $jsst_showhide, jssupportticket::$jsst_data[0]['show_header']);
                      $jsst_description =  esc_html(__('Show hide Top Header', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_avatar'])){
                        $jsst_title = esc_html(__('Show User Avatar', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('show_avatar', $jsst_yesno, jssupportticket::$jsst_data[0]['show_avatar']);
                        $jsst_description =  esc_html(__('Showing avatars may slightly slow down page loading', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['count_on_myticket'])){
                      $jsst_title = esc_html(__('Show Count On My Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('count_on_myticket', $jsst_yesno, jssupportticket::$jsst_data[0]['count_on_myticket']);
                      $jsst_description =  esc_html(__('Show number of the open, closed, answered ticket in my ticket and dashboard', 'js-support-ticket'));
                      $jsst_video = '9ORIFf6jPPg';
                      $jsst_videotext = 'Show Count On My Tickets';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['wp_default_role'])){
                      $jsst_title = esc_html(__('Default WordPress Role For New Users', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('wp_default_role', $jsst_userroles, JSSTregistrationrole::configured());
                      $jsst_description =  esc_html(__('The role given to anyone who registers through the support portal.', 'js-support-ticket'));
                      if (!empty($jsst_refusedroles)) {
                          $jsst_description .= ' ' . sprintf(
                              /* translators: %s: comma-separated list of role names */
                              esc_html(__('These roles are not offered, because registration is open to the public and they can administer the site, manage users, publish content or work tickets: %s.', 'js-support-ticket')),
                              esc_html(implode(', ', $jsst_refusedroles))
                          );
                      }
                      $jsst_videotext = 'Default Wp Role For New Users';
                      // The help video is part of core now, so it always shows.
                      // (Roadmap 4.0-CORE-07)
                      $jsst_video = 'T3HRojY2UN4';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    // Roadmap 3.2-CORE-02: the documented data-retention contract.
                    if(isset(jssupportticket::$jsst_data[0]['data_retention_on_uninstall'])){
                      $jsst_title = esc_html(__('Data When Uninstalling', 'js-support-ticket'));
                      $jsst_retentionoptions = array(
                          (object) array('id' => 'preserve', 'text' => esc_html(__('Keep all tickets, tables and files', 'js-support-ticket'))),
                          (object) array('id' => 'delete', 'text' => esc_html(__('Delete all tickets, tables and files', 'js-support-ticket')))
                      );
                      $jsst_field = JSSTformfield::select('data_retention_on_uninstall', $jsst_retentionoptions, jssupportticket::$jsst_data[0]['data_retention_on_uninstall']);
                      $jsst_description =  esc_html(__('What happens when the plugin is deleted from the Plugins screen. Deactivating never removes data. Deleting always removes this plugin\'s roles and capabilities; this setting decides whether it also drops the ticket tables, plugin options and the uploaded attachment directory. Deleting cannot be undone.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                  ?>
              </div>
              <div class="jsst_gen_body" id="TicketDefault">
                  <h2><?php echo esc_html(__('Attachments', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['no_of_attachement'])){
                      $jsst_title = esc_html(__('Number Of Attachments', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('no_of_attachement', jssupportticket::$jsst_data[0]['no_of_attachement'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Number of attachments allowed at a time', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                   if(isset(jssupportticket::$jsst_data[0]['file_maximum_size'])){
                      $jsst_title = esc_html(__('File Maximum Size', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('file_maximum_size', jssupportticket::$jsst_data[0]['file_maximum_size'], array('class' => 'inputbox')) ?><?php //echo esc_html(__('Kb', 'js-support-ticket'));
                      $jsst_description =  esc_html(__('Kb', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field,$jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['file_extension'])){
                      $jsst_title = esc_html(__('File Extension', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::textarea('file_extension', jssupportticket::$jsst_data[0]['file_extension'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('File extension allowed to attach', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="login">
                  <h2><?php echo esc_html(__('Login', 'js-support-ticket')); ?></h2>
                  <?php
                    // Configuration not in use
                    /*
                    if(isset(jssupportticket::$jsst_data[0]['login_redirect'])){
                      $jsst_title = esc_html(__('Login Redirect', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('login_redirect', $jsst_yesno, jssupportticket::$jsst_data[0]['login_redirect']);
                      $jsst_description =  esc_html(__('Redirect user on log in', 'js-support-ticket'));
                      $jsst_video = 'Hq1UzmUqFIA';
                      $jsst_videotext = 'Login redirect';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }
                    */

                    if(isset(jssupportticket::$jsst_data[0]['set_login_link'])){
                        $jsst_title = esc_html(__('Set Login Link', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('set_login_link', $jsst_defaultcustom, jssupportticket::$jsst_data[0]['set_login_link']);
                        $jsst_description =  esc_html(__('Set Login Link Default or Custom', 'js-support-ticket'));
                        $jsst_childfield = '';
                        $jsst_video = 'Hq1UzmUqFIA';
                        $jsst_videotext = 'Login redirect';
                        if(isset(jssupportticket::$jsst_data[0]['login_link'])){
                            $jsst_childfield = JSSTformfield::text('login_link', jssupportticket::$jsst_data[0]['login_link'], array('class' => 'inputbox loginlink_field'));
                        }
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, $jsst_childfield, $jsst_videotext);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="register">
                  <h2><?php echo esc_html(__('Register', 'js-support-ticket')); ?></h2>
                  <?php

                    if(isset(jssupportticket::$jsst_data[0]['set_register_link'])){
                      $jsst_title = esc_html(__('Set Register Link', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('set_register_link', $jsst_defaultregisterpage, jssupportticket::$jsst_data[0]['set_register_link']);
                      $jsst_description =  esc_html(__('Set register Link Default or Custom','js-support-ticket')).'.<br />'.esc_html(__(' To enable registrations, WordPress admin > General > Settings > Membership: Anyone can register', 'js-support-ticket'));
                      $jsst_childfield = '';
                      if(isset(jssupportticket::$jsst_data[0]['register_link'])){
                          $jsst_childfield = JSSTformfield::text('register_link', jssupportticket::$jsst_data[0]['register_link'], array('class' => 'inputbox registerlink_field'));
                      }
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, '', $jsst_childfield);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="SupportIcons">
                  <h2><?php echo esc_html(__('Support Icon', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['support_screentag'])){
                      $jsst_title = esc_html(__('Support Icon', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('support_screentag', $jsst_showhide, jssupportticket::$jsst_data[0]['support_screentag'], esc_html(__('Screen Tag', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $jsst_description =  esc_html(__('Enable / disable your support icon', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['support_custom_img'])){ ?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Custom Image', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value">
                            <input type="file" name="support_custom_img" id="support_custom_img"  />
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('Set custom support image', 'js-support-ticket')); ?>
                            </div>
                            <span class="js-ticket-configuration-img">
                              <?php if(jssupportticket::$jsst_data[0]['support_custom_img'] != '0'){
                                $jsst_maindir = wp_upload_dir();
                                $jsst_basedir = $jsst_maindir['baseurl'];
                                $jsst_datadirectory = jssupportticket::$_config['data_directory'];
                                $jsst_path = $jsst_basedir . '/' . $jsst_datadirectory;
                                $jsst_path .= "/supportImg/" . jssupportticket::$jsst_data[0]['support_custom_img'];
                                ?>
                                <img alt="<?php echo esc_attr(__('image','js-support-ticket')); ?>" width="50px" height="50px" src="<?php echo esc_url($jsst_path); ?>">
                                  <?php echo esc_html(jssupportticket::$jsst_data[0]['support_custom_img']) ?>
                                  <a title="<?php echo esc_attr(__('Delete','js-support-ticket')); ?>" onclick="deleteSupportCustomImage()">( <?php echo esc_html(__('Delete','js-support-ticket')); ?> )</a>
                              <?php } ?>
                            </span>
                        </div>
                      </div>
                    <?php }

                    if(isset(jssupportticket::$jsst_data[0]['support_custom_txt'])){
                        $jsst_title = esc_html(__('Custom Text', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('support_custom_txt', jssupportticket::$jsst_data[0]['support_custom_txt'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Set custom support text', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['screentag_position'])){
                      $jsst_title = esc_html(__('Support Icon Position', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('screentag_position', $jsst_screentagposition, jssupportticket::$jsst_data[0]['screentag_position'], esc_html(__('Screen Tag Position', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                      $jsst_description =  esc_html(__('Select position for your support icon', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="Offline">
                  <h2><?php echo esc_html(__('Offline', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['offline'])){
                     $jsst_title = esc_html(__('Offline', 'js-support-ticket'));
                     $jsst_field = JSSTformfield::select('offline', array((object) array('id' => '1', 'text' => esc_html(__('Offline', 'js-support-ticket'))), (object) array('id' => '2', 'text' => esc_html(__('Online', 'js-support-ticket')))), jssupportticket::$jsst_data[0]['offline']);
                     $jsst_description =  esc_html(__('Set your plugin offline for front end', 'js-support-ticket'));
                     JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                  if(isset(jssupportticket::$jsst_data[0]['offline_message'])){?>
                  <div class="js-ticket-configuration-row">
                    <div class="js-ticket-configuration-title"><?php echo esc_html(__('Offline Message', 'js-support-ticket')); ?></div>
                    <div class="js-ticket-configuration-value full-width">
                        <?php wp_editor(jssupportticket::$jsst_data[0]['offline_message'], 'offline_message', array('media_buttons' => false)); ?>
                        <div class="js-ticket-configuration-description">
                          <?php echo esc_html(__('Set the offline message for your user', 'js-support-ticket')); ?>
                        </div>
                    </div>
                  </div>
                  <?php } ?>
              </div>
                <?php if(in_array('paidsupport', jssupportticket::$_active_addons) && in_array('woocommerce/woocommerce.php', $jsst_plugin_array)){ ?>
                    <div class="jsst_gen_body" id="PaidSupport">
                        <h2><?php echo esc_html(__('Paid Support', 'js-support-ticket')); ?></h2>
                        <?php
                        if(isset(jssupportticket::$jsst_data[0]['woocommerce_default_categoryid'])){
                            $jsst_title = esc_html(__('Woocommerce Category', 'js-support-ticket'));
                            $jsst_field = JSSTformfield::select('woocommerce_default_categoryid', JSSTincluder::getJSModel('configuration')->getWooCommerceCategoryList(), jssupportticket::$jsst_data[0]['woocommerce_default_categoryid'], esc_html(__('Select Category', 'js-support-ticket')), array('class' => 'inputbox', 'data-validation' => 'required'));
                            $jsst_description =  esc_html(__('Select category to display only products of this category on the WooCommerce shop page.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
           
            <!-- .....TICKET SETTINGS.... -->
            <!-- .....TICKET SETTINGS.... -->
            <div id="ticketsettig" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#TicketSetting"><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#TicketListing"><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#TS_visitorTs"><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="TicketSetting">
                  <h2><?php echo esc_html(__('Ticket Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['prefix_ticketid'])){
                      $jsst_title = esc_html(__('Ticketid Prefix', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('prefix_ticketid', jssupportticket::$jsst_data[0]['prefix_ticketid'], array('class' => 'inputbox','maxlength' => '10'));
                      $jsst_description =  esc_html(__('Set prefix for custom ticketid', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field,$jsst_description);
                    }
                  ?>
                    <?php
                      if(isset(jssupportticket::$jsst_data[0]['ticketid_sequence'])){ ?>
                        <div class="js-ticket-configuration-row">
                          <div class="js-ticket-configuration-title"><?php echo esc_html(__('Ticketid sequence', 'js-support-ticket')); ?></div>
                          <div class="js-ticket-configuration-value">
                            <?php echo wp_kses(JSSTformfield::select('ticketid_sequence', $jsst_sequence, jssupportticket::$jsst_data[0]['ticketid_sequence']), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('Set ticketid sequential or random .e.g ', 'js-support-ticket')); ?><span id="padZeros-prefix" class="js-ticket-font-bold"></span><span id="padZeros" class="js-ticket-font-bold"></span><span id="padZeros-suffix" class="js-ticket-font-bold"></span>
                            </div>
                          </div>
                        </div>
                      <?php
                       }
                      if(isset(jssupportticket::$jsst_data[0]['padding_zeros_ticketid'])){ ?>
                        <div class="js-ticket-configuration-row Ticketid-sequence-custom">
                          <div class="js-ticket-configuration-title"><?php echo esc_html(__('Pad Zeros', 'js-support-ticket')); ?></div>
                          <div class="js-ticket-configuration-value">
                            <?php echo wp_kses(JSSTformfield::select('padding_zeros_ticketid', $jsst_padZeros, jssupportticket::$jsst_data[0]['padding_zeros_ticketid']), JSST_ALLOWED_TAGS); ?>
                            <div class="js-ticket-configuration-description">
                              <?php echo esc_html(__('To pad an integer with leading zeros to a specific length', 'js-support-ticket')); ?>
                            </div>
                          </div>
                        </div>
                      <?php
                       }
                     if(isset(jssupportticket::$jsst_data[0]['suffix_ticketid'])){
                        $jsst_title = esc_html(__('Ticketid Suffix', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('suffix_ticketid', jssupportticket::$jsst_data[0]['suffix_ticketid'], array('class' => 'inputbox','maxlength' => '7'));
                        $jsst_description =  esc_html(__('Set suffix for custom ticketid', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field,$jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['maximum_tickets'])){
                      $jsst_title = esc_html(__('Maximum Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('maximum_tickets', jssupportticket::$jsst_data[0]['maximum_tickets'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Maximum ticket per user', 'js-support-ticket'));
                      $jsst_video = 'LoALnBJnT48';
                      $jsst_videotext = 'Maximum tickets';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['maximum_open_tickets'])){
                      $jsst_title = esc_html(__('Maximum Open Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('maximum_open_tickets', jssupportticket::$jsst_data[0]['maximum_open_tickets'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Maximum opened tickets per user', 'js-support-ticket'));
                      $jsst_video = 'SJjHk50buw0';
                      $jsst_videotext = 'Maximum open tickets';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['reopen_ticket_within_days'])){
                      $jsst_title = esc_html(__('Reopen Ticket Within Days', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('reopen_ticket_within_days', jssupportticket::$jsst_data[0]['reopen_ticket_within_days'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('The ticket can be reopened within a given number of days', 'js-support-ticket'));
                      $jsst_video = 'S7KWbUHvmmk';
                      $jsst_videotext = 'Reopen Ticket Within Days';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(in_array('multiform', jssupportticket::$_active_addons)){
                        if(isset(jssupportticket::$jsst_data[0]['show_multiform_popup'])){
                          $jsst_title = esc_html(__('Multiforms Popup For New Tickets', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::select('show_multiform_popup', $jsst_showhide, jssupportticket::$jsst_data[0]['show_multiform_popup']);
                          $jsst_description =  esc_html(__('Show or hide the multiform popup when creating a new ticket. if you hide them, the system will open the default form.','js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }
                    }

                    if(isset(jssupportticket::$jsst_data[0]['print_ticket_user'])){
                      $jsst_title = esc_html(__('User Can Print Ticket', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('print_ticket_user', $jsst_yesno, jssupportticket::$jsst_data[0]['print_ticket_user']);
                      $jsst_description =  esc_html(__('Can user print ticket from ticket detail or not', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['reply_to_closed_ticket'])){
                      $jsst_title = esc_html(__('Allow Users To Reply Via Email On Closed Ticket', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('reply_to_closed_ticket', $jsst_yesno, jssupportticket::$jsst_data[0]['reply_to_closed_ticket']);
                      $jsst_description =  esc_html(__('Select whether users can reply to closed email piping ticket or not','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_email_on_ticket_reply'])){
                      $jsst_title = esc_html(__('Show Admin OR Agent Email On Ticket Reply', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_email_on_ticket_reply', $jsst_yesno, jssupportticket::$jsst_data[0]['show_email_on_ticket_reply']);
                      $jsst_description =  esc_html(__('Select whether users can see the email of administrator or agent on ticket reply','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['ticket_replies_ordering'])){
                      $jsst_title = esc_html(__('Ticket Replies Ordering', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('ticket_replies_ordering', $jsst_repliesordering, jssupportticket::$jsst_data[0]['ticket_replies_ordering']);
                      $jsst_description =  esc_html(__('Set the default ordering for ticket replies in the detail page.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['anonymous_name_on_ticket_reply'])){
                        $jsst_title = esc_html(__('Show Anonymous Name On Ticket Reply', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('anonymous_name_on_ticket_reply', $jsst_yesno, jssupportticket::$jsst_data[0]['anonymous_name_on_ticket_reply']);
                        $jsst_description =  esc_html(__('Select whether users can see the name of administrator or agent on ticket reply','js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_read_receipt_to_admin_on_reply'])){
                        $jsst_title = esc_html(__('Show Message Read Icon For Admin On Ticket Detail', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('show_read_receipt_to_admin_on_reply', $jsst_yesno, jssupportticket::$jsst_data[0]['show_read_receipt_to_admin_on_reply']);
                        $jsst_description =  esc_html(__('Select whether the message read icon is displayed to the administrator on ticket detail.','js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_read_receipt_to_agent_on_reply'])){
                        $jsst_title = esc_html(__('Show Message Read Icon For Agents On Ticket Detail', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('show_read_receipt_to_agent_on_reply', $jsst_yesno, jssupportticket::$jsst_data[0]['show_read_receipt_to_agent_on_reply']);
                        $jsst_description =  esc_html(__('Select whether the message read icon is displayed to agents on ticket detail.','js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_read_receipt_to_user_on_reply'])){
                        $jsst_title = esc_html(__('Show Message Read Icon For Users On Ticket Detail', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('show_read_receipt_to_user_on_reply', $jsst_yesno, jssupportticket::$jsst_data[0]['show_read_receipt_to_user_on_reply']);
                        $jsst_description =  esc_html(__('Select whether the message read icon is displayed to users on the ticket detail.','js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['ticket_auto_close'])){
                        $jsst_title = esc_html(__('Ticket Auto Close', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('ticket_auto_close', jssupportticket::$jsst_data[0]['ticket_auto_close'], array('class' => 'inputbox'));
                        $jsst_description = '<span class="js-ticket-configuration-sml-txt">'. esc_html(__('Days','js-support-ticket')).'</span>' . esc_html(__('Ticket auto-close if user does not respond within given days', 'js-support-ticket'));
                        $jsst_video = 'Yi3zPvGdGG4';
                        $jsst_videotext = 'Ticket Auto Close';
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_ticket_delete_button'])){
                      $jsst_title = esc_html(__('Show Ticket Delete Button', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_ticket_delete_button', $jsst_yesno, jssupportticket::$jsst_data[0]['show_ticket_delete_button']);
                      $jsst_description =  esc_html(__('Select whether users can see the ticket delete button','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['new_ticket_message'])){?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('New ticket message', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$jsst_data[0]['new_ticket_message'], 'new_ticket_message'); ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This message will show on the new ticket', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                    <?php
                    }
                  ?>
                </div>
                <div class="jsst_gen_body" id="TicketListing">
                    <h2><?php echo esc_html(__('Ticket Listing', 'js-support-ticket')); ?></h2>
                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['tickets_ordering'])){
                      $jsst_title = esc_html(__('Ticket Listing Ordering', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tickets_ordering', $jsst_ticketordering, jssupportticket::$jsst_data[0]['tickets_ordering']);
                      $jsst_description =  esc_html(__('Set default ordering for ticket listing', 'js-support-ticket'));
                      $jsst_video = 'qloE9WQM4rE';
                      $jsst_videotext = 'Ticket listing ordering';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tickets_sorting'])){
                      $jsst_title = esc_html(__('Ticket Listing Sorting', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tickets_sorting', $jsst_ticketsorting, jssupportticket::$jsst_data[0]['tickets_sorting']);
                      $jsst_description =  esc_html(__('Set default sorting for ticket listing', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_closedby_on_admin_tickets'])){
                      $jsst_title = esc_html(__('Show Closure Info On Admin Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_closedby_on_admin_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_closedby_on_admin_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, an admin can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_closedby_on_agent_tickets'])){
                      $jsst_title = esc_html(__('Show Closure Info on Agent Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_closedby_on_agent_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_closedby_on_agent_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, an agent can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_closedby_on_user_tickets'])){
                      $jsst_title = esc_html(__('Show Closure Info on User Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_closedby_on_user_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_closedby_on_user_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, a user can know who closed the ticket and when that ticket closed.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_assignto_on_admin_tickets'])){
                      $jsst_title = esc_html(__('Assigned Info. On Admin Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_assignto_on_admin_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_assignto_on_admin_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, an admin can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_assignto_on_agent_tickets'])){
                      $jsst_title = esc_html(__('Assigned Info. On Agent Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_assignto_on_agent_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_assignto_on_agent_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, an agent can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_assignto_on_user_tickets'])){
                      $jsst_title = esc_html(__('Assigned Info. On User Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_assignto_on_user_tickets', $jsst_showhide, jssupportticket::$jsst_data[0]['show_assignto_on_user_tickets']);
                      $jsst_description =  esc_html(__('By enabling this option, a user can know to whom the ticket has been assigned.','js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="TS_visitorTs">
                  <h2><?php echo esc_html(__('Visitor Ticket Setting', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['visitor_can_create_ticket'])){
                      $jsst_title = esc_html(__('Visitor Can Create Ticket', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('visitor_can_create_ticket', $jsst_yesno, jssupportticket::$jsst_data[0]['visitor_can_create_ticket']);
                      $jsst_description =  esc_html(__('Can visitor create ticket or not', 'js-support-ticket'));
                      $jsst_video = 'Gcss-ybwiXk';
                      $jsst_videotext = 'Visitor Can Create Ticket';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '',$jsst_videotext);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['visitor_message'])){?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Visitor ticket creation message', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$jsst_data[0]['visitor_message'], 'visitor_message') ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This text will appear whenever a visitor creates a ticket', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                  <?php } ?>
              </div>
            </div>

            <!-- .....SYSTEM EMAILS..... -->
            <!-- .....SYSTEM EMAILS..... -->
            <div id="defaultemail" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#SystemEmail"><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="SystemEmail">
                  <h2><?php echo esc_html(__('System Emails', 'js-support-ticket')); ?></h2>
                  <?php

                   if(isset(jssupportticket::$jsst_data[0]['default_alert_email'])){
                      $jsst_title = esc_html(__('Default Alert Email', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('default_alert_email', jssupportticket::$jsst_data[1], jssupportticket::$jsst_data[0]['default_alert_email']);
                      $jsst_description = esc_html(__('If ticket department email is not selected then this email is used to send emails', 'js-support-ticket'));
                      $jsst_video = 'dNYnZw8WK0M';
                      $jsst_videotext = 'Default alert email';
                      $jsst_actionbtn = 'Add New Email';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext, $jsst_actionbtn);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['default_admin_email'])){
                      $jsst_title = esc_html(__('Default Admin Email', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('default_admin_email', jssupportticket::$jsst_data[1], jssupportticket::$jsst_data[0]['default_admin_email']);
                      $jsst_description = esc_html(__('Admin email address to receive emails', 'js-support-ticket'));
                      $jsst_video = 'LvsrMtEqRms';
                      $jsst_videotext = 'Default admin email';
                      $jsst_actionbtn = 'Add New Email';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext, $jsst_actionbtn);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['department_email_on_ticket_create'])){
                        $jsst_title = esc_html(__('Department Email', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('department_email_on_ticket_create', $jsst_yesno, jssupportticket::$jsst_data[0]['department_email_on_ticket_create']);
                        $jsst_description =  esc_html(__('Send email to all departments on ticket create', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                  ?>
              </div>
            </div>
            <!-- .....EMAIL Settings..... -->
            <div id="mailsetting" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <?php if(isset(jssupportticket::$jsst_data[0]['banemail_mail_to_admin'])){ ?>
                      <li class="tab-link jsst_current_tab"><a href="#BanEmailNewTicket"><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></a></li>
                    <?php } ?>
                      <li class="tab-link"><a href="#TicketOperationsEmailSetting"><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <?php if(isset(jssupportticket::$jsst_data[0]['banemail_mail_to_admin'])){ ?>
                <div class="jsst_gen_body" id="BanEmailNewTicket">
                    <h2><?php echo esc_html(__('Ban Email New Ticket', 'js-support-ticket')); ?></h2>
                    <?php
                      $jsst_title = esc_html(__('Mail To Admin', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('banemail_mail_to_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['banemail_mail_to_admin']);;
                      $jsst_description = esc_html(__('Email sends to admin when banned email try to create a ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    ?>
                </div>
              <?php } ?>
              <div class="jsst_gen_body" id="TicketOperationsEmailSetting">
                  <h2><?php echo esc_html(__('Ticket Operations Email Setting', 'js-support-ticket')); ?></h2>
                  <div class="js-ticket-configuration-row-mail">
                    <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('Admin', 'js-support-ticket')); ?></div>
                    <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                      <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('Agent', 'js-support-ticket')); ?></div>
                    <?php }else{ ?>
                      <div class="js-ticket-conf-text-sub">------</div>
                    <?php } ?>
                    <div class="js-ticket-conf-text-sub"><?php echo esc_html(__('User', 'js-support-ticket')); ?></div>
                  </div>
                  <?php

                  if(isset(jssupportticket::$jsst_data[0]['new_ticket_mail_to_admin'])){
                    $jsst_title = esc_html(__('New Ticket', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('new_ticket_mail_to_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['new_ticket_mail_to_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('new_ticket_mail_to_staff_members', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['new_ticket_mail_to_staff_members']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_reassign_admin'])){
                    $jsst_title = esc_html(__('Ticket Reassign', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_reassign_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reassign_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_reassign_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reassign_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_reassign_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reassign_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_close_admin'])){
                    $jsst_title = esc_html(__('Ticket Close', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_close_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_close_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_close_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_close_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_close_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_close_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_delete_admin'])){
                    $jsst_title = esc_html(__('Ticket Delete', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_delete_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_delete_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_delete_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_delete_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_delete_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_delete_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_mark_overdue_admin'])){
                    $jsst_title = esc_html(__('Ticket Marked As Overdue', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_mark_overdue_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_overdue_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_mark_overdue_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_overdue_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_mark_overdue_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_overdue_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_ban_email_admin'])){
                    $jsst_title = esc_html(__('Ticket Ban Email', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_ban_email_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_ban_email_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_ban_email_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_ban_email_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_ban_email_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_ban_email_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_department_transfer_admin'])){
                    $jsst_title = esc_html(__('Ticket Department Transfer', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_department_transfer_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_department_transfer_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_department_transfer_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_department_transfer_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_department_transfer_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_department_transfer_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_reply_ticket_user_admin'])){
                    $jsst_title = esc_html(__('Ticket Reply User', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_reply_ticket_user_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reply_ticket_user_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_reply_ticket_user_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reply_ticket_user_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_reply_ticket_user_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reply_ticket_user_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_response_to_staff_admin'])){
                    $jsst_title = esc_html(__('Ticket Response Agent', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_response_to_staff_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_response_to_staff_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_response_to_staff_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_response_to_staff_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_response_to_staff_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_response_to_staff_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticker_ban_eamil_and_close_ticktet_admin'])){
                    $jsst_title = esc_html(__('Ticket Ban Email And Close Ticket', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticker_ban_eamil_and_close_ticktet_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticker_ban_eamil_and_close_ticktet_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticker_ban_eamil_and_close_ticktet_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticker_ban_eamil_and_close_ticktet_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['unban_email_admin'])){
                    $jsst_title = esc_html(__('Ticket Unban Email', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('unban_email_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['unban_email_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('unban_email_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['unban_email_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('unban_email_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['unban_email_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_lock_admin'])){
                    $jsst_title = esc_html(__('Ticket Lock', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_lock_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_lock_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_lock_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_lock_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_lock_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_lock_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_unlock_admin'])){
                    $jsst_title = esc_html(__('Ticket Unlock', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_unlock_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_unlock_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_unlock_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_unlock_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_unlock_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_unlock_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_priority_admin'])){
                    $jsst_title = esc_html(__('Ticket Change Priority', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_priority_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_priority_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_priority_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_priority_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_priority_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_priority_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_mark_progress_admin'])){
                    $jsst_title = esc_html(__('Mark Ticket In Progress', 'js-support-ticket'));
                    $jsst_field1 = JSSTformfield::select('ticket_mark_progress_admin', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_progress_admin']);
                    if(in_array('agent', jssupportticket::$_active_addons)){
                      $jsst_field2 = JSSTformfield::select('ticket_mark_progress_staff', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_progress_staff']);
                    }else{
                      $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'------'.'</span>';
                    }
                    $jsst_field3 = JSSTformfield::select('ticket_mark_progress_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_mark_progress_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_reply_closed_ticket_user'])){
                    $jsst_title = esc_html(__('Reply To A Closed Ticket By Email', 'js-support-ticket'));
                    $jsst_field1 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $jsst_field3 =  JSSTformfield::select('ticket_reply_closed_ticket_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_reply_closed_ticket_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  if(isset(jssupportticket::$jsst_data[0]['ticket_feedback_user'])){
                    $jsst_title = esc_html(__('Send Feedback Email To User', 'js-support-ticket'));
                    $jsst_field1 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $jsst_field2 = '<span class="js-ticket-configuration-no-rec">'.'----'.'</span>';
                    $jsst_field3 = JSSTformfield::select('ticket_feedback_user', $jsst_enableddisabled, jssupportticket::$jsst_data[0]['ticket_feedback_user']);
                    JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3);
                  }

                  ?>
              </div>
            </div>
            <!-- .....AGENT MENUS..... -->
            <!-- .....AGENT MENUS..... -->
            <div id="staffmenusetting" class="jsstadmin-hide-config">
              <?php if(in_array('agent', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#DashboardLinks"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#TopMenuLinks"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="DashboardLinks">
                  <h2><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></h2>
                  <?php

                    if(isset(jssupportticket::$jsst_data[0]['cplink_openticket_staff'])){
                        $jsst_title = esc_html(__('Open Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_openticket_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_openticket_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_myticket_staff'])){
                        $jsst_title =  esc_html(__('My Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_myticket_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_myticket_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addrole_staff'])){
                        $jsst_title = esc_html(__('Add Role', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addrole_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addrole_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_roles_staff'])){
                        $jsst_title =  esc_html(__('Roles', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_roles_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_roles_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addstaff_staff'])){
                        $jsst_title = esc_html(__('Add Agent', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addstaff_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addstaff_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_staff_staff'])){
                        $jsst_title =  esc_html(__('Agent', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_staff_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_staff_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_adddepartment_staff'])){
                        $jsst_title = esc_html(__('Add Department', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_adddepartment_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_adddepartment_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_department_staff'])){
                        $jsst_title =  esc_html(__('Department', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_department_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_department_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addcategory_staff'])){
                        $jsst_title = esc_html(__('Add Category', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addcategory_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addcategory_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_category_staff'])){
                        $jsst_title =  esc_html(__('Category', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_category_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_category_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addkbarticle_staff'])){
                        $jsst_title = esc_html(__('Add Knowledge Base', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addkbarticle_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addkbarticle_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_kbarticle_staff'])){
                        $jsst_title =  esc_html(__('Knowledge Base', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_kbarticle_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_kbarticle_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_adddownload_staff'])){
                        $jsst_title = esc_html(__('Add Download', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_adddownload_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_adddownload_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/

                    if(isset(jssupportticket::$jsst_data[0]['cplink_download_staff'])){
                        $jsst_title =  esc_html(__('Download', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_download_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_download_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addannouncement_staff'])){
                        $jsst_title = esc_html(__('Add Announcement', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addannouncement_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addannouncement_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_announcement_staff'])){
                        $jsst_title =  esc_html(__('Announcement', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_announcement_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_announcement_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    // not in use
                    /*if(isset(jssupportticket::$jsst_data[0]['cplink_addfaq_staff'])){
                        $jsst_title = esc_html(__('Add FAQ', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_addfaq_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_addfaq_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }*/


                    if(isset(jssupportticket::$jsst_data[0]['cplink_faq_staff'])){
                        $jsst_title =  esc_html(__("FAQs", 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_faq_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_faq_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_mail_staff'])){
                        $jsst_title = esc_html(__('Mail', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_mail_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_mail_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_myprofile_staff'])){
                        $jsst_title =  esc_html(__('My Profile', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_myprofile_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_myprofile_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_staff_report_staff'])){
                        $jsst_title = esc_html(__('Agent Reports', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_staff_report_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_staff_report_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_department_report_staff'])){
                        $jsst_title =  esc_html(__('Department Reports', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_department_report_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_department_report_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_feedback_staff'])){
                        $jsst_title = esc_html(__('Feedback', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_feedback_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_feedback_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_login_logout_staff'])){
                        $jsst_title =  esc_html(__('Login / Logout Button', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_login_logout_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_login_logout_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_totalcount_staff'])){
                        $jsst_title = esc_html(__('Ticket Total Count', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_totalcount_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_totalcount_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_ticketstats_staff'])){
                        $jsst_title =  esc_html(__('Ticket Statistics', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_ticketstats_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_ticketstats_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_latesttickets_staff'])){
                        $jsst_title = esc_html(__('Latest Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latesttickets_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latesttickets_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestdownloads_staff'])){
                        $jsst_title = esc_html(__('Latest Downloads', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestdownloads_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestdownloads_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestannouncements_staff'])){
                        $jsst_title = esc_html(__('Latest Announcements', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestannouncements_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestannouncements_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestkb_staff'])){
                        $jsst_title = esc_html(__('Latest Knowledge Base', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestkb_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestkb_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestfaqs_staff'])){
                        $jsst_title = esc_html(__('Latest FAQs', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestfaqs_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestfaqs_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_helptopic_agent'])){
                        $jsst_title = esc_html(__('Ticket Topics', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_helptopic_agent', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_helptopic_agent']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_cannedresponses_agent'])){
                        $jsst_title = esc_html(__('Canned Response', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_cannedresponses_agent', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_cannedresponses_agent']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_erasedata_staff'])){
                        $jsst_title = esc_html(__('Erase Agent Data', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_erasedata_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_erasedata_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_export_ticket_staff'])){
                        $jsst_title = esc_html(__('Export Ticket', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_export_ticket_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_export_ticket_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    ?>
                </div>

                <div class="jsst_gen_body" id="TopMenuLinks">
                    <h2><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></h2>
                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['tplink_home_staff'])){
                        $jsst_title = esc_html(__('Home', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('tplink_home_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_home_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_tickets_staff'])){
                        $jsst_title = esc_html(__('Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('tplink_tickets_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_tickets_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_openticket_staff'])){
                        $jsst_title = esc_html(__('Open Tickets', 'js-support-ticket'));
                        $jsst_field =  JSSTformfield::select('tplink_openticket_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_openticket_staff']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_login_logout_staff'])){
                      $jsst_title = esc_html(__('Login / Logout Button', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tplink_login_logout_staff', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_login_logout_staff']);
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                  ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....USER MENUS..... -->
            <!-- .....USER MENUS..... -->
            <div id="usermenusetting" class="jsstadmin-hide-config">
               <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#DashboardLinksUser"><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#TopMenuLinksUser"><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></a></li>
                  </ul>
              </div>
              <div class="jsst_gen_body" id="DashboardLinksUser">
                  <h2><?php echo esc_html(__('Dashboard Links', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['cplink_openticket_user'])){
                        $jsst_title = esc_html(__('Open Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_openticket_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_openticket_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_myticket_user'])){
                        $jsst_title = esc_html(__('My Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_myticket_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_myticket_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_checkticketstatus_user'])){
                        $jsst_title = esc_html(__('Check Ticket Status', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_checkticketstatus_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_checkticketstatus_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_downloads_user'])){
                        $jsst_title = esc_html(__('Downloads', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_downloads_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_downloads_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_announcements_user'])){
                        $jsst_title = esc_html(__('Announcements', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_announcements_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_announcements_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_faqs_user'])){
                        $jsst_title = esc_html(__("FAQs", 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_faqs_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_faqs_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_knowledgebase_user'])){
                        $jsst_title = esc_html(__('Knowledge Base', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_knowledgebase_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_knowledgebase_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_login_logout_user'])){
                        $jsst_title = esc_html(__('Login / Logout Button', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_login_logout_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_login_logout_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['cplink_register_user'])){
                        $jsst_title = esc_html(__('Registration', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_register_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_register_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_erasedata_user'])){
                        $jsst_title = esc_html(__('Erase User Data', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_erasedata_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_erasedata_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latesttickets_user'])){
                        $jsst_title = esc_html(__('Latest Tickets', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latesttickets_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latesttickets_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_ticketstats_user'])){
                        $jsst_title =  esc_html(__('Ticket Statistics', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_ticketstats_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_ticketstats_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_totalcount_user'])){
                        $jsst_title = esc_html(__('Ticket Total Count', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_totalcount_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_totalcount_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestdownloads_user'])){
                        $jsst_title = esc_html(__('Latest Downloads', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestdownloads_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestdownloads_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestannouncements_user'])){
                        $jsst_title = esc_html(__('Latest Announcements', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestannouncements_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestannouncements_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestkb_user'])){
                        $jsst_title = esc_html(__('Latest Knowledge Base', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestkb_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestkb_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                    if(isset(jssupportticket::$jsst_data[0]['cplink_latestfaqs_user'])){
                        $jsst_title = esc_html(__('Latest FAQs', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('cplink_latestfaqs_user', $jsst_showhide, jssupportticket::$jsst_data[0]['cplink_latestfaqs_user']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                  ?>
              </div>
              <div class="jsst_gen_body" id="TopMenuLinksUser">
                  <h2><?php echo esc_html(__('Top Menu Links', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['tplink_home_user'])){
                      $jsst_title = esc_html(__('Home', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tplink_home_user', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_home_user']);
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_tickets_user'])){
                      $jsst_title = esc_html(__('Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tplink_tickets_user', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_tickets_user']);
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_openticket_user'])){
                      $jsst_title = esc_html(__('Open Tickets', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tplink_openticket_user', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_openticket_user']);
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['tplink_login_logout_user'])){
                      $jsst_title = esc_html(__('Login / Logout Button', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('tplink_login_logout_user', $jsst_showhide, jssupportticket::$jsst_data[0]['tplink_login_logout_user']);
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                    }
                  ?>
              </div>
            </div>
            <!-- .....feedback..... -->
            <div id="feedback" class="jsstadmin-hide-config">
              <?php if(in_array('feedback', jssupportticket::$_active_addons)){ ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#FeedbackSettings"><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="FeedbackSettings">
                  <h2><?php echo esc_html(__('Feedback Settings', 'js-support-ticket')); ?></h2>
                  <?php
                    if(isset(jssupportticket::$jsst_data[0]['feedback_email_delay_type'])){
                      $jsst_title = esc_html(__('Feedback Email Delay Type', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('feedback_email_delay_type',  array((object) array('id' => '1', 'text' => esc_html(__('Days', 'js-support-ticket'))), (object) array('id' => '2', 'text' => esc_html(__('Hours', 'js-support-ticket')))), jssupportticket::$jsst_data[0]['feedback_email_delay_type']);
                      $jsst_description = esc_html(__('Select delay type for feedback email', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['feedback_email_delay'])){
                      $jsst_title = esc_html(__('Feedback Email Delay', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('feedback_email_delay', jssupportticket::$jsst_data[0]['feedback_email_delay'], array('class' => 'inputbox'));
                      $jsst_description = esc_html(__('Set no. of days or hours to send feedback email after a ticket is closed', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['feedback_thanks_message'])){ ?>
                      <div class="js-ticket-configuration-row">
                        <div class="js-ticket-configuration-title"><?php echo esc_html(__('Success message after submitting feedback', 'js-support-ticket')); ?></div>
                        <div class="js-ticket-configuration-value full-width">
                          <?php wp_editor(jssupportticket::$jsst_data[0]['feedback_thanks_message'], 'feedback_thanks_message') ?>
                          <div class="js-ticket-configuration-description">
                            <?php echo esc_html(__('This text will appear whenever anyone submits feedback', 'js-support-ticket')); ?>
                          </div>
                        </div>
                      </div>
                    <?php } ?>

                </div>
              <?php } ?>
            </div>
            <!-- .....Social Login..... -->
            <div id="sociallogin" class="jsstadmin-hide-config">
              <?php if (in_array('sociallogin', jssupportticket::$_active_addons)) { ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#Facebook"><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></a></li>
                      <li class="tab-link"><a href="#Linkedin"><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="Facebook">
                    <h2><?php echo esc_html(__('Facebook', 'js-support-ticket')); ?></h2>
                    <?php
                      $jsst_loginwithfacebook = "";
                      $jsst_apikeyfacebook = "";
                      $jsst_clientsecretfacebook = "";
                      if (isset(jssupportticket::$jsst_data[0]['loginwithfacebook'])) {
                          $jsst_loginwithfacebook = jssupportticket::$jsst_data[0]['loginwithfacebook'];
                      }
                      $jsst_title = esc_html(__('Login With Facebook', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('loginwithfacebook', array((object)array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))), (object)array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))), $jsst_loginwithfacebook);
                      $jsst_description = esc_html(__('Facebook user can login in js support ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);

                      // if (isset(jssupportticket::$jsst_data[0]['apikeyfacebook'])) {
                      //     $jsst_apikeyfacebook = jssupportticket::$jsst_data[0]['apikeyfacebook'];
                      // }
                      $jsst_title = esc_html(__('Secret', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('clientsecretfacebook', jssupportticket::$jsst_data[0]['clientsecretfacebook'], array('class' => 'inputbox'));
                      $jsst_description = esc_html(__('Secret Key', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);

                      // if (isset(jssupportticket::$jsst_data[0]['clientsecretfacebook'])) {
                      //     $jsst_clientsecretfacebook = jssupportticket::$jsst_data[0]['clientsecretfacebook'];
                      // }
                      $jsst_title = esc_html(__('API Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('apikeyfacebook', jssupportticket::$jsst_data[0]['apikeyfacebook'], array('class' => 'inputbox'));
                      $jsst_description = esc_html(__('API key is required for facebook app', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    ?>
                </div>
                <div class="jsst_gen_body" id="Linkedin">
                    <h2><?php echo esc_html(__('Linkedin', 'js-support-ticket')); ?></h2>
                    <?php
                      $jsst_loginwithlinkedin = "";
                      $jsst_apikeylinkedin = "";
                      $jsst_clientsecretlinkedin = "";
                      if (isset(jssupportticket::$jsst_data[0]['loginwithlinkedin'])) {
                          $jsst_loginwithlinkedin = jssupportticket::$jsst_data[0]['loginwithlinkedin'];
                      }
                      $jsst_title = esc_html(__('Login with linkedin', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('loginwithlinkedin', array((object)array('id' => '1', 'text' => esc_html(__('Yes', 'js-support-ticket'))), (object)array('id' => '2', 'text' => esc_html(__('No', 'js-support-ticket')))), $jsst_loginwithlinkedin);
                      $jsst_description = esc_html(__('Facebook user can login in js support ticket', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);

                      if (isset(jssupportticket::$jsst_data[0]['apikeylinkedin'])) {
                          $jsst_loginwithlinkedin = jssupportticket::$jsst_data[0]['apikeylinkedin'];
                      }
                      $jsst_title = esc_html(__('Secret', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('clientsecretlinkedin',  jssupportticket::$jsst_data[0]['clientsecretlinkedin'], array('class' => 'inputbox'));
                      $jsst_description = esc_html(__('Secret Key', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);

                      if (isset(jssupportticket::$jsst_data[0]['clientsecretlinkedin'])) {
                          $jsst_clientsecretlinkedin = jssupportticket::$jsst_data[0]['clientsecretlinkedin'];
                      }
                      $jsst_title = esc_html(__('API Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('apikeylinkedin',jssupportticket::$jsst_data[0]['apikeylinkedin'], array('class' => 'inputbox'));
                      $jsst_description = esc_html(__('API key is required for linkedin app', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                  ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Email Piping..... -->
            <div id="ticketviaemail" class="jsstadmin-hide-config">
              <?php if (in_array('emailpiping', jssupportticket::$_active_addons)) { ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#EmailPiping"><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EmailPiping">
                    <h2><?php echo esc_html(__('Email Piping', 'js-support-ticket')); ?></h2>
                    <?php
                      if (isset(jssupportticket::$jsst_data[0]['read_utf_ticket_via_email'])) {
                        $jsst_title = esc_html(__('UTF Auto Switch', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('read_utf_ticket_via_email',$jsst_yesno, jssupportticket::$jsst_data[0]['read_utf_ticket_via_email']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                      }
                      if(isset(jssupportticket::$jsst_data[0]['create_user_via_email'])){
                        $jsst_title = esc_html(__('Create User via email', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::select('create_user_via_email',$jsst_yesno, jssupportticket::$jsst_data[0]['create_user_via_email']);
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Firebase Notifications..... -->
            <div id="pushnotification" class="jsstadmin-hide-config">
              <?php if(in_array('notification', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#FirebaseNotifications"><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
              <div class="jsst_gen_body" id="FirebaseNotifications">
                  <h2><?php echo esc_html(__('Firebase Notifications', 'js-support-ticket')); ?></h2>
                  <?php
                    if(!file_exists(WP_PLUGIN_DIR.'/js-support-ticket-notification/js-support-ticket-notification.php')){ ?>
                      <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                        <span style="color: #000;" class="jsst_msg" id="jsst_error_message"><?php echo esc_html(__("JS Help Desk Desktop Notifications plugin is not installed. Please install the plugin to enable desktop notifications","js-support-ticket"));?><a title="<?php echo esc_attr(__("Click here to insert Install.","js-support-ticket")); ?>" href="<?php echo esc_url(admin_url("admin.php?page=premiumplugin")); ?>"><?php echo esc_html(__("Click here to insert Install.","js-support-ticket")); ?></a></span>
                      </div>
                    <?php
                    }elseif(!class_exists('JSSTNotification') && !class_exists('JSSTBundleNotification')){ ?>
                      <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                          <span style="color: #000;" class="jsst_msg" id="jsst_success_message"><?php echo esc_html(__("JS Help Desk Desktop Notifications plugin is not active.","js-support-ticket"));?></span>
                      </div>
                    <?php
                    } ?>
                    <div class="jsst_error_messages" style="color: #000; margin-bottom: 15px;">
                      <span style="color: #000;" class="jsst_warning_msg" id="jsst_error_message"><?php echo esc_html(__("Find and add firebase api's keys","js-support-ticket"));?><a title="<?php echo esc_attr(__("Click here to get firebae api keys.","js-support-ticket")); ?>" href="https://console.firebase.google.com" target="_blank"><?php echo esc_html(__("Click here to get firebae api keys.","js-support-ticket")); ?></a></span>
                    </div>
                    <?php
                      if(isset(jssupportticket::$jsst_data[0]['apiKey_firebase'])){
                        $jsst_title = esc_html(__('API Key For User', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('apiKey_firebase', jssupportticket::$jsst_data[0]['apiKey_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase api key for front user', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['authDomain_firebase'])){
                        $jsst_title = esc_html(__('Auth Domain', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('authDomain_firebase', jssupportticket::$jsst_data[0]['authDomain_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Auth Domain', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['databaseURL_firebase'])){
                        $jsst_title = esc_html(__('Database URL', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('databaseURL_firebase', jssupportticket::$jsst_data[0]['databaseURL_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Database URL', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['projectId_firebase'])){
                        $jsst_title = esc_html(__('Project Id', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('projectId_firebase', jssupportticket::$jsst_data[0]['projectId_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Project Id', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['storageBucket_firebase'])){
                        $jsst_title = esc_html(__('Bucket Storage', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('storageBucket_firebase', jssupportticket::$jsst_data[0]['storageBucket_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Bucket Storage', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['messagingSenderId_firebase'])){
                        $jsst_title = esc_html(__('Message Sender Id', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('messagingSenderId_firebase', jssupportticket::$jsst_data[0]['messagingSenderId_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Message Sender Id', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['server_key_firebase'])){
                        $jsst_title = esc_html(__('Private Server Key', 'js-support-ticket'));
                        $jsst_field = JSSTformfield::text('server_key_firebase', jssupportticket::$jsst_data[0]['server_key_firebase'], array('class' => 'inputbox'));
                        $jsst_description =  esc_html(__('Firebase Server Key', 'js-support-ticket'));
                        JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }

                      if(isset(jssupportticket::$jsst_data[0]['logo_for_desktop_notfication_url'])){
                        $jsst_title = esc_html(__('Logo Image For Desktop Notifications', 'js-support-ticket'));
                        $jsst_value = '<input type="file" name="logo_for_desktop_notfication" id="logo_for_desktop_notfication">';
                        $jsst_description = '';
                        if(jssupportticket::$_config['logo_for_desktop_notfication_url'] != ''){
                          $jsst_maindir = wp_upload_dir();
                          $jsst_path = $jsst_maindir['baseurl'].'/'.jssupportticket::$_config['data_directory'].'/attachmentdata';
                          $jsst_description = '<img alt="'. esc_html(__('Remove Image','js-support-ticket')).'" height="60px" width="60px;" src="'.esc_attr($jsst_path).'/'.esc_attr(jssupportticket::$_config['logo_for_desktop_notfication_url']).'"/> <label><input type="checkbox" name="del_logo_for_desktop_notfication" value="1">'. esc_html(__('Remove Logo','js-support-ticket')).'</label>';
                        }else{
                          $jsst_description = esc_html(__('No Firebase Notification Logo', 'js-support-ticket'));
                        }
                        JSST_printConfigFieldSingle($jsst_title, $jsst_value, $jsst_description);
                      }
                    ?>
              </div>
              <?php } ?>
            </div>
            <!-- .....Private Credentials..... -->
            <div id="privatecredentials" class="jsstadmin-hide-config">
              <?php if(in_array('privatecredentials', jssupportticket::$_active_addons)){ ?>
                 <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#PrivateCredentials"><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="PrivateCredentials">
                    <h2><?php echo esc_html(__('Private Credentials', 'js-support-ticket')); ?></h2>
                    <?php
                    /* The encryption key used to be edited here, in a plain
                       text field, which put it on screen in wp-admin and in
                       every browser autofill store that saw the page - beside
                       the ciphertext it protects, in the same database. Both
                       of those are what 5.5-SEC-01 exists to end, so the field
                       has gone and the key comes from wp-config.php or the
                       environment. What is left is a pointer to the screen that
                       says where this site's key is actually coming from and
                       what to do about it. (Roadmap 5.5-SEC-01) */
                    $jsst_title = esc_html(__('Encryption key', 'js-support-ticket'));
                    $jsst_field = '';
                    if (class_exists('JSSTcredentialvault')) {
                        $jsst_where = JSSTcredentialvault::keySource();
                        $jsst_field = '<a class="button" href="' . esc_url(admin_url('admin.php?page=privatecredentials&jstlay=vault')) . '">'
                            . esc_html(__('Open the Credentials screen', 'js-support-ticket')) . '</a>';
                        $jsst_description = sprintf(
                            /* translators: %s: where the encryption key is coming from */
                            esc_html__('This site is using: %s. The key is no longer editable here — it is read from wp-config.php or the environment, so that a copy of the database is not also a copy of the key.', 'js-support-ticket'),
                            $jsst_where['label']);
                        if (empty($jsst_where['safe'])) {
                            $jsst_description .= ' ' . esc_html__('That is the fallback and it protects nothing against somebody holding a database dump. The Credentials screen says exactly what to paste into wp-config.php.', 'js-support-ticket');
                        }
                    } else {
                        $jsst_description = esc_html__('Update the Private Credentials add-on to manage the encryption key.', 'js-support-ticket');
                    }
                    JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Envato Validation..... -->
            <div id="envatovalidation" class="jsstadmin-hide-config">
              <?php if(in_array('envatovalidation', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#EnvatoValidation"><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EnvatoValidation">
                    <h2><?php echo esc_html(__('Envato Validation', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$jsst_data[0]['envato_api_key'])){
                          $jsst_title = esc_html(__('API Key', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::text('envato_api_key', jssupportticket::$jsst_data[0]['envato_api_key'], array('class' => 'inputbox'));
                          $jsst_description =  esc_html(__('Enter Envato api key ', 'js-support-ticket'));
                          $jsst_description.= '<a title="'. esc_html(__("Click here to generate an api key",'js-support-ticket')).'" target="_blank" href="https://build.envato.com/create-token/">'. esc_html(__("Click here to generate an api key",'js-support-ticket')).'</a>';
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                      if(isset(jssupportticket::$jsst_data[0]['envato_license_required'])){
                          $jsst_title = esc_html(__('License Mandatory', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::select('envato_license_required', $jsst_yesno, jssupportticket::$jsst_data[0]['envato_license_required']);
                          $jsst_description =  esc_html(__('Prevent users from submitting a ticket without a valid license for one of your product', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                      if(isset(jssupportticket::$jsst_data[0]['envato_product_ids'])){
                          $jsst_title = esc_html(__('Product ID', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::text('envato_product_ids', jssupportticket::$jsst_data[0]['envato_product_ids'], array('class' => 'inputbox'));
                          $jsst_description =  esc_html(__('A comma-separated list of Envato product ids', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....MailChimp..... -->
            <div id="mailchimp" class="jsstadmin-hide-config">
              <?php if(in_array('mailchimp', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#MailChimp"><?php echo esc_html(__('Mailchimp', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="MailChimp">
                    <h2><?php echo esc_html(__('Mailchimp', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$jsst_data[0]['mailchimp_api_key'])){
                          $jsst_title = esc_html(__('API Key', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::text('mailchimp_api_key', jssupportticket::$jsst_data[0]['mailchimp_api_key'], array('class' => 'inputbox'));
                          $jsst_description =  esc_html(__('Enter MailChimp API key ', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                      if(isset(jssupportticket::$jsst_data[0]['mailchimp_list_id'])){
                          $jsst_title = esc_html(__('Audience ID', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::text('mailchimp_list_id', jssupportticket::$jsst_data[0]['mailchimp_list_id'], array('class' => 'inputbox'));
                          $jsst_description =  esc_html(__('Find Audience ID in your MailChimp account', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                      if(isset(jssupportticket::$jsst_data[0]['mailchimp_double_optin'])){
                          $jsst_title = esc_html(__('Enable Double Opt-in', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::select('mailchimp_double_optin', $jsst_yesno, jssupportticket::$jsst_data[0]['mailchimp_double_optin']);
                          $jsst_description =  esc_html(__('You must also enable double opt-in in your MailChimp account', 'js-support-ticket'));
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      }
                      $jsst_title = esc_html(__('Welcome Email', 'js-support-ticket'));
                      $jsst_field = esc_html(__('You can enable Final Welcome Email in your MailChimp account', 'js-support-ticket'));
                      $jsst_description = '';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                      ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Easy Digital Downloads..... -->
            <div id="easydigitaldownloads" class="jsstadmin-hide-config">
              <?php if(in_array('easydigitaldownloads', jssupportticket::$_active_addons)){ ?>
                <div class="tabs config-tabs" id="tabs">
                  <ul class="jsst_tabs">
                      <li class="tab-link jsst_current_tab"><a href="#EasyDigitalDownloads"><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></a></li>
                  </ul>
                </div>
                <div class="jsst_gen_body" id="EasyDigitalDownloads">
                    <h2><?php echo esc_html(__('Easy Digital Downloads', 'js-support-ticket')); ?></h2>
                    <?php
                      if(isset(jssupportticket::$jsst_data[0]['verify_license_on_ticket_creation'])){
                          $jsst_title = esc_html(__('Verify License On Ticket Creation', 'js-support-ticket'));
                          $jsst_field = JSSTformfield::select('verify_license_on_ticket_creation', $jsst_yesno, jssupportticket::$jsst_data[0]['verify_license_on_ticket_creation']);
                          JSST_printConfigFieldSingle($jsst_title, $jsst_field);
                      }
                    ?>
                </div>
              <?php } ?>
            </div>
            <!-- .....Auto Cleanup..... -->
            <div id="autocleanup" class="jsstadmin-hide-config">
                <?php if(JSSTmergedaddon::featureEnabled('autocleanup')){
                    $jsst_cleanup_coreowns = JSSTmergedaddon::coreOwns('autocleanup');
                    $jsst_cleanup = $jsst_cleanup_coreowns ? JSSTincluder::getJSModel('autocleanup') : null;
                    ?>
                    <div class="tabs config-tabs" id="tabs">
                        <ul class="jsst_tabs">
                            <li class="tab-link jsst_current_tab"><a href="#AutoCleanupSettings"><?php echo esc_html(__('Retention', 'js-support-ticket')); ?></a></li>
                        </ul>
                    </div>
                    <div class="jsst_gen_body" id="AutoCleanupSettings">
                        <?php /* Named for the subject rather than the mechanism.
                                 (Roadmap 5.0-ANA-04) "Auto cleanup" here and
                                 "Retention" on the Reporting screen are two names
                                 for one question - how long this desk keeps
                                 things - and nothing on either screen said they
                                 were related, so the rule and the governance of
                                 the rule read as two unconnected features. */ ?>
                        <h2><?php echo esc_html(__('Retention (auto cleanup)', 'js-support-ticket')); ?></h2>
                        <p class="description"><?php echo esc_html(__('The rule itself: what is deleted, when, and what is never touched. This is the only place it is set.', 'js-support-ticket')); ?></p>
                        <?php
                        /* And where the rest of the story is, when this site has
                           it. The Retention screen never deletes anything - the
                           deleting is this rule's, in JSSTautocleanupModel - but
                           it decides which tickets survive it and whether a run
                           happens at all, so a number set here can be overruled
                           by something set there. Somebody reading "delete after
                           24 months" with no idea a legal hold exists is the
                           whole reason this line is here. (Roadmap 5.0-ANA-04) */
                        if (class_exists('JSSTretention')) {
                            $jsst_title = esc_html(__('Holds, approvals and reports', 'js-support-ticket'));
                            $jsst_field = '<a class="button" href="'
                                . esc_url(admin_url('admin.php?page=reporting&jstlay=retention'))
                                . '">' . esc_html(__('Open Retention', 'js-support-ticket')) . '</a>';
                            $jsst_description = esc_html(__('This site also has archive tiers, legal holds, deletion approvals and retention reports. A ticket under legal hold is kept whatever the rule below says, and where approvals are switched on a run waits for somebody to approve it. Those are set on Reporting > Retention.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset(jssupportticket::$jsst_data[0]['autocleanup_attachment_interval'])){
                            $jsst_title = esc_html(__('Delete Old Attachments', 'js-support-ticket'));
                            $jsst_options = array(
                                (object) array('id' => '0', 'text' => esc_html(__('Never', 'js-support-ticket'))),
                                (object) array('id' => '1', 'text' => esc_html(__('After 1 Month', 'js-support-ticket'))),
                                (object) array('id' => '3', 'text' => esc_html(__('After 3 Months', 'js-support-ticket'))),
                                (object) array('id' => '6', 'text' => esc_html(__('After 6 Months', 'js-support-ticket'))),
                                (object) array('id' => '12', 'text' => esc_html(__('After 1 Year', 'js-support-ticket')))
                            );
                            $jsst_field = JSSTformfield::select('autocleanup_attachment_interval', $jsst_options, jssupportticket::$jsst_data[0]['autocleanup_attachment_interval']);
                            $jsst_description =  esc_html(__('Automatically delete attachments from closed tickets to save storage space.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }

                        if(isset(jssupportticket::$jsst_data[0]['autocleanup_ticket_interval'])){
                            $jsst_title = esc_html(__('Delete Old Tickets', 'js-support-ticket'));
                            $jsst_options = array(
                                (object) array('id' => '0', 'text' => esc_html(__('Never', 'js-support-ticket'))),
                                (object) array('id' => '12', 'text' => esc_html(__('After 1 Year', 'js-support-ticket'))),
                                (object) array('id' => '24', 'text' => esc_html(__('After 2 Years', 'js-support-ticket'))),
                                (object) array('id' => '36', 'text' => esc_html(__('After 3 Years', 'js-support-ticket')))
                            );
                            $jsst_field = JSSTformfield::select('autocleanup_ticket_interval', $jsst_options, jssupportticket::$jsst_data[0]['autocleanup_ticket_interval']);
                            $jsst_description =  esc_html(__('Permanently delete closed tickets and all their associated data after this time period.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }

                        if(isset(jssupportticket::$jsst_data[0]['autocleanup_cron_frequency'])){
                            $jsst_title = esc_html(__('Cron Frequency', 'js-support-ticket'));
                            $jsst_options = array(
                                (object) array('id' => 'daily', 'text' => esc_html(__('Daily', 'js-support-ticket'))),
                                (object) array('id' => 'weekly', 'text' => esc_html(__('Weekly', 'js-support-ticket'))),
                                (object) array('id' => 'monthly', 'text' => esc_html(__('Monthly', 'js-support-ticket')))
                            );
                            $jsst_field = JSSTformfield::select('autocleanup_cron_frequency', $jsst_options, jssupportticket::$jsst_data[0]['autocleanup_cron_frequency']);
                            $jsst_description =  esc_html(__('How often the background cleanup task should execute.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }

                        if ($jsst_cleanup_coreowns) {
                        // Exclusions: departments and priorities that retention
                        // never touches. (Roadmap 4.0-CORE-13)
                        //
                        // Picked by name from a checkbox group, never typed as ids:
                        // nobody administering a help desk knows that Billing is
                        // department 3, and a wrong number here does not fail - it
                        // silently deletes the tickets it was meant to protect. The
                        // group posts a JSON array through the hidden field that the
                        // script at the foot of this screen keeps in sync.
                        //
                        // Disabled departments and priorities are listed as well.
                        // Retention only ever reaches old closed tickets, which is
                        // exactly where a retired priority still turns up; leaving
                        // one out of the list would quietly drop it from the
                        // exclusion the next time this form was saved.
                        $jsst_cleanup_depts = jssupportticket::$_db->get_results(
                            "SELECT id, departmentname AS name FROM `" . jssupportticket::$_db->prefix . "js_ticket_departments` ORDER BY ordering ASC"
                        );
                        $jsst_cleanup_prios = jssupportticket::$_db->get_results(
                            "SELECT id, priority AS name FROM `" . jssupportticket::$_db->prefix . "js_ticket_priorities` ORDER BY ordering ASC"
                        );
                        $jsst_cleanup_deptnames = array();
                        foreach ((array) $jsst_cleanup_depts as $jsst_cleanup_row) {
                            $jsst_cleanup_deptnames[(int) $jsst_cleanup_row->id] = $jsst_cleanup_row->name;
                        }
                        $jsst_cleanup_prionames = array();
                        foreach ((array) $jsst_cleanup_prios as $jsst_cleanup_row) {
                            $jsst_cleanup_prionames[(int) $jsst_cleanup_row->id] = $jsst_cleanup_row->name;
                        }

                        if(isset(jssupportticket::$jsst_data[0]['autocleanup_exclude_departments'])){
                            // Read back through the model, so what is ticked here is
                            // by construction what the run would skip - including a
                            // comma-separated value saved before 4.0.
                            $jsst_cleanup_on = JSSTautocleanupModel::excludedDepartments();
                            $jsst_title = esc_html(__('Never Delete These Departments', 'js-support-ticket'));
                            $jsst_field = '<div class="jsst-ir-checks" data-jsst-ir-target="autocleanup_exclude_departments">';
                            if (empty($jsst_cleanup_depts)) {
                                $jsst_field .= '<em>' . esc_html(__('No departments found.', 'js-support-ticket')) . '</em>';
                            } else {
                                foreach ($jsst_cleanup_depts as $jsst_cleanup_row) {
                                    $jsst_field .= '<label><input type="checkbox" value="' . esc_attr($jsst_cleanup_row->id) . '"'
                                                . (in_array((int) $jsst_cleanup_row->id, $jsst_cleanup_on, true) ? ' checked' : '') . '> '
                                                . esc_html($jsst_cleanup_row->name) . '</label>';
                                }
                            }
                            $jsst_field .= '<input type="hidden" name="autocleanup_exclude_departments" id="autocleanup_exclude_departments" value="'
                                        . esc_attr(json_encode($jsst_cleanup_on)) . '"></div>';
                            $jsst_description = esc_html(__('Tickets in these departments are never deleted or stripped of attachments, whatever the intervals above say.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }
                        if(isset(jssupportticket::$jsst_data[0]['autocleanup_exclude_priorities'])){
                            $jsst_cleanup_on = JSSTautocleanupModel::excludedPriorities();
                            $jsst_title = esc_html(__('Never Delete These Priorities', 'js-support-ticket'));
                            $jsst_field = '<div class="jsst-ir-checks" data-jsst-ir-target="autocleanup_exclude_priorities">';
                            if (empty($jsst_cleanup_prios)) {
                                $jsst_field .= '<em>' . esc_html(__('No priorities found.', 'js-support-ticket')) . '</em>';
                            } else {
                                foreach ($jsst_cleanup_prios as $jsst_cleanup_row) {
                                    $jsst_field .= '<label><input type="checkbox" value="' . esc_attr($jsst_cleanup_row->id) . '"'
                                                . (in_array((int) $jsst_cleanup_row->id, $jsst_cleanup_on, true) ? ' checked' : '') . '> '
                                                . esc_html($jsst_cleanup_row->name) . '</label>';
                                }
                            }
                            $jsst_field .= '<input type="hidden" name="autocleanup_exclude_priorities" id="autocleanup_exclude_priorities" value="'
                                        . esc_attr(json_encode($jsst_cleanup_on)) . '"></div>';
                            $jsst_description = esc_html(__('Tickets at these priorities are never deleted or stripped of attachments.', 'js-support-ticket'));
                            JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                        }
                        ?>

                        <?php
                        // The dry run. Retention deletes permanently and on a
                        // schedule, so the preview is the safety feature that makes
                        // the rest of this screen usable. (Roadmap 4.0-CORE-13)
                        $jsst_preview = $jsst_cleanup->preview();
                        $jsst_lastrun = JSSTautocleanupModel::lastRun();
                        ?>
                        <h2><?php echo esc_html(__('What Would Be Deleted', 'js-support-ticket')); ?></h2>
                        <div class="jsst-cleanup-preview">
                            <?php if (!$jsst_preview['enabled']) { ?>
                                <p><?php echo esc_html(__('Nothing is deleted at the moment: both intervals are set to Never.', 'js-support-ticket')); ?></p>
                            <?php } else { ?>
                                <ul class="jsst-cleanup-figures">
                                    <li>
                                        <strong><?php echo esc_html(number_format_i18n($jsst_preview['tickets'])); ?></strong>
                                        <?php echo esc_html(__('tickets would be permanently deleted', 'js-support-ticket')); ?>
                                        <?php if ($jsst_preview['ticket_cutoff'] !== '') { ?>
                                            <span class="jsst-cleanup-cutoff"><?php echo esc_html(sprintf(
                                                /* translators: %s: a date */
                                                __('closed on or before %s', 'js-support-ticket'),
                                                date_i18n(get_option('date_format'), jssupportticketphplib::JSST_strtotime($jsst_preview['ticket_cutoff']))
                                            )); ?></span>
                                        <?php } ?>
                                    </li>
                                    <li>
                                        <strong><?php echo esc_html(number_format_i18n($jsst_preview['attachments'])); ?></strong>
                                        <?php echo esc_html(__('attachments would be removed, freeing', 'js-support-ticket')); ?>
                                        <strong><?php echo esc_html(JSSTautocleanupModel::formatBytes($jsst_preview['bytes'])); ?></strong>
                                    </li>
                                </ul>
                                <?php if (!empty($jsst_preview['excluded_departments']) || !empty($jsst_preview['excluded_priorities'])) { ?>
                                    <p class="jsst-cleanup-exclusions">
                                        <?php echo esc_html(__('Exclusions in force:', 'js-support-ticket')); ?>
                                        <?php
                                        // Names here too. This line is read as a check
                                        // that the right things are protected, and an
                                        // id proves nothing to the person reading it.
                                        // An id with no row left behind it is shown as
                                        // itself rather than dropped - the exclusion is
                                        // still in force.
                                        if (!empty($jsst_preview['excluded_departments'])) {
                                            $jsst_cleanup_shown = array();
                                            foreach ($jsst_preview['excluded_departments'] as $jsst_cleanup_id) {
                                                $jsst_cleanup_shown[] = isset($jsst_cleanup_deptnames[$jsst_cleanup_id]) ? $jsst_cleanup_deptnames[$jsst_cleanup_id] : '#' . $jsst_cleanup_id;
                                            }
                                            /* translators: %s: comma separated list of department names excluded from the cleanup. */
                                            echo esc_html(sprintf(__('departments %s', 'js-support-ticket'), implode(', ', $jsst_cleanup_shown)));
                                        }
                                        if (!empty($jsst_preview['excluded_priorities'])) {
                                            $jsst_cleanup_shown = array();
                                            foreach ($jsst_preview['excluded_priorities'] as $jsst_cleanup_id) {
                                                $jsst_cleanup_shown[] = isset($jsst_cleanup_prionames[$jsst_cleanup_id]) ? $jsst_cleanup_prionames[$jsst_cleanup_id] : '#' . $jsst_cleanup_id;
                                            }
                                            /* translators: %s: comma separated list of priority names excluded from the cleanup. */
                                            echo esc_html(sprintf(__('priorities %s', 'js-support-ticket'), implode(', ', $jsst_cleanup_shown)));
                                        }
                                        ?>
                                    </p>
                                <?php } ?>
                                <?php if (!empty($jsst_preview['sample'])) { ?>
                                    <p><?php echo esc_html(__('The oldest of them:', 'js-support-ticket')); ?></p>
                                    <ul class="jsst-cleanup-sample">
                                        <?php foreach ($jsst_preview['sample'] AS $jsst_sample) { ?>
                                            <li>
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=ticket&jstlay=ticketdetail&jssupportticketid=' . (int) $jsst_sample->id)); ?>">#<?php echo esc_html($jsst_sample->ticketid); ?></a>
                                                <?php echo esc_html($jsst_sample->subject); ?>
                                                <span class="jsst-cleanup-cutoff"><?php echo esc_html(date_i18n(get_option('date_format'), jssupportticketphplib::JSST_strtotime($jsst_sample->closed))); ?></span>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                                <p class="jsst-cleanup-warning"><?php echo esc_html(__('Deletion cannot be undone. These figures are recalculated every time this screen is opened, and nothing is deleted by looking at them.', 'js-support-ticket')); ?></p>
                            <?php } ?>
                            <?php if (!empty($jsst_lastrun)) { ?>
                                <p class="jsst-cleanup-lastrun">
                                    <?php echo esc_html(sprintf(
                                        /* translators: 1: date, 2: what the run did */
                                        __('Last run %1$s: %2$s', 'js-support-ticket'),
                                        $jsst_lastrun['when'],
                                        $jsst_lastrun['message']
                                    )); ?>
                                </p>
                            <?php } ?>
                        </div>
                        <?php } // end $jsst_cleanup_coreowns ?>
                    </div>
                <?php } ?>
            </div>


            <!-- .....AI Agent..... -->
            <div id="aiagent" class="jsstadmin-hide-config">
                <?php
                /* The AI Agent settings live on one screen now, and it is not
                   this one. (Roadmap 6.0-AI-01)

                   Four tabs of them were here - Suggestions, AI Assistant,
                   Automatic Replies and Advanced - because they arrived as the
                   Instant Resolve add-on's settings and pre-4.0 that is where an
                   add-on put them. Meanwhile the things that decide whether any
                   of it may run at all - the three lanes, the master switch, the
                   redaction mode, the engine and its key - were on AI Agent >
                   Settings, because they cannot be expressed as config rows: the
                   key is write-only by design, and this screen round-trips every
                   stored value back into the markup.

                   So one feature had two homes, one called Settings and the
                   other called Configuration, and which one held a given setting
                   depended on the era it was written in. They are all on AI Agent
                   > Settings now.

                   What is left here is a pointer, not a redirect. `?page=configuration&jsstconfigid=aiagent`
                   is in bookmarks, in support answers and in a menu entry beside
                   sixteen others, and a link that silently lands somewhere else
                   teaches nobody where the settings went. */
                ?>
                <div class="jsst_gen_body" id="AIAgentMoved">
                    <h2><?php echo esc_html(__('AI Agent', 'js-support-ticket')); ?></h2>
                    <?php
                    $jsst_title = esc_html(__('Where these settings are now', 'js-support-ticket'));
                    $jsst_field = '<a class="button" href="'
                        . esc_url(admin_url('admin.php?page=aiagent&jstlay=aiagent_settings'))
                        . '">' . esc_html(__('Open AI Agent Settings', 'js-support-ticket')) . '</a>';
                    $jsst_description = esc_html(__('Suggestions, the AI Assistant, Automatic Replies and the Advanced retrieval settings have moved to AI Agent > Settings, where the lanes, the master switch and the answer engine already were. Nothing was reset — every value is exactly where you left it, on the new screen.', 'js-support-ticket'));
                    JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    ?>
                </div>
            </div>
            <!-- .....Captcha..... -->
            <div id="captcha" class="jsstadmin-hide-config">
              <div class="tabs config-tabs" id="tabs">
                <ul class="jsst_tabs">
                    <li class="tab-link jsst_current_tab"><a href="#CaptchaSetting"><?php echo esc_html(__('Captcha', 'js-support-ticket')); ?></a></li>
                </ul>
              </div>
              <div class="jsst_gen_body" id="CaptchaSetting">
                  <h2><?php echo esc_html(__('Captcha Setting', 'js-support-ticket')); ?></h2>
                    <?php
        
                    if(isset(jssupportticket::$jsst_data[0]['captcha_on_registration'])){
                      $jsst_title = esc_html(__('Show Captcha On Registration Form', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('captcha_on_registration', $jsst_yesno, jssupportticket::$jsst_data[0]['captcha_on_registration']);
                      $jsst_description =  esc_html(__('Select whether you want to show captcha on the registration form or not', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['show_captcha_on_visitor_from_ticket'])){
                      $jsst_title = esc_html(__('Show Captcha On The Visitor Ticket Form', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('show_captcha_on_visitor_from_ticket', $jsst_yesno, jssupportticket::$jsst_data[0]['show_captcha_on_visitor_from_ticket']);
                      $jsst_description =  esc_html(__('Show captcha when a visitor wants to create a ticket', 'js-support-ticket'));
                      $jsst_video = '-78pMXbZy8o';
                      $jsst_videotext = 'Show Captcha On The Visitor Ticket Form';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description, $jsst_video, '', $jsst_videotext);
                    }

                    // Provider choice replaces the old two-way captcha_selection.
                    // The row is still read to derive the provider for sites that
                    // have not saved this screen since upgrading, so it stays in
                    // the database untouched. (Roadmap 4.0-SEC-01)
                    if(isset(jssupportticket::$jsst_data[0]['captcha_provider'])){
                      $jsst_title = esc_html(__('Verification method', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('captcha_provider', $jsst_captcha_providers, JSSTverification::provider());
                      $jsst_description =  esc_html(__('How visitors are checked to be human. The built-in method needs no account and no third-party request: it uses a hidden field, a submission-timing check and a small automatic browser calculation, and it shows a simple question only if JavaScript is switched off.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_min_submit_seconds'])){
                      $jsst_title = esc_html(__('Minimum seconds before submit', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_min_submit_seconds', jssupportticket::$jsst_data[0]['captcha_min_submit_seconds'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('A form sent faster than this is treated as automated. 3 seconds suits most forms. Built-in method only.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_pow_bits'])){
                      $jsst_title = esc_html(__('Browser check difficulty', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_pow_bits', jssupportticket::$jsst_data[0]['captcha_pow_bits'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Work the visitor\'s browser does silently, in leading zero bits (0 to 24, in steps of 4). 12 takes a few milliseconds; 16 takes about a second on a slow phone. 0 turns the calculation off and leaves the hidden field and timing checks. Built-in method only.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_fail_open'])){
                      $jsst_title = esc_html(__('Allow submissions if the provider is unreachable', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('captcha_fail_open', $jsst_yesno, jssupportticket::$jsst_data[0]['captcha_fail_open']);
                      $jsst_description =  esc_html(__('When Turnstile, hCaptcha or reCAPTCHA cannot be reached, accept the ticket and record the failure rather than blocking the customer. Recommended.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('Submission Rate Limits', 'js-support-ticket')); ?></h2>

                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['submission_rate_limit'])){
                      $jsst_title = esc_html(__('Limit submissions per visitor', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('submission_rate_limit', $jsst_yesno, jssupportticket::$jsst_data[0]['submission_rate_limit']);
                      $jsst_description =  esc_html(__('Caps how often one visitor can submit the ticket or registration form. Agents and administrators are never limited.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['submission_rate_limit_max'])){
                      $jsst_title = esc_html(__('Submissions allowed', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('submission_rate_limit_max', jssupportticket::$jsst_data[0]['submission_rate_limit_max'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('How many submissions one visitor may send inside the window below.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['submission_rate_limit_window'])){
                      $jsst_title = esc_html(__('Window in seconds', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('submission_rate_limit_window', jssupportticket::$jsst_data[0]['submission_rate_limit_window'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Length of the rolling window the limit above applies to. 600 is ten minutes.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('Cloudflare Turnstile', 'js-support-ticket')); ?></h2>

                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['captcha_turnstile_sitekey'])){
                      $jsst_title = esc_html(__('Turnstile Site Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_turnstile_sitekey', jssupportticket::$jsst_data[0]['captcha_turnstile_sitekey'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('From your Cloudflare dashboard under Turnstile.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_turnstile_secret'])){
                      $jsst_title = esc_html(__('Turnstile Secret Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_turnstile_secret', jssupportticket::$jsst_data[0]['captcha_turnstile_secret'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Used only on the server to verify each submission.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('hCaptcha', 'js-support-ticket')); ?></h2>

                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['captcha_hcaptcha_sitekey'])){
                      $jsst_title = esc_html(__('hCaptcha Site Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_hcaptcha_sitekey', jssupportticket::$jsst_data[0]['captcha_hcaptcha_sitekey'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('From your hCaptcha account.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_hcaptcha_secret'])){
                      $jsst_title = esc_html(__('hCaptcha Secret Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_hcaptcha_secret', jssupportticket::$jsst_data[0]['captcha_hcaptcha_secret'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Used only on the server to verify each submission.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('Google reCAPTCHA v3', 'js-support-ticket')); ?></h2>

                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['captcha_recaptcha3_sitekey'])){
                      $jsst_title = esc_html(__('reCAPTCHA v3 Site Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_recaptcha3_sitekey', jssupportticket::$jsst_data[0]['captcha_recaptcha3_sitekey'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('A v3 key is not interchangeable with a v2 key.', 'js-support-ticket')).' https://www.google.com/recaptcha/admin ';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_recaptcha3_secret'])){
                      $jsst_title = esc_html(__('reCAPTCHA v3 Secret Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_recaptcha3_secret', jssupportticket::$jsst_data[0]['captcha_recaptcha3_secret'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Used only on the server to verify each submission.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['captcha_score_threshold'])){
                      $jsst_title = esc_html(__('Minimum score to accept', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('captcha_score_threshold', jssupportticket::$jsst_data[0]['captcha_score_threshold'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Between 0 and 1. Google\'s own default is 0.5; raise it to be stricter, lower it if real customers are being turned away.', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('Google reCaptcha v2', 'js-support-ticket')); ?></h2>
                    
                    <?php
                    // The version is now part of the verification method above, so
                    // there is no second control that can contradict it. The row is
                    // left in place because it is what the provider is derived from
                    // on a site that has not saved this screen since upgrading.
                    // (Roadmap 4.0-SEC-01)
                    if(isset(jssupportticket::$jsst_data[0]['recaptcha_publickey'])){
                      $jsst_title = esc_html(__('Google reCaptcha Site Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('recaptcha_publickey', jssupportticket::$jsst_data[0]['recaptcha_publickey'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Please enter the google re-captcha site key from','js-support-ticket')).' https://www.google.com/recaptcha/admin ';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['recaptcha_privatekey'])){
                      $jsst_title = esc_html(__('Google reCaptcha Secret Key', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::text('recaptcha_privatekey', jssupportticket::$jsst_data[0]['recaptcha_privatekey'], array('class' => 'inputbox'));
                      $jsst_description =  esc_html(__('Please enter the google re-captcha secret key from','js-support-ticket')).' https://www.google.com/recaptcha/admin ';
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }
                    ?>

                    <h2><?php echo esc_html(__('No-JavaScript Fallback Question', 'js-support-ticket')); ?></h2>
                    <p class="jsst-config-note"><?php echo esc_html(__('The built-in method shows this arithmetic question only to visitors with JavaScript switched off. Everyone else is checked invisibly.', 'js-support-ticket')); ?></p>

                    <?php
                    if(isset(jssupportticket::$jsst_data[0]['owncaptcha_calculationtype'])){
                      $jsst_title = esc_html(__('Fallback Calculation Type', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('owncaptcha_calculationtype', $jsst_owncaptchatype, jssupportticket::$jsst_data[0]['owncaptcha_calculationtype']);
                      $jsst_description =  esc_html(__('Select calculation type addition or subtraction', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                   if(isset(jssupportticket::$jsst_data[0]['owncaptcha_totaloperand'])){
                      $jsst_title = esc_html(__('Own Captcha Operands', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('owncaptcha_totaloperand', $jsst_owncaptchaoparend, jssupportticket::$jsst_data[0]['owncaptcha_totaloperand']);
                      $jsst_description =  esc_html(__('Select the total operands to be given', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                    if(isset(jssupportticket::$jsst_data[0]['owncaptcha_subtractionans'])){
                      $jsst_title = esc_html(__('Own Captcha Subtraction Answer Positive', 'js-support-ticket'));
                      $jsst_field = JSSTformfield::select('owncaptcha_subtractionans', $jsst_yesno, jssupportticket::$jsst_data[0]['owncaptcha_subtractionans']);
                      $jsst_description =  esc_html(__('Is subtraction answer should be positive', 'js-support-ticket'));
                      JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description);
                    }

                  ?>
              </div>
            </div>
            </div>
            <?php echo wp_kses(JSSTformfield::hidden('action', 'configuration_saveconfiguration'), JSST_ALLOWED_TAGS); ?>
            <?php echo wp_kses(JSSTformfield::hidden('form_request', 'jssupportticket'), JSST_ALLOWED_TAGS); ?>
            <div class="js-form-button jsst-config-savebar">
              <span class="jsst-config-changecount" aria-live="polite"></span>
              <?php echo wp_kses(JSSTformfield::submitbutton('save', esc_html(__('Save Configurations', 'js-support-ticket')), array('class' => 'button js-form-save')), JSST_ALLOWED_TAGS); ?>
            </div>
          </form>
        </div>
    </div>
</div>
<?php

    function JSST_printConfigFieldSingle($jsst_title, $jsst_field, $jsst_description = '', $jsst_video = '', $jsst_childfield = '', $jsst_videotext = '', $jsst_actionbtn = ''){
        $jsst_html = '';
        $jsst_html .= '
            <div class="js-ticket-configuration-row">
                <div class="js-ticket-configuration-title">';
                $jsst_html .= esc_html($jsst_title).'</div>
                <div class="js-ticket-configuration-value">'.wp_kses($jsst_field, JSST_ALLOWED_TAGS);
                  if($jsst_childfield !=''){
                      $jsst_html .= '<div class="js-ticket-configuration-value childfield">'.wp_kses($jsst_childfield, JSST_ALLOWED_TAGS).'</div>';
                  }
                  if($jsst_description !=''){
                      $jsst_html .= '<div class="js-ticket-configuration-description">'.wp_kses($jsst_description, JSST_ALLOWED_TAGS).'</div>';
                  }
                $jsst_html .= '</div>';
                if(isset($jsst_video) && $jsst_video != ''){
                    $jsst_html .= '<div class="js-ticket-configuration-video">
                      <a target="blank" href="https://www.youtube.com/watch?v='.esc_attr($jsst_video).'" class="js-tkt-det-hdg-img js-cp-video-'.esc_attr($jsst_video).'">
                        <img title="'. esc_html(__('Watch Video','js-support-ticket')) .'" alt="'. esc_html(__('Watch Video','js-support-ticket')).'" src="'. JSST_PLUGIN_URL . '/includes/images/watch-video-icon-config.png" />
                        <span></span>
                      </a>';
                      if(isset($jsst_actionbtn) && $jsst_actionbtn != ''){
                        $jsst_html .= '<a href="?page=email&jstlay=addemail" class="js-ticket-configuration-btn">
                                    <img title="'. esc_html(__('Add','js-support-ticket')) .'" alt="'. esc_html(__('Add','js-support-ticket')).'" src="'. JSST_PLUGIN_URL . '/includes/images/plus-icon.png" />
                                    '. esc_html(jssupportticket::JSST_getVarValue($jsst_actionbtn)).'
                                  </a>';
                      }
                    $jsst_html .= '</div>';
                }
                  

        $jsst_html .= '
            </div>';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

    function JSST_printConfigFieldMulti($jsst_title, $jsst_field1, $jsst_field2, $jsst_field3){
        $jsst_html = '';

        $jsst_html = '
        <div class="js-ticket-configuration-row-mail">
            <div class="js-ticket-configuration-title">'.esc_html($jsst_title).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('Agent','js-support-ticket')) .'</span>'.wp_kses($jsst_field1, JSST_ALLOWED_TAGS).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('User','js-support-ticket')) .'</span>'.wp_kses($jsst_field2, JSST_ALLOWED_TAGS).'</div>
            <div class="js-ticket-configuration-value"><span class="js-ticket-config-xs-show-hide">'. esc_html(__('Admin','js-support-ticket')) .'</span>'.wp_kses($jsst_field3, JSST_ALLOWED_TAGS).'</div>
        </div>
        ';
        echo wp_kses($jsst_html, JSST_ALLOWED_TAGS);
    }

 ?>
