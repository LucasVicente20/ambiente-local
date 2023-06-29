<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRegistroOcorrencia.php
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
AddMenuAcesso( '/fornecedores/CadRegistroOcorrenciaSelecionarManter.php' );
AddMenuAcesso( '/fornecedores/CadRegistroOcorrenciaAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$Sequencial   = $_POST['Sequencial'];
		$Programa     = $_POST['Programa'];
		$Ocorrencia   = $_POST['Ocorrencia'];
}else{
		$Programa   = $_GET['Programa'];
		$Sequencial = $_GET['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Voltar" ){
	  header("location: CadRegistroOcorrenciaSelecionarManter.php");
	  exit;
}elseif( $Botao == "Selecionar" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $Ocorrencia == "" ){
				if ($Mens == 1){$Mensagem .= ", ";}
				$Mens      = 1;
				$Tipo      = 2;
		  	$Mensagem .= "Ocorrência";
		}else{
				$Url = "CadRegistroOcorrenciaAlterar.php?Sequencial=$Sequencial&Ocorrencia=$Ocorrencia";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
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
	document.CadRegistroOcorrencia.Botao.value=valor;
	document.CadRegistroOcorrencia.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRegistroOcorrencia.php" method="post" name="CadRegistroOcorrencia">
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
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="3" summary="">
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
					    					CADASTRO E GESTÃO DE FORNECEDOR - OCORRÊNCIAS
					          	</td>
					        	</tr>
				  	      	<tr>
				    	      	<td class="textonormal" colspan="4">
				      	    		<p align="justify">
				        	    		Para efetuar a alteração ou exclusão de uma Ocorrência, marque a ocorrência desejada e clique no botão "Selecionar". Para retornar a tela anterior clique no botão "Voltar".
				          	   	</p>
				          		</td>
				          	</tr>
				          	<?php
			          		$db   = Conexao();
										$sql  = "SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ";
										$sql .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
										$sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ";
										$sql .= " ORDER BY A.DFOROCDATA, A.EFOROCDETA, A.CFORTOCODI";
										$res  = $db->query($sql);
									  if( PEAR::isError($res) ){
											  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $res->numRows();
												echo "			<tr>\n";
												echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
												echo "			</tr>\n";
												if( $Rows == 0 ){
														echo "	<tr>\n";
														echo "		<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\nNenhuma Ocorrência Encontrada.\n</td>\n";
														echo "	</tr>\n";
												}else{
			          						for( $i=0;$i<$Rows;$i++ ){
																$Linha     = $res->fetchRow();
				          	      			$Codigo    = $Linha[0];
				          	      			$Detalhe   = $Linha[1];
				          	      			$Data      = $Linha[2];
				          	      			$Descricao = $Linha[3];
				          	      			if( $i == 0 ){
												            echo "			<tr>\n";
																		echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">OCORRÊNCIAS</td>\n";
																		echo "			</tr>\n";
												            echo "<tr>\n";
											        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"5%\">&nbsp;</td>\n";
											        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\">DATA</td>\n";
												            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"34%\">TIPO DE OCORRÊNCIA</td>\n";
											        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
											        	    echo "</tr>\n";
									        	  	}
										            echo "<tr>\n";
									        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"center\" valign=\"top\">\n";
									      				echo "  	<input type=\"checkbox\" name=\"Ocorrencia\" value=\"$Codigo\">\n";
									        	    echo "  </td>\n";
									        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"center\" valign=\"top\">".substr($Data,8,2)."/".substr($Data,5,2)."/".substr($Data,0,4)."</td>\n";
										            echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" valign=\"top\">".strtoupper2($Descricao)."</td>\n";
									        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" valign=\"top\">$Detalhe</td>\n";
									        	    echo "</tr>\n";
					                	}
					              }
										}
			            	$db->disconnect();
			          		?>
						      	<tr>
						        	<td colspan="4" align="right">
												<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
						          	<input type="button" value="Selecionar" class="botao" onclick="javascript:enviar('Selecionar');">
						          	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
						        	</td>
			      				</tr>
			   					</table>
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
