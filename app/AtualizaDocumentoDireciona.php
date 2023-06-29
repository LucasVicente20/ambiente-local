<?php
/*
 * #-------------------------------------------------------------------------
 * # Portal da DGCO
 * # Programa: AtualizaDocumentoDireciona.php
 * # Autor:    Ernesto Ferreira
 * # Data:     14/11/18
 * # Objetivo: Programa que Direciona para Atualização de Documentos do Fornecedor
 */

# Acesso ao arquivo de funções #
if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao", 1);
}

header("location: AtualizaDocumentoSenha.php");
exit;
