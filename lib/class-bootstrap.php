<?php
/**
 * UsabilityDynamics\Veneer Bootstrap
 *
 * ### Options
 * * cache.enabled
 * * scripts.outline.enabled
 *
 * * media.shard.enabled
 * * media.path.public
 * * media.shard.subdomain
 *
 * * assets.shard.enabled
 * * assets.shard.subdomain
 * * scripts.path.public
 * * html.minify.enabled
 * *
 *
 *
 * @verison 0.6.0
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
      public static $version = '0.6.0';

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
       * Site Root Domain
       *
       * Used as basis for media, assets, cdn subdomains.
       *
       * @public
       * @property $apex
       * @type {Object}
       */
      public $apex = null;

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
       * URL Rewrites
       *
       * @public
       * @property $_rewrites
       * @type {Object}
       */
      public $_rewrites = null;

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
        global $wpdb, $current_site, $current_blog, $wp_veneer, $wp_cluster;

        // Save context reference.
        $wp_veneer = self::$instance = &$this;

        if( did_action( 'init' ) ) {
          _doing_it_wrong( 'UsabilityDynamics\Veneer\Bootstrap::__construct', 'Veneer should not be initialized before "init" filter.', '0.6.0' );
        }

        // Requires $this->site to be defined, therefore being ignored on single-site installs.
        if( defined( 'MULTISITE' ) && MULTISITE ) {
          $this->site     = $wpdb->get_var( "SELECT domain FROM {$wpdb->blogs} WHERE blog_id = '{$wpdb->blogid}' LIMIT 1" );
          $this->network  = $wpdb->get_var( "SELECT domain FROM {$wpdb->site} WHERE id = {$wpdb->siteid}" );
        } else {
          $this->site     = str_replace( array( 'http://', 'https://' ), '', get_bloginfo( 'url' ) );
          $this->network  = str_replace( array( 'http://', 'https://' ), '', get_bloginfo( 'url' ) );
          $this->apex     = str_replace( array( 'http://', 'https://' ), '', get_bloginfo( 'url' ) );
        }

        $this->site_id = $wpdb->blogid;
        $this->apex    = isset( $current_blog->apex ) ? $current_blog->apex : $apex = str_replace( "www.", '', $this->site );

        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_head', array( $this, 'wp_head' ), 0, 200 );

        // Initialize Settings.
        $this->_settings();

        // Initialize Components.
        $this->_components();

        // Initialize Interfaces.
        $this->_interfaces();

        ob_start( array( $this, 'ob_start' ) );

        // Create Public and Cache directories. Media directory created in Media class.
        if( defined( 'WP_VENEER_STORAGE' ) && WP_VENEER_STORAGE && is_dir( WP_CONTENT_DIR ) ) {

          $this->set( 'media.path.disk',    trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'media' );
          $this->set( 'cache.path.disk',    trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'cache' );
          $this->set( 'assets.path.disk',   trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'assets' );
          $this->set( 'static.path.disk',   trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'static' );
          $this->set( 'cdn.path.disk',      trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'cdn' );
          $this->set( 'scripts.path.disk',  trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'assets/scripts' );
          $this->set( 'styles.path.disk',   trailingslashit( WP_CONTENT_DIR ) . trailingslashit( WP_VENEER_STORAGE ) . 'sites/' . trailingslashit( $this->site ) . 'assets/styles' );

          if( !wp_mkdir_p( $this->get( 'media.path.disk' ) ) ) {
            $this->set( 'media.available', false );
          }

          if( !wp_mkdir_p( $this->get( 'cache.path.disk' ) ) ) {
            $this->set( 'cache.available', false );
          }

          if( !wp_mkdir_p( $this->get( 'assets.path.disk' ) ) ) {
            $this->set( 'assets.available', false );
          }

          if( !wp_mkdir_p( $this->get( 'static.path.disk' ) ) ) {
            $this->set( 'static.available', false );
          }

          if( !wp_mkdir_p( $this->get( 'cdn.path.disk' ) ) ) {
            $this->set( 'cdn.available', false );
          }

        }


      }

      /**
       *
       */
      public function _admin_menu() {
        global $menu, $submenu;

        // Site Only.
        if( current_filter() === 'admin_menu' ) {

          // Remove Native Site Sections.
          // remove_submenu_page( 'index.php', 'my-sites.php' );

          // Add Network Administration.
          add_options_page( __( 'Services', self::$text_domain ), __( 'Services', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'site_settings' ) );
          add_options_page( __( 'CDN', self::$text_domain ), __( 'CDN', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'site_settings' ) );

        }

        // Network Only.
        if( current_filter() === 'network_admin_menu' ) {
          // Remove Native Network Settings.
          // remove_menu_page( 'sites.php' );
        }

        // Add Network Administration to Network and Site.
        add_submenu_page( 'settings.php', __( 'API Settings', self::$text_domain ), __( 'API Settings', self::$text_domain ), 'manage_network', 'network-dns', array( $this, 'network_settings' ) );
        add_submenu_page( 'index.php', __( 'Reports', self::$text_domain ), __( 'Reports', self::$text_domain ), 'manage_network', 'network-policy', array( $this, 'network_settings' ) );

      }

      /**
       * Site Settings
       *
       */
      public function site_settings() {

        if( file_exists( dirname( __DIR__ ) . '/views/settings-site.php' ) ) {
          include( dirname( __DIR__ ) . '/views/settings-site.php' );
        }

      }

      /**
       * Network Settings
       *
       */
      public function network_settings() {

        if( file_exists( dirname( __DIR__ ) . '/views/settings-network.php' ) ) {
          include( dirname( __DIR__ ) . '/views/settings-network.php' );
        }

      }
      /**
       * Outline Scripts and Styles.
       *
       * https://developers.google.com/speed/pagespeed/module/filter-js-outline
       *
       * @param null   $buffer
       * @param string $type
       *
       * @return string
       */
      public function _outline( $buffer = null, $type = 'script' ) {

        // Will extract all JavaScript from page.
        if( class_exists( 'phpQuery' ) ) {

          $doc = \phpQuery::newDocumentHTML( $buffer );

          if( function_exists( 'pq' ) ) {
            $scripts = pq( 'script:not([data-main])' );
          }

          $_output = array();

          // @todo Write extracted Scripts to an /asset file to be served.
          foreach( $scripts as $script ) {
            $_output[] = $script;
          }

          // Remove all found <script> tags.
          $scripts->remove();

          if( function_exists( 'is_user_logged_in' ) && !is_user_logged_in() ) {
            // @todo Write generated app.config.js file to to static /assets cache.
          }

          // Return HTML without tags.
          return $doc->document->saveHTML();

        }

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

        if( is_admin() ) {
          return $buffer;
        }


        // Remove W3 Total Cache generic text.
        $buffer = str_replace( "Performance optimized by W3 Total Cache. Learn more: http://www.w3-edge.com/wordpress-plugins/", 'Served from', $buffer );
        $buffer = str_replace( "\n\r\n Served from:", '', $buffer );
        $buffer = str_replace( 'by W3 Total Cache ', '', $buffer );

        // Never cached logged in users.
        if( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
          return $buffer;
        }

        // Ignore CRON requests.
        if( isset( $_GET[ 'doing_wp_cron' ] ) && $_GET[ 'doing_wp_cron' ] ) {
          return $buffer;
        }

        // Do not cache search results.
        if( is_search() ) {
          return $buffer;
        }

        // Ignore 404 pages.
        if( is_404() ) {
          return $buffer;
        }

        // Bail on Media and Assets.
        if( is_attachment() ) {
          return $buffer;
        }

        // Bypass non-get requests.
        if( $_SERVER[ 'REQUEST_METHOD' ] !== 'GET'  ) {
          return $buffer;
        }

        // Always bypass AJAX and CRON Requests.
        if( ( defined( 'DOING_CRON' ) && DOING_CRON ) && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
          return $buffer;
        }

        // Media Domain Sharding.
        if( $this->get( 'media.shard.enabled' ) ) {
          $buffer = str_replace( "//{$this->site}/" . $this->get( 'media.path.public' ) . "/",  "//" . $this->get( 'media.shard.subdomain' ) . ".{$this->apex}/", $buffer );
        }

        // Asset Domain Sharding.
        if( $this->get( 'assets.shard.enabled' ) ) {
          $buffer = str_replace( "//{$this->site}/" . $this->get( 'scripts.path.public' ) . "/", "//" . $this->get( 'assets.shard.subdomain' ) . ".{$this->apex}/", $buffer );
        }

        // Outline and AMD JavaScript Assets.
        if( $this->get( 'scripts.outline.enabled' ) && $_response = $this->_outline( $buffer, 'scripts' ) ) {
          $buffer = $_response;
        }

        // Minify HTML Output.
        if( $this->get( 'html.minify.enabled' ) ) {
          $buffer = Cache::minify_html( $buffer );
        }

        // Save static HTML cache.
        if( $this->get( 'cache.enabled' ) && $this->get( 'cache.available' ) && $this->get( 'cache.path.disk' ) ) {

          $_info = pathinfo( $_SERVER[ 'REQUEST_URI' ] );

          $_parts = array(
            untrailingslashit( $this->get( 'cache.path.disk' ) ),
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

      public function init() {

        // Only admin can see W3TC notices and errors
        // add_action('admin_notices', array( $this, 'admin_notices' ));
        // add_action('network_admin_notices', array( $this, 'admin_notices' ));

        add_action( 'admin_menu', array( $this, '_admin_menu' ), 8 );
        add_action( 'network_admin_menu', array( $this, '_admin_menu' ), 8 );

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
       * Initialize Settings.
       *
       */
      private function _settings() {

        // Initialize Settings.
        $this->_settings = new \UsabilityDynamics\Settings( array(
          "store" => "options",
          "key"   => "ud:veneer",
        ) );

        // $this->_settings->flush();

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
        ));

        // CDN Service Settings.
        $this->set( 'media', array(
          "relative"  => true
        ));

        // CDN Service Settings.
        $this->set( 'cdn', array(
          "provider"  => array(
            "active"   => false,
            "provider" => "cf",
            "key"      => null,
            "secret"   => null,
            "bucket"   => null
          )
        ));

        $this->set( 'static.enabled', true );
        $this->set( 'cdn.enabled', true );
        $this->set( 'cache.enabled', true );

        $this->set( 'media.shard.enabled', false );
        $this->set( 'scripts.shard.enabled', false );
        $this->set( 'styles.shard.enabled', false );

        $this->set( 'media.shard.subdomain', 'media' );
        $this->set( 'scripts.shard.subdomain', 'assets' );
        $this->set( 'styles.shard.subdomain', 'assets' );

        $this->set( 'media.path.public', '/media' );
        $this->set( 'scripts.path.public', '/assets/scripts' );
        $this->set( 'styles.path.public', '/assets/styles' );

        $this->set( 'html.minify.enabled', false );

        $this->set( 'scripts.outline.enabled', false );

        // die( '<pre>' . print_r( $this->get(), true ) . '</pre>' );

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
          $this->_cache =     new W3( $this->get( 'cache' ) );
        }

        // Enable CDN Media.
        if( class_exists( 'UsabilityDynamics\Veneer\Media' ) ) {
          $this->_media =     new Media( $this->get( 'media' ) );
        }

        // Enable Varnish.
        if( class_exists( 'UsabilityDynamics\Veneer\Varnish' ) ) {
          $this->_varnish =   new Varnish( $this->get( 'varnish' ) );
        }

        // Enable URL Rewrites.
        if( class_exists( 'UsabilityDynamics\Veneer\Rewrites' ) ) {
          $this->_rewrites =  new Rewrites( $this->get( 'rewrites' ) );
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

        if( !$this->get( 'toolbar.menu.enabled' ) ) {
          return;
        }

        $wp_admin_bar->add_menu( array(
          'id'    => 'veneer',
          'meta'  => array(
            'html'     => '<div class="veneer-toolbar-info"></div>',
            'target'   => '',
            'onclick'  => '',
            'title'    => 'Services',
            'tabindex' => 10,
            'class'    => 'veneer-toolbar'
          ),
          'title' => 'Services',
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
      public static function get( $key = null, $default = null ) {
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
