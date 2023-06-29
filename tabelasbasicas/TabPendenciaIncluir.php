<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPendenciaIncluir.php
# Autor:    Roberta Costa
# Data:     27/12/04
# Objetivo: Programa de Inclusão de Pendência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao              = $_POST['Botao'];
		$PendenciaDescricao = strtoupper2(trim($_POST['PendenciaDescricao']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPendenciaIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $PendenciaDescricao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Pendencia.PendenciaDescricao.focus();\" class=\"titulo2\">Pendência</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Pendencia #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CTIPPECODI) FROM SFPC.TBTIPOPENDENCIA WHERE RTRIM(LTRIM(ETIPPEDESC)) = '$PendenciaDescricao' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Pendencia.PendenciaDescricao.focus();\" class=\"titulo2\"> Pendência Já Cadastrada</a>";
						}else{
								# Recupera a última Pendencia e incrementa mais um #
						    $sql    = "SELECT MAX(CTIPPECODI) FROM SFPC.TBTIPOPENDENCIA";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
								    }
								}

						    # Insere Pendencia #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBTIPOPENDENCIA (";
						    $sql   .= "CTIPPECODI, ETIPPEDESC, TTIPPEULAT";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$Codigo, '$PendenciaDescricao', '$Data')";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Pendência Incluída com Sucesso";
										$PendenciaDescricao = "";
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
	document.Pendencia.Botao.value=valor;
	document.Pendencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPendenciaIncluir.php" method="post" name="Pendencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Pendência > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
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
	            		INCLUIR - PENDÊNCIA
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova Pendência, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Pendência*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="PendenciaDescricao" value="<?php echo $PendenciaDescricao; ?>" size="70" maxlength="100" class="textonormal">
	          	    		</td>
	            			</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	  			<tD class="textonormal" align="right">
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
	<!-- Fim do Corpo -->
</table>
</form>
<script language="JavaScript">
<!--
document.Pendencia.PendenciaDescricao.focus();
//-->
</script>
</body>
</html>
