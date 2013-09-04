<?php

    namespace sys\classes\mvc;

    class MvcFactory {
        
        public static function getView(){
           return new View();           
        }
        
        public static function getViewPart($pathViewHtml=''){
            return new ViewPart($pathViewHtml);
        }
        
        public static function getModule($module=''){
            return new \Module($module); 
        }
    }
?>
