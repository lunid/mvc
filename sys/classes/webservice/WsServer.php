<?php

namespace sys\classes\webservice;
use \sys\classes\util\Component;
use \sys\classes\mvc\ExceptionController;
use \sys\classes\security\Token;
use \auth\classes\helpers\AuthHelper;

class WsServer extends ExceptionController{
        private $local = "api"; //Local que está utilizando WSDL
        private $wsInterfaceClass; //Classe a ser consumida no webservice.
        private $arrIgnoredMethods; //Métodos de $class que NÃO devem ser consumidos no webservice.
        
        protected function setWsInterfaceClass($wsInterfaceClass){
            $this->wsInterfaceClass = $wsInterfaceClass;
        }
        
        protected function setLocal($local){
            $this->local = $local;
        }
        
        protected function setArrIgnoredMethods($arrIgnoredMethods){
            $this->arrIgnoredMethods = $arrIgnoredMethods;
        }
        
        /**
         * Inicia o servico SOAP.
         * 
         * @throws Exception
         */
        public function actionIndex(){
            try{
                //Inicia o SoapServer
                $this->getSoap()->index();
            }catch(Exception $e){
                throw $e;
            }
        }      
        
        /**
         * Gera o WSDL do Serviço.
         * 
         * @throws Exception Caso uma falha ocorra ao tentar gerar o WSDL.
         */
        public function actionWsdl(){
            try{
                //Inicia WSDL                   
                $wsInterfaceClass   = $this->wsInterfaceClass;
                $objSoap            = $this->getSoap();
                
                //Inicia WSDL da Classe
                $objSoap->wsdlGenerate($wsInterfaceClass);
                
                //Trata caminhos de inclusão                
                $pathController = preg_replace("/(sys)(.*)/", "", __DIR__) . $this->local . "\classes\controllers\\" . $wsInterfaceClass . "Controller.php";
                $pathWsServer   = __DIR__ . "\WsServer.php";

                if(file_exists($pathController)){
                    //Adiciona Arquivo ao WSDL
                    $objSoap->addFile($pathWsServer, "WsServer");
                    $objSoap->addFile($pathController, $wsInterfaceClass);
                    
                    //Ignora métodos da classe Enviada
                    $objSoap->addIgnore($wsInterfaceClass, "__construct");
                    
                    //Verifica array de métodos para serem ifnorados
                    if(is_array($this->arrIgnoredMethods) && sizeof($this->arrIgnoredMethods)){
                        //Caso existam métodos, o loop ignora os mesmos
                        foreach($this->arrIgnoredMethods as $method){
                            $objSoap->addIgnore($wsInterfaceClass, $method);
                        }
                    }
                    
                    //Ignora métodos da classe WebService
                    $objSoap->addIgnore("WsServer", "__construct");                
                    $objSoap->addIgnore("WsServer", "actionIndex");
                    $objSoap->addIgnore("WsServer", "xmlException");
                    $objSoap->addIgnore("WsServer", "actionWsdl");
                    $objSoap->addIgnore("WsServer", "getXmlField");
                    $objSoap->addIgnore("WsServer", "setArrIgnoredMethods");
                    $objSoap->addIgnore("WsServer", "setLocal");
                    $objSoap->addIgnore("WsServer", "setWsInterfaceClass");
                    
                    //Exibe WSDL
                    $objSoap->showWsdl();
                }
            }catch(Exception $e){
                throw $e;
            }
        }     
        
        /**
         * Retorna um objeto SOAP a partir da chamada do componente webservice().
         * 
         * @return Soap
         */
        private function getSoap(){
            $objSoap = Component::webservice();    
            $objSoap->setClass($this->wsInterfaceClass);
            
            return $objSoap;
        }
        
        /**
         * Gera um token de sessão.
         * 
         * @return string
         */
        public function getToken(){
            $objRet         = new \stdClass();
            $objRet->erro   = 7;
            $objRet->msg    = "Token inválido!";
            $xmlToken       = '';
            
            //Captura o Usuário e Senha enviados via HTTP - Basic (Base64)
            if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
                $usuario    = $_SERVER['PHP_AUTH_USER'];
                $senha      = $_SERVER['PHP_AUTH_PW'];
                
                //valida user enviado
                $rsAuth = AuthHelper::authWsUsuario($usuario, $senha);
                
                if($rsAuth !== FALSE){
                    //Gera um novo TOKEN                
                    $objToken  = new Token();
                    $token     = $objToken->tokenGen(0);                                                           

                    if (strlen($token) == 40) {
                        //Grava o novo TOKEN no banco de dados
                        $rsToken = AuthHelper::salvarTokenWs($rsAuth, $usuario, $token);
                        
                        //Verifica retorno 
                        if($rsToken->status){
                            $objRet->erro   = 0;
                            $objRet->msg    = 'Token gerado com sucesso.';                    

                            $xmlToken       .= "<dados>";
                            $xmlToken       .= "<token>".$token."</token>";
                            $xmlToken       .= "</dados>";
                        }
                    }
                }else{
                    $ret->erro  = 2;
                    $ret->msg   = "Falha na autenticacao HTTP - Usuário inválido ou Não enviado!";
                }
            }else{
                $ret->erro  = 2;
                $ret->msg   = "Falha na autenticacao HTTP - Usuário inválido ou Não enviado!";
            }

            $ret = "<root>";
            $ret .= "<status>";
            $ret .= "<erro>{$objRet->erro}</erro>";
            $ret .= "<msg>{$objRet->msg}</msg>";
            $ret .= "</status>";
            $ret .= $xmlToken;
            $ret .= "</root>";

            return $ret;
        }
        
        /**
         * Valida o usuário que está acessando o serviço
         * 
         * @param mixed $dados
         * @return boolean
         */
        protected function authenticate($token){
            //Objeto de retorno
            $ret                = new \stdClass();
            $ret->status        = false;
            $ret->erro          = 1;
            $ret->msg           = "Erro inesperado!";
            $ret->ID_CLIENTE    = 0;

            //Captura o Usuário e Senha enviados via HTTP - Basic (Base64)
            if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
                $usuario    = $_SERVER['PHP_AUTH_USER'];
                $senha      = $_SERVER['PHP_AUTH_PW'];

                //valida user enviado
                $rsAuth = AuthHelper::authWsUsuario($usuario, $senha);

                //Se houver falha na autenticação o usuário é retornado
                if($rsAuth === FALSE){
                    $ret->erro  = 2;
                    $ret->msg   = "Falha na autenticacao HTTP - Usuário inválido ou Não enviado!";
                    return $ret;
                }

                //Verificação do TOKEN enviado.
                if($token == null){
                    $ret->erro  = 7;
                    $ret->msg   = "Token inválido ou nulo!";
                    return $ret;
                }else{
                    //Caso o $token seja enviado o mesmo será validado      
                    if(AuthHelper::validarTokenAtivoCliente($rsAuth, $token)){
                        $ret->status        = true;
                        $ret->erro          = 0;
                        $ret->msg           = "Token válido!";
                        $ret->ID_USER       = $rsAuth;
                    }else{
                        $ret->status    = false;
                        $ret->erro      = 4;
                        $ret->msg       = "Token inválido!";
                    }

                    return $ret;
                }
            }else{
                $ret->erro  = 2;
                $ret->msg   = "Falha na autenticacao HTTP - Usuário inválido ou Não enviado!";
                return $ret;
            }
        }
        
        /**
        * Função que varre o array XML de parâmetros e retorna o valor do campo solicitado
        * 
        * @param array $xmlParams
        * @param string $fielName
        * 
        * @return string $value
        * @throws Exception
        */
        protected function getXmlField($xmlParams, $fielName){
            //Varre o array de campos para capturar o valor solicitado.
            if(is_object($xmlParams) || is_array($xmlParams)){
                foreach($xmlParams as $param){
                    $id = (string)$param['id'];

                    if($id == $fielName){
                        $tmp = mysql_escape_string(trim((string)$param));

                        //Se depois das tratativas se o valor for vazio retorna NULL
                        if($tmp == '' || $tmp == null){
                            return null;
                        }else{
                            return mysql_escape_string(trim((string)$param));
                        }
                    }
                }
            }

            return null;
        }
}

?>
