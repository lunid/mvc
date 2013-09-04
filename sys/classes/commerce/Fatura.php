<?php


namespace sys\classes\commerce;
use \sys\classes\util\Curl;

class Fatura {
    
    //Lista de parâmetros permitidos ao criar um novo pedido
    private $arrLibParams = array(
        'NUM_PEDIDO:setNumPedido',
        'VALOR_COMPRA:setValorCompra',                
        'VALOR_TOTAL:setTotalPedido',
        'VALOR_FRETE:setFrete',
        'NOME_SAC:setNomeSac',
        'EMAIL_SAC:setEmailSac',
        'ENDERECO_SAC:setEnderecoSac',
        'CIDADE_SAC:setCidadeSac',
        'UF_SAC:setUfSac',
        'CPF_CNPJ_SAC:setCpfCnpjSac',
        'CAMPANHA:setCampanha',
        'OBS:setObs'
    );
    
    private $arrParams          = array();
    private $arrItemPedido      = array(); //Array de objetos do tipo ItemPedido.
    private $valorTotalDoPedido = 0;
    private $valorFrete         = 0;
    private $nomeSac;
    private $emailSac;
    private $enderecoSac;
    private $cidadeSac;
    private $ufSac;
    private $cpfCnpjSac;
    private $_urlSend   = 'http://dev.superproweb.com.br/commerce/pedido/request/';
    private $debug      = FALSE;
    private $saveSacado = FALSE;//Grava os dados do sacado no servidor remoto.
    
    function __construct($arrDados=array()){
        $this->loadArrDados($arrDados);
    }    
    
    private function checkObjXml($obj){
        if (is_object($obj) && $obj instanceof XmlValidation) {
            return $obj;
        } else {
            throw new \Exception("Fatura: o objeto informado em {$param} não é válido.");
        }
    }
    
    function setConfig($obj){
        try {
            $obj        = $this->checkObjXml($obj,__FUNCTION__);
            $numFatura  = $obj->getNumFatura();
            if ($numFatura == 0) {
                /*
                 * Um número de fatura não foi informado. Localiza o próximo número de fatura
                 * disponível para o convêncio atual.
                 */
                
            }
        } catch (\Exception $e) {
            throw new $e;
        }
    }
    
    function setSacado($obj){
        
    }
    
    function setItens($obj){
        
    }
    
    function setCheckout($objCc, $objBlt){
        
    }
    
    private function checkObjXmlValidation($objXmlValidation){
        if (is_object($objXmlValidation)) {
            return TRUE;
        }
        return FALSE;     
    }
    
    public function debugOn(){
        $this->debug = TRUE;
    }
    
    public function debugOff(){
        $this->debug = FALSE;
    }
    
    /**
     * Salva os dados do sacado no servidor remoto.
     * Este recurso pode ser útil caso seja necessário efetuar uma nova cobrança 
     * para o mesmo sacado via painel de controle.
     * 
     * @return void     
     */
    public function saveSacadoOn(){
        $this->saveSacado = true;
    }  
    
    public function saveSacadoOff(){
        $this->saveSacado = FALSE;
    }
    
    /**
     * Recebe um array associativo de dados onde cada índice deve coincidir com um índice em $arrLibParams.
     * 
     * @param string[] $arrDadosSac
     * @return void
     * 
     * @throws \Exception Caso um ou mais parâmetros informados não possuam correspondência em $arrLibParams.
     */
    public function loadArrDados($arrDados){
        $arrMsgErr = NULL;
        if (is_array($arrDados) && count($arrDados) > 0) {
            $arrLibParams    = $this->arrLibParams;//Parâmetros permitidos
            $arrAction       = array();
            $arrTag          = array();
            
            //Separa um array com os parâmetros autorizados e outro com os seus respectivos métodos.
            foreach($arrLibParams as $label){
                list($indice,$action) = explode(':',$label);
                if (strlen($action) > 0) $arrAction[]   = $action;
                if (strlen($indice) > 0) $arrTag[]      = $indice;
            }          
            
            //Valida o array de dados do sacado:
            foreach($arrDados as $name=>$value) {
                $key = array_search($name,$arrTag);
                if ($key !== FALSE) {
                    $action = $arrAction[$key];
                    if (method_exists($this, $action)) {
                        //Existe um método para definir o valor do parâmetro atual:
                        $this->$action($value);
                    } else {
                        throw new \Exception('Pedido->loadArrDadosSac() A tag informada '.$name.' parece ser inválida ou o método '.$action.' associado a ela não existe.');
                    }
                } else {
                    $arrMsgErr[] = "Parâmetro {$name} não permitido";
                }               
            }
        }
        
        if (is_array($arrMsgErr)) {
            $msgErr = join(', ',$arrMsgErr);
            throw new \Exception($msgErr);                
        }
    }
    
    /**
     * Adiciona um item (produto) ao pedido atual.
     * 
     * @param ItemPedido $objItemPedido
     * @return void
     */
    public function addItemPedido($objItemPedido){
        if (is_object($objItemPedido)) $this->arrItemPedido[] = $objItemPedido;
    }
    
    /**
     * Define o parâmetro numPedido.
     * O valor deve ser numérico.
     * 
     * @param integer $numPedido
     */
    public function setNumPedido($numPedido){
        if (ctype_digit($numPedido)) {
            $this->addParam('NUM_PEDIDO',$numPedido);
        } else {
            $msgErr = 'O número do pedido deve ser um valor numérico inteiro.';
            throw new \Exception($msgErr);
        }
    }
    
    /**
     * Informa o valor total do pedido.
     * Este valor refere-se ao valor que será cobrado do cliente (soma de produtos + frete + acréscimos - descontos).
     * Caso não seja informado, o valor total do pedido será calculado pelo sistema.
     * 
     * @param float $value Valor decimal (formato 9999.99)
     * @return void
     * @throws \Exception Caso o valor informado não seja numérico
     */
    public function setTotalPedido($value){
        if (is_numeric($value)) {
           $valueDec                    = number_format($value, 2, '.', '');
           $this->valorTotalDoPedido    = $valueDec;
           
           $this->addParam('VALOR_TOTAL',$valueDec);
        } else {
            $msgErr = "Pedido->setTotalPedido() O valor informado {$value} não é um valor válido.";
            throw new \Exception($msgErr);
        }
    }
    
    public function getTotalPedido(){
        $valorTotalDoPedido = $this->valorTotalDoPedido;
        if ($valorTotalDoPedido == 0) {
            //Um valor explícito não foi informado. Calcula o total do pedido           
            $subtotalItens  = 0;
            $frete          = $this->valorFrete;
            $arrItemPedido  = $this->arrItemPedido;
            if (is_array($arrItemPedido)) {
                foreach($arrItemPedido as $objItemPedido){
                    //Soma o subtotal do produto atual com os anteriores:
                    $subtotalItens += $objItemPedido->calcSubtotal();
                }
            }
            
            $valorTotalDoPedido = $subtotalItens+$frete;
        }
        return $valorTotalDoPedido;
    }
    
    /**
     * Informa um valor numérico que representa o valor do frete do pedido.              
     * A informação de frete é opcional. Se não for informado o valor zero será enviado.          
     * 
     * @param float $value Valor do frete
     * @return void
     */    
    public function setFrete($value=0){
        if (is_numeric($value)) {
           $valueDec            = number_format($value, 2, '.', '');
           $this->valorFrete    = $valueDec;
           
           $this->addParam('VALOR_FRETE',$valueDec);
        } elseif (strlen($value) > 0) {
            $msgErr = "Pedido->setFrete() O valor informado {$value} não é um valor numérico válido. ";
            $msgErr .= "Utilize ponto como separador decimal (formato: 9999.99).";
            throw new \Exception($msgErr);
        }               
    }
    
    public function setNomeSac($value){
        $value = trim($value);
        if (strlen($value) > 0){
            $this->nomeSac = $value;
            $this->addParam('NOME_SAC',$value);
        }          
    }
    
    public function setEmailSac($value){
        $value = trim($value);
        if (strlen($value) > 0){
            $this->emailSac = $value;
            $this->addParam('EMAIL_SAC',$value);
        }          
    }
    
    public function setEnderecoSac($value){
        $value = trim($value);
        if (strlen($value) > 0){
            $this->enderecoSac = $value;
            $this->addParam('ENDERECO_SAC',$value);
        }        
    }
    
    public function setCidadeSac($value){
        $value = trim($value);
        if (strlen($value) > 0){
            $this->cidadeSac = $value;
            $this->addParam('CIDADE_SAC',$value);
        }
    }
    
    public function setUfSac($value){
        $value = trim($value);
        if (strlen($value) == 2 && ctype_alpha($value)) {
            $value          = strtoupper($value);
            $this->ufSac    = $value;
            $this->addParam('UF_SAC',$value);
        }
    }
    
   /**
    * Recebe e guarda um valor referente a um CPF (11 dígitos) ou a um CNPJ (14 dígitos).
    * 
    * @param string $value
    */
    function setCpfCnpjSac($value){
        $value = trim($value);
        if (strlen($value) > 0) {
            $arrValue   = str_split($value);
            $arrChar    = array();
            
            //Retira caracteres que não sejam numéricos:
            foreach ($arrValue as $char){
                if (ctype_digit($char)) $arrChar[] = $char;
            }
            
            $valueChar = join('',$arrChar);
            
            if (strlen($valueChar) >=11 && strlen($valueChar) <= 14 && ctype_alnum($valueChar)) {
                $this->cpfCnpjSac = $valueChar;
                $this->addParam('CPF_CNPJ_SAC',$valueChar);
            } else {
                $msgErr = 'Pedido->setCpfCnpjSac() O CPF/CNPJ informado ('.$value.') parece ser inválido.';
                throw new \Exception($msgErr);
            }       
        }
    }           
    
    /**
     * Armazena uma variável com seu respectivo valor em um array que será usado
     * posteriormente para gerar o XML de envio.
     * 
     * @param string $name Nome que será usado no atributo 'id' da tag XML 'PARAM'.
     * @param mixed $value Valor da tag.
     * @throws \Exception caso o parâmetro $name não seja um parâmetro válido.
     */
    private function addParam($name,$value){
       $name            = strtoupper($name);
       $arrLibParams    = $this->arrLibParams;//Parâmetros permitidos
       $arrTag          = array();
       
       foreach($arrLibParams as $label){
           list($indice,$action) = explode(':',$label);
           if (strlen($indice) > 0) $arrTag[] = $indice;
       }
       
       $key = array_search($name,$arrTag);
       if ($key !== FALSE) {
           $this->arrParams[$name] = $value;
       } else {
           $msgErr = 'O parâmetro informado não é válido.';
           throw new \Exception($msgErr);
       }
    }
    
    /**
     * Gera a string XML que será enviada ao gateway de pagamento.
     * 
     * @throws \Exception Caso nenhum parâmetro tenha sido informado ou então não exista produto(s).
     */
    function getXml(){
        $xml            = '<ROOT>';
        $arrParams      = $this->arrParams;
        $arrItemPedido  = $this->arrItemPedido;
        
        if (is_array($arrParams) && count($arrParams) > 0) {
            $xml .= "<PEDIDO>";
                        
            foreach ($arrParams as $key=>$value){
                $xml .= $this->setTagXml($key, $value);
            }
            
            $totalPedido = $this->getTotalPedido();
            $xml .= $this->setTagXml('TOTAL_PEDIDO', $totalPedido);
            
            //Salvar dados do sacado no servidor remoto:           
            if ($this->saveSacado) $xml .= $this->setTagXml('SAVE_SAC', 1);
            
            if (is_array($arrItemPedido) && count($arrItemPedido) > 0) {
                foreach ($arrItemPedido as $objItemPedido){
                    $descricao           = $objItemPedido->getDescricao();
                    $quantidade          = $objItemPedido->getQuantidade();
                    $precoUnit           = $objItemPedido->getPrecoUnit();
                    $campanha            = $objItemPedido->getCampanha();
                    $subtotal            = $objItemPedido->calcSubtotal();
                    $saveItem            = ($objItemPedido->getSaveItem())?1:0;
                    
                    $xml .= "
                    <ITEM>
                            ".$this->setTagXml('DESCRICAO', $descricao)."                        
                            ".$this->setTagXml('QUANTIDADE', $quantidade)."             
                            ".$this->setTagXml('PRECO_UNIT', $precoUnit)."  
                            ".$this->setTagXml('CAMPANHA', $campanha)." 
                            ".$this->setTagXml('SUBTOTAL', $subtotal)."
                            ".$this->setTagXml('SAVE', $saveItem)."    
                    </ITEM>";
                }                
            } else {
                $msgErr = 'Pedido->getXml() Nenhum produto foi adicionado ao pedido.';
                throw new \Exception($msgErr);
            }
            
            $xml .= "</PEDIDO>";
        } else {
            $msgErr = 'Pedido->getXml() Nenhum parâmetro foi informado.';
            throw new \Exception($msgErr);
        }
        $xml .= "</ROOT>";
        
        return $xml;
    }
    
    /**
     * Método auxiliar de getXml(), retira caracteres não permitidos antes de criar 
     * a tag PARAM com seu respectivo valor.
     * 
     * @param string $tag
     * @param mixed $value
     * @return string Tag que será usada para compor o XML de envio.
     */
    private function setTagXml($tag,$value){
        $value  = str_replace('"', '', $value);
        $value  = str_replace('<', '', $value);
        $value  = str_replace('>', '', $value);
        $tagXml = "<PARAM id='{$tag}'>{$value}</PARAM>";
        return $tagXml;
    }
    
    /**
     * Gera a string XML de envio e faz a conexão com o gateway.
     *  
     * @return string Resposta do gateway.
     * @throws \Exception Caso um erro ocorra na comunicação entre o servidor local e o gateway.
     */
    function send(){
        $xmlNovoPedido  = $this->getXml(); 
        if ($this->debug) die($xmlNovoPedido);//O debug foi acionado: interrompe o envio e imprime o XML a ser enviado.
        
        $uid            = 'c3130175fa9617f59f51841f38c06e029482918a';
        $request	= "xmlNovoPedido=".$xmlNovoPedido."&uid=".$uid;
        $objCurl 	= new Curl($this->_urlSend);
        
        $objCurl->setPost($request);
        $objCurl->createCurl();
        $errNo = $objCurl->getErro();
        if ($errNo == 0){
            $response = $objCurl->getResponse();
            return $response;
        } else {
            $err    = $objCurl->getOutput();
            $msgErr = "Pedido->send() Erro ao se comunicar com o gateway: {$err}";
            throw new \Exception($msgErr);                
        }                
    }
    
}

?>
