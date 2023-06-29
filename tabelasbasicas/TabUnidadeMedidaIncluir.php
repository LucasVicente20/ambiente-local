<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeMedidaIncluir.php
# Autor:    Roberta Costa
# Data:     31/05/05
# Objetivo: Programa de Inclusão de Unidade de Medida
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao     = $_POST['Botao'];
		$Sigla		 = strtoupper2(trim($_POST['Sigla']));
		$Descricao = strtoupper2(trim($_POST['Descricao']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $Sigla == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.UnidadeMedida.Sigla.focus();\" class=\"titulo2\">Sigla</a>";
    }
	  if( $Descricao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.UnidadeMedida.Descricao.focus();\" class=\"titulo2\">Unidade de Medida</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de UnidadeMedida #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CUNIDMCODI) FROM SFPC.TBUNIDADEDEMEDIDA ";
		   	$sql   .= "WHERE RTRIM(LTRIM(EUNIDMSIGL)) = '$Sigla' OR RTRIM(LTRIM(EUNIDMDESC)) = '$Descricao'";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.UnidadeMedida.Descricao.focus();\" class=\"titulo2\">Sigla/Descrição da Unidade de Medida Já Cadastrada</a>";
						}else{
								# Recupera a última UnidadeMedida e incrementa mais um #
						    $sql    = "SELECT MAX(CUNIDMCODI) FROM SFPC.TBUNIDADEDEMEDIDA";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
								    }
								}

						    # Insere UnidadeMedida #
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBUNIDADEDEMEDIDA (";
						    $sql   .= "CUNIDMCODI, EUNIDMSIGL, EUNIDMDESC, TUNIDMULAT";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$Codigo, '$Sigla','$Descricao', '$Data')";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens      = 1;
										$Tipo      = 1;
										$Mensagem  = "Unidade de Medida Incluída com Sucesso";
										$Sigla     = "";
										$Descricao = "";
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
	document.UnidadeMedida.Botao.value=valor;
	document.UnidadeMedida.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUnidadeMedidaIncluir.php" method="post" name="UnidadeMedida">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Unidade de Medida > Incluir
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
	            		INCLUIR - UNIDADE DE MEDIDA
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir uma nova unidade de medida, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Sigla*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Sigla" value="<?php  echo $Sigla; ?>" size="4" maxlength="4" class="textonormal">
	          	    		</td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Descricao" value="<?php  echo $Descricao; ?>" size="40" maxlength="40" class="textonormal">
	          	    		</td>
	            			</tr>
	            		</table>
		          	</td>
		        	</tr>
			      	<tr>
				      	<td align="right">
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
</body>
</html>
<script language="JavaScript">
<!--
document.UnidadeMedida.Sigla.focus();
//-->
</script>
