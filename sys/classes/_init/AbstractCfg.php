<?php

require_once('sys/classes/util/Xml.php');  

/**
 * Classe abstrata herdada pelas classes responsáveis pela leitura de arquivos de 
 * configuração XML.
 * 
 */
abstract class AbstractCfg extends sys\classes\util\Xml {

        protected $pathXml          = '';                
        private $arrAtribId         = NULL;
        private $nodesParam         = NULL;
        private $prefixoSessionVar  = 'SVIP_CFG_';
        
        function __construct($xmlFile,$arrAtribId, $prefixoSessionVar=''){                        
            $pathXml            = 'cfg/'.$xmlFile;                        
            
            /*
             * Carrega um array com os IDs permitidos no XML informado
             */
            $this->setAtribId($arrAtribId);
            
            if (strlen($prefixoSessionVar) > 0) {
                //Um prefixo para definir o nome das variáveis SESSION foi informado. Substitui o atual.
                $this->prefixoSessionVar = $prefixoSessionVar;            
            }

            /*
             * Valida o XML informado e retorna o nó de itens <param id=''>...</param>
             */
            $nodesParam = $this->loadCfgXml($pathXml);  
            
            if (is_object($nodesParam)) {
                /*
                 * Armazena os valores lidos em variável SESSION
                 */
                $this->persistParams();
            }
        }          
        
        /**
         * Método responsável por definir um arrray contendo os valores aceitos
         * nos atributos dos nós XML <param> lidos a partir do arquivo informado no construtor.
         * 
         * @param String[] $arrAtribId Array unidimensional array(item1, item2, ...)
         * @return void
         * 
         * @throws \Exception Caso o valor informado não seja um array
         */
        protected function setAtribId($arrAtribId){
            if (is_array($arrAtribId)) {
                $this->arrAtribId = $arrAtribId;
            } else {
                $msgErr = "Os atributos do arquivo XML não foram informados.";
                throw new \Exception($msgErr);
            }
        }
                
        protected function getArrAtribId(){
            return $this->arrAtribId;
        }        


        /**
         * Verifica e valida o path do arquivo XML informado.
         * Define o parâmetro nodesParam da classe atual.
         * 
         * O path é um caminho relativo que por padrão deve estar no formato 
         * 'cfg/filename.xml'.
         * 
         * @param string $pathXml Formato cfg/filename.xml
         * @return Xml Objeto XML com os nós encontrados.
         * 
         * @throws \Exception Caso o arquivo informado não seja localizado.
         * @throws \Exception Caso o arquivo informado não possua a extensão .xml.
         * @throws \Exception Caso ocorra um erro ao ler/carregar os nós do arquivo XML informado.
         * @throws \Exception Caso o arquivo XML informado não possua tags <param>
         */
        private function loadCfgXml($pathXml){
            $msgErr = '';                       
            if (file_exists($pathXml)) { 
                //O arquivo informado existe.
                $this->pathXml  = $pathXml;
                $arrPath        = pathinfo($pathXml);//Quebra as partes do nome do arquivo                
                $extension      = $arrPath['extension'];
                
                if ($extension == 'xml') { 
                    //Trata-se de um arquivo XML                    
                    $objXml = self::loadXml($pathXml);  
                    if (is_object($objXml)) {
                        $nodesParam   = $objXml->params->param;
                        $numParams    = count($nodesParam); 
                        if ($numParams > 0) {
                            $this->nodesParam = $nodesParam;
                            return $nodesParam;
                        } else {
                            $msgErr = 'Não há itens de configuração no arquivo '.$pathXml.'.'; 
                        }
                    } else {                
                        $msgErr = 'Impossível ler o arquivo '.$pathXml.'.';                                            
                    }
                } else {
                   $msgErr = 'O arquivo '.$pathXml.' parece não ser um arquivo XML';                                                                 
                }
            } else {                
                $msgErr = "Arquivo {$pathXml} não foi localizado.";                
            }                        
            if (strlen($msgErr)) throw new \Exception( $msgErr );    
        }                        
        
        /**
         * Lê cada nó XML do arquivo informado a partir da lista de IDs permitidos
         * e persiste os valores em variáveis SESSION. 
         * 
         * O array $arrAtribId contém os IDs fornecidos pela classe-filha.
         * @return void
         */
        private function persistParams(){
            $nodesParam = $this->getNodesParam();
            $arrAtribId = $this->getArrAtribId();//Atributos permitidos para o arquivo XML informado.
            if (is_object($nodesParam) && is_array($arrAtribId)) {
                foreach($arrAtribId as $id) {
                    //Para cada id encontra o valor contido entre as tags <param>...</param>
                    $value = self::valueForAttrib($nodesParam,'id',$id);                    
                    $this->setSessionVar($id,$value);//Persiste o valor encontrado em Session
               }             
            } elseif (is_array($arrAtribId)) {
                //O objeto não existe. Limpa as variáveis do objeto atual, se houver.
                foreach($arrAtribId as $id) {                         
                    $this->setSessionVar($id,'');
               }                 
            }              
        } 
        
        /**
         * Retorna o objeto XML criado no método loadCfgXml().
         * @return XML
         */
        function getNodesParam(){
            return $this->nodesParam;
        }        
        
        /**
         * Persiste o valor informado em uma variável SESSION.
         * O nome da variável é formado pelo prefixo definido em $prefixoSessionVar + $id.
         * O prefixo padrão pode ser alterado na chamada do construtor da classe atual.
         * 
         * @param String $id
         * @param mixed $value
         * @return void
         */
        private function setSessionVar($id,$value){
           $varName             = $this->prefixoSessionVar.$id;  
           $value               = str_replace('./','',$value);
           $_SESSION[$varName]  = $value;           
        }  
        
        /**
         * Recupera o valor de uma variável gravada em SESSION a partir de setSessionVar();
         * 
         * @param String $id
         * @return String
         */
        private function getSessionVar($id){
            $varName    = $this->prefixoSessionVar.$id; 
            $value      = (isset($_SESSION[$varName])) ? $_SESSION[$varName] : '';
            return $value;
        }
        
        /**
         * O método atual deve ser sobrescrito na classe-filha.
         * Permite recuperar um valor de variável a partir de um método estático.
         * 
         * Exemplo:
         * <code>
         *  echo CfgApp::get('rootFolder');
         * </code>
         * 
         * @param string $id Atributo id da tag <param> cujo valor se deseja recuperar.
         * 
         */
        public static function get($id){
            /*
             * Este método deve ser sobrescrito na classe-filha. Basta copiá-lo
             * para a classe-filha e descomentar a linha abaixo:
             */            
            
            //return self::getValueForId($id, get_class());   
        }
        
        /**
         * Permtie acessar um atributo do arquivo atual pelo seu id, a partir 
         * de um método estático.
         * 
         * @param string $id Valor do atributo id da tag <param id=''>...</param>
         * @return string
         */
        public static function getValueForId($id, $childClass){
            $objCfg = new $childClass;
            return $objCfg->$id;
        }                
          
        
        function __get($name) {
            $arrAtribId = $this->getArrAtribId();
            $key        = array_search($name,$arrAtribId);
            if ($key !== FALSE) {
                return $this->getSessionVar($name);
            } else {
                $msgErr = "A variável {$name} não é um id conhecido para o arquivo de configuração {$this->pathXml}.";
                throw new \Exception( $msgErr );
            }
        }

}
?>
