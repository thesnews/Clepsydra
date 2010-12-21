<?php
/*
 File: cacheLite
  Provides \foundry\cache\driver\cacheLite class
  
 Version:
  2010.06.14
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\cache\driver;
use foundry\config as Conf;
use foundry\fs\path as Path;

require_once 'vendor/CacheLite/Lite.php';

/*
 Class: cacheLite
  Wraps the existing Cache_Lite PEAR library in a foundry specific cache
  driver. Cache_Lite is included in application/vendor
 
 Namespace:
  foundry\cache\driver
*/
class cacheLite implements \foundry\cache\cacheInterface {
	
	private $cache = false;
	
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
		// cache lite assumes that your cacheDir option has a trailing slash
		// so we have to make sure it does
		$pth = sprintf('/%s/', Path::standardize(Conf::get('private-path')));
		
		$this->cache = new \Cache_Lite(array(
			'lifeTime' => 900,
			'cacheDir' => $pth
		));
	}
	
	/*
	 Method: store
	  Store item in cache
	 
	 Access:
	  public
	 
	 Parameters:
	  key - _string_
	  val - _mixed_
	 
	 Returns:
	  _bool_
	*/
	public function store($key, $val) {
		try {
			return $this->cache->save($val, $key);
		} catch(Exception $e) {
			return false;
		}
	}
	
	/*
	 Method: retrieve
	  Retrieve cached item
	 
	 Access:
	  public
	 
	 Parameters:
	  key - _string_
	  ignoreValidity - _bool_ (OPTONAL) defaults to FALSE. If TRUE will return
		data regardless of expiry.
	 
	 Returns:
	  _mixed_
	*/
	public function retrieve($key, $ignoreValidity = false) {
		try {
			return $this->cache->get($key, 'default', $ignoreValidity);
		} catch(Exception $e) {
			return false;
		}
	}
	
	/*
	 Method: clear
	  Clear single cache item
	 
	 Access:
	  public
	 
	 Parameters:
	  key - _string_
	 
	 Returns:
	  _bool_
	*/
	public function clear($key) {
		return $this->cache->remove($key);
	}
	
	/*
	 Method: clean
	  Empty cache
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	*/
	public function clean() {
		return $this->cache->clean();
	}
	
}

?>