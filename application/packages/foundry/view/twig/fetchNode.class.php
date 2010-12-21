<?php
/*
 File: fetchNode
  Provides \foundry\view\twig\fetchNode class
  
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
 Class: fetchNode
  Performs the compilation of the 'fetch' tag
 
 Namespace:
  \foundry\view\twig
*/
class fetchNode extends \Twig_Node {
	
	public function __construct($target, $model, $def, $lineNo) {
		parent::__construct(array('def' => $def), array('target'=>$target, 'model'=>$model), $lineNo);

	}
	
	public function compile($c) {
		$handler = sprintf('$context[\'%s\'] = new %s("%s", ', $this['target'],
			'\\foundry\\view\\twig\\modelHandler', $this['model']);
	
		$c->addDebugInfo($this)->
			write($handler)->
			subcompile($this->def)->
			raw(");\n");
	}

}
?>