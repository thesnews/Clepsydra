<?php
/*
 File: helper
  Provides \foundry\view\helper class
  
 Version:
  2010.06.03
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view {
	
	use foundry\config as Conf;

	/*
	 Class: helper
	  Base helper class
	 
	 Namespace:
	  \foundry\view
	*/
	class helper {
		public static $helpers = array();

		/*
		 Method: register
		  Register a new helper
		 
		 Example:
		 (start code)
		  \foundry\view\helper::register('someHelper', '\\my\\sweet\\helper');
		  ... later in the templates ...
		  {{ someHelper.anAction() }}
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  id - _string_ unique identifier for helper
		  str - _string_ helper class
		 
		 Returns:
		  _void_
		*/
		public static function register($id, $str) {
			self::$helpers[$id] = $str;
		}
		
		
	}
}

namespace foundry\view\helper {
	use foundry\fs\path as P;
	use foundry\model as M;
	use foundry\request\url as URL;
	use foundry\view as View;
	use foundry\utility as Util;
	use foundry\config as Conf;
	use foundry\proc as FProc;
	
	/*
	 Method: fetch
	  Fetches a template helper into the current scope. This method is actually
	  implemented by a the 'helper' template tag.
	  
	  Template helpers are not initialized until they're first called.
	 
	 Access:
	  public
	 
	 Parameters:
	  hlpr - _string_ helper name
	 
	 Returns:
	  _object_ a template helper
	*/
	function fetch($hlpr) {
		if( !array_key_exists($hlpr, \foundry\view\helper::$helpers) ) {
			return false;
		}
		
		if( is_string(\foundry\view\helper::$helpers[$hlpr]) ) {
			$cls = \foundry\view\helper::$helpers[$hlpr];
			\foundry\view\helper::$helpers[$hlpr] = new $cls();
		}
		
		return \foundry\view\helper::$helpers[$hlpr];
	}
	
	/*
	 Class: config
	  Config template helper. Provides access to Foundry's config system from
	  the templates.
	 
	 Namespace:
	  \foundry\view\helper
	*/
	class config {

		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _object_
		*/
		public function __construct() {
		}
		
		/*
		 Method: get
		  Get a config variable. Just like \foundry\config::get()
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ config variable name
		 
		 Returns:
		  _mixed_
		*/
		public function get($str) {
			return Conf::get($str);
		}
		
		/*
		 Method: appVersion
		  Return the application version as defined by the package config file
		  for the package running in primary context.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function appVersion() {
			return Conf::get(sprintf('%s:version', Conf::get('namespace')));
		}

		/*
		 Method: foundryVersion
		  Return the current Foundry version
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function foundryVersion() {
			return \foundry\VERSION;
		}
		
		/*
		 Method: phpVersion
		  Return the current PHP version
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function phpVersion() {
			return phpversion();
		}
	}
}
?>