<?php

namespace sys\classes\performance;
use \sys\classes\performance\CssMinify;
use \sys\classes\performance\JsMinify;
use \sys\classes\performance\JSMin;
class Compress {
    
    private $script         = '';
    private $extension      = '';
    private $cacheFileName  = '';
    
    function __construct($arrParams){
        if (is_array($arrParams)) {
            $this->script           = (isset($arrParams['string']))?$arrParams['string']:'';
            $this->extension        = (isset($arrParams['extension']))?strtolower($arrParams['extension']):'';
            $this->cacheFileName    = (isset($arrParams['fileNameMin']))?$arrParams['fileNameMin']:'';
        }
    }
    
    /**
     * Faz a compactação do string (css ou js) e grava em um arquivo externo.
     *  
     * @return int Total de bytes do arquivo gerado.
     */
    function minify(){
        $extension      = $this->extension;
        $script         = $this->script;
        $strCompressed  = '';
        $bytes          = 0;
        $output         = '';
        
        if (strlen($script) > 0) {           
            $strCompressed  = $script;
            
            if ($extension == 'css') {
                $strCompressed = CssMinify::minify($script);
            } elseif ($extension == 'js') {
                //$strCompressed = JsMinify::minify($script);
                try {
                    //$strCompressed = JsMin::minify($script);
                } catch(\Exception $e) {
                    die($e->getMessage());
                }
            }
                        
            $bytes = $this->writeCache($strCompressed);

        } else {
            //echo $output.': Nenhum script foi informado para a compressão atual.<br>';
        }
        return  $bytes;
    }
    
    /**
     * Grava o script comprimido em um arquivo externo com o nome informado no construtor.
     * 
     * @param string $strCompressed String comprimida.
     * @return int Número de bytes do novo arquivo.
     */
    private function writeCache($strCompressed){
        $bytes          = 0;
        $cacheFilename  =  \Application::setRootProject($this->cacheFileName);
        $arrPath        = pathinfo($cacheFilename);
        $dirname        = @$arrPath['dirname'];
       
        //Verifica se o diretório já existe. Se ainda não existe deve ser criado.
        if (strlen($dirname) > 0 && !is_dir($dirname)) {
            $arrDirName         = explode('/',$dirname);
            array_pop($arrDirName);//Retira o último elemento do array
            $rootDirName        = implode('/',$arrDirName);
            chmod($rootDirName, 777);
            
            $dirCreated = mkdir($dirname);
            if (!$dirCreated) {
                die('Impossível criar o diretório '.$dirname.'. Verifica se há permissão na pasta atual.');
            }
        }

        if (strlen($cacheFilename) > 0) {
            $f = fopen($cacheFilename, "w");        
            flock($f, LOCK_EX);//Trava o arquivo para gravar o conteúdo
            $bytes = fwrite($f,$strCompressed);
            fclose($f);
            if ($bytes === FALSE) $bytes = 0;
        }
        return $bytes;
    }    
}

?>
