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
        $checkboxStatus = $ids['checkbox']; // 1 / 0 
        if (!$checkboxStatus) {
            $output = '<div class="notice notice-success is-dismissible">';
            $output .= '<p>Veranstaltung wird nicht kopiert, da die Checkbox "Veranstaltung kopieren" nicht gesetzt ist!!</p>';
            $output .= '</div>';
            return $output;
        }
        $post_id = $ids['input_text'];
        $event_type = $ids['radio_option']; // option1 , option2
        $post = get_post($post_id);
        if ($post) {
            // Post-Daten abrufen
            $title = $post->post_title;
            $content = $post->post_content;
            // Thumbnail (falls vorhanden)
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail_url = wp_get_attachment_url($thumbnail_id);
            //check if title exsists in wp_em_events
              // Neues Event-Objekt erstellen
              $event = new EM_Event();
              if (class_exists('EM_Event')) {
                // Event-Daten setzen
                $event->event_name = $title;
                $event->post_content =   $content;
                $event->event_start_date = date('Y-m-d'); // Startdatum
                $event->event_end_date = date('Y-m-d'); // Enddatum am selben Tag (einmaliges Event)
                // Zeit für das Event festlegen
                $event->event_start_time = '09:00:00';
                $event->event_end_time = '10:00:00';
                // Setze den Ort (optional)
                $event->location_id = 1; // Beispielhaft, du kannst hier die tatsächliche Location-ID verwenden
                if ($event_type  == 'option2'){
                    $event->event_end_date = date('Y-m-d', strtotime('+1 day'));
                    // Wiederholungseinstellungen für tägliches Event
                    $event->recurrence = 1; // Aktiviert die Wiederholung
                    $event->recurrence_freq = 'daily'; // Tägliche Wiederholung
                    $event->recurrence_interval = 1; // Wiederhole es jeden Tag
                    $event->recurrence_byday = ''; // Nicht relevant für tägliche Wiederholung
                    $event->recurrence_days = ''; // Nicht notwendig für tägliche Wiederholung
                    // Start- und Enddatum der Wiederholung festlegen
                    $event->recurrence_start_date = date('Y-m-d'); // Wiederholung startet am 30. September 2024
                    $event->recurrence_end_date = date('Y-m-d', strtotime('+1 day')); // Wiederholung endet nach einem Jahr
                }
                // Event speichern
                    $event_id = $event->save();
                    if ($event_id) {
                            $post_event_id = $event->post_id;
                            $event_id = $event->event_id;
                            $event_name = $event->event_name;
                            // Beitragsbild (Thumbnail) setzen
                            if (has_post_thumbnail($post->ID)) {
                                $thumbnail_id = get_post_thumbnail_id($post->ID); // ID des Beitragsbilds abrufen
                                set_post_thumbnail($post_event_id, $thumbnail_id); // Beitragsbild für das Event setzen
                            }
                            $output = '<h2>Event erfolgreich erstellt mit der ID:</h2>';
                            //$output .= '<div class="notice notice-success is-dismissible">';
                            $output .= '<p>Event erfolgreich erstellt! <a href="'. get_edit_post_link($post_event_id) .'" target="_blank">Event bearbeiten</a></p>';
                            $output .= '</div>';
                           
                            $output .= '<table><tr>'; 
                            $output .=  '<td width ="200px">Post ID: </td><td>' . $post_id  .  '</td>'; 
                            $output .=  '</tr><tr>';
                            $output .=  '<td>Event ID: </td><td>' . $post_event_id . '</td>';
                            $output .=  '</tr><tr>';
                            $output .=  '<td>Event Post ID: </td><td>' . $event_id . '</td>';
                            $output .=  '</tr><tr>';
                            $output .=   '<td>Event Name: </td><td>' . $event_name . '</td>'; 
                            $output .=  '</tr><tr>';
                            $output .=  '<td>thumbnail ID </td><td>' . $thumbnail_id . '</td>'; 
                            $output .=  '</tr><tr>';
                            $output .= '<td>thumbnail URL: </td><td>' . $thumbnail_url . '</td>'; 
                            $output .=  '</tr><tr></table>';
                            
                      


                    } else {
                        $output = 'Fehler beim Erstellen des Events: ' . $event->get_errors();
                    }
              } else {
                $output = '<div class="error"><p>Events Manager Plugin ist nicht installiert oder aktiviert.</p></div>';

              }
              return ($output);
           
        }
 
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