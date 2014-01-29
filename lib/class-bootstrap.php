<?php
/**
 * UsabilityDynamics\Veneer Bootstrap
 *
 * @verison 0.4.1
 * @author potanin@UD
 * @namespace UsabilityDynamics\Veneer
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Bootstrap' ) ) {

    /**
     * Bootstrap Veneer
     *
     * @class Bootstrap
     * @author potanin@UD
     * @version 0.0.1
     */
    class Bootstrap {

      /**
       * Veneer core version.
       *
       * @static
       * @property $version
       * @type {Object}
       */
      public static $version = '0.1.0';

      /**
       * Textdomain String
       *
       * @public
       * @property text_domain
       * @var string
       */
      public static $text_domain = 'wp-veneer';

      /**
       * Site Domain
       *
       * @public
       * @property $site
       * @type {Object}
       */
      public $site = null;

      /**
       * Site ID
       *
       * @public
       * @property $site_id
       * @type {Integer}
       */
      public $site_id = null;

      /**
       * Network Domain
       *
       * @public
       * @property $network
       * @type {Object}
       */
      public $network = null;

      /**
       * Current Domain
       *
       * @public
       * @property $veneer
       * @type {Object}
       */
      public $veneer = null;

      /**
       * Veneer Cache Instance.
       *
       * @public
       * @property $_cache
       * @type {Object}
       */
      private $_cache;

      /**
       * Settings Instance.
       *
       * @property $_settings
       * @type {Object}
       */
      private $_settings;

      /**
       * Veneer Documents Instance.
       *
       * @property $_documents
       * @type {Object}
       */
      private $_documents;

      /**
       * Veneer Log Instance.
       *
       * @property $_log
       * @type {Object}
       */
      private $_log;

      /**
       * Veneer Media Instance
       *
       * @property $media
       * @type {Object}
       */
      private $_media = null;

      /**
       * Veneer Security Instance
       *
       * @property $_security
       * @type {Object}
       */
      private $_security = null;

      /**
       * Veneer Varnish Instance.
       *
       * @property $_varnish
       * @type {Object}
       */
      private $_varnish = null;

      /**
       * Singleton Instance Reference.
       *
       * @public
       * @static
       * @property $instance
       * @type {Object}
       */
      public static $instance = false;

      /**
       * Constructor.
       *
       * UsabilityDynamics components should be avialable.
       * - class_exists( '\UsabilityDynamics\API' );
       * - class_exists( '\UsabilityDynamics\Utility' );
       *
       * @for Loader
       * @method __construct
       */
      public function __construct() {
        global $wpdb, $current_site, $current_blog, $wp_veneer;

        // Save context reference.
        $wp_veneer = self::$instance = & $this;

        // Set Properties.
        $this->site    = $wpdb->get_var( "SELECT domain FROM {$wpdb->blogs} WHERE blog_id = '{$wpdb->blogid}' LIMIT 1" );
        $this->network = $wpdb->get_var( "SELECT domain FROM {$wpdb->site} WHERE id = {$wpdb->siteid}" );
        $this->cluster = WP_BASE_DOMAIN;
        $this->site_id = $wpdb->blogid;

        // Initialize Settings.
        $this->_settings();

        // Initialize Components.
        $this->_components();

        // Initialize Interfaces.
        $this->_interfaces();

        // Minify Output.
        ob_start( 'UsabilityDynamics\Veneer\Cache::minify' );

      }

      /**
       * Shutdown Request.
       *
       * add_action( 'shutdown', array( $this, '_shutdown' ) );
       *
       */
      public function _shutdown() {
        $headers = getallheaders();
      }

      /**
       * Initialize Settings.
       *
       */
      private function _settings() {

        // Initialize Settings.
        $this->_settings = new \UsabilityDynamics\Settings(array(
          "store" => "options",
          "key"   => "ud:veneer",
        ));

        // ElasticSearch Service Settings.
        $this->set( 'documents', array(
          "active" => true,
          "host"   => "localhost",
          "port"   => 9200,
          "token"  => null,
        ));

        // Varnish Service Settings.
        $this->set( 'varnish', array(
          "active" => false,
          "host"   => "localhost",
          "key" => null
        ));

        // CDN Service Settings.
        $this->set( 'media', array(
          "subdomain" => "media",
          "cdn"    => array(
            "active"    => false,
            "provider"  => "gcs",
            "key"       => null
          )
        ));

        // Save Settings.
        $this->_settings->commit();

      }

      /**
       * Initialize Media, Varnish, etc.
       *
       */
      private function _components() {

        // Enable CDN Media.
        $this->_media = new Media( $this->get( 'media' ) );

        // Enable Varnish.
        $this->_varnish = new Varnish($this->get( 'varnish' ));

      }

      /**
       * Initialize Interface Compnents
       *
       */
      private function _interfaces() {

        // Render Toolbar.
        add_action( 'wp_before_admin_bar_render', array( &$this, 'toolbar' ), 10 );

      }

      /**
       * Add Veneer Toolbar
       *
       * @method toolbar
       * @for Boostrap
       */
      public function toolbar() {
        global $wp_admin_bar;

        $wp_admin_bar->add_menu( array(
            'id'    => 'veneer',
            'meta'  => array(
              'html'     => '<div class="veneer-toolbar-info"></div>',
              'target'   => '',
              'onclick'  => '',
              'title'    => 'Veneer',
              'tabindex' => 10,
              'class'    => 'veneer-toolbar'
            ),
            'title' => 'Veneer',
            'href'  => network_admin_url( 'admin.php?page=veneer' )
          )
        );

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'     => 'veneer-cdn',
          'meta'   => array(),
          'title'  => 'Media',
          'href'   => network_admin_url( 'admin.php?page=veneer#panel=cdn' )
        ) );

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'     => 'veneer-search',
          'meta'   => array(),
          'title'  => 'Search',
          'href'   => network_admin_url( 'admin.php?page=veneer#panel=search' )
        ) );

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'     => 'veneer-varnish',
          'meta'   => array(),
          'title'  => 'Cache',
          'href'   => network_admin_url( 'admin.php?page=veneer#panel=varnish' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'     => 'veneer-api',
          'meta'   => array(),
          'title'  => 'API',
          'href'   => network_admin_url( 'admin.php?page=veneer#panel=api' )
        ) );

      }

      /**
       * Get Setting.
       *
       *    // Get Setting
       *    Veneer::get( 'my_key' )
       *
       * @method get
       *
       * @for Flawless
       * @author potanin@UD
       * @since 0.1.1
       */
      public static function get( $key, $default = null ) {
        return self::$instance->_settings ? self::$instance->_settings->get( $key, $default ) : null;
      }

      /**
       * Set Setting.
       *
       * @usage
       *
       *    // Set Setting
       *    Veneer::set( 'my_key', 'my-value' )
       *
       * @method get
       * @for Flawless
       *
       * @author potanin@UD
       * @since 0.1.1
       */
      public static function set( $key, $value = null ) {
        return self::$instance->_settings ? self::$instance->_settings->set( $key, $value ) : null;
      }

      /**
       * Get the Veneer Singleton
       *
       * Concept based on the CodeIgniter get_instance() concept.
       *
       * @example
       *
       *      var settings = Veneer::get_instance()->Settings;
       *      var api = Veneer::$instance()->API;
       *
       * @static
       * @return object
       *
       * @method get_instance
       * @for Veneer
       */
      public static function &get_instance() {
        return self::$instance;
      }

    }

  }

}
