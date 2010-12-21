<?php
/*
 File: response
  Provides \foundry\response class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\response {
	function __init() {
	
	}
}

namespace foundry {
	use foundry\utility as Util;
	use foundry\event as Event;
	
	/*
	 Class: response
	  A standard response object. Response objects are returned by view
	  callbacks and contain any information to be returned to the user.
	 
	 Namespace:
	  \foundry
	*/
	class response {

		/*
		 Parameter: content
		  _string_ - The content of the response
		 
		 Access:
		  public
		*/
		public $content = '';

		/*
		 Parameter: headers
		  _array_ - The header stack
		 
		 Access:
		  public
		*/
		public $headers = array();
		
		private $type = 'tpl';
		private $request = '';
		private $payload = array();

		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function __construct() {
		}
		
		public function type() {
		
		}
		
		/*
		 Method: toString
		  Overloaded magic method to return the content of the response on
		  echo
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function __toString() {
			return $this->content;
		}
		
		/*
		 Method: process
		  Handler for the response process event
		 
		 Access:
		  public
		 
		 Parameters:
		  noHeader - _bool_ (optional) don't display headers
		 
		 Returns:
		  _object_ this response
		*/
		public function process($noHeader=false) {
			if( !$noHeader ) {
				$this->processHeaders();
			}
			echo $this->content;
		}

		/*
		 Method: processHeaders
		  Send response headers to the client
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function processHeaders() {
			if( count($this->headers) ) {
				foreach( $this->headers as $k => $v ) {
					if( $v ) {
						$v = ':'.$v;
					}
					header(sprintf('%s%s', $k, $v));
				}
			}
		}
		
		/*
		 Method: setHeader
		  Set a response header
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ header name
		  v - _string_ header value
		 
		 Returns:
		  _void_
		*/
		public function setHeader($k, $v) {
			$this->headers[$k] = $v;
		}
		
		/*
		 Method: getHeader
		  Returns a header value
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ header name
		 
		 Returns:
		  _mixed_ returns header value if it exists, FALSE otherwise
		*/
		public function getHeader($k) {
			if( isset($this->headers[$k]) ) {
				return $this->headers[$k];
			}
			
			return false;
		}
		
	
	}
}

?>