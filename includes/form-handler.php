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
    $normalized_body = feasy_normalize_remote_body($response_body);

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

    $remote_json = json_decode($normalized_body, true);
    $json_error  = json_last_error();
    $success_message = 'Formulario enviado correctamente';

    if ($json_error === JSON_ERROR_NONE) {
        if (is_array($remote_json)) {
            if (isset($remote_json['message']) && is_string($remote_json['message'])) {
                $success_message = $remote_json['message'];
            }

            $status_keys = ['status', 'success', 'ok', 'result'];
            $status_value = null;

            foreach ($status_keys as $key) {
                if (array_key_exists($key, $remote_json)) {
                    $status_value = $remote_json[$key];
                    break;
                }
            }

            if ($status_value !== null) {
                $status_flag = feasy_interpret_remote_status($status_value);

                if ($status_flag === false) {
                    feasy_store_failed_submission($data);
                    $msg = isset($remote_json['message']) ? $remote_json['message'] : 'Error al enviar los datos.';
                    error_log('Error reportado por endpoint: ' . $msg);
                    $feasy_shutdown['sent'] = true;
                    header('Content-Type: application/json; charset=utf-8');
                    wp_send_json_error(['message' => $msg]);
                    wp_die();
                }

                if ($status_flag === null) {
                    error_log('[Feasy] Aviso: estado remoto no reconocido: ' . print_r($status_value, true));
                }
            }
        } elseif (is_string($remote_json)) {
            $trimmed = trim($remote_json);
            if ($trimmed !== '') {
                if (stripos($trimmed, 'success') !== false || stripos($trimmed, 'éxito') !== false || stripos($trimmed, 'exito') !== false) {
                    $success_message = $trimmed;
                }
            }
        }
    } else {
        $trimmed_body = trim($normalized_body);
        if ($trimmed_body === ''
            || stripos($trimmed_body, 'success') !== false
            || stripos($trimmed_body, 'éxito') !== false
            || stripos($trimmed_body, 'exito') !== false
            || stripos($trimmed_body, 'correctamente') !== false) {
            if ($trimmed_body !== '') {
                $success_message = $trimmed_body;
            }
        } else {
            feasy_store_failed_submission($data);
            error_log('Respuesta JSON inválida: ' . json_last_error_msg() . '. Cuerpo recibido: ' . $trimmed_body);
            $feasy_shutdown['sent'] = true;
            header('Content-Type: application/json; charset=utf-8');
            wp_send_json_error(['message' => 'Respuesta no válida del servidor']);
            wp_die();
        }
    }

    // ✅ Éxito: marcar envío y devolver respuesta JSON para el frontend (JS)
    $feasy_shutdown['sent'] = true;
    header('Content-Type: application/json; charset=utf-8');
    wp_send_json_success(['message' => $success_message]);
    wp_die(); // ✅ Detener ejecución completamente
}

// Registrar la acción AJAX (logueados y no logueados)
add_action('wp_ajax_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_proyecto_cangrejo_ajax_submit_form', 'proyecto_cangrejo_handle_form_submission_ajax');