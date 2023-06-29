<?php
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

/* -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     13/03/2019
 * Objetivo: Tarefa Redmine 210705
 * -------------------------------------------------------------------------
 */

if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/EmailLicitacoes.html", "EmailLicitacoes");

//variável vinha como campo hidden no programa anterior.
$Critica = 1;

// Passando epargesetr
$db = Conexao();
$db->setFetchMode(DB_FETCHMODE_OBJECT);
$data = $db->getCol('SELECT epargesetr FROM SFPC.TBPARAMETROSGERAIS');

$tpl->NOME_SETOR = $data[0];

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Nome      = strtoupper2(trim($_POST['txtnome']));
    $Email     = trim($_POST['txtemail']);
    $MensEmail = strtoupper2(trim($_POST['txtmensagem']));

    # Critica dos Campos #
    if ($Critica == 1) {
        $Mens = 0;
        $Mensagem = "Informe: ";

        if ($Nome == "") {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtnome.focus();\" class=\"titulo2\">Nome</a>";
        }

        if ($Email == "") {
            if ($Mens == 1) {
                $Mensagem.= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtemail.focus();\" class=\"titulo2\">E-mail</a>";
        } elseif (!strchr($Email, "@")) {
            if ($Mens == 1) {
                $Mensagem.= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtmail.focus();\" class=\"titulo2\">E-mail Inválido</a>";
        }

        if ($MensEmail == "") {
            if ($Mens == 1) {
                $Mensagem.= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtmensagem.focus();\" class=\"titulo2\">Mensagem</a>";
        }

        if ($Mens == 0) {
            $Para = "portalcompras@recife.pe.gov.br";
            $From = $Email;
            $Mensagem = "Erro no envio. Tente novamente mais tarde.";
            $Tipo = 2;

            if (
            EnviaEmail(
                $Para,
                "Mensagem Enviada do Portal de Compras",
                "Nome: ".$Nome."\nE-mail: ".$Email."\n\nMensagem:\n".$MensEmail,
                "from: $From"
            )
            ) {
                $Mensagem ="Mensagem Enviada com Sucesso";
                $Tipo = 1;
            }

            $Mens      = 1;
            $Tipo      = 1;
            $Nome      = "";
            $Email     = "";
            $MensEmail = "";
        }

        if ($Mens != 0 || !empty($Mensagem)) {
            $tpl->NOME = $Nome;
            $tpl->EMAIL = $Email;
            $tpl->TEXTO_MENSAGEM = $MensEmail;

            $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
        }
    }
}

$tpl->show();
