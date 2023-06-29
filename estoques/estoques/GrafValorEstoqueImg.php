<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: GrafValorEstoqueImg.php
# Autor:    Álvaro Faria
# Data:     22/05/2006
# Objetivo: Criar gráfico de valores estocados em cada almoxarifado
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";



if($GLOBALS["LOCAL_SISTEMA"]==CONST_NOMELOCAL_PRODUCAO){
	#Produção
	include ("/home/wwwdisco1/portal/html/common/jpgraph/jpgraph.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/jpgraph_bar.php");
}else{
	#Desenvolvimento
	include ("/home/wwwdisco1/portal/html/common/jpgraph/src/jpgraph.php");
	include ("/home/wwwdisco1/portal/html/common/jpgraph/src/jpgraph_bar.php");
}


$db   = Conexao();
$sql  = "SELECT CALMPOCODI, EALMPODESC ";
$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
$sql .= " WHERE CALMPOCODI <> 34 ";
$sql .= " ORDER BY EALMPODESC ";
$res  = $db->query($sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$i = 0;
		while($Almox = $res->fetchRow()){
				$sqlval  = "SELECT SUM(A.AARMATQTDE * A.VARMATUMED) ";
				$sqlval .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL A, SFPC.TBLOCALIZACAOMATERIAL B  ";
				$sqlval .= " WHERE A.CLOCMACODI = B.CLOCMACODI ";
				$sqlval .= "   AND B.CALMPOCODI = $Almox[0] ";
				$resval  = $db->query($sqlval);
				if( db::isError($resval) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlval");
				}else{
						$Val = $resval->fetchRow();
						if($Val[0] > 0){
								$indice = count($AlmoxDesc) + 1;
								$AlmoxCod[$indice-1] = $Almox[0];
								$AlmoxIndice[$indice-1] = $indice;
								$AlmoxDesc[$indice-1] = str_replace("ALMOXARIFADO ","",$Almox[1]);
								$AlmoxVal[$indice-1]  = ($Val[0]/1000);
						}
				}
				$i++;
		}
}
$db->disconnect();

$grafico = new graph(759,412,"auto");
$grafico->SetScale("textlin");

# Esquerda, Direita, Cima, Baixo #
$grafico->Set90AndMargin(203,24,65,20);
$grafico->SetShadow();
$grafico->SetBackgroundImage( '../midia/brasaografico.jpg', BGIMG_FILLFRAME);

$grafico->title->SetFont(FF_ARIAL,FS_BOLD,12);
$grafico->title->Set('Portal de Compras da Prefeitura do Recife');
$grafico->subtitle->SetFont(FF_ARIAL,FS_NORMAL,10);
$grafico->subtitle->Set('Valor em estoque por Almoxarifado (R$ x 1000)');

# pedir para mostrar os grides no fundo do gráfico, o ygrid é marcado como true por padrão #
$grafico->ygrid->Show(true);
$grafico->xgrid->Show(true);

$gBarras = new BarPlot($AlmoxVal);
$gBarras->SetFillColor("#DCEDF7");
$gBarras->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);
$gBarras->SetShadow("darkblue");

$valores = new BarPlot($AlmoxVal);
$valores->SetShadow();
$valores->value->Show();
$valores->value->SetFont(FF_ARIAL,FS_NORMAL,7);
$valores->value->SetAlign('left','center');
$valores->value->SetColor("black","darkred"); // Cores dos valores
$valores->value->SetFormat('%d');
$grafico->Add($valores);

$grafico->yaxis->SetFont(FF_ARIAL,FS_NORMAL,7);
$grafico->xaxis->SetFont(FF_ARIAL,FS_NORMAL,7);
$grafico->xaxis->SetTickLabels($AlmoxDesc);
$grafico->Add($gBarras);
$grafico->Stroke();
?>
