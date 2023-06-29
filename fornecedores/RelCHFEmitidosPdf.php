<?php
#--------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelCHFEmitidosPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão dos CHF Emitidos num Período
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------
# Alterado: Lucas André e Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282898
# -----------------------------------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelCHFEmitidos.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$DataIni  = $_GET['DataIni'];
		$DataFim  = $_GET['DataFim'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Invertendo as datas para pesquisa #

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de CHF's Emitidos no Período de ".$DataIni." à ".$DataFim;;

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

# Pega os Dados do Fornecedor Cadastrado #
$db	  = Conexao();
$sql  = " SELECT A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS, ";
$sql .= "        A.DFORCRGERA, B.DFORCHGERA, B.DFORCHVALI, B.AFORCHNEMF, ";
$sql .= "        B.DFORCHULEF, B.AFORCHNEMU, B.DFORCHULEU, B.CGREMPCOD1, ";
$sql .= "        B.CUSUPOCOD1 ";
$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDORCHF B ";
$sql .= "  WHERE B.DFORCHGERA >= '$DataIni' AND B.DFORCHGERA <= '$DataFim' ";
$sql .= "    AND A.AFORCRSEQU = B.AFORCRSEQU ";
$sql .= "  ORDER BY B.DFORCHVALI DESC, A.NFORCRRAZS";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelCHFEmitidos.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha      = $res->fetchRow();
						$Sequencial	= $Linha[0];
						$CNPJ				= $Linha[1];
						$CPF				= $Linha[2];
						if( $CNPJ != 0 ){
								$CPF_CNPJ     = $CNPJ;
								$DescCNPJCPF  = "CNPJ";
								$CNPJCPFForm	= substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
						}else{
								$CPF_CNPJ     = $CPF;
								$DescCNPJCPF  = "CPF";
								$CNPJCPFForm  = substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
						}
						$RazaoSocial  	  = $Linha[3];
						$DataInscricao    = DataBarra($Linha[4]);
						$DataGeracaoCHF   = DataBarra($Linha[5]);
						$DataValidade     = DataBarra($Linha[6]);
						$NumeroFornecedor = $Linha[7];
						$DataFornecedor   = $Linha[8];
						$NumeroPrefeitura = $Linha[9];
						$DataPrefeitura   = $Linha[10];
						$Grupo            = $Linha[11];
						$Usuario          = $Linha[12];

						if( $Grupo != "" and $Usuario != "" ){
								# Pega o Nome do Responsável #
								$sqlres  = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL";
								$sqlres .= " WHERE CGREMPCODI = $Grupo AND CUSUPOCODI = $Usuario";
								$resres  = $db->query($sqlres);
								if( PEAR::isError($resres) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlres");
								}else{
										$Linha 	     = $resres->fetchRow();
										$Responsavel = $Linha[0];
								}
						}

						if( $NumeroFornecedor != "" or $NumeroPrefeitura != "" ){
								$pdf->Cell(60,5,$DescCNPJCPF,1,0,'L',1);
								$pdf->Cell(130,5,$CNPJCPFForm,1,1,'L',0);
								if( $CNPJ != 0 ){
										$pdf->Cell(60,5,'Razão Social',1,0,'L',1);
								}else{
										$pdf->Cell(60,5,'Nome do Fornecedor',1,0,'L',1);
								}
								$pdf->Cell(130,5,strtoupper2($RazaoSocial),1,1,'L',0);
								$pdf->Cell(60,5,'Número de CHF',1,0,'L',1);
								$pdf->Cell(130,5,$Sequencial,1,1,'L',0);
								$pdf->Cell(60,5,'Data de Geração',1,0,'L',1);
								$pdf->Cell(130,5,$DataGeracaoCHF,1,1,'L',0);
								$pdf->Cell(60,5,'Data de Validade',1,0,'L',1);
								$pdf->Cell(130,5,$DataValidade,1,1,'L',0);
								$pdf->Cell(60,5,'Número de Emissões do Fornecedor',1,0,'L',1);
								if( $NumeroFornecedor == "" ){ $NumeroFornecedor = "0"; }
								$pdf->Cell(130,5,$NumeroFornecedor,1,1,'L',0);
								$pdf->Cell(60,5,'Última Data de Emissão do Fornecedor',1,0,'L',1);
								if( $DataFornecedor == "" ){ $DataFornecedor = "-"; }else{ $DataFornecedor = DataBarra($DataFornecedor); }
								$pdf->Cell(130,5,$DataFornecedor,1,1,'L',0);
								$pdf->Cell(60,5,'Número de Emissões da Prefeitura',1,0,'L',1);
								if( $NumeroPrefeitura == "" ){ $NumeroPrefeitura = "0"; }
								$pdf->Cell(130,5,$NumeroPrefeitura,1,1,'L',0);
								$pdf->Cell(60,5,'Última Data de Emissão da Prefeitura',1,0,'L',1);
								if( $DataPrefeitura == "" ){ $DataPrefeitura = "-"; }else{ $DataPrefeitura = DataBarra($DataPrefeitura); }
								$pdf->Cell(130,5,$DataPrefeitura,1,1,'L',0);
								$pdf->Cell(60,5,'Responsável pela Emissão da Prefeitura',1,0,'L',1);
								if( $Responsavel == "" ){ $Responsavel = "-"; }
								$pdf->Cell(130,5,$Responsavel,1,1,'L',0);
								$pdf->ln(5);
						}
				}
		}
}

$db->disconnect();
$pdf->Output();
?> 
