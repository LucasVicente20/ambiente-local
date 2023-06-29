<?php
#------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioPeriodicoConsolidarPdf.php
# Autor:    Carlos Abreu
# Data:     14/12/2006
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
# OBS.:     Tabulação 2 espaços
#           Ao passar para produção trocar o caminho da imagem do cabeçalho na função
#           CabecalhoRodapeInventario() neste arquivo
#------------------------------------------------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     25/07/2014 - Na query sql, a coluna FINVREETAP é do tipo VARCHAR, mas na 
#                        formação da query estava sendo passado um integer.
#------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Localizacao  = $_GET['Localizacao'];
		$Ordem        = $_GET['Ordem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
if ($Ordem == "1"){
		$TituloRelatorio = "Relatório de Consolidação de Inventário (Ordem: Família)";
} else {
		$TituloRelatorio = "Relatório de Consolidação de Inventário (Ordem: Material)";
}

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Muda o tamanho do Rodapé #
$pdf->SetAutoPageBreak(true,32);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$db = Conexao();
$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU
					FROM SFPC.TBINVENTARIOCONTAGEM A
				 WHERE A.CLOCMACODI=$Localizacao 
				   AND A.FINVCOFECH IS NULL
				   AND A.AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
				 GROUP BY A.AINVCOANOB";
$res  = $db->query($sql);
if(PEAR::isError($res)){
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	$db->disconnect();
	exit;
}else{
	$Rows = $res->numRows();
	if( $Rows != 0 ){
			$Linha = $res->fetchRow();
	}
	$Ano        = $Linha[0];
	if (!$Ano){$Ano=date("Y");}
	$Sequencial = $Linha[1];
}
	
if( $Ordem == 1 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Família #
		$sql  = "SELECT TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, SUM(TABELA.QTD1), SUM(TABELA.QTD2), SUM(TABELA.TOTAL), ";
		$sql .= "  CASE WHEN (SUM(EXISTE1.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END,  ";
		$sql .= "  CASE WHEN (SUM(EXISTE2.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END, GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC  ";
		$sql .= "  FROM ( ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, AINVREQTDE AS QTD1, 0 AS QTD2, 0 AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOREGISTRO  ";
		$sql .= "        WHERE FINVREETAP = '1' AND CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial  ";
		$sql .= "        UNION  ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, AINVREQTDE AS QTD2, 0 AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOREGISTRO  ";
		$sql .= "        WHERE FINVREETAP = '2' AND CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial  ";
		$sql .= "        UNION  ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, 0 AS QTD2, AINVMAESTO AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOMATERIAL  ";
		$sql .= "        WHERE CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial ";
		$sql .= "       ) AS TABELA ";
		$sql .= "  LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE1 ";
		$sql .= "    ON EXISTE1.CLOCMACODI = TABELA.CLOCMACODI ";
		$sql .= "   AND EXISTE1.AINVCOANOB = TABELA.AINVCOANOB ";
		$sql .= "   AND EXISTE1.AINVCOSEQU = TABELA.AINVCOSEQU ";
		$sql .= "   AND EXISTE1.CMATEPSEQU = TABELA.CMATEPSEQU ";
		$sql .= "   AND EXISTE1.FINVREETAP = '1' ";
		$sql .= "  LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE2 ";
		$sql .= "    ON EXISTE2.CLOCMACODI = TABELA.CLOCMACODI ";
		$sql .= "   AND EXISTE2.AINVCOANOB = TABELA.AINVCOANOB ";
		$sql .= "   AND EXISTE2.AINVCOSEQU = TABELA.AINVCOSEQU ";
		$sql .= "   AND EXISTE2.CMATEPSEQU = TABELA.CMATEPSEQU ";
		$sql .= "   AND EXISTE2.FINVREETAP = '2', ";
		$sql .= "       SFPC.TBMATERIALPORTAL AS MATERIAL, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA AS UNIDADE, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL AS SUBCLASSE, ";
		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO AS CLASSE, ";
		$sql .= "       SFPC.TBGRUPOMATERIALSERVICO AS GRUPO ";
		$sql .= " WHERE TABELA.CMATEPSEQU = MATERIAL.CMATEPSEQU ";
		$sql .= "   AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI ";
		$sql .= "   AND MATERIAL.CSUBCLSEQU = SUBCLASSE.CSUBCLSEQU ";
		$sql .= "   AND SUBCLASSE.CGRUMSCODI = CLASSE.CGRUMSCODI ";
		$sql .= "   AND SUBCLASSE.CCLAMSCODI = CLASSE.CCLAMSCODI ";
		$sql .= "   AND CLASSE.CGRUMSCODI = GRUPO.CGRUMSCODI ";
		$sql .= " GROUP BY TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC ";
		$sql .= "HAVING SUM(TABELA.QTD1) <> SUM(TABELA.QTD2) OR SUM(EXISTE1.AINVREQTDE) IS NULL OR SUM(EXISTE2.AINVREQTDE) IS NULL";
		$sql .= " ORDER BY GRUPO.EGRUMSDESC, CLASSE.ECLAMSDESC, MATERIAL.EMATEPDESC ";
}elseif( $Ordem == 2 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Material #
		$sql  = "SELECT TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, SUM(TABELA.QTD1), SUM(TABELA.QTD2), SUM(TABELA.TOTAL), ";
		$sql .= "  CASE WHEN (SUM(EXISTE1.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END,  ";
		$sql .= "  CASE WHEN (SUM(EXISTE2.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END  ";
		$sql .= "  FROM ( ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, AINVREQTDE AS QTD1, 0 AS QTD2, 0 AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOREGISTRO  ";
		$sql .= "        WHERE FINVREETAP = '1' AND CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial  ";
		$sql .= "        UNION  ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, AINVREQTDE AS QTD2, 0 AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOREGISTRO  ";
		$sql .= "        WHERE FINVREETAP = '2' AND CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial  ";
		$sql .= "        UNION  ";
		$sql .= "       SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, 0 AS QTD2, AINVMAESTO AS TOTAL  ";
		$sql .= "         FROM SFPC.TBINVENTARIOMATERIAL  ";
		$sql .= "        WHERE CLOCMACODI = $Localizacao AND AINVCOANOB = $Ano AND AINVCOSEQU = $Sequencial ";
		$sql .= "       ) AS TABELA ";
		$sql .= "  LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE1 ";
		$sql .= "    ON EXISTE1.CLOCMACODI = TABELA.CLOCMACODI ";
		$sql .= "   AND EXISTE1.AINVCOANOB = TABELA.AINVCOANOB ";
		$sql .= "   AND EXISTE1.AINVCOSEQU = TABELA.AINVCOSEQU ";
		$sql .= "   AND EXISTE1.CMATEPSEQU = TABELA.CMATEPSEQU ";
		$sql .= "   AND EXISTE1.FINVREETAP = '1' ";
		$sql .= "  LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE2 ";
		$sql .= "    ON EXISTE2.CLOCMACODI = TABELA.CLOCMACODI ";
		$sql .= "   AND EXISTE2.AINVCOANOB = TABELA.AINVCOANOB ";
		$sql .= "   AND EXISTE2.AINVCOSEQU = TABELA.AINVCOSEQU ";
		$sql .= "   AND EXISTE2.CMATEPSEQU = TABELA.CMATEPSEQU ";
		$sql .= "   AND EXISTE2.FINVREETAP = '2',  ";
		$sql .= "       SFPC.TBMATERIALPORTAL AS MATERIAL,  ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA AS UNIDADE  ";
		$sql .= " WHERE TABELA.CMATEPSEQU = MATERIAL.CMATEPSEQU  ";
		$sql .= "   AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI  ";
		$sql .= " GROUP BY TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL  ";
		$sql .= "HAVING SUM(TABELA.QTD1) <> SUM(TABELA.QTD2) OR SUM(EXISTE1.AINVREQTDE) IS NULL OR SUM(EXISTE2.AINVREQTDE) IS NULL";
		$sql .= " ORDER BY MATERIAL.EMATEPDESC";
}

//echo $sql; die;

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
				$Almox     = $resalmo->fetchRow();
				$DescAlmox = $Almox[0];

				$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
				$pdf->Cell(183,5,"$DescAlmox",1,0,"L",0);
				$pdf->Cell(31,5,"DATA INVENTÁRIO", 1, 0, "L", 1);
				$pdf->Cell(36,5,"          /          /", 1, 1, "L", 0);
				$pdf->ln(5);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelInventarioPeriodicoConsolidar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha              = $res->fetchRow();
						$CodigoReduzido[$i] = $Linha[0];
						$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]   		  = $Linha[2];
						$Contagem[$i]       = $Linha[3];
						$Recontagem[$i] 	  = $Linha[4];
						$Consolidado[$i]	  = $Linha[5];
						$Contado[$i]        = $Linha[6];
						$Recontado[$i]	    = $Linha[7];

						# Montando o array de Itens do Inventário #
						if( $Ordem == 1 ){
								$DescGrupo[$i]  = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
								$DescClasse[$i] = RetiraAcentos($Linha[9]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[9]);
								$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Contagem[$i].$SimboloConcatenacaoArray.$Recontagem[$i].$SimboloConcatenacaoArray.$Consolidado[$i].$SimboloConcatenacaoArray.$Contado[$i].$SimboloConcatenacaoArray.$Recontado[$i];
						}else{
								$Itens[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Contagem[$i].$SimboloConcatenacaoArray.$Recontagem[$i].$SimboloConcatenacaoArray.$Consolidado[$i].$SimboloConcatenacaoArray.$Contado[$i].$SimboloConcatenacaoArray.$Recontado[$i];
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes     = "";
$DescCalsseAntes    = "";
sort($Itens);
for( $i=0; $i< count($Itens); $i++ ){
		# Extrai os dados do Array de Itens #
		$Dados = explode($SimboloConcatenacaoArray,$Itens[$i]);
		if( $Ordem == 1 ){
				$DescGrupo      = $Dados[0];
				$DescClasse     = $Dados[1];
				$DescMaterial   = $Dados[2];
				$CodigoReduzido = $Dados[3];
				$Unidade   		  = $Dados[4];

				if ($Dados[8]=='S'){
						$Contagem   	  = converte_quant($Dados[5]);
				} else {
						$Contagem       = "Inexistente";
				}
				if ($Dados[9]=='S'){
						$Recontagem     = converte_quant($Dados[6]);
				} else {
						$Recontagem     = "Inexistente";
				}
				$Consolidado	  = converte_quant($Dados[7]);

				# Pega a descrição do Grupo com acento #
				$DescricaoG = explode($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = explode($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];

				if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
						$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
						$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
						$pdf->Cell(169,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(17,5,"CÓD.RED.",1,0,"C",1);
						$pdf->Cell(27,5,"CONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"RECONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"CONSOLIDADO",1,1,"C",1);
				}
				$DescGrupoAntes     = $DescGrupo;
				$DescClasseAntes    = $DescClasse;
		}else{
				$DescMaterial   = $Dados[0];
				$CodigoReduzido = $Dados[1];
				$Unidade   		  = $Dados[2];
				if ($Dados[6]=='S'){
						$Contagem   	  = converte_quant($Dados[3]);
				} else {
						$Contagem       = "Inexistente";
				}
				if ($Dados[7]=='S'){
						$Recontagem     = converte_quant($Dados[4]);
				} else {
						$Recontagem     = "Inexistente";
				}
				$Consolidado	  = converte_quant($Dados[5]);
				if( $i == 0 ){
						$pdf->Cell(169,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(17,5,"CÓD.RED.",1,0,"C",1);
						$pdf->Cell(27,5,"CONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"RECONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"CONSOLIDADO",1,1,"C",1);
				}
		}

		# Pega a descrição do Material com acento #
		$Descricao    = explode($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

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
				$pdf->Cell(169,$AlturaMat,"",1,0,"L",0);
				for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
						if( $Quebra == 0 ){
					  		$pdf->SetX(10);
					  		$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Cell(13,$AlturaMat,$Unidade,1,0,"C",0);
								$pdf->Cell(17,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
								$pdf->Cell(27,$AlturaMat,$Contagem,1,0,"C",0);
								$pdf->Cell(27,$AlturaMat,$Recontagem,1,0,"C",0);
								$pdf->Cell(27,$AlturaMat,"",1,0,"C",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 1 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
					  		$pdf->Ln(5);
					  }elseif( $Quebra == 2 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }elseif( $Quebra == 3 ){
								$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }else{
						  	$pdf->Cell(169,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					  }
						$Inicio = $Inicio + 79;
			  }
	  		$pdf->Cell(169,0,"",1,1,"",0);
		}else{
				$pdf->Cell(169,5,$DescMaterial, 1,0, "L",0);
				$pdf->Cell(13,5,$Unidade,1,0,"C",0);
				$pdf->Cell(17,5,$CodigoReduzido,1, 0,"C",0);
				$pdf->Cell(27,5,$Contagem,1,0,"C",0);
				$pdf->Cell(27,5,$Recontagem,1,0,"C",0);
				$pdf->Cell(27,5,"",1,1,"C",0);
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(253,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(27,5,$rows, 1,1, "R",0);
$pdf->Output();
?> 
