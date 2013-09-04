<?php
    namespace sys\lib\comps\webservice\classes;
    
    class Soap extends Wsdl{
        private $server; //Aramazena SoapServer       
        private $class; //Classe me utilização do serviço
        
        /**
         * Seta o nome da classe a ser utilizada com métodos de serviço
         * 
         * @param string $name Nome da classe
         */
        public function setClass($name){
            $this->class = $name;
        }
        
        /**
         * Inicia o serviço SoapServer
         * 
         * @throws SoapFault
         */
        public function index(){
            try{
                //Carrega configurações do XML
                $this->loadConfig("server");
                
                //Inicia serviço SoapServer
                $this->server = new \SoapServer(null, 
                    array(
                        'uri'       => $this->wsdl,
                        'encoding'  => 'utf-8'
                    )
                );
                
                //Cadastra Classe com métodos a serem utilizados
                $this->server->setClass(ucfirst($this->class));  
                $this->server->handle();
            }catch(Exception $e){
                throw $e;
            }
        }
    }
?>
