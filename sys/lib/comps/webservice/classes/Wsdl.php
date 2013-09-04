<?php
    namespace sys\lib\comps\webservice\classes;   
    use \sys\classes\webservice\WsConfigXml;
    /**
     * Abstração da Lobrary WSDLCreator para geração de WSDL
     */
    abstract class Wsdl extends WsConfigXml {
                
        private $objWsdl;
        
        /**
         * Gera o WSDL com os métodos da classe informada.
         *          
         * @param string $uri URI onde se encontra o serviço
         * 
         * @throws Exception
         */
        public function wsdlGenerate($wsInterfaceClass){
            try{
                //Aramazena URI
                $this->loadConfig('server');
                $wsdl       = $this->wsdl;  
                $wsdlName   = $this->wsdlName;                
                $uri        = $wsdl . '/'.strtolower($wsInterfaceClass).'/wsdl';
                $uri        = str_replace('//','/',$uri);
                $this->uri  = $uri;

                //Trata caminhos de inclusão
                $path      = preg_replace("/(sys)(.*)/", "", __DIR__);
                $pathWsdl  = $path . "sys/lib/comps/webservice/src/wsdlcreator/WSDLCreator.php";
               
                //Inclui bibilioteca WSDLCreator
                include($pathWsdl);
                
                //Instancia a classe WSDLCreator e inicia funções primárias
                $this->objWsdl = new \WSDLCreator($wsdlName, $this->uri);
                
                $this->objWsdl->setClassesGeneralURL($this->uri); //Seta URL do serviço
            }catch(Exception $e){
                throw $e;
            }   
        }
        
        public function addFile($file, $className){
            try{           
                //Adiciona Arquivo               
                $this->objWsdl->addFile($file); //Adiciona classe ao WSDL
                $this->objWsdl->addURLToClass(ucfirst($className), $this->uri); //Seta url da classe
            }catch(Exception $e){
                throw $e;
            }
        }
        
        /**
         * Adiciona um método para ser ignorado no WSDL
         * 
         * @param string $metodo nome do método (dentro do Controller setado no construct)
         * 
         * @throws Exception
         */
        public function addIgnore($className, $metodo){
            try{
                //Adiciona método a ser ignorado
                $this->objWsdl->ignoreMethod(array(ucfirst($className) => $metodo));
            }catch(Exception $e){
                throw $e;
            }
        }
        
        /**
         * Constroi o arquivo WSDL final e imprime a saída do mesmo
         * 
         * @throws Exception
         */
        public function showWsdl(){
            try{
                //Cria WSDL
                $this->objWsdl->createWSDL();
                //Imprime Saída
                $this->objWsdl->printWSDL(true);
            }catch(Exception $e){
                throw $e;
            }
        }
    }
?>
