<?php
    
    class DISite extends DIContainerController {
       
       function indexController(){           
           $objController = $this->defaultController();
           //$objController->addView('vwTeste',$this->view('teste'));   
           return $objController;
       }
    }
?>
