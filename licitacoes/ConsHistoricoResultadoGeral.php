<?php
/**
 * Portal da DGCO
 *
 * Programa: ConsHistoricoResultadoGeral.php
 * @author Pitang - José Francisco <jose.francisco@pitang.com>
 * Data: 19/06/2014 - CR123143]: REDMINE 19 (P6)
 * Alteração [CR123143]: REDMINE 19 (P6): Daniel Semblano <daniel.semblano@pitang.com>
 */

#----------------------------------------------------------------------------
# Alterado:	Pitang
# Data:		19/08/2014 - Limpar formulário ao clicar no botão nova consulta.
#						 Altera exibição dos valores do checkbox para "desmarcado/marcado"
#						 no resultado da pesquisa.
#----------------------------------------------------------------------------
# Alterado:	Pitang
# Data:		26/08/2014 - [CR123143]: REDMINE 19 (P6)
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     10/07/2018
# Objetivo: Tarefa Redmine 73631
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     11/09/2018
# Objetivo: Tarefa Redmine 203345
# -----------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data:     10/10/2018
# Objetivo: Tarefa Redmine 205100
# -----------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data:     17/12/2018
# Objetivo: Tarefa Redmine 200950
# -----------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     05/04/2019
# Objetivo: Tarefa Redmine 214033
# -----------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 15/05/2023
# Objetivo: Cr 282613
# -----------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 30/05/2023
# Objetivo: Cr 283867
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';
include "funcoesLicitacoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsHistoricoPesquisarGeral.php');
AddMenuAcesso('/licitacoes/ConsHistoricoDetalhes.php');

// $arraySituacoesConcluidas = array('11', '12', '13', '15', '17', '18', '19'); // Array com os ids das situações concluídas
$db = Conexao();
$arraySituacoesConcluidas = getIdFasesConcluidas($db);
$arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
define("COL_SPAN", 6);

$Botao                  = $_POST['Botao'];
$ItemId                 = $_SESSION['ItemId'];
$ItemName               = $_SESSION['ItemName'];
$ItemTipo               = $_SESSION['ItemTipo'];
$Objeto                 = strtoupper2($_SESSION['Objeto']);
$OrgaoLicitanteCodigo   = $_SESSION['OrgaoLicitanteCodigo'];
$ComissaoCodigo         = $_SESSION['ComissaoCodigo'];
$ModalidadeCodigo       = $_SESSION['ModalidadeCodigo'];
$tratamentoDiferenciado = $_SESSION['TratamentoDiferenciado'];
$LicitacaoAno           = $_SESSION['licitacaoAno'];
$TipoItemLicitacao      = $_SESSION['TipoItemLicitacao'];
$Item                   = $_SESSION['Item'];
$adminDireta            = $_SESSION['adminDireta'];
$tipoEmpresa            = $_SESSION['tipoEmpresa'];
$licitacaoSituacao      = $_SESSION['licitacaoSituacao'];
$processoNumero         = $_SESSION['processoNumero'];
$processoAno            = $_SESSION['processoAno'];
$legislacao             = $_SESSION['legislacao'];

// Prepara exibição dos filtros
$fragmentoSelect = " SELECT LP.CLICPOPROC ";
$fragmentoFrom = " FROM SFPC.TBLICITACAOPORTAL LP ";
$fragmentoWhere = " WHERE 1 = 1 ";
$queryExiste = false;

if (empty($OrgaoLicitanteCodigo) === false) {
    $fragmentoSelect .= " , OL.EORGLIDESC ";
    $fragmentoFrom .= " , SFPC.TBORGAOLICITANTE OL ";
    $fragmentoWhere .= " AND OL.CORGLICODI = $OrgaoLicitanteCodigo ";
    $queryExiste = true;
}

if (empty($ComissaoCodigo) === false) {
    $fragmentoSelect .= " , CL.ECOMLIDESC ";
    $fragmentoFrom .= " , SFPC.TBCOMISSAOLICITACAO CL ";
    $fragmentoWhere .= " AND CL.CCOMLICODI = $ComissaoCodigo ";
    $queryExiste = true;
}

if (empty($ModalidadeCodigo) === false) {
    $fragmentoSelect .= " , ML.EMODLIDESC ";
    $fragmentoFrom .= " , SFPC.TBMODALIDADELICITACAO ML ";
    $fragmentoWhere .= " AND ML.CMODLICODI = $ModalidadeCodigo ";
    $queryExiste = true;
}

if (empty($tratamentoDiferenciado) === false) {
    $fragmentoSelect .= " , LP.FLICPOVFOR ";
    $fragmentoWhere .= " AND LP.FLICPOVFOR = '$tratamentoDiferenciado' ";
    $queryExiste = true;
}

$descricaoOrgaoLicitante = 'Todos';
$descricaoComissao = 'Todas';
$descricaoModalidade = 'Todas';
$descricaoFase = ucfirst($licitacaoSituacao);

// Verifica se é necessário executar consulta ao banco
if ($queryExiste) {
    $sql = $fragmentoSelect . $fragmentoFrom . $fragmentoWhere . " LIMIT 1";
    $db = Conexao();
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
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
    $descricaoTipoItem = ($TipoItemLicitacao == "M") ? 'Material' : 'Serviço';
}

if (!empty($tratamentoDiferenciado) === false) {
	$descricaoTratamentoDiferenciado = getDescricaoTratamentoDiferenciado($tratamentoDiferenciado);
} else {
	$descricaoTratamentoDiferenciado = 'Não selecionado';
}

$filtroPesquisa = array(
    'Objeto' => ($Objeto == '') ? 'Todos' : $Objeto ,
    'Órgão Licitante' => $descricaoOrgaoLicitante,
    'Administração direta' => ($adminDireta === true) ? 'Marcado' : 'Desmarcado',
    'Comissão' => $descricaoComissao,
    'Número do Processo' => !empty($processoNumero) ? str_pad($processoNumero, 4, '0', STR_PAD_LEFT) : 'Todos',
    'Ano do Processo' => !empty($processoAno) ? $processoAno : 'Todos',
    'Lesgislação de Compra' => ($legislacao)?$legislacao:'Todas',
    'Modalidade' => $descricaoModalidade,
    'Tratamento diferenciado EPP/ME/MEI' => $descricaoTratamentoDiferenciado,
    'Situação' => $descricaoFase,
    'Ano' => ($LicitacaoAno != "" && $licitacaoSituacao == 'concluídas') ? $LicitacaoAno : 'Todos',
    'Microempresa, EPP ou MEI' => ($tipoEmpresa === true) ? 'Marcado' : 'Desmarcado',
    'Item' => $descricaoTipoItem,
    'Descrição item' => ($ItemName == '') ? 'Todas' : $ItemName
);

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsHistoricoResultadoGeral.php";

# Redireciona dados para ConsAcompPesquisar.php se Houve Erro #
if (($Item == "") and ( $TipoItemLicitacao) != "") {
    $_SESSION['Mensagem'] = "Falta digitar o texto do Item";
    $_SESSION['Mens'] = 1;
    $_SESSION['Tipo'] = 1;
    $_SESSION['Objeto'] = $Objeto;
    $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
    $_SESSION['ComissaoCodigo'] = $ComissaoCodigo;
    $_SESSION['ModalidadeCodigo'] = $ModalidadeCodigo;
    $_SESSION['TratamentoDiferenciado'] = $tratamentoDiferenciado;
    $_SESSION['Selecao'] = $Selecao;
    $_SESSION['RetornoPesquisa'] = 1;
    $_SESSION['TipoItemLicitacao'] = $TipoItemLicitacao;
    $_SESSION['Item'] = $ItemName;
    $_SESSION['adminDireta'] = $adminDireta;
    $_SESSION['tipoEmpresa'] = $tipoEmpresa;
    $_SESSION['licitacaoSituacao'] = $licitacaoSituacao;

    header("location: ConsHistoricoPesquisarGeral.php");
    exit();
}

# Redireciona dados para ConsHistoricoPesquisarGeral.php #
if ($Botao == "Pesquisa") {
    $Url = "ConsHistoricoPesquisarGeral.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
}

$Mens = 0;
if ($Mens == 0) {
    $db = Conexao();

    $novaSql = '';

    // SELECT
    $novaSql .= " SELECT ";
    $novaSql .= " distinct A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ";
    $novaSql .= " A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
    $novaSql .= " A.TLICPODHAB, A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, e.CFASESCODI, A.FLICPOVFOR ";

    // FROM
    $novaSql .= ' FROM ';

    if ($TipoItemLicitacao == 'M') {
        $novaSql .= " SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ";
    }

    if ($TipoItemLicitacao == 'S') {
        $novaSql .= " SFPC.tbitemlicitacaoportal F, SFPC.tbservicoportal G, ";
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
    if ($tipoEmpresa === true) {
        $novaSql .= " LEFT OUTER JOIN SFPC.TBITEMLICITACAOPORTAL ILP ";
        $novaSql .= " ON ILP.CLICPOPROC = A.CLICPOPROC ";
        $novaSql .= " AND ILP.ALICPOANOP = A.ALICPOANOP ";
        $novaSql .= " AND ILP.CGREMPCODI = A.CGREMPCODI ";
        $novaSql .= " AND ILP.CCOMLICODI = A.CCOMLICODI ";
        $novaSql .= " AND ILP.CORGLICODI = A.CORGLICODI INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FC ";
        $novaSql .= " ON ILP.AFORCRSEQU = FC.AFORCRSEQU ";
        $novaSql .= " AND FC.FFORCRMEPP IS NOT NULL ";
    }

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

    if ($OrgaoLicitanteCodigo != "") {
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
    if ($Objeto != "") {
        $novaSql .= " AND upper(A.XLICPOOBJE) LIKE '%$Objeto%'";
    }

    if($legislacao == '14133'){
        $novaSql .= " AND a.flicpolegi = '14133' ";
    }else{
        $novaSql .= " AND (a.flicpolegi is null or a.flicpolegi = '8666') ";
    }

    // Administração direta
    if ($adminDireta === true) {
        $novaSql .= " AND a.CGREMPCODI = 1 "; // Grupo administração direta
    }

    // Comissão
    if ($ComissaoCodigo != "") {
        $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
    }

    // Modalidade
    if ($ModalidadeCodigo != "") {
        $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
    }

    // Tratamento diferenciado
    if ($tratamentoDiferenciado != "") {
        $novaSql .= " AND A.FLICPOVFOR = '$tratamentoDiferenciado' ";
    }

    // Processo e ano
    if(!empty($processoNumero)) {
        $novaSql .= " AND A.CLICPOPROC = $processoNumero ";
    }

    if(!empty($processoAno)) {
        $novaSql .= " AND A.ALICPOANOP = $processoAno ";
    }

    // Situação
    if ($licitacaoSituacao != "") {
        if ($licitacaoSituacao == 'concluídas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $novaSql   .= " AND D.CFASESCODI IN ($strIdConcluidas) ";
        } elseif ($licitacaoSituacao == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $novaSql   .= " AND D.CFASESCODI IN ($strIdAndamento) ";
        }

        if ($LicitacaoAno != "" && $licitacaoSituacao == 'concluídas') {
            $novaSql .= " AND EXTRACT(YEAR FROM D.TFASELDATA) = '$LicitacaoAno' ";
        }
    }

    // Item
    if (($TipoItemLicitacao == 'M') or ($TipoItemLicitacao == 'S')) {
        $novaSql .= " AND A.CLICPOPROC = F.CLICPOPROC ";
        $novaSql .= " AND A.ALICPOANOP = F.ALICPOANOP ";
        $novaSql .= " AND A.CGREMPCODI = F.CGREMPCODI ";
        $novaSql .= " AND A.CCOMLICODI = F.CCOMLICODI ";
        $novaSql .= " AND A.CORGLICODI = F.CORGLICODI ";
    }

    // Descrição do item material
	if ($TipoItemLicitacao == 'M') {
		$novaSql .= " AND F.CMATEPSEQU = G.CMATEPSEQU ";
		$novaSql .= " AND (G.EMATEPDESC ILIKE '%$ItemName%') ";
    }

    // Descrição do item serviço
    if ($TipoItemLicitacao == 'S') {
        $novaSql .= " AND F.CSERVPSEQU = G.CSERVPSEQU ";
        $novaSql .= " AND (G.ESERVPDESC ILIKE '%$ItemName%') ";
    }

    // ORDER BY
    $novaSql .= " ORDER BY GE.EGREMPDESC, ML.EMODLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
  
    $result = $db->query($novaSql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $novaSql");
    }

    // $arrayIntermediario serve para armazenar as licitações que serão fundidas com as licitações sem fase dentro do bloco [CUSTOMIZAÇÃO]
    $arrayIntermediario = array();

    $cont = 0;
    while ($cols = $result->fetchRow()) {
        $cont++;

        // Caso a situação seja diferente de concluídas o array $dados será gerado dentro do bloco [CUSTOMIZAÇÃO]
        // do contrário segue o fluxo normal
        if ($licitacaoSituacao != 'concluídas') {
        	$arrayIntermediario[$cont-1] = $cols;
        } else {
            $dados[$cont-1] = $cols[4];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[5];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[2];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[0];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[6];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[7];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[8];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[9];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[10];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[1];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[11];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[12];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[13];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[14];
            $dados[$cont-1] .= $SimboloConcatenacaoArray . $cols[3];

        }
    }

    // [CUSTOMIZAÇÃO] - Licitações sem fase associada

    if ($licitacaoSituacao != 'concluídas') {
    	$sqlSolicitacoesSemFase = '';

    	// SELECT
    	$sqlSolicitacoesSemFase .= " SELECT ";
    	$sqlSolicitacoesSemFase .= " distinct A.CLICPOPROC, B.EORGLIDESC , CD.ECOMLIDESC , CD.ECOMLIDESC , GE.EGREMPDESC , ML.EMODLIDESC , ";
    	$sqlSolicitacoesSemFase .= " A.ALICPOANOP , A.CLICPOCODL , A.ALICPOANOL , A.XLICPOOBJE , A.TLICPODHAB , ";
    	$sqlSolicitacoesSemFase .= " A.CGREMPCODI , A.CCOMLICODI , A.CORGLICODI , D.CFASESCODI, A.FLICPOVFOR ";

    	// FROM
    	$sqlSolicitacoesSemFase .= ' FROM ';

    	if ($TipoItemLicitacao == 'M') {
    		$sqlSolicitacoesSemFase .= " SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ";
    	}

    	if ($TipoItemLicitacao == 'S') {
    		$sqlSolicitacoesSemFase .= " SFPC.tbitemlicitacaoportal F, SFPC.tbservicoportal G, ";
    	}

    	$sqlSolicitacoesSemFase .= " SFPC.TBFASES e, ";
    	$sqlSolicitacoesSemFase .= ' SFPC.TBLICITACAOPORTAL A ';

    	// Microempresa, EPP ou MEI
    	if ($tipoEmpresa === true) {
    		$sqlSolicitacoesSemFase .= " LEFT OUTER JOIN SFPC.TBITEMLICITACAOPORTAL ILP ";
    		$sqlSolicitacoesSemFase .= " ON ILP.CLICPOPROC = A.CLICPOPROC ";
    		$sqlSolicitacoesSemFase .= " AND ILP.ALICPOANOP = A.ALICPOANOP ";
    		$sqlSolicitacoesSemFase .= " AND ILP.CGREMPCODI = A.CGREMPCODI ";
    		$sqlSolicitacoesSemFase .= " AND ILP.CCOMLICODI = A.CCOMLICODI ";
    		$sqlSolicitacoesSemFase .= " AND ILP.CORGLICODI = A.CORGLICODI INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FC ";
    		$sqlSolicitacoesSemFase .= " ON ILP.AFORCRSEQU = FC.AFORCRSEQU ";
    		$sqlSolicitacoesSemFase .= " AND FC.FFORCRMEPP IS NOT NULL ";
    	}

    	// JOIN Comissão licitação
    	$sqlSolicitacoesSemFase .= ' INNER JOIN SFPC.TBCOMISSAOLICITACAO CD ';
    	$sqlSolicitacoesSemFase .= ' ON CD.CCOMLICODI = A.CCOMLICODI ';

    	// JOIN Grupo
    	$sqlSolicitacoesSemFase .= ' INNER JOIN SFPC.TBGRUPOORGAO GO ';
    	$sqlSolicitacoesSemFase .= ' ON GO.CGREMPCODI = A.CGREMPCODI AND GO.CORGLICODI = A.CORGLICODI ';
    	$sqlSolicitacoesSemFase .= ' INNER JOIN SFPC.TBGRUPOEMPRESA GE ';
    	$sqlSolicitacoesSemFase .= ' ON GE.CGREMPCODI = GO.CGREMPCODI ';

    	// JOIN Modalidade
    	$sqlSolicitacoesSemFase .= ' INNER JOIN SFPC.TBMODALIDADELICITACAO ML ';
    	$sqlSolicitacoesSemFase .= ' ON ML.CMODLICODI = A.CMODLICODI ';

    	// JOIN Órgão licitante
    	$sqlSolicitacoesSemFase .= ' INNER JOIN SFPC.TBORGAOLICITANTE B ';
    	$sqlSolicitacoesSemFase .= ' ON B.CORGLICODI = A.CORGLICODI ';

    	if ($OrgaoLicitanteCodigo != "") {
    		$sqlSolicitacoesSemFase .= " AND A.CORGLICODI = '$OrgaoLicitanteCodigo' ";
    	}

    	$sqlSolicitacoesSemFase .= "LEFT OUTER JOIN SFPC.TBFASELICITACAO D
								        ON A.CLICPOPROC = D.CLICPOPROC
								    AND A.ALICPOANOP = D.ALICPOANOP
								    AND A.CGREMPCODI = D.CGREMPCODI
								    AND A.CCOMLICODI = D.CCOMLICODI
								    AND A.CORGLICODI = D.CORGLICODI";

    	// WHERE
    	$sqlSolicitacoesSemFase .= ' WHERE D.CFASESCODI IS NULL
	                      			 	   AND a.CCOMLICODI NOT IN(41) ';

    	// Objeto
    	if ($Objeto != "") {
    		$sqlSolicitacoesSemFase .= " AND upper(A.XLICPOOBJE) LIKE '%$Objeto%'";
    	}

        if($legislacao == '14133'){
            $sqlSolicitacoesSemFase .= " AND a.flicpolegi = '14133' ";
        }else{
            $sqlSolicitacoesSemFase .= " AND (a.flicpolegi is null or a.flicpolegi = '8666') ";
        }
    	// Administração direta
    	if ($adminDireta === true) {
    		$sqlSolicitacoesSemFase .= " AND a.CGREMPCODI = 1 "; // Grupo administração direta
    		$sqlSolicitacoesSemFase .= " AND a.CCOMLICODI IN (1, 2, 3, 4, 8, 40, 34, 39, 42, 44) ";
    	}

    	// Comissão
    	if ($ComissaoCodigo != "") {
    		$sqlSolicitacoesSemFase .= " AND A.CCOMLICODI = $ComissaoCodigo ";
    	}

    	// Modalidade
    	if ($ModalidadeCodigo != "") {
    		$sqlSolicitacoesSemFase .= " AND A.CMODLICODI = $ModalidadeCodigo ";
        }
        
        // Modalidade
    	if ($tratamentoDiferenciado != "") {
    		$sqlSolicitacoesSemFase .= " AND A.FLICPOVFOR = '$tratamentoDiferenciado' ";
    	}

    	// Item
    	if (($TipoItemLicitacao == 'M') or ($TipoItemLicitacao == 'S')) {
    		$sqlSolicitacoesSemFase .= " AND A.CLICPOPROC = F.CLICPOPROC ";
    		$sqlSolicitacoesSemFase .= " AND A.ALICPOANOP = F.ALICPOANOP ";
    		$sqlSolicitacoesSemFase .= " AND A.CGREMPCODI = F.CGREMPCODI ";
    		$sqlSolicitacoesSemFase .= " AND A.CCOMLICODI = F.CCOMLICODI ";
    		$sqlSolicitacoesSemFase .= " AND A.CORGLICODI = F.CORGLICODI ";
    	}

    	// Descrição do item material
    	if ($TipoItemLicitacao == 'M') {
    		$sqlSolicitacoesSemFase .= " AND F.CMATEPSEQU = G.CMATEPSEQU ";
    		$sqlSolicitacoesSemFase .= " AND (G.EMATEPDESC ILIKE '%$ItemName%') ";
    	}

    	// Descrição do item serviço
    	if ($TipoItemLicitacao == 'S') {
    		$sqlSolicitacoesSemFase .= " AND F.CSERVPSEQU = G.CSERVPSEQU ";
    		$sqlSolicitacoesSemFase .= " AND (G.ESERVPDESC ILIKE '%$ItemName%') ";
    	}

        // Processo e ano
        if(!empty($processoNumero)) {
            $sqlSolicitacoesSemFase .= " AND A.CLICPOPROC = $processoNumero ";
        }

        if(!empty($processoAno)) {
            $sqlSolicitacoesSemFase .= " AND A.ALICPOANOP = $processoAno ";
        }

    	// ORDER BY
    	$sqlSolicitacoesSemFase .= " ORDER BY GE.EGREMPDESC, ML.EMODLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";

    	$resultadoSolicitacoesSemFase = $db->query($sqlSolicitacoesSemFase);

    	if (PEAR::isError($resultadoSolicitacoesSemFase)) {
    		ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesSemFase");
    	}

    	$contSolicitacoesSemFase = $cont;
    	$arrayIntermediarioSemFase = array();

    	// Lê as linhas de resultado da consulta de licitações sem fase e gera um array
    	while ($cols = $resultadoSolicitacoesSemFase->fetchRow()) {
    		$contSolicitacoesSemFase++;
    		$cols[14] = 0; // Atribui 0 (zero) à posição que corresponde ao código da fase, pois a licitação não possui fase
    		$arrayIntermediarioSemFase[$contSolicitacoesSemFase-1] = $cols;
    	}

    	// Faz um merge do array de licitações com fase e do array de licitações sem fase
    	$licitacoesCompletas = array_merge($arrayIntermediario, $arrayIntermediarioSemFase);

    	$ordenarGrupo = array();
    	$ordenarModalidade = array();
    	$ordenarComissao = array();
    	$ordenarAno = array();
    	$ordenarCodigoProcesso = array();

    	// Gera um array com chaves que definirão a ordenação das licitações
    	foreach ($licitacoesCompletas as $licitacao) {
    		$ordenarGrupo[] = $licitacao[4];
    		$ordenarModalidade[] = $licitacao[5];
    		$ordenarComissao[] = $licitacao[2];
    		$ordenarAno[] = $licitacao[6];
    		$ordenarCodigoProcesso[] = $licitacao[0];
    	}

    	// Ordenando o array de licitações por chaves específicas
    	array_multisort($ordenarGrupo, SORT_ASC,
    					$ordenarModalidade, SORT_ASC,
    					$ordenarComissao, SORT_ASC,
    					$ordenarAno, SORT_DESC,
    					$ordenarCodigoProcesso, SORT_DESC,
    					$licitacoesCompletas);

    	// Gera um array onde o valor de cada posição está no formato que o renderizador de HTML da EMPREL espera
    	foreach ($licitacoesCompletas as $posicao => $licitacao) {
    		$cols = $licitacao;
    		$dados[$posicao] = $cols[4];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[5];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[2];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[0];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[6];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[7];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[8];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[9];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[10];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[1];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[11];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[12];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[13];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[14];
            $dados[$posicao] .= $SimboloConcatenacaoArray . $cols[3];

        }

    	// Atribui o total de licitações à $cont
    	$cont = count($dados);
    }
    // [/CUSTOMIZAÇÃO]

    $GrupoDescricao = "";

    if ($cont != 0) {
        echo "<html>\n";
        # Carrega o layout padrão #
        layout();
        echo "<script language=\"javascript\" type=\"text/javascript\">\n";
        echo "<!--\n";
        echo "function enviar(valor) {\n";
        echo "	document.Historico.Botao.value=valor;\n";
        echo "	document.Historico.submit();\n";
        echo "}\n";
        MenuAcesso();
        echo "//-->\n";
        echo "</script>\n";
        echo "<link rel=\"Stylesheet\" type=\"Text/Css\" href=\"../estilo.css\">\n";
        ?>
        <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
            <script language="JavaScript" src="../menu.js"></script>
            <script language="JavaScript">Init();</script>
            <?php
            echo "<form action=\"ConsHistoricoResultadoGeral.php\" method=\"post\" name=\"Historico\">\n";
            echo "<br><br><br><br><br>\n";
            echo "<table cellpadding=\"3\" border=\"0\">\n";
            echo "  <!-- Caminho -->\n";
            echo "  <tr>\n";
            echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
            echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\">\n";
            echo "      <font class=\"titulo2\">|</font>\n";
            echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Histórico\n";
            echo "    </td>\n";
            echo "  </tr>\n";
            echo "  <!-- Fim do Caminho-->\n";
            echo "	<!-- Erro -->\n";
            if ($Mens == 1) {
                echo "	<tr>\n";
                echo "	  <td width=\"100\"></td>\n";
                echo "	  <td align=\"left\" colspan=\"2\">\n";
                if ($Mens == 1) {
                    ExibeMens($Mensagem, $Tipo, 1);
                }
                echo "    </td>\n";
                echo "	</tr>\n";
            }
            echo "	<!-- Fim do Erro -->\n";
            echo "	<!-- Corpo -->\n";
            echo "	<tr>\n";
            echo "		<td width=\"100\"></td>\n";
            echo "		<td class=\"textonormal\">\n";
            echo "      <table  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#FFFFFF\">\n";
            echo "        <tr>\n";
            echo "	      	<td class=\"textonormal\">\n";
            echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
            echo "	          	<tr>\n";
            echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"6\">\n";
            echo "		    					<font class=\"titulo3\">HISTÓRICO DE LICITAÇÕES\n";
            echo "		    					- RESULTADO</font>\n";
            echo "		          	</td>\n";
            echo "		        	</tr>\n";
            echo "	          	<tr>\n";
            echo "	            	<td colspan=\"6\" class=\"textonormal\">\n";
            echo "	        	    		Para visualizar mais informações sobre a Licitação, clique no número da Licitação desejada. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\".\n";
            echo "		          	</td>\n";
            echo "		        	</tr>\n";
            echo "	          	<tr>\n";
            echo "   			  			<td class=\"textonormal\" align=\"right\" colspan=\"6\">\n";
            echo "	          			<input type=\"button\" name=\"Pesquisa\" value=\"Nova Pesquisa\" class=\"botao\" onclick=\"javascript:enviar('Pesquisa');\">\n";
            echo "			          	<input type=\"hidden\" name=\"Botao\" value=\"\">\n";
            echo "          			</td>\n";
            echo "		        	</tr>\n";

            // [Filtros da pesquisa]
            echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"".COL_SPAN."\" bgcolor=\"#DCEDF7\">CRITÉRIOS DE PESQUISA</td></tr>\n";
            foreach ($filtroPesquisa as $nomeFiltro => $valor) {
                echo "<tr>";
                echo "<td colspan=\"2\" valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">$nomeFiltro</td>";
                echo "<td colspan=\"4\" valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$valor</td>";
                echo "</tr>";
            }
            // [/Filtros da pesquisa]

            //while ($Linha = $result->fetchRow()) {
            for ($Row = 0 ; $Row < $cont ; $Row++) {
                $Linha = explode($SimboloConcatenacaoArray,$dados[$Row]);

                if ($GrupoDescricao != $Linha[0]) {
                    $GrupoDescricao = $Linha[0];
                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"6\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
                    $ModalidadeDescricao = "";
                }

                if ($ModalidadeDescricao != $Linha[1]) {
                    $ModalidadeDescricao = $Linha[1];
                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"6\">$ModalidadeDescricao <input type=\"hidden\" name=\"Selecao\" value=$Selecao size=\"1\"></td>\n";
                    echo "</tr>\n";
                    $ComissaoDescricao = "";
                }

                if ($ComissaoDescricao != $Linha[2]) {
                    $ComissaoDescricao = $Linha[2];
                    echo "<tr><td class=\"titulo2\" colspan=\"6\" color=\"#000000\">$ComissaoDescricao</tr></td>\n";
                    echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">SITUAÇÃO</td></tr>\n";
                }

                $NProcesso  = substr($Linha[3] + 10000,1);
                $NLicitacao = substr($Linha[4] + 10000,1);
                $Linha[5]   = substr($Linha[5] + 10000,1);
                $Linha[6]   = substr($Linha[6] + 10000,1);

                $LicitacaoDtAbertura = substr($Linha[8], 8, 2) . "/" . substr($Linha[8], 5, 2) . "/" . substr($Linha[8], 0, 4);
                $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);

                echo "<tr>\n";
                $Url = "ConsHistoricoDetalhes.php";
                $Parametros = "?GrupoCodigo=$GrupoCodigo&LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Objeto=$Objeto&GrupoCodigoDet=$Linha[10]&LicitacaoProcessoDet=$Linha[3]&LicitacaoAnoDet=$Linha[4]&ComissaoCodigoDet=$Linha[11]&OrgaoLicitanteCodigoDet=$Linha[12]&TipoItemLicitacao=$TipoItemLicitacao&Item=$Item";
                $Url .= $Parametros;

                if (!in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }

                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"$Url\"><font color=\"#000000\">$NProcesso/$Linha[4]</font></td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]/$Linha[6]</font></td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura&nbsp;h</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";                
                $idUltimaFaseLicitacao = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
                $situacaoAtualLicitacao = 'EM ANDAMENTO';

                if (in_array($idUltimaFaseLicitacao, $arraySituacoesConcluidas)) {
                    $situacaoAtualLicitacao = 'CONCLUÍDA';
                }

                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$situacaoAtualLicitacao</td>\n";
                echo "</tr>\n";
            }
        } else {
            $Mens = 1;
            $Tipo = 1;
            $Critica = 0;
            $Mensagem = "Nenhuma ocorrência foi encontrada";
            $Mensagem = urlencode($Mensagem);
            # Envia mensagem para página selecionar #
            $Url = "ConsHistoricoPesquisarGeral.php?Mensagem=$Mensagem&Mens=$Mens&Tipo=$Tipo&Selecao=$Selecao&Objeto=$Objeto&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ComissaoCodigo=$ComissaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&TipoItemLicitacao=$TipoItemLicitacao&Item=$Item&processoNumero=$processoNumero&processoAno=$processoAno";
            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }
            header("location: " . $Url);
            exit();
        }
        echo "    	  	  </table>\n";
        echo "					</td>\n";
        echo "				</tr>\n";
        echo "      </table>\n";
        echo "		</td>\n";
        echo "	</tr>\n";
        echo "	<!-- Fim do Corpo -->\n";
        echo "</table>\n";
        echo "</form>\n";
        echo "</body>\n";
        echo "</html>\n";
        $db->disconnect();
    }
    unset($_SESSION['item']);
    ?>

