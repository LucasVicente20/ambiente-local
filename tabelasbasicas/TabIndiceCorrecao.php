<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIndiceCorrecao.php
# Autor:    João Batista Brito
# Data:     26/10/11
# Objetivo: Programa de Inclusão do ìndice de Correção
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     23/05/2012
# Objetivo: Correção dos erros - Demanda Redmine: #10644#
#-------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica         = $_POST['Critica'];
		$IndCorrDesc = strtoupper2(trim($_POST['IndCorrDesc']));
		$Situacao        = $_POST['Situacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIndiceCorrecao.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $IndCorrDesc == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Indice.IndCorrDesc.focus();\" class=\"titulo2\">Índice</a>";
    }
	  if( $Mens == 0 ) {
	  	    # Verifica a Duplicidade do Indice #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CINCORSEQU) FROM SFPC.TBINDICECORRECAO WHERE RTRIM(LTRIM(EINCORNOME))  = '$IndCorrDesc' ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
						    $Mensagem = "<a href=\"javascript:document.Indice.indCorrDesc.focus();\" class=\"titulo2\"> Índice Já Cadastrado</a>";	
				    }else{
							# Recupera o último Indice e incrementa mais um #
						    $sql    = "SELECT MAX(CINCORSEQU) FROM SFPC.TBINDICECORRECAO";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		$Linha  = $result->fetchRow();
								    $Codigo = $Linha[0] + 1;

								    # Insere Indice #
								    $Data   = date("Y-m-d H:i:s");
								    $sql    = "INSERT INTO SFPC.TBINDICECORRECAO (";
								    $sql   .= "CINCORSEQU,cincorsiti,CUSUPOCODI,EINCORNOME,TINCORULAT ";
								    $sql   .= ") VALUES ( ";
								    $sql   .= "$Codigo,$Situacao,".$_SESSION['_cusupocodi_'].",'$IndCorrDesc','$Data')";
								    $result = $db->query($sql);
										if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Mens            = 1;
												$Tipo            = 1;
												$Mensagem        = "Índice Incluído com Sucesso";
												$IndCorrDesc = "";
										}
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
<?php  MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabIndiceCorrecao.php" method="post" name="Indice">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Índice Correção > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
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
		    					INCLUIR - ÍNDICE CORREÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo Índice Correção, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" class="caixa">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Índice*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="IndCorrDesc" value="<?php  echo $IndCorrDesc; ?>" size="40" maxlength="30" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
		              		<td class="textonormal" >
	  	              		<select name="Situacao" size="1" value="A"  class="textonormal">
	      	            		<option value="1">ATIVO </option>
	    	              		<option value="2">INATIVO</option>
	        	        		</select>
	          	    		</td>
	            			</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
	          	  	<input type="submit" name="Incluir" value="Incluir" class="botao">
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
document.Indice.IndCorrDesc.focus();
//-->
</script>
