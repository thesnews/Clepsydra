<?php
/*
 File: common
  Provides \foundry\middleware\common class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\middleware;

use foundry\middleware\middlewareInterface as MiddlewareInterface;
use foundry\response as Response;

/*
 Class: common
  The common middleware. This middleware class provides protection against
  variable injection via the GLOBALS array.
 
 Namespace:
  \foundry\middleware\common
*/
class common implements MiddlewareInterface  {

	// return response stops processing
	public function handleRequest($request) {

		// catches globals array injection attacks
		if( in_array( 'globals', array_keys( array_change_key_case(
			$_REQUEST, CASE_LOWER ) ) ) ) {
			unset( $_GET );
			unset( $_POST );
		}

		return false;
	}
	
	public function handleController($request, $controller, $kwargs = array()) {
		return false;
	}

	// return response stops processing
	public function handleView($request, $view, &$payload, $kwargs = array()) {
		return false;
	}

	// return response stops processing
	public function handleResponse($request, $response) {
		return false;
	}

	// return request stops processing
	public function handleException($request, $response, $exception) {
		return false;
	}
	
	public function handleHalt($request, $kwargs = array()) {
		return false;
	}

}

?>