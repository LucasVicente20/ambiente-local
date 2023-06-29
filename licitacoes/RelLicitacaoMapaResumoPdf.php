<?php
// ------------------------------------------------------------------------
// Portal da DGCO
// Programa: RelLicitacaoMapaResumoPdf.php
// Autor: Igor Duarte
// Data: 15/08/2012
// Objetivo: Criaçao do pdf de relatório de mapa resumo de licitações
// ------------------------------------------------------------------------
// OBS.: Tabulação 2 espaços
// ------------------------------------------------------------------------
/**
 * Alterado: José Francisco <jose.francisco@pitang.com>
 * Data:     06/06/2014 	- [CR123142]: REDMINE 22 (P5)
 */
// Acesso ao arquivo de funções #
require '../funcoes.php';

// Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/RelLicitacaoMapaResumoSelecionar.php');

/**
 * Lista de dados dos Itens da Licitação
 *
 * @author José Francisco <jose.francisco@pitang.com>
 *
 */
class PitangRLMRPdf
{

    /**
     * Verifica se existe algum item/serviço com o campo descrição detalhada
     * diferente de null
     *
     * @param DB_Result $result
     *
     * @return boolean
     */
    public static function hasDetailedDescription(DB_Result $result)
    {
        $exibeTd = false;
        // checando se existe material/serviços
        while ($arrI = $result->fetchRow()) {
            if (!empty($arrI[15]) || !empty($arrI[16])) {
                $exibeTd = true;
                break;
            }
        }

        return $exibeTd;
    }

    public static function sizeCellItens($item, $logrados) {
        $array = array(
            'DESCRIÇÃO'           => 50,
            'DESCRIÇÃO DETALHADA' => 50,
            'MARCA'               => 22,
            'MODELO'              => 23,
        );
        $array_logrado = array(
            'DESCRIÇÃO'           => 40,
            'MARCA'               => 22,
            'MODELO'              => 23,
            'DESCRIÇÃO DETALHADA' => 40,
            'MARCA'               => 22,
            'MODELO'              => 23,
        );
        

    }

    public static function getQueryItens($itensLogrados) {

        if($itensLogrados != 'N') {
            $sqlLotes = "SELECT FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.AITELPORDE, ILP.CMATEPSEQU, ILP.CSERVPSEQU, ILP.AITELPQTSO, ILP.VITELPUNIT, (ILP.AITELPQTSO * ILP.VITELPUNIT) AS VTESTM, ILP.VITELPVLOG, (ILP.AITELPQTSO * ILP.VITELPVLOG) AS VTLOGR, ILP.EITELPMARC, ILP.EITELPMODE, ILP.CMOTNLSEQU, ILP.CITELPNUML, UNID.EUNIDMSIGL, ILP.EITELPDESCMAT, ILP.EITELPDESCSE
            FROM
                    SFPC.TBITEMLICITACAOPORTAL ILP
                    LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = ILP.AFORCRSEQU
                    LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CMATEPSEQU = ILP.CMATEPSEQU
                    LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
            WHERE
                    ILP.CLICPOPROC = ?
                    AND ILP.ALICPOANOP = ?
                    AND ILP.CGREMPCODI = ?
                    AND ILP.CCOMLICODI = ?
                    AND ILP.CORGLICODI = ?
            ORDER BY
                ILP.CITELPNUML ASC, FORN.AFORCRCCGC ASC, ILP.AITELPORDE ASC, ILP.CMATEPSEQU ASC";

        } else {
            $sqlLotes = "SELECT FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.AITELPORDE, ILP.CMATEPSEQU, ILP.CSERVPSEQU, ILP.AITELPQTSO, ILP.VITELPUNIT, (ILP.AITELPQTSO * ILP.VITELPUNIT) AS VTESTM, ILP.VITELPVLOG, (ILP.AITELPQTSO * ILP.VITELPVLOG) AS VTLOGR, ILP.EITELPMARC, ILP.EITELPMODE, ILP.CMOTNLSEQU, ILP.CITELPNUML, UNID.EUNIDMSIGL, ILP.EITELPDESCMAT, ILP.EITELPDESCSE, C.EMOTNLNOME
            FROM
                    SFPC.TBITEMLICITACAOPORTAL ILP
                    LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = ILP.AFORCRSEQU
                    LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CMATEPSEQU = ILP.CMATEPSEQU
                    LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
                    LEFT JOIN SFPC.TBMOTIVOITEMNAOLOGRADO as C on C.CMOTNLSEQU = ILP.CMOTNLSEQU
            WHERE
                    ILP.CLICPOPROC = ?
                    AND ILP.ALICPOANOP = ?
                    AND ILP.CGREMPCODI = ?
                    AND ILP.CCOMLICODI = ?
                    AND ILP.CORGLICODI = ?        
            ORDER BY
                ILP.CITELPNUML ASC, FORN.AFORCRCCGC ASC, ILP.AITELPORDE ASC, ILP.CMATEPSEQU ASC";
        }

        return $sqlLotes;
    }

    public static function generateHeaderItem(PDF &$pdf, $fornDados, $item_logrado = false)
    {
        $pdf->SetFont("Arial", "B", 8);
        $pdf->Cell(280, 5, "FORNECEDOR: " . $fornDados, 1, 1, "L", 1);
        $pdf->SetFont("Arial", "B", 7);
        $pdf->Cell(10, 15, "ORD", 1, 0, "C", 0);
        $pdf->Cell(50, 15, "DESCRIÇÃO", 1, 0, "C", 0);// TEXTO LONGO
        $pdf->Cell(12, 15, "TIPO", 1, 0, "C", 0);
        $pdf->Cell(10, 5, "CÓD", "LTR", 0, "C", 0); // 1
        $pdf->Cell(11, 15, "UND", 1, 0, "C", 0);
        $pdf->Cell(15, 15, "QUANT", 1, 0, "C", 0);
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 5
        $pdf->Cell(($item_logrado) ? 38 : 50, 15, "MARCA", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(($item_logrado) ? 37 : 50, 15, "MODELO", 1, 0, "C", 0); // TEXTO LONGO
        
        if($item_logrado) {
            $pdf->Cell(25, 5, "MOTIVO ITEM", "LTR", 0, "C", 0); // TEXTO LONGO
        }

        $pdf->SetXY(82, ($pdf->GetY() + 5));
        $pdf->Cell(10, 5, "RED", "LR", 0, "C", 0); // 1
        $pdf->SetX(118);
        $pdf->Cell(18, 5, "ESTIMADO", "LR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "LOGRADO", "LR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 5
        
        if($item_logrado) {
            $pdf->Cell(75, 5, "", 0, 0, "C", 0); // 4
            $pdf->Cell(25, 5, "NÃO LOGRADO", "LR", 0, "C", 0); // 1
        }

        $pdf->SetXY(82, ($pdf->GetY() + 5));
        $pdf->Cell(10, 5, "", "LBR", 0, "C", 0); // 1
        $pdf->SetX(118);
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "ESTIMADO", "LBR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "LOGRADO", "LBR", 0, "C", 0); // 5
        if($item_logrado) {
            $pdf->Cell(100, 5, "", "R", 0, "C", 0); // 4
        }

        $pdf->Ln(5);

    }

    public static function generateHeaderItemWithDetailedDescription(PDF &$pdf, $fornDados)
    {
        $pdf->SetFont("Arial", "B", 8);
        $pdf->Cell(280, 5, "FORNECEDOR: " . $fornDados, 1, 1, "L", 1);
        $pdf->SetFont("Arial", "B", 7);
        $pdf->Cell(10, 15, "ORD", 1, 0, "C", 0);
        $pdf->Cell(50, 15, "DESCRIÇÃO", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(12, 15, "TIPO", 1, 0, "C", 0);
        $pdf->Cell(10, 5, "CÓD", "LTR", 0, "C", 0); // 1
        $pdf->Cell(11, 15, "UND", 1, 0, "C", 0);
        $pdf->Cell(50, 15, "DESCRIÇÃO DETALHADA", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(15, 15, "QUANT", 1, 0, "C", 0);
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 5
        $pdf->Cell(25, 15, "MARCA", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(25, 15, "MODELO", 1, 0, "C", 0); // TEXTO LONGO

        $pdf->SetXY(82, ($pdf->GetY() + 5));
        $pdf->Cell(10, 5, "RED", "LR", 0, "C", 0); // 1
        $pdf->SetX(168);
        $pdf->Cell(18, 5, "ESTIMADO", "LR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "LOGRADO", "LR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 5

        $pdf->SetXY(82, ($pdf->GetY() + 5));
        $pdf->Cell(10, 5, "", "LBR", 0, "C", 0); // 1
        $pdf->SetX(168);
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "ESTIMADO", "LBR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "LOGRADO", "LBR", 0, "C", 0); // 5
        $pdf->Ln(5);
    }

    public static function generateHeaderItemWithDetailedDescriptionItemNaoLogrado(PDF &$pdf, $fornDados)
    {
        $pdf->SetFont("Arial", "B", 8);
        $pdf->Cell(280, 5, "FORNECEDOR: " . $fornDados, 1, 1, "L", 1);
        $pdf->SetFont("Arial", "B", 7);
        $pdf->Cell(10, 15, "ORD", 1, 0, "C", 0);
        $pdf->Cell(40, 15, "DESCRIÇÃO", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(12, 15, "TIPO", 1, 0, "C", 0);
        $pdf->Cell(10, 5, "CÓD", "LTR", 0, "C", 0); // 1
        $pdf->Cell(11, 15, "UND", 1, 0, "C", 0);
        $pdf->Cell(40, 15, "DESCRIÇÃO DETALHADA", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(15, 15, "QUANT", 1, 0, "C", 0);
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "VALOR", "LTR", 0, "C", 0); // 5
        $pdf->Cell(22, 15, "MARCA", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(23, 15, "MODELO", 1, 0, "C", 0); // TEXTO LONGO
        $pdf->Cell(25, 5, "MOTIVO ITEM", "LTR", 0, "C", 0); // TEXTO LONGO

        $pdf->SetXY(72, ($pdf->GetY() + 5 ));
        $pdf->Cell(10, 5, "RED", "LR", 0, "C", 0); // 1
        $pdf->SetX(148);
        $pdf->Cell(18, 5, "ESTIMADO", "LR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "LOGRADO", "LR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "TOTAL", "LR", 0, "C", 0); // 5
        $pdf->Cell(45, 5, "", "LR", 0, "C", 0); // 1
        $pdf->Cell(25, 5, "NÃO LOGRADO", "LR", 0, "C", 0); // 1

        $pdf->SetXY(82, ($pdf->GetY() + 5));
        $pdf->Cell(11, 5, "", "LBR", 0, "C", 0); // 1
        $pdf->SetX(148);
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 2
        $pdf->Cell(18, 5, "ESTIMADO", "LBR", 0, "C", 0); // 3
        $pdf->Cell(18, 5, "", "LBR", 0, "C", 0); // 4
        $pdf->Cell(18, 5, "LOGRADO", "LBR", 0, "C", 0); // 5
        $pdf->Cell(45, 5, "", "LR", 0, "C", 0); // 1
        $pdf->Cell(25, 5, "", "LBR", 0, "C", 0); // 5
        $pdf->Ln(5);
    }

    public static function generateRowItem(PDF &$pdf, array $data)
    {        
        $pdf->Cell(15, $data['altLItem'], converte_valor_estoques($data['quant']), 1, 0, "C", 0);
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorEst']), 1, 0, "C", 0); // 2
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorTEst']), 1, 0, "C", 0); // 3
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorLog']), 1, 0, "C", 0); // 4
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorTLog']), 1, 0, "C", 0); // 5
    }

    public static function generateRowItemOneLine(PDF &$pdf, array $data)
    {
        $pdf->Cell(12, 5, $data['tipo'], 1, 0, "C", 0);
        $pdf->Cell(10, 5, $data['codRed'], 1, 0, "C", 0); // 1
        $pdf->Cell(11, 5, $data['unid'], 1, 0, "C", 0);
        $pdf->Cell(15, 5, converte_valor_estoques($data['quant']), 1, 0, "C", 0);
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorEst']), 1, 0, "C", 0); // 2
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorTEst']), 1, 0, "C", 0); // 3
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorLog']), 1, 0, "C", 0); // 4
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorTLog']), 1, 0, "C", 0); // 5
        $pdf->Cell(50, 5, $data['marcaItem'], 1, 0, "C", 0); // marca - TEXTO LONGO
        $pdf->Cell(50, 5, $data['modeloItem'], 1, 1, "C", 0); // modelo - TEXTO LONGO
    }

    public static function generateRowItemWithDetailedDescription(PDF &$pdf, array $data, $item_logrado = 'S')
    {  
        $pdf->Cell(($item_logrado == 'N') ? 40 : 50, 5, $data['descDetalhada'], "LTR", 0, "L", 0);
        $pdf->Cell(15, $data['altLItem'], converte_valor_estoques($data['quant']), 1, 0, "C", 0);
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorEst']), 1, 0, "C", 0); // 2
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorTEst']), 1, 0, "C", 0); // 3
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorLog']), 1, 0, "C", 0); // 4
        $pdf->Cell(18, $data['altLItem'], converte_valor_estoques($data['valorTLog']), 1, 0, "C", 0); // 5
    }

    public static function generateRowItemOneLineWithDetailedDescription(PDF &$pdf, array $data)
    {
        $pdf->Cell(12, 5, $data['tipo'], 1, 0, "C", 0);
        $pdf->Cell(10, 5, $data['codRed'], 1, 0, "C", 0); // 1
        $pdf->Cell(11, 5, $data['unid'], 1, 0, "C", 0);
        $pdf->Cell(50, $data['altLItem'], $data['descDetalhada'], 1, 0, "C", 0);
        $pdf->Cell(15, 5, converte_valor_estoques($data['quant']), 1, 0, "C", 0);
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorEst']), 1, 0, "C", 0); // 2
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorTEst']), 1, 0, "C", 0); // 3
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorLog']), 1, 0, "C", 0); // 4
        $pdf->Cell(18, 5, converte_valor_estoques($data['valorTLog']), 1, 0, "C", 0); // 5
        $pdf->Cell(25, 5, $data['marcaItem'], 1, 0, "C", 0); // marca - TEXTO LONGO
        $pdf->Cell(25, 5, $data['modeloItem'], 1, 1, "C", 0); // modelo - TEXTO LONGO
    }    

    /**
     * Função para retornar os tamanhos 
     * das cells de acordo com as colunas
     * 
     * @param $cell
     * @param $has_description
     * @param $has_logrado
     * 
     * @return int
     */
    public static function getSizeCell($cell,$has_description, $has_logrado = false) 
    {
        $width = 0;
        switch ($cell) {
            case 'descricao':
                if($has_logrado && $has_description) {
                    $width = 40;
                } else if(!$has_logrado && $has_description) {
                    $width = 50;
                }else {
                    $width = 50;   
                }
                break; 
            case 'desc_detalhada':
                if($has_logrado) {
                    $width = 40;
                } else {
                    $width = 50;   
                }
                break; 
            case 'marca':
                if($has_logrado && $has_description) {
                    $width = 22;
                } else if($has_logrado && !$has_description) {
                    $width = 38;
                } else if(!$has_logrado && $has_description) {
                    $width = 25;
                } else {
                    $width = 50;   
                }
                break; 
            case 'modelo':
                if($has_logrado && $has_description) {
                    $width = 23;
                } else if($has_logrado && !$has_description) {
                    $width = 37;
                } else if(!$has_logrado && $has_description) {
                    $width = 25;
                } else {
                    $width = 50;   
                }
                break; 
            default:
                # code...
                break;
        }

        return $width;
    }

    /**
     * Função para gerar o tamanho
     * e os textos quebrados para a cell
     * 
     * @param $largura
     * @param $text
     * @param $stringHight
     * 
     * @return array
     */
    public static function generateDinamiCells($largura, $text, $stringHight) {
        $h = 5;
        $hm = 0;
        $h1 = $stringHight;        
        $hm = $h1;        
        $hLinhas = $hm / $h;
        
        if (($hLinhas % 2 != 0) && ($hLinhas != 1)) {
            $hLinhas--;
        }
        
        if (($lin = ($hLinhas - ($h1 / $h)) / 2) > 0) {
            $text = str_repeat("\n", (int) $lin) . $text . str_repeat("\n", (int) $lin);    
        }

        $h1 = $hm / ($h1 / $h);

        return array('h1' => $h1, 'hm' => $hm, 'text' => $text);
    }

 }

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $LicitacaoProcessoAnoComissao = $_GET['LicitacaoProcessoAnoComissao'];
    $Critica = $_GET['Critica'];
    $NProcessoAnoComissao = $_GET['NProcessoAnoComissao'];
    $Processo = $_GET['Processo'];
    $ProcessoAno = $_GET['ProcessoAno'];
    $ComissaoCodigo = $_GET['ComissaoCodigo'];
    $ItemHomologado = $_GET['ItemHomologado'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelLicitacaoMapaResumoPdf.php";

// Inicio da criação das partes fixas dos pdf
// Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

// Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Mapa Resumo de Licitação";

    // Cria o objeto PDF, o Default formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("L", "mm", "A4");

// Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

// Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

// Adiciona uma página no documento #
$pdf->AddPage();

// Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "B", 9);
// final das partes fixas

// Parte 1 - Informações sobre o processo de licitação
// Carrega os dados da licitação selecionada #
$db = Conexao();

$sql = "SELECT
                LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.FLICPOREGP,
                LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE,
                LIC.CGREMPCODI, LIC.CCOMLICODI, LIC.CORGLICODI,
                ORGL.EORGLIDESC, COM.ECOMLIDESC, MOD.EMODLIDESC
            FROM
                SFPC.TBLICITACAOPORTAL LIC
                JOIN SFPC.TBORGAOLICITANTE ORGL ON ORGL.CORGLICODI = LIC.CORGLICODI
                JOIN SFPC.TBCOMISSAOLICITACAO COM ON COM.CCOMLICODI = LIC.CCOMLICODI
                JOIN SFPC.TBMODALIDADELICITACAO MOD ON MOD.CMODLICODI = LIC.CMODLICODI
            WHERE
                LIC.CLICPOPROC = ?
                AND LIC.ALICPOANOP = ?
                AND LIC.CCOMLICODI = ?
            ORDER BY LIC.CCOMLICODI ASC";

$res = $db->query($sql, array($Processo, $ProcessoAno, $ComissaoCodigo));

if (PEAR::isError($res)) {
    $CodErroEmail = $res->getCode();
    $DescErroEmail = $res->getMessage();
    var_export($DescErroEmail);
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
} else {
    $Linha = $res->fetchRow();
}

// if (isset($Linha[2]) && ($Linha[2] != "")) {
//     if ($Linha[2] == "S") {
//         $Linha[2] = "SIM";
//     } else {
//         $Linha[2] = "NAO";
//     }
// }

$Linha[2] = (!empty($Linha[2]) && $Linha[2] == 'S') ? "SIM" : "NAO";

// Parte 2 - Solicitação de compra/contratamão
$sqlSCC = "	SELECT
                            CET.CCENPOCORG, CET.CCENPOUNID,
                            SOLC.CSOLCOCODI, SOLC.ASOLCOANOS
                        FROM
                            SFPC.TBSOLICITACAOCOMPRA SOLC
                            JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SOLP ON SOLP.CSOLCOSEQU = SOLC.CSOLCOSEQU
                            JOIN SFPC.TBCENTROCUSTOPORTAL CET ON CET.CCENPOSEQU = SOLC.CCENPOSEQU
                        WHERE
                            SOLP.CLICPOPROC = ?
                            AND SOLP.ALICPOANOP = ?
                            AND SOLP.CGREMPCODI = ?
                            AND SOLP.CCOMLICODI = ?
                            AND SOLP.CORGLICODI = ?
                        ORDER BY
                            CET.CCENPOCORG DESC, CET.CCENPOUNID DESC";

$resultSCC = $db->query($sqlSCC, array($Linha[0], $Linha[1], $Linha[6], $Linha[7], $Linha[8]));

if (PEAR::isError($resultSCC)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlSCC");
}

$arraySCC = array();

while ($LinhaSCC = $resultSCC->fetchRow()) {
    $temp = "";
    $temp .= ($LinhaSCC[0] < 10) ? "0" . $LinhaSCC[0] : $LinhaSCC[0];
    $temp .= ($LinhaSCC[1] < 10) ? "0" . $LinhaSCC[1] . "." : $LinhaSCC[1] . ".";

    if ($LinhaSCC[2] < 10) {
        $temp .= "000" . $LinhaSCC[2] . ".";
    } elseif (($LinhaSCC[2] < 100) && ($LinhaSCC[2] >= 10)) {
        $temp .= "00" . $LinhaSCC[2] . ".";
    } elseif (($LinhaSCC[2] < 1000) && ($LinhaSCC[2] >= 100)) {
        $temp .= "0" . $LinhaSCC[2] . ".";
    } else {
        $temp .= $LinhaSCC[2] . ".";
    }

    $temp .= $LinhaSCC[3];
    $arraySCC[] = $temp;
}

// Parte 3 - busca dos itens dos lotes do processo de licitação
$sqlLotes = PitangRLMRPdf::getQueryItens($ItemHomologado);
$arrParams = array($Linha[0], $Linha[1], $Linha[6], $Linha[7], $Linha[8]);
$resultLotes = $db->query($sqlLotes, $arrParams);
//var_dump($resultLotes);

$resultQuery = $db->query($sqlLotes, $arrParams);
//var_dump($resultQuery); die;
$showField = PitangRLMRPdf::hasDetailedDescription($resultQuery);
// if (($resteste->numRows()) > 1) {
//     while ($teste = $resteste->fetchRow()) {
//         if ($teste[12] == NULL) {
//             $qtLotes[$teste[13]] += 1;
//         }
//     }
// } else {
//     $teste = $resteste->fetchRow();
//     if ($teste[12] == NULL) {
//         $qtLotes[$teste[13]] += 1;
//     }
// }
//var_dump($exibeTd);
$resteste = $db->query($sqlLotes, $arrParams);
while ($teste = $resteste->fetchRow()) {
    if ($teste[12] == NULL || ($teste[12] != null && ($ItemHomologado == 'N'))) {
        $qtLotes[$teste[13]] += 1;
    }
}
unset($resteste);

// var_dump($qtLotes); die(aqui);

if (PEAR::isError($resultLotes)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlLotes");
}

// Parte 4 - busca pela PSE
$sqlPSE = "SELECT
                    PSE.CPRESOSEQU,
                    PSE.APRESOANOE,
                    PSE.TPRESOGERA,
                    PSE.APRESONBLOQ, PSE.APRESOANOB,
                    (SELECT SUM(VIPRESEMPN) FROM SFPC.TBITEMPRESOLICITACAOEMPENHO
                    WHERE 	CPRESOSEQU = PSE.CPRESOSEQU AND APRESOANOE = PSE.APRESOANOE) AS VALPSE,
                    PSE.CPRESOCNPJ,
                    PSE.CPRESOCCPF,
                    FORN.NFORCRRAZS,
                    PSE.CMOTNICODI, MNI.EMOTNIDESC, --9
                    PSE.TPRESOIMPO, PSE.TPRESOULAT, --11
                    PSE.DPRESOCSEM,					--13
                    PSE.DPRESOANUE, PSE.VPRESOANUE, --14
                    PSE.DPRESOGERE, PSE.APRESONUES, PSE.APRESOANES
                FROM
                    SFPC.TBPRESOLICITACAOEMPENHO PSE
                    JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = PSE.AFORCRSEQU
                    LEFT JOIN SFPC.TBMOTIVONAOIMPORTACAO MNI ON MNI.CMOTNICODI = PSE.CMOTNICODI
                WHERE
                    PSE.CLICPOPROC = ?
                    AND PSE.ALICPOANOP = ?
                    AND PSE.CGREMPCODI = ?
                    AND PSE.CCOMLICODI = ?
                    AND PSE.CORGLICODI = ?
                ORDER BY PSE.TPRESOGERA ASC";

$resultPSE = $db->query($sqlPSE, array($Linha[0], $Linha[1], $Linha[6], $Linha[7], $Linha[8]));

$forn = array(1,1,1,2,3);
$tempforn;

if (PEAR::isError($resultPSE)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlPSE");
}

$numSCC = $resultSCC->numRows();

$orgao = $Linha[9]; // Órgão
$comissao = $Linha[10]; // Comissão de Licitação:
$objeto = $Linha[5]; // Objeto:
$modalidade = $Linha[11]; // Modalidade:
$regPreco = $Linha[2]; // Registro de Preço:
$proLicAno = $Linha[0] . "/" . $Linha[1]; // Processo Licitatório/Ano:

if (strlen($proLicAno) == 6) {
    $proLicAno = "000" . $proLicAno;
} elseif (strlen($proLicAno) == 7) {
    $proLicAno = "00" . $proLicAno;
} elseif (strlen($proLicAno) == 8) {
    $proLicAno = "0" . $proLicAno;
}

$licAno = $Linha[3] . "/" . $Linha[4]; // Licitação/Ano:

if (strlen($licAno) == 6) {
    $licAno = "000" . $licAno;
} elseif (strlen($proLicAno) == 7) {
    $licAno = "00" . $licAno;
} elseif (strlen($proLicAno) == 8) {
    $licAno = "0" . $licAno;
}

$NumLinhas = 1;
$AlturaCel = 5;

$TamObjeto = $pdf->GetStringWidth($objeto);

if ($TamObjeto <= 95) {
    $NumLinhas = 1;
    $AlturaCel = 5;
} elseif ($TamObjeto > 95 and $TamObjeto <= 190) {
    $NumLinhas = 2;
    $AlturaCel = 10;
} elseif ($TamObjeto > 280 and $TamObjeto <= 375) {
    $NumLinhas = 3;
    $AlturaCel = 15;
} else {
    $NumLinhas = 4;
    $AlturaCel = 20;
}

if ($numSCC > $NumLinhas) {
    $NumLinhas = $numSCC;
    $AlturaCel = 5 * $NumLinhas;
}

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(33, 5, "Órgão:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 7);
$pdf->Cell(100, 5, $orgao, 1, 0, "L", 0);

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(50, 5, "Comissão de Licitação:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 6);
$pdf->Cell(97, 5, $comissao, 1, 1, "L", 0);

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(33, 5, "Processo Licitatório/Ano:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 7);
$pdf->Cell(100, 5, $proLicAno, 1, 0, "L", 0);

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(50, 5, "Modalidade:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 7);
$pdf->Cell(97, 5, $modalidade, 1, 1, "L", 0);

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(33, 5, "Registro de Preço:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 7);
$pdf->Cell(100, 5, $regPreco, 1, 0, "L", 0);

$pdf->SetFont("Arial", "B", 7);
$pdf->Cell(50, 5, "Licitação/Ano:", 1, 0, "L", 1);
$pdf->SetFont("Arial", "", 7);
$pdf->Cell(97, 5, $licAno, 1, 1, "L", 0);

if ($NumLinhas > 1) {
    $Inicio = 0;
    for ($Quebra = 0; $Quebra < $NumLinhas; $Quebra ++) {
        if ($Quebra < $NumLinhas - 1) {
            $Borda = "LR";
        } else {
            $Borda = "B";
        }

        $pdf->SetFont("Arial", "", 7);

        if ($Quebra == 0) {
            $pdf->SetX(43);
            $pdf->Cell(100, 5, trim(substr($objeto, 0, 65)), $Borda, 0, 'L', 0);

            if ($Quebra <= $numSCC) {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, $arraySCC[$Quebra], $Borda . "R", 0, 'L', 0);
            } else {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, "", $Borda . "R", 1, 'L', 0);
            }

            $pdf->Ln(5);
        } elseif ($Quebra == 1) {
            $pdf->SetX(43);
            $pdf->Cell(100, 5, trim(substr($objeto, $Inicio, 65)), $Borda, 0, "L", 0);

            if ($Quebra <= $numSCC) {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, $arraySCC[$Quebra], $Borda . "R", 0, 'L', 0);
            } else {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, "", $Borda . "R", 1, 'L', 0);
            }

            $pdf->Ln(5);
        } elseif ($Quebra == 2) {
            $pdf->SetX(43);
            $pdf->Cell(100, 5, trim(substr($objeto, $Inicio, 65)), $Borda, 0, "L", 0);

            if ($Quebra <= $numSCC) {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, $arraySCC[$Quebra], $Borda . "R", 0, 'L', 0);
            } else {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, "", $Borda . "R", 1, 'L', 0);
            }

            $pdf->Ln(5);
        } else {
            $pdf->SetX(43);
            $pdf->Cell(100, 5, trim(substr($objeto, $Inicio, 65)), $Borda, 0, "L", 0);

            if ($Quebra <= $numSCC) {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, $arraySCC[$Quebra], $Borda . "R", 1, 'L', 0);
            } else {
                $pdf->SetX(193);
                $pdf->Cell(97, 5, "", $Borda . "R", 1, 'L', 0);
            }

            $pdf->Ln(5);
        }

        $Inicio = $Inicio + 65;
    }

    $pdf->SetXY(10, 54);
    $pdf->SetFont("Arial", "B", 7);
    $pdf->Cell(33, $AlturaCel, "Objeto:", 1, 0, "L", 1);

    $pdf->SetX(143);
    $pdf->SetFont("Arial", "B", 7);
    $pdf->Cell(50, $AlturaCel, "Solicitação de Compra/Contratação:", 1, 1, "L", 1);
} else {
    $pdf->SetFont("Arial", "B", 7);
    $pdf->Cell(33, $AlturaCel, "Objeto:", 1, 0, "L", 1);
    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(100, $AlturaCel, $objeto, 1, 0, "L", 0);

    $pdf->SetFont("Arial", "B", 7);
    $pdf->Cell(50, $AlturaCel, "Solicitação de Compra/Contratação:", 1, 0, "L", 1);
    $pdf->SetFont("Arial", "", 7);
    $pdf->Cell(97, $AlturaCel, $arraySCC[0], 1, 1, "L", 0);
}

$totalGeralEstimado = 0;
$totalEstimado = 0;
$totalSerHomologado = 0;

$numIL = $resultLotes->numRows();

// var_dump($numIL);die;

if ($numIL > 0) {
    $loteAtual = 0;
    $fornAtual = "";
    $valorTotalLogLote = 0;
    $valorTotalEstLote = 0;
    $vLTEst = 0;
    $vLTLog = 0;
    $flag2 = 0;

    while ($itensLote = $resultLotes->fetchRow()) {
        
        $fornDados = FormataCNPJ($itensLote[0]) . " - " . $itensLote[1];
        $ord            = $itensLote[2];
        $quant          = $itensLote[5];
        $valorEst       = $itensLote[6];
        $valorTEst      = $itensLote[7];
        $valorLog       = $itensLote[8];
        $valorTLog      = $itensLote[9];
        $marcaItem      = $itensLote[10];
        $modeloItem     = $itensLote[11];
        $motivoLogrado  = '.';
        
        if($ItemHomologado == 'N' && !empty($itensLote[17])) {
            $motivoLogrado = $itensLote[17];
        }

        $desc;
        $tipo;
        $codRed;
        $unid;
        $descDetalhada;

        if ($itensLote[14] != null) {
            $unid = $itensLote[14];
        } else {
            $unid = "nulo";
        }

        if ($itensLote[3] != null) {
            $conDesc = "SELECT EMATEPDESC FROM SFPC.TBMATERIALPORTAL WHERE CMATEPSEQU = $itensLote[3]";
            $desc = resultValorUnico(executarSQL($db, $conDesc));
            $tipo = "CADUM";
            $codRed = $itensLote[3];
            $descDetalhada = $itensLote[15];
            $descDetalhada = $descDetalhada == '' ? '' : $descDetalhada;

        } else {
            $conDesc = "SELECT ESERVPDESC FROM SFPC.TBSERVICOPORTAL WHERE CSERVPSEQU = $itensLote[4]";
            $desc = resultValorUnico(executarSQL($db, $conDesc));
            $tipo = "CADUS";
            $codRed = $itensLote[4];
            $descDetalhada = $itensLote[16];
        }

        $flag1 = false;

        // Verificar se tem a coluna item não logrado
        $coluna_nlogrado = ($ItemHomologado == 'N');

        if ($itensLote[12] == null || ($itensLote[12] != null && $coluna_nlogrado)) {
            if ($loteAtual == 0) {
                $pdf->SetFont("Arial", "B", 8);
                $pdf->Cell(280, 5, "ITENS DA LICITAÇÃO", "LTR", 1, "C", 1);
                $flag1 = true;
            }

            if ($loteAtual != $itensLote[13]) {
                // verificação se lote é o atual
                $b = 1;

                if ($flag1) {
                    $b = "LBR";
                    $flag1 = false;
                }

                $loteAtual = $itensLote[13];
                $fornAtual = "";

                $pdf->SetFont("Arial", "B", 8);
                $pdf->Cell(280, 5, "LOTE " . $itensLote[13], $b, 1, "C", 1);

                if ($fornAtual != $fornDados) {
                    // if verificar se o fornecedor é o atual
                    if ($showField === true) {
                        if($ItemHomologado == 'N') {
                            PitangRLMRPdf::generateHeaderItemWithDetailedDescriptionItemNaoLogrado($pdf, $fornDados);
                        } else {
                            PitangRLMRPdf::generateHeaderItemWithDetailedDescription($pdf, $fornDados);
                        }
                    } else {
                        PitangRLMRPdf::generateHeaderItem($pdf, $fornDados, $coluna_nlogrado);
                    }
                    $fornAtual = $fornDados;
                } // fim do if para verificar se o fornecedor é o atual
            }             // fim do if para verificar se é o lote atual
            else { // caso o lote seja o atual
                $loteAtual = $itensLote[13];
                if ($fornAtual != $fornDados) { // verificar se um novo fornecedor do lote atual
                    if ($showField === true) {
                        if($ItemHomologado != 'N') {
                            PitangRLMRPdf::generateHeaderItemWithDetailedDescription($pdf, $fornDados);
                        } else {
                            PitangRLMRPdf::generateHeaderItemWithDetailedDescriptionItemNaoLogrado($pdf, $fornDados);
                        }
                    } else {
                        PitangRLMRPdf::generateHeaderItem($pdf, $fornDados, $coluna_nlogrado);
                    }
                    $fornAtual = $fornDados;
                } // fim do if para verificar se o fornecedor é o atual
            } // fim do else do lote atual

            $totalEstimado += ($itensLote[12] == null) ? $itensLote[7] : 0;
            $totalSerHomologado += ($itensLote[12] == null) ? $itensLote[9] : 0;

            $vLTEst += $valorTEst;
            $vLTLog += $valorTLog;
          
            //$tDes = ceil(($pdf->GetStringWidth($desc)) / 25);
            //$tMar = ceil(($pdf->GetStringWidth($marcaItem)) / 25);
            //$tMod = ceil(($pdf->GetStringWidth($modeloItem)) / 25);
            //$tLog = ceil(($pdf->GetStringWidth($motivoLogrado)) / 15);
            //$tDescDet = ceil(($pdf->GetStringWidth($descDetalhada)) / 25);

            //$numLItem = max($tDes, $tMar, $tMod, $tDescDet, $tLog);
            //$altLItem = 5 * $numLItem;
            
            /**
             * dados para exibição
             */
            $data['altLItem'] = $altLItem;
            $data['quant'] = $quant;
            $data['valorEst'] = $valorEst;
            $data['valorTEst'] = $valorTEst;
            $data['valorLog'] = $valorLog;
            $data['valorTLog'] = $valorTLog;
            $data['descDetalhada'] = $descDetalhada;
            $data['itemNaoLogrado'] = $tLog;            

            // Itens
            $largura1 = PitangRLMRPdf::getSizeCell('descricao', $showField, $coluna_nlogrado);
            $largura2 = PitangRLMRPdf::getSizeCell('desc_detalhada', $showField, $coluna_nlogrado);    
            $largura3 = PitangRLMRPdf::getSizeCell('marca', $showField, $coluna_nlogrado); 
            $largura4 = PitangRLMRPdf::getSizeCell('modelo', $showField, $coluna_nlogrado);   
            $largura5 = 25; // largura coluna item não logrado          


            // Campos com texto dinâmicos
            $desc_stringHeight          = $pdf->GetStringHeight($largura1, 5, $desc, "L");
            $descDetalhada_stringHeight = $pdf->GetStringHeight($largura2, 5, $descDetalhada, "L");
            $marca_stringHeight         = $pdf->GetStringHeight($largura3, 5, $marcaItem, "L");
            $modelo_stringHeight        = $pdf->GetStringHeight($largura4, 5, $modeloItem, "L");
            $itemnlogrado_stringHeight  = $pdf->GetStringHeight($largura5, 5, $motivoLogrado, "L");
            
            $desc_info          = PitangRLMRPdf::generateDinamiCells($largura1, $desc, $desc_stringHeight);
            $descDetalhada_info = PitangRLMRPdf::generateDinamiCells($largura2, $descDetalhada, $descDetalhada_stringHeight);
            $marca_info         = PitangRLMRPdf::generateDinamiCells($largura3, $marcaItem, $marca_stringHeight);
            $modelo_info        = PitangRLMRPdf::generateDinamiCells($largura4, $modeloItem, $modelo_stringHeight);
            $itemnlogrado_info  = PitangRLMRPdf::generateDinamiCells($largura5, $motivoLogrado, $itemnlogrado_stringHeight);
            
            // Verificar altura max
            $array_max = array($desc_info['hm'], $marca_info['hm'], $modelo_info['hm']);
            if ($showField === true) {       
                $array_max[] = $descDetalhada_info['hm'];
            }

            if ($coluna_nlogrado) {       
                $array_max[] = $itemnlogrado_info['hm'];
            }

            $hight = max($array_max);                

            // Campos com texto dinâmicos
        

            // Ord
            $pdf->Cell(10, $hight, $ord, "LRT", 0, "C", 0);

            // Descrição
            $x = $pdf->GetX() + $largura1;
            $y = $pdf->GetY();
            $pdf->MultiCell($largura1, $desc_info['h1'], $desc_info['text'], "LRT", "L", 0);
            $pdf->SetXY($x, $y);

            //$pdf->Cell(40, $hm, substr($desc,0,10), 1, 0, "C", 0);
            
            
            $pdf->Cell(12, $hight, $tipo, "LRT", 0, "C", 0);
            $pdf->Cell(10, $hight, $codRed, "LRT", 0, "C", 0);
            $pdf->Cell(11, $hight, $unid, "LRT", 0, "C", 0);
            
            if ($showField === true) {       
                $x = $pdf->GetX() + $largura2;
                $y = $pdf->GetY();
                $pdf->MultiCell($largura2, $descDetalhada_info['h1'], $descDetalhada_info['text'], "LRT", "L", 0);
                $pdf->SetXY($x, $y);             
                
                //$pdf->Cell($largura2, $hight, $descDetalhada_info['text'], 1, 0, "C", 0);
            }
            
            $pdf->Cell(15, $hight, converte_valor_estoques($data['quant']), "LRT", 0, "C", 0);
            $pdf->Cell(18, $hight, converte_valor_estoques($data['valorEst']), "LRT", 0, "C", 0);
            $pdf->Cell(18, $hight, converte_valor_estoques($data['valorTEst']), "LRT", 0, "C", 0);
            $pdf->Cell(18, $hight, converte_valor_estoques($data['valorLog']), "LRT", 0, "C", 0);
            $pdf->Cell(18, $hight, converte_valor_estoques($data['valorTLog']), "LRT", 0, "C", 0);
            
            // Marca
            $x = $pdf->GetX() + $largura3;
            $y = $pdf->GetY();
            $pdf->MultiCell($largura3, $marca_info['h1'], $marca_info['text'], "LRT", "C", 0);
            $pdf->SetXY($x, $y);     

            // Modelo
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->MultiCell($largura4, $modelo_info['h1'], $modelo_info['text'], "LRT", "C", 0);
            $pdf->SetXY($x, $y);     
            
            // Cell em branco para marcar a borda
            $x = $pdf->GetX() + $largura4;
            $pdf->Cell($largura4, $hight, "", "LR",0, "L", 0);
            $pdf->SetXY($x, $y);     

            // Item n Logrado
            if($coluna_nlogrado) {
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->MultiCell($largura5, $itemnlogrado_info['h1'], $itemnlogrado_info['text'], "LRT", "C", 0);
                $pdf->SetXY($x, $y);   

                // Cell em branco para marcar a borda
                $x = $pdf->GetX() + $largura5;
                $pdf->Cell($largura5, $hight, "", "LR",0, "L", 0);
                $pdf->SetXY($x, $y); 
                
            }
            
            $pdf->Ln(ceil($hight));

            $flag2 ++;

            // var_dump($vLTEst); die;
            // $vLTEst

            if (($flag2 == $qtLotes[$loteAtual]) && (($vLTEst + $vLTLog) > 0)) { // IF DE IMPRESSÃO DAS INFORMAÇÕES DO FINAL DO LOTE

                $flag2 = 0;

                $pdf->SetFont("Arial", "", 7);

                
                if ($showField === true) {
                    $size_cell_1 = ($ItemHomologado == 'N') ? 156 : 176;
                    $size_cell_2 = ($ItemHomologado == 'N') ? 36 : 36;
                    $size_cell_3 = ($ItemHomologado == 'N') ? 88 : 68;

                    $pdf->Cell($size_cell_1, 5, "TOTAL LOTE " . $loteAtual, 1, 0, "L", 0);
                    $pdf->Cell($size_cell_2, 5, converte_valor_estoques($vLTEst), 1, 0, "L", 0);
                    $pdf->Cell($size_cell_3, 5, converte_valor_estoques($vLTLog), 1, 1, "L", 0);
                } else {
                    $pdf->Cell(126, 5, "TOTAL LOTE " . $loteAtual, 1, 0, "L", 0);
                    $pdf->Cell(36, 5, converte_valor_estoques($vLTEst), 1, 0, "L", 0);
                    $pdf->Cell(118, 5, converte_valor_estoques($vLTLog), 1, 1, "L", 0);
                }

                $vLTEst = 0;
                $vLTLog = 0;
            } // FINAL DO IF DE IMPRESSÃO DAS INFORMAÇÕES DO FINAL DO LOTE
        } // fim do if para item válido
        $totalGeralEstimado += $itensLote[7];
    } // fim do while
}
// ------------------------------------------------------------------------------------------
// IMPRIMIR OS VALORES TOTAIS DA LICITAÇÃO
$pdf->SetFont("Arial", "B", 8);
$pdf->Cell(140, 5, "TOTAL GERAL ESTIMADO", 1, 0, "L", 0);
$pdf->Cell(140, 5, converte_valor_estoques($totalGeralEstimado), 1, 1, "R", 0);
$pdf->Cell(140, 5, "TOTAL ESTIMADO(ITENS QUE LOGRARAM ÊXITO)", 1, 0, "L", 0);
$pdf->Cell(140, 5, converte_valor_estoques($totalEstimado), 1, 1, "R", 0);
$pdf->Cell(140, 5, "TOTAL A SER HOMOLOGADO(ITENS QUE LOGRARAM ÊXITO)", 1, 0, "L", 0);
$pdf->Cell(140, 5, converte_valor_estoques($totalSerHomologado), 1, 1, "R", 0);

// ------------------------------------------------------------------------------------------
// VERIFICAR SE ALGUMA PSE FOI GERADA / CABEÇALHO
// ------------------------------------------------------------------------------------------
if (($resultPSE->NumRows()) > 0) {
    $pdf->SetFont("Arial", "B", 7);
    $pdf->Cell(280, 5, "PRÉ-SOLICITAÇÃO DE EMPENHO (PSE)", 1, 1, "C", 1);

    $pdf->SetFont("Arial", "", 7);

    $pdf->Cell(15, 10, "NÚMERO", 1, 0, "C", 0); // ok
    $pdf->Cell(10, 10, "ANO", 1, 0, "C", 0); // ok

    $pdf->Cell(32, 5, "DATA/HORA", "LTR", 0, "C", 0); // ok
    $pdf->Cell(25, 5, "NÚMERO DO", "LTR", 0, "C", 0); // ok

    $pdf->Cell(20, 10, "VALOR", 1, 0, "C", 0); // ok
    $pdf->Cell(80, 10, "FORNECEDOR", 1, 0, "C", 0); // ok
    $pdf->Cell(80, 10, "SITUAÇÃO", 1, 0, "C", 0); // ok

    $pdf->Cell(18, 5, "DATA", "LTR", 1, "C", 0); // ok

    $pdf->SetX(($pdf->GetX()) + 25);
    $pdf->Cell(32, 5, "GERAÇÃO", "LBR", 0, "C", 0); // ok
    $pdf->Cell(25, 5, "BLOQUEIO", "LBR", 0, "C", 0); // ok

    $pdf->SetX(($pdf->GetX()) + 180);
    $pdf->Cell(18, 5, "SITUAÇÃO", "LBR", 1, "C", 0); // ok

    // ------------------------------------------------------------------------------------------
                                               // IMPRIMIR PSE GERADA / DADOS
                                               // ------------------------------------------------------------------------------------------
    while ($PSE = $resultPSE->fetchRow()) {

        $Data = substr($PSE[2], 0, 10);
        $Data = explode("-", $Data);
        $Hora = substr($PSE[2], 11, 8);
        $valor = converte_valor_estoques($PSE[5]);
        $fornCGCRaz = "";

        if ($PSE[6]) {
            $fornCGCRaz = FormataCNPJ($PSE[6]) . " " . $PSE[8];
        } else {
            $fornCGCRaz = FormataCNPJ($PSE[7]) . " " . $PSE[8];
        }

        $SituacaoPSE = "";
        $DataPSE = "";

        if ($PSE[9] != NULL) {
            $SituacaoPSE = "PSE RECUSADA POR MOTIVO " . $PSE[10];
            $DataPSE = $PSE[12];
        } elseif ($PSE[14] != NULL) {
            $SituacaoPSE = "EMPENHO ANULADO " . $PSE[15];
            $DataPSE = $PSE[14];
        } elseif ($PSE[16] != NULL) {
            $SituacaoPSE = "EMPENHADO " . $PSE[17] . "/" . $PSE[18];
            $DataPSE = $PSE[16];
        } elseif ($PSE[13] != NULL) {
            $SituacaoPSE = "SE CANCELADA";
            $DataPSE = $PSE[13];
        } elseif ($PSE[11] != NULL) {
            $SituacaoPSE = "SE GERADA";
            $DataPSE = $PSE[12];
        }

        if ($DataPSE != "" || $DataPSE != NULL) {
            $DataPSE = explode("-", $DataPSE);
            $DataPSE = $DataPSE[2] . "/" . $DataPSE[1] . "/" . $DataPSE[0];
        }

        $tamForn = ceil(($pdf->GetStringWidth($fornCGCRaz)) / 50);
        $tamDesc = ceil(($pdf->GetStringWidth($SituacaoPSE)) / 50);

        $numLPSE = 1;
        $altLPSE = 5;

        if ($tamForn > $tamDesc) {
            $numLPSE = $tamForn;
        } else {
            $numLPSE = $tamDesc;
        }

        $altLPSE = 5 * $numLPSE;

        if ($numLPSE > 1) {
            $Inicio = 0;

            for ($q = 0; $q < $numLPSE; $q ++) {

                $pdf->SetFont("Arial", "", 7);

                if ($q == 0) {
                    $pdf->Cell(15, $altLPSE, $PSE[0], 1, 0, "C", 0); // NUMERO
                    $pdf->Cell(10, $altLPSE, $PSE[1], 1, 0, "C", 0); // ANO
                    $pdf->Cell(32, $altLPSE, $Data[2] . "/" . $Data[1] . "/" . $Data[0] . " " . $Hora, 1, 0, "C", 0); // DATA/HORA
                    $pdf->Cell(25, $altLPSE, $PSE[3] . "/" . $PSE[4], 1, 0, "C", 0); // NUMERO DO BLOQUEIO
                    $pdf->Cell(20, $altLPSE, $valor, 1, 0, "C", 0); // VALOR

                    if ($q <= $tamForn) { // FORNECEDOR
                        $pdf->Cell(80, 5, (substr($fornCGCRaz, 0, 50)), "LTR", 0, "L", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LTR", 0, 'L', 0);
                    } // FORNECEDOR

                    if ($q <= $tamDesc) { // SITUAÇÃO
                        $pdf->Cell(80, 5, (substr($SituacaoPSE, 0, 50)), "LTR", 0, "C", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LTR", 0, 'C', 0);
                    } // SITUAÇÃO

                    $pdf->Cell(18, $altLPSE, $DataPSE, 1, 0, "C", 0); // DATA SITUAÇÃO

                    $pdf->Ln(5);
                } elseif ($q == ($numLPSE - 1)) {
                    $pdf->SetX(112);

                    if ($q <= $tamForn) { // FORNECEDOR
                        $pdf->Cell(80, 5, (substr($fornCGCRaz, $Inicio, 50)), "LBR", 0, "L", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LBR", 0, 'L', 0);
                    } // FORNECEDOR

                    if ($q <= $tamDesc) { // SITUAÇÃO
                        $pdf->Cell(80, 5, (substr($SituacaoPSE, $Inicio, 50)), "LBR", 1, "C", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LBR", 1, 'C', 0);
                    } // SITUAÇÃO

                    // $pdf->Ln(5);
                } else {
                    $pdf->SetX(112);

                    if ($q <= $tamForn) { // FORNECEDOR
                        $pdf->Cell(80, 5, (substr($fornCGCRaz, $Inicio, 50)), "LR", 0, "L", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LR", 0, 'L', 0);
                    } // FORNECEDOR

                    if ($q <= $tamDesc) { // SITUAÇÃO
                        $pdf->Cell(80, 5, (substr($SituacaoPSE, $Inicio, 50)), "LR", 1, "C", 0);
                    } else {
                        $pdf->Cell(80, 5, "", "LR", 1, 'C', 0);
                    } // SITUAÇÃO
                }
                $Inicio = $Inicio + 50;
            }
        } else {
            $pdf->Cell(15, $altLPSE, $PSE[0], 1, 0, "C", 0); // NUMERO
            $pdf->Cell(10, $altLPSE, $PSE[1], 1, 0, "C", 0); // ANO
            $pdf->Cell(32, $altLPSE, $Data[2] . "/" . $Data[1] . "/" . $Data[0] . " " . $Hora, 1, 0, "C", 0); // DATA/HORA
            $pdf->Cell(25, $altLPSE, $PSE[3] . "/" . $PSE[4], 1, 0, "C", 0); // NUMERO DO BLOQUEIO
            $pdf->Cell(20, $altLPSE, $valor, 1, 0, "C", 0); // VALOR
            $pdf->Cell(80, 5, $fornCGCRaz, 1, 0, "L", 0);
            $pdf->Cell(80, 5, $SituacaoPSE, 1, 0, "L", 0);
            $pdf->Cell(18, $altLPSE, $DataPSE, 1, 1, "C", 0); // DATA SITUAÇÃO
        }
    }
}

$db->disconnect();

$pdf->Output();
