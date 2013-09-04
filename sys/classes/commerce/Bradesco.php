<?php

namespace sys\classes\commerce;

class Bradesco {
    private $assinaturaBoleto	= '9E5CAAB5582F4F3B4CFC6664966FC3AE832CD7048E72EFDF2514CBF715E7F4DA3EC19A4BA1C693F3CF3730C47217E29C804FC8AB31308860C0E1B089A4DA883672384210DC15C349E05BC753D8FB3C3D5C020E5D6125262FB921B98AFFC96A5BA4EA6C4B225E42EA91F079A081786DFC6EA78F08DC867A50A56A0FBDB05255C1';
    private $assinaturaTransf	= 'AEA4AD635FDB2A29D1A5FCB534B735E0BF7971CA373C4831B740EDD46D1EA5D634C4EBB7EB324FAD7EA3D1682B9B613EAAA6362A36225C67B0FB17780D5BAB2D12614CF1AE502C22D383FBF3CF77F03910C2B9C52CB03DB813563D2BB85C16834A6BD060450BCB1360A61533F9129F5D473FED93EC9FDD70AC57E98D0A9E6684';    			
    private $agencia 	= '2884';
    private $conta      = '0004740';	
    private $request    = '';
    function __construct() {
       
    }
    
    function response(){
        $request    = $this->request;
        $objCurl    = new Curl('https://superprofessorweb.websiteseguro.com/');
        $objCurl->setPost($request);
        $objCurl->createCurl();
        $errNo = $objCurl->getErro();
        if ($errNo == 0){
                $ret = $objCurl->getResponse();
                return $ret;
        } else {
                $msgErr = 'Erro: '.$objCurl->getOutput();
                echo $msgErr;
                die();
        }         
    }
}

?>
