<?php

class HTMLPurifier_Filter_MyStudiyo extends HTMLPurifier_Filter
{
    
    public $name = 'MyStudiyo';
    
    public function preFilter( $html, $config, $context )
    {
        $pre_regex = '@<iframe[^>].+?mystudiyo.com/([a-zA-Z0-9\/_\-\.]+).+?</iframe>@si';
        $pre_replace = '<span class="mys-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter( $html, $config, $context )
    {

        $post_regex = '#<span class="mys-embed">([a-zA-Z0-9\/_\-\.]*)</span>#';
        $post_replace = '<iframe src="http://www.mystudiyo.com/\1" width="380" height="400" frameborder="0" scrolling="no" name="mystudiyoIframe" title="MyStudiyo.com"><a href="http://www.mystudiyo.com/\1">Quiz</a></iframe>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}

