<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoCCMaterialTodosPdf.php
# Autor:    Filipe Cavalcanti
# Data:     09/02/2006
# Objetivo: Programa de Impressão dos Materias por centro de custo
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     23/08/2006
# ------------------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     09/10/2006
# ------------------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     23/04/2007 - Colocação do to_date e do translate na query e retirada do sort($itens)
# ------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     13/06/2007 - Colocado filtro para restringir almoxarifado quando o centro de 
#           custo estiver relacionado com mais de um almoxarifado
# ------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterial.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$CentroCusto  = $_GET['CentroCusto'];
		$DataFim      = $_GET['DataFim'];
		$DataIni      = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Atendimento por Centro de Custo de Todos os Materiais por Atendimento";

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

# Pega os dados para exibição #
$db = Conexao();

$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Resgata os intens do estoque #
$sql  = " SELECT TO_DATE(A.TREQMAULAT,'YYYY-MM-DD'), E.EMATEPDESC, F.EUNIDMSIGL, E.CMATEPSEQU, A.AREQMAANOR, A.CREQMACODI, D.AITEMRQTSO, ";
$sql .= "        D.AITEMRQTAT, A.DREQMADATA ";
$sql .= "   FROM SFPC.TBREQUISICAOMATERIAL A, ";
$sql .= "        SFPC.TBSITUACAOREQUISICAO B, ";
$sql .= "        SFPC.TBITEMREQUISICAO D, ";
$sql .= "        SFPC.TBMATERIALPORTAL E, ";
$sql .= "        SFPC.TBUNIDADEDEMEDIDA F ";
$sql .= "  WHERE B.CTIPSRCODI BETWEEN 3 AND 4 AND B.CREQMASEQU = A.CREQMASEQU ";
$sql .= "    AND A.DREQMADATA >= '$DataInibd' AND A.DREQMADATA <= '$DataFimbd' ";
$sql .= "    AND A.CCENPOSEQU = $CentroCusto ";
$sql .= "    AND A.CREQMASEQU = D.CREQMASEQU AND D.AITEMRQTAT > 0 ";
$sql .= "    AND D.CMATEPSEQU = E.CMATEPSEQU AND E.CUNIDMCODI = F.CUNIDMCODI ";

$sql .= "    AND (SELECT DISTINCT CALMPOCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = A.CREQMASEQU) = $Almoxarifado ";

$sql .= " ORDER BY TO_DATE(A.TREQMAULAT,'YYYY-MM-DD'), E.EMATEPDESC  ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Pega as informações do Almoxarifado #
		$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
		$resalmo = $db->query($sqlalmo);
		if( PEAR::isError($resalmo) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
		}else{
				# Carrega os dados do Centro de Custo selecionado #
				$sqlCentrodeCusto  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
				$sqlCentrodeCusto .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
				$sqlCentrodeCusto .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
				$resCC  = $db->query($sqlCentrodeCusto);
				if( PEAR::isError($resCC) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCentrodeCusto");
				}else{
						while( $LinhaCC = $resCC->fetchRow() ){
								$DescCentroCusto = $LinhaCC[0];
								$DescOrgao       = $LinhaCC[1];
								$Orgao           = $LinhaCC[2];
								$RPA             = $LinhaCC[3];
								$Detalhamento    = $LinhaCC[4];
						}
				}

				$Almox     = $resalmo->fetchRow();
				$DescAlmox = $Almox[0];

				$pdf->Cell(33,5,"ALMOXARIFADO",1,0,"L",1);
				$pdf->Cell(247,5,$DescAlmox,1,1,"L",0);
				$pdf->Cell(33,5,"CENTRO DE CUSTO",1,0,"L",1);
				$pdf->Cell(247,5,$DescOrgao." - RPA ".$RPA." - ".$DescCentroCusto." - ".$Detalhamento,1,1,"L",0);
				$pdf->Cell(33,5,"PERÍODO",1,0,"L",1);
				$pdf->Cell(247,5,$DataIni." a ".$DataFim,1,1,"L",0);
				$pdf->ln(8);
				$pdf->SetFont("Arial","",8);
				$pdf->Cell(20,5,"DATA ATEND.",1,0,"C",1);
				$pdf->Cell(132,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
				$pdf->Cell(9,5,"UND",1, 0,"C",1);
				$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
				$pdf->Cell(25,5,"Nº / ANO REQ.",1,0,"C",1);
				$pdf->Cell(24,5,"DATA REQ.",1,0,"C",1);
				$pdf->Cell(29,5,"QTD SOLICITADA",1,0,"C",1);
				$pdf->Cell(24,5,"QTD. ATENDIDA",1,1,"C",1);
				$pdf->SetFont("Arial","",9);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelAtendimentoCCMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for($i=0; $i< $rows; $i++){
						$Linha = $res->fetchRow();
						$DataAtend[$i]      = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
						$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]        = $Linha[2];
						$CodigoReduzido[$i] = $Linha[3];
						$AnoReq[$i]         = $Linha[4];
						$NumReq[$i]         = $Linha[5];
						$QtdSolicitada[$i]  = $Linha[6];
						$QtdAtendida[$i]    = $Linha[7];
						$DataReq[$i]        = $Linha[8];

						# Montando o array de Itens do Estoque #
						$Itens[$i] = $DataAtend[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdAtendida[$i].$SimboloConcatenacaoArray.$QtdSolicitada[$i].$SimboloConcatenacaoArray.$AnoReq[$i].$SimboloConcatenacaoArray.$NumReq[$i].$SimboloConcatenacaoArray.$DataReq[$i];
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
for($i=0; $i< count($Itens); $i++){
		# Extrai os dados do Array de Itens #
		$Dados = explode($SimboloConcatenacaoArray,$Itens[$i]);
		$DataAtend          = $Dados[0];
		$DescMaterial       = $Dados[1];
		$CodigoReduzido     = $Dados[2];
		$Unidade            = $Dados[3];
		$QtdAtendida        = $Dados[4];
		$QtdSolicitada      = $Dados[5];
		$AnoReq             = $Dados[6];
		$NumReq             = $Dados[7];
		$DataReq            = substr($Dados[8],8,2)."/".substr($Dados[8],5,2)."/".substr($Dados[8],0,4);
		# Pega a descrição do Material com acento #
		$Descricao          = explode($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial       = $Descricao[1];
		# Quebra de Linha para Descrição do Material #
		$DescMaterialSepara = SeparaFrase($DescMaterial,65);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		$TamMax = 125;
		if($TamDescMaterial <= $TamMax){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif( $TamDescMaterial > $TamMax and $TamDescMaterial <= ($TamMax * 2) ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif( $TamDescMaterial > ($TamMax * 2) and $TamDescMaterial <= ($TamMax * 3) ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}else{
				$LinhasMat = 4;
				$AlturaMat = 20;
		}
		if($TamDescMaterial > $TamMax){
				$Inicio = 0;
				$pdf->SetX(10);
				$pdf->Cell(20,$AlturaMat,$DataAtend,1,0,"C",0);
				$pdf->SetX(30);
				$pdf->Cell(132,$AlturaMat,"",1,0,"L",0);
				$pdf->SetX(162);
				$pdf->Cell(9,$AlturaMat,$Unidade,1,0,"C",0);
				$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
				$pdf->Cell(25,$AlturaMat,substr($NumReq+100000,1)."/".$AnoReq,1,0,"C",0);
				$pdf->Cell(24,$AlturaMat,$DataReq,1,0,"C",0);
				$pdf->Cell(29,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada))),1,0,"R",0);
				$pdf->Cell(24,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida))),1,0,"R",0);
				for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
						if($Quebra == 0){
								$pdf->SetX(30);
								$pdf->Cell(132,5,trim(substr($DescMaterialSepara,$Inicio,65)),0,0,"L",0);
								$pdf->Ln(5);
						}elseif($Quebra == 1){
								$pdf->SetX(30);
								$pdf->Cell(132,5,trim(substr($DescMaterialSepara,$Inicio,65)),0,0,"L",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 2 ){
								$pdf->SetX(30);
								$pdf->Cell(132,5,trim(substr($DescMaterialSepara,$Inicio,65)),0,0,"L",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 3 ){
								$pdf->SetX(30);
								$pdf->Cell(132,5,trim(substr($DescMaterialSepara,$Inicio,65)),0,0,"L",0);
								$pdf->Ln(5);
						}else{
								$pdf->SetX(30);
								$pdf->Cell(132,5,trim(substr($DescMaterialSepara,$Inicio,65)),0,0,"L",0);
								$pdf->Ln(5);
						}
						$Inicio = $Inicio + 65;
				}
		}else{
			$pdf->Cell(20,5,$DataAtend,1,0,"C",0);
			$pdf->Cell(132,5,$DescMaterial,1,0,"L",0);
			$pdf->Cell(9,5,$Unidade,1,0,"C",0);
			$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
			$pdf->Cell(25,5,substr($NumReq+100000,1)."/".$AnoReq,1,0,"C",0);
			$pdf->Cell(24,5,$DataReq,1,0,"C",0);
			$pdf->Cell(29,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada))),1,0,"R",0);
			$pdf->Cell(24,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida))),1,1,"R",0);
		}
		$TotalQtdSolicitada+=$QtdSolicitada;
		$TotalQtdAtendida+=$QtdAtendida;
}

# Mostra o totalizador de Materiais #
$pdf->Cell(227,5,"TOTAL DE ITENS",1,0,"R",1);
$pdf->Cell(29,5,converte_quant(sprintf("%01.2f",$TotalQtdSolicitada)),1,0,"R",0);
$pdf->Cell(24,5,converte_quant(sprintf("%01.2f",$TotalQtdAtendida)),1,0,"R",0);
$pdf->Output();
?>
