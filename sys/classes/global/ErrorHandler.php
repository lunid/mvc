<?php

    class ErrorHandler {
        
        function initErrorHandler(){
            set_error_handler("self::exceptionErrorHandler");  
        }
        
        protected static function exceptionErrorHandler($errno, $errstr, $errfile, $errline){
            $str = '';
            
            switch ($errno) {
                case E_USER_ERROR:
                    $str = "<b>ERROR</b> [$errno] $errstr<br />\n";
                    $str .= "  Erro fatal na linha $errline, do arquivo $errfile";                    
                case E_USER_WARNING:
                    $str = "<b>WARNING</b> [$errno] $errstr<br />\n";
                    break;
                case E_USER_NOTICE:
                    $str = "<b>NOTICE</b> [$errno] $errstr<br />\n";
                    break;
                default:
                    $str = "Erro desconhecido: [$errno] $errstr<br />\n";
                    break;
            }            
            throw new \ErrorException($str, 0, $errno, $errfile, $errline);
        }        
    }
?>
