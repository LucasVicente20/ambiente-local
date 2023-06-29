<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadFaseLicitacaoSelecionar.php
# Autor:    Rossana Lira
# Data:     28/04/03
# Objetivo: Programa de Seleção de Fase de Licitação
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
#-------------------------------------------------------------------------
# Autor:    Pitang Agile TI
# Data:     10/03/2015
# Objetivo: CR 170 - Fase de Licitação - Incluir e Manter - só exibir a fase de
#           arquivamento para o perfil corporativo
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:			04/07/2018
# Objetivo:	Tarefa Redmine 95885
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208783
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/CadFaseLicitacaoAlterar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$ProcessoAnoComissaoOrgaoFase = $_POST['ProcessoAnoComissaoOrgaoFase'];
	$Critica                      = $_POST['Critica'];
} else {
	$Critica   = $_GET['Critica'];
	$Mensagem  = urldecode($_GET['Mensagem']);
	$Mens      = $_GET['Mens'];
	$Tipo      = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if ($Critica == 1 ){
	$Mens     = 0;
	$Mensagem = "Informe: ";

	if ($ProcessoAnoComissaoOrgaoFase == "") {
	    $Mens      = 1;
	    $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.FaseLicitacao.ProcessoAnoComissaoOrgaoFase.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    } else {
		$NProcessoAnoComissao = explode("_",$ProcessoAnoComissaoOrgaoFase);
		$Processo             = substr($NProcessoAnoComissao[0] + 10000,1);
		$ProcessoAno          = $NProcessoAnoComissao[1];
		$ComissaoCodigo       = $NProcessoAnoComissao[2];
		$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];
		$FaseCodigo           = $NProcessoAnoComissao[4];
		$ModalidadeCodigo     = $NProcessoAnoComissao[5];
		$RegistroPreco        = trim($NProcessoAnoComissao[6]);

		$Url = "CadFaseLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&ModalidadeCodigo=$ModalidadeCodigo&RegistroPreco=$RegistroPreco";

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
<script language="JavaScript">
	<!--
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadFaseLicitacaoSelecionar.php" method="post" name="FaseLicitacao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
  			<!-- Caminho -->
  			<tr>
    			<td width="150"><img border="0" src="../midia/linha.gif"></td>
    			<td align="left" class="textonormal" colspan="2">
      				<font class="titulo2">|</font>
      				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Fase Licitação > Manter
    			</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
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
	           					MANTER - FASE DE LICITAÇÃO
	          				</td>
    	    			</tr>
        				<tr>
          					<td class="textonormal">
             					<p align="justify">
             						Para atualizar/excluir um Fase de Licitação cadastrada, selecione o Processo/Fase e clique no botão "Selecionar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" width="30%">Processo </td>
	                					<td class="textonormal">
    	              						<select name="ProcessoAnoComissaoOrgaoFase" class="textonormal">
        	          							<option value="">Selecione um Processo Licitatório/Fase...</option>
            	      							<!-- Mostra as licitações/fases cadastradas -->
                  								<?php
												$db     = Conexao();

												$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, ";
												$sql   .= "       C.EGREMPDESC, A.CORGLICODI, D.CFASESCODI, D.EFASESDESC, ";
												$sql   .= "       D.AFASESORDE, F.CMODLICODI, F.FLICPOREGP ";
												$sql   .= "  FROM SFPC.TBFASELICITACAO A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, ";
												$sql   .= "       SFPC.TBFASES D, SFPC.TBUSUARIOCOMIS E, SFPC.TBLICITACAOPORTAL  F ";
												$sql   .= " WHERE E.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND E.CUSUPOCODI =".$_SESSION['_cusupocodi_']."";
												$sql   .= "   AND E.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = E.CGREMPCODI ";
												$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
												$sql   .= "   AND A.CFASESCODI = D.CFASESCODI AND F.CLICPOPROC = A.CLICPOPROC ";
												$sql   .= "   AND F.ALICPOANOP = A.ALICPOANOP AND F.CGREMPCODI = A.CGREMPCODI ";
												$sql   .= "   AND F.CCOMLICODI = A.CCOMLICODI AND F.CORGLICODI = A.CORGLICODI ";
												$sql   .= "   AND MAKE_DATE(A.ALICPOANOP, 1,1) > CURRENT_DATE - INTERVAL '5 YEARS' ";
     											$sql   .= " ORDER BY A.ALICPOANOP DESC, A.CLICPOPROC DESC, A.TFASELDATA ASC";

												$result = $db->query($sql);

												if (db::isError($result)) {
 											    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													$ComissaoCodigoAnt = "";

													while ($Linha = $result->fetchRow()) {
														if ($Linha[2] != $ComissaoCodigoAnt) {
															$ComissaoCodigoAnt = $Linha[2];
															echo "<option value=\"\">$Linha[3]</option>\n" ;
														}

														if ($Linha[6] != "19" || ($Linha[6] == "19" && $_SESSION["_fperficorp_"] == "S")) {
															$NProcesso = substr($Linha[0] + 10000,1);
															echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]_$Linha[9]_$Linha[10]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso-$Linha[1]/$Linha[7]</option>\n" ;
														}
													}
												}

												$db->disconnect();
												?>
	                  						</select>
    	            					</td>
        	      					</tr>
            					</table>
          					</td>
        				</tr>
	        			<tr>
    	      				<td class="textonormal"align="right">
								<input type="hidden" name="Critica" value="1">
								<input type="submit" value="Selecionar" class="botao">
	          				</td>
    	    			</tr>
	      			</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>
<script language="JavaScript">
	<!--
	document.FaseLicitacao.ProcessoAnoComissaoOrgaoFase.focus();
	//-->
</script>