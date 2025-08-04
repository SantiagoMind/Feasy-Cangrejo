(function () {
    function clearFeasyStorage() {
        if (typeof localStorage !== 'undefined') {
            Object.keys(localStorage)
                .filter(function (k) { return k.startsWith('feasy_'); })
                .forEach(function (k) { localStorage.removeItem(k); });
        }
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a.delete');
        if (!link) return;
        if (link.href && link.href.indexOf('proyecto_cangrejo_alpha') !== -1) {
            clearFeasyStorage();
        }
    });

    // If plugin deletion is triggered via wp.updates
    if (window.wp && wp.updates) {
        var origDelete = wp.updates.deletePlugin;
        wp.updates.deletePlugin = function () {
            clearFeasyStorage();
            return origDelete.apply(this, arguments);
        };
    }
})();