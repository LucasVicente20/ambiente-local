<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDocumentoLicitacaoPesquisar.php
# Autor:    Rodrigo Melo
# Data:     18/03/11
# Objetivo: Programa de Seleção de Documento de Licitação
# OBS.:     Tabulação 2 espaços
#			Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsDocumentoLicitacaoResultado.php');

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$Selecao              = $_POST['Selecao'];
		$Objeto               = $_POST['Objeto'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
}
else{
		$Selecao = $_GET['Selecao'];
		if (!is_null($_GET['Selecao'])){
			$_SESSION['Selecao']              = $Selecao;
			$_SESSION['Objeto']               = null;
		    $_SESSION['OrgaoLicitanteCodigo'] = null;
		    $_SESSION['ComissaoCodigo']       = null;
		    $_SESSION['ModalidadeCodigo']     = null;
		    $_SESSION['RetornoPesquisa']      = null;
		    $_SESSION['Pesquisar']            = null;
		}
}

if ( $_SESSION['RetornoPesquisa'] == 1 ){
	$Mensagem             = $_SESSION['Mensagem'];
	$Mens                 = $_SESSION['Mens'];
	$Tipo                 = $_SESSION['Tipo'];
	$Objeto               = $_SESSION['Objeto'];
	$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
	$ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
	$MocalidadeCodigo     = $_SESSION['ModalidadeCodigo'];
	$Selecao              = $_SESSION['Selecao'];
	$_SESSION['RetornoPesquisa'] = "";
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsDocumentoLicitacaoPesquisar.php";

if( $Botao == "Pesquisar" ){
		$_SESSION['Selecao']              = $Selecao;
		$_SESSION['Objeto']               = $Objeto;
		$_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
		$_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
		$_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
		$_SESSION['LicitacaoAno']         = $LicitacaoAno;
		$_SESSION['Pesquisar']            = 1;
		header("location: ConsDocumentoLicitacaoResultado.php");
		exit();
}elseif( $Botao == "Limpar" ){
		$_SESSION['Selecao'] = $Selecao;

    $Url = "ConsDocumentoLicitacaoPesquisar.php?Selecao=$Selecao";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url );
	  exit();
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
	document.Acomp.Botao.value=valor;
	document.Acomp.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsDocumentoLicitacaoPesquisar.php" method="post" name="Acomp">
<br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Documento > Auditoria
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					CONSULTA DE DOCUMENTOS DE LICITAÇÃO (AUDITORIA)
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para realizar o Acompanhamento das Licitações, selecione o item de pesquisa e  clique no botão "Pesquisar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width=100>Objeto
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1" size="1">
												<input type="hidden" name="Selecao" value="<?php echo $Selecao;?>" size="1">
											</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
	          	    		<td class="textonormal" >
		  				  	      <select name="OrgaoLicitanteCodigo" class="textonormal">
													<option value="">Todos os Órgãos Licitantes...</option>
													<?
													$db     = Conexao();
													$sql    = "SELECT CORGLICODI,EORGLIDESC ";
													$sql   .= "FROM SFPC.TBORGAOLICITANTE ";
													$sql   .= "ORDER BY EORGLIDESC";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if( $Linha[0] == $OrgaoLicitante ){
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
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
											<td class="textonormal">
		  				  	      <select name="ModalidadeCodigo" class="textonormal">
													<option value="">Todas as Modalidades...</option>
													<?
											    $db     = Conexao();
													$sql    = "SELECT CMODLICODI, EMODLIDESC ";
													$sql   .= "  FROM SFPC.TBMODALIDADELICITACAO ";
													$sql   .= " ORDER BY AMODLIORDE";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
													   	if( $Linha[0] == $ModalidadeCodigo ){
													    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
													   	}else{
													      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
													   	}
									     		}
									     		$db->disconnect();
													?>
											  </select>
										  </td>
										</tr>										
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
											<td class="textonormal">
		  				  	      <select name="LicitacaoAno" class="textonormal">
													<?
											    $db     = Conexao();
													$sql    = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
													$sql   .= "  FROM SFPC.TBLICITACAOPORTAL ";
													$sql   .= " WHERE TO_CHAR(TLICPODHAB,'YYYY') <= '".date('Y')."' ";
													$sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
													   	if( $Linha[0] == $LicitacaoAno ){
													    		echo "<option value=\"$Linha[0]\" selected>$Linha[0]</option>\n";
													   	}else{
													      	echo "<option value=\"$Linha[0]\">$Linha[0]</option>\n";
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
	          	  	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
	          	    <input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
document.Acomp.Objeto.focus();
//-->
</script>
