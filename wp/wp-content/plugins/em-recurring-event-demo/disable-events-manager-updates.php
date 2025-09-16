<?php
/*
Plugin Name: Disable Events Manager + Addons Updates
Description: Verhindert dauerhaft Update-Hinweise für Events Manager und alle zugehörigen Add-ons.
Author: RKnOT
Version: 1.0
*/

// Liste der Plugins, deren Updates blockiert werden sollen
function disable_em_updates_list() {
    return [
        'events-manager/events-manager.php',       // Hauptplugin
        'events-manager-pro/events-manager-pro.php', // Pro-Version
        // ggf. hier weitere Addons eintragen:
        // 'events-manager-xyz/events-manager-xyz.php',
    ];
}

add_filter('site_transient_update_plugins', function($transient) {
    if (empty($transient->response)) {
        return $transient;
    }

    foreach (disable_em_updates_list() as $plugin_file) {
        if (isset($transient->response[$plugin_file])) {
            unset($transient->response[$plugin_file]);
        }
    }

    return $transient;
});
