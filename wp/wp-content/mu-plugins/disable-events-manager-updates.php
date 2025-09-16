<?php
/*
Plugin Name: Disable Events Manager Updates (Configurable)
Description: Blockiert dauerhaft Updates für Events Manager & Addons, mit Umschalter im Backend.
Version: 1.4
Author: RKnOT
*/

function disable_em_updates_list() {
    return ['events-manager/events-manager.php', 'events-manager-pro/events-manager-pro.php'];
}

/**
 * Filtert Update-Infos raus, wenn die Option aktiviert ist
 */
function disable_em_updates_filter($transient) {
    if (!get_option('disable_em_updates_enabled', true)) {
        return $transient; // nichts blockieren
    }

    if (!is_object($transient) || empty($transient->response)) return $transient;

    foreach ($transient->response as $plugin_file => $data) {
        if (strpos($plugin_file, 'events-manager') !== false) {
            unset($transient->response[$plugin_file]);
        }
    }
    return $transient;
}
add_filter('site_transient_update_plugins', 'disable_em_updates_filter', 1);
add_filter('pre_set_site_transient_update_plugins', 'disable_em_updates_filter', 1);

/**
 * Admin-Einstellungen hinzufügen
 */
add_action('admin_menu', function(){
    add_options_page(
        'Events Manager Updates',
        'Events Manager Updates',
        'manage_options',
        'em-updates-settings',
        'disable_em_updates_settings_page'
    );
});

function disable_em_updates_settings_page() {
    if (isset($_POST['disable_em_updates_nonce']) && wp_verify_nonce($_POST['disable_em_updates_nonce'], 'disable_em_updates_save')) {
        update_option('disable_em_updates_enabled', !empty($_POST['disable_em_updates_enabled']));
        echo '<div class="updated"><p><strong>Einstellungen gespeichert.</strong></p></div>';
    }

    $enabled = get_option('disable_em_updates_enabled', true);
    ?>
    <div class="wrap">
        <h1>Events Manager Update-Blocker</h1>
        <form method="post">
            <?php wp_nonce_field('disable_em_updates_save', 'disable_em_updates_nonce'); ?>
            <label>
                <input type="checkbox" name="disable_em_updates_enabled" value="1" <?php checked($enabled, true); ?>>
                Updates für Events Manager blockieren
            </label>
            <p class="submit">
                <button type="submit" class="button-primary">Speichern</button>
            </p>
        </form>
    </div>
    <?php
}
