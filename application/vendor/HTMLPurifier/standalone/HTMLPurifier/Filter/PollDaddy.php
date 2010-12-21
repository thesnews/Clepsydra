<?php

class HTMLPurifier_Filter_PollDaddy extends HTMLPurifier_Filter
{
    
    public $name = 'PollDaddy';
    
    public function preFilter( $html, $config, $context )
    {
        $pre_regex = '@<script[^>].+?polldaddy.com/p/([a-zA-Z0-9]+).+?</script>@si';
        $pre_replace = '<span class="pld-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter( $html, $config, $context )
    {

        $post_regex = '#<span class="pld-embed">([a-zA-Z0-9]*)</span>#';
        $post_replace = '<script type="text/javascript" charset="utf-8" src="http://static.polldaddy.com/p/\1.js"></script>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}

