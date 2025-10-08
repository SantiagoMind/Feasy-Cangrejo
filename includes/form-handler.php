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
 * Interpreta valores devueltos por Apps Script (u otros endpoints) como bandera de éxito.
 *
 * Devuelve true si el valor representa éxito, false si representa un fallo,
 * y null si no puede determinarse con certeza.
 */
function feasy_interpret_remote_status($value) {
    if (is_bool($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return intval($value) === 1 ? true : (intval($value) === 0 ? false : null);
    }

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        $truthy = ['success', 'ok', 'done', 'true', '1', 'yes'];
        $falsy  = ['error', 'fail', 'failed', 'false', '0', 'no'];

        if (in_array($normalized, $truthy, true)) {
            return true;
        }

        if (in_array($normalized, $falsy, true)) {
            return false;
        }

        return null;
    }

    return null;
}

/**
 * Normaliza cadenas JSON provenientes de servicios externos eliminando BOM y caracteres de control.
 */
function feasy_normalize_remote_body($body) {
    if (!is_string($body) || $body === '') {
        return $body;
    }

    // Elimina BOM UTF-8 si está presente.
    if (substr($body, 0, 3) === "\xEF\xBB\xBF") {
        $body = substr($body, 3);
    }

    // Quita caracteres de control no permitidos en JSON (excepto saltos de línea y tabulaciones).
    $body = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $body);

    return trim($body);
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
    file_put_contents($file, feasy_wp_json_encode($data));

    error_log('[Feasy] Datos de envío guardados en: ' . $file);
}

/**
 * Determina si un WP_Error está relacionado con un timeout en la solicitud HTTP.
 */
function feasy_is_timeout_error($error) {
    if (!($error instanceof WP_Error)) {
        return false;
    }

    foreach ($error->get_error_codes() as $code) {
        $code = strtolower((string) $code);
        if ($code === 'request_timed_out' || strpos($code, 'timeout') !== false) {
            return true;
        }
    }

    foreach ($error->get_error_messages() as $message) {
        $message = strtolower((string) $message);
        if (strpos($message, 'timed out') !== false || strpos($message, 'timeout') !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Wrapper para codificar JSON igual que el receptor (JSON.stringify en Apps Script)
 * y evitar las conversiones JSON_HEX_* que aplica WordPress por defecto.
 */
if (!function_exists('feasy_wp_json_encode')) {
    function feasy_wp_json_encode($data) {
        $options = 0;

        if (defined('JSON_UNESCAPED_UNICODE')) {
            $options |= JSON_UNESCAPED_UNICODE;
        }

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $options |= JSON_UNESCAPED_SLASHES;
        }

        return wp_json_encode($data, $options);
    }
}

if (!function_exists('feasy_trim_debug_value')) {
    function feasy_trim_debug_value($value, $limit = 600) {
        if (is_array($value) || is_object($value)) {
            $value = wp_json_encode($value);
        }

        if (!is_string($value)) {
            $value = strval($value);
        }

        if ($value === '') {
            return $value;
        }

        $has_mb = function_exists('mb_strlen') && function_exists('mb_substr');

        if ($has_mb) {
            $length = mb_strlen($value, 'UTF-8');
            if ($length > $limit) {
                return mb_substr($value, 0, $limit, 'UTF-8') . '…';
            }
            return $value;
        }

        if (strlen($value) > $limit) {
            return substr($value, 0, $limit) . '…';
        }

        return $value;
    }
}

if (!function_exists('feasy_extract_first_string')) {
    function feasy_extract_first_string($value) {
        if (is_string($value)) {
            $value = trim(wp_strip_all_tags($value));
            return $value === '' ? null : $value;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $found = feasy_extract_first_string($item);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

if (!function_exists('feasy_parse_remote_error_message')) {
    function feasy_parse_remote_error_message($body) {
        if (!is_string($body) || $body === '') {
            return '';
        }

        $decoded = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach (['message', 'error', 'errors', 'detail'] as $key) {
                if (!array_key_exists($key, $decoded)) {
                    continue;
                }

                $candidate = feasy_extract_first_string($decoded[$key]);
                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return trim(wp_strip_all_tags($body));
    }
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
            $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
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
    $headers = ['Content-Type' => 'application/json; charset=utf-8'];
    $secret  = feasy_get_hmac_secret();

    if (!empty($secret)) {
        // 1) Token temporal
        $data['__token'] = feasy_generate_auth_token($secret);

        // 2) Firmar sobre el JSON EXACTO con slashes escapados:
        //    IMPORTANTE: usar wp_json_encode (NO feasy_wp_json_encode) para que queden "\/"
        $body_to_sign = wp_json_encode($data);
        $signature    = hash_hmac('sha256', $body_to_sign, $secret);

        // 3) Incluir la firma en el cuerpo
        $data['__signature'] = $signature;

        // 4) Codificar el body FINAL (otra vez con wp_json_encode)
        $body = wp_json_encode($data);

        // 5) Cabecera opcional (déjala)
        $headers['X-Feasy-Signature'] = $signature;
    } else {
        // Sin secret, solo enviar el JSON
        $body = wp_json_encode($data);
    }

    // Enviar los datos al endpoint de Google Apps Script
    $request_args = [
        'method'  => 'POST',
        'headers' => $headers,
        'body'    => $body,
        // Tiempo de espera extendido para cargas con imágenes base64 pesadas
        'timeout' => apply_filters('feasy_remote_timeout', 60, $endpoint_url, $data),
    ];

    $request_args = apply_filters('feasy_remote_request_args', $request_args, $endpoint_url, $data);

    $response = wp_remote_post($endpoint_url, $request_args);

    // Body for logging/validation
    $response_body   = wp_remote_retrieve_body($response);
    $normalized_body = feasy_normalize_remote_body($response_body);

     if (is_wp_error($response)) {
        feasy_store_failed_submission($data);

        $error_codes = $response->get_error_codes();
        $error_message = $response->get_error_message();
        $log_context  = sprintf('[Feasy] Error al enviar datos al endpoint (%s): %s', $endpoint_url, $error_message);
        error_log($log_context);

        if (!empty($error_codes)) {
            error_log('[Feasy] Códigos de error: ' . implode(', ', $error_codes));
        }

        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        $payload = [
            'message' => 'Error al enviar los datos.',
            'details' => [
                'endpoint'      => $endpoint_url,
                'error_message' => $error_message,
            ],
        ];

        if (!empty($error_codes)) {
            $payload['details']['error_codes'] = $error_codes;
        }

        if (is_string($normalized_body) && $normalized_body !== '') {
            $payload['details']['raw_body'] = feasy_trim_debug_value($normalized_body);
            $payload['details']['raw_body_full'] = $normalized_body;
        }

        wp_send_json_error($payload);
        wp_die();
    }

    $status_code = wp_remote_retrieve_response_code($response);

    $remote_json          = null;
    $remote_json_error    = null;
    $status_keys          = ['status', 'success', 'ok', 'result'];
    $status_value         = null;
    $normalized_status    = '';
    $interpreted_status   = null;

    if ($normalized_body !== '') {
        $decoded = json_decode($normalized_body, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $remote_json = $decoded;

            foreach ($status_keys as $key) {
                if (!array_key_exists($key, $remote_json)) {
                    continue;
                }

                $status_value = $remote_json[$key];

                if (is_string($status_value)) {
                    $normalized_status = strtolower(trim($status_value));
                }

                $interpreted_status = feasy_interpret_remote_status($status_value);
                break;
            }
        } else {
            $remote_json_error = json_last_error_msg();
        }
    }

    if ($status_code >= 400 && $interpreted_status !== true) {
        feasy_store_failed_submission($data);
        $remote_message = feasy_parse_remote_error_message($normalized_body);
        $fallback_msg   = sprintf('Error al enviar los datos. (HTTP %d)', $status_code);
        $error_message  = $remote_message !== '' ? $remote_message : $fallback_msg;

        error_log(sprintf('[Feasy] Respuesta no exitosa del endpoint (%s): HTTP %d. Mensaje: %s', $endpoint_url, $status_code, feasy_trim_debug_value($error_message)));
        if ($normalized_body !== '') {
            error_log('[Feasy] Cuerpo devuelto: ' . feasy_trim_debug_value($normalized_body));
        }

        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');
        $error_details = [
            'endpoint'    => $endpoint_url,
            'http_status' => $status_code,
        ];

        if (is_string($normalized_body) && $normalized_body !== '') {
            $error_details['raw_body'] = feasy_trim_debug_value($normalized_body);
            $error_details['raw_body_full'] = $normalized_body;
        }

        if ($remote_json_error !== null) {
            $error_details['json_error'] = $remote_json_error;
        }

        wp_send_json_error([
            'message' => $error_message,
            'details' => $error_details,
        ], $status_code);
        wp_die();
    }

    if ($status_code >= 400 && $interpreted_status === true) {
        error_log(sprintf('[Feasy] Aviso: endpoint (%s) respondió HTTP %d pero indicó éxito.', $endpoint_url, $status_code));
    }

    if (!is_array($remote_json)) {
        feasy_store_failed_submission($data);
        $feasy_shutdown['sent'] = true;
        header('Content-Type: application/json; charset=utf-8');

        $payload = ['message' => 'Respuesta no válida del servidor'];
        $details = [];

        if (is_string($normalized_body) && $normalized_body !== '') {
            $details['endpoint'] = $endpoint_url;
            $details['raw_body'] = feasy_trim_debug_value($normalized_body);
            $details['raw_body_full'] = $normalized_body;
        }

        if ($remote_json_error !== null) {
            $details['json_error'] = $remote_json_error;
        }

        if (!empty($details)) {
            $payload['details'] = $details;
        }

        wp_send_json_error($payload);
        wp_die();
    }

    $message = (isset($remote_json['message']) && is_string($remote_json['message']))
        ? $remote_json['message']
        : 'Datos agregados correctamente.';

    if ($message === '') {
        $message = 'Datos agregados correctamente.';
    }

    if ($interpreted_status === true) {
        $feasy_shutdown['sent'] = true;

        $payload = [
            'status'  => $normalized_status === '' ? 'success' : $normalized_status,
            'message' => $message,
        ];

        foreach (['data', 'payload', 'details'] as $extra_key) {
            if (isset($remote_json[$extra_key])) {
                $payload[$extra_key] = $remote_json[$extra_key];
            }
        }

        wp_send_json($payload);
    }

    if ($interpreted_status === false) {
        feasy_store_failed_submission($data);
        error_log('[Feasy] Error reportado por endpoint: ' . $message);
        $feasy_shutdown['sent'] = true;

        $payload = [
            'status'  => $normalized_status === '' ? 'error' : $normalized_status,
            'message' => $message ?: 'Error al enviar los datos.',
        ];

        if (!empty($remote_json)) {
            $payload['details'] = $remote_json;
        }

        wp_send_json($payload, 400);
    }

    if ($interpreted_status === null && $status_value !== null) {
        error_log('[Feasy] Aviso: estado remoto no reconocido: ' . print_r($status_value, true));
    }

    $feasy_shutdown['sent'] = true;
    wp_send_json_success([
        'status'  => 'success',
        'message' => $message,
    ]);
}

// Registrar la acción AJAX (logueados y no logueados)
add_action('wp_ajax_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');