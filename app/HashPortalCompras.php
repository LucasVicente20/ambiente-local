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
 * @version GIT: v1.21.0-18-gc622221
 */
require_once dirname(__FILE__).'/../vendor/autoload.php';

$requisicaoMetodoPost = ($_SERVER["REQUEST_METHOD"] == "POST");
$requisicaoComAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($requisicaoMetodoPost && $requisicaoComAjax) {
    $login = $_SERVER['HTTP_X_LOGIN'];
    $banco = Conexao();
    $sql = "SELECT EUSUPOSENH FROM SFPC.TBUSUARIOPORTAL WHERE EUSUPOLOGI = '$login'";
    $result = $banco->query($sql);
    if ($result->numRows() > 0) {
        $linha = $result->fetchRow();
        $banco->disconnect();

        $salt = $linha[0].$login.$_COOKIE['PHPSESSID'];
        $senhaCript = Bcrypt::hash($salt, 8);
        echo json_encode(array(
            'emac' => $senhaCript,
        ));
    } else {
        echo json_encode(array(
            'emac' => false,
        ));
    }
}
