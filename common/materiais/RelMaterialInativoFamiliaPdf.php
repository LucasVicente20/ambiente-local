<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMaterialFamiliaPdf.php
# Autor:    Filipe Cavalcanti
# Data:     14/09/2005
# Objetivo: Programa de Impressão dos Relatórios de Materiais
#------------------------
# Alterado: Wagner Barros
# Data:     03/10/2006 - Exibir o código reduzido do material ao lado da descrição"
# Alterado: Rossana Lira
# Data:     18/10/2006 - Foi renomeado de RelMaterialPdf para RelMaterialFamiliaPdf
#           para ser criado outro relatório RelMaterialSubclassePdf
# Alterado: Ariston
# Data:     02/01/2009 - Não mostrar materiais inativos
#-------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/RelMaterial.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Grupo     = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
}
// $filtroPesquisa = array(
//     'Subclasse' => ($_SESSION['pdf']['Subclasse']),
//     'Classe' => $_SESSION['pdf']['Classe'],
//     'Grupo' => $_SESSION['pdf']['Grupo'],

// );
// var_dump($filtroPesquisa['SubClasse']);die;
var_dump($Subclasse);die;
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Materiais - Ordenado por Família de Material";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Pega os dados para exibição #
$db   = Conexao();
$sql  = "
	SELECT 
		DISTINCT(GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, SUB.CSUBCLSEQU, 
		SUB.ESUBCLDESC, MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM 
	FROM 
		SFPC.TBMATERIALPORTAL MAT, 
		SFPC.TBGRUPOMATERIALSERVICO GRU, 
		SFPC.TBCLASSEMATERIALSERVICO CLA, 
		SFPC.TBSUBCLASSEMATERIAL SUB, 
		SFPC.TBUNIDADEDEMEDIDA UND 
	WHERE 
		MAT.CSUBCLSEQU = SUB.CSUBCLSEQU 
		AND SUB.CGRUMSCODI = CLA.CGRUMSCODI 
		AND SUB.CCLAMSCODI = CLA.CCLAMSCODI 
		AND CLA.CGRUMSCODI = GRU.CGRUMSCODI 
		AND MAT.CUNIDMCODI = UND.CUNIDMCODI 
		AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU 
	
";

# Verifica se o Grupo foi escolhido #
if( $Grupo != "" ){
  	$sql .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if( $Classe != "" ){
  	$sql .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if( $Subclasse != "" ){
  	$sql .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}
$sql .= " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";

$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
    		$DescGrupoAntes     = "";
        $DescCalsseAntes    = "";
        $DescSubcalsseAntes = "";
				for( $i=0; $i< $rows; $i++ ){
						$Linha = $res->fetchRow();
						$GrupoCodigo        = $Linha[0];
						$GrupoDescricao     = $Linha[1];
						$ClasseCodigo       = $Linha[2];
						$ClasseDescricao    = $Linha[3];
						$SubClasseSequ      = $Linha[4];
						$SubClasseDescricao = $Linha[5];
						$MaterialSequencia  = $Linha[6];
						$MaterialDescricao  = $Linha[7];
						$UndMedidaSigla     = $Linha[8];
						$TipoMaterialCodigo = $Linha[9];
						if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
								if($TipoMaterialCodigo == "C"){
									 	$pdf->Cell(280,5,"CONSUMO",1,1,"C",1);
								}else{
	  								$pdf->Cell(280,5, " ", 0, 1, "R", 0);
									 	$pdf->Cell(280,5,"PERMANENTE",1,1,"C",1);
								}
						}
						if( $GrupoAntes != $GrupoDescricao ){
								$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
								$pdf->Cell(250,5,$GrupoDescricao." / ".$ClasseDescricao,1,1,"L",0);
								$pdf->Cell(30,5,"SUBCLASSE",1,0,"L",1);
								$pdf->Cell(250,5,$SubClasseDescricao,1,1,"L",0);
								$pdf->Cell(249,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
								$pdf->Cell(16,5,"CÓD.RED",1, 0,"C",1);
								$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);								
						}elseif( $ClasseAntes != $ClasseDescricao ){
								$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
								$pdf->Cell(250,5,$GrupoDescricao." / ".$ClasseDescricao,1,1,"L",0);
								$pdf->Cell(30,5,"SUBCLASSE",1,0,"L",1);
								$pdf->Cell(250,5,$SubClasseDescricao,1,1,"L",0);
								$pdf->Cell(249,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
								$pdf->Cell(16,5,"CÓD.RED",1, 0,"C",1);
								$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);								
						}elseif( $SubClasseAntes != $SubClasseDescricao ){
								$pdf->Cell(30,5,"SUBCLASSE",1,0,"L",1);
								$pdf->Cell(250,5,$SubClasseDescricao,1,1,"L",0);
								$pdf->Cell(249,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
								$pdf->Cell(16,5,"CÓD.RED",1, 0,"C",1);
								$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);								
						}
						$SubClasseAntes    = $SubClasseDescricao;
						$GrupoAntes        = $GrupoDescricao;
						$ClasseAntes       = $ClasseDescricao;
						$TipoMaterialAntes = $TipoMaterialCodigo;

						# Quebra de Linha para Descrição do Material #
						$DescMaterialSepara = SeparaFrase($MaterialDescricao,130);
						$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
						if( $TamDescMaterial <= 249 ){
								$LinhasMat = 1;
								$AlturaMat = 5;
						}elseif( $TamDescMaterial > 249 and $TamDescMaterial <= 495 ){
								$LinhasMat = 2;
								$AlturaMat = 10;
						}else{
								$LinhasMat = 3;
								$AlturaMat = 15;
						}
						if( $TamDescMaterial > 249 ){
								$Inicio = 0;
								$pdf->Cell(249,$AlturaMat,"",1,0,"L",0);
								for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
										if( $Quebra == 0 ){
									  		$pdf->SetX(10);
											  $pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Cell(16,$AlturaMat,$MaterialSequencia,1,0,"C",0);											  
												$pdf->Cell(15,$AlturaMat,$UndMedidaSigla,1,0,"C",0);
												$pdf->Ln(5);
										}elseif( $Quebra == 1 ){
												$pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
									  		$pdf->Ln(5);
									  }else{
												$pdf->Cell(249,5,trim(substr($DescMaterialSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Ln(5);
									  }
										$Inicio = $Inicio + 130;
								}
						}else{
								$pdf->Cell(249,5,$MaterialDescricao,1,0,"L",0);
								$pdf->Cell(16,5,$MaterialSequencia,1,0,"C",0);								
								$pdf->Cell(15,5,$UndMedidaSigla,1,1,"C",0);
						}
				}
				$pdf->Cell(265,5,"TOTAL DE ITENS",1,0,"R",1);
				$pdf->Cell(15,5,$rows,1,"R",0);
		}
}
$db->disconnect();
$pdf->Output();
?> 
