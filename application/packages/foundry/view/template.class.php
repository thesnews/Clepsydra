<?php
/*
 File: template
  Provides \foundry\template class
  
 Version:
  2010.06.03
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
  
 See Also:
  Twig - <http://www.twig-project.org/>
*/
namespace foundry\view\template {

	/*
	 Function: __init__
	  Autoload initializer. Sets up Twig template environment
	 
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	
	 Namespace:
	  \foundry\view\template
	*/
	function __init__() {
		require_once 'vendor/Twig/Autoloader.php';
		require_once 'foundry/view/helper.class.php';
				
		\Twig_Autoloader::register();
	}
}

namespace foundry\view {
	use foundry\fs\path as Path;
	use foundry\proc as FProc;
	use foundry\config as Conf;
	use foundry\view as View;
	use foundry\exception as MainException;
	use foundry\exception\view as ViewException;
	
	/*
	 Class: template
	  Initializes the Twig template system, locates the appropriate template
	  file and loads Foundry specific template extensions.
	  
	  This is generally called from custom views.
	 
	 Example:
	 (start code)
	  $tpl = new Template('path/to/template.tpl');
	  $res = new Response;
	  $res->content = $tpl->render($payload); 
	  return $res;
	 (end)
	 
	 Namespace:
	  \foundry\view
	*/
	class template {

		private $handler = false;
		private $template = false;
		
		private $templateDirectory = false;
		private $templateFile = false;
		
		private $templateConfig = array();
		
		private $cache;
		
		private $request;
		
		private $didFindTemplate = false;
		
		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  path - _string_ path to template file
		 
		 Returns:
		  _object_
		*/
		public function __construct($path) {
			$this->getPath($path);
					
			// respects debug config
			$this->cache = Path::join(Conf::get('private-path'), 'template');
			if( !file_exists($this->cache) ) {
				mkdir($this->cache);
			}
			if( Conf::get('debug') ) {
				$this->cache = false;
			}
		
			
		}
		
		/*
		 Method: render
		  Will attempt to process the requested template, making anything
		  passed in 'args' available to the template.
		 
		 Access:
		  public
		 
		 Parameters:
		  args - _array_ arguments to make available to the template
		 
		 Returns:
		  _string_
		*/
		public function render($args) {
			if( !$this->templateDirectory || !$this->templateFile ) {
				throw new MainException('nullValue', 'template not found');
			}
			$templatePaths = array_map(function($v) {
				if( !Path::isAbsolute($v) ) {
					$v = Path::join(FProc::getAppRoot(), $v);
				}
				
				return $v;
			}, Conf::get('templates'));
			array_push($templatePaths, 
				Path::join(FProc::getAppRoot(), 'macros'));

			$loader = new \Twig_Loader_Filesystem($templatePaths);

			$this->handler = new \Twig_Environment($loader, array(
				'cache' => $this->cache
			));
			$this->handler->addExtension(new \foundry\view\twig);
			
			$this->template = $this->handler->loadTemplate($this->templateFile);
			
			try {
				// initialize the helper array. helpers aren't actually
				// initialized until they're first used.
				
				helper::register('config', '\\foundry\\view\\helper\\config');
				helper::register('calendar',
					'\\foundry\\view\\helper\\calendar');
				foreach( Conf::get('helpers') as $k => $v ) {
					helper::register($k, $v);
				}

				return $this->template->render($args);
			} catch( \Twig_Error $ex ) {
				throw new ViewException( 'twigError', $ex->getMessage() );
			} catch( \Twig_SyntaxError $ex ) {
				throw new ViewException( 'twigSyntaxError', $ex->getMessage() );
			} catch( \Twig_RuntimeError $ex ) {
				throw new ViewException( 'twigRuntimeError',
					$ex->getMessage() );
			} catch( \Twig_Sandbox_SecurityError $ex ) {
				throw new ViewException( 'twigSandboxSecurityError',
					$ex->getMessage() );
			}
		}
		
		/*
		 Method: getPath
		  Attempt to determine the path to the appropriate template file.
		  The search order is as follows:
		  
		   - [application-root]/[package]/[controller]/[action].[requestType]
		   - [application-root]/[package]/[controller]/default.[requestType]
		   - [application-root]/[package]/[controller]/[action].tpl
		   - [application-root]/[package]/[controller]/default.tpl
		   
		 
		 Access:
		  public
		 
		 Parameters:
		  path - _string_ template path
		 
		 Returns:
		  _string_
		*/
		public function getPath($path) {
	
			$parts = explode('/', $path);
			$ctlr = array_shift($parts);
			$actn = implode( '/', $parts);
			$type = substr($actn, strrpos($actn, '.' )+1);
			$actn = str_replace('.'.$type, '', $actn);
	
			$appRoot = FProc::getAppRoot();
			
//			$pkg = FProc::getPackage();
			$pkg = FProc::getRequest()->package;
			
			foreach( Conf::get('templates') as $pth ) {
				$this->templateFile = false;
				if( !Path::isAbsolute($pth) ) {
					$pth = Path::join($appRoot, $pth);
				}
				if( !file_exists($pth) ) {
					continue;
				}
				$filePath = $pth;

				$ext = self::getExtensionForType($type);
		
				// first we try $filePath/$path/$action.$type
				$this->templateDirectory = realpath($filePath);
	
				$fullPath = Path::join($filePath, $pkg,	$ctlr, $actn.'.'.$ext);

				if( file_exists($fullPath) ) {
					$this->templateFile  = Path::join($pkg, $ctlr,
						$actn.'.'.$ext);
					$this->didFindTemplate = true;
					break;
				}
				
				// next we try $filePath/$path/default.$type
				$fullPath = Path::join($filePath, $pkg,	$ctlr, 'main.'.$ext);
				if( file_exists($fullPath) ) {
					$this->templateFile  = Path::join($pkg, $ctlr,
						'main.'.$ext);
					break;
				}
				
				// next we try $filePath/$path/$action.tpl			
				$fullPath = Path::join($filePath, $pkg,	$ctlr, $actn.'.tpl');
				if( $ext != 'tpl' && file_exists($fullPath) ) {
					$this->templateFile  = Path::join($pkg, $ctlr,
						$actn.'.tpl');
					break;
				}
				
				// finally, we try $filePath/$path/default.tpl			
				$fullPath = Path::join($filePath, $pkg,	$ctlr, 'main.tpl');
				if( $ext != 'tpl' && file_exists($fullPath) ) {
					$this->templateFile  = Path::join($pkg, $ctlr,
						'main.tpl');
					break;
				}
				
			}
			
			return false;
		}
	
		/*
		 Method: getExtensionForType
		  Get template extension for request type
		 
		 Access:
		  public
		 
		 Parameters:
		  type - _string_ request type
		 
		 Returns:
		  _string_ template extension (tpl, mbl, json, etc.)
		*/
		public function getExtensionForType($type) {
			switch($type) {
				case 'tpl':
				case 'htm':
				case 'html':
					return 'tpl';
					break;
				case 'mobile':
					return 'mbl';
					break;
			}
			
			return $type;
		}
		
		/*
		 Method: didFindTemplate
		  Returns TRUE if the _exact_ template string requested was found. FALSE
		  otherwise.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public function didFindTemplate() {
			return $this->didFindTemplate;
		}
	
	}
}

?>