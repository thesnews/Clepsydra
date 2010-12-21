<?php
/*
 File: route
  Provides \foundry\request\route class
  
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
use foundry\fs\path as Path;

/*
 Class: route
  Manage route and controller connections
  Routes are in the form of:
   'package:controller' => 'url/:placeholder'
  
 Example:
 (start code)
 use foundry\request\route as Route;
 
 $r = new Route;
 $r->connect('gryphon:article', 'article/:year/:month/:slug');
 // connects gryphon's article controller to url, ':year', ':month', ':slug'
 // are GET array placeholders
 
 $r->connect('roost:main', 'housing/:type');
 // connects roost's main controller to url
 (end)
 
 Namespace:
  \foundry\request\route
*/
class route {

	private $routes = array();
	private $reverseRoute = array();

	/*
	 Method: connect
	  Connect a controller to a route
	 
	 Access:
	  public
	 
	 Parameters:
	  c - _string_ a namespaced controller ('package:controller')
	  route - _string_ the route
	 
	 Returns:
	  _void_
	*/
	public function connect($c, $route) {
		$parts = explode(':', $c);
		$ns = $parts[0];
		$c = $parts[1];
		
		$parts = explode('/', $route);
		
		$this->routes[$parts[0]] = array(
			'route'	=> $route,
			'controller'	=> $c,
			'package' => $ns,
			'parts'	=> $parts
		);
		$this->reverseRoute[$ns.':'.$c] = $parts[0];
	}
	
	/*
	 Method: process
	  Determine the route information for the passed URL
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ a URL
	 
	 Returns:
	  _array_ route information
	*/
	public function process($str) {
		$parts = explode('/', $str);
		
		if( !isset($this->routes[$parts[0]]) ) {
			return false;
		}

		$info = $this->routes[$parts[0]];
		if( !$info ) {
			return false;
		}
		
		$routeParts = $info['parts'];
		array_shift($routeParts);
		
		for( $i=1; $i<count($parts); $i++ ) {
			if( $routeParts[($i-1)] ) {
				$_GET[$routeParts[($i-1)]] = $parts[$i];
			}
		}
		
		return $info;
	}
	
	/*
	 Method: urlFor
	  Return url for given controller
	 
	 Access:
	  public
	 
	 Parameters:
	  c - _string_ namespaced controller ('package:controller')
	 
	 Returns:
	  _string_
	*/
	public function urlFor($c) {
		if( $this->reverseRoute[$c] ) {
			return $this->reverseRoute[$c];
		}
		
		return $c;
	}

}

?>