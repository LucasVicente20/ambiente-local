<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelTramitacaoMonitoramento.php
#-------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 14/02/2022
# Objetivo: CR #254672
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Executa o controle de segurança #
session_start();
$pesquisa = $_SESSION['origemPesquisa'];
$pesquisaAgente = $_SESSION['relTramitacaoAgente'];

Seguranca();

$db = Conexao();

# Variáveis com o global off #
$Critica  = $_GET['Critica'];
$Mensagem = urldecode($_GET['Mensagem']);
$Mens     = $_GET['Mens'];
$Tipo     = $_GET['Tipo'];
$Sequencial   = $_GET['protsequ'];

$numprotocolo          = $_GET['numprotocolo'];
$anoprotocolo          = $_GET['anoprotocolo'];
//dados para voltar
$numprotocoloRetorno   = $_POST['numprotocoloRetorno'];
$anoprotocoloRetorno   = $_POST['anoprotocoloRetorno'];
$orgaoRetorno          = $_GET['orgao'];
$objetoRetorno         = $_GET['objeto'];
$numerociRetorno       = $_GET['numeroci'];
$numeroOficioRetorno   = $_GET['numeroOficio'];
$numeroSccRetorno      = $_GET['numeroScc'];
$proLicitatorioRetorno = $_GET['proLicitatorio'];
$acaoRetorno           = $_GET['acao'];
$origemRetorno         = $_GET['origem'];
$DataIniRetorno    = $_GET['DataIni'];
$DataFimRetorno    = $_GET['DataFim'];
$retornoEntrada    = $_GET['retornoEntrada'];
$retornoSaida    = $_GET['retornoSaida'];
$usuarioAlterado = $_GET['usuarioAlterado'];
$tramitacaoExcluida = $_GET['tramitacaoExcluida'];
$origemTramitacao = $_GET['origemTramitacao'];

if(empty($Sequencial) || empty($numprotocolo) ||  empty($anoprotocolo)) {
    header('Location: MapaTramitacaoSelecionar.php');
    exit();
}

if($botao == 'Voltar') {
    header('Location: MapaTramitacaoSelecionar.php');
    exit();
}

$protocolo  = getProtocoloDetalhe($Sequencial);
$processo   = getProcesso($Sequencial, $numprotocolo, $anoprotocolo);
$passos     = getTramitacaoPassos($Sequencial);

if($protocolo[12]){
    $nnumeroScc = getNumeroSolicitacaoCompra($db, $protocolo[12]);
}else{
    $nnumeroScc = '';
}
////($passos);exit;
$titles = array();
$cacteresPorLinha = 21;
$maiorTítuloLinhas = 1;
if(!empty($passos)) {
    $passos = array_reverse($passos);

    // Título vertical e maior título
    foreach ($passos as $key => $passo) {
        if (in_array($passo[0], $titles)) {
            continue;
        }

        $linhas = strlen($passo[0]) / $cacteresPorLinha;
        if($linhas > $maiorTítuloLinhas) {
            $maiorTítuloLinhas = (int) $linhas;
        }

        $cleanTitle = cleanTitle($passo[0], $cacteresPorLinha);
        $titles[] = $passo[0];
    }
}
$prazo = $passo[4];
function cleanTitle ($title, $size) {
    $t = '';
    $breakNextSpace = false;
    for($i=0; $i < strlen($title); $i++) {
        if(($i > 0 && ($i % $size) == 0) || $breakNextSpace) {
            if($title[$i] == ' ') {
                $t .= '\n';
                $breakNextSpace = false;
            } else {
                $t .= mb_substr($title[$i],0, 1);
                $breakNextSpace = true;
            }
        } else {
            $t .= mb_substr($title[$i],0, 1);
        }
    }

    return $t;
}

# Critica dos Campos #
if( $Critica == 1 ){
    $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissao == "" ) {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Licitacao.LicitacaoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    }else{
        $NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissao);
        $Processo             = substr($NProcessoAnoComissao[0] + 10000,1);
        $ProcessoAno          = $NProcessoAnoComissao[1];
        $ComissaoCodigo       = $NProcessoAnoComissao[2];
        $novaTela 			  = $NProcessoAnoComissao[3];
        if($novaTela=="1"){
            $Url = "CadLicitacaoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }else{
            $Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }
        if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
        header("location: ".$Url);
        exit;
    }
}


if($usuarioAlterado == 1){
    $Mens      = 1;
    $Tipo      = 1;
    $Mensagem .= "<a href=\"#\" class=\"titulo2\">Usuário alterado com sucesso</a>";
}

if($tramitacaoExcluida == 1){
    $Mens      = 1;
    $Tipo      = 1;
    $Mensagem .= "<a href=\"#\" class=\"titulo2\">Último passo excluído com sucesso</a>";
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" src="../import/mxGraph/js/mxClient.min.js?v=<?php echo date('i-s'); ?>" type="text/javascript"></script>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0" onload="main(document.getElementById('graphContainer'))">
<script language="JavaScript" src="../menu.js"></script>
<!-- Example code -->
<script type="text/javascript">
    // Defines an icon for creating new connections in the connection handler.
    // This will automatically disable the highlighting of the source vertex.
    mxConnectionHandler.prototype.connectImage = new mxImage('images/connector.gif', 16, 16);

    // Program starts here. Creates a sample graph in the
    // DOM node with the specified ID. This function is invoked
    // from the onLoad event handler of the document (see below).
    function main(container)
    {
        // Checks if browser is supported
        if (!mxClient.isBrowserSupported())
        {
            // Displays an error message if the browser is
            // not supported.
            mxUtils.error('Browser is not supported!', 200, false);
        }
        else
        {
            // Creates a wrapper editor around a new graph inside
            // the given container using an XML config for the
            // keyboard bindings
            var config = mxUtils.load(
                'editors/config/keyhandler-commons.xml').
            getDocumentElement();
            var editor = new mxEditor(config);
            editor.setGraphContainer(container);
            var graph = editor.graph;
            var model = graph.getModel();

            // Auto-resizes the container
            graph.border = 80;
            graph.getView().translate = new mxPoint(graph.border/2, graph.border/2);
            graph.setResizeContainer(true);
            graph.graphHandler.setRemoveCellsFromParent(false);
            graph.graphHandler.setEnabled(false);

            graph.setCellsSelectable(false);
            graph.setConnectable(false);

            // Disables folding
            graph.isCellFoldable = function(cell, collapse) {
                return false;
            };

            // Changes the default vertex style in-place
            var style = graph.getStylesheet().getDefaultVertexStyle();
            style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_SWIMLANE;
            style[mxConstants.STYLE_VERTICAL_ALIGN] = 'middle';
            style[mxConstants.STYLE_LABEL_BACKGROUNDCOLOR] = 'white';
            style[mxConstants.STYLE_FONTSIZE] = 10;
            style[mxConstants.STYLE_STARTSIZE] = <?php echo 20 * $maiorTítuloLinhas; ?>;
            style[mxConstants.STYLE_HORIZONTAL] = false;
            style[mxConstants.STYLE_FONTCOLOR] = 'black';
            style[mxConstants.STYLE_STROKECOLOR] = 'black';
            delete style[mxConstants.STYLE_FILLCOLOR];

            style = mxUtils.clone(style);
            style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_RECTANGLE;
            style[mxConstants.STYLE_FONTSIZE] = 10;
            style[mxConstants.STYLE_ROUNDED] = true;
            style[mxConstants.STYLE_HORIZONTAL] = true;
            style[mxConstants.STYLE_VERTICAL_ALIGN] = 'middle';
            delete style[mxConstants.STYLE_STARTSIZE];
            style[mxConstants.STYLE_LABEL_BACKGROUNDCOLOR] = 'none';
            graph.getStylesheet().putCellStyle('process', style);

            style = mxUtils.clone(style);
            style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_ELLIPSE;
            style[mxConstants.STYLE_PERIMETER] = mxPerimeter.EllipsePerimeter;
            delete style[mxConstants.STYLE_ROUNDED];
            graph.getStylesheet().putCellStyle('state', style);

            style = mxUtils.clone(style);
            style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_RHOMBUS;
            style[mxConstants.STYLE_PERIMETER] = mxPerimeter.RhombusPerimeter;
            style[mxConstants.STYLE_VERTICAL_ALIGN] = 'top';
            style[mxConstants.STYLE_SPACING_TOP] = 40;
            style[mxConstants.STYLE_SPACING_RIGHT] = 64;
            graph.getStylesheet().putCellStyle('condition', style);

            style = mxUtils.clone(style);
            style[mxConstants.STYLE_SHAPE] = mxConstants.SHAPE_DOUBLE_ELLIPSE;
            style[mxConstants.STYLE_PERIMETER] = mxPerimeter.EllipsePerimeter;
            style[mxConstants.STYLE_SPACING_TOP] = 28;
            style[mxConstants.STYLE_FONTSIZE] = 14;
            style[mxConstants.STYLE_FONTSTYLE] = 1;
            delete style[mxConstants.STYLE_SPACING_RIGHT];
            graph.getStylesheet().putCellStyle('end', style);

            style = graph.getStylesheet().getDefaultEdgeStyle();
            style[mxConstants.STYLE_EDGE] = mxEdgeStyle.ElbowConnector;
            style[mxConstants.STYLE_ENDARROW] = mxConstants.ARROW_BLOCK;
            style[mxConstants.STYLE_ROUNDED] = true;
            style[mxConstants.STYLE_FONTCOLOR] = 'black';
            style[mxConstants.STYLE_STROKECOLOR] = 'black';

            style = mxUtils.clone(style);
            style[mxConstants.STYLE_DASHED] = true;
            style[mxConstants.STYLE_ENDARROW] = mxConstants.ARROW_OPEN;
            style[mxConstants.STYLE_STARTARROW] = mxConstants.ARROW_OVAL;
            graph.getStylesheet().putCellStyle('crossover', style);

            // Installs double click on middle control point and
            // changes style of edges between empty and this value
            graph.alternateEdgeStyle = 'elbow=vertical';

            // Adds automatic layout and various switches if the
            // graph is enabled
            if (graph.isEnabled())
            {
                // Allows new connections but no dangling edges
                graph.setConnectable(true);
                graph.setAllowDanglingEdges(false);

                // End-states are no valid sources
                var previousIsValidSource = graph.isValidSource;

                graph.isValidSource = function(cell)
                {
                    if (previousIsValidSource.apply(this, arguments))
                    {
                        var style = this.getModel().getStyle(cell);

                        return style == null || !(style == 'end' || style.indexOf('end') == 0);
                    }

                    return false;
                };

                // Start-states are no valid targets, we do not
                // perform a call to the superclass function because
                // this would call isValidSource
                // Note: All states are start states in
                // the example below, so we use the state
                // style below
                graph.isValidTarget = function(cell)
                {
                    var style = this.getModel().getStyle(cell);

                    return !this.getModel().isEdge(cell) && !this.isSwimlane(cell) &&
                        (style == null || !(style == 'state' || style.indexOf('state') == 0));
                };

                // Allows dropping cells into new lanes and
                // lanes into new pools, but disallows dropping
                // cells on edges to split edges
                graph.setDropEnabled(true);
                graph.setSplitEnabled(false);

                // Returns true for valid drop operations
                graph.isValidDropTarget = function(target, cells, evt)
                {
                    if (this.isSplitEnabled() && this.isSplitTarget(target, cells, evt))
                    {
                        return true;
                    }

                    var model = this.getModel();
                    var lane = false;
                    var pool = false;
                    var cell = false;

                    // Checks if any lanes or pools are selected
                    for (var i = 0; i < cells.length; i++)
                    {
                        var tmp = model.getParent(cells[i]);
                        lane = lane || this.isPool(tmp);
                        pool = pool || this.isPool(cells[i]);

                        cell = cell || !(lane || pool);
                    }

                    return !pool && cell != lane && ((lane && this.isPool(target)) ||
                        (cell && this.isPool(model.getParent(target))));
                };

                // Adds new method for identifying a pool
                graph.isPool = function(cell)
                {
                    var model = this.getModel();
                    var parent = model.getParent(cell);

                    return parent != null && model.getParent(parent) == model.getRoot();
                };

                // Changes swimlane orientation while collapsed
                graph.model.getStyle = function(cell)
                {
                    var style = mxGraphModel.prototype.getStyle.apply(this, arguments);

                    if (graph.isCellCollapsed(cell))
                    {
                        if (style != null)
                        {
                            style += ';';
                        }
                        else
                        {
                            style = '';
                        }

                        style += 'horizontal=1;align=left;spacingLeft=14;';
                    }

                    return style;
                };

                // Keeps widths on collapse/expand
                var foldingHandler = function(sender, evt)
                {
                    var cells = evt.getProperty('cells');

                    for (var i = 0; i < cells.length; i++)
                    {
                        var geo = graph.model.getGeometry(cells[i]);

                        if (geo.alternateBounds != null)
                        {
                            geo.width = geo.alternateBounds.width;
                        }
                    }
                };

                graph.addListener(mxEvent.FOLD_CELLS, foldingHandler);
            }

            // Applies size changes to siblings and parents
            new mxSwimlaneManager(graph);

            // Creates a stack depending on the orientation of the swimlane
            var layout = new mxStackLayout(graph, false);

            // Makes sure all children fit into the parent swimlane
            layout.resizeParent = true;

            // Applies the size to children if parent size changes
            layout.fill = true;

            // Only update the size of swimlanes
            layout.isVertexIgnored = function(vertex)
            {
                return !graph.isSwimlane(vertex);
            }

            // Keeps the lanes and pools stacked
            var layoutMgr = new mxLayoutManager(graph);

            layoutMgr.getLayout = function(cell)
            {
                if (!model.isEdge(cell) && graph.getModel().getChildCount(cell) > 0 &&
                    (model.getParent(cell) == model.getRoot() || graph.isPool(cell)))
                {
                    layout.fill = graph.isPool(cell);

                    return layout;
                }

                return null;
            };

            // Gets the default parent for inserting new cells. This
            // is normally the first child of the root (ie. layer 0).
            var parent = graph.getDefaultParent();

            // Adds cells to the model in a single step
            model.beginUpdate();
            try
            {

                // Agentes
                <?php $x = 220; $box = 280; ?>
                <?php
                    foreach ($titles as $key => $title) { ?>
                        var title_<?php echo $key; ?> = graph.insertVertex(null, null, '<?php echo cleanTitle($title, $cacteresPorLinha); ?>', 0, 0, 640, 180);
                        title_<?php echo $key; ?>.setConnectable(false);
                <?php } ?>

                // Início
                var start1 = graph.insertVertex(title_0, null, null, 30, 75, 30, 30, 'state');

                // Tramitações
                <?php
                    foreach ($passos as $key => $passo) {
                        $last = array_search($passo[0], $titles);
                        $entradaHora = substr($passo[3],11,5);
                        $entradaHora = substr($passo[3],8,2).'/'.substr($passo[3],5,2).'/'.substr($passo[3],0,4)." ".$entradaHora;

                        $diffDias = 0;
                        $now = date('Y-m-d');
                        $saida = substr($passo[5],0,10);
                        $saidaHora = substr($passo[5],11,5);

                        $arrEntrada = explode("-",substr($passo[3],0,10));
                        $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];

                        $previsto = calcularTramitacaoSaida($dataHoraEntrada, $passo[4]);
                        $arrPrevisto = explode("/",$previsto);
                        $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];

                        if(!empty($passo[5])){
                            $saidaHora = substr($passo[5],11,5);
                            $saidaHora = substr($passo[5],8,2).'/'.substr($passo[5],5,2).'/'.substr($passo[5],0,4)." ".$saidaHora;
                        }
                    ?>
                    var step<?php echo $key; ?> = graph.insertVertex(title_<?php echo array_search($passo[0], $titles); ?>, null, '<?php echo $key + 1; ?>º Passo\n' +
                        '<?php echo ucfirst(strtolower2(cleanTitle($passo[2], 41))); ?>\n' +

                        <?php
                            $usuarioNome = '';
                            if($passo[8] == 0 && $passo[9] == 'I') {
                                $usuarioNome = $passo[0];
                            } elseif($passo[8] > 0) {
                                $usuarioNome = $passo[1];
                            } else {
                                $usuarioNome = 'ÓRGÃO EXTERNO';
                            }
                        ?>
                        '<?php echo $usuarioNome; ?>\n' +

                        'Data de Entrada: <?php echo $entradaHora; ?>\n' +

                        <?php if(!empty($passo[5])) { ?>
                        'Data de Saída: <?php echo $saidaHora?>\n' +
                        <?php } ?>

                        <?php
                            $atraso = false;
                            if($saida) {
                                if (strtotime($saida) > strtotime($dataPrevista)) {
                                    $atraso = true;
                                    $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $saida);
                                    $dia = ($diffDias > 1) ? ' dias' : ' dia';
                                }
                            } else {
                                $atual = date('Y-m-d');
                                $atraso = true;
                                if(strtotime($atual) > strtotime($dataPrevista)) {
                                    $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $atual);
                                    $dia = ($diffDias > 1) ? ' dias' : ' dia';
                                }
                            }
                        ?>

                        <?php if($atraso) { ?>
                        'Atraso: <?php echo $diffDias . $dia; ?>\n' +
                        <?php } ?>

                        'prazo: <?php echo $passo[4]; ?> dia(s)',

                        <?php echo $x; ?>, 25, 225, 130, 'process');
                        <?php $x = $x + $box; ?>
                <?php } ?>
                           
                // Fluxo
                <?php for ($i=0; $i < count($passos) - 1; $i++) { ?>
                    graph.insertEdge(title_0, null, null, step<?php echo $i; ?>, step<?php echo $i+1; ?>);
                <?php } ?>

                graph.insertEdge(title_0, null, null, start1, step0);

                // Final
                <?php if($passos[count($passos) - 1][19] == 'S') { ?>
                var end1 = graph.insertVertex(title_<?php echo $last; ?>, null, 'A', <?php echo $x; ?>, 75, 30, 30, 'end');
                graph.insertEdge(title_0, null, null, step<?php echo count($passos) - 1; ?>, end1);
                <?php } ?>
            }
            finally
            {
                // Updates the display
                model.endUpdate();
            }
        }
    };
</script>
<script language="javascript" type="">
    <?php MenuAcesso(); ?>
    function enviar(valor){
        document.mapaTramitacao.Botao.value=valor;
        document.mapaTramitacao.submit();
    }
</script>
<script language="JavaScript">Init();</script>
<form action="mapaTramitacao.php" method="POST" name="mapaTramitacao" enctype="multipart/form-data" >
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Relatórios > Fluxo do Protocolo
            </td>
        </tr>
        <!-- Fim do Caminho-->

        <!-- Erro -->
        <?php if ( $Mens == 1 ) {?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
        <?php } ?>
        <!-- Fim do Erro -->

        <!-- Corpo -->
        <tr>
            <td width="150"></td>
            <td class="textonormal" >
                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" colspan="22" bgcolor="#75ADE6" valign="middle" class="titulo3">
                            FLUXO DO PROTOCOLO
                        </td>
                    </tr>
                    <tr>
                        <td colspan="22">
                            <table border="0" summary="">
                                <tr>
                                    <td>
                                        <div id="graphContainer"
                                             style="overflow:hidden;width:600px;height:400px;border: gray dotted 1px;cursor:default;">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal" align="right" colspan="22">
                            <input type="button" value="Voltar" onclick="javascript:enviar('Voltar');" class="botao">
                            <input type="hidden" name="Botao" value="" />
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
