<?php
// ==================================================
// Manejador de actualización automática de librerías
// ==================================================

add_action('init', 'cangrejo_schedule_library_updates');
function cangrejo_schedule_library_updates() {
    if (!wp_next_scheduled('cangrejo_update_libraries')) {
        wp_schedule_event(time(), 'daily', 'cangrejo_update_libraries');
    }
}

add_action('cangrejo_update_libraries', 'cangrejo_update_libraries');
function cangrejo_update_libraries() {
    $remote_url = 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js';
    $response   = wp_remote_get($remote_url);

    if (is_wp_error($response)) {
        error_log('[Feasy] ❌ Error al descargar Sortable: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    if ($body) {
        $path = plugin_dir_path(__FILE__) . '../assets/vendor/sortable/sortable.min.js';
        file_put_contents($path, $body);
        error_log('[Feasy] ✅ Librería Sortable actualizada automáticamente');
    }
}

function cangrejo_deactivate_library_updates() {
    $timestamp = wp_next_scheduled('cangrejo_update_libraries');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'cangrejo_update_libraries');
    }
}