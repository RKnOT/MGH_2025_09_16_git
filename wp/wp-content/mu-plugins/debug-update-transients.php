<?php
/*
Plugin Name: Debug Plugin Updates
Description: Zeigt den Inhalt von _site_transient_update_plugins im Admin-Bereich an.
Version: 1.0
*/

// Menüpunkt unter "Werkzeuge"
add_action('admin_menu', function () {
    add_management_page(
        'Plugin Update Debug',
        'Update Debug',
        'manage_options',
        'update-debug',
        'debug_update_transients_page'
    );
});

function debug_update_transients_page() {
    echo '<div class="wrap"><h1>Debug: _site_transient_update_plugins</h1>';
    echo '<p>So speichert WordPress die Plugin-Update-Infos. Hier finden wir die Slugs für Events Manager.</p>';

    $transient = get_site_transient('update_plugins');

    if (!$transient) {
        echo '<p><strong>Keine Daten gefunden.</strong></p>';
    } else {
        echo '<pre>' . esc_html(print_r($transient, true)) . '</pre>';
    }

    echo '</div>';
}
