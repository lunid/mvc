<?php

    /**
     * Classe usada para trocar marcadores em string por variáveis.
     * O marcador deve estar entre chaves e em maiúsculas na string, no formato {NOME_EM_CAIXA_ALTA}.
     * 
     * Exemplo de uso:     
     * <code>
     *  $message    = "Meu nome é {NOME}.";
     *  $arrReplace = array('NOME'=>'Maria');
     *  $objReplace = new \Replace($message, $arrReplace);                    
     *  $message    = $objReplace->getMessageDest();
     *  echo $message;
     * </code>              
     */
    class Replace {
        private $messageOrig    = '';      
        private $messageDest    = '';
        
        /**
         * Recebe a mensagem cujos marcadores devem ser substituídos 
         * e um array associativo (opcional) que, se informado, faz a troca dos 
         * marcadores pelas variáveis do array.
         * 
         * @param string $message
         * @param string[] $arrReplace Array associativo
         */
        function __construct($message, $arrReplace = NULL){
            $this->setMessageOrig($message);
            $this->setArrReplace($arrReplace);
        }
        
        function setMessageOrig($message){
            if (strlen($message) > 0) {
                $this->messageOrig = $message;                                
            }             
        }
        
        function setArrReplace($arrReplace){
            if (is_array($arrReplace)) {                
                foreach($arrReplace as $name=>$value) {                    
                    $this->addParam($name, $value);
                }                
            }
        }

        /**
         * Adiciona um novo parâmetro na string $message, substituindo o marcardor {$name}
         * pelo valor informado em $value.
         * 
         * @param string $name
         * @param string $value
         * @return string Retorna a string alterada
         */
        function addParam($name, $value){
            $name               = strtoupper($name);
            $message            = $this->getMessageOrig();
            $this->messageDest  = str_replace("{{$name}}",$value,$message);
            return $this->getMessageDest();
        }
        
        function getMessageOrig(){
            return $this->messageOrig;
        }
        
        function getMessageDest(){
            $message = $this->messageDest;
            if (strlen($message) == 0) {
                $message = $this->getMessageOrig();
            }
            return $message;
        }        
        
    }
?>
