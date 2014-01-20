<?php

    session_start();    
    
    $rootFolder = '';
    define("ROOT_FOLDER", $rootFolder);      
    
    include('sys/classes/_init/Application.php');
                 
    
    try {
                
        //Inicializa a aplicação:
        Application::setup();        
        Application::environmentSetup();//Desenvolvimento, produção, teste etc.
             
    } catch(Exception $e) {         
       $msgErr = "Infelizmente não foi possível completar sua requisição:<br/>".$e->getMessage()."<br/>";
       $msgErr .= "Origem: ".$e->getFile()."<br/>";
       $msgErr .= "Linha: ".$e->getLine()."<br/>";
       echo utf8_decode($msgErr);
       die();
    }
?>
