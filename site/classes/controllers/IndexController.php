<?php
use \site\classes\controllers\SiteController;

class IndexController extends SiteController {
    
    public function actionIndex(){
       $objView = $this->getView()->setContent('teste');
       $objView->assign('BODY','<b>Olá mundo</b>');
       $objView->render();       
    }
    
}

?>
