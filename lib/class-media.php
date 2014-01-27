<?php
/**
 * Media Access Controller
 *
 * @version 0.1.5
 * @module Cluster
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Media' ) ) {

    /**
     * Class Media
     *
     * @todo When CDN is disabled some images seem to use the network's domain as the path.
     *
     * @module Cluster
     */
    class Media {

      /**
       * Absolute path to site-specific file directory
       *
       * @public
       * @static
       * @property $path
       * @type {Object}
       */
      public $path = null;

      /**
       * Custom URL Base for all media.
       *
       * @public
       * @static
       * @property $url_base
       * @type {Object}
       */
      public $url_base = null;

      /**
       * Instance Domain.
       *
       * @public
       * @static
       * @property $site
       * @type {String}
       */
      public $site = null;

      /**
       * Instance Site ID.
       *
       * @public
       * @static
       * @property $site_id
       * @type {Integer}
       */
      public $site_id = null;

      /**
       * Media Active.
       *
       * @public
       * @property $active
       * @type {Boolean}
       */
      public $active = false;

      /**
       * Initialize Media
       *
       * @for Media
       */
      public function __construct( $args ) {
        global $wp_veneer, $wpdb;

        if( !defined( 'UPLOADBLOGSDIR' ) ) {
          wp_die( '<h1>Network Error</h1><p>Unable to instatiate media the UPLOADBLOGSDIR constant is not defined.</p>' );
        }

        // Extend Arguments with defaults.
        $args = \UsabilityDynamics\Utility::parse_args( $args, array(
          "active"    => true,
          "subdomain" => "media",
          "cdn"       => array(),
          "url_base"  => get_option( 'upload_url_path' )
        ));

        $this->site    = $wp_veneer->site;
        $this->site_id = $wp_veneer->site_id;
        $this->cluster   = WP_BASE_DOMAIN;
        $this->network   = $wpdb->get_var( "SELECT domain FROM {$wpdb->site} WHERE id = {$wpdb->siteid}" );

        if( $args->subdomain ) {
          $this->subdomain = $args->subdomain;
        }
        if( $args->url_base ) {
          $this->url_base = $args->url_base;
        }

        if( $args->cdn ) {
          $this->cdn = $args->cdn;
        }

        if( $args->active ) {
          $this->active = $args->active;
        }

        // Primary image path/url override.
        add_filter( 'upload_dir', array( &$this, 'upload_dir' ) );

        // Get media/upload vales. (wp_upload_dir() will generate directories).
        $wp_upload_dir   = wp_upload_dir();
        $this->directory = defined( 'BLOGUPLOADDIR' ) && BLOGUPLOADDIR ? BLOGUPLOADDIR : '';
        $this->path      = $wp_upload_dir[ 'path' ];
        $this->url       = $wp_upload_dir[ 'url' ];
        $this->basedir   = $wp_upload_dir[ 'basedir' ];
        $this->baseurl   = $wp_upload_dir[ 'baseurl' ];
        $this->domain    = defined( 'WP_VENEER_DOMAIN_MEDIA' ) && WP_VENEER_DOMAIN_MEDIA ? null : $wp_upload_dir[ 'baseurl' ];

        //die( '<pre>' . print_r( $this->_debug(), true ) . '</pre>' );

      }

      /**
       * Return URL Mapping Array
       *
       * @return array
       */
      private function _debug() {
        global $wp_veneer;

        return array(
          'wp_upload_dir' => wp_upload_dir(),
          'this'          => $this,
          'wp_veneer'     => $wp_veneer
        );

      }

      /**
       * Media Paths and URLs
       *
       * @param $settings
       * @param $settings .path
       * @param $settings .url
       * @param $settings .subdir
       * @param $settings .basedir
       * @param $settings .baseurl
       * @param $settings .error
       */
      public function upload_dir( $settings ) {
        global $wp_veneer;

        // $wp_veneer->set( 'media.cdn.active', true );

        // Fix Cluster / Network / Site domains.
        $settings[ 'url' ]     = $this->url_base ? $this->url_base : str_replace( $wp_veneer->cluster, $this->site, $settings[ 'url' ] );
        $settings[ 'baseurl' ] = $this->url_base ? $this->url_base : str_replace( $wp_veneer->cluster, $this->site, $settings[ 'url' ] );
        $settings[ 'path' ]    = str_replace( untrailingslashit( ABSPATH ), untrailingslashit( WP_BASE_DIR ), $settings[ 'path' ] );
        $settings[ 'basedir' ] = str_replace( untrailingslashit( ABSPATH ), untrailingslashit( WP_BASE_DIR ), $settings[ 'basedir' ] );

        // If Currently on Network Main Site, e.g. "UsabilityDynamics.com" or "DiscoDonniePresents.com"
        if( is_main_site() ) {

          if( strpos( $settings[ 'path' ], '/uploads/sites' ) ) {
            $settings[ 'path' ]    = str_replace( '/uploads/sites/' . $this->site_id , '/' . UPLOADBLOGSDIR . '/' . $this->site, $settings[ 'path' ] );
            $settings[ 'basedir' ] = str_replace( '/uploads/sites/' . $this->site_id , '/' . UPLOADBLOGSDIR . '/' . $this->site, $settings[ 'path' ] );
            $settings[ 'url' ]     = str_replace( '/uploads/sites/' . $this->site_id, '/media', $settings[ 'url' ] );
            $settings[ 'baseurl' ] = str_replace( '/uploads/sites/' . $this->site_id, '/media', $settings[ 'baseurl' ] );
          }

          if( strpos( $settings[ 'path' ], '/uploads' ) ) {
            $settings[ 'path' ]    = str_replace( '/uploads', '/static/storage/' . $this->site, $settings[ 'path' ] );
            $settings[ 'basedir' ] = str_replace( '/uploads', '/static/storage/' . $this->site, $settings[ 'basedir' ] );
            $settings[ 'url' ]     = str_replace( '/uploads', '/media', $settings[ 'url' ] );
          }

          $settings[ 'subdir' ]  = str_replace( '', '', $settings[ 'subdir' ] );
        }

        // If On Standard Site.
        if( !is_main_site() ) {

          if( strpos( $settings[ 'path' ], '/uploads/sites' ) ) {
            $settings[ 'path' ]    = str_replace( '/uploads/sites/' . $this->site_id, '/static/storage/' . $this->site, $settings[ 'path' ] );
            $settings[ 'basedir' ] = str_replace( '/uploads/sites/' . $this->site_id, '/static/storage/' . $this->site, $settings[ 'basedir' ] );
            $settings[ 'url' ]     = str_replace( '/uploads/sites/' . $this->site_id, '/media', $settings[ 'url' ] );
          }

          if( strpos( $settings[ 'path' ], '/sites' ) ) {
            $settings[ 'path' ]    = str_replace( '/sites/' . $this->site_id , '', $settings[ 'path' ] );
            $settings[ 'basedir' ] = str_replace( '/sites/' . $this->site_id , '', $settings[ 'basedir' ] );
            $settings[ 'url' ]     = str_replace( '/uploads/sites/' . $this->site_id, '/media', $settings[ 'url' ] );
          }

          if( strpos( $settings[ 'path' ], '/uploads' ) ) {
            $settings[ 'path' ]    = str_replace( '/uploads/sites/' . $this->site_id, '/static/storage/' . $this->site, $settings[ 'path' ] );
            $settings[ 'basedir' ] = str_replace( '/uploads/sites/' . $this->site_id, '/static/storage/' . $this->site, $settings[ 'basedir' ] );
            $settings[ 'url' ]     = str_replace( '/uploads/sites/' . $this->site_id, '/media', $settings[ 'url' ] );
          }

          $settings[ 'baseurl' ] = $this->url_base ? $this->url_base : ( is_ssl() ? 'https://' : 'http://' ) . untrailingslashit( $wp_veneer->site ) . '/media';
          $settings[ 'subdir' ]  = str_replace( '', '', $settings[ 'subdir' ] );
        }

        // Custom URL Path explicitly set.
        if( $this->url_base ) {
          $settings[ 'baseurl' ] = $this->url_base;
        }

        // CDN Media Redirection.
        if( !$this->url_base && $wp_veneer->get( 'media.cdn.active' ) ) {

          // Strip Media from Pathname.
          $settings[ 'baseurl' ] = str_replace( '/media', '', $settings[ 'baseurl' ] );
          $settings[ 'url' ]     = str_replace( '/media', '', $settings[ 'url' ] );

          // Add media Subdomain.
          $settings[ 'baseurl' ] = str_replace( '://', '://' . $wp_veneer->get( 'media.subdomain' ) . '.', $settings[ 'baseurl' ] );
          $settings[ 'url' ]     = str_replace( '://', '://' . $wp_veneer->get( 'media.subdomain' ) . '.', $settings[ 'url' ] );

        }

        return $settings;

      }

    }

  }

}