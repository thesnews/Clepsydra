<?php
/*
 File: controller
 
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
use foundry\event as Event;

/*
 Class: controller
  Provides the base implementation for all Foundry controller objects
 
 Namespace:
  \foundry
*/
class controller {

	/*
	 Parameter: request
	  The current request object
	 
	 Access:
	  protected
	*/
	protected $request = false;
	protected $response = false;

	/*
	 Parameter: defaultAction
	  The default action to perform
	 
	 Access:
	  protected
	*/
	protected $defaultAction = 'main';

	/*
	 Method: constructor
	  Initialize the object
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ the current request object
	 
	 Returns:
	  _object_
	*/
	public function __construct($request) {
		$this->request = $request;

		Event::fire( $this, 'init');
	}

	/*
	 Method: callDefault
	  Call the controller's default action
	  
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _mixed_ the action's payload
	*/
	public function callDefault() {
		return call_user_func(array($this, $this->defaultAction));
	}
	
	/*
	 Method: callAction
	  Call a named action (method) on the controller
	 
	 Access:
	  public
	 
	 Parameters:
	  action - _string_ (optional) if FALSE, default action will be called
	 
	 Returns:
	  _mixed_ the action's payload
	*/
	public function callAction($action=false) {
		$payload = false;
		
		Event::fire($this, 'actionStart');
		
		if( method_exists($this, $action) ) {
			$payload = call_user_func(array($this, $action));
		} else {
			$payload = $this->callDefault();
		}

		Event::fire($this, 'actionComplete');
		
		return $payload;
	}
	
}

?>