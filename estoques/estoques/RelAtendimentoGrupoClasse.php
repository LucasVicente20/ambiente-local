<?php
# ---------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoGrupoClasse.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Grupo, Classe e Subclasse.
# Autor:    Filipe Cavalcanti
# Data:     23/09/2005
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     07/06/2007 - Conclusão do desenvolvimento do relatório
# ---------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ---------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoGrupoClasse.php' );
AddMenuAcesso( '/estoques/RelAtendimentoGrupoClassePdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    			    = $_POST['Botao'];
		$TipoMaterial 	    = $_POST['TipoMaterial'];
		$DataIni            = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim            = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$Grupo   				    = $_POST['Grupo'];
		$Classe    			    = $_POST['Classe'];
		$Subclasse    	    = $_POST['Subclasse'];
		$Almoxarifado  			= $_POST['Almoxarifado'];
		$SubclasseDescricao = strtoupper2(trim($_POST['SubclasseDescricao']));
		$CheckSubclasse     = $_POST['CheckSubclasse'];
}else{
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$Mensagem = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelAtendimentoGrupoClasse.php");
	  exit;
}elseif( $Botao == "Validar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelAtendimentoGrupoClasse");
		if( $MensErro != "" ){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
}elseif( $Botao == "Emitir" ){
		$Url = "RelAtendimentoGrupoClassePdf.php?Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse&Almoxarifado=$Almoxarifado&DataIni=$DataIni&DataFim=$DataFim&".mktime();
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<head>
<title>Portal de Compras - Relatório de Antendimento por Grupo, Classe e Subclasse</title>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function checktodos(){
	document.RelAtendimentoGrupoClasse.Subclasse.value = '';
	document.RelAtendimentoGrupoClasse.SubclasseDescricao.value = '';
	document.RelAtendimentoGrupoClasse.Botao.value = 'Validar';
	document.RelAtendimentoGrupoClasse.submit();
}
function enviar(valor){
	document.RelAtendimentoGrupoClasse.Botao.value=valor;
	document.RelAtendimentoGrupoClasse.submit();
}
function validapesquisa(){
	if( document.RelAtendimentoGrupoClasse.Subclasse ){
	  if( document.RelAtendimentoGrupoClasse.SubclasseDescricao.value != '' ){
   	   document.RelAtendimentoGrupoClasse.Subclasse.value = '';
    }
  }
  document.RelAtendimentoGrupoClasse.Botao.value = 'Validar';
  document.RelAtendimentoGrupoClasse.submit();
}
function emitir(valor,subclasse){
	document.RelAtendimentoGrupoClasse.Botao.value=valor;
	document.RelAtendimentoGrupoClasse.Subclasse.value=subclasse;
	document.RelAtendimentoGrupoClasse.submit();
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAtendimentoGrupoClasse.php" method="post" name="RelAtendimentoGrupoClasse">
<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Material por Grupo, Classe e Subclasse
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
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
			    					RELATÓRIO DE ATENDIMENTO POR GRUPO, CLASSE E SUBCLASSE
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" colspan="2">
										<p align="justify">
											Para pesquisar uma Subclasse, preencha os argumentos da pesquisa.
											Depois, clique na Subclasse desejada.<br><br>
						        	Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
									<td colspan="2">
										<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr>
												<td colspan="2">
							      	    <table class="textonormal" border="0" width="100%" summary="">
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Almoxarifado </td>
							              <td class="textonormal">
						                	<?php
						              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
											$db   = Conexao();
											$sql  = "SELECT DISTINCT D.CALMPOCODI, D.EALMPODESC ";
											$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO A, SFPC.TBCENTROCUSTOPORTAL B, SFPC.TBALMOXARIFADOORGAO C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sql .= " WHERE A.CCENPOSEQU = B.CCENPOSEQU AND B.CORGLICODI = C.CORGLICODI AND A.FUSUCCTIPO IN ('T','R') ";
											$sql .= "   AND C.CALMPOCODI = D.CALMPOCODI ";
											if( $_SESSION['_cgrempcodi_'] != 0 ){
												$sql .= "   AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
												$sql .= "   AND A.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
											}
											$sql .= " ORDER BY D.EALMPODESC";
						              		$res  = $db->query($sql);
															if( db::isError($res) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$Rows = $res->numRows();
																	if( $Rows == 1 ){
																			$Linha = $res->fetchRow();
							          	      			$Almoxarifado = $Linha[0];
						        	   	      			echo "$Linha[1]<br>";
						        	   	      			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
						        	   	      			echo $DescAlmoxarifado;
										            	}elseif( $Rows > 1 ){
																			$DescGrupoAntes       = "";
																			echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
										                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																			for( $i=0;$i< $Rows; $i++ ){
																					$Linha = $res->fetchRow();
																					$DescAlmoxarifado = $Linha[1];
																					$Orgao            = $Linha[2];
															            $DescGrupo        = $Linha[3];
							          	   	      			if( $Linha[0] == $Almoxarifado ){
							          	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
									          	      			}else{
									          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
									          	      			}
									          	      			$DescGrupoAntes = $DescGrupo ;
										                	}
										                	echo "</select>\n";
										              }
								              }
						           			 	$db->disconnect();
						    	            ?>
			    	            		</td>
							            </tr>
			 			              <tr>
						                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
						                <td class="textonormal">
															<?php
						    	      			$DataMes = DataMes();
						    	      			if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
															if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
															$URLIni = "../calendario.php?Formulario=RelAtendimentoGrupoClasse&Campo=DataIni";
															$URLFim = "../calendario.php?Formulario=RelAtendimentoGrupoClasse&Campo=DataFim";
															?>
															<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
															&nbsp;a&nbsp;
															<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
						                </td>
						              </tr>
					                <tr>
							              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
							              <td class="textonormal">
								              <input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.RelAtendimentoGrupoClasse.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
								              <input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.RelAtendimentoGrupoClasse.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?> > Permanente
						              	</td>
						            	</tr>
						            	<tr>
						              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
						              	<td class="textonormal">
						              	  <select name="Grupo" onChange="javascript:enviar('');" class="textonormal">
            	              		<option value="">Selecione um Grupo...</option>
							              	    <?php
							              			$db = Conexao();
																	if( $TipoMaterial != "" ){
																			$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																			$sql .= " WHERE FGRUMSSITU = 'A' AND FGRUMSTIPM = '$TipoMaterial' ";
																			$sql .= " ORDER BY EGRUMSDESC";
										                	$result = $db->query($sql);
		                									if (db::isError($result)) {
																		     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																			   while($Linha = $result->fetchRow()){
			          	      							      $Descricao   = substr($Linha[1],0,75);
								          	      			    if( $Linha[0] == $Grupo ){
								    	      						      	echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	      		      }else{
								    	      						      	echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
										      	      		      }
						      	      				       }
									                    }
					              	        }
							              	    ?>
							              	  </select>
							              	</td>
							            	</tr>
                       		  <?php if( $Grupo != ""){ ?>
							              <tr>
								              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
              								<td class="textonormal">
								              	<select name="Classe" class="textonormal" onChange="javascript:enviar('');">
              										<option value="">Selecione uma Classe...</option>
									              		<?php
              												if( $Grupo != "" ){
												              		$db   = Conexao();
																					$sql  = "SELECT CCLAMSCODI, ECLAMSDESC ";
																					$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO ";
																					$sql .= " WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
																					$sql .= " ORDER BY ECLAMSDESC";
																					$res  = $db->query($sql);
																				  if( db::isError($res) ){
																						  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					}else{
																							while( $Linha = $res->fetchRow() ){
														          	      			$Descricao = substr($Linha[1],0,75);
			          	  											    			if( $Linha[0] == $Classe){
								    	      														echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																	      	      		}else{
																												echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																	      	      		}
					            								    		}
																					}
											  	              	$db->disconnect();
	  	            										}
              											?>
              									</select>
              								</td>
            								</tr>
                            <?
                            }
                            if( $Grupo != "" and $Classe != "" ){
                            ?>
								        		<tr>
									            <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
								  	        	<td class="textonormal">
							              	  <select name="Subclasse" onChange="javascript:enviar('Validar');" class="textonormal">
              	              		<option value="">Selecione uma Subclasse...</option>
							              	    <?php
							              			$db     = Conexao();
																	$sql    = "SELECT CSUBCLSEQU,ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL ";
																	$sql   .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
																	$sql   .= " ORDER BY ESUBCLDESC";
										              $result = $db->query($sql);
		                							if (db::isError($result)) {
																	   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																	   while($Linha = $result->fetchRow()){
			          	      					      $Descricao = substr($Linha[1],0,75);
								          	      	    if( $Linha[0] == $Subclasse ){
								    	      				       echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
										      	            }else{
								    	      						   echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
										      	      		  }
						      	      				   }
									                }
							  	              	$db->disconnect();
							              	    ?>
							              	  </select>
							              	  <input type="text" name="SubclasseDescricao" size="10" maxlength="10" class="textonormal">
							              	  <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
							              	  <input type="checkbox" name="CheckSubclasse" onClick="javascript:checktodos();" value="T">Todas
								  	        	</td>
									        	</tr>
									        	<?php } ?>
											   	</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
	              <tr>
				 	        <td class="textonormal" align="right" colspan="2">
				   	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
				   	      	<input type="hidden" name="Botao" value="">
				          </td>
				        </tr>
						    <?php
								# Faz a pesquisa dos Materiais #
								if( $Mens == 0 and ( $Subclasse != "" or $SubclasseDescricao != "" or $CheckSubclasse == "T" ) ){
										# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
										$db     = Conexao();
										$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC,";
										$sql   .= "       SUB.CSUBCLSEQU, SUB.ESUBCLDESC, MAT.CMATEPSEQU, MAT.EMATEPDESC, ";
										$sql   .= "       UND.EUNIDMSIGL, GRU.FGRUMSTIPM ";
										$from   = "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
										$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
										$where  = " WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
										$where .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
										$where .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
										$where .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";

										# Verifica se o Tipo de Material foi escolhido #
										if( $TipoMaterial != "" ){
										  	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
										}

										# Verifica se o Grupo foi escolhido #
										if( $Grupo != "" ){
										  	$where .= " AND GRU.CGRUMSCODI = $Grupo ";
										}

										# Verifica se a Classe foi escolhida #
										if( $Classe != "" ){
										  	$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
										}

										# Verifica se a SubClasse foi escolhida #
										if( $Subclasse != 0 and $Subclasse != "" ){
											  $where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
										}

										# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
										if( $SubclasseDescricao != "" ){
												$where .= " AND SUB.ESUBCLDESC LIKE '$SubclasseDescricao%' ";
										}

										$order  = " ORDER BY GRU.FGRUMSTIPM, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";

										# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
										$sqlgeral = $sql.$from.$where.$order;
										$res      = $db->query($sqlgeral);
										$qtdres  	= $res->numRows();
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												echo "<tr>\n";
												echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"2\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
												echo "</tr>\n";
												if( $qtdres > 0 ){
				  									$TipoMaterialAntes = "";
				  									$GrupoAntes        = "";
				  									$ClasseAntes       = "";
				  									$SubClasseAntes    = "";
														$irow = 1;
				  									while( $row	= $res->fetchRow() ){
				    										$GrupoCodigo        = $row[0];
				    										$GrupoDescricao     = $row[1];
				    										$ClasseCodigo       = $row[2];
				    										$ClasseDescricao    = $row[3];
				    										$SubClasseSequ      = $row[4];
				    										$SubClasseDescricao = $row[5];
				    										$MaterialSequencia  = $row[6];
				    										$MaterialDescricao  = $row[7];
				    										$UndMedidaSigla     = $row[8];
				    										$TipoMaterialCodigo = $row[9];
																if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
					    											echo "<tr>\n";
					    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"2\" align=\"center\">";
				         										if($TipoMaterialCodigo == "C"){ echo "CONSUMO"; }else{ echo "PERMANENTE";}
				  		    									echo "  </td>\n";
				  				    							echo "</tr>\n";
				    										}
				    										if( $GrupoAntes != $GrupoCodigo ) {
				    						            if( $ClasseAntes != $ClasseCodigo ) {
				    						            		echo "<tr>\n";
				      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"2\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
				      											    echo "</tr>\n";
				      											}
				   											}else{
				    						            if( $ClasseAntes != $ClasseCodigo ) {
				    						            		echo "<tr>\n";
				      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"2\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
				      											    echo "</tr>\n";
				      											}
				   										  }

				    										if( $SubClasseAntes != $SubClasseSequ ) {
				    												echo "<tr>\n";
				      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">\n";
				      											echo "    <a href=\"javascript:emitir('Emitir',$SubClasseSequ);\"><font color=\"#000000\">$SubClasseDescricao</font></a>";
				      											echo "  </td>\n";
						    										echo "</tr>\n";
				    									  }

				    										$TipoMaterialAntes = $TipoMaterialCodigo;
				    										$GrupoAntes        = $GrupoCodigo;
				    										$ClasseAntes       = $ClasseCodigo;
				    										$SubClasseAntes    = $SubClasseSequ;
				  									}
														$db->disconnect();
								        }else{
				  									echo "<tr>\n";
				  									echo "	<td valign=\"top\" colspan=\"2\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
				  									echo "		Pesquisa sem Ocorrências.\n";
				  									echo "	</td>\n";
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
