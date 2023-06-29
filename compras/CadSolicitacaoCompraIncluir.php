<?php
/**
 * Portal de Compras
 * 
 * Programa: CadSolicitacaoCompraIncluir.php
 * Autor:    Ariston Cordeiro
 * Data:     31/08/2011
 * Objetivo: Programa de inclusão de solicitação de compra
 * ---------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     19/11/2018
 * Objetivo: Tarefa Redmine 207003
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/07/2019
 * Objetivo: Tarefa Redmine 220564
 * ---------------------------------------------------------------------------------------
 */

session_start();
# Acesso ao arquivo de funções #
require_once("../funcoes.php");

$acaoPagina     = ACAO_PAGINA_INCLUIR;
$programa       = "CadSolicitacaoCompraIncluir.php";
$conn           = Conexao();
$incluir_antigo = isset($_GET['tela']) ? $_GET['tela'] : '';
$pilotos        = getOrgaosPilotos($conn);
$pilotos        = explode(',', $pilotos);
//($orgao_usuario);

if(!empty($_SESSION['_cgrempcodi_'])){
    $orgao_usuario  = getOrgaoUsuarioLogado($conn);
}
$intersect      = array_intersect($orgao_usuario, $pilotos);


if($_SESSION["_fperficorp_"] == "S" && $incluir_antigo == 'antiga') {
    require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
} else if ($_SESSION["_fperficorp_"] != "S" && (!empty($intersect) || in_array(99, $pilotos)) && $_POST['TipoCompra'] == 5) {
    require_once("CadSolicitacaoCompraIncluirManterExcluirScc.php");
} else if($_SESSION["_fperficorp_"] != "S" && empty($intersect)) {
    require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
} else {    
    if(isset($_POST['TipoCompra']) && $_POST['TipoCompra'] == 5) {
        require_once("CadSolicitacaoCompraIncluirManterExcluirScc.php");
    } else {
        require_once("CadSolicitacaoCompraIncluirManterExcluir.php");
    }
}

?>
