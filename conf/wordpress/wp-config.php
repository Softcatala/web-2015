<?php

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

/** Path to Database information file. */
include_once ABSPATH . "../../conf/wordpress/db.php";


$table_prefix = 'wp_';

define('WP_DEBUG', true);


/* That's all, stop editing! Happy blogging. */

/** Plugin, Uploads and Theme directories **/
define( 'WP_PLUGIN_DIR', ABSPATH . '../../htdocs/plugins' );
if(isset($_SERVER['HTTP_HOST'])) {
    define( 'WP_PLUGIN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/plugins' );
}
define( 'UPLOADS', '../uploads' );
define( 'PLUGINDIR', ABSPATH . '../../htdocs/plugins' );

/** Location of your WordPress configuration. */

require_once(ABSPATH . 'wp-settings.php');