<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotFaleConosco.php
# Autor:    Rossana Lira
# Data:     15/05/03
# Objetivo: Programa para Envio de E-mail do Fale Conosco
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica   = $_POST['Critica'];
		$Nome      = strtoupper2(trim($_POST['Nome']));
		$Email     = trim($_POST['Email']);
		$MensEmail = strtoupper2(trim($_POST['MensEmail']));
		$Para      = $_POST['Para'];
}else{
		$Para      = $_GET['Para'];
}

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Nome == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.FaleConosco.Nome.focus();\" class=\"titulo2\">Nome</a>";
	  }
	  if( $Email == "" ){
				if( $Mens == 1 ){ $Mensagem.=", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.FaleConosco.Email.focus();\" class=\"titulo2\">E-mail</a>";
	  }elseif(! strchr($Email, "@")){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens      = 1;
		    $Tipo      = 2;
    		$Mensagem .= "<a href=\"javascript:document.FaleConosco.Email.focus();\" class=\"titulo2\">E-mail Inválido</a>";
		}
	  if( $MensEmail == "" ){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens      = 1;
		    $Tipo      = 2;
    		$Mensagem .= "<a href=\"javascript:document.FaleConosco.MensEmail.focus();\" class=\"titulo2\">Mensagem</a>";
		}
    if( $Mens == 0){
				$Para = "portalcompras@recife.pe.gov.br";
				$From = $Email;
				if( EnviaEmail($Para,"Mensagem Enviada do Portal da Gerência Geral de Licitações e Compras - GGLIC","Nome: ".$Nome."\nE-mail: ".$Email."\n\nMensagem:\n".$MensEmail,"from: $From") ){
						$Mensagem ="Mensagem Enviada com Sucesso";
				}else{
						$Mensagem ="Erro no envio. Tente novamente mais tarde";
				}
				$Mens      = 1;
				$Tipo      = 1;
				$Nome      = "";
	    	$Email     = "";
				$MensEmail = "";
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
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotFaleConosco.php" method="post" name="FaleConosco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fale Conosco > Enviar e-mail
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,$Virgula); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					FALE CONOSCO - ENVIAR E-MAIL
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para enviar um e-mail para a Diretoria Geral de Compras da Prefeitura do Recife, preencha os dados abaixo e clique no botão "Enviar".
	          	   	</p>
	          		</td>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Nome*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Nome" size="45" maxlength="60" value="<?php echo $Nome;?>" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="Email" size="45" maxlength="60" value="<?php echo $Email;?>" class="textonormal">
	          	    		</td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Mensagem*</td>
	        	      		<td class="textonormal">
	        	      			<textarea name="MensEmail" cols="44" rows="5" class="textonormal"><?php echo $MensEmail ?></textarea>
	        	      		</td>
	            			</tr>
	            		</table>
	        	    </td>
		        	</tr>
		        	<tr>
	   	  	  		<td class="textonormal" align="right">
	          	  	<input type="submit" name="Enviar" value="Enviar" class="botao">
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
<!--
document.FaleConosco.Nome.focus();
//-->
</script>
</body>
</html>
