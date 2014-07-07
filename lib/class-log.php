<?php
/**
 * Log Access Controller
 *
 * @module Veneer
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  use Monolog\Logger;
  use Monolog\Handler\StreamHandler;
  use Monolog\Handler\SyslogHandler;
  use Monolog\Formatter\LineFormatter;
  use Monolog\ErrorHandler;

  if( !class_exists( 'UsabilityDynamics\Cluster\Log' ) ) {
    /**
     * Class Log
     *
     * @module Cluster
     */
    class Log {

      /**
       * Declare some static constants
       */
      const CHANNEL  = 'veneer';

      /**
       *
       */
      const FACILITY = 'local6';

      /**
       * Holds our required Monolog objects
       */
      private $handler;

      /**
       * @var \Monolog\Logger
       */
      private $logger;

      /**
       * @var \Monolog\Formatter\LineFormatter
       */
      private $formatter;
      /**
       * @var string
       */
      private $guid;

      /**
       * The line format we'll be using
       */
      private $line_format = '%channel% - %datetime% - %level_name% - %message% - %extra% - %context%';

      /**
       * Initialize Log
       *
       * @param boolean $do_stuff If we should actually process (used by 'init')
       *
       * @returns Log $this
       */
      public function __construct( $do_stuff = true ) {
        global $current_blog;

        if( !$do_stuff || !defined( 'WP_LOGS_DIR' ) || !file_exists( rtrim( WP_LOGS_DIR, '/' ) . '/' . WP_LOGS_FILE ) ) {
          return $this;
        }

        // Verify Class Exists.
        if( !class_exists( 'Monolog\Logger' ) ) {
          return $this;
        }

        /** Setup the GUID */
        $this->guid = $this->create_guid();

        /** Setup the logger */
        $this->logger = new Logger( $this->guid );

        /** Build our line formatter */
        $this->line_format = ( is_object( $current_blog ) ? $current_blog->domain : null ) . ' - ' . $this->line_format . PHP_EOL;

        /** Setup the formatter */
        $this->formatter = new LineFormatter( $this->line_format );

        /** Add our handler */
        switch( true ) {
          case defined( 'WP_LOGS_HANDLER' ) && WP_LOGS_HANDLER == 'syslog':
            /** Syslog handler */
            $this->handler = new SyslogHandler( self::CHANNEL, self::FACILITY, Logger::DEBUG );
            break;
          case !defined( 'WP_LOGS_HANDLER' ):
          default:
            /** File handler */
            $this->handler = new StreamHandler( rtrim( WP_LOGS_DIR, '/' ) . '/' . WP_LOGS_FILE, Logger::DEBUG );
            break;
        }

        /** Implement the formatter */
        $this->handler->setFormatter( $this->formatter );

        /** Now, bring in our file handler */
        $this->logger->pushHandler( $this->handler );

        /** Register the new error handler */
        ErrorHandler::register( $this->logger );

        /** Default info */
        $this->logger->addDebug( 'GUID: ' . $this->guid );
        $this->logger->addDebug( 'Logging initialized...' );

        /** Return this */

        return $this;
      }

      /**
       * This function generates a guid for logging purposes
       *
       * @link http://php.net/manual/en/function.com-create-guid.php
       */
      function create_guid() {
        if( function_exists( 'com_create_guid' ) ) {
          return strtolower( com_create_guid() );
        } else {
          mt_srand( (double) microtime() * 10000 ); //optional for php 4.2.0 and up.
          $charid = strtoupper( md5( uniqid( rand(), true ) ) );
          $hyphen = chr( 45 );
          $uuid   = substr( $charid, 0, 8 ) . $hyphen . substr( $charid, 8, 4 ) . $hyphen . substr( $charid, 12, 4 ) . $hyphen . substr( $charid, 16, 4 ) . $hyphen . substr( $charid, 20, 12 );

          return strtolower( $uuid );
        }
      }

      /**
       * This function lets us chain methods without having to instantiate first, YOU MUST COPY THIS TO ALL SUB CLASSES
       */
      static public function init() {
        return new self( false );
      }

    }
  }

}