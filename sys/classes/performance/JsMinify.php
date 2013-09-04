<?php

namespace sys\classes\performance;

class JsMinify implements IMinify {
    
    public static function minify($script){
        $output = '';
        if (strlen($script) > 0) {        
            /* Remove comentários*/
            $buffer = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $script);
            /* Remove tabs, spaços, newlines etc. */
            $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
            /* Remove outros espaços antes/depois */
            $output = preg_replace(array('(( )+\))','(\)( )+)'), ')', $buffer);
        }
        return $output;        
    }
}
?>
