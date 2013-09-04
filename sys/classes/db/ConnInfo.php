<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConnInfo
 *
 * @author Supervip
 */
class ConnInfo implements IConnInfo {
        
        private $host   = 'localhost';
        private $db;
        private $user   = 'root';
        private $passwd = 'root';
        
        function setHost($host){
            $this->host = $host;
        }
        
        function getHost(){
            return $this->host;
        }
        
        
        function setDb($db){
            $this->db = $db;
        }
        
        function getDb(){
            return $this->db;
        }
        
        function setUser($user){
            $this->user = $user;
        }
        
        function getUser(){
            return $this->user;
        }
        
        function setPasswd($passwd){
            $this->passwd = $passwd;
        }
        
        function getPasswd(){
            return $this->passwd;
        }
}

?>
