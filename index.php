<?php

    session_start();
    
    /*
     * Reportar todos os erros
     */
    error_reporting(-1);
    $pathProject = '/';
    define("PATH_PROJECT", $pathProject);      
    
    include('sys/classes/_init/Application.php');
                 
    
    try {
                
        /**
         * Define o ambiente atual.
         * Esta ação é importante porque habilita/desabilita recursos exclusivos de cada ambiente.
         * Por exemplo, no ambiente de desenvolvimento, por padrão, todos os logs e avisos estão ativados.
         * 
         * Ambientes disponíveis:
         * test() Ambiente de testes.
         * prod() Ambiente de produção.
         */
        //Application::dev();//Indica que está no ambiente de desenvolvimento
        
        //Inicializa a aplicação:
        Application::setup();         
        Application::setDefaultConnDb('dev');         
    } catch(Exception $e) {         
       $msgErr = "Infelizmente não foi possível completar sua requisição:<br/>".$e->getMessage()."<br/>";
       $msgErr .= "Origem: ".$e->getFile()."<br/>";
       $msgErr .= "Linha: ".$e->getLine()."<br/>";
       echo utf8_decode($msgErr);
       die();
    }
?>
