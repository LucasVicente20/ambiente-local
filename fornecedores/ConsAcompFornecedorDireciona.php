<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAcompFornecedorDireciona.php
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

AddMenuAcesso("/fornecedores/ConsAcompFornecedorSenha.php");
AddMenuAcesso("/fornecedores/ConsAcompFornecedorSelecionar.php");

if( $_SESSION['_cperficodi_'] == 0 ){
	  header("location: ConsAcompFornecedorSenha.php");
	  exit;
}else{
		header("location: ConsAcompFornecedorSelecionar.php");
		exit;
}
?>
