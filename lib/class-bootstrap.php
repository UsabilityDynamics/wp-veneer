<?php
/**
 * UsabilityDynamics\Cluster Bootstrap
 *
 * @verison 0.4.1
 * @author potanin@UD
 * @namespace UsabilityDynamics\Cluster
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Bootstrap' ) ) {

    /**
     * Bootstrap Cluster
     *
     * @class Bootstrap
     * @author potanin@UD
     * @version 0.0.1
     */
    class Bootstrap {

      /**
       * Cluster core version.
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
       * Veneer Cache Instance.
       *
       * @public
       * @property $media
       * @type {Object}
       */
      public $cache;

      /**
       * Veneer Documents Instance.
       *
       * @public
       * @property $documents
       * @type {Object}
       */
      public $documents;

      /**
       * Veneer Log Instance.
       *
       * @public
       * @property $log
       * @type {Object}
       */
      public $log;

      /**
       * Veneer Media Instance
       *
       * @public
       * @property $media
       * @type {Object}
       */
      public $media;

      public $domain;

      /**
       * Veneer Security Instance
       *
       * @public
       * @property $security
       * @type {Object}
       */
      public $security;

      /**
       * Veneer Varnish Instance.
       *
       * @public
       * @property $varnish
       * @type {Object}
       */
      public $varnish;

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
        global $wp_veneer;

        // Save context reference.
        $wp_veneer = self::$instance = &$this;

        $this->media     = new Media();

        add_action( 'wp_before_admin_bar_render', array( $this, 'toolbar' ), 10 );

      }

      /**
       * Add Cluster Toolbar
       *
       * @todo Add some sort of vidual for Bootstrap::$version and hostname.
       *
       * @method cluster_toolbar
       * @for Boostrap
       */
      public function toolbar() {
        global $wp_admin_bar;

        $wp_admin_bar->add_menu( array(
            'id'   => 'veneer',
            'meta'   => array(
              'html'     => '<div class="veneer-toolbar-info"></div>',
              'target'   => '',
              'onclick'  => '',
              'title'    => 'Veneer',
              'tabindex' => 10,
              'class' => 'veneer-toolbar'
            ),
            'title' => 'Cluster',
            'href' => network_admin_url( 'admin.php?page=veneer' )
          )
        );

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'   => 'veneer-cdn',
          'meta' => array(),
          'title' => 'Media',
          'href' => network_admin_url( 'admin.php?page=veneer#panel=cdn' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'   => 'veneer-search',
          'meta' => array(),
          'title' => 'Search',
          'href' => network_admin_url( 'admin.php?page=veneer#panel=search' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'   => 'veneer-varnish',
          'meta' => array(),
          'title' => 'Speed',
          'href' => network_admin_url( 'admin.php?page=veneer#panel=varnish' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'   => 'veneer-api',
          'meta' => array(),
          'title' => 'API',
          'href' => network_admin_url( 'admin.php?page=veneer#panel=api' )
        ));

        $wp_admin_bar->add_menu( array(
          'parent' => 'veneer',
          'id'   => 'veneer-support',
          'meta' => array(),
          'title' => 'Support',
          'href' => network_admin_url( 'admin.php?page=veneer#panel=support' )
        ));

      }

      /**
       * Get Setting.
       *
       *    // Get Setting
       *    Cluster::get( 'my_key' )
       *
       * @method get
       *
       * @for Flawless
       * @author potanin@UD
       * @since 0.1.1
       */
      public static function get( $key, $default = null ) {

        // @temp
        if( $key === 'cdn.subdomain' ) {
          return 'media';
        }

        // @temp
        if( $key === 'cdn.active' ) {
         return false;
        }

        return self::$instance->_settings ? self::$instance->_settings->get( $key, $default ) : null;
      }

      /**
       * Set Setting.
       *
       * @usage
       *
       *    // Set Setting
       *    Cluster::set( 'my_key', 'my-value' )
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
       * Get the Cluster Singleton
       *
       * Concept based on the CodeIgniter get_instance() concept.
       *
       * @example
       *
       *      var settings = Cluster::get_instance()->Settings;
       *      var api = Cluster::$instance()->API;
       *
       * @static
       * @return object
       *
       * @method get_instance
       * @for Cluster
       */
      public static function &get_instance() {
        return self::$instance;
      }

    }

  }

}
