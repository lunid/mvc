<?php
    
    namespace sys\classes\db;
    
    class ErrorDb {
        public static function static_error_handler($params) {
          echo "Error: " . $params['error'] . "<br>\n";
          echo "Query: " . $params['query'] . "<br>\n";
          die; // don't want to keep going if a query broke
        }

        public function error_handler($params) {
          echo "Error: " . $params['error'] . "<br>\n";
          echo "Query: " . $params['query'] . "<br>\n";
          die; // don't want to keep going if a query broke
        }
    }
?>
