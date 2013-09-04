<?php

namespace sys\classes\db;

class Resultset implements \Iterator {
    //put your code here
    private $arrObj = array();
    
    function __construct($arrObj){
        $this->save($arrObj);
    }
    
    private function save($arrObj){
        if (is_array($arrObj)) $this->arrObj = $arrObj;
    }
    
    function getRs(){
        return $this->arrObj;
    }
    
    function count(){
        $arrObj = $this->arrObj;
        return count($arrObj);
    }
    
    //Interator
    public function rewind(){
        reset($this->arrObj);
    }
  
    public function current(){
        $var = current($this->arrObj);
        return $var;
    }
  
    public function key(){
        $var = key($this->arrObj);
        return $var;
    }
  
    public function next(){
        $var = next($this->arrObj);
        return $var;
    }
  
    public function valid(){
        $key = key($this->arrObj);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}

?>
