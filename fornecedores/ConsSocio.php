<?php
#-----------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsSocio.php
# Autor:    Pitang TI
# Data:     20/04/18ł
# Objetivo: Programa que consulta todos os sócios que estão cadastrados e os 
#           fornecedores associados a estes
#           CR #181348 - [FORNECEDORES] Consulta de sócios - Nova funcionalidade
#------------------------------------------------------------------------------------------------------------------------------------------------
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
		$Argumento		= trim($_POST['Argumento']);//strtoupper2(
		$Palavra		= $_POST['Palavra'];
		$Desvio    		= $_POST['Desvio'];
}else{
		$Mens      		= $_GET['Mens'];
		$Mensagem  		= $_GET['Mensagem'];
		$Tipo	  		= $_GET['Tipo'];
		$Desvio    		= $_GET['Desvio'];
}


# Atribuir $_SESSION['AcompFornecedorDesvio'] com o desvio de chamada
	$_SESSION['AcompFornecedorDesvio']="";
	$_SESSION['origem'] = "ConsSocio";
 



# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: ConsSocio.php");
	  exit;
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
<form action="ConsSocio.php" method="post" name="CadAcompFornecedor">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>

       <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Consulta de Sócios 

       
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
		    					CONSULTA DE SÓCIOS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Para pesquisar uma pessoa registrada como sócio cadastrado no SICREF, use como argumento da pesquisa o nome ou o CPF/CNPJ. Para pesquisar pelo nome, altere a opção no campo Pesquisa para "Nome". 
							<br>Para pesquisar pelo CPF ou CNPJ, altere a opção no campo Pesquisa para "CPF/CNPJ". O resultado será diferente caso o nome possua caracteres especiais, como acentos. Não colocar pontos, barra ou traços no CPF/CNPJ.
	        	    		 
	          	   	</p>
	          		</td>
	          	</tr>
	          		    <input type="hidden" name="Desvio" value="<?php echo $Desvio  ;?>" >

         		    
 	    				<?php
 	    				    PesquisaSocio($ItemPesquisa,$Argumento,$Palavra,4);
							if( $Botao == "Pesquisar" and $Mens == 0 ){
									# Busca os Dados da Tabela de Fornecedor de Acordo com o argumento da pesquisa #
									$db	  = Conexao();
									$sql  = "SELECT DISTINCT fc.aforcrsequ, sf.nsofornome, sf.asoforcada,fc.nforcrrazs,fc.aforcrccgc,fc.aforcrccpf";
									$sql .= " FROM sfpc.tbsociofornecedor sf ";
									$sql .= " INNER JOIN sfpc.tbfornecedorcredenciado fc ON sf.aforcrsequ = fc.aforcrsequ ";
									$sql .= "  WHERE ";
									if( $Palavra == 1 ){

										if($ItemPesquisa=='CNPJCPF'){
											//$sql .= "fc.AFORCRCCGC LIKE '$Argumento' OR ";
											//$sql .= "fc.AFORCRCCPF LIKE '$Argumento' OR ";
											$sql .= " sf.asoforcada LIKE '$Argumento' ";
										}else{
											$sql .= " LOWER(sf.nsofornome) LIKE '".strtolower($Argumento)."' ";
										}

										

									}else{

										if($ItemPesquisa=='CNPJCPF'){
											//$sql .= "fc.AFORCRCCGC LIKE '%$Argumento%' OR ";
											//$sql .= "fc.AFORCRCCPF LIKE '%$Argumento%' OR ";
											$sql .= " sf.asoforcada LIKE '%$Argumento%' ";
										}else{
											$sql .= " LOWER(sf.nsofornome) LIKE '%".strtolower($Argumento)."%' ";
										}

									}
						 			$sql     .= " order by sf.nsofornome asc, fc.nforcrrazs asc ";
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
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\"  >NOME DO SÓCIO</td>\n";
			echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\"  align=\"center\">CNPJ/CPF</td>\n";
			echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" >FORNECEDOR ASSOCIADO</td>\n";
			echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\"   align=\"center\">CNPJ/CPF</td>\n";
			echo "		</tr>\n";
			$Sequencial	= "";
			while( $Linha	= $result->fetchRow() ){
					$Sequencial	   	= $Linha[0];
					$NomeSocio      = $Linha[1];
					$CPFCNPJSocio   = $Linha[2];
					$CNPJ    		= $Linha[4];
					$CPF			= $Linha[5];

					$Fornecedor     = $Linha[3];
					$DataInscricao 	= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
					$DataInscSicref = ($Linha[6] != "") ? substr($Linha[6], 8, 2).'/'.substr($Linha[6], 5, 2).'/'.substr($Linha[6], 0, 4).' '.substr($Linha[6], 11, 9) : ' - ';

					echo "	<tr>\n";
					if( $CNPJ != 0 ){
	     				$CNPJForm = FormataCNPJ($CNPJ);
					}else{
	     				$CPFForm = FormataCPF($CPF);
					}

					

					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NomeSocio</td>\n";
					echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">".FormataCpfCnpj($CPFCNPJSocio)."</td>\n";


					# Situação do Fornecedor é igual a Aprovado ou excluído #
					if( ($Situacao == 2) or ($Situacao == 5) ){
							
							echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"left\">$Fornecedor</td>\n";
							
							if( $CNPJ <> 0 ){
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CNPJForm</td>\n";
							}else{
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CPFForm</td>\n";
							}

					}else{

						
						
						    //---------------------------------------------
						    // Define a URL de acordo com o DESVIO recebido
						    //--------------------------------------------
						    
						    /*if ( $Desvio=="CadRenovacaoCadastroIncluir") {
						    	$UrlRenovacao = "CadRenovacaoCadastroIncluir.php?Sequencial=$Sequencial";
						    	$Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial&Retorno=CadRenovacaoCadastroIncluir";
						    }
						    
						    else if ( $Desvio=="CadAnaliseCertidaoFornecedor") {
						    	$UrlRenovacao = "CadAnaliseCertidaoFornecedor.php?Sequencial=$Sequencial";
						    	$Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial&Retorno=CadAnaliseCertidaoFornecedor";
						     }
						    
						    else {*/
     						    $Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial";
						    //}     	
						    
						    /*if ( $Desvio=="CadRenovacaoCadastroIncluir") {	
						    	
								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"left\"><a href=\"$UrlRenovacao\"><font color=\"#000000\">$Fornecedor</font></a></td>\n";					    
								
								if( $CNPJ <> 0 ){
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Registar Pedido\" value=\"Registar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
								}else{
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Registar Pedido\" value=\"Registar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
								}
						    }
						    else if ( $Desvio=="CadAnaliseCertidaoFornecedor") { 

								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"left\"><a href=\"$UrlRenovacao\"><font color=\"#000000\">$Fornecedor</font></a></td>\n";

						    	if( $CNPJ <> 0 ){
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Analizar Pedido\" value=\"Analisar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
						    	}else{
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$UrlRenovacao\"><font color=\"#000000\"><input type=\"button\" name=\"Analizar Pedido\" value=\"Analisar Pedido\" class=\"botao\" ></font></a>&nbsp;&nbsp;<a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
					    		}
						    }
						    else {*/

								echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"left\"><a href=\"$Url\"><font color=\"#000000\">$Fornecedor</font></a></td>\n";

						    	if( $CNPJ <> 0 ){
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
						    	}else{
						    		echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
					    		}
						    //}	
							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					}

					echo "	</tr>\n";
			}
	}else{
			echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
			echo "		Nome do fornecedor não encontrado no SICREF.\n";
			echo "		</td>\n";
			echo "	</tr>\n";
	}
	echo "</table>\n";
}



?>


</form>
</body>
</html>




