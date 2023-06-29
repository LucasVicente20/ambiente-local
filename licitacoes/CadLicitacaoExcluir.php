<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadLicitacaoExcluir.php
 * Autor:    Rossana Lira
 * Data:     14/04/2003
 * --------------------------------------------------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     24/05/2007
 * Objetivo: Liberar Permissão Remunerada de Uso para Tomada de Preços
 * --------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston
 * Data:     26/05/2011
 * Objetivo: Salvar usuário responsável pela exclusão na tabela de log
 * --------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/01/2023
 * Objetivo: Correções na tela - atualização do PHP
 * --------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/CadLicitacaoAlterar.php');
AddMenuAcesso('/licitacoes/CadLicitacaoSelecionar.php');

# Ano Atual do Exercicio #
$AnoExercicio = AnoExercicio();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao               = $_POST['Botao'];
	$Critica             = $_POST['Critica'];
	$Processo            = $_POST['Processo'];
	$ProcessoAno         = $_POST['ProcessoAno'];
	$ComissaoCodigo      = $_POST['ComissaoCodigo'];
	$Unidade             = $_POST['Unidade'];
	$Orgao               = $_POST['Orgao'];
	$FlagValorHomologado = $_POST['FlagValorHomologado'];
} else {
	$Processo            = $_GET['Processo'];
	$ProcessoAno         = $_GET['ProcessoAno'];
	$ComissaoCodigo      = $_GET['ComissaoCodigo'];
	$FlagValorHomologado = $_GET['FlagValorHomologado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadLicitacaoExcluir.php";

# Critica dos Campos #
if ($Botao == "Voltar") {
	$Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: $Url");
	exit;
} elseif ($Botao == "Excluir") {
	$Mens = 0;

	if ($Critica == 1) {
		# Verifica se a Licitacao está relacionada com algum documento #
		$db = Conexao();

		$sql  = "SELECT COUNT(*) FROM SFPC.TBDOCUMENTOLICITACAO ";
		$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";

		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			while ($Linha = $result->fetchRow()) {
				$QtdDocumentos = $Linha[0];
			}

			if ($QtdDocumentos > 0) {
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "Exclusão Cancelada!<br>Licitação Relacionada com ($QtdDocumentos) Documento(s)";
			}

			# Verifica se a Licitacao está relacionada com alguma fase #
			$sql  = "SELECT COUNT(*) FROM SFPC.TBFASELICITACAO ";
			$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
			$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";

			$result = $db->query($sql);

			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				while ($Linha = $result->fetchRow()) {
					$QtdFases = $Linha[0];
				}

				if ($QtdFases > 0) {
					if ($Mens == 1) {
						$Mensagem .= "<br>";
					} else {
						$Mensagem .= "Exclusão Cancelada!<br>";
					}
					
					$Mens      = 1;
					$Mensagem .= "Licitação Relacionada com ($QtdFases) Fase(s)";
				}

				# Verifica se a Licitacao está relacionada com algum resultado #
				$sql    = "SELECT COUNT(*) FROM SFPC.TBRESULTADOLICITACAO ";
				$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
				$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";

				$result = $db->query($sql);

				if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					while ($Linha = $result->fetchRow()) {
						$QtdResultados = $Linha[0];
					}

					if ($QtdResultados > 0) {
						if ($Mens == 1) {
							$Mensagem .= "<br>";
						} else {
							$Mensagem .= "Exclusão Cancelada!<br>";
						}

						$Mens      = 1;
						$Mensagem .= "Licitação Relacionada com ($QtdResultados) Resultado(s)";
					}

					# Verifica se a Licitacao está relacionada com alguma lista de solicitante #
					$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBLISTASOLICITAN ";
					$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
					$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";

					$result = $db->query($sql);

					if (PEAR::isError($result)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						while ($Linha = $result->fetchRow()) {
							$QtdLista = $Linha[0];
						}

						if ($QtdLista > 0) {
							if ($Mens == 1) {
								$Mensagem .= "<br>";
							} else {
								$Mensagem .= "Exclusão Cancelada!<br>";
							}

							$Mens = 1;
							$Tipo = 2;
							$Mensagem .= "Licitação Relacionada com ($QtdLista) Solicitante(s)";
						}

						if ($Mens == 0) {
					    	if ($FlagValorHomologado == "N" and $RegistroPreco != "S") {
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 2;
								$Mensagem  = "A Licitação não pode ser Excluída, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
								$Url = "CadLicitacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";

								if (!in_array($Url,$_SESSION['GetUrl'])) {
									$_SESSION['GetUrl'][] = $Url;
								}

								header("location: $Url");
								exit;
							} else {
								# Exclui as Pendências #
								$db->query("BEGIN TRANSACTION");

								$sql    = "DELETE FROM SFPC.TBLICITACAOPENDENCIAS ";
					    		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
								$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";

								if (PEAR::isError($result)) {
									$db->query("ROLLBACK");
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
									# Apaga o(s) Número(s) de Bloqueio #
									$sql    = "DELETE FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT ";
									$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
									$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";

									$result = $db->query($sql);

									if (PEAR::isError($result)) {
										$db->query("ROLLBACK");
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										# Exclui a Licitação #
										$sql    = "DELETE FROM SFPC.TBLICITACAOPORTAL ";
										$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
										$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";

										$result = $db->query($sql);

										if (PEAR::isError($result)) {
											$db->query("ROLLBACK");
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}

										# Adiciona Usuário no último registro da tabela de log #
										$sql = "UPDATE SFPC.TBLICITACAO_LOG
												SET    cusupocodi = ".$_SESSION['_cusupocodi_']."
												WHERE  cusupocodi is NULL AND
													   clplogcodi = (select last_value from SFPC.TBlicitacao_log_clplogcodi_sequ) ";

										$result = $db->query($sql);

										if (PEAR::isError($result)) {
											$db->query("ROLLBACK");
											EmailErroSQL("Erro no SQL", __FILE__, __LINE__, "Erro no SQL", $sql, $result);
										}
									}

									$db->query("COMMIT");
									$db->query("END TRANSACTION");
									$db->disconnect();

									# Envia mensagem para página selecionar #
									$Critica  = 0;
									$Mensagem = urlencode("Licitação Excluída com Sucesso");
									$Url = "CadLicitacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";

									if (!in_array($Url,$_SESSION['GetUrl'])) {
										$_SESSION['GetUrl'][] = $Url;
									}

									header("location: $Url");
									exit;
								}
							}

							$db->disconnect();
						} else {
							$Url = "CadLicitacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";

							if (!in_array($Url,$_SESSION['GetUrl'])) {
								$_SESSION['GetUrl'][] = $Url;
							}
							
							header("location: $Url");
							exit;
						}
					}
				}
			}
		}
	}
}

if ($Critica == 0) {
	# Carrega os dados de acordo com a Licitação selecionada #
	$db = Conexao();

	$sql  = "SELECT A.CLICPOCODL, A.ALICPOANOP, A.TLICPODHAB, A.XLICPOOBJE, ";
	$sql .= "       B.ECOMLIDESC, C.EMODLIDESC, D.EGREMPDESC, E.EORGLIDESC, ";
	$sql .= "       A.VLICPOVALE, A.CORGLICODI, A.CMODLICODI, A.FLICPOREGP ";
	$sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBMODALIDADELICITACAO C, ";
	$sql .= "       SFPC.TBGRUPOEMPRESA D, SFPC.TBORGAOLICITANTE E ";
	$sql .= " WHERE A.CLICPOPROC = $Processo AND A.ALICPOANOP = $ProcessoAno ";
	$sql .= "   AND A.CCOMLICODI = $ComissaoCodigo AND A.CCOMLICODI = B.CCOMLICODI ";
	$sql .= "   AND A.CMODLICODI = C.CMODLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
	$sql .= "   AND A.CORGLICODI = E.CORGLICODI";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}

	while ($Linha = $result->fetchRow()) {
		$Licitacao               = substr($Linha[0] + 10000,1);
		$LicitacaoAno            = $Linha[1];
		$LicitacaoDtAbertura     = substr($Linha[2],8,2) ."/". substr($Linha[2],5,2) ."/". substr($Linha[2],0,4);
		$LicitacaoHoraAbertura   = substr($Linha[2],11,5);
		$LicitacaoObjeto         = nl2br($Linha[3]);
		$ComissaoDescricao       = $Linha[4];
		$ModalidadeDescricao     = $Linha[5];
		$GrupoDescricao          = $Linha[6];
		$OrgaoLicitanteDescricao = $Linha[7];
		$ValorTotal              = converte_valor($Linha[8]);
		$OrgaoLicitanteCodigo    = $Linha[9];
		$ModalidadeCodigo        = $Linha[10];
		$RegistroPreco           = $Linha[11];
	}

	# Pega os Dados dos do Bloqueio #
	$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ";
	$sql   .= "       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ";
	$sql   .= "       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ";
	$sql   .= "       CLICBLELE4, CLICBLFONT ";
	$sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
	$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
	$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "   AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
	$sql   .= " ORDER BY ALICBLSEQU";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
    	$Rows = $result->numRows();

		for ($i=0; $i < $Rows;$i++) {
			$Linha             = $result->fetchRow();
			$ExercicioBloq[$i] = $Linha[0];
			$Orgao[$i]         = $Linha[1];
			$Unidade[$i]       = $Linha[2];
			$Bloqueios[$i]     = $Linha[3];
			$Funcao[$i]        = $Linha[4];
			$Subfuncao[$i]     = $Linha[5];
			$Programa[$i]      = $Linha[6];
			$TipoProjAtiv[$i]  = $Linha[7];
			$ProjAtividade[$i] = $Linha[8];
			$Elemento1[$i]     = $Linha[9];
			$Elemento2[$i]     = $Linha[10];
			$Elemento3[$i]     = $Linha[11];
			$Elemento4[$i]     = $Linha[12];
			$Fonte[$i]         = $Linha[13];
			$Dotacao[$i]       = NumeroDotacao($Funcao[$i],$Subfuncao[$i],$Programa[$i],$Orgao[$i],$Unidade[$i],$TipoProjAtiv[$i],$ProjAtividade[$i],$Elemento1[$i],$Elemento2[$i],$Elemento3[$i],$Elemento4[$i],$Fonte[$i]);
		}
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
	function enviar(valor) {
		document.Licitacao.Botao.value=valor;
		document.Licitacao.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadLicitacaoExcluir.php" method="post" name="Licitacao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Manter
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
 				<tr>
 					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					EXCLUIR - LICITAÇÃO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal">
             					<p align="justify">
               						Para confirmar a exclusão da Licitação clique no botão "Excluir", caso contrário clique no botão "Voltar".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table border="0" width="100%" summary="">
      	    						<tr>
    	      							<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão Licitante</td>
    	      							<td class="textonormal"><?php echo $OrgaoLicitanteDescricao; ?></td>
      	    						</tr>
              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão</td>
               							<td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
              						</tr>
             						<tr>
              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
      	    							<td class="textonormal"><?php echo $Processo; ?></td>
        	  						</tr>
									<tr>
              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano</td>
      	    							<td class="textonormal"><?php echo $LicitacaoAno; ?></td>
        	  						</tr>
        							<tr>
    	      							<td class="textonormal" bgcolor="#DCEDF7" height="20">Modalidade</td>
    	      							<td class="textonormal"><?php echo $ModalidadeDescricao; ?></td>
      	    						</tr>
									<?php
									if ($RegistroPreco == "S") {
										?>
										<tr>
	              							<td class="textonormal" bgcolor="#DCEDF7" height="20">
	              								<!-- Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso -->
	              								Registro de Preço
												<?php 
												if ($ModalidadeCodigo == 3 or $ModalidadeCodigo == 2) {
													echo "/Permissão Remunerada de Uso";
												}
												?>
	              							</td>
	              							<td class="textonormal">
												<?php echo "SIM"; ?>
											</td>
	            						</tr>
	            						<?php
									}
									?>
             						<tr>
              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Licitação</td>
      	    							<td class="textonormal">
											<?php echo $Licitacao; ?>
										</td>
        	  						</tr>
									<tr>
              							<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano da Licitação</td>
      	    							<td class="textonormal">
											<?php echo $LicitacaoAno; ?>
										</td>
        	  						</tr>
									<tr>
    	      							<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Abertura</td>
    	      							<td class="textonormal">
											<?php echo $LicitacaoDtAbertura; ?>
										</td>
      	    						</tr>
									<tr>
    	      							<td class="textonormal" bgcolor="#DCEDF7" height="20">Hora de Abertura</td>
    	      							<td class="textonormal">
											<?php echo $LicitacaoHoraAbertura; ?>
										</td>
      	    						</tr>
      	    						<tr>
    	      							<td class="textonormal" bgcolor="#DCEDF7" height="20">Objeto</td>
    	      							<td class="textonormal" width="75%">
											<?php echo $LicitacaoObjeto; ?>
										</td>
      	    						</tr>
      	    						<?php
									if ($ModalidadeCodigo != 10) {
										?>
      	    							<tr>
    	      								<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Total Estimado</td>
    	      								<td class="textonormal" width="75%">
												<?php echo $ValorTotal; ?>
											</td>
      	    							</tr>
  										<?php
									}

									if ($RegistroPreco != "S") {
										?>
      	    							<tr>
	              							<td class="textonormal" colspan="2">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" class="textonormal" summary="">
			          								<tr>
	              										<td bgcolor="#75ADE6" class="textoabasoff" colspan="4" align="center">BLOQUEIOS</td>
	              									</tr>
				            						<?php
				            						if (count($Bloqueios) != 0) {
														echo "			<tr>\n";
	              										echo "				<td bgcolor=\"#DCEDF7\" class=\"textoabason\" width=\"7%\">EXERCÍCIO</td>\n";
	              										echo "				<td bgcolor=\"#DCEDF7\" class=\"textoabason\" width=\"7%\">NÚMERO</td>\n";
	              										echo "				<td bgcolor=\"#DCEDF7\" class=\"textoabason\" width=\"*\">UNIDADE ORÇAMENTÁRIA</td>\n";
	              										echo "				<td bgcolor=\"#DCEDF7\" class=\"textoabason\" width=\"25%\">DOTAÇÃO</td>\n";
	              										echo "			</tr>\n";

			              								for ($i=0; $i< count($Bloqueios);$i++) {
			              									echo "			<tr>\n";
			              									echo "				<td class=\"textonormal\">$ExercicioBloq[$i]</td>\n";
			              									echo "				<td class=\"textonormal\">\n";
			              									echo "					".$Orgao[$i].".".sprintf("%02d",$Unidade[$i]).".1.".$Bloqueios[$i]."\n";
			              									echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
			              									echo "				</td>\n";
			              									echo "				<td class=\"textonormal\">\n";

 													  		# Busca a descrição da Unidade Orçamentaria #
 													  		$db = Conexao();

													  		$sql    = "SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
															$sql   .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
															$sql   .= "   AND CUNIDOCODI = $Unidade[$i]";

															$result = $db->query($sql);

															if (PEAR::isError($result)) {
														    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															} else {
																$Linha               = $result->fetchRow();
																$UnidadeOrcament[$i] = str_replace("?","Ã",$Linha[0]);
															}

															$db->disconnect();

															echo "					$UnidadeOrcament[$i]\n";
			              									echo "				</td>\n";
			              									echo "				<td class=\"textonormal\">\n";
					           								echo "					$Dotacao[$i]\n";
			              									echo "				</td>\n";
					    	      							echo "			</tr>\n";
	              										}
	              									}
	              									?>
			          							</table>
			        						</td>
			     						</tr>
			     						<?php
									}
									?>
      	    					</table>
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" align="right">
          						<input type="hidden" name="Critica" value="1">
            					<input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
            					<input type="hidden" name="Orgao" value="<?php echo $Orgao; ?>">
    							<input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
          						<input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno; ?>">
          						<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
       							<input type="hidden" name="FlagValorHomologado" value="<?php echo $FlagValorHomologado; ?>">
				    			<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
            					<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
            					<input type="hidden" name="Botao" value="">
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