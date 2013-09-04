<?php

    namespace sys\classes\html;
    
    /**
     * Permite gerar uma tabela HTML usando recursos de phtml.
     * 
     * Exemplo:
     * <code>
     *  //Define os parâmetros do combobox:
     *  $objParams               = new \stdClass();
     *  $objParams->id           = 'idMateria';
     *                     
     *  $obj = new Combobox($objParams);
     *  $obj->addOption('0','Matérias cadastradas');//Define o <option> inicial.
     *  $obj->selected(4); //Seleciona o item cujo valor é igual a 4 (pode ser tb uma lista de itens (ex: 4,7,6))          
     *  $obj->addOptions($rsMaterias);//Permite passar um array bidimensional para popular o combobox
     *  $cbx = $obj->render();//Gera o combobox
     *  echo $cbx;                
     * </code> 
     */    
    class Table extends Html {
         
        function __construct( $objParams = NULL){  
            
            //Informa o nome do arquivo phtml a ser usado na classe atual:
            $this->setHtml('table');
            
            //Define os parâmetros específicos da classe atual:
            $this->addParam('cellPadding');            
            $this->addParam('cellSpacing');            
            $this->addParam('border');            
            $this->addParam('width');
            $this->addParam('height');
            $this->addParam('columns');
            $this->addParam('rows');
            
            //Carrega as propriedades do objeto atual a partir de objParams:
            $this->popParams($objParams);            
        }          
        
        function setColumns($arrColumns){            
            if (is_array($arrColumns)) $this->columns = $arrColumns;            
        }
        
        function setColumn($column){
            if (strlen($column) > 0) {
                $columns = $this->columns;            
                $indice = (is_array($columns))?count($columns):0;                
                $columns[$indice] = $column;                
                $this->columns    = $columns;            
            }
        }
        
        public function setLayoutName($name){
            //Informa o nome do arquivo phtml a ser usado na classe atual:
            $this->setHtml($name);
        }
    }
?>
