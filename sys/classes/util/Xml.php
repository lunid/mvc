<?php

    namespace sys\classes\util;
    
    abstract class Xml {
        protected $objXml = NULL;        
        private static $ExceptionFile = 'Xml.xml';
        
        public static function loadXml($pathXml=''){
            if (isset($pathXml)){
                if (!file_exists($pathXml)) {      
                    die('LoadXml(): Arquivo '.$pathXml.' não localizado.');
                } else { 
                    $contentFile = file_get_contents($pathXml);
                    if (strlen($contentFile) > 0) {
                        return simplexml_load_file($pathXml);                                        
                    } else {
                        die('LoadXml(): O arquivo '.$pathXml.' está vazio.');
                    }
                }
            }
        }      
        
        /**
         * Retorna o valor contido em um atributo do nó informado.
         * 
         * Exemplo para o arquivo XML abaixo:
         * <root>
         *      <config id='item1'>...</config>
         *      <config id='item2'>...</config>
         * </root>
         * 
         * <code>
         *  $objXml = new Xml();
         *  //Localiza os dados de uma tag <config> a partir de seu id:
         *  foreach($nodesServer as $node){
         *      $id = $objXml->getAttrib($node,'id');
         *      if ($id == 'item2') {
         *          echo 'Item2 localizado.';
         *          break;
         *      }         
         *  }
         * </code>
         * 
         * @param simplexml $nodes Nó XML a ser lido.
         * @param string $attrib Nome do atributo a ser lido.
         * @return string. 
         */
        public static function getAttrib($nodes,$attrib){
           $value = '';           
           if (get_class($nodes) == 'SimpleXMLElement' && $nodes->attributes() !== NULL) {
                $value = @(string)$nodes->attributes()->$attrib;
           }
           return $value;
        }
        
        public static function convertNode2Array($nodes){
            $arrNode = array();
            if (is_object($nodes) && $nodes->count() > 0) {            
                $i = 0;
                foreach($nodes as $node) {
                    $arrAttrib = $node->attributes();                    
                    $arrNode[$i]['attrib']    = $arrAttrib;
                    $arrNode[$i++]['value']   = (string)$node;                    
                }                       
            }          
            return $arrNode;
        }
        
        /**
         * Extrai os atributos e seus respectivos valores de um único nó XML.        
         * 
         * @param SimpleXMLElement $nodes
         * @return mixed[] Retorna um array associativo contendo o valor do atributo respectivo.
         */
        public static function getAttribsOneNode($node){        
            $arrAtrib = array();
            try {               
                if (is_object($node)) {  
                    foreach($node->attributes() as $name => $value){                    
                        $arrAtrib[$name] = (string)$value;
                    }                       
                }
            } catch(\Exception $e) {
                
            }
            return $arrAtrib;
        }
        
        /**
         * Localiza e retorna o contéudo de um nó XML a partir de um atributo específico.
         * 
         * Exemplo:
         * <code>
         *      Arquivo config.xml:
         *      <config>
         *          <header>
         *              <include id='css'>itemCss1, itemCss2...</include>
         *              <include id='js'>itemJs1, itemJs2...</include>
         *          </header>
         *      </config>
         * 
         *      Classe que extende XML:
         *      class Dic extends XML {
         *          ...
         *          function loadIncludeNode($pathXml,$attribName,$attribValue){
         *               $objXml    = self::loadXml($pathXml);
         *               $nodeValue = '';
         *               if (is_object($objXml)) {
         *                 $nodes      = $objXml->headers->include;
         *                 $numItens    = count($nodes);
         *
         *                 if ($numItens > 0){
         *                     $nodeValue  = self::valueForAttrib($nodes,$attribName,$attribValue);
         *                     echo $attribValue;//Retorna o conteúdo do nó cujo atributo id='css' 
         *                 } else {
         *                      //Nenhuma mensagem foi localizada no XML informado.
         *                      echo 'O arquivo '.$pathXml.' existe, porém o Xml Node com a mensagem '.$codMsg.' não foi localizado.';
         *                 }
         *              } else {
         *                  echo 'Impossível ler o arquivo '.$pathXml;
         *              }
         *              return $nodeValue;
         *          }                       
         *      }
         * 
         *      Carrega o arquivo config.xml e imprime o valor do nó <include> cujo id='css':
         *      echo Dic::loadIncludeNode('config.xml','id','css');         
         * </code>        
         * 
         * @param SimpleXMLElement $nodes
         * @param string $atribName Nome do atributo
         * @param string $atribValue Valor do atributo
         * @return string
         */
        public static function valueForAttrib($nodes,$atribName,$atribValue){        
            foreach($nodes as $node){     
                foreach($node->attributes() as $name => $value){                       
                    if ($name == $atribName && $value == $atribValue) return $node;                    
                }                
            }  
            
            $objE = new \ExceptionHandler(new \Exception, self::$ExceptionFile);            
            $objE->setCodeMessage('ATRIB_ERR')->replaceTagFor(array('ATRIB'=>$atribValue));
            $objE->render();                             
            throw $objE;              
        }
        
        public static function getNode($arrNodes,$node){            
            if ((is_object($arrNodes) || is_array($arrNodes)) && strlen($node) > 0){
                //Arquivo xml carregado com sucesso.  
                //foreach($arrNodes->$node as $node){
                   // print_r($node);
                //}
                $nodes = $arrNodes;                
                if (count($arrNodes->$node) == 1) $nodes = $arrNodes->$node; 
                //echo $nodes;
                return $nodes;
            } else {
                die("Não foi possível carregar um objeto XML para ".print_r($arrNodes));
            }            
        }                
    }
?>
