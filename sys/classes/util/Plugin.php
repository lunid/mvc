<?php

    namespace sys\classes\util;  

    class Plugin extends Xml {        
        
        private static $arrExtension = array('css','cssInc','js','jsInc');
        
	public static function __callStatic($fn,$value){
            $plugin = $fn;
            switch($fn){
                case 'menuHorizontal':
                    $plugin = 'menu';
                    break;
                case 'menuIdiomas':
                    $plugin = 'dropdown';
                    break;
                case 'modal':
                    $plugin = 'fancybox';
                    break;
            }                                              
            
            return self::loadXmlPlugin($plugin);	
	}
        
        protected static function loadXmlPlugin($plugin){ 
            $assets         = \LoadConfig::assetsFolderRoot();
            $plugins        = \LoadConfig::folderPlugins();            
            $arrHeaderInc   = array();
            $pathRootPlugin = $plugins.'/'.$plugin.'/';//Root de plugins (ex: plugins/accordeon/)
            $pathXml        = $assets.'/'.$pathRootPlugin.'install.xml';
            $pathXml        = str_replace('//','/',$pathXml);             
                        
            $objXml         = self::loadXml($pathXml);  
          
            if (is_object($objXml)) {
                $descricao      = $objXml->descricao;
                $nodesConfig    = $objXml->config->include;
                $arrExtension   = self::$arrExtension;                
                if (is_object($nodesConfig)) {
                    foreach($arrExtension as $ext) {
                        //Cada item do loop possui uma lista de includes.
                        //Caso exista mais de um include na lista, estes devem estar separados por vírgula.
                        
                        $listInclude = self::valueForAttrib($nodesConfig,'id',$ext);
                        if (strlen($listInclude) == 0) continue;
                                                
                        $arrList        = explode(',',$listInclude);
                        $arrPathList    = array();
                        foreach($arrList as $include) {                            
                            $arrPathList[]  = $pathRootPlugin.$include;
                        }
                        
                        $listInclude = join(',',$arrPathList);
                        $arrHeaderInc[$ext] = $listInclude;           
                    }                    
                }
                return $arrHeaderInc;         
            } else {     
                $msgErr = __CLASS__.'->'.__METHOD__.' Impossível ler o arquivo '.$pathXml;                 
                throw new \Exception( $msgErr );                 
                                                         
            }                
        }                 
    }    
?>
