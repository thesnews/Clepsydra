<?php
/*
 File: file
  Provides \foundry\fs\file class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\fs;

/*
 Class: file
  A simple, standardized interface for dealing directly with files
 
 Namespace:
  \foundry\fs
*/
class file {

	/*
	 Method: path
	  Return the full base path of a file
	 
	 Example:
	 > echo File::path('/foo/bar/baz.txt'); // /foo/bar
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function path($str) {
		return dirname($str);
	}
	
	/*
	 Method: name
	  Return the filename for a given path

	 Example:
	 > echo File::name('/foo/bar/baz.txt'); // baz.txt
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function name($str) {
		return basename($str);
	}
	
	/*
	 Method: rootName
	  Returns the root name of a given path

	 Example:
	 > echo File::rootName('/foo/bar/baz.txt'); // baz
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function rootName($str) {
		if( strpos($str, '/') !== false ) {
			$str = self::name($str);
		}
		if( strrpos($str, '.') === false ) {
			return $str;
		}
		
		return substr($str, 0, strrpos($str, '.'));
	}
	
	/*
	 Method: extension
	  Return the file extension for a given path
	 
	 Example:
	 > echo File::extension('/foo/bar/baz.txt'); // txt

	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function extension($str) {
		$str = self::name($str);
		return substr($str, strrpos($str, '.')+1);
	}
	
	/*
	 Method: type
	  Attempt to determine the MIME type for a given file
	 
	 Example:
	 > echo File::type('/foo/bar/baz.txt'); // 'text/plain'

	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function type($str) {
		if( !file_exists($str) ) {
			return false;
		}

		$cmd = 'file -b --mime';
		$type = trim(exec($cmd.' '.escapeshellarg($str)));

		if( !$type ) {
			$type = trim(exec('file -b '.escapeshellarg($str)));
		}

		return strtolower($type);
	}
	
	/*
	 Method: size
	  Determine file size
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ file path
	  fmt - _bool_ (OPTIONAL) if TRUE, format size
	 Returns:
	  _int_
	*/
	public static function size($str, $fmt = false) {
		if( !is_readable($str) ) {
			return false;
		}
		
		$mod = '-sk';
		if( $fmt ) {
			$mod .= 'h';
		}
		$ret = \foundry\sys::exec("/usr/bin/du ".$mod." ".$str);
		$ret = explode("\t", $ret);
		return $ret[0];
	}
	
	/*
	 Method: info
	  Basically just stat
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _array_
	*/
	public static function info($str) {
		return stat($str);
	}
	
	public static function exists() {
	
	}
	
	public static function touch() {
	
	}
	
	public static function remove() {
	
	}
	
	/*
	 Method: standardize
	  Standardize a path by stripping spaces, slashes and any non file-system
	  safe characters
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function standardize($str) {
		return strtolower(preg_replace(
			array('/[^a-zA-Z0-9\._ ]/', '/\s{2,}/', '/\.{2,}/', '/ /'),
			array('', ' ', '.', '_'),
			trim($str)
		));
	}

}


?>