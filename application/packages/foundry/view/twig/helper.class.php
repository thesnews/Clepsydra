<?php
/*
 File: helper
  Provides \foundry\view\twig\helper class
  
 Version:
  2010.10.26
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view\twig;

/*
 Class: helper
  Defines the 'helper' template tag that allows you to register a helper
  method in the current scope.
 
 Example:
 (start code)
  {% helper someHelper %}
  {% someHelper.foo() %}
  
  {% helper anotherHelper as foo %}
  {% foo.bar() %}
 (end)
 
 Namespace:
  \foundry\view\twig
  
 See Also:
  Twig <http://twig-project.org>
*/
class helper extends \Twig_TokenParser {

	public function getTag() {
		return 'helper';
	}
	
	public function parse(\Twig_Token $tok) {
		$lineNo = $tok->getLine();
		$stream = $this->parser->getStream();
		
		$alias = false;
		
		$target = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		
		if( $stream->test(\Twig_Token::NAME_TYPE, 'as') ) {
			$stream->expect(\Twig_Token::NAME_TYPE);
			$alias = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		}
		
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		return new \foundry\view\twig\helperNode($target, $alias, $lineNo);
	}

}

?>