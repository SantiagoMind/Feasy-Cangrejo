<?php

/**
 * Obtiene la clave secreta usada para firmar los datos.
 *
 * Puedes definirla mediante la constante FEASY_HMAC_SECRET en wp-config.php
 * o como variable de entorno del mismo nombre.
 */
function feasy_get_hmac_secret() {
    if (defined('FEASY_HMAC_SECRET')) {
        return FEASY_HMAC_SECRET;
    }

    $env = getenv('FEASY_HMAC_SECRET');
    return $env ? $env : '';
}

/**
 * Genera un token temporal para autenticación.
 *
 * El token está compuesto por el timestamp y una firma HMAC del mismo,
 * separados por dos puntos. Expira a los pocos minutos.
 */
function feasy_generate_auth_token($secret) {
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp, $secret);
    return $timestamp . ':' . $signature;
}

/**
 * Función para procesar el envío del formulario y enviarlo a un endpoint de Google Apps Script.
 * Este handler trabaja exclusivamente con AJAX (admin-ajax.php).
 */
function proyecto_cangrejo_handle_form_submission_ajax() {
    // Verificar que sea una solicitud POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Método no permitido']);
    }

    // Validar nonce para evitar CSRF
    check_ajax_referer('proyecto_cangrejo_form', 'cangrejo_nonce');

    // Obtener el endpoint dinámico desde el formulario enviado
    $endpoint_url = isset($_POST['_endpoint_url']) ? esc_url_raw($_POST['_endpoint_url']) : '';

    // Validar que exista el endpoint
    if (empty($endpoint_url)) {
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Endpoint no proporcionado']);
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

    // Preparar cuerpo y firma HMAC para asegurar la integridad de los datos
    $headers = ['Content-Type' => 'application/json'];

    $secret = feasy_get_hmac_secret();
    if (!empty($secret)) {
        // Token dinámico incluido en el cuerpo
        $data['__token'] = feasy_generate_auth_token($secret);

        $body_to_sign = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature    = hash_hmac('sha256', $body_to_sign, $secret);

        // Firma dentro del cuerpo para que Apps Script pueda validarla
        $data['__signature'] = $signature;

        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $headers['X-Feasy-Signature'] = $signature; // opcional para otros entornos
    } else {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Enviar los datos al endpoint de Google Apps Script
    $response = wp_remote_post($endpoint_url, [
        'method'  => 'POST',
        'headers' => $headers,
        'body'    => $body,
    ]);

    if (is_wp_error($response)) {
        error_log('Error al enviar datos al endpoint: ' . $response->get_error_message());
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Error al enviar los datos.']);
    }

    // ✅ Éxito: devolver respuesta JSON para el frontend (JS)
    header('Content-Type: application/json; charset=utf-8');
    wp_send_json_success(['message' => 'Formulario enviado correctamente']);
}

// Registrar la acción AJAX (logueados y no logueados)
add_action('wp_ajax_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');