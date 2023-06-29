<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioPeriodicoFechamento.php
# Objetivo: Programa de Geração(Inclusão/Alteração) de Inventário do Estoque
#-----------------
# Autor:    Carlos Abreu
# Data:     16/11/2006
# Alterado: Carlos Abreu
# Data:     15/02/2007 - Acrescentado funcionalidade para novos especificações do custo (Fardamento,Limpeza e Didático)
# Alterado: Carlos Abreu
# Data:     15/05/2007 - Ajuste para registrar movimentacoes com DataBase e nao com a Data de Fechamento
# Alterado: Carlos Abreu
# Data:     25/05/2007 - Alteração nas Movimentações 33 e 34 para lançamento em custo e contabil com valores das diferencas
#                        entre o armazenado e o inventariado e nao com o saldo anterior de custo
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Álvaro Faria / Rossana Lira
# Data:     20/12/2007 - Adição de filtro no select para só fechar o inventário que estiver aberto, pois antes atualizava todos
# Alterado: Rodrigo Melo
# Data:     26/12/2007 - Correção de erro de SQL no filtro do select feito fechar o inventário que estiver aberto.
# Alterado: Rodrigo Melo
# Data:    02/05/2007 - Ajuste nas movimentações para chamar a rotina de lançamento contábil.
# Alterado: Ariston Cordeiro
# Data:     09/07/2008	- Valor informado em consolidação agora refere-se à quantidade real do estoque (AARMATQTDE), ao invés da quantidade total (AARMATESTR)
# Alterado: Ariston Cordeiro
# Data:     23/10/2008	- Permitir que itens de estoques zerados não precisem de contagem/recontagem
# Alterado: Rodrigo Melo
# Data:     11/08/2009	- Permitir que almoxarifados que não realizaram movimentações no ano corrente possam realizar o fechamento do inventário periódico.
#----------------
# Alterado: Rossana Lira - retirada temporária condição
# Data:     11/08/2021	
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
if($_SERVER['REQUEST_METHOD'] == "POST"){
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
	////($Localizacao);
		# Resgata a flag do fechamento #
		$db   = Conexao();
		$sql  = "SELECT FINVCOFECH ";
		$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM ";
		$sql .= " WHERE CLOCMACODI = $Localizacao ";
		$sql .= "   AND AINVCOANOB = $AnoExercicio ";
		$sql .= "   AND AINVCOSEQU = (SELECT MAX(AINVCOSEQU) FROM SFPC.TBINVENTARIOCONTAGEM ";
		$sql .= "                     WHERE CLOCMACODI = $Localizacao AND AINVCOANOB = $AnoExercicio ) ";
		////($sql);
		//exit;
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				$db->disconnect();
				exit;
		}else{
				$Linha         = $res->fetchRow();
				$FlgFechamento = $Linha[0];
				if( $FlgFechamento == "S" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 1;
						$Mensagem .= "Inventário Fechado. A geração não pode ser efetuada"; //TESTE
				}
		}
		$db->disconnect();
}

if( $Botao == "Fechar" && $Mens == 0){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if ($Almoxarifado == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoFechamento.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if ($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoFechamento.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$MensErro = ValidaData($DataBase);
		if( $MensErro != "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoFechamento.DataBase.focus();\" class=\"titulo2\">Data Base Válida</a>";
		}
		if ($Responsavel == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoFechamento.Responsavel.focus();\" class=\"titulo2\">Responsável</a>";
		}
		if (!SoNumeros($Matricula)) {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoFechamento.Matricula.focus();\" class=\"titulo2\">Matrícula</a>";
		}
		if ($Localizacao!=""){
			////($Localizacao);
				$db = Conexao();
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
				$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
				$sql .= "   AND A.FINVCOFECH IS NULL ";
				$sql .= "   AND A.AINVCOANOB=( ";
				$sql .= "       SELECT MAX(AINVCOANOB) ";
				$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
				$sql .= "        WHERE CLOCMACODI=$Localizacao ";
				$sql .= "       ) ";
				$sql .= " GROUP BY A.AINVCOANOB";
				//var_dump($sql);die;
				$res  = $db->query($sql);
				////($res);
				////($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Rows = $res->numRows();
						////($Rows);
						
						if( $Rows != 0 ){
								$Linha = $res->fetchRow();
						}
						$Ano        = $Linha[0];
						if (!$Ano){$Ano=date("Y");}
						$Sequencial = $Linha[1];
						//$Sequencial = 16;
						////($Sequencial);//die;
				}
				
				$sql  = "
					SELECT SUM(CONTA)
						FROM (
							SELECT CASE WHEN (
              	((DADOS.ALMQTD <> 0 OR DADOS.INVQTD <> 0) AND (DADOS.INVVAL = 0 OR DADOS.INVVAL IS NULL)) OR
								((DADOS.ALMQTD <> DADOS.INVQTD) AND (INVENTARIO.EINVMAJUST = '' OR INVENTARIO.EINVMAJUST IS NULL)) OR
								( (SUM(EXISTE1.AINVREQTDE) IS NULL) and (DADOS.ALMQTD <> 0 OR DADOS.INVQTD <> 0) ) OR
								( (SUM(EXISTE2.AINVREQTDE) IS NULL) and (DADOS.ALMQTD <> 0 OR DADOS.INVQTD <> 0) ) OR
								(EXISTE3.AINVMAESTO IS NULL)
							) THEN 1 ELSE 0 END AS CONTA
							FROM (
								SELECT TABELA.CLOCMACODI, TABELA.CMATEPSEQU,
									SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL, SUM(TABELA.QTD2) AS INVQTD, SUM(TABELA.VAL2) AS INVVAL
										FROM (
											select * from (
												SELECT CLOCMACODI, CMATEPSEQU, AARMATQTDE AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2
												FROM SFPC.TBARMAZENAMENTOMATERIAL
												WHERE CLOCMACODI = $Localizacao
												UNION ALL
												SELECT CLOCMACODI, CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT
												FROM SFPC.TBINVENTARIOMATERIAL
												WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial)
											) as materiais where
													-- verificar se material é maior que zero no esqtoque
													cmatepsequ in (
														SELECT cmatepsequ
														FROM SFPC.TBARMAZENAMENTOMATERIAL
														WHERE
															CLOCMACODI = $Localizacao and AARMATQTDE > 0
													) or
													-- verificar se material foi movimentado desde ultimo inventario
													cmatepsequ in (
														SELECT distinct
															cmatepsequ
														FROM SFPC.TBmovimentacaomaterial
														WHERE
															calmpocodi = $Almoxarifado
															and tmovmaulat  >
															(
																-- data do ultimo inventário
																SELECT max(tinvcoulat) as ultimo_periodico
																FROM sfpc.tbinventariocontagem
																WHERE clocmacodi = $Localizacao and finvcofech = 'S'
															)
													) or
													materiais.qtd1 > 0 or --caso não tenha sido movimentado, verificar se material não é zero no almoxarifado
													materiais.qtd2 > 0 --caso seja zerado não movimentado no almoxarifado, verificar se material não é zero no inventario
										) AS TABELA
										GROUP BY TABELA.CLOCMACODI, TABELA.CMATEPSEQU
							) AS DADOS
								LEFT OUTER JOIN SFPC.TBARMAZENAMENTOMATERIAL AS ARMAZENAMENTO
									ON DADOS.CLOCMACODI = ARMAZENAMENTO.CLOCMACODI
									AND DADOS.CMATEPSEQU = ARMAZENAMENTO.CMATEPSEQU
								LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS INVENTARIO
									ON DADOS.CLOCMACODI = INVENTARIO.CLOCMACODI
									AND DADOS.CMATEPSEQU = INVENTARIO.CMATEPSEQU
									AND INVENTARIO.AINVCOANOB = $Ano
									AND INVENTARIO.AINVCOSEQU = $Sequencial
								LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE1
									ON DADOS.CLOCMACODI = DADOS.CLOCMACODI AND EXISTE1.AINVCOANOB = $Ano AND EXISTE1.AINVCOSEQU = $Sequencial
									AND EXISTE1.CMATEPSEQU = DADOS.CMATEPSEQU AND EXISTE1.FINVREETAP=1
								LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE2
									ON EXISTE2.CLOCMACODI = DADOS.CLOCMACODI AND EXISTE2.AINVCOANOB = $Ano AND EXISTE2.AINVCOSEQU = $Sequencial
									AND EXISTE2.CMATEPSEQU = DADOS.CMATEPSEQU AND EXISTE2.FINVREETAP=2
								LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS EXISTE3
									ON EXISTE3.CLOCMACODI = DADOS.CLOCMACODI AND EXISTE3.AINVCOANOB = $Ano AND EXISTE3.AINVCOSEQU = $Sequencial
									AND EXISTE3.CMATEPSEQU = DADOS.CMATEPSEQU
								GROUP BY DADOS.ALMVAL, DADOS.ALMQTD,DADOS.INVVAL, DADOS.INVQTD, INVENTARIO.EINVMAJUST, EXISTE3.AINVMAESTO
						) AS ERRO
					
				";
				//var_dump($sql);die;
				$res  = $db->query($sql);
				
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Linha = $res->fetchRow();
						
						$Linha = 0;
						if ($Linha[0]>0){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 0;
			 				$Mensagem = "<font class=\"titulo2\">Não foi possível realizar o fechamento deste Inventário, devido a pendências na rotina de Diferenças/Acertos. Acesse pelo Menu Principal > Estoques > Inventário > Periódico > Diferenças/Acertos para depois retornar a rotina de fechamento</font>";
						}
				}
				$db->disconnect();
		}
		
		if( $Mens == 0 ){
				$db = Conexao();
				$datahora = date("Y-m-d H:i:s");

				$sql  = "SELECT TO_CHAR(TINVCOBASE,'YYYY-MM-DD'), TINVCOBASE ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM ";
				$sql .= " WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial) ";
				$res  = $db->query($sql);
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				} else {
						$Linha  = $res->fetchRow();
						$DataBaseRes = $Linha[0];
						$DataBaseExt = $Linha[1];
				}

				$sql  = "
					SELECT DADOS.CMATEPSEQU, DADOS.ALMQTD, DADOS.ALMVAL, DADOS.INVQTD, DADOS.INVVAL,
						CASE WHEN (ARMAZENAMENTO.CLOCMACODI IS NULL ) THEN 'N' ELSE 'S' END, GRUPO.FGRUMSTIPC, ARMAZENAMENTO.AARMATQTDE, GRUPO.FGRUMSTIPM
							FROM (
								SELECT TABELA.CLOCMACODI, TABELA.CMATEPSEQU, SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL,
									SUM(TABELA.QTD2) AS INVQTD, SUM(TABELA.VAL2) AS INVVAL
										FROM (
											select * from (
												SELECT CLOCMACODI, CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2
												FROM SFPC.TBARMAZENAMENTOMATERIAL
												WHERE CLOCMACODI = $Localizacao AND (AARMATQTDE<>0 OR VARMATUMED<>0)
												UNION ALL
												SELECT CLOCMACODI, CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT
												FROM SFPC.TBINVENTARIOMATERIAL
												WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial) AND (AINVMAESTO<>0 OR VINVMAUNIT<>0)
											) as materiais where
													-- verificar se material é maior que zero no esqtoque
													cmatepsequ in (
														SELECT cmatepsequ
														FROM SFPC.TBARMAZENAMENTOMATERIAL
														WHERE
															CLOCMACODI = $Localizacao and AARMATQTDE > 0
													) or
													-- verificar se material foi movimentado desde ultimo inventario
													cmatepsequ in (
														SELECT distinct
															cmatepsequ
														FROM SFPC.TBmovimentacaomaterial
														WHERE
															calmpocodi = $Almoxarifado
															and tmovmaulat  >
															(
																-- data do ultimo inventário
																SELECT max(tinvcoulat) as ultimo_periodico
																FROM sfpc.tbinventariocontagem
																WHERE clocmacodi = $Localizacao and finvcofech = 'S'
															)
													) or
													materiais.qtd1 > 0 or --caso não tenha sido movimentado, verificar se material não é zero no almoxarifado
													materiais.qtd2 > 0 --caso seja zerado não movimentado no almoxarifado, verificar se material não é zero no inventario
										) AS TABELA
										GROUP BY TABELA.CLOCMACODI, TABELA.CMATEPSEQU
							) AS DADOS
								LEFT OUTER JOIN SFPC.TBARMAZENAMENTOMATERIAL AS ARMAZENAMENTO
									ON DADOS.CLOCMACODI = ARMAZENAMENTO.CLOCMACODI AND DADOS.CMATEPSEQU = ARMAZENAMENTO.CMATEPSEQU,
										SFPC.TBMATERIALPORTAL MATERIAL, SFPC.TBSUBCLASSEMATERIAL SUBCLASSE, SFPC.TBGRUPOMATERIALSERVICO GRUPO
							WHERE DADOS.CMATEPSEQU = MATERIAL.CMATEPSEQU
								AND MATERIAL.CSUBCLSEQU = SUBCLASSE.CSUBCLSEQU
								AND SUBCLASSE.CGRUMSCODI = GRUPO.CGRUMSCODI
				";
        $res  = $db->query($sql);
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Rows = $res->numRows();
						if( $Rows > 0 ){

								# Pega o Máximo valor da Movimentação #
								$sqlmov  = "SELECT MAX(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL";
								$sqlmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")."";
								//$sqlmov .= "	 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
								$resmov  = $db->query($sqlmov);
								if(db::isError($resmov)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
								}else{
										$LinhaMov  = $resmov->fetchRow();
										$Movimento = $LinhaMov[0];
								}
								//rossana
					
								# Pega o Máximo valor do Movimento do Material do Tipo - ENTRADA POR ACERTO DE INVENTÁRIO #
								$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
								$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
								$sqltipo .= "   AND CTIPMVCODI = 33 ";
								//$sqltipo .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
								$restipo  = $db->query($sqltipo);
								if( db::isError($restipo) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
								}else{
										$LinhaTipo     = $restipo->fetchRow();
										$TipoMovimentoEntrada = $LinhaTipo[0];
								}
								//rossana
								# Pega o Máximo valor do Movimento do Material do Tipo - SAÍDA POR ACERTO DE INVENTÁRIO #
								$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
								$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
								$sqltipo .= "   AND CTIPMVCODI = 34 ";
								//$sqltipo .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )";
								$restipo  = $db->query($sqltipo);
								if( db::isError($restipo) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
								}else{
										$LinhaTipo     = $restipo->fetchRow();
										$TipoMovimentoSaida = $LinhaTipo[0];
								}

								//$db->query('BEGIN'); //ORIGINAL

                $db->query('BEGIN TRANSACTION'); //TESTE

								$CustoPermanente = 0;
								$CustoConsumo = 0;
								$CustoDidatico = 0;
								$CustoLimpeza = 0;
								$CustoFardamento = 0;
                list($AnoBaixa,$MesBaixa,$DiaBaixa) = explode("-",$DataBaseRes);

								#Preparando os parâmetros para o lançamento custo/contábil
                $SubElementosDespesa = array(); //Array que contém os sub-elementos de despesa
                $ValoresSubelementos = array(); //Valores dos sub-elementos de despesa
                $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                for( $i=0;$i<$Rows; $i++ ){
                  $Linha = $res->fetchRow();

                  # Realiza alterações em SFPC.TBarmazenamentomaterial
                  if (is_null($Linha[7])) $Linha[7]='NULL';
                  $sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
                  $sql .= "   SET AINVMAQTEA = $Linha[7], ";
                  $sql .= "       TINVMAULAT = '$datahora', ";
                  $sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
                  $sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
                  $sql .= " WHERE CLOCMACODI = $Localizacao ";
                  $sql .= "   AND AINVCOANOB = $Ano ";
                  $sql .= "   AND AINVCOSEQU = $Sequencial ";
                  $sql .= "   AND CMATEPSEQU = $Linha[0]";

                  $resmaterial  = $db->query($sql);
                  if( db::isError($resmaterial) ){
                      $db->query('ROLLBACK');
                      $db->query('END TRANSACTION');
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                      $db->disconnect();
                      exit;
                  }
				  // Rossana
				  //echo "sql 3 ";
				 // echo "<BR><BR> ";
                  if ($Linha[5]=="N"){
                      $sql  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                      $sql .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
                      $sql .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
                      $sql .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
                      $sql .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                      $sql .= ") VALUES ( ";
                      $sql .= "$Linha[0], $Localizacao, $Linha[3], NULL, ";
                      $sql .= "NULL, $Linha[3], '0', NULL, ";
                      $sql .= "NULL, NULL, $Linha[4], $Linha[4], ";
                      $sql .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$datahora' )";
                      $resarmazenamento  = $db->query($sql);
                      if( db::isError($resarmazenamento) ){
                          $db->query('ROLLBACK');
                          $db->query('END TRANSACTION');
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $db->disconnect();
                          exit;
                      }

					  
					 $Linha[2] = 0;
                  } elseif ($Linha[1]!=$Linha[3] or $Linha[2]!=$Linha[4]) {
                      if ($Linha[2] == 0){

                          $sql  = "
						  
														UPDATE SFPC.TBARMAZENAMENTOMATERIAL
															SET
																AARMATQTDE = $Linha[3] + COALESCE(AARMATVIRT,0),
																AARMATESTR = $Linha[3],
																VARMATUMED = $Linha[4],
																VARMATULTC = $Linha[4],
																CGREMPCODI = ".$_SESSION['_cgrempcodi_'].",
																CUSUPOCODI = ".$_SESSION['_cusupocodi_'].",
																TARMATULAT = '$datahora'
															WHERE
																CMATEPSEQU = $Linha[0] AND
																CLOCMACODI = $Localizacao
													";
                      }else{
					
                          $sql  = "

														UPDATE SFPC.TBARMAZENAMENTOMATERIAL
															SET
																AARMATQTDE = $Linha[3] + COALESCE(AARMATVIRT,0),
																AARMATESTR = $Linha[3],
																VARMATUMED = $Linha[4],
																VARMATULTC = $Linha[2],
																CGREMPCODI = ".$_SESSION['_cgrempcodi_'].",
																CUSUPOCODI = ".$_SESSION['_cusupocodi_'].",
																TARMATULAT = '$datahora'
															WHERE
																CMATEPSEQU = $Linha[0] AND
																CLOCMACODI = $Localizacao
													";
                      }
                      $resarmazenamento  = $db->query($sql);
                      if( db::isError($resarmazenamento) ){
                          $db->query('ROLLBACK');
                          $db->query('END TRANSACTION');
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $db->disconnect();
                          exit;
                      }
                  }

                  # Registra as Movimentações
                  $QtdDiferenca = $Linha[3] - $Linha[1]; //QTDE DO INVENTÁRIO - QTDE NO ALMOXARIFADO

                  if ($Linha[1]>0 and $Linha[2]==0){
                      $sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
                      $sql .= "       CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
                      $sql .= "       CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, ";
                      $sql .= "       CGREMPCODI, CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, ";
                      $sql .= "       NMOVMARESP )";
                      $sql .= "VALUES ";
                      $sql .= "       ($Almoxarifado, ".date("Y").", ".++$Movimento.", '".$DataBaseRes."', 34, ";
                      $sql .= "       NULL, $Linha[0], $Linha[1], 0, 0, ";
                      $sql .= "       ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataBaseExt."', ".++$TipoMovimentoSaida.", NULL, ";
                      $sql .= "       NULL )";
                      $resmovimentacao  = $db->query($sql);
                      if( db::isError($resmovimentacao) ){
                          $db->query('ROLLBACK');
                          $db->query('END TRANSACTION');
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $db->disconnect();
                          exit;
                      }
                      # Entrada por acerto de inventário atribuindo valor (29)
                      $sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
                      $sql .= "       CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
                      $sql .= "       CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, ";
                      $sql .= "       CGREMPCODI, CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, ";
                      $sql .= "       NMOVMARESP )";
                      $sql .= "VALUES ";
                      $sql .= "       ($Almoxarifado, ".date("Y").", ".++$Movimento.", '".$DataBaseRes."', 33, ";
                      $sql .= "       NULL, $Linha[0], $Linha[1], $Linha[4], $Linha[4], ";
                      $sql .= "       ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataBaseExt."', ".++$TipoMovimentoEntrada.", NULL, ";
                      $sql .= "       NULL )";
                      $resmovimentacao  = $db->query($sql);
                      if( db::isError($resmovimentacao) ){
                          $db->query('ROLLBACK');
                          $db->query('END TRANSACTION');
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $db->disconnect();
                          exit;
                      }
                  }
                  if ($QtdDiferenca>0){
                      # Entrada por acerto de inventario
                      $sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
                      $sql .= "       CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
                      $sql .= "       CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, ";
                      $sql .= "       CGREMPCODI, CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, ";
                      $sql .= "       NMOVMARESP )";
                      $sql .= "VALUES ";
                      $sql .= "       ($Almoxarifado, ".date("Y").", ".++$Movimento.", '".$DataBaseRes."', 33, ";
                      $sql .= "       NULL, $Linha[0], $QtdDiferenca, $Linha[4], $Linha[4], ";
                      $sql .= "       ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataBaseExt."', ".++$TipoMovimentoEntrada.", NULL, ";
                      $sql .= "       NULL )";
                      $resmovimentacao  = $db->query($sql);
                      if( db::isError($resmovimentacao) ){
                          $db->query('ROLLBACK');
                          $db->query('END TRANSACTION');
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $db->disconnect();
                          exit;
                      }
                  } elseif($QtdDiferenca<0){
                    # Saída por acerto de inventario
                    $sql  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
                    $sql .= "       CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
                    $sql .= "       CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, ";
                    $sql .= "       CGREMPCODI, CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, ";
                    $sql .= "       NMOVMARESP )";
                    $sql .= "VALUES ";
                    $sql .= "       ($Almoxarifado, ".date("Y").", ".++$Movimento.", '".$DataBaseRes."', 34, ";
                    $sql .= "       NULL, $Linha[0], ".abs($QtdDiferenca).", $Linha[4], $Linha[4], ";
                    $sql .= "       ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".$DataBaseExt."', ".++$TipoMovimentoSaida.", NULL, ";
                    $sql .= "       NULL )";
                    $resmovimentacao  = $db->query($sql);
                    if( db::isError($resmovimentacao) ){
                        $db->query('ROLLBACK');
                        $db->query('END TRANSACTION');
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        $db->disconnect();
                        exit;
                    }
                  }


									if($QtdDiferenca!=0){
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
										$sql .= "       MAT.CMATEPSEQU = $Linha[0] ";

										$resSubElemento  = $db->query($sql);

										if( db::isError($resSubElemento) ){
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
												$ValorSubElemento = $QtdDiferenca*$Linha[4];

												# Adicionar materiais no array para gerar custo contábil.
												# note que apenas os diferentes de 0 vão ser contados


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
												$Mensagem = urlencode("O grupo do Material (Cod. Red: $Linha[0]) não possui integração com Sub-elemento(s)");
												$Url = "estoques/CadInventarioPeriodicoFechamento.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
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
										$ValorContabilTESTE = $QtdDiferenca*$Linha[4];
										$TipoMaterialTESTE = $Linha[8];

										if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
											$indice = count($EspecificacoesContabeis);
											$EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
											$ValoresContabeis[$indice] = $ValorContabilTESTE;
										} else {
											$indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
											$ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
										}
								  }
								}

								$sql  = "UPDATE SFPC.TBINVENTARIOCONTAGEM ";
								$sql .= "   SET FINVCOFECH = 'S', ";
								$sql .= "       TINVCOFECH = '$datahora', ";
								$sql .= "       TINVCOULAT = '$datahora', ";
								$sql .= "       CGREMPCOD1 = ".$_SESSION['_cgrempcodi_'].", ";
								$sql .= "       CUSUPOCOD1 = ".$_SESSION['_cusupocodi_'];
								$sql .= " WHERE CLOCMACODI = $Localizacao ";
								$sql .= "   AND (FINVCOFECH = 'N' OR FINVCOFECH is null) ";
								$rescontagem  = $db->query($sql);
								if( db::isError($rescontagem) ){
										$db->query('ROLLBACK');
                    $db->query('END TRANSACTION');
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										$db->disconnect();
										exit;
								}

								$sql  = "UPDATE SFPC.TBALMOXARIFADOPORTAL ";
								$sql .= "   SET FALMPOINVE = 'N', ";
								$sql .= "       TALMPOULAT = '$datahora' ";
								$sql .= " WHERE CALMPOCODI = $Almoxarifado";
								$resalmoxarifado  = $db->query($sql);
								if( db::isError($resalmoxarifado) ){
										$db->query('ROLLBACK');
                    $db->query('END TRANSACTION');
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										$db->disconnect();
										exit;
								}

								/*
								# A trecho abaixo foi utilizado em 2006 para igualar os saldos de custo com o de estoque #
								$dbora  = ConexaoOracle();
								$sql  = "SELECT (SUM(PERMANENTE_ENTRADA)-SUM(PERMANENTE_SAIDA)) AS PERMANENTE,";
								$sql .= "       (SUM(CONSUMO_ENTRADA)-SUM(CONSUMO_SAIDA)) AS CONSUMO, ";
								$sql .= "       (SUM(DIDATICO_ENTRADA)-SUM(DIDATICO_SAIDA)) AS DIDATICO, ";
								$sql .= "       (SUM(FARDAMENTO_ENTRADA)-SUM(FARDAMENTO_SAIDA)) AS FARDAMENTO,";
								$sql .= "       (SUM(LIMPEZA_ENTRADA)-SUM(LIMPEZA_SAIDA)) AS LIMPEZA";
								$sql .= "  FROM ( ";
								$sql .= "       SELECT SUM(VMOVCUREQU) AS PERMANENTE_ENTRADA, 0 AS PERMANENTE_SAIDA,";
								$sql .= "              0 AS CONSUMO_ENTRADA, 0 AS CONSUMO_SAIDA,";
								$sql .= "              0 AS DIDATICO_ENTRADA, 0 AS DIDATICO_SAIDA, ";
								$sql .= "              0 AS FARDAMENTO_ENTRADA, 0 AS FARDAMENTO_SAIDA,";
								$sql .= "              0 AS LIMPEZA_ENTRADA, 0 AS LIMPEZA_SAIDA";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'E' AND CESPCPCODI = 27";
								$sql .= "        UNION ALL ";
								$sql .= "       SELECT 0, SUM(VMOVCUREQU), 0, 0, 0, 0, 0, 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'S' AND CESPCPCODI = 27";
								$sql .= "        UNION ALL";
								$sql .= "       SELECT 0, 0, SUM(VMOVCUREQU), 0, 0, 0, 0, 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'E' AND CESPCPCODI = 3";
								$sql .= "        UNION ALL ";
								$sql .= "       SELECT 0, 0, 0, SUM(VMOVCUREQU), 0, 0, 0, 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'S' AND CESPCPCODI = 3";
								$sql .= "        UNION ALL";
								$sql .= "       SELECT 0, 0, 0, 0, SUM(VMOVCUREQU), 0, 0, 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'E' AND CESPCPCODI = 6";
								$sql .= "        UNION ALL ";
								$sql .= "       SELECT 0, 0, 0, 0, 0, SUM(VMOVCUREQU), 0, 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'S' AND CESPCPCODI = 6";
								$sql .= "        UNION ALL";
								$sql .= "       SELECT 0, 0, 0, 0, 0, 0,SUM(VMOVCUREQU), 0, 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'E' AND CESPCPCODI = 30";
								$sql .= "        UNION ALL ";
								$sql .= "       SELECT 0, 0, 0, 0, 0, 0, 0, SUM(VMOVCUREQU), 0, 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'S' AND CESPCPCODI = 30";
								$sql .= "        UNION ALL";
								$sql .= "       SELECT 0, 0, 0, 0, 0, 0, 0, 0, SUM(VMOVCUREQU), 0";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'E' AND CESPCPCODI = 37";
								$sql .= "        UNION ALL ";
								$sql .= "       SELECT 0, 0, 0, 0, 0, 0, 0, 0, 0,SUM(VMOVCUREQU)";
								$sql .= "         FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
								$sql .= "        WHERE CMOVCUALMO = $Almoxarifado AND FMOVCULANC = 'S' AND CESPCPCODI = 37";
								$sql .= "       )";
								$rescusto  = $dbora->query($sql);
								if(db::isError($rescusto)){
										$db->query('ROLLBACK');
										$db->query('END');
										$db->disconnect();
										$dbora->disconnect();
										ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										exit;
								}else{
										$Linha = $rescusto->fetchRow();
								}
								$dbora->disconnect();
								*/

								# Executar lancamentos contábeis
								# Nota: Apenas se houver algum material com diferenças entre a quantidade do almoxarifado e do inventário
								if(count($SubElementosDespesa) != 0 and count($EspecificacoesContabeis) != 0 ){

									$CC = 799;
									$Det = 77;
									$sqlOUR  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
									$sqlOUR .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
									$sqlOUR .= " WHERE A.CORGLICODI = B.CORGLICODI ";
									$sqlOUR .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
									$sqlOUR .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
									$sqlOUR .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
									$resOUR  = $db->query($sqlOUR);
									if( db::isError($resOUR) ){
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
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlOUR");
												$db->query('ROLLBACK');
												$db->query('END TRANSACTION');
												$db->disconnect();
												exit;
										}
									}

									/*
																					 PARA LANÇAMENTO CONTÁBIL
																						- ACUMULAR CONFORME O TIPO DO MATERIAL (CONSUMO OU PERMANENTE) E CASO O RESULTADO SEJA POSITIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "ENTRADA POR GERAÇÃO DE INVENTÁRIO (33)"
																					 - ACUMULAR CONFORME O TIPO DO MATERIAL (CONSUMO OU PERMANENTE) E CASO O RESULTADO SEJA NEGATIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "SAÍDA POR GERAÇÃO DE INVENTÁRIO (34)"

																					 PARA LANÇAMENTO DE CUSTO
																						- ACUMULAR CONFORME OS ITENS DE GASTO E CASO O RESULTADO SEJA POSITIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "ENTRADA POR GERAÇÃO DE INVENTÁRIO (33)"
																						- ACUMULAR CONFORME OS ITENS DE GASTO E CASO O RESULTADO SEJA NEGATIVO, DEVE-SE LANÇAR COMO MOVIMENTAÇãO "SAÍDA POR GERAÇÃO DE INVENTÁRIO (34)"
																			*/
									$Movimentacao = 34; // Saída por acerto de inventario
									$ProgramaDestino      = "CadInventarioPeriodicoFechamento.php";
									$TimeStamp = $DataBaseExt;
									$AnoMovimentacao      = date("Y");


									//DEBUG
									// $db->query('ROLLBACK');
									// $db->query('END TRANSACTION');
									// echo "TESTANDO OS VALORES CONTÁBEIS: ";
									// echo "<BR><BR> ";
									// echo "ITENS DE GASTO: ";
									// print_r($EspecificacoesContabeis);
									// echo "<BR>";
									// echo "VALORES: ";
									// print_r($ValoresContabeis);
									// exit;
									//FIM DEBUG

									# Verificar se os valores contábeis foram são todos zeros
									$valoresZero = true;
									foreach($ValoresContabeis as $valor) if($valor!=0) $valoresZero = false;
									
									if(!$valoresZero){
									
										# Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
										$dbora = ConexaoOracle();
	
										# Evita que Rollback não funcione #
										$dbora->autoCommit(false);
										# Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
										$dbora->query("BEGIN TRANSACTION");
	
										$ConfirmarInclusao = true;
		
										GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
													 $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
													 $EspecificacoesContabeis, $ValoresContabeis,
													 $SubElementosDespesa, $ValoresSubelementos,
													 $Matricula, $Responsavel,
													 $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
													 $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
										exit;
									}else{
										 $db->query("COMMIT");
										 $db->query("END TRANSACTION");
										 $db->disconnect();
											$Mens     = 1;
											$Tipo     = 1;
											$Mensagem = "Inventário Periódico Concluído com Sucesso1";							
									}
						    } else {
								 $db->query("COMMIT");
								 $db->query("END TRANSACTION");
								 $db->disconnect();
									$Mens     = 1;
									$Tipo     = 1;
									$Mensagem = "Inventário Periódico Concluído com Sucesso2";

								}


						} else {
							$db->query('BEGIN TRANSACTION'); //TESTE

							$sql  = "UPDATE SFPC.TBINVENTARIOCONTAGEM ";
							$sql .= "   SET FINVCOFECH = 'S', ";
							$sql .= "       TINVCOFECH = '$datahora', ";
							$sql .= "       TINVCOULAT = '$datahora', ";
							$sql .= "       CGREMPCOD1 = ".$_SESSION['_cgrempcodi_'].", ";
							$sql .= "       CUSUPOCOD1 = ".$_SESSION['_cusupocodi_'];
							$sql .= " WHERE CLOCMACODI = $Localizacao ";
							$sql .= "   AND (FINVCOFECH = 'N' OR FINVCOFECH is null) ";
							//var_dump($sql);die;
							$rescontagem  = $db->query($sql);
							if( db::isError($rescontagem) ){
								$db->query('ROLLBACK');
                   				$db->query('END TRANSACTION');
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
							}

							$sql  = "UPDATE SFPC.TBALMOXARIFADOPORTAL ";
							$sql .= "   SET FALMPOINVE = 'N', ";
							$sql .= "       TALMPOULAT = '$datahora' ";
							$sql .= " WHERE CALMPOCODI = $Almoxarifado";
							var_dump($sql);die;
							$resalmoxarifado  = $db->query($sql);
							////($resalmoxarifado);die;
							if( db::isError($resalmoxarifado) ){
								$db->query('ROLLBACK');
                    			$db->query('END TRANSACTION');
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
							} else {
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();
								$Mens     = 1;
								$Tipo     = 1;
								$Mensagem = "Nenhum material foi movimentado neste ano - Inventário Periódico Concluído com Sucesso3";
								//teste2
								//Resentando os valores para não permitir o mesmo fechamento repetidas vezes
								$Almoxarifado = '';
								$Localizacao = '';
								$Botao = '';
								$Responsavel = '';
								$Matricula = '';
							}
						}
				}
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
	document.CadInventarioPeriodicoFechamento.Botao.value = valor;
	document.CadInventarioPeriodicoFechamento.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoFechamento.Almoxarifado.value ){
			document.CadInventarioPeriodicoFechamento.submit();
	}else	if( ! document.CadInventarioPeriodicoFechamento.Localizacao.value ){
			document.CadInventarioPeriodicoFechamento.submit();
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
<form action="CadInventarioPeriodicoFechamento.php" method="post" name="CadInventarioPeriodicoFechamento">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Fechamento
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?
	if ( $Mens == 1 ) {
			if (!isset($Virgula)){ $Virgula = 1; }
	?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
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

												////($_SESSION['_cgrempcodi_']);
												////($_SESSION['_fperficorp_']);
												////($_SESSION['_cperficodi_']);
												//cperficodi = 2
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db  = Conexao();
												if(($_SESSION['_cgrempcodi_'] == 2) or ($_SESSION['_fperficorp_'] == 'S')){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= " WHERE A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
												} else {
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														$sql .= "   AND A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
														$sql .= "   AND B.CORGLICODI IN ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R') ";
														$sql .= "            AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
														$sql .= "            AND CEN.FCENPOSITU <> 'I' ";

														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";

														$sql .= "       ) ";
														$sql .= "   AND A.CALMPOCODI NOT IN ";
														$sql .= "       ( SELECT CALMPOCODI ";
														$sql .= "           FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql .= "          GROUP BY CALMPOCODI ";
														$sql .= "         HAVING COUNT(*) = 0)";
												}
												$sql .= " ORDER BY A.EALMPODESC ";

												var_dump($sql);
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												}else{
														$Rows = $res->numRows();
														echo "<select name=\"Almoxarifado\" class=\"textonormal\" onchange=\"Localizacao[0].selected=true;submit()\">\n";
														if( $Rows == 0 ){
																echo "	<option value=\"\">NENHUM ALMOXARIFADO DISPONÍVEL</option>\n";
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
														$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B, SFPC.TBALMOXARIFADOPORTAL C ";
														$sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
														$sql .= "   AND A.FLOCMASITU = 'A'";
														$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql .= "   AND A.CALMPOCODI = C.CALMPOCODI ";
														$sql .= "   AND C.FALMPOINVE = 'S' ";
														$sql .= "   AND C.FALMPOSITU = 'A' ";
														$sql .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																$db->disconnect();
																exit;
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
											<td class="textonormal"><input type="text" name="DataBase" value="<?=$DataBase?>" maxlength="10" size="10" class="textonormal" disabled>
											<input type="hidden" name="DataBase" value="<?=$DataBase?>">
										</tr>
										<!--<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Data Base*</td>
											<?php
											if($DataBase == ""){ $DataBase = date("d/m/Y"); }
											$URL = "../calendario.php?Formulario=CadInventarioPeriodicoFechamento&Campo=DataBase";
											?>
											<td class="textonormal"><input type="text" name="DataBase" value="<?=$DataBase?>" maxlength="10" size="10" class="textonormal">
											<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a></td>
										</tr>-->
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Responsável*</td>
											<td class="textonormal"><input type="text" name="Responsavel" value="<?=$Responsavel?>" maxlength="70" size="40" class="textonormal">
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Matrícula*</td>
											<td class="textonormal"><input type="text" name="Matricula" value="<?=$Matricula?>" maxlength="10" size="10" class="textonormal">
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

