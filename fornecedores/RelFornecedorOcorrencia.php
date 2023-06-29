<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorOcorrencia.php
# Autor:    Roberta Costa
# Data:     27/10/04
# Objetivo: Programa que Seleciona Fornecedor para Impressão de Ocorrências
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------
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
AddMenuAcesso( '/fornecedores/RelFornecedorOcorrenciaPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$ItemPesquisa = $_POST['ItemPesquisa'];
		$Argumento		= trim($_POST['Argumento']);
		$Palavra			= $_POST['Palavra'];
		$TipoForn			= $_POST['TipoForn'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = $_GET['Mensagem'];
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelFornecedorOcorrencia.php");
	  exit;
}elseif( $Botao == "Pesquisar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $Argumento != "" ){
				if( ($ItemPesquisa == "CNPJ") and (!SoNumeros($Argumento)) ){
			    	$Mens 		 = 1;$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelFornecedorOcorrencia.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;$Tipo = 2;
								$Mensagem .= "<a href=\"javascript:document.RelFornecedorOcorrencia.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorOcorrencia.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.RelFornecedorOcorrencia.Botao.value=valor;
	document.RelFornecedorOcorrencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelFornecedorOcorrencia.php" method="post" name="RelFornecedorOcorrencia">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Relatórios > Ocorrências de Fornecedores
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left">
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
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="3" class="titulo3">
		    					RELATÓRIO DE OCORRÊNCIAS DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="3">
	      	    		<p align="justify">
	        	    		Preencha o argumento da pesquisa e clique no botão "Pesquisar".
										Depois, clique no fornecedor desejado para visualizar as ocorrências do fornecedor.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
  	      		<tr>
      	    		<td class="textonormal" colspan="3">
      	    			<table border="0" cellpadding="0" cellspacing="2" summary="" class="textonormal" width="100%">
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Pesquisa<span style="color: red;">*</span></td>
			    	      		<td class="textonormal">
			 									<select name="ItemPesquisa" class="textonormal" value="<?php echo $ItemPesquisa;?>" >
							          	<?php if( $ItemPesquisa == "CNPJ" ){?>
										  			<option value="CNPJ">CNPJ
										  			<option value="CPF">CPF
										  			<option value="RAZAO">Razão Social/Nome
							          	<?php } else if( $ItemPesquisa == "CPF" ){?>
										  			<option value="CPF">CPF
										  			<option value="CNPJ">CNPJ
										  			<option value="RAZAO">Razão Social/Nome
							          	<?php }else{ ?>
										  			<option value="RAZAO">Razão Social/Nome
										  			<option value="CNPJ">CNPJ
										  			<option value="CPF">CPF
							          	<?php } ?>
												</select>
											</td>
											</td>
			        	    </tr>
			      	    	<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7">Argumento<span style="color: red;">*</span></td>
			    	      		<td class="textonormal">
			      	    			<input type="text" class="textonormal" name="Argumento" size="40" maxlength="60" value="<?php echo $Argumento;?>">
												<input type="checkbox" class="textonormal" name="Palavra" value="1" <?php if( $Palavra == 1 ){ echo "checked";}?>> Palavra Exata
											</td>
			        	    </tr>
									</table>
								</td>
        	    </tr>
      	      <tr>
    	      		<td align="right" colspan="3">
  	      				<input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
  	      				<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
            			<input type="hidden" name="Botao" value="">
								</td>
        			</tr>
		        	<?php
							if( $Botao == "Pesquisar" and $Mens == 0 ){
									$Argumento = strtoupper2($Argumento);
									# Busca os Dados da Tabela de PreFornecedor de Acordo com o argumento da pesquisa #
									$db	  = Conexao();
									$sql  = "SELECT DISTINCT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS ";
									$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDOROCORRENCIA B WHERE A.AFORCRSEQU = B.AFORCRSEQU AND ";
									if( $ItemPesquisa == "CNPJ" ){
					   					$sql .= "A.AFORCRCCGC  LIKE '$Argumento' ";
									}elseif( $ItemPesquisa == "CPF" ){
						   				$sql .= "A.AFORCRCCPF LIKE '$Argumento' ";
									}else{
											if( $ItemPesquisa == "RAZAO" ){
													if( $Palavra == 1 ){
															$sql .= SQL_ExpReg("A.NFORCRRAZS",$Argumento)." ";
													}else{
							   							$sql .= "A.NFORCRRAZS LIKE '$Argumento%' ";
													}
											}
									}
						 			$sql   .= "ORDER BY A.NFORCRRAZS ";
						 			$result = $db->query($sql);
									if( PEAR::isError($result) ){
							    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Qtd = $result->numRows();
											Resultado($Qtd,$result,$TipoForn);
									}
									$db->disconnect();
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
<?php
function Resultado($Qtd,$result,$TipoForn){
	echo "<tr>\n";
	echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"3\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	if( $Qtd > 0 ){
			if( $ItemPesquisa == "CNPJ" ){
					echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" width=\"24%\">CNPJ</td>\n";
			}elseif( $ItemPesquisa == "CPF" ){
					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CPF</td>\n";
			}else{
					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CNPJ/CPF</td>\n";
			}
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\">RAZÃO/NOME</td>\n";
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" width=\"22%\">DATA INSCRIÇÃO</td>\n";
			echo "		</tr>\n";
			$Sequencial	= "";
			while( $Linha	= $result->fetchRow() ){
					$DataInscricao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
					$Sequencial	   = $Linha[1];
					$CNPJ    			 = $Linha[2];
					$CPF					 = $Linha[3];
					$Razao         = $Linha[4];
					echo "	<tr>\n";
					if( $CNPJ != 0 ){
	     				$CNPJForm = $CNPJ;
	     				$CNPJForm = (substr($CNPJForm,0,2).".".substr($CNPJForm,2,3).".".substr($CNPJForm,5,3)."/".substr($CNPJForm,8,4)."-".substr($CNPJForm,12,2));
					}else{
	     				$CPFForm = $CPF;
	     				$CPFForm = (substr($CPFForm,0,3).".".substr($CPFForm,3,3).".".substr($CPFForm,6,3)."-".substr($CPFForm,9,2));
					}
					$Url = "RelFornecedorOcorrenciaPdf.php?Sequencial=$Sequencial&".mktime();
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					if( $CNPJ != 0 ){
							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
					}else{
							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
					}
					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataInscricao</td>\n";
					echo "	</tr>\n";
			}
	}else{
			echo "		<td valign=\"top\" colspan=\"3\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
			echo "		Pesquisa sem Ocorrências.\n";
			echo "		</td>\n";
			echo "	</tr>\n";
	}
	echo "</table>\n";
}
?>
