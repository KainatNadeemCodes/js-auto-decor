<?php
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          '{/MDWQMQF;:rPQM7;/:Ou_fBiezxmS(`7a]Xdl%~Ejh.mDbT#R49Y!lW@y_u@~]8' );
define( 'SECURE_AUTH_KEY',   '}:hv2CCfX:|EwekW*p-zK_[nSJsx;%kC@hf7m:(8F-ef[DC0=D$sgSm(q!,xCw.F' );
define( 'LOGGED_IN_KEY',     'u]2j8iU]=ITLAf4oRLvM6[A#3am*q!%T`EqSG6%JttzYD1#**L0sXwIfObSq%uRp' );
define( 'NONCE_KEY',         '%kq#Y^ @<B+}fTL1NVz:(k@C1MR$A1`Mle5]r@RhR`O*0?C>oO1bRsTn[N[PqIJF' );
define( 'AUTH_SALT',         '/elrupAvkLVxAB6jE1nn)8Ozxr,k1C@xAh.ut]3^Ms16I[n:$rAz]^E+aM/7C?:$' );
define( 'SECURE_AUTH_SALT',  '5bw$,dG2jO<s#bJnH9Lk >FE#j&2B;2:V?zvx,(,(849S?UExRO?p`%^**{bXfP1' );
define( 'LOGGED_IN_SALT',    '5q(RbaOF-.M*Dk284oapK_+`8HLIVvC[06Pl<&}?.GqGQc&f*LJ@~,qXyfl@m+*$' );
define( 'NONCE_SALT',        '<7& Zz7:9_:B(#^HX>`xa=^sD K+[mSB-l?a:ezO/}K{BgHH=*%YDHb_PDGWG.M,' );
define( 'WP_CACHE_KEY_SALT', 'oZh?{dxjAxI7Cl,@ 7|TN{cNPiu/l0&-sdbc&t|$%f>yZ{^lV#x_VYM8xz-W%ffD' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
