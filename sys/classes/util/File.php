<?php

    namespace sys\classes\util;
    
    class File {
        
        /**
         * Verifica se o arquivo informado existe.
         * 
         * @param string $urlFile Path relativo do arquivo (não deve ser usado caminho absoluto com http://...)
         * @param boolean $exception Se o arquivo não existir: exception = TRUE dispara uma exceção, caso contrário retorna FALSE;
         * @return boolean
         * @throws \Exception Caso o arquivo não exista e o parâmetro $exception = TRUE.
         */
        public static function exists($urlFile,$exception=TRUE){
            if (!file_exists($urlFile)) {
                if ($exception){
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'FILE_NOT_EXISTS'); 
                    $msgErr = str_replace('{FILE}',$urlFile,$msgErr);
                    throw new \Exception( $msgErr );  
                }
                return FALSE;
            }
            return TRUE;
        }        
        
        public static function appendOrCreate($pathFile,$content){    
            $save = FALSE;
            if (strlen($pathFile) > 0) {
                $folderLogExists    = FALSE;
                $pathParts          = pathinfo($pathFile);
                $dirName            = $pathParts['dirname'];
                $folderLog          = \Url::physicalPath($dirName);
                
                if (!is_dir($folderLog)) {
                    //Diretório informado ainda não existe. Tenta criá-lo.
                    $folderLogExists = mkdir($folderLog);                        
                } else {
                    //Pasta de logs já existe
                    $folderLogExists = TRUE;
                }           
                
                if ($folderLogExists) {
                    //Diretório informado existe ou foi criado com sucesso.
                    $fh = fopen($pathFile, 'a');
                    if ($fh !== FALSE) {
                        $content = (string)$content;
                        fwrite($fh, $content);                    
                        fclose($fh);  
                        $save = TRUE;
                    }
                }
            }
            return $save;
        }
    }
?>
