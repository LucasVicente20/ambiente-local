<?php
/**
* Portal de Compras
* Programa: PostDadosBloqueioIncluir.php
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
    // case "anosDFD":
    //     $anos = $objPlanejamento->getAnosCadastrados();
    //     $html = '';  
    //     foreach($anos as $ano){
    //         if($_POST["selectAnoPCA"] == $ano){
    //             $html .=    '<option value="'.$ano->apldfdanod.'" selected>'.$ano->apldfdanod.'</option>';
    //         }else{
    //             $html .=    '<option value="'.$ano->apldfdanod.'">'.$ano->apldfdanod.'</option>';
    //         }
    //     }
    //     print_r($html);
    // break; 
    case 'salvarPeriodo':
        $erroMsg = "";
        //PROCESSO DE VALIDAÇÃO Simples
        if(empty($_POST['selectAnoPCA'])){
            $erroMsg .= "Ano do PCA, ";
        }else{
            $dadosDFDPeriodo["APLBLOAPCA"] = $objPlanejamento->anti_injection($_POST['selectAnoPCA']);
        }

        if(empty($_POST['DataIni']) && empty($_POST['DataFim'])){
            $dadosDFDPeriodo['DPLBLODINI'] = "null";
            $dadosDFDPeriodo['DPLBLODFIM'] = "null";
        }else{
            // if(!empty($_POST['DataIni']) && empty($_POST['DataFim'])){
            //     $erroMsg .= "Fim do período de Bloqueio, ";
            // }
            // if(empty($_POST['DataIni']) && !empty($_POST['DataFim'])){
            //     $erroMsg .= "Início do período de Bloqueio, ";
            // }
            // if(!empty($_POST['DataIni']) && !empty($_POST['DataFim'])){
                //organizar a data
                if(!empty($_POST['DataIni'])){
                    $dtperiodoIni  = explode("/", $_POST['DataIni']);
                    $dplDFDPerIni = mktime(00,00,00, $dtperiodoIni[1], $dtperiodoIni[0], $dtperiodoIni[2]);
                    $dadosDFDPeriodo['DPLBLODINI'] = "'".date("Y-m-d", $dplDFDPerIni)." 00:00:00'";
                }else{
                    $dadosDFDPeriodo['DPLBLODINI'] = "null";
                }
                //organizar a data
                if(!empty($_POST['DataFim'])){
                    $dtperiodoFim  = explode("/", $_POST['DataFim']);
                    $dplDFDPerFim = mktime(00,00,00, $dtperiodoFim[1], $dtperiodoFim[0], $dtperiodoFim[2]);
                    $dadosDFDPeriodo['DPLBLODFIM'] = "'".date("Y-m-d", $dplDFDPerFim)." 00:00:00'";
                }else{
                    $dadosDFDPeriodo['DPLBLODFIM'] = "null";
                }
                
                // if(($dtperiodoIni[2] != $dadosDFDPeriodo["APLBLOAPCA"] && $dadosDFDPeriodo['DPLBLODINI'] != "null") || ($dtperiodoFim[2] != $dadosDFDPeriodo["APLBLOAPCA"] && $dadosDFDPeriodo['DPLBLODFIM'] != "null")){
                //     $erroMsg .= "Período de Bloqueio precisa ser no Ano do PCA, ";
                // }
                // else{
                //     if($dtperiodoIni[2] != $dadosDFDPeriodo["APLBLOAPCA"]){
                //         $erroMsg .= "Início do Período de Bloqueio precisa ser no Ano do PCA, ";
                //     }
                //     if($dtperiodoFim[2] != $dadosDFDPeriodo["APLBLOAPCA"]){
                //         $erroMsg .= "Fim do Período de Bloqueio precisa ser no Ano do PCA, ";
                //     }
                // }
            // }
        }

        if($erroMsg != ""){
            $informe = substr_replace($erroMsg, ".", strrpos($erroMsg, ", "));
            print_r(json_encode(array("status"=>false, "msm"=>"Informe: ".$informe)));
            exit;
        }
        $validaBloqueioExistente = $objPlanejamento->existeBloqueio($dadosDFDPeriodo);
        $dadosDFDPeriodo['CPLBLOSEQU'] = $validaBloqueioExistente->cplblosequ;
        if(!is_null($validaBloqueioExistente)){
            $objPlanejamento->updateBloqPeriodo($dadosDFDPeriodo);
        }else{
            $objPlanejamento->IserirBloqPeriodo($dadosDFDPeriodo);
        }

        if($dadosDFDPeriodo['DPLBLODINI'] != "null" && $dadosDFDPeriodo['DPLBLODFIM'] != "null"){
            print_r(json_encode(array("status"=>true, "msm"=>"Período Bloqueado com sucesso")));
        }else{
            print_r(json_encode(array("status"=>true, "msm"=>"Período não informado, não há bloqueio cadastrado para o Ano do PCA informado.")));
        }
    break;
    case "buscaBloqueio":
        //No javascript eu garanti que postDados s[o seja ativado se algum valor for detectado, por tatno não vai vir nulo ou vazio
        $dadosBloqueio = $objPlanejamento->SelectBloqPeriodo($_POST['selectAnoPCA']);
        // reorganizar data
        if(!is_null($dadosBloqueio->dplblodini)){
            $dtperiodoIni  = explode("-", $dadosBloqueio->dplblodini);
            $dplDFDPerIni = mktime(00,00,00, $dtperiodoIni[1], $dtperiodoIni[2], $dtperiodoIni[0]);
            $dataIni = date("d/m/Y", $dplDFDPerIni);
        }else{
            $dataIni = "";
        }
        if(!is_null($dadosBloqueio->dplblodfim)){
            $dtperiodoFim  = explode("-", $dadosBloqueio->dplblodfim);
            $dplDFDPerFim = mktime(00,00,00, $dtperiodoFim[1], $dtperiodoFim[2], $dtperiodoFim[0]);
            $dataFim = date("d/m/Y", $dplDFDPerFim);
        }else{
            $dataFim = "";
        }
        
        if(!is_null($dadosBloqueio)){
            print_r(json_encode(array("status"=>true, "dataIni"=>$dataIni, "dataFim"=>$dataFim)));
        }else{
            print_r(json_encode(array("status"=>false)));
        }
    break;
}
?>
