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
namespace <# namespace #>\job;
use foundry\config as Conf;
use foundry\registry as Reg;

/*
 Class: <# class #>
  
   - <# argument #> - <# type #> <# description #>
   
 
 Namespace:
  \<# ns #>\job
*/
class <# name #> extends \foundry\queue\job {

	public function setUp() {
		$this->log('setup');
	}
	
	public function run() {
		$this->log('running');
	
	}
	
	public function tearDown() {
	
	}

}

?>