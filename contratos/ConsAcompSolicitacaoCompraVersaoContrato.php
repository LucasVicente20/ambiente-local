<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompSolicitacaoCompra.php
# Autor:    Ariston Cordeiro
# Data:     31/08/2011
# Objetivo: Programa de visualização de solicitação de compra
# -------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data: 18/06/2018
# Objetivo: Ajustar btn voltar em compras para o histórico em extrato (Registro de preço)
# -------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data: 19/09/2018
# Objetivo: [LICITAÇÕES - TRAMITAÇÃO] Entrada - Erros (Item 4 da lista da CR)
# -------------------------------------------------------------------------

// informações referentes as páginas de tramitações para retorno após o uso
session_start();
$_SESSION['_fperficorp_'] = 'S';
$pesquisa = $_SESSION['origemPesquisa'];

//unset($_SESSION['origemPesquisa']);
$telaAppView = true;
# Acesso ao arquivo de funções #
require_once '../funcoes.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $programaChamando = filter_input(INPUT_POST, 'programa', FILTER_SANITIZE_STRING);
}

$acaoPagina = ACAO_PAGINA_ACOMPANHAR;

if (!is_null($programaChamando)) {
    $programaSelecao = $programaChamando;
} else {
    // Caminho para voltar ao histórico (extrato) em registro de preço
    if (isset($_GET['ProgramaOrigem']) && (strpos($_GET['ProgramaOrigem'], 'ConsDetalheHistoricoParticipanteExtratoAta') !== false || strpos($_GET['ProgramaOrigem'], 'ConsDetalheHistoricoCaronaAtaExtratoAta') !== false)) {
        $url_destino = '../registropreco/ConsAtaRegistroPrecoExtratoAta.php';
        $params_url = explode('-', $_GET['ProgramaOrigem']);

        if (!empty($params_url) && count($params_url) == 7) {
            $url_destino  = '../registropreco/'.$params_url[0].'.php';
            $url_destino .= '?ata='.$params_url[1];
            $url_destino .= '&tipo='.$params_url[2];
            $url_destino .= '&orgao='.$params_url[3];
            $url_destino .= '&item='.$params_url[4];
            $url_destino .= '&tipoItem='.$params_url[5];
            $url_destino .= '&seqItem='.$params_url[6];
            
            $programaSelecao = $url_destino;
        } else {
            $programaSelecao = $url_destino;
        }
    } else {
        $programaSelecao = "ConsAcompSolicitacaoCompraSelecionar.php";
    }
}

$programa = $_GET['programa']; // Para poder o botão voltar ir para o programa de origem

// caso venha da pesquisa de entrada ou saida de tramitacao
if (isset($pesquisa) && $_GET['origemTramitacao']==1) {
    $Url  = "../licitacoes/CadTramitacao".$pesquisa['rotina'].".php?";
    $Url .= "numProtocolo=".$pesquisa['numProtocolo'];
    $Url .= "&anoProtocolo=".$pesquisa['anoProtocolo'];
    $Url .= "&orgao=".$pesquisa['orgao'];
    $Url .= "&objeto=".$pesquisa['objeto'];
    $Url .= "&numeroci=".$pesquisa['numeroci'];
    $Url .= "&numeroOficio=".$pesquisa['numeroOficio'];
    $Url .= "&numeroScc=".$pesquisa['numeroScc'];
    $Url .= "&proLicitatorio=".$pesquisa['proLicitatorio'];
    $Url .= "&acao=".$pesquisa['acao'];
    $Url .= "&origem=".$pesquisa['origem'];
    $Url .= "&Data".$pesquisa['rotina']."Ini=".$pesquisa['Data'.$pesquisa['rotina'].'Ini'];
    $Url .= "&Data".$pesquisa['rotina']."Fim=".$pesquisa['Data'.$pesquisa['rotina'].'Fim'];
    $Url .= "&botao=Pesquisar&Critica=1";
    $Url .= "&t=".mktime();

    $urlTramitacao  = $Url;
}  

if (isset($pesquisa) && $_GET['origemTramitacao']==2) {
    $Url  = "../licitacoes/RelTramitacaoMonitoramento.php?";
    $Url .= "tramitacaoNumeroProtocolo=".$pesquisa['tramitacaoNumeroProtocolo'];
    $Url .= "&tramitacaoAnoProtocolo=".$pesquisa['tramitacaoAnoProtocolo'];
    $Url .= "&tramitacaoGrupo=".$pesquisa['tramitacaoGrupo'];
    $Url .= "&tramitacaoOrgao=".$pesquisa['tramitacaoOrgao'];
    $Url .= "&tramitacaoObjeto=".$pesquisa['tramitacaoObjeto'];
    $Url .= "&tramitacaoNumeroCI=".$pesquisa['tramitacaoNumeroCI'];
    $Url .= "&tramitacaoNumeroOficio=".$pesquisa['tramitacaoNumeroOficio'];
    $Url .= "&tramitacaoNumeroScc=".$pesquisa['tramitacaoNumeroScc'];
    $Url .= "&tramitacaoComissaoLicitacao=".$pesquisa['tramitacaoComissaoLicitacao'];
    $Url .= "&tramitacaoAcao=".$pesquisa['tramitacaoAcao'];
    $Url .= "&tramitacaoAgenteDestino=".$pesquisa['tramitacaoAgenteDestino'];
    $Url .= "&tramitacaoProcessoNumero=".$pesquisa['tramitacaoProcessoNumero'];
    $Url .= "&tramitacaoProcessoAno=".$pesquisa['tramitacaoProcessoAno'];
    $Url .= "&tramitacaoDataEntradaInicio=".$pesquisa['tramitacaoDataEntradaInicio'];
    $Url .= "&tramitacaoDataEntradaFim=".$pesquisa['tramitacaoDataEntradaFim'];
    $Url .= "&tramitacaoSituacao=".$pesquisa['tramitacaoSituacao'];
    $Url .= "&tramitacaoOrdem=".$pesquisa['tramitacaoOrdem'];
    $Url .= "&tramitacaoAtraso=".$pesquisa['tramitacaoAtraso'];
    $Url .= "&Botao=Pesquisar";//&Critica=1";
    $Url .= "&t=".mktime();

    $urlTramitacao  = $Url;
}

$solicitacao = isset($_GET['SeqSolicitacao'])
    ? filter_input(INPUT_GET, 'SeqSolicitacao', FILTER_SANITIZE_NUMBER_INT)
    : null;

// Verificar se a solicitação tem sequencial para redirecionar ao programa antigo
$numeroSeqAta[0] = null;
 
if (!is_null($solicitacao)) {
    $sql = "SELECT CARPNOSEQU FROM SFPC.TBSOLICITACAOCOMPRA WHERE CSOLCOSEQU = " . $solicitacao;
    
    $db = Conexao();
    $res = $db->query($sql);
    $numeroSeqAta = $res->fetchRow();  
    $db->disconnect();  
}

if (is_null($numeroSeqAta[0])) {
   require_once '../compras/CadSolicitacaoCompraIncluirManterExcluir.php'; 
} else {
   require_once '../compras/CadSolicitacaoCompraIncluirManterExcluirScc.php';    
}