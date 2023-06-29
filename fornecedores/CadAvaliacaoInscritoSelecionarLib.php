<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAvaliacaoInscritoSelecionarLib.php
# Autor:    Rossana Lira
# Data:     27/08/04
# Objetivo: Programa de Avaliação dos Inscritos - Selecionar (Liberação)
# Alterado: Rossana Lira
# Data:     14/05/2007 - Corrigir query para não mostrar mais de uma situação 
#           com a palavra exata
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
AddMenuAcesso( '/fornecedores/CadAvaliacaoInscritoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        	= $_POST['Botao'];
		$Critica      	= $_POST['Critica'];
		$ItemPesquisa 	= $_POST['ItemPesquisa'];
		$Argumento			= strtoupper2(trim($_POST['Argumento']));
		$Palavra				= $_POST['Palavra'];
} else {
		$Mens         		= $_GET['Mens'];
		$Mensagem     		= $_GET['Mensagem'];
		$Tipo			     		= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: CadAvaliacaoInscritoSelecionarLib.php");
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
<!--
function enviar(valor){
	document.Avaliacao.Botao.value=valor;
	document.Avaliacao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAvaliacaoInscritoSelecionarLib.php" method="post" name="Avaliacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição > Liberação da Avaliação
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					AVALIAÇÃO DAS INSCRIÇÕES DE FORNECEDORES - LIBERAÇÃO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Selecione o item de pesquisa desejado, preencha o argumento da pesquisa e clique no botão "Pesquisar".
										Depois, clique no fornecedor desejado para proceder a avaliação ou complementação dos dados necessários.<br>
	        	    		Para limpar a pesquisa, clique no botão "Limpar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
    	      		<td class="textonormal" colspan="4">
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
    	      		<td align="right" colspan="4">
  	      				<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
  	      				<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
            			<input type="hidden" name="Botao" value="">
									<input type="hidden" name="Critica" value="1">
								</td>
        			</tr>
							<?php
							if( ($Critica == 1) and ($Mens == 0) ){
									$db	= Conexao();
									$Argumento = strtoupper2($Argumento);
									# Busca os Dados da Tabela de Inscritos de Acordo com o argumento da pesquisa #
									if( $Palavra == 1 ){
											$sql  = " SELECT A.APREFOSEQU, A.APREFOCCGC, A.APREFOCCPF, A.NPREFORAZS, A.DPREFOGERA, ";
											$sql .= " A.CPREFSCODI, B.EPREFSDESC FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B ";
											if( $ItemPesquisa == "CNPJ" ){
										 			$sql .= " WHERE APREFOCCGC  LIKE '$Argumento' AND A.CPREFSCODI = B.CPREFSCODI ORDER BY APREFOCCGC";
											}else{
													if( $ItemPesquisa == "CPF" ){
												   		$sql .= " WHERE APREFOCCPF LIKE '$Argumento' AND A.CPREFSCODI = B.CPREFSCODI ORDER BY APREFOCCPF";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
																	# Monta a expressão regular de pesquisa #
																	$sql .= " WHERE (". SQL_ExpReg("NPREFORAZS",$Argumento).") AND A.CPREFSCODI = B.CPREFSCODI ORDER BY B.EPREFSDESC, A.NPREFORAZS";
															}
													}
											}
									}else{
											$sql  = " SELECT A.APREFOSEQU, A.APREFOCCGC, A.APREFOCCPF, A.NPREFORAZS, A.DPREFOGERA, ";
											$sql .= " A.CPREFSCODI, B.EPREFSDESC FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B ";
											if( $ItemPesquisa == "CNPJ" ) {
											 		$sql .= " WHERE APREFOCCGC  LIKE '%$Argumento%' AND A.CPREFSCODI = B.CPREFSCODI ORDER BY APREFOCCGC";
											}else{
													if( $ItemPesquisa == "CPF" ){
												   		$sql .= " WHERE APREFOCCPF LIKE '%$Argumento%' AND A.CPREFSCODI = B.CPREFSCODI ORDER BY APREFOCCPF";
													}else{
															if( $ItemPesquisa == "RAZAO" ){
													   			$sql .= " WHERE NPREFORAZS LIKE '$Argumento%' AND A.CPREFSCODI = B.CPREFSCODI ORDER BY B.EPREFSDESC, A.NPREFORAZS";
															}
													}
											}
									}
									$result = $db->query($sql);
									if( PEAR::isError($result) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}
									$Qtd = $result->numRows();
									echo "			<tr>\n";
									echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
									echo "			</tr>\n";
									if( $Qtd > 0 ){
											echo "		<tr>\n";
											if( $ItemPesquisa == "CNPJ" ){
													echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\">CNPJ</td>\n";
											}elseif( $ItemPesquisa == "CPF" ){
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">CPF</td>\n";
											}else{
													echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"24%\">CNPJ/CPF</td>\n";
											}
											echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\">RAZÃO SOCIAL/NOME</td>\n";
											echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" width=\"22%\">DATA INSCRIÇÃO</td>\n";
											echo "			<td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" width=\"15%\">SITUAÇÃO</td>\n";
											echo "		</tr>\n";
											$Sequencial	= "";
											while( $Linha = $result->fetchRow() ){
													$Sequencial	= $Linha[0];
													$CNPJ    			 = $Linha[1];
													$CPF					 = $Linha[2];
													$Razao         = $Linha[3];
													$DataInscricao = substr($Linha[4],8,2)."/".substr($Linha[4],5,2)."/".substr($Linha[4],0,4);$Linha[4];
													$Situacao 		 = $Linha[5];
													$DescSituacao  = $Linha[6];
													echo "	<tr>\n";
													if( $CNPJ <> 0 ){
															$CNPJForm = $CNPJ;
															$CNPJForm = (substr($CNPJForm,0,2).".".substr($CNPJForm,2,3).".".substr($CNPJForm,5,3)."/".substr($CNPJForm,8,4)."-".substr($CNPJForm,12,2));
													}else{
															$CPFForm = $CPF;
															$CPFForm = (substr($CPFForm,0,3).".".substr($CPFForm,3,3).".".substr($CPFForm,6,3)."-".substr($CPFForm,9,2));
													}

													# Situação do Fornecedor Inscrito é igual a Aprovado ou excluído #
													if( ( $Situacao == 2 ) or ( $Situacao == 5 ) ){
															if( $CNPJ <> 0 ){
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CNPJForm</td>\n";
															}else{
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$CPFForm</td>\n";
															}
													}else{
															$ProgramaSelecao = urlencode("CadAvaliacaoInscritoSelecionarLib.php");
															$Url = "CadAvaliacaoInscritoManter.php?ProgramaSelecao=$ProgramaSelecao&Sequencial=$Sequencial";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															if( $CNPJ <> 0 ){
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CNPJForm</font></td>\n";
															}else{
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$CPFForm</font></td>\n";
															}
													}
													echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Razao</td>\n";
													echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$DataInscricao</td>\n";
													echo "		<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSituacao</td>\n";
											}
											echo "</tr>\n";
									  	$db->disconnect();
									}else{
											echo "	<tr>\n";
											echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
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
