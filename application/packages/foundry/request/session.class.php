<?php
/*
 File: session
  Provides \foundry\request\session class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\request;
use foundry\filter as Filter;

/*
 Class: session
  Standard session handler. This class uses PHP's built in session handler and
  can thus be used at the same time as an authenticated session 
  (\foundry\auth\session)
  
  This class is automatically initialized by the \foundry\request object, so you
  shouldn't ever need to create a session object directly. Use 
  \foundry\request::getSession() instead
  
 Example:
 (begin code)
 $r = \foundry\proc::getRequest();
 $r->getSession()->set('foo', 'bar');
 (end)
 
 Namespace:
  \foundry\request
*/
class session {
	
	/*
	 Parameter: stack
	  Internal property stack
	 
	 Access:
	  public
	*/
	public $stack = array();
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ (optional) session name
	 
	 Returns:
	  _object_
	*/
	public function __construct($str=false) {
		if( $str ) {
			session_name($str);
		}
	
		@session_start();
	}
	
	/*
	 Method: id
	  Get or set the session ID
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ session ID
	 
	 Returns:
	  _string_ session ID
	*/
	public function id($str=false) {
		if( $str ) {
			session_id($str);
		}
		
		return session_id();
	}
	
	/*
	 Method: get
	  Get a session variable
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ session variable name
	  filter - _string_ (optional) a \foundry\filter method to filter against
	 
	 Returns:
	  _mixed_
	*/
	public function get($k, $filter='specialChars') {
		return Filter::$filter($_SESSION[$k]);
	}
	
	/*
	 Method: set
	  Set a session variable
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ variable name
	  v - _mixed_ value
	 
	 Returns:
	  _void_
	*/
	public function set($k, $v) {
		$_SESSION[$k] = $v;
	}
	
	/*
	 Method: reset
	  Reset internal session data
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function reset() {
		$_SESSION = array();
	}
	
	/*
	 Method: destroy
	  Destroy the session
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function destroy() {
		$this->reset();
		if( isset($_COOKIE[session_name()]) ) {
			setcookie(session_name(), '', time-42000, '/');
		}
		
		session_destroy();
	}
}

?>