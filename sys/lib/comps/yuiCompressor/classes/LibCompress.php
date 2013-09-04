<?php
    // This defines the header type
    class LibCompress {
            
        // Function which actually compress
        // The CSS file
        public static function compressCss($buffer) {
            /* remove comments */
            $buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer) ;
            /* remove tabs, spaces, newlines, etc. */
            $arr = array("\r\n", "\r", "\n", "\t", "  ", "    ", "    ") ;
            $buffer = str_replace($arr, "", $buffer) ;
            return $buffer;
        }

        /**
         * Builds CSS cache file
         * @param array $cssFiles 
         * @return string
         */
        public static function buildCache(array $cssFiles){
            $cssString = "";
            foreach($cssFiles as $cssFile){
                $cssString .= file_get_contents($cssFile);
            }
            $cssString = self::compressCss($cssString);
            return $cssString;
        }

        /**
         * Writes the cache into file
         * @param string $cacheFilename
         * @param string $cssCompressed 
         */
        public static function writeCache($cacheFilename, $cssCompressed){
            $f = fopen($cacheFilename, "w");
            // lock file to prevent problems under high load
            flock($f, LOCK_EX);
            fwrite($f,$cssCompressed);
            fclose($f);
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
