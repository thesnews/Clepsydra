<?php
/*
 File: query
  Provides \foundry\request\query class
  
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
 Class: query
  Parses the current request's query string
 
 Namespace:
  \foundry\request\
*/
class query {

	/*
	 Parameter: path
	  The absolute request path
	 
	 Example:
	 (start code)
	  use foundry\request\query as Query;

	  $q = new Query($_SERVER['REQUEST_URI']);
	  #given http://something.com/index.php/foo/bar?baz=yes
	  echo $q->path; // '/'

	  #given http://something.com/someapp/index.php/foo/bar?baz=yes
	  echo $q->path; // '/someapp'
	 (end)
	 
	 Access:
	  public
	*/
	public $path = false;

	/*
	 Parameter: query
	  The query path
	 
	 Example:
	 (start code)
	  use foundry\request\query as Query;
	 
	  $q = new Query($_SERVER['REQUEST_URI']);
	  #given http://something.com/index.php/foo/bar?baz=yes
	  echo $q->query; // 'foo/bar'
	 (end)
	 
	 Access:
	  public
	*/
	public $query = false;

	/*
	 Parameter: params
	  The GET parameters
	 
	 Example:
	 (start code)
	  use foundry\request\query as Query;
	 
	  $q = new Query($_SERVER['REQUEST_URI']);
	  #given http://something.com/index.php/foo/bar?baz=yes
	  print_r($q->params); // array( 'baz' => 'yes' )
	 (end)
	 
	 Access:
	  public
	*/
	public $params = array();
	
	/*
	 Parameter: types
	  Valid request types
	 
	 Access:
	  public
	*/
	public $types = array(
		'htm', 'html', 'xml', 'json', 'rss', 'atom', 'js', 'manifest', 'css',
		'yaml'
	);

	/*
	 Parameter: type
	  Current request type
	 
	 Access:
	  public
	*/
	public $type = false;

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ request query string
	 
	 Returns:
	  _object_
	*/
	public function __construct($str) {
		$temp = explode(__FRONTCONTROLLER_NAME, $str, 2);
		$str = $temp[1];
		
		if( strpos($temp[0], '?') !== false ) {
			$this->path = Path\dirSep.Path::standardize(substr(
				$temp[0], 0, strpos($temp[0], '?')));
		} else {
			$this->path = Path\dirSep.Path::standardize($temp[0]);
		}
		
		foreach( $this->types as $type ) {
			if( strpos($str, '.'.$type) !== false ) {
				$str = str_replace('.'.$type, '', $str);
				$this->type = $type;
				break;
			}
		}

		if( strpos($str, '?') !== false ) {
			$temp = explode('?', $str);
			
			$this->query = Path::standardize($temp[0]);
			
			$out = array();
			foreach( explode('&', $temp[1]) as $part ) {
				$part = explode('=', $part);
				$out[$part[0]] = $part[1];
			}
			
			$this->params = $out;
		} else {
			$this->query = Path::standardize($str);
		}

		
		if( !$this->type || $this->type == 'htm' ) {
			$this->type = 'html';
		}
		
		
	}
	
	public function __toString() {
		$str = \foundry\fs\path::join($this->path, $this->query);
		if( count($this->params) ) {
			$str .= '?'.http_build_query($this->params);
		}
		
		return $str;

	}
}

?>