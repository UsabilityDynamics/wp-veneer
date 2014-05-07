<?php
/**
 * Configuration Loader.
 *
 */

foreach( $_composer = (array) json_decode( file_get_contents( $_SERVER[ 'DOCUMENT_ROOT' ] . '/composer.json' ) )->settings as $key => $value ) {
  define( strtoupper( $key ), strpos( $value, '/' ) === 0 && realpath( $_SERVER[ 'DOCUMENT_ROOT' ] . $value ) ? realpath( $_SERVER[ 'DOCUMENT_ROOT' ] . $value ) : $value );
}

$table_prefix = defined( 'DB_PREFIX' ) ? DB_PREFIX : 'wp_';

require_once( ABSPATH . '/wp-settings.php' );