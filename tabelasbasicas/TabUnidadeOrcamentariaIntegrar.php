<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUnidadeOrcamentariaIntegrar.php
# Autor:    Roberta Costa
# Data:     20/12/04
# Objetivo: Programa que integra a tabela de Unidade Orçamentária  com Orgão licitante
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$OrgaoLicitante      = $_POST['OrgaoLicitante'];
		$UnidadeOrcamentaria = $_POST['UnidadeOrcamentaria'];
		$CheckUnidade        = $_POST['CheckUnidade'];
		$Unidade             = $_POST['Unidade'];
}else{
		$Erro = $_GET['Erro'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUnidadeOrcamentariaIntegrar.php";
$Ano = date("Y");
if( $Botao == "Integrar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $OrgaoLicitante == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabUnidadeOrcamentariaIntegrar.OrgaoLicitante.focus();\" class=\"titulo2\">Orgão Licitante</a>";
    }
    if( $UnidadeOrcamentaria == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabUnidadeOrcamentariaIntegrar.UnidadeOrcamentaria.focus();\" class=\"titulo2\">Unidade Orçamentária</a>";
    }
		if( $Mens == 0 ){
				$Dados = explode("_",$UnidadeOrcamentaria);
				$Exercicio = $Dados[0];
				$Orgao     = $Dados[1];
				$Unidade   = $Dados[2];
				# Atualiza a tabela de Unidade Orçamentária com o Órgão Licitante Associado #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "UPDATE SFPC.TBUNIDADEORCAMENTPORTAL ";
				$sql   .= "   SET CORGLICODI = $OrgaoLicitante, TUNIDOULAT = '".date("Y-m-d H:i:s")."' ";
				$sql   .= " WHERE TUNIDOEXER = $Exercicio AND CUNIDOORGA = $Orgao ";
				$sql   .= "   AND CUNIDOCODI = $Unidade ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$OrgaoLicitante      = "";
						$UnidadeOrcamentaria = "";
						$Mens                = 1;
		    		$Tipo                = 1;
				    $Mensagem            = "Integração Realizada com Sucesso";
				}
				$db->query("END TRANSACTION");
				$db->disconnect();
		}
		$Botao = "";
}elseif( $Botao == "Retirar" ){
		if( count($Unidade) != 0 ){
				for( $i=0; $i< count($Unidade); $i++ ){
						if( $CheckUnidade[$i] == "" ){
								$Qtd++;
								$CheckUnidade[$i] = "";
								$Unidade[$Qtd-1]  = $Unidade[$i];
						}else{
								$Dados = explode("_",$CheckUnidade[$i]);
								$Exercicio = $Dados[0];
								$Orgao     = $Dados[1];
								$UnidadeOr = $Dados[2];
								$Licitante = $Dados[3];
								# Verifica se o Órgão Licitante está na tabela de Bloqueio #
								$db = Conexao();
								$sql    = "SELECT COUNT(CORGLICODI) FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT ";
								$sql   .= " WHERE CUNIDOCODI = $UnidadeOr AND CUNIDOORGA = $Orgao";
								$sql   .= "   AND CORGLICODI = $Licitante AND TUNIDOEXER = $Ano";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha = $result->fetchRow();
										$Qtd   = $Linha[0];
										if( $Qtd == 0 ){
												# Retira um Órgão Licitante da tabela de Unidade Orçamentária #
												$db->query("BEGIN TRANSACTION");
												$sql    = "UPDATE SFPC.TBUNIDADEORCAMENTPORTAL ";
												$sql   .= "   SET CORGLICODI = NULL, TUNIDOULAT = '".date("Y-m-d H:i:s")."' ";
												$sql   .= " WHERE TUNIDOEXER = $Exercicio AND CUNIDOORGA = $Orgao ";
												$sql   .= "   AND CUNIDOCODI = $UnidadeOr AND CORGLICODI = $Licitante";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
										}else{
												$Mens     = 1;
								    		$Tipo     = 1;
										    $Mensagem = "Retirada da Integração Cancelada! Este Órgão Licitante/Unidade Orçamentária esta ligado a ($Qtd) Números de Bloqueios";
										}
								}
								$db->disconnect();
						}
				}
				$CheckUnidade = array_slice($CheckUnidade,0,$Qtd);
				$Unidade      = array_slice($Unidade,0,$Qtd);
				if( count($Unidade) == 1 ){ $Unidade == ""; }
		}
		$Botao = "";
}
if( $Botao == "" ){
		$db = Conexao();
		# Verifica se existe algum Órgão Licitante Integrado para o ano de exercicio corrente #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBUNIDADEORCAMENTPORTAL";
		$sql   .= " WHERE CORGLICODI IS NOT NULL AND TUNIDOEXER = $Ano ";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha  = $result->fetchRow();
				$Existe = $Linha[0];
		}
		# Verifica se o ano corrente está Cadastrado #
		$sql    = "SELECT COUNT(*) FROM SFPC.TBUNIDADEORCAMENTPORTAL";
		$sql   .= "  WHERE TUNIDOEXER = $Ano";
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
	document.TabUnidadeOrcamentariaIntegrar.Botao.value=valor;
	document.TabUnidadeOrcamentariaIntegrar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUnidadeOrcamentariaIntegrar.php" method="post" name="TabUnidadeOrcamentariaIntegrar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Unidade Orçamentária > Integrar
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
		    					INTEGRAÇÃO DA TABELA DE UNIDADE ORÇAMENTÁRIA - ANO <?php echo date("Y");?>
		          	</td>
		        	</tr>
		        	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para fazer a integração da tabela de Unidade Orçamentária com a tabela de Órgão Licitante, escolha os campos abaixo e clique no botão "Integrar".<br>
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
			                  	<?
			                  	$db = Conexao();
			                  	if( $ExisteAno != 0 ){
					                  	# Mostra os órgãos cadastrados #
					                		$sql    = "SELECT A.CORGLICODI, A.EORGLIDESC, A.FORGLISITU ";
					                		$sql   .= "  FROM SFPC.TBORGAOLICITANTE A";
					                		$sql   .= " WHERE	A.CORGLICODI NOT IN ( SELECT B.CORGLICODI FROM SFPC.TBUNIDADEORCAMENTPORTAL B WHERE B.CORGLICODI IS NOT NULL AND B.TUNIDOEXER = ".date("Y").")";
					                		$sql   .= " ORDER BY A.EORGLIDESC ";
					                		$result = $db->query($sql);
					                		if( PEAR::isError($result) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
																			if( FindArray($Linha[0],$OrgaoLicitante) ){
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
											<td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top">Unidade Orçamentária*</td>
											<td class="textonormal">
												<select name="UnidadeOrcamentaria" class="textonormal">
			                  	<option value="">Selecione um Órgão/Unidade - Descrição...</option>
													<?
													# Mostra as Unidades Orçamentárias #
													$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
													$sql   .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
													$sql   .= " WHERE CORGLICODI IS NULL AND TUNIDOEXER = ".date("Y")."";
													$sql   .= " ORDER BY EUNIDODESC";
													$result = $db->query($sql);
													if (PEAR::isError($result)) {
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	if( FindArray($Linha[0],$UnidadeOrcamentaria) ){
																			echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]\" selected>$Linha[1]/$Linha[2] - ".str_replace("?","Ã",$Linha[3])."</option>\n";
																	}else{
																			echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]\">$Linha[1]/$Linha[2] - ".str_replace("?","Ã",$Linha[3])."</option>\n";
																	}
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
											<td class="titulo3" bgcolor="#DCEDF7" height="20">UNIDADE ORÇAMENTÁRIA</td>
										</tr>
							    	<?php
                  	# Mostra os órgãos cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT A.EORGLIDESC, B.EUNIDODESC, B.TUNIDOEXER, B.CUNIDOORGA, B.CUNIDOCODI, B.CORGLICODI ";
                		$sql   .= "  FROM SFPC.TBORGAOLICITANTE A, SFPC.TBUNIDADEORCAMENTPORTAL B ";
                		$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND B.TUNIDOEXER = ".date("Y")."";
										$sql   .= " ORDER BY EORGLIDESC, B.EUNIDODESC ";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $result->numRows();
												for( $i=0; $i< $Rows;$i++ ){
														$Linha = $result->fetchRow();
		          	      			echo "<tr>\n";
														echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";
														echo "	<input type=\"checkbox\" name=\"CheckUnidade[$c]\" value=\"$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	      			echo "	$Linha[0]\n";
		          	      			echo "	<input type=\"hidden\" name=\"Unidade[$i]\" value=\"$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" valign=\"top\">".str_replace("?","Ã",$Linha[1])."</td>\n";
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
