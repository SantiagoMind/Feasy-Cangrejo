export function initHistory({ selector, getCurrentFields, setCurrentFields }) {
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

    undoBtn.addEventListener('click', () => {
        if (idx > 0) {
            idx--;
            setCurrentFields(history[idx]);
            updateButtons();
        }
    });
    redoBtn.addEventListener('click', () => {
        if (idx < history.length - 1) {
            idx++;
            setCurrentFields(history[idx]);
            updateButtons();
        }
    });

    // Al cambiar de formulario:
    function loadHistory() {
        const saved = localStorage.getItem(`feasy_history_${selector.value}`);
        let loaded = false;
        if (saved) {
            const obj = JSON.parse(saved);
            history = obj.history;
            idx = obj.idx;
            if (history[idx]) {
                setCurrentFields(history[idx]);
                loaded = true;
            }
        } else {
            history = [];
            idx = -1;
        }
        updateButtons();
        return loaded;
    }

    return {
        saveSnapshot,
        loadHistory
    };
}