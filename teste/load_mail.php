<?php
    $server = 'pop.supervip.com.br';
    $port   = '110';
    $user   = 'claudio@supervip.com.br';
    $passwd = 'adm91100x';
    
    $mbox = imap_open("{{$server}:{$port}/pop3/novalidate-cert}INBOX", $user, $passwd);
    if ($mbox) {
        $totalMsg = imap_num_msg($mbox);
        for ($msgId = 1; $msgId <= $totalMsg; $msgId++) {
            $header                 = imap_header($mbox, $msgId);
            $message['subject']     = $header->subject;
            $message['fromaddress'] = $header->fromaddress;
            $message['toaddress']   = $header->toaddress;
            $message['ccaddress']   = $header->ccaddress;
            $message['date']        = $header->date;
            $message['body']        = imap_fetchbody($mbox,$msgId,"1"); ## GET THE BODY OF MULTI-PART MESSAGE
            if(!$message['body']) {$message['body'] = '[Nenhuma mensagem foi enviada]\n\n';}
            echo  imap_qprint($message['body']);
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
?>
