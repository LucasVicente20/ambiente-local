<?php
/**
* Portal de Compras
* Programa: PostDadosGerarPCA.php
* Autor: João Madson
* Data: 03/02/2023
* Objetivo: Programa para GerarPCA
* -------------------------------------------------------------------
*/

# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
require_once "ClassPlanejamento.php";


$objPlanejamento = new Planejamento();
switch ($_POST['op']) {
    case 'limpar':
        unset($_SESSION['Export']);
        unset($_SESSION['HTMLPDF']);
        unset($_SESSION['HTMLPDFNome']);
        unset($_SESSION['HTMLPDFDownload']);
        unset($_SESSION['HTMLPDFMudaOrientacao']);
        unset($_POST);
        print_r(json_encode(array("status"=>true)));
    break;
    case "anosDFD":
        $anos = $objPlanejamento->getAnosCadastrados();
        $html = '<option value="">Selecione o Ano do PCA...</option>';  
        foreach($anos as $ano){
            if($_POST["selectAnoPCA"] == $ano){
                $html .=    '<option value="'.$ano->apldfdanod.'" selected>'.$ano->apldfdanod.'</option>';
            }else{
                $html .=    '<option value="'.$ano->apldfdanod.'">'.$ano->apldfdanod.'</option>';
            }
        }
        // $html .= '</select>';

        print_r($html);
    break;
    case 'geraPCA':
        //Limpa os dados de importação
        unset($_SESSION['Export']);
        
        //Mensagem da validação
        $mensagem = "";

        if(empty($_POST["selectAnoPCA"])){
            $mensagem .= "Ano do PCA, ";
        }else{
            $dadosPesquisa["AnoPCA"] = $_POST["selectAnoPCA"];
        }
        if(empty($_POST["tipoPCA"])){
            $mensagem .= "Tipo do PCA, ";
        }
        if($_POST["tipoPCA"] != "C"){
            if(empty($_POST["autoridade"])){
                $mensagem .= "Nome da Autoridade Competente, ";
            }
            if(empty($_POST["cargo"])){
                $mensagem .= "Cargo da Autoridade Competente, ";
            }
        }
        //Caso haja algo faltando a mensagem vai ser montada e reportada, o processo é terminado
        if($mensagem != ""){
            // $mensagem = substr_replace($mensagem,".",strrpos($mensagem, ", "));
            $informe = "Informe: ".substr_replace($mensagem,".",strrpos($mensagem, ", "));
            print_r(json_encode(array("status"=>false, "msm"=>$informe)));exit;
        }


        if($_POST["tipoPCA"] == "C"){    
            //Caso tudo esteja okay a busca é feita
            $listaDadosDFD = $objPlanejamento->getDadosDFDGeraPCACompleto($dadosPesquisa["AnoPCA"]); // Pesqeuisa os DFDS no ANO do PCA
            if(empty($listaDadosDFD)){
                $objJS = json_encode(array("status"=>false, "msm"=>"O PCA não pode ser gerado, pois não há DFDs com situação “Consolidado, Aprovado ou Em Execução” para o período desejado."));
                print_r($objJS);exit;
            }
            $nomeArquivo = "PCACompleto ".$dadosPesquisa["AnoPCA"];
            $cabecalho = array(
                "Ano do PCA",
                "Número do DFD",
                "Área Requisitante",
                "CNPJ",
                "Código da classe",
                "Descrição da classe",
                "Estimativa de valor",
                "Justificativa da necessidade de contratação",
                "Data prevista para conclusão",
                "Tipo de processo",
                "Grau de prioridade",
                "DFD Agrupado", // (“Sim” ou “Não”);
                "Justificativa de prioridade alta", // (caso o grau de prioridade seja “alto”);
                "Compra corporativa", // (“Sim” ou “Não”);
                "Grupo de Despesa", //(Custeio ou Investimento); Para obter essa informação, deverá ser verificado o elemento de despesa ao qual a classe do item está vinculada (3 - Custeio; 4 - Investimentos).
                "Tipo", // (material ou serviço)
                "Situação do DFD",
                "Vinculação com outro DFD",
                "Itens"
            );

            $resultados = array();
            $conta = 0;
            foreach($listaDadosDFD as $dado){
                //Buscar vinculação com outro DFD
                if(!empty($dado->cplvincodi)){
                    $vinculo = $objPlanejamento->DFDSVinculadasGERAPCA($dado->cplvincodi);
                }else{
                    $vinculo = "";
                }
                //Buscar itens do DFD
                $itens = $objPlanejamento->DFDSItensGERAPCA($dado->cpldfdsequ);

                //Busca Despesa
                $despesa = $objPlanejamento->DFDGeraPCADespesa($dadosPesquisa["AnoPCA"], $dado->cgrumscodi);

                //Busca TIPO 
                $tipoMS = $objPlanejamento->DFDGeraPCATipoMS($dado->cgrumscodi);

                if(!empty($dado->dpldfdpret)){
                    $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                }else{
                    $dataPrevConclusao = "";
                }

                if(!empty($dado->fpldfdtpct)){
                    $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                }else{
                    $tpProcesso = "";
                }
                
                if(!empty($dado->fpldfdgrau)){
                    if($dado->fpldfdgrau == 1){
                        $grauprioridade = "ALTO";
                    }else if($dado->fpldfdgrau == 2){
                        $grauprioridade = "MÉDIO";
                    }else if($dado->fpldfdgrau == 3){
                        $grauprioridade = "BAIXO";
                    }
                }else{
                    $grauprioridade = "";
                }

                $agrupada = ($dado->fpldfdagru == "S")? "SIM":"NÃO";
                $corporativa = ($dado->fpldfdcorp == "S")? "SIM":"NÃO";
                
                $cnpj = $objPlanejamento->MascarasCPFCNPJ($dado->aorglicnpj);

                $valorFormatado = converte_valor_licitacao($dado->cpldfdvest);

                
                $resultados[$conta][] = $dadosPesquisa["AnoPCA"];
                $resultados[$conta][] = $dado->cpldfdnumf;
                $resultados[$conta][] = $dado->descorgao;
                $resultados[$conta][] = $cnpj;
                $resultados[$conta][] = $dado->cclamscodi;
                $resultados[$conta][] = $dado->descclasse;
                $resultados[$conta][] = $valorFormatado;
                $resultados[$conta][] = $dado->epldfdjust;
                $resultados[$conta][] = $dataPrevConclusao;
                $resultados[$conta][] = $tpProcesso;
                $resultados[$conta][] = $grauprioridade;
                $resultados[$conta][] = $agrupada;
                $resultados[$conta][] = $dado->epldfdjusp;
                $resultados[$conta][] = $corporativa;
                $resultados[$conta][] = $despesa;
                $resultados[$conta][] = $tipoMS;
                $resultados[$conta][] = $dado->eplsitnome;
                $resultados[$conta][] = $vinculo;
                $resultados[$conta][] = $itens;

                $conta++;
            }
            
            $_SESSION['Export']['nomeArquivo']  = $nomeArquivo;
            $_SESSION['Export']['resultados']   = $resultados;
            $_SESSION['Export']['cabecalho']    = $cabecalho;
            $_SESSION['Export']['formatoExport']    = "csv";
            $_SESSION['Export']['gerar']        = true;

            //Salva dados do Novo PCA gerado
            $objPlanejamento->insertPCAGerado($dadosPesquisa["AnoPCA"]);

            // O procedimento de  download deve ser feito no CadGerarPCA.php pois não funciona no AJAX
            print_r(json_encode(array("status"=>true, "tipo"=> "csv")));exit;
        }
        
        
        if($_POST["tipoPCA"] == "A"){    
            //Caso tudo esteja okay a busca é feita
            $listaDadosDFD = $objPlanejamento->getDadosDFDGeraPCAAprovacao($dadosPesquisa["AnoPCA"]); // Pesqeuisa os DFDS no ANO do PCA
            if(empty($listaDadosDFD)){
                $objJS = json_encode(array("status"=>false, "msm"=>"O PCA não pode ser gerado, pois não há DFDs com situação “Consolidado, Aprovado ou Em Execução” para o período desejado."));
                print_r($objJS);exit;
            }
            $_SESSION['HTMLPDF'] = $objPlanejamento->montaHTMLPCAAprovacao($listaDadosDFD, $_POST["selectAnoPCA"], $_POST["autoridade"], $_POST["cargo"]);
            $_SESSION['HTMLPDFNome'] = "PCA ".$dadosPesquisa["AnoPCA"];
            $_SESSION['HTMLPDFDownload'] = false;
            $_SESSION['HTMLPDFMudaOrientacao'] = true;

            //Salva dados do Novo PCA gerado
            $objPlanejamento->insertPCAGerado($dadosPesquisa["AnoPCA"]);

            print_r(json_encode(array("status"=>true, "tipo"=> "pdf")));exit;
        }


        if($_POST["tipoPCA"] == "P"){ 
            //Caso tudo esteja okay a busca é feita
            $listaDadosDFD = $objPlanejamento->getDadosDFDGeraPCAPublicacao($dadosPesquisa["AnoPCA"]); // Pesqeuisa os DFDS no ANO do PCA
            if(empty($listaDadosDFD)){
                $objJS = json_encode(array("status"=>false, "msm"=>"O PCA não pode ser gerado, pois não há DFDs com situação “Consolidado, Aprovado ou Em Execução” para o período desejado."));
                print_r($objJS);exit;
            }   
            $htmlResultado = $objPlanejamento->montaHTMLPCAPublicacao($listaDadosDFD, $_POST["selectAnoPCA"], $_POST["autoridade"], $_POST["cargo"]);
            $html = $htmlResultado;
            $_SESSION['HTMLPDF'] = $html;
            $_SESSION['HTMLPDFNome'] = "PCA ".$dadosPesquisa["AnoPCA"];
            $_SESSION['HTMLPDFDownload'] = false;
            $_SESSION['HTMLPDFMudaOrientacao'] = true;

            //Salva dados do Novo PCA gerado
            $objPlanejamento->insertPCAGerado($dadosPesquisa["AnoPCA"]);

            print_r(json_encode(array("status"=>true, "tipo"=> "pdf")));exit;
        }

    break;
}
?>
