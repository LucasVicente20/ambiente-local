<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version GIT: v1.21.0-16-g300d38d
 *
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado:  Pitang Agile IT
 * Data:      21/07/2015
 * Objetivo:  CR76836 - Licitações Concluídas
 * -----------------------------------------------------------------------------
 */

// Acesso ao arquivo de funções #
include '../licitacoes/funcoesLicitacoes.php';
require_once '../compras/funcoesCompras.php';
require_once 'TemplateAppPopup.php';

$tpl = new TemplateAppPopup('templates/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.html');

// Executa o controle de segurança #
// session_start();
// Seguranca();

$_SESSION['PermitirAuditoria'] = 'N'; // Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Processo = filter_var($_GET['processo'], FILTER_SANITIZE_NUMBER_INT);
$ProcessoAno = filter_var($_GET['ano'], FILTER_SANITIZE_NUMBER_INT);
$ComissaoCodigo = filter_var($_GET['comissao'], FILTER_SANITIZE_NUMBER_INT);
$GrupoCodigo = filter_var($_GET['grupo'], FILTER_SANITIZE_NUMBER_INT);
$OrgaoLicitanteCodigo = filter_var($_GET['orgaoLicitante'], FILTER_SANITIZE_NUMBER_INT);

// Pega as atas da fase de homologação da licitação #
$sql = 'SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ';
$sql .= '       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ';
$sql .= '       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT';
$sql .= '  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ';
$sql .= '    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ';
$sql .= '   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ';
$sql .= '   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ';
$sql .= ' 	    LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI';
$sql .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
$sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
$sql .= '   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI = 13 '; // Apenas fase de homologação
$sql .= ' ORDER BY A.AFASESORDE';

$db = Conexao();
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $result->numRows();

    if ($Rows == 0) {
        $tpl->block('BLOCO_SEM_RESULTADO');
    }
    $cont = 0;
    $dados = array();
    while ($cols = $result->fetchRow()) {
        ++$cont;
        $dados[$cont - 1] = "$cols[8];$cols[11];$cols[9];$cols[4];$cols[7];$cols[10];$cols[12]";
    }
    for ($Row = 0; $Row < $Rows; ++$Row) {
        $Linha = explode(';', $dados[$Row]);

        $nomeAta = $Linha[0];
        $itemAutor = $Linha[1];
        $itemObservacao = $Linha[2].'&nbsp;';
        $FaseCodigo = $Linha[3];
        $codAta = $Linha[4];
        $itemExcluido = $Linha[5];
        $itemDataAlteracao = $Linha[6];

        $ArqUpload = 'licitacoes/ATASFASE'.$GrupoCodigo.'_'.$Processo.'_'.$ProcessoAno.'_'.$ComissaoCodigo.'_'.$OrgaoLicitanteCodigo.'_'.$FaseCodigo.'_'.$codAta;
        $Arquivo = $GLOBALS['CAMINHO_UPLOADS'].$ArqUpload;
        addArquivoAcesso($ArqUpload);

        if (file_exists($Arquivo)) {
            $tamanho = filesize($Arquivo) / 1024;
        }

        if ($itemExcluido == 'S') {
            $itemNome = "<s><font color=\"#000000\"> $nomeAta </font></s><b>(excluído)</b>";
        } else {
            if (file_exists($Arquivo)) {
                $Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$codAta";
                if (!in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                $itemNome = "<a href='$Url'><font color='#000000'> $nomeAta </font></a>";
            } else {
                $itemNome = "<font color=\"#000000\"> $nomeAta </font><b>(arquivo não armazenado)</b>";
            }
            // Autor e observação de documentos de antes da melhoria não devem ser mostrados
            if ($itemDataAlteracao < '2011-03-23') {
                $itemAutor = '---';
                $itemObservacao = '---';
            }
            if (file_exists($Arquivo) and $itemExcluido != 'S') {
                $tpl->block('BLOCO_LINK_DOWNLOAD');
            } else {
                $tpl->block('BLOCO_SEM_LINK');
            }
            $tpl->DOCUMENTO = $itemNome;

            if (file_exists($Arquivo)) {
                $tpl->TAMANHO = intval($tamanho).' Kbytes';
            } else {
                echo '&nbsp;';
            }
            $tpl->DOCUMENTO = $itemNome;
            $tpl->RESPONSAVEL = $itemAutor;
            $tpl->OBSERVACAO_JUSTIFICATIVA = $itemObservacao;
            $tpl->block('BLOCO_RESULTADO_DOCUMENTOS');
        }
    } // endfor
}
$tpl->show();
$db->disconnect();
