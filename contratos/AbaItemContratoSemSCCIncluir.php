<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaItemContratoSemSCCIncluir.php
# Autor:    Edson Dionisio
# Data:     24/07/2020
# Objetivo: Programa de incluir contrato Antigo $_SESSION['dadosSalvar']["origemScc"]
#-------------------------------------------------------------------------

    require_once "./ClassContratos.php";
    
    # Acesso ao arquivo de funções #
    require_once '../compras/funcoesCompras.php';
    // require_once '../compras/RotDadosFornecedor.php';
    //00329696000102

    session_start();
  
    # Adiciona páginas no MenuAcesso #

    $_SESSION['telaAppView'] = !(empty($telaAppView))?$telaAppView:false;
    # Adiciona páginas no MenuAcesso #
    AddMenuAcesso ('/estoques/CadIncluirItem.php');
    AddMenuAcesso ('/estoques/CadItemDetalhe.php');
    AddMenuAcesso ('/estoques/CadIncluirCentroCusto.php');
    AddMenuAcesso ('..compras/RotDadosFornecedor.php');
    AddMenuAcesso ('/compras/ConsProcessoPesquisar.php');
    AddMenuAcesso ('/compras/RelAcompanhamentoSCCPdf.php');
    AddMenuAcesso ('/compras/RelTRPConsultar.php');
    AddMenuAcesso ('/compras/RelTRPConsultarDireta.php');
    AddMenuAcesso ('/compras/InfPreenchimentoBloqueios.php');
    AddMenuAcesso ('/registropreco/CadIncluirIntencaoRegistroPreco.php');
    AddMenuAcesso ('/registropreco/CadVisualizarIntencaoRegistroPreco.php');
    
    function tipo_material() {
        define("TIPO_ITEM_MATERIAL", 1);
    }

    function tipo_servico() {
        define("TIPO_ITEM_SERVICO", 2);
    }
      
      //Cria a constante declarada na função
      tipo_material();
      tipo_servico();
      
      //Exibe o conteúdo da constante
      echo(TIPO_ITEM_MATERIAL);
      echo(TIPO_ITEM_SERVICO);

function ExibeAbaItemContratoIncluir(){
        $ObjContrato = new Contrato();
        $db       = Conexao();


    global $programaSelecao, $programa, $acaoPagina;
    // Volta para o programa de origem
    if (is_null($programaSelecao)) {
        AddMenuAcesso('/compras/' . $programaSelecao);
    } else {
        if ($programa == 'CadLicitacaoIncluir.php') {} else {
            AddMenuAcesso('compras/' . $programa);
        }
        if ($programa == 'JanelaLicitacaoIncluir.php') {} else {
            AddMenuAcesso('compras/' . $programa);
        }
    }

    class CR92 {
        public static function retornarItensMateriasAtaSarp() {
            $itens = array();

            if ($_SESSION['materialSarp'] != null) {
                $itens = $_SESSION['materialSarp'];
                unset($_SESSION['materialSarp']);
            }
            return $itens;
        }

        public static function retornarItensServicoAtaSarp() {
            $itens = array();

            if ($_SESSION['servicoSarp'] != null) {
                $itens = $_SESSION['servicoSarp'];
                unset($_SESSION['servicoSarp']);
            }
            return $itens;
        }

        private function sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial) {
            $sql = 'select sum(coei.acoeitqtat) as qtdTotalOrgao from sfpc.tbcaronaorgaoexterno coe';
            ' left outer join sfpc.tbcaronaorgaoexternoitem coei';
            ' on coe.ccaroesequ = coei.ccaroesequ';
            ' left outer join sfpc.tbitemataregistropreconova iarpn';
            ' on iarpn.carpnosequ = coe.carpnosequ';
            ' and iarpn.citarpsequ = coei.citarpsequ';
            ' where coe.carpnosequ =' . $ata;
            if ($isMaterial) {
                $sql = ' and iarpn.cmatepsequ =' . $item;
            } else {
                $sql = ' and iarpn.cservpsequ =' . $item;
            }
            return $sql;
        }

        private function sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial) {
            $sql = 'select sum(itp.apiarpqtat) as qtdTDSoli from sfpc.tbsolicitacaocompra s';
            $sql .= ' left outer join sfpc.tbitemsolicitacaocompra i';
            $sql .= ' on i.csolcosequ = s.csolcosequ';
            $sql .= ' left outer join sfpc.tbparticipanteatarp p';
            $sql .= ' on s.carpnosequ = p.carpnosequ';
            $sql .= ' left outer join sfpc.tbparticipanteitematarp itp';
            $sql .= ' on itp.carpnosequ = s.carpnosequ ';
            $sql .= ' and itp.carpnosequ = p.carpnosequ';
            $sql .= ' where s.carpnosequ =' . $ata;

            if (! empty($orgao)) {
                $sql .= ' and p.corglicodi =' . $orgao;
            }

            if ($isMaterial) {
                $sql .= ' and i.cmatepsequ =' . $item;
            } else {
                $sql .= ' and i.cservpsequ =' . $item;
            }
            return $sql;
        }

        private function sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial) {
            $sql = 'select sum(iarp.aitarpqtor) as qtdMaxAta from sfpc.tbparticipanteatarp p';
            $sql .= ' left outer join sfpc.tbitemataregistropreconova iarp';
            $sql .= ' on iarp.carpnosequ = p.carpnosequ';
            $sql .= ' where p.carpnosequ =' . $ata;
            $sql .= ' and p.corglicodi =' . $orgao;
            
            if ($isMaterial) {
                $sql .= ' and iarp.cmatepsequ =' . $item;
            } else {
                $sql .= ' and iarp.cservpsequ =' . $item;
            }
            return $sql;
        }

        /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
        public static function validarCondicaoSARPCarona($orgao, $ata, $item, $isMaterial, $quantidadeInformada) {
            // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlvalidarCondicaoSARPParticpante(null, $ata, $item, $isMaterial));
            //
            // $resultado->fetchInto($quantidadeSolicitadaSemOrgao, DB_FETCHMODE_OBJECT);
            //
            // $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), self::sqlQuantidadeItemAtaCarona($ata, $item, $isMaterial));
            //
            // $resultado->fetchInto($quantidadeSolicitadaCarona, DB_FETCHMODE_OBJECT);
            //
            // if ($quantidadeSolicitadaSemOrgao > $quantidadeInformada) {
            // return false;
            // }
            //
            // if ($quantidadeSolicitadaCarona > (5 * $quantidadeSolicitadaSemOrgao)) {
            // return false;
            // }
            return true;
        }

        /* Validação Retirada para entrega sem registro de preço, favor recolocar quando o ato de entregar */
        public static function validarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial, $quantidadeInformada) {
            // $dao = Conexao();
            //
            // $resultado = executarSQL($dao, self::sqlvalidarCondicaoSARPParticpante($orgao, $ata, $item, $isMaterial));
            //
            // $resultado->fetchInto($quantidadeSolicitada, DB_FETCHMODE_OBJECT);
            //
            // $resultado = executarSQL($dao, self::sqlQuantidadeMaxAtaParticipante($orgao, $ata, $item, $isMaterial));
            //
            // $resultado->fetchInto($quantidadeTotalItem, DB_FETCHMODE_OBJECT);
            //
            // $qtdSolicitada = $quantidadeSolicitada->qtdTDSoli;
            // $qtdMaxAtaItem = $quantidadeTotalItem->qtdMaxAta;
            //
            // if ($qtdMaxAtaItem < ($qtdSolicitada + $quantidadeInformada)) {
            // return false;
            // }
            return true;
        }

        public static function sqlVerificarCarpnosequ($Solicitacao) {
            $sql  = " SELECT sc.carpnosequ, sc.ctpcomcodi ";
            $sql .= " FROM SFPC.TBsolicitacaocompra sc ";
            $sql .= " WHERE sc.csolcosequ = " . $Solicitacao ;
            $sql .= " AND sc.csitsocodi in (1,5) ";
            $sql .= " AND sc.ctpcomcodi = 5 ";

            return $sql;
        }
    }

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgula da mensagem de erro por "e"
            // Recebendo variáveis via POST
        //    var_dump($_POST);            
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $Botao                       = $_POST['Botao'];
    $InicioPrograma              = $_POST['InicioPrograma'];
    $CentroCusto                 = $_POST['CentroCusto'];
    $sequencialIntencao          = $_POST['sequencialIntencao'];
 
    $CnpjFornecedor              = $_POST['CnpjFornecedor'];
    $GeraContrato                = $_POST['GeraContrato'];
    $TipoCompra                  = $_POST['TipoCompra'];
    $NumProcessoSARP             = $_POST['NumProcessoSARP'];
    $AnoProcessoSARP             = $_POST['AnoProcessoSARP'];
    $ComissaoCodigoSARP          = $_POST['ComissaoCodigoSARP'];
    $OrgaoLicitanteCodigoSARP    = $_POST['OrgaoLicitanteCodigoSARP'];
    $GrupoEmpresaCodigoSARP      = $_POST['GrupoEmpresaCodigoSARP'];
    $CarregaProcessoSARP         = $_POST['CarregaProcessoSARP'];

    $MaterialCheck               = $_POST['MaterialCheck'];
    $MaterialCod                 = $_POST['MaterialCod'];
    $MaterialQuantidade          = $_POST['MaterialQuantidade'];
    $MaterialValorEstimado       = $_POST['MaterialValorEstimado'];
    
    $ServicoCheck                = $_POST['ServicoCheck'];
    $ServicoCod                  = $_POST['ServicoCod'];
    $ServicoQuantidade           = $_POST['ServicoQuantidade'];
    $ServicoDescricaoDetalhada   = $_POST['ServicoDescricaoDetalhada'];
    $ServicoQuantidadeExercicio  = $_POST['ServicoQuantidadeExercicioValor'];
    $ServicoValorEstimado        = $_POST['ServicoValorEstimado'];

    // Testes nesse ponto

    if(!empty($_POST['codigo_mat'])){
    
        $_SESSION['codigo_material'] = $_POST['codigo_mat'];
        
        $codigo_material = explode('|', $_SESSION['codigo_material']);
        $MaterialCod = $codigo_material;
    }

    if(!empty($_POST['qtd_mat'])){
        $_SESSION['qtd_material'] = $_POST['qtd_mat'];

        $qtd_material = explode('|', $_SESSION['qtd_material']);
        //$qtd_material = $_SESSION['qtd_material'];
        $MaterialQuantidade = $qtd_material;
    }

    if(!empty($_POST['valor_mat'])){
        $_SESSION['material_valor_estimado'] = $_POST['valor_mat'];

        $material_valor_estimado = explode('|', $_SESSION['material_valor_estimado']);
    //    $material_valor_estimado = $_SESSION['material_valor_estimado'];
        $MaterialValorEstimado = $material_valor_estimado; 
    }

    if(!empty($_POST['codigo_servico'])){
        $_SESSION['servico_cod'] = $_POST['codigo_servico'];
        // $servico_cod = $_SESSION['servico_cod'];
        
        $servico_cod = explode('|', $_SESSION['servico_cod']);
        $ServicoCod = $servico_cod;
    }

    if(!empty($_POST['qtd_servico'])){
        $_SESSION['servico_qtd'] = $_POST['qtd_servico'];

        $servico_qtd = explode('|', $_SESSION['servico_qtd']);

        $ServicoQuantidade = $servico_qtd;
    }

    if(!empty($_POST['valor_estimado_servico'])){
        $_SESSION['servico_valor_estimado'] = $_POST['valor_estimado_servico'];

        $servico_valor_estimado = explode('|', $_SESSION['servico_valor_estimado']);

        $ServicoValorEstimado = $servico_valor_estimado;
    }

   // if(!empty($_POST['descricao_detalhada_servico'])){
   //     $_SESSION['servico_descricao_detalhada'] = $_POST['descricao_detalhada_servico'];
   //     $servico_descricao_detalhada = explode('|', $_SESSION['servico_descricao_detalhada']);

   //     $ServicoDescricaoDetalhada = $servico_descricao_detalhada;
   // }


    ////////////////////////////////////////////////////////////////////////////////////////////


   

    $_SESSION['MATERIAIS'];
    $_SESSION['SERVICOS'];
  
    $Solicitacao = $_POST['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'  
 
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Solicitacao = $_GET['SeqSolicitacao'];

    unset($_SESSION['codigo_material']);
    unset($_SESSION['qtd_material']);
    unset($_SESSION['material_valor_estimado']);
    unset($_SESSION['servico_cod']);
    unset($_SESSION['qtd_servico']);
    unset($_SESSION['valor_estimado_servico']);
    //unset($_SESSION['descricao_detalhada_servico']);

    unset($_SESSION['MATERIAIS']);
    unset($_SESSION['SERVICOS']);

    if (is_null($Solicitacao)) {
        unset($_SESSION['Arquivos_Upload']); // inicio de uma inclusão. excluir arquivos na sessão
    }
}

$dbOracle = ConexaoOracle();

$sql = 'SELECT  QPARGETMAOBJETO, QPARGETMAJUSTIFICATIVA, QPARGEDESCSE, EPARGESUBELEMESPEC, QPARGEQMAC, QPARGEQMAC,
                EPARGETDOV
        FROM    SFPC.TBPARAMETROSGERAIS ';

$linha = resultLinhaUnica(executarSQL($db, $sql));

if (is_null($linha)) {
    echo '<br/><br/><br/><br/><br/><br/>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo "<b>Falha do sistema, pois os Parâmetros Gerais precisam ser preenchidos. Vá em em 'Tabelas > Parâmetros Gerais' e preencha os campos.</b>";
}

$tamanhoDescricaoServico = strlen($linha[2]);
$subElementosEspeciais   = explode(',', $linha[3]);
$cargaBloqueiosManter    = false; // informa se é primeiro carregamento e existem bloqueios, para serem carregados para o javascript

// Verificar se é SARP e se tem carpnosequ e redireciona para tela anterior
if ((!empty($Solicitacao) && $acaoPagina == ACAO_PAGINA_MANTER && $_SESSION['_fperficorp_'] != 'S') && false) {
    $sql   = CR92::sqlVerificarCarpnosequ($Solicitacao);
    $linha = resultLinhaUnica(executarSQL($db, $sql));
    
    if (empty($linha[0]) && $linha[1] == TIPO_COMPRA_SARP) {
        $Botao = 'Voltar';
        $_SESSION['mensagemSarp'] = "Esta Solicitação de Compra e Contratação de Material e Serviço (SCC) não poderá ser alterada, pois antecede a criação do Módulo de Registro de Preços. Proceda a inclusão de uma nova SCC do tipo SARP com os mesmos dados";
    }
}

if ($Botao == 'Voltar') {
    $_SESSION['carregarSelecionarDoSession'] = true;
    
    if ($programa == 'CadLicitacaoIncluir.php') {
        $programaSelecao = $programa;
    } else {

        if(isset($pesquisa) && (($_GET['origemTramitacao']==1) || ($_GET['origemTramitacao']==2)) ){
            $programaSelecao = $urlTramitacao;
            unset($_SESSION['origemPesquisa']);
        }else{
            if (is_null($programaSelecao)) {
                $programaSelecao = '../licitacoes/CadLicitacaoIncluir.php';
            }
        }
    }
    header('Location: ' . $programaSelecao);
    exit();
}

// recuperando dados da SCC (acompanhamento, manter, excluir)
if (($acaoPagina == ACAO_PAGINA_MANTER and is_null($CentroCusto)) or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    if (is_null($Solicitacao)) { // solicitação nao foi informada. voltar para seleção de solicitacao
        header('Location: ' . $programaSelecao);
        exit();
    } else {
        $cargaBloqueiosManter = true;

        // recuperando dados da SCC informada
        $sql = "SELECT  CCENPOSEQU, ESOLCOOBSE, ESOLCOOBJE, ESOLCOJUST, ASOLCOANOS,
                        CTPCOMCODI, TSOLCODATA, CLICPOPROC, ALICPOANOP, CCOMLICODI,
                        CORGLICOD1, CGREMPCODI, DSOLCODPDO, CTPLEITIPO, CLEIPONUME,
                        CARTPOARTI, CINCPAINCI, FSOLCORGPR, FSOLCORPCP, FSOLCOCONT,
                        CSOLCOTIPCOSEQU, CINTRPSEQU, CINTRPSANO
                FROM    SFPC.TBSOLICITACAOCOMPRA
                WHERE   CSOLCOSEQU = $Solicitacao "; // here

        $linha = resultLinhaUnica(executarSQL($db, $sql));
        
        $cargaInicial = true;
        
        $CentroCusto              = $linha[0];
        $TipoCompra               = $linha[5];
        $DataHora                 = $linha[6];
        $DataSolicitacao          = DataBarra($DataHora);
        $NumProcessoSARP          = $linha[7];
        $AnoProcessoSARP          = $linha[8];
        $ComissaoCodigoSARP       = $linha[9];
        $OrgaoLicitanteCodigoSARP = $linha[10];
        $GrupoEmpresaCodigoSARP   = $linha[11];
        $DataDom                  = DataBarra($linha[12]);
        $TipoLei                  = $linha[13];
        $Lei                      = $linha[14];
        $Artigo                   = $linha[15];
        $Inciso                   = $linha[16];
        $RegistroPreco            = $linha[17];
        $Sarp                     = $linha[18];
        $GeraContrato             = $linha[19];
        $SarpTipo                 = $Sarp;
        $CarregaProcessoSARP      = 1;

        $sql = "SELECT  SC.CMATEPSEQU, SC.CSERVPSEQU, SC.EITESCDESCSE, SC.AITESCQTSO, SC.VITESCUNIT,
                        SC.VITESCVEXE, SC.AITESCQTEX, SC.EITESCMARC, SC.EITESCMODE, SC.CUSUPOCODI,
                        SC.AFORCRSEQU, SC.CITESCSEQU, F.AFORCRCCPF, F.AFORCRCCGC, SC.EITESCDESCMAT
                FROM    SFPC.TBITEMSOLICITACAOCOMPRA SC
                        LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO F ON F.AFORCRSEQU = SC.AFORCRSEQU
                WHERE   SC.CSOLCOSEQU = $Solicitacao
                ORDER BY SC.AITESCORDE ";

        $res = executarSQL($db, $sql);
        
        $cntMaterial        = - 1;
        $cntServico         = - 1;
        $tipoItem           = null;

        // para cada item de solicitação
        
        while ($linha = $res->fetchRow()) {
            $codigoItem = $linha[11];
            
            if (! is_null($linha[12])) {
                $fornecedorItem = $linha[12]; // CPF
            } else {
                $fornecedorItem = $linha[13]; // CNPJ
            }
            
            if (! is_null($linha[0])) {
                ++ $cntMaterial;
                $MaterialCheck[$cntMaterial]               = false;
                $MaterialCod[$cntMaterial]                 = $linha[0];
                $MaterialQuantidade[$cntMaterial]          = converte_valor_estoques($linha[3]);
                $MaterialValorEstimado[$cntMaterial]         = converte_valor_estoques($linha[4]);
                $MaterialTotalExercicio[$cntMaterial]         = converte_valor_estoques($linha[5]);
                $MaterialQuantidadeExercicio[$cntMaterial] = converte_valor_estoques($linha[6]);
                $MaterialMarca[$cntMaterial]               = $linha[7];
                $MaterialModelo[$cntMaterial]              = $linha[8];
                $MaterialFornecedor[$cntMaterial]          = $fornecedorItem;
                $MaterialDescricaoDetalhada[$cntMaterial]  = strtoupper2(trim($linha[14]));
                $tipoItem                                  = 'M';
               
            } else {
                ++ $cntServico;
                $ServicoCheck[$cntServico]               = false;
                $ServicoCod[$cntServico]                 = $linha[1];
                $ServicoDescricaoDetalhada[$cntServico]  = strtoupper2(trim($linha[2]));
                $ServicoQuantidade[$cntServico]          = converte_valor_estoques($linha[3]);
                $ServicoValorEstimado[$cntServico]       = converte_valor_estoques($linha[4]);
                $ServicoTotalExercicio[$cntServico]      = converte_valor_estoques($linha[5]);
                $ServicoQuantidadeExercicio[$cntServico] = converte_valor_estoques($linha[6]);
                $ServicoFornecedor[$cntServico]          = $fornecedorItem;
                $tipoItem                                = 'S';
            }
        }
        
        // unset($_SESSION['materiais']);
        // unset($_SESSION['Arquivos_Upload']); // Recuperando documentos
    }
}

// pegando limites de compra
// sintaxe para pegar o limite de compra: $limiteCompra[cód do tipo da compra][administração D ou I][é obras?]
$limiteCompra          = null;
$JSCriacaoLimiteCompra = '';

if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_INCLUIR) {
    // em manter apenas recuperar dados quando ainda não foi preenchido
    $sql = 'SELECT  CTPCOMCODI, FLICOMTIPO, CMODLICODI, VLICOMOBRA, VLICOMSERV
            FROM    SFPC.TBLIMITECOMPRA
            ORDER BY CTPCOMCODI, FLICOMTIPO, CMODLICODI, VLICOMOBRA, VLICOMSERV ';
    
    $res = executarSQL($db, $sql);
    
    $oldctpcomcodi = null;
    $oldflicomtipo = null;
    
    while ($obj = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
        if (is_null($obj->CMODLICODI) or $obj->CMODLICODI == '') {
            $limiteCompra[$obj->ctpcomcodi][$obj->flicomtipo][true]  = $obj->vlicomobra;
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

// pegando situação atual da SCC (Este if está fora do if acima pois essa parte tem que ser carregada toda vez que recarrega a página, enquanto acima só carrega uma vez apenas)
if ($acaoPagina == ACAO_PAGINA_MANTER or $acaoPagina == ACAO_PAGINA_ACOMPANHAR or $acaoPagina == ACAO_PAGINA_EXCLUIR) { // em manter apenas recuperar dados quando ainda não foi preenchido
    $sql = "SELECT  CSITSOCODI
            FROM    SFPC.TBSOLICITACAOCOMPRA
            WHERE   csolcosequ = $Solicitacao ";
    
    $situacaoSolicitacaoAtual = resultValorUnico(executarSQL($db, $sql));
}

// variáveis para ocultar campos e checagens associadas
$ocultarCampoRegistroPreco       = false;
$ocultarCampoProcessoLicitatorio = false;
$ocultarCampoGeraContrato        = false;
$ocultarCampoLegislacao          = false;
$ocultarCampoTRP                 = false; // campo não aparecerá enquanto não for definido a tabela TRP
$ocultarCampoSARP                = false;
$ocultarCampoDataDOM             = false;
$ocultarCampoExercicio           = false;
$ocultarCampoFornecedor          = false;
$ocultarCampoNumeroSCC           = false;
$ocultarCampoNumero              = false; // ocultar campo numero
$ocultarCampoJustificativa       = false;
$preencherCampoGeraContrato      = false; // informa que, apesar de ser oculto, CampoGeraContrato deve possuir o valor 'S'
$isFornecedorUnico               = false; // informa se o campo de fornecedores dos itens está bloqueado para edição
$isValidacaoFornecedorLicitacao  = true; // informa se a validação do fornecedor deve ser de licitação. Caso false, irá validar os fornecedores como compra direta
$isFracionamentoDespesa          = false;
$ocultarCamposEdicao             = false;
$isBloqueioUnico                 = false; // Se o bloqueio ou dotação é o mesmo para toda SCC (todos os itens terão os mesmos bloqueios/dotacoes)

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
    $ocultarCampoTRP = true; // Pra SARP, o TRP não é mostrado
}

if ($TipoCompra == TIPO_COMPRA_LICITACAO and $RegistroPreco == 'S') {
    $ocultarCampoGeraContrato = true;
    
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

if ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
    $isValidacaoFornecedorLicitacao = false;
}

if ($isFornecedorUnico) {
    $ifVisualizacaoThenReadOnlyFornecedorItens = 'disabled';
}




//var_dump($codigo_material);
if(!empty($codigo_material)){
    //var_dump($codigo_material);
    $QuantidadeMaterial = count($codigo_material); // Materiais do POST    
  //  var_dump($QuantidadeMaterial);
//    var_dump('1');
}else{
    $QuantidadeMaterial = count($MaterialCod); // Materiais do POST
    
}
// Pegando os dados dos materiais enviados por POST
for ($itr = 0; $itr < $QuantidadeMaterial; ++ $itr) {

    $_SESSION['cods'][] = $MaterialCod[$itr];

    $sql = 'SELECT  M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT
            FROM    SFPC.TBMATERIALPORTAL M
                    LEFT JOIN SFPC.TBUNIDADEDEMEDIDA U ON U.CUNIDMCODI = M.CUNIDMCODI
                    LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA I ON M.CMATEPSEQU = I.CMATEPSEQU
            WHERE   M.CMATEPSEQU = ' . $MaterialCod[$itr] . '
            GROUP BY M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT
            ORDER BY M.EMATEPDESC, U.EUNIDMSIGL, I.EITESCDESCMAT ';
            //die;
            $res = $db->query($sql);
            
            if (db::isError($res)) {
                EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
            }
            $Linha             = $res->fetchRow();
            $MaterialDescricao = $Linha[0];
            $MaterialUnidade   = $Linha[1];
            $MaterialDescDet   = strtoupper2($Linha[2]);
            $pos               = count($materiais);
            

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

    $materiais[$pos]['posicao']              = $pos; // posição no array
    $materiais[$pos]['posicaoItem']          = $pos + 1; // posição mostrada na tela
    $materiais[$pos]['tipo']                 = TIPO_ITEM_MATERIAL;
    $materiais[$pos]['codigo']               = $MaterialCod[$itr];
    $materiais[$pos]['descricao']            = $MaterialDescricao;
    $materiais[$pos]['unidade']              = $MaterialUnidade;
    $materiais[$pos]['descricaoDetalhada']   = strtoupper(trim($MaterialDescricaoDetalhada[$itr]));

    if (is_null($MaterialCheck[$itr]) or ! $MaterialCheck[$itr]) {
        $materiais[$pos]['check'] = false;
    } else {
        $materiais[$pos]['check'] = true;
    }
    $materiais[$pos]['quantidade']          = $MaterialQuantidade[$itr];
    $materiais[$pos]['valorEstimado']       = $MaterialValorEstimado[$itr];
    $materiais[$pos]['quantidadeItem']      = moeda2float($MaterialQuantidade[$itr]); // valores em float para uso em funções
    $materiais[$pos]['valorItem']           = moeda2float($MaterialValorEstimado[$itr]); // valores em float para uso em funções
    $materiais[$pos]['quantidadeExercicio'] = $MaterialQuantidadeExercicio[$itr];

    if (moeda2float($materiais[$pos]['quantidade']) == 1) {
        $materiais[$pos]['totalExercicio'] = $MaterialTotalExercicio[$itr];
    } else {
        $materiais[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($materiais[$pos]['quantidadeExercicio']) * moeda2float($materiais[$pos]['valorEstimado']));
    }
}

$_SESSION['MATERIAIS'] = $materiais;

$QuantidadeServico = count($ServicoCod); // Pegando os dados dos servicos enviados por POST

for ($itr = 0; $itr < $QuantidadeServico; ++ $itr) {
    $sql = 'SELECT  M.ESERVPDESC
            FROM    SFPC.TBSERVICOPORTAL M
            WHERE   M.CSERVPSEQU = ' . $ServicoCod[$itr] . ' ';

    $res = $db->query($sql);
    
    if (db::isError($res)) {
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
    $servicos[$pos]['posicao']              = $pos;
    $servicos[$pos]['posicaoItem']          = $pos + 1; // posição mostrada na tela
    $servicos[$pos]['tipo']                 = TIPO_ITEM_SERVICO;
    $servicos[$pos]['codigo']               = $ServicoCod[$itr];
    $servicos[$pos]['descricao']            = $Descricao;
    $servicos[$pos]['descricaoDetalhada']   = strtoupper(trim($ServicoDescricaoDetalhada[$itr]));
    
    if (is_null($ServicoCheck[$itr]) or ! $ServicoCheck[$itr]) {
        $servicos[$pos]['check'] = false;
    } else {
        $servicos[$pos]['check'] = true;
    }
    $servicos[$pos]['quantidade']          = $ServicoQuantidade[$itr];
    $servicos[$pos]['valorEstimado']       = $ServicoValorEstimado[$itr];
    $servicos[$pos]['quantidadeItem']      = moeda2float($ServicoQuantidade[$itr]); // valores em float para uso em funções
    $servicos[$pos]['valorItem']           = moeda2float($ServicoValorEstimado[$itr]); // valores em float para uso em funções
    $servicos[$pos]['quantidadeExercicio'] = $ServicoQuantidadeExercicio[$itr];

    if (moeda2float($servicos[$pos]['quantidade']) == 1) {
        $servicos[$pos]['totalExercicio'] = $ServicoTotalExercicio[$itr];
    } else {
        $servicos[$pos]['totalExercicio'] = converte_valor_estoques(moeda2float($servicos[$pos]['quantidadeExercicio']) * moeda2float($servicos[$pos]['valorEstimado']));
    }

}
// Pegando os materiais e serviços sendo incluídos via SESSION (janela de seleção de material/serviço) #

if (count($_SESSION['item']) != 0) {

    // [CUSTOMIZAÇÃO]
    if ($TipoCompra == TIPO_COMPRA_LICITACAO && $RegistroPreco == "S" && $sequencialIntencao != "") {
        $servicos = array();
        $materiais = array();
    }
    // [/CUSTOMIZAÇÃO]
    sort($_SESSION['item']);
    
    for ($i = 0; $i < count($_SESSION['item']); ++ $i) {
        //$DadosSessao = explode($SimboloConcatenacaoArray, $_SESSION['item'][$i]);
        $DadosSessao = explode('Æ', $_SESSION['item'][$i]);
        
        $ItemCodigo  = $DadosSessao[1];
        $ItemTipo    = $DadosSessao[3];
        
        if ($ItemTipo == 'M') {
           
            // incluindo item
            // if (!$itemJaExiste) {
            $sql = 'SELECT  M.EMATEPDESC, U.EUNIDMSIGL
                    FROM    SFPC.TBMATERIALPORTAL M, SFPC.TBUNIDADEDEMEDIDA U
                    WHERE   M.CMATEPSEQU = ' . $ItemCodigo . '
                            AND U.CUNIDMCODI = M.CUNIDMCODI ';

            $res = $db->query($sql);
            
            if (db::isError($res)) {
                EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
            }
            $Linha = $res->fetchRow();
            $MaterialDescricao = $Linha[0];
            $MaterialUnidade = $Linha[1];

            $pos = count($materiais);
            $materiais[$pos]                        = array();
            $materiais[$pos]['tipo']                = TIPO_ITEM_MATERIAL;
            $materiais[$pos]['codigo']              = $ItemCodigo;
            $materiais[$pos]['descricao']           = $MaterialDescricao;
            $materiais[$pos]['descricaoDetalhada']  = (!empty($DadosSessao[8])) ? strtoupper($DadosSessao[8]) : '';
            $materiais[$pos]['unidade']             = $MaterialUnidade;
            $materiais[$pos]['check']               = false;
            $materiais[$pos]['quantidade']          = !empty($_SESSION['MaterialQuantidade'][$pos]) ? $_SESSION['MaterialQuantidade'][$pos] : '0,0000';
            $materiais[$pos]['valorEstimado']       = !empty($_SESSION['MaterialValorEstimado'][$pos]) ? converte_valor_estoques($_SESSION['MaterialValorEstimado'][$pos]) : '0,0000';
            $materiais[$pos]['quantidadeItem']      = 0;
            $materiais[$pos]['valorItem']           = 0;
            $materiais[$pos]['quantidadeExercicio'] = '0,0000';
            $materiais[$pos]['totalExercicio']      = '0,0000';
            $materiais[$pos]['posicao']             = $pos;
            $materiais[$pos]['posicaoItem']         = $pos + 1; // posição mostrada na tela // $materiais[$pos]["reservas"] = array();
            $materiais[$pos]['teste_valor'] = $_POST['MaterialValorEstimado'][$pos];
            $materiais[$pos]['teste_qtd'] = $_POST['MaterialQuantidade'][$pos];
            
            $_SESSION['MATERIAIS'] = $materiais;

        } elseif ($ItemTipo == 'S') {
            // verificando se item já existe
            /*
             * $itemJaExiste = false;
             * $qtdeServicos = count($servicos);
             *
             * for ($i2=0; $i2<$qtdeServicos; $i2++) {
             * if ($ItemCodigo == $servicos[$i2]["codigo"]) {
             * $itemJaExiste = true;
             * }
             * }
             *
             * #incluindo item
             * if (!$itemJaExiste) {
             */
            $sql = 'SELECT  M.ESERVPDESC
                    FROM    SFPC.TBSERVICOPORTAL M
                    WHERE   M.CSERVPSEQU = ' . $ItemCodigo . ' ';

            $res = $db->query($sql);
            
            if (PEAR::isError($res)) {
                EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
            }
            $Linha = $res->fetchRow();
            $Descricao = $Linha[0];
            
            $pos = count($servicos);
            $servicos[$pos]                        = array();
            $servicos[$pos]['tipo']                = TIPO_ITEM_SERVICO;
            $servicos[$pos]['codigo']              = $ItemCodigo;
            $servicos[$pos]['descricao']           = $Descricao;
            $servicos[$pos]['descricaoDetalhada']  = (!empty($_SESSION['ServicoDescricaoDetalhada'][$pos])) ? strtoupper($_SESSION['ServicoDescricaoDetalhada'][$pos]) : '';
            $servicos[$pos]['check']               = false;
            $servicos[$pos]['quantidade']          = !empty($_SESSION['ServicoQuantidade'][$pos]) ? $_SESSION['ServicoQuantidade'][$pos] : '0,0000';
            $servicos[$pos]['valorEstimado']       = !empty($_SESSION['ServicoValorEstimado'][$pos]) ? converte_valor_estoques($_SESSION['ServicoValorEstimado'][$pos]) : '0,0000';
            $servicos[$pos]['quantidadeItem']      = 0;
            $servicos[$pos]['valorItem']           = 0;
            $servicos[$pos]['quantidadeExercicio'] = '0';
            $servicos[$pos]['totalExercicio']      = '0,0000';
            $servicos[$pos]['fornecedor']          = '';
            $servicos[$pos]['posicao']             = $pos;
            $servicos[$pos]['posicaoItem']         = $pos + 1; // posição mostrada na tela

           $_SESSION['SERVICOS'] = $servicos;

        } else {
            EmailErro('Erro', __FILE__, __LINE__, 'ItemTipo não é nem material nem serviço! /n var SimboloConcatenacaoArray = ' . $SimboloConcatenacaoArray . '');
        }
    }
  
    //unset($_SESSION['MATERIAIS']);
    //unset($_SESSION['SERVICOS']);
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

$materiaisSARP = CR92::retornarItensMateriasAtaSarp();
$servicosSARP  = CR92::retornarItensServicoAtaSarp();

if (! empty($materiaisSARP)) {
    $materiais = $materiaisSARP;
}

if (! empty($servicosSARP)) {
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
    //$sql = "SELECT  COUNT(FGRUSECONT)
    //        FROM    SFPC.TBGRUPOSUBELEMENTODESPESA
    //        WHERE   FGRUSECONT = 'S'
    //                AND CGRUMSCODI IN ($gruposMateriaisServicos) ";
    
    //$quantidadeSubElementoComGeraContrato = resultValorUnico(executarSQL($db, $sql));
    /*
    if ($quantidadeSubElementoComGeraContrato > 0) { // Preenchendo contrato
        $GeraContrato               = 'S';
        $preencherCampoGeraContrato = true;
        $ocultarCampoGeraContrato   = false;
    }*/
}

// Identifica o Programa para Erro de Banco de Dados
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
$DataAtual      = date('Y-m-d');
$anoAtual       = date('Y');

// verificar se SCC está em uma situação que não pode ser alterada
if ($Botao == 'Manter' or $Botao == 'Excluir') {
    $sql = "SELECT  SOL.CORGLICODI, SOL.CSITSOCODI, SOL.CSOLCOTIPCOSEQU, SOL.CTPCOMCODI, CS.CDOCPCSEQU
            FROM    SFPC.TBSOLICITACAOCOMPRA SOL
                    LEFT OUTER JOIN SFPC.TBCONTRATOSFPC CS ON SOL.CSOLCOSEQU = CS.CSOLCOSEQU
            WHERE   SOL.CSOLCOSEQU = $Solicitacao ";

    $linha = resultLinhaUnica(executarTransacao($db, $sql));
    $Orgao                 = $linha[0];
    $SituacaoCompra        = $linha[1];
    $Numero                = $linha[2];
    $tipoCompra            = $linha[3];
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
    } else {
        if (($SituacaoCompra == TIPO_SITUACAO_SCC_EM_LICITACAO) && $_SESSION['_cperficodi_'] == 2) {
            $alterarSCC = true;
        } else {
            assercao(false, "Esta SCC não pode ser alterada/cancelada pois está em uma situação que não pode ser alterada. SCC='" . $Solicitacao . "'");
        }
    }
}
/*
if ($Botao == 'Excluir' and $acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $sql = "SELECT  CSITSOCODI
            FROM    SFPC.TBSOLICITACAOCOMPRA
            WHERE   CSOLCOSEQU = $Solicitacao ";
    
    $situacao = resultValorUnico(executarSQL($db, $sql));
    
    $sql = 'UPDATE  SFPC.TBSOLICITACAOCOMPRA
            SET     CUSUPOCOD1 = ' . $_SESSION['_cusupocodi_'] . ',
                    TSOLCOULAT = NOW(),
                    CSITSOCODI = ' . $TIPO_SITUACAO_SCC_CANCELADA . "
            WHERE   CSOLCOSEQU = $Solicitacao ";
    
    executarTransacao($db, $sql);
    
    $sql = "INSERT INTO SFPC.TBHISTSITUACAOSOLICITACAO(CSOLCOSEQU, THSITSDATA, CSITSOCODI, XHSITSOBSE, CUSUPOCODI, THSITSULAT)
            VALUES ($Solicitacao, now(), " . $TIPO_SITUACAO_SCC_CANCELADA . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';
    
    executarTransacao($db, $sql);
    finalizarTransacao($db);
    
    $Mensagem = 'Solicitação cancelada com Sucesso';
    
    header('Location: ' . $programaSelecao . '?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    exit();
} else
*/
if ($Botao == 'Incluir' or $Botao == 'Manter') {
    $Mens     = 0;
    $Mensagem = '';

    if (empty($materiais) === false) {
        foreach ($materiais as $mat) {
            if ((hasIndicadorCADUM($db, (int) $mat['codigo']) && trim($mat['descricaoDetalhada']) == '')) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela     = $mat['posicao'] + 1;
                    
                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela     = null;
            }

            $DescDetMat = trim($mat['descricaoDetalhada']);

            if (strlen($DescDetMat) > 1000 && strlen(trim($DescDetMat)) > 1000) {
                $idHtmlDescricao = 'MaterialDescricaoDetalhada_' . $mat['posicao'];
                $posicaoTela     = $mat['posicao'] + 1;

                adicionarMensagem("<a href='javascript:document.getElementById(\"$idHtmlDescricao\").focus();' class='titulo2'>Descrição detalhada do material ord $posicaoTela acima do limite de 1000 caracteres</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

                $idHtmlDescricao = null;
                $posicaoTela     = null;
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
        if (! $ocultarCampoFornecedor and ! is_null($CnpjFornecedor) and $CnpjFornecedor != '') {
            $retorno = validaFormatoCNPJ_CPF($CnpjFornecedor);
            
            if (! $retorno[0]) {
                $msgAux = $retorno[1];
                adicionarMensagem("<a href='javascript:formulario.CnpjFornecedor.focus();' class='titulo2'>erro em campo de fornecedor com a mensagem '$msgAux'</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            }
        }
       

        if (! $ocultarCampoGeraContrato and is_null($GeraContrato)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"GeraContrato\"][0].focus();' class='titulo2'> Gera Contrato </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (! $ocultarCampoRegistroPreco and is_null($RegistroPreco)) {
            adicionarMensagem("<a href='javascript:formulario.elements[\"RegistroPreco\"][0].focus();' class='titulo2'> Registro de Preço </a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }


        $item       = empty($MaterialCod) ? $ServicoCod : $MaterialCod;
        $isMaterial = empty($MaterialCod);
        $valido     = true;

        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        if (count($MaterialCod) == 0 and count($ServicoCod) == 0) { // Se não escolheu nenhum item
            adicionarMensagem("<a href='javascript:formulario.IncluirItem.focus();' class='titulo2'>Pelo menos um item de material ou serviço</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }

        $fornecedorCompra       = null; // verificando se há mais de 1 fornecedor (tanto para materiais quanto servicos)
        $fornecedorCompraSetado = false;
        $elementoDespesaItem    = null;
        $posElementoDespesa     = - 1;

        if (! is_null($materiais)) {
            foreach ($materiais as $material) {
                if (! $GLOBALS['BloquearMensagens']) {
                    $pos = $material['posicao'];
                    $ord = $pos + 1;
                    
                    if ($material['quantidade'] == '' or moeda2float($material['quantidade']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidade[" . $pos . "]').focus();\" class='titulo2'> Quantidade do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    
                    if ($material['valorEstimado'] == '' or moeda2float($material['valorEstimado']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    
                    if (! $ocultarCampoFornecedor) {
                        if ($material['marca'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialMarca[" . $pos . "]').focus();\" class='titulo2'> Marca do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }
                        
                        if ($material['modelo'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialModelo[" . $pos . "]').focus();\" class='titulo2'> Modelo do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        }

                        if ($material['fornecedor'] == '') {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialFornecedor[" . $pos . "]').focus();\" class='titulo2'> Fornecedor do material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } else {
                            if (! $fornecedorCompraSetado) { // pegando 1o fornecedor dos itens
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
                                
                                try { // checar sicref e debito mercantil
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

                        if (comparaFloat(moeda2float($material['quantidade']), '<', moeda2float($material['quantidadeExercicio']), 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialQuantidadeExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Quantidade no Exercício maior que a Quantidade' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                        } elseif (comparaFloat($valorTotalItem, '<', $valorTotalExercicioItem, 4)) {
                            adicionarMensagem("<a href=\"javascript:document.getElementById('MaterialTotalExercicio[" . $pos . "]').focus();\" class='titulo2'> 'Valor total de exercício maior que valor total do item' no material ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
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
                    if (strlen($DescDet) > 1000 && strlen(trim($DescDet)) > 1000) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . ' acima do limite de 200 caracteres</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    
                    if (! strlen($DescDet) > 0 && ! strlen(trim($DescDet)) > 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoDescricaoDetalhada[" . $pos . "]').focus();\" class='titulo2'> Descrição detalhada do serviço ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }
                    

                    if ($servico['valorEstimado'] == '' or moeda2float($servico['valorEstimado']) == 0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoValorEstimado[" . $pos . "]').focus();\" class='titulo2'> Valor estimado do servico ord " . ($ord) . '</a>', $GLOBALS['TIPO_MENSAGEM_ERRO']);
                    }

                    
                    $valorTotalItem = moeda2float($servico[$pos]['quantidade']) * moeda2float($servico[$pos]['valorEstimado']);

                    
                    if ($ocultarCampoExercicio) {
                        $valorTotalExercicioItem = 0;
                    } else {
                        if ($servico['quantidadeExercicio']=="" or moeda2float($servico['quantidadeExercicio'])==0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoQuantidadeExercicio[".$pos."]').focus();\" class='titulo2'> Quantidade de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        } elseif ($servico['totalExercicio']=="" or moeda2float($servico['totalExercicio'])==0) {
                        adicionarMensagem("<a href=\"javascript:document.getElementById('ServicoTotalExercicio[".$pos."]').focus();\" class='titulo2'> Valor de exercício do serviço ord ".($ord)."</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
                        }
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



    $itensSCC = array_merge((array) $materiais, (array) $servicos);

    if ((is_null($itensSCC) or count($itensSCC) == 0) and ! is_null($Bloqueios) and count($Bloqueios) > 0) {
        adicionarMensagem('Não é possível adicionar Bloqueios ou Dotações em SCCs que não tenham itens', $GLOBALS['TIPO_MENSAGEM_ERRO']);
    } else {
        if ($Botao == 'Incluir' or $Botao == 'Manter') {
            $campoDotacaoNulo = campoDotacaoNulo();
            
            if ( ($TipoCompra == 2 && $RegistroPreco == 'S') || ($TipoCompra == 1 && $GeraContrato == 'N') || !($campoDotacaoNulo) ) {

            } else {
              //  validarReservaOrcamentaria($db, $dbOracle, $tipoReserva, $Bloqueios, $itensSCC, 'BloqueioTodos', $TipoCompra, $RegistroPreco);
            }
        }
    }
    
    /*
    if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
        if (! $ocultarCampoProcessoLicitatorio and (is_null($NumProcessoSARP) or $NumProcessoSARP == '')) {
            adicionarMensagem("<a href='javascript:javascript:document.getElementById(\"SarpLicitacaoLink\").focus();' class='titulo2'>Processo Licitatório</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        }
    }
    */

    if ($GLOBALS['Mens'] != 1) {
        if ((($Botao == 'Incluir' or $Botao == 'Rascunho') and $acaoPagina == ACAO_PAGINA_INCLUIR) or (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $acaoPagina == ACAO_PAGINA_MANTER)) {
            $ano = date('Y');
            
            // Pegando dados de órgão e unidade pelo centro de custo
            $sql = "SELECT  CORGLICODI, CCENPOCORG, CCENPOUNID
                    FROM    SFPC.TBCENTROCUSTOPORTAL
                    WHERE   CCENPOSEQU = $CentroCusto ";
            
            $Linha = resultLinhaUnica(executarTransacao($db, $sql));
            $Orgao        = $Linha[0];
            $OrgaoSofin   = $Linha[1];
            $UnidadeSofin = $Linha[2];
            
            /*
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                // Pegando ano, órgão e tipo para ver se sequencial da SCC deve mudar, e vendo se a situação da SCC
                $sql = "SELECT  CORGLICODI, ASOLCOANOS, CTPCOMCODI, CSOLCOTIPCOSEQU, CSOLCOCODI, CSITSOCODI, CINTRPSEQU, CINTRPSANO
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CSOLCOSEQU = $Solicitacao ";

                $linha = resultLinhaUnica(executarTransacao($db, $sql));
                $OrgaoAntes                     = $linha[0];
                $AnoAntes                       = $linha[1];
                $TipoCompraAntes                = $linha[2];
                $sequencialPorAnoOrgaoTipoAntes = $linha[3];
                $sequencialPorAnoOrgaoAntes     = $linha[4];
                $SituacaoCompraAntes            = $linha[5];
                $IntencaoSequ                   = $linha[6];
                $IntencaoAno                    = $linha[7];
                $ano = $AnoAntes; // em manter o ano nao deve mudar
                                  // aceitar Cadastramento apenas para rascunho!
                
                assercao(($SituacaoCompraAntes != TIPO_SITUACAO_SCC_EM_CADASTRAMENTO and $Botao != 'Rascunho' and $Botao != 'ManterRascunho') or $SituacaoCompraAntes == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO, "ERRO: Tentando alterar SCC já incluída para 'EM CADASTRAMENTO'. Abortando.");
            }

            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano and $TipoCompraAntes == $TipoCompra) { // Pegando sequencial da SCC pelo ano, orgao e tipo
                $sequencialPorAnoOrgaoTipo = $sequencialPorAnoOrgaoTipoAntes; // nao mudar o sequencial caso o ano e orgao e tipo nao mudaram
            } else {
                // para inclusão ou mudança de orgao ano ou tipo, mudar sequencial
                $sql = 'SELECT  MAX(CSOLCOTIPCOSEQU)
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CORGLICODI = ' . $Orgao . '
                                AND ASOLCOANOS = ' . date('Y') . '
                                AND CTPCOMCODI = ' . $TipoCompra . ' ';

                $sequencialPorAnoOrgaoTipo = resultValorUnico(executarTransacao($db, $sql));
                
                if (is_null($sequencialPorAnoOrgaoTipo) or $sequencialPorAnoOrgaoTipo == '') {
                    $sequencialPorAnoOrgaoTipo = 1;
                } else {
                    ++ $sequencialPorAnoOrgaoTipo;
                }
            }
            */

            if (($Botao == 'Manter' or $Botao == 'ManterRascunho') and $OrgaoAntes == $Orgao and $AnoAntes == $ano) { // Pegando sequencial da SCC pelo ano e orgao
                $sequencialPorAnoOrgao = $sequencialPorAnoOrgaoAntes; // nao mudar o sequencial caso o ano e orgao nao mudaram
            } else {
                // para inclusão ou mudança de orgao ou ano, mudar sequencial
                $sql = 'SELECT  MAX(CSOLCOCODI)
                        FROM    SFPC.TBSOLICITACAOCOMPRA
                        WHERE   CORGLICODI = ' . $Orgao . '
                                AND ASOLCOANOS = ' . date('Y') . ' ';
                
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
                $strNumProcessoSARP          = 'null';
                $strGrupoEmpresaCodigoSARP   = 'null';
                $strAnoProcessoSARP          = 'null';
                $strComissaoCodigoSARP       = 'null';
                $strOrgaoLicitanteCodigoSARP = 'null';
            } else {
                $strNumProcessoSARP          = $NumProcessoSARP;
                $strGrupoEmpresaCodigoSARP   = $GrupoEmpresaCodigoSARP;
                $strAnoProcessoSARP          = $AnoProcessoSARP;
                $strComissaoCodigoSARP       = $ComissaoCodigoSARP;
                $strOrgaoLicitanteCodigoSARP = $OrgaoLicitanteCodigoSARP;
            }
            
            if ($ocultarCampoLegislacao or is_null($Inciso) or $Inciso == '') {
                $strTipoLei = 'null';
                $strLei     = 'null';
                $strArtigo  = 'null';
                $strInciso  = 'null';
            } else {
                $strTipoLei = "'" . $TipoLei . "'";
                $strLei     = "'" . $Lei . "'";
                $strArtigo  = "'" . $Artigo . "'";
                $strInciso  = "'" . $Inciso . "'";
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
            

            // Verificando a situação da solicitação
            $situacaoSolicitacao         = - 1;
            $fluxoVerificarGerarContrato = false;

            // Encontrando situação da solicitação
            if ($Botao == 'Rascunho' or $Botao == 'ManterRascunho') {
                $situacaoSolicitacao = $TIPO_SITUACAO_SCC_EM_CADASTRAMENTO;
            } elseif ($TipoCompra == TIPO_COMPRA_DIRETA or $TipoCompra == TIPO_COMPRA_DISPENSA or $TipoCompra == TIPO_COMPRA_INEXIGIBILIDADE) {
                $fluxoVerificarGerarContrato = true;
            } elseif ($TipoCompra == TIPO_COMPRA_LICITACAO) {
                if ($Botao == 'Manter' && $_SESSION['_cperficodi_'] == 2 && $SituacaoCompraAntes == 9) {
                    $situacaoSolicitacao = 9;
                } else {
                        $sql = 'SELECT  FORGLITIPO
                                FROM    SFPC.TBORGAOLICITANTE
                                WHERE   CORGLICODI = ' . $Orgao . ' ';
                
                     $administracao = resultValorUnico(executarTransacao($db, $sql));
                
                    if ($administracao == 'D') {
                        $situacaoSolicitacao = TIPO_SITUACAO_SCC_EM_ANALISE;
                    } elseif ($administracao == 'I') {
                        $situacaoSolicitacao = TIPO_SITUACAO_SCC_PARA_ENCAMINHAMENTO;
                    } else {
                        assercao(false, 'Tipo de adiministração de órgão não reconhecido', $db);
                    }
                }
            } elseif ($TipoCompra == TIPO_COMPRA_SARP) {
                if (! isset($Solicitacao)) {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                } elseif ($Botao != 'Incluir' and isAutorizadoSarp($db, $Solicitacao)) {
                    $fluxoVerificarGerarContrato = true;
                } else {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_AUTORIZACAO_SARP;
                }
            } else {
                assercao(false, 'Tipo de compra não reconhecida', $db);
            }

            if ($fluxoVerificarGerarContrato) {
                if ($GeraContrato == 'S') {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_CONTRATO;
                } else {
                    $situacaoSolicitacao = TIPO_SITUACAO_SCC_PENDENTE_DE_EMPENHO;
                }
            }
            assercao(($situacaoSolicitacao != - 1), 'Caso da situação de solicitação de compra não está sendo tratado.', $db);
            assercao(! is_null($situacaoSolicitacao), 'Erro em variável de situação de solicitação de compra. Variável nula. Motivo provável é se foi usado uma constante nula.', $db);

            $sequencialIntencao = !empty($sequencialIntencao) ? (int) $sequencialIntencao : 'null';
            $anoIntencao = !empty($anoIntencao) ? $anoIntencao : 'null';
            $sequencialSolicitacao = $Solicitacao;
            
            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') {
                $Observacao = str_replace("'","''",trim($Observacao));
                $Observacao = RetiraAcentos($Observacao);

                $Objeto = str_replace("'","''",trim($Objeto));
                $Objeto = RetiraAcentos($Objeto);

                $Justificativa = str_replace("'","''",trim($Justificativa));
                $Justificativa = RetiraAcentos($Justificativa);

                $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                        SET     CORGLICODI = " . $Orgao . ",
                                CSOLCOCODI = " . $sequencialPorAnoOrgao . ",
                                CTPCOMCODI = " . $TipoCompra . ",
                                CSOLCOTIPCOSEQU = " . $sequencialPorAnoOrgaoTipo . ",
                                CCENPOSEQU = " . $CentroCusto . ",
                                ESOLCOOBSE = '".$Observacao."' ,
                                ESOLCOOBJE = '".$Objeto."',
                                ESOLCOJUST = '".$Justificativa."',
                                CLICPOPROC = " . $strNumProcessoSARP . ",
                                ALICPOANOP = " . $strAnoProcessoSARP . ",
                                CGREMPCODI = " . $strGrupoEmpresaCodigoSARP . ",
                                CCOMLICODI = " . $strComissaoCodigoSARP . ",
                                CORGLICOD1 = " . $strOrgaoLicitanteCodigoSARP . ",
                                DSOLCODPDO = " . $strDataDom . ",
                                CTPLEITIPO = " . $strTipoLei . ",
                                CLEIPONUME = " . $strLei . ",
                                CARTPOARTI = " . $strArtigo . ",
                                CINCPAINCI = " . $strInciso . ",
                                FSOLCORGPR = " . $strRegistroPreco . ",
                                FSOLCORPCP = '" . $SarpTipo . "',
                                FSOLCOCONT = " . $strGeraContrato . ",
                                TSOLCOULAT = now(),
                                CSITSOCODI = " . $situacaoSolicitacao . ",
                                CINTRPSEQU = " . $sequencialIntencao . ",
                                CINTRPSANO = " . $anoIntencao;
                $sql .= " WHERE CSOLCOSEQU = $Solicitacao";

                executarTransacao($db, $sql);

                if ($_SESSION['_cperficodi_'] == 2 && $SituacaoCompraAntes == 9) {
                    $codUsuario = $_SESSION['_cusupocodi_'];

                    $sql = "UPDATE  SFPC.TBLICITACAOPORTAL
                            SET     XLICPOOBJE = '".$Objeto."',
                                    CUSUPOCODI = $codUsuario,
                                    TLICPOULAT = now()
                            WHERE	CLICPOPROC = (SELECT CLICPOPROC FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao)
                                    AND ALICPOANOP = (SELECT ALICPOANOP FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao)
                                    AND CCOMLICODI = (SELECT CCOMLICODI FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CSOLCOSEQU = $Solicitacao) ";
                    
                    executarTransacao($db, $sql);
                }
            } else {
                $Observacao = str_replace("'","''",trim($Observacao));
                $Observacao = RetiraAcentos($Observacao);

                $Objeto = str_replace("'","''",trim($Objeto));
                $Objeto = RetiraAcentos($Objeto);

                $Justificativa = str_replace("'","''",trim($Justificativa));
                $Justificativa = RetiraAcentos($Justificativa);
                
                $sql = 'INSERT INTO SFPC.TBSOLICITACAOCOMPRA (CORGLICODI, ASOLCOANOS, CSOLCOCODI, CTPCOMCODI, CSOLCOTIPCOSEQU, TSOLCODATA, CCENPOSEQU,
                                    ESOLCOOBSE, ESOLCOOBJE, ESOLCOJUST, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICOD1, DSOLCODPDO, CTPLEITIPO,
                                    CLEIPONUME, CARTPOARTI, CINCPAINCI, FSOLCORGPR, FSOLCORPCP, FSOLCOCONT, CUSUPOCODI, CUSUPOCOD1, TSOLCOULAT, CSITSOCODI, CINTRPSEQU, CINTRPSANO)
                        VALUES (' . $Orgao . ', ' . $anoAtual . ', ' . $sequencialPorAnoOrgao . ', ' . $TipoCompra . ', ' . $sequencialPorAnoOrgaoTipo . ',
                                now(), ' . $CentroCusto . ", '" . $Observacao . "', '" . $Objeto . "', '" . $Justificativa . "', " . $strNumProcessoSARP . ',
                                ' . $strAnoProcessoSARP . ', ' . $strGrupoEmpresaCodigoSARP . ', ' . $strComissaoCodigoSARP . ', ' . $strOrgaoLicitanteCodigoSARP . ', ' . $strDataDom . ', ' . $strTipoLei . ',
                                ' . $strLei . ', ' . $strArtigo . ', ' . $strInciso . ', ' . $strRegistroPreco . ", '" . $SarpTipo . "', " . $strGeraContrato . ',
                                ' . $_SESSION['_cusupocodi_'] . ', ' . $_SESSION['_cusupocodi_'] . ', now(), ' . $situacaoSolicitacao . ',' . $sequencialIntencao . ',' . $anoIntencao . ') ';
                
                executarTransacao($db, $sql);
            }

            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') { // Deletando itens e salvando no histórico
                $sequencialSolicitacao = $Solicitacao;
            } else {
                $sql = 'SELECT LAST_VALUE FROM SFPC.TBSOLICITACAOCOMPRA_CSOLCOSEQU_SEQ1 ';
                $sequencialSolicitacao = resultValorUnico(executarTransacao($db, $sql));
            }

            if ($Botao == 'Manter' or $Botao == 'ManterRascunho') { // Deletando itens e salvando no histórico
                // Apagando PSEs para apagar os itens de SCC
                $sql = "SELECT  '( '||APRESOANOE||', '||CPRESOSEQU||')' AS CHAVE
                        FROM    SFPC.TBPRESOLICITACAOEMPENHO
                        WHERE   CSOLCOSEQU = " . $sequencialSolicitacao . ' ';
                
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
                    $sql = "INSERT INTO sfpc.tbhistsituacaosolicitacao (csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                            VALUES ($sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';
                }
                executarTransacao($db, $sql);
            } else {
                // Incluir
                // salvar o histórico da situação da SCC
                $sql = "INSERT INTO sfpc.tbhistsituacaosolicitacao (csolcosequ, thsitsdata, csitsocodi, xhsitsobse, cusupocodi, thsitsulat)
                        VALUES ($sequencialSolicitacao, now(), " . $situacaoSolicitacao . ', NULL, ' . $_SESSION['_cusupocodi_'] . ', now()); ';
                executarTransacao($db, $sql);
            }

            // Incluindo os itens
            $sequencialItem = 0;
            
            if (is_array($materiais)) {
                foreach ($materiais as $material) {
                    ++ $sequencialItem;
                    $ordem = $material['posicao'] + 1;

                    $totalExercicio      = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    
                    if (! $ocultarCampoExercicio) {
                        $totalExercicio      = $material['totalExercicio'];
                        $quantidadeExercicio = $material['quantidadeExercicio'];
                    }

                    if ($ocultarCampoFornecedor) {
                        $strFornecedorSeq = 'null';
                    } else {
                        $strFornecedor = "'" . removeSimbolos($material['fornecedor']) . "'";
                        
                        $sql = 'SELECT  AFORCRSEQU
                                FROM    SFPC.TBFORNECEDORCREDENCIADO
                                WHERE   AFORCRCCGC = ' . $strFornecedor . ' OR AFORCRCCPF = ' . $strFornecedor . ' ';
                        
                     //   $strFornecedorSeq = resultValorUnico(executarTransacao($db, $sql));
                        
                     //   if (is_null($strFornecedorSeq)) {
                     //       $strFornecedorSeq = 'null';
                     //   } else {
                     //       $strFornecedorSeq = "'" . $strFornecedorSeq . "'";
                     //   }
                    }
                    $material['descricaoDetalhada'] = str_replace("'","''",$material['descricaoDetalhada']);
                    
                    // $material['descricaoDetalhada'] =  str_replace('€', '', $material['descricaoDetalhada']); //removeCaracteresEspeciais($material['descricaoDetalhada']);
                    $material['descricaoDetalhada'] =  RetiraAcentos($material['descricaoDetalhada']);

                    $sql = 'INSERT INTO SFPC.TBITEMSOLICITACAOCOMPRA (CSOLCOSEQU, CITESCSEQU, CMATEPSEQU, CSERVPSEQU, EITESCDESCSE, AITESCORDE, AITESCQTSO, VITESCUNIT, VITESCVEXE, AITESCQTEX, AFORCRSEQU, EITESCMARC, EITESCMODE, CUSUPOCODI, TITESCULAT, EITESCDESCMAT)
                            VALUES (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', ' . $material['codigo'] . ', null, null, ' . $ordem . ", '" . moeda2float($material['quantidade']) . "', '" . moeda2float($material['valorEstimado']) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "', " . $strFornecedorSeq . ", '" . $material['marca'] . "', '" . $material['modelo'] . "', " . $_SESSION['_cusupocodi_'] . ", now(), '" . trim($material['descricaoDetalhada']) . "'); ";
                    print_r($sql);die;
                    executarTransacao($db, $sql);

                }
            }
            
            if (! is_null($servicos)) {
                foreach ($servicos as $servico) {
                    ++ $sequencialItem;
                    $ordem               = $servico['posicao'] + 1;
                    $totalExercicio      = 0.0000;
                    $quantidadeExercicio = 0.0000;
                    
                    if (! $ocultarCampoExercicio) {
                        $totalExercicio      = $servico['totalExercicio'];
                        $quantidadeExercicio = $servico['quantidadeExercicio'];
                    }

                    $servico['descricaoDetalhada'] = str_replace("'","''",trim($servico['descricaoDetalhada']));

                    // $servico['descricaoDetalhada'] =  str_replace('€', '', $servico['descricaoDetalhada']); //removeCaracteresEspeciais($servico['descricaoDetalhada']);
                    $servico['descricaoDetalhada'] =  RetiraAcentos($servico['descricaoDetalhada']);

                    $sql = 'INSERT  INTO SFPC.TBITEMSOLICITACAOCOMPRA (CSOLCOSEQU, CITESCSEQU, CMATEPSEQU, CSERVPSEQU, EITESCDESCSE, AITESCORDE, AITESCQTSO, VITESCUNIT, VITESCVEXE, AITESCQTEX, AFORCRSEQU, EITESCMARC, EITESCMODE, CUSUPOCODI, TITESCULAT)
                            VALUES  (' . $sequencialSolicitacao . ', ' . $sequencialItem . ', null, ' . $servico['codigo'] . ", '" . trim($servico['descricaoDetalhada']) . "', " . $ordem . ", '" . moeda2float($servico['quantidade']) . "', '" . moeda2float($servico['valorEstimado']) . "', '" . moeda2float($totalExercicio) . "', '" . moeda2float($quantidadeExercicio) . "', $strFornecedorSeq, null, null, " . $_SESSION['_cusupocodi_'] . ', now()); ';

                    executarTransacao($db, $sql);
                   
                }
            }

            // ***********************************
            // Gerar TRP
            // ***********************************
            if ($GLOBALS['Mens'] != 1 && !is_null($materiais)) {
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
                    $Botao                       = null;
                    $InicioPrograma              = null;
                    $CentroCusto                 = null;
                    $TipoCompra                  = null;
                    $MaterialCheck               = null;
                    $MaterialCod                 = null;
                    $MaterialQuantidade          = null;
                    $MaterialValorEstimado       = null;
                    $MaterialTotalExercicio      = null;
                    $MaterialQuantidadeExercicio = null;
                    $MaterialMarca               = null;
                    $MaterialModelo              = null;
                    $MaterialFornecedor          = null;
                    $ServicoCheck                = null;
                    $ServicoCod                  = null;
                    $ServicoQuantidade           = null;
                    $ServicoDescricaoDetalhada   = null;
                    $ServicoQuantidadeExercicio  = null;
                    $ServicoValorEstimado        = null;
                    $ServicoTotalExercicio       = null;
                    $ServicoFornecedor           = null;
                    $materiais                   = array();
                    $servicos                    = array();
                  
                    unset($_SESSION['Arquivos_Upload']);
                    header('Location: CadSolicitacaoCompraManterSelecionar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
                    exit();
                }
            }
        }
    }
} elseif ($Botao == 'Retirar') {
    $quantidade = count($materiais);

    //var_dump($quantidade);
    for ($itr = 0; $itr < $quantidade; ++$itr) {
        $materiais[$itr]['check'];
        if ($materiais[$itr]['check']) {

            array_splice( $materiais, $itr, 1 );

          //  var_dump($materiais);
           // $materiais = array_removerItem($materiais, $itr);
            // $MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
           // $quantidade = count($materiais);
            
           // if ($quantidadeNova != $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
          //      $quantidade = $quantidadeNova;
          //      --$itr; // compensando a posição do item removido
          //  }
        }
    }

    $quantidade = count($materiais);
    //var_dump($quantidade);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $materiais[$itr]['posicao'] = $itr;
    }
/*
    var_dump($quantidade);
   
    for ($itr = 0; $itr < $quantidade; ++$itr) {
        if ($materiais[$itr]['check']) {
            $materiais = array_removerItem($materiais, $itr);
            // $MaterialBloqueioItem = array_removerItem($MaterialBloqueioItem, $itr);
            $quantidadeNova = count($materiais);
            
            if ($quantidadeNova != $quantidade) { // verificação de tamanho para confirmar exclusão, para evitar loop infinito causado pelo itr--
                $quantidade = $quantidadeNova;
                --$itr; // compensando a posição do item removido
            }
        }
    }
    */
    
    $quantidade = count($servicos);
   // var_dump($quantidade);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        if ($servicos[$itr]['check']) {
            array_splice( $servicos, $itr, 1 );
            //$servicos = array_removerItem($servicos, $itr);
            // $ServicoBloqueioItem = array_removerItem($ServicoBloqueioItem, $itr);
            //$quantidadeNova = count($servicos);
            
            //if ($quantidadeNova != $quantidade) {
            //    $quantidade = $quantidadeNova;
            //    -- $itr; // compensando a posição do item removido
            //}
        }
    }
    $quantidade = count($servicos);
  //  var_dump($quantidade);
    for ($itr = 0; $itr < $quantidade; ++ $itr) {
        $servicos[$itr]['posicao'] = $itr;
    }
}

// INÍCIO DA GERAÇÃO DA PÁGINA
$acesso = '';

if ($acaoPagina == ACAO_PAGINA_INCLUIR) {
    $acesso = 'Incluir';
    $descricao = "Preencha os dados abaixo e clique no botão 'Incluir'. Os itens obrigatórios estão com *. O valor estimado refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
    $acesso = 'Manter';
    $descricao = "Preencha os dados abaixo e clique no botão 'Manter'. Os itens obrigatórios estão com *. O valor estimado refere-se ao valor unitário de cada material, de acordo com a unidade. Pode-se anexar documentos em pdf.";
} elseif ($acaoPagina == ACAO_PAGINA_ACOMPANHAR) {
    $acesso = 'Acompanhar';
    $descricao = "Para visualizar nova solicitação clique no botão 'Voltar'.";
} elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) {
    $acesso = 'Cancelar';
    $descricao = 'Clique no botão Cancelar Solicitação.';
}

$tela = isset($_GET['tela']) ? '?tela='.$_GET['tela'] : '';

// ## Centro de custo
// Pegando dados do usuário
$sql = "SELECT  USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
        FROM    SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
        WHERE   USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
                AND USUCEN.FUSUCCTIPO IN ('C')
                AND ((USUCEN.CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ' AND USUCEN.CGREMPCODI = ' . $_SESSION['_cgrempcodi_'] . ')
                    OR (USUCEN.CUSUPOCOD1 = ' . $_SESSION['_cusupocodi_'] . ' AND USUCEN.CGREMPCOD1 = ' . $_SESSION['_cgrempcodi_'] . " AND '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS))
                AND USUCEN.FUSUCCTIPO = 'C'
                AND CENCUS.FCENPOSITU <> 'I'
        GROUP BY USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI ";

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
    $sqlCC = '  SELECT  A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,
                        B.CORGLICODI, B.EORGLIDESC, B.FORGLITIPO
                FROM    SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B
                WHERE   A.CORGLICODI IS NOT NULL
                        AND A.ACENPOANOE = ' . date('Y') . '';
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
    
} else {
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    if ($CentroCusto != '') {
        // Carrega os dados do Centro de Custo selecionado #
        $sql = 'SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA, B.FORGLITIPO, A.FCENPOSITU';
        $sql .= '  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ';
        $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
        // $sql .= " AND A.FCENPOSITU <> 'I' ";
        $res = executarSQL($db, $sql);

        while ($Linha = $res->fetchRow()) {
            $DescCentroCusto = $Linha[0];
            $DescOrgao       = $Linha[1];
            $Orgao           = $Linha[2];
            $RPA             = $Linha[3];
            $Detalhamento    = $Linha[4];
            $administracao   = $Linha[5];
            $ccSituacao      = $Linha[6];
            if ($ccSituacao == 'I') {
                $Detalhamento .= ' (Centro de custo inativo)';
            }
        }

    }
}

// ## Fim Centro de custo

// tipo de compra
$sql = 'SELECT CTPCOMCODI, ETPCOMNOME FROM SFPC.TBTIPOCOMPRA';

$res = executarSQL($db, $sql);


// fim tipo compra

// inicio processo licitatorio SARP
if (! $ocultarCampoProcessoLicitatorio) {
    
    
    if ($CarregaProcessoSARP == 1) {
        $sql = "SELECT  DISTINCT A.CLICPOPROC, A.ALICPOANOP, D.ECOMLIDESC, B.EORGLIDESC
                FROM    SFPC.TBLICITACAOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBCOMISSAOLICITACAO D
                WHERE   A.CORGLICODI = B.CORGLICODI
                        AND A.FLICPOSTAT = 'A'
                        AND A.CCOMLICODI = D.CCOMLICODI ";

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

        $res                         = executarTransacao($db, $sql);
        $Rows                        = $res->numRows();
        $Linha                       = $res->fetchRow();
        $ProcessoAnoSARP             = $Linha[0] . '/' . $Linha[1];
        $ComissaoDescricaoSARP       = $Linha[2];
        $OrgaoLicitanteDescricaoSARP = $Linha[3];


    } else {}

}

ob_start(); // pegando o html ainda não tratado pelo template, para depois jogar no template

    ?>
    <html>
    <?php
    # Carrega o layout padrão #
    layout();
    ?>
    <script language="JavaScript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        function Submete(Destino) {
            document.CadContratoAntigoIncluir.Destino.value = Destino;
            document.CadContratoAntigoIncluir.submit();
        }
        function AbreJanela(url,largura,altura) {
            window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
        }

        function AbreJanelaItem(url,largura,altura){
            window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
        }


        // Executado quando valor estimado é mudado
        function onChangeValorEstimadoItem1(linha, tipoItem){

            if(tipoItem == 1){
                materialServico = "Material";
            }else{
                materialServico = "Servico";
            }
        //materialServico =  nomeMaterialServico[tipoItem];
        
            if(materialServico=="Material"){
                valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
                //trp = moeda2float(document.getElementById(materialServico+'Trp['+linha+']').value);

                //if(valor>trp){
                //    if(!confirm("O valor estimado informado ultrapassa o valor TRP (Tabela Referencial de Preços). Deseja continuar?")){
                //        campo = document.getElementById('MaterialValorEstimado['+linha+']');
                //        campo.value = "";
                //    }
                //}
            }
        }

        function onChangeItemQuantidade1(linha, tipoItem){
            AtualizarValorTotal1(linha, tipoItem);
           // AtualizarQuantidadeExercicio(linha, tipoItem);
        }
        function onChangeItemValor1(linha, tipoItem){
            AtualizarValorTotal1(linha, tipoItem);
         //   AtualizarValorExercicio(linha, tipoItem);
        }

        // Atualiza o valor total de um material Material ou serviço, e recalcula o total de todos os itens
        function AtualizarValorTotal1(linha, tipoItem){
            if(tipoItem == 1){
                materialServico = "Material";
            }else{
                materialServico = "Servico";
            }
            //materialServico = nomeMaterialServico[tipoItem];
            qtde = moeda2float(document.getElementById(materialServico+'Quantidade['+linha+']').value);
            valor = moeda2float(document.getElementById(materialServico+'ValorEstimado['+linha+']').value);
            totalItem = (qtde * valor);
            document.getElementById(materialServico+'ValorTotal['+linha+']').innerHTML = float2moeda(totalItem);
            total = 0;
            qtdeItens = 0;
            qtdeMateriais = 0;
            rowCount = 0;
            if(materialServico == 'Servico'){
                rowCount = $('.'+materialServico+'ValorTotal').length;
                qtdeItens = rowCount;
            }else if(materialServico == 'Material'){
                rowCount = $('.'+materialServico+'ValorTotal').length;
                qtdeItens = rowCount;
            }

//alert(rowCount);
//            var vlor = $(this).val();
 //           alert(vlor);
         //   $('.'+materialServico+'ValorTotal').each(function(){
      //          total += moeda2float(document.getElementById(materialServico+'ValorTotal['+linha+']').innerHTML);
         //   });

            //calc();
           
            for( itr = 0; itr < qtdeItens; itr++){
                total += moeda2float(document.getElementById(materialServico+'ValorTotal['+itr+']').innerHTML);
                console.log(total);
            }

            document.getElementById(materialServico+'Total').innerHTML = float2moeda(total);
        }
        
        function mudaBotao(valor) {
            document.CadContratoAntigoIncluir.Botao.value = valor;
            document.CadContratoAntigoIncluir.submit();
        }

        <?php MenuAcesso(); 
         AddMenuAcesso ('../estoques/CadIncluirItem.php');
         AddMenuAcesso ('../estoques/CadItemDetalhe.php');
        ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript" src="../compras/CadSolicitacaoCompraIncluirManterExcluir.js"></script>

    <script language="JavaScript">Init();</script>
    <form action="CadContratoSemSCC.php" method="post" name="CadContratoAntigoIncluir">
        <input type="hidden" name="codigo_mat" value="<?php echo !empty($codigo_material) ? implode('|',$codigo_material) : ''; ?>">
        <input type="hidden" name="qtd_mat" value="<?php echo !empty($qtd_material) ? implode('|',$qtd_material) : ''; ?>">
        <input type="hidden" name="valor_mat" value="<?php echo !empty($material_valor_estimado) ? implode('|',$material_valor_estimado) : ''; ?>">
        <input type="hidden" name="codigo_servico" value="<?php echo !empty($servico_cod) ? implode('|',$servico_cod) : ''; ?>">
        <input type="hidden" name="qtd_servico" value="<?php echo !empty($servico_qtd) ? implode('|',$servico_qtd) : ''; ?>">
        <input type="hidden" name="valor_estimado_servico" value="<?php echo !empty($servico_valor_estimado) ? implode('|',$servico_valor_estimado) : ''; ?>">
        <!-- <input type="hidden" name="descricao_detalhada_servico" value="<?php // echo !empty($servico_descricao_detalhada) ? implode('|',$servico_descricao_detalhada) : ''; ?>"> -->

        <input type="hidden" name="numcontrato" value="<?php echo !empty($_POST['numcontrato'])?$_POST['numcontrato']:'';?>">
        <input type="hidden" name="objeto" value="<?php echo !empty($_POST['objeto'])?$_POST['objeto']:'';?>">
        <input type="hidden" name="fieldConsorcio" value="<?php echo !empty($_POST['fieldConsorcio'])?$_POST['fieldConsorcio']:'';?>">
        <input type="hidden" name="fieldContinuo" value="<?php echo !empty($_POST['fieldContinuo'])?$_POST['fieldContinuo']:'';?>">
        <input type="hidden" name="obra" value="<?php echo !empty($_POST['obra'])?$_POST['obra']:'';?>">
        <input type="hidden" name="cmb_regimeExecucaoModoFornecimento1" value="<?php echo !empty($_POST['cmb_regimeExecucaoModoFornecimento1'])?$_POST['cmb_regimeExecucaoModoFornecimento1']:'';?>">
        <input type="hidden" name="opcaoExecucaoContrato" value="<?php echo !empty($_POST['opcaoExecucaoContrato'])?$_POST['opcaoExecucaoContrato']:'';?>">
        <input type="hidden" name="prazo" value="<?php echo !empty($_POST['prazo'])?$_POST['prazo']:'';?>">
        <input type="hidden" name="dataPublicacaoDom" value="<?php echo !empty($_POST['dataPublicacaoDom'])?$_POST['dataPublicacaoDom']:'';?>">
        <input type="hidden" name="vigenciaDataInicio" value="<?php echo !empty($_POST['vigenciaDataInicio'])?$_POST['vigenciaDataInicio']:'';?>">
        <input type="hidden" name="vigenciaDataTermino" value="<?php echo !empty($_POST['vigenciaDataTermino'])?$_POST['vigenciaDataTermino']:'';?>">
        <input type="hidden" name="execucaoDataInicio" value="<?php echo !empty($_POST['execucaoDataInicio'])?$_POST['execucaoDataInicio']:'';?>">
        <input type="hidden" name="execucaoDataTermino" value="<?php echo !empty($_POST['execucaoDataTermino'])?$_POST['execucaoDataTermino']:'';?>">
        <input type="hidden" name="comboGarantia" value="<?php echo !empty($_POST['comboGarantia'])?$_POST['comboGarantia']:'';?>">
        <input type="hidden" name="repNome" value="<?php echo !empty($_POST['repNome'])?$_POST['repNome']:'';?>">
        <input type="hidden" name="repCPF" value="<?php echo !empty($_POST['repCPF'])?$_POST['repCPF']:'';?>">
        <input type="hidden" name="repCargo" value="<?php echo !empty($_POST['repCargo'])?$_POST['repCargo']:'';?>">
        <input type="hidden" name="repRG" value="<?php echo !empty($_POST['repRG'])?$_POST['repRG']:'';?>">
        <input type="hidden" name="repRgOrgao" value="<?php echo !empty($_POST['repRgOrgao'])?$_POST['repRgOrgao']:'';?>">
        <input type="hidden" name="repRgUF" value="<?php echo !empty($_POST['repRgUF'])?$_POST['repRgUF']:'';?>">
        <input type="hidden" name="repCidade" value="<?php echo !empty($_POST['repCidade'])?$_POST['repCidade']:'';?>">
        <input type="hidden" name="repEstado" value="<?php echo !empty($_POST['repEstado'])?$_POST['repEstado']:'';?>">
        <input type="hidden" name="repNacionalidade" value="<?php echo !empty($_POST['repNacionalidade'])?$_POST['repNacionalidade']:'';?>">
        <input type="hidden" name="repEstCiv" value="<?php echo !empty($_POST['repEstCiv'])?$_POST['repEstCiv']:'';?>">
        <input type="hidden" name="repProfissao" value="<?php echo !empty($_POST['repProfissao'])?$_POST['repProfissao']:'';?>">
        <input type="hidden" name="repEmail" value="<?php echo !empty($_POST['repEmail'])?$_POST['repEmail']:'';?>">
        <input type="hidden" name="repTelefone" value="<?php echo !empty($_POST['repTelefone'])?$_POST['repTelefone']:'';?>">
        <input type="hidden" name="gestorNome" value="<?php echo !empty($_POST['gestorNome'])?$_POST['gestorNome']:'';?>">
        <input type="hidden" name="gestorMatricula" value="<?php echo !empty($_POST['gestorMatricula'])?$_POST['gestorMatricula']:'';?>">
        <input type="hidden" name="gestorCPF" value="<?php echo !empty($_POST['gestorCPF'])?$_POST['gestorCPF']:'';?>">
        <input type="hidden" name="gestorEmail" value="<?php echo !empty($_POST['gestorEmail'])?$_POST['gestorEmail']:'';?>">
        <input type="hidden" name="gestorTelefone" value="<?php echo !empty($_POST['gestorTelefone'])?$_POST['gestorTelefone']:'';?>">

        <input type="hidden" name="origem" value="<?php echo !empty($_POST['origem']) ? $_POST['origem'] : ''; ?>">
        <input type="hidden" name="orgao_licitante" value="<?php echo !empty($_POST['orgao_licitante']) ? $_POST['orgao_licitante'] : ''; ?>">
        <input type="hidden" name="CnpjCpf" value="<?php echo !empty($_POST['CnpjCpf'])?$_POST['CnpjCpf']:'';?>">
        <input type="hidden" name="CNPJ_CPF" value="<?php echo !empty($_POST['CNPJ_CPF'])?$_POST['CNPJ_CPF']:'';?>">
        <input type="hidden" name="razao" value="<?php echo !empty($_POST['razao']) ? $_POST['razao'] : ''; ?>">
        
        <input type="hidden" name="valor_original" value="<?php echo !empty($_POST['valor_original'])?$_POST['valor_original']:'';?>">
        <input type="hidden" name="valor_global" value="<?php echo !empty($_POST['valor_global'])?$_POST['valor_global']:'';?>">
        <input type="hidden" name="saldo_executar" value="<?php echo !empty($_POST['saldo_executar'])?$_POST['saldo_executar']:'';?>">
        <input type="hidden" name="valor_executado_acumulado" value="<?php echo !empty($_POST['valor_executado_acumulado'])?$_POST['valor_executado_acumulado']:'';?>">
        <input type="hidden" name="numero_ultimo_aditivo" value="<?php echo !empty($_POST['numero_ultimo_aditivo'])?$_POST['numero_ultimo_aditivo']:'';?>">
        <input type="hidden" name="numero_ultimo_apostilamento" value="<?php echo !empty($_POST['numero_ultimo_apostilamento'])?$_POST['numero_ultimo_apostilamento']:'';?>">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contrato Sem SCC > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }

                    $_SESSION['Mens'] = null;
                    $_SESSION['Tipo'] = null;
                    $_SESSION['Mensagem'] = null

                    ?>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <!-- Corpo -->
                <tr>
                    <td width="100"></td>
                    <td class="textonormal">
                        <table  border=1px bordercolor="#75ADE6" cellspacing="0" cellpadding="3" summary="" width="1024px">
                            <thead>
                                <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> 
                                    <b>INCLUIR CONTRATO SEM SOLICITAÇÃO (SCC)</b>
                                </td>
                            </thead>
                            <tr>
                                <td class="textonormal">
                                    <table border="0" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                <tr>
            <td align="left">

                <?php echo NavegacaoAbasIncluir('off','on'); ?>
                                                    
                    <!-- Itens - início -->
                    <table id="scc_material" summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
                        <tbody>
                            <tr>
                                <td colspan="7" class="titulo3 itens_material" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE MATERIAL</td>
                            </tr>
                            <tr class="head_principal">
                                <?php   $descricaoWidth = '300px';
                                        
                                        // redimensionando dependendo do número de campos
                                        if ($TipoCompra == TIPO_COMPRA_LICITACAO and ($RegistroPreco == 'S' or is_null($RegistroPreco)) or is_null($TipoCompra)) {
                                            $descricaoWidth = '700px';
                                        }
                                        $qtdeColunas    = 12;
                                        $colunasOcultas = 0;
                                        
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
                                <td class="textoabason" align="center" bgcolor="#DCEDF7">
                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" width="<?= $descricaoWidth ?>"/>
                                    <br />
                                    DESCRIÇÃO DO MATERIAL
                                </td>
                                <td class="textoabason" align="center" bgcolor="#DCEDF7">
                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                                    <br />
                                    CÓD.RED. CADUM
                                </td>
                                <td class="textoabason" align="center" bgcolor="#DCEDF7">
                                    <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                                    <br />
                                    UND
                                </td>
                                <?php
                            /**
                             * Exibir TD na layout?
                             *
                             * @var bool
                             */
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
                    
                    <?php
                        } else {
                            $colunasOcultas += 1;
                        }   
                    ?>
                    <?php 
                    // kim cr#227641 & cr#228067  CR#228616
                        if(empty($telaAppView)){
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        QUANTIDADE
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        VALOR UNITÁRIO
                    </td>
                    <?php }?>
                    
                    <?php 
                    // kim cr#227641 & cr#228067  
                      //  if(empty($telaAppView)){
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        <img src="../midia/linha.gif" alt="" border="0" height="1px" width="30px"/>
                        <br />
                        VALOR TOTAL
                    </td>
                    <?php //  } ?>
                </tr>
                <?php   // Materiais do POST
                        $QuantidadeMateriais = count($materiais);
                        $QuantidadeServicos  = count($servicos);
                        $ValorTotalItem      = 0;
                        $ValorTotal          = 0;

                        for ($itr = 0; $itr < $QuantidadeMateriais; ++ $itr) {
                            $ValorTotalItem = moeda2float($materiais[$itr]['quantidade']) * moeda2float($materiais[$itr]['valorEstimado']);
                            $ValorTotal += $ValorTotalItem;
                            
                            if (! $ocultarCampoExercicio) {
                                $ValorTotalExercicio   = $materiais[$itr]['totalExercicio'];
                                $TotalDemaisExercicios = $ValorTotalItem - moeda2float($ValorTotalExercicio);
                                
                                if ($TotalDemaisExercicios < 0) {
                                    $TotalDemaisExercicios = 0;
                                }
                            }
                            ?>
                            <!-- Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                            <tr>
                                <!--  Coluna 1 = Codido-->
                                <td class="textonormal" align="center" style="text-align: center">
                                    <?php echo ($itr + 1);?>
                                </td>
                                <!--  Coluna 2  = Descricao -->
                                <td class="textonormal">
                                    <?php if (! $ocultarCamposEdicao) { ?>
                                        <input name="MaterialCheck[<?= $itr ?>]" <?=($materiais[$itr]['check']) ? 'checked' : '';?> <?= $ifVisualizacaoThenReadOnly?> type="checkbox"/>
                                    <?php } ?>
                                        <!-- Kim 227641 -->
                                        <?php if(isset($telaAppView) && $telaAppView == true){ ?>
                                            <font color="#000000"><?php echo $materiais[$itr]['descricao']; ?></font>
                                        <?php }else{ ?>
                                            <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?= $materiais[$itr]['codigo'] ?>&amp;TipoGrupo=M&amp;ProgramaOrigem=<?= $programa ?>',700,370);">
                                                <font color="#000000"><?= $materiais[$itr]['descricao'] ?></font>
                                            </a>
                                        <?php } ?>
                                </td>
                                <!--  Coluna 3 = Cod CADUM-->
                                <td class="textonormal" style="text-align: center !important;">
                                    <?= $materiais[$itr]['codigo']?>
                                    <input value="<?= $materiais[$itr]['codigo'] ?>" name="MaterialCod[<?= $itr ?>]" type="hidden"/>
                                </td>
                                <!--  Coluna 4 = UND-->
                                <td class="textonormal" align="center">
                                    <?= $materiais[$itr]['unidade']?>
                                </td>
                                <!--  Coluna 5 = DESCRIÇÃO DETALHADA-->                            
                                <!--  Coluna 7 =  Quantidade -->
                                <?php
                                     //    kim cr#228067 CR#228616
                                    // if (! $ocultarCampoTRP) { 
                                     //if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="center" width="10">
                                    <?php   if (!$ocultarCamposEdicao) { ?>
                                        <input name="MaterialQuantidade[<?= $itr ?>]" class="dinheiro4casas" value="<?= $materiais[$itr]['quantidade'] ?>" <?= $ifVisualizacaoThenReadOnly?> maxlength="16" size="15" id="MaterialQuantidade[<?= $itr ?>]" type="text" onKeyUp="onChangeItemQuantidade1('<?= $itr ?>', 1); "/>
                                    <?php   } else {
                                           
                                                    echo $materiais[$itr]['quantidade'];
                                              
                                            }
                                    ?>
                                </td>
                                <?php  //}
                                    // }
                                ?>
                                <!--  Coluna 8 =  Valor Estimado -->
                                <?php 
                                    // kim cr#227641 & cr#228067 CR#228616
                                    // if (! $ocultarCampoTRP) {   
                                    //if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="center" width="10">
                                    <?php   if (!$ocultarCamposEdicao) { ?>
                                        <input name="MaterialValorEstimado[<?= $itr ?>]" id="MaterialValorEstimado[<?= $itr ?>]" size="16" maxlength="16" value="<?= $materiais[$itr]['valorEstimado'] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValor1('<?= $itr ?>', 1); " onBlur=" onChangeValorEstimadoItem1('<?= $itr ?>', 1)"/>
                                    <?php   } else {
                                                echo $materiais[$itr]['valorEstimado'];
                                               
                                            }
                                    ?>
                                </td>
                                <?PHP // } 
                                    // }
                                ?>
                                <?php  
                                    if (! $ocultarCampoExercicio) {
                                            // condicoes em que campos são desabilitados
                                            if ($ifVisualizacaoThenReadOnly) {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                            } elseif (moeda2float($materiais[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly = 'disabled';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
                                            } else {
                                                $ifVisualizacaoQtdeExercicioThenReadOnly = '';
                                                $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                            }
                                        }

                                        // kim cr#227641 & cr#228067  
                                    //if(empty($telaAppView)){
                                ?>
                                <td class="textonormal" align="right" width="10">
                                    <div class="MaterialValorTotal" id="MaterialValorTotal[<?= $itr ?>]"><?php
                                           echo converte_valor_estoques($ValorTotalItem); 
                                      ?></div>
                                </td>
                                    <?php // } ?>
                            </tr>
                <?php   }
                        
                        if ($QuantidadeMateriais <= 0) {
                            ?>
                            <tr>
                                <td class="textonormal itens_material" colspan="11">Nenhum item de material informado</td>
                            </tr>
                            <!-- FIM Dados ITENS DA SOLICITAÇÃO DE MATERIAL  -->
                            <?php
                        }
                ?>
                <?php 
                    // kim cr#227641 & cr#228067  
                    //if(empty($telaAppView)){
                ?>
                <tr>
                    <td class="titulo3" colspan="6">VALOR TOTAL DA SOLICITAÇÃO DE MATERIAL</td>
                    <td class="textonormal" align="right">
                        <div class="MaterialTotal" id="MaterialTotal">
                            <?php 
                                //    kim cr#228067
                                //if($ValorTotal == 0){
                                //    echo " - ";
                               //}else{
                                echo converte_valor_estoques($ValorTotal); 
                               //}
                            ?>
                        </div>
                    </td>
                </tr>
            <?php // } ?>
            </tbody>
        </table>
        <?php // Servicos  ?>
        <table id="scc_servico" summary="" bgcolor="bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
            <tbody>
                <tr>
                    <td colspan="8" class="titulo3" align="center" bgcolor="#75ADE6" valign="middle">ITENS DA SOLICITAÇÃO DE SERVIÇO</td>
                </tr>
                <?php   $qtdeColunas    = 7;
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
                        DESCRIÇÃO DO SERVIÇO
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        CÓD.RED. CADUS
                    </td>
                   
                    <?php 
                    // kim cr#227641 & cr#228067  
                 //   if(empty($telaAppView)){
                    ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7" width="50px">
                        QUANTIDADE
                    </td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        VALOR UNITÁRIO
                    </td>
             
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">
                        VALOR TOTAL
                    </td>
                    <?php //} ?>
                </tr>
                <!-- FIM Headers ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <?php   // Serviços do POST-----------------------------------
                        $Quantidade     = count($servicos);
                        $ValorTotalItem = 0;
                        $ValorTotal     = 0;

                        for ($itr = 0; $itr < $Quantidade; ++ $itr) {
                            $ValorTotalItem = moeda2float($servicos[$itr]['quantidade']) * moeda2float($servicos[$itr]['valorEstimado']);
                            $ValorTotal += $ValorTotalItem;
                            
                            if (! $ocultarCampoExercicio) {
                                $ValorTotalExercicio   = moeda2float($servicos[$itr]['quantidadeExercicio']) * moeda2float($servicos[$itr]['valorEstimado']);
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
                        <?= ($itr + 1)?>
                    </td>
                    <!--  Coluna 2 => Descricao -->
                    <td class="textonormal">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input name="ServicoCheck[<?= $itr ?>]" <?php if ($servicos[$itr]['check']) { echo 'checked'; } ?> <?= $ifVisualizacaoThenReadOnly?> type="checkbox">
                        <?php   } ?>
                        <a href="javascript:AbreJanela('../estoques/CadItemDetalhe.php?Material=<?= $servicos[$itr]['codigo'] ?>&amp;TipoGrupo=S&amp;ProgramaOrigem=<?= $programa ?>',700,370);">
                            <font color="#000000"><?= $servicos[$itr]['descricao'] ?></font>
                        </a>
                    </td>
                    <!--  Coluna 3 => Código Red -->
                    <td class="textonormal" align="center">
                        <?= $servicos[$itr]['codigo']?>
                        <input value="<?= $servicos[$itr]['codigo'] ?>" name="ServicoCod[<?= $itr ?>]" type="hidden">
                    </td>
                    <!--  Coluna 4 => Descrição Detalhada -->
                  
                    <!--  Coluna 5 => Quantidade -->
                    <?php 
                    // kim cr#227641 & cr#228067  
                  //  if(empty($telaAppView)){
                    ?>
                    <td class="textonormal" align="center">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input class="dinheiro4casas" value="<?= $servicos[$itr]['quantidade'] ?>" <?= $ifVisualizacaoThenReadOnly?> maxlength="16" size="11" name="ServicoQuantidade[<?= $itr ?>]" id="ServicoQuantidade[<?= $itr ?>]" type="text" onKeyUp="onChangeItemQuantidade1('<?= $itr ?>', TIPO_ITEM_SERVICO); "/>
                        <?php   } else {
                                    echo $servicos[$itr]['quantidade'];
                                }
                        ?>
                    </td>
                    <!--  Coluna 6 => Valor Extimado -->
                    <td class="textonormal" align="center" width="10">
                        <?php   if (! $ocultarCamposEdicao) { ?>
                            <input name="ServicoValorEstimado[<?= $itr ?>]" id="ServicoValorEstimado[<?= $itr ?>]" size="16" maxlength="16" value="<?= $servicos[$itr]['valorEstimado'] ?>" class="dinheiro4casas" type="text" onKeyUp="onChangeItemValor1('<?= $itr ?>', TIPO_ITEM_SERVICO); " onBlur="onChangeValorEstimadoItem1('<?= $itr ?>', TIPO_ITEM_SERVICO)"/>
                        <?php   } else {
                                    echo $servicos[$itr]['valorEstimado'];
                                }
                        ?>
                    </td>
                    <?php   if (! $ocultarCampoExercicio) {
                                // condicoes em que campos são desabilitados
                                if ($ifVisualizacaoThenReadOnly) {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                } elseif (moeda2float($servicos[$itr]['quantidade']) == 1 and ($QuantidadeMateriais + $QuantidadeServicos) == 1) {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = 'disabled';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = '';
                                } else {
                                    $ifVisualizacaoQtdeExercicioThenReadOnly         = '';
                                    $ifVisualizacaoTotalDemaisExerciciosThenReadOnly = 'disabled';
                                }
                            }
                            
                           ?>
                    <!--  Coluna 7 => Valor Total -->
                    <td class="textonormal" align="right" width="10">
                        <?php // echo $ValorTotalItem; ?>
                        <div class="ServicoValorTotal" id="ServicoValorTotal[<?= $itr ?>]">
                            <?php 
                                echo converte_valor_estoques($ValorTotalItem);
                            ?>
                        </div>
                    </td>
                <?php // } ?>
                </tr>
                <?php   }
                        
                        if ($Quantidade <= 0) {
                            ?>
                            <tr>
                                <td class="textonormal itens_servico" colspan="6">Nenhum item de serviço informado</td>
                            </tr>
                <?php   } ?>
                <!-- FIM Dados ITENS DA SOLICITAÇÃO DE SERVIÇO  -->
                <?php 
                    // kim cr#227641 & cr#228067  
                 //   if(empty($telaAppView)){
                 ?>
                <tr>
                    <td class="titulo3" colspan="5">VALOR TOTAL DA SOLICITAÇÃO DE SERVIÇO</td>
                    <td class="textonormal" align="right">
                        <div class="ServicoTotal" id="ServicoTotal">
                            <?php 
                               echo converte_valor_estoques($ValorTotal); 
                            ?>
                        </div>
                    </td>
                </tr>
                <?php //} ?>
                <?php   if (! $ocultarCamposEdicao) :
                            if (! $ocultarBotaoItem) :
                ?>
                <tr>
                    <td class="textonormal" colspan="7" align="center">
                        <input name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('CadIncluirItem.php?ProgramaOrigem=CadContratoAntigoIncluir&amp;PesqApenas=C', 700, 350);" type="button">
                        <input name="RetirarItem" value="Retirar Item" class="botao" onclick="javascript:mudaBotao('Retirar');" type="button">
                    </td>
                </tr>
                <?php       endif;
                        endif;
                ?>
            </tbody>
        </table></td>
    </td>
</tr>
</table>
<!-- <tr>
    <td class="textonormal" align="right"> -->
        <input type="hidden" name="InicioPrograma" value="1">
        <input type="hidden" name="RetirarDocs" value="<?= $RetirarDocs ?>">
        <input type="hidden" name="Solicitacao" value="<?= $Solicitacao ?>">
        <input type="hidden" name="Botao" value="">
        <input type="hidden" name="Foco" value="">
        <input type="hidden" name="SeqSolicitacao" value="<?= $Solicitacao ?>">

        <?php   if ($acaoPagina == ACAO_PAGINA_INCLUIR) { ?>
            <input type="button" name="Rascunho" value="Salvar Rascunho" class="botao" onClick="javascript:enviar('Rascunho');">
        <?php   } elseif ($acaoPagina == ACAO_PAGINA_MANTER) {
                    if ($situacaoSolicitacaoAtual == TIPO_SITUACAO_SCC_EM_CADASTRAMENTO) {
        ?>
            <input type="button" name="Rascunho" value="Manter Rascunho" class="botao" onClick="javascript:enviar('ManterRascunho');">
        <?php   } ?>
                    <input type="button" name="Manter" value="Manter Solicitação" class="botao" onClick="javascript:onButtonManter();">
        <?php   } elseif ($acaoPagina == ACAO_PAGINA_EXCLUIR) { ?>
                    <input type="button" name="Excluir" value="Cancelar Solicitação" class="botao" onClick="javascript:enviar('Excluir');">
        <?php   }                
                if ($acaoPagina == ACAO_PAGINA_EXCLUIR or $acaoPagina == ACAO_PAGINA_MANTER) {
        ?>
                    <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:onButtonVoltar();">
        <?php   } ?>
<!--     </td>
</tr>   -->          
                                       
</td>
</tr>
                            
                            
                            
                            
                                </table>        
                            </td>
                        </tr>
                        <input type="hidden" id="Destino" name="Destino" value="B">
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>

<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
<?php
exit;
}
?>