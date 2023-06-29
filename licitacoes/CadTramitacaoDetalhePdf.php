<?php
# --------------------------------------------------------------------------------
# Portal de Compras
# Programa: CadTramitacaoDetalhePdf.php
# Alterado: Caio Coutinho
# Data:		25/07/2018
# Objetivo:	Tarefa Redmine 199435
# --------------------------------------------------------------------------------

// Sempre vai buscar o programa no servidor #
header("Expires: 0");
header("Cache-Control: private");

// Executa o controle de segurança #
session_cache_limiter('private');
session_start();

include "./funcoesTramitacao.php";

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Sequencial   = $_GET['protsequ'];
    $numprotocolo = $_GET['numprotocolo'];
    $anoprotocolo = $_GET['anoprotocolo'];
}

$protocolo  = getProtocoloDetalhe($Sequencial);
$processo   = getProcesso($Sequencial, $numprotocolo, $anoprotocolo);
$passos     = getTramitacaoPassos($Sequencial);
if($protocolo[12]){
    $nnumeroScc = getNumeroSolicitacaoCompra($db, $protocolo[12]);
}else{
    $nnumeroScc = '';
}
// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

// Informa o Título do Relatório #
$TituloRelatorio = "DETALHAMENTO DA TRAMITAÇÃO";

// Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("L", "mm", "A4");

// Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

// Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

// Adiciona uma página no documento #
$pdf->AddPage();

// Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 9);



$pdf->ln(05);
$pdf->Cell(93, 5, "NÚMERO DO PROTOCOLO", 1, 0, 'L', 1);
$pdf->Cell(187, 5, str_pad($protocolo[1], 4, "0", STR_PAD_LEFT)."/". $protocolo[5], 1, 0, 'L', 0);
$pdf->ln(05);
$pdf->Cell(93, 5, 'ÓRGÃO DEMANDANTE', 1, 0, 'L', 1);
$pdf->Cell(187, 5, $protocolo[2], 1, 0, 'L', 0);
$pdf->ln(05);
$h2 = $pdf->GetStringHeight(187, 5, $protocolo[4], "L");
$pdf->Cell(93,$h2, 'OBJETO', 1, 0, 'L', 1);
$pdf->MultiCell(187,5,$protocolo[4],1,'L',0);

//$pdf->ln(05);
$pdf->Cell(93, 5, 'NÚMERO CI', 1, 0, 'L', 1);
$pdf->Cell(187, 5, $protocolo[6], 1, 0, 'L', 0);
$pdf->ln(05);
$pdf->Cell(93, 5, 'NÚMERO OFÍCIO', 1, 0, 'L', 1);
$pdf->Cell(187, 5, $protocolo[7], 1, 0, 'L', 0);
$pdf->ln(05);
$pdf->Cell(93, 5, 'NÚMERO DA SCC', 1, 0, 'L', 1);
$pdf->Cell(187, 5, $nnumeroScc, 1, 0, 'L', 0);
$pdf->ln(05);




if(!empty($protocolo[12])){ 
    //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
    
    $arrProcesso = getProcessoScc($protocolo[12]);


    if(!empty($arrProcesso)){
        $arrProcesso = $arrProcesso[0];
    }

    //var_dump($arrProcesso);

    if(!empty($arrProcesso[0])) {
        $processo = str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5];
    }

}else{
    //verifica se existe processo cadastrado 
    if(!empty($protocolo[8])) {
        $processo = str_pad($protocolo[8], 4, "0", STR_PAD_LEFT) . '/' . $protocolo[9]. ' - '. $protocolo[11];

    }

}


if($processo){
    $pdf->Cell(93, 5, 'PROCESSO LICITATÓRIO', 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $processo, 1, 0, 'L', 0);
    $pdf->ln(05);
}else{
    $pdf->Cell(93, 5, 'PROCESSO LICITATÓRIO', 1, 0, 'L', 1);
    $pdf->Cell(187, 5, '', 1, 0, 'L', 0);
    $pdf->ln(05);  
}




if($protocolo[18]){
    $pdf->Cell(93, 5, 'MONITORAMENTO', 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $protocolo[18], 1, 0, 'L', 0);
    $pdf->ln(05);
}
//linha divisória
$pdf->Cell((187+93), 5, 'PASSOS DA TRAMITAÇÃO', 1, 0, 'C', 1);
$pdf->ln(5);

if(!empty($passos)) {
    $tamPassos = count($passos);
    foreach ($passos as $key => $value) {
        

        $pdf->Cell(280, 5, $tamPassos.'º PASSO', 1, 0, 'L', 0);
        $tamPassos--;
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'AGENTE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, $value[0], 1, 0, 'L', 0);
        $pdf->ln(05);

        $pdf->Cell(93, 5, 'USUÁRIO RESPONSÁVEL', 1, 0, 'L', 1);

        if($value[17]=='S'){
            
            if($value[8] <= 0 ){
                $pdf->Cell(187, 5, $value[0], 1, 0, 'L', 0);
            }else{
                $pdf->Cell(187, 5, $value[1], 1, 0, 'L', 0);
            }
        }else{
            if($value[8]=='0'){
                if($value[9]=='I'){
                    $pdf->Cell(187, 5, $value[0], 1, 0, 'L', 0);
                }else{
                    $pdf->Cell(187, 5, 'ÓRGÃO EXTERNO', 1, 0, 'L', 0);
                }
                
            }else{
                $pdf->Cell(187, 5, $value[1], 1, 0, 'L', 0);
            }
        }
        
        $pdf->ln(05);

        $pdf->Cell(93, 5, 'AÇÃO', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, $value[2], 1, 0, 'L', 0);
        $pdf->ln(05);

        if($value[13]){ 

            $pdf->Cell(93, 5, 'ENCAMINHADO PARA A COMISSÃO', 1, 0, 'L', 1);
            $pdf->Cell(187, 5, $value[14], 1, 0, 'L', 0);
            $pdf->ln(05);

        }

        $pdf->Cell(93, 5, 'DATA DA ENTRADA', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, substr($value[3],8,2).'/'.substr($value[3],5,2).'/'.substr($value[3],0,4), 1, 0, 'L', 0);
        $pdf->ln(05);

            $now = date('Y-m-d');
            $saida = substr($value[5],0,10);

            $arrEntrada = explode("-",substr($value[3],0,10));
            $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];

            $previsto = calcularTramitacaoSaida($dataHoraEntrada, $value[4]);
            $arrPrevisto = explode("/",$previsto);
            $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        
            
        $pdf->Cell(93, 5, 'PRAZO EM DIAS', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, $value[4], 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'PREVISTO', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, $previsto, 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'REALIZADO', 1, 0, 'L', 1);
        if($value[5]){
            $pdf->Cell(187, 5, substr($value[5],8,2).'/'.substr($value[5],5,2).'/'.substr($value[5],0,4), 1, 0, 'L', 0);
        }else{
            $pdf->Cell(187, 5, '', 1, 0, 'L', 0);
        }
        $pdf->ln(05);


        if($saida){   
            if(strtotime($saida) > strtotime($dataPrevista)) { 

                //$diffDias = calculaDias2($dataPrevista, $saida);
                $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $saida);

                $dia = ($diffDias > 1) ? ' dias' : ' dia';

                $pdf->Cell(93, 5, 'ATRASO', 1, 0, 'L', 1);
                $pdf->SetFont("Arial","B",9);
                $pdf->Cell(187, 5, $diffDias.$dia, 1, 0, 'L', 0);
                $pdf->SetFont("Arial","",9);
                $pdf->ln(05);
            }
        }else{
            $atual = date('Y-m-d');
            if(strtotime($atual) > strtotime($dataPrevista)) { 

                //$diffDias = calculaDias2($dataPrevista, $atual); 
                $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $atual);

                $dia = ($diffDias > 1) ? ' dias' : ' dia';

                
                $pdf->Cell(93, 5, 'ATRASO', 1, 0, 'L', 1);
                $pdf->SetFont("Arial","B",9);
                $pdf->Cell(187, 5, $diffDias.$dia, 1, 0, 'L', 0);
                $pdf->SetFont("Arial","",9);
                $pdf->ln(05);
            }        
        }



        $observacao = $value[6];
        $h0 = $pdf->GetStringHeight(187, 5, trim(str_ireplace($breaks, "\r\n", $observacao)), "L");
        if ($h0 < 5) {
            $h0 = 5;
        }
        $pdf->Cell(93, $h0, 'OBSERVAÇÃO', 1, 0, 'L', 1);
        $pdf->MultiCell(187, 5, $observacao, 1, "L", 0);
    }
}
$pdf->Output();
