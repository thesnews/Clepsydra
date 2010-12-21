<?php
/*
 File: basic
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php

 See Also:
  foundry\middleware
*/
namespace foundry\auth\middleware;
use foundry\middleware\middlewareInterface as MiddlewareInterface;

use foundry\config as Conf;
use foundry\auth\session as AuthSession;
use foundry\utility as Util;

/*
 Class: basic
  Provides for automatic session detection and verification.
 
 Namespace:
  \foundry\auth\middleware
 
 Implements:
  \foundry\middleware\middlewareInterface
*/
class basic implements MiddlewareInterface {

	public function handleRequest($request) {
	}

	/*
	 Method: handleController
	  Handles controller call
	 
	 Parameters:
	  request - _object_ request object
	  controller - _object_ controller object
	  kwargs - _array_ array of named arguments
	 
	 Returns:
	  _bool_
	  
	 Access:
	  public
	*/
	public function handleController($request, $controller, $kwargs = array()) {
		// this provides basic authenticated session checking when used in
		// conjunction with \foundry\auth\session
		// if you need more groups and ACLs check foundry\auth\session\acl
		// and foundry\auth\middleware\acl
		if( $controller->requiresAuth ) {
			$auth = new AuthSession;

			if( !$auth->verify() ) {
				throw new \foundry\exception\auth('access', 'Not verified');
			}
			
			// load auth session into the request
			$request->authSession = $auth;
		}
	
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