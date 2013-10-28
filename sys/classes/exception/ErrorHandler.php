<?php
    
    namespace sys\classes\error;

    /**
     * Classe usada para tratar mensagens de erro.
     * Crie uma classe moduloAtual/classes/helpers/Error estendendo a classe atual para fazer uso dos recursos contidos aqui.
     * 
     * EXEMPLO 1:
     * Exemplo de uso, considerando uma classe Error que extende a classe atual.
     * A clase Error deve extender a classe ErrorHandler.
     * <code>
     *  use \sys\classes\error\ErrorHandler;   
     *  class Error extends ErrorHandler {
     * 
     *      public static function eLogin($codErr){
     *          //Abaixo, nome do arquivo xml, neste caso o mesmo nome do método (eLogin.xml), que contém o código ($codErr) solicitado.
     *          $nameXmlFile    = __FUNCTION__;
     *          $msgErr         = self::getErrorString($nameXmlFile,$codErr);
     *          return $msgErr;
     *      }
     * }     
     * </code>
     * 
     * Estrutura do arquivo moduloAtual/dic/eLogin.xml:
     * Consulte o modelo XML em modelo/dic/eError.xml.
     * 
     * 
     * EXEMPLO 2:
     * Exemplo de uso mostrando uma Exception em uma página de erro.
     * IMPORTANTE: a classe Controller deve extender sys/classes/mvc/ExceptionController.
     * <code>
     *  use \moduloAtual\classes\helpers\Error;            
     *  class Login extends Mvc\ExceptionController {
     *      ...
     *      function actionProcessAuth(){
     *          ...
     *          if ($login == FALSE) {
     *              $msgErr = Error::eLogin('LOGIN');      
     *              throw new \Exception($msgErr);
     *          }     
     *      }
     *      ...
     *  }
     * </code>
     * 
     * 
     * EXEMPLO 3:
     * Exemplo de uso gerando um arquivo de log:     
     * <code>
     *  use \moduloAtual\classes\helpers\Error;            
     *  class Login extends Mvc\ExceptionController {
     *      ...
     *      function actionProcessAuth(){
     *          ...
     *          if ($login == FALSE) {
     *              $msgErr = Error::eLogin('LOGIN');      
     *              Error::log($msgErr);
     *          }     
     *      }
     *      ...
     *  }
     * </code>   
     * 
     * EXEMPLO 4:
     * Exemplo usando mensagem comum a dois ou mais módulos. 
     * O arquivo XML é o sys/dic/eException.xml.
     * <code>
     *      ...
     *      $arrParams = array('FILE'=>'arquivo.xml');
     *      $msgErr = Error::eException('FILE_NOT_EXISTS',$arrParams);                                         
     *      throw new \Exception($msgErr);  
     *      ...
     * </code>
     */
    class ErrorHandler extends XmlException {
        
        /**
         * Procura a mensagem referente ao código de erro informado no arquivo eException.xml.
         * Caso não encontre o arquivo em moduloAtual/dic/eException.xml, procura em sys/dic/eException.xml;
         * 
         * É altamente recomendável NÃO sobrepor o arquivo eException.xml.
         * Utilize este método para localizar mensagens de erro comuns a todos os módulos.
         * 
         * @param string $codErr Exemplo: 'FILE_NOT_EXISTS'
         * @param array $arrParams Array associativo contendo valores a substituir na mensagem de erro encontrada.
         * @return string
         */
        public static function eException($codErr,$arrParams=array()){ 
            $nameXmlFile = __FUNCTION__;
            return self::getErrorString($nameXmlFile,$codErr,$arrParams);            
    }
        
        /**
         * Grava o código do erro informado em um arquivo de log com a data atual.
         * 
         * @param string $msgErr
         * @param array $arrParams
         * @return boolean
         */
        public static function log($msgErr,$arrParams=array()){     
            $save               = FALSE;
            $folderLogExists    = FALSE;            
            if (strlen($msgErr) > 0) { 
                $data       = date('d/m/Y');
                $hora       = date('H:i:s');
                $agora      = "{$data} às {$hora}";
                
                if ($msgErr)
                $content    = "### {$agora} ###\n{$msgErr}\n\n";
                $filename   = 'data/logs/log_'.date('d_m_Y').'.log';
                if (File::appendOrCreate($filename, $content)) $save = TRUE;
            }
            return $save;
        }
    }
?>
