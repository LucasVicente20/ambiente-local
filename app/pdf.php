<?php

require_once "TemplateAppPopup.php";
require_once '../fornecedores/funcoesFornecedores.php';

$tpl = new TemplateAppPopup("templates/pdf.html", "");

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$tpl->show();
