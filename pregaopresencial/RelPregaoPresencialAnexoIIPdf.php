<?php
/**
 * Portal de Compras
 * Programa: RelPregaoPresencialAnexoIIPdf.php
 * Objetivo: Imprimir o Anexo II do Pregão Presencial
 * Autor:    Hélio Miranda
 * Data:     10/02/2017
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:	 26/06/2018
 * Objetivo: Tarefa Redmine 197164
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:	 26/06/2018
 * Objetivo: Tarefa Redmine 199906
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     07/05/2019
 * Objetivo: Tarefa Redmine 215450
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/08/2019
 * Objetivo: Tarefa Redmine 222074
 * ---------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Processo             = $_GET['Processo'];
	$ProcessoAno          = $_GET['Ano'];
	$ComissaoCodigo       = $_GET['Comissao'];
	$OrgaoLicitanteCodigo = $_GET['Orgao'];
	$IntervaloLotes       = $_GET['IntervaloLotes'];		
}

# Identifica o programa para erro de banco de dados #
$ErroPrograma = __FILE__;

# Fução exibe o cabeçalho e o rodapé #
CabecalhoRodapePaisagem();

# Informa o título do relatório #
$TituloRelatorio = "Anexo II: Histórico de Lances por Lote";

# Cria o objeto PDF, o default é formato retrato, A4 e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Conecta no banco de dados #
$db = Conexao();

$sql  = "SELECT	LIC.CCOMLICODI, COM.ECOMLIDESC, LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CMODLICODI, MOD.EMODLIDESC, ";
$sql .= "		LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, LIC.CORGLICODI, LIC.FLICPOVFOR, ";
$sql .= "		OL.EORGLIDESC ";
$sql .= "FROM    SFPC.TBLICITACAOPORTAL LIC ";
$sql .= "		INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI ";
$sql .= "		INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI ";
$sql .= "		INNER JOIN SFPC.TBORGAOLICITANTE OL ON LIC.CORGLICODI = OL.CORGLICODI ";
$sql .= "WHERE   LIC.CLICPOPROC = " . $Processo;
$sql .= "		AND LIC.ALICPOANOP = " . $ProcessoAno;
$sql .= "		AND LIC.CCOMLICODI = " . $ComissaoCodigo;
$sql .= "		AND LIC.CORGLICODI = " . $OrgaoLicitanteCodigo;
$sql .= " ORDER BY LIC.CCOMLICODI ASC ";

$res = $db->query($sql);

if (PEAR::isError($res)) {
  $CodErroEmail  = $res->getCode();
  $DescErroEmail = $res->getMessage();
  
  var_export($DescErroEmail);
  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}

$Linha = $res->fetchRow();
$_SESSION['CodigoComissao']   = $Linha[0];
$_SESSION['NomeComissao'] 	  = $Linha[1];
$_SESSION['NumeroDoProcesso'] = $Linha[2];
$_SESSION['AnoDoExercicio']   = $Linha[3];
$_SESSION['Modalidade'] 	  = $Linha[5]; 
$_SESSION['RegistroPreco'] 	  = $Linha[6];
$_SESSION['Licitação'] 		  = $Linha[7];
$_SESSION['AnoLicitação'] 	  = $Linha[8];
$_SESSION['Objeto'] 		  = $Linha[9];
$_SESSION['OrgaoDemandante']  = $Linha[12];

$Comissao 		 = $_SESSION['NomeComissao'];
$Processo 		 = substr($_SESSION['NumeroDoProcesso'] + 10000,1);
$AnoDoProcesso 	 = $_SESSION['AnoDoExercicio'];
$Modalidade 	 = $_SESSION['Modalidade'];
$RegistroDePreco = $_SESSION['RegistroPreco'] == 'S' ? "SIM" : "NÃO";
$Licitacao 		 = substr($_SESSION['Licitação'] + 10000,1);
$AnoDaLicitacao  = $_SESSION['AnoLicitação'];
$Objeto 		 = $_SESSION['Objeto'];
$OrgaoDemandante = $_SESSION['OrgaoDemandante'];

$sqlSolicitacoes = "SELECT  CPREGASEQU, FPREGATIPO
					FROM 	SFPC.TBPREGAOPRESENCIAL PP 
					WHERE 	PP.CLICPOPROC     = $Processo 
							AND PP.ALICPOANOP = $ProcessoAno
							AND PP.CCOMLICODI = $ComissaoCodigo 
							AND PP.CORGLICODI = $OrgaoLicitanteCodigo 
							AND PP.CGREMPCODI = ". $_SESSION['_cgrempcodi_'];

$result = $db->query($sqlSolicitacoes);

if (PEAR::isError($result)) {
	$CodErroEmail  = $result->getCode();
	$DescErroEmail = $result->getMessage();
	
	var_export($DescErroEmail);
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes\n\n$DescErroEmail ($CodErroEmail)");
}

$Linha = $result->fetchRow();

$_SESSION['PregaoCod'] 	= $Linha[0];
$_SESSION['PregaoTipo'] = $Linha[1];

$TipoDePregao = $_SESSION['PregaoTipo'] == 'N' ? "MENOR PREÇO" : "MAIOR OFERTA";
$PregaoCod 	  = $_SESSION['PregaoCod'];

$pdf->Cell(40,5,"Comissão:",1,0,"L",1);
$pdf->Cell(240,5,$Comissao,1,1,"L",0);
$pdf->Cell(40,5,"Processo:",1,0,"L",1);
$pdf->Cell(240,5,$Processo,1,1,"L",0);
$pdf->Cell(40,5,"Ano do Processo:",1,0,"L",1);
$pdf->Cell(240,5,$AnoDoProcesso,1,1,"L",0);
$pdf->Cell(40,5,"Modalidade:",1,0,"L",1);
$pdf->Cell(240,5,$Modalidade,1,1,"L",0);
$pdf->Cell(40,5,"Registro de Preço:",1,0,"L",1);
$pdf->Cell(240,5,$RegistroDePreco,1,1,"L",0);
$pdf->Cell(40,5,"Licitação:",1,0,"L",1);
$pdf->Cell(240,5,$Licitacao,1,1,"L",0);
$pdf->Cell(40,5,"Ano da Licitação:",1,0,"L",1);
$pdf->Cell(240,5,$AnoDaLicitacao,1,1,"L",0);

$breaks = array("<br />", "<br>", "<br/>");

$h1 = 5;
$h1 = $pdf->GetStringHeight(240, 5, trim(str_ireplace($breaks, '\r\n', $Objeto)), 'L');

if ($h1 < 5) {
	$h1 = 5;
}

$pdf->Cell(40,$h1,"Objeto:",1,0,"L",1);
$pdf->MultiCell(240,5,$Objeto,1,1,"L",0);
$pdf->Cell(40,5,"Órgão Demandante:",1,0,"L",1);
$pdf->Cell(240,5,$OrgaoDemandante,1,1,"L",0);
$pdf->Cell(40,5,"Tipo de Licitação:",1,0,"L",1);
$pdf->Cell(240,5,$TipoDePregao,1,1,"L",0);

$sql  = "SELECT	DISTINCT PL.CPREGTNUML, PL.CPREGTSEQU, PL.EPREGTDESC ";
$sql .= "FROM 	SFPC.TBPREGAOPRESENCIALLOTE PL ";
$sql .= "WHERE 	PL.CPREGASEQU = " . $PregaoCod;

if ($IntervaloLotes == True) {
	$sql .= "AND PL.CPREGTNUML BETWEEN " . $_SESSION['LoteInicialIntervalo'] . " AND " . $_SESSION['LoteFinalIntervalo'];
}

$sql .= " ORDER BY PL.CPREGTNUML ";

$resultLote = $db->query($sql);

if (PEAR::isError($resulresultLotet)) {
	$CodErroEmail  = $resultLote->getCode();
	$DescErroEmail = $resultLote->getMessage();

	var_export($DescErroEmail);
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}

$LinhaLote 		 = $resultLote->fetchRow();
$QuantidadeLotes = $resultLote->numRows();

$LimitMaxColumns = 4;
$CurrentRound	 = 1;

for ($itrL = 0; $itrL < $QuantidadeLotes; ++$itrL) {
	do {
		if ($CurrentRound <= 1) {
			$CurrentLotID = $LinhaLote[1];

			$sql  = "SELECT	MAX(PL.CPREGLNUMR) ";
			$sql .= "FROM 	SFPC.TBPREGAOPRESENCIALLANCE PL ";
			$sql .= "WHERE 	PL.CPREGTSEQU = " . $CurrentLotID;

			$ResultLance = $db->query($sql);

			if (PEAR::isError($ResultLance)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}

			$LinhaLance   = $ResultLance->fetchRow();
			$TotalRounds  = $LinhaLance[0];
			$CurrentRound = $TotalRounds; // pq isso?
		}

		$pdf->ln(5);

		if ($CurrentRound == $TotalRounds) {
			$pdf->SetFont("Arial","B",8);
		} else {
			$pdf->SetFont("Arial","",8);
		}

		$pdf->Cell(20,5,"LOTE: ".$LinhaLote[0],1,0,"L",1);
		$pdf->MultiCell(260,5,$LinhaLote[2],1,1,"L",1);
		$pdf->SetFont("Arial","",8);
		$pdf->Cell(20,5,"Ordem",1,0,"C",1);
		$pdf->Cell(30,5,"CNPJ/CPF",1,0,"C",1);
		$pdf->Cell(110,5,"Fornecedor",1,0,"C",1);

		if ($TotalRounds == 0 || $TotalRounds == '') {
			$TotalRounds = 1;
		}

		if ($CurrentRound == 0 || $CurrentRound == '') {
			$CurrentRound = 1;
		}

		if (($TotalRounds - $CurrentRound > 0) and ($CurrentRound < $LimitMaxColumns)) {
			$ColumnSize	= (120 / $CurrentRound);
			$LimitColumns = $CurrentRound;
		} else {
			$ColumnSize	= (120 / $LimitMaxColumns);
			$LimitColumns = $LimitMaxColumns;
		}

		if ($TotalRounds < $LimitMaxColumns) {
			$ColumnSize	= (120 / $TotalRounds);
			$LimitColumns = $TotalRounds;
		}

		for ($itrR = 1; $itrR <= $LimitColumns; ++$itrR) {
			//var_dump($itrR);
			//var_dump((int) $LimitColumns);
			if ($itrR == (int) $LimitColumns) {
				$pdf->Cell($ColumnSize,5,$CurrentRound."ª Rodada",1,1,"C",1);
				$CurrentRound = $CurrentRound - 1;
			} else {
				$pdf->Cell($ColumnSize,5,$CurrentRound."ª Rodada",1,0,"C",1);
				$CurrentRound = $CurrentRound - 1;
			}
		}

		if (($TotalRounds - $CurrentRound <= $LimitMaxColumns) or ($TotalRounds <= $LimitMaxColumns)) {
			$CurrentBidCounter = $TotalRounds;
		} else {
			$CurrentBidCounter = $CurrentBidCounter - $LimitColumns;

			if ($CurrentBidCounter == 0) {
				$CurrentBidCounter = $LimitColumns;
			}
		}

		$CodLoteSelecionado = $LinhaLote[1];

		$sqlMinMax  = "SELECT	MIN(PI.VPREGPVALI), MAX(PI.VPREGPVALI) ";
		$sqlMinMax .= "FROM 	SFPC.TBPREGAOPRESENCIALFORNECEDOR FN, ";
		$sqlMinMax .= "		SFPC.TBPREGAOPRESENCIALCLASSIFICACAO CL, ";
		$sqlMinMax .= "			SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR SF, ";
		$sqlMinMax .= "		SFPC.TBPREGAOPRESENCIALLOTE LT, ";
		$sqlMinMax .= "		SFPC.TBPREGAOPRESENCIALPRECOINICIAL PI ";
		$sqlMinMax .= "WHERE	LT.CPREGTSEQU = " . $CodLoteSelecionado;
		$sqlMinMax .= "		AND	SF.CPRESFSEQU = 1 ";
		$sqlMinMax .= "		AND	FN.CPREGFSEQU = CL.CPREGFSEQU ";
		$sqlMinMax .= "		AND	LT.CPREGTSEQU = CL.CPREGTSEQU ";
		$sqlMinMax .= "		AND	SF.CPRESFSEQU = CL.CPRESFSEQU ";
		$sqlMinMax .= "		AND	CL.CPREGFSEQU = PI.CPREGFSEQU ";
		$sqlMinMax .= "		AND	CL.CPREGTSEQU = PI.CPREGTSEQU ";
		$sqlMinMax .= "		AND	PI.VPREGPVALI > 0 ";

		$resultMinMax = $db->query($sqlMinMax);	
		$LinhaMinMax = $resultMinMax->fetchRow();

		if ($_SESSION['PregaoTipo'] == 'N') {
			$tipoOrdenacao = "ASC";
		} else {
			$tipoOrdenacao = "DESC";
		}

		# Verificando se existe licitações ligadas ao processo, para ver qual programa devo chamar
		$sqlFornecedores  = "SELECT	FN.APREGFCCGC, FN.APREGFCCPF, FN.NPREGFRAZS, FN.NPREGFNOMR, FN.APREGFNURG, ";
		$sqlFornecedores .= "		FN.EPREGFSITU, FN.CPREGFSEQU, NPREGFORGU, SF.EPRESFNOME, SF.CPRESFSEQU, LT.CPREGTSEQU, ";
		$sqlFornecedores .= "		PI.VPREGPVALI, PI.CPREGPSEQU, FN.NPREGFNOMR, FN.FPREGFMEPP, PI.FPREGPALAN, CPREGPOEMP ";
		$sqlFornecedores .= "FROM 	SFPC.TBPREGAOPRESENCIALFORNECEDOR FN, ";
		$sqlFornecedores .= "		SFPC.TBPREGAOPRESENCIALCLASSIFICACAO CL, ";
		$sqlFornecedores .= "		SFPC.TBPREGAOPRESENCIALSITUACAOFORNECEDOR SF, ";
		$sqlFornecedores .= "		SFPC.TBPREGAOPRESENCIALLOTE LT, ";
		$sqlFornecedores .= "		SFPC.TBPREGAOPRESENCIALPRECOINICIAL PI ";
		$sqlFornecedores .= "WHERE	LT.CPREGTSEQU = " . $CodLoteSelecionado;
		$sqlFornecedores .= "		AND FN.CPREGFSEQU = CL.CPREGFSEQU ";
		$sqlFornecedores .= "		AND	LT.CPREGTSEQU = CL.CPREGTSEQU ";
		$sqlFornecedores .= "		AND SF.CPRESFSEQU = CL.CPRESFSEQU ";
		$sqlFornecedores .= "		AND CL.CPREGFSEQU = PI.CPREGFSEQU ";
		$sqlFornecedores .= "		AND	CL.CPREGTSEQU = PI.CPREGTSEQU ";
		$sqlFornecedores .= "ORDER BY CASE	WHEN (SF.CPRESFSEQU = 1) THEN 0 ";
		$sqlFornecedores .= "				WHEN (SF.CPRESFSEQU <> 1) THEN 1 ";
		$sqlFornecedores .= "				ELSE 2 ";
		$sqlFornecedores .= "		 END, ";
		$sqlFornecedores .= "		 CASE	WHEN (PI.VPREGPVALI > 0) THEN 0 ";
		$sqlFornecedores .= "				WHEN (PI.VPREGPVALI <= 0) THEN 1 ";
		$sqlFornecedores .= "				ELSE 2 ";
		$sqlFornecedores .= "		 END, ";
		$sqlFornecedores .= "		 PI.VPREGPVALI " . $TIPOORDENACAO . ", PI.CPREGPOEMP ASC, FN.NPREGFRAZS ASC, FN.NPREGFNOMR ASC ";

		$resultFornecedores = $db->query($sqlFornecedores);

		if (PEAR::isError($resultFornecedores)) {
			ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
		}

		$ValorReferencia = 0;

		$LinhaPrecoInicial = $resultFornecedores->fetchRow();

		$QuantidadeFornecedores = 0;
		$QuantidadeFornecedores = $resultFornecedores->numRows();

		if ($_SESSION['PregaoTipo'] == 'N') {
			$ValorReferencia = $LinhaMinMax[0];
		} else {
			$ValorReferencia = $LinhaMinMax[1];
		}

		$Qtd = $resultFornecedores->numRows();

		if ($LimitColumns == '') {
			$LimitColumns = 1;
		}
		//var_dump($CurrentBidCounter);
		//var_dump($LimitColumns);
		if ($LimitColumns < 4) {
			$LimitBidStart = ($CurrentBidCounter - $LimitColumns) - $LimitColumns;
		//	$CurrentBidCounter = $CurrentBidCounter - $LimitColumns;
			//var_dump($CurrentBidCounter);
			//var_dump($LimitColumns);
			$LimitBidStart = $CurrentBidCounter - $LimitColumns;
		} else {
			$LimitBidStart = $CurrentBidCounter - $LimitColumns;
		}

		if ($CurrentBidCounter == '') {
			$CurrentBidCounter = 1;
		}

		if ($Qtd > 0) {
			for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr) {
				$Ordem   = ($itr + 1);
				$CnpjCpf = ($LinhaPrecoInicial[1] == "" 
					?	(substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2)) 
					:	(substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));						
				$Fornecedor	= $LinhaPrecoInicial[2];

				if ($LinhaPrecoInicial[11] > 0) {
					if ($ValorReferencia > 0) {
						if ($_SESSION['PregaoTipo'] == 'N') {
							$Percentual = (($LinhaPrecoInicial[11] - $ValorReferencia) / $ValorReferencia) * 100;
							$Percentual = number_format($Percentual, 3, ',', '');
						} else {
							$Percentual = (($ValorReferencia - $LinhaPrecoInicial[11]) / $ValorReferencia) * 100;
						}
					}
				}

				$Percentual   = (($LinhaPrecoInicial[11] > 0 and $ValorReferencia > 0)  ? $Percentual."%" : "-");
				$PrecoInicial = ($LinhaPrecoInicial[11] > 0 ?  number_format($LinhaPrecoInicial[11], 4, ',', '.')  : "NÃO COTOU");
				$Situacao     = $LinhaPrecoInicial[8];
				$Apto         = ($LinhaPrecoInicial[15] == 1 ? "SIM" : "NÃO");

				$pdf->Cell(20,5,$Ordem,1,0,"C",0);
				$pdf->Cell(30,5,$CnpjCpf,1,0,"C",0);
				$pdf->Cell(110,5,$Fornecedor,1,0,"C",0);
				//var_dump(-$LimitBidStart);
				//var_dump($CurrentBidCounter);
				$sqlLancesAnteriores  = "SELECT	VPREGLVALL, FPREGLLVEN, CPREGLNUMR ";
				$sqlLancesAnteriores .= "FROM 	SFPC.TBPREGAOPRESENCIALLANCE ";
				$sqlLancesAnteriores .= "WHERE 	CPREGLNUMR BETWEEN " . $LimitBidStart . " AND " . $CurrentBidCounter;
				$sqlLancesAnteriores .= "		AND CPREGTSEQU = " . $CodLoteSelecionado;
				$sqlLancesAnteriores .= "		AND	CPREGFSEQU = " . $LinhaPrecoInicial[6];
				$sqlLancesAnteriores .= "ORDER BY CPREGLNUMR DESC ";
				$sqlLancesAnteriores .= "LIMIT " . $LimitColumns;

				$resultLancesAnteriores = $db->query($sqlLancesAnteriores);

				if (PEAR::isError($resultLancesAnteriores)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLancesAnteriores");
				} else {
					$LinhaLancesAnteriores = $resultLancesAnteriores->fetchRow();
				}

				for ($itrD = 1; $itrD <= $LimitColumns; ++ $itrD) {
					$CurrentBid = (($LinhaLancesAnteriores[0] > 0) ? (number_format($LinhaLancesAnteriores[0], 4, ',', '.')) : ("S/L"));

					if ($itrD >= $LimitColumns) {
						$pdf->Cell($ColumnSize,5,$CurrentBid,1,1,"C",0);
					} else {
						$pdf->Cell($ColumnSize,5,$CurrentBid,1,0,"C",0);
					}

					$LinhaLancesAnteriores = $resultLancesAnteriores->fetchRow();
				}

				$LinhaPrecoInicial = $resultFornecedores->fetchRow();
			}
		}

		$pdf->ln(5);
	}
	
	while ($CurrentRound > 1);

	if ($CurrentRound <= 1) {
		$LinhaLote 	= $resultLote->fetchRow();
	}
	//exit;
}

$db->disconnect();
$pdf->Output();
?>