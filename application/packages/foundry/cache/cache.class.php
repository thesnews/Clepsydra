<?php
/*
 File: cache
  Provides \foundry\cache class and \foundry\cache\cacheInterface interface
  
 Version:
  2010.06.14
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry {
	
	use foundry\config as Conf;
	
	/*
	 Class: cache
	  \foundry\cache provides a static frontend to the cache driver. This
	  class is initialized by the bootstrap script.
	  
	 Example:
	 (start code)
	   use foundry\cache as Cache;
	   ...
	   Cache::store('foo', $bar);
	   echo Cache::retrieve('foo');
	 (end)
	 
	 Namespace:
	  \foundry\cache
	*/
	class cache {
	
		/*
		 Parameter: driver
		  _object_ cache driver
		 
		 Access:
		  public
		*/
		public static $driver = false;

		/*
		 Parameter: status
		  _bool_
		 
		 Access:
		  public
		*/
		public static $status = false;
		
		/*
		 Method: init
		  Initializes the cache static class
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public static function init() {
			if( self::$driver ) {
				return false;
			}
			if( !($info = Conf::get('cache')) ) {
				return;
			}
	
			$cls = sprintf('\\foundry\\cache\\driver\\%s', $info['driver']);
			self::$driver = new $cls($info);
		
			self::$status = true;
		}
		
		/*
		 Method: isEnabled
		  Test to see if cache is enabled
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public static function isEnabled() {
			return self::$status;
		}
		
		/*
		 Method: store
		  Pass store command to cache driver
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ cache key
		  v - _mixed_ item to cache
		 
		 Returns:
		  _bool_
		*/
		public static function store($k, $v) {
			return self::$driver->store($k, $v);
		}
		
		/*
		 Method: retrieve
		  Pass retrieve command to cache driver
		 
		 Access:
		  public
		 
		 Parameters:
		  v - _string_ cache key
		  ignore - _bool_ (OPTIONAL) default FALSE. If TRUE cache will skip
		    validation check.
		 
		 Returns:
		  _mixed_
		*/
		public static function retrieve($v, $ignore = false) {
			return self::$driver->retrieve($v, $ignore);
		}
		
		/*
		 Method: clear
		  Pass clear command to cache driver (removes a single cache entry)
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ cache key
		 
		 Returns:
		  _bool_
		*/
		public static function clear($k) {
			return self::$driver->clear($k);
		}
		
		/*
		 Method: clean
		  Pass clean command to cache driver (empties entire cache)
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public static function clean() {
			return self::$driver->clean();
		}
	
	}
}

namespace foundry\cache {
	interface cacheInterface {
		public function store($key, $val);
		public function retrieve($key);
		public function clear($key);
		public function clean();
	}
}

?>