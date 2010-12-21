<?php
/*
 File: bucket
  Provides \foundry\fs\bucket class
  
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
use foundry\fs\path as P;

/*
 Class: bucket
  Generates paths for an integer based filebucket system
  
 Example:
 (start code)
  use foundry\fs\bucket as Bucket;
  ...
  echo Bucket::buildPath('foo/bar', 10); // foo/bar/00/00/00/00
  echo Bucket::getPath('foo/bar', 123456); // foo/bar/00/00/12/34
 (end)
 
 Namespace:
  foundry\fs
*/

class bucket {

	/*
	 Method: buildPath
	  Generates a path based on padded integer. Will create any directories
	  on-the-fly.
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_ base path
	  uid - _int_ integer to base the path ON
	 
	 Returns:
	  _string_
	*/
	public static function buildPath($path, $uid) {
		while( strlen($uid) < 10 ) {
			$uid = '0'.$uid;
		}
		
		$path = P::join($path, substr($uid, 0, 2)).'/';
		
		if( !is_dir($path) ) {
			mkdir($path);
		}

		
		for( $i=2; $i<=6; $i+=2 ) {
			$path .= substr( $uid, $i, 2 ).'/';
			
			if( !is_dir( $path ) ) {
				mkdir( $path );
			}
		}
		
		return $path;
	}
	
	/*
	 Method: getPath
	  Like buildPath, but only generates the path string. It doesn't create
	  directories or check to see if the path exists.
	 
	 Access:
	  public
	 
	 Parameters:
	  uid - _int_
	 
	 Returns:
	  _string_
	*/
	public static function getPath($uid) {
		while( strlen($uid) < 10 ) {
			$uid = '0'.$uid;
		}
		
		$path = substr($uid, 0, 2).'/';
		
		for( $i=2; $i<=6; $i+=2 ) {
			$path .= substr( $uid, $i, 2 ).'/';
		}
		
		return $path;
	}
	
}

?>