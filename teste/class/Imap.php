<?php

    class Imap {
        private $host;
        private $conn;
        private $totalMsg;
        private $totalNaoLidas;
        private $arrMailbox = array();
        private $excluirMsgAposLoad = FALSE;
        private $enviarResumo       = TRUE;
        
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
        
        function excluirMsgAposLoad($action){
            $this->excluirMsgAposLoad = (boolean)$action;
        }        
        
        function enviarResumo($action){
            $this->enviarResumo = (boolean)$action;
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
            $arrSendMail    = array();
            
            if ($conn) {
                if ($totalMsg > 0) {
                    for ($index = 1; $index <= $totalMsg; $index++) { 
                        // Carrega mensagem atual do servidor.
                        $objMailMessage = new MailMessage($idAssinatura, $conn,$index);
                        //if ($objMailMessage->verifEmailJaCadastrado() && !$forceLoadAll) continue;
                        
                        //Salva mensagem no DB
                        $idMessage          = $objMailMessage->save();                                                                        
                        if ($idMessage > 0) {
                            //Mensagem gravada com sucesso. 
                            //Localiza dados de pseudo-linguagem na mensagem atual.
                            $arrDadosParse      = $objMailMessage->getParsePseudoLinguagem();
                            
                            //Persiste no DB os dados de pseudo-linguagem.
                            $arrResumo                = $this->persistDadosParsePseudoLing($arrDadosParse, $idMessage);
                            $arrResumo['ASSUNTO']     = $objMailMessage->getAssunto();
                            $arrResumo['TO_NAME']     = $objMailMessage->getFromName();
                            $arrResumo['TO_EMAIL']    = $objMailMessage->getFromEmail();
                                    
                            if ($this->excluirMsgAposLoad === true) {
                                //Excluir mensagem da caixa postal
                                $objMailMessage->del();
                            }
                           
                        }
                        $arrSendMail[]          = $arrResumo;
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
         * @param mixed[] $arrDadosParse
         * @return mixed[] Array com resumo dos dados persistidos.
         */
        private function persistDadosParsePseudoLing($arrDadosParse, $idMessage){
            $arrDados   = array();
            $arrReturn  = array();
            
            $arrReturn['NUM_TAREFAS']   = 0;
            $arrReturn['NUM_MEMO']      = 0;
            
            if ($idMessage == 0) return FALSE;
            
            if (is_array($arrDadosParse)) {
                foreach($arrDadosParse as $key => $value) {                   
                    if (is_array($value)) {
                        if ($key == 'TAREFAS') {
                            if (is_array($value)) {
                                $arrTarefas = $value;
                                foreach($arrTarefas as $tarefa) {                            
                                    $rows[] = array(
                                        'ID_EMOP_MSG' => $idMessage,
                                        'TAREFA' => utf8_encode($tarefa),
                                        'DATA_REGISTRO' => DB::sqleval("NOW()")
                                    );
                                }
                                DB::insert('SVIP_EMOP_TAREFA', $rows);
                                $numTarefas = count($arrTarefas);
                                $arrReturn['NUM_TAREFAS'] = $numTarefas;
                            }
                        }
                    } elseif ($key == 'MEMO') {
                        
                    } else {
                        $arrDados[$key] = $value;
                    }
                }
            }
            
            if (count($arrDados) > 0) {
                //Faz um update no registro da mensagem atual.
                DB::update('SVIP_EMOP_MSG', $arrDados, "ID_EMOP_MSG=%i", $idMessage);
            }
            return $arrReturn;
        }
        
        function sendMailResumo($arrResumo){
            if (is_array($arrResumo) && count($arrResumo) > 0) {
                //Envia e-mail resumido das mensagens rastreadas.
                $msgResumo = '';
                foreach($arrResumo as $row){ 
                    $assunto    = $row['ASSUNTO'];
                    $numTarefas = (int)$row['NUM_TAREFAS']; 
                    $mTarefas   = ($numTarefas > 0) ? " [$numTarefas tarefa(s)]" : '';
                    $msgResumo  .= " - {$assunto} {$mTarefas}<br/>";
                }

                $toName         = $row['TO_NAME'];
                $toMail         = $row['TO_EMAIL'];
                $toMail         = 'claudio@supervip.com.br';
                $fromName       = 'e-MOP';
                $fromEmail      = 'project@supervip.com.br';            
                $tituloResumo   = "{$totalMsg} mensagens rastreadas";
                //echo "$fromEmail - $toMail";
                //die();
                $bodyReturn     = '<b>Mensagens rastreadas com sucesso:</b><br/>'.$msgResumo;
                $emailResumo    = sendEmail($fromName, $fromEmail, $toName, $toMail, $tituloResumo, $bodyReturn);
                if ($emailResumo) {
                    echo "Resumo enviado com sucesso!";
                } else {
                    echo "Não foi possível enviar o resumo.";
                }
            } else {
                echo "Nenhum resumo foi gerado.";
            }             
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
