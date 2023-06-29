<?php
/**
 * Portal de Compras
 * Programa: ConsultaDFD.php
 * Autor: João Madson
 * Data:  07/03/2023
 * Objetivo: Programa de consulta de DFD usado em consulta popup de outras telas que não são, consultar DFD(consDFD.php)
 * -------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "FuncoesAbasPlanejamento.php";
# Abas
include "AbaInformacoesConsultaDFD.php";
include "AbaHistoricoConsultaDFD.php";
# Executa o controle de segurança	#

Seguranca();
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Destino = $_POST['Destino'];
} else {
	$Destino = $_GET['Destino'];
	unset($_SESSION['DFD']);
} 
# Aba de Inclusão de Contrato  - Formulário A #

ExibeAbas($Destino);

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino){
	if( $Destino == "A" or $Destino == "" ){
        ExibeAbaInformacoesDFD();
	} else if( $Destino == "B" ){
        ExibeAbaHistoricoDFD();
	} 
}
?>
