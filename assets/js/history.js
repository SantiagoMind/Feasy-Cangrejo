// Manage undo/redo history for the form editor.
// History can be persisted to localStorage by setting `persistHistory` to true.
// Pass `persistHistory: false` to keep history only for the current session.
export function initHistory({ selector, getCurrentFields, setCurrentFields, persistHistory = true }) {
    const undoBtn = document.getElementById('feasy-undo');
    const redoBtn = document.getElementById('feasy-redo');
    let history = [];
    let idx = -1;

    function persist() {
        if (persistHistory) {
            localStorage.setItem(
                `feasy_history_${selector.value}`,
                JSON.stringify({ history, idx })
            );
        }
    }

    function updateButtons() {
        undoBtn.disabled = idx <= 0;
        redoBtn.disabled = idx >= history.length - 1;
    }

    function saveSnapshot() {
        const clone = JSON.parse(JSON.stringify(getCurrentFields()));
        const entry = { fields: clone, timestamp: Date.now() };
        history = history.slice(0, idx + 1);
        history.push(entry);
        idx = history.length - 1;
        persist();
        updateButtons();
    }

    undoBtn.addEventListener('click', () => {
        if (idx > 0) {
            idx--;
            const entry = history[idx];
            setCurrentFields(entry.fields ?? entry);
            updateButtons();
        }
    });
    redoBtn.addEventListener('click', () => {
        if (idx < history.length - 1) {
            idx++;
            const entry = history[idx];
            setCurrentFields(entry.fields ?? entry);
            updateButtons();
        }
    });

    // Al cambiar de formulario:
    function loadHistory() {
        if (!persistHistory) {
            localStorage.removeItem(`feasy_history_${selector.value}`);
        }
        const saved = persistHistory
            ? localStorage.getItem(`feasy_history_${selector.value}`)
            : null;
        let loaded = false;
        if (saved) {
            const obj = JSON.parse(saved);
            history = obj.history || [];
            idx = typeof obj.idx === 'number' ? obj.idx : history.length - 1;
            const entry = history[idx];
            if (entry) {
                setCurrentFields(entry.fields ?? entry);
                loaded = true;
            }
        } else {
            history = [];
            idx = -1;
        }
        updateButtons();
        return loaded;
    }
    function getHistory() {
        return history;
    }

    function restoreSnapshot(i) {
        if (history[i]) {
            idx = i;
            const entry = history[i];
            setCurrentFields(entry.fields ?? entry);
            persist();
            updateButtons();
        }
    }

    return {
        saveSnapshot,
        loadHistory,
        getHistory,
        restoreSnapshot
    };
}