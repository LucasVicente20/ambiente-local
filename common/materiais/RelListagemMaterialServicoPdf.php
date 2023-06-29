<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelListagemMaterialServicoPdf.php
# Autor:    Rossana Lira
# Data:     10/02/05
# Objetivo: Programa de Impressão da Listagem de Material e Serviço
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/RelListagemMaterialServicoSelecionar.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$TipoGrupo	 	= $_GET['TipoGrupo'];
		$Grupo	 		  = $_GET['Grupo'];
		$GrupoTodos	  = $_GET['GrupoTodos'];
		$Classe 			= $_GET['Classe'];
		$ClasseTodas	= $_GET['ClasseTodas'];
		$Ordem			 	= $_GET['Ordem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelListagemMaterialServicoPdf.php";

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Listagem de Material e Serviço";

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
$sql		 = "SELECT A.FGRUMSTIPO, A.CGRUMSCODI, A.EGRUMSDESC, A.FGRUMSSITU, ";
$sql 		.= "       B.CCLAMSCODI, B.ECLAMSDESC, B.FCLAMSSITU ";
$sql 		.= "  FROM SFPC.TBGRUPOMATERIALSERVICO A ";
$sql 		.= " LEFT OUTER JOIN SFPC.TBCLASSEMATERIALSERVICO B ";
$sql 		.= " ON 	A.CGRUMSCODI = B.CGRUMSCODI ";
if (($GrupoTodos == 1) and ($ClasseTodas == 1)) { // Opção Todos os Grupos e Todas as Classes
		$sql	.= " WHERE A.FGRUMSTIPO = '$TipoGrupo' ";
} else {
		if ($ClasseTodas == 1) {
			$sql	.= " WHERE A.CGRUMSCODI = $Grupo ";
		} else {
			$sql 	.= " WHERE A.CGRUMSCODI = $Grupo AND B.CCLAMSCODI = $Classe ";
		}
}
if ($Ordem == "D" ) {
		$sql		.= " ORDER BY A.EGRUMSDESC, B.ECLAMSDESC ";
} else {
		$sql		.= " ORDER BY A.CGRUMSCODI, B.CCLAMSCODI ";
}

$res  	 = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelListagemMaterialServicoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location:	".$Url);
				exit();
		}else{
				$Grupoant  				 = "";
				for( $i=0; $i< $rows; $i++ ){
						$Linha 		     = $res->fetchRow();
						$TipoGrupo	 	 = $Linha[0];
						$CodigoGrupo 	 = $Linha[1];
						$DescGrupo 		 = trim($Linha[2]);
						$Grupo				 = $CodigoGrupo.'. '.$DescGrupo;
						$SituacaoGrupo = $Linha[3];
						$CodigoClasse	 = $Linha[4];
						$DescClasse    = trim($Linha[5]);
						$Classe				 = $CodigoClasse.'. '.$DescClasse;
						$SituacaoClasse= $Linha[6];
						if ($Grupoant == "") {
								if ($TipoGrupo == "M") {
									$pdf->Cell(190,5,"MATERIAL",1,1,'C',0);
								} else {
									$pdf->Cell(190,5,"SERVIÇO",1,1,'C',0);
								}
						}
						if ($Grupo <> $Grupoant) {
								$pdf->ln(3);
								$pdf->SetFont("Arial","B",9);
								$pdf->Cell(15,5,"Grupo: ",0,0,'L',0);
								$pdf->SetFont("Arial","",9);
								$pdf->Cell(150,5,$Grupo,0,0,'L',0);
								if ($SituacaoGrupo <> "A") {
										$pdf->Cell(10,5,'Situação: INATIVO',0,1,'L',0);
								} else {
									$pdf->ln(4);
								}
								if ($CodigoClasse <> "") {
										$pdf->Cell(15,5,"Classe(s): ",0,0,'L',0);
										$PrimeiraClasse = "S";
								}
								$Grupoant = $Grupo;
						}
						if ($CodigoClasse <> "") {
								if ($PrimeiraClasse <> "S" ) {
										$pdf->Cell(15,5,'',0,0,'L',0);
								}
								$pdf->Cell(150,5,$Classe,0,0,'L',0);
								if ($SituacaoClasse <> "A") {
										$pdf->Cell(10,5,'Situação:INATIVA',0,1,'L',0);
										$pdf->ln(1);
								} else {
									$pdf->ln(4);
								}
								$PrimeiraClasse	= "";
						} else {
								$pdf->ln(3);
						}
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
