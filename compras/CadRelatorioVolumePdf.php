<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRelatorioVolumePdf.php
# Autor:    Igor Duarte
# Data:     02/10/2012
# Objetivo: Programa que irá gerar o relatório em pdf
#			de volume de compra/contratação
# OBS.:     Tabulação 2 espaços
################################################
# Autor:	Igor
# Data:		28/01/2013 - alteração na formatação do relatório
################################################
#-----------------------------------------------------------------------------

include "../funcoes.php";
	
# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/compras/CadRelatorioVolumeSelecionar.php');

#função auxiliar
function max_cell($array1, $array2,$pdf){
	$array_temp = array();
	
	foreach($array1 as $key => $cell){
		$array_temp[] = ceil($pdf->GetStringWidth($cell)/$array2[$key]);
	}
	
	return max($array_temp);
}

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$DataIni		= $_GET['DataIni'];
	$DataFim		= $_GET['DataFim'];
	$Orgao			= $_GET['Orgao'];
	$TipoCompra		= $_GET['TipoCompra'];
	$TipoPesquisa	= $_GET['TipoPesquisa'];
	
	if($TipoPesquisa == 'G'){
		$TipoGrupo	= $_GET['TipoGrupo'];
		$Grupo		= $_GET['Grupo'];
	}
	elseif($TipoPesquisa == 'S'){
		$Subelemento = $_GET['Subelemento'];
	}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadRelatorioVolumePdf.php";
	
//Inicio da criação das partes fixas dos pdf
# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatário #
$TituloRelatorio = "Relatório de Volume de Compra/Contratação - SCC - Solicitação de Compras ou Contratação";
	
# Cria o objeto PDF, o Default o formato Retrato, A4  e a medida em milémetros #
$pdf = new PDF("L","mm","A4");
	
# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","B",9);
//final das partes fixas

$db = Conexao();

if($Orgao == "TODOS"){
	$OrgaoDesc = $Orgao;
}
else{
	$OrgaoDesc = resultValorUnico(executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $Orgao")); //DESC ORGAO
}

$TipoCompraDesc = resultValorUnico(executarSQL($db, "SELECT ETPCOMNOME FROM SFPC.TBTIPOCOMPRA WHERE CTPCOMCODI = $TipoCompra"));//DESC TIPO COMPRA
$Periodo		= $DataIni." a ".$DataFim;

if($TipoPesquisa == "G"){
	if($Grupo == "TODOS"){
		$GrupoSubDesc = $Grupo;
	}
	else{
		$GrupoSubDesc = resultValorUnico(executarSQL($db, "SELECT EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $Grupo"));//DESC GRUPO
	}
}
elseif($TipoPesquisa == "S"){
	if($Subelemento == "TODOS"){
		$GrupoSubDesc = $Subelemento;
	}
	else{
		$subParam 	= explode(".", $Subelemento);
		$subsql		= " SELECT 	DISTINCT NGRUSENOMS 
						FROM 	SFPC.TBGRUPOSUBELEMENTODESPESA 
						WHERE	CGRUSEELE1 = $subParam[0]
								AND CGRUSEELE2 = $subParam[1]
								AND CGRUSEELE3 = $subParam[2]
								AND CGRUSEELE4 = $subParam[3]
								AND CGRUSESUBE = $subParam[4]";
		$subnome 	= resultValorUnico(executarSQL($db, $subsql));
		
		$GrupoSubDesc = $Subelemento." - ".$subnome;
	}
}

/*
 * CABEÇALHO INICIAL
 */
$pdf->SetFont("Arial","",7);

$pdf->SetFont("Arial","B",7);
$pdf->Cell(40,5,"Órgão",1,0,"L",1);
$pdf->Cell(100,5,$OrgaoDesc,1,0,"L",0);

$pdf->Cell(40,5,"Período",1,0,"L",1);
$pdf->Cell(100,5,$Periodo,1,1,"L",0);

$NumLinhas = 1;
$AlturaCel = 5;

$tamanho = $pdf->GetStringWidth($GrupoSubDesc);

if( $tamanho <= 95 ){
	$NumLinhas = 1;
	$AlturaCel = 5;
}
else{
	$NumLinhas = 2;
	$AlturaCel = 10;
}

$pdf->Cell(40, $AlturaCel,"Tipo de Compra",1,0,"L",1);
$pdf->Cell(100, $AlturaCel,$TipoCompraDesc,1,0,"L",0);

$pdf->Cell(40, $AlturaCel,"Grupo/Subelemento de Despesa",1,0,"L",1);

$x = $pdf->GetX();

if($NumLinhas > 1){
	$Inicio	= 0;
	
	for($quebra = 0; $quebra < $NumLinhas; $quebra++){
		if($quebra == ($NumLinhas -1)){
			$b = "B";
		}
		else{
			$b = "";
		}
		if($quebra == 0){
			$pdf->SetX($x);
			$pdf->Cell(100,5,(substr($GrupoSubDesc,$Inicio,65)),"LTR".$b,0,"L",0);			
		}
		elseif($quebra == 1){
			$pdf->SetX($x);
			$pdf->Cell(100,5,(substr($GrupoSubDesc,$Inicio,65)),"LR".$b,0,"L",0);
		}
		$pdf->Ln(5);
		$Inicio += 65;
	}
}
else{
	$pdf->Cell(100,5,$GrupoSubDesc,1,1,"L",0);
}

/*
 * FINAL DO CABEÇALHO INICIAL
 */

/*
 * CRIAÇÃO DA CONSULTA
 */

$dataTIni	= explode("/", $DataIni);
$dataTIni	= $dataTIni[2]."-".$dataTIni[1]."-".$dataTIni[0];

$dataTFim	= explode("/", $DataFim);
$dataTFim	= $dataTFim[2]."-".$dataTFim[1]."-".$dataTFim[0];

$select = "SELECT DISTINCT
			SCC.CORGLICODI			--0-CODIGO DO ORGÃO
			,SCC.CSITSOCODI			--1-CODIGO AUX. PARA CALCULO DE VALOR DA COMPRA
			,SITSO.ESITSONOME		--2-SITUAÇÃO DA SCC ok
			,SCC.TSOLCODATA			--3-DATA DA SCC ok
			,CCEN.CCENPOCORG		--4-NUMERO DA SCC/OO ok
			,CCEN.CCENPOUNID		--5-NUMERO DA SCC/UU ok
			,SCC.CSOLCOCODI			--6-NUMERO DA SCC/SEQU ok
			,SCC.ASOLCOANOS			--7-NUMERO DA SCC/ANO ok
			,TPCOM.ETPCOMNOME		--8-ORIGEM ok
			,SCC.ESOLCOOBJE			--9-OBJETO SCC ok
			,SCC.CSOLCOSEQU			--10
			,SCC.CLICPOPROC			--11
			,SCC.ALICPOANOP			--12
			,SCC.CGREMPCODI			--13
			,SCC.CCOMLICODI 		--14\n";

$from	= "FROM
			SFPC.TBSOLICITACAOCOMPRA SCC
			JOIN SFPC.TBSITUACAOSOLICITACAO SITSO ON SITSO.CSITSOCODI = SCC.CSITSOCODI
			JOIN SFPC.TBTIPOCOMPRA TPCOM ON TPCOM.CTPCOMCODI = SCC.CTPCOMCODI
			JOIN SFPC.TBCENTROCUSTOPORTAL CCEN ON CCEN.CCENPOSEQU = SCC.CCENPOSEQU	\n";

$where	= "WHERE
			SCC.TSOLCODATA BETWEEN '".$dataTIni."' AND '".$dataTFim."'
			AND SCC.CTPCOMCODI = $TipoCompra \n";

if($Orgao != "TODOS"){
	$where	.= "	AND SCC.CORGLICODI = $Orgao \n";
}
else{
	$where 	.= "	AND SCC.CORGLICODI IN (SELECT DISTINCT ORG.CORGLICODI FROM SFPC.TBORGAOLICITANTE ORG WHERE ORG.FORGLISITU = 'A')\n";
}

//ordenação dos itens da consultas
$order  = "ORDER BY
			SCC.CORGLICODI, SITSO.ESITSONOME, CCEN.CCENPOCORG, CCEN.CCENPOUNID, SCC.CSOLCOCODI, SCC.ASOLCOANOS, SCC.TSOLCODATA";


$consulta = $select.$from.$where.$order;
$resultado = $db->query($consulta);

/*
 * FINAL DA CONSULTA
 */

$itens = array();
$i = 0;
$totalGeral = 0;
$totalOrg = array();

if( PEAR::isError($resultado) ){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $consulta");
}
else{
	if($resultado->numRows()>0){		
		while($Linha = $resultado->fetchRow()){
			
			$arrayGS = array();
				
			
			if($TipoGrupo == 'M' || ($Subelemento != NULL && $Subelemento != "")){
				$sqlGS = "SELECT DISTINCT SCM.CGRUMSCODI
						  FROM   SFPC.TBMATERIALPORTAL MAT
								 JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON SCM.CSUBCLSEQU = MAT.CSUBCLSEQU
						  WHERE  MAT.CMATEPSEQU IN (SELECT CMATEPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10])";
			
				if($Grupo != "TODOS" && ($Subelemento == NULL || $Subelemento == "") ){
					$sqlGS .= " AND SCM.CGRUMSCODI = $Grupo";
				}
			
				$resultGS = $db->query($sqlGS);
			
				if($resultGS->numRows()>0){
					while($linhaGS = $resultGS->fetchRow()){
						if($linhaGS[0] != NULL && $linhaGS[0] != "")
						$arrayGS[] = $linhaGS[0];
					}
				}
			}//PESQUISA = GRUPO > GRUPO = MATERIAL
			elseif($TipoGrupo == 'S' || ($Subelemento != NULL && $Subelemento != "")){
				$sqlGS = "SELECT DISTINCT CGRUMSCODI
						  FROM   SFPC.TBSERVICOPORTAL 
						  WHERE  CSERVPSEQU IN (SELECT CSERVPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10])";
			
				if($Grupo != "TODOS" && ($Subelemento == NULL || $Subelemento == "") ){
					$sqlGS .= " AND CGRUMSCODI = $Grupo";
				}
			
				$resultGS = $db->query($sqlGS);
				//var_dump($sqlGS); die;
				if($resultGS->numRows()>0){
					while($linhaGS = $resultGS->fetchRow()){
						if($linhaGS[0] != NULL && $linhaGS[0] != "")
						$arrayGS[] = $linhaGS[0];
					}
				}
			}//PESQUISA = GRUPO > GRUPO = SERVICO
				
			$whereGS = "";
				
			if($Subelemento != 'TODOS' && $Subelemento != "" && $Subelemento != NULL){
				$whereGS = "AND CGRUSEELE1 = $subParam[0] AND CGRUSEELE2 = $subParam[1]
							AND CGRUSEELE3 = $subParam[2] AND CGRUSEELE4 = $subParam[3] AND CGRUSESUBE = $subParam[4]
							LIMIT 1";
			}
			else{
				$whereGS = "LIMIT 1";
			}

			$tempGS = array();
				
			foreach($arrayGS as $gs){
				$sqlGS2 = "	SELECT DISTINCT
									GMS.EGRUMSDESC ,GSUB.CGRUSEELE1 ,GSUB.CGRUSEELE2 ,GSUB.CGRUSEELE3 ,GSUB.CGRUSEELE4 ,GSUB.CGRUSESUBE ,GSUB.NGRUSENOMS 
							FROM
									SFPC.TBGRUPOMATERIALSERVICO GMS 
									LEFT JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSUB ON GMS.CGRUMSCODI = GSUB.CGRUMSCODI
							WHERE
									GSUB.CGRUMSCODI = $gs 
									AND GSUB.FGRUSENATU = 'S'
									".$whereGS;
			
				$resultGS1 = $db->query($sqlGS2);
					
				if($resultGS1->numRows()>0){
					$linhaGS1 = $resultGS1->fetchRow();
					if($linhaGS1 != null){
						if($linhaGS1[1] == null || $linhaGS1[1] == ""){
							$itens[$i]['GrupoSub'][] = $linhaGS1[0]." / -";
						}
						else{
							$tempGS[] = $linhaGS1[0]." / ".$linhaGS1[1].".".$linhaGS1[2].".".$linhaGS1[3].".".$linhaGS1[4].".".$linhaGS1[5];
						}
					}
				}
			}
				
			if(empty($tempGS)){
				continue;
			}
			
			$itens[$i]['Orgao'] 		= $Linha[0];	//CÓDIGO DO ORGÃO
			$itens[$i]['OrgDesc']		= resultValorUnico(executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $Linha[0]"));//DESC DO ÓRGÃO
			$itens[$i]['SituacaoSCC'] 	= $Linha[2];	//SITUAÇÃO DA SCC
			$itens[$i]['Origem'] 		= $Linha[8];	//ORIGEM
			$itens[$i]['GrupoSub'] 		= $tempGS;		//GRUPO/SUBELEMENTO
			$itens[$i]['Objeto']	 	= $Linha[9];	//OBJETO

			if($Linha[1] != 9){
				$v = (resultValorUnico(executarSQL($db, "SELECT SUM(AITESCQTSO*VITESCUNIT) FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10]")));//VALOR COMPRA
				$totalGeral += $v;
				$itens[$i]['Valor'] = converte_valor_estoques($v);
				$totalOrg[$Linha[0]] += $v;
			}
			else{
				if($Linha[11] != null && $Linha[11] != ""){
					$sqlValor = "SELECT	SUM(ILP.AITELPQTSO*ILP.VITELPVLOG) 
								 FROM 	SFPC.TBITEMLICITACAOPORTAL ILP
								 WHERE  ILP.CLICPOPROC = $Linha[11] AND ILP.ALICPOANOP = $Linha[12]
										AND ILP.CGREMPCODI = $Linha[13] AND ILP.CCOMLICODI = $Linha[14]
										AND ILP.CORGLICODI = $Linha[0]
										AND(
											ILP.CMATEPSEQU IN(SELECT CMATEPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10] AND CMATEPSEQU IS NOT NULL)
											OR ILP.CSERVPSEQU IN(SELECT CSERVPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10] AND CSERVPSEQU IS NOT NULL)
										)";
					$v = (resultValorUnico(executarSQL($db, $sqlValor))); //VALOR COMPRA
					$totalGeral += $v;
					$itens[$i]['Valor'] = converte_valor_estoques($v);
					$totalOrg[$Linha[0]] += $v;
				}
				else{
					$itens[$i]['Valor'] = converte_valor_estoques(0);
				}
				
			}
			
			$temp = "";
			if($Linha[4] < 10){
				$temp .= "0".$Linha[4];
			}
			else{
				$temp .= $Linha[4];
			}
		
			if($Linha[5] < 10){
				$temp .= "0".$Linha[5].".";
			}
			else{
				$temp .= $Linha[5].".";
			}
		
			if($Linha[6] < 10){
				$temp .= "000".$Linha[6].".";
			}
			elseif(($Linha[6] < 100) && ($Linha[6] >= 10)){
				$temp .= "00".$Linha[6].".";
			}
			elseif(($LinhaSCC[6] < 1000) && ($Linha[6] >= 100)){
				$temp .= "0".$Linha[6].".";
			}
			else{
				$temp .= $Linha[6].".";
			}
	
			$temp .= $Linha[7];
			 
			$dataTemp = explode(" ", $Linha[3]);
			$dataTemp = explode("-",$dataTemp[0]);
			$dataTemp = $dataTemp[2]."/".$dataTemp[1]."/".$dataTemp[0];
			$itens[$i]['NumSCCData'] = $temp."/".$dataTemp;//Nº DA SCC/DATA
			
			/*
			 * OBTER GRUPOS/SUBELEMENTO
			 */
									
			$i++;	
		}//FINAL DO WHILE 1
		
//		var_dump($itens);
//		var_dump($consulta); 
//		var_dump($totalOrg);
//		die();
		
		if(empty($itens)){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">A pesquisa não retornou resultados</a>";
			
			$Url = "CadRelatorioVolumeSelecionar.php?Mens=$Mens&Mensagem=$Mensagem&Tipo=$Tipo";
				
			if (!in_array($Url,$_SESSION['GetUrl'])){
				$_SESSION['GetUrl'][] = $Url;
			}
			
			header("location: ".$Url);
			exit;
		}
		else{
			
			$orgtemp = 0;
			$totalP = 0;
			$flag1 = FALSE;
			$tempOrg = 0;
			
			foreach($itens as $item){
				if($item['Orgao'] != $orgtemp){
					if(!empty($orgtemp)){
						$pdf->SetFont("Arial", "B", 8);
						$pdf->Cell(140, 5, "TOTAL",1,0,"L",1);
						$pdf->SetFont("Arial", "", 8);
						$pdf->Cell(140, 5, converte_valor_estoques($totalOrg[$tempOrg]),1,1,"L",1);
					}
					$pdf->SetFont("Arial", "B", 8);
					$pdf->Cell(280, 5, "ÓRGÃO: ".$item['OrgDesc']." ",1,1,"C",1);
					
					$orgtemp = $item['Orgao'];
					$flag1 = FALSE;
				}
				
				if(!$flag1){
					$flag1 = true;
					
					$pdf->SetFont("Arial","B",6);
					
					$pdf->Cell(30,10,"SITUAÇÃO DA SCC",1,0,"C",1);
					$pdf->Cell(30,10,"SCC Nº/DATA",1,0,"C",1);
					$pdf->Cell(25,10,"ORIGEM",1,0,"C",1);
					$pdf->Cell(90,10,"GRUPO MATERIAL OU SERVIÇO/SUBELEMENTO",1,0,"C",1);
					$pdf->Cell(85,10,"OBJETO",1,0,"C",1);
					
					$pdf->Cell(20,5,"VALOR COMPRA/","LTR",1,"C",1);
					$pdf->SetX(270);
					$pdf->Cell(20,5,"CONTRATAÇÃO","LBR",1,"C",1);					
					
				}
				
				$imprimir = "";
				foreach($item['GrupoSub'] as $gruposub){
					//$imprimir .= $gruposub."\n";
					$imprimir .= $gruposub." ";
				}
				
				/*TESTE MULTICELL
				$y = $pdf->GetY();
				$ymax = 0;
				
				//$tam = ceil($pdf->GetStringWidth($item['SituacaoSCC'])/30);
				
				$pdf->SetXY(10, $y);
				$pdf->MultiCell(30,5,$item['SituacaoSCC'],0,"C",0);//SITUACAO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(40, $y);
				$pdf->MultiCell(30,5,$item['NumSCCData'],0,"C",0);//Nº SCC/DATA
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(70, $y);
				$pdf->MultiCell(25,5,$item['Origem'],0,"C",0);//ORIGEM
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);


				$tam = ceil($pdf->GetStringWidth($imprimir)/90);
				
				$pdf->SetXY(95, $y);
				$pdf->MultiCell(90,5,trim($imprimir)." $tam",0,"C",0);//GRUPO/SUBELEMENTO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				
				$tam = ceil($pdf->GetStringWidth($item['Objeto'])/85);
				
				$pdf->SetXY(185, $y);
				$pdf->MultiCell(85,5,trim($item['Objeto'])." $tam",0,"C",0);//OBJETO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(270, $y);
				$pdf->MultiCell(20,5,$item['Valor'],0,"R",0);//VALOR
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				if($ymax < 189){
					$ymax2 = $ymax - $y;
					
					$pdf->Rect(10, $y, 30, $ymax2);
					$pdf->Rect(40, $y, 30, $ymax2);
					$pdf->Rect(70, $y, 25, $ymax2);
					$pdf->Rect(95, $y, 90, $ymax2);
					$pdf->Rect(185, $y, 85, $ymax2);
					$pdf->Rect(270, $y, 20, $ymax2);
					
					$pdf->SetY($ymax);
				}
				else{
					$range =  189.00125 - $y;
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 25, $range);
					$pdf->Rect(95, $y, 90, $range);
					$pdf->Rect(185, $y, 85, $range);
					$pdf->Rect(270, $y, 20, $range);
					
					$pdf->AddPage();
					
					$range =  $ymax - 189.00125;
					
					$pdf->SetY(39.00125);
					$y = $pdf->GetY();
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 25, $range);
					$pdf->Rect(95, $y, 90, $range);
					$pdf->Rect(185, $y, 85, $range);
					$pdf->Rect(270, $y, 20, $range);
				}
				/**/
				
				 
				
				/*
				 * TESTE NOVO LAYOUT
				 */
				 
				$array_imprimir = array($item['SituacaoSCC'],$item['NumSCCData'],$item['Origem'],trim($imprimir),trim($item['Objeto']),$item['Valor']);
				$array_width	= array(30,30,25,90,85,20);
				
				$qteLinha 		= max_cell($array_imprimir,$array_width,$pdf);
				$testeQteLinha 	= $qteLinha*5 + $pdf->GetY(); 

				
				if($testeQteLinha > 189.99){
					$pdf->AddPage();
					$ymax = 0;
					$pdf->SetY(39.00125);
				}
					
				$y = $pdf->GetY();
				$ymax = 0;
				
				//$tam = ceil($pdf->GetStringWidth($item['SituacaoSCC'])/30);
				
				$pdf->SetXY(10, $y);
				$pdf->MultiCell(30,5,$item['SituacaoSCC'],0,"C",0);//SITUACAO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(40, $y);
				$pdf->MultiCell(30,5,$item['NumSCCData'],0,"C",0);//Nº SCC/DATA
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(70, $y);
				$pdf->MultiCell(25,5,$item['Origem'],0,"C",0);//ORIGEM
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);


				//$tam = ceil($pdf->GetStringWidth($imprimir)/90);
				
				$pdf->SetXY(95, $y);
				$pdf->MultiCell(90,5,trim($imprimir),0,"C",0);//GRUPO/SUBELEMENTO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				
				//$tam = ceil($pdf->GetStringWidth($item['Objeto'])/85);
				
				$pdf->SetXY(185, $y);
				$pdf->MultiCell(85,5,trim($item['Objeto']),0,"C",0);//OBJETO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(270, $y);
				$pdf->MultiCell(20,5,$item['Valor'],0,"R",0);//VALOR
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				if($ymax < 189.99){
					$ymax2 = $ymax - $y;
					
					$pdf->Rect(10, $y, 30, $ymax2);
					$pdf->Rect(40, $y, 30, $ymax2);
					$pdf->Rect(70, $y, 25, $ymax2);
					$pdf->Rect(95, $y, 90, $ymax2);
					$pdf->Rect(185, $y, 85, $ymax2);
					$pdf->Rect(270, $y, 20, $ymax2);
					
					$pdf->SetY($ymax);
				}
				else{
					$range =  189.9900125 - $y;
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 25, $range);
					$pdf->Rect(95, $y, 90, $range);
					$pdf->Rect(185, $y, 85, $range);
					$pdf->Rect(270, $y, 20, $range);
					
					$pdf->AddPage();
					
					$range =  $ymax - 189.9900125;
					
					$pdf->SetY(39.00125);
					$y = $pdf->GetY();
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 25, $range);
					$pdf->Rect(95, $y, 90, $range);
					$pdf->Rect(185, $y, 85, $range);
					$pdf->Rect(270, $y, 20, $range);
				}
				/*
				* TESTE NOVO LAYOUT
				*/
				
				$tempOrg = $item['Orgao'];
			}//fim do foreach
			
			$pdf->SetFont("Arial", "B", 8);
			$pdf->Cell(140, 5, "TOTAL",1,0,"L",1);
			$pdf->SetFont("Arial", "", 8);
			$pdf->Cell(140, 5, converte_valor_estoques($totalOrg[$tempOrg]),1,1,"L",1);
			
			$pdf->SetFont("Arial", "B", 8);
			$pdf->Cell(140, 5, "TOTAL GERAL",1,0,"L",1);
			$pdf->SetFont("Arial", "", 8);
			$pdf->Cell(140, 5, converte_valor_estoques($totalGeral),1,1,"L",1);
		}

		/*
		* FINAL DO DOCUMENTO
		*/
		$db->disconnect();
		$pdf->Output();
	}
	else{
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">A pesquisa não retornou resultados</a>";
		
		$Url = "CadRelatorioVolumeSelecionar.php?Mens=$Mens&Mensagem=$Mensagem&Tipo=$Tipo";
			
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}
		
		header("location: ".$Url);
		exit;
	}//EXCEÇÃO GERADA CASO NÃO SEJA RETORNADA NENHUM RESULTADO DA PESQUISA
}


/*
 * FINAL DO DOCUMENTO
 */
$db->disconnect();
$pdf->Output();
?>