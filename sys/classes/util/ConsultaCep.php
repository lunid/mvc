<?php
namespace sys\classes\util;

/**
 * Esta classe permite a consulta de um endereço a partir de um CEP, e ainda 
 * permite efetuar o cálculo de sedex a partir do CEP de origem e destino, 
 * valor declarado e peso da encomenda.
 *
 * @author Claudio
 */
class ConsultaCep {
    
    private $cepOrig        = '02420001';
    private $valorDeclarado = '1';
    private $pesoTotal      = 0;
    private $pathCalcSedex  = 'http://cartao.locaweb.com.br/correios/calcula_sedex.asp';
    private $arrVar         = array();//Guarda os valores recebidos após a consulta do CEP
    
    function __construct($cepOrig){
        $this->cepOrig = $this->vldCep($cepOrig);
    }
    
    function setValorDeclarado($valorDeclarado){
        $this->valorDeclarado = $valorDeclarado;
    }
    
    function pesoTotal($pesoTotal){
        $this->pesoTotal = $pesoTotal;
    }
    
    function getEndereco($cepDest){
        
        $cepDest    = $this->vldCep($cepDest);
        $path       = $this->getPath($cepDest);
        
        ini_set("allow_url_fopen", 1);
        //----------------------------------------------------------------------
        $file           = file($path);
        $response       = $file[0];
        $arrResponse    = explode("<br>", $response);

        $strEndereco    ='';
        $cep            = trim($cep);
        $i              = 0;
        $erro           = 0;
        
        foreach ($arrResponse as $row) {
            if ($i == 0 && ereg("^OK$", $line)) {
                continue;
            } elseif ($i == 0) {                    
                $erro++;
            } else {
                list($var, $value) = explode(':', $row);
                $var = strtolower(trim($var));
                $this->arrVar[$var] = utf8_encode(trim($value)); 
            }
            $i++;
        }
        //----------------------------------------------------------------------
        ini_set("allow_url_fopen", 0); //funÃ§Ã£o desabilitada	  
    }
    
    /**
     * Valida um CEP (código postal) informado.
     * Caso o CEP seja fornecido com hífen, no formato 99999-999, este será removido.
     * 
     * @param string $cep Deve conter 8 caracteres numéricos, ou então, 
     * pode ser fornecido no formato 99999-999
     * @return string | FALSE Retorna o CEP válido, ou FASLE caso seja inválido
     * 
     * @throws Exception Caso o CEP informado seja inválido.
     */
    function vldCep($cep){
        $cep        = trim($cep);
        $cep        = str_replace('-','',$cep);
        $numChars   = strlen($cep);
        
        if ($numChars == 8 && ctype_digit($cep)) {
            return $cep;
        } else {
            $msgErr = "O cep informado {$cep} não é válido.";
            throw new \Exception($msgErr);
        }
    }
    
    private function getPath($cepDest){
        $cepOrig        = $this->cepOrig;
        $pathCalcSedex  = $this->pathCalcSedex;
        $metodo         = "leitura";
        $ValorDeclarado = $this->valorDeclarado;
        $pesoTotal      = $this->pesoTotal;
        
        $path   = $pathCalcSedex."?cepOrig={$cepOrig}&cepDest={$cepDest}&pesoDeclarado={$pesoTotal}";
        $path   .= "&vlrDeclarado={$ValorDeclarado}&metodo={$metodo}";        
        return $path;
    }
    
    function __get($var){
        $value  = '';
        $arrVar = $this->arrVar;
        if (isset($arrVar[$var])) {
            $value = $arrVar[$var];
        }
        return $value;
    }
}

?>
