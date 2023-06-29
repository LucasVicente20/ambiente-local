<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirAutorizacao.php
# Autor:    Roberta Costa
# Data:     01/02/05
# Objetivo: Programa de Inclusão de Etidade Profissional Competente
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
		$Botao    			     = $_POST['Botao'];
		$Critica  		       = $_POST['Critica'];
		$Autoriza  		       = $_POST['Autoriza'];
		$Nome 			         = strtoupper2($_POST['Nome']);
		$RegistroAutorizacao = $_POST['RegistroAutorizacao'];
		$DataAutorizacao     = $_POST['DataAutorizacao'];
		$ProgramaOrigem      = $_POST['ProgramaOrigem'];
}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Nome == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.Nome.focus();\" class=\"titulo2\">Nome</a>";
		}
		if( $RegistroAutorizacao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.RegistroAutorizacao.focus();\" class=\"titulo2\">Registro ou Inscrição</a>";
		}else{
				if( ! SoNumeros($RegistroAutorizacao) ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.RegistroAutorizacao.focus();\" class=\"titulo2\">Registro ou Inscrição Válida</a>";
				}
		}
		if( $DataAutorizacao == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.DataAutorizacao.focus();\" class=\"titulo2\">Data de Vigência</a>";
		}else{
				$MensErro = ValidaData($DataAutorizacao);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
					  $Mens      = 1;
					  $Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadIncluirAutorizacao.DataAutorizacao.focus();\" class=\"titulo2\">Data de Vigência Válida</a>";
				}
		}
		if( $Mens == 0 ){ $Autoriza = "S"; }else{ $Autoriza = ""; }
}
?>
<html>
<head>
<title>Portal de Compras - Incluir Autorização Específica</title>
<script language="javascript" type="">
function enviar(){
	document.CadIncluirAutorizacao.Critica.value = 1;
	document.CadIncluirAutorizacao.submit();
}
function voltar(){
	self.close();
}
//-->
</script>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirAutorizacao.php" method="post" name="CadIncluirAutorizacao">
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
			    					INCLUIR - AUTORIZAÇÃO ESPECÍFICA
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" >
										<p align="justify">
											Para incluir uma Autorização Específica preencha os campos abaixo e clique no botão "Incluir". Os campos obrigatórios estão com *. Para voltar para a tela anterior clique no botão "Voltar".
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
							              <td class="textonormal" bgcolor="#DCEDF7">Nome da Entidade Emissora*</td>
							              <td class="textonormal">
											      	<input type="text" name="Nome" size="25" maxlength="18" value="<?php echo $Nome;?>" class="textonormal">
							              </td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Registro ou Inscrição*</td>
							              <td class="textonormal">
							              	<input type="text" name="RegistroAutorizacao" size="10" maxlength="10" value="<?php echo $RegistroAutorizacao;?>" class="textonormal">
							              </td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Data de Vigência*</td>
							              <td class="textonormal">
				              				<?php $URL = "../calendario.php?Formulario=CadIncluirAutorizacao&Campo=DataAutorizacao" ?>
									          	<input type="text" name="DataAutorizacao" size="10" maxlength="10" value="<?php echo $DataAutorizacao; ?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
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
		     					<input type="hidden" name="Autoriza" value="<?php echo $Autoriza; ?>">
		     					<input type="hidden" name="Critica" value="<?php echo $Critica; ?>">
					       	<input type="button" value="Incluir" class="botao" onclick="javascript:enviar();">
		            	<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
									<input type="hidden" name="Botao" value="">
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
document.CadIncluirAutorizacao.Nome.focus();
<?php if( $Autoriza == "S" ){ ?>
	opener.document.<?php echo $ProgramaOrigem; ?>.AutorizaNome.value     = document.CadIncluirAutorizacao.Nome.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.AutorizaRegistro.value = document.CadIncluirAutorizacao.RegistroAutorizacao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.AutorizaData.value     = document.CadIncluirAutorizacao.DataAutorizacao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.Origem.value  = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.Destino.value = 'D';
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
<?php } ?>
//-->
</script>
</body>
</html>
