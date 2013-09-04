<?php

    namespace sys\classes\util;  
    
    /**
     * 28/12/2012
     * Classe utilizada para Injeção de Dependência.
     * 
     * @author KRASIMIR TSONEV
     *      
     * Download:
     * https://github.com/krasimir/php-dependency-injection
     * 
     * @link http://krasimirtsonev.com/blog/article/Dependency-Injection-in-PHP-example-how-to-DI-create-your-own-dependency-injection-container
     *     
     */
    require_once(PATH_PROJECT . 'sys/classes/util/Xml.php');  
    
    class DI extends \sys\classes\util\Xml {
    
        private static $map;
        private static $xmlConfig = 'DI.xml';
        
        public static function loadMapXml($classNamespace){
            $pathXml = \Application::getModule().'/'.self::$xmlConfig;
            if (file_exists($pathXml)) {
                $objXml = self::loadXml($pathXml);  
                if (is_object($objXml)) {
                    //$this->objXml   = $objXml;
                    $nodesMap       = $objXml->map;
                    $numItens       = count($nodesMap);
                    if ($numItens > 0) {                        
                        //Localiza as dependências que devem ser mapeadas para classe informada.
                        $nodeMaps        = self::valueForAttrib($nodesMap,'class',$classNamespace);
                        foreach($nodeMaps as $nodeMap) {    
                            //Um ou mais nós da tag <injection /> foram encontrados
                            $arrInjection   = self::getAttribsOneNode($nodeMap);                                       
                            
                            if (is_array($arrInjection)) {
                                //Objeto a ser injetado na classe atual. Guarda os valores de cada atributo da tag.                               
                                $arrAttrib  = array('singleton','var','class','params');//Atributos da tag XML.                            
                                foreach($arrAttrib as $id){
                                    $$id = (isset($arrInjection[$id]))?$arrInjection[$id]:null;                                                                 
                                }

                                //Faz o mapeamento de injeção para a classe atual:                                    
                                if ((int)$singleton == 1) {
                                    DI::mapClassAsSingleton($var, $class,$params);
                                } elseif ($var !== null && $class !== null) {
                                    $arrParams = ($params !== null)?explode(';',$params):null;
                                    DI::mapClass($var, $class,$params);
                                    $obj = self::getInstanceOf_($classNamespace,$var,$params);                                    
                                } else {
                                    echo 'Impossível fazer injeção de dependência para a classe '.$classNamespace;                                
                                }

                                echo "$var - $class - $params";
                            }
                        }
                    }
                } else {                
                    $msgErr = 'Impossível ler o arquivo '.$pathXml;                                            
                }                            
            }
            echo $pathXml;
            die();
        }
        
        public static function getInstanceOf_($className,$var,$params = null){
            //ReflectionClass
            echo $className;
            $obj    = null;
            $urlInc = str_replace("\\", "/" , $className.'.php');                
            if (isset($className) && file_exists($urlInc)) {
                require_once($urlInc);  
                $reflection = new \ReflectionClass($className);

                //Cria uma instância da classe
                if($params === null || count($params) == 0) {
                   $obj = new $className;
                } else {
                    if(!is_array($params)) {
                        $params = array($params);
                    }
                   $obj = $reflection->newInstanceArgs($params);
                }  

                //Faz a injeção de Dependência
                
                switch(self::$map->$var->type) {
                    case "value":
                    case "class":
                        echo self::$map->$var->value.'<br>';
                        $obj->$var = self::$map->$var->value;
                    break;
                    case "classSingleton":
                        if(self::$map->$var->instance === null) {
                            $obj->$var = self::$map->$var->instance = self::$map->$var->value;
                        } else {
                            $obj->$var = self::$map->$var->instance;
                        }
                    break;
                }                          
            }  
            var_dump($obj);
            die();
            //Retorna uma instância criada.
            return $obj;              
        }
        
        public static function getInstanceOf($className, $arguments = null) {
        
            // checking if the class exists
            if(!class_exists($className)) {
                throw new Exception("DI: missing class '".$className."'.");
            }
            
            // initialized the ReflectionClass
            $reflection = new ReflectionClass($className);
            
            // creating an instance of the class
            if($arguments === null || count($arguments) == 0) {
               $obj = new $className;
            } else {
                if(!is_array($arguments)) {
                    $arguments = array($arguments);
                }
               $obj = $reflection->newInstanceArgs($arguments);
            }
            
            // injecting
            if($doc = $reflection->getDocComment()) {
                $lines = explode("\n", $doc);
                foreach($lines as $line) {
                    if(count($parts = explode("@Inject", $line)) > 1) {
                        $parts = explode(" ", $parts[1]);
                        if(count($parts) > 1) {
                            $key = $parts[1];
                            $key = str_replace("\n", "", $key);
                            $key = str_replace("\r", "", $key);
                            if(isset(self::$map->$key)) {
                                switch(self::$map->$key->type) {
                                    case "value":
                                        $obj->$key = self::$map->$key->value;
                                    break;
                                    case "class":
                                        $obj->$key = self::getInstanceOf(self::$map->$key->value, self::$map->$key->arguments);
                                    break;
                                    case "classSingleton":
                                        if(self::$map->$key->instance === null) {
                                            $obj->$key = self::$map->$key->instance = self::getInstanceOf(self::$map->$key->value, self::$map->$key->arguments);
                                        } else {
                                            $obj->$key = self::$map->$key->instance;
                                        }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            // return the created instance
            return $obj;
            
        }
        public static function mapValue($key, $value) {
            self::addToMap($key, (object) array(
                "value" => $value,
                "type" => "value"
            ));
        }
        public static function mapClass($key, $value, $arguments = null) {
            self::addToMap($key, (object) array(
                "value" => $value,
                "type" => "class",
                "arguments" => $arguments
            ));
        }
        public static function mapClassAsSingleton($key, $value, $arguments = null) {
            self::addToMap($key, (object) array(
                "value" => $value,
                "type" => "classSingleton",
                "instance" => null,
                "arguments" => $arguments
            ));
        }
        private static function addToMap($key, $obj) {
            if(self::$map === null) {
                self::$map = (object) array();
            }
            self::$map->$key = $obj;
        }
    
    }

?>