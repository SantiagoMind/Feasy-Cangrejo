<?php

class Feasy_Library_Updater {
    protected static $libraries = [];

    public static function register(array $libs) {
        self::$libraries = $libs;
        add_action('init', [self::class, 'maybe_update_libraries']);
    }

    public static function maybe_update_libraries() {
        if (get_transient('feasy_libraries_last_update')) {
            error_log('[Feasy] ⏳ Saltando update (ya se ejecutó esta semana).');
            return;
        }

        foreach (self::$libraries as $slug => $lib) {
            self::update_library($slug, $lib);
        }

        set_transient('feasy_libraries_last_update', time(), WEEK_IN_SECONDS);
    }

    protected static function update_library(string $slug, array $lib) {
        $repo        = $lib['repo'] ?? '';
        $filename    = $lib['filename'] ?? '';
        $urlPattern  = $lib['url'] ?? '';

        if (!$repo || !$filename || !$urlPattern) {
            error_log("[Feasy] ⚠️ Datos incompletos para la librería {$slug}");
            return;
        }

        $vendorDir   = plugin_dir_path(__DIR__) . 'assets/vendor/' . $slug . '/';
        $versionFile = $vendorDir . $slug . '.version';
        $jsFile      = $vendorDir . $filename;

        if (!is_dir($vendorDir)) {
            mkdir($vendorDir, 0755, true);
        }

        $currentVersion = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '0.0.0';

        $apiUrl = 'https://api.github.com/repos/' . $repo . '/releases/latest';
        $response = wp_remote_get($apiUrl, [
            'headers' => ['User-Agent' => 'FeasyUpdater/1.0']
        ]);

        if (is_wp_error($response)) {
            error_log("[Feasy] ❌ Error consultando {$slug}: " . $response->get_error_message());
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $latestVersion = ltrim($data['tag_name'] ?? '', 'v');

        if (!$latestVersion) {
            error_log("[Feasy] ⚠️ No se pudo obtener la versión para {$slug}");
            return;
        }

        if (version_compare($latestVersion, $currentVersion, '<=')) {
            error_log("[Feasy] ✅ {$slug} ya está en la versión {$currentVersion}");
            return;
        }

        $downloadUrl = str_replace('%version%', $latestVersion, $urlPattern);
        $jsData = wp_remote_get($downloadUrl, [
            'headers' => ['User-Agent' => 'FeasyUpdater/1.0']
        ]);

        if (is_wp_error($jsData)) {
            error_log("[Feasy] ❌ Falló descarga de {$slug}: " . $jsData->get_error_message());
            return;
        }

        file_put_contents($jsFile, wp_remote_retrieve_body($jsData));
        file_put_contents($versionFile, $latestVersion);
        error_log("[Feasy] 🚀 {$slug} actualizado a {$latestVersion}");
    }
}