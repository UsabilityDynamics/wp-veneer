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
       * Clean domain.
       *
       * @public
       * @static
       * @property $domain
       * @type {Object}
       */
      public $domain = null;

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
        global $wp_veneer;

        if( !defined( 'UPLOADBLOGSDIR' ) ) {
          wp_die( '<h1>Network Error</h1><p>Unable to instatiate media the UPLOADBLOGSDIR constant is not defined.</p>' );
        }

        // Extend Arguments with defaults.
        $args = Utility::parse_args( $args, array(
          "active" => true,
          "subdomain" => "media",
          "cdn" => array(),
          "url_base" => get_option( 'upload_url_path' )
        ));

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
        $wp_upload_dir    = wp_upload_dir();
        $this->directory = BLOGUPLOADDIR;
        $this->path      = $wp_upload_dir[ 'path' ];
        $this->url       = $wp_upload_dir[ 'url' ];
        $this->basedir   = $wp_upload_dir[ 'basedir' ];
        $this->baseurl   = $wp_upload_dir[ 'baseurl' ];
        $this->domain    = defined( 'WP_VENEER_DOMAIN_MEDIA' ) && WP_VENEER_DOMAIN_MEDIA ? null : $wp_upload_dir[ 'baseurl' ];

      }

      /**
       *
       * @todo Add hookin to override media path here to a subdomain.
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

        // If Currently on Network Main Site. (Which is unlikely).
        if( is_main_site() ) {
          $settings[ 'path' ]    = str_replace( '/uploads', '/' . UPLOADBLOGSDIR . '/' . $wp_veneer->site, $settings[ 'path' ] );
          $settings[ 'basedir' ] = str_replace( '/uploads', '/' . UPLOADBLOGSDIR . '/' . $wp_veneer->site, $settings[ 'basedir' ] );
          $settings[ 'baseurl' ] = str_replace( '/uploads', '/media/', $settings[ 'baseurl' ] );
          $settings[ 'url' ]     = str_replace( '/uploads', '/media', $settings[ 'url' ] );
        }

        // If On Standard Site.
        if( !is_main_site() ) {
          $settings[ 'baseurl' ] = ( is_ssl() ? 'https://' : 'http://' ) . untrailingslashit( $wp_veneer->site ) . '/media';
          $settings[ 'url' ]     = str_replace( '/files/', '/media/', $settings[ 'url' ] );
        }

        // Custom URL Path explicitly set.
        if( $this->url_base ) {
          $settings[ 'baseurl' ] = $this->url_base;
        }

        // CDN Media Redirection.
        if( !$this->url_base && $wp_veneer->get( 'media.cdn.active' ) ) {

          // Strip Media from Pathname.
          $settings[ 'baseurl' ] = str_replace( '/media', '', $settings[ 'baseurl' ] );
          $settings[ 'url' ] = str_replace( '/media', '', $settings[ 'url' ] );

          // Add media Subdomain.
          $settings[ 'baseurl' ] = str_replace( '://', '://' . $wp_veneer->get( 'media.subdomain' ) . '.', $settings[ 'baseurl' ] );
          $settings[ 'url' ] = str_replace( '://', '://' . $wp_veneer->get( 'media.subdomain' ) . '.', $settings[ 'url' ] );

        }

        return $settings;

      }

    }

  }

}