<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRegistroOcorrenciaSelecionar.php
# Autor:    Roberta Costa
# Data:     29/09/04
# Objetivo: Programa que Selecionar o Fornecedor para Incluir Ocorrência
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadRegistroOcorrencia.php' );
AddMenuAcesso( '/fornecedores/CadRegistroOcorrenciaIncluir.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$ItemPesquisa = $_POST['ItemPesquisa'];
		$Argumento		= strtoupper2(trim($_POST['Argumento']));
		$Palavra			= $_POST['Palavra'];
		$Programa     = $_POST['Programa'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
		$Programa = $_GET['Programa'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: CadRegistroOcorrenciaSelecionar.php");
	  exit;
}elseif( $Botao == "Pesquisar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $Argumento != "" ){
				if( ($ItemPesquisa == "CNPJ") and (!SoNumeros($Argumento)) ){
			    	$Mens 		 = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaSelecionar.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;$Tipo = 2;
								$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaSelecionar.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRegistroOcorrenciaSelecionar.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
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
	document.CadRegistroOcorrenciaSelecionar.Botao.value=valor;
	document.CadRegistroOcorrenciaSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRegistroOcorrenciaSelecionar.php" method="post" name="CadRegistroOcorrenciaSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Registro das Ocorrências >
      <?php if( $Programa == 1 ){ echo "Incluir"; }else{ echo "Manter"; } ?>
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
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ade6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ade6" valign="middle" colspan="4" class="titulo3">
		    					REGISTRO DAS OCORRÊNCIAS DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Selecione o item de pesquisa desejado, preencha o argumento da pesquisa e
	        	    		clique no botão "Pesquisar". Depois, clique no fornecedor desejado para
	        	    		<?php if( $Programa == 1 ){ echo "incluir "; }else{ echo "alterar ou excluir "; } ?>
	        	    		as ocorrências.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
    	      		<td class="textonormal" colspan="4">
      	    			<table border="0" cellpadding="0" cellspacing="2" summary="" class="textonormal" width="100%">
				  	      	<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" width="30%">Pesquisa*</td>
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
			    	      		<td class="textonormal" bgcolor="#DCEDF7">Argumento*</td>
			    	      		<td class="textonormal">
			      	    			<input type="text" class="textonormal" name="Argumento" size="40" maxlength="60" value="<?php echo $Argumento;?>">
												<input type="checkbox" class="textonormal" name="Palavra" value="1" <?php if( $Palavra == 1 ){ echo "checked";}?>> Palavra Exata
											</td>
			        	    </tr>
									</table>
								</td>
      	      </tr>
      	      <tr>
    	      		<td align="right" colspan="4">
            			<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
  	      				<input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
  	      				<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
            			<input type="hidden" name="Botao" value="">
								</td>
        			</tr>
		        	<?php
							if( $Botao == "Pesquisar" and $Mens == 0 ){
									$DataAtual = date("Y-m-d");
									$Argumento = strtoupper2($Argumento);
									# Busca os Dados da Tabela de Fornecedor de Acordo com o argumento da pesquisa #
									$db	  = Conexao();
									$sql  = "SELECT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, ";
									$sql .= "       A.NFORCRRAZS, B.DFORCHVALI ";
									$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDORCHF B ";
									$sql .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND "; //B.DFORCHVALI < '$DataAtual' AND ";
									if( $Palavra == 1 ){
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC  LIKE '$Argumento' ";
											}else{
													if( $ItemPesquisa == "CPF" ){
								   						$sql .= "A.AFORCRCCPF LIKE '$Argumento' ";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
																	$sql .= SQL_ExpReg("A.NFORCRRAZS",$Argumento)." ";
															}
													}
											}
									}else{
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC LIKE '%$Argumento%' ";
											}else{
													if( $ItemPesquisa == "CPF" ){
								   						$sql .= "A.AFORCRCCPF LIKE '%$Argumento%' ";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
									   							$sql .= "A.NFORCRRAZS LIKE '$Argumento%' ";
															}
													}
											}
									}
						 			$sql     .= "ORDER BY A.NFORCRRAZS ";
						 			$result 	= $db->query($sql);
									if( PEAR::isError($result) ){
							    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Qtd = $result->numRows();
											echo "			<tr>\n";
											echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "			</tr>\n";
											if( $Qtd > 0 ){
													echo "		<tr>\n";
													if( $ItemPesquisa == "CNPJ" ){
															echo "		<td class=\"titulo3\" bgcolor=\"#DCEDF7\" width=\"24%\">CNPJ</td>\n";
													}elseif( $ItemPesquisa == "CPF" ){
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CPF</td>\n";
													}else{
															echo "		<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CNPJ/CPF</td>\n";
													}
													echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\">RAZÃO/NOME</td>\n";
													echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" width=\"22%\">DATA INSCRIÇÃO</td>\n";
													echo "		</tr>\n";
													$Sequencial	= "";
													while( $Linha	= $result->fetchRow() ){
															$Sequencial	   = $Linha[1];
															$CNPJ    			 = $Linha[2];
															$CPF					 = $Linha[3];
															$Razao         = $Linha[4];
															$DataInscricao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
															$DataValidade  = substr($Linha[5],8,2)."/".substr($Linha[5],5,2)."/".substr($Linha[5],0,4);
															echo "	<tr>\n";
															if( $CNPJ <> 0 ){
							             				$CNPJForm = $CNPJ;
							             				$CNPJForm = (substr($CNPJForm,0,2).".".substr($CNPJForm,2,3).".".substr($CNPJForm,5,3)."/".substr($CNPJForm,8,4)."-".substr($CNPJForm,12,2));
															}else{
							             				$CPFForm = $CPF;
							             				$CPFForm = (substr($CPFForm,0,3).".".substr($CPFForm,3,3).".".substr($CPFForm,6,3)."-".substr($CPFForm,9,2));
															}

															# Situação do Fornecedor é igual a Aprovado ou excluído #
															if( ($Situacao == 2) or ($Situacao == 5) ){
																	if( $CNPJ <> 0 ){
																			echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CNPJForm</td>\n";
																	}else{
																			echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CPFForm</td>\n";
																	}
															}else{
																	if( $Programa == 1 ){ 
																			$NomePrograma = "CadRegistroOcorrenciaIncluir.php"; 
																	} else{ 
																			$NomePrograma = "CadRegistroOcorrencia.php"; 
																	}
																	if( $CNPJ <> 0 ){
																			echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$NomePrograma?Sequencial=$Sequencial\"><font color=\"#000000\">$CNPJForm</font></td>\n";
																	}else{
																			echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$NomePrograma?Sequencial=$Sequencial\"><font color=\"#000000\">$CPFForm</font></td>\n";
																	}
															}
															echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
															echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataValidade</td>\n";
													}
													echo "</tr>\n";
									    		$db->disconnect();
											}else{
													echo "	<tr>\n";
													echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
													echo "		Pesquisa sem Ocorrências de Fornecedores com Data de CHF Preenchida.\n";
													echo "		</td>\n";
													echo "	</tr>\n";
											}
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
