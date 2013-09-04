<?php

        namespace sys\lib\classes;
        
        class Url {
            
            public static function pathRootComps($folder=''){
                $folderSys      = \LoadConfig::folderSys();  
                $folderLib      = \LoadConfig::folderLib();
                $folderComps    = \LoadConfig::folderComps();                     
                $folderComp     = (strlen($folder) > 0)?$folder.'/':'';

                $rootComp       = "{$folderSys}/{$folderLib}/{$folderComps}/{$folderComp}";
                
                return $rootComp;
            }
            
            public static function __callStatic($func,$args){                
                $path = '';                                          
                if ($func == 'exceptionClassXml'){
                    /*
                     * Exception Xml das classes contidas em lib/classes:
                     * 
                     * Foi solicitado o path do XML de exception específico da 
                     * classe informada na chamada do método ($args[0]).
                     * 
                     * Por exemplo: 
                     * Recebe lib\classes\LibComponent 
                     * e retorna lib/dic/eLibComponent.xml.
                     */
                    
                    $class  = (isset($args[0]))?$args[0]:'exception';
                    $path   = str_replace('\\','/',$class);
                    $path   = str_replace('classes/','/dic/e',$path);
                    $path   .= '.xml';
                } else {
                    /*
                     * Exception Xml do componente atual ou arquivo install.xml:
                     * 
                     * Se $func = 'exceptionXml' retorna o path do XML específico do 
                     * componente atual. Caso contrário retorna o path de install.xml
                     * do componente.
                     * 
                     * A pasta referente ao componente foi informada em $args[0].
                     * 
                     * Exemplo que retorna exception.xml: 
                     * Recebe folderComponent
                     * e retorna sys/lib/comps/folderComponent/dic/exception.xml
                     * 
                     * Exemplo que retorna install.xml: 
                     * Recebe folderComponent
                     * e retorna sys/lib/comps/folderComponent/install.xml           
                     */                    
                    $folder     = (isset($args[0]))?$args[0]:'';                    
                    $rootComp   = self::pathRootComps($args[0]);                    
                    
                    if ($func == 'exceptionXml') {
                        $path  = $rootComp.'dic/exception.xml';
                    } elseif ($func == 'installXml') {
                        $path     = $rootComp.'/install.xml';                                        
                    }                     
                }
                return $path;
            }
        }
        
?>
