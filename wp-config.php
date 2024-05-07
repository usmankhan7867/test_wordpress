<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
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
define( 'DB_NAME', 'exampledb' );
/** Database username */
define( 'DB_USER', 'exampleuser' );
/** Database password */
define( 'DB_PASSWORD', 'examplepass' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
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
define( 'AUTH_KEY',         'a4dzmsozgqybx50mhfdnulpr2lcrfmtdqxpnv57ahqc8phv8pzecsjgxn93nkzs1' );
define( 'SECURE_AUTH_KEY',  '1esjsr6oyymrwvinvqqewva3qoegb44fuv8urlzmz9j4k5mppy8nlhleq0wodmzx' );
define( 'LOGGED_IN_KEY',    '5sfpv2ajeoshxgkgpqwdkvg9mlcjtfcspn9vaqxpw8u8qc0uexjymcj6wxedlnqf' );
define( 'NONCE_KEY',        'zn7s0mkzkyk3ivkli7luuy2s5wfmnraoodjzqhugtjahyxrikukoexf5xoasyhlk' );
define( 'AUTH_SALT',        't5krtefappa21rwjmslauunmvhz7cxa6gok8grnkcqh8zvxwuhqwgzjyztd1vdlm' );
define( 'SECURE_AUTH_SALT', 'kk7vkidwanhkxksab4yrt2aiirfote6tfseahpi414zqhnwsnizoqp5fjccoyvv9' );
define( 'LOGGED_IN_SALT',   'zahkjq4mup2j6x4xkzmrhiqn1u6koojrkm8kjrukuors2l0wt0wuzrs9eot4p2xf' );
define( 'NONCE_SALT',       '9tryfgb9cd8pjhxi9gcp1obzh8nilicgiazmhpi0yv8qa26vvedl4si7nhrpt8av' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpqj_';
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
define( 'WP_DEBUG', true );
/* Add any custom values between this line and the "stop editing" line. */
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
