<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAuditoriaSelecionar.php
# Autor:    Ariston Cordeiro
# Data:     02/05/11
# Objetivo: Programa de Pesquisa de Auditoria Licitação
#--------------------------------
# Alterado:
# Data:
#---------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAuditoria.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ProcessoAno         = $_POST['ProcessoAno'];
		$Processo         = $_POST['Processo'];
}else if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Botao                = null;
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$ProcessoAno         = $_GET['ProcessoAno'];
		$Processo         = $_GET['Processo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAuditoriaSelecionar.php";

/*if( $Botao == "Pesquisar" ){
	$Mensagem = "Informe: ";
	if($OrgaoLicitanteCodigo=="" AND $ComissaoCodigo==""){
		$Mens = 1; $Tipo = 1; $Critica = 0;
		$Mensagem .= "Orgão ou Comissão";
	}
}else*/
if( $Botao == "Limpar" ){
	$Botao                = "";
	$Objeto               = "";
	$OrgaoLicitanteCodigo = "";
	$ComissaoCodigo       = "";
	$ModalidadeCodigo     = "";
	$ProcessoAno         = "";
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Historico.Botao.value=valor;
	document.Historico.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAuditoriaSelecionar.php" method="post" name="Historico">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Auditoria
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					AUDITORIA DE LICITAÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para consultar a Auditoria de Licitações, selecione o item de pesquisa e  clique no botão "Pesquisar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal">
		  				  	      <select name="OrgaoLicitanteCodigo" class="textonormal">
													<option value="">Todos os Órgãos Licitantes...</option>
														<?
														$db     = Conexao();
														$sql    = "SELECT CORGLICODI,EORGLIDESC ";
														$sql   .= "  FROM SFPC.TBORGAOLICITANTE ";
														$sql   .= " ORDER BY EORGLIDESC";
			                  		$result = $db->query($sql);
														if( PEAR::isError($result) ){
																EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
														}
														while( $Linha = $result->fetchRow() ){
														   	if( $Linha[0] == $OrgaoLicitanteCodigo ){
														    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
														   	}else{
														      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
														   	}
										     		}
			    	              	$db->disconnect();
														?>
													</option>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal" bgcolor="#DCEDF7">Comissão</td>
		              		<td class="textonormal">
		  				  	      <select name="ComissaoCodigo" class="textonormal">
													<option value="">Todas as Comissões...</option>
														<?
														$db     = Conexao();
														$sql    = "SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI ";
														$sql   .= "  FROM SFPC.TBCOMISSAOLICITACAO ";
														$sql   .= "ORDER BY CGREMPCODI,ECOMLIDESC";
			                  		$result = $db->query($sql);
														if( PEAR::isError($result) ){
																EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
														}else{
																while( $Linha = $result->fetchRow() ){
																   	if( $Linha[0] == $ComissaoCodigo ){
																    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
																   	}else{
																      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
																   	}
												     		}
												    }
			    	              	$db->disconnect();
														?>
													</option>
											  </select>
										  </td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ano Processo</td>
											<td class="textonormal">
		  				  	      <input type="text" name="ProcessoAno" size="4" maxlength="4" value="<?php echo $ProcessoAno;?>" class="textonormal">
										  </td>
										</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Num Processo</td>
											<td class="textonormal">
		  				  	      <input type="text" name="Processo" size="4" maxlength="4" value="<?php echo $Processo;?>" class="textonormal">
										  </td>
										</tr>
	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
      	      		<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
      	      		<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
                	<input type="hidden" name="Botao" value="">
		          	</td>
		        	</tr>
		        	<?php
		       		if($Botao == "Pesquisar" AND $Mens==0){
								$db = Conexao();
								$sql  = "
								select * from  (
									SELECT
										c.EGREMPDESC, a.CLICPOPROC, a.ALICPOANOP, b.EORGLIDESC,
										a.CORGLICODI, a.CGREMPCODI, a.CCOMLICODI, d.ECOMLIDESC,
										NULL as codDelete
									FROM
										SFPC.TBLICITACAOPORTAL a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c,
										SFPC.TBCOMISSAOLICITACAO d
									WHERE
										a.CORGLICODI = b.CORGLICODI and
										a.CGREMPCODI = c.CGREMPCODI AND
										a.CCOMLICODI = d.CCOMLICODI
								";
								if( $ComissaoCodigo != "" ){ $sql .= "and a.CCOMLICODI = $ComissaoCodigo "; }
								if( $OrgaoLicitanteCodigo != "" ){ $sql .= "and a.CORGLICODI = $OrgaoLicitanteCodigo"; }
								if( $ProcessoAno != "" ){ $sql .= "and a.ALICPOANOP = $ProcessoAno"; }
								if( $Processo != "" ){ $sql .= "and a.CLICPOPROC = $Processo"; }
								$sql .= "
									UNION
									-- Licitacoes deletadas da tabela
									SELECT
										c.EGREMPDESC, a.CLICPOPROC, a.ALICPOANOP, b.EORGLIDESC,
										a.CORGLICODI, a.CGREMPCODI, a.CCOMLICODI, d.ECOMLIDESC,
										a.CLPLOGCODI as codDelete
									FROM
										SFPC.TBLICITACAO_LOG a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c,
										SFPC.TBCOMISSAOLICITACAO d
									WHERE
										a.CORGLICODI = b.CORGLICODI and
										a.CGREMPCODI = c.CGREMPCODI AND
										a.CCOMLICODI = d.CCOMLICODI AND
										a.XLPLOGTABL = 'tblicitacaoportal' AND
										a.XLPLOGCMND = 'DELETE'
								";
								if( $ComissaoCodigo != "" ){ $sql .= "and a.CCOMLICODI = $ComissaoCodigo "; }
								if( $OrgaoLicitanteCodigo != "" ){ $sql .= "and a.CORGLICODI = $OrgaoLicitanteCodigo"; }
								if( $ProcessoAno != "" ){ $sql .= "and a.ALICPOANOP = $ProcessoAno"; }
								if( $Processo != "" ){ $sql .= "and a.CLICPOPROC = $Processo"; }
								$sql .= "
								) as processos
								order by EGREMPDESC, ECOMLIDESC, ALICPOANOP DESC, CLICPOPROC DESC, codDelete DESC
								";

								$result = $db->query($sql);
								if( PEAR::isError($result) ){
									EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
								}
								$Rows = $result->numRows();
								$GrupoDescricao = "";
								?>
										<tr>
										<td class='textonormal' style ="padding :0; border : 0">
								<?php
										echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
										echo "	          	<tr>\n";
										echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"1\">\n";
										echo "		    					<font class=\"titulo3\">RESULTADO DA PESQUISA- PROCESSOS LICITATÓRIOS\n";
										echo "		          	</td>\n";
										echo "		        	</tr>\n";
										echo "	          	<tr>\n";
										echo "	            	<td colspan=\"1\" class=\"textonormal\">\n";
										echo "	        	    		Para visualizar mais informações sobre a Licitação, clique no número da Licitação desejada. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\".\n";
										echo "		          	</td>\n";
										echo "		        	</tr>\n";
								if( $Rows != 0 ){
										while( $Linha = $result->fetchRow() ){
												$GrupoDescricaoItem = $Linha[0];
												$NProcesso 	= substr($Linha[1] + 10000,1);
												$ProcessoItem = $Linha[1];
												$NProcessoAno = $Linha[2];
												$OrgaoDescricao = $Linha[3];
												$OrgaoItem = $Linha[4];
												$GrupoItem = $Linha[5];
												$ComissaoItem = $Linha[6];
												$ComissaoDescricaoItem = $Linha[7];
												$CodExcluidoItem = $Linha[8];

												if( $GrupoDescricao != $GrupoDescricaoItem ){
														$GrupoDescricao = $GrupoDescricaoItem;
														echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"1\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
														$ModalidadeDescricao = "";
												}
												if( $ComissaoDescricao != $ComissaoDescricaoItem ){
														$ComissaoDescricao = $ComissaoDescricaoItem;
														echo "<tr><td class=\"titulo2\" colspan=\"1\" color=\"#000000\">$ComissaoDescricao</tr></td>\n";
												}
												echo "<tr>\n";
												$Url = "ConsAuditoria.php";
						            $Parametros = "?Grupo=".$GrupoItem."&Processo=".$ProcessoItem."&Ano=".$NProcessoAno."&Comissao=".$ComissaoItem."&Orgao=".$OrgaoItem."&CodDelete=".$CodExcluidoItem."";
						            $Url .= $Parametros;
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><font color=\"#000000\">";
												if(!is_null($CodExcluidoItem) and $CodExcluidoItem !=""){
													echo "<S>";
												}
												echo "<a href=\"$Url\">$NProcesso/$NProcessoAno</a>";
												if(!is_null($CodExcluidoItem) and $CodExcluidoItem !=""){
													echo "</S> (excluído)";
												}
												echo "</font></td></tr>\n";
										}
								}else{
										echo "<tr><td colspan=\"1\">Nenhuma ocorrência encontrada.</td></tr>\n";
								}

								$db->disconnect();

						?>
    	  	  </table>
					</td>
					<?php } ?>
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
document.Historico.Objeto.focus();
//-->
</script>
