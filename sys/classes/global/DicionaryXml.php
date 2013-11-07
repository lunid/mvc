<?php

    /**
     * Classe responsável por localizar/carregar um arquivo XML 
     * a ser usado como dicionário.
     * 
     * Se nenhum nome de arquivo for informado, utiliza o arquivo XML padrão ('exceptions/common.xml')
     * armazenado em /sys/dic/.
     * 
     */
    class DicionaryXml extends \ErrorHandler {
        
        private $xmlPath;
        
        /**
         * Pode receber o nome do arquivo XML 
         * @param type $xmlFilename
         * @throws Exception
         */
        function __construct($xmlFilename='') {
            self::initErrorHandler();//Trata um erro (caso ocorra) como uma Exception
            if (strlen($xmlFilename) == 0) $xmlFilename = 'exceptions/common.xml';
            
            //Retira a extensão, caso tenha sido informada, para evitar erro ao concatenar com '.xml'
            $xmlFilename = str_replace('.xml','',$xmlFilename);
            
            try {
                $this->checkXmlFile($xmlFilename);
            } catch(\Exception $e) {                
                throw $e;
            }
        }
        
        /**
         * Verifica se o arquivo informado existe dentro de sys/dic/...
         * 
         * @param string $xmlFilename Path do arquivo xml (a pasta root é 'sys/dic/')
         * @return boolean
         * @throws \Exception Caso o arquivo não tenha sido localizado
         */
        private function checkXmlFile($xmlFilename){
            //Retira as barras de início e fim da pasta root
            $rootProject    = str_replace('/','',\CfgApp::get('baseUrl'));
            
            /*
             * Localiza o caminho físico (c:/root/..) da pasta root do projeto             
             * usando como separador a string de baseUrl.             
             */
            list($realPath,$pathFile) = explode($rootProject,__DIR__);
            
            //Muda a barra invertida para barra normal.
            $realPath   = str_replace('\\', '/', $realPath);
            
            //Monta o path do arquivo xml a partir da pasta padrão de dicionário
            $xmlPath = $realPath.$rootProject.'/sys/dic/'.$xmlFilename.'.xml';

            if (file_exists($xmlPath)) {
                $this->xmlPath = $xmlPath;
                return TRUE;
            } else {
                throw new \Exception("O arquivo {$xmlPath} não foi localizado.");
            }
        }
        
        function getMessageForId($id){
            $value      = '';           
            $xmlPath    = $this->xmlPath;
            
            $contentFile = file_get_contents($xmlPath);
            if (strlen($contentFile) > 0) {
                $objXml     = simplexml_load_file($xmlPath);     
                $nodes      = $objXml->msg;
                $numItens   = count($nodes);
               
                if ($nodes->attributes() !== NULL) {                    
                    if (get_class($nodes) == 'SimpleXMLElement' && $nodes->attributes() !== NULL) {
                        $value = self::valueForAttrib($nodes,'id',$id);                                                 
                    }
                    
                    if (strlen($value) == 0) {
                        $value = 'A mensagem referente ao atributo '.$id.' não foi localizada.';
                    }                      
                    return $value;
                } else {
                    $value = 'O arquivo informado não possui nós.';
                }
            } else {
                $value = 'O arquivo '.$xmlPath.' está vazio.';
            }
            
            return $value;
        }        
        
        /**
         * Localiza a string contida em um nó XML a partir do atributo informado.
         * 
         * Exemplo:
         *<root>
         *      <messages>
         *          <message id='OLA_MUNDO'>Olá mundo!</message>
         *      </messages>
         * </root>
         * Para imprimir o texto 'Olá mundo!' do nó XML acima, a chamada deve ser:
         * <code>
         *  $objXml     = simplexml_load_file('<pathDoArquivoXml.xml>');     
         *  $nodes      = $objXml->messages;
         *  $value      = self::valueForAttrib($nodes,'id','OLA_MUNDO'); 
         *  echo $value;
         * </code>
         * 
         * @param SimpleXMLElement $nodes
         * @param string $atribName Nome do atributo que se deseja verificar
         * @param string $atribValue Refere-se ao valor do atributo cujo nó se deseja localizar
         * @return string
         */
        public static function valueForAttrib($nodes,$atribName,$atribValue){        
            foreach($nodes as $node){     
                foreach($node->attributes() as $name => $value){                       
                    if ($name == $atribName && $value == $atribValue) return $node;                    
                }                
            }           
        }        
    }
?>
