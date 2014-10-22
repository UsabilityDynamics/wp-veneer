<?php
/**
 * Plugin Name: WP-Veneer
 * Plugin URI: http://usabilitydynamics.com/
 * Description: Veneer.io Helper.
 * Version: 0.6.7
 * Author: Usability Dynamics
 * Author URI: http://usabilitydynamics.com/
 * License: GPLv2 or later
 * Network: True
 *
 * The Loader class is self-initializing.
 *
 * @example
 *
 *      // Get Settings Object
 *      UsabilityDynamics\Veneer::get_instance()->state->settings;
 *      UsabilityDynamics\Veneer::get_instance()->get()
 *
 * @namespace Veneer
 * @module Veneer
 */

if( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

// Include bootstrap.
if( !class_exists( 'UsabilityDynamics\Veneer\Bootstrap' ) ) {
  include_once( __DIR__ . '/lib/class-bootstrap.php' );
}

// Initialize.
new UsabilityDynamics\Veneer\Bootstrap;
