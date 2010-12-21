<?php
/*
 File: directory
  Provides \foundry\fs\directory class
  
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
use foundry\fs\path as Path;

/*
 Class: directory
  A simple interface for manipulating directories
 
 Namespace:
  \foundry\fs
*/
class directory {

	/*
	 Method: contents
	  Enumerate the contents of a directory. Ignores any 'dotfiles'.
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ the directory path
	 
	 Returns:
	  _array_
	*/
	public static function contents($str) {
		$contents = array();
		$dir = dir($str);
		while( ($entity = $dir->read()) ) {
			if( strpos($entity, '.') === 0 ) {
				continue;
			}
			$contents[] = Path::join($str, $entity);
		}
		
		return $contents;
	}

	/*
	 Method: name
	  Get the directory name from a given path
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function name($str) {
		$str = explode('/', $str);
		return array_pop($str);
	}
	
	/*
	 Method: contains
	  Determine if directory contains named path
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ directory
	  file - _string_ file to check for
	 
	 Returns:
	  _bool_
	*/
	public static function contains($str, $file) {
		$dir = dir($str);
		while( ($entity = $dir->read()) ) {
			if( strpos($entity, '.') === 0 ) {
				continue;
			}
			if( $entity == $file ) {
				return true;
			}
		}
		
		return false;
		
	}

	/*
	 Method: chmodr
	  Recursively chmod a directory's contents. Second parameter is an array
	  containing octals to set the files and/or directories to.
	 
	 Example:
	  (start code)
	   \foundry\fs\directory::chmodr('path/to/dir', array(
	     'files' => 0664,
	     'directories' => 0775
	   ));
	  (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ directory path
	  perms - _array_ array containing permission octals
	 
	 Returns:
	  _void_
	*/
	public static function chmodr($str, $perms) {
		$dir = dir($str);
		while( ($entity = $dir->read()) ) {
			if( strpos($entity, '.') === 0 ) {
				continue;
			}
			
			if( array_key_exists('files', $perms) &&
				is_file($str.'/'.$entity) ) {
				
				chmod($str.'/'.$entity, $perms['files']);
			} elseif( is_dir($str.'/'.$entity) ) {
				
				if( array_key_exists('directories', $perms) ) {
					chmod($str.'/'.$entity, $perms['directories']);
				}
				
				self::chmodr($str.'/'.$entity, $perms);
			}
		}
	}
	
	/*
	 Method: size
	  Determine total directory size
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ directory path
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
	
}

?>