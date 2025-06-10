<?php
// ==================================================
// Registrar el shortcode del editor visual
// ==================================================
add_action('init', function () {
    add_shortcode('feasy_form_editor', 'feasy_form_editor_shortcode');
});

function feasy_form_editor_shortcode() {
    static $already_rendered = false;
    if ($already_rendered) return '';
    $already_rendered = true;

    if (!is_admin()) {
        wp_enqueue_style(
            'feasy-editor-styles',
            plugin_dir_url(__DIR__) . 'assets/editor-styles.css',
            [],
            filemtime(plugin_dir_path(__DIR__) . 'assets/editor-styles.css')
        );
    }

    wp_enqueue_script(
        'feasy-editor-core',
        plugin_dir_url(__DIR__) . 'assets/js/editor-core.js',
        ['sortablejs'],
        filemtime(plugin_dir_path(__DIR__) . 'assets/js/editor-core.js'),
        true
    );

    wp_localize_script('feasy-editor-core', 'feasy_globals', [
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'plugin_url' => plugin_dir_url(__DIR__),
        'nonce'      => wp_create_nonce('feasy_editor_nonce')
    ]);

    ob_start();
    include plugin_dir_path(__FILE__) . 'editor-template.php';
    return ob_get_clean();
}

// ==================================================
// AJAX: Listar archivos de formularios
// ==================================================
add_action('wp_ajax_feasy_list_forms', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $files = glob(plugin_dir_path(__DIR__) . '/includes/form-config-*.php');
    $list = array_map('basename', $files);

    wp_send_json_success($list);
});

// ==================================================
// AJAX: Cargar formulario
// ==================================================
add_action('wp_ajax_feasy_load_form', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $file = sanitize_file_name($_GET['file'] ?? '');
    if (!$file || strpos($file, 'form-config-') !== 0) {
        wp_send_json_error(['message' => 'Archivo inválido']);
    }

    $path = plugin_dir_path(__DIR__) . 'includes/' . $file;
    if (!file_exists($path)) {
        wp_send_json_error(['message' => 'Archivo no encontrado']);
    }

    $form = include $path;
    wp_send_json_success(['data' => $form]);
});

// ==================================================
// AJAX: Guardar formulario (con validación y sanitización)
// ==================================================
add_action('wp_ajax_feasy_save_form', function () {
    check_ajax_referer('feasy_editor_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $file    = sanitize_file_name($_POST['file'] ?? '');
    $content = stripslashes($_POST['content'] ?? '');

    if (!$file || strpos($file, 'form-config-') !== 0) {
        error_log("[Feasy] ❌ Nombre de archivo inválido: $file");
        wp_send_json_error(['message' => 'Archivo inválido']);
    }

    $path     = plugin_dir_path(__DIR__) . 'includes/' . $file;
    $existing = file_exists($path) ? include $path : [];

    if (!preg_match('/return\s+(\[.+\]);/s', $content, $matches)) {
        error_log("[Feasy] ❌ No se pudo extraer arreglo desde el contenido");
        wp_send_json_error(['message' => 'Formato inválido']);
    }

    $fieldsCode = '<?php return ' . $matches[1] . ';';
    $tempFile   = tempnam(sys_get_temp_dir(), 'feasy_');
    file_put_contents($tempFile, $fieldsCode);
    $newData = include $tempFile;
    unlink($tempFile);

    if (!isset($newData['fields']) || !is_array($newData['fields'])) {
        wp_send_json_error(['message' => 'Campos inválidos']);
    }

    // ✅ Validar y sanitizar campos
    $sanitizedFields = [];
    foreach ($newData['fields'] as $field) {
        if (!isset($field['name'], $field['label'], $field['type'])) {
            wp_send_json_error(['message' => 'Campo incompleto']);
        }

        $name  = sanitize_key($field['name']);
        $label = sanitize_text_field($field['label']);
        $type  = sanitize_text_field($field['type']);

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            wp_send_json_error(['message' => 'Nombre de campo inválido']);
        }

        $sanitizedField = [
            'name'  => $name,
            'label' => $label,
            'type'  => $type
        ];

        if (!empty($field['required'])) {
            $sanitizedField['required'] = true;
        }

        if (!empty($field['placeholder'])) {
            $sanitizedField['placeholder'] = sanitize_text_field($field['placeholder']);
        }

        if (!empty($field['options']) && is_array($field['options'])) {
            $sanitizedField['options'] = array_map('sanitize_text_field', $field['options']);
        }

        if (!empty($field['conditional']) && is_array($field['conditional'])) {
            $sanitizedField['conditional'] = [
                'field' => sanitize_key($field['conditional']['field'] ?? ''),
                'value' => sanitize_text_field($field['conditional']['value'] ?? ''),
                'type'  => sanitize_text_field($field['conditional']['type'] ?? '')
            ];
        }

        if (!empty($field['attributes']) && is_array($field['attributes'])) {
            $sanitizedField['attributes'] = array_map('sanitize_text_field', $field['attributes']);
        }

        if (!empty($field['dynamic']) && is_array($field['dynamic'])) {
            $sanitizedField['dynamic'] = array_map('sanitize_text_field', $field['dynamic']);
        }

        $sanitizedFields[] = $sanitizedField;
    }

    // ✅ Fusión con campos existentes
    $mergedFields = [];
    foreach ($sanitizedFields as $newField) {
        $name = $newField['name'];
        $oldField = null;

        foreach ($existing['fields'] ?? [] as $field) {
            if (($field['name'] ?? '') === $name) {
                $oldField = $field;
                break;
            }
        }

        $mergedFields[] = array_merge($oldField ?? [], $newField);
    }

    $combined = $existing;
    $combined['fields'] = $mergedFields;

    // Función inline para exportar como short array [] en lugar de array()
    function feasy_array_to_php($data, $indent = 0) {
        $spaces = str_repeat('    ', $indent);
        if (is_array($data)) {
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);
            $items = [];
            foreach ($data as $key => $value) {
                $keyPart = $isAssoc ? "'" . addslashes($key) . "' => " : '';
                $items[] = $spaces . '    ' . $keyPart . feasy_array_to_php($value, $indent + 1);
            }
            return "[\n" . implode(",\n", $items) . "\n" . $spaces . "]";
        } elseif (is_string($data)) {
            return "'" . addslashes($data) . "'";
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            return 'null';
        } else {
            return $data;
        }
    }

    // Reemplazo de var_export por formato limpio
    $export = "<?php\n\nreturn " . feasy_array_to_php($combined) . ";\n";

    if (false === file_put_contents($path, $export)) {
        error_log("[Feasy] ❌ Error al guardar archivo: $path");
        wp_send_json_error(['message' => 'Error al guardar']);
    }

    error_log("[Feasy] ✅ Guardado exitoso: $file");
    wp_send_json_success(['message' => 'Guardado']);
});

// ==================================================
// AJAX: Limpiar historial de formulario (session-based)
// ==================================================
add_action('wp_ajax_feasy_clear_form_history', function () {
    check_ajax_referer('feasy_editor_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $formId = sanitize_text_field($_POST['form_id'] ?? '');
    if (!$formId || strpos($formId, 'form-config-') !== 0) {
        wp_send_json_error(['message' => 'ID de formulario inválido']);
    }

    wp_send_json_success([
        'message'   => 'Historial marcado para eliminación',
        'clear_key' => "feasy_history_{$formId}"
    ]);
});

// ==================================================
// AJAX: Crear nuevo formulario desde el editor visual
// ==================================================
add_action('wp_ajax_feasy_create_form', function () {
    check_ajax_referer('feasy_editor_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $base = sanitize_file_name($_POST['base'] ?? '');
    if (!$base || !preg_match('/^[a-zA-Z0-9_-]+$/', $base)) {
        wp_send_json_error(['message' => 'Nombre de formulario inválido']);
    }

    $configFile = "form-config-{$base}.php";
    $logicFile  = "form-logic-{$base}.php";
    $configPath = plugin_dir_path(__DIR__) . 'includes/' . $configFile;
    $logicPath  = plugin_dir_path(__DIR__) . 'includes/' . $logicFile;

    if (file_exists($configPath) || file_exists($logicPath)) {
        wp_send_json_error(['message' => 'El archivo ya existe']);
    }

    // Plantilla base del nuevo formulario
    $default = [
        'title'     => "New Form: {$base}",
        'form_name' => "New Form: {$base}",
        'endpoint'  => '',
        'fields'    => [
            [
                'type'  => 'text',
                'label' => 'New Field',
                'name'  => 'new_field',
            ],
        ],
    ];

    // Función para exportar como short array (si no existe ya)
    if (!function_exists('feasy_array_to_php')) {
        function feasy_array_to_php($data, $indent = 0) {
            $spaces = str_repeat('    ', $indent);
            if (is_array($data)) {
                $isAssoc = array_keys($data) !== range(0, count($data) - 1);
                $items = [];
                foreach ($data as $key => $value) {
                    $keyPart = $isAssoc ? "'" . addslashes($key) . "' => " : '';
                    $items[] = $spaces . '    ' . $keyPart . feasy_array_to_php($value, $indent + 1);
                }
                return "[\n" . implode(",\n", $items) . "\n" . $spaces . "]";
            } elseif (is_string($data)) {
                return "'" . addslashes($data) . "'";
            } elseif (is_bool($data)) {
                return $data ? 'true' : 'false';
            } elseif (is_null($data)) {
                return 'null';
            } else {
                return $data;
            }
        }
    }

    $phpCode    = "<?php\n\nreturn " . feasy_array_to_php($default) . ";\n";
    $logicStub  = "<?php\n\nreturn [];\n";

    if (file_put_contents($configPath, $phpCode) === false) {
        wp_send_json_error(['message' => 'No se pudo guardar el archivo']);
    }

    // Crear stub para la lógica condicional
    if (file_put_contents($logicPath, $logicStub) === false) {
        wp_send_json_error(['message' => 'No se pudo crear el archivo de lógica']);
    }

    wp_send_json_success(['message' => 'Formulario creado']);
    });

// ==================================================
// AJAX: Cargar lógica condicional
// ==================================================
add_action('wp_ajax_feasy_load_logic', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $file = sanitize_file_name($_GET['file'] ?? '');
    if (!$file || strpos($file, 'form-logic-') !== 0) {
        wp_send_json_error(['message' => 'Archivo inválido']);
    }

    $path = plugin_dir_path(__DIR__) . 'includes/' . $file;
    if (!file_exists($path)) {
        wp_send_json_error(['message' => 'Archivo no encontrado']);
    }

    $logic = include $path;
    wp_send_json_success(['data' => $logic]);
});

// ==================================================
// AJAX: Guardar lógica condicional
// ==================================================
add_action('wp_ajax_feasy_save_logic', function () {
    check_ajax_referer('feasy_editor_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $file    = sanitize_file_name($_POST['file'] ?? '');
    $content = stripslashes($_POST['content'] ?? '');

    if (!$file || strpos($file, 'form-logic-') !== 0) {
        wp_send_json_error(['message' => 'Archivo inválido']);
    }

    $path = plugin_dir_path(__DIR__) . 'includes/' . $file;

    $data = json_decode($content, true);
    if (!is_array($data)) {
        wp_send_json_error(['message' => 'Contenido inválido']);
    }

    if (!function_exists('feasy_array_to_php')) {
        function feasy_array_to_php($data, $indent = 0) {
            $spaces = str_repeat('    ', $indent);
            if (is_array($data)) {
                $isAssoc = array_keys($data) !== range(0, count($data) - 1);
                $items = [];
                foreach ($data as $key => $value) {
                    $keyPart = $isAssoc ? "'" . addslashes($key) . "' => " : '';
                    $items[] = $spaces . '    ' . $keyPart . feasy_array_to_php($value, $indent + 1);
                }
                return "[\n" . implode(",\n", $items) . "\n" . $spaces . "]";
            } elseif (is_string($data)) {
                return "'" . addslashes($data) . "'";
            } elseif (is_bool($data)) {
                return $data ? 'true' : 'false';
            } elseif (is_null($data)) {
                return 'null';
            } else {
                return $data;
            }
        }
    }

    $export = "<?php\n\nreturn " . feasy_array_to_php($data) . ";\n";

    if (false === file_put_contents($path, $export)) {
        wp_send_json_error(['message' => 'Error al guardar']);
    }

    wp_send_json_success(['message' => 'Guardado']);
});