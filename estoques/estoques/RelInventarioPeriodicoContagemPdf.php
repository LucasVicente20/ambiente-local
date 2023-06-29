<?php
# ------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelInventarioPeriodicoContagemPdf.php
# Autor:    Carlos Abreu
# Data:     21/11/2006
# Objetivo: Programa de Impressão da contagem de inventário de acordo com o Almoxarifado
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------------------------------------------
# Autor:    Ariston Cordeiro
# Data:     21/11/2008	-	Alteração para não mostrar itens que estão zero no almoxarifado e não tiveram movimentação desde o último periódico
# ------------------------------------------------------------------------------------------------------------------
# Autor:    Ariston Cordeiro
# Data:     28/11/2008	- Correção de bug de data do último periódico quando não existe último periódico
#												-	Adicionando mensagens de erro para debug (verificar valores de variáveis antes de usá-las)
# ------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     15/12/2008	- Não ignorar itens zerados sem movimentação que foram incluídos após a abertura do inventário
# ------------------------------------------------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     15/07/2014 - Ajuste no cabeçalho do formulário para exibir os dados dinâmicos.
# ------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

AddMenuAcesso( '/estoques/RelInventarioPeriodicoContagem.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Localizacao  = $_GET['Localizacao'];
		$Ordem 				= $_GET['Ordem'];
		$Etapa        = $_GET['Etapa'];
}

if ( (is_null($Almoxarifado)) or (is_null($Localizacao)) ){
		header("location: /portalcompras/estoques/RelInventarioPeriodicoContagem.php");
		exit;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$ErroAssunto = "Erro em RelInventarioPeriodicoContagemPdf.php";

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapeInventario();

# Informa o Título do Relatório #
if ($Ordem == "1"){
		if ($Etapa==1){
				$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Família) - Tipo Contagem";
		} elseif($Etapa==2) {
				$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Família) - Tipo Recontagem";
		}
} else {
		if ($Etapa==1){
				$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Material) - Tipo Contagem";
		} elseif($Etapa==2) {
				$TituloRelatorio = "Relatório de Contagem de Inventário (Ordem: Material) - Tipo Recontagem";
		}
}
if ($Etapa==1){$Etapa="CONTAGEM";} elseif($Etapa==2){$Etapa="RECONTAGEM";}
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

# Verifica data do último inventário fechado
$sql = "
	SELECT max(tinvcoulat) as ultimo_periodico 
	FROM SFPC.TBinventariocontagem 
	WHERE clocmacodi = $Localizacao and finvcofech = 'S'
";
$res  = $db->query($sql);
if(db::isError($res)){
		//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		EmailErroSQL($ErroAssunto, __FILE__, __LINE__, "Erro no SQL", $sql, $res);
		$db->disconnect();
		exit(0);
}			
$Linha = $res->fetchRow();
$UltimoPeriodico = $Linha[0];

if($UltimoPeriodico=="" or is_null($UltimoPeriodico)){
		# Se não encontrou, então este á o primeiro inventário após criação do almoxarifado. 
		# Neste caso, colocar uma data mínima para todas datas de movimentação passarem
		$UltimoPeriodico = '1999-01-01 01:00:00';
}

$sql="
	SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU  
		FROM SFPC.TBINVENTARIOCONTAGEM A  
		WHERE A.CLOCMACODI= $Localizacao 
			AND A.FINVCOFECH IS NULL  
			AND A.AINVCOANOB=( 
				SELECT MAX(AINVCOANOB)  
					FROM SFPC.TBINVENTARIOCONTAGEM  
					WHERE CLOCMACODI = $Localizacao 
			)  
		GROUP BY A.CLOCMACODI,A.AINVCOANOB
";

$res = $db->query($sql);
if( db::isError($res) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
}else{
	$Linha = $res->fetchRow();
	$InvAno = $Linha[0];
	$InvSequ = $Linha[1];
	
	if( $Ordem == 1 ){
			# Pega os dados dos Materiais Cadastrados - Ordem Família #
			$sql  = "
				SELECT 
					A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, 
					--------------------------------------------------------
					-- Coluna que verifica se material com item zerado teve movimentação desde último inventário
					--------------------------------------------------------
					CASE 
						WHEN
							(
								( 
									-- Verifica se teve movimentação desde último periódico
									SELECT 
										count(*) 
									FROM SFPC.TBmovimentacaomaterial 
									WHERE 
										calmpocodi = ".$Almoxarifado."
										and cmatepsequ = A.CMATEPSEQU 
										and tmovmaulat  > '".$UltimoPeriodico."'
								) = 0
							) AND (
								(
										(
										-- verificação se o material está zerado no almoxarifado 
										-- (note:itens adicionados durante o inventario retornarão nulo pois só serao adicionados no armazenamento após o fechamento)
											SELECT ARMA.AARMATQTDE
											FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
											WHERE ARMA.CLOCMACODI = A.CLOCMACODI AND ARMA.CMATEPSEQU = A.CMATEPSEQU
										) = 0
								)  AND (
										(
										-- verificação se o material está adicionado no almoxarifado 
											SELECT count(*)
											FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
											WHERE ARMA.CLOCMACODI = A.CLOCMACODI AND ARMA.CMATEPSEQU = A.CMATEPSEQU
										) <> 0
							)
						)
						THEN 'S'
						ELSE 'N'
						END AS ZERADONAOMOVIMENTADO
						----------------------------------------------
						, F.EGRUMSDESC, E.ECLAMSDESC 
					FROM ( 
						SELECT CLOCMACODI, CMATEPSEQU FROM SFPC.TBARMAZENAMENTOMATERIAL WHERE CLOCMACODI = $Localizacao 
						UNION 
						SELECT CLOCMACODI, CMATEPSEQU 
							FROM SFPC.TBINVENTARIOMATERIAL 
							WHERE 
								CLOCMACODI = $Localizacao 
								AND AINVCOANOB = $InvAno
								AND AINVCOSEQU = $InvSequ
					) AS A 
						INNER JOIN SFPC.TBMATERIALPORTAL B ON (A.CMATEPSEQU = B.CMATEPSEQU) 
						INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON (B.CUNIDMCODI = C.CUNIDMCODI) 
						INNER JOIN SFPC.TBSUBCLASSEMATERIAL D ON (B.CSUBCLSEQU = D.CSUBCLSEQU) 
						INNER JOIN SFPC.TBCLASSEMATERIALSERVICO E ON (D.CGRUMSCODI = E.CGRUMSCODI AND D.CCLAMSCODI = E.CCLAMSCODI ) 
						INNER JOIN SFPC.TBGRUPOMATERIALSERVICO F ON (E.CGRUMSCODI = F.CGRUMSCODI) 
					WHERE A.CLOCMACODI = $Localizacao 
					ORDER BY F.EGRUMSDESC, E.ECLAMSDESC, B.EMATEPDESC 
			";
	}elseif( $Ordem == 2 ){
			# Pega os dados dos Materiais Cadastrados - Ordem Material #
			/*
			 SQL removido da Coluna que verifica se material com item zerado
									( 
										-- Verifica se este item foi adicionado após a criação do inventário
										
										SELECT 
											COUNT(*)
										FROM SFPC.TBINVENTARIOMATERIAL IM
										WHERE 
											IM.CLOCMACODI = A.CLOCMACODI
											and im.cmatepsequ = A.CMATEPSEQU
											AND IM.AINVCOANOB = $InvAno
											AND IM.AINVCOSEQU = $InvSequ
											AND IM.TINVMAULAT  > 
												(
													SELECT TINVCOBASE 
													FROM SFPC.TBINVENTARIOCONTAGEM IC 
													WHERE 
														IC.CLOCMACODI = IM.CLOCMACODI 
														AND IC.AINVCOANOB = IM.AINVCOANOB 
														AND IC.AINVCOSEQU = IM.AINVCOSEQU
												)
									) <> 0

									
									(
											-- Verifica se este item existe no armazenamento (isto é, não foi adicionado após a criação do inventário)
											SELECT COUNT(*)
											FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
											WHERE ARMA.CLOCMACODI = A.CLOCMACODI AND ARMA.CMATEPSEQU = A.CMATEPSEQU
									) IS NULL
			 
			*/
			$sql  = "
				SELECT 
					A.CMATEPSEQU, B.EMATEPDESC, C.EUNIDMSIGL, 
					--------------------------------------------------------
					-- Coluna que verifica se material com item zerado teve movimentação desde último inventário
					--------------------------------------------------------
					CASE 
						WHEN
							(
								( 
									-- Verifica se teve movimentação desde último periódico
									SELECT 
										count(*) 
									FROM SFPC.TBmovimentacaomaterial 
									WHERE 
										calmpocodi = ".$Almoxarifado."
										and cmatepsequ = A.CMATEPSEQU 
										and tmovmaulat  > '".$UltimoPeriodico."'
								) = 0
							) AND (
								(
										(
										-- verificação se o material está zerado no almoxarifado 
										-- (note:itens adicionados durante o inventario retornarão nulo pois só serao adicionados no armazenamento após o fechamento)
											SELECT ARMA.AARMATQTDE
											FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
											WHERE ARMA.CLOCMACODI = A.CLOCMACODI AND ARMA.CMATEPSEQU = A.CMATEPSEQU
										) = 0
								)  AND (
										(
										-- verificação se o material está adicionado no almoxarifado 
											SELECT count(*)
											FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
											WHERE ARMA.CLOCMACODI = A.CLOCMACODI AND ARMA.CMATEPSEQU = A.CMATEPSEQU
										) <> 0
							)
						)
						THEN 'S'
						ELSE 'N'
						END AS ZERADONAOMOVIMENTADO
						----------------------------------------------
					FROM ( 
						SELECT CLOCMACODI, CMATEPSEQU FROM SFPC.TBARMAZENAMENTOMATERIAL WHERE CLOCMACODI = $Localizacao
						UNION
						SELECT CLOCMACODI, CMATEPSEQU 
							FROM SFPC.TBINVENTARIOMATERIAL 
							WHERE 
								CLOCMACODI = $Localizacao 
								AND AINVCOANOB = $InvAno
								AND AINVCOSEQU = $InvSequ
					) AS A 
						INNER JOIN SFPC.TBMATERIALPORTAL B ON (A.CMATEPSEQU = B.CMATEPSEQU) 
						INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON (B.CUNIDMCODI = C.CUNIDMCODI) 
				ORDER BY B.EMATEPDESC 
			";
	}

	//echo "[".$sql."]";
	//exit(0);
	
	$res = $db->query($sql);
	if( db::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
	}else{
			# Pega as informações do Almoxarifado #
			$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
			$resalmo = $db->query($sqlalmo);
			if( db::isError($resalmo) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
			}else{
					$Almox     = $resalmo->fetchRow();
					$DescAlmox = $Almox[0];
	
					$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
					$pdf->Cell(183,5,"$DescAlmox",1,0,"L",0);
					$pdf->Cell(31,5,"DATA INVENTÁRIO", 1, 0, "L", 1);
					$pdf->Cell(36,5,"          /          /", 1, 1, "L", 0);
					$pdf->ln(5);
			}
	
			# Linhas de Itens de Material #
			$rows = $res->numRows();
			$qtdeItens =0; // itens mostrados na tela
			if( $rows == 0 ){
					$Mensagem = "Nenhuma Ocorrência Encontrada";
					$Url = "RelInventarioPeriodicoContagem.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
					exit;
			}else{
					for( $i=0; $i< $rows; $i++ ){
							$Linha              = $res->fetchRow();
							$CodigoReduzido[$i] = $Linha[0];
							$DescMaterial[$i]   = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]);
							$Unidade[$i]   		  = $Linha[2];
							$ZeradoNaoMovimentado[$i]   		  = $Linha[3];
	
							# Montando o array de Itens do Inventário #
							if( $Ordem == 1 ){
									$DescGrupo[$i]  = RetiraAcentos($Linha[4]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[4]);
									$DescClasse[$i] = RetiraAcentos($Linha[5]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[5]);
									$Itens[$i]      = $DescGrupo[$i].$SimboloConcatenacaoArray.$DescClasse[$i].$SimboloConcatenacaoArray.$DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$ZeradoNaoMovimentado[$i];
							}else{
									$Itens[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$CodigoReduzido[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$ZeradoNaoMovimentado[$i];
							}
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
		$Dados = split($SimboloConcatenacaoArray,$Itens[$i]);
		if( $Ordem == 1 ){
				$DescGrupo      = $Dados[0];
				$DescClasse     = $Dados[1];
				$DescMaterial   = $Dados[2];
				$CodigoReduzido = $Dados[3];
				$Unidade   		  = $Dados[4];
				$ZeradoNaoMovimentado = $Dados[5];

				# Pega a descrição do Grupo com acento #
				$DescricaoG = split($SimboloConcatenacaoDesc,$DescGrupo);
				$DescGrupo = $DescricaoG[1];

				# Pega a descrição do Grupo com acento #
				$DescricaoC = split($SimboloConcatenacaoDesc,$DescClasse);
				$DescClasse = $DescricaoC[1];

				if( $DescGrupoAntes != $DescGrupo or ( $DescGrupoAntes == $DescGrupo and $DescClasseAntes != $DescClasse ) ){
						$pdf->Cell(30,5,"GRUPO / CLASSE",1,0,"L",1);
						$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);
						$pdf->Cell(213,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(27,5,"CÓD REDUZIDO",1,0,"C",1);
						$pdf->Cell(27,5,$Etapa,1,1,"C",1);
				}
				$DescGrupoAntes     = $DescGrupo;
				$DescClasseAntes    = $DescClasse;
		}else{
				$DescMaterial   = $Dados[0];
				$CodigoReduzido = $Dados[1];
				$Unidade   		  = $Dados[2];
				$ZeradoNaoMovimentado = $Dados[3];
				if( $i == 0 ){
						$pdf->Cell(213,5,"DESCRIÇÃO DO ITEM",1,0,"L",1);
						$pdf->Cell(13,5,"UNID",1, 0,"C",1);
						$pdf->Cell(27,5,"CÓD REDUZIDO",1,0,"C",1);
						$pdf->Cell(27,5,$Etapa,1,1,"C",1);
				}
		}

		# Pega a descrição do Material com acento #
		$Descricao    = split($SimboloConcatenacaoDesc,$DescMaterial);
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
		if($ZeradoNaoMovimentado=='N'){
			$qtdeItens ++;
			if( $TamDescMaterial > 147 ){
					$Inicio = 0;
					$pdf->Cell(213,$AlturaMat,"",1,0,"L",0);
					for( $Quebra = 0; $Quebra < $LinhasMat; $Quebra++ ){
							if( $Quebra == 0 ){
									$pdf->SetX(10);
									$pdf->Cell(213,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
									$pdf->Cell(13,$AlturaMat,$Unidade,1,0,"C",0);
									$pdf->Cell(27,$AlturaMat,$CodigoReduzido,1, 0,"C",0);
									$pdf->Cell(27,$AlturaMat,"",1,0,"L",0);
									$pdf->Ln(5);
							}elseif( $Quebra == 1 ){
									$pdf->Cell(213,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
									$pdf->Ln(5);
							}elseif( $Quebra == 2 ){
									$pdf->Cell(213,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
									$pdf->Ln(5);
						 }elseif( $Quebra == 3 ){
									$pdf->Cell(213,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
									$pdf->Ln(5);
						 }else{
									$pdf->Cell(213,5,trim(substr($DescMaterialSepara,$Inicio,79)),0,0,"L",0);
									$pdf->Ln(5);
							}
							$Inicio = $Inicio + 79;
					}
					$pdf->Cell(213,0,"",1,1,"",0);
			}else{
					$pdf->Cell(213,5,$DescMaterial, 1,0, "L",0);
					$pdf->Cell(13,5,$Unidade,1,0,"C",0);
					$pdf->Cell(27,5,$CodigoReduzido,1, 0,"C",0);
					$pdf->Cell(27,5,"",1,1,"L",0);
			}
		}
}
# Mostra o totalizador de Itens do Inventário #
$pdf->Cell(253,5,"TOTAL DE ITENS", 1,0, "R",1);
$pdf->Cell(27,5,$qtdeItens, 1,1, "R",0);
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
			$this->Cell(0,30,"Portal de Compras - DGCO",0,0,"L");
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
			$this->Cell(179,5,"Emissão: ".date("d/m/Y H:i:s"),0,0,"L",0);
			$this->Cell(20,5,"CPF:",1,0,"L",1);
			$this->Cell(81,5,"",1,1,"C",0);
			$this->Cell(179,5,"",0,0,"C",0);
			$this->Cell(20,5,"Nome:",1,0,"L",1);
			$this->Cell(81,5,"",1,1,"C",0);
			$this->Cell(179,5,"",0,0,"C",0);
			$this->Cell(20,5,"Data:",1,0,"L",1);
			$this->Cell(81,5,"",1,1,"C",0);
			$this->Cell(179,5,"",0,0,"C",0);
			$this->Cell(20,5,"Assinatura:",1,0,"L",1);
			$this->Cell(81,5,"",1,1,"C",0);
		  $this->SetY(-10);
		  $this->Cell(199,5," ",0,0,"L");
			$this->Cell(81,5,"Página: ".$this->PageNo()."/{nb}",0,0,"R",0);
		}
	}
}
?> 
