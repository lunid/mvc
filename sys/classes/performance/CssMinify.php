<?php
    
    namespace sys\classes\performance;
    
    class CssMinify implements IMinify {
            
        // Function which actually compress
        // The CSS file
        public static function minify($script) {
            /* remove comments */
            $buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $script) ;
            /* remove tabs, spaces, newlines, etc. */
            $arr = array("\r\n", "\r", "\n", "\t", "  ", "    ", "    ") ;
            $buffer = str_replace($arr, "", $buffer) ;
            return $buffer;
        }
        
        /**
         * Compare the modification time of cache file against the CSS files
         * @param type $cacheFilename
         * @param array $cssFiles
         * @return bool
         */
        public static function checkCacheIsOk($cacheFilename, array $cssFiles){
            if(file_exists($cacheFilename)){
                $lastCssModificatedAt = 0;
                foreach($cssFiles as $cssFile){
                    $cssModificatedAt = filemtime($cssFile);
                    if($cssModificatedAt > $lastCssModificatedAt){
                        $lastCssModificatedAt = $cssModificatedAt;
                    }
                }

                if(filemtime($cacheFilename) >= $lastCssModificatedAt){
                    return true;
                }
            }
            return false;
        }  
    }
?>
