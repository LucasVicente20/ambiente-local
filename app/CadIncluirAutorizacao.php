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
 * @category Novo Layout
 * @package App
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version Git: $Id:$
 */
require_once 'TemplateAppPopup.php';

$tpl = new TemplateAppPopup("templates/CadIncluirAutorizacao.html");

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']    == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
    $Autoriza = $_POST['Autoriza'];
    $Nome = strtoupper2($_POST['Nome']);
    $RegistroAutorizacao = $_POST['RegistroAutorizacao'];
    $DataAutorizacao = $_POST['DataAutorizacao'];
    $ProgramaOrigem = $_POST['ProgramaOrigem'];

    $_SESSION["AutorizaNome"] = $Nome;
    $_SESSION["AutorizaRegistro"] = $RegistroAutorizacao;
    $_SESSION["AutorizaData"] = $DataAutorizacao;
} else {
    $ProgramaOrigem    = $_GET['ProgramaOrigem'];
}

$tpl->PROGRAMA_ORIGEM = $ProgramaOrigem;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Critica == 1) {
    $Mens     = 0;
    $Mensagem = "Informe: ";

    if ($Nome == "") {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.Nome.focus();\" class=\"titulo2\">Nome</a>";
    }

    if ($RegistroAutorizacao == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.RegistroAutorizacao.focus();\" class=\"titulo2\">Registro ou Inscrição</a>";
    } else {
        if (!SoNumeros($RegistroAutorizacao)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.RegistroAutorizacao.focus();\" class=\"titulo2\">Registro ou Inscrição Válida</a>";
        }
    }

    if ($DataAutorizacao == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.DataAutorizacao.focus();\" class=\"titulo2\">Data de Vigência</a>";
    } else {
        $MensErro = ValidaData($DataAutorizacao);
        if ($MensErro != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.DataAutorizacao.focus();\" class=\"titulo2\">Data de Vigência Válida</a>";
        }
    }

    if ($Mens == 0) {
        $AutorizacaoEspecifica = $Nome . "#" . $RegistroAutorizacao;

        if ($_SESSION['AutoEspecifica'] == "" || !in_array($AutorizacaoEspecifica, $_SESSION['AutoEspecifica'])) {
            $_SESSION['AutorizacaoNome'][ count($_SESSION['AutorizacaoNome']) ] = $Nome;
            $_SESSION['AutorizacaoRegistro'][ count($_SESSION['AutorizacaoRegistro']) ] = $RegistroAutorizacao;
            $_SESSION['AutorizacaoData'][ count($_SESSION['AutorizacaoData']) ] = $DataAutorizacao;
            $_SESSION['AutoEspecifica'][ count($_SESSION['AutoEspecifica']) ] = $AutorizacaoEspecifica;
        }

        $Autoriza = "S";
        $tpl->block("BLOCO_ENVIAR");
    } else {
        $Autoriza = "";
        $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
    }
}

$tpl->Autoriza = $Autoriza;
$tpl->Critica = $Critica;
$tpl->NOME = $Nome;
$tpl->RegistroAutorizacao = $RegistroAutorizacao;
$tpl->DataAutorizacao = $DataAutorizacao;
$tpl->show();
