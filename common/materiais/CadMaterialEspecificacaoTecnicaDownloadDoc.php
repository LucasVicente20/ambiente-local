<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialEspecificacaoTecnicaDownloadDoc.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Objetivo: Programa de Download das Especificações Técnicas de Materiais
# OBS.:     Tabulação 2 espaços
#           Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
	$GrupoCodigo          = $_GET['GrupoCodigo'];
	$ClasseCodigo         = $_GET['ClasseCodigo'];
	$DocCodigo            = $_GET['DocCodigo'];
}

session_start();



# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadMaterialEspecificacaoTecnicaDownloadDoc.php";

$caminhoEspecificacoes = $GLOBALS["CAMINHO_UPLOADS"]."materiais/";
$caminhoEspecificacoesTmp = $GLOBALS["CAMINHO_UPLOADS"]."tmp/materiais/";


if( ( $GrupoCodigo != "" ) && ( $ClasseCodigo != "" ) && ( $DocCodigo != "" ) ){

	$db = Conexao();
	$sql  = "SELECT EESPTMNOME ";
	$sql .= "  FROM SFPC.TBESPECIFICACAOTECNICA ";
	$sql .= " WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo ";
	$sql .= "   AND CESPTMCODI = $DocCodigo ";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}
	while( $Linha = $result->fetchRow() ){
		$NomeArquivo = $Linha[0];
	}
	$db->disconnect();

	$ArquivoNomeServidor = "ESPECIFICACAO_".$GrupoCodigo."_".$ClasseCodigo."_".$DocCodigo;
	$Arquivo = $caminhoEspecificacoes.$ArquivoNomeServidor;
	if( file_exists($Arquivo) ){
		$Tmp = $caminhoEspecificacoesTmp.urlencode($NomeArquivo);

		$urlEspecificacoesTmp = "../carregarArquivo.php?arq=materiais/".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);
		header("Location: " . $urlEspecificacoesTmp);
		exit();
	}
}
?>
