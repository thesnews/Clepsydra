<?php
/*
 Title: middleware\auth

 Group: Middleware
 
 File: auth
  Provides \admin\middleware\auth class
  
 Version:
  2010.06.24
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace clepsydra\middleware;
use foundry\auth\utility as AuthUtil;
use foundry\auth\session as AuthSession;
use admin\lib\auth as AuthLib;
use foundry\request\url as URL;
use foundry\config as Conf;

/*
 Class: auth
  Admin package authentication middleware
 
 Namespace:
  \admin\middleware
*/
class auth implements \foundry\middleware\middlewareInterface {

	public function handleRequest($request) {
		if( !Conf::get($request->package.':requiresAuth') ) {
			return;
		}
		
		if( !in_array(sprintf('%s:%s', $request->package, $request->controller), 
			Conf::get($request->package.':requiresAuth')) ) {
			
			return false;
		}

		// verify the session
		$authSess = new AuthSession;
		if( !$authSess->verify() ) {
			$to = URL::build(URL::linkTo('clepsydra:main'), array(
				'message' => 'You must be logged in.',
				'type' => 'error',
				'refer' => $request->query->query
			));
			return new \foundry\response\redirect($to);
		}

		// also disable query caching
		\foundry\model::$disableCache = true;
		
		$request->authSession = $authSess;
		
		// we also set a temporary cookie to allow the frontend caching
		// to be disabled while browsing the 'gryphon' packages
		$name = AuthUtil::generateKeyName('cleps1_');
		$value = time();
		setcookie($name, $value, time()+3600, '/');

	}

	public function handleController($request, $controller, $kwargs = array()) {
		if( !Conf::get($request->package.':requiresAuth') ) {
			return;
		}

		if( !in_array(sprintf('%s:%s', $request->package, $request->controller), 
			Conf::get($request->package.':requiresAuth')) ) {
			
			return false;
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
		if( $request->authSession ) {
			$request->authSession->__destruct();
		}
		return false;
	}


}
?>