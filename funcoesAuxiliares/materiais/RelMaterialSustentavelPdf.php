<?php
#------------------------------------------------------------------------------------
# Portal de Compras
# Programa: RelMaterialSustentavelPdf.php
# Autor:    Lucas Vicente
# Data:     28/12/2022
# Objetivo: Programa de Impressão dos Relatórios de Materiais Sustentaveis Ordenados por Material CR 235027
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/RelMaterial.php' );

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Materiais - Ordenado por Material";

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
$db   = Conexao();
$sql  = "	SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL 
		FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND 
		WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND MAT.CUNIDMCODI = UND.CUNIDMCODI 
   		AND MAT.fmatepsust = 'S'
 		ORDER BY MAT.EMATEPDESC " ;
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				$pdf->Cell(249,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
				$pdf->Cell(16,5,"CÓD.RED",1, 0,"C",1);
				$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);								
				for( $i=0; $i< $rows; $i++ ){
						$Linha = $res->fetchRow();
						$MaterialSequencia  = $Linha[0];
						$MaterialDescricao  = $Linha[1];
						$UndMedidaSigla     = $Linha[2];
						# Quebra de Linha para Descrição do Material #
						$DescMaterialSepara = SeparaFrase($MaterialDescricao,130);
						$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
						if( $TamDescMaterial <= 249 ){
								$LinhasMat = 1;
								$AlturaMat = 5;
						}elseif( $TamDescMaterial > 249 and $TamDescMaterial <= 495 ){
								$LinhasMat = 2;
								$AlturaMat = 10;
						}else{
								$LinhasMat = 3;
								$AlturaMat = 15;
						}
						if( $TamDescMaterial > 249 ){
								$Inicio = 0;
								$pdf->Cell(249,$AlturaMat,"",1,0,"L",0);
								for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
										if( $Quebra == 0 ){
									  		$pdf->SetX(10);
											  $pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Cell(16,$AlturaMat,$MaterialSequencia,1,0,"C",0);											  
												$pdf->Cell(15,$AlturaMat,$UndMedidaSigla,1,0,"C",0);
												$pdf->Ln(5);
										}elseif( $Quebra == 1 ){
												$pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
									  		$pdf->Ln(5);
									  }else{
												$pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Ln(5);
									  }
										$Inicio = $Inicio + 130;
								}
						}else{
								$pdf->Cell(249,5,$MaterialDescricao,1,0,"L",0);
								$pdf->Cell(16,5,$MaterialSequencia,1,0,"C",0);								
								$pdf->Cell(15,5,$UndMedidaSigla,1,1,"C",0);
						}
				}
		}		
		$pdf->Cell(265,5,"TOTAL DE ITENS",1,0,"R",1);
		$pdf->Cell(15,5,$rows,1,"R",0);
}
$db->disconnect();
$pdf->Output();
?> 