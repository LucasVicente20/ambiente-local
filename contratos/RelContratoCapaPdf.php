<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelContratoCapaPdf.php
# Autor:    Edson Dionisio
# Atividade: 235037
# Data:     17/07/2020
#-------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 26/07/2021
# Objetivo: CR #251286
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 26/10/2021
# Objetivo: CR #255102
#---------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();
$db = Conexao();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/contratos/RelContratoCapa.php' );
if(empty($_SESSION['registro'])){
	$id_registro = $_POST['idregistro'];
	$_SESSION['registro'] = $id_registro;
}elseif(!empty($_SESSION['registro'])){
	$id_registro = $_SESSION['registro']; 
}


# Variáveis com o global off #

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagemComprovante();

# Informa o Título do Relatório #
$TituloRelatorio = "FORMALIZAÇÃO DO CONTRATO";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Muda o tamanho do Rodapé #
$pdf->SetAutoPageBreak(false,60);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 10);

# Adiciona uma página no documento #
$pdf->AddPage();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

#Busca o campo ligado a SCC do contrato para definir se roda a query de contrato normal ou o Contrato Antigo
$sqlChecaSCC = "Select csolcosequ from sfpc.tbcontratosfpc where cdocpcsequ = $id_registro";
$res  = executarSQL($db, $sqlChecaSCC);
$res->fetchInto($retornoScc, DB_FETCHMODE_OBJECT);

# Pega as quantidades atendidas da Requisição de Material de acordo com o Sequencial, para verificar se o relatório deve ser exibido ou emitir mensagem de nenhum item atendido #
if(!is_null($retornoScc->csolcosequ)){
	$sql   = "SELECT con.cdocpcsequ,  con.ectrpcraza,  con.csolcosequ, forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf,";
	$sql  .= "ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, CC.ecenpodesc as orgaocontratante, SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, orlic.eorglidesc ";
	$sql  .= "FROM sfpc.tbcontratosfpc CON inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) ";
	$sql  .= "left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
	$sql  .= "left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
	$sql  .= "left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";          
	$sql  .= " where orlic.corglicodi = CC.corglicodi AND cdocpcsequ = $id_registro";
}else{
	$sql   = "SELECT con.cdocpcsequ,  con.ectrpcraza,  con.csolcosequ, forn.aforcrsequ, con.corglicodi, con.actrpcnumc, forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf,
		ectrpcnumf, actrpcanoc, ectrpcobje, dctrpcinvg, dctrpcfivg, dctrpcinex, dctrpcfiex, orlic.eorglidesc
		FROM sfpc.tbcontratosfpc CON 
		inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ = forn.aforcrsequ ) 
		left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi )         
		where cdocpcsequ = $id_registro";
}

$resteste  = executarSQL($db, $sql);

$resteste->fetchInto($dadosContrato, DB_FETCHMODE_OBJECT);

if(db::isError($resteste) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{

	$RowsTeste = $resteste->numRows();
	for($i = 0; $i < $RowsTeste; $i++){
		$LinhaTeste       = $resteste->fetchRow();
		$QtdAtendidaTeste = $dadosContrato->cdocpcsequ;
		if($QtdAtendidaTeste != 0){
				$FlagItemAtendido = 1;
		}
	}
}

$db->disconnect();

# Início do Cabeçalho Móvel #

	if($FlagItemAtendido == 1){	
						
			if( db::isError($res) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
					
					// var_dump($dadosContrato);exit;
					for($i = 0; $i < $RowsTeste; $i++){
							$_SESSION['AnoRequisicao'] = $AnoRequisicao;
							if(!is_null($retornoScc->csolcosequ)){
								$SCC  = sprintf('%02s', $dadosContrato->ccenpocorg) . sprintf('%02s', $dadosContrato->ccenpounid) . '.' . sprintf('%04s', $dadosContrato->csolcocodi) . '/' . $dadosContrato->asolcoanos;
							}

							$Contrato           = $dadosContrato->ectrpcnumf;
							if($Contrato == 'Aguardando Numeração' or $Contrato == 'AGUARDANDO NUMERAçãO'){
								$contratoNumero = 'Aguardando Nº do Contrato     ';
							}else{
							$contratoNumero = 'CONTRATO Nº '.$Contrato;
							}
							$pdf->Rotate(90);
							$pdf->SetFont("Arial", "B", 11);
							$pdf->Write(-10, $contratoNumero);
							$pdf->Rotate(270);
					
							$pdf->SetFont("Arial", "", 10);

							$_SESSION['Requisicao']    = $dadosContrato->cdocpcsequ;
							
							$razao = $dadosContrato->ectrpcraza;
							$cnpjCpf = !empty($dadosContrato->cnpj)?formatCnpjCpf($dadosContrato->cnpj):formatCnpjCpf($dadosContrato->cpf);
	
							$objeto       		= $dadosContrato->ectrpcobje;
							$data_vigencia_ini  = $dadosContrato->dctrpcinvg;
							$data_vigencia_fim  = $dadosContrato->dctrpcfivg;

							$data_exec_ini      = $dadosContrato->dctrpcinex;
							$data_exec_fim      = $dadosContrato->dctrpcfiex;
							$orgao_contratante = $dadosContrato->eorglidesc;

							$l = 65;

							// Move to 8 cm to the right
							if(!is_null($retornoScc->csolcosequ)){	
								$pdf->Cell(15);
								$pdf->Cell(5, $l,'SCC:',0,0,'C');
								$pdf->Cell(35, $l, $SCC,'','',0,'C');
								$pdf->Ln(10);
							}else{
								$pdf->Cell(15);
								$pdf->Cell(5, $l,'',0,0,'C');
								$pdf->Cell(35, $l, '','','',0,'C');
								$pdf->Ln(10);
							}

							$pdf->Cell(80);
							$pdf->Cell(3, $l,'VIGÊNCIA:',0,0,'C');
							$pdf->Cell(65, $l, date('d/m/Y', strtotime($data_vigencia_ini)) .' a '. date('d/m/Y', strtotime($data_vigencia_fim)),'',0,'C');
							$pdf->Cell(30, $l,'EXECUÇÃO:',0,0,'C');
							$pdf->Cell(38, $l, date('d/m/Y', strtotime($data_exec_ini)) .' a '. date('d/m/Y', strtotime($data_exec_fim)),'',0,'C'); // INSIRO A DATA CORRENTE NA CELULA
							$pdf->Ln(15);

							$pdf->Cell(84);
							$pdf->Cell(5, $l,'FORNECEDOR:',0,0,'C');
							//$pdf->Cell(43, $l, $orgao_contratante, 0,0,'C');
							//$pdf->Ln(6);

							
							if(!empty($dadosContrato->cnpj)){
								$pdf->Cell(28, $l,'CNPJ : ','','B', 0,'C');
							}else{
								$pdf->Cell(28, $l,'CPF : ','','B', 0,'C');
							}
							$pdf->Cell(48, $l, $cnpjCpf, 0,0,'C');
							$pdf->Ln(6);
							$pdf->Cell(122, $l,'Empresa : ','','B', 0,'C');
							$pdf->Ln(30);
							$pdf->Cell(125, 140,'',0, 0,'R');
							$pdf->MultiCell(90, 5, $razao, 0, 'J');
							//$pdf->Ln();

							$pdf->Ln(-5);
							$pdf->Cell(191, 30,'ÓRGÃO CONTRATANTE :',0,0,'C');
							$pdf->Ln(12);
							$pdf->Cell(120, 40,'',0, 0,'R');
							$pdf->MultiCell(140, 6, $orgao_contratante, 0, 'L');
							//$pdf->Cell(22, 30, $orgao_contratante, 0,0,'R');
							$pdf->Ln(-20);
							
							$pdf->Cell(93, $l,'OBJETO : ',0, 0,'R');
							$pdf->Ln(30);
							$pdf->Cell(93, 100,'',0, 0,'R');
							$pdf->MultiCell(150, 6, $objeto, 0, 'J');
							$pdf->Ln(10);

							
					# Fim dos Itens #
					}
			}
	}else{
			$Mensagem = "Nenhum Item Atendido nesta Requisição";
			$Url = "RelContratoCapa.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
			exit;
	}

	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	$pdf->Output('Capa do contrato.pdf', 'I');

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
						$this->Image("$CaminhoImagens/brasao.jpg",95,5,0);
					
						$this->SetFont("Arial","B",12);
						$this->Cell(0,39,"PREFEITURA DO", 0,0,"C");
						$this->Cell(0,30,"",0,0,"R");
						$this->Ln();
					//	$this->Line(30,0,290,205);
						$this->SetFont("Arial","B",36);
						$this->Cell(0,7,"RECIFE",0,0,"C");
						$this->Cell(0,30,"",0,0,"R");
						$this->Ln(15);

						$this->SetFont("Arial","B", 20);
						$this->Cell(0, 29, $GLOBALS['TituloRelatorio'], 0, 0, "C");
						//$this->Ln();
						
						$this->Ln(65);
				}

				function Rotate($angle, $x = -1, $y = -1) {

					if($x == -1){
						$x = $this->x;
					}
					
					if($y == -1){
						$y = $this->y;
					}
			
					if($this->angle!=0){
						$this->_out('Q');
						$this->angle=$angle;
					}
			
					if($angle != 0){
						$angle*=M_PI/180; 
						$c = cos($angle); 
						$s = sin($angle); 
						$cx = $x*$this->k; 
						$cy = ($this->h-$y)*$this->k; 
			
						$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
					}
				}
		}
	}

?>
