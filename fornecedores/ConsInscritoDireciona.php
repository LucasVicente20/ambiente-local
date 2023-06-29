<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInscritoDireciona.php
# Autor:    Roberta Costa
# Data:     08/09/04
# Objetivo: Programa que Direciona para Acompanhamento de Fornecedor
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsInscritoSenha.php');
AddMenuAcesso( '/fornecedores/ConsInscritoSelecionar.php');

if( $_SESSION['_cperficodi_'] == 0 ){
	  header("location: ConsInscritoSenha.php");
	  exit();
}else{
		header("location: ConsInscritoSelecionar.php");
	  exit();
}
?>
