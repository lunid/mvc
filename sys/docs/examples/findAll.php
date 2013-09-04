<?php

    

    highlight_string(

      '

        $objCliente = new Cliente();

        $results    = $objCliente->findAll(); // Retorna todos os registros da tabela.

        foreach($results as $obj){

            echo $obj->EMAIL.\'</br>\';//Objetos da classe Cliente

        };'

    );

?>

