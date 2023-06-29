<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotAlteracaoSenha.php
# Autor:    Rossana Lira
# Data:     09/04/03
# Objetivo: Programa de Alteração de Senha do Usuário
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
		$SenhaAtual    = $_POST['SenhaAtual'];
		$NovaSenha     = $_POST['NovaSenha'];
		$ConfirmaSenha = $_POST['ConfirmaSenha'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RotAlteracaoSenha.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SenhaAtual == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Gerencia.SenhaAtual.focus();\" class=\"titulo2\">Senha atual</a>";
		}
		if( $NovaSenha == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Gerencia.NovaSenha.focus();\" class=\"titulo2\">Nova senha</a>";
		}
		if( $ConfirmaSenha == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Gerencia.ConfirmaSenha.focus();\" class=\"titulo2\">Confirmação da senha</a>";
		}
		if( $Mens == 0 ){
				if( $NovaSenha != $ConfirmaSenha ){
						$Mens          = 1;
						$Tipo          = 2;
						$Mensagem      = "Confirmação de Senha Inválida";
						$SenhaAtual    = "";
						$NovaSenha     = "";
						$ConfirmaSenha = "";
				}
		}
		if( $Mens == 0 ) {
				$SenhaAtualCript = hash('sha512',$SenhaAtual);
				$_cusupocodi_    = $_SESSION['_cusupocodi_'];
				$db     = Conexao();
				$sql    = "SELECT EUSUPOSEN2 FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $_cusupocodi_";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						while( $Linha = $result->fetchRow() ){
								$SenAtu = $Linha[0];
						}
						$db->disconnect();
						if( $SenAtu != $SenhaAtualCript ){
								$Mens          = 1;
								$Tipo          = 2;
								$Mensagem     .= "<a href=\"javascript:document.Gerencia.SenhaAtual.focus();\" class=\"titulo2\">Senha Atual Válida</a>";
								$SenhaAtual    = "";
								$NovaSenha     = "";
								$ConfirmaSenha = "";
						}
				}
		}
		if( $Mens == 0 ){
				$SenhaCript = hash('sha512', $NovaSenha);
				$_cusupocodi_ = $_SESSION['_cusupocodi_'];
				$Data   = date("Y-m-d H:i:s");
				$db     = Conexao();
				$sql    = "UPDATE SFPC.TBUSUARIOPORTAL SET EUSUPOSEN2 = '$SenhaCript', ";
				$sql   .= "TUSUPOULAT = '$Data' WHERE CUSUPOCODI = ". $_SESSION['_cusupocodi_'];
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
				$db->disconnect();

				$SenhaAtual    = "";
				$NovaSenha     = "";
				$ConfirmaSenha = "";
				$Mens          = 1;
				$Tipo          = 1;
				$Mensagem      = "Senha Alterada com Sucesso";
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
</script><link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<br><br><br><br><br>
<form name="Gerencia" action="RotAlteracaoSenha.php" method="post">
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Controles > Alteração de Senha
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
  	<td width="150"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					ALTERAR SENHA DO USUÁRIO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para Alterar a senha do usuário, informe os dados abaixo e clique no botão "Alterar". Para Limpar a tela clique no botão "Limpar".
	        	    		Os campos obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário </td>
	          	    		<td class="textonormal">
	          	    			<?php echo $_SESSION['_eusupologi_']; ?>
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Senha* </td>
	          	    		<td class="textonormal">
	          	    			<input type="password" name="SenhaAtual" value="<?php echo $SenhaAtual; ?>" size="8" maxlength="8" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Nova Senha* </td>
	          	    		<td class="textonormal">
	          	    			<input type="password" name="NovaSenha" value="<?php echo $NovaSenha; ?>" size="8" maxlength="8" class="textonormal">
	          	    		</td>
	            	  	</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Confirmação da Senha* </td>
	          	    		<td class="textonormal">
	          	    			<input type="password" name="ConfirmaSenha" value="<?php echo $ConfirmaSenha; ?>" size="8" maxlength="8" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
		          	  <input type="submit" name="Alterar" value="Alterar" class="botao">
									<input type="reset" value="Limpar" class="botao">
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
document.Gerencia.SenhaAtual.focus();
//-->
</script>
</body>
</html>
