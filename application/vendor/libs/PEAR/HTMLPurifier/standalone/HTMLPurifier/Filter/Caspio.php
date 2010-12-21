<?php

class HTMLPurifier_Filter_Caspio extends HTMLPurifier_Filter
{
    
    public $name = 'Caspio';
    
    public function preFilter( $html, $config, $context )
    {
        $pre_regex = '/<script[^>](.*) src\="http\:\/\/(([a-zA-Z0-9]*)\.caspio\.com([a-zA-Z0-9\.\/]*))(.*)f_cbload\("([a-zA-Z0-9]*)"(.*)<\/script>/';

        $pre_replace = '<span class="casp-embed">%\2%\6%</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter( $html, $config, $context )
    {
        $post_regex = '/<span class\="casp-embed">\%([a-zA-Z0-9\.\/]*)\%([a-zA-Z0-9]*)\%<\/span>/';

        $post_replace = '<script type="text/javascript" src="http://\1"></script><script type="text/javascript" language="javascript">try{f_cbload("\2","http:");}catch(v_e){;}</script>';

        return preg_replace($post_regex, $post_replace, $html);
    }
    
}

