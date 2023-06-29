<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsAcompSolicitacaoCompraPdf.php
# Autor:    Dolores Santa Cruz
# Data:     23/01/07
# Objetivo: Programa de Consulta de Acompanhamento das Solicitações de Compra
# Alterado: Carlos Abreu
# Data:     19/06/2007 - Finalizado relatório
# Alterado: Rossana Lira
# Data:     02/07/2007 - Retirada dos campo de valor compra/ cnpf e logrado
#                      - Inclusão de Totalizador por item e no final por solicitação
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Acrescentado o destaque '*' quando o preco do material não esta registrado na tabela de registro de preco
# Alterado: Rodrigo Melo
# Data:     30/10/2007 - Alteração no relatório para impressão correta do campo "VALOR TOTAL DA SOLICITAÇÃO".
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Sequencial     = $_GET['Sequencial'];
	$AnoSolicitacao = $_GET['AnoSolicitacao'];
	$CentroCusto    = $_GET['CentroCusto'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Acompanhamento de Solicitação de Compra";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta a Margem esquerda da página #
$pdf->SetLeftMargin(5);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

unset($_SESSION['item']);
unset($_SESSION['Arquivos_Upload']);

$db = Conexao();
#Pega documentação da Solicitação de Compra
$sql  = "SELECT CDOCSOCODI, EDOCSONOME, TDOCSOULAT  ";
$sql .= "  FROM SFPC.TBDOCUMENTOSOLICITACAOCOMPRA  ";
$sql .= " WHERE CSOLCOSEQU = $Sequencial ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}else{
	while( $linha = $res->fetchRow() ){
		$_SESSION['Arquivos_Upload'][2][]=$linha[0];
		$_SESSION['Arquivos_Upload'][1][]=$linha[1];
	}
}
# Pega os dados da Solicitação de Material de acordo com o Sequencial #
$sql  = "SELECT SOL.CSOLCOSEQU, SOL.CORGLICODI, SOL.ASOLCOANOS, SOL.CSOLCOCODI, SOL.DSOLCODATA, ";
$sql .= "       SOL.CCENPOSEQU, SOL.ESOLCOOBSE, SOL.FSOLCOTIPM, SOL.FSOLCOTIPC, SOL.FSOLCOSITU, ";
$sql .= "       SOL.TSOLCOSITU, SOL.CGREMPCODI, SOL.CUSUPOCODI, SOL.CGREMPCOD1, SOL.CUSUPOCOD1, ";
$sql .= "       SOL.TSOLCOULAT, USU.EUSUPORESP ";
$sql .= "  FROM SFPC.TBSOLICITACAOCOMPRA SOL, SFPC.TBUSUARIOPORTAL USU ";
$sql .= " WHERE SOL.CSOLCOSEQU = $Sequencial AND SOL.CUSUPOCODI = USU.CUSUPOCODI ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}else{
	$Linha	=	$res->fetchRow();
	$Sequencial      = $Linha[0];
	$Orgao           = $Linha[1];
	$AnoSolicitacao  = $Linha[2];
	$Solicitacao     = $Linha[3];
	$DataSolicitacao = DataBarra($Linha[4]);
	$Observacao      = $Linha[6];
	$TipoMaterial    = $Linha[7];
	if($TipoMaterial=="C"){
		$DescTipoMaterial = "CONSUMO";
	}elseif($TipoMaterial=="P"){
		$DescTipoMaterial = "PERMANENTE";
	}
	$TipoCompra      = $Linha[8];
	if($TipoCompra=="D"){
		$DescTipoCompra	= "DIRETA";
	}elseif($TipoCompra=="L"){
		$DescTipoCompra	= "LICITAÇÃO";
	}else{
		$DescTipoCompra	= "NÃO DEFINIDO";
	}
	if($Linha[9] == "C"){
		$Situacao = "CADASTRADA";
	}elseif($Linha[9] == "X"){
		$Situacao = "CANCELADA";
	}elseif($Linha[9] == "F"){
		$Situacao="FINALIZADA";
	}
	$DataSituacao       = DataBarra($Linha[10]);
	$UsuarioSolicitante = $Linha[16];
	$CodComissao        = $Linha[19];
}
if($CodComissao!=""){
	$sql  = "SELECT DISTINCT A.CCOMLICODI, A.ECOMLIDESC ";
	$sql .= "  FROM SFPC.TBCOMISSAOLICITACAO A, SFPC.TBUSUARIOCOMIS B ";
	$sql .= " WHERE A.CCOMLICODI = $CodComissao AND B.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
	$sql .= "   AND B.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = B.CGREMPCODI ";
	$sql .= "   AND FCOMLISTAT = 'A' ";
	$sql .= " ORDER BY A.ECOMLIDESC";
	$result = $db->query($sql);
	if( PEAR::isError($res) ){
		$CodErroEmail  = $res->getCode();
		$DescErroEmail = $res->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
	}else{
		$Comiss = $result->fetchRow();
		$Comissao = $Comiss[1];
	}
}
# Pega os dados dos Itens da Solicitação de Material de acordo com o Sequencial #
$sql  = " SELECT DISTINCT ITE.AITESCORDE, ITE.AITESCQTSO, ITE.VITESCESTI, MAT.CMATEPSEQU, MAT.EMATEPDESC,  ";
$sql .= "       UNI.EUNIDMSIGL, FORN.AFORCRCCGC,FORN.AFORCRCCPF, PREF.APREFOCCGC,PREF.APREFOCCPF,  ";
$sql .= "(SELECT CASE WHEN COUNT(*)>0 THEN 'S' ELSE 'N' END FROM SFPC.TBPRECOMATERIAL PRECO WHERE MAT.CMATEPSEQU = PRECO.CMATEPSEQU ) ";
$sql .= " FROM SFPC.TBSOLICITACAOCOMPRA SOL,  SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UNI,  ";
$sql .= "       SFPC.TBITEMSOLICITACAOCOMPRA ITE  ";
$sql .= " LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON (ITE.AFORCRSEQU=FORN.AFORCRSEQU )  ";
$sql .= " LEFT OUTER JOIN SFPC.TBPREFORNECEDOR PREF ON (ITE.APREFOSEQU=PREF.APREFOSEQU )   ";
$sql .= " WHERE SOL.CSOLCOSEQU = $Sequencial AND SOL.CSOLCOSEQU = ITE.CSOLCOSEQU AND   ";
$sql .= "       ITE.CMATEPSEQU = MAT.CMATEPSEQU AND ITE.CMATEPSEQU = MAT.CMATEPSEQU   ";
$sql .= "       AND MAT.CUNIDMCODI = UNI.CUNIDMCODI  ";
$sql .= " ORDER BY ITE.AITESCORDE  ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}else{
	$Rows = $res->numRows();
	for( $i=0;$i<$Rows;$i++ ){
		$Linha = $res->fetchRow();
		$Ordem[$i]          = $i+1;
		$QtdSolicitada[$i]  = str_replace(".",",",$Linha[1]);
		$QtdSolicitada[$i]  = converte_valor($QtdSolicitada[$i]);
		$ValorEstimado[$i]  = str_replace(".",",",$Linha[2]);
		$ValorEstimado[$i]  = converte_valor_estoques($ValorEstimado[$i]);
		$Material[$i]       = $Linha[3];
		$DescMaterial[$i]   = $Linha[4];
		$Unidade[$i]        = $Linha[5];
		$ExistePreco[$i]    = $Linha[10];
	}
}

$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

$res  = $db->query($sql);
if( PEAR::isError($res) ){
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
}else{
	$Linha            = $res->fetchRow();
	$DescCentroCusto = $Linha[0];
	$DescOrgao       = $Linha[1];
	$Orgao           = $Linha[2];
	$RPA             = $Linha[3];
	$Detalhamento    = $Linha[4];
}

$pdf->SetX(5); # Necessário para deixar o título "Centro de Custo", alinhando com os outros títulos abaixo.
$pdf->Cell(35,5,"Centro de Custo",1,0,"L",1);
$pdf->Cell(251,5,"$DescOrgao - RPA $RPA - $DescCentroCusto - $Detalhamento",1,1,"L",0);
$pdf->Cell(35,5,"Solicitação",1,0,"L",1);
$pdf->Cell(251,5,substr($Solicitacao+100000,1)."/".$AnoSolicitacao,1,1,"L",0);
$pdf->Cell(35,5,"Usuário da Solicitação",1,0,"L",1);
$pdf->Cell(251,5,"$UsuarioSolicitante",1,1,"L",0);
$pdf->Cell(35,5,"Data da Solicitação",1,0,"L",1);
$pdf->Cell(251,5,"$DataSolicitacao",1,1,"L",0);
$pdf->Cell(35,5,"Situação",1,0,"L",1);
$pdf->Cell(251,5,"$Situacao",1,1,"L",0);
$TamObservacao = $pdf->GetStringWidth($Observacao);
if( $TamObservacao > 244 ){ $Altura = 10; }else{ $Altura = 5; }
$pdf->Cell(35,$Altura,"Observação",1,0,"L",1);
if( $Observacao == "" ){ $Observacao = "NÃO INFORMADA"; }
$pdf->MultiCell(251,5,$Observacao,1,"L",0);
$pdf->Cell(35,5,"Tipo de Material",1,0,"L",1);
$pdf->Cell(251,5,"$DescTipoMaterial",1,1,"L",0);
$pdf->Cell(35,5,"Tipo de Compra",1,0,"L",1);
$pdf->Cell(251,5,"$DescTipoCompra",1,1,"L",0);
if($CodComissao!=""){
	$pdf->Cell(35,5,"Comissão de Licitação",1,0,"L",1);
	$pdf->Cell(245,5,"$Comissao",1,1,"L",0);
}

$TotDoc	= count($_SESSION['Arquivos_Upload'][2]);
if($TotDoc>0){$pdf->ln(5);}
for($x=0;$x < $TotDoc;$x++){
	if($x==0){
		$pdf->Cell(280,5,"DOCUMENTOS ANEXOS",1,1,'C',1);
		$pdf->Cell(280,5,"NOME DO ARQUIVO",1,1,"L",1);
	}
	$pdf->Cell(280,5,$_SESSION['Arquivos_Upload'][1][$x],1,1,"L",0);
}
$pdf->ln(5);
$pdf->Cell(286,5,"ITENS DA SOLICITAÇÃO",1,1,'C',1);
for($i=0;$i < $Rows;$i++){
        if( $i == 0 ){
		$pdf->Cell(10,10,"ORD",1,0,"C",1);
		$pdf->Cell(18,10,"COD.RED",1,0,"C",1);
		$pdf->Cell(184,10,"DESCRIÇÃO DO MATERIAL",1,0,"C",1);
		$pdf->Cell(8,10,"UNID",1,0,"C",1);
		$pdf->Cell(20,5,"QUANT.","TLR",0,"C",1);
		$pdf->Cell(22,5,"VALOR","TLR",0,"C",1);
		$pdf->Cell(24,5,"VALOR","TLR",1,"C",1);
		$Y	= $pdf->GetY();
		$X	= $pdf->GetX();
		$pdf->Cell(220,5,"",0,0,"C",0);
		$pdf->Cell(20,5,"SOLICITADA","LRB",0,"C",1);
		$pdf->Cell(22,5,"ESTIMADO","LRB",0,"C",1);
		$pdf->Cell(24,5,"TOTAL","LRB",0,"C",1);
		$pdf->ln(5);
	}
	
	# Quebra de Linha para Descrição do Material #
	$TamDescMaterial = $pdf->GetStringWidth($DescMaterial[$i]);

	//504
	//554
	//537
	//384
	//		$pdf->Cell(1,10,"*".$TamDescMaterial."*",1,0,"L",0);
	if( $TamDescMaterial <= 160 ){
		$LinhasMat = 1;
		$AlturaMat = 5;
	}elseif( $TamDescMaterial > 160 and $TamDescMaterial <= 320 ){
		$LinhasMat = 2;
		$AlturaMat = 10;
	}elseif( $TamDescMaterial > 320 and $TamDescMaterial <= 480 ){
		$LinhasMat = 3;
		$AlturaMat = 15;
	}else{
		$LinhasMat = 4;
		$AlturaMat = 20;

	}

	if( $TamDescMaterial > 130 ){
		//		if( $TamDescMaterial > 115 ){
		$Inicio = 0;
		$pdf->Cell(10,$AlturaMat,"",1,0,"L",0);
		$pdf->Cell(18,$AlturaMat,"",1,0,"R",0);
		$pdf->Cell(184,$AlturaMat,"",1,0,"L",0);
		$pdf->SetX(5); #DEBUG
		for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
			if( $Quebra == 0 ){
				if ($ExistePreco[$i] == 'N'){
					$pdf->Cell(10,$AlturaMat,$Ordem[$i]." *",0,0,"C",0);
				} else {
					$pdf->Cell(10,$AlturaMat,$Ordem[$i],0,0,"C",0);
				}
                                $pdf->Cell(18,$AlturaMat,$Material[$i],0,0,"C",0);

                                $NovoY = $pdf->GetY();
                                $pdf->MultiCell(184,5,trim($DescMaterial[$i]),0,"L",0);
                                
                                /*
                                    O X = 217, pois é a soma do X da página, 
                                    anterior ao ser colocado a descrição do material, 
                                    assim, faz-se: 184 + 33 = 217
                                    
                                    É necessário para setar o X e o Y dá página, para ficar ao lado da "Descrição do material",
                                    pois, o pdf estava sendo gerando com uma quebra de página, após a "descrição do material" 
                                    ser impressa, deixando os demais campos desalinhados e em locais impróprios.
                                */
                                $pdf->SetXY(217,$NovoY);

				$pdf->Cell(8,$AlturaMat,$Unidade[$i],1,0,"C",0); #DEBUG

				if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }

				$pdf->Cell(20,$AlturaMat,$QtdSolicitada[$i],1,0,"R",0);

				if( $ValorEstimado[$i] == "" ){ $ValorEstimado[$i] = "0"; }

                                $pdf->Cell(22,$AlturaMat,$ValorEstimado[$i],1,0,"R",0); #DEBUG

				$decQuantidade    = str_replace(",",".",str_replace(".","",$QtdSolicitada[$i]));
				$decValorUnitario = str_replace(",",".",str_replace(".","",$ValorEstimado[$i]));
				$ValorTotal[$i]   = str_replace(",",".",($decQuantidade * $decValorUnitario));
				$ValorNota        = $ValorNota + $ValorTotal[$i];
				$ValorTotal[$i]   = converte_valor_estoques(sprintf("%01.4f",($ValorTotal[$i])));
                                $pdf->Cell(24,$AlturaMat,$ValorTotal[$i],1,0,"R",0);

				$pdf->Ln(5);
			}elseif( $Quebra == 1 ){
				$pdf->Cell(10,5,"",$Borda,0,"R",0);
				$pdf->Cell(18,5,"",$Borda,0,"R",0);
				$pdf->Ln(5);
			}elseif( $Quebra == 2 ){
				$pdf->Cell(10,5,"",$Borda,0,"R",0);
				$pdf->Cell(18,5,"",$Borda,0,"R",0);
                                $pdf->Ln(5);
			}elseif( $Quebra == 3 ){
				$pdf->Cell(10,5,"",$Borda,0,"R",0);
				$pdf->Cell(18,5,"",$Borda,0,"R",0);
                                $pdf->Ln(5);
			}else{
				$pdf->Cell(10,5,"",$Borda,0,"R",0);
				$pdf->Cell(18,5,"",$Borda,0,"R",0);
                                $pdf->Ln(5);
			}
			$Inicio = $Inicio + 95;
		}
		$pdf->Cell(194,0,"",1,1,"",0);
	}else{
		if ($ExistePreco[$i] == 'N'){
			$pdf->Cell(10,5,$Ordem[$i]." *",1,0,"C",0);
		} else {
			$pdf->Cell(10,5,$Ordem[$i],1,0,"C",0);
		}
		$pdf->Cell(18,5,$Material[$i],1,0,"C",0);
		#$pdf->Cell(184,5,trim($DescMaterial[$i]),1,0,"L",0); #TESTE - ORIGINAL
		#$pdf->MultiCell(180,5,trim($DescMaterial[$i]),1,"L",0); #DEBUG
		$NovoY = $pdf->GetY();
                $pdf->MultiCell(184,5,trim($DescMaterial[$i]),1,"L",0);

                /*
                   O X = 217, pois é a soma do X da página,
                   anterior ao ser colocado a descrição do material,
                   assim, faz-se: 184 + 33 = 217

                   É necessário para setar o X e o Y dá página, para ficar ao lado da "Descrição do material",
                   pois, o pdf estava sendo gerando com uma quebra de página, após a "descrição do material"
                   ser impressa, deixando os demais campos desalinhados e em locais impróprios.
                */

                $pdf->SetXY(217,$NovoY);

		$pdf->Cell(8,5,$Unidade[$i],1,0,"C",0);
		if( $QtdSolicitada[$i] == "" ){ $QtdSolicitada[$i] = "0"; }
		$pdf->Cell(20,5,$QtdSolicitada[$i],1,0,"R",0);
		if( $ValorEstimado[$i] == "" ){ $ValorEstimado[$i] = "0"; }
		$pdf->Cell(22,$AlturaMat,$ValorEstimado[$i],1,0,"R",0);
		$decQuantidade    = str_replace(",",".",str_replace(".","",$QtdSolicitada[$i]));
		$decValorUnitario = str_replace(",",".",str_replace(".","",$ValorEstimado[$i]));
		$ValorTotal[$i]   = str_replace(",",".",($decQuantidade * $decValorUnitario));
		$ValorNota        = $ValorNota + $ValorTotal[$i];
		$ValorTotal[$i]   = converte_valor_estoques(sprintf("%01.4f",($ValorTotal[$i])));
		$pdf->Cell(24,$AlturaMat,$ValorTotal[$i],1,0,"R",0);
		$pdf->Ln(5);
	}
}
$pdf->Cell(262,5,"VALOR TOTAL DA SOLICITAÇÃO",1,0,"R",1);
$pdf->Cell(24,5,converte_valor_estoques($ValorNota),1,0,"R",0);

$db->disconnect();
$pdf->Output();
?>
