<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotVerificaEmail.php
# Autor:    Roberta Costa
# Data:     15/02/05
# Objetivo: Programa que Verifica se o Email foi informado
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Critica  			= $_POST['Critica'];
		$Remeter  			= $_POST['Remeter'];
		$Email    			= $_POST['Email'];
		$ProgramaOrigem = $_POST['ProgramaOrigem'];
}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Email == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.VerificaEmail.Email.focus();\" class=\"titulo2\">Email ou clique no botão \"Não\" para deixar o e-mail vazio</a>";
		}else{
	 			if( ! strchr($Email, "@") ){
	  				if( $Mens == 1 ){ $Mensagem.=", "; }
	  				$Mens      = 1;
	  				$Tipo      = 2;
			 			$Mensagem .= "<a href=\"javascript:document.VerificaEmail.Email.focus();\" class=\"titulo2\">E-mail Válido</a>";
				}
		}
		if( $Mens == 0 ){ $Remeter = "S"; }
}
?>
<html>
<head>
<title>Portal de Compras - Verifica Email</title>
<script language="javascript" type="">
function enviar(){
	document.VerificaEmail.Critica.value = 1;
	document.VerificaEmail.submit();
}
function voltar(){
	opener.document.<?php echo $ProgramaOrigem; ?>.EmailPopup.value = 'NULL';
	opener.document.<?php echo $ProgramaOrigem; ?>.Origem.value  = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.Destino.value = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="RotVerificaEmail.php" method="post" name="VerificaEmail">
	<table cellpadding="0" border="0" summary="">
		<?php if( $Critica == 0 ){ echo "<br>"; }?>
		<!-- Erro -->
		<tr>
		  <td align="left" colspan="2">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1);	}?>
		 	</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					INCLUIR - VERIFICA EMAIL
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
										<p align="justify">
											Informe um Email, se possuir, para participar das facilidades do Portal de Compras e clique no botão "Sim". Caso contrário, clique no botão "Não".
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" summary="">
										<tr>
											<td colspan="2">
							          <table class="textonormal" border="0" align="left" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">E-mail &nbsp;</td>
														<td class="textonormal">
															<input type="text" name="Email" size="45" maxlength="60" value="<?php echo $Email;?>" class="textonormal">
														</td>
							            </tr>
							          </table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
	          	<tr>
		            <td colspan="2" align="right">
									<input type="hidden" name="Critica" value="<?php echo $Critica; ?>">
		     					<input type="hidden" name="Remeter" value="<?php echo $Remeter; ?>">
					       	<input type="button" value="Sim" class="botao" onclick="javascript:enviar();">
		            	<input type="button" value="Não" class="botao" onclick="javascript:voltar();">
									<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
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
<script language="javascript" type="">
window.focus();
document.VerificaEmail.Email.focus();
<?php if( $Remeter == "S" ){ ?>
	opener.document.<?php echo $ProgramaOrigem; ?>.EmailPopup.value = document.VerificaEmail.Email.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.Origem.value  = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.Destino.value = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
<?php } ?>
//-->
</script>
</body>
</html>
