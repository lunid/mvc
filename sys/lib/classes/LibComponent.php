<?php
/**
 * Classe abstrata que deve ser herdada por todas as classes que inicializam um componente.
 * Uma classe de componente deve existir em sys/lib/comps/componentFolder/classes/, onde o path sys/lib/comps/
 * pode ser alterado no arquvo config.xml global.
 * 
 * O nome de uma classe de componente deve iniciar obrigatóriamente com o prefixo Lib.
 * Por exemplo, um componente cujo path é sys/lib/comps/componentTest, a classe de componente 
 * será sys/lib/comps/componentTest/classes/LibComponentTest.
 * 
 * @abstract
 */
namespace sys\lib\classes;
use \sys\lib\classes\IComponent;
use \sys\classes\util\Dic;

abstract class LibComponent implements IComponent {
    private $params             = array();
    private $return             = NULL;
    private $objInfoComp        = NULL;//Guarda um objeto com as informações lidas a partir do arquivo install.xml
    private $exceptionFileXml   = '';
    
    function __construct($folder,$params=array()){
        
        //Url::exceptionXml($folder);
        $exceptionFileXml   = Url::exceptionXml($folder);
        $installFileXml     = Url::installXml($folder);
       
        //Define o path do arquivo XML usado como dicionário das mensagens de Exception.
        if (file_exists($exceptionFileXml)) $this->exceptionFileXml = $exceptionFileXml;
        
        try {
            $this->objInfoComp = new LoadInstallXml($installFileXml);          
        } catch(\Exception $e) {
            throw $e;
        }
        $this->saveParams($params);
    }
    
    /**
     * Armazena um array associativo de parâmetros a ser usados como variáveis
     * na execução do método init(), da classe de inicialização do componente.
     * 
     * Estes parâmetros são opcionais.
     * 
     * @param mixed[] $args 
     */
    function saveParams($params){
        $this->params = $params;
        if (is_array($params) && count($params) > 0) {
            $this->params = $params[0];
            foreach($params[0] as $key=>$value){
                if (!$this->vldParam($key)){
                    //Parâmetro não aceito.
                    $pathXmlDic = Url::exceptionClassXml(__CLASS__);
                    $msgErr     = Dic::loadMsgForXml($pathXmlDic,__METHOD__,'PARAM_NOT_EXISTS');                
                    $msgErr     = str_replace('{PARAM}',$key,$msgErr);
                    throw new \Exception( $msgErr );            
                }
            }
        }
    }
    
    private function vldParam($param){
        $objInfoComp = $this->objInfoComp;
        if (is_object($objInfoComp)) {
            //Há informações dos parâmetros permitidos para o componente atual.
            //Verifica se o parâmetro solicitado é permitido.
            if (is_object(@$objInfoComp->$param)) return TRUE;
        }         
        return FALSE;
    }
    
    /**
     * Retorna o xml referente às mensagens de Exception da lib atual.
     * 
     * @return string Caminho relativo do arquivo XML. 
     */
    function getXmlDic(){
        return $this->exceptionFileXml;
    }
    
    /**
     * Método mágico para acessar os parâmetros recebidos em setArgs() como variáveis de objeto.
     * 
     * @param string $var Nome da variável requisitada.
     * @return mixed Valor da variável. 
     */
    function __get($var){
        $params   = $this->params;
        $value    = '';   
        if ($var == 'param') {
            //O usuário solicitou o primeiro (ou único) parâmetro           
            $value  = $params;
        } elseif (is_array($params) && array_key_exists($var, $params)) {
            $value = $params[$var];
        }                
        return $value;
    }
    
    /**
     * Captura e dispara exceções.
     * 
     * @param string $method Método que fez a chamada. Use __METHOD__ ao chamar Exception().
     * @param string $codErr Código referente ao atributo id da mensagem a ser capturada: <msg id='codErr'>...</msg>
     * @param mixed[] $arrVars Array associativo, opcional. Se informado, é utilizado para mesclar valores com marcadores da mensagem.
     */
    protected function Exception($method,$codErr,$arrVars=array()){
        $pathXmlDic = $this->getXmlDic();
        $msgErr     = Dic::loadMsgForXml($pathXmlDic,$method,$codErr);
        if (is_array($arrVars)) {
            foreach($arrVars as $var=>$value) {
                $tag        = "{{$var}}";                
                $msgErr     = str_replace($tag,$value,$msgErr);
            }
        }        
        throw new \Exception( $msgErr );            
    }
    
    
    protected function setReturn($return){
        $this->return = $return;
    }
    
    /**
     * Retorna o resultado gerado no método init() da classe de inicialização do componente.
     * 
     * @return mixed 
     */
    function getReturn(){
        return $this->return;
    }    
}

?>
