<?php

class HTMLPurifier_Filter_CoverItLive extends HTMLPurifier_Filter
{
    
    public $name = 'CoverItLive';
    
    public function preFilter( $html, $config, $context )
    {
        $pre_regex = '@<iframe[^>]*?(altcast_code=([a-zA-Z0-9]*)/height=([0-9]*)/width=([0-9]*))(.*)>.*?</iframe>@si';
        $pre_replace = '<span class="cil-embed">\2:\3:\4</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter( $html, $config, $context )
    {

        $post_regex = '#<span class="cil-embed">([a-zA-Z0-9]*):([0-9]*):([0-9]*)</span>#';
        $post_replace = '<iframe src="http://www.coveritlive.com/index2.php/option=com_altcaster/task=viewaltcast/altcast_code=\1/height=\2/width=\3" scrolling="no" height="\2px" width="\3px" frameBorder="0" ><a href="http://www.coveritlive.com/mobile.php?option=com_mobile&task=viewaltcast&altcast_code=\1" >Live Blog</a></iframe>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}

