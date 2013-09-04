<?php

    /**
    * Interface usada em classes de compactação de script.
    *
    * @author Claudio Rubens Silva Filho
    */
    namespace sys\classes\performance;
    interface IMinify {
        public static function minify($script);
    }
?>
