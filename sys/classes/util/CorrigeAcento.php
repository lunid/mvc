<?php
    namespace sys\classes\util;

    class CorrigeAcento{
            
            private $latin1_to_utf8;
            private $utf8_to_latin1;          
            
            function __construct(){
                for($i=32; $i<=255; $i++) {
                    $this->latin1_to_utf8[chr($i)] = utf8_encode(chr($i));
                    $this->utf8_to_latin1[utf8_encode(chr($i))] = chr($i);
                }	                
            }
            
            /**
             * Método incluído em 03/07/2013 para correção de acentuação
             * de string lida do banco e mostrada no navegador.
             * 
             * @param string $str
             * @return string
             */
            function parse($str){
                $str     = $this->mixed_to_latin1($str);                    
                $str     = htmlentities($str, ENT_NOQUOTES, "ISO-8859-1");//Converte para caracteres especiais HTML                    
                $str     = html_entity_decode($str,ENT_COMPAT,"UTF-8"); 
                return $str;
            }            

            public static function getEncode($str){
                return mb_detect_encoding($str);
            }
            
            function mixed_to_latin1($text) {
                foreach( $this->utf8_to_latin1 as $key => $val ) {
                    $text = str_replace($key, $val, $text);
                }
                return $text;
            }

            function mixed_to_utf8($text) {
                return utf8_encode($this->mixed_to_latin1($text));
            }
            
            public function corrigeAcentosArray(&$array, $campos){
                $arrCampos = explode(",", $campos);
                
                if(is_array($arrCampos)){
                    foreach($arrCampos as $campo){
                        for($i=0; $i<count($array); $i++){
                            if(is_array($array[$i])){
                                $array[$i][$campo] = $this->mixed_to_utf8($array[$i][$campo]);
                            }else if(is_object($array[$i])){
                                $array[$i]->$campo = $this->mixed_to_utf8($array[$i]->$campo);
                            }
                        }
                    }
                }
            }
	}
?>