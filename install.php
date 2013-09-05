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

//Cria a pasta para repositório de dados
$rootFolderData         = 'data/';
$arrSubfolders          = array('log','upload');
$msgCreateFoldersData   = '<br/><h4>Criação das pastas de dados:</h4>';

foreach($arrSubfolders as $subfolder) {
    $path = $rootFolderData.$subfolder;
    if (!is_dir($path)) {               
        if (!mkdir($path,0777, TRUE)) {
            $msgCreateFoldersData .= "Impossível criar a pasta <span class='folder'>{$path}</span>.";
        } else {
            $msgCreateFoldersData .=  "Pasta <span class='folder'>{$path}</span> criada com sucesso!<br>";
        }
    } else {
        $msgCreateFoldersData .= "Pasta <span class='folder'>{$path}</span> já existe.<br/>";
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
                margin-bottom:8px;
            }
            
            .folder {
                color: #00F;
            }
        </style>
    </head>
    <body>
        <?php echo $msgInstall ?>
        <?php echo $msgCreateFoldersData ?>
    </body>
</html>
