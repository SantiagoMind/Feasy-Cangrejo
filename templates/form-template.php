<?php
$form_key = isset($_GET['form']) ? sanitize_text_field($_GET['form']) : 'sip_f_005';
$config = include plugin_dir_path(__FILE__) . '../includes/form-config-' . $form_key . '.php';

// Cargar y aplicar reglas de l�gica de forma modular
include_once plugin_dir_path(__FILE__) . '../includes/logic-handler.php';
$logic = cangrejo_load_logic($form_key);
cangrejo_apply_logic_to_config($config, $logic);
$logic_json = esc_attr(wp_json_encode($logic));

?>

<form class="feasy-form"
      method="post"
      action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
      data-form-name="<?php echo esc_attr($config['title'] ?? strtoupper($form_key)); ?>"
      data-logic="<?php echo $logic_json; ?>">
     
    <!-- Acci�n que ser� capturada por WordPress (AJAX) -->
    <input type="hidden" name="action" value="proyecto_cangrejo_ajax_submit_form">

    <!-- Clave del formulario, por si se quiere identificar en el backend -->
    <input type="hidden" name="form_key" value="<?php echo esc_attr($form_key); ?>">

    <!-- Endpoint din�mico desde el config -->
    <input type="hidden" name="_endpoint_url" value="<?php echo esc_url($config['endpoint'] ?? ''); ?>">

    <?php wp_nonce_field('proyecto_cangrejo_form', 'cangrejo_nonce'); ?>

    <!-- Render din�mico de campos -->
    <?php foreach ($config['fields'] as $field): ?>
        <?php echo cangrejo_render_field($field); ?>
    <?php endforeach; ?>

    <!-- Bot�n con clase para aplicar el CSS -->
    <div class="form-submit-row">
        <button type="submit">Enviar</button>
    </div>
</form>