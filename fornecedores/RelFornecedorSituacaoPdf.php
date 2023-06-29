<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorSitucaoPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão do Fornecedor de acordo com a Situação
#---------------------------------
# Alterado: Ariston Cordeiro
# Data:     06/01/09 - 	Listar todos fornecedores de uma determinada situação
#						Editado select para apenas trazer fornecedores daquela situação.
# 						Antes estava trazendo todos fornecedores com código menores que 100, para depois pegar apenas os daquela situação
#---------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorSituacao.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Situacao = $_GET['Situacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Fornecedores Por Situação";

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

/*
 // Select anterior
	SELECT
		MAX(B.DFORSISITU), A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS,
		A.CCEPPOCODI, A.EFORCRLOGR, A.AFORCRNUME, A.EFORCRCOMP, A.EFORCRBAIR,
		A.NFORCRCIDA, A.CFORCRESTA, A.DFORCRGERA
	FROM
		SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNSITUACAO B
	WHERE
		A.AFORCRSEQU = B.AFORCRSEQU
		and  A.AFORCRSEQU < 100
	GROUP BY
		A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS, A.CCEPPOCODI,
		A.EFORCRLOGR, A.AFORCRNUME, A.EFORCRCOMP, A.EFORCRBAIR, A.NFORCRCIDA,
		A.CFORCRESTA, A.DFORCRGERA
*/

$sql  = "
	SELECT
		B.DFORSISITU AS DATASITUACAO, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS,
		A.CCEPPOCODI, A.EFORCRLOGR, A.AFORCRNUME, A.EFORCRCOMP, A.EFORCRBAIR,
		A.NFORCRCIDA, A.CFORCRESTA, A.DFORCRGERA
	FROM
		SFPC.TBFORNECEDORCREDENCIADO A
			LEFT OUTER JOIN SFPC.TBFORNSITUACAO B
				ON  B.AFORCRSEQU = A.AFORCRSEQU
				AND B.DFORSISITU = ( -- última situação em vigor
					SELECT MAX(FS2.DFORSISITU)
					FROM SFPC.TBFORNSITUACAO FS2
					WHERE  FS2.AFORCRSEQU = A.AFORCRSEQU
				)
	WHERE
		CFORTSCODI = $Situacao
";

$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelFornecedorSituacao.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha        = $res->fetchRow();
						$DataSituacao = DataBarra($Linha[0]);
						$Sequencial   = $Linha[1];
						if( $Linha[2] != "" ){
								$CPF_CNPJ = $Linha[2];
						}else{
								$CPF_CNPJ = $Linha[3];
						}
						$RazaoSocial   = $Linha[4];
						$Cep           = $Linha[5];
						$Logradouro    = $Linha[6];
						$Numero        = $Linha[7];
						$Complemento   = $Linha[8];
						$Bairro        = $Linha[9];
						$Cidade        = $Linha[10];
						$Estado        = $Linha[11];
						$DataInsc      = DataBarra($Linha[12]);

						# Colocando o Endereço Agrupado #
						if( $Logradouro != "" ){
								if( $Numero == ""){ $Numero = "S/N"; }
								if( $Complemento != "" ){
										$Endereco = $Logradouro.", ".$Numero." ".$Compemento." - ".$Bairro." ".$Cidade."/".$Estado;
								}else{
										$Endereco = $Logradouro.", ".$Numero." - ".$Bairro." ".$Cidade."/".$Estado;
								}
						}else{
								$Endereco = "";
						}

						# Formata o CNPJ ou o CPF #
						if( strlen($CPF_CNPJ) == 14 ){
								$Tipo     = 1;
								$CPF_CNPJ = FormataCNPJ($CPF_CNPJ);
						}elseif( strlen($CPF_CNPJ) == 11 ){
								$Tipo     = 2;
								$CPF_CNPJ = FormataCPF($CPF_CNPJ);
						}
						# Pegando os Dados da Situação #
						$sqlsit  = "
							SELECT
								A.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, B.EFORTSDESC
							FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B
							WHERE
								A.CFORTSCODI = B.CFORTSCODI
								AND A.DFORSISITU = '".DataInvertida($DataSituacao)."'
								AND A.AFORCRSEQU = $Sequencial
							ORDER BY A.TFORSIULAT DESC --Garantir que a última modificação da data de situação mais recente esteja na 1a linha
						";

						$ressit  = $db->query($sqlsit);
						if( PEAR::isError($ressit) ){
								ExibeErroBD("$ErroPrograma\nLinha: 115\nSql: $sqlsit");
						}else{
								$Linha          = $ressit->fetchRow();
								$SituacaoCodigo = $Linha[0];
								$Motivo         = $Linha[1];
								$DataExpiracao  = DataBarra($Linha[2]);
								$DescSituacao   = $Linha[3];
								if( $SituacaoCodigo == $Situacao ){
										$cont++;

										$pdf->Cell(40,5,"Código da Inscrição",1,0,"L",1);
										$pdf->Cell(150,5,$Sequencial,1,1,"L",0);
										$pdf->Cell(40,5,"Razão Social/Nome",1,0,"L",1);
										$pdf->Cell(150,5,$RazaoSocial,1,1,"L",0);
										$pdf->Cell(40,5,"CNPJ/CPF",1,0,"L",1);
										$pdf->Cell(150,5,$CPF_CNPJ,1,1,"L",0);
										$pdf->Cell(40,5,"Data de Cadastramento",1,0,"L",1);
										$pdf->Cell(150,5,$DataInsc,1,1,"L",0);

										if( $SituacaoCodigo != 5 ){
												$TamEndereco = strlen($Endereco);
												if( $TamEndereco < 86 ){
														$Linha = 5;
												}elseif( $TamEndereco >= 86 and  $TamEndereco < 182 ){
														$Linha = 10;
												}else{
														$Linha = 15;
												}
												$pdf->Cell(40,$Linha,"Endereço",1,0,"L",1);
												$pdf->MultiCell(150,5,$Endereco,1,"L",0);
										}
										$pdf->Cell(40,5,"Data da Situação",1,0,"L",1);
										$pdf->Cell(150,5,$DataSituacao,1,1,"L",0);
										$pdf->Cell(40,5,"Situação",1,0,"L",1);
										$pdf->Cell(150,5,$DescSituacao,1,1,"L",0);
										if( $Motivo != "" ){
												$pdf->Cell(40,5,"Motivo",1,0,"L",1);
												$pdf->Cell(150,5,$Motivo,1,1,"L",0);
										}
										if( $DataExpiracao != "" and ( $SituacaoCodigo == 3 or $SituacaoCodigo == 6 ) ){
												$pdf->Cell(40,5,"Data Expiração",1,0,"L",1);
												$pdf->Cell(150,5,$DataExpiracao,1,1,"L",0);
										}
										$pdf->ln(5);
								}
						}
				}
				if( $cont == 0 ){
						$Mensagem = "Nenhuma Ocorrência Encontrada";
						$Url = "RelFornecedorSituacao.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
