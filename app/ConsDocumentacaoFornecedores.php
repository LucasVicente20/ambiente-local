<?php
/*
 * Criado por : Osmar Celestino
 * Data:     17/06/2021
 * Objetivo: Tarefa Redmine  #249643
 * -----------------------------------------------------------------------------
 */

require_once "../funcoes.php";

require_once "TemplateAppPadrao.php";

$g= new TemplateAppPadrao("templates/ConsDocumentacaoFornecedores.html");
//$x->MENU = "teste maldito";
$g->show();
