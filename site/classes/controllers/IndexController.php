<?php
use \site\classes\controllers\SiteController;

class IndexController extends SiteController {
    
    public function actionIndex(){      
       $objView = $this->getView('teste');
       $objView->javascript('site', 'index');
       /*
        $data = array(
               'title' => 'My Title',
               'heading' => 'My Heading',
               'message' => 'My Message'
          );

          $objView->assign($data);
        */
       $objView->assign('BODY','<b>Olá mundo</b>');      
       $objView->render();       
    }
    
}

?>
