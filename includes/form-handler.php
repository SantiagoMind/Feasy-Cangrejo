<?php
/**
 * Función para procesar el envío del formulario y enviarlo a un endpoint de Google Apps Script.
 * Este handler trabaja exclusivamente con AJAX (admin-ajax.php).
 */
function proyecto_cangrejo_handle_form_submission_ajax() {
    // Verificar que sea una solicitud POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Método no permitido']);
        wp_die(); // 🚨 Detiene completamente la ejecución
    }

    // Validar nonce para evitar CSRF
    check_ajax_referer('proyecto_cangrejo_form', 'cangrejo_nonce');

    // Obtener el endpoint dinámico desde el formulario enviado
    $endpoint_url = isset($_POST['_endpoint_url']) ? esc_url_raw($_POST['_endpoint_url']) : '';

    // Validar que exista el endpoint
    if (empty($endpoint_url)) {
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Endpoint no proporcionado']);
        wp_die();
    }

    // Recopilar y sanitizar los datos enviados
    $data = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['action', '_endpoint_url'])) continue;

        if (is_array($value)) {
            $value = array_map('sanitize_text_field', $value);
            $data[$key] = json_encode($value);
        } else {
            $data[$key] = sanitize_text_field($value);
        }
    }

    // Enviar los datos al endpoint de Google Apps Script
    $response = wp_remote_post($endpoint_url, [
        'method'  => 'POST',
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => wp_json_encode($data),
    ]);

    if (is_wp_error($response)) {
        error_log('Error al enviar datos al endpoint: ' . $response->get_error_message());
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Error al enviar los datos.']);
        wp_die();
    }

    // ✅ Éxito: devolver respuesta JSON para el frontend (JS)
    header('Content-Type: application/json; charset=utf-8');
    wp_send_json_success(['message' => 'Formulario enviado correctamente']);
    wp_die(); // ✅ Detener ejecución completamente
}

// Registrar la acción AJAX (logueados y no logueados)
add_action('wp_ajax_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');