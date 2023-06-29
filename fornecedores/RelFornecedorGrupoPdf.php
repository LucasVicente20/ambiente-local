<?php
#----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorGrupoPdf.php
# Autor:    Roberta Costa
# Data:     25/10/04
# Objetivo: Programa de Impressão do Fornecedor por Classe de Fornecimento
#---------------------
# Alterado: Ariston Cordeiro
# Data:     30/05/11-	Alterando para mostrar grupos ao invés de classes. Adicionando mais informações.
#---------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorGrupo.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Grupo     = $_GET['Grupo'];
		$TipoGrupo = $_GET['TipoGrupo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Fornecedores por Fornecimento";

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
$db      = Conexao();
$sqlpre  = "SELECT A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS, ";
$sqlpre .= "       A.DFORCRGERA, A.AFORCRCDDD, A.AFORCRTELS, A.AFORCRNFAX,
									 D.EGRUMSDESC, A.NFORCRMAIL, A.NFORCRMAI2, A.FFORCRTIPO,
									 A.EFORCRLOGR, A.AFORCRNUME, A.EFORCRCOMP, A.EFORCRBAIR,
									 A.NFORCRCIDA, A.CFORCRESTA
";
$sqlpre .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBGRUPOFORNECEDOR B, ";
$sqlpre .= "       SFPC.TBGRUPOMATERIALSERVICO D  ";
$sqlpre .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND B.CGRUMSCODI = $Grupo ";
$sqlpre .= "   AND B.CGRUMSCODI = D.CGRUMSCODI ";
$sqlpre .= " ORDER BY A.NFORCRRAZS";
$respre  = $db->query($sqlpre);
if( PEAR::isError($respre) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
}else{
		$rows = $respre->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelFornecedorGrupo.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0;$i< $rows;$i++ ){
						$Linha      = $respre->fetchRow();
						$Sequencial = $Linha[0];
						if( strlen($Linha[1]) == 14 ){
								$CPF_CNPJ = $Linha[1];
						}else{
								$CPF_CNPJ = $Linha[2];
						}
						$RazaoSocial = $Linha[3];
						$DataInsc    = $Linha[4];
						$FornDDD     = $Linha[5];
						$FornFones   = $Linha[6];
						$FornFax     = $Linha[7];
						$GrupoDesc   = $Linha[8];

						$FornEMails   = $Linha[9];
						if($Linha[10]){
							if($Linha[9]){
								$FornEMails .= ", ";
							}
							$FornEMails .= $Linha[10];
						}
						$FornETipoVar = $Linha[11];
						$Logradouro = $Linha[12];
						$Numero = $Linha[13];
						$Complemento = $Linha[14];
						$Bairro = $Linha[15];
						$Cidade = $Linha[16];
						$Estado = $Linha[17];

						$Endereco = $Logradouro;
						if($Numero){
							$Endereco .= ", ".$Numero;
						}
						if($Complemento){
							$Endereco .= " - ".$Complemento;
						}
						if($Bairro){
							$Endereco .= " - ".$Bairro;
						}
						$Endereco .= " - ".$Cidade;
						$Endereco .= "/".$Estado;

						$FornETipo = "";
						if($FornETipoVar=="L"){
							$FornETipo = "LICITAÇÃO";
						}else if($FornETipoVar=="D"){
							$FornETipo = "COMPRA DIRETA";
						}else if($FornETipoVar=="L"){
							$FornETipo = "ESTOQUE";
						}

						$sql = "
							SELECT FTS.EFORTSDESC
							FROM SFPC.TBFORNSITUACAO FS, SFPC.TBfornecedortiposituacao fts
							WHERE
								FS.AFORCRSEQU = $Sequencial AND
								FS.TFORSIULAT = (
									SELECT MAX(FS2.TFORSIULAT)
									FROM SFPC.TBFORNSITUACAO FS2
									WHERE FS2.AFORCRSEQU = $Sequencial
								) AND
								FS.CFORTSCODI = FTS.CFORTSCODI
						";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						$Linha      = $res->fetchRow();
						$Situacao = $Linha[0];
						$sql = "
							select dforchvali
							from sfpc.tbfornecedorCHF
							where aforcrsequ = $Sequencial
						";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						$Linha      = $res->fetchRow();
						$DataCHF = $Linha[0];
						if( $i == 0 ){
								if( $TipoGrupo == "M" ){
										$Tipo = "MATERIAL";
								}elseif( $TipoGrupo == "S" ){
										$Tipo = "SERVIÇO";
								}
								$pdf->Cell(40,5,"Tipo do Grupo",1,0,'L',1);
								$pdf->Cell(150,5,$Tipo,1,0,'L',0);
								$pdf->ln(5);
								$pdf->Cell(40,5,"Grupo",1,0,'L',1);
								$pdf->Cell(150,5,$GrupoDesc,1,0,'L',0);
								$pdf->ln(10);
						}

						$pdf->Cell(40,5,"Código do Fornecedor",1,0,'L',1);
						$pdf->Cell(150,5,$Sequencial,1,0,'L',0);
						$pdf->ln(5);

						# Formata o CNPJ ou o CPF #
						if( strlen($CPF_CNPJ) == 14 ){
								$CPF_CNPJ = substr($CPF_CNPJ,0,2).".".substr($CPF_CNPJ,2,3).".".substr($CPF_CNPJ,5,3)."/".substr($CPF_CNPJ,8,4)."-".substr($CPF_CNPJ,12,2);
								$pdf->Cell(40,5,"Razão Social",1,0,'L',1);
								$pdf->Cell(150,5,$RazaoSocial,1,0,'L',0);
								$pdf->ln(5);
								$pdf->Cell(40,5,"CNPJ",1,0,'L',1);
								$pdf->Cell(150,5,$CPF_CNPJ,1,0,'L',0);
								$pdf->ln(5);
						}elseif( strlen($CPF_CNPJ) == 11 ){
								$CPF_CNPJ = substr($CPF_CNPJ,0,3).".".substr($CPF_CNPJ,3,3).".".substr($CPF_CNPJ,6,3)."-".substr($CPF_CNPJ,9,2);
								$pdf->Cell(40,5,"Nome",1,0,'L',1);
								$pdf->Cell(150,5,$RazaoSocial,1,0,'L',0);
								$pdf->ln(5);
								$pdf->Cell(40,5,"CPF",1,0,'L',1);
								$pdf->Cell(150,5,$CPF_CNPJ,1,0,'L',0);
								$pdf->ln(5);
						}
						$pdf->Cell(40,5,"Data de Cadastramento",1,0,'L',1);
						$pdf->Cell(150,5,substr($DataInsc,8,2)."/".substr($DataInsc,5,2)."/".substr($DataInsc,0,4)." ".substr($DataInsc,11,5),1,0,'L',0);
						$pdf->ln(5);

            //Campo Endereço
            $TamEndereco = strlen($Endereco);

            $QtdeLinha = ceil( $TamEndereco / 75); // O tamanho da linha é 81, então ao dividir pela metade de uma string com 2 linhas (tamanho = 82 em diante), peguei o multiplo para calcular a quantidade de linhas.
            $Linha = 5 * $QtdeLinha;

						$pdf->Cell(40,$Linha,"Endereço",1,0,'L',1);
						//$pdf->Cell(40,$Linha,$Endereco,1,0,'L',1);
						$pdf->MultiCell(150,5,$Endereco,1,"L",0);


						if( $FornFones <> "" ){
								$pdf->Cell(40,5,"Telefone(s)",1,0,'L',1);
								if( $FornDDD <> "" ){
										$pdf->Cell(6,5,"(".$FornDDD.") ","LTB",0,'L',0);
								}
								$pdf->Cell(144,5,$FornFones,"RTB",0,'L',0);
								$pdf->ln(5);
						}
						if( $FornFax <> "" ){
								$pdf->Cell(40,5,"Fax",1,0,'L',1);
								$pdf->Cell(150,5,$FornFax,1,0,'L',0);
								$pdf->ln(5);
						}
						if( $FornEMails <> "" ){
								$pdf->Cell(40,5,"Email(s)",1,0,'L',1);
								$pdf->Cell(150,5,$FornEMails,1,0,'L',0);
								$pdf->ln(5);
						}
						if( $DataCHF <> "" ){
								$pdf->Cell(40,5,"Data de validade do CHF",1,0,'L',1);
								$pdf->Cell(150,5,DataBarra($DataCHF),1,0,'L',0);
								$pdf->ln(5);
						}
						$pdf->Cell(40,5,"Tipo de fornecedor",1,0,'L',1);
						$pdf->Cell(150,5,$FornETipo,1,0,'L',0);
						$pdf->ln(5);

						$pdf->Cell(40,5,"Situação",1,0,'L',1);
						$pdf->Cell(150,5,$Situacao,1,0,'L',0);
						$pdf->ln(10);
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
