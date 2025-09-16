<?php
/*
Plugin Name: Sichere REST API für Gäste (mit Logging)
Description: Erlaubt REST API im Frontend ohne Benutzerdaten oder private Inhalte. Loggt REST-Zugriffe von Gästen.
Author: RKnOT
Version: 1.1
*/

// === 1. Endpunkte entfernen ===
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( ! is_user_logged_in() ) {
        unset( $endpoints['/wp/v2/users'] );
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        unset( $endpoints['/wp/v2/comments'] );
        unset( $endpoints['/wp/v2/settings'] );
    }
    return $endpoints;
});

// === 2. Nur veröffentlichte Inhalte ===
add_filter( 'rest_post_query', function( $args, $request ) {
    if ( ! is_user_logged_in() ) {
        $args['post_status'] = 'publish';
    }
    return $args;
}, 10, 2 );

// === 3. REST-Antworten säubern ===
add_filter( 'rest_prepare_post', 'rkn_sichere_rest_daten', 10, 3 );
add_filter( 'rest_prepare_page', 'rkn_sichere_rest_daten', 10, 3 );
add_filter( 'rest_prepare_event', 'rkn_sichere_rest_daten', 10, 3 );
add_filter( 'rest_prepare_location', 'rkn_sichere_rest_daten', 10, 3 );

function rkn_sichere_rest_daten( $response, $post, $request ) {
    if ( ! is_user_logged_in() ) {
        $data = $response->get_data();

        unset( $data['author'] );
        unset( $data['meta'] );
        unset( $data['comment_status'] );
        unset( $data['ping_status'] );

        $response->set_data( $data );
    }

    // Logging
    if ( ! is_user_logged_in() ) {
        rkn_log_rest_request( $request );
    }

    return $response;
}

// === 4. Logging-Funktion ===
function rkn_log_rest_request( $request ) {
    $log_dir  = WP_CONTENT_DIR;
    $log_file = $log_dir . '/rest-api-log.txt';
    $max_size = 500 * 1024; // 500 KB

    // Archivieren wenn zu groß
    if ( file_exists( $log_file ) && filesize( $log_file ) > $max_size ) {
        $timestamp     = date( 'Y-m-d-H-i-s' );
        $archive_file  = $log_dir . "/rest-api-log-{$timestamp}.txt";
        rename( $log_file, $archive_file );
    }

    // Logeintrag schreiben
    $timestamp = date( 'Y-m-d H:i:s' );
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $endpoint  = $request->get_route();
    $method    = $request->get_method();
    $agent     = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $log_entry = "[{$timestamp}] {$ip} {$method} {$endpoint} | Agent: {$agent}" . PHP_EOL;
    file_put_contents( $log_file, $log_entry, FILE_APPEND );
}







