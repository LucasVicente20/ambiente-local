<?php
# --------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotSubelementoIntegrar.php
# Autor:    Roberta Costa
# Data:     20/12/04
# Objetivo: Programa que integra a tabela de Subelemento de Despesa com Grupo
# OBS.:     Tabulação 2 espaços
# --------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		25/10/2018
# Objetivo: Tarefa Redmine 73662
# --------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/tabelasbasicas/RotSubelementoCarregar.php' );
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao            = $_POST['Botao'];
		$Carrega          = $_POST['Carrega'];
		$Subelemento      = $_POST['Subelemento'];
		$TipoGrupo        = $_POST['TipoGrupo'];
		$TipoMaterial     = $_POST['TipoMaterial'];
		$Grupo            = $_POST['Grupo'];
		$CheckSubelemento = $_POST['CheckSubelemento'];
}else{
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Mensagem = $_GET['Mensagem'];
		$Carrega  = $_GET['Carrega'];
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
# Ano Atual do Exercicio #
$AnoExercicio = AnoExercicio();
$AnoExercicio = 2005;
if( $Botao == "Integrar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $Subelemento == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.RotSubelementoIntegrar.Subelemento.focus();\" class=\"titulo2\">Subelemento</a>";
    }
    if( $TipoGrupo == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.RotSubelementoIntegrar.TipoGrupo.focus();\" class=\"titulo2\">Tipo de Grupo</a>";
    }
    if( $TipoGrupo == "M" and $TipoMaterial == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.RotSubelementoIntegrar.TipoMaterial.focus();\" class=\"titulo2\">Tipo de Material</a>";
    }
    if( $Grupo == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.RotSubelementoIntegrar.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
    }
		if( $Mens == 0 ){
				$db   = Conexao();
				$sql  = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOSUBELEMENTO ";
				$sql .= " WHERE CGRUMSCODI = $Grupo AND AGRUSUEXER = $AnoExercicio ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $res->fetchRow();
						$Qtd   = $Linha[0];
						if( $Qtd != 0 ){
								$Mens     = 1;
				    		$Tipo     = 1;
						    $Mensagem = "Integração para o Grupo já Cadastrada";
						}else{
								$Dados     = explode("_",$Subelemento);
								$Elemento1 = $Dados[0];
								$Elemento2 = $Dados[1];
								$Elemento3 = $Dados[2];
								$Elemento4 = $Dados[3];
								$ElementoM = $Dados[4];
								# Insere na tabela de Subelemento Associado com o Grupo #
								$db->query("BEGIN TRANSACTION");
								$sql    = "INSERT INTO SFPC.TBGRUPOSUBELEMENTO ( ";
								$sql   .= "CGRUMSCODI, AGRUSUEXER, CGRUSUELE1, CGRUSUELE2, ";
								$sql   .= "CGRUSUELE3, CGRUSUELE4, CGRUSUSUBE, TGRUSUULAT ";
								$sql   .= " ) VALUES ( ";
								$sql   .= "$Grupo, $AnoExercicio, $Elemento1, $Elemento2, ";
								$sql   .= "$Elemento3, $Elemento4, $ElementoM, '".date("Y-m-d H:i:s")."' )";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$Subelemento  = "";
										$TipoGrupo    = "";
										$TipoMaterial = "";
										$Grupo        = "";
										$Mens         = 1;
						    		$Tipo         = 1;
								    $Mensagem     = "Integração Realizada com Sucesso";
								}
								$db->query("END TRANSACTION");
						}
				}
		}
		$db->disconnect();
		$Botao = "";
}elseif( $Botao == "Retirar" ){
		if( count($CheckSubelemento) == 0 ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "Informe pelo menos um item para ser Retirado";
		}else{
				for( $i=0; $i< count($CheckSubelemento); $i++ ){
						$Dados = explode("_",$CheckSubelemento[$i]);
						$Elemento1 = $Dados[0];
						$Elemento2 = $Dados[1];
						$Elemento3 = $Dados[2];
						$Elemento4 = $Dados[3];
						$ElementoM = $Dados[4];
						$Grupo     = $Dados[5];
						# Verifica se o Órgão Licitante está na tabela de Bloqueio #
						$db = Conexao();
						$db->query("BEGIN TRANSACTION");
						$sql    = "DELETE FROM SFPC.TBGRUPOSUBELEMENTO ";
        		$sql   .= " WHERE AGRUSUEXER = $AnoExercicio AND CGRUMSCODI = $Grupo ";
        		$sql   .= "   AND CGRUSUELE1 = $Elemento1 AND CGRUSUELE2 = $Elemento2 ";
        		$sql   .= "   AND CGRUSUELE3 = $Elemento3 AND CGRUSUELE4 = $Elemento4 ";
        		$sql   .= "   AND CGRUSUSUBE = $ElementoM ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$db->query("COMMIT");
								$Subelemento  = "";
								$TipoGrupo    = "";
								$TipoMaterial = "";
								$Grupo        = "";
								$Mens         = 1;
				    		$Tipo         = 1;
						    $Mensagem     = "Retirada da Integração efetuada com Sucesso";
						}
						$db->query("END TRANSACTION");
						$db->disconnect();
				}
		}
		$Botao = "";
}
if( $Botao == "" ){
		# Carrega o array de Subelementos no Oracle #
		if( $Carrega == "" ){
				$Url = "tabelasbasicas/RotSubelementoCarregar.php";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				Redireciona($Url);
		}else{
      	if( $_SESSION['CarregArray'] == "" ){
          	LerSubelemento();
				}
		}
		# Verifica se existe algum Subelemento Integrado para o ano de exercicio corrente #
		$db     = Conexao();
		$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOSUBELEMENTO ";
		$sql   .= " WHERE AGRUSUEXER = $AnoExercicio ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha  = $result->fetchRow();
				$Existe =  $Linha[0];
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
function submeter(){
	if( document.RotSubelementoIntegrar.TipoGrupo.value == 'S' ){
		document.RotSubelementoIntegrar.TipoMaterial.value = '';
	}
	document.RotSubelementoIntegrar.Grupo.value = '';
	document.RotSubelementoIntegrar.submit();
}
function remeter(){
	document.RotSubelementoIntegrar.Grupo.value = '';
	document.RotSubelementoIntegrar.submit();
}
function enviar(valor){
	document.RotSubelementoIntegrar.Botao.value=valor;
	document.RotSubelementoIntegrar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotSubelementoIntegrar.php" method="post" name="RotSubelementoIntegrar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Integração
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
		    					INTEGRAÇÃO DA TABELA DE SUBELEMENTO - ANO <?php echo $AnoExercicio;?>
		          	</td>
		        	</tr>
		        	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para fazer a integração da tabela de Subelemento de Despesa com a tabela de Grupo, escolha os campos abaixo e clique no botão "Integrar".<br>
	        	    		Quando houver alguma integração feita, será exibida uma lista, para retirar um ou mais itens dessa lista marque o(s) item(s) desejados e clique no botão "Retirar".
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Subelemento*</td>
											<td class="textonormal">
			                  <select name="Subelemento" class="textonormal">
			                  	<option value="">Selecione um Subelemento...</option>
			                  	<?php 
			                  	if( $Carrega == "S" ){
					                  	$ElementoAntes = "";
					                  	for( $i=0;$i<count($_SESSION['SubEle']);$i++ ){
									          			$Sub = explode("_",$_SESSION['SubEle'][$i]);
									   							$Elemento1 = $Sub[0];
																	$Elemento2 = $Sub[1];
																	$Elemento3 = $Sub[2];
																	$Elemento4 = $Sub[3];
																	$ElementoM = $Sub[4];
																	$SubNome   = $Sub[5];
																	# Procura pelos Subelemento já Cadastrados #
																	$db     = Conexao();
																	$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOSUBELEMENTO ";
							                		$sql   .= " WHERE AGRUSUEXER = $AnoExercicio AND CGRUSUELE1 = $Elemento1 ";
							                		$sql   .= "   AND CGRUSUELE2 = $Elemento2 AND CGRUSUELE3 = $Elemento3 ";
							                		$sql   .= "   AND CGRUSUELE4 = $Elemento4 AND CGRUSUSUBE = $ElementoM ";
																	$result = $db->query($sql);
							                		if( PEAR::isError($result) ){
																	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																			$Linha = $result->fetchRow();
																			$Qtd   =  $Linha[0];
																			if( $Qtd == 0 ){
																					$Elemento = "".$Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."";
																					if( $Elemento != $ElementoAntes ){
																							echo "<option value=\"\">ELEMENTO DE DESPESA: ".$Elemento1.".".$Elemento2.".".$Elemento3.".".$Elemento4."</option>\n";
											                  	}
																					$Numeracao = "".$Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."_".$ElementoM."";
																					if( $Subelemento == $Numeracao ){
									                  					echo "<option value=\"$Numeracao\" selected>&nbsp;&nbsp;&nbsp;&nbsp;".substr($SubNome,0,55)."</option>\n";
									                  			}else{
									                  					echo "<option value=\"$Numeracao\">&nbsp;&nbsp;&nbsp;&nbsp;".substr($SubNome,0,55)."</option>\n";
									                  			}
									                  			$ElementoAntes = $Elemento;
									                  	}
							                  	}
							                  	$db->disconnect();
							                }
					                }
			      	            ?>
			                  </select>
											</td>
	          	    	</tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo de Grupo*</td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoGrupo" value="M" onClick="submeter();" <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> >Material
			              		<input type="radio" name="TipoGrupo" value="S" onClick="submeter();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> >Servico
			              	</td>
			            	</tr>
				            <?php if( $TipoGrupo == "M" ){ ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo de Material*</td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoMaterial" value="C" onClick="remeter();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
			              		<input type="radio" name="TipoMaterial" value="P" onClick="remeter();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
			              	</td>
			            	</tr>
			            	<?php } ?>
			            	<tr>
			              	<td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
			              	<td class="textonormal">
			              		<select name="Grupo" class="textonormal">
			              			<option value="">Selecione um Grupo...</option>
				              		<?php
				              		if( ( $TipoGrupo == "M" and $TipoMaterial != "" ) or $TipoGrupo == "S" ){
						              		$db   = Conexao();
		  												$sql  = "SELECT CGRUMSCODI, EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
															$sql .= " WHERE FGRUMSSITU = 'A' AND FGRUMSTIPO = '$TipoGrupo' ";
  														if( $TipoGrupo == "M" and $TipoMaterial != "" ){
  																$sql .= "   AND FGRUMSTIPM = '$TipoMaterial'";
  														}
		  												$sql .= " AND CGRUMSCODI NOT IN ( SELECT CGRUMSCODI FROM SFPC.TBGRUPOSUBELEMENTO WHERE AGRUSUEXER = $AnoExercicio ) ";
		  												$sql .= " ORDER BY EGRUMSDESC";
		  												$res  = $db->query($sql);
														  if( PEAR::isError($res) ){
																  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $res->fetchRow() ){
							          	      			$DescGrupo = substr($Linha[1],0,60);
							          	      			if( $Linha[0] == $Grupo ){
												    	      			echo"<option value=\"$Linha[0]\" selected>$DescGrupo</option>\n";
										      	      		}else{
												    	      			echo"<option value=\"$Linha[0]\">$DescGrupo</option>\n";
										      	      		}
							                  	}
															}
					  	              	$db->disconnect();
					  	            }
				              		?>
			              		</select>
			              	</td>
			            	</tr>
									</table>
								</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
									<input type="hidden" name="Carrega" value="<?php echo $Carrega; ?>">
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
											<td class="menu" bgcolor="#75ADE6" height="20" colspan="3" align="center">GRUPOS INTEGRADOS</td>
										</tr>
							    	<?php
                  	# Mostra os grupos cadastrados #
                		$db     = Conexao();
                		$sql    = "SELECT A.CGRUSUELE1, A.CGRUSUELE2, A.CGRUSUELE3, A.CGRUSUELE4, ";
                		$sql   .= "       A.CGRUSUSUBE, B.EGRUMSDESC, B.FGRUMSTIPO, B.FGRUMSTIPM, ";
                		$sql   .= "       A.CGRUMSCODI ";
                		$sql   .= "  FROM SFPC.TBGRUPOSUBELEMENTO A, SFPC.TBGRUPOMATERIALSERVICO B ";
                		$sql   .= " WHERE A.CGRUMSCODI = B.CGRUMSCODI AND A.AGRUSUEXER = $AnoExercicio ";
										$sql   .= " ORDER BY B.FGRUMSTIPO, B.FGRUMSTIPM ";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows       = $result->numRows();
												$TipoGAntes = "";
												for( $i=0; $i< $Rows;$i++ ){
														$Linha          = $result->fetchRow();
		          	      			$Elemento1      = $Linha[0];
		          	      			$Elemento2      = $Linha[1];
		          	      			$Elemento3      = $Linha[2];
		          	      			$Elemento4      = $Linha[3];
		          	      			$ElementoM      = $Linha[4];
		          	      			$GrupoDescricao = $Linha[5];
		          	      			$TipoG          = $Linha[6];
		          	      			$TipoM          = $Linha[7];
		          	      			$Grupo          = $Linha[8];
				                  	if( $TipoG == "M" ){ $DescTipoGrupo = "MATERIAL"; }else{ $DescTipoGrupo = "SERVIÇO"; }
		          	      			if( $TipoM == "C" ){ $DescTipoMaterial = "CONSUMO"; }else{ $DescTipoMaterial = "PERMANENTE"; }
														if( $TipoG != $TipoGAntes ){
																echo "<tr>\n";
																echo "	<td class=\"textoabason\" bgcolor=\"#BFDAF2\" height=\"20\" align=\"center\" colspan=\"3\">$DescTipoGrupo</td>\n";
																echo "</tr>\n";
				          	      			if( $TipoG == "S" ){
																		echo "<tr>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"5%\">&nbsp;</td>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"45%\">GRUPO</td>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\">SUBELEMENTO DE DESPESA</td>\n";
																		echo "</tr>\n";
																}
														}
		          	      			if( $TipoG == "M" ){
		          	      					if( $TipoM != $TipoMAntes ){
						          	      			echo "<tr>\n";
																		echo "	<td class=\"titulo2\" bgcolor=\"#FFFFFF\" height=\"20\" colspan=\"3\">$DescTipoMaterial</td>\n";
																		echo "</tr>\n";
						          	      			echo "<tr>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"5%\">&nbsp;</td>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"45%\">GRUPO</td>\n";
																		echo "	<td class=\"titulo3\" bgcolor=\"#DCEDF7\" height=\"20\">SUBELEMENTO DE DESPESA</td>\n";
																		echo "</tr>\n";
																}
														}
		          	      			echo "<tr>\n";
														echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";
														echo "	<input type=\"checkbox\" name=\"CheckSubelemento[$c]\" value=\"".$Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."_".$ElementoM."_".$Grupo."\">\n";
														echo "</td>\n";
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	      			echo "	$GrupoDescricao\n";
		          	      			echo "</td>\n";
				                  	for( $j=0;$j < count($_SESSION['SubEle']);$j++ ){
								          			$Sub  = explode("_",$_SESSION['SubEle'][$j]);
								   							$Ele1 = $Sub[0];
																$Ele2 = $Sub[1];
																$Ele3 = $Sub[2];
																$Ele4 = $Sub[3];
																$EleM = $Sub[4];
																$SubN = $Sub[5];
																if( "".$Elemento1."_".$Elemento2."_".$Elemento3."_".$Elemento4."_".$ElementoM."" == "".$Ele1."_".$Ele2."_".$Ele3."_".$Ele4."_".$EleM."" ){
				                  					$DescricaoSubelemento = $SubN;
				                  			}
				                  	}
		          	      			echo "<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" valign=\"top\">$DescricaoSubelemento</td>\n";
		          	      			echo "</tr>\n";
		          	      			$TipoGAntes = $TipoG;
		          	      			$TipoMAntes = $TipoM;
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
<?php 
function LerSubelemento(){
		unset($_SESSION['SubEle']);
		unset($_SESSION['CarregArray']);
		//$caminho = "http://dcdesenv.emprel.recife/pr/secfinancas/portalcompras/programas/tabelasbasicas/tmp/subelemento.txt";
		$caminho = "http://guabiraba.recife/pr/secfinancas/portalcompras/programas/tabelasbasicas/tmp/subelemento.txt";
		if( !( $fp = fopen($caminho,"r") ) ){
 				echo "Erro na abertura do Arquivo: $caminho";
 				exit;
 		}else{
				$i = 0;
				while( ! feof ($fp)) {
				    $Dados = fgets($fp, 1024);
						if( $Dados != "" ){
								$_SESSION['SubEle'][$i] = trim($Dados);
								//echo "_SESSION[SubEle][$i] = ".$_SESSION['SubEle'][$i]."<br>";
								$i++;
						}
				}
				fclose($fp);
				$_SESSION['CarregArray'] = "S";
		}
}
?>
