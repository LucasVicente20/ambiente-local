<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsNotaFiscalMaterial.php
# Autor:    Altamiro Pedrosa
# Data:     25/08/2005
# Objetivo: Programa de Consulta de Nota Fiscal a partir da Pesquisa
# -------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/05/2006
# -------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     27/07/2006
# Objetivo: Exibir mais de um Empenho
# -------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     24/08/2006
# Objetivo: Máximo de 16 empenhos para o espelho da nota (PDF)
# -------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     03/07/2008
# Objetivo: Alteração para exibir a situação da nota fiscal. A situação deverá ser: Virtual, Normal ou Cancelada.
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     11/09/2008
# Objetivo: Removido todos acessos a SFPC.TBFORNECEDORESTOQUE.
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     12/12/2008
# Objetivo: Redirecionar página para seleção caso as variáveis requeridas estão vazias.
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     26/11/2018
# Objetivo: Tarefa Redmine 207288
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/ConsNotaFiscalMaterialSelecionar.php');
AddMenuAcesso ('/estoques/RelNotaFiscalPdf.php');
AddMenuAcesso ('/estoques/CadItemDetalhe.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao            = $_POST['Botao'];
	$NotaFiscal       = $_POST['NotaFiscal'];
	$AnoNota          = $_POST['AnoNota'];
	$Almoxarifado     = $_POST['Almoxarifado'];
	$Fornecedor       = $_POST['Fornecedor'];
	$NumeroNota       = $_POST['NumeroNota'];
	$SerieNota        = $_POST['SerieNota'];
	$DataEntrada      = $_POST['DataEntrada'];
	$DataEmissao      = $_POST['DataEmissao'];
	$ValorNota        = $_POST['ValorNota'];
	$AnoEmpenho       = $_POST['AnoEmpenho'];
	$OrgaoEmpenho     = $_POST['OrgaoEmpenho'];
	$UnidadeEmpenho   = $_POST['UnidadeEmpenho'];
	$SerieEmpenho     = $_POST['SerieEmpenho'];
	$ParcelaEmpenho   = $_POST['ParcelaEmpenho'];
	$QtdItem[$i]      = $_POST['QtdItem'];
	$ValorItem[$i]    = $_POST['ValorItem'];
	$DescMaterial[$i] = $_POST['DescMaterial'];
	$ano              = $_POST['Ano'];
    $orgao            = $_POST['Orgao'];
    $unidade          = $_POST['Unidade'];
    $sequencial       = $_POST['Sequencial'];
    $parcela          = $_POST['Parcela'];
} else {
	$NotaFiscal   = $_GET['NotaFiscal'];
	$AnoNota      = $_GET['AnoNota'];
	$Almoxarifado = $_GET['Almoxarifado'];
	$ano          = $_GET['Ano'];
    $orgao        = $_GET['Orgao'];
    $unidade      = $_GET['Unidade'];
    $sequencial   = $_GET['Sequencial'];
    $parcela      = $_GET['Parcela'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if (($Botao == "Voltar") or (is_null($NotaFiscal) or $NotaFiscal=="")) {
	if (!empty($orgao)) {
		$enviar = 'S';
	} else {
		header("location: ConsNotaFiscalMaterialSelecionar.php");
		exit;
	}
}

if ($Botao == "Imprimir") {
	$Url = "RelNotaFiscalPdf.php?NotaFiscal=$NotaFiscal&AnoNota=$AnoNota&Almoxarifado=$Almoxarifado";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit;
}

# Verifica se o Fornecedor de Estoque já está cadastrado #
if ($Botao == "") {
	# Pega os dados da Entrada por NF de acordo com o Sequencial #
	$db = Conexao();
	
	$sql  = "SELECT A.AENTNFNOTA, A.AENTNFSERI, A.DENTNFENTR, ";
	$sql .= "       A.DENTNFEMIS, A.VENTNFTOTA, ";
	$sql .= "       B.AITENFQTDE, B.VITENFUNIT, ";
	$sql .= "       C.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, ";
	$sql .= "       A.AFORCRSEQU, A.CFORESCODI, A.FENTNFCANC, ";
	$sql .= "       A.AENTNFANOE, A.FENTNFVIRT ";
	$sql .= "  FROM SFPC.TBENTRADANOTAFISCAL A, SFPC.TBITEMNOTAFISCAL B, ";
	$sql .= "	  	  SFPC.TBMATERIALPORTAL C, SFPC.TBUNIDADEDEMEDIDA D ";
	$sql .= " WHERE A.CENTNFCODI = B.CENTNFCODI AND B.CMATEPSEQU = C.CMATEPSEQU ";
	$sql .= "   AND A.CALMPOCODI = B.CALMPOCODI AND A.CENTNFCODI = B.CENTNFCODI ";
	$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND A.CALMPOCODI = $Almoxarifado ";
	$sql .= "   AND A.CENTNFCODI = $NotaFiscal AND A.AENTNFANOE = $AnoNota ";
	$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
	$sql .= " ORDER BY A.AENTNFNOTA, C.EMATEPDESC ";
	
	$res = $db->query($sql);
		
	if (PEAR::isError($res)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Rows = $res->numRows();
			
		for ($i=0; $i<$Rows; $i++) {
			$Linha            = $res->fetchRow();
			$NumeroNota       = $Linha[0];
			$SerieNota        = $Linha[1];
			$DataEntrada      = DataBarra($Linha[2]);
			$DataEmissao      = DataBarra($Linha[3]);
			$ValorNota        = str_replace(",",".",$Linha[4]);
			$QtdItem[$i]      = str_replace(",",".",$Linha[5]);
			$ValorItem[$i]    = str_replace(",",".",$Linha[6]);
			$Material[$i]     = $Linha[7];
			$DescMaterial[$i] = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
			$DescUnidade[$i]  = $Linha[9];
			$FornecedorSequ   = $Linha[10];
			$FornecedorCodi   = $Linha[11];
			$Situacao         = $Linha[12];
			$AnoExercicio     = $Linha[13];
            $NFVirtual        = $Linha[14];
		}
			
		# Recupera dados dos empenhos #
		$sqlemp  = "SELECT ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
		$sqlemp .= "       CNFEMPSEEM, CNFEMPPAEM ";
		$sqlemp .= "  FROM SFPC.TBNOTAFISCALEMPENHO ";
		$sqlemp .= " WHERE CALMPOCODI = $Almoxarifado ";
		$sqlemp .= "   AND AENTNFANOE = $AnoExercicio ";
		$sqlemp .= "   AND CENTNFCODI = $NotaFiscal ";
		
		$resemp = $db->query($sqlemp);
				
		if (PEAR::isError($resemp)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
		} else {
			while ($LinhaEmp = $resemp->fetchRow()) {
				$AnoEmp        = $LinhaEmp[0];
				$OrgaoEmp      = $LinhaEmp[1];
				$UnidadeEmp    = $LinhaEmp[2];
				$SequencialEmp = $LinhaEmp[3];
				$ParcelaEmp    = $LinhaEmp[4];
				
				if ($ParcelaEmp) {
					$Empenhos[] = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";
				} else {
					$Empenhos[] = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp";
				}
			}
		}
				
		if ($FornecedorSequ != "") {
			# Verifica se o Fornecedor de Estoque é Credenciado #
			$sqlforn  = "SELECT NFORCRRAZS,AFORCRCCGC,AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
			$sqlforn .= " WHERE AFORCRSEQU = '$FornecedorSequ' ";
			
			$resforn  = $db->query($sqlforn);
			
			if (PEAR::isError($resforn)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
			} else {
				$Linhaforn  = $resforn->fetchRow();
				$Razao = $Linhaforn[0];
				$CNPJ  = $Linhaforn[1];
				$CPF   = $Linhaforn[2];
			}
		} else {
			EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo sistema foi correto ou se há algum fornecedor que não foi migrado de SFPC.TBFORNECEDORESTOQUE para SFPC.TBFORNECEDORCREDENCIADO corretamente.");
		}
	}
	$db->disconnect();
}
?>
<html>
<?php
if (empty($orgao)) {
	# Carrega o layout padrão #
	layout();
}
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor){
		document.ConsNotaFiscal.Botao.value = valor;
		document.ConsNotaFiscal.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<?php if (empty($orgao)) { ?>
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<?php } ?>
	<form action="ConsNotaFiscalMaterial.php" method="post" name="ConsNotaFiscal">
		<?php if (empty($orgao)) { ?>
			<br><br><br><br><br>
		<?php } ?>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<?php if (empty($orgao)) { ?>
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Consultar
				</td>
			</tr>
			<?php } ?>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<?php if (empty($orgao)) { ?>
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
			<?php } ?>
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											CONSULTA - NOTA FISCAL
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Para emitir o espelho da nota fiscal clique no botão "Imprimir". Neste espelho aparecerá no máximo 16 empenhos. Para retornar a tela anterior clique no botão "Voltar".<br>
											</p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left" width="100%" summary="">
												<?php if ($Situacao == "S") { ?>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
														<td class="textonormal">CANCELADA</td>
													</tr>
												<?php } ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
													<td class="textonormal">
													<?php
													# Mostra o Almoxarifado de Acordo com o Usuário Logado #
														$db = Conexao();
												
														$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
														$sql .= " WHERE CALMPOCODI = $Almoxarifado ";

														$res = $db->query($sql);

														if (PEAR::isError($res)) {
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														} else {
															$Linha = $res->fetchRow();
															echo "$Linha[0]<br>";
														}
														
														$db->disconnect();
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota</td>
													<td class="textonormal"><?php echo $NumeroNota."/".$AnoNota; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Série da Nota</td>
													<td class="textonormal"><?php echo $SerieNota; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
													<td class="textonormal">
														<?php
														if ($Situacao == 'S') {
															echo "Cancelada";
														} else {
                          									if ($NFVirtual == 'S') {
                            									echo "Virtual";
                          									} else {
													  			echo "Normal";
                          									}
														}
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Emissão</td>
													<td class="textonormal"><?php echo $DataEmissao; ?></td>
												</tr>
												<tr>
													<?php
													if ($CPF) {
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">CPF do Fornecedor</td>";
														echo "<td class=\"textonormal\">".FormataCPF($CPF)."</td>";
													} else {
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">CNPJ do Fornecedor</td>";
														echo "<td class=\"textonormal\">".FormataCNPJ($CNPJ)."</td>";
													}
													?>
												</tr>
												<tr>
													<?php
													if ($CPF) {
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">Nome</td>";
													} else {
														echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\" width=\"30%\">Razão Social</td>";
													}
													?>
													<td class="textonormal"><?php echo $Razao; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Entrada</td>
													<td class="textonormal"><?php echo $DataEntrada; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor da Nota</td>
													<td class="textonormal"><?php echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota))); ?></td>
												</tr>
												<tr>
													<td class="textonormal" colspan="4">
														<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
																	ITENS DA NOTA FISCAL
																</td>
															</tr>
															<?php
															for ($i=0; $i< count($DescMaterial); $i++) {
																if ($i == 0) {
																	echo "		<tr>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">QUANTIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR UNITÁRIO</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR TOTAL</td>\n";
																	echo "		</tr>\n";
																}
															?>
															<tr>
																<td class="textonormal">
																	<?php
																	$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=$Material[$i]";
																	
																	if (!in_array($Url,$_SESSION['GetUrl'])) {
																		$_SESSION['GetUrl'][] = $Url;
																	}
																	?>
																	<a href="javascript:AbreJanela('<?php $Url;?>',700,350);">
																		<font color="#000000">
																			<?php
																			$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																			echo $Descricao[1];
																			?>
																		</font>
																	</a>
																</td>
																<td class="textonormal" align="center">
																	<?php echo $DescUnidade[$i];?>
																</td>
																<td class="textonormal" align="right">
																	<?php if ($QtdItem[$i] == "") { echo 0; } else { echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdItem[$i]))); } ?>
																</td>
																<td class="textonormal" align="right">
																	<?php if ($ValorItem[$i] == "") { echo 0; } else { echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorItem[$i]))); } ?>
																</td>
																<td class="textonormal" align="right">
																	<?php echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",($QtdItem[$i] * $ValorItem[$i])))); ?>
																</td>
															</tr>
															<?php
															}
															?>
														</table>
													</td>
												</tr>
												<tr>
													<td class="textonormal" colspan="5">
														<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
																	EMPENHOS
																</td>
															</tr>
															<?php
															# Exibe os empenhos #
															for ($i=0; $i< count($Empenhos); $i++) {
																# Imprime o cabeçalho se for a primeira execução #
																if ($i == 0) {
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"25%\">Ano<input type=\"hidden\" name=\"Empenhos[$i]\" value=\"$Empenhos[$i]\"></td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Órgão</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Unidade</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"30%\">Sequencial</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"15%\">Parcela</td>\n";
																	echo "		</tr>\n";
																}
																# Separa Ano, Órgão, Unidade, Sequencial e Parcela #
																$Emp = explode(".",$Empenhos[$i]);
																$AnoEmp        = $Emp[0];
																$OrgaoEmp      = $Emp[1];
																$UnidadeEmp    = $Emp[2];
																$SequencialEmp = $Emp[3];
																$ParcelaEmp    = $Emp[4];
															?>
																<tr>
																	<td class="textonormal" align="center" width="10%">
																		<?php echo $AnoEmp;?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php echo $OrgaoEmp;?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php echo $UnidadeEmp;?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php echo $SequencialEmp;?>
																	</td>
																	<td class="textonormal" align="center" width="10%">
																		<?php if ($ParcelaEmp) { echo $ParcelaEmp; } else { echo "&nbsp;"; } ?>
																	</td>
																</tr>
															<?php
															}
															?>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right">
											<input type="hidden" name="AnoNota" value="<?php echo $AnoNota; ?>">
											<input type="hidden" name="NotaFiscal" value="<?php echo $NotaFiscal; ?>">
											<input type="hidden" name="Razao" value="<?php echo $Razao; ?>">
											<input type="hidden" name="CNPJ" value="<?php echo $CNPJ; ?>">
											<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
											<input type="hidden" name="Fornecedor" value="<?php echo $Fornecedor; ?>">
											<input type="hidden" name="FornecedorCodi" value="<?php echo $FornecedorCodi; ?>">
											<input type="button" name="Imprimir" value="Imprimir" class="botao" onClick="javascript:enviar('Imprimir');">
											
											<?php if (!empty($orgao)) { ?>
												<input type="hidden" name="Enviar" value="<?php echo $enviar; ?>">
						       					<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
											<?php } else { ?>
												<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
											<?php } ?>
											
											<input type="hidden" name="Botao" value="">
										</td>
									</tr>
								</table>
							</td>
						</tr>
					<?php if (empty($orgao)) { ?>
					</table>
				</td>
			</tr>
			<?php } ?>
			<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>