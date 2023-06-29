<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraIncluir.php
# Autor:    Heraldo Botelho
# Data:     05/01/2013
# Objetivo: Decidir se carrega Login  ou Se Carraga pesquisador de
#           fornecedor
#
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 03/12/2014

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
# Executa o controle de segurança #
session_start();

AddMenuAcesso('/fornecedores/ConsAcompFornecedorSenha.php');
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSelecionar.php');

//*********************************************************
// se for efetuado o login do fornecedor
// o fluxo será desviado para ConsFornecedorCadastroRenovacao
//************************************************************

// if ($_SESSION['_eusupologi_']=="INTERNET") {
    $Url = "ConsAcompFornecedorSenha.php?Desvio=CadRenovacaoCadastroIncluir";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit;
// } else {
//     $Url = "ConsAcompFornecedorSelecionar2.php?Desvio=CadRenovacaoCadastroIncluir";
//     if (!in_array($Url, $_SESSION['GetUrl'])) {
//         $_SESSION['GetUrl'][] = $Url;
//     }
//     header("location: ".$Url);
//     exit;
// }
