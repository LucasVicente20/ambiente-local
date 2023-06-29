<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.13.0-41-gf34a9d8
 *
 * * -----------------------------------------------------------------------------
 * HISTORICO DE ALTERACOES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI <contato@pitang.com>
 * Data:     17/09/2015
 * Objetivo: CR 100458 - Mensagem de erro recorrente
 * Versão:   20150916_1550-1-gf471375
 * -----------------------------------------------------------------------------
 */
if (! @require_once dirname(__FILE__) . '/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}

if ($_SESSION['ValidaArquivoDownload'] != 'ValidaArquivoDownload') {
    TiraSeguranca();
    header('Location: home.php');
    exit();
}

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = filter_var_array($_POST, FILTER_SANITIZE_STRING);
    $Botao = $_POST['Botao'];
    // $Critica = $_POST['Critica'];
    $Objeto = $_POST['Objeto'];
    $OrgaoLicitanteCodigo = filter_var($_POST['OrgaoLicitanteCodigo'], FILTER_SANITIZE_NUMBER_INT);
    $ComissaoCodigo = filter_var($_POST['ComissaoCodigo'], FILTER_SANITIZE_NUMBER_INT);
    $ModalidadeCodigo = filter_var($_POST['ModalidadeCodigo'], FILTER_SANITIZE_NUMBER_INT);
    $GrupoCodigo = filter_var($_POST['GrupoCodigo'], FILTER_SANITIZE_NUMBER_INT);
    $LicitacaoProcesso = filter_var($_POST['LicitacaoProcesso'], FILTER_SANITIZE_NUMBER_INT);
    $LicitacaoAno = filter_var($_POST['LicitacaoAno'], FILTER_SANITIZE_NUMBER_INT);
    $DocumentoCodigo = filter_var($_POST['DocumentoCodigo'], FILTER_SANITIZE_NUMBER_INT);
    $SolicitanteCodigo = filter_var($_POST['SolicitanteCodigo'], FILTER_SANITIZE_NUMBER_INT);
    
    // Identifica o Programa para Erro de Banco de Dados #
    $ErroPrograma = 'ConsAvisosArquivo.php';
    
    if ($LicitacaoProcesso == false || $LicitacaoAno == false || $ComissaoCodigo == false || $GrupoCodigo == false || $DocumentoCodigo == false) {
        TiraSeguranca();
        header('Location: home.php');
        exit();
    }
    // Abre o arquivo para donwload #
    if ($Botao == 'Download') {
        // Procura o nome do arquivo na tabela de documentos #
        $db = Conexao();
        $sql = 'SELECT EDOCLINOME FROM SFPC.TBDOCUMENTOLICITACAO ';
        $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso ";
        $sql .= "   AND ALICPOANOP = $LicitacaoAno AND CCOMLICODI = $ComissaoCodigo ";
        $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CDOCLICODI = $DocumentoCodigo";
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: 67\nSql: $sql");
        } else {
            while ($Linha = $result->fetchRow()) {
                $NomeArquivo = $Linha[0];
            }
        }
        
        $db->disconnect();
        
        // Copia arquivo para dentro do diretório tmp #
        $ArquivoNomeServidor = 'licitacoes/DOC' . $GrupoCodigo . '_' . $LicitacaoProcesso . '_' . $LicitacaoAno . '_' . $ComissaoCodigo . '_' . $OrgaoLicitanteCodigo . '_' . $DocumentoCodigo;
        $Arq = $GLOBALS['CAMINHO_UPLOADS'] . $ArquivoNomeServidor;
        
        if (file_exists($Arq)) {
            /*
             * Warning
             * This function has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0.
             */
            session_unregister('ValidaArquivoDownload');
            addArquivoAcesso($ArquivoNomeServidor);
            $ArquivoNomeServidor = str_replace('/', '%2F', $ArquivoNomeServidor);
            $url = '../carregarArquivo.php?arq=' . $ArquivoNomeServidor . '&arq_nome=' . urlencode($NomeArquivo);
            header("Location: $url ");
            exit();
        }
    }
    if ($OrgaoLicitanteCodigo == false || $SolicitanteCodigo == false) {
        TiraSeguranca();
        header('Location: home.php');
        exit();
    }
    $db = Conexao();
    $sql = 'SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF, ELISOLMAIL, ';
    $sql .= '       ELISOLENDE, ALISOLFONE, ALISOLNFAX, NLISOLCONT, ';
    $sql .= '       FLISOLPART ';
    $sql .= '  FROM SFPC.TBLISTASOLICITAN ';
    $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
    $sql .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
    $sql .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND CLISOLCODI = $SolicitanteCodigo";
    $result = $db->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: 110\nSql: $sql");
    }
    
    while ($Linha = $result->fetchRow()) {
        $RazaoSocial = $Linha[0];
        $CNPJ = $Linha[1];
        $CPF = $Linha[2];
        $Email = $Linha[3];
        $Endereco = $Linha[4];
        $Telefone = $Linha[5];
        $Fax = $Linha[6];
        $Contato = $Linha[7];
        $Participacao = $Linha[8];
    }
    $db->disconnect();
    // GUI
    $tpl = new TemplateAppPadrao('templates/ConsAvisosArquivo.html', 'ConsAvisosArquivo');
    // Dados informados
    $tpl->RAZAO_SOCIAL = $RazaoSocial;
    if ($CNPJ == '') {
        $tpl->INSCRICAO_INFORMADA = 'CPF';
        $tpl->NUMERO_INSCRICAO = FormataCPF($CPF);
    } else {
        $tpl->INSCRICAO_INFORMADA = 'CNPJ';
        $tpl->NUMERO_INSCRICAO = FormataCNPJ($CNPJ);
    }
    $tpl->ENDERECO = $Endereco;
    $tpl->EMAIL = $Email;
    $tpl->TELEFONE = $Telefone;
    $tpl->FAX = $Fax;
    $tpl->NOME_CONTATO = $Contato;
    $tpl->PARTICIPAR_LICITACAO = ($Participacao == 'S') ? 'SIM' : 'NÃO';
    
    // Dados mantidos para o voltar
    $tpl->OBJETO = $Objeto;
    $tpl->ORGAO_LICITANTE_CODIGO = $OrgaoLicitanteCodigo;
    $tpl->COMISSAO_CODIGO = $ComissaoCodigo;
    $tpl->MODALIDADE_CODIGO = $ModalidadeCodigo;
    $tpl->GRUPO_CODIGO = $GrupoCodigo;
    $tpl->LICITACAO_PROCESSO = $LicitacaoProcesso;
    $tpl->LICITACAO_ANO = $LicitacaoAno;
    $tpl->DOCUMENTO_CODIGO = $DocumentoCodigo;
    $tpl->SOLICITANTE_CODIGO = $SolicitanteCodigo;
    
    $tpl->show();
}
