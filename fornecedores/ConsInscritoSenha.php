<?php
#---------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsInscritoSenha.php
# Autor:    Roberta Costa
# Data:     25/01/05
# Objetivo: Programa que faz o login do Fornecedor Inscrito para Consulta
# Alterado: Rossana Lira
# Data:     22/05/2007-Retirada de $_SESSION['GetUrl']=array(), pois já está em funcoes.php;
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsInscritoSenha.php');
AddMenuAcesso( '/fornecedores/RotAlteracaoSenhaFornecedor.php');
AddMenuAcesso( '/fornecedores/ConsInscrito.php');

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao         = $_POST['Botao'];
		$CPF_CNPJ	     = trim($_POST['CPF_CNPJ']);
		$Senha 		     = $_POST['Senha'];
		$Codigo		     = $_POST['Codigo'];
		$TipoCnpjCpf   = $_POST['TipoCnpjCpf'];
}else{
		$Programa	= $_GET['Programa'];
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: ConsInscritoSenha.php");
	  exit();
}elseif( $Botao == "Confirmar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $TipoCnpjCpf == "CPF" ){
		  	$Qtd = strlen($CPF_CNPJ);
		  	if( $Qtd != 11 and $Qtd > 0 ){
		      	if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
				}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
				}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CPF($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
	  				}
		  	}
		}elseif( $TipoCnpjCpf == "CNPJ" ){
				$Qtd = strlen($CPF_CNPJ);
		   	if( ($Qtd != 14) and ($Qtd != 0)  ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
			 	}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
			 	}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CNPJ($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
	  				}
			 	}
		}
		if( $cpfcnpj === true ){
				# Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
				$Senha = trim($Senha);
				if( $Senha == ""  ){
						if( $Mens == 1){$Mensagem.=", ";}
			  		$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.Senha.focus();\" class=\"titulo2\">Senha</a>";
				}
				if( $Codigo == "" ){
						if( $Mens == 1){$Mensagem.=", ";}
			  		$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.Codigo.focus();\" class=\"titulo2\">Código</a>";
				}else{
						if( strtoupper2($Codigo) != $_SESSION['_Combinacao_'] ){
								if ($Mens == 1) { $Mensagem .= ", "; }
								$Codigo    = "";
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.Codigo.focus();\" class=\"titulo2\">C&oacute;digo V&aacute;lido</a>";
						}
				}
		}else{
				$Codigo = "";
		}

		if( $Mens  == 0 ){
				$DataAtual = date("Y-m-d H:i:s");
 			  # Verifica se o Fornecedor é Cadastrado #
			  $db	    = Conexao();
		 	  $sqlfor = "SELECT NFORCRSENH, AFORCRSEQU, DFORCREXPS, AFORCRNTEN FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
		    if( $TipoCnpjCpf == "CPF" ){
		    		$sqlfor   .= "AFORCRCCPF = '$CPF_CNPJ' ";
		    }elseif( $TipoCnpjCpf == "CNPJ" ){
		    		$sqlfor   .= "AFORCRCCGC = '$CPF_CNPJ' ";
		    }
				$resfor = $db->query($sqlfor);
			  if( PEAR::isError($resfor) ){
			  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
				}else{
						$rowfor = $resfor->numRows();
						if( $rowfor != 0 ){
					  		$Mens     = 1;
					  		$Tipo     = 1;
					  		$Virgula  = 2;
								$Mensagem = "Fornecedor Já Cadastrado, selecione no menu a opção Fornecedores/Acompanhamento para a verificação dos dados cadastrais";
						}else{
							  # Verifica se o Fornecedor Já foi Inscrito #
							  $db	    = Conexao();
						 	  $sqlpre = "SELECT NPREFOSENH, APREFOSEQU, DPREFOEXPS, APREFONTEN FROM SFPC.TBPREFORNECEDOR WHERE ";
						    if( strlen($CPF_CNPJ) == 11 ){
						    		$sqlpre   .= "APREFOCCPF = '$CPF_CNPJ' ";
						    }elseif( strlen($CPF_CNPJ) == 14 ){
						    		$sqlpre   .= "APREFOCCGC = '$CPF_CNPJ' ";
						    }
								$respre = $db->query($sqlpre);
							  if( PEAR::isError($respre) ){
							  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
								}else{
										$rowpre = $respre->numRows();
										if( $rowpre != 0 ){
												$prefornecedor = $respre->fetchRow();
												$SenhaPre      = $prefornecedor[0];
												$Sequencial    = $prefornecedor[1];
												$SenhaCript    = crypt($Senha,"P");
												$DataExpSenha  = $prefornecedor[2];
												$NumTentativas = $prefornecedor[3];
												if( $SenhaPre !=  $SenhaCript ){
														if( $NumTentativas < 5 ){
																$NumTentativas++;
																if( $Mens == 1){$Mensagem.=", ";}
													  		$Codigo    = "";
													  		$Mens      = 1;
													  		$Tipo      = 2;
																$Mensagem .= "<a href=\"javascript:document.ConsInscritoSenha.Senha.focus();\" class=\"titulo2\">Senha Válida</a>";

																# Atualiza no Banco o número de tentativas #
																$db->query("BEGIN TRANSACTION");
																$sql  = "UPDATE SFPC.TBPREFORNECEDOR ";
																$sql .= "   SET APREFONTEN = $NumTentativas, TPREFOULAT = '$DataAtual' ";
																$sql .= " WHERE APREFOSEQU = $Sequencial";
																$result = $db->query($sql);
																if( PEAR::isError($result) ){
																		$db->query("ROLLBACK");
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}
																$db->query("COMMIT");
																$db->query("END TRANSACTION");
														}else{
													  		$Mens       = 1;
													  		$Tipo       = 2;
													  		$Codigo     = "";
																$Mensagem   = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
																$Mensagem  .= "Para solicitar uma nova senha procurar a Divisão de Credenciado - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo nº 925 - Bairro do Recife - Recife/PE";
													 	}
												}else{
														if( $NumTentativas < 5 ){
																# Atualiza no Banco o número de tentativas #
																$db->query("BEGIN TRANSACTION");
																$sql  = "UPDATE SFPC.TBPREFORNECEDOR ";
																$sql .= "   SET APREFONTEN = 0, TPREFOULAT = '$DataAtual' ";
																$sql .= " WHERE APREFOSEQU = $Sequencial";
																$result = $db->query($sql);
																if( PEAR::isError($result) ){
																		$db->query("ROLLBACK");
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}
																$db->query("COMMIT");
																$db->query("END TRANSACTION");

																if( $DataExpSenha < date("Y-m-d") and $DataExpSenha != "" ){
																		# Redireciona para a página de Alteração de Senha #
																		$Programa = urlencode("ConsInscritoSenha.php");
																		$Url = "RotAlteracaoSenhaFornecedor.php?Sequencial=$Sequencial&TipoForn=I&Programa=$Programa";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit();
																}else{
																		# Redireciona para a página de Consulta #
																		$Url = "ConsInscrito.php?Sequencial=$Sequencial";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit();
																}
														}else{
													  		$Mens       = 1;
													  		$Tipo       = 2;
													  		$Codigo     = "";
																$Mensagem   = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
																$Mensagem  .= "Para solicitar uma nova senha procurar a Divisão de Credenciado - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo nº 925 - Bairro do Recife - Recife/PE";
													 	}
												}
										}else{
									  		$Mens     = 1;
									  		$Tipo     = 1;
												$Mensagem = "Fornecedor Inscrito não Encontrado em Nossos Cadastros";
										}
								}
								$db->disconnect();
						}
				}
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
	document.ConsInscritoSenha.Botao.value=valor;
	document.ConsInscritoSenha.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsInscritoSenha.php" method="post" name="ConsInscritoSenha">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição > Consulta
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,$Virgula); } ?>
	  </td>
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
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					CONSULTA DE FORNECEDORES INSCRITOS - LOGIN
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Preencha os dados abaixo e clique no botão "Confimar".
										Para limpar os dados, clique no botão "Limpar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
    	      		<td class="textonormal">
				        	<table border="0" cellpadding="0" cellspacing="2" summary="" class="textonormal" width="100%">
	    			      	<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7">
				    	      		<?php if( $TipoCnpjCpf == "CPF" or $TipoCnpjCpf == "" ){ $MarcaCPF = "checked";  }else{	$MarcaCNPJ = "checked"; } ?>
												<input type="radio" name="TipoCnpjCpf" value="CPF" <?php echo $MarcaCPF; ?>> CPF*
												<input type="radio" name="TipoCnpjCpf" value="CNPJ" <?php echo $MarcaCNPJ; ?>>CNPJ*
		    		      		</td>
		    		      		<td class="textonormal">
			      	    			<input type="text" class="textonormal" name="CPF_CNPJ" size="15" maxlength="14" value="<?php echo $CPF_CNPJ; ?>">
											</td>
						      	</tr>
	    			      	<tr>
		    		      		<td class="textonormal" bgcolor="#DCEDF7">Senha*</td>
			    	      		<td class="textonormal">
			      	    			<input type="password" class="textonormal" name="Senha" size="15" maxlength="8" value="<?php echo $Senha; ?>">
											</td>
						      	</tr>
	    			      	<tr>
		    		      		<td class="textonormal" bgcolor="#DCEDF7">Código*</td>
			    	      		<td class="textonormal">
			      	    			<input type="text" class="textonormal" name="Codigo" size="15" maxlength="5" value="<?php echo $Codigo;?>">
			      	    			<img src="../midia/seta_direita2.gif" hspace="5" alt=""><img src="/common/rotinas_php/Gerajpeg/Gerajpeg.php">
											</td>
						      	</tr>
						      </table>
    						</td>
        	    </tr>
      	      <tr>
    	      		<td align="right" colspan="4">
  	      				<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
  	      				<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
            			<input type="hidden" name="Botao" value="">
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
