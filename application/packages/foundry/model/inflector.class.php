<?php
/*
 File: inflector
  Provides \foundry\model\inflector class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model;
use foundry\config as Conf;
use foundry\utility as Util;

/*
 Class: inflector
  Converts model strings into various forms
 
 Namespace:
  \foundry\model
*/
class inflector {

	/*
	 Parameter: singular
	  An array of plural to singular PCRE conversions
	 
	 Access:
	  public
	*/
	public static $singular = array(
		'/(database)s$/i'		=> '\1',
		'/(quiz)zes$/i'			=> '\1',
		'/(matr)ices$/i'		=> '\1ix',
		'/(vert|ind)ices$/i'	=> '\1ex',
		'/^(ox)en/i'			=> '\1',
		'/(alias|status)es$/i'	=> '\1',
		'/(octop|vir)i$/i'		=> '\1us',
		'/(cris|ax|test)es$/i'	=> '\1is',
		'/(shoe)s$/i'			=> '\1',
		'/(o)es$/i'				=> '\1',
		'/(bus)es$/i'			=> '\1',
		'/([m|l])ice$/i'		=> '\1ouse',
		'/(x|ch|ss|sh)es$/i'	=> '\1',
		'/(m)ovies$/i'			=> '\1ovie',
		'/(s)eries$/i'			=> '\1eries',
		'/([^aeiouy]|qu)ies$/i'	=> '\1y',
		'/([lr])ves$/i'			=> '\1f',
		'/(tive)s$/i'			=> '\1',
		'/(hive)s$/i'			=> '\1',
		'/([^f])ves$/i'			=> '\1fe',
		'/(^analy)ses$/i'		=> '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'			=> '\1\2sis',
		'/([ti])a$/i'			=> '\1um',
		'/(n)ews$/i'			=> '\1ews',
		'/(wo)?men$/i'			=>	'\1man',
		'/s$/i'					=> '',
	);
	
	/*
	 Parameter: plural
	  An array of singular to plural PCRE conversions
	 
	 Access:
	  public
	*/
	public static $plural = array(
		'/(quiz)$/i'			=> '\1zes',
		'/^(ox)$/i'				=> '\1en',
		'/([m|l])ouse$/i'		=> '\1ice',
		'/(matr|vert|ind)(?:ix|ex)$/i' =>  '\1ices',
		'/(x|ch|ss|sh)$/i'		=>  '\1es',
		'/([^aeiouy]|qu)y$/i'	=>  '\1ies',
		'/(hive)$/i'			=>  '\1s',
		'/(?:([^f])fe|([lr])f)$/i'	=> '\1\2ves',
		'/sis$/i'				=>  'ses',
		'/([ti])um$/i'			=>  '\1a',
		'/(buffal|tomat)o$/i'	=>  '\1oes',
		'/(bu)s$/i'				=>  '\1ses',
		'/(alias|status)$/i'	=>  '\1es',
		'/(octop|vir)us$/i'		=>  '\1i',
		'/(ax|test)is$/i' 		=>  '\1es',
		'/(wo)?man$/i'			=>	'\1men',
		'/s$/i'					=> 's',
	);

	/*
	 Method: singularize
	  Convert a plural noun into a singular noun
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function singularize($str) {
		// edge cases
		switch( $str ) {
			case 'moves':
				return 'move';
				break;
			case 'people':
				return 'person';
				break;
			case 'children':
				return 'child';
				break;
			case 'medium':
			case 'media':
				return 'media';
				break;
			case 'addresses':
			case 'address':
				return 'address';
				break;
			case 'bannedAddresses':
			case 'bannedAddress':
				return 'bannedAddress';
				break;
			case 'status':
				return 'status';
				break;
			
		}
	
		$count = 0;
		$data = preg_replace(array_keys(self::$singular), self::$singular,
			$str, 1, $count);
		
		if( $count > 1 ) {
			$data .= 's';
		}
		return $data;
	}
	
	/*
	 Method: pluralize
	  Convert a singular noun into a plural one
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function pluralize($str) {
		// edge cases
		switch( $str ) {
			case 'person':
				return 'people';
				break;
			case 'child':
				return 'children';
				break;
			case 'media':
			case 'medium':
			case 'medias':
				return 'media';
				break;
			case 'address':
			case 'addresses':
				return 'addresses';
				break;
			case 'bannedAddress':
			case 'bannedAddresses':
				return 'bannedAddresses';
				break;
		}
	
		$count = 0;
		$data = preg_replace( array_keys( self::$plural ), self::$plural,
			$str, 1, $count );

		if( $count == 0 ) {
			return $str.'s';
		}
		return $data;
	}
	
	/*
	 Method: tableize
	  Convert a noun into a table name
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::tableize('gryphon', 'article'); // gryphon_articles
	 
	 Access:
	  public
	 
	 Parameters:
	  ns - _string_ model namespace (package)
	  str - _string_ model name
	 
	 Returns:
	  _string_
	*/
	public static function tableize($ns, $str) {
		$str = self::singularize($str);
		return sprintf('%s_%s', $ns, self::pluralize($str));
	
	}
	
	/*
	 Method: classize
	  Convert a noun into a propery namespaced classname
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::classize('gryphon', 'articles'); 
	  > // \gryphon\model\article
	 
	 Access:
	  public
	 
	 Parameters:
	  ns - _string_ model namespace (package)
	  str - _string_ model name
	 
	 Returns:
	  _string_
	*/
	public static function classize($ns, $str) {
		return sprintf('\\%s\\model\\%s', $ns, self::singularize($str));
	}
	
	/*
	 Method: modelize
	  Convert a string into a model name
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::modelize('gryphon', '\\gryphon\\model\\article');
	  > // article
	  > echo Inflector::modelize('gryphon', 'gryphon_articles'); // article
	 
	 Access:
	  public
	 
	 Parameters:
	  ns - _string_ model's namespace (package)
	  str - _string_ table name, class name or plural noun
	 
	 Returns:
	  _string_
	*/
	public static function modelize($ns, $str) {
		$str = str_replace( array('\\', $ns.'model'), '', $str );
		
		return self::singularize(strtolower(preg_replace('/'.$ns.'_(.*)/',
			'$1', $str)));
	}
	
	/*
	 Method: foreignKeyize
	  Convert a noun into a foreign key
	 
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::foreignKeyIze('gryphon', 'article'); // article_id
	  
	 Access:
	  public
	 
	 Parameters:
	  ns - _string_ the model's namespace
	  str - _string_ a table name, class name, or noun
	 
	 Returns:
	  _string_
	*/
	public static function foreignKeyize($ns, $str) {
		return self::modelize($ns, $str).'_id';
	}
	
	/*
	 Method: lookupTableize
	  Convert two class names into a lookup table name. The table prefix will
	  always be the namespace of the first table alphabetically.
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::lookupTableize( '\\foo\\model\\article', 
	  > '\\bar\\model\\tags'); // foo_articlesTags
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_ first class name
	  str2 - _string_ second class name
	 
	 Returns:
	  _string_
	*/
	public static function lookupTableize($str, $str2) {
		
		$ns1 = Util::rootNamespace($str);
		$ns2 = Util::rootNamespace($str2);
		
		$model1 = self::pluralize(self::modelize($ns1, $str));
		$model2 = self::pluralize(self::modelize($ns2, $str2));
		
		$out = '';
		
		$i=0;
		$go = true;
		while( $go ) {
			if( $model1{$i} == $model2{$i} ) {
				$i++;
				continue;
			}
			
			if( ord($model1{$i}) < ord($model2{$i}) ) {
				$out = sprintf('%s_%s%s', $ns1, $model1, ucfirst($model2));
			} else {
				$out = sprintf('%s_%s%s', $ns2, $model2, ucfirst($model1));
			}
			
			$go = false;
		}
		
		return $out;
	}
	
	/*
	 Method: camelize
	  Convert a string to camel case
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::camelize('this_is some string'); // thisIsSomeString
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function camelize($str) {
		$parts = explode('_', $str);
		$out = '';
		foreach( $parts as $p ) {
			$out .= ucfirst($p);
		}
		
		return $out;
	}
	
	/*
	 Method: underscoreize
	  Convert a string to underscores
	  
	 Example:
	  > use foundry\model\inflector as Inflector;
	  > echo Inflector::underscoreize('thisIsSomeString'); 
	  > // this_is_some_string
	 
	 Access:
	  public
	 
	 Parameters:
	  str - _string_
	 
	 Returns:
	  _string_
	*/
	public static function underscoreize($str) {
		$str = strtolower(preg_replace('/([A-Z])/', '_$1', $str));
		if( strpos($str, '_') === 0 ) {
			$str = substr($str, 1);
		}
		
		return $str;
	}

}

?>