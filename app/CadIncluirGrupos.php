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

$tpl = new TemplateAppPopup("templates/CadIncluirGrupos.html");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao          = filter_var($_POST['Botao']);
    $TipoGrupo      = filter_var($_POST['TipoGrupo']);
    $TipoMaterial   = filter_var($_POST['TipoMaterial']);
    $Grupo          = $_POST['Grupo'];
    $ProgramaOrigem = filter_var($_POST['ProgramaOrigem']);
} else {
    $ProgramaOrigem = filter_var($_GET['ProgramaOrigem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Incluir") {
    $Mens     = 0;
    $Mensagem = "Informe: ";

    if ($TipoGrupo == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.FornecedorGrupos.TipoGrupo.focus();\" class=\"titulo2\">Tipo de Grupo</a>";
    } else {
        if ($TipoGrupo == "M" and $TipoMaterial == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.FornecedorGrupos.TipoMaterial.focus();\" class=\"titulo2\">Tipo de Material</a>";
        }
    }

    if ($Grupo == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.FornecedorGrupos.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
    }

    if ($Mens == 0) {
        if (!$_SESSION['Servicos']) {
            $Servicos = array();
        } else {
            $Servicos = $_SESSION['Servicos'];
        }

        if (!$_SESSION['Materiais']) {
            $Materiais = array();
        } else {
            $Materiais = $_SESSION['Materiais'];
        }

        for ($i=0; $i < count($Grupo); $i++) {
            $Item = $TipoGrupo."#".$Grupo[$i];

            if ($TipoGrupo == "S" and (! in_array($Item, $Servicos))) {
                $_SESSION['Servicos'][count($_SESSION['Servicos'])] = $Item;
            } elseif ($TipoGrupo == "M" and (! in_array($Item, $Materiais))) {
                $_SESSION['Materiais'][count($_SESSION['Materiais'])] = $Item;
            }
        }

        $Enviar = "S";
    } else {
    	$tpl->exibirMensagemFeedback($Mensagem, $Tipo);
    }
}

if ($Enviar == "S") {
    $tpl->block('BLOCO_ENVIAR');
}

$tpl->PROGRAMA_ORIGEM = $ProgramaOrigem;

if ($TipoGrupo == "M") {
    $tpl->CHECKED_MATERIAL = 'checked';
    $tpl->block('BLOCO_TIPO_MATERIAL');

    if ($TipoMaterial != "") {
        if ($TipoMaterial == "C") {
            $tpl->CHECKED_CONSUMO = 'checked';
        } else {
            $tpl->CHECKED_PERMANENTE = 'checked';
        }
    }
}

if ($TipoGrupo == "S") {
    $tpl->CHECKED_SERVICO = 'checked';
}

if ($TipoGrupo == "S" or ($TipoGrupo == "M" and $TipoMaterial != "")) {
    $db   = Conexao();
    $sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE FGRUMSSITU = 'A' AND ";

    if ($TipoGrupo == "M") {
        $sql .= "FGRUMSTIPO = 'M'";

        if ($TipoMaterial != "") {
            $sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
        }
    } else {
        $sql .= "FGRUMSTIPO = 'S'";
    }

    $sql .= "ORDER BY 2";
    $res  = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $res->fetchRow()) {
            $Descricao   = substr($Linha[1], 0, 75);

            $tpl->COD = $Linha[0];
            $tpl->DESCRICAO = $Descricao;
            $tpl->SELECIONADO = "";
            $tpl->block('BLOCO_OPCOES_GRUPO');
        }

        if ($res->numRows() > 0) {
            $tpl->block('BLOCO_SELECT_GRUPOS');
        }
    }

    $db->disconnect();
}

$tpl->show();
