<?php

/**
 * Portal de Compras
 * Programa: CadAnalisarDFD.php
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
 */

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}
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
    $database = Conexao();
    $sql = 'SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC';
    $result = $database->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $tpl->VALOR_ID_ORGAO_LICITANTE = $Linha[0];
            $tpl->VALOR_NOME_ORGAO_LICITANTE = $Linha[1];
            if ($Linha[0] == $OrgaoLicitanteCodigo) {
                $tpl->VALOR_ORGAO_SELECTED = 'selected="selected"';
            } else {
                $tpl->clear('VALOR_ORGAO_SELECTED');
            }
            $tpl->block('BLOCO_ITEM_ORGAO_LICITANTE');
        }
        $tpl->block('BLOCO_ORGAO_LICITANTE');
    }
    $database->disconnect();
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
     $tpl->ANO_VALUE = '';
     $tpl->ANO_TEXT = 'Selecione o ano';
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
     $sql = "SELECT distinct dfd.*, org.eorglidesc, org.aorglicnpj, classe.eclamsdesc, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS dfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
            inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi)
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
            where dfd.cplsitcodi in (3,9)";
     if($_POST['GerarNumeracaoAno']){
        $sql .= "and  dfd.apldfdanod =". $_POST['GerarNumeracaoAno'];
     }
     if($_POST['UnidadeCodigo']){
        $sql .= "and  dfd.apldfdanod =". $_POST['UnidadeCodigo'];
     }
     $cnpj = RemoveFormatoCPF_CNPJ($_POST['cnpj']);
     if($_POST['cnpj']){
        $sql .= "and  org.aorglicnpj ='". $cnpj."'";
     }
     $DataIniConv = DataInvertida($_POST['DataInicio']);          // Retorna aaaa-mm-dd
     $DataFimConv = DataInvertida($_POST['DataFim']);
     if(!empty($_POST['DataInicio']) and !empty($_POST['DataFim'])){
        $sql .= "and  dfd.dpldfdpret BETWEEN '".$DataIniConv."' AND '".$DataFimConv."' ";;
     }
     if($_POST['prioridade']){
        $sql .= "and  dfd.fpldfdgrau =   ' ".$_POST['prioridade']."'";
     }

     $resultado = executarSQL($db, $sql);
     $dados = array();
     while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
         $dados[] = $retorno;
     }
     return $dados;
 }

 function processDevolver(){
     if($_POST['sequencial']){
         alteraSituacao($_POST['sequencial'],7);
     }
 }

 function plotarJustificativa(TemplatePaginaPadrao  $tpl){
    $tpl->block('BLOCO_JUSTIFICATIVA');
 }

 function processPesquisar(TemplatePaginaPadrao $tpl){
     $dados = consultaDFD();
     if ($dados) {
         foreach ($dados as $dado) {
             if ($dado->fpldfdgrau == 'A') {
                 $grau = 'ALTA';
             } elseif ($dado->fpldfdgrau == 'M') {
                 $grau = 'MEDIA';
             } else {
                 $grau = 'BAIXA';
             }
             $cnpj = FormataCNPJ($dado->aorglicnpj);
             $tpl->ANOPCA = $dado->apldfdanod;
             $tpl->IDENTIFICADOR = $dado->cpldfdnumf;
             $tpl->CODIGOCLASSE = $dado->cclamscodi;
             $tpl->DESCRICAOCLASSE = $dado->eclamsdesc;
             $tpl->UNIDADE = $dado->eorglidesc;
             $tpl->CNPJ = $cnpj;
             $tpl->ESTIMATIVA_VALOR = number_format($dado->cpldfdvest, 2, ',', '.');
             $tpl->DATACONCLUSAO = date('d/m/Y', strtotime($dado->dpldfdpret));
             $tpl->TIPOPROCESSO = ($dado->fpldfdtpct == 'D') ? 'DIRETA' : 'LICITAÇÃO';
             $tpl->GRAU = $grau;
             $tpl->SITUACAO = $dado->eplsitnome;
             $tpl->SEQUENCIAL = $dado->cpldfdsequ;
             $tpl->block("BLOCO_DADOS");
         }
         $tpl->block("BLOCO_RESULTADO");
         $tpl->block("BOTAO_ANALISAR");
     } else {
         $tpl->block("BLOCO_SEM_RESULTADO");
     }
 }

     /**
      * [proccessPrincipal description]
      * @param TemplateAppPadrao $tpl [description]
      * @return [type]                 [description]
      */
 function proccessPrincipal(TemplatePaginaPadrao $tpl)
 {
     // Variáveis com o global off #
     if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
         $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_VALIDATE_INT);

     } else {
         $Mens2 = $_GET ['Mens2'];
         $Tipo2 = $_GET ['Tipo2'];
         $Mensagem2 = urldecode($_GET ['Mensagem2']);
     }
     if ($_POST['cnpj']) {
         $tpl->CNPJ_PESQUISA = $_POST['cnpj'];
     }
     if ($_POST['DataInicio']) {
         $tpl->VALOR_DATA_INICIO_CADASTRO = $_POST['DataInicio'];
     }
     if ($_POST['DataFim']) {
         $tpl->VALOR_DATA_FIM_CADASTRO= $_POST['DataFim'];
     }

     if($_POST['prioridade']=='D'){
         $tpl->PRIORIDADE_SELECTED_D = 'selected';
     }elseif($_POST['prioridade']=='A'){
         $tpl->PRIORIDADE_SELECTED_A = 'selected';
     }elseif($_POST['prioridade']=='B'){
         $tpl->PRIORIDADE_SELECTED_B = 'selected';
     }else{
         $tpl->PRIORIDADE_SELECTED_C = 'selected';
     }

     plotarUnidade($tpl, $OrgaoLicitanteCodigo);
     $anos = consultaAno();
     plotarBlocoAno($anos, $tpl);

     if($_POST['Botao'] == 'Pesquisar'){
         processPesquisar($tpl);
     }
     if($_POST['Botao'] == 'Analisar'){
         processAnalisar();
     }
     if($_POST['Botao'] == 'Devolver'){
        processDevolver();
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


 function alteraSituacao($sequencial,$situacao)
 {
     $db = Conexao();
     $sql = "update sfpc.tbplanejamentodfd set cplsitcodi= '" .$situacao."' where cpldfdsequ = $sequencial";
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
         $status = alteraSituacao($_POST['sequencial'],6);
         header("Refresh: 0");
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
        "Código da Classe",
        "Descrição da Classe",
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
        $resultados[$conta][] = $dado->cclamscodi;
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
     $tpl = new TemplatePaginaPadrao("templates/CadAnalisarDFD.html", "Planejamento > Encaminhar DFD > Selecionar");
     proccessPrincipal($tpl);
     $tpl->show();
 }
frontController();