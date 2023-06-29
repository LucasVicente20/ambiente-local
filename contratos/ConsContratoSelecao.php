<?php
/*
    Alterado: Lucas Vicente
    Data:     28/06/2023
    Objetivo: 285485
*/
if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao(CAMINHO_SISTEMA . "contratos/ConsContratoSelecao.html");

$tpl->show();

?>