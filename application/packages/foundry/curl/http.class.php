<?php
/*
 File: http
  Provides \foundry\curl\http class
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\curl;
use foundry\curl\exception as Ex;
use foundry\config as Conf;
use foundry\fs\path as Path;

/*
 Class: http
  cURL based HTTP client
  
 Example:
 (begin code)
	$client = new HTTPClient('http://someurl.com/api');
	$client->name = 'Mike';
	$client->hello = 'World';
	
	$client->get(); // performs a GET request with name=Mike&hello=World

	$raw = trim($client->getResponse());
	
	$client->close();
 (end)
  
 Namespace:
 	\foundry\curl\http
*/
class http {

	private $url = false;
	private $headers = array();
	private $params = array();
	
	private $handle = false;
	
	private $data = false;
	private $response = false;

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  url - _string_ URL to fetch
	  
	 Returns: 
	  _object_
	*/
	public function __construct($url) {
		$this->url = $url;
		$this->mode = $mode;
		
		$this->handle = curl_init();
	}

	/*
	 Method: setHeader
	  Set a HTTP header
	 
	 Access:
	  public
	 
	 Example:
	  > $client->setHeader('Content-Length', strlen($foo));
	 
	 Parameters:
	  k - _string_ the header name
	  v - _string_ the header value
	 
	 Returns:
	  _void_
	  
	 See Also:
	  <getHeader>
	*/
	public function setHeader($k, $v) {
		$this->headers[$k] = $v;
	}
	
	/*
	 Method: getHeader
	  Get a set header value
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ header name
	 
	 Returns:
	  _string_ header value
	 
	 See Also:
	  <setHeader>
	*/
	public function getHeader($k) {
		return $this->headers[$k];
	}

	/*
	 Method: setOption
	  Set a cURL option directly in the cURL handle
	 
	 Access:
	  public
	 
	 Parameters:
	  opt - _const_ a cURL option CONST
	  val - _mixed_ the option value to set
	 
	 Returns:
	  _void_
	*/
	public function setOption($opt, $val) {
		curl_setopt($this->handle, $opt, $val);
	}
	
	/*
	 Overloaded getter and setter methods
	*/
	public function __set($k, $v) {
		$this->params[$k] = $v;
	}
	public function __get($k) {
		return $this->params[$k];
	}

	/*
	 Method: get
	  Perform a GET request
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	 
	 See Also:
	  <post>
	*/
	public function get() {
		$this->setOption(CURLOPT_URL, sprintf('%s?%s', $this->url, 
			$this->prepareParams()));

		if( !$this->getHeader('Content-Type') ) {
			$this->setHeader('Content-Type', 'text/plain');
		}

		$this->setHeader('Content-Length', strlen($this->prepareParams()));

		return $this->send();
	}
	
	/*
	 Method: post
	  Perform a POST request
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	 
	 See Also:
	  <get>
	*/
	public function post() {
		$this->setOption(CURLOPT_POST, true);
		$this->setOption(CURLOPT_POSTFIELDS, $this->prepareParams());
		$this->setOption(CURLOPT_URL, $this->url);

		$this->setHeader('Content-Length', strlen($this->prepareParams()));

		return $this->send();
	}

	/*
	 Method: send
	  Perform the HTTP request. This method is called by <get> and <post>. You
	  shouldn't need to call this directly.
	 
	 Access:
	  public
	  
	 Parameters:
	  _void_
	  
	 Returns:
	  _bool_
	*/
	public function send() {

		if( count($this->headers) ) {
			$this->setOption(CURLOPT_HTTPHEADER, $this->headers);
		} else {
			$this->setOption(CURLOPT_HTTPHEADER, 0);
		}
		
		$this->setOption(CURLOPT_USERAGENT, 'Foundry API '.\foundry\VERSION);
		$this->setOption(CURLOPT_RETURNTRANSFER, true);
		
		
		$this->data = curl_exec($this->handle);
		if( $this->data === false ) {
			throw new Ex('curlError', sprintf('[%d] %s',
				curl_errno($this->handle), curl_error($this->handle)));
			
		}
		$this->close();
		
		return true;
	}

	/*
	 Method: getResponse
	  Return the raw response data
	 
	 Access: public
	
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getResponse() {
		return $this->data;
	}
	
	/*
	 Method: close
	  Close the HTTP connection
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function close() {
		curl_close($this->handle);
	}
	
	/*
	 Build and return a query string
	*/
	private function prepareParams() {
		return http_build_query($this->params);
	}
}
?>