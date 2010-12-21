<?php
/*
 File: cookie
  Provides \foundry\request\cookie class
  
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
 Class: cookie
  Standardized interface for dealing with cookies
 
 Namespace:
  \foundry\request
*/
class cookie {
	
	public $stack = array();
	private $name = false;
	private $timeout = false;
	private $domain = false;
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  name - _string_ the cookie name
	  timeout - _int_ (optional) timeout in seconds
	  domain - _string_ (optional) valid domain
	 
	 Returns:
	  _void_
	*/
	public function __construct($name, $timeout=false, $domain='/') {
		if( !$timeout ) {
			$timeout = time()+3600;
		}
		$this->name = $name;
		$this->timeout = $timeout;
		$this->domain = $domain;
		
		if( isset($_COOKIE[$name]) ) {
			parse_str($_COOKIE[$name], $this->stack);
		}
	}
	
	
	/*
	 Method: get
	  Get value from cookie array
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ value name
	  filter - _string_ (optional) method to filter against
	 
	 Returns:
	  _mixed_
	*/
	public function get($k, $filter='specialChars') {
		return Filter::$filter($this->stack[$k]);
	}
	
	/*
	 Method: set
	  Set a value to store in the cookie array
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ value key name
	  v - _mixed_ value to store
	 
	 Returns:
	  <# return value #>
	*/
	public function set($k, $v) {
		$this->stack[$k] = $v;
		
		setcookie($this->name, http_build_query($this->stack), $this->timeout,
			$this->domain);
	}
	
	/*
	 Method: reset
	  Reset cookie internal stack
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function reset() {
		$this->stack = array();
	}
	
	/*
	 Method: destroy
	  Destroy cookie
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function destroy() {
		$this->stack = array();
		if( isset($_COOKIE[$this->name]) ) {
			setcookie($this->name, '', time-42000, '/');
		}
	}
}

?>