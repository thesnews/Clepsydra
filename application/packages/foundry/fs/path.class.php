<?php
/*
 File: path
  Provides \foundry\fs\path class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\fs {

	use foundry\config as Conf;
	use foundry\os as OS;

	/*
	 Class: path
	  Provides standard interface to path manipulation functions
	 
	 Namespace:
	  \foundry\fs
	*/
	class path {
		
		/*
		 Method: join
		  Joins seperate path names into a complete whole. Accepts either a
		  range of strings or an array of strings.
		  
		 Example:
		 (start code)
		  use foundry\path as Path;
		  ...
		  Path::join('foo', '/bar/', '//baz/'); # /foo/bar/baz
		  Path::join(array('foo/bar', '///baz//')); # /foo/bar/baz
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_ ...
		 
		 Returns:
		  _string_
		*/
		public static function join() {
			$args = func_get_args();
			if( is_array($args[0]) ) {
				$args = $args[0];
			}

			$args = array_map(function($i) {
				return \foundry\fs\path::standardize($i);
			}, $args);
			
			$p= implode(path\dirSep, $args);
			
			if( strpos($p, path\dirSep) === 0 ) {
				return $p;
			}
			
			return path\dirSep.$p;
		}
		
		/*
		 Method: isAbsolute
		  Determine if given path is an absolute path
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_
		 
		 Returns:
		  _bool_
		*/
		public static function isAbsolute($str) {
			return( $str{0} == path\dirSep );
		}
		
		/*
		 Method: fromNamespace
		  Build a path based on a valid foundry namespace
		 
		 Access:
		  public
		 
		 Parameters:
		  ns - _string_
		 
		 Returns:
		  _string_
		*/
		public static function fromNamespace($ns) {
			return str_replace('\\', path\dirSep, $ns);
		}
		
		/*
		 Method: standardize
		  Standardize a give path by removing leading and trailing slashes and
		  extra spaces
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_
		 
		 Returns:
		  _string_
		*/
		public static function standardize($str) {
			if( $str == '/' ) {
				return '';
			}
		
			$len = strlen($str)-1;
			
			if( $len < 0 ) {
				return $str;
			}
			
			if( $str{$len} == path\dirSep ) {
				$str = substr($str, 0, $len);
			}
			
			if( $str{0} == path\dirSep ) {
				$str = substr($str, 1);
			}
			
/*			if( substr($str, -1, 1) == path\dirSep ) {
				$str = substr($str, 0, strlen($str) - 1);
			}
			
			if( substr($str, 0, 1) == path\dirSep ) {
				$str = substr($str, 1);
			}
*/			
			return $str;
		}		
	}
}

namespace foundry\fs\path {
	const dirSep = DIRECTORY_SEPARATOR;
}

?>