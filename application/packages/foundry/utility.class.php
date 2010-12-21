<?php
/*
 File: utility
  Provides \foundry\utility class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;

/*
 Class: utililty
  Provides misc utility methods
 
 Namespace:
  \foundry
*/
class utility {

	/*
	 Method: inspect
	  Return a preformatted string representation of a PHP construct
	  
	 Example:
	  > echo \foundry\utility::inspect($someMode);
	 
	 Access:
	  public
	 
	 Parameters:
	  obj - _mixed_ item to inspect
	 
	 Returns:
	  _string_
	*/
	public static function inspect($obj) {
		return '<pre>'.print_r($obj, true).'</pre>';
	}

	/*
	 Method: rootNamespace
	  Determines the root namespace of a given class name
	 
	 Example:
	  > echo \foundry\utility::rootNamespace('foo/bar/baz'); // foo
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ a class name
	 
	 Returns:
	  _string_
	*/
	public static function rootNamespace($str) {
		$parts = explode('\\', $str);
		if( !strlen($parts[0]) ){
			return $parts[1];
		}
		
		return $parts[0];
	}

	/*
	 Method: classNamespace
	  Determines the class name from the namespace string
	 
	 Example:
	  > echo \foundry\utility::classNamespace('foo/bar/baz'); // baz
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ a class name
	 
	 Returns:
	  _string_
	*/
	public static function classNamespace($str) {
		$parts = explode('\\', $str);
		
		return array_pop($parts);
	}

	/*
	 Method: normalize
	  Remove any non-alphanumeric characters, strip extra spaces and convert to
	  lowercase.
	  
	 Example:
	  > echo \foundry\utility::normalize('This is a 123      Test. & Stuff!');
	  > // this is a 123 test stuff
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public function normalize($str) {
		return strtolower(preg_replace(
			array('/[^a-zA-Z0-9\. ]/', '/\s{2,}/', '/\.{2,}/', '/ /'),
			array('', ' ', '.', '_'),
			$str
		));
	}

	/*
	 Method: clip
	  Generates an abstract

	  Attempts to seperate string by sentence then return an abstract trimmed to
	  the requested length. Optional third parameter will be filled with
	  the seperated sentences.

	  It will attempt to differentiate between Dr., Mrs., Mr., etc.. but
	  it's not perfect.
	 
	 Access:
	  public
	  
	 Parameters:
	  string - _string_
	  size - _int_ length of clipping
	  return - _array_ if passed will populate with the separate sentences.
	  
	 Returns:
	 	_string_
	*/
	public function clip($string, $size=100, &$return=array()) {
		$abstract = '';
	
		$return = preg_split( 
			'/\b((?<!Dr|Mr|Mrs|Ms|Inc|Co)\. |\! |\? )/', 
			$string, -1, PREG_SPLIT_DELIM_CAPTURE);
	
		for( $i=0; $i<count($return); $i+=2 ) {
			if( strlen( $abstract ) > $size ) {
				continue;
			}
			$sentence = $return[$i].$return[($i+1)];
			$abstract .= $sentence;
		}
		
		return rtrim($abstract);
	}

	/*
	 Method: tidy
	  A really, really bad pure-php implementation of tag balancing

	  Tidy not avaliable? Attempts to close tags left open by util_abstract
	 
	 Access:
	  public
	  
	 Parameters:
	  string - _string_ to tidy up
	  
	 Returns:
	  _string_
	*/
	public function tidy($string) {
	
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->loadHTML('<div>'.$string);
		
		return substr($doc->saveXML($doc->getElementsByTagName('div')
			->item(0)), 5, -6);
	}

}