<?php
/*
 File: moved
  Provides \foundry\request\moved class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\response;

/*
 Class: moved
  Performs a status 301 "Moved Permanently" redirect. Returning a redirect
  response from a controller or view will cause foundry to immediately HALT and
  header redirect the user to the provided url
 
 Namespace:
  \foundry\request
*/
class moved extends \foundry\response {

	private $url = false;

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  to - _string_ URL to direct to
	 
	 Returns:
	  _object_
	*/
	public function __construct($to) {
		$this->url = $to;
		$this->setHeader('HTTP/1.1 301 Moved Permanently', false);
		$this->setHeader('Location', $this->url);
	}
	
	/*
	 Method: process
	  Handle the request process event
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function process() {
		$this->processHeaders();
		throw new \foundry\exception\halt;
	}

}
?>