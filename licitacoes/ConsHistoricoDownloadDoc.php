<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompDownloadDoc.php
# Autor:    Rossana Lira
# Data:     06/05/2003
# Objetivo: Programa de Download dos Documentos da Licitação (Histórico)
#--------------------------------
# Alterado: ???
# Data:     23/08/2006 - Apaga arquivo temporário anterior apenas se ele
#                        foi criado a mais de 10 minutos
# Alterado: Ariston Cordeiro
# Data:     20/03/2011 - Proibir visualização de arquivos marcados como excluídos
#----------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$GrupoCodigo          = $_GET['GrupoCodigo'];
		$LicitacaoProcesso    = $_GET['LicitacaoProcesso'];
		$LicitacaoAno         = $_GET['LicitacaoAno'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$DocCodigo            = $_GET['DocCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsHistoricoDownloadDoc.php";

# Critica aos Campos #
if( ( $GrupoCodigo != "" ) && ( $ComissaoCodigo != "" ) && ( $LicitacaoProcesso != "" ) && ( $LicitacaoAno != "" )&& ( $DocCodigo != "" ) ){

		$db     = Conexao();
		$sql    = "SELECT EDOCLINOME FROM SFPC.TBDOCUMENTOLICITACAO ";
		$sql   .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";
		$sql   .= "   AND CDOCLICODI = $DocCodigo AND (FDOCLIEXCL='N' or FDOCLIEXCL is null) ORDER BY EDOCLINOME";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		$Linha = $result->fetchRow();
		$NomeArquivo = $Linha[0];

		$db->disconnect();

		$ArquivoNomeServidor = "licitacoes/DOC".$GrupoCodigo."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocCodigo;
		$Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;
		if( file_exists($Arquivo) ) {

				$url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);
				header("Location: $url");
				exit();
		}
}
?>
