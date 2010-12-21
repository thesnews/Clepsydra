<?php
/*
 File: interface
  Provides \foundry\middleware\interface interface
  
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

/*
 Interface: middlewareInterface
  Provides the interface all middleware must implement
 
 Namespace:
  \foundry\middleware
*/
interface middlewareInterface {

	/*
	 Method: handleRequest
	  Called during the request phase, immediately after the request is 
	  processed and initialized.
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public function handleRequest($request);
	
	/*
	 Method: handleController
	  Called during the controller phase, immediately after the proper 
	  controller is selected and initialized.
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ request object
	  controller - _object_ the current controller
	  kwargs - _array_ (optional) any keyword arguments
	 
	 Returns:
	  _void_
	*/
	public function handleController($request, $controller, $kwargs = array());

	/*
	 Method: handleView
	  Called during the view phase, immediately after the view callback is
	  selected.
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  view - _callback_ the view function callback (not a string, but the
	  actual callback).
	  payload - _mixed_ the data returned from the controller
	  kwargs - _array_ (optional) any extra keyword arguments
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public function handleView($request, $view, &$payload, $kwargs = array());

	/*
	 Method: handleResponse
	  Called during the response phase, immediately after the view callback
	  has returned a response.
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  response - _object_ the response object
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public function handleResponse($request, $response);

	/*
	 Method: handleException
	  Called during the exception handler
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the request object
	  response - _object_ the response object
	  exception - _object_ the thrown exception`
	 
	 Returns:
	  _mixed_ returning anything but false will halt processing
	*/
	public function handleException($request, $response, $exception);
	
	/*
	 Method: handleHalt
	  Called during the HALT exception
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the current request
	  kwargs - _array_ (optional) any extra keyword arguments
	 
	 Returns:
	  _void_
	*/
	public function handleHalt($request, $kwargs = array());

}
?>