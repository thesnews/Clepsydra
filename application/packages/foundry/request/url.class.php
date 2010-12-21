<?php
/*
 File: url
  Provides \foundry\request\url class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\request;
use foundry\os as OS;
use foundry\fs\path as Path;
use foundry\proc as FProc;

/*
 Class: url
  Provides a simple interface to generating and manipulating URLS
 
 Namespace:
  \foundry\request
*/
class url {
	private static $request;

	/*
	 Method: build
	  Build an url based on individual components. Similar to 
	  foundry\fs\path::join()
	 
	 Example:
	 (start code)
	 $url = \foundry\request\url::build('/some/', 'path/to', 'index.php', array(
	 	'hello' => 'world',
	 	'name' => 'mike'
	 ));
	 echo $url; // 'some/path/to/index.php?hello=world&name=mike'
	 (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  _mixed
	 
	 Returns:
	  _string_
	*/
	public static function build() {
		$url = array();
		$params = array();
		foreach( func_get_args() as $arg ) {
			if( is_array($arg) ) {
				$params = array_merge($params, $arg);
			} else {
				$url[] = $arg;
			}
		}
		
		$outParams = '';
		
		if( count($params) ) {
		
			$params = array_map(function($v) {
				return urlencode($v);
			}, $params);
		
			$outParams = '/?'.http_build_query($params);
		}
		
		return implode('/', $url).$outParams;
	}

	/*
	 Method: linkTo
	  Generate correct internal URL based on the defined routes.
	  
	 Example:
	 (start code)
	  // given 'section' controller routes to 'section' url
	  echo foundry\request\url::linkTo('package:section/sports'); 
	  // index.php/section/sports
	  
	  // given 'admin' controller routes to '432fdsafe2qreafds323' url
	  echo foundry\request\url::linkTo('package:admin/foo');
	  // index.php/432fdsafe2qreafds323/foo
	  
	  // given 'article' is not defined
	  echo foundry\request\url::linkTo('package:article'); // package:article
	 (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_ you can prefix the path with a package name followed by a
	  colon. If you omit the package name, linkTo will assume the current
	  package
	  expand - _bool_ (optional) if TRUE, will expand the string into a fully
	  qualified URL (i.e. has http://server.tld prepended)
	 
	 Returns:
	  _string_
	*/
	public static function linkTo($path, $expand = false) {
		if( strpos($path, 'http://') !== false ) {
			return $path;
		}
		
		if( $path != '/' ) {
			$ctlr = $path;
			$remainder = '';
			if( strpos($path, '/') ) {
				$tmp = explode('/', $path, 2);
				$ctlr = $tmp[0];
				$remainder = $tmp[1];
			}
	
			if( strpos($ctlr, ':') === false ) {
				$ctlr = FProc::getPackage().':'.$ctlr;
			}
			
			if( !FProc::getRequest()->route ) {
				return $path;
			}
			
			$ctlr = FProc::getRequest()->route->urlFor($ctlr);
			if( strpos($ctlr, ':') !== false ) {
				// if no controller is found only the current context is passed
				// so return what was passed
				return $path;
			}
		} else {
			$str = '/';
		}
			
		$str = Path::join(FProc::getRelativeWebRoot(),
			__FRONTCONTROLLER_NAME,	$ctlr, $remainder);
		
		if( strpos($str, '//') !== false ) {
			$str = str_replace('//', '/', $str);
		}
		
		if( strpos($str, '?') !== false && substr($str, -1, 1) == '/' ) {
			return substr($str, 0, strlen($str)-1);
		}

		if( $expand ) {
			$sn = OS::serverName();
			$prot = 'http';
			if( strpos($_SERVER['SCRIPT_URI'], 'https://') !== false ) {
				$prod .= 's';
			}
			
			$str = sprintf("%s:/%s", $prot, Path::join($sn, $str));
		}
		return $str;
	}

	/*
	 Method: urlFor
	  Return a fully quialified url
	 
	 Example:
	  > echo \foundry\request\url::urlFor('foo/bar');
	  > // http://foo.bar.com/foo/bar
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_
	 
	 Returns:
	  _string_
	*/
	public static function urlFor($path) {
		$sn = OS::serverName();
		$prot = 'http';
		if( strpos($_SERVER['SCRIPT_URI'], 'https://') !== false ) {
			$prod .= 's';
		}
		if( strpos($path, FProc::getRelativeWebRoot()) === 0 ) {
			// already contains the rel path
			return sprintf('%s://%s', $prot, $sn.$path);
		}
		return sprintf('%s://%s', $prot, 
			$sn.Path::join(FProc::getRelativeWebRoot(), $path));
	}	
}
?>