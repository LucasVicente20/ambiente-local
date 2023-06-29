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
/**
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data: 18/06/2015 - CR82766
 * Versão: v1.20.0-21-g0bb0451
 * -------------------------------------------------------------------------
 */
session_start();

 if (! require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/home.html");
$larguraBoxNoticias = 12;
$limiteNoticiasExibidas = 6;
$urlContrato = "http://".$_SERVER['HTTP_HOST'].str_replace('app','',dirname($_SERVER['REQUEST_URI']));

list($Ip) = explode('[\.]', $_SERVER['REMOTE_ADDR']); // $Ip = 1o número do IP remoto
/* IPs que iniciam com 10 são locais ou da intranet */
/*
 * IPs dos roteadores da intranet
 * OBS: Comentado após mudança de proxy
 * $_SERVER['REMOTE_ADDR'] == '200.151.250.106' or
 * $_SERVER['REMOTE_ADDR'] == '200.151.250.5' or
 * range de IPs da intranet
 */
/* 
* Acesso Liberado temporariamente para usuarios fora do proxy padrão 
* Por orientação da secretaria de administração
* 18/03/2020
* Autor: Eliakim Ramos
* Autorizado por : Rossana Lira
* Solicitado Por: George Pierre
* CR#231443
*/
// não logado (usuário INTERNET)
// if (($Ip == 10 || (eregi($Ip, "192.168.")) || (eregi($Ip, "200.151.250.")) || (eregi($Ip, "192.207.206.")) || (eregi($Ip, "172.18.7.")) || (eregi($Ip, "172.18.8.")) || (eregi($Ip, "172.18.9."))) && ($_SESSION['_cusupocodi_'] == 0) && ($_SESSION['_ref_'] != "transparencia")) {
  if(true){
    $larguraBoxNoticias = 8;
    if (isset($_SESSION['tipoErroLogin']) && $_SESSION['tipoErroLogin'] == "2") {
        $tpl->exibirMensagemFeedback($_SESSION['textoMensagemLogin'], $_SESSION['tipoErroLogin']);
        $tpl->EMAIL_LOGIN = $_SESSION['emailLogin'];
        $tpl->SENHA_LOGIN = $_SESSION['senhaLogin'];

        unset($_SESSION['textoMensagemLogin']);
        unset($_SESSION['tipoErroLogin']);
        unset($_SESSION['emailLogin']);
        unset($_SESSION['senhaLogin']);
    }

    $tpl->block('BOX_LOGIN');
 }

$tpl->LARGURA_BOX_NOTICIAS = $larguraBoxNoticias;

// Begin lista de notícias
$db = Conexao();
$sql = " SELECT * FROM SFPC.TBNOTICIAPORTALCOMPRAS ";
$sql .= " WHERE FNOTPCSITU = 'A' ORDER BY TNOTPCDATC DESC LIMIT $limiteNoticiasExibidas ";

$resultado = executarSQL($db, $sql);
$item = null;
while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
    $dataCadastro = new DataHora($item->tnotpcdatc);
    $dataCadastro = $dataCadastro->formata('d.m.y').' - '.$dataCadastro->formata('H:i');

    $tpl->TITULO_NOTICIA = $item->enotpctitl;
    $tpl->DATA_CADASTRO = $dataCadastro;

    if ($item->fnotpcdest == "P") {
        $tpl->LINK = "#modalNoticia";
        $tpl->TEXTO_NOTICIA = $item->enotpctext;
        $tpl->DESTINO = 'data-toggle="modal"';
        $tpl->ABRE_MODAL = "abre-modal";
    } else {
        $tpl->LINK = $item->enotpctext;
        $tpl->TEXTO_NOTICIA = "";
        $tpl->DESTINO = 'target="_blank"';
        $tpl->ABRE_MODAL = "";
    }
    $tpl->block("BLOCO_NOTICIA_LINK");
}

if ($resultado->numRows() <= 0) {
    $tpl->block("BLOCO_SEM_NOTICIAS");
}
// End lista de notícias

$tpl->CAPTCHA = getUriCaptcha();
$tpl->URLCONTRATO = $urlContrato;

$tpl->show();
