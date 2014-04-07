<?php

    class Imap {
        private $host;
        private $conn;
        private $totalMsg;
        private $totalNaoLidas;
        private $arrMailbox = array();
        
        function __construct($server,$port,$login,$password) {
            try {
                $host   = "{{$server}:{$port}/pop3/novalidate-cert}";
                $conn   = @imap_open($host."INBOX", $login, $password);
                if (FALSE === $conn) {
                    throw new Exception('Não foi possível estabelecer conexão com o servidor iMap: ' . imap_last_error());
                }

                $this->host             = $host;
                $this->conn             = $conn;
                $this->totalMsg         = imap_num_msg($conn);  
                $this->totalNaoLidas    = imap_num_recent($conn);                                
                
            } catch (Exception $e) {
                throw new Exception('Servidor de e-mail não disponível.');
            }            
        }
        
        function getMailboxes($tipoRet='*'){
            $host       = $this->host;  
            $conn       = $this->conn;
            $arrMailbox = array();
            $folders    = imap_list($conn, "{$host}", $tipoRet);
            
            if (is_array($folders)) {
                foreach ($folders as $folder) {
                    $arrMailbox[] = str_replace($host, "", imap_utf7_decode($folder));                    
                }
                
                $this->arrMailbox = $arrMailbox;
            } else {
                echo "imap_list failed: " . imap_last_error() . "\n";
            }            
        }
        
        function loadAllMessages($idAssinatura, $forceLoadAll=FALSE){
            $conn           = $this->conn;
            $totalMsg       = (int)$this->totalMsg;
            $arrMailMessage = array();
            
            if ($conn) {
                if ($totalMsg > 0) {
                    for ($index = 1; $index <= $totalMsg; $index++) {           
                        $objMailMessage = new MailMessage($idAssinatura, $conn,$index);
                        //if ($objMailMessage->verifEmailJaCadastrado() && !$forceLoadAll) continue;
                        
                        $idMessage          = $objMailMessage->save();                                                                        
                        if ($idMessage > 0) {
                            $arrDadosPseudoLing = $objMailMessage->parsePseudoLinguagem();
                            $arrDadosPseudoLing = $this->persistDadosPseudoLing($arrDadosPseudoLing);
                            $arrDadosMessage    = $objMailMessage->getDados();
                        }
                        
                        $arrMailMessage[$index] = $objMailMessage;
                    }
                } else {
                    echo 'Não há mensagens';
                }
            } else {
                throw new Exception('Conexão iMap inexistente.');
            }
        }
        
        /**
         * Recebe dados extraídos da pseudo-linguagem e, dependendo do grupo,
         * persiste no DB.
         * 
         * @param mixed[] $arrDadosPseudoLing
         * @return type
         */
        private function persistDadosPseudoLing($arrDadosPseudoLing){
            $arrDadosPseudoLingReturn = array();
            if (is_array($arrDadosPseudoLing)) {
                foreach($arrDadosPseudoLing as $key => $value) {                   
                    if (is_array($value)) {
                        if ($key == 'TAREFAS') {
                            if (is_array($value)) {
                                print_r($value);
                            }
                        }
                    } else {
                        $arrDadosPseudoLingReturn[] = $value;
                    }
                }
            }
            die();
            return $arrDadosPseudoLingReturn;
        }
        
        public function recurse($messageParts, $prefix = '', $index = 1, $fullPrefix = true) {
            foreach($messageParts as $part) {			
                $partNumber = $prefix . $index;			
                if($part->type == 0) {
                    if($part->subtype == 'PLAIN') {
                        $this->bodyPlain .= $this->getPart($partNumber, $part->encoding);
                    }
                    else {
                        $this->bodyHTML .= $this->getPart($partNumber, $part->encoding);
                    }
                } elseif($part->type == 2) {
                    $msg = new EmailMessage($this->connection, $this->messageNumber);
                    $msg->getAttachments = $this->getAttachments;
                    $msg->recurse($part->parts, $partNumber.'.', 0, false);
                    $this->attachments[] = array(
                            'type' => $part->type,
                            'subtype' => $part->subtype,
                            'filename' => '',
                            'data' => $msg,
                            'inline' => false,
                    );
                } elseif(isset($part->parts)) {
                    if($fullPrefix) {
                        $this->recurse($part->parts, $prefix.$index.'.');
                    } else {
                        $this->recurse($part->parts, $prefix);
                    }
                } elseif($part->type > 2) {
                    if(isset($part->id)) {
                        $id = str_replace(array('<', '>'), '', $part->id);
                        $this->attachments[$id] = array(
                                'type' => $part->type,
                                'subtype' => $part->subtype,
                                'filename' => $this->getFilenameFromPart($part),
                                'data' => $this->getAttachments ? $this->getPart($partNumber, $part->encoding) : '',
                                'inline' => true,
                        );
                    } else {
                        $this->attachments[] = array(
                                'type' => $part->type,
                                'subtype' => $part->subtype,
                                'filename' => $this->getFilenameFromPart($part),
                                'data' => $this->getAttachments ? $this->getPart($partNumber, $part->encoding) : '',
                                'inline' => false,
                        );
                    }
                }
                $index++;			
            }		
	}        
    }
?>
