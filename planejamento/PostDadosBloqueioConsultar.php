<?php
/**
* Portal de Compras
* Programa: PostDadosBloqueioConsultar.php
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
        $anos = $objPlanejamento->getAnosCadastradosConsultaLiberacao();
        $html = '<option value="">Selecione o Ano do PCA...</option>';  
        foreach($anos as $ano){
            if($_POST["selectAnoPCA"] == $ano){
                $html .=    '<option value="'.$ano->apllibapca.'" selected>'.$ano->apllibapca.'</option>';
            }else{
                $html .=    '<option value="'.$ano->apllibapca.'">'.$ano->apllibapca.'</option>';
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
    case "pesquisaLib":
        $erroMsg = "";
        //PROCESSO DE VALIDAÇÃO Simples
        if(empty($_POST['selectAnoPCA'])){
            $erroMsg .= "Ano do PCA, ";
        }else{
            $dadosDFDPeriodo["APLLIBAPCA"] = $objPlanejamento->anti_injection($_POST['selectAnoPCA']);
        }

        if(empty($_POST['selectAreaReq'])){
            $dadosDFDPeriodo["CORGLICODI"] = null;
        }else{
            $dadosDFDPeriodo["CORGLICODI"] = $_POST['selectAreaReq'];
        }

        if($erroMsg != ""){
            $informe = substr_replace($erroMsg, ".", strrpos($erroMsg, ", "));
            print_r(json_encode(array("status"=>false, "msm"=>"Informe: ".$informe)));
            exit;
        }

        $liberacoes = $objPlanejamento->getDadosLiberacao($dadosDFDPeriodo["APLLIBAPCA"], $dadosDFDPeriodo["CORGLICODI"]);
        $html = $objPlanejamento->montaHTMLBloqConsulta($liberacoes);

        print_r(json_encode(array("status"=>true, "html"=>$html)));

    break;
}
?>
