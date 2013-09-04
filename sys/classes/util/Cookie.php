<?php
    namespace sys\classes\util;
    
    /**
     * Classse para gerenciamento de cookies da aplicação
     */
    class Cookie {
        /**
         * Recebe a View que deseja controlar o Cookie de memorização de Login.
         * Caso o cookie interbits_login esteja setado, a view receberá dois parâmetros:
         * <br />
         * LOGIN - O login gravado no cookie para exibição no <input>
         * CHECK_MEMORIA - O controle do <checkbox> para memorização
         * 
         * @param View $view
         */
        public static function verMemorizar($view){
            $view->LOGIN         = "";
            $view->CHECK_MEMORIA = "";

            if(isset($_COOKIE['interbits_login'])){
                $view->LOGIN         = $_COOKIE['interbits_login'];
                $view->CHECK_MEMORIA = "checked='checked'";
            }
        }
        
        /**
         * Verifica se já existe um Cookie criado para controle do Tooltip de Micro Conteúdo
         * do mecanismo de listas e caso não exista cria o mesmo.
         * 
         * Além de cria o JS para ser incorporado no HTML de Lista
         * 
         * @return string Js com variável de controle para o HTML
         */
        public static function verTooltipConteudo(){
            if(isset($_COOKIE['tooltip_conteudo'])){
                $js = "var verCookieTooltipConteudo = false;";
            }else{
                setcookie("tooltip_conteudo", "1", time()+60*60*24*30, "/");
                $js = "var verCookieTooltipConteudo = true;";
            }
            
            return $js;
        }
    }
?>
