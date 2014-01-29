<?php

    
    class View {

        private $objHeader          = NULL;        
        private $tplFile            = '';           
        private $forceNewIncMin     = FALSE;
        private $pathTpl            = '';
        private $arrIncludeCfgOff   = array();
        private $includeCfgAllOff   = FALSE;
        private $pathView;
        private $arrAssign          = array();
        private $commonFolder       = FALSE; //Determina se o arquivo View está em common (TRUE) ou no módulo atual.
        
        function __construct($pathView,$common=FALSE){   
            $this->commonFolder = $common;
            $this->checkPathView($pathView);
        }                 
        
        function setContent($filename=''){
            if (strlen($filename) > 0) {
                $this->commonFolder = FALSE;
                $this->checkPathView($filename);            
            }
            return $this;
        }
        
        private function checkPathView($filename){  
            $container      = new DIContainer();                   
            $objUri         = $container->Uri();
            $objMvcParts    = $objUri->getMvcParts();            
            $module         = $objMvcParts->module;
            $viewExtension  = CfgApp::get('htmlExtension');  
            $common         = CfgApp::get('commonFolder');
            $extension      = '.'.$viewExtension;  
            
            $path           = ($this->commonFolder) ? $common.'/views/' : $module.'/classes/views/';
            $path           .= $filename.$extension;
            
            $find           = FALSE;
            
            if (file_exists($path)) {
                $find = TRUE;
            } else {
                $path = rtrim($path,$extension);
                if (file_exists($path)) {
                    $find = TRUE;
                }
            }
            
            if (!$find) {
                throw new \Exception("O arquivo '".$filename."' da view informada não foi localizado.");
            }
            echo $path.'<br>';
            $this->pathView = $path;
        }
        
        function assign($name,$value){
            $this->arrAssign[$name] = utf8_encode($value);
        }
        
        function getRender(){
            return $this->joinString();
        }
        
        function render(){
            $string = $this->joinString();
            echo $string;
        }
        
        private function joinString(){
            $string     = $this->getString();
            if (strlen($string) > 0) {
                $arrAssign  = $this->arrAssign;
                if (is_array($arrAssign)) {
                    foreach($arrAssign as $name => $value) {
                        $tag    = "{{$name}}";
                        $string = str_replace($tag,$value,$string);
                    }
                }
            }
            $string = utf8_decode($string);            
            return $string;
        }
        
        function setModel(){
            
        }
        
        function getString(){
            $string = file_get_contents($this->pathView);
            return $string;
        }
        
             
        
        /**
         * Cria um novo arquivo template caso ainda não exista e guarda o nome em $pathTpl.
         * 
         * Exemplo:
         * Caso o arquivo seja criado em modulo/viewParts/br/templates/blank.html, o valor da 
         * variávei $pathTpl será apenas templates/blank.html.
         * 
         * @return boolean Retorna TRUE caso o arquivo tenha sido criado com sucesso.
         */
        private function createNewTemplate(){
            $tplExists      = FALSE;
            $blankFilename  = 'blank.html';
            $objModule      = MvcFactory::getModule();
            $pathTemplate   = $objModule->tplLangFile($blankFilename); 
            $physicalPath   = \Url::physicalPath($pathTemplate);
            if (file_exists($physicalPath)){
                //O arquivo padrão não existe. Não precisa ser criado.
                $tplExists = TRUE;
            } else {
                //O arquivo padrão ainda não existe. Deve ser criado.
                $fp = @fopen($pathTemplate, "wb+");               
                if ($fp !== FALSE) {
                    fwrite($fp, "<div>{BODY}</div>");
                    fclose($fp);
                    $tplExists = TRUE;
                }
            }
            
            if ($tplExists) {
                $folderTpl      = \LoadConfig::folderTemplate();                  
                $this->pathTpl  = $folderTpl.'/'.$blankFilename; 
            }
            return $tplExists;
        }
        
        
        /**
         * Desabilita a inclusão da lista de javascript definida em config.xml, conforme o nó abaixo:
         * <header><include id='js'></include></header>
         * 
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout().
         * 
         * @return void
         */
        function cfgJsOff(){
            $this->includeCfgOff(Header::EXT_JS);
        }
        
        /**
         * Desabilita a inclusão da lista de javascript (arquivos externos) definida em config.xml, conforme o nó abaixo:
         * <header><include id='jsInc'>...</include></header>
         * 
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout(). 
         * 
         * @return void
         */        
        function cfgJsIncOff(){
            $this->includeCfgOff(Header::EXT_JS_INC);
        }   
        
        /**
         * Desabilita a inclusão da lista de css definida em config.xml, conforme o nó abaixo:
         * <header><include id='css'>...</include></header>
         * 
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout().
         *          
         * @return void
         */        
        function cfgCssOff(){
            $this->includeCfgOff(Header::EXT_CSS);
        }        
        
        /**
         * Desabilita a inclusão da lista de css (arquivos externos) definida em config.xml, conforme o nó abaixo:
         * <header><include id='cssInc'>...</include></header>
         *          
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout().
         * 
         * @return void
         */        
        function cfgCssIncOff(){
            $this->includeCfgOff(Header::EXT_CSS_INC);
        }    
        
        /**
         * Desabilita a inclusão dos plugins definidos em config.xml, conforme o nó abaixo:
         * <header><include id='plugins'>...</include></header>
         * 
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout().
         * 
         * @return void
         */        
        function cfgPluginOff(){
            $this->includeCfgOff('plugin');
        } 
        
        /**
         * Desabilita um tipo específico de include (parâmetro $ext) ou todos os includes 
         * definidos em config.xml, conforme o nó abaixo:
         * <header><include id='$ext'></include></header>
         * 
         * IMPORTANTE: 
         * Este método deve ser chamado antes de setLayout().
         * Este método exclui o arquivo minify de inclusão para css/js e cria um novo, se necessário, a cada load da página.
         * 
         * @param string $ext Pode ser css, js, cssInc, jsInc (vide constantes da classe Header)
         * @return void
         */        
        function includeCfgOff($ext='all'){
            if ($ext == 'all') {
                $this->forceCssJsMinifyOn();//Força recriar o arquivo minify de inclusão para css/js
                $this->includeCfgAllOff = TRUE;                 
            } else {
                $this->arrIncludeCfgOff[] = $ext;
            }
        }
        
        /**
         * Faz a junção do conteúdo parcial (ViewPart) com o template atual.
         * Os includes definidos no config.xml são processados na seguinte ordem:
         *  - plugins
         *  - css/js 
         * 
         * @param ViewPart $objViewPart 
         */
        function setLayout(ViewPart $objViewPart){
            if (is_object($objViewPart)) {                       
                
                $this->getObjHeader();//Inicializa um objeto Header
                
                $pathTpl                        = $this->getTemplate();    
                $objViewTpl                     = MvcFactory::getViewPart($pathTpl);
                $objViewTpl->BODY               = $objViewPart->render();                               
                $this->bodyContent              = $objViewTpl->render();
                $this->layoutName               = $objViewPart->layoutName;                
                
                if (strlen($pathTpl) > 0){    
                    
                    //Configurações lidas do arquivo config.xml:           
                    $plugins    = '';                    
                        
                    if (!$this->includeCfgAllOff) {    
                        /*
                         * Inclusões css e js:   
                         * As configurações de include definidas em config.xml devem ser carregadas.
                         */                       
                        
                        //1º - Faz a inclusão de cada PLUGIN definido no config.xml:
                        $plugins            = \LoadConfig::plugins();  
                   
                        //Faz a inclusão de arquivos css e js padrão.
                        try {                                                                                                                                                                  

                            //Plugins         
                            if (strlen($plugins) > 0) {                               
                                $arrPlugins = explode(',',$plugins);
                                if (is_array($arrPlugins)) {
                                    foreach($arrPlugins as $plugin) {                
                                        $this->setPlugin($plugin);
                                    }
                                }
                            }                      
                        } catch(\Exception $e){
                            $this->showErr('View()',$e,FALSE); 
                        }  
                         
                        //2º - Faz a inclusão de cada CSS/JS definidos no config.xml:
                        $arrIncludeCfgOff   = $this->arrIncludeCfgOff;                    
                        $arrExt             = Header::$arrExt;
                        foreach($arrExt as $fn) {
                            $key = array_search($fn, $arrIncludeCfgOff);
                            if ($key === FALSE) {                         
                                //A extensão atual NÃO consta na lista de exclusão. 
                                //Portanto, os includes dessa extensão devem ser incluídos.
                                $list   = \LoadConfig::$fn();                                
                                $this->objHeader->$fn($list);                        
                            }
                        }        
                        
                        //Verifica se apenas os plugins defindos em config.xml foram desabilitados:
                        $key = array_search('plugin', $arrIncludeCfgOff);
                        if ($key !== FALSE) $plugins = '';
                    } else {
                        //Todos os includes de config.xml devem ser ignorados.                        
                    }                                                        
                } else {
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'TEMPLATE_NOT_INFO'); 
                    throw new \Exception( $msgErr );                     
                }                
            } else {
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'VIEWPART_NOT_INFO'); 
                throw new \Exception( $msgErr );                                        
            }                        
        }
        
        private function getObjHeader(){
            $objHeader = $this->objHeader;            
            if (!is_object($objHeader)) $objHeader = new Header();
            $this->objHeader = $objHeader;    
            return $objHeader;
        }        
               
        function setPlugin($plugin){
           if (strlen($plugin) > 0){
               $arr = Plugin::$plugin();                 
               if (is_array($arr) && count($arr) > 0){ 
                   $objHeader = $this->getObjHeader();
                   try {                       
                       foreach($arr as $ext=>$listInc){                           
                           $objHeader->memoIncludeJsCss($listInc, $ext);
                       }
                       $this->objHeader = $objHeader;
                   } catch(\Exception $e){
                       $this->showErr('Erro ao incluir o Plugin solicitado ('.$plugin.')',$e);      
                   }
               } else {
                   echo 'Plugin não retornou dados de inclusão (css | js).';
               }
           } 
        }                         
        
        /**
         * Monta e retorna a saída HTML das partes processadas na camada View.
         * Carrega os includes definidos em config.xml (plugins, css, cssInc, js, jsInc) 
         * ao chamar o método setLayout e ao chamar também os métodos setCss(), setCssInc(), setJs(), setJsInc().
         * 
         * Permite fazer o cache da string resultante caso um objeto Cache (parâmetro $objMemCache)seja informado.
         *  
         * @param string $layoutName 
         * Se informado, $layoutName será usado para definir o nome do arquivo minify de js e css (método getIncludes()),
         * sobrepondo o que foi lido anteriormente ao carregar a ViewPart no método setLayout().
         * 
         * @param util\Cache $objMemCache 
         * Se for um objeto Cache válido faz o cache do conteúdo HTML gerado.
         * 
         * @return string Conteúdo HMTL
         */
        function renderOld($layoutName='',$objMemCache=NULL){                
            $css    = '';
            $js     = '';
            
            if (isset($layoutName) && strlen($layoutName) > 0) {
                $this->layoutName   = $layoutName;                
            
                /*
                 * Gera as tags de inclusões js e css.             
                 */
                $css  = $this->getIncludesCss();
                $js   = $this->getIncludesJs();                   
            }                     
            
            $bodyContent               = trim($this->bodyContent);
            $params                    = $this->params;                                       
            $params['INCLUDE_CSS']     = $css;
            $params['INCLUDE_JS']      = $js;                                                                              
            
            if (is_array($params)) {
                foreach($params as $key=>$value){
                    $bodyContent = str_replace('{'.$key.'}',$value,$bodyContent);                
                }
            }
            if (is_object($objMemCache)) {
                //O cache foi ativado para o conteúdo atual. Armazena $bodyContent em cache.
                $objMemCache->setCache($bodyContent);
            }
            echo $bodyContent;
        } 
        
        /**
         * Gera/retorna as tags de inclusão de arquivos CSS.
         * 
         * IMPORTANTE: 
         * A ordem da extensão (EXT_CSS_INC e EXT_CSS) reflete a ordem em que as tags serão
         * incluídas no arquivo. Por exemplo, para incluir primeiro o arquivo minify 
         * (compactação de todos os arquivos css dentro de um único arquivo) e depois os arquivos com
         * inclusões separadas, faça a chamada conforme a ordem abaixo:
         * 
         * <code>
         *      $inc        = $this->getIncludes(Header::EXT_CSS); //Gera o minify em um único arquivo
         *      $inc        .= $this->getIncludes(Header::EXT_CSS_INC);         
         * </code>
         * 
         * Ou então, caso queira incluir os includes separados antes do arquivo minify,
         * siga o exemplo abaixo:
         * 
         * <code>
         *      $inc        .= $this->getIncludes(Header::EXT_CSS_INC); 
         *      $inc        = $this->getIncludes(Header::EXT_CSS);//Gera o minify em um único arquivo     
         * </code>
         * 
         * @return string 
         * Tags <link rel='stylesheet'...></script> 
         * Para consultar/alterar as tags de inclusão consulte mvc\Header->setTag().
         */
        private function getIncludesCss(){            
            $inc        = '';
            $inc        .= $this->getIncludes(Header::EXT_CSS_INC); 
            $inc        .= $this->getIncludes(Header::EXT_CSS);//Gera o minify em um único arquivo            
            return $inc;
        }
        
        /**
         * Gera/retorna as tags de inclusão de arquivos JS.
         * 
         * IMPORTANTE: 
         * A ordem da extensão (EXT_JS_INC e EXT_JS) reflete a ordem em que as tags serão
         * incluídas no arquivo. Por exemplo, para incluir primeiro o arquivo minify 
         * (compactação de todos os arquivos css dentro de um único arquivo) e depois os arquivos com
         * inclusões separadas, faça a chamada conforme a ordem abaixo:
         * 
         * <code>
         *      $inc        = $this->getIncludes(Header::EXT_JS); //Gera o minify em um único arquivo
         *      $inc        .= $this->getIncludes(Header::EXT_JS_INC);         
         * </code>
         * 
         * Ou então, caso queira incluir os includes separados antes do arquivo minify,
         * siga o exemplo abaixo:
         * 
         * <code>
         *      $inc        .= $this->getIncludes(Header::EXT_CSS_INC); 
         *      $inc        = $this->getIncludes(Header::EXT_CSS);//Gera o minify em um único arquivo     
         * </code>
         * 
         * @return string 
         * Tags <script type='text/javascript'...></script>
         * Para consultar/alterar as tags de inclusão consulte mvc\Header->setTag().
         */        
        private function getIncludesJs(){  
            $inc        = '';
            $inc        .= $this->getIncludes(Header::EXT_JS_INC); 
            $inc        .= $this->getIncludes(Header::EXT_JS); //Gera o minify em um único arquivo           
            return $inc;
        }
        
        /**
         * Método de suporte para getIncludesCss() e getIncludesJs().
         * 
         * @param string $ext Pode ser css, cssInc, js ou jsInc
         * @return string
         * @throws Exception Caso um problema ocorra ao executar Component::yuiCompressor()
         */
        private function getIncludes($ext){    
            try {                
                $objHeader = $this->getObjHeader();                               
                return $objHeader->getTags($ext,$this->layoutName);
            } catch(\Exception $e) {                                   
                throw $e;
            }
        }                
        
        function __call($fn,$args){
            $objHeader  = $this->getObjHeader(); 
            
            if (is_object($objHeader)){        
                $ext = '';                
                switch($fn){                
                    case 'setCss':
                        $ext = $objHeader::EXT_CSS;
                        break;
                    case 'setCssInc':
                        $ext = $objHeader::EXT_CSS_INC;
                        break;
                    case 'setJs':
                        $ext = $objHeader::EXT_JS;
                        break;
                    case 'setJsInc':
                        $ext = $objHeader::EXT_JS_INC;
                }
                                
                if (strlen($ext) > 0){
                    $listFiles = (isset($args[0]))?$args[0]:'';
                    if (strlen($listFiles) > 0) {                        
                        try {
                        $objHeader->memoIncludeJsCss($listFiles,$ext);  
                        } catch(\Exception $e){
                            $this->showErr('Erro ao memorizar arquivo(s) de inclusão(ões) css | js -> '.$listFiles,$e);                    
                        }
                    } else {
                       echo "View->{$fn}(): Inclusão não realizada $listFiles<br>"; 
                    }
                } elseif ($fn == 'forceCssJsMinifyOn') {
                    //Força a compactação e junção dos includes (css e js), mesmo que o arquivo _min já exista.
                    $objHeader->forceCssJsMinifyOn();                       
                } elseif ($fn == 'forceCssJsMinifyOff') {
                    //Volta à situação padrão: apenas compacta e junta includes se o arquivo _min ainda não existir.
                    $objHeader->forceMinifyOff();     
                } elseif ($fn == 'onlyExternalCssJs') {
                    //Gera a página HTML com os includes (css e js) separados.
                    $objHeader->onlyExternalCssJs();  
                }
            }            
        }
    }
?>
