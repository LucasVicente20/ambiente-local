<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraManterExcluirEspecial.php
# Autor: Pitang
// ############################################################################
// Alterado: Pitang Agile TI
// Data:     14/07/2015
// Objetivo: Requisito 73624 - TRP - Fase Licitação Incluir - Nova regra para a TRP
// ############################################################################

require_once 'funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');
AddMenuAcesso('/estoques/CadIncluirCentroCusto.php');
AddMenuAcesso('/compras/RotDadosFornecedor.php');
AddMenuAcesso('/compras/ConsProcessoPesquisar.php');
AddMenuAcesso('/compras/RelAcompanhamentoSCCPdf.php');
AddMenuAcesso('/compras/RelTRPConsultar.php');
AddMenuAcesso('/compras/RelTRPConsultarDireta.php');
AddMenuAcesso('/compras/InfPreenchimentoBloqueios.php');

// Volta para o programa de origem
if (is_null($programaSelecao)) {
    AddMenuAcesso('/compras/' . $programaSelecao);
} else {
    if ($programa == 'CadLicitacaoIncluir.php') {

    } else {
        AddMenuAcesso('compras/' . $programa);
    }
}

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e"
# Recebendo variáveis via POST #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $InicioPrograma = $_POST['InicioPrograma'];
    //$Orgao               = $_POST['Orgao'];
    $CentroCusto = $_POST['CentroCusto'];
    //$OrgaoUsuario        = $_POST['OrgaoUsuario'];
    $Observacao = strtoupper2($_POST['Observacao']);
    $Objeto = strtoupper2($_POST['Objeto']);
    $Justificativa = strtoupper2($_POST['Justificativa']);

    /*
      # contadores de caracteres agora estão sendo setados em baixo a partir da variável associada
      $NCaracteresObservacao = $_POST['NCaracteresObservacao'];
      $NCaracteresObjeto = $_POST['NCaracteresObjeto'];
      $NCaracteresJustificativa = $_POST['NCaracteresJustificativa'];
     */

    $DataDom = $_POST['DataDom'];
    $Lei = $_POST['Lei'];
    $Artigo = $_POST['Artigo'];
    $Inciso = $_POST['Inciso'];
    $Foco = $_POST['Foco'];
    $TipoLei = $_POST['TipoLei'];
    $RegistroPreco = $_POST['RegistroPreco'];
    $Sarp = $_POST['Sarp'];
    $TipoReservaOrcamentaria = $_POST['TipoReservaOrcamentaria']; // se é bloqueio (1) ou dotação (2)
    $DotacaoTodos = $_POST['DotacaoTodos'];
    $CnpjFornecedor = $_POST['CnpjFornecedor'];
    $GeraContrato = $_POST['GeraContrato'];
    $TipoCompra = $_POST['TipoCompra'];
    $NomeDocumento = $_POST['NomeDocumento'];
    $DDocumento = $_POST['DDocumento'];
    $OrigemBancoPreços = $_POST['OrigemBancoPreços'];
    $NumProcessoSARP = $_POST['NumProcessoSARP'];
    $AnoProcessoSARP = $_POST['AnoProcessoSARP'];
    $ComissaoCodigoSARP = $_POST['ComissaoCodigoSARP'];
    $OrgaoLicitanteCodigoSARP = $_POST['OrgaoLicitanteCodigoSARP'];
    $GrupoEmpresaCodigoSARP = $_POST['GrupoEmpresaCodigoSARP'];
    $CarregaProcessoSARP = $_POST['CarregaProcessoSARP'];
    $isDotacaoAnterior = $_POST['isDotacaoAnterior']; // informa se na pagina anterior era dotação ou bloqueio
    $Bloqueios = $_POST['Bloqueios'];
    $BloqueiosCheck = $_POST['BloqueiosCheck'];

    $BloqueioAno = $_POST['BloqueioAno'];
    $BloqueioOrgao = $_POST['BloqueioOrgao'];
    $BloqueioUnidade = $_POST['BloqueioUnidade'];
    $BloqueioDestinacao = $_POST['BloqueioDestinacao'];
    $BloqueioSequencial = $_POST['BloqueioSequencial'];

    $DotacaoAno = $_POST['DotacaoAno'];
    $DotacaoOrgao = $_POST['DotacaoOrgao'];
    $DotacaoUnidade = $_POST['DotacaoUnidade'];
    $DotacaoFuncao = $_POST['DotacaoFuncao'];
    $DotacaoSubfuncao = $_POST['DotacaoSubfuncao'];
    $DotacaoPrograma = $_POST['DotacaoPrograma'];
    $DotacaoTipoProjetoAtividade = $_POST['DotacaoTipoProjetoAtividade'];
    $DotacaoProjetoAtividade = $_POST['DotacaoProjetoAtividade'];
    $DotacaoElemento1 = $_POST['DotacaoElemento1'];
    $DotacaoElemento2 = $_POST['DotacaoElemento2'];
    $DotacaoElemento3 = $_POST['DotacaoElemento3'];
    $DotacaoElemento4 = $_POST['DotacaoElemento4'];
    $DotacaoFonte = $_POST['DotacaoFonte'];

    $MaterialCheck = $_POST['MaterialCheck'];
    $MaterialCod = $_POST['MaterialCod'];
    $MaterialTrp = $_POST['MaterialTrp'];
    $MaterialQuantidade = $_POST['MaterialQuantidade'];
    $MaterialValorEstimado = $_POST['MaterialValorEstimado'];
    $MaterialQuantidadeExercicio = $_POST['MaterialQuantidadeExercicioValor'];
    $MaterialTotalExercicio = $_POST['MaterialTotalExercicioValor'];
    $MaterialMarca = $_POST['MaterialMarca'];
    $MaterialModelo = $_POST['MaterialModelo'];
    $MaterialFornecedor = $_POST['MaterialFornecedorValor'];
    /**
     * [$MaterialDescricaoDetalhada description]
     * @var string
     */
    $MaterialDescricaoDetalhada = $_POST['MaterialDescricaoDetalhada'];
    //$MaterialBloqueio                    = $_POST['MaterialBloqueio'];
    //$MaterialBloqueioItem              = $_POST['MaterialBloqueioItem'];
    $ServicoCheck = $_POST['ServicoCheck'];
    $ServicoCod = $_POST['ServicoCod'];
    $ServicoQuantidade = $_POST['ServicoQuantidade'];
    $ServicoDescricaoDetalhada = $_POST['ServicoDescricaoDetalhada'];
    $ServicoQuantidadeExercicio = $_POST['ServicoQuantidadeExercicioValor'];
    $ServicoValorEstimado = $_POST['ServicoValorEstimado'];
    $ServicoTotalExercicio = $_POST['ServicoTotalExercicioValor'];
    $ServicoFornecedor = $_POST['ServicoFornecedorValor'];
    //$ServicoBloqueio                   = $_POST['ServicoBloqueio'];
    //$ServicoBloqueioItem             = $_POST['ServicoBloqueioItem'];
    $Solicitacao = $_POST['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'
    $Numero = $_POST['Numero'];
    $DataSolicitacao = $_POST['DataSolicitacao'];
} elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Solicitacao = $_GET['SeqSolicitacao'];
    if (is_null($Solicitacao)) {
        #inicio de uma inclusão. excluir arquivos na sessão
        unset($_SESSION['Arquivos_Upload']);
    }
}

// echo "Tipo de compra= $TipoCompra  Registro de Preço= $RegistroPreco ";
//  exit;
# Variáveis para teste
$desabilitarChecagemFornecedorSistemaMercantil = false; // correto é false. se true, permite inclusão de fornecedores que não passaram na checagem do cadastro mercantil
$desabilitarChecagemBloqueioSofin = false; // correto é false. se true, não valida bloqueios no sistema do sofin e valores de bloqueio (só valida bloqueio pelo portal)
// $desabilitarChecagemFornecedorSistemaMercantil = true;
// $desabilitarChecagemBloqueioSofin = true;

$db = Conexao();
$dbOracle = ConexaoOracle();

$sql = " select qpargetmaobjeto, qpargetmajustificativa, qpargedescse,
                        epargesubelemespec, qpargeqmac, qpargeqmac, epargetdov
             from sfpc.tbparametrosgerais ";
$linha = resultLinhaUnica(executarSQL($db, $sql));
if (is_null($linha)) {
    echo "<br/><br/><br/><br/><br/><br/>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<b>Falha do sistema, pois os Parâmetros Gerais precisam ser preenchidos. Vá em em 'Tabelas > Parâmetros Gerais' e preencha os campos.</b>";
}

$tamanhoObjeto = $linha[0];
$tamanhoJustificativa = $linha[1];
$tamanhoDescricaoServico = $linha[2];
$subElementosEspeciais = explode(',', $linha[3]);
$tamanhoArquivo = $linha[4];
$tamanhoNomeArquivo = $linha[5];
$extensoesArquivo = $linha[6];
$cargaBloqueiosManter = false; //informa se é primeiro carregamento e existem bloqueios, para serem carregados para o javascript

if ($Botao == "Voltar") {
    $_SESSION["carregarSelecionarDoSession"] = true;
    if ($programa == 'CadLicitacaoIncluir.php') {
        $programaSelecao = $programa;
    } else {
        if (is_null($programaSelecao)) {
            $programaSelecao = '../licitacoes/CadLicitacaoIncluir.php';
        }
    }
    header("location: " . $programaSelecao);
    exit;
} elseif ($Botao == "Imprimir") {
    $Url = "RelAcompanhamentoSCCPdf.php?Solicitacao=" . $Solicitacao;
    header("location: " . $Url);
    exit;
}
# recuperando dados da SCC (acompanhamento, manter, excluir)
if (
        ($acaoPagina == ACAO_PAGINA_MANTER and is_null($CentroCusto)) or //em manter apenas recuperar dados quando ainda não foi preenchido
        $acaoPagina == ACAO_PAGINA_ACOMPANHAR or
        $acaoPagina == ACAO_PAGINA_EXCLUIR
) {

    if (is_null($Solicitacao)) { //solicitação nao foi informada. voltar para seleção de solicitacao
        header("location: " . $programaSelecao);
        exit;
    } else {
        $cargaBloqueiosManter = true;

        #recuperando dados da SCC informada

        $sql = " SELECT ccenposequ, esolcoobse, esolcoobje, esolcojust, asolcoanos, --04
            ctpcomcodi, tsolcodata, clicpoproc, alicpoanop, ccomlicodi, --09
            corglicod1, cgrempcodi, dsolcodpdo, ctpleitipo, cleiponume, --14
            cartpoarti, cincpainci, fsolcorgpr, fsolcorpcp, fsolcocont, csolcotipcosequ --20
            FROM SFPC.TBsolicitacaocompra
            WHERE csolcosequ = $Solicitacao ";

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        $cargaInicial = true;
        $CentroCusto = $linha[0];
        $Observacao = $linha[1];
        $Objeto = $linha[2];
        $Justificativa = $linha[3];
        $Ano = $linha[4];
        $Numero = $linha[20];
        $TipoCompra = $linha[5];
        $DataHora = $linha[6];
        $DataSolicitacao = DataBarra($DataHora);
        $DataDom = DataBarra($linha[12]);
        $Lei = $linha[14];
        $Artigo = $linha[15];
        $Inciso = $linha[16];
        $TipoLei = $linha[13];
        $RegistroPreco = $linha[17];
        $Sarp = $linha[18];
        $TipoReservaOrcamentaria = 1; // se é bloqueio (1) ou dotação (2)
        $GeraContrato = $linha[19];
        $NumProcessoSARP = $linha[7];
        $AnoProcessoSARP = $linha[8];
        $ComissaoCodigoSARP = $linha[9];
        $OrgaoLicitanteCodigoSARP = $linha[10];
        $GrupoEmpresaCodigoSARP = $linha[11];
        $CarregaProcessoSARP = 1;

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
				    sc.eitescdescmat
				FROM
				    SFPC.TBitemsolicitacaocompra sc LEFT JOIN sfpc.tbfornecedorcredenciado f
				        ON f.aforcrsequ = sc.aforcrsequ
				WHERE
				    sc.csolcosequ = $Solicitacao
				ORDER BY
				    sc.aitescorde";

        $res = executarSQL($db, $sql);
        $cntMaterial = -1;
        $cntServico = -1;
        $tipoItem = null;
        $strBloqueioDotacao = null;

        #para cada item de solicitação
        while ($linha = $res->fetchRow()) {
            $codigoItem = $linha[11];
            if (!is_null($linha[12])) {
                #cpf
                $fornecedorItem = $linha[12];
            } else {
                #cnpj
                $fornecedorItem = $linha[13];
            }
            if (!is_null($linha[0])) {
                $cntMaterial++;
                $MaterialCheck[$cntMaterial] = false;
                $MaterialCod[$cntMaterial] = $linha[0];
                $MaterialQuantidade[$cntMaterial] = converte_valor_estoques($linha[3]);
                $MaterialValorEstimado[$cntMaterial] = converte_valor_estoques($linha[4]);
                $MaterialTotalExercicio[$cntMaterial] = converte_valor_estoques($linha[5]);
                $MaterialQuantidadeExercicio[$cntMaterial] = converte_valor_estoques($linha[6]);
                $MaterialMarca[$cntMaterial] = $linha[7];
                $MaterialModelo[$cntMaterial] = $linha[8];
                $MaterialFornecedor[$cntMaterial] = $fornecedorItem;
                $MaterialDescricaoDetalhada[$cntMaterial] = strtoupper2($linha[14]);
                //$MaterialBloqueio[$cntMaterial] = "";
                //$MaterialBloqueioItem[$cntMaterial] = array();
                $tipoItem = 'M';
            } else {
                $cntServico++;
                $ServicoCheck[$cntServico] = false;
                $ServicoCod[$cntServico] = $linha[1];
                $ServicoDescricaoDetalhada[$cntServico] = strtoupper2($linha[2]);
                $ServicoQuantidade[$cntServico] = converte_valor_estoques($linha[3]);
                $ServicoValorEstimado[$cntServico] = converte_valor_estoques($linha[4]);
                $ServicoTotalExercicio[$cntServico] = converte_valor_estoques($linha[5]);
                $ServicoQuantidadeExercicio[$cntServico] = converte_valor_estoques($linha[6]);
                $ServicoFornecedor[$cntServico] = $fornecedorItem;
                //$ServicoBloqueio[$cntServico] = "";
                //$ServicoBloqueioItem[$cntServico] = array();
                $tipoItem = 'S';
            }
        }
        #para cada bloqueio/dotacao
        $Bloqueios = array();
        if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == "S") { //neste caso o é uma dotação
			//$template->CAMPO_HIDDEN_CONTRATO = $GeraContrato;
			//$template->block("BLOCO_HIDDEN_CONTRATO");
            #pegar dotação
            $sql = " SELECT DISTINCT aitcdounidoexer, citcdounidoorga, citcdounidocodi, citcdotipa, aitcdoordt,
                                                citcdoele1, citcdoele2, citcdoele3, citcdoele4, citcdofont
                                     FROM SFPC.TBitemdotacaoorcament
                                    WHERE csolcosequ = $Solicitacao";

            $res2 = executarSQL($db, $sql);

            $cntBloqueios = -1;
            while ($linha = $res2->fetchRow()) {
                $cntBloqueios++;
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

                $dotacao = getDadosDotacaoOrcamentariaFromChave(
                        $dbOracle, $ano, $orgao, $unidade, $tipoAtividade, $atividade, $elemento1, $elemento2, $elemento3, $elemento4, $fonte
                );

                $strBloqueioDotacao = $dotacao['dotacao'];

                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == "") {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . "Bloqueio_" . $cntBloqueios . "').focus();\" class='titulo2'>Dotação foi excluída do sistema orçamentário em material ord " . $pos . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        } else {
            #pegar bloqueio

            $sql = " SELECT DISTINCT aitcblnbloq, aitcblanob
                                     FROM SFPC.TBitembloqueioorcament
                                    WHERE csolcosequ = $Solicitacao";
            $res2 = executarSQL($db, $sql);
            $cntBloqueios = -1;

            while ($linha = $res2->fetchRow()) {
                $cntBloqueios++;
                $pos = $cntBloqueios + 1;
                $bloqChaveAno = $linha[1];
                $bloqChaveSequ = $linha[0];
                $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqChaveAno, $bloqChaveSequ);
                $strBloqueioDotacao = $bloqueioArray['bloqueio'];
                if (is_null($strBloqueioDotacao) or $strBloqueioDotacao == "") {
                    adicionarMensagem("<a href=\"javascript:document.getElementById('" . $strIdNome . "Bloqueio_" . $cntBloqueios . "').focus();\" class='titulo2'>Bloqueio foi excluído do sistema orçamentário</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
                array_push($Bloqueios, $strBloqueioDotacao);
            }
        }

        #Recuperando documentos
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

# pegando limites de compra
# sintaxe para pegar o limite de compra: $limiteCompra[cód do tipo da compra][administração D ou I][é obras?]
$limiteCompra = NULL;
$JSCriacaoLimiteCompra = "";
if (
        $acaoPagina == ACAO_PAGINA_MANTER or //em manter apenas recuperar dados quando ainda não foi preenchido
        $acaoPagina == ACAO_PAGINA_INCLUIR
) {

    $sql = " select ctpcomcodi, flicomtipo, cmodlicodi, vlicomobra, vlicomserv
                         from sfpc.tblimitecompra
                 order by ctpcomcodi, flicomtipo, cmodlicodi, vlicomobra, vlicomserv
            ";
    $res = executarSQL($db, $sql);
    $oldctpcomcodi = null;
    $oldflicomtipo = null;
    while ($obj = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        if (is_null($obj->CMODLICODI) or $obj->CMODLICODI == "") {
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][true] = $obj->vlicomobra;
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][false] = $obj->vlicomserv;
            if ($oldctpcomcodi == null) {
                $JSCriacaoLimiteCompra .= "limiteCompra = new Array();";
            }
            if ($oldctpcomcodi != $obj->ctpcomcodi) {
                $JSCriacaoLimiteCompra .= "limiteCompra[" . $obj->ctpcomcodi . "] = new Array();";
                $oldctpcomcodi = $obj->ctpcomcodi;
                $oldflicomtipo = null;
            }
            if ($oldflicomtipo != $obj->flicomtipo) {
                $JSCriacaoLimiteCompra .= "limiteCompra[" . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "'] = new Array();";
                $oldflicomtipo = $obj->flicomtipo;
            }
            $JSCriacaoLimiteCompra .= "
                    limiteCompra[" . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['true'] = " . $obj->vlicomobra . ";
                    limiteCompra[" . $obj->ctpcomcodi . "]['" . $obj->flicomtipo . "']['false'] = " . $obj->vlicomserv . ";
                ";
        }
    }
}

# pegando situação atual da SCC (Este if está fora do if acima pois essa parte tem que ser carregada toda vez que recarrega a página,
# enquanto acima só carrega uma vez apenas)
if (
        $acaoPagina == ACAO_PAGINA_MANTER or //em manter apenas recuperar dados quando ainda não foi preenchido
        $acaoPagina == ACAO_PAGINA_ACOMPANHAR or
        $acaoPagina == ACAO_PAGINA_EXCLUIR
) {
    $sql = " SELECT csitsocodi
                   FROM SFPC.TBsolicitacaocompra
                  WHERE csolcosequ = $Solicitacao ";
    $situacaoSolicitacaoAtual = resultValorUnico(executarSQL($db, $sql));
}
$NCaracteresObservacao = strlen($Observacao);
$NCaracteresObjeto = strlen($Objeto);
$NCaracteresJustificativa = strlen($Justificativa);

#variáveis para ocultar campos e checagens associadas
$ocultarCampoRegistroPreco = false;
$ocultarCampoProcessoLicitatorio = false;
$ocultarCampoGeraContrato = false;
$ocultarCampoLegislacao = false;
$ocultarCampoTRP = false; // campo não aparecerá enquanto não for definido a tabela TRP
$ocultarCampoSARP = false;
$ocultarCampoDataDOM = false;
$ocultarCampoExercicio = false;
$ocultarCampoFornecedor = true;
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

if (is_null($cargaInicial))
    $cargaInicial = false;

if ($TipoCompra != TIPO_COMPRA_DISPENSA and $TipoCompra != TIPO_COMPRA_INEXIGIBILIDADE) {
    $ocultarCampoDataDOM = true;
    $ocultarCampoLegislacao = true;
} else {
    $isFornecedorUnico = true;
}

if ($TipoCompra == TIPO_COMPRA_LICITACAO) {
    $ocultarCampoFornecedor = true;

    $data = $db->getOne ("SELECT
				DISTINCT lic.flicporesu
			FROM
				sfpc.tblicitacaoportal AS lic LEFT JOIN sfpc.tbsolicitacaolicitacaoportal AS sol
					ON sol.cgrempcodi = lic.cgrempcodi
				AND sol.ccomlicodi = lic.ccomlicodi
				AND sol.corglicodi = lic.corglicodi
				AND sol.alicpoanop = lic.alicpoanop
			WHERE
				sol.csolcosequ = $Solicitacao AND lic.flicporesu IS NOT NULL");

	// Se licitação já tiver resultado da licitação cadastrado
	if ($data ==='S') {
		$ifVisualizacaoThenReadOnly = 'disabled';
		$ocultarCamposEdicaoLicitacao = true;
	}

	$licitacaoComResultado=false;
	if ($row->resultado == 'S') {
		$licitacaoComResultado=true;
	}
	echo $licitacaoComResultado;

} else {
    $ocultarCampoRegistroPreco = true;
}
if ($TipoCompra != TIPO_COMPRA_SARP) {
    $ocultarCampoSARP = true;
    $ocultarCampoProcessoLicitatorio = true;
} else {
    // Pra SARP, o TRP não é mostrado
    $ocultarCampoTRP = true;
}
if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == "S") {
    $ocultarCampoGeraContrato = true;
    $isDotacao = true;
}
if (
        ($TipoCompra == TIPO_COMPRA_LICITACAO and ( $RegistroPreco == "S" or is_null($RegistroPreco)))
) {
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

if ($TipoCompra == TIPO_COMPRA_DIRETA
        or $TipoCompra == TIPO_COMPRA_DISPENSA
        or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE
) {
    $isValidacaoFornecedorLicitacao = false;
}

/*
  if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
  } elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
  } elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
  }
 */

if (is_null($NCaracteresObservacao) or $NCaracteresObservacao == "") {
    $NCaracteresObservacao = "0";
}
if (is_null($NCaracteresObjeto) or $NCaracteresObjeto == "") {
    $NCaracteresObjeto = "0";
}
if (is_null($NCaracteresJustificativa) or $NCaracteresJustificativa == "") {
    $NCaracteresJustificativa = "0";
}
if ($isFornecedorUnico) {
    $ifVisualizacaoThenReadOnlyFornecedorItens = 'disabled';
}
if ($isDotacao) {
    $isBloqueioUnico = true;
}

$reserva = "Bloqueio";
if ($isDotacao)
    $reserva = "Dotação";

# Materiais do POST #
$QuantidadeMaterial = count($MaterialCod);

# Pegando os dados dos materiais enviados por POST
for ($itr = 0; $itr < $QuantidadeMaterial; $itr++) {
    $sql =
        "SELECT
            M.EMATEPDESC ,
            U.EUNIDMSIGL ,
            I.EITESCDESCMAT
        FROM
            SFPC.TBMATERIALPORTAL M
            LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA U
                ON U.CUNIDMCODI = M.CUNIDMCODI
            LEFT OUTER JOIN SFPC.TBITEMSOLICITACAOCOMPRA I
                ON M.CMATEPSEQU = I.CMATEPSEQU
        WHERE
            M.CMATEPSEQU = $MaterialCod[$itr]";

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
    }
    $Linha = $res->fetchRow();
    $MaterialDescricao = $Linha[0];
    $MaterialUnidade = $Linha[1];
    $MaterialDescDet = strtoupper2($Linha[2]);
    $pos = count($materiais);

    #preenchendo valores padrões caso seja nulo, para não ocorrer erro.
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
    /**
     *
     */
    $materiais[$pos]["descricaoDetalhada"] = strtoupper($MaterialDescricaoDetalhada[$itr]);

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
    $materiais[$pos]["isObras"] = isObras($db, $materiais[$pos]["codigo"], TIPO_ITEM_MATERIAL);
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

    // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
    if ($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
    	$materiais[$pos]["fornecedor"] = $CnpjFornecedor;
    }
    // [/CUSTOMIZAÇÃO]

    #reservas (bloqueios ou dotações)
    /*
      $materiais[$pos]["reservas"] = array();
      $posReserva =-1;
      if (!is_null($MaterialBloqueioItem[$pos])) {
      foreach ($MaterialBloqueioItem[$pos] as $bloqueio) {
      $posReserva ++;
      $materiais[$pos]["reservas"][$posReserva] = $bloqueio;
      }
      } */
}

# Pegando os dados dos servicos enviados por POST
$QuantidadeServico = count($ServicoCod);

for ($itr = 0; $itr < $QuantidadeServico; $itr++) {

    $sql = " select m.eservpdesc
                 from SFPC.TBservicoportal m
                where m.cservpsequ = " . $ServicoCod[$itr] . "
    ";

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
    }
    $Linha = $res->fetchRow();
    $Descricao = $Linha[0];

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
    $servicos[$pos]["descricaoDetalhada"] = strtoupper($ServicoDescricaoDetalhada[$itr]);
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
    $servicos[$pos]["isObras"] = isObras($db, $servicos[$pos]["codigo"], TIPO_ITEM_SERVICO);

    if (moeda2float($servicos[$pos]["quantidade"]) == 1) {
        $servicos[$pos]["totalExercicio"] = $ServicoTotalExercicio[$itr];
    } else {
        $servicos[$pos]["totalExercicio"] = converte_valor_estoques(moeda2float($servicos[$pos]["quantidadeExercicio"]) * moeda2float($servicos[$pos]["valorEstimado"]));
    }
    /*
      $servicos[$pos]["reservas"] = array();
      $posReserva =-1;
      if (!is_null($ServicoBloqueioItem[$pos])) {
      foreach ($ServicoBloqueioItem[$pos] as $bloqueio) {
      $posReserva ++;
      $servicos[$pos]["reservas"][$posReserva] = $bloqueio;
      }
      }
     */
}

# Pegando os materiais e serviços sendo incluídos via SESSION (janela de seleção de material/serviço) #
if (count($_SESSION['item']) != 0) {
    sort($_SESSION['item']);
    for ($i = 0; $i < count($_SESSION['item']); $i++) {
        $DadosSessao = explode($SimboloConcatenacaoArray, $_SESSION['item'][$i]);

        $ItemCodigo = $DadosSessao[1];
        $ItemTipo = $DadosSessao[3];

        if ($ItemTipo == "M") {

            #verificando se item já existe
            /* $itemJaExiste = false;

              $qtdeMateriais = count($materiais);

              //$dataMinimaValidaTrp = prazoValidadeTrp($db,$TipoCompra)->format('Y-m-d');

              for ($i2=0; $i2<$qtdeMateriais; $i2++) {
              if ($ItemCodigo == $materiais[$i2]["codigo"]) {
              $itemJaExiste = true;
              }
              } */

            #incluindo item
            //if (!$itemJaExiste) {

            $sql = " select m.ematepdesc, u.eunidmsigl
                             from SFPC.TBmaterialportal m, SFPC.TBunidadedemedida u
                            where m.cmatepsequ = " . $ItemCodigo . "
                              and u.cunidmcodi = m.cunidmcodi
                ";

            $res = $db->query($sql);
            if (PEAR::isError($res)) {
                EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
            }
            $Linha = $res->fetchRow();
            $MaterialDescricao = $Linha[0];
            $MaterialUnidade = $Linha[1];

            $pos = count($materiais);
            $materiais[$pos] = array();
            $materiais[$pos]["tipo"] = TIPO_ITEM_MATERIAL;
            $materiais[$pos]["codigo"] = $ItemCodigo;
            $materiais[$pos]["descricao"] = $MaterialDescricao;
            /** adiciona descricao detalhada */
            $materiais[$pos]["descricaoDetalhada"];
            $materiais[$pos]["unidade"] = $MaterialUnidade;

            $materiais[$pos]["check"] = false;
            $materiais[$pos]["quantidade"] = "0,0000";
            $materiais[$pos]["valorEstimado"] = "0,0000";

            //valores em float para uso em funções
            $materiais[$pos]["quantidadeItem"] = 0;
            $materiais[$pos]["valorItem"] = 0;
            $materiais[$pos]["quantidadeExercicio"] = "0,0000";
            $materiais[$pos]["totalExercicio"] = '0,0000';
            $materiais[$pos]["marca"] = "";
            $materiais[$pos]["modelo"] = "";
            $materiais[$pos]["fornecedor"] = "";
            $materiais[$pos]["posicao"] = $pos;
            $materiais[$pos]["posicaoItem"] = $pos + 1; // posição mostrada na tela
            //$materiais[$pos]["reservas"]           = array();
            $materiais[$pos]["trp"] = calcularValorTrp($db, $TipoCompra, $materiais[$pos]["codigo"]);
            $materiais[$pos]["isObras"] = isObras($db, $materiais[$pos]["codigo"], TIPO_ITEM_MATERIAL);

            if (!is_null($materiais[$pos]["trp"]))
                $materiais[$pos]["trp"] = converte_valor_estoques($materiais[$pos]["trp"]);
            //}

            // [CUSTOMIZAÇÃO] - Fornecedor único para compras do tipo dispensa e inexigibilidade
            if ($TipoCompra == TIPO_COMPRA_DISPENSA || $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
            	$materiais[$pos]["fornecedor"] = $CnpjFornecedor;
            }
            // [/CUSTOMIZAÇÃO]
        } elseif ($ItemTipo == "S") {

            #verificando se item já existe
            /* $itemJaExiste = false;
              $qtdeServicos = count($servicos);

              for ($i2=0; $i2<$qtdeServicos; $i2++) {
              if ($ItemCodigo == $servicos[$i2]["codigo"]) {
              $itemJaExiste = true;
              }
              }

              #incluindo item
              if (!$itemJaExiste) { */

            $sql = " select m.eservpdesc
                             from SFPC.TBservicoportal m
                            where m.cservpsequ = " . $ItemCodigo . "
                ";

            $res = $db->query($sql);
            if (PEAR::isError($res)) {
                EmailErroSQL("Erro em SQL", __FILE__, __LINE__, "Erro em SQL", $sql, $res);
            }
            $Linha = $res->fetchRow();
            $Descricao = $Linha[0];
            $pos = count($servicos);
            $servicos[$pos] = array();
            $servicos[$pos]["tipo"] = TIPO_ITEM_SERVICO;
            $servicos[$pos]["codigo"] = $ItemCodigo;
            $servicos[$pos]["descricao"] = $Descricao;
            $servicos[$pos]["check"] = false;
            $servicos[$pos]["quantidade"] = "0,0000";
            $servicos[$pos]["valorEstimado"] = "0,0000";

            //valores em float para uso em funções
            $servicos[$pos]["quantidadeItem"] = 0;
            $servicos[$pos]["valorItem"] = 0;
            $servicos[$pos]["quantidadeExercicio"] = "0";
            $servicos[$pos]["totalExercicio"] = '0,0000';
            $servicos[$pos]["fornecedor"] = "";
            $servicos[$pos]["posicao"] = $pos;
            $servicos[$pos]["posicaoItem"] = $pos + 1; // posição mostrada na tela
            $servicos[$pos]["isObras"] = isObras($db, $servicos[$pos]["codigo"], TIPO_ITEM_SERVICO);
            //$servicos[$pos]["reservas"]               = array();
            //}
        } else {
            EmailErro("Erro", __FILE__, __LINE__, "ItemTipo não é nem material nem serviço! /n var SimboloConcatenacaoArray = " . $SimboloConcatenacaoArray . "");
        }
    }
    unset($_SESSION['item']);
}

$qtdeMateriais = 0;
if (!is_null($materiais)) {
    $qtdeMateriais = count($materiais);
}
$qtdeServicos = 0;
if (!is_null($servicos)) {
    $qtdeServicos = count($servicos);
}

#Verificando se o valor de 'GeraContrato' é automático (no campo SFPC.TBgruposubelementodespesa.fgrusecont)
if (!$ocultarCampoGeraContrato and ( $qtdeMateriais + $qtdeServicos) > 0) {
    $gruposMateriaisServicos = "";
    $colocarVirgula = false;
    if (!is_null($materiais)) {
        foreach ($materiais as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item["codigo"], TIPO_ITEM_MATERIAL);
            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }
    if (!is_null($servicos)) {
        foreach ($servicos as $item) {
            $grupo = getGrupoDeMaterialServico($db, $item["codigo"], TIPO_ITEM_SERVICO);
            if ($colocarVirgula) {
                $gruposMateriaisServicos .= ', ';
            }
            $gruposMateriaisServicos .= $grupo;
            $colocarVirgula = true;
        }
    }
    $sql = " select count(fgrusecont)
               from SFPC.TBgruposubelementodespesa
              where cgrumscodi in (" . $gruposMateriaisServicos . ")
                and fgrusecont = 'S' ";
    $quantidadeSubElementoComGeraContrato = resultValorUnico(executarSQL($db, $sql));
    if ($quantidadeSubElementoComGeraContrato > 0) {
        # Preenchendo contrato
        $GeraContrato = 'S';
        $preencherCampoGeraContrato = true;
        $ocultarCampoGeraContrato = false;
    }
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Imprimir") {
    $Url = "RelAcompanhamentoSCCPdf.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit;
}

# Ano da Requisição Ano Atual #
$AnoSolicitacao = date("Y");
$DataAtual = date("Y-m-d");

/* if ( ($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')) {
  $Mens              = 1;
  $Tipo              = 2;
  $Mensagem .= "O Usuário do grupo INTERNET ou corporativo não pode fazer Solicitação de Material";
  } */

$anoAtual = date('Y');

#verificar se SCC está em uma situação que não pode ser alterada
if ($Botao == "Manter" or $Botao == "Excluir") {

    /*
    $sql = "
        select corglicodi, CSITSOCODI, csolcotipcosequ
          from sfpc.tbsolicitacaocompra
         where csolcosequ = $Solicitacao ";
    */

    //
    $sql  = "
            SELECT
            	SOL.CORGLICODI, SOL.CSITSOCODI, SOL.CSOLCOTIPCOSEQU, SOL.CTPCOMCODI, CS.CDOCPCSEQU
            FROM
            	SFPC.TBSOLICITACAOCOMPRA SOL
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
    $arrayVerificacaoSarp = array(TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO, TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO);
    $arrayVerificacaoLicitacao = $arrayVerificacaoSarp;

    $arrayTipoCompra = array(TIPO_COMPRA_DIRETA, TIPO_COMPRA_DISPENSA, TIPO_COMPRA_INEXIGIBILIDADE);

    if ( ($SituacaoCompra == TIPO_SITUACAO_SCC_EM_ANALISE) or ( $SituacaoCompra == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) ) {
        $alterarSCC = true;
    } else if ($tipoCompra == TIPO_COMPRA_SARP && in_array($SituacaoCompra, $arrayVerificacaoSarp)) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (SARP - PSE Gerada). SCC='" . $Solicitacao . "'");
    } else if ($tipoCompra == TIPO_COMPRA_LICITACAO && in_array($SituacaoCompra, $arrayVerificacaoLicitacao)) { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Licitação - PSE Gerada). SCC='" . $Solicitacao . "'");
    } else if (in_array($tipoCompra, $arrayTipoCompra) && $SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO && $idContratoSolicitacao != "") { // Nova regra
        assercao(false, "Esta SCC não pode ser alterada/cancelada. (Contrato associado a solicitação). SCC='" . $Solicitacao . "'");
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
        if (!hasPSEImportadaSofin($db, $Solicitacao)) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois o SOFIN já efetuou a importação dos dados da PSE. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO) {
        if (!hasSSCContrato($db, $Solicitacao)) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois já está relacionada com Contrato. SCC='" . $Solicitacao . "'");
        }
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP) {
        $alterarSCC = true;
    } elseif ($SituacaoCompra == TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO) {
        if (administracaoOrgao($db, $Orgao) == "I") {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois não está mais na fase de análise. SCC='" . $Solicitacao . "'");
        }
    } else {
        assercao(false, "Esta SCC não pode ser alterada/cancelada pois está em uma situação que não pode ser alterada. SCC='" . $Solicitacao . "'");
    }
}

if ($Botao == "Excluir" and $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $sql = "
            SELECT csitsocodi
              FROM sfpc.tbsolicitacaocompra
             WHERE csolcosequ = $Solicitacao ";
    $situacao = resultValorUnico(executarSQL($db, $sql));
    $sql = "
            UPDATE sfpc.tbsolicitacaocompra
               SET
                   cusupocod1 = " . $_SESSION['_cusupocodi_'] . ",
                   tsolcoulat = now(),
                   csitsocodi = " . $TIPO_SITUACAO_SCC_CANCELADA . "
             WHERE
                   csolcosequ = $Solicitacao ";
    executarTransacao($db, $sql);
    $sql = "
            INSERT INTO sfpc.tbhistsituacaosolicitacao(
        csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
            VALUES (
    $Solicitacao, now(), " . $TIPO_SITUACAO_SCC_CANCELADA . ", NULL, " . $_SESSION['_cusupocodi_'] . ", now()
        );
    ";
    executarTransacao($db, $sql);
    finalizarTransacao($db);
    $Mensagem = "Solicitação cancelada com Sucesso";
    header("location: " . $programaSelecao . "?Mens=1&Tipo=1&Mensagem=" . $Mensagem);
    exit;
} elseif ($Botao == "Incluir" or $Botao == "Manter" or $Botao == "Rascunho" or $Botao == 'ManterRascunho') {
    $Mens = 0;
    $Mensagem = "";

    // Quando for rascunho ou manter rascunho, nao verifica se descriçao detalhada foi preenchida
    if ( ($Botao != "Rascunho") &&  ($Botao != 'ManterRascunho') ) {

        // [CUSTOMIZAÇÃO] - Validação para a descrição detalhada dos itens genéricos
        if (empty($materiais) === false) {
	    foreach ($materiais as $mat) {
	    	if (hasIndicadorCADUM($db, (int) $mat['codigo']) && $mat['descricaoDetalhada'] == "") {
	    		$idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
	    		$posicaoTela = $mat['posicao'] + 1;
	    		adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela</a>",
	    						  $GLOBALS["TIPO_MENSAGEM_ERRO"]);

	    		$idHtmlDescricao = null;
	    		$posicaoTela = null;
	    	}
	    }
        }

    }

    // [/CUSTOMIZAÇÃO]

    if ($CentroCusto == "") {
        adicionarMensagem("<a href='javascript:document.getElementById(\"CentroCustoLink\").focus();' class='titulo2'>Centro de Custo</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    }
    if ($TipoCompra == "") {
        adicionarMensagem("<a href='javascript:formulario.TipoCompra.focus();' class='titulo2'> Tipo de Compra </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    }

    if ($Botao != "Rascunho" and $Botao != 'ManterRascunho') {

        // fornecedor
        if (!$ocultarCampoFornecedor and ! is_null($CnpjFornecedor) and $CnpjFornecedor != "") {
            $retorno = validaFormatoCNPJ_CPF($CnpjFornecedor);
            if (!$retorno[0]) {
                $msgAux = $retorno[1];
                adicionarMensagem("<a href='javascript:formulario.CnpjFornecedor.focus();' class='titulo2'>erro em campo de fornecedor com a mensagem '$msgAux'</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        if ($Objeto == "") {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        } elseif ($NCaracteresObjeto > $tamanhoObjeto) {
            adicionarMensagem("<a href='javascript:formulario.Objeto.focus();' class='titulo2'>Objeto menor que " . $tamanhoObjeto . " caracteres</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        if ($NCaracteresObservacao > "200") {
            adicionarMensagem("<a href='javascript:formulario.Observacao.focus();' class='titulo2'>Observação menor que 200 caracteres</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        if (!$ocultarCampoJustificativa) {
            /* if ($Justificativa == "") {
              adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>Justificativa</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
              }else */
            if ($NCaracteresJustificativa > $tamanhoJustificativa) {
                adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>Justificativa menor que " . $tamanhoJustificativa . " caracteres</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        if (!$ocultarCampoGeraContrato and is_null($GeraContrato)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"GeraContrato\"][0].focus();' class='titulo2'> Gera Contrato </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        if (!$ocultarCampoRegistroPreco and is_null($RegistroPreco)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"RegistroPreco\"][0].focus();' class='titulo2'> Registro de Preço </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        if (!$ocultarCampoSARP and is_null($Sarp)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"Sarp\"][0].focus();' class='titulo2'> Tipo de Sarp </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }
        if (!$ocultarCampoLegislacao) {
            if (is_null($TipoLei) or $TipoLei == '') {
                adicionarMensagem("<a href='javascript:formulario.TipoLei.focus();' class='titulo2'> Tipo de Lei </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            if (is_null($Lei) or $Lei == '') {
                adicionarMensagem("<a href='javascript:formulario.Lei.focus();' class='titulo2'> Lei </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            if (is_null($Artigo) or $Artigo == '') {
                adicionarMensagem("<a href='javascript:formulario.Artigo.focus();' class='titulo2'> Artigo </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
            if (is_null($Inciso) or $Inciso == '') {
                adicionarMensagem("<a href='javascript:formulario.Inciso.focus();' class='titulo2'> Inciso/ Parágrafo </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        if (!$ocultarCampoDataDOM) {
            if (is_null($DataDom) or $DataDom == "") {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } elseif (ValidaData($DataDom)) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> '" . ValidaData($DataDom) . "' em Data de publicação do DOM</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } elseif (DataInvertida($DataDom) > DataAtual()) {
                adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'> Data de publicação do DOM menor ou igual à data atual </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            } else {
                $dataHoje = new DateTime();
                $dataDOM = new DateTime(DataInvertida($DataDom));
                $data_vigencia = new DateTime();
                $data_vigencia->setDate($dataDOM->format("Y"), $dataDOM->format("m"), $dataDOM->format("d") + prazoDOM($db));
                //echo $data_vigencia->format("Y-m-d");
                //echo " ! >= ";
                //echo $dataHoje->format("Y-m-d");
                //echo " --- ";
                //echo (!($data_vigencia->format("Y-m-d") >= $dataHoje->format("Y-m-d")));
                if (!($data_vigencia->format("Y-m-d") >= $dataHoje->format("Y-m-d"))) {
                    adicionarMensagem("<a href='javascript:formulario.DataDom.focus();' class='titulo2'>A Dispensa/Inexigibilidade extrapola a data limite a partir da publicação no DOM</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
                //exit;
            }
        }

        if (!$ocultarCampoProcessoLicitatorio and ( is_null($NumProcessoSARP) or $NumProcessoSARP == "")) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        # Se não escolheu nenhum item #
        if (count($MaterialCod) == 0 and count($ServicoCod) == 0) {
            adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Pelo menos um item de material ou serviço</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        }

        $fornecedorCompra = null; // verificando se há mais de 1 fornecedor (tanto para materiais quanto servicos)
        $fornecedorCompraSetado = false;
        $elementoDespesaItem = null;
        $posElementoDespesa = -1;

        $hasGrupoEspecial = false;

        # verificar se há só itens de materiais ou só itens de serviços. nao deve permitir os 2
        # exceto quando forem usados grupos com subelementos especiais, definidos nos parâmetros gerais
        if (!is_null($materiais) and ! is_null($servicos) and count($materiais) > 0 and count($servicos) > 0) {
            foreach ($materiais as $material) {
                $grupo = getGrupoDeMaterialServico($db, $material['codigo'], TIPO_ITEM_MATERIAL);
                $elementoDespesaArray = getSubElementoDespesaDeGrupoMaterial($db, $anoAtual, $grupo);
                foreach ($subElementosEspeciais as $subElementoEspecial) {
                    $subElementoEspecial = trim($subElementoEspecial);
                    if ($subElementoEspecial == $elementoDespesaArray['elementoDespesa']) {
                        $hasGrupoEspecial = true;
                    }
                }
            }
            foreach ($servicos as $servico) {
                $grupo = getGrupoDeMaterialServico($db, $servico['codigo'], TIPO_ITEM_SERVICO);
                $elementoDespesaArray = getSubElementoDespesaDeGrupoMaterial($db, $anoAtual, $grupo);
                foreach ($subElementosEspeciais as $subElementoEspecial) {
                    $subElementoEspecial = trim($subElementoEspecial);
                    if ($subElementoEspecial == $elementoDespesaArray['elementoDespesa']) {
                        $hasGrupoEspecial = true;
                    }
                }
            }
            if (!$hasGrupoEspecial) {
                mostrarMensagemErroUnica("Solicitação de compra deve conter apenas itens de materiais ou itens de serviços exceto quando conter grupos com subelementos especiais");
            }
        }

        if (!is_null($materiais)) {
            foreach ($materiais as $material) {
                if (!$GLOBALS['BloquearMensagens']) {
                    $pos = $material['posicao'];
                    $ord = $pos + 1;
                    if ($material['quantidade'] == "" or moeda2float($material['quantidade']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                    if ($material['valorEstimado'] == "" or moeda2float($material['valorEstimado']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                    if (!$ocultarCampoFornecedor) {

                        if ($material['marca'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialMarca[" . $pos . "]').focus();\" class='titulo2'> Marca do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }
                        if ($material['modelo'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialModelo[" . $pos . "]').focus();\" class='titulo2'> Modelo do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }

                        if ($material['fornecedor'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } else {
                            # pegando 1o fornecedor dos itens
                            if (!$fornecedorCompraSetado) {
                                $fornecedorCompra = $material['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            $retorno = validaFormatoCNPJ_CPF($materiais[$pos]['fornecedor']);
                            if (!$retorno[0]) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> falha na validação do fornecedor do material ord " . ($ord) . " com a seguinte mensagem:" . $retorno[1] . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                            } else {

                                $materiais[$pos]['fornecedor'] = $retorno[1];
                                if ($isFornecedorUnico and $material['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                                }
                                # checar sicref e debito mercantil
                                try {
                                    validaFornecedorItemSCC($db, $material['fornecedor'], $TipoCompra, $material['codigo'], TIPO_ITEM_MATERIAL);
                                } catch (ExcecaoPendenciasUsuario $e) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                                }
                            }
                        }
                    }

                    $valorTotalItem = moeda2float($material["quantidade"]) * moeda2float($material["valorEstimado"]);

                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        $varAux = trim($material['quantidadeExercicio']);
                        if ($material['quantidadeExercicio'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> Quantidade de exercício do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } else
                        if ($material['totalExercicio'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> Valor de exercício do material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }

                        $valorTotalExercicioItem = moeda2float($material["totalExercicio"]);

                        //if (moeda2float($material["quantidade"])<moeda2float($material["quantidadeExercicio"])) {
                        if (comparaFloat(moeda2float($material["quantidade"]), "<", moeda2float($material["quantidadeExercicio"]), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } elseif (comparaFloat($valorTotalItem, "<", $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no material ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                            //exit;
                        }
                    }
                }
            }
        }

        if (!is_null($servicos)) {
            $posElementoDespesa = -1;

            foreach ($servicos as $servico) {

                if (!$GLOBALS['BloquearMensagens']) {

                    $pos = $servico['posicao'];
                    $ord = $pos + 1;
                    if ($servico['quantidade'] == "" or moeda2float($servico['quantidade']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                    //if ($servico['descricaoDetalhada']=='' or is_null($servico['descricaoDetalhada'])) {
                    //  adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[".$pos."]').focus();\" class='titulo2'> Descrição detalhada do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    //}

                    if (strlen($servico['descricaoDetalhada']) > $tamanhoDescricaoServico) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> 'Descrição detalhada deve ser menor que " . $tamanhoDescricaoServico . " caracteres' no serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }
                    //comparaFloat($servico['valorEstimado'], "==", 0, 4)

                    if ($servico['valorEstimado'] == "" or moeda2float($servico['valorEstimado']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do servico ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                    }

                    if (!$ocultarCampoFornecedor) {
                        if ($servico['fornecedor'] == "") {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } else {
                            # pegando 1o fornecedor dos itens
                            if (!$fornecedorCompraSetado) {
                                $fornecedorCompra = $servico['fornecedor'];
                                $fornecedorCompraSetado = true;
                            }
                            $retorno = validaFormatoCNPJ_CPF($servicos[$pos]['fornecedor']);
                            if (!$retorno[0]) {
                                adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>falha na validação do fornecedor do serviço ord " . ($ord) . " com a seguinte mensagem:" . $retorno[1] . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                            } else {
                                $servicos[$pos]['fornecedor'] = $retorno[1];
                                if ($isFornecedorUnico and $servico['fornecedor'] != $fornecedorCompra) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'> 'Para os tipos Dispensa e Inexigibilidade, só deve haver 1 fornecedor' em fornecedor do serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                                }
                                # checar sicref e debito mercantil
                                try {
                                    validaFornecedorItemSCC($db, $servico['fornecedor'], $TipoCompra, $servico['codigo'], TIPO_ITEM_SERVICO);
                                } catch (ExcecaoPendenciasUsuario $e) {
                                    adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoFornecedor[" . $pos . "]').focus();\" class='titulo2'>'" . $e->getMessage() . "' em fornecedor do serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                                }
                            }
                        }
                    }
                    $valorTotalItem = moeda2float($servico[$pos]["quantidade"]) * moeda2float($servico[$pos]["valorEstimado"]);
                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        //if ($servico['quantidadeExercicio']=="" or moeda2float($servico['quantidadeExercicio'])==0) {
                        //  adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[".$pos."]').focus();\" class='titulo2'> Quantidade de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        //} elseif ($servico['totalExercicio']=="" or moeda2float($servico['totalExercicio'])==0) {
                        //  adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[".$pos."]').focus();\" class='titulo2'> Valor de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        //}
                        $valorTotalExercicioItem = moeda2float($servico[$pos]["totalExercicio"]);
                        if (comparaFloat(moeda2float($material["quantidade"]), "<", moeda2float($material["quantidadeExercicio"]), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } elseif (comparaFloat($valorTotalItem, "<", $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no serviço ord " . ($ord) . "</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
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

    /* if (count($itensSCC)==0 or is_null($itensSCC)) {
      adicionarMensagem("'Valor total de exercício maior que valor total do item' no serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
      } */
    if ((is_null($itensSCC) or count($itensSCC) == 0) and ! is_null($Bloqueios) and count($Bloqueios) > 0) {
        adicionarMensagem("Não é possível adicionar Bloqueios ou Dotações em SCCs que não tenham itens", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    } else {
        if ($Botao == "Incluir" or $Botao == "Manter") {
            // TRECHO SENDO MANTIDO POR HERALDO BOTELHO
            $campoDotacaoNulo = campoDotacaoNulo();
            if (!$campoDotacaoNulo or $TipoCompra != 2 or $RegistroPreco != "S") {
                validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $Bloqueios, $itensSCC, 'BloqueioTodos', $TipoCompra, $RegistroPreco);
            }
        }
    }

	// [CUSTOMIZAÇÃO] - Conforme comentário na linha 1508 "rascunho também deve gravar licitação de SARP"
	//                  então é realizada validação de preenchimento de dados para o tipo SARP
    if ($Botao == "Rascunho" or $Botao == "ManterRascunho") {
    	if (!$ocultarCampoProcessoLicitatorio and ( is_null($NumProcessoSARP) or $NumProcessoSARP == "")) {
    		adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
    	}
    }

    if ($GLOBALS['Mens'] != 1) {
        if (
                (($Botao == "Incluir" or $Botao == "Rascunho") and $acaoPagina == ACAO_PAGINA_INCLUIR) or ( ($Botao == "Manter" or $Botao == "ManterRascunho") and $acaoPagina == ACAO_PAGINA_MANTER)
        ) {
            $ano = date('Y');
            #Pegando dados de órgão e unidade pelo centro de custo
            $sql = "
                    select corglicodi, ccenpocorg, ccenpounid
                      from sfpc.tbcentrocustoportal
                     where ccenposequ = $CentroCusto ";
            $Linha = resultLinhaUnica(executarTransacao($db, $sql));
            $Orgao = $Linha[0];
            $OrgaoSofin = $Linha[1];
            $UnidadeSofin = $Linha[2];
            if ($Botao == "Manter" or $Botao == "ManterRascunho") {
                #Pegando ano, órgão e tipo para ver se sequencial da SCC deve mudar, e vendo se a situação da SCC
                $sql = "
                        select corglicodi, asolcoanos, ctpcomcodi, csolcotipcosequ, csolcocodi, CSITSOCODI
                          from sfpc.tbsolicitacaocompra
                         where csolcosequ = $Solicitacao ";
                $linha = resultLinhaUnica(executarTransacao($db, $sql));
                $OrgaoAntes = $linha[0];
                $AnoAntes = $linha[1];
                $TipoCompraAntes = $linha[2];
                $sequencialPorAnoOrgaoTipoAntes = $linha[3];
                $sequencialPorAnoOrgaoAntes = $linha[4];
                $SituacaoCompraAntes = $linha[5];
                $ano = $AnoAntes; // em manter o ano nao deve mudar
                // aceitar Cadastramento apenas para rascunho!
                assercao(
                        ($SituacaoCompraAntes != TIPO_SITUACAO_SCC_EM_CADASTRAMENTO and $Botao != "Rascunho" and $Botao != "ManterRascunho") or
                        $SituacaoCompraAntes == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO, "ERRO: Tentando alterar SCC já incluída para 'EM CADASTRAMENTO'. Abortando."
                );
            }
            #Pegando sequencial da SCC pelo ano, orgao e tipo
            if (
                    ($Botao == "Manter" or $Botao == "ManterRascunho") and
                    $OrgaoAntes == $Orgao and
                    $AnoAntes == $ano and
                    $TipoCompraAntes == $TipoCompra
            ) {
                $sequencialPorAnoOrgaoTipo = $sequencialPorAnoOrgaoTipoAntes; //nao mudar o sequencial caso o ano e orgao e tipo nao mudaram
            } else {
                #para inclusão ou mudança de orgao ano ou tipo, mudar sequencial
                $sql = "
                        select max(csolcotipcosequ)
                          from sfpc.tbsolicitacaocompra
                         where corglicodi = " . $Orgao . "
                           and asolcoanos = " . date('Y') . "
                           and ctpcomcodi = " . $TipoCompra . " ";
                $sequencialPorAnoOrgaoTipo = resultValorUnico(executarTransacao($db, $sql));
                if (is_null($sequencialPorAnoOrgaoTipo) or $sequencialPorAnoOrgaoTipo == "") {
                    $sequencialPorAnoOrgaoTipo = 1;
                } else {
                    $sequencialPorAnoOrgaoTipo++;
                }
            }

            #Pegando sequencial da SCC pelo ano e orgao
            if (
                    ($Botao == "Manter" or $Botao == "ManterRascunho") and
                    $OrgaoAntes == $Orgao and
                    $AnoAntes == $ano
            ) {
                $sequencialPorAnoOrgao = $sequencialPorAnoOrgaoAntes; //nao mudar o sequencial caso o ano e orgao nao mudaram
            } else {
                #para inclusão ou mudança de orgao ou ano, mudar sequencial
                $sql = "
                        select max(csolcocodi)
                          from sfpc.tbsolicitacaocompra
                         where corglicodi = " . $Orgao . " and asolcoanos = " . date('Y') . "
                    ";
                $sequencialPorAnoOrgao = resultValorUnico(executarTransacao($db, $sql));
                if (is_null($sequencialPorAnoOrgao) or $sequencialPorAnoOrgao == "") {
                    $sequencialPorAnoOrgao = 1;
                } else {
                    $sequencialPorAnoOrgao++;
                }
            }

            $strCodigoSolicitacao = "";

            #tratando dados para SQL
            if ($ocultarCampoSARP /* or $Botao == "Rascunho" or $Botao == "ManterRascunho" */) { // rascunho também deve gravar licitação de SARP
                $strNumProcessoSARP = 'null';
                $strGrupoEmpresaCodigoSARP = 'null';
                $strAnoProcessoSARP = 'null';
                $strComissaoCodigoSARP = 'null';
                $strOrgaoLicitanteCodigoSARP = 'null';
            } else {
                $strNumProcessoSARP = $NumProcessoSARP;
                $strGrupoEmpresaCodigoSARP = $GrupoEmpresaCodigoSARP;
                $strAnoProcessoSARP = $AnoProcessoSARP;
                $strComissaoCodigoSARP = $ComissaoCodigoSARP;
                $strOrgaoLicitanteCodigoSARP = $OrgaoLicitanteCodigoSARP;
            }
            if ($ocultarCampoLegislacao or is_null($Inciso) or $Inciso == "") {
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
            if ($ocultarCampoDataDOM or is_null($DataDom) or $DataDom == "") {
                $strDataDom = 'null';
            } else {
                $strDataDom = "'" . DataInvertida($DataDom) . "'";
            }
            if ($ocultarCampoJustificativa or $Justificativa == "" or is_null($Justificativa)) {
                $strJustificativa = 'null';
            } else {
                $strJustificativa = "'" . $Justificativa . "'";
            }

            #Verificando a situação da solicitação
            $situacaoSolicitacao = -1;
            $fluxoVerificarGerarContrato = false;

            #Encontrando situação da solicitação
            if ($Botao == "Rascunho" or $Botao == "ManterRascunho") {
                $situacaoSolicitacao = $TIPO_SITUACAO_SCC_EM_CADASTRAMENTO;
            } elseif ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                $fluxoVerificarGerarContrato = true;
            } elseif ($TipoCompra == TIPO_COMPRA_LICITACAO) {

                #verificando se é adm direta.
                $sql = " select forglitipo
                                 from sfpc.tborgaolicitante
                                where corglicodi = " . $Orgao . "
                    ";
                $administracao = resultValorUnico(executarTransacao($db, $sql));
                if ($administracao == 'D') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_EM_ANALISE;
                } elseif ($administracao == 'I') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO;
                } else {
                    assercao(false, "Tipo de adiministração de órgão não reconhecido", $db);
                }
            } elseif ($TipoCompra == TIPO_COMPRA_SARP) {
                if (!isset($Solicitacao)) {
                    # é inclusão. SCC não é aprovada
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                } elseif ($Botao != "Incluir" and isAutorizadoSarp($db, $Solicitacao)) {
                    $fluxoVerificarGerarContrato = true;
                } else {
                    # é alteração mas não foi autorizado
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                }
            } else {
                assercao(false, "Tipo de compra não reconhecida", $db);
            }
            # direta, dispensa, inexibilidade ou (SARP autorizado tipo participante que não gera contrato)
            if ($fluxoVerificarGerarContrato) {

                if ($GeraContrato == 'S') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO;
                } else {
                    # Gera contrato = N
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO;
                }
            }

            assercao(($situacaoSolicitacao != -1), "Caso da situação de solicitação de compra não está sendo tratado.", $db);
            assercao(!is_null($situacaoSolicitacao), "Erro em variável de situação de solicitação de compra. Variável nula. Motivo provável é se foi usado uma constante nula.", $db);

            /* echo "[".$strNumProcessoSARP."]";
              echo "[".$strAnoProcessoSARP."]";
              echo "[".$strGrupoEmpresaCodigoSARP."]";
              echo "[".$strComissaoCodigoSARP."]";
              echo "[".$strOrgaoLicitanteCodigoSARP."]";
              assercao(false,"FIM",$db); */

            #iniciando inclusão ou alteração
            if ($Botao == "Manter" or $Botao == "ManterRascunho") {
                $sql = "
                        UPDATE sfpc.tbsolicitacaocompra SET
                            corglicodi = " . $Orgao . ",
                            --asolcoanos = " . $anoAtual . ", -- ano nao deve mudar
                            csolcocodi = " . $sequencialPorAnoOrgao . ",
                            ctpcomcodi = " . $TipoCompra . ",
                            csolcotipcosequ = " . $sequencialPorAnoOrgaoTipo . ",
                            --tsolcodata = now(), -- não mudar data de solicitação
                            ccenposequ = " . $CentroCusto . ",
                            esolcoobse = '" . $Observacao . "',
                            esolcoobje = '" . $Objeto . "',
                            esolcojust = '" . $Justificativa . "',
                            clicpoproc = " . $strNumProcessoSARP . ",
                            alicpoanop = " . $strAnoProcessoSARP . ",
                            cgrempcodi = " . $strGrupoEmpresaCodigoSARP . ",
                            ccomlicodi = " . $strComissaoCodigoSARP . ",
                            corglicod1 = " . $strOrgaoLicitanteCodigoSARP . ",
                            dsolcodpdo = " . $strDataDom . ",
                            ctpleitipo = " . $strTipoLei . ",
                            cleiponume = " . $strLei . ",
                            cartpoarti = " . $strArtigo . ",
                            cincpainci = " . $strInciso . ",
                            fsolcorgpr = " . $strRegistroPreco . ",
                            fsolcorpcp = " . $strSarp . ",
                            fsolcocont = " . $strGeraContrato . ",
                            --cusupocodi = " . $_SESSION['_cusupocodi_'] . ", -- usuário de inclusão nao deve mudar
                            cusupocod1 = " . $_SESSION['_cusupocodi_'] . ",
                            tsolcoulat = now(),
                            csitsocodi = " . $situacaoSolicitacao . "
                        WHERE
                            csolcosequ = $Solicitacao
                        ;
                    ";
            } else { //Inclusão
                $sql = "
                        INSERT INTO sfpc.tbsolicitacaocompra(
                    corglicodi, asolcoanos, csolcocodi, ctpcomcodi, csolcotipcosequ,
                    tsolcodata, ccenposequ, esolcoobse, esolcoobje, esolcojust, clicpoproc,
                    alicpoanop, cgrempcodi, ccomlicodi, corglicod1, dsolcodpdo, ctpleitipo,
                    cleiponume, cartpoarti, cincpainci, fsolcorgpr, fsolcorpcp, fsolcocont,
                    cusupocodi, cusupocod1, tsolcoulat, csitsocodi
                        ) VALUES (
                                " . $Orgao . ", " . $anoAtual . ", " . $sequencialPorAnoOrgao . ", " . $TipoCompra . ", " . $sequencialPorAnoOrgaoTipo . ",
                    now(), " . $CentroCusto . ", '" . $Observacao . "', '" . $Objeto . "', '" . $Justificativa . "', " . $strNumProcessoSARP . ",
                    " . $strAnoProcessoSARP . ", " . $strGrupoEmpresaCodigoSARP . ", " . $strComissaoCodigoSARP . ", " . $strOrgaoLicitanteCodigoSARP . ", " . $strDataDom . ", " . $strTipoLei . ",
                    " . $strLei . ", " . $strArtigo . ", " . $strInciso . ", " . $strRegistroPreco . ", " . $strSarp . ", " . $strGeraContrato . ",
                    " . $_SESSION['_cusupocodi_'] . ", " . $_SESSION['_cusupocodi_'] . ", now(), " . $situacaoSolicitacao . "
                        );
                    ";
            }

            executarTransacao($db, $sql);

            # Pegando sequencial de solicitação
            if ($Botao == 'Manter' or $Botao == "ManterRascunho") {
                $sequencialSolicitacao = $Solicitacao;
            } else {
                $sql = "
                        select last_value from SFPC.TBsolicitacaocompra_csolcosequ_seq1
                    ";
                $sequencialSolicitacao = resultValorUnico(executarTransacao($db, $sql));
            }
            # Deletando itens e salvando no histórico
            if ($Botao == 'Manter' or $Botao == "ManterRascunho") {
                # Apagando PSEs para apagar os itens de SCC
                $sql = "
                        select
                            '( '||apresoanoe||', '||cpresosequ||')' as chave
                        from SFPC.TBPRESOLICITACAOEMPENHO
                        where csolcosequ = " . $sequencialSolicitacao . "
                    ";
                $resPSE = executarSQL($db, $sql);
                if (hasPSEImportadaSofin($db, $sequencialSolicitacao)) {
                    assercao(false, "ERRO: SCC possui PSE que já foi processado pelo SOFIN. Portanto, não é possível alterá-la!");
                }
                while ($pse = $resPSE->fetchRow(DB_FETCHMODE_OBJECT)) {
                    $sql = "DELETE FROM SFPC.TBITEMPRESOLICITACAOEMPENHO WHERE (apresoanoe, cpresosequ) = " . $pse->chave . "";
                    executarTransacao($db, $sql);
                }

                $sql = "DELETE FROM SFPC.TBPRESOLICITACAOEMPENHO WHERE CSOLCOSEQU = " . $sequencialSolicitacao . "";
                executarTransacao($db, $sql);
                # remover todos itens de compra para depois recriá-los
                $sql = "DELETE FROM sfpc.tbitemdotacaoorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitembloqueioorcament WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbtabelareferencialprecos WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                $sql = "DELETE FROM sfpc.tbitemsolicitacaocompra WHERE csolcosequ = $sequencialSolicitacao";
                executarTransacao($db, $sql);
                # salvar o histórico da situação da SCC
                if ($situacaoSolicitacaoAtual != $situacaoSolicitacao) {
                    $sql = "
                            INSERT INTO sfpc.tbhistsituacaosolicitacao(
                            csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                            VALUES (
                        $sequencialSolicitacao, now(), " . $situacaoSolicitacao . ", NULL, " . $_SESSION['_cusupocodi_'] . ", now()
                            );
                        ";
                }
                executarTransacao($db, $sql);
            } else { # Incluir
                # salvar o histórico da situação da SCC
                $sql = "
                        INSERT INTO sfpc.tbhistsituacaosolicitacao(
                        csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                    VALUES (
                            $sequencialSolicitacao, now(), " . $situacaoSolicitacao . ", NULL, " . $_SESSION['_cusupocodi_'] . ", now()
                        );
                    ";
                executarTransacao($db, $sql);
            }

            # Incluindo os itens
            $sequencialItem = 0;

            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    $sequencialItem++;
                    $ordem = $material['posicao'] + 1;

                    $totalExercicio = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    if (!$ocultarCampoExercicio) {
                        //$totalExercicio = moeda2float($material["quantidadeExercicio"]) * moeda2float($material["valorEstimado"]);
                        $totalExercicio = $material['totalExercicio'];
                        $quantidadeExercicio = $material['quantidadeExercicio'];
                    }
                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($material['fornecedor']) . "'";
                        $sql = "
                                select aforcrsequ
                                  from sfpc.tbfornecedorcredenciado
                                 where aforcrccgc = " . $strFornecedor . " or aforcrccpf = " . $strFornecedor . "
                            ";
                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));
                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }

                    $sql = " INSERT INTO sfpc.tbitemsolicitacaocompra(
                                csolcosequ, citescsequ, cmatepsequ, cservpsequ, eitescdescse,
                                aitescorde, aitescqtso, vitescunit, vitescvexe, aitescqtex, aforcrsequ,
                                eitescmarc, eitescmode, cusupocodi, titesculat, eitescdescmat
                                ) VALUES (%d, %d, %d, null, null, %d, '%s', '%s', '%s', '%s', %s, '%s', '%s', %d, now(), '%s'); ";

                    $sqlinsert = sprintf(
                            $sql,
                            $sequencialSolicitacao, //csolcosequ
                            $sequencialItem, //citescsequ
                            $material['codigo'], //cmatepsequ
                            $ordem, //aitescorde
                            moeda2float($material['quantidade']), //aitescqtso
                            moeda2float($material['valorEstimado']), //vitescunit
                            moeda2float($totalExercicio), //vitescvexe
                            moeda2float($quantidadeExercicio), //aitescqtex
                            $strFornecedorSeq, //aforcrsequ
                            $material['marca'], //eitescmarc
                            $material['modelo'], //eitescmode
                            $_SESSION['_cusupocodi_'], //cusupocodi
                            strtoupper2($material['descricaoDetalhada']) //eitescdescmat
                            );

                    executarTransacao($db, $sqlinsert);

                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                if ($isDotacao) {

                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(!is_null($dados), 'Dotação Inválido ou Inexistente');

                                    $sql = "
                                            INSERT INTO sfpc.tbitemdotacaoorcament(
                                            citescsequ, csolcosequ, aitcdounidoexer, citcdounidoorga, citcdounidocodi,
                                            citcdotipa, aitcdoordt, citcdoele1,
                                            citcdoele2, citcdoele3, citcdoele4, citcdofont, titcdoulat
                                            ) VALUES (
                                            " . $sequencialItem . ", " . $sequencialSolicitacao . ", " . $dados['ano'] . ", " . $dados['orgao'] . ", " . $dados['unidade'] . ",
                                            " . $dados['tipoProjetoAtividade'] . ", " . $dados['projetoAtividade'] . ", " . $dados['elemento1'] . ",
                                            " . $dados['elemento2'] . ", " . $dados['elemento3'] . ", " . $dados['elemento4'] . ", " . $dados['fonte'] . ", now()
                                            );
                                            ";
                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);
                                    assercao(!is_null($dados), 'Bloqueio Inválido ou Inexistente');
                                    $sql = "
                                            INSERT INTO sfpc.tbitembloqueioorcament(
                                            csolcosequ, citescsequ, titcblulat, aitcblnbloq, aitcblanob)
                                            VALUES (
                                            " . $sequencialSolicitacao . ", " . $sequencialItem . ", now(), " . $dados['sequencialChave'] . ", " . $dados['anoChave'] . "
                                            );
                                            ";
                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }
            if (!is_null($servicos)) {
                foreach ($servicos as $servico) {
                    $sequencialItem++;
                    $ordem = $servico['posicao'] + 1;
                    $totalExercicio = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    if (!$ocultarCampoExercicio) {
                        //$totalExercicio = moeda2float($material["quantidadeExercicio"]) * moeda2float($material["valorEstimado"]);
                        $totalExercicio = $servico['totalExercicio'];
                        $quantidadeExercicio = $servico['quantidadeExercicio'];
                    }

                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($servico['fornecedor']) . "'";
                        $sql = "
                                select aforcrsequ
                                  from sfpc.tbfornecedorcredenciado
                                 where aforcrccgc = " . $strFornecedor . " or aforcrccpf = " . $strFornecedor . "
                            ";
                        $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));
                        if (is_null($strFornecedorSeq)) {
                            $strFornecedorSeq = 'null';
                        } else {
                            $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                        }
                    }

                    $sql = "
                            INSERT INTO sfpc.tbitemsolicitacaocompra(
                    csolcosequ, citescsequ, cmatepsequ, cservpsequ, eitescdescse,
                    aitescorde, aitescqtso, vitescunit, vitescvexe, aitescqtex,
                    aforcrsequ, eitescmarc, eitescmode, cusupocodi, titesculat)
                            VALUES (" . $sequencialSolicitacao . ", " . $sequencialItem . ", null, " . $servico['codigo'] . ", '" . strtoupper($servico['descricaoDetalhada']) . "',
                    " . $ordem . ", '" . moeda2float($servico['quantidade']) . "', '" . moeda2float($servico['valorEstimado']) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "',
                                $strFornecedorSeq, null, null, " . $_SESSION['_cusupocodi_'] . ", now());

                        ";

                    executarTransacao($db, $sql);
                    if (count($Bloqueios) > 0) {
                        foreach ($Bloqueios as $bloqueio) {
                            if (isset($bloqueio)) {
                                echo $isDotacao;
                                if ($isDotacao) {
                                    $dados = getDadosDotacaoOrcamentaria($dbOracle, $bloqueio);
                                    assercao(!is_null($dados), 'Dotação Inválido ou Inexistente');
                                    $sql = "
                                            INSERT INTO sfpc.tbitemdotacaoorcament(
                                            citescsequ, csolcosequ, aitcdounidoexer, citcdounidoorga, citcdounidocodi,
                                            citcdotipa, aitcdoordt, citcdoele1,
                                            citcdoele2, citcdoele3, citcdoele4, citcdofont, titcdoulat
                                            ) VALUES (
                                            " . $sequencialItem . ", " . $sequencialSolicitacao . ", " . $dados['ano'] . ", " . $dados['orgao'] . ", " . $dados['unidade'] . ",
                                            " . $dados['tipoProjetoAtividade'] . ", " . $dados['projetoAtividade'] . ", " . $dados['elemento1'] . ",
                                            " . $dados['elemento2'] . ", " . $dados['elemento3'] . ", " . $dados['elemento4'] . ", " . $dados['fonte'] . ", now()
                                            );
                                            ";
                                    executarTransacao($db, $sql);
                                } else {
                                    $dados = getDadosBloqueio($dbOracle, $bloqueio);
                                    assercao(!is_null($dados), 'Bloqueio Inválido ou Inexistente');
                                    $sql = "
                                            INSERT INTO sfpc.tbitembloqueioorcament(
                                            csolcosequ, citescsequ, titcblulat, aitcblnbloq, aitcblanob)
                                        VALUES (
                                            " . $sequencialSolicitacao . ", " . $sequencialItem . ", now(), " . $dados['sequencialChave'] . ", " . $dados['anoChave'] . "
                                        );
                                        ";
                                    executarTransacao($db, $sql);
                                }
                            }
                        }
                    }
                }
            }

            # inserir documentos
            $dirdestino = $GLOBALS["CAMINHO_UPLOADS"] . "compras/";
            for ($i = 0; $i < count($_SESSION['Arquivos_Upload']['conteudo']); $i++) {
                $sql = "
                            SELECT MAX(CDOCSOCODI)
                            FROM SFPC.TBDOCUMENTOSOLICITACAOCOMPRA
                         WHERE CSOLCOSEQU = $sequencialSolicitacao
                        ";
                $CodigoDocto = resultValorUnico(executarTransacao($db, $sql)) + 1;
                $NomeDocto = "DOC_" . $sequencialSolicitacao . "_" . $CodigoDocto . "_" . $_SESSION['Arquivos_Upload']['nome'][$i];
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
                                        CDOCSOCODI = " . $_SESSION['Arquivos_Upload']['codigo'][$i] . "
                                ";
                    executarTransacao($db, $sql);
                }
            }

            # Transação foi bem sucedida. gerar pre solicitação
            if ($situacaoSolicitacao == $TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO) {
                #neste ponto, o pendente de empenho são só de SCCs que não foram processadas ainda pelo sofin. Neste caso
                try {
                    gerarPreSolicitacaoEmpenho($db, $dbOracle, $sequencialSolicitacao);
                } catch (Excecao $e) {
                    cancelarTransacao($db);
                    $e->getMessage();
                    adicionarMensagem("Não foi possível gerar a solicitação de compra pois houve falha ao gerar a solicitação de empenho, com a seguinte mensagem: " . $e->getMessage(), $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                }
            }

            //***********************************
            // Gerar TRP
            //***********************************
            if ($GLOBALS['Mens'] != 1) {
                inserirItensSCCNaTrp($sequencialSolicitacao, $db);
            }

            if ($GLOBALS['Mens'] != 1) {
                finalizarTransacao($db);
                $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $sequencialSolicitacao);

                if ($acaoPagina == ACAO_PAGINA_MANTER) {
                    $Mensagem = "Solicitação " . $strSolicitacaoCodigo . " Alterada com Sucesso";
                    header("location: CadSolicitacaoCompraManterEspecialSelecionar.php?Mens=1&Tipo=1&Mensagem=" . $Mensagem);
                    exit;

                    //adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);
                } else {
                    $Mensagem = "Solicitação " . $strSolicitacaoCodigo . " Incluída com Sucesso";

                    //adicionarMensagem($Mensagem, $GLOBALS["TIPO_MENSAGEM_ATENCAO"]);

                    # Limpar variáveis
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
                    $MaterialQuantidade = null;
                    $MaterialValorEstimado = null;
                    $MaterialTotalExercicio = null;
                    $MaterialQuantidadeExercicio = null;
                    $MaterialMarca = null;
                    $MaterialModelo = null;
                    $MaterialFornecedor = null;

                    $ServicoCheck = null;
                    $ServicoCod = null;
                    $ServicoQuantidade = null;
                    $ServicoDescricaoDetalhada = null;
                    $ServicoQuantidadeExercicio = null;
                    $ServicoValorEstimado = null;
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

                    header("location: CadSolicitacaoCompraManterEspecialSelecionar.php?Mens=1&Tipo=1&Mensagem=" . $Mensagem);
                    exit;
                }
            }
        }
    }
} elseif ($Botao == "Retirar") {
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; $itr++) {
        if ($materiais[$itr]["check"]) {
            $materiais = array_removerItem($materiais, $itr);
            //$MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
            $quantidadeNova = count($materiais);
            if ($quantidadeNova <> $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
                $quantidade = $quantidadeNova;
                $itr--; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($materiais);
    for ($itr = 0; $itr < $quantidade; $itr++) {
        $materiais[$itr]['posicao'] = $itr;
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; $itr++) {
        if ($servicos[$itr]["check"]) {
            $servicos = array_removerItem($servicos, $itr);
            //$ServicoBloqueioItem = array_removerItem($ServicoBloqueioItem, $itr);
            $quantidadeNova = count($servicos);
            if ($quantidadeNova <> $quantidade) {
                $quantidade = $quantidadeNova;
                $itr--; // compensando a posição do item removido
            }
        }
    }
    $quantidade = count($servicos);
    for ($itr = 0; $itr < $quantidade; $itr++) {
        $servicos[$itr]['posicao'] = $itr;
    }
} elseif ($Botao == "Incluir_Documento") {
    if ($_FILES['Documentacao']['tmp_name']) {
        $_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);
        $extensoes = explode(",", strtolower2($extensoesArquivo));
        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;
        for ($itr = 0; $itr < $noExtensoes; $itr++) {
            //echo strtolower2($_FILES['Documentacao']['name']);
            //echo "\n".strtolower2($_FILES['Documentacao']['name']);
            //exit;
            if (preg_match("/\\" . trim($extensoes[$itr]) . "$/", strtolower2($_FILES['Documentacao']['name']))) {
                $isExtensaoValida = true;
            }
        }
        if (!$isExtensaoValida) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Selecione somente documento com a(s) extensão(ões) " . $extensoesArquivo;
        }
        if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Nome do Arquivo com até " . $tamanhoNomeArquivo . " Caracateres ( atualmente com " . strlen($_FILES['Documentacao']['name']) . " )";
        }
        $Tamanho = $tamanhoArquivo * 1024;
        if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Kbytes = $tamanhoArquivo;
            $Kbytes = (int) $Kbytes;
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }
        if ($Mens == "") {
            if (!($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "Caminho da Documentação Inválido";
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; //como é um arquivo novo, ainda nao possui código
            }
        }
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = "Documentação Inválida";
    }
} elseif ($Botao == "Retirar_Documento") {
    foreach ($DDocumento as $valor) {
        //$_SESSION['Arquivos_Upload']['conteudo'][$valor]="";
        //$_SESSION['Arquivos_Upload']['nome'][$valor]="";
        if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; //cancelado- quando o usuário incluiu um arquivo novo mas desistiu
        } elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; //excluído- quando o arquivo já existe e deve ser excluido no sistema
        }
    }
} elseif ($Botao == "IncluirBloqueio") {

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
            adicionarMensagem("<a href=\"javascript:document.getElementById('BloqueioUnidade').focus();\" class='titulo2'>Unidade do Bloqueio</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
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
    $Foco = "BloqueioAno";
    if ($isDotacao)
        $Foco = "DotacaoAno";

    if ($GLOBALS['Mens'] != 1) {
        if (is_null($BloqueioTodosData)) {
            adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . " não existe</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
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
                adicionarMensagem("<a href=\"javascript:document.getElementById('" . $Foco . "').focus();\" class='titulo2'>" . $reserva . " repetido(a)</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
            }
        }
        if (!$isRepetido) {
            array_push($Bloqueios, $BloqueioTodos);
        }
    }
} elseif ($Botao == "RetirarBloqueio") {
    $quantidade = count($Bloqueios);
    for ($itr = 0; $itr < $quantidade; $itr++) {
        if ($BloqueiosCheck[$itr]) {
            unset($Bloqueios[$itr]);
        }
    }
    unset($BloqueiosCheck);
    if ($is_dotacao) {
        $Foco = "DotacaoAno";
    } else {
        $Foco = "BloqueioAno";
    };
}

if (!$cargaInicial and $isDotacaoAnterior != $isDotacao) { //era bloqueio e agora é dotação
    unset($Bloqueios);
    unset($BloqueiosCheck);
}

# INÍCIO DA GERAÇÃO DA PÁGINA

$acesso = "";
if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $acesso = "Incluir";
    $descricao = "Preencha os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *.
                                                 O valor estimado   refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
    $acesso = "Manter Especial";
    $descricao = "Preencha os dados abaixo e clique no botão 'Manter'. Os itens obrigatórios estão com *.
                                                 O valor estimado   refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $acesso = "Acompanhar";
    $descricao = "Para visualizar nova solicitação clique no botão 'Voltar'.";
} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $acesso = "Cancelar";
    $descricao = "Clique no botão Cancelar Solicitação.";
}

$template = new TemplatePaginaPadrao("templates/CadSolicitacaoCompraIncluirManterEspecialExcluir.template.html", "Compras > " . $acesso);

if ($programa == 'CadLicitacaoIncluir.php') {
    $template->NOME_PROGRAMA = 'CadSolicitacaoCompraIncluirManterExcluir.php';
} else {
    $template->NOME_PROGRAMA = $programa;
}

//$template->ACESSO = $acesso;
$template->ACESSO_TITULO = strtoupper2($acesso);
$template->DESCRICAO = $descricao;

if (!$ocultarCampoNumeroSCC) {
    $template->block("BLOCO_NUMERO_SCC");
    $template->NUMERO_SCC = getNumeroSolicitacaoCompra($db, $Solicitacao);
}
if (!$ocultarCampoNumero) {
    $template->SEQUENCIAL_SCC = $Numero;
    $template->SEQUENCIAL_SCC_VALOR = $Numero;
    $template->block("BLOCO_SEQUENCIAL_SCC");
}
if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $DataSolicitacao = date("d/m/Y");
}
//$template->DATA_SCC = $DataSolicitacao;
$template->DATA_SCC_VALOR = $DataSolicitacao;

### Centro de custo
# Pegando dados do usuário
$sql = "
    SELECT USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
    FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
    WHERE
        USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
        AND USUCEN.FUSUCCTIPO IN ('C')
        AND (
            (
                USUCEN.CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . "
                AND USUCEN.CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "
            ) OR (
                USUCEN.CUSUPOCOD1 = " . $_SESSION['_cusupocodi_'] . " AND
                USUCEN.CGREMPCOD1 = " . $_SESSION['_cgrempcodi_'] . " AND
                '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS
            )
        ) AND USUCEN.FUSUCCTIPO = 'C'
        AND CENCUS.FCENPOSITU <> 'I'
";
$res = executarSQL($db, $sql);

$Rows = $res->numRows();
if ($Rows != 0) {
    $Linha = $res->fetchRow();
    $TipoUsuario = $Linha[0];
    $OrgaoUsuario = $Linha[1];
    if ($TipoUsuario == "R") {
        $DescUsuario = "Requisitante";
    } elseif ($TipoUsuario == "A") {
        $DescUsuario = "Aprovador";
    } else {
        $DescUsuario = "Atendimento";
    }
}

if (($_SESSION['_cgrempcodi_'] != 0) and ( $TipoUsuario == "C")) {
    $sqlCC = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, ";
    $sqlCC .= "       B.CORGLICODI, B.EORGLIDESC, B.FORGLITIPO ";
    $sqlCC .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
    $sqlCC .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = " . date("Y") . "";
    $sqlCC .= "   AND A.CORGLICODI = B.CORGLICODI  ";
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    $sqlCC .= "   AND A.CCENPOSEQU IN  ";
    $sqlCC .= "        ( SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ";
    $sqlCC .= "       WHERE USU.CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . " AND USU.FUSUCCTIPO IN ('C'))";
    $sqlCC .= "       ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA ";
} else {
    $sqlCC = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,";
    $sqlCC .= "       D.CORGLICODI, D.EORGLIDESC, D.FORGLITIPO";
    $sqlCC .= "  FROM SFPC.TBCENTROCUSTOPORTAL A,  SFPC.TBGRUPOORGAO B, ";
    $sqlCC .= "       SFPC.TBGRUPOEMPRESA C, SFPC.TBORGAOLICITANTE D ";
    $sqlCC .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = " . date("Y") . "";
    $sqlCC .= "   AND A.CORGLICODI = B.CORGLICODI AND C.CGREMPCODI = B.CGREMPCODI ";
    $sqlCC .= "   AND B.CORGLICODI = D.CORGLICODI ";
    $sqlCC .= "   AND A.FCENPOSITU <> 'I' ";
    if ($TipoUsuario == "C") {
        $sqlCC .= " AND C.CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "";
    }
    $sqlCC .= " ORDER BY D.EORGLIDESC,A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA";
}
$resCC = executarSQL($db, $sqlCC);

$RowsCC = $resCC->numRows();
if ($RowsCC == 0) {
    # Nenhum centro de custo foi encontrado
    $template->block("BLOCO_CENTRO_CUSTO_NENHUM");

    /* // Sempre pegar o centro de custo da SCC no else abaixo.
	} elseif ($RowsCC == 1) {
    $Linha = $resCC->fetchRow();
    $CentroCusto = $Linha[0];
    $DescCentroCusto = $Linha[1];
    $RPA = $Linha[2];
    $Detalhamento = $Linha[3];
    $Orgao = $Linha[4];
    $DescOrgao = $Linha[5];
    $administracao = $Linha[6];

    # Apenas 1 CC foi encontrado
    $template->CC_ORGAO = $DescOrgao;
    $template->CC_RPA = $RPA;
    $template->CC_DESCRICAO = $DescCentroCusto;
    $template->CC_DETALHAMENTO = $Detalhamento;
    $template->block("BLOCO_CENTRO_CUSTO");
    */
} else {
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    if (!$ocultarCamposEdicao and $acaoPagina != ACAO_PAGINA_MANTER) {
        # Vários CCs existem
        $template->CC_TIPO_USUARIO = $TipoUsuario;
        $template->block("BLOCO_CENTRO_CUSTO_SELECIONAR");
    }
    if ($CentroCusto != "") {
        # Carrega os dados do Centro de Custo selecionado #
        $sql = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA, B.FORGLITIPO, A.FCENPOSITU";
        $sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
        $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
        //$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
        $res = executarSQL($db, $sql);

        while ($Linha = $res->fetchRow()) {
            $DescCentroCusto = $Linha[0];
            $DescOrgao = $Linha[1];
            $Orgao = $Linha[2];
            $RPA = $Linha[3];
            $Detalhamento = $Linha[4];
            $administracao = $Linha[5];
            $ccSituacao = $Linha[6];
            if ($ccSituacao == "I")
                $Detalhamento .= " (Centro de custo inativo)";
        }

        # Vários CCs existem mas um já foi selecionado
        $template->CC_ORGAO = $DescOrgao;
        $template->CC_RPA = $RPA;
        $template->CC_DESCRICAO = $DescCentroCusto;
        $template->CC_DETALHAMENTO = $Detalhamento;
        $template->block("BLOCO_CENTRO_CUSTO");
    }
}
$template->CC = $CentroCusto;
$template->CC_ADMINISTRACAO = $administracao;

### Fim Centro de custo

$template->CAMPO_OBJETO = gerarTextArea("formulario", "Objeto", $Objeto, $tamanhoObjeto, $ocultarCamposEdicao);
$template->CAMPO_OBSERVACAO = gerarTextArea("formulario", "Observacao", $Observacao, 200, $ocultarCamposEdicao);

### tipo de compra
$sql = "select ctpcomcodi, etpcomnome from SFPC.TBtipocompra";
$res = executarSQL($db, $sql);

if (!$ocultarCamposEdicao) {

    while ($linha = $res->fetchRow()) {
        $codTipoCompra = $linha[0];
        $nomeTipoCompra = $linha[1];
        $template->TIPO_COMPRA = $nomeTipoCompra;
        $template->TIPO_COMPRA_VALOR = $codTipoCompra;
        if ($TipoCompra == $codTipoCompra) {
            $template->TIPO_COMPRA_SELECTED = "selected";
            $template->VALOR_TIPO_COMPRA = $codTipoCompra;
        } else {
            $template->TIPO_COMPRA_SELECTED = "";
        }
        $template->block("BLOCO_TIPO_COMPRA_ITEM");
    }
    $template->block("BLOCO_TIPO_COMPRA");
} else {
    while ($linha = $res->fetchRow()) {
        $codTipoCompra = $linha[0];
        $nomeTipoCompra = $linha[1];
        if ($TipoCompra == $codTipoCompra) {
            $template->TIPO_COMPRA = $nomeTipoCompra;
            $template->block("BLOCO_TIPO_COMPRA_VISUALIZAR");
        }
    }
}

### fim tipo compra

if (!$ocultarCampoRegistroPreco) {
    $template->CAMPO_REGISTRO_PRECO = gerarRadioButtons('RegistroPreco', array('SIM', 'NAO'), array('S', 'N'), $RegistroPreco, false, $ocultarCamposEdicao, 'submit()');
    $template->block("BLOCO_REGISTRO_PRECO");
}
if (!$ocultarCampoSARP) {
    $template->CAMPO_SARP = gerarRadioButtons('Sarp', array('CARONA', 'PARTICIPANTE'), array('C', 'P'), $Sarp, false, $ocultarCamposEdicao);
    $template->block("BLOCO_SARP");
}

### inicio processo licitatorio SARP
if (!$ocultarCampoProcessoLicitatorio) {
    if (!$ocultarCamposEdicao) {
        $template->block("BLOCO_LICITACAO_SELECIONAR");
    }
    if ($CarregaProcessoSARP == 1) {

        $sql = "
                                                    SELECT
                                                        distinct A.CLICPOPROC, A.ALICPOANOP, D.ECOMLIDESC,  B.EORGLIDESC
                                                      FROM
                                                        SFPC.TBLICITACAOPORTAL A,
                                                        SFPC.TBORGAOLICITANTE B,
                                                        SFPC.TBCOMISSAOLICITACAO D
                                                     WHERE
                                                        A.CORGLICODI = B.CORGLICODI AND
                                                        A.FLICPOSTAT = 'A' AND
                                                        A.CCOMLICODI = D.CCOMLICODI ";

        if ($NumProcessoSARP != "") {
            $sql .= " AND A.CLICPOPROC = $NumProcessoSARP ";
        }
        if ($AnoProcessoSARP != "") {
            $sql .= " AND A.ALICPOANOP = $AnoProcessoSARP ";
        }
        if ($ComissaoCodigoSARP != "") {
            $sql .= " AND A.CCOMLICODI = $ComissaoCodigoSARP ";
        }
        if ($OrgaoLicitanteCodigoSARP != "") {
            $sql .= " AND A.CORGLICODI = $OrgaoLicitanteCodigoSARP ";
        }
        if ($GrupoEmpresaCodigoSARP != "") {
            $sql .= " AND A.CGREMPCODI = $GrupoEmpresaCodigoSARP ";
        }

        $res = executarTransacao($db, $sql);
        $Rows = $res->numRows();
        $Linha = $res->fetchRow();
        $ProcessoAnoSARP = $Linha[0] . "/" . $Linha[1];
        $ComissaoDescricaoSARP = $Linha[2];
        $OrgaoLicitanteDescricaoSARP = $Linha[3];

        if ($Rows == 1) {
            $template->SARP_LICITACAO_ANO = $ProcessoAnoSARP;
            $template->SARP_LICITACAO_COMISSAO = $ComissaoDescricaoSARP;
            $template->SARP_LICITACAO_ORGAO = $OrgaoLicitanteDescricaoSARP;
            $template->block("BLOCO_LICITACAO_VISUALIZAR");
        }
    } else {

    }

    $template->SARP_LICITACAO_PROCESSO = $NumProcessoSARP;
    $template->SARP_LICITACAO_ANO_VALOR = $AnoProcessoSARP;
    $template->SARP_LICITACAO_COMISSAO_VALOR = $ComissaoCodigoSARP;
    $template->SARP_LICITACAO_ORGAO_VALOR = $OrgaoLicitanteCodigoSARP;
    $template->SARP_LICITACAO_EMPRESA = $GrupoEmpresaCodigoSARP;
    $template->SARP_LICITACAO_CARREGA = $CarregaProcessoSARP;
    $template->block("BLOCO_LICITACAO");
}
### fim processo licitatorio SARP

if (!$ocultarCampoGeraContrato or $preencherCampoGeraContrato) {
    $template->CAMPO_CONTRATO = gerarRadioButtons('GeraContrato', array('SIM', 'NAO'), array('S', 'N'), $GeraContrato, false, $ocultarCamposEdicao or $preencherCampoGeraContrato);
    $template->block("BLOCO_CONTRATO");
}
if (!$ocultarCamposEdicao) {
    if (!$ocultarCampoFornecedor) {
        $CnpjStr = FormataCpfCnpj($CnpjFornecedor);
        $template->FORNECEDOR_CNPJ = $CnpjStr;
        if (!is_null($CnpjFornecedor)) {
            $CPFCNPJ = removeSimbolos($CnpjFornecedor);
            $materialServicoFornecido = null;
            $TipoMaterialServico = null;
            $resposta = checaSituacaoFornecedor($db, $CPFCNPJ);
            if (!is_null($resposta) and ! is_null($resposta["razao"]) and $resposta["razao"] != "") {
                $template->FORNECEDOR = $resposta["razao"];
            }
        }
        $template->block("BLOCO_FORNECEDOR");
    }
}

$template->READ_ONLY = $ifVisualizacaoThenReadOnly;

ob_start(); //pegando o html ainda não tratado pelo template, para depois jogar no template
?>

<?php
if (!$ocultarCampoLegislacao) {
    ?>

    <tr>
    <td class="textonormal" bgcolor="#DCEDF7">Legislação*</td>
    <td class="textonormal">
    <?php
    $sql = "select ctpleitipo, etpleitipo from SFPC.TBtipoleiportal";
    $res = executarTransacao($db, $sql);
    if (!$ocultarCamposEdicao) {
        ?>
            Tipo de lei:
            <select name="TipoLei" size="1" <?= $ifVisualizacaoThenReadOnly ?> class="textonormal"
                    onChange="atualizar('TipoLei')">
                <option value="">Selecionar Tipo de Lei</option>
        <?php
        while ($Linha = $res->fetchRow()) {
            $tipoLeiItem = $Linha[0];
            $tipoLeiDesc = $Linha[1];
            ?>
                    <option value="<?= $tipoLeiItem ?>" <?php
            if ($tipoLeiItem == $TipoLei) {
                echo "selected";
            }
            ?> ><?= $tipoLeiDesc ?></option>
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
                echo "Tipo de lei: " . $tipoLeiDesc . "  ";
            }
        }
    }
    ?>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <?php
        if (!is_null($TipoLei) and $TipoLei <> "") {
            $sql = "select cleiponume from sfpc.tbleiportal where ctpleitipo= " . $TipoLei;
            $res = executarTransacao($db, $sql);
        }
        if (!$ocultarCamposEdicao) {
            ?>
            Lei:
            <select name="Lei" size="1" <?= $ifVisualizacaoThenReadOnly ?> class="textonormal"
                    onChange="atualizar('Lei')">
                <option value="">Selecionar Lei</option>
                <?php
                if (!is_null($TipoLei) and $TipoLei <> "") {
                    while ($Linha = $res->fetchRow()) {
                        $leiItem = $Linha[0];
                        ?>
                        <option value="<?= $leiItem ?>" <?php
                if ($leiItem == $Lei) {
                    echo "selected";
                }
                        ?> ><?= $leiItem ?></option>
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
                    echo "Lei: " . $leiItem . " ";
                }
            }
        }
        ?>

        &nbsp;&nbsp;&nbsp;&nbsp;
    <?php
    if (!is_null($TipoLei) and $TipoLei <> '' and ! is_null($Lei) and $Lei <> '') {
        $sql = "
                                                            select cartpoarti--, nartponume
                                                            from sfpc.tbartigoportal
                                                            where
                                                                ctpleitipo= " . $TipoLei . "
                                                                and cleiponume = " . $Lei . "
                                                        ";
        $res = executarTransacao($db, $sql);
    }
    if (!$ocultarCamposEdicao) {
        ?>

            Artigo:
            <select name="Artigo" size="1" <?= $ifVisualizacaoThenReadOnly ?> class="textonormal"
                    onChange="atualizar('Artigo')">
                <option value="">Selecionar Artigo</option>
            <?php
            if (!is_null($TipoLei) and $TipoLei <> '' and ! is_null($Lei) and $Lei <> '') {
                while ($Linha = $res->fetchRow()) {
                    $artigoItem = $Linha[0];
                    //$artigoNumero = $Linha[1];
                    ?>
                        <option value="<?= $artigoItem ?>" <?php
                if ($artigoItem == $Artigo) {
                    echo "selected";
                }
                    ?> ><?= $artigoItem ?></option>
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
                    echo "Artigo " . $artigoItem . "  ";
                }
            }
        }
        ?>

        &nbsp;&nbsp;&nbsp;&nbsp;
            <?php
            if (!is_null($TipoLei) and $TipoLei <> '' and ! is_null($Lei) and $Lei <> '' and ! is_null($Artigo) and $Artigo <> '') {
                $sql = "
                                                select cincpainci, nincpanume
                                                  from sfpc.tbincisoparagrafoportal
                                                 where ctpleitipo = " . $TipoLei . "
                                                     and cleiponume = " . $Lei . "
                                                     and cartpoarti = " . $Artigo . "
                                            ";
                $res = executarTransacao($db, $sql);
            }
            if (!$ocultarCamposEdicao) {
                ?>

            Inciso/Parágrafo:
            <select name="Inciso" size="1" <?= $ifVisualizacaoThenReadOnly ?> class="textonormal">
                <option value="">Selecionar Inciso ou Parágrafo</option>
            <?php
            if (!is_null($TipoLei) and $TipoLei <> '' and ! is_null($Lei) and $Lei <> '' and ! is_null($Artigo) and $Artigo <> '') {
                while ($Linha = $res->fetchRow()) {
                    $incisoItem = $Linha[0];
                    $incisoNumero = $Linha[1];
                    ?>
                        <option value="<?= $incisoItem ?>" <?php
                    if ($incisoItem == $Inciso) {
                        echo "selected";
                    }
                    ?> ><?= $incisoNumero ?></option>
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
                    echo "Inciso/ parágrafo: " . $incisoNumero . " ";
                }
            }
        }
        ?>

    </td>
    </tr>
            <?php
        }
        if (!$ocultarCampoJustificativa) {
            ?>
    <tr>
    <td class="textonormal" bgcolor="#DCEDF7">Justificativa</td>
                    <?php if (!$ocultarCamposEdicao) { ?>
        <td class="textonormal">
            <font class="textonormal">máximo de <?= $tamanhoJustificativa ?> caracteres</font>
            <input type="text" name="NCaracteresJustificativa" size="3" disabled
            value="<?php echo $NCaracteresJustificativa ?>"
                   class="textonormal"><br>
            <textarea name="Justificativa" cols="50"  rows="4"
                      OnKeyUp="javascript:CaracteresJustificativa(1)" OnBlur="javascript:CaracteresJustificativa(0)"
                      OnSelect="javascript:CaracteresJustificativa(1)"
                      class="textonormal" style="text-transform: uppercase;"><?php echo $Justificativa; ?></textarea>
        </td>
    <?php } else { ?>
        <td class="textonormal">
        <?php echo $Justificativa; ?>
        </td>
    <?php } ?>
    </tr>
    <?php
}
if (!$ocultarCampoDataDOM) {
    ?>
    <tr>
    <td class="textonormal" bgcolor="#DCEDF7">Data da publicação no DOM*</td>
    <td class="textonormal">
               <?php if (!$ocultarCamposEdicao) { ?>
            <input name="DataDom" <?= $ifVisualizacaoThenReadOnly ?>  id="DataDom" class="data" size="10"
                   maxlength="10" value="<?= $DataDom ?>" type="text">
            <a href="javascript:janela('../calendario.php?Formulario=CadSolicitacaoCompraIncluirManterExcluir&amp;Campo=DataDom','Calendario',220,170,1,0)"><img
                    src="../midia/calendario.gif" alt="" border="0"></a>
    <?php } else { ?>
        <?= $DataDom ?>
    <?php } ?>
    </td>
    </tr>
    <?php } ?>
<tr>
<?php #--------------------------Inicio Bloqueios ?>
<td class="textonormal" colspan="4">
    <input type="hidden" name="TipoReservaOrcamentaria" id="TipoReservaOrcamentaria"
           value="<?= $TipoReservaOrcamentaria ?>"/>

    <table id="scc_bloqueios" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
        <tbody>
            <tr>
                <td class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">
                    <span id="BloqueioTitulo" colspan=2>BLOQUEIO OU DOTAÇÃO ORÇAMENTÁRIA</span>
                </td>
            </tr>
            <?php
            $cntBloqueio = -1;
            if (!is_null($Bloqueios)) {
                foreach ($Bloqueios as $bloqueioItem) {
                    if (isset($bloqueioItem)) {
                        $cntBloqueio++;
                        ?>
                        <tr>
                            <td class="textonormal">
                                <?php
                                if (!$ocultarCamposEdicao) {
                                    ?>
                                    <input
                                        name="BloqueiosCheck[<?php echo $cntBloqueio; ?>]"
                                        type="checkbox" <?php if ($BloqueiosCheck[$cntBloqueio]) echo "checked"; ?>
                                        />
                                        <?php
                                    }
                                    ?>
                                    <?php echo $bloqueioItem ?>
                                <input name="Bloqueios[<?= $cntBloqueio ?>]" value="<?= $bloqueioItem ?>" type="hidden" />
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            <?php
            if (!$ocultarCamposEdicao) {
                ?>
                <tr>
                    <td class="textonormal" colspan=2 bgcolor="#ffffff">
                        <table class="textonormal" border="0" align="left" width="100%" summary="">
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="200px">Novo <span
                                        id="BloqueioLabel"><?= $reserva ?></span>:
                                </td>
                                <td class="textonormal">
                                    <?php
                                    if ($isDotacao) {
                                        ?>

                                        Ano: <input name="DotacaoAno" id="DotacaoAno" size="4" maxlength="4" value=""
                                                    type="text" value="<?= $DotacaoAno ?>"/>
                                        Órgão: <input name="DotacaoOrgao" id="DotacaoOrgao" size="2" maxlength="2" value=""
                                                      type="text" value="<?= $DotacaoAno ?>"/>
                                        Unidade: <input name="DotacaoUnidade" id="DotacaoUnidade" size="2" maxlength="2"
                                                        value="" type="text" value="<?= $DotacaoAno ?>"/>
                                        Funcao: <input name="DotacaoFuncao" id="DotacaoFuncao" size="2" maxlength="2"
                                                       value="" type="text" value="<?= $DotacaoFuncao ?>"/>
                                        SubFunção: <input name="DotacaoSubfuncao" id="DotacaoSubfuncao" size="4"
                                                          maxlength="4" value="" type="text"
                                                          value="<?= $DotacaoSubfuncao ?>"/>
                                        Programa: <input name="DotacaoPrograma" id="DotacaoPrograma" size="4" maxlength="4"
                                                         value="" type="text" value="<?= $DotacaoSubfuncao ?>"/>
                                        Tipo Projeto/Atividade: <input name="DotacaoTipoProjetoAtividade"
                                                                       id="DotacaoTipoProjetoAtividade" size="1"
                                                                       maxlength="1" value="" type="text"
                                                                       value="<?= $DotacaoTipoProjetoAtividade ?>"/>
                                        Projeto/Atividade: <input name="DotacaoProjetoAtividade"
                                                                  id="DotacaoProjetoAtividade" size="3" maxlength="3"
                                                                  value="" type="text"
                                                                  value="<?= $DotacaoProjetoAtividade ?>"/>
                                        Elemento1: <input name="DotacaoElemento1" id="DotacaoElemento1" size="1"
                                                          maxlength="1" value="" type="text"
                                                          value="<?= $DotacaoElemento1 ?>"/>
                                        Elemento2: <input name="DotacaoElemento2" id="DotacaoElemento2" size="1"
                                                          maxlength="1" value="" type="text"
                                                          value="<?= $DotacaoElemento2 ?>"/>
                                        Elemento3: <input name="DotacaoElemento3" id="DotacaoElemento3" size="2"
                                                          maxlength="2" value="" type="text"
                                                          value="<?= $DotacaoElemento3 ?>"/>
                                        Elemento4: <input name="DotacaoElemento4" id="DotacaoElemento4" size="2"
                                                          maxlength="2" value="" type="text"
                                                          value="<?= $DotacaoElemento4 ?>"/>
                                        Fonte: <input name="DotacaoFonte" id="DotacaoFonte" size="4" maxlength="4" value=""
                                                      type="text" value="<?= $DotacaoFonte ?>"/>

                                        <?php
                                    } else {
                                        ?>
                                        Ano: <input name="BloqueioAno" id="BloqueioAno" size="4" maxlength="4" value=""
                                                    type="text" value="<?= $BloqueioAno ?>"/>
                                        Órgão: <input name="BloqueioOrgao" id="BloqueioOrgao" size="2" maxlength="2"
                                                      value="" type="text" value="<?= $BloqueioOrgao ?>"/>
                                        Unidade: <input name="BloqueioUnidade" id="BloqueioUnidade" size="2" maxlength="2"
                                                        value="" type="text" value="<?= $BloqueioUnidade ?>"/>
                                        Destinação: <input name="BloqueioDestinacao" id="BloqueioDestinacao" size="1"
                                                           maxlength="1" value="" type="text"
                                                           value="<?= $BloqueioDestinacao ?>"/>
                                        Sequencial: <input name="BloqueioSequencial" id="BloqueioSequencial" size="4"
                                                           maxlength="4" value="" type="text"
                                                           value="<?= $BloqueioSequencial ?>"/>
                                                           <?php
                                                       }
                                                       ?>

                                    <?php
                                    /*
                                      <input name="BloqueioTodos" id="BloqueioTodos" class="bloqueioDotacao" size="40" maxlength="36" value="" type="text" value="<?=$BloqueioTodos?>"/>
                                      <a href="javascript:AbreJanela('InfPreenchimentoBloqueios.php',700,370);" id='CentroCustoLink'><img src="../midia/icone_interrogacao.gif" border="0"></a>
                                     */
                                    ?>
                                </td>
                            </tr>
                        </table>
    </td>
    </tr>
    <tr>
    <td class="textonormal" align="center">
        <input
            name="BotaoIncluirBloqueioTodos"
            value="Incluir <?= $reserva ?>"
            class="botao"
            type="button"
            onClick="incluirBloqueio()"
            />
        <input
            name="BotaoRemoverBloqueioTodos"
            value="Remover <?= $reserva ?>"
            class="botao"
            type="button"
            onClick="retirarBloqueio()"
            />
    </td>
    </tr>
                                   <?php } ?>
</tbody>
</table>
                                   <?php #--------------------------Inicio Itens ?>
<table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
    <tbody>
        <tr>
        <td colspan="" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">
            ITENS DA SOLICITAÇÃO DE MATERIAL
        </td>
        </tr>

<!-- Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->
<tr class="head_principal">
<?php
$descricaoWidth = "300px";

# redimensionando dependendo do número de campos
if ($TipoCompra == TIPO_COMPRA_LICITACAO and ( $RegistroPreco == "S" or is_null($RegistroPreco)) or is_null($TipoCompra))
    $descricaoWidth = "700px";

$qtdeColunas = 12;
$colunasOcultas = 0;
if ($ocultarCampoTRP) {
    $colunasOcultas++;
}
if ($ocultarCampoExercicio) {
    $colunasOcultas += 3;
}
if ($ocultarCampoFornecedor) {
    $colunasOcultas += 3;
}
if ($TipoCompra == TIPO_COMPRA_LICITACAO ) {
    $colunasOcultas -= 3;
}
?>
        <?php // <!--  Coluna 1 = ORD--> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
        <?php // <!--  Coluna 2 = DESCRIÇÃO DO MATERIAL--> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">
            <img src="../midia/linha.gif" alt="" border="0" height="1px" width="<?= $descricaoWidth ?>" />
            <br />
            DESCRIÇÃO DO MATERIAL
        </td>
        <?php // <!--  Coluna 3 = CÓD.RED. CADUM--> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">
            <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
            <br />
            CÓD.RED. CADUM
        </td>
        <?php // <!--  Coluna 4 = UND --> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">
            <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
            <br />
            UND
        </td>
            <?php
            /**
             * Exibir TD na layout?
             * @var boolean
             */
            $exibirTd = false;

            // Se material tiver indicador cadum (genérico) ou tiver descrição detalhada preenchida (diferente de vazio ou null)
            if (is_array($materiais)) {
                foreach ($materiais as $key) {
                    if ( (hasIndicadorCADUM($db, (int) $key['codigo']) === true)  ) {
                        $exibirTd = true;
                        break;
                    }
                }
            }

            if ($exibirTd) {
                ?>
                <?php // <!--  Coluna 5 = DESCRICAO DETALHADA --> ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">
                <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
                <br />
                DESCRIÇÃO DETALHADA
            </td>
    <?php
            } else {
                $colunasOcultas += 1;
            }
?>

<?php
if (!$ocultarCampoTRP) {
    ?>
            <?php // <!--  Coluna 6 = VALOR TRP --> ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">
                <img src="../midia/linha.gif" alt="" border="0" height="1px" width="10px" />
                <br />
                VALOR TRP
            </td>
    <?php
}
?>
            <?php // <!--  Coluna 7 = QUANTIDADE --> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">
            <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
            <br />
            QUANTIDADE
        </td>
        <?php // <!--  Coluna 8 = VALOR ESTIMADO --> ?>
        <td class="textoabason" align="center" bgcolor="#DCEDF7">
            <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
            <br />
            VALOR ESTIMADO
        </td>

        <!--  Inibido por Heraldo  4/11/2013-->
<?php //if (!$ocultarCampoExercicio) { ?>
        <!--
            <td class="textoabason" align="center" bgcolor="#DCEDF7"><img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"><br>QUANTIDADE NO EXERCÍCIO</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7"><img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"><br>VALOR NO EXERCÍCIO</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7"><img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"><br>VALOR NOS DEMAIS EXERCÍCIOS</td>
        -->
        <?php //}?>

        <?php
        if (!$ocultarCampoFornecedor) {
            ?>
            <?php // <!--  Coluna 10 = CPF/CNPJ DO FORNECEDOR --> ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">
                <img src="../midia/linha.gif" alt="" border="0" height="1px" width="300px" />
                <br />
                CPF/CNPJ DO FORNECEDOR
            </td>
            <?php // <!--  Coluna 11 = MARCA --> ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">
                <img src="../midia/linha.gif" alt="" border="0" height="1px" width="50px" />
                <br />
                MARCA
            </td>
            <?php // <!--  Coluna 12 = MODELO--> ?>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">
                <img src="../midia/linha.gif" alt="" border="0" height="1px" width="10px" />
                <br />
                MODELO
            </td>
    <?php
}
?>
        <?php // <!--  Coluna 9 = VALOR TOTAL --> ?>
    <td class="textoabason" align="center" bgcolor="#DCEDF7">
        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px" />
        <br />
        VALOR TOTAL
    </td>

</tr>
<!-- FIM Headers ITENS DA SOLICITAÇÃO DE MATERIAL  -->

<?php
# Materiais do POST-----------------------------------
$QuantidadeMateriais = count($materiais);
$QuantidadeServicos = count($servicos);
$ValorTotalItem = 0;
$ValorTotal = 0;

for ($itr = 0; $itr < $QuantidadeMateriais; $itr++) {
    $ValorTotalItem = moeda2float($materiais[$itr]["quantidade"]) * moeda2float($materiais[$itr]["valorEstimado"]);
    $ValorTotal += $ValorTotalItem;
    if (!$ocultarCampoExercicio) {
        //$ValorTotalExercicio = moeda2float($materiais[$itr]["quantidadeExercicio"]) * moeda2float($materiais[$itr]["valorEstimado"]);
        $ValorTotalExercicio = $materiais[$itr]["totalExercicio"];
        $TotalDemaisExercicios = $ValorTotalItem - moeda2float($ValorTotalExercicio);
        if ($TotalDemaisExercicios < 0) {
            $TotalDemaisExercicios = 0;
        }
    }
    ?>
<!-- Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
<tr>
                <!--  Coluna 1 = Codigo-->
            <td class="textonormal" align="center" style="text-align:center">
    <?= ($itr + 1) ?>
            </td>

            <!--  Coluna 2  = Descricao -->
            <td class="textonormal">
    <?php
    if (!$ocultarCamposEdicao) {
        ?>
                    <input
                        name="MaterialCheck[<?= $itr ?>]" <?= ($materiais[$itr]["check"]) ? 'checked' : ''; ?>
        <?= $ifVisualizacaoThenReadOnly ?>
                        type="checkbox"
                        />
                <?php
            }
            ?>
                <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?= $materiais[$itr]["codigo"] ?>&amp;TipoGrupo=M&amp;ProgramaOrigem=<?= $programa ?>',700,370);">
                    <font color="#000000"><?= $materiais[$itr]["descricao"] ?></font>
                </a>
            </td>

            <!--  Coluna 3 = Cod CADUM-->
            <td class="textonormal" style="text-align:center !important;">
            <?= $materiais[$itr]["codigo"] ?>
                <input value="<?= $materiais[$itr]["codigo"] ?>" name="MaterialCod[<?= $itr ?>]" type="hidden" />
                <input
                    value="<?= ($materiais[$itr]["isObras"]) ? 'true' : 'false'; ?>"
                    name="MaterialIsObras[<?= $itr ?>]"
                    id="MaterialIsObras_<?= $itr ?>"
                    type="hidden"
                    />
            </td>

            <!--  Coluna 4 = UND-->
            <td class="textonormal" align="center">
            <?= $materiais[$itr]["unidade"] ?>
            </td>

            <!--  Coluna 5 = DESCRIÇÃO DETALHADA-->
    <?php
    if ($exibirTd) {
        ?>
                <td class="textonormal" align="center">
        <?php
        if ( (hasIndicadorCADUM($db, $materiais[$itr]["codigo"])) ) {
            $disabled = '';
            if (!$ocultarCamposEdicao) { ?>
			<textarea style="text-transform: uppercase;"
				<?= $ifVisualizacaoThenReadOnly ?>
				name="MaterialDescricaoDetalhada[<?= $itr ?>]"
				id="MaterialDescricaoDetalhada_<?= $itr ?>"
				cols="50"
				rows="4"
				class="textonormal"><?= $materiais[$itr]["descricaoDetalhada"] ?>
			</textarea>

			<?php if ($ifVisualizacaoThenReadOnly) {
				$desc = $materiais[$itr]['descricaoDetalhada'];
				echo "<input type=\"hidden\" name=\"MaterialDescricaoDetalhada[$itr]\" value=\"$desc\" />";
				}
			?>


			<?php
			}
				else { echo $materiais[$itr]["descricaoDetalhada"]; }
            ?>
                    <?php
            }
        else {
           echo "<nobr>---</nobr>";
           echo "<input name='MaterialDescricaoDetalhada[" . $itr . "]' id='MaterialDescricaoDetalhada[" . $itr . "]' value='' type='hidden'   />";
        }
            ?>
    </td>
    <?php } ?>

            <!--  Coluna 6 = VALOR TRP-->
    <?php
    if (!$ocultarCampoTRP) {
        ?>
                <td class="textonormal" align="center">
        <?php
        if (!is_null($materiais[$itr]["trp"])) {

            $material = $materiais[$itr]["codigo"];
            $dataMinimaValidaTrp = prazoValidadeTrp($db, $TipoCompra)->format('Y-m-d');
            $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');
            $exibirMediaTRP = calcularValorTrp($db, $TipoCompra, $material)  > 0;

            if ($exibirMediaTRP) {
                if ($TipoCompra == TIPO_COMPRA_DIRETA) {
                    $Url = "RelTRPConsultarDireta.php?Material=" . $materiais[$itr]["codigo"];
                } else {
                    $Url = "RelTRPConsultar.php?Material=" . $materiais[$itr]["codigo"];
                }
                echo "<a href='javascript:AbreJanela(\"" . $Url . "\",800,500);'>" . $materiais[$itr]["trp"] . "</a>";
                echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='" . $materiais[$itr]["trp"] . "' type='hidden'   />";
            }
            else {
                echo "<nobr>---</nobr>";
            }

        } else {
            echo "<nobr>---</nobr>";
            echo "<input name='MaterialTrp[" . $itr . "]' id='MaterialTrp[" . $itr . "]' value='' type='hidden'   />";
        }
        ?>
                </td>
                <?php
            }
            ?>

            <!--  Coluna 7 =  Quantidade -->
            <td class="textonormal" align="center" width="10">
                <?php
                if (!$ocultarCamposEdicao) {
                    ?>
                    <input
                        name="MaterialQuantidade[<?= $itr ?>]"
                        class="dinheiro4casas"
                        value="<?= $materiais[$itr]["quantidade"] ?>" <?= $ifVisualizacaoThenReadOnly ?>
                        maxlength="16"
                        size="15"
                        id="MaterialQuantidade[<?= $itr ?>]"
                        type="text"
                        onKeyUp="onChangeItemQuantidade('<?= $itr ?>', TIPO_ITEM_MATERIAL);
                                        "
                        />

                        <?php if ($ifVisualizacaoThenReadOnly) {
							$desc = $materiais[$itr]["quantidade"];
							echo "<input type=\"hidden\" name=\"MaterialQuantidade[$itr]\" value=\"$desc\" />";
							}
						?>

                        <?php
                    } else {
                        echo $materiais[$itr]["quantidade"];
                    }
                    ?>
            </td>

            <!--  Coluna 8 =  Valor Estimado -->
            <td class="textonormal" align="center" width="10">
            <?php
            if (!$ocultarCamposEdicao) {
                ?>
                    <input
                        name="MaterialValorEstimado[<?= $itr ?>]"
                        id="MaterialValorEstimado[<?= $itr ?>]" <?= $ifVisualizacaoThenReadOnly ?>
                        size="16"
                        maxlength="16"
                        value="<?= $materiais[$itr]["valorEstimado"] ?>"
                        class="dinheiro4casas"
                        type="text"
                        onKeyUp="onChangeItemValor('<?= $itr ?>', TIPO_ITEM_MATERIAL);
                                        "
                        onBlur=" onChangeValorEstimadoItem('<?= $itr ?>', TIPO_ITEM_MATERIAL)"
                        />

                        <?php if ($ifVisualizacaoThenReadOnly) {
							$desc = $materiais[$itr]["valorEstimado"];
							echo "<input type=\"hidden\" name=\"MaterialValorEstimado[$itr]\" value=\"$desc\" />";
							}
						?>

                    <?php
                } else {
                    echo $materiais[$itr]["valorEstimado"];
                }
                ?>
            </td>

    <?php
    if (!$ocultarCampoExercicio) {
        #condicoes em que campos são desabilitados
        if ($ifVisualizacaoThenReadOnly) {
            $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
        } elseif (moeda2float($materiais[$itr]["quantidade"]) == 1 and ( $QuantidadeMateriais + $QuantidadeServicos) == 1) {
            $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
        } else {
            $ifVisualizacaoQtdeExercicioThenReadOnly = '';
            $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
        }
        ?>

                <!--  Coluna 8 =  Qtd Exercício -->
                <!--  Inibido por Heraldo -->
                <!--  <td class="textonormal" align="center" width="10">  -->
        <?php //if (!$ocultarCamposEdicao) { ?>
                <!--  <input name="MaterialQuantidadeExercicio[<?php //=$itr ?>]" id="MaterialQuantidadeExercicio[<?php //=$itr ?>]" <?php //=$ifVisualizacaoQtdeExercicioThenReadOnly ?> size="16" maxlength="16" value="<?php //=$materiais[$itr]["quantidadeExercicio"] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemQuantidadeExercicio('<?php //=$itr ?>', TIPO_ITEM_MATERIAL); " /> -->
                <!--  <input type="hidden" name="MaterialQuantidadeExercicioValor[<?php //=$itr?>]" id="MaterialQuantidadeExercicioValor[<?php //=$itr?>]" value="<?php //=$materiais[$itr]["quantidadeExercicio"]?>" /> -->
                    <?php
                    // } else {
                    //echo $materiais[$itr]["quantidadeExercicio"];
                    // }
                    ?>
                <!--  </td>  -->

                <!--  Coluna 9 =  Valor do Exercício -->
                <!--  Inibido por Heraldo -->
                <!--  <td class="textonormal" align="center" width="10">  -->
        <?php // if (!$ocultarCamposEdicao) { ?>
                <!--   <input name="MaterialTotalExercicio[<?php //=$itr ?>]" id="MaterialTotalExercicio[<?php //=$itr ?>]"  <?php //=$ifVisualizacaoTotalDemaisExerciciosThenReadOnly ?> size="16" maxlength="16" value="<?php //=$materiais[$itr]["totalExercicio"] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValorExercicio('<?php //=$itr ?>', TIPO_ITEM_MATERIAL); " /> -->
                <!--   <input type="hidden" name="MaterialTotalExercicioValor[<?php //=$itr ?>]" id="MaterialTotalExercicioValor[<?php //=$itr ?>]" value="<?php //=$materiais[$itr]["totalExercicio"] ?>" type="text" /> -->
                        <?php
                        // } else {
                        //echo $materiais[$itr]["totalExercicio"];
                        //}
                        ?>
                <!--  </td>  -->

                <!--  Coluna 10 =  Valor nos Demais Exercícios -->
                <!--  Inibido por Heraldo -->
                <!--   <td class="textonormal" align="right" width="10">  -->
                <!--   <span id="MaterialTotalDemaisExercicios[<?php //=$itr ?>]"><?php //=converte_valor_estoques($TotalDemaisExercicios) ?></span>  -->
                <!--   </td>  -->

        <?php
    }
    ?>
            <?php
            if (!$ocultarCampoFornecedor) {
                ?>
                <td class="textonormal" align="left">
                <?php
                $CnpjStr = FormataCpfCnpj($materiais[$itr]["fornecedor"]);
                if (!$ocultarCamposEdicao) {
                    ?>
                        <input
                            name="MaterialFornecedor[<?= $itr ?>]"
                            id="MaterialFornecedor[<?= $itr ?>]" <?= $ifVisualizacaoThenReadOnly ?> <?= $ifVisualizacaoThenReadOnlyFornecedorItens ?>
                            size="18" maxlength="18" value="<?= $CnpjStr ?>" type="text"
                            onChange="validaFornecedor('MaterialFornecedor[<?= $itr ?>]', 'MaterialFornecedorNome[<?= $itr ?>]',<?= $materiais[$itr]["codigo"] ?>, TIPO_ITEM_MATERIAL);
                                                   AtualizarFornecedorValor('<?= $itr ?>', TIPO_ITEM_MATERIAL);"/>
                        <input name="MaterialFornecedorValor[<?= $itr ?>]" id="MaterialFornecedorValor[<?= $itr ?>]"
                               value="<?= $CnpjStr ?>" type="hidden"/>
            <?php
        } else {
            echo $CnpjStr;
        }
        ?>
                    <br>

                    <div align="left" id="MaterialFornecedorNome[<?= $itr ?>]">
                <?php
                if (!is_null($materiais[$itr]["fornecedor"])) {
                    $CPFCNPJ = removeSimbolos($materiais[$itr]["fornecedor"]);
                    $materialServicoFornecido = $materiais[$itr]["codigo"];
                    $tipoMaterialServico = TIPO_ITEM_MATERIAL;

                    require 'RotDadosFornecedor.php';
                }
                $db = Conexao();
                ?>
                    </div>
                </td>
                <td class="textonormal" align="center" width="10">
                <?php if (!$ocultarCamposEdicao) { ?>
                        <input name="MaterialMarca[<?= $itr ?>]" id="MaterialMarca[<?= $itr ?>]"
                               size="18" <?= $ifVisualizacaoThenReadOnly ?> maxlength="18"
                               value="<?= $materiais[$itr]["marca"] ?>" class="textonormal" type="text">
            <?php
        } else {
            echo $materiais[$itr]["marca"] . "&nbsp;";
        }
        ?>
                </td>
                <td class="textonormal" align="right" width="10">
                <?php if (!$ocultarCamposEdicao) { ?>
                        <input name="MaterialModelo[<?= $itr ?>]" id="MaterialModelo[<?= $itr ?>]"
                               size="18" <?= $ifVisualizacaoThenReadOnly ?> maxlength="18"
                               value="<?= $materiais[$itr]["modelo"] ?>" class="textonormal" type="text">
                    <?php
                } else {
                    echo $materiais[$itr]["modelo"] . "&nbsp;";
                }
                ?>
                </td>
        <?php
    }
    ?>
        <!--  Coluna 9 =  Valor Total -->
		<td class="textonormal" align="right" width="10">
			<?php $valorTotal = converte_valor_estoques($ValorTotalItem); ?>
			<div id="MaterialValorTotal[<?= $itr ?>]"><?= $valorTotal ?></div>

			<?php if ($ifVisualizacaoThenReadOnly) {
				echo "<input type=\"hidden\" name=\"$valorTotal\" />";
			}
			?>

		</td>
</tr>
<?php
}
?>

<?php if ($QuantidadeMateriais <= 0) { ?>
<tr>
    <td class="textonormal itens_material" colspan="<?= ($qtdeColunas - $colunasOcultas) ?>">Nenhum item de material
        informado
    </td>
</tr>
<!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->

<?php } ?>

        <tr>
        <td colspan="0" class="titulo3 itens_material menosum">
            VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL
        </td>
        <td class="textonormal" align="right">
            <div id="MaterialTotal"><?= converte_valor_estoques($ValorTotal) ?></div>
        </td>
        </tr>
    </tbody>
</table>

<?php //----------- Servicos  ?>
<table id="scc_servico" summary="" bgcolor="bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
    <tbody>
        <tr>
        <td colspan="17" class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">
            ITENS DA SOLICITAÇÃO DE SERVIÇO
        </td>
        </tr>
                   <?php
                   $qtdeColunas = 11;
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
            <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" width="<?= $descricaoWidth ?>"/>
            DESCRIÇÃO DO SERVIÇO</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">CÓD.RED. CADUS</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7"/>
            DESCRIÇÃO DETALHADA</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7" width="50px">QUANTIDADE</td>
            <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR ESTIMADO</td>
            <?php if (!$ocultarCampoExercicio) { ?>
                <!--  Inibir colunas - Heraldo - 4/11/2013 -->
                <!--
                    <td class="textoabason" align="center" bgcolor="#DCEDF7"  width="1px" />QUANTIDADE NO EXERÍCIO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR NO EXERCÍCIO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR NOS DEMAIS EXERCÍCIOS</td>
                -->
    <?php } ?>
    <?php if (!$ocultarCampoFornecedor) { ?>
                <td class="textoabason" align="center" bgcolor="#DCEDF7"><img src="../midia/linha.gif" alt="" border="0"
                                                                              height="1px" width="300px"><br>CPF/CNPJ DO
                    FORNECEDOR
                </td>
    <?php } ?>
                <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR TOTAL</td>
        </tr>
        <!-- FIM Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->

<?php
# Serviços do POST-----------------------------------
$Quantidade = count($servicos);
$ValorTotalItem = 0;
$ValorTotal = 0;

for ($itr = 0; $itr < $Quantidade; $itr++) {
    $ValorTotalItem = moeda2float($servicos[$itr]["quantidade"]) * moeda2float($servicos[$itr]["valorEstimado"]);
    $ValorTotal += $ValorTotalItem;
    if (!$ocultarCampoExercicio) {
        $ValorTotalExercicio = moeda2float($servicos[$itr]["quantidadeExercicio"]) * moeda2float($servicos[$itr]["valorEstimado"]);
        $TotalDemaisExercicios = $ValorTotalItem - $ValorTotalExercicio;
        if ($TotalDemaisExercicios < 0) {
            $TotalDemaisExercicios = 0;
        }
    }
    ?>
        <!-- Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
        <tr>

                <!--   Coluna 1 => Ordem   -->
            <td class="textonormal" align="center">
            <?= ($itr + 1) ?>
            </td>

            <!--  Coluna 2 => Descricao -->
            <td class="textonormal">
            <?php if (!$ocultarCamposEdicao) { ?>
                    <input
                        name="ServicoCheck[<?= $itr ?>]" <?php if ($servicos[$itr]["check"]) echo 'checked'; ?> <?= $ifVisualizacaoThenReadOnly ?>
                        type="checkbox">
    <?php } ?>

                <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?= $servicos[$itr]["codigo"] ?>&amp;TipoGrupo=S&amp;ProgramaOrigem=<?= $programa ?>',700,370);">
                    <font color="#000000"><?= $servicos[$itr]["descricao"] ?></font>
                </a>
            </td>

            <!--  Coluna 3 => Código Red -->
            <td class="textonormal" align="center">
    <?= $servicos[$itr]["codigo"] ?>
                <input value="<?= $servicos[$itr]["codigo"] ?>" name="ServicoCod[<?= $itr ?>]" type="hidden">
                <input value="<?php
    //echo ($servicos[$itr]["isObras"]) ? 'true' : 'false';
    echo ($servicos[$itr]["isObras"]) ? 'true' : 'true';
    ?>" name="ServicoIsObras[<?= $itr ?>]" id="ServicoIsObras_<?= $itr ?>" type="hidden"/>
            </td>

            <!--  Coluna 4 => Descrição Detalhada -->
            <td class="textonormal" align="center" width="300px">
    <?php if (!$ocultarCamposEdicao) { ?>
                    <textarea style="text-transform: uppercase;" name="ServicoDescricaoDetalhada[<?= $itr ?>]" <?= $ifVisualizacaoThenReadOnly ?> cols="50"
                              rows="4" OnKeyUp="javascript:CaracteresObservacao(1)"
                              OnBlur="javascript:CaracteresObservacao(0)" OnSelect="javascript:CaracteresObservacao(1)"
                              class="textonormal"><?= $servicos[$itr]["descricaoDetalhada"] ?></textarea>

                              <?php if ($ifVisualizacaoThenReadOnly) {
								  $desc = $servicos[$itr]['descricaoDetalhada'];
								  echo "<input type=\"hidden\" name=\"ServicoDescricaoDetalhada[$itr]\" value=\"$desc\" />";
							  }
							  ?>
                <?php
            } else {
                echo $servicos[$itr]["descricaoDetalhada"];
            }
            ?>
            </td>

            <!--  Coluna 5 => Quantidade -->
            <td class="textonormal" align="center"/>
            <?php if (!$ocultarCamposEdicao) { ?>
                <input class="dinheiro4casas"
                       value="<?= $servicos[$itr]["quantidade"] ?>" <?= $ifVisualizacaoThenReadOnly ?> maxlength="16"
                       size="11" name="ServicoQuantidade[<?= $itr ?>]" id="ServicoQuantidade[<?= $itr ?>]" type="text"
                       onKeyUp="onChangeItemQuantidade('<?= $itr ?>', TIPO_ITEM_SERVICO);
                                       "/>

				<?php if ($ifVisualizacaoThenReadOnly) {
					$desc = $servicos[$itr]["quantidade"];
					echo "<input type=\"hidden\" name=\"ServicoQuantidade[$itr]\" value=\"$desc\" />";
				}
				?>

                <?php
            } else {
                echo $servicos[$itr]["quantidade"];
            }
            ?>
            </td>

            <!--  Coluna 6 => Valor Extimado -->
            <td class="textonormal" align="center" width="10">
			<?php if (!$ocultarCamposEdicao) { ?>
				<input name="ServicoValorEstimado[<?= $itr ?>]"
					   id="ServicoValorEstimado[<?= $itr ?>]" <?= $ifVisualizacaoThenReadOnly ?> size="16"
					   maxlength="16" value="<?= $servicos[$itr]["valorEstimado"] ?>" class="dinheiro4casas"
					   type="text" onKeyUp="onChangeItemValor('<?= $itr ?>', TIPO_ITEM_SERVICO);
									   "
					   onBlur="onChangeValorEstimadoItem('<?= $itr ?>', TIPO_ITEM_SERVICO)"/>

				<?php if ($ifVisualizacaoThenReadOnly) {
					$desc = $servicos[$itr]["valorEstimado"];
					echo "<input type=\"hidden\" name=\"ServicoValorEstimado[$itr]\" value=\"$desc\" />";
				}
				?>

        <?php
    } else {
        echo $servicos[$itr]["valorEstimado"];
    }
    ?>
            </td>

                <?php
                if (!$ocultarCampoExercicio) {
                    #condicoes em que campos são desabilitados
                    if ($ifVisualizacaoThenReadOnly) {
                        $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                        $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                    } elseif (moeda2float($servicos[$itr]["quantidade"]) == 1 and ( $QuantidadeMateriais + $QuantidadeServicos) == 1) {
                        $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                        $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
                    } else {
                        $ifVisualizacaoQtdeExercicioThenReadOnly = '';
                        $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                    }
                    ?>

                <!--  Coluna 8 => Quantidade do Exercício -->
                <!--  <td class="textonormal" align="center" width="10">  -->
        <?php //if (!$ocultarCamposEdicao) { ?>
                <!-- <input name="ServicoQuantidadeExercicio[<?php //=$itr ?>]" id="ServicoQuantidadeExercicio[<?php //=$itr ?>]" <?php //=$ifVisualizacaoQtdeExercicioThenReadOnly ?> size="11" maxlength="16" value="<?php //=$servicos[$itr]["quantidadeExercicio"] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemQuantidadeExercicio('<?php //=$itr ?>', TIPO_ITEM_SERVICO); " /> -->
                <!--    <input type="hidden" name="ServicoQuantidadeExercicioValor[<?php //=$itr ?>]" id="ServicoQuantidadeExercicioValor[<?php //=$itr ?>]" value="<?php //=$servicos[$itr]["quantidadeExercicio"] ?>" /> -->
                <?php
                //} else {
                //echo $servicos[$itr]["quantidadeExercicio"];
                //}
                ?>
                <!--  </td> -->

                <!--  Coluna 9 => Valor no Exercício -->
                <!--  <td class="textonormal" align="center" width="10">  -->
                       <?php //if (!$ocultarCamposEdicao) {?>
                <!-- <input name="ServicoTotalExercicio[<?php //=$itr ?>]" id="ServicoTotalExercicio[<?php //=$itr ?>]"  <?php //=$ifVisualizacaoTotalDemaisExerciciosThenReadOnly ?> size="16" maxlength="16" value="<?php //=$servicos[$itr]["totalExercicio"] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValorExercicio('<?php //=$itr ?>', TIPO_ITEM_SERVICO); " />  -->
                <!--  <input type="hidden" name="ServicoTotalExercicioValor[<?php //=$itr ?>]" id="ServicoTotalExercicioValor[<?php //=$itr ?>]"  value="<?php //=$servicos[$itr]["totalExercicio"] ?>" /> -->
        <?php
        //} else {
        //  echo $servicos[$itr]["totalExercicio"];
        //}
        ?>
                <!--  </td>  -->

                <!--  Coluna 10 => Valor nos Demais Exercícios -->
                <!--  <td class="textonormal" align="right" width="10"> -->
                <!--  <span id="ServicoTotalDemaisExercicios[<?php //=$itr?>]"><?php //=converte_valor_estoques($TotalDemaisExercicios)?></span> -->
                <!--  </td> -->

                       <?php } ?>
    <?php
    if (!$ocultarCampoFornecedor) {
        ?>
                <td class="textonormal" align="left" width="10%">
        <?php
        $CnpjStr = FormataCpfCnpj($servicos[$itr]["fornecedor"]);

        if (!$ocultarCamposEdicao) {
            ?>
                        <input
                            name="ServicoFornecedor[<?= $itr ?>]"
                            id="ServicoFornecedor[<?= $itr ?>]"
                    <?= $ifVisualizacaoThenReadOnly ?>
                    <?= $ifVisualizacaoThenReadOnlyFornecedorItens ?>
                            size="18"
                            maxlength="18"
                            value="<?= $CnpjStr ?>"
                            type="text"
                            onChange="validaFornecedor('ServicoFornecedor[<?= $itr ?>]', 'ServicoFornecedorNome[<?= $itr ?>]',<?= $servicos[$itr]["codigo"] ?>, TIPO_ITEM_SERVICO);
                                                AtualizarFornecedorValor('<?= $itr ?>', TIPO_ITEM_SERVICO);"
                            />
                        <input
                            name="ServicoFornecedorValor[<?= $itr ?>]"
                            id="ServicoFornecedorValor[<?= $itr ?>]"
                            value="<?= $CnpjStr ?>"
                            type="hidden"
                            />
                    <?php
                } else {
                    echo $CnpjStr;
                }
                ?>
                    <br>

                    <div align="left" id='ServicoFornecedorNome[<?= $itr ?>]'/>
                <?php
                if (!is_null($servicos[$itr]["fornecedor"])) {
                    $CPFCNPJ = removeSimbolos($servicos[$itr]["fornecedor"]);
                    $materialServicoFornecido = $servicos[$itr]["codigo"];
                    $tipoMaterialServico = TIPO_ITEM_SERVICO;
                    require 'RotDadosFornecedor.php';
                }
                $db = Conexao();
                ?>
                    </div>
                </td>
    <?php } ?>
                <!--  Coluna 7 => Valor Total -->
            <td class="textonormal" align="right" width="10">
				<?php $valorTotal = converte_valor_estoques($ValorTotalItem); ?>
                <div id="ServicoValorTotal[<?= $itr ?>]"><?= $valorTotal ?></div>

                <?php if ($ifVisualizacaoThenReadOnly) {
					echo "<input type=\"hidden\" name=\"$valorTotal\" />";
				}
				?>
            </td>

        </tr>
<?php } ?>
        <?php
        if ($Quantidade <= 0) {
            ?>
            <tr>
            <td class="textonormal itens_servico" colspan="<?= ($qtdeColunas - $colunasOcultas + 1) ?>">Nenhum item de serviço
                informado
            </td>
            </tr>
                <?php
            }
            ?>
         <!-- FIM Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
        <tr>
        <td class="titulo3 itens_servico menosum_servico" colspan="">
            VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO
        </td>
        <td class="textonormal"  align="right">
            <div id="ServicoTotal"><?= converte_valor_estoques($ValorTotal) ?></div>
        </td>
        </tr>
                <?php
                if (!$ocultarCamposEdicaoLicitacao) {
                    ?>
            <tr>
            <td class="textonormal" colspan="<?= ($qtdeColunas - $colunasOcultas) ?>" align="center">
                <input name="IncluirItem" value="Incluir Item" class="botao"
                       onclick="javascript:AbreJanelaItem('../estoques/CadIncluirItem.php?ProgramaOrigem=CadSolicitacaoCompraIncluirManterExcluir&amp;PesqApenas=C', 700, 350);"
                       type="button">
                <input name="RetirarItem" value="Retirar Item" class="botao" onclick="javascript:enviar('Retirar');"
                       type="button">
            </td>
            </tr>
    <?php
}
?>

    </tbody>
</table>
</td>
                <?php #--------------------------Fim Itens ?>
</tr>
<tr>
<td class="textonormal" colspan="4">
    <table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%"
           summary="">
        <tr>
        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
            ANEXAÇÃO DE DOCUMENTO(S)
        </td>
    </tr>
            <?php
            if (!$ocultarCamposEdicao) {
                ?>

        <tr>
        <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%" valign="top">Anexação de Documentos</td>
        <td class="textonormal">
            <table border="0" width="100%" summary="">
                <tr>
                <td>
                    <input type="file" name="Documentacao" class="textonormal"/>
                </td>
                </tr>
            </table>
        </td>
    </tr>
            <?php
        }
        ?>
<?php
$DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

if ($DTotal == 0) {
    ?>
    <tr>
    <td class="textonormal" colspan='2'>Nenhum documento informado</td>
    </tr>

            <?php
        }

        for ($Dcont = 0; $Dcont < $DTotal; $Dcont++) {
            if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente'
            ) {
                echo "<tr>";
                if (!$ocultarCamposEdicao) {
                    echo "<td align='right' ><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
                }
                echo "<td class='textonormal' >";
                if (!$ocultarCamposEdicao) {
                    echo $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                } else {
                    $arquivo = 'compras/' . $_SESSION['Arquivos_Upload']['nome'][$Dcont];
                    addArquivoAcesso($arquivo);

                    echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload']['nome'][$Dcont] . "</a>";
                }
                echo "</td></tr>";
            }
        }
        ?>
<?php
if (!$ocultarCamposEdicao) {
    ?>
    <tr>
    <td class="textonormal" colspan="7" align="center">
        <input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao"
               onclick="javascript:enviar('Incluir_Documento');">
        <input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao"
               onClick="javascript:enviar('Retirar_Documento');">
    </td>
    </tr>
        <?php
    }
    ?>
</table>
</td>
</tr>

<tbody>
    <!-- HISTORICO DE SCC -->
<?php
if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    ?>

        <tr>
        <td class="textonormal" colspan="4">
            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                <tbody>
                    <tr>
                    <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">
                        HISTÓRICO DA SITUAÇÃO DA SCC
                    </td>
                    </tr>
                    <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">SITUAÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">DATA</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">RESPONSÁVEL</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">TELEFONE</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="5%">EMAIL</td>
                    </tr>
    <?php
    $sql = "
            SELECT ss.esitsonome, hss.thsitsdata, u.eusuporesp, u.eusupomail, u.ausupofone, hss.csitsocodi
          FROM SFPC.TBhistsituacaosolicitacao hss, SFPC.TBsituacaosolicitacao ss, SFPC.TBusuarioportal u
             WHERE hss.csitsocodi = ss.csitsocodi
                 and hss.cusupocodi = u.cusupocodi
                 and csolcosequ = $Solicitacao
                ORDER BY hss.thsitsdata DESC
        ";

    $res = executarSQL($db, $sql);
    while ($linha = $res->fetchRow()) {
        $nomeSituacao = $linha[0];

        //---------------------------
        // Se situação = Licitação
        //---------------------------
        if ($linha[5] == 9) {
            $vetor = getChaveLicitacao($Solicitacao, $db);
            $descComissao = getDescComissao($vetor[3], $db);
            if ($vetor[1] == "999") {
                $nomeSituacao .= $descComissao;
            } else {
                $nomeSituacao .= " - PL " . $vetor[0] . "/" . $vetor[1] . " - " . $descComissao;
            }
        }

        //---------------------------
        // Se situação = Encaminhada
        //---------------------------
        if ($linha[5] == 8) {
            $row = getDadosSolicitacao($Solicitacao, $db);
            $descComissao = getDescComissao($row->comissao, $db);
            $nomeSituacao .= " - " . $descComissao;
        }

        $dataSituacao = DataBarra($linha[1]) . " " . hora($linha[1]);
        $nomeUsuario = $linha[2];
        $emailUsuario = $linha[3];
        $foneUsuario = $linha[4];
        ?>

                        <tr style="text-align:center">
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            width="30%"><?= $nomeSituacao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            width="30%"><?= $dataSituacao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            width="30%"><?= $nomeUsuario ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            width="30%"><?= $foneUsuario ?>&nbsp;</td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            width="30%"><?= $emailUsuario ?>&nbsp;</td>
                        </tr>
            <?php
        }
        ?>
                </tbody>
            </table>

            <!-- Inserir aqui itens pre-solicitacao de empnho(HERALDO BOTELHO)                        -->

        <tr>
        <td class="textonormal" colspan="4">

            <table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                <tbody>
                    <tr>
                    <td class="titulo3" colspan="7" align="center" bgcolor="#75ADE6" valign="middle">
                        PRÉ-SOLICITAÇÃO DE EMPENHO (PSE)
                    </td>

                    </tr>
                    <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">NÚMERO/ANO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">DATA/HORA GERAÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">NÚMERO BLOQUEIO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">FORNECEDOR</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">SITUAÇÃO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">DATA SITUAÇÃO</td>
                    </tr>
                    <?php
                    $sql = " SELECT ";
                    $sql .= " a.CPRESOSEQU as numero, ";
                    $sql .= " a.APRESOANOE as ano, ";
                    $sql .= " to_char(a.TPRESOGERA,'DD/MM/YYYY HH:MI') as datahora,";
                    $sql .= " a.APRESONBLOQ as bloqueio, ";
                    $sql .= " a.APRESOANOB as anobloqueio,";
                    $sql .= " c.aforcrccgc as cgc, ";
                    $sql .= " c.aforcrccpf as cpf, ";
                    $sql .= " c.nforcrrazs as razao, ";
                    $sql .= " a.CMOTNICODI as idmotivo, ";
                    $sql .= " d.emotnidesc as descricao, ";
                    $sql .= " a.apresonues as numeroemp, ";
                    $sql .= " a.apresonues as anoemp, ";
                    $sql .= " to_char(a.TPRESOULAT,'DD/MM/YYYY') as datault,";
                    $sql .= " to_char(a.TPRESOIMPO,'DD/MM/YYYY') as dataimportacao,";
                    $sql .= " to_char(a.DPRESOCSEM,'DD/MM/YYYY') as datacancel,";
                    $sql .= " to_char(a.DPRESOGERE,'DD/MM/YYYY') as datageracao,";
                    $sql .= " a.APRESONUES as numemp,";
                    $sql .= " a.APRESOANES as anoemp,";
                    $sql .= " to_char(a.DPRESOANUE,'DD/MM/YYYY') as dataanulacao,";
                    $sql .= " a.VPRESOANUE as valoranulado, ";
                    $sql .= " sum(b.VIPRESEMPN) as soma ";
                    $sql .= " FROM ";
                    $sql .= " sfpc.tbpresolicitacaoempenho a ";
                    $sql .= " LEFT JOIN sfpc.tbfornecedorcredenciado c ON c.aforcrsequ  = a.aforcrsequ";
                    $sql .= " LEFT JOIN sfpc.TBITEMPRESOLICITACAOEMPENHO b ON (a.CPRESOSEQU  = b.CPRESOSEQU and  a.APRESOANOE=b.APRESOANOE )";
                    $sql .= " LEFT JOIN sfpc.tbmotivonaoimportacao d ON d.cmotnicodi = a.cmotnicodi";
                    $sql .= " WHERE ";
                    // $sql .= " a.CSOLCOSEQU=110 and "; // só pra testar (inibir a de baixo)
                    $sql .= " a.CSOLCOSEQU=$Solicitacao and ";
                    $sql .= "   a.CPRESOSEQU = b.CPRESOSEQU ";
                    $sql .= " and a.APRESOANOE = b.APRESOANOE ";
                    $sql .= " and a.aforcrsequ = c.aforcrsequ ";
                    $sql .= " group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20  ";

                    $result = executarTransacao($db, $sql);
                    // $numRows = $result->numRows(); // está falhando aqui

                    $contAux = 0;
                    while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {

                        $contAux = contAux + 1;

                        //--------Formatar bloqueio
                        if (!empty($row->anobloqueio) && !empty($row->bloqueio))
                            $vetor = getDadosBloqueioFromChave($dbOracle, $row->anobloqueio, $row->bloqueio);
                        $blqFormato = $vetor['bloqueio'];

                        //-------Formatar cpf/cgc de fornecedor
                        if (!empty($row->cpf))
                            $cpfcgc = $row->cpf;
                        else
                            $cpfcgc = $row->cgc;

                        $cpfcgcAux = FormataCpfCnpj($cpfcgc);

                        //-------Formatar soma
                        $soma = number_format($row->soma, 4, ",", ".");

                        //-------Formatar mensagens da situacao e datas
                        if (!empty($row->idmotivo)) {
                            $descSituacao = "PSE RECUSADA POR MOTIVO DE " . $row->descricao;
                            $dataMotivo = $row->datault;
                        }

                        if (!empty($row->dataimportacao)) {
                            $descSituacao = "SE GERADA";
                            $dataMotivo = $row->datault;
                        }

                        if (!empty($row->datacancel)) {
                            $descSituacao = "SE CANCELADA";
                            $dataMotivo = $row->datacancel;
                        }

                        if (!empty($row->datageracao)) {
                            $descSituacao = "EMPENHADO (NÚMERO=" . $row->numemp . "/" . $row->anoemp . ")";
                            $dataMotivo = $row->datageracao;
                        }

                        if (!empty($row->dataanulacao)) {
                            $descSituacao = "EMPENHO ANULADO (VALOR=" . number_format($row->valoranulado, 4, ",", ".") . ")";
                            $dataMotivo = $row->dataanulacao;
                        }
                        ?>

                        <tr style="text-align:left">
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $row->numero . "/" . $row->ano ?> </td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $row->datahora ?> </td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $blqFormato ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $soma ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $cpfcgcAux . " " . $row->razao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $descSituacao ?></td>
                        <td class="textonormal" bgcolor="#DCEDF7" height="20" valign="top"
                            align="center"><?php echo $dataMotivo ?></td>
                        </tr>
                        <?php
                    }

                    if ($contAux == 0) {
                        ?>
                        <tr style="text-align:left">
                        <td class="textonormal" bgcolor="#DCEDF7" colspan=7 height="20" valign="top" align="left">
                            Nenhum item de pré-solicitação informado
                        </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            </table>
        </td>
    </tr>
    <tr>
    <td align="right">
        <input type="button" name="Imprimir" value="Imprimir" class="botao"
               onClick="javascript:enviar('Imprimir');"/>
        <!--<input type="hidden" name="Botao" value=""> -->
                    <?php
                    if ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
                        ?>
            <input type="submit" name="Voltar" value="Voltar" class="botao"
                   onClick="javascript:onButtonVoltar();">
                        <?php
                    }
                    ?>
    </td>
    </tr>
    </table>

    </td>
    </tr>

    <!--  final heraldo botelho -->

                    <?php
                }
                ?>
<!-- FIM DE HISTORICO DE SCC -->

</tbody>
</table>
<tr>
<td class="textonormal" align="right">
    <input type="hidden" name="InicioPrograma" value="1">
    <input type="hidden" name="RetirarDocs" value="<?= $RetirarDocs ?>">
    <input type="hidden" name="Solicitacao" value="<?= $Solicitacao ?>">
    <input type="hidden" name="Botao" value="">
    <input type="hidden" name="Foco" value="">
    <input type="hidden" name="SeqSolicitacao" value="<?= $Solicitacao ?>">
    <input type="hidden" name="isDotacaoAnterior" value="<?= $isDotacao ?>">
	<input type="hidden" name="GeraContrato" value="<?= $GeraContrato ?>" />

                <?php
                if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
                    ?>
        <input type="button" name="Rascunho" value="Salvar Rascunho" class="botao"
               onClick="javascript:enviar('Rascunho');">
        <input type="button" name="Incluir" value="Incluir Solicitação" class="botao"
               onClick="javascript:onButtonIncluir();">
    <?php
} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
    if ($situacaoSolicitacaoAtual == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) {
        ?>
            <input type="button" name="Rascunho" value="Manter Rascunho" class="botao"
                   onClick="javascript:enviar('ManterRascunho');">
        <?php
    }
    ?>
        <input type="button" name="Manter" value="Manter Solicitação" class="botao"
               onClick="javascript:onButtonManter();">
    <?php
} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
    ?>
        <input type="button" name="Excluir" value="Cancelar Solicitação" class="botao"
               onClick="javascript:enviar('Excluir');">
                    <?php
                }
                ?>
                <?php if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_MANTER) { ?>
        <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:onButtonVoltar();">
<?php } ?>

    <!--  ?php if ($acaoPagina != ACAO_PAGINA_INCLUIR) { ?>
                                        <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:history.back(1);">
     } ?-->

</td>
</tr>
</table>

</form>
<script language="javascript" type="">
    qtdeMateriais        = <?= count($materiais) ?>;
    qtdeServicos         = <?= count($servicos) ?>;
    campoExercicioExiste = <?= ($ocultarCampoExercicio) ? 'false' : 'true'; ?>;

    // ITENS DA SOLICITAÇÃO DE MATERIAL colspan
    $('td.itens_material').attr('colspan', contador('head_principal'));
    $('td.menosum').attr('colspan', contador('head_principal')-1);
    // ITENS DA SOLICITAÇÃO DE SERVIÇO colspan
    $('td.itens_servico').attr('colspan', contador('head_principal_servico'));
    $('td.menosum_servico').attr('colspan', contador('head_principal_servico')-1);

    var formulario = document.CadSolicitacaoCompraIncluirManterExcluir;

    <?php
    if (!is_null($Foco) and $Foco != "") {
        ?>
        document.CadSolicitacaoCompraIncluirManterExcluir.<?= $Foco ?>.focus();
        <?php
    }
    ?>
           <?php
           if ($isDotacao) {
               //echo "passou";
               //exit;
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

echo $JSCriacaoLimiteCompra; //imprime JS que gera todos valores de limite
?>

</script>

<?php
# Pegando output gerado fora do template e incluindo na posição correta no template

$outputNaoTratratado = ob_get_contents();
ob_clean();
$template->FINAL = $outputNaoTratratado;

$template->show();
