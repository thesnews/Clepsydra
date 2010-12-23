<?php
/*
 Title: controller\main

 Group: Controllers
 
 File: controller.class.php
  Provides main controller class
  
 Version:
  2010.12.21
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace clepsydra\controller;
use foundry\model as M;
use foundry\config as Conf;
use foundry\request\url as URL;

/*
 Class: main
  Main controller
 
 Namespace:
  \clepsydra\controller
*/
class main extends \foundry\controller {


	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  request - _object_ request instance
	 
	 Returns:
	  _object_
	*/
	public function __construct($request) {
		parent::__construct($request);
	}

	
	/*
	 Method: main
	  main action
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_
	  
	   - refer - _string_ refering url
	*/
	public function main() {
			
		return array(
			'refer' => $this->request->get('refer', 'specialChars')
		);
	}
	
	/*
	 Method: login
	  Login action. Accepts via POST:
	  
	   - email - _string_
	   - passwd - _string_
	 
	  Action will forward to next action if authentication is successful,
	  otherwise it will return to the 'main' action with a 'message' and
	  'status' in the GET params.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - \foundry\response\redirect
	*/
	public function login() {
	
		$authSess = new \foundry\auth\session;

		$email = $this->request->post('email', 'email');
		$passwd = $this->request->post('passwd', 'specialChars');

		$refer = $this->request->get('refer', 'specialChars');

		if( !$email || !$passwd ) {
			$to = URL::build(URL::linkTo('clepsydra:main'), array(
				'message' => 'Email and password are required',
				'type' => 'error'
			));

			$resp = new \foundry\response\redirect($to);
			return $resp;
		}

		$user = M::init('clepsydra:person')
			->cache(false)
			->findByEmail($email)
			->pop();

		if( !$user->uid ) {
			// we don't let on that this is not a valid account
			$to = URL::build(URL::linkTo('clepsydra:main'), array(
				'message' => 'Email or password was not correct',
				'type' => 'error',
				'refer' => $refer
			));

			$resp = new \foundry\response\redirect($to);
			return $resp;
		}

		$timeout = 900;
		$attempts = 3;

		if( $user->locked > 0 && (time() - $user->locked) < $timeout ) {
			// locked and has not timed out
			// again, we don't let on that this is not valid
			$to = URL::build(URL::linkTo('clepsydra:main'), array(
				'message' => 'Email or password was not correct',
				'type' => 'error',
				'refer' => $refer
			));

			$resp = new \foundry\response\redirect($to);
			return $resp;
		}
		
		// must append the salt to the sent password for a match
		// why salt? see http://en.wikipedia.org/wiki/Salt_(cryptography)

		if( $user->password != md5( $passwd.$user->salt ) ) {
			\foundry\log::message('Invalid login: '.$user->name,
				\foundry\log\INFO);

			$user->attempts = $user->attempts + 1;
			\foundry\log::message('User locked: '.$user->name,
				\foundry\log\INFO);
				
			
			if( $user->attempts == $attempts ) {

				$user->locked = time();
			}
			
			$user->save();

			$to = URL::build(URL::linkTo('clepsydra:main'), array(
				'message' => 'Email or password was not correct',
				'type' => 'error',
				'refer' => $refer
			));

			$resp = new \foundry\response\redirect($to);
			return $resp;
		}

		// we have a good login
		
		// reset any attempts
		$user->attempts = 0;
		$user->locked = 0;
		
		$user->save();
		$authSess->create();
		
		$authSess->user = $user->uid;
		
		if( $refer ) {
			$refer = \foundry\fs\path::join(\foundry\proc::getRelativeWebRoot(),
				'index.php', $refer);
			$resp = new \foundry\response\redirect($refer);
			return $resp;
		}
		
		$resp = new \foundry\response\redirect(URL::linkTo('clepsydra:person'));
		return $resp;
			
	}

	/*
	 Method: logout
	  Logs the user out and destroys the auth session
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - \foundry\response\redirect
	*/
	public function logout() {
		$sess = new \foundry\auth\session;
		$sess->verify();
		$sess->destroy();
		

		$to = URL::build(URL::linkTo('clepsydra:main'), array(
			'message' => 'Logged out',
			'type' => 'success'
		));

		// unset the cache disable cookie
		$name = \foundry\auth\utility::generateKeyName('cleps1_');
		setcookie($name, null, time()-3600, '/');
		
		$resp = new \foundry\response\redirect($to);
		
		return $resp;
	}
	
	/*
	 Method: beat
	  Returns the current server timestamp. We can't rely on the client
	  computer's time... people will be tricky.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_
	  
	   - currently - _int_ timestamp
	*/
	public function beat() {
		return array(
			'currently' => time()
		);
	}


}

?>