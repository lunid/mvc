<?php

    namespace sys\lib\classes;
    use \sys\classes\util\Xml;
    use \sys\classes\util\Dic;
    
    class LoadInstallXml extends Xml {
        private $pathXml;
        private $descricao;
        private $objConfig;//Guarda um objeto de dados com os parâmetros lidos do arquivo install.xml
        
        function __construct($pathXml){
            $this->loadConfig($pathXml);            
        }                
    
        private function loadConfig($pathXml){
            if (file_exists($pathXml)) {
                $this->pathXml  = $pathXml;
                $objXml         = self::loadXml($pathXml);  
                $objConfig      = new \stdClass();
                if (is_object($objXml)) {                    
                    $component          = $objXml->component;
                    $descricao          = $objXml->descricao;
                    $paramsNode         = $objXml->params->param;
                    if (count($paramsNode) > 0) {
                        //O componente atual espera um ou mais parâmetros na chamada do método init().
                        foreach($paramsNode as $node){
                            $arrAttrib  = self::getAttribsOneNode($node); 

                            if (isset($arrAttrib['id'])) {
                                $nomeVar    = $arrAttrib['id'];

                                $objConfig->$nomeVar            = new \stdClass();
                                $objConfig->$nomeVar->__info    = (string)$node;
                                $objConfig->$nomeVar->type      = (isset($arrAttrib['type']))?$arrAttrib['type']:'';
                                $objConfig->$nomeVar->list      = (isset($arrAttrib['list']))?$arrAttrib['list']:'';
                                $objConfig->$nomeVar->required  = (isset($arrAttrib['required']))?(int)$arrAttrib['required']:'';                            
                            } else {                                
                                $pathXmlDic = Url::exceptionClassXml(__CLASS__);
                                $msgErr     = Dic::loadMsgForXml($pathXmlDic,__METHOD__,'ATTRIB_ID_NOT_EXISTS');                
                                $msgErr     = str_replace('{COMP}',$component,$msgErr);
                                throw new \Exception( $msgErr );                                
                            }
                        }
                    }
                } else {                
                    $msgErr = 'Impossível ler o arquivo '.$pathXml;                                            
                } 
                $this->objConfig = $objConfig;
            } else {
                
            }
            
        }
        
        function getDescricao(){
            return $this->descricao;
        }
        
        function __get($var){
            $objConfig = $this->objConfig;
            return $objConfig->$var;
        }
        
    }
?>
