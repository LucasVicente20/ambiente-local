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
AddMenuAcesso('/contratos/RelContratoValorContratadoPdf.php');

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
}elseif(!empty($_SESSION['Orgao'])){
	$orgao_selecionado = $_SESSION['Orgao']; 
}
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Orgao			= $_POST['Orgao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelContratoVencerVencidoPdf.php";
	
//Inicio da criação das partes fixas dos pdf
# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatário #
$TituloRelatorio = "Relatório de Contratos - Valor Contratado";
	
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
if(empty($_SESSION['cpf-cnpj']) || empty($_SESSION['cnpj']) || empty($_SESSION['cpf'])){
	$cpfCnpj = !empty($_POST['cpf-cnpj'])?$_POST['cpf-cnpj']:$_SESSION['cpf-cnpj'];
	$_SESSION['cpf-cnpj'] = $cpfCnpj;
	$cnpj = !empty($_POST['cnpj'])?$_POST['cnpj']:$_SESSION['cnpj'];
	$_SESSION['cnpj'] = $cnpj;
	$cpf = !empty($_POST['cpf'])?$_POST['cpf']:$_SESSION['cpf'];
	$_SESSION['cpf'] = $cpf;
}else{
	$cpfCnpj = $_SESSION['cpf-cnpj'];
	$cnpj    = $_SESSION['cnpj'];
	$cpf     = $_SESSION['cpf'];
}
if($cpfCnpj == 'CNPJ'){
	$fornecedor = str_replace($arrayTirar,'',$cnpjForn);
	$sql_forn = " and forn.aforcrccgc = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccgc = '" . $fornecedor ."'";
}else{
	$fornecedor = str_replace($arrayTirar,'',$cpfForn);
	$sql_forn = " and forn.aforcrccpf = '" . $fornecedor ."'";
	$sql_desc_forn = " aforcrccpf = '" . $fornecedor ."'";
}

$array_orgaos = explode(',', $orgao_selecionado);
$Orgao_sel = '';
if(count($array_orgaos) > 1){
	$Orgao_sel = "TODOS";
	$sql_orgao = ""; //" and orlic.CORGLICODI in (" . $orgao_selecionado . ") ";
}else{
	$Orgao = $array_orgaos;
	$Orgao_sel = $array_orgaos;
	$sql_orgao = " and orlic.CORGLICODI = " . $orgao_selecionado;
}
if(empty($_SESSION['vigente-nvigente'])){
	$vigenteNvigente = $_POST['vigente-nvigente'];
	$_SESSION['vigente-nvigente'] = $vigenteNvigente;
}else{
	$vigenteNvigente = $_SESSION['vigente-nvigente'];
}
if($vigenteNvigente == 'nvigente'){
	// PAssou da data final especificada
	//soma
	$tipo = 'NÃO VIGENTES';
	$periodo = "NÃO";

	$sql_periodo_novo = " and (CASE 
	WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg < now() or con.dctrpcfivg >= now())
	WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
		on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
		and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN (con.dctrpcfivg < now() or con.dctrpcfivg >= now())
	WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg is not null and 
		adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
		left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1))
	END )";

}else{
	$tipo = 'VIGENTES';

	$periodo = "SIM";

	$sql_periodo_novo = " and (CASE 
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
}

$sql   = "SELECT DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ, adt.cdocpcseq1,  forn.nforcrrazs AS razao_social,  con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf,";
$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, CC.ecenpodesc as orgaocontratante, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, orlic.eorglidesc, adt.aaditinuad, adt.daditifivg, CASE WHEN con.vctrpcsean IS NULL THEN (con.vctrpceant + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpceant IS NOT NULL THEN (con.vctrpcsean + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcglaa + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) WHEN con.vctrpcvlor IS NOT NULL THEN (con.vctrpcvlor + COALESCE(sum(adt.vaditivtad),0.000) + COALESCE(sum(apost.vapostvtap),0.000) - COALESCE(sum(med.vmedcovalm),0.000)) END as soma_total, COALESCE(sum(med.vmedcovalm),0.000) as valor_total_medicao, COALESCE(sum(apost.vapostvtap),0.000) as valor_total_apostilamento, COALESCE(sum(adt.vaditivtad),0.000) as valor_total_aditivo, ";
$sql  .= "CASE 
			WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
			on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
			where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
			and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN ( dctrpcfivg )
			end as aditivo_cadastrado ";
$sql  .= "FROM sfpc.tbcontratosfpc CON inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) ";
$sql  .= "inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) ";
$sql  .= "left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
$sql  .= "left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
$sql  .= "left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
$sql  .= "left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.faditialpz = 'SIM') ";
$sql  .= "left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) ";
$sql  .= "left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) ";
$sql  .= " where doc.csitdcsequ = 1 ";
$sql  .= (count($array_orgaos) == 1) ? $sql_orgao : '';
$sql  .= (!empty($cnpj) | !empty($cpf)) ? $sql_forn : '';
$sql  .= $sql_periodo_novo;
$sql  .= " group by con.cdocpcsequ, adt.cdocpcseq1, con.ectrpcraza, con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, ";
$sql  .= "con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, ";
$sql  .= "forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc, forn.aforcrccpf, ";
$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, adt.aaditinuad, adt.daditifivg, dctrpcinex, dctrpcfiex, ";
$sql  .= "CC.ecenpodesc, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, ";
$sql  .= "CC.ccenpounid, orlic.eorglidesc, razao_social";
    //  print_r($sql);die;
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

$tem_fornecedor = (!empty($cnpj) | !empty($cpf)) ? $sql_forn : '';
$orgao_todos = " and orlic.eorglidesc = "; // ($Orgao_sel != "TODOS") ? "orlic.eorglidesc = " : "orlic.CORGLICODI = ";

if($Orgao_sel == "TODOS"){
	$OrgaoDesc = "SELECT DISTINCT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI in (". $orgao_selecionado . ")"; //executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI in (". $orgao_selecionado . ")"); //DESC ORGAO
	$resteste  = $db->query($OrgaoDesc);
	$RowsDesc = $resteste->numRows();
	for($i = 0; $i < $RowsDesc; $i++){
		$orgao_lic = $resteste->fetchRow();
		$tst .= $orgao_lic[0].',';
		$QtdAtendidaTeste = $orgao_lic[0];
	}

	$array_orgaos = explode(',', $tst);
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

if((!empty($cnpj) || !empty($cpf))){
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
		
		$pdf->Cell(30, $hm, "  FORNECEDOR ", 1, 0, "L", 1);
		$pdf->SetFont("Arial", "B", 7.4);
		$x = $pdf->GetX() + 250;
		$y = $pdf->GetY();
		$pdf->MultiCell(250, $h1, ((!empty($cnpj) || !empty($cpf)) ? $fornecedor[0] : ' TODOS '), 1, "L", 0);
		// $pdf->SetXY($x, $y);
		
		
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
				

				$valor_medicao_total_orgao = 0;
				$valor_global_total_orgao = 0;
				$valor_saldo_executar_total_orgao = 0;

			foreach ($array_orgaos as $key => $orgao_licitante) {
				//   print_r($array_orgaos);die;
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
						
						$pdf->Cell(110, 10, " FORNECEDOR ", 1, 0, "C", 0);
		
						$x = $pdf->GetX() + 26;
						$y = $pdf->GetY();
						$pdf->MultiCell(26, 10, " CPF/CNPJ ", 1, "C", 0);
						$pdf->SetXY($x, $y);

						$x = $pdf->GetX() + 22;
						$y = $pdf->GetY();
						$pdf->MultiCell(22, 2.5, " \nNÚMERO\nCONTRATO\n ", 1, "C", 0);
						$pdf->SetXY($x, $y);

						$x = $pdf->GetX() + 18;
						$y = $pdf->GetY();
						$pdf->MultiCell(18, 2.5, " \nTÉRMINO\nVIGÊNCIA\n ", 1, "C", 0);
						$pdf->SetXY($x, $y);
				
						$x = $pdf->GetX() + 35;
						$y = $pdf->GetY();
						$pdf->MultiCell(35, 2.5, " \nVALOR\nMEDIDO\n ", 1, "C", 0);
						$pdf->SetXY($x, $y);
				
						$x = $pdf->GetX() + 35;
						$y = $pdf->GetY();
						$pdf->MultiCell(35, 3.3, " \nVALOR CONTRATO \nCONSOLIDADO ", 1, "C", 0);
						$pdf->SetXY($x, $y);
						
						$x = $pdf->GetX() + 34;
						$y = $pdf->GetY();
						$pdf->MultiCell(34, 2.5, " \nSALDO\nEXECUTAR\n ", 1, "C", 0);
						$pdf->SetXY($x, $y);
						$pdf->Ln(10);
						
						$array_orgaos[$key_i] = current(array($orgao));

						$valor_globa = 0;
						$saldo_a_executar = 0;
						$saldo_a_executar_orgao = 0;

						// Variáveis
						$valor_medicao_orgao = 0;
						$valor_global_orgao = 0;
						$valor_saldo_executar_orgao = 0;

						foreach ($registros as $key => $value) {
															
							$teste[] = $value;
							
							if($teste[$key][30] == $orgao_licitante){

								// $valor_aditivo = "select COALESCE(sum(adit.vaditivtad),0.000) from sfpc.tbaditivo as adit left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ where doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 and cdocpcseq1 = ". $teste[$key][0];
								// $valor_adit  = $db->query($valor_aditivo);
								// $soma_valor_adt = $valor_adit->fetchRow();

								// $valor_apostilamento = "select COALESCE(sum(vapostvtap),0.000) from sfpc.tbapostilamento  apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ where cdocpcseq2 = ". $teste[$key][0] . " and cfasedsequ = 6 and doc.csitdcsequ = 1";
								// $valor_apost  = $db->query($valor_apostilamento);
								// $soma_valor_apost = $valor_apost->fetchRow();

								// $valor_medicao = "select COALESCE(sum(vmedcovalm),0.000) from sfpc.tbmedicaocontrato where cdocpcsequ = ". $teste[$key][0];
								// $valor_med  = $db->query($valor_medicao);
								// $existe_valor_med = $valor_med->fetchRow();
								// $valor_totalMedicao = $existe_valor_med[0];
								// // print_r($valor_med);die;
								// $valores_originais = "select con.cdocpcsequ, (CASE 
								// WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000) - COALESCE(con.vctrpceant, 0.000)) 
								// WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as saldo_executar,
								// (CASE 
								// WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000))
								// WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as valor_global_aditivo_apostilamento, con.vctrpcglaa, con.vctrpcvlor, (COALESCE(con.vctrpceant, 0.000)) as valor_executado_ini, adt.cdocpcseq1
							
								// FROM sfpc.tbcontratosfpc CON 
								// $tabelas_pesquisa
								// where con.cdocpcsequ = ". $teste[$key][0] ."
								// group by con.vctrpcglaa, con.cdocpcsequ, con.csolcosequ, con.vctrpcsean, con.vctrpceant, con.vctrpcvlor, con.vctrpcglaa, adt.cdocpcseq1
								// ";

								// $existe_valor  = $db->query($valores_originais);
								
								// $existe_valor_registro = $existe_valor->fetchRow();

								
								// if($existe_valor_registro[5] != 0){
									// $valor_medicao_registro = 'R$ '. number_format(floatval($existe_valor_registro[5]) + floatval($existe_valor_med[0]), 4, ',', '.');
									// $valor_global_registro = 'R$ '. number_format(floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]), 4, ',', '.');
									// $saldo_a_executar_registro = 'R$ '. number_format(floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]) - floatval($existe_valor_med[0]), 4, ',', '.');

									// $valor_medicao_orgao += (floatval($existe_valor_registro[5]) + floatval($existe_valor_med[0]));
									// $valor_global_orgao += (floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]));
									// $valor_saldo_executar_orgao += (floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]) - floatval($existe_valor_med[0]));
								// }else{
								// 	$valor_medicao_registro = 'R$ '. number_format(floatval($existe_valor_med[0]), 4, ',', '.');
								// 	$valor_global_registro = 'R$ '. number_format(floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]), 4, ',', '.');
								// 	$saldo_a_executar_registro = 'R$ '. number_format(floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0])  - floatval($existe_valor_med[0]), 4, ',', '.');

								// 	$valor_medicao_orgao += (floatval($existe_valor_med[0]));
								// 	$valor_global_orgao += (floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]));
								// 	$valor_saldo_executar_orgao += (floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0])  - floatval($existe_valor_med[0]));
								// }

								$valor_medicao_registro = 'R$ '. $objFuncoesGerais->valorExecutado($teste[$key][0]);
								$valor_global_registro = 'R$ '. $objFuncoesGerais->valorGlobal($teste[$key][0]);
								$saldo_a_executar_registro = 'R$ '. $objFuncoesGerais->saldoAExecutar($teste[$key][0]);

								$valor_medicao_orgao += $objFuncoesGerais->valorExecutado($teste[$key][0], false);
								$valor_global_orgao += $objFuncoesGerais->valorGlobal($teste[$key][0], false);
								$valor_saldo_executar_orgao += $objFuncoesGerais->saldoAExecutar($teste[$key][0], false);


								$soma_total_medidos += $valor_medicao_tot;

								$orgao_contratante = $teste[$key][30];
								$razao = $teste[$key][2];

								if(strlen($razao) < 73){
									$altura_linha = 13;
								}else{
									$altura_linha = 6.5;
								}

								$Contrato = $teste[$key][18];

								$altura_linha_contrato = 13;
								if(strlen($teste[$key][18]) > 15){
									
									$altura_linha_contrato = 6.5;
								}

								$cnpj = (!empty($teste[$key][16])) ? formatCnpjCpf($teste[$key][16]) : formatCnpjCpf($teste[$key][17]);
								
								$orgao_contr = $teste[$key][30];
			
								$data_vigencia_ini  = date('d/m/Y', strtotime($teste[$key][21]));
									
								$data_exec_ini      = date('d/m/Y', strtotime($teste[$key][23]));
								$data_exec_fim      = date('d/m/Y', strtotime($teste[$key][24]));

								if(empty($teste[$key][37])){
									$data_vigencia_fim = date('d/m/Y', strtotime($teste[$key][32])); //($teste[$key][32] != NULL) ? $data_vigencia_fim : date('d/m/Y', strtotime($teste[$key][32]));
								}else{
									$data_vigencia_fim  =  date('d/m/Y', strtotime($teste[$key][22]));
								}

								$pdf->SetFont("Arial", "", 7);

								$x = $pdf->GetX() + 110;
								$y = $pdf->GetY();
								$pdf->MultiCell(110, $altura_linha, $razao, 1, "C", 0);
								$pdf->SetXY($x, $y);
								
								$pdf->Cell(26, 13, ucwords(strtolower($cnpj)), 1, 0, "C");
								$x = $pdf->GetX() + 22;
								$y = $pdf->GetY();
								$pdf->MultiCell(22, $altura_linha_contrato, ucwords(strtolower($Contrato)), 1, "C", 0);
								$pdf->SetXY($x, $y);
								$pdf->Cell(18, 13, $data_vigencia_fim, 1, 0, "C");
								// $valor_medicao = $soma_total_medidos;


								// Valores referentes aos registros
								$pdf->Cell(35, 13, $valor_medicao_registro, 1, 0, "C");
								$pdf->Cell(35, 13, $valor_global_registro, 1, 0, "C");
								$pdf->Cell(34, 13, $saldo_a_executar_registro, 1, 0, "C");								

								$pdf->Ln();
								$y = $pdf->GetY();
								
								if (($y + $height_of_cell) >= 244) {
									$pdf->AddPage();
								}
							}
						}
						// die;

						$valor_medicao_total_orgao += $valor_medicao_orgao;
						$valor_global_total_orgao += $valor_global_orgao;
						$valor_saldo_executar_total_orgao += $valor_saldo_executar_orgao;

						// Valores referentes aos orgãos
						$pdf->SetFont("Arial", "B", 8);
						$x = $pdf->GetX() + 176;
						$y = $pdf->GetY();
						$pdf->MultiCell(176, 8, " VALOR TOTAL POR ORGÃO ", 1, "C", 0);
						$pdf->SetXY($x, $y);

						$pdf->SetFont("Arial", "B", 7);
						$x = $pdf->GetX() + 35;
						$y = $pdf->GetY();
						$pdf->MultiCell(35, 8, 'R$ '. number_format($valor_medicao_orgao, 4, ',', '.'), 1, "C", 0);
						$pdf->SetXY($x, $y);
						// $pdf->Ln();
						$x = $pdf->GetX() + 35;
						$y = $pdf->GetY();
						$pdf->MultiCell(35, 8, 'R$ '. number_format($valor_global_orgao, 4, ',', '.'), 1, "C", 0);
						$pdf->SetXY($x, $y);
						
						$x = $pdf->GetX() + 34;
						$y = $pdf->GetY();
						$pdf->MultiCell(34, 8, 'R$ '. number_format($valor_saldo_executar_orgao, 4, ',', '.'), 1, "C", 0);
						$pdf->SetXY($x, $y);

						$pdf->Ln(10);
						$y = $pdf->GetY();
						// $pdf->Ln(10);
						if (($y + $height_of_cell) >= 246) {
							$pdf->AddPage();
						}
						$pdf->Cell(280, 5, " ", 0, 1, "C", 0);
					}
				}
			}

				$soma_total_saldo_executar = 'R$ '. number_format($valor_saldo_executar_total_orgao, 4, ',', '.');
				$soma_total_valor_global = 'R$ '. number_format($valor_global_total_orgao, 4, ',', '.');
				$soma_total_medidos = 'R$ '. number_format($valor_medicao_total_orgao, 4, ',', '.');
				
				$pdf->Cell(280, 5, " ", 0, 1, "C", 0);

				$y = $pdf->GetY();
								
				if (($y + $height_of_cell) >= 234) {
					$pdf->AddPage();
				}

				$pdf->SetFont("Arial", "B", 9);
				$pdf->Cell(280, 5, " VALORES TOTAIS ", 1, 1, "C", 1);
				$x = $pdf->GetX() + 93;
				$y = $pdf->GetY();
				$pdf->MultiCell(93, 8, " VALOR MEDIDO ", 1, "C", 0);
				$pdf->SetXY($x, $y);
				// $pdf->Ln();
				$x = $pdf->GetX() + 93;
				$y = $pdf->GetY();
				$pdf->MultiCell(93, 8, " VALOR CONTRATO CONSOLIDADO ", 1, "C", 0);
				$pdf->SetXY($x, $y);
				
				$x = $pdf->GetX() + 94;
				$y = $pdf->GetY();
				$pdf->MultiCell(94, 8, " SALDO EXECUTAR ", 1, "C", 0);
				$pdf->SetXY($x, $y);
				$pdf->Ln();

				$pdf->SetFont("Arial", "B", 8);

				$pdf->Cell(93, 13, $soma_total_medidos, 1, 0, "C");
				$pdf->Cell(93, 13, $soma_total_valor_global, 1, 0, "C");								
				$pdf->Cell(94, 13, $soma_total_saldo_executar, 1, 0, "C");

			//  die;

	}
}else{
	$Mensagem = "Nenhum Item Atendido nesta Requisição";
	$Url = "RelContratoVencerVencido.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit;
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
$pdf->Output('relatorio.pdf', 'I');

function formatCnpjCpf($value)
{
$cnpj_cpf = preg_replace("/\D/", '', $value);

if (strlen($cnpj_cpf) === 11) {
	return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cnpj_cpf);
}	
	return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj_cpf);
}

function CabecalhoRodapePaisagemComprovante(){
	# Classes FPDF #
	class PDF extends FPDF {
		# Cabeçalho #
		function Header() {
				##### Verificar endereço quando passar para produção #####
				Global $CaminhoImagens;
				//$this->Image("$CaminhoImagens/brasao.jpg",95,5,0);
			
				$this->SetFont("Arial","B",12);
				$this->Cell(0,39,"1 PREFEITURA DO", 0, 0,"C");
				$this->Cell(0,30,"",0,0,"R");
				$this->Ln();
			//	$this->Line(30,0,290,205);
				$this->SetFont("Arial","B",36);
				$this->Cell(0,7,"RECIFE",0,0,"C");
				$this->Cell(0,30,"",0,0,"R");
				$this->Ln(15);

				$this->SetFont("Arial","B", 12);
				$this->Cell(210, 20, $GLOBALS['TituloRelatorio'], 0, 0, "C");
				
				$this->Ln(15);
		}

	}
}

?>