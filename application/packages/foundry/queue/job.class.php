<?php
/*
 File: job
  Provides \foundry\queue\job class
  
 Version:
  2010.06.17
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\queue;

use foundry\fs\path as Path;
use foundry\config as Conf;
use foundry\proc as FProc;

require_once 'vendor/Textile.php';

/*
 Class: job
  All queue jobs inherit from this class.
 
 Namespace:
  \foundry\queue
*/
class job {

	/*
	 Parameter: arguments
	  _array_ of arguments passed to the queue
	 
	 Access:
	  protected
	*/
	protected $arguments = false;

	/*
	 Parameter: jobID
	  the current job's unique id
	 
	 Access:
	  protected
	*/
	protected $jobID = false;	

	/*
	 Parameter: handle
	  Database handle for the queue
	 
	 Access:
	  protected
	*/
	protected $handle = false;
	
	private $logHandle = false;
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  id - _string_
	  args - _string_
	  handle - _object_
	 
	 Returns:
	  _object_
	*/
	public function __construct($id, $args=false, $handle) {
		$this->arguments = json_decode($args, true);
		$this->jobID = $id;
		$this->handle = $handle;
		
		$path = Path::join(Conf::get('private-path'), FProc::getPackage()
			.'_queue.log');
		
		try {
			$this->logHandle = fopen($path, 'a');
		} catch( \foundry\exception $e ) {
			$this->logHandle = false;
		}
	}
		
	/*
	 Method: setUp
	  jobs can supersede 'setUp' to set up jobs before they're run
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function setUp() {
		return false;
	}
	
	/*
	 Method: tearDown
	  jobs can supersede 'tearDown' to cleanup anything after a job has run
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function tearDown() {
		return false;
	}
	
	/*
	 Method: run
	  Overriding 'run' is required as it is where your job is processed
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function run() {
		return false;
	}
	
	/*
	 Method: markFailed
	  Calling this will mark the job as failed. Automatically called when an
	  exception is thrown
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function markFailed($message=false) {
		$this->log($message."\n\t\tDebug: ".print_r($this->arguments, true));
	
		$q = 'update queue set status = :st where job_id = :jid';
		$stmt = $this->handle->prepare($q);
		$stmt->execute(array(
			':st' => '-1',
			':jid' => $this->jobID
		));
	}
	
	/*
	 Method: markCompleted
	  Calling this will mark the job as having completed successfully. You are
	  responsible for calling this when your job has run.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function markCompleted() {
		$q = 'update queue set status = :st where job_id = :jid';
		$stmt = $this->handle->prepare($q);
		$stmt->execute(array(
			':st' => '1',
			':jid' => $this->jobID
		));	}
	
	/*
	 Method: markRunning
	  Calling this will mark the job as currently running. This is called
	  automatically before your job is run
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function markRunning() {
		$q = 'update queue set status = :st where job_id = :jid';
		$stmt = $this->handle->prepare($q);
		$stmt->execute(array(
			':st' => '2',
			':jid' => $this->jobID
		));	}
	
	/*
	 Method: log
	  Log a message
	 
	 Access:
	  protected
	 
	 Parameters:
	  message - _string_
	 
	 Returns:
	  _void_
	*/
	protected function log($message) {
		if( !$this->logHandle ) {
			echo sprintf("%s\t%s: %s\n", date('r'), get_class($this), $message);
			return;
		}
		
		fwrite($this->logHandle, sprintf("%s\t%s: %s\n",
			date('r'), get_class($this), $message));
	}


	public function __destruct() {
		if( $this->logHandle ) {
			fclose($this->logHandle);
		}
	}
}


?>