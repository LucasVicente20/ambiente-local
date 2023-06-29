<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: EmissaoCHFDireciona.php
# Autor:    Roberta Costa
# Data:     21/09/04
# Objetivo: Programa que Redireciona para Emissão de CHF 
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso("/fornecedores/EmissaoCHFSenha.php");
AddMenuAcesso("/fornecedores/EmissaoCHFSelecionar.php");

if( $_SESSION['_cperficodi_'] == 0 ){
	  header("location: EmissaoCHFSenha.php");
	  exit;
}else{
		header("location: EmissaoCHFSelecionar.php");
		exit;
}
?>
