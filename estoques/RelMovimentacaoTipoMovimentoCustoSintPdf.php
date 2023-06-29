<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoTipoMovimentoCustoPdf.php
# Autor:    Álvaro Faria
# Data:     20/09/2006
# Alterado: Álvaro Faria
# Data:     21/09/2006 - Alteração dos títulos das colunas da tabela do relatório
# Alterado: Álvaro Faria
# Data:     19/10/2006 - Correção na ordenação do Select
#                        Correção para pegar data de baixa em caso de requisição
#                        Totalizador por data
# Objetivo: Programa para impressão do relatório de movimentações de material
#           agrupadas por tipo de movimentação, para movimentações que geram custo
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimentoCusto.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado     = $_GET['Almoxarifado'];
		$TipoMaterial     = $_GET['TipoMaterial'];
		$TipoMovimentacao = $_GET['TipoMovimentacao'];
		$DataFim          = $_GET['DataFim'];
		$DataIni          = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Movimentação por Tipo Para Custo Sintético";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Datas para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Pega os dados do almoxarifado #
$db   = Conexao();
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}
$pdf->Cell(28,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(162,5,$DescAlmoxarifado,1,1,"L",0);

$pdf->Cell(28,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(162,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->Cell(190,5,"OBS: O asterisco (*) indica que também houve inclusão de movimentação para o Almoxarifado - Extra Atividade (799/77)",0,1,"L",0);
$pdf->ln(3);

# Inicializa variáveis #
$TotalEntradaVal = 0;
$TotalSaidaVal   = 0;

# Caso seja uma movimentação Extra, que não envolva requisição, utiliza os dados do CC a partir do almoxarifado #
$sqlCC  = "SELECT CEN.CCENPOCORG, CEN.CCENPOUNID, CEN.CCENPONRPA, ";
$sqlCC .= "       CEN.CCENPOCENT, CEN.CCENPODETA, ";
$sqlCC .= "       CEN.ECENPODESC, CEN.ECENPODETA ";
$sqlCC .= "  FROM SFPC.TBALMOXARIFADOORGAO ALO, SFPC.TBCENTROCUSTOPORTAL CEN ";
$sqlCC .= " WHERE ALO.CORGLICODI = CEN.CORGLICODI ";
$sqlCC .= "   AND ALO.CALMPOCODI = $Almoxarifado ";
$sqlCC .= "   AND (CEN.CCENPOCENT = 799 or CEN.CCENPOCENT = 800) AND CEN.CCENPODETA = 77 ";
$resCC = $db->query($sqlCC);
if( PEAR::isError($resCC) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC");
}else{
		$rowCC        = $resCC->fetchRow();
		$OrgaoCC      = $rowCC[0];
		$UnidadeCC    = $rowCC[1];
		$RPACC        = $rowCC[2];
		$CentroCC     = $rowCC[3];
		$DetaCC       = $rowCC[4];
		$CentroDescCC = substr($rowCC[5],0,39);
		$CentroDetaCC = substr($rowCC[6],0,43);
}

$sqlitens .= " SELECT MOV.CALMPOCODI, ALM.EALMPODESC, ";
$sqlitens .= "        CASE WHEN MOV.CREQMASEQU IS NOT NULL THEN to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') ELSE MOV.DMOVMAMOVI END AS DATAMOV, ";
$sqlitens .= "        CEN.CCENPOCORG, CEN.CCENPOUNID, CEN.CCENPONRPA, ";
$sqlitens .= "        CEN.CCENPOCENT, CEN.CCENPODETA, ";
$sqlitens .= "        CEN.ECENPODESC, CEN.ECENPODETA, ";
$sqlitens .= "        MOV.AMOVMAQTDM*MOV.VMOVMAVALO AS VALOR, ";
$sqlitens .= "        GRU.FGRUMSTIPM, TIP.FTIPMVTIPO, TIP.CTIPMVCODI, SIT.CTIPSRCODI ";
$sqlitens .= "   FROM SFPC.TBALMOXARIFADOPORTAL ALM, ";
$sqlitens .= "        SFPC.TBMATERIALPORTAL MAT, ";
$sqlitens .= "        SFPC.TBTIPOMOVIMENTACAO TIP, ";
$sqlitens .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU, ";
$sqlitens .= "        SFPC.TBMOVIMENTACAOMATERIAL MOV ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL REQ ";
$sqlitens .= "     ON MOV.CREQMASEQU = REQ.CREQMASEQU ";
# Busca pela situação da requisição, se esta movimentação tiver haver com requisição #
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBSITUACAOREQUISICAO SIT ";
$sqlitens .= "     ON MOV.CREQMASEQU = SIT.CREQMASEQU ";
$sqlitens .= "    AND TSITREULAT IN ";
$sqlitens .= "                   (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
$sqlitens .= "                     WHERE CREQMASEQU = SIT.CREQMASEQU) ";
$sqlitens .= "   LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL CEN ";
$sqlitens .= "     ON REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
$sqlitens .= "  WHERE MOV.CALMPOCODI =  $Almoxarifado  ";
if ($TipoMovimentacao) $sqlitens .= " AND   TIP.FTIPMVTIPO = '$TipoMovimentacao' ";
# Não traz movimentações que não geram custo #
$sqlitens .= "  AND   TIP.CTIPMVCODI NOT IN(0,1,3,5,7,8,18,31) ";
# Dependendo do tipo da movimentação, sendo uma movimentação entre almoxarifados, #
# deve requerer que esta já esteja concluída, pois só elas geram custo #
$sqlitens .= "  AND ( (TIP.CTIPMVCODI IN(12,13,15,30) AND MOV.FMOVMACORR = 'S') OR ";
$sqlitens .= "        (TIP.CTIPMVCODI NOT IN(12,13,15,30) ) ) ";
$sqlitens .= "  AND ";
$sqlitens .= "  CASE WHEN MOV.CREQMASEQU IS NOT NULL THEN ";
$sqlitens .= "            to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') >= '".DataInvertida($DataIni)."' ";
$sqlitens .= "        AND to_date(to_char(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
$sqlitens .= "        AND REQ.CCENPOSEQU = CEN.CCENPOSEQU ";
$sqlitens .= "  ELSE      MOV.DMOVMAMOVI >= '".DataInvertida($DataIni)."' ";
$sqlitens .= "        AND MOV.DMOVMAMOVI <= '".DataInvertida($DataFim)."' ";
$sqlitens .= "        AND (CEN.CCENPOCENT = 799 OR CEN.CCENPOCENT = 800) AND CEN.CCENPODETA = 77 ";
$sqlitens .= "  END ";
$sqlitens .= "  AND   MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
$sqlitens .= "  AND   MAT.CMATEPSEQU = MOV.CMATEPSEQU ";
$sqlitens .= "  AND   MOV.CALMPOCODI = ALM.CALMPOCODI ";
$sqlitens .= "  AND   MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$sqlitens .= "  AND   SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
if($TipoMaterial != 'T'){
		$sqlitens .= "  AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}
$sqlitens .= "  AND   (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
$sqlitens .= " ORDER BY CEN.CCENPONRPA, CEN.CCENPOCENT, CEN.CCENPODETA, GRU.FGRUMSTIPM, DATAMOV, TIP.FTIPMVTIPO ";
$resitens = $db->query($sqlitens);
if( PEAR::isError($resitens) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlitens");
}else{
		$rowsitens = $resitens->numRows();
		if($rowsitens > 0){
				while($rowitens = $resitens->fetchRow()){
						$Almoxarifado          = $rowitens[0];
						$AlmoxarifadoDesc      = substr($rowitens[1],0,43);
						$Data                  = databarra($rowitens[2]);
						$Orgao                 = $rowitens[3];
						$Unidade               = $rowitens[4];
						$RPA                   = $rowitens[5];
						$Centro                = $rowitens[6];
						$Deta                  = $rowitens[7];
						$CentroDesc            = substr($rowitens[8],0,39);
						$CentroDeta            = substr($rowitens[9],0,43);
						$QuantValor            = converte_valor_estoques($rowitens[10]);
						$QuantXValor           = $rowitens[10];
						$ItemGasto             = $rowitens[11];
						if($ItemGasto == 'C') $ItemGasto = '003';
						elseif($ItemGasto == 'P') $ItemGasto = '027';
						$TipoMovimentacao      = $rowitens[12];
						$Movimentacao          = $rowitens[13];
						$SituacaoRequisicao    = $rowitens[14];

						if($Movimentacao != 2 and $Movimentacao != 4 and $Movimentacao != 18 and $Movimentacao != 19 and $Movimentacao != 20 and $Movimentacao != 22){
								# Caso seja uma movimentação Extra, que não envolva requisição, usa os dados do CC a partir do select via almoxarifado órgão #
								$Orgao        = $OrgaoCC;
								$Unidade      = $UnidadeCC;
								$RPA          = $RPACC;
								$Centro       = $CentroCC;
								$Deta         = $DetaCC;
								$CentroDesc   = $CentroDescCC;
								$CentroDeta   = $CentroDetaCC;
								$Asterisco    = 0;
						}else{
								# Se for uma movimentação de requisição, a referência da informação no #
								# relatório de ser do C/C, como a tabela de movimentação referencia o # 
								# almoxarifado, para se ter a outra visão, inverte-se o sentido da movimentação #
								if($TipoMovimentacao == "S"){ $TipoMovimentacao = "E"; } else { $TipoMovimentacao = "S"; }
								# Exibe o asterisco no relatório, referenciando que houve inserção de movimentação também no CC 799/77 #
								$Asterisco    = 1;
						}

						# Só exibe a movimentação de requisição se esta for uma requisição baixada (5), pois só esta gera custo #
						if(!$SituacaoRequisicao or $SituacaoRequisicao == 5){

								if( ($Centro != $CentroEscrito) or ($ItemGasto != $ItemGastoEscrito) ){
										$FlagMudaCC = 1;
								}

								if( ( ($Data != $DataEscrita) or ($FlagMudaCC == 1 and $JaMudouCC == 1) ) and $UmaOuMaisData ){
										# Imprime os totais do C/C anterior #
										if($TotalValDataE) $TotalValDataE = converte_valor_estoques($TotalValDataE);
										if($TotalValDataS) $TotalValDataS = converte_valor_estoques($TotalValDataS);
										$pdf->Cell(124,5,"TOTAL CENTRO DE CUSTO EM $DataEscrita",1,0,"R",1);
										$pdf->Cell(33,5,$TotalValDataE,1,0,"R",0);
										$pdf->Cell(33,5,$TotalValDataS,1,1,"R",0);
										$TotalValDataE = null; $TotalValDataS = null;
								}
								$DataEscrita = $Data;
								$UmaOuMaisData = 1;

								if($FlagMudaCC == 1){
										$FlagMudaCC = 0;
										if($UmaOuMais){
												$JaMudouCC  = 1;
												# Imprime os totais do C/C anterior #
												if($TotalValCenE) $TotalValCenE = converte_valor_estoques($TotalValCenE);
												if($TotalValCenS) $TotalValCenS = converte_valor_estoques($TotalValCenS);
												$pdf->Cell(124,5,"TOTAL CENTRO DE CUSTO",1,0,"R",1);
												$pdf->Cell(33,5,$TotalValCenE,1,0,"R",0);
												$pdf->Cell(33,5,$TotalValCenS,1,1,"R",0);
												$TotalValCenE = null; $TotalValCenS = null;
										}
										$pdf->Cell(190,5,"$CentroDesc - $CentroDeta",1,1,"C",0);
										$CentroDesc = ""; $CentroDeta = "";
								}
								$UmaOuMais = 1;

								if($TipoMovimentacao == "S") {
										$TotalSaidaVal   = $TotalSaidaVal + $QuantXValor;
										$TotalValCenS    = $TotalValCenS + $QuantXValor;
										$TotalValDataS   = $TotalValDataS + $QuantXValor;
								}
								if($TipoMovimentacao == "E") {
										$TotalEntradaVal = $TotalEntradaVal + $QuantXValor;
										$TotalValCenE    = $TotalValCenE + $QuantXValor;
										$TotalValDataE   = $TotalValDataE + $QuantXValor;
								}
								if( ($Centro != $CentroEscrito) or ($ItemGasto != $ItemGastoEscrito) ){
										$CentroEscrito    = $Centro;
										$ItemGastoEscrito = $ItemGasto;
										$pdf->Cell(18,5,"DATA", 1, 0, "C", 1);
										$pdf->Cell(15,5,"ÓRG.", 1, 0, "C", 1);
										$pdf->Cell(15,5,"UNID.", 1, 0, "C", 1);
										$pdf->Cell(15,5,"RPA", 1, 0, "C", 1);
										$pdf->Cell(15,5,"C/C", 1, 0, "C", 1);
										$pdf->Cell(23,5,"FUNC. GOV.", 1, 0, "C", 1);
										$pdf->Cell(23,5,"ITEM GASTO", 1, 0, "C", 1);
										$pdf->Cell(33,5,"ENTRADA", 1, 0, "C", 1);
										$pdf->Cell(33,5,"SAÍDA", 1, 1, "C", 1);
								}
								$CentroEscrito = $Centro;
								$ItemGastoEscrito = $ItemGasto;
								$pdf->Cell(18,5,"$Data", 1, 0, "L", 0);
								$pdf->Cell(15,5,"$Orgao", 1, 0, "R", 0);
								$pdf->Cell(15,5,"$Unidade", 1, 0, "R", 0);
								$pdf->Cell(15,5,"$RPA", 1, 0, "R", 0);
								$pdf->Cell(15,5,"$Centro", 1, 0, "R", 0);
								$pdf->Cell(23,5,"$Deta", 1, 0, "R", 0);
								$pdf->Cell(23,5,"$ItemGasto", 1, 0, "R", 0);
								if($TipoMovimentacao == "E"){
										$pdf->Cell(33,5,"$QuantValor", 1, 0, "R", 0);
										if($Asterisco == 1) $pdf->Cell(33,5,"*", 1, 1, "C", 0);
										else $pdf->Cell(33,5,"", 1, 1, "R", 0);
								}elseif($TipoMovimentacao == "S"){
										if($Asterisco == 1) $pdf->Cell(33,5,"*", 1, 0, "C", 0);
										else $pdf->Cell(33,5,"", 1, 0, "R", 0);
										$pdf->Cell(33,5,"$QuantValor", 1, 1, "R", 0);
								}
						}
				}
				if($UmaOuMais){
						# Imprime última/única data #
						if($TotalValDataE) $TotalValDataE = converte_valor_estoques($TotalValDataE);
						if($TotalValDataS) $TotalValDataS = converte_valor_estoques($TotalValDataS);
						$pdf->Cell(124,5,"TOTAL CENTRO DE CUSTO EM $Data",1,0,"R",1);
						$pdf->Cell(33,5,$TotalValDataE,1,0,"R",0);
						$pdf->Cell(33,5,$TotalValDataS,1,1,"R",0);
						# Imprime último/único total do C/C #
						if($TotalValCenE) $TotalValCenE = converte_valor_estoques($TotalValCenE);
						if($TotalValCenS) $TotalValCenS = converte_valor_estoques($TotalValCenS);
						$pdf->Cell(124,5,"TOTAL DO CENTRO DE CUSTO",1,0,"R",1);
						$pdf->Cell(33,5,$TotalValCenE,1,0,"R",0);
						$pdf->Cell(33,5,$TotalValCenS,1,1,"R",0);
				}
				$pdf->Cell(124,5,"TOTAL GERAL",1,0,"R",1);
				if($TotalEntradaVal) $TotalEntradaVal = converte_valor_estoques($TotalEntradaVal);
				if($TotalSaidaVal)   $TotalSaidaVal   = converte_valor_estoques($TotalSaidaVal);
				if($TotalEntradaVal === 0) $pdf->Cell(33,5,'',1,0,"R",0); else $pdf->Cell(33,5,$TotalEntradaVal,1,0,"R",0);
				if($TotalSaidaVal === 0)   $pdf->Cell(33,5,'',1,1,"R",0); else $pdf->Cell(33,5,$TotalSaidaVal,1,1,"R",0);
		}else{
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelMovimentacaoTipoMovimentoCusto.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}
$db->disconnect();
$pdf->Output();
?>
