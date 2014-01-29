<?php

    require_once('AbstractCfg.php');  
    
    class CfgEnv extends AbstractCfg {
        
        function __construct($loadXml=TRUE){ 
            $host       = $_SERVER['HTTP_HOST'];
            $xmlFile    = 'host/'.$host.'.xml';
            
            $arrAtribId = array(
                'rootFolder',
                'baseUrlHttp',
                'baseUrlHttps'
            );
            
            if ($loadXml == TRUE) {
                parent::__construct($xmlFile,$arrAtribId);            
            } else {
                //Permite carregar o arquivo XML novamente na próxima chamada desse método.
                $this->setStatusLoadCfg(FALSE);
            }
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
            if ($id == 'rootFolder') {
                //Certifica-se de incluir a barra normal (/) antes e depois do rootFolder.
                $value  = trim($value, '/');//Retira as barras antes e depois caso existam, para evitar inserí-las em duplicidade.
                //if (strlen($value) == 0) $value = 'public_html';
                //$value  = "/$value/";
            }
            $value = trim($value);
            return $value;
        }
          
    }
?>
