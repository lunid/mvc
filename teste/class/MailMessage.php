<?php

    class MailMessage {
        private $conn;
        private $index;
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

        private $arrHtmlMap = array(
            'tks'   => 'Tarefas:',
            'ds'    => 'Descrição:',
            'tag'   => 'Palavras-chave:',
            'memo'  => ''
        );
            
        function __construct($conn, $index) {
            $this->conn             = $conn;
            $this->index            = $index;
            $header                 = imap_header($conn, $index);            
            var_dump($header);
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
            $this->bodyReturn       = $this->getBody($conn, $index);//Mensagem a ser reenviada no final do script
            $this->overview         = imap_fetch_overview($conn,$index,0);            
            $this->structure        = @imap_fetchstructure($conn, $index);            
            if (!function_exists("imap_qprint")) {
                die('Não existe');
            }
            $body                   = imap_qprint(imap_fetchbody($conn,$index,"1")); ## GET THE BODY OF MULTI-PART MESSAGE
            //if(!$body) {$body = '[Nenhuma mensagem foi enviada]\n\n';}                            
            //$msg = imap_qprint($body);
        }
        
        private function getBody() {
            $arrHtmlMap = $this->arrHtmlMap;
            $format = 'html';
            $body   = $this->getPart("TEXT/HTML");
            // if HTML body is empty, try getting text body
            if ($body == "") {
                $format = 'text_plain';
                $body = $this->getPart("TEXT/PLAIN");
            }

            foreach($arrHtmlMap as $key=>$value) {
                $cod    = "#{$key}:";   
                $value  = utf8_decode($value);
                if ($format == 'html') $value = "<b>$value</b>";
                $body   = str_replace($cod,$value,$body);            
            }
            return $body;
        }   
                
        private function getPart($mimetype, $structure = false, $partNumber = false) {
            $conn   = $this->conn;
            $index  = (int)$this->index;
            
            if (!$structure) {
                $structure = imap_fetchstructure($conn, $index, FT_UID);
            }
            if ($structure) {
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
    }
?>
