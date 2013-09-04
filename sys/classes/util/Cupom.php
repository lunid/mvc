<?php

namespace sys\classes\util;

class Cupom {
    
    private $limMinChars        = 4;//Quantidade mínima de caracteres para um novo cupom.
    private $limMaxChars        = 15;//Quantidade máxima de caracteres para um novo cupom.
    private $cupom              = '';//Guarda um cupom alfanumérico válido.
    private $percentDescProPeq  = 0;
    private $percentDescProMed  = 0;
    private $percentDescProGrd  = 0;
    private $dataIniEn          = NULL;
    private $dataFimEn          = NULL;
    const CUPOM_SESSION         = 'GL_CUPOM';
    
    function __construct($cupom=''){
        if (strlen($cupom) > 0) $this->setCupom($cupom);
    }    
    
    function setCupom($cupom){
        $vldCupom = $this->vldCupom($cupom);
        if ($vldCupom) {
            $this->cupom = $cupom;
        } else {
            throw new \Exception("O cupom informado ({$cupom}) é inválido.");
        }
    }
    
    /**
     * Verifica se o cupom possui caracteres alfanuméricos 
     * dentro da quantidade mínima e máxima permitida.
     * 
     * @param string $cupom
     * @return boolean
     */
    function vldCupom($cupom) {        
        $cupom       = trim($cupom);
        $tamCupom    = strlen($cupom);
        $cupomValido = TRUE;
        if ($tamCupom > $this->limMaxChars || $tamCupom < $this->limMinChars || !ctype_alnum($cupom)) $cupomValido = FALSE;
        return $cupomValido;        
    }
    
    /**
     * Gera um novo cupom randomicamente.
     * IMPORTANTE: Este método não verifica no banco se o cupom já existe.
     * 
     * @param string $prefixo Opcional. Permite definir um prefixo para o novo cupom. Limite máximo de 5 caracteres
     * @param int $maxChars Opcional. Se informado indica o limite de caracteres do cupom. Caso contrário o $limMaxChars será usado
     * @return string Retorna o cupom gerado
     * @assert ('ABCD') == 'ABCD123456789100'
     * @assert ('AB',8) == 'AB12345678'
     */
    function setNovoCupomRandom($prefixo='',$maxChars=0){
        $cupom              = '';
        $limPrefixo         = 10;//Limite de caracteres permitido para prefixo.
        $limMaxChars        = ($maxChars > 0)?$maxChars:$this->limMaxChars;//Quantidade máxima de caracteres do cupom.
        $minCharsNumber     = $limMaxChars - $limPrefixo;//Limite mínimo para a parte numérica da cupom.
        $tamPrefixo         = strlen($prefixo);
        $numCharsNumber     = $limMaxChars;//Quantidade de caracteres reservada para caracteres numéricos.
        if ($tamPrefixo > 0) $numCharsNumber = $limMaxChars - $tamPrefixo;
        
        if ($numCharsNumber >= $minCharsNumber) {
            //Tudo ok. Randomiza a parte numérica.
            $random = Code::getRandomCodeNumChars($numCharsNumber);
            $cupom  = $prefixo.$random;
        } else {
            //Throw
            
        }
        
        //Aramazena cupom na instância do objeto.
        $this->setCupom($cupom);
        return $cupom;
    }
    
    /**
     * Define os percentuais de desconto para cada plano de assinatura.
     * 
     * Os planos são classificados como:
     *  - PEQ = plano vigente com menor quantidade de créditos.
     *  - GRD = plano vigente com maior quantidade de créditos.
     *  - MED = plano vigente cuja quantidade de créditos está entre os planos PEQ e GRD.
     * 
     * @param float $percentDescProPeq Percentual de desconto para o plano PEQ
     * @param float $percentDescProMed Percentual de desconto para o plano MED
     * @param float $percentDescProGrd Percentual de desconto para o plano GRD
     * @return void 
     */
    function setPercentDesc($percentDescProPeq,$percentDescProMed=0,$percentDescProGrd=0){
        $this->percentDescProPeq = (float)$percentDescProPeq;
        $this->percentDescProMed = (float)$percentDescProMed;
        $this->percentDescProGrd = (float)$percentDescProGrd;
    }
    
    /**
     * Define o período de validade do cupom.
     * Refere-se ao período em que o cupom permite uma compra com desconto.
     * 
     * @param date $dataIniEn Formato YYYY-mm-dd
     * @param date $dataFimEn Formato YYYY-mm-dd
     * @return void
     */
    function setValidade($dataIniEn,$dataFimEn){
        if (strlen($dataIniEn) == 10 && strlen($dataFimEn) == 10) {
            list($y,$m,$d) = explode('-',$dataIniEn);
            if (checkdate($m,$d,$y)) {
                list($y,$m,$d) = explode('-',$dataFimEn);
                if (checkdate($m,$d,$y)) {
                   $this->dataIniEn = $dataIniEn;
                   $this->dataFimEn = $dataFimEn;                    
                } else {
                    throw new \Exception('A data de fim de período não é válida. Utilize uma data no formato YYYY-mm-dd.');
                }
            } else {
                throw new \Exception('A data de início de período não é válida. Utilize uma data no formato YYYY-mm-dd.');
            }
        } else {
            throw new \Exception('As datas informadas não são válidas.');
        }        
    }
    
    /**
     * Faz a persistência do cupom em uma variável SESSION.
     * A chamada deste método é obrigatória para persistir o cupom atual.
     * 
     * @see getCupom()
     * @return stdClass Objeto de dados com as informações do cupom.
     */
    function render(){
        $cupom = $this->cupom;
        if (strlen($cupom) > 0) {
            $_SESSION[self::CUPOM_SESSION] = serialize($this);   
            
            $objCupom                       = new \stdClass();
            $objCupom->cupom                = $this->cupom;
            $objCupom->percentDescProPeq    = $this->percentDescProPeq;
            $objCupom->percentDescProMed    = $this->percentDescProMed;
            $objCupom->percentDescProGrd    = $this->percentDescProGrd;
            $objCupom->dataIniEn            = $this->dataIniEn;
            $objCupom->dataFimEn            = $this->dataFimEn;
            
            return $objCupom;
        } else {
            //throw
            
        }
    }
    
    /**
     * Retorna um objeto Cupom a partir de sua variável SESSION
     * 
     * @see render()
     */
    public static function getCupom(){
        $objCupom = NULL;
        if (isset($_SESSION[self::CUPOM_SESSION])) $obj = unserialize($_SESSION[self::CUPOM_SESSION]);
        return $objCupom;
    }

    
}

?>
