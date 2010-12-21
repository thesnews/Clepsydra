<?php
/*
 File: sys
  Provides \foundry\sys class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;

/*
 Class: sys
  Provides system specific methods for interacting with the command line
 
 Namespace:
  \foundry
*/
class sys {

	/*
	 Method: exec
	  Execute a command. Returns the last line of the program output.
	  
	 Example:
	 (start code)
	  $cmd = 'ls -la';
	  $output = '';
	  \foundry\sys::exec($cmd, $output);
	 (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  cmd - _string_ the command to execute
	  op - _string_ (optional) variable to capture the complete output
	  ret - _int_ (optional) variable to capture the return status
	 
	 Returns:
	  _string_
	*/
	public static function exec($cmd, &$op = false, &$ret = false) {
		return exec(escapeshellcmd($cmd), $op, $ret);
	}
	
	/*
	 Method: execnb
	  Similar to <exec> except this performs in non-blocking mode, meaning that
	  this method doesn't wait for the command to return. As such you cannot
	  capture the return value or exit code. This is useful for running complex
	  or long-running commands.
	 
	 Access:
	  public
	 
	 Parameters:
	  cmd - _string_ command to execut
	 
	 Returns:
	  _void_
	*/
	public static function execnb($cmd) {
		$cmd = escapeshellcmd($cmd);
		exec($cmd.' > /dev/null 2>&1 &'); 	
	}

}
?>