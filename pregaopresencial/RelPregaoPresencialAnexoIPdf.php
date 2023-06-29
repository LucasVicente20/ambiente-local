<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelPregaoPresencialAnexoIPdf.php
# Objetivo: Imprimir o Anexo I do Pregão Presencial
# Autor:    Hélio Miranda
# Data:     09/02/2017
#---------------------------------------------------------------------------------------
# Autor:	Lucas Baracho
# Data:		26/06/2018
# Objetivo:	Tarefa Redmine 197164
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Processo        			= $_GET['Processo'];
		$ProcessoAno          		= $_GET['Ano'];
		$ComissaoCodigo        		= $_GET['Comissao'];
		$OrgaoLicitanteCodigo       = $_GET['Orgao'];
		$IntervaloLotes  			= $_GET['IntervaloLotes'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Anexo I: Preços iniciais de cada Fornecedor por Lote";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
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

$sql  = "	SELECT 		LIC.CCOMLICODI, COM.ECOMLIDESC, LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CMODLICODI, MOD.EMODLIDESC,
						LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, LIC.CORGLICODI, LIC.FLICPOVFOR,
						OL.EORGLIDESC
			FROM    	SFPC.TBLICITACAOPORTAL LIC
						INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI
						INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI
						INNER JOIN SFPC.TBORGAOLICITANTE OL ON LIC.CORGLICODI = OL.CORGLICODI
			WHERE   	LIC.CLICPOPROC 		= $Processo 
						AND LIC.ALICPOANOP 	= $ProcessoAno
						AND LIC.CCOMLICODI 	= $ComissaoCodigo 
						AND LIC.corglicodi 	= $OrgaoLicitanteCodigo
			ORDER BY 	LIC.CCOMLICODI ASC";

$res = $db->query($sql);

if ( PEAR::isError($res) ) {
  $CodErroEmail  = $res->getCode();
  $DescErroEmail = $res->getMessage();
  var_export($DescErroEmail);
  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}

$Linha = $res->fetchRow();
  
$_SESSION['CodigoComissao'] 	= $Linha[0];
$_SESSION['NomeComissao'] 		= $Linha[1];
$_SESSION['NumeroDoProcesso'] 	= $Linha[2];
$_SESSION['AnoDoExercicio'] 	= $Linha[3];
$_SESSION['Modalidade'] 		= $Linha[5]; 
$_SESSION['RegistroPreco'] 		= $Linha[6];
$_SESSION['Licitação'] 			= $Linha[7];
$_SESSION['AnoLicitação'] 		= $Linha[8];
$_SESSION['Objeto'] 			= $Linha[9];
$_SESSION['OrgaoDemandante']	= $Linha[12];


$Comissao 			= $_SESSION['NomeComissao'];
$Processo 			= substr($_SESSION['NumeroDoProcesso'] + 10000,1);
$AnoDoProcesso 		= $_SESSION['AnoDoExercicio'];
$Modalidade 		= $_SESSION['Modalidade'];
$RegistroDePreco 	= $_SESSION['RegistroPreco'] == 'S' ? "SIM" : "NÃO";
$Licitacao 			= substr($_SESSION['Licitação'] + 10000,1);
$AnoDaLicitacao 	= $_SESSION['AnoLicitação'];
$Objeto 			= $_SESSION['Objeto'];
$OrgaoDemandante	= $_SESSION['OrgaoDemandante'];

$sqlSolicitacoes = "SELECT  cpregasequ, fpregatipo
					FROM 	sfpc.tbpregaopresencial pp 
					WHERE 	pp.clicpoproc  = $Processo 
						AND pp.alicpoanop  = $ProcessoAno
						AND pp.ccomlicodi  = $ComissaoCodigo 
						AND pp.corglicodi  = $OrgaoLicitanteCodigo 
						AND pp.cgrempcodi  =". $_SESSION['_cgrempcodi_']; 
	
	

$result= $db->query($sqlSolicitacoes);
$Linha = $result->fetchRow();

$_SESSION['PregaoCod'] 	= $Linha[0];
$_SESSION['PregaoTipo'] = $Linha[1];

$TipoDePregao 		= $_SESSION['PregaoTipo'] == 'N' ? "MENOR PREÇO" : "MAIOR OFERTA";
$PregaoCod 			= $_SESSION['PregaoCod'];


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
$pdf->MultiCell(240,5,$Objeto,1,"L",0);
$pdf->Cell(40,5,"Órgão demandante:",1,0,"L",1);
$pdf->Cell(240,5,$OrgaoDemandante,1,1,"L",0);
$pdf->Cell(40,5,"Tipo de Licitação:",1,0,"L",1);
$pdf->Cell(240,5,$TipoDePregao,1,1,"L",0);

$sql    = "SELECT DISTINCT 	pl.cpregtnuml, pl.cpregtsequ, pl.epregtdesc ";
$sql   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
$sql   .= "  WHERE 			pl.cpregasequ = ".$PregaoCod." ";

if($IntervaloLotes == True)
{
	$sql   .= "  AND pl.cpregtnuml BETWEEN ".$_SESSION['LoteInicialIntervalo']." AND ".$_SESSION['LoteFinalIntervalo'];
}

$sql   .= "  ORDER BY 		pl.cpregtnuml";

$resultLote = $db->query($sql);
if( PEAR::isError($resultLote) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

$LinhaLote 			= $resultLote->fetchRow();
$QuantidadeLotes 	= $resultLote->numRows();


for($itrL = 0; $itrL < $QuantidadeLotes; ++ $itrL)
{	
	$pdf->ln(5);
	$pdf->SetFont("Arial","",8);
	$pdf->Cell(20,5,"LOTE: ".$LinhaLote[0],1,0,"L",1);
	$pdf->MultiCell(260,5,$LinhaLote[2],1,1,"L",1);
	$pdf->Cell(10,5,"Ordem",1,0,"C",1);
	$pdf->Cell(30,5,"CNPJ/CPF",1,0,"C",1);
	$pdf->Cell(118,5,"Fornecedor",1,0,"C",1);
	$pdf->Cell(17,5,"(%)",1,0,"C",1);
	$pdf->Cell(30,5,"Preço Inicial",1,0,"C",1);
	$pdf->Cell(55,5,"Situação",1,0,"C",1);
	$pdf->Cell(20,5,"Apto (Lances)",1,1,"C",1);
	
	$CodLoteSelecionado = $LinhaLote[1];
	
	$sqlMinMax = "SELECT		MIN(pi.vpregpvali), MAX(pi.vpregpvali)
						FROM 		sfpc.tbpregaopresencialfornecedor fn,
									sfpc.tbpregaopresencialclassificacao cl,
									sfpc.tbpregaopresencialsituacaofornecedor sf,
									sfpc.tbpregaopresenciallote lt,
									sfpc.tbpregaopresencialprecoinicial pi
						WHERE		lt.cpregtsequ  = $CodLoteSelecionado
							AND 	sf.cpresfsequ  = 1
							AND 	fn.cpregfsequ  = cl.cpregfsequ
							AND		lt.cpregtsequ  = cl.cpregtsequ
							AND 	sf.cpresfsequ  = cl.cpresfsequ
							AND 	cl.cpregfsequ  = pi.cpregfsequ
							AND		cl.cpregtsequ  = pi.cpregtsequ
							AND		pi.vpregpvali > 0"; 
							
	$resultMinMax = $db->query($sqlMinMax);	
	$LinhaMinMax = $resultMinMax->fetchRow();

	if($_SESSION['PregaoTipo'] == 'N')
	{
		$tipoOrdenacao = "ASC";
	}
	else
	{
		$tipoOrdenacao = "DESC";
	}										

	//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
	$sqlFornecedores = "SELECT		fn.apregfccgc, fn.apregfccpf, fn.npregfrazs, fn.npregfnomr, fn.apregfnurg, 
									fn.epregfsitu, fn.cpregfsequ, npregforgu, sf.epresfnome, sf.cpresfsequ, lt.cpregtsequ,
									pi.vpregpvali, pi.cpregpsequ, fn.npregfnomr, fn.fpregfmepp, pi.fpregpalan, cpregpoemp
						FROM 		sfpc.tbpregaopresencialfornecedor fn,
									sfpc.tbpregaopresencialclassificacao cl,
									sfpc.tbpregaopresencialsituacaofornecedor sf,
									sfpc.tbpregaopresenciallote lt,
									sfpc.tbpregaopresencialprecoinicial pi
						WHERE		lt.cpregtsequ  = $CodLoteSelecionado
							AND 	fn.cpregfsequ  = cl.cpregfsequ
							AND		lt.cpregtsequ  = cl.cpregtsequ
							AND 	sf.cpresfsequ  = cl.cpresfsequ
							AND 	cl.cpregfsequ  = pi.cpregfsequ
							AND		cl.cpregtsequ  = pi.cpregtsequ																
						ORDER BY	CASE WHEN(sf.cpresfsequ = 1) THEN  0
										 WHEN(sf.cpresfsequ <> 1) THEN  1
										 ELSE 2
									END,
									CASE WHEN(pi.vpregpvali > 0) THEN  0
										 WHEN(pi.vpregpvali <= 0) THEN  1
										 ELSE 2
									END,
									pi.vpregpvali $tipoOrdenacao, pi.cpregpoemp ASC, fn.npregfrazs ASC,
									fn.npregfnomr ASC"; 
		
		
	$resultFornecedores = $db->query($sqlFornecedores);

	if( PEAR::isError($resultFornecedores) ){
		ExibeErroBD("$ErroPrograma\nLinhaPrecoInicial: ".__LINE__."\nSql: $sqlSolicitacoes");
	}

	$ValorReferencia = 0;

	$LinhaPrecoInicial = $resultFornecedores->fetchRow();

	$QuantidadeFornecedores = 0;

	$QuantidadeFornecedores = $resultFornecedores->numRows();	

	if($_SESSION['PregaoTipo'] == 'N')
	{
		$ValorReferencia = $LinhaMinMax[0];
	}
	else
	{
		$ValorReferencia = $LinhaMinMax[1];
	}

	$Qtd = $resultFornecedores->numRows();
	if($Qtd > 0)
	{
		for ($itr = 0; $itr < $QuantidadeFornecedores; ++ $itr)
		{
				$Ordem     		= ($itr + 1);
				
				$CnpjCpf      	= ($LinhaPrecoInicial[1] == "" 
									?
									(substr($LinhaPrecoInicial[0], 0, 2).'.'.substr($LinhaPrecoInicial[0], 2, 3).'.'.substr($LinhaPrecoInicial[0], 5, 3).'/'.substr($LinhaPrecoInicial[0], 8, 4).'-'.substr($LinhaPrecoInicial[0], 12, 2)) 
									: 
									(substr($LinhaPrecoInicial[1], 0, 3).'.'.substr($LinhaPrecoInicial[1], 3, 3).'.'.substr($LinhaPrecoInicial[1], 6, 3).'-'.substr($LinhaPrecoInicial[1], 9, 2)));
									
				$Fornecedor   	= $LinhaPrecoInicial[2];
				
				if($LinhaPrecoInicial[11] > 0)
				{
					if($ValorReferencia > 0)
					{
						if($_SESSION['PregaoTipo'] == 'N')
						{
							$Percentual = (($LinhaPrecoInicial[11] - $ValorReferencia) / $ValorReferencia) * 100;
							
							$Percentual = number_format($Percentual, 3, ',', '');
						}
						else
						{
							$Percentual = (($ValorReferencia - $LinhaPrecoInicial[11]) / $ValorReferencia) * 100;
						}
					}																			
				}
				$Percentual   	= (($LinhaPrecoInicial[11] > 0 and $ValorReferencia > 0)  ? $Percentual."%" : "-");
				
				$PrecoInicial 	= ($LinhaPrecoInicial[11] > 0 ?  number_format($LinhaPrecoInicial[11], 4, ',', '.')  : "NÃO COTOU");
				
				$Situacao     	= $LinhaPrecoInicial[8];
				
				$Apto      	  	= ($LinhaPrecoInicial[15] == 1 ? "SIM" : "NÃO");
				
				$pdf->Cell(10,5,$Ordem,1,0,"C",0);
				$pdf->Cell(30,5,$CnpjCpf,1,0,"C",0);
				$pdf->Cell(118,5,substr($Fornecedor, 0, 67),1,0,"L",0);
				$pdf->Cell(17,5,$Percentual,1,0,"C",0);
				$pdf->Cell(30,5,$PrecoInicial,1,0,"C",0);
				$pdf->Cell(55,5,substr($Situacao, 0, 33),1,0,"C",0);
				$pdf->Cell(20,5,$Apto,1,1,"C",0);

				$LinhaPrecoInicial = $resultFornecedores->fetchRow();				
		}
	}
	
	$pdf->ln(5);
	
	$LinhaLote 	= $resultLote->fetchRow();
}
$db->disconnect();
$pdf->Output();

?>
