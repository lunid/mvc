<?php
    require_once('sys/classes/util/Xml.php');  
    
    /**
     * Classe usada para recuperar dados de arquivo de configuração XML.
     * Os arquivos de configuração devem possuir a mesma estrutura e ficar na pasta
     * cfg do projeto.
     */
    class Cfg extends sys\classes\util\Xml {
        private static $ExceptionFile = 'Cfg.xml';//Nome do arquivo com mensagens de Exception da classe atual
        
        
        /**
         * Converte a chamada de um método dinâmico no nome do arquivo xml de configuração.
         * O parâmetro informado refere-se ao atributo id da tag param cujo valor se deseja
         * capturar.
         * 
         * @param string $nameXmlFile Nome do arquivo xml guardado na pasta cfg.
         * @param string $args Valor do atributo id da tag param.
         * @return string
         * @throws \ExceptionHandler Caso o arquivo referente ao método chamado não exista.
         */
        public static function __callstatic($nameXmlFile, $args) {
            $pathXml = $_SERVER['DOCUMENT_ROOT'] . 'cfg/'.$nameXmlFile .'.xml';
           
            if (file_exists($pathXml)) {                
                $objXmlParams   = self::getObjXml($pathXml);
                $id             = $args[0];
                
                if (is_object($objXmlParams)) {                    
                    $value = self::valueForAttrib($objXmlParams,'id',$id);       
                }

                if ($id == 'baseUrl') {
                    //Certifica-se de incluir a barra normal (/) antes e depois do baseUrl.
                    $value  = trim($value, '/');//Retira as barras antes e depois caso existam, para evitar inserí-las em duplicidade.
                    if (strlen($value) == 0) $value = 'public_html';
                    $value  = "/$value/";
                }
                $value = trim($value);
                return $value;                
            } else {                
                $arrReplace = array('FILE'=>$pathXml);               
                $exception  = new \ExceptionHandler('FILE_NOT_EXISTS',new \Exception, $arrReplace);                       
                throw $exception;                    
            }
        }
        
        /**
         * Faz a leitura do arquivo XML a partir do path informado e converte em um
         * objeto XML contendo os nós PARAM.
         * 
         * @param string $pathXml Caminho físico do arquivo XML a ser lido.
         * @return XML
         * @throws \ExceptionHandler Caso o arquivo não possua uma ou mais tags PARAM ou ocorra erro no XML
         */
        private static function getObjXml($pathXml){                
            $codMessageErr      = '';
            $objXml             = self::loadXml($pathXml);  
            if (is_object($objXml)) {
                $nodesParam   = $objXml->params->param;
                $numParams    = count($nodesParam); 
                if ($numParams > 0) {
                    return $nodesParam;
                } else {
                    $codMessageErr = 'NAO_HA_PARAMS';                    
                }
            } else { 
                $codMessageErr = 'ERR_XML';                                                          
            }
            
            if (strlen($codMessageErr) > 0) {
                $objE = new \ExceptionHandler(self::$ExceptionFile);            
                $objE->setCodeMessage($codMessageErr)->replaceTagFor(array('FILE'=>$pathXml));
                $objE->setException(new \Exception)->render();                             
                throw $objE;             
            }
        }                                    
    }
?>
