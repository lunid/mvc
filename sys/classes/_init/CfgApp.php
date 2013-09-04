<?php

    require_once('sys/classes/_init/AbstractCfg.php');  
    
    class CfgApp extends AbstractCfg {
        
        function __construct(){ 
            $xmlFile    = 'app.xml';
            $arrAtribId = array('baseUrl','baseUrlHttp','baseUrlHttps','modules','defaultModule','magicModules','defaultTemplate','languages','defaultLang');
            parent::__construct($xmlFile,$arrAtribId);            
        }
        
        public static function get($id){
            return self::getValueForId($id, get_class());            
        }
          
    }
?>
