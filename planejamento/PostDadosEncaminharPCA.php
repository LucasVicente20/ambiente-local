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
        unset($_SESSION['item']);
        unset($_SESSION['cnpjMultiplos']);
        unset($_SESSION['KeepOrgaoSelect']);
        unset($_SESSION['classe']);
        unset($_SESSION["cclamscodi"]);
        unset($_SESSION["cgrumscodi"]);
        unset($_SESSION["classeSelecionadada"]);
        print_r(json_encode(array("status"=>true)));
    break;
    case "anosDFD":
        $anos = $objPlanejamento->getAnosCadastrados();
        $html = '<option value="">Selecione o Ano do PCA...</option>';  
        foreach($anos as $ano){
            $html .=    '<option value="'.$ano->apldfdanod.'">'.$ano->apldfdanod.'</option>';
        }
        // $html .= '</select>';

        print_r($html);
    break;
    case 'getDadosDFD':
        //Sessão de coleta dos dados,
        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($_POST['selectAreaReq']);
        $dadosPesquisa["selectAnoPCA"]          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
        $dadosPesquisa["selectSitDFD"]          = $objPlanejamento->anti_injection($_POST["selectSitDFD"]);
        $dadosPesquisa["grauPrioridade"]        = $objPlanejamento->anti_injection($_POST["grauPrioridade"]);
        $dadosPesquisa["descDemanda"]           = $objPlanejamento->anti_injection($_POST["descDemanda"]);
        $dadosPesquisa["DataIni"]               = $objPlanejamento->anti_injection($_POST["DataIni"]);
        $dadosPesquisa["DataFim"]               = $objPlanejamento->anti_injection($_POST["DataFim"]);
        $dadosPesquisa["cclamscodi"]            = $objPlanejamento->anti_injection($_SESSION["cclamscodi"]);
        $dadosPesquisa["cgrumscodi"]            = $objPlanejamento->anti_injection($_SESSION["cgrumscodi"]);

        if(empty($dadosPesquisa['selectAreaReq'])){
            $dadosPesquisa['selectAreaReq'] = $_SESSION['AreaReqUsuario'];
        }
        // Como nenhum campo é obrigatório, a validação é direta.
        if (!empty($dadosPesquisa["DataIni"])) {
            $dataIni  = explode("/", $dadosPesquisa['DataIni']);
            $dadosPesquisa["DataIni"] = date("Y-m-d", mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]));
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = date("Y-m-d", mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]));
        }

        $listaDadosDFD = $objPlanejamento->getDadosDFDConsulta($dadosPesquisa);
        $htmlResultado = $objPlanejamento->montaHTMLConsulta($listaDadosDFD);
        $tudoOk = true;

        $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
        print_r($objJS);

    break;
    case 'encaminharPCA':
        //VALIDA CAMPOS OBRIGATÓRIOS
        $erroMsg = "";
        $AnoPCA = $_POST['selectAnoPCA'];
        if(empty($AnoPCA)){
            $erroMsg .= "Ano PCA";
        }
        $arquivo = $objPlanejamento->checaDocumentoAnexoDOCePDF();
        if ($arquivo == false) {
            $erroMsg .= ", $arquivo.";
        }   
        //VERIFICA SE TEM ALGO A REPORTAR SENÃO, PASSA PARA O TRATAMENTO
        if($erroMsg != ""){
            $objJS = json_encode(array("status"=>false, "msm"=>"Informe:".$erroMsg));
            print_r($objJS);
        }
        //SE TUDO ESTIVER OK, VAI INSERIR O ARQUIVO NO DEVIDO LOCAL.
        $path = $GLOBALS["CAMINHO_UPLOADS"]."temp/";
        $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['arquivoAnexo']['name'];
        $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
        $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
        $_SESSION['Arquivos_Upload']['codigo'][] = ''; // local onde o arquivo vai ser salvo no ftp
        $_SESSION['Arquivos_Upload']['path'][] = $path.$_FILES['arquivoAnexo']['name'];
        move_uploaded_file($_FILES['arquivoAnexo']['tmp_name'], $path.$_FILES['arquivoAnexo']['name']);
        //Em qul pasta deve salvar definitivamente?
    break;   
}
?>
