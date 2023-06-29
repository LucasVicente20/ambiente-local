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
 * @version   GIT: v1.16.1-10-g6112772
 */
/**
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data: 13/07/2015 - CR95756
 * Link: http://redmine.recife.pe.gov.br/issues/95756
 * Versão:
 * -------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/09/2018
 * Objetivo: Tarefa Redmine 203345
 * -----------------------------------------------------------------------------
 * Alterado: Caio Coutinho
 * Data:     10/10/2018
 * Objetivo: Tarefa Redmine 205100
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/04/2019
 * Objetivo: Tarefa Redmine 215448
 * -----------------------------------------------------------------------------
 * Alterado: João Madson    
 * Data:     27/01/2021
 * Objetivo: CR #243182
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     07/06/2021
 * Objetivo: Tarefa Redmine  #248482
 * -----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     27/05/2022 - 07/06/2022
 * Objetivo: Projeto Transparência
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     04/11/2022
 * Objetivo: CR 270290 - Mover botão de Exportar para tela de Resultados
 * ---------------------------------------------------------------------------
 * Alterado: Thiago Lins e Lucas André
 * Data:     13/06/2023
 * Objetivo: CR 284508
 * ---------------------------------------------------------------------------
 */
if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}

include_once '../licitacoes/funcoesLicitacoes.php';

$tpl = new TemplateAppPadrao('templates/ConsLicitacoesAndamentoResultado.html', 'ConsLicitacoesAndamentoResultado');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carregaProcesso      = $_POST['carregaProcesso'];
    $Botao                = ($_POST['Botao'] == 'carregaProcesso') ? $_POST['Botao'] : 'Pesquisar';
    $Critica              = $_POST['Critica'];
    $Objeto               = strtoupper($_POST['Objeto']);
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
    $TipoItemLicitacao    = $_POST['TipoItemLicitacao'];
    $Item                 = $_POST['ItemInput'];

    if ($Botao == 'Pesquisar') {
        $_SESSION['Pesquisar'] = true;
    } else {
        $_SESSION['Pesquisar'] = false;
    }

    if ($Botao == 'Pesquisar') {
        $_SESSION['Objeto']               = $Objeto;
        $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
        $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
        $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
        $_SESSION['TipoItemLicitacao']    = $TipoItemLicitacao;
        $_SESSION['Item']                 = $Item;
        $_SESSION['botao']                = $Botao;
    }

    if ($Botao == 'carregaProcesso') {
        list($_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']) = explode('-', $carregaProcesso);
        header('Location: ConsLicitacoesAndamentoDetalhe.php');
        exit();
    }
}
$processoCodigo = $_SESSION['ProcessoDet'];
$processoAno = $_SESSION['ProcessoAnoDet'];

$ArrImun = array('IMUNIZAÇÃO', 'IMUNIZACAO', 'IMUNIZAÇAO', 'IMUNIZACÃO');
$arraySituacoesConcluidas  = getIdFasesConcluidas(); // Array com os ids das situações concluídas
$arraySituacoesEmAndamento = getIdFasesEmAndamento(); // Array com os ids das situações em andamento

if ($_SESSION['botao'] == 'carregaProcesso' || $_SESSION['botao'] == 'Pesquisar') {
    $Objeto               = $_SESSION['Objeto'];
    $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
    $ModalidadeCodigo     = $_SESSION['ModalidadeCodigo'];
    $TipoItemLicitacao    = $_SESSION['TipoItemLicitacao'];
    $Item                 = $_SESSION['Item'];
    $tipoEmpresa          = $_SESSION['tipoEmpresa'];
    $Botao                = $_SESSION['botao'];

    // Prepara exibição dos filtros
    $fragmentoSelect = ' SELECT LP.CLICPOPROC ';
    $fragmentoFrom   = ' FROM SFPC.TBLICITACAOPORTAL LP ';
    $fragmentoWhere  = ' WHERE 1 = 1 ';
    $queryExiste     = false;

    if (empty($OrgaoLicitanteCodigo) === false) {
        $fragmentoSelect .= ' , OL.EORGLIDESC ';
        $fragmentoFrom   .= ' , SFPC.TBORGAOLICITANTE OL ';
        $fragmentoWhere  .= " AND OL.CORGLICODI = $OrgaoLicitanteCodigo ";
        $queryExiste      = true;
    }

    if (empty($ComissaoCodigo) === false) {
        $fragmentoSelect .= ' , CL.ECOMLIDESC ';
        $fragmentoFrom   .= ' , SFPC.TBCOMISSAOLICITACAO CL ';
        $fragmentoWhere  .= " AND CL.CCOMLICODI = $ComissaoCodigo ";
        $queryExiste      = true;
    }

    if (empty($ModalidadeCodigo) === false) {
        $fragmentoSelect .= ' , ML.EMODLIDESC ';
        $fragmentoFrom   .= ' , SFPC.TBMODALIDADELICITACAO ML ';
        $fragmentoWhere  .= " AND ML.CMODLICODI = $ModalidadeCodigo ";
        $queryExiste      = true;
    }

    $descricaoOrgaoLicitante = 'Todos';
    $descricaoComissao       = 'Todas';
    $descricaoModalidade     = 'Todas';

    // Verifica se é necessário executar consulta ao banco
    if ($queryExiste) {
        $sql = $fragmentoSelect.$fragmentoFrom.$fragmentoWhere.' LIMIT 1';
        
        $db = Conexao();
        
        $result = $db->query($sql);

        if (db::isError($result)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: $sql");
        }

        while ($linha = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $descricaoOrgaoLicitante = (isset($linha['eorglidesc'])) ? $linha['eorglidesc'] : 'Todos';
            $descricaoComissao       = (isset($linha['ecomlidesc'])) ? $linha['ecomlidesc'] : 'Todas';
            $descricaoModalidade     = (isset($linha['emodlidesc'])) ? $linha['emodlidesc'] : 'Todas';
        }

        $db->disconnect();
    }

    // Recupera o tipo de item
    $descricaoTipoItem = 'Todos';
    
    if (empty($TipoItemLicitacao) === false) {
        $descricaoTipoItem = ($TipoItemLicitacao == 'Material') ? 'Material' : 'Serviço';
    }

    $filtroPesquisa = array(
        'Objeto'          => ($Objeto == '') ? 'Todos' : $Objeto,
        'Órgão Licitante' => $descricaoOrgaoLicitante,
        'Comissão'        => $descricaoComissao,
        'Modalidade'      => $descricaoModalidade,
        'Item'            => $descricaoTipoItem,
        'Descrição item'  => ($Item == '') ? 'Todas' : $Item,
    );

    $_SESSION['Export'] = array(
        'Objeto' => ($Objeto == '') ? 'Todos' : $Objeto,
        'Órgão Licitante' => $descricaoOrgaoLicitante,
        'Comissão' => $descricaoComissao,
        'Modalidade' => $descricaoModalidade,
        $pesqLabel => $pesqValue,
        'Item' => $descricaoTipoItem,
        'Descrição item' => ($Item == '') ? 'Todas' : $Item,
        'ComissaoCodigo' => $ComissaoCodigo,
        'ModalidadeCodigo' => $ModalidadeCodigo,
        'TipoItemLicitacao'=> $TipoItemLicitacao,
        'dataInicio' => $dataIniFormatado,
        'dataFim' => $dataFimFormatado,
        'LicitacaoAno' => $LicitacaoAno,
        'OrgaoLicitanteCodigo' => $OrgaoLicitanteCodigo,
        'TipoItemLicitacao'=> $TipoItemLicitacao,
        

    );
    

    // Redireciona dados para ConsLicitacoesAndamento.php se Houve Erro #
    if (($Item == '') and ($TipoItemLicitacao) != '') {
        $_SESSION['Mensagem']             = 'Informe: Descrição do item';
        $_SESSION['Mens']                 = 1;
        $_SESSION['Tipo']                 = 2;
        $_SESSION['Objeto']               = $Objeto;
        $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
        $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
        $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
        $_SESSION['RetornoPesquisa']      = 1;
        $_SESSION['TipoItemLicitacao']    = $TipoItemLicitacao;
        $_SESSION['Item']                 = $Item;

        header('Location: ConsLicitacoesAndamento.php');
        exit();
    }

    // Redireciona dados para ConsAcompPesquisaGeral.php #
    if ($Botao == 'Pesquisa') {
        $_SESSION['RetornoPesquisa'] = null;
        header('Location: ConsLicitacoesAndamento.php');
        exit();
    }
  

    $Mens = 0;

    if ($Mens == 0) {
        $db = Conexao();
        $Data = date('Y-m-d');
        $novaSql = '';

        // SELECT
        $novaSql .= ' SELECT ';
        $novaSql .= ' distinct A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ';
        $novaSql .= ' A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ';
        $novaSql .= ' A.TLICPODHAB, A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, e.CFASESCODI ';

        // FROM
        $novaSql .= ' FROM ';

        if ($TipoItemLicitacao == 'Material') {
            $novaSql .= ' SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ';
        }

        if ($TipoItemLicitacao == 'Servico') {
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

        // JOIN Comissão licitação
        $novaSql .= ' INNER JOIN SFPC.TBCOMISSAOLICITACAO CD ';
        $novaSql .= ' ON CD.CCOMLICODI = A.CCOMLICODI ';

        // JOIN Grupo
        //$novaSql .= ' INNER JOIN SFPC.TBGRUPOORGAO GO ';
        //$novaSql .= ' ON GO.CGREMPCODI = A.CGREMPCODI AND GO.CORGLICODI = A.CORGLICODI ';
        $novaSql .= ' INNER JOIN SFPC.TBGRUPOEMPRESA GE ';
        $novaSql .= ' ON GE.CGREMPCODI = A.CGREMPCODI ';

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
            if(in_array(strtoupper($Objeto), $ArrImun)){
                $novaSql .= " AND (A.XLICPOOBJE ILIKE '%$ArrImun[0]%' OR A.XLICPOOBJE ILIKE '%$ArrImun[1]%' OR A.XLICPOOBJE ILIKE '%$ArrImun[2]%' OR A.XLICPOOBJE ILIKE '%$ArrImun[3]%')";
            }else{
                $novaSql .= " AND (A.XLICPOOBJE ILIKE '%$Objeto%' OR A.XLICPOOBJE ILIKE '%".RetiraAcentos($Objeto)."%')";
            }
        }

        // Comissão
        if ($ComissaoCodigo != '') {
            $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
        }

        // Modalidade
        if ($ModalidadeCodigo != '') {
            $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
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
            ;
            $novaSql .= ' AND F.CMATEPSEQU = G.CMATEPSEQU ';
            $novaSql .= " AND (G.EMATEPDESC ILIKE '%$Item%') ";
        }

        // Descrição do item serviço
        if ($TipoItemLicitacao == 'Servico') {
            $novaSql .= ' AND F.CSERVPSEQU = G.CSERVPSEQU ';
            $novaSql .= " AND (G.ESERVPDESC ILIKE '%$Item%') ";
        }

        // Situação
        $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
        $novaSql .= " AND ((D.CFASESCODI IN ($strIdAndamento) AND A.TLICPODHAB < clock_timestamp()) OR (D.CFASESCODI = 24))";

        // ORDER BY
        $novaSql .= ' ORDER BY GE.EGREMPDESC, B.EORGLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC';
        
        $result = $db->query($novaSql);

        $ErroPrograma = 'app/ConsLicitacoesAndamentoResultado.php';

        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $novaSql");
        }

        if ($result->numRows() <= 0) {
            $_SESSION['Mensagem'] = 'Nenhuma ocorrência foi encontrada.';
            $_SESSION['Mens'] = 1;
            $_SESSION['Tipo'] = 1;

            header('Location: ConsLicitacoesAndamento.php');
            exit();
        }

        $cont                = 0;
        $GrupoDescricao      = '';
        $ModalidadeDescricao = '';
        $OrgaoDescricao      = '';
        
        function comissaoDescricao($db, $GrupoCodigo, $ComissaoCodigo, $ProcessoAno, $Processo, $OrgaoLicitanteCodigo){
            $db = Conexao();
            $sql  = "SELECT  C.ECOMLIDESC as comissaodescricao ";  
            $sql .= " FROM ";
            $sql .= " SFPC.TBLICITACAOPORTAL D, SFPC.TBCOMISSAOLICITACAO C ";
            $sql .= "WHERE C.CCOMLICODI = D.CCOMLICODI ";
            $sql .= "       AND D.CGREMPCODI = $GrupoCodigo ";
            $sql .= "       AND D.CCOMLICODI = $ComissaoCodigo ";
            $sql .= "       AND D.ALICPOANOP = $ProcessoAno ";
            $sql .= "       AND D.CLICPOPROC = $Processo ";
            $sql .= "       AND D.CORGLICODI = $OrgaoLicitanteCodigo ";
            $result = executarTransacao($db,$sql);
            $row	= $result->fetchRow(DB_FETCHMODE_OBJECT);
            return $row->comissaodescricao;
        }

        while ($cols = $result->fetchRow()) {
            // Query para recuperar todas as fases da licitação
            $sqlFases = "SELECT F.CFASESCODI
                         FROM SFPC.TBFASELICITACAO F
                         WHERE F.CLICPOPROC = $cols[0] AND F.ALICPOANOP = $cols[6] AND F.CGREMPCODI = $cols[11] AND F.CCOMLICODI = $cols[12] AND F.CORGLICODI = $cols[13]";
            
            $fasesLicitacao = $db->getCol($sqlFases);
            
            if (db::isError($fasesLicitacao)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFases");
            }

            $licitacaoPublicada = in_array(2, $fasesLicitacao);
            //$dataAberturaMenorQueAtual = strtotime($cols[10]) < strtotime(date('Y-m-d H:i:s'));

            // Verifica se a licitação está publicada ou se a data de abertura é menor que a data atual
            if ($licitacaoPublicada /*&& $dataAberturaMenorQueAtual*/) {
                ++$cont;
                $dados[$cont - 1]  = $cols[4];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[5];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[2];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[0];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[6];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[7];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[8];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[9];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[10];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[1];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[11];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[12];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[13];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[14];
                $dados[$cont - 1] .= $SimboloConcatenacaoArray . $cols[3];
            }
        }

        foreach ($filtroPesquisa as $nomeFiltro => $valor) {
            $tpl->NAME_SEARCH = $nomeFiltro;
            $tpl->VALUE_SEARCH = $valor;
            $tpl->block('BLOCO_SEARCH');
        }

        $mudou = 0;
        
        for ($Row = 0; $Row < $cont; ++$Row) {
            
            $Linha = explode($SimboloConcatenacaoArray, $dados[$Row]);
           

            if ($Linha[0] != '' && $GrupoDescricao != $Linha[0]) {
                $tpl->GRUPO_DESCRICAO = $Linha[0];
                $GrupoDescricao = $Linha[0];
                $tpl->block('BLOCO_GRUPO');
                $mudou = 1;
            }

            if ($OrgaoDescricao != $Linha[9]) {
                $ultimoOrgaoPlotado = $Linha[9];
                $tpl->ORGAO_DESCRICAO = $Linha[9];
                $tpl->block('BLOCO_ORGAO');
                $mudou = 1;
            }

            if ($OrgaoDescricao != $Linha[9]) {
                $tpl->block('BLOCO_CABECALHO');
                $OrgaoDescricao = $Linha[9];
                $mudou = 1;
            }

            if ($mudou == 1) {
                $tpl->block('BLOCO_TITULO');
                $mudou = 0;
            }
            

            $OrgaoDescricao        = $Linha[9];
            $LicitacaoDtAbertura   = substr($Linha[8], 8, 2).'/'.substr($Linha[8], 5, 2).'/'.substr($Linha[8], 0, 4);
            $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);
            $idUltimaFaseLicitacao = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
            $valorEstimado = totalValorEstimado($db, $Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12]);
            $processo = $Linha[3];
            $ano = $Linha[4];
            $comissao = $Linha[11];
            $grupo = $Linha[10];
            $valorFormatado = converte_valor($valorEstimado, 2, ',', '.');
            $comissaoDescricao = comissaoDescricao($db, $Linha[10], $Linha[11], $Linha[4], $Linha[3], $Linha[12]);

            if(empty($valorEstimado) && $grupo == 2){
                $hintValor = 'De acordo com o Art. 34 da Lei 13.303/2016, o valor estimado do contrato a ser celebrado pela empresa pública ou pela sociedade de economia mista será sigiloso, facultando-se à contratante, mediante justificação na fase de preparação prevista no inciso I do art. 51 desta Lei, conferir publicidade ao valor estimado do objeto da licitação, sem prejuízo da divulgação do detalhamento dos quantitativos e das demais informações necessárias para a elaboração das propostas. 
 Obs. A informação relativa ao valor estimado do objeto da licitação, ainda que tenha caráter sigiloso, será disponibilizada a órgãos de controle externo e interno, devendo a empresa pública ou a sociedade de economia mista registrar em documento formal sua disponibilização aos órgãos de controle, sempre que solicitado.';
            }elseif(empty($valorEstimado) && $Linha[1] == 'CREDENCIAMENTO'){
                $hintValor = 'O credenciamento é um procedimento administrativo no qual a Administração convoca interessados para, segundo condições previamente definidas e divulgadas em edital, credenciarem-se como prestadores de serviços ou beneficiários de um negócio futuro e eventual a ser ofertado. Atendidas às condições fixadas, os interessados serão credenciados em condição de igualdade para executar o objeto. ';
            }elseif(empty($valorEstimado) || !empty($valorEstimado)){
                $hintValor = '';
            }

            //busca pelo codigo da scc
                    $sqlSolicitacoesC  = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi ";
                    $sqlSolicitacoesC .= " FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $processo AND SOL.ALICPOANOP =" . $ano ;
                    $sqlSolicitacoesC .= " AND SOL.CCOMLICODI = $comissao AND SOL.cgrempcodi =" . $grupo;

                    $resultSolic = $db->query($sqlSolicitacoesC);
                    if (db::isError($resultSolic)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesC");
                    }
                    $int = 0;
                    while ($llinha = $resultSolic->fetchRow()) {
                        $Solicitacao .= getNumeroSolicitacaoCompra($db, $llinha[0]);
                        $SeqSolicitacao = $llinha[0];
                        $int++;
                    }
                    
                    $barraAno = str_replace(".", "/", substr($Solicitacao, -5));
                    $Solicitacao = substr($Solicitacao, 0, 9) . $barraAno;

            $data = date('Y-m-d', mktime(0, 0, 0, substr($Linha[8], 5, 2), substr($Linha[8], 8, 2), substr($Linha[8], 0, 4)));

            // Descrição da última fase
            $sqlFaseAtual = "SELECT F.EFASESDESC FROM SFPC.TBFASES F WHERE F.CFASESCODI = $idUltimaFaseLicitacao";
            
            $faseAtual = $db->query($sqlFaseAtual);
            
            if (db::isError($faseAtual)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFaseAtual");
            }
            
            $descricaoUltimaFase = resultValorUnico($faseAtual);

            $tpl->PROCESSO = substr($Linha[3] + 10000, 1).'/'.$Linha[4];
            $tpl->LICITACAO = substr($Linha[5] + 10000, 1).'/'.substr($Linha[6] + 10000, 1);
            $tpl->OBJETO = $Linha[7];
            $tpl->DATA_HORA_ABERTURA = $LicitacaoDtAbertura.' '.$LicitacaoHoraAbertura.' h';
            $tpl->MODALIDADE_DESCRICAO = $Linha[1];
            $tpl->COMISSAO_LICITACAO = $comissaoDescricao;
            $tpl->FASE = $descricaoUltimaFase;
            $tpl->VALOR_TOTAL = 'R$ '.$valorFormatado;
            $tpl->SCC =  $Solicitacao;
            $tpl->HINT_VALOR = $hintValor;

            
            if ($i + 1 < count($Linha)) {
                if ($ultimoOrgaoPlotado != $Linha[9]) {
                    $tpl->block('BLOCO_SEPARATOR');
                }
            }
            
            $tpl->URL = $Linha[10].'-'.$Linha[3].'-'.$Linha[4].'-'.$Linha[11].'-'.$Linha[12];
            $tpl->block('BLOCO_VALORES');
            $tpl->block('BLOCO_CORPO');
        }
    }
    $db->disconnect();
    $tpl->show();
} else {
    header('Location: ConsLicitacoesAndamento.php');
    exit();
}
?>