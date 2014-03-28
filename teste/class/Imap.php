<?php

    class Imap {
        
        function __construct($server,$port,$login,$password) {
            try {
                $conn = imap_open("{{$server}:{$port}/pop3/novalidate-cert}INBOX", $login, $password)
                     or die('Não foi possível estabelecer conexão com o servidor iMap: '.imap_last_error());               
            } catch (Exception $e) {
                throw new Exception('Servidor de e-mail não disponível.');
            }            
        }
    }
?>
