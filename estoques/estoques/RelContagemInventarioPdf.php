<?php
#------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelContagemInventarioPdf.php
# Autor:    Filipe Cavalcanti
# Data:     09/08/2005
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
#------------------------------------------------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     15/07/2014 - Ajuste no cabeçalho do formulário para exibir os dados dinâmicos.
#------------------------------------------------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#           Ao passar para produção trocar o caminho da imagem do cabeçalho na função
#           CabecalhoRodapeInventario() neste arquivo
#------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelContagemInventario.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Localizacao  = $_GET['Localizacao'];
		$Ordem 				= $_GET['Ordem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapeInventario();

# Informa o Título do Relatório #
if ($Ordem == "1"){
		$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Família)";
} else {
		$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Material)";
}

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Muda o tamanho do Rodapé #
$pdf->SetAutoPageBreak(true,32);

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$db = Conexao();
if( $Ordem == 1 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Família #
		$sql  = "SELECT B.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, ";
		$sql .= "       F.EGRUMSDESC, E.ECLAMSDESC ";
		$sql .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL A ";
		$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL B ON (A.CMATEPSEQU = B.CMATEPSEQU) ";
		$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON (B.CUNIDMCODI = C.CUNIDMCODI) ";
		$sql .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL D ON (B.CSUBCLSEQU = D.CSUBCLSEQU) ";
		$sql .= " INNER JOIN SFPC.TBCLASSEMATERIALSERVICO E ON (D.CGRUMSCODI = E.CGRUMSCODI AND D.CCLAMSCODI = E.CCLAMSCODI ) ";
		$sql .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO F ON (E.CGRUMSCODI = F.CGRUMSCODI) ";
		$sql .= "  LEFT JOIN SFPC.TBINVENTARIOMATERIAL G ON (G.CMATEPSEQU = A.CMATEPSEQU AND G.CLOCMACODI = A.CLOCMACODI ) ";
		$sql .= " WHERE A.CLOCMACODI = $Localizacao ";
		$sql .= " ORDER BY F.EGRUMSDESC, E.ECLAMSDESC, B.EMATEPDESC ";
}elseif( $Ordem == 2 ){
		# Pega os dados dos Materiais Cadastrados - Ordem Material #
		$sql  = "SELECT B.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL ";
		$sql .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL A ";
		$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL B ON (A.CMATEPSEQU = B.CMATEPSEQU) ";
		$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON (B.CUNIDMCODI = C.CUNIDMCODI) ";
		$sql .= "  LEFT JOIN SFPC.TBINVENTARIOMATERIAL D ON (D.CMATEPSEQU = A.CMATEPSEQU AND A.CLOCMACODI = D.CLOCMACODI ) ";
		$sql .= " WHERE A.CLOCMACODI = $Localizacao ";
		$sql .= " ORDER BY B.EMATEPDESC ";
}
$res = $db->query($sql);
if( PEAR::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
		# Pega as informações do Almoxarifado #
		$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
		$resalmo = $db->query($sqlalmo);
		if( PEAR::isError($resalmo) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
		}else{
				$Almox     = $resalmo->fetchRow();
				$DescAlmox = $Almox[0];

				$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
				$pdf->Cell(129,5,"$DescAlmox",1,0,"L",0);
				$pdf->Cell(31,5,"DATA INVENTÁRIO", 1, 0, "L", 1);
				$pdf->Cell(36,5,"          /          /", 1, 1, "L", 0);
				$pdf->ln(5);
		}

		# Linhas de Itens de Material #
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelContagemInventario.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha              = $res->fetchRow();
						$CodigoReduzido[$i] = $Linha[0];
						$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
						$Unidade[$i]   		  = $Linha[2];

						# Montando o array de Itens do Inventário #
						if( $Ordem == 1 ){
								$DescGrupo[$i]  = RetiraAcentos($Linha[3]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[3]);
								$DescClasse[$i] = RetiraAcentos($Linha[4]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[4]);
								$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i];
						}else{
								$Itens[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i];
						}
				}
		}
}
$db->disconnect();

# Escrevendo o Relatório #
$DescGrupoAntes     = "";
$DescCalsseAntes    = "";
sort($Itens);
for( $i=0; $i< count($Itens); $i++ ){
		# Extrai os dados do Array de Itens #
		$Dados = explode($SimboloConcatenacaoArray,$Itens[$i]);
		if( $Ordem == 1 ){
				$DescGrupo      = $Dados[0];
				$DescClasse     = $Dados[1];
				$DescMaterial   = $Dados[2];
				$CodigoReduzido = $Dados[3];
				$Unidade   		  = $Dados[4];

				# Pega a descrição do Grupo com acento #
				$DescricaoG = explode($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = explode($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];

				if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
						$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
						$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
						$pdf->Cell(159,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(27,5,"CÓD REDUZIDO",1,0,"C",1);
						$pdf->Cell(27,5,"CONSOLIDADO",1,0,"C",1);
						$pdf->Cell(27,5,"RECONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"CONTAGEM",1,1,"C",1);
				}
				$DescGrupoAntes     = $DescGrupo;
				$DescClasseAntes    = $DescClasse;
		}else{
				$DescMaterial   = $Dados[0];
				$CodigoReduzido = $Dados[1];
				$Unidade   		  = $Dados[2];
				if( $i == 0 ){
						$pdf->Cell(159,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(27,5,"CÓD REDUZIDO",1,0,"C",1);
						$pdf->Cell(27,5,"CONSOLIDADO",1,0,"C",1);
						$pdf->Cell(27,5,"RECONTAGEM",1,0,"C",1);
						$pdf->Cell(27,5,"CONTAGEM",1,1,"C",1);
				}
		}

		# Pega a descrição do Material com acento #
		$Descricao    = explode($SimboloConcatenacaoDesc,$DescMaterial);
		$DescMaterial = $Descricao[1];

		# Quebra de Linha para Descrição do Material #
		$DescMaterialSepara = SeparaFrase($DescMaterial,79);
		$TamDescMaterial    = $pdf->GetStringWidth($DescMaterialSepara);
		if( $TamDescMaterial <= 148 ){
				$LinhasMat = 1;
				$AlturaMat = 5;
		}elseif( $TamDescMaterial > 148 and $TamDescMaterial <= 296 ){
				$LinhasMat = 2;
				$AlturaMat = 10;
		}elseif( $TamDescMaterial > 296 and $TamDescMaterial <= 444 ){
				$LinhasMat = 3;
				$AlturaMat = 15;
		}else{
				$LinhasMat = 4;
				$AlturaMat = 20;
		}
		if( $TamDescMaterial > 147 ){
				$Inicio = 0;
				$pdf->Cell(159,$AlturaMat,"",1,0,"L",0);
				for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
						if( $Quebra == 0 ){
					  		$pdf->SetX(10);
					  		$pdf->Cell(159,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Cell(13,$AlturaMat,$Unidade,1,0,"C",0);
								$pdf->Cell(27,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
								$pdf->Cell(27,$AlturaMat,"",1,0,"L",0);
								$pdf->Cell(27,$AlturaMat,"",1,0,"L",0);
								$pdf->Cell(27,$AlturaMat,"",1,0,"L",0);
								$pdf->Ln(5);
						}elseif( $Quebra == 1 ){
								$pdf->Cell(159,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
					  		$pdf->Ln(5);
					  }elseif( $Quebra == 2 ){
								$pdf->Cell(159,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }elseif( $Quebra == 3 ){
								$pdf->Cell(159,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					 }else{
						  	$pdf->Cell(159,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
								$pdf->Ln(5);
					  }
						$Inicio = $Inicio + 79;
			  }
	  		$pdf->Cell(159,0,"",1,1,"",0);
		}else{
				$pdf->Cell(159,5,$DescMaterial, 1,0, "L",0);
				$pdf->Cell(13,5,$Unidade,1,0,"C",0);
				$pdf->Cell(27,5,$CodigoReduzido,1, 0,"C",0);
				$pdf->Cell(27,5,"",1,0,"L",0);
				$pdf->Cell(27,5,"",1,0,"L",0);
				$pdf->Cell(27,5,"",1,1,"L",0);
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(199,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(27,5,$rows, 1,1, "R",0);
$pdf->Output();

function CabecalhoRodapeInventario(){
	# Classes FPDF #
	class PDF extends FPDF {
		# Cabeçalho #
		function Header() {
			##### Verificar endereço quando passar para produção #####
			/*
			Global $CaminhoImagens;
			//$this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
			$this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
			$this->SetFont("Arial","B",10);
			$this->Cell(0,20,"Prefeitura do Recife",0,0,"L");
			$this->Cell(0,20,"EMPREL",0,0,"R");
			$this->Ln(1);
			$Empresa = $_SESSION['_egruatdesc_'];
			$this->Cell(0,25,"Secretaria de Financas",0,0,"L");
			$this->Cell(0,25,"Diretoria Geral de Compras",0,0,"R");
			$this->Ln(1);
			$this->Cell(0,30,"Portal de Compras",0,0,"L");
			$this->Cell(0,30,"",0,0,"R");
			$this->Ln(1);
			$this->Line(10,30,290,30);
			$this->Cell(0,39,$GLOBALS['TituloRelatorio'],0,0,"C");
			$this->Ln(1);
			$this->Line(10,36,290,36);
			$this->Ln(25);
			*/
			
		    Global $CaminhoImagens;
		    $cabecalho = retornaCabecalho();
		    //$this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
		    $this->Image("$CaminhoImagens/brasaopeq.jpg",135,5,0);
		    $this->SetFont("Arial","B",10);
		    $this->Cell(0,20,"$cabecalho[empresa]",0,0,"L");
		    $this->Cell(0,20,"$cabecalho[orgao1]",0,0,"R");
		    $this->Ln(1);
		    //$Empresa = $_SESSION['_egruatdesc_'];
		    $this->Cell(0,25,"$cabecalho[orgao2]",0,0,"L");
		    $this->Cell(0,25,"$cabecalho[setor1]",0,0,"R");
		    $this->Ln(1);
		    $this->Cell(0,30,"$cabecalho[nomesistema]",0,0,"L");
		    $this->Cell(0,30,"",0,0,"R");
		    $this->Ln(1);
		    $this->Line(10,30,290,30);
		    $this->Cell(0,39,$GLOBALS['TituloRelatorio'],0,0,"C");
		    $this->Ln(1);
		    $this->Line(10,36,290,36);
		    $this->Ln(25);
		}

		# Rodapé #
		function Footer() {
			$this->SetFillColor(200);
			$this->SetFont("Arial","",10);
			$this->Line(10,178,290,178);

			$this->SetY(-30);
			$this->Cell(199,5,"Emissão: ".date("d/m/Y H:i:s"),0,0,"L",0);
			$this->Cell(27,5,"Matrícula",1,0,"C",1);
			$this->Cell(27,5,"Matrícula",1,0,"C",1);
			$this->Cell(27,5,"Matrícula",1,1,"C",1);

			$this->Cell(199,5,"",0,0,"C",0);
			$this->Cell(27,5,"",1,0,"C",0);
			$this->Cell(27,5,"",1,0,"C",0);
			$this->Cell(27,5,"",1,1,"C",0);

			$this->Cell(199,5,"",0,0,"C",0);
			$this->Cell(27,5,"Assinatura",1,0,"C",1);
			$this->Cell(27,5,"Assinatura",1,0,"C",1);
			$this->Cell(27,5,"Assinatura",1,1,"C",1);

			$this->Cell(199,5,"",0,0,"C",0);
			$this->Cell(27,5,"",1,0,"C",0);
			$this->Cell(27,5,"",1,0,"C",0);
			$this->Cell(27,5,"",1,1,"C",0);

		  $this->SetY(-10);
		  $this->Cell(199,5," ",0,0,"L");
			$this->Cell(27,5,"Página: ".$this->PageNo()."/{nb}",0,0,"R",0);
			$this->Cell(27,5,"Página: ".$this->PageNo()."/{nb}",0,0,"R",0);
			$this->Cell(27,5,"Página: ".$this->PageNo()."/{nb}",0,1,"R",0);
		}
	}
}
?> 
