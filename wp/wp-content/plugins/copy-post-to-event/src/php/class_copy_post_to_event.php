<?php

if (!defined('ABSPATH')) {
    exit; // Sicherheitsprüfung, um direkten Zugriff zu verhindern
}



class copy_post_to_event {
    function __construct() {
        $this->current_date_db = date("Y-m-d", strtotime("yesterday"));
        $this->current_date = date("d.m.Y", strtotime("yesterday"));
    }


    // b1
    public function display_post($ids) {
        $output = '';
        $post_id = $ids['input_text'];
        $post = get_post($post_id);
        if ($post) {
            $output = '<h2>Beitrags Inhalt mit der post_ID: ' .  $post_id . '</h2><br>';
            $output .= '<h2>' . esc_html($post->post_title) . '</h2>';
            $output .= '<div>' . wpautop($post->post_content) . '</div>';
           
            // Beitrag-Bilder anzeigen
            $images = get_attached_media('image', $post_id);
            if ($images) {
                $output .= '<h3>Beitragsbilder:</h3>';
                foreach ($images as $image) {
                    $img_url = wp_get_attachment_image_src($image->ID, 'full')[0];
                     $output .='<img src="' . esc_url($img_url) . '" style="max-width:300px; margin:10px;" />';
                }
            }
           // Thumbnail (falls vorhanden)
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail = $thumbnail_id ? wp_get_attachment_image($thumbnail_id) : '';

            // Post-Inhalt anzeigen
            $output .= '<div class="wrap">';
            $output .= '<h2>Thumbnail</h2>';
            if ($thumbnail) {
                $output .= '<div>' . $thumbnail . '</div>';
            }
        } else {
            $output =  '<p>Beitrag nicht gefunden.</p>';
        }
        return $output;
    }
    // b2
    public function check_if_titel_exsists_in_db($ids) {
        $post_id = $ids['input_text'];
        $post = get_post($post_id);
        $output = '';
        
        if ($post) {
            $title = $post->post_title;
            global $wpdb;
            $table_name = $wpdb->prefix . 'em_events'; 
            $posts_table = $wpdb->prefix . 'posts'; // WordPress posts table
            
            // Abfrage, um alle Events ohne im Papierkorb zu erhalten
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT e.*
                    FROM $table_name e
                    JOIN $posts_table p ON e.post_id = p.ID
                    WHERE e.event_name = %s
                    AND p.post_status != 'trash'
                    ", 
                    $title
                ), 
                ARRAY_A
            );
            /*
            // Überprüfen, ob der Event existiert
            $table_name = $wpdb->prefix . 'em_events'; // Die Tabelle für Events
            $event_names = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE event_name = %s", $title), ARRAY_A);
            */
        //var_dump($results);
        $anzahl_event = count($results);
        if($anzahl_event == 0) {
            $output = '<h3>es gibt keine Veranstaltungen mit dem Veranstaltungsnamen</br>' . $title . '</h3>';
            return $output;

        } 
        $output = '<h2>Es gibt Events von dem Beitrag mit der ID: ' . $post_id . '</h2>';
        $output .= '<h3>es sind ' . $anzahl_event . ' Veranstaltungen mit dem Veranstaltungsnamen:</h3>';
        $output .= '<h4>' . $title . '</h4>';   
    
        $output .= '<table><tr>';
        $output .= '<td width="100px">Event ID: </td>';
        $output .= '<td width="100px">Post Event ID: </td>';
        $output .= '<td width="100px"> Start Datum: </td>';
        $output .= '<td width="100px"> End Datum: </td>';
        $output .= '<td width="150px"> Event Typ: </td>';
        $output .= '<td>Event bearbeiten:</td>';
       
       
        foreach ($results as $item) {
            $event_typ  = 'einzel'; 
            if ($item['recurrence'] == 1)   $event_typ  = 'wiederkehrend'; 

            $output .= '<tr>';
            $output .= '<td>' . $item['post_id'] . '</td>';
            $output .= '<td>' . $item['event_id'] . '</td>';
            $output .= '<td>' . $item['event_start_date'] . '</td>';
            $output .= '<td>' . $item['event_end_date'] . '</td>';
            $output .= '<td>' . $event_typ . '</td>';
            $url = get_edit_post_link($item['post_id']);
            $output .= '<td><p><a href="'. $url .'" target="_blank" rel="noopener noreferrer"> Event bearbeiten</a></p></td>';
        }
        $output .= '</tr></table>';
            
        return $output;
        }
    }  
    // b3
    public function generate_event($ids) {
      
        $output = '';
        /*
        $post_id = $ids['input_text'];
        $post = get_post($post_id);
        $output =  $post->post_title . ' / '. $post->ID;
        $output .= '<h2>Event erfolgreich erstellt mit der ID:</h2>';
      */
         if ( ! class_exists( 'EM_Event' ) ) return ('Events Manager ist nicht installiert');

            $event = new EM_Event();

            $event->event_name        = 'Mein wiederkehrender Workshop 333';
            $event->post_content      = 'Beschreibung des Workshops';
            $event->event_start_date  = '2025-09-01';
            $event->event_start_time  = '10:00:00';
            $event->event_end_date    = '2025-09-01';
            $event->event_end_time    = '12:00:00';
            $event->location_id       = 1;

            // Wiederholung
            $event->recurrence           = 1;
            $event->recurrence_freq      = 'weekly';
            $event->recurrence_interval  = 1;
            $event->recurrence_days      = '1';    // Montag
            $event->recurrence_byday     = 'MO';
            $event->recurrence_end_date  = '2025-12-31';

            // Kategorie oder Location optional setzen
            $event->location_id = 1; // muss existieren

            // Speichern – Events Manager erzeugt dann automatisch die Serie
            $event->save();

             // 2. Serien-Events generieren
            if ( method_exists( 'EM_Events', 'generate_events_from' ) ) {
                EM_Events::generate_events_from( $event );
            }
            $output .= '<h2>Event erfolgreich erstellt mit der ID:</h2>';
           return ($output);
              
 
    }

    // b4
    public function get_old_single_events() {
        $content_array = $this->get_all(1);
        return $content_array;
    } 
    // b5
    public function put_all_old_events_in_papierkorb() {
        $content_array = $this->get_all(2);
        return $content_array;
    } 
    // b6
    public function clear_events_papierkorb() {
      
        $stat = '';
        $output = '';
        $events = $this->get_all_events_in_papierkorb(); 
        if (!empty($events)) {
            $anzahl_event = count($events);
            $output .= '<h3>Event ID der gelöschten Einzelveranstaltungen:</h3>';
            foreach ($events as $event) {
                wp_delete_post($event->post_id, true);
                $output .= $event->post_id . '<br>';
            }
            $stat .= 'es wurden im Papierkorb ' . $anzahl_event .' Einzelveranstaltung(en) gelöscht ';
            $stat .=  ' / bis zum Stichtag: ' .  $this->current_date; 
        } else {
            $stat = '<h3>es gibt keine zurückliegende Veranstaltungen</br>' .  '</h3>';
        }
     
      
        $content_array = array(
            'p1' => $stat,
            'p2' => $output
        );
        return $content_array;
    } 
    //------------ helper functions -------------------------------------
    function get_all_old_events() {

        global $wpdb;
        $output = '';
        // Heutiges Datum
        $current_date = date('Y-m-d');

        // SQL-Abfrage
        $query = "
            SELECT e.*
            FROM {$wpdb->prefix}em_events e
            INNER JOIN {$wpdb->prefix}posts p
                ON e.post_id = p.ID
            WHERE p.post_status != 'trash'
              AND e.event_end_date < %s
              AND recurrence = 0
             
        ";
        //
        // Ergebnisse abrufen
        $events = $wpdb->get_results($wpdb->prepare($query,  $this->current_date_db));
        return $events;
        

    }
    function get_all_events_in_papierkorb() {

        global $wpdb;
        $output = '';
        // Heutiges Datum
        $current_date = date('Y-m-d');

        // SQL-Abfrage
        $query = "
            SELECT e.*
            FROM {$wpdb->prefix}em_events e
            INNER JOIN {$wpdb->prefix}posts p
                ON e.post_id = p.ID
            WHERE p.post_status = 'trash'
              AND e.event_end_date < %s
              AND recurrence = 0
             
        ";
        //
        // Ergebnisse abrufen
        $events = $wpdb->get_results($wpdb->prepare($query,  $this->current_date_db));
        return $events;
    }
    function check_events_in_trash() {
        global $wpdb;
        $current_date = date('Y-m-d');
        // Ersetze 'event' durch den tatsächlichen Post-Typ des Events Managers
        $post_type = 'event';
    
        // Abfrage, um Events im Papierkorb zu zählen
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status = 'trash'
            AND recurrence = 0
        ", $post_type));
    
        return $count;
    }



    function get_all($get_old_events) {
        $output = "";
        $stat = "";
        $events = $this->get_all_old_events();
        if (!empty($events)) {
            $anzahl_event = count($events);
            $output .= '<table width= "80%"><tr>';
            $output .= '<td width="5%"><h3>lfd. Nr.:</h3></td>
                        <td width="7%"><h3>ID:</h3></td>
                        <td width="10%"><h3>Name:</h3></td>
                        <td width="10%"><h3>Datum: (Anfang / Ende)</h3></td>
                        <td width="10%"><h3>Zeit: (Anfang / Ende)</h3></td>';
            $output .= '</tr><tr>'; 
            $current_date = date("d.m.Y", strtotime("yesterday"));
            $count = 1;
            foreach ($events as $event) {
                    $name = $event->event_name;
                    $date = $event->event_start_date  . " / " .  $event->event_end_date;
                    $time = $event->event_start_time . " / " .  $event->event_start_time;
                    $output .= '<td>' . $count . '</td>';
                    $output .= '<td>' . $event->post_id . '</td>';
                    $output .= '<td>' . $name . '</td>';
                    $output .= '<td>' .  $date .'</td>';
                    $output .= '<td>' .  $time .'</td>';
                    $output .= '</tr><tr>'; 
                    $count = $count +1;
                    if($get_old_events == 2) {
                       wp_trash_post($event->post_id);
                       $output .= $event->post_id . '<br>';
                       $stat .= $i . '<br>';
                    }
                }
            $output .= '</tr></table>';
           
            if($get_old_events  == 1) {
                $stat = 'Es wurden ' .  $anzahl_event . ' Einzelveranstaltungen bis zum ' .  $this->current_date . ' erkannt';
            }
            elseif($get_old_events == 2) {
                $stat = 'Es wurden ' .  $anzahl_event . ' Einzelveranstaltungen bis zum ' .  $this->current_date . ' in den Veranstaltungs-Papierkorb verschoben';
            }
            else {
                $stat = ''; 
            }
        } else {
            $stat = '<h3>es gibt keine zurückliegende Veranstaltungen</br>' . '</h3>';
        }
        $content_array = array(
            'p1' => $stat,
            'p2' => $output
        );
        return $content_array;
    } 

}