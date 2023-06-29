<?php
 /**
 * Portal de Compras
 * Programa: PostDadosConsultaDFD.php
 * Autor: Diógenes Dantas
 * Data: 22/11/2022
 * Objetivo: Programa para pesquisar DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
  * Alterado: Osmar Celestino
  * Data: 20/12/2022
  * Tarefa: 276459
  * -------------------------------------------------------------------
  * Alterado: João Madson
  * Data: 05/01/2023
  * Tarefa: 276691
  * -------------------------------------------------------------------
  * Alterado: Lucas Vicente/João Madson
  * Data: 07/01/2023
  * Tarefa: 277315
  * -------------------------------------------------------------------
  * Alterado: João Madson   
  * Data: 09/01/2023
  * Tarefa: #277372
  * -------------------------------------------------------------------
  * Alterado: João Madson | Lucas Vicente  
  * Data: 16/01/2023
  * Tarefa: Relatório de correções Nº3 Incluir DFD
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
    case 'limparVinc':
        //Limpa a tela de JanelaVincular.php
        unset($_SESSION['classe']);
        unset($_SESSION["cclamscodi"]);
        unset($_SESSION["cgrumscodi"]);
        unset($_SESSION["classeSelecionadada"]);
        print_r(json_encode(array("status"=>true)));
    break;
    case 'getOrgao':
        $orgaosUsuario = $objPlanejamento->getOrgaoConsultar();
        $_SESSION['AreaReqUsuario'] = $orgaosUsuario;
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
    case 'getOrgaoDesc':
        $corglicodi = $_POST['codArea'];
        if(!empty($corglicodi)){
            $orgaosUsuario = $objPlanejamento->getOrgaoDesc($corglicodi);
        }
        $htmlAreaReq = "<span id='SpanAreaReqDesc' style='font-size: 10.6667px;'>".$orgaosUsuario->eorglidesc."</span>";
        $htmlAreaReq .= "<input type='hidden' name='AreaReqCod' id='AreaReqCod' value='".$corglicodi."'>";
        $htmlAreaReq .= "<input type='hidden' name='AreaReqDesc' id='AreaReqDesc' value='".$orgaosUsuario->eorglidesc."'>";

        print($htmlAreaReq);

    break;

    case 'getSituacaoDFDCons':
        $listaSituacaoDFD = $objPlanejamento->getSituacaoDFD();

        if (isset($listaSituacaoDFD) && (!empty($listaSituacaoDFD))) {
            $htmlSitDFD = "<select id='selectSitDFD' name='selectSitDFD' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlSitDFD .= "<option value=''>Escolha a Situação...</option>";

            foreach ($listaSituacaoDFD as $lista) {
                $htmlSitDFD .= "<option value='".$lista->cplsitcodi."'>".$lista->eplsitnome."</option>";
            }
            print($htmlSitDFD);
        }
        break;
    case 'getSituacaoDFD':
        $listaSituacaoDFD = $objPlanejamento->getSituacaoDFD();

        if (isset($listaSituacaoDFD) && (!empty($listaSituacaoDFD))) {
            $htmlSitDFD = "<select id='selectSitDFD' class='textonormal' name='selectSitDFD' size='1' style='width:auto; font-size: 10.6667px;'>";
            // $htmlSitDFD = "<select id='selectSitDFD' multiple size='5' name='selectSitDFD[]' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlSitDFD .= "<option value=''>Escolha a Situação...</option>";

            foreach ($listaSituacaoDFD as $lista) {
                if($lista->cplsitcodi != '1' and $lista->cplsitcodi != '5'){
                    $htmlSitDFD .= "<option value='".$lista->cplsitcodi."'>".$lista->eplsitnome."</option>";
                }
               
            }
            print($htmlSitDFD);
        }
        break;

    case "anosDFD":
        $anos = $objPlanejamento->getAnosCadastrados();
        $html = '<select name="selectAnoPCA" id="selectAnoPCA" style="width:160px;">';     
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
        $html .= '<form action="" method="post" name="ConsPesquisarDFD">';
        $html .= '<table class="textonormal" width="100%">';
        $html .= '<tr border=1>';
        $html .='<tr>';
        $html .='<td align="left" colspan="2" id="tdmensagemM">';
        $html .='<div class="mensagemM">';
        $html .='<div class="error" colspan="5">';
        $html .='Erro';
        $html .='</div>';
        $html .='<span id="mensagemErroModal" class="mensagem-textoM">';
        $html .='</span>';
        $html .='</div>';
        $html .='</td>';
        $html .='</tr>';    
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" width="31%">Classe</td>';
        $html .='<td class="textonormal" colspan="5">';
        $html .='<select id="OpcaoPesquisaClasse" name="OpcaoPesquisaClasse" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="ClasseDescricaoDireta" name="ClasseDescricaoDireta" value="" size="10" maxlength="10" class="textonormal">';
        $html .='<img id="lupaClasse" src="../midia/lupa.gif" border="0">';
        $html .='</td>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7">Material</td>';
        $html .='<td class="textonormal" colspan="5">';
        $html .='<select id="OpcaoPesquisaMaterial" name="OpcaoPesquisaMaterial" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="MaterialDescricaoDireta" name="MaterialDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .='<img id="lupaMaterial" src="../midia/lupa.gif" border="0">';
        $html .='</td>';
        $html .='</tr>';
		$html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>';
        $html .='<td class="textonormal" colspan="2">';
        $html .='<select id="OpcaoPesquisaServico" name="OpcaoPesquisaServico" class="textonormal">';
        $html .='<option value="0">Código Reduzido</option>';
        $html .='<option value="1">Descrição contendo</option>';
        $html .='<option value="2">Descrição iniciada por</option>';
        $html .='</select>';
        $html .='<input type="text" id="ServicoDescricaoDireta" name="ServicoDescricaoDireta" value="" size="10" maxlength="10" class="textonormal" >';
        $html .='<img id="lupaServico" src="../midia/lupa.gif" border="0" alt="0">';
        $html .='</td>';
        $html .='</tr>';
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
    case 'PesqClasse':
        $tipoDePesquisa  = $_POST["tipoPesq"];
        $opcaoDePesquisa = $_POST["opcaoPesq"];
        $formAction      = $_POST["action"];
        $dadoAPesquisar  = $objPlanejamento->anti_injection($_POST["dadoPesq"]);
        //validar o que foi informado
        if(empty($dadoAPesquisar)){
            //retorna mensagem de erro para a tela usando json
            print_r(json_encode(array("status"=>404, "msm"=>"O campo não deve pode vazio para realizar a pesquisa.")));
            exit;
        }
        // fim da validação

        //Chama a função de busca
        $resultadoPesquisa = $objPlanejamento->pesquisaClasseMatServ($tipoDePesquisa, $opcaoDePesquisa, $dadoAPesquisar);
        
        $_SESSION['classe'] = $resultadoPesquisa;
        //Montagem do resultado
        $tableHtml  = "<form method='post' action='$formAction' name='formModalPesqClasse' id='formModalPesqClasse'>";
        $tableHtml  .= "<table border='1' bordercolor='#75ADE6' width='100%' class='textonormal'>";
        $tableHtml  .="<thead>";
        $tableHtml  .="<tr>";
        $tableHtml  .='<td colspan="6" style="text-align: center; background-color: #75ADE6; font-weight: bold;" >';
        $tableHtml  .="RESULTADO DA PESQUISA";
        $tableHtml  .="</td>";
        $tableHtml  .="</tr>";
        if(!empty($resultadoPesquisa)){
            $tableHtml  .= '<tr style="background-color: #bfdaf2; text-align: center; font-weight: bold; color: #3165a5;">';
            $tableHtml  .= "<td></td>";
            $tableHtml  .= "<td>Classe</td>";
            $tableHtml  .= "<td>Descrição do material</td>";
            $tableHtml  .= "<td>Cód. reduzido </td>";
            $tableHtml  .= "<td>unidade </td>";
            $tableHtml  .= "</tr>";
            $tableHtml  .= "</thead>";
            $tableHtml  .= "<TBody>";
            $ordinal = 0;
            foreach($resultadoPesquisa as $dados){ 
                $tableHtml  .= "<tr>";
                $tableHtml  .= "<td><input type='radio' name='radioClasse' id='radioClasse' value='$ordinal'></td>";
                $tableHtml  .= "<td>$dados->eclamsdesc</td>";
                $tableHtml  .= "<td>$dados->ematepdesc</td>";
                if(!empty($dados->cmatepsequ)){
                    $tableHtml  .= "<td>$dados->cmatepsequ</td>";
                }else{
                    $tableHtml  .= "<td>$dados->cservpsequ</td>";    
                }
                $tableHtml  .= "<td>$dados->eunidmsigl </td>";
                $tableHtml  .= "</tr>";
                $ordinal++;
            }
            $tableHtml .="</TBody>";
            $tableHtml .="<tfoot>";
            $tableHtml .="<tr>";
            $tableHtml .="<td colspan='8'>";
            $tableHtml .='<button type="submit" value="Incluir Classe" name="incluirClasse" id="incluirClasse" class="botao" disabled style="float:right">Incluir Classe</button>';
            $tableHtml .="</td>";
            $tableHtml .="</tr>";
            $tableHtml .="</tfoot>";
        }else{
            $tableHtml .="<td colspan='8' style='border:none;'>";
            $tableHtml .='Pesquisa sem ocorrências.';
            $tableHtml .="</td>";
        }
        $tableHtml .="</table>";
        $tableHtml .="</form>";
        print_r(json_encode(array("status"=>200, "msm"=>$tableHtml)));
        exit;
    break;
    case "sessaoVincularDFD":
          $dfds = $objPlanejamento->getDadosDFDbySequ($_POST['sequencialVincularDFD']);
        if($_SESSION['vincularDFD']){
           $_SESSION['vincularDFD'] =  array_merge($_SESSION['vincularDFD'],$dfds);
        }else{
            $_SESSION['vincularDFD'] = $dfds;
        }
        $auxiliar = array_unique($_SESSION['vincularDFD'], SORT_REGULAR);
        $_SESSION['vincularDFD'] = $auxiliar;
        print_r(json_encode(array("status"=>true)));
    break;
    case "sessaoVincularDFDManter":
        $dfds = $objPlanejamento->getDadosDFDbySequ($_POST['sequencialVincularDFD']);
        if ($_SESSION['vincularDFD']){
            $dfds = $objPlanejamento->getDadosDFDbySequVincularManter($_POST['sequencialVincularDFD'], $_SESSION['vincularDFD']);
            $fusao = array_merge($_SESSION['vincularDFD'], $dfds);
            unset($_SESSION['vincularDFD']);
            $_SESSION['vincularDFD'] = $fusao;
        }else{
            $dfds = $objPlanejamento->getDadosDFDbySequVincularManter($_POST['sequencialVincularDFD'], $_SESSION['vincularDFD']);
            $_SESSION['vincularDFD'] = $dfds;
        }
        $_SESSION['vincularDFD'] = array_unique($_SESSION['vincularDFD'],SORT_REGULAR);
        print_r(json_encode(array("status"=>true)));
        break;
    case 'getDadosDFD':
        //Verifica se a busca é limitada a DFDs derivados de agrupamento.
        if($_POST["DFDagrupador"] == "1"){
            $dadosPesquisa['DfdAgrupador'] = 1;
        }else if($_POST["DFDagrupador"] == "2"){
            $dadosPesquisa['DfdAgrupador'] = 2;
        }
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

        case 'getDadosDFDAgrupar':
            //Sessão de coleta dos dados,
            $ano          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
            $listaDadosDFD = $objPlanejamento->getDadosDFDAgrupar($ano);
            $htmlResultado = $objPlanejamento->montaHTMLAgrupar($listaDadosDFD);
            $tudoOk = true;
    
            $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
            print_r($objJS);
    
            break;
        
        case 'abrirJanelaAgrupar':
            $codigoGrupo = $_POST['radioPesquAgrupar'];
            if(empty($codigoGrupo)){
                $objJS = json_encode(array("status"=>false, "msm"=>'Selecione um DFD.'));
                print_r($objJS);
            }else{
            $objJS = json_encode(array("status"=>true, "data"=>$codigoGrupo));
            print_r($objJS);
            }
            
            
            break;

    case 'getDadosVincularDFD':
        
        //Sessão de coleta dos dados,
        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($_POST['areaReq']);
        $dadosPesquisa["selectAnoPCA"]          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
        $dadosPesquisa["selectSitDFD"]          = $_POST["selectSitDFD"];
        $dadosPesquisa["grauPrioridade"]        = $objPlanejamento->anti_injection($_POST["grauPrioridade"]);
        $dadosPesquisa["descDemanda"]           = $objPlanejamento->anti_injection($_POST["descDemanda"]);
        $dadosPesquisa["DataIni"]               = $objPlanejamento->anti_injection($_POST["DataIni"]);
        $dadosPesquisa["DataFim"]               = $objPlanejamento->anti_injection($_POST["DataFim"]);
        $dadosPesquisa["cclamscodi"]            = $objPlanejamento->anti_injection($_POST["cclamscodi"]);
        $dadosPesquisa["cgrumscodi"]            = $objPlanejamento->anti_injection($_POST["cgrumscodi"]);
        
        // Como nenhum campo é obrigatório, a validação é direta.
        if (!empty($dadosPesquisa["DataIni"])) {
            $dataIni  = explode("/", $dadosPesquisa['DataIni']);
            $dadosPesquisa["DataIni"] = date('Y-m-d', mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]));
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = date('Y-m-d', mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]));
        }
        $listaDadosDFD = $objPlanejamento->getDadosDFDVincular($dadosPesquisa,null);
        $htmlResultado = $objPlanejamento->montaHTMLVincular($listaDadosDFD);
        $tudoOk = true;

        $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
        print_r($objJS);

        break;
    case 'getDadosVincularDFDManter':
        //Pega o numero da dfd aberta em manter
        $dadosPesquisa['dfdsequ']                 = $_POST['dfdsequ'];
        //Sessão de coleta dos dados,
        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($_POST['areaReq']);
        $dadosPesquisa["selectAnoPCA"]          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
        $dadosPesquisa["selectSitDFD"]          = $_POST["selectSitDFD"];
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
        // foreach ($_SESSION['vincularDFD'] as $returnDados){
            if($_SESSION['DFD']->cplvincodi){
                
                $listaDadosDFD = $objPlanejamento->consultaDFDcodigoVinculoDiferente($_SESSION['DFD']->cpldfdsequ, $_SESSION['DFD']->cplvincodi, $dadosPesquisa['selectAreaReq']);
                
            }else{
                
                $listaDadosDFD = $objPlanejamento->getDadosDFDVincular($dadosPesquisa, $_SESSION['DFD']->cpldfdsequ);
            }
                $listaDadosDFD = array_unique($listaDadosDFD, SORT_REGULAR);
                
        // }
                $htmlResultado = $objPlanejamento->montaHTMLVincularManter($listaDadosDFD);
                $tudoOk = true;
                $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
                print_r($objJS);
       




        break;
    case 'getDadosExportarDFD':
        unset($_SESSION['Export']);
        //Sessão de coleta dos dados,
        $formatoExport                            = $_POST['formatoExport']; // Identifica o formato
        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($_POST['selectAreaReq']);
        $dadosPesquisa["selectAnoPCA"]          = $objPlanejamento->anti_injection($_POST["selectAnoPCA"]);
        $dadosPesquisa["selectSitDFD"]          = $objPlanejamento->anti_injection($_POST["selectSitDFD"]);
        $dadosPesquisa["grauPrioridade"]        = $objPlanejamento->anti_injection($_POST["grauPrioridade"]);
        $dadosPesquisa["descDemanda"]           = $objPlanejamento->anti_injection($_POST["descDemanda"]);
        $dadosPesquisa["DataIni"]               = $objPlanejamento->anti_injection($_POST["DataIni"]);
        $dadosPesquisa["DataFim"]               = $objPlanejamento->anti_injection($_POST["DataFim"]);
        $dadosPesquisa["cclamscodi"]               = $objPlanejamento->anti_injection($_POST["cclamscodi"]);
        $dadosPesquisa["cgrumscodi"]               = $objPlanejamento->anti_injection($_POST["cgrumscodi"]);

        // Como nenhum campo é obrigatório, a validação é direta.
        if (!empty($dadosPesquisa["DataIni"])) {
            $dataIni  = explode("/", $dadosPesquisa['DataIni']);
            $dadosPesquisa["DataIni"] = mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]);
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]);
        }
        
        // $listaDadosDFD = $objPlanejamento->getDadosDFD($dadosPesquisa);
        $listaDadosDFD = $objPlanejamento->getDadosDFDConsulta($dadosPesquisa); // Imprimi conforme a tela

        if($formatoExport == "pdf"){
        $htmlResultado = $objPlanejamento->montaHTMLConsultaPDF($listaDadosDFD);
        $html = $htmlResultado;
        $_SESSION['HTMLPDF'] = $html;
        $_SESSION['HTMLPDFDownload'] = false;
        $_SESSION['HTMLPDFMudaOrientacao'] = true;
        $objJS = json_encode(array("status"=>true));
        print_r($objJS);
        }
        if($formatoExport == "xls" || $formatoExport == "csv"){
            
            $nomeArquivo = "RelatorioDFDConsultar";
            $cabecalho = array(
                "Número do DFD",
                "Ano do PCA",
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
                if(!empty($dado->dpldfdpret)){
                    $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                }else{
                    $dataPrevConclusao = "";
                }

                if(!empty($dado->fpldfdtpct)){
                    $tpProcesso = ($dado->fpldfdtpct=="D")? "Contratação Direta" : "Licitação";
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
                
                
                $resultados[$conta][] = $dado->cpldfdnumf;
                $resultados[$conta][] = $dado->apldfdanod;
                $resultados[$conta][] = $dado->descclasse;
                $resultados[$conta][] = $dataPrevConclusao;
                $resultados[$conta][] = strtoUpper2($tpProcesso);
                $resultados[$conta][] = $grauprioridade;
                $resultados[$conta][] = $dado->eplsitnome;

                $conta++;
            }
            $_SESSION['Export']['nomeArquivo']  = $nomeArquivo;
            $_SESSION['Export']['resultados']   = $resultados;
            $_SESSION['Export']['cabecalho']    = $cabecalho;
            $_SESSION['Export']['formatoExport']    = $formatoExport;
            $_SESSION['Export']['gerar']        = true;
            // O procedimento de  download deve ser feito no ConsPesquisarDFD.php pois não funciona no AJAX
            $objJS = json_encode(array("status"=>true));
            print_r($objJS);
        }
        
    break;
    case 'AnalisarJustDevolucao':
        $dadosDFD = $objPlanejamento->consultaDFDAnalisar($_POST['cpldfdsequ']);
        $html  ='<div class="modal-title textonormal" >';
        $html .='JUSTIFICATIVA PARA DEVOLUÇÃO';
        $html .='<span class="btn-fecha-modal close" >[ X ]</span>';
        $html .='</div>';
        $html .='<div class="modal-body">';
        $html .='<form action="" method="post" name="cadJustDevolucao">';
        $html .='<table style="align-content:center;" class="textonormal" width="400px">';
        $html .='<tr border=1>';
        $html .='<tr>';
        $html .='<td align="left" colspan="2" id="tdmensagemM">';
        $html .='<div class="mensagemM">';
        $html .='<div class="error" colspan="5">';
        $html .='Erro';
        $html .='</div>';
        $html .='<span id="mensagemErroModal" class="mensagem-textoM">';
        $html .='</span>';
        $html .='</div>';
        $html .='</td>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<table style="padding-top:3px;">';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" style="max-width:31%;">Número do DFD</td>';
        $html .='<td class="textonormal">'.$dadosDFD->cpldfdnumf.'</td>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" style="width:31%;">Área Requisitante</td>';
        $html .='<td class="textonormal">'.$dadosDFD->descorgao.'</td>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<td class="textonormal" bgcolor="#DCEDF7" style="width:31%;">Classe</td>';
        $html .='<td class="textonormal">'.$dadosDFD->descclasse.'</td>';
        $html .='</tr>';
        $html .='</table>';
        $html .='<table>';
        $html .='<tr>
                    <td>
                    <textarea cols="50" rows="4" style="margin-left:6px; margin-top:5px;text-transform:uppercase;max-width:380px;" class="justificativa" maxlength="1000" uppercase name="Justificativa" placeholder="Escreva aqui o motivo da devolução do DFD"></textarea>
                    </td>
                </tr>';
        $html .='<tr>
                    <td style="float:right;">
                        <input type="button" name="DevolverDFD" id="DevolverDFD" value="Confirmar" class="botao" >
                        <input type="button" name="Cancelar" value="Cancelar" class="botao btn-fecha-modal" >
                    </td>
                </tr>';
        $html .='</table>';
        $html .='</tr>';
        $html .= '</table> </form> </div>';
        $objJS = json_encode(array("status"=>true, "html"=>$html));
        print_r($objJS);
    break;
    
    case 'orgaosAgrupar':
        $sequencias = $_POST['sequencial'];
        $orgaoConsulta = '';
        foreach($sequencias as $dado){
            $orgaoConsulta .= $dado.','; 
        }
        
        $orgaoConsulta = substr_replace($orgaoConsulta, "", strrpos($orgaoConsulta, ","));
        if(!empty($orgaoConsulta)){
            $areaRequisitante = $objPlanejamento->getDescOrg($orgaoConsulta);

            // $htmlAreaReq = "<select id='selectAreaReq' name='selectAreaReq' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlAreaReq .= "<option value=''>Selecione a Área Requisitante...</option>";
            foreach ($areaRequisitante as $orgao) {
                $htmlAreaReq .= "<option value='".$orgao->corglicodi."'>".$orgao->eorglidesc."</option>";
            }
            $objJS = json_encode(array("status"=>true, "html"=>$htmlAreaReq));
            print_r($objJS);
        }else{
            $objJS = json_encode(array("status"=>false));
            print_r($objJS);
        }
        
    break;
}
?>
