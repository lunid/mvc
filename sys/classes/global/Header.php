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

        function __construct($extension, $list, $filenameDest){
            $pathAssets = 'assets/';
            if ($extension == 'js') {
                $pathAssets .= 'scripts';
            } elseif ($extension == 'css') {
                $pathAssets .= 'css';
            }
            $this->extension    = $extension;
            $this->pathAssets   = $pathAssets;
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

        private function getHeaderParams(){
            $arrPathIncludes    = NULL;
            $objHeader          = NULL;

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
                    $objHeader                  = new \stdClass();
                    $objHeader->arrPathIncludes = $arrPathIncludes;
                    $objHeader->pathDest        = $this->pathAssets.'min/'.$filenameDest.'_min_'.$extension;
                }
            }

            return $objHeader;
        }
    }
?>
