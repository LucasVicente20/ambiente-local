<?php
/**
* Portal de Compras
* Programa: PostDadosBloqueioLiberar.php
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
    case 'getOrgao':
        $orgaosUsuario = $objPlanejamento->getOrgaoConsultar();
        $_SESSION['AreaReqUsuario'] = $orgaosUsuario;
        $countOrgao = (!is_null($orgaosUsuario) && !empty($orgaosUsuario)) ? count($orgaosUsuario) : 0;

        $htmlAreaReq = "";
        $htmlAreaReq .= "<option value=''>Selecione a Área Requisitante...</option>";
        foreach ($orgaosUsuario as $orgao) {
            $htmlAreaReq .= "<option value='".$orgao->corglicodi."'>".$orgao->eorglidesc."</option>";
        }
        print_r(json_encode(array("status"=>true, "options"=>$htmlAreaReq)));
        // print($htmlAreaReq);

    break;
    case 'salvarPeriodo':
        $erroMsg = "";
        //PROCESSO DE VALIDAÇÃO Simples
        if(empty($_POST['selectAnoPCA'])){
            $erroMsg .= "Ano do PCA, ";
        }else{
            $dadosDFDPeriodo["APLLIBAPCA"] = $objPlanejamento->anti_injection($_POST['selectAnoPCA']);
        }

        if(empty($_POST['selectAreaReq'])){
            $erroMsg .= "Área requisitante, ";
        }else{
            $dadosDFDPeriodo["CORGLICODI"] = $_POST['selectAreaReq'];
        }

        if(empty($_POST['DataIni']) && empty($_POST['DataFim'])){
            $dadosDFDPeriodo['DPLLIBDINI'] = "null";
            $dadosDFDPeriodo['DPLLIBDFIM'] = "null";
        }else{
            //organizar a data
            if(!empty($_POST['DataIni'])){
                $dtperiodoIni  = explode("/", $_POST['DataIni']);
                $dplDFDPerIni = mktime(00,00,00, $dtperiodoIni[1], $dtperiodoIni[0], $dtperiodoIni[2]);
                $dadosDFDPeriodo['DPLLIBDINI'] = "'".date("Y-m-d", $dplDFDPerIni)." 00:00:00'";
            }else{
                $dadosDFDPeriodo['DPLLIBDINI'] = "null";
            }
            //organizar a data
            if(!empty($_POST['DataFim'])){
                $dtperiodoFim  = explode("/", $_POST['DataFim']);
                $dplDFDPerFim = mktime(00,00,00, $dtperiodoFim[1], $dtperiodoFim[0], $dtperiodoFim[2]);
                $dadosDFDPeriodo['DPLLIBDFIM'] = "'".date("Y-m-d", $dplDFDPerFim)." 00:00:00'";
            }else{
                $dadosDFDPeriodo['DPLLIBDFIM'] = "null";
            }
            
            // if(($dtperiodoIni[2] != $dadosDFDPeriodo["APLBLOAPCA"] && $dadosDFDPeriodo['DPLLIBDINI'] != "null") || ($dtperiodoFim[2] != $dadosDFDPeriodo["APLBLOAPCA"] && $dadosDFDPeriodo['DPLLIBDFIM'] != "null")){
            //     $erroMsg .= "Período de Liberação precisa ser no Ano do PCA, ";
            // }

            if(!is_null($dplDFDPerIni) && !is_null($dplDFDPerFim)){
                if(strtotime($dadosDFDPeriodo['DPLLIBDFIM']) > $dadosDFDPeriodo['DPLLIBDINI']){
                    $erroMsg .= "O Fim do Período de Liberação é menor do que o Início do Período de Liberação, ";
                }
            }

        }
        // if(empty($_POST['DataIni']) && empty($_POST['DataFim'])){
        //     $erroMsg .= "Período de Liberação, ";
        // }else{
        //     if(!empty($_POST['DataIni']) && empty($_POST['DataFim'])){
        //         $erroMsg .= "Fim do período de Liberação, ";
        //     }
        //     if(empty($_POST['DataIni']) && !empty($_POST['DataFim'])){
        //         $erroMsg .= "Início do período de Liberação, ";
        //     }
        //     if(!empty($_POST['DataIni']) && !empty($_POST['DataFim'])){
        //         //organizar a data
        //         $dtperiodoIni  = explode("/", $_POST['DataIni']);
        //         $dplDFDPerIni = mktime(00,00,00, $dtperiodoIni[1], $dtperiodoIni[0], $dtperiodoIni[2]);
        //         $dadosDFDPeriodo['DPLLIBDINI'] = "'".date("Y-m-d", $dplDFDPerIni)." 00:00:00'";
                
        //         $dtperiodoFim  = explode("/", $_POST['DataFim']);
        //         $dplDFDPerFim = mktime(00,00,00, $dtperiodoFim[1], $dtperiodoFim[0], $dtperiodoFim[2]);
        //         $dadosDFDPeriodo['DPLLIBDFIM'] = "'".date("Y-m-d", $dplDFDPerFim)." 00:00:00'";
                
        //         if($dtperiodoIni[2] != $dadosDFDPeriodo["APLLIBAPCA"] && $dtperiodoFim[2] != $dadosDFDPeriodo["APLLIBAPCA"]){
        //             $erroMsg .= "Período de Liberação precisa ser no Ano do PCA, ";
        //         }else{
        //             if($dtperiodoIni[2] != $dadosDFDPeriodo["APLLIBAPCA"]){
        //                 $erroMsg .= "Início do Período de Liberação precisa ser no Ano do PCA, ";
        //             }
        //             if($dtperiodoFim[2] != $dadosDFDPeriodo["APLLIBAPCA"]){
        //                 $erroMsg .= "Fim do Período de Liberação precisa ser no Ano do PCA, ";
        //             }
        //         }
        //     }
        // }

        if($erroMsg != ""){
            $informe = substr_replace($erroMsg, ".", strrpos($erroMsg, ", "));
            print_r(json_encode(array("status"=>false, "msm"=>"Informe: ".$informe)));
            exit;
        }

        $validaLibExistente = $objPlanejamento->existeLiberacao($dadosDFDPeriodo);
        $dadosDFDPeriodo['cpllibsequ'] = $validaLibExistente->cpllibsequ;
        if(!is_null($validaLibExistente)){
            $objPlanejamento->updateLibPeriodo($dadosDFDPeriodo);
        }else{
            $objPlanejamento->IserirLibPeriodo($dadosDFDPeriodo);
        }
        if($dadosDFDPeriodo['DPLLIBDINI'] == "null" && $dadosDFDPeriodo['DPLLIBDFIM'] == "null"){
            print_r(json_encode(array("status"=>true, "msm"=>"Período de Liberação removido com sucesso!")));
        }else{
            print_r(json_encode(array("status"=>true, "msm"=>"Período Liberado com sucesso!")));
        }
    break;
    case "buscaLiberacao":
        //O jquery checa se os dois valores foram informados, por tanto, é possível garantir que não precisa validação extra
        $dadosBloqueio = $objPlanejamento->SelectLiberacPeriodo($_POST['selectAnoPCA'], $_POST['selectAreaReq']);
        if(!is_null($dadosBloqueio->dpllibdini)){
            $dtperiodoIni  = explode("-", $dadosBloqueio->dpllibdini);
            $dplDFDPerIni = mktime(00,00,00, $dtperiodoIni[1], $dtperiodoIni[2], $dtperiodoIni[0]);
            $dataIni = date("d/m/Y", $dplDFDPerIni);
        }else{
            $dataIni="";
        }
        // reorganizar data
        
        if(!is_null($dadosBloqueio->dpllibdfim)){
            $dtperiodoFim  = explode("-", $dadosBloqueio->dpllibdfim);
            $dplDFDPerFim = mktime(00,00,00, $dtperiodoFim[1], $dtperiodoFim[2], $dtperiodoFim[0]);
            $dataFim = date("d/m/Y", $dplDFDPerFim);
        }else{
            $dataFim="";
        }
        
        if(!is_null($dadosBloqueio)){
            print_r(json_encode(array("status"=>true, "dataIni"=>$dataIni, "dataFim"=>$dataFim)));
        }else{
            print_r(json_encode(array("status"=>false)));
        }
    break;
}
?>
