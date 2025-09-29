// assets/scripts.js

// —————————————————————————————————————————————
// Función para inicializar lógicas condicionales y de cálculo
// —————————————————————————————————————————————
function initFeasyConditionals(root = document) {
    const forms = [];
    function normalize(v) {
        return String(v).trim().toLowerCase();
    }

    if (root.matches?.('form.feasy-form')) {
        forms.push(root);
    } else {
        forms.push(...root.querySelectorAll('form.feasy-form'));
        const closest = root.closest?.('form.feasy-form');
        if (forms.length === 0 && closest) {
            forms.push(closest);
        }
    }

    forms.forEach(form => {
        const evaluate = () => {
            const containers = form.querySelectorAll('[data-conditional]');
            console.log('🔧 Feasy:evaluate en', form, '→ contenedores:', containers.length);
            containers.forEach(container => {
                let data;
                try {
                    data = JSON.parse(container.dataset.conditional);
                } catch {
                    return;
                }

                if (data.type === 'visibility') {
                    let visible = false;

                    if (Array.isArray(data.conditions)) {
                        const results = data.conditions.map(cond => {
                            const el = form.querySelector(`[name="${cond.field}"]`);
                            if (!el) return false;
                            if (el.type === 'radio') {
                                const sel = form.querySelector(`[name="${cond.field}"]:checked`);
                                const val = sel ? normalize(sel.value) : '';
                                return val === normalize(cond.value);
                            }
                            return normalize(el.value) === normalize(cond.value);
                        });
                        visible = (data.operator ?? 'AND') === 'AND'
                            ? results.every(Boolean)
                            : results.some(Boolean);
                    } else {
                        const el = form.querySelector(`[name="${data.field}"]`);
                        const val = el?.type === 'radio'
                            ? form.querySelector(`[name="${data.field}"]:checked`)?.value
                            : el?.value;
                        visible = normalize(val) === normalize(data.value);
                    }

                    let showDisplay = 'block';
                    if (container.classList.contains('single-choice-group') ||
                        container.classList.contains('checkbox-single-group')) {
                        showDisplay = 'flex';
                    }
                    container.style.display = visible ? showDisplay : 'none';
                    console.log('→ display set to', container.style.display, 'for', container);
                }

                if (data.type === 'calculation') {
                    let v = '';
                    data.fields.forEach(n => {
                        const f = form.querySelector(`[name="${n}"]`);
                        if (f) v += f.value;
                    });
                    if (container.tagName === 'INPUT') container.value = v;
                    else container.innerText = v;
                }
            });
        };

        if (!form._feasyCondInit) {
            const handler = () => evaluate();
            form.addEventListener('change', handler);
            form.addEventListener('input', handler);
            form.addEventListener('click', handler);
            form._feasyCondInit = true;
        }

        evaluate();
        setTimeout(evaluate, 100);
    });
}

// —————————————————————————————————————————————
// Función para inicializar lógica avanzada de formularios
// —————————————————————————————————————————————
function normalizeFeasyLogic(raw) {
    if (!raw) return null;
    let logic = Array.isArray(raw) ? raw : raw.data;
    if (logic && !Array.isArray(logic)) logic = logic.data;
    return Array.isArray(logic) ? logic : null;
}

function initFeasyAdvancedConditions(logic, root = document) {
    logic = normalizeFeasyLogic(logic);
    if (!logic) return;
    function normalize(v) {
        return String(v).trim().toLowerCase();
    }

    const evaluate = () => {
        logic.forEach(rule => {
            const results = (rule.conditions || []).map(cond => {
                const el = root.querySelector(`[name="${cond.field}"]`);
                if (!el) return false;
                let val = '';
                if (el.type === 'radio') {
                    const sel = root.querySelector(`[name="${cond.field}"]:checked`);
                    val = sel ? sel.value : '';
                } else if (el.type === 'checkbox') {
                    if (el.name.endsWith('[]')) {
                        const cbs = Array.from(root.querySelectorAll(`[name="${cond.field}[]"]:checked`));
                        val = cbs.map(cb => cb.value);
                    } else {
                        val = el.checked ? el.value : '';
                    }
                } else {
                    val = el.value;
                }

                const normVal = Array.isArray(val)
                    ? val.map(normalize)
                    : normalize(val);
                const target = normalize(cond.value);

                switch (cond.operator) {
                    case 'not_equal_to':
                        return Array.isArray(normVal)
                            ? !normVal.includes(target)
                            : normVal !== target;
                    case 'equal_to':
                    default:
                        return Array.isArray(normVal)
                            ? normVal.includes(target)
                            : normVal === target;
                }
            });

            const passed = (rule.match || 'all') === 'any'
                ? results.some(Boolean)
                : results.every(Boolean);

            (rule.actions || []).forEach(action => {
                (action.targets || []).forEach(name => {
                    root.querySelectorAll(`[name="${name}"]`).forEach(el => {
                        const container = el.closest('.form-group') || el.closest('.form-columns') || el;
                        if (action.action === 'show') {
                            container.style.display = passed ? '' : 'none';
                        } else if (action.action === 'hide') {
                            container.style.display = passed ? 'none' : '';
                        }
                    });
                });
            });
        });
    };

    const fields = new Set();
    logic.forEach(rule => {
        (rule.conditions || []).forEach(c => fields.add(c.field));
    });

    fields.forEach(name => {
        root.querySelectorAll(`[name="${name}"]`).forEach(el => {
            el.addEventListener('change', evaluate);
            el.addEventListener('input', evaluate);
        });
    });

    evaluate();
    setTimeout(evaluate, 100);
}

// —————————————————————————————————————————————
// Función para inicializar campos dinámicos desde Google Sheets
// —————————————————————————————————————————————
function initFeasyDynamicFields(root = document) {

    root.querySelectorAll('[data-dynamic]').forEach(el => {
        let cfg;
        try {
            cfg = JSON.parse(el.dataset.dynamic);
        } catch {
            return;
        }

        (cfg.depends_on || []).forEach(dep => {
            const depEl = root.querySelector(`[name="${dep}"]`);
            if (depEl) depEl.addEventListener('change', () => fetchAndPopulate(el, cfg));
        });

        fetchAndPopulate(el, cfg);
    });

    function fetchAndPopulate(el, cfg) {
        let url = `${cfg.endpoint}?${cfg.query_param || ''}`;
        (cfg.depends_on || []).forEach(dep => {
            const v = root.querySelector(`[name="${dep}"]`)?.value;
            if (v) url += `&${dep}=${encodeURIComponent(v)}`;
        });

        fetch(url)
            .then(r => r.json())
            .then(data => {
                const items = data.items || [];

                if (el.tagName === 'SELECT') {
                    el.innerHTML = '<option value="">— Selecciona —</option>';
                    items.forEach(i => {
                        const o = document.createElement('option');
                        o.value = i[cfg.value_field];
                        o.textContent = i[cfg.label_field];
                        el.appendChild(o);
                    });
                    initFeasyConditionals(el.closest('form') || root);
                } else if (el.classList.contains('single-choice-group')) {
                    el.innerHTML = '';
                    items.forEach(i => {
                        const lbl = document.createElement('label');
                        lbl.className = 'form-option';
                        const inp = document.createElement('input');
                        inp.type = 'radio';
                        inp.name = el.dataset.name;
                        inp.value = i[cfg.value_field];
                        lbl.appendChild(inp);
                        lbl.append(` ${i[cfg.label_field]}`);
                        el.appendChild(lbl);
                    });
                    initFeasyConditionals(el.closest('form') || root);
                } else if (el.classList.contains('checkbox-single-group')) {
                    el.innerHTML = '';
                    items.forEach(i => {
                        const lbl = document.createElement('label');
                        lbl.className = 'form-option';
                        const inp = document.createElement('input');
                        inp.type = 'checkbox';
                        inp.name = el.dataset.name + '[]';
                        inp.value = i[cfg.value_field];
                        lbl.appendChild(inp);
                        lbl.append(` ${i[cfg.label_field]}`);
                        el.appendChild(lbl);
                    });
                    initFeasyConditionals(el.closest('form') || root);
                } else if (el.tagName === 'INPUT' && el.hasAttribute('list')) {
                    const list = document.getElementById(el.getAttribute('list'));
                    if (!list) return;
                    list.innerHTML = '';
                    items.forEach(i => {
                        const opt = document.createElement('option');
                        opt.value = i[cfg.value_field];
                        list.appendChild(opt);
                    });
                    initFeasyConditionals(el.closest('form') || root);
                } else if (['INPUT', 'TEXTAREA'].includes(el.tagName)) {
                    if (items.length) {
                        el.value = items[0][cfg.value_field];
                    }
                    initFeasyConditionals(el.closest('form') || root);
                }
            })
            .catch(console.error);
    }
}

// —————————————————————————————————————————————
// Helper para ejecutar funciones cuando el DOM ya está listo
// —————————————————————————————————————————————

// —————————————————————————————————————————————
// Función para inicializar campos de imagen con previsualización
// —————————————————————————————————————————————
function initFeasyImageFields(root = document) {
    root.querySelectorAll('.feasy-image-file').forEach(input => {
        const wrapper = input.closest('.feasy-image-wrapper') || input.parentElement;
        const preview = wrapper.querySelector('.feasy-image-preview');
        const hidden = wrapper.querySelector('.feasy-image-data');
        const uploadLabel = wrapper.querySelector('.feasy-image-upload');
        const removeBtn = wrapper.querySelector('.feasy-image-remove');

        if (hidden && hidden.value && preview) {
            preview.src = hidden.value;
            preview.style.display = 'block';
            if (removeBtn) removeBtn.style.display = 'inline-block';
            if (uploadLabel) uploadLabel.style.display = 'none';
        } else {
            if (uploadLabel) uploadLabel.style.display = 'inline-block';
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                if (preview) {
                    preview.src = '';
                    preview.style.display = 'none';
                }
                if (hidden) hidden.value = '';
                input.value = '';
                removeBtn.style.display = 'none';
                if (uploadLabel) uploadLabel.style.display = 'inline-block';
            });
        }

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return; // Keep existing preview if user cancels
            const reader = new FileReader();
            reader.onload = () => {
                if (preview) {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                }
                if (removeBtn) removeBtn.style.display = 'inline-block';
                if (hidden) hidden.value = reader.result;
                if (uploadLabel) uploadLabel.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });
    });
}

function onReady(fn) {
    if (document.readyState !== 'loading') {
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}

// —————————————————————————————————————————————
// Inicialización general en el DOM
// —————————————————————————————————————————————
onReady(() => {
        console.log('✅ Feasy JS active — DOM cargado');
        
        // 📢 Inyectamos log para MutationObserver
        console.log('👀 Feasy: configurando MutationObserver para formularios dinámicos');

    let isSubmitting = false;

    const overlay = document.createElement('div');
    overlay.className = 'spinner-overlay';
    overlay.innerHTML = '<div class="spinner"></div><span>Sending...</span>';
    overlay.style.display = 'none';
    document.body.appendChild(overlay);

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `
        <span class="toast-msg"></span>
        <div class="toast-controls">
            <button class="toast-close">×</button>
            <span class="toast-countdown">30</span>s
        </div>
    `;
    toast.style.display = 'none';
    document.body.appendChild(toast);

    const toastMsg = toast.querySelector('.toast-msg');
    const toastCountdown = toast.querySelector('.toast-countdown');
    const toastClose = toast.querySelector('.toast-close');

    function showToast(message, duration = 30) {
        let remaining = duration;
        toastMsg.textContent = message;
        toastCountdown.textContent = remaining;
        toast.style.display = 'flex';

        const interval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(interval);
                toast.style.display = 'none';
            } else {
                toastCountdown.textContent = remaining;
            }
        }, 1000);

        toastClose.onclick = () => {
            clearInterval(interval);
            toast.style.display = 'none';
        };
    }

    initFeasyConditionals();
    initFeasyDynamicFields();
    initFeasyImageFields();

    // Detect dynamically inserted forms and initialize their logic
    const observer = new MutationObserver(records => {
        console.log('🔄 Feasy: MutationObserver detectó cambios en DOM', records);
        records.forEach(rec => {
            rec.addedNodes.forEach(node => {
                console.log('   ➕ Nodo añadido por AJAX:', node);
                if (!(node instanceof HTMLElement)) return;
                if (node.matches('[data-conditional], form.feasy-form')) {
                    initFeasyConditionals(node);
                    initFeasyDynamicFields(node);
                    initFeasyImageFields(node);
                    if (node.dataset.logic) {
                        try {
                            const raw = JSON.parse(node.dataset.logic);
                            const logic = normalizeFeasyLogic(raw);
                            if (logic) initFeasyAdvancedConditions(logic, node);
                        } catch { }
                    }
                } else if (node.querySelector?.('[data-conditional], form.feasy-form')) {
                    initFeasyConditionals(node);
                    initFeasyDynamicFields(node);
                    initFeasyImageFields(node);
                    node.querySelectorAll('form.feasy-form[data-logic]').forEach(f => {
                        try {
                            const raw = JSON.parse(f.dataset.logic);
                            const logic = normalizeFeasyLogic(raw);
                            if (logic) initFeasyAdvancedConditions(logic, f);
                        } catch { }
                    });
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });

    document.querySelectorAll('form.feasy-form[data-logic]').forEach(f => {
        try {
            const raw = JSON.parse(f.dataset.logic);
            const logic = normalizeFeasyLogic(raw);
            if (logic) initFeasyAdvancedConditions(logic, f);
        } catch { }
    });

    document.querySelectorAll('.checkbox-single').forEach(cb =>
        cb.addEventListener('change', function () {
            if (!this.checked) return;
            document.querySelectorAll(`.checkbox-single[data-group="${this.dataset.group}"]`)
                .forEach(o => { if (o !== this) o.checked = false; });
        })
    );

    document.addEventListener('submit', e => {
        const f = e.target;
        if (!f.matches('form.feasy-form')) return;
        e.preventDefault();

        if (isSubmitting) return;
        isSubmitting = true;

        const submitBtn = f.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formTitle = f.dataset.formName
            || f.querySelector('[name="form_key"]')?.value
            || 'Unnamed Form';

        const now = new Date();
        const formattedDate = now.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const panel = f.closest('.feasy-slide-form');
        const closeBtn = panel?.querySelector('.feasy-close-btn');

        const sendingPanel = document.createElement('div');
        sendingPanel.className = 'form-submit-loading';
        sendingPanel.innerHTML = `
            <div class="spinner"></div>
            <span class="loading-title">Submitting form...</span>
            <div class="form-info-summary">
                <span><strong>Form:</strong> ${formTitle}</span>
                <span><strong>Submitted at:</strong> ${formattedDate}</span>
                <div class="error-placeholder" style="color:red;margin-top:10px;"></div>
            </div>
        `;
        f.style.display = 'none';
        f.parentNode.insertBefore(sendingPanel, f);

        if (closeBtn) closeBtn.setAttribute('disabled', 'true');
        toast.style.display = 'none';

        const fd = new FormData(f);

        // Adjuntar nonce de seguridad si existe
        const nonceField = f.querySelector('input[name="cangrejo_nonce"]');
        if (nonceField) {
            fd.append('cangrejo_nonce', nonceField.value);
        }
        fetch(f.getAttribute('action'), { method: 'POST', body: fd })
            .then(r => {
                const ct = r.headers.get('content-type') || '';
                if (ct.includes('application/json')) return r.json();
                return Promise.reject('Invalid content type');
            })
            .then(data => {
                if (data.success) {
                    f.reset();
                    sendingPanel.innerHTML = `
                        <div class="checkmark-container">
                            <svg class="checkmark" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14 27l7 7 17-17"/>
                            </svg>
                        </div>
                        <span class="loading-title success">Form submitted successfully</span>
                        <div class="form-info-summary">
                            <span><strong>Form:</strong> ${formTitle}</span>
                            <span><strong>Submitted at:</strong> ${formattedDate}</span>
                        </div>
                    `;
                    sendingPanel.style.pointerEvents = 'none';
                } else {
                    sendingPanel.querySelector('.error-placeholder')
                        .textContent = data?.data?.message || 'Submission error';
                    f.style.display = '';
                }
            })
            .catch(err => {
                console.error(err);
                sendingPanel.querySelector('.error-placeholder')
                    .textContent = 'Unexpected error';
                f.style.display = '';
            })
            .finally(() => {
                isSubmitting = false;
                if (closeBtn) closeBtn.removeAttribute('disabled');
                if (submitBtn) submitBtn.disabled = false;
            });
    });

    document.querySelectorAll('.feasy-slide-trigger').forEach(btn => {
        const id = btn.id.replace('feasy-slide-trigger-', '');
        const panel = document.getElementById(`feasy-slide-form-${id}`);
        const placeholder = panel.querySelector(`#feasy-form-placeholder-${id}`);
        let formNode = null;

        btn.addEventListener('click', () => {
            panel.classList.add('open');
            panel.style.display = 'flex';

            // ✅ Eliminar panel de éxito anterior
            const prevPanel = placeholder.querySelector('.form-submit-loading');
            if (prevPanel) prevPanel.remove();

            // ✅ Si el formNode ya existía y está desconectado, lo reinserta
            if (formNode && !formNode.parentElement) {
                placeholder.innerHTML = '';
                placeholder.appendChild(formNode);
                initFeasyConditionals(formNode);
                initFeasyDynamicFields(formNode);
                initFeasyImageFields(formNode);
                const logicData = formNode.dataset.logic;
                if (logicData) {
                    try {
                        const raw = JSON.parse(logicData);
                        const logic = normalizeFeasyLogic(raw);
                        if (logic) initFeasyAdvancedConditions(logic, formNode);
                    } catch { }
                }
                return;
            }

            placeholder.innerHTML = `
                <div class="form-loading">
                    <div class="spinner"></div>
                    <span>Loading…</span>
                </div>`;

            fetch(`?feasy_form_ajax=1&form=${id}`)
                .then(r => r.text())
                .then(html => {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = html;
                    formNode = tmp.firstElementChild;
                    placeholder.innerHTML = '';
                    placeholder.appendChild(formNode);
                    initFeasyConditionals(formNode);
                    initFeasyDynamicFields(formNode);
                    initFeasyImageFields(formNode);
                    const logicData = formNode.dataset.logic;
                    if (logicData) {
                        try {
                            const raw = JSON.parse(logicData);
                            const logic = normalizeFeasyLogic(raw);
                            if (logic) initFeasyAdvancedConditions(logic, formNode);
                        } catch { }
                    }
                })
                .catch(() => {
                    placeholder.innerHTML = '<div class="form-message error">❌ Error al cargar form.</div>';
                });
        });

        const closeBtn = panel.querySelector('.feasy-close-btn');
        closeBtn?.addEventListener('click', () => {
            panel.classList.remove('open');
            panel.style.display = 'none';
        });
    });
});