<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRelatorioContratoPdf.php
# Autor:    Edson Dionisio
# Data:     30/09/2020
# Objetivo: Programa que irá gerar o relatório em pdf
#			de contratos
# OBS.:     Tabulação 2 espaços
################################################
#-----------------------------------------------------------------------------

include "../funcoes.php";
	
# Executa o controle de segurança #
ini_set("session.auto_start", 0);
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/contratos/CadRelatorioContratoPdf.php');

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

$orgao_selecionado = $_POST['Orgao'];
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Orgao			= $_POST['Orgao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadRelatorioContratoPdf.php";
	
//Inicio da criação das partes fixas dos pdf
# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatário #
if($_POST['vencido-vencer'] == 'vencido'){
	$TituloRelatorio = "RELATÓRIO DE CONTRATOS VENCIDOS";
}else{
	$TituloRelatorio = "RELATÓRIO DE CONTRATOS A VENCER";
}

	
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
$page_height = 225; // mm (portrait letter)
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
$Orgao_sel = '';
if(count($array_orgaos) > 1){
	$Orgao_sel = "TODOS";
	$sql_orgao = ""; //" and orlic.CORGLICODI in (" . $orgao_selecionado . ") ";
}else{
	$Orgao = $array_orgaos;
	$Orgao_sel = $array_orgaos;
	$sql_orgao = " and orlic.CORGLICODI = " . $orgao_selecionado;
}

if($_POST['vencido-vencer'] == 'vencido'){
	// PAssou da data final especificada
	//soma
	$tipo = 'VENCIDO(S)';
	$periodo = $_POST['periodo'];
	$dias_venc = $periodo . " DIAS";
	$data = " INTERVAL '".$periodo." DAYS'";

	$sql_periodo_novo = " and (CASE 
	WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg  BETWEEN now() - $data  AND now())
	WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
		on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
		and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN (con.dctrpcfivg  BETWEEN now() - $data  AND now())
	WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg BETWEEN  now() - $data AND now() and 
		adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
		left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1))
	END )";

}else{
	$tipo = 'A VENCER';

	$periodo = $_POST['periodo'];
	$dias_venc = $periodo . " DIAS";
	$data = " INTERVAL '".$periodo." DAYS'";

	$sql_periodo_novo = " and (CASE 
	WHEN adt.cdocpcseq1 IS NULL THEN (con.dctrpcfivg BETWEEN  now() AND now() + $data )
	WHEN (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 left join sfpc.tbdocumentosfpc as doc2 
		on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM') 
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 
		and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1) IS NULL THEN (con.dctrpcfivg BETWEEN  now() AND now() + $data )
	WHEN adt.cdocpcseq1 IS NOT NULL AND adt.faditialpz = 'SIM' THEN (adt.daditifivg BETWEEN  now() AND now() + $data and 
		adt.aaditinuad = (select MAX(adt2.aaditinuad) from sfpc.tbaditivo adt2 
		left join sfpc.tbdocumentosfpc as doc2 on ( adt2.cdocpcsequ = doc2.cdocpcsequ and adt2.faditialpz = 'SIM')
		where adt2.cdocpcseq1 = con.cdocpcsequ and adt2.faditialpz = 'SIM' and doc2.cfasedsequ = 4 and doc2.ctidocsequ = 2 and doc2.csitdcsequ = 1))
	END )";
}

$sql   = "SELECT DISTINCT ON (con.cdocpcsequ) con.cdocpcsequ, adt.cdocpcseq1,  forn.nforcrrazs as ectrpcraza,  con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf,";
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
$sql  .= " where doc.csitdcsequ = 1 "; //and doc.cfasedsequ = 1 
$sql  .= (count($array_orgaos) > 1) ? $sql_orgao : '';
$sql  .= (!empty($_POST['cnpj']) | !empty($_POST['cpf'])) ? $sql_forn : '';
$sql  .= (!empty($_POST['periodo'])) ? $sql_periodo_novo : '';
$sql  .= " group by con.cdocpcsequ, adt.cdocpcseq1, forn.nforcrrazs, con.csolcosequ, con.cctrpcopex, con.actrpcpzec, con.dctrpcdtpr, ";
$sql  .= "con.vctrpcglaa, con.vctrpcvlor, con.cctrpciden, con.vctrpceant, con.vctrpcsean, con.tctrpculat, ";
$sql  .= "forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc, forn.aforcrccpf, ";
$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, adt.aaditinuad, adt.daditifivg, dctrpcinex, dctrpcfiex, ";
$sql  .= "CC.ecenpodesc, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, ";
$sql  .= "CC.ccenpounid, orlic.eorglidesc";
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

$tem_fornecedor = (!empty($_POST['cnpj']) | !empty($_POST['cpf'])) ? $sql_forn : '';
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
		$pdf->Cell(30, 6, "  ÓRGÃO ", 1, 0, "L", 1);
		$pdf->SetFont("Arial", "B", 7.4);
		$x = $pdf->GetX() + 136;
		$y = $pdf->GetY();
		$pdf->MultiCell(136, $h1, ($Orgao_sel == "TODOS") ? 'TODOS' : $orgao_lic[0], 1, "L", 0);
		$pdf->SetXY($x, $y);

		$pdf->SetFont("Arial", "B", 8);
		$pdf->Cell(28, 6, "  PERÍODO ", 1, 0, "L", 1);
		$pdf->Cell(86, 6, "$periodo Dias", 1, 1, "L", 0);

		$pdf->SetFont("Arial", "B", 8);
		
		$pdf->Cell(30, $hm, "  FORNECEDOR ", 1, 0, "L", 1);
		$pdf->SetFont("Arial", "B", 7.4);
		$x = $pdf->GetX() + 136;
		$y = $pdf->GetY();
		$pdf->MultiCell(250, $h1, ((!empty($_POST['cnpj']) || !empty($_POST['cpf'])) ? $fornecedor[0] : ' TODOS '), 1, "L", 0);
		// $pdf->SetXY($x, $y);
		// $pdf->SetFont("Arial", "B", 8);
		// $pdf->Cell(28, $hm, "  PERÍODO ", 1, 0, "L", 1);
		// $pdf->MultiCell(86, $h2, $tipo, 1, "L", 0, false, 1);
		
		$pdf->Cell(280, 5, " ", 1, 1, "C", 0);

		// $pdf->Cell(280, 5, " RELATÓRIO DE CONTRATOS ".$tipo . " - ". $dias_venc, 1, 1, "C", 1);
		
		$pdf->SetFont("Arial", "B", 9);

			$Rows = $res->numRows();
			$reg_row = $res->fetchRow();
			$tabelas_pesquisa = "	
				inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) 
				inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ = doc.cdocpcsequ ) 
				left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) 
				left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) 
				left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) 
				left outer join sfpc.tbaditivo adt on (con.cdocpcsequ = adt.cdocpcseq1 and adt.ctpadisequ = 11) 
				left outer join sfpc.tbapostilamento apost on (con.cdocpcsequ = apost.cdocpcseq2) 
				left outer join sfpc.tbmedicaocontrato med on (con.cdocpcsequ = med.cdocpcsequ) 
				";
//and doc.cfasedsequ = 1
			foreach ($array_orgaos as $key => $orgao_licitante) {
				//   print_r($array_orgaos);die;
				if($orgao_licitante != ""){
					$existe_periodo = "SELECT COUNT(*) FROM sfpc.tbcontratosfpc con 
					$tabelas_pesquisa
					WHERE doc.csitdcsequ = 1 
					$sql_periodo_novo 
					$orgao_todos 
					'$orgao_licitante' 
					$tem_fornecedor
					";
					
					$existe  = $db->query($existe_periodo);
					$RowsDesc = $existe->numRows();
					$existe_registro_orgao = $existe->fetchRow();
					
					 if($existe_registro_orgao[0] > 0){ //&& ($reg_row[$key][30] == $orgao_licitante)
					
							$pdf->SetFont("Arial", "B", 9);
							$pdf->Cell(280, 5, $orgao_licitante, 1, 1, "C", 1);
							
							$pdf->Cell(126, 12, " FORNECEDOR ", 1, 0, "C", 0);
			
							$x = $pdf->GetX() + 30;
							$y = $pdf->GetY();
							$pdf->MultiCell(30, 3, " \nNÚMERO\nCONTRATO\n ", 1, "C", 0);
							$pdf->SetXY($x, $y);
					
							$x = $pdf->GetX() + 23;
							$y = $pdf->GetY();
							$pdf->MultiCell(23, 3, " \nINÍCIO\nVIGÊNCIA\n ", 1, "C", 0);
							$pdf->SetXY($x, $y);
					
							$x = $pdf->GetX() + 23;
							$y = $pdf->GetY();
							$pdf->MultiCell(23, 3, " \nTÉRMINO\nVIGÊNCIA\n ", 1, "C", 0);
							$pdf->SetXY($x, $y);
					
							$x = $pdf->GetX() + 50;
							$y = $pdf->GetY();
							$pdf->MultiCell(50, 4, " \nVALOR GLOBAL \nADITIVOS/APOSTILAMENTOS ", 1, "C", 0);
							$pdf->SetXY($x, $y);
							
							$x = $pdf->GetX() + 28;
							$y = $pdf->GetY();
							$pdf->MultiCell(28, 3, " \nSALDO\nEXECUTAR\n ", 1, "C", 0);
							$pdf->SetXY($x, $y);
							$pdf->Ln(12);
							
							$array_orgaos[$key_i] = current(array($orgao));
	
							foreach ($registros as $key => $value) {
																
								$teste[] = $value;

								if($teste[$key][30] == $orgao_licitante){

									$valor_aditivo = "select COALESCE(sum(adit.vaditivtad),0.000) from sfpc.tbaditivo as adit left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ where doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 and cdocpcseq1 = ". $teste[$key][0];
									$valor_adit  = $db->query($valor_aditivo);
									$soma_valor_adt = $valor_adit->fetchRow();

									$valor_apostilamento = "select COALESCE(sum(vapostvtap),0.000) from sfpc.tbapostilamento  apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ where cdocpcseq2 = ". $teste[$key][0] . " and cfasedsequ = 6 and doc.csitdcsequ = 1";
									$valor_apost  = $db->query($valor_apostilamento);
									$soma_valor_apost = $valor_apost->fetchRow();

									$valor_medicao = "select COALESCE(sum(vmedcovalm),0.000) from sfpc.tbmedicaocontrato where cdocpcsequ = ". $teste[$key][0];
									$valor_med  = $db->query($valor_medicao);
									$existe_valor_med = $valor_med->fetchRow();

									$valores_originais = "select con.cdocpcsequ, (CASE 
									WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000) - COALESCE(con.vctrpceant, 0.000)) 
									WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as saldo_executar,
									(CASE 
									WHEN con.csolcosequ IS NULL THEN (COALESCE(con.vctrpcglaa, 0.000))
									WHEN con.csolcosequ IS NOT NULL THEN (COALESCE(con.vctrpcvlor,0.000)) END) as valor_global_aditivo_apostilamento, con.vctrpcglaa, con.vctrpcvlor, con.vctrpceant, adt.cdocpcseq1
								
									FROM sfpc.tbcontratosfpc CON 
									$tabelas_pesquisa
									where con.cdocpcsequ = ". $teste[$key][0] ."
									group by con.vctrpcglaa, con.cdocpcsequ, con.csolcosequ, con.vctrpcsean, con.vctrpceant, con.vctrpcvlor, con.vctrpcglaa, adt.cdocpcseq1
								";

								$existe_valor  = $db->query($valores_originais);
								   
								$existe_valor_registro = $existe_valor->fetchRow();
								if(!empty($existe_valor_registro[5])){
									$valor_global_aditivo_apostilamento = 'R$ '. number_format(floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]), 4, ',', '.');
									$saldo_a_executar = 'R$ '. number_format(floatval($existe_valor_registro[1]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]) - floatval($existe_valor_med[0]), 4, ',', '.');
								}else{
									
									$valor_global_aditivo_apostilamento = 'R$ '. number_format(floatval($existe_valor_registro[2]) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0]), 4, ',', '.');
									$saldo_a_executar = 'R$ '. number_format((floatval($existe_valor_registro[1])) + floatval($soma_valor_adt[0]) + floatval($soma_valor_apost[0])  - floatval($existe_valor_med[0]), 4, ',', '.');
								}
								
							$orgao_contratante = $teste[$key][30];
							if(!is_null($teste[$key][16])){
								$razao = "  ".$teste[$key][2]." CNPJ ".formatCnpjCpf($teste[$key][16]);
							}else{
								$razao = "  ".$teste[$key][2]." CPF ".formatCnpjCpf($teste[$key][17]);
							}
							
							if(strlen($razao) < 90){
								$altura_linha = 13;
							}else{
								$altura_linha = 6.5;
							}
														
							$Contrato = $teste[$key][18];
							$cnpj = formatCnpjCpf($teste[$key][16]);
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

							$x = $pdf->GetX() + 126;
							$y = $pdf->GetY();
							$pdf->MultiCell(126, $altura_linha, $razao, 1, "C", 0);
							$pdf->SetXY($x, $y);
							
							$pdf->Cell(30, 13, ucwords(strtolower($Contrato)), 1, 0, "C");
							
							$pdf->Cell(23, 13, $data_vigencia_ini, 1, 0, "C");
		
							$pdf->Cell(23, 13, $data_vigencia_fim, 1, 0, "C");
		
							$pdf->Cell(50, 13, $valor_global_aditivo_apostilamento, 1, 0, "C");
							$pdf->Cell(28, 13, $saldo_a_executar, 1, 0, "C");								
								
									$pdf->Ln(13);

									for($i = 0; $i <= 126; $i++){
										$block=floor($i/6);
										$space_left = $page_height - ($pdf->GetY() + $bottom_margin); // Espaço a esquerda
										
										if ($i/6 == floor($i/6) && $height_of_cell > $space_left) {

											$pdf->AddPage(); // page break
										}
									}

								}

							}
						}
					}
				}

			//  die;

	}
}else{
	$Mensagem = "Nenhum Item Atendido nesta Requisição";
	$Url = "CadRelatorioContrato.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
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