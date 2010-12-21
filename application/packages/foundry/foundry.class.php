<?php
/*
 File: foundry
  Provides base foundry class and initializes framework
  
 Version:
  6.0-alpha3 2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;
use foundry\event as Event;
use foundry\fs\path as Path;
use foundry\proc as FProc;
use foundry\fs\file as File;

const VERSION = '6.0B3-20101111 (mason)';

// add the application root to the include path
set_include_path(get_include_path().PATH_SEPARATOR.realpath(
	dirname(__FILE__).'/../'));
set_include_path(get_include_path().PATH_SEPARATOR.realpath(
	dirname(__FILE__).'/../../'));

// manually require Path because it's required for the autoloader
require_once 'config/config.class.php';
require_once 'fs/path.class.php';
require_once 'proc.class.php';
require_once 'exception/exception.class.php';
require_once 'fs/file.class.php';
require_once 'event.class.php';
require_once 'vendor/spyc.php';

// set exception handler
$oldHandler = set_error_handler(function($eno, $estr, $efile = '', 
	$eline = '') {

	if( error_reporting() == 0 ) {
		return;
	}
	
	throw new \foundry\exception($eno, 
		sprintf('%s [%s:%s]', $estr, $efile, $eline));
		
	return true;
}, E_ERROR | E_WARNING | E_PARSE);

// add the class/namespace autoload callback
spl_autoload_register(function($str) {
	if( strpos($str, '\\') !== false ) {
		$path = Path::join(FProc::getAppRoot(), 'packages',
			Path::join(explode('\\', $str)));

		if( file_exists($path.'.class.php') ) {

			// handle application/ns1/.../nsN/class.class.php
			require_once $path.'.class.php';
		} elseif( file_exists(Path::join($path,
			File::name($path).'.class.php')) ) {

			// handle application/ns1/.../nsN/class/class.class.php
			require_once Path::join($path, File::name($path).'.class.php');
		} else {
			throw new \foundry\exception('nullValue', $str.' not found');
		}
		
		if( function_exists($str.'\__init__') ) {
			// handles those lovely static __init__ calls
			call_user_func($str.'\__init__');
		}
	}

});


?>