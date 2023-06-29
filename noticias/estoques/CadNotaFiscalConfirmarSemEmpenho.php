<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNotaFiscalConfirmarSemEmpenho.php
# Autor:    Álvaro Faria
# Data:     21/10/2005
# Objetivo: Programa de Confirmação de Inclusão ou Alteração de Nota Fiscal sem número de Empenho
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao          = $_POST['Botao'];
		$ProgramaOrigem = $_POST['ProgramaOrigem'];
		$BotaoEnviar    = $_POST['BotaoEnviar'];
} else {
		$ProgramaOrigem = $_GET['ProgramaOrigem'];
		$BotaoEnviar    = $_GET['BotaoEnviar'];
}

$DataAtual = date("Y-m-d");

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Sim" ){
	  echo "<script>opener.document.".$ProgramaOrigem.".SemEmpenhoLiberado.value=1</script>";
	  echo "<script>opener.document.".$ProgramaOrigem.".Botao.value='".$BotaoEnviar."'</script>";
		echo "<script>opener.document.".$ProgramaOrigem.".submit()</script>";
		echo "<script>self.close();</script>";
    }

if( $Botao == "Nao" ){
		echo "<script>self.close();</script>";
    }
?>

<html>
<head>
<title>Portal de Compras - Confirmação de cadastro de nota fiscal sem número de empenho</title>
<script language="javascript" type="">
function enviar(valor){
	document.CadNotaFiscalConfirmarSemEmpenho.Botao.value = valor;
	document.CadNotaFiscalConfirmarSemEmpenho.submit();
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<script language="javascript" src="../janela.js" type="text/javascript"></script>
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadNotaFiscalConfirmarSemEmpenho.php" method="post" name="CadNotaFiscalConfirmarSemEmpenho">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
		  <td align="left" colspan="2">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,$Virgula);	}?>
		 	</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="" width="100%">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					CONFIRMAÇÃO DE CADASTRO DE NOTA FISCAL SEM NÚMERO DE EMPENHO
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
										<p align="justify">
											Se o número do empenho não for informado, uma comunicação será enviada
											para o corpo gerencial da Prefeitura do Recife, informando o ocorrido.
											Deseja confirmar esta operação?
		          	   	</p>
		          		</td>
			        	</tr>
		          		<tr>
			            	<td colspan="2" align="right">
					   	      	<input type="button" name="Sim" value="Sim" class="botao" onClick="javascript:enviar('Sim');">
						       		<input type="button" value="Nao" class="botao" onclick="javascript:self.close();">
											<input type="hidden" name="Botao" value="">
											<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem;?>">
											<input type="hidden" name="BotaoEnviar" value="<?php echo $BotaoEnviar;?>">
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
</script>
</body>
</html>
