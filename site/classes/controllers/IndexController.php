<?php
use \site\classes\controllers\SiteController;

class IndexController extends SiteController {
    
    public function actionIndex(){
       $objView = $this->getView();
       $objView->setView('teste');
       $objView->assign('BODY','<b>Novo conteúdo HTML</b>');
       $output = $objView->render();
       echo $output;
    }
    
}

?>
