<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotAlteracaoSenhaFornecedor.php
# Autor:    Roberta Costa
# Data:     13/12/04
# Objetivo: Programa de Alteração de Senha do Fornecedor
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
		$Sequencial    = $_POST['Sequencial'];
		$TipoForn      = $_POST['TipoForn'];
		$Programa      = $_POST['Programa'];
		$CPF_CNPJ      = $_POST['CPF_CNPJ'];
		$TipoCnpjCpf   = $_POST['TipoCnpjCpf'];
}else{
		$Sequencial  = $_GET['Sequencial'];
		$TipoForn    = $_GET['TipoForn'];
		$Programa    = $_GET['Programa'];
		$TipoCnpjCpf = $_GET['TipoCnpjCpf'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SenhaAtual == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Fornecedor.SenhaAtual.focus();\" class=\"titulo2\">Senha atual</a>";
		}
		if( $NovaSenha == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Fornecedor.NovaSenha.focus();\" class=\"titulo2\">Nova senha</a>";
		}
		if( $ConfirmaSenha == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Fornecedor.ConfirmaSenha.focus();\" class=\"titulo2\">Confirmação da senha</a>";
		}else{
				if( $NovaSenha != $ConfirmaSenha ){
						$Mens          = 1;
						$Tipo          = 2;
						$Mensagem      = "Confirmação de senha inválida";
						$SenhaAtual    = "";
						$NovaSenha     = "";
						$ConfirmaSenha = "";
				}
		}
		if( $Mens == 0 ) {
				$db = Conexao();
				if( $TipoForn == "I" ){
						$sql = "SELECT NPREFOSENH FROM SFPC.TBPREFORNECEDOR WHERE APREFOSEQU = $Sequencial";
				}elseif( $TipoForn == "F" ){
						$sql = "SELECT NFORCRSENH FROM SFPC.TBFORNECEDORCREDENCIADO WHERE AFORCRSEQU = $Sequencial";
				}
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha  = $result->fetchRow();
						$SenAtu = $Linha[0];
						$db->disconnect();
						if( $SenAtu != crypt($SenhaAtual,"P") ){
								$Mens          = 1;
								$Tipo          = 2;
								$Mensagem     .= "<a href=\"javascript:document.Fornecedor.SenhaAtual.focus();\" class=\"titulo2\">Senha Atual Inválida</a>";
								$SenhaAtual    = "";
								$NovaSenha     = "";
								$ConfirmaSenha = "";
						}
				}
		}
		if( $Mens == 0 ){
				$SenhaCript = crypt ($NovaSenha,"P");
				$Data       = date("Y-m-d H:i:s");
				//$DataExp    = SomaData(365,date("d/m/Y"));
				//$DataExpInv = DataInvertida($DataExp);
				$db         = Conexao();
				$db->query("BEGIN TRANSACTION");
				if( $TipoForn == "I" ){
						$sql    = "UPDATE SFPC.TBPREFORNECEDOR ";
						$sql   .= "   SET NPREFOSENH = '$SenhaCript', APREFONTEN = 0, ";
						$sql   .= "       DPREFOEXPS = NULL, TPREFOULAT = '$Data' ";
						$sql   .= " WHERE APREFOSEQU = $Sequencial";
				}elseif( $TipoForn == "F" ){
						$sql    = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
						$sql   .= "   SET NFORCRSENH = '$SenhaCript', AFORCRNTEN = 0, ";
						$sql   .= "       DFORCREXPS = NULL, TFORCRULAT = '$Data' ";
						$sql   .= " WHERE AFORCRSEQU = $Sequencial";
				}
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();

						# Redireciona para a página de Senha #
						$Mensagem = urlencode("Senha Alterada com Sucesso");
						if( $Programa == "ConsAcompFornecedorSenha.php" ){
								$NomePrograma = "ConsAcompFornecedor.php";
						}elseif( $Programa == "ConsInscritoSenha.php" ){
								$NomePrograma = "ConsInscrito.php";
						}elseif( $Programa == "EmissaoCHFSenha.php" ){
								$NomePrograma = "EmissaoCHF.php";
						}
						$Url = "$NomePrograma?Sequencial=$Sequencial&CPF_CNPJ=$CPF_CNPJ&Mensagem=$Mensagem&Mens=1&Tipo=1&TipoCnpjCpf=$TipoCnpjCpf";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
		$Critica = 0;
}

if( $Critica == 0 ){
		$db	= Conexao();
		# Busca os Dados da Tabela de Inscritos ou de Fornecedor #
		if( $TipoForn == "I" ){
				$sql  = "SELECT APREFOCCGC, APREFOCCPF, NPREFORAZS, NPREFOMAIL ";
				$sql .= "  FROM SFPC.TBPREFORNECEDOR ";
				$sql .= " WHERE APREFOSEQU = $Sequencial";
		}elseif( $TipoForn == "F" ){
				$sql  = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, NFORCRMAIL ";
				$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
				$sql .= "  WHERE AFORCRSEQU = $Sequencial";
		}
 		$result 	= $db->query($sql);
		if( PEAR::isError($result) ){
	    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$linha = $result->fetchRow();
				if( $linha[0] != 0 ){
						$CPF_CNPJ	 	 = $linha[0];
	    			$CNPJCPFForm = FormataCNPJ($linha[0]);
				}else{
						$CPF_CNPJ	 	 = $linha[1];
		    		$CNPJCPFForm = FormataCPF($linha[1]);
				}
				$Razao		 = $linha[2];
				$Email		 = $linha[3];
		}
		$db->disconnect();
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
<br><br><br><br><br>
<form name="Fornecedor" action="RotAlteracaoSenhaFornecedor.php" method="post">
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores >
			<?php
			if( $Programa == "ConsAcompFornecedorSenha.php" ){
					echo "Acompanhamento";
			}elseif( $Programa == "ConsInscritoSenha.php" ){
					echo "Inscrição > Consulta";
			}elseif( $Programa == "EmissaoCHFSenha.php" ){
					echo "CHF > Emissão de CHF";
			}
			?>
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
	        	<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" bgcolor="#ffffff" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	            		ALTERAR SENHA DO FORNECEDOR
		    					<?php if( $Programa == "ConsInscritoSenha.php" ){ echo " INSCRITO"; } ?>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Quando o Fornecedor acessa o Portal de Compras da Prefeitura do Recife pela primeira vez é necessário alterar a senha de acesso temporária. Se a senha for alterada com sucesso será exibida a tela de
										<?php
										if( $Programa == "ConsAcompFornecedorSenha.php" ){
												echo "Acompanhamento ";
										}elseif( $Programa == "ConsInscritoSenha.php" ){
												echo "Consulta ";
										}elseif( $Programa == "EmissaoCHFSenha.php" ){
												echo "Emissão de CHF ";
										}
										?>
										automaticamente.<br><br>
	        	    		Para Alterar a senha do fornecedor, informe os dados abaixo e clique no botão "Alterar". Para Limpar a tela clique no botão "Limpar".
	        	    		Os campos obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table align="left" cellpadding="0" cellspacing="2" class="textonormal" width="100%" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">CNPJ/CPF</td>
	          	    		<td class="textonormal">
	          	    			<?php echo $CNPJCPFForm; ?>
	          	    			<input type="hidden" name="Critica" value="1">
	          	    			<input type="hidden" name="CPF_CNPJ" value="<?php echo $CPF_CNPJ; ?>">
	            	  		</td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social/Nome</td>
	          	    		<td class="textonormal"><?php echo $Razao; ?></td>
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
	        	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Confirmação da Senha* </td>
	          	    		<td class="textonormal">
	          	    			<input type="password" name="ConfirmaSenha" value="<?php echo $ConfirmaSenha; ?>" size="8" maxlength="8" class="textonormal">
	          	    		</td>
	            	  	</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
									<input type="hidden" name="TipoCnpjCpf" value="<?php echo $TipoCnpjCpf; ?>">
									<input type="hidden" name="TipoForn" value="<?php echo $TipoForn; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
									<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
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
document.Fornecedor.SenhaAtual.focus();
//-->
</script>
</body>
</html>
