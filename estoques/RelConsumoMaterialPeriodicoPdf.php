<?php

# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsumoMaterialPeriodicoPdf.php
# Objetivo: Programa de Impressão do Relatório de Consumo de Material Periódico (PDF).
# Autor:    Carlos Abreu
# Data:     11/01/2007
# ------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     09/05/2007 - Ajuste no sql para padronização de relatórios
# ------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     21/08/2007 - Ajuste no sql para corrigir geracao de relatório
# -------------------------------------------------------------------------------
# Alterado: Pitang Agile IT
# Data:     21/07/2015
# Objetivo: CR91296 - Estoques - Relatório de Consumo
# ------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 205790
# ------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/RelConsumoMaterialPeriodico.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $Almoxarifado = $_GET['Almoxarifado'];
    $DataFim = $_GET['DataFim'];
    $DataIni = $_GET['DataIni'];
    $ItemZerado = $_GET['ItemZerado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = 'Relatório de Consumo Periódico de Material';

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF('L', 'mm', 'A4');

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220, 220, 220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont('Arial', '', 9);

# Fazer os sqls dos primeiros dados da página #
$db = Conexao();

# Pega os dados do Almoxarifado #
$sql = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Campo = $res->fetchRow();
    $DescAlmoxarifado = $Campo[0];
}

$pdf->Cell(30, 5, 'ALMOXARIFADO', 1, 0, 'L', 1);
$pdf->Cell(250, 5, $DescAlmoxarifado, 1, 1, 'L', 0);
$pdf->Cell(30, 5, 'PERÍODO', 1, 0, 'L', 1);
$pdf->Cell(250, 5, $DataIni.' a '.$DataFim, 1, 1, 'L', 0);
$pdf->ln(5);

$pdf->Cell(206, 5, 'DESCRIÇÃO DO ITEM', 1, 0, 'C', 1);
$pdf->Cell(9, 5, 'UND', 1, 0, 'C', 1);
$pdf->Cell(17, 5, 'CÓD RED', 1, 0, 'C', 1);
$pdf->Cell(24, 5, 'QTD.', 1, 0, 'C', 1);
$pdf->Cell(24, 5, 'VALOR', 1, 1, 'C', 1);

# Sql principal #

$DataInibd = substr($DataIni, 6, 4).'-'.substr($DataIni, 3, 2).'-'.substr($DataIni, 0, 2);
$DataFimbd = substr($DataFim, 6, 4).'-'.substr($DataFim, 3, 2).'-'.substr($DataFim, 0, 2);

if ($ItemZerado == 'on') {
    $sql = 'SELECT B.EMATEPDESC, C.EUNIDMSIGL, A.CMATEPSEQU, ';
    $sql .= '       CASE WHEN E.QTDE IS NOT NULL THEN E.QTDE ELSE 0 END, ';
    $sql .= '       CASE WHEN E.SOMA IS NOT NULL THEN E.SOMA ELSE 0 END ';
    $sql .= '  FROM SFPC.TBARMAZENAMENTOMATERIAL A ';
    $sql .= ' INNER JOIN SFPC.TBMATERIALPORTAL B ON A.CMATEPSEQU = B.CMATEPSEQU ';
    $sql .= ' INNER JOIN SFPC.TBUNIDADEDEMEDIDA C ON B.CUNIDMCODI = C.CUNIDMCODI ';
    $sql .= '  LEFT OUTER JOIN (';
    $sql .= '       SELECT A.CMATEPSEQU, ';
    $sql .= "              SUM(CASE WHEN (C.FTIPMVTIPO = 'S') THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END) AS QTDE, ";
    $sql .= "              SUM(CASE WHEN (C.FTIPMVTIPO = 'S') THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END * ";
    $sql .= '              CASE WHEN A.CTIPMVCODI IN (4,19,20) THEN ';
    $sql .= '              ( ';
    $sql .= '              SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ';
    $sql .= '               WHERE CTIPMVCODI IN (4,19,20) ';
    $sql .= '                 AND CMATEPSEQU = A.CMATEPSEQU ';
    $sql .= '                 AND CREQMASEQU = A.CREQMASEQU ';
    $sql .= "                AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
    $sql .= '                 AND TMOVMAULAT = ( ';
    $sql .= '                       SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ';
    $sql .= '                        WHERE CTIPMVCODI IN (4,19,20) ';
    $sql .= '                          AND CMATEPSEQU = A.CMATEPSEQU ';
    $sql .= '                          AND CREQMASEQU = A.CREQMASEQU ';
    $sql .= "                          AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
    $sql .= '                     ) ';
    $sql .= '              ) ';
    $sql .= '              ELSE ';
    $sql .= '                  A.VMOVMAVALO ';
    $sql .= '              END ';
    $sql .= '              ) AS SOMA ';
    $sql .= '         FROM SFPC.TBMOVIMENTACAOMATERIAL A, ';
    $sql .= '              SFPC.TBTIPOMOVIMENTACAO C ';
    $sql .= "        WHERE A.CALMPOCODI = $Almoxarifado ";
    $sql .= "          AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
    $sql .= "          AND A.DMOVMAMOVI BETWEEN '$DataInibd' AND '$DataFimbd' ";
    $sql .= '          AND A.CTIPMVCODI IN (2,4,18,19,20,21,22) ';
    $sql .= '          AND A.CTIPMVCODI = C.CTIPMVCODI ';
    $sql .= '        GROUP BY A.CMATEPSEQU';
    $sql .= '       ) AS E ';
    $sql .= '    ON A.CMATEPSEQU = E.CMATEPSEQU, ';
    $sql .= '       SFPC.TBLOCALIZACAOMATERIAL D ';
    $sql .= ' WHERE A.CLOCMACODI = D.CLOCMACODI ';
    $sql .= "   AND D.CALMPOCODI = $Almoxarifado ";
    $sql .= ' ORDER BY B.EMATEPDESC';
} else {
    $sql = 'SELECT B.EMATEPDESC, D.EUNIDMSIGL, A.CMATEPSEQU, ';
    $sql .= "       SUM(CASE WHEN (C.FTIPMVTIPO = 'S') THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END) AS QTDE, ";
    $sql .= "       SUM(CASE WHEN (C.FTIPMVTIPO = 'S') THEN A.AMOVMAQTDM ELSE -A.AMOVMAQTDM END * ";
    $sql .= '       CASE WHEN A.CTIPMVCODI IN (4,19,20) THEN ';
    $sql .= '       ( ';
    $sql .= '       SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ';
    $sql .= '        WHERE CTIPMVCODI IN (4,19,20) ';
    $sql .= '          AND CMATEPSEQU = A.CMATEPSEQU ';
    $sql .= '          AND CREQMASEQU = A.CREQMASEQU ';
    $sql .= "          AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
    $sql .= '          AND TMOVMAULAT = ( ';
    $sql .= '                SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ';
    $sql .= '                 WHERE CTIPMVCODI IN (4,19,20) ';
    $sql .= '                   AND CMATEPSEQU = A.CMATEPSEQU ';
    $sql .= '                   AND CREQMASEQU = A.CREQMASEQU ';
    $sql .= "                   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
    $sql .= '              ) ';
    $sql .= '       ) ';
    $sql .= '       ELSE ';
    $sql .= '           A.VMOVMAVALO ';
    $sql .= '       END ';
    $sql .= '       ) AS SOMA ';
    $sql .= '  FROM SFPC.TBMOVIMENTACAOMATERIAL A ';
    $sql .= ' INNER JOIN SFPC.TBMATERIALPORTAL B ';
    $sql .= '    ON A.CMATEPSEQU = B.CMATEPSEQU ';
    $sql .= ' INNER JOIN SFPC.TBUNIDADEDEMEDIDA D ';
    $sql .= '    ON B.CUNIDMCODI = D.CUNIDMCODI, ';
    $sql .= '       SFPC.TBTIPOMOVIMENTACAO C ';
    $sql .= " WHERE A.CALMPOCODI = $Almoxarifado ";
    $sql .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
    $sql .= "   AND A.DMOVMAMOVI BETWEEN '$DataInibd' AND '$DataFimbd' ";
    $sql .= '   AND A.CTIPMVCODI IN (2,4,18,19,20,21,22) ';
    $sql .= '   AND A.CTIPMVCODI = C.CTIPMVCODI ';
    $sql .= ' GROUP BY A.CMATEPSEQU, B.EMATEPDESC, D.EUNIDMSIGL  ';
    $sql .= ' ORDER BY B.EMATEPDESC';
}
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $rows = $res->numRows();
    if ($rows == 0) {
        $Mensagem = 'Nenhuma Ocorrência Encontrada';
        $Url = 'RelConsumoMaterialPeriodico.php?Mensagem='.urlencode($Mensagem).'&Mens=1&Tipo=1';
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header('location: '.$Url);
        exit;
    } else {
        for ($i = 0; $i < $rows; ++$i) {
            $Linha = $res->fetchRow();

            $DescMaterial = $Linha[0];

                        # Quebra de Linha para Descrição do Material #
                        $Fim = 105; //52
                        $Coluna = 200; //92 - Grande demais, corta palavras, pequeno, provoca espaços desnecessários

                        $DescMaterialSepara = SeparaFrase($DescMaterial, $Fim);
            $TamDescMaterial = $pdf->GetStringWidth($DescMaterialSepara);

            if ($TamDescMaterial <= $Coluna) {
                $LinhasMat = 1;
                $AlturaMat = 5;
            } elseif ($TamDescMaterial <= 2 * ($Coluna - 2)) {
                $LinhasMat = 2;
                $AlturaMat = 10;
            } elseif ($TamDescMaterial <= 3 * ($Coluna - 4)) {
                $LinhasMat = 3;
                $AlturaMat = 15;
            } elseif ($TamDescMaterial <= 4 * ($Coluna - 6)) {
                $LinhasMat = 4;
                $AlturaMat = 20;
            } elseif ($TamDescMaterial <= 5 * ($Coluna - 8)) {
                $LinhasMat = 5;
                $AlturaMat = 25;
            } elseif ($TamDescMaterial <= 6 * ($Coluna - 10)) {
                $LinhasMat = 6;
                $AlturaMat = 30;
            } elseif ($TamDescMaterial <= 7 * ($Coluna - 10)) {
                $LinhasMat = 7;
                $AlturaMat = 35;
            } else {
                $LinhasMat = 8;
                $AlturaMat = 40;
            }
            if ($TamDescMaterial > $Coluna or $TamJustMaterial > $ColunaJust) {
                $Inicio = 0;
                $pdf->Cell(206, $AlturaMat, '', 1, 0, 'L', 0);
                for ($Quebra = 0; $Quebra < $LinhasMat; ++$Quebra) {
                    if ($Quebra == 0) {
                        $pdf->SetX(10);
                        $pdf->Cell(206, 5, trim(substr($DescMaterialSepara, $Inicio, $Fim)), 0, 0, 'L', 0);
                        $pdf->Cell(9, $AlturaMat, $Linha[1], 1, 0, 'C', 0);
                        $pdf->Cell(17, $AlturaMat, $Linha[2], 1, 0, 'R', 0);
                        $pdf->Cell(24, $AlturaMat, converte_quant($Linha[3]), 1, 0, 'R', 0);
                        $pdf->Cell(24, $AlturaMat, converte_valor_estoques($Linha[4]), 1, 0, 'R', 0);
                        $pdf->Ln(5);
                    } else {
                        $pdf->Cell(206, 5, trim(substr($DescMaterialSepara, $Inicio, $Fim)), 0, 0, 'L', 0);
                        $pdf->Ln(5);
                    }
                    $Inicio = $Inicio + $Fim;
                }
            } else {
                $pdf->Cell(206, 5, $DescMaterial, 1, 0, 'L', 0);
                $pdf->Cell(9, 5, $Linha[1], 1, 0, 'C', 0);
                $pdf->Cell(17, 5, $Linha[2], 1, 0, 'R', 0);
                $pdf->Cell(24, 5, converte_quant($Linha[3]), 1, 0, 'R', 0);
                $pdf->Cell(24, 5, converte_valor_estoques($Linha[4]), 1, 1, 'R', 0);
            }
            $QtdTotal += $Linha[3];
            $ValorTotal += $Linha[4];
        }
    }

        # Imprime resumo #
        $pdf->Cell(232, 5, 'TOTAL QUANTIDADE / VALOR', 1, 0, 'R', 1);
    $pdf->Cell(24, 5, converte_quant(sprintf('%01.2f', str_replace(',', '.', $QtdTotal))), 1, 0, 'R', 0);
    $pdf->Cell(24, 5, converte_valor_estoques(sprintf('%01.4f', str_replace(',', '.', $ValorTotal))), 1, 1, 'R', 0);
}
$db->disconnect();
$pdf->Output();
