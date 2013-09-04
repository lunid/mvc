<?php

    namespace sys\classes\html;
    
    abstract class Html extends \sys\classes\util\Xml{
        protected $arrConfigParam   = array('id','name','onchange','onclick','onblur','css','cls','disabled');
        protected $params           = array();
        private $folderHtml         = 'sys/phtml';
        private $phtmlName          = '';
        
        /**
         * Define a pasta do arquivo phtml a ser utilizado pelo objeto filho.
         * 
         * A pasta deve ser definida sempre a partir da pasta raíz (parâmetro rootFolder do arquivo config.xml).
         * 
         * @param type $folderHtml 
         */
        function setFolderHtml($folderHtml=''){
            $this->folderHtml = $folderHtml;
        }
        
        /**
         * Define o nome do arquivo phtml a ser utilizado no objeto filho.
         * 
         * O nome NÃO deve conter extensão. Exemplo:
         * Para a classe Combobox o nome do arquivo deve ser apenas 'combobox'.
         * Ao chamar o método getPathHtml(), e considerando que o parâmetro folderHtml não foi alterado, 
         * o retorna será:
         * 
         * rootFolder/sys/phtml/combobox.phtml.
         * 
         * @param string $phtmlName 
         */
        protected function setHtml($phtmlName) {
            if (strlen($phtmlName) > 0) {
                //Retira a extensão do arquivo, se houver.
                $phtmlName          = str_replace('.htm','',$phtmlName);
                $phtmlName          = str_replace('.html','',$phtmlName);
                $phtmlName          = str_replace('.phtml','',$phtmlName);
                
                $this->phtmlName = $phtmlName;
            }
        }
        
        protected function getPathHtml(){
            $folderHtml = $this->folderHtml;
            $phtmlName  = $this->phtmlName;
            $path       = $folderHtml.'/'.$phtmlName.'.phtml';   
            $path       = str_replace('//','/',$path);
            return $path;
        }
        
        /**
         * Adiciona uma propriedade nova ao objeto filho da classe atual.
         * 
         * Cada nova propriedade torna-se um índice no array $arrConfigParam.
         * 
         * @param string $param 
         */
        protected function addParam($param){
            if (strlen($param) > 0) $this->arrConfigParam[] = $param;
        }
        
        /**
         * Define as propriedades do objeto atual a partir do parâmetro $objParams recebido.
         * 
         * As propriedades do objeto $objParams devem ter correspondência com os valores $arrConfigParam.
         * O nome/valor de cada propriedade do primeiro define o nome/valor da mesma propriedade no objeto atual.
         * 
         * @param stdClass $objParams Objeto de dados contendo apenas propriedades.
         */
        function popParams($objParams){
            if (is_object($objParams)) {
                $arrParams = get_object_vars($objParams);
                if (is_array($arrParams)) {
                    foreach($arrParams as $var=>$value) {                        
                        $this->$var = $value;                        
                    }                                        
                }
            }
        }
        

        function disabledOn(){
            $this->disabled = "disabled='disabled'";
        }
        
        function disabledOff(){
            $this->disabled = '';
        }
        
        function render(){            
            return $this->renderHtml();
        }
        
        
        /**
        * Função que efetua captura do HTML Template e executa o PHP inserido nele
        * 
        * @param string $html_template Nome do arquivo físico a ser processado (PHTML)
        * 
        * @return string HTML porcessado
        */
        protected function renderHtml(){
            try{
                $phtmlFile      = $this->getPathHtml();                
                $pathPhtml      = \Url::physicalPath($phtmlFile);            
                $params         = $this->params;
                $arrConfigParam = $this->arrConfigParam;
                
                //Inicializa todos os parâmetros do objeto atual:
                if (is_array($arrConfigParam)) {                    
                    foreach($arrConfigParam as $nameParam) {                        
                        $$nameParam = '';
                    }                
                }
                
                if (is_array($params)) {                    
                    foreach($params as $nameParam=>$value) {                        
                        $$nameParam = $value;
                    }                
                }
                
                if (strlen($name) == 0 && strlen($id) > 0)  $name           = $id;
                if (strlen(@$onchange) > 0)                 $onchange       = "onchange=\"{$onchange}\"";
                if (strlen(@$onclick) > 0)                  $onclick        = "onchange=\"{$onclick}\"";
                if (strlen(@$css) > 0)                      $css            = "style=\"{$css}\"";                
                if (strlen(@$cls) > 0)                      $cls            = "class=\"{$cls}\"";                               
                if (strlen(@$field_name) > 0)               $field_name     = "field_name=\"{$field_name}\"";                               
                                
                ob_start();
                              
                if (!@include($pathPhtml)) {                    
                    //Não localizou o path na pasta sys. Procura no módulo atual                    
                    $module     = \Application::getModule();
                    $folderHtml = $module.'/phtml';
                    $this->setFolderHtml($folderHtml);
                    $phtmlFile  = $this->getPathHtml();
                    $pathPhtml  = \Url::physicalPath($phtmlFile);    
                    
                    if (!@include($pathPhtml)){
                        throw new \Exception (__METHOD__."(): Arquivo {$pathPhtml} não existe.");                    
                    }                                      
                }
                 
                //Include realizado com sucesso                
                $output = ob_get_contents();                
                ob_end_clean();             
                
                return $output;
            }catch(Exception $e){
                throw $e;
            }
        }
        
        
        /** 
         * Imprime os parâmetros definidos para o objeto atual 
         */
        function showParams(){
            print_r($this->params);            
        }
        
        function __set($name,$value){
            $arrConfigParam     = $this->arrConfigParam;                 
            $key                = array_search($name, $arrConfigParam);
            if ($key !== FALSE) {
                $this->params[$name] = $value;
            } else {
                throw new \Exception("HTML->set(): Parâmetro {$name} não permitido.");
            }            
        }
        
        function __get($var){
            $params = $this->params;
            return (isset($params[$var]))?$params[$var]:'';
        }
    }
?>
