<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelGerencialTramitacaoPdf.php
# Objetivo: Programa para impressão do relatório Gerencial de Tramitação 
# Autor:    Ernesto Ferreira
# Data:     05/12/2018
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "../common/mpdf60/mpdf.php";

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

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
	$DataEntradaIni    = $_GET['DataEntradaIni'];
	$DataEntradaFim    = $_GET['DataEntradaFim'];



}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();


# Informa o Título do Relatório #
$TituloRelatorio = "Relatório Gerencial de Tramitação";


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
						<td align='center' class='titulo'>".$GLOBALS['TituloRelatorio']."</td>
					</tr>
				</table>
				<br>
				";

$html .= $cabecalho;




$htmlCabecalhoItem .= "<table width='100%' class='campos_pesquisa'>";
# Pega os dados do almoxarifado #
//$db   = Conexao();

$buscar = $_SESSION['buscar'];

$dados = relatorioGerencialTramitacao($buscar, 'relGerencial');
$arrBase = getModalidadesRelGerencialTramitacao($buscar, '');
$arrAcoes = getAcoesRelGerencial(null, $buscar);







$htmlAcoesTit = '';
$totalAcoes = 4;
foreach($arrAcoes as $objAcao) {

	$htmlAcoesTit .= '<tr><td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">'.$objAcao[0].'</td></tr>';

	$totalAcoes++;
}


if(!empty($arrBase)) { 
	

			

			foreach($arrBase as $value) {
				$mediaRealizado = 0;
				$mediaPrevisto = 0;
				$htmlAcoesDados = '';
				$htmlItem = "";
  
				$htmlCabecalhoItem .= '
				<tr>
					<td rowspan="2" class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">MODALIDADE</td>
					<td colspan="4" class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">MÉDIA EM DIAS POR AÇÃO</td>
				</tr>
				<tr>';
	
				$htmlCabecalhoItem .=  '  
					<td class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">PRAZO<br>PREVISTO</td>
					<td class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">PRAZO<br>REALIZADO</td>
					<td class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">ATRASO</td>
	
				</tr>';

				foreach($arrAcoes as $objAcao) {

					$arrMedia = getMediaDiasAcao($objAcao[1], $value[1], $dados);

				   
					if(!is_int($arrMedia[0])){
						$media = number_format($arrMedia[0], 2, ',', '');
					}else{
						$media = $arrMedia[0];
					}
					
					$mediaRealizado = $mediaRealizado + $arrMedia[0];
					$mediaPrevisto = $mediaPrevisto + $arrMedia[1];

					$htmlAcoesDados.= '<tr><td>'.$objAcao[0].'</td><td colspan="3" align="center">'.$media.'</td></tr>';

				}  


				$htmlItem .= '<tr><td align="center">'.$value[0].'</a></td>';

				$htmlItem .= '<td align="center">'.$mediaPrevisto.'</td>
					<td align="center">';

				if(!is_int($mediaRealizado)){
					$htmlItem .= number_format($mediaRealizado, 2, ',', '');
				}else{
					$htmlItem .= $mediaRealizado ;
				}

				$htmlItem .= '</td>';
				$htmlItem .= '<td align="center">';
				
				$atraso = $mediaRealizado - $mediaPrevisto;
				if($atraso > 0){
					if(!is_int($atraso)){
						$htmlItem .= number_format($atraso, 2, ',', '');
					}else{
						$htmlItem .= $atraso ;
					}
				}else{
					$htmlItem .= '0';
				}

				$htmlItem .= '</td></tr>';

				$htmlCabecalhoItem .= $htmlItem;

				$htmlCabecalhoItem .= '<tr><td class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">AÇÃO</td>';
				$htmlCabecalhoItem .= '<td colspan="3" class="titulo3" bgcolor="#D3D3D3" class="textonormal" align="center">MÉDIA</a></td>';

				$htmlCabecalhoItem .= $htmlAcoesDados;





				
			 } 
			 
 			}

$htmlCabecalhoItem .= "</table>";

$html .= $htmlCabecalhoItem;

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
?>
