<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendRequisicaoMaterialPdf.php
# Autor:    Roberta Costa
# Data:     07/07/05
# Objetivo: Programa de Atendimento da Requisição de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Sequencial    = $_GET['Sequencial'];
		$AnoRequisicao = $_GET['AnoRequisicao'];
		$Almoxarifado  = $_GET['Almoxarifado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Acompanhamento de Requisição de Material";

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

# Pega os dados da Requisição de Material de acordo com o Sequencial #
$db   = Conexao();
$sql  = "SELECT A.CREQMACODI, A.CGREMPCODI, A.CUSUPOCODI, B.AITEMRQTSO, ";
$sql .= "       B.AITEMRQTAT, B.AITEMRORDE, C.CMATEPSEQU, C.EMATEPDESC, ";
$sql .= "       D.EUNIDMSIGL, A.DREQMADATA, A.EREQMAOBSE ";
$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
$sql .= "       SFPC.TBUNIDADEDEMEDIDA D  ";
$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $Sequencial ";
$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
$sql .= " ORDER BY B.AITEMRORDE ";
$res  = $db->query($sql);

if( PEAR::isError($res) ){
	  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Rows = $res->numRows();
		for( $i=0;$i<$Rows;$i++ ){
				$Linha              = $res->fetchRow();
				$Requisicao         = $Linha[0];
				$GrupoEmp           = $Linha[1];
				$Usuario            = $Linha[2];

      	$QtdSolicitada[$i]  = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[3])));
      	$QtdAtendida[$i]    = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[4])));
      	$Ordem[$i]          = $Linha[5];
				$Material[$i]       = $Linha[6];
				$DescMaterial[$i]   = $Linha[7];
				$DescUnidade[$i]    = $Linha[8];
  			$DataRequisicao     = DataBarra($Linha[9]);
  			$Observacao         = $Linha[10];

				if( $i == 0 ){
						# Carrega o centro de custo #
						$sqlcen  = "SELECT D.ECENPODESC, E.EORGLIDESC, D.ECENPODETA, D.CCENPONRPA ";
						$sqlcen .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBTIPOSITUACAOREQUISICAO C, ";
						$sqlcen .= "       SFPC.TBCENTROCUSTOPORTAL D, SFPC.TBORGAOLICITANTE E, SFPC.TBALMOXARIFADOORGAO F ";
						$sqlcen .= " WHERE A.CREQMASEQU = B.CREQMASEQU AND B.CTIPSRCODI = C.CTIPSRCODI ";
						$sqlcen .= "   AND A.CORGLICODI = D.CORGLICODI AND D.CORGLICODI = E.CORGLICODI ";
						$sqlcen .= "   AND A.CORGLICODI = F.CORGLICODI AND F.CALMPOCODI = $Almoxarifado ";
						$sqlcen .= "   AND D.CORGLICODI = F.CORGLICODI AND A.CCENPOSEQU = D.CCENPOSEQU ";
						$sqlcen .= "   AND A.CREQMASEQU = $Sequencial ";
						$rescen  = $db->query($sqlcen);
						if( PEAR::isError($rescen) ) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcen");
						}else{
								$Cen        			= $rescen->fetchRow();
  	      			$DescOrgao  			= $Cen[0];
  	      			$DescCentro       = $Cen[1];
  	      			$DescDetalhamento = $Cen[2];
  	      			$RPA              = $Cen[3];
						}
						$pdf->SetFont("Arial","",9);
						$pdf->Cell(35,5,"Centro de Custo",1,0,"L",1);
						$pdf->Cell(245,5,"$DescCentro - RPA $RPA - $DescOrgao - $DescDetalhamento",1,1,"L",0);

						$pdf->Cell(35,5,"Requisição",1,0,"L",1);
						$pdf->Cell(245,5,substr($Requisicao+100000,1)."/".$AnoRequisicao,1,1,"L",0);

						# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
						$sqlusu  = "SELECT USU.EUSUPOLOGI, USU.EUSUPORESP ";
						$sqlusu .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
						$sqlusu .= " WHERE SIT.CREQMASEQU = $Sequencial    AND SIT.CTIPSRCODI = 1 ";
						$sqlusu .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
						$resusu  = $db->query($sqlusu);
						if( PEAR::isError($resusu) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlusu");
						}else{
								$Usu         = $resusu->fetchRow();
								$NomeUsuario = $Usu[1];
						}
						$pdf->Cell(35,5,"Usuário Requisitante",1,0,"L",1);
						$pdf->Cell(245,5,$NomeUsuario,1,1,"L",0);

						# Carrega os dados da Última Situação da Requisicao #
						$sql  = "SELECT A.TSITREULAT, B.ETIPSRDESC, B.CTIPSRCODI, A.ESITREMOTI ";
						$sql .= "  FROM SFPC.TBSITUACAOREQUISICAO A, SFPC.TBTIPOSITUACAOREQUISICAO B ";
						$sql .= " WHERE A.CREQMASEQU = $Sequencial AND A.CTIPSRCODI = B.CTIPSRCODI ";
						$sql .= "   AND A.TSITREULAT =  ";
						$sql .= "      ( SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
						$sql .= "         WHERE CREQMASEQU = $Sequencial ) ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha        = $result->fetchRow();
								$DataSituacao = DataBarra($Linha[0]);
								$DescSituacao = $Linha[1];
								$Motivo       = $Linha[3];
						}
						$sql   = "   SELECT 	USU.EUSUPOLOGI, USU.EUSUPORESP, SIT.CREQMASEQU,SIT.CTIPSRCODI ";
						$sql   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT, SFPC.TBREQUISICAOMATERIAL MAT  ";
						$sql   .= "  WHERE 	SIT.CREQMASEQU = $Sequencial AND SIT.CREQMASEQU =MAT.CREQMASEQU AND SIT.CTIPSRCODI = 3 ";
						$sql   .= "  AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
						$sql   .= "  UNION SELECT USU.EUSUPOLOGI, USU.EUSUPORESP, SIT.CREQMASEQU,SIT.CTIPSRCODI ";
						$sql   .= "  FROM	SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT, SFPC.TBREQUISICAOMATERIAL MAT  ";
						$sql   .= "  WHERE 	SIT.CREQMASEQU = $Sequencial AND SIT.CREQMASEQU =MAT.CREQMASEQU AND SIT.CTIPSRCODI = 4 ";
						$sql   .= "  AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
		            		$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
								$NomeResp  = $Linha[1];
						}
						$pdf->Cell(35,5,"Data da Requisição",1,0,"L",1);
						$pdf->Cell(245,5,$DataRequisicao,1,1,"L",0);
						if($NomeResp!=""){
							$pdf->Cell(35,5,"Usuário Atendimento",1,0,"L",1);
							$pdf->Cell(245,5,$NomeResp,1,1,"L",0);
						}
						$pdf->Cell(35,5,"Situação",1,0,"L",1);
						$pdf->Cell(245,5,$DescSituacao,1,1,"L",0);
						$pdf->Cell(35,5,"Data da Situação",1,0,"L",1);
						$pdf->Cell(245,5,$DataSituacao,1,1,"L",0);
						if( $Motivo != "" ){
								$TamMotivo = $pdf->GetStringWidth($Motivo);
								if( $TamMotivo > 244 ){ $Altura = 10; }else{ $Altura = 5; }
								$pdf->Cell(35,$Altura,"Motivo",1,0,"L",1);
								$pdf->MultiCell(245,5,$Motivo,1,"L",0);
						}
						$TamObservacao = $pdf->GetStringWidth($Observacao);
						if( $TamObservacao > 244 ){ $Altura = 10; }else{ $Altura = 5; }
						$pdf->Cell(35,$Altura,"Observação",1,0,"L",1);
						if( $Observacao == "" ){ $Observacao = "NÃO INFORMADA"; }
						$pdf->MultiCell(245,5,$Observacao,1,"L",0);

						$pdf->ln(5);
						$pdf->Cell(280,5,"ITENS DA REQUISIÇÃO",1,1,'C',1);
				}
				if( $i == 0 ){
						$pdf->Cell(15,10,"ORDEM",1,0,"C",1);
						$pdf->Cell(177,10,"DESCRIÇÃO DO MATERIAL",1,0,"L",1);
						$pdf->Cell(12,10,"CÓD.",1,0,"C",1);						
						$pdf->Cell(8,10,"UNID",1,0,"C",1);
						$pdf->Cell(68,5,"QUANTIDADE",1,1,"C",1);
						$pdf->Cell(212,5,"",0,0,"C",0);
						$pdf->Cell(34,5,"SOLICITADA",1,0,"C",1);
						$pdf->Cell(34,5,"ATENDIDA",1,0,"C",1);
						$pdf->ln(5);
				}

				# Quebra de Linha para Descrição do Material #
				$TamDescMaterial = $pdf->GetStringWidth($DescMaterial[$i]);
				if( $TamDescMaterial <= 174 ){
						$LinhasMat = 1;
						$AlturaMat = 5;
				}elseif( $TamDescMaterial > 174 and $TamDescMaterial <= 352 ){
						$LinhasMat = 2;
						$AlturaMat = 10;
				}else{
						$LinhasMat = 3;
						$AlturaMat = 15;
				}
				$DescMaterial[$i] = SeparaFrase($DescMaterial[$i],90);
				if( $TamDescMaterial > 174 ){
						$Inicio = 0;
						$pdf->Cell(15,$AlturaMat,"",1,0,"L",0);
						$pdf->Cell(177,$AlturaMat,"",1,0,"L",0);
						$pdf->SetX(10);
						for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
								if( $Quebra == 0 ){
										$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
							  		$pdf->Cell(177,5,substr($DescMaterial[$i],0,90),0,0,"L",0);
										$pdf->Cell(12,$AlturaMat,$Material[$i],1,0,"C",0);							  		
										$pdf->Cell(8,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
										if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }
										$pdf->Cell(34,$AlturaMat,$QtdSolicitada[$i],1,0,"R",0);
										if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
										$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
										$pdf->Ln(5);
								}elseif( $Quebra == 1 ){
										$pdf->Cell(15,5,"",$Borda,0,"R",0);
							  		$pdf->Cell(177,5,trim(substr($DescMaterial[$i],$Inicio,90)),$Borda,0,"L",0);
							  		$pdf->Ln(5);
							  }else{
										$pdf->Cell(15,5,"",$Borda,0,"R",0);
							  		$pdf->Cell(177,5,trim(substr($DescMaterial[$i],$Inicio,90)),$Borda,0,"L",0);
							  		$pdf->Ln(5);
							  }
								$Inicio = $Inicio + 90;
					  }
					  $pdf->Cell(194,0,"",1,1,"",0);
				}else{
						$pdf->Cell(15,5,$Ordem[$i],1,0,"R",0);
				  	$pdf->Cell(177,5,trim($DescMaterial[$i]),1,0,"L",0);
						$pdf->Cell(12,5,$Material[$i],1,0,"C",0);							  		
						$pdf->Cell(8,5,$DescUnidade[$i],1,0,"C",0);
						if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }
						$pdf->Cell(34,5,$QtdSolicitada[$i],1,0,"R",0);
						if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
						$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
