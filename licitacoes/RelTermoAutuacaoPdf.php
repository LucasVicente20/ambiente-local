<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: RelTermoAutuacaoPdf.php
// Autor: Roberta Costa
// Data: 28/12/2004
// Alterado: Álvaro Faria
// Dara: 19/06/2006
// Objetivo: Programa que Imprime o Termo de Autuação
//
// ####################################################################
// Alterado: Rossana
// Data: 24/05/2007 ==> Liberar Permissão Remunerada de Uso para Tomada de Preços

// ####################################################################
// Alterado: Heraldo Botelho
// Data: 22/04/2013 ==> Total Valor Estimado passa ser é calculado pela função
// totalValorEstimado($db,$processo,$ano,$grupo,$comissao,$orgao)

// ####################################################################
// Alterado: Pitang Agile IT
// Data: 27/04/2016
// Objetivo: Requisito #129505 - Relatório Termo de Autuação
// Versão: v1.34.0

// #####################################################################
// OBS.: Tabulação 2 espaços
// ----------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Processo = $_GET['Processo'];
    $Ano = $_GET['Ano'];
    $Comissao = $_GET['Comissao'];
    $Orgao = $_GET['Orgao'];
    $NumPortaria1 = $_GET['NumPortaria1'];
    $NumPortaria2 = $_GET['NumPortaria2'];
    $NumPortaria3 = $_GET['NumPortaria3'];
    $NumPortaria4 = $_GET['NumPortaria4'];
    $DataPublicacao1 = $_GET['DataPublicacao1'];
    $DataPublicacao2 = $_GET['DataPublicacao2'];
    $DataPublicacao3 = $_GET['DataPublicacao3'];
    $DataPublicacao4 = $_GET['DataPublicacao4'];
    $Publicacao = $_GET['Publicacao'];
    $Responsavel = $_GET['Responsavel'];
    $Presidente = strtoupper2(trim(urldecode($_GET['Presidente'])));
    $Membro1 = strtoupper2(trim(urldecode($_GET['Membro1'])));
    $Membro2 = strtoupper2(trim(urldecode($_GET['Membro2'])));
    $Membro3 = strtoupper2(trim(urldecode($_GET['Membro3'])));
    $Membro4 = strtoupper2(trim(urldecode($_GET['Membro4'])));
    $DataTermo = $_GET['DataTermo'];
}

// Montando o(s) Número(s) da Portaria #
if ($Publicacao == "S") {
    if ($NumPortaria1 != "") {
        $NumPortaria = $NumPortaria1;
    }
    if ($NumPortaria2 != "") {
        $NumPortaria .= ", " . $NumPortaria2;
    }
    if ($NumPortaria3 != "") {
        $NumPortaria .= ", " . $NumPortaria3;
    }
    if ($NumPortaria4 != "") {
        $NumPortaria .= ", " . $NumPortaria4;
    }
    if (substr($NumPortaria, 0, 2) == ", ") {
        $NumPortaria = substr($NumPortaria, 2);
    }
    $NumPortaria = "nº " . $NumPortaria;
    
    // Montando a(s) Data(s) da Publicação #
    if ($DataPublicacao1 != "") {
        $DataPublicacao = $DataPublicacao1;
    }
    if ($DataPublicacao2 != "") {
        $DataPublicacao .= ", " . $DataPublicacao2;
    }
    if ($DataPublicacao3 != "") {
        $DataPublicacao .= ", " . $DataPublicacao3;
    }
    if ($DataPublicacao4 != "") {
        $DataPublicacao .= ", " . $DataPublicacao4;
    }
    if (substr($DataPublicacao, 0, 2) == ", ") {
        $DataPublicacao = substr($DataPublicacao, 2);
    }
} elseif ($Publicacao == "N") {
    if ($NumPortaria1 != "") {
        $NumDataPub = " " . $NumPortaria1 . " de " . $DataPublicacao1;
    }
    if ($NumPortaria2 != "") {
        $NumDataPub .= ", " . $NumPortaria2 . " de " . $DataPublicacao2;
    }
    if ($NumPortaria3 != "") {
        $NumDataPub .= ", " . $NumPortaria3 . " de " . $DataPublicacao3;
    }
    if ($NumPortaria4 != "") {
        $NumDataPub .= ", " . $NumPortaria4 . " de " . $DataPublicacao4;
    }
    if (substr($NumDataPub, 0, 2) == ", ") {
        $NumDataPub = substr($NumDataPub, 2);
    }
    $NumDataPub = "nº " . $NumDataPub;
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

// Informa o Título do Relatório #
$TituloRelatorio = "";

// Classes FPDF #
class PDF extends FPDF
{
    // Cabeçalho #
    function Header()
    {
        // #### Verificar endereço quando passar para produção #####
        Global $CaminhoImagens;
        $this->Image("$CaminhoImagens/brasaopeq.jpg", 105, 5, 0);
        $this->Image("$CaminhoImagens/brasaobg.jpg", 5, 25, 0);
        $this->SetMargins(35, 10, 15);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(25, 20, "", 0, 0, "L", 0);
        $this->Cell(0, 20, "Prefeitura do Recife", 0, 0, "L", 0);
        $this->Cell(0, 20, "EMPREL", 0, 0, "R", 0);
        $this->Ln(1);
        $Empresa = $_SESSION['_egruatdesc_'];
        $this->Cell(0, 25, "Secretaria de Administração e Gestão de Pessoas ", 0, 0, "L", 0);
        $this->Cell(0, 25, "Gerência Geral de Licitações e Compras", 0, 0, "R", 0);
        $this->Ln(1);
        $this->Cell(0, 30, "Portal de Compras", 0, 0, "L", 0);
        $this->Cell(0, 30, "", 0, 0, "R");
        $this->Ln(20);
    }
    
    // Rodapé #
    function Footer()
    {
        $this->SetFont("Arial", "", 10);
        $this->SetY(- 29);
        $this->Cell(0, 30, "Emissão: " . date("d/m/Y H:i:s"), 0, 0, "L");
        $this->Line(10, 280, 200, 280);
        $this->SetY(- 19);
        $this->Cell(0, 10, "Página: " . $this->PageNo() . "/{nb}", 0, 0, "R");
    }
}

// Cria o objeto PDF, o Default é formato Retrato, A4 e a medida em milímetros #
$pdf = new PDF("P", "mm", "A4");

// Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

// Adiciona uma página no documento #
$pdf->AddPage();

// Configura as margens #
$pdf->SetMargins(35, 10, 15);

// Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial", "", 10);

// Carrega os dados da licitação selecionada #
$db = Conexao();
$sql = "SELECT A.CMODLICODI, D.EMODLIDESC, A.CLICPOCODL, A.ALICPOANOL, ";
$sql .= "       A.XLICPOOBJE, B.ECOMLIDESC, A.CORGLICODI, C.EORGLIDESC, ";
$sql .= "       A.VLICPOVALE, A.FLICPOREGP ";
$sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBORGAOLICITANTE C, SFPC.TBMODALIDADELICITACAO D ";
$sql .= " WHERE A.CMODLICODI = D.CMODLICODI AND A.CLICPOPROC = $Processo ";
$sql .= "   AND A.ALICPOANOP = $Ano AND A.CCOMLICODI = $Comissao ";
$sql .= "   AND A.CCOMLICODI = B.CCOMLICODI AND A.CORGLICODI = C.CORGLICODI ";
$sql .= "   AND A.CORGLICODI = $Orgao AND A.CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        $Modalidade = $Linha[0];
        $ModalidadeDescricao = $Linha[1];
        $Licitacao = substr($Linha[2] + 10000, 1);
        $LicitacaoAno = $Linha[3];
        $Objeto = trim($Linha[4]);
        $ComissaoDescricao = $Linha[5];
        $OrgaoCodigo = $Linha[6];
        $OrgaoDescricao = $Linha[7];
        
        $TotalEstimado = "0,00";
        // Trecho inibido por Heraldo
        if ($Linha[8] != "") {
            $TotalEstimado = converte_valor($Linha[8]);
        }
        // -- final da inibição
        $Registro = $Linha[9];
    }
}

// Bloqueios do Processo Licitatório #
$sql = "SELECT CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, TUNIDOEXER ";
$sql .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $Ano ";
$sql .= "   AND CCOMLICODI = $Comissao AND CORGLICODI = $Orgao";
$sql .= "   AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        $Bloqueio .= $Linha[3] . "." . $Linha[0] . "." . sprintf("%02s", $Linha[1]) . ".1." . $Linha[2] . ", ";
    }
}

$sql = "
    select
  		 lp.vlicpovale,
  		 lp.vlicpovalh,
  		 lp.vlicpotges
    from sfpc.tbsolicitacaolicitacaoportal slp
         inner join sfpc.tblicitacaoportal lp
                 on lp.clicpoproc = slp.clicpoproc and lp.alicpoanop = slp.alicpoanop and lp.cgrempcodi = slp.cgrempcodi and lp.ccomlicodi = slp.ccomlicodi and lp.corglicodi = slp.corglicodi
   where slp.alicpoanop = $Ano
         and slp.clicpoproc = $Processo
         and slp.ccomlicodi = $Comissao
         and slp.corglicodi = $Orgao
         and slp.cgrempcodi =  " . $_SESSION['_cgrempcodi_'] . "";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        
        // // Calcular valor estimado
        $TotalEstimado = totalValorEstimado($db, $Processo, $Ano, $_SESSION['_cgrempcodi_'], $Comissao, $Orgao);
        if (count($Linha) > 0) {
            $TotalEstimado = converte_valor_estoques($TotalEstimado);
        }
    }
}

$Texto = "Considerando a solicitação/autorização do ordenador de despesas para instauração de processo ";
$Texto .= "licitatório e, ainda, a estimativa de preços realizada no valor de R$ $TotalEstimado, ";
if ($Registro != "S") {
    $Texto .= "bem como, a existência de recursos orçamentários bloqueados pela(s) nota(s) de ";
    $Texto .= "número(s) $Bloqueio ";
}
$Texto .= "esta $ComissaoDescricao, nomeada pela(s) Portaria(s)";
if ($Publicacao == "S") {
    $Texto .= " $NumPortaria ";
} elseif ($Publicacao == "N") {
    $Texto .= " $NumDataPub  ";
}
$Texto .= "do Exmo. Sr. ";
if ($Responsavel == 1) {
    $Texto .= "Prefeito do Recife, ";
} elseif ($Responsavel == 2) {
    $Texto .= "Presidente deste Órgão, ";
}
if ($Publicacao == "S") {
    $Texto .= "publicada(s) no Diário Oficial do Município, edição(ões) de $DataPublicacao, ";
}
$Texto .= " delibera em AUTUAR ";
$Texto .= "o presente processo licitatório, nº de série $Processo/$Ano, enquadrando-o na modalidade $ModalidadeDescricao ";
if ($Registro == "S") {
    $Texto .= "PARA REGISTRO DE PREÇO	";
    // Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso
    if ($Modalidade == 3 or $Modalidade == 2) {
        $Texto .= "/ PERMISSÃO REMUNERADA DE USO ";
    }
}
$Texto .= "de nº $Licitacao/$LicitacaoAno, cujo objeto consiste $Objeto";
$TamObjeto = strlen($Objeto) - 1;
if ($TamObjeto != strrpos($Objeto, ".")) {
    $Texto .= ".";
}

$pdf->SetFont("Arial", "", 9);
$pdf->Cell(10, 10, "", 0, 0, "L", 0);
$pdf->Cell(140, 5, $OrgaoDescricao, 0, 1, "C", 0);
$pdf->Cell(10, 10, "", 0, 0, "L", 0);
$pdf->Cell(140, 5, $ComissaoDescricao, 0, 1, "C", 0);
$pdf->ln(5);
$pdf->SetFont("Arial", "B", 9);
$pdf->Cell(10, 10, "", 0, 0, "L", 0);
$pdf->Cell(140, 5, "TERMO DE AUTUAÇÃO", 0, 0, "C", 0);

$pdf->SetFont("Arial", "", 9);
$pdf->ln(10);
$pdf->MultiCell(160, 5, $Texto, 0, "J", 0);
$pdf->ln(5);

$Data = DataExtensoRecife($DataTermo);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 10, $Data . ".", 0, 0, "L", 0);
$pdf->ln(25);

$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "__________________________________________________________________________", 0, 1, "L", 0);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(140, 5, "Presidente/Pregoeiro: $Presidente", 0, 0, "L", 0);
$pdf->ln(20);

$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "__________________________________________________________________________", 0, 1, "L", 0);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "Membro: $Membro1", 0, 0, "L", 0);
$pdf->ln(20);

$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "__________________________________________________________________________", 0, 1, "L", 0);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "Membro: $Membro2", 0, 0, "L", 0);
$pdf->ln(20);

$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "__________________________________________________________________________", 0, 1, "L", 0);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "Membro: $Membro3", 0, 0, "L", 0);
$pdf->ln(20);

$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "__________________________________________________________________________", 0, 1, "L", 0);
$pdf->Cell(15, 10, "", 0, 0, "L", 0);
$pdf->Cell(150, 5, "Membro: $Membro4", 0, 0, "L", 0);
$pdf->ln(10);

// Pendências do Processo Licitatório #
$sql = "SELECT B.ETIPPEDESC ";
$sql .= "  FROM SFPC.TBLICITACAOPENDENCIAS A, SFPC.TBTIPOPENDENCIA B";
$sql .= " WHERE A.CLICPOPROC = $Processo AND A.ALICPOANOP = $Ano ";
$sql .= "   AND A.CCOMLICODI = $Comissao AND A.CORGLICODI = $Orgao";
$sql .= "   AND A.CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . " AND A.CTIPPECODI = B.CTIPPECODI ";
$sql .= " ORDER BY B.ETIPPEDESC ";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    for ($i = 0; $i < $Rows; $i ++) {
        if ($i == 0) {
            $pdf->Cell(175, 10, "PENDÊNCIAS DETECTADAS:", 0, 0, "L", 0);
            $pdf->ln(10);
        }
        $pdf->SetFont("Arial", "", 9);
        $Linha = $result->fetchRow();
        $pdf->Cell(175, 5, "* " . $Linha[0], 0, 1, "L", 0);
    }
    $pdf->ln(10);
}
$db->disconnect();
$pdf->Output();
?>
