<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabTipoLeiIncluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     21/09/11
# Objetivo: Programa de Inclusão do Tipo de Lei
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
		$TipoLeiDescricao = strtoupper2(trim($_POST['TipoLeiDescricao']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabTipoLeiIncluir.php";

# Critica dos Campos #
if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
	if( $TipoLeiDescricao == ""){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\">Tipo de Lei</a>";
    }
	/*else if (!preg_match("/^[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇºª' ']+$/", $TipoLeiDescricao) ){
	    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\">Prencha o campo Tipo de Lei corretamente ex: Municipal</a>";
    }
	*/
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Tipo de Lei #
				$db     = Conexao();
		   	    $sql    = "SELECT COUNT(CTPLEITIPO) FROM SFPC.TBTIPOLEIPORTAL WHERE RTRIM(LTRIM(ETPLEITIPO)) = '$TipoLeiDescricao' ";
		 		$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.TipoLei.TipoLeiDescricao.focus();\" class=\"titulo2\"> Tipo de Lei Já Cadastrado</a>";
						}else{
								# Recupera a última Ocorrencia e incrementa mais um #
						    $sql    = "SELECT MAX(CTPLEITIPO) FROM SFPC.TBTIPOLEIPORTAL";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		while( $Linha = $result->fetchRow() ){
								    		$Codigo = $Linha[0] + 1;
								    }
								}

						    # Insere Tipo de Lei#
						    $Data   = date("Y-m-d H:i:s");
						    $db->query("BEGIN TRANSACTION");
						    $sql    = "INSERT INTO SFPC.TBTIPOLEIPORTAL (";
						    $sql   .= "CTPLEITIPO,ETPLEITIPO,CUSUPOCODI,TTPLEIULAT";
						    $sql   .= ") VALUES ( ";
						    $sql   .= "$Codigo, '$TipoLeiDescricao', ".$_SESSION['_cusupocodi_'].", '$Data' )";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Tipo de Lei Incluído com Sucesso";
										$TipoLeiDescricao = "";
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
	document.TipoLei.Botao.value=valor;
	document.TipoLei.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTipoLeiIncluir.php" method="post" name="TipoLei">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Tipo de Lei > Incluir
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
	            		INCLUIR - TIPO DE LEI
	            	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo Tipo de Lei, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Tipo de Lei*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="TipoLeiDescricao" value="<?php echo $TipoLeiDescricao; ?>" size="45" maxlength="60" class="textonormal">
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
document.TipoLei.TipoLeiDescricao.focus();
//-->
</script>
