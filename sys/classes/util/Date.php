<?php
    namespace sys\classes\util;
    
    class Date {
        
        public static function isValidDateTime($dateTime) { 
            if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) { 
                if (checkdate($matches[2], $matches[3], $matches[1])) { 
                    return true; 
                } 
            } 
            return false; 
        } 

        public static function formatDate($date, $type = 'DD/MM/AAAA'){
            if(trim($date) == '' || $date == null){
                return '';                
            }
            
            switch($type){
                case 'DD/MM/AAAA':
                    $date_time  = explode(" ", $date);
                    $date_f     = explode("-", $date_time[0]);
                    return $date_f[2] . "/" . $date_f[1] . "/" . $date_f[0];
                    break;
                case 'DD/MM/AAAA HH:MM:SS':
                    $date_time  = explode(" ", $date);
                    $date_f     = explode("-", $date_time[0]);
                    return $date_f[2] . "/" . $date_f[1] . "/" . $date_f[0] . " " . $date_time[1];
                    break;
                case 'DD/MM':
                    $date_time  = explode(" ", $date);
                    $date_f     = explode("-", $date_time[0]);
                    return $date_f[2] . "/" . $date_f[1];
                    break;
                case 'AAAA-MM-DD':
                    $date_time  = explode(" ", $date);
                    $date_f     = explode("/", $date_time[0]);
                    return $date_f[2] . "-" . $date_f[1] . "-" . $date_f[0];
                    break;
                default:
                    return $date;
            }
        }
        
        public static function traduzirMes($mes){
            $mes = (int)$mes;
            
            switch($mes){
                case 1:
                    return 'Janeiro';
                    break;
                case 2:
                    return 'Fevereiro';
                    break;
                case 3:
                    return 'Março';
                    break;
                case 4:
                    return 'Abril';
                    break;
                case 5:
                    return 'Maio';
                    break;
                case 6:
                    return 'Junho';
                    break;
                case 7:
                    return 'Julho';
                    break;
                case 8:
                    return 'Agosto';
                    break;
                case 9:
                    return 'Setembro';
                    break;
                case 10:
                    return 'Outubro';
                    break;
                case 11:
                    return 'Novembro';
                    break;
                case 12:
                    return 'Dezembro';
                    break;
                default:
                    return "Mês Inválido {$mes}";
                    break;
            }
        }
        
        public static function dateDiff($dateFrom, $dateTo){
            $ret            = new \stdClass();
            $ret->days      = false;
            $ret->hours     = false;
            $ret->minutes   = false;
            $ret->status    = false;
            
            $from = self::isValidDate($dateFrom);
            $to   = self::isValidDate($dateTo); 
            
            if($from && $to){
                $dateDiff = mktime( $from['hour']  , $from['minutes'] , $from['seconds'] , $from['month'] , $from['day']     , $from['year'] ) -
                            mktime( $to['hour']    , $to['minutes']   , $to['seconds'] ,
                            $to['month']   , $to['day']       , $to['year'] );
                
                $ret->days    = floor(  $dateDiff / (60*60*24) );
                $ret->hours   = floor( ($dateDiff - ($ret->days*60*60*24) ) / (60*60) );
                $ret->minutes = floor( ($dateDiff - ($ret->days*60*60*24) - ($ret->hours*60*60) ) /60 );
                
                $ret->status = true;
            }
            
            return $ret;
        }

        public static function isValidDate($sDate = "2008-11-10 00:00:00"){
            $dateString = explode(" ", $sDate);
            $dateParts  = isset($dateString[0]) ? explode("-", $dateString[0]) : false;
            $dateParts2 = isset($dateString[1]) ? explode(":", $dateString[1]) : false;
            
            if($dateParts && count($dateParts) == 3){
                if(!checkdate($dateParts[0], $dateParts[1], $dateParts[2])){  
                    $ret['month']   = isset($dateParts[1]) ? $dateParts[1] : 0;
                    $ret['day']     = isset($dateParts[2]) ? $dateParts[2] : 0;
                    $ret['year']    = isset($dateParts[0]) ? $dateParts[0] : 0;
                    
                    $ret['hour']    = isset($dateParts2[0]) ? $dateParts2[0] : 0;
                    $ret['minutes'] = isset($dateParts2[1]) ? $dateParts2[1] : 0;
                    $ret['seconds'] = isset($dateParts2[2]) ? $dateParts2[2] : 0;
                    
                    return $ret;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }

?>
