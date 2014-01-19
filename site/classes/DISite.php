<?php
    
    class DISite extends DIContainerController {
       
        /**
         * Retorna um objeto Controller, iniciado com as dependências.
         * @return Controller
         */
       function indexController(){           
           $objController = $this->defaultController();
           //$objController->addView('vwTeste',$this->view('teste'));   
           return $objController;
       }
    }
?>
