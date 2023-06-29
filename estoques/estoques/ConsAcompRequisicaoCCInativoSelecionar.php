<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompRequisicaoCCInativoSelecionar.php
# Autor:    Lucas Baracho
# Data:     22/08/2018
# Objetivo: Tarefa Redmine 189446
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadItemDetalhe.php');
AddMenuAcesso('/estoques/ConsAcompRequisicaoMaterial.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao        = $_POST['Botao'];
	$Situacao     = $_POST['Situacao'];
	$Todas        = $_POST['Todas'];
	$DataIni      = $_POST['DataIni'];
	
	if ($DataIni != "") {
		$DataIni = FormataData($DataIni);
	}
	
	$DataFim      = $_POST['DataFim'];
	
	if ($DataFim != "") {
		$DataFim = FormataData($DataFim);
	}
	
	$Programa	         = $_POST['Programa'];
	$Almoxarifado        = $_POST['Almoxarifado'];
	$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
} else {
	$Mensagem       = urldecode($_GET['Mensagem']);
	$Mens     		= $_GET['Mens'];
	$Tipo     		= $_GET['Tipo'];
	$Programa		= $_GET['Programa'];
	$Situacao 		= $_GET['Situacao'];
	$Todas			= $_GET['Todas'];
	$DataIni		= $_GET['DataIni'];
	$DataFim		= $_GET['DataFim'];
	$Botao			= $_GET['Botao'];
	$Almoxarifado	= $_GET['Almoxarifado'];
}

$programa = "ConsAcompRequisicaoCCInativoSelecionar.php";

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Limpar") {
	if ($Programa == "A") {
	  	$Url = "ConsAcompRequisicaoCCInativoSelecionar.php?Programa=A";
			if (!in_array($Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit();
	} else {
	  	header("location: ConsAcompRequisicaoCCInativoSelecionar.php");
	  	exit();
	}
	exit;
} elseif ($Botao == "Pesquisar") {
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($_SESSION['_fperficorp_'] != 'S' && $Almoxarifado == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.ConsAcompRequisicaoCCInativoSelecionar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
	}
	
	$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"ConsAcompRequisicaoCCInativoSelecionar");
	
	if ($MensErro != "") {
		$Mensagem .= $MensErro;
		$Mens = 1;
		$Tipo = 2;
	}
}

?>

<html>
	<?	# Carrega o layout padrão #
		layout();
	?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
		<!--
			function enviar(valor) {
				document.ConsAcompRequisicaoCCInativoSelecionar.Botao.value = valor;
				document.ConsAcompRequisicaoCCInativoSelecionar.submit();
			}
			<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="ConsAcompRequisicaoCCInativoSelecionar.php" method="post" name="ConsAcompRequisicaoCCInativoSelecionar">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
  				<!-- Caminho -->
  				<tr>	
    				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    				<td align="left" class="textonormal" colspan="2">
      					<font class="titulo2">|</font>
      					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Acompanhamento - CC Inativos
    				</td>
  				</tr>
  				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?	if ($Mens == 1) {?>
				<tr>
	  				<td width="150"></td>
	  				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?	} ?>
				<!-- Fim do Erro -->
				<!-- Corpo -->
				<tr>
					<td width="150"></td>
					<td class="textonormal">
						<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        					<tr>
          						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
        		 					ACOMPANHAMENTO - REQUISIÇÃO DE MATERIAL - CENTROS DE CUSTO INATIVOS
          						</td>
        					</tr>
        					<tr>
          						<td class="textonormal" colspan="4">
             						<p align="justify">
             							Para acompanhar uma Requisição de Material cadastrada por um centro de custo inativo, informe os campos abaixo, clique no botão "Pesquisar" e clique no número da requisição desejada.
             						</p>
          						</td>
        					</tr>
        					<tr>
          						<td colspan="4">
            						<table border="0" width="100%" summary="">
	            						<tr>
	              							<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
	              							<td class="textonormal">
                								<?php	# Mostra os almoxarifados #
														$db = Conexao();
															  
														$sql = "SELECT	A.CALMPOCODI, A.EALMPODESC
																FROM	SFPC.TBALMOXARIFADOPORTAL A
																ORDER BY A.EALMPODESC ";
					  
														$res = $db->query($sql);
									
														if (db::isError($res)) {
									    					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														} else {
															$Rows = $res->numRows();
																
															if ($Rows == 1) {
																$Linha = $res->fetchRow();
																		
																$Almoxarifado = $Linha[0];
																		 
																echo "$Linha[1]<br>";
        	   	      											echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
        	   	      											echo $DescAlmoxarifado;														
															} elseif ($Rows > 1) {
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																																	
																for ($i=0;$i< $Rows; $i++) {
																	$Linha = $res->fetchRow();
																	$DescAlmoxarifado = $Linha[1];
																			   
																	if ($Linha[0] == $Almoxarifado) {
	          	   	      												echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
			          	      										} else {
			          	      											echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
			          	      										}
				                								}
																echo "</select>\n";
																	  
																$CarregaAlmoxarifado = "";
				              								} else {
				            									echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
		  	          	   	  									echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
				            								}
		              									}
														$db->disconnect(); 
												?>
	              							</td>
	            						</tr>
              							<tr>
                							<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
                							<td class="textonormal">
                  								<select name="Situacao" class="textonormal">
                  									<option value="">Selecione uma Situação...</option>
                  										<?	# Mostra as situações cadastradas #
                											$db = Conexao();
						
															$sql = "SELECT	CTIPSRCODI, ETIPSRDESC
																	FROM	SFPC.TBTIPOSITUACAOREQUISICAO
																	WHERE	CTIPSRCODI not in (5, 6)
																	ORDER BY CTIPSRCODI ASC ";
						
															$result = $db->query($sql);
						
															if (db::isError($result)) {
										    					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															} else {
																while ($Linha = $result->fetchRow()) {
		          	      											if ($Situacao == $Linha[0]) {
		          	      												echo"<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
		          	      											} else {
		          	      												echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
		          	      											}
			                									}
			              									}
  	              											$db->disconnect();
      	            									?>
                  								</select>
							    				<input type="checkbox" <?php if ($Todas == S) { echo "checked"; } ?> name="Todas" value="S" onClick="javascript:enviar('Pesquisar');">Todas
                							</td>
              							</tr>
              							<tr>
                							<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
                							<td class="textonormal">
												<?php	$DataMes = DataMes();
    	      											if ($DataIni == "") { $DataIni = $DataMes[0]; }
														if ($DataFim == "") { $DataFim = $DataMes[1]; }
															
														$URLIni = "../calendario.php?Formulario=ConsAcompRequisicaoCCInativoSelecionar&Campo=DataIni";
														$URLFim = "../calendario.php?Formulario=ConsAcompRequisicaoCCInativoSelecionar&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
													<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
													<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                							</td>
              							</tr>
            						</table>
          						</td>
        					</tr>
        					<tr>
 	        					<td class="textonormal" align="right" colspan="4">
   	      							<input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
   	      							<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
   	      							<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
   	      							<input type="hidden" name="Botao" value="">
          						</td>
        					</tr>
							<?php	if ($Botao == "Pesquisar" and $Mens == 0) {
										# Busca os Dados da Tabela de Requisição de Material de Acordo com o Argumento da Pesquisa #
										$db = Conexao();
								
										$sql = "SELECT	A.CREQMASEQU, A.AREQMAANOR, A.CREQMACODI, A.FREQMATIPO, A.DREQMADATA, C.CTIPSRCODI,
														C.ETIPSRDESC, D.ECENPODESC, E.EORGLIDESC, D.ECENPODETA, A.CALMPOCODI 
												FROM	SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBTIPOSITUACAOREQUISICAO C, 
														SFPC.TBCENTROCUSTOPORTAL D, SFPC.TBORGAOLICITANTE E 
												WHERE	A.CREQMASEQU = B.CREQMASEQU
														AND B.CTIPSRCODI = C.CTIPSRCODI 
														AND A.CORGLICODI = D.CORGLICODI
														AND D.CORGLICODI = E.CORGLICODI
														AND A.CCENPOSEQU = D.CCENPOSEQU
														AND D.FCENPOSITU = 'I'
														AND B.CTIPSRCODI NOT IN (5, 6) ";
														
											if ($Almoxarifado != "") {
												$sql .= " AND A.CALMPOCODI = $Almoxarifado ";
											}

										$sql .= "		AND A.FREQMATIPO = 'R'
														AND B.TSITREULAT IN (SELECT	MAX(TSITREULAT)
																			 FROM	SFPC.TBSITUACAOREQUISICAO SIT
																			 WHERE	SIT.CREQMASEQU = A.CREQMASEQU) ";
													
											if ($Situacao != "" and $Todas == "") {
							  					# Verifica se é a situação é "ANÁLISE" #
							  					if ($Situacao == 1) {
 			   				   						$sql .= "AND B.CTIPSRCODI = $Situacao AND B.CREQMASEQU NOT IN(SELECT CREQMASEQU FROM SFPC.TBSITUACAOREQUISICAO WHERE CTIPSRCODI NOT IN(1,5)) ";
 			   									} else {
 			   				   						$sql .= "AND B.CTIPSRCODI = $Situacao ";
 			   			  						}
					  						}		
											
			   							$sql .= "AND A.DREQMADATA >= '".DataInvertida($DataIni)."' AND A.DREQMADATA <= '".DataInvertida($DataFim)."' ";
																		
										$sql .= " ORDER BY E.EORGLIDESC, D.ECENPODESC, A.AREQMAANOR, A.CREQMASEQU DESC ";

										$res = $db->query($sql);
						
										if (db::isError($res)) {
				    						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										} else {
											$Qtd = $res->numRows();
															
											echo "<tr>\n";
											echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "</tr>\n";
									
											if ($Qtd > 0) {
												$DescOrgaoAntes  = "";
												$DescCentroAntes = "";

												while ($Linha = $res->fetchRow()) {
													$Sequencial    = $Linha[0];
													$AnoRequisicao = $Linha[1];
													$Requisicao    = $Linha[2];
													$Data          = DataBarra($Linha[4]);
													$TipoSituacao  = $Linha[5];
													$DescSituacao  = $Linha[6];
													$DescCentro    = $Linha[7];
													$DescOrgao     = $Linha[8];
													$Detalhamento  = $Linha[9];
													$Almoxarifado  = $Linha[10];
																
													if ($DescOrgaoAntes != $DescOrgao) {
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"4\" class=\"titulo3\">$DescOrgao</td>\n";
														echo "</tr>\n";
													}
																
													if ($DescCentroAntes != $DescCentro) {
														echo "<tr>\n";
														echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"4\" class=\"titulo3\">$DescCentro</td>\n";
														echo "</tr>\n";
														echo "<tr>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">REQUISIÇÃO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
														echo "</tr>\n";
													}
																
													echo "<tr>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";
																
													$Url = "ConsAcompRequisicaoMaterial.php?Sequencial=$Sequencial&AnoRequisicao=$AnoRequisicao&Almoxarifado=$Almoxarifado&Situacao=$Situacao&Todas=$Todas&DataIni=$DataIni&DataFim=$DataFim&Programa=$programa";
																
													if (!in_array($Url,$_SESSION['GetUrl'])) {
														$_SESSION['GetUrl'][] = $Url;
													}
																
													echo "		<a href=\"$Url\"><font color=\"#000000\">".substr($Requisicao+100000,1)."/$AnoRequisicao</font></a>";
													echo "	</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Detalhamento</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Data</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSituacao</td>\n";
													echo "</tr>\n";
												
													$DescOrgaoAntes  = $DescOrgao;
													$DescCentroAntes = $DescCentro;
												}
											} else {
												echo "<tr>\n";
												echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
												echo "	Pesquisa sem Ocorrências.\n";
												echo "	</td>\n";
												echo "</tr>\n";
											}
											echo "</table>\n";
										}
										$db->disconnect();
									}
							?>
      					</table>
					</td>
				</tr>
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>
