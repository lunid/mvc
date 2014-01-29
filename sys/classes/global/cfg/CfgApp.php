<?php

    require_once('AbstractCfg.php');  
    
    class CfgApp extends AbstractCfg {
        
        function __construct(){ 
            $xmlFile    = 'app.xml';
            $arrAtribId = array(                
                'modules',
                'defaultModule',
                'magicModules',
                'defaultTemplate',
                'languages',
                'defaultLang',
                'commonFolder',
                'htmlExtension'
            );
            
            parent::__construct($xmlFile,$arrAtribId);                        
        }
        
        /**
         * Retorna o valor da propriedade de um item definido em app.xml.
         * O atributo id da tag <param> deve coincidir com o parâmetro $id informado na chamada do método.
         * 
         * @param string $id         
         * @return string
         */
        public static function get($id){
            $value = self::getValueForId($id, get_class());           
            $value = trim($value);
            return $value;
        }
          
    }
?>
