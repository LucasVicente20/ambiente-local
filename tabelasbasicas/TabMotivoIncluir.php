<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabMotivoIncluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     27/06/11
# Objetivo: Programa de Inclusão do Motivo
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
		$MotivoNome = strtoupper2(trim($_POST['MotivoNome']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabMotivoIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	if( $MotivoNome == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\">Digite um Motivo</a>";
    }
	
    /*else if (!preg_match("/^[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇºª' ']+$/", $MotivoNome) ){
	    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\">Prencha o campo de Motivo corretamente ex: Portal DGCO</a>";
    }*/
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Motivo #
				$db     = Conexao();
		   	    $sql    = "SELECT COUNT(CMOTNLSEQU) FROM SFPC.TBMOTIVOITEMNAOLOGRADO WHERE RTRIM(LTRIM(EMOTNLNOME)) = '$MotivoNome' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\"> Motivo já cadastrado!</a>";
						}else{
								# Recupera o último Motivo e incrementa mais um #
						    $sql    = "SELECT MAX(CMOTNLSEQU) FROM SFPC.TBMOTIVOITEMNAOLOGRADO";
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
						    $sql    = " INSERT INTO SFPC.TBMOTIVOITEMNAOLOGRADO ( ";
						    $sql   .= " CMOTNLSEQU,EMOTNLNOME,CUSUPOCODI,TMOTNLULAT";
						    $sql   .= " ) VALUES ( ";
						    $sql   .= " $Codigo , '$MotivoNome' , ".$_SESSION['_cusupocodi_']." ,' $Data ' ) ";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Tipo de Motivo Incluído com Sucesso";
										$MotivoNome = "";
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
	document.Motivo.Botao.value=valor;
	document.Motivo.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabMotivoIncluir.php" method="post" name="Motivo">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Motivo > Incluir
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
	            		INCLUIR - MOTIVO  DE ITEM NÃO LOGRADO
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo Motivo, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Motivo*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="MotivoNome" value="<?php  echo $MotivoNome; ?>" size="45" maxlength="60" class="textonormal">
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
document.Motivo.MotivoNome.focus();
//-->
</script>
