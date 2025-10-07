<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once __DIR__ . '/includes/cleanup.php';

Feasy_Cleanup::run();