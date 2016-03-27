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
define('DB_NAME', 'waterstyDBdh055');

/** MySQL database username */
define('DB_USER', 'waterstyDBdh055');

/** MySQL database password */
define('DB_PASSWORD', '5lX4Ar117o');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

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
define('AUTH_KEY',         '^Bqyj3$.YfIu$jrBJ0cjQY^nvJQ4knUg>v$RYBnvcj4B,VcJRzkn8,4gNZ,rzN4ko');
define('SECURE_AUTH_KEY',  '-C[hO~[wGO5DoWd[5-OWDKsel5D#WhL-_tH]5iPa#tHT9lWe]2imX.xM2EmXix*');
define('LOGGED_IN_KEY',    '_2hS~]xHT9lWi2xPXDp+i6*]eLx.pAL2iPb<tIPAiuj3+QXEq+i7*bIQ$iuI7jU');
define('NONCE_KEY',        'ynB,3jQ$>uJQ7jUg0yNYFrck4$>cJv^nC,4kR@>vJ4krc|v!wh1C|ZhR@g9G1hkZ!');
define('AUTH_SALT',        '@Gs@k8!1hNV!oCO5gRd|s@S8kwh1-|ZGs~k9!1dKW_oDO1hpa#s~S9lwi29_WDP-h');
define('SECURE_AUTH_SALT', 'k>gNz|rG>8kRd[vKVCkwh1z|ZGs@k9!1dKV!oDO5lW![wKWDpah5-|aHt~l9_[eO-');
define('LOGGED_IN_SALT',   '@K5gS![-O5Gsd[9!WDO-h5D[ehS~lwL1ipa#t~SDpah6-#eLxipD#aiX_pEP2ipb#');
define('NONCE_SALT',       'j*XEQ$j7I<fMU^nyM3jrc<u^UBnyj3$>cJv^nB,4kR$>vJRBjVg0zNZFrck8!VgNz');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
