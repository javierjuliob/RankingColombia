<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '0000' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'vFzxE@|eM|5]a4&h;oJgu5;(VpTwK|3o4}$545Sy+=%i!+-#e^>!2_,>>5x-s`C>');
define('SECURE_AUTH_KEY',  '>d(!AiKFcKV+jOSL( 6+rzGL>(q6<ulKn:(Aq~_7#%4=sc0e|0rJFPdSjpxXnl2q');
define('LOGGED_IN_KEY',    'P3~-7xT-@3Z9ab)|QevWBqZ-Hh2O}R02ak/u|{6j-Lbs|E=6YN:44{+%.#)d8(9|');
define('NONCE_KEY',        'RZ#.?6Wte=X0gK+vy7p+djT!W@0+?SW.bo!/_l4ISWg`{I=HR|VVWF[&o)lcI$(@');
define('AUTH_SALT',        'HMiWgix@E?OL*)cp>#^^x+?DI92gHrnP`yuvQp#@9LkI<!O7Yf)O(|OM1--*@Ms^');
define('SECURE_AUTH_SALT', 'Rf(`<&#OP+|Iu:TV=)M1X[.c~{s87BsC_M&%WvRA@JZ9-.-tEko{CZX #ge949p*');
define('LOGGED_IN_SALT',   '.Hn{Xyiy=7+]}D<zpt)Wf7DG<J-=jTn4mz;|;Gn-K*3~?E[zr+6gy5P<3mmX^8:c');
define('NONCE_SALT',       'KHUpz7`uB9n4uX~*R*/RJ1&t<5-$2WvS8RWV_nXh;J*0:/bia_JF%d&P%_ZdTu(%');


/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';