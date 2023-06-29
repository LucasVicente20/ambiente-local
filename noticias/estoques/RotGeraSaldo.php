<?php
# Portal da DGCO
# Programa:   RotGeraSaldo.php
# Autor:      Álvaro Faria
# Data:       03/08/2006
# Objetivo:   Geração de saldo inicial dos almoxarifados no Oracle
# OBS.:       Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
//Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Ano          = $_GET['Ano'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Inicia variáveis #
$QuantAlmox  = 0;
$ValorGeralC = 0;
$ValorGeralP = 0;

$AnoIn = date("Y");
$MesIn = date("m");
$DiaIn = date("d");
$Detalhamento = 77;
$DescMovimentacao = "SALDO INICIAL ESTOQUE";

# Função para resgatar o sequencial máximo do Ano / Mês / Dia #
function SequMax($AnoIn, $MesIn, $DiaIn, $dbora, $dbpost){
		# Resgata o maximo sequencial da tabela oracle(SFCP.TBMOVCUSTOALMOXARIFADO) #
		$sql  = "SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
		$sql .= " WHERE DEXERCANOR = $AnoIn AND AMOVCUMESM = $MesIn AND AMOVCUDIAM = $DiaIn ";
		$res  = $dbora->query($sql);
		if(db::isError($res)){
				return null;
		}else{
				$Linha      = $res->fetchRow();
				$Sequencial = $Linha[0] + 1;
				return $Sequencial;
		}
}

# Função para resgatar o órgão, unidade e RPA, a partir do almoxarifado #
function orgunirpa($Almoxarifado, $CentroCusto, $dbora, $dbpost){
		# Resgata valores de órgão, unidade, rpa, centro de custo #
		$sqlOUR  = "SELECT A.CCENPOCORG, A.CCENPOUNID, A.CCENPONRPA ";
		$sqlOUR .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
		$sqlOUR .= " WHERE A.CCENPOCENT = $CentroCusto AND A.CORGLICODI = B.CORGLICODI ";
		$sqlOUR .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPODETA = 77 ";
		$sqlOUR .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$sqlOUR .= " ORDER BY A.CCENPONRPA ASC ";
		$resOUR  = $dbpost->query($sqlOUR);
		if( db::isError($resOUR) ){
				return null;
		}else{
				$rows = $resOUR->numRows();
				if($rows > 0){
						$LinhaOUR    = $resOUR->fetchRow();
						$Orgao       = $LinhaOUR[0];
						$Unidade     = $LinhaOUR[1];
						$RPA         = $LinhaOUR[2];
						return "$Orgao.$Unidade.$RPA";
				}else{
						return null;
				}
		}
}

# Abre a Conexão com Oracle #
$dbora  = ConexaoOracle();
# Abre a Conexão com Postgre #
$dbpost = Conexao();

# INICIANDO TRANSAÇÃO #
$dbora->query("BEGIN TRANSACTION");

# Busca pelo Almoxarifado especificado, ou todos, trazendo a flag de fechamento, se este almoxarifado fez inventário #
$sql  = " SELECT A.CALMPOCODI, A.EALMPODESC, C.FINVCOFECHA ";
$sql .= "   FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBLOCALIZACAOMATERIAL B ";
$sql .= "        LEFT OUTER JOIN SFPC.TBINVENTARIOCONTAGEM C ON (B.CLOCMACODI = C.CLOCMACODI AND C.AINVCOANOB = 2006) ";
$sql .= "  WHERE A.CALMPOCODI = B.CALMPOCODI ";
if($Almoxarifado != "T") {
		$sql .= " AND B.CALMPOCODI = $Almoxarifado ";
}
$sql .= " ORDER BY A.EALMPODESC ";
$res  = $dbpost->query($sql);
if( db::isError($res) ){
		$Rollback = 1;
		EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		while($Linha = $res->fetchRow() and !$Rollback){
				$Almoxarifado = $Linha[0];
				$AlmoxDesc    = $Linha[1];
				$Fechamento   = $Linha[2];
				# Caso seja um Almoxarifado que não fez fechamento, faz dois selects simples das entradas e das saídas e subtrai um do outro #
				if($Fechamento != 'S'){
						# Calcula as entradas Consumo #
						$sqlEC  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
						$sqlEC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
						$sqlEC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
						$sqlEC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
						$sqlEC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
						$sqlEC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
						$sqlEC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
						$sqlEC .= "    AND D.FGRUMSTIPM = 'C' ";
						$sqlEC .= "    AND A.CTIPMVCODI IN (1,5,28) ";
						$sqlEC .= "    AND A.CALMPOCODI = $Almoxarifado ";
						$sqlEC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
						$resEC  = $dbpost->query($sqlEC);
						if( db::isError($resEC) ){
								$Rollback = 1;
								EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlEC");
						}else{
								$LinhaEC = $resEC->fetchRow();
								$ValorEntradasC = $LinhaEC[0];
								# Calcula as entradas Permanente #
								$sqlEP  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
								$sqlEP .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
								$sqlEP .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
								$sqlEP .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
								$sqlEP .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
								$sqlEP .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
								$sqlEP .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
								$sqlEP .= "    AND D.FGRUMSTIPM = 'P' ";
								$sqlEP .= "    AND A.CTIPMVCODI IN (1,5,28) ";
								$sqlEP .= "    AND A.CALMPOCODI = $Almoxarifado ";
								$sqlEP .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
								$resEP  = $dbpost->query($sqlEP);
								if( db::isError($resEP) ){
										$Rollback = 1;
										EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlEP");
								}else{
										$LinhaEP = $resEP->fetchRow();
										$ValorEntradasP = $LinhaEP[0];
										# Calcula as saídas Consumo #
										$sqlSC  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
										$sqlSC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
										$sqlSC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
										$sqlSC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
										$sqlSC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
										$sqlSC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
										$sqlSC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
										$sqlSC .= "    AND D.FGRUMSTIPM = 'C' ";
										$sqlSC .= "    AND A.CTIPMVCODI = 25 ";
										$sqlSC .= "    AND A.CALMPOCODI = $Almoxarifado ";
										$sqlSC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
										$resSC  = $dbpost->query($sqlSC);
										if( db::isError($resSC) ){
												$Rollback = 1;
												EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlSC");
										}else{
												$LinhaSC = $resSC->fetchRow();
												$ValorSaidasC = $LinhaSC[0];
												# Calcula as saídas Permanente #
												$sqlSP  = " SELECT SUM(A.AMOVMAQTDM*A.VMOVMAVALO) ";
												$sqlSP .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A,  ";
												$sqlSP .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
												$sqlSP .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
												$sqlSP .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
												$sqlSP .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
												$sqlSP .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
												$sqlSP .= "    AND D.FGRUMSTIPM = 'P' ";
												$sqlSP .= "    AND A.CTIPMVCODI = 25 ";
												$sqlSP .= "    AND A.CALMPOCODI = $Almoxarifado ";
												$sqlSP .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
												$resSP  = $dbpost->query($sqlSP);
												if( db::isError($resSP) ){
														$Rollback = 1;
														EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlSP");
												}else{
														$LinhaSP      = $resSP->fetchRow();
														$ValorSaidasP = $LinhaSP[0];
														# Calcula totais #
														$TotalAlmoxC  = $ValorEntradasC - $ValorSaidasC;
														$TotalAlmoxP  = $ValorEntradasP - $ValorSaidasP;
														$QuantAlmox   = $QuantAlmox + 1;
														$ValorGeralC  = $ValorGeralC + $TotalAlmoxC;
														$ValorGeralP  = $ValorGeralP + $TotalAlmoxP;
														# INSERT DOS DADOS SIMPLES NO ORACLE #
														if($TotalAlmoxC){
																$CentroCusto = 799;
																$EspCusto    = 3;
																# Descobre Orgão, Unidade e RPA do almoxarifado atual no loop #
																$OUR = orgunirpa($Almoxarifado, $CentroCusto, $dbora, $dbpost);
																$OUR = explode(".",$OUR);
																$Orgao   = $OUR[0];
																$Unidade = $OUR[1];
																$RPA     = $OUR[2];
																# Descobre o sequencial máximo para inclusão de custo #
																$Sequencial = SequMax($AnoIn, $MesIn, $DiaIn, $dbora, $dbpost);
																# Insere custo #
																$sqlc  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
																$sqlc .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
																$sqlc .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
																$sqlc .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
																$sqlc .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
																$sqlc .= "CMOVCUCODI ";
																$sqlc .= ") VALUES ( ";
																$sqlc .= "$AnoIn, $MesIn, $DiaIn, $Sequencial, $RPA, ";
																$sqlc .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
																$sqlc .= "'7242', 'ROSSANA LIRA', $Orgao, $Unidade, ";
																$sqlc .= "".sprintf("%01.2f",$TotalAlmoxC).", ";
																$sqlc .= "SYSDATE, 'E', '$DescMovimentacao', NULL, $Almoxarifado, ";
																$sqlc .= "NULL )";
																$resc  = $dbora->query($sqlc);
																if(db::isError($resc)){
																		$Rollback = 1;
																		EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqlc");
																}
														}
														if($TotalAlmoxP){
																$CentroCusto = 800;
																$EspCusto    = 27;
																# Descobre Orgão, Unidade e RPA do almoxarifado atual no loop #
																$OUR = orgunirpa($Almoxarifado, $CentroCusto, $dbora, $dbpost);
																$OUR = explode(".",$OUR);
																$Orgao   = $OUR[0];
																$Unidade = $OUR[1];
																$RPA     = $OUR[2];
																# Descobre o sequencial máximo para inclusão de custo #
																$Sequencial = SequMax($AnoIn, $MesIn, $DiaIn, $dbora, $dbpost);
																# Insere custo #
																$sqlp  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
																$sqlp .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
																$sqlp .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
																$sqlp .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
																$sqlp .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
																$sqlp .= "CMOVCUCODI ";
																$sqlp .= ") VALUES ( ";
																$sqlp .= "$AnoIn, $MesIn, $DiaIn, $Sequencial, $RPA, ";
																$sqlp .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
																$sqlp .= "'7242', 'ROSSANA LIRA', $Orgao, $Unidade, ";
																$sqlp .= "".sprintf("%01.2f",$TotalAlmoxP).", ";
																$sqlp .= "SYSDATE, 'E', '$DescMovimentacao', NULL, $Almoxarifado, ";
																$sqlp .= "NULL )";
																$resp  = $dbora->query($sqlp);
																if(db::isError($resp)){
																		$Rollback = 1;
																		EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqlp");
																}
														}
												}
										}
								}
						}
				# Se não, o almoxarifado fez fechamento de inventário, passa para o cálculo complexo #
				}else{
						# Inicializa/Zera variável #
						$TotalAlmox  = 0;
						$TotalAlmoxC = 0;
						$TotalAlmoxP = 0;
						$sqlC  = " SELECT A.CMATEPSEQU, A.CTIPMVCODI, A.VMOVMAVALO, A.AMOVMAQTDM, D.FGRUMSTIPM ";
						$sqlC .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL A, ";
						$sqlC .= "        SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
						$sqlC .= "        SFPC.TBGRUPOMATERIALSERVICO D ";
						$sqlC .= "  WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
						$sqlC .= "    AND B.CSUBCLSEQU = C.CSUBCLSEQU ";
						$sqlC .= "    AND C.CGRUMSCODI = D.CGRUMSCODI ";
						$sqlC .= "    AND A.CTIPMVCODI IN (1,5,28, 25) ";
						$sqlC .= "    AND A.CALMPOCODI = $Almoxarifado ";
						$sqlC .= "    AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
						$sqlC .= " ORDER BY A.CMATEPSEQU, A.CTIPMVCODI, A.TMOVMAULAT ";
						$resC  = $dbpost->query($sqlC);
						if( db::isError($resC) ){
								$Rollback = 1;
								EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlC");
						}else{
								while($LinhaC = $resC->fetchRow() and !$Rollback){
										$Material = $LinhaC[0];
										$TipoMov  = $LinhaC[1];
										$ValorMov = $LinhaC[2];
										$QtdmMov  = $LinhaC[3];
										$TipoMat  = $LinhaC[4];
										if($MaterialChecado != $Material){
												# Soma total do material checado ao total do Almoxarifado dependendo do tipo Consumo/Permanente #
												if($TipoMatChecado == 'P'){
														$TotalAlmoxP = $TotalAlmoxP + $TotalMaterial;
												}elseif($TipoMatChecado == 'C'){
														$TotalAlmoxC = $TotalAlmoxC + $TotalMaterial;
												}
												$MaterialChecado = $Material;
												$TipoMatChecado  = $TipoMat;
												# Zera os dados do Material anterior #
												$TotalMaterial = 0;
												$Zerado        = null;
												# Calcula a primeira entrada do próximo Material #
												# Contabiliza positivamente ou negativamente, dependendo do tipo da movimentação #
												if($TipoMov == 5){
														# Descobre o valor do material através da carga inicial #
														$sqlV  = " SELECT VMOVMAVALO ";
														$sqlV .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlV .= "  WHERE CTIPMVCODI = 1 ";
														$sqlV .= "    AND CMATEPSEQU = $Material ";
														$sqlV .= "    AND CALMPOCODI = $Almoxarifado ";
														$sqlV .= "    AND AMOVMAANOM = 2006 ";
														$sqlV .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sqlV .= " ORDER BY TMOVMAULAT DESC ";
														$resV  = $dbpost->query($sqlV);
														if( db::isError($resV) ){
																$Rollback = 1;
																EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlV");
														}else{
																$LinhaV = $resV->fetchRow();
																$ValorMov = $LinhaV[0];
														}
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 1 or $TipoMov == 28){
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 25){
														$TotalMaterial = $TotalMaterial - ($ValorMov*$QtdmMov);
												}
										}else{
												# Contabiliza positivamente ou negativamente, dependendo do tipo da movimentação #
												if($TipoMov == 5){
														if(!$Zerado){
																# Caso seja a primeira execução neste material do tipo inventário, zera o total #
																$TotalMaterial = 0;
																$Zerado = 1;
														}
														# Descobre o valor do material através da carga inicial #
														$sqlV  = " SELECT VMOVMAVALO ";
														$sqlV .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sqlV .= "  WHERE CTIPMVCODI = 1 ";
														$sqlV .= "    AND CMATEPSEQU = $Material ";
														$sqlV .= "    AND CALMPOCODI = $Almoxarifado ";
														$sqlV .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sqlV .= " ORDER BY TMOVMAULAT DESC ";
														$resV  = $dbpost->query($sqlV);
														if( db::isError($resV) ){
																$Rollback = 1;
																EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlV");
														}else{
																$LinhaV = $resV->fetchRow();
																$ValorMov = $LinhaV[0];
														}
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 1 or $TipoMov == 28){
														$TotalMaterial = $TotalMaterial + ($ValorMov*$QtdmMov);
												}elseif($TipoMov == 25){
														$TotalMaterial = $TotalMaterial - ($ValorMov*$QtdmMov);
												}
										}
								}
								# Soma total do último material checado ao total do Almoxarifado #
								if($TipoMat == 'P'){
										$TotalAlmoxP = $TotalAlmoxP + $TotalMaterial;
								}elseif($TipoMatChecado == 'C'){
										$TotalAlmoxC = $TotalAlmoxC + $TotalMaterial;
								}
								# Imprime o Total (Cálculo complexo) para este almoxarifado #
								$QuantAlmox  = $QuantAlmox + 1;
								$ValorGeralC = $ValorGeralC + $TotalAlmoxC;
								$ValorGeralP = $ValorGeralP + $TotalAlmoxP;

								# INSERT DOS DADOS COMPLEXOS NO ORACLE #
								if($TotalAlmoxC){
										$CentroCusto = 799;
										$EspCusto    = 3;
										# Descobre Orgão, Unidade e RPA do almoxarifado atual no loop #
										$OUR = orgunirpa($Almoxarifado, $CentroCusto, $dbora, $dbpost);
										$OUR = explode(".",$OUR);
										$Orgao   = $OUR[0];
										$Unidade = $OUR[1];
										$RPA     = $OUR[2];
										# Descobre o sequencial máximo para inclusão de custo #
										$Sequencial = SequMax($AnoIn, $MesIn, $DiaIn, $dbora, $dbpost);
										# Insere custo #
										$sqlc  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
										$sqlc .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
										$sqlc .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
										$sqlc .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
										$sqlc .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
										$sqlc .= "CMOVCUCODI ";
										$sqlc .= ") VALUES ( ";
										$sqlc .= "$AnoIn, $MesIn, $DiaIn, $Sequencial, $RPA, ";
										$sqlc .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
										$sqlc .= "'7242', 'ROSSANA LIRA', $Orgao, $Unidade, ";
										$sqlc .= "".sprintf("%01.2f",$TotalAlmoxC).", ";
										$sqlc .= "SYSDATE, 'E', '$DescMovimentacao', NULL, $Almoxarifado, ";
										$sqlc .= "NULL )";
										$resc  = $dbora->query($sqlc);
										if(db::isError($resc)){
												$Rollback = 1;
												EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqlc");
										}
								}
								if($TotalAlmoxP){
										$CentroCusto = 800;
										$EspCusto    = 27;
										# Descobre Orgão, Unidade e RPA do almoxarifado atual no loop #
										$OUR = orgunirpa($Almoxarifado, $CentroCusto, $dbora, $dbpost);
										$OUR = explode(".",$OUR);
										$Orgao   = $OUR[0];
										$Unidade = $OUR[1];
										$RPA     = $OUR[2];
										# Descobre o sequencial máximo para inclusão de custo #
										$Sequencial = SequMax($AnoIn, $MesIn, $DiaIn, $dbora, $dbpost);
										# Insere custo #
										$sqlp  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
										$sqlp .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
										$sqlp .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
										$sqlp .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
										$sqlp .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUREQU, CMOVCUALMO, ";
										$sqlp .= "CMOVCUCODI ";
										$sqlp .= ") VALUES ( ";
										$sqlp .= "$AnoIn, $MesIn, $DiaIn, $Sequencial, $RPA, ";
										$sqlp .= "$CentroCusto, $EspCusto, $Detalhamento, $Orgao, $Unidade, ";
										$sqlp .= "'7242', 'ROSSANA LIRA', $Orgao, $Unidade, ";
										$sqlp .= "".sprintf("%01.2f",$TotalAlmoxP).", ";
										$sqlp .= "SYSDATE, 'E', '$DescMovimentacao', NULL, $Almoxarifado, ";
										$sqlp .= "NULL )";
										$resp  = $dbora->query($sqlp);
										if(db::isError($resp)){
												$Rollback = 1;
												EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqlp");
										}
								}
								$TotalMaterial = 0;
								$ValorEntradas = 0;
								$ValorSaidas   = 0;
						}
				}
		}
}
if(!$Rollback){
		$dbora->query("COMMIT TRANSACTION");
		$dbora->query("END TRANSACTION");
		$Mensagem = "Geração de custo no Oracle para $QuantAlmox Almoxarifados, efetuada com sucesso";
		header ("location: RotGeraSaldoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1");
		exit();
}else{
		$dbora->query("ROLLBACK TRANSACTION");
		$Mensagem = "Falha. Nenhuma atualização foi efetuada";
		header ("location: RotGeraSaldoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1");
		exit();
}

$dbora->disconnect();
$dbpost->disconnect();
?>
