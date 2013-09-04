<?php

/**
 * Classe usada para gerar código aleatório.
 */
namespace sys\classes\util;

/**
 * Classe usada para gerar código numérico randômico.
 * 
 * Exemplo de uso:
 * <code>
 *  $codigo = Code::getRandomCode();
 *  echo $codigo;
 * </code>
 * 
 * IMPORTANTE: não há garantias de que o código gerado é único.
 */
class Code {

    /**
     * Retorna um código numérico com quantidade de dígitos variável e maior que dez dígitos.
     * Caso seja necessário definir uma quantidade de dígitos fixa, esse tratamento deve
     * ser feito na origem da chamada.
     * 
     * IMPORTANTE: não há garantias de que o código gerado é único.
     * 
     * @return string
     */
    public static function getRandomCode(){
        mt_srand(self::make_seed());//Utiliza o valor gerado em make_seed() como semente do número randômico.
        $randval = mt_rand();        
        $hr      = date('s');//segundos.
        $cod     = "$randval"."$hr";        
        return $cod;
    }
    
    /**
     * Gera um código randômico com um número de caracteres determinado pelo usuário.
     * 
     * @param integer $numChars Limite: 20.
     * @return integer
     * @throws \Exception Caso $numChars seja maior que 20.
     */
    public static function getRandomCodeNumChars($numChars) {
        if ($numChars < 20) {
            $code       = substr(self::getRandomCode(),0,$numChars);
            $tamCode    = strlen($code);

            if ($tamCode < $numChars) {
                $dif        = $numChars - $tamCode;
                $charsAdic  = substr(self::getRandomCode(),0,$dif);
                $code       .= $charsAdic;
            }
        } else {
            throw new \Exception('A quantidade de caracteres solicitada é maior que 20 (limite permitido).');           
        }
        return $code;
    }
    
    /**
     * Gera um valor decimal que será usado na chamada de mt_srand().
     * Este valor é conhecido como semente (seed) e serve para influenciar o valor 
     * randômico da função mt_rand().          
     * 
     * O método atual é auxiliar do método getCode().
     *  
     * @return float
     */
    private static function make_seed(){
        /*
         * Encontra o timestamp em microsegundos e separa os valores $msec e $sec,
         * sendo que $msec é um valor decimal (ex.: 0.48273900) e $sec trata-se de 
         * um valor numérico inteiro (Ex.: 1363789254).         
         */
        list($msec, $sec) = explode(' ', microtime());        
        $numDecimal = (float)$sec + ((float)$msec * 1000);
        return $numDecimal;
    }    
}

?>
