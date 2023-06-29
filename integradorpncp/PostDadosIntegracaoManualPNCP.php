<?php
/**
* Portal de Compras
* Programa: PostDadosIntegracaoManualPNCP.php
* Autor: José Rodrigo
* Data: 25/01/2022
* Objetivo: Integração Manual do PNCP
* Tarefa Redmine: #277661
*/

# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
require_once "ClassPlanejamento.php";

$objPlanejamento = new Planejamento();

    switch ($_POST['op']) {
        case 'getSistemaOrigem':

            $listaSistemaOrigem = $objPlanejamento->getSistemaOrigem();
            
            if (isset($listaSistemaOrigem) && (!empty($listaSistemaOrigem))) {
                $htmlSistemaOrigem = "<select id='selectSitOrigem' required name='selectSitOrigem' size='1' style='width:340px; font-size: 10.6667px;'>";
                $htmlSistemaOrigem .= "<option value=''>Escolha o sistema de origem</option>";
                $htmlSistemaOrigem .= "<option value='TD'>Todas</option>";

                foreach ($listaSistemaOrigem as $lista) {
                    $htmlSistemaOrigem .= "<option value='$lista->sistema'>" . $lista->sistema . "</option>";
                }
                print($htmlSistemaOrigem);
            }
            break;
    case 'excutarIntegracao':
        
        $cnpj =  $_POST['cnpj'];
        $selectSitOrigem = $_POST['selectSitOrigem'];
        $servico = $_POST['servico'];
        $tipoOperacao = $_POST['tipoOperacao'];
        $statusProcessamento = $_POST['statusProcessamento'];
        $dataInicial = $_POST['dataInicial'];
        $dataFim = $_POST['dataFim'];
        $justificativa = $_POST['justificativa'];

        $dataInicial  = explode("/", $dataInicial);
        $dataInicial = mktime(00,00,00, $dataInicial[1], $dataInicial[0], $dataInicial[2]);
        
        $dataFim  = explode("/", $dataFim);
        $dataFim = mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]);

        $dados = [
            'cnpj' =>  $cnpj,
            'selectSitOrigem' =>  $selectSitOrigem,
            'servico' => $servico,
            'tipoOperacao' => $tipoOperacao,
            'statusProcessamento' => $statusProcessamento,
            'dataInicial' => !empty($dataInicial)?"'".date("Y-m-d",$dataInicial)." 00:00:00'":"null",
            'dataFim'   => !empty($dataFim)?"'".date("Y-m-d",$dataFim)." 00:00:00'":"null",
            'justificativa' => $justificativa
        ];
       
        $insert = $objPlanejamento->insertIntegracaoManualPNCP($dados);

        if($insert['erro'] == true){
            print_r(json_encode(array("status"=>404, "msm"=>$validação['informe'])));
            exit;
        }

        if($insert == true){
            print_r(json_encode(array("status"=>200)));
            $_SESSION['MensagemFinal'] = "Integração Solicitada com Sucesso!";
            exit;
        }else{
            print_r(json_encode(array("status"=>400)));
        }
        
    }
?>