<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version Git: $Id:$
 *
 * #-------------------------------------------------------------------------
 * # Portal da DGCO
 * # Programa: CadAcompFornecedorDireciona.php
 * # Autor:    Roberta Costa
 * # Data:     08/09/04
 * # Objetivo: Programa que Direciona para Acompanhamento de Fornecedor
 *
 * # Autor: Pitang
 * # Data: 16/12/2014
 * #Objetivo: Novo layout
 */
# Acesso ao arquivo de funções #
if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao", 1);
}

header("location: ConsAcompFornecedorSenha.php");
exit;
