<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

    /**
     * Classe usada para retornar caminhos físicos, URLs que, 
     * de acordo com o ambiente atual (desenvolvimento, produção etc), retorna o domínio
     * com protocolo (http ou https) ou sem protocolo.
     * 
     */
    class Url {
        
        public static function physicalBase($file=''){
            $file       = ltrim($file,'/');
            $rootFolder = self::rootFolder();
            $path       = rtrim($_SERVER['DOCUMENT_ROOT'],'/') .'/'. $rootFolder;            
            if (strlen($file) > 0) $path .= $file;
            return $path;
        }
                
        public static function rootFolder(){
            $rootFolder = trim(ROOT_FOLDER,'/');
            if (strlen($rootFolder) > 0) $rootFolder .= '/';            
            return $rootFolder;
        }
        
        public static function siteHttp($uri = ''){
            return self::site($uri,'http');
        }   
        
        public static function siteHttps($uri = ''){
            return self::site($uri,'https');
        }
        
        /**
         * @todo Finalizar implementação e testar.
         * @param type $uri
         * @param type $protocol
         * @return type
         */
        public static function site($uri = '', $protocol = ''){
            //Retira host, porta, usuário
            $path = preg_replace('~^[-a-z0-9+.]++://[^/]++/?~', '', trim($uri, '/'));

            if (!UTF8::is_ascii($path)){
                //Codifica todos os caracteres não ASCII, como por exemplo RFC 1738
                $path = preg_replace('~([^/]+)~e', 'rawurlencode("$1")', $path);
            }            
            
            return URL::base().$path;            
        }
    }
?>
