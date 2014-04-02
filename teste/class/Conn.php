<?php

    class Conn {
        
        function __construct(){
            try {
                DB::$error_handler = false; // since we're catching errors, don't need error handler
                DB::$throw_exception_on_error = true;        
                DB::$user = 'supervip27';
                DB::$password = 'senha3040';
                DB::$dbName = 'supervip27';
                DB::$host = '186.202.152.137'; //defaults to localhost if omitted        
                DB::$encoding = 'utf8'; // defaults to latin1 if omitted    

            } catch(MeekroDBException $e) {
                echo "Error: " . $e->getMessage() . "<br>\n"; // something about duplicate keys
                echo "SQL Query: " . $e->getQuery() . "<br>\n"; // INSERT INTO accounts...        
                die();
            }        
        }
        
        public static function replace($table,$arrDados){
            try {
                DB::replace($table, $arrDados); 
                $id = DB::insertId();
                return $id;
            } catch(MeekroDBException $e) {
              echo "Error: " . $e->getMessage() . "<br>\n"; // something about duplicate keys
              echo "SQL Query: " . $e->getQuery() . "<br>\n"; // INSERT INTO accounts...
            }                
        }
        
        public static function query($sql){
            $result = null;
            if (strlen($sql) > 0) {
                $result = DB::query($sql);
            }
            return $result;
        }
    }
?>
