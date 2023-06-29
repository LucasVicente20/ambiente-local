<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRelatorioSARPPdf.php
# Autor:    Igor Duarte
# Data:     02/10/2012
# Objetivo: Programa para gerar o relatorio em pdf
#			do tipo SARP
#-----------------------------------------------------------------------------

include "../funcoes.php";
	
# Executa o controle de seguranca #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona paginas no MenuAcesso #
AddMenuAcesso('/compras/CadRelatorioSARPSelecionar.php');

#função auxiliar
function max_cell($array1, $array2,$pdf){
	$array_temp = array();

	foreach($array1 as $key => $cell){
		$array_temp[] = ceil($pdf->GetStringWidth($cell)/$array2[$key]);
	}

	return max($array_temp);
}

# Variaveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$DataIni		= $_GET['DataIni'];
	$DataFim		= $_GET['DataFim'];
	$Orgao			= $_GET['Orgao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadRelatorioSARPPdf.php";
	
CabecalhoRodapePaisagem();
$TituloRelatorio = "Relatório de SARP por Comissão - SCC - Solicitação de Compras ou Contratação";
	
$pdf = new PDF("L","mm","A4");
$pdf->AliasNbPages();
$pdf->SetFillColor(220,220,220);
$pdf->AddPage();
$pdf->SetFont("Arial","B",9);


$db = Conexao();

$Periodo = $DataIni." a ".$DataFim;

if($Orgao == "TODOS"){
	$OrgaoDesc = $Orgao;
}
else{
	$OrgaoDesc = resultValorUnico(executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $Orgao")); //DESC ORGAO
}

/*
 * CABECALHO INICIAL
 */
$pdf->SetFont("Arial","",7);

$pdf->SetFont("Arial","B",7);
$pdf->Cell(40,5,"Órgão",1,0,"L",1);
$pdf->Cell(100,5,$OrgaoDesc,1,0,"L",0);

$pdf->Cell(40,5,"Período",1,0,"L",1);
$pdf->Cell(100,5,$Periodo,1,1,"L",0);

/*
 * FINAL DO CABECALHO INICIAL
 */

/*
 * CRIANDO DA CONSULTA
 */

$dataTIni	= explode("/", $DataIni);
$dataTIni	= $dataTIni[2]."-".$dataTIni[1]."-".$dataTIni[0];

$dataTFim	= explode("/", $DataFim);
$dataTFim	= $dataTFim[2]."-".$dataTFim[1]."-".$dataTFim[0];

$select = "SELECT DISTINCT
			SCC.CORGLICODI			--0-CODIGO DO ORG
			,SCC.CSITSOCODI			--1-CODIGO AUX. PARA CALCULO DE VALOR DA COMPRA
			,SITSO.ESITSONOME		--2-SITUACAO DA SCC ok
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
			,SCC.CCOMLICODI 		--14
			,SCC.FSOLCORPCP 		--15
			,COM.ECOMLIDESC			--16-DESC COMISSAO \n";

$from	= "FROM
			SFPC.TBSOLICITACAOCOMPRA SCC
			JOIN SFPC.TBSITUACAOSOLICITACAO SITSO ON SITSO.CSITSOCODI = SCC.CSITSOCODI
			JOIN SFPC.TBTIPOCOMPRA TPCOM ON TPCOM.CTPCOMCODI = SCC.CTPCOMCODI
			JOIN SFPC.TBCENTROCUSTOPORTAL CCEN ON CCEN.CCENPOSEQU = SCC.CCENPOSEQU	
			JOIN SFPC.TBCOMISSAOLICITACAO COM ON COM.CCOMLICODI = SCC.CCOMLICODI \n";

$where	= "WHERE
			SCC.TSOLCODATA BETWEEN '".$dataTIni."' AND '".$dataTFim."'
			AND SCC.FSOLCORPCP IS NOT NULL \n";

if($Orgao != "TODOS"){
	$where	.= "	AND SCC.CORGLICODI = $Orgao \n";
}
else{
	$where 	.= "	AND SCC.CORGLICODI IN (SELECT DISTINCT ORG.CORGLICODI FROM SFPC.TBORGAOLICITANTE ORG WHERE ORG.FORGLISITU = 'A')\n";
}

//ordenando itens da consultas
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
			
			$sqlGS = "SELECT DISTINCT SCM.CGRUMSCODI
					  FROM   SFPC.TBMATERIALPORTAL MAT
							 JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON SCM.CSUBCLSEQU = MAT.CSUBCLSEQU
					  WHERE  MAT.CMATEPSEQU IN (SELECT CMATEPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10])";
		
			$resultGS = $db->query($sqlGS);
		
			if($resultGS->numRows()>0){
				while($linhaGS = $resultGS->fetchRow()){
					if($linhaGS[0] != NULL && $linhaGS[0] != "")
					$arrayGS[] = $linhaGS[0];
				}
			}

			$sqlGS = "SELECT DISTINCT CGRUMSCODI
					  FROM   SFPC.TBSERVICOPORTAL 
					  WHERE  CSERVPSEQU IN (SELECT CSERVPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA WHERE CSOLCOSEQU = $Linha[10])
					";

			$resultGS = $db->query($sqlGS);
			//var_dump($sqlGS); die;
			if($resultGS->numRows()>0){
				while($linhaGS = $resultGS->fetchRow()){
					if($linhaGS[0] != NULL && $linhaGS[0] != "")
					$arrayGS[] = $linhaGS[0];
				}
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
							LIMIT 1";
			
				$resultGS1 = $db->query($sqlGS2);
					
				if($resultGS1->numRows()>0){
					$linhaGS1 = $resultGS1->fetchRow();
					if($linhaGS1 != null){
						if($linhaGS1[1] == null || $linhaGS1[1] == ""){
							$itens[$i]['GrupoSub'][] = $linhaGS1[0]."/ - ";
						}
						else{
							$tempGS[] = $linhaGS1[0]."/ ".$linhaGS1[1].".".$linhaGS1[2].".".$linhaGS1[3].".".$linhaGS1[4].".".$linhaGS1[5];
						}
					}
				}
			}
				
			if(empty($tempGS)){
				continue;
			}
			
			$itens[$i]['Orgao'] 		= $Linha[0];	//COD ORG
			$itens[$i]['OrgDesc']		= resultValorUnico(executarSQL($db, "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $Linha[0]"));//DESC ORG
			$itens[$i]['SituacaoSCC'] 	= $Linha[2];	//SITUACAO DA SCC
			$itens[$i]['GrupoSub'] 		= $tempGS;		//GRUPO/SUBELEMENTO
			$itens[$i]['Objeto']	 	= $Linha[9];	//OBJETO
			$itens[$i]['ProcLic']		= $Linha[11]."/".$Linha[12]." - ".$Linha[16];

			if(!empty($Linha[15])){
				if($Linha[15] == 'C')
					$itens[$i]['SARP'] = "Carona";
				elseif($Linha[15] == 'P')
					$itens[$i]['SARP'] = "Participante";
				else 
					$itens[$i]['SARP'] = "";
			}
			else{
				$itens[$i]['SARP'] = "";	
			}
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
			$itens[$i]['NumSCCData'] = $temp."/".$dataTemp;//N DA SCC/DATA
			
			/*
			 * OBTER GRUPOS/SUBELEMENTO
			 */
									
			$i++;	
		}//FINAL DO WHILE 1
		
		if(empty($itens)){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">A pesquisa não retornou resultados</a>";
			
			$Url = "CadRelatorioSARPSelecionar.php?Mens=$Mens&Mensagem=$Mensagem&Tipo=$Tipo";
				
			if (!in_array($Url,$_SESSION['GetUrl'])){
				$_SESSION['GetUrl'][] = $Url;
			}
			
			$db->disconnect();
			header("location: ".$Url);
			exit;
		}
		else{
			
			$orgtemp = 0;
			$totalP = 0;
			$flag1 = FALSE;
			$tempOrg = 0;
			
			
			//		var_dump($itens);
			//		var_dump($consulta);
			//		var_dump($totalOrg);
			//		die('aqui');
			
			foreach($itens as $item){
				if($item['Orgao'] != $orgtemp){
					if(!empty($orgtemp)){
						$pdf->SetFont("Arial", "B", 8);
						$pdf->Cell(130, 5, "TOTAL",1,0,"L",1);
						$pdf->SetFont("Arial", "", 8);
						$pdf->Cell(150, 5, converte_valor_estoques($totalOrg[$tempOrg]),1,1,"L",1);
					}
					$pdf->SetFont("Arial", "B", 8);
					$pdf->Cell(280, 5, "ORGAO: ".$item['OrgDesc']." ",1,1,"C",1);
					
					$orgtemp = $item['Orgao'];
					$flag1 = FALSE;
				}
				
				if(!$flag1){
					$flag1 = true;
					
					$pdf->SetFont("Arial","B",6);
					
					$pdf->Cell(30,10,"SITUAÇÃO DA SCC",1,0,"C",1);
					$pdf->Cell(30,10,"NÚMERO SCC/DATA",1,0,"C",1);
					$pdf->Cell(70,10,"GRUPO MATERIAL OU SERVICO/SUBELEMENTO",1,0,"C",1);
					$pdf->Cell(70,10,"OBJETO",1,0,"C",1);
					$pdf->Cell(20,5,"VALOR COMPRA/","LTR",0,"C",1);
					$pdf->Cell(15,10,"TIPO SARP",1,0,"C",1);
					$pdf->Cell(45,5,"PROCESSO","LTR",1,"C",1);
					
					$pdf->SetX(210);
					$pdf->Cell(20,5,"CONTRATAÇÃO","LBR",0,"C",1);
					$pdf->SetX(245);
					$pdf->Cell(45,5,"LICITATÓRIO","LBR",1,"C",1);
					
				}
				
				$imprimir = "";
				foreach($item['GrupoSub'] as $gruposub){
					$imprimir .= $gruposub."\n";
				}
				/*NOVO*/
				$array_imprimir = array($item['SituacaoSCC'],$item['NumSCCData'],trim($imprimir),trim($item['Objeto']),$item['Valor'],$item['SARP'],$item['ProcLic']);
				$array_width	= array(30,30,70,70,20,15,45);
				
				$qteLinha 		= max_cell($array_imprimir,$array_width,$pdf);
				$testeQteLinha 	= $qteLinha*5 + $pdf->GetY();
				
				
				if($testeQteLinha > 189.99){
					$pdf->AddPage();
					$ymax = 0;
					$pdf->SetY(39.00125);
				}
					
				$y = $pdf->GetY();
				$ymax = 0;
				
				$pdf->SetXY(10, $y);
				$pdf->MultiCell(30,5,trim($item['SituacaoSCC'],"\t\r"),0,"C",0);//SITUACAO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(40, $y);
				$pdf->MultiCell(30,5,$item['NumSCCData'],0,"C",0);//N SCC/DATA
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$imprimir = "";
				foreach($item['GrupoSub'] as $gruposub){
					$imprimir .= $gruposub."\n";
				}
				
				$pdf->SetXY(70, $y);
				$pdf->MultiCell(70,5,trim($imprimir,"\t\r"),0,"C",0);//GRUPO/SUBELEMENTO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(140, $y);
				$pdf->MultiCell(70,5,trim($item['Objeto'],"\t\r"),0,"C",0);//OBJETO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(210, $y);
				$pdf->MultiCell(20,5,$item['Valor'],0,"C",0);//VALOR
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(230, $y);
				$pdf->MultiCell(15,5,$item['SARP'],0,"C",0);//SARP
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(245, $y);
				$pdf->MultiCell(45,5,trim($item['ProcLic'],"\t\r"),0,"C",0);//PROC LIC
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				
				if($ymax < 189.99){
					$ymax2 = $ymax - $y;
						
					$pdf->Rect(10, $y, 30, $ymax2);
					$pdf->Rect(40, $y, 30, $ymax2);
					$pdf->Rect(70, $y, 70, $ymax2);
					$pdf->Rect(140, $y, 70, $ymax2);
					$pdf->Rect(210, $y, 20, $ymax2);
					$pdf->Rect(230, $y, 15, $ymax2);
					$pdf->Rect(245, $y, 45, $ymax2);
						
					$pdf->SetY($ymax);
				}
				else{
					$range =  189.9900125 - $y;
						
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 70, $range);
					$pdf->Rect(140, $y, 70, $range);
					$pdf->Rect(210, $y, 20, $range);
					$pdf->Rect(230, $y, 15, $range);
					$pdf->Rect(245, $y, 45, $range);
						
					$pdf->AddPage();
						
					$range =  $ymax - 189.9900125;
						
					$pdf->SetY(39.00125);
					$y = $pdf->GetY();
						
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 70, $range);
					$pdf->Rect(140, $y, 70, $range);
					$pdf->Rect(210, $y, 20, $range);
					$pdf->Rect(230, $y, 15, $range);
					$pdf->Rect(245, $y, 45, $range);
				}/**/
				
				
				/*TESTE MULTICELL
				$y = $pdf->GetY();
				$ymax = 0;
				
				$pdf->SetXY(10, $y);
				$pdf->MultiCell(30,5,$item['SituacaoSCC'],0,"C",0);//SITUACAO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(40, $y);
				$pdf->MultiCell(30,5,$item['NumSCCData'],0,"C",0);//N SCC/DATA
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$imprimir = "";
				foreach($item['GrupoSub'] as $gruposub){
					$imprimir .= $gruposub."\n";
				}
				
				$pdf->SetXY(70, $y);
				$pdf->MultiCell(70,5,$imprimir,0,"C",0);//GRUPO/SUBELEMENTO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(140, $y);
				$pdf->MultiCell(70,5,$item['Objeto'],0,"C",0);//OBJETO
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(210, $y);
				$pdf->MultiCell(20,5,$item['Valor'],0,"C",0);//VALOR
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(230, $y);
				$pdf->MultiCell(15,5,$item['SARP'],0,"C",0);//SARP
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				$pdf->SetXY(245, $y);
				$pdf->MultiCell(45,5,$item['ProcLic'],0,"C",0);//PROC LIC
				$ymax = (($pdf->GetY() > $ymax)?$pdf->GetY():$ymax);
				
				
				if($ymax < 189){
					$ymax2 = $ymax - $y;
					
					$pdf->Rect(10, $y, 30, $ymax2);
					$pdf->Rect(40, $y, 30, $ymax2);
					$pdf->Rect(70, $y, 70, $ymax2);
					$pdf->Rect(140, $y, 70, $ymax2);
					$pdf->Rect(210, $y, 20, $ymax2);
					$pdf->Rect(230, $y, 15, $ymax2);
					$pdf->Rect(245, $y, 45, $ymax2);
					
					$pdf->SetY($ymax);
				}
				else{
					$range =  189.00125 - $y;
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 70, $range);
					$pdf->Rect(140, $y, 70, $range);
					$pdf->Rect(210, $y, 20, $range);
					$pdf->Rect(230, $y, 15, $range);
					$pdf->Rect(245, $y, 45, $range);
					
					$pdf->AddPage();
					
					$range =  $ymax - 189.00125;
					
					$pdf->SetY(39.00125);
					$y = $pdf->GetY();
					
					$pdf->Rect(10, $y, 30, $range);
					$pdf->Rect(40, $y, 30, $range);
					$pdf->Rect(70, $y, 70, $range);
					$pdf->Rect(140, $y, 70, $range);
					$pdf->Rect(210, $y, 20, $range);
					$pdf->Rect(230, $y, 15, $range);
					$pdf->Rect(245, $y, 45, $range);
				}
				/**/
				
				$tempOrg = $item['Orgao'];
			}//fim do foreach
			
			$pdf->SetFont("Arial", "B", 8);
			$pdf->Cell(130, 5, "TOTAL",1,0,"L",1);
			$pdf->SetFont("Arial", "", 8);
			$pdf->Cell(150, 5, converte_valor_estoques($totalOrg[$tempOrg]),1,1,"L",1);
			
			$pdf->SetFont("Arial", "B", 8);
			$pdf->Cell(130, 5, "TOTAL GERAL",1,0,"L",1);
			$pdf->SetFont("Arial", "", 8);
			$pdf->Cell(150, 5, converte_valor_estoques($totalGeral),1,1,"L",1);
		}

		$db->disconnect();
		$pdf->Output();
	}
	else{
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">A pesquisa não retornou resultados</a>";
		
		$Url = "CadRelatorioSARPSelecionar.php?Mens=$Mens&Mensagem=$Mensagem&Tipo=$Tipo";
			
		if (!in_array($Url,$_SESSION['GetUrl'])){
			$_SESSION['GetUrl'][] = $Url;
		}

		$db->disconnect();
		header("location: ".$Url);
		exit;
	}//EXCECAO GERADA CASO NAO SEJA RETORNADA NENHUM RESULTADO DA PESQUISA
}
?>