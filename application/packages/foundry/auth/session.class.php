<?php
/*
 File: session
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
 
*/
namespace foundry\auth;
use foundry\auth\utility as AuthUtil;
use foundry\db as DB;
use foundry\config as Conf;

use foundry\filter as Filter;

/*
 Class: \session
  Provides a basic authenticated session, bypassing PHP's built in session
  handler.
 
 Namespace:
   \foundry\auth
*/
class session {

	private $dbh = false;
	private $acl = false;
	
	private $id = false;
	private $last_active = false;
	private $nonce = false;
	private $context = false;
	private $data = array();
	
	private $rowid = false;

	/*
	 Method: constructor
	  Object constructor
	  
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _object_ Session object
	*/
	public function __construct() {
		$this->dbh = DB::create('authSession', array(
			'driver'	=> 'sqlite',
			'host'		=> Conf::get('private-path'),
			'database'	=> 'authSession'
		));
		
		// SQLite only creates a table once, so just do this on every request
		// to make sure the table exists
		$q = 'create table session (last_active int, id varchar(32), '.
			'nonce int, context varchar(255), data text)';
		$this->dbh->exec($q);
		
	}

	/*
	 Method: create
	  Creates a new empty session
	  
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	*/
	public function create() {
		$this->id = md5(time()+rand());
		$this->nonce = AuthUtil::generateSalt();
		$this->data = array();
		$this->last_active = time();
		$this->context = Conf::get('namespace');
		
		$q = 'insert into session (last_active, id, nonce, context, data) '.
			'values (:la, :cid, :nid, :ctx, :dta)';
		$stmt = $this->dbh->prepare($q);
		$stmt->execute(array(
			':la'	=> $this->last_active,
			':cid'	=> $this->id,
			':nid'	=> $this->nonce,
			':ctx'	=> $this->context,
			':dta'	=> serialize(array())
		));

		$this->setCookie();

		$this->rowid = $this->dbh->lastInsertId();
	}
	
	/*
	 Method: verify
	  Verify that the current session is valid
	  
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _bool_ TRUE on valid, FALSE otherwise
	*/
	public function verify() {
		if( !$this->loadSession() ) {
			return false;
		}
		
		// first, make sure the session's context is the same as the current
		// application. this keeps a session created by one app from 
		// spilling over into others		
		if( $this->context != Conf::get('namespace') ) {
			$this->destroy();
			return false;
		}
		
		// session timeout
		$delta = strtotime('-1 hour');
		if( !$this->last_active || $this->last_active < $delta ) {
			$this->destroy();
			return false;
		}

		$this->nonce = AuthUtil::generateSalt();
		$this->last_active = time();
		
		$this->setCookie();

		return true;
	}
	
	/*
	 Method: __get
	  Overloaded get method
	  
	 Access:
	  public
	  
	 Parameters:
	  k - _string_ property
	  
	 Returns:
	  _mixed_ value
	*/
	public function __get($k) {
		return $this->data[$k];
	}
	
	/*
	 Method: __set
	  Overloaded set method
	  
	 Access:
	  public
	  
	 Parameters:
	  k - _string_ property
	  v - _mixed_ value
	  
	 Returns:
	  _mixed_ value
	*/
	public function __set($k, $v) {
		$this->data[$k] = $v;
		return $v;
	}
	
	/*
	 Method: loadSession
	  Loads session data from local store based on passed cookie key
	  
	 Access:
	  private
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _bool_
	*/
	private function loadSession() {
		if( !($cookie = $this->getCookie()) ) {
			return false;
		}
	
		$q = 'select rowid, last_active, id, nonce, context, data from session'.
			' where id = :cid and nonce = :nid and context = :xtc '.
			'order by rowid desc limit 1';

		$stmt = $this->dbh->prepare($q);
		// have to make sure this isn't cached
		$stmt->execute(array(
			':cid' => $cookie['id'],
			':nid' => $cookie['nonce'],
			':xtc' => Conf::get('namespace')
		), false, false);
		
		$row = $stmt->fetch();

		if( !$row || empty($row) ) {
			return false;
		}
		
		$this->id = $row['id'];
		$this->nonce = $row['nonce'];
		$this->last_active = $row['last_active'];
		$this->data = unserialize($row['data']);
		$this->context = $row['context'];
		
		$this->rowid = $row['rowid'];
		
		return true;

	}
	
	/*
	 Method: setCookie
	  Sets cookie key
	  
	 Access:
	  private
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	*/
	private function setCookie() {
		$name = AuthUtil::generateKeyName('f6a_');
		$value = sprintf('id=%s&nonce=%s', $this->id, $this->nonce);
		
		setcookie($name, $value, time()+3600, '/');
	}
	
	/*
	 Method: getCookie
	  Fetch cookie data (key)
	  
	 Access:
	  private
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _string_ authentication key an nonce
	*/
	private function getCookie() {
		$val = $_COOKIE[AuthUtil::generateKeyName('f6a_')];
		if( !$val ) {
			return false;
		}
		parse_str(trim($val), $args);
		if( !$args || empty($args) ) {
			return false;
		}
		
		return $args;
	}
	
	/*
	 Method: destroy
	  Destroy the session and unset all variables
	  
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	*/
	public function destroy() {
		if( $this->rowid ) {
			// delete the data
			$q = 'delete from session where rowid = :rid';
			$stmt = $this->dbh->prepare($q);
			$stmt->execute(array(':rid' => $this->rowid));
		}	

		$name = AuthUtil::generateKeyName('f6a_');
		
		setcookie($name, null, -3600, '/');

	}
	
	/*
	 Method: __destroy
	  Overloaded destructor method. Insures that the nonce is correctly set.
	  This is called automatically, you should never need to call this method.
	  
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	*/
	public function __destruct() {
		if( !$this->rowid ) {
			return false;
		}

		$data = serialize($this->data);
		
		$q = 'update session set last_active = :la, nonce = :nid, data = :dta '.
			'where rowid = :rid';
		$stmt = $this->dbh->prepare($q);
		$stmt->execute(array(
			':la'	=> $this->last_active,
			':rid'	=> $this->rowid,
			':nid'	=> $this->nonce,
			':dta'	=> serialize($this->data)
		));
		
	}
	

}
?>