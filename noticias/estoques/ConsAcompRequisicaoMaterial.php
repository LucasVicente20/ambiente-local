<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompRequisicaoMaterial.php
# Autor:    Roberta Costa
# Data:     09/06/05
# Objetivo: Programa de Acompanhamento de Requisição de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     22/08/2018
# Objetivo: Tarefa Redmine 189446
#-------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     08/09/2022
# Objetivo: CR 219491
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/ConsAcompRequisicaoMaterialSelecionar.php');
AddMenuAcesso ('/estoques/ConsAcompRequisicaoCCInativoSelecionar.php');
AddMenuAcesso ('/estoques/RelConsAcompRequisicaoMaterialPdf.php');
AddMenuAcesso ('/estoques/CadItemDetalhe.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          = $_POST['Botao'];
	$Sequencial     = $_POST['Sequencial'];
	$AnoRequisicao  = $_POST['AnoRequisicao'];
	$Requisicao     = $_POST['Requisicao'];
	$Situacao   	= $_POST['Situacao'];
	$TipoUsuario	= $_POST['TipoUsuario'];
	$CentroCusto	= $_POST['CentroCusto'];
	$DataRequisicao = $_POST['DataRequisicao'];
	$DescMaterial   = $_POST['DescMaterial'];
	$Unidade        = $_POST['Unidade'];
	$DescUnidade    = $_POST['DescUnidade'];
	$QtdSolicitada  = $_POST['QtdSolicitada'];
	$QtdAprovada    = $_POST['QtdAprovada'];
	$QtdAtendida    = $_POST['QtdAtendida'];
	$QtdCancelada   = $_POST['QtdCancelada'];
	$Ordem          = $_POST['Ordem'];
	$Almoxarifado   = $_POST['Almoxarifado'];
	$Situacao		= $_POST['Situacao'];
	$Todas			= $_POST['Todas'];
	$DataIni		= $_POST['DataIni'];
	$DataFim		= $_POST['DataFim'];
	$Programa       = $_POST['Programa'];
} else {
	$Sequencial    = $_GET['Sequencial'];
	$AnoRequisicao = $_GET['AnoRequisicao'];
	$Almoxarifado  = $_GET['Almoxarifado'];
	$Situacao	   = $_GET['Situacao'];
	$Todas		   = $_GET['Todas'];
	$DataIni	   = $_GET['DataIni'];
	$Programa      = $_GET['Programa'];
}

//echo $Botao;die;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;


if ($Botao == "Voltar") {
	////($Programa ."-".!is_null($Programa));
	//echo "texto";
	//die;
	if (!is_null($Programa)) {
		$Url = "ConsAcompRequisicaoMaterialSelecionar.php?Situacao=$Situacao&Todas=$Todas&DataIni=$DataIni&DataFim=$DataFim&Botao=Pesquisar&Almoxarifado=$Almoxarifado";
	} else {
		echo "entrou"; die;
		$Url = "ConsAcompRequisicaoMaterialSelecionar.php?Situacao=$Situacao&Todas=$Todas&DataIni=$DataIni&DataFim=$DataFim&Botao=Pesquisar&Almoxarifado=$Almoxarifado";
	}

	//$Url = "ConsAcompRequisicaoMaterialSelecionar.php?Situacao=$Situacao&Todas=$Todas&DataIni=$DataIni&DataFim=$DataFim&Botao=Pesquisar&Almoxarifado=$Almoxarifado";

	if (!in_array($Url, $_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit;
} elseif ($Botao == "Imprimir") {
	$Url = "RelConsAcompRequisicaoMaterialPdf.php?Sequencial=$Sequencial&AnoRequisicao=$AnoRequisicao&Almoxarifado=$Almoxarifado";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit;
}

if ($Botao == "") {
    # Pega os dados do Centro de Custo #
	$db     = Conexao();
	
	$sql 	  = "SELECT A.CCENPOSEQU ";
	$sql 	 .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
	$sql 	 .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU ";
	$sql 	 .= "   AND C.CREQMASEQU = $Sequencial ";
	$sql   .= " ORDER BY B.EORGLIDESC, A.ECENPODESC ";
	
	$result = $db->query($sql);
	
	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha       = $result->fetchRow();
		$CentroCusto = $Linha[0];
	}

	# Pega os dados da Requisição de Material de acordo com o Sequencial #
	$sql  = "SELECT A.CREQMACODI, A.CGREMPCODI, A.CUSUPOCODI, B.AITEMRQTSO, ";
	$sql .= "       B.AITEMRQTAT, B.AITEMRORDE, C.CMATEPSEQU, C.EMATEPDESC, ";
	$sql .= "       D.EUNIDMSIGL, A.DREQMADATA, A.EREQMAOBSE  ";
	$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
	$sql .= "       SFPC.TBUNIDADEDEMEDIDA D  ";
	$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $Sequencial ";
	$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
	$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
	$sql .= " ORDER BY B.AITEMRORDE ";
	
	$res  = $db->query($sql);
	
	if (db::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $res->numRows();
		
		for ($i=0;$i<$Rows;$i++) {
			$Linha              = $res->fetchRow();
			$Requisicao         = $Linha[0];
			$GrupoEmp           = $Linha[1];
			$Usuario            = $Linha[2];
		    $QtdSolicitada[$i]  = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[3])));
		    $QtdAtendida[$i]    = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[4])));
		    $Ordem[$i]          = $Linha[5];
			$Material[$i]       = $Linha[6];
			$DescMaterial[$i]   = $Linha[7];
			$DescUnidade[$i]    = $Linha[8];
			$DataRequisicao     = DataBarra($Linha[9]);
			$Observacao         = $Linha[10];
		}
	}

	# Pega os dados da Última Situação da Requisicao #
	$sql  = "SELECT A.TSITREULAT, B.ETIPSRDESC, B.CTIPSRCODI, A.ESITREMOTI ";
	$sql .= "  FROM SFPC.TBSITUACAOREQUISICAO A, SFPC.TBTIPOSITUACAOREQUISICAO B ";
	$sql .= " WHERE A.CREQMASEQU = $Sequencial AND A.CTIPSRCODI = B.CTIPSRCODI ";
	$sql .= "   AND A.TSITREULAT =  ";
	$sql .= "      ( SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
	$sql .= "         WHERE CREQMASEQU = $Sequencial ) ";
	
	$result = $db->query($sql);
	
	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha        = $result->fetchRow();
		$DataSituacao = DataBarra($Linha[0]);
		$DescSituacao = $Linha[1];
		$Motivo       = $Linha[3];
	}
	$db->disconnect();
}

?>
<html>
	<?	# Carrega o layout padrão #
		layout();
	?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
		function enviar(valor){
			document.ConsAcompRequisicaoMaterial.Botao.value = valor;
			document.ConsAcompRequisicaoMaterial.submit();
		}
		function AbreJanela(url,largura,altura) {
			window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
		}
		<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="ConsAcompRequisicaoMaterial.php" method="post" name="ConsAcompRequisicaoMaterial">
		<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
  				<!-- Caminho -->
  				<tr>
    				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    				<td align="left" class="textonormal" colspan="2">
      					<font class="titulo2">|</font>
						<?php if (!is_null($Programa)) {?>
							<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Acompanhamento - CC Inativos
						<?php } else { ?>
      						<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Acompanhamento
						<?php } ?>
    				</td>
  				</tr>
  				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?	if ( $Mens == 1 ) {?>
				<tr>
	  				<td width="100"></td>
	  				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?	} ?>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="100"></td>
					<td class="textonormal">
      					<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
        					<tr>
	      						<td class="textonormal">
	        						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
	          							<tr>
	            							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    									ACOMPANHAMENTO - REQUISIÇÃO DE MATERIAL
		          							</td>
		        						</tr>
	  	      							<tr>
	    	      							<td class="textonormal">
	      	    								<p align="justify">
	        	    								Para imprimir os dados da Requisição, clique no botão "Imprimir". Para retornar a tela anterior clique no botão "Voltar".<br>
	          	   								</p>
	          								</td>
		        						</tr>
		        						<tr>
	  	        							<td>
	    	      								<table class="textonormal" border="0" align="left" width="100%" summary="">
				            						<tr>
				              							<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
				              							<td class="textonormal">
			                								<?php	# Verifica a descrição do almoxarifado que fez o atendimento #
																	$db   = Conexao();
																  
																	$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
																	$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
																  
																	$res  = $db->query($sql);
																
																	if (db::isError($res)) {
												    					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
			     	   	      											$Linha = $res->fetchRow();
			     	   	      											echo "$Linha[0]<br>";
					              									}
			           			 							?>
			 	   	      									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
				              							</td>
				            						</tr>
				            						<tr>
				              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo</td>
				              							<td class="textonormal">
	              											<?php	# Pega os dados do Centro de Custo #
																	$sql    = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CCENPONRPA, A.ECENPODETA ";
																	$sql   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																	$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
																	
																	$result = $db->query($sql);
												
																	if (db::isError($result)) {
												    					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
																		$Linha = $result->fetchRow();
																		$DescCentroCusto = $Linha[0];
																		$DescOrgao       = $Linha[1];
																		$RPA             = $Linha[2];
																		$Detalhamento    = $Linha[3];

						               									echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
						               									echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						               									echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																		echo $Detalhamento;
																	}
					           								?>
				              							</td>
				            						</tr>
				            						<tr>
				              							<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Requisição</td>
				              							<td class="textonormal">
															<?php echo substr($Requisicao+100000,1)."/".$AnoRequisicao; ?>
														</td>
				            						</tr>
				            						<tr>
				              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário Requisitante</td>
				              							<td class="textonormal">
				              								<?php	# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
																	$sql    = "SELECT USU.EUSUPOLOGI, USU.EUSUPORESP ";
																	$sql   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
																	$sql   .= " WHERE SIT.CREQMASEQU = $Sequencial    AND SIT.CTIPSRCODI = 1 ";
																	$sql   .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
																	
																	$result = $db->query($sql);
																	
																	if (db::isError($result)) {
											    						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
																		$Linha = $result->fetchRow();
																		$Login = strtoupper2($Linha[0]);
																		$Nome  = $Linha[1];
																		echo $Nome;
																	}
															?>
				              							</td>
				            						</tr>
				            						<tr>
				              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Requisição</td>
				              							<td class="textonormal">
															<?php echo $DataRequisicao; ?>
														</td>
				            					</tr>
				            					<?php	#Pega o usuário responsável pela requisição da tabela SFPC.TBSITUACAOREQUISICAO quando a situação for igual a 3 ou a 4
														$sql   = "   SELECT 	USU.EUSUPOLOGI, USU.EUSUPORESP, SIT.CREQMASEQU,SIT.CTIPSRCODI ";
														$sql   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT, SFPC.TBREQUISICAOMATERIAL MAT  ";
														$sql   .= "  WHERE 	SIT.CREQMASEQU = $Sequencial AND SIT.CREQMASEQU =MAT.CREQMASEQU AND SIT.CTIPSRCODI = 3 ";
														$sql   .= "  AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
														$sql   .= "  UNION SELECT USU.EUSUPOLOGI, USU.EUSUPORESP, SIT.CREQMASEQU,SIT.CTIPSRCODI ";
														$sql   .= "  FROM	SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT, SFPC.TBREQUISICAOMATERIAL MAT  ";
														$sql   .= "  WHERE 	SIT.CREQMASEQU = $Sequencial AND SIT.CREQMASEQU =MAT.CREQMASEQU AND SIT.CTIPSRCODI = 4 ";
														$sql   .= "  AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
									
														$result = $db->query($sql);
								
														if (db::isError($result)) {
								    						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														} else {
															$Linha = $result->fetchRow();
															$NomeResp  = $Linha[1];
														}
														
														$db->disconnect();
														
														if ($NomeResp!="") {
				            					?>
				            					<tr>
						              				<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Usuário Atendimento</td>
						              				<td class="textonormal">
														<?php echo $NomeResp; ?>
													</td>
				            					</tr>
				            					<?	} ?>
							            		<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
				              						<td class="textonormal">
														<?php echo $DescSituacao; ?>
													</td>
				            					</tr>
				            					<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação</td>
				              						<td class="textonormal">
													  	<?php echo $DataSituacao; ?>
													</td>
				            					</tr>
				            					<?	if( $Motivo != "" ){  ?>
				            					<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Motivo</td>
				              						<td class="textonormal">
													  	<?php echo $Motivo; ?>
													</td>
				            					</tr>
				            					<?	} ?>
				            					<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
				              						<td class="textonormal">
														<?php if( $Observacao != "" ){ echo $Observacao; }else{ echo "NÃO INFORMADA"; }?>
													</td>
				            					</tr>
				            					<tr>
				              						<td class="textonormal" colspan="4">
				              							<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
							          						<tr>
							            						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
								    								ITENS DA REQUISIÇÃO
								          						</td>
								        					</tr>
								        					<?php	for( $i=0;$i< count($Material);$i++ ){
																		if( $i == 0 ){
					  	      	            								echo "		<tr>\n";
												          					echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\">ORDEM</td>\n";
								        	      							echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\">DESCRIÇÃO DO MATERIAL</td>\n";
								        	      							echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\">CÓD.RED.</td>\n";								        	      	
								        	      							echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
								        		      						echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\" width=\"10%\" colspan=\"2\">QUANTIDADE</td>\n";
					  	      	            								echo "		</tr>\n";
					  	      	            								echo "		<tr>\n";
								        	      							echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">SOLICITADA</td>\n";
								        	      							echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">ATENDIDA</td>\n";
											            					echo "		</tr>\n";
											        					}
								        					?>
								        					<tr>
				          	    								<td class="textonormal" align="right">
				          	    									<?php echo $Ordem[$i];?>
							  	        							<input type="hidden" name="Ordem[<?php echo $i; ?>]" value="<?php echo $Ordem[$i]; ?>">
				          	    								</td>
							  	        						<td class="textonormal">
							  	        							<?	$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=$Material[$i]";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	?>
							  	        							<a href="javascript:AbreJanela('<?=$Url;?>',700,350);"><font color="#000000"><?php echo $DescMaterial[$i];?></font></a>
							  	        							<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
							  	        						</td>
							  	        						<td class="textonormal" align="center">
																	<?php echo $Material[$i];?>
							              							<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
							              						</td>	
				          	    								<td class="textonormal" align="center">
				          	    									<?php echo $DescUnidade[$i];?>
							  	        							<input type="hidden" name="DescUnidade[<?php echo $i; ?>]" value="<?php echo $DescUnidade[$i]; ?>">
				          	    								</td>
					              								<td class="textonormal" align="right">
				          	    									<?php if( $QtdSolicitada[$i] == "" ){ echo 0; }else{ echo $QtdSolicitada[$i]; } ?>
				          	    								</td>
					              								<td class="textonormal" align="right">
				          	    									<?php if( $QtdAtendida[$i] == "" ){ echo 0; }else{ echo $QtdAtendida[$i]; } ?>
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
   	  	  								<td class="textonormal" align="right">
											<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
											<input type="hidden" name="Todas" value="<?php echo $Todas; ?>">
											<input type="hidden" name="DataIni" value="<?php echo $DataIni; ?>">
											<input type="hidden" name="DataFim" value="<?php echo $DataFim; ?>">
											<input type="hidden" name="Motivo" value="<?php echo $Motivo; ?>">
               								<input type="hidden" name="Observacao" value="<?php echo $Observacao; ?>">
               								<input type="hidden" name="AnoRequisicao" value="<?php echo $AnoRequisicao; ?>">
               								<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
               								<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
											<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
			  	      						<input type="button" name="Imprimir" value="Imprimir" class="botao" onClick="javascript:enviar('Imprimir');">
			  	      						<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
         	      							<input type="hidden" name="Botao" value="">
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
