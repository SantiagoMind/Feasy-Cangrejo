<?php
/**
 * Feasy Editor
 * Admin–area template
 */
?>
<div class="wrap feasy-editor">
    <h1>
        <img src="<?php echo esc_url( plugins_url('../assets/icons/editor.svg', __FILE__) ); ?>"
             width="20" style="vertical-align: middle;">
        Feasy Editor
    </h1>

    <div style="margin-bottom: 1em;">
        <label for="feasy-form-selector"><strong>Select a form</strong></label><br>
        <select id="feasy-form-selector">
            <option value="">Select a form</option>
            <option value="create_new">Create new form</option>
        </select>
    </div>

    <!-- Este bloque estará oculto por defecto y solo se mostrará cuando el usuario seleccione "crear nuevo formulario" -->
    <div id="feasy-new-form-group" style="display: none; margin-bottom: 1em; align-items: center; gap: 0.5em;" class="feasy-new-form-row">
        <input type="text" id="feasy-new-form-name" placeholder="ej: sip_f_105" style="padding: 6px 8px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc;">
        <button id="feasy-create-form" class="button">
            <img src="<?php echo esc_url( plugins_url('../assets/icons/add-field.svg', __FILE__) ); ?>" width="16" style="vertical-align: middle; margin-right: 4px;">
            Crear
        </button>
    </div>

    <div id="feasy-form-builder-container" style="display: none; margin-top: 1em;">
        <h2>
            <img src="<?php echo esc_url( plugins_url('../assets/icons/section-title.svg', __FILE__) ); ?>"
                 width="18" style="vertical-align: middle;">
            Form Builder
        </h2>

        <!-- Pista para que el usuario descubra la funcionalidad de reordenar -->
        <p class="feasy-drag-hint" style="margin-bottom:1em; color:#555;">
            ⇅ Drag and drop fields to reorder
        </p>

        <div id="feasy-form-fields"></div>

        <!-- 🔁 Botones de historial -->
        <div class="feasy-history-controls" style="margin-bottom: 1em;">
            <button id="feasy-undo" class="button" disabled>⏪ Undo</button>
            <button id="feasy-redo" class="button" disabled>⏩ Redo</button>
        </div>

        <button id="feasy-add-field" class="button">
            <img src="<?php echo esc_url( plugins_url('../assets/icons/add-field.svg', __FILE__) ); ?>"
                 width="16" style="vertical-align: middle;">
            Add Field
        </button>

        <br><br>

        <button id="feasy-save-form-visual" class="button button-primary">
            <img src="<?php echo esc_url( plugins_url('../assets/icons/save.svg', __FILE__) ); ?>"
                 width="16" style="vertical-align: middle;">
            Save Changes
        </button>
    </div>
</div>