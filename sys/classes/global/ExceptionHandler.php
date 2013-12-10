<?php

    class ExceptionHandler extends \Exception {
        
        private $objDicionaryXml    = NULL;  
        private $exception          = NULL;//Objeto Exception usado para identificar dados do local onde a Exception foi disparada.
        private $arrReplace         = NULL;//Array associativo com as tags e seus respectivos valores.
        private $codMessage         = NULL;//Valor do atributo ID na tag PARAM do arquivo XML.
        
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
         * EXEMPLO 1:
         * <code>
         * //Informa o nome referente ao atributo id do arquivo sys/dic/common.xml, que deve ser capturado.
         *  $arrReplace = array('FILE'=>'arquivo.pdf);//Deve trocar {FILE} por 'arquivo.pdf'
         *  $e = new \ExceptionHandler('FILE_NOT_EXISTS',new \Exception,$arrReplace);
         *  throw $e;
         * </code>
         * 
         * EXEMPLO 2:
         * <code>
         *  $objE = new \ExceptionHandler(new \Exception, self::$ExceptionFile);            
         *  $objE->setCodeMessage('FILE_NOT_EXISTS')->replaceTagFor(array('FILE'=>'arquivo.pdf));
         *  $objE->setException(new \Exception)->render();                             
         *  throw $objE; 
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
                        if (isset($pathParts['extension']) && @$pathParts['extension'] == 'xml') {
                            $this->setDicXml($param);           
                        } elseif (is_object($param) && get_class($param) == 'Exception') {
                            $this->setException($param);
                        } elseif (is_array($param)) {
                            $this->replaceTagFor($param);
                        } else {
                            $this->setCodeMessage($param);
                        }
                    }
                }
            } catch (\Exception $e) {
                throw $e;                
            }
        }    
        
        /**
         * Define o path do arquivo XML a ser usado como dicionário.
         * O arquivo, por padrão, deve ficar armazenado em sys/dic/exceptions.
         * 
         * Todos os exemplos abaixo são válidos e fazem referência
         * ao arquivo sys/dic/exceptions/Common.xml
         * <code>
         * Common.xml
         * exceptions/Common.xml
         * Common
         * </code>
         * 
         * T
         * @param string $pathXml
         * @throws Exception Caso o path informado esteja incorreto (arquivo inexistente).
         */
        function setDicXml($pathXml){
            try {
              $this->objDicionaryXml  = new DicionaryXml($pathXml);    
            } catch(\Exception $e){
                throw $e;
            }
        }
        
        function setCodeMessage($codeMessage){
            $this->codMessage = $codeMessage;
            return $this;
        }
        
        /**
         * Define um array associativo cujos valores devem substituir tags na mensagem de erro.
         * As tags são marcadores no formato {NOME_DA_TAG} contidas na string de erro.
         * 
         * @param string[] $arrReplace
         * @return void
         */
        function replaceTagFor($arrReplace){
            if (is_array($arrReplace)) {
                $this->arrReplace = $arrReplace;
            }
            return $this;
        }
        
        function setException($exception){
            if ($exception instanceof \Exception) {                                      
                $this->exception = $exception;
            }  
            return $this;
        }
        /**
         * Localiza no arquivo XML, a mensagem referente ao id informado.
         * Monta a mensagem da Exception usando também o objeto Exception, caso 
         * tenha sido informado no construtor.
         * 
         * @param type $id
         */
        function render(){            
            $objDicionaryXml    = $this->objDicionaryXml;
            $exception          = $this->exception;
            $arrReplace         = $this->arrReplace;
            $codeMessage        = $this->codMessage;
            $code               = 0;            
            $message            = 'Impossível localizar a mensagem'.$codeMessage.'. Um arquivo de exceção não foi informado.';
            
            if ($objDicionaryXml !== NULL) {
                $message    = $objDicionaryXml->getMessageForId($codeMessage);  
                
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
