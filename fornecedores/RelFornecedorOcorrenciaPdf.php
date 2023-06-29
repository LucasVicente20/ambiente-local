<?php
#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorOcorrenciaPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão das Ocorrências do Fornecedor
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Sequencial  = $_GET['Sequencial'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Ocorrências dos Fornecedores";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Pega os dados do Fornecedor #
$db   = Conexao();
$sql  = "SELECT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, ";
$sql .= "       A.NFORCRRAZS, B.EFOROCDETA, B.DFOROCDATA, C.EFORTODESC ";
$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDOROCORRENCIA B, SFPC.TBFORNTIPOOCORRENCIA C ";
$sql .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND A.AFORCRSEQU = $Sequencial";
$sql .= "   AND B.CFORTOCODI = C.CFORTOCODI";
$sql .= " ORDER BY B.DFOROCDATA DESC, C.EFORTODESC";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		for( $i=0; $i< $rows; $i++ ){
				$Linha = $res->fetchRow();
				if( $i== 0 ){
						$DataCadastramento = DataBarra($Linha[0]);
						if( strlen($Linha[2]) == 14 ){
								$Tipo     = 1;
								$CPF_CNPJ = FormataCNPJ($Linha[2]);
						}else{
								$Tipo     = 2;
								$CPF_CNPJ = FormataCPF($Linha[3]);
						}
						$pdf->Cell(40,5,"Código do Fornecedor",1,0,'L',1);
						$pdf->Cell(150,5,$Sequencial,1,0,'L',0);
						$pdf->ln(5);
						if ($Tipo  == 1) {
								$RazaoSocial    = $Linha[4];
								$pdf->Cell(40,5,"Razão Social",1,0,'L',1);
								$pdf->Cell(150,5,$RazaoSocial,1,0,'L',0);
								$pdf->ln(5);
								$pdf->Cell(40,5,"CNPJ",1,0,'L',1);
								$pdf->Cell(150,5,$CPF_CNPJ,1,0,'L',0);
								$pdf->ln(5);
						} else {
								$Nome    = $Linha[4];
								$pdf->Cell(40,5,"Nome",1,0,'L',1);
								$pdf->Cell(150,5,$Nome,1,0,'L',0);
								$pdf->ln(5);
								$pdf->Cell(40,5,"CPF",1,0,'L',1);
								$pdf->Cell(150,5,$CPF_CNPJ,1,0,'L',0);
								$pdf->ln(5);
						}
						$pdf->Cell(40,5,"Data de Cadastramento",1,0,'L',1);
						$pdf->Cell(150,5,$DataCadastramento,1,0,'L',0);
						$pdf->ln(10);
						$pdf->Cell(190,5,"OCORRÊNCIAS",1,1,'C',1);
				}
				$Detalhamento   = $Linha[5];
				$DataOcorrencia = DataBarra($Linha[6]);
				$Ocorrencia     = $Linha[7];

				$pdf->Cell(40,5,"Data da Ocorrência",1,0,'L',1);
				$pdf->Cell(150,5,$DataOcorrencia,1,0,'L',0);
				$pdf->ln(5);
				$pdf->Cell(40,5,"Ocorrência",1,0,'L',1);
				$pdf->Cell(150,5,$Ocorrencia,1,0,'L',0);
				$pdf->ln(5);
				$pdf->Cell(190,5,"Detalhamento",1,1,'C',1);
				$pdf->MultiCell(190,5,$Detalhamento,1,'L',0);
		}
}
$db->disconnect();
$pdf->Output();
?> 
