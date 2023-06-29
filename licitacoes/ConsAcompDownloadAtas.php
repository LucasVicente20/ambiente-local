<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompDownloadAtas.php
# Autor:    Rossana Lira
# Data:     06/05/2003
# Objetivo: Programa de Download das Atas das Fases da Licitação
#----------------------------------
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

session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$GrupoCodigo          = $_GET['GrupoCodigo'];
	$Processo             = $_GET['Processo'];
	$ProcessoAno          = $_GET['ProcessoAno'];
	$ComissaoCodigo       = $_GET['ComissaoCodigo'];
	$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
	$FaseCodigo           = $_GET['FaseCodigo'];
	$AtaCodigo            = $_GET['AtaCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAcompDownloadAtas.php";

if (($GrupoCodigo != "") && 
	($ComissaoCodigo != "") && 
	($Processo != "") && 
	($ProcessoAno != "") && 
	($FaseCodigo != "") && 
	($AtaCodigo != "")) {

	$db     = Conexao();
	$sql    = "SELECT EATASFNOME FROM SFPC.TBATASFASE ";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";
	$sql   .= "   AND CFASESCODI = $FaseCodigo AND CATASFCODI = $AtaCodigo ";
	
	if ($_SESSION['PermitirAuditoria'] != 'S') { //'PermitirAuditoria'- Variável de sessão que permite fazer download de arquivos excluídos e armazenados.
		$sql .= "AND (FATASFEXCL='N' or FATASFEXCL is null)";
	}
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$NomeArquivo = $Linha[0];
		}
	}
	
	$db->disconnect();
	AddMenuAcesso('/carregarArquivo.php');

	$ArquivoNomeServidor = "licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$AtaCodigo;
	$Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArquivoNomeServidor;

	if (file_exists($Arquivo)) {
		$url = "../carregarArquivo.php?arq=".urlencode($ArquivoNomeServidor)."&arq_nome=".urlencode($NomeArquivo);
		header("Location: $url");
		exit();
	}
}
