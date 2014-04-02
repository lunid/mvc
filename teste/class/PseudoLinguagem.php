<?php

    class PseudoLinguagem {
        
        private $arrCodMap      =  array(
          'TITULO'      => 'title, tt, título',
          'DESCRICAO'   => 'description, ds,descrição',      
          'TAREFAS'     => 'tasks, tks,tarefas',
          'DEADLINE'    => 'deadline, dl,data de conclusão',
          'TAG'         => 'tag, tags,palavra-chave, palavras-chave',
          'MEMO'        => 'memo'
        );      

        private $arrType    = array('memo','chore','release','feature','bug','none');//Tipos possíveis de mensagem
        private $strBody    = '';
        
        function __construct($strBody){
            $this->strBody = $strBody;            
        }
        
        function extractActionsForString($strBodyInfo=''){
            $strBody        = (strlen($strBodyInfo) > 0) ? $strBodyInfo : $this->strBody;
            $arrMsg         = explode("\n",$strBody);  
            $arrType        = $this->arrType;
            $arrCodMap      = $this->arrCodMap;
            $arrTag         = array();
            $arrTask        = array();            
            $codKey         = '';//Índice associativo do arrTag
            $arrPseudoCod   = $this->getArrPseudoCod();

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
                            $codKey = $cod;
                            $codKey = $this->checkCodMap($arrCodMap,$cod);
                            if ($codKey === FALSE) continue;//A tag informada não existe.
                        }

                        $key = strpos($line,$codLang);
                        if ($key !== false) {
                           //Encontrou uma tag na linha atual
                          echo $codLang. $line.'...<br>';                           
                          $fimLoop = false;
                          if ($codLang == '#tks:' || $codLang == '#tasks:' || $codLang == '#tarefas:') {
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
            return $arrTag;
        }
        
        private function checkCodMap($arrRoadMap,$codSearch){
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

        private function getArrPseudoCod(){
            $arrCodMap = $this->arrCodMap;
            //foreach($arrCodMap as $key=>$value){
                //$arrCod[] = $key;
            //}

            //$strCodKey      = join(',',$arrCod);
            $strCodKey    = join(',',$arrCodMap);
            //if (strlen($strCodValue) > 0) $strCodKey .= ','.$strCodValue;
            $arrPseudoCod = explode(',',$strCodKey);
            //print_r($arrPseudoCod);
            //die();
            return $arrPseudoCod;
        }
    }
?>
