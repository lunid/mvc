<?php

    namespace sys\classes\image;
    
    class Image {
            
        protected $source; //Atributo src.
        protected $physicalSource;//Caminho físico da imagem.
        private $alt;//Atributo alt.
        private $usemap;
        protected $width                = 0;
        protected $height               = 0;        
        protected $filename             = '';
        protected $widthHeight          = '';
        
        /**
         * Inicializa o objeto definindo o nome da imagem caso o parâmetro $filename seja informado.
         * 
         * Exemplos de uso.
         * EXEMPLO 1: Utilização para incluir espaço de imagem para layout.
         * <code>
         *  //Imagem sem tamanho definido pelo usuário (utiliza o tamanho padrão e 200px X 200px).
         *      $objImg = new image\Image();
         *      $img    = $objImg->alt('texto alternativo')->render();
         *      echo $img;
         * 
         *  //Imagem com tamanho definido.
         *      $objImg = new image\Image('',100,50);
         *      $img    = $objImg->alt('texto alternativo')->render();
         *      echo $img;        
         * 
         *  ou então:
         *      $objImg  = new image\Image();
         *      $img     = $objImg->widthHeight(100,50)->render();
         *      echo $img;                    
         * </code>
         * 
         * EXEMPLO 2: Imagem a partir de um arquivo local:
         * <code>
         *      $objImg  = new image\Image('fonts/logo.png');//Procura a imagem em /assets/images/
         *      $img     = $objImg->alt('texto alternativo')->render();
         *      echo $img;   
         * </code>
         * @param type $filename
         */
        function __construct($filename='',$width=0,$height=0){
            if (strlen($filename) > 0) {
                $this->filename = $filename;            
                $this->findSource($filename);
            } else {
                 $this->setWidthHeight((int)$width,(int)$height);//Localiza largura e altura.
            }
        }                
        
        
        function setSource($filename){            
            return $this->findSource($filename);           
        }
        
        
        /**
         * Define o atributo 'src' da imagem.
         * A partir do nome do arquivo, procura na pasta do módulo atual (assets/images/module/...) e,
         * caso o arquivo não seja localizado procura em common (assets/images/common/...).
         * Se o arquivo não for localizado em nenhum dos caminhos anteriores utiliza o $filename como 
         * $source.
         * 
         * @param string $imgFile Nome do arquivo da imagem (ex.: test.jpg).
         * @return Image
         */
        protected function findSource($filename=''){            
            if (strlen($filename) == 0) $filename = $this->filename;
            
            if (strlen($filename) > 0) {
                $folderAssets   = \LoadConfig::assetsFolderRoot();                
                $module         = \Application::getModule();                
                $urlModule      = "/{$folderAssets}/images/{$module}/{$filename}";
                $urlCommon      = "/{$folderAssets}/images/common/{$filename}";
                $source         = $filename;
                
                if (file_exists(\Url::physicalPath($urlModule))) {
                    $source = $urlModule;
                } elseif (file_exists(\Url::physicalPath($urlCommon))) {
                    $source = $urlCommon;                    
                }

                $this->physicalSource   = \Url::physicalPath($source);
                $this->source           = $source;
                
                $this->setWidthHeight();//Localiza largura e altura.
                
            } else {
                die('Image->findSource(): O nome do arquivo não foi informado.');
            }
            return $this;
        }
        
        /**
         * Define a string para os atributos de largura e/ou altura para a imagem atual.
         * 
         * @param integer $width Opcional
         * @param integer $height Opcional
         * @return string
         */
        protected function setWidthHeight($width=0,$height=0){            
            $strW           = '';
            $strH           = '';
            $widthHeight    = '';                        
            $physicalSource = $this->physicalSource;
            
            if (strlen($physicalSource) > 0 && $width == 0 && $height == 0) {
                list($width, $height) = getimagesize($physicalSource);
            }
            
            if ((int)$width > 0) $strW   = "width='{$width}' ";
            if ((int)$height > 0) $strH  = "height='{$height}' ";
            
            if (strlen($strW) > 0 || strlen($strH) > 0) {
                $widthHeight = $strW.$strH;
            }
            
            $this->width        = (int)$width;
            $this->height       = (int)$height;
            $this->widthHeight  = $widthHeight;
            
            return $widthHeight;
        }
        
        function widthHeight($width,$height){
            $this->setWidthHeight($width, $height);
            return $this;
        }

        function alt($alt=''){
            $this->alt = $alt;
            return $this;
        }

        /**
         * Define um nome de mapeamento para a imagem atual.
         * O nome do mapeamento NÃO deve conter o caractere '#'. 
         * 
         * Exemplo:
         * Mapeamento de uma imagem chamada logo.png.
         * 
         * <code>
         *  $obj    = new Image('fonts/logo.png');
         *  $tag    = $obj->usemap('map1')->render();
         *  echo $tag;
         * </code>
         * 
         * @param string $usemap
         * @return \sys\classes\image\Image
         */
        function usemap($usemap=''){
            if (strlen($usemap) > 0) {
                $usemap = str_replace('#', '', $usemap);//Retira o caractere '#' se informado.
                $this->usemap = '#'.$usemap;
            }            
            return $this;                
        }    
        
        /**
         * Cria a tag <img .../> a partir dos parâmetros fornecidos (largura, altura, alt, usemap) para o objeto atual.
         * 
         * @return string Tag HTML a ser usada para mostrar a imagem atual.
         */
        function render(){            
            $usemap         = $this->usemap;
            $source         = $this->source;         
            $widthHeight    = $this->widthHeight;
            $attribUsemap   = '';
                        
            if (strlen($source) == 0) {
                //Gera uma imagem padrão caso nenhum arquivo tenha sido informado.                
                $whDefault  = 200;
                $width      = ($this->width > 0)?$this->width:$whDefault;
                $height     = ($this->height > 0)?$this->height:$whDefault;
                
                $source = "http://placehold.it/400x350/4D99E0/ffffff.png&text={$width}x{$height}";
            }
            
            if (strlen($usemap) > 0) $attribUsemap = "usemap='{$usemap}'";
            $tag = "<img src='{$source}' alt='{$this->alt}' {$attribUsemap} {$widthHeight} border='0' />";
            return $tag;
        }       
    }
?>