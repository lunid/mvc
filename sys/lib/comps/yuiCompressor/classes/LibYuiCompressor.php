<?php
/**
 * COMPONENTE YuiCompressor:
 * Faz a compressão de uma string javascript ou css para um arquivo externo.
 * Utiliza JAVA (arquivo.jar) para fazer a compressão.
 *
 */
use \sys\lib\classes\LibComponent;
use \sys\lib\classes\Url;

class YuiCompressor extends LibComponent {
    
    function minify(){
        $ext            = $this->extension;
        $strInc         = $this->string; 
        $cacheFilename  = $this->fileNameMin; 
        
                               

        $ext            = strtolower($ext);//Extensão do arquivo (css ou cs)
        $strInc         = (string)$strInc;//String a ser compactada.
        $strCompressed  = '';
                
        
        $this->setReturn(FALSE);           
            
        if (strlen($strInc) == 0) return;                       
        
        if ($ext == 'css' || $ext == 'js') {
            if ($ext == 'css') {     
                $rootComps = Url::pathRootComps('yuiCompressor'); 
                require_once($rootComps.'classes/LibCompress.php');  

                $strCompressed  = \LibCompress::compressCss($strInc);

                if (strlen($strCompressed) == 0) {
                    $strCompressed = $strInc;
                }                                            
            } else {
                $strCompressed = $strInc;
            }
        
            if (strlen($strCompressed) > 0 && strlen($cacheFilename) > 0){            
                //Gera um arquivo físico com o conteúdo compactado:                       
                $dirName    = dirname($cacheFilename);
                $dirName    = \Url::relativeUrl($dirName);    

                if (!is_dir($dirName)) mkdir($dirName,'0777');

                \LibCompress::writeCache($cacheFilename, $strCompressed);
                if (file_exists($cacheFilename)) {
                    php_strip_whitespace($cacheFilename);
                    $this->setReturn(TRUE); 
                }
            }
        } else {
            //Extensão inválida.
            die('Extensão do arquivo inválida (permitido apenas css ou js)');
        }
    }
  
    /**
     * Faz a compactação de uma string gravando o resultado em um arquivo externo.
     * Os formatos permitidos são js, para conteúdo javascript, e css para conteúdo de folhas de estilo em cascata.
     *      
     * @return void
     * 
     * @throws \Exception Se uma extensão válida não for informada (valores permitidos: css, js).
     * @throws \Exception Se após a compactação de uma string válida de javascript o resultado for vazio.
     * @throws \Exception Se a tentativa de criar o arquivo de saída falhar.
     * @throws \Exception Se após a sua criação, o arquivo de saída possuir tamanho 0kb.
     */
    function init(){	
            //return $this->minify();
            
            $ext            = $this->extension;
            $strInc         = $this->string; 
            $outFileMin     = $this->fileNameMin;
            $rootComps      = Url::pathRootComps('yuiCompressor');                        
            
            $root           = $rootComps.'/src/yuicompressor-2_4_8/';                                    
            $pathJar        = $root.'build/yuicompressor-2_4_8pre.jar'; 
            
            $ext            = strtolower($ext);//Extensão do arquivo (css ou cs)
            $strInc         = (string)$strInc;//String a ser compactada.      
            
            require_once($rootComps.'classes/YUICompressor.php');
            
            $this->setReturn(FALSE);           
            
            if (strlen($strInc) == 0) return;                       
            
            if (($ext == 'js' || $ext == 'css')) {            
                //Comprime a string:
                $strIncMin      = '';
                $pathTmp        = \Url::physicalPath('/data/tmp/yui/');
                \Minify_YUICompressor::$jarFile  = realpath($pathJar);
                \Minify_YUICompressor::$tempDir  = $pathTmp; 
                
                if (!is_dir($pathTmp)) mkdir($pathTmp);
                
                try {
                    if ($ext == 'js'){
                        //Javascript                        
                        $strIncMin = \Minify_YUICompressor::minifyJs($strInc,array('nomunge' => true, 'line-break' => 1000));                   
                    } else {
                        //css
                        $strIncMin = \Minify_YUICompressor::minifyCss($strInc,array('nomunge' => true, 'line-break' => 2000));                   
                    }                                         
                } catch(\Exception $e){
                    die($e->getMessage()); 
                }                
                
                if (strlen($strIncMin) == 0) {
                    $strIncMin = $strInc;
                }
                                
                if (strlen($strIncMin) > 0 && strlen($outFileMin) > 0){
                    //Gera um arquivo físico com o conteúdo compactado:                       
                    $dirName    = dirname($outFileMin);
                    $dirName    = \Url::relativeUrl($dirName);
                    
                    
                    
                    if (!is_dir($dirName)) mkdir($dirName,'0777');

                    $fp = @fopen($outFileMin, "wb+");   
                    if ($fp !== FALSE) {
                        fwrite($fp, $strIncMin);
                        fclose($fp);
                    } else {
                        //Não foi possível gerar o arquivo compactado.  
                        echo 'Erro ao gerar o arquivo compactado. '.$outFileMin;
                        die();
                        $arrVars = array('FILE'=>$outFileMin);
                        $this->Exception(__METHOD__,'ERR_FILE_INC',$arrVars);                        
                    }
                              
                    if (!file_exists($outFileMin)){
                        $arrVars = array('FILE'=>$outFileMin);
                        $this->Exception(__METHOD__,'ERR_FILE_INC',$arrVars);                        
                    }

                    $size = filesize($outFileMin);
                    if ($size == 0){
                        $arrVars = array('FILE'=>$outFileMin,'STR_MIN'=>$strIncMin);
                        $this->Exception(__METHOD__,'ERR_FILE_SIZE_ZERO',$arrVars);                                                                       
                    }                                          
                } elseif (strlen($strIncMin) == 0 && $ext == 'js') {
                    //O conteúdo compactado está vazio e o trata-se de um conteúdo de javascript.
                    $arrVars = array('FILE'=>$outFileMin);
                    //$this->Exception(__METHOD__,'ERR_JS_COMPRESS',$arrVars);                                
                }                
            } else {   
                $arrVars = array('EXT'=>$ext);
                $this->Exception(__METHOD__,'ERR_EXT',$arrVars);     
            }
            
            $this->setReturn(TRUE);   
        }
}

?>
