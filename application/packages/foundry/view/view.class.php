<?php
/*
 File: view
  Provides \foundry\view class
  
 Version:
  2010.05.27
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;
use foundry\fs\path as Path;
use foundry\proc as FProc;
use foundry\config as Conf;

/*
 Class: view
  Provides methods for determining template files, paths and callbacks
 
 Namespace:
  \foundry
*/
class view {
	
	/*
	 Method: getCallback
	  Determine the view callback for the request.
	  Search order for callbacks is as follows:
	  
	   - \[package]\view\[controller]\[action]_[requestType]
	   - \[package]\view\[controller]\[requestType]
	   - \[package]\view\[controller]\[action]
	   - \[package]\view\[controller]\main
	   - \foundry\view\[requestType]
	 
	  Unless the request isn't a standard HTML request, then if the first two
	  steps fail, Foundry just right to the final step. This way you get access
	  to the standard output types for free.
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ foundry\request instance
	 
	 Returns:
	  _string_
	*/
	public static function getCallback($request) {

		$viewPaths = array_map(function($v) {
			if( !Path::isAbsolute($v) ) {
				$v = Path::join(FProc::getAppRoot(), $v);
			}
			
			return $v;
		}, Conf::get('templates'));
		
		array_push($viewPaths, 
			Path::join(FProc::getAppRoot(), FProc::getPackage()));
	
		$cls = sprintf('%s\\view\\%s', $request->package,
			$request->controller);

		require_once 'foundry/view/default.view.php';

		foreach( $viewPaths as $pth ) {
			$path = Path::join($pth, Path::fromNamespace($cls).'.view.php');
			if( !file_exists($path) ) {
				continue;
			}

			$actn = $request->action;
	
			require_once $path;
			// first pass is ns\view\controller\action_type
			$func = sprintf('\\%s\\%s_%s', $cls, $actn, $request->query->type);
			if( function_exists($func) ) {
				return $func;
			}

			// next is ns\view\controller\type
			$func = sprintf('\\%s\\%s', $cls, $request->query->type);
			if( function_exists($func) ) {
				return $func;
			}
			
			if( $request->query->type != 'html' && function_exists(
				sprintf('\\foundry\\view\\%s', $request->query->type)) ) {
			
				// this isn't a HTML request, so just use the default
				// because you should get the serializers for free
				break;
			}
			
			// that didn't work so try ns\view\controller\action
			$func = sprintf('\\%s\\%s', $cls, $actn);
			if( function_exists($func) ) {
				return $func;
			}
			
			// last try, ns\view\controller\DEFAULT
			$func = sprintf('\\%s\\main', $cls);
			if( function_exists($func) ) {
				return $func;
			}
			
			break;
		}
		// finally, just call the default for the type
		// form of 'foundry\view\TYPE (i.e. foundry\view\html)
		return sprintf('\\foundry\\view\\%s', $request->query->type);

	}

}
?>