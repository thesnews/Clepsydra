<?php
/*
 File: exception
  Provides \foundry\exception.
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;

/*
 Class: exception
  This is the base exception class all other foundry exceptions inherit from.
 
 Namespace:
  \foundry
*/
class exception extends \Exception {

	/*
	 Parameter: type
	  Exception type
	 
	 Access:
	  public
	*/
	public $type = false;

	/*
	 Parameter: message
	  Exception message
	 
	 Access:
	  public
	*/
	public $message = false;

	/*
	 Parameter: backtrace
	  The backtrace from the time of the exception. Object list is *not*
	  populated.
	 
	 Access:
	  public
	*/
	public $backtrace = false;

	/*
	 Method: constructor
	  Object construct
	 
	 Example:
	 (start code)
	  use foundry\exception as Except;
	  ...
	  if( !$foo ) {
	    throw new Except('nullValue', 'Foo not found');
	  }
	 (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  type - _string_ (optional) the exception type
	  message - _string_ (optional) the exception message
	 
	 Returns:
	  _object
	*/
	public function __construct( $type='Unknown', $message='Unknown' ) {
		$this->type = $type;
		$this->message = $message;
		$this->backtrace = debug_backtrace(false);
	}
	
	/*
	 Method: getType
	  Get the exception type
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getType() {
		return $this->type;
	}
	
	/*
	 Method: handle
	  Handle the exception based on requst type (i.e. return an exception for
	  basic HTTP request, JSON object for a XMLHTTP request)
	 
	 Access:
	  public
	 
	 Parameters:
	  req - _object_ (optional) the current request object
	 
	 Returns:
	  _mixed_
	  
	   - _string_ if request is XHR and of the 'json' type
	   - _array_ for everything else, containing:

	     - message - _string_ the exception message
	     - backtrace - _array_ the backtrace from the time of the execption
	*/
	public function handle($req=false) {
		if( $req && $req->query->type ) {
			if( $req->query->type == 'json' ) {
				$out = array(
					'isError' => true,
					'type'	  => $this->type,
					'exception' => get_class($this),
					'message' => $this->message
				);
				
				echo json_encode($out);
				return;
			}
		}

		$message = sprintf('%s: %s', $this->type, $this->message);
		
		return array(
			'message' => $message,
			'backtrace' => $this->backtrace
		);
	}
	
	public function handlePretty($req=false) {
		$data = $this->handle($req);
		if( !is_array($data) ) {
			return;
		}
		
		$data['query'] = '/'.$req->query->query;
		$data['ua'] = $_SERVER['HTTP_USER_AGENT'];
		$data['type'] = get_class($this);
//		$data['backtrace'] = \foundry\utility::inspect($data['backtrace']);
		
		$ext = 'tpl';
		if( $req->isMobile() ) {
			$ext = 'mbl';
		}
		
		$str = sprintf('error/main.%s', $ext);
		$tpl = new \foundry\view\template($str);
		
		$res = new \foundry\response;

		try {
			$res->content = $tpl->render($data);
			$res->process();
		} catch( \foundry\exception $ex ) {
			echo \foundry\utility::inspect($data);
		}
		
		return;
	}
}

?>