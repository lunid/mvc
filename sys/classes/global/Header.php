<?php

    /**
     * Classe auxiliar da classe View.
     * Possui métodos para fazer a junção e compactação de arquivos javascript e css.
     *
     */

    class Header {       
        private $extension     = '';
        private $filenameDest  = '';
        private $arrInclude    = NULL;
        private $pathAssets    = '';
        private $objIncludeParams; //Objeto stdClass com parâmetros armazenados após validação dos includes.
        
        function __construct($extension, $list, $filenameDest){
            $pathAssets = 'assets/';
            if ($extension == 'js') {
                $pathAssets .= 'scripts/';
            } elseif ($extension == 'css') {
                $pathAssets .= 'css/';
            }
            $this->extension    = $extension;
            $this->pathAssets   = $pathAssets;//ex.: assets/scripts/min/
            $this->filenameDest = $filenameDest;

            $arrInclude = $list;  
            if (!is_array($list)) $arrInclude = explode(',',$list);           
            $this->arrInclude = $arrInclude;
        }    

        function getFilenameDest(){
            $filenameDest = $this->filenameDest;
            $filenameDest = str_replace('.'.$this->extension,'',$filenameDest);
            return $filenameDest;            
        }

        function loadIncludeParams(){
            $arrPathIncludes    = NULL;
            $objIncludeParams   = NULL;

            //Um objeto foi informado.
            $extension      = $this->extension;
            $filenameDest   = $this->getFilenameDest();
            $arrInclude     = $this->arrInclude;                                

            if (is_array($arrInclude)) {
                //Há um array com um ou mais includes
                foreach($arrInclude as $filename){
                    $path = $this->pathAssets.$filename.'.'.$extension;
                    if (file_exists($path)) {
                        $arrPathIncludes[] = $path;
                    } else {
                        throw new \Exception("O arquivo '".$path."' não foi localizado.");
                    }                
                }

                if (is_array($arrPathIncludes)) {
                    $pathFolderMin                     = $this->pathAssets.'min/';
                    if (!is_dir($pathFolderMin)) mkdir($pathFolderMin);
                    
                    $objIncludeParams                  = new \stdClass();
                    $objIncludeParams->arrPathIncludes = $arrPathIncludes;
                    $objIncludeParams->pathFileDest    = $pathFolderMin.$filenameDest.'_min_'.$extension;
                }
            }
            $this->objIncludeParams = $objIncludeParams;            
        }
        
        /**
         * Faz a junção e compactação dos arquivos para o tipo atual (css ou js), gerando 
         * o arquivo php a ser usado como URL da tag de inclusão.
         * 
         * @return string Tag (<script> ou <link>)
         */
        function getTagFromFileMerge(){
            $objIncludeParams = $this->objIncludeParams;
            $scriptTag        = '';
            
            if (is_object($objIncludeParams)) {
                $arrPathIncludes    = $objIncludeParams->arrPathIncludes;
                $pathFileDest       = $objIncludeParams->pathFileDest;//Ex.: assets/scripts/min/index_min_js
   
                $vars = array( 
                    'encode' => true, 
                    'timer' => true, 
                    'gzip' => true, 
                    'closure' => true,
                    'echo' => false
                );
               
                $minified       = new Minifier( $vars );  
                
                if ($this->extension == 'js') {
                    $scriptTag = "<script src=\"".$minified->merge( $pathFileDest, $this->pathAssets, $arrPathIncludes )."\"></script>";               
                } elseif ($this->extension == 'css') {                    
                    $scriptTag = "<link rel='stylesheet' media='all' href='".$minified->merge( $pathFileDest, $this->pathAssets, $arrPathIncludes )."'>";               
                }
            }            
            return $scriptTag;
        }
    }
?>
