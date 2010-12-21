<?php
/*
 File: 404
  Specialized 404 handler
  
 Version:
  2010.11.10
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

/*
 For internal redirects
*/
$specializedRedir = array(
// Convenience redirects, internal
//	'keyword'		=> 'some/path',
	'sports' => 'section/sports'
);

/*
  For external redirects
*/
$externalRedir = array(
// Convenience redirects to other sites
//	'keyword'		=> 'http://www.path.com',
);

define('__FRONTCONTROLLER_PATH', dirname(__FILE__));
define('__FRONTCONTROLLER_NAME', basename(__FILE__));


use foundry\config as Conf;
use foundry\request as Request;
use foundry\request\url as URL;
use foundry\fs\path as Path;

use foundry\response\redirect as ResponseRedir;
use foundry\response\moved as ResponseMoved;

function foundry_init($config) {
	$appRoot = $config['application-root'];
	
	if( !file_exists($appRoot.'/packages/foundry/foundry.class.php') ) {
		throw new Exception('Foundry not found in '.$appRoot);
	}
	
	require_once $appRoot.'/packages/foundry/foundry.class.php';
	
	if( isset($config['private-path']) &&
		!Path::isAbsolute($config['private-path']) ) {
		$config['private-path'] = Path::join($appRoot, $config['private-path']);
	}
	
	Conf::load($config);
}

if( file_exists('./foundry.config.php') ) {
	include_once './foundry.config.php';
} else {
	throw new Exception('Config file not found');
}

$relPath = file_get_contents(Conf::get('private-path').'/.gryphonRelativePath');
$temp = explode($relPath, $_SERVER['REQUEST_URI'], 2);
$query = Path::standardize($temp[1]);

$controller = $query;
$remainder = false;
if( strpos($query, '/') !== false ) {
	$controller = substr($query, 0, strpos($query, '/'));
	$remainder = substr($query, strpos($query, '/')+1);
}

$baseURL = $relPath.'/index.php';

if( isset($specializedRedir[$controller]) ) {
	$resp = new ResponseMoved(Path::join($baseURL, 
		$specializedRedir[$controller], $remainder));
} elseif( isset($externalRedir[$controller]) ) {
	$resp = new ResponseRedir($externalRedir[$controller]);
} elseif( strpos($query, 'media') !== false ) {
	exit;
} else {
	$resp = new ResponseRedir(Path::join($baseURL, $query));
}

try {
	$resp->process();
} catch( \foundry\exception\halt $ex ) {

}
exit;

?>