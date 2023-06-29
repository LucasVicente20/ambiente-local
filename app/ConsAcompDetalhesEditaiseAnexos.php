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
 * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado:  Pitang Agile IT
 * Data:      21/07/2015
 * Objetivo:  CR76836 - Licitações Concluídas
 * -----------------------------------------------------------------------------
 */
$ErroPrograma = __FILE__;

include '../licitacoes/funcoesLicitacoes.php';
require_once '../compras/funcoesCompras.php';
require_once 'TemplateAppPopup.php';

$tpl = new TemplateAppPopup('templates/ConsAcompDetalhesEditaiseAnexos.html');

$Selecao = $_SESSION['Selecao'];
$GrupoCodigo = $_SESSION['GrupoCodigoDet'];
$Processo = $_SESSION['ProcessoDet'];
$ProcessoAno = $_SESSION['ProcessoAnoDet'];
$ComissaoCodigo = $_SESSION['ComissaoCodigoDet'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigoDet'];
$Lote = $_SESSION['Lote'];
$Ordem = $_SESSION['Ordem'];

$_SESSION['PermitirAuditoria'] = 'N'; //Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

$Processo = filter_input(INPUT_GET, 'processo');
$ProcessoAno = filter_input(INPUT_GET, 'ano');
$ComissaoCodigo = filter_input(INPUT_GET, 'comissao');
$GrupoCodigo = filter_input(INPUT_GET, 'grupo');

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

resetArquivoAcesso();

$Processo = filter_input(INPUT_GET, 'processo');
$ProcessoAno = filter_input(INPUT_GET, 'ano');
$ComissaoCodigo = filter_input(INPUT_GET, 'comissao');
$GrupoCodigo = filter_input(INPUT_GET, 'grupo');

# Pega os documentos da Licitação #
$sql = 'SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE, FDOCLIEXCL, U.EUSUPORESP, tdocliulat ';
$sql .= '  FROM SFPC.TBDOCUMENTOLICITACAO D, SFPC.TBUSUARIOPORTAL U';
$sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
$sql .= "   AND CCOMLICODI = $ComissaoCodigo AND D.CGREMPCODI = $GrupoCodigo AND D.CUSUPOCODI = U.CUSUPOCODI";

# Exibir as planilhas ORCAMENTO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO>
# e RESULTADO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO> APENAS
# para os usuários que possuem os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)
# VER ALTERAÇÃO: 01/09/2010 - CR: 5210
#Em caso de dúvidas na expressão regular consultar o seguinte site:
#http://www.postgresql.org/docs/8.1/interactive/functions-matching.html#FUNCTIONS-POSIX-REGEXP

if ($_SESSION['_cperficodi_'] == null or ($_SESSION['_cperficodi_'] != 7 and $_SESSION['_cperficodi_'] != 18)) {
    $sql .= " AND ( NOT ( (edoclinome ~* '^RESULTADO_') OR (edoclinome ~* '^ORCAMENTO_') )  ) ";
}

$db = Conexao();
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Rows = $result->numRows();

    while ($cols = $result->fetchRow()) {
        ++$cont;
        $dados[$cont - 1] = "$cols[0];$cols[1];$cols[2];$cols[3];$cols[4];$cols[5]";
    }

    # Mostra os Documentos relacionados com a Licitação #
    if ($Rows > 0) {
        for ($Row = 0; $Row < $Rows; ++$Row) {
            $Linha = explode(';', $dados[$Row]);
            $ArqUpload = 'licitacoes/'.'DOC'.$GrupoCodigo.'_'.$Processo.'_'.$ProcessoAno.'_'.$ComissaoCodigo.'_'.$OrgaoLicitanteCodigo.'_'.$Linha[0];
            $Arq = $GLOBALS['CAMINHO_UPLOADS'].$ArqUpload;
            addArquivoAcesso($ArqUpload);
            $itemNome = $Linha[1];
            $itemObservacao = $Linha[2].'&nbsp;';
            $itemExcluido = $Linha[3];
            $itemAutor = $Linha[4];
            $itemDataAlteracao = $Linha[5];

            if (file_exists($Arq)) {
                $tamanho = filesize($Arq) / 1024;
                $Url = "../licitacoes/ConsAcompDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
            }

            if ($itemExcluido == 'S') {
                $itemNome = "<s style='text-decoration:line-through;'>".$itemNome.'</s> <b>(excluído)</b>';
            } elseif (!file_exists($Arq)) {
                $itemNome = ''.$itemNome.' <b>(arquivo não armazenado)</b>';
            } else {
                $itemNome = "<a href='".$Url."' target='_blank' class='textonormal'>".$itemNome.'</a>';
            }

            # Autor e observação de documentos de antes da melhoria não devem ser mostrados
            if ($itemDataAlteracao < '2011-03-23') {
                $itemAutor = '---';
                $itemObservacao = '---';
            }

            if (file_exists($Arq) and $itemExcluido != 'S') {
                $tpl->LINK = $Url;
                $tpl->block('BLOCO_LINK_DOWNLOAD');
            } else {
                $tpl->block('BLOCO_SEM_LINK');
            }

            $tpl->DOCUMENTO = $itemNome;

            if (file_exists($Arq)) {
                $tpl->TAMANHO = sprintf('%01.1f', $tamanho);
            } else {
                $tpl->TAMANHO = '--';
            }

            $tpl->RESPONSAVEL = $itemAutor;
            $tpl->OBSERVACAO_JUSTIFICATIVA = $itemObservacao;

            $tpl->block('BLOCO_RESULTADO_DOCUMENTOS');
        } // endfor
    } else {
        $tpl->block('BLOCO_SEM_RESULTADO');
    }

    $tpl->show();
    $db->disconnect();
}
