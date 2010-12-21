<?php
/*
 File: proc
  Provides \foundry\proc class
  
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
use foundry\config as Conf;

/*
 Class: proc
  Provides standardized access to process related information, i.e. transitory
  information directly related to the current environment
 
 Example:
 (start code)
  echo \foundry\proc::getPackage(); // gryphon
 (end)
 
 Namespace:
  \foundry
*/
class proc {
	private static $webPath = false;
	private static $appRoot = false;
	private static $webRoot = false;
	private static $package = false;
	
	private static $request = false;

	/*
	 Method: setRequest
	  Attach the current request object to the process class. This should only
	  be done by the front controller or the main CLI script.
	 
	 Access:
	  public
	 
	 Parameters:
	  req - _object_ requst object
	 
	 Returns:
	  _void_
	*/
	public static function setRequest($req) {
		self::$request = $req;
		
		self::setPackage($req->package);
		self::setRelativePath($req->query->path);
	}
	
	/*
	 Method: setPackage
	  Set the current primary context package
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ the current package
	 
	 Returns:
	  _void_
	*/
	public static function setPackage($str) {
		self::$package = $str;
	}
	
	/*
	 Method: setRelativePath
	  Set the current path relative to the server root
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _void_
	*/
	public static function setRelativePath($str) {
		self::$webPath = $str;
	}
	
	/*
	 Method: setAppRoot
	  Set the full path to the application root
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _void_
	*/
	public function setAppRoot($str) {
		self::$appRoot = $str;
	}
	
	/*
	 Method: setWebRoot
	  Set the full path to the application's www root
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _void_
	*/
	public function setWebRoot($str) {
		self::$webRoot = $str;
	}

	/*
	 Method: getPackage
	  Return the current primary context package
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getPackage() {
		return self::$package;
	}

	/*
	 Method: getPackages
	  Returns a list of accessable packages. Packages prepended with an
	  underscore ('_') will not be included.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ containing key/value pairs in the form of:
	  
	   - package_name => /path/to/package
	*/
	public function getPackages() {

		$path = \foundry\fs\path::join(self::getAppRoot(), 'packages');

		$dir = dir($path);
		
		$out = array();
		
		while( ($e = $dir->read()) ) {
			if( strpos($e, '.') === 0 ||
				strpos($e, '_') === 0 ||
				!is_dir($path.'/'.$e) ) {
				continue;
			}
			
			$out[$e] = $path.'/'.$e;
		}
		$dir->close();

		return $out;
	}

	/*
	 Method: getCwd
	  Get the current working directory/application root
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function getCwd() {
		return self::appRoot();
	}

	/*
	 Method: getRequest
	  Return the current request object
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ the current request object
	*/
	public static function getRequest() {
		return self::$request;
	}

	/*
	 Method: getAppRoot
	  Return the current application root. Similar to getCwd except if no
	  application root is defined, it will attempt to pull it from the global
	  config system.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function getAppRoot() {
		if( self::$appRoot ) {
			return self::$appRoot;
		}
		
		return Conf::get('application-root');
	}
	
	/*
	 Method: getRelativeWebRoot
	  Get the path relative to the server root.
	  
	 Example:
	  > // your site lives at http://something.com/foo
	  > echo \foundry\proc::getRelativeWebRoot(); // '/foo'
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function getRelativeWebRoot() {
		return self::$webPath;
	}

	/*
	 Method: getWebRoot
	  Get the absolute file path to the webroot
	  
	 Example:
	  > // your site lives at http://something.com/foo
	  > // but your files are at /usr/local/documents
	  > echo \foundry\proc::getWebRoot(); // '/usr/local/documents'
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function getWebRoot() {
		if( self::$webRoot ) {
			return self::$webRoot;
		}
		
		if( defined('__FRONTCONTROLLER_PATH') ) {
			return __FRONTCONTROLLER_PATH;
		}
		
		return false;
	}

	/*
	 Method: getBin
	  Return the current PHP environment's binary executable path (i.e.
	  /usr/local/bin/php)
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function getBin() {
		if( Conf::get('env:php') ) {
			return Conf::get('env:php');
		}
		
		return \foundry\sys::exec('which php');
	}
}
?>