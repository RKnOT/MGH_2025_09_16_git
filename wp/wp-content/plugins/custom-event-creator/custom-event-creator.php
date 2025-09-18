<?php
/*
Plugin Name: Custom Event Creator
Description: Erstellt einen Event im Event Manager und verknüpft einen bestehenden Post.
Version: 1.0
Author: Dein Name
*/

if (!defined('ABSPATH')) {
    exit; // Sicherheit
}

class CustomEventCreator {

    public function __construct() {
        // WordPress Admin Menü Hook
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX Action für die Event-Erstellung
        add_action('wp_ajax_create_event', array($this, 'create_event'));
    }

    // Admin Menü hinzufügen
    public function add_admin_menu() {
        add_menu_page(
            'Event Creator', // Seitentitel
            'Event Creator', // Menü Titel
            'manage_options', // Berechtigung
            'event-creator', // Menü Slug
            array($this, 'admin_page'), // Callback Funktion
            'dashicons-calendar', // Icon
            6 // Position
        );
    }

    // Admin Seite
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Event Creator</h1>
            <form id="create-event-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="post_id">Post ID</label></th>
                        <td><input type="number" id="post_id" name="post_id" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="event_name">Event Name</label></th>
                        <td><input type="text" id="event_name" name="event_name" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="event_date">Event Datum</label></th>
                        <td><input type="date" id="event_date" name="event_date" required></td>
                    </tr>
                </table>
                <input type="submit" class="button button-primary" value="Event erstellen">
            </form>

            <div id="response"></div>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#create-event-form').on('submit', function(e) {
                        e.preventDefault();

                        var data = {
                            action: 'create_event',
                            post_id: $('#post_id').val(),
                            event_name: $('#event_name').val(),
                            event_date: $('#event_date').val(),
                        };

                        $.post(ajaxurl, data, function(response) {
                            $('#response').html(response);
                        });
                    });
                });
            </script>
        </div>
        <?php
    }

    // Event erstellen
    public function create_event() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Du hast keine Berechtigung, das zu tun.'));
        }

        // Eingabewerte überprüfen
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $event_name = isset($_POST['event_name']) ? sanitize_text_field($_POST['event_name']) : '';
        $event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';

        if (!$post_id || !$event_name || !$event_date) {
            echo 'Fehler: Alle Felder müssen ausgefüllt sein.';
            wp_die();
        }

        // Sicherstellen, dass der Post existiert
        $post = get_post($post_id);
        if (!$post) {
            echo 'Fehler: Post existiert nicht.';
            wp_die();
        }

        // Event erstellen
        $event_id = wp_insert_post(array(
            'post_title'    => $event_name,
            'post_content'  => 'Dies ist ein automatisch erstellter Event für den Post mit ID: ' . $post_id,
            'post_status'   => 'publish',
            'post_type'     => 'event', // Event Manager Event
        ));

        if ($event_id) {
            // Event-Datum speichern (benutze den richtigen Meta Key, den der Event Manager verwendet)
            update_post_meta($event_id, '_event_start_date', $event_date);

            // Verknüpfe den Event mit dem Post (du kannst das Meta-Daten oder Taxonomien verwenden)
            update_post_meta($event_id, 'related_post_id', $post_id);

            echo 'Event erfolgreich erstellt! Event ID: ' . $event_id;
        } else {
            echo 'Fehler beim Erstellen des Events.';
        }

        wp_die();
    }
}

// Initialisiere das Plugin
new CustomEventCreator();
