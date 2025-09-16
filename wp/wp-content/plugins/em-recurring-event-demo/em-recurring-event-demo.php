<?php
/**
 * Plugin Name: EM Recurring Event Demo
 * Description: Erstellt ein wiederkehrendes Event über die Events Manager 7.0.5 API inkl. Anzeige der Instanzen.
 * Version: 1.1
 * Author: Dein Name
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class EM_Recurring_Event_Demo {

    public static function init(){
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
    }

    public static function admin_menu(){
        add_submenu_page(
            'edit.php?post_type=event',
            'EM Recurring Demo',
            'EM Recurring Demo',
            'manage_options',
            'em-recurring-demo',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function render_admin_page(){
        echo '<div class="wrap"><h1>EM Recurring Demo</h1>';
        
        if ( isset($_POST['emrd_create']) && check_admin_referer('emrd_create_nonce','emrd_nonce') ){
            $result = self::create_recurring_event();
            if ( isset($result['error']) ){
                echo '<div class="notice notice-error"><p>Fehler: '.esc_html(print_r($result['error'],true)).'</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>Serie erstellt!</p></div>';

                // Master anzeigen
                echo '<h2>Master-Event</h2>';
                echo '<pre>'.esc_html(print_r($result['master']->to_array(),true)).'</pre>';

                // Kinder anzeigen
                echo '<h2>Generierte Veranstaltungen</h2>';
                if ( !empty($result['children']) ){
                    echo '<table class="widefat"><thead><tr><th>ID</th><th>Name</th><th>Start</th><th>Ende</th></tr></thead><tbody>';
                    foreach( $result['children'] as $child ){
                        echo '<tr>';
                        echo '<td>'.esc_html($child->event_id).'</td>';
                        echo '<td>'.esc_html($child->event_name).'</td>';
                        echo '<td>'.esc_html($child->event_start_date.' '.$child->event_start_time).'</td>';
                        echo '<td>'.esc_html($child->event_end_date.' '.$child->event_end_time).'</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo '<p><em>Keine Instanzen generiert.</em></p>';
                }
            }
        }

        echo '<form method="post">';
        wp_nonce_field('emrd_create_nonce','emrd_nonce');
        submit_button('Wiederkehrendes Event jetzt erstellen', 'primary', 'emrd_create');
        echo '</form>';

        echo '</div>';
    }

   public static function create_recurring_event(){
    if( !class_exists('EM_Event') ) return [ 'error' => 'Events Manager nicht aktiv.' ];

    $event = new EM_Event();

    // Basisdaten
    $event->event_name        = 'Mein wiederkehrender Workshop (Demo)';
    $event->post_content      = 'Beschreibung erstellt vom Plugin.';
    $event->event_owner       = get_current_user_id();
    $event->event_status      = 1;
    $event->event_active_status = 1;
    $event->event_timezone    = 'Europe/Berlin';
    $event->location_id       = self::get_any_location_id();

    // Start/Ende – ALLE Felder setzen!
    $event->event_start_date  = '2025-09-01';
    $event->event_start_time  = '10:00:00';
    $event->event_end_date    = '2025-09-01';
    $event->event_end_time    = '12:00:00';
    $event->event_start       = $event->event_start_date.' '.$event->event_start_time;
    $event->event_end         = $event->event_end_date.' '.$event->event_end_time;

    // Wiederholung aktivieren
    $event->recurrence           = 1;
    $event->recurrence_freq      = 'weekly';
    $event->recurrence_interval  = 1;
    $event->recurrence_byday     = 'MO';
    $event->recurrence_days      = '1';
    $event->recurrence_end_date  = '2025-12-31';

    // Speichern
    $saved = $event->save();
    if( !$saved ){
        return [ 'error' => $event->errors ];
    }

    // Serie generieren
    if( !empty($event->recurrence) && method_exists($event, 'save_events') ){
        $event->save_events(true);
    }

    // Kinder abrufen
    $children = EM_Events::get( [
        'recurrence_id' => $event->event_id,
        'scope' => 'all'
    ]);

    return [
        'master'   => $event,
        'children' => $children
    ];
}

    private static function get_any_location_id(){
        global $wpdb;
        $id = $wpdb->get_var("SELECT location_id FROM {$wpdb->prefix}em_locations LIMIT 1");
        return $id ?: null;
    }
}

EM_Recurring_Event_Demo::init();
