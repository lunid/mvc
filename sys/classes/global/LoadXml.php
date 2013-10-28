<?php

    class LoadXml extends \ErrorHandler {
        
        private $xmlPath;
        
        function __construct($xmlFilename='common.xml') {
            self::initErrorHandler();
            $xmlFilename = str_replace('.xml','',$xmlFilename);
            try {
                $this->checkXmlFile($xmlFilename);
            } catch(\Exception $e) {                
                throw $e;
            }
        }
        
        private function checkXmlFile($xmlFilename){
            //Retira as barras de início e fim da pasta root
            $rootProject    = str_replace('/','',\CfgApp::get('baseUrl'));
            
            /*
             * Localiza o caminho físico (c:/root/..) da pasta root do projeto             
             * usando como separador a string de baseUrl             
             */
            list($realPath,$pathFile) = explode($rootProject,__DIR__);
            
            //Inverte a barra invertida por barra normal
            $realPath   = str_replace('\\', '/', $realPath);
            
            //Monta o path do arquivo xml a partir da pasta padrão de dicionário
            $xmlPath = $realPath.$rootProject.'/sys/dic/exceptions/'.$xmlFilename.'.xml';

            if (file_exists($xmlPath)) {
                $this->xmlPath = $xmlPath;
                return TRUE;
            } else {
                throw new \Exception("O arquivo {$xmlPath} não foi localizado.");
            }
        }
        
        function getMessageForId($id){
            $xmlPath = $this->xmlPath;
            $contentFile = file_get_contents($xmlPath);
            if (strlen($contentFile) > 0) {
                $objXml     = simplexml_load_file($xmlPath);     
                $nodes      = $objXml->msg;
                $numItens   = count($nodes);
               
                if ($nodes->attributes() !== NULL) {
                    $value = '';           
                    if (get_class($nodes) == 'SimpleXMLElement' && $nodes->attributes() !== NULL) {
                        $value = $this->valueForAttrib($nodes,'id',$id);                                                 
                    }
                    
                    if (strlen($value) == 0) {
                        $value = 'A mensagem referente ao atributo '.$id.' não foi localizada.';
                    }   
                   
                    return $value;
                } else {
                    echo 'O arquivo informado não possui nós.';
                }
            } else {
                die('LoadXml(): O arquivo '.$xmlPath.' está vazio.');
            }
        }        
        
        public static function valueForAttrib($nodes,$atribName,$atribValue){        
            foreach($nodes as $node){     
                foreach($node->attributes() as $name => $value){                       
                    if ($name == $atribName && $value == $atribValue) return $node;                    
                }                
            }           
        }        
    }
?>
