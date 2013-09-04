<?php
/**
 * COMPONENTE Mail:
 * Envia mensagens eletrônicas por e-mail ou SMS.
 * Utiliza o PHPMailer para envio de mensagens por e-mail.
 *
 * Exemplo de uso:
 * <code>
 *  $objMail = Component::mail();    
 *  $objMail->smtpDebugOn();//Habilita o debug. Apenas para envio via SMTP.
 *  $objMail->setTemplate('nomeDoArquivo');//Se não for definido, o template default.html será usado.
 *  $objMail->setHtmlFile('teste');//Nome do arquivo texto (html,txt) a ser usado como BODY do template.
 *  $objMail->addAddress('user@server.com','NomeDoDestinatário');
 *  $objMail->setFrom('user@server.com','NomeDoRemetente');
 *  $objMail->setSubject('Assunto do e-mail');
 *  $objMail->addAnexo('caminhoDoArquivo');            
 *  if ($objMail->send()){
 *      echo 'mensagem enviada com sucesso.';
 *  }
 * </code>
 */
use \sys\lib\classes\LibComponent;
use \sys\lib\classes\Url;

class Mail extends LibComponent {
    
    private $objMailer          = NULL;
    private $arrAddress         = array();
    private $arrCco             = array();
    private $arrAnexo           = array();
    private $message            = '';
    private $emailFrom          = '';
    private $nameFrom           = '';
    private $emailReplyTo       = '';
    private $nameReplyTo        = '';
    private $confirmReadingTo   = '';
    private $subject            = 'Sem assunto';
    private $smtpDebug          = FALSE;
    private $folderMail         = '';
    private $pathTemplate       = '';
    
    /**
     * Faz a compactação de uma string gravando o resultado em um arquivo externo.
     * Os formatos permitidos são js, para conteúdo javascript, e css para conteúdo de folhas de estilo em cascata.
     *      
     * @return void
     * 
     * @throws \Exception Se uma extensão válida não for informada (valores permitidos: css, js).
     * @throws \Exception Se após a compactação de uma string válida de javascript o resultado for vazio.
     * @throws \Exception Se a tentativa de criar o arquivo de saída falhar.
     * @throws \Exception Se após a sua criação, o arquivo de saída possuir tamanho 0kb.
     */
    function init(){	        
        $rootComps = Url::pathRootComps('mail');
        require_once($rootComps.'src/PHPMailer_5.2.2/class.phpmailer.php'); 
        require_once($rootComps.'src/PHPMailer_5.2.2/class.smtp.php');
        
        //Define a pasta padrão onde o arquivo de template deve ser localizado.
        $this->setFolderMail();
        $this->setTemplate('default.html');//Define o arquivo padrão a ser usado como modelo.
        
        $objMailer = new PHPMailer(true);// O parâmetro TRUE significa que irá disparar exceções caso existam erros.
        if (!is_object($objMailer)){
            $this->Exception(__METHOD__,'ERR_IS_NOT_A_OBJECT'); 
        }
        
        $this->objMailer = $objMailer;
        $this->setReturn($this);
    }
    
    /**
     * Define a pasta padrão onde os templates de e-mail são armazenados.
     * Se nenhum parâmetro for informado utiliza o valor contido no arquivo config.xml.
     * 
     * Todos os exemplos abaixo são válidos:
     * common/viewParts/emails
     * common/viewParts/emails/
     * common\viewParts\emails
     * 
     * @param string $emailFolderTpl Caminho relativo da pasta.
     * @return void
     */
    function setFolderMail($emailFolderTpl=''){
        $folderTpl     = (strlen($emailFolderTpl) > 0)?$emailFolderTpl:\LoadConfig::emailFolder();
        $folder        = $folderTpl.'/';
        $folder        = str_replace('\\','/',$folder);
        $folder        = str_replace('//','/',$folder);
        
        if (!is_dir($folder)) {
            $arrVars = array('PATH'=>$folderTpl);
            $this->Exception(__METHOD__,'ERR_IS_DIR',$arrVars); 
        } 
        
        $this->folderMail = $folder;
    }
    
    /**
     * Adiciona um destinatário ao envio da mensagem atual.
     * Chame este método para cada destinatário que deseja incluir na mensagem.
     * 
     * @param string $email
     * @param string $name  Opcional
     * @return void
     */
    function addAddress($email,$name=''){
       $arrEmail = $this->vldEmail(__METHOD__,$email,$name);
       if (is_array($arrEmail)) {    
           $nameAddress = $arrEmail['name'];
           $this->objMailer->AddAddress($email, $nameAddress);
           
           //Guarda o valor em um array caso seja necessário saber 
           //os destinatários adicionados antes de fazer o envio.
           $this->arrAddress[]['email']    = $email;
           $this->arrAddress[]['name']     = $nameAddress;
       }      
    }
    
    /**
     * Utilize o método atual se deseja verificar os destinatários da mensagem antes de fazer o envio (método send()).
     * 
     * @return string[] Retorna um array de e-mails incluídos por meio do método addAddress()
     * @see addAddress().
     */
    function getAddress(){
        return $this->arrAddress;
    }
    
    /**
     * Adiciona um destinatário para receber a mensagem com cópia oculta.
     * Chame este método para cada destinatário que deseja incluir no campo Cco (cópia oculta) da mensagem.
     * @param string $email
     * @param string $name Opcional
     * @return void 
     */
    function setCco($email,$name=''){
      $arrEmail = $this->vldEmail(__METHOD__,$email,$name);
      if (is_array($arrEmail)) {    
          $this->objMailer->AddBCC($email, $arrEmail['name']);
          $this->arrCco[] = $email;                     
      }       
    }
    
    
    /**
     * Define qual o e-mail do remetente da mensagem.
     * Caso um remetente não seja informado pelo método atual, o remetente definido  
     * no arquivo config.xml será usado.
     * 
     * @param string $email
     * @param string $name Opcional.
     * @return void
     * @see getFrom()
     */
    function setFrom($email,$name=''){
        $arrEmail = $this->vldEmail(__METHOD__,$email,$name);
        if (is_array($arrEmail)) {    
            $this->emailFrom  = $email;
            $this->nameFrom   = $arrEmail['name'];            
        }        
    }           
        
    /**
     * Adicionar destinatário no campo Cc (com cópia).
     * 
     * @param string $email
     * @param string $name Opcional
     * @return void
     */
    function setReplyTo($email,$name=''){
        $arrEmail = $this->vldEmail(__METHOD__,$email,$name);
        if (is_array($arrEmail)) {           
           $this->objMailer->AddReplyTo($email, $arrEmail['name']);    
        }        
    }            
    
    /**
     * Define qual a conta de e-mail que deve receber a confirmação de leitura.
     * 
     * @param string $email 
     * @return void
     */
    function confirmReadingTo($email){
        $arrEmail = $this->vldEmail(__METHOD__,$email,$name);
        if (is_array($arrEmail)) {          
            $this->objMailer->confirmReadingTo = $email;
        }         
    }    
    
    /**
     * Verifica se o e-mail informado está no formato correto.
     * Corrige também erro de digitação, como por exemplo, user@server.om, corrigido para user@server.com.
     * 
     * @param string $email
     * @return string|boolean 
     * @throws \Exception Se o parâmetro e-mail estiver vazio.
     * @throws \Exception Se o e-mail informado não for uma conta de e-mail válida.
     */
    private function vldEmail($method,$email,$name=''){
        $email      = trim($email);
        $arrEmail   = NULL;
        if (strlen($email) == 0) {
           $arrVars = array('EMAIL'=>$email,'METHOD'=>$method);
           $this->Exception(__METHOD__,'ERR_EMAIL_IS_EMPTY',$arrVars);              
        }
        
        @list($prefixo,$sufixo)	= explode('@',$email);
        if (strlen($sufixo) > 0){
                $sufixo	= str_replace('.om','.com',$sufixo);
                $email	= $prefixo.'@'.$sufixo;
        }
        
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $name = (strlen($name) > 0)?$name:$email;
            $arrEmail['email']  = $email;
            $arrEmail['name']   = $name;
            return $arrEmail;
	} else {
           $arrVars = array('EMAIL'=>$email,'METHOD'=>$method);
           $this->Exception(__METHOD__,'ERR_VLD_EMAIL',$arrVars);  
        }
        return $arrEmail;
    }    
    
    /**
     * Adiciona um anexo à mensagem.
     * 
     * @param string $pathFile Caminho do arquivo a ser incluído na mensagem.
     * @return void
     * @throws \Exception Se o parâmetro $pathFile estiver vazio.
     * @throws \Exception Se o arquivo informado não existir.
     */
    function addAnexo($pathFile=''){
        if (strlen($pathFile) == 0) {
            $this->Exception(__METHOD__,'ERR_PATH_IS_EMPTY');  
        } elseif (file_exists($pathFile)) {
            $this->objMailer->AddAttachment($pathFile);            
        } else {
            $arrVars = array('FILE'=>$pathFile);
            $this->Exception(__METHOD__,'ERR_FILE_NOT_FOUND',$arrVars);  
        }
    }    
    
    /**
     * Informa o template a ser usado na mensagem atual.
     * O arquivo usado como template deve estar no formato texto (HTML ou txt) e deve
     * ser armazenado no diretório templates, dentro da pasta definida como root para as mensagens de e-mail
     * (configuração definida em config.xml).      
     * 
     * Todos os exemplos abaixo são válidos para o parâmetro $tplName:
     * campanha
     * campanha.htm
     * campanha.html
     * campanha/modelo1.htm
     * 
     * @param string $tplName
     * @return void
     * @see setPath()
     */
    function setTemplate($tplName){
        if (strlen($tplName) > 0) {
            $pathTemplate = $this->setPath($tplName,'templates');            
            if (!file_exists($pathTemplate)){
                $arrVars = array('PATH_TEMPLATE'=>$pathTemplate);
                $this->Exception(__METHOD__,'ERR_FILE_NOT_FOUND',$arrVars);                  
            }
            
            $this->pathTemplate = $pathTemplate;
        }
    }
    
    /**
     * Gera o caminho relativo do arquivo solicitado.
     * Método de suporte a setTemplate() e setHtmlFile().
     * 
     * Caso o parâmetro $filename não contenha extensão, a extensão .html será adotada como formato padrão.
     * 
     * @param string $filename Nome do arquivo. Por exemplo, campanhaFerias.
     * @param type $folder Opcional. Nome do diretório adicional a ser incluído no path.
     * @return string 
     * @see setTemplate()
     * @see setHtmlFile()
     */
    private function setPath($filename,$folder=''){
        $pos        = strpos($filename,'.htm');
        $pathFile   = $this->folderMail;
        if (strlen($folder) > 0) $pathFile .= $folder.'/';
        if ($pos !== FALSE) {
            $pathFile .= $filename;
        } else {
            $pathFile .= $filename.'.html';
        }   
        return $pathFile;
    }
    
    /**
     * Utiliza o conteúdo do arquivo informado como corpo da mensagem no formato HTML.
     *      
     * @param string $filename Nome do arquivo que contém o corpo da mensagem. 
     * @param $arrParams Array usado para trocar o marcador do HTML com o respectivo valor, cujo índice coincide com esse marcador.
     * @return void
     * @throws \Exception Se o parâmetro $pathFile estiver vazio.
     * @throws \Exception Se o arquivo informado não existir.     
     */
    function setHtmlFile($filename,$arrParams=NULL){
        
        $pathFile = $this->setPath($filename);
        
        if (strlen($pathFile) > 0) {
            if (file_exists($pathFile)) {
                //Faz a junção do arquivo template com o arquivo do conteúdo da mensagem.
                $body   = file_get_contents($pathFile); 
                $this->setHtml($body,$arrParams);
            } else {
                $arrVars = array('FILE'=>$pathFile);
                $this->Exception(__METHOD__,'ERR_FILE_NOT_FOUND',$arrVars);                  
            }
        } else {            
            $this->Exception(__METHOD__,'ERR_PATH_IS_EMPTY');            
        }
    }
    
    
    /**
     * Concatena uma string (parâmetro $body) com o template informado 
     * e atribui o valor ao campo de mensagem do objeto PHPMailer.     
     * 
     * @param string $body Corpo da mensagem que será mesclado com o template.
     * @param string $arrParams Array associativo usado para substituir tags no template com variáveis.
     * @return void
     */
    function setHtml($body,$arrParams=NULL){
        $template   = $this->pathTemplate;
        $message    = '';       
        $hoje       = date('d/m/Y');
        $hora       = date('H:i:s');
        $agora      = $hoje.' às '.$hora;
        $arrDefault = array('DT_HR_ENVIO'=>$agora,'DT_ENVIO'=>$hoje,'HR_ENVIO'=>$hora);
        $arrLabels  = (is_array($arrParams))?array_merge($arrParams,$arrDefault):$arrDefault;  
        
        if (is_string($body) && strlen($body) > 0) {
            $tpl        = file_get_contents($template);
            $message    = str_replace("{BODY}",$body,$tpl);

            foreach($arrLabels as $key=>$value){
                $message = str_replace("{{$key}}",$value,$message);
            }
        } else {
            
        }
        
        if (strlen($message) > 0) {
            $this->AltBody = 'Para visualizar essa mensagem é necessário usar um programa de e-mail compatível com HTML.';
            $this->objMailer->MsgHTML(utf8_decode($message));
            $this->message = $message; 
        } else {
            
        }
    }  
    
    function setTextPlain($mailBodyTextFile){
        $mail              = $this->objMailer;
        $mail->IsHTML(false);
        
        $mail->ContentType  = 'text/plain';         
        $mail->Body         = $mailBodyTextFile; 
        $this->objMailer    = $mail;
    }
    
    /**
     * Imprime a mensagem a ser enviada por e-mail.
     * Permite conferir as variáveis da mensagem, layout, formato etc.
     * Interrompe a execução do script.
     * 
     * @return void 
     */
    function printMsg(){        
        $message = $this->message;        
        die($message);        
    }
    
    /**
     * Assunto da mensagem.
     * 
     * @param string $subject 
     */
    function setSubject($subject){
        if (strlen($subject) > 0) $this->subject = utf8_decode($subject);
    }
       
    /**
     * Faz o envio da mensagem de acordo com os parâmetros informados.
     * Se houver uma configuração de SMTP em config.xml e o ambiente atual for 'dev' (desenvolvimento)
     * habilita o debug de SMTP.
     * 
     * @return boolean TRUE se a mensagem foi enviada com sucesso, ou FALSE caso contrário.
     * @throws Se houver falha no envio da mensagem.
     * @throws phpmailerException 
     */
    function send(){      
        $arrSmtpConfig      = $this->getSmtpConfig();
        $confirmReadingTo   = $this->confirmReadingTo;
        $mail               = $this->objMailer;
        $mail->Subject      = $this->subject;
        
	if (1==0 && (APPLICATION_ENV == 'test' || APPLICATION_ENV == 'dev')) {
            //O WAMP e MAMP não possuem por padrão a biblioteca sendmail.
            //Por isso não está ativo em ambiente de  desenvolvimento (local).
            $arrFrom  = $this->getFrom();
            $mail->SetFrom($arrFrom['email'], $arrFrom['name']);                                                  
            $mail->IsSendmail();          
        }
        
        if (is_array($arrSmtpConfig)) {
            //Um servidor de SMTP foi definido.             
           
            //O remetente deve ser o mesmo username do SMTP:            
            $mail->SetFrom($arrSmtpConfig['username'], 'SuperPro Web');      

            $mail->IsSMTP();            
            $mail->SMTPAuth = $arrSmtpConfig['auth'];
            $mail->Host     = $arrSmtpConfig['host'];
            $mail->Port     = $arrSmtpConfig['port'];
            $mail->Username = $arrSmtpConfig['username'];
            $mail->Password = $arrSmtpConfig['password'];
            
            //$arrFrom['email']   = $arrSmtpConfig['username'];
            //$arrFrom['name']    = $arrSmtpConfig['SuperPro Web'];
            if ($this->smtpDebug) $mail->SMTPDebug  = 2;            
        }
        
        try {
            if (strlen($confirmReadingTo) > 0) $mail->ConfirmReadingTo = $confirmReadingTo; 
            //$mail = $this->cfgReplyTo($mail);
            //$mail = $this->cfgAddress($mail);                                    
            if(!$mail->Send()) {                
                $arrVars = array('ERR_MSG'=>$mail->ErrorInfo);
                $this->Exception(__METHOD__,'ERR_SEND',$arrVars);          
            } 
            return TRUE;
        } catch (phpmailerException $e) {
            throw $e;
        } catch(\Exception $e) {
            throw $e;
        }
        return FALSE;
    }    
    
    function smtpDebugOn(){
        $this->smtpDebug = TRUE;
    }
    
    function getSmtpConfig(){
        $smtpHost       = \LoadConfig::smtpHost();    
        $smtpAuth       = (int)\LoadConfig::smtpAuth();    
        $smtpPort       = \LoadConfig::smtpPort();    
        $smtpUsername   = \LoadConfig::smtpUsername();    
        $smtpPassword   = \LoadConfig::smtpPassword();    
        
        $arrSmtp        = NULL;
        
        if (strlen($smtpHost) > 0) {
            $arrSmtp['host']        = $smtpHost;
            $arrSmtp['auth']        = ($smtpAuth == 1)?TRUE:FALSE;
            $arrSmtp['port']        = $smtpPort;
            $arrSmtp['username']    = $smtpUsername;
            $arrSmtp['password']    = $smtpPassword;
        }
        return $arrSmtp;        
    }
    
    /**
     * Retorna os dados do campo 'from' ao enviar o e-mail.
     * Caso o campo remetente (from) não tenha sido definido na configuração do envio
     * os dados de config.xml serão usados.
     * 
     * @return string[] Retorna array bidimensional ['email','name']
     * @throws \Exception Se um remetente não foi informado.
     */
    function getFrom(){
        $emailFrom = $this->emailFrom;        
        $nameFrom  = $this->nameFrom;
        if (strlen($emailFrom) == 0) {
            $emailFrom   = trim(\LoadConfig::emailFrom());        
            $nameFrom    = \LoadConfig::nameFrom();
            if (strlen($nameFrom) == 0) $nameFrom = $emailFrom;
        }
        
        if (strlen($emailFrom) == 0) {            
            $this->Exception(__METHOD__,'ERR_FROM');              
        }
        
        $arrFrom['email']   = $emailFrom;
        $arrFrom['name']    = $nameFrom;
        
        return $arrFrom;        
    }
 }