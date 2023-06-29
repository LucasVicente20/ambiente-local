<?php
# ------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoAlterarSemBloqueio.php
# Autor:    Ariston Cordeiro
# Data:     20/05/09
# Objetivo: Programa de Alteração de Licitação Sem Bloqueio, derivado da ferramenta CadLicitacaoAlterar.php
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------
# Autor:    Pitang Agile TI
# Data :    09/04/2018
# Objetivo: CR135964 - Licitação Manter sem bloqueio
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		03/08/2018
# Objetivo: Tarefa Redmine 130314
# ------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 04/01/2019
# Objetivo: Tarefa #208518
#-----------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadLicitacaoExcluir.php' );
AddMenuAcesso( '/licitacoes/CadLicitacaoExibir.php' );
AddMenuAcesso( '/licitacoes/CadLicitacaoSelecionarSemBloqueio.php' );
AddMenuAcesso( '/licitacoes/CadLicitacaoBloqueio.php' );
AddMenuAcesso( '/oracle/licitacoes/RotValidaBloqueio.php' );

# Ano Atual do Exercicio #
$AnoExercicio = AnoExercicio();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                    = $_POST['Botao'];
	$Critica                  = $_POST['Critica'];
	$Processo                 = $_POST['Processo'];
	$ProcessoAntes            = $_POST['ProcessoAntes'];
	$ProcessoAno              = $_POST['ProcessoAno'];
	$ProcessoAnoAntes         = $_POST['ProcessoAnoAntes'];
	$ComissaoCodigo           = $_POST['ComissaoCodigo'];
	$ComissaoDescricao        = $_POST['ComissaoDescricao'];
	$ModalidadeCodigo         = $_POST['ModalidadeCodigo'];
	$ModalidadeCodigoAntes    = $_POST['ModalidadeCodigoAntes'];
	$RegistroPreco            = $_POST['RegistroPreco'];
	$Licitacao                = $_POST['Licitacao'];
	$LicitacaoAntes           = $_POST['LicitacaoAntes'];
	$LicitacaoAno             = $_POST['LicitacaoAno'];
	$LicitacaoAnoAntes        = $_POST['LicitacaoAnoAntes'];
	$LicitacaoDtAbertura      = trim($_POST['LicitacaoDtAbertura']);
	$LicitacaoHoraAbertura    = trim($_POST['LicitacaoHoraAbertura']);
	$LicitacaoDtEncerramento  = trim($_POST['LicitacaoDtEncerramento']);
	$LicitacaoHoraEncerramento= trim($_POST['LicitacaoHoraEncerramento']);
	$OrgaoLicitanteCodigo     = $_POST['OrgaoLicitanteCodigo'];
	$OrgaoLicitanteDescricao  = $_POST['OrgaoLicitanteDescricao'];
	$LicitacaoUltAlteracao    = $_POST['LicitacaoUltAlteracao'];
	$LicitacaoObjeto          = strtoupper(trim($_POST['LicitacaoObjeto']));
	$NCaracteres              = $_POST['NCaracteres'];
	$CarregaPagina            = $_POST['CarregaPagina'];
	$ValorTotal               = $_POST['ValorTotal'];
	$ValorTotalAntes          = $_POST['ValorTotalAntes'];
	$CheckBloqueio            = $_POST['CheckBloqueio'];
	$NumeroBloqueio           = $_POST['NumeroBloqueio'];
	$Bloqueios                = $_POST['Bloqueios'];
	$BloqueiosAntes           = $_POST['BloqueiosAntes'];
	$NumBloqueio              = $_POST['NumBloqueio'];
	$Dotacao                  = $_POST['Dotacao'];
	$NumDotacao               = $_POST['NumDotacao'];
	$Orgao                    = $_POST['Orgao'];
	$NumOrgao                 = $_POST['NumOrgao'];
	$Unidade                  = $_POST['Unidade'];
	$NumUnidade               = $_POST['NumUnidade'];
	$Exercicio                = $_POST['Exercicio'];
	$NumExercicio             = $_POST['NumExercicio'];
	$ExercicioBloq            = $_POST['ExercicioBloq'];
	$ExercicioBloqAntes       = $_POST['ExercicioBloqAntes'];
	$Funcao                   = $_POST['Funcao'];
	$NumFuncao                = $_POST['NumFuncao'];
	$Subfuncao                = $_POST['Subfuncao'];
	$NumSubfuncao             = $_POST['NumSubfuncao'];
	$Programa                 = $_POST['Programa'];
	$NumPrograma              = $_POST['NumPrograma'];
	$TipoProjAtiv             = $_POST['TipoProjAtiv'];
	$NumTipoProjAtiv          = $_POST['NumTipoProjAtiv'];
	$ProjAtividade    	      = $_POST['ProjAtividade'];
	$NumProjAtividade 	      = $_POST['NumProjAtividade'];
	$Elemento1                = $_POST['Elemento1'];
	$NumElemento1             = $_POST['NumElemento1'];
	$Elemento2                = $_POST['Elemento2'];
	$NumElemento2             = $_POST['NumElemento2'];
	$Elemento3                = $_POST['Elemento3'];
	$NumElemento3             = $_POST['NumElemento3'];
	$Elemento4                = $_POST['Elemento4'];
	$NumElemento4             = $_POST['NumElemento4'];
	$Fonte                    = $_POST['Fonte'];
	$NumFonte                 = $_POST['NumFonte'];
	$Valor                    = $_POST['Valor'];
	$NumValor                 = $_POST['NumValor'];
	$Total                    = $_POST['Total'];
	$FlagValorHomologado      = $_POST['FlagValorHomologado'];
	$BloqueiosDot             = $_POST['BloqueiosDot'];
	$Grupo					  = $_POST['Grupo'];
	$CodUsuario				  = $_POST['CodUsuario'];
    $licitacaoTipo            = $_POST['LicitacaoTipoSelecionado'];
    $legislacao		          = $_POST['legislacao'];
} else {
	$Processo                 = $_GET['Processo'];
	$ProcessoAno              = $_GET['ProcessoAno'];
	$ProcessoAntes            = $_GET['Processo'];
	$ProcessoAnoAntes 		  = $_GET['ProcessoAno'];
	$ComissaoCodigo           = $_GET['ComissaoCodigo'];
	$Bloqueio                 = $_GET['Bloqueio'];
	$Existe                   = $_GET['Existe'];
	$AlteraValorHomologadoBlo = $_GET['AlteraValorHomologadoBlo'];
	$ValorBlo                 = $_GET['ValorBlo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
/* Para usar excluir, é necessário alterar o arquivo de excluir para não se basear no grupo do usuário para achar a licitação
 * if( $Botao == "Excluir" ){
		$Url = "CadLicitacaoExcluir.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&FlagValorHomologado=$FlagValorHomologado";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: $Url");
	  exit;
}else*/
if ($Botao == "Exibir") {
	$Url = "CadLicitacaoExibir.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header("location: $Url");
	exit;
} elseif ($Botao == "Voltar") {
	header("location: CadLicitacaoSelecionarSemBloqueio.php");
	exit;
} elseif ($Botao == "Registro") {
	# Verifica se a Licitação tem Fase de Homologação #
	$db     = Conexao();
	
	$sql    = "SELECT COUNT(CFASESCODI) FROM SFPC.TBFASELICITACAO ";
	$sql   .= "WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoExercicio ";
	$sql   .= "AND CGREMPCODI = ".$Grupo." AND CCOMLICODI = $ComissaoCodigo ";
	$sql   .= "AND CORGLICODI = $OrgaoLicitanteCodigo AND cfasescodi = 13 ";
	
	$result = $db->query($sql);
	
	if(PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Row = $result->fetchRow();
		
		if ($Row[0] != 0) {
			if ($RegistroPreco != "") {
				$Mens          = 1;
				$Tipo          = 2;
				$Mensagem     .= "Este Processo Licitatório não pode ser modificado para Registro de Preço, pois possui valor homologado. Para efetuar este procedimento exclua a Fase de Homologação deste processo";
				$RegistroPreco = "";
			} else {
				$Mens          = 1;
				$Tipo          = 2;
				$Mensagem     .= "A informação de Registro de Preço não pode ser retirada deste processo, pois  possui valor homologado. Para efetuar este procedimento exclua a Fase de Homologação deste processo";
				$RegistroPreco = "S";
			}
		}
	}
	$db->disconnect();
} elseif ($Botao == "Licitacao") {
	$Mens          = 0;
	$Mensagem      = "Informe: ";
	$RegistroPreco = "";
	
	if ($OrgaoLicitanteCodigo == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.Licitacao.OrgaoLicitanteCodigo.focus();\" class=\"titulo2\">Órgão Licitante</a>";
	}
	
	if ($ComissaoCodigo == "") {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.Licitacao.ComissaoCodigo.focus();\" class=\"titulo2\">Comissão</a>";
	}
	
	if ($ModalidadeCodigo == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.Licitacao.ModalidadeCodigo.focus();\" class=\"titulo2\">Modalidade</a>";
	}
	
	if ($Mens == 0) {
		if ($ModalidadeCodigo != $ModalidadeCodigoAntes) {
			# Verifica o máximo número da Licitação #
			$db     = Conexao();
			$sql    = "SELECT MAX(CLICPOCODL) FROM SFPC.TBLICITACAOPORTAL ";
			$sql   .= "WHERE ALICPOANOP = $AnoExercicio AND CGREMPCODI = ".$Grupo." ";
			$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CMODLICODI = $ModalidadeCodigo ";
			
			$result = $db->query($sql);
			
			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$Linha = $result->fetchRow();
				
				if ($Linha[0] == 0) {
					$Licitacao = 1;
				} else {
					$Licitacao = $Linha[0] + 1;
				}
				
				$Licitacao = substr($Licitacao + 10000,1);
			}
			
			$db->disconnect();
		} else {
			$Licitacao = $LicitacaoAntes;
		}
	} else {
		$ModalidadeCodigo = "";
	}
} elseif ($Botao == "Alterar") {
	# Critica dos Campos #
	if ($Critica == 1) {
		if ($FlagValorHomologado == "N") {
			$Mens      = 1;
			$Tipo      = 2;
			$Virgula   = 2;
			$Mensagem  = "Este processo licitatório não pode ser alterado, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
		} else{ 
			$Mens     = 0;
			$Mensagem = "Informe: ";
			
			if ($ModalidadeCodigo == "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.ModalidadeCodigo.focus();\" class=\"titulo2\">Modalidade</a>";
			}
			
			$ValidaData = ValidaData($LicitacaoDtAbertura);
			
			if ($ValidaData != "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoDtAbertura.focus();\" class=\"titulo2\">Data Válida</a>";
			}

			$ValidaDataEncerramento = ValidaData($LicitacaoDtEncerramento);
			
			if ($ValidaDataEncerramento != "" && $legislacao == "14133") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoDtEncerramento.focus();\" class=\"titulo2\">Data de Encerramento Válida</a>";
			}
			
			$ValidaHora = ValidaHora($LicitacaoHoraAbertura);
			
			if ($ValidaHora != "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoHoraAbertura.focus();\" class=\"titulo2\">Hora Válida</a>";
			} else {
				$HhMm                  = explode(":",$LicitacaoHoraAbertura);
				$Hh                    = substr($HhMm[0] + 100,1);
				$Mm                    = substr($HhMm[1] + 100,1);
				$LicitacaoHoraAbertura = $Hh .":". $Mm;
			}

			$ValidaHoraEcenrramento = ValidaHora($LicitacaoHoraEncerramento);
			
			if ($ValidaHoraEcenrramento != "" && $legislacao == "14133") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoHoraEncerramento.focus();\" class=\"titulo2\">Hora de Encerramento Válida</a>";
			} else {
				$HhMm                  = explode(":",$LicitacaoHoraEncerramento);
				$Hh                    = substr($HhMm[0] + 100,1);
				$Mm                    = substr($HhMm[1] + 100,1);
				$LicitacaoHoraEncerramento = $Hh .":". $Mm;
			}
			
			if ($LicitacaoObjeto == "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}
				
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoObjeto.focus();\" class=\"titulo2\">Objeto</a>";
				} elseif (strlen($LicitacaoObjeto) > 900) {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.LicitacaoObjeto.focus();\" class=\"titulo2\">Objeto da Licitação com até 900 Caracteres</a>";
				}
						/*
						if( count($Bloqueios) == 0 and $RegistroPreco != "S" and $ModalidadeCodigo != 4 ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Pelo menos um Número de Bloqueio";
						}*/
				if ($CodUsuario == "") {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Licitacao.CodUsuario.focus();\" class=\"titulo2\">Usuário da comissão</a>";
				}

				if ($Mens == 0) {
					if ($ModalidadeCodigo != 10) {
						if ($ValorTotal == "") {
							if  ($Mens == 1) {
								$Mensagem .= ", ";
							}
							$Mens      = 1;
							$Tipo      = 2;
							$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado</a>";
						} else {
							if (!SoNumVirg($ValorTotal)) {
								if ($Mens == 1) {
									$Mensagem .= ", ";
								}
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado Válido</a>";
							} else {
								$Numero = Decimal($ValorTotal);
								
								if(!$Numero) {
									if ($Mens == 1) {
										$Mensagem .= ", ";
									}
									$Mens      = 1;
									$Tipo      = 2;
									$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado Válido</a>";
								} else {
									$ValorTotal = $Numero;
									
									if ($ValorTotal == 0) {
										if ($Mens == 1) {
											$Mensagem .= ", ";
										}
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Diferente de Zero</a>";
									} elseif ($ValorTotal != 0 and $RegistroPreco != "S" and $ModalidadeCodigo != 4) {
										$TotalBloqueado = sprintf("%01.2f",$Total);
										$TotalEstimado  = sprintf("%01.2f",str_replace(",",".",$ValorTotal));
										# Vê se há bloqueio (soma dos bloqueios maior que zero) #
										if ($TotalBloqueado != 0) {
											$TotalBloqueado = converte_valor($TotalBloqueado);
											$TotalEstimado  = converte_valor(sprintf("%01.2f",$TotalEstimado));
											$Mens      = 1;
											$Tipo      = 2;
											$Virgula   = 2;
											$Mensagem  = "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Licitação não deve possuir bloqueios. Para adicionar bloqueios em uma licitação sem bloqueios acesse pelo menu 'Licitações > Licitação > Manter'</a>";
																				/*
																				# Estimado diferente de bloqueado?
																				if($TotalEstimado != $TotalBloqueado){
																					$Mens      = 1;
																					$Tipo      = 2;
																					$Virgula   = 2;
																					$Mensagem  = "<a href=\"javascript:document.Licitacao.ValorTotal.focus();\" class=\"titulo2\">Valor Total Estimado (R$ $TotalEstimado) diferente do Valor Total Bloqueado (R$ $TotalBloqueado)</a>";
																				}
																				*/
											}
										}
									}
								}
							}
						}
					}
					if($Mens == 0) {
						# Verifica duplicidade de Modalidade/Número de Licitação #
						$db     = Conexao();
						$sql    = "SELECT CLICPOPROC FROM SFPC.TBLICITACAOPORTAL ";
						$sql   .= "WHERE CGREMPCODI = ".$Grupo." AND CCOMLICODI = $ComissaoCodigo ";//AND CMODLICODI = $ModalidadeCodigoAntes";
						//$sql   .= "   AND CLICPOCODL = $Licitacao "; //AND CMODLICODI = $ModalidadeCodigo  ";
						$sql   .= "AND CORGLICODI = $OrgaoLicitanteCodigo ";
						$sql   .= "AND CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
						//$sql   .= "   AND ALICPOANOL = $ProcessoAno AND (CLICPOPROC <> $Processo OR ALICPOANOP <> $ProcessoAno)";
						
						$result = $db->query($sql);
						
						if(PEAR::isError($result)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							//verifica se existe alteração de campos chave, caso não tenha permite alterar
							if ($Processo == $ProcessoAntes && $ProcessoAno == $ProcessoAnoAntes) {
								$alterandoCamposChave = false;
							} else {
								$alterandoCamposChave = true;
							}
										
							$Rows = $result->numRows();
										
							if ($Rows != 0 && $alterandoCamposChave) {
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Licitacao.Licitacao.focus();\" class=\"titulo2\">Número da Licitação/Ano já Cadastrado para esta Modalidade</a>";
							} else {
								$Data                     = date("Y-m-d H:i:s");
								$LicitacaoDtAberturaFinal = substr($LicitacaoDtAbertura,6,4)."-".substr($LicitacaoDtAbertura,3,2)."-".substr($LicitacaoDtAbertura,0,2);
								$DataHoraAbertura         = "$LicitacaoDtAberturaFinal $LicitacaoHoraAbertura:00";
								$LicitacaoDtEncerramentoFinal = substr($LicitacaoDtEncerramento,6,4)."-".substr($LicitacaoDtEncerramento,3,2)."-".substr($LicitacaoDtEncerramento,0,2);
								$DataHoraEncerramento         = "$LicitacaoDtEncerramentoFinal $LicitacaoHoraEncerramento:00";
								$ValorTotal               = str_replace(",",".",$ValorTotal);

								if($DataHoraEncerramento == "-- 00:00:00"){
									$DataHoraEncerramento = "NULL";
								}else{
									$DataHoraEncerramento = "'".$DataHoraEncerramento."'";
								}
								
								if ($RegistroPreco != "S" and $ModalidadeCodigo != 4) {
								    if ($FlagValorHomologado == "N" and $ValorTotalAntes != $ValorTotal) {
										$Mens      = 1;
										$Tipo      = 2;
										$Virgula   = 2;
										$Mensagem  = "Valor Estimado não pode ser Alterado, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
									} else {
										if ($Bloqueios) {
											$Diferenca = array_diff($BloqueiosAntes, $Bloqueios);
											
											if ($FlagValorHomologado == "N" and count($Diferenca) > 0) {
												$Mens      = 1;
												$Tipo      = 2;
												$Virgula   = 2;
												$Mensagem  = "A Licitação não pode ser Alterada, pois as informações do(s) bloqueio(s) já foram ajustadas no SOFIN";
											}
										}
									}
								}
												
								if ($Mens == 0) {
									if ($ModalidadeCodigo == 10 ) {
										$ValorTotal = 0;
									}

									$LicitacaoObjeto = removeCaracteresEspeciais($LicitacaoObjeto);
									
									# Atualiza a Licitação #
									$db->query("BEGIN TRANSACTION");
									$sql    = "UPDATE SFPC.TBLICITACAOPORTAL ";
									$sql   .= "SET CMODLICODI = $ModalidadeCodigo, CLICPOCODL = $Licitacao, ";
									$sql   .= "CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno, ";
									$sql   .= "ALICPOANOL = $LicitacaoAno, TLICPODHAB = '$DataHoraAbertura', ";
									$sql   .= "CORGLICODI = $OrgaoLicitanteCodigo, XLICPOOBJE = '$LicitacaoObjeto', ";
									$sql   .= "VLICPOVALE = $ValorTotal, CUSUPOCODI =	".$CodUsuario.", ";
									$sql   .= "TLICPOULAT = '$Data', ";
                                    $sql   .= "FLICPOTIPO = '$licitacaoTipo' ,";
                                    $sql   .= "flicpolegi  = '$legislacao' ,";
                                    $sql   .= "tlicpodhfe  = $DataHoraEncerramento	";
										if($RegistroPreco == "S" ) {
											$sql .= ", FLICPOREGP = 'S' ";
										} else {
											$sql .= ", FLICPOREGP = NULL ";
										}
									$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
									$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." ";
														
									$result = $db->query($sql);

									if (PEAR::isError($result)) {
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
									}

									if ($alterandoCamposChave) {
										$db->query("BEGIN TRANSACTION");
										# Atualiza a tabela tbitemlicitacaoportal#
										$sql    = "UPDATE SFPC.TBITEMLICITACAOPORTAL ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbitemlicitacaobloqueio#
										$sql   .= "UPDATE SFPC.TBITEMLICITACAOBLOQUEIO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= " WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbitemlicitacaodotacao#
										$sql   .= "UPDATE SFPC.TBITEMLICITACAODOTACAO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tblicitacaopendencias# 
										$sql   .= "UPDATE SFPC.TBLICITACAOPENDENCIAS ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbfaselicitacao#
										$sql   .= "UPDATE SFPC.TBFASELICITACAO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbatasfase#
										$sql   .= "UPDATE SFPC.TBATASFASE ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tblicitacaobloqueioorcament#
										$sql   .= "UPDATE SFPC.TBLICITACAOBLOQUEIOORCAMENT ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbresultadolicitacao#
										$sql   .= "UPDATE SFPC.TBRESULTADOLICITACAO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbdocumentolicitacao#
										$sql   .= "UPDATE SFPC.TBDOCUMENTOLICITACAO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tblistasolicitan#
										$sql   .= "UPDATE SFPC.TBLISTASOLICITAN ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbdataregistropreco#
										$sql   .= "UPDATE SFPC.TBATAREGISTROPRECO ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbpregaopresencial#
										$sql   .= "UPDATE SFPC.TBPREGAOPRESENCIAL ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ; ";
										# Atualiza a tabela tbsolicitacaolicitacaoportal#
										$sql   .= "UPDATE SFPC.TBSOLICITACAOLICITACAOPORTAL ";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ;";
										# Atualiza a tabela tbsolicitacaocompra#
										$sql   .= "UPDATE SFPC.TBSOLICITACAOCOMPRA";
										$sql   .= "SET CLICPOPROC = $Processo , ALICPOANOP = $ProcessoAno ";
										$sql   .= "WHERE CLICPOPROC = $ProcessoAntes AND ALICPOANOP = $ProcessoAnoAntes ";
										$sql   .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." AND CORGLICODI = $OrgaoLicitanteCodigo ;";
									}

									$result = $db->query($sql);
									
									if(PEAR::isError($result)) {
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
															/*
															 # Atualizar bloqueios
																if( $ExercicioBloqAntes != "" ){
																		# Deleta Bloqueios da Licicitacao #
																		$sql    = "DELETE FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT ";
																		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
																		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$Grupo." ";
																		$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND TUNIDOEXER = $ExercicioBloqAntes ";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																		    $result = $db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}
																for( $i=0; $i < count($Bloqueios); $i++){
																		# Insere Bloqueio #
																		if( $Funcao[$i] == "" ){ $Funcao[$i] = 0; }
																		if( $Subfuncao[$i] == "" ){ $Subfuncao[$i] = 0; }
																		if( $Programa[$i] == "" ){ $Programa[$i] = 0; }

																		$sql    = "INSERT INTO SFPC.TBLICITACAOBLOQUEIOORCAMENT ( ";
																		$sql   .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
																		$sql   .= "CORGLICODI, TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ";
																		$sql   .= "ALICBLSEQU, CLICBLFUNC, CLICBLSUBF, CLICBLPROG, ";
																		$sql   .= "CLICBLTIPA, ALICBLORDT, CLICBLELE1, CLICBLELE2, ";
																		$sql   .= "CLICBLELE3, CLICBLELE4, CLICBLFONT, TLICBLULAT ";
																		$sql   .= ") VALUES (";
																		$sql   .= "$Processo, $ProcessoAno, ".$Grupo." , $ComissaoCodigo, ";
																		$sql   .= "$OrgaoLicitanteCodigo, $ExercicioBloq[$i], $Orgao[$i], $Unidade[$i], ";
																		$sql   .= "$Bloqueios[$i], $Funcao[$i], $Subfuncao[$i], $Programa[$i], ";
																		$sql   .= "$TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], ";
																		$sql   .= "$Elemento3[$i], $Elemento4[$i], $Fonte[$i], '$Data')";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																		    $result = $db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}
												        */
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();
										
										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Licitação Alterada com Sucesso");
										$Url = "CadLicitacaoSelecionarSemBloqueio.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
										
										if (!in_array($Url,$_SESSION['GetUrl'])) {
											$_SESSION['GetUrl'][] = $Url;
										}
										
										header("location: $Url");
										exit;
									}
								}
							}
						}
						$db->disconnect();
					}
		}
	}
}

if ($Critica == 0) {
    # Carrega os dados da licitação selecionada	#
	$db  = Conexao();
	$sql = "SELECT	A.CMODLICODI, A.CLICPOCODL, A.ALICPOANOL, A.TLICPODHAB, A.XLICPOOBJE,
					A.FLICPOSTAT, B.ECOMLIDESC, A.TLICPOULAT, A.CORGLICODI, C.EORGLIDESC,
					A.VLICPOVALE, A.FLICPOREGP, A.CGREMPCODI, A.CUSUPOCODI, A.FLICPOTIPO, A.flicpolegi, A.tlicpodhfe
			FROM	SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBORGAOLICITANTE C
			WHERE	A.CLICPOPROC = $Processo
					AND A.ALICPOANOP = $ProcessoAno
					AND A.CCOMLICODI = $ComissaoCodigo
					AND A.CCOMLICODI = B.CCOMLICODI
					AND A.CGREMPCODI = B.CGREMPCODI
					AND A.CORGLICODI = C.CORGLICODI";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit();
	} else {
		while ($Linha = $result->fetchRow()) {
			$ModalidadeCodigo        = $Linha[0];
			$ModalidadeCodigoAntes   = $ModalidadeCodigo;
			$Licitacao               = substr($Linha[1] + 10000,1);
			$LicitacaoAntes          = $Licitacao;
			$LicitacaoAno            = $Linha[2];
			$LicitacaoAnoAntes       = $Linha[2];
			$LicitacaoDtAbertura     = substr($Linha[3],8,2) ."/". substr($Linha[3],5,2) ."/". substr($Linha[3],0,4);
			$LicitacaoHoraAbertura   = substr($Linha[3],11,5);
			$LicitacaoObjeto         = $Linha[4];
			$NCaracteres             = strlen($LicitacaoObjeto);
			$LicitacaoStatus         = $Linha[5];
			$ComissaoDescricao       = $Linha[6];
			$LicitacaoUltAlteracao   = substr($Linha[7],8,2) ."/". substr($Linha[7],5,2) ."/". substr($Linha[7],0,4) ." ". substr($Linha[8],11,5);
			$OrgaoLicitanteCodigo    = $Linha[8];
			$OrgaoLicitanteDescricao = $Linha[9];
			$ValorTotal              = str_replace(".",",",$Linha[10]);
			$ValorTotalAntes         = $Linha[10];
			$RegistroPreco           = $Linha[11];
			$Grupo          		 = $Linha[12];
			$CodUsuario          	 = $Linha[13];
            $licitacaoTipo           = $Linha[14];
			$legislacao              = $Linha[15];
			$LicitacaoDtEncerramento     = substr($Linha[16],8,2) ."/". substr($Linha[16],5,2) ."/". substr($Linha[16],0,4);
			$LicitacaoHoraEncerramento   = substr($Linha[16],11,5);
			if($legislacao == '8666'){
				$checked1 = 'checked';
				$checked2 = '';
			
			}else{
				$checked2 = 'checked';
				$checked1 = '';
			}

		}
	}

	if ($RegistroPreco != "S") {
		# Pega os Dados dos do Bloqueio #
		$sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ";
		$sql   .= "       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ";
		$sql   .= "       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ";
		$sql   .= "       CLICBLELE4, CLICBLFONT ";
		$sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
		$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
		$sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
		$sql   .= "   AND CGREMPCODI = ".$Grupo."";
		$sql   .= " ORDER BY ALICBLSEQU";
		
		$result = $db->query($sql);
		
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Rows = $result->numRows();
		    for ($i=0; $i < $Rows;$i++) {
				$Linha              = $result->fetchRow();
				$ExercicioBloq[$i]  = $Linha[0];
				$ExercicioDot       = $ExercicioDot."_".$ExercicioBloq[$i];
				$Orgao[$i]          = $Linha[1];
				$OrgaoDot           = $OrgaoDot."_".$Orgao[$i];
				$Unidade[$i]        = $Linha[2];
				$UnidadeDot         = $UnidadeDot."_".$Unidade[$i];
				$Bloqueios[$i]      = $Linha[3];
				$BloqueiosAntes[$i] = $Linha[3];
				$BloqueiosDot       = $BloqueiosDot."_".$Bloqueios[$i];
				$NumeroBloqueio[$i] = $Unidade[$i]."#".$Orgao[$i]."#".$Bloqueios[$i];
				$Funcao[$i]         = $Linha[4];
				$Subfuncao[$i]      = $Linha[5];
				$Programa[$i]       = $Linha[6];
				$TipoProjAtiv[$i]   = $Linha[7];
				$ProjAtividade[$i]  = $Linha[8];
				$Elemento1[$i]      = $Linha[9];
				$Elemento2[$i]      = $Linha[10];
				$Elemento3[$i]      = $Linha[11];
				$Elemento4[$i]      = $Linha[12];
				$Fonte[$i]          = $Linha[13];
				$Dotacao[$i]        = NumeroDotacao($Funcao[$i],$Subfuncao[$i],$Programa[$i],$Orgao[$i],$Unidade[$i],$TipoProjAtiv[$i],$ProjAtividade[$i],$Elemento1[$i],$Elemento2[$i],$Elemento3[$i],$Elemento4[$i],$Fonte[$i]);
			}
		}
		$db->disconnect();

		if ($BloqueiosDot != "") {
			if ($AlteraValorHomologadoBlo == "") {
				# Redireciona para a RotValidaBloqueio para Pegar o número de Bloqueio #
				$Url = "licitacoes/RotValidaBloqueio.php?NomePrograma=".urlencode("CadLicitacaoAlterar.php")."&BloqueiosDot=$BloqueiosDot&ExercicioDot=$ExercicioDot&OrgaoDot=$OrgaoDot&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&Orgao=$Orgao&UnidadeDot=$UnidadeDot&FaseCodigo=$FaseCodigo";
				
				if (!in_array($Url,$_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}
				Redireciona("$Url");
				exit;
			} else {
				$AlteraValorHomologado = explode("_",$AlteraValorHomologadoBlo);
				for ($j=1; $j < count($AlteraValorHomologado);$j++) {
					if ($AlteraValorHomologado[$j] == "N") {
						$FlagValorHomologado = "N";
					}
				}
			}
		}
		if ($ValorBlo != "") {
			$ValorBloqueado = explode("_",$ValorBlo);
			for ($j=1; $j <= count($ValorBloqueado);$j++) {
				$Valor[$j-1] = $ValorBloqueado[$j];
			}
		}
	}
}

# Constuindo o array de Bloqueios #
if ($NumBloqueio != "" and $NumDotacao != "") {
	if ($FlagValorHomologado == "N") {
		$Mens      = 1;
		$Tipo      = 2;
		$Virgula   = 2;
		$Mensagem  = "As informações do(s) bloqueio(s) não podem ser alteradas, pois já foram ajustadas no SOFIN";
	} else {
		if (!is_array($NumeroBloqueio)) {
			$NumeroBloqueio = array();
		}
		
		if (!is_array($Orgao)) {
			$Orgao = array();
		}
		
		if (!is_array($Unidade)) {
			$Unidade = array();
		}
		
		if (!is_array($Bloqueios)) {
			$Bloqueios = array();
		}
		
		if (!is_array($Exercicio)) {
			$Exercicio = array();
		}
		
		if (!is_array($Funcao)) {
			$Funcao = array();
		}
		
		if (!is_array($Subfuncao)) {
			$Subfuncao = array();
		}
		
		if (!is_array($TipoProjAtiv)) {
			$TipoProjAtiv = array();
		}
		
		if (!is_array($ProjAtividade)) {
			$ProjAtividade = array();
		}
		
		if (!is_array($Elemento1)) {
			$Elemento1 = array();
		}
		
		if (!is_array($Elemento2)) {
			$Elemento2 = array();
		}
		
		if (!is_array($Elemento3)) {
			$Elemento3 = array();
		}
		
		if (!is_array($Elemento4)) {
			$Elemento4 = array();
		}
		
		if (!is_array($Fonte)) {
			$Fonte = array();
		}
		
		if(!is_array($Dotacao)) {
			$Dotacao = array();
		}
		
		if (!is_array($Valor)) {
			$Valor = array();
		}
		
		if (!is_array($ExercicioBloq)) {
			$ExercicioBloq = array();
		}

		# Criando o Array de Classes de Fornecimento - Materiais #
		$Numero = $NumUnidade."#".$NumOrgao."#".$NumBloqueio;
		if (!in_array($Numero,$NumeroBloqueio)) {
			$NumeroBloqueio[count($NumeroBloqueio)] = $NumUnidade."#".$NumOrgao."#".$NumBloqueio;
			$Orgao[count($Orgao)]                   = $NumOrgao;
			$Unidade[count($Unidade)]               = $NumUnidade;
			$Bloqueios[count($Bloqueios)]           = $NumBloqueio;
			$Exercicio[count($Exercicio)]           = $NumExercicio;
			$Funcao[count($Funcao)]                 = $NumFuncao;
			$Subfuncao[count($Subfuncao)]           = $NumSubfuncao;
			$Programa[count($Programa)]             = $NumPrograma;
			$TipoProjAtiv[count($TipoProjAtiv)]     = $NumTipoProjAtiv;
			$ProjAtividade[count($ProjAtividade)]   = $NumProjAtividade;
			$Elemento1[count($Elemento1)]           = $NumElemento1;
			$Elemento2[count($Elemento2)]           = $NumElemento2;
			$Elemento3[count($Elemento3)]           = $NumElemento3;
			$Elemento4[count($Elemento4)]           = $NumElemento4;
			$Fonte[count($Fonte)]                   = $NumFonte;
			$Dotacao[count($Dotacao)]               = $NumDotacao;
			$Valor[count($Valor)]                   = $NumValor;
			$ExercicioBloq[count($ExercicioBloq)]   = $NumExercicio;
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Licitacao.Botao.value=valor;
	document.Licitacao.submit();
}
function ncaracteres(valor){
	document.Licitacao.NCaracteres.value = '' +  document.Licitacao.LicitacaoObjeto.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.Licitacao.NCaracteres.focus();
	}
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=no,left=270,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadLicitacaoAlterarSemBloqueio.php" method="post" name="Licitacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Manter Sem Bloqueio
		</td>
	</tr>
	<!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if ($Mens == 1) {?>
  	<tr>
  		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
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
	        		<table border="1" cellpadding="3" cellspacing="0"  bgcolor="#ffffff" bordercolor="#75ADE6" summary="" class="textonormal">
	          		<tr>
	            		<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    				MANTER - LICITAÇÃO SEM BLOQUEIO
		          		</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para atualizar a Licitação, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Licitação clique no botão "Excluir".
	        	    		O Número do Processo é um sequencial geral para cada Comissão de Licitação e o Número da Licitação depende de cada Modalidade.<br><br>
	        	    		Para adicionar um bloqueio, ou excluir a licitação sem bloqueio, utilize a ferramenta Licitações > Licitação > Manter.
	          	   		</p>
	          		</td>
		        </tr>
		        <tr>
				    <td>
				        <table border="0" width="100%" class="textonormal" summary="">
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão</td>
				              	<td class="textonormal"><?php echo $ComissaoDescricao ?></td>
				            </tr>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
				            	<td class="textonormal">
							  		<?php //echo $Processo ?>
									<input type="text" name="Processo" value="<?php echo $Processo; ?>">
							  	</td>
				            </tr>
				            <tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano</td>
				              	<td class="textonormal">
							  		<?php //echo $ProcessoAno ?>
							  		<input type="text" name="ProcessoAno" value="<?php echo $ProcessoAno; ?>">	
							  	</td>
				            </tr>
							<tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Última Alteração</td>
				              	<td class="textonormal"><?php echo $LicitacaoUltAlteracao ?></td>
				            </tr>
				            <tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Modalidade*</td>
				              	<td class="textonormal">
				                	<select name="ModalidadeCodigo" class="textonormal" onChange="javascript:enviar('Licitacao');">
							  			<option value="">Selecione uma Modalidade...</option>
								    	<?php	$db     = Conexao();
										    $sql    = "SELECT CMODLICODI, EMODLIDESC FROM	SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
										  
											$result = $db->query($sql);
											
											if (PEAR::isError($result)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											} else {
												while ($Linha = $result->fetchRow()) {
													if ($Linha[0] == $ModalidadeCodigo) {
														echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
													} else {
														echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
													}
												}
											} ?>
									</select>
				              </td>
				            </tr>
				            <!-- Caso a modalidade seja pregão eletrônico, concorrência, pregão presencial, credenciamento, tomada de preços, seleção pública ou chamamento público, apareça campo Registro de Preços -->
 							<?php if ($ModalidadeCodigo == 14 or $ModalidadeCodigo == 3 or $ModalidadeCodigo == 5 or $ModalidadeCodigo == 10 or $ModalidadeCodigo == 2 or $ModalidadeCodigo == 15 or $ModalidadeCodigo == 20) { ?>
							<tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">
				              		<!-- Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso -->
				              		Registro de Preço<?php if( $ModalidadeCodigo == 3 or $ModalidadeCodigo == 2){ echo "/Permissão Remunerada de Uso"; }?>
				              	</td>
				              	<td class="textonormal">
				              		<input type="checkbox" name="RegistroPreco" value="S" <?php if( $RegistroPreco == "S" ){ echo "checked"; } ?> onClick="javascript:enviar('Registro');">
				              	</td>
				            </tr>
				            <?php } ?>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Licitação</td>
				              	<td class="textonormal">
				              		<?php //echo $Licitacao; ?>
				              		<input type="text" name="Licitacao" value="<?php echo $Licitacao; ?>">
				              </td>
				            </tr>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano da Licitação</td>
				              	<td class="textonormal">
				              		<?php //echo $LicitacaoAno; ?>
				              		<input type="text" name="LicitacaoAno" value="<?php echo $LicitacaoAno ?>">
				              	</td>
				            </tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Legislação de compras*</td>
								<td class="textonormal" onChange="javascript:enviar('legislacao');">
								<input type="radio" id="lei8666" name="legislacao" value="8666" <?php if ($legislacao == "8666"){ ?> checked <?php } ?>>
								<label for="lei8666">Lei 8.666/1993</label><br>
								<input type="radio" id="lei14133" name="legislacao" value="14133" <?php if ($legislacao == "14133"){ ?> checked <?php } ?>>
								<label for="lei14133">Lei 14.133/2021</label>
								</td>
							</tr>
				            <tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Abertura*</td>
				              	<td class="textonormal">
									<?php $URL = "../calendario.php?Formulario=Licitacao&Campo=LicitacaoDtAbertura";?>
									<input type="text" name="LicitacaoDtAbertura" size="10" maxlength="10" value="<?php echo $LicitacaoDtAbertura ?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									<font class="textonormal">dd/mm/aaaa</font>
					      		</td>
				            </tr>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Hora de Abertura*</td>
				            	<td class="textonormal">
				                	<input type="text" name="LicitacaoHoraAbertura" size="4" maxlength="5" value="<?php echo $LicitacaoHoraAbertura ?>" class="textonormal">
				                	<font class="textonormal">hh:mm</font><br>
				              	</td>
				            </tr>
							<?php if ($legislacao == "14133"){ ?>
				            <tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Encerramento*</td>
				              	<td class="textonormal">
									<?php $URL = "../calendario.php?Formulario=Licitacao&Campo=LicitacaoDtEncerramento";?>
									<input type="text" name="LicitacaoDtEncerramento" size="10" maxlength="10" value="<?php echo $LicitacaoDtEncerramento ?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									<font class="textonormal">dd/mm/aaaa</font>
					      		</td>
				            </tr>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7" height="20">Hora de Encerramento*</td>
				            	<td class="textonormal">
				                	<input type="text" name="LicitacaoHoraEncerramento" size="4" maxlength="5" value="<?php echo $LicitacaoHoraEncerramento ?>" class="textonormal">
				                	<font class="textonormal">hh:mm</font><br>
				              	</td>
				            </tr>
							<?php } ?>
							<tr>
			    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Órgão Licitante</td>
			    	      		<td class="textonormal"><?php echo $OrgaoLicitanteDescricao?></td>
			      	    	</tr>
				            <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7">Objeto*</td>
				              	<td class="textonormal">
				                	<font class="textonormal">máximo de 900 caracteres</font>
									<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres; ?>" class="textonormal"><br>
									<textarea name="LicitacaoObjeto" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $LicitacaoObjeto ?></textarea>
						        </td>
				            </tr>
 				     	    <?php if( $ModalidadeCodigo != 10 ){ ?>
 				     	    <tr>
				            	<td class="textonormal" bgcolor="#DCEDF7">Valor Total Estimado* </td>
				              	<td class="textonormal">
				                	<input type="text" name="ValorTotal" size="17" maxlength="17" value="<?php echo $ValorTotal ?>" class="textonormal">
				              	</td>
				            </tr>
							<?php } ?>
							<tr>
				            	<td class="textonormal" bgcolor="#DCEDF7">Usuário da Comissão de Licitação*</td>
				              	<td class="textonormal">
					           		<select name="CodUsuario" class="textonormal">
									  	<option value="">Selecione um Usuário da Comissão de Licitação...</option>
								    	<?php	$sql    = "SELECT DISTINCT A.CUSUPOCODI, C.EUSUPORESP FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBUSUARIOCOMIS B, SFPC.TBUSUARIOPORTAL C ";
											$sql   .= " WHERE A.CGREMPCODI = $Grupo AND A.CCOMLICODI = $ComissaoCodigo ";
											$sql   .= " AND A.CGREMPCODI = B.CGREMPCODI AND A.CUSUPOCODI = B.CUSUPOCODI ";
											$sql   .= " AND A.CCOMLICODI = B.CCOMLICODI AND A.CGREMPCODI = C.CGREMPCODI AND A.CUSUPOCODI = C.CUSUPOCODI ";
											
											echo "[".$sql."]";
											
											$result = $db->query($sql);
											
											while ($Linha = $result->fetchRow()) {
											
												if ($Linha[0] == $CodUsuario) {
													echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
												} else {
													echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
												}
											} ?>
									</select>
								</td>
				            </tr>
                            <tr>
                                <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Critério de Julgamento*</td>
                                <td>

                                    <select name="LicitacaoTipoSelecionado" class="textonormal">-
										<?php
                                        	$sql = 	" SELECT cj.ccrjulcodi, cj.ecrjulnome ";
											$sql .= " FROM sfpc.tbcriteriojulgamento cj ";
											$sql .= " ORDER BY cj.ccrjulcodi desc";
											$result = $db->query($sql);
											while($Linha = $result->fetchRow()){
												echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
											}
                                    	?>
                                    </select>

                                </td>
                            </tr>
                            <tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Exibição Internet</td>
									<?php	if( $LicitacaoStatus == "A" ) {
											echo "<td class=\"textonormal\"> Ativa\n";
										} else {
											echo "<td class=\"textonormal\"> Inativa\n";
										} ?>
			              		</td>
							</tr>
							<?php if (($ModalidadeCodigo != "" and $RegistroPreco != "S") and $ModalidadeCodigo != 4) {
										if( $CarregaPagina == "" ){
						              		for ($i=0; $i < count($Bloqueios);$i++) {
												echo "					<input type=\"hidden\" name=\"NumeroBloqueio[$i]\" value=\"$NumeroBloqueio[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Orgao[$i]\" value=\"$Orgao[$i]\">\n";
								              	echo "					<input type=\"hidden\" name=\"Unidade[$i]\" value=\"$Unidade[$i]\">\n";
								              	echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Exercicio[$i]\" value=\"$ExercicioBloq[$i]\">\n";
												  
												# Busca a descrição da Unidade Orçamentaria #
												$sql    = "SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
												$sql   .= "WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
												$sql   .= "   AND CUNIDOCODI = $Unidade[$i]";
												
												$result = $db->query($sql);
												
												if (PEAR::isError($result)) {
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													$Linha               = $result->fetchRow();
													$UnidadeOrcament[$i] = str_replace("?","Ã",$Linha[0]);
												}
												  
												echo "					<input type=\"hidden\" name=\"Dotacao[$i]\" value=\"$Dotacao[$i]\">\n";
												echo "					<input type=\"hidden\" name=\"Funcao[$i]\" value=\"$Funcao[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Subfuncao[$i]\" value=\"$Subfuncao[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Programa[$i]\" value=\"$Programa[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"TipoProjAtiv[$i]\" value=\"$TipoProjAtiv[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"ProjAtividade[$i]\" value=\"$ProjAtividade[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Elemento1[$i]\" value=\"$Elemento1[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Elemento2[$i]\" value=\"$Elemento2[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Elemento3[$i]\" value=\"$Elemento3[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Elemento4[$i]\" value=\"$Elemento4[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Fonte[$i]\" value=\"$Fonte[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"Valor[$i]\" value=\"$Valor[$i]\">\n";
						              			echo "					<input type=\"hidden\" name=\"ExercicioBloq[$i]\" value=\"$ExercicioBloq[$i]\">\n";
												  
												  $ExercicioBloqAntes = $ExercicioBloq[$i];
				              				}
											  
											if (is_array($Valor)) {
												$Total = array_sum($Valor);
											}
											  
											$CarregaPagina = 1;
										} else {
									        if (count($Bloqueios) != 0) {
												for ($i=0; $i< count($Bloqueios);$i++) {
								              		echo "					<input type=\"hidden\" name=\"NumeroBloqueio[$i]\" value=\"$NumeroBloqueio[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Orgao[$i]\" value=\"$Orgao[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Unidade[$i]\" value=\"$Unidade[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Exercicio[$i]\" value=\"$ExercicioBloq[$i]\">\n";
													  
													# Busca a descrição da Unidade Orçamentaria #
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
													  
													echo "					<input type=\"hidden\" name=\"Dotacao[$i]\" value=\"$Dotacao[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Funcao[$i]\" value=\"$Funcao[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Subfuncao[$i]\" value=\"$Subfuncao[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Programa[$i]\" value=\"$Programa[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"TipoProjAtiv[$i]\" value=\"$TipoProjAtiv[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"ProjAtividade[$i]\" value=\"$ProjAtividade[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Elemento1[$i]\" value=\"$Elemento1[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Elemento2[$i]\" value=\"$Elemento2[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Elemento3[$i]\" value=\"$Elemento3[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Elemento4[$i]\" value=\"$Elemento4[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Fonte[$i]\" value=\"$Fonte[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"Valor[$i]\" value=\"$Valor[$i]\">\n";
								              		echo "					<input type=\"hidden\" name=\"ExercicioBloq[$i]\" value=\"$ExercicioBloq[$i]\">\n";
								              		
						              			}
						              			if (is_array($Valor)) {
													$Total = array_sum($Valor);
												}
						              		}
						              	} ?>
				            	  		
										<input type="hidden" name="CarregaPagina" value=" <?php echo $CarregaPagina;?> ">
				            	  		<input type="hidden" name="Total" value="<?php echo $Total;?>">
				            	  		<input type="hidden" name="NumBloqueio" value="<?php echo $NumBloqueio;?>">
				            	  		<input type="hidden" name="NumOrgao" value="<?php echo $NumOrgao;?>">
				            	  		<input type="hidden" name="NumUnidade" value="<?php echo $NumUnidade;?>">
				            	  		<input type="hidden" name="NumExercicio" value="<?php echo $NumExercicio; ?>">
				            	  		<input type="hidden" name="NumFuncao" value="<?php echo $NumFuncao;?>">
				            	  		<input type="hidden" name="NumSubfuncao" value="<?php echo $NumSubfuncao;?>">
				            	  		<input type="hidden" name="NumPrograma" value="<?php echo $NumPrograma;?>">
				            	  		<input type="hidden" name="NumTipoProjAtiv" value="<?php echo $NumTipoProjAtiv;?>">
				            	  		<input type="hidden" name="NumProjAtividade" value="<?php echo $NumProjAtividade;?>">
				            	  		<input type="hidden" name="NumElemento1" value="<?php echo $NumElemento1; ?>">
				            	  		<input type="hidden" name="NumElemento2" value="<?php echo $NumElemento2; ?>">
				            	  		<input type="hidden" name="NumElemento3" value="<?php echo $NumElemento3; ?>">
				            	  		<input type="hidden" name="NumElemento4" value="<?php echo $NumElemento4; ?>">
				            	  		<input type="hidden" name="NumFonte" value="<?php echo $NumFonte; ?>">
				            	  		<input type="hidden" name="NumDotacao" value="<?php echo $NumDotacao; ?>">
				            	  		<input type="hidden" name="NumValor" value="<?php echo $NumValor; ?>">
				            	  		<input type="hidden" name="FlagValorHomologado" value="<?php echo $FlagValorHomologado; ?>">
				            	  	<?php 	} ?>
				        </table>
				    </td>
				</tr>
			    <tr>
			        <td class="textonormal" align="right">
        	  			<?php	if ($RegistroPreco != "S") {
		        	  				if (count($BloqueiosAntes) > 0) {
		        	  					for ($i=0; $i< count($BloqueiosAntes);$i++) {
				              				echo "<input type=\"hidden\" name=\"BloqueiosAntes[$i]\" value=\"$BloqueiosAntes[$i]\">\n";
				              			}
				            		} else { 
				            			echo "<input type=\"hidden\" name=\"BloqueiosAntes[0]\" value=\"\">\n";
				            		}
				        		} ?>
		              	<input type="hidden" name="Critica" value="1">
				        <input type="hidden" name="ModalidadeCodigoAntes" value="<?php echo $ModalidadeCodigoAntes;?>">
				        <input type="hidden" name="LicitacaoAntes" value="<?php echo $LicitacaoAntes;?>">
						<input type="hidden" name="LicitacaoAnoAntes" value="<?php echo $LicitacaoAnoAntes;?>">
				        <input type="hidden" name="ExercicioBloqAntes" value="<?php echo $ExercicioBloqAntes;?>">
				 	    <input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
		              	<input type="hidden" name="ComissaoDescricao" value="<?php echo $ComissaoDescricao; ?>">
		              	<input type="hidden" name="ProcessoAntes" value="<?php echo $ProcessoAntes; ?>">
		              	<input type="hidden" name="ProcessoAnoAntes" value="<?php echo $ProcessoAnoAntes; ?>">
		              	<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>">
		              	<input type="hidden" name="OrgaoLicitanteDescricao" value="<?php echo $OrgaoLicitanteDescricao; ?>">
		              	<input type="hidden" name="LicitacaoUltAlteracao" value="<?php echo $LicitacaoUltAlteracao; ?>">
		              	<input type="hidden" name="ValorTotalAntes" value="<?php echo $ValorTotalAntes; ?>">
		              	<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
						<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Exibir" class="botao" onclick="javascript:enviar('Exibir');">
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
<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
<script language="javascript" type="">
<!--
document.Licitacao.ModalidadeCodigo.focus();
//-->
</script>
