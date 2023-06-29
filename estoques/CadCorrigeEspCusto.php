<?php
#-------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCorrigeEspCusto.php
# Autor:    Alvaro Faria
# Data:     22/01/2007
# Objetivo: Corrigir entradas de materiais didáticos, fardamento e limpesa que entraram agrupados como consumo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------------------

exit;


# Acesso ao arquivo de funções #
include "../funcoes.php";

error_reporting("E_ALL&~(E_NOTICE|E_WARNING)");

$db    = Conexao();
$dbora = ConexaoOracle();

# Função que resgata o maximo sequencial da tabela oracle (SFCP.TBMOVCUSTOALMOXARIFADO) #
function SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db){
		$sql  = "SELECT MAX(CMOVCUSEQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
		$sql .= " WHERE DEXERCANOR = $AnoBaixa AND AMOVCUMESM = $MesBaixa AND AMOVCUDIAM = $DiaBaixa ";
		$res  = $dbora->query($sql);
		if(PEAR::isError($res)){
				# Desfaz alterações no Postgre #
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

# Função que pega a descrição do tipo da movimentação #
function MovDesc($Movimentacao,$dbora,$db){
		$sql    = "SELECT ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
		$sql   .= " WHERE CTIPMVCODI = $Movimentacao ";
		$res    = $db->query($sql);
		if( PEAR::isError($res) ){
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				$dbora->disconnect();
				$db->disconnect();
				ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				exit;
		}else{
				$Linha = $res->fetchRow();
				return $Linha[0];
		}
}

# Busca por gerações de inventário por almoxarifado, tipo movimentação e tipo material #
$sql  = "SELECT SUM(A.AMOVMAQTDM * CASE WHEN A.CTIPMVCODI = 33 THEN A.VMOVMAVALO ELSE -A.VMOVMAVALO END), A.CALMPOCODI, D.FGRUMSTIPC ";
$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBMATERIALPORTAL B, ";
$sql .= "       SFPC.TBSUBCLASSEMATERIAL C, SFPC.TBGRUPOMATERIALSERVICO D ";
$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU AND B.CSUBCLSEQU = C.CSUBCLSEQU AND C.CGRUMSCODI = D.CGRUMSCODI ";
$sql .= "   AND A.CTIPMVCODI IN (33,34) ";
$sql .= "   AND D.FGRUMSTIPC <> 'P' ";
$sql .= "   AND D.FGRUMSTIPC <> 'C' ";
$sql .= " GROUP BY A.CALMPOCODI, D.FGRUMSTIPC ";
$sql .= " ORDER BY A.CALMPOCODI, D.FGRUMSTIPC ";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		exit;
}else{
		# Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO #
		//$dbora->query("BEGIN TRANSACTION");
		while($Linha = $res->fetchRow()){
				$Valor        = abs($Linha[0]);
				$Almoxarifado = $Linha[1];
				if($Linha[0] < 0) $TipoMov = 34;
				else $TipoMov = 33;
				$TipoMaterial = $Linha[2];
				if($AlmoxAnt <> $Almoxarifado){
						if($AlmoxAnt){
								if($ValorConsumo33 > 0){
										$Sequencial       = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
										$DescMovimentacao = MovDesc(33,$dbora,$db);
										$TipoMovAlmox     = 'S';
										$EspCusto         = 3;
										$sql33  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
										$sql33 .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
										$sql33 .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
										$sql33 .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
										$sql33 .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUALMO, DMOVCUAMVA  ";
										$sql33 .= ") VALUES ( ";
										$sql33 .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
										$sql33 .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
										$sql33 .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorConsumo33, 2).", ";
										$sql33 .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $AlmoxAnt, $AnoMovimentacao )";
										echo "Almoxarifado: $AlmoxAnt, Tipo Mov: 33, Tipo Material: C, Valor: $ValorConsumo33<BR>";
										echo "$sql33<BR><BR>";
										/*
										$res33 = $dbora->query($sql33);
										if(PEAR::isError($res33)){
												$Rollback = 1;
												$db->disconnect();
												# Desfaz alterações no Oracle #
												$dbora->query("ROLLBACK");
												$dbora->query("END TRANSACTION");
												$dbora->disconnect();
												ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql33");
												exit;
										}
										*/
								}
								if($ValorConsumo34 > 0){
										$Sequencial       = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
										$DescMovimentacao = MovDesc(34,$dbora,$db);
										$TipoMovAlmox     = 'E';
										$EspCusto         = 3;
										$sql34  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
										$sql34 .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
										$sql34 .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
										$sql34 .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
										$sql34 .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUALMO, DMOVCUAMVA  ";
										$sql34 .= ") VALUES ( ";
										$sql34 .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
										$sql34 .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
										$sql34 .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorConsumo34, 2).", ";
										$sql34 .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $AlmoxAnt, $AnoMovimentacao )";
										echo "Almoxarifado: $AlmoxAnt, Tipo Mov: 34, Tipo Material: C, Valor: $ValorConsumo34<BR>";
										echo "$sql34<BR><BR>";
										/*
										$res34 = $dbora->query($sql34);
										if(PEAR::isError($res34)){
												$Rollback = 1;
												$db->disconnect();
												# Desfaz alterações no Oracle #
												$dbora->query("ROLLBACK");
												$dbora->query("END TRANSACTION");
												$dbora->disconnect();
												ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql34");
												exit;
										}
										*/
								}
								echo "----------------------------------------<BR><BR>";
						}

						# Busca dados genéricos na inclusão original feita no Oracle #
						$sqlDados  = "SELECT DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CRPAAACODI, ";
						$sqlDados .= "       CORGORCODI, CUNDORCODI, ";
						$sqlDados .= "       AMOVCUMATR, NMOVCURECE, DMOVCUAMVA ";
						$sqlDados .= "  FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
						$sqlDados .= " WHERE CMOVCUALMO = $Almoxarifado AND EMOVCUDESC LIKE '%POR GERAÇÃO DE INVENTÁRIO%' ";
						$sqlDados .= " ORDER BY TMOVCUULAT DESC";
						$resDados  = $dbora->query($sqlDados);
						if(PEAR::isError($resDados)){
								$Rollback = 1;
								$CodErroEmail  = $resDados->getCode();
								$DescErroEmail = $resDados->getMessage();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDados\n\n$DescErroEmail ($CodErroEmail)");
								exit;						
						}else{
								$LinhaDados = $resDados->fetchRow();
								$AnoBaixa           = $LinhaDados[0];
								$MesBaixa           = $LinhaDados[1];
								$DiaBaixa           = $LinhaDados[2];
								$RPAAlmox           = $LinhaDados[3];
								$Orgao              = $LinhaDados[4];
								$Unidade            = $LinhaDados[5];
								$Matricula          = $LinhaDados[6];
								$Responsavel        = $LinhaDados[7];
								$AnoMovimentacao    = $LinhaDados[8];
								$CentroCustoAlmox   = 799;
								$DetalhamentoAlmox  = 77;
						}
						$ValorConsumo33 = 0;
						$ValorConsumo34 = 0;
						$AlmoxAnt = $Almoxarifado;
				}
				if    ($TipoMov == 33) $ValorConsumo33 = $ValorConsumo33 + $Valor;
				elseif($TipoMov == 34) $ValorConsumo34 = $ValorConsumo34 + $Valor;

				# Bloco Didático, Fardamento, Limpeza #
				$Sequencial       = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
				$DescMovimentacao = MovDesc($TipoMov,$dbora,$db);
				if($TipoMov == 33) $TipoMovAlmox = 'E';
				if($TipoMov == 34) $TipoMovAlmox = 'S';
				if    ($TipoMaterial == 'C') $EspCusto = 3;
				elseif($TipoMaterial == 'D') $EspCusto = 6;
				elseif($TipoMaterial == 'F') $EspCusto = 30;
				elseif($TipoMaterial == 'L') $EspCusto = 37;
				$sqlin  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
				$sqlin .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
				$sqlin .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
				$sqlin .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
				$sqlin .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUALMO, DMOVCUAMVA  ";
				$sqlin .= ") VALUES ( ";
				$sqlin .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
				$sqlin .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
				$sqlin .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($Valor, 2).", ";
				$sqlin .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $AlmoxAnt, $AnoMovimentacao )";
				echo "Almoxarifado: $Almoxarifado, Tipo Mov: $TipoMov, Tipo Material: $TipoMaterial, Valor: $Valor<BR>";
				echo "$sqlin<BR><BR>";
				/*
				$resin = $dbora->query($sqlin);
				if(PEAR::isError($resin)){
						$Rollback = 1;
						$db->disconnect();
						# Desfaz alterações no Oracle #
						$dbora->query("ROLLBACK");
						$dbora->query("END TRANSACTION");
						$dbora->disconnect();
						ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlin");
						exit;
				}
				*/
		}
		if($AlmoxAnt){
				if($ValorConsumo33 > 0){
						$Sequencial       = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						$DescMovimentacao = MovDesc(33,$dbora,$db);
						$TipoMovAlmox     = 'S';
						$EspCusto         = 3;
						$sql33  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql33 .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql33 .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql33 .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql33 .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUALMO, DMOVCUAMVA  ";
						$sql33 .= ") VALUES ( ";
						$sql33 .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
						$sql33 .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
						$sql33 .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorConsumo33, 2).", ";
						$sql33 .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $AlmoxAnt, $AnoMovimentacao )";
						echo "Almoxarifado: $AlmoxAnt, Tipo Mov: 33, Tipo Material: C, Valor: $ValorConsumo33<BR>";
						echo "$sql33<BR><BR>";
						/*
						$res33 = $dbora->query($sql33);
						if(PEAR::isError($res33)){
								$Rollback = 1;
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql33");
								exit;
						}
						*/
				}
				if($ValorConsumo34 > 0){
						$Sequencial       = SequMax($AnoBaixa, $MesBaixa, $DiaBaixa, $dbora, $db);
						$DescMovimentacao = MovDesc(34,$dbora,$db);
						$TipoMovAlmox     = 'E';
						$EspCusto         = 3;
						$sql34  = "INSERT INTO SFCP.TBMOVCUSTOALMOXARIFADO ( ";
						$sql34 .= "DEXERCANOR, AMOVCUMESM, AMOVCUDIAM, CMOVCUSEQU, CRPAAACODI, ";
						$sql34 .= "CCENCPCODI, CESPCPCODI, CDETCPCODI, CORGORCODI, CUNDORCODI, ";
						$sql34 .= "AMOVCUMATR, NMOVCURECE, CORGORCOD1, CUNDORCOD1, VMOVCUREQU, ";
						$sql34 .= "TMOVCUULAT, FMOVCULANC, EMOVCUDESC, CMOVCUALMO, DMOVCUAMVA  ";
						$sql34 .= ") VALUES ( ";
						$sql34 .= "$AnoBaixa, $MesBaixa, $DiaBaixa, $Sequencial, $RPAAlmox, ";
						$sql34 .= "$CentroCustoAlmox, $EspCusto, $DetalhamentoAlmox, $Orgao, $Unidade, ";
						$sql34 .= "$Matricula, '$Responsavel', $Orgao, $Unidade, ".round($ValorConsumo34, 2).", ";
						$sql34 .= "SYSDATE, '$TipoMovAlmox', '$DescMovimentacao', $AlmoxAnt, $AnoMovimentacao )";
						echo "Almoxarifado: $AlmoxAnt, Tipo Mov: 34, Tipo Material: C, Valor: $ValorConsumo34<BR>";
						echo "$sql34<BR><BR>";
						/*
						$res34 = $dbora->query($sql34);
						if(PEAR::isError($res34)){
								$Rollback = 1;
								$db->disconnect();
								# Desfaz alterações no Oracle #
								$dbora->query("ROLLBACK");
								$dbora->query("END TRANSACTION");
								$dbora->disconnect();
								ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql34");
								exit;
						}
						*/
				}
				echo "----------------------------------------<BR>";
		}
/*		if(!$Rollback){
				$dbora->query("COMMIT");
				$dbora->query("END TRANSACTION");
		}else{
				$dbora->query("ROLLBACK");
				$dbora->query("END TRANSACTION");
				
		}*/
}

$dbora->disconnect();
$db->disconnect();

?>
