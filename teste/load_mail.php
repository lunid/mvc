<?php
    $server = 'pop.supervip.com.br';
    $port   = '110';
    $user   = 'project@supervip.com.br';
    $passwd = 'senha3040';
    
    $arrPseudoCod = array('title','tt','ds','task','tk','tasks','tks','type','tp','deadline','dl','tag');
    
    $mbox = imap_open("{{$server}:{$port}/pop3/novalidate-cert}INBOX", $user, $passwd);
    if ($mbox) {
        $totalMsg = imap_num_msg($mbox);
        for ($msgId = 1; $msgId <= $totalMsg; $msgId++) {
            $header                 = imap_header($mbox, $msgId);
            $message['subject']     = $header->subject;
            $message['fromaddress'] = $header->fromaddress;
            $message['toaddress']   = $header->toaddress;
            $message['ccaddress']   = (isset($header->ccaddress)) ? $header->ccaddress : '';
            $message['date']        = $header->date;
            $message['body']        = imap_fetchbody($mbox,$msgId,"1"); ## GET THE BODY OF MULTI-PART MESSAGE
            if(!$message['body']) {$message['body'] = '[Nenhuma mensagem foi enviada]\n\n';}
            
            $titulo     = utf8_decode(iconv_mime_decode($message['subject'],0,"UTF-8"));           
            $msg        = imap_qprint($message['body']);
            $arrMsgTk   = $arrMsg = explode("\n",$msg);                 
            //$arrMsg     = array_filter($arrMsgTk, "delLinhaVazia");
            //$arrMsg     = array_values($arrMsg);
            $arrTag     = array();
            $arrTask    = array();
            
            if (is_array($arrMsg)) {
                $tam = count($arrMsg);
  
                for($i=0; $i < $tam; $i++) {
                    $line = trim($arrMsg[$i]);
                    foreach($arrPseudoCod as $cod) {
                        $codLang = '#'.$cod.':';
                        
                        $key = strpos($line,$codLang);
                        if ($key !== false) {
                           //Encontrou uma tag na linha atual
                          //echo $codLang. $line.'...<br>';                           
                          $fimLoop = false;
                          if ($codLang == '#tks:') {
                               //Localiza as tarefas nas linhas seguintes:                             
                               while(!$fimLoop){
                                    $line = trim($arrMsg[++$i]);
                                    if (strlen($line) == 0) continue;   
  
                                    if (preg_match("/^-/", $line) || preg_match("/^·/", $line)){
                                        $arrTask[] = $line;
                                    } else {                                                    
                                        if (preg_match("/^#[[:alpha:]]{2,3}:/", $line)) $i--;
                                        $str = str_split($line);
                                        echo $line.'<br>';
                                        foreach($str as $char) {
                                            echo chr(ord($char)).'<br>';
                                        }
                                        $fimLoop = true;
                                    }
                               }
                               $arrTag[$codLang] = $arrTask;
                          } else {                              
                               $arrPartTag = explode($codLang,$line);
                               if (isset($arrPartTag[1]) && strlen($arrPartTag[1]) > 0) {
                                   //O conteúdo do pseudo-código está na mesma linha.                               
                                   $arrTag[$codLang] = $arrPartTag[1];
                                   //if (strlen($arrTag[1]) == 0) $arrTag[$codLang] = $arrMsg[++$i];
                               } else {
                                   while(!$fimLoop){
                                       $line = trim($arrMsg[++$i]);
                                       if (strlen($line) == 0) continue;    
                                       $arrTag[$codLang] = $line;
                                       $fimLoop = true;
                                   }                                                                           
                               }
                          }
                        }
                    }
                }
            }
            print_r($arrTag);
            die();

            /*
             * 0 - Message header
             * 1 - MULTIPART/ALTERNATIVE
             * 1.1 - TEXT/PLAIN
             * 1.2 - TEXT/HTML
             * 2 - MESSAGE/RFC822 (entire attached message)
             * 2.0 - Attached message header
             * 2.1 - TEXT/PLAIN
             * 2.2 - TEXT/HTML
             * 2.3 - file.ext
             *  o terceiro parametro pode ser
             *  0=> retorna o body da mensagem com o texto que o servidor recebe
             *  1=> retorna somente o conteudo da mensagem em plain-text
             *  2=> retorna o conteudo da mensagem em html
             */
            
            echo "<hr />";
            $body_1 = ( imap_fetchbody($mbox, $msg, 1) );
            echo $body_1;

            echo "<hr />";
            $body_0 = ( imap_fetchbody($mbox, $msg, 0) );
            echo $body_0;

            echo "<hr />";
            $body_2 = ( imap_fetchbody($mbox, $msg, 2) );
            echo $body_2;

            echo "<hr />";            
            die();
        }
    } else {
        echo 'Não há mensagens';
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
?>
