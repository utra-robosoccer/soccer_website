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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'utrah400_wp323');

/** MySQL database username */
define('DB_USER', 'utrah400_wp323');

/** MySQL database password */
define('DB_PASSWORD', '3W@68@S4Pp');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'u03ay5xlfkonazxn48bpkkhvv3zvxfkg944kwjdgluabvellzfmaggedqevdiexh');
define('SECURE_AUTH_KEY',  'usp1udli472tatcl1tlf9fwzbxydft8bxfb6j36rt7qzx1u0jizdc9qff854bwch');
define('LOGGED_IN_KEY',    'wcqldssvujvrma5qscenatmef7x1bd28xihx39n8wkh0ggjfdz6gay1ddkgk7tpp');
define('NONCE_KEY',        'adjnvej7wro7jehspj7jyz6xlcgn1jdiyskbzgxwsmsndfi1djsja6n3gelsr821');
define('AUTH_SALT',        'xegxjocnnar9nsg3iczd2jv1odscevc9swgcmebdxxjb4oqnkarwlmfdkt1pwis2');
define('SECURE_AUTH_SALT', 'v8ypjmz10lqlyid7ladoywx0rbhhpm7lgq3cvh2lhjh02b4kkfndvpdf9bzjqvp9');
define('LOGGED_IN_SALT',   'ypcnymnsgaztkmsythhcafthig4clqcz6xobkddble3jw1nw1m1oi6lgxav5bq3d');
define('NONCE_SALT',       'pmtd9ejjkkc6jphtbgygpn9gjpoiq54vqauhfsjoz7iav9zjxwjsragled38ip4h');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpgn_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
