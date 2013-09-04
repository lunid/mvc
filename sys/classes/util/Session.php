<?php
    /**
     * Controla o nome das váriáveis de sessão do sistema
     *
     * @author Marcelo Pacheco
     */
    //namespace sys\classes\util;
    
    class Session {
        private static $testeCarregado = "TESTES_CARREGADOS";
        
        public static function __callStatic($name, $value){
            //Retira prefixo para verificar ação.
            $prefixo = substr($name, 0, 3);
            
            switch ($prefixo){
                case "set":
                    $name = substr($name, 3);
                    $name = lcfirst($name);
                    echo $name;
                    echo self::$$name;
                    die();
                    $_SESSION[self::$$name] = $value[0];
                    return TRUE;
                case "get":
                    $name = substr($name, 3);
                    $name = lcfirst($name);
                    return isset($_SESSION[self::$$name]) ? $_SESSION[self::$$name] : FALSE;
                case "unset":
                    $name = substr($name, 3);
                    $name = lcfirst($name);
                    return isset($_SESSION[self::$$name]) ? $_SESSION[self::$$name] : FALSE;
                default:
                    $prefixo = substr($name, 0, 5);
                    
                    if($prefixo == "unset"){
                        $name = substr($name, 5);
                        $name = lcfirst($name);
                        if(isset($_SESSION[self::$$name])){
                            unset($_SESSION[self::$$name]);
                        }
                        return TRUE;
                    }
                    return FALSE;
            }
        }
    }

?>
