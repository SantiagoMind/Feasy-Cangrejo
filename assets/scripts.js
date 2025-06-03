// assets/scripts.js

// —————————————————————————————————————————————
// Función para inicializar lógicas condicionales y de cálculo
// —————————————————————————————————————————————
function initFeasyConditionals(root = document) {
    root.querySelectorAll('[data-conditional]').forEach(container => {
        let data;
        try {
            data = JSON.parse(container.dataset.conditional);
        } catch {
            return;
        }

        if (data.type === 'visibility') {
            const updateVisibility = () => {
                let visible = false;

                if (Array.isArray(data.conditions)) {
                    const results = data.conditions.map(cond => {
                        const el = root.querySelector(`[name="${cond.field}"]`);
                        if (!el) return false;
                        if (el.type === 'radio') {
                            const sel = root.querySelector(`[name="${cond.field}"]:checked`);
                            return sel ? sel.value === cond.value : false;
                        }
                        return el.value === cond.value;
                    });
                    visible = (data.operator ?? 'AND') === 'AND'
                        ? results.every(Boolean)
                        : results.some(Boolean);
                } else {
                    const el = root.querySelector(`[name="${data.field}"]`);
                    const val = el?.type === 'radio'
                        ? root.querySelector(`[name="${data.field}"]:checked`)?.value
                        : el?.value;
                    visible = val === data.value;
                }

                container.style.display = visible ? 'block' : 'none';
            };

            const targetFields = data.conditions?.map(c => c.field) ?? [data.field];
            targetFields.forEach(name => {
                root.querySelectorAll(`[name="${name}"]`).forEach(el => {
                    el.addEventListener('change', updateVisibility);
                    el.addEventListener('input', updateVisibility);
                });
            });

            updateVisibility();
            setTimeout(updateVisibility, 100);
        }

        if (data.type === 'calculation') {
            const fn = () => {
                let v = '';
                data.fields.forEach(n => {
                    const f = root.querySelector(`[name="${n}"]`);
                    if (f) v += f.value;
                });
                if (container.tagName === 'INPUT') container.value = v;
                else container.innerText = v;
            };
            data.fields.forEach(n => {
                const f = root.querySelector(`[name="${n}"]`);
                if (f) {
                    f.addEventListener('input', fn);
                    f.addEventListener('change', fn);
                }
            });
            fn();
        }
    });
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
                } else if (el.tagName === 'INPUT' && el.hasAttribute('list')) {
                    const list = document.getElementById(el.getAttribute('list'));
                    if (!list) return;
                    list.innerHTML = '';
                    items.forEach(i => {
                        const opt = document.createElement('option');
                        opt.value = i[cfg.value_field];
                        list.appendChild(opt);
                    });
                } else if (['INPUT', 'TEXTAREA'].includes(el.tagName)) {
                    if (items.length) {
                        el.value = items[0][cfg.value_field];
                    }
                }
            })
            .catch(console.error);
    }
}

// —————————————————————————————————————————————
// Inicialización general en el DOM
// —————————————————————————————————————————————
document.addEventListener('DOMContentLoaded', function () {
    console.log('✅ Feasy JS active');

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
                initFeasyConditionals(placeholder);
                initFeasyDynamicFields(placeholder);
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
                    initFeasyConditionals(placeholder);
                    initFeasyDynamicFields(placeholder);
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