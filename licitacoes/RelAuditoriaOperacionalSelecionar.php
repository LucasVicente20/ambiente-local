<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAuditoriaOperacionalSelecionar.php
# Autor:    Roberta Costa
# Data:     03/01/05
# Objetivo: Programa de Relatório da Auditoria Operacional
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelAuditoriaOperacionalPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$OrgaoLicitante = $_POST['OrgaoLicitante'];
		$Comissao       = $_POST['Comissao'];
		$Exercicio      = $_POST['Exercicio'];
}else{
		$Mensagem  = urldecode($_GET['Mensagem']);
		$Mens      = $_GET['Mens'];
		$Tipo      = $_GET['Tipo'];
}

# Critica dos Campos #
if( $Botao == "Gerar" ){
		$Mens      = 0;
		$Mensagem .= "Informe: ";
		if( $Exercicio == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Auditoria.Exercicio.focus();\" class=\"titulo2\">Exercício</a>";
		}else{
				if( ! SoNumeros($Exercicio) ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Auditoria.Exercicio.focus();\" class=\"titulo2\">Exercício Válido</a>";
				}
		}
		if( $Mens == 0 ) {
				$Url = "RelAuditoriaOperacionalPdf.php?OrgaoLicitante=$OrgaoLicitante&Comissao=$Comissao&Exercicio=$Exercicio&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Auditoria.Botao.value=valor;
	document.Auditoria.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAuditoriaOperacionalSelecionar.php" method="post" name="Auditoria">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatórios > Auditoria > Por Órgão
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
									AUDITORIA POR ÓRGÃO - SELECIONAR
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para gerar o relatório de Auditoria Por Órgão preencha os campos abaixo e clique no botão "Gerar".
										Os campos obrigatórios estão com *.<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal">
		  				  	      <select name="OrgaoLicitante" class="textonormal">
													<option value="">Todos os Órgãos Licitantes...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT A.CORGLICODI, A.EORGLIDESC FROM SFPC.TBORGAOLICITANTE A, SFPC.TBGRUPOORGAO B";
											    $sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND B.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
											    $sql   .= "   AND A.FORGLISITU <> 'I' ORDER BY A.EORGLIDESC";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $OrgaoLicitanteCodigo ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
		    	              	$db->disconnect();
													?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal" bgcolor="#DCEDF7">Comissão </td>
		              		<td class="textonormal">
		  				  	      <select name="Comissao" class="textonormal">
													<option value="">Todas as Comissões...</option>
													<?php
													$db     = Conexao();
											    $sql    = "SELECT A.CCOMLICODI, A.ECOMLIDESC ";
											    $sql   .= "FROM SFPC.TBCOMISSAOLICITACAO A, SFPC.TBUSUARIOCOMIS B ";
											    $sql   .= "WHERE B.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND B.CUSUPOCODI =".$_SESSION['_cusupocodi_']." ";
											    $sql   .= "  AND B.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = B.CGREMPCODI ";
											    $sql   .= "  AND A.FCOMLISTAT = 'A' ORDER BY A.ECOMLIDESC";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
											  </select>
										  </td>
	            			</tr>
										<tr>
						        	<td class="textonormal" bgcolor="#DCEDF7">Exercício*</td>
		              		<td class="textonormal">
		  				  	      <?php if( $Exercicio == "" ){ $Exercicio = date("Y"); }?>
		  				  	      <input type="text" name="Exercicio" size="5" maxlength="4" value="<?php echo $Exercicio ?>" class="textonormal" >
						          </td>
										</tr>
	      	  			</table>
		          	</td>
		        	</tr>
							<tr>
			        	<td class="textonormal" align="right">
	                <input type="submit" value="Gerar" onClick="javascript:enviar('Gerar');" class="botao">
							    <input type="hidden" name="Botao" value="">
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
document.Auditoria.OrgaoLicitante.focus();
//-->
</script>
