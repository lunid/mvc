<?php 
    
    use sys\classes\util\Dic;
    require_once(PATH_PROJECT . 'sys/classes/util/Xml.php');  
    class Url extends sys\classes\util\Xml {
          
        private $nodesUrl = NULL;
        
        /**
         * Ao instanciar um objeto da classe atual é possível informar um arquivo XML
         * que contém as URLs utilizadas no projeto.
         * 
         * Por padrão, o arquivo urls.xml fica na raíz do projeto.
         * Veja exemplo desse arquivo na pasta modelo/
         * 
         * Exemplo de uso:
         * <code>
         *      $objUrl = new \Url();
         *      $objUrl->common();                        
         *      $dominioHttp =  $objUrl->DOMINIO_HTTP;
         * </code>
         * 
         * @param string $pathXml Path de um arquivo XML, caso queira informar outro diferente de urls.xml.
         */
        function __construct($pathXml='/urls.xml'){
            $msgErr     = '';
            $pathXml    = APPLICATION_PATH.$pathXml;
            
            $urlXml = self::physicalPath($pathXml);
   
            if (file_exists($urlXml)) {                   
                $arrPath        = pathinfo($urlXml);                
                $extension      = $arrPath['extension'];
                if ($extension == 'xml') {                    
                    $objXml = self::loadXml($urlXml);  
                    if (is_object($objXml)) {
                        $this->objXml = $objXml;
                        $this->loadVars($objXml);
                    } else {                
                        $msgErr = 'Impossível ler o arquivo '.$pathXml;                                            
                    }
                } else {
                   $msgErr = 'O arquivo informado parece não ser um arquivo XML';                                                                 
                }
            } 
            if (strlen($msgErr) > 0) echo $msgErr;
        }
        /**
         * Recebe um array associativo que será convertido em URL no formato
         * rootFolder/modulo/controller/action/...onde rootFolder é lido do arquivo config.xml.
         *          
         * @param array $arrUrl Array associativo. 
         * Exemplo: 
         * O array('module'=>'admin','controller'=>'escolas','action'=>'home','id'=>11) retornará
         * /rootFolder/admin/escola/home/id/11
         * 
         * @return string 
         */                
        public static function setUrl(array $arrOptions){   
            $url            = '/';
            $rootFolder     = \LoadConfig::rootFolder();
            $lang           = \Application::getLanguage();            
            
            if (strlen($rootFolder) > 0) $url .= $rootFolder.'/';
            if (strlen($lang) > 0) $url .= $lang.'/';
            
            foreach($arrOptions as $key=>$value) {
                if (($key == 'module' || $key == 'controller' || $key == 'action')) {
                    if (strlen(trim($value)) > 0) $url .= $value.'/';
                } else {
                    $url .= $key.'/'.$value;                
                }
            }
            return $url;
        }
        
        public static function mvc($module='',$controller='',$action=''){
            $arrOptions = array('module'=>$module,'controller'=>$controller,'action'=>$action);
            return self::setUrl($arrOptions);
        }
        
        /**
         * Retorna o caminho físico da URI informada.
         * 
         * Exemplo: c:/serverFolder/projectFolder/...
         * 
         * @param string $uri Exemplo: app/phtml/table.phtml
         * @return string
         */
        public static function physicalPath($uri=''){
            $uri  = str_replace('//','/',$uri);
            $path = $uri;
            
            $root = $_SERVER['DOCUMENT_ROOT'] == "" && PATH_PROJECT != "" ? PATH_PROJECT : $_SERVER['DOCUMENT_ROOT'];
            $root = str_replace('//','/',$root);
            
            //Trata ultimo caractere de root
            if(substr($root, -1) == "/"){
                $root = substr($root, 0, strlen($root)-1);
            }
            
            $path = $root.'/'.self::getRootFolder().$uri;
            $path = str_replace('//','/',$path);
            $path = str_replace('\/','/',$path);
            
            return $path;
        }
        
       
        /**
         * Define o caminho absoluto do path informado retirando o caminho físico, se houver.
         * 
         * Por exemplo, caso o caminho ($path) informado seja c:/projetos/folder/...
         * path de retorno será /folder/...
         * 
         * @param string $path
         * @return string
         */
        public static function absolutePath($path){           
           $root    = $_SERVER['DOCUMENT_ROOT'];
           $path    = str_replace($root,'/',$path);           
           return $path;            
        }

        public static function relativeUrl($uri){
            $path = $uri;                             
           if (strlen($uri) > 0) {                    
               $root            = $_SERVER['DOCUMENT_ROOT'];
               $rootFolder      = \LoadConfig::rootFolder();               
               $physicalPath    = $root.'/'.self::getRootFolder();
               $physicalPath    = str_replace('//','/',$physicalPath);
               $path            = str_replace($physicalPath,'',$uri);               
           }
           $path = str_replace('//','/',$path);
           return $path;
        }
        
        public static function getRootFolder(){
            $rootFolder     = \LoadConfig::rootFolder();
            $folder         = (strlen($rootFolder) > 0)?$rootFolder.'/':'';
            return $folder;
        }
        
        public static function siteUrlHttp($params){
            return self::siteUrl($params,'http');
        }
        
        
        public static function siteUrlHttps($params){
            return self::siteUrl($params,'https');
        }          
        
        /**
         * Método de suporte a siteUrlHttp e siteUrlHttps.
         * 
         * Retorna a URL do site conforme os parâmetros baseUrlHttp, baseUrlHttps e rootFolder do arquivo config.xml.
         * 
         * O index.php será incluído na URL. Por exemplo:
         * <code>
         *  echo Url::siteUrlHttp('catalogo/produto/abcd');
         *</code>
         * Imprimirá: http://www.seudominio.com.br/rootFolder/index.php/catalogo/produto/abcd
         * 
         */
        private static function siteUrl($params,$protocol='http'){
            if (is_array($params)) $params = join('/',$params);
            $baseUrl      = ($protocol == 'http')?\LoadConfig::baseUrlHttp():\LoadConfig::baseUrlHttps();
            $baseUrl      = str_replace($protocol.'://','',$baseUrl);
            $rootFolder   = \LoadConfig::rootFolder();
            $folder       = (strlen($rootFolder) > 0)?$rootFolder.'/':'';
            $uri          = $protocol.'//'.$baseUrl.'/'.$folder.$params;
            
            return $uri;
        }
        
        public static function getUrlLibInitComponent($folderComponent){
            $folderSys      = \LoadConfig::folderSys();
            $folderLib      = \LoadConfig::folderLib();
            $folderComps    = \LoadConfig::folderComps(); 
            
            if (strlen($folderLib) == 0 || strlen($folderComps) == 0) {
                //Arquivo não existe
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'COMPS_UNDEFINED');
                throw new \Exception( $msgErr );              
            }
            $class          = ucfirst($folderComponent);
            $classPath      = "{$folderSys}/{$folderLib}/{$folderComps}/{$folderComponent}/classes/Lib{$class}.php";
            return $classPath;
        }
        
        public static function getUrlEmailTpl(){
            $folderSys      = \LoadConfig::folderSys();
            $folderLib      = \LoadConfig::folderLib();
            $folderComps    = \LoadConfig::folderComps();       
            $classPath      = "{$folderSys}/common/{$folderComps}/{$folderComponent}/classes/Lib{$class}.php";
        }
        
        
        /**
         * Carrega um nó (mesmo nome do atributo $method) do arquivo XML lido no construtor e deixa as tags url como variáveis,
         * podendo ser consultadas a partir de seus ids, via método mágico __get().
         *          
         * @param string $method Deve ser o mesmo nome da tag que agrupa uma ou mais tags <url..> no XML lido.
         */
        function __call($method,$params){
            $objXml = $this->objXml;            
            if (is_object($objXml)) {                
                $this->nodesUrl = $objXml->$method->url;
            } else {
                echo 'Um objeto XML obrigatório não foi localizado.';
            }            
        }
        
        /**
         * Método mágico usado para acessar os valores do nó carregado na chamada de __call().
         * 
         * @param string $id Nome da tag solicitada.
         * @return string
         */
        function __get($id){
            $value      = '';
            $nodesUrl   = $this->nodesUrl;
            if (!is_null($nodesUrl)) {
                $value = self::valueForAttrib($nodesUrl,'id',$id); 
            }            
            return $value;
        }
        
        public static function __callstatic($name,$args){
            $paramId    = (isset($args[0]))?$args[0]:'';
            $objUrl     = new \Url();
            $paramValue = $objUrl->
                    $name(); 
            print_r($paramValue);
            return $paramValue;            
        }
    }
?>
