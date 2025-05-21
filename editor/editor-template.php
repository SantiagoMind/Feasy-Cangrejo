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
    <div id="feasy-new-form-group" class="feasy-new-form-group hidden">
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
            <button id="feasy-show-history" class="button">📜 Historial</button>
        </div>

        <div id="feasy-history-modal" class="feasy-history-modal">
            <div class="feasy-history-inner">
                <button type="button" class="feasy-history-close">✖️</button>
                <ul id="feasy-history-list"></ul>
            </div>
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

<script type="module">
// Editor core logic
import { initHistory } from '<?php echo esc_url( plugins_url('../assets/js/history.js', __FILE__) ); ?>';

document.addEventListener('DOMContentLoaded', function() {
    const iconBase    = feasy_globals.plugin_url + 'assets/icons/';
    const selector    = document.getElementById('feasy-form-selector');
    const container   = document.getElementById('feasy-form-builder-container');
    const fieldsDiv   = document.getElementById('feasy-form-fields');
    const addFieldBtn = document.getElementById('feasy-add-field');
    const saveBtn     = document.getElementById('feasy-save-form-visual');
    const clearHistoryBtn = document.getElementById('feasy-clear-history');

    if (!selector || typeof feasy_globals === 'undefined' || typeof feasy_globals.ajaxurl === 'undefined') {
        console.error('❌ Initialization error: selector or ajaxurl not defined');
        return;
    }

    let currentFields = [];
    let currentFile   = '';

    function renderFields() {
        fieldsDiv.innerHTML = '';

        currentFields.forEach((field, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'field-wrapper';
            wrapper.dataset.index = index;
            wrapper.innerHTML = `
                <!-- Mango para arrastrar -->
                <span class="drag-handle" title="Drag to reorder">
                  <img src="${iconBase}drag-handle.svg" alt="Drag" width="16" height="16" />
                </span>

                <input type="text" placeholder="Field Name" value="${field.name || ''}" class="field-name">
                <div class="error-message name-error" style="color:red;font-size:12px;display:none;"></div>
                <input type="text" placeholder="Label" value="${field.label || ''}" class="field-label">

                <select class="field-type">
                    <option value="text"${field.type==='text' ? ' selected' : ''}>Text</option>
                    <option value="number"${field.type==='number' ? ' selected' : ''}>Number</option>
                    <option value="textarea"${field.type==='textarea' ? ' selected' : ''}>Textarea</option>
                    <option value="select"${field.type==='select' ? ' selected' : ''}>Dropdown</option>
                    <option value="radio"${field.type==='radio' ? ' selected' : ''}>Radio</option>
                    <option value="checkbox"${field.type==='checkbox' ? ' selected' : ''}>Checkbox</option>
                    <option value="checkbox_single"${field.type==='checkbox_single' ? ' selected' : ''}>Single-checkbox</option>
                    <option value="date"${field.type==='date' ? ' selected' : ''}>Date</option>
                    <option value="section_title"${field.type==='section_title' ? ' selected' : ''}>Section Title</option>
                </select>

                <input type="text" placeholder="Conditional: field" value="${field.conditional?.field||''}"
                       class="field-conditional-field">
                <input type="text" placeholder="Conditional: value" value="${field.conditional?.value||''}"
                       class="field-conditional-value">

                <select class="field-conditional-type">
                    <option value="">Type</option>
                    <option value="visibility"${field.conditional?.type==='visibility' ? ' selected' : ''}>Visibility</option>
                    <option value="requirement"${field.conditional?.type==='requirement' ? ' selected' : ''}>Requirement</option>
                </select>

                <button class="button button-link-delete">
                    <img src="${iconBase}delete.svg" width="16" alt="Delete" style="vertical-align: middle;">
                </button>
            `;
            fieldsDiv.appendChild(wrapper);
        });

        validateDuplicateNames();
        fieldsDiv.querySelectorAll('.field-name').forEach(i =>
            i.addEventListener('input', validateDuplicateNames)
        );

        fieldsDiv.querySelectorAll('.button-link-delete').forEach(btn =>
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.closest('.field-wrapper').dataset.index, 10);
                currentFields.splice(idx, 1);
                renderFields();
            })
        );

        if (typeof Sortable !== 'undefined') {
            Sortable.create(fieldsDiv, {
                animation: 150,
                handle: '.drag-handle',
                onEnd(evt) {
                    const item = currentFields.splice(evt.oldIndex, 1)[0];
                    currentFields.splice(evt.newIndex, 0, item);
                    renderFields();
                }
            });
        }
    }

    function validateDuplicateNames() {
        const seen = {};
        fieldsDiv.querySelectorAll('.field-name').forEach(input => {
            const val   = input.value.trim();
            const wrap  = input.closest('.field-wrapper');
            const msgEl = wrap.querySelector('.name-error');
            input.classList.remove('error');
            msgEl.style.display = 'none';
            if (!val) return;
            if (seen[val]) {
                input.classList.add('error');
                msgEl.textContent = `❌ Duplicate name: "${val}"`;
                msgEl.style.display = 'block';
            } else {
                seen[val] = true;
            }
        });
    }

    selector.addEventListener('change', () => {
        currentFile = selector.value;
        if (!currentFile) {
            container.style.display = 'none';
            return;
        }
        fetch(`${feasy_globals.ajaxurl}?action=feasy_load_form&file=${encodeURIComponent(currentFile)}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    currentFields = data.data.fields ?? (data.data.data?.fields ?? []);
                    container.style.display = 'block';
                    renderFields();
                } else {
                    console.error('❌ Error loading form:', data);
                }
            });
    });

    addFieldBtn.addEventListener('click', () => {
        currentFields.push({ name: '', label: '', type: 'text' });
        renderFields();
    });

    saveBtn.addEventListener('click', () => {
        const out = [];
        const names = new Set();
        let hasError = false;
        const mustHaveOptions = ['radio', 'select', 'checkbox_single'];

        document.querySelectorAll('.field-wrapper').forEach(row => {
            const name      = row.querySelector('.field-name').value.trim();
            const label     = row.querySelector('.field-label').value.trim();
            const type      = row.querySelector('.field-type').value;
            const cf        = row.querySelector('.field-conditional-field').value.trim();
            const cv        = row.querySelector('.field-conditional-value').value.trim();
            const ct        = row.querySelector('.field-conditional-type').value;

            if (!name) {
                alert('❌ Every field needs a name.');
                hasError = true;
                return;
            }
            if (names.has(name)) {
                alert(`❌ Duplicate field name: "${name}".`);
                hasError = true;
                return;
            }
            names.add(name);

            const f = { name, label, type };

            if (mustHaveOptions.includes(type)) {
                const ok = currentFields.find(ff => ff.name === name && (ff.options || ff.dynamic));
                if (!ok) {
                    alert(`❌ Field "${name}" requires options or dynamic source.`);
                    hasError = true;
                    return;
                }
            }
            if (cf && cv && ct) {
                f.conditional = { field: cf, value: cv, type: ct };
            }
            out.push(f);
        });

        if (hasError) return;

        const payload = `<?php\n\nreturn ${convertToPhpArray({ fields: out })};\n`;
        const fd = new FormData();
        fd.append('action', 'feasy_save_form');
        fd.append('file', currentFile);
        fd.append('content', payload);

        fetch(feasy_globals.ajaxurl, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) alert('✅ Saved successfully');
                else           alert('❌ Save failed');
            })
            .catch(() => alert('❌ Save error'));
    });

    if (!window.feasyFormListLoaded) {
        window.feasyFormListLoaded = true;
        fetch(`${feasy_globals.ajaxurl}?action=feasy_list_forms`)
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    selector.innerHTML = '<option value="">Select a form</option>';
                    const seen = new Set();
                    d.data.forEach(f => {
                        if (!seen.has(f)) {
                            seen.add(f);
                            const o = document.createElement('option');
                            o.value       = f;
                            o.textContent = f;
                            selector.appendChild(o);
                        }
                    });
                }
            })
            .catch(e => console.error('❌ Error fetching form list:', e));
    }

    clearHistoryBtn?.addEventListener('click', () => {
        if (!currentFile) {
            alert('❌ No form selected.');
            return;
        }
        if (!confirm('❌ Are you sure you want to clear the history for this form?')) return;

        const fd = new FormData();
        fd.append('action', 'feasy_clear_form_history');
        fd.append('form_id', currentFile);

        fetch(feasy_globals.ajaxurl, {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data?.clear_key) {
                    localStorage.removeItem(res.data.clear_key);
                    alert('✅ History cleared.');
                } else {
                    alert('❌ Failed to clear history.');
                }
            });
    });

    // Inicializar undo/redo
    initHistory({
        selector,
        renderFields,
        getCurrentFields: () => currentFields,
        setCurrentFields: fields => {
            currentFields = fields;
            renderFields();
        }
    });
});
</script>