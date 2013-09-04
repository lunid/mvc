<?php
    namespace sys\classes\security;
    use \sys\classes\util\Dic;
    class Password {
        
        /**
         * Gera uma senha aleatória alfaanumérica (letras e números).
         * Como padrão, caso o parâmetro $mask não seja alterado, a senha gerada possui 8 caracteres.
         * 
         * A string informada no parâmetro $mask pode conter dois marcadores:
         * L = marca a posição de uma letra.
         * N = marca a posição de um número.
         * 
         * Exemplo:
         * <code>
         *      //Gera e imprime uma senha com 8 caracteres.
         *      echo Password::newPassword(); 
         * 
         *      //Gera e imprime uma senha aleatória, sendo que as posições marcadas com L serão letras
         *      //e as posições marcadas com N serão números. A senha resultante terá 12 caracteres,
         *      //que é a quantidade de caracteres informada no parâmetro.
         *      echo Password::newPassword('LLLLNNNNLNLN');
         * </code>
         * 
         * @param string $mask Máscara que define a posição de letras (L) e números (N).
         * @return string
         */
        public static function newPassword($mask="LLNNLLNN"){
            $size           = strlen($mask);
            $patternChar    = "ABCDEFGHIJKLMNOPQRSTUVXWYZ";
            $patternNum     = "0123456789";            
            $passwd         = '';
            
            if ($size > 0) {
                $mask = strtoupper($mask);
                preg_match_all('[L|N]',$mask,$matches);                
                if (count($matches[0]) != $size) {
                    //Há um ou mais caracteres não aceitos na máscara informada.
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_MASK');                
                    throw new \Exception( $msgErr );                                          
                } else {                   
                    $arrChar    = str_split($patternChar); 
                    $arrNum     = str_split($patternNum);                                    
                    for($i=0;$i<$size;$i++){
                        $char       = $mask[$i];
                        $arrPos     = ($char == 'L')?$arrChar:$arrNum;    
                        $passwd     .= $arrPos[array_rand($arrPos,1)];                                    
                    }
                }
            } else {
                //A máscara informada está vazia.
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'MASK_IS_EMPTY');                
                throw new \Exception( $msgErr );                 
            }
            return $passwd;
        }
    }
?>
