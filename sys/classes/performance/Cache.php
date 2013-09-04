<?php
    
    namespace sys\classes\performance;  
    
    /**
     * Classe usada para fazer o cache de objetos e strings no servidor.
     * Utiliza a classe Memcache() do PHP.
     * 
     * IMPORTANTE: Memcache deve estar habilitado no PHP.
     * 
     * Exemplo de uso:
     * 
     * <code>     
     *  $cacheName  = 'cacheDeObjTest';
     *  $objCache   = new Cache($cacheName);
     *  $objTest    = $objCache->getCache();
     *  if (!$objTest) {
     *      //Objeto ainda não armazenado em Cache. Gera o objeto e guarda em cache.
     *      $objTest = new stdClass();
     *      $objCache->setMin(5); //O cache irá durar 5 minutos.
     *      $objCache->setCache($objTest);
     *  }
     * 
     *  //Para excluir o objTest do cache:
     *  $objCache->delete();
     * </code>
     */
    class Cache {
            private $memcache = NULL;
            private $version;
            private $timeSec;
            private $nameCache;
            
           private function __construct($nameCache='',$memcache){                
                $this->version  = $memcache->getVersion();                   								
                $this->memcache = $memcache;                
                
                $this->setNameCache($nameCache);                
            }
            
            public static function newCache($nameCache){
                $objCache = NULL;                
                if (extension_loaded('memcache')) {    
                    $memcache = new \Memcache();  
                    if (!@$memcache->connect('localhost', 11211)){
                        //Não foi possível estabelecer uma conexão com o Memcache                        
                        $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_MEMCACHE_CONN'); 
                        throw new \Exception( $msgErr );                         
                    } else {
                        //Conexão com cache Ok                        
                        $objCache = new Cache($nameCache,$memcache);                                
                    }
                } else {
                    //$msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'MEMCACHE_EXTENSION_NOT_EXISTS'); 
                    //throw new \Exception( $msgErr );                            
                }                
                return $objCache;
            }
            
            function setNameCache($nameCache) {
                $this->nameCache = $nameCache;                
            }
            
            function getVersion(){
                return $this->version;
            }

            /**
             * Guarda uma variável em cache.
             *              
             * @param string $content Conteúdo a ser guardado em cache (string ou objeto)             
             * @return void
             * @throws \Exception Caso um erro ocorra ao guardar o contéudo informado em cache.
             */
            function setCache($content){			
                $nameCache = $this->nameCache;               
                if (strlen($nameCache) == 0) {
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_NAME_CACHE'); 
                    throw new \Exception( $msgErr );                                                   		                                        
                }
                
                if (!$this->memcache->set($nameCache,$content, false, $this->timeSec)){
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_SAVE_CACHE'); 
                    throw new \Exception( $msgErr );                                                   		
                }                
            }

            
            /**
             * Captura o valor de uma variável armazenada em cache.
             *
             * @return mixed Retorna o valor da variável ou FALSE caso a variável não exista em cache.
             */
            function getCache(){	
                $nameCache  = $this->nameCache;
                $cache      = (strlen($nameCache) > 0)?$this->memcache->get($nameCache):'';			
                return $cache;
            }
            
            /**
             * Exclui uma variável do cache.
             * 
             * @return void
             */
            function delete(){
                $nameCache = $this->nameCache;
                $this->memcache->delete($nameCache);						
            }           

            /**
             * Define o tempo de vida do cache em dias.
             * 
             * @param integer $time Valor numérico que representa dias.
             * @return void
             */
            function setDay($time){
                 $this->setTime('DAY', $time);    
            }

            /**
             * Define o tempo de vida do cache em horas.
             * 
             * @param integer $time Valor numérico que representa horas.
             * @return void
             */            
            function setHour($time){
                $this->setTime('HOUR', $time);     
            }

            /**
             * Define o tempo de vida do cache em minutos.
             * 
             * @param integer $time Valor numérico que representa minutos.
             * @return void
             */            
            function setMin($time){
                $this->setTime('MIN', $time);                
            }

            /**
             * Define o tempo de vida do cache em segundos.
             * 
             * @param integer $time Valor numérico que representa segundos.
             * @return void
             */            
           function setSec($time){
               $this->setTime('SEC', $time);
            }
            
            /**
             * Método de suporte para setDay(), setHour(), setMin e setSec().
             * Define o tempo de vida do cache convertendo o valor informado em segundos.
             * 
             * @param string $period Pode ser DAY, HOUR, MIN ou SEC.
             * @param integer $time Tempo a ser convertido em segundos de acordo com o período informado em $period.
             * @return void
             */
            function setTime($period,$time){
                $sec        = 0;
                $time       = (int)$time;
                $period     = strtoupper($period);
                $arrPeriod  = array('DAY','HOUR','MIN','SEC');
                $key        = array_search($period,$arrPeriod);
                
                if ($key !== FALSE && $time > 0) {
                    switch ($period){
                        case 'DAY':
                            $sec = $time*24*60*60;
                            break;
                        case 'HOUR':
                            $sec = $time*60*60; 
                            break;
                        case 'MIN':
                            $sec = $time*60; 
                            break; 
                       default:
                            $sec = $time;                      
                    }
                    $this->timeSec = $sec;                    
                } else {
                    //Erro ao definir um período de tempo para o cache
                    $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'ERR_TIME_CACHE');                     
                    throw new \Exception( $msgErr );                         
                }                
            }

            /**
             * Limpa todo o conteúdo armazenado no Memcached.
             */
            function flush(){
                $this->memcache->flush();			
            }		
    }
?>
