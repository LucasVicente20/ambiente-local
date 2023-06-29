<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCargaInicialFixaSemChecar.php
# Autor:    Roberta Costa
# Objetivo: Programa de Carga Inicial em Estoque Fixo
#------------------------------------------
# Data:     09/08/05
# Alterado:	Marcus Thiago
# Data:			04/01/2006
# Alterado: Ariston Cordeiro
# Data:     03/01/2011 - Preenchendo campos de estoque real e virtual na tabela de armazenamento, para não ficarem nulos
#------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
//Seguranca();

// Manter este header para controle de segurança - não apagar -> header("location: CadIncluirItem.php");
// Manter este header para controle de segurança - não apagar -> header("location: CadItemDetalhe.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$InicioPrograma      = $_POST['InicioPrograma'];
		$Montou              = $_POST['Montou'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$DescAlmoxarifado    = $_POST['DescAlmoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao	       = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		$CheckItem           = $_POST['CheckItem'];
		$Material            = $_POST['Material'];
		$MaterialRequisicao  = $_POST['MaterialRequisicao'];
		$DescMaterial        = $_POST['DescMaterial'];
		$Unidade             = $_POST['Unidade'];
		$QtdEstocada         = $_POST['QtdEstocada'];
		$ValorUnitario       = $_POST['ValorUnitario'];
		$QtdRequisicao       = $_POST['QtdRequisicao'];
		$QtdNotaFiscal       = $_POST['QtdNotaFiscal'];
		$QtdInventario       = $_POST['QtdInventario'];
		$QtdMovimentacao     = $_POST['QtdMovimentacao'];
		for( $i=0;$i<count($DescMaterial);$i++ ){
				$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstocada[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$QtdRequisicao[$i].$SimboloConcatenacaoArray.$QtdNotaFiscal[$i].$SimboloConcatenacaoArray.$QtdInventario[$i].$SimboloConcatenacaoArray.$QtdMovimentacao[$i];
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if( $Botao == "Voltar" ){
		header("location: CadCargaInicialFixaSemChecar.php");
		exit;
}elseif( $Botao == "Carregar" ){
		unset($_SESSION['item']);
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		} elseif ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCargaInicialFixaSemChecar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		} elseif ($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCargaInicialFixaSemChecar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if( count($QtdEstocada) != 0 ){
				if( $Existe == "" ){
						$Posicao = "";
						for( $i=0;$i<count($QtdEstocada);$i++ ){
								if( ( ! SoNumVirg($QtdEstocada[$i]) ) and $QtdEstocada[$i] != "" and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
						if( $Existe == "" ){
								for( $k=0;$k<count($QtdEstocada);$k++ ){
										if( ( ! SoNumVirg($QtdEstocada[$i]) ) and $QtdEstocada[$i] != "" and str_replace(",",".",$QtdEstocada[$k]) != 0 and $Existe == "" ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if( $Existe == "" ){
								for( $j=0;$j<count($QtdEstocada);$j++ ){
										if( ( ! Decimal($QtdEstocada[$j]) ) and $Existe == "" ){
												$Existe  = "S";
												$Posicao = $j;
										}
								}
						}
						if( $Existe == "S" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Posicao   = ( $Posicao * 11 ) + 11;
					  		$Mens      = 1;
					  		$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadCargaInicialFixaSemChecar.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Válida</a>";
						}
				}
		}
		if( count($ValorUnitario) != 0 and $Existe == "" ){
				if( $Existe == "" ){
						$PosVal = "";
						for( $i=0;$i<count($ValorUnitario);$i++ ){
								if( ( ! SoNumVirg($ValorUnitario[$i]) ) and $ValorUnitario[$i] != "" and $Existe == "" ){
										$Existe = "S";
										$PosVal = $i;
								}
						}
						if( $Existe == "" ){
								for( $k=0;$k<count($ValorUnitario);$k++ ){
										if( ( ! SoNumVirg($ValorUnitario[$k]) ) and $ValorUnitario[$k] != "" and str_replace(",",".",$ValorUnitario[$k]) != 0 and $Existe == "" ){
												$Existe = "S";
												$PosVal = $k;
										}
								}
						}
						if( $Existe == "" ){
								for( $j=0;$j<count($ValorUnitario);$j++ ){
										if( ( ! DecimalValor($ValorUnitario[$j]) ) and $Existe == "" ){
												$Existe = "S";
												$PosVal = $j;
										}
								}
						}
						if( $Existe == "S" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$PosVal    = ( $PosVal * 11 ) + 12;
					  		$Mens      = 1;
					  		$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadCargaInicialFixaSemChecar.elements[$PosVal].focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
						}
				}
		}
	  if( $Mens == 0 ) {
			  $db = Conexao();
			  $db->query("BEGIN TRANSACTION");

				# Apaga item da carga inicial - Verifica se o item está ligado a alguma requisição, nota fiscal, inventário ou movimentação #
				for( $i=0; $i< count($_SESSION['ItemDelete']) and $Rollback == ""; $i++ ){
						$Dados          = explode($SimboloConcatenacaoDesc,$_SESSION['ItemDelete'][$i]);
						$MaterialDel    = $Dados[0];
						$LocalizacaoDel = $Dados[1];

						/*# Verifica se o Material está ligado a alguma Requisição #
						$sqlreq  = "SELECT COUNT(A.CMATEPSEQU) ";
						$sqlreq .= " 	FROM SFPC.TBITEMREQUISICAO A, SFPC.TBREQUISICAOMATERIAL B, ";
						$sqlreq .= " 	     SFPC.TBALMOXARIFADOORGAO C  ";
						$sqlreq .= " WHERE A.CMATEPSEQU = $MaterialDel AND A.CREQMASEQU = B.CREQMASEQU";
						$sqlreq .= "   AND C.CALMPOCODI = $Almoxarifado AND B.CORGLICODI = C.CORGLICODI";
						$resreq  = $db->query($sqlreq);
						if( PEAR::isError($resreq) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlreq");
						}else{
								$Qtd = $resreq->fetchRow();
								if( $Qtd[0] > 0 ){
							  		$Mens      = 1;
							  		$Tipo      = 2;
										$Mensagem .= "O Material não pode ser Retirado! Existe ($Qtd[0]) Requisição(ões) com este item - $DescMaterial[$i]";
								}else{
										# Verifica se o Material está ligado a alguma Nota Fiscal #
										$sqlnf  = "SELECT COUNT(A.CMATEPSEQU) ";
										$sqlnf .= " 	FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B ";
										$sqlnf .= " WHERE A.CMATEPSEQU = $MaterialDel AND A.AENTNFANOE = B.AENTNFANOE ";
										$sqlnf .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CENTNFCODI = B.CENTNFCODI ";
										$sqlnf .= "   AND A.CALMPOCODI = B.CALMPOCODI ";
										$resnf  = $db->query($sqlnf);
										if( PEAR::isError($resnf) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
										}else{
												$Qtd = $resnf->fetchRow();
												if( $Qtd[0] > 0 ){
											  		$Mens      = 1;
											  		$Tipo      = 2;
														$Mensagem .= "O Material não pode ser Retirado! Existe ($Qtd[0]) Nota(s) Fiscal(is) com este item - $DescMaterial[$i]";
												}else{
														# Verifica se o Material está ligado a algum Inventário #
														$sqlinv  = "SELECT COUNT(A.CMATEPSEQU) ";
														$sqlinv .= " 	FROM SFPC.TBINVENTARIOMATERIAL A, SFPC.TBINVENTARIOCONTAGEM B ";
														$sqlinv .= " WHERE A.CMATEPSEQU = $MaterialDel AND A.AINVCOANOB = B.AINVCOANOB";
														$sqlinv .= "   AND B.CLOCMACODI = $LocalizacaoDel AND A.AINVCOSEQU = B.AINVCOSEQU";
														$sqlinv .= "   AND B.FINVCOFECHA = 'S' ";
														$resinv  = $db->query($sqlinv);
														if( PEAR::isError($resinv) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlinv");
														}else{
																$Qtd = $resinv->fetchRow();
																if( $Qtd[0] > 0 ){
															  		$Mens      = 1;
															  		$Tipo      = 2;
																		$Mensagem .= "O Material não pode ser Retirado! Existe ($Qtd[0]) Inventário(s) com este item - $DescMaterial[$i]";
																}else{
																		# Verifica se o item está em Movimentação diferente do tipo 1 - SALDO INICIAL ESTOQUE(Carga Inicial)#
																		$sqlmov  = "SELECT COUNT(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																		$sqlmov .= " WHERE CALMPOCODI = $Almoxarifado AND CMATEPSEQU = $MaterialDel ";
																		$sqlmov .= "   AND CTIPMVCODI <> 1 ";
																		$resmov  = $db->query($sqlmov);
																		if( PEAR::isError($resmov) ){
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
																		}else{
																				$Qtd = $resmov->fetchRow();
																				if( $Qtd[0] > 0 ){
																			  		$Mens      = 1;
																			  		$Tipo      = 2;
																						$Mensagem .= "O Material não pode ser Retirado! Existe ($Qtd[0]) Movimento(s) com este item - $DescMaterial[$i]";
																				}
																		}
																}
														}
												}
										}
								}
						}*/

						# Apaga os itens que foram selecionados pelo botão retirar #
						if( $Mens == 0 ){
								$sql  = "DELETE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
								$sql .= " WHERE CMATEPSEQU = $MaterialDel AND CLOCMACODI = $LocalizacaoDel";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										$Rollback = 1;
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										# Apaga o Material da tabela de Movimentação #
										$sql  = "DELETE FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sql .= " WHERE CALMPOCODI = $Almoxarifado AND CMATEPSEQU = $MaterialDel ";
										$sql .= "   AND CTIPMVCODI = 1 ";
										$sql .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
										$res  = $db->query($sql);
										if( PEAR::isError($res) ){
												$Rollback = 1;
												$db->query("ROLLBACK");
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Apagou = "S";
										}
								}
						}
				}

				# Atualiza/Insere itens na carga inicial #
				for( $i=0;$i< count($ItemRequisicao) and $Rollback == "";$i++ ){
						# Só atualiza ou insere se o item não estiver ligado a uma requisição ou inventário ou nota fiscal #
						if( ( $QtdRequisicao[$i] == 0 and $QtdNotaFiscal[$i] == 0 and $QtdInventario[$i] == 0 and $QtdMovimentacao[$i] == 0 )
						 or ( $QtdRequisicao[$i] == "" and $QtdNotaFiscal[$i] == "" and $QtdInventario[$i] == "" and $QtdMovimentacao[$i] == "" ) ){
								$Carregou = "";

								# Trocando a vírgula pelo ponto #
								if( $QtdEstocada[$i] == "" ){ $Quantidade[$i] = 0; }else{ $Quantidade[$i] = str_replace(",",".",$QtdEstocada[$i]); }
								if( $ValorUnitario[$i] == "" ){ $Valor[$i] = "NULL"; }else{ $Valor[$i] = str_replace(",",".",$ValorUnitario[$i]); }

								# Verifica se existe o Material em Estoque #
								$sqlest  = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
								$sqlest .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao";
								$resest  = $db->query($sqlest);
								if( PEAR::isError($resest) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlest");
								}else{
										$Qtd = $resest->fetchRow();

										# Se o material não existe no estoque Insere #
										if( $Qtd[0] == 0 ){
							   				# Inserindo Itens em Armazanamento Material #
							   				$sql  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
												$sql .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
												$sql .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
												$sql .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
												$sql .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
												$sql .= ") VALUES ( ";
												$sql .= "$Material[$i], $Localizacao, $Quantidade[$i], NULL, ";
												$sql .= "NULL, 0, 0, NULL, ";
												$sql .= "NULL, NULL, $Valor[$i], $Valor[$i], ";
												$sql .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."' )";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														# Pega o Máximo valor da Movimentação #
														$sqlmov  = "SELECT MAX(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL";
														$sqlmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")."";
														//$sqlmov .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
														$resmov  = $db->query($sqlmov);
														if( PEAR::isError($resmov) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
														}else{
																$LinhaMov  = $resmov->fetchRow();
																$Movimento = $LinhaMov[0] + 1;
														}

														# Pega o Máximo valor do Movimento do Material do Tipo - SALDO INICIAL #
														$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
														$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
														$sqltipo .= "   AND CTIPMVCODI = 1 ";
														//$sqltipo .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
														$restipo  = $db->query($sqltipo);
														if( PEAR::isError($restipo) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
														}else{
																$LinhaTipo     = $restipo->fetchRow();
																$TipoMovimento = $LinhaTipo[0] + 1;
														}

														# Insere na tabela de Movimentação de Material - Tipo 1 Saldo Inicial de Estoque #
														$sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
														$sql .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
														$sql .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
														$sql .= "VMOVMAVALO, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
														$sql .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP ";
														$sql .= ") VALUES ( ";
														$sql .= "$Almoxarifado, ".date("Y").", $Movimento, '".date('Y-m-d')."', ";
														$sql .= "1, NULL, $Material[$i], $Quantidade[$i], ";
														$sql .= "$Valor[$i], ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', ";
														$sql .= "$TipoMovimento, NULL, NULL )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																$Rollback = 1;
																$db->query("ROLLBACK");
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Carregou = "S";
														}
												}
										}else{
							   				# Atualizando os Itens em Armazanamento Material #
							   				$sql  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
												$sql .= "   SET AARMATQTDE = $Quantidade[$i], AARMATESTR =$Quantidade[$i], AARMATVIRT = 0, VARMATUMED = $Valor[$i], ";
												$sql .= "       VARMATULTC = $Valor[$i], CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
												$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TARMATULAT = '".date("Y-m-d H:i:s")."' ";
												$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$Rollback = 1;
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														# Pega o valor do Movimento de Material de acordo com o Sequencial do Material e Tipo Saldo Inicial Estoque #
														$sqlmv  = "SELECT CMOVMACODI FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlmv .= " WHERE CALMPOCODI = $Almoxarifado AND CTIPMVCODI = 1 ";
														$sqlmv .= "   AND CMATEPSEQU = $Material[$i]";
														$sqlmv .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
														$resmv  = $db->query($sqlmv);
														if( PEAR::isError($resmv) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmv");
														}else{
																$Linha = $resmv->fetchRow();
														}

														# Atualiza a tabela de Movimentação de Material - Tipo 1 Saldo Inicial de Estoque #
														$sql  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql .= "   SET AMOVMAQTDM = $Quantidade[$i], VMOVMAVALO = $Valor[$i], VMOVMAUMED = $Valor[$i], ";
														$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
														$sql .= "       CTIPMVCODI = 1, TMOVMAULAT = '".date("Y-m-d H:i:s")."' ";
														$sql .= " WHERE CALMPOCODI = $Almoxarifado AND CMOVMACODI = $Linha[0] ";
														$sql .= "   AND CMATEPSEQU = $Material[$i] ";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																$Rollback = 1;
																$db->query("ROLLBACK");
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Carregou = "S";
														}
												}
										}
								}
						}
			 	}
				if( $Carregou == "S" or $Apagou == "S" ){
						$Mens           = 1;
						$Tipo           = 1;
						$Mensagem       = "Carga Inicial Efetuada com Sucesso";

						# Limpando as variáveis #
						$InicioPrograma = "";
						$Montou         = "";
						$Almoxarifado	  = "";
						$Localizacao	  = "";
						unset($ItemRequisicao);
						unset($DescMaterial);
						unset($Material);
 					  unset($Unidade);
						unset($QtdEstocada);
						unset($ValorUnitario);
						unset($QtdRequisicao);
						unset($QtdNotaFiscal);
						unset($QtdInventario);
						unset($QtdMovimentacao);
						unset($_SESSION['item']);
						unset($_SESSION['ItemDelete']);
				}else{
			  		$Mens     = 1;
			  		$Tipo     = 1;
						$Mensagem = "Nenhuma Atualização foi Efetuada";
				}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();
		}
}elseif( $Botao == "Retirar" ){
    if( count($ItemRequisicao) != 0 ){
				for( $i=0; $i< count($ItemRequisicao); $i++ ){
						if( $CheckItem[$i] == "" ){
								$Qtd++;
								$CheckItem[$i]           = "";
								$ItemRequisicao[$Qtd-1]  = $ItemRequisicao[$i];
								$DescMaterial[$Qtd-1]    = $DescMaterial[$i];
						  	$Material[$Qtd-1]        = $Material[$i];
				      	$Unidade[$Qtd-1]         = $Unidade[$i];
				      	$QtdEstocada[$Qtd-1]     = $QtdEstocada[$i];
				      	$QtdRequisicao[$Qtd-1]   = $QtdRequisicao[$i];
				      	$QtdNotaFiscal[$Qtd-1]   = $QtdNotaFiscal[$i];
				      	$QtdInventario[$Qtd-1]   = $QtdInventario[$i];
				      	$QtdMovimentacao[$Qtd-1] = $QtdMovimentacao[$i];
				      	$ValorUnitario[$Qtd-1]   = $ValorUnitario[$i];
						}else{
								# Verifica se existe o item em estoque #
								$db   = Conexao();
								$sql  = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
								$sql .= " WHERE CLOCMACODI = $Localizacao AND CMATEPSEQU = $Material[$i] ";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$QtdEst = $res->fetchRow();
										if( $QtdEst[0] > 0 ){
												# Monta um Array para Deletar itens da Carga Inicial #
												if( $_SESSION['ItemDelete'] == "" or ! in_array($Material[$i].$SimboloConcatenacaoDesc.$Localizacao,$_SESSION['ItemDelete']) ){
														$_SESSION['ItemDelete'][count($_SESSION['ItemDelete'])] = $Material[$i].$SimboloConcatenacaoDesc.$Localizacao;
												}
										}
								}
								$db->disconnect();
						}
				}
				if( count($ItemRequisicao) > 1 ){
						$ItemRequisicao  = array_slice($ItemRequisicao,0,$Qtd);
						$DescMaterial    = array_slice($DescMaterial,0,$Qtd);
						$Material        = array_slice($Material,0,$Qtd);
						$Unidade         = array_slice($Unidade,0,$Qtd);
		      	$QtdEstocada     = array_slice($QtdEstocada,0,$Qtd);
		      	$ValorUnitario   = array_slice($ValorUnitario,0,$Qtd);
		      	$QtdRequisicao   = array_slice($QtdRequisicao,0,$Qtd);
		      	$QtdNotaFiscal   = array_slice($QtdNotaFiscal,0,$Qtd);
		      	$QtdInventario   = array_slice($QtdInventario,0,$Qtd);
		      	$QtdMovimentacao = array_slice($QtdMovimentacao,0,$Qtd);
				}else{
						unset($ItemRequisicao);
						unset($DescMaterial);
						unset($Material);
						unset($QtdEstocada);
						unset($ValorUnitario);
						unset($QtdRequisicao);
						unset($QtdNotaFiscal);
						unset($QtdInventario);
						unset($QtdMovimentacao);
				}
		}
		unset($_SESSION['item']);
}

if( $Botao == "" and $Montou == "" ){
		if( $InicioPrograma == "" ){
				unset($_SESSION['item']);
				unset($_SESSION['ItemDelete']);
		}else{
				if( $Almoxarifado == "" ){
						$Mens = 1;
						$Tipo = 2;
						$Mensagem = "Informe: <a href=\"javascript:document.CadCargaInicialFixaSemChecar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
				}else{
						if( $Localizacao == "" ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadCargaInicialFixaSemChecar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}
						if( $InicioPrograma == 2 and $Localizacao == "" ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens = 1;
								$Tipo = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadCargaInicialFixaSemChecar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}else{
								if( $Localizacao != "" ){
									  # Carrega os dados do Estoque de acordo com a Localização #
						  		  $db   = Conexao();
										$sql  = "SELECT A.CMATEPSEQU, A.AARMATQTDE, B.EMATEPDESC, C.EUNIDMSIGL, ";
										$sql .= "       A.VARMATUMED ";
										$sql .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL A, SFPC.TBMATERIALPORTAL B, ";
										$sql .= "       SFPC.TBUNIDADEDEMEDIDA C";
										$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.CMATEPSEQU = B.CMATEPSEQU ";
										$sql .= "   AND B.CUNIDMCODI = C.CUNIDMCODI ";
										$sql .= " ORDER BY B.EMATEPDESC ";
										$res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $res->numRows();
												for( $i=0;$i< $Rows; $i++ ){
														$Linha = $res->fetchRow();
														$Material[$i]       = $Linha[0];
														$QtdEstocada[$i]    = str_replace(".",",",$Linha[1]);
														$DescMaterial[$i]   = RetiraAcentos($Linha[2]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[2]);
														$Unidade[$i]        = $Linha[3];
														$ValorUnitario[$i]  = str_replace(".",",",$Linha[4]);

														# Verifica se o Material está ligado a alguma Requisição #
														$sqlreq  = "SELECT COUNT(A.CMATEPSEQU) ";
														$sqlreq .= " 	FROM SFPC.TBITEMREQUISICAO A, SFPC.TBREQUISICAOMATERIAL B, ";
														$sqlreq .= " 	     SFPC.TBALMOXARIFADOORGAO C  ";
														$sqlreq .= " WHERE A.CMATEPSEQU = $Material[$i] AND A.CREQMASEQU = B.CREQMASEQU";
														$sqlreq .= "   AND C.CALMPOCODI = $Almoxarifado AND B.CORGLICODI = C.CORGLICODI";
														$resreq  = $db->query($sqlreq);
														if( PEAR::isError($resreq) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlreq");
														}else{
																$Qtd               = $resreq->fetchRow();
																$QtdRequisicao[$i] = $Qtd[0];
														}

														# Verifica se o Material está ligado a alguma Nota Fiscal #
														$sqlnf  = "SELECT COUNT(A.CMATEPSEQU) ";
														$sqlnf .= "  FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B ";
														$sqlnf .= " WHERE A.CMATEPSEQU = $Material[$i] AND A.AENTNFANOE = B.AENTNFANOE ";
														$sqlnf .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CENTNFCODI = B.CENTNFCODI ";
														$sqlnf .= "   AND A.CALMPOCODI = B.CALMPOCODI ";
														$resnf  = $db->query($sqlnf);
														if( PEAR::isError($resnf) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
														}else{
																$Qtd               = $resnf->fetchRow();
																$QtdNotaFiscal[$i] = $Qtd[0];
														}

														# Verifica se o Material está ligado a algum Inventário #
														$sqlinv  = "SELECT COUNT(A.CMATEPSEQU) ";
														$sqlinv .= " 	FROM SFPC.TBINVENTARIOMATERIAL A, SFPC.TBINVENTARIOCONTAGEM B ";
														$sqlinv .= " WHERE A.CMATEPSEQU = $Material[$i] AND A.AINVCOANOB = B.AINVCOANOB";
														$sqlinv .= "   AND B.CLOCMACODI = $Localizacao AND A.AINVCOSEQU = B.AINVCOSEQU";
														$sqlinv .= "   AND B.FINVCOFECHA = 'S' ";
														$resinv  = $db->query($sqlinv);
														if( PEAR::isError($resinv) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlinv");
														}else{
																$Qtd               = $resinv->fetchRow();
																$QtdInventario[$i] = $Qtd[0];
														}

														# Verifica se o Material está ligado a alguma Movimentação diferente de SALDO INICIAL #
														$sqlinv  = "SELECT COUNT(CMOVMACODI) ";
														$sqlinv .= " 	FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlinv .= " WHERE CMATEPSEQU = $Material[$i] AND CALMPOCODI = $Almoxarifado ";
														$sqlinv .= "   AND CTIPMVCODI <> 1 ";
														$sqlinv .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
														$resinv  = $db->query($sqlinv);
														if( PEAR::isError($resinv) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlinv");
														}else{
																$Qtd                 = $resinv->fetchRow();
																$QtdMovimentacao[$i] = $Qtd[0];
														}

														$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstocada[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$QtdRequisicao[$i].$SimboloConcatenacaoArray.$QtdNotaFiscal[$i].$SimboloConcatenacaoArray.$QtdInventario[$i].$SimboloConcatenacaoArray.$QtdMovimentacao[$i];
														$Montou = "S";
												}
										}
									 	$db->disconnect();
								}
						}
				}
		}
}

# Monta o array de itens da requisição de material #
if( count($_SESSION['item']) != 0 ){
		sort($_SESSION['item']);
		if( $ItemRequisicao == "" ){
				for( $i=0;$i<count($_SESSION['item']);$i++ ){
						$ItemRequisicao[count($ItemRequisicao)] = $_SESSION['item'][$i];
				}
		}else{
				for( $i=0;$i<count($ItemRequisicao);$i++ ){
						$DadosItem            = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
						$SequencialItem[$i]   = $DadosItem[1];
				}
			  for( $i=0;$i<count($_SESSION['item']);$i++ ){
						$DadosSessao          = explode($SimboloConcatenacaoArray,$_SESSION['item'][$i]);
						$SequencialSessao[$i] = $DadosSessao[1];
				 		if( ! in_array($SequencialSessao[$i],$SequencialItem) ){
			  				$ItemRequisicao[count($ItemRequisicao)] = $_SESSION['item'][$i];
			 			}
		 		}
    }
    unset($_SESSION['item']);
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadCargaInicialFixaSemChecar.Botao.value = valor;
	document.CadCargaInicialFixaSemChecar.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadCargaInicialFixaSemChecar.Almoxarifado.value ){
		document.CadCargaInicialFixaSemChecar.submit();
	}else	if( ! document.CadCargaInicialFixaSemChecar.Localizacao.value ){
		document.CadCargaInicialFixaSemChecar.InicioPrograma.value = 2;
		document.CadCargaInicialFixaSemChecar.submit();
	}else{
		window.open(url,'item','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCargaInicialFixaSemChecar.php" method="post" name="CadCargaInicialFixaSemChecar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Carga Inicial > Fixa
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
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
		    					CARGA INICIAL - FIXA
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para efetuar uma Carga Inicial em Estoque, informe os dados abaixo, escolha os itens desejados informe a Quantidade e o Valor Unitário e clique no botão "Carregar". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
				              <td class="textonormal">
			                	<?php
			              		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db   = Conexao();
												# CORREÇÃO FORÇADA PARA ACEITAR EQUIPE DA DLC CADASTRANDO A CARGA INICIAL DE TODOS OS ALMOXARIFADOS
												# CADASTRADOS SEM SER PELO PERFIL/USUÁRIO --- RETIRAR ---
					         			if( ($_SESSION['_cgrempcodi_'] == 0 ) or ($_SESSION['_cusupocodi_'] == 103) or ($_SESSION['_cusupocodi_'] == 100) or ($_SESSION['_cusupocodi_'] == 65) or ($_SESSION['_cusupocodi_'] == 98) ){ //65 =  Eduardo, 98 = Tatianne
				              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if ($Almoxarifado) {
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												} else {
				              			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
											    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
											    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
												    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I') ";
			                  }
												$sql .= " ORDER BY A.EALMPODESC ";
			              		$res  = $db->query($sql);
												if( PEAR::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1 ){
																$Linha = $res->fetchRow();
				          	      			$Almoxarifado = $Linha[0];
			        	   	      			echo "$Linha[1]<br>";
			        	   	      			echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
			        	   	      			echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"$DescAlmoxarifado\">";
			        	   	      			echo $DescAlmoxarifado;
							            	}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"javascript:enviar('TrocaAlmoxarifado');\">\n";
							                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for( $i=0;$i< $Rows; $i++ ){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
				          	   	      			if( $Linha[0] == $Almoxarifado ){
				          	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
						          	      			}else{
						          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
						          	      			}
							                	}
							                	echo "</select>\n";
							                	$CarregaAlmoxarifado = "";
							              }else{
							            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
		  	          	   	    		echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
		  	          	   	    		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
							            	}
					              }
			           			 	$db->disconnect();
			    	            ?>
			    	            <input type="hidden" name="DefineAlmoxarifado" value="<?php echo $DefineAlmoxarifado; ?>">
			    	         </td>
				            </tr>
				           	<?php if( $Almoxarifado != "" ){ ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
				              <td class="textonormal">
				               	<?php
												$db = Conexao();
                		 		if( $Localizacao != "" ){
				                		# Mostra a Descrição de Acordo com o Almoxarifado #
				                		$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
				                		$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
				                		$res  = $db->query($sql);
														if( PEAR::isError($res) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if( $Linha[0] == "E" ){
																	  $Equipamento = "ESTANTE";
																}if( $Linha[0] == "A" ){
																	  $Equipamento = "ARMÁRIO";
																}if( $Linha[0] == "P" ){
																	  $Equipamento = "PALETE";
																}
		                						$DescArea = $Linha[4];
		                						echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
		       	   	      					echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
		       	   	      			}
                		 		}else{
		                		 		# Mostra as Localizações de acordo com o Almoxarifado #
				                		$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
				                		$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= "   AND A.CALMPOCODI = $Almoxarifado ";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
				                		$res  = $db->query($sql);
														if( PEAR::isError($res) ){
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
		  	          	   	      			echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
									                  echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																		$EquipamentoAntes = "";
																		$DescAreaAntes    = "";
																		for( $i=0;$i< $Rows; $i++ ){
																				$Linha = $res->fetchRow();
																				$CodEquipamento = $Linha[2];
																				if( $Linha[1] == "E" ){
																					  $Equipamento = "ESTANTE";
																				}if( $Linha[1] == "A" ){
																					  $Equipamento = "ARMÁRIO";
																				}if( $Linha[1] == "P" ){
																					  $Equipamento = "PALETE";
																				}
														            $NumeroEquip = $Linha[2];
														            $Prateleira  = $Linha[3];
														            $Coluna      = $Linha[4];
														            $DescArea    = $Linha[5];
						          	   	      			if( $DescAreaAntes != $DescArea ){
						          	   	      					echo"<option value=\"\">$DescArea</option>\n";
						          	   	      					$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						          	   	      			}
						          	   	      			if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
						          	   	      					echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
						          	   	      			}
						          	   	      			if( $Localizacao == $Linha[0] ){
						          	   	      					echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
						          	   	      			}else{
						          	   	      					echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
						          	   	      			}
						          	   	      			$DescAreaAntes       = $DescArea;
						          	   	      			$CodEquipamentoAntes = $CodEquipamento;
						          	   	      			$EquipamentoAntes    = $Equipamento;
									                	}
									                	echo "</select>\n";
									                	$CarregaLocalizacao = "";
									              }
							              }
					           		}
					           		$db->disconnect();
		      	            ?>
				              </td>
				            </tr>
				            <?php } ?>
				            <tr>
				              <td class="textonormal" colspan="4">
				              	<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
								        	<?php
												  if( count($ItemRequisicao) != 0 ){ sort($ItemRequisicao); }
  								       	for( $i=0;$i< count($ItemRequisicao);$i++ ){
															$Dados = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
															$DescMaterial[$i]    = $Dados[0];
															$Material[$i]        = $Dados[1];
															$Unidade[$i]         = $Dados[2];
															$QtdEstocada[$i]     = $Dados[3];
															$ValorUnitario[$i]   = $Dados[4];
															$QtdRequisicao[$i]   = $Dados[5];
															$QtdNotaFiscal[$i]   = $Dados[6];
															$QtdInventario[$i]   = $Dados[7];
															$QtdMovimentacao[$i] = $Dados[8];
															if( $i == 0 ){
					  	      	            echo "		<tr>\n";
								        	      	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" wdith=\"5%\">&nbsp;</td>\n";
								        	      	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
								        	      	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" wdith=\"5%\">UNIDADE</td>\n";
								        	      	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">QUANTIDADE</td>\n";
								        	      	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">VALOR</td>\n";
					  	      	            echo "		</tr>\n";
											        }
								        	?>
								        	<tr>
				              			<td class="textonormal" align="right">
			          	    			<?php
			          	    			//if( ( $QtdRequisicao[$i] == 0 and $QtdNotaFiscal[$i] == 0 and $QtdInventario[$i] == 0 and $QtdMovimentacao[$i] == 0 )
			          	    			// or ( $QtdRequisicao[$i] == "" and $QtdNotaFiscal[$i] == "" and $QtdInventario[$i] == "" and $QtdMovimentacao[$i] == "" ) ){
			          	    					echo "	<input type=\"checkbox\" name=\"CheckItem[$i]\" value=\"$Material[$i]\"\n";
					          	    	//}else{
			          	    			//		echo "&nbsp;&nbsp;&nbsp;\n";
			          	    			//		echo "<input type=\"hidden\" name=\"MaterialRequisicao[$i]\" value=\"$Material[$i]\"\n";
					          	    	//}
					          	    	?>
			          	    		 	</td>
							  	        	<td class="textonormal">
							  	        		<a href="javascript:AbreJanela('CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=<?php echo $Material[$i]; ?>',700,370);">
	  							    					<font color="#000000">
	  							    						<?php
	  							    						$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
								    						 	echo trim($Descricao[1]);
								    						 	?>
	  							    					</font>
  							    					</a>
							  	        		<input type="hidden" name="ItemRequisicao[<?php echo $i; ?>]" value="<?php echo $ItemRequisicao[$i]; ?>">
							  	        		<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
							              	<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
						          	    	<input type="hidden" name="QtdRequisicao[<?php echo $i; ?>]" value="<?php echo $QtdRequisicao[$i]; ?>">
						          	    	<input type="hidden" name="QtdNotaFiscal[<?php echo $i; ?>]" value="<?php echo $QtdNotaFiscal[$i]; ?>">
						          	    	<input type="hidden" name="QtdInventario[<?php echo $i; ?>]" value="<?php echo $QtdInventario[$i]; ?>">
						          	    	<input type="hidden" name="QtdMovimentacao[<?php echo $i; ?>]" value="<?php echo $QtdMovimentacao[$i]; ?>">
							  	        	</td>
					              		<td class="textonormal" align="center">
					              			<?php echo $Unidade[$i]; ?>
				          	    			<input type="hidden" name="Unidade[<?echo $i;?>]" size="11" maxlength="11" value="<?php echo $Unidade[$i];?>">
				          	    		</td>
					              		<td class="textonormal" align="right">
			          	    			<?php
			          	    			//if( ( $QtdRequisicao[$i] == 0 and $QtdNotaFiscal[$i] == 0 and $QtdInventario[$i] == 0 and $QtdMovimentacao[$i] == 0 )
			          	    			// or ( $QtdRequisicao[$i] == "" and $QtdNotaFiscal[$i] == "" and $QtdInventario[$i] == "" and $QtdMovimentacao[$i] == "" )){
				          	    		//		if( $QtdEstocada[$i] == "" ){ $QtdEstocada[$i] = 0; }
				          	    				echo "<input type=\"text\" name=\"QtdEstocada[$i]\" size=\"11\" maxlength=\"11\" value=\"$QtdEstocada[$i]\" class=\"textonormal\">\n";
					          	    	//}else{
				          	    		//		echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstocada[$i])));
				          	    		//		echo "<input type=\"hidden\" name=\"QtdEstocada[$i]\" value=\"$QtdEstocada[$i]\">\n";
					          	    	//}
					          	    	?>
				          	    		</td>
					              		<td class="textonormal" align="right">
			          	    			<?php
			          	    			//if( ( $QtdRequisicao[$i] == 0 and $QtdNotaFiscal[$i] == 0 and $QtdInventario[$i] == 0 and $QtdMovimentacao[$i] == 0 )
			          	    			// or ( $QtdRequisicao[$i] == "" and $QtdNotaFiscal[$i] == "" and $QtdInventario[$i] == "" and $QtdMovimentacao[$i] == "" )){
				          	    				echo "<input type=\"text\" name=\"ValorUnitario[$i]\" size=\"11\" maxlength=\"11\" value=\"$ValorUnitario[$i]\" class=\"textonormal\">\n";
					          	    	//}else{
			          	    			//		echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario[$i])));
				          	    		//		echo "<input type=\"hidden\" name=\"ValorUnitario[$i]\" value=\"$ValorUnitario[$i]\">\n";
					          	    	//}
					          	    	?>
				          	    		</td>
								        	</tr>
								        	<?php } ?>
				            			<tr>
						   	  	  			<td class="textonormal" colspan="9" align="center">
						         	      	<input type="button" name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('CadIncluirItem.php?ProgramaOrigem=CadCargaInicialFixaSemChecar&Almoxarifado=<?php echo $Almoxarifado; ?>',700,350);">
						         	      	<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
						            		</td>
								        	</tr>
								        </table>
								      </td>
										</tr>
	           			</table>
	           		</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
               		<input type="hidden" name="Montou" value="<?php echo $Montou; ?>">
               		<input type="hidden" name="InicioPrograma" value="1">
			  	      	<input type="button" name="Carregar" value="Salvar" class="botao" onClick="javascript:enviar('Carregar');">
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
