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
 * @version GIT: v1.40.0
 *
 ** -----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 04/09/2015
 * Objetivo: CR 95697 - Todas as Licitações - internet - tela em branco
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 18/01/2016
 * Objetivo: Bug 121918 - Todas as Licitações - Intranet - erro de exibição dos dados dos itens
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 29/04/2016
 * Objetivo: Requisito 129783 - Correção de exibição das fases de licitação
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 06/05/2016
 * Objetivo: Bug 129506 - Licitações Concluídas - resultado processo
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 27/04/2018
 * Objetivo:CR 145375 - [INTERNET] Licitações - Alterações em Todas as Licitações
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/05/2018
 * Objetivo: Tarefa Redmine 194559
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     15/04/2019
 * Objetivo: Tarefa Redmine 214270
 * -----------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     28/01/2020
 * Objetivo: Tarefa Redmine 229264
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     20/02/2023
 * Objetivo: Tarefa Redmine 245983
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     23/02/2023
 * Objetivo: Tarefa Redmine 245977
 * -----------------------------------------------------------------------------
 */
if (! @require_once dirname(__FILE__) . '/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}

if (! @require_once dirname(__FILE__) . '/../licitacoes/funcoesLicitacoes.php') {
    throw new Exception('Error Processing Request - funcoesLicitacoes.php', 1);
}

if (! @require_once dirname(__FILE__) . '/../compras/funcoesCompras.php') {
    throw new Exception('Error Processing Request - funcoesCompras.php', 1);
}

ini_set('display_errors', 0);
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ALL ^ E_WARNING);
class Pitang_App_ConsTodasLicitacoesDetalhe
{
    public static function validarHttpReferer()
    {
        if ($_SERVER ['HTTP_REFERER'] != HelperPitang::getUrlBase() . 'ConsTodasLicitacoesResultado.php') {
            Pitang_GetUrl::run('ConsTodasLicitacoes.php', true);
        }
    }
}
Pitang_App_ConsTodasLicitacoesDetalhe::validarHttpReferer();

$ErroPrograma = __FILE__;

AddMenuAcesso('/app/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php');

// $Selecao = $_SESSION['Selecao'];

$GrupoCodigo = $_SESSION ['GrupoCodigoDet'];
$Processo = $_SESSION ['ProcessoDet'];
$ProcessoAno = $_SESSION ['ProcessoAnoDet'];
$ComissaoCodigo = $_SESSION ['ComissaoCodigoDet'];
$OrgaoLicitanteCodigo = $_SESSION ['OrgaoLicitanteCodigoDet'];
// $Lote = $_SESSION['Lote'];
// $Ordem = $_SESSION['Ordem'];

// se algum dado do processo estiver vazio deve retorna para listar todas licitações
if (empty($GrupoCodigo) || empty($ComissaoCodigo) || empty($ProcessoAno) || empty($Processo) || empty($OrgaoLicitanteCodigo)) {
    Pitang_GetUrl::run('ConsTodasLicitacoes.php', true);
}

$_SESSION ['PermitirAuditoria'] = 'N'; // Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
resetArquivoAcesso();
$fasesComResultado = array(
        13,
        15
); // Fases que podem ter resultado

// Resgata as informações da licitação #
$db = Conexao();

$ultimaFase = ultimaFase($Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);


$sql = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
$sql .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
$sql .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
//$sql .= "       D.VLICPOTGES, D.flicpovfor,C.NCOMLIPRES, C.ECOMLILOCA, C.ACOMLIFONE, C.ACOMLINFAX ";
$sql .= "       D.VLICPOTGES, D.FLICPODEMC, C.NCOMLIPRES, C.ECOMLILOCA, C.ACOMLIFONE, C.ACOMLINFAX, C.CCOMLICODI  ";
$sql .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
$sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
$sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
$sql .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
$sql .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
$sql .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    
    while ($Linha = $result->fetchRow()) {
        $GrupoDesc = $Linha[0];
        $ModalidadeDesc = $Linha[1];
        $ComissaoDesc = $Linha[2];
        $OrgaoLicitacao = $Linha[4];
        $ObjetoLicitacao = $Linha[3];
        $Licitacao = substr($Linha[6] + 10000, 1);
        $AnoLicitacao = $Linha[7];
        $LicitacaoDtAbertura = substr($Linha[5], 8, 2) . "/" . substr($Linha[5], 5, 2) . "/" . substr($Linha[5], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[5], 11, 5);

        $nomePresidente = $Linha[14];
        $endereco = $Linha[15];
        $telefone = $Linha[16];
        $fax = $Linha[17];
        $comissao = $Linha[18];

        $RegistroPreco = "NÃO";
        if ($Linha[8] == "S") {
            $RegistroPreco = "SIM";
        }
        
        $ModalidadeCodigo = $Linha [9];
        $dbase = Conexao();
        $ValorEstimado = totalValorEstimado($dbase, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
        $dbase->disconnect();
        
        $ValorEstimado = "0,00";
        if (!empty($ValorEstimado)) {
            $ValorEstimado = converte_valor($ValorEstimado);
        }
        
         $ValorHomologado = converte_valor($Linha[11]);
         $TotalGeralEstimado = converte_valor($Linha[12]);

        $validacaoFornecedor = "NÃO";
        if ($Linha[13] == "S") {
            $validacaoFornecedor = "SIM";
        }
    }
}

function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta) {
    $sql  = "SELECT A.CARPINCODN, A.EARPINOBJE, A.AARPINANON, A.AARPINPZVG, A.TARPINDINI, A.CGREMPCODI, A.CUSUPOCODI, ";
    $sql .= "       F.NFORCRRAZS, D.EDOCLINOME, A.CORGLICODI, A.CARPNOSEQU, A.ALICPOANOP, S.CSOLCOSEQU, A.AARPINANON, ";
    $sql .= "       A.CARPNOSEQ1, F.NFORCRRAZS, F.AFORCRCCGC, F.AFORCRCCPF, F.EFORCRLOGR, F.AFORCRNUME, F.EFORCRBAIR, ";
    $sql .= "       F.NFORCRCIDA, F.CFORCRESTA, FA.NFORCRRAZS AS razaoFornecedorAtual, FA.AFORCRCCGC AS cgcFornecedorAtual, ";
    $sql .= "       FA.AFORCRCCPF AS cpfFornecedorAtual, FA.EFORCRLOGR AS logradouroFornecedorAtual, ";
    $sql .= "       FA.AFORCRNUME AS numeroEnderecoFornecedorAtual, FA.EFORCRBAIR AS bairroFornecedorAtual, FA.NFORCRCIDA AS cidadeFornecedorAtual, ";
    $sql .= "       FA.CFORCRESTA AS estadoFornecedorAtual ";
    $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA A ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL S ON (S.CLICPOPROC = A.CLICPOPROC ";
    $sql .= "                                                               AND S.ALICPOANOP = A.ALICPOANOP ";
    $sql .= "                                                               AND S.CCOMLICODI = A.CCOMLICODI ";
    $sql .= "                                                               AND S.CORGLICODI = A.CORGLICODI) ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO F ON F.AFORCRSEQU = A.AFORCRSEQU ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO FA ON FA.AFORCRSEQU = (SELECT AFA.AFORCRSEQU FROM SFPC.TBATAREGISTROPRECOINTERNA AFA WHERE AFA.CARPNOSEQU = A.CARPNOSEQ1) ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBDOCUMENTOLICITACAO D ON D.CLICPOPROC = A.CLICPOPROC ";
    $sql .= "                                                      AND D.CLICPOPROC = " . $processo;
    $sql .= "                                                      AND D.CORGLICODI = " . $orgao;
    $sql .= "                                                      AND D.ALICPOANOP = " . $ano;
    $sql .= " WHERE A.CARPNOSEQU = " . $chaveAta;
    return $sql;
}

// Busca o número da Ata de Resgistro de Preço
$db = Conexao();
$sqlAta = 'select carpnosequ, aarpinanon
        from sfpc.tbataregistroprecointerna
        where clicpoproc = '.$Processo.' and alicpoanop = '.$ProcessoAno.' and cgrempcodi = '.$GrupoCodigo.' and ccomlicodi = '.$ComissaoCodigo.' 
        and corglicodi = '.$OrgaoLicitanteCodigo.'';

$resultAta = $db->query($sqlAta);
if (PEAR::isError($resultAta)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlAta");
}
$LinhaAta = $resultAta->fetchRow();
$ARP    = $LinhaAta[0];
$AnoARP = $LinhaAta[1];

// Busca o sequ da Solicitação de compra para em seguida filtrar na tb de contratos
//---------------------------------------------------------------------------------
$sqlCompra = 'select csolcosequ
        from sfpc.tbsolicitacaocompra
        where clicpoproc = '.$Processo.' and alicpoanop = '.$ProcessoAno.' and cgrempcodi = '.$GrupoCodigo.' and ccomlicodi = '.$ComissaoCodigo.' 
        and corglicodi = '.$OrgaoLicitanteCodigo.'';

$resultCompra = $db->query($sqlCompra);
$sequCompra = $resultCompra->fetchRow();

if (PEAR::isError($resultCompra)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlCompra");
}
$sequContrato = '';
if(!empty($sequCompra)){
$sqlContrato = 'select cdocpcsequ, ectrpcnumf
        from sfpc.tbcontratosfpc
        where csolcosequ = '.$sequCompra[0].'';

$resultContrato = $db->query($sqlContrato);
if (PEAR::isError($resultContrato)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlCompra");
}

$LinhaContrato = $resultContrato->fetchRow();
$sequContrato = $LinhaContrato[0];
$numContrato = $LinhaContrato[1];
}
//------------------------------------------------------------------------------------
if(!empty($ARP)){
// Número da ata
$dto = array();
$sqlConsultaAta = sqlAtaPorchave($Processo, $OrgaoLicitanteCodigo, $ProcessoAno, $ARP);
$resultado = executarSQL($db, $sqlConsultaAta);
$resultado->fetchInto($consultaAta, DB_FETCHMODE_OBJECT);

$sql = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi FROM sfpc.tbcentrocustoportal ccp WHERE 1=1 ";  
if ($consultaAta->corglicodi != null || $consultaAta->corglicodi != "") {
    $sql .= " AND ccp.corglicodi = " . $consultaAta->corglicodi;
}
$res = executarSQL($db, $sql);
$itens = array();
$item = null;
while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
    $itens[] = $item;
}
    
$obj            = current($itens);
$numeroAta      = $obj->ccenpocorg . str_pad($obj->ccenpounid, 2, '0', STR_PAD_LEFT);
$numeroAta      .= "." . str_pad($consultaAta->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $AnoARP;
}

$db = Conexao();
// //================= Kim inicio ====================================
$sqlSolicitacoesC  = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi ";
$sqlSolicitacoesC .= " FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP =" . $ProcessoAno ;
$sqlSolicitacoesC .= " AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =" . $GrupoCodigo;

$resultSolic = $db->query($sqlSolicitacoesC);
$ultimaFase = ultimaFase($Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
if (PEAR::isError($resultSolic)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoesC");
}
$Solicitacao = '';
$int = 0;
while ($Linha = $resultSolic->fetchRow()) {
if ($int > 0) {
    $Solicitacao .= ' - ';
}
$Solicitacao .= getNumeroSolicitacaoCompra($db, $Linha[0]);
$SeqSolicitacao = $Linha[0];
$int++;
}
$dadosIRP = null;
if(!empty($SeqSolicitacao)){
    // --- Busca valores para montar valor da IRP ---
    $sqlIrp = "select cintrpsequ, cintrpsano from sfpc.tbsolicitacaocompra where csolcosequ = $SeqSolicitacao";

    $resultIRP = $db->query($sqlIrp);

    if (PEAR::isError($resultIRP)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlIrp");
    }
    $dadosIRP = $resultIRP->fetchRow();
}

// //=================================  Kim fim ====================

$tpl = new TemplateAppPadrao('templates/ConsTodasLicitacoesDetalhes.html', 'ConsTodasLicitacoesResultado');


$tpl->COLSPAN_GERAL = 12;
$tpl->COLSPAN_GERAL_SUP = 8;
$tpl->COLSPAN_GERAL_HIST = 1;
            
$existeDadosExtras = false;
if (! empty($nomePresidente)) {
    $tpl->VALOR_PRESIDENTE = "PRESIDENTE: $nomePresidente";
    $tpl->block("BLOCO_NOME_PRESIDENTE");
}
if (! empty($endereco)) {
    $tpl->VALOR_ENDERECO = "ENDEREÇO: $endereco";
    $existeDadosExtras = true;
}
if (! empty($telefone)) {
    $tpl->VALOR_TELEFONE = "- TEL: $telefone";
    $existeDadosExtras = true;
}
if (! empty($fax)) {
    $tpl->VALOR_FAX = "- FAX: $fax";
    $existeDadosExtras = true;
}
if ($existeDadosExtras) {
    $tpl->block("BLOCO_DADOS_EXTRAS");
}

$tpl->GRUPO_DESCRICAO = $GrupoDesc;
$tpl->MODALIDADE_DESCRICAO = $ModalidadeDesc;
$tpl->COMISSAO_DESCRICAO = $ComissaoDesc;

$Processo = substr($Processo + 10000, 1);

$tpl->PROCESSO = "$Processo/$ProcessoAno";
$tpl->LICITACAO = "$Licitacao/$AnoLicitacao";

if ($ModalidadeCodigo == 3 or $ModalidadeCodigo == 2) {
    $tpl->block("BLOCO_PERMISSAO_REMUNERADA");
}

$tpl->REGISTRO_PRECO = $RegistroPreco;
$tpl->OBJETO = $ObjetoLicitacao;
$tpl->DATA_ABERTURA = "$LicitacaoDtAbertura $LicitacaoHoraAbertura";
$tpl->ORGAO_LICITANTE = $OrgaoLicitacao;
$tpl->NECESSIDADE_DEMONSTRACOES = $validacaoFornecedor;
$tpl->SCC               = $Solicitacao;
$tpl->LINKSCC           = 'ConsAcompSolicitacaoCompraVersaoAPP.php?SeqSolicitacao='.$SeqSolicitacao.'&programa=window&irp='.$dadosIRP[0].'&ano='.$dadosIRP[1];
$tpl->ARP               = $ARP;
$tpl->PROCESSO          = $Processo;
$tpl->PROCESSOANO       = $ProcessoAno;
$tpl->CODIGOGRUPO       = $GrupoCodigo;
$tpl->CODIGOCOMISSAO    = $ComissaoCodigo;
$tpl->ORGAOLICITANTE    = $OrgaoLicitanteCodigo;
$tpl->NUMEROATA         = $numeroAta;
$tpl->SEQUCONTRATO      = $sequContrato;
$tpl->NUMCONTRATO       = $numContrato;

//kim || $ValorHomologado == "0,00" || $ValorHomologado == "0.00"
if(empty($Solicitacao) ){
    $tpl->SEEXISTESCC = "style='display:none'";
}
if(empty($ARP)){
    $tpl->SEEXISTEARP = "style='display:none'";
}
if(empty($sequContrato)){
    $tpl->SEEXISTECONTRATO = "style='display:none'";
}

if (in_array($ultimaFase, $fasesComResultado)) {
    $tpl->COLSPAN_GERAL = 14;
    $tpl->COLSPAN_GERAL_SUP = 10;
    $tpl->COLSPAN_GERAL_HIST = 3;
    if($comissao != "46"){
            $tpl->block("BLOCO_VALOR_ESTIMADO");
            $tpl->TOTALGERALESTIMADO = $TotalGeralEstimado;
    }
}

if (in_array($ultimaFase, $fasesComResultado)) {
    $tpl->block("HOMOLOGACAO");
    $tpl->VALORHOMOLOGADO = $ValorHomologado;
    
}


$db = Conexao();
// Pega os Dados dos do Bloqueio de uma licitação sem SCC #
$sql = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ";
$sql .= "       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ";
$sql .= "       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ";
$sql .= "       CLICBLELE4, CLICBLFONT ";
$sql .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
$sql .= "   AND CCOMLICODI = $ComissaoCodigo ";
$sql .= "   AND CGREMPCODI = $GrupoCodigo";
$sql .= " ORDER BY ALICBLSEQU";

$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    $ExercicioBloq = array();
    $Orgao = array();
    $Unidade = array();
    $Bloqueios = array();
    $Funcao = array();
    $Subfuncao = array();
    $Programa = array();
    $TipoProjAtiv = array();
    $ProjAtividade = array();
    $Elemento1 = array();
    $Elemento2 = array();
    $Elemento3 = array();
    $Elemento4 = array();
    $Fonte = array();
    $Dotacao = array();
    for ($i = 0; $i < $Rows; ++ $i) {
        $Linha = $result->fetchRow();
        $ExercicioBloq [$i] = $Linha [0];
        $Orgao [$i] = $Linha [1];
        $Unidade [$i] = $Linha [2];
        $Bloqueios [$i] = $Linha [3];
        $Funcao [$i] = $Linha [4];
        $Subfuncao [$i] = $Linha [5];
        $Programa [$i] = $Linha [6];
        $TipoProjAtiv [$i] = $Linha [7];
        $ProjAtividade [$i] = $Linha [8];
        $Elemento1 [$i] = $Linha [9];
        $Elemento2 [$i] = $Linha [10];
        $Elemento3 [$i] = $Linha [11];
        $Elemento4 [$i] = $Linha [12];
        $Fonte [$i] = $Linha [13];
        $Dotacao [$i] = NumeroDotacao($Funcao [$i], $Subfuncao [$i], $Programa [$i], $Orgao [$i], $Unidade [$i], $TipoProjAtiv [$i], $ProjAtividade [$i], $Elemento1 [$i], $Elemento2 [$i], $Elemento3 [$i], $Elemento4 [$i], $Fonte [$i]);
    }
}

$dbOracle = ConexaoOracle();
// Pega os Dados dos do Bloqueio de uma licitação com SCC #
$sql = "
			select Distinct AITLBLNBLOQ, AITLBLANOB
            	from
            sfpc.tbitemlicitacaobloqueio
            	WHERE
            CLICPOPROC = $Processo
            AND ALICPOANOP = $ProcessoAno
            AND CCOMLICODI = $ComissaoCodigo
            AND CGREMPCODI = $GrupoCodigo
            ";

$result = executarSQL($db, $sql);
$i = 0;

while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
    $bloqueioAno = $bloqueioChave->aitlblanob; // AITLBLANOB;
    $bloqueioSequencial = $bloqueioChave->aitlblnbloq; // AITLBLNBLOQ;
    $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqueioAno, $bloqueioSequencial);
    
    $ExercicioBloq [$i] = $bloqueioArray ['ano'];
    $Orgao [$i] = $bloqueioArray ['orgao'];
    $Unidade [$i] = $bloqueioArray ['unidade'];
    $Bloqueios [$i] = $bloqueioArray ['sequencial'];
    $Funcao [$i] = $bloqueioArray ['funcao'];
    $Subfuncao [$i] = $bloqueioArray ['subfuncao'];
    $Programa [$i] = $bloqueioArray ['programa'];
    $TipoProjAtiv [$i] = $bloqueioArray ['tipoProjetoAtividade'];
    $ProjAtividade [$i] = $bloqueioArray ['projetoAtividade'];
    $Elemento1 [$i] = $bloqueioArray ['elemento1'];
    $Elemento2 [$i] = $bloqueioArray ['elemento2'];
    $Elemento3 [$i] = $bloqueioArray ['elemento3'];
    $Elemento4 [$i] = $bloqueioArray ['elemento4'];
    $Fonte [$i] = $bloqueioArray ['fonte'];
    $Dotacao [$i] = NumeroDotacao($Funcao [$i], $Subfuncao [$i], $Programa [$i], $Orgao [$i], $Unidade [$i], $TipoProjAtiv [$i], $ProjAtividade [$i], $Elemento1 [$i], $Elemento2 [$i], $Elemento3 [$i], $Elemento4 [$i], $Fonte [$i]);
    ++ $i;
}

// Pega os Dados de dotação de uma licitação com SCC #
$sql = "
			select distinct
            	aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt,
                citldoele1, citldoele2, citldoele3, citldoele4, citldofont
			from
            	sfpc.tbitemlicitacaodotacao
            WHERE
            	CLICPOPROC = $Processo
                AND ALICPOANOP = $ProcessoAno
                AND CCOMLICODI = $ComissaoCodigo
                AND CGREMPCODI = $GrupoCodigo
            ";

$result = executarSQL($db, $sql);
$i = 0;

while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
    $dotacaoAno = $bloqueioChave->aitldounidoexer;
    $dotacaoOrgao = $bloqueioChave->citldounidoorga;
    $dotacaoUnidade = $bloqueioChave->citldounidocodi;
    $dotacaoTipoProjeto = $bloqueioChave->citldotipa;
    $dotacaoProjeto = $bloqueioChave->aitldoordt;
    $dotacaoE1 = $bloqueioChave->citldoele1;
    $dotacaoE2 = $bloqueioChave->citldoele2;
    $dotacaoE3 = $bloqueioChave->citldoele3;
    $dotacaoE4 = $bloqueioChave->citldoele4;
    $dotacaoFonte = $bloqueioChave->citldofont;
    
    $bloqueioArray = getDadosDotacaoOrcamentariaFromChave($dbOracle, $dotacaoAno, $dotacaoOrgao, $dotacaoUnidade, $dotacaoTipoProjeto, $dotacaoProjeto, $dotacaoE1, $dotacaoE2, $dotacaoE3, $dotacaoE4, $dotacaoFonte);
    
    $ExercicioBloq [$i] = $dotacaoAno;
    $Orgao [$i] = $dotacaoOrgao;
    $Unidade [$i] = $dotacaoUnidade;
    $Bloqueios [$i] = null;
    $Funcao [$i] = null;
    $Subfuncao [$i] = null;
    $Programa [$i] = null;
    $TipoProjAtiv [$i] = $dotacaoTipoProjeto;
    $ProjAtividade [$i] = $dotacaoProjeto;
    $Elemento1 [$i] = $dotacaoE1;
    $Elemento2 [$i] = $dotacaoE2;
    $Elemento3 [$i] = $dotacaoE3;
    $Elemento4 [$i] = $dotacaoE4;
    $Fonte [$i] = $dotacaoFonte;
    $Dotacao [$i] = $bloqueioArray ['dotacao'];
    ++ $i;
}

if (count($Bloqueios) != 0) {
    for ($i = 0; $i < count($Bloqueios); ++ $i) {
        $isDotacao = false;
        
        if (is_null($Bloqueios [$i])) {
            $isDotacao = true;
        }
        
        $tpl->EXERCICIO = $ExercicioBloq [$i];
        
        if ($isDotacao) {
            $tpl->NUMERO_BLOQUEIO = " (dotação) ";
        } else {
            $tpl->NUMERO_BLOQUEIO = $Orgao [$i] . "." . sprintf("%02d", $Unidade [$i]) . ".1." . $Bloqueios [$i];
        }
        
        // Busca a descrição da Unidade Orçamentaria #
        if ($_SERVER ['SERVER_NAME'] != 'varzea.recife' and $_SERVER ['SERVER_NAME'] != 'www.recife.pe.gov.br') {
            if (empty($ExercicioBloq [$i])) {
                $ExercicioBloq [$i] = '9999/99/99';
            }
            if (empty($Orgao [$i])) {
                $Orgao [$i] = '9999';
            }
            if (empty($Unidade [$i])) {
                $Unidade [$i] = '9999';
            }
        }
        
        $sql = 'SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ';
        $sql .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
        $sql .= "   AND CUNIDOCODI = $Unidade[$i]";
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $UnidadeOrcament = null;
            $Linha = $result->fetchRow();
            $UnidadeOrcament [$i] = $Linha [0];
        }
        
        $tpl->UNIDADE_ORCAMENTARIA = $UnidadeOrcament [$i];
        $tpl->DOTACAO = $Dotacao [$i];
        $tpl->block('LINHA_BLOCO_BLOQUEIOS');
    }
    
    $tpl->block('BLOCO_BLOQUEIOS');
} else {
    $tpl->block('BLOCO_SEM_BLOQUEIOS');
}

// --------------------------------------------
// Verificar se Licitação tem resultado
// ---------------------------------------------
$sql = ' select flicporesu as resultado ';
$sql .= ' from sfpc.tblicitacaoportal ';
$sql .= ' where ';
$sql .= " clicpoproc = $Processo";
$sql .= ' and alicpoanop = ' . $ProcessoAno;
$sql .= ' and cgrempcodi = ' . $GrupoCodigo;
$sql .= ' and ccomlicodi = ' . $ComissaoCodigo;
$sql .= ' and corglicodi = ' . $OrgaoLicitanteCodigo;

$result = executarTransacao($db, $sql);
$row = $result->fetchRow(DB_FETCHMODE_OBJECT);

$licitacaoComResultado = false;
if ($row->resultado == 'S') {
    $licitacaoComResultado = true;
}

// --------------------------------------------
// Verificar ultim afase da licitação
// ---------------------------------------------
$ultimaFase = ultimaFase($Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
// $arraySituacoesConcluidas = array(
// '11',
// '12',
// '13',
// '15',
// '17',
// '18'
// ); // Array com os ids das situações concluídas
$arraySituacoesConcluidas = getIdFasesConcluidas();

$exibeTdValorEstimado = false;

if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
    $exibeTdValorEstimado = true;
}

// --------------------------------------------------------
// Inserido por Heraldo
// para exibir itens de materiais e de serviços
// ---------------------------------------------------------

// --------------------------------------------------------
// SQL para capturar os itens de material da licitação
// ---------------------------------------------------------
$sql = " SELECT a.aitelporde, b.ematepdesc, a.cmatepsequ, c.eunidmdesc, a.aitelpqtso, a.citelpnuml, ";
$sql .= " d.aforcrsequ, d.nforcrrazs, d.nforcrfant, d.aforcrccgc, a.eitelpdescmat, a.eitelpmarc, a.eitelpmode ";
$sql .= " , a.vitelpunit, a.vitelpvlog, mnl.emotnlnome ";
$sql .= " FROM ";
$sql .= " sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado d ";
$sql .= " ON a.aforcrsequ = d.aforcrsequ LEFT JOIN sfpc.tbmotivoitemnaologrado mnl ON mnl.cmotnlsequ = a.cmotnlsequ,";
$sql .= " sfpc.tbmaterialportal b, sfpc.tbunidadedemedida c ";
$sql .= " WHERE ";
$sql .= " a.cmatepsequ = b.cmatepsequ  ";
$sql .= " AND b.cunidmcodi = c.cunidmcodi  ";
$sql .= " AND  a.clicpoproc=" . $Processo;
$sql .= " AND  a.alicpoanop=" . $ProcessoAno;
$sql .= " AND a.cgrempcodi=" . $GrupoCodigo;
$sql .= " AND a.ccomlicodi=" . $ComissaoCodigo;
$sql .= " AND a.corglicodi=" . $OrgaoLicitanteCodigo;
$sql .= " ORDER BY 6,1 ";

$resILTmp = $db->query($sql);
$result = $db->query($sql);
$Rows = $result->numRows();
$flagLote = false;
$totalItemMaterial = 0;
$temDescriaoDetalhada = true;


// ------------------------------------------------------------
// - Se encontrar pelo menos uma linha exibir grade com Itens
// ------------------------------------------------------------

if ($Rows > 0) {
    $todosMaterial = array();
    $posicao = 0;
    while ($Linha = $result->fetchRow()) {
        $todosMaterial [$posicao] ['ordeMaterial'] = $Linha [0];
        $todosMaterial [$posicao] ['descMaterial'] = $Linha [1];
        $todosMaterial [$posicao] ['seqMaterial'] = $Linha [2];
        $todosMaterial [$posicao] ['UnidadeMaterial'] = $Linha [3];
        $todosMaterial [$posicao] ['quantidadeMaterial'] = $Linha [4];
        $todosMaterial [$posicao] ['numLoteMaterial'] = $Linha [5];
        $todosMaterial [$posicao] ['codForcredMate'] = $Linha [6];
        $todosMaterial [$posicao] ['razaoSocForMat'] = $Linha [7];
        $todosMaterial [$posicao] ['nomeFantForMat'] = $Linha [8];
        $todosMaterial [$posicao] ['cgcForCredMat'] = $Linha [9];
        $todosMaterial [$posicao] ['desDetalhadaMaterial'] = $Linha [10];
        $todosMaterial [$posicao] ['marcaMaterial'] = $Linha [11];
        $todosMaterial [$posicao] ['modeloMaterial'] = $Linha [12];
        $todosMaterial [$posicao] ['valoresEstimadoMaterial'] = $Linha [13];
        $todosMaterial [$posicao] ['valoresHomologadoMaterial'] = $Linha [14];
        $todosMaterial [$posicao] ['motivoNaoLogrado'] = $Linha [15];
        ++ $posicao;
    }
    -- $posicao;
    $numLoteMatAntes = '';
    for ($i = 0; $i <= $posicao; ++ $i) {
        if ($todosMaterial [$i] ['numLoteMaterial'] != $numLoteMatAntes) {
            /*
             * pega o lote atual e verifica em todas as suas ocorrências se há descrição detalhada do material
             *
             */
            $incremento = $i + 1;
            $loteAtual = $todosMaterial [$i] ['numLoteMaterial'];
            
            $temDescriaoDetalhada = $todosMaterial [$i] ['desDetalhadaMaterial'] != '';
            while (! $temDescriaoDetalhada && $posicao >= $incremento) {
                if ($loteAtual != $todosMaterial [$incremento] ['numLoteMaterial']) {
                    break;
                }
                
                if ($incremento <= $posicao) {
                    $temDescriaoDetalhada = $todosMaterial [$incremento] ['desDetalhadaMaterial'] != '';
                }
                
                if ($temDescriaoDetalhada) {
                    break;
                }
                ++ $incremento;
            }
            if ($temDescriaoDetalhada) {
                $tpl->block("BLOCO_DESCRICAO_DETALHADA_TITULO");
            }

            if ($exibeTdValorEstimado) {
                //$tpl->COLSPAN_GERAL = 14;
                //$tpl->COLSPAN_GERAL_SUP = 10;
                //$tpl->COLSPAN_GERAL_HIST = 3;
                $tpl->block('TDVALORESTIMADO');
                $tpl->block('BLOCO_VALOR_HOMOLOGADO');
            }
            

            if ($flagLote) {
                $tpl->block("BLOCO_LOTE");
            }
            


            if ($temDescriaoDetalhada) {
                $tpl->COLSPAN = 3;
            } else {
                $tpl->COLSPAN = 6;
            }
            
            $numLoteMatAntes = $todosMaterial [$i] ['numLoteMaterial'];
            
            if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($todosMaterial [$i] ['razaoSocForMat'])) {
                $soma = getTotalValorLogrado($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $todosMaterial [$i] ['numLoteMaterial']);
                $tpl->LOTE = "LOTE " . ($todosMaterial [$i] ['numLoteMaterial']) . " FORNECEDOR VENCEDOR : " . FormataCpfCnpj($todosMaterial [$i] ['cgcForCredMat']) . " - " . ($todosMaterial [$i] ['razaoSocForMat']) . " - " . "R$ " . (number_format(( float ) $soma, 2, ",", "."));
            } else {
                $tpl->LOTE = "LOTE " . ($todosMaterial [$i] ['numLoteMaterial']);
            }
            
            $tpl->block("DESCRICAO_LOTE");
            $flagLote = true;
        }
        $tpl->ORDEM = $todosMaterial [$i] ['ordeMaterial'];
        $tpl->DESCRICAO = $todosMaterial [$i] ['descMaterial'];
        $tpl->CODIGO = $todosMaterial [$i] ['seqMaterial'];
        $tpl->UNIDADE = $todosMaterial [$i] ['UnidadeMaterial'];
        
        if ($temDescriaoDetalhada) {
            $tpl->DESCRICAO_DETALHADA = ($todosMaterial [$i] ['desDetalhadaMaterial'] != "") ? $todosMaterial [$i] ['desDetalhadaMaterial'] : "---";
            $tpl->block("BLOCO_DESCRICAO_DETALHADA_DADO");
        }


        if ($temDescriaoDetalhada && $exibeTdValorEstimado) {
            $tpl->COLSPAN_VALORES = 1;
            $tpl->COLSPAN_DESCRICAO_DETALHADA = 3;
            //$tpl->COLSPAN_DESCRICAO_MAT = 1;
        } elseif ($exibeTdValorEstimado) {
            //$tpl->COLSPAN_DESCRICAO_MAT = 4;
            $tpl->COLSPAN_VALORES = 1;
        } else {
            //$tpl->COLSPAN_DESCRICAO_MAT = 3;
        }

        $tpl->QUANTIDADE = number_format($todosMaterial [$i] ['quantidadeMaterial'], '4', ',', '.');
        $tpl->MARCA = ($todosMaterial [$i] ['marcaMaterial'] != '') ? $todosMaterial [$i] ['marcaMaterial'] : '---';
        $tpl->MODELO = ($todosMaterial [$i] ['modeloMaterial'] != '') ? $todosMaterial [$i] ['modeloMaterial'] : '---';

        if($exibeTdValorEstimado) {
            $tpl->VALOR_ESTIMADO = number_format($todosMaterial [$i] ['valoresEstimadoMaterial'], 2, ',', '.');
            if($todosMaterial [$i] ['motivoNaoLogrado']){
                $tpl->VALOR_HOMOLOGADO = $todosMaterial [$i] ['motivoNaoLogrado'];
            }else{
                $tpl->VALOR_HOMOLOGADO = number_format($todosMaterial [$i] ['valoresHomologadoMaterial'], 2, ',', '.');
            }
            
            


            $tpl->block('VALORES');
        }

        $tpl->block('BLOCO_LOTE_ITEM_MATERIAL');
        
        ++ $totalItemMaterial;
    }
    
    if ($totalItemMaterial >= $Rows) {
        if ($temDescriaoDetalhada) {
            $tpl->COLSPAN = 3;
        } else {
            $tpl->COLSPAN = 6;
        }
        $tpl->block("BLOCO_LOTE");
    }
    
    $tpl->block("BLOCO_MATERIAIS");
}

// --------------------------------------------------------
// SQL para capturar os itens de serviço da licitação
// ---------------------------------------------------------
$sql = " SELECT a.aitelporde, b.eservpdesc, a.cservpsequ, a.citelpnuml, c.aforcrsequ, ";
$sql .= " c.nforcrrazs, c.nforcrfant, c.aforcrccgc, a.eitelpdescse ";
$sql .= " , a.vitelpunit, a.vitelpvlog ";
$sql .= " FROM sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado c ";
$sql .= " ON a.aforcrsequ = c.aforcrsequ, ";
$sql .= " sfpc.tbservicoportal b ";
$sql .= " WHERE ";
$sql .= " a.cservpsequ = b.cservpsequ   ";
$sql .= " AND  a.clicpoproc=" . $Processo;
$sql .= " AND  a.alicpoanop=" . $ProcessoAno;
$sql .= " AND a.cgrempcodi=" . $GrupoCodigo;
$sql .= " AND a.ccomlicodi=" . $ComissaoCodigo;
$sql .= " AND a.corglicodi=" . $OrgaoLicitanteCodigo;
$sql .= " ORDER BY 4,1 ";

$resultTemp = $db->query($sql);
$result = $db->query($sql);
$Rows = $result->numRows();
$totalItensServico = 0;
$flagLoteServico = false;

// ------------------------------------------------------------
// - Se encontrar pelo menos uma linha exibir grade com Itens
// ------------------------------------------------------------
if ($Rows > 0) {
    $numLoteServAntes = 0;
    while ($Linha = $result->fetchRow()) {
        $ordServico = $Linha [0];
        $descServico = $Linha [1];
        $seqServico = $Linha [2];
        $numLoteServico = $Linha [3];
        $codForCredServ = $Linha [4];
        $razaoSocForServ = $Linha [5];
        $nomeFantFornServ = $Linha [6];
        $cgcForCredServ = $Linha [7];
        $descDetalhadaServico = $Linha [8];
        $valorEstimadoItem = $Linha [9];
        $valorHomologadoItem = $Linha [10];
        
        if ($numLoteServico != $numLoteServAntes) {
            if ($flagLoteServico) {
                $tpl->block("BLOCO_LOTE_SERVICOS");
            }
            
            $numLoteServAntes = $numLoteServico;
            
            if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($razaoSocForServ)) {
                $soma = getTotalValorServico($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);
                $tpl->LOTE = "LOTE " . ($numLoteServico) . " FORNECEDOR VENCEDOR: " . FormataCpfCnpj($cgcForCredServ) . " - " . ($razaoSocForServ) . " - " . "R$ " . (number_format(( float ) $soma, 2, ",", "."));
            } else {
                $tpl->LOTE = "LOTE " . ($numLoteServico);
            }
            
            $tpl->block("DESCRICAO_LOTE_SERVICOS");
            $flagLoteServico = true;
        }
        
        $tpl->ORDEM = $ordServico;
        $tpl->DESCRICAO = $descServico;
        $tpl->CODIGO = $seqServico;
        $tpl->DESCRICAO_DETALHADA = ($descDetalhadaServico != '') ? $descDetalhadaServico : '---';
        $tpl->block('BLOCO_LOTE_ITEM_SERVICOS');
        
        ++ $totalItensServico;
    }
    
    if ($totalItensServico >= $Rows) {
        $tpl->block("BLOCO_LOTE_SERVICOS");
    }
    
    $tpl->block("BLOCO_SERVICOS");
}

$paramentrosConsultaDocumentos = 'ConsAcompDetalhesDocumentosRelacionados.php?processo=' . $Processo . '&ano=' . $ProcessoAno . '&comissao=' . $ComissaoCodigo . '&grupo=' . $GrupoCodigo;
$tpl->DOCUMENTOS_RELACIONADOS = $paramentrosConsultaDocumentos;
$tpl->block("BLOCO_DOCUMENTOS_RELACIONADOS");

// Pega as Fases da Licitação #
$sql = 'SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ';
$sql .= '       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ';
$sql .= '       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT';
$sql .= '  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ';
$sql .= '    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ';
$sql .= '   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ';
$sql .= '   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ';
$sql .= '       LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI';
$sql .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
$sql .= '   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI <> 1 '; // Menos a fase Interna
$sql .= ' ORDER BY B.TFASELDATA ASC, A.AFASESORDE ASC';
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}

$resultadoFases = $db->query($sql);
$totalLinhas = $resultadoFases->numRows();
$totalAtasNaHomologacao = 0; // Acumulador de total de atas na fase de homologação
$codigoAta = "";
$faseCod = "";

if ($totalLinhas > 0) {
    while ($linhaFase = $resultadoFases->fetchRow()) {
        $descricaoFase = $linhaFase[0];
        $nomeAta = $linhaFase[8];
        $faseCod = $linhaFase[4];
        $descricaoDocumento = $linhaFase[9];

        if (strpos($descricaoFase, 'HOMOLOGAÇÃO') !== false) {
            $codigoAta = $linhaFase[7];
            ++$totalAtasNaHomologacao;
            $faseCod = $linhaFase [4];
        }
    }
    
    // Exibe link direto para o único arquivo
    if ($totalAtasNaHomologacao == 1 && !empty($codigoAta)) {
        $faseCod = is_null($faseCod) ? 13 : $faseCod;
        $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $faseCod . "_" . $codigoAta;
        $Arquivo = $GLOBALS ["CAMINHO_UPLOADS"] . $ArqUpload;

        addArquivoAcesso($ArqUpload);

        $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$faseCod&AtaCodigo=$codigoAta";

        Pitang_GetUrl::run($Url);
        $tpl->URL_DIRETA_ATA = $Url;
        $tpl->block("PROCESSO_LICITATORIO");
    }
    
    // Caso exista mais de uma ata na fase de homologação será exibido um link para um popup
    if ($totalAtasNaHomologacao > 1) {
        $paramentrosConsultaDocumentos = "processo=$Processo&ano=$ProcessoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo&orgaoLicitante=$OrgaoLicitanteCodigo";

        $tpl->EVENTOMUITOSPROCESSOS = $paramentrosConsultaDocumentos;
        $tpl->block("PROCESSO_LICITATORIO_MUITOS_PROCESSOS");
    }
}

$Rows = $result->numRows();

if ($Rows > 0) {
    $primeiraIteracaoDoLoop = true;
    $CodFaseAnterior = null;
    while ($Linha = $result->fetchRow()) {
        $FaseCodigo = $Linha [4];
        $DataFase = substr($Linha [6], 8, 2) . "/" . substr($Linha [6], 5, 2) . "/" . substr($Linha [6], 0, 4);
        $FaseDetalhamento = $Linha [5];
        $nomeAta = $Linha [8];
        $itemObservacao = " - <b>Observação/ Justificativa:</b> \"" . $Linha [9] . "\"";
        $itemExcluido = $Linha [10];
        $itemAutor = " - <b>Responsável:</b> \"" . $Linha [11] . "\"";
        $itemDataAlteracao = $Linha [12];
        
        if ($itemDataAlteracao < "2011-03-23") {
            $itemObservacao = "";
            $itemAutor = "";
        }
        
        if (! $primeiraIteracaoDoLoop && $CodFaseAnterior != $Linha [4]) {
            $tpl->block("BLOCO_TABELA_HISTORICO");
        }
        
        if ($Linha [4] == $CodFaseAnterior) {
            $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha [7];
            $Arquivo = $GLOBALS ["CAMINHO_UPLOADS"] . $ArqUpload;
            addArquivoAcesso($ArqUpload);
            
            if ($itemExcluido == "S") {
                $tpl->ATADAFASE = "<s><br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
            } elseif (file_exists($Arquivo)) {
                $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                Pitang_GetUrl::run($Url);
                $tpl->ATADAFASE = "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
            } else {
                $tpl->ATADAFASE = "<br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
            }
        } else {
            $DataFase = substr($Linha [6], 8, 2) . "/" . substr($Linha [6], 5, 2) . "/" . substr($Linha [6], 0, 4);
            $tpl->FASE = $Linha [0];
            $tpl->DATA = $DataFase;
            $tpl->DETALHE = $Linha [5];
            
            if ($Linha [7] != 0) {
                $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha [7];
                $Arquivo = $GLOBALS ["CAMINHO_UPLOADS"] . $ArqUpload;
                addArquivoAcesso($ArqUpload);
                
                if ($itemExcluido == "S") {
                    $tpl->ATADAFASE = "<img src='../midia/disqueteInexistente.gif' border='0'><s><font color=\"#000000\"> $nomeAta</font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                } elseif (file_exists($Arquivo)) {
                    $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                    Pitang_GetUrl::run($Url);
                    $tpl->ATADAFASE = "<a href=$Url><img src='../midia/disquete.gif' border=0> <font color=\"#000000\"> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                } else {
                    $tpl->ATADAFASE = "<img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta</font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                }
            }
        }
        
        $tpl->block("BLOCO_ATAS_DA_FASE");
        $tpl->ATADAFASE = "";
        $CodFaseAnterior = $Linha [4];
        $primeiraIteracaoDoLoop = false;
    }
    
    $tpl->block("BLOCO_TABELA_HISTORICO");
    $tpl->block("BLOCO_HISTORICO");
}

// Busca o(s) resultado(s) da Licitação #
$sql = "SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
$sql .= "  FROM SFPC.TBRESULTADOLICITACAO ";
$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo";
$sql .= "   AND CGREMPCODI = $GrupoCodigo";
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
}

$Rows = $result->numRows();
$Resultados = 0;
if ($Rows >= 1) {
    while ($Linha = $result->fetchRow()) {
        $Resultados = 1;
        $ResultadoHabi = $Linha [0];
        $ResultadoInab = $Linha [1];
        $ResultadoJulg = $Linha [2];
        $ResultadoRevo = $Linha [3];
        $ResultadoAnul = $Linha [4];
    }
}

$db->disconnect();

if ($ResultadoHabi != "") {
    $tpl->TIPODESCRICAO = "EMPRESAS HABILITADAS";
    $tpl->TIPORESULTADO = $ResultadoHabi;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if ($ResultadoInab != "") {
    $tpl->TIPODESCRICAO = "EMPRESAS INABILITADAS";
    $tpl->TIPORESULTADO = $ResultadoInab;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if ($ResultadoJulg != "") {
    $tpl->TIPODESCRICAO = "JULGAMENTO";
    $tpl->TIPORESULTADO = $ResultadoJulg;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if ($ResultadoRevo != "") {
    $tpl->TIPODESCRICAO = "REVOGAÇÃO";
    $tpl->TIPORESULTADO = $ResultadoRevo;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if ($ResultadoAnul != "") {
    $tpl->TIPODESCRICAO = "REVOGAÇÃO";
    $tpl->TIPORESULTADO = $ResultadoAnul;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if (($ResultadoHabi != "") or ($ResultadoInab != "") or ($ResultadoJulg != "") or ($ResultadoRevo != "") or ($ResultadoAnul != "")) {
    $tpl->block("BLOCO_RESULTADOS");
}

$tpl->OBJETO_PESQUISA = $_SESSION ['Objeto'];
$tpl->ORGAO_LICITANTE_CODIGO = $_SESSION ['OrgaoLicitanteCodigo'];
$tpl->COMISSAO_CODIGO = $_SESSION ['ComissaoCodigo'];
$tpl->MODALIDADE_CODIGO = $_SESSION ['ModalidadeCodigo'];
$tpl->TIPO_ITEM_PESQUISA = $_SESSION ['TipoItemLicitacao'];
$tpl->DESCRICAO_ITEM_PESQUISA = $_SESSION ['Item'];
$tpl->ANO_PESQUISA = $_SESSION ['LicitacaoAno'];
$tpl->BOTAO_PESQUISA = $_SESSION ['botao'];

$tpl->show();
