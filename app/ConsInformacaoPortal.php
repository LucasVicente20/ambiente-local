<?php 

require_once("../funcoes.php");
require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/ConsInformacaoPortal.html");
$tpl->VERSAO = VERSAO;
$tpl->show();

?>
