<?php

/**
 * Portal de Compras
 * Programa: CadAnalisarDFDSelecionar.php
 * Autor: Osmar Celestino
 * Data: 09/12/2022
 * Objetivo: Programa para Analisar de DFD
 * Tarefa Redmine: #275876
 * ---------------------------------------------------------------------------
 * Alterado: Diógenes Dantas
 * Data:     13/12/2022
 * Objetivo: CR 275574
 * ---------------------------------------------------------------------------
 * Alterado: João Madson
 * Data: 04/01/2023
 * Tarefa: 276611
 * ---------------------------------------------------------------------------
 * Alterado: Osmar Celestino && Lucas Vicente
 * Data: 22/03/2023
 * Tarefa: Ajustes Documentos de Teste Planejamento
 * ---------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
// ini_set('display_errors', 1);
require "ClassPlanejamento.php";
include '../app/export/ExportaCSV.php';
include '../app/export/ExportaXLS.php';


function consultaAno()
{
    $db = Conexao();
    $sql = "select distinct apldfdanod from sfpc.tbplanejamentodfd";
    $resultado = executarSQL($db, $sql);
    $dados = array();

    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
    $dados[] = $retorno;
    }

    return $dados;
}

function plotarUnidade(TemplatePaginaPadrao  $tpl, $OrgaoLicitanteCodigo)
{
    $db = Conexao();
    $cusupocodi = $_SESSION['_cusupocodi_'];
    $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc, org.aorglicnpj  ";
    $sql .= " FROM	sfpc.tborgaolicitante org "; 
    // $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
    // $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
    $sql .= " WHERE			org.forglisitu = 'A' ";
    // $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
    $sql .= " ORDER BY		org.eorglidesc ASC";
    $resultado = executarSQL($db, $sql);
    $dados = array();
    $retorno = array();
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
        $dados[] = $retorno;
        $tpl->VALOR_ID_ORGAO_LICITANTE = $retorno->corglicodi;
        $tpl->VALOR_NOME_ORGAO_LICITANTE = $retorno->eorglidesc;
        if ($retorno->corglicodi == $OrgaoLicitanteCodigo) {
            $tpl->VALOR_ORGAO_SELECTED = 'selected="selected"';
        } else {
            $tpl->clear('VALOR_ORGAO_SELECTED');
        }
        $tpl->block('BLOCO_ITEM_ORGAO_LICITANTE');
    }
    $tpl->block('BLOCO_ORGAO_LICITANTE');
    $db->disconnect();
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
     $anoSelecionado = $_POST['selectAnoPCA'];
     $tpl->ANO_VALUE = '';
     $tpl->ANO_TEXT = 'Selecione o Ano...';
     $tpl->ANO_SELECTED = "selected";
     $tpl->block("BLOCO_ANO");

     foreach ($anos as $ano) {
         $tpl->ANO_VALUE = $ano->apldfdanod;
         $tpl->ANO_TEXT = $ano->apldfdanod;

         // Vendo se a opção atual deve ter o atributo "selected"
         if ($anoSelecionado === $ano->apldfdanod) {
             $tpl->ANO_SELECTED = "selected";
         } else {
             // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
             $tpl->clear("ANO_SELECTED");
         }
         $tpl->block("BLOCO_ANO");
     }
 }

 function consultaDFD()
 {  

     $db = Conexao();
     $sql = "
            SELECT distinct plandfd.*, org.eorglidesc, classe.eclamsdesc, sitdfd.eplsitnome, org.aorglicnpj
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi";
    if($_POST["DFDagrupador"] == "1"){
        $sql .= " inner join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                ";
    }else if($_POST["DFDagrupador"] == "2"){
        //Vai trazer tudo que não esteja na tabela agrup mas esteja na tabela plandfd
        $sql .= " left outer join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                ";
    }
    
    $sql .= " where plandfd.cplsitcodi in (3, 7)
        ";

    if($_POST["DFDagrupador"] == "2"){
        //É preciso adicionar esta condição apra que ele traga tudo da tabela plandfd que não tenha sido encontrado na tabela agrup que seja não nulo.
        $sql .= " AND agrup.cpldfdsequ IS NULL ";
    }    
    $sqlWhere = "";
    if(!empty($_POST["numeroDFD"])){
        $sqlWhere .= " and plandfd.cpldfdnumf = '".$_POST['numeroDFD']."' ";
    }else{
        if(!empty($_POST['selectAreaReq'])){
            if(count($_POST['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                for($i=0; $i<count($_POST['selectAreaReq']); $i++){
                    $corglicodi .= $_POST['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($_POST['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " and plandfd.corglicodi in (".$corglicodi.") ";//não pode ter espaço após para garantir na checagem para não faltar where
            }else{
                $sqlWhere .= " and plandfd.corglicodi = '".$_POST['selectAreaReq']."' ";//não pode ter espaço após para garantir na checagem para não faltar where
            }

        }

        if (!empty($_POST["cclamscodi"]) && !empty($_POST["cgrumscodi"])) {
            $sqlWhere .= " and plandfd.cclamscodi = '".$_POST['cclamscodi']."' and plandfd.cgrumscodi = '".$_POST['cgrumscodi']."' ";
        }

        if (!empty($_POST["selectAnoPCA"])) {
            $sqlWhere .= " and plandfd.apldfdanod = '".$_POST['selectAnoPCA']."' ";
        }
        if (!empty($_POST["cnpj"])) {
            $cnpj = RemoveFormatoCPF_CNPJ($_POST['cnpj']);
            $sql .= " and org.aorglicnpj ='". $cnpj."' ";
        }

        if (!empty($_POST["selectSitDFD"])) {
            $sqlWhere .= " and plandfd.cplsitcodi = '".$_POST['selectSitDFD']."' ";
        }

        if (!empty($_POST["grauPrioridade"])) {
            $sqlWhere .= " and plandfd.fpldfdgrau = '".$_POST['grauPrioridade']."' ";
        }
        
        if (!empty($_POST["DataIni"])) {
            $DataIniConv = DataInvertida($_POST['DataIni']);  
            $sqlWhere .= " and plandfd.dpldfdpret >= '".$_POST['DataIni']."' ";
        }

        if (!empty($_POST["DataFim"])) {
            $sqlWhere .= " and plandfd.dpldfdpret <= '".$_POST['DataFim']."' ";
        }
        // if (!empty($_POST["vincular"])) {
        //     $seqVinculados = 0;
        //     $sqlvinc = "select cpldfdsequ, cplvincodi from sfpc.tbplanejamentodfd where cpldfdnumf = '".$_POST["vincular"]."'";
        //     $resultado = executarSql($db, $sqlvinc);
        //     $dfdBase = 0;
        //     $resultado->fetchInto($dfdBase, DB_FETCHMODE_OBJECT);
        //     $seqVinculados = $dfdBase->cpldfdsequ;
        //     if(!empty($dfdBase->cplvincodi)){
        //         $sqlSequs = "select cpldfdsequ from sfpc.tbplanejamentovinculodfd where cplvincodi = '".$dfdBase->cplvincodi."'";
        //         $resultado = executarSql($db, $sqlSequs);
        //         $sequs = 0;
        //         while ($resultado->fetchInto($sequs, DB_FETCHMODE_OBJECT)) {
        //             $seqVinculados .= ", ".$sequs->cpldfdsequ;
        //         }
        //     }
        //     $sqlWhere .= " and plandfd.cpldfdsequ in ($seqVinculados)";
        // }
        //limpa o ultimo and para não quebrar a query
        // $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
    }    
    $sql .= $sqlWhere;
    $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
    
    $resultado = executarSql($db, $sql);
    $retorno = array();
    $dados = array();
    while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
        $dados[] = $retorno;
    }
    return $dados;
 }

 function processDevolver(){
     if($_POST['sequencial']){
        $sequencial = $_POST['sequencial'];
        alteraSituacao($sequencial[0],4);
        inserirHistoricoSituacao($sequencial[0],4,$_POST['Justificativa']);
     }
 }

 function plotarJustificativa(TemplatePaginaPadrao  $tpl){
    $tpl->block('BLOCO_JUSTIFICATIVA');
 }
 function inserirHistoricoSituacao ($cpldfdsequ,$cplsitcodi,$eplhsijust = null){
    if($cplsitcodi == 6){
        $eplhsijust = "null";
    }else{
        $eplhsijust = "'".strtoUpper2($eplhsijust)."'";
    }
    $db = Conexao();
    $sql = "select max(cplhsisequ) from sfpc.tbplanejamentohistoricosituacaodfd";
    $retorno = executarSQL($db, $sql);
    $result = 0;
    $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
    $cplhsisequ = $result->max + 1;

    $sql ="
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
            ".$cplhsisequ.",
            ".$cpldfdsequ.",
            ".$cplsitcodi.",
            ".$eplhsijust.", 
            now(),
            ".$_SESSION['_cusupocodi_'].",
            now()
        )
    ";
    $retorno = executarSQL($db, $sql);
    $db->disconnect();
    return;
 }

function montaHTMLAnalisar($dadosDFD,$tpl){
    $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->eorglidesc;
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
                    <td align="left" colspan="8" class="titulo3" width="900px"><b> Não há DFDs para serem Analisados.</b></td>
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
                            <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                            <td class="textonormal" bgcolor="#DCEDF7" align="center">CNPJ</td>
                            <td class="tdResultTitulo" id="cabDescClasse">CLASSE</td>
                            <td class="textonormal" bgcolor="#DCEDF7" align="center">ESTIMATIVA DE VALOR</td>
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
                    $urlDFD = "ConsultaDFD.php?dfdSelected=$dado->cpldfdnumf";

                    if($dado->fpldfdgrau == 1){
                        $grauprioridade = "ALTO";
                    }else if($dado->fpldfdgrau == 2){
                        $grauprioridade = "MÉDIO";
                    }else if($dado->fpldfdgrau == 3){
                        $grauprioridade = "BAIXO";
                    }

                    $cclamscodi = empty($dado->cclamscodi)? "-": $dado->cclamscodi;
                    $descclasse = empty($dado->eclamsdesc)? "-": $dado->eclamsdesc;
                    $dataPrevConclusao = ($dataPrevConclusao == "01/01/1970")? "-": $dataPrevConclusao;
                    $cnpj = FormataCNPJ($dado->aorglicnpj);
                    $funcJs = "javascript:AbreJanela('ConsultaDFD.php?dfdSelected=$dado->cpldfdnumf', 800, 350);";
                    $html.='<tr id="resultados">
                            <td class="tdresult" id="resIdDFD"><input type="checkbox" id="radioSequencial" class="checkbox" name="sequencial[]" value="'.$dado->cpldfdsequ.'" style="text-transform: capitalize"><a href=# onclick="'.$funcJs.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</a></input></td>
                            <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                            <td class="textonormal" id="cnpj_result" align="center">'.$cnpj.'</td>
                            <td class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                            <td class="textonormal" align="center">R$'.number_format($dado->cpldfdvest, 2, ',', '.').'</td>
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
        $tpl->block("BOTAO_ANALISAR");
        $tpl->block("BLOCO_EXPORT");
    }
    $tpl->RESULTADO = $html;
}

function processPesquisar(TemplatePaginaPadrao $tpl){
     $dados = consultaDFD();
     montaHTMLAnalisar($dados,$tpl); 
}

     /**
      * [proccessPrincipal description]
      * @param TemplateAppPadrao $tpl [description]
      * @return [type]                 [description]
      */
 function proccessPrincipal(TemplatePaginaPadrao $tpl)
 {
     // Variáveis com o global off #
     if ($_SERVER ['REQUEST_METHOD'] == 'GET') {
        unset($_SESSION['classe']);
        unset($_SESSION['classeSelecionadada']);
        unset($_SESSION["cclamscodi"]);
        unset($_SESSION["cgrumscodi"]);
        unset($_SESSION["eclamsdesc"]);
     }
     if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
        if($_POST['Botao'] == "limpar"){
            unset($_POST);
            unset($_SESSION['classe']);
            unset($_SESSION['classeSelecionadada']);
            unset($_SESSION["cclamscodi"]);
            unset($_SESSION["cgrumscodi"]);
            unset($_SESSION["eclamsdesc"]);
        }
         $OrgaoLicitanteCodigo = filter_var($_POST['selectAreaReq'], FILTER_VALIDATE_INT);
        if (!empty($_SESSION['classe']) && !is_null($_POST['radioClasse'])) {
            $selecClasse = $_POST['radioClasse'];
            $classe = $_SESSION['classe'][$selecClasse];
            $_SESSION['classeSelecionadada'] = $classe;
            $tpl->CODIGO_CLASSE    = $_SESSION["cclamscodi"] = $classe->cclamscodi;
            $tpl->CODIGO_GRUPO     = $_SESSION["cgrumscodi"] = $classe->cgrumscodi;
            $tpl->DECRICAO_CLASSE  = $_SESSION["eclamsdesc"] = $classe->eclamsdesc;
            unset($_SESSION['classe']);
        }else{
            $tpl->CODIGO_CLASSE    = $_POST['cclamscodi'];
            $tpl->CODIGO_GRUPO     = $_POST['cgrumscodi'];
            $tpl->DECRICAO_CLASSE  = $_POST['eclamsdesc'];
        }
        $tpl->NUMDFD = $_POST["numeroDFD"];
     } else {
         $Mens2 = $_GET ['Mens2'];
         $Tipo2 = $_GET ['Tipo2'];
         $Mensagem2 = urldecode($_GET ['Mensagem2']);
     }
     if ($_POST['cnpj']) {
         $tpl->CNPJ_PESQUISA = $_POST['cnpj'];
     }
     if ($_POST['DataIni']) {
         $tpl->VALOR_DATA_INICIO_CADASTRO = $_POST['DataIni'];
     }
     if ($_POST['DataFim']) {
         $tpl->VALOR_DATA_FIM_CADASTRO= $_POST['DataFim'];
     }

     if($_POST['grauPrioridade']==''){
         $tpl->PRIORIDADE_SELECTED_D = 'selected';
     }elseif($_POST['grauPrioridade']=='1'){
         $tpl->PRIORIDADE_SELECTED_A = 'selected';
     }elseif($_POST['grauPrioridade']=='2'){
         $tpl->PRIORIDADE_SELECTED_B = 'selected';
     }elseif($_POST['grauPrioridade']=='3'){
         $tpl->PRIORIDADE_SELECTED_C = 'selected';
     }

     if($_POST['DFDagrupador']==''){
         $tpl->PRIORIDADE_SELECTED_0 = 'selected';
     }elseif($_POST['DFDagrupador']=='1'){
         $tpl->PRIORIDADE_SELECTED_1 = 'selected';
     }elseif($_POST['DFDagrupador']=='2'){
         $tpl->PRIORIDADE_SELECTED_2 = 'selected';
     }

     plotarUnidade($tpl, $OrgaoLicitanteCodigo);
     $anos = consultaAno();
     plotarBlocoAno($anos, $tpl);

     if($_POST['Botao'] == 'Pesquisar'){
         processPesquisar($tpl);
     }
     if($_POST['Botao'] == 'Analisar'){
        processAnalisar();
        $tpl->block("BLOCO_SUCESSO_ANALISAR");
     }
     if($_POST['Botao'] == 'Devolver'){
        processDevolver();
        $tpl->block("BLOCO_SUCESSO_DEVOLVER");
     }
     if($_POST['Botao'] == 'ExportarPDF'){
        processExportarPDF($tpl);
     }
     if($_POST['Botao'] == 'ExportarCSV' || $_POST['Botao'] == 'ExportarXLS'){
        if($_POST['Botao'] == 'ExportarCSV'){
            processExportarPlanilha($tpl, "csv");
        }
        if($_POST['Botao'] == 'ExportarXLS'){
            processExportarPlanilha($tpl, "xls");
        }
        
     }


 }


 function alteraSituacao($sequencial, $situacao)
 {
    $db = Conexao();
    $sql = "update sfpc.tbplanejamentodfd set cplsitcodi= '" .$situacao."' where cpldfdsequ in ($sequencial)";
    $resultado = executarSQL($db, $sql);
    if ($resultado != False) {
        return True;
    } else {
        return false;
    }
 }

 function processAnalisar()
 {
    if ($_POST['sequencial']) {

        $todosSequenciais = '';
        foreach($_POST['sequencial'] as $sequencial){
            $todosSequenciais .= $sequencial.',';
        }

        $todosSequenciais = substr_replace($todosSequenciais,'',strripos($todosSequenciais,','));
  
        alteraSituacao($todosSequenciais,6);
        foreach($_POST['sequencial'] as $sequencial){
            inserirHistoricoSituacao($sequencial,6);
        }
        
        // header("Refresh: 0");
    }

 }
 function processExportarPDF($tpl)
 {
    $objPlanejamento = new Planejamento();

    $dados = consultaDFD();
    $htmlResultado = $objPlanejamento->montaHTMLAnalisarPDF($dados);
    $_SESSION['HTMLPDF'] = $htmlResultado;
    $_SESSION['HTMLPDFDownload'] = false;
    $_SESSION['HTMLPDFMudaOrientacao'] = true;
    echo "<script>window.open('../dompdf/GeraPdf.php', '_blank')</script>";
 }
 function processExportarPlanilha($tpl, $formatoExport)
 {  
    $nomeArquivo = "RelatorioDFDAnalisar";
    $cabecalho = array(
        "Número do DFD",
        "Ano do PCA",
        "Classe",
        "Data Prevista para Conclusão",
        "Tipo de Processo",
        "Grau de Prioridade",
        "Situação do DFD"
    );
    $dados = consultaDFD();
    $resultados = array();
    $conta = 0;
    foreach($dados as $dado){
        $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
        $tpProcesso = ($dado->fpldfdtpct=="D")? "Contratação Direta" : "Licitação";

        if($dado->fpldfdgrau == 1){
            $grauprioridade = "ALTO";
        }else if($dado->fpldfdgrau == 2){
            $grauprioridade = "MÉDIO";
        }else if($dado->fpldfdgrau == 3){
            $grauprioridade = "BAIXO";
        }

        
        
        $resultados[$conta][] = $dado->cpldfdnumf;
        $resultados[$conta][] = $dado->apldfdanod;
        // $resultados[$conta][] = $dado->cclamscodi;
        $resultados[$conta][] = $dado->eclamsdesc;
        $resultados[$conta][] = $dataPrevConclusao;
        $resultados[$conta][] = strtoUpper2($tpProcesso);
        $resultados[$conta][] = $grauprioridade;
        $resultados[$conta][] = $dado->eplsitnome;

        $conta++;
    }
    switch($formatoExport){
        case 'xls':
            $nomeArquivo.= '.xls';
            $export = new ExportaXLS($nomeArquivo, $cabecalho, $resultados);
        break; 
        case 'csv':
            $nomeArquivo.= '.csv';
            $export = new ExportaCSV($nomeArquivo, ';', $cabecalho, $resultados);
        break;
    }

    $export->download();
 }


     /**
      * [frontController description]
      */
 function frontController()
 {
     $tpl = new TemplatePaginaPadrao("templates/CadAnalisarDFDSelecionar.html", "Planejamento > DFD > Analisar");
     proccessPrincipal($tpl);
     $tpl->show();
 }
frontController();