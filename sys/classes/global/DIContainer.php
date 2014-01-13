<?php

    class DIContainer {
        private static $container;
        
        function __construct(){
            
        }
        
        function getContainer(){            
            //if (is_null(self::$container)) {
                $container = new Pimple();
                 self::$container = $container;
            //}
            return self::$container;
        }
        
        function CfgApp($paramId){
            $container = $this->getContainer();
            $container['className']     = 'CfgApp';
            $container['object']        = function ($c) {               
                return new $c['className']();
            };              
            $param = $container['object']::get($paramId);
            return $param;            
        }
        
        function Uri($cfgClass='CfgApp'){            
            $container = $this->getContainer();
            $container['paramCfgClass'] = $cfgClass;
            $container['class_name']    = 'Uri';
            $container['object']        = function ($c) {               
                return new $c['class_name']($c['paramCfgClass']);
            };  
            return $container['object'];
        }
        
        function __call($method, $args){           
            if ($method == 'index') {                
                $container = $this->getContainer();
                $container['className']  = ucfirst($method);
                $container['object']     = function ($c) { 
                    return new $c['className']();
                };  
                return $container['object'];                      
            }
        }
    }
?>
