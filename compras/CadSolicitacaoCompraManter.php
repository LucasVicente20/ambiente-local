<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadSolicitacaoCompraManter.php
 * Autor:    Ariston Cordeiro
 * Data:     31/08/2011
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     07/11/2018
 * Objetivo: Tarefa Redmine 205440
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     04/02/2019
 * Objetivo: Tarefa Redmine 210408
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     26/01/2023
 * Objetivo: Tarefa Redmine 278208
 * -------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
session_start();
require_once '../funcoes.php';
require_once 'funcoesCompras.php';

ini_set('display_errors', 0);

error_reporting(E_ALL ^ E_NOTICE);

$acaoPagina         = ACAO_PAGINA_MANTER;
$programa           = "CadSolicitacaoCompraManter.php";
$programaSelecao    = "CadSolicitacaoCompraManterSelecionar.php";
$conn               = Conexao();
$orgao_usuario      = getOrgaoUsuarioLogado($conn);
$sol_sequ           = isset($_GET['SeqSolicitacao']) ? $_GET['SeqSolicitacao'] : (isset($_POST['SeqSolicitacao']) ? $_POST['SeqSolicitacao'] : $_SESSION['SeqSolicitacaok']);

$sql        = " SELECT sc.carpnosequ, sc.ctpcomcodi FROM SFPC.TBsolicitacaocompra sc WHERE sc.csolcosequ = " . $sol_sequ . " AND sc.csitsocodi in (1,5)  AND sc.ctpcomcodi = 5 ";

$result     = executarTransacao($conn,$sql);

$linha      = $result->fetchRow();
$pilotos    = (string) getOrgaosPilotos($conn);
$pilotos    = explode(',', $pilotos); // remover espaços da string
$intersect  = array_intersect($orgao_usuario, $pilotos);

// Verificar se a scc é do tipo SARP
if (!empty($linha) && $linha[1] == TIPO_COMPRA_SARP) {
    if ($_SESSION["_fperficorp_"] == "S" && empty($linha[0])) {
        $_SESSION['mensagemSarp'] = "Esta SCC-Sarp não está adequada ao novo módulo de Registro de Preços";
        require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
    } elseif ($_SESSION["_fperficorp_"] != "S" && (!empty($intersect) || in_array(99, $pilotos)) && empty($linha[0])) {
        $Botao = 'Voltar';
        $_SESSION['mensagemSarp'] = "Esta SCC-Sarp não está adequada ao novo módulo de Registro de Preços";
        require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
    } elseif ($_SESSION["_fperficorp_"] != "S" && empty($intersect) && empty($linha[0])) {
        require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
    } else {
        require_once("CadSolicitacaoCompraIncluirManterExcluirScc.php");
    }
} else {
        require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
}
?>