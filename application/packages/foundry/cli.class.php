<?php
/*
 File: cli
 Initializes and loads the interfaces required to make Foundry packages
 work on the command line. Useful for scripts and cronjobs.
 
 Example:
 (begin code)
	use foundry\cli as CLI;
	use foundry\proc as FProc;
	use foundry\cli\optionParser as OptParse;

	require_once realpath(dirname(__FILE__).'/../../foundry/cli.class.php');
	CLI\init(PACKAGENAME);
 (end)
 
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
use foundry\proc as FProc;
use foundry\fs\path as Path;

define('FOUNDRY_SCRIPT_MODE', 1);

require_once realpath(dirname(__FILE__).'/foundry.class.php');

/*
 Function: init
  Initialize the CLI for a specific package. The package must support primary
  context. Will simply exit on error.
  
 Parameters:
  pkg - _string_ the package name that will run in primary context
 
 Returns:
  _void_
  
 Namespace:
  \foundry\cli
*/
function init($pkg, $pth = false) {
	try {
//		$pth = 'private/.%s%sPath';
		if( !$pth ) {
			$pth = realpath(dirname(__FILE__).'/../../private');
		}
		
		$pth = Path::join($pth, '.%s%sPath');

		$absPath = file_get_contents(sprintf($pth, $pkg, ''));
		$relPath = file_get_contents(sprintf($pth, $pkg, 'Relative'));
		
		FProc::setRelativePath($relPath);
		FProc::setWebRoot($absPath);
		FProc::setPackage($pkg);
		
		include_once Path::join($absPath, 'bootstrap.php');
		include_once Path::join($absPath, 'foundry.config.php');

		FProc::setRequest(new \foundry\request('/'));
		
	} catch( \foundry\exception $e ) {
		echo "\nCLI Library failed to load with error:\n";
		echo $e->getMessage()."\n\n";
		exit(1);
	}
}

/*
 Function: ask
  Request user input for an interactive script
 
 (begin code)
  $resp = CLI::ask('Dogs or cats?', 'Dogs');
  if( $resp == 'dogs' ) {
  	echo 'Really?';
  }
 (end)
 
 Parameters:
  question - _string_ the question to ask
  default - _string_ (optional) the default answer (if the user presses return)
  helpText - _string_ (optional) sugar help text
 
 Returns:
  _string_ the response

 Namespace:
  \foundry\cli
*/
function ask($question, $default=false, $helpText=false) {

	if( $helpText ) {
		$helpText = ' ('.$helpText.')';
	}
	
	$defaultS = $default;
	if( $default ) {
		$defaultS = ' ['.$default.']';
	}
	
	$question = sprintf('%s%s%s ', $question, $helpText, $defaultS);
	
	fwrite(STDOUT, $question);
	
	$response = response();
	
	if( !$response || strlen( $response ) < 1 ) {
		return $default;
	}
	
	return $response;
}

/*
 Function: response
  Get and return response from STDIN
 
 Parameters:
  _void_
  
 Returns:
  _string_

 Namespace:
  \foundry\cli
*/
function response() {
	return trim(fgets(STDIN));
}

/*
 Function: puts
  Output a string or object as a string
 
 Parameters:
  s - _mixed_
  
 Returns:
  _void_

 Namespace:
  \foundry\cli
*/
function puts($s) {
	if( is_string($s) || is_numeric($s) ) {
		echo sprintf("%s\n", $s);
		return;
	}
	
	echo \foundry\utility::inspect($s);
	return;
}

/*
 Function: processExists
  Determine if the a process is running
 
 Parameters:
  _string_ process path
  
 Returns:
  _bool_

 Namespace:
  \foundry\cli
*/
function processExists($self) {
	if( \foundry\os::name() == 'darwin' ) {
		exec(sprintf('ps -c -o pid= | grep %s', $self), $pids);
	} else {
		exec(sprintf('ps -C %s -o pid=', $self), $pids);
	}
	
	if( count($pids) > 1 ) {
		return true;
	}
	
	return false;
}


?>