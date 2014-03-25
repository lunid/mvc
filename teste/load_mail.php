<?php
    include('../sys/vendors/db/Meekrodb_2_2.php');
    require '../sys/vendors/PHPMailer/PHPMailerAutoload.php';
    
    $idAssinatura   = 1;
    $replyTo        = 'claudio@supervip.com.br';
    
    error_reporting(-1);
    try {
        DB::$error_handler = false; // since we're catching errors, don't need error handler
        DB::$throw_exception_on_error = true;        
        DB::$user = 'supervip27';
        DB::$password = 'senha3040';
        DB::$dbName = 'supervip27';
        DB::$host = '186.202.152.137'; //defaults to localhost if omitted        
        DB::$encoding = 'utf8'; // defaults to latin1 if omitted    

    } catch(MeekroDBException $e) {
        echo "Error: " . $e->getMessage() . "<br>\n"; // something about duplicate keys
        echo "SQL Query: " . $e->getQuery() . "<br>\n"; // INSERT INTO accounts...        
        die();
    }
    
    $server = 'pop.supervip.com.br';
    $port   = '110';
    $user   = 'project@supervip.com.br';
    $passwd = 'senha3040';
       
    $arrCodMap      =  array(
      'title'       => 'tt, título',
      'descryption' => 'ds,descrição',
      'task'        => 'tk,tarefa',
      'tasks'       => 'tks,tarefas',
      'type'        => 'tp,tipo',
      'deadline'    => 'dl,data de conclusão',
      'tag'         => 'tags,palavra-chave, palavras-chave'        
    );
    
    $arrHtmlMap     = array(
        'tk'    => 'Tarefa:',
        'tks'   => 'Tarefas:',
        'ds'    => 'Descrição:',
        'tag'   => 'Palavras-chave:'
    );
    
    foreach($arrCodMap as $key=>$value){
        $arrCod[] = $key;
    }
    
    $strCodKey      = join(',',$arrCod);
    $strCodValue    = join(',',$arrCodMap);
    if (strlen($strCodValue) > 0) $strCodKey .= ','.$strCodValue;
    $arrPseudoCod = explode(',',$strCodKey);


    
    $mbox = imap_open("{{$server}:{$port}/pop3/novalidate-cert}INBOX", $user, $passwd);
    if ($mbox) {
        $totalMsg           = imap_num_msg($mbox);  
        $totalMsgNaoLidas   = imap_num_recent($mbox);        
        
        for ($msgId = 1; $msgId <= $totalMsg; $msgId++) {
            $header                 = imap_header($mbox, $msgId);
            
            $from                   = $header->from[0];
            
            //Info Remetente:
            $fromName               = $from->personal;
            $fromMailbox            = $from->mailbox;
            $fromHost               = $from->host;
            $fromEmail              = "$fromMailbox@$fromHost";

            $message['subject']     = $header->subject;
            $message['fromaddress'] = $header->fromaddress;
            
            $message['toaddress']   = $header->toaddress;
            $message['ccaddress']   = (isset($header->ccaddress)) ? $header->ccaddress : '';
            $message['date']        = $header->date;
            $message['size']        = $header->Size;//Em bytes
            $messageId              = $header->message_id;     
            $bodyReturn             = getBody($msgId,$mbox);//Mensagem a ser reenviada no final do script

            $bodyArray              = quoted_printable_decode(imap_fetchbody($mbox,$msgId,"1")); ## GET THE BODY OF MULTI-PART MESSAGE
            if(!$bodyArray) {$bodyArray = '[Nenhuma mensagem foi enviada]\n\n';}
            
            //$struct                 = imap_fetchstructure($mbox,$msgId,FT_UID); 
            //$existAttachments       = existAttachment($struct); 
            //echo $struct;
            //die();
           
            $titulo     = utf8_decode(iconv_mime_decode($message['subject'],0,"UTF-8"));               
            
            //Converte data para o formato Y-m-d H:i:s
            $data       = strtotime($message['date']);
            $dataHoraEn = date("Y-m-d H:i:s", $data);
            
            $msg        = imap_qprint($bodyArray);
            $arrMsg     = explode("\n",$msg);            
            
            $arrTag         = array();
            $arrTask        = array();
            $arrBodyReturn  = array();
            $codKey         = '';//Índice associativo do arrTag
            
            if (is_array($arrMsg)) {
                $tam = count($arrMsg);
  
                for($i=0; $i < $tam; $i++) {
                    $line   = trim($arrMsg[$i]);
                    //$lineR  = $line;
                    foreach($arrPseudoCod as $cod) {                        
                        $codLang            = '#'.$cod.':';
                        $codKey             = '';
                        if (isset($arrCodMap[$cod])) {
                            $codKey = $cod;
                        } else {
                            $codKey = checkCodMap($arrCodMap,$cod);
                            if ($codKey === FALSE) continue;//A tag informada não existe.
                        }

                        $key = strpos($line,$codLang);
                        if ($key !== false) {
                           //Encontrou uma tag na linha atual
                          //echo $codLang. $line.'...<br>';                           
                          $fimLoop = false;
                          if ($codLang == '#tks:' || $codLang == '#tasks:') {
                               //Localiza as tarefas nas linhas seguintes:                             
                               while(!$fimLoop){
                                    $line   = trim($arrMsg[++$i]);  
                                    if (strlen($line) == 0) continue;

                                    
                                    $char1  = ord($line[0]);//Primeiro caractere da linha

                                    if (preg_match("/^-/", $line) || $char1 == 183){                                            
                                        $line       = preg_replace("/\s\s+/", "",$line);//retira espaços vazios adicionais
                                        $line       = preg_replace("/^(-)\s?/", "",$line);//retira hífen no início da linha com ou sem espaço à direita
                                        //$line       = preg_replace("/[^\x01-\x7F]/","", $line);//remove qualquer caractere não ASCII
                                        //$lineR      = $line;
                                        $arrTask[]  = $line;
                                    } else {                                                    
                                        if (preg_match("/^#[[:alpha:]]{2,3}:/", $line)) {                                           
                                            $i--;//Se for uma tag (#..:) volta um item no loop                                       
                                        }
                                        $fimLoop = true;
                                    }
                               }
                               $arrTag[$codKey] = $arrTask;
                          } else {                                
                               $arrPartTag = explode($codLang,$line);
                               if (isset($arrPartTag[1]) && strlen($arrPartTag[1]) > 0) {
                                   //O conteúdo do pseudo-código está na mesma linha.                               
                                   $arrTag[$codKey] = $arrPartTag[1];
                                   //if (strlen($arrTag[1]) == 0) $arrTag[$codLang] = $arrMsg[++$i];
                               } else {
                                   $i++;//Avança uma linha.
                                   while(!$fimLoop){
                                       //Localiza a próxima linha com texto.                                       
                                       $line    = trim($arrMsg[++$i]);
                                       //$lineR   = $line;
                                       if (strlen($line) == 0) continue; //Ignora linha vazia  
                                       $arrTag[$codKey] = $line;
                                       $fimLoop = true;
                                   }                                                                           
                               }
                          }                          
                          //$lineR = str_replace($codLang, '', $lineR);
                        }
                    }
                    //if (strlen($lineR) > 0) $arrBodyReturn[]  = $lineR;
                }
            }
            
            /*
            $vazio = 0;
            foreach($arrMsg as $line) {
                if (strlen(trim($line)) == 0) {
                    $vazio++;
                    if ($vazio > 1) {
                        $vazio = 0;
                        continue;
                    }
                }
                $key  = strpos($line,'#tks:');
                $line = str_replace('#tks:','',$line);
                if (strlen(trim($line)) == 0 && $key !== false) {
                    //echo '<br/>';
                    continue;
                }
                echo $line.'<br/>';
            }
            die();
            $strBody = join($arrMsg,'<br/>');
            echo $strBody;
            die();
            */
            
            $tipo           = '';
            $deadline       = '';
            $tags           = '';
            $obs            = '';
            $descricao      = '';
            
            if (isset($arrTag['type'])) $tipo = $arrTag['type'];
            if (isset($arrTag['descryption'])) $descricao = $arrTag['descryption'];
            if (isset($arrTag['deadline'])) $deadline = $arrTag['deadline'];
            if (isset($arrTag['tag'])) $tags = $arrTag['tag'];
            
            $autor  = $message['fromaddress'];
            $to     = $message['toaddress'];
            $copy   = $message['ccaddress'];           
            $size   = (int)$message['size'];
            /*
             * Grava dados no DB
             */
            $tipoVal    = 'NONE';
            $arrTipo    = array('CHORE','RELEASE','BUG','FEATURE','MEMO');
            if (strlen($tipo) > 0) {
                $checkTipo  = strtoupper($tipo);
                $key        = array_search(strtoupper($checkTipo), $arrTipo);            
                if ($key !== false) $tipoVal = $checkTipo;
            }
            
            //Junta em um único array as tags #task e #tasks:
            if ((isset($arrTag['task']) && strlen($arrTag['task']) > 0) ||
                (isset($arrTag['task']) && strlen($arrTag['task']) == 0)
               ) {
                $arrTag['tasks'][] = $arrTag['task'];
            }

            try {
                DB::startTransaction();
                DB::replace('SVIP_EMOP_MSG', array(
                  'ID_ASSINATURA' => $idAssinatura,
                  'TAM_BYTES' => $size,
                  'MESSAGE_ID' => $messageId,
                  'DATA_HORA_ENVIO' => $dataHoraEn,
                  'TIPO' => $tipoVal, // duplicate primary key 
                  'TITULO' => utf8_encode($titulo),
                  'DESCRICAO' => utf8_encode($descricao),
                  'MENSAGEM' => utf8_encode($msg),
                  'AUTOR' => $autor,
                  'FROM_NAME' => $fromName,
                  'FROM_EMAIL' => $fromEmail,
                  'REMETENTE' => $autor,
                  'DESTINATARIO' => $to,
                  'CC' => $copy,
                  'CCO' => '',
                  'SEGUIDOR' => '',
                  'OBS' => $obs,
                  'TAG' => $tags,
                  'DEADLINE' => $deadline,
                  'DATA_REGISTRO' => DB::sqleval("NOW()")
                ));
                
                $idMsg = DB::insertId();
                if ($idMsg > 0) {
                    //Grava as tarefas da mensagem, se houver
                    $arrTasks = (isset($arrTag['tasks'])) ? $arrTag['tasks'] : null;
                    if (is_array($arrTasks)) {
                        $row = array();
                        foreach($arrTasks as $task) {                            
                            $rows[] = array(
                                'ID_EMOP_MSG' => $idMsg,
                                'TAREFA' => utf8_encode($task)
                            );
                        }
                        DB::insert('SVIP_EMOP_TAREFA', $rows);
                    }
                    DB::commit();
                    
                    $toName = 'Claudio Rubens';
                    $toMail = 'claudio@supervip.com.br';
                    $titulo = str_replace('#:','',$titulo);
    
                    sendEmail($fromName, $fromEmail, $toName, $toMail, $titulo, $bodyReturn);
                } else {
                    DB::rollback();
                }

            } catch(MeekroDBException $e) {
              echo "Error: " . $e->getMessage() . "<br>\n"; // something about duplicate keys
              echo "SQL Query: " . $e->getQuery() . "<br>\n"; // INSERT INTO accounts...
            }

            //$sql = "";
            /*
            echo "Data/hora:{$dataHoraEn} <br/>";
            echo "Tipo:{$type} <br/>";
            echo "Descrição:{$descricao} <br/>";
            echo "Autor:{$autor} <br/>";
            echo "De:{$autor} para: {$to}<br/>";
            echo "Cópia para: {$copy}<br/>";
            echo "Prazo/conclusão:{$deadline}<br/>";
            echo "Tags:{$tags}<br/>";
            echo "Memo:{$obs}<br/>";
            */
           
        }
    } else {
        echo 'Não há mensagens';
    }
    
    function sendEmail($fromName, $fromMail, $toName, $toMail, $titulo, $msg){
        $mail = new PHPMailer;
        
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->SMTPDebug = 2;
        //$mail->Debugoutput = 'html';
        $mail->Host = "smtp.supervip.com.br";
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        //$mail->Host = 'smtp.supervip.com.br';                 // Specify main and backup server
        //$mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'project@supervip.com.br';          // SMTP username
        $mail->Password = 'senha3040';                        // SMTP password
        //$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

        $mail->From = $fromMail;
        $mail->FromName = $fromName;
        $mail->addAddress($toMail, $toName);  // Add a recipient
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $titulo;
        $mail->Body    = $msg;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
           echo 'Message could not be sent.';
           echo 'Mailer Error: ' . $mail->ErrorInfo;
           exit;
        }

        echo 'Mensagem enviada com sucesso!';        
    }
    
    function checkCodMap($arrRoadMap,$codSearch){
        if (is_array($arrRoadMap) && strlen($codSearch) > 0) {
            foreach($arrRoadMap as $codKey => $cod) {
                $key = strpos($cod,$codSearch);
                if ($key !== false) {
                    return $codKey;
                }
            }
        }
        return false;
    }
    
    function getTask($line, $arrOut = array()){
        if (strlen(trim($line)) == 0) return false;
        $arrOut[] = $line;
        return $arrOut;
    }
    
    function delLinhaVazia($var){
        $str = trim($var);
        if (strlen($str) == 0) return false;
        return $var;
    }
    
    function getBody($uid, $imap) {
        global $arrHtmlMap;
        $format = 'html';
        $body   = get_part($imap, $uid, "TEXT/HTML");
        // if HTML body is empty, try getting text body
        if ($body == "") {
            $format = 'text_plain';
            $body = get_part($imap, $uid, "TEXT/PLAIN");
        }
       
        foreach($arrHtmlMap as $key=>$value) {
            $cod    = "#{$key}:";   
            $value  = utf8_decode($value);
            if ($format == 'html') $value = "<b>$value</b>";
            $body   = str_replace($cod,$value,$body);            
        }
        return $body;
    }    
    
    
    function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false) {
        if (!$structure) {
               $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID);
                switch ($structure->encoding) {
                    case 3: return imap_base64($text);
                    case 4: return imap_qprint($text);
                    default: return $text;
               }
           }

            // multipart 
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }    
    
    function get_mime_type($structure) {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if ($structure->subtype) {
           return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    //http://sidneypalmeira.wordpress.com/2011/07/21/php-como-ler-um-e-mail-e-salvar-o-anexo-via-imap/
    function existAttachment($part){ 
        if (isset($part->parts)){ 
            foreach ($part->parts as $partOfPart){ 
                existAttachment($partOfPart); 
            } 
        } 
        else{ 
            if (isset($part->disposition)){ 
                if ($part->disposition == 'attachment'){ 
                    echo '<p>' . $part->dparameters[0]->value . '</p>'; 
                    // here you can create a link to the file whose name is  $part->dparameters[0]->value to download it 
                    return true; 
                } 
            } 
        } 
    }     
?>
