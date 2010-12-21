<?php
/*
 File: middleware
  Provides \foundry\middleware class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;
require_once 'foundry/middleware/interface.class.php';

/*
 Class: middleware
  Manages and runs registered middleware. Middleware allows you to inject code
  at specific points during Foundry's run loop allowing you to dramatically
  alter the way an app functions.
 
 Namespace:
  \foundry\
  
 Sell Also:
 foundry\auth\middleware\basic <foundry\auth\middleware\basic.class.php>
*/
class middleware {

	/*
	 Parameter: registered
	  Array of all registered middleware
	 
	 Access:
	  public
	*/
	public static $registered = array();

	/*
	 Parameter: events
	  Valid middleware processing events
	 
	 Access:
	  public
	*/
	public static $events = array(
		'request'	=> 'forward',
		'controller'=> 'forward',
		'view'		=> 'forward',
		'response'	=> 'reverse',
		'exception'	=> 'reverse',
		'halt'		=> 'reverse'
	);
	
	/*
	 Method: register
	  Register a piece of middleware
	 
	 Example:
	 (start code)
	  class foo extends \foundry\middleware\interface { ... }
	  
	  \foundry\middleware::register(array(
	    '\foundry\middeleware\class'));
	 (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  array - _array_ array of middleware to register
	 
	 Returns:
	  _void_
	*/
	public static function register($array) {
		foreach( $array as $cls ) {
			self::$registered[$cls] = new $cls;
		}
	}
	
	/*
	 Method: handleRequest
	  Process the 'request' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the current request object
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public static function handleRequest($request) {
		$mw = self::$registered;
		foreach( $mw as $obj ) {
			if( ($v = self::process($obj->handleRequest($request))) ) {
				return $v;
			}
		}
		
		return false;
	}

	/*
	 Method: handleView
	  Process the 'view' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the current request object
	  view - _callback_ the currently selected view function
	  payload - _mixed_ the payload passed back from the controller
	  kwargs - _array_ (optional) any extra keyword arguments
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public static function handleView($request, $view, $payload,
		$kwargs=array()) {
		$mw = self::$registered;
		foreach( $mw as $obj ) {
			if( ($v = self::process($obj->handleView($request, $view, $payload,
				$kwargs=array()))) ) {
				return $v;
			}
		}
		
		return false;
	}

	/*
	 Method: handleController
	  Process the 'controller' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  controller - _object_ the current controller
	  kwargs - _array_ (optional) extra keyword arguments
	 
	 Returns:
	  _void_
	*/
	public static function handleController($request, $controller,
		$kwargs=array()) {

		$mw = self::$registered;
		foreach( $mw as $obj ) {
			$obj->handleController($request, $controller, $kwargs);
		}
	}

	/*
	 Method: handleResponse
	  Process the 'response' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  response - _object_ the response object
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public static function handleResponse($request, $response) {
		$mw = array_reverse(self::$registered);
		foreach( $mw as $obj ) {
			if( ($v = self::process($obj->handleResponse($request, 
				$response))) ) {
				return $v;
			}
		}
		
		return false;
	}

	/*
	 Method: handleException
	  Process the 'exception' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  response - _object_ the response object
	  exception - _object_ the exception object
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public static function handleException($request, $response, $exception) {
		$mw = array_reverse(self::$registered);
		foreach( $mw as $obj ) {
			if( ($v = self::process($obj->handleException($request, $response,
				$exception))) ) {
				return $v;
			}
		}
		
		return false;
	}

	/*
	 Method: handleHalt
	  Process the 'halt' middleware event
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	 
	 Returns:
	  _void_
	*/
	public static function handleHalt($request) {
		$mw = array_reverse(self::$registered);
		foreach( $mw as $obj ) {
			$obj->handleHalt($request);
		}
	}
	
	/*
	 Run the event processing
	*/
	private static function process($val=false) {
		if( $val && is_object($val) && 
			strpos(get_class($val), 'foundry\\response') !== false ) {		
			return $val;
		}
		
		return false;
	}
}
?>