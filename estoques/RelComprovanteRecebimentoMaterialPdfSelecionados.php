<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelComprovanteRecebimentoMaterialPdfSelecionados.php
# Autor:    Marcus Thiago
# Data:     12/01/2006
# Alterado: Álvaro Faria
# Data:     15/08/2006 - Exibição do código reduzido do material
# Alterado: Álvaro Faria
# Data:     18/08/2006 - Mudanças do layout do rodapé / OBS Completa
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Padronização de variáveis de requisição
# Alterado: Álvaro Faria
# Data:     27/11/2006 - Alteração de layout do cabeçalho e do rodapé
# Objetivo: Programa de Atendimento da Requisição de Material
# OBS.:     Tabulação 2 espaços
#           As variáveis de SESSÃO são para o rodapé
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelComprovanteRecebimentoMaterial.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$AnoRequisicao = $_GET['AnoRequisicao'];
		$Almoxarifado  = $_GET['Almoxarifado'];
		$Quantidade    = $_GET['Quantidade'];
		$Varios        = $_GET['Varios'];
		$Motorista     = $_GET['Motorista'];
}

$Varios = explode("_",$Varios);
$QtdLinhas = count($Varios);

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagemComprovante();

# Informa o Título do Relatório #
$TituloRelatorio = "Comprovante de Entrega de Material";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Muda o tamanho do Rodapé #
$pdf->SetAutoPageBreak(false,60);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$db   = Conexao();
for( $T=0; $T< $QtdLinhas; $T++ ){
		$SeqRequisicao = $Varios[$T];
		# Adiciona uma página no documento #
		$pdf->AddPage();

		# Pega as quantidades atendidas da Requisição de Material de acordo com o Sequencial, para verificar se o relatório deve ser exibido ou emitir mensagem de nenhum item atendido #
		$sqlteste  = "SELECT B.AITEMRQTAT ";
		$sqlteste .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
		$sqlteste .= "       SFPC.TBUNIDADEDEMEDIDA D  ";
		$sqlteste .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $SeqRequisicao ";
		$sqlteste .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sqlteste .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
		if( $Quantidade == "A" ){
				$sqlteste .= "  AND B.AITEMRQTAT <> 0 AND B.AITEMRQTAT IS NOT NULL ";
		}
		$sqlteste .= " ORDER BY B.AITEMRORDE ";
		$resteste  = $db->query($sqlteste);
		if( PEAR::isError($resteste) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlteste");
		}else{
				$RowsTeste = $resteste->numRows();
				for( $i=0;$i<$RowsTeste;$i++ ){
						$LinhaTeste       = $resteste->fetchRow();
						$QtdAtendidaTeste = $LinhaTeste[0];
						if ($QtdAtendidaTeste != 0) {
								$FlagItemAtendido = 1;
						}
				}
		}
		$db->disconnect();

		# Início do Cabeçalho Móvel #

		if ($FlagItemAtendido == 1) {
				$db   = Conexao();
				# Pega os dados do almoxarifado #
				$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
				$res = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Campo            = $res->fetchRow();
						$DescAlmoxarifado = $Campo[0];
				}

				# Pega os dados da Requisição de Material de acordo com o Sequencial #
				$sql  = "SELECT A.CREQMACODI, A.CGREMPCODI, A.CUSUPOCODI, B.AITEMRQTSO, ";
				$sql .= "       B.AITEMRQTAT, B.AITEMRORDE, C.CMATEPSEQU, C.EMATEPDESC, ";
				$sql .= "       D.EUNIDMSIGL, A.DREQMADATA, A.EREQMAOBSE ";
				$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
				$sql .= "       SFPC.TBUNIDADEDEMEDIDA D ";
				$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $SeqRequisicao ";
				$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
				$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
				if( $Quantidade == "A" ){
						$sql .= "  AND B.AITEMRQTAT <> 0 AND B.AITEMRQTAT IS NOT NULL ";
				}
				$sql .= " ORDER BY B.AITEMRORDE ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						for( $i=0;$i<$Rows;$i++ ){
								$_SESSION['AnoRequisicao'] = $AnoRequisicao;
								$Linha                     = $res->fetchRow();
								$_SESSION['Requisicao']    = $Linha[0];
								$GrupoEmp                  = $Linha[1];
								$Usuario                   = $Linha[2];
								if($Quantidade != "A"){
										$QtdSolicitada[$i]  = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[3])));
								}
								$QtdAtendida[$i]        = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[4])));
								$Ordem[$i]              = $Linha[5];
								$Material[$i]           = $Linha[6];
								$DescMaterial[$i]       = $Linha[7];
								$DescUnidade[$i]        = $Linha[8];
								$DataRequisicao         = DataBarra($Linha[9]);
								$_SESSION['Observacao'] = $Linha[10];

								if( $i == 0 ){
										# Carrega o centro de custo #
										$sqlcen  = "SELECT E.EORGLIDESC, D.ECENPODESC, D.ECENPODETA, D.CCENPONRPA ";
										$sqlcen .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBTIPOSITUACAOREQUISICAO C, ";
										$sqlcen .= "       SFPC.TBCENTROCUSTOPORTAL D, SFPC.TBORGAOLICITANTE E, SFPC.TBALMOXARIFADOORGAO F ";
										$sqlcen .= " WHERE A.CREQMASEQU = B.CREQMASEQU AND B.CTIPSRCODI = C.CTIPSRCODI ";
										$sqlcen .= "   AND A.CORGLICODI = D.CORGLICODI AND D.CORGLICODI = E.CORGLICODI ";
										$sqlcen .= "   AND A.CORGLICODI = F.CORGLICODI AND F.CALMPOCODI = $Almoxarifado ";
										$sqlcen .= "   AND D.CORGLICODI = F.CORGLICODI AND A.CCENPOSEQU = D.CCENPOSEQU ";
										$sqlcen .= "   AND A.CREQMASEQU = $SeqRequisicao ";
										$rescen  = $db->query($sqlcen);
										if( PEAR::isError($rescen) ) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcen");
										}else{
												$Cen                    = $rescen->fetchRow();
												$_SESSION['DescOrgao']  = $Cen[0];
												$_SESSION['DescCentro'] = $Cen[1];
												$_SESSION['DescDeta']   = $Cen[2];
												$_SESSION['RPA']        = $Cen[3]; //Pega o valor da RPA - Marcus Thiago
										}
										$pdf->SetFont("Arial","",9);

										$pdf->Cell(26,5,"Almoxarifado",1,0,"L",1);
										$pdf->Cell(105,5,substr($DescAlmoxarifado,0,52),1,0,"L",0);

										$pdf->Cell(12,5,"Órgão",1,0,"L",1);
										$pdf->Cell(124,5,substr($_SESSION['DescOrgao'],0,62),1,0,"L",0);

										$pdf->Cell(9,5,"RPA",1,0,"L",1);
										$pdf->Cell(4,5,$_SESSION['RPA'],1,1,"L",0);

										$pdf->Cell(26,5,"Centro de Custo",1,0,'L',1);
										$pdf->Cell(254,5,substr($_SESSION['DescCentro'],0,65)."- ".substr($_SESSION['DescDeta'],0,65),1,1,'L',0);

										$pdf->Cell(26,5,"Requisição",1,0,'L',1);
										$pdf->Cell(22,5,substr($_SESSION['Requisicao']+100000,1)."/".$_SESSION['AnoRequisicao'],1,0,'L',0);

										# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
										$sqlusu    = "SELECT USU.EUSUPOLOGI, USU.EUSUPORESP ";
										$sqlusu   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
										$sqlusu   .= " WHERE SIT.CREQMASEQU = $SeqRequisicao AND SIT.CTIPSRCODI = 1 ";
										$sqlusu   .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
										$resusu  = $db->query($sqlusu);
										if( PEAR::isError($resusu) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlusu");
										}else{
												$Usu         = $resusu->fetchRow();
												$NomeUsuario = $Usu[1];
										}
										$pdf->Cell(33,5,"Usuário Requisitante",1,0,'L',1);
										$pdf->Cell(148,5,$NomeUsuario,1,0,'L',0);

										# Carrega os dados da Última Situação da Requisicao #
										$sql  = "SELECT A.TSITREULAT, B.ETIPSRDESC, B.CTIPSRCODI ";
										$sql .= "  FROM SFPC.TBSITUACAOREQUISICAO A, SFPC.TBTIPOSITUACAOREQUISICAO B ";
										$sql .= " WHERE A.CREQMASEQU = $SeqRequisicao AND A.CTIPSRCODI = B.CTIPSRCODI ";
										$sql .= "   AND A.TSITREULAT =  ";
										$sql .= "      ( SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
										$sql .= "         WHERE CREQMASEQU = $SeqRequisicao ) ";
										$result = $db->query($sql);
										if( PEAR::isError($result) ) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
												$DataSituacao = DataBarra($Linha[0]);
												$DescSituacao   = $Linha[1];
										}
										$pdf->Cell(31,5,"Data da Requisição",1,0,'L',1);
										$pdf->Cell(20,5,$DataRequisicao,1,0,'L',0);
										
										$pdf->ln(5);
										$pdf->Cell(280,5,"ITENS DA REQUISIÇÃO",1,1,'C',1);
								}
								# Fim do Cabeçalho Móvel#

								# Inicio dos Itens da Requisição #
								if($i == 0){
										if($Quantidade == "A"){
												$pdf->Cell(15,5,"ORDEM",1,0,"C",1);
												$pdf->Cell(211,5,"CÓDIGO REDUZIDO / DESCRIÇÃO DO MATERIAL",1,0,"L",1);
												$pdf->Cell(20,5,"UNIDADE",1,0,"C",1);
												$pdf->Cell(34,5,"QUANTIDADE",1,1,"C",1);
										}else{
												$pdf->Cell(15,10,"ORDEM",1,0,"C",1);
												$pdf->Cell(177,10,"CÓDIGO REDUZIDO / DESCRIÇÃO DO MATERIAL",1,0,"L",1);
												$pdf->Cell(20,10,"UNIDADE",1,0,"C",1);
												$pdf->Cell(68,5,"QUANTIDADE",1,1,"C",1);
												$pdf->Cell(212,5,"",0,0,"C",0);
												$pdf->Cell(34,5,"SOLICITADA",1,0,"C",1);
												$pdf->Cell(34,5,"ATENDIDA",1,0,"C",1);
												$pdf->ln(5);
										}
								}
								# Quebra de Linha para Descrição do Material #
								$LocalY=$pdf->GetY();
								if($LocalY < 150){
										$DescMaterialSepara = SeparaFrase($Material[$i]." / ".$DescMaterial[$i],90);
										$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
										if($TamDescMaterial <= 174){
												$LinhasMat = 1;
												$AlturaMat = 5;
										}elseif($TamDescMaterial > 174 and $TamDescMaterial <= 352){
												$LinhasMat = 2;
												$AlturaMat = 10;
										}else{
												$LinhasMat = 3;
												$AlturaMat = 15;
										}
										if($TamDescMaterial > 174){
												$Inicio = 0;
												$pdf->Cell(15,$AlturaMat,"",1,0,"L",0);
												for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
														if($Quebra == 0){
																if($Quantidade == "A"){
																		$pdf->Cell(211,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(211,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}else{
																		$pdf->Cell(177,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(177,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if($QtdSolicitada[$i] == ""){ $QtdSolicitada[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdSolicitada[$i],1,0,"R",0);
																		if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}
														}elseif($Quebra == 1){
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(177,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}else{
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(211,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}
														$Inicio = $Inicio + 90;
												}
												$pdf->Cell(226,0,"",1,1,"",0);
										}else{
												$pdf->Cell(15,5,$Ordem[$i],1,0,"R",0);
												if($Quantidade == "A"){
														$pdf->Cell(211,5,trim($DescMaterialSepara),1,0,"L",0);
												}else{
														$pdf->Cell(177,5,trim($DescMaterialSepara),1,0,"L",0);
												}
												$pdf->Cell(20,5,$DescUnidade[$i],1,0,"C",0);
												if($Quantidade == "A"){
														if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}else{
														if($QtdSolicitada[$i] == ""){ $QtdSolicitada[$i] = "0"; }
														$pdf->Cell(34,5,$QtdSolicitada[$i],1,0,"R",0);
														if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}
										}
								}elseif($LocalY < 200){
										$DescMaterialSepara = SeparaFrase($Material[$i]." / ".$DescMaterial[$i],90);
										$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
										if($TamDescMaterial <= 174){
												$LinhasMat = 1;
												$AlturaMat = 5;
										}elseif($TamDescMaterial > 174 and $TamDescMaterial <= 352){
												$LinhasMat = 2;
												$AlturaMat = 10;
										}else{
												$LinhasMat = 3;
												$AlturaMat = 15;
										}
										if($TamDescMaterial > 174){
												$Inicio = 0;
												$pdf->Cell(15,$AlturaMat,"",1,0,"L",0);
												for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
														if($Quebra == 0){
																if($Quantidade == "A"){
																		$pdf->Cell(211,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(211,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}else{
																		$pdf->Cell(177,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(177,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdSolicitada[$i],1,0,"R",0);
																		if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}
														}elseif($Quebra == 1){
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(177,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}else{
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(211,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}
														$Inicio = $Inicio + 90;
												}
												$pdf->Cell(226,0,"",1,1,"",0);
										}else{
												$pdf->Cell(15,5,$Ordem[$i],1,0,"R",0);
												if($Quantidade == "A" ){
														$pdf->Cell(211,5,trim($DescMaterialSepara),1,0,"L",0);
												}else{
														$pdf->Cell(177,5,trim($DescMaterialSepara),1,0,"L",0);
												}
												$pdf->Cell(20,5,$DescUnidade[$i],1,0,"C",0);
												if($Quantidade == "A"){
														if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}else{
														if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }
														$pdf->Cell(34,5,$QtdSolicitada[$i],1,0,"R",0);
														if( $QtdAtendida[$i] == "" ){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}
										}
										$pdf->AddPage();
								}else{
										$pdf->AddPage();
										$DescMaterialSepara = SeparaFrase($Material[$i]." - ".$DescMaterial[$i],90);
										$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
										if($TamDescMaterial <= 174){
												$LinhasMat = 1;
												$AlturaMat = 5;
										}elseif( $TamDescMaterial > 174 and $TamDescMaterial <= 352 ){
												$LinhasMat = 2;
												$AlturaMat = 10;
										}else{
												$LinhasMat = 3;
												$AlturaMat = 15;
										}
										if($TamDescMaterial > 174){
												$Inicio = 0;
												$pdf->Cell(15,$AlturaMat,"",1,0,"L",0);
												for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
														if($Quebra == 0){
																if($Quantidade == "A"){
																		$pdf->Cell(211,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(211,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}else{
																		$pdf->Cell(177,$AlturaMat,"",1,0,"L",0);
																		$pdf->SetX(10);
																		$pdf->Cell(15,$AlturaMat,$Ordem[$i],0,0,"R",0);
																		$pdf->Cell(177,5,substr($DescMaterialSepara,0,90),0,0,"L",0);
																		$pdf->Cell(20,$AlturaMat,$DescUnidade[$i],1,0,"C",0);
																		if($QtdSolicitada[$i] == ""){ $QtdSolicitada[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdSolicitada[$i],1,0,"R",0);
																		if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
																		$pdf->Cell(34,$AlturaMat,$QtdAtendida[$i],1,0,"R",0);
																		$pdf->Ln(5);
																}
														}elseif($Quebra == 1){
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(177,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}else{
																$pdf->Cell(15,5,"",0,0,"R",0);
																$pdf->Cell(211,5,trim(substr($DescMaterialSepara,$Inicio,90)),0,0,"L",0);
																$pdf->Ln(5);
														}
														$Inicio = $Inicio + 90;
												}
												$pdf->Cell(226,0,"",1,1,"",0);
										}else{
												$pdf->Cell(15,5,$Ordem[$i],1,0,"R",0);
												if($Quantidade == "A"){
														$pdf->Cell(211,5,trim($DescMaterialSepara),1,0,"L",0);
												}else{
														$pdf->Cell(177,5,trim($DescMaterialSepara),1,0,"L",0);
												}
												$pdf->Cell(20,5,$DescUnidade[$i],1,0,"C",0);
												if($Quantidade == "A"){
														if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}else{
														if($QtdSolicitada[$i] == ""){ $QtdSolicitada[$i] = "0"; }
														$pdf->Cell(34,5,$QtdSolicitada[$i],1,0,"R",0);
														if($QtdAtendida[$i] == ""){ $QtdAtendida[$i] = "0"; }
														$pdf->Cell(34,5,$QtdAtendida[$i],1,1,"R",0);
												}
										}
								}
								# Fim dos Itens #
						}

						if($Motorista == "N"){
								$pdf->SetY(-37);
								$pdf->Cell(20,5,"Almoxarife",1,0,"L",1);
								$pdf->Cell(152,5,"",1,0,"L",0);
								$pdf->Cell(18,5,"Assinatura",1,0,"L",1);
								$pdf->Cell(50,5,"",1,0,"L",0);
								$pdf->Cell(11,5,"Data",1,0,"L",1);
								$pdf->Cell(29,5,"        /        /        ",1,1,"L",0);

								$pdf->Cell(280,5,"Recebi o material acima descriminado",1,1,"C",0);

								$pdf->Cell(20,5,"Recebedor",1,0,"L",1);
								$pdf->Cell(92,5,"",1,0,"L",0);
								$pdf->Cell(32,5,"Matrícula/Identidade",1,0,"L",1);
								$pdf->Cell(28,5,"",1,0,"L",0);
								$pdf->Cell(18,5,"Assinatura",1,0,"L",1);
								$pdf->Cell(50,5,"",1,0,"L",0);
								$pdf->Cell(11,5,"Data",1,0,"L",1);
								$pdf->Cell(29,5,"       /        /      ",1,1,"L",0);

								if(strlen($_SESSION['Observacao']) > 106){
										$OBS1 = substr($_SESSION['Observacao'],0,106);
										$Posicao = strrpos($OBS1," ");
										if(!$Posicao) $Posicao = 106;
										$OBS1 = substr($_SESSION['Observacao'],0,$Posicao);
										$OBS2 = trim(substr($_SESSION['Observacao'],$Posicao));
								}else{
										$OBS1 = $_SESSION['Observacao'];
								}
								$pdf->Cell(20,5,"Observação","LRT",0,"L",1);
								$pdf->Cell(260,5,$OBS1,1,1,"L",0);
								$pdf->Cell(20,5,"","LRB",0,"L",1);
								$pdf->Cell(260,5,$OBS2,1,0,"L",0);

								$pdf->SetY(-10);
								$pdf->Cell(260,6,"Emissão: ".date("d/m/Y H:i:s"),0,0,"L");
								$pdf->Line(10,205,290,205);
						}else{
								$pdf->SetY(-57);
								$pdf->Cell(20,5,"Almoxarife",1,0,"L",1);
								$pdf->Cell(152,5,"",1,0,"L",0);
								$pdf->Cell(18,5,"Assinatura",1,0,"L",1);
								$pdf->Cell(50,5,"",1,0,"L",0);
								$pdf->Cell(11,5,"Data",1,0,"L",1);
								$pdf->Cell(29,5,"        /        /        ",1,1,"L",0);

								$pdf->Cell(280,5,"Recebi o material acima descriminado",1,1,"C",0);

								$pdf->Cell(20,5,"Recebedor",1,0,"L",1);
								$pdf->Cell(92,5,"",1,0,"L",0);
								$pdf->Cell(32,5,"Matrícula/Identidade",1,0,"L",1);
								$pdf->Cell(28,5,"",1,0,"L",0);
								$pdf->Cell(18,5,"Assinatura",1,0,"L",1);
								$pdf->Cell(50,5,"",1,0,"L",0);
								$pdf->Cell(11,5,"Data",1,0,"L",1);
								$pdf->Cell(29,5,"       /        /      ",1,1,"L",0);

								if(strlen($_SESSION['Observacao']) > 106){
										$OBS1 = substr($_SESSION['Observacao'],0,106);
										$Posicao = strrpos($OBS1," ");
										if(!$Posicao) $Posicao = 106;
										$OBS1 = substr($_SESSION['Observacao'],0,$Posicao);
										$OBS2 = trim(substr($_SESSION['Observacao'],$Posicao));
								}else{
										$OBS1 = $_SESSION['Observacao'];
								}
								$pdf->Cell(20,5,"Observação","LRT",0,"L",1);
								$pdf->Cell(260,5,$OBS1,1,1,"L",0);
								$pdf->Cell(20,5,"","LRB",0,"L",1);
								$pdf->Cell(260,5,$OBS2,1,0,"L",0);

								$pdf->SetY(-30);
								$pdf->Cell(260,6,"Emissão: ".date("d/m/Y H:i:s"),0,0,"L");
								$pdf->Line(10,185,290,185);

								$pdf->SetY(-20);
								$pdf->Cell(20,5,"Motorista",1,0,"L",1);
								$pdf->Cell(152,5,"",1,0,"L",0);
								$pdf->Cell(18,5,"Assinatura",1,0,"L",1);
								$pdf->Cell(50,5,"",1,0,"L",0);
								$pdf->Cell(11,5,"Placa",1,0,"L",1);
								$pdf->Cell(29,5,"",1,0,"L",0);
						}
				}
				$sql  = "UPDATE  SFPC.TBREQUISICAOMATERIAL ";
				$sql .= " SET FREQMACOMP = 'S' ";  
				$sql .= " WHERE CREQMASEQU = $SeqRequisicao ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}
		}
}
$db->disconnect();
$pdf->Output();

function CabecalhoRodapePaisagemComprovante(){
	# Classes FPDF #
	class PDF extends FPDF {
		# Cabeçalho #
		function Header() {
			##### Verificar endereço quando passar para produção #####
			/*
			Global $CaminhoImagens;
			$this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
			$this->SetFont("Arial","B",10);
			$this->Cell(0,20,"Prefeitura do Recife",0,0,"L");
			$this->Cell(0,20,"EMPREL",0,0,"R");
			$this->Ln(1);
			$Empresa = $_SESSION['_egruatdesc_'];
			$this->Cell(0,25,"Secretaria de Financas",0,0,"L");
			$this->Cell(0,25,"Diretoria Geral de Compras",0,0,"R");
			$this->Ln(1);
			$this->Cell(0,30,"Portal de Compras",0,0,"L");
			$this->Cell(0,30,"",0,0,"R");
			$this->Ln(1);
			$this->Line(10,30,290,30);
			$this->Cell(0,39,$GLOBALS['TituloRelatorio'],0,0,"C");
			$this->Ln(1);
			$this->Line(10,36,290,36);
			$this->Ln(25);
			*/
		    
		    Global $CaminhoImagens;
		    $cabecalho = retornaCabecalho();
		    $this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
		    $this->SetFont("Arial","B",10);
		    $this->Cell(0,20,"$cabecalho[empresa]",0,0,"L");
		    $this->Cell(0,20,"$cabecalho[orgao1]",0,0,"R");
		    $this->Ln(1);
		    //$Empresa = $_SESSION['_egruatdesc_'];
		    $this->Cell(0,25,"$cabecalho[orgao2]",0,0,"L");
		    $this->Cell(0,25,"$cabecalho[setor1]",0,0,"R");
		    $this->Ln(1);
		    $this->Cell(0,30,"$cabecalho[nomesistema]",0,0,"L");
		    $this->Cell(0,30,"",0,0,"R");
		    $this->Ln(1);
		    $this->Line(10,30,290,30);
		    $this->Cell(0,39,$GLOBALS['TituloRelatorio'],0,0,"C");
		    $this->Ln(1);
		    $this->Line(10,36,290,36);
		    $this->Ln(25);
		}
	}
}
?>
