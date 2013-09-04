<?php

    namespace sys\classes\webservice;
    use \sys\classes\util as Util;
    
    class WsConfigXml extends Util\Xml {
                
        private $nodeConfig;
        
        protected function loadConfig($type,$id=''){
            if ($type == 'client' || $type == 'server') {
                $file       = ucfirst($type);                
                $pathXml    = "sys_config/webservice{$file}.xml";  
                
                if (!file_exists($pathXml)) {
                    $msgErr = Util\Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'FILE_NOT_EXISTS'); 
                    $msgErr = str_replace('{FILE}',$pathXml,$msgErr); 
                    throw new \Exception( $msgErr );                     
                }
                
                $objXml     = Util\Xml::loadXml($pathXml);        
                
                if (is_object($objXml)) {
                    $type           = strtolower($type);
                    $nodesRoot      = $objXml->$type;//Guarda o nó client ou server, de acordo com $type
                    if (is_object($nodesRoot)) {                        
                        //Carrega os parâmetros do nó atual
                        foreach($nodesRoot as $nodeConfig){
                            $idNodeConfig = $this->getAttrib($nodeConfig,'id'); 
                            if ($idNodeConfig == $id || strlen($id) == 0) {
                                //Encontrou a configuração do servidor informado:                                
                                $this->nodeConfig = $nodeConfig;                 
                                break;
                            }                  
                        }                        
                    } else {
                        $msgErr = Util\Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_OBJ_ROOT'); 
                        $msgErr = str_replace('{FILE}',$pathXml,$msgErr); 
                        $msgErr = str_replace('{TYPE}',$type,$msgErr); 
                        throw new \Exception( $msgErr );                             
                    }
                } else {
                    $msgErr = Util\Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_LOAD_OBJ_XML'); 
                    $msgErr = str_replace('{FILE}',$pathXml,$msgErr);  
                    throw new \Exception( $msgErr );                         
                }
            } else {
                $msgErr = Util\Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_XML_TYPE'); 
                throw new \Exception( $msgErr );                  
            }
       }
       
       /**
        * Retorna o valor do nó requisitado.         
        * A variável deve fazer referência a um nó existente no XML de configuração (server ou client).
        * 
        * @param string $var
        * @return string 
        */
       function __get($var) {
           $value       = '';
           $nodeConfig  = $this->nodeConfig;
                     
           if (is_object($nodeConfig)) {              
                $value = (string)$nodeConfig->$var;               
           } else {
                $msgErr = Util\Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_NODE_CONFIG'); 
                $msgErr = str_replace('{VAR}',$var,$msgErr);  
                throw new \Exception( $msgErr );                    
           }
           return $value;
       }
    }

?>
