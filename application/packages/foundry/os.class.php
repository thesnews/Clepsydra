<?php
/*
 File: os
  Provides \foundry\os class
  
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
 Class: os
  Provides access to OS related information and OS specific functions
 
 Namespace:
  \foundry
*/
class os {

	/*
	 Method: name
	  Return the os name (could be either 'unix', 'linux' or 'darwin')
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_ either 'unix', 'linux' or 'darwin'
	*/
	public static function name() {
		$type = strtolower(trim(exec('uname -a')));

		if( strpos($type, 'linux') !== false ) {
			return 'linux';
		} elseif( strpos($type, 'darwin') !== false ) {
			return 'darwin';
		} elseif( strpos($type, 'nix') !== false ) {
			return 'unix';
		}

		return false;	
	}
	
	/*
	 Method: dirSize
	  Calculate the total size of a given directory
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_ directory path
	 
	 Returns:
	  _int_
	*/
	public static function dirSize($path) {
		if( !is_readable($path) ) {
			return false;
		}
    
		$ret = exec( "/usr/bin/du -sk ".$path );
		$ret = explode( "\t", $ret );
		return $ret[0];	
	}
	
	/*
	 Method: serverName
	  Return the name of the server
	 
	 Example:
	  > echo \foundry\os::serverName(); // 'foo.bar.com'
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public static function serverName() {
		if( ($n = \foundry\config::get('env:hostname')) ) {
			return $n;
		}
	
 		if( isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] ) {
 			return $_SERVER['SERVER_NAME'];
 		}
 		
 		return trim(\foundry\sys::exec('hostname'));
	}
}
?>