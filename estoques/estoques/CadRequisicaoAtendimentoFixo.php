<?php
#----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoAtendimentoFixo.php
# Autor:    Roberta Costa
# Data:     09/06/2005
# Alterado: Marcus Thiago
# Data:     12/01/2006
# Alterado: Álvaro Faria
# Data:     05/05/2006
# Alterado: Álvaro Faria
# Data:     02/06/2006
# Alterado: Wagner Barros
# Data:     28/07/2006
# Alterado: Álvaro Faria
# Data:     28/08/2006 - Correção para link de comprovante de entrega
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Padronização de variáveis de requisição
# Alterado: Álvaro Faria
# Data:     04/12/2006 - Verificação da existência de valor na tabela de
#                        armazenamento antes do atendimento
# Alterado: Ariston Cordeiro
# Data:     07/07/2008 - Alterações para suportar estoque virtual
# Alterado: Ariston Cordeiro
# Data:     22/07/2008 - Alterações para vincular o estoque virtual à nota fiscal relacionada
# Alterado: Ariston Cordeiro
# Data:     29/02/2012 - 	Defeito encontrado ao se cadastrar um atendimento depois de uma movimentação inativada no mesmo
# 												almoxarifado e mesmo ano. Corrigindo
#                      -  Corrigindo defeito em que a funcionalidade entra em loop infinito quando ja existe a chave tentando ser cadastrada
#----------------------
# Objetivo: Programa de Atendimento de Requisição de Material para Localização Fixa
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e"

$QtdEstoqueVirtual = array();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Requisicao     = $_POST['Requisicao'];
		$SeqRequisicao  = $_POST['SeqRequisicao'];
		$AnoRequisicao  = $_POST['AnoRequisicao'];
		$Situacao       = $_POST['Situacao'];
		$TipoUsuario    = $_POST['TipoUsuario'];
		$CentroCusto    = $_POST['CentroCusto'];
		$DataRequisicao = $_POST['DataRequisicao'];
		$TipoSituacao   = $_POST['TipoSituacao'];
		$DescMaterial   = $_POST['DescMaterial'];
		$Unidade        = $_POST['Unidade'];
		$DescUnidade    = $_POST['DescUnidade'];
		$Material       = $_POST['Material'];
		$QtdEstoqueVirtual = $_POST['QtdEstoqueVirtual'];
		$QtdSolicitada  = $_POST['QtdSolicitada'];
		$QtdAtendida    = $_POST['QtdAtendida'];
		$QtdEstoque     = $_POST['QtdEstoque']; //quantidade de estoque, real ou virtual, dependendo de $EstoqueVirtual
		$QtdEstoqueTotal = $_POST['QtdEstoqueTotal']; //quantidade de estoque total (real + virtual)
		$Ordem          = $_POST['Ordem'];
		$Localizacao    = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$NCaracteresO   = $_POST['NCaracteresO'];
		$Observacao     = strtoupper2($_POST['Observacao']);
		$RowsGeral      = $_POST['RowsGeral'];
    $EstoqueVirtual = $_POST['EstoqueVirtual'];
    $NumNota        = $_POST['NumNota'];
		$SerNota        = $_POST['SerNota'];
		$CodigoNFVirtual = $_POST['CodigoNFVirtual'];
}else{
		$SeqRequisicao  = $_GET['SeqRequisicao'];
		$AnoRequisicao  = $_GET['AnoRequisicao'];
		$Almoxarifado   = $_GET['Almoxarifado'];
    $EstoqueVirtual = $_GET['EstoqueVirtual'];
    $NumNota        = $_GET['NumNota'];
		$SerNota        = $_GET['SerNota'];
    $CodigoNFVirtual = $_GET['CodigoNFVirtual'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Atender"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoSelecionarFixo.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($Observacao != ""){
				if(strlen($Observacao) > 200){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoFixo.Observacao.focus();\" class=\"titulo2\">Observação no Máximo com 200 Caracteres</a>";
				}
		}

		# Validações de Quantidade Atendida #
		if( count($QtdAtendida) != 0 ){
				# Verifica se existe alguma quantidade antendida para efetuar o atendimento #
				if($Existe == ""){
						$Posicao = "";
						for($i=0; $i<count($QtdAtendida);$i++){
								if(str_replace(",",".",$QtdAtendida[$i]) != 0){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
						if($Existe == ""){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Posicao   = ( $Posicao * 11 ) + 13;
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Pelo menos um Item com a Quantidade Atendida";
						}
				}

				# Verifica se existe alguma quantidade igual a branco #
				$Existe  = "";
				$Posicao = "";
				for( $i=0;$i<count($QtdAtendida);$i++ ){
						if( $QtdAtendida[$i] == "" and $QtdEstoque[$i] != "" and $Existe == "" ){
								$Existe  = "S";
								$Posicao = $i;
						}
				}
				if($Existe == "S"){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Posicao   = ( $Posicao * 11 ) + 13;
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoFixo.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Atendida</a>";
				}

				# Verifica se as quantidades são menor que zero, só numeros e decimais #
				if($Existe == ""){
						$Posicao = "";
						for($i=0; $i<count($QtdAtendida); $i++){
								if($QtdAtendida[$i] < 0 and $Existe == ""){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
						for($k=0; $k<count($QtdAtendida); $k++){
								if($QtdAtendida[$k] != "" and $QtdEstoque[$k] != ""){
										if( ( ! SoNumVirg($QtdAtendida[$k]) ) and ( $Existe == "" ) ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if($Existe == ""){
								for($j=0; $j<count($QtdAtendida); $j++){
										if( ( ! Decimal($QtdAtendida[$j]) ) and $Existe == "" ){
												$Existe  = "S";
												$Posicao = $j;
										}
								}
						}
						if($Existe == "S"){
								if($Mens == 1){ $Mensagem .= ", "; }
								$Posicao   = ( $Posicao * 11 ) + 13;
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoFixo.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Atendida Válida</a>";
						}
				}
		}

		# Verifica se existe alguma quantidade atendida maior que a solicitada #
		if(count($QtdAtendida) != 0){
				for($i=0;$i<count($QtdAtendida);$i++){
						if($QtdAtendida[$i] != "" and $QtdEstoque[$i] != ""){
								$QAtendida   = str_replace(",",".",$QtdAtendida[$i]);
								$QSolicitada = str_replace(",",".",$QtdSolicitada[$i]);
								if($QtdAtendida[$i] != "" and $QtdAtendida[$i] != 0 and SoNumVirg($QtdAtendida[$i])) {
										if($QAtendida > $QSolicitada and $MaiorAtendida == ""){
												$MaiorAtendida = "S";
												$Posicao       = $i;
										}
								}
						}
				}
				if($MaiorAtendida == "S"){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Posicao   = ( $Posicao * 11 ) + 13;
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoFixo.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade atendida inferior a quantidade solicitada</a>";
				}
		}

		# Verifica se existe alguma quantidade atendida maior que a de estoque #
		if( count($QtdAtendida) != 0 ){
				for($i=0; $i<count($QtdAtendida); $i++){
						if( $QtdAtendida[$i] != "" and $QtdEstoque[$i] != "" and SoNumVirg($QtdAtendida[$i])){
								$QAtendida = str_replace(",",".",$QtdAtendida[$i]);
								$QEstoque  = str_replace(",",".",$QtdEstoque[$i]);
								if ($QAtendida != "" and $QAtendida != 0) {
										if( ($QAtendida > $QEstoque) ){
												$MaiorEstoque = "S";
												$Posicao      = $i;
										}
								}
						}
				}
				if($MaiorEstoque == "S"){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Posicao   = ($Posicao * 11) + 13;
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadRequisicaoAtendimentoFixo.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade atendida inferior a quantidade em estoque</a>";
				}
		}

		# Verifica se a requisição já foi atendida. Correção para o caso de duas pessoas chegarem
		# a esta tela simutaneamente, trabalhando em computadores diferentes, com a mesma
		# requisição (os dois clicando na tela de seleção antes do primeiro antendimento).
		# Caso um deles atenda, o segundo não poderá atender novamente, exibindo erro
		$db = Conexao();
		$sqltestaatend   = "SELECT MAX(CTIPSRCODI) FROM SFPC.TBSITUACAOREQUISICAO ";
		$sqltestaatend  .= "WHERE CREQMASEQU = $SeqRequisicao";
		$restestaatend   = $db->query($sqltestaatend);
		if( db::isError($restestaatend) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltestaatend");
		}else{
				$LinhaTestaAtend = $restestaatend->fetchRow();
				if( ($LinhaTestaAtend[0] == 3) or ($LinhaTestaAtend[0] == 4) ) { // 3 - Atendimento Total, 4 - Atendimento Parcial
						$Mens      = 1;
						$Tipo      = 2;
						$Troca     = 2;
						$Mensagem  = "Esta Requisição já foi Atendida, provavelmente por um acesso simultâneo. Verifique no Acompanhamento de Requisição";
				}
		}
		$db->disconnect();

		# Se não entrou em nenhuma Crítica #
		if($Mens == 0){
				$db = Conexao();
				# Evita duplicidade de gravação teclando F5 #
				$verifica    = "SELECT A.CREQMASEQU FROM SFPC.TBMOVIMENTACAOMATERIAL A WHERE ";
				$verifica   .= "CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoRequisicao AND ";
				$verifica   .= "CTIPMVCODI = 4 AND CREQMASEQU = $SeqRequisicao";
				$resverifica = $db->query($verifica);
				if( db::isError($resverifica) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $verifica");
				}else{
						$QtdVerifica = $resverifica->numRows();
						$db->disconnect();
						if($QtdVerifica != 0){ // Já houve inserção deste atendimento. Não insere de novo, mas não retorna erro.
								$F5 = 1;
						}else{
								//$CodErro = -3; // Para provocar a primeira entrada
								//while($CodErro == -3){
										$CodErro    = null; // Seta null em CodErro, para só voltar a ser -3 se houver outra chave duplicada na próxima tentativa
										$ErrosNoFor = null;
										$db = Conexao();
										$db->query("BEGIN TRANSACTION");
										$icountsituacao = 0;
										for($i=0; $i<count($QtdAtendida) and !$ErrosNoFor; $i++){
												# Se o material não for cadastrado #
												if($QtdAtendida[$i] == "N"){
														$QAtendida     = "NULL";
														$NaoCadastrado = "S";
												}
												if($QtdAtendida[$i] != "" and $QtdEstoque[$i] != ""){
														$QAtendida   = str_replace(",",".",$QtdAtendida[$i]);
														$QSolicitada = str_replace(",",".",$QtdSolicitada[$i]);
														$QEstoque    = str_replace(",",".",$QtdEstoque[$i]);
														$QEstoqueTotal    = str_replace(",",".",$QtdEstoqueTotal[$i]);
														# Verifica se existe alguma pendencia nas solicitações dos materiais #
														if( $QAtendida < $QSolicitada ){
																$icountsituacao++;
														}
														# Verifica o estoque, bloqueando linha de resultado, para gravar este resultado menos o atendimento na tabela de armazenamento. Se não tiver estoque suficiente, exibe erro #
														$sql  = "SELECT AARMATQTDE, AARMATVIRT, AARMATESTR FROM SFPC.TBARMAZENAMENTOMATERIAL ";
														$sql .= " WHERE CMATEPSEQU = $Material[$i] ";
														$sql .= "   AND CLOCMACODI = $Localizacao ";
														$sql .= "   FOR UPDATE ";
														$result  = $db->query($sql);
														if( db::isError($result) ){
																$ErrosNoFor = 1;
																$CodErroEmail  = $result->getCode();
																$DescErroEmail = $result->getMessage();
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
														}else{
																$Linha    = $result->fetchRow();
																$QEstoqueTotal = $Linha[0];
																if($EstoqueVirtual == 'S'){
																	$QEstoque = $Linha[1];
																}else{
																	//$QEstoque = $Linha[2]; Campo do real pode estar corrompido
																	$QEstoque = $Linha[0]-$Linha[1];
																}
																if($QAtendida > $QEstoque){
																		$Mens     = 1;
																		$Tipo     = 2;
																		$Troca    = 2;
																		$Mensagem = "Este Atendimento não pode ser efetuado, pois Quantidades em Estoque foram Alteradas por um Atendimento Simultâneo. Tente novamente, baseando-se no Estoque Atualizado"; // Houveram atendimentos simultâneos de outras requisições com algums itens desta requisição, e estes itens ficaram com estoque insuficiente para as Quantidades Atendidas especificadas. Repitir os atendimentos, verificando as Quantidades em Estoque já atualizadas
																}else{
																		# Atualiza a qtd atendida na tabela de itens da requisição #
																		$sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
																		$sql .= "   SET AITEMRQTAT = $QAtendida, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																		$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TITEMRULAT = '".date("Y-m-d H:i:s")."' ";
																		$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CREQMASEQU = $SeqRequisicao ";
																		$result  = $db->query($sql);
																		if( db::isError($result) ){
																				$ErrosNoFor = 1;
																				$CodErroEmail  = $result->getCode();
																				$DescErroEmail = $result->getMessage();
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																		}else{
																				# Atualiza a qtd atual do estoque #
																				$Diferenca = ( $QEstoqueTotal - $QAtendida );
																				$sql  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
																				$sql .= "   SET AARMATQTDE = $Diferenca, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																				$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TARMATULAT = '".date("Y-m-d H:i:s")."' ";
																				$DiferencaRealVirtual = ( $QEstoque - $QAtendida );
																				if($EstoqueVirtual == 'S'){
																					$sql .= "   , AARMATVIRT = ".$DiferencaRealVirtual." ";
																				}else{
																					$sql .= "   , AARMATESTR = ".$DiferencaRealVirtual." ";
																				}
																				$sql .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																				//echo "[".$sql."]";
																				$result = $db->query($sql);
																				if( db::isError($result) ){
																						$ErrosNoFor = 1;
																						$CodErroEmail  = $result->getCode();
																						$DescErroEmail = $result->getMessage();
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																				}else{
																						# Pega o Máximo valor do Movimento de Material #
																						$sqlmaxmov  = "SELECT MAX(CMOVMACODI) AS CODIGO FROM SFPC.TBMOVIMENTACAOMATERIAL";
																						$sqlmaxmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoRequisicao ";
																						//$sqlmaxmov .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																						$resmaxmov  = $db->query($sqlmaxmov);
																						if( db::isError($resmaxmov) ){
																								$ErrosNoFor = 1;
																								$CodErroEmail  = $resmaxmov->getCode();
																								$DescErroEmail = $resmaxmov->getMessage();
																								$db->query("ROLLBACK");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmaxmov\n\n$DescErroEmail ($CodErroEmail)");
																						}else{
																								$Linhamaxmov = $resmaxmov->fetchRow();
																								$Movimento   = $Linhamaxmov[0] + 1;
																								# Pega o Máximo valor do Movimento de Material do tipo - SAÍDA POR REQUISIÇÃO #
																								$sql  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																								$sql .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoRequisicao";
																								$sql .= "   AND CTIPMVCODI = 4 ";
																								//$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																								$res  = $db->query($sql);
																								if( db::isError($res) ){
																										$ErrosNoFor = 1;
																										$CodErroEmail  = $res->getCode();
																										$DescErroEmail = $res->getMessage();
																										$db->query("ROLLBACK");
																										$db->query("END TRANSACTION");
																										$db->disconnect();
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																								}else{
																										$Linha         = $res->fetchRow();
																										$TipoMovimento = $Linha[0] + 1;
																										# Pega o valor médio do estoque(armazenamento) #
																										$sqlarmat  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
																										$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																										$resarmat  = $db->query($sqlarmat);
																										if( db::isError($resarmat) ){
																												$ErrosNoFor = 1;
																												$CodErroEmail  = $resarmat->getCode();
																												$DescErroEmail = $resarmat->getMessage();
																												$db->query("ROLLBACK");
																												$db->query("END TRANSACTION");
																												$db->disconnect();
																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat\n\n$DescErroEmail ($CodErroEmail)");
																										}else{
																												$Linhaarmat = $resarmat->fetchRow();
																												$decValEstoque = str_replace(",",".",$Linhaarmat[0]);

																												if ( (!$decValEstoque) or ($decValEstoque == "NULL") ) {
																														$decValEstoque = "NULL";
																														$decValEstoqueMed = 0;
																												}else{
																														$decValEstoqueMed = $decValEstoque;
																												}
																												# Insere um movimento de material do tipo - SAÍDA POR REQUISIÇÃO #
																												if( $QAtendida != 0 and $QAtendida != "" ){
																														$sqlmovmat  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																														$sqlmovmat .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																														$sqlmovmat .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																														$sqlmovmat .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																														$sqlmovmat .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP ";

                                                            if($EstoqueVirtual == 'S'){
                                                              $sqlmovmat .= ", AENTNFANOE, CENTNFCODI  ";
                                                            }

																														$sqlmovmat .= ") VALUES ( ";
																														$sqlmovmat .= "$Almoxarifado, $AnoRequisicao, $Movimento, '".date('Y-m-d')."', ";
																														$sqlmovmat .= "4, $SeqRequisicao, $Material[$i], $QAtendida, ";
																														$sqlmovmat .= "$decValEstoque, $decValEstoqueMed, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."',";
																														$sqlmovmat .= "$TipoMovimento, NULL, NULL ";

                                                            if($EstoqueVirtual == 'S'){
                                                              $sqlmovmat .= " , $AnoRequisicao, $CodigoNFVirtual "; //O ano da requisição é o mesmo da nota fiscal virtual. Pois a requisição é realizada logo após a entrada da nota fiscal virtual.
                                                            }

																														$sqlmovmat .= " )";
																														$resmovmat  = $db->query($sqlmovmat);
																														if( db::isError($resmovmat) ){
																																$ErrosNoFor = 1;
																																$CodErro = $resmovmat->getCode();
																																$DescErro = $resmovmat->getMessage();
																																$db->query("ROLLBACK");
																																$db->query("END TRANSACTION");
																																$db->disconnect();
																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat\n\n$DescErro ($CodErro)");
																																/*if($CodErro != -3){ // Outro erro, diferente de chave duplicada, exibe mensagem de erro e envia e-mail para o analista
																																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat\n\n$DescErro ($CodErro)");
																																}*/
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
								//}
						}
				}

				# Se for > 0 existem Requisições com diferença de quantidades #
				if( $NaoCadastrado == "S" ){
						$SituacaoRequisicao = 4;     // ATENDIMENTO PARCIAL
				}else{
						if($icountsituacao > 0){
								$SituacaoRequisicao = 4; // ATENDIMENTO PARCIAL
						}else{
								$SituacaoRequisicao = 3; // ATENDIMENTO TOTAL
						}
				}

				if( !$ErrosNoFor and ($Mens == 0) ){ // Adicionado ($Mens == 0) em 4 de maio 2006, para não dar Commit caso o erro de estoque atual aconteça. Erro que aconteceu em Sec. Eduação quando duas pessoas atendiam requisições simutaneamente com o mesmo material. Este erro fazia o sistema usar estoque desatualizado para gravar na tabela de armazenamento.
						if(!$F5){ // Serve para não inserir dados repetidos no caso de F5 ou botão de atualizar (refresh) do navegador ter sido usado
								# Insere a situação na Requisição de Material #
								$sql  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO ( ";
								$sql .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, ";
								$sql .= "CGREMPCODI, CUSUPOCODI, TSITREULAT ";
								$sql .= ") VALUES ( ";
								$sql .= "$SeqRequisicao, $SituacaoRequisicao , '".date("Y-m-d H:i:s")."', ";
								$sql .= "".$_SESSION['_cgrempcodi_']." ,".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."' )";
								$result = $db->query($sql);
								if( db::isError($result) ){
										$Rollback = 1;
										$CodErroEmail  = $result->getCode();
										$DescErroEmail = $result->getMessage();
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								}else{
										# Atualiza a Requisição de Material #
										$sql   = "UPDATE SFPC.TBREQUISICAOMATERIAL ";
										$sql  .= "   SET CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
										$sql  .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
										$sql  .= "       TREQMAULAT = '".date("Y-m-d H:i:s")."' ";
										if( $Observacao != "" ){
												$sql .= " , EREQMAOBSE = '$Observacao' ";
										}
										$sql  .= " WHERE CREQMASEQU = $SeqRequisicao ";
										$res   = $db->query($sql);
										if( db::isError($res) ){
												$Rollback = 1;
												$CodErroEmail  = $res->getCode();
												$DescErroEmail = $res->getMessage();
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();
										}
								}
						}
						if(!$Rollback){
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.Mens.value=2</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.Tipo.value=1</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.SeqRequisicao.value='$SeqRequisicao'</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.AnoRequisicao.value='$AnoRequisicao'</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.Almoxarifado.value='$Almoxarifado'</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.EstoqueVirtual.value='N'</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.NumNota.value=''</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.SerNota.value=''</script>";
								echo "<script>opener.document.CadRequisicaoAtendimentoSelecionarFixo.submit()</script>";
								echo "<script>self.close()</script>";
								exit;
						}
				}
		}
}

if($Botao == ""){
		$NCaracteresO = strlen($Observacao);
		$ItensSemValor = 0;
		$db   = Conexao();
		# Descobre a localização, se esta ainda não for sabida #
		if(!$Localizacao){
				$sqlLoc    = "SELECT CLOCMACODI ";
				$sqlLoc   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL ";
				$sqlLoc   .= " WHERE CALMPOCODI = $Almoxarifado AND FLOCMASITU = 'A'";
				$resLoc  = $db->query($sqlLoc);
				if( db::isError($resLoc) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLoc");
				}else{
						$RowsLoc = $resLoc->numRows();
						if($RowsLoc == 1){
								$LinhaLoc = $resLoc->fetchRow();
								$Loc = $LinhaLoc[0];
						}
				}
		}else{
				$Loc = $Localizacao;
		}
		# Pega os dados da Requisição de Material de acordo com o Sequencial #
		$sql  = "SELECT DISTINCT(REQ.CREQMACODI), REQ.DREQMADATA, ITE.AITEMRQTSO, ITE.AITEMRQTAP, ";
		$sql .= "       ITE.AITEMRQTAT, ITE.AITEMRQTCA, ITE.AITEMRORDE, MAT.CMATEPSEQU, ";
		$sql .= "       MAT.EMATEPDESC, UND.CUNIDMCODI, UND.EUNIDMSIGL ";
		$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL REQ ";
		$sql .= " INNER JOIN SFPC.TBSITUACAOREQUISICAO SITREQ ON (REQ.CREQMASEQU = SITREQ.CREQMASEQU) ";
		$sql .= " INNER JOIN SFPC.TBTIPOSITUACAOREQUISICAO TIPSIT ON (TIPSIT.CTIPSRCODI = SITREQ.CTIPSRCODI) ";
		$sql .= " INNER JOIN SFPC.TBITEMREQUISICAO ITE ON (REQ.CREQMASEQU = ITE.CREQMASEQU) ";
		$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ON (ITE.CMATEPSEQU = MAT.CMATEPSEQU) ";
		$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON (MAT.CUNIDMCODI = UND.CUNIDMCODI) ";
		$sql .= "  LEFT JOIN SFPC.TBARMAZENAMENTOMATERIAL ARM ON (MAT.CMATEPSEQU = ARM.CMATEPSEQU) ";
		$sql .= " WHERE REQ.AREQMAANOR = $AnoRequisicao AND REQ.CREQMASEQU = $SeqRequisicao ";
		$sql .= " ORDER BY ITE.AITEMRORDE ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$RowsGeral = $res->numRows();
				for($i=0; $i<$RowsGeral and !$ErrosNoFor; $i++){
						$Linha             = $res->fetchRow();
						$Requisicao        = $Linha[0];
						$DataRequisicao    = DataBarra($Linha[1]);
						$QtdSolicitada[$i] = str_replace(".",",",$Linha[2]);
						$QtdAtendida[$i]   = str_replace(".",",",$Linha[4]);
						$Ordem[$i]         = $Linha[6];
						$Material[$i]      = $Linha[7];
						$DescMaterial[$i]  = $Linha[8];
						$Unidade[$i]       = $Linha[9];
						$DescUnidade[$i]   = $Linha[10];
						if($Loc){
								# Descobre os valores dos materiais, se não tiver valor, ou estiver zerado, emite mensagem de erro logo na abertura #
								$sqlZer  = "SELECT COUNT(*) ";
								$sqlZer .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL ";
								$sqlZer .= " WHERE CMATEPSEQU = $Material[$i] ";
								$sqlZer .= "   AND CLOCMACODI = $Loc ";
								$sqlZer .= "   AND (VARMATUMED IS NULL or VARMATUMED = 0) ";
								$reszer  = $db->query($sqlZer);
								if( db::isError($resZer) ) {
										$ErrosNoFor = 1;
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlZer");
								}else{
										$LinhaZer = $reszer->fetchRow();
										if($LinhaZer[0] > 0){
												$ItensSemValor = $ItensSemValor + 1;
												$MatsSemValor[] = $DescMaterial[$i]." (".$Material[$i].")";
										}
								}
						}
				}
		}
		# Pega os dados do Centro de Custo #
		$sql    = "SELECT A.CCENPOSEQU ";
		$sql   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
		$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU ";
		$sql   .= "   AND C.CREQMASEQU = $SeqRequisicao ";
		$sql   .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$sql   .= " ORDER BY B.EORGLIDESC, A.ECENPODESC ";
		$result = $db->query($sql);
		if( db::isError($result) ) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha       = $result->fetchRow();
				$CentroCusto = $Linha[0];
		}
		$db->disconnect();

		if($ItensSemValor > 0){
				$Mens         = 1;
				$Tipo         = 2;
				$Troca        = 2;
				$ItemSemValor = 0;
				foreach($MatsSemValor as $MatSemValor){
						$ItemSemValor = $ItemSemValor + 1;
						if($DescMatSemValor){
								if($ItemSemValor == $ItensSemValor){
										$DescMatSemValor = $DescMatSemValor." e ";
								}else{
										$DescMatSemValor = $DescMatSemValor."; ";
								}
								$DescMatSemValor .= $MatsSemValor[$ItemSemValor-1];
						}else{
								$DescMatSemValor  = $MatsSemValor[$ItemSemValor-1];
						}
				}
				if($ItensSemValor > 1){
						$Mensagem = "Os materiais $DescMatSemValor não possuem valor especificado no Almoxarifado/Localização. Especifique valores para estes materiais antes de atender esta Requisição";
				}else{
						$Mensagem = "O material $DescMatSemValor não possui valor especificado no Almoxarifado/Localização. Especifique um valor para este material antes de atender esta Requisição";
				}
		}

		if(($EstoqueVirtual == 'S') && (isset($Material)) ){
			$db = Conexao();
			for($itr=0;$itr<count($Material);$itr++){
				# Calcular valor do estoque virtual para a nota fiscal.
				# Formula: (Estoque Virtual atual em uma nota fiscal) = (QUANTIDADE DO ITEM NA NOTA FISCAL) - (QUANTIDADE ATENDIDA EM REQUERIMENTOS, DESTE ITEM, PELA NOTA FISCAL, QUE NÃO FORAM CANCELADAS)
				$sql   = "
					SELECT (
						SELECT aitenfqtde
						FROM SFPC.TBitemnotafiscal inf
						WHERE
							inf.cmatepsequ = '".$Material[$itr]."' and
							inf.calmpocodi = '".$Almoxarifado."' and
							inf.aentnfanoe = '".$AnoRequisicao."' and
							inf.centnfcodi = '".$CodigoNFVirtual."'
					)-(
						SELECT coalesce(sum(ir.aitemrqtat),0)
						FROM
							SFPC.TBmovimentacaomaterial mm,
							SFPC.TBrequisicaomaterial rm,
							SFPC.TBsituacaorequisicao sr,
							SFPC.TBitemrequisicao ir
						WHERE
							mm.cmatepsequ = '".$Material[$itr]."' and
							mm.calmpocodi = '".$Almoxarifado."' and
							mm.aentnfanoe = '".$AnoRequisicao."' and
							mm.centnfcodi = '".$CodigoNFVirtual."' and
							mm.creqmasequ = rm.creqmasequ and
							rm.creqmasequ = sr.creqmasequ and
							sr.ctipsrcodi != '6' and
							ir.creqmasequ = rm.creqmasequ and
							ir.cmatepsequ = mm.cmatepsequ and
							sr.tsitresitu = (
								SELECT max(sr1.tsitresitu)
								FROM
									SFPC.TBmovimentacaomaterial mm1,
									SFPC.TBrequisicaomaterial rm1,
									SFPC.TBsituacaorequisicao sr1
								WHERE
									mm1.cmatepsequ = mm.cmatepsequ and
									mm1.calmpocodi = mm.calmpocodi and
									mm1.aentnfanoe = mm.aentnfanoe and
									mm1.centnfcodi = mm.centnfcodi and
									mm1.creqmasequ = mm.creqmasequ and
									mm1.creqmasequ = rm1.creqmasequ and
									rm1.creqmasequ = sr1.creqmasequ
							)
					)
				";
				$res  = $db->query($sql);
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit(0);
				}
				//echo "[sql: ".$sql."]";
				$Linha = $res->fetchRow();
				$QtdEstoqueVirtual[$itr]=$Linha[0];
			}
			$db->disconnect();
		}
}
?>
<html>
<head>
<title>Portal de Compras - Prefeitura do Recife</title>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadRequisicaoAtendimentoFixo.Botao.value = valor;
	document.CadRequisicaoAtendimentoFixo.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
}
function ncaracteresO(valor){
	document.CadRequisicaoAtendimentoFixo.NCaracteresO.value = '' +  document.CadRequisicaoAtendimentoFixo.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadRequisicaoAtendimentoFixo.NCaracteresO.focus();
	}
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<form action="CadRequisicaoAtendimentoFixo.php" method="post" name="CadRequisicaoAtendimentoFixo">
<table cellpadding="3" border="0" summary="">
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									ATENDIMENTO - REQUISIÇÃO DE MATERIAL <?php if($EstoqueVirtual == 'S'){echo "POR NOTA FISCAL VIRTUAL";}?>
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para atender a Requisição de Material, informe os dados abaixo e clique no botão "Atender".<BR>Os itens obrigatórios estão com *.
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
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
												$sql .= " WHERE CALMPOCODI = $Almoxarifado AND FALMPOSITU = 'A'";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo</td>
											<td class="textonormal">
												<?php
												# Mostra os dados de Centro de Custo #
												$sql    = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
												$sql   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
												$sql   .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto";
												$sql	 .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												$result = $db->query($sql);
												if( db::isError($result) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $result->fetchRow() ){
																$DescCentroCusto = $Linha[0];
																$DescOrgao       = $Linha[1];
																$Orgao           = $Linha[2];
																$RPA             = $Linha[3];
																$Detalhamento    = $Linha[4];
														}
														echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
														echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
														echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
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
												$sql    = "SELECT USU.EUSUPORESP ";
												$sql   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
												$sql   .= " WHERE SIT.CREQMASEQU = $SeqRequisicao    AND SIT.CTIPSRCODI = 1 ";
												$sql   .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
												$result = $db->query($sql);
												if( db::isError($result) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														$Nome  = $Linha[0];
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
											<td class="textonormal">
												<?php
												# Mostra a situaçaõ da requisição #
												$db     = Conexao();
												$sql    = "SELECT ETIPSRDESC FROM SFPC.TBREQUISICAOMATERIAL REQMAT ";
												$sql   .= "INNER JOIN SFPC.TBSITUACAOREQUISICAO SITREQ ON (REQMAT.CREQMASEQU = SITREQ.CREQMASEQU) ";
												$sql   .= "INNER JOIN SFPC.TBTIPOSITUACAOREQUISICAO TIPSIT ON (TIPSIT.CTIPSRCODI = SITREQ.CTIPSRCODI) ";
												$sql   .= "WHERE REQMAT.CREQMASEQU = $SeqRequisicao ";
												$result = $db->query($sql);
												if( db::isError($result) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														echo $Linha[0];
												}
												$db->disconnect();
												?>
											</td>
										</tr>

										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if($Localizacao != ""){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if($Linha[0] == "E"){
																		$Equipamento = "ESTANTE";
																}if($Linha[0] == "A"){
																		$Equipamento = "ARMÁRIO";
																}if($Linha[0] == "P"){
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
																if($Rows == 0){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if($Rows == 1){
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

                    <?php if($EstoqueVirtual == 'S') { ?>
                    <tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota</td>
											<td class="textonormal"><?php echo $NumNota."/".$AnoRequisicao; //O Ano da requisição é o mesmo ano da nota fiscal, logo, o ano da requisição pode ser usado para o ano da nota fiscal ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Série da Nota</td>
											<td class="textonormal"><?php echo $SerNota; ?></td>
										</tr>
                    <?php } ?>

										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
											<td class="textonormal">
												<font class="textonormal">máximo de 200 caracteres</font>
												<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO ?>" class="textonormal"><br>
													<?php
													# Mostra o conteúdo da descrição #
													$db     = Conexao();
													$sql    = "SELECT EREQMAOBSE FROM SFPC.TBREQUISICAOMATERIAL REQMAT ";
													$sql   .= "WHERE REQMAT.CREQMASEQU = $SeqRequisicao ";
													$result = $db->query($sql);
													if( db::isError($result) ) {
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Linha = $result->fetchRow();
															$Observacao = $Linha[0];
													}
													$db->disconnect();
													?>
												<textarea name="Observacao" cols="50" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
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
													<?php
													for($i=0; $i< count($Material); $i++){
															if($i == 0){
																	echo "		<tr>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">ORDEM</td>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">CÓD.RED.</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\" width=\"10%\" colspan=\"4\">QUANTIDADE</td>\n";
																	echo "		</tr>\n";
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">SOLICITADA</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">ESTOQUE</td>\n";
                                  echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">ATENDIDA</td>\n";
																	echo "		</tr>\n";
															}
													?>
													<tr>
														<td class="textonormal" align="center">
															<?php echo $Ordem[$i];?>
															<input type="hidden" name="Ordem[<?php echo $i; ?>]" value="<?php echo $Ordem[$i]; ?>">
														</td>
														<td class="textonormal">
															<?
															$Url = "CadItemDetalhe.php?Material=$Material[$i]";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',700,350);"><font color="#000000"><?php echo $DescMaterial[$i];?></font></a>
															<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
															<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
															<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
															<input type="hidden" name="TipoSituacao" value="<?php echo $TipoSituacao; ?>">
															<input type="hidden" name="DataRequisicao" value="<?php echo $DataRequisicao; ?>">
														</td>
														<td class="textonormal" align="center" width="5%">
															<?php echo $Material[$i];?>
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
														</td>
														<td class="textonormal" align="center" width="5%">
															<?php echo $DescUnidade[$i];?>
															<input type="hidden" name="DescUnidade[<?php echo $i; ?>]" value="<?php echo $DescUnidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right">
															<?php if( $QtdSolicitada[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada[$i]))); } ?>
															<input type="hidden" name="QtdSolicitada[<?php echo $i; ?>]" value="<?php echo $QtdSolicitada[$i]; ?>">
														</td>
														<?php
                            if($Localizacao != ""){
                                # Mostra os estoques de acordo com o Almoxarifado #
                                $db   = Conexao();
                                $sql  = "
																	SELECT
																		ARM.AARMATQTDE, ARM.AARMATVIRT, ARM.AARMATESTR
																	FROM SFPC.TBREQUISICAOMATERIAL REQ
																		INNER JOIN SFPC.TBITEMREQUISICAO ITEM ON (REQ.CREQMASEQU = ITEM.CREQMASEQU)
																		INNER JOIN SFPC.TBMATERIALPORTAL MAT ON (ITEM.CMATEPSEQU = MAT.CMATEPSEQU)
																		LEFT JOIN SFPC.TBARMAZENAMENTOMATERIAL ARM ON (MAT.CMATEPSEQU = ARM.CMATEPSEQU)
																	WHERE
																		REQ.AREQMAANOR = $AnoRequisicao AND
																		REQ.CREQMASEQU = $SeqRequisicao AND
																		ARM.CLOCMACODI = $Localizacao AND
																		ARM.CMATEPSEQU = $Material[$i]
																";
                                $res  = $db->query($sql);
																//echo "[".$sql."]";
                                if( db::isError($res) ){
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                }else{
                                    $Rows = $res->numRows();
                                    if($Rows != 0){
                                        $ExisteArmazenamento[$i] = "";
                                        $Linha = $res->fetchRow();
																				$QtdEstoqueTotal[$i] = str_replace(".",",",$Linha[0]);
																				if($EstoqueVirtual == 'S'){
																					# estoque na nota virtual na nota fiscal informada
																					//$QtdEstoque[$i] = str_replace(".",",",$Linha[1]);
																					$QtdEstoque[$i] = str_replace(".",",",$QtdEstoqueVirtual[$i]);
																				}else{
																					# pegar movimantaçoes de requisicoes atendidas pela mesma nota
																					$QtdEstoque[$i] = str_replace(".",",",$Linha[0]-$Linha[1]);
																					//$QtdEstoque[$i] = str_replace(".",",",$Linha[2]); campo do real pode não ser consistente
																				}
                                        echo "<td class=\"textonormal\" align=\"right\">\n";
                                        echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque[$i])));
                                    }else{
                                        $ExisteArmazenamento[$i] = "S";
                                        $QtdEstoque[$i] = "";
                                        echo "<td class=\"textonormal\" align=\"center\">\n";
                                        echo "NÃO CADASTRADO";
                                    }
                                    $db->disconnect();
                                }
                            }else{
                                echo "<td colspan='2' class='textonormal' align='right'>S/Localização</td>";
                            }

														?>
															<input type="hidden" name="QtdEstoqueTotal[<?php echo $i; ?>]" value="<?php echo $QtdEstoqueTotal[$i]; ?>">
															<input type="hidden" name="QtdEstoque[<?php echo $i; ?>]" value="<?php echo $QtdEstoque[$i]; ?>">
															<input type="hidden" name="QtdEstoqueVirtual[<?php echo $i; ?>]" value="<?php echo $QtdEstoqueVirtual[$i]; ?>">
														</td>
														<?php
														if($Localizacao != ""){
																echo '<td class="textonormal" align="center">';
																if($ExisteArmazenamento[$i] == "S"){
																		$QtdAtendida[$i] = "";
																		echo "NÃO CADASTRADO";
																		?>
																		<input type="hidden" name="QtdAtendida[<?php echo $i; ?>]" value="N">
																		<?php
																}else{
																	if($EstoqueVirtual == 'S'){
                                    $QtdAtendida[$i] = $QtdSolicitada[$i];
                                    echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida[$i])));
                                    ?>
                                    <input type="hidden" name="QtdAtendida[<?php echo $i; ?>]" value="<?php echo $QtdAtendida[$i]; ?>">

                                  <?php
                                  } else {
                                  ?>
																		<input type="text" class="textonormal" name="QtdAtendida[<?php echo $i; ?>]" value="<?php if ($QtdAtendida[$i] != '0' and SoNumeros($QtdAtendida[$i])) { echo converte_valor_estoques(sprintf('%01.4f',str_replace(',','.',$QtdAtendida[$i]))); }else{ echo $QtdAtendida[$i]; } ?>" maxlength="11" size="11">
																	<?php
																  }
																	echo "</td>";
                                }
														}else{
																echo "&nbsp;";
														}
														?>
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
									<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
									<input type="hidden" name="AnoRequisicao" value="<?php echo $AnoRequisicao; ?>">
									<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
									<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
                  <input type="hidden" name="EstoqueVirtual" value="<?php echo $EstoqueVirtual; ?>">
                  <input type="hidden" name="NumNota" value="<?php echo $NumNota; ?>">
                  <input type="hidden" name="SerNota" value="<?php echo $SerNota; ?>">
                  <input type="hidden" name="CodigoNFVirtual" value="<?php echo $CodigoNFVirtual; ?>">
									<input type="hidden" name="RowsGeral" value="<?php echo $RowsGeral; ?>">
									<input type="button" name="Atender" value="Atender" class="botao" onClick="javascript:enviar('Atender');" <?php if($Localizacao == "" or $ItensSemValor > 0) { echo "disabled"; }?>>
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:self.close();">
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
<script language="javascript" type="">
<!--
window.focus();
-->
</script>
</body>
</html>
