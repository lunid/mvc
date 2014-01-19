<?php
use \site\classes\controllers\SiteController;

/**
 * Classe representativa do Controller padrão do módulo atual.
 * Geralmente herda a classe-pai do módulo atual, que por sua vez implementa tratamento de Exception,
 * ação Before quando necessário (executa antes da chamada do método solicitado),
 * além dos recursos básicos de um controller.
 * 
 * O objeto Controller atual deve ser instanciado a partir de um container 
 * de Injeção de Dependência. Todo módulo deve ter uma classe container, cujo nome inicia-se com o prefixo DI.
 * Por exemplo, DISamples.php.
 */
class IndexController extends SiteController {
    
    public function actionIndex(){
       $objView = $this->getView();
       $objView->setView('teste');
       $objView->assign('BODY','<b>Olá mundo</b>');
       $output = $objView->render();
       echo $output;
    }
    
}

?>
