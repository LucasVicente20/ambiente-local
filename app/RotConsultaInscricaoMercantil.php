<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotConsultaInscricao.php
# Autor:    Roberta Costa
# Data:     04/06/2004
# Alterado: Álvaro Faria
# Data:     03/07/2006 - Uso do Pear / Mudanças para rodar em Cohab/Varzea
# Objetivo: Programa de Verificação da Inscrição Mercantil dos Pré-Cadastro
#           de Fornecedores
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$InscricaoMercantil = $_GET['InscricaoMercantil'];
		$NomePrograma       = urldecode($_GET['NomePrograma']);
		$ProgramaSelecao    = urldecode($_GET['ProgramaSelecao']);
		$Destino            = $_GET['Destino'];
		$Sequencial         = $_GET['Sequencial'];
		$Critica            = $_GET['Critica'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Abre a Conexão com Oracle #
$db = ConexaoOracle();

# Consulta no Cadastro Mercantil #
$sql  = "SELECT A.NCONTBNOME, B.CSITUMCODI FROM SFCI.TBCONTRIBUINTE A, SFCM.TBMERCANTIL B ";
$sql .= "WHERE A.ACONTBSEQU = B.ACONTBSEQU AND B.AMERCTINSC = $InscricaoMercantil";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		$db->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
		while($Linha = $res->fetchRow()){;
				$ContribuinteNome     = to_iso($Linha[0]);
				$ContribuinteSituacao = to_iso($Linha[1]);
		}
}

if( $ContribuinteNome == "" ){
		$InscricaoValida = "N";
}else{
		$InscricaoValida = "S";
}

$db->disconnect();

$Url = "$NomePrograma?ProgramaSelecao=$ProgramaSelecao&InscricaoValida=$InscricaoValida&Origem=B&Destino=$Destino&Sequencial=$Sequencial&Critica=$Critica";
if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//RedirecionaPost($Url);
header('Location:'.$Url);
?>
