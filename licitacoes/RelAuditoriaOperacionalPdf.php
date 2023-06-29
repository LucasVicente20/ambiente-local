<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAuditoriaOperacionalPdf.php
# Autor:    Roberta Costa
# Data:     03/01/05
# Objetivo: Programa que Gera o Relatório de Auditoria Operacional - PDF
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelAuditoriaOperacionalSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$OrgaoLicitante  = $_GET['OrgaoLicitante'];
		$Comissao        = $_GET['Comissao'];
		$Exercicio       = $_GET['Exercicio'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelAuditoriaOperacionalPdf.php";

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Auditoria Por Órgão dos Processos Homologados com Valor Estimado Diferente do Valor Homologado";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","B",9);

# Carrega os dados da licitação selecionada	#
$db     = Conexao();
$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.XLICPOOBJE, A.TLICPODHAB, ";
$sql   .= "       A.VLICPOVALE, A.VLICPOVALH, B.TFASELDATA, C.EORGLIDESC, ";
$sql   .= "       D.ECOMLIDESC ";
$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBFASELICITACAO B, SFPC.TBORGAOLICITANTE C, SFPC.TBCOMISSAOLICITACAO D ";
$sql   .= " WHERE A.VLICPOVALE <> A.VLICPOVALH  AND A.ALICPOANOP = $Exercicio ";
$sql   .= "   AND A.CLICPOPROC = B.CLICPOPROC AND A.ALICPOANOP = B.ALICPOANOP ";
$sql   .= "   AND A.CGREMPCODI = B.CGREMPCODI AND A.CCOMLICODI = B.CCOMLICODI ";
$sql   .= "   AND A.CORGLICODI = B.CORGLICODI AND B.CFASESCODI = 13 ";
$sql   .= "   AND A.CORGLICODI = C.CORGLICODI AND A.CCOMLICODI = D.CCOMLICODI ";
if( $OrgaoLicitante != "" ){
		$sql .= "   AND A.CORGLICODI = $OrgaoLicitante ";
}
if( $Comissao != "" ){
		$sql .= "   AND A.CCOMLICODI = $Comissao ";
}
$sql   .= " ORDER BY C.EORGLIDESC, D.ECOMLIDESC, A.ALICPOANOP, A.CLICPOPROC ";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Rows = $result->numRows();
		if( $Rows == 0 ){
				$Mensagem = urlencode("Nenhuma Ocorrência Encontrada");
				$Url = "RelAuditoriaOperacionalSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit();
		}else{
				$ComissaoAntes = "";

				for( $i=0; $i < $Rows; $i++ ){
						$Linha             = $result->fetchRow();
						$Processo          = substr($Linha[0] + 10000,1);
						$ProcessoAno       = $Linha[1];
						$Objeto            = trim($Linha[2]);
						$DataHora          = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4)." ".substr($Linha[3],11,5);
						$ValorEstimado     = $Linha[4];
						$ValorHomologado   = $Linha[5];
						$DataHomologacao   = substr($Linha[6],8,2)."/".substr($Linha[6],5,2)."/".substr($Linha[6],0,4);
						$OrgaoDescricao    = $Linha[7];
						$ComissaoDescricao = $Linha[8];

						if( $i == 0 ){
								$pdf->Cell(280,5,"ÓRGÃO LICITANTE",1,1,"C",1);
								$pdf->SetFont("Arial","",9);
								$pdf->Cell(280,5,$OrgaoDescricao,1,1,"C",0);
						}

						if( $ComissaoDescricao != $ComissaoAntes ){
								$pdf->SetFont("Arial","B",9);
								$pdf->Cell(280,5,"COMISSÃO",1,1,"C",1);
								$pdf->SetFont("Arial","",9);
								$pdf->Cell(280,5,$ComissaoDescricao,1,1,"C",0);
								$pdf->SetFont("Arial","B",9);
								$pdf->Cell(20,5,"PROCESSO",1,0,"L",1);
								$pdf->Cell(134,5,"OBJETO",1,0,"L",1);
								$pdf->Cell(30,5,"ABERTURA",1,0,"L",1);
								$pdf->Cell(28,5,"HOMOLOGAÇÃO",1,0,"L",1);
								$pdf->Cell(31,5,"VALOR ESTIMADO",1,0,"C",1);
								$pdf->Cell(37,5,"VALOR HOMOLOGADO",1,1,"C",1);
								$pdf->SetFont("Arial","",9);
								$ComissaoAntes = $ComissaoDescricao;
						}

						$TamObjeto = $pdf->GetStringWidth($Objeto);
						if( $TamObjeto <= 130 ){
								$NumLinhas = 1;
								$AlturaCel = 5;
						}elseif( $TamObjeto > 130 and $TamObjeto <= 242 ){
								$NumLinhas = 2;
								$AlturaCel = 10;
						}elseif( $TamObjeto > 242 and $TamObjeto <= 354 ){
								$NumLinhas = 3;
								$AlturaCel = 15;
						}else{
								$NumLinhas = 4;
								$AlturaCel = 20;
						}

						if( $ValorHomologado > $ValorEstimado ){
								$pdf->SetFont("Arial","U",9);
						}
						$pdf->Cell(20,$AlturaCel,$Processo."/".$ProcessoAno,1,0,"C",0);
						$pdf->SetFont("Arial","",9);

						$Objeto = SeparaFrase($Objeto,65);
						if( $TamObjeto > 130 ){
								$Inicio = 0;
								for( $Quebra = 0; $Quebra < $NumLinhas; $Quebra++ ){
										if( ! $Quebra ){
												$Borda = "T";
										}elseif( $Quebra < $NumLinhas - 1 ){
												$Borda = "";
										}else{
												$Borda = "B";
										}
										if( $Quebra == 0 ){
									  		$pdf->Cell(134,5,substr($Objeto,0,65),$Borda,0,'L',0);
												$pdf->Cell(30,$AlturaCel,$DataHora,1,0,"C",0);
												$pdf->Cell(28,$AlturaCel,$DataHomologacao,1,0,"C",0);
												if( $ValorHomologado > $ValorEstimado ){
														$pdf->SetFont("Arial","B",9);
												}
												$pdf->Cell(31,$AlturaCel,converte_valor($ValorEstimado),1,0,"R",0);
												$pdf->Cell(37,$AlturaCel,converte_valor($ValorHomologado),1,0,"R",0);
												$pdf->SetFont("Arial","",9);
												$pdf->Ln(5);
										}elseif( $Quebra == 1 ){
												$pdf->SetX(30);
									  		$pdf->Cell(134,5,substr($Objeto,$Inicio,65),$Borda,1,"L");
									  }elseif( $Quebra == 2 ){
												$pdf->SetX(30);
									  		$pdf->Cell(134,5,substr($Objeto,$Inicio,65),$Borda,1,"L");
								  	}else{
												$pdf->SetX(30);
									  		$pdf->Cell(134,5,substr($Objeto,$Inicio,65),$Borda,0,"L");
									  		$pdf->Ln(5);
									  }
										$Inicio = $Inicio + 65;
							  }
						}else{
						  	$pdf->Cell(134,$AlturaCel,$Objeto,1,0,'L');
						  	$pdf->Cell(30,$AlturaCel,$DataHora,1,0,"C",0);
								$pdf->Cell(28,$AlturaCel,$DataHomologacao,1,0,"C",0);
								if( $ValorHomologado > $ValorEstimado ){
										$pdf->SetFont("Arial","B",9);
								}
								$pdf->Cell(31,$AlturaCel,converte_valor($ValorEstimado),1,0,"R",0);
								$pdf->Cell(37,$AlturaCel,converte_valor($ValorHomologado),1,0,"R",0);
								$pdf->SetFont("Arial","",9);
								$pdf->Ln(5);
						}
				}
		}
}
$pdf->Output();
?>
