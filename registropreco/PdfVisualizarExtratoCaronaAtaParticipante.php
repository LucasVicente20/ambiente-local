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
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     09/11/2018
# Objetivo: Tarefa Redmine 205803
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     02/01/2019
# Objetivo: Tarefa Redmine 208259
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     14/02/2019
# Objetivo: Tarefa Redmine 210926
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

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
$TituloRelatorio = "EXTRATO ATAS - HISTÓRICO CARONA";

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

$ata          = $_REQUEST['ata'];
$orgao        = $_REQUEST['orgao'];
$item         = $_REQUEST['item'];
$tipo         = $_REQUEST['tipo'];
$seqItem      = $_REQUEST['seqItem'];
$tipoItem     = $_REQUEST['tipoItem'];
$material     = $_REQUEST['tipoItem'] == 'M' ? $item : null;
$servico      = $_REQUEST['tipoItem'] == 'M' ? null : $item;
$tipoControle = consultarTipoControle($ata); // farpnotsal

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

if(!empty($ataAnterior) || $tipoControle[0]->farpnotsal != 1) {
    $itemAtaInterna = getQtdTotalOrgaoCaronaInterna(ClaDatabasePostgresql::getConexao(), null, $ata, $seqItem);
    $itemAtaExterna = getQtdTotalOrgaoCaronaExterna(ClaDatabasePostgresql::getConexao(), $ata, $seqItem);
    $itemAtaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(ClaDatabasePostgresql::getConexao(), $ata, $seqItem);
} else {
    $itemAtaInterna = getQtdTotalOrgaoCaronaInterna(ClaDatabasePostgresql::getConexao(), null, $ata, $seqItem, 'vitescunit');
    $itemAtaExterna = getQtdTotalOrgaoCaronaExterna(ClaDatabasePostgresql::getConexao(), $ata, $seqItem, 'vcoeitvuti');
    $itemAtaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(ClaDatabasePostgresql::getConexao(), $ata, $seqItem, null, 'vitcrpvuti');
}

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

function sqlConsultarQtantidadeSolicitadaDoProcessoExterno($carpnosequ, $citarpsequ)
{

    $sql = "SELECT sol.csolcosequ, sol.asolcoanos, itel.cmatepsequ, itel.cservpsequ, itel.aitescqtso 
    FROM 
        sfpc.tbsolicitacaocompra sol
    LEFT JOIN sfpc.tbitemsolicitacaocompra itel ON sol.csolcosequ = itel.csolcosequ
    WHERE 1=1
    AND sol.carpnosequ   = $carpnosequ
    AND sol.csitsocodi in (3,4)
    AND sol.fsolcorpcp = 'C'
    AND itel.carpnosequ = $carpnosequ
    AND itel.citarpsequ = $citarpsequ";

                

    /*if ($tipoItem == 'M') {
        $sql .= " and itel.cmatepsequ = " . $codigoItem;
    } else {
        $sql .= " and itel.cservpsequ = " . $codigoItem;
    }*/

    return $sql;
}

function sqlConsultarItensInclusaoDireta($carpnosequ, $itemCodigo, $tipoItem, $seqItem)
{
    $sql  = "SELECT * ";                    
    $sql .= "         FROM ";
    $sql .= "             sfpc.tbcaronainternaatarp arpi ";
    $sql .= "             inner join sfpc.tbitemcaronainternaatarp ipa on  ";
    $sql .= "               ipa.carpnosequ = arpi.carpnosequ  ";
    $sql .= "               and ipa.corglicodi = arpi.corglicodi ";
    $sql .= "             inner join sfpc.tbitemataregistropreconova i on ";
    $sql .= "               i.carpnosequ = arpi.carpnosequ ";
    $sql .= "               and i.citarpsequ = ipa.citarpsequ ";   
    $sql .= "             left outer join sfpc.tbmaterialportal m on ";   
    $sql .= "               i.cmatepsequ = m.cmatepsequ  ";   
    $sql .= "             left outer join sfpc.tbunidadedemedida ump on ";   
    $sql .= "               ump.cunidmcodi = m.cunidmcodi  ";   
    $sql .= "             left outer join sfpc.tbservicoportal s on ";   
    $sql .= "               i.cservpsequ = s.cservpsequ ";   
    $sql .= "             inner join sfpc.tborgaolicitante o on ";   
    $sql .= "               o.corglicodi = arpi.corglicodi ";   

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

function consultarQuantidadeSolicitadoDoProcessoExterno($carpnosequ, $citarpsequ)
{
    $sql = sqlConsultarQtantidadeSolicitadaDoProcessoExterno($carpnosequ, $citarpsequ);
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
    iarpn.aitarpqtat,
    iarpn.citarpnuml,
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
FROM    ";


    if ($tipo == "I") {
        $sql .= " sfpc.tbataregistroprecointerna ata ";
    } else {
        $sql .= " sfpc.tbataregistroprecoexterna ata ";
    }


    $sql .= "  INNER JOIN sfpc.tbitemataregistropreconova iarpn
        ON iarpn.carpnosequ = ata.carpnosequ 
        LEFT JOIN sfpc.tbmaterialportal mp2 ON mp2.cmatepsequ = iarpn.cmatepsequ
        LEFT JOIN sfpc.tbservicoportal sp2 ON sp2.cservpsequ = iarpn.cservpsequ
                
WHERE";
        $sql .= " ata.carpnosequ = " . $ata;
        $sql .= " AND iarpn.citarpsequ = " . $seqItem;

        if ($material != null) {
            $sql .= " and iarpn.cmatepsequ=" . $material;
        } else {
            $sql .= " and iarpn.cservpsequ=" . $servico;
        }

$sql .= " limit 1";

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

    $sqlResultadoItem = "   SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ITEMS.vitescunit,"; 
    $sqlResultadoItem .= "   ol.eorglidesc, SOL.clicpoproc, SOL.alicpoanop, SOL.cgrempcodi, SOL.ccomlicodi, ";
    $sqlResultadoItem .= "   SOL.corglicodi, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor ";
    $sqlResultadoItem .= "   FROM  ";
                
    if ($tipo == "I") {
        $sqlResultadoItem .= " sfpc.tbataregistroprecointerna ATAI ";
    } else {
        $sqlResultadoItem .= " sfpc.tbataregistroprecoexterna ATAI ";
    }
    
    $sqlResultadoItem .= " INNER JOIN sfpc.tbitemataregistropreconova ITEMA ON ITEMA.carpnosequ = ATAI.carpnosequ ";
    $sqlResultadoItem .= " INNER JOIN sfpc.tbitemsolicitacaocompra ITEMS ON ITEMS.carpnosequ = ATAI.carpnosequ ";
    $sqlResultadoItem .= " INNER JOIN sfpc.tbsolicitacaocompra SOL ON SOL.csolcosequ = ITEMS.csolcosequ AND SOL.carpnosequ = ATAI.carpnosequ ";
    $sqlResultadoItem .= " INNER JOIN sfpc.tborgaolicitante ol ON ol.corglicodi = SOL.corglicodi ";

    /*$sqlResultadoItem .= "  sfpc.tbitemataregistropreconova ITEMA,
    sfpc.tbsolicitacaocompra SOL, sfpc.tbitemsolicitacaocompra ITEMS, sfpc.tborgaolicitante ol ";*/
    $sqlResultadoItem .= "  WHERE  1=1 ";        
    //$sqlResultadoItem .= "  AND ATAI.carpnosequ  = SOL.carpnosequ ";
    $sqlResultadoItem .= "  AND ITEMA.citarpsequ = " . $seqItem;

    $sqlResultadoItem .= "  AND ATAI.carpnosequ  = ". $ata;
    $sqlResultadoItem .= "  AND SOL.ctpcomcodi   = 5 ";
    $sqlResultadoItem .= "  AND SOL.fsolcorpcp   = 'C' ";
    $sqlResultadoItem .= "  AND SOL.fsolcoautc   = 'S' ";
    //$sqlResultadoItem .= "  AND ol.corglicodi = SOL.corglicodi ";

    if ($tipoItem == 'M') {
        $sqlResultadoItem .= " and ITEMS.cmatepsequ = " . $item;
    } else {
        $sqlResultadoItem .= " and ITEMS.cservpsequ = " . $item;
    }

    $resultadosItens = array();
    $resultadoItemC = executarSQL(ClaDatabasePostgresql::getConexao(), $sqlResultadoItem);
    while ($resultadoItemC->fetchInto($sccProcessoCN, DB_FETCHMODE_OBJECT)) {
        array_push($resultadosItens, $sccProcessoCN);
    }
    
$totalQuantidadeUtilizada = 0;
foreach ($resultadosItens as $sccGestor) {    
        $totalQuantidadeUtilizada += (int) $sccGestor->aitarpqtat != 0 ? (int) $sccGestor->aitarpqtat : (int) $sccGestor->aitarpqtor;
}


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
        $sql ="select sp.eservpdesc from sfpc.tbservicoportal sp where sp.eservpdesc= '".$ata->descricaoitem."'";
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
        $resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT);
    }

    $itemDescricao = $ata->descricaoitem;    
    $quantidade = (int) $ata->aitarpqtat != 0 ? (int) $ata->aitarpqtat : (int) $ata->aitarpqtor;    
   
    if($tipo == "I"){
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

        $objetoDado = current($resultadoCentro);                
        $numeroAtaFormatado = $objetoDado->ccenpocorg . str_pad($objetoDado->ccenpounid, 2, '0', STR_PAD_LEFT);
        $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->alicpoanop;
    }else{
        $numeroAtaFormatado = str_pad($ata->carpexcodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpexanon;
    }

    $alturaDesc = $pdf->GetStringHeight(217, 5, $itemDescricao, "L");
    $saldoQtd = $quantidade - $totalQuantidadeUtilizada;

    $valor    = $ata->vitarpvatu != 0 ? $ata->vitarpvatu : $ata->vitarpvori;

    $pdf->ln(03);
    $pdf->Cell(63, 5, "Nº DA ATA", 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $numeroAtaFormatado, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, "LOTE", 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $ata->citarpnuml, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, 'ORDEM DO ITEM', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $extratosAta->aitarporde, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, 5, $tipoServico, 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $valortem, 1, 0, 'L', 0);
    $pdf->ln(05);
    $pdf->Cell(63, $alturaDesc, 'DESCRIÇÃO', 1, 0, 'L', 1);
    $pdf->MultiCell(217,5,strtoupper2($itemDescricao),1,'L',0);
    $pdf->Cell(63, 5, 'DESCRIÇÃO DETALHADA', 1, 0, 'L', 1);
    $pdf->Cell(217, 5, $descricaoCompletaItem, 1, 0, 'L', 0);
    $pdf->ln(05);
    
    if(($tipoControle[0]->farpnotsal != 1) ) {
        $pdf->Cell(63, 5, 'QUANTIDADE', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($quantidade), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(63, 5, 'QUANTIDADE MÁXIMA CARONA', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($quantidade * $fatorMaxCarona), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(63, 5, 'QUANTIDADE UTILIZADA CARONA', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($saldoGeralCaronaAta), 1, 0, 'L', 0);
    } else {
        $pdf->Cell(63, 5, 'VALOR', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($valor), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(63, 5, 'VALOR MÁXIMA CARONA', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($valor * $fatorMaxCarona), 1, 0, 'L', 0);
        $pdf->ln(05);
        $pdf->Cell(63, 5, 'TOTAL VALOR UTILIZADO', 1, 0, 'L', 1);
        $pdf->Cell(217, 5, converte_valor_licitacao($saldoGeralCaronaAta), 1, 0, 'L', 0);
    }
}

$sql = " SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor, ";
                
if ($tipo == "I") {
    $sql .= " SOL.cgrempcodi, SOL.ccomlicodi FROM sfpc.tbataregistroprecointerna ATAI, ";
} else {
    $sql .= " ATAI.aarpexanon, ATAI.carpexcodn, ATAI.earpexproc FROM sfpc.tbataregistroprecoexterna ATAI, ";
}        
        
$sql .= "   sfpc.tbitemataregistropreconova ITEMA,
            sfpc.tbsolicitacaocompra SOL, 
            sfpc.tbitemsolicitacaocompra ITEMS
        WHERE  ATAI.carpnosequ  = SOL.carpnosequ
            AND SOL.csolcosequ   = ITEMS.csolcosequ
            AND SOL.ctpcomcodi   = 5
            AND SOL.fsolcorpcp   = 'C'
            AND SOL.fsolcoautc   = 'S'
            AND ATAI.carpnosequ  = ". $ata->carpnosequ;

if ($tipoItem == 'M') {
    $sql .= " and ITEMS.cmatepsequ = " . $item;
} else {
    $sql .= " and ITEMS.cservpsequ = " . $item;
}

$sql .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ";
$sql .= "  AND ITEMA.citarpsequ = " . $seqItem;

$resultadosQuantidade = array();
$resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $sql);
while ($resultado->fetchInto($sccProcesso, DB_FETCHMODE_OBJECT)) {
    array_push($resultadosQuantidade, $sccProcesso);
}

$pdf->ln(5);

$qtd_utilizada = 0;
$qtdSolicitadaAta = 0;
//$_SESSION['quatidadeUtz'] = 0;
$plotou = false;
$sccGestorPadrao = null;
if($tipo == "I"){
    $pdf->Cell(280, 5, 'CARONA INTERNA', 1, 0, 'C', 1);
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
        $orgaos[$value->corglicodi]['orgao'] = $value->eorglidesc;
    }
    foreach ($itensInclusaoDireta as $key => $value) {
        $orgaos[$value->corglicodi]['inclusao_direta'][] = array(
            'aitcrpqtut' => $value->aitcrpqtut,
            'vitcrpvatu' => $value->vitcrpvatu,
            'vitcrpvuti' => $value->vitcrpvuti,
        );
    }
}

if(!empty($resultadosItens)) {
    foreach($resultadosItens as $key => $value) {
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

// Exibir as informações
if(!empty($orgaos)) {
    foreach($orgaos as $key => $value) {
        $itemSumarizacao = $qtdSolicitadaAta = 0;
        
        $pdf->Cell(280, 5, $value['orgao'], 1, 0, 'C', 1);
        $pdf->ln();

        if(!empty($orgaos[$key]['itens'])) {
            $textoQtdUtilizada = 'QUANTIDADE UTILIZADA';
            if(($tipoControle[0]->farpnotsal == 1) ) {
                $textoQtdUtilizada = 'VALOR UTILIZADO ';
            }
            $pdf->Cell(93, 5, 'Nº SCC', 1, 0, 'C', 1);
            $pdf->Cell(93, 5, 'DATA SCC', 1, 0, 'C', 1);
            $pdf->Cell(94, 5, $textoQtdUtilizada, 1, 0, 'C', 1); 
            $pdf->ln();

            foreach($value['itens'] as $key_ => $value_) {
                $itemQuantidadeUtilizada = $value_->aitescqtex > 0 ? $value_->aitescqtex : $value_->aitescqtso;
                $tmpSumQtdUtilizada = ($tipoControle[0]->farpnotsal != 1) ? $itemQuantidadeUtilizada : $value_->vitescunit;
                $pdf->Cell(93, 5, getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $value_->csolcosequ), 1, 0, 'C', 0);
                $pdf->Cell(93, 5, date("d/m/Y", strtotime($value_->tsolcodata)), 1, 0, 'C', 0);
                $pdf->Cell(94, 5, converte_valor_licitacao($tmpSumQtdUtilizada), 1, 0, 'C', 0);
                $pdf->ln();
                $itemSumarizacao += $tmpSumQtdUtilizada;
                if($tipo == 'I') {
                    $sccQuantidade = consultarQuantidadeSolicitadoDoProcesso($value_->clicpoproc, $value_->alicpoanop, $value_->cgrempcodi, $value_->ccomlicodi, $value_->orgao_gestor, $value_->orgao_agrupamento, $tipoItem, $item);
                } else {
                    $sccQuantidade = consultarQuantidadeSolicitadoDoProcessoExterno($value_->carpnosequ, $item);
                }

                foreach ($sccQuantidade as $quantidade) {
                    $qtdSolicitadaAta += $quantidade->aitelpqtso;
                }
            }
        }

        $qtdSolicitadaInclusao = 0;
        if(!empty($orgaos[$key]['inclusao_direta'])) {
            foreach($value['inclusao_direta'] as $key_ => $value_) {
                $qtdSolicitadaInclusao += ($tipoControle[0]->farpnotsal != 1) ? $value_['aitcrpqtut'] : $value_['vitcrpvuti'];
                $itemSumarizacao += ($tipoControle[0]->farpnotsal != 1) ? $value_['aitcrpqtut'] : $value_['vitarpvuti'];
            }

            $textoInclusao = 'QUANTIDADE UTILIZADA - INCLUSÃO DIRETA: ';
            if(($tipoControle[0]->farpnotsal == 1) ) {
                $textoInclusao = 'VALOR UTILIZADO INCLUSÃO DIRETA: ';
            }
            $pdf->Cell(280, 5, $textoInclusao . converte_valor_licitacao($qtdSolicitadaInclusao), 1, 0, 'L', 0);
            $pdf->ln();
        }

        $textoTotal = 'TOTAL QUANTIDADE UTILIZADA: ';
        if(($tipoControle[0]->farpnotsal == 1) ) {
            $textoTotal = 'TOTAL VALOR UTILIZADA: ';
        }

        if(!empty($orgaos[$key]['itens']) || !empty($orgaos[$key]['inclusao_direta'])) {
            $pdf->Cell(280, 5, $textoTotal . converte_valor_licitacao($itemSumarizacao), 1, 0, 'L', 0);
            $pdf->ln();
            $pdf->Cell(280, 5, $valorUltimaColuna, 1, 0, 'L', 0);
            $pdf->ln();
        }
    }
}

function sqlConsultarCaronaOrgaoExterno($tipoAta, $ata, $codigoItem, $tipoItem, $comparacao, $orgao = null, $seqItem)
{
            
    $sql = " SELECT 
                COEIT.ccaroesequ,
                COEIT.citarpsequ,
                COEIT.carpnosequ,
                COE.ecaroeorgg,
                COEIT.acoeitqtat,
                COE.tcaroeincl,
                COEIT.vcoeitvuti,
                COE.tcaroedaut
            FROM
                sfpc.tbitemataregistropreconova ITEMA,
                sfpc.tbcaronaorgaoexternoitem COEIT,
                sfpc.tbcaronaorgaoexterno COE
            WHERE  COEIT.citarpsequ = ITEMA.citarpsequ
                AND COEIT.carpnosequ = ITEMA.carpnosequ
                AND COE.ccaroesequ = COEIT.ccaroesequ
                AND COE.carpnosequ = COEIT.carpnosequ                
                AND COE.carpnosequ  = " . $ata->carpnosequ;

    $sql .= " AND ITEMA.citarpsequ = " . $seqItem;
    if ($tipoItem == 'M') {
        if(is_object($codigoItem)) {
            $codigoItem = $codigoItem->cmatepsequ;
        }
        $sql .= " AND ITEMA.cmatepsequ = " . $codigoItem;
    } else {
        if(is_object($codigoItem)) {
            $codigoItem = $codigoItem->cmatepsequ;
        }
        $sql .= " AND ITEMA.cservpsequ = " . $codigoItem;
    }

    return $sql;
}

function consultarCaronaOrgaoExterno($tipoAta, $ata, $item, $tipoItem, $comparacao, $orgao, $seqItem)
{
    $sql = sqlConsultarCaronaOrgaoExterno($tipoAta, $ata, $item, $tipoItem, $comparacao, $orgao, $seqItem);
    $resultado = ClaDatabasePostgresql::executarSQL($sql);   
    ClaDatabasePostgresql::hasError($resultado);
    
    return $resultado;
}

if($tipo == "I"){

    $orgaos_externo = array();
    $caronaOrgaoGestor = consultarCaronaOrgaoExterno($tipo, $ata, $item, $tipoItem, '!=', $orgao, $seqItem);
    if(!empty($caronaOrgaoGestor)) {
        foreach($caronaOrgaoGestor as $key => $value) {
            $orgaos_externo[$value->ecaroeorgg] = array();
        }
    }

    if(!empty($orgaos_externo)) {        
        $pdf->Cell(280, 5, 'CARONA EXTERNA', 1, 0, 'C', 1); 
        $pdf->ln(5);

        // Organizar por orgao
        foreach ($caronaOrgaoGestor as $key => $value) {
            $orgaos_externo[$value->ecaroeorgg][] = $value;
        }

        $textoUtilizado = 'TOTAL QUANTIDADE UTILIZADA: ';
        $textoQtdUtil = 'QUANTIDADE UTILIZADA';
        if(($tipoControle[0]->farpnotsal == 1) ) {
            $textoUtilizado = 'TOTAL VALOR UTILIZADA: ';
            $textoQtdUtil = 'VALOR UTILIZADO';
        }

        foreach($orgaos_externo as $key_ => $value_) {  
            $qtd_utilizadaFinalExterna = 0;
            $pdf->Cell(280, 5, $key_, 1, 0, 'C', 1);
            $pdf->ln();      

            foreach ($value_ as $key => $carona) { 
                $dataInclusao = (!empty($carona->tcaroedaut)) ? $carona->tcaroedaut : $carona->tcaroeincl;
                $qtdTemp = ($tipoControle[0]->farpnotsal != 1) ? $carona->acoeitqtat : $carona->vcoeitvuti;                        
                $pdf->Cell(93, 5, 'Nº SOLICITAÇÃO EXTERNA', 1, 0, 'C', 1);
                $pdf->Cell(93, 5, 'DATA DE AUTORIZAÇÃO', 1, 0, 'C', 1);
                $pdf->Cell(94, 5, $textoQtdUtil, 1, 0, 'C', 1);
                $pdf->ln();        
                $pdf->Cell(93, 5, $carona->ccaroesequ, 1, 0, 'C', 0);
                $pdf->Cell(93, 5, date("d/m/Y", strtotime($dataInclusao)), 1, 0, 'C', 0);
                $pdf->Cell(94, 5, converte_valor_licitacao($qtdTemp), 1, 0, 'C', 0);
                $pdf->ln();

                $qtd_utilizadaFinalExterna += $qtdTemp;
            }

            $pdf->Cell(280, 5,  $textoUtilizado . converte_valor_licitacao($qtd_utilizadaFinalExterna), 1, 0, 'L', 0);
            $pdf->ln();
            $pdf->Cell(280, 5, $valorUltimaColuna, 1, 0, 'L', 0);
            $pdf->ln();
        }
    }
}

$pdf->Output();
