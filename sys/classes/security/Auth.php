<?php

/**
 * Classe utilizada para fazer a autenticação do usuário em páginas protegidas.
 *
 * @author Supervip
 */
use \sys\classes\util\Request;
use \auth\classes\helpers\ErrorHelper;

class Auth {

    const SESSION_USER      = 'sessionUser';
    const SESSION_USER_ID   = 'sessionUserId';
    const SESSION_ID        = 'sessionId';
    const SESSION_MESSAGE   = 'sessionAuthMessage';
        
    /**
    * Destrói a autenticação do usuário logado. 
    */
    public static function logout(){
        self::unsetUsuario();  
    }      
    
    /**
     * Verifica se há uma sessão ativa
     * 
     * @return boolean 
     */
    public static function checkAuth($redirect=''){  
        $objUsuario = self::getUsuario();        
        $out        = FALSE;   

        if (is_object($objUsuario)) {
            $out = TRUE;//Session ativa
        } elseif(strlen($redirect) > 0) {
            header('Location:'.$redirect);
            die();
        }
        return $out;        
    }
    
    /**
     * Checa a permissão do usuário em acessar a área solicitada
     * 
     * @param array $arrUserPerfil Perfil ou Perfis a serem validados. Ex: array( 'ESC', 'PRO', 'ALUNO' );
     * @param string $redirect Local onde será enviado o usuário caso não tenha permissão
     * @return boolean
     */
    public static function checarPermissao($arrUserPerfil, $redirect='/app/login'){    
        $user = self::getUsuario();

        //Verifica sessão 
        if(!$user){
            //\Auth::setMessage(Error::eLogin('EXPIRED_SESSION'));
            \Auth::setMessage("Sessão expirada. Efetue o login para continuar!");
            header('Location: ' . $redirect);
            die();
        }
        
        //Valida perfil
        if(array_search($user->CODIGO_PERFIL, $arrUserPerfil) === false){
            //\Auth::setMessage(Error::eLogin('DANIED_ACCESS'));
            \Auth::setMessage("Desculpe, você não tem permissão para acessar esta área");
            header('Location: ' . $redirect);
            die();
        }
        
        return true;      
    }
   
    /**
     * Persiste em sessão o objeto do Usuário logado.
     * 
     * @param Usuario $objUsuario
     */
    public static function persistUsuario($objUsuario){
        if ($objUsuario instanceof common\classes\helpers\Usuario) $_SESSION[self::SESSION_USER] = serialize($objUsuario);
    }
    
 /**
     * Recupera o objeto Usuario guardado em sessão.
     * 
     * @return Usuario | NULL
     */
    public static function getUsuario(){
        $objUsuario = NULL;
        if (isset($_SESSION[self::SESSION_USER])) {
            $objUsuarioRecup = unserialize($_SESSION[self::SESSION_USER]);
             if ($objUsuarioRecup instanceof common\classes\helpers\Usuario) {
                 $objUsuario = $objUsuarioRecup;
             }
        }
        return $objUsuario;
    }    
    
    public static function unsetUsuario(){
        if (isset($_SESSION[self::SESSION_USER])) unset($_SESSION[self::SESSION_USER]);
    }
    
    
    /**
     * Persiste uma mensagem capturada no processo de autenticação do usuário.
     * A persistência é feita em uma variável de sessão.
     * 
     * Exemplo:
     * Caso falhe a autenticação de usuário e senha, uma mensagem é capturada via classe sys\classes\util\Dic.
     * Essa mensagem, ao ser persistida por \Auth::setMessage(), pode ser mostrada no formulário de acesso.
     * 
     * @param string $message
     * @return void
     */
    public static function setMessage($message){
        $_SESSION[self::SESSION_MESSAGE] = $message;
    }
    
    public static function unsetMessage(){
        unset($_SESSION[self::SESSION_MESSAGE]);
    }
    
    /**
     * Retorna uma mensagem persisitida no processo de autenticação.
     * 
     * @return string
     */
    public static function getMessage(){
        $message = Request::session(self::SESSION_MESSAGE);       
        return $message;
    }
    
    /**
     * Verifica se o usuário logado esta usando uma senha Administrativa ou não
     * 
     * @return boolean
     */
    public static function checarUserAdmin(){
        //Recupera user da Sessão
        $objUser = self::getUsuario();
        
        //Se não estiver logado = FALSE
        if(is_null($objUser)){
            return FALSE;
        }
        
        return $objUser->getAcessoAdmin();
    }
}

?>
