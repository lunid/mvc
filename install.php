<?php

@require_once 'System.php';
$pearInstall = class_exists('System', false);

$msgPear = "<h4>Biblioteca PEAR:</h4>";
if (!$pearInstall) {
    $msgPear .= "Pear ainda não instalado...";
    
    //Inclui o pear no class_path
    $classpath  = shell_exec('echo %PATH%');
    $pearPath   = 'c:\xampp\php';
    if (strlen($classpath) > 0) {
        if (file_exists($pearPath.'\\pear.bat')) {
            //Pear está instalado corretamente.
            $arrPath = explode(';',$classpath);
            $key     = array_search($pearPath,$arrPath);
            if ($key === FALSE) {
                //O pear não está incluído no classpath. Faz a inclusão via SHELL.
                shell_exec('SET PATH=%PATH%;'.$pearPath);
                $msgPear .= "
                    <br>Biblioteca PEAR incluída na variável de ambiente PATH do Windows.
                    Não é necessário iniciar o sistema. Porém, é importante verificar após a inicialização se 
                    o caminho {$pearPath} foi incluído corretamente em PATH.
                ";
            }
        } else {
            $msgPear .= "<br>Arquivo pear.bat não localizado em {$pearPath}.";
        }
    }    
} else {  
  $msgPear .= "Biblioteca PEAR instalada corretamente.";  
}

//Checa novamente a biblioteca PEAR, após a verificação/instalação
if (class_exists('System', false)) {
    //PEAR instalado.
    $phpdoc  = "c:\xampp\php\phpdoc.bat";
    $output  = shell_exec($phpdoc);    
    echo $output;    
} else {
    echo "PEAR não instalado corretamente.";
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$apacheModulesRequired  = array(
    'mod_expires: Altamente recomendável',
    'mod_ssl: Altamente recomendável (por exemplo se for necessário instalar o composer)',
    'mod_deflate: Altamente recomendável',
    'mod_rewrite:',
    'mod_headers:',
    'mod_filter:',
    'mod_openssl: Recomendável caso precise instalar o PEAR'
);

$phpModulesRequired     = array(    
    'openssl:',
    'libxml:',
    'SimpleXML:',
    'xml:',
    'curl:',
    'mysqli:',
    'xsl: Necessário, por exemplo, para a instalação do PHPDocumentor'
);

$arrCheckApache         = checkDependencies($apacheModulesRequired, apache_get_modules());
$arrCheckPhp            = checkDependencies($phpModulesRequired, get_loaded_extensions());

function checkDependencies($required,$deps){
    $errors     = 0;
    $liModules  = '';
    foreach($required as $strModule) {
        list($module,$comment) = explode(':',$strModule);
        $key = array_search($module, $deps);

        if ($key === false) {
            //Módulo obrigatório não carregado no Apache:        
            $cmt        = (strlen($comment) > 0) ? " ({$comment})" : '';
            $liModules .= "<li>{$module}{$cmt}</li>";
            $errors++; 
        }
    }
    $arrOut = array($errors,$liModules);
    return $arrOut;
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

$msgInstallApache = "<h4>Resumo da verificação dos módulos do Apache:</h4>";
if ($arrCheckApache[0] == 0) {
    $msgInstallApache .= "Módulos do Apache OK.";
} else {
    $msgInstallApache .= "Os seguintes módulos são obrigatórios e aparentemente não estão carregados no Apache:<br/>
        <ul>{$arrCheckApache[1]}</ul>
    ";
}

$msgInstallPhp = "<h4>Resumo da verificação dos módulos do PHP:</h4>";
if ($arrCheckPhp[0] == 0) {
    $msgInstallPhp .= "Módulos do PHP OK.";
} else {
    $msgInstallPhp .= "Os seguintes módulos são obrigatórios e aparentemente não estão carregados:<br/>
        <ul>{$arrCheckPhp[1]}</ul>
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
        <?php echo $msgPear ?>
        <?php echo $msgInstallApache ?>
        <?php echo $msgInstallPhp ?>
        <?php echo $msgCreateFoldersData ?>
        <h4>Instalar o Pear:</h4>
        <a href='sys/vendors/pear/pear_install.php' target='_blank'>Instalar PEAR.</a>
        
        <h4>Instalar o Composer:</h4>
        <a href='sys/vendors/composer/install.php' target='_blank'>Instalar Composer.</a>                
    </body>
</html>
