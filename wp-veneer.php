<?php
/**
 * Plugin Name: WP-Veneer
 * Plugin URI: http://usabilitydynamics.com/
 * Description: Veneer.io Helper.
 * Version: 0.2.0
 * Author: Usability Dynamics
 * Author URI: http://usabilitydynamics.com/
 * License: GPLv2 or later
 * Veneer: True
 *
 * The Loder class is self-initializing.
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

// Include bootstrap.
include_once( __DIR__ . '/lib/class-bootstrap.php' );

// Initialize.
new UsabilityDynamics\Veneer\Bootstrap();