<?php
/*
 File: config
 
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

/*
 Class: config
  Manages system wide config values. Foundry reads all global, app and package
  level config files and makes them accessable via the config class.
  
  You can access package level config values by prepending the package name
  to the key. You can also extract keys from an array by using the colon syntax
  outlined below.
  
  Examples:
  (start code)
  use foundry\config as Conf;
  ...
  echo Conf::get('foo'); // looks for the config key 'foo'
  
  echo Conf::get('foo:bar'); // looks for the key 'bar' inside 'foo'
  (end)
 
 Namespace:
  \foundry
*/
class config {
	
	private static $configuration = array(
		'routes' => array(),
		'middleware' => array(),
		'helpers' => array()
	);

	/*
	 Method: load
	  Load config data from array
	 
	 Access:
	  public
	 
	 Parameters:
	  c - _array_ array of config values
	 
	 Returns:
	  _array_ array of config values
	*/
	public static function load($c) {
		self::$configuration = array_merge(self::$configuration, $c);
		
		return self::export();
	}

    /*
     Method: export
      Returns entire config table as nested array
     
     Access:
      public
     
     Parameters:
      _void_
     
     Returns:
      _array_
    */
	public static function export() {
		return self::$configuration;
	}

	/*
	 Method: get
	  Get a value from the global config. Values are namespaced by package.
	  
	 (begin code)
	 use foundry\config as Conf;
	 ...
	 Conf::load(array(
	 	'foo' => array(
	 		'bar' => 'BAZ!'
	 		'bork' => 'Hello World'
	 	),
	 	'name' => 'Mike'
	 ));
	 ...
	 echo Conf::get('name'); // Mike
	 echo Conf::get('foo:bar'); // 'BAZ!'
	 echo Conf::get('foo'); // Array
	 (end)
	 
	 Access:
	  public
	  
	 Parameters:
	  k - _string_ key
	 
	 Returns:
	  _mixed_
	*/
	public static function get($k) {
		// allows for namespaced grabbing
		if( strpos($k, ':') !== false ) {
			$parts = explode(':', $k);
			$arr = self::$configuration;
			while( count($parts) ) {
				$k = array_shift($parts);
				if( is_array($arr[$k]) ) {
					$arr = $arr[$k];
					continue;
				}
				
				return $arr[$k];
			}
			
			return $arr;
		}
		return self::$configuration[$k];
	}
	
	/*
	 Method: set
	  Set a config value.
	  WARNING: You should avoid altering config values at runtime.
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ key
	  v - _mixed_ value
	 
	 Returns:
	  _mixed_ value
	*/	  
	public static function set($k, $v) {
		self::$configuration[$k] = $v;
		return $v;
	}
	
}
?>