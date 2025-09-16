<?php
/*
Plugin Name: Copy Post to Event
Description: Ein Plugin zum Kopieren von posts in events vom plugin events-manager und löschen alter events
Version: 3.0
Author: RKnOT
*/

// Verhindert den direkten Aufruf der Datei
if (!defined('ABSPATH')) {
    exit;
}

class ACPTE_Copy_Post_To_Event {
    private $option_name = 'custom_form_data';
  
    // Konstruktor: Registriert die notwendigen WordPress-Hooks
    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
        add_action('admin_init', array($this, 'save_form_data'));
        // Action-Hook, um JavaScript-Datei zu laden
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_button1_action', array($this, 'button1_click'));
        add_action('wp_ajax_button2_action', array($this, 'button2_click'));
        add_action('wp_ajax_button3_action', array($this, 'button3_click'));
        add_action('wp_ajax_button4_action', array($this, 'button4_click'));
        add_action('wp_ajax_button5_action', array($this, 'button5_click'));
        add_action('wp_ajax_button6_action', array($this, 'button6_click'));
        add_action('wp_ajax_get_progress', array($this, 'get_progress'));

    }
   
    // Erstellen des Admin-Menüs
    public function create_menu() {
        add_menu_page(
            'Copy Post to Event',
            'Copy Post to Event',
            'manage_options',
            'acpte-settings',
            array($this, 'display_form'),
            'dashicons-admin-generic',
            55
        );
    }

    public function display_form() {
        $saved_data = get_option($this->option_name, array(
            'input_text' => '',
            'radio_option' => '',
            'checkbox' => false
        ));

        $bild_url = plugin_dir_url(__FILE__) . 'src/img/screenshot.png';
        $sanduhr = plugin_dir_url(__FILE__) . 'src/img/sanduhr.gif';

        ?>
          <style>
                table {
                    border-collapse: separate;  /*Notwendig, um `border-spacing` zu verwenden */
                    border-spacing: 10px 10px; /* Horizontaler und vertikaler Abstand zwischen den Zellen */
                    background-color: #f7f7f7;   
                    border-radius: 20px;            /* Abgerundete Kanten der Tabelle */
                    overflow: hidden;
                }
                th, td {
                    padding: 15px; /* Innerer Abstand in den Zellen (horizontal und vertikal) */
                }
           </style>
        <div class="wrap">
            <table><tr>
  
            <td rowspan="2"><?php  echo  '<img src="' . esc_url($bild_url) . '" alt="logo"; height="75px">' ?></td>
         
            <td><h3>ein Plugin von RKnOT</h3><br>
            &copy;2024 RKnOT all rights reserved</td>
            </tr></table>  


            <div style="width: 80%; padding: 10px;">
                <div style="float: left; width: 40%; ">
                    <table >
                        <tr>
                            <td colspan ="3"><h3>Beitrag in einen neuen Event kopieren</h3></td>
                            <tr>
                            <form method="post" action="">
                                    <?php wp_nonce_field('custom_form_nonce', 'custom_form_nonce'); ?>
                                    <td ><label>Veranstaltungs Typ:</label></td>
                                    <td><input type="radio" id="radio1" name="radio_option" value="option1" <?php checked($saved_data['radio_option'], 'option1'); ?>>
                                    <label for="radio1">Einzel</label></td>
                                    <td><input type="radio" id="radio2" name="radio_option" value="option2" <?php checked($saved_data['radio_option'], 'option2'); ?>>
                                    <label for="radio2">Wiederkehrend</label></td>
                                    </tr><tr>
                                    <td><label for="input_text">Post ID:</label></td>
                                    <td><input type="text" id="input_text" name="input_text" value="<?php echo esc_attr($saved_data['input_text']); ?>"></td>
                                    </tr><tr>     
                                    <td><label for="checkbox">Veranstaltung kopieren:</label></td>
                                    <td><input type="checkbox" id="checkbox" name="checkbox" <?php checked($saved_data['checkbox'], true); ?>></td>
                                    <td><input type="submit" name="save_button" class="button button-secondary" value="Werte speichern"></td>          
                                    </tr>
                            </form>
                            <tr><td>
                                <button id="b1" class="button button-secondary">Beitrag anzeigen</button></td>
                                <td colspan="2"><button id="b2" class="button button-secondary">check Event bereits vorhanden</button></td>
                                </tr> 
                        <tr><td></td><td>
                        <button id="b3" class="button button-primary">Beitrag in neues Ereignis kopieren</button>
                        </td></tr>
                    </table>
                </div>
     
                <div style= "float: right; width: 40%; ">
                    <table>
                        <tr>
                                <td><h3>Alle alten Veranstaltungen in den Papierkorb verschieben oder komplett löschen</h3></td>
                        </tr><tr>
                                <td><button id="b4" class="button button-secondary">Alle alte Einzel-Veranstaltungen anzeigen</button></td>
                        </tr><tr>
                            
                                <td> <button id="b5" class="button button-primary">Alle alten Einzel-Veranstaltungen in den Papierkorb</button></td>
                        </tr><tr>
                                <td><button id="b6" class="button button-primary">die Einzel-Veranstaltungen im Events Papierkorb löschen</button></td>
                         </tr><tr>
                            <td>
                            <div id="progress" style="float: left; width: 100%;  padding: 10px;"></div>
                               <div class="progress-container">
                                    <div id="progressBar" class="progress-bar"></div>
                            
                                   <div id="sanduhr"  class="hidden" ><?php  echo  '<img src="' . esc_url($sanduhr) . '" alt="logo"; >' ?></div>
                                    
                                </div>
                                
                            <div id="status_del" style="float: left; width: 100%;  padding: 10px;"></div>
                            </div>
                            </td>
                        </tr><tr>
                    </table>    
                    </div>
                <div style="clear: both;"></div>
        
            </div>
           
           
            <div id="status" style="float: left; width: 100%;  padding: 10px;"></div>
        </div>
        <?php
    }

     // JavaScript-Datei einbinden
     public function enqueue_admin_scripts() {
        wp_enqueue_style('rkn_ot_styles', plugins_url('src/css/rkn_ot.css', __FILE__));
        wp_enqueue_script('b1-script', plugin_dir_url(__FILE__) . 'src/js/my-custom-script.js', array('jquery'), '1.3', true);

        // Ajax-URL und Nonce an das Script übergeben
        wp_localize_script('b1-script', 'myButtonAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('my_button_nonce')
        ));
    }

       // PHP-Funktion, die durch den Button ausgelöst wird
       public function button1_click() {
        // Sicherheit prüfen
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
            wp_send_json_error('Ungültige Anfrage');
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
        $myObject = new copy_post_to_event();
        $ids = get_option($this->option_name);
        $content =  $myObject->display_post($ids);
        $myObject = null;
        wp_send_json_success($content);
    }
    public function button2_click() {
        // Sicherheit prüfen
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
            wp_send_json_error('Ungültige Anfrage');
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
        $myObject = new copy_post_to_event();
        $ids = get_option($this->option_name);
        $content =  $myObject->check_if_titel_exsists_in_db($ids);
        $myObject = null;
        wp_send_json_success($content);
    }
    public function button3_click() {
        // Sicherheit prüfen
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
            wp_send_json_error('Ungültige Anfrage');
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
        $myObject = new copy_post_to_event();
        $ids = get_option($this->option_name);
        $content =  $myObject-> generate_event($ids);
        $myObject = null;
        //reste checkbox
        $ids['checkbox'] = 0;
        update_option($this->option_name, $ids);
        wp_send_json_success($content);
    }
    // PHP-Funktion, die durch den Button ausgelöst wird     
    
     public function button4_click() {
        // Sicherheit prüfen
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
            wp_send_json_error('Ungültige Anfrage');                
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
        $myObject = new copy_post_to_event();
        $content =  $myObject->get_old_single_events();
        $myObject = null;
        wp_send_json_success($content);
    }
    public function button5_click() {
        // Sicherheit prüfen
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
            wp_send_json_error('Ungültige Anfrage');                
            return;
        }
        require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
        $myObject = new copy_post_to_event();
        $content_array =  $myObject->put_all_old_events_in_papierkorb();
        $myObject = null;
        wp_send_json_success($content_array);
}
    
    public function button6_click() {
                // Sicherheit prüfen
                if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'my_button_nonce')) {
                    wp_send_json_error('Ungültige Anfrage');                
                    return;
                }
         
                require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
                $myObject = new copy_post_to_event();
                $content =  $myObject->clear_events_papierkorb();
                $myObject = null;
                wp_send_json_success($content);

                /*
                require_once plugin_dir_path(__FILE__) . 'src/php/class_copy_post_to_event.php';
                $myObject = new copy_post_to_event();
                $content =  $myObject->clear_events_papierkorb();
                $myObject = null;
                wp_send_json_success($content);
                */            
    }

   // Fortschritt abfragen
    public function get_progress() {
        $action_id = isset($_POST['action_id']) ? intval($_POST['action_id']) : 0;
        $transient_key = "rkn_ot_progress_$action_id";
        $progress = get_transient($transient_key);
        if ($progress === false) {
            wp_send_json_error("Kein Fortschritt verfügbar.");
        } else {
            wp_send_json_success($progress);
        }
    }
    
       

    public function save_form_data() {
        if (isset($_POST['save_button']) && check_admin_referer('custom_form_nonce', 'custom_form_nonce')) {
            $data = array(
                'input_text' => sanitize_text_field($_POST['input_text']),
                'radio_option' => sanitize_text_field($_POST['radio_option']),
                'checkbox' => isset($_POST['checkbox']) ? true : false
            );
            update_option($this->option_name, $data);
            add_action('admin_notices', array($this, 'show_success_notice'));
        } 
    }

    public function show_success_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Daten erfolgreich gespeichert!</p>
        </div>
        <?php
    }

  

    
}



new ACPTE_Copy_Post_To_Event();





