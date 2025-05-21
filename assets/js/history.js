export function initHistory({ selector, renderFields, getCurrentFields, setCurrentFields, onSnapshotChange }) {
    const undoBtn = document.getElementById('feasy-undo');
    const redoBtn = document.getElementById('feasy-redo');
    let history = [];
    let idx = -1;

    function updateButtons() {
        undoBtn.disabled = idx <= 0;
        redoBtn.disabled = idx >= history.length - 1;
    }

    function saveSnapshot() {
        const clone = JSON.parse(JSON.stringify(getCurrentFields()));
        history = history.slice(0, idx + 1);
        history.push(clone);
        idx = history.length - 1;
        localStorage.setItem(`feasy_history_${selector.value}`, JSON.stringify({ history, idx }));
        updateButtons();
    }

    // ? Guardar snapshot manualmente desde fuera
    window.feasySaveSnapshot = saveSnapshot;

    undoBtn.addEventListener('click', () => {
        if (idx > 0) {
            idx--;
            window.__feasyHistoryRestore = true; // ? Flag para evitar snapshot automático
            setCurrentFields(history[idx]);
            updateButtons();
            localStorage.setItem(`feasy_history_${selector.value}`, JSON.stringify({ history, idx }));
            if (typeof onSnapshotChange === 'function') {
                onSnapshotChange('undo');
            }
        }
    });

    redoBtn.addEventListener('click', () => {
        if (idx < history.length - 1) {
            idx++;
            window.__feasyHistoryRestore = true; // ? Agregado para evitar snapshot tras Redo
            setCurrentFields(history[idx]);
            updateButtons();

            // ? Mantener sincronizado el índice
            localStorage.setItem(`feasy_history_${selector.value}`, JSON.stringify({ history, idx }));

            if (typeof onSnapshotChange === 'function') {
                onSnapshotChange('redo');
            }
        }
    });

    selector.addEventListener('change', () => {
        const saved = localStorage.getItem(`feasy_history_${selector.value}`);
        if (saved) {
            const obj = JSON.parse(saved);
            history = obj.history;
            idx = obj.idx;
            setCurrentFields(history[idx]);
        } else {
            history = [];
            idx = -1;
        }
        updateButtons();
    });
}