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
 * Almacena los datos del formulario que no pudieron enviarse.
 */
function feasy_store_failed_submission($data) {
    $dir = plugin_dir_path(__FILE__) . '../failed-submissions';
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $file = sprintf('%s/submission_%s.json', $dir, time() . '_' . uniqid());
    file_put_contents($file, wp_json_encode($data));

    error_log('[Feasy] Datos de envío guardados en: ' . $file);
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
        wp_die(); // 🚨 Detiene completamente la ejecución
    }

    // Validar nonce para evitar CSRF
    check_ajax_referer('proyecto_cangrejo_form', 'cangrejo_nonce');

    // Recopilar y sanitizar los datos enviados
    $data = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['action', '_endpoint_url', 'cangrejo_nonce'])) continue;

        if (is_array($value)) {
            $value = array_map('sanitize_text_field', $value);
            $data[$key] = json_encode($value);
        } else {
            if (is_string($value) && strpos($value, 'data:image') === 0) {
                $data[$key] = $value; // base64 image, keep as is
            } else {
                $data[$key] = sanitize_text_field($value);
            }
        }
    }

    /**
     * Resguardo automático en caso de error fatal.
     * Si la ejecución termina inesperadamente y la bandera "sent" permanece en falso,
     * los datos sanitizados se almacenarán localmente.
     */
    $feasy_shutdown = ['sent' => false, 'data' => &$data];
    register_shutdown_function(function () use (&$feasy_shutdown) {
        if (!$feasy_shutdown['sent']) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                feasy_store_failed_submission($feasy_shutdown['data']);
                error_log('[Feasy] Respaldo por error fatal: ' . $error['message']);
            }
        }
    });

    // Obtener el endpoint dinámico desde el formulario enviado
    $endpoint_url = isset($_POST['_endpoint_url']) ? esc_url_raw($_POST['_endpoint_url']) : '';

    // Validar que exista el endpoint
    if (empty($endpoint_url)) {
        feasy_store_failed_submission($data);
        error_log('[Feasy] Endpoint no proporcionado, datos almacenados.');
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Endpoint no proporcionado']);
        wp_die();
    }

    // Preparar cuerpo y firma HMAC para asegurar la integridad de los datos
    $headers = ['Content-Type' => 'application/json'];

    $secret = feasy_get_hmac_secret();
    if (!empty($secret)) {
        // Token dinámico incluido en el cuerpo
        $data['__token'] = feasy_generate_auth_token($secret);

        $body_to_sign = wp_json_encode($data);
        $signature    = hash_hmac('sha256', $body_to_sign, $secret);

        // Firma dentro del cuerpo para que Apps Script pueda validarla
        $data['__signature'] = $signature;

        $body = wp_json_encode($data);
        $headers['X-Feasy-Signature'] = $signature; // opcional para otros entornos
    } else {
        $body = wp_json_encode($data);
    }

    // Enviar los datos al endpoint de Google Apps Script
    $response = wp_remote_post($endpoint_url, [
        'method'  => 'POST',
        'headers' => $headers,
        'body'    => $body,
        // Aumentar tiempo de espera para envíos con imágenes base64 pesadas
        'timeout' => 40,
    ]);

    // Body for logging/validation
    $response_body = wp_remote_retrieve_body($response);

    if (is_wp_error($response)) {
        feasy_store_failed_submission($data);
        error_log('Error al enviar datos al endpoint: ' . $response->get_error_message());
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Error al enviar los datos.']);
        wp_die();
    }

    $status_code = wp_remote_retrieve_response_code($response);
     if ($status_code >= 400) {
        feasy_store_failed_submission($data);
        error_log('Respuesta no exitosa al enviar datos: HTTP ' . $status_code);
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Error al enviar los datos.']);
        wp_die();
    }

    $remote_json = json_decode($response_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        feasy_store_failed_submission($data);
        error_log('Respuesta JSON inválida: ' . json_last_error_msg());
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => 'Respuesta no válida del servidor']);
        wp_die();
    }

    if (is_array($remote_json) && isset($remote_json['status']) && $remote_json['status'] !== 'success') {
        feasy_store_failed_submission($data);
        $msg = isset($remote_json['message']) ? $remote_json['message'] : 'Error al enviar los datos.';
        error_log('Error reportado por endpoint: ' . $msg);
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        wp_send_json_error(['message' => $msg]);
        wp_die();
    }

    // ✅ Éxito: marcar envío y devolver respuesta JSON para el frontend (JS)
    $feasy_shutdown['sent'] = true;
    header('Content-Type: application/json; charset=utf-8');
    wp_send_json_success(['message' => 'Formulario enviado correctamente']);
    wp_die(); // ✅ Detener ejecución completamente
}

// Registrar la acción AJAX (logueados y no logueados)
add_action('wp_ajax_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');