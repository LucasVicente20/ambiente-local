<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelServicoPdf.php
# Autor:    Ariston Cordeiro
# Data:     05/06/2012
# Objetivo: Programa de Impressão dos Relatórios de Serviço em PDF
# Acesso ao arquivo de funções #
#------------------------------------------------------------------------------------
include "../funcoes.php";
# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/RelServico.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Grupo     = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Tipo   = $_GET['Tipo'];
	}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Serviço - Ordenado por Família de Serviço";

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
	SELECT DISTINCT(GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, 
	SERV.CSERVPSEQU, SERV.ESERVPDESC, GRU.FGRUMSTIPM
    FROM 
	SFPC.TBSERVICOPORTAL SERV, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA 
    WHERE 
	SERV.CCLAMSCODI = CLA.CCLAMSCODI 
	AND SERV.CGRUMSCODI = GRU.CGRUMSCODI  
	AND SERV.CSERVPSITU = 'A'
    AND CLA.CGRUMSCODI = GRU.CGRUMSCODI 
    AND GRU.FGRUMSSITU = 'A' 
	AND CLA.FCLAMSSITU = 'A'  
	AND GRU.FGRUMSTIPO = 'S'
";
# Verifica se o Grupo foi escolhido #
if( $Grupo != "" ){
  	$sql .= " AND GRU.CGRUMSCODI = $Grupo ";
}
if( $Classe != "" ){
  $sql .= " AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND CLA.CCLAMSCODI = $Classe ";
}	

if($Tipo=='Familia'){
	// Ordem por família
	$sql .= " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC , SERV.ESERVPDESC ";
}else if($Tipo=='Item'){
	// Ordem por item
	$sql  .= " ORDER BY SERV.ESERVPDESC";
	$pdf->Cell(264,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
	$pdf->Cell(16,5,"CÓD.RED",1,1,"C",1);
}else{
	
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}
$res  = $db->query($sql);
if( PEAR::isError($res) ){
	EmailErroDB("Falha de SQL", "Falha ao executar o SQL", $res);		
}else{
		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelServico.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
    		$DescGrupoAntes     = "";
            $DescCalsseAntes    = "";
        
				for( $i=0; $i< $rows; $i++ ){
						$Linha = $res->fetchRow();
						$GrupoCodigo        = $Linha[0];
						$GrupoDescricao     = $Linha[1];
						$ClasseCodigo       = $Linha[2];
						$ClasseDescricao    = $Linha[3];
						$ServicoSequencia   = $Linha[4];
						$ServicoDescricao   = $Linha[5];
						
						/*if( $TipoServicoAntes != $TipoServicoCodigo ) {
								if($TipoServicoCodigo == "C"){
									 	$pdf->Cell(280,5,"CONSUMO",1,1,"C",1);
								}else{
	  								$pdf->Cell(280,5, " ", 0, 1, "R", 0);
									 	$pdf->Cell(280,5,"PERMANENTE",1,1,"C",1);
								}
						}*/
						/*$pdf->Cell(280,5,"SERVIÇO",1,1,"C",1);*/
						if( $Tipo=='Familia'  ){
							if( $GrupoAntes != $GrupoDescricao ){
									$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
									$pdf->Cell(250,5,$GrupoDescricao." / ".$ClasseDescricao,1,1,"L",0);
									$pdf->Cell(30,5,"CLASSE",1,0,"L",1);
									$pdf->Cell(250,5,$ClasseDescricao,1,1,"L",0);
									$pdf->Cell(264,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
									$pdf->Cell(16,5,"CÓD.RED",1,1,"C",1);
									/*$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);*/
							}elseif( $ClasseAntes != $ClasseDescricao ){
									$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
									$pdf->Cell(250,5,$GrupoDescricao." / ".$ClasseDescricao,1,1,"L",0);
									$pdf->Cell(30,5,"CLASSE",1,0,"L",1);
									$pdf->Cell(250,5,$ClasseDescricao,1,1,"L",0);
									$pdf->Cell(264,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
									$pdf->Cell(16,5,"CÓD.RED",1,1,"C",1);
								    /*$pdf->Cell(15,5,"UNIDADE",1, 1,"C",1);*/
							}
						}	
						
						
						$GrupoAntes        = $GrupoDescricao;
						$ClasseAntes       = $ClasseDescricao;
						$ServicoAntes      = $ServicoSequencia;

						# Quebra de Linha para Descrição do Serviço #
						$DescServicoSepara = SeparaFrase($ServicoDescricao,130);
						$TamDescServico    = $pdf->GetStringWidth($DescServicoSepara);
						if( $TamDescServico <= 264 ){
								$LinhasServ = 1;
								$AlturaServ = 5;
						}elseif( $TamDescServico > 264 and $TamDescServico <= 495 ){
								$LinhasServ = 2;
								$AlturaServ = 10;
						}else{
								$LinhasServ = 3;
								$AlturaServ = 15;
						}
						if( $TamDescServico > 264 ){
								$Inicio = 0;
								$pdf->Cell(264,$AlturaServ,"",1,0,"L",0);
								for( $Quebra = 0; $Quebra < $LinhasServ; $Quebra++ ){
										if( $Quebra == 0 ){
									  		$pdf->SetX(10);
											  $pdf->Cell(264,5,trim(substr( $DescServicoSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Cell(16,$AlturaServ,$ServicoSequencia,1,0,"C",0);											  
												/*$pdf->Cell(15,$AlturaServ,$UndMedidaSigla,1,0,"C",0);*/
												$pdf->Ln(5);
										}elseif( $Quebra == 1 ){
												$pdf->Cell(264,5,trim(substr($DescServicoSepara, $Inicio,130)),0,0,"L",0);
									  		$pdf->Ln(5);
									  }else{
												$pdf->Cell(264,5,trim(substr($DescServicoSepara,$Inicio,130)),0,0,"L",0);
												$pdf->Ln(5);
									  }
										$Inicio = $Inicio + 130;
								}
						}else{
								$pdf->Cell(264,5,$ServicoDescricao,1,0,"L",0);
								$pdf->Cell(16,5,$ServicoSequencia,1,1,"C",0);								
								/*$pdf->Cell(15,5,$UndMedidaSigla,1,1,"C",0);*/
						}
				}
				$pdf->Cell(265,5,"TOTAL DE ITENS",1,0,"R",1);
				$pdf->Cell(15,5,$rows,1,"R",0);
		}
}
$db->disconnect();
$pdf->Output();
?> 
