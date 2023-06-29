<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: EmissaoCHFSenha.php
# Autor:    Roberta Costa
# Data:     21/09/04
# Objetivo: Programa que faz o login do Fornecedor para Emissão do CHF
# Alterado: Rossana Lira
# Data:     16/05/07 - Troca do nome fornecedor para firma
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/EmissaoCHFSenha.php' );
AddMenuAcesso( '/fornecedores/RotAlteracaoSenhaFornecedor.php' );
AddMenuAcesso( '/fornecedores/EmissaoCHF.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao       = $_POST['Botao'];
		$CPF_CNPJ	   = $_POST['CPF_CNPJ'];
		$Senha 		   = $_POST['Senha'];
		$Codigo		   = $_POST['Codigo'];
		$TipoCnpjCpf = $_POST['TipoCnpjCpf'];
}else{
		$Mens     = $_GET['Mens'];
		$Tipo			= $_GET['Tipo'];
		$Mensagem = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: EmissaoCHFSenha.php");
	  exit;
}
if( $Botao == "Confirmar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $TipoCnpjCpf == "CPF" ){
		  	$Qtd = strlen($CPF_CNPJ);
		  	$TipoDoc = 2;
		  	if( ($Qtd != 11) and ($Qtd != 0) ){
		      	if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
				}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
				}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CPF($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
	  				}
		  	}
		}elseif( $TipoCnpjCpf == "CNPJ" ){
				$Qtd = strlen($CPF_CNPJ);
				$TipoDoc = 1;
		   	if( ($Qtd != 14) and ($Qtd != 0)  ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
			 	}elseif( $CPF_CNPJ == "" ){
						if ($Mens == 1){$Mensagem.=", ";}
						$Mens = 1;$Tipo = 2;
				  	$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
			 	}else{
				  	if ($Mens == 1){$Mensagem.=", ";}
						$cpfcnpj = valida_CNPJ($CPF_CNPJ);
						if( $cpfcnpj === false ){
						  	$Mens = 1;$Tipo = 2;
	  						$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
	  				}
			 	}
		}
		if( $cpfcnpj === true ){
				# Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
				$Senha = trim($Senha);
				if( $Senha == ""  ){
						if( $Mens == 1){$Mensagem.=", ";}
			  		$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.Senha.focus();\" class=\"titulo2\">Senha</a>";
				}
				if( $Codigo == ""  ){
						if( $Mens == 1){$Mensagem.=", ";}
			  		$Mens = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.Codigo.focus();\" class=\"titulo2\">Código</a>";
				}else{
						if( strtoupper2($Codigo) != $_SESSION['_Combinacao_'] ){
								if ($Mens == 1) { $Mensagem .= ", "; }
								$Mens = 1; $Tipo = 2; $Mod = 1;
								$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.Codigo.focus();\" class=\"titulo2\">C&oacute;digo</a> V&aacute;lido";
						}
				}
		}else{
				$Codigo = "";
		}

		if( $Mens  == 0 ){
				$DataAtual = date("Y-m-d H:i:s");

			  # Verifica se o Fornecedor é Cadastrado #
			  $db	= Conexao();
		 	  $sqlfor = "SELECT NFORCRSENH, AFORCRSEQU, DFORCREXPS, AFORCRNTEN FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
		    if( strlen($CPF_CNPJ) == 11 ){
		    		$sqlfor   .= "AFORCRCCPF = '$CPF_CNPJ' ";
		    }elseif( strlen($CPF_CNPJ) == 14 ){
		    		$sqlfor   .= "AFORCRCCGC = '$CPF_CNPJ' ";
		    }

				$resfor = $db->query($sqlfor);
			  if( PEAR::isError($resfor) ){
			  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$rowfor = $resfor->numRows();
						if( $rowfor != 0 ){
								$fornecedor    = $resfor->fetchRow();
								$SenhaFor      = $fornecedor[0];
								$Sequencial    = $fornecedor[1];
								$DataExpSenha  = $fornecedor[2];
								$NumTentativas = $fornecedor[3];
								if( $SenhaFor != crypt($Senha,"P") ){
										if( $NumTentativas < 5 ){
												$NumTentativas++;
												if( $Mens == 1){$Mensagem.=", ";}
									  		$Mens      = 1;
									  		$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript:document.EmissaoCHFSenha.Senha.focus();\" class=\"titulo2\">Senha Válida</a>";
												$Codigo    = "";

												# Atualiza no Banco o número de tentativas #
												$db->query("BEGIN TRANSACTION");
												$sql  = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
												$sql .= "   SET AFORCRNTEN = $NumTentativas, TFORCRULAT = '$DataAtual'";
												$sql .= " WHERE AFORCRSEQU = $Sequencial";
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
												$sql  = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
												$sql .= "   SET AFORCRNTEN = 0, TFORCRULAT = '$DataAtual'";
												$sql .= " WHERE AFORCRSEQU = $Sequencial";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}
												$db->query("COMMIT");
												$db->query("END TRANSACTION");

												if( $DataExpSenha < date("Y-m-d") and $DataExpSenha != "" ){
														# Redireciona para a página de Alteração de Senha #
														$Programa = urlencode("EmissaoCHFSenha.php");
														$Url = "RotAlteracaoSenhaFornecedor.php?Sequencial=$Sequencial&TipoForn=F&Programa=$Programa&TipoCnpjCpf=$TipoCnpjCpf";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}else{
														# Redireciona para a página de Acompanhamento #
														$Url = "EmissaoCHF.php?Sequencial=$Sequencial&CPF_CNPJ=$CPF_CNPJ&TipoCnpjCpf=$TipoCnpjCpf";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
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
							  # Verifica se o Fornecedor Já foi Inscrito #
							  $db	    = Conexao();
						 	  $sqlpre = "SELECT NPREFOSENH, APREFOSEQU FROM SFPC.TBPREFORNECEDOR WHERE ";
						    if( strlen($CPF_CNPJ) == 11 ){
						    		$sqlpre   .= "APREFOCCPF = '$CPF_CNPJ' ";
						    }elseif( strlen($CPF_CNPJ) == 14 ){
						    		$sqlpre   .= "APREFOCCGC = '$CPF_CNPJ' ";
						    }
								$respre = $db->query($sqlpre);
							  if( PEAR::isError($respre) ){
							  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$rowpre = $respre->numRows();
										if( $rowpre != 0 ){
								  		$Mens     = 1;
								  		$Tipo     = 1;
											$Mensagem = "Fornecedor Encontrado como Inscrito em Nossos Cadastros";
										}else{
								  		$Mens     = 1;
								  		$Tipo     = 2;
											$Mensagem = "Fornecedor não Encontrado em Nossos Cadastros";
										}
								}
						}
				}
				$db->disconnect();
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
	document.EmissaoCHFSenha.Botao.value=valor;
	document.EmissaoCHFSenha.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="EmissaoCHFSenha.php" method="post" name="EmissaoCHFSenha">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
	    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > CHF > Emissão do CHF
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
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
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					EMISSÃO DO CERTIFICADO DE HABILITAÇÃO DE FIRMAS - LOGIN
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
				        	<table border="0" cellpadding="0" cellspacing="2" class="textonormal" width="100%" summary="">
	    			      	<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="35%">
				    	      		<input type="radio" name="TipoCnpjCpf" value="CPF" <?php if( $TipoCnpjCpf == "CPF" or $TipoCnpjCpf == "" ){ echo "checked"; } ?>> CPF*
												<input type="radio" name="TipoCnpjCpf" value="CNPJ" <?php if( $TipoCnpjCpf == "CNPJ" ){ echo "checked"; } ?>>CNPJ*
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
            			<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
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
