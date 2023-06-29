<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelListagemFornecedorPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão do Fornecedor de acordo com a Situação
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelListagemFornecedor.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Ordem = $_GET['Ordem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Listagem de Fornecedores";

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
$db   = Conexao();
$sql  = "SELECT AFORCRSEQU, NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
if( $Ordem == "C" ){
		$sql .= " ORDER BY AFORCRSEQU";
}elseif( $Ordem == "N" ){
		$sql .= " ORDER BY NFORCRRAZS";
}
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelListagemFornecedor.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha        = $res->fetchRow();
						$Sequencial   = $Linha[0];
						$RazaoSocial  = $Linha[1];

						if( $i == 0 ){
								$pdf->Cell(15,5,"CÓDIGO",0,0,"L",1);
								$pdf->Cell(153,5,"NOME/RAZÃO SOCIAL",0,0,"L",1);
								$pdf->Cell(22,5,"DATA CHF",0,1,"C",1);
						}
						$pdf->Cell(15,5,$Sequencial,0,0,"L",0);
						$pdf->Cell(153,5,substr($RazaoSocial,0,85),0,0,"L",0);

						$sqlchf  = "SELECT DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
						$sqlchf .= " WHERE AFORCRSEQU = $Sequencial";
						$reschf  = $db->query($sqlchf);
						if( PEAR::isError($reschf) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlchf");
						}else{
								$Linha = $reschf->fetchRow();
								if( $Linha[0] != "" ){
										$DataValidade = DataBarra($Linha[0]);
								}else{
										$DataValidade = "-";
								}
						}
						$pdf->Cell(22,5,$DataValidade,0,1,"C",0);
				}
		}
}
$db->disconnect();
$pdf->Output();
?> 
