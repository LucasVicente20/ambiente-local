<?php
#----------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDocSolicitacaoCompra.php
# Autor:    Rossana Lira / Dolores Santa Cruz
# Data:     09/02/2007
# Objetivo: Programa de Download dos Documentos da Solicitação de compras
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$DocDownload		= $_GET['DocDownload'];
	$NomeDocumento		= $_GET['NomeDocumento'];
}
$Arquivo = $DocDownload;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsDocSolicitacaoCompra.php";

if( file_exists($Arquivo) ){
		$Tmp = "../compras/arquivos/tmp/".urlencode($NomeDocumento);
		if( !file_exists($Tmp) ){
				copy ($Arquivo, $Tmp);
		}
		header("Location: " . $Tmp);
		exit;
}else{
	echo "Arquivo não existe   ".$Arquivo;
}
if(($DocDownload!="")AND($NomeDocumento!="")){
	$Path	=	 "../compras/arquivos/tmp/";
	$Diretorio	= dir($Path);
	while( $Arquivo = $Diretorio->read() ){
				if($Arquivo != "." && $Arquivo != ".." && $Arquivo != "tmp" ){
						if( mktime(date("H"),date("i")-10,date("s"),date("m"),date("d"),date("Y")) > filectime($Diretorio->path.$Arquivo) ){
								unlink ($Diretorio->path.$Arquivo);
						}
				}
		}
		$Diretorio->close();
}

?>
