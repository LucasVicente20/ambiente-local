<?php
#-------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInscritoPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão do Fornecedor Inscrito
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelInscrito.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$DataIni  = $_GET['DataIni'];
		$DataFim  = $_GET['DataFim'];
		$Situacao = $_GET['Situacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Invertendo as datas para pesquisa #
$DataIniInv = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimInv = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Fornecedores Inscritos de ".$DataIni." à ".$DataFim;

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

# Pega os dados da Inscrição do Fornecedor #
$db      = Conexao();
$sqlpre  = "SELECT A.APREFOSEQU, A.APREFOCCGC, A.APREFOCCPF, A.NPREFORAZS, ";
$sqlpre .= "       A.DPREFOGERA, B.CPREFSCODI, B.EPREFSDESC, A.EPREFOMOTI ";
$sqlpre .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B ";
$sqlpre .= " WHERE A.DPREFOGERA >= '$DataIniInv' AND A.DPREFOGERA <= '$DataFimInv' ";
$sqlpre .= "   AND A.CPREFSCODI = B.CPREFSCODI ";
if( $Situacao != "" and  $Situacao != "T"){
		$sqlpre .= " AND  B.CPREFSCODI = $Situacao";
}
$sqlpre .= " ORDER BY B.EPREFSDESC, A.DPREFOGERA DESC, A.NPREFORAZS ";

$respre = $db->query($sqlpre);
if( PEAR::isError($respre) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$SituacaoAntes = "";
		$rows          = $respre->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhum Fornecedor Encontrado para Esse Período";
				$Url = "RelInscrito.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				$SituacaoAntes == "";
				for($i=0;$i<$rows;$i++ ){
						$Linha      = $respre->fetchRow();
						$Sequencial = $Linha[0];
						if( strlen($Linha[1]) == 14 ){
								$CPF_CNPJ = $Linha[1];
						}else{
								$CPF_CNPJ = $Linha[2];
						}
						$RazaoSocial = $Linha[3];
						$DataGeracao = DataBarra($Linha[4]);
						$Situacao    = $Linha[5];
						$Descricao   = $Linha[6];
						$Motivo      = strtoupper2($Linha[7]);

						if( $SituacaoAntes != $Situacao and $SituacaoAntes != "" ){
								$pdf->Cell(190,5,"TOTAL $DescricaoAntes: ".$Qtd,1,1,"L",1);
								$pdf->ln(5);
								$Qtd = 0;
						}
						$Qtd++;

						$pdf->Cell(35,5,"Código da Inscrição",1,0,'L',1);
						$pdf->Cell(155,5,$Sequencial,1,0,'L',0);
						$pdf->ln(5);

						# Formata o CNPJ ou o CPF #
						if( strlen($CPF_CNPJ) == 14 ){
								$Tipo     = 1;
								$CPF_CNPJForm = substr($CPF_CNPJ,0,2).".".substr($CPF_CNPJ,2,3).".".substr($CPF_CNPJ,5,3)."/".substr($CPF_CNPJ,8,4)."-".substr($CPF_CNPJ,12,2);
						}elseif( strlen($CPF_CNPJ) == 11 ){
								$Tipo     = 2;
								$CPF_CNPJForm = substr($CPF_CNPJ,0,3).".".substr($CPF_CNPJ,3,3).".".substr($CPF_CNPJ,6,3)."-".substr($CPF_CNPJ,9,2);
						}
						$pdf->Cell(35,5,"Razão Social/Nome",1,0,'L',1);
						$pdf->Cell(155,5,$RazaoSocial,1,0,'L',0);
						$pdf->ln(5);
						$pdf->Cell(35,5,"CNPJ/CPF",1,0,'L',1);
						$pdf->Cell(155,5,$CPF_CNPJForm,1,0,'L',0);
						$pdf->ln(5);
						$pdf->Cell(35,5,"Data de Inscrição",1,0,'L',1);
						$pdf->Cell(155,5,$DataGeracao,1,0,'L',0);
						$pdf->ln(5);
						if( $Motivo != "" ){
								$pdf->Cell(35,5,"Motivo",1,0,'L',1);
								$pdf->Cell(155,5,$Motivo,1,0,'L',0);
								$pdf->ln(5);
						}
						$pdf->Cell(35,5,"Situação",1,0,'L',1);
						$pdf->Cell(155,5,$Descricao,1,0,'L',0);
						$pdf->ln(10);

						$SituacaoAntes  = $Situacao;
						$DescricaoAntes = $Descricao;
				}
		}
}
$pdf->Cell(190,5,"TOTAL $DescricaoAntes: ".$Qtd,1,1,"L",1);
$pdf->ln(5);

$db->disconnect();
$pdf->Output();
?> 
