<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/**
 * Ticket tags — a first-class core object. (Roadmap 4.0-CORE-17)
 *
 * There is no add-on to merge here: tags are new. They arrive in 4.0 rather than
 * later because the REST API, webhooks, automation and reporting in v5.0 all
 * assume a ticket can carry tags, and adding the tables afterwards would force a
 * data migration on every install.
 *
 * Two tables: the tags themselves, and a join table linking them to tickets. A tag
 * is identified by a slug, so "Billing", "billing" and " BILLING " are one tag
 * rather than three, which is the failure that makes tagging useless in practice.
 */
class JSSTtagModel {

    /** Bumped when either table below changes. */
    const SCHEMA_VERSION = '400';

    /** A tag name longer than this is truncated rather than refused. */
    const MAX_NAME = 60;

    /**
     * Create the two tables if they are not there.
     */
    public static function ensureSchema() {
        // Both tables, because the map is useless without the tags and the
        // pair is created together. (see JSSTschemaguard)
        if (!JSSTschemaguard::needsRun('jsst_tag_schema', self::SCHEMA_VERSION,
                array('js_ticket_tags' => array(), 'js_ticket_ticket_tags' => array()))) {
            return;
        }
        $jsst_charset = jssupportticket::$_db->get_charset_collate();
        $jsst_tags = jssupportticket::$_db->prefix . 'js_ticket_tags';
        $jsst_map = jssupportticket::$_db->prefix . 'js_ticket_ticket_tags';

        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_tags . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    name varchar(60) NOT NULL,
                    slug varchar(60) NOT NULL,
                    created datetime DEFAULT NULL,
                    status tinyint(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (id),
                    UNIQUE KEY jsst_slug (slug)
                ) " . $jsst_charset);

        // The pair is unique: a ticket carries a tag once or not at all, and the
        // constraint is what makes double-adding harmless rather than duplicating.
        jssupportticket::$_db->query("CREATE TABLE IF NOT EXISTS `" . $jsst_map . "` (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    ticketid int(11) NOT NULL,
                    tagid int(11) NOT NULL,
                    created datetime DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY jsst_ticket_tag (ticketid, tagid),
                    KEY jsst_tagid (tagid)
                ) " . $jsst_charset);

        update_option('jsst_tag_schema', self::SCHEMA_VERSION, false);
    }

    /**
     * The comparable form of a tag name.
     *
     * Lower-cased, whitespace collapsed to single hyphens, and anything that is
     * not a letter, number or hyphen removed. Unicode letters are kept, so a
     * non-English tag is not reduced to nothing.
     */
    public static function slugify($jsst_name) {
        $jsst_name = jssupportticketphplib::JSST_strtolower(trim((string) $jsst_name));
        $jsst_name = preg_replace('/[\s_]+/u', '-', $jsst_name);
        $jsst_name = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $jsst_name);
        $jsst_name = preg_replace('/-+/', '-', $jsst_name);
        $jsst_name = trim((string) $jsst_name, '-');
        if (jssupportticketphplib::JSST_strlen($jsst_name) > self::MAX_NAME) {
            $jsst_name = jssupportticketphplib::JSST_substr($jsst_name, 0, self::MAX_NAME);
            $jsst_name = trim($jsst_name, '-');
        }
        return $jsst_name;
    }

    /**
     * A tag name as it will be shown: trimmed, collapsed, and capped.
     */
    public static function cleanName($jsst_name) {
        $jsst_name = trim(wp_strip_all_tags((string) $jsst_name));
        $jsst_name = preg_replace('/\s+/u', ' ', $jsst_name);
        if (jssupportticketphplib::JSST_strlen($jsst_name) > self::MAX_NAME) {
            $jsst_name = trim(jssupportticketphplib::JSST_substr($jsst_name, 0, self::MAX_NAME));
        }
        return $jsst_name;
    }

    /**
     * Split what an agent typed into distinct tag names.
     *
     * Commas separate tags, so a tag name cannot contain one.
     */
    public static function parseNames($jsst_input) {
        $jsst_names = array();
        if (is_array($jsst_input)) {
            $jsst_pieces = $jsst_input;
        } else {
            $jsst_pieces = explode(',', (string) $jsst_input);
        }
        foreach ($jsst_pieces as $jsst_piece) {
            $jsst_name = self::cleanName($jsst_piece);
            if ($jsst_name === '') {
                continue;
            }
            $jsst_slug = self::slugify($jsst_name);
            if ($jsst_slug === '') {
                continue;
            }
            // Keyed by slug, so two spellings of one tag collapse.
            $jsst_names[$jsst_slug] = $jsst_name;
        }
        return $jsst_names;
    }

    /**
     * The tag with this name, created if it does not exist yet.
     *
     * @return int The tag id, or 0 when the name is not usable.
     */
    public function findOrCreate($jsst_name) {
        self::ensureSchema();
        $jsst_name = self::cleanName($jsst_name);
        $jsst_slug = self::slugify($jsst_name);
        if ($jsst_slug === '') {
            return 0;
        }
        $jsst_existing = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
            "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tags` WHERE slug = %s",
            $jsst_slug
        ));
        if ($jsst_existing) {
            return (int) $jsst_existing;
        }
        jssupportticket::$_db->insert(
            jssupportticket::$_db->prefix . 'js_ticket_tags',
            array('name' => $jsst_name, 'slug' => $jsst_slug, 'created' => date_i18n('Y-m-d H:i:s'), 'status' => 1),
            array('%s', '%s', '%s', '%d')
        );
        if (jssupportticket::$_db->last_error != null) {
            // Another request may have created the same slug between the check and
            // the insert; the unique key makes that safe to recover from.
            $jsst_existing = jssupportticket::$_db->get_var(jssupportticket::$_db->prepare(
                "SELECT id FROM `" . jssupportticket::$_db->prefix . "js_ticket_tags` WHERE slug = %s",
                $jsst_slug
            ));
            return $jsst_existing ? (int) $jsst_existing : 0;
        }
        return (int) jssupportticket::$_db->insert_id;
    }

    /**
     * The tags on one ticket, in name order.
     */
    public function getTicketTags($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid)) {
            return array();
        }
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT tag.id, tag.name, tag.slug
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_ticket_tags` AS map
                INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tags` AS tag ON map.tagid = tag.id
                WHERE map.ticketid = %d
                ORDER BY tag.name ASC",
            $jsst_ticketid
        ));
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * Attach a tag to a ticket. Attaching twice is a no-op.
     */
    public function addTagToTicket($jsst_ticketid, $jsst_tagid) {
        if (!is_numeric($jsst_ticketid) || !is_numeric($jsst_tagid) || $jsst_tagid <= 0) {
            return false;
        }
        self::ensureSchema();
        jssupportticket::$_db->query(jssupportticket::$_db->prepare(
            "INSERT IGNORE INTO `" . jssupportticket::$_db->prefix . "js_ticket_ticket_tags` (ticketid, tagid, created)
                VALUES (%d, %d, %s)",
            $jsst_ticketid,
            $jsst_tagid,
            date_i18n('Y-m-d H:i:s')
        ));
        return true;
    }

    /**
     * Detach a tag from a ticket.
     */
    public function removeTagFromTicket($jsst_ticketid, $jsst_tagid) {
        if (!is_numeric($jsst_ticketid) || !is_numeric($jsst_tagid)) {
            return false;
        }
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_ticket_tags',
            array('ticketid' => (int) $jsst_ticketid, 'tagid' => (int) $jsst_tagid),
            array('%d', '%d')
        );
        return true;
    }

    /**
     * Replace a ticket's tags with exactly this set, and record the change.
     *
     * Returns array('added' => array of names, 'removed' => array of names).
     */
    public function setTicketTags($jsst_ticketid, $jsst_input) {
        if (!is_numeric($jsst_ticketid)) {
            return array('added' => array(), 'removed' => array());
        }
        self::ensureSchema();
        $jsst_wanted = self::parseNames($jsst_input);
        $jsst_current = array();
        foreach ($this->getTicketTags($jsst_ticketid) AS $jsst_tag) {
            $jsst_current[$jsst_tag->slug] = $jsst_tag;
        }

        $jsst_added = array();
        foreach ($jsst_wanted AS $jsst_slug => $jsst_name) {
            if (isset($jsst_current[$jsst_slug])) {
                continue;
            }
            $jsst_tagid = $this->findOrCreate($jsst_name);
            if ($jsst_tagid > 0 && $this->addTagToTicket($jsst_ticketid, $jsst_tagid)) {
                $jsst_added[] = $jsst_name;
            }
        }
        $jsst_removed = array();
        foreach ($jsst_current AS $jsst_slug => $jsst_tag) {
            if (isset($jsst_wanted[$jsst_slug])) {
                continue;
            }
            $this->removeTagFromTicket($jsst_ticketid, $jsst_tag->id);
            $jsst_removed[] = $jsst_tag->name;
        }

        if (!empty($jsst_added) || !empty($jsst_removed)) {
            $this->recordChange($jsst_ticketid, $jsst_added, $jsst_removed);
        }
        return array('added' => $jsst_added, 'removed' => $jsst_removed);
    }

    /**
     * Put a tag change on the activity timeline, which is core from 4.0.
     * (Roadmap 4.0-CORE-01, 4.0-CORE-17)
     */
    private function recordChange($jsst_ticketid, $jsst_added, $jsst_removed) {
        $jsst_parts = array();
        if (!empty($jsst_added)) {
            $jsst_parts[] = sprintf(
                /* translators: %s: comma-separated tag names */
                esc_html(__('added %s', 'js-support-ticket')),
                esc_html(implode(', ', $jsst_added))
            );
        }
        if (!empty($jsst_removed)) {
            $jsst_parts[] = sprintf(
                /* translators: %s: comma-separated tag names */
                esc_html(__('removed %s', 'js-support-ticket')),
                esc_html(implode(', ', $jsst_removed))
            );
        }
        $jsst_current_user = JSSTincluder::getObjectClass('user')->getJSSTCurrentUser();
        $jsst_actor = isset($jsst_current_user->display_name) ? $jsst_current_user->display_name : '';
        $jsst_message = sprintf(
            /* translators: 1: what changed, 2: user name */
            esc_html(__('Tags %1$s by ( %2$s )', 'js-support-ticket')),
            implode('; ', $jsst_parts),
            esc_html($jsst_actor)
        );
        JSSTticketaction::audit(
            $jsst_ticketid,
            esc_html(__('Ticket tags', 'js-support-ticket')),
            $jsst_message,
            esc_html(__('Successfully', 'js-support-ticket'))
        );
    }

    /**
     * Every tag in use, with how many tickets carry it, for the filter control.
     */
    public function getTagsForCombobox() {
        self::ensureSchema();
        $jsst_rows = jssupportticket::$_db->get_results(
            "SELECT tag.id, tag.name AS text, COUNT(map.id) AS total
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_tags` AS tag
                LEFT JOIN `" . jssupportticket::$_db->prefix . "js_ticket_ticket_tags` AS map ON map.tagid = tag.id
                WHERE tag.status = 1
                GROUP BY tag.id, tag.name
                ORDER BY tag.name ASC"
        );
        return is_array($jsst_rows) ? $jsst_rows : array();
    }

    /**
     * The tags on a set of tickets, keyed by ticket id.
     *
     * One query for a whole page of the queue rather than one per row.
     * (Roadmap 4.0-PERF-01)
     */
    public function getTagsForTickets($jsst_ticketids) {
        $jsst_out = array();
        $jsst_ids = array();
        foreach ((array) $jsst_ticketids as $jsst_id) {
            if (is_numeric($jsst_id) && (int) $jsst_id > 0) {
                $jsst_ids[(int) $jsst_id] = (int) $jsst_id;
            }
        }
        if (empty($jsst_ids)) {
            return $jsst_out;
        }
        self::ensureSchema();
        $jsst_placeholders = implode(',', array_fill(0, count($jsst_ids), '%d'));
        $jsst_rows = jssupportticket::$_db->get_results(jssupportticket::$_db->prepare(
            "SELECT map.ticketid, tag.id, tag.name, tag.slug
                FROM `" . jssupportticket::$_db->prefix . "js_ticket_ticket_tags` AS map
                INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_tags` AS tag ON map.tagid = tag.id
                WHERE map.ticketid IN (" . $jsst_placeholders . ")
                ORDER BY tag.name ASC",
            array_values($jsst_ids)
        ));
        if (!is_array($jsst_rows)) {
            return $jsst_out;
        }
        foreach ($jsst_rows AS $jsst_row) {
            $jsst_out[(int) $jsst_row->ticketid][] = $jsst_row;
        }
        return $jsst_out;
    }

    /**
     * Remove a ticket's tag links. Called when a ticket is deleted so the join
     * table does not accumulate rows pointing at nothing.
     */
    public function removeTicketTags($jsst_ticketid) {
        if (!is_numeric($jsst_ticketid)) {
            return false;
        }
        jssupportticket::$_db->delete(
            jssupportticket::$_db->prefix . 'js_ticket_ticket_tags',
            array('ticketid' => (int) $jsst_ticketid),
            array('%d')
        );
        return true;
    }

    /**
     * The join and where a tag filter adds to a queue query.
     *
     * Returned rather than applied, so the caller keeps control of its own
     * argument order.
     */
    public static function queueFilter($jsst_tagid) {
        if (!is_numeric($jsst_tagid) || (int) $jsst_tagid <= 0) {
            return array('join' => '', 'where' => '', 'args' => array());
        }
        return array(
            'join'  => " INNER JOIN `" . jssupportticket::$_db->prefix . "js_ticket_ticket_tags` AS tagmap ON tagmap.ticketid = ticket.id ",
            'where' => " AND tagmap.tagid = %d",
            'args'  => array((int) $jsst_tagid),
        );
    }

}
