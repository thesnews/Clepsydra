<?php
/*
 File: filter
  Provides \foundry\filter class
  
 Version:
  2010.06.16
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

namespace foundry\filter {

	/*
	 Function: __init__
	  Autoload initializer
	 
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	  
	 Namespace:
	  \foundry\filter
	*/
	function __init__() {
//		\foundry\filter::init();
	}
}
namespace foundry {

	use foundry\config as Conf;
	use foundry\registry as Registry;
	use foundry\fs\path as Path;
	use foundry\proc as FProc;
	use foundry\fs\file as File;

	require_once 'vendor/Textile.php';

	/*
	 Class: filter
	  A shared filter instance.
	  
	 Examples:
	 (start code)
	  use foundry\filter as Filter;
	  
	  $clean_foo = Filter::alnum($foo);
	  $clean_bar = Filter::email($bar);
	 (end)
	 
	 Namespace:
	  \foundry
	*/
	class filter {
		private static $purifier = false;
		private static $textile = false;
		
		/*
		 Method: init
		  Initialize the filter and associated HTMLPurifier library. This is
		  called by the autoload initialize function. You only have to call
		  this method if you've directly loaded the class via a 'require' or
		  'import' statement.
		 
		 Access:
		  public
		 
		 Parameters:
		  force - _bool_ (optional) force a reaload of the HTMLPurifier library
		  strict - _bool_ (optional) force HTMLPurifier into strict mode
		 
		 Returns:
		  _void_
		  
		 See Also:
		  HTMLPurifier <http://htmlpurifier.org/>
		*/
		public static function init($force=false, $strict=false) {
			// initializes the html purifier object
			if( self::$purifier && !$force ) {
				return true;
			}
			
			require_once 'vendor/HTMLPurifier/HTMLPurifier.standalone.php';
	
			$config = \HTMLPurifier_Config::createDefault();
	
			$config->set('Cache.SerializerPath', Conf::get('private-path'));
			
			self::$purifier = new \HTMLPurifier($config);
			
			if( $strict ) {
				return;
			}
			
			$config = self::$purifier->config;
			$fltrs = array();
			
			// load custom filters		
			$filterPath = Path::join(FProc::getAppRoot(), 
				'vendor/HTMLPurifier/standalone/HTMLPurifier/Filter');
	
			$dir = \dir($filterPath);
	
			while( ($entry = $dir->read()) !== false ) {
				if( $entry == '.' || $entry == '..' ||
					$entry == 'ExtractStyleBlocks.php' ||
					strpos($entry, '.php') === false ) {
					continue;
				}
				
				$fileRoot = File::rootName($entry);
				require_once Path::join($filterPath, $entry);
				$cls = 'HTMLPurifier_Filter_'.$fileRoot;
				if( !class_exists($cls) ) {
					continue;
				}
				
				$fltrs[] = new $cls;
			}
			
			$config->set('Filter.Custom', $fltrs);
			$config->set('HTML.SafeObject', true );
			$config->set('HTML.SafeEmbed', true );
			
		}
	
		/*
		 Method: num
		  Filter and return only ints and floats
		 
		 Example:
		  > echo Filter::num('ab-32-vds.3432'); // 32.3432
		  > echo Filter::num('fdsafds$#@QAf'); // null
		 
		 Access:
		  public
		 
		 Parameters:
		  num - _mixed_
		 
		 Returns:
		  _int_ or _float_, will return null if no numbers are present
		*/
		public static function num($num) {
			if( is_numeric($num) ) {
				return $num;
			}
			
			return preg_replace('/[^0-9\.]/', '', $num);
		}
		
		/*
		 Method: bool
		  Returns a bool if passed value is a bool. Null otherwise
		 
		 Example:
		 (start code)
		  // all of the following will return TRUE
		  Filter::bool(1);
		  Filter::bool('1');
		  Filter::bool('Yes');
		  Filter::bool(true);

		  // all of the following will return FALSE
		  Filter::bool(0);
		  Filter::bool('0');
		  Filter::bool('No');
		  Filter::bool(false);
		  
		  // everything else evaluates to NULL
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  bool - _mixed_ 
		 
		 Returns:
		  _bool_ if argument evaluates as a boolean, null otherwise
		*/
		public static function bool($num) {
			if( $num === 1 || $num === true || strtolower($num) === 'yes' ||
				$num === 'true' || $num === '1' ) {
				return true;
			}

			if( $num === 0 || $num === false || strtolower($num) === 'no' ||
				$num === 'false' || $num === '0' ) {
				return false;
			}

			return null;
		}
	
		/*
		 Method: char
		  Filter and return a single character
		 
		 Examples:
		  (start code)
		   echo Filter::char('A'); // echo A
		   echo Filter::char('123ABC'); // echo A
		  (end)

		 Access:
		  public
		 
		 Parameters:
		  char - _mixed_ character to filter
		 
		 Returns:
		  _char_ a single char
		*/
		public static function char($char)
		{
			if( ctype_alpha($char) && strlen($char) == 1 ) {
				return $char;
			}
			
			$char = preg_replace('/[^a-zA-Z]/', '', $char);
	
			if( $char{0} ) {
				return $char{0};
			}
			
			return false;
		}
	
		/*
		 Method: alpha
		  Filter and return only alphabetic characters
		  
		 Examples:
		 (start code)
		  echo Filter::alpha( 'ABC' ); // echo ABC
		  echo Filter::alpha( '123ABC' ); // echo ABC
		  echo Filter::alpha( 'This is a test 123' ); // echo Thisisatest
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _mixed_
		 
		 Returns:
		  _string_
		*/
		public static function alpha($string)
		{
			return preg_replace(
				'/[^a-zA-Z_]/',
				'',
				$string
			);
		}
		
		/*
		 Method: alnum
		  Filter and return only alpha-numeric characters
		  
		 Example:
		 (begin code)
		  echo Filter::alnum( 'ABC' ); // echo ABC
		  echo Filter::alnum( '123ABC' ); // echo 123ABC
		  echo Filter::alnum( 'This is a test 123' ); // echo Thisisatest123
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function alnum($string)
		{
			return preg_replace(
				'/[^a-zA-Z0-9_]/',
				'',
				$string
			);
		}
		

		/*
		 Method: alnumExtend
		  Just like alnum except it also allows period, n-dash and underscore
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function alnumExtended($string)
		{
			return preg_replace(
				'/[^a-zA-Z0-9_\-\.]/',
				'',
				$string
			);
		}
	
		/*
		 Method: urlChars
		  Filters and returns only characters that are allowed in URLs. Please
		  note that this method DOES NOT verify that the string is a URL.
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function urlChars($string) {
			return preg_replace('/[^a-zA-Z0-9\/_\-]/', '', $string);		
		}
		
		/*
		 Method: stripP
		  Remove all P tags from a given string
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function stripP($str) {
			return trim(preg_replace('/\<(\/)?(p|P)( )?(\/)?\>/', '', $str));		
		}
	
		/*
		 Method: email
		  Filter and return a valid email address
		 
		 Access:
		  public
		 
		 Parameters:
		  email - _string_
		 
		 Returns:
		  _string_ properly formatted email
		*/
		public static function email($email) {
			$x = '\d\w!\#\$%&\'*+\-/=?\^_`{|}~';    //just for clarity
	
			if( count($email = explode('@', $email, 3)) == 2
				&& strlen($email[0]) < 65
				&& strlen($email[1]) < 256
				&& preg_match("#^[$x]+(\.?([$x]+\.)*[$x]+)?$#", $email[0])
				&& preg_match('#^(([a-z0-9]+-*)?[a-z0-9]+\.)+[a-z]{2,6}.?$#',
					$email[1]) ) {
				
				$email = implode('@', $email);
	
				return $email;
			}
	
			return false;
		}
		
		/*
		 Method: phone
		  Filter and return a properly formatted US phone number
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_ properly formatted phone number
		*/
		public static function phone($string) {
	
			// first, remove anything that isn't a number'
			$string = preg_replace('/[^0-9]/', '', $string);
		
			$fmt = 'X (XXX) XXX-XXXX';
			$min = 7;
			$max = 11;
		
			$len = strlen($string);
			
			if( $len > $max || $len < $min ) {
				return false;
			}
		
			// get all the special chars from the format
			$specials = preg_replace('/[^-\.\/\(\) \*_\[\]]/', '',  $fmt);
			
			$index = strrpos($fmt, 'X');
			
			// map the number onto the format in reverse
			for( $i=$len-1; $i>=0; $i-- ) {
				$fmt{$index} = $string{$i};
				
				$index = strrpos($fmt, 'X');
			}
		
			// now we have to trim the remainder format
			$newLen = strlen( $fmt );
			$trim = 0;
			for( $i=0; $i<$newLen; $i++ ) {
			
				// the current character is a special
				if( strpos( $specials, $fmt{$i} ) !== false ) {
				
					if( $fmt{($i+1)} == 'X' || 
						strpos( $specials, $fmt{($i+1)} ) !== false ) {
						// the next character is an 'X' or is another special character
						$trim++;
					}
					continue;
				}
				
				// format 'X' character
				if( $fmt{$i} == 'X' ) {
					$trim++;
					continue;
				}
				
				break;
			}
		
			
			return trim( substr( $fmt, $trim ) );
		
		}
		
		/*
		 Method: texilte
		  Filter and format a Textile string
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function textile($string) {
			if( !self::$textile ) {
				self::$textile = new \Textile;
			}

			return self::$textile->textileThis($string);
		}
		
		/*
		 Method: specialChars
  		   Filter and convert special characters and convert string to UTF-8
  		   encoding.
  		 
  		 Example:
  		 (start code)
 		  echo Filter::specialChars( '<b>"Hello World"</b>"' );	
		  // &lt;b&gt;&#034;Hello World&#034;&lt;/b&gt;
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_
		*/
		public static function specialChars($string) {
			$string = stripslashes(urldecode($string));
			
			return htmlspecialchars( $string, ENT_QUOTES, 'UTF-8', false );
		}
	
		/*
		 Method: stripTags
		  Strip all but allowed HTML tags.

		  Please note that this method (all of Foundry, in fact) assumes your
		  strings are UTF-8 encoded
		  
		 Example:
		 (start code)
		  echo Filter::specialChars( '<b>Hello<sc<script>ript>World</b>"' );	
		  // <b>HelloWorld</b>
		 (code)
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_

		 See Also:
		  HTMLPurifier <http://htmlpurifier.org/>
		*/
		public static function stripTags($string, $allow = false) {
			self::init();
			
			$string = stripslashes(urldecode($string));
	
			$config = null;
			$config = self::$purifier->config;
	
			return self::$purifier->purify( $string, $config );
		}
		public static function htmlPurify($string) {
			return self::$stripTags( $string );
		}
	
		/*
		 Method: stripTagsStrict
		  Like stripTags but removes ALL HTML
		 
		 Access:
		  public
		 
		 Parameters:
		  string - _string_
		 
		 Returns:
		  _string_

		 See Also:
		  HTMLPurifier <http://htmlpurifier.org/>
		*/
		public static function stripTagsStrict($string) {
//			$this->initializePurifier( $allow );
			self::init();
			
			$string = stripslashes( urldecode( $string ) );
			
			self::init(true, true);
	
			$config = null;
			$config = self::$purifier->config;
			$config->set('HTML.AllowedElements', array() );
	
			return self::$purifier->purify($string, $config);
		}
		
		/*
		 Method: arrayIntersect
		  Returns an array of values that are present in both arrays
		 
		 Access:
		  public
		 
		 Parameters:
		  input - _array_
		  check - _array_ array to check against
		 
		 Returns:
		  _array_
		*/
		public static function arrayIntersect($input, $check) {
			return array_intersect( $input, $check );					
		}
		

		/*
		 Method: arrayCallback
  		  Filters and returns array values based on a callback function
  		  
  		 Example:
  		 (start code)
  		  $arr = Filter::arrayCallback($_POST, function($v) {
  		    // do something
  		    return $v;
  		  });
  		 (end)
  		 
		 Access:
		  public
		 
		 Parameters:
		  array - _array_ the array to filter
		  callback - _mixed_ the callback to filter array with
		 
		 Returns:
		  _array_
		*/
		public static function arrayCallback($array, $callback) {	
			if( !is_array( $array ) ) {
				return array();
			}
			
			if( method_exists( '\\foundry\\filter', $callback ) ) {
				return mapRecursive(
					array( '\\foundry\\filter', $callback ), $array );
			}
			
			return mapRecursive( $callback, $array );
		}
		
		/*
		 Method: arrayFilter
		  Filters and returns array values based on a PCRE pattern
		  
		 Example:
		 (start code)
		  Filter::arrayFilter($_POST, '/[\w\d \. - \/]/');
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  array - _array_ array to filter
		  regex - _string_ the PCRE string to filter against
		 
		 Returns:
		  _array_
		*/
		public static function arrayFilter($array, $regex) {
			if( !is_array( $array ) ) {
				return array();
			}
	
			return array_map( 'arrayFilter', $array );
		}
		
	}

	function mapRecursive( $callback, $value )
	{
		$out = array();
		foreach( $value as $k => $v ) {
			if( is_array( $v ) ) {
				$out[$k] = mapRecursive( $callback, $v );
			} else {
				if( is_array( $callback ) ) {
					list( $obj, $method ) = $callback;
					$out[$k] = call_user_func($callback, $v);
				} else {
					$out[$k] = $callback( $v );
				}
			}
		}
		
		return $out;
	}
	
	function arrayFilter( $var )
	{
		return preg_replace( $regex, $var );
	}

}

?>