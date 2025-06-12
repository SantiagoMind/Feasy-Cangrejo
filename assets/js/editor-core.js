import { initHistory } from './history.js';

document.addEventListener('DOMContentLoaded', function () {
    console.log('? Feasy: Initializing form editor');

    const selector = document.getElementById('feasy-form-selector');
    const container = document.getElementById('feasy-form-builder-container');
    const fieldsDiv = document.getElementById('feasy-form-fields');
    const addFieldBtn = document.getElementById('feasy-add-field');
    const saveBtn = document.getElementById('feasy-save-form-visual');
    const clearHistoryBtn = document.getElementById('feasy-clear-history');
    const showHistoryBtn = document.getElementById("feasy-show-history");
    const historyModal = document.getElementById("feasy-history-modal");
    const historyList = document.getElementById("feasy-history-list");
    const historyClose = document.querySelector(".feasy-history-close");
    const editLogicBtn = document.getElementById("feasy-edit-logic");
    const logicModal = document.getElementById("feasy-logic-modal");
    const logicClose = document.querySelector(".feasy-logic-close");
    const logicRules = document.getElementById("feasy-logic-rules");
    const addRuleBtn = document.getElementById("feasy-add-rule");
    const saveLogicBtn = document.getElementById("feasy-save-logic");

    const createBtn = document.getElementById('feasy-create-form');
    const newFormInput = document.getElementById('feasy-new-form-name');
    const newFormGroup = document.getElementById('feasy-new-form-group'); // <== ESTE BLOQUE ES EL CONTENEDOR OCULTO
    let historyManager;

    selector.addEventListener('change', () => {
        const selected = selector.value;

        // Mostrar bloque de creación si selecciona "Crear nuevo formulario..."
        if (selected === 'create_new') {
            if (newFormGroup) newFormGroup.classList.remove('hidden');
            if (container) container.style.display = 'none';
            return;
        } else {
            if (newFormGroup) newFormGroup.classList.add('hidden');
        }

        // Cargar formulario existente
        window.currentFile = selected;

        if (!window.currentFile) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        fieldsDiv.innerHTML = '';
        fieldsDiv.appendChild(createFeasySpinner('Loading form...'));

        fetch(`${feasy_globals.ajaxurl}?action=feasy_load_form&file=${encodeURIComponent(window.currentFile)}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.currentFields = data.data.fields ?? (data.data.data?.fields ?? []);
                    renderFields();
                    loadLogic();
                    if (historyManager) {
                        const loaded = historyManager.loadHistory();
                        if (!loaded) historyManager.saveSnapshot();
                    }
                } else {
                    fieldsDiv.innerHTML = '<p style="color:red;">? Error loading form.</p>';
                }
            })
            .catch(() => {
                fieldsDiv.innerHTML = '<p style="color:red;">? Failed to fetch form data.</p>';
            });
    });

    if (createBtn && newFormInput) {
        createBtn.addEventListener('click', () => {
            const base = newFormInput.value.trim();
            if (!base || !/^[a-zA-Z0-9_]+$/.test(base)) {
                alert('? Nombre inválido. Usa solo letras, números y guiones bajos.');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'feasy_create_form');
            fd.append('_ajax_nonce', feasy_globals.nonce);
            fd.append('base', base);

            createBtn.disabled = true;

            fetch(feasy_globals.ajaxurl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert('? Formulario creado');
                        location.reload(); // Recarga para que el nuevo aparezca en el selector
                    } else {
                        alert('? Error: ' + (res.data?.message || 'No se pudo crear'));
                    }
                })
                .catch(() => alert('? Error de red al crear formulario'))
                .finally(() => {
                    createBtn.disabled = false;
                });
        });
    }

    const saveIndicator = document.getElementById('feasy-save-indicator');
    const saveStatus = saveIndicator?.querySelector('.feasy-save-status');
    const saveDot = saveIndicator?.querySelector('.feasy-save-dot');

    if (
        !selector ||
        typeof feasy_globals === 'undefined' ||
        typeof feasy_globals.ajaxurl === 'undefined' ||
        typeof feasy_globals.nonce === 'undefined'
    ) {
        console.error('? Feasy: Missing feasy_globals or nonce');
        return;
    }

    window.currentFields = [];
    window.currentFile = '';
    window.currentLogic = [];

    function createFeasySpinner(message = 'Loading...') {
        const container = document.createElement('div');
        container.className = 'feasy-spinner-container';
        container.innerHTML = `
            <div class="spinner"></div>
            <span>${message}</span>
        `;
        return container;
    }

    function getLogicFile() {
        return window.currentFile.replace("form-config", "form-logic");
    }
    function loadLogic() {
        if (!window.currentFile) return;
        fetch(`${feasy_globals.ajaxurl}?action=feasy_load_logic&file=${encodeURIComponent(getLogicFile())}`)
            .then(r => r.json())
            .then(d => { window.currentLogic = d.success ? (d.data || []) : []; renderLogic(); })
            .catch(() => { window.currentLogic = []; renderLogic(); });
    }
    function saveLogic() {
        const fd = new FormData();
        fd.append("action", "feasy_save_logic");
        fd.append("file", getLogicFile());
        fd.append("content", JSON.stringify(window.currentLogic));
        fd.append("_ajax_nonce", feasy_globals.nonce);
        fetch(feasy_globals.ajaxurl, { method: "POST", body: fd })
            .then(r => r.json())
            .then(res => console.log("Logic save", res));
    }

    let isSaving = false;
    let pendingSave = false;
    let lastSaveTime = 0;

    function collectValidFields() {
        const updatedFields = [];
        const namesSet = new Set();
        let hasErrors = false;
        const requiredOptionTypes = ['radio', 'select', 'checkbox_single'];

        document.querySelectorAll('.field-wrapper').forEach(row => {
            const name = row.querySelector('.field-name').value.trim();
            const label = row.querySelector('.field-label').value.trim();
            const type = row.querySelector('.field-type').value;
            const condField = row.querySelector('.field-conditional-field').value.trim();
            const condValue = row.querySelector('.field-conditional-value').value.trim();
            const condType = row.querySelector('.field-conditional-type').value;

            if (!name) {
                hasErrors = true;
                return;
            }
            if (namesSet.has(name)) {
                hasErrors = true;
                return;
            }
            namesSet.add(name);

            const field = { name, label, type };

            // Options
            if (requiredOptionTypes.includes(type)) {
                const optionsText = row.querySelector('.field-options')?.value.trim() || '';
                const optionsArray = optionsText.split('\n').filter(opt => opt.trim() !== '');

                let isDynamic = false;
                let dynamicConfig = null;

                try {
                    const wrapper = row.closest('.field-wrapper');
                    const dynamicAttr = wrapper?.getAttribute('data-dynamic');
                    if (dynamicAttr) {
                        dynamicConfig = JSON.parse(dynamicAttr);
                        isDynamic = !!dynamicConfig.endpoint;
                    }
                } catch (err) {
                    console.warn('Error al interpretar dynamic data', err);
                }

                if (optionsArray.length === 0 && !isDynamic) {
                    hasErrors = true;
                    return;
                }

                if (!isDynamic && optionsArray.length > 0) {
                    field.options = {};
                    optionsArray.forEach(opt => {
                        field.options[opt] = opt;
                    });
                }

                if (isDynamic) {
                    field.dynamic = dynamicConfig;
                }
            }

            // Condicional
            if (condField && condValue && condType) {
                field.conditional = { field: condField, value: condValue, type: condType };
            }

            updatedFields.push(field);
        });

        return hasErrors ? null : updatedFields;
    }

    // Collect fields without validation, preserving current input values
    function collectAllFields() {
        const updatedFields = [];
        const requiredOptionTypes = ['radio', 'select', 'checkbox_single'];

        document.querySelectorAll('.field-wrapper').forEach(row => {
            const name = row.querySelector('.field-name').value.trim();
            const label = row.querySelector('.field-label').value.trim();
            const type = row.querySelector('.field-type').value;
            const condField = row.querySelector('.field-conditional-field').value.trim();
            const condValue = row.querySelector('.field-conditional-value').value.trim();
            const condType = row.querySelector('.field-conditional-type').value;

            const field = { name, label, type };

            if (requiredOptionTypes.includes(type)) {
                const optionsText = row.querySelector('.field-options')?.value.trim() || '';
                const optionsArray = optionsText.split('\\n').filter(opt => opt.trim() !== '');

                let isDynamic = false;
                let dynamicConfig = null;

                try {
                    const wrapper = row.closest('.field-wrapper');
                    const dynamicAttr = wrapper?.getAttribute('data-dynamic');
                    if (dynamicAttr) {
                        dynamicConfig = JSON.parse(dynamicAttr);
                        isDynamic = !!dynamicConfig.endpoint;
                    }
                } catch (err) {
                    console.warn('Error al interpretar dynamic data', err);
                }

                if (!isDynamic && optionsArray.length > 0) {
                    field.options = {};
                    optionsArray.forEach(opt => {
                        field.options[opt] = opt;
                    });
                }

                if (isDynamic) {
                    field.dynamic = dynamicConfig;
                }
            }

            if (condField && condValue && condType) {
                field.conditional = { field: condField, value: condValue, type: condType };
            }

            updatedFields.push(field);
        });

        return updatedFields;
    }

    function autosave(priority = 'medium') {
        const now = Date.now();
        const MIN_INTERVAL = 3000;

        if (!window.currentFile) return;
        if (isSaving) {
            pendingSave = true;
            return;
        }
        if (priority === 'low' && (now - lastSaveTime < MIN_INTERVAL)) return;

        const validFields = collectValidFields();
        const updatedFields = validFields || collectAllFields();
        window.currentFields = updatedFields;
        historyManager?.saveSnapshot();
        if (!validFields) return;
        isSaving = true;
        pendingSave = false;
        lastSaveTime = now;

        if (saveIndicator) {
            saveIndicator.style.display = 'inline-block';
            saveStatus.textContent = 'Guardando…';
            saveDot.textContent = '??';
        }

        // Primero obtenemos el archivo actual desde el backend
        fetch(`${feasy_globals.ajaxurl}?action=feasy_load_form&file=${encodeURIComponent(window.currentFile)}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.data) throw new Error('Error loading current file');

                const existing = data.data;
                const merged = { ...existing, fields: updatedFields };

                const content = `<?php\n\nreturn ${phpify(merged)};\n`;
                const fd = new FormData();
                fd.append('action', 'feasy_save_form');
                fd.append('file', window.currentFile);
                fd.append('content', content);
                fd.append('_ajax_nonce', feasy_globals.nonce);

                return fetch(feasy_globals.ajaxurl, { method: 'POST', body: fd });
            })
            .then(r => r.json())
            .then(data => {
                console.log('[Feasy] Autosave status:', data);
                if (saveIndicator) {
                    saveStatus.textContent = data.success ? 'Guardado' : 'Error al guardar';
                    saveDot.textContent = data.success ? '?' : '??';
                }
            })
            .then(() => saveLogic())
            .catch(err => {
                console.warn('[Feasy] Autosave error:', err);
                if (saveIndicator) {
                    saveStatus.textContent = 'Error de red';
                    saveDot.textContent = '?';
                }
            })
            .finally(() => {
                isSaving = false;
                if (pendingSave) {
                    autosave('medium');
                } else {
                    setTimeout(() => {
                        if (saveIndicator) saveIndicator.style.display = 'none';
                    }, 2000);
                }
            });
    }
    function debounce(fn, delay = 2000) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    const debouncedAutosave = debounce(() => autosave('low'), 2000);

    function getFieldNames() {
        return Array.from(fieldsDiv.querySelectorAll('.field-name'))
            .map(i => i.value.trim())
            .filter(Boolean);
    }

    function updateConditionalSelects() {
        const names = getFieldNames();
        fieldsDiv.querySelectorAll('.field-conditional-field').forEach(select => {
            const desired = select.dataset.desired || select.value;
            select.innerHTML = '<option value="">Conditional: field</option>';
            names.forEach(n => {
                const o = document.createElement('option');
                o.value = n;
                o.textContent = n;
                select.appendChild(o);
            });
            if (names.includes(desired)) {
                select.value = desired;
            } else {
                select.value = '';
            }
        });
    }

    function updateSummary(wrapper) {
        const field = wrapper.querySelector(".field-conditional-field")?.value.trim();
        const val = wrapper.querySelector(".field-conditional-value")?.value.trim();
        const type = wrapper.querySelector(".field-conditional-type")?.value;
        const sum = wrapper.querySelector(".conditional-summary");
        if (!sum) return;
        if (field && val && type) {
            const action = type === "visibility" ? "shows" : "requires";
            sum.textContent = `Trigger: ${field} ${action} this field when value is "${val}"`;
        } else {
            sum.textContent = "";
        }
    }

    function renderFields() {
        fieldsDiv.innerHTML = '';

        window.currentFields.forEach((field, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'field-wrapper';
            wrapper.dataset.index = index;

            // ? Aquí agregamos esto:
            if (field.dynamic) {
                wrapper.setAttribute('data-dynamic', JSON.stringify(field.dynamic));
            }

            const isOptionField = ['select', 'radio', 'checkbox_single'].includes(field.type);
            const optionsText = isOptionField && field.options
                ? Object.values(field.options).join('\n')
                : '';

            wrapper.innerHTML = `
                <span class="drag-handle" title="Drag to reorder">
                  <img src="${feasy_globals.plugin_url}assets/icons/drag-handle.svg" alt="Drag" />
                </span>

                <input type="text" placeholder="Field Name" value="${field.name || ''}" class="field-name">
                <div class="error-message name-error" style="color:red;font-size:12px;display:none;"></div>
                <input type="text" placeholder="Label" value="${field.label || ''}" class="field-label">

                <select class="field-type">
                    <option value="text"${field.type === 'text' ? ' selected' : ''}>Text</option>
                    <option value="number"${field.type === 'number' ? ' selected' : ''}>Number</option>
                    <option value="textarea"${field.type === 'textarea' ? ' selected' : ''}>Textarea</option>
                    <option value="select"${field.type === 'select' ? ' selected' : ''}>Dropdown</option>
                    <option value="radio"${field.type === 'radio' ? ' selected' : ''}>Radio</option>
                    <option value="checkbox"${field.type === 'checkbox' ? ' selected' : ''}>Checkbox</option>
                    <option value="checkbox_single"${field.type === 'checkbox_single' ? ' selected' : ''}>Single-checkbox</option>
                    <option value="date"${field.type === 'date' ? ' selected' : ''}>Date</option>
                    <option value="section_title"${field.type === 'section_title' ? ' selected' : ''}>Section Title</option>
                </select>

                ${isOptionField && !field.dynamic ? `<textarea placeholder="Options (one per line)" class="field-options">${optionsText}</textarea>` : ''}

                 <select class="field-conditional-field"></select>
                <input type="text" placeholder="Conditional: value" value="${field.conditional?.value || ''}" class="field-conditional-value">
                <select class="field-conditional-type">
                    <option value="">Type</option>
                    <option value="visibility"${field.conditional?.type === 'visibility' ? ' selected' : ''}>Visibility</option>
                    <option value="requirement"${field.conditional?.type === 'requirement' ? ' selected' : ''}>Requirement</option>
                </select>

                <button class="button button-link-delete" title="Delete">
                    <img src="${feasy_globals.plugin_url}assets/icons/delete.svg" width="16" alt="Delete" />
                </button>
                 <div class="conditional-summary"></div>
            `;
            fieldsDiv.appendChild(wrapper);
            updateSummary(wrapper);
            wrapper.querySelector('.field-conditional-field').dataset.desired = field.conditional?.field || '';

            // ?? Agregar listeners para autoguardado
            wrapper.querySelector('.field-name')?.addEventListener('input', debouncedAutosave);
            wrapper.querySelector('.field-label')?.addEventListener('input', debouncedAutosave);
            wrapper.querySelector('.field-type')?.addEventListener('change', () => autosave('medium'));
            wrapper.querySelector('.field-options')?.addEventListener('input', debouncedAutosave);
            wrapper.querySelector('.field-conditional-field')?.addEventListener('input', () => { debouncedAutosave(); updateSummary(wrapper); });
            wrapper.querySelector('.field-conditional-value')?.addEventListener('input', () => { debouncedAutosave(); updateSummary(wrapper); });
            wrapper.querySelector('.field-conditional-type')?.addEventListener('change', () => { autosave('medium'); updateSummary(wrapper); });
        });

        activateValidation();
        updateConditionalSelects();
    }

    function activateValidation() {
        fieldsDiv.querySelectorAll('.field-name').forEach(input => {
            input.addEventListener('input', validateDuplicateNames);
        });

        fieldsDiv.querySelectorAll('.button-link-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.closest('.field-wrapper').dataset.index, 10);
                window.currentFields.splice(idx, 1);
                renderFields();
                autosave('high');
            });
        });
    }

    function validateDuplicateNames() {
        const names = {}
        updateConditionalSelects();;
        fieldsDiv.querySelectorAll('.field-name').forEach(input => {
            const val = input.value.trim();
            const wrap = input.closest('.field-wrapper');
            const msgEl = wrap.querySelector('.name-error');
            input.classList.remove('error');
            msgEl.style.display = 'none';

            if (!val) return;
            if (names[val]) {
                input.classList.add('error');
                msgEl.textContent = `? Duplicate name: "${val}"`;
                msgEl.style.display = 'block';
            } else {
                names[val] = true;
            }
        });
        updateConditionalSelects();
    }

    addFieldBtn.addEventListener('click', () => {
        window.currentFields.push({ name: '', label: '', type: 'text' });
        renderFields();
        autosave('high');
    });

    saveBtn.addEventListener('click', () => {
        let overlay = document.querySelector('.feasy-save-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'feasy-save-overlay';
            overlay.appendChild(createFeasySpinner('Saving form...'));
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';

        const updatedFields = collectValidFields();
        if (!updatedFields) {
            overlay.style.display = 'none';
            return;
        }

        // Hacemos fetch del archivo actual
        fetch(`${feasy_globals.ajaxurl}?action=feasy_load_form&file=${encodeURIComponent(window.currentFile)}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.data) throw new Error('Error loading current file');

                const existing = data.data;
                const merged = {
                    ...existing,
                    fields: updatedFields
                };

                const content = `<?php\n\nreturn ${phpify(merged)};\n`;
                const fd = new FormData();
                fd.append('action', 'feasy_save_form');
                fd.append('file', window.currentFile);
                fd.append('content', content);
                fd.append('_ajax_nonce', feasy_globals.nonce);

                return fetch(feasy_globals.ajaxurl, { method: 'POST', body: fd });
            })
            .then(r => r.json())
            .then(data => {
                alert(data.success ? '? Saved successfully' : '? Save failed');
            })
            .catch(() => {
                alert('?? Save error');
            })
            .finally(() => {
                overlay.style.display = 'none';
                saveLogic();
            });
        });

        new Sortable(fieldsDiv, {
        animation: 150,
        handle: '.drag-handle',
        onEnd(evt) {
            window.currentFields = collectValidFields() || collectAllFields();
            renderFields();
            autosave('high');
        }
    });

    fetch(`${feasy_globals.ajaxurl}?action=feasy_list_forms`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                selector.innerHTML = '<option value="">Select a form</option>';
                const seen = new Set();
                data.data.forEach(f => {
                    if (!seen.has(f)) {
                        seen.add(f);
                        const o = document.createElement('option');
                        o.value = f;
                        o.textContent = f;
                        selector.appendChild(o);
                    }
                });

                // ? Opción para crear un nuevo formulario
                const newOption = document.createElement('option');
                newOption.value = 'create_new';
                newOption.textContent = 'Create new form';
                selector.appendChild(newOption);
            }
        });

    function phpify(obj, indent = 0) {
        const pad = '    '.repeat(indent);
        if (Array.isArray(obj)) {
            const items = obj.map(item => phpify(item, indent + 1));
            return `[\n${items.map(i => pad + '    ' + i).join(',\n')}\n${pad}]`;
        } else if (typeof obj === 'object' && obj !== null) {
            const items = Object.entries(obj).map(([key, val]) =>
                `'${key}' => ${phpify(val, indent + 1)}`
            );
            return `[\n${items.map(i => pad + '    ' + i).join(',\n')}\n${pad}]`;
        } else if (typeof obj === 'string') {
            return `'${obj.replace(/'/g, "\\'")}'`;
        } else if (typeof obj === 'number') {
            return obj;
        } else if (typeof obj === 'boolean') {
            return obj ? 'true' : 'false';
        } else {
            return 'null';
        }
    }

    function buildConditionRow(cond = {}) {
        const row = document.createElement("div");
        row.className = "condition-row";
        const f = document.createElement("select");
        getFieldNames().forEach(n => { const o = document.createElement("option"); o.value = n; o.textContent = n; f.appendChild(o); });
        f.className = "cond-field";
        f.value = cond.field || "";
        const op = document.createElement("select");
        op.innerHTML = `<option value="equal_to">=</option><option value="not_equal_to">!=</option>`;
        op.className = "cond-operator";
        op.value = cond.operator || "equal_to";
        const val = document.createElement("input");
        val.type = "text";
        val.className = "cond-value";
        val.value = cond.value || "";
        const del = document.createElement("button"); del.type = "button"; del.textContent = "?"; del.addEventListener("click", () => row.remove());
        row.appendChild(f); row.appendChild(op); row.appendChild(val); row.appendChild(del);
        return row;
    }
    function buildActionRow(act = {}) {
        const row = document.createElement("div");
        row.className = "action-row";
        const t = document.createElement("select");
        t.innerHTML = `<option value="show">Show</option><option value="hide">Hide</option><option value="require">Require</option>`;
        t.className = "action-type";
        t.value = act.action || "show";
        const targets = document.createElement("select");
        targets.multiple = true;
        targets.className = "action-targets";
        getFieldNames().forEach(n => { const o = document.createElement("option"); o.value = n; o.textContent = n; targets.appendChild(o); });
        (act.targets || []).forEach(v => { const opt = [...targets.options].find(o => o.value === v); if (opt) opt.selected = true; });
        const del = document.createElement("button"); del.type = "button"; del.textContent = "?"; del.addEventListener("click", () => row.remove());
        row.appendChild(t); row.appendChild(targets); row.appendChild(del);
        return row;
    }
    function buildRule(rule = {}) {
        const div = document.createElement("div"); div.className = "logic-rule";
        const match = document.createElement("select"); match.className = "rule-match"; match.innerHTML = `<option value="all">All</option><option value="any">Any</option>`; match.value = rule.match || "all";
        const condWrap = document.createElement("div"); condWrap.className = "conditions";
        (rule.conditions || []).forEach(c => condWrap.appendChild(buildConditionRow(c)));
        const addCond = document.createElement("button"); addCond.type = "button"; addCond.textContent = "Add Condition"; addCond.addEventListener("click", () => condWrap.appendChild(buildConditionRow()));
        const actWrap = document.createElement("div"); actWrap.className = "actions";
        (rule.actions || []).forEach(a => actWrap.appendChild(buildActionRow(a)));
        const addAct = document.createElement("button"); addAct.type = "button"; addAct.textContent = "Add Action"; addAct.addEventListener("click", () => actWrap.appendChild(buildActionRow()));
        const del = document.createElement("button"); del.type = "button"; del.textContent = "Delete Rule"; del.addEventListener("click", () => div.remove());
        div.appendChild(match); div.appendChild(condWrap); div.appendChild(addCond); div.appendChild(actWrap); div.appendChild(addAct); div.appendChild(del);
        return div;
    }
    function renderLogic() {
        if (!logicRules) return;
        logicRules.innerHTML = "";
        window.currentLogic.forEach(r => logicRules.appendChild(buildRule(r)));
    }

    clearHistoryBtn?.addEventListener('click', () => {
        if (!window.currentFile) {
            alert('? No form selected.');
            return;
        }
        if (!confirm('? Are you sure you want to clear the history for this form?')) return;

        const fd = new FormData();
        fd.append('action', 'feasy_clear_form_history');
        fd.append('form_id', window.currentFile);
        fd.append('_ajax_nonce', feasy_globals.nonce);

        fetch(feasy_globals.ajaxurl, {
            method: 'POST',
            body: fd
        })
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data?.clear_key) {
                    localStorage.removeItem(res.data.clear_key);
                    alert('? History cleared.');
                } else {
                    alert('? Failed to clear history.');
                }
            });
    });

    showHistoryBtn?.addEventListener('click', () => {
        if (!historyModal) return;
        const list = historyManager?.getHistory() || [];
        historyList.innerHTML = '';
        list.forEach((h, i) => {
            const li = document.createElement('li');
            const d = new Date(h.timestamp || Date.now());
            li.textContent = d.toLocaleString();
            if (h.description) {
                li.textContent += " - " + h.description;
            }
            if (h.description) {
                li.textContent += " - " + h.description;
            }
            li.addEventListener('click', () => {
                historyManager.restoreSnapshot(i);
                historyModal.classList.remove('open');
            });
            historyList.appendChild(li);
        });
        historyModal.classList.add('open');
    });
    historyClose?.addEventListener('click', () => historyModal.classList.remove('open'));
    historyModal?.addEventListener('click', e => { if (e.target === historyModal) historyModal.classList.remove('open'); });

    editLogicBtn?.addEventListener("click", () => { renderLogic(); logicModal.classList.add("open"); });
    logicClose?.addEventListener("click", () => logicModal.classList.remove("open"));
    logicModal?.addEventListener("click", e => { if (e.target === logicModal) logicModal.classList.remove("open"); });
    addRuleBtn?.addEventListener("click", () => { logicRules.appendChild(buildRule()); });
    saveLogicBtn?.addEventListener("click", () => { window.currentLogic = collectLogic(); saveLogic(); logicModal.classList.remove("open"); });

    function collectLogic() {
        const rules = [];
        logicRules.querySelectorAll(".logic-rule").forEach(div => {
            const match = div.querySelector(".rule-match").value;
            const conditions = Array.from(div.querySelectorAll(".condition-row")).map(r => ({
                field: r.querySelector(".cond-field").value,
                operator: r.querySelector(".cond-operator").value,
                target: "value",
                value: r.querySelector(".cond-value").value
            })).filter(c => c.field);
            const actions = Array.from(div.querySelectorAll(".action-row")).map(r => ({
                action: r.querySelector(".action-type").value,
                targets: Array.from(r.querySelector(".action-targets").selectedOptions).map(o => o.value)
            })).filter(a => a.targets.length);
            if (conditions.length && actions.length) rules.push({ conditions, match, actions });
        });
        return rules;
    }

    historyManager = initHistory({
        selector,
        getCurrentFields: () => window.currentFields,
        setCurrentFields: fields => {
            window.currentFields = fields;
            renderFields();
        },
        // Do not persist history between reloads
        persistHistory: false
    });
    window.addEventListener('beforeunload', () => {
        autosave('high');
    });
    // ?? Para depurar desde la consola del navegador
    window.autosave = autosave;
    window.currentFields = currentFields;
    window.currentFile = currentFile;
});