<?php

    namespace sys\classes\mvc;
    use \sys\classes\util\File;
    use \sys\classes\util\Concat;
    
    class ViewPart {               
        
        protected   $bodyContent;
        protected   $viewFile;//path do arquivo estático usado como view.
        protected   $layoutName;
        protected   $params = array();
        
        function __construct($pathViewHtml=''){
            if (isset($pathViewHtml) && strlen($pathViewHtml) > 0) {            
                $arrParts           = explode('/',$pathViewHtml);
                $numParts           = count($arrParts);
                $this->layoutName   = (is_array($arrParts) && $numParts > 1)?$arrParts[$numParts-1]:$pathViewHtml; 

                $physicalTplPath    = \Url::physicalPath($pathViewHtml);

                if (!file_exists($physicalTplPath)){         
                    //O path informado não é qualificado. Deve-se montar a URL do template e verificar se o arquivo existe.
                    //Coloca a extensão no nome do arquivo, caso não tenha sido informada.                
                    $keyHtm             = strpos($pathViewHtml,'.htm');//Verifica se o path possui extensão .htm
                    $keyHtml            = strpos($pathViewHtml,'.html');//Verifica se o path possui extensão .html
                    $extHtml            = ($keyHtm !== false && $keyHtml !== false)?'':'.html';//Coloca a extensão html caso não tenha sido informada

                    $folderViews        = \LoadConfig::folderViews();      
                    $lang               = \Application::getLanguage();
                    $module             = \Application::getModule();
             
                    if (strlen($lang) > 0) $lang = $lang.'/';
                    //$viewFile       = $module.'/'.$folderViews.'/'.$lang.$pathViewHtml.$extHtml;//URL do arquivo template no módulo atual    
                    $viewFile       = APPLICATION_PATH.$module.'/'.$folderViews.'/'.$lang.'templates/'.$this->layoutName.$extHtml;//URL do arquivo template no módulo atual    
                    $viewFileCommon = APPLICATION_PATH.'common/'.$folderViews.'/'.$lang.'templates/'.$this->layoutName.$extHtml;//URL do arquivo template na pasta common   
                   
                    try {          
                       $urlViewFile         = \Url::physicalPath($viewFile);
                       $urlViewFileCommon  = \Url::physicalPath($viewFileCommon);
                       if (file_exists($urlViewFile)){
                           //Arquivo existe.
                           $this->setBodyContent($urlViewFile);    
                       } elseif (File::exists($urlViewFileCommon)){     
                           $this->setBodyContent($viewFileCommon); 
                       }
                   } catch(\Exception $e){
                       $this->showErr('Erro ao instanciar a viewPart solicitada -> '.$viewFile,$e);                    
                   }                     
                } else {
                    //O arquivo do path informado existe. Guarda o conteúdo do arquivo.
                    $this->setBodyContent($physicalTplPath);    
                }
            } else {
                //die('ViewPart(): Impossível continuar. O nome referente ao conteúdo HTML não foi informado'); 
            }
        }
        
        private function setBodyContent($viewFile){
            $this->bodyContent  = file_get_contents(\Url::physicalPath($viewFile));
            $this->viewFile     = $viewFile;                 
        }
        
        /**
         * Define uma string como conteúdo da viewPart.
         * Método utilizado geralmente quando não há um arquivo físico de conteúdo informado no construtor.
         *  
         * @param string $content Conteúdo da ViewPart.
         */
        function setContent($content){
            $this->bodyContent = (string)$content;
        }
        
        protected function showErr($msg,$e=NULL,$die=TRUE){
            $msgErr = "<b>".$msg.':</b><br/><br/>';
            if (is_object($e)) $msgErr .= $e->getMessage();
            if ($die) die($msgErr);
            echo $msgErr.'<br/><br/>';
        } 
        
        
        function render($layoutName=''){  
            if (isset($layoutName) && strlen($layoutName) > 0) $this->layoutName = $layoutName;
            $bodyContent    = $this->bodyContent;
            if (strlen($bodyContent) > 0) {
                $params          = $this->params;                      
                 
                if (is_array($params)) {                                     
                    foreach($params as $key=>$value){                        
                        $bodyContent = str_replace('{'.$key.'}',$value,$bodyContent);                
                    }                    
                }
            }
            return $bodyContent;            
        }        
        
        function __set($var,$value){
            $this->params[$var] = $value;
        }
        
    }
?>
