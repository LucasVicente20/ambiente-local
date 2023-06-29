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
require_once dirname(__FILE__) . '/TemplateAppPadrao.php';

# Acesso ao arquivo de funções #
require_once dirname(__FILE__) . '/../fornecedores/funcoesFornecedores.php';

$tpl = new TemplateAppPadrao("templates/CadGestaoFornecedorExcluido.html", "CadGestaoFornecedorExcluido");

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']    == "GET") {
    $Sequencial = $_GET['Sequencial'];
    $Programa   = urldecode($_GET['Programa']);
} else {
    $Botao      = $_POST['BotaoAcao'];
    $Sequencial = $_POST['Sequencial'];
    $Programa   = $_POST['Programa'];
}

# Redireciona o programa de acordo com o botão voltar #
if ($Botao == "Voltar") {
    header("location: ConsSancoesSelecionar.php");
    exit;
} elseif ($Botao == "") {
    # Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
    $db    = Conexao();
    $sql    = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, DFORCRGERA, TFORCRULAT ";
    $sql   .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
    $sql   .= "  WHERE AFORCRSEQU = $Sequencial";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $result->fetchRow();
        $CNPJ                     = $Linha[0];
        $CPF                     = $Linha[1];
        $RazaoSocial   = $Linha[2];
        $DataInscricao = substr($Linha[3], 8, 2)."/".substr($Linha[3], 5, 2)."/".substr($Linha[3], 0, 4);
        $DataAlteracao = substr($Linha[4], 8, 2)."/".substr($Linha[4], 5, 2)."/".substr($Linha[4], 0, 4);
    }

    # Busca os Dados da Tabela de Situação de acordo com o sequencial do Fornecedor #
    $sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, B.EFORTSDESC ";
    $sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
    $sql   .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CFORTSCODI = B.CFORTSCODI";
    $sql   .= "   AND A.CFORTSCODI = 5 ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        for ($i=0; $i<1; $i++) {
            $Linha          = $result->fetchRow();
            $DataSituacao = substr($Linha[0], 8, 2)."/".substr($Linha[0], 5, 2)."/".substr($Linha[0], 0, 4);
            $Situacao        = $Linha[1];
            $Motivo            = strtoupper2($Linha[2]);
            $DescSituacao = $Linha[3];
        }
    }
    $db->disconnect();
}

if ($CNPJ != 0) {
    $tpl->VALOR_CNPJ = substr($CNPJ, 0, 2).".".substr($CNPJ, 2, 3).".".substr($CNPJ, 5, 3)."/".substr($CNPJ, 8, 4)."-".substr($CNPJ, 12, 2);
} else {
    $tpl->VALOR_CNPJ = substr($CPF, 0, 3).".".substr($CPF, 3, 3).".".substr($CPF, 6, 3)."-".substr($CPF, 9, 2);
}

$tpl->VALOR_RAZAO_SOCIAL_NOME = $RazaoSocial;

$tpl->VALOR_SITUACAO = $DescSituacao;

$tpl->VALOR_DATA_SITUACAO = $DataSituacao;

$tpl->VALOR_MOTIVO = $Motivo;

$tpl->VALOR_DATA_CADASTRO = $DataInscricao;

$tpl->VALOR_DATA_SITUACAO = $DataAlteracao;

$tpl->VALOR_SEQUENCIAL = $Sequencial;

$tpl->show();
