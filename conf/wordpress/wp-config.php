<?php


// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'softcatala_local');

/** MySQL database username */
define('DB_USER', 'softcatala_local');

/** MySQL database password */
define('DB_PASSWORD', 'tx0w4B3JT1QGPMO');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');



define('AUTH_KEY',         '0z<j0E,,mQ~q9RjtCyI71vu(*uI#Dvvh2WRoUrEss%ehO]V|srG#)skrgYDw0`!F');
define('SECURE_AUTH_KEY',  '#DFn{#}|lsnCoR?j0.20+1%qyH,it+Ii[:&.L[rU<|0@i)in*>W8AIprcx-MhR=2');
define('LOGGED_IN_KEY',    'nisNSyA4^T,bA(y,$N_fS&v(;f?,2aZc4[F]AEXHtcH6*Pe+u/#{6$5=dnV/[GT ');
define('NONCE_KEY',        'W+eH[4TArq@DKB]@Ye-M;-TE[iku|X_uWmIj|a%|>;0x2[g7&<9e)z{;-2KKU&;h');
define('AUTH_SALT',        'd+d  TI?CE4%sHVw+tvK++IHj~TX><U ,+-PB7e(>eR6z{4/.w;hk:9iVUL!Tdjb');
define('SECURE_AUTH_SALT', '.#qF1q#~0r;DAP04?&S?a$r]h3P+,13x_!hGi;&O~bKO!`K0DnC=qfqiToX*0Ytt');
define('LOGGED_IN_SALT',   ':58ygjxjVl/2NkioIyP)SKD#goO.M[-y{`F!~8UxPE`mfGL_5>r| 9/17+2 [/8J');
define('NONCE_SALT',       'O_k*ahLq!X<w@.*!}b zt@NfMcjav7}t<S!VEp@k_QKty1>+#dlO,(h:|v$N_eJv');


$table_prefix = 'wp_';


define('WP_DEBUG', false); 



/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Plugin, Uploads and Theme directories **/
define( 'WP_PLUGIN_DIR', ABSPATH . '../../htdocs/plugins' );
define( 'UPLOADS', ABSPATH . 'uploads' );
define( 'PLUGINDIR', ABSPATH . '../../htdocs/plugins' );

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

