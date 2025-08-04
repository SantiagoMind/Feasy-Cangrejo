<?php
/**
 * Plugin Name: Proyecto Cangrejo
 * Description: Plugin modular para construir formularios dinámicos con condicionales.
 * Version: 1.1
 * Author: Tu Nombre
 */

// ==================================================
// Utilidad central para variables JS globales
// ==================================================
function feasy_get_globals() {
    return [
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'plugin_url' => plugin_dir_url(__FILE__),
        'nonce'      => wp_create_nonce('feasy_editor_nonce')
    ];
}

// ==================================================
// Inicialización del plugin
// ==================================================
add_action('plugins_loaded', 'proyecto_cangrejo_init');
function proyecto_cangrejo_init() {
    error_log('[Feasy] ✅ Inicializando plugin Proyecto Cangrejo');

    include_once plugin_dir_path(__FILE__) . 'includes/field-builder.php';
    include_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';

    // ✅ Siempre se carga el editor visual
    include_once plugin_dir_path(__FILE__) . 'editor/editor-core.php';
}

// ==================================================
// Cargar scripts y estilos en frontend si hay shortcodes
// ==================================================
function cangrejo_maybe_enqueue_assets() {
    global $post;

    if (!is_a($post, 'WP_Post')) return;

    $contains_shortcodes = strpos($post->post_content, 'feasy_form_') !== false || strpos($post->post_content, 'feasy_form_editor') !== false;
    error_log('[Feasy] 🔍 Analizando contenido del post para scripts: ' . ($contains_shortcodes ? 'SÍ' : 'NO'));

    if ($contains_shortcodes) {
        error_log('[Feasy] ✅ Encolando scripts y estilos (frontend)');

        // CSS
        wp_enqueue_style(
            'cangrejo-styles',
            plugin_dir_url(__FILE__) . 'assets/styles.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/styles.css')
        );

        wp_enqueue_style(
            'feasy-editor-styles',
            plugin_dir_url(__FILE__) . 'assets/editor-styles.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/editor-styles.css')
        );

        // JS
        wp_enqueue_script(
            'sortablejs',
            plugin_dir_url(__FILE__) . 'assets/js/sortable.min.js',
            [],
            '1.15.0',
            true
        );

        wp_enqueue_script(
            'feasy-editor-core',
            plugin_dir_url(__FILE__) . 'assets/js/editor-core.js',
            ['sortablejs'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/editor-core.js'),
            true
        );

        wp_enqueue_script(
            'cangrejo-scripts',
            plugin_dir_url(__FILE__) . 'assets/scripts.js',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/scripts.js'),
            true
        );

        // ✅ Variables globales JS (con nonce incluido)
        $globals = feasy_get_globals();
        wp_localize_script('feasy-editor-core', 'feasy_globals', $globals);
        wp_localize_script('cangrejo-scripts', 'feasy_globals', $globals);
    }
}
add_action('wp_enqueue_scripts', 'cangrejo_maybe_enqueue_assets');

// ==================================================
// Cargar scripts en el ADMIN para editor visual
// ==================================================
add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'toplevel_page_feasy-form-editor') {
        error_log('[Feasy] ✅ Encolando scripts y estilos (admin editor)');

        wp_enqueue_script(
            'sortablejs',
            plugin_dir_url(__FILE__) . 'assets/js/sortable.min.js',
            [],
            '1.15.0',
            true
        );

        wp_enqueue_script(
            'feasy-editor-core',
            plugin_dir_url(__FILE__) . 'assets/js/editor-core.js',
            ['sortablejs'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/editor-core.js'),
            true
        );

        wp_enqueue_script(
            'feasy-editor-history',
            plugin_dir_url(__FILE__) . 'assets/js/history.js',
            ['feasy-editor-core'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/history.js'),
            true
        );

        wp_enqueue_style(
            'feasy-editor-styles',
            plugin_dir_url(__FILE__) . 'assets/editor-styles.css',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/editor-styles.css')
        );

        wp_localize_script('feasy-editor-core', 'feasy_globals', feasy_get_globals());
    }
});

// ---------------------------------------------------
// Limpieza de localStorage al borrar el plugin desde la lista de plugins
// ---------------------------------------------------
add_action('admin_enqueue_scripts', function () {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'plugins') {
        wp_enqueue_script(
            'feasy-admin-cleanup',
            plugin_dir_url(__FILE__) . 'assets/js/admin-cleanup.js',
            [],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/admin-cleanup.js'),
            true
        );
    }
});

// ==================================================
// Cargar formulario por AJAX
// ==================================================
add_action('init', function () {
    if (isset($_GET['feasy_form_ajax']) && $_GET['feasy_form_ajax'] === '1') {
        $form_id = esc_attr($_GET['form']);
        error_log("[Feasy] 📥 Solicitud AJAX para cargar formulario: {$form_id}");

        $form_config_path = plugin_dir_path(__FILE__) . "includes/form-config-{$form_id}.php";

        if (file_exists($form_config_path)) {
            error_log("[Feasy] ✅ Configuración encontrada para formulario {$form_id}");
            $form_config = include $form_config_path;
            include plugin_dir_path(__FILE__) . 'templates/form-template.php';
        } else {
            error_log("[Feasy] ❌ Configuración no encontrada: {$form_config_path}");
            echo '<div class="form-message error">❌ Formulario no encontrado.</div>';
        }
        exit;
    }
});

// ==================================================
// Registrar shortcodes dinámicos para formularios
// ==================================================
add_action('init', function () {
    $config_dir = plugin_dir_path(__FILE__) . 'includes/';
    $files = glob($config_dir . 'form-config-*.php');
    error_log('[Feasy] 🗂️ Buscando formularios en: ' . $config_dir);

    foreach ($files as $file) {
        if (preg_match('/form-config-(.+)\.php$/', basename($file), $matches)) {
            $form_id = $matches[1];
            $shortcode_name = 'feasy_form_' . $form_id;

            error_log("[Feasy] 🏷️ Registrando shortcode: [{$shortcode_name}] para {$file}");

            add_shortcode($shortcode_name, function () use ($form_id, $file) {
                $form_config = include $file;
                $label = trim($form_config['form_name'] ?? $form_config['title'] ?? 'Formulario ' . strtoupper($form_id));
                $label = str_replace(['–','—','“','”','‘','’'], ['-','-','"','"',"'", "'"], $label);

                ob_start(); ?>
                <button id="feasy-slide-trigger-<?php echo esc_attr($form_id); ?>"
                        class="feasy-slide-trigger"
                        data-form-name="<?php echo esc_attr($label); ?>">
                    <div class="feasy-icon-circle">
                        <img src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/form-icon.svg'; ?>"
                             alt="Icono inspección"
                             class="feasy-icon">
                    </div>
                    <div class="feasy-label"><?php echo esc_html($label); ?></div>
                </button>

                <div id="feasy-slide-form-<?php echo esc_attr($form_id); ?>" class="feasy-slide-form">
                    <div class="feasy-slide-inner">
                        <button class="feasy-close-btn" aria-label="Cerrar formulario">✖️</button>
                        <div id="feasy-form-placeholder-<?php echo esc_attr($form_id); ?>"></div>
                    </div>
                </div>

                <script>
                    console.log('📦 Feasy: Inicializando botón para formulario "<?php echo esc_attr($form_id); ?>"');
                    (function(fn){
                        if (document.readyState !== 'loading') fn();
                        else document.addEventListener('DOMContentLoaded', fn);
                    })(function () {
                        const trigger = document.getElementById('feasy-slide-trigger-<?php echo esc_attr($form_id); ?>');
                        const slide = document.getElementById('feasy-slide-form-<?php echo esc_attr($form_id); ?>');
                        const placeholder = document.getElementById('feasy-form-placeholder-<?php echo esc_attr($form_id); ?>');

                        if (!placeholder.dataset.loaded) {
                            fetch('?feasy_form_ajax=1&form=<?php echo esc_attr($form_id); ?>')
                                .then(res => res.text())
                                .then(html => {
                                    console.log('✅ Feasy: Formulario cargado para "<?php echo esc_attr($form_id); ?>"');
                                    placeholder.innerHTML = html;
                                    placeholder.dataset.loaded = "1";
                                })
                                .catch(err => {
                                    console.error('❌ Feasy: Error al cargar el formulario "<?php echo esc_attr($form_id); ?>"', err);
                                    placeholder.innerHTML = '<div class="form-message error">❌ Error al cargar el formulario.</div>';
                                });
                        }

                        if (trigger && slide && placeholder) {
                            trigger.addEventListener('click', () => {
                                slide.classList.add('open');
                                slide.style.display = 'flex';
                                console.log('🖱️ Feasy: Formulario abierto "<?php echo esc_attr($form_id); ?>"');
                            });
                        }
                    });
                </script>
                <?php return ob_get_clean();
            });
        }
    }
});

// ---------------------------------------------------
// Asegurar que editor-core.js se cargue como <script type="module">
// ---------------------------------------------------
add_filter('script_loader_tag', function( $tag, $handle, $src ) {
    if ( 'feasy-editor-core' === $handle ) {
        return '<script type="module" src="' . esc_url( $src ) . '"></script>';
    }
    return $tag;
}, 10, 3);