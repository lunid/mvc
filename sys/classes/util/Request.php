<?php

    namespace sys\classes\util;
    
    class Request{

            /**
                * Classe usada para receber/lêr variáveis via GET, POST, SESSION,
                * ou então, pode ser instanciada como objeto recebendo uma string 
                * de variáveis (Ex: var1=value1&var2=value2&...), que são convertidas
                * em variáveis a partir do método mágico __set().
                * 
                * Exemplo:
                * $objReq = new Request("nome=João&idade=28");
                * echo $objReq->nome;               
            */

            var $strVars; //Guarda uma string de variáveis no formato var1=value1&var2=value2&... 

            function __construct($strVars){			
                    $this->strVars = $strVars;//String de variáveis. Ex:var1=value1&var2=value2&... 	
            }

            function __get($var){
                    $strVars = $this->strVars;
                    parse_str($strVars);//Converte a string var1=value1&var2=value2&... em variáveis
                    $value = (isset($$var))?$$var:'';
                    return $value;			
            }

            /**
                * Método que recebe o valor de uma varíavel e seu respectivo tipo
                * @param string|number|array|object $varValue Valor da variável a ser tratada
                * @param string $defaultValue Valor retornado caso a variável tenha um tipo diferente do esperado
                * @param string $type Pode ser STRING|NUMBER|OBJECT|ARRAY|SERIAL
                * @return string|number|object 
                */

            public static function getVar($varValue,$type='STRING',$defaultValue=''){			

                    $value  = (isset($varValue))?$varValue:$defaultValue;                

                    switch(strtoupper($type)){
                        case 'ARRAY':
                            //Verifica se $value é um array
                            if (!is_array($value)) $value = array();
                            break;
                        case 'STRING':
                            //Verifica se $value é uma string
                            $value = trim((string)$value);                                                        
                            break;
                        case 'NUMBER':
                            //Verifica se $value é numérica (pode ser float ou integer)
                            $value = trim((int)$value);                                
                            break;
                        case 'OBJECT':
                            if (!is_object($value)) $value = $valorDefault;
                            break;
                        case 'SERIAL':
                            //Objeto serializado. Recupera (unserialize) o objeto
                            $value = unserialize($value);                                
                            break;
                    }

                    return $value;
            }

            public static function all($varName,$type,$method='REQUEST',$defaultValue=''){
                    $varValue  = '';
                    switch(strtoupper($method)){
                        case 'GET':
                            if (isset($_GET[$varName])) $varValue = $_GET[$varName];
                            break;
                        case 'POST':
                            if (isset($_POST[$varName])) $varValue = $_POST[$varName];
                            break;
                        case 'SESSION':
                            if (isset($_SESSION[$varName])) $varValue = $_SESSION[$varName];
                            break;
                        default:
                            //method REQUEST
                            if (isset($_REQUEST[$varName])) $varValue = $_REQUEST[$varName];
                    }

                    return self::getVar($varValue,$type,$defaultValue);
            }

            public static function get($varName,$type='STRING'){
                    return self::all($varName,$type,'GET');
            }

            public static function post($varName,$type='STRING'){
                    return self::all($varName,$type,'POST');
            }

            public static function session($varName,$type='STRING'){
                    return self::all($varName,$type,'SESSION');
            }
    }
?>