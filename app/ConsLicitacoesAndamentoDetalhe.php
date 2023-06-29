<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.38.0
 *
 * ----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÔES
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 29/04/2016
 * Objetivo: Requisito 129783 - Correção de exibição das fases de licitação
 * ----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/12/2018
 * Objetivo: Tarefa Redmine 207615
 * ----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     02/04/2019
 * Objetivo: Tarefa Redmine 214050
 * ----------------------------------------------------------------------------
 * Alterado: Eliakim Ramos
 * Data:     28/01/2020
 * Objetivo: Tarefa Redmine 229264
 * ----------------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     15/06/2020
 * Objetivo: Tarefa Redmine 234612 - Correção exibição das fases de licitação
 * ----------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     18/06/2021
 * Objetivo: Tarefa Redmine  #248576
 * ----------------------------------------------------------------------------
 
  
 
 * ----------------------------------------------------------------------------
 */
if (! @require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}
require_once("../licitacoes/funcoesLicitacoes.php");
require_once("../compras/funcoesCompras.php");

$ErroPrograma = __FILE__;

$tpl = new TemplateAppPadrao("templates/ConsLicitacoesAndamentoDetalhe.html", "");

$Selecao              = $_SESSION['Selecao'];
$GrupoCodigo          = $_SESSION['GrupoCodigoDet'];
$Processo             = $_SESSION['ProcessoDet'];
$ProcessoAno          = $_SESSION['ProcessoAnoDet'];
$ComissaoCodigo       = $_SESSION['ComissaoCodigoDet'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigoDet'];
$Lote                 = $_SESSION['Lote'];
$Ordem                = $_SESSION['Ordem'];

if (empty($GrupoCodigo) || empty($ComissaoCodigo) || empty($ProcessoAno) || empty($Processo) || empty($OrgaoLicitanteCodigo)) {
    header("location: ConsLicitacoesAndamento.php");
    exit();
}

$_SESSION['PermitirAuditoria'] = 'N'; // Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
resetArquivoAcesso();
$fasesComResultado = array(13, 15); // Fases que podem ter resultado

// Resgata as informações da licitação #
$db = Conexao();

$sql  = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
$sql .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
$sql .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
$sql .= "       D.VLICPOTGES, D.FLICPOVFOR, C.NCOMLIPRES, C.ECOMLILOCA, ";
$sql .= "       C.ACOMLIFONE, C.ACOMLINFAX, E.FORGLIEXVE,  C.CCOMLICODI ";
$sql .= " FROM   SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
$sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
$sql .= "WHERE  A.CGREMPCODI = D.CGREMPCODI ";
$sql .= "       AND D.CGREMPCODI = %d ";
$sql .= "       AND D.CMODLICODI = B.CMODLICODI ";
$sql .= "       AND C.CCOMLICODI = D.CCOMLICODI ";
$sql .= "       AND D.CCOMLICODI = %d ";
$sql .= "       AND D.ALICPOANOP = %d ";
$sql .= "       AND D.CLICPOPROC = $Processo ";
$sql .= "       AND E.CORGLICODI = D.CORGLICODI ";
$sql .= "       AND D.CORGLICODI = %d ";

$sql = sprintf($sql, $GrupoCodigo, $ComissaoCodigo, $ProcessoAno, $OrgaoLicitanteCodigo);

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    
    while ($Linha = $result->fetchRow()) {
        $GrupoDesc             = $Linha[0];
        $ModalidadeDesc        = $Linha[1];
        $ComissaoDesc          = $Linha[2];
        $OrgaoLicitacao        = $Linha[4];
        $ObjetoLicitacao       = $Linha[3];
        $Licitacao             = substr($Linha[6] + 10000, 1);
        $AnoLicitacao          = $Linha[7];
        $LicitacaoDtAbertura   = substr($Linha[5], 8, 2) . "/" . substr($Linha[5], 5, 2) . "/" . substr($Linha[5], 0, 4);
        $LicitacaoHoraAbertura = substr($Linha[5], 11, 5);
        $endereco              = $Linha[15];
        $telefone              = $Linha[16];
        $fax                   = $Linha[17];
        $exibicaoValor         = $Linha[18];
        $comissao         = $Linha[19];
        
        if ($Linha[8] == "S") {
            $RegistroPreco = "SIM";
        } else {
            $RegistroPreco = "NÃO";
        }
        
        $ModalidadeCodigo = $Linha[9];
        $ValorEstimado = totalValorEstimado($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
        
        if (empty($ValorEstimado)) {
            $ValorEstimado = "0,00";
        } else {
            $ValorEstimado = converte_valor($ValorEstimado);
        }
        
        $ValorHomologado = converte_valor($Linha[11]);
        $TotalGeralEstimado = converte_valor($Linha[12]);
        
        if ($Linha[13] == "S") {
            $validacaoFornecedor = "SIM";
        } else {
            $validacaoFornecedor = "NÃO";
        }
    }
}

//================= Kim inicio CR#229264 ====================================
$sqlSolicitacoesC  = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi ";
$sqlSolicitacoesC .= " FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL WHERE SOL.CLICPOPROC = $Processo AND SOL.ALICPOANOP =" . $AnoLicitacao ;
$sqlSolicitacoesC .= " AND SOL.CCOMLICODI = $ComissaoCodigo AND SOL.cgrempcodi =" . $GrupoCodigo;

$resultSolic = $db->query($sqlSolicitacoesC);
$ultimaFase = ultimaFase($Processo, $AnoLicitacao, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
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
$dadosIRP = NULL;
if(!empty($SeqSolicitacao)){
    // --- Busca valores para montar valor da IRP ---
    $sqlIrp = "select cintrpsequ, cintrpsano from sfpc.tbsolicitacaocompra where csolcosequ = $SeqSolicitacao";

    $resultIRP = $db->query($sqlIrp);

    if (PEAR::isError($resultIRP)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlIrp");
    }
    $dadosIRP = $resultIRP->fetchRow();
}


//=================================  Kim fim ====================

$existeDadosExtras = false;

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

$tpl->GRUPO_DESCRICAO      = $GrupoDesc;
$tpl->MODALIDADE_DESCRICAO = $ModalidadeDesc;
$tpl->COMISSAO_DESCRICAO   = $ComissaoDesc;

$Processo = substr($Processo + 10000, 1);

$tpl->PROCESSO  = "$Processo/$ProcessoAno";
$tpl->LICITACAO = "$Licitacao/$AnoLicitacao";

if ($ModalidadeCodigo == 3 or $ModalidadeCodigo == 2) {
    $tpl->block("BLOCO_PERMISSAO_REMUNERADA");
}

$tpl->REGISTRO_PRECO            = $RegistroPreco;
$tpl->OBJETO                    = $ObjetoLicitacao;
$tpl->DATA_ABERTURA             = "$LicitacaoDtAbertura $LicitacaoHoraAbertura";
$tpl->ORGAO_LICITANTE           = $OrgaoLicitacao;
$tpl->NECESSIDADE_DEMONSTRACOES = $validacaoFornecedor;
$tpl->SCC                       = $Solicitacao;
$tpl->LINKSCC                   = 'ConsAcompSolicitacaoCompraVersaoAPP.php?SeqSolicitacao='.$SeqSolicitacao.'&programa=window&irp='.$dadosIRP[0].'&ano='.$dadosIRP[1];
//kim CR#229264 || $ValorHomologado == "0,00" || $ValorHomologado == "0.00"
if(empty($Solicitacao) ){
    $tpl->SEEXISTESCC = "style='display:none'";
}

if ($exibicaoValor != "N" &&  $comissao != "46" ) {
    $tpl->NOME_CAMPO = 'VALOR ESTIMADO TOTAL';
    $tpl->VALOR_ESTIMADO_TOTAL = $ValorEstimado;
    $tpl->block('BLOCO_VALOR_ESTIMADO');
}

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
    for ($i = 0; $i < $Rows; $i ++) {
        $Linha = $result->fetchRow();
        $ExercicioBloq[$i] = $Linha[0];
        $Orgao[$i] = $Linha[1];
        $Unidade[$i] = $Linha[2];
        $Bloqueios[$i] = $Linha[3];
        $Funcao[$i] = $Linha[4];
        $Subfuncao[$i] = $Linha[5];
        $Programa[$i] = $Linha[6];
        $TipoProjAtiv[$i] = $Linha[7];
        $ProjAtividade[$i] = $Linha[8];
        $Elemento1[$i] = $Linha[9];
        $Elemento2[$i] = $Linha[10];
        $Elemento3[$i] = $Linha[11];
        $Elemento4[$i] = $Linha[12];
        $Fonte[$i] = $Linha[13];
        $Dotacao[$i] = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
    }
}
$dbOracle = ConexaoOracle();
// Pega os Dados dos do Bloqueio de uma licitação com SCC #
$sql = "
    SELECT DISTINCT AITLBLNBLOQ, AITLBLANOB
    FROM
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
    
    $ExercicioBloq[$i] = $bloqueioArray['ano'];
    $Orgao[$i] = $bloqueioArray['orgao'];
    $Unidade[$i] = $bloqueioArray['unidade'];
    $Bloqueios[$i] = $bloqueioArray['sequencial'];
    $Funcao[$i] = $bloqueioArray['funcao'];
    $Subfuncao[$i] = $bloqueioArray['subfuncao'];
    $Programa[$i] = $bloqueioArray['programa'];
    $TipoProjAtiv[$i] = $bloqueioArray['tipoProjetoAtividade'];
    $ProjAtividade[$i] = $bloqueioArray['projetoAtividade'];
    $Elemento1[$i] = $bloqueioArray['elemento1'];
    $Elemento2[$i] = $bloqueioArray['elemento2'];
    $Elemento3[$i] = $bloqueioArray['elemento3'];
    $Elemento4[$i] = $bloqueioArray['elemento4'];
    $Fonte[$i] = $bloqueioArray['fonte'];
    $Dotacao[$i] = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
    $i ++;
}

// Pega os Dados de dotação de uma licitação com SCC #
$sql = "
    SELECT DISTINCT
        aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt,
        citldoele1, citldoele2, citldoele3, citldoele4, citldofont
    FROM
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
    
    $ExercicioBloq[$i] = $dotacaoAno;
    $Orgao[$i] = $dotacaoOrgao;
    $Unidade[$i] = $dotacaoUnidade;
    $Bloqueios[$i] = null;
    $Funcao[$i] = null;
    $Subfuncao[$i] = null;
    $Programa[$i] = null;
    $TipoProjAtiv[$i] = $dotacaoTipoProjeto;
    $ProjAtividade[$i] = $dotacaoProjeto;
    $Elemento1[$i] = $dotacaoE1;
    $Elemento2[$i] = $dotacaoE2;
    $Elemento3[$i] = $dotacaoE3;
    $Elemento4[$i] = $dotacaoE4;
    $Fonte[$i] = $dotacaoFonte;
    $Dotacao[$i] = $bloqueioArray["dotacao"];
    $i ++;
}

if (count($Bloqueios) != 0) {
    for ($i = 0; $i < count($Bloqueios); $i ++) {
        $isDotacao = false;
        
        if (is_null($Bloqueios[$i])) {
            $isDotacao = true;
        }
        
        $tpl->EXERCICIO = $ExercicioBloq[$i];
        
        if ($isDotacao) {
            $tpl->NUMERO_BLOQUEIO = " (dotação) ";
        } else {
            $tpl->NUMERO_BLOQUEIO = $Orgao[$i] . "." . sprintf("%02d", $Unidade[$i]) . ".1." . $Bloqueios[$i];
        }
        
        // Busca a descrição da Unidade Orçamentaria #
        if ($_SERVER['SERVER_NAME'] != 'varzea.recife' and $_SERVER['SERVER_NAME'] != 'www.recife.pe.gov.br') {
            if (empty($ExercicioBloq[$i])) {
                $ExercicioBloq[$i] = '9999/99/99';
            }
            if (empty($Orgao[$i])) {
                $Orgao[$i] = '9999';
            }
            if (empty($Unidade[$i])) {
                $Unidade[$i] = '9999';
            }
        }
        
        $sql = "SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
        $sql .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
        $sql .= "   AND CUNIDOCODI = $Unidade[$i]";
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $UnidadeOrcament[$i] = $Linha[0];
        }
        
        $tpl->UNIDADE_ORCAMENTARIA = $UnidadeOrcament[$i];
        $tpl->DOTACAO = $Dotacao[$i];
        $tpl->block("LINHA_BLOCO_BLOQUEIOS");
    }
    
    $tpl->block("BLOCO_BLOQUEIOS");
} else {
    $tpl->block("BLOCO_SEM_BLOQUEIOS");
}

// --------------------------------------------
// Verificar se Licitação tem resultado
// ---------------------------------------------
$sql = " SELECT flicporesu as resultado ";
$sql .= " FROM sfpc.tblicitacaoportal ";
$sql .= " WHERE ";
$sql .= " clicpoproc = $Processo";
$sql .= " AND alicpoanop = " . $ProcessoAno;
$sql .= " AND cgrempcodi = " . $GrupoCodigo;
$sql .= " AND ccomlicodi = " . $ComissaoCodigo;
$sql .= " AND corglicodi = " . $OrgaoLicitanteCodigo;

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
$arraySituacoesConcluidas = getIdFasesConcluidas(); // Array com os ids das situações concluídas

// --------------------------------------------------------
                                                    // Inserido por Heraldo
                                                    // para exibir itens de materiais e de serviços
                                                    // ---------------------------------------------------------

// --------------------------------------------------------
                                                    // SQL para capturar os itens de material da licitação
                                                    // ---------------------------------------------------------
$sql = " SELECT a.aitelporde, b.ematepdesc, a.cmatepsequ, c.eunidmdesc, a.aitelpqtso, a.citelpnuml, ";
$sql .= " d.aforcrsequ, d.nforcrrazs, d.nforcrfant, d.aforcrccgc, a.eitelpdescmat, a.eitelpmarc, a.eitelpmode ";
$sql .= " , a.vitelpunit, a.vitelpvlog ";
$sql .= " FROM ";
$sql .= " sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado d ";
$sql .= " ON a.aforcrsequ = d.aforcrsequ, ";
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
if( $OrgaoLicitanteCodigo == 19){
    $tpl->EMPRELCASE = 'style = display:none;';
}

// ------------------------------------------------------------
// - Se encontrar pelo menos uma linha exibir grade com Itens
// ------------------------------------------------------------
if ($Rows > 0) {
    $todosMaterial = array();
    $posicao = 0;
    while ($Linha = $result->fetchRow()) {
        $todosMaterial[$posicao]['ordeMaterial'] = $Linha[0];
        $todosMaterial[$posicao]['descMaterial'] = $Linha[1];
        $todosMaterial[$posicao]['seqMaterial'] = $Linha[2];
        $todosMaterial[$posicao]['UnidadeMaterial'] = $Linha[3];
        $todosMaterial[$posicao]['quantidadeMaterial'] = $Linha[4];
        $todosMaterial[$posicao]['numLoteMaterial'] = $Linha[5];
        $todosMaterial[$posicao]['codForcredMate'] = $Linha[6];
        $todosMaterial[$posicao]['razaoSocForMat'] = $Linha[7];
        $todosMaterial[$posicao]['nomeFantForMat'] = $Linha[8];
        $todosMaterial[$posicao]['cgcForCredMat'] = $Linha[9];
        $todosMaterial[$posicao]['desDetalhadaMaterial'] = $Linha[10];
        $todosMaterial[$posicao]['marcaMaterial'] = $Linha[11];
        $todosMaterial[$posicao]['modeloMaterial'] = $Linha[12];
        $todosMaterial[$posicao]['valoresEstimadoMaterial'] = $Linha[13];
        $todosMaterial[$posicao]['valoresHomologadoMaterial'] = $Linha[14];
        $posicao ++;
    }
    $posicao --;
    for ($i = 0; $i <= $posicao; $i ++) {
        if ($todosMaterial[$i]['numLoteMaterial'] != $numLoteMatAntes) {
            /*
             * pega o lote atual e verifica em todas as suas ocorrências se há descrição detalhada do material
             *
             */
            $incremento = $i + 1;
            $loteAtual = $todosMaterial[$i]['numLoteMaterial'];
            
            $temDescriaoDetalhada = $todosMaterial[$i]['desDetalhadaMaterial'] != '';
            while (! $temDescriaoDetalhada && $posicao >= $incremento) {
                if ($loteAtual != $todosMaterial[$incremento]['numLoteMaterial']) {
                    break;
                }
                
                if ($incremento <= $posicao) {
                    $temDescriaoDetalhada = $todosMaterial[$incremento]['desDetalhadaMaterial'] != '';
                }
                
                if ($temDescriaoDetalhada) {
                    break;
                }
                $incremento ++;
            }
            
            if ($flagLote) {
                $tpl->block("BLOCO_LOTE");
            }
            
            $numLoteMatAntes = $todosMaterial[$i]['numLoteMaterial'];
            
            if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($todosMaterial[$i]['razaoSocForMat'])) {
                $soma = getTotalValorLogrado($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $todosMaterial[$i]['numLoteMaterial']);
                $tpl->LOTE = "LOTE " . ($todosMaterial[$i]['numLoteMaterial']) . " FORNECEDOR VENCEDOR : " . FormataCpfCnpj($todosMaterial[$i]['cgcForCredMat']) . " - " . ($todosMaterial[$i]['razaoSocForMat']) . " - " . "R$ " . (number_format($soma, 2, ",", "."));
            } else {
                $tpl->LOTE = "LOTE " . ($todosMaterial[$i]['numLoteMaterial']);
            }
            
            $tpl->block("DESCRICAO_LOTE");
            $flagLote = true;
        }
        
        $tpl->ORDEM = $todosMaterial[$i]['ordeMaterial'];
        $tpl->DESCRICAO = $todosMaterial[$i]['descMaterial'];
        $tpl->CODIGO = $todosMaterial[$i]['seqMaterial'];
        $tpl->UNIDADE = $todosMaterial[$i]['UnidadeMaterial'];
        
        if ($temDescriaoDetalhada) {
            $tpl->DESCRICAO_DETALHADA = ($todosMaterial[$i]['desDetalhadaMaterial'] != "") ? $todosMaterial[$i]['desDetalhadaMaterial'] : "---";
            $tpl->COLSPAN = 3;
            $tpl->block("BLOCO_DESC_MAT_TITULO");
            $tpl->block("BLOCO_DESC_MAT");
        } else {
            $tpl->COLSPAN = 6;
        }
        
        // $tpl->DESCRICAO_DETALHADA = ($descDetalhadaMaterial != "") ? $descDetalhadaMaterial : "---";
        $tpl->QUANTIDADE = number_format($todosMaterial[$i]['quantidadeMaterial'], "4", ",", ".");
        $tpl->MARCA = ($todosMaterial[$i]['marcaMaterial'] != "") ? $todosMaterial[$i]['marcaMaterial'] : "---";
        $tpl->MODELO = ($todosMaterial[$i]['modeloMaterial'] != "") ? $todosMaterial[$i]['modeloMaterial'] : "---";
        
        $tpl->block("BLOCO_LOTE_ITEM_MATERIAL");
        
        $totalItemMaterial ++;
    }
    
    if ($totalItemMaterial >= $Rows) {
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
    while ($Linha = $result->fetchRow()) {
        $ordServico = $Linha[0];
        $descServico = $Linha[1];
        $seqServico = $Linha[2];
        $numLoteServico = $Linha[3];
        $codForCredServ = $Linha[4];
        $razaoSocForServ = $Linha[5];
        $nomeFantFornServ = $Linha[6];
        $cgcForCredServ = $Linha[7];
        $descDetalhadaServico = $Linha[8];
        $valorEstimadoItem = $Linha[9];
        $valorHomologadoItem = $Linha[10];
        
        if ($numLoteServico != $numLoteServAntes) {
            if ($flagLoteServico) {
                $tpl->block("BLOCO_LOTE_SERVICOS");
            }
            
            $numLoteServAntes = $numLoteServico;
            
            if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and ! empty($razaoSocForServ)) {
                $soma = getTotalValorServico($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);
                $tpl->LOTE = "LOTE " . ($numLoteServico) . " FORNECEDOR VENCEDOR: " . FormataCpfCnpj($cgcForCredServ) . " - " . ($razaoSocForServ) . " - " . "R$ " . (number_format((float) $soma, 2, ",", "."));
            } else {
                $tpl->LOTE = "LOTE " . ($numLoteServico);
            }
            
            $tpl->block("DESCRICAO_LOTE_SERVICOS");
            $flagLoteServico = true;
        }
        
        $tpl->ORDEM = $ordServico;
        $tpl->DESCRICAO = $descServico;
        $tpl->CODIGO = $seqServico;
        
        $tpl->DESCRICAO_DETALHADA = strtoupper2($descDetalhadaServico);
        $tpl->COLSPAN = 3;
        $tpl->block("BLOCO_DESC_MAT_TITULO");
        $tpl->block("BLOCO_DESC_MAT");
        
        $tpl->block("BLOCO_LOTE_ITEM_SERVICOS");
        
        $totalItensServico ++;
    }
    
    if ($totalItensServico >= $Rows) {
        $tpl->block("BLOCO_LOTE_SERVICOS");
    }
    
    $tpl->block("BLOCO_SERVICOS");
}

$paramentrosConsultaDocumentos = 'ConsAcompDetalhesEditaiseAnexos.php?processo=' . $Processo . '&ano=' . $ProcessoAno . '&comissao=' . $ComissaoCodigo . '&grupo=' . $GrupoCodigo;
$tpl->DOCUMENTOS_RELACIONADOS = $paramentrosConsultaDocumentos;
$tpl->block("BLOCO_DOCUMENTOS_RELACIONADOS");

// Pega as Fases da Licitação #
$sql = "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
$sql .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
$sql .= "       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT";
$sql .= "  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
$sql .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
$sql .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
$sql .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
$sql .= "       LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI";
$sql .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
$sql .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI <> 1 "; // Menos a fase Interna
$sql .= " ORDER BY B.TFASELDATA ASC,A.EFASESDESC ";
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
        $codigoAta = $linhaFase[7];
        $nomeAta = $linhaFase[8];
        $faseCod = $linhaFase[4];
        
        if ($descricaoFase == "HOMOLOGAÇÃO" && $codigoAta != "" && $nomeAta != "") {
            $totalAtasNaHomologacao ++;
        }
    }
    
    // Exibe link direto para o único arquivo
    if ($totalAtasNaHomologacao == 1) {
        $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $faseCod . "_" . $codigoAta;
        $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
        addArquivoAcesso($ArqUpload);
        
        $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$faseCod&AtaCodigo=$codigoAta";
        
        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        
        $tpl->URL_DIRETA_ATA = $Url;
        $tpl->block("PROCESSO_LICITATORIO");
    }
    
    // Caso exista mais de uma ata na fase de homologação será exibido um link para um popup
    if ($totalAtasNaHomologacao > 1) {
        $paramentrosConsultaDocumentos = "processo=$Processo&ano=$ProcessoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo&orgaoLicitante=$OrgaoLicitanteCodigo";
        
        $tpl->EVENTOMUITOSPROCESSOS = "onclick=javascript:AbreJanelaItem(\'../licitacoes/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php?' . $paramentrosConsultaDocumentos . '\', 900, 350);" > $tpl->block("PROCESSO_LICITATORIO");
    }
}

$Rows = $result->numRows();

if ($Rows > 0) {
    while ($Linha = $result->fetchRow()) {
        $FaseCodigo = $Linha[4];
        $DataFase = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
        $FaseDetalhamento = $Linha[5];
        $nomeAta = $Linha[8];
        $itemObservacao = " - <b>Observação/ Justificativa:</b> \"" . $Linha[9] . "\"";
        $itemExcluido = $Linha[10];
        $itemAutor = " - <b>Responsável:</b> \"" . $Linha[11] . "\"";
        $itemDataAlteracao = $Linha[12];
        
        if ($itemDataAlteracao < "2011-03-23") {
            $itemObservacao = "";
            $itemAutor = "";
        }
        
        $valor = "";
        
        if ($Linha[4] == $CodFaseAnterior) {
            $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha[7];
            $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
            addArquivoAcesso($ArqUpload);
            
            if ($itemExcluido == "S") {
                $valor .= "<s><br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
            } elseif (file_exists($Arquivo)) {
                $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                
                if (! in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                
                $valor .= "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
            } else {
                $valor .= "<br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
            }
        } else {
            $valor .= "<tr>\n";
            $DataFase = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
            $valor .= "<td colspan='3' style='text-align: left'>$Linha[0]</td>\n";
            $valor .= "<td colspan='3' style='text-align: left'>$DataFase</td>\n";
            $valor .= "<td colspan='3' style='text-align: left'>$Linha[5]&nbsp;</td>\n";
            
            if ($Linha[7] != 0) {
                $ArqUpload = "licitacoes/" . "ATASFASE" . $GrupoCodigo . "_" . $Processo . "_" . $ProcessoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $FaseCodigo . "_" . $Linha[7];
                $Arquivo = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
                addArquivoAcesso($ArqUpload);
                
                if ($itemExcluido == "S") {
                    $valor .= "<td style=\"text-align: left\" colspan=\"3\"><img src='../midia/disqueteInexistente.gif' border='0'><s><font color=\"#000000\"> $nomeAta</font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                } elseif (file_exists($Arquivo)) {
                    $Url = "../licitacoes/ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";
                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }
                    $valor .= "<td colspan='3' style='text-align: left'><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                } else {
                    $valor .= "<td colspan='3' style='text-align: left'><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta</font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                }
            } else {
                $valor .= "<td colspan='3' style='text-align: left'>&nbsp;</td>";
            }
        }
        
        $tpl->BLOCOTOTAL = $valor;
        $tpl->block("BLOCO_LISTA_FASES");
        $tpl->BLOCOTOTAL = "";
        $CodFaseAnterior = $Linha[4];
    }
    
    $tpl->block("BLOCO_TABELA_FINAL");
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

if ($Rows >= 1) {
    while ($Linha = $result->fetchRow()) {
        $Resultados = 1;
        $ResultadoHabi = $Linha[0];
        $ResultadoInab = $Linha[1];
        $ResultadoJulg = $Linha[2];
        $ResultadoRevo = $Linha[3];
        $ResultadoAnul = $Linha[4];
    }
} else {
    $Resultados = 0;
}

$db->disconnect();

if ($ResultadoHabi != "") {
    $tpl->TIPODESCRICAO = "EMPRESAS HABILITADAS";
    $tpl->TIPORESULTADO = $ResultadoHabi;
    $tpl->block("BLOCO_TIPO_RESULTADO");
}

if ($ResultadoInab != "") {
    $tpl->TIPODESCRICAO = "EMPRESAS HABILITADAS";
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

$tpl->show();
