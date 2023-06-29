<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInscritoFornecedorCertidao.php
# Autor:    Roberta Costa
# Data:     18/06/04
# Objetivo: Programa de Inclusão de Certidões no Pré-Cadastro de Fornecedores
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$_SESSION['Obrigatorio']   = $_POST['Obrigatorio'];
		$_SESSION['Certidao']   	 = $_POST['Certidao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$_SESSION['Mens'] = "";
?>
<html>
<script language="javascript" type="">
function enviar(){
		opener.document.CadInscritoIncluir.Obrigatorio.value = 'N';
		opener.document.CadInscritoIncluir.Certidao.value = document.FornecedorCertidao.Certidao.value;
		opener.document.CadInscritoIncluir.Origem.value = 'B';
		opener.document.CadInscritoIncluir.Destino.value = 'B';
		opener.document.CadInscritoIncluir.submit();
		self.close();
}

function voltar(){
	opener.document.CadInscritoIncluir.Origem.value = 'B';
	opener.document.CadInscritoIncluir.Destino.value = 'B';
	opener.document.CadInscritoIncluir.submit();
	self.close();
}
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadInscritoFornecedorCertidao.php" method="post" name="FornecedorCertidao">
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
			    					INCLUIR - CERTIDÃO NA INSCRIÇÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" >
										<p align="justify">
											Para incluir uma certidão fiscal opcional, selecione a certidão desejada e clique no botão "Incluir". Para voltar para a tela anterior clique no botão "Voltar".
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
							              <td class="textonormal" bgcolor="#bfdaf2">Certidões* </td>
							              <td class="textonormal">
							              	<select name="Certidao" class="textonormal">
							              		<option value="">Selecione uma Certidão...</option>
							              		<?php
							              		$db   = Conexao();
			  												$sql  = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO ";
			  												$sql .= "WHERE FTIPCEOBRI  = 'N' ORDER BY ETIPCEDESC";
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
				            	<td><input type="button" value="Incluir" class="botao" onclick="javascript:enviar();"></td>
				            	<td><input type="button" value="Voltar" class="botao" onclick="javascript:voltar();"></td>
											<td><input type="hidden" name="Botao" value=""></td>
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
