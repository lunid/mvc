<?php

    namespace sys\classes\mvc;
    
    class ExceptionController extends Controller {
        
        function actionError($exception){                                 
            $objViewPart    = MvcFactory::getViewPart();
            $objView        = MvcFactory::getView();
            $msgErr         = '';
            if ($exception instanceof \Exception) {
                $errLine    = $exception->getLine();
                $errMsg     = $exception->getMessage();
                $errFile    = $exception->getFile();
                $msgErr     = "<b><font size='3'>Parece que alguma coisa deu errado</font></b>:<br>$errMsg<br>linha $errLine ($errFile)";
            }  else {
                $msgErr = $exception;
            }
            
            $objViewPart->setContent($msgErr);
            
            $objView->setTemplate('error.html');            
            $objView->TITLE = 'Um erro ocorreu!';
            $objView->setLayout($objViewPart);  
            $objView->render('error');                
        }
    }
?>
