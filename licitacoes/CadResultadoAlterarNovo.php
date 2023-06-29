<?php
/**
 * Portal de Compras
 * 
 * Prograam: CadLote.php
 * Autor:    Gladstone Barbosa
 * Data:     19/03/2012
 * Objetivo: Manutenção de lotes de itens da solicitação
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Igor Duarte
 * Data:     06/08/2013
 * Objetivo: CR 31155
 *           Permitir que fornecedores com grupos diferentes de um determinado item possam fornecer esse item
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - José Francisco
 * Data:     27/05/2014
 * Objetivo: [CR123142]: REDMINE 22 (P5)
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Daniel Semblano
 * Data:     21/06/2014
 * Objetivo: [CR123142] REDMINE 22 (P5)
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - José Almir
 * Data:     21/06/2014
 * Objetivo: [CR123142] REDMINE 22 (P5)
 *           Correção fatal error do fetchRow()
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - José Almir
 * Data:     06/10/2014
 * Objetivo: [CR129585]: REDMINE 165
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: ???
 * Data:     08/11/2017
 * Objetivo: Tarefa Redmine 177622
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/07/2018
 * Objetivo: Tarefa Redmine 73631
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     13/07/2018
 * Objetivo: Tarefa Redmine 194552
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/07/2018
 * Objetivo: Tarefa Redmine 95900
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/12/2018
 * Objetivo: Tarefa Redmine 207786
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     30/01/2019
 * Objetivo: Tarefa Redmine 208508
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/05/2019
 * Objetivo: Tarefa Redmine 210696
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/06/2019
 * Objetivo: Tarefa Redmine 218111
 * ---------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     31/01/2023
 * Objetivo: Tarefa Redmine 278411
 * ---------------------------------------------------------------------------------------------------------------
 */

$programa = "CadResultadoAlterarNovo.php";

// Acesso ao arquivo de funções
require_once '../compras/funcoesCompras.php';

// incluindo funcoes de ajuda
require_once 'funcoesComplementaresLicitacao.php';
require_once 'funcoesLicitacoes.php';

// Executa o controle de segurança
session_start();
Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');
AddMenuAcesso('/compras/RotDadosFornecedor.php');
AddMenuAcesso('/licitacoes/CadResultadoExcluir.php');
AddMenuAcesso('/licitacoes/CadResultadoSelecionar.php');

// Variáveis para teste
// correto é false. se true, permite inclusão de fornecedores que não passaram na checagem do cadastro mercantil
$desabilitarChecagemFornecedorSistemaMercantil = false;

// Abrindo Conexão
$db = Conexao();
$dbOracle = ConexaoOracle();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $intCodUsuario        = $_SESSION['_cusupocodi_'];
    $perfilCorporativo    = $_SESSION['_fperficorp_'];
    $Processo             = $_POST['Processo'];
    $ProcessoAno          = $_POST['ProcessoAno'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $Grupo                = $_POST['Grupo'];
    $verificarExclusivo   = $_POST['verificarExclusivo'];

    if ($Botao != 'CarregarRascunho') {
        $arrTipo                  = $_POST["arrTipo"];
        $arrQuantidadeExercicio   = $_POST["arrQuantidadeExercicio"]; // Retirar
        $arrQuantidade            = $_POST["arrQuantidade"];
        $arrCodRed                = $_POST["arrCodRed"];
        $arrValorLogrado          = $_POST['arrValorLogrado'];
        $arrValorEstimado         = $_POST['arrValorEstimado'];
        $arrValorLogradoExercicio = $_POST['arrValorLogradoExercicio']; // Retirar
        $arrCpfCnpj               = $_POST['arrCpfCnpj'];
        $arrCpfCnpjLote           = $_POST['arrCpfCnpjLote'];
        $arrSequencial            = $_POST['arrSequencial'];
        $arrMarca                 = $_POST['arrMarca'];
        $arrModelo                = $_POST['arrModelo'];
        $arrMotivos               = $_POST['arrMotivos'];
        $arrDotacaoBloqueio       = $_POST['dotacaoBloqueio'];
        $arrDescricaoDetalhada    = filter_input(INPUT_POST, 'arrDescricaoDetalhada');
    }

    // Bloqueio
    $Bloqueios         = $_POST['Bloqueios'];
    $BloqueiosCheck    = $_POST['BloqueiosCheck'];
    $BloqueioAno       = $_POST['BloqueioAno'];
    $BloqueioOrgao     = $_POST['BloqueioOrgao'];
    $BloqueioUnidade   = $_POST['BloqueioUnidade'];
    $BloqueioDestinacao = $_POST['BloqueioDestinacao'];
    $BloqueioSequencial = $_POST['BloqueioSequencial'];
    
    // Dotação
    $DotacaoAno                  = $_POST['DotacaoAno'];
    $DotacaoOrgao                = $_POST['DotacaoOrgao'];
    $DotacaoUnidade              = $_POST['DotacaoUnidade'];
    $DotacaoFuncao               = $_POST['DotacaoFuncao'];
    $DotacaoSubfuncao            = $_POST['DotacaoSubfuncao'];
    $DotacaoPrograma             = $_POST['DotacaoPrograma'];
    $DotacaoTipoProjetoAtividade = $_POST['DotacaoTipoProjetoAtividade'];
    $DotacaoProjetoAtividade     = $_POST['DotacaoProjetoAtividade'];
    $DotacaoElemento1            = $_POST['DotacaoElemento1'];
    $DotacaoElemento2            = $_POST['DotacaoElemento2'];
    $DotacaoElemento3            = $_POST['DotacaoElemento3'];
    $DotacaoElemento4            = $_POST['DotacaoElemento4'];
    $DotacaoFonte                = $_POST['DotacaoFonte'];
} else {
    if ((! isset($_GET['Processo'])) || (! isset($_GET['ProcessoAno'])) || (! isset($_GET['ComissaoCodigo'])) || (! isset($_GET['OrgaoLicitanteCodigo'])) || (! isset($_GET['Grupo']))) {
        $Mensagem = urlencode("Alguns dados não foram enviador corretamente para o programa.");
        $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2&Critica=0";

        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }

        header("location: " . $Url);
        exit();
    }

    $Processo             = $_GET['Processo'];
    $ProcessoAno          = $_GET['ProcessoAno'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    $Grupo                = $_GET['Grupo'];

    /* Fazendo calculo para edição ou não dos valores de exercício */
    $sql  = "SELECT  COUNT(ITEM.CITELPSEQU) AS QTD_ITENS ";
    $sql .= "FROM    SFPC.TBITEMLICITACAOPORTAL ITEM ";
    $sql .= "WHERE   ITEM.clicpoproc = $Processo ";
    $sql .= "       AND ITEM.alicpoanop = $ProcessoAno ";
    $sql .= "       AND ITEM.cgrempcodi = " . $Grupo . " ";
    $sql .= "       AND ITEM.ccomlicodi = $ComissaoCodigo ";
    $sql .= "       AND ITEM.corglicodi = $OrgaoLicitanteCodigo ";
    $sql .= "       AND ITEM.citelpnuml IS NOT NULL ";

    $res = $db->query($sql);

    $linha = resultLinhaUnica($res);

    if ($linha[0] <= 0) {
        $Mensagem = urlencode("Deve ser informado o número de lotes para os itens das Licitação");
        $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2&Critica=0";

        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }

        header("location: " . $Url);
        exit();
    }
}

// Verificar se carrega rascunho ou não
$carregarRascunho = false;

if ($Botao == 'CarregarRascunho') {
    $carregarRascunho = true;
}

// Identifica o Programa para Erro de Banco de Dados
$ErroPrograma = __FILE__;

$db = Conexao();

$sql  = "SELECT  LIC.CCOMLICODI, COM.ECOMLIDESC, LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CMODLICODI, ";
$sql .= "       MOD.EMODLIDESC, LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, ";
$sql .= "       LIC.CORGLICODI, LIC.FLICPOVFOR ";
$sql .= "FROM    SFPC.TBLICITACAOPORTAL LIC ";
$sql .= "       INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI ";
$sql .= "       INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI ";
$sql .= "WHERE   LIC.CLICPOPROC = $Processo ";
$sql .= "       AND LIC.ALICPOANOP = $ProcessoAno ";
$sql .= "       AND LIC.CCOMLICODI = $ComissaoCodigo ";
$sql .= "       AND LIC.corglicodi = $OrgaoLicitanteCodigo ";
$sql .= "ORDER BY LIC.CCOMLICODI ASC ";

$res = $db->query($sql);

if (PEAR::isError($res)) {
    $CodErroEmail = $res->getCode();
    $DescErroEmail = $res->getMessage();
    
    var_export($DescErroEmail);
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
} else {
    $Linha = $res->fetchRow();
}

$validacaoFornecedor = $Linha[11];

if (isset($Linha[6]) && $Linha[6] != "") {
    if ($Linha[6] == "S") {
        $RegistroPreco = true;
    } elseif ($Linha[6] == "N") {
        $RegistroPreco = false;
    }
    
    $isDotacao = ($RegistroPreco == 'S') ? true : false;
}

// Buscando e carregando array com as solicitacoes da licitacao
$sqlSolicitacoes  = "SELECT  CSOLCOSEQU, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
$sqlSolicitacoes .= "        CORGLICODI ";
$sqlSolicitacoes .= "FROM    SFPC.TBSOLICITACAOLICITACAOPORTAL SOL ";
$sqlSolicitacoes .= "WHERE   SOL.CLICPOPROC = " . $Processo;
$sqlSolicitacoes .= "        AND SOL.ALICPOANOP = " . $ProcessoAno;
$sqlSolicitacoes .= "        AND SOL.CCOMLICODI = " . $ComissaoCodigo;
$sqlSolicitacoes .= "        AND SOL.CORGLICODI = " . $OrgaoLicitanteCodigo;
$sqlSolicitacoes .= "        AND SOL.CGREMPCODI = " . $Grupo;

$resultSoli = $db->query($sqlSolicitacoes);

$qtdSolicitacoes = $resultSoli->numRows();

if ($qtdSolicitacoes > 1) {
    $isAgrupamento = true;
} else {
    $isAgrupamento = false;
}

if (PEAR::isError($resultSoli)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSolicitacoes");
}

while ($LinhaSoli = $resultSoli->fetchRow()) {
    $arrSolicitacoes[] = $LinhaSoli[0];
}

/**
 * DOTAÇÃO E BLOQUIEO
 */
if ($RegistroPreco) {
    // Faco a busca pelos campos de Dotação AITCDOUNIDOEXER CITCDOUNIDOORGA CITCDOUNIDOCODI CITCDOTIPA AITCDOORDT CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
    $sql  = "SELECT  DISTINCT AITLDOUNIDOEXER, CITLDOUNIDOORGA, CITLDOUNIDOCODI, CITLDOTIPA, AITLDOORDT, ";
    $sql .= "        CITLDOELE1, CITLDOELE2, CITLDOELE3, CITLDOELE4, CITLDOFONT ";
    $sql .= "FROM    SFPC.TBITEMLICITACAODOTACAO ";
    $sql .= "WHERE   CLICPOPROC = " . $Processo;
    $sql .= "        AND ALICPOANOP = " . $ProcessoAno;
    $sql .= "        AND CGREMPCODI = " . $Grupo;
    $sql .= "        AND CCOMLICODI = " . $ComissaoCodigo;
    $sql .= "        AND CORGLICODI = " . $OrgaoLicitanteCodigo;

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();

        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $valorTotalDisponivel = 0;

        while ($linha = $res->fetchRow()) {
            $dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle, $linha[0], $linha[1], $linha[2], $linha[3], $linha[4], $linha[5], $linha[6], $linha[7], $linha[8], $linha[9]);
            $valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
        }
    }
} else {
    // Faco a busca pelos campos de Bloqueio
    $sql  = "SELECT  DISTINCT AITLBLNBLOQ , AITLBLANOB ";
    $sql .= "FROM    SFPC.TBITEMLICITACAOBLOQUEIO ";
    $sql .= "WHERE   CLICPOPROC = " . $Processo;
    $sql .= "       AND ALICPOANOP = " . $ProcessoAno;
    $sql .= "       AND CGREMPCODI = " . $Grupo;
    $sql .= "       AND CCOMLICODI = " . $ComissaoCodigo;
    $sql .= "       AND CORGLICODI = " . $OrgaoLicitanteCodigo;

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();

        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $valorDotacaoBloqueio = array();
        $valorDotacaoBloqueioItem = array();

        while ($linha = $res->fetchRow()) {
            $dotacaoArray = getDadosBloqueioFromChave($dbOracle, $linha[1], $linha[0]);
            $valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
        }
    }
}

/**
 * DOTAÇÃO E BLOQUEIO
 */
/* BUSCANDO OS ITENS DA LICITACAO */
if ($carregarRascunho) {
    $itensLicitacao  = listaItensLicitacaoRascunho($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
} else {
    $itensLicitacao = listaItensLicitacao($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
}

// Verificar se a licitação foi 'fechada'
$licitacaoFechada = verificarLicitacao($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);

$resIL              = $itensLicitacao;
$resILTmp           = $itensLicitacao;
$resItensLicitacao  = $itensLicitacao;
$intQuantidadeItens = $resItensLicitacao->numRows();


/* Consulta lista de motivo não logrado */
$resMotivo       = listaMotivosNaoLogrado();
$arrMotivosLista = array();

while ($listaMotivos = $resMotivo->fetchRow()) {
    $arrMotivosLista[$listaMotivos[0]] = $listaMotivos[1];
}

if ($Botao == "Voltar") {
    $Url = "CadResultadoSelecionar.php";

    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header("location: $Url");
    exit();
} elseif($Botao == "SalvarRascunho" ) {
    $itensLicitacao = itensLicitacao($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
    $rascunho       = verificarRascunho($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);

    // Verificar se tem rascunho cadastrado
    if (!$rascunho) {
        while ($item = $itensLicitacao->fetchRow()) {
            $itemRascunho = ajustarColunasInsertRascunho($item);

            $sqlInsert  = "INSERT INTO SFPC.TBITEMLICITACAORASCUNHO ( ";
            $sqlInsert .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI, ";
            $sqlInsert .= "CITELRSEQU, CMATEPSEQU, CSERVPSEQU, AITELRORDE, AITELRQTSO, ";
            $sqlInsert .= "VITELRUNIT, CMOTNLSEQU, VITELRVLOG, VITELRVEXE, AITELRQTEX, ";
            $sqlInsert .= "CITELRNUML, AFORCRSEQU, EITELRMARC, EITELRMODE, CUSUPOCODI, ";
            $sqlInsert .= "TITELRULAT, FITELRLOGR, EITELRDESCMAT, EITELRDESCSE ";
            $sqlInsert .= ") VALUES (" . implode(',', $itemRascunho) . "); ";

            $res = executarTransacao($db, $sqlInsert);

            if (PEAR::isError($res)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                $Botao = "";
            }
        }
    }

    // Atualizar os itens
    $seqItens               = $_POST['arrSequencial'];
    $arrValorLogrado        = $_POST['arrValorLogrado'];
    $arrMarca               = $_POST['arrMarca'];
    $arrModelo              = $_POST['arrModelo'];
    $arrMotivos             = $_POST['arrMotivos'];
    $arrSequecialFornecedor = $_POST['arrSequecialFornecedor'];
    $arrCpfCnpjLote         = $_POST['arrCpfCnpjLote'];
    $arrCpfCnpj             = $_POST['arrCpfCnpj'];

    foreach ($seqItens as $key => $value) {
        $flag                = ($arrMotivos[$key] == "") ? "'S'" : 'NULL';
        $ValorLogrado        = temValor($arrValorLogrado[$key]) ? moeda2float($arrValorLogrado[$key]) : 'NULL';
        $Marca               = (temValor($arrMarca[$key])) ? "'" . $arrMarca[$key] . "'" : 'NULL';
        $Marca               = strtoupper($Marca);
        $Modelo              = ($arrModelo[$key] != "") ? "'" . $arrModelo[$key] . "'" : 'NULL';
        $Modelo              = strtoupper($Modelo);
        $Motivos             = ($arrMotivos[$key] != "") ? $arrMotivos[$key] : 'NULL';
        $lote                = $arrCpfCnpj[$key];
        $SequecialFornecedor = getSequencialFromCpfCnpj($db, removeSimbolos($arrCpfCnpjLote[$lote]));
        $SequecialFornecedor = !empty($SequecialFornecedor) ? $SequecialFornecedor : 'NULL';

        $atualizarItem  = "UPDATE   SFPC.TBITEMLICITACAORASCUNHO ";
        $atualizarItem .= "SET      CMOTNLSEQU = " . $Motivos . ", ";
        $atualizarItem .= "         VITELRVLOG = " . $ValorLogrado . ", ";
        $atualizarItem .= "         AFORCRSEQU = " . $SequecialFornecedor . ", ";
        $atualizarItem .= "         EITELRMARC = " . $Marca . ", ";
        $atualizarItem .= "         EITELRMODE = " . $Modelo . ", ";
        $atualizarItem .= "         CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . " , ";
        $atualizarItem .= "         TITELRULAT =  now(), ";
        $atualizarItem .= "         FITELRLOGR = " . $flag;
        $atualizarItem .= " WHERE    CLICPOPROC = " . $Processo;
        $atualizarItem .= "         AND ALICPOANOP = " . $ProcessoAno;
        $atualizarItem .= "         AND CGREMPCODI = " . $Grupo;
        $atualizarItem .= "         AND CCOMLICODI = " . $ComissaoCodigo;
        $atualizarItem .= "         AND CORGLICODI = " . $OrgaoLicitanteCodigo;
        $atualizarItem .= "         AND CITELRSEQU = " . $value;

        $res = executarTransacao($db, $atualizarItem);

        if (PEAR::isError($res)) {
            cancelarTransacao($db);
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            $Botao = "";
        }
    }

    finalizarTransacao($db);

    // Envia mensagem para página selecionar
    $Mensagem = urlencode("Rascunho salvo com sucesso");
    $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";

    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header("location: $Url");
    exit();
} elseif ($Botao == "Totalizar" || $Botao == "ExibirMapaResumo" || $Botao == "ConfirmarResultado") {

    if ($_SESSION['_cperficodi_'] != 2) {
        // Validando se a licitacao já foi homologado
        $sql  = "SELECT COUNT(CFASESCODI) ";
        $sql .= "FROM   SFPC.TBFASELICITACAO ";
        $sql .= "WHERE  CFASESCODI = 13 ";
        $sql .= "       AND CLICPOPROC = " . $Processo;
        $sql .= "       AND ALICPOANOP = " . $ProcessoAno;
        $sql .= "       AND CGREMPCODI = " . $Grupo;
        $sql .= "       AND CCOMLICODI = " . $ComissaoCodigo;
        $sql .= "       AND CORGLICODI = " . $OrgaoLicitanteCodigo;

        $res = $db->query($sql);

        $linha = resultLinhaUnica($res);

        if ($linha[0] > 0) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Não é possível alterar esta licitação, pois a mesma já foi homologada"; // here
        }
    }

    // Validar se cnpj caso seja exclusivo
    $exclusivo = $_POST['verificarExclusivo'];

    if (($Botao == "Totalizar" || $Botao == "ExibirMapaResumo") && $exclusivo) {
        $arrCpfCnpjLote = $_POST['arrCpfCnpjLote'];

        foreach ($arrCpfCnpjLote as $key => $value) {
            if(!empty($value)) {
                $fornAtual = tipoCadastroFornecedor(RemoveFormatoCPF_CNPJ($value));

                if ($tipoCadastro == TIPO_CADASTRO_CPF) {
                    $isCnpj = false;
                } else {
                    $isCnpj = true;
                }

                $sql  = "SELECT  F.FFORCRMEPP, F.NFORCRRAZS ";
                $sql .= "FROM   SFPC.TBFORNECEDORCREDENCIADO F ";
                $sql .= "WHERE ";

                    if ($isCnpj) {
                        $sql .= " AFORCRCCGC = '" . RemoveFormatoCPF_CNPJ($value) . "'";
                    } else {
                        $sql .= " AFORCRCCPF = '" . RemoveFormatoCPF_CNPJ($value) . "'";
                    }

                    $veificarFornecedor = resultLinhaUnica(executarSQL($db, $sql));

                    if (empty($veificarFornecedor)) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpjLote[$key]').focus();\" class='titulo2'>Fornecedor do lote ($key) não cadastrado ou não está no grupo reservado</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    } elseif(!in_array($veificarFornecedor[0], array(1, 2, 3))) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpjLote[$key]').focus();\" class='titulo2'>O Fornecedor ".$veificarFornecedor[1]." não está registrado no SICREF como ME, EPP ou MEI.</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                }
            }
        }

        // Validacões de Itens
        foreach ($arrCodRed as $key => $codRed) {
            if ($arrTipo[$key] == "CADUM") {
                $arrTipoCod[$key] = TIPO_ITEM_MATERIAL;
            } elseif ($arrTipo[$key] == "CADUS") {
                $arrTipoCod[$key] = TIPO_ITEM_SERVICO;
            } else {
                adicionarMensagem("<a href=\"javascript:void(0);\" class='titulo2'>O tipo do item $key não foi definido </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        
            // Caso tenha um motivo selecionado para o item, todos os outros campos nao podem ser preechidos
            if ($arrMotivos[$key] != "") {
                if ((temValor($arrValorLogrado[$key])) || (temValor($arrMarca[$key])) || (temValor($arrModelo[$key]))) {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$key]').focus();\" class='titulo2'>O preenchimento do motivo não logrado só é permitido quando as demais informações do item são nulas no item($key).</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            } else {
                // Caso contrario , todos os outros tem que ser preechidos
                if (! temValor($arrValorLogrado[$key])) {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$key]').focus();\" class='titulo2'>Valor Logrado inválido no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }

                if (! temValor($arrMarca[$key])) {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('arrMarca[$key]').focus();\" class='titulo2'>Marca inválida no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }

                if (! temValor($arrModelo[$key])) {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('arrModelo[$key]').focus();\" class='titulo2'>Modelo inválido no item($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }

                if (! temValor($arrCpfCnpjLote[$arrCpfCnpj[$key]])) {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpjLote[$arrCpfCnpj[$key]]').focus();\" class='titulo2'>Fornecedor inválido no lote($arrCpfCnpj[$key])</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                } else {
                    /*
                     * ESSA ALTERAÇÃO FOI FEITA PARA OBRIGATORIAMENTE GRAVAR QUALQUER FORNECEDOR NOS LOTES
                     * E SEUS RESPECTIVOS ITENS FOI PEDIDO PELA EMPREL NO DIA 05/06/2013
                     *
                     * INICIALMENTE ELE ESTAVA NO 'ELSE' SUBSEQUENTE A ESTE COMENTARIO
                     */
                    $arrSequecialFornecedor[$key] = getSequencialFromCpfCnpj($db, removeSimbolos($arrCpfCnpjLote[$arrCpfCnpj[$key]]));
                    $dadosSituacaoFornecedor = checaSituacaoFornecedor($db, $arrCpfCnpjLote[$arrCpfCnpj[$key]]);

                    // [CUSTOMIZAÇÃO] - [CR129585]: REDMINE 165
                    if (! empty($arrSequecialFornecedor[$key])) {
                        $dadosFornecedor = getFornecedor($db, $arrSequecialFornecedor[$key]);
                    }
                    // [/CUSTOMIZAÇÃO]

                    // verificações caso "Necessidade de apresentação de demonstrações contábeis" = "S"
                    if ($validacaoFornecedor == "S") {
                        if ($dadosSituacaoFornecedor["inabilitadoPorDataBalancoVencida"]) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpj[$arrCpfCnpj[$key]]').focus();\" class='titulo2'> Fornecedor '" . $dadosFornecedor['razaoSocial'] . "' com Data de Balanço vencida no Lote ($arrCpfCnpj[$key])</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }

                        if ($dadosFornecedor["patrimonioLiquido"] == 0 or is_null($dadosFornecedor["patrimonioLiquido"])) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpj[$arrCpfCnpj[$key]]').focus();\" class='titulo2'> Fornecedor '" . $dadosFornecedor['razaoSocial'] . "' sem Patrimônio Líquido no Lote ($arrCpfCnpj[$key])</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }
                    }

                    if ($dadosSituacaoFornecedor["situacao"] != 1) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpj[$arrCpfCnpj[$key]]').focus();\" class='titulo2'> Fornecedor '" . $dadosFornecedor['razaoSocial'] . "' com sanções no SICREF.</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }

                    if (($dadosSituacaoFornecedor["erro"] != 0 or $dadosSituacaoFornecedor["inabilitado"]) and ! ($desabilitarChecagemFornecedorSistemaMercantil and $dadosSituacaoFornecedor["inabilitadoPorDebitoCadastroMercantil"])) {
                        /* FOI SOLICITADO PELA EMPREL PARA QUE NÃO SEJA FEITO NADA QUANDO O PROGRAMA ENTRAR NESSE CONDICIONAL 22/02/2013 */
                        /*
                        * if (!($desabilitarChecagemFornecedorSistemaMercantil and $dadosSituacaoFornecedor["inabilitadoPorDebitoCadastroMercantil"]) and $codigoResposta==$GLOBALS["RETORNO_CADASTRO_IMOBILIARIO_ARQUIVO_NAO_ENCONTRADO"]) {
                        * // ignorar erro caso esteja desabilitado checagem no cadastro mercantil
                        * mostrarMensagemErroUnica($dadosSituacaoFornecedor["mensagem"]);
                        * } else {
                        * adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpj[$arrCpfCnpj[$key]]').focus();\" class='titulo2'> '".$dadosSituacaoFornecedor["mensagem"]."' no Item ($key)</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        * }
                        */
                        if ($dadosSituacaoFornecedor["erro"] >= 1 && $dadosSituacaoFornecedor["erro"] <= 4) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('arrCpfCnpj[$arrCpfCnpj[$key]]').focus();\" class='titulo2'> '" . $dadosSituacaoFornecedor["mensagem"] . "' no Lote ($arrCpfCnpj[$key])</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }
                    } else {
                        // verificar se o fornecedor fornece o grupo ao qual o material pertence
                        // $arrSequecialFornecedor[$key] = getSequencialFromCpfCnpj($db, removeSimbolos($arrCpfCnpjLote[$arrCpfCnpj[$key]]));
                        $mesmoGrupo = forneceMaterialServico($db, $arrSequecialFornecedor[$key], $arrCodRed[$key], $arrTipoCod[$key]);

                        if ($dadosSituacaoFornecedor['tipo'] == 'E') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Fornecedor é do tipo Estoques e não pode fazer solicitação de compra' em fornecedor do lote " . ($arrCpfCnpj[$key]) . ", material ord " . ($key) . "</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } elseif ($TipoCompra != $TIPO_COMPRA_DIRETA and $dadosSituacaoFornecedor['tipo'] == 'D') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Fornecedor é do tipo Compra Direta e não pode fazer solicitação de compra que não seja Direta' em fornecedor do lote " . ($arrCpfCnpj[$key]) . ", material ord " . ($key) . "</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }
                        /*
                        * VALIDAÇÃO RETIRADA NA CR #31155
                        * PERMITIR QUE FORNECEDORES COM GRUPOS DIFERENTES POSSAM FORNECER ITENS [MAT/SERV] DO GRUPO DE ORIGEM DO MESMO
                        * elseif (!$mesmoGrupo) {
                        * adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[".$pos."]').focus();\" class='titulo2'> 'Fornecedor com grupo diferente de material' em fornecedor do lote ".($arrCpfCnpj[$key]).", material ord ".($key)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        * }
                        */
                    }
                }

                // +- ['posicaoArray'] - posição do item na lista de itens da scc, separadas por material ou serviço
                // +- ['posicao'] - posição do item no ARRAY (no formulario HTML) dos itens da scc, separadas por material ou serviço
                // +- ['codigo'] - número do material ou serviço
                // +- ['tipo'] - se o item é material ou serviço. Usar as constantes TIPO_ITEM_MATERIAL e TIPO_ITEM_SERVICO.
                // +- ['quantidadeItem'] - quantidade do item
                // +- ['valorItem'] - valor do item
                // +- ['reservas'] - array com números dos bloqueios ou dotações, do item.
                // Montando array de itens para usar a funcao de validacao de bloqueio e dotacao

                $arrayValiDotacaoBloqueio[$key]["posicaoItem"] = $key;
                $arrayValiDotacaoBloqueio[$key]["posicao"] = $key;
                $arrayValiDotacaoBloqueio[$key]["codigo"] = $arrCodRed[$key];
                $arrayValiDotacaoBloqueio[$key]["tipo"] = $arrTipoCod[$key];
                $arrayValiDotacaoBloqueio[$key]["quantidadeItem"] = $arrQuantidade[$key];
                $arrayValiDotacaoBloqueio[$key]["valorItem"] = $arrValorLogrado[$key];
                $arrayValiDotacaoBloqueio[$key]["reservas"] = $arrDotacaoBloqueio;
            }
        }

        // Fazendo a validacao de bloqueio e dotacao
        if ($RegistroPreco) {
            $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_DOTACAO;
            $isDotacao = true;
        } else {
            $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO;
            $isDotacao = false;
        }

        try {
            if (!is_null($arrayValiDotacaoBloqueio) && count($arrDotacaoBloqueio) > 0) {
                validarReservaOrcamentariaBKS($db, $dbOracle, $tipoReserva, $arrDotacaoBloqueio, $arrayValiDotacaoBloqueio, "selectDotBloq");
            }
        } catch (ExcecaoReservaInvalidaEmItemScc $e) {
            $pos = $e->posicaoItemArray;

            adicionarMensagem("<a href=\"javascript:document.getElementById('arrValorLogrado[$pos]').focus();\" class='titulo2'>" . $e->getMessage() . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        } catch (ExcecaoPendenciasUsuario $e) {
            adicionarMensagem($e->getMessage(), $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if ($Mens == 0) {
            if ($Botao == "ConfirmarResultado") {
                $db->query("BEGIN TRANSACTION");

                // Atualizando a licitacao
                $sql  = "UPDATE SFPC.TBLICITACAOPORTAL ";
                $sql .= "SET    FLICPORESU = 'S', ";
                $sql .= "       CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ", ";
                $sql .= "       TLICPOULAT = now() ";
                $sql .= "WHERE  CLICPOPROC = " . $Processo;
                $sql .= "       AND ALICPOANOP = " . $ProcessoAno;
                $sql .= "       AND CCOMLICODI = " . $ComissaoCodigo;
                $sql .= "       AND CORGLICODI = " . $OrgaoLicitanteCodigo;

                $res = executarTransacao($db, $sql);

                if (PEAR::isError($res)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }

                // Removendo as dotacoes e bloqueios
                $sql  = "DELETE FROM SFPC.TBITEMLICITACAODOTACAO ITEM ";
                $sql .= "WHERE  ITEM.CLICPOPROC = " . $Processo;
                $sql .= "       AND ITEM.ALICPOANOP = " . $ProcessoAno;
                $sql .= "       AND ITEM.CGREMPCODI = " . $Grupo;
                $sql .= "       AND ITEM.CCOMLICODI = " . $ComissaoCodigo;
                $sql .= "       AND ITEM.CORGLICODI = " . $OrgaoLicitanteCodigo;

            $res = executarTransacao($db, $sql);
            if (PEAR::isError($res)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }
            
            $sql  = "DELETE FROM SFPC.TBITEMLICITACAOBLOQUEIO ITEM ";
            $sql .= "WHERE  ITEM.CLICPOPROC = " . $Processo;
            $sql .= "       AND ITEM.ALICPOANOP = " . $ProcessoAno;
            $sql .= "       AND ITEM.CGREMPCODI = " . $Grupo;
            $sql .= "       AND ITEM.CCOMLICODI = " . $ComissaoCodigo;
            $sql .= "       AND ITEM.CORGLICODI = " . $OrgaoLicitanteCodigo;

            $res = executarTransacao($db, $sql);

            if (PEAR::isError($res)) {
                cancelarTransacao($db);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }

            foreach ($arrCodRed as $key => $codRed) {
                $flag = ($arrMotivos[$key] == "") ? "'S'" : 'NULL';

                $ValorLogrado = temValor($arrValorLogrado[$key]) ? moeda2float($arrValorLogrado[$key]) : 'NULL';
                $Marca        = (temValor($arrMarca[$key])) ? "'" . $arrMarca[$key] . "'" : 'NULL';
                $Marca        = strtoupper($Marca);
                $Modelo       = ($arrModelo[$key] != "") ? "'" . $arrModelo[$key] . "'" : 'NULL';
                $Modelo       = strtoupper($Modelo);
                $Motivos      = ($arrMotivos[$key] != "") ? $arrMotivos[$key] : 'NULL';

                $SequecialFornecedor = ($arrSequecialFornecedor[$key] != "") ? $arrSequecialFornecedor[$key] : 'NULL';

                // Gravar os dados em SFPC.TBITEMLICITACAOPORTAL
                $sql  = "UPDATE SFPC.TBITEMLICITACAOPORTAL ";
                $sql .= "SET    CMOTNLSEQU = " . $Motivos . ", ";
                $sql .= "       VITELPVLOG = " . $ValorLogrado . ", ";
                $sql .= "       AFORCRSEQU = " . $SequecialFornecedor . ", ";
                $sql .= "       EITELPMARC = " . $Marca . ", ";
                $sql .= "       EITELPMODE = " . $Modelo . ", ";
                $sql .= "       CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ", ";
                $sql .= "       TITELPULAT = now(), ";
                $sql .= "       FITELPLOGR = " . $flag;
                $sql .= " WHERE  CLICPOPROC = " . $Processo;
                $sql .= "       AND ALICPOANOP = " . $ProcessoAno;
                $sql .= "       AND CGREMPCODI = " . $Grupo;
                $sql .= "       AND CCOMLICODI = " . $ComissaoCodigo;
                $sql .= "       AND CORGLICODI = " . $OrgaoLicitanteCodigo;
                $sql .= "       AND CITELPSEQU = " . $arrSequencial[$key];

                $res = executarTransacao($db, $sql);

                if (PEAR::isError($res)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }

                if ($RegistroPreco == "S") {
                    if (is_array($arrDotacaoBloqueio) || is_object($arrDotacaoBloqueio)) {
                        foreach ($arrDotacaoBloqueio as $strDotacaco) {
                            $arrayInfoDotacao = getDadosDotacaoOrcamentaria($dbOracle, $strDotacaco);
                            $sqlDotacao  = "INSERT INTO SFPC.TBITEMLICITACAODOTACAO (";
                            $sqlDotacao .= "CLICPOPROC, "; // clicpoproc IS 'Código do Processo Licitatório';
                            $sqlDotacao .= "ALICPOANOP, "; // alicpoanop IS 'Ano do Processo Licitatório';
                            $sqlDotacao .= "CGREMPCODI, "; // cgrempcodi IS 'Código do Grupo';
                            $sqlDotacao .= "CCOMLICODI, "; // ccomlicodi IS 'Código da Comissão';
                            $sqlDotacao .= "CORGLICODI, "; // corglicodi IS 'Código do Órgão Licitante';
                            $sqlDotacao .= "CITELPSEQU, "; // código sequencial dos itens da licitação';
                            $sqlDotacao .= "CITLDOUNIDOORGA, "; // citldounidoorga IS Código do Órgão do orçamento';
                            $sqlDotacao .= "CITLDOUNIDOCODI, "; // citldounidocodi IS Código da Unidade Orçamentária';
                            $sqlDotacao .= "CITLDOTIPA, "; // citldo IS Tipo ( 1 = Projeto 2= Atividade )
                            $sqlDotacao .= "AITLDOORDT, "; // aitldoordt IS Ordem do Projeto ou da Atividade';
                            $sqlDotacao .= "CITLDOELE1, "; // citldoele1 IS Elemento de Despesa 1 ';
                            $sqlDotacao .= "CITLDOELE2, "; // citldoele2 IS Elemento de Despesa 2 ';
                            $sqlDotacao .= "CITLDOELE3, "; // citldoele3 IS Elemento de Despesa 3 ';
                            $sqlDotacao .= "CITLDOELE4, "; // citldoele4 IS Elemento de Despesa 4 ';
                            $sqlDotacao .= "CITLDOFONT, "; // citldofont IS Fonte de Recursos';
                            $sqlDotacao .= "AITLDOUNIDOEXER "; // aitldounidoexer IS Ano da Unidade Orçamentária';
                            $sqlDotacao .= ") VALUES (";
                            $sqlDotacao .= "$Processo, ";
                            $sqlDotacao .= "$ProcessoAno, ";
                            $sqlDotacao .= $Grupo . ",";
                            $sqlDotacao .= "$ComissaoCodigo, ";
                            $sqlDotacao .= "$OrgaoLicitanteCodigo, ";
                            $sqlDotacao .= "$key, ";
                            $sqlDotacao .= $arrayInfoDotacao['orgao'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['unidade'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['tipoProjetoAtividade'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['projetoAtividade'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['elemento1'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['elemento2'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['elemento3'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['elemento4'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['fonte'] . " , ";
                            $sqlDotacao .= $arrayInfoDotacao['ano'] . " ) ";

                            $resItensDotacao = executarTransacao($db, $sqlDotacao);

                            if (PEAR::isError($resItensDotacao)) {
                                $transacao = false;
                                cancelarTransacao($db);
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlDotacao\n\n$DescErroEmail ($CodErroEmail)");
                            }
                        }
                    }
                } else {
                    foreach ($arrDotacaoBloqueio as $strBloqueio) {
                        $arryInfoBloqueio = getDadosBloqueio($dbOracle, $strBloqueio);

                        $sqlBloqueio = "INSERT INTO SFPC.TBITEMLICITACAOBLOQUEIO  (";
                        $sqlBloqueio .= "CLICPOPROC, "; // clicpoproc IS 'Código do Processo Licitatório';
                        $sqlBloqueio .= "ALICPOANOP, "; // alicpoanop IS 'Ano do Processo Licitatório';
                        $sqlBloqueio .= "CGREMPCODI, "; // cgrempcodi IS 'Código do Grupo';
                        $sqlBloqueio .= "CCOMLICODI, "; // ccomlicodi IS 'Código da Comissão';
                        $sqlBloqueio .= "CORGLICODI, "; // corglicodi IS 'Código do Órgão Licitante';
                        $sqlBloqueio .= "CITELPSEQU, "; // código sequencial dos itens da licitação';
                        $sqlBloqueio .= "AITLBLANOB, "; // Ano do bloqueio Orçamentário SFCO.TBBLOQUEIO
                        $sqlBloqueio .= "AITLBLNBLOQ "; // Número Sequencial do Bloqueio Orçamentário SFCO.TBBLOQUEIO
                        $sqlBloqueio .= ") VALUES (";
                        $sqlBloqueio .= "$Processo, ";
                        $sqlBloqueio .= "$ProcessoAno, ";
                        $sqlBloqueio .= $Grupo . ",";
                        $sqlBloqueio .= "$ComissaoCodigo, ";
                        $sqlBloqueio .= "$OrgaoLicitanteCodigo, ";
                        $sqlBloqueio .= "$key, ";
                        $sqlBloqueio .= $arryInfoBloqueio['anoChave'] . " , ";
                        $sqlBloqueio .= $arryInfoBloqueio['sequencialChave'] . " ) ";

                        $resItensBloqueio = executarTransacao($db, $sqlBloqueio);

                        if (PEAR::isError($resItensBloqueio)) {
                            cancelarTransacao($db);
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlBloqueio\n\n$DescErroEmail ($CodErroEmail)");
                            $transacao = false;
                        }
                    }
                }
            }

            // Remover Rascunho
            if ($Botao == "ConfirmarResultado") {
                $sql  = "DELETE FROM SFPC.TBITEMLICITACAORASCUNHO ITEM ";
                $sql .= "WHERE  ITEM.CLICPOPROC = " . $Processo;
                $sql .= "       AND ITEM.ALICPOANOP = " . $ProcessoAno;
                $sql .= "       AND ITEM.CGREMPCODI = " . $Grupo;
                $sql .= "       AND ITEM.CCOMLICODI = " . $ComissaoCodigo;
                $sql .= "       AND ITEM.CORGLICODI = " . $OrgaoLicitanteCodigo;

                $res = executarTransacao($db, $sql);
            }

            $db->query("COMMIT");
            $db->query("END TRANSACTION");

            // Envia mensagem para página selecionar #
            $Mensagem = urlencode("Resultado alterado com Sucesso");

            if ($_SESSION['_cperficodi_'] == 2) {
                $Mensagem .= ". Verificar o desdobramento desta alteração nos Módulos de Registro de Preços e Contrato";
            }

            $Url = "CadResultadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";

            if (!in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }

            header("location: $Url");
            exit();
        }
    } else {
        $Botao = "";
    }
}

/**
 * INCLUIR/REMOVER BLOQUEIO/DOTAÇÃO
 */
if ($Botao == "IncluirBloqueio") {
    $BloqueioTodos = "";

    if ($isDotacao) {
        if (is_null($DotacaoAno) or $DotacaoAno == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoAno').focus();\" class='titulo2'>Ano da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoOrgao) or $DotacaoOrgao == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoOrgao').focus();\" class='titulo2'>Orgão da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoUnidade) or $DotacaoUnidade == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoUnidade').focus();\" class='titulo2'>Unidade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoFuncao) or $DotacaoFuncao == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFuncao').focus();\" class='titulo2'>Função da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoSubfuncao) or $DotacaoSubfuncao == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoSubfuncao').focus();\" class='titulo2'>Subfunção da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoPrograma) or $DotacaoPrograma == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoPrograma').focus();\" class='titulo2'>Programa da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoTipoProjetoAtividade) or $DotacaoTipoProjetoAtividade == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoTipoProjetoAtividade').focus();\" class='titulo2'>Tipo do projeto/Atividade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoProjetoAtividade) or $DotacaoProjetoAtividade == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoProjetoAtividade').focus();\" class='titulo2'>Projeto/Atividade da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoElemento1) or $DotacaoElemento1 == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento1').focus();\" class='titulo2'>Elemento 1 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoElemento2) or $DotacaoElemento2 == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento2').focus();\" class='titulo2'>Elemento 2 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoElemento3) or $DotacaoElemento3 == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento3').focus();\" class='titulo2'>Elemento 3 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoElemento4) or $DotacaoElemento4 == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento4').focus();\" class='titulo2'>Elemento 4 da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($DotacaoFonte) or $DotacaoFonte == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFonte').focus();\" class='titulo2'>Fonte da dotação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf("%04s", $DotacaoAno);
            $BloqueioTodos .= "." . sprintf("%02s", $DotacaoOrgao);
            $BloqueioTodos .= sprintf("%02s", $DotacaoUnidade);
            $BloqueioTodos .= "." . sprintf("%02s", $DotacaoFuncao);
            $BloqueioTodos .= "." . sprintf("%04s", $DotacaoSubfuncao);
            $BloqueioTodos .= "." . sprintf("%04s", $DotacaoPrograma);
            $BloqueioTodos .= "." . sprintf("%01s", $DotacaoTipoProjetoAtividade);
            $BloqueioTodos .= "." . sprintf("%03s", $DotacaoProjetoAtividade);
            $BloqueioTodos .= "." . sprintf("%01s", $DotacaoElemento1);
            $BloqueioTodos .= "." . sprintf("%01s", $DotacaoElemento2);
            $BloqueioTodos .= "." . sprintf("%02s", $DotacaoElemento3);
            $BloqueioTodos .= "." . sprintf("%02s", $DotacaoElemento4);
            $BloqueioTodos .= "." . sprintf("%04s", $DotacaoFonte);
            $BloqueioTodosData = getDadosDotacaoOrcamentaria($dbOracle, $BloqueioTodos);
        }
    } else {
        if (is_null($BloqueioAno) or $BloqueioAno == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioAno').focus();\" class='titulo2'>Ano do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($BloqueioOrgao) or $BloqueioOrgao == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioOrgao').focus();\" class='titulo2'>Orgão do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($BloqueioUnidade) or $BloqueioUnidade == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueiUnidade').focus();\" class='titulo2'>Unidade do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($BloqueioDestinacao) or $BloqueioDestinacao == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioDestinacao').focus();\" class='titulo2'>Destinação do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (is_null($BloqueioSequencial) or $BloqueioSequencial == "") {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioSequencial').focus();\" class='titulo2'>Sequencial do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf("%04s", $BloqueioAno);
            $BloqueioTodos .= "." . sprintf("%02s", $BloqueioOrgao);
            $BloqueioTodos .= "." . sprintf("%02s", $BloqueioUnidade);
            $BloqueioTodos .= "." . sprintf("%01s", $BloqueioDestinacao);
            $BloqueioTodos .= "." . sprintf("%04s", $BloqueioSequencial);
            $BloqueioTodosData = getDadosBloqueio($dbOracle, $BloqueioTodos);
        }
    }

    if ($isDotacao) {
        $Foco = "DotacaoAno";
    } else {
        $Foco = "BloqueioAno";
    }

    if ($GLOBALS['Mens'] != 1) {
        if (is_null($BloqueioTodosData)) {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>Bloqueio/Dotação não existe</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
    }

    if ($GLOBALS['Mens'] != 1) {        
        if (is_null($Bloqueios)) {
            $Bloqueios = array();
        }

        $isRepetido = false;

        foreach ($Bloqueios as $bloqueio) {
            if ($bloqueio == $BloqueioTodos) {
                $isRepetido = true;
                adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>Bloqueio/Dotação repetido</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }

        if (! $isRepetido) {
            array_push($Bloqueios, $BloqueioTodos);
        }
    }
} elseif ($Botao == "RetirarBloqueio") {
    if ($isDotacao) {
        $Foco = "DotacaoAno";
    } else {
        $Foco = "BloqueioAno";
    }

    $quantidade = count($Bloqueios);

    for ($itr = 0; $itr < $quantidade; $itr ++) {
        if ($BloqueiosCheck[$itr]) {
            unset($Bloqueios[$itr]);
        }
    }

    if ($GLOBALS['Mens'] != 1) {
        if (count($Bloqueios) <= 0) {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>A Licitação deve ter pelo menos um Bloqueio/Dotação </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
    }

    unset($BloqueiosCheck);
}

$descricaoTratamentoDiferenciado = strtoupper(getDescricaoTratamentoDiferenciado($Linha[11]));
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script>
    function enviar(valor) {
        if (valor=="ExibirMapaResumo") {
            var cont = 1;

            while (document.getElementById('arrValorLogrado['+cont+']')) {
                if (moeda2float(document.getElementById('arrValorLogrado['+cont+']').value) > moeda2float(document.getElementById('arrValorEstimado['+cont+']').value)) {
                    alert('Se o valor logrado for superior ao valor estimado, solicitamos que seja inserido no processo a diligência efetuada que motivou a ação.');
                    break;
                }
                cont++;
            }
        } else if(valor == 'CarregarRascunho') {
            var r = confirm("As alterações não salvas serão perdidas, deseja continuar?");
            
            if (r == false) {
                return false;
            }
        } else if (valor == 'SalvarRascunho') {
            var r = confirm("O rascunho antigo será apagado, deseja continuar?");

            if (r == false) {
                return false;
            }
        }

        document.formulario.Botao.value = valor;
        document.formulario.submit();
    }

    function AbreJanela(url,largura,altura) {
        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
    }

    function CaracteresObjeto(text,campo) {
        input = document.getElementById(campo);
        input.value = text.value.length;
    }
    
    // Recupera os dados do fornecedor
    function validaFornecedor(nomeCampoCpfCnpj,nomeCampoResposta) {
        cpfCnpj = limpaCPFCNPJ(document.getElementById(nomeCampoCpfCnpj).value);
        param = cpfCnpj;
        const BASEURL = "http://"+window.location.host;
        exclusivo = document.getElementById('verificarExclusivo').value;
        
        if (exclusivo == 1) {
            param += '&exclusivo=1';
        }
        
        
        carregamentoDinamico(BASEURL+"/compras/RotDadosFornecedor.php","CPFCNPJ="+param,nomeCampoResposta);
        document.getElementById(nomeCampoCpfCnpj).value = formataCpfCnpj(cpfCnpj);
    }

    function atualizarTotalLogrado(linha) {
        //Pegando a quantidade do item na linha que foi alterada
        quantidadeItem = moeda2float(jQuery(".arrQuantidade_" + linha).val());

        //Pegando Valor do item na linha que foi alterada
        valorEstimadoItem = moeda2float(jQuery(".arrValorEstimado_" + linha).val());
        valorLogradoItem = moeda2float(jQuery(".arrValorLogrado_" + linha).val());

        jQuery(".spanTotalLogrado_" + linha).text(float2moeda(valorLogradoItem*quantidadeItem));
    }

    function AtualizarValorTotal(linha) {
        cont=1;

        //Pegando a quantidade do item na linha que foi alterada
        quantidadeItem = moeda2float(document.getElementById('arrQuantidade['+linha+']').value);

        //Pegando Valor do item na linha que foi alterada
        valorEstimadoItem = moeda2float(document.getElementById('arrValorEstimado['+linha+']').value);
        valorLogradoItem = moeda2float(document.getElementById('arrValorLogrado['+linha+']').value);

        //# SO FACO SE TIVER EXERCÍCIO
        if (document.getElementById('registroPreco').value!="S") {
            var booUnicoComItem = document.getElementById('unicoComItem').value;

            //Pegando Valor da quantidade do exercicio
            quantidadeExercicio = moeda2float(document.getElementById('arrQuantidadeExercicio['+linha+']').value);

            if (booUnicoComItem==1) {
                valorExercicio = moeda2float(document.getElementById('arrValorLogradoExercicio['+linha+']').value);
            } else {
                valorExercicio = (valorLogradoItem * quantidadeExercicio);
                document.getElementById('arrValorLogradoExercicio['+linha+']').value = float2moeda(valorExercicio);
                
                if (document.getElementById('spanValorLogradoExercicio['+linha+']')!=undefined) {
                    document.getElementById('spanValorLogradoExercicio['+linha+']').innerHTML = float2moeda(valorExercicio);
                }
            }
            
            document.getElementById('spanValorLogradoDemaisExercicios['+linha+']').innerHTML = float2moeda((valorLogradoItem*quantidadeItem)-valorExercicio);
            document.getElementById('arrValorLogradoDemaisExercicios['+linha+']').value = float2moeda((valorLogradoItem*quantidadeItem)-valorExercicio);
        }

        document.getElementById('spanTotalLogrado['+linha+']').innerHTML = float2moeda(valorLogradoItem*quantidadeItem);
    }
    <?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="<?php $programa?>" method="post" name="formulario">
        <input type="hidden" name="Botao" id="Botao" value="">
        <input type="hidden" id="verificarExclusivo" name="verificarExclusivo" value="<?php echo ($descricaoTratamentoDiferenciado == 'EXCLUSIVO' || $verificarExclusivo) ? 1 : 0; ?>">
        <br> <br> <br> <br> <br>
        <table width="100%" cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="150">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000" >Página Principal</font>
                    </a>
                    &gt; Licitações &gt; Resultados &gt; Manter
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
            <?php
            if ($Mens == 1) {
                ?>
                <tr>
                    <td width="150"></td>
                    <td align="left" colspan="2">
                        <?php ExibeMens($Mensagem,$Tipo,1); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
            <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                                MANTER - RESULTADO DE LICITAÇÃO
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p>
                                    Para atualizar o(s) Resultado(s) da licitação, preencha os dados abaixo e clique no botão "Totalizar".</br>
                                    Para exibir o Mapa de Resultados, atualize e clique no botão "Exibir Mapa Resumo".</br>
                                    Para indicar o Motivo não logrado, deixar os campos sem preenchimento e selecionar o motivo.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input name="Processo" type="hidden" value="<?php echo $Processo; ?>" />
                                <input name="ProcessoAno" type="hidden" value="<?php echo $ProcessoAno; ?>" />
                                <input name="ComissaoCodigo" type="hidden" value="<?php echo $ComissaoCodigo; ?>"/>
                                <input name="OrgaoLicitanteCodigo" type="hidden" value="<?php echo $OrgaoLicitanteCodigo;?>" />
                                <input name="Grupo" type="hidden" value="<?php echo $Grupo;?>" />
                                <table border="0" width="100%" summary="" >
                                    <tr>
                                        <td width="20%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Comissão
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label style="width: 500px;"><?php echo $Linha[1];?></label>
                                            <input type="hidden" name="CodigoDaComissao" value="<?php echo $Linha[0];?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >
                                            Processo
                                        </td>
                                        <td align="left" class="textonormal" colspan="3" >
                                            <label><?php echo substr($Linha[2] + 10000,1); ?></label>
                                            <input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($Linha[2] + 10000,1);?>" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Ano
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $Linha[3]; ?></label>
                                            <input type="hidden" name="AnoDoExercicio" value="<?php echo $Linha[3];?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Modalidade
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $Linha[5]; ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Tratamento diferenciado EPP/ME/MEI
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $descricaoTratamentoDiferenciado; ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Registro de Preço
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <input type="hidden" id="registroPreco" name="registroPreco" value="<?php echo $Linha[6];?>"/>
                                            <label>
                                                <?php
                                                if ($RegistroPreco) {
                                                    echo "Sim";
                                                } else {
                                                    echo "Não";
                                                }
                                                ?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Licitação
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo substr($Linha[7] + 10000,1); ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Ano da Licitação
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $Linha[8]; ?></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Objeto:
                                        </td>
                                        <td>
                                            <label class="textonormal" style="word-wrap: break-word;">
                                                <?php echo $Linha[9];?>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                                            Solicitação de Compra/Contratação-SCC:
                                        </td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <select style="width: 200px;" multiple="multiple">
                                                <?php
                                                foreach ($arrSolicitacoes as $seqSoli) {
                                                    ?>
                                                    <option selected="selected" value="<?php echo $seqSoli;?>">
                                                        <?php echo getNumeroSolicitacaoCompra($db,$seqSoli);?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <?php
                        $estilotd      = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
                        $estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
                        ?>
                        <!-- NOVO DOTAÇÃO/BLOQUEIO -->
                        <tr>
                            <td class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">
                                <span id="BloqueioTitulo" colspan=2>
                                    BLOQUEIO OU DOTAÇÃO ORÇAMENTÁRIA
                                </span>
                            </td>
                        </tr>
                        <?php
                        $cntBloqueio = - 1;

                        // teste
                        if (empty($Bloqueios) && count($valorDotacaoBloqueio) > 0) {
                            $Bloqueios = array_unique($valorDotacaoBloqueio);
                        }

                        $isDotacao = ($RegistroPreco == 'S') ? true : false;

                        if (! is_null($Bloqueios) && (count($Bloqueios) > 0)) {
                            foreach ($Bloqueios as $bloqueioItem) {
                                if (isset($bloqueioItem)) {
                                    $cntBloqueio ++;
                                    ?>
                                    <tr>
                                        <td class="textonormal">
                                            <?php
                                            if ($Botao!="ExibirMapaResumo") {
                                                ?>
                                                <input name="BloqueiosCheck[<?php echo $cntBloqueio; ?>]" type="checkbox" <?php if($BloqueiosCheck[$cntBloqueio]) { echo "checked"; } ?> />
                                                <?php
                                            }
                                            ?>
                                            <?php echo $bloqueioItem; ?>
                                            <input name="Bloqueios[<?php echo $cntBloqueio; ?>]" value="<?php echo $bloqueioItem; ?>" type="hidden">
                                            <input name="dotacaoBloqueio[<?php echo $cntBloqueio; ?>]" type="hidden" value="<?php echo $bloqueioItem; ?>"/>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                        ?>
                        <?php
                        if ($Botao!="ExibirMapaResumo") {
                            ?>
                            <tr>
                                <td class="textonormal" colspan=2 bgcolor="#ffffff">
                                    <table class="textonormal" border="0" align="left" width="100%" summary="">
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7" width="200px" id="BloqueioLabel">
                                                Novo bloqueio ou dotação:
                                            </td>
                                            <td class="textonormal">
                                                <?php
                                                if ($isDotacao) {
                                                    ?>
                                                    Ano: <input name="DotacaoAno" id="DotacaoAno" size="4" maxlength="4" value="" type="text" value="<?php echo $DotacaoAno; ?>"/>
                                                    Órgão: <input name="DotacaoOrgao" id="DotacaoOrgao" size="2" maxlength="2" value="" type="text" value="<?php echo $DotacaoAno; ?>"/>
                                                    Unidade: <input name="DotacaoUnidade" id="DotacaoUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo $DotacaoAno; ?>"/>
                                                    Funcao: <input name="DotacaoFuncao" id="DotacaoFuncao" size="2" maxlength="2" value="" type="text" value="<?php echo $DotacaoFuncao; ?>"/>
                                                    SubFunção: <input name="DotacaoSubfuncao" id="DotacaoSubfuncao" size="4" maxlength="4" value="" type="text" value="<?php echo $DotacaoSubfuncao; ?>"/>
                                                    Programa: <input name="DotacaoPrograma" id="DotacaoPrograma" size="4" maxlength="4" value="" type="text" value="<?php echo $DotacaoSubfuncao; ?>"/>
                                                    Tipo Projeto/Atividade: <input name="DotacaoTipoProjetoAtividade" id="DotacaoTipoProjetoAtividade" size="1" maxlength="1" value="" type="text" value="<?php echo $DotacaoTipoProjetoAtividade; ?>"/>
                                                    Projeto/Atividade: <input name="DotacaoProjetoAtividade" id="DotacaoProjetoAtividade" size="3" maxlength="3" value="" type="text" value="<?php echo $DotacaoProjetoAtividade; ?>"/>
                                                    Elemento1: <input name="DotacaoElemento1" id="DotacaoElemento1" size="1" maxlength="1" value="" type="text" value="<?php echo $DotacaoElemento1; ?>"/>
                                                    Elemento2: <input name="DotacaoElemento2" id="DotacaoElemento2" size="1" maxlength="1" value="" type="text" value="<?php echo $DotacaoElemento2; ?>"/>
                                                    Elemento3: <input name="DotacaoElemento3" id="DotacaoElemento3" size="2" maxlength="2" value="" type="text" value="<?php echo $DotacaoElemento3; ?>"/>
                                                    Elemento4: <input name="DotacaoElemento4" id="DotacaoElemento4" size="2" maxlength="2" value="" type="text" value="<?php echo $DotacaoElemento4; ?>"/>
                                                    Fonte: <input name="DotacaoFonte" id="DotacaoFonte" size="4" maxlength="4" value="" type="text" value="<?php echo $DotacaoFonte; ?>"/>
                                                    <?php
                                                } else {
                                                    ?>
                                                    Ano: <input name="BloqueioAno" id="BloqueioAno" size="4" maxlength="4" value="" type="text" value="<?php echo $BloqueioAno; ?>"/>
                                                    Órgão: <input name="BloqueioOrgao" id="BloqueioOrgao" size="2" maxlength="2" value="" type="text" value="<?php echo $BloqueioOrgao; ?>"/>
                                                    Unidade: <input name="BloqueioUnidade" id="BloqueioUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo $BloqueioUnidade; ?>"/>
                                                    Destinação: <input name="BloqueioDestinacao" id="BloqueioDestinacao" size="1" maxlength="1" value="" type="text" value="<?php echo $BloqueioDestinacao; ?>"/>
                                                    Sequencial: <input name="BloqueioSequencial" id="BloqueioSequencial" size="4" maxlength="4" value="" type="text" value="<?php echo $BloqueioSequencial; ?>"/>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" align="center">
                                    <input name="BotaoIncluirBloqueioTodos" value="Incluir" class="botao" type="button" onClick="enviar('IncluirBloqueio')"/>
                                    <input name="BotaoRemoverBloqueioTodos" value="Remover" class="botao" type="button" onClick="enviar('RetirarBloqueio')"/>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <!-- NOVO BLOQUEIO/DOTAÇÃO -->
                        <tr>
                            <td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4">
                                ITENS DA SOLICITAÇÃO
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #F1F1F1;" colspan="4">
                                <table bordercolor="#75ADE6" border="1" cellspacing="" bgcolor="bfdaf2" width="100%" class="textonormal">
                                    <?php
                                    $ORDEM               = 0;
                                    $LOTEANTERIOR        = "";
                                    $VALORSOMATORIO      = 0;
                                    $VALORSOMATORIOGERAL = 0;
                                    $QTD                 = 0;
                                    $QTDLOGRADOS         = 0;

                                    $exibeTd = false;
                                    
                                    // checando se existe material/serviços
                                    if ($resILTmp instanceof DB_Result) {
                                        while ($arrI = $resILTmp->fetchRow()) {
                                            if (! empty($arrI[17]) || ! empty($arrI[18])) {
                                                $exibeTd = true;
                                                break;
                                            }
                                        }
                                    }

                                    if ($carregarRascunho) {
                                        $buscarItensLicitacao = listaItensLicitacaoRascunho($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
                                    } else {
                                        $buscarItensLicitacao = listaItensLicitacao($Processo, $ProcessoAno, $Grupo, $ComissaoCodigo, $OrgaoLicitanteCodigo);
                                    }

                                    while ($listaIntens = $buscarItensLicitacao->fetchRow()) {
                                        $ORDEM ++;
                                        $INDICE = $listaIntens[3];
                                        
                                        // Se for Material
                                        if ($listaIntens[1] != "") {
                                            $TIPO               = "CADUM";
                                            $DESCRICAO          = $listaIntens[8];
                                            $CODRED             = $listaIntens[1];
                                            $UNIDADE            = $listaIntens[9];
                                            $VALORTRP           = calculaValorTrp($CODRED);
                                            $DESCRICAODETALHADA = $listaIntens[17];
                                        } else {
                                            $TIPO               = "CADUS";
                                            $DESCRICAO          = $listaIntens[10] . " - ";
                                            $CODRED             = $listaIntens[2];
                                            $UNIDADE            = "";
                                            $VALORTRP           = "-";
                                            $DESCRICAODETALHADA = $listaIntens[18];
                                        }

                                        $SENQUENCIAL = $listaIntens[0];
                                        $QUANTIDADE  = $listaIntens[4];
                                        $VALORUNIT   = $listaIntens[5];
                                        
                                        $VALORESTIMADO      = $VALORUNIT;
                                        $VALORTOTALESTIMADO = $VALORESTIMADO * $QUANTIDADE;

                                        if (isset($arrValorLogrado[$ORDEM])) {
                                            $VALORLOGRADO = moeda2float($arrValorLogrado[$ORDEM]);
                                        } else {
                                            $VALORLOGRADO = $listaIntens[12];
                                        }

                                        $TOTALLOGRADO = $VALORLOGRADO * $QUANTIDADE;

                                        // Rotina para pegar o valor do cpf
                                        if (isset($arrCpfCnpjLote[$arrCpfCnpj[$ORDEM]])) {
                                            $CPFCNPJ = $arrCpfCnpjLote[$arrCpfCnpj[$ORDEM]];
                                        } else {
                                            if ($listaIntens[16] != "") {
                                                $sql = "SELECT AFORCRCCGC, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO WHERE AFORCRSEQU = $listaIntens[16] ";

                                                $res = $db->query($sql);

                                                $linha = resultLinhaUnica($res);

                                                if ($linha[0] != "") {
                                                    $CPFCNPJ = $linha[0];
                                                } elseif ($linha[1] != "") {
                                                    $CPFCNPJ = $linha[0];
                                                } else {
                                                    $CPFCNPJ = "";
                                                }
                                            } else {
                                                $CPFCNPJ = "";
                                            }
                                        }

                                        // Marca
                                        if (isset($arrMarca[$ORDEM])) {
                                            $MARCA = $arrMarca[$ORDEM];
                                        } else {
                                            $MARCA = $listaIntens[13];
                                        }

                                        // Marca
                                        if (isset($arrModelo[$ORDEM])) {
                                            $MODELO = $arrModelo[$ORDEM];
                                        } else {
                                            $MODELO = $listaIntens[14];
                                        }
                                        
                                        // Motivo
                                        if (isset($arrMotivos[$ORDEM])) {
                                            $MOTIVO = $arrMotivos[$ORDEM];
                                        } else {
                                            $MOTIVO = $listaIntens[15];
                                        }

                                        $LOTE = $listaIntens[11];
                                        $arrLote = array();

                                        if ($LOTE != $LOTEANTERIOR && $LOTEANTERIOR != "") {
                                            ?>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;TOTAL LOGRADO  LOTE <?php echo $LOTE - 1?> </td>
                                                <td colspan="17" class="textonormal" align="left">&nbsp;
                                                    <b><?php echo converte_valor_estoques($VALORSOMATORIO);?></b>
                                                </td>
                                            </tr>
                                            <?php $VALORSOMATORIO = 0;
                                        }

                                        if ($LOTE != $LOTEANTERIOR) {
                                            ?>
                                            <tr>
                                                <td align="left" bgcolor="#75ADE6" class="titulo3" colspan="19" >&nbsp;Lote <?php echo $LOTE." - CPF/CNPJ FORNECEDOR";?>
                                                    <?php
                                                    if ($Botao != "ExibirMapaResumo") {
                                                        ?>
                                                        <input
                                                            onchange="validaFornecedor('arrCpfCnpjLote[<?php echo $LOTE;?>]','spanCpfCnpjLote[<?php echo $LOTE;?>]');"
                                                            value="<?php echo FormataCpfCnpj($CPFCNPJ); ?>"
                                                            name="arrCpfCnpjLote[<?php echo $LOTE;?>]"
                                                            id="arrCpfCnpjLote[<?php echo $LOTE;?>]"
                                                            type="text"
                                                            size="18"
                                                            maxlength="18"
                                                            class=""
                                                        />
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <?php echo $CPFCNPJ?>
                                                        <input
                                                            value="<?php echo $CPFCNPJ?>"
                                                            name="arrCpfCnpjLote[<?php echo $LOTE;?>]"
                                                            id="arrCpfCnpjLote[<?php echo $LOTE;?>]"
                                                            type="hidden"
                                                            size="18"
                                                            maxlength="18"
                                                            class="inteiroPositivo"
                                                        />
                                                        <?php
                                                    }
                                                    ?>
                                                    <span id="spanCpfCnpjLote[<?php echo $LOTE;?>]">
                                                        <?php
                                                        if (! is_null($CPFCNPJ)) {
                                                            $CPFCNPJ = removeSimbolos($CPFCNPJ);
                                                            $materialServicoFornecido = null;
                                                            $TipoMaterialServico      = null;

                                                            include '../compras/RotDadosFornecedor.php';

                                                            $db = Conexao();
                                                        }

                                                        $arrLote[$LOTE] = $CPFCNPJ;
                                                        ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="linhainfo">
                                                <td <?php echo $estilotd;?>>ORD</td>
                                                <td <?php echo $estilotd;?>>DESCRIÇÃO MATERIAL/SERVIÇO</td>
                                                <td <?php echo $estilotd;?>>TIPO</td>
                                                <td <?php echo $estilotd;?>>CÓD.RED</td>
                                                <td <?php echo $estilotd;?>>UNIDADE</td>

                                                <?php
                                                if ($exibeTd) {
                                                    ?>
                                                    <td <?php echo $estilotd;?>>DESCRIÇÃO DETALHADA</td>
                                                    <?php
                                                }
                                                ?>

                                                <td <?php echo $estilotd;?>>TRP</td>
                                                <td <?php echo $estilotd;?>>QUANTIDADE</td>
                                                <td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
                                                <td <?php echo $estilotd;?>>VALOR TOTAL</td>
                                                <td <?php echo $estilotd;?>>VALOR<br>LOGRADO </td>
                                                <td <?php echo $estilotd;?>>TOTAL<br>LOGRADO </td>
                                            
                                                <?php
                                                if (1 == 2) {
                                                    ?>
                                                    <td <?php echo $estilotd;?>>QUANTIDADE<br>EXERCÍCIO </td>
                                                    <td <?php echo $estilotd;?>>VALOR<br>LOGRADO<br>EXERCÍCIO </td>
                                                    <td <?php echo $estilotd;?>>VALOR<br>LOGRADO<br>DEMAIS EXERCÍCIO </td>
                                                    <?php
                                                }
                                                ?>
                                        
                                                <td <?php echo $estilotd;?>>MARCA</td>
                                                <td <?php echo $estilotd;?>>MODELO</td>
                                                <td <?php echo $estilotd;?>>MOTIVO NÃO LOGRADO</td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td <?php echo $estiloClasstd;?>>
                                                &nbsp;<?php echo $INDICE;?>
                                                <input
                                                name="arrSequencial[<?php echo $ORDEM;?>]"
                                                value="<?php echo $SENQUENCIAL;?>"
                                                type="hidden" />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>
                                            &nbsp;<?php echo $DESCRICAO;?>
                                            <input
                                                name="arrDescricao[<?php echo $ORDEM;?>]"
                                                value="<?php echo $DESCRICAO;?>"
                                                type="hidden" />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>
                                            &nbsp;<?php echo $TIPO;?>
                                            <input
                                                name="arrTipo[<?php echo $ORDEM;?>]"
                                                value="<?php echo $TIPO;?>"
                                                type="hidden" />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>
                                            &nbsp;<?php echo $CODRED;?>
                                            <input
                                                name="arrCodRed[<?php echo $ORDEM;?>]"
                                                value="<?php echo $CODRED;?>"
                                                type="hidden" />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <?php echo $UNIDADE;?><input
                                                name="arrUnidade[<?php echo $ORDEM;?>]"
                                                value="<?php echo $UNIDADE;?>"
                                                type="hidden" />
                                        </td>

                                        <?php if ($exibeTd) {
                                            ?>
                                            <td <?php echo $estiloClasstd;?>>&nbsp;
                                                <?php
                                                if ($DESCRICAODETALHADA != null) {
                                                    echo $DESCRICAODETALHADA;
                                                } else {
                                                    echo '<center>---<center>';
                                                }
                                                ?>
                                                <input name="arrDescricaoDetalhada[<?php echo $ORDEM;?>]" value="<?php echo $DESCRICAODETALHADA;?>" type="hidden" />
                                            </td>
                                            <?php
                                        }
                                        ?>

                                        <td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORTRP);?>
                                            <input
                                                name="arrValorTrp[<?php echo $ORDEM;?>]"
                                                value="<?php echo converte_valor_estoques($VALORTRP);?>"
                                                type="hidden"
                                            />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($QUANTIDADE);?>
                                            <input
                                                id="arrQuantidade[<?php echo $ORDEM;?>]"
                                                class="arrQuantidade_<?php echo $ORDEM;?>"
                                                name="arrQuantidade[<?php echo $ORDEM;?>]"
                                                value="<?php echo converte_valor_estoques($QUANTIDADE);?>"
                                                type="hidden"
                                            />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORESTIMADO);?>
                                            <input
                                                id="arrValorEstimado[<?php echo $ORDEM;?>]"
                                                class="arrValorEstimado_<?php echo $ORDEM;?>"
                                                name="arrValorEstimado[<?php echo $ORDEM;?>]"
                                                value="<?php echo converte_valor_estoques($VALORESTIMADO);?>"
                                                type="hidden"
                                            />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor_estoques($VALORTOTALESTIMADO);?>
                                            <input
                                                name="arrValorTotalEstimado[<?php echo $ORDEM;?>]"
                                                value="<?php echo converte_valor_estoques($VALORTOTALESTIMADO);?>"
                                                type="hidden"
                                            />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <?php
                                            if ($Botao!="ExibirMapaResumo") {
                                                ?>
                                                <input
                                                    value="<?php echo converte_valor_estoques($VALORLOGRADO)?>"
                                                    onKeyUp="atualizarTotalLogrado('<?php echo $ORDEM;?>');"
                                                    name="arrValorLogrado[<?php echo $ORDEM;?>]"
                                                    id="arrValorLogrado[<?php echo $ORDEM;?>]"
                                                    type="text"
                                                    size="16"
                                                    class="dinheiro4casas arrValorLogrado_<?php echo $ORDEM;?>"
                                                    maxlength="16"
                                                />
                                                <?php
                                            } else {
                                                ?>
                                                <?php echo converte_valor_estoques($VALORLOGRADO)?>
                                                <input
                                                    value="<?php echo converte_valor_estoques($VALORLOGRADO)?>"
                                                    name="arrValorLogrado[<?php echo $ORDEM;?>]"
                                                    id="arrValorLogrado[<?php echo $ORDEM;?>]"
                                                    type="hidden"
                                                    size="16"
                                                    class="dinheiro4casas"
                                                    maxlength="16"
                                                />
                                                <?php
                                            }
                                            ?>
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <span id="spanTotalLogrado[<?php echo $ORDEM;?>]" class="spanTotalLogrado_<?php echo $ORDEM;?>">
                                                <?php echo converte_valor_estoques($TOTALLOGRADO);?>
                                            </span>
                                            <input
                                                value="<?php echo $LOTE;?>"
                                                name="arrCpfCnpj[<?php echo $ORDEM;?>]"
                                                id="arrCpfCnpj[<?php echo $ORDEM;?>]"
                                                type="hidden"
                                                size="18"
                                                maxlength="18"
                                                class="inteiroPositivo"
                                            />
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <?php
                                            if ($Botao!="ExibirMapaResumo") {
                                                ?>
                                                <input
                                                    value="<?php echo $MARCA?>"
                                                    name="arrMarca[<?php echo $ORDEM;?>]"
                                                    id="arrMarca[<?php echo $ORDEM;?>]"
                                                    type="text"
                                                />
                                                <?php
                                            } else {
                                                ?>
                                                <?php echo $MARCA?>
                                                <input
                                                    value="<?php echo $MARCA?>"
                                                    name="arrMarca[<?php echo $ORDEM;?>]"
                                                    id="arrMarca[<?php echo $ORDEM;?>]"
                                                    type="hidden"
                                                />
                                                <?php
                                            }
                                            ?>
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <?php
                                            if ($Botao!="ExibirMapaResumo") {
                                                ?>
                                                <input
                                                    value="<?php echo $MODELO?>"
                                                    name="arrModelo[<?php echo $ORDEM;?>]"
                                                    id="arrModelo[<?php echo $ORDEM;?>]"
                                                    type="text"
                                                />
                                                <?php
                                            } else {
                                                echo $MODELO;
                                                ?>
                                                <input
                                                    value="<?php echo $MODELO?>"
                                                    name="arrModelo[<?php echo $ORDEM;?>]"
                                                    id="arrModelo[<?php echo $ORDEM;?>]"
                                                    type="hidden"
                                                />
                                                <?php
                                            } ?>
                                        </td>
                                        <td <?php echo $estiloClasstd;?>>&nbsp;
                                            <?php
                                            if ($Botao!="ExibirMapaResumo") {
                                                ?>
                                                <select name="arrMotivos[<?php echo $ORDEM;?>]">
                                                    <option value="">Selecione..</option>
                                                    <?php
                                                    foreach ($arrMotivosLista as $key => $motivo) {
                                                        ?>
                                                        <option <?php if ($MOTIVO==$key) {echo "selected='selected'";}?> value="<?php echo $key; ?>" >
                                                            <?php echo $motivo; ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                                <?php
                                            } else {
                                                ?>
                                                <input type="hidden" value="<?php echo $MOTIVO;?>" name="arrMotivos[<?php echo $ORDEM;?>]" />
                                                <?php
                                                foreach ($arrMotivosLista as $key => $motivo) {
                                                    ?>
                                                    <?php
                                                    if ($MOTIVO==$key) { 
                                                        echo $motivo;
                                                    }
                                                    ?>
                                                    <?php
                                                }
                                                ?>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $LOTEANTERIOR = $LOTE;
                                    $VALORSOMATORIOGERAL += $VALORESTIMADO * $QUANTIDADE;
                                    $QTD ++;
    
                                    if ($MOTIVO == "") {
                                        $QTDLOGRADOS ++;
                                        $VALORTOTALLOGRADO += $VALORLOGRADO * $QUANTIDADE;
                                        $VALORTESTIMADO += $VALORESTIMADO * $QUANTIDADE;
                                        $VALORSOMATORIO += $VALORLOGRADO * $QUANTIDADE;
                                    }
                                    ?>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;TOTAL LOGRADO LOTE <?php echo $LOTE?> </td>
                                    <td colspan="17" class="textonormal"align="left">&nbsp;
                                        <b><?php echo converte_valor_estoques($VALORSOMATORIO);?></b>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19">
                                        TOTAL LOGRADO GERAL: <?php echo converte_valor_estoques($VALORTOTALLOGRADO);?>
                                    </td>
                                </tr>
                                <?php
                                if ($Botao=="ExibirMapaResumo") {
                                    ?>
                                    <tr>
                                        <td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19">
                                            TOTAL GERAL: <?php echo converte_valor_estoques($VALORSOMATORIOGERAL);?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19">
                                            TOTAL ESTIMADO (ITENS QUE LOGRARAM ÊXITO): <?php echo converte_valor_estoques($VALORTESTIMADO);?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="LEFT" bgcolor="#DCEDF7" class="titulo3" colspan="19">
                                            TOTAL A SER HOMOLOGADO  (ITENS QUE LOGRARAM  ÊXITO): <?php echo converte_valor_estoques($VALORTOTALLOGRADO);?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                            <?php
                            if ($unicoComUmiTem === true) {
                                ?>
                                <input id="unicoComItem" type="hidden" value="1" />
                                <?php
                            } else {
                                ?>
                                <input id="unicoComItem" type="hidden" value="0" />
                                <?php
                            }
                            ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal" align="right"colspan="19">
                                <?php
                                if ($Botao=="ExibirMapaResumo") {
                                    ?>
                                    <input type="button" name="ConfirmarResultado" value="Confirmar Resultado" class="botao" onClick="javascript:enviar('ConfirmarResultado')">
                                    <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Totalizar')">
                                    <?php
                                } elseif ($Botao==""||$Botao=="Totalizar"||$Botao="IncluirBloqueio"||$Botao="RetirarBloqueio") {
                                    ?>
                                    <input type="button" name="Totalizar" value="Totalizar" class="botao" onClick="javascript:enviar('Totalizar')">
                                    <input type="button" name="ExibirMapaResumo" value="Exibir Mapa Resumo" class="botao" onClick="javascript:enviar('ExibirMapaResumo')">
                                    <?php
                                    if (!$licitacaoFechada) {
                                        ?>
                                        <input type="button" name="Rascunho" value="Salvar Rascunho" class="botao" onClick="javascript:enviar('SalvarRascunho')">
                                        <input type="button" name="CarregarRascunho" value="Carregar Rascunho" class="botao" onClick="javascript:enviar('CarregarRascunho')">
                                        <?php
                                    }
                                    ?>
                                    <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>
</body>
<?php $db->disconnect(); ?>
</html>