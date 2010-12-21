<?php
/*
 File: request
  Provides \foundry\request class
  
 Version:
  2010.06.16
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\request {
	function __init__() {
	}
}

namespace foundry {
	use foundry\filter as Filter;
	use foundry\event as Event;
	use foundry\config as Conf;
	use foundry\request\route as Route;
	use foundry\request\session as Session;
	use foundry\proc as FProc;
	
	/*
	 Class: request
	  Manages all information related to the current request
	 
	 Namespace:
	  \foundry
	*/
	class request {
		
		/*
		 Parameter: query
		  The \foundry\request\query object
		 
		 Access:
		  public
		*/
		public $query = false;

		/*
		 Parameter: route
		  The \foundry\request\route object
		 
		 Access:
		  public
		*/
		public $route = false;
		
		/*
		 Parameter: referrer
		  The referring URL
		 
		 Access:
		  public
		*/
		public $referrer = false;
		
		/*
		 Parameter: session
		  The currently active session
		 
		 Access:
		  public
		*/
		public $session = false;
		
		/*
		 Parameter: authSession
		  The currently active authenticated session
		 
		 Access:
		  false
		*/
		public $authSession = false;
		
		/*
		 Parameter: controller
		  The currently active controller
		 
		 Access:
		  public
		*/
		public $controller = 'main';

		/*
		 Parameter: action
		  The currently active... action
		 
		 Access:
		  public
		*/
		public $action = 'main';

		/*
		 Parameter: package
		  The currently active package (i.e. the primary context package)
		 
		 Access:
		  public
		*/
		public $package = false;
		
		private $files = array();
		
		private $stack = array();
		
		/*
		 Method: constructor
		  Also initializes the query and route objects
		 
		 Access:
		  public
		 
		 Parameters:
		  url - _string_ the current request URL
		 
		 Returns:
		  _object_
		*/
		public function __construct($uri) {
			Event::fire('foundry\\request', 'init');
			
			$this->query = new \foundry\request\query($uri);
			$this->route = new \foundry\request\route;
			
			$this->package = Conf::get('namespace');
			
			FProc::setRequest($this);
		}
		
		/*
		 Method: getSession
		  Return the current request session instance. This should only be used
		  for transitory information. Authenticated state should be handled
		  via the \foundry\auth\session class
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _object_ \foundry\request\session instance
		*/
		public function getSession() {
			if( !$this->session ) {
				$this->session = new \foundry\request\session;
			}
			
			return $this->session;
		}

		/*
		 Method: method
		  Determine the current request method (i.e. GET, POST, PUT, etc...0
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function method() {
			return Filter::alnum($_SERVER['REQUEST_METHOD']);
		}

		/*
		 Method: isMobile
		  Determines of the current request is from a mobile UA

		  Adapted from Russell Beattie:
		  http://www.russellbeattie.com/blog/mobile-browser-detection-in-php
		  Which was adapted from another project.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public function isMobile() {
			$isMobile = false;
	
			$op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
			$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
			$ac = strtolower($_SERVER['HTTP_ACCEPT']);
			$ip = $_SERVER['REMOTE_ADDR'];
	
			$isMobile = strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
				|| $op != ''
				|| strpos($ua, 'mobile') !== false
				|| strpos($ua, 'sony') !== false
				|| strpos($ua, 'symbian') !== false
				|| strpos($ua, 'nokia') !== false
				|| strpos($ua, 'samsung') !== false
				|| strpos($ua, 'windows ce') !== false
				|| strpos($ua, 'epoc') !== false
				|| strpos($ua, 'opera mini') !== false
				|| strpos($ua, 'nitro') !== false
				|| strpos($ua, 'j2me') !== false
				|| strpos($ua, 'midp-') !== false
				|| strpos($ua, 'cldc-') !== false
				|| strpos($ua, 'netfront') !== false
				|| strpos($ua, 'mot') !== false
				|| strpos($ua, 'up.browser') !== false
				|| strpos($ua, 'up.link') !== false
				|| strpos($ua, 'audiovox') !== false
				|| strpos($ua, 'blackberry') !== false
				|| strpos($ua, 'ericsson,') !== false
				|| strpos($ua, 'panasonic') !== false
				|| strpos($ua, 'philips') !== false
				|| strpos($ua, 'sanyo') !== false
				|| strpos($ua, 'sharp') !== false
				|| strpos($ua, 'sie-') !== false
				|| strpos($ua, 'portalmmm') !== false
				|| strpos($ua, 'blazer') !== false
				|| strpos($ua, 'avantgo') !== false
				|| strpos($ua, 'danger') !== false
				|| strpos($ua, 'palm') !== false
				|| strpos($ua, 'series60') !== false
				|| strpos($ua, 'palmsource') !== false
				|| strpos($ua, 'pocketpc') !== false
				|| strpos($ua, 'smartphone') !== false
				|| strpos($ua, 'rover') !== false
				|| strpos($ua, 'ipaq') !== false
				|| strpos($ua, 'au-mic,') !== false
				|| strpos($ua, 'alcatel') !== false
				|| strpos($ua, 'ericy') !== false
				|| strpos($ua, 'up.link') !== false
				|| strpos($ua, 'vodafone/') !== false
				|| strpos($ua, 'wap1.') !== false
				|| strpos($ua, 'wap2.') !== false;
	
				return $isMobile;
		}
		
		/*
		 Method: isXHR
		  Determine if the current request is XMLHTTPRequest
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public function isXHR() {
			return $this->isXMLHTTP();
		}

		/*
		 Method: isXMLHTTP
		  Same as <isXHR>
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public function isXMLHTTP() {
			if( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
				return true;
			}
			
			return false;
		}
		
		/*
		 Method: get
		  Convenience method to GET array
		  
		 Example:
		  > echo request::get('foo', 'alnum');
		  You can also filter against an array of values
		  > echo request::get('someVal', array('hello', 'world'))
		 
		 Access:
		  public
		 
		 Parameters:
		  key - _string_ array key
		  filter - _string_ foundry\filter method to filter value against
		  extra - _mixed_ (optional) extra data passed to filter method
		 
		 Returns:
		  _mixed_
		*/
		public function get($key, $filter, $extra = false) {
			if( is_array($filter) ) {
				if( in_array($_GET[$key], $filter) ) {
					return $_GET[$key];
				} elseif( $extra ) {
					return $extra;
				}
				return false;
			}

			if( !method_exists('\\foundry\\filter', $filter) ) {
				return false;
			}
			
			return Filter::$filter($_GET[$key], $extra);
		}
	
		/*
		 Method: post
		  Convenience method to POST array
		  
		 Example:
		  > echo request::post('foo', 'alnum');
		  You can also filter against an array of values
		  > echo request::post('someVal', array('hello', 'world'))
		 
		 Access:
		  public
		 
		 Parameters:
		  key - _string_ array key
		  filter - _string_ foundry\filter method to filter value against
		  extra - _mixed_ (optional) extra data passed to filter method
		 
		 Returns:
		  _mixed_
		*/
		public function post($key, $filter, $extra = false) {
			if( is_array($filter) ) {
				if( in_array($_POST[$key], $filter) ) {
					return $_POST[$key];
				} elseif( $extra ) {
					return $extra;
				}
				return false;
			}

			if( !method_exists('\\foundry\\filter', $filter) ) {
				return false;
			}
			
			return Filter::$filter($_POST[$key], $extra);
		}
	
		/*
		 Method: files
		  Initialize and return a foundry\request\file object
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _object_ \foundry\request\files instance
		*/
		public function files() {
			if( count($this->files) ) {
				return $this->files;
			}
			
			if( count($_FILES) ) {
				$this->files = \foundry\request\file::load($_FILES);
			}
			
			return $this->files;
		}
	
		/*
		 Method: cookie
		  Convenience method to COOKIE array
		  
		 Example:
		  > echo request::cookie('foo', 'alnum');
		  You can also filter against an array of values
		  > echo request::cookie('someVal', array('hello', 'world'))
		 
		 Access:
		  public
		 
		 Parameters:
		  key - _string_ array key
		  filter - _string_ foundry\filter method to filter value against
		  extra - _mixed_ (optional) extra data passed to filter method
		 
		 Returns:
		  _mixed_
		*/
		public function cookie($key, $filter, $extra = false) {
			if( !method_exists('\\foundry\\filter', $filter) ) {
				return false;
			}
			
			return Filter::$filter($_COOKIE[$key], $extra);
		}
		
		/*
		 Method: get
		  Convenience method to GET array
		  
		 Example:
		  > echo request::get('foo', 'alnum');
		  You can also filter against an array of values
		  > echo request::post('someVal', array('hello', 'world'))
		 
		 Access:
		  public
		 
		 Parameters:
		  key - _string_ array key
		  filter - _string_ foundry\filter method to filter value against
		  extra - _mixed_ (optional) extra data passed to filter method
		 
		 Returns:
		  _mixed_
		*/
		public function server($key, $filter, $extra = false) {
			if( !method_exists('\\foundry\\filter', $filter) ) {
				return false;
			}
			
			return Filter::$filter($_SERVER[$key], $extra);
		
		}
		
		/*
		 Method: processRoute
		  It... uh... process the route information
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function processRoute() {
			$info = $this->route->process($this->query->query);
			
			if( $info['controller'] ) {
				$this->controller = $info['controller'];
			}

			if( $info['package'] ) {
				$this->package = $info['package'];
			}

			if( ($actn = $this->get(':action', 'alnum')) ) {
				$this->action = $actn;
			}
			
		}
		
		public function __toString() {
			$refer = \foundry\fs\path::join(\foundry\os::serverName(),
				\foundry\proc::getRelativeWebRoot(),
				'index.php', $this->query->query);

			return 'http:/'.$refer;			
		}
		
		/*
		 Overloaded getters and setters
		*/
		public function __get($k) {
			return $this->stack[$k];
		}
		
		public function __set($k, $v) {
			$this->stack[$k] = $v;
			return $v;
		}

	}
}

?>