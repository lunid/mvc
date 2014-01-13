<?php    
    
    class DIContainerController extends DIContainer {       
        
       
       function view($pathView, $common=FALSE){
           $container               = $this->getContainer();
           $container['common']     = $common;
           $container['pathView']   = $pathView;
           $container['className']  = ucfirst(__FUNCTION__);

           $container['object']     = function ($c) { 
               return new $c['className']($c['pathView'],$c['common']);
           };              
           return $container['object'];
       }
       
        function __call($controller, $args){           
            if ($controller == 'indexController' || $controller == 'defaultController') {   
                //Refere-se ao objeto controller padrão. Retorna um objeto IndexController.
                
                $container               = $this->getContainer();
                $container['className']  = 'IndexController';
                $container['object']     = function ($c) {                     
                    return new $c['className']();
                };                                  
                
                //Inclui um objeto padrão da classe View no Controller atual com conteúdo do arquivo default, em common.
                $objController = $container['object'];
                $objController->addView('default',$this->view('default',TRUE));   
                $objController->getView();
                
                return $objController;                      
            }
        }
    }
    
    
?>
