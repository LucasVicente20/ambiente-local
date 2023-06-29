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
# Alterado: Caio Coutinho
# Data: 04/07/2018
# Objetivo: Tarefa Redmine #198149
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 05/07/2018
# Objtivo: Tarefa Redine #194536
#------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data: 11/07/2018
# Objtivo: Tarefa Redine #198981
#----------------------------------------

#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     03/09/2018
# Objetivo: Tarefa Redmine 201047
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     03/09/2018
# Objetivo: Tarefa Redmine 201674
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     24/10/2018
# Objetivo: Tarefa Redmine 205786
#-------------------------------------------------------------------------

// 220038--

// Sempre vai buscar o programa no servidor #
header("Expires: 0");
header("Cache-Control: private");

// Executa o controle de segurança #
session_cache_limiter('private');
session_start();

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    //$Sequencial = $_GET['Sequencial'];
    //$Mensagem = urldecode($_GET['Mensagem']);
    //if ($Mensagem != "") {
    //    $Mensagem = "Atenção! " . $Mensagem;
    //}
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Fução exibe o Cabeçalho e o Rodapé #
//CabecalhoRodapeBG();
CabecalhoRodapePaisagem();

// Informa o Título do Relatório #
$TituloRelatorio = "EXTRATO ATAS - HISTÓRICO PARTICIPANTE";

// Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("L", "mm", "A4");

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
$tipo       = $_REQUEST['tipo'];
$seqItem    = $_REQUEST['seqItem'];
$tipoItem   = $_REQUEST['tipoItem'];
$material   = $_REQUEST['tipoItem'] == 'M' ? $item : null;
$servico    = $_REQUEST['tipoItem'] == 'M' ? null : $item;

// $sql = " select ata.*, iarpn.aitarporde, iarpn.cmatepsequ, iarpn.cservpsequ, iarpn.eitarpdescmat, iarpn.eitarpdescse,";
// $sql .= " iarpn.aitarpqtor,iarpn.aitarpqtat from sfpc.tbataregistroprecointerna ata";
// $sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
// $sql .= " on iarpn.carpnosequ = ata.carpnosequ";
// // $sql .= " inner join sfpc.tbparticipanteitematarp piarp";
// // $sql .= " on piarp.carpnosequ = ata.carpnosequ";
// // $sql .= " and piarp.citarpsequ = iarpn.citarpsequ";
// $sql .= " where ata.carpnosequ = " . $ata;
// if ($material != null) {
//     $sql .= " and iarpn.cmatepsequ=" . $material;
// } else {
//     $sql .= " and iarpn.cservpsequ=" . $servico;
// }

$itemAtaInterna = getQtdTotalOrgaoCaronaInterna(ClaDatabasePostgresql::getConexao(), null, $ata, $seqItem);
$itemAtaExterna = getQtdTotalOrgaoCaronaExterna(ClaDatabasePostgresql::getConexao(), $ata, $seqItem);
$itemAtaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(
    ClaDatabasePostgresql::getConexao(), $ata, $seqItem
);
$fatorMaxCarona = getFatorQtdMaxCarona(ClaDatabasePostgresql::getConexao());
$saldoGeralCaronaAta = ($itemAtaInterna + $itemAtaExterna + $itemAtaInternaIncDir);   
$fatorMaxCarona = getFatorQtdMaxCarona(ClaDatabasePostgresql::getConexao());
$qtdTotalMaxCarona = getQtdTotalItensAta(ClaDatabasePostgresql::getConexao(), $ata, $seqItem);

function sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem)
{
    
    $sql = "    select sol.csolcosequ, sol.asolcoanos, citelpsequ, itel.cmatepsequ, itel.cservpsequ, itel.aitelpqtso from sfpc.tbsolicitacaolicitacaoportal solic, sfpc.tbsolicitacaocompra sol, sfpc.tbitemlicitacaoportal itel, sfpc.tbitemsolicitacaocompra ites
                where 1=1
                AND solic.clicpoproc = $clicpoproc 
                AND solic.alicpoanop = $alicpoanop 
                AND solic.cgrempcodi = $cgrempcodi 
                AND solic.ccomlicodi = $ccomlicodi
                AND solic.corglicodi = $orgaoGestor                    
                AND solic.clicpoproc = itel.clicpoproc
                AND solic.alicpoanop = itel.alicpoanop
                AND solic.cgrempcodi = itel.cgrempcodi
                AND solic.ccomlicodi = itel.ccomlicodi
                AND solic.corglicodi = itel.corglicodi
                AND sol.corglicodi   = $orgaoAgrupamento
                AND sol.csolcosequ   = solic.csolcosequ
                AND sol.csolcosequ   = ites.csolcosequ
                AND itel.citelpsequ  = ites.citescsequ ";                

    if ($tipoItem == 'M') {
        if(is_object($codigoItem)) {
            $codigoItem = $codigoItem->cmatepsequ;
        }
        $sql .= " and itel.cmatepsequ = " . $codigoItem;
    } else {
        if(is_object($codigoItem)) {
            $codigoItem = $codigoItem->cservpsequ;
        }
        $sql .= " and itel.cservpsequ = " . $codigoItem;
    }

    return $sql;
}

function sqlConsultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
{
    $sql  = "SELECT *, ipa.*, i.*, m.*, ump.*, s.*, sc.corglicodi as orgao_agrupamento, sc.corglicodi as orgao_gestor ";
    $sql .= "         FROM ";
    $sql .= "             sfpc.tbparticipanteatarp arpi ";
    $sql .= "             INNER JOIN sfpc.tbparticipanteitematarp ipa ON  ";
    $sql .= "               ipa.carpnosequ = arpi.carpnosequ  ";
    $sql .= "               AND ipa.corglicodi = arpi.corglicodi ";
    $sql .= "             INNER JOIN sfpc.tbitemataregistropreconova i ON ";
    $sql .= "               i.carpnosequ = arpi.carpnosequ ";
    $sql .= "               AND i.citarpsequ = ipa.citarpsequ ";
    $sql .= "             LEFT OUTER JOIN sfpc.tbmaterialportal m ON ";
    $sql .= "               i.cmatepsequ = m.cmatepsequ  ";
    $sql .= "             LEFT OUTER JOIN sfpc.tbunidadedemedida ump ON ";
    $sql .= "               ump.cunidmcodi = m.cunidmcodi  ";
    $sql .= "             LEFT OUTER JOIN sfpc.tbservicoportal s ON ";
    $sql .= "               i.cservpsequ = s.cservpsequ ";
    $sql .= "             INNER JOIN sfpc.tborgaolicitante o ON ";
    $sql .= "               o.corglicodi = arpi.corglicodi ";
    $sql .= "             left join sfpc.tbataregistroprecointerna sc on ";
    $sql .= "               sc.carpnosequ = arpi.carpnosequ ";
    $sql .= "               and sc.corglicodi = arpi.corglicodi  ";

    $sql .= "         WHERE ";
    $sql .= "             i.carpnosequ = %d ";
    
    if ($tipoItem == 'M') {
        $sql .= " and i.cmatepsequ = " . $itemCodigo;
    } else {
        $sql .= " and i.cservpsequ = " . $itemCodigo;
    }

    $sql .= " and i.citarpsequ = " . $seqItem;

    $sql .= "         order by ipa.corglicodi asc  ";

    return sprintf($sql, $carpnosequ);
}

function consultarQuantidadeSolicitadoDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem)
{
    $sql = sqlConsultarQtantidadeSolicitadaDoProcesso($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $orgaoGestor, $orgaoAgrupamento, $tipoItem, $codigoItem);
    $resultado = ClaDatabasePostgresql::executarSQL($sql);
    
    ClaDatabasePostgresql::hasError($resultado);
    
    return $resultado;
}

function consultarTipoControle($ata) {
    $sql = "
        SELECT arpn.farpnotsal 
        FROM sfpc.tbataregistropreconova arpn
        WHERE arpn. carpnosequ = %d";
    $resultado = ClaDatabasePostgresql::executarSQL(sprintf($sql, $ata));

    return $resultado;
}

$sql = "SELECT
    ata.*,
    iarpn.aitarporde,
    iarpn.cmatepsequ,
    iarpn.cservpsequ,
    iarpn.eitarpdescmat,
    iarpn.eitarpdescse,
    iarpn.aitarpqtor,
    iarpn.citarpnuml,
    iarpn.aitarpqtat,
    iarpn.vitarpvatu,
    iarpn.vitarpvori,
    (CASE
       when iarpn.cmatepsequ IS NOT NULL
           then (select mp.ematepdesc from sfpc.tbmaterialportal mp
           where mp.cmatepsequ = iarpn.cmatepsequ)
       when iarpn.cservpsequ IS NOT NULL
           then (select sp.eservpdesc from sfpc.tbservicoportal sp
           where sp.cservpsequ = iarpn.cservpsequ)
    END) AS descricaoItem
FROM
    sfpc.tbataregistroprecointerna ata INNER JOIN sfpc.tbitemataregistropreconova iarpn
        ON iarpn.carpnosequ = ata.carpnosequ 
                
WHERE";

$sql .= " ata.carpnosequ = " . $ata;

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

$sqlResultadoItem = " SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ol.eorglidesc, SOL.clicpoproc, SOL.alicpoanop, SOL.cgrempcodi, SOL.ccomlicodi, SOL.corglicodi, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor, ITEMS.vitescunit
                      FROM  sfpc.tbataregistroprecointerna ATAI, 
                            sfpc.tbitemataregistropreconova ITEMA,
                            sfpc.tbsolicitacaocompra SOL, 
                            sfpc.tbitemsolicitacaocompra ITEMS, 
                            sfpc.tborgaolicitante ol  
                      WHERE  ATAI.carpnosequ  = SOL.carpnosequ
                            AND SOL.csolcosequ   = ITEMS.csolcosequ
                            AND SOL.ctpcomcodi   = 5
                            AND SOL.fsolcorpcp   = 'P'
                            AND ol.corglicodi = SOL.corglicodi
                            AND ATAI.carpnosequ  = ". $ata;

if ($tipoItem == 'M') {
    $sqlResultadoItem .= " and ITEMS.cmatepsequ = " . $item;
} else {
    $sqlResultadoItem .= " and ITEMS.cservpsequ = " . $item;
}

$sqlResultadoItem .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ORDER BY SOL.csolcosequ DESC ";

$resultadosItens = array();
$resultadoItemC = executarSQL(ClaDatabasePostgresql::getConexao(), $sqlResultadoItem);
while ($resultadoItemC->fetchInto($sccProcessoCN, DB_FETCHMODE_OBJECT)) {
    array_push($resultadosItens, $sccProcessoCN);
}

$totalQuantidadeUtilizada = 0;
$valorUtilizadoGP = 0;
foreach ($resultadosItens as $sccGestor) {    
        $totalQuantidadeUtilizada += $sccGestor->aitescqtso;
        $valorUtilizadoGP += $sccGestor->vitescunit;
}

// verificar o total da inclusão direta
$itensInclusaoDireta = consultarItensInclusaoDireta($ata, $item, $tipoItem, $seqItem);
if(!empty($itensInclusaoDireta)) { 
    foreach ($itensInclusaoDireta as $key => $inclusao) {  
        $totalQuantidadeUtilizada += $inclusao->apiarpqtut;
        $valorUtilizadoGP += $value->vpiarpvuti;
    }
}

$tipoControle = consultarTipoControle($ata);

foreach ($resultados as $ata) {       
    $itemDescricao = '';
    
    if ($ata->cmatepsequ != null) {
        $tipoServico = 'CADUM';
        $valortem = $ata->cmatepsequ;       
        
        $descricaoCompletaItem = $ata->eitarpdescmat;
        $sql = "select mp.ematepdesc from sfpc.tbmaterialportal mp where mp.cmatepsequ=" . $ata->cmatepsequ;
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT);
    } else {
        $tipoServico = 'CADUS';
        $valortem = $ata->cservpsequ;
              
        $descricaoCompletaItem = $ata->eitarpdescse;
        $sql = "select sp.eservpdesc from sfpc.tbservicoportal sp where sp.cservpsequ=" . $ata->cservpsequ;
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT);
    }

    $itemDescricao  = $ata->descricaoitem;    
    $quantidade     = $ata->aitarpqtat > 0 ? $ata->aitarpqtat : $ata->aitarpqtor;

    $sqlCentroCusto = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
        FROM sfpc.tbcentrocustoportal ccp
        WHERE 1=1 ";
    $sqlCentroCusto .= " AND ccp.corglicodi = " . $extratosAta->corglicodi;        
    $resCentro = executarSQL(ClaDatabasePostgresql::getConexao(), $sqlCentroCusto);    
    $resultadoCentro = array();
    $resultCentro = null;
    while ($resCentro->fetchInto($resultCentro, DB_FETCHMODE_OBJECT)) {
        $resultadoCentro[] = $resultCentro;
    }
    
    $alturaDesc = $pdf->GetStringHeight(187, 5, $itemDescricao, "L");

    $objetoDado         = current($resultadoCentro);            
    $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);
    $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->alicpoanop;
    $saldoQtd           = ($quantidade - $totalQuantidadeUtilizada);
    
    // Valor
    $valorTotalAta = (!empty($ata->vitarpvatu) && $ata->vitarpvatu != 0) ? $ata->vitarpvatu : $ata->vitarpvori;
    $saldoValor    = $valorTotalAta - $valorUtilizadoGP;

    $pdf->ln(05);
    $pdf->Cell(93, 5, "Nº DA ATA", 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $numeroAtaFormatado, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(93, 5, "LOTE", 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $ata->citarpnuml, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(93, 5, 'ORDEM DO ITEM', 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $extratosAta->aitarporde, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(93, 5, $tipoServico, 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $valortem, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(93, $alturaDesc, 'DESCRIÇÃO', 1, 0, 'L', 1);
    $pdf->MultiCell(187,5,strtoupper2($itemDescricao),1,'L',0);
    $pdf->Cell(93, 5, 'DESCRIÇÃO COMPLETA', 1, 0, 'L', 1);
    $pdf->Cell(187, 5, $descricaoCompletaItem, 1, 0, 'L', 0);
    $pdf->ln(05);
    
    if(($tipoControle[0]->farpnotsal != 1) ) {
        $pdf->Cell(93, 5, 'QUANTIDADE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($quantidade), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'QUANTIDADE UTILIZADA GESTOR/PARTICIPANTE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($totalQuantidadeUtilizada), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'SALDO DA QUANTIDADE DO GESTOR/PARTICIPANTE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($saldoQtd), 1, 0, 'L', 0);
    } else {        
        $pdf->Cell(93, 5, 'VALOR TOTAL DA ATA', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($valorTotalAta), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'VALOR UTILIZADO GESTOR/PARTICIPANTE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($valorUtilizadoGP), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(93, 5, 'SALDO DO VALOR DO GESTOR/PARTICIPANTE', 1, 0, 'L', 1);
        $pdf->Cell(187, 5, converte_valor_licitacao($saldoValor), 1, 0, 'L', 0);
    }
    
    break;
}

$sql = " SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor
         FROM sfpc.tbataregistroprecointerna ATAI, 
              sfpc.tbitemataregistropreconova ITEMA,
              sfpc.tbsolicitacaocompra SOL, 
              sfpc.tbitemsolicitacaocompra ITEMS
         WHERE  ATAI.carpnosequ  = SOL.carpnosequ
                AND SOL.csolcosequ   = ITEMS.csolcosequ
                AND SOL.ctpcomcodi   = 5
                AND SOL.fsolcorpcp   = 'P'
                AND ATAI.carpnosequ  = ". $ata->carpnosequ;

if ($tipoItem == 'M') {
    $sql .= " and ITEMS.cmatepsequ = " . $item;
} else {
    $sql .= " and ITEMS.cservpsequ = " . $item;
}

$sql .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ";
$resultadosQuantidade = array();
$resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
while ($resultado->fetchInto($sccProcesso, DB_FETCHMODE_OBJECT)) {
    array_push($resultadosQuantidade, $sccProcesso);
}

$itensInclusaoDireta = consultarItensInclusaoDireta($ata->carpnosequ, $item, $tipoItem, $seqItem);

/* Itens da Inclusão direta */
function consultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
{
    $sql = sqlConsultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem);
    $resultado = ClaDatabasePostgresql::executarSQL($sql);
    
    ClaDatabasePostgresql::hasError($resultado);
    
    return $resultado;
}

$pdf->ln(5);

/* Organizar os orgãos */
$orgaos = array();

if(!empty($itensInclusaoDireta)) {
    foreach($itensInclusaoDireta as $key => $value) {
        $orgao = $value->eorglidesc;
        if($value->corglicodi == $value->orgao_gestor) {
            $orgao .= ' - GESTOR';
        }
        $orgaos[$value->corglicodi]['orgao'] = $orgao;
    }
    foreach ($itensInclusaoDireta as $key => $value) {
        $orgaos[$value->corglicodi]['inclusao_direta'][] = array(
            'apiarpqtut' => ($tipoControle[0]->farpnotsal == 1) ? $value->vpiarpvuti : $value->apiarpqtut,
            'apiarpqtat' => ($tipoControle[0]->farpnotsal == 1) ? $value->vpiarpvatu : $value->apiarpqtat,
            'vitarpvori' => $value->vitarpvori,
            'vitarpvatu' => $value->vitarpvatu,
        );
    }
}

if(!empty($resultadosItens)) {
    foreach($resultadosItens as $key => $value) {
        if(isset($orgaos[$value->corglicodi]['orgao']) &&  strpos($orgaos[$value->corglicodi]['orgao'], 'GESTOR') !== false) {
            continue;
        }

        $orgao = $value->eorglidesc;
        if($value->orgao_agrupamento == $value->orgao_gestor) {
            $orgao .= ' - GESTOR';
        }
        $orgaos[$value->corglicodi]['orgao'] = $orgao;
    }
}

// Organizar os itens nos respectivos orgãos
if(!empty($resultadosItens)) {
    foreach($resultadosItens as $key => $value){
        $orgaos[$value->corglicodi]['itens'][] = $value;
    }
}

function compareOrgaos($a, $b)
{
    return strcmp($a["orgao"], $b["orgao"]);
}

// Exibir as informações
if(!empty($orgaos)) {

    usort($orgaos, "compareOrgaos");

    $gestores = array();
    foreach( $orgaos as $key => $value) {
        if (strpos($value['orgao'], 'GESTOR') !== false) {
            $gestores[$key] = $orgaos[$key];
            unset($orgaos[$key]);
        }
    }

    $orgaos = array_merge($gestores, $orgaos);    

    foreach($orgaos as $key => $value) {
        $itemSumarizacao    = 0; 
        $qtdSolicitadaAta   = 0;
        $valorSolicitadaAta = 0;
        $valorUtilizado     = 0;
        $totalValorUtilizado = 0;
        $valorSolicitadoPro = 0 ;
        
        $pdf->Cell(280, 5, $value['orgao'], 1, 0, 'C', 1);
        $pdf->ln();

        if(!empty($orgaos[$key]['itens'])) {
            $pdf->Cell(93, 5, 'Nº SCC', 1, 0, 'C', 1);
            $pdf->Cell(93, 5, 'Data SCC', 1, 0, 'C', 1);
            $pdf->Cell(94, 5, 'Quantidade Utilizada', 1, 0, 'C', 1);
            $pdf->ln();

            foreach($value['itens'] as $key_ => $value_) {
                $itemQuantidadeUtilizada = $value_->aitescqtex > 0 ? $value_->aitescqtex : $value_->aitescqtso;
                $tmpSumQtdUtilizada = ($tipoControle[0]->farpnotsal != 1) ? $itemQuantidadeUtilizada : $value_->vitescunit;
                $qtd_utilizada += $tmpSumQtdUtilizada;
                $pdf->Cell(93, 5, getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $value_->csolcosequ), 1, 0, 'C', 0);
                $pdf->Cell(93, 5, date("d/m/Y", strtotime($value_->tsolcodata)), 1, 0, 'C', 0);
                $pdf->Cell(94, 5, converte_valor_licitacao($qtd_utilizada), 1, 0, 'C', 0);
                $pdf->ln();
                $itemSumarizacao += $qtd_utilizada;
                $sccQuantidade = consultarQuantidadeSolicitadoDoProcesso($value_->clicpoproc, $value_->alicpoanop, $value_->cgrempcodi, $value_->ccomlicodi, $value_->orgao_gestor, $value_->orgao_agrupamento, $tipo, $item);

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }
            }
        }

        $qtdSolicitadaInclusao = 0;
        if(!empty($orgaos[$key]['inclusao_direta'])) {
            foreach($value['inclusao_direta'] as $key_ => $value_) {
                $qtdSolicitadaInclusao += $value_['apiarpqtut'];
                $qtdSolicitadaAta      += $value_['apiarpqtat'];
                $valorSolicitadaAta    += $value_['vpiarpvatu'];
                $valorUtilizado        += $value_['vitarpvuti'];
                $itemSumarizacao       += ($tipoControle[0]->farpnotsal != 1) ? $value_['vitarpvuti'] : $value_['apiarpqtut'];
                $valorSolicitadoPro    += $value_['apiarpqtat'];
            }
            
            $textoInclusao = 'QUANTIDADE UTILIZADA - INCLUSÃO DIRETA: ';
            if(($tipoControle[0]->farpnotsal == 1) ) {
                $textoInclusao = 'VALOR UTILIZADO - INCLUSÃO DIRETA: ';
                //$qtdSolicitadaInclusao = $qtdSolicitadaInclusao;
            }
            
            $pdf->Cell(280, 5, $textoInclusao . converte_valor_licitacao($qtdSolicitadaInclusao), 1, 0, 'L', 0);
            $pdf->ln();
        }

        if(!empty($orgaos[$key]['itens']) || !empty($orgaos[$key]['inclusao_direta'])) {
            $textoSolicitado = 'QUANTIDADE SOLICITADA NO PROCESSO: ';
            $textoTotal      = 'TOTAL QUANTIDADE UTILIZADA: ';
            if(($tipoControle[0]->farpnotsal == 1) ) {
                $textoTotal      = 'TOTAL VALOR UTILIZADO: ';
                $textoSolicitado = 'VALOR SOLICITADO NO PROCESSO: ';
            }
            $valorUltimaColuna = $textoSolicitado . converte_valor_licitacao($valorSolicitadoPro);
            $pdf->Cell(280, 5, $textoTotal . converte_valor_licitacao($itemSumarizacao), 1, 0, 'L', 0);
            $pdf->ln();
            $pdf->Cell(280, 5, $valorUltimaColuna, 1, 0, 'L', 0);
            $pdf->ln();
            $pdf->Cell(280, 5, 'SALDO DISPONÍVEL: '. converte_valor_licitacao($valorSolicitadoPro - $itemSumarizacao), 1, 0, 'L', 0);
            $pdf->ln();
        }
    }
}

$pdf->Output();
