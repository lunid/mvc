<?php

    class ExceptionHandler extends \Exception {
        
        private $objDicionaryXml    = NULL;  
        private $exception          = NULL;
        
        /**
         * Inicializa o objeto da classe atual.
         * 
         * Opções de inicialização:
         * <code>
         * //Informa o nome referente ao atributo id do arquivo sys/dic/common.xml que deve ser capturado.
         * $e = new \ExceptionHandler('FILE_NOT_EXISTS');
            //$e->getException('FILE_NOT_EXISTS');
            throw $e;
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
                
                if ($numParams > 0) {
                    foreach($arrParams as $param) {                        
                        $pathParts = pathinfo($param);
                        if (@$pathParts['extension'] == 'xml') {
                            $xmlFilename = $param;
                        } elseif (is_object($param) && get_class($param) == 'Exception') {
                            $exception = $param;
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
        
        function getException($id){
            $objDicionaryXml    = $this->objDicionaryXml;
            $exception          = $this->exception;
            $code               = 0;
            $message            = 'Impossível localizar a mensagem'.$id.'. Um arquivo de exceção não foi informado.';
            
            if ($objDicionaryXml !== NULL) {
                $message = $objDicionaryXml->getMessageForId($id);                                                          
            } 
            
            if ($exception !== NULL) {
                $this->file = $exception->getFile();
                $this->line = $exception->getLine();
                $code       = $exception->getCode();
            }
            
            parent::__construct($message, $code, $exception); 
        }
    }
?>
