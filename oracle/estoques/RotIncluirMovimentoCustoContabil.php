<?php
#--------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotIncluirMovimentoCustoContabil.php
# Objetivo: Programa que Incluir os Movimentos da Requisição para o Centro de Custo do Oracle
# Autor:    Álvaro Faria
# Data:     23/05/2006
# Alterado: Álvaro Faria
# Data:     01/08/2006 - Custo para acerto de inventário
# Alterado: Álvaro Faria
# Data:     19/09/2006 - Exibição, no e-mail de erro de sql, do código e da descrição (getCode & getMessage)
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Transformação do código para ser usado como include, não mais por redirecionamento
# Alterado: Álvaro Faria
# Data:     30/11/2006 - Função de movimentação contábil
# Alterado: Álvaro Faria
# Data:     20/12/2006 - Suporte para inventário periódico
# Alterado: Álvaro Faria
# Data:     03/01/2006 - Suporte para materiais didáticos, fardamento e limpeza
#                        Alteração do tipo da movimentação de inventário periódico para "MOVIMENTAÇÃO POR GERAÇÃO DE INVENTÁRIO" em vez de "ACERTO DE INVENTÁRIO"
#                        Finalização da função de movimentação contábil
# Alterado: Álvaro Faria
# Data:     31/01/2006 - Suporte aos tipos 26 e 27, com nova funcionalidade e descrição
# Alterado: Carlos Abreu
# Data:     07/02/2007 - Suporte ao tipo 32, com nova funcionalidade e descrição
# Alterado: Carlos Abreu
# Data:     18/04/2007 - Remover lancamento contabil para materiais permanentes
# Alterado: Carlos Abreu
# Data:     25/05/2007 - Alteração nas Movimentações 33 e 34 para lançamento em custo e contabil com valores das diferencas entre o armazenado e o inventariado e nao com o saldo anterior de custo
# Alterado: Carlos Abreu
# Data:     18/07/2007 - Rotina para que quando data superior a 01/ago/2007 a funcao insereContabil funcione com material permanente
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------------



//-----------------------------------------------------------
//  MOver variáveis 
//-----------------------------------------------------------
$ResponsavelSecun = to_iso($ResponsavelSecun);


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Se as informações de tipo de movimentação não estiverem especificadas,
# como no caso de Inclusão, seta os padrões. Para movimentações que geram custo
# e podem ser mantidas, usa as informações de acordo com a manutenção efetuada #
if($Movimentacao == 2 or $Movimentacao == 10 or $Movimentacao == 28 or $Movimentacao == 33 or $Movimentacao == 32) {
		if(!$TipoMovAlmox) $TipoMovAlmox = 'E';
		if(!$TipoMovCC)    $TipoMovCC    = 'S'; // Para a movimentação de tipo 2
}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24 or $Movimentacao == 25 or $Movimentacao == 34){
		if(!$TipoMovAlmox) $TipoMovAlmox = 'S';
}else {
	if(!$TipoMovAlmox) $TipoMovAlmox = 'E';
}
# Email quando houver erro #
$Assunto = "TESTE - Rotina de Movimentação p/ Custos e Contabilidade";
# Abre a Conexão com Oracle #
$dbora = ConexaoOracle();
# Função que resgata o maximo sequencial da tabela oracle (SFCP.TBMOVCUSTOALMOXARIFADO) #
function SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db){
		$sql  = "SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
		$sql .= " WHERE DEXERCANOR = $AnoBaixa AND AMOVCUMESM = $MesBaixa AND AMOVCUDIAM = $DiaBaixa ";
		$res  = $dbora->query($sql);
		if(PEAR::isError($res)){
				# Desfaz alterações no Postgre #
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha      = $res->fetchRow();
				$Sequencial = $Linha[0] + 1;
				return $Sequencial; 
		}
}

# Função que resgata o maximo sequencial da tabela oracle (SFCT.TBMOVCONTABILALMOXARIFADO) #
function SequMaxContabil($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db){
		$sql  = "SELECT MAX(AMVCALSEQU) FROM SFCT.TBMOVCONTABILALMOXARIFADO ";
		$sql .= " WHERE APLCTAANOC = $AnoBaixa AND AMVCALMESM = $MesBaixa AND AMVCALDIAM = $DiaBaixa ";
		$res  = $dbora->query($sql);
		if(PEAR::isError($res)){
				# Desfaz alterações no Postgre #
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha      = $res->fetchRow();
				$Sequencial = $Linha[0] + 1;
				return $Sequencial;
		}
}

# Função para inclusão de dados na Contabilidade #
function InsereContabil($Movimentacao, $TipoMaterial, $AnoBaixa,
                        $MesBaixa, $DiaBaixa, $ValorCusto, $Orgao, $Unidade,
                        $Matricula, $Responsavel, $DescMovimentacao,
                        $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db){
                          
                          
        $Responsavel= to_iso($Responsavel);                  
        $DescMovimentacao= to_iso($DescMovimentacao);                                          
                          
                          
        if ($TipoMaterial=='C' or ($TipoMaterial=='P' and (int)date("Ymd")>=20070801)){ // Tipo Permanente funciona apenas quando data igual ou superior a 01/ago/2007
				if($Almoxarifado != 34){
						# Descobre os parâmetros para inclusão #
						$sqlPara  = " SELECT AMVCPMLOTE, AMVCPMTPMC, AMVCPMHIST, AMVCPMCONT, FMVCPMDBCD ";
						$sqlPara .= "   FROM SFPC.TBMOVCONTABILALMOXARIFADOPARAM ";
						$sqlPara .= "  WHERE CTIPMVCODI = $Movimentacao ";
						$sqlPara .= "    AND FMVCPMTIPM = '$TipoMaterial' ";
						$sqlPara .= "    AND AMVCPMANOC = $AnoBaixa ";
						$resPara  = $db->query($sqlPara);
						if( PEAR::isError($resPara) ){
								$CodErroEmail  = $resPara->getCode();
								$DescErroEmail = $resPara->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPara\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}else{
								while($LinhaPara = $resPara->fetchRow()){
										$Sequencial  = SequMaxContabil($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
										$Lote        = $LinhaPara[0];
										$TipoMovCont = $LinhaPara[1];
										$Historico   = $LinhaPara[2];
										$NumeroConta = $LinhaPara[3];
										$Natureza    = $LinhaPara[4];
										# Insere informações de acordo com os parâmetros #
										$sqlCont  = "INSERT INTO SFCT.TBMOVCONTABILALMOXARIFADO ";
										$sqlCont .= "(APLCTAANOC, AMVCALMESM, AMVCALDIAM, AMVCALSEQU, VMVCALVALR, ";
										$sqlCont .= " AMVCALLOTE, CTIPMOCODI, AHMOVINUME, APLCTACONT, FMVCALDBCD, ";
										$sqlCont .= " CORGORCODI, DEXERCANOR, CUNDORCODI, AMVCALMATR, NMVCALRECE, EMVCALDESC, ";
										$sqlCont .= " CMVCALREQU, CMVCALALMO, CMVCALCODI, TMVCALULAT, DMVCALAMVA ";
										$sqlCont .= ")VALUES( ";
										$sqlCont .= " $AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, ".round($ValorCusto, 2).", ";
										$sqlCont .= " $Lote, $TipoMovCont, $Historico, $NumeroConta, '$Natureza', ";
										$sqlCont .= " $Orgao, $AnoBaixa, $Unidade, $Matricula, '$Responsavel', '$DescMovimentacao', ";
										$sqlCont .= " $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, SYSDATE, $AnoMovimentacao ";
										$sqlCont .= ") ";
										$resCont  = $dbora->query($sqlCont);
										if( PEAR::isError($resCont) ){
												$CodErroEmail  = $resCont->getCode();
												$DescErroEmail = $resCont->getMessage();
												# Desfaz alterações no Postgre #
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												# Desfaz alterações no Oracle #
												$dbora->query("ROLLBACK");
												$dbora->query("END TRANSACTION");
												$dbora->disconnect();
												ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCont\n\n$DescErroEmail ($CodErroEmail)");
												exit;
										}
								}
						}
				}
        }
}

# Função que pega a descrição do tipo da movimentação #
function MovDesc($Movimentacao,$dbora,$db){
		$sql    = "SELECT ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
		$sql   .= " WHERE CTIPMVCODI = $Movimentacao ";
		$res    = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha = $res->fetchRow();
				return $Linha[0];
		}
}

if($MovimentacaoSecun){
		# PEGA A DESCRIÇÃO DO TIPO DE MOVIMENTAÇÃO SECUNDÁRIA #
		$sql    = "SELECT ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
		$sql   .= " WHERE CTIPMVCODI = $MovimentacaoSecun ";
		$res    = $db->query($sql);
		if(PEAR::isError($res)){
				# Desfaz alterações no Postgre #
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha         = $res->fetchRow();
				$DescMoviSecun = $Linha[0];
		}
}

# Verifica se existe dados em unidade orçamentaria #
$sql  = "SELECT COUNT(*) FROM SPOD.TBUNIDADEORCAMENT WHERE CUNDORCODI = $Unidade AND DEXERCANOR = $AnoBaixa ";
$res  = $dbora->query($sql);
if(PEAR::isError($res)){
		# Desfaz alterações no Postgre #
		$db->query("ROLLBACK");
		$db->query("END TRANSACTION");
		$db->disconnect();
		# Desfaz alterações no Oracle #
		$dbora->query("ROLLBACK");
		$dbora->query("END TRANSACTION");
		$dbora->disconnect();
		ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
		$Linha = $res->fetchRow();
		$qtdrec = $Linha[0];
		if($qtdrec == 0){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nErro de Integridade na Tabela SPOD.TBUNIDADEORCAMENT (Oracle)\nSql: $sql");
				exit;
		}else{
				# Verifica se existe dados em rpa #
				$sql  = "SELECT COUNT(*) FROM SFCL.TBRPA WHERE CRPAAACODI = $RPA ";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						exit;
				}else{
						$Linha = $res->fetchRow();
						$qtdrec = $Linha[0];
						if($qtdrec == 0){
								$dbora->disconnect();
								$db->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nErro de Integridade na Tabela SFCL.TBRPA (Oracle)\nSql: $sql");
								exit;
						}else{
								# Verifica se existe dados em centro de custo #
								if($CentroCusto == 799){
										$sql  = "SELECT COUNT(*) FROM SFCP.TBCENTROCUSTOPUBLICO WHERE CCENCPCODI IN (799,800) ";
								}else{
										$sql  = "SELECT COUNT(*) FROM SFCP.TBCENTROCUSTOPUBLICO WHERE CCENCPCODI = $CentroCusto ";
								}
								$res  = $dbora->query($sql);
								if(PEAR::isError($res)){
										# Desfaz alterações no Postgre #
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										# Desfaz alterações no Oracle #
										$dbora->query("ROLLBACK");
										$dbora->query("END TRANSACTION");
										$dbora->disconnect();
										ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										exit;
								}else{
										$Linha = $res->fetchRow();
										$qtdrec = $Linha[0];
										if( ($CentroCusto != 799 and $qtdrec == 0) or ($CentroCusto == 799 and $qtdrec < 2) ){
												$dbora->disconnect();
												$db->disconnect();
												ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nErro de Integridade na Tabela SFCP.TBCENTROCUSTOPUBLICO (Oracle) com a Tabela SFPC.TBCENTROCUSTOPORTAL (Postgre)\nSql: $sql $qtdrec");
												exit;
										}else{
												# Verifica se existe dados em detalhamento custo #
												$sql  = "SELECT COUNT(*) FROM SFCP.TBDETALHAMENTOCUSTO WHERE CDETCPCODI = $Detalhamento ";
												$res = $dbora->query($sql);
												if(PEAR::isError($res)){
														# Desfaz alterações no Postgre #
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														# Desfaz alterações no Oracle #
														$dbora->query("ROLLBACK");
														$dbora->query("END TRANSACTION");
														$dbora->disconnect();
														ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														exit;
												}else{
														$Linha  = $res->fetchRow();
														$qtdrec = $Linha[0];
														if($qtdrec == 0){
																$dbora->disconnect();
																$db->disconnect();
																ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nErro de Integridade na Tabela SFCP.TBDETALHAMENTOCUSTO (Oracle) com a Tabela SFPC.TBCENTROCUSTOPORTAL (Postgre)\nSql: $sql");
																exit;
														}
												}
										}
								}
						}
				}
		}
}


# Seta variáveis #
if(!$MovNumero){
		$CodigoMovimentacao = "NULL";
}else{
		$CodigoMovimentacao = $MovNumero;
}
if(!$MovNumeroSec){
		$CodigoMovimentacaoSec = "NULL";
}else{
		$CodigoMovimentacaoSec = $MovNumeroSec;
}
if(!$SeqRequisicao){
		$SeqRequisicao = "NULL";
}

# ORACLE - SFPC.TBESPECIFICACAOCUSTO CESPCPCODI = 27 PATRIMONIO (EQUIP. E MAT. PERMANENTES) #
# ORACLE - SFPC.TBESPECIFICACAOCUSTO CESPCPCODI = 3 MATERIAL DE CONSUMO #
# ORACLE - Centro de Custo Consumo = 799 / Centro de Custo Permanente = 800 #

# Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO #
$dbora->query("BEGIN TRANSACTION");


# Trabalho no Oracle para movimentação especial - Entrada por Troca #
if($Movimentacao == 11){
		# Descobre a descrição da movimentação #
		$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
		if($ValorTrocaMat1Consumo != 0){
				$EspCustoMat1    = 3;                         // Especificação Consumo
				$EspContabilMat1 = 'C';
				$ValorMat1       = $ValorTrocaMat1Consumo;    // Custo Consumo Material 1
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox1  = 799; # Almoxarifado
				$DetalhamentoAlmox1 =  77; # Extra Atividade
		}elseif($ValorTrocaMat1Permane != 0){
				$EspCustoMat1    = 27;                        // Especificação Permanente
				$EspContabilMat1 = 'P';
				$ValorMat1       = $ValorTrocaMat1Permane;    // Custo Permanente Material 1
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox1  = 800; # Patrimônio
				$DetalhamentoAlmox1 =  77; # Extra Atividade
		}elseif($ValorTrocaMat1Didatic != 0){
				$EspCustoMat1    = 6;                         // Especificação Didático
				$EspContabilMat1 = 'C';
				$ValorMat1       = $ValorTrocaMat1Didatic;    // Custo Didático Material 1
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox1  = 799; # Almoxarifado
				$DetalhamentoAlmox1 =  77; # Extra Atividade
		}elseif($ValorTrocaMat1Fardame != 0){
				$EspCustoMat1    = 30;                        // Especificação Fardamento
				$EspContabilMat1 = 'C';
				$ValorMat1       = $ValorTrocaMat1Fardame; // Custo Fardamento Material 1
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox1  = 799; # Almoxarifado
				$DetalhamentoAlmox1 =  77; # Extra Atividade
		}elseif($ValorTrocaMat1Limpeza != 0){
				$EspCustoMat1    = 37;                        // Especificação Limpeza
				$EspContabilMat1 = 'C';
				$ValorMat1       = $ValorTrocaMat1Limpeza;    // Custo Limpeza Material 1
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox1  = 799; # Almoxarifado
				$DetalhamentoAlmox1 =  77; # Extra Atividade
		}

		if($ValorTrocaMat2Consumo != 0){
				$EspCustoMat2    = 3;                       // Especificação Consumo
				$EspContabilMat2 = 'C';
				$ValorMat2       = $ValorTrocaMat2Consumo;  // Custo Consumo Material 2
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox2  = 799; # Almoxarifado
				$DetalhamentoAlmox2 =  77; # Extra Atividade
		}elseif($ValorTrocaMat2Permane != 0){
				$EspCustoMat2    = 27;                      // Especificação Permanente
				$EspContabilMat2 = 'P';
				$ValorMat2       = $ValorTrocaMat2Permane;  // Custo Permanente Material 2
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox2  = 800; # Patrimônio
				$DetalhamentoAlmox2 =  77; # Extra Atividade
		}elseif($ValorTrocaMat2Didatic != 0){
				$EspCustoMat2    = 6;                       // Especificação Didático
				$EspContabilMat2 = 'C';
				$ValorMat2       = $ValorTrocaMat2Didatic;  // Custo Didático Material 2
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox2  = 799; # Patrimônio
				$DetalhamentoAlmox2 =  77; # Extra Atividade
		}elseif($ValorTrocaMat2Fardame != 0){
				$EspCustoMat2    = 30;                        // Especificação Fardamento
				$EspContabilMat2 = 'C';
				$ValorMat2       = $ValorTrocaMat2Fardame; // Custo Fardamento Material 2
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox2  = 799; # Patrimônio
				$DetalhamentoAlmox2 =  77; # Extra Atividade
		}elseif($ValorTrocaMat2Limpeza != 0){
				$EspCustoMat2    = 37;                        // Especificação Limpeza
				$EspContabilMat2 = 'C';
				$ValorMat2       = $ValorTrocaMat2Limpeza;    // Custo Limpeza Material 2
				# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
				$CentroCustoAlmox2  = 799; # Patrimônio
				$DetalhamentoAlmox2 =  77; # Extra Atividade
		}

		# Insere movimentações na contabilidade #
		InsereContabil('11', $EspContabilMat1, $AnoBaixa,
		               $MesBaixa, $DiaBaixa, $ValorMat1, $Orgao, $Unidade,
		               $Matricula, $Responsavel, $DescMovimentacao,
		               $SeqRequisicao, $Almoxarifado, $MovNumeroMat1Almox, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
		InsereContabil('15', $EspContabilMat2, $AnoBaixa,
		               $MesBaixa, $DiaBaixa, $ValorMat2, $Orgao, $Unidade,
		               $Matricula, $Responsavel, $DescMoviSecun,
		               $SeqRequisicao, $Almoxarifado, $MovNumeroMat2Almox, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
		InsereContabil('11', $EspContabilMat2, $AnoBaixa,
		               $MesBaixa, $DiaBaixa, $ValorMat2, $OrgaoSecun, $UnidadeSecun,
		               $MatriculaSecun, $ResponsavelSecun, $DescMovimentacao,
		               $SeqRequisicao, $AlmoxSec, $MovNumeroMat2AlmoxSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
		InsereContabil('15', $EspContabilMat1, $AnoBaixa,
		               $MesBaixa, $DiaBaixa, $ValorMat1, $OrgaoSecun, $UnidadeSecun,
		               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
		               $SeqRequisicao, $AlmoxSec, $MovNumeroMat1AlmoxSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);

		$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
		# GERA ENTRADA P/ CC 799 ou 800 DO ALMOXARIFADO #
		$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
		$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
		$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
		$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
		$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
		$sql .= "CMOVCUCODI, DMOVCUAMVA ";
		$sql .= ") VALUES ( ";
		$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
		$sql .= "$CentroCustoAlmox1, $EspCustoMat1, $DetalhamentoAlmox1, $Orgao, $Unidade, ";
		$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ";
		$sql .= "".round($ValorMat1, 2).", ";
		$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
		$sql .= "'$MovNumeroMat1Almox' )";
		$res  = $dbora->query($sql);
		if(PEAR::isError($res)){
				$Rollback = 1;
				$CodErroEmail  = $res->getCode();
				$DescErroEmail = $res->getMessage();
				# Desfaz alterações no Postgre #
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
				# Desfaz alterações no Oracle #
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
				exit;
		}else{
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAIDA P/ CC 799 ou 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox2, $EspCustoMat2, $DetalhamentoAlmox2, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ";
				$sql .= "".round($ValorMat2, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "'$MovNumeroMat2Almox' )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC 799 ou 800 DO ALMOXARIFADO SECUNDÁRIO #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox2, $EspCustoMat2, $DetalhamentoAlmox2, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "".round($ValorMat2, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$MovNumeroMat2AlmoxSec )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}else{
								$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
								# GERA SAÍDA P/ CC 799 ou 800 DO ALMOXARIFADO SECUNDÁRIO #
								$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
								$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
								$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
								$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
								$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
								$sql .= "CMOVCUCODI, DMOVCUAMVA ";
								$sql .= ") VALUES ( ";
								$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
								$sql .= "$CentroCustoAlmox1, $EspCustoMat1, $DetalhamentoAlmox1, $OrgaoSecun, $UnidadeSecun, ";
								$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ";
								$sql .= "".round($ValorMat1, 2).", ";
								$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
								$sql .= "$MovNumeroMat1AlmoxSec )";
								$res  = $dbora->query($sql);
								if(PEAR::isError($res)){
										$Rollback = 1;
										$CodErroEmail  = $res->getCode();
										$DescErroEmail = $res->getMessage();
										# Desfaz alterações no Postgre #
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										# Desfaz alterações no Oracle #
										$dbora->query("ROLLBACK");
										$dbora->query("END TRANSACTION");
										$dbora->disconnect();
										ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										exit;
								}else{
										if($ProgramaDestino == "CadMovimentacaoConfirmar.php"){
												$Mensagem = urlencode("Movimentação Incluída com Sucesso");
												$Url = "estoques/CadMovimentacaoIncluir.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												RedirecionaPost($Url);
												exit;
										}
								}
						}
				}
		}
}


# Trabalhos no Oracle para movimentações normais #

# BLOCO DE CUSTO PERMANENTE - 27 # // Não Gera Contabil
if($ValorCustoPermanente and $ValorCustoPermanente != 0.00 and $Rollback != 1){

		$PadraoMovimentacao = $Movimentacao;
		$PadraoTipoMovAlmox = $TipoMovAlmox;
		if( ($ValorCustoPermanente > 0) and ($Movimentacao == 34) ){
				$Movimentacao = 33;
				$TipoMovAlmox = 'E';
		}
		$ValorCustoPermanente = abs($ValorCustoPermanente);

		$EspCusto    =  27; // Permanente
		$EspContabil = 'P'; // Permanente
		# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
		$CentroCustoAlmox  = 800; # Patrimônio
		$DetalhamentoAlmox =  77; # Extra Atividade
		$sql  = "SELECT CALMPONRPA ";
		$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha        = $res->fetchRow();
				$RPAAlmox     = $Linha[0];
		}
		
		# SALDO INICIAL ESTOQUE - 01 #
		if($Movimentacao == 1){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('1', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# DEVOLUÇÃO INTERNA - 02 #
		}elseif($Movimentacao == 2){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('2', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto - Pode também ser entrada se for alteração, especificado por $TipoMovCC #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, '$TipoMovCC', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR REQUISIÇÃO - BAIXA - 04 #
		}elseif($Movimentacao == 4){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('4', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 6){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('6', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('12', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, '$AnoMovimentacao' )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}
		
		# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 9){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('9', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('13', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA OU ENTRADA DE MATERIAL DE ILUMINAÇÃO RECUPERADO #
		}elseif($Movimentacao == 10 or $Movimentacao == 32){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO (RECEBEDOR SE DOAÇÃO EXTERNA) - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA POR OBSOLETISMO - AVARIA - VENCIMENTO DO PRAZO DE VALIDADE - FURTO - DOAÇÃO EXTERNA #
		}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# ENTRADA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 21){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('21', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 22){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('22', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
		}elseif($Movimentacao == 25){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('25', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 26){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('26', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 27){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('27', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR ACERTO DE INVENTÁRIO #
		}elseif($Movimentacao == 28){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('28', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
		}elseif($Movimentacao == 29){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('29', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('30', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoPermanente, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}
		
		# CASO SEJA SAÍDA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 34){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('34', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 800 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		
		# CASO SEJA ENTRADA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 33){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('33', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoPermanente, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 800 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoPermanente, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		}else{
				# O programa que invocou a rotina acreditava que precisava incluir custo, porém #
				# não encontrou uma entrada para processar tal ação. Desta forma, a variável #
				# $Rollback é setada, para que as movimentações do Postgre não sejam commitadas #
				$Rollback = 1;
		}
		$Movimentacao = $PadraoMovimentacao;
		$TipoMovAlmox = $PadraoTipoMovAlmox;
}


# BLOCO DE CUSTO CONSUMO - 3 #
if($ValorCustoConsumo and $ValorCustoConsumo != 0.00 and $Rollback != 1){

		$PadraoMovimentacao = $Movimentacao;
		$PadraoTipoMovAlmox = $TipoMovAlmox;
		
		if( ($ValorCustoConsumo > 0) and ($Movimentacao == 34) ){
				$Movimentacao = 33;
				$TipoMovAlmox = 'E';
		}
		
		$ValorCustoConsumo = abs($ValorCustoConsumo);

		$EspCusto    =   3; // Consumo
		$EspContabil = 'C'; // Consumo
		# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
		$CentroCustoAlmox  = 799; # Almoxarifado
		$DetalhamentoAlmox =  77; # Extra Atividade
		$sql  = "SELECT CALMPONRPA ";
		$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha        = $res->fetchRow();
				$RPAAlmox     = $Linha[0];
		}
		
		# SALDO INICIAL ESTOQUE - 01 #
		if($Movimentacao == 1){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('1', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# DEVOLUÇÃO INTERNA - 02 #
		}elseif($Movimentacao == 2){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('2', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto - Pode também ser entrada se for alteração, especificado por $TipoMovCC #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, '$TipoMovCC', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR REQUISIÇÃO - BAIXA - 04 #
		}elseif($Movimentacao == 4){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('4', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 6){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('6', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('12', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 9){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('9', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('13', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA OU ENTRADA DE MATERIAL DE ILUMINAÇÃO RECUPERADO #
		}elseif($Movimentacao == 10 or $Movimentacao == 32){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO (RECEBEDOR SE DOAÇÃO EXTERNA) - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA POR OBSOLETISMO - AVARIA - VENCIMENTO DO PRAZO DE VALIDADE - FURTO - DOAÇÃO EXTERNA #
		}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# ENTRADA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 21){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('21', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 22){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('22', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
		}elseif($Movimentacao == 25){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('25', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 26){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('26', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 27){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('27', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR ACERTO DE INVENTÁRIO #
		}elseif($Movimentacao == 28){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('28', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
		}elseif($Movimentacao == 29){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('29', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('30', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoConsumo, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 34){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('34', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		
		# CASO SEJA ENTRADA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 33){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('33', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoConsumo, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoConsumo, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		}else{
				# O programa que invocou a rotina acreditava que precisava incluir custo, porém #
				# não encontrou uma entrada para processar tal ação. Desta forma, a variável #
				# $Rollback é setada, para que as movimentações do Postgre não sejam commitadas #
				$Rollback = 1;
		}
		$Movimentacao = $PadraoMovimentacao;
		$TipoMovAlmox = $PadraoTipoMovAlmox;
}


# BLOCO DE CUSTO DIDÁTICO - 6 #
if($ValorCustoDidatico and $ValorCustoDidatico != 0.00 and $Rollback != 1){

		$PadraoMovimentacao = $Movimentacao;
		$PadraoTipoMovAlmox = $TipoMovAlmox;
		
		if( ($ValorCustoDidatico > 0) and ($Movimentacao == 34) ){
				$Movimentacao = 33;
				$TipoMovAlmox = 'E';
		}
		
		$ValorCustoDidatico = abs($ValorCustoDidatico);

		$EspCusto    =   6; // Consumo
		$EspContabil = 'C'; // Consumo
		# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
		$CentroCustoAlmox  = 799; # Almoxarifado
		$DetalhamentoAlmox =  77; # Extra Atividade
		$sql  = "SELECT CALMPONRPA ";
		$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha        = $res->fetchRow();
				$RPAAlmox     = $Linha[0];
		}

		# SALDO INICIAL ESTOQUE - 01 #
		if($Movimentacao == 1){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('1', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# DEVOLUÇÃO INTERNA - 02 #
		}elseif($Movimentacao == 2){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('2', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto - Pode também ser entrada se for alteração, especificado por $TipoMovCC #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, '$TipoMovCC', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR REQUISIÇÃO - BAIXA - 04 #
		}elseif($Movimentacao == 4){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('4', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 6){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('6', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('12', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 9){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('9', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('13', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA OU ENTRADA DE MATERIAL DE ILUMINAÇÃO RECUPERADO #
		}elseif($Movimentacao == 10 or $Movimentacao == 32){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO (RECEBEDOR SE DOAÇÃO EXTERNA) - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA POR OBSOLETISMO - AVARIA - VENCIMENTO DO PRAZO DE VALIDADE - FURTO - DOAÇÃO EXTERNA #
		}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# ENTRADA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 21){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('21', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 22){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('22', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
		}elseif($Movimentacao == 25){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('25', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 26){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('26', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 27){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('27', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR ACERTO DE INVENTÁRIO #
		}elseif($Movimentacao == 28){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('28', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
		}elseif($Movimentacao == 29){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('29', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('30', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoDidatico, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 34){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('34', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		
		# CASO SEJA ENTRADA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 33){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('33', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoDidatico, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoDidatico, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		}else{
				# O programa que invocou a rotina acreditava que precisava incluir custo, porém #
				# não encontrou uma entrada para processar tal ação. Desta forma, a variável #
				# $Rollback é setada, para que as movimentações do Postgre não sejam commitadas #
				$Rollback = 1;
		}
		$Movimentacao = $PadraoMovimentacao;
		$TipoMovAlmox = $PadraoTipoMovAlmox;
}


# BLOCO DE CUSTO FARDAMENTO - 30 #
if($ValorCustoFardamento and $ValorCustoFardamento != 0.00 and $Rollback != 1){

		$PadraoMovimentacao = $Movimentacao;
		$PadraoTipoMovAlmox = $TipoMovAlmox;
		
		if( ($ValorCustoFardamento > 0) and ($Movimentacao == 34) ){
				$Movimentacao = 33;
				$TipoMovAlmox = 'E';
		}
		
		$ValorCustoFardamento = abs($ValorCustoFardamento);

		$EspCusto    =  30; // Consumo
		$EspContabil = 'C'; // Consumo
		# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
		$CentroCustoAlmox  = 799; # Almoxarifado
		$DetalhamentoAlmox =  77; # Extra Atividade
		$sql  = "SELECT CALMPONRPA ";
		$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha        = $res->fetchRow();
				$RPAAlmox     = $Linha[0];
		}

		# SALDO INICIAL ESTOQUE - 01 #
		if($Movimentacao == 1){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('1', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# DEVOLUÇÃO INTERNA - 02 #
		}elseif($Movimentacao == 2){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('2', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto - Pode também ser entrada se for alteração, especificado por $TipoMovCC #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, '$TipoMovCC', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR REQUISIÇÃO - BAIXA - 04 #
		}elseif($Movimentacao == 4){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('4', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 6){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('6', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('12', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 9){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('9', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('13', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA OU ENTRADA DE MATERIAL DE ILUMINAÇÃO RECUPERADO #
		}elseif($Movimentacao == 10 or $Movimentacao == 32){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO (RECEBEDOR SE DOAÇÃO EXTERNA) - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA POR OBSOLETISMO - AVARIA - VENCIMENTO DO PRAZO DE VALIDADE - FURTO - DOAÇÃO EXTERNA #
		}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# ENTRADA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 21){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('21', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 22){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('22', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
		}elseif($Movimentacao == 25){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('25', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 26){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('26', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 27){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('27', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR ACERTO DE INVENTÁRIO #
		}elseif($Movimentacao == 28){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('28', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
		}elseif($Movimentacao == 29){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('29', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('30', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoFardamento, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 34){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('34', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		
		# CASO SEJA ENTRADA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 33){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('33', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoFardamento, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoFardamento, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		}else{
				# O programa que invocou a rotina acreditava que precisava incluir custo, porém #
				# não encontrou uma entrada para processar tal ação. Desta forma, a variável #
				# $Rollback é setada, para que as movimentações do Postgre não sejam commitadas #
				$Rollback = 1;
		}
		$Movimentacao = $PadraoMovimentacao;
		$TipoMovAlmox = $PadraoTipoMovAlmox;
}


# BLOCO DE CUSTO LIMPEZA - 37 #
if($ValorCustoLimpeza and $ValorCustoLimpeza != 0.00 and $Rollback != 1){

		$PadraoMovimentacao = $Movimentacao;
		$PadraoTipoMovAlmox = $TipoMovAlmox;
		
		if( ($ValorCustoLimpeza > 0) and ($Movimentacao == 34) ){
				$Movimentacao = 33;
				$TipoMovAlmox = 'E';
		}
		
		$ValorCustoLimpeza = abs($ValorCustoLimpeza);

		$EspCusto    =  37; // Consumo
		$EspContabil = 'C'; // Consumo
		# DADOS NECESSÁRIO PARA MOVIMENTAÇÃO NOS ALMOXARIFADOS #
		$CentroCustoAlmox  = 799; # Almoxarifado
		$DetalhamentoAlmox =  77; # Extra Atividade
		$sql  = "SELECT CALMPONRPA ";
		$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha        = $res->fetchRow();
				$RPAAlmox     = $Linha[0];
		}

		# SALDO INICIAL ESTOQUE - 01 #
		if($Movimentacao == 1){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('1', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# DEVOLUÇÃO INTERNA - 02 #
		}elseif($Movimentacao == 2){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('2', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto - Pode também ser entrada se for alteração, especificado por $TipoMovCC #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, '$TipoMovCC', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR REQUISIÇÃO - BAIXA - 04 #
		}elseif($Movimentacao == 4){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('4', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 6){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('6', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('12', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
		}elseif($Movimentacao == 9){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('9', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('13', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA OU ENTRADA DE MATERIAL DE ILUMINAÇÃO RECUPERADO #
		}elseif($Movimentacao == 10 or $Movimentacao == 32){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO (RECEBEDOR SE DOAÇÃO EXTERNA) - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA POR OBSOLETISMO - AVARIA - VENCIMENTO DO PRAZO DE VALIDADE - FURTO - DOAÇÃO EXTERNA #
		}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil($Movimentacao, $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# ENTRADA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 21){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('21', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# SAÍDA POR ACERTO DA DEVOLUÇÃO INTERNA #
		}elseif($Movimentacao == 22){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('22', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA ENTRADA P/ CC $CentroCusto #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
						$sql .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
						$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
						$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
		}elseif($Movimentacao == 25){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('25', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 26){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('26', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA SAÍDA PARA CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO #
		}elseif($Movimentacao == 27){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('27', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'S', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR ACERTO DE INVENTÁRIO #
		}elseif($Movimentacao == 28){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('28', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}

		# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
		}elseif($Movimentacao == 29){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentações na contabilidade #
				InsereContabil('29', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				InsereContabil('30', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $OrgaoSecun, $UnidadeSecun,
				               $MatriculaSecun, $ResponsavelSecun, $DescMoviSecun,
				               $SeqRequisicao, $AlmoxSec, $CodigoMovimentacaoSec, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO RECEBEDOR #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPA, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, 'E', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}else{
						$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO DOADOR #
						$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
						$sql .= "CMOVCUCODI, DMOVCUAMVA ";
						$sql .= ") VALUES ( ";
						$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPASecun, ";
						$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $OrgaoSecun, $UnidadeSecun, ";
						$sql .= "$MatriculaSecun, '$ResponsavelSecun', $OrgaoSecun, $UnidadeSecun, ".round($ValorCustoLimpeza, 2).", ";
						$sql .= "SYSDATE, 'S', '$DescMoviSecun', $SeqRequisicao, $AlmoxSec, ";
						$sql .= "$CodigoMovimentacaoSec, $AnoMovimentacao )";
						$res  = $dbora->query($sql);
						if(PEAR::isError($res)){
								$Rollback = 1;
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								# Desfaz alterações no Postgre #
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
								exit;
						}
				}

		# CASO SEJA SAÍDA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 34){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('34', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA SAÍDA P/ CC 799 DO ALMOXARIFADO #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		
		# CASO SEJA ENTRADA POR GERAÇÃO DE INVENTÁRIO #
		}elseif($Movimentacao == 33){
				# Descobre a descrição da movimentação #
				$DescMovimentacao = MovDesc($Movimentacao,$dbora,$db);
				# Insere movimentação na contabilidade #
				InsereContabil('33', $EspContabil, $AnoBaixa,
				               $MesBaixa, $DiaBaixa, $ValorCustoLimpeza, $Orgao, $Unidade,
				               $Matricula, $Responsavel, $DescMovimentacao,
				               $SeqRequisicao, $Almoxarifado, $CodigoMovimentacao, $AnoMovimentacao, $ErroPrograma, $dbora, $db);
				$Sequencial = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				# GERA ENTRADA P/ CC 799 DO ALMOXARIFADO - Ou, se for alteração, pode também ser saída. $TipoMovAlmox especifica #
				$sql  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sql .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sql .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sql .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sql .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
				$sql .= "CMOVCUCODI, DMOVCUAMVA ";
				$sql .= ") VALUES ( ";
				$sql .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sql .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sql .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorCustoLimpeza, 2).", ";
				$sql .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $SeqRequisicao, $Almoxarifado, ";
				$sql .= "$CodigoMovimentacao, $AnoMovimentacao )";
				$res  = $dbora->query($sql);
				if(PEAR::isError($res)){
						$Rollback = 1;
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						# Desfaz alterações no Postgre #
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						exit;
				}
		}else{
				# O programa que invocou a rotina acreditava que precisava incluir custo, porém #
				# não encontrou uma entrada para processar tal ação. Desta forma, a variável #
				# $Rollback é setada, para que as movimentações do Postgre não sejam commitadas #
				$Rollback = 1;
		}
		$Movimentacao = $PadraoMovimentacao;
		$TipoMovAlmox = $PadraoTipoMovAlmox;
}


# Commita, finaliza transações do Post e do Oracle e redireciona para a página inicial da movimentação #
if($Rollback != 1){
		# Commita alterações no Postgre #
		$db->query("COMMIT");
		$db->query("END TRANSACTION");
		$db->disconnect();
		# Commita alterações no Oracle #
		$dbora->query("COMMIT");
		$dbora->query("END TRANSACTION");
		$dbora->disconnect();
		# Trabalha retorno de acordo com o programa que chamou a rotina #
		if($ProgramaDestino == "CadMovimentacaoConfirmar.php"){
				# Grava dados para controle de F5 #
				GravaSessionChkF5($Almoxarifado, $AnoBaixa, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
				# Seta mensagem de sucesso e redireciona para página inicial da movimentação #
				$Mensagem = urlencode("Movimentação Incluída com Sucesso");
				$Url = "estoques/CadMovimentacaoIncluir.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}elseif($ProgramaDestino == "CadMovimentacaoAlterar.php"){
				# Seta mensagem de sucesso e redireciona para página inicial da movimentação #
				$Mensagem = urlencode("Movimentação Alterada com Sucesso");
				$Url = "estoques/CadMovimentacaoSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}elseif($ProgramaDestino == "CadRequisicaoBaixa.php"){
				# Seta mensagem de sucesso e redireciona para página inicial da movimentação #
				$Mensagem = urlencode("Baixa da Requisição efetuada com Sucesso");
				$Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}
}else{
		# Se chegou aqui e Rollback é igual a 1, é porque esta variável foi setada manualmente #
		# pois nenhuma entrada para a movimentação especificada foi encontrada
		$Mensagem = urlencode("Problema na geração de custo. Nenhuma alteração foi efetuada");
		if($ProgramaDestino == "CadMovimentacaoConfirmar.php"){
				$Url = "estoques/CadMovimentacaoIncluir.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}elseif($ProgramaDestino == "CadMovimentacaoAlterar.php"){
				$Url = "estoques/CadMovimentacaoSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}elseif($ProgramaDestino == "CadRequisicaoBaixa.php"){
				$Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				RedirecionaPost($Url);
				exit;
		}
}
?>
