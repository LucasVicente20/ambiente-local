<?php
# ------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirCertidaoComplementar.php
# Autor:    Roberta Costa/Rossana Lira
# Data:     05/08/04
# Objetivo: Programa de Inclusão de Certidões Opcionais
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$_SESSION['Certidao'] = $_POST['Certidao'];
		$ProgramaOrigem	      = $_POST['ProgramaOrigem'];
}else{
		$ProgramaOrigem	= $_GET['ProgramaOrigem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<head>
<title>Portal de Compras - Incluir Certidao Complementar</title>
<script language="javascript" type="">
function enviar(){
		opener.document.<?php echo $ProgramaOrigem; ?>.Certidao.value = document.FornecedorCertidao.Certidao.value;
		opener.document.<?php echo $ProgramaOrigem; ?>.Origem.value = 'B';
		opener.document.<?php echo $ProgramaOrigem; ?>.Destino.value = 'B';
		opener.document.<?php echo $ProgramaOrigem; ?>.submit();
		self.close();
}

function voltar(){
	self.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirCertidaoComplementar.php" method="post" name="FornecedorCertidao">
	<table cellpadding="3" border="0" summary="">
		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					INCLUIR - CERTIDÃO COMPLEMENTAR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
										<p align="justify">
											Para incluir uma certidão fiscal complementar, selecione a certidão desejada e clique no botão "Incluir". Para voltar para a tela anterior clique no botão "Voltar".
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" summary="">
										<tr>
											<td colspan="5">
							          <table class="textonormal" border="0" align="left" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Certidões* </td>
							              <td class="textonormal">
							              	<select name="Certidao" class="textonormal">
							              		<option value="">Selecione uma Certidão...</option>
							              		<?php
							              		$db   = Conexao();
			  												$sql  = "SELECT CTIPCECODI,ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO ";
			  												$sql .= "WHERE FTIPCEOBRI  = 'N' ORDER BY 2";
			  												$res  = $db->query($sql);
															  if( PEAR::isError($res) ){
																	  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		while( $Linha = $res->fetchRow() ){
								          	      			$Descricao = substr($Linha[1],0,75);
								          	      			echo"<option value=\"$Linha[0]\">$Descricao\n";
									                	}
																}
						  	              	$db->disconnect();
							              		?>
							              	</select>
							              </td>
							            </tr>
							          </table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
				      <tr>
				        <td colspan="2">
				   				<table class="textonormal" border="0" align="right" summary="">
				          	<tr>
				            	<td>
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
