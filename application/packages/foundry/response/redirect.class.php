<?php
/*
 File: redirect
  Provides \foundry\response\redirect class
  
 Version:
  2010.10.12
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\response;

/*
 Class: redirect
  Performs a standard header redirect. Returning a redirect response from a
  controller or view will cause foundry to immediately HALT and header redirect
  the user to the provided url.
  
 Example:
  > return new \foundry\response\redirect('http://path.to.com');
 
 
 Namespace:
  \foundry\request
*/
class redirect extends \foundry\response {

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
	
	/*
	 Method: getUrl
	  Get the redirect url. Used for testing, mostly.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getUrl() {
		return $this->url;
	}

}
?>