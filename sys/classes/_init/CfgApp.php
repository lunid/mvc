<?php

    require_once('sys/classes/_init/AbstractCfg.php');  
    
    class CfgApp extends AbstractCfg {
        
        function __construct(){ 
            $xmlFile    = 'app.xml';
            $arrAtribId = array('baseUrl','baseUrlHttp','baseUrlHttps','modules','defaultModule','magicModules','defaultTemplate','languages','defaultLang');
            parent::__construct($xmlFile,$arrAtribId);            
        }
        
        public static function get($id){
            $value = self::getValueForId($id, get_class());            
            if ($id == 'baseUrl') {
                //Certifica-se de incluir a barra normal (/) antes e depois do baseUrl.
                $value  = trim($value, '/');//Retira as barras antes e depois caso existam, para evitar inserí-las em duplicidade.
                $value  = "/$value/";
            }
            $value = trim($value);
            return $value;
        }
          
    }
?>
