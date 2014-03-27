<?php

    class CfgHost extends AbstractCfg {
        
        function __construct($loadXml=TRUE){ 

            $arrAtribId = array(
                'domainHTTP',
                'domainHTTPS',
                'rootFolder'
            );
            
            if ($loadXml == TRUE) {
                parent::__construct(self::getPathXmlFilename(),$arrAtribId);       
            } else {
                //Força o carregamento do arquivo XML novamente na próxima chamada desse método,
                //ao invés de ler os atributos em SESSION.
                $this->setXmlConfigInMemory(FALSE);
            }
        }
        
        /**
         * Retorna a pasta raíz do projeto atual, definido no arquivo xml em cfg/host/dominio.xml,
         * onde dominio.xml deve coincidir com o domínio atual. Por exemplo, se o domínio do projeto
         * for dev.projeto.com, então o arquivo xml deve ser dev.projeto.com.xml.
         * 
         */
        public static function getRootFolder(){
            $value  = self::get('rootFolder');
            $value  = trim($value, '/');//Retira as barras antes e depois caso existam, para evitar inserí-las em duplicidade.
            return $value;
        }
        
        public static function getDomain($protocol='http'){
            $id         = 'domainHTTP';
            $protocol   = strtoupper($protocol);
            if ($protocol == 'HTTPS') $id = 'domainHTTPS';
            $domain = self::get($id);
            if ($protocol == 'HTTPS' && strlen($domain) == 0) {
                //Nenhum domínio HTTPS foi definido. Retorna o https do domínio http.
                $domain = self::getDomain();
            }
            return $domain;
        }
        
        /**
         * Retorna a URL do site com a pasta root, se houver.
         * Por exemplo, se o domínio do site for dev.projeto.com e a pasta raíz for app,
         * então o retorno será dev.projeto.com/app/
         * 
         * @param $protocol Protocolo do domínio (http ou https)
         * @return string Domínio + pasta root + '/'
         */
        public static function getDomainPathRoot($protocol='http'){
            $domain         = self::getDomain($protocol);
            $domain         = rtrim(\CfgHost::getDomain(),'/');     
            $rootFolder     =  self::getRootFolder();
            $rootProject    = $domain.'/'.$rootFolder;
            return $rootFolder;
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
