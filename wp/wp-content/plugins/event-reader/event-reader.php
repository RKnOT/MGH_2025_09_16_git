<?php
/*
Plugin Name: Event Manager Record Reader
Description: Zeigt Event-Daten aus der wp_em_events-Tabelle im Backend an.
Version: 1.0
Author: Dein Name
*/

if (!defined('ABSPATH')) exit;

// Backend-Menü hinzufügen
add_action('admin_menu', function () {
    add_menu_page(
        'Event Record Reader',
        'Event Reader',
        'manage_options',
        'event-reader',
        'event_reader_admin_page',
        'dashicons-search',
        30
    );
});

// Backend-Seite
function event_reader_admin_page() {
    ?>
    <div class="wrap">
        <h1>Event Manager Record Reader</h1>
        <form method="post">
            <label for="event_id">Event ID:</label>
            <input type="number" name="event_id" id="event_id" required>
            <input type="submit" name="read_event" class="button button-primary" value="Read DB Record">
        </form>
        <hr>
        <?php
        if (isset($_POST['read_event']) && !empty($_POST['event_id'])) {
            global $wpdb;
            $event_id = intval($_POST['event_id']);
            $table_name = $wpdb->prefix . 'em_events';

            $event = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_name WHERE event_id = %d", $event_id),
                ARRAY_A
            );

            if ($event) {
                echo "<h2>Event ID $event_id gefunden:</h2>";
                echo "<table class='widefat fixed striped'>";
                echo "<thead><tr><th>Feldname</th><th>Wert</th></tr></thead><tbody>";
                foreach ($event as $key => $value) {
                    echo "<tr><td><strong>" . esc_html($key) . "</strong></td><td>" . esc_html($value) . "</td></tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<div class='notice notice-error'><p>Kein Event mit der ID $event_id gefunden.</p></div>";
            }
        }
        ?>
    </div>
    <?php
}
