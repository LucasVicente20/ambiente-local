<?php
# ---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoCCMaterialPdf.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Centro de Custo/Material.
# Autor:    Filipe Cavalcanti
# Data:     16/08/2005
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     10/03/2006
# ---------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     23/08/2006
# ---------------------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     27/10/2006
# Data:     18/12/2006 - Correção do select para mostrar a data do atendimento,
#                        e não a data da última atualização da requisição
# ---------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     13/06/2007 - Colocado filtro para restringir almoxarifado quando o centro de 
#           custo estiver relacionado com mais de um almoxarifado
# ---------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoCCMaterial.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$CentroCusto  = $_GET['CentroCusto'];
		$Material     = $_GET['CodigoReduzido'];
		$DataFim      = $_GET['DataFim'];
		$DataIni      = $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Atendimento por Centro de Custo/Material";

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

# Datas para consultas no banco #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Conecta com o Banco de Dados #
$db   = Conexao();

# Pega os dados do Almoxarifado #
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Campo            = $res->fetchRow();
		$DescAlmoxarifado = $Campo[0];
}

# Query para escrever o centro de custo #
$SqlCC  = "SELECT ECENPODESC, ECENPODETA ";
$SqlCC .= "  FROM SFPC.TBCENTROCUSTOPORTAL ";
$SqlCC .= " WHERE CCENPOSEQU = $CentroCusto ";
$res	  = $db->query($SqlCC);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlCC");
}else{
		$campoCC      = $res->fetchRow();
		$DescricaCC   = $campoCC[0];
		$EcenpoDescCC = $campoCC[1];
}

# Query para escrever o material e a unidade do material #
$Sqlmaterial   = " SELECT A.EMATEPDESC, B.EUNIDMSIGL FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
$Sqlmaterial  .= " WHERE A.CUNIDMCODI = B.CUNIDMCODI AND CMATEPSEQU = $Material ";
$result        = $db->query($Sqlmaterial);
if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlMaterial");
}else{
		$campo2     = $result->fetchRow();
		$descMat    = $campo2[0];
		$descMatUni = $campo2[1];
}

$pdf->Cell(33,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(157,5,$DescAlmoxarifado,1,1,"L",0);
$pdf->Cell(33,5,"CENTRO DE CUSTO",1,0,"L",1);
$pdf->MultiCell(0,5,$DescricaCC." - ".$EcenpoDescCC,1,1,"L",0);
$pdf->Cell(33,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(157,5,$DataIni." a ".$DataFim,1,1,"L",0);
$pdf->Cell(190,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"C",1);
$pdf->MultiCell(190,5,$Material." / ".$descMat." / ".$descMatUni,1,"J",0);
$pdf->ln(5);

$pdf->Cell(35,5,"Data de Atendimento",1,0,"C",1);
$pdf->Cell(50,5,"Número/Ano Requisição",1,0,"C",1);
$pdf->Cell(35,5,"Data da Requisição",1,0,"C",1);
$pdf->Cell(35,5,"Quantidade Solicitada",1,0,"C",1);
$pdf->Cell(35,5,"Quantidade Atendida",1,1,"C",1);

# Sql principal #
$Sql  = " SELECT DISTINCT B.TSITRESITU, A.AREQMAANOR, A.CREQMACODI, C.AITEMRQTSO, ";
$Sql .= "        C.AITEMRQTAT, A.DREQMADATA ";
$Sql .= "   FROM SFPC.TBREQUISICAOMATERIAL A, ";
$Sql .= "        SFPC.TBSITUACAOREQUISICAO B, ";
$Sql .= "        SFPC.TBITEMREQUISICAO C ";
$Sql .= "  WHERE A.CALMPOCODI = $Almoxarifado ";
$Sql .= "    AND A.DREQMADATA >= '$DataInibd' ";
$Sql .= "    AND A.DREQMADATA <= '$DataFimbd' ";
$Sql .= "    AND A.CREQMASEQU = B.CREQMASEQU ";
$Sql .= "    AND A.CREQMASEQU = C.CREQMASEQU ";
$Sql .= "    AND C.CMATEPSEQU = $Material ";
$Sql .= "    AND A.CCENPOSEQU = $CentroCusto ";
$Sql .= "    AND C.AITEMRQTAT > 0 ";
$Sql .= "    AND B.TSITRESITU = ";
$Sql .= "        (SELECT MAX(SIT.TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO SIT ";
$Sql .= "          WHERE SIT.CREQMASEQU = A.CREQMASEQU AND (SIT.CTIPSRCODI IN (3,4) OR SIT.CTIPSRCODI = 6) ) ";
$Sql .= "    AND B.CTIPSRCODI <> 6 ";

$Sql .= "    AND (SELECT DISTINCT CALMPOCODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CREQMASEQU = A.CREQMASEQU) = $Almoxarifado ";

$Sql .= " ORDER BY B.TSITRESITU ";

$res  = $db->query($Sql);

if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
}else{
		$rows = $res->numRows();
		if($rows == 0){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelAtendimentoCCMaterial.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for($i=0; $i< $rows; $i++){
						$Linha              = $res->fetchRow();
						$DataAtendimento    = $Linha[0];
						$AnoRequisicao      = $Linha[1];
						$NumRequisicao      = $Linha[2];
						$QtdSolicitada      = $Linha[3];
						$QtdAtendida        = $Linha[4];
						$DataRequisicao     = $Linha[5];
						$DataRequisicao     = substr($DataRequisicao,8,2)."/".substr($DataRequisicao,5,2)."/".substr($DataRequisicao,0,4);
						$DataAtendimento    = substr($DataAtendimento,8,2)."/".substr($DataAtendimento,5,2)."/".substr($DataAtendimento,0,4);
						$pdf->SetFont("Arial","",8);
						$pdf->SetFillColor(280);
						$pdf->Cell(35,5,$DataAtendimento,1,0,"C",1);
						$pdf->Cell(50,5,substr($NumRequisicao+100000,1)."/".$AnoRequisicao,1,0,"C",1);
						$pdf->Cell(35,5,$DataRequisicao,1,0,"C",1);
						$pdf->Cell(35,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada))),1,0,"R",1);
						$pdf->Cell(35,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida))),1,1,"R",1);
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
