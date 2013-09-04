<?php

    /**
     * Exemplo de uso:
     * 
     * <code>
     *      $objCurl = new Curl('http://...');
     *      $objCurl->setPost($request);
     *      $objCurl->createCurl();
     *      $errNo = $objCurl->getErro();    
     *      if ($errNo == 0){
     *          //Consulta realizada com sucesso. Imprime a resposta na tela.
     *          $ret = $objCurl->getResponse();
     *          echo $ret;
     *      } else {
     *          //Um erro ocorreu. Imprime o erro na tela. 
     *          $msgErr = 'Erro: '.$objCurl->getOutput();
     *          echo $msgErr;
     *      }
     * </code>		
     */

    namespace sys\classes\util;
    
    class Curl {
        protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
        protected $_url;
        protected $_followlocation;
        protected $_timeout;
        protected $_maxRedirects;
        protected $_cookieFileLocation = './cookie.txt';
        protected $_post;
        protected $_postFields;
        protected $_referer;

        protected $_session;
        protected $_webpage;
        protected $_includeHeader;
        protected $_noBody;
        protected $_status;
        protected $_errno;//NÚMERO DO ERRO
        protected $_errDescr;//DESCRIÇÃO DO ERRO
        protected $_arrInfo;
        protected $_binaryTransfer;
        public    $authentication = 0;
        public    $auth_name      = '';
        public    $auth_pass      = '';

        public function __construct($url,$followlocation = true,$timeOut = 180,$maxRedirecs = 4,$binaryTransfer = false,$includeHeader = false,$noBody = false)
        {
                 $this->_url                = $url;
                 $this->_followlocation     = $followlocation;
                 $this->_timeout            = $timeOut;
                 $this->_maxRedirects       = $maxRedirecs;
                 $this->_noBody             = $noBody;
                 $this->_includeHeader      = $includeHeader;
                 $this->_binaryTransfer     = $binaryTransfer;
                 $this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';

        }

        public function useAuth($use){
                $this->authentication = 0;
                if($use == true) $this->authentication = 1;
        }

        public function setName($name){
                $this->auth_name = $name;
        }

        public function setPass($pass){
                $this->auth_pass = $pass;
        }

        public function setReferer($referer){
                $this->_referer = $referer;
        }

        public function setCookiFileLocation($path){
                $this->_cookieFileLocation = $path;
        }

        public function setPost ($postFields){
                $this->_post = true;
                $this->_postFields = $postFields;
        }

        public function setUserAgent($userAgent){
                $this->_useragent = $userAgent;
        }

        public function createCurl($url = 'null'){
                if($url != 'null') $this->_url = $url;

                 $s = curl_init();

                 curl_setopt($s,CURLOPT_URL,$this->_url);
                 curl_setopt($s,CURLOPT_HTTPHEADER,array('Expect:'));
                 curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout);
                 curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects);
                 curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
                 curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation);
                 curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation);
                 curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation);
                 curl_setopt($s,CURLINFO_HEADER_OUT,false);

                 if($this->authentication == 1){
                   curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
                 }


                 if($this->_post){
                         curl_setopt($s,CURLOPT_CAINFO, getcwd()."/ca/-.websiteseguro.com.crt");		
                         curl_setopt($s,CURLOPT_POST,true);
                         curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields);
                         curl_setopt($s,CURLOPT_SSL_VERIFYHOST, 2);
                         curl_setopt($s,CURLOPT_SSL_VERIFYPEER, FALSE);
                 }

                 if($this->_includeHeader
                 ){
                        curl_setopt($s,CURLOPT_HEADER,true);
                 }

                 if($this->_noBody){
                        curl_setopt($s,CURLOPT_NOBODY,true);
                 }
                 
                curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent);
                curl_setopt($s,CURLOPT_REFERER,$this->_referer);

                $response 			= curl_exec($s);
                $this->_webpage 	= $response;
                $this->_errno 		= curl_errno($s);
                $this->_errDescr 	= curl_error($s);
                $this->_status 		= curl_getinfo($s,CURLINFO_HTTP_CODE);
                $this->_arrInfo 	= curl_getinfo($s);
                curl_close($s);		
                return $response;
        }

        public function getOutput(){
                $output = $this->getResponse();
                $arrInfo = $this->getArrInfo();
                if ($output === false || $arrInfo['http_code'] != 200) {
                        $output = "ERRO [". $arrInfo['http_code']. " DESCR: " .$this->getErro('DESCR'). "]";
                } else {
                        $output = 'OK';
                }			
                return $output;
        }

        public function getArrInfo(){
                return $this->_arrInfo;	
        }

        public function getErro($show='NUM'){
                $ret = $this->_errno;	
                if ($show == 'DESCR') $ret = $this->_errDescr;
                return $ret;
        }		

        public function getHttpStatus(){
                return $this->_status;
        }

        public function getResponse(){
                return $this->_webpage;
        }
    }
?>
