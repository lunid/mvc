<?php

/**
 * Interface para classes de componentes contidos na pasta lib.
 * Os componentes são utilizados nos módulos da aplicação, 
 * inicializados a partir da classe sys/classes/util/Component.php
 *
 * @author Claudio Rubens Silva Filho
 */
namespace sys\lib\classes;

interface IComponent {
    
    /**
     * Executa uma ou mais tarefas para iniciar o componente.
     * @return void; 
     */
    function init();
    
    /**
     * Retorna o resultado do processo executado em init().
     * 
     * @return mixed 
     */
    function getReturn();
    
    /**
     * Retorna o path do arquivo XML usado como dicionário de mensagens de erro.
     * O arquivo deve ficar na raíz da pasta do componente, dentro de lib.
     * 
     * Exemplo do path XML para um componente chamado componentFolder:
     * sys/lib/componentFolder/dic/exception.xml;
     * 
     * @return string
     */
    function getXmlDic();
}

?>
