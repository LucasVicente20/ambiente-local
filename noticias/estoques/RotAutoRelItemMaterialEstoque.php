<?php
# ------------------------------------------------------------------------------------
# Portal da DGCO
# Programa:   RotAutoRelItemMaterialEstoque.php
# Autor:      Álvaro Faria
# Data:       07/11/2006
# Objetivo:   Programa de arquivamento dos itens em estoque em Acrobat para Crontab
#             Será rodado pelo Crontab nos primeiros minutos do dia 1 de cada mês
# OBS.:       Tabulação 2 espaços
#             Formatação do arquivo gerado: RELITEMESTOQUE_$ANO_$MÊS_$ALMOXARIFADO.pdf
#             Este programa não está sendo usado diretamente pelo sistema. Está servindo
#             para garantir mensalmente as informações de estoque dos almoxarifados 
# ------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/funcoes.php";

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Definição dos padrões de geração do relatório #
$Ordem     =  2 ; # 1 - Família / 2 - Material
$ExibirLoc = "N"; # S - Sim / N - Não
$ExibirZer = "S"; # S - Sim / N - Não

# Definindo data #
$DataAtual = date("d/m/Y");
$DataAtual = explode("/",$DataAtual);
$DiaAtual  = $DataAtual[0];
$MesAtual  = $DataAtual[1];
$AnoAtual  = $DataAtual[2];
if($MesAtual == 1){       // Se o mês for janeiro, o ano será o anterior
		$Ano = $AnoAtual - 1; // E o mês, o último do ano anterior, ou seja 12 (dezembro)
		$Mes = 12;
}else{
		$Ano = $AnoAtual;     // Se não for janeiro, o ano será o atual
		$Mes = $MesAtual - 1; // E o mês, o anterior
}
# Descobre os útimos dias dos meses #
$Dias1 = array("31","28","31","30","31","30","31","31","30","31","30","31");
$Dias2 = array("31","29","31","30","31","30","31","31","30","31","30","31");
if($Ano%4 == 0 ){
		$Dia = $Dias2[$Mes-1];
}else{
		$Dia = $Dias1[$Mes-1];
}
$Data = sprintf("%02d",$Dia)."/".sprintf("%02d",$Mes)."/".sprintf("%04d",$Ano);

# Informa o Título do Relatório #
if($Ordem == "1"){
		$TituloRelatorio = "Relatório de Itens de Material em Estoque em ".$Data." (Ordem: Família";
}else{
		$TituloRelatorio = "Relatório de Itens de Material em Estoque em ".$Data." (Ordem: Material";
}
if($ExibirZer == 'N') $TituloRelatorio .= ", Opção: Não exibir itens zerados";
elseif($ExibirZer == 'S') $TituloRelatorio .= ", Opção: Exibir itens zerados";
$TituloRelatorio .= ")";

# BUSCA INFORMAÇÕES NO BANCO #
# Conecta ao banco de dados #
$db = Conexao();
# Busca todos os almoxarifados cadastrados na SFPC.TBALMOXARIFADOPORTAL #
$sqlalmo  = "SELECT CALMPOCODI, EALMPODESC ";
$sqlalmo .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
$sqlalmo .= " ORDER BY CALMPOCODI ";
$resalmo = $db->query($sqlalmo);
if( db::isError($resalmo) ){
		$CodErroEmail  = $resalmo->getCode();
		$DescErroEmail = $resalmo->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo\n\n$DescErroEmail ($CodErroEmail)");
}else{
		CabecalhoRodapePaisagem();
		while($Almox = $resalmo->fetchRow() ){
				$Almoxarifado = $Almox[0];
				$DescAlmox    = $Almox[1];
				# Gera nome de arquivo para este almoxarifado #
				$NomeArquivo  = "imagemrel/RELITEMESTOQUE_";
				$NomeArquivo .= sprintf("%04d",$Ano);
				$NomeArquivo .= "_";
				$NomeArquivo .= sprintf("%02d",$Mes);
				$NomeArquivo .= "_".$Almoxarifado.".pdf";
				# Verifica se o arquivo para este mês e para este almoxarifado já foi criado #
				# Função exibe o Cabeçalho e o Rodapé #
				if(!file_exists($NomeArquivo)){
						# Se não foi criado, INICIA UM PDF PARA ESTE ALMOXARIFADO #
						# Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
						$pdf = new PDF("L","mm","A4");
						# Define um apelido para o número total de páginas #
						$pdf->AliasNbPages();
						# Define as cores do preenchimentos que serão usados #
						$pdf->SetFillColor(220,220,220);
						# Adiciona uma página no documento #
						$pdf->AddPage();
						# Seta as fontes que serão usadas na impressão de strings #
						$pdf->SetFont("Arial","",9);

						$sql2  = " SELECT SUM(ARM.AARMATQTDE*ARM.VARMATUMED), SUM(ARM.AARMATQTDE)";
						$sql2 .= "   FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
						$sql2 .= "        SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC ";
						$sql2 .= "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
						$sql2 .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
						$res2 = $db->query($sql2);
						if( db::isError($res2) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql2");
						}else{
								$LinhaTotal = $res2->fetchRow();
								$Totalizador = $LinhaTotal[0];
								$QtdTotal    = $LinhaTotal[1];
								$Totalizador = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Totalizador)));
						}

						# Resgata os intens do estoque #
						$select  = " SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ARM.AARMATQTDE, ";
						$select .= "        ARM.VARMATUMED, LOC.FLOCMAEQUI, LOC.ALOCMANEQU, LOC.ALOCMAPRAT, ";
						$select .= "        LOC.ALOCMACOLU, LOC.CARLOCCODI ";
						$from    = "   FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
						$from   .= "        SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC ";
						$where   = "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
						$where  .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
						if( $Ordem == 1 ){
								$select .= "       , GRU.EGRUMSDESC, CLA.ECLAMSDESC ";
								$from   .= "       , SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
								$from   .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
								$where  .= "   AND GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CCLAMSCODI = SUB.CCLAMSCODI ";
								$where  .= "   AND CLA.CGRUMSCODI = SUB.CGRUMSCODI AND SUB.CSUBCLSEQU = SUB.CSUBCLSEQU ";
								$where  .= "   AND SUB.CSUBCLSEQU = MAT.CSUBCLSEQU ";
								$order   = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, MAT.EMATEPDESC ";
						}else{
								$order   = " ORDER BY MAT.EMATEPDESC ";
						}
						$sql = $select.$from.$where.$order;
						$res = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
						}else{
								$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
								$pdf->Cell(250,5,$DescAlmox,1,0,"L",0);
								$pdf->ln(8);
						
								# Linhas de Itens de Material #
								$rows = $res->numRows();
								if($rows != 0){
										for($i=0; $i< $rows; $i++){
												$Linha = $res->fetchRow();
												$CodigoReduzido[$i] = $Linha[0];
												$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
												$Unidade[$i]        = $Linha[2];
												$QtdEstoque[$i]     = $Linha[3];
												$ValorUnitario[$i]  = $Linha[4];
												$Equipamento[$i]    = $Linha[5];
												$NumEquipamento[$i] = $Linha[6];
												$Prateleira[$i]     = $Linha[7];
												$Coluna[$i]         = $Linha[8];
												$Area[$i]           = $Linha[9];

												# Montando o array de Itens do Estoque #
												if($Ordem == 1){
														if(  ($ExibirZer == "S") or ( ($ExibirZer == "N") and ($QtdEstoque[$i] > 0) )  ){
																$DescGrupo[$i]  = RetiraAcentos($Linha[10]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[10]);
																$DescClasse[$i] = RetiraAcentos($Linha[11]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[11]);
																$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstoque[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$Equipamento[$i].$SimboloConcatenacaoArray.$NumEquipamento[$i].$SimboloConcatenacaoArray.$Prateleira[$i].$SimboloConcatenacaoArray.$Coluna[$i].$SimboloConcatenacaoArray.$Area[$i];
																$TotalItens     = $TotalItens + 1;
														}
												}else{
														if(  ($ExibirZer == "S") or ( ($ExibirZer == "N") and ($QtdEstoque[$i] > 0) )  ){
																$Itens[$i]  = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstoque[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$Equipamento[$i].$SimboloConcatenacaoArray.$NumEquipamento[$i].$SimboloConcatenacaoArray.$Prateleira[$i].$SimboloConcatenacaoArray.$Coluna[$i].$SimboloConcatenacaoArray.$Area[$i];
																$TotalItens = $TotalItens + 1;
														}
												}
										}

										# Escrevendo o Relatório #
										$DescGrupoAntes  = "";
										$DescCalsseAntes = "";
										sort($Itens);
										for($i=0; $i< count($Itens); $i++){
												# Extrai os dados do Array de Itens #
												$Dados = split($SimboloConcatenacaoArray,$Itens[$i]);
												if($Ordem == 1){
														$DescGrupo      = $Dados[0];
														$DescClasse     = $Dados[1];
														$DescMaterial   = $Dados[2];
														$CodigoReduzido = $Dados[3];
														$Unidade        = $Dados[4];
														$QtdEstoque     = $Dados[5];
														$ValorUnitario  = $Dados[6];
														$Equipamento    = $Dados[7];
														$NumEquipamento = $Dados[8];
														$Prateleira     = $Dados[9];
														$Coluna         = $Dados[10];
														$Area           = $Dados[11];
														$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;

														# Pega a descrição do Grupo com acento #
														$DescricaoG = split($SimboloConcatenacaoDesc,$DescGrupo);
														$DescGrupo = $DescricaoG[1];

														# Pega a descrição do Grupo com acento #
														$DescricaoC = split($SimboloConcatenacaoDesc,$DescClasse);
														$DescClasse = $DescricaoC[1];
														if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
																$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
																$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
																if($ExibirLoc == "S"){
																		$pdf->Cell(152,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
																		$pdf->Cell(9,5,"UND",1, 0,"C",1);
																		$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
																		$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR UNIT",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR TOTAL",1,0,"C",1);
																		$pdf->Cell(29,5,"LOCALIZAÇÃO",1,1,"C",1);
																}else{
																		$pdf->Cell(181,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
																		$pdf->Cell(9,5,"UND",1, 0,"C",1);
																		$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
																		$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR UNIT",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR TOTAL",1,1,"C",1);
																}
														}
														$DescGrupoAntes     = $DescGrupo;
														$DescClasseAntes    = $DescClasse;
												}else{
														$DescMaterial   = $Dados[0];
														$CodigoReduzido = $Dados[1];
														$Unidade        = $Dados[2];
														$QtdEstoque     = $Dados[3];
														$ValorUnitario  = $Dados[4];
														$Equipamento    = $Dados[5];
														$NumEquipamento = $Dados[6];
														$Prateleira     = $Dados[7];
														$Coluna         = $Dados[8];
														$Area           = $Dados[9];
														$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;
														if($i == 0){
																if($ExibirLoc == "S"){
																		$pdf->Cell(152,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
																		$pdf->Cell(9,5,"UND",1, 0,"C",1);
																		$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
																		$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR UNIT",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR TOTAL",1,0,"C",1);
																		$pdf->Cell(29,5,"LOCALIZAÇÃO",1,1,"C",1);
																}else{
																		$pdf->Cell(181,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
																		$pdf->Cell(9,5,"UND",1, 0,"C",1);
																		$pdf->Cell(17,5,"CÓD RED",1,0,"C",1);
																		$pdf->Cell(25,5,"QTD ESTOQUE",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR UNIT",1,0,"C",1);
																		$pdf->Cell(24,5,"VALOR TOTAL",1,1,"C",1);
																}
														}
												}

												# Pega a descrição do Material com acento #
												$Descricao    = split($SimboloConcatenacaoDesc,$DescMaterial);
												$DescMaterial = $Descricao[1];

												# Quebra de Linha para Descrição do Material #
												$DescMaterialSepara = SeparaFrase($DescMaterial,79);
												$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
												if($TamDescMaterial <= 150){
														$LinhasMat = 1;
														$AlturaMat = 5;
												}elseif($TamDescMaterial > 150 and $TamDescMaterial <= 296){
														$LinhasMat = 2;
														$AlturaMat = 10;
												}elseif($TamDescMaterial > 296 and $TamDescMaterial <= 444){
														$LinhasMat = 3;
														$AlturaMat = 15;
												}else{
														$LinhasMat = 4;
														$AlturaMat = 20;
												}
												if($TamDescMaterial > 150){
														$Inicio = 0;
														if($ExibirLoc == "S"){
																$pdf->Cell(152,$AlturaMat,"",1,0,"L",0);
														}else{
																$pdf->Cell(181,$AlturaMat,"",1,0,"L",0);
														}
														for($Quebra = 0; $Quebra < $LinhasMat; $Quebra++){
																if($Quebra == 0){
																		$pdf->SetX(10);
																		if($ExibirLoc == "S"){
																				$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																				$pdf->Cell(9,$AlturaMat,$Unidade,1,0,"C",0);
																				$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
																				$pdf->Cell(25,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
																				$pdf->Cell(24,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
																				$ValorTotal = $QtdEstoque * $ValorUnitario;
																				$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
																				$pdf->Cell(24,$AlturaMat,$ValorTotal,1,0,"R",0);
																				$pdf->Cell(29,$AlturaMat,$Localizacao,1,0,"L",0);
																		}else{
																				$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																				$pdf->Cell(9,$AlturaMat,$Unidade,1,0,"C",0);
																				$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
																				$pdf->Cell(25,$AlturaMat,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
																				$pdf->Cell(24,$AlturaMat,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
																				$ValorTotal = $QtdEstoque * $ValorUnitario;
																				$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
																				$pdf->Cell(24,$AlturaMat,$ValorTotal,1,0,"R",0);
																		}
																		$pdf->Ln(5);
																}elseif($Quebra == 1){
																		if($ExibirLoc == "S"){
																				$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}else{
																				$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}
																		$pdf->Ln(5);
																}elseif($Quebra == 2){
																		if($ExibirLoc == "S"){
																				$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}else{
																				$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}
																		$pdf->Ln(5);
																}elseif($Quebra == 3){
																		if($ExibirLoc == "S"){
																				$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}else{
																				$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}
																		$pdf->Ln(5);
																}else{
																		if($ExibirLoc == "S"){
																				$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}else{
																				$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
																		}
																		$pdf->Ln(5);
																}
																$Inicio = $Inicio + 79;
														}
												}else{
														if($ExibirLoc == "S"){
																$pdf->Cell(152,5,$DescMaterial,1,0,"L",0);
																$pdf->Cell(9,5,$Unidade,1,0,"C",0);
																$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
																$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
																$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
																$ValorTotal = $QtdEstoque * $ValorUnitario;
																$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
																$pdf->Cell(24,5,$ValorTotal,1,0,"R",0);
																$pdf->Cell(29,5,$Localizacao,1,1,"L",0);
														}else{
																$pdf->Cell(181,5,$DescMaterial,1,0,"L",0);
																$pdf->Cell(9,5,$Unidade,1,0,"C",0);
																$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
																$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
																$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
																$ValorTotal = $QtdEstoque * $ValorUnitario;
																$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
																$pdf->Cell(24,5,$ValorTotal,1,1,"R",0);
														}
												}
										}

										# Mostra o totalizador de Materiais #
										if($ExibirLoc == "S"){
												$pdf->Cell(280,5,"",1,1,"R",0);
												$pdf->Cell(132,5,"TOTAL DE ITENS",1,0,"R",1);
												$pdf->Cell(20,5,$TotalItens,1,0,"R",0);
												$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
												$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
												$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
												$pdf->Cell(24,5,$Totalizador,1,0,"R",0);
												$pdf->Cell(29,5,"",1,1,"R",1);
										}else{
												$pdf->Cell(280,5,"",1,1,"R",0);
												$pdf->Cell(161,5,"TOTAL DE ITENS",1,0,"R",1);
												$pdf->Cell(20,5,$TotalItens,1,0,"R",0);
												$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
												$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
												$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
												$pdf->Cell(24,5,$Totalizador,1,1,"R",0);
										}
										# Grava arquivo e fecha sessão PDF #
										$pdf->Output($NomeArquivo,'F');
								}
						}
				}
				$CodigoReduzido = null;
				$DescMaterial   = null;
				$Unidade        = null;
				$QtdEstoque     = null;
				$ValorUnitario  = null;
				$Equipamento    = null;
				$NumEquipamento = null;
				$Prateleira     = null;
				$Coluna         = null;
				$Area           = null;
				$DescGrupo      = null;
				$DescClasse     = null;
				$Itens          = null;
		}
}
$db->disconnect();
?>
