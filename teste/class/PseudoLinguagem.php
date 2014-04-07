<?php

    class PseudoLinguagem {
        private $idAssinatura = 0;        
        private $arrType    = array('memo','chore','release','feature','bug','none');//Tipos possíveis de mensagem
        private $strBody    = '';
        
        function __construct($strBody, $idAssinatura=0){
            $this->strBody      = $strBody;            
            $this->idAssinatura = $idAssinatura;
        }
        
        function getIdAssinatura(){
            return (int)$this->idAssinatura;
        }
        
        function extractActionsForString($strBodyInfo=''){
            $strBody        = (strlen($strBodyInfo) > 0) ? $strBodyInfo : $this->strBody;
            $arrMsg         = explode("\n",$strBody);  
            $arrType        = $this->arrType;
            $arrTag         = array();
            $arrTask        = array();            
            $arrPseudoCod   = $this->getArrPseudoCod();
            $type           = 'none';
            
            if (FALSE !== $arrPseudoCod) {
                $tam = count($arrMsg);

                for($i=0; $i < $tam; $i++) {
                    $line       = trim($arrMsg[$i]);

                    //Localiza o tipo da mensagem (sempre definido na primeira linha):
                    if ($i == 0) {
                        $typeCheck  = str_replace('#', '', strtolower($line));
                        $typeCheck  = str_replace(':', '', $typeCheck);
                        $posType    = array_search($typeCheck,$arrType);
                        if ($posType !== false) $type = strtoupper($arrType[$posType]);
                    }
                    
                    foreach($arrPseudoCod as $rowCod) {
                        $cod                = $rowCod['CODIGO'];
                        $grupoCod           = $rowCod['GRUPO'];
                        $codLang            = '#'.$cod.':';                        
                        
                        $key = strpos($line,$codLang);
                        if ($key !== false) {
                           //Encontrou uma tag na linha atual                         
                          $fimLoop = false;
                          if ($grupoCod == 'TAREFAS') {
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
                               $arrTag[$grupoCod] = $arrTask;
                          } else {                                
                               $arrPartTag = explode($codLang,$line);
                               if (isset($arrPartTag[1]) && strlen($arrPartTag[1]) > 0) {
                                   //O conteúdo do pseudo-código está na mesma linha.                               
                                   $arrTag[$grupoCod] = $arrPartTag[1];
                                   //if (strlen($arrTag[1]) == 0) $arrTag[$codLang] = $arrMsg[++$i];
                               } else {
                                   $i++;//Avança uma linha.
                                   while(!$fimLoop){
                                       //Localiza a próxima linha com texto.                                       
                                       $line    = trim($arrMsg[++$i]);
                                       //$lineR   = $line;
                                       if (strlen($line) == 0) continue; //Ignora linha vazia  
                                       $arrTag[$grupoCod] = $line;
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
            $sql = "SELECT CODIGO, (
                SELECT GRUPO FROM SVIP_EMOP_PSEUDOLING_GRUPO TB2 WHERE TB2.ID_PSEUDOLING_GRUPO = TB1.ID_PSEUDOLING_GRUPO
            ) AS GRUPO FROM SVIP_EMOP_PSEUDOLING_COD TB1 WHERE 
            ID_ASSINATURA = 0 OR ID_ASSINATURA = ".$this->getIdAssinatura();
            $result = DB::query($sql);
            if (is_array($result) && count($result) > 0) return $result;
            return FALSE;
        }
    }
?>
