<?php

/**
 * Classe responsável por guardar/iniciar as configurações de conexão com mySql. 
 * @package api\db
 */
class Conn {
   
    /**
     * Inicia a conexão com o DB (utiliza o pattern Singleton).
     * O ambiente definido no arquivo index.php (dev | test | prod) determina qual conexão deve ser iniciada.
     * Os dados de conexão dependem do ambiente configurado em sys\classes\db\ConnConfig.php
     * 
     * Exemplo:
     * <code>
     *  Conn::init();
     * </code>
     * 
     * @params string $conn O nome de uma conexão (método existente em ConnConfig)
     * @return void
     */
    public static function init($conn=''){
       if (!defined('APPLICATION_ENV')) die('Impossível efetuar conexão como DB. Ambiente não definido.');
        
       $arrConn    = NULL;
       $ambiente   = (strlen($conn) > 0)?$conn:APPLICATION_ENV;                
        
       $objConn    = ConnConfig::$ambiente();       
       self::setConn($objConn);
    }
    
   private static function setConn($objConn){
        if ($objConn instanceOf IConnInfo) {             
            DB::$host       = $objConn->getHost();
            DB::$dbName     = $objConn->getDb();
            DB::$user       = $objConn->getUser();
            DB::$password   = $objConn->getPasswd();
            DB::$encoding   = 'utf8';        
        } else {
            die('Impossível fazer conexão com DB. O ambiente informado não é válido ou os dados de conexão informados estão em um formato diferente do esperado.');
        }        
    }
    
    private static function setConfigConnTest($host, $db, $user, $passwd){
        return self::setConfigConn('test', $host, $db, $user, $passwd);
    }
    
    private static function setConfigConnDev($host, $db, $user, $passwd){
        return self::setConfigConn('dev', $host, $db, $user, $passwd);
    }
    
    private static function setConfigConnProd($host, $db, $user, $passwd){
        return self::setConfigConn('prod', $host, $db, $user, $passwd);
    }
    
    private static function setConfigConn($env,$host,$db,$user,$passwd){
        $arrConn['env']      = $env;
        $arrConn['host']     = $host;
        $arrConn['db']       = $db;
        $arrConn['user']     = $user;
        $arrConn['passwd']   = $passwd;        
        return $arrConn;
    }
}
?>
