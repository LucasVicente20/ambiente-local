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
 * @package Pitang_App
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.21.0-18-gc622221
 */

/**
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81058 - Fornecedores - Alteração senha internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81058
 * Versão:   v1.22.0-14-g53bd17e
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';

$requisicaoMetodoPost = ($_SERVER["REQUEST_METHOD"] == "POST");
$requisicaoComAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($requisicaoMetodoPost && $requisicaoComAjax) {
    if (isset($_SERVER['HTTP_X_SENHAATUAL'])) {
        echo json_encode(array(
            'senhaAtual' => Bcrypt::hash($_SERVER['HTTP_X_SENHAATUAL'] . $_COOKIE['PHPSESSID'], 8),
            'novaSenha' => Bcrypt::hash($_SERVER['HTTP_X_NOVASENHA'] . $_COOKIE['PHPSESSID'], 8),
            'confirmaSenha' => Bcrypt::hash($_SERVER['HTTP_X_CONFIRMASENHA'] . $_COOKIE['PHPSESSID'], 8)
        ));
    } else {
        echo json_encode(array(
            'emac' => Bcrypt::hash($_SERVER['HTTP_X_CNPJCPF'] . $_COOKIE['PHPSESSID'], 8)
        ));
    }
}
