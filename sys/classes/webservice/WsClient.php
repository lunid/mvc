<?php

namespace sys\classes\webservice;
use \sys\classes\util\Dic;

abstract class WsClient extends WsConfigXml {
        
    private $httpUser;
    private $httpPass;    
    protected $client;
    protected $webserviceAlias;
    protected $wsInterface;
    
    public function __construct() {        
        $this->init();
    }    
    
    /**
     * Faz a leitura dos dados solicitados na classe filha, 
     * gera o array com os parâmetros necessários para inicializar um SoapClient e,
     * se todas as validações estiverem corretas, cria um objeto SoapClient.
     *  
     */
    function init(){
        $this->config();
        $options = $this->loadParams();
        $this->setSoapClient($options);        
    }
    
    /**
     * Carrega e valida as informações de autenticação para um determinado webservice.
     * É necessário que exista uma configuração válida no arquivo /config/webservice.xml.
     * 
     * @param $ws Nome (alias) do webservice, cujas configurações encontram-se no arquivo webservice.xml.
     */
    private function loadParams(){
        $options            = NULL;
        $webserviceAlias    = $this->webserviceAlias;
        $wsInterface        = $this->wsInterface;
        $host               = '';
        $user               = '';
        $passwd             = '';
        
        if (strlen($webserviceAlias) == 0 || strlen($wsInterface) == 0) {
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_DADOS_CONFIG'); 
            throw new \Exception( $msgErr );            
        }
        
        $pathXml    = "sys_config/webserviceClient.xml";
        $objXml     = Xml::loadXml($pathXml);
        if (is_object($objXml)) {
            $nodesServer = $objXml->client;
            
            foreach($nodesServer as $node){
                $id = $this->getAttrib($node,'id');
                if ($id == $webserviceAlias) {
                    //Encontrou a configuração do servidor informado:
                    $host   = (string)$node->host;
                    $user   = (string)$node->user;
                    $passwd = (string)$node->passwd;                    
                    break;
                }
            }
            
            if (strlen($host) > 0 && strlen($user) > 0 && strlen($passwd) > 0) {
                $options = array(
                    'location'  => $host . "{$wsInterface}/",
                    'uri'       => $host,
                    'encoding'  => "utf-8",
                    'trace'     => 1,
                    "stream_context" => stream_context_create(
                        array(
                            //Usuário para autenticação HTTP
                            "http" => array("header" => "Authorization: Basic " . base64_encode($user .":". md5($passwd)))
                        )
                    )
                );                   
            } else {
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_INFO_AUTH_WS'); 
                $msgErr = str_replace('{FILE_XML}',$pathXml,$msgErr);  
                $msgErr = str_replace('{WS}',$webserviceAlias,$msgErr);  
                throw new \Exception( $msgErr );  
            }               
        } else {
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_LOAD_XML'); 
            $msgErr = str_replace('{FILE}',$pathXml,$msgErr);
            throw new \Exception( $msgErr );                        
        }
        return $options;        
    }
    
    private function setSoapClient($options){
        try{
            if ($options != NULL) {
                //Inicia serviço SOAP:
                $this->client = new \SoapClient(null, $options);

                //Verifica erros no serviço:
                if(is_soap_fault($this->client)){
                    throw new Exception($this->client);
                }
            } else {
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_OPTIONS_SOAP_CLIENT');                 
                throw new \Exception( $msgErr );                              
            }
        }catch(Exception $e){
            $err    = (isset($e->faultcode))?$e->faultstring:$e->getMessage();
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_CREATE_SOAP_CLIENT'); 
            $msgErr = str_replace('{ERR_SERVER}',$err,$msgErr);
            throw new \Exception( $msgErr );              
        }        
    }
    
    protected function getToken(){
        $client = $this->client;
        if (is_object($client)) {
            $token = $this->client->getToken();
        } else {
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_OPTIONS_SOAP_CLIENT');                 
            throw new \Exception( $msgErr );              
        }
    }
}
?>
