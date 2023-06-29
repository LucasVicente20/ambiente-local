<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelClassificacaoFinalPregaoPresencialPdf.php
# Objetivo: Imprimir o Anexo III do Pregão Presencial
# Autor:    Hélio Miranda
# Data:     14/02/2017
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
$TituloRelatorio = "Classificação Final (Resumo)";

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
						LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, LIC.CORGLICODI, LIC.flicpovfor
			FROM    	SFPC.TBLICITACAOPORTAL LIC
						INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI
						INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI
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


$Comissao 			= $_SESSION['NomeComissao'];
$Processo 			= substr($_SESSION['NumeroDoProcesso'] + 10000,1);
$AnoDoProcesso 		= $_SESSION['AnoDoExercicio'];
$Modalidade 		= $_SESSION['Modalidade'];
$RegistroDePreco 	= $_SESSION['RegistroPreco'] == 'S' ? "SIM" : "NÃO";
$Licitacao 			= substr($_SESSION['Licitação'] + 10000,1);
$AnoDaLicitacao 	= $_SESSION['AnoLicitação'];
$Objeto 			= $_SESSION['Objeto'];

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
$pdf->Cell(40,5,"Objeto:",1,0,"L",1);
$pdf->MultiCell(240,5,$Objeto,1,1,"L",0);
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

$ValorTotalEstimadoLotes 	= 0;
$ValorTotalLogradoLotes 	= 0;
$EconomicidadeTotalLotes 	= 0;

for($itrL = 0; $itrL < $QuantidadeLotes; ++ $itrL)
{	
	$pdf->ln(5);
	$pdf->SetFont("Arial","",8);
	$pdf->Cell(40,5,"LOTE: ".$LinhaLote[0],1,0,"L",1);
	$pdf->MultiCell(240,5,$LinhaLote[2],1,1,"L",1);
	$pdf->Cell(40,5,"CNPJ/CPF",1,0,"C",1);
	$pdf->Cell(140,5,"Fornecedor Vencedor",1,0,"C",1);
	$pdf->Cell(35,5,"Valor Estimado",1,0,"C",1);
	$pdf->Cell(35,5,"Valor Logrado",1,0,"C",1);
	$pdf->Cell(30,5,"% Economicidade",1,1,"C",1);
	
	$NumLoteSelecionado = $LinhaLote[0];
	$CodLoteSelecionado = $LinhaLote[1];
	
	//Valor Estimado Lote - início
	
	//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
	
	$sqlSolicitacoes = "SELECT		SUM(a.aitelpqtso * a.vitelpunit) as valor
						FROM 		sfpc.tbitemlicitacaoportal a 
										left outer join 
									sfpc.tbmaterialportal b on a.cmatepsequ 	= b.cmatepsequ 
										left outer join 
									sfpc.tbservicoportal c on a.cservpsequ 		= c.cservpsequ
										left outer join 
									sfpc.tbunidadedemedida d on b.cunidmcodi 	= d.cunidmcodi
						WHERE		a.clicpoproc = $Processo 
							AND   	a.alicpoanop = $ProcessoAno
							AND  	a.ccomlicodi = $ComissaoCodigo
							AND   	a.corglicodi = $OrgaoLicitanteCodigo
							AND   	a.cgrempcodi =".$_SESSION['_cgrempcodi_']."
							AND 	a.citelpnuml = $NumLoteSelecionado"; 	
	

	$resultItens = $db->query($sqlSolicitacoes);

	if( PEAR::isError($resultItens) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
	}
	
	$LinhaItens = $resultItens->fetchRow();
	$ValorTotalEstimado = number_format($LinhaItens[0], 4, ',', '.');
	
	//Valor Estimado Lote - fim
	
	
	$sql = "SELECT		pf.npregfrazs, pf.apregfccgc, pf.apregfccpf, pl.vpregtvalv
				FROM 	sfpc.tbpregaopresenciallote			pl,			
						sfpc.tbpregaopresencialfornecedor	pf
				WHERE 	pl.cpregtsequ  						= $CodLoteSelecionado 
					AND pl.cpregfsequ						= pf.cpregfsequ"; 
	
	$result = $db->query($sql);
	
	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
	}
	
	$LinhaVencedorLote = $result->fetchRow();
		

	$CnpjCpf      	= ($LinhaVencedorLote[2] == "" 
						?
						(substr($LinhaVencedorLote[1], 0, 2).'.'.substr($LinhaVencedorLote[1], 2, 3).'.'.substr($LinhaVencedorLote[1], 5, 3).'/'.substr($LinhaVencedorLote[1], 8, 4).'-'.substr($LinhaVencedorLote[1], 12, 2)) 
						: 
						(substr($LinhaVencedorLote[2], 0, 3).'.'.substr($LinhaVencedorLote[2], 3, 3).'.'.substr($LinhaVencedorLote[2], 6, 3).'-'.substr($LinhaVencedorLote[2], 9, 2)));
						
	$Fornecedor   	= $LinhaVencedorLote[0];

	$ValorDoLanceArrematante 	= ($LinhaVencedorLote[3] > 0 ?  number_format($LinhaVencedorLote[3], 4, ',', '.')  : "NÃO COTOU");	
	
	$Economicidade = number_format((((($ValorDoLanceArrematante / $ValorTotalEstimado) * 100) * -1) + 100), 4, ',', '.');
	
	$pdf->Cell(40,5,$CnpjCpf,1,0,"C",0);
	$pdf->Cell(140,5,$Fornecedor,1,0,"C",0);
	$pdf->Cell(35,5,$ValorTotalEstimado,1,0,"C",0);
	$pdf->Cell(35,5,$ValorDoLanceArrematante,1,0,"C",0);
	$pdf->Cell(30,5,$Economicidade,1,1,"C",0);
	
	$pdf->ln(5);
	
	$ValorTotalEstimadoLotes 	+= $LinhaItens[0];
	$ValorTotalLogradoLotes 	+= $LinhaVencedorLote[3];
	
	$LinhaLote 	= $resultLote->fetchRow();
}

$EconomicidadeTotalLotes 	= number_format((((($ValorTotalLogradoLotes / $ValorTotalEstimadoLotes) * 100) * -1) + 100), 4, ',', '.');	

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","B",10);
$pdf->ln(15);
$pdf->Cell(180,5,"Valor Total Estimado:",1,0,"L",1);
$pdf->Cell(100,5,number_format($ValorTotalEstimadoLotes, 4, ',', '.'),1,1,"L",0);
$pdf->Cell(180,5,"Valor Total Logrado:",1,0,"L",1);
$pdf->Cell(100,5,number_format($ValorTotalLogradoLotes, 4, ',', '.'),1,1,"L",0);
$pdf->Cell(180,5,"% de Economicidade Total:",1,0,"L",1);
$pdf->Cell(100,5,number_format($EconomicidadeTotalLotes, 4, ',', '.'),1,1,"L",0);


$db->disconnect();
$pdf->Output();

?>
