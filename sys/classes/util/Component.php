<?php

/**
 * Classe responsável por inicializar um componente.
 * O endereço padrão para armazenar componentes é sys/lib/.
 * 
 * Exemplo de uso 1 - Inicializando um novo objeto Component:
 * <code>
 *      $objComp = new Component();
 *      //O componente atual aceita 3 parâmetros:
 *      $objComp->fileNameMin    = 'index';
 *      $objComp->extension      = 'js';
 *      $objComp->string         = 'Texto de exemplo';
 * 
 *      //Inicializa o objeto e guarda o retorno na variável $out:
 *      $out = $objComp->yuiCompressor();
 * </code>
 *
 * Exemplo de uso 2 - Chamando um método estático na classe Component:
 * <code>
 *      $arrParams['fileNameMin']   = 'index';
 *      $arrParams['extension']     = 'js';
 *      $arrParams['string']        = 'Texto de exemplo';
 * 
 *      //Inicializa o objeto e guarda o retorno na variável $out:
 *      $out = Component::yuiCompressor($arrParams);
 * </code> 
 * @author Supervip
 */
namespace sys\classes\util;
use \sys\classes\performance\Cache;

class Component {
    
    private $arrParams = array();//Array associativo para armazenar parâmetros na inicialização do componente.
    
    function __construct() {        
    }
    
    function __set($name, $value) {
        $this->arrParams[$name] = $value;
    }
    
    //function __call($folder,$args=NULL){
     //   $arrParams  = (!empty($args))?$args:$this->arrParams;
    //    $args[0]    = $arrParams;
    //    return Component::factory($folder,$args);
    //}
    
    /**
     * Inicializa o componente solicitado.
     * 
     * Exemplo que mostra a utilização do componente YuiCompressor:
     * <code>     
     *  $arrParams['string']        = 'Texto de exemplo';
     *  $arrParams['extension']     = 'js';
     *  $arrParams['fileNameMin']   = 'assets/app/js/_teste_min.js';
     * 
     *  //O nome do método estático deve ser a pasta onde se encontra o componente em sys/lib/.
     *  if (Component::yuiCompressor($arrParams)){
     *      echo 'Arquivo gerado com sucesso.';     
     *  } else {
     *      echo 'Erro ao comprimir o arquivo';
     *  }
     * </code>
     * 
     * @return mixed
     */
    public static function __callStatic($folder,$args=array()){
        return self::factory($folder,$args);
    }
    
    /**
     * Fábrica de objetos de componentes.
     * 
     * IMPORTANTE:
     * Caso o módulo Memcache esteja ativo (php.ini) o objeto será guardado em cache.
     * O tempo de vida padrão estipulado para o cache é de 30 dias.
     * 
     * @param string $folder Pasta que contém o componente a ser inicializado.
     * @param mixed[] $args Parâmetros usados no construtor do novo objeto
     * @return mixed 
     * @throws \Exception Caso a classe do componente não tenha sido localizada.
     */
    private static function factory($folder,$args=array()){
                
        /*
         * Retorna o path da classe padrão do componente solicitado.
         * Por exemplo, se $folder = 'webservice' a URL será:         
         * {$folderSys}/{$folderLib}/{$folderComps}/{$folderComponent}/classes/Lib{$class}.php
         * 
         * As pastas que compõe a URL estão configuradas em config.xml.
         */
        $classPath = \Url::getUrlLibInitComponent($folder);
     
        if (file_exists($classPath)){
            include_once($classPath);
            $class      = ucfirst($folder);
            $cacheName  = $folder.'_'.$class;
            $objCache   = Cache::newCache($cacheName);
            $objComp    = NULL;
            
            if (is_object($objCache)) {
                //Utiliza cache:
                $objComp = $objCache->getCache();                
                if (!is_object($objComp) || 1==1) {
                    $objComp = new $class($folder,$args);
                    $objCache->setDay(30);//30 dias de cache
                    $objCache->setCache($objComp);
                } else {
                    //O objeto já está em cache. 
                    //Guarda o array de parâmetros usados no método init().
                    $objComp->setArgs($args);                
                }                                
            } else {
                //Não utiliza cache:
               $objComp = new $class($folder,$args); 
            }         
            
            $out = NULL;
            if ($objComp instanceof \sys\lib\classes\IComponent) {               
                $objComp->init();           
                $out = $objComp->getReturn();
            }
            return $out;
        } else {
            //Arquivo não existe
            $msgErr = Dic::loadMsg(__CLASS__,__METHOD__,__NAMESPACE__,'FILE_NOT_FOUND');
            $msgErr = str_replace('{FILE}',$classPath,$msgErr);
            $msgErr = str_replace('{COMP}',$folder,$msgErr);
            throw new \Exception( $msgErr );                                    
        }
    }
}

?>
