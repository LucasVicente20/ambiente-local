<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsHistoricoPesquisar.php
# Autor:    Rossana Lira
# Data:     06/05/03
# Objetivo: Programa de Pesquisa de Histórico Licitação
# Alterado: Carlos Abreu
# Data:     20/02/2007 - Colocar ano como argumento da pesquisa para reduzir estouro da variável de sessão
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsHistoricoPesquisar.php' );
AddMenuAcesso( '/licitacoes/ConsHistoricoResultado.php' );
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Selecao              = $_POST['Selecao'];
		$Objeto               = $_POST['Objeto'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$LicitacaoAno         = $_POST['LicitacaoAno'];
		$TipoItemLicitacao    = $_POST['TipoItemLicitacao'];
		$Item                 = $_POST['Item'];
		
}else{
		$Selecao              = $_GET['Selecao'];
		$Mensagem             = $_GET['Mensagem'];
		$Mens                 = $_GET['Mens'];
	 	$Tipo                 = $_GET['Tipo'];
	 	$Objeto               = $_GET['Objeto'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
		$LicitacaoAno         = $_GET['LicitacaoAno'];
		$TipoItemLicitacao    = $_GET['TipoItemLicitacao'];
		$Item                 = $_GET['Item'];
		
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsHistoricoPesquisar.php";

if( $Botao == "Pesquisar" ){
		$Url = "ConsHistoricoResultado.php?Selecao=$Selecao&Objeto=$Objeto&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ComissaoCodigo=$ComissaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&LicitacaoAno=$LicitacaoAno&TipoItemLicitacao=$TipoItemLicitacao&Item=$Item";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		
 
	  
  	header("location: ".$Url);
  	exit();
}elseif( $Botao == "Limpar" ){
		$Url = "ConsHistoricoPesquisar.php?Selecao=$Selecao";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url );
	  exit();
}

if( $Selecao==1 ){
		$Titulo=' Anos Anteriores';
}elseif( $Selecao==2 ){
		$Titulo=' Ano Atual';
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--

window.onload = function(){

limparTextoItem();

}


function enviar(valor){
  	document.Historico.Botao.value=valor;
	document.Historico.submit();
}


function limparTextoItem(){
//	var valorSel = document.getElementById('idTipoItemLicitacao2').value;
//	if ( valorSel=="") {
//		document.getElementById('idItem').value ="";
//	    document.getElementById('idItem').disabled =true;
//	}  
//	else 
//	{
//	    document.getElementById('idItem').disabled =false;
//	}  
}


<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsHistoricoPesquisar.php" method="post" name="Historico">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Histórico > <?php echo $Titulo ?>
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
		    					HISTÓRICO DA LICITAÇÃO -<?php echo strtoupper2($Titulo);?>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para consultar o Histórico das Licitações, selecione o item de pesquisa e  clique no botão "Pesquisar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Objeto" size="45" maxlength="60" value="<?php echo $Objeto;?>" class="textonormal">
												<input type="hidden" name="Selecao" value="<?php echo $Selecao;?>" size="1">
											</td>
	            			</tr>
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
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
		              		<td class="textonormal" bgcolor="#DCEDF7">Comissão </td>
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
													</option>
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
														$sql   .= "FROM SFPC.TBMODALIDADELICITACAO ";
														$sql   .= "ORDER BY AMODLIORDE";
			                  		$result = $db->query($sql);
														if( PEAR::isError($result) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																while( $Linha = $result->fetchRow() ){
																   	if( $Linha[0] == $ModalidadeCodigo ){
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
										
										
										
										
										
										<?php if ($Selecao==1){?>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
											<td class="textonormal">
		  				  	      <select name="LicitacaoAno" class="textonormal">
													<?
											    	$db     = Conexao();
													$sql    = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
													$sql   .= "  FROM SFPC.TBLICITACAOPORTAL ";
													$sql   .= " WHERE TO_CHAR(TLICPODHAB,'YYYY') < '".date('Y')."' ";
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
										<?php }?>

										<tr>
										   <td class="textonormal" bgcolor="#DCEDF7">Item</td>
												<td class="textonormal">
												  <select name="TipoItemLicitacao" class="textonormal" id="idTipoItemLicitacao2" onChange="limparTextoItem();">
													<option value="">Selecione o Item...</option>
													<option value="1" <?php if($TipoItemLicitacao == 1)  {echo 'selected';} ?>>Material</option>
													<option value="2" <?php if($TipoItemLicitacao == 2) {echo 'selected';}?>>Serviço</option>					
												  </select>
												 <input type="text" name="Item" id="idItem" value="<?php echo $Item; ?>" size="50" maxlength="60" class="textonormal">
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
document.Historico.Objeto.focus();
//-->
</script>
