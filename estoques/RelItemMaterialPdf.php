<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelItemMaterialEstoquePdf.php
# Autor:    Rossana Lira
# Data:     12/08/2005
# Objetivo: Programa de Impressão dos itens em estoque
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelContagemInventario.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Ordem 				= $_GET['Ordem'];
		$ExibirLoc		= $_GET['ExibirLoc'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Itens de Material em Estoque";

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
if($Ordem == 1){
		$Sql  = "SELECT A.EGRUMSDESC, B.ECLAMSDESC, C.ESUBCLDESC, D.CMATEPSEQU, ";
		$Sql .= "  		  D.EMATEPDESC, E.AARMATQTDE, E.VARMATUMED, F.CARLOCCODI, ";
		$Sql .= " 	  	F.FLOCMAEQUI, F.ALOCMANEQU, F.ALOCMAPRAT, F.ALOCMACOLU, ";
		$Sql .= " 	  	G.EUNIDMSIGL ";
		$Sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO A, SFPC.TBCLASSEMATERIALSERVICO B, ";
		$Sql .= " 	 	  SFPC.TBSUBCLASSEMATERIAL C, SFPC.TBMATERIALPORTAL D,  ";
		$Sql .= " 	    SFPC.TBARMAZENAMENTOMATERIAL E, SFPC.TBLOCALIZACAOMATERIAL F, ";
		$Sql .= " 	    SFPC.TBUNIDADEDEMEDIDA G";
		$Sql .= " WHERE A.CGRUMSCODI = B.CGRUMSCODI AND B.CCLAMSCODI = C.CCLAMSCODI ";
		$Sql .= "   AND B.CGRUMSCODI = C.CGRUMSCODI AND C.CSUBCLSEQU = D.CSUBCLSEQU ";
		$Sql .= "   AND D.CMATEPSEQU = E.CMATEPSEQU AND E.CLOCMACODI = F.CLOCMACODI ";
		$Sql .= "   AND D.CUNIDMCODI = G.CUNIDMCODI AND F.CALMPOCODI = $Almoxarifado ";
		$Sql .= " ORDER BY A.EGRUMSDESC, B.ECLAMSDESC, C.ESUBCLDESC, D.EMATEPDESC ";
}else{
		$Sql  = "SELECT A.CMATEPSEQU, A.EMATEPDESC, D.EUNIDMSIGL, B.AARMATQTDE, ";
		$Sql .= "  		  B.VARMATUMED, C.CARLOCCODI, C.FLOCMAEQUI, C.ALOCMANEQU, ";
		$Sql .= "  		  C.ALOCMAPRAT, C.ALOCMACOLU  ";
		$Sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBARMAZENAMENTOMATERIAL B, ";
		$Sql .= "       SFPC.TBLOCALIZACAOMATERIAL C, SFPC.TBUNIDADEDEMEDIDA D ";
		$Sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU AND B.CLOCMACODI = C.CLOCMACODI ";
		$Sql .= "   AND A.CUNIDMCODI = D.CUNIDMCODI AND C.CALMPOCODI = $Almoxarifado ";
		$Sql .= " ORDER BY A.EMATEPDESC ";
}
$res = $db->query($Sql);
if( PEAR::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$res  = $db->query($Sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
		}else{
				# Pega as informações do Almoxarifado #
				$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
				$resalmo = $db->query($sqlalmo);
				if( PEAR::isError($resalmo) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlalmo");
				}else{
						$Almox     = $resalmo->fetchRow();
						$DescAlmox = $Almox[0];

						$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
						$pdf->Cell(250,5,$DescAlmox,1,0,"L",0);
						$pdf->ln(8);
				}

				# Linhas de Itens de Material #
				$rows = $res->numRows();
				if( $rows == 0 ){
						$Mensagem = "Nenhuma Ocorrência Encontrada";
						$Url = "RelContagemInventario.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}else{
		        $DescGrupoAntes     = "";
		        $DescCalsseAntes    = "";
		        $DescSubcalsseAntes = "";
						for( $i=0; $i< $rows; $i++ ){
								$Linha = $res->fetchRow();
								if( $Ordem == 1 ){
										$DescGrupo      = $Linha[0];
										$DescClasse     = $Linha[1];
										$CodigoReduzido = $Linha[3];
										$DescMaterial   = $Linha[4];
										$QtdEstoque  		= $Linha[5];
										$ValorUnitario  = $Linha[6];
										$Area       		= $Linha[7];
										$Equipamento 		= $Linha[8];
										$NumEquipamento = $Linha[9];
										$Prateleira    	= $Linha[10];
										$Coluna    	  	= $Linha[11];
										$Unidade   		  = $Linha[12];
										$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;
										if( $DescGrupoAntes != $DescGrupo ){
												$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
												$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
												if( $ExibirLoc == "S" ){
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
										$DescSubclasseAntes = $DescSubclasse;
								}else{
										$CodigoReduzido = $Linha[0];
										$DescMaterial   = $Linha[1];
										$Unidade   		  = $Linha[2];
										$QtdEstoque  		= $Linha[3];
										$ValorUnitario  = $Linha[4];
										$Area       		= $Linha[5];
										$Equipamento 		= $Linha[6];
										$NumEquipamento = $Linha[7];
										$Prateleira    	= $Linha[8];
										$Coluna    	  	= $Linha[9];
										$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;
										if( $i == 0 ){
												if( $ExibirLoc == "S" ){
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

								# Quebra de Linha para Descrição do Material #
								$DescMaterialSepara = SeparaFrase($DescMaterial,79);
								$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
								if( $TamDescMaterial <= 148 ){
										$LinhasMat = 1;
										$AlturaMat = 5;
								}elseif( $TamDescMaterial > 148 and $TamDescMaterial <= 296 ){
										$LinhasMat = 2;
										$AlturaMat = 10;
								}elseif( $TamDescMaterial > 296 and $TamDescMaterial <= 444 ){
										$LinhasMat = 3;
										$AlturaMat = 15;
								}else{
										$LinhasMat = 4;
										$AlturaMat = 20;
								}
								if( $TamDescMaterial > 147 ){
										$Inicio = 0;
							  		if( $ExibirLoc == "S" ){
												$pdf->Cell(152,$AlturaMat,"",1,0,"L",0);
										}else{
												$pdf->Cell(181,$AlturaMat,"",1,0,"L",0);
										}
										for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
												if( $Quebra == 0 ){
											  		$pdf->SetX(10);
											  		if( $ExibirLoc == "S" ){
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
												}elseif( $Quebra == 1 ){
														if( $ExibirLoc == "S" ){
																$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}else{
																$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}
											  		$pdf->Ln(5);
											  }elseif( $Quebra == 2 ){
														if( $ExibirLoc == "S" ){
																$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}else{
																$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}
														$pdf->Ln(5);
											 }elseif( $Quebra == 3 ){
														if( $ExibirLoc == "S" ){
																$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}else{
																$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}
														$pdf->Ln(5);
											 }else{
														if( $ExibirLoc == "S" ){
																$pdf->Cell(152,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}else{
																$pdf->Cell(181,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
														}
														$pdf->Ln(5);
											  }
												$Inicio = $Inicio + 79;
									  }
								}else{
										if( $ExibirLoc == "S" ){
												$pdf->Cell(152,5,$DescMaterial, 1,0, "L",0);
												$pdf->Cell(9,5,$Unidade,1,0,"C",0);
												$pdf->Cell(17,5,$CodigoReduzido,1, 0,"C",0);
												$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
												$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
												$ValorTotal = $QtdEstoque * $ValorUnitario;
												$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
												$pdf->Cell(24,5,$ValorTotal,1,0,"R",0);
												$pdf->Cell(29,5,$Localizacao,1,1,"L",0);
										}else{
												$pdf->Cell(181,5,$DescMaterial, 1,0, "L",0);
												$pdf->Cell(9,5,$Unidade,1,0,"C",0);
												$pdf->Cell(17,5,$CodigoReduzido,1, 0,"C",0);
												$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
												$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
												$ValorTotal = $QtdEstoque * $ValorUnitario;
												$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
												$pdf->Cell(24,5,$ValorTotal,1,1,"R",0);
										}
								}
						}
				}
		}
}
$db->disconnect();
$pdf->Output();
?> 
