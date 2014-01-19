<?php

    class ContainerSys {
        private $container;
        
        function __construct(){
            $container = new Pimple();
        }
    }
?>
