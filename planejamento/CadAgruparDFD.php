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
 * Programa: CadAgruparDFD.php
 * Autor: Osmar Celestino
 * Data: 31/01/2023
 * Objetivo: Programa para Agrupar DFD
 * Tarefa Redmine: #275719
 * ---------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     01/02/2023
 * Objetivo: #275719 Ajuste no Agrupar
 * ---------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
require_once "ClassPlanejamento.php";

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

function montaHTMLEncaminahar($dadosDFD,$tpl)
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
                    <td align="center" colspan="8" class="titulo3" width="900px"> Não há DFDs para serem Agrupados.</td>
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
            foreach($secretariasDFD as $secretaria){
                $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';

        $html.='<tr>
                    <td>
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                        <tr id="cabecalhos">
                        <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                        <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                        <td class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                        <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PRESVISTA PARA CONCLUSÃO</td>
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
                    <td class="tdresult"><input type="checkbox" class="checkbox" name="sequencial[]" value="'.$dado->cpldfdsequ.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</input></td>
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
    }
        
        $tpl->block("BLOCO_DADOS");
        $tpl->block("BLOCO_RESULTADO");
        $tpl->block("BOTAO_ENCAMINHAR");
    }
    $tpl->RESULTADO = $html;
}




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
        $codigoGrupo = $_GET ['CodigoGrupo'];
        $codigoClasse = $_GET['CodigoClasse'];
        $_SESSION['codigoGrupo'] = $_GET ['CodigoGrupo'];
        $_SESSION['codigoClasse'] = $_GET['CodigoClasse'];
     
    }
    if($_SESSION['codigoGrupo']){
        $codigoGrupo = $_SESSION['codigoGrupo'];
    }
    if($_SESSION['codigoClasse']){
        $codigoClasse = $_SESSION['codigoClasse'];
    }
    
    if($codigoGrupo){
        $dados = $objPlanejamento->getDadosDFDRealizarAgrupamento($codigoGrupo, $codigoClasse);
        $areaRequisitante = $objPlanejamento->getOrgao();
        $codigoAreaRequisitante = $_POST['UnidadeCodigo'];
        $motivoAgrupamento = $_POST['motivoAgrupamento'];
        $sequencialDFDSelecionada = $_POST['sequencial'];
        if($dados){
            plotarUnidade($tpl, $areaRequisitante, $codigoAreaRequisitante);
        }
        $valor = 0;
        $sequencial = '';
        if($_POST['Botao'] == 'Agrupar'){
            if($motivoAgrupamento &&  $codigoAreaRequisitante &&  $sequencialDFDSelecionada){

                $dadosPesquisaValor = $objPlanejamento->getSequenciaisOrgao($_POST['sequencial']);
                $sequenciais = substr_replace($dadosPesquisaValor['Sequencial'], "", strrpos($dadosPesquisaValor['Sequencial'], ","));
                $orgaos = substr_replace($dadosPesquisaValor['Orgao'], "", strrpos($dadosPesquisaValor['Orgao'], ","));
               
                $valor = $objPlanejamento->getDadosValorAgrupar($sequenciais,$orgaos);
                $valorFinal = $valor[0]->totalvalor;

                $sequencialPlanNovo = $objPlanejamento->novoSequencialPlanejamento();
                $numeroDFDNovo = $objPlanejamento->novoSequencialDFD($codigoAreaRequisitante,'2024');

                $centroCusto = $objPlanejamento->getCenCustoUsuario($codigoAreaRequisitante);
                $ccenpocorg = (strlen($centroCusto[0]->ccenpocorg) == 2) ? $centroCusto[0]->ccenpocorg : "0".$centroCusto[0]->ccenpocorg;
                $ccenpounid = (strlen($centroCusto[0]->ccenpounid) == 2) ? $centroCusto[0]->ccenpounid : "0".$centroCusto[0]->ccenpounid;
                $parte1 = $ccenpocorg . $ccenpounid;

                if(strlen($numeroDFDNovo) < 4){
                    $quantosZeros = 4 - strlen($numeroDFDNovo);
                    $cpldfdnumd = "";
                    for($i=0; $i<$quantosZeros; $i++){
                        $cpldfdnumd .= "0";
                    }
                    $cpldfdnumd .= $numeroDFDNovo;
                }else{
                    $cpldfdnumd = $numeroDFDNovo;
                }

                $cpldfdnumfFormatado ="'".$parte1.".".$cpldfdnumd."/2024'";

                $sequencialAgrupamentoNovo = $objPlanejamento->novoSequencialPlanejamentoAgrupamento();
                inserirDadosPlanejamento($sequencialPlanNovo, $valorFinal, $codigoAreaRequisitante, $codigoGrupo, $codigoClasse, $cpldfdnumfFormatado, $numeroDFDNovo);
                inserirDadosPlanejamentoAgrupamento($sequencialPlanNovo, $sequencialAgrupamentoNovo, $motivoAgrupamento, $codigoAreaRequisitante);
                inserirHistoricoRascunho($objPlanejamento, $sequencialPlanNovo);
                
                foreach($_POST['sequencial'] as $sequencial){
                    removeVinculo($sequencial);
                    inserirDadosPlanejamentoDFDAgrupada($sequencialAgrupamentoNovo, $sequencial);                    
                }
                alteraSituacaoAgrupado($sequencialDFDSelecionada);  
                inserirHistoricoAgrupado($objPlanejamento, $sequencialDFDSelecionada);
            }
            $_SESSION['Sucesso'] = true;
            header("location: CadSelecionarAgruparDFD.php");
        }
        if($_POST['Botao'] == 'Valida'){
            $mensagem = '';
            if(empty($sequencialDFDSelecionada)){
                $mensagem .= " Selecione pelo menos um DFD,";         
            }
            if(count($sequencialDFDSelecionada)==1){
                $mensagem .= " Selecione pelo menos dois DFD,";         
            }

            if(empty($codigoAreaRequisitante)){
                $mensagem .= " Selecione a Área do Requisitante,";
            }

            if(empty($motivoAgrupamento)){
                 $mensagem .= " Digite o Motivo do Agrupamento,";
            }     
            
            $mensagem = substr_replace($mensagem, ".", strrpos($mensagem, ","));
            $tpl->MENSAGEM_ERROR = $mensagem;
            $tpl->block('BLOCO_ERROR');
        }
        
        montaHTMLEncaminahar($dados,$tpl);
       
    
    }

    
}

function inserirHistoricoAgrupado($objPlanejamento,$sequenciaDFD){
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
            5,
            null,
            now(),
            ".$_SESSION['_cusupocodi_'].",
            now()
        )
    ";

    $resultado = executarSQL($db, $sqlInsertHistorico);

    }
}
function inserirHistoricoRascunho($objPlanejamento,$sequenciaDFD){
    $db = Conexao();

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
            ".$sequenciaDFD.",
            1,
            null,
            now(),
            2,
            now()
        )
    ";

    $resultado = executarSQL($db, $sqlInsertHistorico);
}
function alteraSituacaoAgrupado($sequencial) {
    $db = Conexao();
    foreach($sequencial as $sequ){
        $sql = " update sfpc.tbplanejamentodfd set cplsitcodi = 5 where cpldfdsequ = $sequ";
        $resultado = executarSQL($db, $sql);
        
    }
        return $resultado;
    }

function inserirDadosPlanejamento($sequencial, $valor, $codigoAreaRequisitante, $codigoGrupo, $codigoClasse, $numeroDFDFormatado, $numeroDFD){
    $db = Conexao();   
        $sqlInsertHistorico ="
        insert into sfpc.tbplanejamentodfd  
        (
            cpldfdsequ, 
            apldfdanod,	
            corglicodi,	
            cgrumscodi,	
            cclamscodi,	
            cpldfdvest,	
            cusupocodi,
            cplsitcodi,
            cpldfdnumf,
            cpldfdnumd,
            fpldfdagru,
            tpldfdincl
        ) values ( 
            ".$sequencial.",
            2024,
            ".$codigoAreaRequisitante.",
            ".$codigoGrupo.",
            ".$codigoClasse.",
            ".$valor.",
            2,
            1,
            ".$numeroDFDFormatado.",
            ".$numeroDFD.",
            'S',
            now()
        )
    ";
   

    $resultado = executarSQL($db, $sqlInsertHistorico);

    }
    function inserirDadosPlanejamentoAgrupamento($sequencial, $sequencialNovo, $motivoAgrupamento, $codigoAreaRequisitante){
        $db = Conexao();   
            $sqlInsertHistorico ="
            insert into sfpc.tbplanejamentoagrupamentodfd  
            (
                cplagdsequ, 
                cpldfdsequ,	
                corglicodi,	
                eplagdmoti,	
                tplagdincl,	
                cusupocodi,	
                tplagdulat
            ) values ( 
                ".$sequencialNovo.",
                ".$sequencial.",
                ".$codigoAreaRequisitante.",
                '".$motivoAgrupamento."',
                now(),
                2,
                now()
            )
        ";
    
        $resultado = executarSQL($db, $sqlInsertHistorico);
    
        }
        function inserirDadosPlanejamentoDFDAgrupada($sequencial, $sequencialDFD){
            $db = Conexao();   
                $sqlInsertHistorico ="
                insert into sfpc.tbplanejamentodfdagrupada  
                (
                    cplagdsequ, 
                    cpldfdsequ,	
                    tpldfaincl,	
                    cusupocodi,	
                    tpldfaulat
                
                ) values ( 
                    ".$sequencial.",
                    ".$sequencialDFD.",
                    now(),
                    ".$_SESSION['_cusupocodi_'].",
                    now()
                )
            ";
        
            $resultado = executarSQL($db, $sqlInsertHistorico);
        
            }
        function removeVinculo($sequencial){
            $db = Conexao();   
            $sql ="
                DELETE FROM sfpc.tbplanejamentovinculodfd where cpldfdsequ = $sequencial;
            ";
        
            $resultado = executarSQL($db, $sql);
        }




/**
 * [frontController description]
 */
function frontController()
{
    $tpl = new TemplatePaginaPadrao("templates/CadAgruparDFD.html", "Planejamento > DFD > Agrupar");
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
