<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInscritoFornecedorRecibo.php
# Autor:    Roberta Costa
# Data:     22/06/04
# Objetivo: Programa que mostra o Recibo de Inscrição de Fornecedores
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Critica           = $_POST['Critica'];
		$Origem	           = $_POST['Origem'];
		$Destino           = $_POST['Destino'];
		$_SESSION['Botao'] = $_POST['Botao'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<script language="javascript" type="">
function enviar(){
	self.print();
}
function voltar(){
	<?
	$Url = "CadInscritoIncluir.php?Destino=D";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	?>
	opener.location.href = '<?=$Url;?>';
	self.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadInscritoFornecedorRecibo.php" method="post" name="Recibo">
	<table cellpadding="3" border="0" summary="">
		<!-- Erro -->
		<tr>
		  <td align="left" colspan="2">
				<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$Tipo,$Virgula);	}?>
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
			    					INCLUIR - RECIBO DE INSCRIÇÃO DE FORNECEDOR
			          	</td>
			        	</tr>
			        	<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" summary="">
										<tr>
											<td colspan="5">
							          <table class="textonormal" border="0" align="left" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2">Código da Inscrição</td>
							              <td class="textonormal"><?php echo $_SESSION['InscricaoCodigo'];?></td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2">CPF/CNPJ</td>
							              <td class="textonormal"><?php echo $_SESSION['CPF_CNPJ'];?></td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2" >Razão Social </td>
							              <td class="textonormal"><?php echo $_SESSION['RazaoSocial'];?></td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2">Endereço</td>
							              <td class="textonormal">
							              	<?php echo $_SESSION['Logradouro'].",  ".$_SESSION['Numero']." - ".$_SESSION['Bairro'].", ".$_SESSION['Cidade']."/".$_SESSION['UF']." CEP:".$_SESSION['CEP'];?>
							              </td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2">Senha Próvisória</td>
							              <td class="textonormal"><?php echo $_SESSION['Senha'];?></td>
							            </tr>
							            <tr>
							              <td class="textonormal" bgcolor="#bfdaf2">Data da Inscrição</td>
							              <td class="textonormal"><?php echo $_SESSION['InscricaoData'];?></td>
							            </tr>
							          </table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
				      <tr>
				        <td colspan="4">
				   				<table class="textonormal" border="0" align="right" summary="">
				          	<tr>
				            	<td>
				            		<input type="button" value="Imprimir" class="botao" onclick="javascript:enviar();">
					            	<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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
<script language="javascript" type="">
window.focus();
//-->
</script>
</body>
</html>
