<?php
/**
 * Our custom wp-config bootstrapper, it looks at the environment, and then loads config files
 * based on those environment variables - typically environment set in .htaccess, see
 * .htaccess.tpl
 *
 * Configs are loaded based on the following hierarchy, and you can do both folders and files:
 *  1) application/etc/wp-config/{ENVIRONMENT}/{FILE_NAME}
 *  2) application/etc/wp-config/{FILE_NAME}
 *  3) All items defined in composer.json, in the settings object key
 *
 * @author Reid Williams
 * @class UsabilityDynamics\Veneer\Config
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Config' ) ){
    class Config{

      /**
       * Holds the arrayed location of our config files/folders
       */
      private $config_folders = array();

      /**
       * This variable defines the config files which will be autoloaded in the class, can be a
       * directory (of which all files will be loaded), or a specific file - scope is defined
       * such that you might have to use $uds_config->get_config() if the variables aren't declared
       * globally
       */
      private $autoload_files = array(
        'g:system', /** Holds variables such as 'path' or 'web host name' declarations */
        'g:constants', /** Our defines file, should hold all static declarations, replacement of old wp-config.php */
        'g:database', /** Our database settings */
        'g:debug', /** Any debug file, looks for 'debug.php' - g: prefix makes it global */
        'options', /** Looking for some options definition files in a directory (scans all files) */
      );

      /**
       * This variable will hold the config files that have already been included
       */
      private $loaded = array();

      /**
       * This variable holds protected config variables (they cannot be defined in the config files)
       */
      private $protected_variables = array(
        'slug',
        'file'
      );

      /**
       * This function looks through the configuration options that are stored and returns them
       *
       * @param string $config The config we're trying to load
       * @param mixed $value Whether we want to get a specific value from this config, or the whole thing
       *
       * @return mixed False on failure, config array on success
       */
      function get_config( $config, $value = false ){
        if( isset( $this->loaded[ $config ] ) && is_array( $this->loaded[ $config ] ) && isset( $this->loaded[ $config ][ 'vars' ] ) ){
          if( is_string( $value ) && !empty( $value ) && isset( $this->loaded[ $config ][ 'vars' ][ $value ] ) ){
            return $this->loaded[ $config ][ 'vars' ][ $value ];
          }else{
            /** If there is only one item, return it directly */
            if( count( $this->loaded[ $config ][ 'vars' ] ) == 1 ){
              return array_pop( array_values( $this->loaded[ $config ][ 'vars' ] ) );
            }else{
              return $this->loaded[ $config ][ 'vars' ];
            }
          }
        }else{
          return false;
        }
      }

      /**
       * This function basically looks for a way to load the specific config files, by first looking in the
       * current environment's folder, and then looking into the base config folder afterwards
       *
       * @param string $file The file we want to include
       * @param string $scope The scope for the variables, globally or locally, defaults to 'local'
       */
      function load_config( $file, $scope = 'local' ){
        /** Ok, make sure our variables are good */
        if( !( is_string( $scope ) && $scope == 'global' ) ){
          $scope = 'local';
        }
        $files = array();
        /** Loop through our config folders, stopping at the first one we can find and include */
        foreach( $this->config_folders as $config_folder ){
          if( is_dir( $config_folder . $file ) ){
            // echo 'Directory: ' . $config_folder . $file . "\r\n";
            $config_folder = $config_folder . $file . DIRECTORY_SEPARATOR;
            /** Scan the directory */
            $possibles = scandir( $config_folder );
            /** Loop through the possibles and include them if you can */
            foreach( $possibles as $possible ){
              /** Skip root folders */
              if( $possible == '.' || $possible == '..' ){
                continue;
              }
              /** Remove the '.php' file from the name if it has it */
              if( substr( $possible, strlen( $possible ) - 4, 4 ) == '.php' ){
                $possible = substr( $possible, 0, strlen( $possible ) - 4 );
              }
              /** Remove the '.json' file from the name if it has it */
              if( substr( $possible, strlen( $possible ) - 5, 5 ) == '.json' ){
                $possible = substr( $possible, 0, strlen( $possible ) - 5 );
              }
              /** Ok, now call ourselves, so we'll recurse through directories */
              $this->load_config( $file . DIRECTORY_SEPARATOR . $possible, $scope );
            }
          }elseif( is_file( $config_folder . $file . '.php' ) ){
            // echo 'File: ' . $config_folder . $file . '.php' . "\r\n";
            /** Try to include the file in our exclusions list, if not already included */
            if( !isset( $files[ $file ] ) ){
              $files[ $file ] = array(
                'scope' => $scope,
                'file' => $config_folder . $file . '.php'
              );
            }
          }elseif( is_file( $config_folder . $file . '.json' ) ){
            // echo 'File: ' . $config_folder . $file . '.json' . "\r\n";
            /** Try to include the file in our exclusions list, if not already included */
            if( !isset( $files[ $file ] ) ){
              $files[ $file ] = array(
                'scope' => $scope,
                'file' => $config_folder . $file . '.json'
              );
            }
          }
        }
        /** If we have a files array that is not empty, go through and include them */
        if( is_array( $files ) && count( $files ) ){
          /** Go ahead and require the file */
          foreach( $files as $slug => $file ){
            /** Ok, call our function (so we don't have to do a bunch of unsets) */
            $this->_try_load_config_file( $slug, $file );
          }
        }
      }

      /**
       * This function actually does the requiring
       *
       * @param string $slug File's slug to store
       * @param array $file File definition array as done in 'load_config'
       */
      function _try_load_config_file( $slug, $file ){
        if( !in_array( $slug, array_keys( $this->loaded ) ) ){
          /** Now, require the file, base on the type it is */
          if( substr( $file[ 'file' ], strlen( $file[ 'file' ] ) - 4, 4 ) == '.php' ){
            require_once( $file[ 'file' ] );
            $file[ 'vars' ] = get_defined_vars();
          }elseif( substr( $file[ 'file' ], strlen( $file[ 'file' ] ) - 5, 5 ) == '.json' ){
            $file[ 'vars' ] = json_decode( file_get_contents( $file[ 'file' ] ), true );
            /** Loop through the items, and if they prefix with 'c:', they should be defined constants */
            foreach( $file[ 'vars' ] as $key => $value ){
              if( substr( $key, 0, 2 ) == 'c:' ){
                /** Let's go ahead and unset the key */
                unset( $file[ 'vars' ][ $key ] );
                /** Set the constant */
                define( substr( $key, 2, strlen( $key ) - 2 ), $value );
              }
            }
          }
          /** Go through and unset the protected variables */
          foreach( $this->protected_variables as $protected_variable ){
            if( isset( $file[ 'vars' ][ $protected_variable ] ) ){
              unset( $file[ 'vars' ][ $protected_variable ] );
            }
          }
          /** Now, determine what to do with the vars */
          if( isset( $file[ 'scope' ] ) && $file[ 'scope' ] == 'global' ){
            foreach( $file[ 'vars' ] as $key => $value ){
              $GLOBALS[ $key ] = $value;
            }
          }
          /** No, add it to our loaded array */
          $this->loaded[ $slug ] = $file;
        }
      }

      /**
       * On init, we're just going to setup and include all our config files
       * @param string $base_dir Override the base dir to search for files (defaults to __DIR__)
       * @param bool $do_stuff Whether we should actually do initialization( needed for 'init' )
       */
      function __construct( $base_dir = __DIR__, $do_stuff = true ){
        if( !( is_bool( $do_stuff ) && $do_stuff ) ){
          return;
        }

        /** Set some local variables */
        $base_dir = dirname( dirname( dirname( dirname( $base_dir ) ) ) );

        /** Bring in our local-debug file if we have it */
        if( is_file( $base_dir . '/local-debug.php' ) ){
          require_once( $base_dir . '/local-debug.php' );
        }

        /** Bring in our environment file if we need to */
        if( !defined( 'ENVIRONMENT' ) && is_file( $base_dir . '/.environment' ) ){
          $environment = @file_get_contents( '.environment' );
          define( 'ENVIRONMENT', trim( $environment ) );
        }

        /** For these variables, make sure they exist */
        $this->config_folders[] = rtrim( $base_dir, '/' ) . '/application/etc/wp-config/' . ENVIRONMENT . '/';
        $this->config_folders[] = rtrim( $base_dir, '/' ) . '/application/etc/wp-config/';
        foreach( $this->config_folders as $key => $value ){
          if( !is_dir( $value ) ){
            unset( $this->config_folders[ $key ] );
          }
        }
        /** Renumber the array */
        $this->config_folders = array_values( $this->config_folders );
        /** If we don't have any config folders, bail */
        if( !( is_array( $this->config_folders ) && !count( $this->config_folders ) ) ){
          /** Now, go through our autoloaded configs, and bring them in */
          foreach( $this->autoload_files as $autoload_file ){
            /** See if it needs to be global or local */
            if( substr( $autoload_file, 0, 2 ) == 'g:' ){
              $autoload_scope = 'global';
              $autoload_file = substr( $autoload_file, 2, strlen( $autoload_file ) - 2 );
            }else{
              $autoload_scope = 'local';
            }
            /** Include the files then */
            $this->load_config( $autoload_file, $autoload_scope );
          }
        }
        /** Finally, go through the composer.json file and add all the configs there */
        foreach( $_composer = (array) json_decode( file_get_contents( $_SERVER[ 'DOCUMENT_ROOT' ] . '/composer.json' ) )->settings as $key => $value ) {
          if( !defined( strtoupper( $key ) ) ){
            define( strtoupper( $key ), strpos( $value, '/' ) === 0 && realpath( $_SERVER[ 'DOCUMENT_ROOT' ] . $value ) ? realpath( $_SERVER[ 'DOCUMENT_ROOT' ] . $value ) : $value );
          }
        }
        /** Return this own object */
        return $this;
      }

      /**
       * This function lets us chain methods without having to instantiate first, YOU MUST COPY THIS TO ALL SUB CLASSES
       */
      static public function init(){
        return new self( __DIR__, false );
      }

    }
  }

  /**
   * If we don't have the following defined, we should assume that we're directly including this file,
   * so we should initialize it
   */
  if( !defined( 'WP_BASE_DOMAIN' ) && !defined( 'WP_DEBUG' ) && !defined( 'AUTH_KEY' ) ){
    global $wp_veneer;
    /** Init our config object */
    if( !is_object( $wp_veneer ) ){
      $wp_veneer = new \stdClass();
    }
    /** Add to our object, if we don't have the config object */
    if( !isset( $wp_veneer->config ) ){
      $wp_veneer->config = new Config();
    }
    /** Is this needed? */
    $table_prefix = defined( 'DB_PREFIX' ) ? DB_PREFIX : 'wp_';
    /** Now that we've done that, lets include our wp settings file, as per normal operations */
    require_once( ABSPATH . '/wp-settings.php' );
  }

}