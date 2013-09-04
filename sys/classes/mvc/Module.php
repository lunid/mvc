<?php

    class Module {
        
        private $module;
        
        function __construct($module=''){
            $this->module = (strlen($module) > 0)?$module:\Application::getModule();
        }
        
        function getModule(){
            return $this->module;
        }

        
        function tplLangFile($path){
            $rootModule     = $this->viewPartsLangFile('');
            $tplFolder      = \LoadConfig::folderTemplate(); 
            if (strlen($tplFolder) > 0) $rootModule = $rootModule.$tplFolder.'/';
            
            $url            = $rootModule.$path;
            
            return $url;
        }
        
        function viewPartsLangFile($path){
            $rootModule     = $this->viewPartsFile('');
            $lang           = \Application::getLanguage();
            if (strlen($lang) > 0) $rootModule = $rootModule.$lang.'/';
            
            $url            = $rootModule.$path;
            
            return $url;
        }
        
        function viewPartsFile($path){
            $module       = $this->module;
            $folderViews  = \LoadConfig::folderViews();//viewParts
            $url          = $module.'/'.$folderViews.'/'.$path;
            return $url;
        }       
    }
?>
