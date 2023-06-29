<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraIncluirManterExcluir.php
# Autor: Ariston Cordeiro
# Data: 10/08/2011
# Objetivo: Programa de Solicitação de Compra de Material, comum para as ferramentas de inclusão, manutenção e exclusão
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data: 12/12/2011 - Adicionando restrições informadas pela tabela de parâmetros gerais
# -------------------------------------------------------------------------
# Alterado: Marcos Tulio
# Data: 28/02/2012 - Suporte para o IE
# -------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data: 28/02/2012 - Suportando nova estrutura do bloqueio
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 15/03/12
# Objetivo: Resolvendo problemas na exibição do browse IE.
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 04/06/12
# Objetivo: Correção na mensagem exibida - Demanda Redmine: #11228
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 06/06/12
# Objetivo: Correção na mensagem exibida - Demanda Redmine: #11258
# -------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Data: 28/06/12
# Objetivo: Inserir a tabela de Pre-solicitação de empenho no Acompanhamento - Demanda Redmine: #11570
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 03/08/12
# Objetivo: Correção na mensagem exibida - Demanda Redmine: #13347
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 23/10/12
# Objetivo: Criar campo--> Número - Demanda Redmine: #15787
# -------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Data: 29/10/2012
# Objetivo: permitir que campo Quantidade no Exercício seja 0.0000
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 01/11/12
# Objetivo: Correção de erros - Demanda Redmine: #17770
# -------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data: 21/11/12
# Objetivo: Correção de erros - Demanda Redmine: #18167
# -------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Data: 27/08/13
# Objetivo: Só criticar dotação se Licitação e Registro de Preço="S"
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 11/06/2014
# Objetivo: [CR123140]: REDMINE 21 (P3)
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 25/09/2014
# Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 18/06/2014
# Objetivo: [CR123140]: REDMINE 21, [CR123142]: REDMINE 22
# Verifica se $materiais existe, eliminando mensagem de warning do foreach.
# Gravação da descrição detalhada CADUM genérico em maiúsculo.
# Melhora da query que pega os dados dos materiais via POST
# Não exibe mais a palavra "Array" quando dois ou mais materiais com descrição detalhada
# não estiverem preenchidos.
# -------------------------------------------------------------------------
# Alterada: Pitang Agile TI
# Data: 31/07/2014 - Adiciona novas regras para alteração ou exclusão de SCC
# -------------------------------------------------------------------------
# Alterada: Pitang Agile TI
# Data: 21/08/2014
# Objetivo: [CR123140]: REDMINE 21 (P3)
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 27/10/2014
# Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 14/11/2014
# Objetivo: [CR]:
# issue 44 - Alterar inclusão/manutenção da SCC par retirar a
# validação que não permite incluir tens de material
# e serviço na mesma SCC
# -------------------------------------------------------------------------
# Alterada: Pitang Agile TI
# Data: 17/11/2014
# Objetivo: #45 - Erro em SQL de produção - Realiza validação de preenchimento de dados para
# tipo de compra SARP em rascunho.
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 11/03/2015
# Objetivo: [CR129149]: REDMINE 92 (Registro de Preço)
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 24/03/2015
# Objetivo: [Sem CR redmine] Sistema obriga o preenchimento do campo ""Intenção de Registro de Preço" na inclusão da SCC
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 18/05/2015
# Objetivo: CR redmine 78563 - Solicitação de Compras Manter- Obrigar a digitação da descrição detalhada para serviços
# Versão: v1.16.1-40-g4a5491e
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 03/06/2015
# Objetivo: CR redmine 80467 Manter Solicitação - trocar o tipo de compra
# Versão: v1.18.0-17-g9920068
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 06/07/2015
# Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
# Link: http://redmine.recife.pe.gov.br/issues/81057
# Versão: v1.22.0-8-g375a774
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 06/07/2015
# Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
# Link: http://redmine.recife.pe.gov.br/issues/81057
# Versão: v1.22.0-8-g375a774
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 04/09/2015
# Objetivo: CR 80498 - Acompanhamento de Solicitação de Compras
# Link: http://redmine.recife.pe.gov.br/issues/80498
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 29/12/2015
# Objetivo: Requisito 115579 - Incluir SCC ou Incluir Licitação - problema de gravação, caracter estranho
# Link: http://redmine.recife.pe.gov.br/issues/115579
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data: 10/02/2016
# Objetivo: Bug 123570 - Incluir e manter SCC - Exibição de Colunas com problema
# Link: http://redmine.recife.pe.gov.br/issues/123570
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 04/09/2018
# Objetivo: Tarefa Redmine 201677
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/10/2018
# Objetivo: Tarefa Redmine 205467
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/10/2018
# Objetivo: Tarefa Redmine 205467
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     07/11/2018
# Objetivo: Tarefa Redmine 205440
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     30/11/2018
# Objetivo: Tarefa Redmine 207575
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     21/12/2018
# Objetivo: Tarefa Redmine #208655
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     12/02/2019
# Objetivo: Tarefa Redmine #210579
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Ernesto Ferreira
# Data:     14/03/2019
# Objetivo: Tarefa Redmine #212571
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     14/03/2019
# Objetivo: Tarefa Redmine #212728
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     14/03/2019
# Objetivo: Tarefa Redmine #212730
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     11/04/2019
# Objetivo: Tarefa Redmine 214711
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     03/06/2019
# Objetivo: Tarefa Redmine 218080
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     31/07/2019
# Objetivo: Tarefa Redmine 221527
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     01/08/2019
# Objetivo: Tarefa Redmine 221694
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado: João Madson
# Data:     14/07/2020
# Objetivo: Tarefa Redmine #235540
# ---------------------------------------------------------------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 10/09/2021
# Objetivo: CR #253343
#---------------------------------------------------------------------------
# Alterado : João Madson    
# Data: 26/08/2022
# Objetivo: CR #268022
#---------------------------------------------------------------------------
# Alterado : João Madson e Lucas Vicente  
# Data: 09/09/2022
# Objetivo: CR #268442
#---------------------------------------------------------------------------
# Alterado : João Madson e Lucas Vicente
# Data: 06/10/2022
# Objetivo: CR #269737
#---------------------------------------------------------------------------
// Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';
require_once 'funcoesScc.php';
require_once 'CR92.php';
// require_once 'vendor/autoload.php';

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');
AddMenuAcesso('/estoques/CadIncluirCentroCusto.php');
AddMenuAcesso('/compras/RotDadosFornecedor.php');
AddMenuAcesso('/compras/ConsProcessoPesquisarScc.php');
AddMenuAcesso('/compras/RelAcompanhamentoSCCPdf.php');
AddMenuAcesso('/compras/RelTRPConsultar.php');
AddMenuAcesso('/compras/RelTRPConsultarDireta.php');
AddMenuAcesso('/compras/InfPreenchimentoBloqueios.php');
AddMenuAcesso('/registropreco/CadIncluirIntencaoRegistroPreco.php');
AddMenuAcesso('/registropreco/CadVisualizarIntencaoRegistroPreco.php');

global $programaSelecao, $programa, $acaoPagina;
// Volta para o programa de origem
if (is_null($programaSelecao)) {
    AddMenuAcesso('/compras/' . $programaSelecao);
} else {
    if ($programa == 'CadLicitacaoIncluir.php') {} else {
        AddMenuAcesso('compras/' . $programa);
    }
}

// Remover todos os itens caso SARP e troque de tipo
$removerItens = false;
$tipoControle = 0;
$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e"
            // Recebendo variáveis via POST #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Botao = $_POST['Botao'];
    $InicioPrograma = $_POST['InicioPrograma'];
    // $Orgao = $_POST['Orgao'];
    $CentroCusto = $_POST['CentroCusto'];
    // $OrgaoUsuario = $_POST['OrgaoUsuario'];
    $Observacao = strtoupper2(trim($_POST['Observacao']));
    $Objeto = strtoupper2(trim($_POST['Objeto']));
    $Justificativa = strtoupper2(trim($_POST['Justificativa']));

    $sequencialIntencao = $_POST['sequencialIntencao'];
    $anoIntencao = $_POST['anoIntencao'];

    /*
     * # contadores de caracteres agora estão sendo setados em baixo a partir da variável associada
     * $NCaracteresObservacao = $_POST['NCaracteresObservacao'];
     * $NCaracteresObjeto = $_POST['NCaracteresObjeto'];
     * $NCaracteresJustificativa = $_POST['NCaracteresJustificativa'];
     */

    $DataDom                    = $_POST['DataDom'];
    $Lei                        = $_POST['Lei'];
    $Artigo                     = $_POST['Artigo'];
    $Inciso                     = $_POST['Inciso'];
    $Foco                       = $_POST['Foco'];
    $TipoLei                    = $_POST['TipoLei'];
    $RegistroPreco              = $_POST['RegistroPreco'];
    $SarpTipo                   = $_REQUEST['Sarp'];
    $tipoAta                    = $_POST['tipoAta'];
    $CompromissoValor           = $_POST['campoCompromisso'];
    $TipoReservaOrcamentaria    = $_POST['TipoReservaOrcamentaria']; // se é bloqueio (1) ou dotação (2)
    $DotacaoTodos               = $_POST['DotacaoTodos'];
    $CnpjFornecedor             = $_POST['CnpjFornecedor'];
    $GeraContrato               = $_POST['GeraContrato'];
    $TipoCompra                 = $_POST['TipoCompra'];
    $NomeDocumento              = $_POST['NomeDocumento'];
    $DDocumento                 = $_POST['DDocumento'];
    $OrigemBancoPreços          = $_POST['OrigemBancoPreços'];
    $NumProcessoSARP            = $_POST['NumProcessoSARP'];
    $AnoProcessoSARP            = $_POST['AnoProcessoSARP'];
    $ComissaoCodigoSARP         = $_POST['ComissaoCodigoSARP'];
    $OrgaoLicitanteCodigoSARP   = $_POST['OrgaoLicitanteCodigoSARP'];
    $GrupoEmpresaCodigoSARP     = $_POST['GrupoEmpresaCodigoSARP'];
    $CarregaProcessoSARP        = $_POST['CarregaProcessoSARP'];
    $isDotacaoAnterior          = $_POST['isDotacaoAnterior']; // informa se na pagina anterior era dotação ou bloqueio
    $Bloqueios                  = $_POST['Bloqueios'];
    $BloqueiosCheck             = $_POST['BloqueiosCheck'];
    $valorBloqueio              = $_POST['ValorBloqueio'];
    
    // Verificar para remover itens caso trocar de sarp para outro tipo
     if($_SESSION['tipoAnterior'] == TIPO_COMPRA_SARP) {
        if(isset($_SESSION['tipoAnterior']) && ($_SESSION['tipoAnterior'] != $TipoCompra)) {
            $NumProcessoSARP = $AnoProcessoSARP = $ComissaoCodigoSARP = $OrgaoLicitanteCodigoSARP = $GrupoEmpresaCodigoSARP = null;
            $CarregaProcessoSARP = 0;
            unset($_SESSION['numeroAtaCasoSARP']);
            $removerItens = true;
        }
        if(isset($_SESSION['tipoAtaAnterior']) && ($_SESSION['tipoAtaAnterior'] != $tipoAta)) {
            $removerItens = true;
            unset($_SESSION['numeroAtaCasoSARP']);
            $NumProcessoSARP = $AnoProcessoSARP = $ComissaoCodigoSARP = $OrgaoLicitanteCodigoSARP = $GrupoEmpresaCodigoSARP = null;
            $CarregaProcessoSARP = 0;
        }
        if(isset($_SESSION['tipoSarpAnterior']) && ($_SESSION['tipoSarpAnterior'] != $SarpTipo)) {
            $removerItens = true;
            unset($_SESSION['numeroAtaCasoSARP']);
            $NumProcessoSARP = $AnoProcessoSARP = $ComissaoCodigoSARP = $OrgaoLicitanteCodigoSARP = $GrupoEmpresaCodigoSARP = null;
            $CarregaProcessoSARP = 0;
        }
        if(!empty($_SESSION['centroCustoAnterior']) && ($_SESSION['centroCustoAnterior'] != $CentroCusto)) {
            $NumProcessoSARP = $AnoProcessoSARP = $ComissaoCodigoSARP = $OrgaoLicitanteCodigoSARP = $GrupoEmpresaCodigoSARP = null;
            $CarregaProcessoSARP = 0;
            unset($_SESSION['numeroAtaCasoSARP']);
            $removerItens = true;
        }
        if(isset($_SESSION['processoAnterior']) && ($_SESSION['processoAnterior'] != $NumProcessoSARP)) {
            unset($_SESSION['numeroAtaCasoSARP']);
            $removerItens = true;
        }
    }

    if(isset($_SESSION ['numeroAtaCasoSARP'])) {
        $numeroAtaRP = $_SESSION ['numeroAtaCasoSARP'];
    }

    $_SESSION['tipoAnterior']        = $TipoCompra;
    $_SESSION['tipoAtaAnterior']     = $tipoAta;
    $_SESSION['tipoSarpAnterior']    = $SarpTipo;
    $_SESSION['centroCustoAnterior'] = $CentroCusto;
    $_SESSION['processoAnterior']    = $NumProcessoSARP;

    $BloqueioAno        = $_POST['BloqueioAno'];
    $BloqueioOrgao      = $_POST['BloqueioOrgao'];
    $BloqueioUnidade    = $_POST['BloqueioUnidade'];
    $BloqueioDestinacao = $_POST['BloqueioDestinacao'];
    $BloqueioSequencial = $_POST['BloqueioSequencial'];

    $DotacaoAno                     = $_POST['DotacaoAno'];
    $DotacaoOrgao                   = $_POST['DotacaoOrgao'];
    $DotacaoUnidade                 = $_POST['DotacaoUnidade'];
    $DotacaoFuncao                  = $_POST['DotacaoFuncao'];
    $DotacaoSubfuncao               = $_POST['DotacaoSubfuncao'];
    $DotacaoPrograma                = $_POST['DotacaoPrograma'];
    $DotacaoTipoProjetoAtividade    = $_POST['DotacaoTipoProjetoAtividade'];
    $DotacaoProjetoAtividade        = $_POST['DotacaoProjetoAtividade'];
    $DotacaoElemento1               = $_POST['DotacaoElemento1'];
    $DotacaoElemento2               = $_POST['DotacaoElemento2'];
    $DotacaoElemento3               = $_POST['DotacaoElemento3'];
    $DotacaoElemento4               = $_POST['DotacaoElemento4'];
    $DotacaoFonte                   = $_POST['DotacaoFonte'];

    $MaterialCheck               = $_POST['MaterialCheck'];
    $MaterialCod                 = $_POST['MaterialCod'];
    $MaterialCodItem             = $_POST['MaterialCodItem'];
    $MaterialTrp                 = $_POST['MaterialTrp'];
    $MaterialQuantidade          = $_POST['MaterialQuantidade'];
    $MaterialVitescunit          = $_POST['MaterialVitescunit'];
    $MaterialValorEstimado       = $_POST['MaterialValorEstimado'];
    $MaterialQuantidadeExercicio = $_POST['MaterialQuantidadeExercicioValor'];
    $MaterialTotalExercicio      = $_POST['MaterialTotalExercicioValor'];
    $MaterialMarca               = $_POST['MaterialMarca'];
    $MaterialModelo              = $_POST['MaterialModelo'];
    $MaterialFornecedor          = $_POST['MaterialFornecedorValor'];
    $MaterialDescricaoDetalhada  = $_POST['MaterialDescricaoDetalhada'];

    $ServicoCheck               = $_POST['ServicoCheck'];
    $ServicoCod                 = $_POST['ServicoCod'];
    $ServicoCodItem             = $_POST['ServicoCodItem']; 
    $ServicoQuantidade          = $_POST['ServicoQuantidade'];
    $ServicoVitescunit          = $_POST['ServicoVitescunit'];
    $ServicoDescricaoDetalhada  = $_POST['ServicoDescricaoDetalhada'];
    $ServicoQuantidadeExercicio = $_POST['ServicoQuantidadeExercicioValor'];
    $ServicoValorEstimado       = $_POST['ServicoValorEstimado'];
    $ServicoTotalExercicio      = $_POST['ServicoTotalExercicioValor'];
    $ServicoFornecedor          = $_POST['ServicoFornecedorValor'];

    $Solicitacao     = $_POST['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'
    $Numero          = $_POST['Numero'];
    $DataSolicitacao = $_POST['DataSolicitacao'];
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Solicitacao = $_GET['SeqSolicitacao'];
    if (is_null($Solicitacao)) {
        // inicio de uma inclusão. excluir arquivos na sessão
        unset($_SESSION['Arquivos_Upload']);
    }
}

// Variáveis para teste
$desabilitarChecagemFornecedorSistemaMercantil = false; // correto é false. se true, permite inclusão de fornecedores que não passaram na checagem do cadastro mercantil
$desabilitarChecagemBloqueioSofin = false; // correto é false. se true, não valida bloqueios no sistema do sofin e valores de bloqueio (só valida bloqueio pelo portal)
                                           // $desabilitarChecagemFornecedorSistemaMercantil = true;
                                           // $desabilitarChecagemBloqueioSofin = true;

$db = Conexao();
$dbOracle = ConexaoOracle();

$sql = ' select qpargetmaobjeto, qpargetmajustificativa, qpargedescse,
                        epargesubelemespec, qpargeqmac, qpargeqmac, epargetdov
             from sfpc.tbparametrosgerais ';
$linha = resultLinhaUnica(executarSQL($db, $sql));
if (is_null($linha)) {
    echo '<br/><br/><br/><br/><br/><br/>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<b>Falha do sistema, pois os Parâmetros Gerais precisam ser preenchidos. Vá em em 'Tabelas > Parâmetros Gerais' e preencha os campos.</b>";
}

$tamanhoObjeto              = 400;
$tamanhoJustificativa       = 200;
$tamanhoDescricaoServico    = strlen($linha[2]);
$subElementosEspeciais      = explode(',', $linha[3]);
$tamanhoArquivo             = 30;
$tamanhoNomeArquivo         = $linha[5];
$extensoesArquivo           = $linha[6];
$cargaBloqueiosManter       = false; // informa se é primeiro carregamento e existem bloqueios, para serem carregados para o javascript


// Verificar se é SARP e se tem carpnosequ e redireciona para tela anterior
if((!empty($Solicitacao) && $acaoPagina == ACAO_PAGINA_MANTER && $_SESSION['_fperficorp_'] != 'S') && false) {
    $sql = CR92::sqlVerificarCarpnosequ($Solicitacao);
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    
    if(empty($linha[0]) && $linha[1] == TIPO_COMPRA_SARP) {
        $Botao = 'Voltar';
        $_SESSION['mensagemSarp'] = "Esta Solicitação de Compra e Contratação de Material e Serviço (SCC) não poderá ser alterada, pois antecede a criação do Módulo de Registro de Preços. Proceda a inclusão de uma nova SCC do tipo SARP com os mesmos dados";
    }
}

if ($Botao == 'Voltar') {    
    $_SESSION['carregarSelecionarDoSession'] = true;
    if ($programa == 'CadLicitacaoIncluir.php') {
        $programaSelecao = $programa;
    } else {
        if (is_null($programaSelecao)) {
            $programaSelecao = '../licitacoes/CadLicitacaoIncluir.php';
        }
    }
    header('Location: ' . $programaSelecao);
    exit();
} elseif ($Botao == 'Imprimir') {
    $sql = "SELECT CSITSOCODI FROM SFPC.TBSOLICITACAOCOMPRA WHERE CSOLCOSEQU = " . $Solicitacao;

    $res = $db->query($sql);

    $situacaoImp = $res->fetchRow();

    $situacaoImp = $situacaoImp[0];

    if ($situacaoImp <> 10) {
        $Url = 'RelAcompanhamentoSCCPdf.php?Solicitacao=' . $Solicitacao;
        header('Location: ' . $Url);
        exit();
    } else {
        $Mens     = 1;
        $Tipo     = 2;
        $Mensagem = 'Não é possível gerar PDF de SCC cancelada';
    }
}

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    if(isset($_SESSION['ataCasoSARP'])) {
        //$numeroAtaRP = $ataCasoSARP; TODO verificar se precisa dessa linha descomentada
    }
}

// recuperando dados da SCC (acompanhamento, manter, excluir)
if (($acaoPagina == ACAO_PAGINA_MANTER and is_null($CentroCusto)) or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido        
    
    if (is_null($Solicitacao)) { // solicitação nao foi informada. voltar para seleção de solicitacao
        header('Location: ' . $programaSelecao);
        exit();
    } else {
        $cargaBloqueiosManter     = true;
        $linha                    = resultLinhaUnica(executarSQL($db, sqlRecuperarDadosScc($Solicitacao)));
        $cargaInicial             = true;
        $CentroCusto              = $linha[0];
        $Observacao               = trim($linha[1]);
        $Objeto                   = trim($linha[2]);
        $Justificativa            = trim($linha[3]);
        $Ano                      = $linha[4];
        $Numero                   = $linha[20];
        $TipoCompra               = $linha[5];
        $DataHora                 = $linha[6];
        $DataSolicitacao          = DataBarra($DataHora);
        $DataDom                  = DataBarra($linha[12]);
        $Lei                      = $linha[14];
        $Artigo                   = $linha[15];
        $Inciso                   = $linha[16];
        $TipoLei                  = $linha[13];
        $RegistroPreco            = $linha[17];
        $Sarp                     = $linha[18];
        $SarpTipo                 = $Sarp;
        $tipoAta                  = $linha[21];
        $NumAta                   = $linha[22];
        $TipoReservaOrcamentaria  = 1; // se é bloqueio (1) ou dotação (2)
        $GeraContrato             = $linha[19];
        $NumProcessoSARP          = $linha[7];
        $AnoProcessoSARP          = $linha[8];
        $ComissaoCodigoSARP       = $linha[9];
        $OrgaoLicitanteCodigoSARP = $linha[10];
        $GrupoEmpresaCodigoSARP   = $linha[11];
        $CarregaProcessoSARP      = 1;
        $CnpjFornecedor           = (!empty($linha[23])) ? $linha[23] : $linha[24];
        $carpincodn               = $linha[25];
        $aarpinanon               = $linha[26];
        $carpexcodn               = $linha[27];
        $arpexanon                = $linha[28];
        $sequencialIntencao       = !empty($sequencialIntencao) ? $sequencialIntencao : $linha[29];
        $anoIntencao              = !empty($anoIntencao) ? $anoIntencao : $linha[30];

        $sqlCarpnosequ = CR92::sqlVerificarCarpnosequ($Solicitacao);
        $linha = resultLinhaUnica(executarSQL($db, $sqlCarpnosequ));
        $sqlTipoControle = "
            SELECT arpn.farpnotsal 
            FROM sfpc.tbataregistropreconova arpn
            WHERE arpn. carpnosequ = %d";

        $tipoControle = resultLinhaUnica(executarSQL($db, sprintf($sqlTipoControle, $linha[0])));
        $_SESSION['tipoControle'] = $tipoControle[0];
        $_SESSION['centroCustoAnterior'] = $CentroCusto;

        // Verificar se é acampanhamento para pegar o numero da ata
        // Verificar o número da ata se for SARP
        if($tipoAta == 'I') {
            $numeroAtaRP = numeroAtaSarpInterna($db, $OrgaoLicitanteCodigoSARP, $carpincodn, $aarpinanon);
        } else {
            $numeroAtaRP = $carpexcodn . '/' . $arpexanon;
        }

        $sql = "SELECT
                    sc.cmatepsequ ,
                    sc.cservpsequ ,
                    sc.eitescdescse ,
                    sc.aitescqtso ,
                    sc.vitescunit ,
                    sc.vitescvexe ,
                    sc.aitescqtex ,
                    sc.eitescmarc ,
                    sc.eitescmode ,
                    sc.cusupocodi ,
                    sc.aforcrsequ ,
                    sc.citescsequ ,
                    f.aforcrccpf ,
                    f.aforcrccgc ,
                    sc.eitescdescmat,
                    sc.citarpsequ
                FROM
                    SFPC.TBitemsolicitacaocompra sc LEFT JOIN sfpc.tbfornecedorcredenciado f
                        ON f.aforcrsequ = sc.aforcrsequ
                WHERE
                    sc.csolcosequ = $Solicitacao
                ORDER BY
                    sc.aitescorde";

        $res = executarSQL($db, $sql);
        $cntMaterial = - 1;
        $cntServico = - 1;
        $tipoItem = null;
        $strBloqueioDotacao = null;
        
        // para cada item de solicitação
        while ($linha = $res->fetchRow()) {
            $codigoItem = $linha[11];
            if (! is_null($linha[12])) {
                // cpf
                $fornecedorItem = $linha[12];
            } else {
                // cnpj
                $fornecedorItem = $linha[13];
            }
            if (! is_null($linha[0])) {
                ++ $cntMaterial;
                $MaterialCheck[$cntMaterial] = false;
                $MaterialCod[$cntMaterial] = $linha[0];
                $MaterialQuantidade[$cntMaterial] = converte_valor_estoques($linha[3]);
                $MaterialValorEstimado[$cntMaterial] = converte_valor_estoques($linha[4]);
                $MaterialValorVitescunit[$cntMaterial] = converte_valor_estoques($linha[4]);
                $MaterialTotalExercicio[$cntMaterial] = converte_valor_estoques($linha[5]);
                $MaterialQuantidadeExercicio[$cntMaterial] = converte_valor_estoques($linha[6]);
                $MaterialMarca[$cntMaterial] = $linha[7];
                $MaterialModelo[$cntMaterial] = $linha[8];
                $MaterialFornecedor[$cntMaterial] = $fornecedorItem;
                $MaterialDescricaoDetalhada[$cntMaterial] = strtoupper2(trim($linha[14]));
                $MaterialCodItem[$cntMaterial] = $linha[15];
                $tipoItem = 'M';
            } else {
                ++ $cntServico;
                $ServicoCheck[$cntServico] = false ;
                $ServicoCod[$cntServico] = $linha[1];
                $ServicoDescricaoDetalhada[$cntServico] = strtoupper2(trim($linha[2]));
                $ServicoQuantidade[$cntServico] = converte_valor_estoques($linha[3]);
                $ServicoValorEstimado[$cntServico] = converte_valor_estoques($linha[4]);
                $ServicoVitescunit[$cntServico] = converte_valor_estoques($linha[4]);
                $ServicoTotalExercicio[$cntServico] = converte_valor_estoques($linha[5]);
                $ServicoQuantidadeExercicio[$cntServico] = converte_valor_estoques($linha[6]);
                $ServicoFornecedor[$cntServico] = $fornecedorItem;
                $ServicoCodigoItem[$cntServico] = $linha[15];
                $ServicoCodItem[$cntServico] = $linha[15];
                $tipoItem = 'S';
            }
        }
        // para cada bloqueio/dotacao
        $Bloqueios = array();
        if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == 'S') { // neste caso o é uma dotação
                                                                              // pegar dotação
            $sql = " SELECT DISTINCT aitcdounidoexer, citcdounidoorga, citcdounidocodi, citcdotipa, aitcdoordt,
                                citcdoele1, citcdoele2, citcdoele3, citcdoele4, citcdofont
                     FROM SFPC.TBitemdotacaoorcament
                     WHERE csolcosequ = $Solicitacao";
            $res2 = executarSQL($db, $sql);

            $cntBloqueios = - 1;
            while ($linha = $res2->fetchRow()) {
                ++ $cntBloqueios;
                $pos = $cntBloqueios + 1;
                $ano = $linha[0];
                $orgao = $linha[1];
                $unidade = $linha[2];
                $tipoAtividade = $linha[3];
                $atividade = $linha[4];
                $elemento1 = $linha[5];
                $elemento2 = $linha[6];
                $elemento3 = $linha[7];
                $elemento4 = $linha[8];
                $fonte = $linha[9];

                $dotacao = getDadosDotacaoOrcamentariaFromChave($dbOracle, $ano, $orgao, $unidade, $tipoAtividade, $atividade, $elemento1, $elemento2, $elemento3, $elemento4, $fonte);

                $strBloqueioDotacao = $dotacao['dotacao'];

                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == '') {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . 'Bloqueio_' . $cntBloqueios . "').focus();\" class='titulo2'>Dotação foi excluída do sistema orçamentário em material ord " . $pos . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        } else {
            // pegar bloqueio

            $sql = " SELECT DISTINCT aitcblnbloq, aitcblanob
                     FROM SFPC.TBitembloqueioorcament
                     WHERE csolcosequ = $Solicitacao";
            $res2 = executarSQL($db, $sql);
            $cntBloqueios = - 1;

            while ($linha = $res2->fetchRow()) {
                ++ $cntBloqueios;
                $pos = $cntBloqueios + 1;
                $bloqChaveAno = $linha[1];
                $bloqChaveSequ = $linha[0];
                $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqChaveAno, $bloqChaveSequ);
                $strBloqueioDotacao = $bloqueioArray['bloqueio'];
                $valorBloqueio[] = $bloqueioArray['valorTotal'];

                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == '') {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . 'Bloqueio_' . $cntBloqueios . "').focus();\" class='titulo2'>Bloqueio foi excluído do sistema orçamentário</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        }

        // Recuperando documentos
        unset($_SESSION['Arquivos_Upload']);

        $sql = " SELECT cdocsocodi, edocsonome, edocsoexcl
                             FROM SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                            WHERE csolcosequ = $Solicitacao
                              and edocsoexcl != 'S' ";
        $res = executarSQL($db, $sql);
        while ($linha = $res->fetchRow()) {
            $codigoDoc = $linha[0];
            $codigoNome = $linha[1];
            $codigoExcl = $linha[2];
            $_SESSION['Arquivos_Upload']['nome'][] = $codigoNome;
            $_SESSION['Arquivos_Upload']['conteudo'][] = '';
            $_SESSION['Arquivos_Upload']['situacao'][] = 'existente';
            $_SESSION['Arquivos_Upload']['codigo'][] = $codigoDoc;
        }
    }
}

// pegando limites de compra
// sintaxe para pegar o limite de compra: $limiteCompra[cód do tipo da compra][administração D ou I][é obras?]
$limiteCompra = null;
$JSCriacaoLimiteCompra = '';

if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_INCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    $sql = ' SELECT ctpcomcodi, flicomtipo, cmodlicodi, vlicomobra, vlicomserv FROM sfpc.tblimitecompra ORDER BY ctpcomcodi, flicomtipo, cmodlicodi, vlicomobra, vlicomserv';
    $res = executarSQL($db, $sql);
    $oldctpcomcodi = null;
    $oldflicomtipo = null;
    while ($obj = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        if (is_null($obj->CMODLICODI) or $obj->CMODLICODI == '') {
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][true] = $obj->vlicomobra;
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][false] = $obj->vlicomserv;
            if ($oldctpcomcodi == null) {
                $JSCriacaoLimiteCompra .= 'limiteCompra = new Array();';
            }
            if ($oldctpcomcodi != $obj->ctpcomcodi) {
                $JSCriacaoLimiteCompra .= 'limiteCompra[' . $obj->ctpcomcodi . '] = new Array();';
                $oldctpcomcodi = $obj->ctpcomcodi;
                $oldflicomtipo = null;
            }
            if ($oldflicomtipo != $obj->flicomtipo) {
                $JSCriacaoLimiteCompra .= 'limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "'] = new Array();";
                $oldflicomtipo = $obj->flicomtipo;
            }
            $JSCriacaoLimiteCompra .= '
                    limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['true'] = " . $obj->vlicomobra . ';
                    limiteCompra[' . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['false'] = " . $obj->vlicomserv . ';
                ';
        }
    }
}

// pegando situação atual da SCC (Este if está fora do if acima pois essa parte tem que ser carregada toda vez que recarrega a página,
// enquanto acima só carrega uma vez apenas)
if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    $sql = " SELECT csitsocodi FROM SFPC.TBsolicitacaocompra WHERE csolcosequ = $Solicitacao ";
    $situacaoSolicitacaoAtual = resultValorUnico(executarSQL($db, $sql));
}
$NCaracteresObservacao = strlen($Observacao);
$NCaracteresObjeto = strlen($Objeto);
$NCaracteresJustificativa = strlen($Justificativa);

// variáveis para ocultar campos e checagens associadas
$ocultarCampoRegistroPreco = false;
$ocultarCampoProcessoLicitatorio = false;
$ocultarCampoGeraContrato = false;
$ocultarCampoLegislacao = false;
$ocultarCampoTRP = false; // campo não aparecerá enquanto não for definido a tabela TRP
$ocultarCampoSARP = false;
$ocultarCampoDataDOM = false;
$ocultarCampoExercicio = false;
$ocultarCampoFornecedor = false;
$ocultarCampoNumeroSCC = false;
// ocultar campo numero
$ocultarCampoNumero = false;
//
$ocultarCampoJustificativa = false;
$preencherCampoGeraContrato = false; // informa que, apesar de ser oculto, CampoGeraContrato deve possuir o valor 'S'
$isFornecedorUnico = false; // informa se o campo de fornecedores dos itens está bloqueado para edição
$isValidacaoFornecedorLicitacao = true; // informa se a validação do fornecedor deve ser de licitação. Caso false, irá validar os fornecedores como compra direta
$isFracionamentoDespesa = false;
$ocultarCamposEdicao = false;
$isDotacao = false; // caso true o campo bloqueio é usado para dotação
$isBloqueioUnico = false; // Se o bloqueio ou dotação é o mesmo para toda SCC (todos os itens terão os mesmos bloqueios/dotacoes)

if (is_null($cargaInicial)) {
    $cargaInicial = false;
}

if ($TipoCompra != TIPO_COMPRA_DISPENSA and $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) {
    $ocultarCampoDataDOM = true;
    $ocultarCampoLegislacao = true;
} else {
    $isFornecedorUnico = true;
}
if ($TipoCompra == TIPO_COMPRA_LICITACAO) {
    $ocultarCampoFornecedor = true;
} else {
    $ocultarCampoRegistroPreco = true;
}

if ($TipoCompra != TIPO_COMPRA_SARP) {
    $ocultarCampoSARP = true;
    $ocultarCampoProcessoLicitatorio = true;
} else {
    // Pra SARP, o TRP não é mostrado
    $ocultarCampoTRP = true;
    //$ifVisualizacaoThenReadOnly = 'disabled';
}
if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == 'S') {
    $ocultarCampoGeraContrato = true;
    $isDotacao = true;
}
if (($TipoCompra == TIPO_COMPRA_LICITACAO and ($RegistroPreco == 'S' or is_null($RegistroPreco)))) {
    $ocultarCampoExercicio = true;
}
if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $ifVisualizacaoThenReadOnly = 'disabled'; // variável para bloquear alteração de dados nos campos do form
    $ocultarCamposEdicao = true;
}

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $ocultarCampoNumeroSCC = true;
    $ocultarCampoNumero = true;
}
//

if ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
    $isValidacaoFornecedorLicitacao = false;
}

if (is_null($NCaracteresObservacao) or $NCaracteresObservacao == '') {
    $NCaracteresObservacao = '0';
}
if (is_null($NCaracteresObjeto) or $NCaracteresObjeto == '') {
    $NCaracteresObjeto = '0';
}
if (is_null($NCaracteresJustificativa) or $NCaracteresJustificativa == '') {
    $NCaracteresJustificativa = '0';
}
if ($isFornecedorUnico) {
    $ifVisualizacaoThenReadOnlyFornecedorItens = 'disabled';
}
if ($isDotacao) {
    $isBloqueioUnico = true;
}

$reserva = 'Bloqueio';
if ($isDotacao) {
    $reserva = 'Dotação';
}

// Materiais do POST #
$QuantidadeMaterial = count($MaterialCod);

// Pegando os dados dos materiais enviados por POST
for ($itr = 0; $itr < $QuantidadeMaterial; ++ $itr) {
    $sql = sqlDadosMaterial($MaterialCod[$itr]);
    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
    }
    $Linha = $res->fetchRow();
    $MaterialDescricao = $Linha[0];
    $MaterialUnidade = $Linha[1];
    $MaterialDescDet = strtoupper2($Linha[2]);
    $pos = count($materiais);

    // preenchendo valores padrões caso seja nulo, para não ocorrer erro.
    if (is_null($MaterialQuantidade[$itr]) or $MaterialQuantidade[$itr] == '') {
        $MaterialQuantidade[$itr] = '0,0000';
    }
    if (is_null($MaterialValorEstimado[$itr]) or $MaterialValorEstimado[$itr] == '') {
        $MaterialValorEstimado[$itr] = '0,0000';
    }
    if (is_null($MaterialQuantidadeExercicio[$itr]) or $MaterialQuantidadeExercicio[$itr] == '') {
        $MaterialQuantidadeExercicio[$itr] = '0,0000';
    }
    if (is_null($MaterialTotalExercicio[$itr]) or $MaterialTotalExercicio[$itr] == '') {
        $MaterialTotalExercicio[$itr] = '0,0000';
    }

    if(empty($MaterialVitescunit[$itr])) {
        $MaterialVitescunit[$itr] = '0,0000';
    }

    $materiais[$pos]['posicao'] = $pos; // posição no array
    $materiais[$pos]['posicaoItem'] = $pos + 1; // posição mostrada na tela
    $materiais[$pos]['tipo'] = TIPO_ITEM_MATERIAL;
    $materiais[$pos]['codigo'] = $MaterialCod[$itr];
    $materiais[$pos]['codigo_item'] = $MaterialCodItem[$itr];
    $materiais[$pos]['descricao'] = $MaterialDescricao;
    $materiais[$pos]['unidade'] = $MaterialUnidade;
    /*
     */
    $materiais[$pos]['descricaoDetalhada'] = strtoupper(trim($MaterialDescricaoDetalhada[$itr]));

    if (is_null($MaterialCheck[$itr]) or ! $MaterialCheck[$itr]) {
        $materiais[$pos]['check'] = false;
    } else {
        $materiais[$pos]['check'] = true;
    }
    $materiais[$pos]['quantidade'] = $MaterialQuantidade[$itr]; 
    $materiais[$pos]['vitescunit'] = $MaterialVitescunit[$itr];
    $materiais[$pos]['valorEstimado'] = $MaterialValorEstimado[$itr];

    // valores em float para uso em funções
    $materiais[$pos]['quantidadeItem'] = moeda2float($MaterialQuantidade[$itr]);
    $materiais[$pos]['valorItem'] = moeda2float($MaterialValorEstimado[$itr]);

    $materiais[$pos]['quantidadeExercicio'] = $MaterialQuantidadeExercicio[$itr];
    $materiais[$pos]['marca'] = $MaterialMarca[$itr];
    $materiais[$pos]['modelo'] = $MaterialModelo[$itr]; 
    $materiais[$pos]['fornecedor'] = $MaterialFornecedor[$itr];
    $materiais[$pos]['isObras'] = isObras($db, $materiais[$pos]['codigo'], TIPO_ITEM_MATERIAL);
    if (moeda2float($materiais[$pos]['quantidade']) == 1) {
        $materiais[$pos]['totalExercicio'] = $MaterialTotalExercicio[$itr];
    } else {
        $materiais[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($materiais[$pos]['quantidadeExercicio']) * moeda2float($materiais[$pos]['valorEstimado']));
    }

    $materiais[$pos]['trp'] = calcularValorTrp($db, $TipoCompra, $materiais[$pos]['codigo']);
    if (! is_null($materiais[$pos]['trp'])) {
        $materiais[$pos]['trp'] = converte_valor_estoques($materiais[$pos]['trp']);
        // Na regra o valor estimado deveria ser preenchido, mas isso gera um problema.
        // O valor TRP pode ser alterado por outros usuários antes da SCC ser salva, o que, ao apertar o botáo incluir, alteraria o valor estimado
        // sem o usuário saber
        /*
         * if (is_null($materiais[$pos]["valorEstimado"]) or moeda2float($materiais[$pos]["valorEstimado"])==0) {
         * $materiais[$pos]["valorEstimado"] = $materiais[$pos]["trp"];
         * }
         */
    }

    // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
    if ($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
        $materiais[$pos]['fornecedor'] = $CnpjFornecedor;
    }
    // [/CUSTOMIZAÇÃO]

    // reservas (bloqueios ou dotações)
    /*
     * $materiais[$pos]["reservas"] = array();
     * $posReserva =-1;
     * if (!is_null($MaterialBloqueioItem[$pos])) {
     * foreach ($MaterialBloqueioItem[$pos] as $bloqueio) {
     * $posReserva ++;
     * $materiais[$pos]["reservas"][$posReserva] = $bloqueio;
     * }
     * }
     */
}

// Pegando os dados dos servicos enviados por POST
$QuantidadeServico = count($ServicoCod);

for ($itr = 0; $itr < $QuantidadeServico; ++ $itr) {
    $sql = ' SELECT m.eservpdesc
                 FROM SFPC.TBservicoportal m
                WHERE m.cservpsequ = ' . $ServicoCod[$itr] . '
    ';

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
    }
    $Linha = $res->fetchRow();
    $Descricao = $Linha[0];

    $pos = count($servicos);

    // preenchendo valores padrões caso seja nulo, para não ocorrer erro.
    if (is_null($ServicoQuantidade[$itr]) or $ServicoQuantidade[$itr] == '') {
        $ServicoQuantidade[$itr] = '0,0000';
    }
    if (is_null($ServicoValorEstimado[$itr]) or $ServicoValorEstimado[$itr] == '') {
        $ServicoValorEstimado[$itr] = '0,0000';
    }
    if (is_null($ServicoQuantidadeExercicio[$itr]) or $ServicoQuantidadeExercicio[$itr] == '') {
        $ServicoQuantidadeExercicio[$itr] = '0,0000';
    }
    if (is_null($ServicoTotalExercicio[$itr]) or $ServicoTotalExercicio[$itr] == '') {
        $ServicoTotalExercicio[$itr] = '0,0000';
    }

    if(empty($ServicoVitescunit[$itr])) {
        $ServicoVitescunit[$itr] = '0,0000';
    }
    
    $servicos[$pos]['posicao'] = $pos;
    $servicos[$pos]['posicaoItem'] = $pos + 1; // posição mostrada na tela
    $servicos[$pos]['tipo'] = TIPO_ITEM_SERVICO;
    $servicos[$pos]['codigo'] = $ServicoCod[$itr];
    $servicos[$pos]['codigo_item'] = $ServicoCodItem[$itr]; // $ServicoCodigoItem
    $servicos[$pos]['descricao'] = $Descricao;
    $servicos[$pos]['descricaoDetalhada'] = strtoupper(trim($ServicoDescricaoDetalhada[$itr]));
    if (is_null($ServicoCheck[$itr]) or ! $ServicoCheck[$itr]) {
        $servicos[$pos]['check'] = false;
    } else {
        $servicos[$pos]['check'] = true;
    }
    
    $servicos[$pos]['quantidade'] = $ServicoQuantidade[$itr];
    $servicos[$pos]['vitescunit'] = $ServicoVitescunit[$itr];
    $servicos[$pos]['valorEstimado'] = $ServicoValorEstimado[$itr];
    // valores em float para uso em funções
    $servicos[$pos]['quantidadeItem'] = moeda2float($ServicoQuantidade[$itr]);
    $servicos[$pos]['valorItem'] = moeda2float($ServicoValorEstimado[$itr]);

    $servicos[$pos]['quantidadeExercicio'] = $ServicoQuantidadeExercicio[$itr];
    $servicos[$pos]['fornecedor'] = $ServicoFornecedor[$itr];
    $servicos[$pos]['isObras'] = isObras($db, $servicos[$pos]['codigo'], TIPO_ITEM_SERVICO);

    if (moeda2float($servicos[$pos]['quantidade']) == 1) {
        $servicos[$pos]['totalExercicio'] = $ServicoTotalExercicio[$itr];
    } else {
        $servicos[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($servicos[$pos]['quantidadeExercicio']) * moeda2float($servicos[$pos]['valorEstimado']));
    }
    /*
     * $servicos[$pos]["reservas"] = array();
     * $posReserva =-1;
     * if (!is_null($ServicoBloqueioItem[$pos])) {
     * foreach ($ServicoBloqueioItem[$pos] as $bloqueio) {
     * $posReserva ++;
     * $servicos[$pos]["reservas"][$posReserva] = $bloqueio;
     * }
     * }
     */
}
// Pegando os materiais e serviços sendo incluídos via SESSION (janela de seleção de material/serviço) #
if (count($_SESSION['item']) != 0) {
    // [CUSTOMIZAÇÃO]
    // if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S" && $sequencialIntencao != "" && $anoIntencao != "") {
        // $servicos = array();
        // $materiais = array();
        // }
        // [/CUSTOMIZAÇÃO]
        
    sort($_SESSION['item']);
    for ($i = 0; $i < count($_SESSION['item']); ++ $i) {
        $DadosSessao = explode($SimboloConcatenacaoArray, $_SESSION['item'][$i]);
        $codigos = explode('#', $DadosSessao[1]);
        if(is_array($codigos) && count($codigos) == 2) {
            $ItemCodigo = $codigos[0];
            $codigoItemAta = $codigos[1];
        } else {
            $ItemCodigo = $DadosSessao[1];
            $codigoItemAta = '';
        }
            
        $ItemTipo = $DadosSessao[3];
        
        if ($ItemTipo == 'M') {
            $CnpjFornecedor = (!empty($DadosSessao[9])) ? $DadosSessao[9] : '';
            // verificando se item já existe
            
            $itemJaExiste = false;            
            $qtdeMateriais = count($materiais);             
            $dataMinimaValidaTrp = prazoValidadeTrp($db,$TipoCompra)->format('Y-m-d');
            for ($i2=0; $i2<$qtdeMateriais; $i2++) {

                // Verificar pelo código do item ou pelo código do item na ata
                if(!empty($codigoItemAta)) {
                    if ($codigoItemAta == $materiais[$i2]["codigo_item"]) {
                        $materiais[$i2]['quantidade'] = $DadosSessao[7];
                        $materiais[$i2]['vitescunit'] = $DadosSessao[10];
                        $materiais[$i2]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                        $itemJaExiste = true;
                    }
                } else {
                    if ($ItemCodigo == $materiais[$i2]["codigo"]) {
                        $materiais[$i2]['quantidade'] = $DadosSessao[7];
                        $materiais[$i2]['vitescunit'] = $DadosSessao[10];
                        $materiais[$i2]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                        $itemJaExiste = true;
                    }
                }
            }            
            
            // incluindo item
            if (!$itemJaExiste) {
                $sql = ' SELECT m.ematepdesc, u.eunidmsigl FROM SFPC.TBmaterialportal m, SFPC.TBunidadedemedida u WHERE m.cmatepsequ = ' . $ItemCodigo . ' and u.cunidmcodi = m.cunidmcodi ';

                $res = $db->query($sql);
                if (PEAR::isError($res)) {
                    EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
                }
                $Linha = $res->fetchRow();
                $MaterialDescricao = $Linha[0];
                $MaterialUnidade = $Linha[1];

                $pos = count($materiais);
                $materiais[$pos] = array();
                $materiais[$pos]['tipo'] = TIPO_ITEM_MATERIAL;
                $materiais[$pos]['codigo'] = $ItemCodigo;
                $materiais[$pos]['codigo_item'] = $codigoItemAta;
                $materiais[$pos]['descricao'] = $MaterialDescricao;
                /*
                * adiciona descricao detalhada
                */
                $materiais[$pos]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                $materiais[$pos]['unidade'] = $MaterialUnidade;

                $materiais[$pos]['check'] = false;
                $materiais[$pos]['quantidade'] = $DadosSessao[7];
                $materiais[$pos]['valorEstimado'] = str_replace('.', ',', $DadosSessao[4]);

                // valores em float para uso em funções
                $materiais[$pos]['quantidadeItem'] = 0;
                $materiais[$pos]['valorItem'] = $DadosSessao[4];
                $materiais[$pos]['quantidadeExercicio'] = '0,0000';
                $materiais[$pos]['totalExercicio'] = '0,0000';
                $materiais[$pos]['marca'] = $DadosSessao[5];
                $materiais[$pos]['modelo'] = $DadosSessao[6];
                $materiais[$pos]['fornecedor'] = (!empty($DadosSessao[9])) ? $DadosSessao[9] : '';
                $materiais[$pos]['vitescunit'] = (!empty($DadosSessao[10])) ? $DadosSessao[10] : '0,0000';
                $materiais[$pos]['posicao'] = $pos;
                $materiais[$pos]['posicaoItem'] = $pos + 1; // posição mostrada na tela
                                                            // $materiais[$pos]["reservas"] = array();
                $materiais[$pos]['trp'] = calcularValorTrp($db, $TipoCompra, $materiais[$pos]['codigo']);
                $materiais[$pos]['isObras'] = isObras($db, $materiais[$pos]['codigo'], TIPO_ITEM_MATERIAL);

                if (! is_null($materiais[$pos]['trp'])) {
                    $materiais[$pos]['trp'] = converte_valor_estoques($materiais[$pos]['trp']);
                }
            }

            // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
            if ($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                $materiais[$pos]['fornecedor'] = $CnpjFornecedor;
            }
            // [/CUSTOMIZAÇÃO]
        } elseif ($ItemTipo == 'S') {
            $CnpjFornecedor = (!empty($DadosSessao[9])) ? $DadosSessao[9] : '';
            // verificando se item já existe
            
            $itemJaExiste = false;
            $qtdeServicos = count($servicos);
            
            for ($i2=0; $i2<$qtdeServicos; $i2++) {
                if(!empty($codigoItemAta)) {
                    if ($codigoItemAta == $servicos[$i2]["codigo_item"]) {
                        $servicos[$i2]['quantidade'] = $DadosSessao[7];
                        $servicos[$i2]['vitescunit'] = $DadosSessao[10];
                        $servicos[$i2]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                        $itemJaExiste = true;
                    }
                } else {
                    if ($ItemCodigo == $servicos[$i2]["codigo"]) {
                        $servicos[$i2]['quantidadeItem'] = $DadosSessao[7];
                        $servicos[$i2]['vitescunit'] = $DadosSessao[10];
                        $servicos[$i2]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                        $itemJaExiste = true;
                    }
                }
            }
            
            // incluindo item
            if (!$itemJaExiste) {
                $sql = ' select m.eservpdesc
                                from SFPC.TBservicoportal m
                                where m.cservpsequ = ' . $ItemCodigo . '
                    ';

                $res = $db->query($sql);
                if (PEAR::isError($res)) {
                    EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
                }
                $Linha = $res->fetchRow();
                $Descricao = $Linha[0];
                $pos = count($servicos);
                $servicos[$pos] = array();
                $servicos[$pos]['tipo'] = TIPO_ITEM_SERVICO;
                $servicos[$pos]['codigo'] = $ItemCodigo;
                $servicos[$pos]['codigo_item'] = $codigoItemAta;
                $servicos[$pos]['descricao'] = $Descricao;
                $servicos[$pos]['descricaoDetalhada'] = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
                $servicos[$pos]['check'] = false;
                $servicos[$pos]['quantidade'] = $DadosSessao[7];
                $servicos[$pos]['valorEstimado'] = str_replace('.', ',', $DadosSessao[4]);
                // valores em float para uso em funções
                $servicos[$pos]['quantidadeItem'] = 0;
                $servicos[$pos]['valorItem'] = $DadosSessao[4];
                $servicos[$pos]['quantidadeExercicio'] = '0';
                $servicos[$pos]['totalExercicio'] = '0,0000';
                $servicos[$pos]['fornecedor'] = (!empty($DadosSessao[9])) ? $DadosSessao[9] : '';
                $servicos[$pos]['vitescunit'] = (!empty($DadosSessao[10])) ? $DadosSessao[10] : '0,0000';
                $servicos[$pos]['posicao'] = $pos;
                $servicos[$pos]['posicaoItem'] = $pos + 1; // posição mostrada na tela
                $servicos[$pos]['isObras'] = isObras($db, $servicos[$pos]['codigo'], TIPO_ITEM_SERVICO);                
            }
        } else {
            EmailErro('Erro', __FILE__, __LINE__, 'ItemTipo não é nem material nem serviço! /n var SimboloConcatenacaoArray = ' . $SimboloConcatenacaoArray . '');
        }
    }
    unset($_SESSION['item']);
}

$qtdeMateriais = 0;
if (! is_null($materiais)) {
    $qtdeMateriais = count($materiais);
}
$qtdeServicos = 0;
if (! is_null($servicos)) {
    $qtdeServicos = count($servicos);
}

$materiaisSARP = CR92::retornarItensMateriasAtaSarp();
$servicosSARP = CR92::retornarItensServicoAtaSarp();
if (! empty($materiaisSARP)) {
    $materiais = $materiaisSARP;
}

if (! empty($servicosSARP)) {
    echo "SARP";exit;
    $servicos = $servicosSARP;
}
// Verificando se o valor de 'GeraContrato' é automático (no campo SFPC.TBgruposubelementodespesa.fgrusecont)
if (! $ocultarCampoGeraContrato and ($qtdeMateriais + $qtdeServicos) > 0) {
    $gruposMateriaisServicos = '';
    $colocarVirgula = false;
    if (! is_null($materiais)) {
        foreach ($materiais as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item['codigo'], TIPO_ITEM_MATERIAL);
            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }
    if (! is_null($servicos)) {
        foreach ($servicos as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item['codigo'], TIPO_ITEM_SERVICO);
            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }
    $sql = ' select count(fgrusecont)
               from SFPC.TBgruposubelementodespesa
              where cgrumscodi in (' . $gruposMateriaisServicos . ")
                and fgrusecont = 'S' ";
    $quantidadeSubElementoComGeraContrato = resultValorUnico(executarSQL($db, $sql));
    if ($quantidadeSubElementoComGeraContrato > 0) {
        // Preenchendo contrato
        $GeraContrato = 'S';
        $preencherCampoGeraContrato = true;
        $ocultarCampoGeraContrato = false;
    }
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == 'Imprimir' && $situacaoImp <> 10) {
    $Url = 'RelAcompanhamentoSCCPdf.php';
    
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header('Location: ' . $Url);
    exit();
}

// Ano da Requisição Ano Atual #
$AnoSolicitacao = date('Y');
$DataAtual = date('Y-m-d');

/*
 * if ( ($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')) {
 * $Mens = 1;
 * $Tipo = 2;
 * $Mensagem .= "O Usuário do grupo INTERNET ou corporativo não pode fazer Solicitação de Material";
 * }
 */

$anoAtual = date('Y');

// verificar se SCC está em uma situação que não pode ser alterada
if ($Botao == 'Manter' or $Botao == 'Excluir') {

    $sql = " SELECT SOL.CORGLICODI, SOL.CSITSOCODI, SOL.CSOLCOTIPCOSEQU, SOL.CTPCOMCODI, CS.CDOCPCSEQU
            FROM SFPC.TBSOLICITACAOCOMPRA SOL
            LEFT OUTER JOIN SFPC.TBCONTRATOSFPC CS ON SOL.CSOLCOSEQU = CS.CSOLCOSEQU
            WHERE
                SOL.CSOLCOSEQU = $Solicitacao ";

    $linha = resultLinhaUnica(executarTransacao($db, $sql));
    $Orgao = $linha[0];
    $SituacaoCompra = $linha[1];
    $Numero = $linha[2];
    $tipoCompra = $linha[3];
    $idContratoSolicitacao = $linha[4];
    $alterarSCC = false;

    // OBS.: TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO == PSE GERADA
    $arrayVerificacaoSarp = array(
        TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO,
        TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO
    );
    $arrayVerificacaoLicitacao = $arrayVerificacaoSarp;

    $arrayTipoCompra = array(
        TIPO_COMPRA_DIRETA,
        TIPO_COMPRA_DISPENSA,
        TIPO_COMPRA_INEXIGIBILIDADE
    );

    if (($SituacaoCompra == TIPO_SITUACAO_SCC_EM_ANALISE) or ($SituacaoCompra == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO)) {
        $alterarSCC = true;
    } elseif ($tipoCompra == TIPO_COMPRA_SARP && in_array($SituacaoCompra, $arrayVerificacaoSarp)) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (SARP - PSE Gerada). SCC='" . $Solicitacao . "'");
    } elseif ($tipoCompra == TIPO_COMPRA_LICITACAO && in_array($SituacaoCompra, $arrayVerificacaoLicitacao)) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Licitação - PSE Gerada). SCC='" . $Solicitacao . "'");
    } elseif (in_array($tipoCompra, $arrayTipoCompra) && $SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO && $idContratoSolicitacao != '') { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Contrato associado a solicitação). SCC='" . $Solicitacao . "'");
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
        if (! hasPSEImportadaSofin($db, $Solicitacao)) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois o SOFIN já efetuou a importação dos dados da PSE. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO) {
        if (! hasSSCContrato($db, $Solicitacao)) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois já está relacionada com Contrato. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP) {
        $alterarSCC = true;
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO) {
        if (administracaoOrgao($db, $Orgao) == 'I') {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois não está mais na fase de análise. SCC='" . $Solicitacao . "'");
        }
    } else {
        if (($SituacaoCompra == TIPO_SITUACAO_SCC_EM_LICITACAO) && $_SESSION['_cperficodi_'] == 2) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois está em uma situação que não pode ser alterada. SCC='" . $Solicitacao . "'");
        }
    }
}

if ($Botao == 'Excluir' and $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $sql = "
            SELECT csitsocodi
              FROM sfpc.tbsolicitacaocompra
             WHERE csolcosequ = $Solicitacao ";
    $situacao = resultValorUnico(executarSQL($db, $sql));
    $sql = ' UPDATE sfpc.tbsolicitacaocompra
               SET
                   cusupocod1 = ' . $_SESSION['_cusupocodi_'] . ',
                   tsolcoulat = now(),
                   csitsocodi = ' . $TIPO_SITUACAO_SCC_CANCELADA . "
             WHERE
                   csolcosequ = $Solicitacao ";
    executarTransacao($db, $sql);
    $sql = "
            INSERT INTO sfpc.tbhistsituacaosolicitacao(
        csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
            VALUES (
    $Solicitacao, now(), " . $TIPO_SITUACAO_SCC_CANCELADA . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()
        );
    ';
    executarTransacao($db, $sql);
    finalizarTransacao($db);
    $Mensagem = 'Solicitação cancelada com Sucesso';
    header('Location: ' . $programaSelecao . '?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    exit();
} elseif ($Botao == 'Incluir' or $Botao == 'Manter' or $Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
    $Mens = 0;
    $Mensagem = '';

    if (empty($materiais) === false) {
        foreach ($materiais as $mat) {
            if ((hasIndicadorCADUM($db, (int) $mat['codigo']) && $mat['descricaoDetalhada'] == '') && (($Botao != 'Rascunho') && ($Botao != 'ManterRascunho')) ) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela = $mat['posicao'] + 1;

                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela = null;
            }

            $DescDetMat = trim($mat['descricaoDetalhada']);

            if (strlen($DescDetMat) > 1000 && strlen(trim($DescDetMat)) > 1000) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela = $mat['posicao'] + 1;

                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela acima do limite de 1000 caracteres</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela = null;
            }
        }
    }

    if ($CentroCusto == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"CentroCustoLink\").focus();' class='titulo2'>Centro de Custo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }
    if ($TipoCompra == '') {
        adicionarMensagem("<a href='javascript:formulario.TipoCompra.focus();' class='titulo2'> Tipo de Compra </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }

    if (($TipoCompra == TIPO_COMPRA_SARP) && is_null($SarpTipo)) {
        adicionarMensagem("<a href='javascript:formulario.Sarp.focus();' class='titulo2'>Tipo Sarp</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }

    if ($Botao != 'Rascunho' and $Botao != 'ManterRascunho') {
        // fornecedor
        if (! $ocultarCampoFornecedor and ! is_null($CnpjFornecedor) and $CnpjFornecedor != '') {
            $retorno = validaFormatoCNPJ_CPF($CnpjFornecedor);
            if (! $retorno[0]) {
                $msgAux = $retorno[1];
                adicionarMensagem("<a href='javascript:formulario.CnpjFornecedor.focus();' class='titulo2'>erro em campo de fornecedor com a mensagem '$msgAux'</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        if ($Objeto == '') {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        } elseif ($NCaracteresObjeto > $tamanhoObjeto) {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto menor que " . $tamanhoObjeto . ' caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if ($NCaracteresObservacao > '200') {
            adicionarMensagem("<a href='javascript:formulario.Observacao.focus();' class='titulo2'>Observação menor que 200 caracteres</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (! $ocultarCampoJustificativa) {
            /*
             * if ($Justificativa == "") {
             * adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>Justificativa</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
             * }else
             */
            if ($NCaracteresJustificativa > $tamanhoJustificativa) {
                adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>Justificativa menor que " . $tamanhoJustificativa . ' caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        if (! $ocultarCampoGeraContrato and is_null($GeraContrato)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"GeraContrato\"][0].focus();' class='titulo2'> Gera Contrato </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (! $ocultarCampoRegistroPreco and is_null($RegistroPreco)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"RegistroPreco\"][0].focus();' class='titulo2'> Registro de Preço </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        // if (!$ocultarCampoSARP and is_null($Sarp)) {
        // adicionarMensagem("<a href='javascript:formulario.elements[\"Sarp\"][0].focus();' class='titulo2'> Tipo de Sarp </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        // }
        if (! $ocultarCampoLegislacao) {
            if (is_null($TipoLei) or $TipoLei == '') {
                adicionarMensagem("<a href='javascript:formulario.TipoLei.focus();' class='titulo2'> Tipo de Lei </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if (is_null($Lei) or $Lei == '') {
                adicionarMensagem("<a href='javascript:formulario.Lei.focus();' class='titulo2'> Lei </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if (is_null($Artigo) or $Artigo == '') {
                adicionarMensagem("<a href='javascript:formulario.Artigo.focus();' class='titulo2'> Artigo </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
            if (is_null($Inciso) or $Inciso == '') {
                adicionarMensagem("<a href='javascript:formulario.Inciso.focus();' class='titulo2'> Inciso/ Parágrafo </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        if (! $ocultarCampoDataDOM) {
            if (is_null($DataDom) or $DataDom == '') {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } elseif (ValidaData($DataDom)) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> '" . ValidaData($DataDom) . "' em Data de publicação do DOM</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } elseif (DataInvertida($DataDom) > DataAtual()) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM menor ou igual à data atual </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            } else {
                $dataHoje = new DateTime();
                $dataDOM = new DateTime(DataInvertida($DataDom));
                $data_vigencia = new DateTime();
                $data_vigencia->setDate($dataDOM->format('Y'), $dataDOM->format('m'), $dataDOM->format('d') + prazoDOM($db));
                // echo $data_vigencia->format("Y-m-d");
                // echo " ! >= ";
                // echo $dataHoje->format("Y-m-d");
                // echo " --- ";
                // echo (!($data_vigencia->format("Y-m-d") >= $dataHoje->format("Y-m-d")));
                if (! ($data_vigencia->format('Y-m-d') >= $dataHoje->format('Y-m-d'))) {
                    adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'>A Dispensa/Inexigibilidade extrapola a data limite a partir da publicação no DOM</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
                // exit;
            }
        }

        $item = empty($MaterialCod) ? $ServicoCod : $MaterialCod;
        $isMaterial = empty($MaterialCod);

        $valido = true;

        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        // Se não escolheu nenhum item #
        if (count($MaterialCod) == 0 and count($ServicoCod) == 0) {
            adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Pelo menos um item de material ou serviço</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        $fornecedorCompra = null; // verificando se há mais de 1 fornecedor (tanto para materiais quanto servicos)
        $fornecedorCompraSetado = false;
        $elementoDespesaItem = null;
        $posElementoDespesa = - 1;

        // [issue 44 - 14/11/2014 - Pitang]

        // $hasGrupoEspecial = false;

        // # verificar se há só itens de materiais ou só itens de serviços. nao deve permitir os 2
        // # exceto quando forem usados grupos com subelementos especiais, definidos nos parâmetros gerais
        // if (!is_null($materiais) and ! is_null($servicos) and count($materiais) > 0 and count($servicos) > 0) {
        // foreach ($materiais as $material) {
        // $grupo = getGrupoDeMaterialServico($db, $material['codigo'], TIPO_ITEM_MATERIAL);
        // $elementoDespesaArray = getSubElementoDespesaDeGrupoMaterial($db, $anoAtual, $grupo);
        // foreach ($subElementosEspeciais as $subElementoEspecial) {
        // $subElementoEspecial = trim($subElementoEspecial);
        // if ($subElementoEspecial == $elementoDespesaArray['elementoDespesa']) {
        // $hasGrupoEspecial = true;
        // }
        // }
        // }
        // foreach ($servicos as $servico) {
        // $grupo = getGrupoDeMaterialServico($db, $servico['codigo'], TIPO_ITEM_SERVICO);
        // $elementoDespesaArray = getSubElementoDespesaDeGrupoMaterial($db, $anoAtual, $grupo);
        // foreach ($subElementosEspeciais as $subElementoEspecial) {
        // $subElementoEspecial = trim($subElementoEspecial);
        // if ($subElementoEspecial == $elementoDespesaArray['elementoDespesa']) {
        // $hasGrupoEspecial = true;
        // }
        // }
        // }
        // if (!$hasGrupoEspecial) {
        // mostrarMensagemErroUnica("Solicitação de compra deve conter apenas itens de materiais ou itens de serviços exceto quando conter grupos com subelementos especiais");
        // }
        // }
        // [/ issue 44 - 14/11/2014 - Pitang]
        if (! is_null($materiais)) {
            foreach ($materiais as $material) {
                if (! $GLOBALS['BloquearMensagens']) {
                    $pos = $material['posicao'];
                    $ord = $pos + 1;
                    if ($material['quantidade'] == '' or moeda2float($material['quantidade']) == 0 &&($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    if ((!isset($_SESSION['tipoControle']) || $_SESSION['tipoControle'] != 2) && ($material['valorEstimado'] == '' or moeda2float($material['valorEstimado']) == 0)) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    if (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0 && ($material['vitescunit'] == '' || moeda2float($material['vitescunit']) == 0))  {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialVitescunit[" . $pos . "]').focus();\" class='titulo2'> Valor solicitado do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    if (! $ocultarCampoFornecedor && ($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                        if ($material['marca'] == '' && ($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialMarca[" . $pos . "]').focus();\" class='titulo2'> Marca do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                        if ($material['modelo'] == ''&&($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialModelo[" . $pos . "]').focus();\" class='titulo2'> Modelo do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }

                        if ($material['fornecedor'] == '' && ($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            // pegando 1o fornecedor dos itens
                            if (! $fornecedorCompraSetado) {
                                $fornecedorCompra = $material['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            $retorno = validaFormatoCNPJ_CPF($materiais[$pos]['fornecedor']);
                            if (! $retorno[0]) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> falha na validação do fornecedor do material ord " . ($ord) . ' com a seguinte mensagem:' . $retorno[1] . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            } else {
                                $materiais[$pos]['fornecedor'] = $retorno[1];
                                if ($isFornecedorUnico and $material['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                                // checar sicref e debito mercantil
                                try {
                                    validaFornecedorItemSCC($db, $material['fornecedor'], $TipoCompra, $material['codigo'], TIPO_ITEM_MATERIAL);
                                } catch (ExcecaoPendenciasUsuario $e) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                            }
                        }
                    }

                    // if ($TipoCompra == TIPO_COMPRA_SARP) {
                    // if ($SarpTipo == 'C') {
                    // $valido = CR92::validarCondicaoSARPCarona($_SESSION ['UsuOrgLogado'], $_SESSION['ataCasoSARP'], $material['codigo'], true, $material['quantidade']);
                    // } else {
                    // $valido = CR92::validarCondicaoSARPParticpante($_SESSION ['UsuOrgLogado'], $_SESSION['ataCasoSARP'], $material['codigo'], true, $material['quantidade']);
                    // }
                    // if (!$valido) {
                    // adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Quantidade dos Itens da SCC to tipo Sarp Inválida</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    // }
                    // }

                    $valorTotalItem = moeda2float($material['quantidade']) * moeda2float($material['valorEstimado']);

                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        $varAux = trim($material['quantidadeExercicio']);
                        if ($material['quantidadeExercicio'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> Quantidade de exercício do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            if ($material['totalExercicio'] == '') {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> Valor de exercício do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            }
                        }

                        $valorTotalExercicioItem = moeda2float($material['totalExercicio']);

                        // if (moeda2float($material["quantidade"])<moeda2float($material["quantidadeExercicio"])) {
                        if (comparaFloat(moeda2float($material['quantidade']), '<', moeda2float($material['quantidadeExercicio']), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } elseif (comparaFloat($valorTotalItem, '<', $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            // exit;
                        }
                    }
                }
            }
        }

        if (! is_null($servicos)) {
            $posElementoDespesa = - 1;

            foreach ($servicos as $servico) {
                if (! $GLOBALS['BloquearMensagens']) {
                    $pos = $servico['posicao'];
                    $ord = $pos + 1;
                    if ($servico['quantidade'] == '' or moeda2float($servico['quantidade']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    $DescDet = trim($servico['descricaoDetalhada']);
                    if (! strlen($DescDet) > 0 && ! strlen(trim($DescDet)) > 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if (strlen($DescDet) > 1000 && strlen(trim($DescDet)) > 1000) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . ' acima do limite de 200 caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if ( (!isset($_SESSION['tipoControle']) || $_SESSION['tipoControle'] != 2) && ($servico['valorEstimado'] == '' || moeda2float($servico['valorEstimado']) == 0)) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do servico ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0 && ($servico['vitescunit'] == '' || moeda2float($servico['vitescunit']) == 0))  {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoVitescunit[" . $pos . "]').focus();\" class='titulo2'> Valor solicitado do servico ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    if (! $ocultarCampoFornecedor &&($Lei != 1  && $Artigo != 14133 && $Artigo != 75 && ($Inciso != 69 || 70))) {
                        if ($servico['fornecedor'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            // pegando 1o fornecedor dos itens
                            if (! $fornecedorCompraSetado) {
                                $fornecedorCompra = $servico['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            $retorno = validaFormatoCNPJ_CPF($servicos[$pos]['fornecedor']);
                            if (! $retorno[0]) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>falha na validação do fornecedor do serviço ord " . ($ord) . ' com a seguinte mensagem:' . $retorno[1] . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                            } else {
                                $servicos[$pos]['fornecedor'] = $retorno[1];
                                if ($isFornecedorUnico and $servico['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                                // checar sicref e debito mercantil
                                try {
                                    validaFornecedorItemSCC($db, $servico['fornecedor'], $TipoCompra, $servico['codigo'], TIPO_ITEM_SERVICO);
                                } catch (ExcecaoPendenciasUsuario $e) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                                }
                            }
                        }
                    }
                    $valorTotalItem = moeda2float($servico[$pos]['quantidade']) * moeda2float($servico[$pos]['valorEstimado']);
                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        // if ($servico['quantidadeExercicio']=="" or moeda2float($servico['quantidadeExercicio'])==0) {
                        // adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[".$pos."]').focus();\" class='titulo2'> Quantidade de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        // } elseif ($servico['totalExercicio']=="" or moeda2float($servico['totalExercicio'])==0) {
                        // adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[".$pos."]').focus();\" class='titulo2'> Valor de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        // }
                        $valorTotalExercicioItem = moeda2float($servico[$pos]['totalExercicio']);
                        if (comparaFloat(moeda2float($material['quantidade']), '<', moeda2float($material['quantidadeExercicio']), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } elseif (comparaFloat($valorTotalItem, '<', $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                    }
                }
            }
        }
        $permitirSemBloqueios = false;
    } else { // Caso seja rascunho
        $permitirSemBloqueios = true;
    }

    if ($isDotacao) {
        $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_DOTACAO;
    } else {
        $tipoReserva = TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO;
    }
    $itensSCC = array_merge((array) $materiais, (array) $servicos);

    /*
     * if (count($itensSCC)==0 or is_null($itensSCC)) {
     * adicionarMensagem("'Valor total de exercício maior que valor total do item' no serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
     * }
     */
    if ((is_null($itensSCC) or count($itensSCC) == 0) and ! is_null($Bloqueios) and count($Bloqueios) > 0) {
        adicionarMensagem('Não é possível adicionar Bloqueios ou Dotações em SCCs que não tenham itens', $GLOBALS['TIPO_MENSAGEM_ERRO']);
    } else {
        if ($Botao == 'Incluir' or $Botao == 'Manter') {
            // TRECHO SENDO MANTIDO POR HERALDO BOTELHO
            $campoDotacaoNulo = campoDotacaoNulo();
            if (! $campoDotacaoNulo or $TipoCompra != 2 or $RegistroPreco != 'S') {
                $tipo_controle_ = isset($_SESSION['tipoControle']) ? $_SESSION['tipoControle'] : '0';
                $field_ = $tipo_controle_ == 1 ? 'vitescunit' : 'valorItem';
                if($TipoCompra != TIPO_COMPRA_SARP){
                    validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $Bloqueios, $itensSCC, 'BloqueioTodos', $TipoCompra, $RegistroPreco, $field_);
                }
                
            }
        }
    }

    // [CUSTOMIZAÇÃO] - Conforme comentário na linha 1508 "rascunho também deve gravar licitação de SARP"
    // então é realizada validação de preenchimento de dados para o tipo SARP
    if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }
    
    if($Botao == 'Rascunho') {
        if (! $ocultarCampoProcessoLicitatorio and (!isset($_SESSION['ataCasoSARP']))) {
            adicionarMensagem("<a href='javascript:;' class='titulo2'>Ata Registro de Preços</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }

    if ($GLOBALS['Mens'] != 1) {
        if ((($Botao == 'Incluir' or $Botao == 'Rascunho') and $acaoPagina == ACAO_PAGINA_INCLUIR) or (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $acaoPagina == ACAO_PAGINA_MANTER)) {
            $ano = date('Y');
            // Pegando dados de órgão e unidade pelo centro de custo
            $sql = " SELECT corglicodi, ccenpocorg, ccenpounid
                     FROM sfpc.tbcentrocustoportal
                     WHERE ccenposequ = $CentroCusto ";
            $Linha = resultLinhaUnica(executarTransacao($db, $sql));
            $Orgao = $Linha[0];
            $OrgaoSofin = $Linha[1];
            $UnidadeSofin = $Linha[2];
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                // Pegando ano, órgão e tipo para ver se sequencial da SCC deve mudar, e vendo se a situação da SCC
                $sql = " SELECT corglicodi, asolcoanos, ctpcomcodi, csolcotipcosequ, csolcocodi, CSITSOCODI
                         FROM sfpc.tbsolicitacaocompra
                         WHERE csolcosequ = $Solicitacao ";

                $linha = resultLinhaUnica(executarTransacao($db, $sql));
                $OrgaoAntes = $linha[0];
                $AnoAntes = $linha[1];
                $TipoCompraAntes = $linha[2];
                $sequencialPorAnoOrgaoTipoAntes = $linha[3];
                $sequencialPorAnoOrgaoAntes = $linha[4];
                $SituacaoCompraAntes = $linha[5];
                $ano = $AnoAntes; // em manter o ano nao deve mudar
                                  // aceitar Cadastramento apenas para rascunho!
                assercao(($SituacaoCompraAntes != TIPO_SITUACAO_SCC_EM_CADASTRAMENTO and $Botao != 'Rascunho' and $Botao != 'ManterRascunho') or $SituacaoCompraAntes == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO, "ERRO: Tentando alterar SCC já incluída para 'EM CADASTRAMENTO'. Abortando.");
            }
            // Pegando sequencial da SCC pelo ano, orgao e tipo
            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano and $TipoCompraAntes == $TipoCompra) {
                $sequencialPorAnoOrgaoTipo = $sequencialPorAnoOrgaoTipoAntes; // nao mudar o sequencial caso o ano e orgao e tipo nao mudaram
            } else {
                // para inclusão ou mudança de orgao ano ou tipo, mudar sequencial
                $sql = ' SELECT max(csolcotipcosequ)
                         FROM sfpc.tbsolicitacaocompra
                         WHERE corglicodi = ' . $Orgao . '
                                AND asolcoanos = ' . date('Y') . '
                                AND ctpcomcodi = ' . $TipoCompra . ' ';

                $sequencialPorAnoOrgaoTipo = resultValorUnico(executarTransacao($db, $sql));
                if (is_null($sequencialPorAnoOrgaoTipo) or $sequencialPorAnoOrgaoTipo == '') {
                    $sequencialPorAnoOrgaoTipo = 1;
                } else {
                    ++ $sequencialPorAnoOrgaoTipo;
                }
            }

            // Pegando sequencial da SCC pelo ano e orgao
            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano) {
                $sequencialPorAnoOrgao = $sequencialPorAnoOrgaoAntes; // nao mudar o sequencial caso o ano e orgao nao mudaram
            } else {
                // para inclusão ou mudança de orgao ou ano, mudar sequencial
                $sql = ' SELECT max(csolcocodi)
                         FROM sfpc.tbsolicitacaocompra
                         WHERE corglicodi = ' . $Orgao . ' AND asolcoanos = ' . date('Y') . '
                    ';
                $sequencialPorAnoOrgao = resultValorUnico(executarTransacao($db, $sql));
                if (is_null($sequencialPorAnoOrgao) or $sequencialPorAnoOrgao == '') {
                    $sequencialPorAnoOrgao = 1;
                } else {
                    ++ $sequencialPorAnoOrgao;
                }
            }

            $strCodigoSolicitacao = '';

            // tratando dados para SQL
            if ($ocultarCampoSARP /* or $Botao == "Rascunho" or $Botao == "ManterRascunho" */) { // rascunho também deve gravar licitação de SARP
                $strNumProcessoSARP = 'null';
                $strGrupoEmpresaCodigoSARP = 'null';
                $strAnoProcessoSARP = 'null';
                $strComissaoCodigoSARP = 'null';
                $strOrgaoLicitanteCodigoSARP = 'null';
            } else {
                $strNumProcessoSARP = $NumProcessoSARP;
                $strGrupoEmpresaCodigoSARP = (!empty($GrupoEmpresaCodigoSARP)) ? $GrupoEmpresaCodigoSARP : 'null';
                $strAnoProcessoSARP = $AnoProcessoSARP;
                $strComissaoCodigoSARP = (!empty($ComissaoCodigoSARP)) ? $ComissaoCodigoSARP : 'null';
                $strOrgaoLicitanteCodigoSARP = (!empty($OrgaoLicitanteCodigoSARP)) ? $OrgaoLicitanteCodigoSARP : 'null';
            }
            if ($ocultarCampoLegislacao or is_null($Inciso) or $Inciso == '') {
                $strTipoLei = 'null';
                $strLei = 'null';
                $strArtigo = 'null';
                $strInciso = 'null';
            } else {
                $strTipoLei = "'" . $TipoLei . "'";
                $strLei = "'" . $Lei . "'";
                $strArtigo = "'" . $Artigo . "'";
                $strInciso = "'" . $Inciso . "'";
            }
            if ($ocultarCampoGeraContrato or is_null($GeraContrato)) {
                $strGeraContrato = 'null';
            } else {
                if ($preencherCampoGeraContrato) {
                    $strGeraContrato = "'S'";
                } else {
                    $strGeraContrato = "'" . $GeraContrato . "'";
                }
            }
            if ($ocultarCampoSARP) {
                $strSarp = 'null';
            } else {
                $strSarp = "'" . $Sarp . "'";
            }
            if ($ocultarCampoRegistroPreco or is_null($RegistroPreco)) {
                $strRegistroPreco = 'null';
            } else {
                $strRegistroPreco = "'" . $RegistroPreco . "'";
            }
            if ($ocultarCampoDataDOM or is_null($DataDom) or $DataDom == '') {
                $strDataDom = 'null';
            } else {
                $strDataDom = "'" . DataInvertida($DataDom) . "'";
            }
            if ($ocultarCampoJustificativa  == '' or is_null($Justificativa)) {
                $strJustificativa = 'null';
            } else {
                $strJustificativa = "'" . $Justificativa . "'";
            }

            // Verificando a situação da solicitação
            $situacaoSolicitacao = - 1;
            $fluxoVerificarGerarContrato = false;

            // Encontrando situação da solicitação
            if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
                $situacaoSolicitacao = $TIPO_SITUACAO_SCC_EM_CADASTRAMENTO;
            } elseif ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                $fluxoVerificarGerarContrato = true;
            } elseif ($TipoCompra == TIPO_COMPRA_LICITACAO) {
                // verificando se é adm direta.
                $sql = ' SELECT forglitipo
                         FROM sfpc.tborgaolicitante
                         WHERE corglicodi = ' . $Orgao . '
                    ';
                $administracao = resultValorUnico(executarTransacao($db, $sql));
                if ($administracao == 'D') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_EM_ANALISE;
                } elseif ($administracao == 'I') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO;
                } else {
                    assercao(false, 'Tipo de adiministração de órgão não reconhecido', $db);
                }
            } elseif ($TipoCompra == TIPO_COMPRA_SARP) {
                if (! isset($Solicitacao)) {
                    // é inclusão. SCC não é aprovada
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                } elseif ($Botao != 'Incluir' and isAutorizadoSarp($db, $Solicitacao)) {
                    $fluxoVerificarGerarContrato = true;
                } else {
                    // é alteração mas não foi autorizado
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                }
            } else {
                assercao(false, 'Tipo de compra não reconhecida', $db);
            }
            // direta, dispensa, inexibilidade ou (SARP autorizado tipo participante que não gera contrato)
            if ($fluxoVerificarGerarContrato) {
                if ($GeraContrato == 'S') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO;
                } else {
                    // Gera contrato = N
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO;
                }
            }

            assercao(($situacaoSolicitacao != - 1), 'Caso da situação de solicitação de compra não está sendo tratado.', $db);
            assercao(! is_null($situacaoSolicitacao), 'Erro em variável de situação de solicitação de compra. Variável nula. Motivo provável é se foi usado uma constante nula.', $db);

            $sequencialIntencao = !empty($sequencialIntencao) ? (int) $sequencialIntencao : 'null';
            $anoIntencao = !empty($anoIntencao) ? $anoIntencao : 'null';
            $sequencialSolicitacao = $Solicitacao;
            // iniciando inclusão ou alteração

            $Observacao = str_replace("'","''",trim($Observacao));
            $Observacao = RetiraAcentos($Observacao);
            $Objeto = str_replace("'","''",trim($Objeto));
            $Objeto = RetiraAcentos($Objeto);
            $Justificativa = str_replace("'","''",trim($Justificativa));
            $Justificativa = RetiraAcentos($Justificativa);

            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                $sql = ' UPDATE sfpc.tbsolicitacaocompra SET
                            corglicodi = ' . $Orgao . ',
                            csolcocodi = ' . $sequencialPorAnoOrgao . ',
                            ctpcomcodi = ' . $TipoCompra . ',
                            csolcotipcosequ = ' . $sequencialPorAnoOrgaoTipo . ',
                            ccenposequ = ' . $CentroCusto . ",
                            esolcoobse = '" . $Observacao . "',
                            esolcoobje = '" . $Objeto . "',
                            esolcojust = '" . $Justificativa . "',
                            clicpoproc = " . $strNumProcessoSARP . ',
                            alicpoanop = ' . $strAnoProcessoSARP . ',
                            cgrempcodi = ' . $strGrupoEmpresaCodigoSARP . ',
                            ccomlicodi = ' . $strComissaoCodigoSARP . ',
                            corglicod1 = ' . $strOrgaoLicitanteCodigoSARP . ',
                            dsolcodpdo = ' . $strDataDom . ',
                            ctpleitipo = ' . $strTipoLei . ',
                            cleiponume = ' . $strLei . ',
                            cartpoarti = ' . $strArtigo . ',
                            cincpainci = ' . $strInciso . ',
                            fsolcorgpr = ' . $strRegistroPreco . ",
                            fsolcorpcp = '" . $SarpTipo . "',
                            fsolcocont = " . $strGeraContrato . ',
                            tsolcoulat = now(),
                            csitsocodi = ' . $situacaoSolicitacao . ',
                            cintrpsequ = ' . $sequencialIntencao . ',
                            cintrpsano = ' . $anoIntencao;
                            
                if ($_SESSION['ataCasoSARP'] != null) {
                    $sql .= ', carpnosequ = ' . $_SESSION['ataCasoSARP'] . ' ';
                }
                $sql .= " WHERE csolcosequ = $Solicitacao";
                executarTransacao($db, $sql);
            } else {
                /* Higienizar as variáveis */
                //$Observacao = removeCaracteresEspeciais($Observacao);
                //$Objeto = removeCaracteresEspeciais($Objeto);
                //$Justificativa = removeCaracteresEspeciais($Justificativa);
                $Observacao = str_replace("'","''",trim($Observacao));
                $Observacao = RetiraAcentos($Observacao);
                $Objeto = str_replace("'","''",trim($Objeto));
                $Objeto = RetiraAcentos($Objeto);
                $Justificativa = str_replace("'","''",trim($Justificativa));
                $Justificativa = RetiraAcentos($Justificativa);

                $entidade = array();
                $entidade['corglicodi'] = (int) $Orgao;
                $entidade['asolcoanos'] = (int) $anoAtual;
                $entidade['csolcocodi'] = (int) $sequencialPorAnoOrgao;
                $entidade['ctpcomcodi'] = (int) $TipoCompra;
                $entidade['csolcotipcosequ'] = (int) $sequencialPorAnoOrgaoTipo;
                $entidade['tsolcodata'] = 'NOW()';
                $entidade['ccenposequ'] = (int) $CentroCusto;
                $entidade['esolcoobse'] = "'$Observacao'";
                $entidade['esolcoobje'] = "'$Objeto'";
                $entidade['esolcojust'] = "'$Justificativa'";
                $entidade['clicpoproc'] = $strNumProcessoSARP;
                $entidade['alicpoanop'] = $strAnoProcessoSARP;
                $entidade['cgrempcodi'] = $strGrupoEmpresaCodigoSARP;
                $entidade['ccomlicodi'] = $strComissaoCodigoSARP;
                $entidade['corglicod1'] = $strOrgaoLicitanteCodigoSARP;
                $entidade['dsolcodpdo'] = $strDataDom;
                $entidade['ctpleitipo'] = $strTipoLei;
                $entidade['cleiponume'] = $strLei;
                $entidade['cartpoarti'] = $strArtigo;
                $entidade['cincpainci'] = $strInciso;
                $entidade['fsolcorgpr'] = "$strRegistroPreco";
                $entidade['fsolcorpcp'] = "'$SarpTipo'";
                $entidade['fsolcocont'] = $strGeraContrato;
                $entidade['cusupocodi'] = (int) $_SESSION['_cusupocodi_'];
                $entidade['cusupocod1'] = (int) $_SESSION['_cusupocodi_'];
                $entidade['tsolcoulat'] = 'NOW()';
                $entidade['csitsocodi'] = $situacaoSolicitacao;
                $entidade['cintrpsequ'] = $sequencialIntencao;
                $entidade['cintrpsano'] = $anoIntencao;

                if ($_SESSION['ataCasoSARP'] != null) {
                    $entidade['carpnosequ'] = $_SESSION['ataCasoSARP'];
                }
                
                $sql = ' INSERT INTO sfpc.tbsolicitacaocompra (
                            ' . implode(',', array_keys($entidade)) . '
                        ) VALUES (
                            ' . implode(',', $entidade) . '
                        )
                    ';     
                executarTransacao($db, $sql);                
            }

            // Deletando itens e salvando no histórico
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                $sequencialSolicitacao = $Solicitacao;
            } else {
                $sql = ' SELECT last_value from SFPC.TBsolicitacaocompra_csolcosequ_seq1';
                $sequencialSolicitacao = resultValorUnico(executarTransacao($db, $sql));
            }
            // Deletando itens e salvando no histórico
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                // Apagando PSEs para apagar os itens de SCC
                $sql = " SELECT '( '||apresoanoe||', '||cpresosequ||')' as chave
                         FROM SFPC.TBPRESOLICITACAOEMPENHO
                         WHERE csolcosequ = " . $sequencialSolicitacao . '
                    ';
                $resPSE = executarSQL($db, $sql);
                if (hasPSEImportadaSofin($db, $sequencialSolicitacao)) {
                    assercao(false, 'ERRO: SCC possui PSE que já foi processado pelo SOFIN. Portanto, não é possível alterá-la!');
                }
                while ($pse = $resPSE->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $sql = 'DELETE FROM SFPC.TBITEMPRESOLICITACAOEMPENHO WHERE (apresoanoe, cpresosequ) = ' . $pse->chave . '';
                    executarTransacao($db, $sql);
                }

                $sql = 'DELETE FROM SFPC.TBPRESOLICITACAOEMPENHO WHERE CSOLCOSEQU = ' . $sequencialSolicitacao . '';
                executarTransacao($db, $sql);
                // remover todos itens de compra para depois recriá-los
                $sql = "DELETE FROM sfpc.tbitemdotacaoorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitembloqueioorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbtabelareferencialprecos WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitemsolicitacaocompra WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                // salvar o histórico da situação da SCC
                if ($situacaoSolicitacaoAtual != $situacaoSolicitacao) {
                    $sql = " INSERT INTO sfpc.tbhistsituacaosolicitacao(
                                csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                            VALUES (
                            $sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()
                                );
                            ';
                }
                executarTransacao($db, $sql);
            } else {
                // Incluir
                // salvar o histórico da situação da SCC
                $sql = " INSERT INTO sfpc.tbhistsituacaosolicitacao(
                            csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                         VALUES (
                                $sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()
                            );
                        ';
                executarTransacao($db, $sql);
            }
            
            // Incluindo os itens
            $sequencialItem = 0;
            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    ++ $sequencialItem;
                    $ordem = $material['posicao'] + 1;

                    $totalExercicio = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    if (! $ocultarCampoExercicio) {
                        // $totalExercicio = moeda2float($material["quantidadeExercicio"]) * moeda2float($material["valorEstimado"]);
                        $totalExercicio = $material['totalExercicio'];
                        $quantidadeExercicio = $material['quantidadeExercicio'];
                    }
                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($material['fornecedor']) . "'";
                        $sql = ' SELECT aforcrsequ
                                 FROM sfpc.tbfornecedorcredenciado
                                 WHERE aforcrccgc = ' . $strFornecedor . ' OR aforcrccpf = ' . $strFornecedor . '
                            ';
                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));
                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }

                    // $material['descricaoDetalhada'] =  str_replace('€', '', $material['descricaoDetalhada']); //removeCaracteresEspeciais($material['descricaoDetalhada']);
                    $material['descricaoDetalhada'] =  RetiraAcentos($material['descricaoDetalhada']);

                    $sql = " INSERT INTO sfpc.tbitemsolicitacaocompra(
                        csolcosequ, citescsequ, cmatepsequ, cservpsequ, eitescdescse,
                        aitescorde, aitescqtso, vitescunit, vitescvexe, aitescqtex, aforcrsequ,
                        eitescmarc, eitescmode, cusupocodi, titesculat, eitescdescmat   ";
                    
                    if ($_SESSION['ataCasoSARP'] != null) {
                        $carpnosequ = $_SESSION['ataCasoSARP'];
                        $citarpqsequ = $material['codigo_item'];
                        $sql .= ",carpnosequ, citarpsequ ";
                    }
                    $_vitescunit = (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0) ? $material['vitescunit'] : $material['valorEstimado'];
                    $sql .= '    ) VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', ' . $material['codigo'] . ', null, null, ' . $ordem . ", '" . moeda2float($material['quantidade']) . "', '" . moeda2float($_vitescunit) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "', " . $strFornecedorSeq . ", '" . $material['marca'] . "', '" . $material['modelo'] . "', " . $_SESSION['_cusupocodi_'] . ", now(), '" . trim($material['descricaoDetalhada']) . "'";                    
                    
                    if ($_SESSION['ataCasoSARP'] != null) {
                        $sql .= ", ".$carpnosequ." ,".$citarpqsequ." ); ";
                    } else {
                        $sql .= "); ";
                    }
                    
                    executarTransacao($db, $sql);

                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                if ($isDotacao) {
                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Dotação Inválido ou Inexistente');

                                    $sql = '
                                            INSERT INTO sfpc.tbitemdotacaoorcament(
                                            citescsequ, csolcosequ, aitcdounidoexer, citcdounidoorga, citcdounidocodi,
                                            citcdotipa, aitcdoordt, citcdoele1,
                                            citcdoele2, citcdoele3, citcdoele4, citcdofont, titcdoulat
                                            ) VALUES (
                                            ' . $sequencialItem . ', ' . $sequencialSolicitacao . ', ' . $dados['ano'] . ', ' . $dados['orgao'] . ', ' . $dados['unidade'] . ',
                                            ' . $dados['tipoProjetoAtividade'] . ', ' . $dados['projetoAtividade'] . ', ' . $dados['elemento1'] . ',
                                            ' . $dados['elemento2'] . ', ' . $dados['elemento3'] . ', ' . $dados['elemento4'] . ', ' . $dados['fonte'] . ', now()
                                            );
                                            ';
                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Bloqueio Inválido ou Inexistente');
                                    $sql = '
                                            INSERT INTO sfpc.tbitembloqueioorcament(
                                            csolcosequ, citescsequ, titcblulat, aitcblnbloq, aitcblanob)
                                            VALUES (
                                            ' . $sequencialSolicitacao . ', ' . $sequencialItem . ', now(), ' . $dados['sequencialChave'] . ', ' . $dados['anoChave'] . '
                                            );
                                            ';
                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }
            if (! is_null($servicos)) {
                foreach ($servicos as $servico) {
                    ++ $sequencialItem;
                    $ordem = $servico['posicao'] + 1;
                    $totalExercicio = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    if (! $ocultarCampoExercicio) {
                        // $totalExercicio = moeda2float($material["quantidadeExercicio"]) * moeda2float($material["valorEstimado"]);
                        $totalExercicio = $servico['totalExercicio'];
                        $quantidadeExercicio = $servico['quantidadeExercicio'];
                    }

                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($servico['fornecedor']) . "'";
                        $sql = ' SELECT aforcrsequ
                                 FROM sfpc.tbfornecedorcredenciado
                                 WHERE aforcrccgc = ' . $strFornecedor . ' OR aforcrccpf = ' . $strFornecedor . '
                            ';
                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));
                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }

                    // $servico['descricaoDetalhada'] =  str_replace('€', '', $servico['descricaoDetalhada']); //removeCaracteresEspeciais($servico['descricaoDetalhada']);
                    $servico['descricaoDetalhada'] =  RetiraAcentos($servico['descricaoDetalhada']);
                    
                    $sql = ' INSERT INTO sfpc.tbitemsolicitacaocompra(
                                csolcosequ, citescsequ, cmatepsequ, cservpsequ, eitescdescse,
                                aitescorde, aitescqtso, vitescunit, vitescvexe, aitescqtex,
                                aforcrsequ, eitescmarc, eitescmode, cusupocodi, titesculat ';
                    if ($_SESSION['ataCasoSARP'] != null) {
                        $carpnosequ = $_SESSION['ataCasoSARP'];
                        $citarpqsequ = $servico['codigo_item'];
                        $sql .= ",carpnosequ, citarpsequ ";
                    }
                    $_vitescunit = (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0) ? $servico['vitescunit'] : $servico['valorEstimado'];
                    $sql .= ' )  VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', null, ' . $servico['codigo'] . ", '" . trim($servico['descricaoDetalhada']) . "',
                    " . $ordem . ", '" . moeda2float($servico['quantidade']) . "', '" . moeda2float($_vitescunit) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "',
                    $strFornecedorSeq, null, null, " . $_SESSION['_cusupocodi_'] . ', now()';
                    
                    if ($_SESSION['ataCasoSARP'] != null) {
                        $sql .= ", ".$carpnosequ." ,".$citarpqsequ." ); ";
                    } else {
                        $sql .= "); ";
                    }
                    
                    executarTransacao($db, $sql);
                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                echo $isDotacao;
                                if ($isDotacao) {
                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Dotação Inválido ou Inexistente');
                                    $sql = '
                                            INSERT INTO sfpc.tbitemdotacaoorcament(
                                            citescsequ, csolcosequ, aitcdounidoexer, citcdounidoorga, citcdounidocodi,
                                            citcdotipa, aitcdoordt, citcdoele1,
                                            citcdoele2, citcdoele3, citcdoele4, citcdofont, titcdoulat
                                            ) VALUES (
                                            ' . $sequencialItem . ', ' . $sequencialSolicitacao . ', ' . $dados['ano'] . ', ' . $dados['orgao'] . ', ' . $dados['unidade'] . ',
                                            ' . $dados['tipoProjetoAtividade'] . ', ' . $dados['projetoAtividade'] . ', ' . $dados['elemento1'] . ',
                                            ' . $dados['elemento2'] . ', ' . $dados['elemento3'] . ', ' . $dados['elemento4'] . ', ' . $dados['fonte'] . ', now()
                                            );
                                            ';
                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);
                                    assercao(! is_null($dados), 'Bloqueio Inválido ou Inexistente');
                                    $sql = '
                                            INSERT INTO sfpc.tbitembloqueioorcament(
                                            csolcosequ, citescsequ, titcblulat, aitcblnbloq, aitcblanob)
                                        VALUES (
                                            ' . $sequencialSolicitacao . ', ' . $sequencialItem . ', now(), ' . $dados['sequencialChave'] . ', ' . $dados['anoChave'] . '
                                        );
                                        ';
                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }

            // inserir documentos
            $dirdestino = $GLOBALS['CAMINHO_UPLOADS'] . 'compras/';
            for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); ++ $i) {
                $sql = "
                            SELECT MAX(CDOCSOCODI)
                            FROM SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                         WHERE CSOLCOSEQU = $sequencialSolicitacao
                        ";
                $CodigoDocto = resultValorUnico(executarTransacao($db, $sql)) + 1;
                $NomeDocto = 'DOC_' . $sequencialSolicitacao . '_' . $CodigoDocto . '_' . $_SESSION['Arquivos_Upload']['nome'][$i];
                if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
                    $arquivo_criado = file_put_contents($dirdestino . $NomeDocto, $_SESSION['Arquivos_Upload']['conteudo'][$i]);
                    assercao($arquivo_criado, 'Falha na inclusão do documento. Verifique se o diretório de gravação não está protegido contra escrita.');
                    $sql = "
                                INSERT INTO SFPC.TBDOCUMENTOSOLICITACAOCOMPRA(
                                    CSOLCOSEQU, CDOCSOCODI, EDOCSONOME, CUSUPOCODI, TDOCSOULAT, edocsoexcl
                                ) VALUES(
                                    $sequencialSolicitacao, $CodigoDocto, '" . $NomeDocto . "', " . $_SESSION['_cusupocodi_'] . ", now(), 'N'
                                )
                            ";
                    executarTransacao($db, $sql);
                } elseif ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'excluido') {
                    $sql = "
                                    UPDATE SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                                    SET
                                        edocsoexcl = 'S'
                                    WHERE
                                        CSOLCOSEQU = $sequencialSolicitacao AND
                                        CDOCSOCODI = " . $_SESSION['Arquivos_Upload']['codigo'][$i] . '
                                ';
                    executarTransacao($db, $sql);
                }
            }

            // Transação foi bem sucedida. gerar pre solicitação
            if ($situacaoSolicitacao == $TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
                // neste ponto, o pendente de empenho são só de SCCs que não foram processadas ainda pelo sofin. Neste caso
                try {
                    gerarPreSolicitacaoEmpenho($db, $dbOracle, $sequencialSolicitacao);
                } catch (Excecao $e) {
                    cancelarTransacao($db);
                    $e->getMessage();
                    adicionarMensagem('Não foi possível gerar a solicitação de compra pois houve falha ao gerar a solicitação de empenho, com a seguinte mensagem: ' . $e->getMessage(), $GLOBALS['TIPO_MENSAGEM_ERRO']);
                }
            }

            // ***********************************
            // Gerar TRP
            // ***********************************
            if ($GLOBALS['Mens'] != 1) {
                inserirItensSCCNaTrp($sequencialSolicitacao, $db);
            }

            if ($GLOBALS['Mens'] != 1) {
                finalizarTransacao($db);
                $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $sequencialSolicitacao);

                if ($acaoPagina == ACAO_PAGINA_MANTER) {
                    $Mensagem = 'Solicitação ' . $strSolicitacaoCodigo . ' Alterada com Sucesso';
                    header('Location: CadSolicitacaoCompraManterSelecionar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                    exit();

                    // adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
                } else {
                    $Mensagem = 'Solicitação ' . $strSolicitacaoCodigo . ' Incluída com Sucesso';

                    // adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);

                    // Limpar variáveis
                    $Botao = null;
                    $InicioPrograma = null;
                    $CentroCusto = null;
                    $Observacao = null;
                    $Objeto = null;
                    $Justificativa = null;
                    $NCaracteresObservacao = null;
                    $NCaracteresObjeto = null;
                    $NCaracteresJustificativa = null;
                    $DataDom = null;
                    $Lei = null;
                    $Artigo = null;
                    $Inciso = null;
                    $Foco = null;
                    $TipoLei = null;
                    $RegistroPreco = null;
                    $Sarp = null;
                    $BloqueioTodos = null;
                    $TipoReservaOrcamentaria = null; // se é bloqueio (1) ou dotação (2)
                    $DotacaoTodos = null;
                    $CnpjFornecedor = null;
                    $GeraContrato = null;
                    $TipoCompra = null;

                    $NomeDocumento = null;
                    $DDocumento = null;
                    $OrigemBancoPreços = null;

                    $NumProcessoSARP = null;
                    $AnoProcessoSARP = null;
                    $ComissaoCodigoSARP = null;
                    $OrgaoLicitanteCodigoSARP = null;
                    $GrupoEmpresaCodigoSARP = null;
                    $CarregaProcessoSARP = null;

                    $MaterialCheck = null;
                    $MaterialCod = null;
                    $MaterialCodItem = null;
                    $MaterialQuantidade = null;
                    $MaterialValorEstimado = null;
                    $MaterialVitescunit = null;
                    $MaterialTotalExercicio = null;
                    $MaterialQuantidadeExercicio = null;
                    $MaterialMarca = null;
                    $MaterialModelo = null;
                    $MaterialFornecedor = null;

                    $ServicoCheck = null;
                    $ServicoCod = null;
                    $ServicoCodItem = null;
                    $ServicoQuantidade = null;
                    $ServicoDescricaoDetalhada = null;
                    $ServicoQuantidadeExercicio = null;
                    $ServicoValorEstimado = null;
                    $ServicoVitescunit = null;
                    $ServicoTotalExercicio = null;
                    $ServicoFornecedor = null;

                    $materiais = array();
                    $servicos = array();

                    $isDotacaoAnterior = null; // informa se na pagina anterior era dotação ou bloqueio
                    $Bloqueios = null;
                    $BloqueiosCheck = null;

                    $BloqueioAno = null;
                    $BloqueioOrgao = null;
                    $BloqueioUnidade = null;
                    $BloqueioDestinacao = null;
                    $BloqueioSequencial = null;

                    $DotacaoAno = null;
                    $DotacaoOrgao = null;
                    $DotacaoUnidade = null;
                    $DotacaoFuncao = null;
                    $DotacaoSubfuncao = null;
                    $DotacaoPrograma = null;
                    $DotacaoTipoProjetoAtividade = null;
                    $DotacaoProjetoAtividade = null;
                    $DotacaoElemento1 = null;
                    $DotacaoElemento2 = null;
                    $DotacaoElemento3 = null;
                    $DotacaoElemento4 = null;
                    $DotacaoFonte = null;

                    unset($_SESSION['Arquivos_Upload']);

                    header('Location: CadSolicitacaoCompraManterSelecionar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                    exit();
                }
            }
        }
        // TODO confirmar se fica aqui os unsets
        unset($_SESSION['ataSarp']);
        unset($_SESSION['ataCasoSARP']);
        unset($_SESSION['urlItensAta']);
    }
} elseif ($Botao == 'Retirar') {
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($materiais[$itr]['check']) {
            $materiais = array_removerItem($materiais, $itr);
            // $MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
            $quantidadeNova = count($materiais);
            if ($quantidadeNova != $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
                $quantidade = $quantidadeNova;
                -- $itr; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $materiais[$itr]['posicao'] = $itr;
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($servicos[$itr]['check']) {
            $servicos = array_removerItem($servicos, $itr);
            // $ServicoBloqueioItem = array_removerItem($ServicoBloqueioItem, $itr);
            $quantidadeNova = count($servicos);
            if ($quantidadeNova != $quantidade) {
                $quantidade = $quantidadeNova;
                -- $itr; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $servicos[$itr]['posicao'] = $itr;
    }
} elseif ($Botao == 'Incluir_Documento') {
    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);        
        $extensoesArquivo .= ', .zip, .xlsm';       
        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;
        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
                $isExtensaoValida = true;
            }
        }
        if (! $isExtensaoValida) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }
        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
        }
        
        $Tamanho = $tamanhoArquivo * pow(10, 6);  // tamanho em MB

        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Kbytes = $tamanhoArquivo;
            $Kbytes = (int) $Kbytes;
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Mb";
        }
        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Documentação Inválida';
    }
} elseif ($Botao == 'Retirar_Documento') {
    foreach ($DDocumento as $valor) {
        // $_SESSION['Arquivos_Upload']['conteudo'][$valor]="";
        // $_SESSION['Arquivos_Upload']['nome'][$valor]="";
        if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
        } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
        }
    }
} elseif ($Botao == 'IncluirBloqueio') {
    $BloqueioTodos = '';

    if ($isDotacao) {
        if (is_null($DotacaoAno) or $DotacaoAno == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoAno').focus();\" class='titulo2'>Ano da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoOrgao) or $DotacaoOrgao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoOrgao').focus();\" class='titulo2'>Orgão da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoUnidade) or $DotacaoUnidade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoUnidade').focus();\" class='titulo2'>Unidade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoFuncao) or $DotacaoFuncao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFuncao').focus();\" class='titulo2'>Função da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoSubfuncao) or $DotacaoSubfuncao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoSubfuncao').focus();\" class='titulo2'>Subfunção da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoPrograma) or $DotacaoPrograma == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoPrograma').focus();\" class='titulo2'>Programa da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoTipoProjetoAtividade) or $DotacaoTipoProjetoAtividade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoTipoProjetoAtividade').focus();\" class='titulo2'>Tipo do projeto/Atividade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoProjetoAtividade) or $DotacaoProjetoAtividade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoProjetoAtividade').focus();\" class='titulo2'>Projeto/Atividade da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoElemento1) or $DotacaoElemento1 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento1').focus();\" class='titulo2'>Elemento 1 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoElemento2) or $DotacaoElemento2 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento2').focus();\" class='titulo2'>Elemento 2 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoElemento3) or $DotacaoElemento3 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento3').focus();\" class='titulo2'>Elemento 3 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoElemento4) or $DotacaoElemento4 == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoElemento4').focus();\" class='titulo2'>Elemento 4 da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($DotacaoFonte) or $DotacaoFonte == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('DotacaoFonte').focus();\" class='titulo2'>Fonte da dotação</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf('%04s', $DotacaoAno);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoOrgao);
            $BloqueioTodos .= sprintf('%02s', $DotacaoUnidade);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoFuncao);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoSubfuncao);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoPrograma);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoTipoProjetoAtividade);
            $BloqueioTodos .= '.' . sprintf('%03s', $DotacaoProjetoAtividade);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoElemento1);
            $BloqueioTodos .= '.' . sprintf('%01s', $DotacaoElemento2);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoElemento3);
            $BloqueioTodos .= '.' . sprintf('%02s', $DotacaoElemento4);
            $BloqueioTodos .= '.' . sprintf('%04s', $DotacaoFonte);
            $BloqueioTodosData = getDadosDotacaoOrcamentaria($dbOracle, $BloqueioTodos);
        }
    } else {
        if (is_null($BloqueioAno) or $BloqueioAno == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioAno').focus();\" class='titulo2'>Ano do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($BloqueioOrgao) or $BloqueioOrgao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioOrgao').focus();\" class='titulo2'>Orgão do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($BloqueioUnidade) or $BloqueioUnidade == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioUnidade').focus();\" class='titulo2'>Unidade do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($BloqueioDestinacao) or $BloqueioDestinacao == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioDestinacao').focus();\" class='titulo2'>Destinação do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if (is_null($BloqueioSequencial) or $BloqueioSequencial == '') {
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioSequencial').focus();\" class='titulo2'>Sequencial do Bloqueio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
        if ($GLOBALS['Mens'] != 1) {
            $BloqueioTodos .= sprintf('%04s', $BloqueioAno);
            $BloqueioTodos .= '.' . sprintf('%02s', $BloqueioOrgao);
            $BloqueioTodos .= '.' . sprintf('%02s', $BloqueioUnidade);
            $BloqueioTodos .= '.' . sprintf('%01s', $BloqueioDestinacao);
            $BloqueioTodos .= '.' . sprintf('%04s', $BloqueioSequencial);
            $BloqueioTodosData = getDadosBloqueio($dbOracle, $BloqueioTodos);

            $valorBloqueio[] = $BloqueioTodosData['valorTotal'];
        }
    }
    $Foco = 'BloqueioAno';
    if ($isDotacao) {
        $Foco = 'DotacaoAno';
    }

    if ($GLOBALS['Mens'] != 1) {
        if (is_null($BloqueioTodosData)) {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . ' não existe</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
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
                adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . ' repetido(a)</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
        if (! $isRepetido) {
            array_push($Bloqueios, $BloqueioTodos);
        }
    }
} elseif ($Botao == 'RetirarBloqueio') {
    $quantidade = count($Bloqueios);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($BloqueiosCheck[$itr]) {
            unset($Bloqueios[$itr]);
        }
    }
    unset($BloqueiosCheck);
    if ($is_dotacao) {
        $Foco = 'DotacaoAno';
    } else {
        $Foco = 'BloqueioAno';
    }
    ;
}

if($removerItens) {
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $materiais = array_removerItem($materiais, $itr);
        // $MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
        $quantidadeNova = count($materiais);
        if ($quantidadeNova != $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
            $quantidade = $quantidadeNova;
            -- $itr; // compensando a posição do item removido
        }
    }
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $materiais[$itr]['posicao'] = $itr;
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $servicos = array_removerItem($servicos, $itr);
        // $ServicoBloqueioItem = array_removerItem($ServicoBloqueioItem, $itr);
        $quantidadeNova = count($servicos);
        if ($quantidadeNova != $quantidade) {
            $quantidade = $quantidadeNova;
            -- $itr; // compensando a posição do item removido
        }
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $servicos[$itr]['posicao'] = $itr;
    }
    
    unset($_SESSION['ataSarp']);
    unset($_SESSION['numeroAtaCasoSARP']);
    unset($_SESSION['urlItensAta']);
    unset($_SESSION['tipoControle']);
    //unset($_SESSION['ataCasoSARP']);
    $CnpjFornecedor = null;
}

if (! $cargaInicial and $isDotacaoAnterior != $isDotacao) { // era bloqueio e agora é dotação
    unset($Bloqueios);
    unset($BloqueiosCheck);
}

// INÍCIO DA GERAÇÃO DA PÁGINA

$acesso = '';
if ($acaoPagina == ACAO_PAGINA_INCLUIR) { 
    $acesso = 'Incluir';
    $descricao = "Preencha os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *.
                                                 O valor estimado   refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
    $acesso = 'Manter';
    $descricao = "Preencha os dados abaixo e clique no botão 'Manter'. Os itens obrigatórios estão com *.
                                                 O valor estimado   refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $acesso = 'Acompanhar';
    $descricao = "Para visualizar nova solicitação clique no botão 'Voltar'.";
} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $acesso = 'Cancelar';
    $descricao = 'Clique no botão Cancelar Solicitação.';
}

// 
if($programa == 'window') {
    $template = new TemplateNovaJanela('templates/CadSolicitacaoCompraIncluirManterExcluirScc.template.html', 'Compras > ' . $acesso);
} else {
    $template = new TemplatePaginaPadrao('templates/CadSolicitacaoCompraIncluirManterExcluirScc.template.html', 'Compras > ' . $acesso);
}

if ($programa == 'CadLicitacaoIncluir.php') {
    $template->NOME_PROGRAMA = 'CadSolicitacaoCompraIncluirManterExcluir.php';
} else {
    $template->NOME_PROGRAMA = $programa;
}

// $template->ACESSO = $acesso;
$template->ACESSO_TITULO = strtoupper2($acesso);
$template->DESCRICAO = $descricao;

if (! $ocultarCampoNumeroSCC && ! empty($Solicitacao)) {
    $template->block('BLOCO_NUMERO_SCC');
    $template->NUMERO_SCC = getNumeroSolicitacaoCompra($db, $Solicitacao);
}
if (! $ocultarCampoNumero) {
    $template->SEQUENCIAL_SCC = $Numero;
    $template->SEQUENCIAL_SCC_VALOR = $Numero;
    $template->block('BLOCO_SEQUENCIAL_SCC');
}
if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $DataSolicitacao = date('d/m/Y');
}
$template->DATA_SCC = $DataSolicitacao;
$template->DATA_SCC_VALOR = $DataSolicitacao;

// ## Centro de custo
// Pegando dados do usuário
$sql = sqlDadosUsuario($DataAtual); 
$res = executarSQL($db, $sql);

$Rows = $res->numRows();
if ($Rows != 0) {
    $Linha = $res->fetchRow();
    $TipoUsuario = $Linha[0];
    $OrgaoUsuario = $Linha[1];
    if ($TipoUsuario == 'R') {
        $DescUsuario = 'Requisitante';
    } elseif ($TipoUsuario == 'A') {
        $DescUsuario = 'Aprovador';
    } else {
        $DescUsuario = 'Atendimento';
    }
}

if (($_SESSION['_cgrempcodi_'] != 0) and ($TipoUsuario == 'C')) {
    $sqlCC = 'SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, ';
    $sqlCC .= '       B.CORGLICODI, B.EORGLIDESC, B.FORGLITIPO ';
    $sqlCC .= '  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ';
    $sqlCC .= ' WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ' . date('Y') . '';
    $sqlCC .= '   AND A.CORGLICODI = B.CORGLICODI  ';
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    $sqlCC .= '   AND A.CCENPOSEQU IN  ';
    $sqlCC .= '        ( SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ';
    $sqlCC .= '       WHERE USU.CUSUPOCODI = ' . $_SESSION['_cusupocodi_'] . " AND USU.FUSUCCTIPO IN ('C'))";
    $sqlCC .= '       ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA ';
} else {
    $sqlCC = 'SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,';
    $sqlCC .= '       D.CORGLICODI, D.EORGLIDESC, D.FORGLITIPO';
    $sqlCC .= '  FROM SFPC.TBCENTROCUSTOPORTAL A,  SFPC.TBGRUPOORGAO B, ';
    $sqlCC .= '       SFPC.TBGRUPOEMPRESA C, SFPC.TBORGAOLICITANTE D ';
    $sqlCC .= ' WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ' . date('Y') . '';
    $sqlCC .= '   AND A.CORGLICODI = B.CORGLICODI AND C.CGREMPCODI = B.CGREMPCODI ';
    $sqlCC .= '   AND B.CORGLICODI = D.CORGLICODI ';
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    if ($TipoUsuario == 'C') {
        $sqlCC .= ' AND C.CGREMPCODI = ' . $_SESSION['_cgrempcodi_'] . '';
    }
    $sqlCC .= ' ORDER BY D.EORGLIDESC,A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA';
}
$resCC = executarSQL($db, $sqlCC);

$RowsCC = $resCC->numRows();
if ($RowsCC == 0) {
    // Nenhum centro de custo foi encontrado
    $template->block('BLOCO_CENTRO_CUSTO_NENHUM');

    /*
     * // Sempre pegar o centro de custo da SCC no else abaixo.
     * } elseif ($RowsCC == 1) {
     * $Linha = $resCC->fetchRow();
     * $CentroCusto = $Linha[0];
     * $DescCentroCusto = $Linha[1];
     * $RPA = $Linha[2];
     * $Detalhamento = $Linha[3];
     * $Orgao = $Linha[4];
     * $DescOrgao = $Linha[5];
     * $administracao = $Linha[6];
     *
     * # Apenas 1 CC foi encontrado
     * $template->CC_ORGAO = $DescOrgao;
     * $template->CC_RPA = $RPA;
     * $template->CC_DESCRICAO = $DescCentroCusto;
     * $template->CC_DETALHAMENTO = $Detalhamento;
     * $template->block("BLOCO_CENTRO_CUSTO");
     */
} else {
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    if (! $ocultarCamposEdicao and $acaoPagina != ACAO_PAGINA_MANTER) {
        // Vários CCs existem
        $template->CC_TIPO_USUARIO = $TipoUsuario;
        $template->block('BLOCO_CENTRO_CUSTO_SELECIONAR');
    }
    if ($CentroCusto != '') {
        // Carrega os dados do Centro de Custo selecionado #
        $sql = 'SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA, B.FORGLITIPO, A.FCENPOSITU';
        $sql .= '  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ';
        $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
        // $sql .= " AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
        $res = executarSQL($db, $sql);

        while ($Linha = $res->fetchRow()) {
            $DescCentroCusto = $Linha[0];
            $DescOrgao = $Linha[1];
            $Orgao = $Linha[2];
            $RPA = $Linha[3];
            $Detalhamento = $Linha[4];
            $administracao = $Linha[5];
            $ccSituacao = $Linha[6];
            if ($ccSituacao == 'I') {
                $Detalhamento .= ' (Centro de custo inativo)';
            }
        }

        // Vários CCs existem mas um já foi selecionado
        $template->CC_ORGAO = $DescOrgao;
        $template->CC_RPA = $RPA;
        $template->CC_DESCRICAO = $DescCentroCusto;
        $template->CC_DETALHAMENTO = $Detalhamento;
        $template->block('BLOCO_CENTRO_CUSTO');
    }
}
$template->CC = $CentroCusto;
$template->CC_ADMINISTRACAO = $administracao;


 // [CUSTOMIZAÇÃO] - [CR129149]: REDMINE 92 (Registro de Preço) - intenção - irp
 if ($_SESSION['_fperficorp_'] == 'S') {
    if ($sequencialIntencao != "" && $anoIntencao != "") {
        $template->SEQUENCIAL_INTENCAO = substr($sequencialIntencao + 10000, 1);
        $template->ANO_INTENCAO = $anoIntencao;
        $template->NUMERO_INTENCAO_REGISTRO_PRECO = substr($sequencialIntencao + 10000, 1) . '/' . $anoIntencao;
        $template->block("BLOCO_INTENCAO_REGISTRO_PRECO");
    }

    if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S") {
        $template->block("BLOCO_SELECT_INTENCAO_REGISTRO_PRECO");
    }

    $ocultarBotaoItem = false;    
    if ($TipoCompra == TIPO_COMPRA_SARP) {
        $ocultarBotaoItem = true;
    }
 }

 // [/CUSTOMIZAÇÃO]
 
// ## Fim Centro de custo

// Verificar se é SARP e tem os dados na sessão 
// para abrir a janela dos itens da ata
$labelProcesso = 'Ata de Registro de Preço*';
if ($TipoCompra == TIPO_COMPRA_SARP) {
    $ocultarBotaoItem = true;
    $labelProcesso = 'Processo Licitatório*';
    if(isset($_SESSION['ataSarp']) && !isset($_GET['SeqSolicitacao'])) {
        // Url para abrir a janela dos itens da ata
        $urlItensAta = "ConsSelecionarItensAtaProcessoScc.php";
        $urlItensAta .= '?ata=' . $_SESSION['ataSarp']['ata'];
        $urlItensAta .= '&processo=' . $_SESSION['ataSarp']['processo'];
        $urlItensAta .= '&ano=' . $_SESSION['ataSarp']['ano'];
        $urlItensAta .= '&orgao=' . $_SESSION['ataSarp']['orgao'];
        $urlItensAta .= '&grupo=' . $_SESSION['ataSarp']['grupo'];
        $urlItensAta .= '&tipoSarp=' . $_SESSION['tipoSarpAnterior'];
        $urlItensAta .= '&TipoAta=' . $_SESSION['tipoAtaAnterior'];
        $urlItensAta .= '&close=1';
        $_SESSION['urlItensAta'] = $urlItensAta;
    } else if(isset($_SESSION['ataCasoSARP']) && $tipoAta == 'E') {
        $urlItensAta = "ConsSelecionarItensAtaProcessoScc.php";
        $urlItensAta .= '?ata=' . $_SESSION['ataCasoSARP'];
        $urlItensAta .= '&TipoAta=' . $_SESSION['tipoAtaAnterior'];
        $urlItensAta .= '&ano=' . $_SESSION['ataSarp']['ano'];
        $urlItensAta .= '&close=1';
        $_SESSION['urlItensAta'] = $urlItensAta;
    } else if(isset($_GET['SeqSolicitacao'])) {
        $urlItensAta = "ConsSelecionarItensAtaProcessoScc.php";
        $urlItensAta .= '?ata=' . $NumAta;
        $urlItensAta .= '&ano=' . $Ano;
        $urlItensAta .= '&tipoSarp=' . $SarpTipo;
        $urlItensAta .= '&TipoAta=' . $tipoAta;        
        $urlItensAta .= '&close=1';        
        $_SESSION['urlItensAta'] = $urlItensAta;
    } else if(isset($_SESSION['urlItensAta'])) {
        $urlItensAta = $_SESSION['urlItensAta'];
    }
} else {
    unset($_SESSION['urlItensAta']);
}


$template->CAMPO_OBJETO = gerarTextArea('formulario', 'Objeto', $Objeto, "400", $ocultarCamposEdicao);
$template->CAMPO_OBSERVACAO = gerarTextArea('formulario', 'Observacao', $Observacao, "200", $ocultarCamposEdicao);
$template->LABEL_PROCESSO = $labelProcesso;

// ## tipo de compra
$sql = 'select ctpcomcodi, etpcomnome from SFPC.TBtipocompra where ctpcomcodi <> 1';
$res = executarSQL($db, $sql);

if (! $ocultarCamposEdicao && empty($Solicitacao)) {
    while ($linha = $res->fetchRow()) {
        $codTipoCompra = $linha[0];
        $nomeTipoCompra = $linha[1];
        $template->TIPO_COMPRA = $nomeTipoCompra;
        $template->TIPO_COMPRA_VALOR = $codTipoCompra;
        if ($TipoCompra == $codTipoCompra) {
            $template->TIPO_COMPRA_SELECTED = 'selected';
        } else {
            $template->TIPO_COMPRA_SELECTED = '';
        }
        $template->block('BLOCO_TIPO_COMPRA_ITEM');
    }
    $template->block('BLOCO_TIPO_COMPRA');
} else {
    while ($linha = $res->fetchRow()) {
        $codTipoCompra = $linha[0];
        $nomeTipoCompra = $linha[1];
        if ($TipoCompra == $codTipoCompra) {
            $template->TIPO_COMPRA = $nomeTipoCompra;
            $template->VALOR_TIPO_COMPRA = $codTipoCompra;
            $template->block('BLOCO_TIPO_COMPRA_VISUALIZAR');
        }
    }
}

if ($TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE ||$TipoCompra == TIPO_COMPRA_DISPENSA) {

    if(!empty($DisputaValor)){
        $disputaDefault = $DisputaValor;
    }
    if(!empty($PublicaValor)){
        $publicaDefault = $PublicaValor;
    }
    if( $Lei == 1  && $Artigo == 14133 && $Artigo == 75 && ($Inciso == 69 || 70)){
        $template->CAMPO_DISPUTA = gerarRadioButtons('campoDisputa', array('SIM', 'NAO'), array('S', 'N') ,$disputaDefault, false, false,);
        $template->block("BLOCO_DISPUTA");
    }
                   
        $template->CAMPO_PUBLICAR = gerarRadioButtons('campoPublicar', array('SIM', 'NAO'), array('S', 'N') ,$publicaDefault, false, false,);
       
        $template->block("BLOCO_PUBLICAR");



    $compromissoDefault  = 'N';

if(!empty($campoCompromissoManter)){

    $compromissoDefault = $campoCompromissoManter;

}
if(!empty($CompromissoValor)){
    $compromissoDefault = $CompromissoValor;
}
    $template->CAMPO_COMPROMISSO = gerarRadioButtons('campoCompromisso', array('SIM', 'NAO'), array('S', 'N') ,$compromissoDefault, false, $ocultarCamposEdicao);
    $template->block("BLOCO_COMPROMISSO");
}

if($TipoCompra == TIPO_COMPRA_DISPENSA){
    if(!empty($DisputaValor)){
        $disputaDefault = $DisputaValor;
    }
    if(!empty($PublicaValor)){
        $publicaDefault = $PublicaValor;
    }
        $template->CAMPO_DISPUTA = gerarRadioButtons('campoDisputa', array('SIM', 'NAO'), array('S', 'N') ,$disputaDefault, false, false,);
        $template->CAMPO_PUBLICAR = gerarRadioButtons('campoPublicar', array('SIM', 'NAO'), array('S', 'N') ,$publicaDefault, false, false,);
        $template->block("BLOCO_DISPUTA");
        $template->block("BLOCO_PUBLICAR");
}



// ## fim tipo compra

if (! $ocultarCampoRegistroPreco) {
    $template->CAMPO_REGISTRO_PRECO = gerarRadioButtons('RegistroPreco', array(
        'SIM',
        'NAO'
    ), array(
        'S',
        'N'
    ), $RegistroPreco, false, $ocultarCamposEdicao, 'submit()');
    $template->block('BLOCO_REGISTRO_PRECO');
}
if (! $ocultarCampoSARP) {
    // Se o tipo da ata for externa, automaticamente a SARP será do tipo carona, e não será possível edita-lá

    if ($tipoAta == 'E') {
        $SarpTipo = 'C';
        $habilitado = 'disabled';
    }

    $template->TIPOATASARP = $tipoAta;
    $template->TIPOSARP = $SarpTipo;
    $acaoOnChange = "$( 'form' ).submit();";

    $template->CAMPO_TIPO_ATA = gerarRadioButtons('tipoAta', array(
        'INTERNA', 
        'EXTERNA'
    ), array(
        'I', 
        'E'
    ), $tipoAta, false, $ocultarCamposEdicao, $acaoOnChange);
    $template->CAMPO_SARP = gerarRadioButtons('Sarp', array(
        'CARONA',
        'PARTICIPANTE'
    ), array(
        'C',
        'P'
    ), $SarpTipo, false, $ocultarCamposEdicao, $acaoOnChange, $habilitado);

     // Desabilitar buscar processo caso não selecionado centro de custo
     $habilitado = true;
     if(empty($CentroCusto)) {
         $habilitado = false;
         $template->DESABILITARLUPAQUANDOTIPOSHARPINFORMADO = 'display:none';
         $template->DESABILITARLUPAQUANDOCENTRONAOINFORMADO = 'display: block';
     } else {    
         $template->DESABILITARLUPAQUANDOCENTRONAOINFORMADO = 'display: none';
     }
     
     if(empty($tipoAta)) {
        $habilitado = false;
        $template->DESABILITARLUPAQUANDOTIPOSHARPINFORMADO = 'display:none';
        $template->DESABILITARLUPAQUANDOCENTRONAOINFORMADO = 'display: block';
    } else {    
        $template->DESABILITARLUPAQUANDOCENTRONAOINFORMADO = 'display: none';
    }
     
     if ($SarpTipo == null && $habilitado) {        
         $template->DESABILITARLUPAQUANDOTIPOSHARPINFORMADO = 'cursor: default !important;pointer-events: none !important;';
     }

    $template->block('BLOCO_SARP');
}

// ## inicio processo licitatorio SARP
if (! $ocultarCampoProcessoLicitatorio) {

    if (! $ocultarCamposEdicao) {
        $template->block('BLOCO_LICITACAO_SELECIONAR');
    }
    if ($CarregaProcessoSARP == 1) {
        if($tipoAta == 'E') {
            $sql  = "SELECT A.CARPEXCODN, A.EARPEXPROC, A.EARPEXOBJE, B.AFORCRCCGC, B.AFORCRCCPF, B.NFORCRRAZS, A.EARPEXORGG, ";  
            $sql .= "  A.CARPNOSEQU, A.AARPEXANON ";
            $sql .= "  FROM ";
            $sql .= "  SFPC.TBATAREGISTROPRECOEXTERNA A, ";
            $sql .= "  SFPC.TBFORNECEDORCREDENCIADO B, ";
            $sql .= "  SFPC.TBATAREGISTROPRECONOVA C ";
            $sql .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU ";
            $sql .= "   AND A.CARPNOSEQU = C.CARPNOSEQU ";
            $sql .= "   AND C.CARPNOTIAT = 'E' ";
            $sql .= "   AND A.FARPEXSITU = 'A' ";
            
            if ($NumProcessoSARP != '') {
                $sql .= " AND A.CARPNOSEQU = $NumProcessoSARP ";
            }

            $res = executarTransacao($db, $sql);
            $Rows = $res->numRows();
            $Linha = $res->fetchRow();
            
            $numeroAnoAta = $Linha[0] . '/' . $Linha[8];
            $processoExternoAta = $Linha[1];
            $orgaoExternoAta = $Linha[6]; 
        } else if($tipoAta == 'I') {
            $sql = "
            SELECT
                    distinct A.CLICPOPROC, A.ALICPOANOP, D.ECOMLIDESC,  B.EORGLIDESC, E.EMODLIDESC,
                    A.CLICPOCODL, A.ALICPOANOL
                    FROM
                    SFPC.TBLICITACAOPORTAL A,
                    SFPC.TBORGAOLICITANTE B,
                    SFPC.TBCOMISSAOLICITACAO D,
                    SFPC.TBMODALIDADELICITACAO E
                    WHERE
                    A.CORGLICODI = B.CORGLICODI AND
                    A.FLICPOSTAT = 'A' AND
                    A.CCOMLICODI = D.CCOMLICODI AND
                    A.CMODLICODI = E.CMODLICODI 
                    ";        
            if ($NumProcessoSARP != '') {
                $sql .= " AND A.CLICPOPROC = $NumProcessoSARP ";
            }
            if ($AnoProcessoSARP != '') {
                $sql .= " AND A.ALICPOANOP = $AnoProcessoSARP ";
            }
            if ($ComissaoCodigoSARP != '') {
                $sql .= " AND A.CCOMLICODI = $ComissaoCodigoSARP ";
            }
            if ($OrgaoLicitanteCodigoSARP != '') {
                $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigoSARP ";
            }
            if ($GrupoEmpresaCodigoSARP != '') {
                $sql .= " AND A.CGREMPCODI = $GrupoEmpresaCodigoSARP ";
            }
            $res = executarTransacao($db, $sql);
            $Rows = $res->numRows();
            $Linha = $res->fetchRow();

            $ProcessoAnoSARP = $Linha[0] . '/' . $Linha[1];
            $ComissaoDescricaoSARP = $Linha[2];
            $OrgaoLicitanteDescricaoSARP = $Linha[3];
            $modalidadeLicitacao = $Linha[4];
            $modalidadeLicitacaoNum = $Linha[5];
            $modalidadeLicitacaoAno = $Linha[6];           
        }

        if ($Rows == 1) {            
            if($tipoAta == 'E') {
                $template->STYLE_NUMERO_ATA = 'display: none';
                $template->VALOR_ATA_RP     = $numeroAnoAta;
                $template->SHOW_DIV_ATA         = '<div>';
                $template->SHOW_DIV_PROCESSO    = '<div style="display:none">';
                $template->SARP_ATA_NUMERO      = $numeroAnoAta;
                $template->SARP_ATA_PROCESSO_E  = $processoExternoAta;
                $template->SARP_ATA_ORGAO_E  = $orgaoExternoAta;
                $template->END_DIV = '</div>';
                $template->block('BLOCO_ATA_REGISTROP_EXTERNA');  
            } else if($tipoAta == 'I') {
                $template->STYLE_NUMERO_ATA = '';
                $template->SHOW_DIV_ATA = '<div style="display:none">';
                $template->SHOW_DIV_PROCESSO = '<div>';
                $template->SARP_LICITACAO_ANO = $ProcessoAnoSARP;
                $template->SARP_LICITACAO_COMISSAO = $ComissaoDescricaoSARP;
                $template->SARP_LICITACAO_ORGAO = $OrgaoLicitanteDescricaoSARP;
                $template->SARP_LICITACAO_MODALIDADE = $modalidadeLicitacao;
                $template->SARP_LICITACAO_MODALIDADE_NUM = $modalidadeLicitacaoNum;
                $template->SARP_LICITACAO_MODALIDADE_ANO = $modalidadeLicitacaoAno;                
                $template->END_DIV = '</div>';
                $template->block('BLOCO_LICITACAO_VISUALIZAR');
            }
        }
    } else {
        /*if($tipoAta == 'E') {
            $template->SHOW_DIV_PROCESSO = '<div style="display:none">';
            $template->SHOW_DIV_ATA = '<div>';
        } else if($tipoAta == 'I') {
            $template->SHOW_DIV_PROCESSO = '<div>';
            $template->SHOW_DIV_ATA = '<div style="display:none">';
        }*/
    }    

    if(!empty($numeroAtaRP)) {
        $template->VALOR_ATA_RP = $numeroAtaRP;
    }

    $template->SARP_LICITACAO_PROCESSO = $NumProcessoSARP;
    $template->SARP_LICITACAO_ANO_VALOR = $AnoProcessoSARP;
    $template->SARP_LICITACAO_COMISSAO_VALOR = $ComissaoCodigoSARP;
    $template->SARP_LICITACAO_ORGAO_VALOR = $OrgaoLicitanteCodigoSARP;
    $template->SARP_LICITACAO_EMPRESA = $GrupoEmpresaCodigoSARP;
    $template->SARP_LICITACAO_CARREGA = $CarregaProcessoSARP;
    $template->block('BLOCO_LICITACAO');
}
// ## fim processo licitatorio SARP

if (! $ocultarCampoGeraContrato or $preencherCampoGeraContrato) {
    $template->CAMPO_CONTRATO = gerarRadioButtons('GeraContrato', array(
        'SIM',
        'NAO'
    ), array(
        'S',
        'N'
    ), $GeraContrato, false, $ocultarCamposEdicao or $preencherCampoGeraContrato);
    $template->block('BLOCO_CONTRATO');
}
if (! $ocultarCamposEdicao) {
    if (! $ocultarCampoFornecedor) {
        $CnpjStr = FormataCpfCnpj($CnpjFornecedor);
        $template->FORNECEDOR_CNPJ = $CnpjStr;
        $template->FORNECEDOR_BOTOES = ($TipoCompra == TIPO_COMPRA_SARP) ? 'style="display:none"' : '';
        $template->FORNECEDOR_NUMERO = ($TipoCompra == TIPO_COMPRA_SARP) ? $CnpjStr . '<br>' : '';
        $template->FORNECEDOR_TYPE_INPUT = ($TipoCompra == TIPO_COMPRA_SARP) ? 'hidden' : 'text';
        if (! is_null($CnpjFornecedor)) {
            $CPFCNPJ = removeSimbolos($CnpjFornecedor);
            $materialServicoFornecido = null;
            $TipoMaterialServico = null;
            $resposta = checaSituacaoFornecedor($db, $CPFCNPJ);
            if (! is_null($resposta) and ! is_null($resposta['razao']) and $resposta['razao'] != '') {
                $template->FORNECEDOR = $resposta['razao'];
            }
        }
        $template->block('BLOCO_FORNECEDOR');
    }
}

$template->READ_ONLY = $ifVisualizacaoThenReadOnly;

ob_start(); // pegando o html ainda não tratado pelo template, para depois jogar no template
?>

<?php
if (! $ocultarCampoLegislacao) {
    ?>
<tr>
    <td
        class="textonormal"
        bgcolor="#DCEDF7"
    >Legislação*</td>
    <td class="textonormal">
    <?php
    $sql = 'select ctpleitipo, etpleitipo from SFPC.TBtipoleiportal';
    $res = executarTransacao($db, $sql);
    if (! $ocultarCamposEdicao) {
        ?>
            Tipo de lei:
            <select
        name="TipoLei"
        size="1"
        <?php echo  $ifVisualizacaoThenReadOnly?>
        class="textonormal"
        onChange="atualizar('TipoLei')"
    >
            <option value="">Selecionar Tipo de Lei</option>
        <?php
        while ($Linha = $res->fetchRow()) {
            $tipoLeiItem = $Linha[0];
            $tipoLeiDesc = $Linha[1];
            ?>
                    <option
                value="<?php echo  $tipoLeiItem ?>"
                <?php
            if ($tipoLeiItem == $TipoLei) {
                echo 'selected';
            }
            ?>
            ><?php echo  $tipoLeiDesc ?></option>
            <?php
        }
        ?>
            </select>
        <?php
    } else {
        while ($Linha = $res->fetchRow()) {
            $tipoLeiItem = $Linha[0];
            $tipoLeiDesc = $Linha[1];
            if ($tipoLeiItem == $TipoLei) {
                echo 'Tipo de lei: ' . $tipoLeiDesc . '  ';
            }
        }
    }
    ?>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <?php
    if (! is_null($TipoLei) and $TipoLei != '') {
        $sql = 'select cleiponume from sfpc.tbleiportal where ctpleitipo= ' . $TipoLei;
        $res = executarTransacao($db, $sql);
    }
    if (! $ocultarCamposEdicao) {
        ?>
            Lei:
            <select
        name="Lei"
        size="1"
        <?php echo  $ifVisualizacaoThenReadOnly?>
        class="textonormal"
        onChange="atualizar('Lei')"
    >
            <option value="">Selecionar Lei</option>
                <?php
        if (! is_null($TipoLei) and $TipoLei != '') {
            while ($Linha = $res->fetchRow()) {
                $leiItem = $Linha[0];
                ?>
                        <option
                value="<?php echo  $leiItem ?>"
                <?php
                if ($leiItem == $Lei) {
                    echo 'selected';
                }
                ?>
            ><?php echo  $leiItem ?></option>
                    <?php
            }
        }
        ?>
            </select>
            <?php
    } else {
        while ($Linha = $res->fetchRow()) {
            $leiItem = $Linha[0];
            if ($leiItem == $Lei) {
                echo 'Lei: ' . $leiItem . ' ';
            }
        }
    }
    ?>

        &nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '') {
        $sql = ' SELECT cartpoarti--, nartponume
                 FROM sfpc.tbartigoportal
                 WHERE ctpleitipo = ' . $TipoLei . '
                    AND  cleiponume = ' . $Lei . ' ';
        $res = executarTransacao($db, $sql);
    }
    if (! $ocultarCamposEdicao) {
        ?>

            Artigo: <select name="Artigo" size="1" <?php echo  $ifVisualizacaoThenReadOnly?> class="textonormal" onChange="atualizar('Artigo')" >
                    <option value="">Selecionar Artigo</option>
                    <?php
                        if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '') {
                            while ($Linha = $res->fetchRow()) {
                                $artigoItem = $Linha[0];
                                ?>
                                <option value="<?php echo  $artigoItem ?>" <?php echo ($artigoItem == $Artigo) ? 'selected' : '' ?> >
                                    <?php echo  $artigoItem ?>
                                </option>
                                <?php
                            }
                        }
                    ?>
                </select>
            <?php
    } else {
        while ($Linha = $res->fetchRow()) {
            $artigoItem = $Linha[0];
            $artigoNumero = $Linha[1];
            if ($artigoItem == $Artigo) {
                echo 'Artigo ' . $artigoItem . '  ';
            }
        }
    }
    ?>

        &nbsp;&nbsp;&nbsp;&nbsp;
            <?php
    if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '' and ! is_null($Artigo) and $Artigo != '') {
        $sql = ' SELECT cincpainci, nincpanume
                 FROM sfpc.tbincisoparagrafoportal
                 WHERE ctpleitipo = ' . $TipoLei . '
                        AND cleiponume = ' . $Lei . '
                        AND cartpoarti = ' . $Artigo . ' ';
        $res = executarTransacao($db, $sql);
    }
    if (! $ocultarCamposEdicao) {
        ?>
            Inciso/Parágrafo: <select name="Inciso" onChange="atualizar('Inciso')" size="1" <?php echo  $ifVisualizacaoThenReadOnly?> class="textonormal" >
            <option value="">Selecionar Inciso ou Parágrafo...</option>
            <?php
            if (! is_null($TipoLei) and $TipoLei != '' and ! is_null($Lei) and $Lei != '' and ! is_null($Artigo) and $Artigo != '') {
                while ($Linha = $res->fetchRow()) {
                    $incisoItem = $Linha[0];
                    $incisoNumero = $Linha[1];
                    ?>
                        <option value="<?php echo  $incisoItem ?>"
                    <?php echo ($incisoItem == $Inciso) ? 'selected' : ''?>>
                        <?php echo  $incisoNumero ?>
                    </option>
                    <?php
                }
            }
            ?>
            </select>
        <?php
    } else {
        while ($Linha = $res->fetchRow()) {
            $incisoItem = $Linha[0];

            if ($incisoItem == $Inciso) {
                echo 'Inciso/ parágrafo: ' . $incisoNumero . ' ';
            }
        }
    }
    ?>

    </td>
</tr>
<?php
}
if (! $ocultarCampoJustificativa) {
    ?>
<tr>
    <td class="textonormal" bgcolor="#DCEDF7" >Justificativa</td>
    <?php if (! $ocultarCamposEdicao) { ?>
        <td class="textonormal"><font class="textonormal">máximo de 200 caracteres</font>
        <input type="text" <?php echo  $ifVisualizacaoThenReadOnly?> name="NCaracteresJustificativa" size="3" disabled <?php echo  $ifVisualizacaoThenReadOnly?> value="<?php echo $NCaracteresJustificativa ?>" class="textonormal" ><br> 
        <textarea name="Justificativa" maxlength="200" cols="50" <?php echo  $ifVisualizacaoThenReadOnly?> rows="4" OnKeyUp="javascript:CaracteresJustificativa(1)" OnBlur="javascript:CaracteresJustificativa(0)" OnSelect="javascript:CaracteresJustificativa(1)" class="textonormal" style="text-transform: uppercase;" ><?php echo $Justificativa; ?></textarea>
    </td>
    <?php } else { ?>
        <td class="textonormal">
            <?php echo trim($Justificativa); ?>
        </td>
    <?php } ?>
</tr>
<?php
}
if (! $ocultarCampoDataDOM) {
    ?>
<tr>
    <td class="textonormal" bgcolor="#DCEDF7" >Data da publicação no DOM*</td>
    <td class="textonormal">
        <?php  if (! $ocultarCamposEdicao) { ?>
        <input name="DataDom" <?php echo  $ifVisualizacaoThenReadOnly?> id="DataDom" class="data" size="10" maxlength="10" value="<?php echo  $DataDom ?>" type="text" > 
        <a href="javascript:janela('../calendario.php?Formulario=CadSolicitacaoCompraIncluirManterExcluir&amp;Campo=DataDom','Calendario',220,170,1,0)" >
            <img src="../midia/calendario.gif" alt="" border="0" >
        </a>
        <?php } else { ?>
            <?php echo  $DataDom?>
        <?php } ?>
    </td>
</tr>
<?php
}
?>
<tr>
    <?php #--------------------------Inicio Bloqueios ?>
    <td class="textonormal" colspan="4" >
        <input type="hidden" name="TipoReservaOrcamentaria" id="TipoReservaOrcamentaria" value="<?php echo  $TipoReservaOrcamentaria ?>" />
        <table id="scc_bloqueios" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
            <tbody>
                <tr>
                    <td class="titulo3" align="center" bgcolor="#75ADE6" valign="middle" >
                    <span id="BloqueioTitulo" colspan=2 >BLOQUEIO OU DOTAÇÃO ORÇAMENTÁRIA</span>
                </td>
                </tr>
            <?php
            $cntBloqueio = - 1;
            if (! is_null($Bloqueios)) {
                foreach ($Bloqueios as $key => $bloqueioItem) {
                    if (isset($bloqueioItem)) {
                        ++ $cntBloqueio;
                        ?>
                <tr>
                    <td class="textonormal">
                    <?php if (! $ocultarCamposEdicao) { ?>
                        <input name="BloqueiosCheck[<?php echo $cntBloqueio; ?>]" type="checkbox" <?php echo ($BloqueiosCheck[$cntBloqueio]) ? 'checked' : '' ?>  />
                    <?php } ?>
                        <?php echo $bloqueioItem; echo ' - R$ '; echo converte_valor_estoques($valorBloqueio[$key]); ?>
                        <input type="hidden" name="ValorBloqueio[]" id="ValorBloqueio" value="<?php echo  $valorBloqueio[$key] ?>"/>
                        <input name="Bloqueios[<?php echo  $cntBloqueio ?>]" value="<?php echo  $bloqueioItem ?>" type="hidden" />
                    </td>
                </tr>
                        <?php
                    }
                }
            }
            ?>
            <?php
            if (! $ocultarCamposEdicao) {
                ?>
                <tr>
                    <td class="textonormal" colspan=2 bgcolor="#ffffff" >
                        <table class="textonormal" border="0" align="left" width="100%" summary="" >
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="200px" >Novo <span id="BloqueioLabel"><?php echo  $reserva ?></span>: </td>
                                <td class="textonormal">
                                <?php if ($isDotacao) { ?>
                                    Ano: <input name="DotacaoAno" id="DotacaoAno" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoAno ?>"/> 
                                    Órgão: <input name="DotacaoOrgao" id="DotacaoOrgao" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoAno ?>" /> 
                                    Unidade: <input name="DotacaoUnidade" id="DotacaoUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoAno ?>" /> 
                                    Funcao: <input name="DotacaoFuncao" id="DotacaoFuncao" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoFuncao ?>" /> 
                                    SubFunção: <input name="DotacaoSubfuncao" id="DotacaoSubfuncao" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoSubfuncao ?>" /> 
                                    Programa: <input name="DotacaoPrograma" id="DotacaoPrograma" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoSubfuncao ?>" /> 
                                    Tipo Projeto/Atividade: <input name="DotacaoTipoProjetoAtividade" id="DotacaoTipoProjetoAtividade" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoTipoProjetoAtividade ?>" /> 
                                    Projeto/Atividade: <input name="DotacaoProjetoAtividade" id="DotacaoProjetoAtividade" size="3" maxlength="3" value="" type="text" value="<?php echo  $DotacaoProjetoAtividade ?>" /> 
                                    Elemento1: <input name="DotacaoElemento1" id="DotacaoElemento1" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoElemento1 ?>" /> 
                                    Elemento2: <input name="DotacaoElemento2" id="DotacaoElemento2" size="1" maxlength="1" value="" type="text" value="<?php echo  $DotacaoElemento2 ?>" /> 
                                    Elemento3: <input name="DotacaoElemento3" id="DotacaoElemento3" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoElemento3 ?>" /> 
                                    Elemento4: <input name="DotacaoElemento4" id="DotacaoElemento4" size="2" maxlength="2" value="" type="text" value="<?php echo  $DotacaoElemento4 ?>" /> 
                                    Fonte: <input name="DotacaoFonte" id="DotacaoFonte" size="4" maxlength="4" value="" type="text" value="<?php echo  $DotacaoFonte ?>" />
                                <?php
                                    } else {
                                ?>
                                    Ano: <input name="BloqueioAno" id="BloqueioAno" size="4" maxlength="4" value="" type="text" value="<?php echo  $BloqueioAno ?>" /> 
                                    Órgão: <input name="BloqueioOrgao" id="BloqueioOrgao" size="2" maxlength="2" value="" type="text" value="<?php echo  $BloqueioOrgao ?>" /> 
                                    Unidade: <input name="BloqueioUnidade" id="BloqueioUnidade" size="2" maxlength="2" value="" type="text" value="<?php echo  $BloqueioUnidade ?>" /> 
                                    Destinação: <input name="BloqueioDestinacao" id="BloqueioDestinacao" size="1" maxlength="1" value="" type="text" value="<?php echo  $BloqueioDestinacao ?>" /> 
                                    Sequencial: <input name="BloqueioSequencial" id="BloqueioSequencial" size="4" maxlength="4" value="" type="text" value="<?php echo  $BloqueioSequencial ?>" />
                                <?php
                                }
                                ?>

                                <?php
                                /*
                                * <input name="BloqueioTodos" id="BloqueioTodos" class="bloqueioDotacao" size="40" maxlength="36" value="" type="text" value="<?php echo $BloqueioTodos?>"/>
                                * <a href="javascript:AbreJanela('InfPreenchimentoBloqueios.php',700,370);" id='CentroCustoLink'><img src="../midia/icone_interrogacao.gif" border="0"></a>
                                */
                                ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="textonormal" align="center" >
                        <input name="BotaoIncluirBloqueioTodos" value="Incluir <?php echo  $reserva ?>" class="botao" type="button" onClick="incluirBloqueio()" /> 
                        <input name="BotaoRemoverBloqueioTodos" value="Remover <?php echo  $reserva ?>" class="botao" type="button" onClick="retirarBloqueio()" />
                    </td>
                </tr>
            <?php } ?>
</tbody>
        </table>
<?php #--------------------------Inicio Itens ?>
<?php 
    $exibirValorEstimado   = '';
    $exibirValorSolicitado = 'display: none';
    if(isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0) {
        $exibirValorEstimado   = 'display: none';
        $exibirValorSolicitado = '';
    }
?>
<table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
    <tbody>
        <tr>
            <td colspan="17" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle" >ITENS DA SOLICITAÇÃO DE MATERIAL</td>
        </tr>
        <!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
        <tr class="head_principal">
            <?php
            $descricaoWidth = '300px';

            // redimensionando dependendo do número de campos
            if ($TipoCompra == TIPO_COMPRA_LICITACAO and ($RegistroPreco == 'S' or is_null($RegistroPreco)) or is_null($TipoCompra)) {
                $descricaoWidth = '700px';
            }

            $qtdeColunas = 11;
            $colunasOcultas = 1;
            if ($ocultarCampoTRP) {
                ++ $colunasOcultas;
            }
            if ($ocultarCampoExercicio) {
                $colunasOcultas += 3;
            }
            if ($ocultarCampoFornecedor) {
                $colunasOcultas += 3;
            }
            if ($TipoCompra == TIPO_COMPRA_LICITACAO) {
                $colunasOcultas -= 3;
            }
            ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO DO MATERIAL</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >CÓD.RED. CADUM</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >UND</td>
            <?php            
                $exibirTd = false;

                // Se material tiver indicador cadum (genérico) ou tiver descrição detalhada preenchida (diferente de vazio ou null)
                if (is_array($materiais)) {
                    foreach ($materiais as $key) {
                        if ((hasIndicadorCADUM($db, (int) $key['codigo']) === true)) {
                            $exibirTd = true;
                            break;
                        }
                    }
                }

                if ($exibirTd) {
            ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO DETALHADA</td>
            <?php
                } else {
                    $colunasOcultas += 1;
                }
            ?>

            <?php
            if (! $ocultarCampoTRP) { ?>
                <td class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR TRP</td>
            <?php
            }
            ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7"><?php echo (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] == 1) ? 'QUANTIDADE' : 'QUANTIDADE' ?></td>
            
            <td style="<?php echo $exibirValorSolicitado; ?>" class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR SOLICITADO</td>
            <td style="<?php echo $exibirValorEstimado; ?>" class="textoabason" align="center" bgcolor="#DCEDF7" ><?php echo  (($TipoCompra == TIPO_COMPRA_SARP)) ? 'VALOR' : 'VALOR ESTIMADO'; ?></td>

            <?php if (! $ocultarCampoFornecedor) { ?>
                <td class="textoabason" align="center" bgcolor="#DCEDF7">CPF/CNPJ DO FORNECEDOR</td>
                <td class="textoabason" align="center" bgcolor="#DCEDF7">MARCA</td>
                <td class="textoabason" align="center" bgcolor="#DCEDF7" >MODELO</td>
            <?php } ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR TOTAL</td>
        </tr>
        <!-- FIM Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->

<?php
// Materiais do POST-----------------------------------
$QuantidadeMateriais = count($materiais);
$QuantidadeServicos = count($servicos);
$ValorTotalItem = 0;
$ValorTotal = 0;

for ($itr = 0; $itr < $QuantidadeMateriais; ++ $itr) {
    $_valor = (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0) ? $materiais[$itr]['vitescunit'] : $materiais[$itr]['valorEstimado'];
    $ValorTotalItem = moeda2float($materiais[$itr]['quantidade']) * moeda2float($_valor);
    $ValorTotal += $ValorTotalItem;
    if (! $ocultarCampoExercicio) {
        // $ValorTotalExercicio = moeda2float($materiais[$itr]["quantidadeExercicio"]) * moeda2float($materiais[$itr]["valorEstimado"]);
        $ValorTotalExercicio = $materiais[$itr]['totalExercicio'];
        $TotalDemaisExercicios = $ValorTotalItem - moeda2float($ValorTotalExercicio);
        if ($TotalDemaisExercicios < 0) {
            $TotalDemaisExercicios = 0;
        }
    }
    ?>
        <!-- Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
        <tr>
            <!--  Coluna 1 = Codido-->
            <td class="textonormal" align="center" style="text-align: center" >
                <?php echo  ($itr + 1)?>
            </td>
            <!--  Coluna 2  = Descricao -->
            <td class="textonormal">
                <?php if (! $ocultarCamposEdicao) { ?>
                <input name="MaterialCheck[<?php echo  $itr ?>]" <?php echo ($materiais[$itr]['check']) ? 'checked' : '';?> <?php echo  $ifVisualizacaoThenReadOnly?> <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly' : ''; ?> type="checkbox" />
                <?php } ?>
                <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo  $materiais[$itr]['codigo'] ?>&amp;TipoGrupo=M&amp;ProgramaOrigem=<?php echo  $programa ?>',700,370);" > 
                    <font color="#000000"><?php echo  $materiais[$itr]['descricao'] ?></font>
                </a>
            </td>
            <!--  Coluna 3 = Cod CADUM-->
            <td class="textonormal" style="text-align: center !important;" >
                <?php echo  $materiais[$itr]['codigo']?>
                <input value="<?php echo  $materiais[$itr]['codigo_item'] ?>" name="MaterialCodItem[<?php echo  $itr ?>]" type="hidden" />
                <input value="<?php echo  $materiais[$itr]['codigo'] ?>" name="MaterialCod[<?php echo  $itr ?>]" type="hidden" /> 
                <input value="<?php echo ($materiais[$itr]['isObras']) ? 'true' : 'false';?>" name="MaterialIsObras[<?php echo  $itr ?>]" id="MaterialIsObras_<?php echo  $itr ?>" type="hidden" />
            </td>
            <!--  Coluna 4 = UND-->
            <td class="textonormal" align="center" >
                <?php echo  $materiais[$itr]['unidade']?>
            </td>
            <!--  Coluna 5 = DESCRIÇÃO DETALHADA-->
            <?php if ($exibirTd) { ?>
            <td class="textonormal" align="center" >
                <?php
                if (hasIndicadorCADUM($db, $materiais[$itr]['codigo'])) {
                    $disabled = '';

                    if (!$ocultarCamposEdicao || $_SESSION['_cperficodi_'] == 2) { ?>
                        <textarea id="MaterialDescricaoDetalhada[<?php echo  $itr ?>]" name="MaterialDescricaoDetalhada[<?php echo  $itr ?>]" style="display:none" cols="50" rows="4"  class="textonormal"><?php echo  $materiais[$itr]['descricaoDetalhada'] ?></textarea>
                        <?php 
                        echo $materiais[$itr]['descricaoDetalhada']; // ABACO
                    } else {
                        echo trim($materiais[$itr]['descricaoDetalhada']);
                    } ?>
                <?php } else {
                    echo '<nobr>---</nobr>';
                    echo "<input name='MaterialDescricaoDetalhada[" . $itr . "]' id='MaterialDescricaoDetalhada[" . $itr . "]' value='' type='hidden'   />";
                }
                ?>
            </td>
            <?php } ?>

            <!--  Coluna 6 = VALOR TRP-->
            <?php if (! $ocultarCampoTRP) { ?>
            <td class="textonormal" align="center" >
            <?php
            if (! is_null($materiais[$itr]['trp'])) {
                $material = $materiais[$itr]['codigo'];
                $dataMinimaValidaTrp = prazoValidadeTrp($db, $TipoCompra)->format('Y-m-d');
                $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');
                $exibirMediaTRP = $materiais[$itr]['trp'] != null;

                if ($exibirMediaTRP) {
                    if ($TipoCompra == TIPO_COMPRA_DIRETA) {
                        $Url = 'RelTRPConsultarDireta.php?Material=' . $materiais[$itr]['codigo'];
                    } else {
                        $Url = 'RelTRPConsultar.php?Material=' . $materiais[$itr]['codigo'];
                    }
                    echo "<a href='javascript:AbreJanela(\"" . $Url . "\",800,500);'>" . $materiais[$itr]['trp'] . '</a>';
                    echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='" . $materiais[$itr]['trp'] . "' type='hidden'   />";
                } else {
                    echo '<nobr>---</nobr>';
                }
            } else {
                echo '<nobr>---</nobr>';
                echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='' type='hidden'   />";
            } ?>
            </td>
            <?php } ?>

            <!--  Coluna 7 =  Quantidade -->
            <td class="textonormal" align="center" width="10" >
                <?php if (! $ocultarCamposEdicao) { ?>
                    <input
                        name="MaterialQuantidade[<?php echo  $itr ?>]"
                        class="dinheiro4casas"
                        value="<?php echo  $materiais[$itr]['quantidade'] ?>"
                        <?php echo  $ifVisualizacaoThenReadOnly?>
                        <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                        maxlength="16"
                        size="15"
                        id="MaterialQuantidade[<?php echo  $itr ?>]"
                        type="text"
                        onKeyUp="onChangeItemQuantidade('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL); " />
                        <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? $materiais[$itr]['quantidade'] : ''; ?>
                <?php } else {
                    echo $materiais[$itr]['quantidade'];
                } ?>
            </td>

            <td class="textonormal" align="center" width="10" style="<?php echo $exibirValorSolicitado; ?>" >
                <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    name="MaterialVitescunit[<?php echo  $itr ?>]"
                    id="MaterialVitescunit[<?php echo  $itr ?>]"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    size="16"
                    maxlength="16"
                    value="<?php echo  $materiais[$itr]['vitescunit'] ?>" 
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    class="dinheiro4casas"
                    type="text"
                    onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO); " />
                    <?php echo $materiais[$itr]['vitescunit']; ?>
                <?php } else {
                    echo $materiais[$itr]['vitescunit'];
                }
                ?>
            </td>

            <!--  Coluna 8 =  Valor Estimado -->
            <td class="textonormal" align="center" width="10" style="<?php echo $exibirValorEstimado; ?>" >
                <?php if (! $ocultarCamposEdicao) { ?>
                <input
                        name="MaterialValorEstimado[<?php echo  $itr ?>]"
                        id="MaterialValorEstimado[<?php echo  $itr ?>]"
                        <?php echo  $ifVisualizacaoThenReadOnly?>
                        <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                        size="16"
                        maxlength="16"
                        value="<?php echo  $materiais[$itr]['valorEstimado'] ?>"
                        class="dinheiro4casas"
                        type="text"
                        onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL); "
                        onBlur=" onChangeValorEstimadoItem('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL)" />
                        <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? $materiais[$itr]['valorEstimado'] : ''; ?>
                <?php } else {
                    echo $materiais[$itr]['valorEstimado'];
                }
                ?>
            </td>

    <?php
    if (! $ocultarCampoExercicio) {
        // condicoes em que campos são desabilitados
        if ($ifVisualizacaoThenReadOnly) {
            $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
        } elseif (moeda2float($materiais[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
            $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
        } else {
            $ifVisualizacaoQtdeExercicioThenReadOnly = '';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
        }
        ?>
        <?php
    }
    ?>
        <?php if (! $ocultarCampoFornecedor) { ?>
            <td class="textonormal" align="center">
                <?php
                $CnpjStr = FormataCpfCnpj($materiais[$itr]['fornecedor']);                
                if (! $ocultarCamposEdicao) { ?>
                <input
                    name="MaterialFornecedor[<?php echo  $itr ?>]"
                    id="MaterialFornecedor[<?php echo  $itr ?>]"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    <?php echo  $ifVisualizacaoThenReadOnlyFornecedorItens?>
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    size="18"
                    maxlength="18"
                    value="<?php echo  $CnpjStr ?>"
                    type="text"
                    onChange="validaFornecedor('MaterialFornecedor[<?php echo  $itr ?>]', 'MaterialFornecedorNome[<?php echo  $itr ?>]',<?php echo  $materiais[$itr]['codigo'] ?>, TIPO_ITEM_MATERIAL);
                                            AtualizarFornecedorValor('<?php echo  $itr ?>', TIPO_ITEM_MATERIAL);" /> 
                <input
                    name="MaterialFornecedorValor[<?php echo  $itr ?>]"
                    id="MaterialFornecedorValor[<?php echo  $itr ?>]"
                    value="<?php echo  $CnpjStr ?>"
                    type="hidden" />
                <?php } ?>
                <div id="MaterialFornecedorNome[<?php echo  $itr ?>]" >
                    <?php echo  $CnpjStr ?> <br>
                    <?php
                        if (! is_null($materiais[$itr]['fornecedor'])) {
                            $CPFCNPJ = removeSimbolos($materiais[$itr]['fornecedor']);
                            $materialServicoFornecido = $materiais[$itr]['codigo'];
                            $tipoMaterialServico = TIPO_ITEM_MATERIAL;

                            require 'RotDadosFornecedor.php';
                        }
                        $db = Conexao();
                    ?>
                </div>
            </td>
            <td class="textonormal" align="center" width="10" >
            <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    name="MaterialMarca[<?php echo  $itr ?>]"
                    id="MaterialMarca[<?php echo  $itr ?>]"
                    size="18"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    maxlength="18"
                    value="<?php echo  $materiais[$itr]['marca'] ?>"
                    class="textonormal"
                    type="text" >
                    <?php echo $materiais[$itr]['marca'] . '&nbsp;'; ?>
                <?php } else {
                echo $materiais[$itr]['marca'] . '&nbsp;';
            }
            ?>
            </td>
            <td class="textonormal" align="center" width="10" >
            <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    name="MaterialModelo[<?php echo  $itr ?>]"
                    id="MaterialModelo[<?php echo  $itr ?>]"
                    size="18"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    maxlength="18"
                    value="<?php echo  $materiais[$itr]['modelo'] ?>"
                    class="textonormal"
                    type="text">
                    <?php echo $materiais[$itr]['modelo'] . '&nbsp;'; ?>
            <?php } else {
                echo $materiais[$itr]['modelo'] . '&nbsp;';
            }
            ?>
            </td>
        <?php } ?>
                <!--  Coluna 9 =  Valor Total -->
            <td class="textonormal" align="right" width="10" >
                <div id="MaterialValorTotal[<?php echo  $itr ?>]"><?php echo  converte_valor_estoques($ValorTotalItem) ?></div>
            </td>
        </tr>
<?php } ?>

<?php if ($QuantidadeMateriais <= 0) { ?>
<tr>
    <td class="textonormal itens_material" colspan="<?php echo  ($qtdeColunas - $colunasOcultas) ?>" >Nenhum item de material informado</td>
</tr>
<!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
<?php } ?>
        <tr>
            <td colspan="0" class="titulo3 itens_material menosum" >VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL</td>
            <td class="textonormal" align="right" >
                <div id="MaterialTotal"><?php echo  converte_valor_estoques($ValorTotal) ?></div>
            </td>
        </tr>
    </tbody>
</table>

<!--  Serviços  -->
<table id="scc_servico" summary="" bgcolor="bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
    <tbody>
        <tr>
            <td colspan="17" class="titulo3" align="center" bgcolor="#75ADE6" valign="middle" >ITENS DA SOLICITAÇÃO DE SERVIÇO</td>
        </tr>
            <?php
                $qtdeColunas = 7;
                $colunasOcultas = 0;
                if ($ocultarCampoExercicio) {
                    $colunasOcultas += 3;
                }
                if ($ocultarCampoFornecedor) {
                    $colunasOcultas += 1;
                }
            ?>

        <!-- Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
        <tr class="head_principal_servico">
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >ORD</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" width="<?php echo  $descricaoWidth ?>" /> DESCRIÇÃO DO SERVIÇO </td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >CÓD.RED. CADUS</td> 
            <td class="textoabason" align="center" bgcolor="#DCEDF7" /> DESCRIÇÃO DETALHADA </td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7"><?php echo (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] == 1) ? 'QUANTIDADE' : 'QUANTIDADE' ?></td>            
            <td style="<?php echo $exibirValorSolicitado; ?>" class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR SOLICITADO</td>
            <td style="<?php echo $exibirValorEstimado; ?>" class="textoabason" align="center" bgcolor="#DCEDF7" ><?php echo  (($TipoCompra == TIPO_COMPRA_SARP)) ? 'VALOR' : 'VALOR ESTIMADO'; ?></td>
            
            <?php if (! $ocultarCampoFornecedor) {
                ++ $qtdeColunas;
                ?>
                <td class="textoabason" align="center" bgcolor="#DCEDF7">CPF/CNPJ DO FORNECEDOR</td>
            <?php } ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR TOTAL</td>
        </tr>
        <!-- FIM Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->

<?php
// Serviços do POST-----------------------------------
$Quantidade = count($servicos);
$ValorTotalItem = 0;
$ValorTotal = 0;

for ($itr = 0; $itr < $Quantidade; ++ $itr) {
    $_valor = (isset($_SESSION['tipoControle']) && $_SESSION['tipoControle'] != 0) ? $servicos[$itr]['vitescunit'] : $servicos[$itr]['valorEstimado'];
    $ValorTotalItem = moeda2float($servicos[$itr]['quantidade']) * moeda2float($_valor);
    $ValorTotal += $ValorTotalItem;
    if (! $ocultarCampoExercicio) {
        $ValorTotalExercicio = moeda2float($servicos[$itr]['quantidadeExercicio']) * moeda2float($_valor);
        $TotalDemaisExercicios = $ValorTotalItem - $ValorTotalExercicio;
        if ($TotalDemaisExercicios < 0) {
            $TotalDemaisExercicios = 0;
        }
    }
    ?>
        <!-- Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
        <tr>
            <!--   Coluna 1 => Ordem   -->
            <td class="textonormal" align="center" >
                <?php echo  ($itr + 1)?>
            </td>
            <!--  Coluna 2 => Descricao -->
            <td class="textonormal">
                <?php if (! $ocultarCamposEdicao) { ?>
                    <input name="ServicoCheck[<?php echo  $itr ?>]"
                    <?php if ($servicos[$itr]['check']) { echo 'checked'; } ?>
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    type="checkbox" >
                <?php } ?>

                <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?php echo  $servicos[$itr]['codigo'] ?>&amp;TipoGrupo=S&amp;ProgramaOrigem=<?php echo  $programa ?>',700,370);" > 
                    <font color="#000000"><?php echo  $servicos[$itr]['descricao'] ?></font>
                </a>
            </td>
            <!--  Coluna 3 => Código Red -->
            <td class="textonormal" align="center" >
                <?php echo  $servicos[$itr]['codigo']?>
                <input value="<?php echo  $servicos[$itr]['codigo_item'] ?>" name="ServicoCodItem[<?php echo  $itr ?>]" type="hidden" />
                <input value="<?php echo  $servicos[$itr]['codigo'] ?>" name="ServicoCod[<?php echo  $itr ?>]" type="hidden"> 
                <input value="<?php
                    // echo ($servicos[$itr]["isObras"]) ? 'true' : 'false';
                    echo ($servicos[$itr]['isObras']) ? 'true' : 'true';
                    ?>"
                    name="ServicoIsObras[<?php echo  $itr ?>]" id="ServicoIsObras_<?php echo  $itr ?>" type="hidden" />
            </td>
            
            <!--  Coluna 4 => Descrição Detalhada -->
            <td class="textonormal" align="center" width="300px">
            <?php if($_SESSION['_cperficodi_'] != 2){?>
                <input value="<?php echo  $servicos[$itr]['descricaoDetalhada'] ?>" id="ServicoDescricaoDetalhada[<?php echo  $itr ?>]" name="ServicoDescricaoDetalhada[<?php echo  $itr ?>]" type="hidden">     
                <?php
                        echo '<p style="word-wrap:  break-word; width: 300px">'.$servicos[$itr]['descricaoDetalhada'].'<p>';
                        // echo $servicos[$itr]['descricaoDetalhada'];
                }else{ ?>
                    <textarea rows="4" style="word-wrap:  break-word; width: 300px" value="<?php echo  $servicos[$itr]['descricaoDetalhada'] ?>" id="ServicoDescricaoDetalhada[<?php echo  $itr ?>]" name="ServicoDescricaoDetalhada[<?php echo  $itr ?>]"><?php echo $servicos[$itr]['descricaoDetalhada'];?></textarea>
            <?php }?>
            
                
            </td>
            <!--  Coluna 5 => Quantidade -->
            <td class="textonormal" align="center" >
                <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    class="dinheiro4casas"
                    value="<?php echo  $servicos[$itr]['quantidade'] ?>"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    maxlength="16"
                    size="11"
                    name="ServicoQuantidade[<?php echo  $itr ?>]"
                    id="ServicoQuantidade[<?php echo  $itr ?>]"
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    type="text"
                    onKeyUp="onChangeItemQuantidade('<?php echo  $itr ?>', TIPO_ITEM_SERVICO);" />
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? $servicos[$itr]['quantidade'] : ''; ?>
                <?php } else {
                    echo $servicos[$itr]['quantidade'];
                }
                ?>
            </td>
            <!--  Coluna 6 => Valor solicitado -->            
            <td class="textonormal" align="center" width="10" style="<?php echo $exibirValorSolicitado; ?>" >
                <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    name="ServicoVitescunit[<?php echo  $itr ?>]"
                    id="ServicoVitescunit[<?php echo  $itr ?>]"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    size="16"
                    maxlength="16"
                    value="<?php echo  $servicos[$itr]['vitescunit'] ?>"
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    class="dinheiro4casas"
                    type="text"
                    onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO); " />
                    <?php echo ($TipoCompra == TIPO_COMPRA_SARP) ? $servicos[$itr]['vitescunit'] : ''; ?>
                <?php } else {
                    echo $servicos[$itr]['vitescunit'];
                }
                ?>
            </td>
            
            <!--  Coluna 6 => Valor Extimado -->            
            <td class="textonormal" align="center" width="10" style="<?php echo $exibirValorEstimado; ?>" >
                <?php if (! $ocultarCamposEdicao) { ?>
                <input
                    name="ServicoValorEstimado[<?php echo  $itr ?>]"
                    id="ServicoValorEstimado[<?php echo  $itr ?>]"
                    <?php echo  $ifVisualizacaoThenReadOnly?>
                    size="16"
                    maxlength="16"
                    value="<?php echo  $servicos[$itr]['valorEstimado'] ?>"
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    class="dinheiro4casas"
                    type="text"
                    onKeyUp="onChangeItemValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO); "
                    onBlur="onChangeValorEstimadoItem('<?php echo  $itr ?>', TIPO_ITEM_SERVICO)" />
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? $servicos[$itr]['valorEstimado'] : ''; ?>
                <?php } else {
                    echo $servicos[$itr]['valorEstimado'];
                }
                ?>
            </td>
        <?php
        if (! $ocultarCampoExercicio) {
            // condicoes em que campos são desabilitados
            if ($ifVisualizacaoThenReadOnly) {
                $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
            } elseif (moeda2float($servicos[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
                $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
            } else {
                $ifVisualizacaoQtdeExercicioThenReadOnly = '';
                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
            }
            ?>
                <?php
        }
        ?>
        <?php if (! $ocultarCampoFornecedor) { ?>
        <td class="textonormal" align="center" width="10%">
            <?php
            $CnpjStr = FormataCpfCnpj($servicos[$itr]['fornecedor']);

            if (! $ocultarCamposEdicao) { ?>
                <input name="ServicoFornecedor[<?php echo  $itr ?>]" id="ServicoFornecedor[<?php echo  $itr ?>]"
                    <?php echo  $ifVisualizacaoThenReadOnly?>  <?php echo  $ifVisualizacaoThenReadOnlyFornecedorItens?>
                    <?php echo  ($TipoCompra == TIPO_COMPRA_SARP) ? 'readonly style="display:none"' : ''; ?>
                    size="18" maxlength="18" value="<?php echo  $CnpjStr ?>" type="text"
                    onChange="validaFornecedor('ServicoFornecedor[<?php echo  $itr ?>]', 'ServicoFornecedorNome[<?php echo  $itr ?>]',<?php echo  $servicos[$itr]['codigo'] ?>, TIPO_ITEM_SERVICO);
                                        AtualizarFornecedorValor('<?php echo  $itr ?>', TIPO_ITEM_SERVICO);" /> 
                <input name="ServicoFornecedorValor[<?php echo  $itr ?>]" id="ServicoFornecedorValor[<?php echo  $itr ?>]" value="<?php echo  $CnpjStr ?>" type="hidden" />
                <?php
            }
            ?>
            <div id='ServicoFornecedorNome[<?php echo  $itr ?>]' />
                <?php echo  $CnpjStr ?> <br>
                <?php 
                if (! is_null($servicos[$itr]['fornecedor'])) {
                    $CPFCNPJ = removeSimbolos($servicos[$itr]['fornecedor']);
                    $materialServicoFornecido = $servicos[$itr]['codigo'];
                    $tipoMaterialServico = TIPO_ITEM_SERVICO;
                    require 'RotDadosFornecedor.php';
                }
                $db = Conexao();
                ?>
            </div>
        </td>
        <?php } ?>
         <!--  Coluna 7 => Valor Total -->
        <td class="textonormal" align="right" width="10" >
            <div id="ServicoValorTotal[<?php echo  $itr ?>]"><?php echo  converte_valor_estoques($ValorTotalItem) ?></div>
        </td>
    </tr>
<?php
}
?>
        <?php if ($Quantidade <= 0) { ?>
        <tr>
            <td class="textonormal itens_servico" colspan="<?php echo  ($qtdeColunas - 1) ?>" >
            Nenhum item de serviço informado</td>
        </tr>
        <?php } ?>
         <!-- FIM Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
        <tr>
            <td class="titulo3" colspan="<?php echo  ($qtdeColunas - 1) ?>" >
                VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO
            </td>
            <td class="textonormal" align="right" >
                <div id="ServicoTotal"><?php echo  converte_valor_estoques($ValorTotal) ?></div>
            </td>
        </tr>

        <tr>
        <?php  
            if($acaoPagina != ACAO_PAGINA_ACOMPANHAR) {
                if (! $ocultarCamposEdicao && $TipoCompra != TIPO_COMPRA_SARP) {
                    if (! $ocultarBotaoItem) { 
                        ?>
                    <td class="textonormal" colspan="<?php echo  ($qtdeColunas - $colunasOcultas) + 4 ?>" align="center">
                        <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('../estoques/CadIncluirItem.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 350);" type="button">                                                 
                        <input name="RetirarItem" value="Retirar Item" class="botao" onclick="javascript:enviar('Retirar');" type="button">
                        <?php
                    }
                } else if($TipoCompra == TIPO_COMPRA_SARP && !empty($urlItensAta)) { ?>
                    <td class="textonormal" colspan="<?php echo  ($qtdeColunas - $colunasOcultas) ?>" align="center">
                    <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('../compras/<?php echo $urlItensAta; ?>', 700, 350);" type="button"> 
                    <input name="RetirarItem" value="Retirar Item" class="botao" onclick="javascript:enviar('Retirar');" type="button">
            <?php 
                } 
            }   ?>
            </td>
        </tr>  
    </tbody>
        </table></td>
                <?php #--------------------------Fim Itens ?>
</tr>
<tr>
    <td class="textonormal" colspan="4" >
        <table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="" >
            <tr>
                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7" >
                    ANEXAÇÃO DE DOCUMENTO(S)
                </td>
            </tr>
            <?php if (! $ocultarCamposEdicao) { ?>
            <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%" valign="top" >
                    Anexação de Documentos
                </td>
                <td class="textonormal">
                    <table border="0" width="100%" summary="" >
                        <tr>
                            <td>
                                <input type="file" name="Documentacao" class="textonormal" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php } ?>
            
            <?php
            $DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

            if ($DTotal == 0) { ?>
            <tr>
                <td class="textonormal" colspan='2' >
                Nenhum documento informado</td>
            </tr>
        <?php } ?>

        <?php 
            for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
                    echo '<tr>';
                    if (! $ocultarCamposEdicao) {
                        echo "<td align='right' ><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
                    }
                    echo "<td class='textonormal' >";
                    if (! $ocultarCamposEdicao) {
                        echo $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                    } else {
                        $arquivo = 'compras/' . $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                        addArquivoAcesso($arquivo);

                        echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload']['nome'][$Dcont] . '</a>';
                    }
                    echo '</td></tr>';
                }
            }
        ?>
        
        <?php if (! $ocultarCamposEdicao) { ?>
            <tr>
                <td class="textonormal" colspan="7" align="center" >
                    <input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir_Documento');" >
                     <input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao" onClick="javascript:enviar('Retirar_Documento');" >
                </td>
            </tr>
        <?php } ?>
    </table>
    </td>
</tr>
<tbody>

<!-- HISTORICO DE SCC -->
<?php if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {?>
<tr>
    <td class="textonormal" colspan="4" >
        <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
            <tbody>
                <tr>
                    <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">
                        HISTÓRICO DA SITUAÇÃO DA SCC
                    </td>
                </tr>
                <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" > SITUAÇÃO </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" > DATA </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" > RESPONSÁVEL </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" > TELEFONE </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%" > EMAIL </td>
                </tr>
                <?php
                $sql = " SELECT ss.esitsonome, hss.thsitsdata, u.eusuporesp, u.eusupomail, u.ausupofone, hss.csitsocodi
                    FROM SFPC.TBhistsituacaosolicitacao hss, SFPC.TBsituacaosolicitacao ss, SFPC.TBusuarioportal u
                        WHERE hss.csitsocodi = ss.csitsocodi
                            and hss.cusupocodi = u.cusupocodi
                            and csolcosequ = $Solicitacao
                            ORDER BY hss.thsitsdata DESC ";

                $res = executarSQL($db, $sql);
                while ($linha = $res->fetchRow()) {
                    $nomeSituacao = $linha[0];

                    // ---------------------------
                    // Se situação = Licitação
                    // ---------------------------
                    if ($linha[5] == 9) {
                        $vetor = getChaveLicitacao($Solicitacao, $db);
                        $descComissao = getDescComissao($vetor[3], $db);
                        if ($vetor[1] == '999') {
                            $nomeSituacao .= $descComissao;
                        } else {
                            $nomeSituacao .= ' - PL ' . $vetor[0] . '/' . $vetor[1] . ' - ' . $descComissao;
                        }
                    }

                    // ---------------------------
                    // Se situação = Encaminhada
                    // ---------------------------
                    if ($linha[5] == 8) {
                        $row = getDadosSolicitacao($Solicitacao, $db);
                        $descComissao = getDescComissao($row->comissao, $db);
                        $nomeSituacao .= ' - ' . $descComissao;
                    }

                    $dataSituacao = DataBarra($linha[1]) . ' ' . hora($linha[1]);
                    $nomeUsuario = $linha[2];
                    $emailUsuario = $linha[3];
                    $foneUsuario = $linha[4];
                ?>

                <tr style="text-align: center">
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%" > <?php echo  $nomeSituacao ?> </td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%" ><?php echo  $dataSituacao ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%" ><?php echo  $nomeUsuario ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%" ><?php echo  $foneUsuario ?>&nbsp;</td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" width="30%" ><?php echo  $emailUsuario ?>&nbsp;</td>
                </tr>
        <?php } ?>
            </tbody>
        </table> <!-- Inserir aqui itens pre-solicitacao de empnho(HERALDO BOTELHO)                        -->
    </td>
</tr>
<tr>
    <td class="textonormal" colspan="4" >
        <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%" >
            <tbody>
                <tr>
                    <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle" >PRÉ-SOLICITAÇÃO DE EMPENHO (PSE)</td>
                </tr>
                <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >NÚMERO/ANO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >DATA/HORA GERAÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >NÚMERO BLOQUEIO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >VALOR</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >FORNECEDOR</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >SITUAÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" >DATA SITUAÇÃO</td>
                </tr>
<?php
    $sql = sqlDadosScc($Solicitacao);   
    $result = executarTransacao($db, $sql);
    // $numRows = $result->numRows(); // está falhando aqui

    $contAux = 0;
    while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
        $contAux = $contAux + 1;

        // --------Formatar bloqueio
        if (! empty($row->anobloqueio) && ! empty($row->bloqueio)) {
            $vetor = getDadosBloqueioFromChave($dbOracle, $row->anobloqueio, $row->bloqueio);
        }
        $blqFormato = $vetor['bloqueio'];

        // -------Formatar cpf/cgc de fornecedor
        if (! empty($row->cpf)) {
            $cpfcgc = $row->cpf;
        } else {
            $cpfcgc = $row->cgc;
        }

        $cpfcgcAux = FormataCpfCnpj($cpfcgc);

        // -------Formatar soma
        $soma = number_format($row->soma, 4, ',', '.');

        // -------Formatar mensagens da situacao e datas
        if (! empty($row->idmotivo)) {
            $descSituacao = 'PSE RECUSADA POR MOTIVO DE ' . $row->descricao;
            $dataMotivo = $row->datault;
        }

        if (! empty($row->dataimportacao)) {
            $descSituacao = 'SE GERADA';
            $dataMotivo = $row->datault;
        }

        if (! empty($row->datacancel)) {
            $descSituacao = 'SE CANCELADA';
            $dataMotivo = $row->datacancel;
        }

        if (! empty($row->datageracao)) {
            $descSituacao = 'EMPENHADO (NÚMERO=' . $row->numemp . '/' . $row->anoemp . ')';
            $dataMotivo = $row->datageracao;
        }

        if (! empty($row->dataanulacao)) {
            $descSituacao = 'EMPENHO ANULADO (VALOR=' . number_format($row->valoranulado, 4, ',', '.') . ')';
            $dataMotivo = $row->dataanulacao;
        }
?>

                <tr style="text-align: left">
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $row->numero.'/'.$row->ano ?> </td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $row->datahora ?> </td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $blqFormato ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $soma ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $cpfcgcAux.' '.$row->razao ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $descSituacao ?></td>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top" align="center" ><?php echo $dataMotivo ?></td>
                </tr>
                <?php if ($contAux == 0) { ?>
                <tr style="text-align: left">
                    <td class="textonormal" bgcolor="#DCEDF7" colspan=7 height="20" valign="top" align="left" >Nenhum item de pré-solicitação informado</td>
                </tr>   
                <?php } ?>
                </tbody>
            </table>
        </table>
    </td>
</tr>
<?php } // se ser erro verificar esse if TODO remover ?> 
</table>
</td>
</tr>
<tr>
    <td class="textonormal" align="right" colspan="4">
    <?php if($programa != 'window') { ?>
        <input type="button" name="Imprimir" value="Imprimir" class="botao" onClick="javascript:enviar('Imprimir');" /> 
    <?php }?>
        <!--<input type="hidden" name="Botao" value=""> -->
    <?php if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) { ?>
        <input type="submit" name="Voltar" value="Voltar" class="botao"
            onClick="<?php echo ($programa == 'window') ? 'window.close()' : 'javascript:onButtonVoltar();'?>">
    <?php } ?>
    </td>
</tr>
<!--  final heraldo botelho -->

<?php } ?>
<!-- FIM DE HISTORICO DE SCC -->
</tbody>
</table>
<tr>
    <td class="textonormal" align="right" >
    <input type="hidden" name="InicioPrograma" value="1" > 
        <input type="hidden" name="RetirarDocs" value="<?php echo  $RetirarDocs ?>" > 
        <input type="hidden" name="Solicitacao" value="<?php echo  $Solicitacao ?>" > 
        <input type="hidden" name="Botao" value="" > 
        <input type="hidden" name="Foco" value="" > 
        <input type="hidden" name="SeqSolicitacao" value="<?php echo  $Solicitacao ?>" > 
        <input type="hidden" name="isDotacaoAnterior" value="<?php echo  $isDotacao ?>" >
        <?php if ($acaoPagina == ACAO_PAGINA_INCLUIR) { ?> 
            <input type="button" name="Rascunho" value="Salvar Rascunho" class="botao" onClick="javascript:enviar('Rascunho');" > 
            <input type="button" name="Incluir" value="Incluir Solicitação" class="botao" onClick="javascript:onButtonIncluir();" >
        <?php } elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
                if ($situacaoSolicitacaoAtual == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) {         ?>
                <input type="button" name="Rascunho" value="Manter Rascunho" class="botao" onClick="javascript:enviar('ManterRascunho');" >
            <?php } ?>
                <input type="button" name="Manter" value="Manter Solicitação" class="botao" onClick="javascript:onButtonManter();" >
        <?php } elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) { ?>
                <input type="button" name="Excluir" value="Cancelar Solicitação" class="botao" onClick="javascript:enviar('Excluir');" >
        <?php } ?>
        <?php if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_MANTER) { ?>
            <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:onButtonVoltar();" >
        <?php } ?>

    <!--  ?php if ($acaoPagina != ACAO_PAGINA_INCLUIR) { ?>
                                        <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:history.back(1);">
     } ?-->
    </td>
</tr>
</table>
</form>
<script language="javascript" type="" >
    qtdeMateriais        = <?php echo  count($materiais) ?>;
    qtdeServicos         = <?php echo  count($servicos) ?>;
    campoExercicioExiste = <?php echo  ($ocultarCampoExercicio) ? 'false' : 'true'; ?>;

    // ITENS DA SOLICITAÇÃO DE MATERIAL colspan
    $('td.itens_material').attr('colspan', contador('head_principal'));
    $('td.menosum').attr('colspan', contador('head_principal')-2);
    // ITENS DA SOLICITAÇÃO DE SERVIÇO colspan
    $('td.itens_servico').attr('colspan', contador('head_principal_servico'));
    $('td.menosum_servico').attr('colspan', contador('head_principal_servico')-1);

    var formulario = document.CadSolicitacaoCompraIncluirManterExcluir;

    <?php
    if (! is_null($Foco) and $Foco != '') {
        ?>
        document.CadSolicitacaoCompraIncluirManterExcluir.<?php echo  $Foco ?>.focus();
        <?php
    }
    ?>
            <?php
            if ($isDotacao) {
                // echo "passou";
                // exit;
                ?>
         mudarBloqueioDotacao(TIPO_RESERVA_DOTACAO);
        <?php
            } else {
                ?>
                    mudarBloqueioDotacao(TIPO_RESERVA_BLOQUEIO);
                <?php
            }
            $db->disconnect();
            $dbOracle->disconnect();

            echo $JSCriacaoLimiteCompra; // imprime JS que gera todos valores de limite
            ?>

</script>
<?php
// Pegando output gerado fora do template e incluindo na posição correta no template

$outputNaoTratratado = ob_get_contents();
ob_clean();
$template->FINAL = $outputNaoTratratado;

$template->show();

// TODO unset($_SESSION['tipoControle'])