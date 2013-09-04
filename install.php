<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$errors                 = 0;
$liModules              = '';
$apacheModulesRequired  = array('mod_expires: Altamente recomendável','mod_deflate: Altamente recomendável','mod_rewrite:','mod_headers:','mod_filter:');
$apacheModules          = apache_get_modules();

foreach($apacheModulesRequired as $strModule) {
    list($module,$comment) = explode(':',$strModule);
    $key = array_search($module, $apacheModules);
    if ($key === false) {
        //Módulo obrigatório não carregado no Apache:        
        $cmt        = (strlen($comment) > 0) ? " ({$comment})" : '';
        $liModules .= "<li>{$module}{$cmt}</li>";
        $errors++; 
    }
}

$msgInstall = "<h4>Resumo da verificação do ambiente:</h4>";
if ($errors == 0) {
    $msgInstall .= "Módulos do Apache OK.";
} else {
    $msgInstall .= "Os seguintes módulos são obrigatórios e aparentemente não estão carregados no Apache:<br/>
        <ul>{$liModules}</ul>
    ";
}
?>

<html>
    <head>
        <style>
            body {
                font-family: 'Arial', 'sans-serif';
                font-size: 12px;
                margin: 50px;
            }
            
            h4 {
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <?php echo $msgInstall ?>
    </body>
</html>
