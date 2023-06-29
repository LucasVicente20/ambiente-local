<?php
# ------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAuxilioCancelamentoNotaPdf.php
# Autor:    Álvaro Faria
# Data:     02/02/2007
# Objetivo: Programa de Impressão da Movimentação de Material
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     20/01/2009 - Correções no relatório para não mostrar movimentações desfeitas
# ------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     01/09/2009 - Correção para cancelamentos que não possuem nenhuma ligação no registro com a movimentação cancelada
# ------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     08/09/2009 	- Remoção das classes contendo a regra de negócio para arquivo separado, ClaCancelamentoNota, para uso por outras ferramentas
#												- Remoção das variáveis GET Ulat, DataIni e DataFim e adicionamento das variáveis NotaFiscal e AnoNota. Necessário para obtenção de mais dados sobre a Nota Fiscal a ser cancelada.
# ------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoMaterial.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$Material     = $_GET['Material'];
		$Procedimento = $_GET['Procedimento'];
		$NotaFiscal   = $_GET['NotaFiscal'];
		$AnoNota      = $_GET['AnoNota'];
		//$Localizacao  = $_GET['Localizacao'];
		# data início e fim sempre vai ter o intervalo da criação da nota fiscal até o momento atual,
		# portanto são redundantes. Ulat pode ser recuperado pela nota fiscal
		//$DataFim      = $_GET['DataFim'];
		//$DataIni      = $_GET['DataIni'];
		//$Ulat         = urldecode($_GET['Ulat']);
}

if($Procedimento == "C"){
		$ProcedimentoDesc = "o cancelamento";
		# Informa o Título do Relatório #
		$TituloRelatorio = "Relatório de Auxílio para Cancelamento de Nota Fiscal";
}elseif($Procedimento == "M"){
		$ProcedimentoDesc = "a manutenção";
		# Informa o Título do Relatório #
		$TituloRelatorio = "Relatório de Auxílio para Manutenção de Nota Fiscal";
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Classes de cancelamento de movimentações
require_once("./ClaCancelamentoNota.php");

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

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

# Pega os dados do almoxarifado #
$db   = Conexao();
Banco::guardarSessao($db); // guardar sessão para uso nas classes
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}
$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmoxarifado,1,1,"L",0);

# Pega os dados do Material #
$sql  = "SELECT A.EMATEPDESC, B.EUNIDMSIGL ";
$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
$sql .= " WHERE A.CMATEPSEQU = $Material AND A.CUNIDMCODI = B.CUNIDMCODI";
$res  = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha        = $res->fetchRow();
		$DescMaterial = $Linha[0];
		$Unidade      = $Linha[1];

		# Pega os dados da nota fiscal #
		$Sql  = "
			select tmovmaulat
			from SFPC.TBmovimentacaomaterial
			where
				calmpocodi = ".$Almoxarifado."
				and aentnfanoe = ".$AnoNota."
				and centnfcodi = ".$NotaFiscal."
				and ctipmvcodi = 3
		";
		$res  = $db->query($Sql);
		if( db::isError($res) ){
			$db->disconnect();
			EmailErroSQL($GLOBALS["NomePrograma"], __FILE__, __LINE__, "Erro em SQL no construtor da classe MovimentacoesCancelamentoNota", $Sql, $res);
			exit();
		}
		$Linha = $res->fetchRow();

		$Ulat = $Linha[0];

		if(is_null($Ulat)){
			$db->disconnect();
			EmailErro($GLOBALS["NomePrograma"], __FILE__, __LINE__,"Nota Fiscal não foi encontrada.\n\nSQL: ".$Sql);
			exit();

		}

		$DataIni = DataBarra($Ulat);
		$DataFim = DataBarra(date("Y-m-d"));

		$pdf->Cell(30,5,"PERÍODO",1,0,"L",1);
		$pdf->Cell(250,5,$DataIni." a ".$DataFim,1,1,"L",0);
		$pdf->Cell(280,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"L",1);
		$pdf->MultiCell(280,5,$Material." / ".$DescMaterial." / ".$Unidade,1,"J",0);
		$pdf->Cell(280,5,"Efetue as ações inversas na ordem direta. Utilize marcação manual nas colunas DESFAZ e REFAZ para auxiliar o procedimento.",'LRT',1,"L",1);
		$pdf->Cell(280,5,"Após $ProcedimentoDesc da nota, faz-se necessário refazer todas as movimentações.",'LRB',1,"L",1);
		$pdf->Cell(280,5,"No campo DESCRIÇÃO DA MOVIMENTAÇÃO poderá vir o Número da Requisição, o Número da Nota Fiscal ou o Número da Movimentação.",'LRB',1,"L",1);
		//$pdf->Cell(280,5,"O campo QUANTIDADE informa a quantidade atual da Movimentação, incluindo movimentações de alterações sobre desta movimentação.",'LRB',1,"L",1);
		$pdf->Cell(280,5,"Para Notas Fiscais virtuais, a Nota Fiscal virtual será cancelada automaticamente no momento do cancelamento da requisição",'LRB',1,"L",1);
		$pdf->ln(5);

		$OcultarMovimentacao = false; //oculta a movimentação da iteração atual

		$cancelamentos = Cancelamentos::singleton();
		$movimentacoes = new MovimentacoesCancelamentoNota($Almoxarifado,$AnoNota,$NotaFiscal,$Material);
		$movimentacoes->ocultarCanceladas();

		if($movimentacoes->getNoMovimentacoes() == 0){
			$Mensagem = "Nenhuma Ocorrência Encontrada";
			$Url = "RelMovimentacaoMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
			if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
			exit;
		}

		/*for($i=0; $i< $movimentacoes->getNoMovimentacoes(); $i++){
				$movimentacao = $movimentacoes->getMovimentacao($i);
				$DataMovimentacao  = $movimentacao->getDataMovimentacao();
				$CodMovimentacao   = $movimentacao->getCodigo();
				//subtrair quantidade já cancelada da quantidade movimentada
				//$QtdeMovimentacao  = $movimentacao->getQtdeMaterial() -  $movimentacao->getQtdeMaterialCancelamento();
				$QtdeMovimentacao  = $movimentacao->getQtdeMaterial();
				$QtdeMovimentacaoCancelado = $movimentacao->getQtdeMaterialCancelamento();
				$ValorMovimentacao = $movimentacao->getValorMaterial();
				$TipoMovimentacao  = $movimentacao->getEntradaSaida();
				if(!is_null($movimentacao->getRequisicao())){
					$Requisicao    		= $movimentacao->getRequisicao()->getCodigo();
					$AnoRequisicao 		= $movimentacao->getRequisicao()->getAno();
				}else{
					$Requisicao    		= null;
					$AnoRequisicao 		= null;
				}
				if(!is_null($movimentacao->getNotaFiscal())){
					$NotaNumero    		= $movimentacao->getNotaFiscal()->getNota();
					$NotaSerie     		= $movimentacao->getNotaFiscal()->getSerie();
					$NotaCancelada 		= $movimentacao->getNotaFiscal()->getCancelado();
				}else{
					$NotaNumero    		= null;
					$NotaSerie     		= null;
					$NotaCancelada 		= null;
				}
				$CodMovimentacaoT 		= $movimentacao->getCodigoTipo();
				$CodTipoMov 		= $movimentacao->getTipo();
				$finalizada = $movimentacao->getFinalizado();
				try{
					$CodTipoMovRetorno 	= $cancelamentos->getCancelamento($CodTipoMov,$finalizada,0);
				} catch(ExceptionValorNaoEncontrado $e){
					//echo "[CodTipoMov- '".$CodTipoMov."' ]";
					//echo "[finalizada- '".$finalizada."' ]";

					$movimentacoes->getMovimentacao($i)->setOcultar(true);
				}
				//echo "[1-".$CodTipoMov."]";
				//echo "[2-".$finalizada."]";
				$DescMovimentacao 	= $cancelamentos->getDescricao($CodTipoMov);
				$DescRetorno 	= $cancelamentos->getDescricao($CodTipoMovRetorno);

				$OcultarMovimentacao = $movimentacoes->getMovimentacao($i)->getOcultar();

				echo "<br/>[".$CodMovimentacao."]";
				echo "[".$CodTipoMov."]";
		}*/

		for($i=0; $i< $movimentacoes->getNoMovimentacoes(); $i++){
				$movimentacao = $movimentacoes->getMovimentacao($i);
				$DataMovimentacao  = $movimentacao->getDataMovimentacao();
				$CodMovimentacao   = $movimentacao->getCodigo();
				//subtrair quantidade já cancelada da quantidade movimentada
				//$QtdeMovimentacao  = $movimentacao->getQtdeMaterial() -  $movimentacao->getQtdeMaterialCancelamento();
				$QtdeMovimentacao  = $movimentacao->getQtdeMaterial();
				$QtdeMovimentacaoCancelado = $movimentacao->getQtdeMaterialCancelamento();
				$ValorMovimentacao = $movimentacao->getValorMaterial();
				$TipoMovimentacao  = $movimentacao->getEntradaSaida();
				if(!is_null($movimentacao->getRequisicao())){
					$Requisicao    		= $movimentacao->getRequisicao()->getCodigo();
					$AnoRequisicao 		= $movimentacao->getRequisicao()->getAno();
				}else{
					$Requisicao    		= null;
					$AnoRequisicao 		= null;
				}
				if(!is_null($movimentacao->getNotaFiscal())){
					$NotaNumero    		= $movimentacao->getNotaFiscal()->getNota();
					$NotaSerie     		= $movimentacao->getNotaFiscal()->getSerie();
					$NotaCancelada 		= $movimentacao->getNotaFiscal()->getCancelado();
				}else{
					$NotaNumero    		= null;
					$NotaSerie     		= null;
					$NotaCancelada 		= null;
				}
				$CodMovimentacaoT 		= $movimentacao->getCodigoTipo();
				$CodTipoMov 		= $movimentacao->getTipo();
				$finalizada = $movimentacao->getFinalizado();
				try{
					$CodTipoMovRetorno 	= $cancelamentos->getCancelamento($CodTipoMov,$finalizada,0);
				} catch(ExceptionValorNaoEncontrado $e){
					//echo "[CodTipoMov- '".$CodTipoMov."' ]";
					//echo "[finalizada- '".$finalizada."' ]";
					/*
					 * Movimentacao não possui cancelamento.
					 * Neste caso supõe-se que a movimentação não precisa ser cancelada,
					 * portanto não deve ser mostrado no relatório
					 */
					$movimentacoes->getMovimentacao($i)->setOcultar(true);
				}
				//echo "[1-".$CodTipoMov."]";
				//echo "[2-".$finalizada."]";
				$DescMovimentacao 	= $cancelamentos->getDescricao($CodTipoMov);
				/*echo "[$CodTipoMov]";
				echo "[$DescMovimentacao]";
				echo "[$CodTipoMovRetorno]";
				echo "[$Finalizada]";
				exit;*/
				if(is_null($CodTipoMovRetorno)){
					$DescRetorno 	= "NÃO PODE SER CANCELADO";
				}else{
					$DescRetorno 	= $cancelamentos->getDescricao($CodTipoMovRetorno);
				}

				$OcultarMovimentacao = $movimentacoes->getMovimentacao($i)->getOcultar();

				if($i == 0){
						$pdf->Cell(17,10,"DATA", 1, 0, "C", 1);
						$pdf->Cell(10,10,"MOV.", 1, 0, "C", 1);
						$pdf->Cell(97,10,"DESCRIÇÃO DA MOVIMENTAÇÃO", 1, 0, "L", 1);
						$pdf->Cell(20,10,"QUANTID.", 1, 0, "C", 1);
						$pdf->Cell(20,10,"VALOR", 1, 0, "C", 1);
						$pdf->Cell(86,10,"AÇÃO INVERSA A REALIZAR", 1, 0, "C", 1);
						$pdf->Cell(30,5,"AÇÃO", 1, 1, "C", 1);
						$pdf->SetX(260);
						$pdf->Cell(15,5,"DESFAZ", 1, 0, "C", 1);
						$pdf->Cell(15,5,"REFAZ", 1, 1, "C", 1);
				}


				/*#Verificar linhas que estão sendo canceladas
				$pdf->Cell(25,5,"_".$ValorMovimentacao."_".$ValorMovimentacaoProximo, 1, 0, "C", 1);*/


				$alturaLinha = 5;
				$linhasPorCelula = 1;

				$tamanhodescRetorno = 1;

				$alturaCelula = $alturaLinha*$linhasPorCelula;
				if(!$OcultarMovimentacao){
					if($DataMovimentacao <> $DataMovAnt){
						$pdf->Cell(17,$alturaCelula,"$DataMovimentacao",1,0,"C",0);
						$DataMovAnt = $DataMovimentacao;
					}else{
						$pdf->Cell(17,$alturaCelula,"",1,0,"C",0);
					}
					$pdf->Cell(10,$alturaCelula,$TipoMovimentacao,1,0,"C",0);
					if     ($Requisicao != ""){ $Add = " - ".substr($Requisicao+100000,1)."/$AnoRequisicao";
					}elseif($NotaNumero != ""){ $Add = " - ".$NotaNumero."/".$NotaSerie;
					}else{$Add = " - ".$CodMovimentacaoT;}
				/*echo "<br/>[".$CodMovimentacao."]";
				echo "[".$CodTipoMov."]";*/
					$pdf->Cell(97,$alturaCelula,$DescMovimentacao.$Add,1,0,"L",0);
					$pdf->Cell(20,$alturaCelula,$QtdeMovimentacao,1,0,"R",0);
					$pdf->Cell(20,$alturaCelula,$ValorMovimentacao,1,0,"R",0);
					$pdf->Cell(86,$alturaCelula,$DescRetorno,1,0,"L",0);
					$pdf->Cell(15,$alturaCelula,"", 1, 0, "C", 0);
					$pdf->Cell(15,$alturaCelula,"", 1, 1, "C", 0);
					$Add = "";
				}

		}
}
$db->disconnect();
$pdf->Output(mktime().'.pdf','D');
?>
