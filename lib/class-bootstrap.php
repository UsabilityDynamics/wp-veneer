<?php
/**
 * UsabilityDynamics\Veneer Bootstrap
 *
 * ### Options
 * * minification.enabled
 * * cache.enabled
 * * offload.scripts
 *
 *
 * @verison 0.5.1
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
      public static $version = '0.5.1';

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
       * CloudFront
       *
       * @property
       * @type {Object}
       */
      private $_cloud = null;

      /**
       *
       *
       * @property
       * @type {Object}
       */
      private $_search = null;

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
        $wp_veneer = self::$instance = &$this;

//        if( !isset( $wp_cluster ) )  {
//          _doing_it_wrong( 'UsabilityDynamics\Veneer\Bootstrap::__construct', 'Veneer should not be initialized until after WP-Cluster.', '0.5.1' );
//        }

        // Set Properties.
        $this->site    = $wpdb->get_var( "SELECT domain FROM {$wpdb->blogs} WHERE blog_id = '{$wpdb->blogid}' LIMIT 1" );
        $this->network = $wpdb->get_var( "SELECT domain FROM {$wpdb->site} WHERE id = {$wpdb->siteid}" );
        $this->cluster = WP_BASE_DOMAIN;
        $this->site_id = $wpdb->blogid;

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'get_header', array( $this, 'get_header' ) );
        add_action( 'wp_head', array( $this, 'wp_head' ), 0, 200 );

        // Initialize Settings.
        $this->_settings();

        // Initialize Components.
        $this->_components();

        // Initialize Interfaces.
        $this->_interfaces();

        // Init Search
        $this->_search();

        // Create Public and Cache directories. Media directory created in Media class.
        if( defined( 'WP_VENEER_STORAGE' ) && WP_VENEER_STORAGE && is_dir( WP_CONTENT_DIR ) ) {

          $this->set( 'cache.path',  trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . trailingslashit( $this->site ) . 'cache' );
          $this->set( 'assets.path',  trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . trailingslashit( $this->site ) . 'assets' );
          $this->set( 'public.path',  trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . trailingslashit( $this->site ) . 'public' );

          // Path to static cache directory, e.g. /static/storage/my-site.com/cache
          if( wp_mkdir_p( $this->get( 'cache.path' ) ) ) {
            $this->set( 'cache.available', true );
          }

          if( wp_mkdir_p( $this->get( 'assets.path' ) ) ) {
            $this->set( 'assets.available', true );
          }

          if( wp_mkdir_p( $this->get( 'public.path' ) ) ) {
            $this->set( 'public.available', true );
          }

        }

        $this->set( 'assets.enabled', true );
        $this->set( 'public.enabled', true );
        $this->set( 'minification.enabled', false ); // @temp disabled
        $this->set( 'cache.enabled', false ); // @temp disabled
        $this->set( 'offload.scripts', false ); // @temp disabled

        ob_start( array( $this, 'ob_start' ) );

      }

      /**
       * Set Headers
       *
       * @todo Etag should be a lot more sophistiacted and take into account actual content changes.
       *
       * @author potanin@UD
       */
      public function wp_head() {

        $modified_since = ( isset( $_SERVER[ "HTTP_IF_MODIFIED_SINCE" ] ) ? strtotime( $_SERVER[ "HTTP_IF_MODIFIED_SINCE" ] ) : false );
        $etagHeader     = ( isset( $_SERVER[ "HTTP_IF_NONE_MATCH" ] ) ? trim( $_SERVER[ "HTTP_IF_NONE_MATCH" ] ) : false );

        $meta = array(
          'etag' => md5( time() ),
          'x-server' => 'wp-veneer/v' . self::$version,
          'public' => 'true',
          'cache-control' => 'max-age=3600, must-revalidate',
          'last-modified' => gmdate( "D, d M Y H:i:s", time() )." GMT"
        );

        foreach( (array) $meta as $key => $value ) {
          //printf( "\n\t" . '<meta http-equiv="%s" content="%s" />', $key, $value );
        }

      }

      /**
       * Handle Caching and Minification
       *
       * @todo Add logging.
       *
       * @mehod cache
       * @author potanin@UD
       */
      public function ob_start( &$buffer ) {
        global $post, $wp_query;

        // Will extract all JavaScript from page. 
        if( $this->get( 'offload.scripts' ) && class_exists( 'phpQuery' ) ) {

          $doc = \phpQuery::newDocumentHTML( $buffer );
          $scripts = pq( 'script:not([pagespeed_no_defer])' );

          $_output = array();

          foreach( $scripts as $script ) {
            $_output[] = $script;
            // @todo Write extracted Scripts to an /asset file to be served.
          }

          // Remove all found <script> tags.
          $scripts->remove();

          // Return HTML without tags.
          return $doc->document->saveHTML();

        }

        // Remove W3 Total Cache generic text.
        $buffer = str_replace( "Performance optimized by W3 Total Cache. Learn more: http://www.w3-edge.com/wordpress-plugins/", 'Served from', $buffer );
        $buffer = str_replace( "\n\r\n Served from:", '', $buffer );
        $buffer = str_replace( 'by W3 Total Cache ', '', $buffer );

        if( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
          return $buffer;
        }

        if( $_GET[ 'doing_wp_cron' ] ) {
          return $buffer;
        }

        if( is_search() ) {
          return $buffer;
        }

        if( is_404() ) {
          return $buffer;
        }

        if( is_attachment() ) {
          return $buffer;
        }

        // Bypass non-get requests.
        if( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET'  ) {
          return $buffer;
        }

        if( defined( 'DOING_CRON' ) && DOING_CRON ) {
          return $buffer;
        }

        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
          return $buffer;
        }

        if( $this->get( 'minification.enabled' ) ) {
          $buffer = Cache::minify( $buffer );
        }

        if( $this->get( 'cache.enabled' ) && $this->get( 'cache.available' ) && $this->get( 'cache.path' ) ) {

          $_info = pathinfo( $_SERVER[ 'REQUEST_URI' ] );

          $_parts = array(
            untrailingslashit( $this->get( 'cache.path' ) ),
            trailingslashit( $_info[ 'dirname' ] ),
            $_info[ 'filename' ] ? $_info[ 'filename' ] : 'index',
            in_array( $_info[ 'extension' ], array( 'html', 'htm' ) ) ? $_info[ 'extension' ] : '.html'
          );

          $_path = implode( '', $_parts );

          if( !wp_mkdir_p( dirname( $_path ) ) ) {
            return $buffer;
          }

          // Don't cache blank results.
          if( !trim( $buffer ) ) {
            return $buffer;
          }

          // @todo Single post object detected.
          // if( is_single() ) { }

          // Write Cached Page.
          file_put_contents( $_path, $buffer );

        }

        return $buffer;

      }

      /**
       * Minify Output.
       */
      public function enqueue_scripts() {

        if( is_user_logged_in() ) {
          wp_enqueue_style( 'veneer', plugins_url( '/styles/veneer.css', dirname( __DIR__ ) ) );
        };

      }

      /**
       *
       */
      public function _search() {

        /*
          $this->_search = new Search( array(
              'host' => '91.240.22.17',
              'port' => 9200
          ) );
        //*/

//        $elasticaIndex = $this->_search->getIndex('twitter');
//        $elasticaType = $elasticaIndex->getType('tweet');
//
//        // The Id of the document
//        $id = rand(1, 9999999);
//
//        // Create a document
//        $tweet = array(
//            'id'      => $id,
//            'user'    => array(
//                'name'      => 'mewantcookie',
//                'fullName'  => 'Cookie Monster'
//            ),
//            'msg'     => 'Me wish there were expression for cookies like there is for apples. "A cookie a day make the doctor diagnose you with diabetes" not catchy.',
//            'tstamp'  => time(),
//            'location'=> '41.12,-71.34',
//            '_boost'  => 1.0,
//            'terms' => array(
//                'f', 'g', 'e'
//            )
//        );
//        // First parameter is the id of document.
//        $tweetDocument = new \Elastica\Document($id, $tweet);
//
//        echo '<pre>';
//        print_r( $elasticaType->addDocument($tweetDocument) );
//        echo '</pre>';
//
//        // Refresh Index
//        $elasticaType->getIndex()->refresh();
      }

      /**
       * Initialize Settings.
       *
       */
      private function _settings() {

        // Initialize Settings.
        $this->_settings = new \UsabilityDynamics\Settings( array(
          "store" => "options",
          "key"   => "ud:veneer",
        ) );

        // ElasticSearch Service Settings.
        $this->set( 'documents', array(
          "active" => true,
          "host"   => "localhost",
          "port"   => 9200,
          "token"  => null,
        ) );

        // Varnish Service Settings.
        $this->set( 'varnish', array(
          "active" => false,
          "host"   => "localhost",
          "key"    => null
        ) );

        // CDN Service Settings.
        $this->set( 'media', array(
          "subdomain" => "media",
          "cdn"       => array(
            "active"   => false,
            "provider" => "gcs",
            "key"      => null
          )
        ) );

        // Save Settings.
        $this->_settings->commit();

      }

      /**
       * Initialize Media, Varnish, etc.
       *
       */
      private function _components() {

        // Initialize W3 Total Cachen Handlers.
        if( class_exists( 'UsabilityDynamics\Veneer\W3' ) ) {
          $this->_cache = new W3( $this->get( 'cache' ) );
        }

        // Enable CDN Media.
        if( class_exists( 'UsabilityDynamics\Veneer\Media' ) ) {
        $this->_media = new Media( $this->get( 'media' ) );
        }

        // Enable Varnish.
        if( class_exists( 'UsabilityDynamics\Veneer\Varnish' ) ) {
          $this->_varnish = new Varnish( $this->get( 'varnish' ) );
        }

      }

      /**
       * Initialize Interface Compnents
       *
       */
      private function _interfaces() {

        // Render Toolbar.
        add_action( 'wp_before_admin_bar_render', array( &$this, 'toolbar' ), 10 );

        if( file_exists( WP_BASE_DIR . '/local-debug.php' ) || in_array( $_SERVER[ 'REMOTE_ADDR' ], array( '127.0.0.1', '10.0.0.1', '0.0.0.0' ) ) ) {
          add_action( 'wp_before_admin_bar_render', array( &$this, 'toolbar_local' ), 100 );
        }

      }

      public function toolbar_local() {
        global $wp_admin_bar;

        $wp_admin_bar->add_menu( array(
          'id'     => 'localhost',
          'parent' => 'top-secondary',
          'meta'   => array(
            'html'    => '<div class="veneer-toolbar-environment"></div>',
            'target'  => '',
            'onclick' => '',
            'title'   => __( 'Local' ),
            'class'   => 'veneer-toolbar-local'
          ),
          'title'  => __( 'Local' ),
        ) );

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
        ) );

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
        ) );

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
