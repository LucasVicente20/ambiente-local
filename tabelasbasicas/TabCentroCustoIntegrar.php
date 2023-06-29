<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabCentroCustoIntegrar.php
# Autor:    Roberta Costa
# Data:     03/08/05
# Objetivo: Programa que integra a tabela de Centro de Custo com Orgão licitante
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207375
#--------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$OrgaoLicitante = $_POST['OrgaoLicitante'];
		$CentroCusto    = $_POST['CentroCusto'];
		$CheckUnidade   = $_POST['CheckUnidade'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$AnoExercicio = date("Y");
if( $Botao == "Integrar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $OrgaoLicitante == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabCentroCustoIntegrar.OrgaoLicitante.focus();\" class=\"titulo2\">Orgão Licitante</a>";
    }
    if( $CentroCusto == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabCentroCustoIntegrar.CentroCusto.focus();\" class=\"titulo2\">Centro de Custo</a>";
    }
		if( $Mens == 0 ){
				$Dados   = explode("_",$CentroCusto);
				$Orgao   = $Dados[0];
				$Unidade = $Dados[1];
				# Atualiza a tabela de Centro de Custo com o Órgão Licitante Associado #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "UPDATE SFPC.TBCENTROCUSTOPORTAL ";
				$sql   .= "   SET CORGLICODI = $OrgaoLicitante, TCENPOULAT = '".date("Y-m-d H:i:s")."', CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
				$sql   .= " WHERE CCENPOCORG = $Orgao AND CCENPOUNID = $Unidade";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$OrgaoLicitante = "";
						$Centrocusto    = "";
						$Mens           = 1;
		    		$Tipo           = 1;
				    $Mensagem       = "Integração Realizada com Sucesso";
				}
				$db->query("END TRANSACTION");
				$db->disconnect();
		}
		$Botao = "";
}elseif( $Botao == "Retirar" ){
		if( count($CheckUnidade) != 0 ){
				$db = Conexao();
				for( $i=0; $i< count($CheckUnidade); $i++ ){
						if( $CheckUnidade[$i] != "" ){
								$Dados   = explode("_",$CheckUnidade[$i]);
								$Orgao   = $Dados[0];
								$Unidade = $Dados[1];
								# Verifica se o Órgão Licitante está na tabela de UsuárioCentroCusto #
								$sql    = "SELECT COUNT(A.CCENPOSEQU) ";
								$sql   .= "  FROM SFPC.TBUSUARIOCENTROCUSTO A,  SFPC.TBCENTROCUSTOPORTAL B ";
								$sql   .= " WHERE B.CCENPOCORG = $Orgao AND B.CCENPOUNID = $Unidade ";
								$sql   .= "   AND A.CCENPOSEQU = B.CCENPOSEQU ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha = $result->fetchRow();
										$Qtd   = $Linha[0];
										if( $Qtd == 0 ){
												# Retira um Órgão Licitante da tabela de Centro de Custo #
												$db->query("BEGIN TRANSACTION");
												$sql    = "UPDATE SFPC.TBCENTROCUSTOPORTAL ";
												$sql   .= "   SET CORGLICODI = NULL, TCENPOULAT = '".date("Y-m-d H:i:s")."', CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
												$sql   .= " WHERE CCENPOCORG = $Orgao AND CCENPOUNID = $Unidade";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}
												$Mens     = 1;
								    		$Tipo     = 1;
										    $Mensagem = "Retirada da Integração Efetuada com Sucesso";
										}else{
												$Mens     = 1;
								    		$Tipo     = 1;
										    $Mensagem = "Retirada da Integração Cancelada! Este Órgão Licitante/Centro de Custo esta ligado a ($Qtd) Usuário(s)";
										}
								}
						}
				}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();
				$Check	 = array_slice($CheckUnidade,0,$Qtd);
		}
		$Botao = "";
}
if( $Botao == "" ){
		# Verifica se existe algum Órgão Licitante Integrado para o ano de exercicio corrente #
		$db     = Conexao();
		$sql    = "SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL";
		$sql   .= " WHERE CORGLICODI IS NOT NULL AND ACENPOANOE = $AnoExercicio ";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha  = $result->fetchRow();
				$Existe = $Linha[0];
		}
		# Verifica se o ano corrente está Cadastrado #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL";
		$sql   .= "  WHERE ACENPOANOE = $AnoExercicio";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha     = $result->fetchRow();
				$ExisteAno = $Linha[0];
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
function enviar(valor){
	document.TabCentroCustoIntegrar.Botao.value=valor;
	document.TabCentroCustoIntegrar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabCentroCustoIntegrar.php" method="post" name="TabCentroCustoIntegrar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Estoques > Centro Custo > Integrar
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
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INTEGRAÇÃO DA TABELA DE CENTRO DE CUSTO - ANO <?php echo date("Y");?>
		          	</td>
		        	</tr>
		        	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para fazer a integração da tabela de Centro de Custo com a tabela de Órgão Licitante, escolha os campos abaixo e clique no botão "Integrar".<br>
	        	    		Quando houver alguma integração feita, será exibida uma lista, para retirar um ou mais itens dessa lista marque o(s) item(s) desejados e clique no botão "Retirar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão Licitante*</td>
											<td class="textonormal">
			                  <select name="OrgaoLicitante" class="textonormal">
			                  	<option value="">Selecione um Órgão Licitante...</option>
			                  	<?php 
			                  	$db = Conexao();
			                  	if( $ExisteAno != 0 ){
					                  	# Mostra os órgãos cadastrados #
					                		$sql    = "SELECT A.CORGLICODI, A.EORGLIDESC, A.FORGLISITU ";
					                		$sql   .= "  FROM SFPC.TBORGAOLICITANTE A";
					                		//$sql   .= " WHERE	A.CORGLICODI NOT IN ( SELECT B.CORGLICODI FROM SFPC.TBCENTROCUSTOPORTAL B WHERE B.CORGLICODI IS NOT NULL AND B.ACENPOANOE = $AnoExercicio )";
					                		$sql   .= " ORDER BY A.EORGLIDESC ";
					                		$result = $db->query($sql);
					                		if( PEAR::isError($result) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
																			if( $Linha[0] == $OrgaoLicitante ){
																					echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
																			}else{
									          	      			echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
																			}
																	}
								              }
								          }
			      	            ?>
			                  </select>
											</td>
	          	    	</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo*</td>
											<td class="textonormal">
												<select name="CentroCusto" class="textonormal">
			                  	<option value="">Selecione um Unidade - Centro de Custo...</option>
													<?php 
													# Mostra as Unidades Orçamentárias #
													$sql    = "SELECT A.CCENPOCORG, A.CCENPOUNID, B.EUNIDODESC ";
													$sql   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBUNIDADEORCAMENTPORTAL B";
													$sql   .= " WHERE A.CORGLICODI IS NULL AND A.ACENPOANOE = $AnoExercicio ";
													$sql   .= "   AND A.CCENPOCORG = B.CUNIDOORGA AND A.CCENPOUNID = B.CUNIDOCODI ";
													$sql   .= "   AND B.TUNIDOEXER = $AnoExercicio ORDER BY B.EUNIDODESC";
													$result = $db->query($sql);
													if (PEAR::isError($result)) {
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$DescUnidadeAntes = "";
															while( $Linha = $result->fetchRow() ){
																	$Orgao       = $Linha[0];
																	$Unidade     = $Linha[1];
																	$DescUnidade = $Linha[2];
																	if( $DescUnidadeAntes != $DescUnidade ){
																			if( $CentroCusto == "$Orgao_$Unidade" ){
																					echo "<option value=\"".$Orgao."_".$Unidade."\" selected>$DescUnidade</option>\n";
																			}else{
																					echo "<option value=\"".$Orgao."_".$Unidade."\">$DescUnidade</option>\n";
																			}
																	}
																	$DescUnidadeAntes = $DescUnidade;
															}
													}
													$db->disconnect();
													?>
												</select>
											</td>
	          	    	</tr>
									</table>
								</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
									<input type="hidden" name="Existe" value="<?php echo $Existe; ?>">
			            <input type="button" value="Integrar" class="botao" onclick="javascript:enviar('Integrar');">
			            <input type="hidden" name="Botao" value="">
		          	</td>
		        	</tr>
		        	<?php if( $Existe != 0 ){ ?>
		        	<tr>
	  	        	<td>
	    	      		<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
										<tr>
											<td class="titulo3" bgcolor="#BFDAF2" height="20" colspan="3" align="center">ÓRGÃOS INTEGRADOS</td>
										</tr>
										<tr>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="5%">&nbsp;</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="45%">ÓRGÃO LICITANTE</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20">CENTRO DE CUSTO</td>
										</tr>
							    	<?php
                  	# Mostra os órgãos cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT DISTINCT B.CCENPOCORG, B.CCENPOUNID, A.EORGLIDESC, C.EUNIDODESC ";
                		$sql   .= "  FROM SFPC.TBORGAOLICITANTE A, SFPC.TBCENTROCUSTOPORTAL B, SFPC.TBUNIDADEORCAMENTPORTAL C ";
                		$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND B.ACENPOANOE = $AnoExercicio";
										$sql   .= "   AND B.CCENPOCORG = C.CUNIDOORGA AND B.CCENPOUNID = C.CUNIDOCODI AND C.TUNIDOEXER = $AnoExercicio";
										$sql   .= " ORDER BY A.EORGLIDESC";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $result->numRows();
												for( $i=0; $i< $Rows;$i++ ){
														$Linha = $result->fetchRow();
		          	      			echo "<tr>\n";
														echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";
														echo "		<input type=\"checkbox\" name=\"CheckUnidade[]\" value=\"".$Linha[0]."_".$Linha[1]."\">\n";
														echo "	</td>\n";
		          	      			echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	      			echo "		$Linha[2]\n";
														echo "	</td>\n";
		          	      			echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" valign=\"top\">$Linha[3]</td>\n";
		          	      			echo "</tr>\n";
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
									</table>
								</td>
		        	</tr>
							<tr>
	  	        	<td>
	    	      		<table border="0" cellpadding="3" cellspacing="0" class="textonormal" width="100%" summary="" >
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Total de Órgãos Integrados</td>
											<td class="textonormal"><?php echo $i; ?></td>
				     	    	</tr>
									</table>
								</td>
		        	</tr>
			      	<tr>
				  			<td class="textonormal" align="right">
			        	  <input type="button" value="Retirar" class="botao" onclick="javascript:enviar('Retirar');">
			        	</td>
			      	</tr>
		        	<?php } ?>
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
