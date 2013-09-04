<?php

/*
 * Classe que trata mensagens de erro da aplicação disparadas com trhow.
 * O arquivo de dicionário é o XML /app/dic/backend_exception.xml.
 * 
 */
    namespace sys\classes\util;
    
    class Dic {
        
        /**
         * Recebe um nome de classe ou método na variável $item, retira o namespace e retorna 
         * o conteúdo de $item sem o namespace.
         * 
         * @param string $item Pode ser o nome de uma classe com namespace (Ex:) ou 
         * o nome de um método com namespace
         * @param string $ns Namespace a ser excluído da variável $item
         * @return string
         */
        public static function getNameItem($item,$ns){
            $out = str_replace($ns,'',$item);
            return $out;            
        }
        
        public static function loadMsgForXml($xmlPath,$func,$codMsg=''){  
            $msgErr = ''; 
            $msg    = '';               
            if (file_exists($xmlPath)){
                list($ns,$action)   = explode('::',$func);
                $strXml             = file_get_contents($xmlPath);
                $objXml             = @simplexml_load_string($strXml);
                $arrNodes           = $objXml->$action;//object se for um único nó <msg..>, array se for maior que um.    
                
                if (is_object($objXml) && (is_object($arrNodes) || is_array($arrNodes))){
                    //Arquivo xml carregado com sucesso.                    
                    foreach($arrNodes as $msgNodes){              
                        if (count($msgNodes->msg) > 1){ 
                            //Existe mais de uma mensagem (<msg...>) para a $class->$func atual.
                            foreach($msgNodes as $msgNode) {
                                $atrib = $msgNode->attributes();
                                if ($atrib == strtoupper($codMsg)) {
                                    $msg = $msgNode;                                   
                                    break;
                                }
                            }
                        } else {
                            //Existe um único nó <msg...> para a $class->$func atual.
                            $atrib  = $msgNodes->msg->attributes();                            
                            $msg    = ($atrib == $codMsg)?$msgNodes->msg:'Erro desconhecido';
                        }                                                
                    } 
                    
                    $msg    = nl2br(htmlentities(utf8_decode($msg)));                    
                    $msg    = "<b>".$func."()</b>:<br/>".$msg;
                } else {
                    $msgErr = "Não foi possível carregar um objeto XML para {$func->$codMsg}";
                }                
            } else {
                $msgErr = "Arquivo {$xmlPath} não localizado";                
            }
            if (strlen($msgErr) > 0) self::setErr($func, $msgErr);
            return $msg;
        }
 
        public static function loadMsg($class,$func,$ns,$codMsg='',$arrReplace = null){
            $msgErr         = ''; 
            $msg            = '';
           
            //Retira o namespace da variável $class e $func
            $class          = self::getNameItem($class,$ns.'\\');
            $func           = ($func == NULL)?'default':self::getNameItem($func,$ns.'\\'.$class.'::');
            $module         = \Application::getModule();
            $fileException  = APPLICATION_PATH.$module.'/dic/e'.$class.'.xml';
                       
            if (!file_exists(\Url::physicalPath($fileException))) $fileException = APPLICATION_PATH.'/sys/dic/e'.$class.'.xml';
            if (!file_exists(\Url::physicalPath($fileException))) $fileException = APPLICATION_PATH.'/sys/dic/exception.xml';
            
            $method = __CLASS__.'\\'.__FUNCTION__."()";//Monta uma string ref. ao método atual. Usado para mostrar erro do método setErr()
            
            $xml = $fileException;

            if (!file_exists(\Url::physicalPath($fileException))) {
                //Verifica na pasta sys        
                $fileException  = str_replace($module.'/','sys/',$fileException);
                $xml            = (!file_exists($fileException))?'sys/dic/exception.xml':$fileException;
            }            
            
            $xml = \Url::physicalPath($xml);
           
            if (file_exists($xml)){     
                $strXml = file_get_contents($xml);
                $objXml = @simplexml_load_string($strXml);
                
                if ($objXml === FALSE) return false;
                
                $arrNodes = $objXml->$class->$func;//object se for um único nó <msg..>, array se for maior que um.

                if (is_object($objXml) && (is_object($arrNodes) || is_array($arrNodes))){
                    //Arquivo xml carregado com sucesso.                    
                    foreach($arrNodes as $msgNodes){              
                        if (count($msgNodes->msg) > 1){ 
                            //Existe mais de uma mensagem (<msg...>) para a $class->$func atual.
                            foreach($msgNodes as $msgNode) {
                                $atrib = $msgNode->attributes();                                
                                if ((string)$atrib == strtoupper($codMsg)) {
                                    $msg = (string)$msgNode;
                                    break 2;
                                }
                            }
                        } else {
                            //Existe um único nó <msg...> para a $class->$func atual.
                            $atrib  = $msgNodes->msg->attributes();                               
                            $msg    = ((string)$atrib == $codMsg)?(string)$msgNodes->msg:'Erro desconhecido';
                        }                                                
                    }    
                    
                    $msg    = $msg;
                    $msg    = '<b>'.$class.'/'.$func."()</b>:<br/>".$msg;
                } else {
                    $msgErr = "Não foi possível carregar um objeto XML para $class->$func->$codMsg.";
                }
            } else {
                $msgErr = "Arquivo $xml não localizado";                
            }
            if (strlen($msgErr) > 0) self::setErr($method, $msgErr);
            return $msg;
        }
        
        /**
         * Mostra um erro de execução originário na classe Dic.
         * 
         * @param string $method Nome do método que fez a chamada
         * @param type $msgErr Mensagem de erro a mostrar na tela
         */
        public static function setErr($method,$msgErr){
            die($method." : $msgErr");
        }
    }
?>
