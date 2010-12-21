<?php
/*
 File: fetch
  Provides \foundry\view\twig\fetch class
  
 Version:
  2010.06.03
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view\twig;

/*
 Class: fetch
  Defines the 'fetch' template tag that allows you to request model data
  directly from templates.
 
 Example:
 (start code)
  {% fetch foo as article with [
   'limit': 1,
   'order': 'created desc',
   'where': 'status = 1'
  ] %}

  {% fetch foo as article with [
   'limit': 12,
   'order': 'created desc',
   'where': 'status = 1'
   'withTags': ['frontpage', 'sports']
  ] %}
 (end)
 
 Namespace:
  \foundry\view\twig
  
 See Also:
  Twig <http://twig-project.org>
*/
class fetch extends \Twig_TokenParser {

	public function getTag() {
		return 'fetch';
	}
	
	public function parse(\Twig_Token $tok) {
		$lineNo = $tok->getLine();
		$stream = $this->parser->getStream();
		
		$target = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();

		$stream->expect(\Twig_Token::NAME_TYPE, 'from');
		
		$model = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
		if( $stream->test(\Twig_Token::OPERATOR_TYPE, ':') ) {
			$stream->expect(\Twig_Token::OPERATOR_TYPE);
			$actualModel = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
			
			$model = $model.':'.$actualModel;
		}
		
		$stream->expect(\Twig_Token::NAME_TYPE, 'with');
		
		$def = $this->parser->getExpressionParser()->parseExpression();

		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		
		return new \foundry\view\twig\fetchNode($target, $model, $def, $lineNo);
	}

}

?>