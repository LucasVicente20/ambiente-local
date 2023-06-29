<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelContratoVencerVencidoPdf.php
# Autor:    Edson Dionisio
# Data:     30/09/2020
# Objetivo: Programa que irá gerar o relatório em pdf
#			de contratos
# OBS.:     Tabulação 2 espaços
################################################
#-----------------------------------------------------------------------------

include "../funcoes.php";
require_once "ClassContratosFuncoesGerais.php";

$objFuncoesGerais = new ContratosFuncoesGerais();
	
# Executa o controle de segurança #
ini_set("session.auto_start", 0);
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/contratos/RelContratoQuadroResumoPdf.php');

//die;
#função auxiliar
function max_cell($array1, $array2,$pdf){
	$array_temp = array();
	
	foreach($array1 as $key => $cell){
		$array_temp[] = ceil($pdf->GetStringWidth($cell)/$array2[$key]);
	}
	
	return max($array_temp);
}

function limitar($str, $limita = 100, $limpar = true){
    if($limpar = true){
        $str = strip_tags($str);
    }
    if(strlen($str) <= $limita){
        return $str;
    }
    $limita_str = substr($str, 0, $limita);
    $ultimo = strrpos($limita_str, ' ');
    return substr($limita_str, 0, $ultimo).'...';
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Orgao			= $_GET['Orgao'];
}

$arrayTirar  = array('.',',','-','/');

// $orgao_selecionado = $_POST['Orgao'];
if(empty($_SESSION['Orgao'])){
	$orgao_selecionado = $_POST['Orgao'];
	$_SESSION['Orgao'] = $orgao_selecionado;
}else{
	$orgao_selecionado = $_SESSION['Orgao']; 
}

if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Orgao			= $_POST['Orgao'];
}

if(empty($_SESSION['vigente-nvigente'])){
	$vigenteNvigente = $_POST['vigente-nvigente'];
	$_SESSION['vigente-nvigente'] = $vigenteNvigente;
}else{
	$vigenteNvigente = $_SESSION['vigente-nvigente'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelContratoQuadroResumoPdf.php";
	
//Inicio da criação das partes fixas dos pdf
# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatário #
$TituloRelatorio = "Relatório de Contratos - Quadro Resumo";
	
# Cria o objeto PDF, o Default o formato Retrato, A4  e a medida em milémetros #
$pdf = new PDF("L","mm","A4");
	
# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","B",16);
//final das partes fixas
$height_of_cell = 67; // mm
$page_height = 230; // mm (portrait letter)
$bottom_margin = 0; // mm

$db = Conexao();

if($_POST['cpf-cnpj'] == 'CNPJ'){
	$fornecedor = str_replace($arrayTirar,'',$_POST['cnpj']);
	$sql_forn = " and forn.aforcrccgc = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccgc = '" . $fornecedor ."'";
}else{
	$fornecedor = str_replace($arrayTirar,'',$_POST['cpf']);
	$sql_forn = " and forn.aforcrccpf = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccpf = '" . $fornecedor ."'";
}

$array_orgaos = explode(',', $orgao_selecionado);
// var_dump($array_orgaos);die;
$Orgao_sel = '';
if(count($array_orgaos) > 1){

	$Orgao_sel = "TODOS";
	$sql_orgao = " and orlic.CORGLICODI in (" . $orgao_selecionado.")";
	$orgaos_distintos = " DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ";
	// $sql_orgao = "";
}else{
	$Orgao = $array_orgaos;
	$Orgao_sel = $array_orgaos;
	
	$orgaos_distintos = " DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ";

	$sql_orgao = " and orlic.CORGLICODI = " . $orgao_selecionado;
}
// var_dump($sql_orgao);die;

// Variável para ser utilizada na query de registros vigentes, a qual será utilizada tanto para trazer resultados dos relatórios vigentes quanto não vigentes
$sql_apenas_vigentes = " and (CASE 
	WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg >= now())
	WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
		on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
		and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN (con.dctrpcfivg >= now() )
	WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg >= now() and 
		adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
		left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1))
		END )";

if($vigenteNvigente == 'nvigente'){
	// PAssou da data final especificada
	//soma
	$tipo = 'NÃO VIGENTES';
	$periodo = "NÃO";
	
	$retorno_qtd = ", CASE 
						WHEN (con.dctrpcfivg >= now() or adt.daditifivg >= now()) THEN count(DISTINCT con.cdocpcsequ) 
					END as vigentes ";

	$retorna_apenas_vencidos = " and (CASE 
	WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg < now())
	WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
		on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
		and doc2.ctidocsequ = 2) IS NULL THEN (con.dctrpcfivg < now())
	WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg < now() and 
		adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
		left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1))
	END )";

		
	$sql_periodo_novo = " and (CASE 
		WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg is not null)
		WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
			on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
			where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
			and doc2.ctidocsequ = 2) IS NULL THEN (con.dctrpcfivg is not null)
		WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg is not null and 
			adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
			left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
			where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2  and doc2.csitdcsequ = 1))
		END )";
	}else{
		$tipo = 'VIGENTES';
		
		$periodo = "SIM";
		$retorno_qtd = ", CASE 
							WHEN (con.dctrpcfivg >= now() or adt.daditifivg >= now()) THEN count(DISTINCT con.cdocpcsequ) 
						END as vigentes ";
		
		$sql_periodo_novo = " and (CASE 
		WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg >= now())
		WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
			on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
			where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
			and doc2.ctidocsequ = 2) IS NULL THEN (con.dctrpcfivg >= now())
		WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg >= now() and 
			adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
			left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
			where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2))
		END )";
	}


	$sql   = "SELECT $orgaos_distintos, adt.cdocpcseq1, (CASE WHEN con.ectrpcraza = '' THEN forn.nforcrrazs WHEN con.ectrpcraza != '' THEN con.ectrpcraza END) AS razao_social,  con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf,";
	$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, CC.ecenpodesc as orgaocontratante, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, orlic.eorglidesc, adt.aaditinuad, adt.daditifivg, CASE WHEN con.vctrpcsean IS NULL THEN (con.vctrpceant + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpceant IS NOT NULL THEN (con.vctrpcsean + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcglaa + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcvlor + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) END as soma_total, COALESCE(sum(med.vmedcovalm),0.000) as valor_total_medicao, COALESCE(sum(apost.vapostvtap),0.000) as valor_total_apostilamento, COALESCE(sum(adt.vaditivtad),0.000) as valor_total_aditivo, ";
	$sql  .= "CASE 
				WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
				on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
				where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
				and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN ( count(DISTINCT con.cdocpcsequ) )
				end as aditivo_cadastrado $retorno_qtd, con.cdocpcsequ ";
	$sql  .= "FROM sfpc.tbcontratosfpc CON inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) ";
	$sql  .= "inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) ";
	$sql  .= "left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
	$sql  .= "left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
	$sql  .= "left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
	$sql  .= "left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') ";
	$sql  .= "left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) ";
	$sql  .= "left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) ";
	$sql  .= " where doc.csitdcsequ = 1 "; //and doc.cfasedsequ = 1
	$sql  .= $sql_orgao; //(count($array_orgaos) == 1) ? $sql_orgao : '';
	$sql  .= $sql_periodo_novo;
	$sql  .= " group by con.cdocpcsequ, adt.cdocpcseq1, con.ectrpcraza, con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, ";
	$sql  .= "con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, ";
	$sql  .= "forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc, forn.aforcrccpf, ";
	$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, adt.aaditinuad, adt.daditifivg, dctrpcinex, dctrpcfiex, ";
	$sql  .= "CC.ecenpodesc, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, ";
	$sql  .= "CC.ccenpounid, orlic.eorglidesc, razao_social";

		// print_r($sql);die;
	$resteste  = $db->query($sql);
	if(db::isError($resteste) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{

		$RowsTeste = $resteste->numRows();
		for($i = 0; $i < $RowsTeste; $i++){
			$registros[] = $resteste->fetchRow();

			$QtdAtendidaTeste = $registros[0];
			if($QtdAtendidaTeste != 0){
				$FlagItemAtendido = 1;
			}
		}
	}

	// Query de registros vigentes, a mesma está sendo utilizada tanto para relatórios não vigentes quanto vigentes.
	// Ela é para apresentar a quantidade de registros no count

	$sql_vigentes   = "SELECT DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ, orlic.eorglidesc, count(DISTINCT con.cdocpcsequ)";
	// $sql_vigentes  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, CC.ecenpodesc as orgaocontratante, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, orlic.eorglidesc, adt.aaditinuad, adt.daditifivg, CASE WHEN con.vctrpcsean IS NULL THEN (con.vctrpceant + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpceant IS NOT NULL THEN (con.vctrpcsean + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcglaa + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcvlor + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) END as soma_total, COALESCE(sum(med.vmedcovalm),0.000) as valor_total_medicao, COALESCE(sum(apost.vapostvtap),0.000) as valor_total_apostilamento, COALESCE(sum(adt.vaditivtad),0.000) as valor_total_aditivo, ";
	// $sql_vigentes  .= "count(con.cdocpcsequ) ";
	$sql_vigentes  .= "FROM sfpc.tbcontratosfpc CON inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) ";
	$sql_vigentes  .= "inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) ";
	$sql_vigentes  .= "left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
	$sql_vigentes  .= "left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
	$sql_vigentes  .= "left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
	$sql_vigentes  .= "left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') ";
	$sql_vigentes  .= "left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) ";
	$sql_vigentes  .= "left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) ";
	$sql_vigentes  .= " where doc.csitdcsequ = 1 "; //
	$sql_vigentes  .= $sql_orgao; //(count($array_orgaos) == 1) ? $sql_orgao : '';
	$sql_vigentes  .= $sql_apenas_vigentes;
	$sql_vigentes  .= " group by con.cdocpcsequ, orlic.eorglidesc, adt.cdocpcseq1 ";
		
	$vigentes  = $db->query($sql_vigentes);

	if(db::isError($vigentes) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql_vigentes");
	}else{

		$RowsTeste_vigentes = $vigentes->numRows();
		for($j = 0; $j < $RowsTeste_vigentes; $j++){
			$registros_vigentes[] = $vigentes->fetchRow();
			// var_dump($registros_vigentes);
			$QtdAtendidaTeste = $registros_vigentes[0];
			if($QtdAtendidaTeste != 0){
				$FlagItemAtendido = 1;
			}
		}
		// die;
	}

	// ===============================================================================================================

if($vigenteNvigente == 'nvigente'){
	$sql_vencidos   = "SELECT DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ, orlic.eorglidesc, count(DISTINCT con.cdocpcsequ)";
	// $sql_vencidos  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, CC.ecenpodesc as orgaocontratante, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, orlic.eorglidesc, adt.aaditinuad, adt.daditifivg, CASE WHEN con.vctrpcsean IS NULL THEN (con.vctrpceant + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpceant IS NOT NULL THEN (con.vctrpcsean + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcglaa + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcvlor + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) END as soma_total, COALESCE(sum(med.vmedcovalm),0.000) as valor_total_medicao, COALESCE(sum(apost.vapostvtap),0.000) as valor_total_apostilamento, COALESCE(sum(adt.vaditivtad),0.000) as valor_total_aditivo, ";
	// $sql_vencidos  .= "count(con.cdocpcsequ) ";
	$sql_vencidos  .= "FROM sfpc.tbcontratosfpc CON inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) ";
	$sql_vencidos  .= "inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) ";
	$sql_vencidos  .= "left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
	$sql_vencidos  .= "left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
	$sql_vencidos  .= "left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
	$sql_vencidos  .= "left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') ";
	$sql_vencidos  .= "left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) ";
	$sql_vencidos  .= "left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) ";
	$sql_vencidos  .= " where doc.csitdcsequ = 1 ";
	$sql_vencidos  .= $sql_orgao; //(count($array_orgaos) == 1) ? $sql_orgao : '';
	$sql_vencidos  .= $retorna_apenas_vencidos;
	$sql_vencidos  .= " group by con.cdocpcsequ, orlic.eorglidesc, adt.cdocpcseq1 ";
		
	$vencidos  = $db->query($sql_vencidos);

	if(db::isError($vencidos) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql_vencidos");
	}else{

		$RowsTeste_vencidos = $vencidos->numRows();
		for($j = 0; $j < $RowsTeste_vencidos; $j++){
			$registros_vencidos[] = $vencidos->fetchRow();
			// var_dump($registros_vencidos);die;
			$QtdAtendidaTeste = $registros_vencidos[0];
			if($QtdAtendidaTeste != 0){
				$FlagItemAtendido = 1;
			}
		}
	}
	
}

$tem_fornecedor = (!empty($_POST['cnpj']) | !empty($_POST['cpf'])) ? $sql_forn : '';
$orgao_todos = " and orlic.eorglidesc = "; // ($Orgao_sel != "TODOS") ? "orlic.eorglidesc = " : "orlic.CORGLICODI = ";

if($Orgao_sel == "TODOS"){
	$OrgaoDesc = "SELECT DISTINCT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI in (". $orgao_selecionado . ")"; //executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI in (". $orgao_selecionado . ")"); //DESC ORGAO
	$resteste  = $db->query($OrgaoDesc);
	$RowsDesc = $resteste->numRows();
	for($i = 0; $i < $RowsDesc; $i++){
		$orgao_lic = $resteste->fetchRow();
		$tst .= $orgao_lic[0].'|';
		$QtdAtendidaTeste = $orgao_lic[0];
	}

	$array_orgaos = explode('|', $tst);
	// var_dump($array_orgaos);die;
}
else{
	$OrgaoDesc = "SELECT DISTINCT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $orgao_selecionado"; //executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $orgao_selecionado"); //DESC ORGAO
	$resteste  = $db->query($OrgaoDesc);
	$RowsDesc = $resteste->numRows();
	for($i = 0; $i < $RowsDesc; $i++){
		$orgao_lic       = $resteste->fetchRow();
		$array_orgaos = $orgao_lic;

		$QtdAtendidaTeste = $orgao_lic[0];
	}
}

if((!empty($_POST['cnpj']) || !empty($_POST['cpf']))){
	$FornDesc = "select nforcrrazs from sfpc.tbfornecedorcredenciado where $sql_desc_forn";
	$resteste  = $db->query($FornDesc);
	
	$RowsDesc = $resteste->numRows();
	for($i = 0; $i < $RowsDesc; $i++){
		$fornecedor = $resteste->fetchRow();
		$QtdAtendidaTeste = $fornecedor[0];
	}
}

$db->disconnect();

# Início do Cabeçalho Móvel #

if($FlagItemAtendido == 1){
	$db   = Conexao();
	
	$res  = $db->query($sql);
	$res_vencidos  = $db->query($sql_vencidos);
	// var_dump($res_vencidos);die;
	$orgaos_descricao = $db->query($OrgaoDesc);
	
	if( db::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{

		$h = 4;
		$hm = 0;
		$h1 = $pdf->GetStringHeight(113, $h, 'Período', "L");
		$h2 = $pdf->GetStringHeight(105, $h, 'orgão', "L");
		$hm = $h1;
		if ($hm < $h2)
			$hm = $h2;
		$h1 = $hm / ($h1 / $h);
		$h2 = $hm / ($h2 / $h);

		if ($hm < 6) {
			$h1 = 6;
			$h2 = 6;
			$hm = 6;
		}

		$pdf->SetFont("Arial", "B", 8);
		$pdf->Cell(30, 6, "  ORGÃO ", 1, 0, "L", 1);
		$pdf->SetFont("Arial", "B", 7.4);
		$x = $pdf->GetX() + 136;
		$y = $pdf->GetY();
		$pdf->MultiCell(136, $h1, ($Orgao_sel == "TODOS") ? 'TODOS' : $orgao_lic[0], 1, "L", 0);
		$pdf->SetXY($x, $y);

		$pdf->SetFont("Arial", "B", 8);
		$pdf->Cell(54, 6, "  APENAS CONTRATOS VIGENTES? ", 1, 0, "L", 1);
		$pdf->Cell(60, 6, $periodo, 1, 1, "L", 0);

		$pdf->SetFont("Arial", "B", 8);
		
		
		
		$pdf->Cell(280, 5, " ", 0, 1, "C", 0);

		// $pdf->Cell(280, 5, " RELATÓRIO DE CONTRATOS ".$tipo, 1, 1, "C", 1);
		
		$pdf->SetFont("Arial", "B", 9);

			$Rows = $res->numRows();
			$reg_row = $res->fetchRow();

			$tabelas_pesquisa = "	
				inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) 
				inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) 
				left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) 
				left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) 
				left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) 
				left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') 
				left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) 
				left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) 
				";

				$soma_total_medidos = 0;
				$soma_total_valor_global = 0;
				$soma_total_saldo_executar = 0;
				$valor_medido = 0;

				$soma_vigentes = 0;
				$soma_vencidos = 0;

			foreach ($array_orgaos as $key => $orgao_licitante) {
				
				if($orgao_licitante != ""){
					$existe_periodo = "SELECT COUNT(*) FROM sfpc.tbcontratosfpc con 
					$tabelas_pesquisa
					WHERE doc.csitdcsequ = 1 and doc.cfasedsequ = 1
					$sql_periodo_novo 
					$orgao_todos 
					'$orgao_licitante' 
					$tem_fornecedor
					";
					
					$existe  = $db->query($existe_periodo);
					$RowsDesc = $existe->numRows();
					$existe_registro_orgao = $existe->fetchRow();
					
					
					
					if($existe_registro_orgao[0] > 0){ //&& ($reg_row[$key][30] == $orgao_licitante)
					
						$pdf->SetFont("Arial", "B", 8);
						$pdf->Cell(280, 5, $orgao_licitante, 1, 1, "C", 1);
						$array_orgaos[$key_i] = current(array($orgao));

						foreach ($registros as $key => $value) {

							$teste[] = $value;
							
							if($teste[$key][30] == $orgao_licitante){
								
								$codigo = $teste[$key][39];
								
								// $valor_aditivo = "select COALESCE(sum(adit.vaditivtad),0.000) from sfpc.tbaditivo as adit left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ where doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 and cdocpcseq1 = ". $codigo;
								// $valor_adit  = $db->query($valor_aditivo);
								// $soma_valor_adt = $valor_adit->fetchRow();

								// $valor_apostilamento = "select COALESCE(sum(vapostvtap),0.000) from sfpc.tbapostilamento  apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ where cdocpcseq2 = ". $codigo . " and cfasedsequ = 6 and doc.csitdcsequ = 1";
								// $valor_apost  = $db->query($valor_apostilamento);
								// $soma_valor_apost = $valor_apost->fetchRow();

								// $valor_medicao = "select COALESCE(sum(vmedcovalm),0.000) from sfpc.tbmedicaocontrato where cdocpcsequ = ". $codigo;
								// $valor_med  = $db->query($valor_medicao);
								// $existe_valor_med = $valor_med->fetchRow();
								// $valor_totalMedicao = $existe_valor_med[0];
								
								// $valores_originais = "select con.cdocpcsequ, (CASE 
								// WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000) - COALESCE(con.vctrpceant, 0.000)) 
								// WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as saldo_executar,
								// (CASE 
								// WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000))
								// WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as valor_global_aditivo_apostilamento, con.vctrpcglaa, con.vctrpcvlor, (COALESCE(con.vctrpceant, 0.000)) as valor_executado_ini, adt.cdocpcseq1
							
								// FROM sfpc.tbcontratosfpc CON 
								// $tabelas_pesquisa
								// where con.corglicodi = ".$teste[$key][14]." and con.cdocpcsequ = ". $codigo ."
								// group by con.vctrpcglaa, con.cdocpcsequ, con.csolcosequ, con.vctrpcsean, con.vctrpceant, con.vctrpcvlor, con.vctrpcglaa, adt.cdocpcseq1
								// ";

								// $existe_valor  = $db->query($valores_originais);
								
								// $existe_valor_registro = $existe_valor->fetchRow();
								// if($existe_valor_registro[5] != 0){

								// 	$valor_registro_global += floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]);
								// 	$valor_saldo_executar += floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]) - floatval($existe_valor_med[0]);

								// 	$valor_medicao_orgao += (floatval($existe_valor_registro[5]) + floatval($existe_valor_med[0]));
								// 	$valor_global_orgao += (floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]));
								// 	$valor_saldo_executar_orgao += (floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]) - floatval($existe_valor_med[0]));
								// }else{
								// 	$valor_registro_global += floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]);
								// 	$valor_saldo_executar += floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0])  - floatval($existe_valor_med[0]);

								// 	$valor_medicao_orgao += (floatval($existe_valor_med[0]));
								// 	$valor_global_orgao += (floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]));
								// 	$valor_saldo_executar_orgao += (floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0])  - floatval($existe_valor_med[0]));
								// }

								$valor_registro_global += $objFuncoesGerais->valorGlobal($codigo, false);
								$valor_saldo_executar += $objFuncoesGerais->saldoAExecutar($codigo, false);

								$valor_medicao_orgao += $objFuncoesGerais->valorExecutado($codigo, false);
								$valor_global_orgao += $objFuncoesGerais->valorGlobal($codigo, false);
								$valor_saldo_executar_orgao += $objFuncoesGerais->saldoAExecutar($codigo, false);

								$valor_global = $valor_registro_global;
								$valor_saldo_a_executar = $valor_saldo_executar;
								
								$orgao_contratante = $teste[$key][30];
							}
						}

						foreach ($registros_vigentes as $key => $value_vigente) {

							$reg_vigente[] = $value_vigente;

							if($reg_vigente[$key][1] == $orgao_licitante){
								$soma_vigentes += $reg_vigente[$key][2];
							}
						}
						
						if($vigenteNvigente == 'nvigente'){
						
							foreach ($registros_vencidos as $key => $value_vencidos) {

								$reg_vencidos[] = $value_vencidos;
								
								if($reg_vencidos[$key][1] == $orgao_licitante){
									$soma_vencidos += $reg_vencidos[$key][2];
								}
							}
						}
						
						// $pdf->SetFont("Arial", "", 7);

						$pdf->SetFont("Arial", "B", 8);


						$pdf->Cell(80, 6, "  Número de Contratos Vigentes ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, $soma_vigentes, 1, 1, "L", 0);

						if($vigenteNvigente == 'nvigente'){
							$pdf->Cell(80, 6, "  Número de Contratos Vencidos ", 1, 0, "L", 1);
							$pdf->Cell(200, 6, $soma_vencidos, 1, 1, "L", 0);
						}

						$pdf->Cell(80, 6, "  Valor Contratado ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, 'R$ '. number_format($valor_global, 4, ',', '.'), 1, 1, "L", 0);
						$pdf->Cell(80, 6, "  Saldo a Executar ", 1, 0, "L", 1);
						$pdf->Cell(200, 6, 'R$ '. number_format($valor_saldo_a_executar, 4, ',', '.'), 1, 1, "L", 0);
						
						$pdf->Ln();
						
						for($i = 0; $i <= 126; $i++){
							$block=floor($i/6);
							$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
							
							if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {

								$pdf->AddPage(); // page break
							}
						}
					}

					$soma_vigentes = 0;
					$soma_vencidos = 0;
	
					$valor_global = 0;
					$valor_saldo_executar = 0;
					$valor_saldo_a_executar = 0;
					$valor_registro_global = 0;
					$valor_saldo_executa = 0;
					$valor_medicao_orgao = 0;
					$valor_global_orgao = 0;
					$valor_saldo_executar_orgao = 0;
				}
			}
// die;
	}
}else{
	$Mensagem = "Nenhum Item Atendido nesta Requisição";
	$Url = "RelContratoValorContratado.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
$pdf->Output('relatorio.pdf', 'I');


?>