<?php
/**
 * COMPONENTE Facebook:
 * Cria o conjunto de serviços que podem ser utilizados na API do Facebook
 *
 * Exemplo de uso:
 * <code>
 * </code>
 */
use \sys\lib\classes\LibComponent;
use \sys\lib\classes\Url;

class Facebookapi extends LibComponent {
    //Define configurações da classe
    private $config;
    
    private $redirectUrl = "http://www.supervip.com.br";
    
    public function init(){	
        try{
            //Inclui arquivo da biblioteca
            $rootComps = Url::pathRootComps('facebookapi');
            require_once($rootComps.'src/facebook/src/facebook.php'); 
            
            //Ambiente de produção
            $appId  = '331964393598999';
            $secret = '3041e86bcf50a3d808a7065ef57a8d9f';
            
            $this->config = array(
                'appId'         => $appId,
                'secret'        => $secret,
                'fileUpload'    => false
            );
            
            $this->setReturn($this);
        }catch(Exception $e){
            throw $e;    
        }
    }
    
    public function getAppId(){
        return $this->config['appId'];
    }
    
    public function getSecretId(){
        return $this->config['secret'];
    }
    
    public function getRedirectUrl(){
        return $this->redirectUrl . '/dev/auth/login/fb';
    }
    
    public function getRedirectUrlCadastro(){
        return $this->redirectUrl . '/dev/auth/cadastro/fb';
    }
    
    public function getUser($access_token){
        //Cria instancia do Objeto com as configuirações
        $fb = new Facebook($this->config);
        $fb->setAccessToken($access_token);
        
        return $fb->api("/me");
    }
}
