<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOcorrenciaIncluir.php
# Autor:    Roberta Costa
# Data:     29/09/04
# Objetivo: Programa de Inclusão de Ocorrência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$OcorrenciaDescricao = strtoupper2(trim($_POST['OcorrenciaDescricao']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOcorrenciaIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $OcorrenciaDescricao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Ocorrencia.OcorrenciaDescricao.focus();\" class=\"titulo2\">Ocorrência</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Ocorrencia #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CFORTOCODI) FROM SFPC.TBFORNTIPOOCORRENCIA WHERE RTRIM(LTRIM(EFORTODESC)) = '$OcorrenciaDescricao' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Ocorrencia.OcorrenciaDescricao.focus();\" class=\"titulo2\"> Ocorrência Já Cadastrada</a>";
						}else{
								# Recupera a última Ocorrencia e incrementa mais um #
						    $sql    = "SELECT MAX(CFORTOCODI) FROM SFPC.TBFORNTIPOOCORRENCIA";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
								    }
								}

						    # Insere Ocorrencia #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBFORNTIPOOCORRENCIA (";
						    $sql   .= "CFORTOCODI, EFORTODESC, TFORTOULAT";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$Codigo, '$OcorrenciaDescricao', '$Data')";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Ocorrência Incluída com Sucesso";
										$OcorrenciaDescricao = "";
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
	document.Ocorrencia.Botao.value=valor;
	document.Ocorrencia.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabOcorrenciaIncluir.php" method="post" name="Ocorrencia">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Ocorrência > Incluir
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
	            		INCLUIR - OCORRÊNCIA
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova ocorrência, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Ocorrência*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="OcorrenciaDescricao" value="<?php  echo $OcorrenciaDescricao; ?>" size="45" maxlength="60" class="textonormal">
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
document.Ocorrencia.OcorrenciaDescricao.focus();
//-->
</script>
