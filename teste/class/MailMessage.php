<?php

    class MailMessage {
        private $idAssinatura;
        private $conn;
        private $index;
        private $body;
        private $fechBody;
        private $bodyPlain;
        private $bodyHtml;
        private $assuntoDb;
        private $assunto;
        private $from;
        private $fromAddress;
        private $fromName;
        private $fromMailbox;//ex.: joao
        private $fromHost; //ex.: host.com.br
        private $fromEmail; //ex.: joao@host.com.br
        private $to;
        private $cc;
        private $date;
        private $size;//Em bytes
        private $messageId;
        private $bodyReturn;//mensagem completa a ser reenviada caso seja necessário.
        private $structure;
        private $overview;
        private $arrAnexos;
        private $numImagesInline    = 0;
        private $folderAnexos       = 'anexos_';//Pasta usada para armazenar anexos das mensagens lidas
        
        private $arrHtmlMap = array(
            'tks'   => 'Tarefas:',
            'ds'    => 'Descrição:',
            'tag'   => 'Palavras-chave:',
            'memo'  => ''
        );
            
        function __construct($idAssinatura, $conn, $index) {
            $this->idAssinatura     = $idAssinatura;
            $this->conn             = $conn;
            $this->index            = $index;
            $header                 = imap_header($conn, $index);            
            $from                   = $header->from[0];
            $this->from             = $from;
           
            //Info Remetente:
            $this->fromName         = $from->personal;
            $this->fromMailbox      = $from->mailbox;
            $this->fromHost         = $from->host;
            $this->fromEmail        = "{$this->fromMailbox}@{$this->fromHost}";
            $this->assuntoDb        = iconv_mime_decode($header->subject,0,"UTF-8");
            $this->assunto          = utf8_decode($this->assuntoDb);
            $this->fromAddress      = $header->fromaddress;            
            $this->to               = $header->toaddress;
            $this->cc               = (isset($header->ccaddress)) ? $header->ccaddress : '';
            $this->date             = $header->date;
            $this->size             = (int)$header->Size;//Em bytes
            $this->messageId        = $header->message_id;                 
            $this->overview         = imap_fetch_overview($conn,$index,0);            
            $this->structure        = @imap_fetchstructure($conn, $index,FT_UID);    
            $fechBody               = imap_fetchbody($conn, $index,"");
            $this->fechBody         = $fechBody;
            $this->numImagesInline  = (int)substr_count($fechBody,"Content-Transfer-Encoding: base64");//total de imagens inline           
            $this->body             = imap_qprint(imap_fetchbody($conn,$index,"1")); ## GET THE BODY OF MULTI-PART MESSAGE
            
            // Extrai a mensagem a ser reenviada e anexos, se houver
            $this->extractParts();            
        }
        
        function verifEmailJaCadastrado(){
            //Verifica se a mensagem atual já foi cadastrada.
            $dtHrEn = $this->getDtHrEn();
            $sql = "SELECT COUNT(*) AS TOTAL_MSG FROM SVIP_EMOP_MSG WHERE 
            ID_ASSINATURA = $this->idAssinatura AND TAM_BYTES = $this->size AND TITULO = '$this->assuntoDb' AND DATA_HORA_ENVIO = '$dtHrEn'";
            
            $result = Conn::query($sql);  
            if (!is_null($result)) {
                $msgJaCad   = (int)$result[0]['TOTAL_MSG'];
                if ($msgJaCad > 0) return TRUE;
            }
            return FALSE;
        }
        
        private function getCc(){
            return $this->cc;
        }
        
        private function getTo(){
            return $this->to;
        }
        
        function parsePseudoLinguagem(){
            //Faz tratamento da pseudo-linguagem             
            $objPseudoLing  = new PseudoLinguagem($this->body);
            return $objPseudoLing->extractActionsForString();
        }
        
        private function extractParts() {
            $arrHtmlMap = $this->arrHtmlMap;
            $format = 'html';
            $body   = $this->getPart("TEXT/HTML");
            // if HTML body is empty, try getting text body
            if ($body == "") {
                $format = 'text_plain';
                $body = $this->getPart("TEXT/PLAIN");
            }

            //Retira marcadores de pseudo-linguagem, se houver.
            foreach($arrHtmlMap as $key=>$value) {
                $cod    = "#{$key}:";   
                $value  = utf8_decode($value);
                if ($format == 'html') $value = "<b>$value</b>";
                $body   = str_replace($cod,$value,$body);            
            }
            $this->bodyReturn = $body; 
        }   
                
        private function getPart($mimetype, $structure = false, $partNumber = false) {
            $conn   = $this->conn;
            $index  = (int)$this->index;
            
            if (!$structure) {
                $structure = $this->structure;
            }
            if ($structure) {               
                if (count($this->arrAnexos) == 0) $this->saveAnexos($structure);
                if ($mimetype == $this->getMimeType($structure)) {
                    if (!$partNumber) {
                        $partNumber = 1;
                    }
                    $text = imap_fetchbody($conn, $index, $partNumber, FT_UID);
                    switch ($structure->encoding) {
                        case 3: return imap_base64($text);
                        case 4: return imap_qprint($text);
                        default: return $text;
                   }
               }

                // multipart 
                if ($structure->type == 1) {
                    foreach ($structure->parts as $i => $subStruct) {
                        $prefix = "";
                        if ($partNumber) {
                            $prefix = $partNumber . ".";
                        }
                        $data = $this->getPart($mimetype, $subStruct, $prefix . ($i + 1));
                        if ($data) {
                            return $data;
                        }
                    }
                }
            }
            return false;            
        }
        
        private function getMimeType ($structure){    
            $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
            if ($structure->subtype) {
               return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
            }
            return "TEXT/PLAIN";
        }
        
        function getDtHrEn(){
            //Converte data para o formato Y-m-d H:i:s
            $dataHoraEn = '';
            $date       = $this->date;
            
            if (strlen($date) > 0) {
                $strDate    = strtotime($date);
                $dataHoraEn = date("Y-m-d H:i:s", $strDate);                
            }
            return $dataHoraEn;
        }
        
        function msgJaCadastrada(){
            //Verifica se a mensagem atual já foi cadastrada.
            $jaCadastrada   = false;
            $dataHoraEn     = $this->getDtHrEn();            
            $sql = "SELECT COUNT(*) AS TOTAL_MSG FROM SVIP_EMOP_MSG WHERE 
            ID_ASSINATURA = $idAssinatura AND TAM_BYTES = {$this->size} AND TITULO = '{$this->tituloDb}' 
            AND DATA_HORA_ENVIO = '{$dataHoraEn}'";

            $result     = DB::query($sql);
            $msgJaCad   = (int)$result[0]['TOTAL_MSG'];
            if ($msgJaCad > 0) $jaCadastrada = true;
            return $jaCadastrada;
        }
        
        function saveAnexos($structure){
            $attachments    = array();
            $conn           = $this->conn;
            $index          = $this->index;
            $folderAnexos   = $this->getFolderAnexos();
            if ($folderAnexos === FALSE) {
                throw new Exception("Não foi possível criar a pasta {$folderAnexos} para armazenamento de anexos.");
            }            
            
            if(isset($structure->parts) && count($structure->parts)) {
                for($i = 0; $i < count($structure->parts); $i++) {
                      $attachments[$i] = array(
                          'is_attachment' => false,
                          'filename' => '',
                          'name' => '',
                          'attachment' => ''
                      );

                      if($structure->parts[$i]->ifdparameters) {
                        foreach($structure->parts[$i]->dparameters as $object) {
                          $atrib = strtolower($object->attribute);
                          if($atrib == 'filename' || $atrib == 'name') {
                              $attachments[$i]['is_attachment'] = true;
                              if ($atrib == 'filename') {
                                $attachments[$i]['filename'] = $object->value;
                              } elseif ($atrib == 'name') {
                                  $attachments[$i]['name'] = $object->value;
                              }
                          }
                        }
                      }                

                      if($attachments[$i]['is_attachment']) {
                        $attachments[$i]['attachment'] = imap_fetchbody($conn, $index, $i+1);
                        if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                          $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                          $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                      }
                }
            }      
            
            $totalAnexos = count($attachments);
            if($totalAnexos!=0){
                //A mensagem atual possui anexos.
                foreach($attachments as $at){
                    if($at['is_attachment']==1){
                        $fileDest = $folderAnexos.$at['filename'];
                        //echo "<a href='".$at['filename']."'>".$at['filename']."</a><br/>";
                        //echo $at['filename'].'- '.$at['attachment'].'<br>';
                        if (file_put_contents($fileDest, $at['attachment'])){
                            echo 'Imagem gravada com sucesso!<br>';
                            $this->arrAnexos[] = $fileDest;
                            //echo "<a href='".$fileDest."'>".$fileDest."</a><br/>";
                        } else {
                            echo 'Erro ao gravar anexo.<br>';
                        }
                    }
                 }
             }  
             return $totalAnexos;
        }
        
        private function getFolderAnexos(){
            $folderAnexos   = $this->folderAnexos;
            $idAssinatura   = (int)$this->idAssinatura;
            if (strlen($folderAnexos) > 0 && $folderAnexos !== '/'){
                 if (!is_dir($folderAnexos)) mkdir($folderAnexos); 
                 if (is_dir($folderAnexos)) {
                     $subfolder = $folderAnexos.'/'.$idAssinatura.'/';
                     if (!is_dir($subfolder)) mkdir($subfolder); 
                     return $subfolder;
                 }
            } else {
                return '';
            }
            return FALSE;
        }
                
        function getDados(){
            $arrDados = array(                
                'TAM_BYTES' => $this->size,
                'MESSAGE_ID' => $this->index,
                'DATA_HORA_ENVIO' => $this->getDtHrEn(),
                'TITULO' => $this->assuntoDb,
                'MENSAGEM' => utf8_encode($this->body),
                'AUTOR' => $this->fromName,
                'FROM_NAME' => $this->fromName,
                'FROM_EMAIL' => $this->fromEmail,
                'REMETENTE' => $this->fromName,
                'DESTINATARIO' => $this->getTo(),
                'CC' => $this->getCc(),
                'CCO' => '',
                'DATA_REGISTRO' => DB::sqleval("NOW()")
            ); 
            return $arrDados;
        }        
          

    }
?>
