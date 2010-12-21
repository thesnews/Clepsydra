<?php
use foundry\config as Conf;
use foundry\db as DB;
use foundry\middleware as Middleware;
use foundry\fs\path as Path;
use foundry\fs\directory as Directory;

// initializes foundry and loads the configs
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

	if( isset($config['config-path']) &&
		!Path::isAbsolute($config['config-path']) ) {
		$config['config-path'] = Path::join($appRoot, $config['config-path']);
	}

	// first we load the default config file in
	// approot/config/NAMESPACE.config.php
	$cfgPath = $config['config-path'];
	$defaultPath = Path::join($cfgPath, $config['namespace'].'.config.php');
	if( file_exists($defaultPath) ) {
		require_once $defaultPath;
		$config = array_merge($config, \foundry\config\defaults());
	}

	$config = Conf::load($config);
	$config['packages'] = array();
	// the base config is cached
	if( !\foundry\config\cache::load() ) {
		
		// now we can load the individual package URL and CONFIG files
		foreach( \foundry\proc::getPackages() as $name => $p ) {
			$name = Directory::name($p);
			if( $name == 'foundry' ) {
				continue;
			}

			if( file_exists(Path::join($p, 'package.class.php')) ) {
				include_once Path::join($p, 'package.class.php');
				$config['packages'][] = $name;
				$cls = sprintf('\\%s\\package', $name);
				
				// first we need to ensure that the primary package has
				// primary context and everything else has secondary
				$ctx = 'secondary';
				if( $name == Conf::get('namespace') ) {
					$ctx = 'primary';
				}
				
				// some packages don't support one or the other context
				if( !$cls::supportsContext($ctx) ) {
					continue;
				}
				
				$cls::setContext($ctx);
				$config['routes'] = array_merge($cls::urls(),
					$config['routes']);
				$config['middleware'] = array_merge($cls::middleware(),
					$config['middleware']);
				$config['helpers'] = array_merge($cls::helpers(),
					$config['helpers']);

				// defaults are automatically namespaced to the package					
				if( isset($config[$name]) && is_array($config[$name]) ) {
					$config[$name] = array_merge($cls::defaults(),
						$config[$name]);
				} else {
					$config[$name] = $cls::defaults();
				}
			}
		}

		Conf::load($config);
		
		if( !defined('FOUNDRY_SCRIPT_MODE') ) {
			// now we cache for later
			\foundry\config\cache::store();
	
			// update relative web path for CLI scripts
			$temp = explode(__FRONTCONTROLLER_NAME,
				$_SERVER['REQUEST_URI']);
			if( strpos($temp[0], '?') !== false ) {
				$relPth = substr($temp[0], 0, strpos($temp[0], '?'));
			} else {
				$relPth = $temp[0];
			}

			file_put_contents(Path::join($config['private-path'], 
				sprintf('.%sRelativePath', $config['namespace'])),
				Path\dirSep.Path::standardize($relPth));
				
			file_put_contents(Path::join($config['private-path'], 
				sprintf('.%sPath', $config['namespace'])),
				__FRONTCONTROLLER_PATH);
		}		
	}

	DB::init(Conf::get('databases'));
	
	if( Conf::get('middleware') && is_array(Conf::get('middleware')) ) {
		require_once 'foundry/middleware/middleware.class.php';
		Middleware::register(Conf::get('middleware'));
	}

	if( !defined('FOUNDRY_SCRIPT_MODE') ) {
		// initialize the cache
		if( Conf::get('cache') ) {
			\foundry\cache::init();
		}
	}
};

?>