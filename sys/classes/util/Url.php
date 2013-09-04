<?php


    class Url {
        
       
        function teste(){
            $arrPartsUrl    = array();
            $module         = LoadConfig::defaultModule(); 
            $params         = (isset($_GET['PG']))?$_GET['PG']:''; 
            
            $pathParts      = explode('/',$params);            
            $controller     = 'index';
            $language       = LoadConfig::defaultLang();            
            $action         = self::getPartUrl(@$pathParts[1]);            
            
            if (is_array($pathParts) && count($pathParts) > 0) { 
                //A URL pode conter partes que representam o módulo, controller e action
                $lang           = LoadConfig::langs();//Idiomas aceitos pelo sistema
                $modules        = LoadConfig::modules();
                
                $arrLangs       = explode(',',$lang); 
                
                $arrModulesDefault  = array('panel','commerce','test');//Módulos que não precisam constar no config.xml.
                $arrModules         = explode(',',$modules);
                $arrModules         = array_merge($arrModules,$arrModulesDefault);
                
                $controllerPart = $pathParts[0];
                
                $controllerPart = self::mapMagicModule($controllerPart);
                
                //Verifica se a primeira parte da URL é um idioma
                $keyLang        = FALSE; 
                if (strlen($arrLangs[0]) > 0) $keyLang = array_search($controllerPart,$arrLangs);

                if ($keyLang !== FALSE) {
                    //O primeiro parâmetro refere-se a um idioma específico
                    $language   = $controllerPart;
                    array_shift($pathParts); 
                    $controllerPart = (isset($pathParts[0]))?$pathParts[0]:'';
                }
                
                $keyModule      = array_search($controllerPart,$arrModules);
                if ($keyModule !== FALSE) {
                    //O primeiro parâmetro é um módulo
                    $module     = $controllerPart;
                    array_shift($pathParts);
                    $controller = self::getPartUrl(@$pathParts[0]);            
                    $action     = self::getPartUrl(@$pathParts[1]);            
                } else {                    
                    $controller = self::getPartUrl($controllerPart);                
                }       
            }
        }
    }
?>
