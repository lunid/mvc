<?php

    class ExceptionHandler extends \Exception {
        
        private $objDicionaryXml    = NULL;  
        private $exception          = NULL;
        private $arrReplace         = NULL;
        
        /**
         * Inicializa o objeto da classe atual.
         * O método utiliza polimorfismo, podendo receber o nome do arquivo xml, 
         * um objeto Exception e/ou um array associativo para fazer a troca de marcadores
         * na mensagem vinda do XML por variáveis.
         * 
         *  1 - Recebe os parâmetros (nome do arquivo xml e/ou um objeto Exception).
         *  2 - Cria um objeto do tipo DicionaryXml informando o nome do arquivo XML.
         *  3 - Se o id for informado, gera a mensagem de Exception a retornar.
         * 
         * Opções de inicialização:
         * <code>
         * //Informa o nome referente ao atributo id do arquivo sys/dic/common.xml, que deve ser capturado.
         *  $arrReplace = array('FILE'=>'arquivo.pdf);//Deve trocar {FILE} por 'arquivo.pdf'
         *  $e = new \ExceptionHandler('FILE_NOT_EXISTS',new \Exception,$arrReplace);
         *  throw $e;
         * </code>
         * 
         * @throws Exception
         */
        function __construct(){
            try {
                $id               = '';
                $exception        = NULL;                
                $xmlFilename      = '';
                $numParams        = func_num_args();
                $arrParams        = func_get_args();
                
                //Faz o tratamento de polimorfismo a partir das variáveis recebidas:
                if ($numParams > 0) {
                    foreach($arrParams as $param) {  
                      
                        $pathParts = (!is_array($param)) ? pathinfo($param) : '';
                        if (@$pathParts['extension'] == 'xml') {
                            $xmlFilename = $param;
                        } elseif (is_object($param) && get_class($param) == 'Exception') {
                            $exception = $param;
                        } elseif (is_array($param)) {
                            $this->arrReplace = $param;
                        } else {
                            $id = $param;
                        }
                    }
                }
                
                if ($exception instanceof \Exception) {                                      
                    $this->exception    = $exception;
                }
                
                $this->objDicionaryXml   = new DicionaryXml($xmlFilename);  
                if (strlen($id) > 0) $this->getException ($id);
                
            } catch (\Exception $e) {
                throw $e;                
            }
        }    
        
        /**
         * Localiza no arquivo XML, a mensagem referente ao id informado.
         * Monta a mensagem da Exception usando também o objeto Exception, caso 
         * tenha sido informado no construtor.
         * 
         * @param type $id
         */
        function getException($id){            
            $objDicionaryXml    = $this->objDicionaryXml;
            $exception          = $this->exception;
            $arrReplace         = $this->arrReplace;
            $code               = 0;
            $message            = 'Impossível localizar a mensagem'.$id.'. Um arquivo de exceção não foi informado.';
            
            if ($objDicionaryXml !== NULL) {
                $message    = $objDicionaryXml->getMessageForId($id);  
                
                if (strlen($message) > 0 && is_array($arrReplace)) {
                    //Faz a troca de marcadores por variáveis:
                    $objReplace = new \Replace($message, $arrReplace);                    
                    $message    = $objReplace->getMessageDest();
                }
                
            } 
            
            if ($exception !== NULL) {
                $this->file = $exception->getFile();
                $this->line = $exception->getLine();
                $code       = $exception->getCode();
            }
            
            //Define a mensagem do objeto Exception
            parent::__construct($message, $code, $exception); 
        }
    }
?>
