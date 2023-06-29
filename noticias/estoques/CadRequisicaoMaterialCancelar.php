<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoMaterialCancelar.php
#---------------------
# Autor:    Roberta Costa
# Data:     24/08/2005
# Alterado: Marcus Thiago
# Data:     12/01/2006
# Alterado: Álvaro Faria
# Data:     04/05/2006
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Padronização das variáveis de requisição
# Alterado: Rodrigo Melo
# Data:     11/02/2008 - Alteração para que as requisções dos centros de custos inativos sejam exibidas para então ser canceladas.
# Alterado: Ariston Cordeiro
# Data:     08/07/2008 - Alteração para repor a quantidade do item no campo de estoque real (aarmatestr) ou virtual (aarmatvirt), em SFPC.TBarmazenamentomaterial.
# Alterado: Ariston Cordeiro
# Data:     04/12/2008 - Ao cancelar uma requisição atendida por uma nota fiscal virtual, a nota também é cancelada
# Alterado: Ariston Cordeiro
# Data:     18/08/2009 - Calculando novo valor médio ao cancelar a nota fiscal
# Alterado: Ariston Cordeiro
# Data:     14/09/2009 - verificando se variáveis POST foram recebidas antes de tentar usá-las.
# Alterado: Ariston Cordeiro
# Data:     15/09/2009 - Bloqueando cancelamentos de notas fiscais virtuais com movimentos após sua criação.
#-----------------
# Objetivo: Programa de Cancelamento de Requisição de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadRequisicaoMaterialCancelarSelecionar.php' );
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/RelAuxilioCancelamentoNotaPdf.php' );

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgua da mensagem de erro por "e"

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao              = $_POST['Botao'];
		$GrupoEmp           = $_POST['GrupoEmp'];
		$Usuario            = $_POST['Usuario'];
		$Almoxarifado       = $_POST['Almoxarifado'];
		$CentroCusto        = $_POST['CentroCusto'];
		$SeqRequisicao      = $_POST['SeqRequisicao'];
		$AnoRequisicao      = $_POST['AnoRequisicao'];
		$Requisicao         = $_POST['Requisicao'];
		$OrgaoUsuario       = $_POST['OrgaoUsuario'];
		$DataRequisicao     = $_POST['DataRequisicao'];
		$Situacao           = $_POST['Situacao'];
		$DescSituacao       = $_POST['DescSituacao'];
		$DataSituacao       = $_POST['DataSituacao'];
		$Localizacao        = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$NCaracteres        = $_POST['NCaracteres'];
		$Motivo             = strtoupper2($_POST['Motivo']);
		$Material           = $_POST['Material'];
		$DescMaterial       = $_POST['DescMaterial'];
		$Unidade            = $_POST['Unidade'];
		$QtdSolicitada      = $_POST['QtdSolicitada'];
		if( $Solicitacao != 1 ){
				$QtdAtendida        = $_POST['QtdAtendida'];
		}
		for( $i=0;$i< count($DescMaterial);$i++ ){
				$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdSolicitada[$i].$SimboloConcatenacaoArray.$QtdAtendida[$i];
		}
}else{
		$SeqRequisicao = $_GET['SeqRequisicao'];
		$AnoRequisicao = $_GET['AnoRequisicao'];
		$Almoxarifado  = $_GET['Almoxarifado'];
}
if( $NCaracteres == "" ){ $NCaracteres = 0; }

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "CadRequisicaoMaterialCancelar";

#vê se as variáveis post não foram recebidas
if(is_null($SeqRequisicao)){
		header("location: CadRequisicaoMaterialCancelarSelecionar.php");
		exit;
}

if( $Botao == "Voltar" ){
		header("location: CadRequisicaoMaterialCancelarSelecionar.php");
		exit;
}elseif( $Botao == "Cancelar" ){
		# Crítica aos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') && ($Situacao != 1) ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		} elseif ( ($Localizacao == "") && ($Situacao != 1) ) {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialCancelar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if( $Motivo == ""  ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialCancelar.Motivo.focus();\" class=\"titulo2\">Motivo</a>";
		}else{
				if( strlen($Motivo) > 200 ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialCancelar.Motivo.focus();\" class=\"titulo2\">Motivo com apenas 200 caracteres - Atualmente com (".strlen($Motivo).")</a>";
				}
		}

		# Verifica se a requisição já foi cancelada. Correção para o caso de duas pessoas chegarem
		# a esta tela simutaneamente, trabalhando em computadores diferentes, com a mesma
		# requisição (os dois clicando na tela de seleção antes do primeiro cancelamento).
		# Caso um deles cancele, o segundo não poderá cancelar novamente, exibindo erro
		$db = Conexao();
		$sqltestaatend   = "SELECT MAX(CTIPSRCODI) FROM SFPC.TBSITUACAOREQUISICAO ";
		$sqltestaatend  .= "WHERE CREQMASEQU = $SeqRequisicao";
		$restestaatend   = $db->query($sqltestaatend);
		if( db::isError($restestaatend) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltestaatend");
		}else{
				$LinhaTestaAtend = $restestaatend->fetchRow();
				if($LinhaTestaAtend[0] == 6) { // 6 - Requisição Cancelada
						$Mens      = 1;
						$Tipo      = 2;
						$Troca     = 2;
						$Mensagem  = "Esta Requisição já foi Cancelada, provavelmente por um acesso simultâneo. Verifique no Acompanhamento de Requisição";
				}
		}
		$db->disconnect();

		# Se não entrou em nenhuma Crítica #
		if( $Mens == 0 ) {
				if( $_SESSION['_cgrempcodi_'] != 0 ){
						# Situação diferente de Em Análise #
						if( $Situacao != 1 ){
								for( $i=0;$i< count($ItemRequisicao);$i++ ){
										# Verifica se o Material está na localização escolhida, se ele tiver sido atendido na requisição #
										if ($QtdAtendida[$i] > 0) {
												$db = Conexao();
												$sql  = "SELECT COUNT(CMATEPSEQU) FROM SFPC.TBARMAZENAMENTOMATERIAL";
												$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Qtd = $res->fetchRow();
														# Se o material não existir no estoque desta localização ($Qtd[0] == 0) e tiver sido atendindo ($QtdAtendida[$i] > 0), não terá para onde retornar, setando a flag que proíbe o cancelamento e exibe mensagem #
														if($Qtd[0] == 0){
																$Cont++;
																$ExisteMaterial = "N";
														}
												}
												$db->disconnect();
										}
								}
								if( $ExisteMaterial == "N" ){
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = "O Cancelamento não pode ser executado! Existe ($Cont) Material(is) que não pertence(m) a Localização selecionada";
								}
						}
						# Se não entrou em nenhuma Crítica #
						if( $Mens == 0 ){
								$db = Conexao();

								# Evita duplicidade de gravação teclando F5 #
								$verifica    = "SELECT A.CREQMASEQU FROM SFPC.TBMOVIMENTACAOMATERIAL A WHERE ";
								$verifica   .= "CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoRequisicao AND ";
								$verifica   .= "CTIPMVCODI = 18 AND CREQMASEQU = $SeqRequisicao";
								$resverifica = $db->query($verifica);
								if( db::isError($resverifica) ){
										$ErrosNoFor = 1;
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $verifica");
								}else{
										$QtdVerifica	= $resverifica->numRows();
										if( $QtdVerifica != 0 ){ // Já houve inserção deste atendimento. Não insere de novo, mas não retorna erro.
												$F5 = 1;
										}else{
												for( $i=0; $i< count($ItemRequisicao) and !$ErrosNoFor; $i++ ){
																		# Verifica se se trata do estoque real ou virtual, e retorna valores de nota fiscal #
																		$sqlMov  = "
																			SELECT m.aentnfanoe, m.centnfcodi, m.calmpocodi, nf.aentnfnota
																			FROM SFPC.tbmovimentacaomaterial m, SFPC.TBentradanotafiscal nf
																			WHERE
																				m.CREQMASEQU = $SeqRequisicao AND
																				m.CMATEPSEQU = $Material[$i] AND
																				nf.calmpocodi = m.calmpocodi and
																				nf.aentnfanoe = m.aentnfanoe and
																				nf.centnfcodi = m.centnfcodi
																		";
																		$resMov = $db->query($sqlMov);

																		if( db::isError($resMov) ){
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov \n");
																				exit();
																		}
																		$Linha = $resMov->fetchRow();
																		$EstoqueVirtual='N';
																		$NFAno = $Linha[0];
																		if( ($Linha[0]!='') && ($Linha[0]!=null) ){
																			$NFCod 					= $Linha[1];
																			$NFAlmoxarifado = $Linha[2];
																			$NFNota = $Linha[3];
																			$NFNumero = $NFNota."/".$NFAno;

																			$EstoqueVirtual='S';
																		}
																		$RequisicaoNumero = substr($Requisicao+100000,1)."/".$AnoRequisicao;
																		if($EstoqueVirtual == 'S'){
																			# Classes de cancelamento de movimentações
																			require_once("./ClaCancelamentoNota.php");
																			Banco::guardarSessao($db); // guardar sessão para uso nas classes

																			$cancelamentos = Cancelamentos::singleton();
																			$movimentacoes = new MovimentacoesCancelamentoNota($NFAlmoxarifado,$NFAno,$NFCod,$Material[$i]);
																			$movimentacoes->ocultarCanceladasNotaFiscal();

																			#ocultar movimentação de criação da requisição (a que se deseja cancelar, para não contar como movimentação a ser cancelada)
																			$movArray = $movimentacoes->getMovimentacoes();
																			foreach($movArray as $movimentacao){
																				/*
																				if(!is_null($movimentacao->getRequisicao())){
																					echo "[1-".$SeqRequisicao."]";
																					echo "[2-".$movimentacao->getRequisicao()->getSequencial()."]";
																				}
																				*/
																				if(
																					(!is_null($movimentacao->getRequisicao())) and
																					($movimentacao->getTipo() == 4) and
																					($movimentacao->getRequisicao()->getSequencial() == $SeqRequisicao )
																				){
																					$movimentacao->setOcultar(true);
																				}
																			}


																			/*# Verificação da checagm de movimemtações de materiais
																			if($i == 0){
																				echo "Teste de verificação de movimentações não canceladas<br/>";
																				echo "A verificação passa apenas se apenas houver movimentações ocultos.<br/><br/>";
																			}
																			echo "### Material ".$Material[$i].". ###<br/>";
																			### teste da checagem das movimentações ###
																			foreach($movArray as $movimentacao){
																				echo "[";
																				echo "cod.mov.: ".$movimentacao->getCodigo()."";
																				echo "| tipo: ".$movimentacao->getTipo()." ";
																				if($movimentacao->getOcultar()){
																					echo "| OCULTADO ";
																				}else{
																				}
																				echo "]<br/>";
																			}
																			if($movimentacoes->getNoMovimentacoesNaoOcultas()!=0 or $i>= count($ItemRequisicao)){
																				exit;
																			}
																			###########################################*/



																			if($movimentacoes->notaFiscalAntesDoInventario()){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");

																				$ErrosNoFor = 1;
																				$Rollback = 1;
																				$Mens     = 1;
																				$Tipo     = 2;
																				$Mensagem = "Impossível cancelar requisição ".$RequisicaoNumero.". Esta requisição de material foi atendida pela Nota Fiscal virtual ".$NFNumero." que foi criada antes do último inventário. Como Notas Fiscais virtuais não podem ser canceladas requisições vinculadas a elas também não";

																			}else{
																				//verificar se há movimentações não canceladas
																				$movimentacoes->ocultarCanceladasNotaFiscal();
																				if($movimentacoes->getNoMovimentacoesNaoOcultas()!=0){
																					$db->query("ROLLBACK");
																					$db->query("END TRANSACTION");

																					#Mensagem padrão para visualização do relatório de cancelamento
																					$UrlRelatorio = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=".$NFAlmoxarifado."&Material=".$Material[$i]."&NotaFiscal=".$NFCod."&AnoNota=".$NFAno."&Procedimento=M&".mktime();
																					$MensagemRelatorio = " Utilize o relatório de <a href=\"$UrlRelatorio\">Auxílio para Cancelamento de Nota Fiscal</a> para identificar estas movimentações";

																					$ErrosNoFor = 1;
																					$Rollback = 1;
																					$Mens     = 1;
																					$Tipo     = 2;
																					$Mensagem = "O Cancelamento da requisição ".$RequisicaoNumero." não pode ser feito. Esta requisição de material foi atendida pela Nota Fiscal virtual ".$NFNumero." em que o material código ".$Material[$i]." possui movimentações não canceladas após a criação da Nota Fiscal. ".$MensagemRelatorio;

																				}
																			}


																		}

												}
												 //classes usam conexao e podem te-la finalizado
												$db->query("BEGIN TRANSACTION");
												# Atualiza a Requisição #
												$sql  = "UPDATE SFPC.TBREQUISICAOMATERIAL ";
												$sql .= "   SET CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
												$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
												$sql .= "       TREQMAULAT = '".date("Y-m-d H:i:s")."'";
												$sql .= " WHERE CREQMASEQU = $SeqRequisicao ";
												$res = $db->query($sql);
												if( db::isError($res) ){
														$ErrosNoFor = 1;
														$Rollback = 1;
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														exit;
												}else{
														for( $i=0; $i< count($ItemRequisicao) and !$ErrosNoFor; $i++ ){
																# Acréscimo no Estoque se a Situação for diferente de Em Análise #
																$QtdAtendida[$i] = str_replace(",",".",$QtdAtendida[$i]);
																if( $QtdAtendida[$i] > 0 and $Situacao != 1 ){
																		$QtdMovimento = str_replace(",",".",$QtdAtendida[$i]);

																		if($ErrosNoFor != 1){
																			if($EstoqueVirtual != 'S'){
																				# Atualiza a Tabela de Estoque #
																				$sql  = "
																					UPDATE
																						SFPC.TBARMAZENAMENTOMATERIAL
																					SET
																						AARMATQTDE = AARMATQTDE + $QtdMovimento,
																						CGREMPCODI = ".$_SESSION['_cgrempcodi_'].",
																						CUSUPOCODI = ".$_SESSION['_cusupocodi_'].",
																						TARMATULAT = '".date("Y-m-d H:i:s")."'
																						,AARMATESTR = AARMATESTR + $QtdMovimento
																					WHERE
																						CMATEPSEQU = $Material[$i] AND
																						CLOCMACODI = $Localizacao
																				";
																				/*
																				  ";
																					if($EstoqueVirtual == 'S'){ //ESTOQUE VIRTUAL
																						 $sql  .= " ,AARMATVIRT = AARMATVIRT + $QtdMovimento ";
																					}else{ //ESTOQUE REAL
																						 $sql  .= " ,AARMATESTR = AARMATESTR + $QtdMovimento ";
																					}
																					$sql  .= "
																				*/
																				$res = $db->query($sql);
																				if( db::isError($res) ){
																						$ErrosNoFor = 1;
																						$Rollback = 1;
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						exit(0);
																				}
																			}
																			# Pega o Máximo valor do Movimento de Material #
																			$sql  = "SELECT MAX(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																			$sql .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")."";
																			//$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																			$ressel  = $db->query($sql);
																			if( db::isError($ressel) ){
																					$ErrosNoFor = 1;
																					$Rollback = 1;
																					$db->query("ROLLBACK");
																					$db->query("END TRANSACTION");
																					$db->disconnect();
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					$Linha     = $ressel->fetchRow();
																					$Movimento = $Linha[0] + 1;
																					# Pega o Máximo valor do Movimento do Material do Tipo - ENTRADA POR CANCELAMENTO DE REQUISIÇÃO #
																					$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																					$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
																					$sqltipo .= "   AND CTIPMVCODI = 18 ";
																					//$sqltipo .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																					$restipo  = $db->query($sqltipo);
																					if( db::isError($restipo) ){
																							$ErrosNoFor = 1;
																							$Rollback = 1;
																							$db->query("ROLLBACK");
																							$db->query("END TRANSACTION");
																							$db->disconnect();
																							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																					}else{
																							$LinhaTipo     = $restipo->fetchRow();
																							$TipoMovimento = $LinhaTipo[0] + 1;
																							# Formatando a quantidade #
																							if( $QtdAtendida[$i] == "" or $QtdAtendida[$i] == 0 ){
																									$QtdMovimento = "NULL";
																							}else{
																									$QtdMovimento = str_replace(",",".",$QtdAtendida[$i]);
																							}

																							# Pega o valor médio do estoque(armazenamento) #
																							$sqlarmat  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
																							$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																							$resarmat  = $db->query($sqlarmat);
																							if( db::isError($ressaldoini) ){
																									$ErrosNoFor = 1;
																									$Rollback = 1;
																									$db->query("ROLLBACK");
																									$db->query("END TRANSACTION");
																									$db->disconnect();
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
																							}else{
																									$Linhaarmat    = $resarmat->fetchRow();
																									$decValEstoque = str_replace(",",".",$Linhaarmat[0]);
																									# Insere uma Movimentação do tipo 18 - ENTRADA POR CANCELAMENTO DE REQUISIÇÃO #
																									$sql    = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																									$sql   .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																									$sql   .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																									$sql   .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																									$sql   .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP ";
																									$sql   .= ") VALUES ( ";
																									$sql   .= "$Almoxarifado, ".date("Y").", $Movimento, '".date("Y-m-d")."', ";
																									$sql   .= "18, $SeqRequisicao, $Material[$i], $QtdMovimento, ";
																									$sql   .= "$decValEstoque, $decValEstoque, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', ";
																									$sql   .= "$TipoMovimento, NULL, NULL )";
																									$result = $db->query($sql);
																									if( db::isError($result) ){
																											$ErrosNoFor = 1;
																											$Rollback = 1;
																											$db->query("ROLLBACK");
																											$db->query("END TRANSACTION");
																											$db->disconnect();
																											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																											exit;
																									}else{
																										if($EstoqueVirtual == 'S'){
																											# Pega o Máximo valor do Movimento do Material do Tipo - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																											$sqltipo  = "
																												SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL
																												WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")."
																													AND CTIPMVCODI = 8
																											"; // Apresentar só as movimentações ativas
																											$restipo  = $db->query($sqltipo);
																											if( db::isError($restipo) ){
																													$ErrosNoFor = 1;
																													$Rollback = 1;
																													$db->query("ROLLBACK");
																													$db->query("END TRANSACTION");
																													$db->disconnect();
																													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqltipo, $restipo);
																											}

																											$LinhaTipo     = $restipo->fetchRow();
																											$MovimentoTipo = $LinhaTipo [0] + 1;
																											$Movimento ++;

																												#Pegando valor e quantidade do estoque para recalcular o valor médio
																												$sql  = "
																													select aarmatqtde, varmatumed
																													from sfpc.tbarmazenamentomaterial
																													where
																														clocmacodi = $Localizacao
																														and cmatepsequ = $Material[$i]
																												";
																												$res  = $db->query($sql);
																												if( db::isError($res) ){
																														$ErrosNoFor = 1;
																														$Rollback = 1;
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
																														exit(0);
																												}
																												$linha = $res->fetchRow();

																												$itemEstoqueQtde = $linha[0];
																												$itemEstoqueValor = $linha[1];


																												# Retorna o valor médio do item na nota fiscal

																												$sql  = "
																													SELECT VITENFUNIT, AITENFQTDE
																													FROM SFPC.TBITEMNOTAFISCAL
																													WHERE
																														CALMPOCODI = $NFAlmoxarifado
																														AND AENTNFANOE = $NFAno
																														AND CENTNFCODI = $NFCod
																														AND CMATEPSEQU = $Material[$i]
																												";
																												$res  = $db->query($sql);
																												if( db::isError($res) ){
																														$ErrosNoFor = 1;
																														$Rollback = 1;
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
																														exit(0);
																												}

																												$linha = $res->fetchRow();

																												$itemNFQtde = $linha[1];
																												$itemNFValor = $linha[0];

																												# Resgata o ultimo valor do item na ultima nota fiscal, antes da nota fiscal sendo cancelada #
																												$sqlultvalnot  = "
																													SELECT DISTINCT(VITENFUNIT) FROM SFPC.TBITEMNOTAFISCAL
																													WHERE
																														CMATEPSEQU = $Material[$i] AND CALMPOCODI = $NFAlmoxarifado
																														AND AENTNFANOE = $NFAno
																														AND CENTNFCODI = (
																															SELECT MAX(A.CENTNFCODI)
																															FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B
																															WHERE
																																A.CENTNFCODI <> $NFCod
																																AND A.CMATEPSEQU = $Material[$i]
																																AND A.CALMPOCODI = $NFAlmoxarifado AND A.AENTNFANOE = $NFAno
																																AND A.CALMPOCODI = B.CALMPOCODI
																																AND A.AENTNFANOE = B.AENTNFANOE
																																AND A.CENTNFCODI = B.CENTNFCODI
																																AND ( B.FENTNFCANC = 'N' or B.FENTNFCANC IS NULL )
																														)
																												";


																												$res  = $db->query($sqlultvalnot);

																												if( db::isError($res) ){
																														$ErrosNoFor = 1;
																														$Rollback = 1;
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlultvalnot, $res);
																														exit(0);
																												}

																												$Linha   = $res->fetchRow();
																												$ValorUnitarioUlt = $Linha[0];
																												if( is_null($ValorUnitarioUlt) ){
																														$ValorUnitarioUlt = 0;
																												}



																												//Considerar no cálculo do valor médio a movimentação do material no armazenamentomaterial ao cancelar a requisição (o valor da requisição é retornado)
																												$itemEstoqueQtde += $QtdMovimento;

																												$itemEstoqueValorTotal = $itemEstoqueQtde * $itemEstoqueValor;
																												$itemNFValorTotal = $itemNFQtde * $itemNFValor;

																												$valorMedioNovo = ($itemEstoqueValorTotal - $itemNFValorTotal) / ($itemEstoqueQtde - $itemNFQtde);
																												$valorMedioNovo = round($valorMedioNovo, 4);

																												if($ValorUnitarioUlt == 0){
																													$ValorUnitarioUlt = $valorMedioNovo;
																												}


																												//Quando quantidade em estoque é 0, valor é indeterminado
																												if($itemEstoqueQtde!=0){

																													//echo "[vm2 = $valorMedioNovo]";
																													//exit;

																													# Altera o valor médio para o novo #
																													$sqlUpdate  = "
																														UPDATE SFPC.TBARMAZENAMENTOMATERIAL
																														SET
																															CGREMPCODI = $GrupoEmp,
																															CUSUPOCODI = $Usuario,
																															TARMATULAT = '".date("Y-m-d H:i:s")."',
																															VARMATUMED = ".$valorMedioNovo.",
																															varmatultc = ".$ValorUnitarioUlt."
																														WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao
																													";
																													$resUpdate  = $db->query($sqlUpdate);

																													if( db::isError($resUpdate) ){
																															$ErrosNoFor = 1;
																															$Rollback = 1;
																															$db->query("ROLLBACK");
																															$db->query("END TRANSACTION");
																															$db->disconnect();
																															EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlUpdate, $resUpdate);
																															exit(0);
																													}


																												}

																												# Insere na tabela de Movimentação de Material do Tipo 8 - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																											$sql  = "
																												INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL (
																													CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI,
																													CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM,
																													VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT,
																													CMOVMACODT, AMOVMAMATR, NMOVMARESP, AENTNFANOE, CENTNFCODI
																												) VALUES (
																													$NFAlmoxarifado, ".date("Y").", $Movimento, '".date('Y-m-d')."',
																													8, NULL, $Material[$i], $QtdMovimento,
																													$ValorUnitarioUlt, $valorMedioNovo, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."',
																													$MovimentoTipo, NULL, NULL, $NFAno, $NFCod
																												)
																											";
																											//$sql ="Select * from SFPC.TBfornecedorcredenciado where false";
																											$result  = $db->query($sql);
																											if( db::isError($result) ){
																													$ErrosNoFor = 1;
																													$Rollback = 1;
																													$db->query("ROLLBACK");
																													$db->query("END TRANSACTION");
																													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $result);
																													$db->disconnect();
																											}else{
																												# Atualiza a flag de cancelamento da nota fiscal do item #
																												$sqlnf  = "
																													UPDATE SFPC.TBENTRADANOTAFISCAL
																														SET FENTNFCANC = 'S'
																														WHERE CALMPOCODI = $NFAlmoxarifado
																															AND AENTNFANOE = $NFAno
																															AND CENTNFCODI = $NFCod
																												";
																												$resnf  = $db->query($sqlnf);
																												if( db::isError($resnf) ){
																														$ErrosNoFor = 1;
																														$Rollback = 1;
																														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														exit(0);
																												}

																											}
																											$linha = $res->fetchRow();

																											$itemEstoqueQtde = $linha[0];
																											$itemEstoqueValor = $linha[1];

																											if($itemEstoqueQtde!=0){

																												# Retorna o valor médio do item na nota fiscal

																												$sql  = "
																													SELECT VITENFUNIT, AITENFQTDE
																													FROM SFPC.TBITEMNOTAFISCAL
																													WHERE
																														CALMPOCODI = $NFAlmoxarifado
																														AND AENTNFANOE = $NFAno
																														AND CENTNFCODI = $NFCod
																														AND CMATEPSEQU = $Material[$i]
																												";
																												$res  = $db->query($sql);
																												if( db::isError($res) ){
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
																														exit(0);
																												}

																												$linha = $res->fetchRow();

																												$itemNFQtde = $linha[1];
																												$itemNFValor = $linha[0];

																												$itemEstoqueValorTotal = $itemEstoqueQtde * $itemEstoqueValor;
																												$itemNFValorTotal = $itemNFQtde * $itemNFValor;

																												$valorMedioNovo = ($itemEstoqueValorTotal - $itemNFValorTotal) / ($itemEstoqueQtde - $itemNFQtde);

																												# Altera o valor médio para o novo #
																												$sqlUpdate  = "
																													UPDATE SFPC.TBARMAZENAMENTOMATERIAL
																													SET
																														CGREMPCODI = $GrupoEmp,
																														CUSUPOCODI = $Usuario,
																														TARMATULAT = '".date("Y-m-d H:i:s")."',
																														VARMATUMED = ".round($valorMedioNovo,4)."
																													WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao
																												";
																												$resUpdate  = $db->query($sqlUpdate);

																												if( db::isError($resUpdate) ){
																														$db->query("ROLLBACK");
																														$db->query("END TRANSACTION");
																														$db->disconnect();
																														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlUpdate, $resUpdate);
																														exit(0);
																												}


																											}

																										}
																									}
																							}
																					}
																			}
																	}//
																}
														}
												}
										}
										if (!$ErrosNoFor) {
												if(!$F5){ // Serve para não inserir dados repetidos no caso de F5 ter sido usado
														# Insere Situação em Requisição - Tipo 6 Cancelada #
														$sql    = "INSERT INTO SFPC.TBSITUACAOREQUISICAO ( ";
														$sql   .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, ";
														$sql   .= "CUSUPOCODI, ESITREMOTI, TSITREULAT ";
														$sql   .= ") VALUES ( ";
														$sql   .= "$SeqRequisicao, 6, '".date("Y-m-d H:i:s")."', ".$_SESSION['_cgrempcodi_'].", ";
														$sql   .= "".$_SESSION['_cusupocodi_'].", '$Motivo', '".date("Y-m-d H:i:s")."' )";
														$result = $db->query($sql);
														if( db::isError($result) ){
																$Rollback = 1;
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
															$db->query("COMMIT");
															$db->query("END TRANSACTION");
															$db->disconnect();
															unset($ItemRequisicao);
														}

												}
										}
										if(!$Rollback){
												$Mens     = 1;
												$Tipo     = 1;
												$Mensagem = urlencode("Requisição ".substr($Requisicao+100000,1)."/$AnoRequisicao Cancelada com Sucesso");
												$Url = "CadRequisicaoMaterialCancelarSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}
								}
						}
				}else{
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "O Usuário do grupo INTERNET não pode fazer Requisição de Material";
				}
		}
}

if( $Botao == "" ){
		# Verifica se o Usuário está ligado a algum centro de Custo #
		$db  = Conexao();
		$sql  = "SELECT USUCEN.CUSUPOCODI ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, ";
		$sql .= "       SFPC.TBGRUPOEMPRESA GRUEMP, SFPC.TBORGAOLICITANTE ORGSOL, SFPC.TBUSUARIOPORTAL USUPOR ";
		$sql .= " WHERE USUCEN.CGREMPCODI <> 0 AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
		$sql .= "   AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI AND CENCUS.CORGLICODI = ORGSOL.CORGLICODI ";
		$sql .= "   AND USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
		$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$sql .= " ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows == 0 ){
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "O Usuário não está ligado a nenhum Centro de Custo";
				}
		}

		# Carrega o Tipo do Usuário e Orgão Solicitante do GrupoEmpresa/Usuário Logado #
		$sql  = "SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS ";
		$sql .= " WHERE USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
		$sql .= "   AND ( ( USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
		$sql .= "           AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ) ";
		$sql .= "           OR ( USUCEN.CUSUPOCOD1 = ".$_SESSION['_cusupocodi_']." ";
		$sql .= "                AND USUCEN.CGREMPCOD1 = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "                AND '".date("Y-m-d")."' BETWEEN DUSUCCINIS AND DUSUCCFIMS ) ";
		$sql .= "       ) AND USUCEN.FUSUCCTIPO = 'T' ";
		$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if ($Rows != 0) {
						$Linha        = $res->fetchRow();
						$OrgaoUsuario = $Linha[1];
				}else{
						$sql  = "SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";
						$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS ";
						$sql .= " WHERE USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU  AND USUCEN.FUSUCCTIPO IN ('T','R') ";
						$sql .= "   AND ( (USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ) ";
						$sql .= "    OR (USUCEN.CUSUPOCOD1 = ".$_SESSION['_cusupocodi_']." AND USUCEN.CGREMPCOD1 = ".$_SESSION['_cgrempcodi_']." ";
						$sql .= "   AND '".date("Y-m-d")."' BETWEEN DUSUCCINIS AND DUSUCCFIMS ) ) AND USUCEN.FUSUCCTIPO <> 'T' ";
						$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Rows = $res->numRows();
								if ($Rows != 0) {
										$Linha        = $res->fetchRow();
										$OrgaoUsuario = $Linha[1];
								}
						}
				}
		}

		# Carrega os dados do usuário logado #
		$sql  = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL ";
		$sql .= " WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND CUSUPOCODI = ".$_SESSION['_cusupocodi_']."";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $res->fetchRow();
				$Nome  = $Linha[0];
		}

		# Pega os dados da Requisição de Material de acordo com o Sequencial #
		$sql  = "SELECT A.CREQMACODI, A.CGREMPCODI, A.CUSUPOCODI, B.AITEMRQTSO, ";
		$sql .= "       B.AITEMRQTAT, B.AITEMRORDE, C.CMATEPSEQU, C.EMATEPDESC, ";
		$sql .= "       D.EUNIDMSIGL, A.DREQMADATA ";
		$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA D  ";
		$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $SeqRequisicao ";
		$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
		$sql .= " ORDER BY B.AITEMRORDE ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				for( $i=0;$i<$Rows;$i++ ){
						$Linha                  = $res->fetchRow();
						$Requisicao             = $Linha[0];
						$GrupoEmp               = $Linha[1];
						$Usuario                = $Linha[2];
						$QtdSolicitada[$i]      = str_replace(".",",",$Linha[3]);
						if($Solicitacao != 1){
								if($Linha[4] != ""){
										$QtdAtendida[$i] = str_replace(".",",",$Linha[4]);
								}
						}
						$Material[$i]         = $Linha[6];
						$DescMaterial[$i]     = RetiraAcentos($Linha[7]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[7]);
						$Unidade[$i]          = $Linha[8];
						$DataRequisicao       = DataBarra($Linha[9]);
						$ItemRequisicao[$i]   = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdSolicitada[$i].$SimboloConcatenacaoArray.$QtdAtendida[$i];
				}
		}

		# Pega os dados da Última Situação da Requisicao #
		$sql  = "SELECT A.TSITREULAT, B.ETIPSRDESC, B.CTIPSRCODI ";
		$sql .= "  FROM SFPC.TBSITUACAOREQUISICAO A, SFPC.TBTIPOSITUACAOREQUISICAO B ";
		$sql .= " WHERE A.CREQMASEQU = $SeqRequisicao AND A.CTIPSRCODI = B.CTIPSRCODI ";
		$sql .= "   AND A.TSITREULAT =  ";
		$sql .= "      ( SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
		$sql .= "         WHERE CREQMASEQU = $SeqRequisicao ) ";
		$result = $db->query($sql);
		if( db::isError($result) ) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$DataSituacao = DataBarra($Linha[0]);
				$DescSituacao = $Linha[1];
				$Situacao     = $Linha[2];
		}
		$db->disconnect();
		if( $_SESSION['_cgrempcodi_'] == 0 ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem  = "O Usuário do grupo INTERNET não pode fazer Cancelamento de Requisição de Material";
		}
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
	document.CadRequisicaoMaterialCancelar.Botao.value = valor;
	document.CadRequisicaoMaterialCancelar.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=no,left=40,top=120,width='+largura+',height='+altura);
}
function ncaracteres(valor){
	document.CadRequisicaoMaterialCancelar.NCaracteres.value = '' +  document.CadRequisicaoMaterialCancelar.Motivo.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadRequisicaoMaterialCancelar.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRequisicaoMaterialCancelar.php" method="post" name="CadRequisicaoMaterialCancelar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Cancelamento
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
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
									CANCELAMENTO - REQUISIÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para cancelar uma Requisição de Material cadastrada clique no botão "Cancelar". As informações sobre a requisição cancelada serão retiradas definitivamento do sistema.
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra os Centro de Custo de Acordo com o Usuário Logado #
												$db  = Conexao();
												$sql    = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
												$sql   .= " WHERE CALMPOCODI = $Almoxarifado ";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]<br>";
												}
												$db->disconnect();
												?>
												<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo</td>
											<td class="textonormal">
												<?php
												# Pega os dados do Centro de Custo #
												$db   = Conexao();
												$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CCENPONRPA, A.ECENPODETA ";
												$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
												$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU ";
												$sql .= "   AND C.CREQMASEQU = $SeqRequisicao ";
												#$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												$sql .= " ORDER BY B.EORGLIDESC, A.ECENPODESC ";
												$res  = $db->query($sql);
												if( db::isError($res) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														$DescCentroCusto = $Linha[0];
														$DescOrgao       = $Linha[1];
														$RPA             = $Linha[2];
														$Detalhamento    = $Linha[3];
														echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
														echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
														echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
														echo $Detalhamento;
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Requisição</td>
											<td class="textonormal"><?php echo substr($Requisicao+100000,1)."/".$AnoRequisicao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário Requisitante</td>
											<td class="textonormal">
											<?php
											# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
											$db     = Conexao();
											$sql    = "
												SELECT USU.EUSUPOLOGI, USU.EUSUPORESP
												FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT
												WHERE SIT.CREQMASEQU = $SeqRequisicao    --AND SIT.CTIPSRCODI = 1
												AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI
											";
											$result = $db->query($sql);
											if( db::isError($result) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
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
											<td class="textonormal"><?php echo $DataRequisicao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
											<td class="textonormal"><?php echo $DescSituacao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação</td>
											<td class="textonormal"><?php echo $DataSituacao; ?></td>
										</tr>
										<?php if( $Situacao != 1 and $Almoxarifado != "" ){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if( $Localizacao != "" ){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if( db::isError($res) ){
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
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if( $Rows == 1 ){
																				$Linha = $res->fetchRow();
																				if( $Linha[1] == "E" ){
																						$Equipamento = "ESTANTE";
																				}if( $Linha[1] == "A" ){
																						$Equipamento = "ARMÁRIO";
																				}if( $Linha[1] == "P" ){
																						$Equipamento = "PALETE";
																				}
																				echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																				$Localizacao = $Linha[0];
																				echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
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
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
												<?php
												$db     = Conexao();
												$sql    = "SELECT A.EREQMAOBSE ";
												$sql   .= "FROM SFPC.TBREQUISICAOMATERIAL A ";
												$sql   .= "WHERE A.CREQMASEQU = $SeqRequisicao";
												$result = $db->query($sql);
												if( db::isError($result) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														$Observacao = $Linha[0];
												}
												$db->disconnect();
												?>
											<td class="textonormal"><?php if( $Observacao != "" ){ echo $Observacao; }else{ echo "NÃO INFORMADA"; }?></td>
										</tr>
										<?php } ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Motivo*</td>
											<td class="textonormal">
												<font class="textonormal">máximo de 200 caracteres</font>
												<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
												<textarea name="Motivo" cols="40" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Motivo; ?></textarea>
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
															ITENS DA REQUISIÇÃO
														</td>
													</tr>
													<?php
													for( $i=0;$i< count($ItemRequisicao);$i++ ){
															$Dados = split($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
															$DescMaterial[$i]  = $Dados[0];
															$Material[$i]      = $Dados[1];
															$Unidade[$i]       = $Dados[2];
															$QtdSolicitada[$i] = $Dados[3];
															$QtdAtendida[$i]   = $Dados[4];
															if ($i == 0) {
																	if( $Situacao != 1 ){
																			echo "		<tr>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\">ORDEM</td>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\">DESCRIÇÃO DO MATERIAL</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\" width=\"10%\" colspan=\"2\">QUANTIDADE</td>\n";
																			echo "		</tr>\n";
																			echo "		<tr>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">SOLICITADA</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">ATENDIDA</td>\n";
																			echo "		</tr>\n";
																	}else{
																			echo "		<tr>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">ORDEM</td>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">UNID.</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\" align=\"center\">QUANTIDADE</td>\n";
																			echo "		</tr>\n";
																	}
															}
													?>
													<tr>
														<td class="textonormal" align="center" width="5%">
															<?php echo $i+1; ?>
															<input type="hidden" name="ItemRequisicao[<?php echo $i; ?>]" value="<?php echo $ItemRequisicao[$i]; ?>">
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
														</td>
														<td class="textonormal" width="80%">
															<?
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialCancelar&Material=$Material[$i]";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',730,370);">
																<font color="#000000">
																	<?php
																	$Descricao = split($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																	echo $Descricao[1];
																	?>
																</font>
															</a>
															<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
														</td>
														<td class="textonormal" width="5%" align="center">
															<?php echo $Unidade[$i];?>
															<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php echo $QtdSolicitada[$i];?>
															<input type="hidden" name="QtdSolicitada[<?php echo $i; ?>]" size="11" maxlength="11" value="<?php echo $QtdSolicitada[$i]; ?>" class="textonormal">
														</td>
														<?php
														if( $Situacao != 1 ){
																if( $QtdAtendida[$i] != "" ){
																		echo "<td class=\"textonormal\" align=\"right\" width=\"10%\">$QtdAtendida[$i]\n";
																}else{
																		echo "<td class=\"textonormal\" align=\"center\" width=\"10%\">NÃO CADASTRADO\n";
																}
														?>
															<input type="hidden" name="QtdAtendida[<?php echo $i; ?>]" size="11" maxlength="11" value="<?php echo $QtdAtendida[$i]; ?>" class="textonormal">
														</td>
														<?php } ?>
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
									<input type="hidden" name="DataRequisicao" value="<?php echo $DataRequisicao; ?>">
									<input type="hidden" name="DescSituacao" value="<?php echo $DescSituacao; ?>">
									<input type="hidden" name="DataSituacao" value="<?php echo $DataSituacao; ?>">
									<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
									<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
									<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
									<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
									<input type="hidden" name="AnoRequisicao" value="<?php echo $AnoRequisicao; ?>">
									<input type="hidden" name="OrgaoUsuario" value="<?php echo $OrgaoUsuario; ?>">
									<input type="button" name="Cancelar" value="Cancelar Requisição" class="botao" onClick="javascript:enviar('Cancelar');">
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
