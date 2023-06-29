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
 * Alterado: João Madson 
 * Data:     02/09/2022
 * Objetivo: 268355 
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/02/2023
 * Objetivo: Tarefa Redmine 278667
 * -----------------------------------------------------------------------------
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
function localCertame($processo,$ano,$grupo,$comissao,$orgao,$db){
    $sql = "SELECT ELICPOLOCA";
    $sql .= " FROM SFPC.TBLICITACAOPORTAL";
    $sql .= " WHERE ALICPOANOP = $ano ";
    $sql .= " AND CLICPOPROC = $processo ";
    $sql .= " AND CGREMPCODI = $grupo ";
    $sql .= " AND CCOMLICODI = $comissao ";
    $sql .= " AND CORGLICODI = $orgao";
    $result	= executarTransacao($db, $sql);
	$row	= $result->fetchRow();
	return $row[0];

}

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
    $sql = 'SELECT CCOMLICODI, ECOMLIDESC, CGREMPCODI ';
    $sql .= 'FROM SFPC.TBCOMISSAOLICITACAO ORDER BY CGREMPCODI,ECOMLIDESC';
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



/**
 * [proccessPrincipal description]
 * @param  TemplateAppPadrao $tpl [description]
 * @return [type]                 [description]
 */
function proccessPrincipal(TemplateAppPadrao $tpl)
{
    // Variáveis com o global off #
    if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
        // $Botao = $_POST ['Botao'];
        $Objeto = $_POST ['Objeto'];
        $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_VALIDATE_INT);
        $ComissaoCodigo = filter_var($_POST['ComissaoCodigo'], FILTER_VALIDATE_INT);
        $ModalidadeCodigo = $_POST ['ModalidadeCodigo'];
        $TipoItemLicitacao = $_POST ['TipoItemLicitacao'];
        $Item = $_POST ['Item'];

        $Mens2 = $_POST ['Mens2'];
        $Tipo2 = $_POST ['Tipo2'];
        $Mensagem2 = urldecode($_POST ['Mensagem2']);
    } else {
        $Mens2 = $_GET ['Mens2'];
        $Tipo2 = $_GET ['Tipo2'];
        $Mensagem2 = urldecode($_GET ['Mensagem2']);
    }
    $ArrImun = array('IMUNIZAÇÃO', 'IMUNIZACAO', 'IMUNIZAÇAO', 'IMUNIZACÃO');
    $erro = false;
    $sineDie = 24; // Id fase Adiamento Sine Die

    if (($Item == '') and ($TipoItemLicitacao) != '') {
        $erro = true;
        $Mensagem2 = 'Atenção! Falta digitar o texto do Item.';
    }

    $tpl->VALOR_OBJETO_PESQUISA = $Objeto;

    // Orgão licitante
    plotarOrgaoLicitante($tpl, $OrgaoLicitanteCodigo);
    // Comissões
    plotarComissoes($tpl, $ComissaoCodigo);
    // Modalidade
    plotarModalidade($tpl, $ModalidadeCodigo);

    if ($TipoItemLicitacao == '1') {
        $tpl->VALOR_TIPO_LICITACAO_1_SELECTED = 'selected';
    }
    if ($TipoItemLicitacao == '2') {
        $tpl->VALOR_TIPO_LICITACAO_2_SELECTED = 'selected';
    }

    $tpl->VALOR_TEXTO_ITEM = $Item;

    $Data = date('Y-m-d H:i:s');

    if (!$erro) {
        $database = Conexao();
        $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, LICPO.CLICPOPROC, LICPO.ALICPOANOP, ';
        $sql  .= ' LICPO.CLICPOCODL, LICPO.ALICPOANOL, LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC, ';
        $sql  .= ' LICPO.CGREMPCODI, LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, COLIC.ACOMLINFAX, LICPO.CMODLICODI, LICPO.VLICPOVALE  ';
        $sql  .= ' FROM SFPC.TBLICITACAOPORTAL LICPO, SFPC.TBORGAOLICITANTE ORGLIC, SFPC.TBGRUPOEMPRESA GRUPRE, ';
        $sql  .= ' SFPC.TBCOMISSAOLICITACAO COLIC,SFPC.TBMODALIDADELICITACAO MODLIC';
        $sql  .= " WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI AND LICPO.FLICPOSTAT = 'A' ";
        $sql  .= ' AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI AND LICPO.CCOMLICODI = COLIC.CCOMLICODI  ';
        $sql  .= " AND LICPO.CMODLICODI = MODLIC.CMODLICODI AND LICPO.TLICPODHAB >= '$Data' ";

        if (($Item != '') and ($TipoItemLicitacao == '1')) {
            $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ';
            $sql .= ' LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ';
            $sql .= ' LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ';
            $sql .= ' LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ';
            $sql .= ' COLIC.ACOMLINFAX, LICPO.CMODLICODI, LICPO.VLICPOVALE  ';
            $sql .= ' FROM  SFPC.TBORGAOLICITANTE ORGLIC, ';
            $sql .= ' SFPC.TBGRUPOEMPRESA GRUPRE,  ';
            $sql .= ' SFPC.TBCOMISSAOLICITACAO COLIC, ';
            $sql .= ' SFPC.TBMODALIDADELICITACAO MODLIC, ';
            $sql .= ' SFPC.TBLICITACAOPORTAL LICPO, ';
            $sql .= ' SFPC.TBITEMLICITACAOPORTAL ILIC,';
            $sql .= ' SFPC.TBMATERIALPORTAL MAT ';
            $sql .= ' WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI ';
            $sql .= " AND LICPO.FLICPOSTAT = 'A'  AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ";
            $sql .= ' AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ';
            $sql .= ' AND LICPO.CMODLICODI = MODLIC.CMODLICODI ';
            $sql .= " AND LICPO.TLICPODHAB >= '$Data'";
            $sql .= ' AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ';
            $sql .= ' AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ';
            $sql .= ' AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ';
            $sql .= ' AND LICPO.CORGLICODI = ILIC.CORGLICODI ';
            $sql .= ' AND ILIC.CMATEPSEQU  = MAT.CMATEPSEQU  ';
            $sql .= " AND(MAT.EMATEPDESC ILIKE '%".strtoupper2($Item)."%')";
        }
        if (($Item != '') and ($TipoItemLicitacao == '2')) {
            $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ';
            $sql .= ' LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ';
            $sql .= ' LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ';
            $sql .= ' LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ';
            $sql .= ' COLIC.ACOMLINFAX, LICPO.CMODLICODI, LICPO.VLICPOVALE ';
            $sql .= ' FROM  SFPC.TBORGAOLICITANTE ORGLIC,';
            $sql .= ' SFPC.TBGRUPOEMPRESA GRUPRE,  ';
            $sql .= ' SFPC.TBCOMISSAOLICITACAO COLIC ,';
            $sql .= ' SFPC.TBMODALIDADELICITACAO MODLIC, ';
            $sql .= ' SFPC.TBLICITACAOPORTAL LICPO ,';
            $sql .= ' SFPC.TBITEMLICITACAOPORTAL ILIC,';
            $sql .= ' SFPC.TBSERVICOPORTAL SERV ';
            $sql .= ' WHERE   LICPO.CORGLICODI = ORGLIC.CORGLICODI ';
            $sql .= " AND LICPO.FLICPOSTAT = 'A' ";
            $sql .= ' AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ';
            $sql .= ' AND LICPO.CMODLICODI = MODLIC.CMODLICODI ';
            $sql .= ' AND LICPO.TLICPODHAB >= clock_timestamp()';
            $sql .= ' AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ';
            $sql .= ' AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ';
            $sql .= ' AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ';
            $sql .= ' AND LICPO.CORGLICODI = ILIC.CORGLICODI ';
            $sql .= ' AND ILIC.CSERVPSEQU  = SERV.CSERVPSEQU ';
            $sql .= " AND(SERV.ESERVPDESC ILIKE '%".strtoupper2($Item)."%') ";
        }

        if ($Objeto != '') {
            if(in_array(strtoupper($Objeto), $ArrImun)){
                $sql .= " AND (LICPO.XLICPOOBJE ILIKE '%$ArrImun[0]%' OR LICPO.XLICPOOBJE ILIKE '%$ArrImun[1]%' OR LICPO.XLICPOOBJE ILIKE '%$ArrImun[2]%' OR LICPO.XLICPOOBJE ILIKE '%$ArrImun[3]%')";
            }else{
                $sql .= " AND ( LICPO.XLICPOOBJE ILIKE '%".strtoupper2($Objeto)."%' OR LICPO.XLICPOOBJE ILIKE '%".RetiraAcentos(strtoupper2($Objeto))."%')";
            }
        }
        if ($ComissaoCodigo != '') {
            $sql .= " AND LICPO.CCOMLICODI = $ComissaoCodigo ";
        }
        if ($ModalidadeCodigo != '') {
            $sql .= " AND LICPO.CMODLICODI = $ModalidadeCodigo ";
        }
        if ($OrgaoLicitanteCodigo != '') {
            $sql .= " AND LICPO.CORGLICODI = $OrgaoLicitanteCodigo ";
        }

        $sql .= ' ORDER BY LICPO.TLICPODHAB ASC, GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC,  LICPO.ALICPOANOP, LICPO.CLICPOPROC';
        
        
        $result = $database->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $result->numRows();
        }

        if ($Rows != 0) {
            while ($Linha = $result->fetchRow()) {
                // Verificar última fase
                $ultimaFase = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $database);
                $localCertame = localCertame($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $database);
                if(empty($localCertame)){
                    if($Linha[16] == 14){
                        $localCertame = ' <a href="https://www.licitacoes-e.com.br/aop/index.jsp" target="_blank">https://www.licitacoes-e.com.br/aop/index.jsp</a>';
                    }else{
                        $localCertame = comissaoLocal($database, $Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
                    }
                }
                $valorEstimado = totalValorEstimado($database, $Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
                $comissaoDesc = comissaoDescricao($database,$Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
                if(empty($valorEstimado) && $Linha[10] == 2){
                    $hintValor = 'De acordo com o Art. 34 da Lei 13.303/2016, o valor estimado do contrato a ser celebrado pela empresa pública ou pela sociedade de economia mista será sigiloso, facultando-se à contratante, mediante justificação na fase de preparação prevista no inciso I do art. 51 desta Lei, conferir publicidade ao valor estimado do objeto da licitação, sem prejuízo da divulgação do detalhamento dos quantitativos e das demais informações necessárias para a elaboração das propostas. 
Obs. A informação relativa ao valor estimado do objeto da licitação, ainda que tenha caráter sigiloso, será disponibilizada a órgãos de controle externo e interno, devendo a empresa pública ou a sociedade de economia mista registrar em documento formal sua disponibilização aos órgãos de controle, sempre que solicitado.';
                }elseif(empty($valorEstimado) && $Linha[1] == 'CREDENCIAMENTO'){
                    $hintValor = 'O credenciamento é um procedimento administrativo no qual a Administração convoca interessados para, segundo condições previamente definidas e divulgadas em edital, credenciarem-se como prestadores de serviços ou beneficiários de um negócio futuro e eventual a ser ofertado. Atendidas às condições fixadas, os interessados serão credenciados em condição de igualdade para executar o objeto. ';
                }elseif(empty($valorEstimado) || !empty($valorEstimado)){
                    $hintValor ='';
                }
                
                $valorEstimado = empty($valorEstimado) ? $Linha[17] : $valorEstimado;
                
                $valorEstimado = converte_Valor($valorEstimado);


                if($ultimaFase != $sineDie) {
                    $LicitacaoDtAbertura = substr($Linha[8], 8, 2) . '/' . substr($Linha[8], 5, 2) . '/' . substr($Linha[8], 0, 4);
                    $licDtAbertura = substr($Linha[8], 11, 5);
                    $tpl->VALOR_OBJETO = $Linha[7];
                    $tpl->COMISSAO_DESC = $comissaoDesc;
                    $tpl->VALOR_DATA = $LicitacaoDtAbertura;
                    $tpl->VALOR_HORA = $licDtAbertura;
                    $tpl->VALOR_LINHA_12 = $Linha[12];
                    $tpl->VALOR_LINHA_11 = $Linha[11];
                    $tpl->VALOR_MODALIDADE_CODIGO = $ModalidadeCodigo;
                    $tpl->VALOR_LINHA_10 = $Linha[10];
                    $tpl->VALOR_LINHA_3 = $Linha[3];
                    $tpl->VALOR_LINHA_4 = $Linha[4];
                    $tpl->VALOR_PROCESSO = str_pad($Linha[3], 4, "0", STR_PAD_LEFT);
                    $tpl->VALOR_ANOPROCESSO = $Linha[4];
                    $tpl->VALOR_MODALIDADE = $Linha[1];
                    $tpl->ORGAO_LICITANTE = $Linha[9];
                    $tpl->LOCAL_CERTAME = $localCertame;
                    $tpl->VALOR_ESTIMADO = 'R$'.$valorEstimado;
                    $tpl->VALOR_LICITACAO = str_pad($Linha[5], 4, "0", STR_PAD_LEFT);
                    $tpl->VALOR_ANOLICITACAO = $Linha[6];
                    $tpl->HINT_VALOR = $hintValor;
                    $tpl->block('BLOCO_EXIBIR_COMISSAO');
                }
            }
            $tpl->block('BLOCO_RESULTADO_PESQUISA');
        } else {
            $tpl->exibirMensagemFeedback('Nenhuma ocorrência encontrada.', 1);
        }
    } else {
        $tpl->exibirMensagemFeedback('Atenção! Falta digitar o texto do Item.', 2);
    }
}

//CAIO MELQUIADES - FUNCAO PARA EXTRAIR DADOS DO BD EM FORMATO DE ARRAY E PASSAR PARA FUNCAO
//QUE VAI EXPORTAR PARA PLANILHA. ADAPTADA A PARTIR DA FUNCAO 'processPrincipal'
function proccessExport($formatoExport)
{
    
    
    if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
        // $Botao = $_POST ['Botao'];
        $Objeto = $_POST ['Objeto'];
        $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_VALIDATE_INT);
        $ComissaoCodigo = filter_var($_POST['ComissaoCodigo'], FILTER_VALIDATE_INT);
        $ModalidadeCodigo = $_POST ['ModalidadeCodigo'];
        $TipoItemLicitacao = $_POST ['TipoItemLicitacao'];
        $Item = $_POST ['Item'];

        $Mens2 = $_POST ['Mens2'];
        $Tipo2 = $_POST ['Tipo2'];
        $Mensagem2 = urldecode($_POST ['Mensagem2']);
    } else {
        $Mens2 = $_GET ['Mens2'];
        $Tipo2 = $_GET ['Tipo2'];
        $Mensagem2 = urldecode($_GET ['Mensagem2']);
    }

    $erro = false;
    $sineDie = 24; // Id fase Adiamento Sine Die

    if (($Item == '') and ($TipoItemLicitacao) != '') {
        $erro = true;
        $Mensagem2 = 'Atenção! Falta digitar o texto do Item.';
    }

    $Data = date('Y-m-d H:i:s');

    if (!$erro) {
        $database = Conexao();
        $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, LICPO.CLICPOPROC, LICPO.ALICPOANOP, ';
        $sql  .= ' LICPO.CLICPOCODL, LICPO.ALICPOANOL, LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC, ';
        $sql  .= ' LICPO.CGREMPCODI, LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, COLIC.ACOMLINFAX  ';
        $sql  .= ' FROM SFPC.TBLICITACAOPORTAL LICPO, SFPC.TBORGAOLICITANTE ORGLIC, SFPC.TBGRUPOEMPRESA GRUPRE, ';
        $sql  .= ' SFPC.TBCOMISSAOLICITACAO COLIC,SFPC.TBMODALIDADELICITACAO MODLIC';
        $sql  .= " WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI AND LICPO.FLICPOSTAT = 'A' ";
        $sql  .= ' AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI AND LICPO.CCOMLICODI = COLIC.CCOMLICODI  ';
        $sql  .= " AND LICPO.CMODLICODI = MODLIC.CMODLICODI AND LICPO.TLICPODHAB >= '$Data' ";

        if (($Item != '') and ($TipoItemLicitacao == '1')) {
            $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ';
            $sql .= ' LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ';
            $sql .= ' LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ';
            $sql .= ' LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ';
            $sql .= ' COLIC.ACOMLINFAX   ';
            $sql .= ' FROM  SFPC.TBORGAOLICITANTE ORGLIC, ';
            $sql .= ' SFPC.TBGRUPOEMPRESA GRUPRE,  ';
            $sql .= ' SFPC.TBCOMISSAOLICITACAO COLIC, ';
            $sql .= ' SFPC.TBMODALIDADELICITACAO MODLIC, ';
            $sql .= ' SFPC.TBLICITACAOPORTAL LICPO, ';
            $sql .= ' SFPC.TBITEMLICITACAOPORTAL ILIC,';
            $sql .= ' SFPC.TBMATERIALPORTAL MAT ';
            $sql .= ' WHERE LICPO.CORGLICODI = ORGLIC.CORGLICODI ';
            $sql .= " AND LICPO.FLICPOSTAT = 'A'  AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ";
            $sql .= ' AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ';
            $sql .= ' AND LICPO.CMODLICODI = MODLIC.CMODLICODI ';
            $sql .= " AND LICPO.TLICPODHAB >= '$Data'";
            $sql .= ' AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ';
            $sql .= ' AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ';
            $sql .= ' AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ';
            $sql .= ' AND LICPO.CORGLICODI = ILIC.CORGLICODI ';
            $sql .= ' AND ILIC.CMATEPSEQU  = MAT.CMATEPSEQU  ';
            $sql .= " AND(MAT.EMATEPDESC ILIKE '%".strtoupper2($Item)."%')";
        }
        if (($Item != '') and ($TipoItemLicitacao == '2')) {
            $sql = ' SELECT DISTINCT GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC, ';
            $sql .= ' LICPO.CLICPOPROC, LICPO.ALICPOANOP,  LICPO.CLICPOCODL, LICPO.ALICPOANOL, ';
            $sql .= ' LICPO.XLICPOOBJE, LICPO.TLICPODHAB, ORGLIC.EORGLIDESC,  LICPO.CGREMPCODI, ';
            $sql .= ' LICPO.CCOMLICODI, LICPO.CORGLICODI, COLIC.ECOMLILOCA, COLIC.ACOMLIFONE, ';
            $sql .= ' COLIC.ACOMLINFAX   ';
            $sql .= ' FROM  SFPC.TBORGAOLICITANTE ORGLIC,';
            $sql .= ' SFPC.TBGRUPOEMPRESA GRUPRE,  ';
            $sql .= ' SFPC.TBCOMISSAOLICITACAO COLIC ,';
            $sql .= ' SFPC.TBMODALIDADELICITACAO MODLIC, ';
            $sql .= ' SFPC.TBLICITACAOPORTAL LICPO ,';
            $sql .= ' SFPC.TBITEMLICITACAOPORTAL ILIC,';
            $sql .= ' SFPC.TBSERVICOPORTAL SERV ';
            $sql .= ' WHERE   LICPO.CORGLICODI = ORGLIC.CORGLICODI ';
            $sql .= " AND LICPO.FLICPOSTAT = 'A' ";
            $sql .= ' AND LICPO.CGREMPCODI = GRUPRE.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = COLIC.CCOMLICODI   ';
            $sql .= ' AND LICPO.CMODLICODI = MODLIC.CMODLICODI ';
            $sql .= ' AND LICPO.TLICPODHAB >= clock_timestamp()';
            $sql .= ' AND LICPO.CLICPOPROC = ILIC.CLICPOPROC ';
            $sql .= ' AND LICPO.ALICPOANOP = ILIC.ALICPOANOP ';
            $sql .= ' AND LICPO.CGREMPCODI = ILIC.CGREMPCODI ';
            $sql .= ' AND LICPO.CCOMLICODI = ILIC.CCOMLICODI ';
            $sql .= ' AND LICPO.CORGLICODI = ILIC.CORGLICODI ';
            $sql .= ' AND ILIC.CSERVPSEQU  = SERV.CSERVPSEQU ';
            $sql .= " AND(SERV.ESERVPDESC ILIKE '%".strtoupper2($Item)."%') ";
        }

        if ($Objeto != '') {
            $sql .= " AND ( LICPO.XLICPOOBJE ILIKE '%".strtoupper2($Objeto)."%')";
        }
        if ($ComissaoCodigo != '') {
            $sql .= " AND LICPO.CCOMLICODI = $ComissaoCodigo ";
        }
        if ($ModalidadeCodigo != '') {
            $sql .= " AND LICPO.CMODLICODI = $ModalidadeCodigo ";
        }
        if ($OrgaoLicitanteCodigo != '') {
            $sql .= " AND LICPO.CORGLICODI = $OrgaoLicitanteCodigo ";
        }

        $sql .= ' ORDER BY LICPO.TLICPODHAB ASC, GRUPRE.EGREMPDESC, MODLIC.EMODLIDESC, COLIC.ECOMLIDESC,  LICPO.ALICPOANOP, LICPO.CLICPOPROC';
        
        $result = $database->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $Rows = $result->numRows();
        }

        $cabecalho = array('OBJETO', 'DATA ABERTURA','HORA ABERTURA', 'ORGÃO LICITANTE','COMISSÃO DE LICITAÇÃO','LOCAL DE REALIZACAO DO CERTAME',  'PROCESSO', 'MODALIDADE', 'LICITACAO', 'VALOR ESTIMADO TOTAL');
        $linhas = array();

        if ($Rows != 0) {
            while ($Linha = $result->fetchRow()) {
                // Verificar última fase
                $ultimaFase = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $database);
                $localCertame = localCertame($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $database);
                $comissaoDesc = comissaoDescricao($database,$Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
                $valorEstimado = totalValorEstimado($database, $Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
                $valor_convertido = 'R$ '.converte_valor($valorEstimado);
                if($ultimaFase != $sineDie) {
                    $LicitacaoDtAbertura = substr($Linha[8], 8, 2) . '/' . substr($Linha[8], 5, 2) . '/' . substr($Linha[8], 0, 4);
                    $licDtAbertura = substr($Linha[8], 11, 5);

                    $processo = str_pad($Linha[3], 4, "0", STR_PAD_LEFT) . '/' . $Linha[4];
                    $licitacao = str_pad($Linha[5], 4, "0", STR_PAD_LEFT) . '/' . $Linha[6];

                    array_push($linhas, array(
                        $Linha[7], 
                        $LicitacaoDtAbertura,
                        $licDtAbertura,
                        $Linha[9],
                        $comissaoDesc,
                        $localCertame,
                        $processo,
                        $Linha[1],
                        $licitacao,
                        $valor_convertido
                    ));

                }
            }
            //$tpl->block('BLOCO_RESULTADO_PESQUISA');
            //$y= print_r($cabecalho, true);
		    //die($y);
            
            $nomeArquivo = 'pcr_portal_compras_avisos_licitacao';

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
        } else {
            //$tpl->exibirMensagemFeedback('Nenhuma ocorrência encontrada.', 1);
        }
    } else {
        //$tpl->exibirMensagemFeedback('Atenção! Falta digitar o texto do Item.', 2);
    }
}


/**
 * [frontController description]
 */
function frontController()
{
    $tpl = new TemplateAppPadrao(CAMINHO_SISTEMA.'app/templates/ConsAvisosPesquisar.html', 'ConsAvisosPesquisar');
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