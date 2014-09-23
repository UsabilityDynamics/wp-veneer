<?php
/**
 * Utility Class
 *
 * @module Veneer
 * @author potanin@UD
 */
namespace UsabilityDynamics\Veneer {

  if( !class_exists( 'UsabilityDynamics\Veneer\Utility' ) ) {

    /**
     * Class Utility
     *
     * @module Veneer
     */
    class Utility extends \UsabilityDynamics\Utility {

	    /**
	     *
	     *
	     * @todo Add filter;
	     * @todo Cache lookup.
	     *
	     * @param string $basePath
	     *
	     * @return mixed
	     *
	     */
	    static function get_deployment_branch( $basePath = ABSPATH ) {

		    try {

			    $stringfromfile = file( $basePath . '.git/HEAD' );
			    $stringfromfile = $stringfromfile[0];
			    $explodedstring = explode("/", $stringfromfile);
			    $branchname = $explodedstring[2];

		    } catch( \Exception $error ) {}

		    if( isset( $branchname  ) ) {
			    return  $branchname;
		    }

	    }

    }

  }

}