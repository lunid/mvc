<?php
    include('../sys/vendors/db/Meekrodb_2_2.php');
    require ('class/Conn.php');
    require ('../sys/vendors/PHPMailer/PHPMailerAutoload.php');
    require ('class/Imap.php');
    require ('class/MailMessage.php');
    require ('class/PseudoLinguagem.php');
    
    $idAssinatura           = 1;
    $replyTo                = 'claudio@supervip.com.br';
    $excluirMsgAposGravar   = false;
    
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
    
    $arrResumo  = array();//Guarda o resultado de cada mensagem rastreada e permite enviar um e-mail resumido no final   
    
    $arrCodMap      =  array(
      'title'       => 'tt, título',
      'descryption' => 'ds,descrição',      
      'tasks'       => 'tks,tarefas',
      'deadline'    => 'dl,data de conclusão',
      'tag'         => 'tags,palavra-chave, palavras-chave',
      'memo'        => 'memo'
    );
    
    $arrHtmlMap     = array(
        'tks'   => 'Tarefas:',
        'ds'    => 'Descrição:',
        'tag'   => 'Palavras-chave:',
        'memo'  => ''
    );
        
    $arrType = array('memo','chore','release','feature','bug','none');//Tipos possíveis de mensagem
    
    foreach($arrCodMap as $key=>$value){
        $arrCod[] = $key;
    }
    
    $strCodKey      = join(',',$arrCod);
    $strCodValue    = join(',',$arrCodMap);
    if (strlen($strCodValue) > 0) $strCodKey .= ','.$strCodValue;
    $arrPseudoCod = explode(',',$strCodKey);

    try {
        $server     = 'pop.supervip.com.br';
        $port       = '110';
        $user       = 'project@supervip.com.br';
        $passwd     = 'senha3040';        
        $objImap = new Imap($server,$port,$user,$passwd);        
        $objImap->loadAllMessages($idAssinatura);  
    } catch(\Exception $e) {
        die($e->getMessage());
    }
    die();
    
    /*
    try {
        $mbox = imap_open("{{$server}:{$port}/pop3/novalidate-cert}INBOX", $user, $passwd)
             or die('Não foi possível estabelecer conexão com o servidor iMap: '.imap_last_error());
    } catch (Exception $e) {
        die('Servidor de e-mail não disponível.');
    }
    */
    
    if ($mbox) {
        
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
            //$overview               = imap_fetch_overview($mbox,$msgId,0);            
            $bodyArray              = quoted_printable_decode(imap_fetchbody($mbox,$msgId,"1")); ## GET THE BODY OF MULTI-PART MESSAGE
            if(!$bodyArray) {$bodyArray = '[Nenhuma mensagem foi enviada]\n\n';}           
            
            //echo $bodyReturn;
            
            //Substitui imagens inline na mensagem, se houver:
            //http://www.electrictoolbox.com/php-email-extract-inline-image-attachments/
            preg_match_all('/src="cid:(.*)"/Uims', $bodyReturn, $matches);
            if (is_array($matches) && count($matches[0]) > 0) {
                //print_r($matches);
                $search = array();
                $replace = array();

                foreach($matches[1] as $match) {
                        $uniqueFilename = "A UNIQUE_FILENAME.extension";
                        file_put_contents("images/email/$uniqueFilename", $emailMessage->attachments[$match]['data']);
                        $search[] = "src=\"cid:$match\"";
                        $replace[] = "src=\"http://dev.mvc.com/teste/images/email/$uniqueFilename\"";
                }
                $bodyReturn = str_replace($search, $replace, $bodyReturn);  
                echo $bodyReturn;
            }
            /*
            $intStatic = 2;//to initialize the mail body section
            $strImgageInline   = imap_fetchbody($mbox, $msgId,"");             
            
            $numImagesInline    = substr_count($strImgageInline,"Content-Transfer-Encoding: base64");//to get the no of images 
            
            if($numImagesInline > 0){
                for($i = 0; $i < $numImagesInline; $i++){
                    $strChange  = strval($intStatic+$i); 
                    $decode     = imap_fetchbody($mbox, $msgId , $strChange);//to get the base64 encoded string for the image 
                    $data       = base64_decode($decode);                   
                    $fName      = time()."_".$strChange . '.gif'; 
                    $file       = 'images/email/'.$fName; 
                    $success    = file_put_contents($file, $data); 
                    if ($success) {
                        //$bodyReturn = str_replace('cid:image002.jpg', $file, $bodyReturn);
                        //echo $bodyReturn;                        
                    }
                    echo "<img src='{$file}'><br/>";
                }
            }
            */
            
            /*
            $strImg         = imap_fetchbody($mbox, $msgId,3);            
            $decoded_data   = base64_decode($strImg);    
            if (strlen($decoded_data) > 0) {
                $img = $targetFolder.'/image_'.$msgId.'.jpg';
                file_put_contents($img, $decoded_data);
                echo "<img src='{$img}'><br/>";
            }
            */
               
            $struct          = imap_fetchstructure($mbox,$msgId,FT_UID);             
           
            $titulo     = utf8_decode(iconv_mime_decode($message['subject'],0,"UTF-8"));               
            $type       = 'NONE';
            
            //Converte data para o formato Y-m-d H:i:s
            $data       = strtotime($message['date']);
            $dataHoraEn = date("Y-m-d H:i:s", $data);
            
            $size       = (int)$message['size'];
            $tituloDb   = utf8_encode($titulo);
            //echo $tituloDb.'<br>';
            //Verifica se a mensagem atual já foi cadastrada.
            $sql = "SELECT COUNT(*) AS TOTAL_MSG FROM SVIP_EMOP_MSG WHERE 
            ID_ASSINATURA = $idAssinatura AND TAM_BYTES = $size AND TITULO = '$tituloDb' AND DATA_HORA_ENVIO = '$dataHoraEn'";

            $result     = DB::query($sql);
            $msgJaCad   = (int)$result[0]['TOTAL_MSG'];
            if ($msgJaCad > 0) continue;//Mensagem já cadastrada
            
            $msg        = imap_qprint($bodyArray);
            $arrMsg     = explode("\n",$msg);            
            
            $arrTag         = array();
            $arrTask        = array();            
            $codKey         = '';//Índice associativo do arrTag
            
            if (is_array($arrMsg)) {
                $tam = count($arrMsg);
  
                for($i=0; $i < $tam; $i++) {
                    $line       = trim($arrMsg[$i]);
                    
                    //Localiza o tipo da mensagem (sempre definido na primeira linha):
                    $typeCheck  = str_replace('#', '', strtolower($line));
                    $typeCheck  = str_replace(':', '', $typeCheck);
                    $posType    = array_search($typeCheck,$arrType);
                    if ($posType !== false) $type = strtoupper($arrType[$posType]);
      
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
                        
            $deadline       = '';
            $tags           = '';
            $obs            = '';
            $descricao      = '';
                        
            if (isset($arrTag['descryption'])) $descricao = $arrTag['descryption'];
            if (isset($arrTag['deadline'])) $deadline = $arrTag['deadline'];
            if (isset($arrTag['tag'])) $tags = $arrTag['tag'];
            
            $autor  = $message['fromaddress'];
            $to     = $message['toaddress'];
            $copy   = $message['ccaddress'];           
            
            /*
             * Grava dados no DB
             */
            
            try {
                //Mensagem ainda não cadastrada.               
                DB::startTransaction();
                DB::replace('SVIP_EMOP_MSG', array(
                  'ID_ASSINATURA' => $idAssinatura,
                  'TAM_BYTES' => $size,
                  'MESSAGE_ID' => $messageId,
                  'DATA_HORA_ENVIO' => $dataHoraEn,
                  'TIPO' => $type, 
                  'TITULO' => $tituloDb,
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

                $numTarefas = 0;
                $idMsg      = DB::insertId();
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
                        $numTarefas = count($arrTasks);
                    }
                    DB::commit();
                    
                    //Verifica anexos do e-mail:
                    existAttachment($struct,$idMsg); 
                    
                    $toName = 'Claudio Rubens';
                    $toMail = 'claudio@supervip.com.br';
                    $titulo = str_replace('#:','',$titulo);
                    //$emailResend = sendEmail($fromName, $fromEmail, $toName, $toMail, $titulo, $bodyReturn);
                    $emailResend = false;
                    
                    if ($emailResend === true) {
                        $arrResumo[] = array($titulo,$numTarefas);
                        if ($excluirMsgAposGravar === true) {
                            //Excluir mensagem da caixa postal
                            imap_delete($mbox, $msgId);
                            imap_expunge($mbox);
                        }
                    }
                    echo 'Mensagem cadastrada com sucesso.<br>';
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

        $totalMsg = count($arrResumo);
        if ($totalMsg > 0) {
            //Envia e-mail resumido das mensagens rastreadas.
            $msgResumo = '';
            foreach($arrResumo as $row){ 
                $tituloMsg  = $row[0];
                $numTarefas = (int)$row[1]; 
                $mTarefas   = ($numTarefas > 0) ? " [$numTarefas tarefas]" : '';
                $msgResumo  .= " - {$tituloMsg} {$mTarefas}<br/>";
            }
            
            $toName         = $fromName;
            $toMail         = $fromEmail;
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
        
    } else {
        echo 'Não há mensagens';
    }
    imap_close($mbox);
    
    function sendEmail($fromName, $fromMail, $toName, $toMail, $titulo, $msg){
        $mail = new PHPMailer;
        
        $mail->isSMTP();                                      // Set mailer to use SMTP
        //$mail->SMTPDebug = 2;
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

        //$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $titulo;
        $mail->Body    = $msg;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        
        $emailEnviado   = $mail->send();
        
        if(!$emailEnviado) {
           echo 'Message could not be sent.';
           echo 'Mailer Error: ' . $mail->ErrorInfo;
           exit;
        }
        
        return $emailEnviado;
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
    


    //http://sidneypalmeira.wordpress.com/2011/07/21/php-como-ler-um-e-mail-e-salvar-o-anexo-via-imap/
    function existAttachment($part,$idMsg){
        global $arrAnexo;
        if (isset($part->parts)){ 
            foreach ($part->parts as $partOfPart){ 
                existAttachment($partOfPart,$idMsg); 
            } 
        } else {                         
            if (property_exists($part,'disposition')) {                
                if (strtoupper($part->disposition) == 'ATTACHMENT'){ 
                    //echo '<p>' . $part->dparameters[0]->value . '</p>'; 
                    $file = $part->dparameters[0]->value;
                    //echo "<a href='$file'>$file</a><br/>";
                    if (gravaAnexo($file)){
                        //Arquivo gravado com sucesso. Grava no DB
                        DB::insert('SVIP_EMOP_ANEXO', array(
                          'ID_EMOP_MSG' => $idMsg,
                          'NOME_ARQ' => $file,                          
                          'DATA_REGISTRO' => DB::sqleval("NOW()")
                        ));                        
                    }                    
                } 
            } 
        }
        
        return false;
    }
    

?>
