<?php
    
    class View {

        private $header             = array();        
        private $tplFile            = '';           
        private $forceNewIncMin     = FALSE;
        private $pathTpl            = '';
        private $arrIncludeCfgOff   = array();
        private $includeCfgAllOff   = FALSE;
        private $pathView;
        private $arrAssign          = array();
        private $commonFolder       = FALSE; //Determina se o arquivo View está em common (TRUE) ou no módulo atual.
        
        function __construct($filename, $common=FALSE){   
            $this->commonFolder = $common;
            $this->checkPathView($filename);
            $this->header = new Header();
        }      
        
        function javascript($list,$filenameDest){
            $objHeader                  = new Header('js',$list,$filenameDest);
            $this->header['javascript'] = $objHeader->getHeaderParams();                      
        }
        
        function css($list,$filenameDest){
            $objHeader           = new Header('css',$list,$filenameDest);
            $this->header['css'] = $objHeader->getHeaderParams();               
        }        
        
        /*
        private function joinIncludes($arrList,$extension){
            $stringInc = '';
            if (is_array($arrList)) {
                foreach($arrList as $filename) {
                    $incFilename  = str_replace('.'.$extension,'',$filename);
                    $path         = 'assets/scripts/'.$incFilename.'.'.$extension;
                    
                    if (file_exists(realpath($path))) {
                        $stringInc .= file_get_contents($path);
                    } else {
                        throw new \Exception("O arquivo '".$path."' não foi localizado.");
                    }
                }
            }
            return $stringInc;
        }*/
        
        /**
         * Define um arquivo template diferente daquele definido no construtor.
         * 
         * @param string $filename Nome do arquivo (sem path e sem extensão)
         * @return \View
         */
        function setContent($filename=''){
            if (strlen($filename) > 0) {
                $this->commonFolder = FALSE;
                $this->checkPathView($filename);            
            }
            return $this;
        }
        
        /**
         * Localiza/valida a existência do arquivo físico informado no construtor do objeto atual.
         * O nome do arquivo também pode ser definido pelo método setContent().
         * 
         * @param string $filename Nome do arquivo (sem path e sem extensão)
         * @throws \Exception Caso o arquivo informado não tenha sido localizado.
         */
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
            //echo $path.'<br>';
            $this->pathView = $path;
        }
        
        /**
         * Define um valor para uma variável contida no arquivo view atual.
         * Ao invés de uma variável, pode receber um único parâmetro contendo um array associativo
         * com uma ou mais variáveis.
         * 
         * @param mixed $param Pode ser uma string ou um array associativo
         * @param string $value
         */
        function assign($param,$value=''){
            if (is_array($param)) {
                foreach($param as $var => $value) {
                    $this->arrAssign[$var] = utf8_encode($value);
                }
            } else {
                $this->arrAssign[$param] = utf8_encode($value);
            }
        }
        
        /**
         * Retorna a saída da string referente á view atual.
         * Útil caso seja necessário manipular a string antes de retorná-la na tela do usuário.
         * 
         * @return string
         */
        function getRender(){
            return $this->joinString();
        }
        
        /**
         * Imprime na tela a saída da string referente à view atual.
         * 
         * @return void
         */
        function render(){
            $string = $this->joinString();
            echo $string;
        }
        
        /**
         * Faz a junção de variáveis com os marcadores da string da view atual.
         * 
         * @return string
         */
        private function joinString(){
            $string     = $this->getString();
            $script     = '';
            $css        = '';
            $header     = $this->header;
            $arrAssign  = $this->arrAssign;
            
            if (is_object($header['javascript'])) {
                $objHeaderJs        = $header['javascript'];
                $arrPathIncludes    = $objHeaderJs->arrPathIncludes;
                $pathDest           = $objHeaderJs->pathDest;
   
                $vars = array( 
                    'encode' => true, 
                    'timer' => true, 
                    'gzip' => true, 
                    'closure' => true,
                    'echo' => false
                );
               
                $minified   = new Minifier( $vars );             
                $script     = "<script src=\"".$minified->merge( $pathDest, 'assets/scripts/min', $arrPathIncludes )."\"></script>";               
            }
             
            if (strlen($string) > 0) {
                $arrAssign['CSS']       = $css;
                $arrAssign['SCRIPT']    = $script;
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

        function getString(){
            $string = file_get_contents($this->pathView);
            return $string;
        }
    }
?>
