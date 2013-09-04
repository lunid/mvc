<?php

    /**
    * Classe de configuração de acesso ao banco de dados.
    *
    * @author Supervip
    */
    class ConnConfig {        
        /**
        * Define as configurações do DB no ambiente de testes.
        * Ambiente de testes: servidor remoto compartilhado com a equipe de desenvolvimento.
        * 
        * @return ConnInfo
        * @see dev
        * @see prod
        */
        public static function test(){ 
            $objConnInfo = new ConnInfo();  
            $objConnInfo->setHost('mysql09.supervip.com.br');
            $objConnInfo->setDb('supervip27');
            $objConnInfo->setUser('supervip27');
            $objConnInfo->setPasswd('senha3040');
            return $objConnInfo;                 
        }

        /**
        * Define as configurações do DB no ambiente local de desenvolmento.
        * Computador do desenvolvedor.
        * 
        * @return object ConnInfo
        * @see test
        * @see prod
        */    
        public static function dev(){            
            return self::test();//Ambiente de desenvolvimento com mesma configuração do ambiente de teste.
        }    

        /**
        * Define as configurações do DB no ambiente de produção.
        * Ambiente utlizado pelos clientes.
        * 
        * @return object ConnInfo
        * @see dev
        * @see test
        */
        public static function prod(){   
            $objConnInfo = new ConnInfo();   
            $objConnInfo->setHost('');
            $objConnInfo->setDb('');
            $objConnInfo->setUser('');
            $objConnInfo->setPasswd('');
            return $objConnInfo;    
        }    
    }

?>
