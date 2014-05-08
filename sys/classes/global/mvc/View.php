<?php
    
    class View {

        private $headerInc          = array();// Scripts a serem incluídos no cabeçalho da página.                                    
        private $footerInc          = array();// Scripts a serem incluídos no rodapé da página.
        private $pathView;
        private $arrAssign          = array();
        private $commonFolder       = FALSE; //Determina se o arquivo View está em common (TRUE) ou no módulo atual.
        private $includePosition    = 'header';//Local a incluir javascript
        
        function __construct($filename, $common=FALSE){   
            $this->commonFolder = $common;
            $this->checkPathView($filename);          
        }      
        
        /**
         * Define explicitamente uma lista de arquivos javascript para a view atual,
         * que devem ser incluídos no topo da página (seção Header).
         * Chamar este método ou o método javascript() tem o mesmo efeito.
         * 
         * @see javascript()
         * @param string $list Lista de nomes de arquivos separados por vírgula. Ex.: home,form,auth/acesso
         * @param string $filenameDest Nome do arquivo de destino (não usar extensão). Ex.: index
         * @return void
         */
        function javascriptTop($list,$filenameDest){
            $this->includePosition = 'header';
            $this->setInclude('js',$list,$filenameDest);   
        }
        
        /**
         * Define explicitamente uma lista de arquivos javascript para a view atual,
         * que devem ser incluídos na parte inferior da página, imediatamente após fechar a tag <BODY> e antes de <HTML>.         
         * 
         * @see javascript()
         * @param string $list Lista de nomes de arquivos separados por vírgula. Ex.: home,form,auth/acesso
         * @param string $filenameDest Nome do arquivo de destino (não usar extensão). Ex.: index
         * @return void
         */        
        function javascriptFooter($list,$filenameDest){
            $this->includePosition = 'footer';
            $this->setInclude('js',$list,$filenameDest);   
        }
        
        /**
         * Define a lista de arquivos javascript utilizados na view atual.
         * A tag de inclusão, por padrão, será definida no cabeçalho da página.
         * Chamar este método é o mesmo que chamar javascriptTop().
         * 
         * @param string $list Lista de nomes de arquivos separados por vírgula. Ex.: home,form,auth/acesso
         * @param string $filenameDest Nome do arquivo de destino (não usar extensão). Ex.: index
         * @return void
         */
        function javascript($list,$filenameDest){
            $this->setInclude('js',$list,$filenameDest);                     
        }
        
        /**
         * Define a lista de arquivos de Css utilizados na view atual.
         * 
         * @param string $list Lista de nomes de arquivos separados por vírgula. Ex.: home,form,theme/table
         * @param string $filenameDest Nome do arquivo de destino (não usar extensão). Ex.: index
         * @return void
         */
        function css($list,$filenameDest){
            $this->includePosition = 'header';
            $this->setInclude('css',$list,$filenameDest);            
        }        
        
        /**
         * Define os parâmetros de cabeçalho para um tipo específico de include (css ou js).
         * Os dados retornados serão usados para compactar todos os includes de um tipo em um
         * único arquivo.
         * 
         * @param string $extension Pode ser 'css' ou 'js'
         * @param type $list Lista de nomes de arquivos separados por vírgula. Não é necessário informar a pasta root de assets.
         * @param type $filenameDest Nome do arquivo de destino que será criado para os includes.
         * @return void
         */
        private function setInclude($extension,$list,$filenameDest){
            $objHeader = new Header($extension,$list,$filenameDest);
            $objHeader->loadIncludeParams();
            if ($this->includePosition == 'header') {
                //Inclusões de cabeçalho
                $this->headerInc[$extension] = $objHeader;                 
            } else {
                //Inclusões de rodapé (footer)
                $this->footerInc[$extension] = $objHeader;                 
            }
        }
        
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
            $arrAssign  = $this->arrAssign;                       
             
            if (strlen($string) > 0) {
                $arrAssign['CSS']               = $this->getTagIncHeader('css');
                $arrAssign['SCRIPT_HEADER']     = $this->getTagIncHeader('js');
                $arrAssign['SCRIPT_FOOTER']     = $this->getTagIncFooter('js');
                if (is_array($arrAssign)) {
                    foreach($arrAssign as $name => $value) {
                        $tag    = "{{$name}}";
                        $string = str_replace($tag,$value,$string);
                    }
                }
            }            
            return utf8_decode($string);
        }

        function getString(){
            $string = file_get_contents($this->pathView);
            return $string;
        }
        
        /**
         * Retorna tag de inclusão a ser inserida no cabeçalho da página.
         * 
         * @param string $extension (css ou js)
         * @return string Tag de inclusão contendo URL do arquivo que mescla todos os includes.
         */
        private function getTagIncHeader($extension){
            return $this->getTagInc($extension, $this->headerInc);
        }
        
        /**
         * Retorna tag de inclusão a ser inserida no rodapé da página (após o <body> e antes do <html>).
         * 
         * @param string $extension (css ou js)
         * @return string Tag de inclusão contendo URL do arquivo que mescla todos os includes.
         */        
        private function getTagIncFooter($extension){
            return $this->getTagInc($extension, $this->footerInc);
        }        
        
        /**
         * Retorna a tag HTML de inclusão para o tipo informado (js ou css).
         * 
         * @param string $extension (js ou css)
         * @return string
         */
        private function getTagInc($extension, $arrInc){
            $tagInc     = '';
            $headerInc  = $this->headerInc;
            
            if (isset($arrInc[$extension]) && is_object($arrInc[$extension])) {
                $objHeader  = $arrInc[$extension];
                $tagInc     = $objHeader->getTagFromFileMerge();        
            }
            return $tagInc;
        }               
    }
?>
