<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraManterAcompanharSelecionar.php
# Autor:    Carlos Abreu
# Data:     15/06/2006
# Objetivo: Programa que Seleciona a Solicitação de Compra de Material
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     15/08/2011 - Demanda do Redmine: #3224 - Tela de seleção SCC
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     21/09/2011 - Alterações para novas SCCs
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     06/03/2012 - Demanda do Redmine: #3998 - Tela de seleção SCC
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     17/07/2012 - Demanda do Redmine: #12645 - Tela Manter e Excluir PSE Gerada
# -------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     31/07/2014 - Adiciona novas regras para alteração ou exclusão de SCC
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     04/09/2018
# Objetivo: Tarefa Redmine 202403
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     12/09/2018
# Objetivo: Tarefa Redmine 203403
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     30/11/2018
# Objetivo: Tarefa Redmine 207413
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     15/01/2019
# Objetivo: Tarefa Redmine 209296
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     02/04/2019
# Objetivo: Tarefa Redmine 213421
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     03/04/2019
# Objetivo: Tarefa Redmine 214276
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     12/04/2019
# Objetivo: Tarefa Redmine 214807
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     17/04/2019
# Objetivo: Tarefa Redmine 215203
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('compras/'.$programaSelecao);

$db = Conexao();

# Variáveis com o global off #
if ($_SESSION["carregarSelecionarDoSession"]) {
	$Botao    = $_SESSION['Botao'];
	$Orgao	  = $_SESSION['Orgao'];
	$Situacao = $_SESSION['Situacao'];
	$TipoSCC  = $_SESSION['TipoSCC'];
	$DataIni  = $_SESSION['DataIni'];
	$DataFim  = $_SESSION['DataFim'];
    $numeroSccAtual = $_SESSION['numeroSccAtual'];
	
	$_SESSION["carregarSelecionarDoSession"] = false;
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao    = $_POST['Botao'];
	$DataIni  = $_POST['DataIni'];
	$Orgao	  = $_POST['Orgao'];
	$Situacao = $_POST['Situacao'];
	$TipoSCC   = $_POST['TipoSCC'];
	$DataFim  = $_POST['DataFim'];
    $numeroSccAtual = $_POST['numeroScc'];
	
	if ($DataIni != "") {
		$DataIni = FormataData($DataIni);
	}
	
	if ($DataFim != "") {
		$DataFim = FormataData($DataFim);
	}
} else {
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

$_SESSION['Botao']    = $Botao;
$_SESSION['Orgao']    = $Orgao;
$_SESSION['Situacao'] = $Situacao;
$_SESSION['DataIni']  = $DataIni;
$_SESSION['DataFim']  = $DataFim;
$_SESSION['TipoSCC']  = $TipoSCC;
$_SESSION['numeroSccAtual']  = $numeroSccAtual;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Limpar") {
	header("location: ".$programa);
	exit;
} elseif ($Botao == "Pesquisar") {
    # Critica dos Campos #
    $Mens     = 0;
	$Mensagem = "Informe: ";
	$MensErro = ValidaData($DataIni);

	if (!empty($DataIni) && $MensErro != "") {
	    adicionarMensagem("<a href='javascript:document.formulario.DataIni.focus();' class='titulo2'>Data Inicial Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}

	$MensErro = ValidaData($DataFim);
	
	if (!empty($DataFim) && $MensErro != "") {
	    	adicionarMensagem("<a href='javascript:document.formulario.DataFim.focus();' class='titulo2'>Data Final Válida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	    }

	if ($DataIni != "" && $DataFim != "" && ValidaData($DataIni) == '' && ValidaData($DataFim) == '') {
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"formulario");
		
		if ($MensErro != "" ) {
	    	adicionarMensagem("<a href='javascript:document.formulario.DataIni.focus();' class='titulo2'>Data Final igual ou maior que Data Inicial</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
		}
	}

	if ($Situacao == "") {
		adicionarMensagem("<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Situação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
	}

	if($_SESSION['_fperficorp_'] != 'S' && empty($Orgao)) {
        adicionarMensagem("<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Órgão</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    }

    // Número SCC
    $seqScc = null;
    if(!empty($numeroSccAtual)) {
        $retirar = array(".", "/");
        $scc = str_replace($retirar, ".", $numeroSccAtual);

        if(isNumeroSCCValido($scc)){
            $seqScc = getSequencialSolicitacaoCompra($db, $scc);
            if(empty($seqScc)) {
                adicionarMensagem("<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Número da SCC válido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }

    }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.formulario.Botao.value = valor;
		document.formulario.submit();
	}
	
	function AbreJanela(url,largura,altura) {
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
	}

	function onClickDesativado(erro) {
		alert(erro);
	}

    $(document).ready(function() {
        $('#numeroScc').mask('9999.9999/9999');
    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="<?php $programa?>" method="post" name="formulario">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>			
					<?php
					if ($acaoPagina == ACAO_PAGINA_MANTER) {
						?>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Compras > Manter			
						<?php
					} elseif ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
						?>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Compras > Acompanhar			
						<?php
					} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
						?>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Compras > Cancelar
						<?php
					}
					?>
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
								<?php
								if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
									?>
									ACOMPANHAR
									<?php
								} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
									?>
									MANTER
									<?php
								} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
									?>
									CANCELAR
									<?php
								}
								?>
								SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO DE MATERIAL OU SERVIÇO (SCC)
							</td>
						</tr>
						<tr>
							<td class="textonormal" colspan="4">
								<p align="justify">
									Preencha os dados abaixo para efetuar a pesquisa e clique no número da Solicitação desejada.
								</p>
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<table border="0" width="100%" summary="">
									<?php
									if ($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == "S") {
										?>
                                    	<tr>
                                        	<td class="textonormal" bgcolor="#DCEDF7">Número da SCC </td>
                                        	<td class="textonormal">
                                            	<input type="text" id="numeroScc" value="<?php echo (!empty($numeroSccAtual)) ? $numeroSccAtual : ''; ?>" name="numeroScc" class="textonormal" />
                                        	</td>
                                    	</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão</td>
											<td class="textonormal">
												<select name="Orgao" class="textonormal">
													<option value="">Selecione um Órgão...</option>
													<?php
													$sql = "SELECT	DISTINCT A.CORGLICODI, B.EORGLIDESC 
															FROM	SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B
															WHERE	A.CORGLICODI = B.CORGLICODI
																	AND A.FCENPOSITU <> 'I'
															ORDER BY B.EORGLIDESC ";

													$res = $db->query($sql);

													if (PEAR::isError($res)) {
														$CodErroEmail  = $res->getCode();
														$DescErroEmail = $res->getMessage();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
													} else {
														while ($Linha = $res->fetchRow()) {
															if ($Linha[0] == $Orgao) {
								                   				echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
								               				} else {
								                   				echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";
															}
									    				}
													}

													$db->disconnect();
													?>
												</select>
											</td>
										</tr>
										<?php
									} else {
										echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\" width=\"30%\" height=\"20\">Órgão</td>\n";

										$db = Conexao();

										if (($_SESSION['_cgrempcodi_'] != 0 ) and ($_SESSION['_fperficorp_'] <> 'S')) {
											$ano     = date("Y");
											$usuario = $_SESSION['_cusupocodi_'];

											$sql = "SELECT	DISTINCT B.CORGLICODI, B.EORGLIDESC
													FROM	SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B
													WHERE	A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = $ano
															AND A.CORGLICODI = B.CORGLICODI
															AND A.FCENPOSITU <> 'I'
															AND A.CCENPOSEQU IN (SELECT	USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU WHERE USU.CUSUPOCODI = $usuario)
													ORDER BY 1 ";
										} else {
											$sql = "SELECT	DISTINCT A.CORGLICODI, A.EORGLIDESC
													FROM	SFPC.TBORGAOLICITANTE A
													WHERE	A.FORGLISITU <> 'I'
													ORDER BY D.EORGLIDESC ";
										}

										$res = $db->query($sql);

										if (PEAR::isError($res)) {
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();

											EmailErroDB('Erro de SQL', 'Erro de SQL', $res);
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC\n\n$DescErroEmail ($CodErroEmail)");
										} else {
											$rowsorg = $res->numRows();

											if ($rowsorg == 0) {
												echo "Nenhum Centro de Custo Ativo associado ao usuário";
											} elseif ($rowsorg == 1) {
												$Linha = $res->fetchRow();
												$_SESSION['Orgao'] = $Linha[0];
												$DescOrgao         = $Linha[1];

												echo "<td class=\"textonormal\" >$DescOrgao</td>\n";
                                                echo '<input type="hidden" name="Orgao" value="'.$Linha[0].'">';
											} else {
												echo "<td class=\"textonormal\" >";
												echo "<select name=\"Orgao\" class=\"textonormal\"><option value=\"\">Selecione um Órgao...</option>";

												while ($Linha = $res->fetchRow()) {
                                                    if ($Linha[0] == $Orgao) {
                                                        echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                    } else {
                                                        echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                    }
												}

												echo "</select>";
												echo "</td>\n";
											}
										}

										$db->disconnect();
									}
									?>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
										<td class="textonormal">
											<select name="Situacao" class="textonormal">
												<option value="T" <?php if ($Situacao == "T" || empty($Situacao)) { echo selected; } ?> >TODAS</>
												<?php
												$db = Conexao();

												$sql = "SELECT CSITSOCODI, ESITSONOME FROM SFPC.TBSITUACAOSOLICITACAO ";

												if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_MANTER) { // na exclusão não excluir solicitações concluídas
													$sql .= "WHERE CSITSOCODI in (".TIPO_SITUACAO_SCC_EM_CADASTRAMENTO.", ".TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO.", ".TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO.", ".TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO.", ".TIPO_SITUACAO_SCC_EM_ANALISE.", ".TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP.") ";
												}

												$res = $db->query($sql);

												if (PEAR::isError($res)) {
													$CodErroEmail  = $res->getCode();
													$DescErroEmail = $res->getMessage();
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
												} else {
													while ($Linha = $res->fetchRow()) {
														if ($Linha[0] == $Situacao) {
								                   			echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
								                   		} else {
								                    		echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";
												    	}
										        	}
												}

												$db->disconnect();
												?>
											</select>
										</td>
									</tr>
									<?php
									if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
										?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Compra</td>
											<td class="textonormal">
												<select name="TipoSCC" class="textonormal">
													<option value="">Selecione um tipo de compra...</option>
													<option value="T" <?php if ($TipoSCC == "T") { echo selected; } ?>>Todas</option>
													<?php
													$db = Conexao();

													$sql = "SELECT CTPCOMCODI, ETPCOMNOME FROM SFPC.TBTIPOCOMPRA WHERE CTPCOMCODI <> 5 ";

													$res = $db->query($sql);

													if (PEAR::isError($res)) {
														$CodErroEmail  = $res->getCode();
														$DescErroEmail = $res->getMessage();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
													} else {
														while ($Linha = $res->fetchRow()) {
															if ($Linha[0] == $TipoSCC) {
								                   				echo"<option value='".$Linha[0]."' selected>".$Linha[1]."</option>";
								                   			} else {
								                   				echo"<option value='".$Linha[0]."'>".$Linha[1]."</option>";
												   			}
										        		}
													}

													$db->disconnect();
													?>
													<option value="SP">SARP PARTICIPANTE</option>
													<option value="SC">SARP CARONA</option>
												</select>
											</td>
										</tr>
										<?php
									}
									?>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
										<td class="textonormal">
											<?php
											$DataMes = DataMes();

											if ($DataIni == "" or is_null($DataIni)) {
												$DataIni = "";
											}

											if ($DataFim == "" or is_null($DataFim)) {
												$DataFim = "";
											}

											$URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
											$URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
											?>
											<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni; ?>" class="textonormal">
											<a href="javascript:janela('<?php echo $URLIni; ?>','Calendario',220,170,1,0)">
												<img src="../midia/calendario.gif" border="0" alt="">
											</a>
											&nbsp;a&nbsp;
											<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim; ?>" class="textonormal">
											<a href="javascript:janela('<?php echo $URLFim; ?>','Calendario',220,170,1,0)">
												<img src="../midia/calendario.gif" border="0" alt="">
											</a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
								<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
								<input type="hidden" name="Botao" value="">
							</td>
						</tr>
						<?php
						if ($Botao == "Pesquisar" && $Mens == 0) {
							$db = Conexao();	

							// Adiciona join na consulta para trazer código do contrato.
                    		$sql = "SELECT	DISTINCT ON (SOL.CSOLCOSEQU)
											SOL.CSOLCOSEQU , SOL.ASOLCOANOS, SOL.CSOLCOCODI, SOL.TSOLCODATA, ORG.EORGLIDESC, 
                            				CEN.ECENPODESC, CEN.ECENPODETA, SOL.CSITSOCODI, SSO.ESITSONOME, CEN.CCENPOCORG,  
                            				CEN.CCENPOUNID, SOL.CSITSOCODI, ORG.CORGLICODI, CEN.FCENPOSITU, SOL.CTPCOMCODI, CS.CDOCPCSEQU
                            		FROM	SFPC.TBORGAOLICITANTE ORG, SFPC.TBCENTROCUSTOPORTAL CEN,
                            				SFPC.TBSITUACAOSOLICITACAO SSO, SFPC.TBSOLICITACAOCOMPRA SOL
                                			LEFT OUTER JOIN SFPC.TBCONTRATOSFPC CS ON SOL.CSOLCOSEQU = CS.CSOLCOSEQU
                            		WHERE	CEN.CORGLICODI = ORG.CORGLICODI ";

								if ($acaoPagina != ACAO_PAGINA_ACOMPANHAR) {
									$sql .= " AND CEN.FCENPOSITU <> 'I' ";
								}

							    $sql .= " 		AND CEN.CCENPOSEQU = SOL.CCENPOSEQU
											AND ORG.CORGLICODI = CEN.CORGLICODI
											AND SOL.CSITSOCODI = SSO.CSITSOCODI ";

								if ($Orgao != null) {
									$sql .= " AND ORG.CORGLICODI = $Orgao ";
								}

								if (SoNumeros($Situacao)) {
               						$sql .= " AND SOL.CSITSOCODI = $Situacao ";
								}

								if (SoNumeros($TipoSCC)) {
									$sql .= " AND SOL.CTPCOMCODI = $TipoSCC ";
								} elseif ($TipoSCC == "SP") {
									$sql .= " AND SOL.FSOLCORPCP = 'P' ";
								} elseif ($TipoSCC == "SC") {
									$sql .= " AND SOL.FSOLCORPCP = 'C' ";
								}

                                // Número SCC
                                if(!empty($numeroSccAtual) && !empty($seqScc)) {
                                    $sql .= " AND SOL.CSOLCOSEQU = ".$seqScc;
                                }

								if (($_SESSION['_cgrempcodi_'] <> 0 ) and ($_SESSION['_fperficorp_'] <> 'S')) {
									$sql .= " AND CEN.CCENPOSEQU  = SOL.CCENPOSEQU AND ORG.CORGLICODI = ".$_SESSION['Orgao'] ;
								}

								if ($DataIni != "" and $DataFim != "") {
									$sql .= " AND to_char(SOL.TSOLCODATA,'YYYY-MM-DD') >= '".DataInvertida($DataIni)."' AND to_char(SOL.TSOLCODATA,'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
								}

							$sql .= " ORDER BY SOL.CSOLCOSEQU, ORG.EORGLIDESC, CEN.ECENPODESC, SOL.CSOLCOSEQU, SOL.ASOLCOANOS DESC ";

							$res  = $db->query($sql);

							if (PEAR::isError($res)) {
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
							} else {
								$Qtd = $res->numRows();

								echo "<tr>\n";
								echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								
								if ($Qtd > 0) {
									$DescOrgaoAntes  = "";
									$DescCentroAntes = "";
										
									while ($Linha = $res->fetchRow()) {
										$SeqSolicitacao        = $Linha[0];
										$AnoSolicitacao        = $Linha[1];
										$Solicitacao           = $Linha[2];
										$Data                  = DataBarra($Linha[3]);
										$DescOrgao             = $Linha[4];
										$DescCentro            = $Linha[5];
										$Detalhamento          = $Linha[6];
										$DescSituacao          = $Linha[8];
										$OrgaoSofin            = $Linha[9];
										$UnidadeSofin          = $Linha[10];
										$SituacaoSCC           = $Linha[11];
										$orgaoSCC              = $Linha[12];
										$alterarSCC            = false;
										$ccSituacao            = $Linha[13];
										$tipoCompra            = $Linha[14];
										$idContratoSolicitacao = $Linha[15];

										$erroMsg = "Esta SCC não pode ser alterada/cancelada pois está em uma situação que não pode ser alterada.";

										//-------------------- BLOQUEAR PSE GERADA LICITAÇÃO HOMOLOGADA

										$sql = "SELECT	COUNT(*)
												FROM	SFPC.TBFASELICITACAO A, SFPC.TBPRESOLICITACAOEMPENHO B
												WHERE	B.CSOLCOSEQU = $SeqSolicitacao
														AND A.CLICPOPROC = B.CLICPOPROC
														AND A.ALICPOANOP = B.ALICPOANOP
														AND A.CGREMPCODI = B.CGREMPCODI
														AND A.CCOMLICODI = B.CCOMLICODI
														AND A.CORGLICODI = B.CORGLICODI
														AND CFASESCODI = 13 ";

										$resultx = $db->query($sql);
												
										if (PEAR::isError($resultx)) {
											$CodErroEmail  = $resultx->getCode();
											$DescErroEmail = $resultx->getMessage();
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}
												
										$Linha = $resultx->fetchRow();
										$regHomologado = $Linha[0];

										//------------------------------ FIM DA PSE GERADA
												
										// OBS.: TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO == PSE GERADA
										$arrayVerificacaoSarp = array(TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO, TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO);
										$arrayVerificacaoLicitacao = $arrayVerificacaoSarp;
												
										$arrayTipoCompra = array(TIPO_COMPRA_DIRETA, TIPO_COMPRA_DISPENSA, TIPO_COMPRA_INEXIGIBILIDADE);

										if (($SituacaoSCC == TIPO_SITUACAO_SCC_EM_ANALISE) or ($SituacaoSCC == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) or ($SituacaoSCC == 11) or ($SituacaoSCC == TIPO_SITUACAO_SCC_EM_LICITACAO && $_SESSION['_cperficodi_'] == 2)) {
											$alterarSCC = true;
										} elseif ($tipoCompra == TIPO_COMPRA_SARP && in_array($SituacaoSCC, $arrayVerificacaoSarp)) {
											$erroMsg = "Esta SCC não pode ser alterada/cancelada. (SARP - PSE Gerada)";
										} elseif ($tipoCompra == TIPO_COMPRA_LICITACAO && in_array($SituacaoSCC, $arrayVerificacaoLicitacao)) {
										    $erroMsg = "Esta SCC não pode ser alterada/cancelada. (Licitação - PSE Gerada)";
										} elseif (in_array($tipoCompra, $arrayTipoCompra) && $SituacaoSCC == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO && $idContratoSolicitacao != "") {
										    $erroMsg = "Esta SCC não pode ser alterada/cancelada. (Contrato associado a solicitação)";
										} elseif ($SituacaoSCC == TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
											if (!hasPSEImportadaSofin($db, $SeqSolicitacao) && intval($regHomologado) == 0) {
												$alterarSCC = true;
											} else {
												$erroMsg = "";
												
												if (hasPSEImportadaSofin($db, $SeqSolicitacao)) {
													$erroMsg .= "Esta SCC não pode ser alterada/cancelada pois o SOFIN já efetuou a importação dos dados da PSE.";
												}
														
												if (intval($regHomologado) > 0) {
													if ($erroMsg != "") {
														$erroMsg .= "";
													}	
													$erroMsg .= "Proibido Alterar, Solicitação da Situação PSE Gerada, pois ela é de uma Licitação Homologada.";
												}
											}
										} elseif ($SituacaoSCC == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO) {
											if (!hasSSCContrato($db, $SeqSolicitacao)) {
												$alterarSCC = true;
											} else {
												$erroMsg = "Esta SCC não pode ser alterada/cancelada pois já está relacionada com Contrato.";
											}
										} elseif ($SituacaoSCC == TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP) {
											$alterarSCC = true;
										} elseif ($SituacaoSCC == TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO) {
											if (administracaoOrgao($db, $orgaoSCC) == "I") {
												$alterarSCC = true;
											} else {
												$erroMsg = "Esta SCC não pode ser alterada/cancelada pois não está mais na fase de análise.";
											}
										}

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
											echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SOLICITAÇÃO</td>\n";
											echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
											echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
											echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
											echo "</tr>\n";
										}

										echo "<tr>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";
										$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $SeqSolicitacao);

										if (($acaoPagina != ACAO_PAGINA_MANTER and $acaoPagina != ACAO_PAGINA_EXCLUIR) or $alterarSCC) {
											$Url = $programaSelecao."?SeqSolicitacao=$SeqSolicitacao";
											$color = "#000000";

											echo "<a href='$Url'><font color='".$color."'>".$strSolicitacaoCodigo."</font></a>";
										} else {
											$color = "#bb8888"; 
											?>
											<div onClick='alert("<?php $erroMsg?>");' onmouseover="this.style.cursor='pointer';">
												<font color='<?php $color?>' >
													<u>
														<?php $strSolicitacaoCodigo ?>
													</u>
												</font>
											</div>
											<?php
										}
										
										if (!in_array($Url,$_SESSION['GetUrl'])) {
											$_SESSION['GetUrl'][] = $Url;
										}
										
										$avisoCCInativo = "";
										
										if ($ccSituacao=="I")
											$avisoCCInativo = "<br/>(Centro de custo inativo)";
											
										echo "	</td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><font color='".$color."'>".$Detalhamento.$avisoCCInativo."</font></td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><font color='".$color."'>$Data</font></td>\n";
										echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><font color='".$color."'>$DescSituacao</font></td>\n";
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