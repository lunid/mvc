<?php

    namespace sys\classes\util;
    
    class Redirect extends Xml {
        private $pathXml;
        
        /**
         * Construtor que carrrega um arquivo XML contendo configurações de redirect de acordo com o perfil solicitado.
         * 
         * Para saber qual a estrutura do arquivo redirect.xml VEJA /modelo/redirect.xml.
         * 
         * @param type $xmlFile Nome do arquivo XML ou path relativo de um arquivo fora do módulo atual.
         * @throws \Exception Caso o arquivo XML informado não exista.
         */
        function __construct($xmlFile='redirect.xml'){           
            //Verifica o arquivo informado, concatenado no $rootFolder, existe.            
            $xmlFile            = APPLICATION_PATH.$xmlFile;
            $physicalPathXml    = \Url::physicalPath($xmlFile);
           
            if (!file_exists($physicalPathXml)) {
                //Path não existe. Verifica se o arquivo encontra-se na raíz do módulo atual.
                $module             = \Application::getModule();
                $pathXml            = $module.'/'.$xmlFile;
                $physicalPathXml    = \Url::physicalPath($pathXml);
                
                if (!file_exists($physicalPathXml)) {
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'XML_FILE_NOT_EXISTS');
                    $msgErr = str_replace('{FILE}',$xmlFile,$msgErr);
                    throw new \Exception( $msgErr );                      
                }
            }
            $this->pathXml = $physicalPathXml;
        }
        
        /**
         * Constrói o path a ser usado como redirect a partir do perfil solicitado.
         * 
         * EXEMPLO:
         * Supondo que, após checar o login e senha informados e localizar o perfil do usuário (PRO),
         * este deverá ser redirecionado para sua área restrita.
         * 
         * <code>
         *  $objRedirect    = new Redirect();
         *  $redirect       = $objRedirect->PRO;
         *  Header('Location:'.$redirect);
         * </code>
         * 
         * @param string $perfil Perfil cujo redirect deve ser localizado no XML.
         * @return string
         * @throws \Exception Caso o arquivo XML não tenha sido carregado corretamente.
         * @throws \Exception Caso, após a leitura do XML, um nó redirect não exista.
         */
        function __get($perfil){
            $path       = FALSE;
            $pathXml    = $this->pathXml;
            $objXml     = Xml::loadXml($pathXml);                        
            $rootFolder = \LoadConfig::rootFolder();
            if (strlen($rootFolder) > 0) $path = ($rootFolder != '' ? "/{$rootFolder}/" : "");
            if (is_object($objXml)) {            
                $nodesRoot      = $objXml->redirect;
                if (is_object($nodesRoot) && count($nodesRoot) > 0) {                     
                    foreach($nodesRoot as $node){
                        $nodePerfil = $this->getAttrib($node,'perfil'); 
                        if ($nodePerfil == $perfil) {
                            //Encontrou os dados de redirect do perfil atual                               
                            $url = $node->url;
                            if (strlen($url) == 0) {
                                $module     = $node->module; 
                                if (strlen($module) == 0) $module = \Application::getModule();
                                $controller = $node->controller;                            
                                $action     = $node->action;
                                $path       .= "{$module}/";
                                if (strlen($controller) > 0) $path .= "{$controller}/";
                                if (strlen($action) > 0) $path .= "{$action}/";                            
                            } else {
                                $path = $url;
                            }
                            break;
                        }
                    }
                } else {
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_XML_REDIRECT');    
                    $msgErr = str_replace('{FILE}',$pathXml,$msgErr);
                    throw new \Exception( $msgErr );                     
                }                
            } else {
                $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_LOAD_XML');    
                $msgErr = str_replace('{FILE}',$pathXml,$msgErr);
                throw new \Exception( $msgErr );                       
            }
            return $path;
        }
    }
?>
