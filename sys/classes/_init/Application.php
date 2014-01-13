<?php
    
    //Vendors
    require_once('sys/vendors/di/Pimple.php');//Dependency Injection
    
    //Conexão DB
    require_once('sys/classes/db/Meekrodb_2_2.php');
    require_once('sys/classes/db/IConnInfo.php');
    require_once('sys/classes/db/ConnInfo.php');
    require_once('sys/classes/db/ConnConfig.php');
    require_once('sys/classes/db/Conn.php');
    require_once('sys/classes/db/ORM.php');
    require_once('sys/classes/db/Table.php');
    
    require_once('sys/classes/util/Uri.php');      
    require_once('sys/classes/util/Url.php');  
    require_once('sys/classes/util/Session.php');
    
    require_once('sys/classes/security/Token.php');
    require_once('sys/classes/security/Auth.php');         

    //MVC
    require_once('sys/classes/global/mvc/Controller.php');
    require_once('sys/classes/global/mvc/View.php');
    require_once('sys/classes/global/mvc/ExceptionController.php');
    
    //Classes globais (disponíveis para toda a aplicação)
    require_once('sys/classes/global/DIContainer.php');
    require_once('sys/classes/global/DIContainerController.php');
    require_once('sys/classes/global/Replace.php');
    require_once('sys/classes/global/Cfg.php');
    require_once('sys/classes/global/CfgApp.php');
    require_once('sys/classes/global/ErrorHandler.php');
    require_once('sys/classes/global/ExceptionHandler.php');
    require_once('sys/classes/global/DicionaryXml.php');      
    
    //Vendors
    require_once('sys/vendors/errorTrack/class.errorTalk.php');         
    require_once('sys/vendors/di/DI.php');   
    
    use sys\classes\util\DI;
    use sys\classes\mvc as MVC;
    use sys\classes\util as UTIL;
    
    class Application {
       /**
       * Classe de inicialização da aplicação.
       * Identifica o módulo, controller e action a partir da URL e faz a chamada
       * do método, como segue:
       * 
       * $objController  = new $controller;
       * $objController->method()
       * 
       * O $method deve iniciar sempre com o prefixo 'action' seguido do parâmetro
       * $action com inicial maiúscula.
       * 
       * Exemplo:
       * Para $action='faleConosco' a variável $method será 
       * actionFaleConosco().                         
       *  
       */                    
        
        public static function setup(){                                
        
            //Captura erros em tempo de execução e trata como Exception
            //set_error_handler("self::exceptionErrorHandler");                       
                        
            
            //$msgErr = Error::eApp('LOGIN');     
            //throw new \Exception($msgErr);
            
            //Faz a leitura dos parâmetros em cfg/app.xml na raíz do site 
            $container      = new DIContainer();
            $baseUrl        = CfgApp::get('baseUrl');            
            $objUri         = $container->Uri();
            $objMvcParts    = $objUri->getMvcParts();              
            
            //Inicializa tratamento de erro para o projeto atual.
            errorTalk::initialize();   
            errorTalk::$conf['logFilePath'] = "data/log/erroTalkLogFile.txt";
            errorTalk::errorTalk_Open(); // run error talk object
            //echo $test; // Run-time notices (Undefined variable: test)               
             
            $module         = $objMvcParts->module;
            $controller     = $objMvcParts->controller;
            $action         = $objMvcParts->action;            
            $method         = 'action'.ucfirst($action);                        
           
            //Carrega, a partir do namespace, classes invocadas na aplicação.
            spl_autoload_register('self::loadClass');	                          
            
            //Faz o include do Controller atual
            $DI                 = 'DI'.ucfirst($module);
            $DIClass            = $DI.'.php';
            $urlFileController  = $module . '/classes/controllers/'.ucfirst($controller).'Controller.php';
            $urlDI              = $module .'/classes/'.$DIClass;
            
            if (!file_exists($urlFileController)) {                
                $msgErr = "Arquivo de inclusão ".$urlFileController." não localizado, 
                    ou então o módulo solicitado não foi informado no item \'modules\' do arquivo global config.xml";
                throw new \Exception( $msgErr );  
            } elseif (!file_exists($urlDI)) {
                $msgErr = "O arquivo a ser usado como container do módulo, ".$urlDI.", não foi localizado. 
                    Este arquivo é obrigatório para o desenvolvimento com injeção de dependência.";
                throw new \Exception( $msgErr );                  
            }else{
                require_once($urlDI);                
                require_once($urlFileController);
            }
            
            $controller     .= 'Controller';
            $objContainer   = new $DI();
            $objController  = $objContainer->$controller('Claudio');
            
            if (!method_exists($objController,$method)) throw new \Exception ('Método '.$controller.'->'.$method.'() não existe.');
            if (method_exists($objController, 'before')) {
                try {
                    $objController->before();//Executa o método before(), caso esteja implementado.
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            try {
                $objController->$method();//Executa o Controller->method()            
            } catch(\Exception $e) {                         
                throw $e;
            }
        }  
        
        /**
        * Localiza a classe solicitada de acordo com o seu namespace e faz o include do arquivo.
        * @param String $class (nome da classe requisitada).
        * return void
        */             
        public static function loadClass($class){   
            //Tratamento para utilização do Hybridauth.
            if($class == 'FacebookApiException') return false; 
            $class  = rtrim($class,'.php');
            $urlInc = str_replace("\\", "/" , $class . '.php');                           
            $urlInc = $_SERVER['DOCUMENT_ROOT'] . $urlInc;            
            
            if (isset($class) && file_exists($urlInc)){          
                require_once($urlInc);           
            } else {      
                throw new \Exception("Classe $class não encontrada ({$urlInc})");
            }                      
        }     
        

        public static function listFiles($dir = NULL, $path = NULL){
            $baseUrl    = CfgApp::get('baseUrl');
            $dir        = $baseUrl;
            if ($dir !== NULL) {
                $dir = $baseUrl.trim($dir,'/').'/';            
            }

            // Array para guardar os arquivos
            $arrFound = array();         
            if (is_dir($dir)) {
                // Cria um novo directory iterator
                $dirIterator = new DirectoryIterator($dir);
                foreach ($dir as $file) {
                    // Pega o nome do arquivo
                    $filename = $file->getFilename();
                    if ($filename[0] === '.' OR $filename[strlen($filename)-1] === '~') {
                        // Ignora arquivos ocultos e arquivos de backup do UNIX
                        continue;
                    }

                    // Relative filename is the array key
                    $key = $dir.$filename;

                    if ($file->isDir()) {
                        if ($subDir = self::listFiles($key, $paths)) {
                            if (isset($found[$key])) {
                                // Faz um append à lista de sub-diretórios.
                                $arrFound[$key] += $subDir;
                            } else {
                                // Cria uma nova lista de sub-diretórios.
                                $arrFound[$key] = $subDir;
                            }
                        }
                    } else {
                        if (!isset($arrFound[$key])) {
                            // Adiciona novos arquivos para a lista.
                            $arrFound[$key] = realpath($file->getPathName());
                        }
                    }
                }
            }
            
            // Ordena os resultados alfabeticamente.
            ksort($arrFound);
            return $arrFound;
        }        
                
        
        public static function setDefaultConnDb($defaultConnDb){            
            if (!defined('APPLICATION_DEFAULT_CONN_DB')) {                             
                define ('APPLICATION_DEFAULT_CONN_DB', $defaultConnDb);
            }        
            
            /*
             * Inicializa a conexão com o DB.
             * Necessário para evitar erro de conexão ao executar o Controller->action().
             */            
            Conn::init();                        
        }                
        
        /**
         * Valida o template padrão da aplicação e cria um arquivo novo 
         * na pasta de templates caso ainda não exista.
         * 
         * @param string $pathTplFolder Path da pasta de templates
         * @return void
         */
        private static function vldTemplate($pathTplFolder){
            $cfgDefaultTemplate = LoadConfig::defaultTemplate();
            $pathFileTplDefault = $pathTplFolder.$cfgDefaultTemplate;            
            if (!file_exists($pathFileTplDefault)) {
                //Arquivo template não existe                
                if (!is_dir($pathTplFolder)) {
                    //Diretório de templates ainda não existe. Tenta criá-lo.                    
                    if (!mkdir($pathTplFolder, 0, true)) {
                        $msgErr = 'A tentativa de criar a pasta de templates em '.$pathTplFolder.' falhou.';
                        throw new \Exception( $msgErr );                           
                    }                   
                }   
                \LoadConfig::
                $date               = date('d/m/Y H:i:s'); 
                $pathFileTplDefault = str_replace('//','/',$pathFileTplDefault);
                $open               = fopen($pathFileTplDefault, "a+");

                //Conteúdo do novo arquivo template:
                $fileContent = "<!-- Arquivo criado dinâmicamente em LoadConfig.php, em {$date} -->".chr(13)."<div>{BODY}</div>";
                    
                if (fwrite($open, $fileContent) === false) {
                    $msgErr = "Um template padrão não foi definido no arquivo config.xml e a tentativa de 
                    gerar um novo arquivo ({$pathFileTplDefault}) falhou. Verifique a tag 
                    <fileName id='default'>nomeDoArquivoTemplate.html</fileName>";
                    $msgErr = htmlentities($msgErr);                     
                    throw new \Exception( $msgErr );                                                                  
                }
                fclose($open);                  
            }            
        }                                             
    }
?>
