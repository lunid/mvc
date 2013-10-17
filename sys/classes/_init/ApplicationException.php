<?php

    class ApplicationException extends \Exception {
        
        public function __construct($message, $code = 0) {
            parent::__construct($message, $code);
            $this->sendNotifications();
            $this->logError();            
        }
    }
?>
