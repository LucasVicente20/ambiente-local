<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompTramitacaoAgentePdf.php
# Objetivo: Programa para impressão do relatório de Acompanhamento de Tramitação por Agente
# Autor:    Caio Coutinho
# Data:     14/08/2018
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../common/mpdf60/mpdf.php";

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

$db = Conexao();

# Adiciona páginas no MenuAcesso #
//AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimento.php' );

# Variáveis com o global off #
//if($_SERVER['REQUEST_METHOD'] == "GET"){
	$buscar = $_SESSION['relTramitacaoAgente'];

	$agente          = $buscar['agente'];
	$agenteDesc		 = $buscar['agenteDesc'];
	$responsavel     = $buscar['responsavel'];
	$responsavelDesc = $buscar['responsavelDesc'];
	$DataIni 		 = $buscar['dataInicio'];
	$DataFim 		 = $buscar['dataFim'];
	$situacao		 = $buscar['situacao'];
	$atraso 		 = $buscar['atraso'];
//}






# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();


# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Tramitação";


# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
	$pdf->SetFont("Arial","",8);

// Início conteudo do PDF
$html = "<html><body>";
$css = "
@page {
	margin-top:15px;
	margin-left:40px;
	margin-right:40px;
}

body{
	font-family:Arial;
	font-size: 12px;
}

#cabecalho{
	margin-top:0px;
	font-weight: bold;
	font-family:Arial;
	font-size: 14px;
}

.titulo{
	font-weight: bold;
	font-family:Arial;
	font-size: 14px;
}

.campos_pesquisa{
	border-spacing:0px;
	border-bottom: 1px solid #000;
	border-left: 1px solid #000;
}

.campos_pesquisa td{
	border: 1px solid #000;
	border-bottom: 0px solid #000;
	border-left: 0px solid #000;
	
	padding:3px;
}

.campo_pesquisado{
	text-transform uppercase;
	background-color: #D3D3D3;
}

#titulorelatorio{
	margin-top: 10px;
	border-top: 1px solid #000;
	border-bottom: 1px solid #000;

}
#rodaperelatorio{
	margin-bottom: 0px;
	border-top: 1px solid #000;


}
#brasao{
	margin-left:215px;
}";
$brasao = "../" . $GLOBALS["PASTA_MIDIA"] . "brasaopeq.jpg";
$cabecalho = "<table width='100%' id='cabecalho' >
				<tr>
					<td ><br><br>Prefeitura do Recife</td>
					<td ><img id='brasao' src='$brasao' width='85' height='auto'></td>
					<td style='text-align:right;'><br><br>Portal de Compras</td>
					</tr>

				</table>
				<br>
				<table width='100%' id='titulorelatorio'>
					<tr>
						<td align='center' class='titulo'>RELATÓRIO DE ACOMPANHAMENTO DE TRAMITAÇÃO POR AGENTE</td>
					</tr>
				</table>
				<br>
				";

$html .= $cabecalho;

// DADOS DA PESQUISA

$html .="<br><table width='100%' class='campos_pesquisa'>";
	

	if(!empty($agente)) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>AGENTE</td>";
		$html .="<td align='left' class='campos_pesquisa' >".strtoupper2($agenteDesc)."</td>";
		$html .="</tr>";
	}
	if(!empty($responsavel)) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>RESPONSÁVEL</td>";
		$html .="<td align='left' class='campos_pesquisa' >".strtoupper2($responsavelDesc)."</td>";
		$html .="</tr>";
	}
	if(!empty($DataIni) ){
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>PERÍODO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$DataIni." a ".$DataFim."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['situacao'])) {

		switch($buscar['situacao']){
			case "andamento":
				$txtSituacao = 'Em andamento';
				break;
			case "concluidas":
				$txtSituacao = 'Concluídas';
				break;
			case "todas":
				$txtSituacao = 'Todas';
				break;
		}

		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>SITUAÇÃO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$txtSituacao."</td>";
		$html .="</tr>";
	}
	if(!empty($atraso)) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>SÓ EM ATRASO</td>";
		$html .="<td align='left' class='campos_pesquisa' >Sim</td>";
		$html .="</tr>";	
	}


$html .="</table>";






$dados = protocoloPesquisarAgentes($db, $_SESSION['relTramitacaoAgente']);
// Adicionar último passo de cada protocolo
if(!empty($dados)) {
    foreach($dados as $key => $value) {                
		$atual = date('Y-m-d');
		$passos_ = getTramitacaoPassos($value[0]);
		$passos_[0][3] = substr($passos_[0][3], 0, 10);
		$passos_[0][5] = !empty($passos_[0][5]) ? substr($passos_[0][5], 0, 10) : '';
		$calcSaida = calcularTramitacaoSaida(DataBarra($passos_[0][3]), $passos_[0][4]);       
		$arrCalcSaida = explode("/",$calcSaida);
		$calcSaidaDate = $arrCalcSaida[2]."-".$arrCalcSaida[1]."-".$arrCalcSaida[0];                                        
	   
		if (empty($passos_[0][5])) { // Saída não realizada
			//$diffDias = calculaDias2($calcSaidaDate, $atual); 
			$diffDias = calcularTramitacaoDiasUteisAtraso($calcSaidaDate, $atual); 
			
			if(strtotime($atual) <= strtotime($calcSaidaDate)) {     
				$passos_[0]['atraso'] = ' - ';
			} else {
				$exibirAtrasados = true;
				$dia = ($diffDias > 1) ? ' dias' : ' dia';
				$passos_[0]['atraso'] = $diffDias . $dia;
			}
		} else { // Saída ralizada
			//$diffDias = calculaDias2($passos_[0][5], $calcSaidaDate);
			$diffDias = calcularTramitacaoDiasUteisAtraso($passos_[0][5], $calcSaidaDate); 

			if($passos_[0][5] <= DataInvertida($calcSaida)) {
				$passos_[0]['atraso'] = ' - ';
			} else {
				$exibirAtrasados = true;
				$dia = ($diffDias > 1) ? ' dias' : ' dia';
				$passos_[0]['atraso'] = $diffDias . $dia;
			}
		}                
		
		$dados[$key]['ultimo_passo'] = $passos_[0];
		
		// Remover os não atrasados
		if(!empty($atrasoAtual) && $atrasoAtual == 'S' && strpos($passos_[0]['atraso'], 'dia') === false) {
			unset($dados[$key]);
		}
	}
}

if( !empty($dados) ){
    $htmlCabecalhoItem ="<br><table width='100%' class='campos_pesquisa'>";	  
    $htmlCabecalhoItem .="<tr>";
    $htmlCabecalhoItem .="<td rowspan='2' align='center' class='campo_pesquisado'>AGENTE DE TRAMITAÇÃO</td>";
    $htmlCabecalhoItem .="<td rowspan='2' align='center' class='campo_pesquisado'>RESPONSÁVEL</td>";
    $htmlCabecalhoItem .="<td rowspan='2' align='center' class='campo_pesquisado'>NÚMERO DO PROTOCOLO</td>";
    $htmlCabecalhoItem .="<td colspan='5' align='center' class='campo_pesquisado'>ÚLTIMO PASSO</td>";		
    $htmlCabecalhoItem .="</tr>";
    $htmlCabecalhoItem .="<tr>";
    $htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>AÇÃO</td>";
    $htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>ENTRADA</td>";
    $htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>SAÍDA</td>";
    $htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>ATRASO</td>";
    $htmlCabecalhoItem .="<td align='center' class='campo_pesquisado'>OBSERVAÇÃO</td>";
    $htmlCabecalhoItem .="</tr>";		
    $html .= $htmlCabecalhoItem;

    foreach($dados as $value) {    
    $htmlItem .= "<tr>";
	$htmlItem .= "<td align='center'>".$value[1]."</td>";
	$htmlItem .= "<td align='center'>";
	$entrada = 0;
	$saida = 0;

	if($value[1]>0){
		$htmlItem .= $value[2];


		if($value[2] || $value[5]){

			if($value[2] == $responsavelDesc){
			   $entrada = 1;
			}

			if($value[5] == $responsavelDesc){
			   $saida = 1;
			}


			switch ($entrada+$saida) {
				case 1:
						if($entrada){
							$htmlItem .= " (Entrada)";
						}else{
							$htmlItem .= " (Saída)";
						}
					break;
				case 2:
				$htmlItem .= " (Entrada/Saída)";
					break;
			}


		}
	}else{

		if($value[6]=='E'){
			$htmlItem .= 'ÓRGÃO EXTERNO';
		}else{
			$htmlItem .= $value[1];
		}

	}

	
	
	$htmlItem .= "</td>";
    $htmlItem .= "<td align='center'>".str_pad($value[3], 4, "0", STR_PAD_LEFT)."/".$value[4]."</td>";
    $htmlItem .= "<td align='center'>".$value['ultimo_passo'][2]."</td>";
    $htmlItem .= "<td align='center'>".DataBarra($value['ultimo_passo'][3])."</td>";
    $saida = (!empty($value['ultimo_passo'][5])) ? DataBarra($value['ultimo_passo'][5]) : ' - ';
	$htmlItem .= "<td align='center'>".$saida."</td>";
	if($value['ultimo_passo'][15] != 'S'){ 
		$htmlItem .= "<td align='center'><b>".$value['ultimo_passo']['atraso']."</b></td>";
	}else{
		$htmlItem .= "<td align='center'><b> - </b></td>";	
	}
    $htmlItem .= "<td style='border-top: 1px solid #000' align='center'>".$value['ultimo_passo'][6]."</td>";	
    $htmlItem .="</tr>";
    }

    $html .= $htmlItem;
	$html .= "</table>";		
} else {
    $Mensagem = "Nenhuma Ocorrência Encontrada";
    $Url = "RelAcamitacaoAgenteSelecionar.php?Mensagem=".urlencode($Mensagem)."&Critica=1&Mens=1&Tipo=1";
    if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
    header("location: ".$Url);
    exit;
}

$db->disconnect();

//finaliza arquivo
$html .= "</body></html>";

$mpdf=new mPDF("c","A4-L"); 
$mpdf->SetDisplayMode('fullpage');
//$css = file_get_contents("css/estilo.css");

$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html);
$mpdf->SetHTMLFooter('
<table width="100%" id="rodaperelatorio"><tr><td></td></tr></table>
<table width="100%">
    <tr>
        <td width="33%">Emissão: {DATE d/m/Y H:i:s}</td>
        <td width="33%" align="center"></td>
        <td width="33%" style="text-align: right;">{PAGENO}/{nbpg}</td>
    </tr>
</table>');
$mpdf->Output();

//unset($_SESSION['relTramitacaoMonitoramento']);
?>

