<?php
/*
 Title: <# subns #>\<# name #>

 Group: <# group #>
 
 File: <# name #>
  Provides <# class #> class
  
 Version:
  <# version #>
  
 Copyright:
  2004-<# year #> The State News, Inc
  
 Author:
  <# Your name #> <<# you #>@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

/*
  Please note that the arguments array can either be a key/value array of
  arguments OR a closure that returns a key/value array. The closure can be used
  to access the Registry or any other object/resources that can only be accessed
  after the Foundry environment has been initialized.
*/
$config = function() {
	return array(
		'<# package #>',
		'<# fullly namespaced job #>',
		<# arguments array #>		
	);
};

require_once realpath(dirname(__FILE__).'/../scripts/cli/runner.php');

?>