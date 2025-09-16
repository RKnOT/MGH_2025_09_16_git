<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mgh_2025_09_16_git' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'e<G&Mqr;KhnoVS-GuO3%Y^_Y1D]<AsC[+TaTQMUnKs(y#jlzxH&5NI;,+lw;;Y0,' );
define( 'SECURE_AUTH_KEY',  'r_.4+[3)2}U4n;[+K jJR{S:S1g9WxAZ.KqLsp;1UC~*|ciH pHgunMZ6qq;ZX1)' );
define( 'LOGGED_IN_KEY',    'Kwm:h+SOSe0TR= ?^tp^1#7dHk-.k9_ruO?%#75r|oc<o!y:10k?>}3O]FrM5BDs' );
define( 'NONCE_KEY',        '#/%3_q:HGkF2*$z<0[}F:Y1aroAoF~]JDOR5F#qp2Emqp$AqJU09qIVJe]Z`YDKR' );
define( 'AUTH_SALT',        ';Fc31FO7=,:1-{iBIil<Wojxe.<,0I%UD&}xyI=!{S70>k8?[AGbPg`.;Cqc0Y6D' );
define( 'SECURE_AUTH_SALT', '5J?{h[U{}y.vdsAsnlWq8$5$<])FlQ4L]S5x`QHT1ptapw9[mOK]UUK,ge^NB&8#' );
define( 'LOGGED_IN_SALT',   '#$P:hD 2h=&:Cu.5&;tp4Wo~;9kdoAPbA0`f1S>BD#*}R`_4ft^)gVgftiI[K3I.' );
define( 'NONCE_SALT',       '@v_!`;+]h^$AJV)g2[^HN%HVe1(lC5&,&n1zvo7Lt#(]l`: Vub#Z[-T]Y%>e]+T' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
