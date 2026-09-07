<?php

if (!defined('ABSPATH'))
    die('Restricted Access');

/*
 * These classes are loaded from the plugin bootstrap with include_once. That
 * normally guarantees one declaration, but it deduplicates by resolved path, so
 * anything that reaches this file by a second spelling of the same path - or any
 * route that runs the bootstrap twice - redeclares the class and takes the whole
 * site down with a fatal. Returning early costs nothing and makes the file safe
 * to include however many times and by whatever route. (Roadmap 4.0-CORE-19)
 */
if (class_exists('JSSTcsvwriter')) {
    return;
}


/**
 * Streaming CSV output. (Roadmap 4.0-CORE-10, 3.2-CORE-05)
 *
 * Replaces the hand-built export payload, which had three problems that made it
 * not really a data format at all:
 *
 *   - it wrapped values in quotes and joined them with tabs, without escaping the
 *     quotes, tabs or newlines inside a value. One subject containing a quote
 *     shifted every following column on that row;
 *   - it assembled the whole report in a PHP string before sending, so a large
 *     export died on memory rather than downloading;
 *   - it passed that string through wp_kses() before printing, which is an HTML
 *     sanitiser: it rewrote ampersands and stripped anything resembling a tag out
 *     of the customer's own words.
 *
 * This writes RFC 4180 CSV through fputcsv, a row at a time, straight to the
 * output stream. Excel needs the UTF-8 byte order mark to read accented
 * characters from a CSV, so it is written first.
 *
 * Formulas are neutralised: a value beginning =, +, - or @ is prefixed with a
 * single quote, because spreadsheets execute those on open and a ticket subject
 * is attacker-supplied text.
 */
class JSSTcsvwriter {

    /** @var resource|null */
    private $jsst_handle = null;

    /** @var int */
    private $jsst_rows = 0;

    /**
     * Start a download. Sends the headers and opens the output stream.
     */
    public function start($jsst_basename) {
        $jsst_filename = sanitize_file_name($jsst_basename) . '.csv';
        // Anything already buffered would land inside the file.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $jsst_filename . '"');
        header('X-Content-Type-Options: nosniff');
        $this->jsst_handle = fopen('php://output', 'w');
        // The BOM is what makes Excel read this as UTF-8.
        fwrite($this->jsst_handle, "\xEF\xBB\xBF");
        return $this;
    }

    /**
     * Write to a string instead of the browser. Used by the tests, and by any
     * caller that wants the payload rather than a download.
     */
    public function startToMemory() {
        $this->jsst_handle = fopen('php://temp', 'r+');
        fwrite($this->jsst_handle, "\xEF\xBB\xBF");
        return $this;
    }

    /**
     * One row. Values are flattened to scalars first: a stray array or object
     * would otherwise emit "Array" and a PHP notice into the file.
     */
    public function row($jsst_values) {
        if ($this->jsst_handle === null) {
            return $this;
        }
        if (!is_array($jsst_values)) {
            $jsst_values = array($jsst_values);
        }
        $jsst_clean = array();
        foreach ($jsst_values as $jsst_value) {
            $jsst_clean[] = self::cell($jsst_value);
        }
        fputcsv($this->jsst_handle, $jsst_clean);
        $this->jsst_rows++;
        return $this;
    }

    /**
     * A blank separator row, for reports that hold several tables.
     */
    public function blank() {
        if ($this->jsst_handle !== null) {
            fwrite($this->jsst_handle, "\r\n");
        }
        return $this;
    }

    /**
     * A section caption on its own row.
     */
    public function section($jsst_title) {
        return $this->row(array($jsst_title));
    }

    /**
     * Many rows at once.
     */
    public function rows($jsst_rows) {
        if (!is_array($jsst_rows)) {
            return $this;
        }
        foreach ($jsst_rows as $jsst_row) {
            $this->row($jsst_row);
        }
        return $this;
    }

    public function rowCount() {
        return $this->jsst_rows;
    }

    /**
     * Finish a download.
     */
    public function finish() {
        if ($this->jsst_handle !== null) {
            fclose($this->jsst_handle);
            $this->jsst_handle = null;
        }
        exit;
    }

    /**
     * Finish an in-memory write and return what was written.
     */
    public function contents() {
        if ($this->jsst_handle === null) {
            return '';
        }
        rewind($this->jsst_handle);
        $jsst_out = stream_get_contents($this->jsst_handle);
        fclose($this->jsst_handle);
        $this->jsst_handle = null;
        return $jsst_out;
    }

    /**
     * One value, made safe for a spreadsheet.
     *
     * HTML is reduced to text — a reply body is stored as HTML and a raw <br> in
     * a cell is noise — and formula triggers are defused.
     */
    public static function cell($jsst_value) {
        if (is_null($jsst_value) || is_bool($jsst_value)) {
            $jsst_value = is_bool($jsst_value) ? ($jsst_value ? '1' : '0') : '';
        }
        if (is_array($jsst_value) || is_object($jsst_value)) {
            $jsst_value = '';
        }
        $jsst_value = (string) $jsst_value;
        if ($jsst_value === '') {
            return '';
        }
        // <br> and </p> become spaces so a paragraph does not collapse into one
        // run-on word once the tags are removed.
        $jsst_value = preg_replace('#<(br|/p|/div|/li)[^>]*>#i', ' ', $jsst_value);
        $jsst_value = wp_strip_all_tags($jsst_value);
        $jsst_value = html_entity_decode($jsst_value, ENT_QUOTES, 'UTF-8');
        // Control characters other than tab, newline and carriage return break
        // some readers; the writer quotes the survivors properly.
        $jsst_value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $jsst_value);
        $jsst_value = trim($jsst_value);
        if ($jsst_value === '') {
            return '';
        }
        if (in_array(substr($jsst_value, 0, 1), array('=', '+', '-', '@'), true)) {
            $jsst_value = "'" . $jsst_value;
        }
        return $jsst_value;
    }

}
