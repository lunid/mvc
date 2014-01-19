<?php

/*
 * Classe abastrata que contém os recursos comuns a todos os Controllers
 * @abstract
 */    
    use \sys\classes\performance\Cache;
    
    abstract class Controller {
        
        private $memCache;
        private $nameCache  = NULL;
        private $arrView    = array();
        
        function __construct(){
             
        }  
        
        function addView($nameView, $objView){
            $this->arrView[$nameView] = $objView;  
        }
        
        function getView($nameView=''){
            $objView    = NULL;
            $arrView    = $this->arrView; 
           
            if (strlen(trim($nameView)) == 0) $nameView = 'default';
            if (isset($arrView[$nameView])) {
                $objView = $arrView[$nameView];
            }
            
            if (!is_object($objView)) {                
                throw new \Exception("Controller->getView(): o objeto View solicitado não foi encontrado.");
            }
            
            return $objView;
        }
        
        /*
         * Método que recebe o nome do arquivo e um objeto cujos atributos 
         * representam as variáveis a concatenar.
         * 
         * @param $fileName (nome do arquivo HTML que servirá de matriz para a página solicitada).
         * @param $objParams (os atributos representam os parâmetros usados para concatenar com o HTML de $fileName)
         * @return String
        */            
        protected function view($fileName,$objParams = NULL){
           
            $tpl            = $this->templateFile;            
            $arrPlugin      = $this->arrPlugin;
            $urlFile        = 'app/views/'.$fileName.'View.html';
            $urlTpl         = 'app/views/templates/'.$tpl.'.html';
            $arrJsCssInc    = $this->arrJsCssInc;
            
            if (count($arrJsCssInc['css']) == 0) $this->incCssJsFileName($fileName,'css');//Tenta incluir o css de $fileName            
            if (count($arrJsCssInc['js']) == 0) $this->incCssJsFileName($fileName,'js');//Tenta incluir o js de $fileName
            
            $this->checkUrlFile($urlFile);
            $this->checkUrlFile($urlTpl);                       
            
            $htmlTpl            = file_get_contents($urlTpl);
            $objParams->BODY    = utf8_encode(file_get_contents($urlFile)); 
            
            if (is_object($objParams)) {
                foreach($objParams as $key=>$value){
                    $htmlTpl = str_replace('{'.$key.'}',$value,$htmlTpl);                
                }
            }
            
            //Faz a inclusão de JS e CSS dos plugins da página:
            //==================================================================
            //Array multidimensional usado para agrupar os tipos de todos os plugins (extensão: js|css|jsInc|cssInc)
            //Ex: arrVarInc['css'][], arrVarInc['js'][]...
            $arrVarInc = array();
            
            //Faz a leitura de cada plugin e separa as strings de cada extensão. 
            //Guarda em $arrVarIn.
            if (is_array($arrPlugin) && count($arrPlugin) > 0){
                foreach($arrPlugin as $plugin){                    
                    foreach($plugin as $var=>$value){
                       $arrVarInc[$var][] = $value;   
                    }
                }                
            }
            
            //Agora, para cada extensão, faz o include e/ou concatena em um único arquivo.
            if (count($arrVarInc) > 0){
                $this->outFileMin = get_class($this).'Plugin';
                foreach($arrVarInc as $ext => $arrValue){  
                    $strValue   = join(ViewInclude::$separadorList,$arrValue);
                    $this->$ext = $strValue;
                }
            }
            //==================================================================
            
            $arrHeaderInc   = $this->arrHeaderInc;
            if (is_array($arrHeaderInc) && count($arrHeaderInc) > 0){                
                foreach($arrHeaderInc as $key=>$value) {
                    $htmlTpl = str_replace('{'.$key.'}',$value,$htmlTpl);
                }
            }
            
            header( "Expires: ".gmdate("D, d M Y H:i:s", time() + (24 * 60 * 60)) . " GMT");//adiciona 1 dia ao tempo de expiração
            echo $htmlTpl;
        } 
        
        /**
         * Habilita o cache para um método (action) específico do Controller atual.
         * 
         * O parâmetro $action será usado para compor o nome do cache de acordo com 
         * o formato modulo_action (vide método setNameCache()).
         * O período do cache, caso os parâmetros $period e $time sejam mantidos sem alteração, 
         * será de 30 dias.
         * 
         * Exemplo de uso:
         * <code>
         *  function actionIndex(){         
         *    $this->cacheOn(__METHOD__);
         *    ...
         * </code>
         * 
         * @param string $action Nome do método (action) onde cacheOn foi chamado.
         * @param string $period Unidade que representa o período de validade do cache (DAY, HOUR, MIN ou SEC).
         * @param integer $time Número que representa a quantidade do período.
         * @throws Exception 
         */
        function cacheOn($action,$period='DAY',$time=30){            
            try {
                $nameCache          = $this->setNameCache($action);                
                $this->nameCache    = $nameCache;                
                $objCache           = Cache::newCache($nameCache);
            
                if (is_object($objCache)) {
                   //Utiliza cache:
                   $objCache->setTime($period,$time);            
                   $this->memCache = $objCache;                     
                   $content        = $objCache->getCache();              
                   if (strlen($content) > 0) {
                        //Um conteúdo ref. ao parâmetro action foi localizado.
                       //Imprime o conteúdo em cache.
                       die($content);                       
                   }                                                  
                }           
            }catch(Exception $e){
                throw $e;
            }                        
        }
        
        function getMemCache(){
            return $this->memCache;
        }
        
        /**
         * Cria um nome para o cache do conteúdo gerado pelo método ($action) informado.
         * Por padrão, o nome do cache é formado pelo módulo atual + '_' + $action;
         * 
         * Exemplo:
         * Ao chamar setNameCache('index') no módulo admin, o nome do cache será admin_index.
         *  
         * @param string $action Nome do método __METHOD__ a ser guardado em cache.
         * @return string
         */
        private function setNameCache($action){
            $nameCache      = \Application::getModule().'_'.$action;
            $nameCache      = str_replace('::','_',$nameCache); 
            return $nameCache;
        }        

        /**
         * Limpa o cache de uma página.
         * O parâmetro recebido refere-se ao método onde cacheOff foi chamado.
         * 
         * Exemplo de uso:
         * <code>
         *  function actionIndex(){         
         *    $this->cacheOff(__METHOD__);
         *    ...
         * </code>
         * 
         * @return void
         * @param string $action Nome da action cujo conteúdo deve ser eliminado do cache.
         */
        function cacheOff($action){
            if (strlen($action) > 0) {
                $nameCache  = $this->setNameCache($action);                    
                if (strlen($nameCache) > 0) { 
                    $objCache = Cache::newCache($nameCache);
                    if (is_object($objCache)) {
                        //Um objeto Cache válido está disponível
                        $objCache->delete();
                    }                    
                }
                $this->memCache = NULL;
            }
        }
        
        function __set($var,$value){
           if (
                isset($value) && is_string($value) && 
                ($var == 'js' || $var == 'css' || $var == 'jsInc' || $var == 'cssInc')
           ){       
               if (strlen($value) == 0) return;
               
               //Identifica qual a inclusão solicitada (js ou css):
               //===============================================================
               $ext         = 'js';
               $tag         = 'INCLUDE_JS';
               $outFileMin  = $this->outFileMin;               
               $outFile     = (strlen($outFileMin) == 0)?get_class($this):$outFileMin;

               if ($var == 'css' || $var == 'cssInc'){
                   $ext = 'css';
                   $tag = 'INCLUDE_CSS';
               }
               //===============================================================
               
               $sep           = ViewInclude::$separadorList;
               $arrIncDefault = $this->arrIncDefault;
               if (isset($arrIncDefault[$var])) $value = $arrIncDefault[$var].$sep.$value;
               
               $include   = ($var == 'jsInc' || $var == 'cssInc')?TRUE:FALSE;                              
               $objInc    = new ViewInclude();
               
               $objInc->setInclude($include);//Se TRUE retorna o(s) include(s) do(s) arquivo(s) solicitado(s)
               $objInc->setList($value,$ext);
               
               $headerInc = $objInc->convert($outFile);//Retorna string com as tags <script> ou <link> para cada arquivo.         
               if ($headerInc !== FALSE) {
                   $this->arrHeaderInc[$tag]    .= $headerInc;
                   $arrInc                      = explode(',',$value);
                   foreach($arrInc as $inc)  $this->arrJsCssInc[$ext][] = $inc;
               }
           }
        }
    }
?>
