<?php

    class Uri {
        
        public static function parts(){
            $objUriParts        = new \stdClass();//Objeto de dados (return)
            $arrPartsUrl        = array();
            $controller         = 'index';   
            $baseUrl            = CfgApp::get('baseUrl');
            $action             = '';
            
            //Módulos:
            $modules            = CfgApp::get('modules');//String com módulos do sistema
            $arrModules         = explode(',',$modules);
            $module             = CfgApp::get('defaultModule');
            $arrModulesSys      = array('panel','test');//Módulos que não precisam constar em app.xml.            
            $arrMergeModules    = array_merge($arrModules,$arrModulesSys);//Array com todos os módulos.

            //Idioma:
            $languages          = CfgApp::get('languages');//Idiomas aceitos
            $language           = CfgApp::get('defaultLang');//Idioma padrão         
            $arrLanguages       = NULL;
            
            if (strlen($languages) > 0) {
                $arrLanguages  = explode(',',$languages); 
            }
            
            /*
             * Quebra a variável 'PG' recebida via GET em partes identificáveis 
             * como módulo, controller e action.
             * 
             * IMPORTANTE:
             * A pasta root do projeto, se houver, deve ser retirada da URL antes de
             * identificar o módulo, controller e action.
             */
            $params         = (isset($_GET['PG']))?trim($_GET['PG']):'';             
            $params         = str_replace($baseUrl,'','/'.$params);//Retira a pasta root da string.
            
            $pathParts      = explode('/',$params);            
                       
            $action         = self::getPartUrl(@$pathParts[1]);            
            
            if (is_array($pathParts) && count($pathParts) > 0) { 
                //A URL pode conter partes que representam o módulo, controller e action
                $controllerPart = $pathParts[0];
                
                $controllerPart = self::mapMagicModule($controllerPart);
                
                //Verifica se a primeira parte da URL é um idioma
                $keyLang = FALSE; 
                
                if (is_array($arrLanguages)) $keyLang = array_search($controllerPart,$arrLanguages);
               
                if ($keyLang !== FALSE) {
                    //O primeiro parâmetro refere-se a um idioma específico
                    $language = $controllerPart;
                    array_shift($pathParts);//Retira o índice zero do array.
                    $controllerPart = (isset($pathParts[0]))?$pathParts[0]:'';
                }
                
                $keyModule      = array_search($controllerPart,$arrMergeModules);
                if ($keyModule !== FALSE) {
                    //O primeiro parâmetro é um módulo
                    $module     = $controllerPart;
                    array_shift($pathParts);//Retira o índice zero do array.
                    $controller = self::getPartUrl(@$pathParts[0]);            
                    $action     = self::getPartUrl(@$pathParts[1]);            
                } else {                    
                    $controller = self::getPartUrl($controllerPart);                
                }       
            } 
            
            
            $objUriParts->lang          = $language;
            $objUriParts->module        = $module;
            $objUriParts->controller    = $controller;
            $objUriParts->action        = $action;
            
            return $objUriParts;
        }
        
        private static function getPartUrl($pathPart,$default='index'){
           $value = (isset($pathPart) && $pathPart != null)?$pathPart:$default; 
           return $value;
        }  
        
        /**
         * Faz o mapeamento do controller informado para um nome diferente, 
         * caso o nome do controller informado esteja na lista magicModules de app.xml.
         * 
         * @param string $controller
         * @return string Nome do $controller alterado com o prefixo.
         */
        private static function mapMagicModule($controller=''){
            if (strlen($controller) > 0) {
                $magicModules   = CfgApp::get('magicModules');
                
                if (strlen($magicModules) > 0) {
                    //Há configurações de módulos mágicos para o projeto atual.
                    $arrMagicModules = explode(';',$magicModules);
                    
                    if (is_array($arrMagicModules)) {
                        //Há uma ou mais listas de módulos mapeados.
                        foreach($arrMagicModules as $groupMagicModules) {                        
                            /* Formato de $groupMagicModules:
                             * prefixo:modulo1,modulo2,modulo3...
                             * 
                             * EXEMPLO:
                             * app_:aluno,professor,escola;dev_:ambiente1,ambiente2
                             * 
                             * Neste caso, há dois mapeamentos separados por ponto-e-vírgula.
                             * Caso a URL server.com.br/aluno/ seja informada, o sistema deverá buscar o controller app_aluno.
                             * 
                             */                            
                            @list($prefixo,$listModules) = explode(':',$groupMagicModules);                         
                            $arrModules = explode(',',$listModules);
                            $key = array_search($controller,$arrModules);
                            
                            if ($key !== false) {
                                //O controller atual refere-se a um módulo mapeado.                                
                                $controller = $prefixo.$controller;
                                break;
                            }                        
                        }
                    }
                }
            }
            return $controller;
        }        
    }
?>
