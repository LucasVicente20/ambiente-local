<?php
// --------------------------------------------------------------------------------
// Portal da DGCO
// Programa: RelEmissaoCHFPdf.php
// Autor: Roberta Costa
// Data: 19/10/04
// Objetivo: Programa de Impressão do Certificado de Habilitação de Firmas
// Alterado: Rossana Lira
// Data: 16/05/07 - Troca do nome fornecedor para firma
// Data: 09/07/07 - Exibir mensagem de fornecedor c/certidões fora do
// prazo de validade
// Alterado: Rodrigo Melo
// Data: 25/04/2011 - Exibir a certidão de falência, no final de certidões obrigatórias, devido a solicitação do usuário. Tarefa Redmine: 2205.
// - Retirar exibição de classes de fornecedores (manter apenas o grupo), devido a solicitação do usuário. Tarefa Redmine: 2205.
// Alterado: Rodrigo Melo
// Data: 02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
// - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
// OBS.: Tabulação 2 espaços
// --------------------------------------------------------------------------------

// Sempre vai buscar o programa no servidor #
header("Expires: 0");
header("Cache-Control: private");

// Executa o controle de segurança #
session_cache_limiter('private');
session_start();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Sequencial = $_GET['Sequencial'];
    $Mensagem = urldecode($_GET['Mensagem']);
    if ($Mensagem != "") {
        $Mensagem = "Atenção! " . $Mensagem;
    }
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapeBG();

// Informa o Título do Relatório #
$TituloRelatorio = "EXTRATO ATAS - HISTÓRICO PARTICIPANTE";

// Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("P", "mm", "A4");

// Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

// Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

// Adiciona uma página no documento #
$pdf->AddPage();

// Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 9);

$ata        = $_REQUEST['ata'];
$orgao      = $_REQUEST['orgao'];
$item       = $_REQUEST['item'];
$tipoItem   = $_REQUEST['tipoItem'];
$material   = $_REQUEST['tipoItem'] == 'M' ? $item : null;
$servico    = $_REQUEST['tipoItem'] == 'M' ? null : $item;
$seqItem    = $_REQUEST['seqItem'];

$sql = " select ata.*, iarpn.aitarporde, iarpn.cmatepsequ, iarpn.cservpsequ, iarpn.eitarpdescmat, iarpn.eitarpdescse,";
$sql .= " iarpn.aitarpqtor,iarpn.aitarpqtat from sfpc.tbataregistroprecointerna ata";
$sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
$sql .= " on iarpn.carpnosequ = ata.carpnosequ";
$sql .= " inner join sfpc.tbparticipanteitematarp piarp";
$sql .= " on piarp.carpnosequ = ata.carpnosequ";
$sql .= " and piarp.citarpsequ = iarpn.citarpsequ";
$sql .= " where ata.carpnosequ = " . $ata;

if ($material != null) {
    $sql .= " and iarpn.cmatepsequ=" . $material;
} else {
    $sql .= " and iarpn.cservpsequ=" . $servico;
}

$sql .= "   AND iarpn.citarpsequ=" . $seqItem;

if ($_REQUEST['tipoItem'] == 'S') {
    $servico = $_REQUEST['item'];
} else {
    $material = $_REQUEST['item'];
}
$resultados = array();
$resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
while ($resultado->fetchInto($extratosAta, DB_FETCHMODE_OBJECT)) {
    array_push($resultados, $extratosAta);
}

foreach ($resultados as $ata) {
    $tipoServico = $ata->eservpdesc != null ? 'CADUS' : 'CADUM';
    $valortem = $ata->eservpdesc == null ? $ata->cmatepsequ : $ata->eservpdesc;
    $descricaoCompletaItem = $ata->eservpdesc != null ? $ata->eitarpdescmat : $ata->eitarpdescse;
    
    if ($ata->cmatepsequ != null) {
        $sql = "select mp.ematepdesc from sfpc.tbmaterialportal mp where mp.cmatepsequ=" . $ata->cmatepsequ;
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT);
    } else {
        $sql = "select sp.eservpdesc from sfpc.tbservicoportal sp where sp.eservpdesc=" . $ata->eservpdesc;
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT);
    }
    $itemDescricao = $ata->cmatepsequ != null ? $itemAta->ematepdesc : $itemAta->eservpdesc;
    $quantidade = $ata->aitarpqtat > 0 ? $ata->aitarpqtat : $ata->aitarpqtor;
    
    $pdf->ln(03);
    $pdf->Cell(63, 5, "Nº da Ata", 1, 0, 'L', 1);
    $pdf->Cell(100, 5, "37/052.teste/2015", 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Ordem do Item', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, $extratosAta->aitarporde, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, $tipoServico, 1, 0, 'L', 1);
    $pdf->Cell(100, 5, $valortem, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Descrição', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, $itemDescricao, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Descrição Completa', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, $descricaoCompletaItem, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Quantidade', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, intval($quantidade), 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Quantidade utilizada Gestor/Participante', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, '{QUANGP}', 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'Saldo da quantidade do Gestor/Participante', 1, 0, 'L', 1);
    $pdf->Cell(100, 5, '105', 1, 0, 'L', 0);
    $pdf->ln(10);
}

/* Consulta a quantidade de cada membro */

$sql = "select iarpn.aitarpqtat, parp.corglicodi,iarpn.aitarpqtor from sfpc.tbparticipanteatarp parp";
$sql .= " inner join sfpc.tbparticipanteitematarp piarp";
$sql .= " on piarp.carpnosequ = parp.carpnosequ";
$sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
$sql .= " on piarp.carpnosequ = iarpn.carpnosequ";
$sql .= " and piarp.citarpsequ = iarpn.citarpsequ";
$sql .= " where parp.carpnosequ =" . $_REQUEST['ata'];
if ($tipoItem == 'M') {
    $sql .= " and iarpn.cmatepsequ =$material";
} else {
    $sql .= " and iarpn.cservpsequ =$servico";
}

$resultadosQuantidade = array();
$resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
while ($resultado->fetchInto($sccProcesso, DB_FETCHMODE_OBJECT)) {
    array_push($resultadosQuantidade, $sccProcesso);
}

/* Consulta dados da SCC do participante */
$sql = "select sum(isc.aitescqtso) as quantidadeOrgao, sc.csolcosequ, sc.tsolcodata, ol.eorglidesc,sc.corglicod1,isc.aitescqtso,isc.aitescqtex  from sfpc.tbsolicitacaocompra sc";
$sql .= " inner join sfpc.tbitemsolicitacaocompra isc";
$sql .= " on isc.csolcosequ = sc.csolcosequ";
$sql .= " inner join sfpc.tborgaolicitante ol";
$sql .= " on ol.corglicodi = sc.corglicod1";
$sql .= " where sc.carpnosequ =" . $_REQUEST['ata'];
if ($orgao != null) {
    $sql .= " and sc.corglicod1 =" . $orgao;
}
if ($tipoItem == 'M') {
    $sql .= " and isc.cmatepsequ =" . $material;
} else {
    $sql .= " and isc.cservpsequ =" . $servico;
}
$sql .= " group by sc.csolcosequ,sc.corglicod1,ol.eorglidesc,sc.tsolcodata,isc.aitescqtso,isc.aitescqtex";

$resultados = array();
$resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
while ($resultado->fetchInto($sccProcesso, DB_FETCHMODE_OBJECT)) {
    array_push($resultados, $sccProcesso);
}

$plotou = false;
$sccGestorPadrao = null;
foreach ($resultados as $sccGestor) {
    $sccGestorPadrao = $sccGestor;
    /* Escreve dados do gestor */
    if (! $plotou) {
        $pdf->Cell(163, 5, $sccGestor->eorglidesc . '- GESTOR ', 1, 0, 'C', 1);
        $pdf->ln();
        $pdf->Cell(60, 5, 'Nº SCC', 1, 0, 'C', 1);
        $pdf->Cell(53, 5, 'Data SCC', 1, 0, 'C', 1);
        $pdf->Cell(50, 5, 'Quantidade Utilizada', 1, 0, 'C', 1);
        $pdf->ln();
        $plotou = true;
    }
    
    $itemSumarizacao = 0;
    $itemQuantidadeUtilizada = $sccGestor->aitescqtex > 0 ? $sccGestor->aitescqtex : $sccGestor->aitescqtso;
    $pdf->Cell(60, 5, getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $sccGestor->csolcosequ), 1, 0, 'C', 0);
    $pdf->Cell(53, 5, date("d/m/Y", strtotime($sccGestor->tsolcodata)), 1, 0, 'C', 0);
    $pdf->Cell(50, 5, intval($itemQuantidadeUtilizada), 1, 0, 'C', 0);
    $pdf->ln();
    $itemSumarizacao += $itemQuantidadeUtilizada;
}
$qtdSolicitadaAta = 0;
foreach ($resultadosQuantidade as $quantidade) {
    if ($quantidade->corglicodi == $sccGestor->corglicod1) {
        $itemQuantidade = $quantidade->aitarpqtat > 0 ? $quantidade->aitarpqtat : $quantidade->aitarpqtor;
        $qtdSolicitadaAta += $itemQuantidade;
    }
}

$valorUltimaColuna = 'QUANTIDADE SOLICITADA NO PROCESSO: ' . intval($qtdSolicitadaAta) . '                      SALDO DISPONÍVEL: ' . ($qtdSolicitadaAta - $itemSumarizacao);
$pdf->Cell(163, 5, 'TOTAL QUANTIDADE UTILIZADA: ' . $itemSumarizacao, 1, 0, 'L', 0);
$pdf->ln();
$pdf->Cell(163, 5, $valorUltimaColuna, 1, 0, 'L', 0);
$pdf->ln();

$pdf->Output();
