<?php
/**
 * REST API Controller
 *
 * @module Veneer
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\API' ) ) {

    /**
     * Class API
     *
     * @module Veneer
     */
    class API extends \UsabilityDynamics\API {

      /**
       * API Namespace
       *
       * @public
       * @static
       * @property $namespace
       * @type {String}
       */
      public static $namespace = 'wp-veneer';

    }

  }

}