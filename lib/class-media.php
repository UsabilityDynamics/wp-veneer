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
       * @property $url_rewrite
       * @type {Object}
       */
      public $url_rewrite = null;

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
          "url_rewrite"  => get_option( 'upload_url_path' )
        ));

        $this->site    = $wp_veneer->site;
        $this->site_id = $wp_veneer->site_id;
        $this->cluster   = WP_BASE_DOMAIN;
        $this->network   = $wpdb->get_var( "SELECT domain FROM {$wpdb->site} WHERE id = {$wpdb->siteid}" );

        if( $args->subdomain ) {
          $this->subdomain = $args->subdomain;
        }
        if( $args->url_rewrite ) {
          $this->url_rewrite = $args->url_rewrite;
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

        // die( json_encode( $this->_debug() ) );

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
       * @version 2.0.0
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

        if( defined( 'WP_VENEER_STORAGE' ) ) {
          $settings[ 'path' ]    = str_replace( '/blogs.dir/' . $this->site_id . '/files', '/' . WP_VENEER_STORAGE . '/' . $this->site . '/media', $settings[ 'path' ] );
          $settings[ 'basedir' ] = str_replace( '/blogs.dir/' . $this->site_id . '/files', '/' . WP_VENEER_STORAGE . '/' . $this->site . '/media', $settings[ 'basedir' ] );
          
          if( !$this->url_rewrite ) {
            $settings[ 'url' ] = str_replace( $this->site . '/files', $this->site . '/media', $settings[ 'url' ] );
            $settings[ 'baseurl' ] = str_replace( $this->site . '/files', $this->site . '/media', $settings[ 'baseurl' ] );
          }
          
          if( $this->url_rewrite ) {
            $_rewrite = untrailingslashit( $this->url_rewrite );
            $settings[ 'url' ] = str_replace( 'http://' . $this->site . '/files', $_rewrite, $settings[ 'url' ] );
            $settings[ 'baseurl' ] = str_replace( 'http://' . $this->site . '/files', $_rewrite, $settings[ 'baseurl' ] );
            
          }
          
        }
        
        // CDN Media Redirection.
        if( !$this->url_rewrite && $wp_veneer->get( 'media.cdn.active' ) ) {

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