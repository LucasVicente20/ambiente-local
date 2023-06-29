<?php
/**
 * Portal de Compras
 * Programa: ConsDFD.php
 * Autor: Diógenes Dantas | João Madson
 * Data: 01/12/2022
 * Objetivo: Programa de consulta de DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data: 05/01/2023
  * Tarefa: #277231 
  *-------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "FuncoesAbasPlanejamento.php";
# Abas
include "AbaInformacoesDFD.php";
include "AbaHistoricoDFD.php";
# Executa o controle de segurança	#

Seguranca();
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Destino = $_POST['Destino'];
} else {
	$Destino = $_GET['Destino'];
	unset($_SESSION['DFD']);
	unset($_SESSION['ultimohist']);
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
