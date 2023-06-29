<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version GIT: v1.19.0-6-ge9180d4
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES DO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     21/07/2015 - 08/09/2015
 * Objetivo: CR95756 - Pesquisa de Licitações por item de material ou serviço - vários programas
 * -----------------------------------------------------------------------------
 */
if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}
require_once '../licitacoes/funcoesLicitacoes.php';

$tpl = new TemplateAppPadrao('templates/ConsAcompResultadoGeral.html', 'ConsAcompResultadoGeral');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carregaProcesso = $_POST['carregaProcesso'];
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
    $Selecao = $_POST['Selecao'];
    $Objeto = strtoupper2($_POST['Objeto']);
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $LicitacaoAno = $_POST['LicitacaoAno'];
    $TipoItemLicitacao = $_POST['TipoItemLicitacao'];
    $Item = $_POST['ItemInput'];
    $adminDireta = isset($_POST['adminDireta']) ? true : false;
    $tipoEmpresa = isset($_POST['tipoEmpresa']) ? true : false;
    $licitacaoSituacao = $_POST['licitacaoSituacao'];
}

$arraySituacoesConcluidas = getIdFasesConcluidas(); // Array com os ids das situações concluídas
$arraySituacoesEmAndamento = getIdFasesEmAndamento(); // Array com os ids das situações em andamento

define('COL_SPAN', 6);

//Define pesquisar como 1 - valor não muda devido a página só ter uma função.
$pesquisar = 1;
$filtroPesquisa = null;

if (!$_SESSION['Pesquisar'] == 1) {
    // Prepara exibição dos filtros
    $fragmentoSelect = ' SELECT LP.CLICPOPROC ';
    $fragmentoFrom = ' FROM SFPC.TBLICITACAOPORTAL LP ';
    $fragmentoWhere = ' WHERE 1 = 1 ';
    $queryExiste = false;

    if (empty($OrgaoLicitanteCodigo) === false) {
        $fragmentoSelect  .= ' , OL.EORGLIDESC ';
        $fragmentoFrom    .= ' , SFPC.TBORGAOLICITANTE OL ';
        $fragmentoWhere   .= " AND OL.CORGLICODI = $OrgaoLicitanteCodigo ";
        $queryExiste = true;
    }

    if (empty($ComissaoCodigo) === false) {
        $fragmentoSelect  .= ' , CL.ECOMLIDESC ';
        $fragmentoFrom    .= ' , SFPC.TBCOMISSAOLICITACAO CL ';
        $fragmentoWhere   .= " AND CL.CCOMLICODI = $ComissaoCodigo ";
        $queryExiste = true;
    }

    if (empty($ModalidadeCodigo) === false) {
        $fragmentoSelect  .= ' , ML.EMODLIDESC ';
        $fragmentoFrom    .= ' , SFPC.TBMODALIDADELICITACAO ML ';
        $fragmentoWhere   .= " AND ML.CMODLICODI = $ModalidadeCodigo ";
        $queryExiste = true;
    }

    $descricaoOrgaoLicitante = 'Todos';
    $descricaoComissao = 'Todas';
    $descricaoModalidade = 'Todas';

    // Verifica se é necessário executar consulta ao banco
    if ($queryExiste) {
        $sql = $fragmentoSelect.$fragmentoFrom.$fragmentoWhere.' LIMIT 1';

        $db = Conexao();
        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }

        while ($linha = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $descricaoOrgaoLicitante = (isset($linha['eorglidesc'])) ? $linha['eorglidesc'] : 'Todos';
            $descricaoComissao = (isset($linha['ecomlidesc'])) ? $linha['ecomlidesc'] : 'Todas';
            $descricaoModalidade = (isset($linha['emodlidesc'])) ? $linha['emodlidesc'] : 'Todas';
        }

        $db->disconnect();
    }

    // Recupera o tipo de item
    $descricaoTipoItem = 'Todos';
    if (empty($TipoItemLicitacao) === false) {
        $descricaoTipoItem = ($TipoItemLicitacao == '1') ? 'Material' : 'Serviço';
    }

    $arraySituacaoLicitacao = array(
        1 => 'Concluídas',
        2 => 'Andamento',
        3 => 'Todas',
    );

    $filtroPesquisa = array(
        'Objeto' => ($Objeto == '') ? 'Todos' : $Objeto ,
        'Órgão Licitante' => $descricaoOrgaoLicitante,
        'Administração direta' => ($adminDireta === true) ? 'Marcado' : 'Desmarcado',
        'Comissão' => $descricaoComissao,
        'Modalidade' => $descricaoModalidade,
        'Situação' => $arraySituacaoLicitacao[$licitacaoSituacao],
        'Ano' => (isset($LicitacaoAno) && $licitacaoSituacao == 1) ? $LicitacaoAno : 'Todos',
        /*'Microempresa, EPP ou MEI' => ($tipoEmpresa === true) ? 'Marcado' : 'Desmarcado',*/
        'Item' => $descricaoTipoItem,
        'Descrição item' => ($Item == '') ? 'Todas' : $Item,
    );
}

# Redireciona dados para ConsAcompPesquisaGeral.php se Houve Erro #
if (($Item == '') and ($TipoItemLicitacao) != '') {
    $_SESSION['Mensagem'] = 'Atenção! Falta digitar o texto do Item.';
    $_SESSION['Mens'] = 1;
    $_SESSION['Tipo'] = 2;
    $_SESSION['Objeto'] = $Objeto;
    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
    $_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
    $_SESSION['ModalidadeCodigo'] = $ModalidadeCodigo;
    $_SESSION['Selecao'] = $Selecao;
    $_SESSION['RetornoPesquisa'] = 1;
    $_SESSION['TipoItemLicitacao'] = $TipoItemLicitacao;
    $_SESSION['Item'] = $Item;
    $_SESSION['adminDireta'] = $adminDireta;
    //$_SESSION['tipoEmpresa'] = $tipoEmpresa;
    $_SESSION['licitacaoSituacao'] = $licitacaoSituacao;

    header('Location: ConsAcompPesquisaGeral.php');
    exit();
}

# Redireciona dados para ConsAcompPesquisaGeral.php #
if ($Botao == 'Pesquisa') {
    $_SESSION['RetornoPesquisa'] = null;
    header('Location: ConsAcompPesquisaGeral.php');
    exit();
}

if ($Botao == 'carregaProcesso') {
    list($_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']) = explode('-', $carregaProcesso);

    header('Location: ConsAcompDetalhes.php');
    exit();
}

$Mens = 0;

if ($Mens == 0) {
    $db = Conexao();
    $Data = date('Y-m-d');

    $novaSql = '';

    // SELECT
    $novaSql .= ' SELECT ';
    $novaSql .= ' DISTINCT A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ';
    $novaSql .= ' A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ';
    $novaSql .= ' A.TLICPODHAB, A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, e.CFASESCODI ';

    // FROM
    $novaSql .= ' FROM ';

    if ($TipoItemLicitacao == 1) {
        $novaSql .= ' SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ';
    }

    if ($TipoItemLicitacao == 2) {
        $novaSql .= ' SFPC.tbitemlicitacaoportal F, SFPC.tbservicoportal G, ';
    }

    $novaSql .= ' SFPC.TBFASES e,
    (
     SELECT
     l.CLICPOPROC AS Proc ,
     l.ALICPOANOP AS Ano ,
     l.CGREMPCODI AS Grupo ,
     l.CCOMLICODI AS Comis ,
     l.CORGLICODI AS Orgao ,
     MAX(o.AFASESORDE) AS Maior
     FROM
     SFPC.TBFASELICITACAO l ,
     SFPC.TBFASES o
     WHERE
     l.CFASESCODI = o.CFASESCODI
     GROUP BY
     l.CLICPOPROC ,
     l.ALICPOANOP ,
     l.CGREMPCODI ,
     l.CCOMLICODI ,
     l.CORGLICODI
     ) AS maiorordem, ';
    $novaSql .= ' SFPC.TBFASELICITACAO D, ';
    $novaSql .= ' SFPC.TBLICITACAOPORTAL A ';

    // Microempresa, EPP ou MEI
    // if ($tipoEmpresa === true) {
    //     $novaSql .= ' LEFT OUTER JOIN SFPC.TBITEMLICITACAOPORTAL ILP ';
    //     $novaSql .= ' ON ILP.CLICPOPROC = A.CLICPOPROC ';
    //     $novaSql .= ' AND ILP.ALICPOANOP = A.ALICPOANOP ';
    //     $novaSql .= ' AND ILP.CGREMPCODI = A.CGREMPCODI ';
    //     $novaSql .= ' AND ILP.CCOMLICODI = A.CCOMLICODI ';
    //     $novaSql .= ' AND ILP.CORGLICODI = A.CORGLICODI INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FC ';
    //     $novaSql .= ' ON ILP.AFORCRSEQU = FC.AFORCRSEQU ';
    //     $novaSql .= ' AND FC.FFORCRMEPP IS NOT NULL ';
    // }

        // JOIN Comissão licitação
    $novaSql .= ' INNER JOIN SFPC.TBCOMISSAOLICITACAO CD ';
    $novaSql .= ' ON CD.CCOMLICODI = A.CCOMLICODI ';

        // JOIN Grupo
    $novaSql .= ' INNER JOIN SFPC.TBGRUPOORGAO GO ';
    $novaSql .= ' ON GO.CGREMPCODI = A.CGREMPCODI AND GO.CORGLICODI = A.CORGLICODI ';
    $novaSql .= ' INNER JOIN SFPC.TBGRUPOEMPRESA GE ';
    $novaSql .= ' ON GE.CGREMPCODI = GO.CGREMPCODI ';

        // JOIN Modalidade
    $novaSql .= ' INNER JOIN SFPC.TBMODALIDADELICITACAO ML ';
    $novaSql .= ' ON ML.CMODLICODI = A.CMODLICODI ';

        // JOIN Órgão licitante
    $novaSql .= ' INNER JOIN SFPC.TBORGAOLICITANTE B ';
    $novaSql .= ' ON B.CORGLICODI = A.CORGLICODI ';

    if ($OrgaoLicitanteCodigo != '') {
        $novaSql .= " AND A.CORGLICODI = '$OrgaoLicitanteCodigo' ";
    }

        // WHERE
    $novaSql .= ' WHERE 1 = 1 ';
    $novaSql .= ' AND a.CLICPOPROC = maiorordem.Proc
        AND a.ALICPOANOP = maiorordem.Ano
        AND a.CGREMPCODI = maiorordem.Grupo
        AND a.CCOMLICODI = maiorordem.Comis
        AND a.CORGLICODI = maiorordem.Orgao
        AND e.AFASESORDE = maiorordem.Maior
        AND a.clicpoproc = d.clicpoproc
        AND a.alicpoanop = d.alicpoanop
        AND a.cgrempcodi = d.cgrempcodi
        AND a.ccomlicodi = d.ccomlicodi
        AND a.corglicodi = d.corglicodi
        AND d.CFASESCODI = e.CFASESCODI
        AND a.CCOMLICODI NOT IN(41) ';

        // Objeto
    if ($Objeto != '') {
        $novaSql .= " AND A.XLICPOOBJE ILIKE '%$Objeto%'";
    }

            // Administração direta
    if ($adminDireta === true) {
        $novaSql .= ' AND a.CGREMPCODI = 1 '; // Grupo administração direta
        $novaSql .= ' AND a.CCOMLICODI IN (1, 2, 3, 4, 8, 40, 34, 39, 42, 44) ';
    }

    // Comissão
    if ($ComissaoCodigo != '') {
        $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
    }

    // Modalidade
    if ($ModalidadeCodigo != '') {
        $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
    }

    // Situação
    if ($licitacaoSituacao > 0) {
        if ($licitacaoSituacao == 1) {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $novaSql   .= " AND D.CFASESCODI IN ($strIdConcluidas) ";
        } elseif ($licitacaoSituacao == 2) {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $novaSql   .= " AND D.CFASESCODI IN ($strIdAndamento) ";
        } elseif ($licitacaoSituacao == 3) {
            $strTodas = array_merge($arraySituacoesConcluidas, $arraySituacoesEmAndamento);
            $sorted_array = $strTodas;
            asort($sorted_array);
            $sorted_array = implode(', ', $sorted_array);
            $novaSql   .= " AND D.CFASESCODI IN ($sorted_array) ";
        }

        if (isset($LicitacaoAno) && $licitacaoSituacao == 1) {
            $novaSql .= " AND EXTRACT(YEAR FROM D.TFASELDATA) = '$LicitacaoAno' ";
        }
    }

    // Item
    if (($TipoItemLicitacao == 'Material') or ($TipoItemLicitacao == 'Servico')) {
        $novaSql .= ' AND A.CLICPOPROC = F.CLICPOPROC ';
        $novaSql .= ' AND A.ALICPOANOP = F.ALICPOANOP ';
        $novaSql .= ' AND A.CGREMPCODI = F.CGREMPCODI ';
        $novaSql .= ' AND A.CCOMLICODI = F.CCOMLICODI ';
        $novaSql .= ' AND A.CORGLICODI = F.CORGLICODI ';
    }

    // Descrição do item material
    if ($TipoItemLicitacao == 'Material') {
        $novaSql .= ' AND F.CMATEPSEQU = G.CMATEPSEQU ';
        $novaSql .= " AND (G.EMATEPDESC ILIKE '%$Item%') ";
    }

    // Descrição do item serviço
    if ($TipoItemLicitacao == 'Servico') {
        $novaSql .= ' AND F.CSERVPSEQU = G.CSERVPSEQU ';
        $novaSql .= " AND (G.ESERVPDESC ILIKE '%$Item%') ";
    }

    // ORDER BY
    $novaSql .= ' ORDER BY GE.EGREMPDESC, ML.EMODLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC';

    $result = $db->query($novaSql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $novaSql");
    }

    $cont = 0;
    $GrupoDescricao = '';
    $ModalidadeDescricao = '';
    $ComissaoDescricao = '';

    while ($cols = $result->fetchRow()) {
        // Query para recuperar todas as fases da licitação
            $sqlFases = "SELECT F.CFASESCODI FROM SFPC.TBFASELICITACAO F WHERE F.CLICPOPROC = $cols[0] AND F.ALICPOANOP = $cols[6]
            AND F.CGREMPCODI = $cols[11] AND F.CCOMLICODI = $cols[12] AND F.CORGLICODI = $cols[13]";

        $fasesLicitacao = $db->getCol($sqlFases);
        if (PEAR::isError($fasesLicitacao)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFases");
        }

        $licitacaoPublicada = in_array(2, $fasesLicitacao);
        $dataAberturaMenorQueAtual = strtotime($cols[10]) < strtotime(date('Y-m-d H:i:s'));

        // Verifica se a licitação está publicada ou se a data de abertura é menor que a data atual
        if ($licitacaoPublicada || $dataAberturaMenorQueAtual) {
            ++$cont;
            $dados[$cont - 1] = "$cols[4]_$cols[5]_$cols[2]_$cols[0]_$cols[6]_$cols[7]_$cols[8]_$cols[9]_$cols[10]_$cols[1]_$cols[11]_$cols[12]_$cols[13]_$cols[14]_$cols[3]";
        }
    }

    if (count($dados) === 0) {
        $tpl->exibirMensagemFeedback('Nenhuma ocorrência foi encontrada', '1');
        $tpl->OMITIRCRITERIOS = 'none';
    } else {
        $tpl->OMITIRCRITERIOS = 'block';
    }

    // [CRITÉRIOS]
    $tpl->OBJETO_CRITERIO = $filtroPesquisa['Objeto'];
    $tpl->ADMDIRETA_CRITERIO = $filtroPesquisa['Administração direta'];
    $tpl->ORGAO_LICITANTE_CRITERIO = $filtroPesquisa['Órgão Licitante'];
    $tpl->COMISSAO_DESCRICAO_CRITERIO = $filtroPesquisa['Comissão'];
    $tpl->MODALIDADE_DESCRICAO_CRITERIO = $filtroPesquisa['Modalidade'];
    $tpl->SITUACAO_CRITERIO = $filtroPesquisa['Situação'];
    $tpl->ANO_CRITERIO = $filtroPesquisa['Ano'];
    $tpl->ITEM_CRITERIO = $filtroPesquisa['Item'];
    $tpl->DESCITEM_CRITERIO = $filtroPesquisa['Descrição item'];
    if ($filtroPesquisa['Situação'] == 'Concluídas') {
        $tpl->TIPO_EMPRESA_CRITERIO = $filtroPesquisa['Microempresa, EPP ou MEI'];
        $tpl->block('BLOCO_TIPO_EMPRESA');
    }
    // [/CRITÉRIOS]

    for ($Row = 0; $Row < $cont; ++$Row) {
        $Linha = explode('_', $dados[$Row]);
        if ($Linha[0] != '' && $GrupoDescricao != $Linha[0]) {
            $tpl->GRUPO_DESCRICAO = $Linha[0];
            $GrupoDescricao = $Linha[0];
            $tpl->block('BLOCO_GRUPO');
        } else {
            $tpl->clear('BLOCO_GRUPO');
        }
        if ($ModalidadeDescricao != $Linha[1]) {
            $tpl->MODALIDADE_DESCRICAO = $Linha[1];
            $tpl->block('BLOCO_MODALIDADE');
            $ModalidadeDescricao = $Linha[1];
        }
        if ($ComissaoDescricao != $Linha[2]) {
            $tpl->COMISSAO_DESCRICAO = $Linha[2];
            $tpl->block('BLOCO_COMISSAO');
        }
        if ($ComissaoDescricao != $Linha[2]) {
            $tpl->block('BLOCO_CABECALHO');
            $ComissaoDescricao = $Linha[2];
        }
        $ComissaoDescricao = $Linha[2];
        $LicitacaoDtAbertura = substr($Linha[8], 8, 2).'/'.substr($Linha[8], 5, 2).'/'.substr($Linha[8], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
        $idUltimaFaseLicitacao = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
        $situacaoAtualLicitacao = 'EM ANDAMENTO';

        if (in_array($idUltimaFaseLicitacao, $arraySituacoesConcluidas)) {
            $situacaoAtualLicitacao = 'CONCLUÍDA';
        }

        $tpl->URL = $Linha[10].'-'.$Linha[3].'-'.$Linha[4].'-'.$Linha[11].'-'.$Linha[12];
        $tpl->PROCESSO = substr($Linha[3] + 10000, 1).'/'.$Linha[4];
        $tpl->LICITACAO = substr($Linha[5] + 10000, 1).'/'.substr($Linha[6] + 10000, 1);

        $tpl->OBJETO = $Linha[7];
        $tpl->DATA_HORA_ABERTURA = $LicitacaoDtAbertura.' '.$LicitacaoHoraAbertura.' h';
        $tpl->ORGAO_LICITANTE = $Linha[9];
        $tpl->SITUACAO = $situacaoAtualLicitacao;

        // if ($i+1 < count($Linha)) {
        //     if ($ultimaComissaoPlotada != $Linha[2]) {
        //         $tpl->block("BLOCO_SEPARATOR");
        //     }
        // }

        $tpl->block('BLOCO_VALORES');
        $tpl->block('BLOCO_CORPO');
    }
}

$db->disconnect();
$tpl->show();
