<?php

    require_once('AbstractCfg.php');  
    
    class CfgApp extends AbstractCfg {
        
        function __construct(){ 
            $xmlFile    = 'app.xml';
            $arrAtribId = array(
                'baseUrl',
                'rootFolder',
                'baseUrlHttp',
                'baseUrlHttps',
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
            if ($id == 'baseUrl') {
                //Certifica-se de incluir a barra normal (/) antes e depois do baseUrl.
                $value  = trim($value, '/');//Retira as barras antes e depois caso existam, para evitar inserí-las em duplicidade.
                if (strlen($value) == 0) $value = 'public_html';
                $value  = "/$value/";
            }
            $value = trim($value);
            return $value;
        }
          
    }
?>
