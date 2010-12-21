<?php
/*
 File: index
  Front controller for the Foundry package system
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

/*if( extension_loaded('xhprof') ) {
	include_once '/usr/lib/php/xhprof_lib/utils/xhprof_lib.php';
	include_once '/usr/lib/php/xhprof_lib/utils/xhprof_runs.php';
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}*/

// Allrigt, this is a bit different, so watch the comments

// this is required for any foundry front controller, assists with pathfinding
define('__FRONTCONTROLLER_PATH', dirname(__FILE__));
define('__FRONTCONTROLLER_NAME', basename(__FILE__));

// just a boat load of namespace aliases
use foundry\config as Conf;
use foundry\request as Request;
use foundry\response as Response;
use foundry\event as Event;
use foundry\timer as Timer;
use foundry\middleware as Middleware;
use foundry\view as View;
use foundry\fs\path as Path;
use foundry\request\url as URL;

use foundry\utility as Util;

include_once './bootstrap.php';

try {

	if( file_exists('./foundry.config.php') ) {
		include_once './foundry.config.php';
	} else {
		throw new Exception('Config file not found');
	}
	
	if( Conf::get('debug') ) {
		ini_set('display_errors', 1);
		error_reporting(E_ALL ^ E_NOTICE);
	}
	
	$t = new Timer('globalExecutionTimer');
	$t->start();

	// Request will process the URL and figure out what goes where and what
	// type of request we're dealing with (html, json, xml, css, etc...)
	$request = new Request($_SERVER['REQUEST_URI']);
	$response = false;
	
	// wire up the URLs
	if( Conf::get('routes') ) {
		foreach( Conf::get('routes') as $route => $url ) {
			$request->route->connect($route, $url);
		}
	}

	$request->processRoute();

	// call all middleware handleRequest methods
	// if any of them return a Response, we echo it and halt
	if( ($res = Middleware::handleRequest($request)) ) {
		$res->process();
		throw new \foundry\exception\halt;
	}
	
	$class = $request->package.'\\controller\\'.$request->controller;

	$c = new $class($request);

	// once the controller is initialized, we call middleware against it
	// nothing happens if middleware returns
	Middleware::handleController($request, $c);

	// wait for the controller to return something and figure out the view
	// callback
	$payload = $c->callAction($request->action);

	if( is_object($payload) && 
		strpos(get_class($payload), 'response') !== false ) {
		
		$payload->process();
		throw new \foundry\exception\halt;
	} elseif( $payload === false ) {
		throw new \foundry\exception\halt;
	}

	$callback = View::getCallback($request);

	// now we can the view middleware, if any middleware returns a Response
	// we echo it and halt
	if( ($res = Middleware::handleView($request, $callback, &$payload)) ) {
		$res->process();
		throw new \foundry\exception\halt;
	}

	// if a controller returns 'false' we halt automatically
	if( $payload !== false ) {
		
		// we call the view handler and get a response
		$response = call_user_func($callback, $request, $payload);
		
		// now we call the response middleware, if anything returns another
		// response, we echo that and halt
		if( $response && ($res = Middleware::handleResponse($request,
			$response)) ) {
			$res->process();
			throw new \foundry\exception\halt;
		}
		
		// if we have a response, deal with the headers and echo it
		if( $response ) {
			$response->process();
			
			if( $request->query->type != 'html' ) {
				throw new \foundry\exception\halt;
			}
			
		} else {
			// The view callback didn't return anything. This is a problem.
			throw new \foundry\exception('nullValue', 'Bad view response.');
		}
	} else {
		// halt
		throw new \foundry\exception\halt;
	}

} catch( \foundry\exception\view $ex ) {
	// this will be thrown if there is an error processing templates
	$exResponse = Middleware::handleException($request, $response, $ex);

	error_log('Foundry Exception: '.$ex->getMessage().' ('
		.$_SERVER['REQUEST_URI'].')');

	if( $exResponse ) {
		echo $exResponse;
	} else {
		echo $ex->getMessage();
	}
} catch( \foundry\exception\halt $ex ) {

	// is is the standard HALT method
	Event::fire('foundry', 'halt', array(
		'request' => $request
	));

	$haltResponse = Middleware::handleHalt($request);
	if( $haltResponse ) {
		echo $haltResponse;
	}
	exit;

} catch( \foundry\exception\auth $ex ) {
	// authentication errors
	
	$exResponse = Middleware::handleException($request, $response, $ex);

	error_log('Foundry Exception: '.$ex->getMessage().' ('
		.$_SERVER['REQUEST_URI'].')');

	if( $exResponse ) {
		echo $exResponse;
	} else {
		$ex->handlePretty($request);
	}
	exit;
} catch( \foundry\exception\db $ex ) {
	// database errors
	$exResponse = Middleware::handleException($request, $response, $ex);
	error_log('Foundry Exception: '.$ex->getMessage().' ('

		.$_SERVER['REQUEST_URI'].')');

	if( $exResponse ) {
		echo $exResponse;
	} else {
		$ex->handlePretty($request);
	}
	exit;
} catch( \foundry\exception\model $ex ) {
	// model errors
	$exResponse = Middleware::handleException($request, $response, $ex);

	error_log('Foundry Exception: '.$ex->getMessage().' ('
		.$_SERVER['REQUEST_URI'].')');

	if( $exResponse ) {
		echo $exResponse;
	} else {
		$ex->handlePretty($request);
	}
	exit;
} catch( \foundry\exception $ex ) {
	// general errors
	$exResponse = Middleware::handleException($request, $response, $ex);

	error_log('Foundry Exception: '.$ex->getMessage().' ('
		.$_SERVER['REQUEST_URI'].')');

	if( $exResponse ) {
		echo $exResponse;
	} else {
		$ex->handlePretty($request);
	}
	exit;
} catch( Exception $ex ) {
	// php errors
	$exResponse = Middleware::handleException($request, $response, $ex);
	if( $exResponse ) {
		echo $exResponse;
	}

	error_log('Foundry Exception: '.$ex->getMessage().' ('
		.$_SERVER['REQUEST_URI'].')');
	if( file_exists('./error.html') ) {
		include_once('./error.html');
	}
	exit;
}

echo "\n\n<!-- ".\foundry\db\pdo::$counter.' '.$t->stop()." -->";
/*if( extension_loaded('xhprof') && Conf::get('debug') == true ) {
//if( extension_loaded('xhprof') ) {
	$profiler_namespace = 'gryphon3';
	
	$xhprof_data = xhprof_disable();
	$xhprof_runs = new \XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
	
	$profiler_url = sprintf('http://ossian.statenews.com/~mike/xhprof/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
	
//	echo '<!-- '.$profiler_url.' -->';
	echo '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>';
}*/
?>