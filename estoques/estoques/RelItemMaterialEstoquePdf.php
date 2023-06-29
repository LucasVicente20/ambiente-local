<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelItemMaterialEstoquePdf.php
# Autor:    Rossana Lira
# Data:     12/08/2005
# Objetivo: Programa de Impressão dos itens em estoque
#-----------------------------
# Alterado: Álvaro Faria
# Data:     17/03/2006
# Alterado: Álvaro Faria
# Data:     10/12/2007 - Retirada do sort e acréscimo do translate no order by
# Alterado: Rodrigo Melo
# Data:     26/12/2007 - Retirada do sort e acréscimo do translate no order by
# Alterado: Rodrigo Melo
# Data:     27/12/2007 - Inclusão de total dos itens em estoque conforme a classificação "Permanente" e "Consumo"
# Alterado: Ariston Cordeiro
# Data:     24/09/2008	- Mostrar Sub-Totais de cada grupo / classe
# Data:     						- Permitir selecionar os grupos / classes a serem impressos
#----------------------------
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
		$ExibirZer		= $_GET['ExibirZer'];
		$GrupoClasses = $_GET['GrupoClasses'];
}

# convertendo $GrupoClasses de string para um array de arrays
$Grupos = array();
$Classes = array();
$GruposClassesItens = array();
$itr=0;
if(!is_null($GrupoClasses) and $GrupoClasses != ""){
	$GrupoClasses = explode("!",$GrupoClasses);
	foreach($GrupoClasses as $GrupoClasse){
		$GruposClassesItens[$itr] = explode("_",$GrupoClasse);
		$Grupos[$itr] = $GruposClassesItens[$itr][0];
		$Classes[$itr] = $GruposClassesItens[$itr][1];
		$itr++;
	}
}else{
	$GrupoClasses = array();
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Itens de Material em Estoque na data atual (Ordem: ";
if ($Ordem == "1"){
		$TituloRelatorio .= "Família";
} else {
		$TituloRelatorio .= "Material";
}
$TituloRelatorio .=", Opção: ";
if ($ExibirZer == 'N') $TituloRelatorio .= "Não exibir itens zerados";
elseif ($ExibirZer == 'S') $TituloRelatorio .= "Exibir itens zerados";
if($Ordem == "1" and count($GrupoClasses) != 0 ){
	$TituloRelatorio .= ", Exibir grupos/classes selecionados";
} 
$TituloRelatorio .= ")";

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

$sql2	 = " SELECT SUM(ARM.AARMATQTDE*ARM.VARMATUMED), SUM(ARM.AARMATQTDE), GRU.FGRUMSTIPM";
$sql2	.= " FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
$sql2	.= " 			SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC, ";
$sql2	.= " 			SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU ";
$sql2	.= "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
$sql2	.= "    	AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
$sql2	.= "    	AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
$sql2	.= "    	GROUP BY GRU.FGRUMSTIPM ";

$res2 = $db->query($sql2);

//var_dump($sql2);die;
if( db::isError($res2) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql2");
}else{
		$rows = $res2->numRows();
    $Totalizador = 0;
    $QtdTotal = 0;
    
    $TotalizadorConsumo = 0;
    $QtdTotalConsumo = 0;
    $TotalizadorPermanente = 0;
    $QtdTotalPermanente = 0;
    
    for($i=0; $i < $rows; $i++){
      $LinhaTotal = $res2->fetchRow();
      $TotalizadorEstoque  = $LinhaTotal[0];
      $QtdTotalEstoque     = $LinhaTotal[1];
      $TipoMaterial        = $LinhaTotal[2];
      
      
      if($TipoMaterial == 'C'){
        $TotalizadorConsumo = $TotalizadorConsumo + $TotalizadorEstoque;
        $QtdTotalConsumo = $QtdTotalConsumo + $QtdTotalEstoque;
      } else {
        $TotalizadorPermanente = $TotalizadorPermanente + $TotalizadorEstoque;
        $QtdTotalPermanente = $QtdTotalPermanente + $QtdTotalEstoque;
      }
      
      $QtdTotal        = $QtdTotal + $QtdTotalEstoque;
			$Totalizador     = $Totalizador + $TotalizadorEstoque;
    }
    
		$Totalizador = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$Totalizador)));
    $TotalizadorConsumo = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorConsumo)));
    $TotalizadorPermanente = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$TotalizadorPermanente)));
}

# Resgata os itens do estoque #
$select  = " SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UNI.EUNIDMSIGL, ARM.AARMATQTDE, ";
$select .= "  		  ARM.VARMATUMED, LOC.FLOCMAEQUI, LOC.ALOCMANEQU, LOC.ALOCMAPRAT, ";
$select .= "        LOC.ALOCMACOLU, LOC.CARLOCCODI, GRU.FGRUMSTIPM ";
$from   .= "   FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI, ";
$from   .= "        SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC, ";
$from   .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU ";
$where  .= "  WHERE MAT.CUNIDMCODI = UNI.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
$where  .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
$where  .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
if( $Ordem == 1 ){
		$select .= "       , GRU.EGRUMSDESC, CLA.ECLAMSDESC, GRU.CGRUMSCODI, CLA.CCLAMSCODI ";
		$from   .= "       , SFPC.TBCLASSEMATERIALSERVICO CLA ";
		//$from   .= "       , SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		//$from   .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
		$where  .= "   AND GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CCLAMSCODI = SUB.CCLAMSCODI ";
		$where  .= "   AND CLA.CGRUMSCODI = SUB.CGRUMSCODI AND SUB.CSUBCLSEQU = MAT.CSUBCLSEQU ";
		$order  .= " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, MAT.EMATEPDESC ";
}else{
		$order .= " ORDER BY MAT.EMATEPDESC ";
		//$order .= " ORDER BY MAT.EMATEPDESC ";
}
$sql = $select.$from.$where.$order;

//var_dump($sql);die;

$res = $db->query($sql);
if( db::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Pega as informações do Almoxarifado #
		$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
		$resalmo = $db->query($sqlalmo);
		if( db::isError($resalmo) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
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
				for( $i=0; $i< $rows; $i++ ){
						$Linha = $res->fetchRow();
						$CodigoReduzido[$i] = $Linha[0];
						$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]   		  = $Linha[2];
						$QtdEstoque[$i]  		= $Linha[3];
						$ValorUnitario[$i]  = $Linha[4];
						$Equipamento[$i] 		= $Linha[5];
						$NumEquipamento[$i] = $Linha[6];
						$Prateleira[$i]    	= $Linha[7];
						$Coluna[$i]    	  	= $Linha[8];
						$Area[$i]       		= $Linha[9];
            $TipoMat[$i]       	= $Linha[10];

						# Montando o array de Itens do Estoque #
						if( $Ordem == 1 ){
								if (  ($ExibirZer == "S") or ( ($ExibirZer == "N") and ($QtdEstoque[$i] > 0) )  ) {
										$DescGrupo[$i]  = RetiraAcentos($Linha[11]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[11]);
										$DescClasse[$i] = RetiraAcentos($Linha[12]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[12]);
										$CodGrupo[$i] = $Linha[13];
										$CodClasse[$i] = $Linha[14];
										$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstoque[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$Equipamento[$i].$SimboloConcatenacaoArray.$NumEquipamento[$i].$SimboloConcatenacaoArray.$Prateleira[$i].$SimboloConcatenacaoArray.$Coluna[$i].$SimboloConcatenacaoArray.$Area[$i].$SimboloConcatenacaoArray.$CodGrupo[$i].$SimboloConcatenacaoArray.$CodClasse[$i].$SimboloConcatenacaoArray.$TipoMat[$i];
										$TotalItens     = $TotalItens + 1;
                    
                    if($TipoMat[$i] == 'C'){ //Tipo Material Consumo
                      $TotalItensConsumo = $TotalItensConsumo + 1;
                    } else { //Tipo Material Permanente -> 'P'
                      $TotalItensPermanente = $TotalItensPermanente + 1;
                    }
								}
						}else{
								if (  ($ExibirZer == "S") or ( ($ExibirZer == "N") and ($QtdEstoque[$i] > 0) )  ) {
										$Itens[$i]  = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdEstoque[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$Equipamento[$i].$SimboloConcatenacaoArray.$NumEquipamento[$i].$SimboloConcatenacaoArray.$Prateleira[$i].$SimboloConcatenacaoArray.$Coluna[$i].$SimboloConcatenacaoArray.$Area[$i].$SimboloConcatenacaoArray.$TipoMat[$i];
										$TotalItens = $TotalItens + 1;
                    
                    if($TipoMat[$i] == 'C'){ //Tipo Material Consumo
                      $TotalItensConsumo = $TotalItensConsumo + 1;
                    } else { //Tipo Material Permanente -> 'P'
                      $TotalItensPermanente = $TotalItensPermanente + 1;
                    }
								}
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes  = "";
$DescClasseAntes = "";
$CodGrupoAntes  = "";
$CodClasseAntes = "";
$TipoClasseAntes = "";
$ImprimirClasse = true;
sort($Itens);
if($Ordem == 1){
	$ValorTotalGrupo = 0;
	$ValorTotalQtde = 0;
}
for( $i=0; $i< count($Itens); $i++ ){
		# Extrai os dados do Array de Itens #
		$Dados = split($SimboloConcatenacaoArray,$Itens[$i]);
		if( $Ordem == 1 ){
				$DescGrupo      = $Dados[0];
				$DescClasse     = $Dados[1];
				$DescMaterial   = $Dados[2];
				$CodigoReduzido = $Dados[3];
				$Unidade   		  = $Dados[4];
				$QtdEstoque  		= $Dados[5];
				$ValorUnitario  = $Dados[6];
				$Equipamento 		= $Dados[7];
				$NumEquipamento = $Dados[8];
				$Prateleira    	= $Dados[9];
				$Coluna    	  	= $Dados[10];
				$Area       		= $Dados[11];
				$CodGrupo      	= $Dados[12];
				$CodClasse      = $Dados[13];
				$TipoClasse     = $Dados[14];
				$Localizacao    = "A:". $Area." E:".$Equipamento.$NumEquipamento."/ESC:".$Prateleira.$Coluna;

				# Pega a descrição do Grupo com acento #
				$DescricaoG = split($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = split($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];
				if($ImprimirClasse){
					if( ( ($DescGrupoAntes != $DescGrupo) or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ) and ($i!=0) ){
						if($TipoClasseAntes=="P"){
							$TipoClasseDescr = "PERMANENTE";
						}else{
							$TipoClasseDescr = "CONSUMO";
						}
						$pdf->Cell(133,5,"TOTAL DO GRUPO/CLASSE",1,0,"L",1);
						$pdf->Cell(24,5,"TIPO",1,0,"R",1);
						$pdf->Cell(24,5,$TipoClasseDescr,1,0,"R",0);
						$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
						$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$ValorTotalQtde))),1,0,"R",0);
						$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
						$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalGrupo))),1,1,"R",0);
						$pdf->Cell(280,5,"",1,1,"R",0);
					}
				}
				if( count($GrupoClasses)==0 or in_array($CodGrupo."_".$CodClasse,$GrupoClasses) ){
					$ImprimirClasse = true;
				}else{
					$ImprimirClasse = false;
				}
				if($ImprimirClasse){
					if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
							$ValorTotalQtde = 0;
							$ValorTotalGrupo = 0;
							$TipoClasseAntes = $TipoClasse;
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
					$CodGrupoAntes     = $CodGrupo;
					$CodClasseAntes    = $CodClasse;
				}
		}else{
				$DescMaterial   = $Dados[0];
				$CodigoReduzido = $Dados[1];
				$Unidade   		  = $Dados[2];
				$QtdEstoque  		= $Dados[3];
				$ValorUnitario  = $Dados[4];
				$Equipamento 		= $Dados[5];
				$NumEquipamento = $Dados[6];
				$Prateleira    	= $Dados[7];
				$Coluna    	  	= $Dados[8];
				$Area       		= $Dados[9];
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

		# Pega a descrição do Material com acento #
		$Descricao    = split($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

		# Quebra de Linha para Descrição do Material #
		$DescMaterialSepara = SeparaFrase($DescMaterial,79);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		if( $TamDescMaterial <= 150 ){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif( $TamDescMaterial > 150 and $TamDescMaterial <= 296 ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif( $TamDescMaterial > 296 and $TamDescMaterial <= 444 ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}else{
				$LinhasMat = 4;
				$AlturaMat = 20;
		}
		if($ImprimirClasse){
			if( $TamDescMaterial > 150 ){
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
											$ValorTotalQtde += $QtdEstoque;
											$ValorTotalGrupo += $ValorTotal;
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
											$ValorTotalQtde += $QtdEstoque;
											$ValorTotalGrupo += $ValorTotal;
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
							$pdf->Cell(152,5,$DescMaterial,1,0,"L",0);
							$pdf->Cell(9,5,$Unidade,1,0,"C",0);
							$pdf->Cell(17,5,$CodigoReduzido,1,0,"C",0);
							$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEstoque))),1,0,"R",0);
							$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario))),1,0,"R",0);
							$ValorTotal = $QtdEstoque * $ValorUnitario;
							$ValorTotalQtde += $QtdEstoque;
							$ValorTotalGrupo += $ValorTotal;
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
							$ValorTotalQtde += $QtdEstoque;
							$ValorTotalGrupo += $ValorTotal;
							$ValorTotal = converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal)));
							$pdf->Cell(24,5,$ValorTotal,1,1,"R",0);
					}
			}
		}
}
if( $Ordem == 1 and $ImprimirClasse ){
	if($TipoClasseAntes=="P"){
		$TipoClasseDescr = "PERMANENTE";
	}else{
		$TipoClasseDescr = "CONSUMO";
	}
	$pdf->Cell(133,5,"TOTAL DO GRUPO/CLASSE",1,0,"L",1);
	$pdf->Cell(24,5,"TIPO",1,0,"R",1);
	$pdf->Cell(24,5,$TipoClasseDescr,1,0,"R",0);
	$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
	$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$ValorTotalQtde))),1,0,"R",0);
	$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
	$pdf->Cell(24,5,converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotalGrupo))),1,1,"R",0);
	$pdf->Cell(280,5,"",1,1,"R",0);
}


# Mostra o totalizador de Materiais #
if( $ExibirLoc == "S" ){
    if($TotalItensConsumo != null && $TotalItensConsumo > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(132,5,"TOTAL DE ITENS DE CONSUMO",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensConsumo,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalConsumo))),1,0,"R",0);
  		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(24,5,$TotalizadorConsumo,1,0,"R",0);
  		$pdf->Cell(29,5,"",1,1,"R",1);
    }
    
    if($TotalItensPermanente != null && $TotalItensPermanente > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(132,5,"TOTAL DE ITENS PERMANENTES",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensPermanente,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalPermanente))),1,0,"R",0);
  		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(24,5,$TotalizadorPermanente,1,0,"R",0);
  		$pdf->Cell(29,5,"",1,1,"R",1);
    }
    
    $pdf->Cell(280,5,"",1,1,"R",0);
		$pdf->Cell(132,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(20,5,$TotalItens,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(24,5,$Totalizador,1,0,"R",0);
		$pdf->Cell(29,5,"",1,1,"R",1);
    
}else{		
    
    if($TotalItensConsumo != null && $TotalItensConsumo > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(161,5,"TOTAL DE ITENS DE CONSUMO",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensConsumo,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalConsumo))),1,0,"R",0);
  		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(24,5,$TotalizadorConsumo,1,1,"R",0);
    }

    if($TotalItensPermanente != null && $TotalItensPermanente > 0){
      $pdf->Cell(280,5,"",1,1,"R",0);
  		$pdf->Cell(161,5,"TOTAL DE ITENS PERMANENTES",1,0,"R",1);
  		$pdf->Cell(20,5,$TotalItensPermanente,1,0,"R",0);
  		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
  		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotalPermanente))),1,0,"R",0);
  		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
  		$pdf->Cell(24,5,$TotalizadorPermanente,1,1,"R",0);
    }

    $pdf->Cell(280,5,"",1,1,"R",0);
		$pdf->Cell(161,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(20,5,$TotalItens,1,0,"R",0);
		$pdf->Cell(26,5,"QTD TOTAL",1,0,"R",1);
		$pdf->Cell(25,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdTotal))),1,0,"R",0);
		$pdf->Cell(24,5,"VLR TOTAL",1,0,"R",1);
		$pdf->Cell(24,5,$Totalizador,1,1,"R",0);
}
$pdf->Output();
?>
