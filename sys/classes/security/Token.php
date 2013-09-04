<?php
    /* This file is really *free* software, not like FSF ones.
    *  Do what you want with this piece of code, I just enjoyed coding, don't care.
    */

    /**
    * Provides a way to prevent Session Hijacking attacks, using session tokens.
    *
    * This class was written starting from Claudio Guarnieri's {@link http://www.playhack.net Seride}. I took Claudio's code and learned how it works; then I wrote my own code tring to improve his.
    * @author Francesco Cirac� <sydarex@gmail.com>
    * @link http://sydarex.org
    * @version 0.2
    * @copyright Copyleft (c) 2009/2010 Francesco Cirac�
    */

    /**
    * Token class.
    *
    * Provides a way to prevent Session Hijacking attacks, using session tokens.
    *
    * This class was written starting from Claudio Guarnieri's {@link http://www.playhack.net Seride}. I took Claudio's code and learned how it works; then I wrote my own code tring to improve his.
    *
    * @author Francesco Cirac� <sydarex@gmail.com>
    * @copyright Copyleft (c) 2009, Francesco Cirac�
    */
    
    namespace sys\classes\security;
    class Token {

	/**
	 * Guarda o tempo de vida do token, em minutos.
	 *
	 * @access private
	 * @var integer
	 */
	private $timeout;

	/**
	 * Guarda um novo token.
	 *
	 * @access private
	 * @var string
	 */
	private $token = null;
        private $tokenLength = 0;

	/**
	 * Guarda o código de erro identificado pelo método Check().
	 *
	 * Valores possíveis:
	 * 0 Não há erros.
	 * 1 Nenhum token foi requisitado.
	 * 2 Não ha úma sessão ativa para o token atual.
	 * 3 Não há um token definido para a sessão atual.
	 * 4 Token expirou.
	 * @access private
	 * @var integer
	 */
	private $error = 0;

	/**
	 * Construtor da classe. 
         * Inicia a variável que guarda o tempo de vida do token (valor em minutos),
         * inicializa a sessão caso ainda não exista e gera um novo token.
	 *
	 * @param integer $timeout Tempo de vida do token, em minutos. Default 5 minutos.
	 */
	function __construct($timeout=5) {
		$this->timeout = $timeout;//Em minutos.
		if(!isset($_SESSION)) session_start();
		$this->tokenSet();
	}
        
        function getTokenLength(){
            return $this->tokenLength;
        }
        
	/**
	 * Cria um token
	 *
	 * @access private
	 */
	private function tokenSet() {
		$this->token                        = $this->tokenGen(8);//Token com 8 caracteres.
		$_SESSION["spackt_".$this->token]   = time();
	}        

	/**
	 * Gera um token. 
         * Técnica para gerar token de {@link http://www.playhack.net Seride}.
	 * 
         * @param $length 
         * Se zero, retorna um token com 40 caracteres. 
         * Se > 0 retorna uma string com o tamanho informado em $length.
	 * 
         * @return string
	 */
	function tokenGen($length=32) {
		// Hashes a randomized UniqId.
                $uId    = uniqid(rand(), true);                
                $str    = $uId.date('dmYHis');
		$hash   = sha1($str);
                $tam    = strlen($hash);
		$token  = $hash;
                
                if ($length > 0 && $length < $tam) {
                    //Retorna apenas uma string randômnica com tamanho $length.
                    $this->tokenLength  = $length;
                    
                    // Gera o token retornando uma parte do hash com 8 caracteres, iniciando do número randômico $n                 
                    $token = substr($hash, 0, $length);
                }
		return $token;
	}
        
        function getToken(){
            return $this->token;
        }

	/**
	 * Destrói um token.
	 *
	 * @param string $token Nome do token a ser destruído. 
	 */
	function tokenDel($token) {
		unset($_SESSION[$token]);
	}

	/**
	 * Destrói todos os tokens, exceto o novo  token.
	 */
	function tokenDelAll() {
		$sessvars = array_keys($_SESSION);
		$tokens = array();
		foreach ($sessvars as $var) if(substr_compare($var,"spackt_",0,7)==0) $tokens[]=$var;
		unset($tokens[array_search("spackt_".$this->token,$tokens)]);
		foreach ($tokens as $token) unset($_SESSION[$token]);
	}

	/**
	 * Define um token para proteger um formulário.
         * 
         * @return string Retorna um campo hidden com o token.
	 */
	function protectForm() {
            return "<input type=\"hidden\" name=\"spack_token\" value=\"".$this->token."\" />";
	}

	/**
	 * Define um token para proteger um link, colocando um token em uma variável querystring.
	 *
	 * @param string $link O link a ser protegido.
	 * @return string
	 */
	function protectLink($link) {
		if(strpos($link,"?")) return $link."&spack_token=".$this->token;
		else return $link."?spack_token=".$this->token;
	}

	/**
	 * Verifica se a requisição possui o token correto.
         * Depois disso destrói os tokens antigos.
         * 
         * @return boolean Retorna TRUE se a requisição está ok, ou FALSE caso contrário.         
	 */
	function Check() {
		// Verifica se o token foi enviado.
		if(isset($_REQUEST['spack_token'])) {
			// Verifica se o token existe.
			if(isset($_SESSION["spackt_".$_REQUEST['spack_token']])) {
				// Verifica se o token não está vazio.
				if(isset($_SESSION["spackt_".$_REQUEST['spack_token']])) {
					$age = time()-$_SESSION["spackt_".$_REQUEST['spack_token']];
					// Verifica se o token não expirou.
					if($age > $this->timeout*60) $this->error = 4;
				}
				else $this->error = 3;
			}
			else $this->error = 2;
		}
		else $this->error = 1;
		
                // Destrói todos os tokens.
		$this->tokenDelAll();
		if($this->error==0) return true;
		else return false;
	}
        
	/**
	 * Retorna um código de erro.
         * 
         * @return integer
	 */
	function Error() {
		return $this->error;
	}
    }
 ?>