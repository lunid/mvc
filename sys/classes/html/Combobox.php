<?php
    
    namespace sys\classes\html;
    
    /**
     * Permite gerar um combobox usando recursos de phtml.
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
    class Combobox extends Html {                
        
        function __construct( $objParams = NULL){  
            
            //Informa o nome do arquivo phtml a ser usado na classe atual:
            $this->setHtml('combobox');
            
            //Define os parâmetros específicos da classe atual:
            $this->addParam('multiple');            
            $this->addParam('options');                                 
            $this->addParam('select');            
            $this->addParam('field_name');            
            
            //Carrega as propriedades do objeto atual a partir de objParams:
            $this->popParams($objParams);            
        }       
        
        /** 
         * Define um item ou uma lista de itens selecionados (listbox).
         * 
         * Quando houver mais de um item a ser selecionado, deve-se usar a vírgula como separador.
         * 
         * @param string $selectList 
         * return void
         */
        function selected($selectList){
            $this->select = $selectList;
        }
        
        function multipleOn(){
            $this->multiple = "multiple='multiple'";
        }
        
        function multipleOff(){
            $this->multiple = '';
        }        
        
        /**
         * Adiciona as options do combobox atual a partir de um array bidimensional.
         * 
         * @param $arrOptions Array bidimensional em que cada índice possui um array de duas posições
         * no formato array = array(array(VALUE1,LABEL1), array(VALUE2,LABEL2)...)
         */
        function addOptions($arrOptions) {
            if (is_array($arrOptions)) {
                $options        = $this->options;
                if (is_array($options)) {
                    //Já existe um ou mais valores em $this->options. Mescla o array atual com $arrOpgions.
                    $this->options  = array_merge($options,$arrOptions);            
                } else {
                    //Nenhum valor ainda foi definido para $this->options. 
                    //Grava o arrOptions em $this->options.
                    $this->options  = $arrOptions;            
                }
            }
        }
        
        function addOption($value,$label) {
            if (strlen($value) > 0 && strlen($label) > 0) {
                $options = $this->options;
                $indice = (is_array($options))?count($options):0;
                
                $options[$indice][0] = $value;
                $options[$indice][1] = $label;
                $this->options       = $options;
            }
        }   
    }
?>
