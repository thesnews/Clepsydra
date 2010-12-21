<?php
/*
 File: http
  Provides \foundry\http class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

namespace foundry\http;

/*
 Class: client
  A fiile pointer based HTTP client. For a much more reliable cURL based client
  please see <curl/http.class.php>
 
 Example:
 (start code)
  $client = new \foundry\http('http://foobar.com');
  $client->name = 'Mike';
  $client->hello = 'World';
  
  $rawResponse = $client->send('GET');
 (end)
 
 Namespace:
  foundry
*/
class client {
	private $url = false;
	private $headers = array();
	private $params = array();
	private $response = false;

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  url - _string_ url to connect to
	 
	 Returns:
	  _object
	*/
	public function __construct($url) {
		$this->url = $url;
	}
	
	/*
	 Method: send
	  Send the HTTP request
	 
	 Access:
	  public
	 
	 Parameters:
	  method - _string_ (optional) request method. Defaults to POST.
	 
	 Returns:
	  _string_ the HTTP request's response text
	*/
	public function send($method='POST') {
	
		$content = http_build_query($this->params);
		$length = strlen($content);
		
		if( !$this->getHeader('Content-Type') ) {
			$this->setHeader('Content-Type', 
				'text/plain');
		}
		
		$this->setHeader('Content-Length', $length);
		
		$headers = array();
		foreach( $this->headers as $k => $v ) {
			$headers[] = sprintf('%s: %s', $k, $v);
		}
		
		$headers = implode("\r\n", $headers);
		
		$context = array('http' => array(
			'method'	=> $method,
			'user_agent'=> 'Foundry API '.\foundry\VERSION,
			'header'	=> $headers,
			'content'	=> $content
		));
		
		$url = $this->url;
		if( $method == 'GET' && $content ) {
			$url = $this->url.'?'.$content;
		}
		
		$ctx = stream_context_create($context);
		$sock = fopen($url, 'r', false, $ctx);
		
		$res = '';
		if( $sock ) {
			while( !feof($sock) ) {
				$res .= fgets($sock, 4096);
			}
			fclose($sock);
		}
	
		return $res;	
	}
	
	/*
	 Method: setHeader
	  Set a HTTP header
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ the header name
	  v - _string_ the header value
	 
	 Returns:
	  _void_
	*/
	public function setHeader($k, $v) {
		$this->headers[$k] = $v;
	}
	
	/*
	 Method: getHeader
	  Get a header value
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ header name
	 
	 Returns:
	  _string_
	*/
	public function getHeader($k) {
		return $this->headers[$k];
	}

	/*
	 Overloaded getter and setter
	*/
	public function __get($k) {
		return $this->params[$k];
	}
	public function __set($k, $v) {
		$this->params[$k] = $v;
	}

}

?>