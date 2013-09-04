<?php
/**
 * Description of Setup
 *
 * @author Interbits
 */
use \sys\lib\classes\LibComponent;
use \sys\lib\comps\webservice\classes\Soap;

class Webservice extends LibComponent {
    /**
     * Constrói um SoapServer baseado em uma classe Controller em API
     * 
     * @param string $class Nome do Controller
     * 
     * @return Soap Objeto Soap para utilização dos métodos
     * @throws Exception
     */
    public function init(){	
        try{
            //Verifica o envio do nome de serviço
            if(is_null($this->class)){
                throw new \Exception("Nome do serviço WS não definido no construtor de SOAP");
            }

            //Devolvendo o próprio objeto como retorno
            $this->setReturn(new Soap($this->class));
        }catch(Exception $e){
            throw $e;    
        }
    }
}

?>
