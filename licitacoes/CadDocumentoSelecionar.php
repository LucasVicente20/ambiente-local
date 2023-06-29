<?php
/**
 * Portal de Compras
 * Prefeitura do Recife
 * 
 * Programa: CadDocumentoSelecionar.php
 * Autor:    Rossana Lira
 * Data:     22/04/2003
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/07/2018
 * Objetivo: Tarefa Redmine 95885
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     27/12/2018
 * Objetivo: Tarefa Redmine 208783
 * ---------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/01/2023
 * Objetivo: Tarefa Redmine 277360
 * ---------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/CadDocumentoManter.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$LicitacaoProcessoAnoComissaoOrgao = $_POST['LicitacaoProcessoAnoComissaoOrgao'];
	$Critica                           = $_POST['Critica'];
} else {
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadDocumentoSelecionar.php";

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);

if ($Critica == 1) {
	$Mens = 0;
	$Mensagem = "Informe: ";

	if ($LicitacaoProcessoAnoComissaoOrgao == "") {
	    $Mens      = 1;
		$Tipo      = 2;
		$Troca     = 1;
        $Mensagem .= "<a href=\"javascript: document.Documento.DocumentoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    } else {
		$NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissaoOrgao);
		$LicitacaoProcesso    = substr($NProcessoAnoComissao[0] + 10000,1);
		$LicitacaoAno         = $NProcessoAnoComissao[1];
		$ComissaoCodigo       = $NProcessoAnoComissao[2];
		$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];

		$Url = "CadDocumentoManter.php?LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";

		if (!in_array($Url,$_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}

		header("location: ".$Url);
		exit();
    }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadDocumentoSelecionar.php" method="post" name="Documento">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
  			<!-- Caminho -->
  			<tr>
    			<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    			<td align="left" class="textonormal" colspan="2">
      				<font class="titulo2">|</font>
      				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Documento
    			</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1 ) {
				?>
				<tr>
	  				<td width="150"></td>
	  				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					MANTER - DOCUMENTO DE LICITAÇÃO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" bgcolor="#FFFFFF">
             					<p align="justify">
             						Para incluir/excluir um Documento cadastrado, selecione o Processo e clique no botão "Selecionar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table border="0" width="100%" summary="">
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                						<td class="textonormal" bgcolor="#FFFFFF">
                  							<select name="LicitacaoProcessoAnoComissaoOrgao" class="textonormal">
                  								<option value="">Selecione um Processo Licitatório...</option>
                  								<?php
												$db = Conexao();

												$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, ";
												$sql   .= "       B.ECOMLIDESC, C.EGREMPDESC, A.CORGLICODI ";
												$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D ";
												$sql   .= " WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
												$sql   .= "   AND D.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
												$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
												$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";

												$result = $db->query($sql);

												if (PEAR::isError($result)) {
										    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													$ComissaoCodigoAnt = "";

													while ($Linha = $result->fetchRow()) {
														if ($Linha[2] != $ComissaoCodigoAnt) {
															$ComissaoCodigoAnt = $Linha[2];
															echo "<option value=\"\">$Linha[3]</option>\n" ;
														}

														$NProcesso = substr($Linha[0] + 10000,1);
														echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]\">&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
													}
												}

												$db->disconnect();
												?>
                  							</select>
                  							<input type="hidden" name="Critica" value="1">
                						</td>
              						</tr>
            					</table>
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" align="right">
             					<input type="submit" value="Selecionar" class="botao">
          					</td>
        				</tr>
      				</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
<script language="javascript" type="">
	<!--
	document.Documento.LicitacaoProcessoAnoComissaoOrgao.focus();
	//-->
</script>
</body>
</html>