<?php

    namespace site\classes\controllers;
  
    class SiteController extends \ExceptionController {
        /**
         * Função para redirecionar o usuário a login temporariamente.
         */
        function before(){
            
            $host = $_SERVER['HTTP_HOST'];
            \Cfg::app('defaultTemplate');
            
            if ($host != 'dev.interbits.com') {
                //Está em ambiente de produção ou ambiente diferente de teste.
                //header("Location: /login");
                //die;
            }
        }        
    }
?>
