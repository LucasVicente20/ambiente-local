<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioExcluir.php
# Autor:    Rossana Lira
# Data:     08/04/2003
# Alterado: Álvaro Faria
# Data:     26/06/2006 - Verificação de integridade para exclusão de usuários
# Alterado: Rossana Lira
# Data:     20/05/2007 - Verificação de integridade para exclusão de usuários
# Objetivo: Programa de Exclusão do Usuário
# Alterado: Everton Lino
# Data:     08/04/2010 	- Inclusão do campo CPF.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao         = $_POST['Botao'];
		$Critica       = $_POST['Critica'];
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
}else{
		$UsuarioCodigo = $_GET['UsuarioCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioExcluir.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		if( $Critica == 1 ){
				$Mens     = 0;

				# Verifica se o usuário está excluindo a si próprio #
				if( $UsuarioCodigo == $_SESSION['_cusupocodi_'] ){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = urlencode("Exclusão Cancelada!<br>O Usuário Está Logado Atualmente");
						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit();
				}else{
						$db     = Conexao();
						# Verifica se o usuário está relacionado com alguma comissão #
						$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $UsuarioCodigo";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
						}else{
								$Linha = $result->fetchRow();
								$QtdUsuarioComissao = $Linha[0];
								if( $QtdUsuarioComissao > 0 ){
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = urlencode("Exclusão Cancelada!<br>Usuário Relacionado com ($QtdUsuarioComissao) Comissão(ões)");
										$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}else{
										# Verifica se o usuário está relacionado com alguma licitação #
										$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBLICITACAOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												$db->disconnect();
										}else{
												$Linha = $result->fetchRow();
												$QtdLicitacao = $Linha[0];
												if( $QtdLicitacao > 0 ){
														$Mens     = 1;
														$Tipo     = 2;
														$Mensagem = urlencode("Exclusão Cancelada!<br>Usuário Relacionado com ($QtdLicitacao) Licitação(ões)");
														$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit();
												}else{
														# Verifica se o usuário está relacionado com algum documento #
														$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBDOCUMENTOLICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																$db->disconnect();
														}else{
																$Linha = $result->fetchRow();
																$QtdDocumento = $Linha[0];
																if( $QtdDocumento > 0 ){
																		$Mens     = 1;
																		$Tipo     = 2;
																		$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdDocumento) Documento(s)";
																		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit();
																}else{
																		# Verifica se o usuário está relacionado com alguma Fase de Licitação #
																		$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFASELICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				$db->disconnect();
																		}else{
																				$Linha = $result->fetchRow();
																				$QtdFaseLicitacao = $Linha[0];
																				if( $QtdFaseLicitacao > 0 ){
																						$Mens     = 1;
																						$Tipo     = 2;
																						$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFaseLicitacao) Fase(s) de Licitação";
																						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																						header("location: ".$Url);
																						exit();
																				}else{
																						# Verifica se o usuário está relacionado com alguma Ata da Fase de Licitação #
																						$sql   = "SELECT COUNT(*) AS Qtd FROM SFPC.TBATASFASE WHERE CUSUPOCODI = $UsuarioCodigo";
																						$result = $db->query($sql);
																						if( PEAR::isError($result) ){
		    																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		    																				$db->disconnect();
																						}else{
																								$Linha = $result->fetchRow();
																								$QtdAtasFase = $Linha[0];
																								if( $QtdAtasFase > 0 ){
																										$Mens     = 1;
																										$Tipo     = 2;
																										$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdAtasFase) Ata(s) da Fase de Licitação";
																										$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																										header("location: ".$Url);
																										exit();
																								}else{
																										# Verifica se o usuário está relacionado com algum Resultado de Licitação #
																										$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBRESULTADOLICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
																										$result = $db->query($sql);
																										if( PEAR::isError($result) ){
																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												$db->disconnect();
																										}else{
																												$Linha = $result->fetchRow();
																												$QtdResultado = $Linha[0];
																												if( $QtdResultado > 0 ){
																														$Mens     = 1;
																														$Tipo     = 2;
																														$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdResultado) Resultado(s) de Licitação";
																														$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																														header("location: ".$Url);
																														exit();
																												}else{
																														# Verifica se o usuário está relacionado com algum Pré-fornecedor #
																														$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBPREFORNECEDOR WHERE CUSUPOCODI = $UsuarioCodigo";
																														$result = $db->query($sql);
																														if( PEAR::isError($result) ){
																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																$db->disconnect();
																														}else{
																																$Linha = $result->fetchRow();
																																$QtdPrefornecedor = $Linha[0];
																																if( $QtdPrefornecedor > 0 ){
																																		$Mens     = 1;
																																		$Tipo     = 2;
																																		$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdPrefornecedor) Fornecedor(es) Inscrito(s)";
																																		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																		header("location: ".$Url);
																																		exit();
																																}else{
																																		# Verifica se o usuário está relacionado com algum Fornecedor #
																																		$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFORNECEDORCREDENCIADO WHERE CUSUPOCODI = $UsuarioCodigo";
																																		$result = $db->query($sql);
																																		if( PEAR::isError($result) ){
																																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																				$db->disconnect();
																																		}else{
																																				$Linha = $result->fetchRow();
																																				$QtdFornecedor = $Linha[0];
																																				if( $QtdFornecedor > 0 ){
																																						$Mens     = 1;
																																						$Tipo     = 2;
																																						$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFornecedor) Fornecedor(es)";
																																						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																						header("location: ".$Url);
																																						exit();
																																				}else{
																																						# Verifica se o usuário está relacionado com alguma Certidão de  Fornecedor #
																																						$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFORNECEDORCERTIDAO WHERE CUSUPOCODI = $UsuarioCodigo";
																																						$result = $db->query($sql);
																																						if( PEAR::isError($result) ){
																																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																								$db->disconnect();
																																						}else{
																																								$Linha = $result->fetchRow();
																																								$QtdFornCert = $Linha[0];
																																								if( $QtdFornCert > 0 ){
																																										$Mens     = 1;
																																										$Tipo     = 2;
																																										$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFornCert) Certidão(ões) de Fornecedor";
																																										$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																										header("location: ".$Url);
																																										exit();
																																								}else{
																																										# Verifica se o usuário está relacionado com alguma Situação do Fornecedor #
																																										$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFORNSITUACAO WHERE CUSUPOCODI = $UsuarioCodigo";
																																										$result = $db->query($sql);
																																										if( PEAR::isError($result) ){
																																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																												$db->disconnect();
																																										}else{
																																												$Linha = $result->fetchRow();
																																												$QtdFornSit = $Linha[0];
																																												if( $QtdFornSit > 0 ){
																																														$Mens     = 1;
																																														$Tipo     = 2;
																																														$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFornSit) Situação(ões) de Fornecedor";
																																														$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																														header("location: ".$Url);
																																														exit();
																																												}else{
																																														# Verifica se o usuário está relacionado com alguma Ocorrência do Fornecedor #
																																														$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFORNECEDOROCORRENCIA WHERE CUSUPOCODI = $UsuarioCodigo";
																																														$result = $db->query($sql);
																																														if( PEAR::isError($result) ){
																																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																$db->disconnect();
																																														}else{
																																																$Linha = $result->fetchRow();
																																																$QtdFornOcorr = $Linha[0];
																																																if( $QtdFornOcorr > 0 ){
																																																		$Mens     = 1;
																																																		$Tipo     = 2;
																																																		$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFornOcorr) Ocorrência(s) de Fornecedor";
																																																		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																		header("location: ".$Url);
																																																		exit();
																																																}else{
																																																		# Verifica se o usuário está relacionado com CHF de Fornecedor #
																																																		$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFORNECEDORCHF WHERE CUSUPOCODI = $UsuarioCodigo";
																																																		$result = $db->query($sql);
																																																		if( PEAR::isError($result) ){
																																																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																				$db->disconnect();
																																																		}else{
																																																				$Linha = $result->fetchRow();
																																																				$QtdFornChf = $Linha[0];
																																																				if( $QtdFornChf > 0 ){
																																																						$Mens     = 1;
																																																						$Tipo     = 2;
																																																						$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdFornChf) CHF de Fornecedor";
																																																						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																						header("location: ".$Url);
																																																						exit();
																																																				}else{
																																																						# Verifica se o usuário está relacionado com Centro de Custo #
																																																						$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBUSUARIOCENTROCUSTO WHERE CUSUPOCODI = $UsuarioCodigo ";
																																																						$result = $db->query($sql);
																																																						if( PEAR::isError($result) ){
																																																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																								$db->disconnect();
																																																						}else{
																																																								$Linha = $result->fetchRow();
																																																								$QtdCentroCusto = $Linha[0];
																																																								if( $QtdCentroCusto > 0 ){
																																																										$Mens     = 1;
																																																										$Tipo     = 2;
																																																										$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdCentroCusto) Centro(s) de Custo(s)";
																																																										$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																										header("location: ".$Url);
																																																										exit();
																																																								}else{
																																																										# Verifica se o usuário está relacionado com Armazenamento de Material #
																																																										$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBARMAZENAMENTOMATERIAL WHERE CUSUPOCODI = $UsuarioCodigo";
																																																										$result = $db->query($sql);
																																																										if( PEAR::isError($result) ){
																																																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																												$db->disconnect();
																																																										}else{
																																																												$Linha = $result->fetchRow();
																																																												$QtdMaterial = $Linha[0];
																																																												if( $QtdMaterial > 0 ){
																																																														$Mens     = 1;
																																																														$Tipo     = 2;
																																																														$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdMaterial) Material(is) Armazenado(s)";
																																																														$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																														header("location: ".$Url);
																																																														exit();
																																																												}else{
																																																														# Verifica se o usuário está relacionado com Movimentação de Material #
																																																														$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CUSUPOCODI = $UsuarioCodigo";
																																																														$result = $db->query($sql);
																																																														if( PEAR::isError($result) ){
																																																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																$db->disconnect();
																																																														}else{
																																																																$Linha = $result->fetchRow();
																																																																$QtdMovMaterial = $Linha[0];
																																																																if( $QtdMovMaterial > 0 ){
																																																																		$Mens     = 1;
																																																																		$Tipo     = 2;
																																																																		$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdMovMaterial) Material(is) Movimentado(s)";
																																																																		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																																		header("location: ".$Url);
																																																																		exit();
																																																																}else{
																																																																		# Verifica se o usuário está relacionado com Requisição de Material #
																																																																		$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBREQUISICAOMATERIAL WHERE CUSUPOCODI = $UsuarioCodigo";
																																																																		$result = $db->query($sql);
																																																																		if( PEAR::isError($result) ){
																																																																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																				$db->disconnect();
																																																																		}else{
																																																																				$Linha = $result->fetchRow();
																																																																				$QtdReqMaterial = $Linha[0];
																																																																				if( $QtdReqMaterial > 0 ){
																																																																						$Mens      = 1;
																																																																						$Tipo      = 2;
																																																																						$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdReqMaterial) Requisição(ões) de Material";
																																																																						$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																																						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																																						header("location: ".$Url);
																																																																						exit();
																																																																				}else{
																																																																						# Verifica se o usuário está relacionado com Entrada de Nota Fiscal #
																																																																						$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBENTRADANOTAFISCAL WHERE CUSUPOCODI = $UsuarioCodigo";
																																																																						$result = $db->query($sql);
																																																																						if( PEAR::isError($result) ){
																																																																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																								$db->disconnect();
																																																																						}else{
																																																																								$Linha = $result->fetchRow();
																																																																								$QtdNotaFiscal = $Linha[0];
																																																																								if( $QtdNotaFiscal > 0 ){
																																																																										$Mens     = 1;
																																																																										$Tipo     = 2;
																																																																										$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdNotaFiscal) Nota(s) Fiscal(is) de Material";
																																																																										$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																																										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																																										header("location: ".$Url);
																																																																										exit();
																																																																								}else{
																																																																										# Verifica se o usuário está relacionado com Situação Requisição #
																																																																										$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBSITUACAOREQUISICAO WHERE CUSUPOCODI = $UsuarioCodigo";
																																																																										$result = $db->query($sql);
																																																																										if( PEAR::isError($result) ){
																																																																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																												$db->disconnect();
																																																																										}else{
																																																																												$Linha = $result->fetchRow();
																																																																												$QtdSituacaoRequisicao = $Linha[0];
																																																																												if( $QtdSituacaoRequisicao > 0 ){
																																																																														$Mens     = 1;
																																																																														$Tipo     = 2;
																																																																														$Mensagem = "Exclusão Cancelada!<br>Usuário Relacionado com ($QtdSituacaoRequisicao) Situação(ões) de Requisição";
																																																																														$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo&Mens=$Mens&Tipo=$Tipo&Mensagem=$Mensagem";
																																																																														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																																														header("location: ".$Url);
																																																																														exit();
																																																																												}else{

																																																																														# Exclui Usuario/Perfil #
																																																																														$db->query("BEGIN TRANSACTION");
																																																																														$sql    = "DELETE FROM SFPC.TBUSUARIOPERFIL WHERE CUSUPOCODI = $UsuarioCodigo";
																																																																														$result = $db->query($sql);
																																																																														if( PEAR::isError($result) ){
																																																																																$db->query("ROLLBACK");
																																																																																$db->query("END TRANSACTION");
																																																																																$db->disconnect();
																																																																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																														}else{
																																																																																# Exclui Usuario #
																																																																																$sql = "DELETE FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo ";
																																																																																$result = $db->query($sql);
																																																																																if( PEAR::isError($result) ){
																																																																																		$db->query("ROLLBACK");
																																																																																		$db->query("END TRANSACTION");
																																																																																		$db->disconnect();
																																																																																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																																																																																}else{
																																																																																		$db->query("COMMIT");
																																																																																		$db->query("END TRANSACTION");
																																																																																		$db->disconnect();
																																																																																		# Envia mensagem para página selecionar #
																																																																																		$Mensagem = urlencode("Usuário Excluído com Sucesso");
																																																																																		$Url = "TabUsuarioSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
																																																																																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																																																																																		header("location: ".$Url);
																																																																																		exit;
																																																																																}
																																																																														}
																																																																												}
																																																																										}
																																																																								}
																																																																						}
																																																																				}
																																																																		}
																																																																}
																																																														}
																																																												}
																																																										}
																																																								}
																																																						}
																																																				}
																																																		}
																																																}
																																														}
																																												}
																																										}
																																								}
																																						}
																																				}
																																		}
																																}
																														}
																												}
																										}
																								}
																						}
																				}
																		}
																}
														}
												}
										}
								}
						}
				}
		}
}

if( $Critica == 0 ){
		# Carrega os dados do usuário selecionado #
		$db     = Conexao();
		$sql    = "SELECT AUSUPOCCPF,EUSUPOLOGI, EUSUPORESP, EUSUPOMAIL, AUSUPOFONE, CGREMPCODI ";
		$sql   .= "  FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CPF         = $Linha[0];
						$Login       = $Linha[1];
						$Nome        = $Linha[2];
						$Email       = $Linha[3];
						$Fone        = $Linha[4];
						$GrupoCodigo = $Linha[5];
				}
		}

		# Carrega o perfil do usuário selecionado #
		$sql    = "SELECT CPERFICODI FROM SFPC.TBUSUARIOPERFIL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PerfilCodigo = $Linha[0];
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
function enviar(valor){
	document.Usuario.Botao.value=valor;
	document.Usuario.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUsuarioExcluir.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Manter
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
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
						EXCLUIR - USUÁRIO
					</td>
				</tr>
				<tr>
					<td class="textonormal">
						<p align="justify">
							Para confirmar a exclusão do Usuário clique no botão "Excluir", caso contrário clique no botão "Voltar".
						</p>
					</td>
				</tr>
				<tr>
					<td>
						<table border="0" summary="">
						<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF </td>
								<td class="textonormal"><?php echo $CPF; ?></td>
						</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Login </td>
								<td class="textonormal">
									<?php echo $Login; ?>
									<input type="hidden" name="Critica" value="1">
									<input type="hidden" name="UsuarioCodigo" value="<?php echo $UsuarioCodigo; ?>">
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Usuário </td>
								<td class="textonormal"><?php echo $Nome; ?></td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail </td>
									<td class="textonormal"><?php echo $Email; ?></td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone </td>
								<td class="textonormal"><?php echo $Fone; ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right">
						<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
						<input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
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
