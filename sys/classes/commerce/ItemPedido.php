<?php

namespace sys\classes\commerce;

class ItemPedido {
    
    private $descricao  = '';
    private $quantidade = 1;    
    private $precoUnit  = 0;
    private $unidade    = 'CX';
    private $campanha   = '';
    private $saveItem   = FALSE;//TRUE = grava o registro atual no servidor remoto.
    private $precoUnitSemFormat; //Preço unitário sem formatação. Ex.: 123,40 ficará 12340, 2345 ficará 234500, 65,3 ficará 6530    
    
    /**
     * Inicializa um objeto com descrição, preço unitário e quantidade.
     * Ao informar o preço unitário, o padrão usado será o ponto '.' como separador decimal e nenhum separador de milhar.
     * 
     * @param string $descricao
     * @param float $precoUnit
     * @param integer $qtde
     */
    function __construct($descricao,$precoUnit=0,$qtde=1,$unidade='CX',$campanha=''){
        $this->setDescricao($descricao);
        $this->quantidade = (int)$qtde;
        $this->setUnidade($unidade);
        $this->setCampanha($campanha);
        if ($precoUnit > 0) {
            $this->precoUnitEn($precoUnit);
        }
    }
    
    /**
     * Salva o produto atual no servidor remoto.
     * Este recurso pode ser útil caso queira gerar um novo pedido que inclui o produto atual
     * a partir do painel de controle.
     * 
     * @return void
     */
    public function saveItemOn(){
        $this->saveItem = true;
    }    
    
    public function getSaveItem(){
        return $this->saveItem;
    }
    
    function setUnidade($unidade){
        if (strlen($unidade) <= 3 && ctype_alpha($unidade)) {
            $this->unidade = $unidade;
        } else {
            throw new \Exception('ItemPedido->setUnidade(): a unidade informada '.$unidade.' não é válida. A unidade deve conter apenas letras e no máximo 3 catacteres.');
        }
    }
    
    function getUnidade(){
        return $this->unidade;
    }

    /**
     * Informa o nome da campanha relacionada à compra do produto/serviço atual (opcional).     
     * 
     * @param string $campanha String alfanumérica de até 20 caracteres.
     * @return void
     */
    function setCampanha($campanha){
        if (strlen($campanha) > 0) {
             $this->campanha = $campanha;                
        }
    }
    
    function getCampanha() {
        return $this->campanha;
    }
    
    /**
     * Informa a descrição do produto.     
     * @param type $descricao
     */
    function setDescricao($descricao){
        if (strlen($descricao) > 0) {            
            $this->descricao = $descricao;
        }
    }
    
    function getDescricao(){
        return $this->descricao;
    }
    
    /**
     * Informa a quantidade de um produto e qual a sua unidade de medida.
     * 
     * @param integer $qtde Valor inteiro que indica a quantidade do item.
     * @param string $unid Unidade de medida do produto. É permitido utilizar no máximo 3 letras para indicar a unidade.
     * Por exemplo, "CX" para caixa, "PC" para pacote, "UN"
     * 
     * @throws \Exception
     */
    function setQuantidade($qtde=1,$unidade='CX'){
        if (ctype_alpha($unid)) {
            $this->quantidade = (int)$qtde;
            $this->setUnidade($unidade);
        } else {
            throw new \Exception('A unidade '.$unid.' informada em ItemPedido->setQuantidade() não é válida. O parâmetro unid deve conter apenas letras.');
        }
    }
    
    function getQuantidade(){
        return $this->quantidade;
    }
    
    /**
     * Informa o preço unitário do produto no formato 9999.99 (notação inglesa).
     * 
     * @param float $precoUnit Valor decimal no formato americano (usa ponto como separador decimal)
     */
    function precoUnitEn($precoUnit){
        $precoUnit = str_replace(',','',$precoUnit);
        $this->setPrecoUnit($precoUnit,'.','');
    }
    
    /**
     * Informa o preço unitário do produto no formato 9999,99.
     * 
     * @param float $precoUnit Valor decimal no formato brasileiro (usa vírgula como separador decimal)
     * @param string $thousandsSep Caractere separador de milhar.
     */    
    function precoUnitBr($precoUnit) {
        $precoUnit = str_replace('.','',$precoUnit);        
        $this->setPrecoUnit($precoUnit,',','');
    }
    
    /**
     * Informa o preço unitário do produto.
     *      
     * @param float $precoUnit Preço como valor decimal
     * @param string $decPoint Separador decimal usado no $precoUnit informado.
     * @param string $thousandsSep Separador de milhar usado no $precoUnit informado.
     * @return void
     */
    private function setPrecoUnit($precoUnit,$decPoint,$thousandsSep){
        if (is_numeric($precoUnit)) {
            $precoUnitSemFormat             = $this->convertNumberDec2NumberInt($precoUnit, $decPoint, $thousandsSep); 
            $this->precoUnitSemFormat       = $precoUnitSemFormat;
            $this->precoUnit                = number_format($precoUnit,2,'.','');
        } else {
            throw new \Exception('ItemPedido->setPrecoUnit(): O preço unitário informado não é um valor válido.');
        }
    }
    
    function getPrecoUnit(){
        return $this->precoUnit;
    }
    
    function getPrecoUnitSemFormat(){
        return $this->precoUnitSemFormat;
    }
    
    /**
     * Recebe um valor no formato 9.999,99, ou 9999.99, ou ainda 9999,99, e converte 
     * para um valor inteiro, sem separadores, onde os dois últimos caracteres representam 
     * a parte decimal.
     * 
     * Exemplos:
     * 123,543 ficará 12354 (equivale a 123,54)
     * 1254    ficará 125400 (equivale a 1254,00)
     * 
     * @param float $valueDec Valor decimal a ser convertido para inteiro
     * @param string $decPoint Separador decimal usado no $valueDec informado.
     * @param string $thousandsSep Separador de milhar usado no $valueDec informado.
     * @return integer
     */
    function convertNumberDec2NumberInt($valueDec,$decPoint,$thousandsSep){
        $numberInt  = number_format($valueDec, 2, $decPoint, $thousandsSep);
        $numberInt  = str_replace($decPoint,'',$valueDec);
        $numberInt  = str_replace($thousandsSep,'',$valueDec); 
        return $numberInt;
    }
    
    /**
     * Calcula o subtotal do produto atual multiplicando a quantidade pelo valor unitário.
     * 
     * @return flaot Retorna o subtotal do produto atual.
     */
    function calcSubtotal(){
        $quantidade    = (int)$this->quantidade;
        $precoUnit     = $this->precoUnitSemFormat;
        $subtotal      = $precoUnit;
        
        if ($quantidade > 1) {
            $subtotal = $quantidade*$precoUnit;
        }
        
        //Formata a saída com duas casas decimais:
        $subtotal = number_format($subtotal,2,'.','');
        
        return $subtotal;
    }
    
    function setPrecoPromo($precoDe,$precoPor){
        
    }
}

?>
