 <?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioCPF.php
# Autor:    Everton Lino
# Data:     22/04/2010
# Objetivo: Programa de Inclusão(Alteração) de CPF de Usuário
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica       = $_POST['Critica'];
		$CPF           = $_POST['CPF'];

}
/*
else{
		$UsuarioCodigo = $_GET['UsuarioCodigo'];
		$Mens          = $_GET['Mens'];
		$Tipo          = $_GET['Tipo'];
		$Mensagem      = urldecode($_GET['Mensagem']);
}
*/
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioCPF.php";

# Critica dos Campos #

 $Mens = 0;

if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
		  if( $CPF === "" )
		  {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF</a>";
      }else{
    	# Chama a função para validar CPF #
						 if( !valida_CPF($CPF))
						 {
							$Mens      = 1;
		          $Tipo      = 2;
  		       	$Mensagem .= "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Válido</a>";
		  			 }
     }

						if( $Mens == 0 )
						{
	  				# Verifica a Duplicidade de CPF #
						$db     = Conexao();
		  		 	$sql    = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL WHERE AUSUPOCCPF = '$CPF' ";
		 				$result = $db->query($sql);
						if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
		    		$Linha = $result->fetchRow();
				    $Qtd   = $Linha[0];
		    		if( $Qtd > 0 ){
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Já Cadastrado</a>";
						}else{

								# Atualiza CPF Usuário #
					   		$Data   = date("Y-m-d H:i:s");
								$sql    = "UPDATE SFPC.TBUSUARIOPORTAL ";
								$sql   .= "   SET AUSUPOCCPF = '$CPF' , ";
								$sql   .= "   TUSUPOULAT = '$Data' ";
								$sql   .= " WHERE CUSUPOCODI = '".$_SESSION['_cusupocodi_']."' ";
								$result = $db->query($sql);
								if( PEAR::isError($result) )
								{
									$RowBack = 1;
									$db->query("ROLLBACK");
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
 								# Caso não tenha ocorrido nenhum erro, redireciona página selecionar com mensagem de sucesso #

								if(!$RowBack)
								{
									$db->query("COMMIT");
									$db->query("END TRANSACTION");
									$db->disconnect();

								# Envia mensagem para página selecionar #
								AddMenuAcesso( 'index.php' );
								$redirecionar = "../index.php";
								header("Location: $redirecionar");
								exit();

								//	$Mensagem = urlencode("CPF Adicionado com Sucesso");
								//	$redirecionar = "../index.php";
								//	header("Location: $redirecionar");
								// exit();
								//	$Url = "../index.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
								//	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								//	header("location: ".$Url);
								// exit();
								}
					    }
		    	}
		}
		$db->disconnect();
	}
}

?>
<html>
<?
# Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
<!--
<?php /*MenuAcesso(); */ ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUsuarioCPF.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
		  <font class="titulo2">|</font>
		  <font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Alterar CPF
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
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - CPF DO USUÁRIO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		<b>Foi verificado no sistema que seu CPF não está cadastrado. Informe CPF para prosseguir.</b><br><br>
	        	    		Para incluir um novo CPF, informe o dado abaixo e clique no botão "Adicionar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	              	<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF* </td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="CPF" value="<?php echo $CPF; ?>" size="11" maxlength="11" class="textonormal">
	            	  		</td>
	            		</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	  			<td class="textonormal" align="right">
	    	  			  <input type="submit" name="Alterar" value="Adicionar" class="botao">
	    	  			  <input type="hidden" name="Critica" value="1">
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
document.Usuario.CPF.focus();
//-->
</script>




