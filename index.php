<?php

    session_start();          
    include('sys/vendors/benchmark/Benchmark.php');  
    include('sys/classes/_init/Application.php');                
    
    try {
        //benchmark::run();
        
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
