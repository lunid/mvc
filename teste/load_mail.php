<?php
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
    
    foreach($arrCodMap as $key=>$value){
        $arrCod[] = $key;
    }
    
    $strCodKey      = join(',',$arrCod);
    $strCodValue    = join(',',$arrCodMap);
    if (strlen($strCodValue) > 0) $strCodKey .= ','.$strCodValue;
    $arrPseudoCod = explode(',',$strCodKey);


    
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
            
            //Converte data para o formato Y-m-d H:i:s
            $arrDate    = date_parse($message['date']);            
            $dataHoraEn = $arrDate['year'].'-'.$arrDate['month'].'-'.$arrDate['day'].' '.$arrDate['hour'].':'.$arrDate['minute'].':'.$arrDate['second'];
            
            $msg        = imap_qprint($message['body']);
            $arrMsgTk   = $arrMsg = explode("\n",$msg);                 
            //$arrMsg     = array_filter($arrMsgTk, "delLinhaVazia");
            //$arrMsg     = array_values($arrMsg);
            $arrTag     = array();
            $arrTask    = array();
            $codKey     = '';//Índice associativo do arrTag
            
            if (is_array($arrMsg)) {
                $tam = count($arrMsg);
  
                for($i=0; $i < $tam; $i++) {
                    $line = trim($arrMsg[$i]);
 
                    foreach($arrPseudoCod as $cod) {
                        $codLang    = '#'.$cod.':';
                        $codKey     = '';
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
                                    //$line = preg_replace("/\s\s+/", "",$line);

                                    if (strlen($line) == 0) continue;   
                                    $char1  = ord($line[0]);//Primeiro caractere da linha
                                    
                                    if (preg_match("/^-/", $line) || $char1 == 183){                                            
                                        $line   = preg_replace("/\s\s+/", "",$line);//retira espaços vazios adicionais
                                        $line   = preg_replace("/^(-)\s?/", "",$line);//retira hífen no início da linha com ou sem espaço à direita
                                        $line = preg_replace("/[^\x01-\x7F]/","", $line);//remove qualquer caractere não ASCII
 
                                        $arrTask[]  = $line;
                                    } else {                                                    
                                        if (preg_match("/^#[[:alpha:]]{2,3}:/", $line)) $i--;//Se for uma tag (#..:) volta um item no loop                                       
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
                                       $line = trim($arrMsg[++$i]);                                      
                                       if (strlen($line) == 0) continue; //Ignora linha vazia  
                                       $arrTag[$codKey] = $line;
                                       $fimLoop = true;
                                   }                                                                           
                               }
                          }
                        }
                    }
                }
            }
            
            $type           = 'chore';
            $deadline       = '';
            $tags           = '';
            $memo           = '';
            $descryption    = '';
            
            if (isset($arrTag['type'])) $type = $arrTag['type'];
            if (isset($arrTag['descryption'])) $descryption = $arrTag['descryption'];
            if (isset($arrTag['deadline'])) $deadline = $arrTag['deadline'];
            if (isset($arrTag['tag'])) $tags = $arrTag['tag'];
            
            $author = $message['fromaddress'];
            $to     = $message['toaddress'];
            $copy   = $message['ccaddress'];           
                                
            echo "Data/hora:{$dataHoraEn} <br/>";
            echo "Tipo:{$type} <br/>";
            echo "Descrição:{$descryption} <br/>";
            echo "Autor:{$author} <br/>";
            echo "De:{$author} para: {$to}<br/>";
            echo "Cópia para: {$copy}<br/>";
            echo "Prazo/conclusão:{$deadline}<br/>";
            echo "Tags:{$tags}<br/>";
            echo "Memo:{$memo}<br/>";
            
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
?>
