<?php
/*
 Title: job\queryQueue

 Group: Jobs
 
 File: queryQueue.class.php
  Provides queryQueue job class
  
 Version:
  2010.11.09
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\job;
use foundry\config as Conf;
use foundry\registry as Reg;

/*
 Class: queryQueue
  
   - query - _string_
   - binds - _array_
   - key - _string_
   - flags - _array_
   
 
 Namespace:
  \foundry\job
*/
class queryQueue extends \foundry\queue\job {

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