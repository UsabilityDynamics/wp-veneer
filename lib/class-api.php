<?php
/**
 * REST API Controller
 *
 * @module Veneer
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  use UsabilityDynamics\Utility;

  if( !class_exists( 'UsabilityDynamics\Veneer\API' ) ) {

    /**
     * Class API
     *
     * @module Veneer
     */
    class API {

      public static $_routes = Array();

      /**
       * Define API Endpoints.
       *
       *    Veneer\API::define( '/merchant-feed/google', array( 'CDO\Application\API\MerchantFeed', 'compute' ) )
       *
       * @param       $path
       * @param null  $handler
       * @param array $args
       *
       * @return array|void
       */
      public static function define( $path, $handler = null, $args = array() ) {

        $args = (object) Utility::extend( array(
          'method' => 'GET',
          'scopes' => array(),
          'parameters' => array()
        ), $args);

        if( !is_callable( $handler ) ) {
          return _doing_it_wrong( 'UsabilityDynamics\Veneer\API::define', 'Handler not callable.', null );
        }

        $_route = array(
          '_type' => 'route',
          'path' => $path,
          'method' => $args->method,
          'url' => add_query_arg( array( 'action' => $path ), admin_url( 'admin-ajax.php' ) ),
          'parameters' => $args->parameters,
          'scopes' => $args->scopes,
          'detail' => array(
            'handler' => is_array( $handler ) ? join( '::', $handler ) : $handler,
            'action' => current_action()
          )
        );

        add_action( 'wp_ajax_' . $path, $handler );
        add_action( 'wp_ajax_nopriv_' . $path, $handler );

        self::$_routes[] = $_route;

        return $_route;

      }

      /**
       * List Routes
       *
       * @param array $args
       *
       * @return array
       */
      public static function routes( $args = array() ) {

        // Filter.
        $args = (object) Utility::extend( array(), $args);

        return API::$_routes;

      }

      /**
       * Default Response Handler.
       *
       */
      public static function default_handler() {
        self::send( new \WP_Error( "API endpoint does not have a handler." ) );
      }

      /**
       * Send Response
       *
       * @todo Add content-type detection for XML response handling.
       *
       * @param       $data
       * @param array $headers
       *
       * @return bool
       */
      public static function send( $data, $headers = array() ) {

        nocache_headers();

        if( is_string( $data ) ) {
          return die( $data );
        }

        // Error Response.
        if( is_wp_error( $data ) ) {
          return wp_send_json(array(
            "ok" => false,
            "error" => $data
          ));
        }

        // Standard Object Response.
        if( ( is_object( $data ) || is_array( $data ) ) && !is_wp_error( $data ) ) {

          $data = (object) $data;

          if( !isset( $data->ok ) ) {
            $data = (object) ( array( 'ok' => true ) + (array) $data );
          }

          return wp_send_json( $data );
        }

      }

    }

  }

}