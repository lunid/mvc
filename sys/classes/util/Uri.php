<?php

    /**
     * @Inject objCfgApp
     */
    class Uri {
        
        private $cfgClass;
        private $objUriMvcParts;
        
        function __construct($cfgClass='CfgApp'){
            $e = new \ExceptionHandler('FILE_NOT_EXISTS',new \Exception);
            //$e->setParam('FILE_EXISTS','teste.xml');
            //$e->getException('FILE_NOT_EXISTS');
            throw $e;
            
            $this->cfgClass = $cfgClass;
            $this->setUriMvcParts();
        }
        
        private function getCfgClass(){
            $cfgClass = $this->cfgClass;
            if (strlen($cfgClass) == 0) {
                throw new \Exception("Uri: o objeto {$cfgClass} não foi informado.");
            }
            return $cfgClass;
        }
        
        private function setUriMvcParts(){            
            $cfgClass           = $this->getCfgClass();
            $arrPartsUrl        = array();
            $controller         = 'index';   
            $baseUrl            = $cfgClass::get('baseUrl');
            $action             = '';
            
            //Módulos:
            $modules            = $cfgClass::get('modules');//String com módulos do sistema
            $arrModules         = explode(',',$modules);
            $module             = $cfgClass::get('defaultModule');
            $arrModulesSys      = array('panel','test');//Módulos que não precisam constar em app.xml.            
            $arrMergeModules    = array_merge($arrModules,$arrModulesSys);//Array com todos os módulos.

            //Idioma:
            $languages          = $cfgClass::get('languages');//Idiomas aceitos
            $language           = $cfgClass::get('defaultLang');//Idioma padrão         
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
            
            $objUriMvcParts                = new \stdClass();//Objeto de dados (return)
            $objUriMvcParts->lang          = $language;
            $objUriMvcParts->module        = $module;
            $objUriMvcParts->controller    = $controller;
            $objUriMvcParts->action        = $action;
            
            $this->objUriMvcParts = $objUriMvcParts;
        }
        
        private static function getPartUrl($pathPart,$default='index'){
           $value = (isset($pathPart) && $pathPart != null)?$pathPart:$default; 
           return $value;
        }  
        
        /**
         * Retorna um objeto de dados contendo os seguintes parâmetros:
         *  - lang
         *  - module
         *  - controller
         *  - action
         * 
         * O objeto é criado após a execução do método setUriMvcParts() 
         * no construtor da classe
         * 
         * @return stdClass
         */
        function getMvcParts(){
            return $this->objUriMvcParts;
        }
        /**
         * Faz o mapeamento do controller informado para um nome diferente, 
         * caso o nome do controller informado esteja na lista magicModules de app.xml.
         * 
         * @param string $controller
         * @return string Nome do $controller alterado com o prefixo.
         */
        private function mapMagicModule($controller=''){
            $cfgClass = $this->getCfgClass();
            if (strlen($controller) > 0) {
                $magicModules   = $cfgClass::get('magicModules');
                
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
