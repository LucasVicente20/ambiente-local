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
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.13.0-47-gc4c994b
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES DO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015 - 08/09/2015
 * Objetivo: CR 95756 - Pesquisa de Licitações por item de material ou serviço - vários programas
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data:     17/09/2015
 * Objetivo: CR 100458 - Mensagem de erro recorrente
 * Versão:   20150916_1550-1-gf471375
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     13/02/2019
 * Objetivo: Tarefa Redmine 208688
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     06/05/2019
 * Objetivo: Tarefa Redmine 216295
 * -----------------------------------------------------------------------------
 * Alterado: João Madson    
 * Data:     27/01/2021
 * Objetivo: CR #243182
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     27/05/2022 - 07/06/2022
 * Objetivo: Projeto Transparência
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     06/09/2022
 * Objetivo: CR 268483
 * ---------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     21/09/2022
 * Objetivo: CR 269010
 * ---------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     20/03/2023
 * Objetivo: CR 275575
 * ---------------------------------------------------------------------------
# Alterado: Osmar Celestino 
# Data:     02/06/2023
# Objetivo: CR 284097
# ---------------------------------------------------------------------------
 */

if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}
include '../licitacoes/funcoesLicitacoes.php';
//CAIO MELQUIADES - CLASSES EXPORT
require(dirname(__FILE__).'/export/ExportaCSV.php');
require(dirname(__FILE__).'/export/ExportaXLS.php');
require(dirname(__FILE__).'/export/ExportaODS.php');

/**

 *
 * @param  TemplateAppPadrao $tpl Template para renderização do Sistema
 * @param  integer $OrgaoLicitanteCodigo Código do Órgão Licitante
 * @return void
 */



function comissaoDescricao($db, $processo, $ano, $grupo, $comissao, $OrgaoLiciorgaoanteCodigo){
    $db = Conexao();
    $sql  = "SELECT  C.ECOMLIDESC as comissaodescricao ";  
    $sql .= " FROM ";
    $sql .= " SFPC.TBLICITACAOPORTAL D, SFPC.TBCOMISSAOLICITACAO C ";
    $sql .= "WHERE C.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "       AND D.CGREMPCODI = $grupo ";
    $sql .= "       AND D.CCOMLICODI = $comissao ";
    $sql .= "       AND D.ALICPOANOP = $ano ";
    $sql .= "       AND D.CLICPOPROC = $processo ";
    $sql .= "       AND D.CORGLICODI = $OrgaoLiciorgaoanteCodigo ";
    $result = executarTransacao($db,$sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->comissaodescricao;
}
function comissaoLocal($db, $processo, $ano, $grupo, $comissao, $OrgaoLiciorgaoanteCodigo){
    $db = Conexao();
    $sql  = "SELECT  C.ECOMLILOCA as localcomissao ";  
    $sql .= " FROM ";
    $sql .= " SFPC.TBLICITACAOPORTAL D, SFPC.TBCOMISSAOLICITACAO C ";
    $sql .= "WHERE C.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "       AND D.CGREMPCODI = $grupo ";
    $sql .= "       AND D.CCOMLICODI = $comissao ";
    $sql .= "       AND D.ALICPOANOP = $ano ";
    $sql .= "       AND D.CLICPOPROC = $processo ";
    $sql .= "       AND D.CORGLICODI = $OrgaoLiciorgaoanteCodigo ";
    $result = executarTransacao($db,$sql);
    $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
    return $row->localcomissao;
}

function plotarOrgaoLicitante(TemplateAppPadrao $tpl, $OrgaoLicitanteCodigo)
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
 * [plotarComissoes description]
 * @param  TemplateAppPadrao $tpl            [description]
 * @param  integer            $ComissaoCodigo [description]
 * @return [type]                            [description]
 */
function plotarComissoes(TemplateAppPadrao $tpl, $ComissaoCodigo)
{
    $database = Conexao();
    $sql = "SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ";
    $sql .= "FROM SFPC.TBCOMISSAOLICITACAO where ECOMLIDESC != 'CARONA EXTERNA' ORDER BY CGREMPCODI,ECOMLIDESC";

    $result = $database->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $tpl->VALOR_ID_COMISSAO = $Linha [0];
            $tpl->VALOR_NOME_COMISSAO = $Linha [1];
            if ($Linha [0] == $ComissaoCodigo) {
                $tpl->VALOR_COMISSAO_SELECTED = 'selected';
            } else {
                $tpl->clear('VALOR_COMISSAO_SELECTED');
            }
            $tpl->block('BLOCO_ITEM_COMISSAO');
        }
        $tpl->block('BLOCO_COMISSAO');
    }
    $database->disconnect();
}
/**
 * [plotarModalidade description]
 * @param  TemplateAppPadrao $tpl              [description]
 * @param  [type]            $ModalidadeCodigo [description]
 * @return [type]                              [description]
 */
function plotarModalidade(TemplateAppPadrao $tpl, $ModalidadeCodigo)
{
    $database = Conexao();
    $sql = 'SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE';
    $result = $database->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $tpl->VALOR_ID_MODALIDADE = $Linha[0];
            $tpl->VALOR_NOME_MODALIDADE = $Linha[1];
            if ($Linha [0] == $ModalidadeCodigo) {
                $tpl->VALOR_MODALIDADE_SELECTED = 'selected';
            } else {
                $tpl->clear('VALOR_MODALIDADE_SELECTED');
            }
            $tpl->block('BLOCO_ITEM_MODALIDADE');
        }
        $tpl->block('BLOCO_MODALIDADE');
    }
    $database->disconnect();
}

function PesquisarSCC($dados){
   
    $conexaoDb = Conexao();
    if($dados){
        $sql =  "SELECT DISTINCT scc.csolcosequ, scc.ctpcomcodi, scc.esolcoobje, scc.asolcoanos, scc.csolcocodi, cc.ccenpocorg, cc.ccenpounid, fc.nforcrrazs, scc.corglicodi, scc.carpnosequ, scc.fsolcorpcp, scc.clicpoproc, scc.ccomlicodi, scc.alicpoanop, org.eorglidesc as orgaodesc ";
        $sql .= " FROM sfpc.tbsolicitacaocompra AS scc INNER JOIN sfpc.tbcentrocustoportal AS cc ON scc.ccenposequ = cc.ccenposequ ";
        $sql .= " INNER JOIN sfpc.tbitemsolicitacaocompra iscc ON scc.csolcosequ = iscc.csolcosequ";
        $sql .= " INNER JOIN sfpc.tbfornecedorcredenciado AS fc ON iscc.aforcrsequ = fc.aforcrsequ ";
        $sql .= " INNER JOIN sfpc.tborgaolicitante org on scc.corglicodi = org.corglicodi "; 
        $sql .= " INNER JOIN sfpc.tbregistroprecoadesaodoc DOC ON scc.csolcosequ = DOC.csolcosequ";
        

        if($dados['item']){
            if($dados['tipoitem'] == 'M'){
                $sql .= " JOIN SFPC.TBMATERIALPORTAL MP ON MP.CMATEPSEQU = iscc.CMATEPSEQU ";
            }
            if($dados['tipoitem'] == 'S'){
                $sql .= " JOIN SFPC.TBSERVICOPORTAL SP ON SP.CSERVPSEQU = iscc.CSERVPSEQU ";
            }
        }

        $sql .= " where scc.csitsocodi IN (3,4) AND scc.ctpcomcodi = 5 ";

        if(!empty($dados['tipoitem']) && !empty($dados['item'])){
            $encoding = 'UTF-8';
    
            if ($dados['tipoitem'] == 'M') {
                $sql .= " AND MP.EMATEPDESC ILIKE '%" . mb_strtoupper($dados['item'], $encoding)."%'";
            } elseif ($dados['tipoitem'] == 'S') {
                $sql .= " AND SP.ESERVPDESC ILIKE '%" . mb_strtoupper($dados['item'], $encoding)."%'";
            }
        }

        if($dados["tipo_ata"] == "E"){
            $sql .= "AND ( ( scc.clicpoproc = 1 AND scc.ccomlicodi = 41 AND scc.alicpoanop = 2012 ) OR ( scc.carpnosequ IS NOT NULL AND scc.ccomlicodi IS NULL ) ) ";
        }
        if($dados["tipo_ata"] == "I"){
            $sql .= "AND ( scc.clicpoproc <> 1 AND scc.ccomlicodi <> 41 AND scc.alicpoanop <> 2012 )";
        }
        if($dados["tipo_ata"] == "A"){
            $sql .= "AND ( scc.clicpoproc IS NOT NULL AND scc.ccomlicodi IS NOT NULL AND scc.alicpoanop IS NOT NULL )";
        }

        $fornecedor = RetiraAcentos($dados['fornecedor']);
        if($dados['fornecedor']){
            if($dados['fornecedorSelect'] == "iniciado"){  
                $sql .= " AND fc.nforcrrazs ILIKE '".$dados['fornecedor']."%' OR fc.nforcrrazs ILIKE'".$fornecedor."%'";
            }else if($dados['fornecedorSelect'] == "contendo"){
                $sql .= " AND fc.nforcrrazs ILIKE '%".$dados['fornecedor']."%'OR fc.nforcrrazs ILIKE'".$fornecedor."%'";
            }
         }
       
        if($dados['fornecedorCpf']){
            
            if($dados['fornecedorSelectCpf'] == "Cpf"){
                $CnpjCpf = str_replace('.', '', str_replace('-', '', $dados['fornecedorCpf']));
                $sql .= " AND fc.aforcrccpf = '".$CnpjCpf."'";
            }else if($dados['fornecedorSelectCpf'] == "Cnpj"){
                $CnpjCpf = str_replace('.', '', str_replace('-', '', str_replace('/', '', $dados['fornecedorCpf'])));
                $sql .= " AND fc.aforcrccgc = '".$CnpjCpf."'";
            }
        }
        
        if(!empty($dados['comissao']) && $dados['tipo_ata'] == "I"){
            $sql .= " AND scc.ccomlicodi = ".$dados['comissao'];
        }

         $objeto = RetiraAcentos(strtoupper2($dados['objeto']));

        if (!empty($dados['objeto'])) {
            $sql .= " AND (scc.esolcoobje ILIKE '%".$objeto."%' OR scc.esolcoobje ILIKE '%".$dados['objeto']."%')";
        }

        if (!empty($dados['tipoitem']) && !empty($dados['item'])) {
            $encoding = 'UTF-8';
    
            if ($dados['tipoitem'] == 'M') {
                $sql .= " AND MP.EMATEPDESC ILIKE '%" . mb_strtoupper($dados['item'], $encoding)."%'";
            } elseif ($dados['tipoitem'] == 'S') {
                $sql .= " AND SP.ESERVPDESC ILIKE '%" . mb_strtoupper($dados['item'], $encoding)."%'";
            }
        }
        
        if (!empty($dados['processo'])) {      
    
            $sql .= " AND ARPI.CLICPOPROC = " . substr($dados['processo'], 0,4);
            $sql .= " AND ARPI.ALICPOANOP = " . substr($dados['processo'], -4);
            
        }
        $sql .= " AND doc.frpaddexcl <>  'S' ";
        if(!empty($dados['scc'])){
            $asolcoanos = substr($dados['scc'], -4);   //valores a partir da barra
            $csolcocodi = substr($dados['scc'], -9 , -5);
            $ccenpocorg = substr($dados['scc'], 0, 2);
            $ccenpounid = substr($dados['scc'], 2, 2);
            $sql .= " and scc.asolcoanos = $asolcoanos
                      and scc.csolcocodi = $csolcocodi
                      and cc.ccenpocorg = $ccenpocorg
                      and cc.ccenpounid = $ccenpounid";         
        }

        if(!empty($dados['orgao'])){
            $sql .= " and scc.corglicodi in (".$dados['orgao'].")";
        }
        
        if($dados['tipo_sarp'] != 'A'){
            $sql .= " and scc.fsolcorpcp	 = '".$dados['tipo_sarp']."'";
        }

        if($dados['tipo_sarp'] == 'A'){
            $sql .= " and scc.fsolcorpcp IS NOT NULL";
        }
        
        if(!empty($dados['data_ini']) && !empty($dados['data_fim'])){
            $sql .= " AND scc.tsolcodata BETWEEN '".$dados['data_ini']."' AND '".$dados['data_fim']."' ";
        }
        $sql .= " ORDER BY org.eorglidesc ASC, scc.asolcoanos DESC";
       
        $resultado = executarSQL($conexaoDb, $sql);
        $dadosPesquisa = array();
       //osmar
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosPesquisa[] = (object) array(
                            'csolcosequ'=> $retorno->csolcosequ,
                            'ctpcomcodi'=> $retorno->ctpcomcodi,
                            'esolcoobje'=> $retorno->esolcoobje,
                            'asolcoanos'=> $retorno->asolcoanos,
                            'csolcocodi'=> $retorno->csolcocodi,
                            'ccenpocorg'=> $retorno->ccenpocorg,
                            'ccenpounid'=> $retorno->ccenpounid,
                            'nforcrrazs'=> $retorno->nforcrrazs,
                            'carpnosequ'=> $retorno->carpnosequ,
                            'orgaodesc' => $retorno->orgaodesc,
                            'fsolcorpcp'=> $retorno->fsolcorpcp,
                            'clicpoproc'=> $retorno->clicpoproc,
                            'ccomlicodi'=> $retorno->ccomlicodi,
                            'alicpoanop'=> $retorno->alicpoanop,
                    );
        }
    }
        return $dadosPesquisa;
    }


/**
 * [proccessPrincipal description]
 * @param  TemplateAppPadrao $tpl [description]
 * @return [type]                 [description]
 */
function proccessPrincipal(TemplateAppPadrao $tpl)
{
    // Variáveis com o global off #
    if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
        $Botao = $_POST ['Botao'];
        $Objeto = $_POST['Objeto'];
        $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_VALIDATE_INT);
        $ComissaoCodigo = filter_var($_POST['ComissaoCodigo'], FILTER_VALIDATE_INT);
        $ModalidadeCodigo = $_POST ['ModalidadeCodigo'];
        $TipoItem = $_POST ['TipoItemLicitacao'];
        $processo = $_POST['processoLicitatorio'];
        $Item = $_POST ['Item'];
        $FornecedorPesquisa = strtoupper($_POST['fornecedor']);
        $tipoAta = $_POST['tipoAta'];
        $tipoSarp = $_POST['tipoSarp'];
        $Mens2 = $_POST ['Mens2'];
        $Tipo2 = $_POST ['Tipo2'];
        $fornecedorSelect = $_POST['fornecedorSelect'];
        $Mensagem2 = urldecode($_POST ['Mensagem2']);
        $dataIni = $_POST['DataIni'];
        $dataFim = $_POST['DataFim'];
        $scc = $_POST['scc'];
        $fornecedorCpf = $_POST['fornecedorCpf'];
        $fornecedorSelectCpf = $_POST['fornecedorSelectCpf'];
    } else {
        $Mens2 = $_GET ['Mens2'];
        $Tipo2 = $_GET ['Tipo2'];
        $Mensagem2 = urldecode($_GET ['Mensagem2']);
    }
    $ArrImun = array('IMUNIZAÇÃO', 'IMUNIZACAO', 'IMUNIZAÇAO', 'IMUNIZACÃO');
    $erro = false;
    $sineDie = 24; // Id fase Adiamento Sine Die
    if(!empty($scc)){
        $tpl->VALOR_SCC = $scc;
    }

    if (($Item == '') and ($TipoItem) != '') {
        $erro = true;
        $Mensagem2 = 'Atenção! Falta digitar o texto do Item.';
    }

    $tpl->VALOR_OBJETO_PESQUISA = ($_POST ['Objeto'])?$_POST ['Objeto']:'';
    $tpl->VALOR_FORNECEDOR_CPF = ($_POST ['fornecedorCpf'])?$_POST ['fornecedorCpf']:'';

    // Orgão licitante
    plotarOrgaoLicitante($tpl, $OrgaoLicitanteCodigo);
    // Comissões
    plotarComissoes($tpl, $ComissaoCodigo);
    // Modalidade

    if ($TipoItem == 'M') {
        $tpl->VALOR_TIPO_MATERIAL = 'selected';
    }
    if ($TipoItem == 'S') {
        $tpl->VALOR_TIPO_SERVICO = 'selected';
    }
    if ($TipoItem == 'A') {
        $tpl->AMBOS = 'selected';
    }

    if ($tipoAta == 'I') {
        $tpl->VALOR_TIPO_ATA_I= 'selected';
    }
    if ($tipoAta == 'E') {
        $tpl->VALOR_TIPO_ATA_E = 'selected';
    }
    if ($tipoAta == 'A') {
        $tpl->AMBOS = 'selected';
    }

    $tpl->VALOR_TIPO_FORNECEDOR_I= 'selected';
    if ($fornecedorSelect == 'iniciado') {
        $tpl->VALOR_TIPO_FORNECEDOR_I= 'selected';
    }
    if ($fornecedorSelect == 'contendo') {
        $tpl->VALOR_TIPO_FORNECEDOR_C = 'selected';
    }
    if ($fornecedorSelectCpf == 'Cpf') {
        $tpl->VALOR_TIPO_FORNECEDOR_CPF= 'selected';
    }
    if ($fornecedorSelectCpf == 'Cnpj') {
        $tpl->VALOR_TIPO_FORNECEDOR_CNPJ = 'selected';
    }
    if($FornecedorPesquisa){
        $tpl->VALOR_FORNECEDOR = $FornecedorPesquisa;
    }

    if ($tipoSarp == 'P') {
        $tpl->VALOR_PARTICIPANTE = 'selected';
    }
    if($tipoSarp == 'C'){
        $tpl->VALOR_CARONA = 'selected';
    }
    if($tipoSarp == 'A'){
        $tpl->AMBOS = 'selected';
    }

    $tpl->VALOR_TEXTO_ITEM = $Item;
    if(!empty($_POST['DataIni'])){
        $tpl->VALOR_DATA_INI = $_POST['DataIni'];
    }
    // else{
    //     $tpl->VALOR_DATA_INI = date("01/m/Y");
    // }
    if(!empty($_POST['DataFim'])){
        $tpl->VALOR_DATA_FIM = $_POST['DataFim'];
    }
    // else{
    //     $tpl->VALOR_DATA_FIM = date("30/m/Y");
    // }


    $DataIniConv = DataInvertida($dataIni);          // Retorna aaaa-mm-dd
    $DataFimConv = DataInvertida($dataFim);          // Retorna aaaa-mm-dd
    $DataIniConv = str_replace("-", "", $DataIniConv); // Retorna aaaammdd
    $DataFimConv = str_replace("-", "", $DataFimConv);

    $dados = array(
        'orgao' => $OrgaoLicitanteCodigo,
        'comissao' => $ComissaoCodigo,
        'item' => $Item,
        'tipoitem' => $TipoItem,
        'fornecedor' => $FornecedorPesquisa,
        'tipo_ata' => $tipoAta,
        'objeto' => $Objeto,
        'tipo_sarp' => $tipoSarp,
        'fornecedorSelect' => $fornecedorSelect,
        'processo' => $processo,
        'data_ini'=>$DataIniConv,
        'data_fim'=>$DataFimConv,
        'scc'     =>$scc,
        'fornecedorCpf'     =>$fornecedorCpf,
        'fornecedorSelectCpf'     =>$fornecedorSelectCpf,
    ); 

    $tipoAta = (empty($tipoAta)) ? "I" : $tipoAta;      
    $dadosPesquisa = PesquisarSCC($dados);

    $ultimoOrgaoPlotado = "";

    for ($i = 0; $i < count($dadosPesquisa); $i++){
        if ($dadosPesquisa[$i]->orgaodesc != '' && $ultimoOrgaoPlotado != $dadosPesquisa[$i]->orgaodesc) {
            
            $tpl->ORGAO_DESC = $dadosPesquisa[$i]->orgaodesc;
            $ultimoOrgaoPlotado = $dadosPesquisa[$i]->orgaodesc;
    
            $tpl->block("BLOCO_ORGAO_DESC");
            $tpl->block("BLOCO_CABECALHO");

        }
        
        if(($dadosPesquisa[$i]->clicpoproc == '1' && $dadosPesquisa[$i]->ccomlicodi == '41' && $dadosPesquisa[$i]->alicpoanop == '2012') || ($dadosPesquisa[$i]->carpnosequ != NULL && $dadosPesquisa[$i]->ccomlicodi == NULL)){
            $tipoAta = 'EXTERNA';
        }else{
            $tipoAta = 'INTERNA';
        }

        $codigoDinamico = str_pad($dadosPesquisa[$i]->csolcocodi,4,'0',STR_PAD_LEFT);
        $codigoDinamicoUni = str_pad($dadosPesquisa[$i]->ccenpounid,2,'0',STR_PAD_LEFT);
        $codigoOrgao = str_pad($dadosPesquisa[$i]->ccenpocorg,2,'0',STR_PAD_LEFT);
        $codigoscc = $codigoOrgao.$codigoDinamicoUni.'.'.$codigoDinamico.'/'.$dadosPesquisa[$i]->asolcoanos;
        $partes = explode('.', $codigoscc);
        if(strlen($partes[0]) < 4) {
            $partes[0] = str_pad($partes[0], 4, '0', STR_PAD_LEFT);
        }
        $tpl->SCC = $partes[0].'.'.$codigoDinamico.'/'.$dadosPesquisa[$i]->asolcoanos; ;
        $tpl->VALOR_OBJETO = $dadosPesquisa[$i]->esolcoobje;
        $tpl->TIPO_SOLICITACAO = ($dadosPesquisa[$i]->fsolcorpcp == "P") ? "PARTICIPANTE":"CARONA";
        $tpl->TIPO_ATA_DESC = $tipoAta;
        $tpl->FORNECEDOR = $dadosPesquisa[$i]->nforcrrazs;
        $tpl->CODIGO_SCC = $dadosPesquisa[$i]->csolcosequ;
        $tpl->TIPO_ATA = $tipoAta;
        $tpl->block('BLOCO_EXIBIR_COMISSAO');
        $tpl->block('BLOCO_RESULTADO_PESQUISA');
    }

    

} 


function proccessExport($formatoExport)
{
    
    
    if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
        // $Botao = $_POST ['Botao'];
        $Objeto = $_POST ['Objeto'];
        $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_VALIDATE_INT);
        $ComissaoCodigo = filter_var($_POST['ComissaoCodigo'], FILTER_VALIDATE_INT);
        $ModalidadeCodigo = $_POST ['ModalidadeCodigo'];
        $TipoItem = $_POST ['TipoItemLicitacao'];
        $processo = $_POST['processoLicitatorio'];
        $Item = $_POST ['Item'];
        $FornecedorPesquisa = $_POST['fornecedor'];
        $tipoAta = $_POST['tipoAta'];
        $tipoSarp = $_POST['tipoSarp'];
        $Mens2 = $_POST ['Mens2'];
        $Tipo2 = $_POST ['Tipo2'];
        $fornecedorSelect = $_POST['fornecedorSelect'];
        $Mensagem2 = urldecode($_POST ['Mensagem2']);
        $dataIni = $_POST['DataIni'];
        $dataFim = $_POST['DataFim'];
        $scc = $_POST['scc'];
        $fornecedorCpf = $_POST['fornecedorCpf'];
        $fornecedorSelectCpf = $_POST['fornecedorSelectCpf'];
    } else {
        $Mens2 = $_GET ['Mens2'];
        $Tipo2 = $_GET ['Tipo2'];
        $Mensagem2 = urldecode($_GET ['Mensagem2']);
    }
    $DataIniConv = DataInvertida($DataIni);          // Retorna aaaa-mm-dd
    $DataFimConv = DataInvertida($DataFim);          // Retorna aaaa-mm-dd
    $DataIniConv = str_replace("-", "", $DataIniConv); // Retorna aaaammdd
    $DataFimConv = str_replace("-", "", $DataFimConv);

    $erro = false;
    $sineDie = 24; // Id fase Adiamento Sine Die


    $Data = date('Y-m-d H:i:s');

    $dados = array(
        'orgao' => $OrgaoLicitanteCodigo,
        'comissao' => $ComissaoCodigo,
        'item' => $Item,
        'tipoitem' => $TipoItem,
        'fornecedor' => $FornecedorPesquisa,
        'tipo_ata' => $tipoAta,
        'objeto' => $Objeto,
        'tipo_sarp' => $tipoSarp,
        'fornecedorSelect' => $fornecedorSelect,
        'processo' => $processo,
        'data_ini'=>$DataIniConv,
        'data_fim'=>$DataFimConv,
        'scc'     => $scc,
        'fornecedorCpf'     =>$fornecedorCpf,
        'fornecedorSelectCpf'     =>$fornecedorSelectCpf,
    ); 

    $dadosPesquisa = PesquisarSCC($dados);

        $cabecalho = array('SCC', 'OBJETO','TIPO SOLICITACAO','TIPO ATA','FORNECEDOR');
        $linhas = array();
            foreach($dadosPesquisa as $dados) {
                $tipoSolicitacao = ($tipoSarp == "P")?"PARTICIPANTE":"CARONA";
                $ata = ($tipoAta == "I")?"INTERNA":"EXTERNA";
            
                $codigoDinamico = str_pad($dados->csolcocodi,2,'0',STR_PAD_LEFT);
                $codigoDinamico = str_pad($dados->csolcocodi,4,'0',STR_PAD_LEFT);
                $codigoDinamicoUni = str_pad($dados->ccenpounid,2,'0',STR_PAD_LEFT);
                $scc = $dados->ccenpocorg.$codigoDinamicoUni.'.'.$codigoDinamico.'.'.$dados->asolcoanos;
                
                array_push($linhas, array(
                    $scc, 
                    $dados->esolcoobje,
                    $tipoSolicitacao,
                    $ata,
                    $dados->nforcrrazs,

                ));
    }
            
   
            $nomeArquivo = 'pcr_portal_compras_processo_adesao_atas';

            $export = null;

            switch($formatoExport){
                case 'xls':
                    $nomeArquivo.= '.xls';
                    $export = new ExportaXLS($nomeArquivo, $cabecalho, $linhas);
                break;
                case 'ods':
                    $nomeArquivo.= '.ods';
                    $export = new ExportaODS($nomeArquivo, $cabecalho, $linhas);
                break;
                case 'txt':
                        $nomeArquivo.= '.txt';
                        $export = new ExportaCSV($nomeArquivo, '|', $cabecalho, $linhas);
                break;
                case 'csv':
                default:
                    $nomeArquivo.= '.csv';
                    $export = new ExportaCSV($nomeArquivo, ';', $cabecalho, $linhas);
            }

            $export->download();
            //$tpl->exibirMensagemFeedback('Nenhuma ocorrência encontrada.', 1);
        }   
/**
 * [frontController description]
 */
function frontController()
{
    $tpl = new TemplateAppPadrao(CAMINHO_SISTEMA.'app/templates/ConsRegistroPrecoAdesaoAtas.html', 'ConsRegistroPrecoAdesaoAtas');
    $botao = isset($_REQUEST ['BotaoAcao']) ? $_REQUEST ['BotaoAcao'] : 'Principal';
    
    switch ($botao) {
        case 'AbreDocumentos':
            processAbreDocumentos();
            break;
        case 'LimparTela':
            proccessPrincipal($tpl);
            break;

        //CAIO MELQUIADES - DIRENCIONANDO PARA FUNCAO DE EXPORTAR PARA PLANILHA
        case 'Exportar':
            $formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';
            proccessExport($formatoExport);
            break;
    
        case 'Principal':
        default:
            proccessPrincipal($tpl);
    }

    $tpl->show();
}

frontController();