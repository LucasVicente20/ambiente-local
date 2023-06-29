<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompDownloadDoc.php
# Autor:    Rossana Lira
# Data:     06/05/2003
# Objetivo: Programa de Download dos Documentos da Licitação
#---------------------------
# Alterado: ???
# Data:     23/08/2006 - Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
# Alterado: Ariston Cordeiro
# Data:     20/03/2011 - Proibir visualização de arquivos marcados como excluídos
#----------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

session_start();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$GrupoCodigo          = $_GET['GrupoCodigo'];
		$Processo             = $_GET['Processo'];
		$ProcessoAno          = $_GET['ProcessoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$DocCodigo            = $_GET['DocCodigo'];
}else{
		$Arquivo              = $_GET['Arquivo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAcompDownloadDoc.php";

if( ( $GrupoCodigo != "" ) && ( $ComissaoCodigo != "" ) && ( $Processo != "" ) && ( $ProcessoAno != "" )&& ( $DocCodigo != "" ) ){


		$db     = Conexao();
		$sql = "SELECT EDOCLINOME ";
		$sql.= "FROM SFPC.TBDOCUMENTOLICITACAO WHERE CLICPOPROC = $Processo AND ";
		$sql.= "ALICPOANOP = $ProcessoAno AND CCOMLICODI = $ComissaoCodigo AND ";
		$sql.= "CGREMPCODI = $GrupoCodigo AND CDOCLICODI = $DocCodigo ";

		if($_SESSION['PermitirAuditoria']!='S'){//'PermitirAuditoria'- Variável de sessão que permite fazer download de arquivos excluídos e armazenados.
			$sql.= "AND (FDOCLIEXCL='N' or FDOCLIEXCL is null)";
		}
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		while( $Linha = $result->fetchRow() ){
				$NomeArquivo = $Linha[0];
		}
		$db->disconnect();

		$ArquivoNomeServidor = "licitacoes/DOC".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocCodigo;
		$Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;
		
		if( file_exists($Arquivo) ){
				$url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);

				header("Location: " . $url);
				exit();
		}
}
?>
