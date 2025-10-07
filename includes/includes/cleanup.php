<?php
// ==================================================
// Utilities to clean up plugin data on uninstall
// ==================================================

if (!defined('ABSPATH')) {
    exit;
}

class Feasy_Cleanup {
    /**
     * Remove all plugin data stored in WordPress.
     */
    public static function run() {
        // Delete transients used by the updater
        delete_transient('feasy_libraries_last_update');

        // Unschedule updater events in case they exist
        $timestamp = wp_next_scheduled('cangrejo_update_libraries');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'cangrejo_update_libraries');
        }

        // Delete any options that might be added in the future
        $options = apply_filters('feasy_cleanup_options', []);
        foreach ($options as $option) {
            delete_option($option);
        }

        // Allow additional cleanup via hooks
        do_action('feasy_cleanup_extra');

        // Attempt to clear browser localStorage keys
        self::output_localstorage_script();
    }

    /**
     * Output JS snippet to wipe localStorage for this plugin.
     */
    protected static function output_localstorage_script() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Avoid breaking JSON responses during AJAX deletions.
            return;
        }
        echo '<script>';
        echo 'if (typeof localStorage !== "undefined") {';
        echo '  Object.keys(localStorage)';
        echo '    .filter(function(k){ return k.startsWith("feasy_"); })';
        echo '    .forEach(function(k){ localStorage.removeItem(k); });';
        echo '}';
        echo '</script>';
    }
}