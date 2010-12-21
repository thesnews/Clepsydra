<?php

class HTMLPurifier_Filter_TwitterWidget extends HTMLPurifier_Filter
{
    
    public $name = 'TwitterWidget';
    
    private static $dataCache = array();
    
    public function preFilter($html, $config, $context) {
    	if( strpos($html, 'TWTR.Widget({') === false ) {
    		return $html;
    	}
    
    	$data = substr($html, strpos($html, 'TWTR.Widget('));
    	$data = substr($data, 0, strpos($data, '.start();')+9);
    
    	if( !$data ) {
    		return $html;
    	}
    	
    	$k = md5($data);
    	
    	file_put_contents('/tmp/twitter.txt', $data);
    	
    	self::$dataCache[$k] = $data;
		
		$offset = '<script src="http://widgets.twimg.com';
		
		if( strpos($html, '<notextile><script src="http://widgets.twimg.com')
			!== false ) {
			
			$offset = '<notextile><script src="http://widgets.twimg.com';
		}
		$html = substr($html, 0, 
			strpos($html, $offset)-1).$k.
			substr($html, strpos($html, $offset));
		
		return $html;
    }
    
    public function postFilter($html, $config, $context) {

		foreach( self::$dataCache as $k => $v ) {
			if( strpos($html, $k) === false ) {
				continue;
			}
			
			$v = "<notextile><script src=\"http://widgets.twimg.com/j/2/widget.js\">".
				"</script>\n<script>\nnew ".$v."\n</script></notextile>";
			
			$html = str_replace($k, $v, $html);
		}
		
		return $html;
    }
    
}

