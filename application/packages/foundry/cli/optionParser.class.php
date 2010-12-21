<?php
/*
 File: optionParser
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
 
*/
namespace foundry\cli;

/*
 Class: optionParser
  Provides a convenient method for parsing command line options
 
 Namespace:
  \foundry\cli
 
 (start code)
  use foundry\cli\optionParser as OptParse
  ...
  $parser = new OptPArse;
  $parser->addOption('file', array(
  	'long' => '--file',
  	'short' => '-f',
  	default => 'empty file!'
  ));
  
  extract($oParser->parse($argv), EXTR_PREFIX_ALL, 'conf');

  echo $conf_file;
 (end)
*/
class optionParser {

	private $longArgs = array();
	private $shortArgs = array();
	
	private $arguments = array();
	
	public function __construct() {
	
	}
	
	/*
	 Method: addOption
	  Add a parse option. Parse options consist of an array of named keywords
	  in the form of
	  
	  (start code)
	  array(
	   'long' => _string_ // the long form
	   'short' => _string_ // the short form
	   'default' => _mixed_ // the default value
	  )
	  (end)
	 
	 Access:
	  public
	  
	 Parameters:
	  k - _string_ option name
	  v - _array_ array of options
	 
	 Returns:
	  _void_
	*/	  
	public function addOption($k, $v) {
		$this->arguments[$k] = $v;
		if( $v['long'] ) {
			$this->longArgs[$v['long']] = $k;
		} elseif( $v['short'] ) {
			$this->shortArgs[$v['short']] = $k;
		}
	}

	/*
	 Method: parse
	  Parse command line arguments (usually by passing $argv)
	 
	 > extract($oParser->parse($argv), EXTR_PREFIX_ALL, 'conf');
	 
	 Access:
	  public
	  
	 Parameters:
	  args - _array_
	 
	 Returns
	  _array_
	*/
	public function parse($args) {
		$ret = array();
	
		array_shift($args);
		foreach( $args as $arg ) {
			if( !($key = $this->getKey($arg)) ) {
				continue;
			}
			
			$ret[$key] = $this->getVal($key, $arg);
		}
		
		return $ret;
	}
	
	public function printHelp() {
	
	}
	
	/*
		Fetch key
	*/
	private function getKey($str) {
		if( substr($str, 0, 2) == '--' ) {
			if( strpos($str, '=') !== false ) {
				$parts = explode('=', $str);
				$str = $parts[0];
			}
			
			if( array_key_exists($str, $this->longArgs) ) {
				return $this->longArgs[$str];
			}
		} elseif( substr($str, 0, 1) == '-' ) {
			if( array_key_exists($str, $this->shortArgs) ) {
				return $this->shortArgs[$str];
			}

		}
		
		return false;
	}
	
	/*
	 Fetch value
	*/
	private function getVal($key, $arg) {
		if( substr($arg, 0, 2) == '--' ) {
			if( strpos($arg, '=') !== false ) {
				$parts = explode('=', $arg);
				return $parts[1];
			}
			
			if( isset($this->arguments[$key]['default']) ) {
				return $this->arguments[$key]['default'];
			}
			
			return null;
			
		} elseif( substr($arg, 0, 1) == '-' ) {
			if( isset($this->arguments[$key]['default']) ) {
				return $this->arguments[$key]['default'];
			}
			
			return true;
		}
		
		return false;
	}

}
?>