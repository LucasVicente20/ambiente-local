<?php
 /**
 * Portal de Compras
 * Programa: PostDadosManterDFD.php
 * Autor: Diógenes Dantas
 * Data: 22/11/2022
 * Objetivo: Programa para pesquisar DFD
 * Tarefa Redmine: #275345
 * -------------------------------------------------------------------
 * Alterado:    Lucas Vicente e João Madson 
 * Data:        06/01/2023
 * Tarefa:      CR 277232
 * -------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:09/01/2023
 * Tarefa: Ajuste na regra do Configurador DFD
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
        unset($_SESSION['DFD']);
        unset($_SESSION['historico']);
        unset($_SESSION['itensManter']);
        unset($_SESSION['item']);
        unset($_SESSION['classe']);
        unset($_SESSION['classeSelecionadada']);
        unset($_SESSION['cnpjMultiplos']);
        unset($_SESSION['KeepOrgaoSelect']);
        unset($_SESSION['classe']);
        unset($_SESSION["vincularDFD"]);
        unset($_SESSION["cclamscodi"]);
        unset($_SESSION["cgrumscodi"]);
        unset($_SESSION['MensagemManter']);
        unset($_SESSION['itensDFD']);
        unset($_SESSION['classeAlterada']);
        unset($_SESSION['servico']);
        unset($_SESSION['novoSequ']); 
        unset($_SESSION['ultHist']); 
        unset($_SESSION['Bloqueio']); 
        unset($_POST);
        print_r(json_encode(array("status"=>true)));
    break;
    case 'getOrgao':
        $orgaosUsuario = $objPlanejamento->getOrgao();
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

    case 'getSituacaoDFD':
        $listaSituacaoDFD = $objPlanejamento->getSituacaoDFD();

        if (isset($listaSituacaoDFD) && (!empty($listaSituacaoDFD))) {
            $htmlSitDFD = "<select id='selectSitDFD' name='selectSitDFD' size='1' style='width:auto; font-size: 10.6667px;'>";
            $htmlSitDFD .= "<option value=''>Escolha a situação</option>";

            foreach ($listaSituacaoDFD as $lista) {
                if($lista->cplsitcodi==1 || $lista->cplsitcodi==2 || $lista->cplsitcodi==4){
                    $htmlSitDFD .= "<option value='".$lista->cplsitcodi."'>".$lista->eplsitnome."</option>";
                } 
                
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
        $html .= '<form action="" method="post" name="ConsSelecionarManterDFD">';
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

    case 'getDadosDFD':
        //Sessão de coleta dos dados,
        //Verifica qual o metodo de area requisitante e salva o correto
        if(empty($_POST['AreaReqCod']) && !empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["selectAreaReq"];
        }else if(!empty($_POST['AreaReqCod']) && empty($_POST["selectAreaReq"])){
            $corglicodi = $_POST["AreaReqCod"];
        }

        //Verifica se a busca é limitada a DFDs derivados de agrupamento.
        if($_POST["DFDagrupador"] == "1"){
            $dadosPesquisa['DfdAgrupador'] = 1;
        }else if($_POST["DFDagrupador"] == "2"){
            $dadosPesquisa['DfdAgrupador'] = 2;
        }


        $dadosPesquisa['idDFD']                 = $objPlanejamento->anti_injection($_POST['idDFD']);
        $dadosPesquisa['selectAreaReq']         = $objPlanejamento->anti_injection($corglicodi);
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
            $dadosPesquisa["DataIni"] = date("Y/m/d", mktime(00,00,00, $dataIni[1], $dataIni[0], $dataIni[2]));
        }

        if (!empty($dadosPesquisa["DataFim"])) {
            $dataFim  = explode("/", $dadosPesquisa['DataFim']);
            $dadosPesquisa["DataFim"] = date("Y/m/d", mktime(00,00,00, $dataFim[1], $dataFim[0], $dataFim[2]));
        }

        $listaDadosDFD = $objPlanejamento->getDadosDFD($dadosPesquisa);
        $htmlResultado = $objPlanejamento->montaHTMLManter($listaDadosDFD);
        $tudoOk = true;

        $objJS = json_encode(array("status"=>true, "html"=>$htmlResultado));
        print_r($objJS);

    break;
    case 'ExcluirDFD':
        $numDFD = $_POST['dfdSelected'];
        if(!empty($numDFD)){
            $consultaDFD = $objPlanejamento->consultaDFD($numDFD);
            $consultaHistorico = $objPlanejamento->consultaHistorico($numDFD);
            $consultaItens = $objPlanejamento->consultaItens($numDFD);
            // $deletaDadosDFD = $objPlanejamento->deleteDadosDFD($consultaDFD->cpldfdsequ);
            // $db = Conexao();
            // $sql = "SELECT cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi 
            //     from sfpc.tbplanejamentoconfiguracao 
            //     where cplconcodi = (select max(cplconcodi) 
            //                         from sfpc.tbplanejamentoconfiguracao 
            //                         where corglicodi = ".$consultaDFD->corglicodi." and aplconanop = ".$consultaDFD->apldfdanod." and cplcontpmd = 3)";
                
            //     $result = $db->query($sql);
            //     $count = $result->numRows();
                
            //     $Linha = $result->fetchRow();
            //     $opcaoModificacao   =    $Linha[0];
            //     $tipoModificacao    =    $Linha[1];
            //     $dataIni            =    $Linha[2];
            //     $dataFim            =    $Linha[3];
                
            //     if ($opcaoModificacao == 3 && $tipoModificacao == 1){
                    $deletaDadosDFD = $objPlanejamento->deleteDadosDFD($consultaDFD->cpldfdsequ);
                    if($deletaDadosDFD == true){
                        $_SESSION['MensagemManter'] = array("status"=>true, "msm"=>"O DFD foi excluido!");//Salvei aqui para mostrar na tela de pesquisa para onde o usuario será direcionado.
                        $objJS = json_encode($_SESSION['MensagemManter']);
                        print_r($objJS);
                    }else{
                        $objJS = json_encode(array("status"=>false, "msm"=>"Problema na operação, por favor entre em contato com o suporte."));
                        print_r($objJS);
                    }
        //         }else{
        //             $objJS = json_encode(array("status"=>false, "msm"=>"A Área requisitante informada está bloqueada para exclusão no Ano PCA informado."));
        //             print_r($objJS);
        //         }
        }

    break;
    case 'AlterarDFD':
        if($_SESSION['Bloqueio']['status'] == true){
            $mensagem = $_SESSION['Bloqueio']['msm'];
            print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
            exit;
        }
        $dadosDFD = $_SESSION['DFD']; // pega todos os dados antigos antes de pegar o que foi alterado.
        $dadosDFD->itens = $_SESSION['itensDFD']; // Pega os dados dos itens
        $situacaoAtual = $_SESSION['DFD']->cplsitcodi;
        
        //Pega os campos alteraveis e valida os obrigatórios
        if(empty($_POST["descSuDemanda"]) || $_POST["descSuDemanda"] == ""){
            $dadosDFD->epldfddesc = "";
        }else{
            $dadosDFD->epldfddesc = $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["descSuDemanda"])));
        }
        
        if(empty($_POST["justContratacao"]) || $_POST["justContratacao"] == ""){
            $dadosDFD->epldfdjust = "";
        }else{
            $dadosDFD->epldfdjust = $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justContratacao"])));
        }

        if(empty($_POST["tpProcContratacao"]) || $_POST["tpProcContratacao"] == ""){
            $dadosDFD->fpldfdtpct = "";
        }else{
            $dadosDFD->fpldfdtpct = $objPlanejamento->anti_injection($_POST["tpProcContratacao"]);
        }
        if(empty($_POST["justPriAlta"]) || $_POST["justPriAlta"] == ""){
            $dadosDFD->epldfdjusp = "";
        }else{
            $dadosDFD->epldfdjusp = $objPlanejamento->anti_injection(removeSimbolos(RetiraAcentos($_POST["justPriAlta"])));
            if($_POST["grauPrioridade"] != "1"){
                $dadosDFD->epldfdjusp = "";  
            }
        }
        
        if(!empty($_POST["estValorContratacao"])){
            $dadosDFD->cpldfdvest = moeda2float($_POST["estValorContratacao"]);        
        }else{
            $dadosDFD->cpldfdvest = "NULL";
        }

        if(!empty($_POST['dtPretendidaConc'])){
            $dtpretConc  = explode("/", $_POST['dtPretendidaConc']);
            $dadosDFD->dpldfdpret = date("Y-m-d", mktime(00,00,00, $dtpretConc[1], $dtpretConc[0], $dtpretConc[2]));
        }else{
            $dadosDFD->dpldfdpret = ""; 
        }
        
        $dadosDFD->fpldfdgrau = !empty($_POST["grauPrioridade"])?$_POST["grauPrioridade"]:"";
       
        //verifica se tem alteração na classe
        if($_POST['chaveNovaClasse'] == "true"){
            $dadosDFD->cclamscodi  = $_POST['cclamscodi'];
            $dadosDFD->cgrumscodi  = $_POST['cgrumscodi'];
        }else{
            $dadosDFD->cclamscodi=null;
            $dadosDFD->cgrumscodi=null;
        }

        if($_POST["rascunho"] != 'true'){
            $retorno = $objPlanejamento->validarManterDFD($dadosDFD);
        }else{
            $retorno = $objPlanejamento->validarManterRascunhoDFD($dadosDFD);
        }
        if($retorno['valida'] == false ) {
            $objJS = json_encode($retorno);
            print_r($objJS);die;
        }
       
        if($_POST["rascunho"] == 'true' ){
            $dadosDFD->cplsitcodi = 1;
        }else{
            $dadosDFD->cplsitcodi = 2;
        }
       
        $dadosDFD->fpldfdcorp = !empty($dadosDFD->fpldfdcorp) ? $objPlanejamento->anti_injection($_POST["contratCorp"]) : $dadosDFD->fpldfdcorp;
        if(!empty($_SESSION['vincularDFD']) && empty($dadosDFD->cplvincodi)){
            $dadosDFD->cplvincodi  = $objPlanejamento->gerarCodigoGrupolVincularDFD();
            $dadosDFD->chaveNovoCodVinc = true;
        }
        
        $update = $objPlanejamento->updateDFD($dadosDFD);
        
        $itens = $objPlanejamento->updateitensDFD($dadosDFD->itens, $dadosDFD->cpldfdsequ);
        
        if($situacaoAtual != $dadosDFD->cplsitcodi){
            $historico = $objPlanejamento->insertHistoricoSituacaoDFD($dadosDFD);
        }
        
        if(!empty($_SESSION['retirarVinculoBanco'])){
            foreach ($_SESSION['retirarVinculoBanco'] as $dadosRetirar) {
                $dfdExiste =  $objPlanejamento->consultaDFDVinculoBySequVinc($dadosRetirar->cpldfdsequ, $dadosDFD->cplvincodi);
                if(!empty($dfdExiste)){
                    $objPlanejamento->deleteDFDVinculoBySequ($dadosRetirar->cpldfdsequ, $dadosDFD->cplvincodi);
                }
            }
        }
        
        if(!empty($_SESSION['vincularDFD'])){
            $sequencial = $objPlanejamento->gerarSequencialVincularDFD();
            foreach ($_SESSION['vincularDFD'] as $dadosVincular) {
                $dfdExiste = $objPlanejamento->consultaDFDVinculoBySequVinc($dadosVincular->cpldfdsequ, $dadosDFD->cplvincodi);
                if(empty($dfdExiste)){
                    $objPlanejamento->insertDadosVinculo($sequencial, $dadosDFD->cplvincodi, $dadosVincular->cpldfdsequ,$_SESSION['_cusupocodi_']);
                    $sequencial++;
                }
            }
        }
        unset($_SESSION['DFD']);
        unset($_SESSION['vincularDFD']);
        unset($_SESSION['retirarVinculoBanco']);

        $_SESSION['MensagemManter'] = array("status"=>true, "msm"=>"O DFD alterado com sucesso!");//Salvei aqui para mostrar na tela de pesquisa para onde o usuario será direcionado.
        $objJS = json_encode($_SESSION['MensagemManter']);
        print_r($objJS);
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
            $tableHtml  .= "<td>CLASSE</td>";
            $tableHtml  .= "<td>DESCRIÇÃO DO ITEM</td>";
            $tableHtml  .= "<td>CÓD. REDUZIDO </td>";
            if(empty($resultadoPesquisa[0]->cservpsequ)){
            $tableHtml  .= "<td>UNIDADE</td>";
            }
            $tableHtml  .= "</tr>";
            $tableHtml  .= "</thead>";
            $tableHtml  .= "<TBody>";
            $ordinal = 0;
            foreach($resultadoPesquisa as $dados){ 
                $tableHtml  .= "<tr width='850px'>";
                $tableHtml  .= "<td><input type='radio' name='radioClasse' id='radioClasse' value='$ordinal'></td>";
                $tableHtml  .= "<td style='word-wrap: inherit; max-width: 210px;'>$dados->eclamsdesc</td>";
                
                if(!empty($dados->cmatepsequ)){
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->ematepdesc</td>";
                    $tableHtml  .= "<td>$dados->cmatepsequ</td>";
                }else if($dados->sequencialmatserv){
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->descricaomatserv</td>";
                    $tableHtml  .= "<td>$dados->sequencialmatserv</td>";
                }else{
                    $tableHtml  .= "<td style='word-wrap: inherit; max-width: 450px;'>$dados->eservpdesc</td>";
                    $tableHtml  .= "<td>$dados->cservpsequ</td>";    
                }
                if(empty($dados->cservpsequ)){
                $tableHtml  .= "<td>$dados->eunidmsigl </td>";
                }
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
    case "desvinculaDFD":
        foreach ($_POST['checkVincular'] as $sequencial){
            $i=0;
            foreach($_SESSION['vincularDFD'] as $key=>$vinculo){
                if($sequencial == $_SESSION['vincularDFD'][$key]->cpldfdsequ){
                    $_SESSION['retirarVinculoBanco'][] = $_SESSION['vincularDFD'][$key];
                    unset($_SESSION['vincularDFD'][$key]);
                }
                $i++;
            }
            
        }
        print_r(json_encode(array("status"=>true)));
     break;
     case 'retirarItem':
        $removerItem   = $_POST['chkbxItem'];
        $organizaItens = $_SESSION['itensDFD']; //Passa os dados para a variavel
        foreach ($removerItem as $sequencial){
            foreach($organizaItens as $key=>$item){
                if($sequencial == $item->cplitecodi){
                    $_SESSION['itensDFD'][$key] = null;
                }
           }
            
        }
        $aux=array();
        $i=0;
        foreach($_SESSION['itensDFD'] as $sess){
            if(!empty($sess)){
                $aux[$i]=$sess;
            }
            $i++;
        }
        unset($_SESSION['itensDFD']);
        $_SESSION['itensDFD'] = $aux;
        $objJS = json_encode(array("status"=>true,"msm"=>"Item removido com sucesso!"));
        print_r($objJS);
     break;
     
    case "checaBloqueio":
        $corglicodi = $_POST['orgDfd'];
        $anoPCA = $_POST['anoDfd'];
        $hoje = date('Y-m-d');
        if(!is_null($anoPCA) && !is_null($corglicodi)){
            $checaBloq = $objPlanejamento->checaBloqueio($anoPCA);
            if(is_null($checaBloq->cplblosequ) || (is_null($checaBloq->dplblodini) || is_null($checaBloq->dplblodfim))){ //Se não houver bloqueio, ou datas no bloqueio;
                $_SESSION['PeriodoBloqueado']['status'] == false;
                print_r(json_encode(array("status"=>false)));
                exit;
            }
            //Se houver bloqueio
            if(!empty($corglicodi)){
                $checaLib = $objPlanejamento->checaLiberacao($anoPCA, $corglicodi);
                //Se estiver dentro do periodo liberado permite  
                if((!is_null($checaLib->dpllibdini) && !is_null($checaLib->dpllibdfim)) && (strtotime($checaLib->dpllibdini) <= strtotime($hoje) && strtotime($checaLib->dpllibdfim) >= strtotime($hoje))){
                    $_SESSION['Bloqueio']['status'] = false;
                    print_r(json_encode(array("status"=>false)));
                    exit;
                }
                
            }
            if(strtotime($checaBloq->dplblodini) <= strtotime($hoje) && strtotime($checaBloq->dplblodfim) >= strtotime($hoje)){
                $mensagem = "No atual período, não está autorizada a alteração de DFD para o PCA de $anoPCA";
                $_SESSION['Bloqueio']['status'] = true;
                $_SESSION['Bloqueio']['msm'] = $mensagem;
                print_r(json_encode(array("status"=>true, "msm"=>$mensagem)));
                exit;
            }else{
                $_SESSION['Bloqueio']['status'] = false;
                print_r(json_encode(array("status"=>false)));
                exit;
            }
        }
    break;
}
?>
