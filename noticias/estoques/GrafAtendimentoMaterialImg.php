<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: GrafAtendimentoMaterialPieImg.php
# Autor:    Álvaro Faria
# Data:     22/05/2006
# Objetivo: Criar gráfico dos itens mais requisitados nos almoxarifados
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
if($GLOBALS["LOCAL_SISTEMA"]==CONST_NOMELOCAL_PRODUCAO){
	# para produção
	include ("/home/wwwdisco1/portal/html/common/jpgraph/jpgraph.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/jpgraph_pie.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/jpgraph_pie3d.php");
}else{
	# para desenvolvimento
	include ("/home/wwwdisco1/portal/html/common/jpgraph/src/jpgraph.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/src/jpgraph_pie.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/src/jpgraph_pie3d.php");
}
# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado   = $_GET['Almoxarifado'];
		$Alteracoes     = $_GET['Alteracoes'];
		$Quantidade     = $_GET['Quantidade'];
		$DataIni        = $_GET['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim        = $_GET['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Datas para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

$db   = Conexao();
$sql  = "SELECT A.CMATEPSEQU, B.EMATEPDESC, SUM(CASE WHEN C.FTIPMVTIPO = 'S' THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END) AS SOMA ";
$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBMATERIALPORTAL B, SFPC.TBTIPOMOVIMENTACAO C ";
$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU AND A.CTIPMVCODI = C.CTIPMVCODI ";
if($Alteracoes == 'S'){
		$sql .= "   AND A.CTIPMVCODI IN (4,20,22, 2,18,19,21) ";
}else{
		$sql .= "   AND A.CTIPMVCODI = 4 ";
}
$sql .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') "; // Apresentar só as movimentações ativas
$sql .= "   AND A.DMOVMAMOVI >= '$DataInibd' AND A.DMOVMAMOVI <= '$DataFimbd' ";
if($Almoxarifado != 'T') $sql .= "   AND A.CALMPOCODI = $Almoxarifado ";
$sql .= " GROUP BY A.CMATEPSEQU, B.EMATEPDESC ";
$sql .= " ORDER BY SOMA DESC";
$res  = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$qtd = $res->numRows();
		if($qtd > 0){
				$i = 0;
				while($Linha = $res->fetchRow()){
						if($i < $Quantidade){
								$QuantExibe = converte_quant(sprintf("%01.2f",str_replace(",",".",$Linha[2])));
								if($Linha[2] < 0) $Somatorio[$i] = 0;
								else $Somatorio[$i] = $Linha[2];
								$Material[$i]  = $Linha[0] . " - " . substr($Linha[1],0,20)." (".$QuantExibe.")";
								$i++;				
						}else{
								$Somatorio[$i] = $Somatorio[$i] + $Linha[2];
								$QuantExibe = converte_quant(sprintf("%01.2f",str_replace(",",".",$Somatorio[$i])));
								$Material[$i] = "OUTROS (".$QuantExibe.")";
						}
				}
				if($i >= $Quantidade){
						if($Somatorio[$i] < 0) $Somatorio[$i] = 0;
				}
		}
}
$db->disconnect();

if($Almoxarifado == 'T'){
		$AlmoxTitulo = 'Todos os almoxarifados';
}else{
		$db   = Conexao();
		$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $res->fetchRow();
				$AlmoxTitulo = $Linha[0];
		}
		$db->disconnect();
}

$grafico = new PieGraph(800,600,"auto");
$grafico->SetScale("textlin");
# Esquerda, Direita, Cima, Baixo #
//$grafico->SetMargin(10,0,0,0);
//$grafico->SetShadow();
$grafico->SetBackgroundImage( '../midia/brasaografico.jpg', BGIMG_FILLFRAME);
//$grafico->SetAntiAliasing();
$grafico->SetFrameBevel(0,false);

# Título e subtítulo do gráfico #
$grafico->title->SetFont(FF_ARIAL,FS_BOLD,12);
$grafico->title->Set('Portal de Compras da Prefeitura do Recife');
$grafico->subtitle->SetFont(FF_ARIAL,FS_NORMAL,8);
$grafico->subtitle->Set('Materiais mais atendidos (Und) - '.$AlmoxTitulo.'. Período: '.$DataIni.' a '.$DataFim);

# Valores do gráfico #
$p1 = new PiePlot3D($Somatorio);

# Destacar o valor correspondente ao elemento #
$p1->ExplodeSlice(0);

# Posição da pizza #
$p1->SetCenter(0.36);

# Fonte da porcentagem da fatia #
$p1->value->SetFont(FF_ARIAL,FS_NORMAL,7);

# Fonte da legenda #
$grafico->legend->SetFont(FF_ARIAL,FS_NORMAL,7);
# Posição da legenda #
$grafico->legend->Pos(0,0.5,"right","center");
$p1->SetLegends($Material);

# Adicionar valores ao gráfico #
$grafico->Add($p1);

# Gerar o gráfico #
$grafico->Stroke();
?>
