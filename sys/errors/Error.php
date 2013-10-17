<?php
    use sys\classes\error\ErrorHandler;

    class Error extends ErrorHandler {
        public static function eApp($codErr){
            //Abaixo, nome do arquivo xml, neste caso o mesmo nome do método (eLogin.xml), que contém o código ($codErr) solicitado.            
            $nameXmlFile    = __FUNCTION__;
            $msgErr         = self::getErrorString($nameXmlFile,$codErr);
            return $msgErr;
         }        
    }
?>

