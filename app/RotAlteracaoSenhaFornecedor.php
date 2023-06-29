<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo_Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.22.0-1-g6082e41
 */

/**
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81057
 * Versão:   v1.22.0-13-g3500bf1
 * -----------------------------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     21/09/2021
 * Objetivo: Tarefa Redmine #248922
 * -----------------------------------------------------------------------------------------------------------
 */
if (! require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

/**
 * [proccessPrincipal description]
 * @return [type] [description]
 */
function proccessPrincipal()
{
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $Critica = filter_var($_POST['Critica'], FILTER_SANITIZE_NUMBER_INT);
        $SenhaAtual = filter_var($_POST['SenhaAtual'], FILTER_SANITIZE_STRING);
        $senhaAtualDigitada = str_replace($_POST['atual'], "", base64_decode($SenhaAtual));

        $NovaSenha = filter_var($_POST['NovaSenha'], FILTER_SANITIZE_STRING);
        $novaSenhaDigitada = str_replace($_POST['nova'], "", base64_decode($NovaSenha));
        $ConfirmaSenha = filter_var($_POST['ConfirmaSenha'], FILTER_SANITIZE_STRING);
        $confirmaDigitada = str_replace($_POST['confirma'], "", base64_decode($ConfirmaSenha));

        $Sequencial = filter_var($_POST['Sequencial'], FILTER_SANITIZE_NUMBER_INT);
        $TipoForn = filter_var($_POST['TipoForn'], FILTER_SANITIZE_STRING);

        $CPF_CNPJ = filter_var($_POST['CPF_CNPJ'], FILTER_SANITIZE_STRING);
        $TipoCnpjCpf = filter_var($_POST['TipoCnpjCpf'], FILTER_SANITIZE_STRING);
    } else {
        $Sequencial = $_GET['Sequencial'];
        $TipoForn = $_GET['TipoForn'];
        $TipoCnpjCpf = $_GET['TipoCnpjCpf'];
    }

    $Programa = filter_var($_REQUEST['Programa'], FILTER_SANITIZE_STRING);
    $NomeBreadCrumb = '';
    // Identifica o Programa para Erro de Banco de Dados #
    $ErroPrograma = __FILE__;

    // Critica dos Campos #
    if ($Critica == 1) {
        $Mens = 0;
        $Mensagem = "Informe: ";
        if ($senhaAtualDigitada == "") {
            $Mens = 1;
            $Mensagem .= "Senha atual";
        }
        if ($novaSenhaDigitada == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Mensagem .= "Nova senha";
        }
        if ($confirmaDigitada == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Mensagem .= "Confirmação da senha";
        } else {
            if ($novaSenhaDigitada != $confirmaDigitada) {
                $Mens = 1;
                $Mensagem = "Confirmação de senha inválida";
                $senhaAtualDigitada = "";
                $novaSenhaDigitada = "";
                $confirmaDigitada = "";
            }
        }

        if ($Mens == 0) {
            $db = Conexao();
            if ($TipoForn == "I") {
                $sql = "SELECT NPREFOSENH FROM SFPC.TBPREFORNECEDOR WHERE APREFOSEQU = %d";
            } else{
                $sql = "SELECT NFORCRSENH FROM SFPC.TBFORNECEDORCREDENCIADO WHERE AFORCRSEQU = %d";
            }
            $sql = sprintf($sql, $Sequencial);
            $result = executarTransacao($db, $sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $result->fetchRow();
                $SenAtu = $Linha[0];
                $db->disconnect();

                if ($SenAtu != crypt($senhaAtualDigitada, "P")) {
                    $Mens = 1;
                    $Mensagem .= "Senha Atual Inválida";
                    $senhaAtualDigitada = "";
                    $novaSenhaDigitada = "";
                    $confirmaDigitada = "";
                }
            }
        }
        if ($Mens == 0) {
            $SenhaCript = crypt($novaSenhaDigitada, "P");
            $Data = date("Y-m-d H:i:s");
            // $DataExp = SomaData(365,date("d/m/Y"));
            // $DataExpInv = DataInvertida($DataExp);
            $db = Conexao();
            $db->query("BEGIN TRANSACTION");
            if ($TipoForn == "I") {
                $sql = "UPDATE SFPC.TBPREFORNECEDOR ";
                $sql .= "   SET NPREFOSENH = '$SenhaCript', APREFONTEN = 0, ";
                $sql .= "       DPREFOEXPS = NULL, TPREFOULAT = '$Data' ";
                $sql .= " WHERE APREFOSEQU = $Sequencial";
            } else {
                $sql = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
                $sql .= "   SET NFORCRSENH = '$SenhaCript', AFORCRNTEN = 0, ";
                $sql .= "       DFORCREXPS = NULL, TFORCRULAT = '$Data' ";
                $sql .= " WHERE AFORCRSEQU = $Sequencial";
            }
              $result = executarTransacao($db, $sql);
            if (PEAR::isError($result)) {
                $dao->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $db->query("COMMIT");
                $db->query("END TRANSACTION");
                $db->disconnect();
                if($Programa == "AtualizaDocumentoSenha.php"){
                $Mensagem = urlencode("Senha Alterada com Sucesso");
                $Url = "AtualizaDocumentoSenha.php?Sequencial=$Sequencial&CPF_CNPJ=$CPF_CNPJ&Mensagem=$Mensagem&Mens=1&Tipo=1&TipoCnpjCpf=$TipoCnpjCpf";
                header("location: ".$Url);
                exit();
                }
                $Mensagem = urlencode("Senha Alterada com Sucesso");
                if ($Programa == "ConsAcompFornecedorSenha.php") {
                    $NomePrograma = "ConsAcompFornecedor.php";
                } elseif ($Programa == "ConsInscritoSenha.php") {
                    $NomePrograma = "ConsInscrito.php";
                } elseif ($Programa == "EmissaoCHFSenha.php") {
                    $NomePrograma = "EmissaoCHF.php";
                }
                $Url = "AtualizaDocumentoSenha.php?Sequencial=$Sequencial&CPF_CNPJ=$CPF_CNPJ&Mensagem=$Mensagem&Mens=1&Tipo=1&TipoCnpjCpf=$TipoCnpjCpf";
                if (! in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                header("location: ".$Url);
                exit();
            }
        }
        $Critica = 0;
    }

    if ($Critica == 0) {
        $db = Conexao();
        // Busca os Dados da Tabela de Inscritos ou de Fornecedor #
        if ($TipoForn == "I") {
            $sql = "SELECT APREFOCCGC, APREFOCCPF, NPREFORAZS, NPREFOMAIL ";
            $sql .= "  FROM SFPC.TBPREFORNECEDOR ";
            $sql .= " WHERE APREFOSEQU = $Sequencial";
        } else {
            $sql = " SELECT AFORCRCCGC, AFORCRCCPF, NFORCRRAZS, NFORCRMAIL ";
            $sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
            $sql .= "  WHERE AFORCRSEQU = $Sequencial";
            
        }   
        
            $result = executarTransacao($db, $sql);
                 $linha = $result->fetchRow();
                 if ($linha[0] != 0) {
                    $CPF_CNPJ = $linha[0];
                    $CNPJCPFForm = FormataCNPJ($linha[0]);
                } else {
                    $CPF_CNPJ = $linha[1];
                    $CNPJCPFForm = FormataCPF($linha[1]);
                }
                $Razao = $linha[2];
                // $Email = $linha[3];
            
       // $dao->disconnect();
    }

    $tpl = new TemplateAppPadrao("templates/RotAlteracaoSenhaFornecedor.html", "RotAlteracaoSenhaFornecedor");

    if ($Mens == 1) {
        $tpl->exibirMensagemFeedback($Mensagem, 2);
    }

    $nomeCrumpProg = 'Inscrição';

    if ($Programa == "ConsAcompFornecedorSenha.php") {
        $NomeBreadCrumb = 'Acompanhamento';
        $nomeCrumpProg = null;
    } elseif ($Programa == "ConsInscritoSenha.php") {
        $NomeBreadCrumb = 'Consulta';
    } elseif ($Programa == "EmissaoCHFSenha.php") {
        $nomeCrumpProg = 'CHF';
        $NomeBreadCrumb = 'Emissão CHF';
        $tpl->block('BLOCO_PROGRAMA');
    }

    if (! empty($nomeCrumpProg)) {
        $tpl->VALORCRUMPPROG = $nomeCrumpProg;
    }
    $tpl->VALORCRUMP = $NomeBreadCrumb;

    if ($Programa == "ConsAcompFornecedorSenha.php" || $Programa == "EmissaoCHFSenha.php") {
        $tituloPagina = 'Alterar Senha do Fornecedor';
    } elseif ($Programa == "ConsInscritoSenha.php") {
        $tituloPagina = 'Alterar Senha do Fornecedor Inscrito';
    }
    $tpl->TITULOPAGINA = $tituloPagina;
    $tpl->TITULOTELA = $tituloPagina;
    $tpl->VALOR_RAZAO_SOCIAL = $Razao;
    $tpl->VALOR_FORM_CNPJ_CPF = $CNPJCPFForm;
    $tpl->VALOR_CNPJ_CPF = $CPF_CNPJ;
    $tpl->VALOR_SENHA_ATUAL = $senhaAtualDigitada;
    $tpl->VALOR_NOVA_SENHA = $novaSenhaDigitada;
    $tpl->VALOR_CONFIRMAR_SENHA = $confirmaDigitada;
    $tpl->VALOR_TIPO_CNPJ_CPF = $TipoCnpjCpf;
    $tpl->VALOR_TIPO_FORN = $TipoForn;
    $tpl->VALOR_SEQUENCIAL = $Sequencial;
    $tpl->VALOR_PROGRAMA = $Programa;

    $tpl->show();
}

/**
 * [frontController description]
 *
 * @return [type] [description]
 */
function frontController()
{
    $botao = isset($_REQUEST['BotaoAcao']) ? $_REQUEST['BotaoAcao'] : 'Principal';
    switch ($botao) {
        case 'Confirmar':
        case 'Limpar':
        case 'Principal':
        default:
            proccessPrincipal();
    }
}

frontController();
