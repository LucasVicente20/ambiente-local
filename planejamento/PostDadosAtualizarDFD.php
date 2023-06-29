<?php
/**
 * Portal de Compras
 * Programa: PostDadosAtualizarDFD.php
 * Autor: Diógenes Dantas
 * Data: 03/01/2023
 * Objetivo: Programa para Atualizar DFD
 * Tarefa Redmine: CR277065
 * -------------------------------------------------------------------
 * Alterado:
 * Data:
 * Tarefa:
 * -------------------------------------------------------------------
 */

# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
require_once "ClassPlanejamento.php";

$objPlanejamento = new Planejamento();

switch ($_POST['op']) {
    case 'getOrgao':
        $orgaosUsuario = $objPlanejamento->getOrgao();
        $countOrgao = (!is_null($orgaosUsuario) && !empty($orgaosUsuario)) ? count($orgaosUsuario) : 0;

        if ($countOrgao > 1) {
            $htmlAreaReq = "<select id='selectAreaReq' name='selectAreaReq' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlAreaReq .= "<option value=''>Selecione a Área Requisitante</option>";
            foreach ($orgaosUsuario as $orgao) {
                $htmlAreaReq .= "<option value='".$orgao->corglicodi."'>".$orgao->eorglidesc."</option>";
            }

        } else {
            $htmlAreaReq = "<span id='SpanAreaReqDesc' style='font-size: 10.6667px;'>".$orgaosUsuario[0]->eorglidesc."</span>";
            $htmlAreaReq .= "<input type='hidden' name='AreaReqCod' id='AreaReqCod' value='".$orgaosUsuario[0]->corglicodi."'>";
            $htmlAreaReq .= "<input type='hidden' name='AreaReqDesc' id='AreaReqDesc' value='".$orgaosUsuario[0]->eorglidesc."'>";
        }
        print($htmlAreaReq);

        break;

    case 'getSituacaoDFD':
        $listaSituacaoDFD = $objPlanejamento->getSituacaoDFD();

        if (isset($listaSituacaoDFD) && (!empty($listaSituacaoDFD))) {
            $htmlSitDFD = "<select id='selectSitDFD' name='selectSitDFD' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlSitDFD .= "<option value=''>Escolha a situação</option>";

            foreach ($listaSituacaoDFD as $lista) {
                $htmlSitDFD .= "<option value='".$lista->cplsitcodi."'>".$lista->eplsitnome."</option>";
            }
            print($htmlSitDFD);
        }
        break;

    case "anosDFD":
        $anos = $objPlanejamento->getAnosCadastrados();
        $html = '<select name="selectAnoPCA" id="selectAnoPCA" style="width:160px;">
                    <option value="">Selecione o ano do PCA</option>';     
        foreach($anos as $ano){
            $html .=    '<option value="'.$ano->apldfdanod.'">'.$ano->apldfdanod.'</option>';
        }
        $html .= '</select>';

        print_r($html);
    break;

    case 'modalPesqClasse':
        $html  = '<div class="modal-title textonormal" >';
        $html .= 'PESQUISAR - CLASSE MATERIAL/SERVIÇO ';
        $html .= '<span class="btn-fecha-modal close" >[ X ]</span>';
        $html .= '</div>';
        $html .= '<div class="modal-body">';
        $html .= '<form action="" method="post" name="CadIncluirDFD">';
        $html .= '<table class="textonormal" width="100%">';
        $html .= '<tr border=1>';
        $html .= '<tr>';
        $html .= '<td align="left" colspan="2" id="tdmensagemM">';
        $html .= '<div class="mensagemM">';
        $html .= '<div class="error" colspan="5">';
        $html .= 'Erro';
        $html .= '</div>';
        $html .= '<span id="mensagemErroModal" class="mensagem-textoM">';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="textonormal" bgcolor="#DCEDF7" width="31%">Classe</td>';
        $html .= '<td class="textonormal" colspan="5">';
        $html .= '<select id="OpcaoPesquisaClasse" name="OpcaoPesquisaClasse" class="textonormal">';
        $html .= '<option value="0">Código Reduzido</option>';
        $html .= '<option value="1">Descrição contendo</option>';
        $html .= '<option value="2">Descrição iniciada por</option>';
        $html .= '</select>';
        $html .= '<input type="text" id="ClasseDescricaoDireta" name="ClasseDescricaoDireta" value="" size="10" maxlength="10" class="textonormal">';
        $html .= '<img id="lupaClasse" src="../midia/lupa.gif" border="0">';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="textonormal" bgcolor="#DCEDF7">Material</td>';
        $html .= '<td class="textonormal" colspan="5">';
        $html .= '<select id="OpcaoPesquisaMaterial" name="OpcaoPesquisaMaterial" class="textonormal">';
        $html .= '<option value="0">Código Reduzido</option>';
        $html .= '<option value="1">Descrição contendo</option>';
        $html .= '<option value="2">Descrição iniciada por</option>';
        $html .= '</select>';
        $html .= '<input type="text" id="MaterialDescricaoDireta" name="MaterialDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .= '<img id="lupaMaterial" src="../midia/lupa.gif" border="0">';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>';
        $html .= '<td class="textonormal" colspan="2">';
        $html .= '<select id="OpcaoPesquisaServico" name="OpcaoPesquisaServico" class="textonormal">';
        $html .= '<option value="0">Código Reduzido</option>';
        $html .= '<option value="1">Descrição contendo</option>';
        $html .= '<option value="2">Descrição iniciada por</option>';
        $html .= '</select>';
        $html .= '<input type="text" id="ServicoDescricaoDireta" name="ServicoDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .= '<img id="lupaServico" src="../midia/lupa.gif" border="0" alt="0">';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</tr>';
        $html .= '<tr><table width="100%" bordercolor="#75ADE6" border="1" cellspacing="0"><tbody><tr> <td colspan="3">';
        $html .= ' <div><input class="btn-fecha-modal botao"  name="botao_voltar" value="Voltar" type="button" style="float:right">';
        // $html .= '<input  type="button" name="IncluirClasse" id="btnIncluirClasse" value="Incluir Classe" style="float:right" title="Incluir Classe" class="botao">';
        $html .= '</div> </td></tr></tbody></table> </tr>';
        $html .= '<tr> <td colspan="3" style="border:none;"> ';
        $html .= ' <!-- loading -->';
        $html .= ' <div class="load" id="LoadPesqScc" style="display:none;">';
        $html .= ' <div class="load-content" >';
        $html .= ' <img src="../midia/loading.gif" alt="Carregando">';
        $html .= ' <spam>Carregando...</spam>';
        $html .= ' </div>';
        $html .= ' </div>';
        $html .= ' <!-- Fim do loading -->';
        $html .= ' <div id="pesqDivModal"> ';
        $html .= ' </div> </td> </tr>';
        $html .= '</table> </form> </div>';
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

        // Como nenhum campo é obrigatório, a validação é direta.
        if (!empty($dadosPesquisa["DataIni"])) {
            $dataIni  = explode("/", $dadosPesquisa['DataIni']);
            $dadosPesquisa["DataIni"] = mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]);
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]);
        }

        $listaDadosDFD = $objPlanejamento->getDadosAtualizarDFD($dadosPesquisa);
        $htmlResultado = $objPlanejamento->montaHTMLAtualizar($listaDadosDFD);

        $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
        print_r($objJS);

        break;

    case 'ManterDFD':
        $seqDFD = $_SESSION['DFD'];
        $listaDadosDFD = $objPlanejamento->updateManterDFD($seqDFD->cpldfdsequ);

        $objJS = json_encode(array("status"=>true, "msm"=>'DFD atualizado para o status "ATUALIZADO NO ANO DE EXECUÇÃO"!'));
        print_r($objJS);
        break;

    case 'ExcluirDFD':
        $seqDFD = $_SESSION['DFD'];
        $listaDadosDFD = $objPlanejamento->updateExcluirDFD($seqDFD->cpldfdsequ);

        $objJS = json_encode(array("status"=>true, "msm"=>'DFD atualizado para o status "EXCLUÍDO NO ANO DE EXECUÇÃO"!'));
        print_r($objJS);
        break;

    case 'ExcluirDFDResultado':
        foreach ($_POST['numDFD'] as $excluirDFD) {
            $listaDadosDFD = $objPlanejamento->updateExcluirDFD($excluirDFD);
        }

        $objJS = json_encode(array("status"=>true, "msm"=>'DFD atualizado para o status "EXCLUÍDO NO ANO DE EXECUÇÃO"!'));
        print_r($objJS);
        break;

    case 'getDadosExportarDFD':
        unset($_SESSION['Export']);
        //Sessão de coleta dos dados,
        $formatoExport                          = $_POST['formatoExport']; // Identifica o formato
        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($_POST['selectAreaReq']);
        $dadosPesquisa["selectAnoPCA"]          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
        $dadosPesquisa["selectSitDFD"]          = $objPlanejamento->anti_injection($_POST["selectSitDFD"]);
        $dadosPesquisa["grauPrioridade"]        = $objPlanejamento->anti_injection($_POST["grauPrioridade"]);
        $dadosPesquisa["descDemanda"]           = $objPlanejamento->anti_injection($_POST["descDemanda"]);
        $dadosPesquisa["DataIni"]               = $objPlanejamento->anti_injection($_POST["DataIni"]);
        $dadosPesquisa["DataFim"]               = $objPlanejamento->anti_injection($_POST["DataFim"]);

        // Como nenhum campo é obrigatório, a validação é direta.
        if (!empty($dadosPesquisa["DataIni"])) {
            $dataIni  = explode("/", $dadosPesquisa['DataIni']);
            $dadosPesquisa["DataIni"] = mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]);
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]);
        }

        $listaDadosDFD = $objPlanejamento->getDadosAtualizarDFD($dadosPesquisa);

        if($formatoExport == "pdf"){
            $html = $objPlanejamento->montaHTMLAnalisarPDF($listaDadosDFD);

            $_SESSION['HTMLPDF'] = $html;
            $_SESSION['HTMLPDFDownload'] = false;
            $_SESSION['HTMLPDFMudaOrientacao'] = true;
            $objJS = json_encode(array("status"=>true));
            print_r($objJS);exit;
        }
        if($formatoExport == "xls" || $formatoExport == "csv"){

            $nomeArquivo = "RelatorioDFDConsultar";
            $cabecalho = array(
                "Número do DFD",
                "Ano do PCA",
                "Código da Classe",
                "Descrição da Classe",
                "Data Prevista para Conclusão",
                "Tipo de Processo",
                "Grau de Prioridade",
                "Situação do DFD"
            );

            $dados = $listaDadosDFD;

            $resultados = array();
            $conta = 0;
            foreach($dados as $dado){
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                $tpProcesso = ($dado->fpldfdtpct=="D") ? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                if($dado->fpldfdgrau == 1){
                    $grauprioridade = "ALTO";
                }else if($dado->fpldfdgrau == 2){
                    $grauprioridade = "MÉDIO";
                }else if($dado->fpldfdgrau == 3){
                    $grauprioridade = "BAIXO";
                }

                $resultados[$conta][] = $dado->cpldfdnumf;
                $resultados[$conta][] = $dado->apldfdanod;
                $resultados[$conta][] = $dado->cclamscodi;
                $resultados[$conta][] = $dado->descclasse;
                $resultados[$conta][] = $dataPrevConclusao;
                $resultados[$conta][] = $tpProcesso;
                $resultados[$conta][] = $grauprioridade;
                $resultados[$conta][] = $dado->eplsitnome;

                $conta++;
            }
            $_SESSION['Export']['nomeArquivo']  = $nomeArquivo;
            $_SESSION['Export']['resultados']   = $resultados;
            $_SESSION['Export']['cabecalho']    = $cabecalho;
            $_SESSION['Export']['formatoExport']    = $formatoExport;
            $_SESSION['Export']['gerar']        = true;
            $objJS = json_encode(array("status"=>true));
            print_r($objJS);exit;
        }
        break;
}
?>
