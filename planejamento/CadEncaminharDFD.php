<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * Portal de Compras
 * Programa: CadEncaminharDFD.php
 * Autor: Osmar Celestino
 * Data: 09/12/2022
 * Objetivo: Programa para Encaminhar DFD
 * Tarefa Redmine: #275574
 * ---------------------------------------------------------------------------
 * Alterado: Diógenes Dantas
 * Data:     12/12/2022
 * Objetivo: CR 275574
 * ---------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
require_once "ClassPlanejamento.php";

function montaHTMLEncaminhar($dadosDFD,$tpl)
{
    $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
    if(empty($dadosDFD)){
        $html = '<tr>
                <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="970px ">
                        RESULTADO DA PESQUISA
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="8" class="titulo3" width="900px"> Não há DFDs para serem encaminhados.</td>
                </tr>';
                $tpl->block("BLOCO_DADOS");
                $tpl->block("BLOCO_RESULTADO");
    }else{
        $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>';
        $i=0;
        foreach($secretariasDFD as $secretaria){
            if($i == 0){
                $CheckAll ='<input type="checkbox" id="numDFDAll" name="numDFDAll"/><label for="numDFDAll">Selecionar todos</label>';
            }else{
                $CheckAll ='';
            }
            $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';

            $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                            <td class="tdresult" width="63px">'.$CheckAll.'</td>
                            <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                            <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                            <td class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                            <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PREVISTA PARA CONCLUSÃO</td>
                            <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                            <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                            <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                            </tr>
                        </thead>
                        <tbody>';

            foreach($dadosDFD as $dado){
                if($secretaria->corglicodi == $dado->corglicodi){
                    $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                    $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                    $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";

                    if($dado->fpldfdgrau == 1){
                        $grauprioridade = "ALTO";
                    }else if($dado->fpldfdgrau == 2){
                        $grauprioridade = "MÉDIO";
                    }else if($dado->fpldfdgrau == 3){
                        $grauprioridade = "BAIXO";
                    }

                    $cclamscodi = empty($dado->cclamscodi)? "-": $dado->cclamscodi;
                    $descclasse = empty($dado->descclasse)? "-": $dado->descclasse;
                    $dataPrevConclusao = ($dataPrevConclusao == "01/01/1970")? "-": $dataPrevConclusao;
                    $tpProcesso = empty($tpProcesso)? "-": $tpProcesso;
                    $grauprioridade = empty($grauprioridade)? "-": $grauprioridade;
                
                $html.='<tr id="resultados">
                        <td class="tdresult" id="resIdDFD"><input type="checkbox" class="checkbox" name="sequencial[]" value="'.$dado->cpldfdsequ.'" style="text-transform: capitalize"></input></td>
                        <td class="tdresult" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                        <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                        <td class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                        <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                        <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                        <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                        <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                    </tr>';       
            }
            } 
            $html .= '</tbody></table></td></tr>';
            $i++;
        }
        
        $tpl->block("BLOCO_DADOS");
        $tpl->block("BLOCO_RESULTADO");
        $tpl->block("BOTAO_ENCAMINHAR");
    }
    $tpl->RESULTADO = $html;
}

function consultaAno() {
    $db = Conexao();
    $sql = "select distinct apldfdanod from sfpc.tbplanejamentodfd";
    $resultado = executarSQL($db, $sql);
    $dados = array();
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
        $dados[] = $retorno;
    }
    return $dados;
}
function plotarUnidade(TemplatePaginaPadrao  $tpl, $areaRequisitante, $OrgaoLicitanteCodigo)
{
        foreach ($areaRequisitante as $orgao) {
            $tpl->VALOR_ID_ORGAO_LICITANTE = $orgao->corglicodi;
            $tpl->VALOR_NOME_ORGAO_LICITANTE = $orgao->eorglidesc;
            if ($orgao->corglicodi == $OrgaoLicitanteCodigo) {
                $tpl->VALOR_ORGAO_SELECTED = 'selected="selected"';
            } else {
                $tpl->clear('VALOR_ORGAO_SELECTED');
            }
            $tpl->block('BLOCO_ITEM_ORGAO_LICITANTE');
        }
        $tpl->block('BLOCO_ORGAO_LICITANTE');
}

/**
 * plotarBlocoAno
 *
 * Monta o select de ano
 *
 * @param array $anos Lista com anos
 *
 * @return void
 */
 function plotarBlocoAno(array $anos, $tpl)
{
    $anoSelecionado = $_POST['GerarNumeracaoAno'];
    $tpl->ANO_VALUE     = '';
    $tpl->ANO_TEXT      = 'Selecione o Ano...';
    $tpl->ANO_SELECTED  = "selected";
    $tpl->block("BLOCO_ANO");

    foreach ($anos as  $ano) {
        $tpl->ANO_VALUE = $ano->apldfdanod;
        $tpl->ANO_TEXT  = $ano->apldfdanod;

        // Vendo se a opção atual deve ter o atributo "selected"
        if ($anoSelecionado === $ano->apldfdanod) {
            $tpl->ANO_SELECTED = "selected";
        } else {
            // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
            $tpl->clear("ANO_SELECTED");
        }
        $tpl->block("BLOCO_ANO");
    }

}//end plotarBlocoAno()


/**
 * [proccessPrincipal description]
 * @param  TemplateAppPadrao $tpl [description]
 * @return [type]                 [description]
 */
function proccessPrincipal(TemplatePaginaPadrao $tpl)
{
    $objPlanejamento = new Planejamento();
    // Variáveis com o global off #
    if ($_SERVER ['REQUEST_METHOD'] == 'POST') {

    } else {
        $Mens2 = $_GET ['Mens2'];
        $Tipo2 = $_GET ['Tipo2'];
        $Mensagem2 = urldecode($_GET ['Mensagem2']);
    }
    if($_POST['Botao'] == 'Pesquisar'){
        $erroMsm ="";
        if($_POST['GerarNumeracaoAno'] and $_POST['UnidadeCodigo']) {
            $dadosPesquisa["selectAnoPCA"] = $objPlanejamento->anti_injection($_POST["GerarNumeracaoAno"]);
            $dadosPesquisa["selectAreaReq"] = $objPlanejamento->anti_injection($_POST['UnidadeCodigo']);
            $validaBloqLiveracao = checaBloqueioLib($dadosPesquisa["selectAnoPCA"], $dadosPesquisa["selectAreaReq"], $objPlanejamento);
            if($validaBloqLiveracao == false){
                $dados = $objPlanejamento->getDadosDFDEncaminhar($dadosPesquisa);
                montaHTMLEncaminhar($dados,$tpl);   
            }else{
                $tpl->block('BLOCO_BLOQUEIA');
            }
                
        }
        if(empty($_POST['GerarNumeracaoAno'])){
            $erroMsm .= "Ano do PCA, ";
        }
        if(empty($_POST['UnidadeCodigo'])){
            $erroMsm .= "Área Requisitante, ";
        } 
        if(!empty($erroMsm)){
            $erroMsm = substr_replace($erroMsm, '.', strrpos($erroMsm, ", "));
            $tpl->MENSAGEM_ERROR = "Informe: ".$erroMsm;
            $tpl->block('BLOCO_ERROR');
        }

    }
    if($_POST['Botao'] == 'Valida'){ 
        if(empty($_POST['sequencial'])){
            $tpl->MENSAGEM_ERROR = "Selecione um ou mais DFD(s).";
        }
        $tpl->block('BLOCO_ERROR');
    }
    

    $anos = consultaAno();
    plotarBlocoAno($anos, $tpl);
    $areaRequisitante = $objPlanejamento->getOrgao();
    $codigoOrgao = $_POST['UnidadeCodigo'];
    plotarUnidade($tpl, $areaRequisitante, $codigoOrgao);
}

function inserirHistorico($objPlanejamento,$sequenciaDFD){
    $db = Conexao();
        foreach($sequenciaDFD as $sequ){
        $sequencialHistorico = $objPlanejamento->novoSequencialHistoricoSituacao();
        $sqlInsertHistorico ="
        insert into sfpc.tbplanejamentohistoricosituacaodfd 
        (
            cplhsisequ, 
            cpldfdsequ,	
            cplsitcodi,	
            eplhsijust,	
            tplhsiincl,	
            cusupocodi,	
            tplhsiulat
        ) values ( 
            ".$sequencialHistorico.",
            ".$sequ.",
            3,
            null,
            now(),
            ".$_SESSION['_cusupocodi_'].",
            now()
        )
    ";

    $resultado = executarSQL($db, $sqlInsertHistorico);

    }
}

function alteraSituacao($sequencial) {
    $db = Conexao();
    foreach($sequencial as $sequ){
        $sql = "update sfpc.tbplanejamentodfd set cplsitcodi = 3 where cpldfdsequ = $sequ";
        $resultado = executarSQL($db, $sql);
    }
    

    if($resultado != False) {
        return True;
    }else {
        return false;
    }
}

function processEncaminhar($tpl, $objPlanejamento) {
    alteraSituacao($_POST['sequencial']);
    inserirHistorico($objPlanejamento,$_POST['sequencial']);
    $tpl->block('BLOCO_SUCESSO');
}

function checaBloqueioLib($ano, $corglicodi, $objPlanejamento){
    $hoje = date('Y-m-d');
        $checaBloq = $objPlanejamento->checaBloqueio($ano);
        if(is_null($checaBloq->cplblosequ) || (is_null($checaBloq->dplblodini) || is_null($checaBloq->dplblodfim))){ //Se não houver bloqueio, ou datas no bloqueio;
            return false;
        }
        //Se houver bloqueio
        if(!empty($corglicodi)){
            $checaLib = $objPlanejamento->checaLiberacao($ano, $corglicodi);
            //Se estiver dentro do periodo liberado permite  
            if((!is_null($checaLib->dpllibdini) && !is_null($checaLib->dpllibdfim)) && (strtotime($checaLib->dpllibdini) <= strtotime($hoje) && strtotime($checaLib->dpllibdfim) >= strtotime($hoje))){
                return false;
            }
            
        }
        if(strtotime($checaBloq->dplblodini) <= strtotime($hoje) && strtotime($checaBloq->dplblodfim) >= strtotime($hoje)){
            return true;
        }else{
            return  false;
        }
}

/**
 * [frontController description]
 */
function frontController()
{
    $tpl = new TemplatePaginaPadrao("templates/CadEncaminharDFD.html", "Planejamento > DFD > Encaminhar");
    $botao = isset($_POST['Botao']) ? $_POST['Botao']: 'Principal';
    switch ($botao) {
        case 'Encaminhar':
            $objPlanejamento = new Planejamento();
            processEncaminhar($tpl, $objPlanejamento);

        case 'Principal':
        default:
            proccessPrincipal($tpl);
    }

    $tpl->show();
}

frontController();
