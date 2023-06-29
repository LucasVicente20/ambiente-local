<?php
/**
 * Porta de Compras
 * 
 * Programa: RelAcompanhamentoSCCPdf.php
 * Autor:    João Batista Brito
 * Data:     08/08/2012
 * Objetivo: Programa de impressão de relatório de acompanhamento da SCC em FPDF
 * ------------------------------------------------------------------------------------------------------------
 * Autor:    João Batista Brito
 * Data:     03/09/2012
 * Objetivo: Correção de Erros - Redmine 14741
 * ------------------------------------------------------------------------------------------------------------
 * Autor:    João Batista Brito
 * Data:     18/10/2012
 * Objetivo: Criar campo Número no relatório - Redmine 15787
 * ------------------------------------------------------------------------------------------------------------
 * Autor:    Daniel Semblano
 * Data:     01/04/2014
 * 
 * Objetivo: [CR121594]: REDMINE 15 (P2)
 *           - Comentado exibição dos dados de pré-solicitação de empenho
 *           - Só imprimir a tabela de Itens da solicitação de material ou serviço se existirem;
 *           - Exibir o campo SCC´S AGRUPADAS: antes do campo JUSTIFICATIVA
 *           - Exibir SCCs agrupadas
 * ------------------------------------------------------------------------------------------------------------
 * Autor:    José Francisco <jose.francisco@pitang.com>
 * Data:     09/06/2014 - [CR121776]: REDMINE 14 (P4)
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     07/11/2018
 * Objetivo: Tarefa Redmine 205440
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     03/12/2018
 * Objetivo: Tarefa Redmine 207316
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     20/12/2018
 * Objetivo: Tarefa Redmine 208547
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     03/06/2019
 * Objetivo: Tarefa Redmine 218084
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     19/06/2019
 * Objetivo: Tarefa Redmine 219095
 * ------------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     22/03/2023
 * Objetivo: Tarefa Redmine 280573
 * ------------------------------------------------------------------------------------------------------------
 */

 # Acesso ao arquivo de funções #
include '../funcoes.php';
require_once 'funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
$ata = null;
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Solicitacao = filter_input(INPUT_GET, 'Solicitacao');
    if(!empty($_GET['ata'])) {
        $ata = $_GET['ata'];
    }
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Acompanhamento de Solicitação de Compra e Contratação - SCC";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L", "mm", "A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 6);

$db = Conexao();
$dbOracle = ConexaoOracle();

#recuperando dados da SCC informada
$sqlSCC = " SELECT s.ccenposequ, s.esolcoobse, s.esolcoobje, s.esolcojust, s.asolcoanos,
                        s.ctpcomcodi, t.etpcomnome, s.tsolcodata, s.clicpoproc, s.alicpoanop,
                        s.ccomlicodi, s.corglicod1, s.cgrempcodi, s.dsolcodpdo, s.ctpleitipo,
                        s.cleiponume, s.cartpoarti, s.cincpainci, s.fsolcorgpr, s.fsolcorpcp,
                        s.fsolcocont, d.eorglidesc, s.csitsocodi, i.esitsonome, p.nincpanume, 
                        s.csolcotipcosequ, n.carpnotiat, d.eorglidesc, arpi.carpincodn, arpi.aarpinanon,
                        s.corglicodi, arpe.carpexcodn, arpe.aarpexanon, ol.eorglidesc as orgaointerna,
                        arpe.earpexorgg, s.cintrpsequ, s.cintrpsano, n.carpnosequ, arpe.earpexproc
             FROM sfpc.tbsolicitacaocompra s
            INNER JOIN sfpc.tborgaolicitante d ON d.corglicodi = s.corglicodi
            INNER JOIN sfpc.tbsituacaosolicitacao i ON i.csitsocodi = s.csitsocodi
            INNER JOIN sfpc.tbtipocompra t ON t.ctpcomcodi = s.ctpcomcodi
            LEFT JOIN sfpc.tbincisoparagrafoportal p ON p.cleiponume = s.cleiponume           
                AND p.ctpleitipo = s.ctpleitipo
                AND p.cartpoarti = s.cartpoarti
                AND p.cincpainci = s.cincpainci
            LEFT JOIN sfpc.tbataregistropreconova n ON n.carpnosequ = s.carpnosequ
            LEFT JOIN SFPC.TBataregistroprecointerna arpi ON arpi.carpnosequ = s.carpnosequ
            LEFT JOIN SFPC.TBorgaolicitante ol ON ol.corglicodi = arpi.corglicodi
            LEFT JOIN SFPC.TBataregistroprecoexterna arpe ON arpe.carpnosequ = s.carpnosequ
            WHERE s.csolcosequ = $Solicitacao ";

$linha                     = resultObjetoUnico(executarSQL($db, $sqlSCC));
$CentroCusto               = $linha->ccenposequ;
$Observacao                = $linha->esolcoobse;
$Objeto                    = $linha->esolcoobje;
$Justificativa             = $linha->esolcojust;
$Ano                       = $linha->asolcoanos;
$Numero                    = $linha->csolcotipcosequ;
$TipoCompra                = $linha->ctpcomcodi;
$TipoCompraDesc            = $linha->etpcomnome;
$DataHora                  = $linha->tsolcodata;
$DataSolicitacao           = DataBarra($DataHora) . " " . Hora($DataHora);
$CodigoProcessoLicitatorio = $linha->clicpoproc;
$AnoProcessoSARP           = $linha->alicpoanop;
$Artigo                    = $linha->cartpoarti;
$Inciso                    = $linha->cincpainci;
$Paragrafo                 = $linha->nincpanume;
$TipoLei                   = $linha->ctpleitipo;
$RegistroPreco             = $linha->fsolcorgpr;
$dataPublicacao            = $linha->dsolcodpdo;
$Sarp                      = $linha->fsolcorpcp;
$TipoReservaOrcamentaria   = 1; // se é bloqueio (1) ou dotação (2)
$GeraContrato              = $linha->fsolcocont;
$LeiProcessoSARP           = $linha->cleiponume;
$NumProcessoSARP           = $linha->cartpoarti;
$ComissaoCodigoSARP        = $linha->fsolcorgpr;
$TipoSARP                  = $linha->fsolcorpcp;
$GrupoEmpresaCodigoSARP    = $linha->fsolcocont;
$Orgao                     = $linha->eorglidesc;
$SituacaoAtual             = $linha->esitsonome;
$TipoAta                   = $linha->carpnotiat;
$OrgaoGestor               = $linha->eorglidesc;
$CarregaProcessoSARP       = 1;
$carpincodn                = $linha->carpincodn;
$aarpinanon                = $linha->aarpinanon;
$corglicodi                = $linha->corglicodi;
$carpexcodn                = $linha->carpexcodn;
$arpexanon                 = $linha->aarpexanon;
$orgaointerna              = $linha->orgaointerna;
$orgaoexterno              = $linha->earpexorgg;
$intencaoSequ              = $linha->cintrpsequ;
$intencaoAno               = $linha->cintrpsano;
$carpnosequ                = $linha->carpnosequ;
$processoAtaExterna        = $linha->earpexproc;

if ($GeraContrato == "S") {
    $GeraContrato = "SIM";
} elseif ($GeraContrato == "N") {
    $GeraContrato = "NÃO";
}

$sqlMaterial = "SELECT sc.cmatepsequ, sc.cservpsequ, sc.eitescdescse, sc.aitescqtso, sc.vitescunit,
                        sc.vitescvexe, sc.aitescqtex, sc.eitescmarc, sc.eitescmode, sc.cusupocodi,
                        sc.aforcrsequ, sc.citescsequ, f.aforcrccpf, f.aforcrccgc, sc.eitescdescmat
                    FROM SFPC.TBitemsolicitacaocompra sc
                    LEFT JOIN sfpc.tbfornecedorcredenciado f ON f.aforcrsequ = sc.aforcrsequ
                    WHERE sc.csolcosequ = $Solicitacao
                    ORDER BY sc.aitescorde ";

$res = executarSQL($db, $sqlMaterial);
$cntMaterial = -1;
$cntServico = -1;
$tipoItem = null;
$strBloqueioDotacao = null;

// quando houver material cadum generico, exibe coluna descrição detalhada
$exibeTd = false;

#para cada item de solicitação
while ($linha = $res->fetchRow(DB_FETCHMODE_OBJECT)) {

    $codigoItem = $linha->citescsequ;
    if (!is_null($linha->aforcrccpf)) {
        #cpf
        $fornecedorItem = $linha->aforcrccpf;
    } else {
        #cnpj
        $fornecedorItem = $linha->aforcrccgc;
    }

    # ITENS DA SOLICITACAO DE MATERIAL #
    if (!is_null($linha->cmatepsequ)) {
        $cntMaterial++;
        $MaterialCheck[$cntMaterial] = false;
        $MaterialCod[$cntMaterial] = $linha->cmatepsequ;
        $MaterialQuantidade[$cntMaterial] = converte_valor_estoques($linha->aitescqtso);
        $MaterialValorEstimado[$cntMaterial] = converte_valor_estoques($linha->vitescunit);
        $MaterialTotalExercicio[$cntMaterial] = converte_valor_estoques($linha->vitescvexe);
        $MaterialQuantidadeExercicio[$cntMaterial] = converte_valor_estoques($linha->aitescqtex);

        // Se houver pelo menos um material cadum genérico com descrição detalhada preenchida
        if ( ( hasIndicadorCADUM($db, $linha->cmatepsequ) ) && !empty($linha->eitescdescmat) ) {
            $MaterialDescDetalhada[$cntMaterial] = $linha->eitescdescmat;
            $exibeTd = true;
        }

        if ($TipoCompra != 2) {
            $MaterialMarca[$cntMaterial] = $linha->eitescmarc;
            $MaterialModelo[$cntMaterial] = $linha->eitescmode;
        } else {
            $MaterialMarca[$cntMaterial] = $linha->eitelpmarc;
            $MaterialModelo[$cntMaterial] = $linha->eitelpmode;
        }

        $MaterialFornecedor[$cntMaterial] = $fornecedorItem;
        $MaterialBloqueio[$cntMaterial] = "";
        $MaterialBloqueioItem[$cntMaterial] = array();
        $tipoItem = 'M';
        $MaterialUltimoPreco[$cntMaterial] = calcularValorUltimoPreco($db, $Solicitacao, $MaterialCod[$cntMaterial]);

        # ITENS DA SOLICITACAO DE SERVICO #
    } else {
        $cntServico++;
        $ServicoCheck[$cntServico] = false;
        $ServicoCod[$cntServico] = $linha->cservpsequ;
        $ServicoDescricaoDetalhada[$cntServico] = $linha->eitescdescse;
        $ServicoQuantidade[$cntServico] = converte_valor_estoques($linha->aitescqtso);
        $ServicoValorEstimado[$cntServico] = converte_valor_estoques($linha->vitescunit);
        $ServicoTotalExercicio[$cntServico] = converte_valor_estoques($linha->vitescvexe);
        $ServicoQuantidadeExercicio[$cntServico] = converte_valor_estoques($linha->aitescqtex);
        $ServicoFornecedor[$cntServico] = $fornecedorItem;
        $ServicoBloqueio[$cntServico] = "";
        $ServicoBloqueioItem[$cntServico] = array();
        $tipoItem = 'S';
    }
}

$reservasDados = array();
$cntBloqueios = -1;

# reservas
if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == "S") { //neste caso o é uma dotação
    $isDotacao = true;

    #pegar dotação
    $sql = "SELECT DISTINCT aitcdounidoexer, citcdounidoorga, citcdounidocodi, citcdotipa, aitcdoordt,
                       citcdoele1, citcdoele2, citcdoele3, citcdoele4, citcdofont
                  FROM SFPC.TBitemdotacaoorcament
                 WHERE csolcosequ = $Solicitacao";

    $res2 = executarSQL($db, $sql);

    while ($linha = $res2->fetchRow(DB_FETCHMODE_OBJECT)) {
        $cntBloqueios++;
        $ano = $linha->aitcdounidoexer;
        $orgao = $linha->citcdounidoorga;
        $unidade = $linha->citcdounidocodi;
        $tipoAtividade = $linha->citcdotipa;
        $atividade = $linha->aitcdoordt;
        $elemento1 = $linha->citcdoele1;
        $elemento2 = $linha->citcdoele2;
        $elemento3 = $linha->citcdoele3;
        $elemento4 = $linha->citcdoele4;
        $fonte = $linha->citcdofont;

        $dotacao = getDadosDotacaoOrcamentariaFromChave(
                $dbOracle, $ano, $orgao, $unidade, $tipoAtividade, $atividade, $elemento1, $elemento2, $elemento3, $elemento4, $fonte
        );

        $strBloqueioDotacao = $dotacao['dotacao'];

        $reservasDados[$cntBloqueios] = $strBloqueioDotacao;
    }
} else {
    #pegar bloqueio

    $sql = "SELECT DISTINCT aitcblnbloq, aitcblanob
                  FROM SFPC.TBitembloqueioorcament
                 WHERE csolcosequ = $Solicitacao";

    $res2 = executarSQL($db, $sql);

    while ($linha = $res2->fetchRow(DB_FETCHMODE_OBJECT)) {    
        $cntBloqueios++;
        $bloqChaveAno = $linha->aitcblanob;
        $bloqChaveSequ = $linha->aitcblnbloq;
        $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqChaveAno, $bloqChaveSequ);
        $strBloqueioDotacao = $bloqueioArray['bloqueio'];
        $reservasDados[$cntBloqueios] = $strBloqueioDotacao;
    }
}
$reservas = implode(' ', $reservasDados);

# Pegando os dados dos materiais
$QuantidadeMaterial = count($MaterialCod);
for ($itr = 0; $itr < $QuantidadeMaterial; $itr++) {
    $sql = " SELECT m.ematepdesc, u.eunidmsigl
                 FROM SFPC.TBmaterialportal m, SFPC.TBunidadedemedida u
                WHERE m.cmatepsequ = " . $MaterialCod[$itr] . "
                  AND u.cunidmcodi = m.cunidmcodi ";

    $res = executarSQL($db, $sql);
    $Linha = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $MaterialDescricao = $Linha->ematepdesc;
    $MaterialUnidade = $Linha->eunidmsigl;
    $pos = count($materiais);

    if (is_null($MaterialQuantidade[$itr]) or $MaterialQuantidade[$itr] == "") {
        $MaterialQuantidade[$itr] = "0,0000";
    }
    if (is_null($MaterialValorEstimado[$itr]) or $MaterialValorEstimado[$itr] == "") {
        $MaterialValorEstimado[$itr] = "0,0000";
    }
    if (is_null($MaterialQuantidadeExercicio[$itr]) or $MaterialQuantidadeExercicio[$itr] == "") {
        $MaterialQuantidadeExercicio[$itr] = "0,0000";
    }
    if (is_null($MaterialTotalExercicio[$itr]) or $MaterialTotalExercicio[$itr] == "") {
        $MaterialTotalExercicio[$itr] = "0,0000";
    }

    $materiais[$pos]["posicao"] = $pos; // posição no array
    $materiais[$pos]["posicaoItem"] = $pos + 1; // posição mostrada na tela
    $materiais[$pos]["tipo"] = TIPO_ITEM_MATERIAL;
    $materiais[$pos]["codigo"] = $MaterialCod[$itr];
    $materiais[$pos]["descricao"] = $MaterialDescricao;
    $materiais[$pos]["unidade"] = $MaterialUnidade;
    if (is_null($MaterialCheck[$itr]) or ! $MaterialCheck[$itr]) {
        $materiais[$pos]["check"] = false;
    } else {
        $materiais[$pos]["check"] = true;
    }
    $materiais[$pos]["quantidade"] = $MaterialQuantidade[$itr];
    $materiais[$pos]["valorEstimado"] = $MaterialValorEstimado[$itr];

    //valores em float para uso em funções
    $materiais[$pos]["quantidadeItem"] = moeda2float($MaterialQuantidade[$itr]);
    $materiais[$pos]["valorItem"] = moeda2float($MaterialValorEstimado[$itr]);

    $materiais[$pos]["quantidadeExercicio"] = $MaterialQuantidadeExercicio[$itr];
    $materiais[$pos]["marca"] = $MaterialMarca[$itr];
    $materiais[$pos]["modelo"] = $MaterialModelo[$itr];
    $materiais[$pos]["fornecedor"] = $MaterialFornecedor[$itr];
    $materiais[$pos]["descdetalhada"] = $MaterialDescDetalhada[$itr];
    if (!is_null($MaterialUltimoPreco[$itr])) {
        $materiais[$pos]["ultimoPreco"] = converte_valor_estoques($MaterialUltimoPreco[$itr]);
    } else {
        $materiais[$pos]["ultimoPreco"] = "INEXISTENTE";
    }

    $CnpjStr = FormataCpfCnpj($materiais[$itr]["fornecedor"]);

    if (!is_null($materiais[$itr]["fornecedor"])) {
        $CPFCNPJ = removeSimbolos($materiais[$itr]["fornecedor"]);
        $materialServicoFornecido = $materiais[$itr]["codigo"];
        $tipoMaterialServico = TIPO_ITEM_MATERIAL;

        $fornRazao = checaSituacaoFornecedor($db, $CPFCNPJ);
    }

    $materiais[$pos]["fornecedorDesc"] = $CnpjStr . " " . $fornRazao["razao"]; // aqui preenche o campo descricao de fornecedor

    if (moeda2float($materiais[$pos]["quantidade"]) == 1) {
        $materiais[$pos]["totalExercicio"] = $MaterialTotalExercicio[$itr];
    } else {
        $materiais[$pos]["totalExercicio"] = converte_valor_estoques(moeda2float($materiais[$pos]["quantidadeExercicio"]) * moeda2float($materiais[$pos]["valorEstimado"]));
    }

    $materiais[$pos]["trp"] = calcularValorTrp($db, $TipoCompra, $materiais[$pos]["codigo"]);
    if (!is_null($materiais[$pos]["trp"])) {
        $materiais[$pos]["trp"] = converte_valor_estoques($materiais[$pos]["trp"]);
        # Na regra o valor estimado deveria ser preenchido, mas isso gera um problema.
        # O valor TRP pode ser alterado por outros usuários antes da SCC ser salva, o que, ao apertar o botáo incluir, alteraria o valor estimado
        # sem o usuário saber
        /*
          if (is_null($materiais[$pos]["valorEstimado"]) or moeda2float($materiais[$pos]["valorEstimado"])==0) {
          $materiais[$pos]["valorEstimado"] = $materiais[$pos]["trp"];
          } */
    }
}

# Pegando os dados dos servicos enviados por POST
$QuantidadeServico = count($ServicoCod);
for ($itr = 0; $itr < $QuantidadeServico; $itr++) {

    $sql = "SELECT m.eservpdesc
                  FROM SFPC.TBservicoportal m
                 WHERE m.cservpsequ = " . $ServicoCod[$itr] . " ";

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
    }
    $Linha = $res->fetchRow(DB_FETCHMODE_OBJECT);
    $Descricao = $Linha->eservpdesc;

    $pos = count($servicos);

    #preenchendo valores padrões caso seja nulo, para não ocorrer erro.
    if (is_null($ServicoQuantidade[$itr]) or $ServicoQuantidade[$itr] == "") {
        $ServicoQuantidade[$itr] = "0,0000";
    }
    if (is_null($ServicoValorEstimado[$itr]) or $ServicoValorEstimado[$itr] == "") {
        $ServicoValorEstimado[$itr] = "0,0000";
    }
    if (is_null($ServicoQuantidadeExercicio[$itr]) or $ServicoQuantidadeExercicio[$itr] == "") {
        $ServicoQuantidadeExercicio[$itr] = "0,0000";
    }
    if (is_null($ServicoTotalExercicio[$itr]) or $ServicoTotalExercicio[$itr] == "") {
        $ServicoTotalExercicio[$itr] = "0,0000";
    }

    $servicos[$pos]["posicao"] = $pos;
    $servicos[$pos]["posicaoItem"] = $pos + 1; // posição mostrada na tela
    $servicos[$pos]["tipo"] = TIPO_ITEM_SERVICO;
    $servicos[$pos]["codigo"] = $ServicoCod[$itr];
    $servicos[$pos]["descricao"] = $Descricao;
    $servicos[$pos]["descricaoDetalhada"] = $ServicoDescricaoDetalhada[$itr];
    if (is_null($ServicoCheck[$itr]) or ! $ServicoCheck[$itr]) {
        $servicos[$pos]["check"] = false;
    } else {
        $servicos[$pos]["check"] = true;
    }
    $servicos[$pos]["quantidade"] = $ServicoQuantidade[$itr];
    $servicos[$pos]["valorEstimado"] = $ServicoValorEstimado[$itr];

    //valores em float para uso em funções

    $servicos[$pos]["quantidadeItem"] = moeda2float($ServicoQuantidade[$itr]);
    $servicos[$pos]["valorItem"] = moeda2float($ServicoValorEstimado[$itr]);

    $servicos[$pos]["quantidadeExercicio"] = $ServicoQuantidadeExercicio[$itr];
    $servicos[$pos]["fornecedor"] = $ServicoFornecedor[$itr];

    $CnpjStr = FormataCpfCnpj($servicos[$pos]["fornecedor"]);

    if (!is_null($servicos[$pos]["fornecedor"])) {
        $CPFCNPJ = removeSimbolos($servicos[$pos]["fornecedor"]);
        $materialServicoFornecido = $servicos[$pos]["codigo"];
        $tipoMaterialServico = TIPO_ITEM_MATERIAL;

        $fornRazao = checaSituacaoFornecedor($db, $CPFCNPJ);
    }

    $servicos[$pos]["fornecedorDesc"] = $CnpjStr . " " . $fornRazao["razao"];

    if (moeda2float($servicos[$pos]["quantidade"]) == 1) {
        $servicos[$pos]["totalExercicio"] = $ServicoTotalExercicio[$itr];
    } else {
        $servicos[$pos]["totalExercicio"] = converte_valor_estoques(moeda2float($servicos[$pos]["quantidadeExercicio"]) * moeda2float($servicos[$pos]["valorEstimado"]));
    }
}

$historico = array();
$sql = "SELECT ss.esitsonome, hss.thsitsdata, u.eusuporesp, u.eusupomail, u.ausupofone
          FROM SFPC.TBhistsituacaosolicitacao hss, SFPC.TBsituacaosolicitacao ss, SFPC.TBusuarioportal u
         WHERE hss.csitsocodi = ss.csitsocodi
           AND hss.cusupocodi = u.cusupocodi
           AND csolcosequ = $Solicitacao
      ORDER BY hss.thsitsdata DESC ";

$res = executarSQL($db, $sql);
$itr = 0;
while ($linha = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
    $historico[$itr]["nomeSituacao"] = $linha->esitsonome;
    $historico[$itr]["dataSituacao"] = DataBarra($linha->thsitsdata) . " " . hora($linha->thsitsdata);
    $historico[$itr]["nomeUsuario"] = $linha->eusuporesp;
    $historico[$itr]["emailUsuario"] = $linha->eusupomail;
    $historico[$itr]["foneUsuario"] = $linha->ausupofone;

    $itr++;
}

$sql = " SELECT ";
$sql.= " a.CPRESOSEQU as numero, ";
$sql.= " a.APRESOANOE as ano, ";
$sql.= " to_char(a.TPRESOGERA,'DD/MM/YYYY HH:MI') as datahora,";
$sql.= " a.APRESONBLOQ as bloqueio, ";
$sql.= " a.APRESOANOB as anobloqueio,";
$sql.= " c.aforcrccgc as cgc, ";
$sql.= " c.aforcrccpf as cpf, ";
$sql.= " c.nforcrrazs as razao, ";
$sql.= " a.CMOTNICODI as idmotivo, ";
$sql.= " d.emotnidesc as descricao, ";
$sql.= " a.apresonues as numeroemp, ";
$sql.= " a.apresonues as anoemp, ";
$sql.= " to_char(a.TPRESOULAT,'DD/MM/YYYY') as datault,";
$sql.= " to_char(a.TPRESOIMPO,'DD/MM/YYYY') as dataimportacao,";
$sql.= " to_char(a.DPRESOCSEM,'DD/MM/YYYY') as datacancel,";
$sql.= " to_char(a.DPRESOGERE,'DD/MM/YYYY') as datageracao,";
$sql.= " a.APRESONUES as numemp,";
$sql.= " a.APRESOANES as anoemp,";
$sql.= " to_char(a.DPRESOANUE,'DD/MM/YYYY') as dataanulacao,";
$sql.= " a.VPRESOANUE as valoranulado, ";
$sql.= " sum(b.VIPRESEMPN) as soma ";
$sql.= " FROM ";
$sql.= " sfpc.tbpresolicitacaoempenho a ";
$sql.= " LEFT JOIN sfpc.tbfornecedorcredenciado c ON c.aforcrsequ  = a.aforcrsequ";
$sql.= " LEFT JOIN sfpc.TBITEMPRESOLICITACAOEMPENHO b ON (a.CPRESOSEQU  = b.CPRESOSEQU and  a.APRESOANOE=b.APRESOANOE )";
$sql.= " LEFT JOIN sfpc.tbmotivonaoimportacao d ON d.cmotnicodi = a.cmotnicodi";
$sql.= " WHERE ";
$sql.= " a.CSOLCOSEQU=$Solicitacao and ";
$sql.= " a.CPRESOSEQU = b.CPRESOSEQU ";
$sql.= " and a.APRESOANOE = b.APRESOANOE ";
$sql.= " and a.aforcrsequ = c.aforcrsequ ";
$sql.= " group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20  ";
$result = executarTransacao($db, $sql);

$contAux = 0;

$empenho = array();
$itr = 0;
while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
    $contAux = contAux + 1;
    $empenho[$itr]["numero"] = $row->numero;
    $empenho[$itr]["ano"] = $row->ano;
    $empenho[$itr]["datahora"] = $row->datahora;

    //--------Formatar bloqueio
    if (!empty($row->anobloqueio) && !empty($row->bloqueio))
        $vetor = getDadosBloqueioFromChave($dbOracle, $row->anobloqueio, $row->bloqueio);
    $empenho[$itr]["blqFormato"] = $vetor['bloqueio'];

    //-------Formatar cpf/cgc de fornecedor
    if (!empty($row->cpf))
        $cpfcgc = $row->cpf;
    else
        $cpfcgc = $row->cgc;

    $empenho[$itr]["fornecedor"] = FormataCpfCnpj($cpfcgc) . " " . $row->razao;

    //-------Formatar soma
    $empenho[$itr]["soma"] = number_format($row->soma, 4, ",", ".");
    //-------Formatar mensagens da situacao e datas
    if (!empty($row->idmotivo)) {
        $empenho[$itr]["descSituacao"] = "PSE RECUSADA POR MOTIVO DE " . $row->descricao;
        $empenho[$itr]["dataMotivo"] = $row->datault;
    }
    if (!empty($row->dataimportacao)) {
        $empenho[$itr]["descSituacao"] = "PSE GERADA";
        $empenho[$itr]["dataMotivo"] = $row->datault;
    }
    if (!empty($row->datacancel)) {
        $empenho[$itr]["descSituacao"] = "PSE CANCELADA";
        $empenho[$itr]["dataMotivo"] = $row->datacancel;
    }
    if (!empty($row->datageracao)) {
        $empenho[$itr]["descSituacao"] = "EMPENHADO (NÚMERO=" . $row->numemp . "/" . $row->anoemp . ")";
        $empenho[$itr]["dataMotivo"] = $row->datageracao;
    }
    if (!empty($row->dataanulacao)) {
        $empenho[$itr]["descSituacao"] = "EMPENHO ANULADO (VALOR=" . number_format($row->valoranulado, 4, ",", ".") . ")";
        $empenho[$itr]["dataMotivo"] = $row->dataanulacao;
    }
    $itr++;
}

$pdf->Cell(32, 6, "  NÚMERO ", 1, 0, "L", 1);
$pdf->Cell(113, 6, getNumeroSolicitacaoCompra($db, $Solicitacao), 1, 0, "L", 0);

$pdf->Cell(30, 6, "  SEQUENCIAL ", 1, 0, "L", 1);
$pdf->Cell(105, 6, $Numero, 1, 1, "L", 0);

$h = 4;
$hm = 0;
$h1 = $pdf->GetStringHeight(113, $h, $DataSolicitacao, "L");
$h2 = $pdf->GetStringHeight(105, $h, trim($Orgao), "L");
$hm = $h1;
if ($hm < $h2)
    $hm = $h2;
$h1 = $hm / ($h1 / $h);
$h2 = $hm / ($h2 / $h);

if ($hm < 6) {
    $h1 = 6;
    $h2 = 6;
    $hm = 6;
}

$pdf->Cell(32, $hm, "  DATA/HORA DA SCC ", 1, 0, "L", 1);
$x = $pdf->GetX() + 113;
$y = $pdf->GetY();
$pdf->MultiCell(113, $h1, $DataSolicitacao, 1, "L", 0);
$pdf->SetXY($x, $y);
$pdf->Cell(30, $hm, "  ÓRGÃO ", 1, 0, "L", 1);
$pdf->MultiCell(105, $h2, trim($Orgao), 1, "L", 0);

$detalheTipoCompra = ' ';
switch ($TipoCompra) {
    case 2:
        $detalheTipoCompra.= 'REGISTRO DE PREÇOS: ';
        if ($ComissaoCodigoSARP == 'S') {
            $detalheTipoCompra.= 'SIM';
        } elseif ($ComissaoCodigoSARP == 'N') {
            $detalheTipoCompra.= 'NÃO';
        }
        break;
    case 3:
    case 4:
        $detalheTipoCompra.= 'LEGISLAÇÃO: Nº DA LEI-' . $LeiProcessoSARP . ' ARTIGO-' . $NumProcessoSARP . ' INCISO/PARÁGRAFO-' . $Paragrafo;
        if (trim($dataPublicacao) <> '') {
            $detalheTipoCompra.= ' DATA DE PUBLICAÇÃO DOM: ' . DataBarra($dataPublicacao);
        }
        break;
    case 5:
        if(!is_null($carpnosequ)) {
            $detalheTipoCompra.= '      TIPO DE ATA: ';
            if($TipoAta == 'I') {
                $detalheTipoCompra.= 'INTERNA   ';
                $numeroAta = numeroAtaSarpInterna($db, $corglicodi, $carpincodn, $aarpinanon);
                $OrgaoGestorAta = $orgaointerna;
            } else {
                $detalheTipoCompra.= 'EXTERNA   ';
                $numeroAta = $carpexcodn . '/' . $arpexanon;
                $OrgaoGestorAta = $orgaoexterno;
            }
        }

        $detalheTipoCompra.= '   TIPO DE SARP: ';
        if ($TipoSARP == 'C')
            $detalheTipoCompra.= 'CARONA            ';
        elseif ($TipoSARP == 'P')
            $detalheTipoCompra.= 'PARTICIPANTE            ';

        // Numero da ata
        if($TipoAta == 'I' && !is_null($carpnosequ)) {
            $detalheTipoCompra.= 'NÚMERO DA ATA: ' . $numeroAta;
            $detalheTipoCompra.= '                       ';
            $detalheTipoCompra.= '                       ';
            $detalheTipoCompra.= '                       ';
        }

        if ($TipoAta == 'E') {
            $detalheTipoCompra .= ' PROCESSO EXTERNO: ' . $processoAtaExterna;
        } else {
            if (trim($CodigoProcessoLicitatorio) != '') {
                $detalheTipoCompra.= ' PROCESSO: ' . $CodigoProcessoLicitatorio . '/' . $AnoProcessoSARP . '                                                                ';
            }
        }

        if(!empty($OrgaoGestorAta)) {
            $detalheTipoCompra.= '                  ORGÃO GESTOR: ' . $OrgaoGestorAta;
        }
            
        break;
}
$objetoH = !is_null($carpnosequ) ? 113 : 248;
$h = 4;
$hm = 0;
$h1 = $pdf->GetStringHeight(113, $h, trim($Objeto), "L");
$h2 = $pdf->GetStringHeight(105, $h, trim($TipoCompraDesc . $detalheTipoCompra), "L");
$hm = $h1;
if ($hm < $h2)
    $hm = $h2;
$h1 = $hm / ($h1 / $h);
$h2 = $hm / ($h2 / $h);

if ($hm < 6) {
    $h1 = 6;
    $h2 = 6;
    $hm = 6;
}

$pdf->Cell(32, $hm, "  OBJETO ", 1, 0, "L", 1);
$x = $pdf->GetX() + 113;
$y = $pdf->GetY();
$pdf->MultiCell(113, $h1, trim($Objeto), 1, "L", 0);
$pdf->SetXY($x, $y);
$pdf->Cell(30, $hm, "  TIPO DE COMPRA ", 1, 0, "L", 1);
$pdf->MultiCell(105, $h2, trim($TipoCompraDesc . $detalheTipoCompra), 1, "L", 0);

$h = 4;
$hm = 0;
$h1 = $pdf->GetStringHeight(113, $h, trim($GeraContrato), "L");
$h2 = $pdf->GetStringHeight(105, $h, trim($SituacaoAtual), "L");
$hm = $h1;
if ($hm < $h2)
    $hm = $h2;
$h1 = $hm / ($h1 / $h);
$h2 = $hm / ($h2 / $h);

if ($hm < 6) {
    $h1 = 6;
    $h2 = 6;
    $hm = 6;
}

$pdf->Cell(32, $hm, "  GERA CONTRATO ", 1, 0, "L", 1);
$x = $pdf->GetX() + 113;
$y = $pdf->GetY();
$pdf->Cell(113, $h1, trim($GeraContrato), 1, 0, "L", 0);
$pdf->SetXY($x, $y);
$pdf->Cell(30, $hm, "  SITUAÇÃO ", 1, 0, "L", 1);
$pdf->MultiCell(105, $h2, $SituacaoAtual, 1, "L", 0);

$pdf->Cell(32, 6, "  SCC'S AGRUPADAS ", 1, 0, "L", 1);
$pdf->MultiCell(248, 6, trim(getNumeroSolicitacaoCompraAgrupadas($db, $Solicitacao)), 1, "L", 0);

$h = 4;
$hm = 0;
$h1 = $pdf->GetStringHeight(248, $h, trim($Observacao), "L");
$hm = $h1;
$h1 = $hm / ($h1 / $h);

if ($hm < 6) {
    $h1 = 6;
    $h2 = 6;
    $hm = 6;
}
$pdf->Cell(32, $hm, "  OBSERVAÇÃO ", 1, 0, "L", 1);
$pdf->MultiCell(248, $h1, trim($Observacao), 1, "L", 0);

$h = 10;

$h = 4;
$hm = 0;
$h1 = $pdf->GetStringHeight(248, $h, trim($Justificativa), "L");
$hm = $h1;
$h1 = $hm / ($h1 / $h);

if ($hm < 6) {
    $h1 = 6;
    $h2 = 6;
    $hm = 6;
}
$pdf->Cell(32, $hm, "  JUSTIFICATIVA ", 1, 0, "L", 1);
$pdf->MultiCell(248, $h1, trim($Justificativa), 1, "L", 0);

$h = 10;
if(!empty($intencaoSequ) && !empty($intencaoAno)) {
    $pdf->Cell(32, $hm, "  INTENÇÃO ", 1, 0, "L", 1);
    $pdf->MultiCell(248, $h1, str_pad($intencaoSequ, 4, '0', STR_PAD_LEFT) . '/' . $intencaoAno, 1, "L", 0);
    
    $h = 10;
}

if ($isDotacao == false) {
    $pdf->Cell(280, 5, " BLOQUEIOS ", 1, 1, "C", 1);
    $pdf->MultiCell(280, $h, $reservas, 1, "L", 0);
} else {
    $pdf->Cell(280, 5, " DOTAÇÕES ORÇAMENTÁRIAS ", 1, 1, "C", 1);
    $pdf->MultiCell(280, $h, $reservas, 1, "L", 0);
}

# Materiais
$QuantidadeMateriais = count($materiais);
if ($QuantidadeMateriais != 0) {
    $pdf->Cell(280, 5, " ITENS DA SOLICITAÇÃO DE MATERIAL ", 1, 1, "C", 1);
    $pdf->Cell(8, 12, " ORD. ", 1, 0, "C", 0);
    $pdf->Cell(($exibeTd) ? '35' : '70' , 12, " DESCRIÇÃO ", 1, 0, "C", 0);

    $x = $pdf->GetX() + 8.5;
    $y = $pdf->GetY();
    $pdf->MultiCell(8.5, 3, " \nCÓD\nRED\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $pdf->Cell(8.5, 12, " UND. ", 1, 0, "C", 0);
    $pdf->Cell(20, 12, " QUANTIDADE ", 1, 0, "C", 0);

    if ($exibeTd) {
        $pdf->Cell(35, 12, " \nDESCRIÇÃO\n DETALHADA\n ", 1, 0, "C", 0);
    }

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 3, " \nVALOR\nTRP\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 3, " \nÚLTIMO\nPREÇO\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 3, " \nVALOR\nESTIMADO\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 2.4, " \nVALOR\nTOTAL\nESTIMADO\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $pdf->Cell(38, 12, " FORNECEDOR ", 1, 0, "C", 0);
    $pdf->Cell(26, 12, " MARCA ", 1, 0, "C", 0);
    $pdf->Cell(21, 12, " MODELO ", 1, 1, "C", 0);

    $ValorTotalItem = 0;
    $ValorTotal = 0;

    for ($itr = 0; $itr < $QuantidadeMateriais; $itr++) {
        $ValorTotalItem = moeda2float($materiais[$itr]["quantidade"]) * moeda2float($materiais[$itr]["valorEstimado"]);
        $ValorTotal += $ValorTotalItem;

        if (!$ocultarCampoExercicio) {
            $ValorTotalExercicio = moeda2float($materiais[$itr]["quantidadeExercicio"]) * moeda2float($materiais[$itr]["valorEstimado"]);
            $TotalDemaisExercicios = $ValorTotalItem - $ValorTotalExercicio;

            if ($TotalDemaisExercicios < 0) {
                $TotalDemaisExercicios = 0;
            }
        }

        $largura1 = ($exibeTd) ? '35' : '70';
        
        $h = 4;
        $hm = 0;
        $h1 = $pdf->GetStringHeight($largura1, $h, $materiais[$itr]["descricao"], "L");
        $h2 = $pdf->GetStringHeight($largura1, $h, $materiais[$itr]["descdetalhada"], "L");
        $h3 = $pdf->GetStringHeight(38, $h, $materiais[$itr]["fornecedorDesc"], "L");
        $h4 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["marca"], "L");
        $h5 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["modelo"], "L");

        $hm = $h1;
        if ($hm < $h2)
            $hm = $h2;
        if ($hm < $h3)
            $hm = $h3;
        if ($hm < $h4)
            $hm = $h4;
        if ($hm < $h5)
            $hm = $h5;

        $hLinhas = $hm / $h;

        if (($lin = $hLinhas - ($h1 / $h)) > 0)
            $materiais[$itr]["descricao"].= str_repeat("\n", $lin);
        if (($lin = $hLinhas - ($h2 / $h)) > 0)
            $materiais[$itr]["descdetalhada"].= str_repeat("\n", $lin);
        if (($lin = $hLinhas - ($h3 / $h)) > 0)
            $materiais[$itr]["fornecedorDesc"].= str_repeat("\n", $lin);
        if (($lin = $hLinhas - ($h4 / $h)) > 0)
            $materiais[$itr]["marca"].= str_repeat("\n", $lin);
        if (($lin = $hLinhas - ($h5 / $h)) > 0)
            $materiais[$itr]["modelo"].= str_repeat("\n", $lin);

        $h1 = $pdf->GetStringHeight($largura1, $h, $materiais[$itr]["descricao"], "L");
        $h2 = $pdf->GetStringHeight($largura1, $h, $materiais[$itr]["descdetalhada"], "L");
        $h3 = $pdf->GetStringHeight(38, $h, $materiais[$itr]["fornecedorDesc"], "L");
        $h4 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["marca"], "L");
        $h5 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["modelo"], "L");

        $hm = $h1;
        if ($hm < $h2)
            $hm = $h2;
        if ($hm < $h3)
            $hm = $h3;
        if ($hm < $h4)
            $hm = $h4;
        if ($hm < $h5)
            $hm = $h5;

        $h1 = $hm / ($h1 / $h);
        $h2 = $hm / ($h2 / $h);
        $h3 = $hm / ($h3 / $h);
        $h4 = $hm / ($h4 / $h);
        $h5 = $hm / ($h5 / $h);

        $pdf->Cell(8, $hm, ($itr + 1), 1, 0, "C", 0);

        $x = $pdf->GetX() + $largura1;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura1, $h1, $materiais[$itr]["descricao"], 1, "L", 0);
        $pdf->SetXY($x, $y);

        $pdf->Cell(8.5, $hm, $materiais[$itr]["codigo"], 1, 0, "C", 0);
        $pdf->Cell(8.5, $hm, $materiais[$itr]["unidade"], 1, 0, "C", 0);
        $pdf->Cell(20, $hm, $materiais[$itr]["quantidade"], 1, 0, "R", 0);

        $materiais[$itr]["descdetalhada"]   =  preg_replace("/\n/", "", $materiais[$itr]["descdetalhada"]);
        
        $valorDescDetalhada                 = $materiais[$itr]["descdetalhada"];
        $alinhamentoDescDetalhada           = "L";

        if (empty($valorDescDetalhada)) {
            $valorDescDetalhada         = "---";
            $alinhamentoDescDetalhada   = "C";
        }
        // var_dump($materiais[$itr]["descdetalhada"]);die;
        // Exibe quando houver material genérico
        if ($exibeTd) {
            $h1 = $pdf->GetStringHeight($largura1, $h, $materiais[$itr]["descricao"], "L");
            $h2 = $pdf->GetStringHeight($largura1, $h, $valorDescDetalhada, "L");
            $h3 = $pdf->GetStringHeight(38, $h, $materiais[$itr]["fornecedorDesc"], "L");
            $h4 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["marca"], "L");
            $h5 = $pdf->GetStringHeight(26, $h, $materiais[$itr]["modelo"], "L");

            $hm = $h1;
            if ($hm < $h2)
                $hm = $h2;
            if ($hm < $h3)
                $hm = $h3;
            if ($hm < $h4)
                $hm = $h4;
            if ($hm < $h5)
                $hm = $h5;

            $h1 = $hm / ($h1 / $h);
            $h2 = $hm / ($h2 / $h);
            $h3 = $hm / ($h3 / $h);
            $h4 = $hm / ($h4 / $h);
            $h5 = $hm / ($h5 / $h);

            $alturaDescDetalhada = (empty($valorDescDetalhada)) ? $hm : $h2;
            $arrayTirar  = array('.',',','-','/','--','–',';','ª','º','"');
            $valorDescDetalhada = str_replace($arrayTirar,'',$valorDescDetalhada);

            $x = $pdf->GetX() + $largura1;
            $y = $pdf->GetY();
            // var_dump($valorDescDetalhada);die;
            //osmar
            $pdf->MultiCell($largura1, $alturaDescDetalhada,$valorDescDetalhada, 1, "L", 0);
            $pdf->SetXY($x, $y);
        }

        $pdf->Cell(20, $hm, $materiais[$itr]["trp"], 1, 0, "R", 0);
        $pdf->Cell(20, $hm, $materiais[$itr]["ultimoPreco"], 1, 0, "R", 0);
        if(strlen(converte_valor_estoques($ValorTotalItem)) >= 14){
            $pdf->SetFont('Arial','',5.6);
        }
        $pdf->Cell(20, $hm, $materiais[$itr]["valorEstimado"], 1, 0, "R", 0);
        $pdf->Cell(20, $hm, converte_valor_estoques($ValorTotalItem), 1, 0, "R", 0);
        $pdf->SetFont('Arial','',6);
        $x = $pdf->GetX() + 38;
        $y = $pdf->GetY();
        $pdf->MultiCell(38, $h3, $materiais[$itr]["fornecedorDesc"], 1, "L", 0);
        $pdf->SetXY($x, $y);

        $x = $pdf->GetX() + 26;
        $y = $pdf->GetY();
        $pdf->MultiCell(26, $h4, $materiais[$itr]["marca"], 1, "L", 0);
        $pdf->SetXY($x, $y);

        $x = $pdf->GetX() + 26;
        $y = $pdf->GetY();
        $pdf->MultiCell(21, $h5, $materiais[$itr]["modelo"], 1, "L", 0);
    }

    $pdf->Cell(130, 5, " VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL ", 1, 0, "L", 0);
    $pdf->Cell(30, 5, converte_valor_estoques($ValorTotal), 'LTB', 0, "R", 0);
    $pdf->Cell(120, 5, '', 'TRB', 1, "L", 0);
}

# ITENS DA SOLICITAÇÃO DE SERVIÇO
$QuantidadeServicos = count($servicos);
if ($QuantidadeServicos != 0) {

    $pdf->Cell(280, 5, " ITENS DA SOLICITAÇÃO DE SERVIÇO ", 1, 1, "C", 1);

    $pdf->Cell(8, 12, " ORD. ", 1, 0, "C", 0);
    $pdf->Cell(65, 12, " DESCRIÇÃO ", 1, 0, "C", 0);

    $x = $pdf->GetX() + 8.5;
    $y = $pdf->GetY();
    $pdf->MultiCell(8.5, 3, " \nCÓD\nRED\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);
    $pdf->Cell(73.5, 12, " DESCRIÇÃO DETALHADA ", 1, 0, "C", 0);
    $pdf->Cell(20, 12, " QUANTIDADE ", 1, 0, "C", 0);

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 3, " \nVALOR\nESTIMADO\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);

    $x = $pdf->GetX() + 20;
    $y = $pdf->GetY();
    $pdf->MultiCell(20, 2.4, " \nVALOR\nTOTAL\nESTIMADO\n ", 1, "C", 0);
    $pdf->SetXY($x, $y);
    $pdf->Cell(65, 12, " FORNECEDOR ", 1, 1, "C", 0);
    $ValorTotalItem = 0;
    $ValorTotal = 0;

    for ($itr = 0; $itr < $QuantidadeServicos; $itr++) {
        $ValorTotalItem = moeda2float($servicos[$itr]["quantidade"]) * moeda2float($servicos[$itr]["valorEstimado"]);
        $ValorTotal += $ValorTotalItem;
        if (!$ocultarCampoExercicio) {
            $ValorTotalExercicio = moeda2float($servicos[$itr]["quantidadeExercicio"]) * moeda2float($servicos[$itr]["valorEstimado"]);
            $TotalDemaisExercicios = $ValorTotalItem - $ValorTotalExercicio;
            if ($TotalDemaisExercicios < 0) {
                $TotalDemaisExercicios = 0;
            }
        }



        $largura1 = 65;
        $largura2 = 73.5;

        $h = 4;
        $hm = 0;
        $h1 = $pdf->GetStringHeight($largura1, $h, $servicos[$itr]["descricao"], "L");
        $h2 = $pdf->GetStringHeight($largura2, $h, $servicos[$itr]["descricaoDetalhada"], "L");
        $h3 = $pdf->GetStringHeight(65, $h, $servicos[$itr]["fornecedorDesc"], "L");

        $hm = $h1;
        if ($hm < $h2)
            $hm = $h2;
        if ($hm < $h3)
            $hm = $h3;
        $hLinhas = $hm / $h;
        if (($hLinhas % 2 != 0) && ($hLinhas != 1))
            $hLinhas--;
        if (($lin = ($hLinhas - ($h1 / $h)) / 2) > 0)
            $servicos[$itr]["descricao"] = str_repeat("\n", (int) $lin) . $servicos[$itr]["descricao"] . str_repeat("\n", (int) $lin);
        if (($lin = ($hLinhas - ($h2 / $h)) / 2) > 0)
            $servicos[$itr]["descricaoDetalhada"] = str_repeat("\n", (int) $lin) . $servicos[$itr]["descricaoDetalhada"] . str_repeat("\n", (int) $lin);
        if (($lin = ($hLinhas - ($h3 / $h)) / 2) > 0)
            $servicos[$itr]["fornecedorDesc"] = str_repeat("\n", (int) $lin) . $servicos[$itr]["fornecedorDesc"] . str_repeat("\n", (int) $lin);

        $h1 = $pdf->GetStringHeight($largura1, $h, $servicos[$itr]["descricao"], "L");
        $h2 = $pdf->GetStringHeight($largura2, $h, $servicos[$itr]["descricaoDetalhada"], "L");
        $h3 = $pdf->GetStringHeight(65, $h, $servicos[$itr]["fornecedorDesc"], "L");

        $hm = $h1;
        if ($hm < $h2)
            $hm = $h2;
        if ($hm < $h3)
            $hm = $h3;

        $h1 = $hm / ($h1 / $h);
        $h2 = $hm / ($h2 / $h);
        $h3 = $hm / ($h3 / $h);

        $pdf->Cell(8, $hm, ($itr + 1), 1, 0, "C", 0);

        $x = $pdf->GetX() + $largura1;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura1, $h1, $servicos[$itr]["descricao"], 1, "L", 0);
        $pdf->SetXY($x, $y);
        $pdf->Cell(8.5, $hm, $servicos[$itr]["codigo"], 1, 0, "C", 0);
        $x = $pdf->GetX() + $largura2;
        $y = $pdf->GetY();
        $pdf->MultiCell($largura2
                , $h2, $servicos[$itr]["descricaoDetalhada"], 1, "L", 0);
        $pdf->SetXY($x, $y);
        if(strlen(converte_valor_estoques($ValorTotalItem)) >= 14){
            $pdf->SetFont('Arial','',5.6);
        }
        $pdf->Cell(20, ($hm), $servicos[$itr]["quantidade"], 1, 0, "C", 0);
        $pdf->Cell(20, ($hm), $servicos[$itr]["valorEstimado"], 1, 0, "C", 0);
        $pdf->Cell(20, ($hm), converte_valor_estoques($ValorTotalItem), 1, 0, "C", 0);
        $pdf->SetFont('Arial','',6);
        $x = $pdf->GetX() + 65;
        $y = $pdf->GetY();
        $pdf->MultiCell(65, $h3, $servicos[$itr]["fornecedorDesc"], 1, "L", 0);
    }

    $pdf->Cell(130, 5, " VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO ", 1, 0, "L", 0);
    $pdf->Cell(30, 5, converte_valor_estoques($ValorTotal), 'LTB', 0, "R", 0);
    $pdf->Cell(120, 5, '', 'TRB', 1, "L", 0);
}
# Histórico
$pdf->Cell(280, 5, " HISTÓRICO DA SITUAÇÃO DA SCC ", 1, 1, "C", 1);

$pdf->Cell(65, 8, " SITUAÇÃO ", 1, 0, "C", 0);
$pdf->Cell(30, 8, " DATA/HORA ", 1, 0, "C", 0);
$pdf->Cell(85, 8, " RESPONSÁVEL ", 1, 0, "C", 0);

$pdf->Cell(30, 8, " TELEFONE ", 1, 0, "C", 0);
$pdf->Cell(70, 8, " EMAIL ", 1, 1, "C", 0);

$QuantidadeHistorico = sizeof($historico);
if ($QuantidadeHistorico == 0) {
    $pdf->Cell(280, 5, "Nenhum histórico informado", 1, 1, "L", 0);
} else {
    for ($itr = 0; $itr < $QuantidadeHistorico; $itr++) {
        $h = 4;
        $hm = 0;
        $h1 = $pdf->GetStringHeight(85, $h, $historico[$itr]["nomeUsuario"], "C");
        $h2 = $pdf->GetStringHeight(70, $h, $historico[$itr]["emailUsuario"], "C");
        $hm = $h1;
        if ($hm < $h2)
            $hm = $h2;
        $h1 = $hm / ($h1 / $h);
        $h2 = $hm / ($h2 / $h);

        $pdf->Cell(65, $hm, $historico[$itr]["nomeSituacao"], 1, 0, "C", 0);
        $pdf->Cell(30, $hm, $historico[$itr]["dataSituacao"], 1, 0, "C", 0);

        $x = $pdf->GetX() + 85;
        $y = $pdf->GetY();
        $pdf->MultiCell(85, $h1, $historico[$itr]["nomeUsuario"], 1, "C", 0);
        $pdf->SetXY($x, $y);

        $pdf->Cell(30, $hm, $historico[$itr]["foneUsuario"], 1, 0, "C", 0);
        $pdf->Cell(70, $h2, $historico[$itr]["emailUsuario"], 1, 1, "C", 0);
    }
}

$dbOracle->disconnect();
$db->disconnect();
$pdf->Output();
