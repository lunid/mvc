<?php

    class ExceptionHandler extends \Exception {
        
        private $objLoadXml = NULL;  
        private $exception  = NULL;
        
        function __construct(){
            try {
                $id               = '';
                $exception        = NULL;
                $xmlFilename      = 'common.xml';
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
                
                $this->objLoadXml   = new LoadXml($xmlFilename);  
                if (strlen($id) > 0) $this->getException ($id);
                
            } catch (\Exception $e) {
                throw $e;                
            }
        }    
        
        function getException($id){
            $objLoadXml = $this->objLoadXml;
            $exception  = $this->exception;
            $code       = 0;
            $message    = 'Impossível localizar a mensagem'.$id.'. Um arquivo de exceção não foi informado.';
            if ($objLoadXml !== NULL) {
                $message = $this->objLoadXml->getMessageForId($id);                                                          
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
