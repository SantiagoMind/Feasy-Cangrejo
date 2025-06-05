<?php
$form_key = isset($_GET['form']) ? sanitize_text_field($_GET['form']) : 'sip_f_005';
$config = include plugin_dir_path(__FILE__) . '../includes/form-config-' . $form_key . '.php';

/ Cargar reglas de logica condicional y aplicarlas al config
$logic_file = plugin_dir_path(__FILE__) . '../includes/form-logic-' . $form_key . '.php';
if (file_exists($logic_file)) {
    $logic = include $logic_file;
    foreach ($logic as $rule) {
        $condData = [
            'type'       => 'visibility',
            'conditions' => array_map(function ($c) {
                return ['field' => $c['field'], 'value' => $c['value']];
            }, $rule['conditions'] ?? []),
            'operator'   => ($rule['match'] ?? 'all') === 'all' ? 'AND' : 'OR',
        ];
        foreach ($rule['actions'] as $action) {
            if (($action['action'] ?? '') === 'show') {
                foreach ($action['targets'] as $target) {
                    foreach ($config['fields'] as &$f) {
                        if (($f['name'] ?? '') === $target) {
                            $f['conditional'] = $condData;
                        }
                    }
                }
            }
        }
    }
    unset($f);
}

?>

<form class="feasy-form"
      method="post"
      action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
      data-form-name="<?php echo esc_attr($config['title'] ?? strtoupper($form_key)); ?>">
    
    <!-- Acción que será capturada por WordPress (AJAX) -->
    <input type="hidden" name="action" value="proyecto_cangrejo_ajax_submit_form">

    <!-- Clave del formulario, por si se quiere identificar en el backend -->
    <input type="hidden" name="form_key" value="<?php echo esc_attr($form_key); ?>">

    <!-- Endpoint dinámico desde el config -->
    <input type="hidden" name="_endpoint_url" value="<?php echo esc_url($config['endpoint'] ?? ''); ?>">

    <?php wp_nonce_field('proyecto_cangrejo_form', 'cangrejo_nonce'); ?>

    <!-- Render dinámico de campos -->
    <?php foreach ($config['fields'] as $field): ?>
        <?php echo cangrejo_render_field($field); ?>
    <?php endforeach; ?>

    <!-- Botón con clase para aplicar el CSS -->
    <div class="form-submit-row">
        <button type="submit">Enviar</button>
    </div>
</form>