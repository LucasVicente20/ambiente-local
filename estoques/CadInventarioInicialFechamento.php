<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioInicialFechamento.php
# Objetivo: Programa de Geração(Inclusão/Alteração) de Inventário do Estoque
# Autor:    Carlos Abreu
# Data:     10/11/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Autor:    Rodrigo Melo
# Data:     05/05/2008 - Ajuste nas movimentações para chamar a rotina de lançamento contábil.
# Autor:    Rodrigo Melo
# Data:     13/03/2009 - Correção para permitir o fechamento do inventário.
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

#Acesso a rotina de lançamento custo/contábil
include "../oracle/estoques/RotLancamentoCustoContabil.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$Localizacao         = $_POST['Localizacao'];
		$DataBase            = $_POST['DataBase'];
		$Responsavel         = strtoupper2(RetiraAcentos($_POST['Responsavel']));
		$Matricula           = $_POST['Matricula'];
} else {
    $Mens           = $_GET['Mens'];
		$Tipo           = $_GET['Tipo'];
		$Mensagem       = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano de Exercício #
$AnoExercicio = date("Y");

if( $Localizacao != "" ){
		# Resgata a flag do fechamento #
		$db   = Conexao();
		$sql  = "SELECT FINVCOFECH ";
		$sql .= "FROM SFPC.TBINVENTARIOCONTAGEM ";
		$sql .= "WHERE CLOCMACODI = $Localizacao AND AINVCOANOB = $AnoExercicio ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         = $res->fetchRow();
				$FlgFechamento = $Linha[0];
				if( $FlgFechamento == "S" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 1;
						$Mensagem .= "Inventário Fechado. A geração não pode ser efetuada";
				}
		}
		$db->disconnect();
}

if( $Botao == "Fechar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialFechamento.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if ($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialFechamento.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$MensErro = ValidaData($DataBase);
		if( $MensErro != "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialFechamento.DataBase.focus();\" class=\"titulo2\">Data Base Válida</a>";
		}
		if ($Responsavel == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialFechamento.Responsavel.focus();\" class=\"titulo2\">Responsável</a>";
		}
		if (!SoNumeros($Matricula)) {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialFechamento.Matricula.focus();\" class=\"titulo2\">Matrícula</a>";
		}
		if( $Mens == 0 ){
				$db = Conexao();
				# Identifica dados do inventário aberto
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU
						FROM SFPC.TBINVENTARIOCONTAGEM A
						 WHERE A.CLOCMACODI=$Localizacao
						   AND A.FINVCOFECH IS NULL
						   AND A.AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
						 GROUP BY A.AINVCOANOB";
				$res  = $db->query($sql);
				if(PEAR::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if( $Rows != 0 ){
								$Linha = $res->fetchRow();
								$Ano        = $Linha[0];
								$Sequencial = $Linha[1];

								# Retorna quantidade de itens do inventario com quantidade ou valor iguais a zero
								$sql = "SELECT COUNT(*)
													FROM SFPC.TBINVENTARIOMATERIAL
												 WHERE CLOCMACODI = $Localizacao
													 AND AINVCOANOB = $Ano
													 AND AINVCOSEQU = $Sequencial
													 AND (VINVMAUNIT = 0 OR VINVMAUNIT IS NULL OR AINVMAESTO = 0 OR AINVMAESTO IS NULL)";
								$res  = $db->query($sql);
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
										$Linha = $res->fetchRow();
										if ( $Linha[0] > 0 ){
												$Mens           = 1;
												$Tipo           = 1;
												$Mensagem       = "O inventário não pode ser fechado pois possui item(s) com quantidade e/ou valor a ser(em) preenchido(s) na contagem";
										} else {
												$datahora = date("Y-m-d H:i:s");

												$db->query('BEGIN TRANSACTION');
												# Ativa a Flag de inventario encerrado
												$sql  = "UPDATE SFPC.TBINVENTARIOCONTAGEM ";
												$sql .= "   SET FINVCOFECH = 'S', ";
												$sql .= "       TINVCOFECH = '$datahora', ";
												$sql .= "       TINVCOULAT = '$datahora', ";
												$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
												$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
												$sql .= " WHERE CLOCMACODI = $Localizacao";
												$sql .= "   AND AINVCOANOB = $Ano";
												$sql .= "   AND AINVCOSEQU = $Sequencial";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$res  = $db->query('ROLLBACK');
														$res  = $db->query('END TRANSACTION');
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												}
												# Libera Almoxarifado para utilizacao
												$sql  = "UPDATE SFPC.TBALMOXARIFADOPORTAL ";
												$sql .= "   SET FALMPOINVE = 'N' ";
												$sql .= " WHERE CALMPOCODI = $Almoxarifado";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$res  = $db->query('ROLLBACK');
														$res  = $db->query('END TRANSACTION');
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												}

												# Pega dados dos materiais sob invetario
												$sql  = "SELECT A.CMATEPSEQU, A.AINVMAESTO, A.VINVMAUNIT, D.FGRUMSTIPM ";
												$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
												$sql .= "       SFPC.TBCLASSEMATERIALSERVICO C, SFPC.TBGRUPOMATERIALSERVICO D, ";
												$sql .= "       SFPC.TBMATERIALPORTAL E ";
												$sql .= " WHERE A.CLOCMACODI = $Localizacao ";
												$sql .= "   AND A.AINVCOANOB = $Ano ";
												$sql .= "   AND A.AINVCOSEQU = $Sequencial ";
												$sql .= "   AND A.CMATEPSEQU = E.CMATEPSEQU ";
												$sql .= "   AND E.CSUBCLSEQU = B.CSUBCLSEQU ";
												$sql .= "   AND B.CGRUMSCODI = C.CGRUMSCODI ";
												$sql .= "   AND B.CCLAMSCODI = C.CCLAMSCODI ";
												$sql .= "   AND C.CGRUMSCODI = D.CGRUMSCODI ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														$db->query('ROLLBACK');
														$db->query('END TRANSACTION');
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												} else {
														$Rows = $res->numRows();
														if( $Rows != 0 ){
																# Pega o Máximo valor da Movimentação #
																$sqlmov  = "SELECT MAX(CMOVMACODI) ";
																$sqlmov .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL";
																$sqlmov .= " WHERE CALMPOCODI = $Almoxarifado ";
																$sqlmov .= "   AND AMOVMAANOM = ".date("Y")."";
																//$sqlmov .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
																$resmov  = $db->query($sqlmov);
																if(PEAR::isError($resmov)){
																		$db->query('ROLLBACK');
																		$db->query('END TRANSACTION');
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
																		$db->disconnect();
																		exit;
																}else{
																		$LinhaMov  = $resmov->fetchRow();
																		$Movimento = $LinhaMov[0] + 1;
																}

																# Pega o Máximo valor do Movimento do Material do Tipo - SALDO INICIAL #
																$sqltipo  = "SELECT MAX(CMOVMACODT) ";
																$sqltipo .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL";
																$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado ";
																$sqltipo .= "   AND AMOVMAANOM = ".date("Y")." ";
																$sqltipo .= "   AND CTIPMVCODI = 1 ";
																//$sqltipo .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
																$restipo  = $db->query($sqltipo);
																if( PEAR::isError($restipo) ){
																		$db->query('ROLLBACK');
																		$db->query('END TRANSACTION');
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																		$db->disconnect();
																		exit;
																}else{
																		$LinhaTipo     = $restipo->fetchRow();
																		$TipoMovimento = $LinhaTipo[0] + 1;
																}

                                #Preparando os parâmetros para o lançamento custo/contábil
                                $SubElementosDespesa = array(); //Array que contém os sub-elementos de despesa
                                $ValoresSubelementos = array(); //Valores dos sub-elementos de despesa
                                $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                                $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                                $DiaBaixa             = date("d");
    														$MesBaixa             = date("m");
    														$AnoBaixa             = date("Y");
    														$AnoMovimentacao      = date("Y");

																for( $i=0;$i< $Rows; $i++ ){
																		$Linha = $res->fetchRow();
																		$Material   = $Linha[0];
																		$Quantidade = $Linha[1];
																		$Valor      = $Linha[2];
                                    $TipoMaterialTESTE = $Linha[3];

                                    /*
                                                                                       PARA LANÇAMENTO DE CUSTO
                                                                                                            - ACUMULAR CONFORME OS ITENS DE GASTO E CASO O RESULTADO SEJA POSITIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "ENTRADA POR GERAÇÃO DE INVENTÁRIO (33)"
                                                                                                            - ACUMULAR CONFORME OS ITENS DE GASTO E CASO O RESULTADO SEJA NEGATIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "SAÍDA POR GERAÇÃO DE INVENTÁRIO (34)"
                                                                                  */
                                    //Obtendo os Sub-elementos de despesa
                                    $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                                    $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                                    $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                                    $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                                    $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                                    $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                                    $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                                    $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                                    $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                                    $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                                    $sql .= "       MAT.CMATEPSEQU = $Material ";

                                    $resSubElemento  = $db->query($sql);

                                    if( PEAR::isError($resSubElemento) ){
                                        $RollBack = 1;
                                        $db->query("ROLLBACK");
                                        $db->query("END TRANSACTION");
                                        $db->disconnect();
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                    } else {
                                      $LinhaSubElmento = $resSubElemento->fetchRow();
                                      $CGRUSEELE1 = $LinhaSubElmento[0];
                                      $CGRUSEELE2 = $LinhaSubElmento[1];
                                      $CGRUSEELE3 = $LinhaSubElmento[2];
                                      $CGRUSEELE4 = $LinhaSubElmento[3];
                                      $CGRUSESUBE = $LinhaSubElmento[4];

                                      if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                                        $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                                        $ValorSubElemento = $Quantidade * $Valor;

                                        if(!in_array($Subelemento, $SubElementosDespesa)){
                                          $indice = count($SubElementosDespesa);
                                          $SubElementosDespesa[$indice] = $Subelemento;
                                          $ValoresSubelementos[$indice] = $ValorSubElemento;
                                        } else {
                                          $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                          $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                                        }
                                      } else {
                                        # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                                        $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                                        $Url = "estoques/CadInventarioInicialFechamento.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                                        if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                        RedirecionaPost($Url);
                                        exit;
                                      }
                                    }

                                    /*
                                                                                 PARA LANÇAMENTO CONTÁBIL
                                                                                    - ACUMULAR CONFORME O TIPO DO MATERIAL (CONSUMO OU PERMANENTE) E CASO O RESULTADO SEJA POSITIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "ENTRADA POR GERAÇÃO DE INVENTÁRIO (33)"
                                                                                    - ACUMULAR CONFORME O TIPO DO MATERIAL (CONSUMO OU PERMANENTE) E CASO O RESULTADO SEJA NEGATIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "SAÍDA POR GERAÇÃO DE INVENTÁRIO (34)"
                                                                                */

                                    #Preparando os parâmetros para o lançamento contábil
                                    $ValorContabilTESTE = $Quantidade * $Valor;

                                    if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                                      $indice = count($EspecificacoesContabeis);
                                      $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                                      $ValoresContabeis[$indice] = $ValorContabilTESTE;
                                    } else {
                                      $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                                      $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                                    }
                                    //FIM NOVO TESTE 2

																		# Insere na tabela de Movimentação de Material - Tipo 1 Saldo Inicial de Estoque #
																		$sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																		$sql .= "            CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
																		$sql .= "            CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, ";
																		$sql .= "            CGREMPCODI, CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, ";
																		$sql .= "            NMOVMARESP ";
																		$sql .= ") VALUES ( ";
																		$sql .= "            $Almoxarifado, ".date("Y").", $Movimento, '".date('Y-m-d')."', 1, ";
																		$sql .= "            NULL, $Material, $Quantidade, $Valor, $Valor, ";
																		$sql .= "            ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', $TipoMovimento, NULL, ";
																		$sql .= "            NULL )";
																		$resmov  = $db->query($sql);
																		if( PEAR::isError($resmov) ){
																				$db->query('ROLLBACK');
																				$db->query('END TRANSACTION');
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				$db->disconnect();
																				exit;
																		} else {
																				# Inserindo Itens em Armazenamento Material #
																				$sql  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
																				$sql .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
																				$sql .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
																				$sql .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
																				$sql .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
																				$sql .= ") VALUES ( ";
																				$sql .= "$Material, $Localizacao, $Quantidade, NULL, ";
																				$sql .= "NULL, $Quantidade, '0', NULL, ";
																				$sql .= "NULL, NULL, $Valor, $Valor, ";
																				$sql .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."' )";
																				$resarm  = $db->query($sql);
																				if( PEAR::isError($resarm) ){
																						$db->query('ROLLBACK');
																						$db->query('END TRANSACTION');
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						$db->disconnect();
																						exit;
																				}
																		}
																		$TipoMovimento++;
																		$Movimento++;
																}
														}
												}


												//TESTE

												# Executar lancamentos contábeis
								                # Nota: Apenas se houver algum material com diferenças entre a quantidade do almoxarifado e do inventário
								                if($SubElementosDespesa != null and count($SubElementosDespesa) != 0 and $EspecificacoesContabeis != null and count($EspecificacoesContabeis) != 0 ){

												//FIM TESTE


													$CC = 799;
													$Det = 77;
													$sqlOUR  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
													$sqlOUR .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
													$sqlOUR .= " WHERE A.CORGLICODI = B.CORGLICODI ";
													$sqlOUR .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
													$sqlOUR .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
													$sqlOUR .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
													$resOUR  = $db->query($sqlOUR);
													if( PEAR::isError($resOUR) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlOUR");
															$db->query('ROLLBACK');
															$db->query('END TRANSACTION');
															$db->disconnect();
															exit;
													}else{
															$rows = $resOUR->numRows();
															if($rows > 0){
																	$LinhaOUR     = $resOUR->fetchRow();
																	$Orgao        = $LinhaOUR[0];
																	$Unidade      = $LinhaOUR[1];
																	$RPA          = $LinhaOUR[2];
																	$CentroCusto  = $CC;
																	$Detalhamento = $Det;
															}else{
																	$Mens      = 1;
																	$Tipo      = 2;
																	$Mensagem = "Falta Cadastrar o Centro de Custo $CC/$Det, Contatar o Responsável pelo Cadastramento de Centros de Custo";
																	ExibeErroBD("$Mensagem\n\n$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlOUR");
																	$db->query('ROLLBACK');
																	$db->query('END TRANSACTION');
																	$db->disconnect();
																	exit;
															}
													}

							                        $Movimentacao = 1; // Saldo Inicial Estoque
							                        $ProgramaDestino = "CadInventarioInicialFechamento.php";
							                        $TimeStamp            = date("Y-m-d H:i:s");

							                        //INICIO TESTE
							                        # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
							                        $dbora = ConexaoOracle();

							                        # Evita que Rollback não funcione #
							                        $dbora->autoCommit(false);
							                        # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
							                        $dbora->query("BEGIN TRANSACTION");

							                        $ConfirmarInclusao = true;

							                        //ORIGINAL
							                        // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
							                          // $Movimentacao, $TipoMaterialTESTE,
							                          // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
							                          // $Matricula, $Responsavel,
							                          // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
							                          // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
							                          // $SubElementosDespesa, $ValoresSubelementos,
							                          // $EspecificacoesContabeis, $ValoresContabeis);
							                        //ORIGINAL

							                        //TESTE 3
							                        GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
							                          $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
							                          $EspecificacoesContabeis, $ValoresContabeis,
							                          $SubElementosDespesa, $ValoresSubelementos,
							                          $Matricula, $Responsavel,
							                          $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
							                          $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
							                        //FIM TESTE 3

							                        exit;
										} //FIM DO if($SubElementosDespesa != null and count($SubElementosDespesa) != 0 and $EspecificacoesContabeis != null and count($EspecificacoesContabeis) != 0 ){
										 else {
										    $db->query("COMMIT");
										    $db->query("END TRANSACTION");
										    $db->disconnect();
											$Mens     = 1;
											$Tipo     = 1;
											$Mensagem = "Inventário Periódico Concluído com Sucesso";
								        }
									}
								}
						}
				}
				$db->disconnect();
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
	document.CadInventarioInicialFechamento.Botao.value = valor;
	document.CadInventarioInicialFechamento.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioInicialFechamento.Almoxarifado.value ){
			document.CadInventarioInicialFechamento.submit();
	}else	if( ! document.CadInventarioInicialFechamento.Localizacao.value ){
			document.CadInventarioInicialFechamento.submit();
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
<form action="CadInventarioInicialFechamento.php" method="post" name="CadInventarioInicialFechamento">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Inicial > Fechamento
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
									INVENTÁRIO - FECHAMENTO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para efetuar o Fechamento do Inventário, selecione o Almoxarifado e clique no botão "Fechar".
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
												$db  = Conexao();
												if(($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= "  LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL C ";
														$sql .= "    ON A.CALMPOCODI = C.CALMPOCODI ";
              											$sql .= " WHERE A.FALMPOSITU = 'A' ";
														$sql .= "   AND A.FALMPOINVE = 'S' ";
														$sql .= " GROUP BY A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "HAVING COUNT(C.*)=0 ";
												} else {
														$sql = "SELECT A.CALMPOCODI, A.EALMPODESC, COUNT(C.*)
																		  FROM SFPC.TBALMOXARIFADOPORTAL A
																		  LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL C
																		    ON A.CALMPOCODI = C.CALMPOCODI,
																		       SFPC.TBALMOXARIFADOORGAO B
																		 WHERE A.CALMPOCODI = B.CALMPOCODI
																		   AND A.FALMPOSITU = 'A'
																		   AND A.FALMPOINVE = 'S'
																		   AND B.CORGLICODI IN
																		       ( SELECT DISTINCT CEN.CORGLICODI
																		           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU
																		          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R')
																		            AND USU.CUSUPOCODI =  ".$_SESSION['_cusupocodi_']."
																		            AND CEN.FCENPOSITU <> 'I'

																		            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END

																		       )
																		 GROUP BY A.CALMPOCODI, A.EALMPODESC
																		HAVING COUNT(C.*)=0";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														echo "<select name=\"Almoxarifado\" class=\"textonormal\" onchange=\"Localizacao[0].selected=true;submit()\">\n";
														if( $Rows == 0 ){
																echo "	<option value=\"\">Nenhum Almoxarifado Disponível</option>\n";
														}else{
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

														}
														echo "</select>\n";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												echo "<select name=\"Localizacao\" class=\"textonormal\">\n";
												if (!$Almoxarifado){
														echo "	<option value=\"\">---</option>\n";
												} else {
														echo "	<option value=\"\">Selecione uma Localização...</option>\n";
														$db = Conexao();
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql  = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
														$sql .= "   AND A.FLOCMASITU = 'A'";
														$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "	<option value=\"\">NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO</option>\n";
																}else{
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
																		$CarregaLocalizacao = "";
																}
														}
														$db->disconnect();
												 }
												echo "</select>\n";
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Data Base</td>
											<?php
											$DataBase = date("d/m/Y");
											?>
											<td class="textonormal"><input type="text" name="DataBase" value="<?php $DataBase?>" maxlength="10" size="10" class="textonormal" disabled>
											<input type="hidden" name="DataBase" value="<?php $DataBase?>">
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Responsável*</td>
											<td class="textonormal"><input type="text" name="Responsavel" value="<?php $Responsavel?>" maxlength="70" size="40" class="textonormal">
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Matrícula*</td>
											<td class="textonormal"><input type="text" name="Matricula" value="<?php $Matricula?>" maxlength="10" size="10" class="textonormal">
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="submit" name="Botao" value="Fechar" class="botao">
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
