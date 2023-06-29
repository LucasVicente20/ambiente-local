<?php
#-----------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompFornecedorSelecionar.php
# Autor:    Roberta Costa
# Data:     09/06/05
# Objetivo: Programa que Seleciona Requisição de Material para Acompanhamento
#-----------------
# Alterado: Ariston Cordeiro
# Data:     05/09/2008 - Não usar SQL_ExpReg() de funções, e permitir que a procura seja feita no meio da razão social
# Alterado: Ariston Cordeiro
# Data:     04/02/09 - Correções na busca

# Alterado: Herado Botelho
# Data:			02/04/2013	-	Adaptar para chamar Analisar Pedido e Registrar Certidão
#-----------------
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------------
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
AddMenuAcesso( '/fornecedores/ConsAcompFornecedorSelecionar.php' );
AddMenuAcesso( '/fornecedores/ConsAcompFornecedor.php' );
AddMenuAcesso( '/fornecedores/CadRenovacaoCadastroIncluir.php' );
AddMenuAcesso( '/fornecedores/CadAnaliseCertidaoFornecedor.php' );



# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	= $_POST['Botao'];
		$ItemPesquisa 	= $_POST['ItemPesquisa'];
		$Argumento		= strtoupper2(trim($_POST['Argumento']));
		$Palavra		= $_POST['Palavra'];
		$Desvio    		= $_POST['Desvio'];
}else{
		$Mens      		= $_GET['Mens'];
		$Mensagem  		= $_GET['Mensagem'];
		$Tipo	  		= $_GET['Tipo'];
		$Desvio    		= $_GET['Desvio'];
}


# Atribuir $_SESSION['AcompFornecedorDesvio'] com o desvio de chamada
if (  $Desvio=="CadRenovacaoCadastroIncluir"  )  { 
   $_SESSION['AcompFornecedorDesvio']= "CadRenovacaoCadastroIncluir";
} 
else if   (  $Desvio=="CadAnaliseCertidaoFornecedor"  )  { 
   $_SESSION['AcompFornecedorDesvio']= "CadAnaliseCertidaoFornecedor";
} 
else {
	$_SESSION['AcompFornecedorDesvio']="";
}

 



# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: ConsAcompFornecedorSelecionar.php");
	  exit;
}
if( $Botao == "Pesquisar" ){
		$Mens			= 0;
		$Mensagem = "Informe: "; 
		if( $Argumento != "" ){
				if( $ItemPesquisa == "CNPJ" and ! SoNumeros($Argumento) ){
			    	$Mens 		 = 1;
			    	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadAcompFornecedor.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;
					    	$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadAcompFornecedor.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
				if( $ItemPesquisa == "CNPJ" or $ItemPesquisa == "CPF" ){
						# Verifica se o Fornecedor Já foi Inscrito #
					  $db	    = Conexao();
				 	  $sqlpre = "SELECT PRE.NPREFOSENH, PRE.APREFOSEQU, PRE.DPREFOEXPS, PRE.APREFONTEN ";
				 	  $sqlpre.= "  FROM SFPC.TBPREFORNECEDOR PRE, SFPC.TBFORNECEDORCREDENCIADO FORN WHERE ";
    		    if( $ItemPesquisa == "CPF" ){
				    		$sqlpre   .= "APREFOCCPF = '$Argumento' AND PRE.APREFOSEQU = FORN.APREFOSEQU AND AFORCRCCPF <> '$Argumento'";
				    }elseif( $ItemPesquisa == "CNPJ" ){
				    		$sqlpre   .= "APREFOCCGC = '$Argumento' AND PRE.APREFOSEQU = FORN.APREFOSEQU AND AFORCRCCGC <> '$Argumento'";
				    }

						$respre = $db->query($sqlpre);
					  if( PEAR::isError($respre) ){
					  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
						}else{
								$rowpre = $respre->numRows();
								if( $rowpre != 0 ){
							  		$Mens     = 1;
							  		$Tipo     = 1;
							  		$Virgula  = 2;
										$Mensagem = "Fornecedor Inscrito, selecione no menu a opção Fornecedores/Inscrição/Consulta para a verificação dos dados cadastrais";
								}
						}
						$db->disconnect();
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadAcompFornecedor.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
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
	document.CadAcompFornecedor.Botao.value=valor;
	document.CadAcompFornecedor.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAcompFornecedorSelecionar.php" method="post" name="CadAcompFornecedor">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>

      <?php //echo "Desvio=$Desvio"; exit;    ?>
      <?php if ($Desvio=="CadRenovacaoCadastroIncluir" )   { ?>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Registro de Renovação
      <?php } else if  ($Desvio=="CadAnaliseCertidaoFornecedor" ) { ?>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Analisar Certidões
      <?php } else    { ?>
       <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Acompanhamento 
      <?php }   ?>
       
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left">
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
		    					ACOMPANHAMENTO DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Marque o tipo de fornecedor desejado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
										Depois, clique no fornecedor desejado para proceder o acompanhamento.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".
	        	    		 
	          	   	</p>
	          		</td>
	          	</tr>
	          		    <input type="hidden" name="Desvio" value="<?php echo $Desvio  ;?>" >
	          		    
	          		    
 	    				<?php
 	    				    Pesquisa($ItemPesquisa,$Argumento,$Palavra,4);
							if( $Botao == "Pesquisar" and $Mens == 0 ){
									# Busca os Dados da Tabela de Fornecedor de Acordo com o argumento da pesquisa #
									$db	  = Conexao();
									$sql  = "SELECT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS, A.APREFOSEQU, B.DPREFOGERA ";
									$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A ";
									$sql .= "  LEFT JOIN  SFPC.TBPREFORNECEDOR AS B ON B.APREFOSEQU = A.APREFOSEQU";
									$sql .= "  WHERE ";
									if( $Palavra == 1 ){
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC LIKE '%$Argumento' ";
											}else if( $ItemPesquisa == "CPF" ){
								   						$sql .= "A.AFORCRCCPF LIKE '%$Argumento' ";
											}else if( $ItemPesquisa == "RAZAO" ){
														$sql .= "
															A.NFORCRRAZS LIKE '$Argumento %'
															OR A.NFORCRRAZS LIKE '% $Argumento %'
															OR A.NFORCRRAZS LIKE '% $Argumento'
														";
																	//$sql .= SQL_ExpReg("A.NFORCRRAZS",$Argumento)." ";
											}
									}else{
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= "A.AFORCRCCGC LIKE '%$Argumento%' ";
											}else{
													if( $ItemPesquisa == "CPF" ){
								   						$sql .= "A.AFORCRCCPF LIKE '%$Argumento%' ";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
									   							$sql .= "A.NFORCRRAZS ILIKE '%$Argumento%' ";
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

<?php
function Resultado($Qtd,$result,$TipoForn){
	global $Desvio; 
	
	echo "<tr>\n";
	echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	if( $Qtd > 0 ){
			if( $ItemPesquisa == "CNPJ" ){
					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" >CNPJ</td>\n";
			}elseif( $ItemPesquisa == "CPF" ){
					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\"  >CPF</td>\n";
			}else{
					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\"  >CNPJ/CPF</td>\n";
			}
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\"  >RAZÃO SOCIAL/NOME</td>\n";
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" >DATA CADASTRAMENTO</td>\n";
			echo "		</tr>\n";
			$Sequencial	= "";
			while( $Linha	= $result->fetchRow() ){
					$Sequencial	   	= $Linha[1];
					$CNPJ    		= $Linha[2];
					$CPF			= $Linha[3];
					$Razao         	= $Linha[4];
					$DataInscricao 	= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
					$DataInscSicref = ($Linha[6] != "") ? substr($Linha[6], 8, 2).'/'.substr($Linha[6], 5, 2).'/'.substr($Linha[6], 0, 4).' '.substr($Linha[6], 11, 9) : ' - ';

					echo "	<tr>\n";
					if( $CNPJ != 0 ){
	     				$CNPJForm = FormataCNPJ($CNPJ);
					}else{
	     				$CPFForm = FormataCPF($CPF);
					}

					# Situação do Fornecedor é igual a Aprovado ou excluído #
					if( ($Situacao == 2) or ($Situacao == 5) ){
							if( $CNPJ <> 0 ){
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CNPJForm</td>\n";
							}else{
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CPFForm</td>\n";
							}
					}else{

						
						
						    //---------------------------------------------
						    // Define a URL de acordo com o DESVIO recebido
						    //--------------------------------------------
						    
						    if ( $Desvio=="CadRenovacaoCadastroIncluir") {
						    	$UrlRenovacao = "CadRenovacaoCadastroIncluir.php?Sequencial=$Sequencial";
						    	$Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial&Retorno=CadRenovacaoCadastroIncluir";
						    }
						    
						    else if ( $Desvio=="CadAnaliseCertidaoFornecedor") {
						    	$UrlRenovacao = "CadAnaliseCertidaoFornecedor.php?Sequencial=$Sequencial";
						    	$Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial&Retorno=CadAnaliseCertidaoFornecedor";
						     }
						    
						    else {
     						    $Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial";
						    }     	
						    
						    if ( $Desvio=="CadRenovacaoCadastroIncluir") {	
						    						    
								if( $CNPJ <> 0 ){
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Registar Pedido\" value=\"Registar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
								}else{
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Registar Pedido\" value=\"Registar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
								}
						    }
						    else if ( $Desvio=="CadAnaliseCertidaoFornecedor") { 
						    	if( $CNPJ <> 0 ){
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Analizar Pedido\" value=\"Analisar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
						    	}else{
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Analizar Pedido\" value=\"Analisar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
					    		}
						    }
						    else {
						    	if( $CNPJ <> 0 ){
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
						    	}else{
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
					    		}
						    }	
							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					}
					
					
					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataInscricao</td>\n";
					echo "	</tr>\n";
			}
	}else{
			echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
			echo "		Pesquisa sem Ocorrências.\n";
			echo "		</td>\n";
			echo "	</tr>\n";
	}
	echo "</table>\n";
}



?>


</form>
</body>
</html>




