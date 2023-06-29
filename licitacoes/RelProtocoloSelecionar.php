<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelProtocoloSelecionar.php
# Autor:    Roberta Costa
# Data:     20/05/03
# Objetivo: Programa de Relatório do Protocolo de Entrega
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as comissões de acordo c/as cadastradas p/o
#           usuário logado
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		04/07/2018
# Objetivo:	Tarefa Redmine 95885
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208783
#-------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     10/10/2022
# Objetivo: Tarefa Redmine 206442
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelProtocoloParticipante.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica   = $_POST['Critica'];
		$Licitacao = $_POST['Licitacao'];
}else{
		$Critica   = $_GET['Critica'];
		$Mensagem  = urldecode($_GET['Mensagem']);
		$Mens      = $_GET['Mens'];
		$Tipo      = $_GET['Tipo'];
}

# Critica dos Campos #
if( $Critica == 1 ){
		if( $Licitacao == "" ) {
		    $Mens = 1;$Tipo = 2; $Critica = 0;
				$Mensagem .= "Informe: <a href=\"javascript:document.Relatorio.Licitacao.focus();\" class=\"titulo2\">Processo</a>";
		}else{
		    $NLicitacao       = explode("_",$Licitacao);
				$Processo         = $NLicitacao[0];
				$AnoProcesso      = $NLicitacao[1];
				$OrgaoCodigo      = $NLicitacao[2];
				$ComissaoCodigo   = $NLicitacao[3];
				$GrupoCodigo      = $NLicitacao[4];
				$ModalidadeCodigo = $NLicitacao[5];
				$Licitacao        = $NLicitacao[6];
				$Url = "RelProtocoloImpressao.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&Licitacao=$Licitacao";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
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
function enviar(valor){
	document.Relatorio.Botao.value=valor;
	document.Relatorio.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelProtocoloSelecionar.php" method="post" name="Relatorio">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatórios > Protocolo de Entrega
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									PROTOCOLO DE ENTREGA - SELECIONAR
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para gerar um protocolo de entrega selecione um Processo Licitatório na lista abaixo e clique no botão "Selecionar".
									</p>
								</td>
							</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Processo</td>
	          	    		<td class="textonormal" bgcolor="#FFFFFF">
			                  <select name="Licitacao" class="textonormal">
			                  	<option value="">Selecione um Processo Licitatório...</option>
			                  	<?php
													# Mostra as licitações cadastradas #
			                  	$db     = Conexao();
													$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CORGLICODI, B.ECOMLIDESC, ";
													$sql   .= "       B.CCOMLICODI, C.CGREMPCODI, C.EGREMPDESC, A.CMODLICODI, ";
													$sql   .= "       A.CLICPOCODL ";
													$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D ";
													$sql   .= " WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
													$sql   .= "   AND D.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
													$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
													$sql   .= "   AND MAKE_DATE(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS' "; //CR 206442 MAKE_DATE
													$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    $CodErroEmail  = $result->getCode();
															$DescErroEmail = $result->getMessage();
												  		ExibeErroBD("RelProtocoloSelecionar.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
													}else{
															$ComissaoCodigoAnt = "";
															while( $Linha = $result->fetchRow() ){
																	if( $Linha[4] != $ComissaoCodigoAnt ){
																			$ComissaoCodigoAnt = $Linha[4];
																			echo "<option value=\"\">$Linha[3]</option>\n" ;
																	}
																	$NProcesso = substr($Linha[0] + 10000,1);
																	echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[4]_$Linha[5]_$Linha[7]_$Linha[8]\">&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
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
			        	<td class="textonormal" align="right">
							    <input type="hidden" name="Critica" value="1">
	                <input type="submit" name="Selecionar" value="Selecionar" class="botao">
			          </td>
							</tr>
						</table>
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
<script language="javascript" type="">
<!--
document.Relatorio.Licitacao.focus();
//-->
</script>
