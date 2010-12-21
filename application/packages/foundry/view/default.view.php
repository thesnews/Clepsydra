<?php
/*
 File: default
  Provides default view callbacks
  
 Version:
  2010.05.27
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view;

use foundry\response as Response;
use foundry\fs\path as Path;
use foundry\view as View;
use foundry\view\template as Template;

/*
 Function: html
  Default HTML view callback
 
 Parameters:
  request - _object_ \foundry\request instances
  payload - _mixed_ data returned from controller
  kwargs - _array_ extra keyword arguments
  
 Returns:
  _object_ a \foundry\response instance

 Namespace:
  \foundry\view
*/
function html($request, $payload, $kwargs=array()) {

	// now load the new template
	$ext = 'tpl';
	if( $request->isMobile() ) {
		$ext = 'mbl';
	}
	$str = sprintf('%s/%s.%s', $request->controller,
		$request->action, $ext);

	$tpl = new Template($str);
	
	// create a new response and set the content to that of the template
	$res = new Response;
	
	// you can add stuff to payload, view helpers are added automatically
	$res->content = $tpl->render($payload); 

	// return the response
	return $res;
}

/*
 Function: json
  Default JSON view callback
 
 Parameters:
  request - _object_ \foundry\request instances
  payload - _mixed_ data returned from controller
  kwargs - _array_ extra keyword arguments
  
 Returns:
  _object_ a \foundry\response instance

 Namespace:
  \foundry\view
*/
function json($request, $payload, $kwargs=array()) {
	$g = new \foundry\model\generic;
	
	// first we load the payload data into a generic, remove any helpers
	// that have been injected along the way
	foreach( $payload as $k => $v ) {
		if( strpos($k, 'Helper') !== false ) {
			continue;
		}
		$g->$k = $v;
	}

	// jsonify it
	$res = new Response;
	$res->setHeader('Content-Type', 'application/json; charset=utf-8');
	$res->content = '['.$g->serialize('json').']';
	
	return $res;
}

/*
 Function: yaml
  Default YAML view callback
 
 Parameters:
  request - _object_ \foundry\request instances
  payload - _mixed_ data returned from controller
  kwargs - _array_ extra keyword arguments
  
 Returns:
  _object_ a \foundry\response instance

 Namespace:
  \foundry\view
*/
function yaml($request, $payload, $kwargs=array()) {
	$g = new \foundry\model\generic;
	
	// first we load the payload data into a generic, remove any helpers
	// that have been injected along the way
	foreach( $payload as $k => $v ) {
		if( strpos($k, 'Helper') !== false ) {
			continue;
		}
		$g->$k = $v;
	}

	// yaml it
	$res = new Response;
	$res->setHeader('Content-Type', 'text/plain; charset=utf-8');
	$res->content = $g->serialize('yaml');
	
	return $res;
}

/*
 Function: js
  Default JS view callback
 
 Parameters:
  request - _object_ \foundry\request instances
  payload - _mixed_ data returned from controller
  kwargs - _array_ extra keyword arguments
  
 Returns:
  _object_ a \foundry\response instance

 Namespace:
  \foundry\view
*/
function js($request, $payload, $kwargs=array()) {

	// why is this loaded via twig? cause we want to be able to use
	// the tempate directives
	$str = sprintf('%s/%s.js', $request->controller,
		$request->action);

	$tpl = new Template($str);
	
	// create a new response and set the content to that of the template
	$res = new Response;
	
	// you can add stuff to payload, view helpers are added automatically
	$res->content = $tpl->render($payload); 

	// return the response
	return $res;
}

?>