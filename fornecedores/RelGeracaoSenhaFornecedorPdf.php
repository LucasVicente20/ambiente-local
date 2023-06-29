<?php
#--------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelGeracaoSenhaFornecedorPdf.php
# Autor:    Rossana Lira
# Data:     28/07/04
# Objetivo: Programa de Impressão da Geração de Senha do Fornecedor ou Inscrito
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$CNPJCPF			 = $_GET['CNPJCPF'];
		$Razao				 = $_GET['Razao'];
		$Senha				 = $_GET['Senha'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Geração de Senha Temporária do Fornecedor ou Inscrito";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

#Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$pdf->Cell(35,5,"CNPJ/CPF",1,0,'L',1);
if( strlen($CNPJCPF) == 14 ){
		$Formato = (substr($CNPJCPF,0,2).".".substr($CNPJCPF,2,3).".".substr($CNPJCPF,5,3)."/".substr($CNPJCPF,8,4)."-".substr($CNPJCPF,12,2));
}else{
		$Formato = (substr($CNPJCPF,0,3).".".substr($CNPJCPF,3,3).".".substr($CNPJCPF,6,3)."-".substr($CNPJCPF,9,2));
}
$pdf->Cell(155,5,$Formato,1,0,'L',0);
$pdf->ln(5);
$pdf->Cell(35,5,"Razão Social",1,0,'L',1);
$pdf->Cell(155,5,$Razao,1,0,'L',0);
$pdf->ln(5);
$pdf->Cell(35,5,"Senha Temporária",1,0,'L',1);
$pdf->Cell(155,5,$Senha,1,0,'L',0);
$pdf->ln(5);
$pdf->Output();
?> 
