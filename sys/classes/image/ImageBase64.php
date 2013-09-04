<?php

namespace sys\classes\image;

class ImageBase64 extends Image {

    private $codImgBase64; //String com codificação BASE64 da imagem atual.    
    private $extension; //gif,jpg,png...
    
    /**
     * Recebe uma URL de um arquivo de imagem (ex: /assets/images/app/image.png) ou uma string 
     * referente à codificação de BASE64 de uma image.
     * 
     * EXEMPLO 1:
     * Mostra a criação de uma tag IMG a partir de um código BASE64 de uma imagem gif 
     * com largura = 400px e altura = 300px.
     * <code>
     *  //String contendo o código BASE64 de uma imagem .
     *  $string = 'R0lGODlhnQI9AfMPAAAAABERESIiIjMzM0RERFVVVWZmZnd3d4iIiJmZmaqqqru7u8zMz...';
     *  $width  = 400;
     *  $height = 300;
     *  $objImg = new ImageBase64($string,'gif',$width,$height);
     *  $tag    = $objImg->alt('Texto descritivo da imagem')->render();
     *  
     *  echo $tag;   
     *   
     * </code>
     * 
     * EXEMPLO 2:
     * Mostra a criação de uma tag IMG convertendo para BASE64 um arquivo de imagem.
     * <code>
     *  $objImg     = new ImageBase64('fonts/logo.png');
            $tag    = $objImg->alt('Logo da empresa')->render();
            echo $tag;
     * </code>
     * 
     * @param type $filenameOrCodbase64
     */
    function __construct($filenameOrCodbase64='',$extension='',$width=0,$height=0){ 
        if (strlen($filenameOrCodbase64) > 0) {
            if (self::isBase64($filenameOrCodbase64)) {
                //Uma string codificada com BASE 64 foi informada.
                $this->setWidthHeight($width,$height);
                $this->extension = $extension;
                
                $this->setSource($filenameOrCodbase64);
                
            } else {
                //Uma URL foi informada.                
                parent::__construct($filenameOrCodbase64,$width,$height);
                if (strlen($filenameOrCodbase64) > 0) $this->convertFileToImgBase64();//Converte o arquivo de imagem para BASE64.   
            }
        } else {
            //Nenhum arquivo foi informado. Cria uma imagem padrão para definição de layout.
            parent::__construct($filenameOrCodbase64,$width,$height);
        }
    }
    
    private static function isBase64($string){
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string);
    }      

    /**
     * Recebe um código de imagem no formato BASE64 e converte em arquivo.
     * IMPORTANTE: a pasta destino deve ter permissão de escrita.
     * 
     * EXEMPLO:
     * <code>
     * //String contendo o código BASE64 de uma imagem .
     * $string = 'R0lGODlhnQI9AfMPAAAAABERESIiIjMzM0RERFVVVWZmZnd3d4iIiJmZmaqqqru7u8zMz...';
     * 
     * if (ImageBase64::convertImgBase64ToFile($string,'/test/view_1.gif')) {
     *      echo 'Arquivo criado com sucesso';
     * } else {
     *      echo 'Erro ao criar arquivo.';
     * }
     * </code>
     * @param type $base64
     * @param type $outputfile
     * @return boolean
     */
    public static function convertImgBase64ToFile($base64,$outputfile){  
        $physicalFile   = \Url::physicalPath($outputfile);
        $ifp            = fopen($physicalFile,"wb"); //Se o arquivo já existir deve ser substituído.
        if ($ifp !== false){
            $base64 = chunk_split(preg_replace('!\015\012|\015|\012!','',$base64)); 
            fwrite($ifp, base64_decode($base64)); 
            fclose($ifp); 
            $out = (file_exists($physicalFile))?true:false;
            return $out; 				
        }
           
        return false;
    }
    
    
    /**
     * Converte uma imagem em arquivo físico para BASE64.
     * 
     * @return string Código BASE64 gerado.
     */
    private function convertFileToImgBase64(){
        $physicalSource         = $this->physicalSource;
        $pathParts              = pathinfo($physicalSource);       
        $extension              = $pathParts['extension'];//gif, jpg, png...
        $handle                 = fopen($physicalSource, "r");				
        $imgbinary              = fread(fopen($physicalSource, "r"), filesize($physicalSource));
        $codImgBase64           = base64_encode($imgbinary);
        $this->extension        = $extension;
        
        $this->setSource($codImgBase64);
        
        return $codImgBase64;
    }
    
    
    /**
     * Armazena a codificação BASE64 da imagem e cria a string do atributo 'src' da tag IMG.
     * 
     * @param string $codImgBase64
     * @param integer $widgh
     * @param integer $height
     * @return void
     */
    function setSource($codImgBase64){      
        $this->codImgBase64 = $codImgBase64;    
        $this->source       = "data:image/{$this->extension};base64,".$codImgBase64;               
    }      
}

?>
