<?php

    /*
     * Classe abastrata que contém os recursos comuns a todos os Controllers
     * @abstract
     */    
    abstract class Controller {
        
        private $memCache;
        private $nameCache  = NULL;
        private $arrView    = array();

        function __construct(){
             
        }  
                
        
        /**
         * Adiciona um objeto View no Container atual, com um nome que 
         * permite recuperá-lo posteriormente pelo método getView().
         * 
         * Um Container pode ter mais de um objeto View.
         * 
         * Ao ser adicionado, o objeto recebe um nome.
         * @param type $nameView
         * @param type $objView
         */
        function addView($nameView, $objView){
            $this->arrView[$nameView] = $objView;  
        }
        
        /**
         * Permite recuperar um objeto View, a partir do nome informado 
         * ao armazená-lo no Controller atual pelo método addView().
         * 
         * Caso nenhum valor seja informado pelo parâmetro $nameView, 
         * o objeto View padrão (default) do Controller atual será retornado.
         *          
         * @param string $nameView Nome do objeto a ser recuperado
         * @return View
         * @throws \Exception caso não seja possível localizar/recuperar o objeto informado.
         */
        function getView($nameView=''){
            $objView        = NULL;            
            $commonFolder   = FALSE;
            
            if (strlen(trim($nameView)) == 0) {
                $nameView       = 'default';
                $commonFolder   = TRUE;
            }
                        
            if (!isset($this->arrView[$nameView])) {
                $this->addView($nameView, new View($nameView, $commonFolder));                            
            }
            
            $arrView = $this->arrView; 
            
            if (isset($arrView[$nameView])) {
                $objView = $arrView[$nameView];
            }
            
            if (!is_object($objView)) {                
                throw new \Exception("Controller->getView(): o objeto View solicitado não foi encontrado.");
            }
            
            return $objView;
        }
        
    }
?>
