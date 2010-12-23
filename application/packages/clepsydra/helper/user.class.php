<?php
/*
 Title: helper\user

 Group: Helpers
 
 File: user.class.php
  Provides thisUser helper class
  
 Version:
  2010.07.09
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace clepsydra\helper;
use foundry\proc as FProc;
use foundry\model as M;

/*
 Class: user
  Provides a simple template interface to the currently logged in user
 
 Example:
 (start code)
  {% helper thisUser %}
  
  Hey there {{ thisUser.name }}!
 (end)
 
 Namespace:
  \clepsydra\helper
*/
class user {
	
	private $request = false;
	private $user = false;
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ helper instance
	*/
	public function __construct() {
		$this->request = FProc::getRequest();
		
		$this->user = M::init('clepsydra:person')->findByUID(
			$this->request->authSession->user);
	}
	
	/*
	 Method: getSetting
	  Passthrough for getOption, returns option for key
	 
	 Access:
	  public
	 
	 Parameters:
	  - k - _string_ setting key
	 
	 Returns:
	  _mixed_ value
	*/
	public function getSetting($k) {
		return $this->getOption($k);
	}

	/*
	 Method: getOption
	  Returns option for key
	 
	 Access:
	  public
	 
	 Parameters:
	  - k - _string_ setting key
	 
	 Returns:
	  _mixed_ value
	*/
	public function getOption($k) {
		return $this->user->getOption($k);
	}
	
	/*
	 Method: __get
	  Overloaded get method
	 
	 Access:
	  public
	 
	 Parameters:
	  - k - _string_
	 
	 Returns:
	  _mixed_
	*/
	public function __get($k) {
		return $this->user->$k;
	}
	
	public function __isset($k) {
		return true;
	}
	
	/*
	 Method: isAdmin
	  Determine if current user is admin
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	*/
	public function isAdmin() {
		return $this->user->is_admin;
	}
}
?>