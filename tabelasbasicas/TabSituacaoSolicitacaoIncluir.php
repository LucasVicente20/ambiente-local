<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabSituacaoSolicitacaoIncluir.php
# Autor:    Luiz Alves
# Data:     10/06/11
# Objetivo: Programa de Inclusão de Situação Solicitação - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:
#


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$SituacaoDescricao = strtoupper2(trim($_POST['SituacaoDescricao']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabSituacaoSolicitacaoIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	if( $SituacaoDescricao == ""){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabSituacaoSolicitacaoIncluir.SituacaoDescricao.focus();\" class=\"titulo2\">Situação Solicitação</a>";
	}
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Situacao Solicitação #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CSITSOCODI) FROM SFPC.TBSITUACAOSOLICITACAO WHERE RTRIM(LTRIM(ESITSONOME)) = '$SituacaoDescricao' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.TabSituacaoSolicitacaoIncluir.SituacaoDescricao.focus();\" class=\"titulo2\"> Situação Solicitação Já Cadastrada</a>";
						}else{
								# Recupera a última Situacao Solicitação e incrementa mais um #
						    $sql    = "SELECT MAX(CSITSOCODI) FROM SFPC.TBSITUACAOSOLICITACAO";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
								    }
								}

						    # Insere uma Situacao Solicitação #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBSITUACAOSOLICITACAO (";
						    $sql   .= "CSITSOCODI, ESITSONOME, TSITSOULAT, CUSUPOCODI";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$Codigo, '$SituacaoDescricao', '$Data', ".$_SESSION['_cusupocodi_'].")";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Situação Solicitação Incluída com Sucesso";
										$SituacaoDescricao = "";
								}
						}
				}
		    $db->disconnect();
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
	document.TabSituacaoSolicitacaoIncluir.Botao.value=valor;
	document.TabSituacaoSolicitacaoIncluir.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabSituacaoSolicitacaoIncluir.php" method="post" name="TabSituacaoSolicitacaoIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Situação Solicitação > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php  if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	            		INCLUIR - SITUAÇÃO SOLICITAÇÃO
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Situacao Solicitação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Situação Solicitação*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="SituacaoDescricao" value="<?php  echo $SituacaoDescricao; ?>" size="45" maxlength="60" class="textonormal">
	          	    		</td>
	            			</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td>
	   	  	  			<table class="textonormal" border="0" align="right" summary="">
	        	      	<tr>
	          	      	<td>
	          	      		<input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
	     									<input type="hidden" name="Botao" value="">
	          	      	</td>
	            	  	</tr>
	            		</table>
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
<script language="JavaScript">
<!--
document.TabSituacaoSolicitacaoIncluir.SituacaoDescricao.focus();
//-->
</script>
