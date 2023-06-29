<?php
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
require_once 'ClassReport.php';
require_once 'ClassReportServicos.php';

$objetoReport = new Report();
$objetoReportServico = new ReportServicos();
switch ($_REQUEST['action']) {
    case 'carregaDados':
        $dados = $objetoReport->DadosReportPuro();
        $dadosAglutinado = array();
        $i = 0;
         foreach($dados as $d)
         {
            if(!empty($d->anoprocesso) &&  !empty($d->numataitem) &&   !empty($d->numataseq) &&  !empty($d->numataseq) )
            {
                            $dadosParticipantes = $objetoReport->DadosReportParticipante($d->numataseq,$d->codigomaterial, $d->citarpitel);
                            foreach($dadosParticipantes as $p){
                                $dadosAglutinado[$i]['orgaolicitante'] = $d->orgaolicitante;
                                $dadosAglutinado[$i]['numeroprocesso'] = $d->numeroprocesso;
                                $dadosAglutinado[$i]['anoprocesso'] = $d->anoprocesso;
                                $dadosAglutinado[$i]['comissaolicitacao'] = $d->comissaolicitacao;
                                $dadosAglutinado[$i]['codigomaterial'] = $d->codigomaterial;
                                $dadosAglutinado[$i]['descricaoresumida'] = $d->descricaoresumida;
                                $dadosAglutinado[$i]['especificacao'] = $d->especificacao;
                                $dadosAglutinado[$i]['descricaodetalhada'] = $d->descricaodetalhada;
                                $dadosAglutinado[$i]['unidade'] = $d->unidade;
                                $dadosAglutinado[$i]['grupo'] = $d->grupo;
                                $dadosAglutinado[$i]['subgrupo'] = $d->subgrupo;
                                $dadosAglutinado[$i]['numero_ata'] = $d->numataa;
                                $dadosAglutinado[$i]['quantidade'] = $d->quantidade;
                                $dadosAglutinado[$i]['valorunitario'] = $d->valorunitario;
                                $dadosAglutinado[$i]['valortotal'] = $d->valortotal;
                                $dadosAglutinado[$i]['numataseq'] = $d->numataseq;
                                $dadosAglutinado[$i]['itemata'] = $p->itemata;
                                $dadosAglutinado[$i]['orgaoparticipante'] = $p->orgaoparticipante;
                                $dadosAglutinado[$i]['orgaoata'] = $p->orgaoata;
                                $dadosAglutinado[$i]['somaparticipante'] = $p->somaparticipante;
                                $i++;
                            }
                            $i++;
                             $dadosCarona = $objetoReport->DadosReportCarona($d->numataseq,$d->codigomaterial, $d->citarpitel);
                            if(!empty($dadosCarona)){
                                foreach($dadosCarona as $c){
                                    $dadosAglutinado[$i]['orgaolicitante'] = $d->orgaolicitante;
                                    $dadosAglutinado[$i]['numeroprocesso'] = $d->numeroprocesso;
                                    $dadosAglutinado[$i]['anoprocesso'] = $d->anoprocesso;
                                    $dadosAglutinado[$i]['comissaolicitacao'] = $d->comissaolicitacao;
                                    $dadosAglutinado[$i]['codigomaterial'] = $d->codigomaterial;
                                    $dadosAglutinado[$i]['descricaoresumida'] = $d->descricaoresumida;
                                    $dadosAglutinado[$i]['especificacao'] = $d->especificacao;
                                    $dadosAglutinado[$i]['descricaodetalhada'] = $d->descricaodetalhada;
                                    $dadosAglutinado[$i]['unidade'] = $d->unidade;
                                    $dadosAglutinado[$i]['grupo'] = $d->grupo;
                                    $dadosAglutinado[$i]['subgrupo'] = $d->subgrupo;
                                    $dadosAglutinado[$i]['numero_ata'] = $d->numataa;
                                    $dadosAglutinado[$i]['numataseq'] = $d->numataseq;
                                    $dadosAglutinado[$i]['quantidade'] = $d->quantidade;
                                    $dadosAglutinado[$i]['valorunitario'] = $d->valorunitario;
                                    $dadosAglutinado[$i]['valortotal'] = $d->valortotal;
                                    $dadosAglutinado[$i]['itemata'] = $c->itemata;
                                    $dadosAglutinado[$i]['orgaocarona'] = $c->orgaocarona;
                                    $dadosAglutinado[$i]['orgaoatac'] = $c->orgaoata;
                                    $dadosAglutinado[$i]['somacarona'] = $c->somacarona;
                                    $i++;
                                }
                            }
             
            }
        }
        
        //var_dump($dadosAglutinado);
        $_SESSION['dados_exelm'] = $dadosAglutinado;
        break;
    case "excelm":
        $fields = array(
                         strtoupper2('orgaolicitante'),
                         strtoupper2('numeroprocesso'),
                         strtoupper2('anoprocesso'),
                         strtoupper2('comissaolicitacao'),
                         strtoupper2('descricaoresumida'),
                         strtoupper2('especificacao'),
                         strtoupper2('descricaodetalhada'),
                         strtoupper2('unidade'),
                         strtoupper2('grupo'),
                         strtoupper2('subgrupo'),
                         strtoupper2('numero_ata'),
                         strtoupper2('Codigo ata'),
                         strtoupper2('quantidade'),
                         strtoupper2('valorunitario'),
                         strtoupper2('valortotal'),
                         strtoupper2('N. item Ata'),
                         strtoupper2('codigomaterial'),
                         strtoupper2('orgaoparticipante'),
                         strtoupper2('orgao descricao'),
                         strtoupper2('somaparticipante'),
                         strtoupper2('orgaocarona'),
                         strtoupper2('orgao descricao'),
                         strtoupper2('somacarona'),

                        //  strtoupper2('numerocontrato'),
                        //  strtoupper2('numeroprocesso'),
                        //  strtoupper2('numprocessomodalidade'),
                        //  strtoupper2('orgaolicitante'),
                        //  strtoupper2('orgaoparticipante'),
                        //  strtoupper2('orgaoprocesso'),
                        //  strtoupper2('orgaoCarona'),
                        //  strtoupper2('quantidade'),
                        //  strtoupper2('somacarona'),
                        //  strtoupper2('somaparticipante'),
                        //  strtoupper2('subgrupo'),
                        //  strtoupper2('unidade'),
                        //  strtoupper2('valortotal'),
                        //  strtoupper2('valorunitario')
                        );
        $excelData = implode("\t", array_values($fields)) . "\n";
        $somaCarona = 0;
        $somaParticipante = 0;
        foreach($_SESSION['dados_exelm'] as $d){
            $lineData = array(
                                $d['orgaolicitante'],
                                $d['numeroprocesso'],
                                $d['anoprocesso'],
                                $d['comissaolicitacao'],
                                $d['descricaoresumida'],
                                $d['especificacao'],
                                $d['descricaodetalhada'],
                                $d['unidade'],
                                $d['grupo'],
                                $d['subgrupo'],
                                $d['numero_ata'],
                                $d['numataseq'],
                                $d['quantidade'],
                                $d['valorunitario'],
                                $d['valortotal'],
                                $d['itemata'],
                                $d['codigomaterial'],
                                $d['orgaoparticipante'],
                                $d['orgaoata'],
                                floatval($d['somaparticipante']),
                                $d['orgaocarona'],
                                $d['orgaoatac'],
                                floatval($d['somacarona']),
                                // $d['numataseq'],
                                
                                // $d['numerocontrato'],
                                // $d['numeroprocesso'],
                                // $d['numprocessomodalidade'],
                                // $d['orgaolicitante'],
                                // $d['orgaoparticipante'],
                                // $d['orgaoprocesso'],
                                // $d['orgaocarona'],
                                // $d['quantidade'],
                                // $d['somacarona'],
                                // $d['somaparticipante'],
                                // $d['subgrupo'],
                                // $d['unidade'],
                                // $d['valortotal'],
                                // $d['valorunitario']
                            );
                            $somaCarona += floatval($d['somacarona']);
                            $somaParticipante += floatval($d['somaparticipante']);
            array_walk($lineData, 'filterData'); 
            $excelData .= implode("\t", array_values($lineData)) . "\n"; 
        }
        //$linhaTotal = array('','','','','','','','','','','','','','','','','Total Participante',$somaParticipante,'Total Carona',$somaCarona);
        //$excelData .= implode("\t", array_values($linhaTotal)) . "\n";
        unset($_SESSION['dados_exelm']);
        // Headers for download 
        header("Content-Type: application/vnd.ms-excel"); 
        header("Content-Disposition: attachment; filename=Report.xls"); 
        echo $excelData;
        break;
    case 'carregaDadosServico':
        $dados = $objetoReportServico->DadosReportPuroServico();
        $dadosAglutinado = array();
        $i = 0;
         foreach($dados as $d)
         {
            if(!empty($d->numataseq) &&  !empty($d->codigoservico) )
            {
                            $dadosParticipantes = $objetoReportServico->DadosReportParticipante($d->numataseq,$d->codigoservico,$d->citarpitel);
                            foreach($dadosParticipantes as $p){
                                echo "Entrou no for p";
                                $dadosAglutinado[$i]['orgaolicitante'] = $d->orgaolicitante;
                                $dadosAglutinado[$i]['numeroprocesso'] = $d->numeroprocesso;
                                $dadosAglutinado[$i]['anoprocesso'] = $d->anoprocesso;
                                $dadosAglutinado[$i]['comissaolicitacao'] = $d->comissaolicitacao;
                                $dadosAglutinado[$i]['codigoservico'] = $d->codigoservico;
                                $dadosAglutinado[$i]['descricaoresumida'] = $d->descricaoresumida;
                                $dadosAglutinado[$i]['especificacao'] = $d->especificacao;
                                $dadosAglutinado[$i]['descricaodetalhada'] = $d->descricaodetalhada;
                                $dadosAglutinado[$i]['unidade'] = $d->unidade;
                                $dadosAglutinado[$i]['grupo'] = $d->grupo;
                                $dadosAglutinado[$i]['subgrupo'] = $d->subgrupo;
                                $dadosAglutinado[$i]['numero_ata'] = $d->numataa;
                                $dadosAglutinado[$i]['quantidade'] = $d->quantidade;
                                $dadosAglutinado[$i]['valorunitario'] = $d->valorunitario;
                                $dadosAglutinado[$i]['valortotal'] = $d->valortotal;
                                $dadosAglutinado[$i]['numataseq'] = $d->numataseq;
                                $dadosAglutinado[$i]['itemata'] = $p->itemata;
                                $dadosAglutinado[$i]['orgaoparticipante'] = $p->orgaoparticipante;
                                $dadosAglutinado[$i]['orgaoata'] = $p->orgaoata;
                                $dadosAglutinado[$i]['somaparticipante'] = $p->somaparticipante;
                                $i++;
                            }
                            $i++;
                            $dadosCarona = $objetoReportServico->DadosReportCarona($d->numataseq,$d->codigoservico, $d->citarpitel);
                            if(!empty($dadosCarona)){
                                foreach($dadosCarona as $c){
                                    echo "Entrou no for c";
                                    $dadosAglutinado[$i]['orgaolicitante'] = $d->orgaolicitante;
                                    $dadosAglutinado[$i]['numeroprocesso'] = $d->numeroprocesso;
                                    $dadosAglutinado[$i]['anoprocesso'] = $d->anoprocesso;
                                    $dadosAglutinado[$i]['comissaolicitacao'] = $d->comissaolicitacao;
                                    $dadosAglutinado[$i]['codigoservico'] = $d->codigoservico;
                                    $dadosAglutinado[$i]['descricaoresumida'] = $d->descricaoresumida;
                                    $dadosAglutinado[$i]['especificacao'] = $d->especificacao;
                                    $dadosAglutinado[$i]['descricaodetalhada'] = $d->descricaodetalhada;
                                    $dadosAglutinado[$i]['unidade'] = $d->unidade;
                                    $dadosAglutinado[$i]['grupo'] = $d->grupo;
                                    $dadosAglutinado[$i]['subgrupo'] = $d->subgrupo;
                                    $dadosAglutinado[$i]['numero_ata'] = $d->numataa;
                                    $dadosAglutinado[$i]['numataseq'] = $d->numataseq;
                                    $dadosAglutinado[$i]['quantidade'] = $d->quantidade;
                                    $dadosAglutinado[$i]['valorunitario'] = $d->valorunitario;
                                    $dadosAglutinado[$i]['valortotal'] = $d->valortotal;
                                    $dadosAglutinado[$i]['itemata'] = $c->itemata;                                    
                                    $dadosAglutinado[$i]['orgaocarona'] = $c->orgaocarona;
                                    $dadosAglutinado[$i]['orgaoatac'] = $c->orgaoata;
                                    $dadosAglutinado[$i]['somacarona'] = $c->somacarona;
                                    $i++;
                                }
                            }
            }
        }
        $_SESSION['dados_exelv'] = $dadosAglutinado;
        //print(json_encode($dadosAglutinado));
        break;
    case "excelv":
        $fields = array(
                            strtoupper2('orgaolicitante'),
                            strtoupper2('numeroprocesso'),
                            strtoupper2('anoprocesso'),
                            strtoupper2('comissaolicitacao'),
                            strtoupper2('descricaoresumida'),
                            strtoupper2('especificacao'),
                            strtoupper2('descricaodetalhada'),
                            strtoupper2('unidade'),
                            strtoupper2('grupo'),
                            strtoupper2('subgrupo'),
                            strtoupper2('numero_ata'),
                            strtoupper2('Codigo ata'),
                            strtoupper2('quantidade'),
                            strtoupper2('valorunitario'),
                            strtoupper2('valortotal'),
                            strtoupper2('N. item Ata'),
                            strtoupper2('codigoservico'),
                            strtoupper2('orgaoparticipante'),
                            strtoupper2('orgao descricao'),
                            strtoupper2('somaparticipante'),
                            strtoupper2('orgaocarona'),
                            strtoupper2('orgao descricao'),
                            strtoupper2('somacarona'),

                        );
        $excelData = implode("\t", array_values($fields)) . "\n";
        $somaCarona = 0;
        $somaParticipante = 0;
        foreach($_SESSION['dados_exelv'] as $d){
            $lineData = array(
                                $d['orgaolicitante'],
                                $d['numeroprocesso'],
                                $d['anoprocesso'],
                                $d['comissaolicitacao'],
                                $d['descricaoresumida'],
                                $d['especificacao'],
                                $d['descricaodetalhada'],
                                $d['unidade'],
                                $d['grupo'],
                                $d['subgrupo'],
                                $d['numero_ata'],
                                $d['numataseq'],
                                $d['quantidade'],
                                $d['valorunitario'],
                                $d['valortotal'],
                                $d['itemata'],
                                $d['codigoservico'],
                                $d['orgaoparticipante'],
                                $d['orgaoata'],
                                floatval($d['somaparticipante']),
                                $d['orgaocarona'],
                                $d['orgaoatac'],
                                floatval($d['somacarona']),
                            );
                            $somaCarona += floatval($d['somacarona']);
                            $somaParticipante += floatval($d['somaparticipante']);
            array_walk($lineData, 'filterData'); 
            $excelData .= implode("\t", array_values($lineData)) . "\n"; 
        }
        // Headers for download 
        header("Content-Type: application/vnd.ms-excel"); 
        header("Content-Disposition: attachment; filename=Report.xls"); 
        echo $excelData;
        break;
}


function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

function trataDados($dadosAglutinadoCarona, $dadosAglutinadoParticipante){
    $novoArray = array();
    $i = 0;
    foreach($dadosAglutinadoParticipante as $p) {
            $novoArray[$i]['orgaolicitante'] = $p["orgaolicitante"];
            $novoArray[$i]['numeroprocesso'] = $p["numeroprocesso"];
            $novoArray[$i]['anoprocesso'] = $p["anoprocesso"];
            $novoArray[$i]['comissaolicitacao'] = $p["comissaolicitacao"];
            $novoArray[$i]['codigoservico'] = $p["codigoservico"];
            $novoArray[$i]['descricaoresumida'] = $p["descricaoresumida"];
            $novoArray[$i]['especificacao'] = $p["especificacao"];
            $novoArray[$i]['descricaodetalhada'] = $p["descricaodetalhada"];
            $novoArray[$i]['unidade'] = $p["unidade"];
            $novoArray[$i]['grupo'] = $p["grupo"];
            $novoArray[$i]['subgrupo'] = $p["subgrupo"];
            $novoArray[$i]['numero_ata'] = $p["numero_ata"];
            $novoArray[$i]['numataseq'] = $p["numataseq"];
            $novoArray[$i]['quantidade'] = $p["quantidade"];
            $novoArray[$i]['valorunitario'] = $p["valorunitario"];
            $novoArray[$i]['valortotal'] = $p["valortotal"];
            $novoArray[$i]['orgaoparticipante'] = $p["orgaoparticipante"];
            $novoArray[$i]['somaparticipante'] = $p["somaparticipante"];
            $novoArray[$i]['quantidade'] = $p["quantidade"];
            $novoArray[$i]['orgaocarona'] = $p["orgaocarona"];
            $novoArray[$i]['somacarona'] = $p["somacarona"];
            $i++;
        foreach($dadosAglutinadoCarona as $c){
            if($p["codigoservico"] ==  $c["codigoservico"])
            {
                $novoArray[$i]['orgaolicitante'] = $c["orgaolicitante"];
                $novoArray[$i]['numeroprocesso'] = $c["numeroprocesso"];
                $novoArray[$i]['anoprocesso'] = $c["anoprocesso"];
                $novoArray[$i]['comissaolicitacao'] = $c["comissaolicitacao"];
                $novoArray[$i]['codigoservico'] = $c["codigoservico"];
                $novoArray[$i]['descricaoresumida'] = $c["descricaoresumida"];
                $novoArray[$i]['especificacao'] = $c["especificacao"];
                $novoArray[$i]['descricaodetalhada'] = $c["descricaodetalhada"];
                $novoArray[$i]['unidade'] = $c["unidade"];
                $novoArray[$i]['grupo'] = $c["grupo"];
                $novoArray[$i]['subgrupo'] = $c["subgrupo"];
                $novoArray[$i]['numero_ata'] = $c["numero_ata"];
                $novoArray[$i]['numataseq'] = $c["numataseq"];
                $novoArray[$i]['quantidade'] = $c["quantidade"];
                $novoArray[$i]['valorunitario'] = $c["valorunitario"];
                $novoArray[$i]['valortotal'] = $c["valortotal"];
                $novoArray[$i]['quantidade'] = $c["quantidade"];
                $novoArray[$i]['orgaocarona'] = $c["orgaocarona"];
                $novoArray[$i]['somacarona'] = $c["somacarona"];
                $i++;
            }
        }
    }
   return $novoArray;
}

