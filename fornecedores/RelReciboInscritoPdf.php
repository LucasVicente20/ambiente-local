<?php
#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelReciboInscritoPdf.php
# Autor:    Roberta Costa
# Data:     05/10/2004
# Objetivo: Programa de Impressão do Recibo do Cadastro do Fornecedor Inscrito
# Alterado: Rossana Lira
# Data:     14/05/2007 - Mudança do nome da senha de provisória para temporária
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
		$CodigoInsc = $_GET['CodigoInsc'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Pega os dados da Inscrição do Fornecedor #
$db      = Conexao();
$sqlpre  = "SELECT APREFOCCGC, APREFOCCPF, NPREFORAZS, NPREFOSENH, ";
$sqlpre .= "       CCEPPOCODI, CCELOCCODI, EPREFOLOGR, APREFONUME, ";
$sqlpre .= "       EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA, DPREFOGERA ";
$sqlpre .= "FROM SFPC.TBPREFORNECEDOR WHERE APREFOSEQU = $CodigoInsc";
$respre = $db->query($sqlpre);
if( PEAR::isError($respre) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
}else{
		$Linha = $respre->fetchRow();
		if( strlen($Linha[0]) == 14 ){
				$CPF_CNPJ = $Linha[0];
		}else{
				$CPF_CNPJ = $Linha[1];
		}
		$RazaoSocial = $Linha[2];
		$Senha       = $Linha[3];
		if( $Linha[4] != "" ){
				$Cep = $Linha[4];
		}else{
				$Cep = $Linha[5];
		}
		$Logradouro  = $Linha[6];
		$Numero      = $Linha[7];
		$Complemento = $Linha[8];
		$Bairro      = $Linha[9];
		$Cidade      = $Linha[10];
		$Estado      = $Linha[11];
		$DataInsc    = $Linha[12];

		# Colocando o Endereço Agrupado #
		if( $Numero == ""){ $Numero = "S/N"; }
		if( $Complemento != "" ){
				$Endereco = $Logradouro.", ".$Numero." ".$Compemento." - ".$Bairro." ".$Cidade."/".$Estado." CEP:".$Cep;
		}else{
				$Endereco = $Logradouro.", ".$Numero." - ".$Bairro." ".$Cidade."/".$Estado." CEP:".$Cep;
		}
}
$db->disconnect();

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Recibo de Inscrição de Fornecedor";

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

$pdf->Cell(35,5,"Código da Inscrição",1,0,'L',1);
$pdf->Cell(155,5,$CodigoInsc,1,0,'L',0);
$pdf->ln(5);

# Gera o Número de Controle do Fornecedor #
$Numero      = $CodigoInsc.$CPF_CNPJ.date("Ymd");
$NumControle = ControlaDocumento($Numero);

# Formata o CNPJ ou o CPF #
if( strlen($CPF_CNPJ) == 14 ){
		$CPF_CNPJ = substr($CPF_CNPJ,0,2).".".substr($CPF_CNPJ,2,3).".".substr($CPF_CNPJ,5,3)."/".substr($CPF_CNPJ,8,4)."-".substr($CPF_CNPJ,12,2);
		$pdf->Cell(35,5,"CNPJ",1,0,"L",1);
		$pdf->Cell(155,5,$CPF_CNPJ,1,0,"L",0);
		$pdf->ln(5);
		$pdf->Cell(35,5,"Razão Social",1,0,"L",1);
}elseif( strlen($CPF_CNPJ) == 11 ){
		$CPF_CNPJ = substr($CPF_CNPJ,0,3).".".substr($CPF_CNPJ,3,3).".".substr($CPF_CNPJ,6,3)."-".substr($CPF_CNPJ,9,2);
		$pdf->Cell(35,5,"CPF",1,0,"L",1);
		$pdf->Cell(155,5,$CPF_CNPJ,1,0,"L",0);
		$pdf->ln(5);
		$pdf->Cell(35,5,"Nome",1,0,"L",1);
}
$pdf->Cell(155,5,$RazaoSocial,1,0,"L",0);
$pdf->ln(5);
$TamEndereco = strlen($Endereco);
if( $TamEndereco < 86 ){
		$Linha = 5;
}elseif( $TamEndereco >= 86 and  $TamEndereco < 182 ){
		$Linha = 10;
}else{
		$Linha = 15;
}
$pdf->Cell(35,$Linha,"Endereço",1,0,"L",1);
$pdf->MultiCell(155,5,$Endereco,1,"L",0);
$pdf->Cell(35,5,"Senha Temporária",1,0,"L",1);
$pdf->Cell(155,5,$_SESSION['Senha'],1,0,"L",0);
$pdf->ln(5);
$pdf->Cell(35,5,"Data de Inscrição",1,0,"L",1);
$pdf->Cell(155,5,substr($DataInsc,8,2)."/".substr($DataInsc,5,2)."/".substr($DataInsc,0,4)." ".substr($DataInsc,11,5),1,0,'L',0);
$pdf->ln(5);
$pdf->Cell(35,5,"Número de Controle",1,0,"L",1);
$pdf->Cell(155,5,"1".$Numero."-".$NumControle,1,0,"L",0);
$pdf->Output();
?> 
