<?php

    /**
    * Interface para armazenar dados de acesso ao banco de dados.
    *
    * @author Claudio Rubens Silva Filho
    */
    interface IConnInfo {
        
        function setHost($host);
        function getHost();
        function setDb($db);
        function getDb();
        function setUser($user);
        function getUser();
        function setPasswd($passwd);
        function getPasswd();
    }

?>
