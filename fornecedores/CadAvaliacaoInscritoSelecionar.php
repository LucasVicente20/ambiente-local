<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAvaliacaoInscritoSelecionar.php
# Objetivo: Programa de Avaliação dos Inscritos - Selecionar
# Autor:    Rossana Lira
# Data:     16/06/04
#-------------------------------
# Alterado: Rossana Lira
# Data:     14/05/2007 - Corrigir query para não mostrar mais de uma situação
#           com a palavra exata
# Alterado: Carlos Abreu
# Data:     14/05/2007 - Corrigir problema de direcionamento
# Alterado: Ariston Cordeiro
# Data:     09/06/2008 - Novo campo Email 2
# Alterado: Ariston Cordeiro
# Data:     04/02/09 - Correções na busca
#----------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "../funcoes.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadAvaliacaoInscritoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$Critica      = $_POST['Critica'];
		$ItemPesquisa = $_POST['ItemPesquisa'];
		$Argumento		= strtoupper2(trim($_POST['Argumento']));
		$Palavra			= $_POST['Palavra'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: CadAvaliacaoInscritoSelecionar.php");
	  exit;
}

if( $Critica == 1 ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $Argumento != "" ){
				if( ($ItemPesquisa == "CNPJ") and (!SoNumeros($Argumento)) ){
			    	$Mens 		 = 1;
			    	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Avaliacao.Argumento.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}else{
						if( ($ItemPesquisa == "CPF") and (!SoNumeros($Argumento)) ){
					    	$Mens 		 = 1;
					    	$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.Avaliacao.Argumento.focus();\" class=\"titulo2\">CPF Válido</a>";
						}
				}
		}else{
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Avaliacao.Argumento.focus();\" class=\"titulo2\">Argumento da Pesquisa</a>";
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
function enviar(valor){
	document.Avaliacao.Botao.value=valor;
	document.Avaliacao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAvaliacaoInscritoSelecionar.php" method="post" name="Avaliacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição > Avaliação
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellspacing="0" cellpadding="2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					AVALIAÇÃO DAS INSCRIÇÕES DE FORNECEDORES
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Selecione o item de pesquisa desejado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
										Depois, clique no fornecedor desejado para avaliar ou complementar os dados necessários.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
	          			<input type="hidden" name="Critica" value="1">
  	      		</tr>
		        	<?php
		        	Pesquisa($ItemPesquisa,$Argumento,$Palavra,4);
							if( $Critica == 1 and $Mens == 0 ){
									$Argumento = strtoupper2($Argumento);
									# Busca os Dados da Tabela de Inscritos de Acordo com o argumento da pesquisa #
									$db	= Conexao();
									$sql  = " SELECT A.APREFOSEQU, A.APREFOCCGC, A.APREFOCCPF, A.NPREFORAZS, ";
									$sql .= "        A.DPREFOGERA, A.CPREFSCODI, B.EPREFSDESC ";
									$sql .= "   FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B ";
									$sql .= "  WHERE A.CPREFSCODI = B.CPREFSCODI AND ";
									if( $Palavra == 1 ){
											if( $ItemPesquisa == "CNPJ" ){
										   		$sql .= "(APREFOCCGC LIKE '$Argumento') ORDER BY APREFOCCGC";
											}else if( $ItemPesquisa == "CPF" ){
											   			$sql .= "(APREFOCCPF LIKE '$Argumento') ORDER BY APREFOCCPF";
											}else if( $ItemPesquisa == "RAZAO" ){
														$sql .= "
															( A.NPREFORAZS LIKE '$Argumento %'
															OR A.NPREFORAZS LIKE '% $Argumento %'
															OR A.NPREFORAZS LIKE '% $Argumento' )
														";
																	# Monta a expressão regular de pesquisa #
																	//$sql .= "". SQL_ExpReg("NPREFORAZS",$Argumento).") ORDER BY B.EPREFSDESC, A.NPREFORAZS ";
											}
									}else{
											if( $ItemPesquisa == "CNPJ" ){
							   					$sql .= " ( APREFOCCGC  LIKE '%$Argumento%') ORDER BY APREFOCCGC";
											}else{
													if( $ItemPesquisa == "CPF" ){
								   						$sql .= " ( APREFOCCPF LIKE '%$Argumento%') ORDER BY APREFOCCPF";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
												   				$sql .= " ( NPREFORAZS LIKE '%$Argumento%') ORDER BY B.EPREFSDESC, A.NPREFORAZS";
															}
													}
											}
									}
							 		$result = $db->query($sql);
									if( PEAR::isError($result) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Qtd = $result->numRows();
											echo "<tr>\n";
											echo "	<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "</tr>\n";
											if( $Qtd > 0 ){
													echo "<tr>\n";
													if( $ItemPesquisa == "CNPJ" ){
															echo "<td class=\"titulo3\" bgcolor=\"#f7f7f7\" width=\"24%\">CNPJ</td>\n";
													}elseif( $ItemPesquisa == "CPF" ){
															echo "<td class=\"titulo3\" bgcolor=\"#f7f7f7\" width=\"24%\">CPF</td>\n";
													}else{
															echo "<td class=\"titulo3\" bgcolor=\"#f7f7f7\" width=\"24%\">CNPJ/CPF</td>\n";
													}
													echo "	<td class=\"titulo3\" bgcolor=\"#f7f7f7\">RAZÃO SOCIAL/NOME</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#f7f7f7\" align=\"center\" width=\"22%\">DATA INSCRIÇÃO</td>\n";
													echo "	<td class=\"titulo3\" bgcolor=\"#f7f7f7\" align=\"center\" width=\"15%\">SITUAÇÃO</td>\n";
													echo "</tr>\n";
													$Sequencial	= "";
													while( $Linha = $result->fetchRow() ){
															$Sequencial	    = $Linha[0];
															$CNPJ    				= $Linha[1];
															$CPF						= $Linha[2];
															$Razao          = $Linha[3];
															$DataInscricao  = substr($Linha[4],8,2)."/".substr($Linha[4],5,2)."/".substr($Linha[4],0,4);$Linha[4];
															$Situacao 		  = $Linha[5];
															$DescSituacao   = $Linha[6];
															echo "<tr>\n";

															# Situação do Fornecedor Inscrito é igual a Aprovado ou excluído #
															if( ($Situacao == 2) or ($Situacao == 5) ){
																	if( $CNPJ <> 0 ){
																			echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\">\n";
																			echo substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
																			echo "</td>\n";
																	}else{
																			echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\">\n";
								             					echo substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
																			echo "</td>\n";
																	}
															}else{
																	$URL = "CadAvaliacaoInscritoManter.php?ProgramaSelecao=CadAvaliacaoInscritoSelecionar.php&Sequencial=$Sequencial";
																	if( $CNPJ <> 0 ){
																			echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\"><a href=\"$URL\" class=\"textonormal\"><u>\n";
																			echo (substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2));
																			echo "</u></a></td>\n";
																	}else{
																			echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\"><a href=\"$URL\" class=\"textonormal\"><u>\n";
																			echo (substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2));
																			echo "</u></a></td>\n";
																	}
																	if (!in_array($URL,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $URL; }
															}
															echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\">$Razao</td>\n";
															echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\" align=\"center\">$DataInscricao</td>\n";
															echo "<td valign=\"top\" bgcolor=\"#f7f7f7\" class=\"textonormal\">$DescSituacao</td>\n";
													}
													echo "</tr>\n";
										    	$db->disconnect();
											}else{
													echo "<tr>\n";
													echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"#ffffff\">Pesquisa sem Ocorrências.</td>\n";
													echo "</tr>\n";
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
