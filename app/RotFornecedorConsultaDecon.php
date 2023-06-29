<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotFornecedorConsultaDecon.php
# Autor:    Roberta Costa
# Data:     01/09/2004
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea
# Objetivo: Programa de Verificação da Existência dos Fornecedores no DECON
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$NomePrograma    = urldecode($_GET['NomePrograma']);
		$Origem          = $_GET['Origem'];
		$Destino         = $_GET['Destino'];
		$Sequencial      = $_GET['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Conectando no SFCI.TBCONTRIBUINTE para pegar a Inscrição mercantil #
$db = ConexaoOracle();
$Sql  = "SELECT COUNT(*) FROM PROCESSO WHERE PARCODIGO = 3";
$res = $db->query($Sql);
if( PEAR::isError($res) ){
		$db->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
		exit;
}else{
		$Linha = $res->fetchRow();
		$Contador	= $Linha[0];
}
$db->disconnect();

if( $Contador == 0 ){
		$FornecedorDecon = "N";
}else{
		$FornecedorDecon = "S";
}

$Url = "fornecedores/$NomePrograma?Origem=$Origem&Destino=$Destino&Sequencial=$Sequencial&FornecedorDecon=$FornecedorDecon";
if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
RedirecionaPost($Url);
?>
