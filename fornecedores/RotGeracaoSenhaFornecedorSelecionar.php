<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotGeracaoSenhaFornecedorSelecionar.php
# Autor:    Rossana Lira
# Data:     30/06/04
# Objetivo: Programa de Geração de Senha de Fornecedor - Selecionar
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RotGeracaoSenhaFornecedor.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	= $_POST['Botao'];
		$Critica      	= $_POST['Critica'];
		$ItemPesquisa 	= $_POST['ItemPesquisa'];
		$Argumento			= $_POST['Argumento'];
		$Palavra				= $_POST['Palavra'];
		$TipoForn				= $_POST['TipoForn'];
} else {
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RotGeracaoSenhaFornecedorSelecionar.php");
	  exit;
}

if( $Critica == 1 ){
		$Mens			= 0;
		$Mensagem = "Informe: ";
		if( $Argumento != "" ){
				$Argumento 		= trim($Argumento);
				if( ($ItemPesquisa == "CNPJ") and (!SoNumeros($Argumento)) ){
			    	$Mens 		 = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.Geracao.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;$Tipo = 2;
								$Mensagem .= "<a href=\"javascript:document.Geracao.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Geracao.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
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
	document.Geracao.Botao.value=valor;
	document.Geracao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotGeracaoSenhaFornecedorSelecionar.php" method="post" name="Geracao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Geração Senha
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="3" class="titulo3">
		    					GERAÇÃO SENHA - FORNECEDOR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="3">
	      	    		<p align="justify">
	        	    		Informe o Tipo do Fornecedor, selecione o item de pesquisa desejado, preencha o argumento da pesquisa e clique no botão "Selecionar".<br>
										Depois, clique no fornecedor ou inscrito desejado para gerar uma nova senha.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
	  	        	<td colspan="3">
	    	      		<table class="textonormal" cellpadding="0" cellspacing="2" border="0" align="left" width="100%">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Tipo*</td>
	          	    		<td class="textonormal">
												<?php
												if( $TipoForn == "FORN" ){
														echo "<input type=\"radio\" name=\"TipoForn\" value=\"INSC\"> Inscrito\n";
														echo "<input type=\"radio\" name=\"TipoForn\" value=\"FORN\" checked >Fornecedor\n";
												}else{
														echo "<input type=\"radio\" name=\"TipoForn\" value=\"INSC\" checked> Inscrito\n";
														echo "<input type=\"radio\" name=\"TipoForn\" value=\"FORN\">Fornecedor\n";
											  }
												?>
											</td>
	            	  		<td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
	            			</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Pesquisa*</td>
			    	      		<td class="textonormal">
			 									<select name="ItemPesquisa" class="textonormal" value="<?php echo $ItemPesquisa;?>" >
							          	<?php if( $ItemPesquisa == "CNPJ" ){?>
									  			<option value="CNPJ">CNPJ</option>
									  			<option value="CPF">CPF</option>
									  			<option value="RAZAO">Razão Social/Nome</option>
							          	<?php } else if( $ItemPesquisa == "CPF" ){?>
									  			<option value="CPF">CPF</option>
									  			<option value="CNPJ">CNPJ</option>
									  			<option value="RAZAO">Razão Social/Nome</option>
							          	<?php }else{ ?>
									  			<option value="RAZAO">Razão Social/Nome</option>
									  			<option value="CNPJ">CNPJ</option>
									  			<option value="CPF">CPF</option>
							          	<?php } ?>
												</select>
											</td>
										</tr>
										<tr>
        	      			<td class="textonormal" bgcolor="#DCEDF7" width="30%">Argumento*</td>
        	      			<td>
			      	    			<input type="text" class="textonormal" name="Argumento" size="40" maxlength="60" value="<?php echo $Argumento;?>">
												<input type="checkbox" class="textonormal" name="Palavra" value="1" <?php if( $Palavra == 1 ){ echo "checked";}?>> Palavra Exata
											</td>
			        	    </tr>
	            		</table>
		          	</td>
		        	</tr>
      	      <tr>
    	      		<td align="right" colspan="3">
  	      				<input type="button" name="Selecionar" value="Selecionar" class="botao" onclick="javascript:enviar('Selecionar');">
  	      				<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
            			<input type="hidden" name="Botao" value="">
									<input type="hidden" name="Critica" value="1">
								</td>
        			</tr>
		        	<?php
							if( ($Critica == 1) and ($Mens == 0) ){
									$db	       = Conexao();
									$Argumento = strtoupper2($Argumento);
									if( $TipoForn == "INSC" ){
											# Busca os Dados da Tabela de Inscritos de Acordo com o argumento da pesquisa #
											$sql .= " SELECT DISTINCT A.APREFOSEQU, A.APREFOCCGC, A.APREFOCCPF, A.NPREFORAZS ";
											$sql .= "   FROM SFPC.TBPREFORNECEDOR A ";
											$sql .= "   LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO B ON ( A.APREFOSEQU = B.APREFOSEQU ) ";
											$sql .= "  WHERE A.APREFOSEQU";
											$sql .= "    NOT IN ( SELECT APREFOSEQU FROM SFPC.TBFORNECEDORCREDENCIADO WHERE APREFOSEQU IS NOT NULL ) AND ";
											if( $Palavra == 1 ){
													if( $ItemPesquisa == "CNPJ" ){
											   			$sql .= " A.APREFOCCGC  LIKE '$Argumento' ORDER BY A.APREFOCCGC";
													}else{
															if( $ItemPesquisa == "CPF" ){
														   		$sql .= " A.APREFOCCPF LIKE '$Argumento' ORDER BY A.APREFOCCPF";
															}else{
																	if( $ItemPesquisa == "RAZAO" ){
																			# Monta a expressão regular de pesquisa #
																			$sql .= " ". SQL_ExpReg("A.NPREFORAZS",$Argumento)." ORDER BY A.NPREFORAZS";
																	}
															}
													}
											}else{
													if( $ItemPesquisa == "CNPJ" ){
											   			$sql .= " A.APREFOCCGC  LIKE '%$Argumento%' ORDER BY A.APREFOCCGC";
													}else{
															if( $ItemPesquisa == "CPF" ){
														   		$sql .= " A.APREFOCCPF LIKE '%$Argumento%' ORDER BY A.APREFOCCPF";
															}else{
																	if( $ItemPesquisa == "RAZAO" ){
															   			$sql .= " A.NPREFORAZS LIKE '$Argumento%' ORDER BY A.NPREFORAZS";
																	}
															}
													}
											}
									 		$result 	= $db->query($sql);
											if( PEAR::isError($result) ){
										    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}
									}else{
											# Busca os Dados da Tabela de Fornecedores de Acordo com o argumento da pesquisa #
											$sql  = "SELECT A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS ";
											$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A WHERE ";
											if( $Palavra == 1 ){
													if( $ItemPesquisa == "CNPJ" ){
										   				$sql .= " A.AFORCRCCGC  LIKE '$Argumento' ORDER BY A.AFORCRCCGC";
													}else{
															if( $ItemPesquisa == "CPF" ){
														   		$sql .= " A.AFORCRCCPF LIKE '$Argumento' ORDER BY A.AFORCRCCPF";
															}else{
																	if( $ItemPesquisa == "RAZAO" ){
																			$sql .= " ". SQL_ExpReg("A.NFORCRRAZS",$Argumento)." ORDER BY A.NFORCRRAZS"; // Monta a expressão regular de pesquisa
																	}
															}
													}
											}else{
													if( $ItemPesquisa == "CNPJ" ){
											   			$sql .= " A.AFORCRCCGC  LIKE '%$Argumento%' ORDER BY A.AFORCRCCGC";
													}else{
															if( $ItemPesquisa == "CPF" ){
													   			$sql .= " A.AFORCRCCPF LIKE '%$Argumento%' ORDER BY A.AFORCRCCPF";
															}else{
																	if( $ItemPesquisa == "RAZAO" ){
															   			$sql .= " A.NFORCRRAZS LIKE '$Argumento%' ORDER BY A.NFORCRRAZS";
																	}
															}
													}
											}
									 		$result = $db->query($sql);
											if( PEAR::isError($result) ){
										    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}
									}
									$Qtd = $result->numRows();
									echo "			<tr>\n";
									echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"3\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
									echo "			</tr>\n";
									if( $Qtd > 0 ){
											echo "		<tr>\n";
											if( $ItemPesquisa == "CNPJ" ){
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CNPJ</td>\n";
											}else if ($ItemPesquisa == "CPF"){
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CPF</td>\n";
											}else{
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CNPJ/CPF</td>\n";
											}
											echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\">RAZÃO SOCIAL/NOME</td>\n";
											echo "		</tr>\n";
											$Sequencial				= "";
											while( $Linha 	 	= $result->fetchRow() ){
													$Sequencial			= $Linha[0];
													$CNPJ    				= $Linha[1];
													$CPF						= $Linha[2];
													$Razao          = $Linha[3];
													echo "	<tr>\n";
													if( $CNPJ <> 0 ){
					             				$CNPJForm = FormataCNPJ($CNPJ);
													}else{
					             				$CPFForm = FormataCPF($CPF);
													}
													$Url = "RotGeracaoSenhaFornecedor.php?Sequencial=$Sequencial&TipoForn=$TipoForn";
													if( $CNPJ <> 0 ){
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
													}else{
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
													}
													if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
													echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
											}
											echo "</tr>\n";
									    $db->disconnect();
									}else{
												echo "	<tr>\n";
												echo "		<td valign=\"top\" colspan=\"3\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
												echo "		Pesquisa sem Ocorrências.\n";
												echo "		</td>\n";
												echo "	</tr>\n";
									}
							}
							?>
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
