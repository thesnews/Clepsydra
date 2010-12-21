<?php
/*
 File: twig
  Provides \foundry\view\twig class
  
 Version:
  2010.08.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
  
 See Also:
  Twig <http://twig-project.org>
*/
namespace foundry\view;
use foundry\request\url as URL;
use foundry\fs\path as Path;
use foundry\proc as FProc;
use foundry\model\inflector as Inflector;
use foundry\model as M;

/*
 Class: twig
  Initializes Foundry-specific Twig template extensions.
 
 Namespace:
  \foundry\view
*/
class twig extends \Twig_Extension {

	public function getFilters() {
		return array(
			'url' => new \Twig_Filter_Method($this, '_url'),
			'qualifiedUrl' => new \Twig_Filter_Method($this, '_qurl'),
			'timeSince' => new \Twig_Filter_Method($this, '_tsince'),
			'inject' => new \Twig_Filter_Method($this, '_inject'),
			'extract' => new \Twig_Filter_Method($this, '_extract'),
			'in' => new \Twig_Filter_Method($this, '_in'),
			'int2noun' => new \Twig_Filter_Method($this, '_int2noun'),
			'call' => new \Twig_Filter_Method($this, '_call'),
			'clip' => new \Twig_Filter_Method($this, '_clip'),
			'json' => new \Twig_Filter_Method($this, '_json'),
			'exists' => new \Twig_Filter_Method($this, '_exists'),
			'toTime' => new \Twig_Filter_Method($this, '_toTime'),

			'first' => new \Twig_Filter_Method($this, '_first'),
			'next' => new \Twig_Filter_Method($this, '_next'),
			'last' => new \Twig_Filter_Method($this, '_last')
		);
	}
	
	public function getTokenParsers() {
		return array(
			new \foundry\view\twig\fetch,
			new \foundry\view\twig\helper
		);
	}

	public function getName() {
		return 'foundryFilters';
	}
	
	/*
	 Method: _url
	  Provides the 'url' filter
	  
	 Example:
	  > {{ 'section/sports'|url }} // /path/to/index.php/section/sports
	  > {{ 'javascript/ui.js'|url }} // /path/to/javascript/ui.js
	 
	 Access:
	  public
	 
	 Parameters:
	  _string_ path to generate URL from
	 
	 Returns:
	  _string_
	*/
	public function _url() {
		$parts = array();
		$params = array();
		foreach( func_get_args() as $part ) {
			if( is_array($part) ) {
				$params = array_merge($params, $part);
			} else {
				$parts[] = $part;
			}
		}
		
		$str = implode('/', $parts);
		$params = http_build_query($params);
		
		// is already a fully qualified URL
		if( strpos($str, 'http') === 0 ) {
			return $str;
		}
		
		$path = URL::linkTo($str);
		if( $path == $str ) {
			return Path::join(FProc::getRelativeWebRoot(), $path);
		}
		if( $params ) {
			$path .= '?'.$params;
		}
		
		return $path;
	}
	
	/*
	 Method: _qurl
	  Provides 'qualifiedUrl' template filter
	  
	 Example:
	  > {{ 'section/sports'|qualifiedUrl }} 
	  > // http://foo.com/path/to/index.php/section/sports
	 
	 Access:
	  public
	 
	 Parameters:
	  _string_
	 
	 Returns:
	  _string_
	*/
	public function _qurl() {
		return URL::urlFor($this->_url(implode('/', func_get_args())));
	}

	/*
	 Method: _tsince
	  Provides timeSince template filter
	 
	 Example:
	  > {{ 1234566|timeSince }} // > 1 minute ago
	 
	 Access:
	  public
	 
	 Parameters:
	  _int_
	 
	 Returns:
	  _string_
	*/
	public function _tsince($v) {
		$now = time();
		
		$out = '';
		
		if( $now - $v < 60 ) {
			return $out.'< 1 minute ago';
		} elseif( $now - $v < 3600 ) {
			$str = $out.floor( ($now-$v)/60 );
			if( $str == 1 ) {
				return $str.' minute ago';
			}
			return $str.' minutes ago';
		} elseif( $now - $v < 86400 ) {
			$str = $out.floor( ($now-$v)/3600 );
			if( $str == 1 ) {
				return $str.' hour ago';
			}
			return $str.' hours ago';
		} else {
			return $out.date( 'm/d/y g:ia', $v );
		}
	}
	
	/*
	 Method: _inject
	  Provides the 'inject' template filter
	 
	 Example:
	  > {{ article.copy|inject('Something else') }}
	  > // will inject 'Something else' after the first paragraph of the
	  > // article copy
	 
	 Access:
	  public
	 
	 Parameters:
	  _string_ string to inject into
	  text - _string_ string to inject
	  skip - _int_ (optional) number of paragraphs to inject after	  
	 
	 Returns:
	  _string_
	*/
	public function _inject($inject, $text, $skip = 1) {
		$text = explode( '</p>', $text );
		if( count($text) <= $skip ) {
			return $text.$inject;
		}
		
		$top = array_slice($text, 0, $skip);
		$bottom = array_slice($text, $skip);
		
		return $top.$inject.$bottom;
	}
	
	/*
	 Method: _extract
	  Provides the 'extract' template filter.
	  
	 Example:
	  > {{ article.copy|extract(1) }} // extract the first paragraph of copy
	 
	 Access:
	  public
	 
	 Parameters:
	  _string_ to extract from
	  length - _int_ number of paragraphs to extract
	  offset - _int_ number of paragraphs to skip before extracting
	 
	 Returns:
	  _string_
	*/
	public function _extract($text, $length=0, $offset=0) {
		$text = explode('</p>', $text);
		if( !$length ) {
			$length = null;
		}
		
		$slice = array_slice($text, $offset, $length);
		return str_replace('</p></p>', '</p>', trim(implode('</p>',
			$slice).'</p>'));
	}
	
	/*
	 Method: _in
	  Provides the 'in' template filter. 'in' checks to see if the given value
	  is present in either an array or a string.
	  
	 Example:
	  > {% if 'foo'|in(someVar) %}
	  > ...
	 
	 Access:
	  public
	 
	 Parameters:
	  check - _mixed_ value to test for
	  item - _mixed_ item to text in, can be an _array_ or a _string_
	 
	 Returns:
	  _bool_
	*/
	public function _in($check, $item) {
	
		if( is_array($item) ) {
			return in_array($check, $item);
		} elseif( is_string($item) ) {
			if( strpos($item, $check) !== false ) {
				return true;
			}
			
			return false;
		}
		
		return null;
	
	}

	/*
	 Method: _int2noun
	  Provides the 'int2noun' template filter which converts an integer and a
	  noun into a human friendly string.
	  
	 Example:
	 > {{ article.commentTotal|int2noun('comment') }} // 12 comments
	 > {{ 0|int2noun('ox') }} // No oxen
	 
	 Access:
	  public
	 
	 Parameters:
	  int - _int_ the count
	  noun - _string_ the... noun
	 
	 Returns:
	  _string_
	*/
	public function _int2noun($int=0, $noun='item') {

		if( !$int ) {
			return sprintf('No %s', Inflector::pluralize($noun));
		} elseif( $int == 1 || $int == '1' ) {
			return sprintf('1 %s', Inflector::singularize($noun));
		} else {
			return sprintf('%d %s', $int, Inflector::pluralize($noun));
		}
	}
	
	/*
	 Method: _call
	  Provides 'call' filter method which allows you to call a method on an
	  unintialized foundry model.
	  
	 Example:
	  > {% set popular = 'article'|call('popular') %}
	 
	 Access:
	  public
	 
	 Parameters:
	  mod - _string_ model name
	  what - _string_ method to call
	  args - _array_ (OPTIONAL)
	 
	 Returns:
	  _mixed_
	*/
	public function _call($mod, $what, $args=array()) {
		$mod = M::init($mod);
		
		return call_user_func_array(array($mod, $what), $args);
	}

	/*
	 Method: _clip
	  Adds 'clip' template filter allowing you to clip a string to arbitrary
	  length.
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	  len - _int_
	 
	 Returns:
	  _string_
	*/
	public function _clip($str, $len=100) {
		if( strlen($str) < $len ) {
			return $str;
		}

		$out = '';
		foreach( explode(' ', $str) as $s ) {
			$out .= sprintf('%s ', $s);
			if( strlen($out) >= $len ) {
				break;
			}
		}
		
		return trim($out.'...');
	}
	
	/*
	 Method: _json
	  Adds 'json' template filter allowing you to convert PHP variables into
	  javascript objects.
	 
	 Access:
	  public
	 
	 Parameters:
	  obj - _mixed_
	 
	 Returns:
	  _string_ a JSON object
	*/
	public function _json($obj) {
		return json_encode($obj);
	}

	/*
	 Method: _exists
	  Determine if a given file path exists
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _boolean_
	*/
	public function _exists($str) {
		return file_exists($str);
	}
	
	/*
	 Method: _first
	  Return the first element of an array. Useful if the array doesn't have
	  numerical indexes.
	 
	 Example:
	  > {% set foo = image.meta|first() %}
	 
	 Access:
	  public
	 
	 Parameters:
	  array - _array_
	 
	 Returns:
	  _mixed_
	*/
	public function _first($arr) {
		return reset($arr);
	}
	
	/*
	 Method: _next
	  Return the next (as defined by the internal pointer) element of an array.
	  Useful if the array doesn't have
	  numerical indexes.
	 
	 Example:
	  > {% set foo = image.meta|first() %}
	  > {% set bar = image.meta|next() %}
	 
	 Access:
	  public
	 
	 Parameters:
	  array - _array_
	 
	 Returns:
	  _mixed_
	*/
	public function _next($arr) {
		return next($arr);
	}
	
	/*
	 Method: _last
	  Return the last element of an array. Useful if the array doesn't have
	  numerical indexes.
	 
	 Example:
	  > {% set foo = image.meta|last() %}

	 Access:
	  public
	 
	 Parameters:
	  array - _array_
	 
	 Returns:
	  _mixed_
	*/
	public function _last($arr) {
		return end($arr);
	}
	
	/*
	 Method: _toTime
	  Template alias for strtotime
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	  time - _int_ (OPTIONAL)
	  
	 Returns:
	  _signed int_
	*/
	public function _toTime($str, $time=false) {
		if( !$time ) {
			$time = time();
		}
		
		return strtotime($str, $time);
	}
}
?>