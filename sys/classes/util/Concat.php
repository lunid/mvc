<?php
namespace sys\classes\util;
/**
 * Classe usada para concatenar uma string com uma ou mais variáveis. 
 * As tags são marcadores entre chaves que seguem o formato {VAR_NAME}, onde VAR_NAME pode 
 * ser qualquer string contendo letras maiúsculas e/ou minúsculas.
 * 
 * EXEMPLO 1: 
 * O exemplo abaixo define uma matriz a partir de uma string e imprime "Olá João. Seu pedido é 123456.":
 * <code>
 *      $objConcat = new Concat();
 *      $objConcat->setStrMatriz("Olá {NOME}. Seu pedido é {NUM_PEDIDO}");
 *      $objConcat->addParam('NOME','João');
 *      $objConcat->addParam('NUM_PEDIDO','123456');
 *      echo $objConcat->render();
 * </code>
 * 
 * EXEMPLO 2:
 * O exemplo abaixo define uma matriz a partir de um arquivo e imprime "Olá Paulo. Seu pedido é ABCD1234.":
 * <code>
 *      $objConcat = new Concat();
 *      $objConcat->setFile('/files/path/msgEmail.html');
 *      $objConcat->addParam('NOME','Paulo');
 *      $objConcat->addParam('NUM_PEDIDO','ABCD1234');
 *      echo $objConcat->render();
 * </code>
 * 
 * EXEMPLO 3:
 * O exemplo abaixo define uma matriz a partir de um arquivo carregado no construtor,
 * e imprime "Olá Maria. Seu pedido é 0001234.":
 * <code>
 *      $objConcat = new Concat('/files/path/msgEmail.html');
 *      $objConcat->addParam('NOME','Maria');
 *      $objConcat->addParam('NUM_PEDIDO','0001234');
 *      echo $objConcat->render();
 * </code>
 */
class Concat {
    private $stringMatriz   = '';
    private $stringConcat   = '';//Guarda a stringMatriz concatenada com suas variáveis
    
    function __construct($pathFile=''){
        if (strlen($pathFile) > 0) $this->setFile($pathFile);
    }
    
    /**
     * Carrega o conteúdo de um arquivo válido para ser usado como matriz.
     * O contéudo pode conter variáveis que serão mescladas ao chamar o método 
     * 
     * @param $pathFile Caminho do arquivo cujo conteúdo deve ser armazenado.
     */
    function setFile($pathFile){
        if (file_exists($pathFile)){            
            $this->setStrMatriz(file_get_contents($pathFile));
        } else {
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'FILE_NOT_FOUND');    
            $msgErr = str_replace('{FILE}',$pathFile,$msgErr);
            throw new \Exception( $msgErr );            
        }
    }
    
    /**
     * Guarda uma string que servirá de matriz para mesclar uma ou mais variáveis a partir 
     * do método addParam();
     * 
     * @param String $stringMatriz
     * @return void 
     */
    function setStrMatriz($stringMatriz){
        $this->stringMatriz = $stringMatriz;       
    }
    
    function addParams($params=array()){
        if (is_array($params)) {          
            foreach($params as $var=>$value){
                //echo "$var - $value<br>;";
                $this->addParam($var,$value);                              
            }            
        }
    }
    
    /**
     * Concatena um valor no marcador (tag) da string carregada em setString().
     * Uma tag é formada por {$var}.
     * 
     * Execute este método para cada variável que deseja mesclar na string atual.
     * 
     * O exemplo abaixo imprime "Olá João. Seu pedido é 123456.":
     * <code>
     *      $objConcat = new Concat();
     *      $objConcat->setStrMatriz("Olá {NOME}. Seu pedido é {NUM_PEDIDO}");
     *      $objConcat->addParam('NOME','João');
     *      $objConcat->addParam('NUM_PEDIDO','123456');
     *      echo $objConcat->render();
     * </code>
     * 
     * @param string $var Nome da variável a ser mesclada na string (Ex. {$var}).
     * @param string $value Valor da variável que substituirá a tag.
     * @return void
     */
    function addParam($var,$value) {
        $stringConcat = $this->getStringConcat();//stringMatriz + variáveis concatenadas até o momento.
        if (strlen($stringConcat) > 0) {
            $tag             = "{{$var}}";
            $stringConcat    = str_replace($tag,$value,$stringConcat);
            $this->setStringConcat($stringConcat);
        } else {
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'STRING_NULL');              
            throw new \Exception( $msgErr );              
        }
    }
    
    private function getStringConcat(){
        $stringConcat = $this->stringConcat;
        if (strlen($stringConcat) == 0) $stringConcat = $this->stringMatriz;        
        return $stringConcat;
    }
    
    private function setStringConcat($stringConcat){
        $this->stringConcat = $stringConcat;
    }
    
    /**
     * Retorna a stringMatriz com as alterações feitas após chamar addParam().
     * 
     * @return string 
     */
    function render(){
        return $this->stringConcat;
    }
}

?>
