<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAnaliseCertidao.php
# Autor:    Heraldo Botelho
# Data:     15/01/2013
# Objetivo: Chamar ConsAcompFornecedorSelecionar.php  passando o parametro 
#                               Desvio=CadAnaliseCertidaoFornecedor
#            
#           
#-------------------------------------------------------------------------
# Alterado:
# Data: ??/??/?? -
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();

AddMenuAcesso( '/fornecedores/ConsAcompFornecedorSelecionar.php' );

//*********************************************************
// o fluxo será desviado para ConsFornecedorCadastroRenovacao
//************************************************************

$Url = "ConsAcompFornecedorSelecionar.php?Desvio=CadAnaliseCertidaoFornecedor";
header("location: ".$Url);


?>
