<?php
    
    namespace sys\classes\security;
    
    class Hash {
        
        /**
         * Gera um código alfanumérico para ser usado como HASH.
         * 
         * @param integer $id Pode ser o ID autonumber de um registro no DB.
         * @param integer $length O tamanho da string 
         * @return string
         */
        public static function geraHash($id=0, $length=32){            
            $length     = (int)$length;
            if ($length == 0) $length = 32;
            
            $salt	= date('dYmHsi');
            $dymh	= date('dYmH');
            $i		= date('i');//Minutos
            $seg	= date('s');		
            $salt	= $dymh.$seg.$i;
   
            $id 	= ((int)$id == 0)?uniqid(hash("sha512",rand()), TRUE):$id;
            $code 	= hash("sha512", $id.$salt);
            $hash       = substr($code, 0, $length);
            return $hash;
        }	
    }
?>
