<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTramitacaoMonitoramentoPdf.php
# Objetivo: Programa para impressão do relatório de Tramitação
# Autor:    Caio Coutinho
# Data:     07/08/2018
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

# Adiciona páginas no MenuAcesso #
//AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimento.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){

	$numProtocolo   = $_GET['numProtocolo'];
	$anoProtocolo   = $_GET['anoProtocolo'];
	$orgao          = $_GET['orgao'];
	$objeto         = $_GET['objeto'];
	$numeroci       = $_GET['numeroci'];
	$numeroOficio   = $_GET['numeroOficio'];
	$numeroScc      = $_GET['numeroScc'];
	$proLicitatorio = $_GET['proLicitatorio'];
	$acao           = $_GET['acao'];
	$origem         = $_GET['origem'];
	$DataEntradaIni = $_GET['DataEntradaIni'];
	$DataEntradaFim = $_GET['DataEntradaFim'];



}

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
}

.protocolo{
	border-top: 2px solid #000;
}
";
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
						<td align='center' class='titulo'>".$GLOBALS['TituloRelatorio']."</td>
					</tr>
				</table>
				<br>
				";

$html .= $cabecalho;

$dados = protocoloPesquisar($_SESSION['tramitacaoProtocolo'], 'relMonitoramento');

$buscar = $_SESSION['tramitacaoProtocolo'];



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
			//if(DataInvertida($atual) <= DataInvertida($calcSaida)) {
				$passos_[0]['atraso'] = ' - ';
			} else {
				$exibirAtrasados = true;
				$dia = ($diffDias > 1) ? ' dias' : ' dia';
				$passos_[0]['atraso'] = $diffDias . $dia;
			}
		} else { // Saída ralizada
			$diffDias = calcularTramitacaoDiasUteisAtraso($passos_[0][5], $calcSaidaDate); 
			//$diffDias = calculaDias2($passos_[0][5], $calcSaidaDate);

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
	//

	$html .="<br><table width='100%' class='campos_pesquisa'>";
	
	if(!empty($buscar['protocolo'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%' >NÚMERO/ANO PROTOCOLO DO PROCESSO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".str_pad($buscar['protocolo'], 4, "0", STR_PAD_LEFT).'/'.$buscar['anoProtocolo']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['grupo'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%' >GRUPO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['grupoDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['orgao'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%' >ORGÃO DEMANDANTE</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['orgaoDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['objeto'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>OBJETO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['objeto']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['numeroCI'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>NÚMERO CI</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['numeroCI']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['numeroOficio'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>NÚMERO OFÍCIO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['numeroOficio']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['numeroScc'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>NÚMERO SCC</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['numeroScc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['comissao'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>COMISSÃO DA LICITAÇÃO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['comissaoDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['processoNumero'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>PROCESSO LICITATÓRIO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['grupoDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['acao'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>AÇÃO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['acaoDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['agente'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>AGENTE DESTINO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['agenteDesc']."</td>";
		$html .="</tr>";
	}
	if(!empty($buscar['dataIni'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>PERÍODO DE ENTRADA DO PROTOCOLO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$buscar['dataIni']." a ".$buscar['dataFim']."</td>";
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
	if(!empty($buscar['ordem'])) {

		switch($buscar['ordem']){
			case "numAnoDesc":
				$txtOrdem = 'Número / Ano do Protocolo (DESC)';
				break;
			case "orgao":
				$txtOrdem = 'Órgão demandante (ASC)';
				break;
		}

		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>ORDEM DE EXIBIÇÃO</td>";
		$html .="<td align='left' class='campos_pesquisa' >".$txtOrdem."</td>";
		$html .="</tr>";
	}

	if(!empty($buscar['atraso'])) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' width='20%'>SÓ EM ATRASO</td>";
		$html .="<td align='left' class='campos_pesquisa' >Sim</td>";
		$html .="</tr>";	
	}


	$html .="</table>";

	$html .="<br><table width='100%' class='campos_pesquisa'>";

	foreach($dados as $value) {
		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;' >NÚMERO/ANO PROTOCOLO DO PROCESSO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>ÓRGÃO DEMANDANTE</td>";
		$html .="<td colspan='5' align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>OBJETO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>NÚMERO CI</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>NÚMERO OFÍCIO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>PERÍODO DE ENTRADA DA PROTOCOLO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>MONITORAMENTO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>NÚMERO SCC</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>PROCESSO LICITATÓRIO</td>";
		$html .="<td align='center' class='campo_pesquisado' style='border-top: 3px solid #000;'>FASE ATUAL</td>";
		$html .="</tr>";		
		
		$html .= "<tr>";
			$html .= "<td rowspan='4' align='center'>".str_pad($value[1], 4, "0", STR_PAD_LEFT).'/'.$value[5]."</td>";
			$html .= "<td align='center'>".$value[2]."</td>";
			$html .= "<td colspan='5'>".$value[4]."</td>";
			$ci = (!empty($value[6])) ? $value[6] : ' - ';
			$oficio = (!empty($value[7])) ? $value[7] : ' - ';
			$html .= "<td align='center'>".$ci."</td>";
			$html .= "<td align='center'>".$oficio."</td>";
			$html .= "<td align='center'>".DataBarra($value[3])."</td>";
			$html .= "<td align='center'>".$value[23]."</td>";
			$getScc = (!empty($value[8])) ? getNumeroSolicitacaoCompra($db, $value[8]) : ' - ';
			$html .= "<td align='center'>".$getScc."</td>";

			$processo = ' - '; 
			$fase = ' - ';

			if(!empty($value[8])){ 
					//APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
					
					$arrFase = getFaseLicitacaoScc($value[8]);
					$arrProcesso = getProcessoScc($value[8]);

					if(!empty($arrFase)){
						$arrFase = $arrFase[0];
					}
					if(!empty($arrProcesso)){
						$arrProcesso = $arrProcesso[0];
					}

					//var_dump($arrProcesso);
				
					if(!empty($arrProcesso[0])) {
						$processo = str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5];
						$fase = $arrFase[1];
					}else{
						$processo = "-";
						$fase = "-";
					}

			}else{

				if(!empty($value[13])) {
					$processo = str_pad($value[13], 4, "0", STR_PAD_LEFT) . '/' . $value[14]. ' - '. $value[16];
					$fase = $value[15];
				}

			}


			$html .= "<td align='center'>".$processo."</td>";
			$html .= "<td align='center'>".$fase."</td>";
		$html .="</tr>";
		
		$html .="<tr>";
			$html .="<td align='center' class='campo_pesquisado' colspan='7'>ÚLTIMO PASSO</td>";
			$html .="<td align='center' class='campo_pesquisado' colspan='6'>COMPARATIVO VALORES TOTAIS PROCESSO LICITATÓRIO</td>";
		$html .="</tr>";

		$html .="<tr>";
		$html .="<td align='center' class='campo_pesquisado'>AÇÃO</td>";
		$html .="<td align='center' class='campo_pesquisado'>ENTRADA</td>";
		$html .="<td align='center' class='campo_pesquisado'>SAÍDA</td>";
		$html .="<td align='center' class='campo_pesquisado'>ATRASO</td>";
		$html .="<td align='center' class='campo_pesquisado'>AGENTE DE TRAMITAÇÃO</td>";
		$html .="<td align='center' class='campo_pesquisado'>USUÁRIO RESPONSÁVEL</td>";
		$html .="<td align='center' class='campo_pesquisado'>OBSERVAÇÃO</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado' >ENTRADA PROTOCOLO</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado'>ESTIMADO LICITAÇÃO</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado'>ECONOMICIDADE %</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado'>ESTIMADO (ITENS QUE LOGRARAM COM ÊXITO)</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado'>HOMOLOGADO (ITENS QUE LOGRARAM COM ÊXITO)</td>";
		$html .="<td style='border-bottom: 1px solid #000;' align='center' class='campo_pesquisado'>ECONOMICIDADE %</td>";
		$html .="</tr>";				
		$html .="<tr>";				
        $html .= "<td align='center'>".strtoupper2($value['ultimo_passo'][2])."</td>"; // Ação
        $html .= "<td align='center'>".DataBarra($value['ultimo_passo'][3])."</td>"; // Entrada
		$saida = !empty($value['ultimo_passo'][5]) ? DataBarra($value['ultimo_passo'][5]) : ' - '; 
		$html .= "<td align='center'>".$saida."</td>"; // Saída
		$html .= "<td align='center'><b>".$value['ultimo_passo']['atraso']."</b></td>"; // Atraso
		$html .= "<td align='center'>".strtoupper2($value['ultimo_passo'][0])."</td>"; 

		// usuario
		$usuarioDesc = '';
		if($value['ultimo_passo'][17]=='S'){
										
			if($value['ultimo_passo'][8] <= 0 ){
				$usuarioDesc = $value['ultimo_passo'][0];
			}else{
				$usuarioDesc = $value['ultimo_passo'][1];
			}
		}else{
			if($value['ultimo_passo'][8] <= 0){
				if($value['ultimo_passo'][9]=='I'){
					$usuarioDesc = $value['ultimo_passo'][0];
				}else{
					$usuarioDesc = 'ÓRGÃO EXTERNO';
				}
			}else{
				$usuarioDesc = $value['ultimo_passo'][1];
			}
		}

		$html .= "<td style='border-right: 1px solid #000; border-top: 1px solid #000' align='center'>".strtoupper2($usuarioDesc)."</td>";
		$html .= "<td style='border-right: 1px solid #000; border-top: 1px solid #000' align='center'>".$value['ultimo_passo'][6]."</td>";
		$html .= "<td style='border-right: 1px solid #000;' align='center'>R$ ". converte_valor_estoques($value[9])."</td>";
		$html .= "<td style='border-right: 1px solid #000;' align='center'>R$ ". converte_valor_estoques($value[10])."</td>";
		$diferenca_1 = floatval($value[9]) - floatval($value[10]);

		if($value[9]>0){
			$economicidade_1 = ($diferenca_1 != 0) ? number_format(((($diferenca_1 * 100) / $value[9])), 2, ',', '.') . ' %' : ' - ';
		}else{
			$economicidade_1 = 0;
		}

		if($value[10] <= 0){
			$economicidade_1 = '-';
		}else{
			if($value[9]>0){
				$economicidade_1 = $economicidade_1 . ' %';
			}else{
				$economicidade_1 = '-';
			}
			
		}
		$html .= "<td style='border-right: 1px solid #000;' align='center'>".$economicidade_1."</td>";
		$html .= "<td style='border-right: 1px solid #000;' align='center'>R$ ".converte_valor_estoques($value[11])."</td>";
		$html .= "<td style='border-right: 1px solid #000;' align='center'>R$ ".converte_valor_estoques($value[12])."</td>";
		$diferenca_2 = floatval($value[11]) - floatval($value[12]);
		if($value[11]>0){
			$economicidade_2 = ($diferenca_2 != 0) ? number_format(((($diferenca_2 * 100) / $value[11])), 2, ',', '.') . ' %' : ' - ';
		}else{
			$economicidade_2 = 0;
		}

		if($value[12] <= 0){
			$economicidade_2 = '-';
		}else{
			if($value[11]>0){
				$economicidade_2 = $economicidade_2 . ' %';
			}else{
				$economicidade_2 = '-';
			}
			
		}
		
		$html .= "<td style='border-right: 1px solid #000;' align='center'>".$economicidade_2."</td>";
		$html .="</tr>";
    }
                            
	$html .= "</table>";		
} else {
    $Mensagem = "Nenhuma Ocorrência Encontrada";
    $Url = "RelTramitacaoMonitoramento.php?Mensagem=".urlencode($Mensagem)."&Critica=1&Mens=1&Tipo=1";
    if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
    header("location: ".$Url);
    exit;
}
//print_r($html);exit;
$db->disconnect();

//finaliza arquivo
$html .= "</body></html>";

$mpdf=new mPDF("c","A4-L"); 
$mpdf->SetDisplayMode('fullpage');
//$css = file_get_contents("css/estilo.css");

$mpdf->WriteHTML($css,1);
$mpdf->WriteHTML($html);
$mpdf->SetFooter('
<table width="100%" id="rodaperelatorio"><tr><td></td></tr></table>
<table width="100%">
    <tr>
        <td width="33%">Emissão: {DATE d/m/Y H:i:s}</td>
        <td width="33%" align="center"></td>
        <td width="33%" style="text-align: right;">{PAGENO}/{nbpg}</td>
    </tr>
</table>');
$mpdf->Output();

//unset($_SESSION['tramitacaoProtocolo']);
?>

