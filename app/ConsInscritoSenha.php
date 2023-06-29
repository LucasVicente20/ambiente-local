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
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version GIT: v1.21.0-18-gc622221
 */

/**
 * HISTORICO DE ALTERAÇÔES NO PROGRAMA
 * ------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     03/07/2015
 * Objetivo: CR81053 - Fornecedores - Inscrição - Consulta
 * Versão:   v1.22.0-6-g8a53c92
 * ------------------------------------------------------------------------
 */
if (! require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

if (! require_once dirname(__FILE__)."/../vendor/autoload.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

session_start();

/**
 */
function proccessPrincipal()
{
    $tpl = new TemplateAppPadrao("templates/ConsInscritoSenha.html", "ConsInscritoSenha");
    $tpl->VALOR_CPF_SELECTED = "checked";
    $tpl->URI_CAPTCHA = getUriCaptcha();
    $tpl->show();
}

/**
 */
function processConfirmar()
{
    $tpl = new TemplateAppPadrao("templates/ConsInscritoSenha.html", "ConsInscritoSenha");

    $ErroPrograma = __FILE__;

    $CPF_CNPJ = trim($_POST['CPF_CNPJ']);
    $certidaoEstrang = trim($_POST['CERTIDAO_ESTRANG']);
    $Senha = filter_var($_POST['Senha'], FILTER_SANITIZE_STRING);
    $senhaDigitada = str_replace($_POST['Hash'], "", base64_decode($Senha));
    $Codigo = $_POST['Codigo'];
    $TipoCnpjCpf = $_POST['TipoCnpjCpf'];

    $Mens = 0;
    $Mensagem = "Informe: ";
    $Qtd = strlen(removeSimbolos($CPF_CNPJ));
    if ($TipoCnpjCpf == "CPF") {
        if ($Qtd != 11 and $Qtd > 0) {
            $Mens = 1;
            $Mensagem .= "CPF com 11 números";
        } elseif ($CPF_CNPJ == "") {
            $Mens = 1;
            $Mensagem .= "CPF Válido";
        } else {
            $cpfcnpj = valida_CPF(removeSimbolos($CPF_CNPJ));
            if ($cpfcnpj === false) {
                $Mens = 1;
                $Mensagem .= "CPF Válido";
            }
        }
    } elseif ($TipoCnpjCpf == "CNPJ") {
        if (($Qtd != 14) and ($Qtd != 0)) {
            $Mens = 1;
            $Mensagem .= "CNPJ com 14 números";
        } elseif ($CPF_CNPJ == "") {
            $Mens = 1;
            $Mensagem .= "CNPJ Válido";
        } else {
            $cpfcnpj = valida_CNPJ(removeSimbolos($CPF_CNPJ));
            if ($cpfcnpj === false) {
                $Mens = 1;
                $Mensagem .= "CNPJ Válido";
            }
        }
    }

    if ($cpfcnpj === true) {
        // Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
        $Senha = trim($Senha);
        if ($Senha == "") {
            $Mens = 1;
            $Mensagem .= "Senha";
        }
        if ($Codigo == "") {
            $Mens = 1;
            $Mensagem .= " Código";
        } else {
            if (strtoupper2($Codigo) != $_SESSION['_Combinacao_']) {
                $Codigo = "";
                $Mens = 1;
                $Mensagem .= "Código Válido";
            }
        }
    } else {
        $Codigo = "";
    }

    if ($Mens == 0) {
        $DataAtual = date("Y-m-d H:i:s");
        // Verifica se o Fornecedor é Cadastrado #
        $bancoDados = Conexao();
        $sqlfor = "SELECT NFORCRSENH, AFORCRSEQU, DFORCREXPS, AFORCRNTEN FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
        if ($TipoCnpjCpf == "CPF") {
            $sqlfor .= "AFORCRCCPF = '".removeSimbolos($CPF_CNPJ)."'";
        } elseif ($TipoCnpjCpf == "CNPJ") {
            $sqlfor .= "AFORCRCCGC = '".removeSimbolos($CPF_CNPJ)."'";
        }else{
            $sqlfor .= "aforcridfe  = '".removeSimbolos($certidaoEstrang)."'"; 
        }
        $resfor = $bancoDados->query($sqlfor);
        if (PEAR::isError($resfor)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
        } else {
            $rowfor = $resfor->numRows();
            if ($rowfor != 0) {
                $Mens = 1;
                $Tipo = 1;
                $Mensagem = "Fornecedor Já Cadastrado, selecione no menu a opção Fornecedores/Acompanhamento para a verificação dos dados cadastrais";
            } else {
                // Verifica se o Fornecedor Já foi Inscrito #
                $bancoDados = Conexao();
                $sqlpre = "SELECT NPREFOSENH, APREFOSEQU, DPREFOEXPS, APREFONTEN FROM SFPC.TBPREFORNECEDOR WHERE ";
                if ($TipoCnpjCpf == "CPF") {
                    $sqlpre .= "APREFOCCPF = '".removeSimbolos($CPF_CNPJ)."'";
                } elseif ($TipoCnpjCpf == "CNPJ") {
                    $sqlpre .= "APREFOCCGC = '".removeSimbolos($CPF_CNPJ)."'";
                }
                $respre = $bancoDados->query($sqlpre);
                if (PEAR::isError($respre)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
                } else {
                    $rowpre = $respre->numRows();
                    if ($rowpre != 0) {
                        $prefornecedor = $respre->fetchRow();
                        $SenhaPre = $prefornecedor[0];
                        $Sequencial = $prefornecedor[1];
                        $DataExpSenha = $prefornecedor[2];
                        $NumTentativas = $prefornecedor[3];
                        if ($SenhaPre != crypt($senhaDigitada, "P")) {
                            if ($NumTentativas < 5) {
                                $NumTentativas ++;
                                if ($Mens == 1) {
                                    $Mensagem .= ", ";
                                }
                                $Codigo = "";
                                $Mens = 1;
                                $Mensagem .= "Senha Válida";

                                // Atualiza no Banco o número de tentativas #
                                $bancoDados->query("BEGIN TRANSACTION");
                                $sql = "UPDATE SFPC.TBPREFORNECEDOR ";
                                $sql .= "   SET APREFONTEN = $NumTentativas, TPREFOULAT = '$DataAtual' ";
                                $sql .= " WHERE APREFOSEQU = $Sequencial";
                                $result = $bancoDados->query($sql);
                                if (PEAR::isError($result)) {
                                    $bancoDados->query("ROLLBACK");
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                }
                                $bancoDados->query("COMMIT");
                                $bancoDados->query("END TRANSACTION");
                            } else {
                                $Mens = 1;
                                $Codigo = "";
                                $Mensagem = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
                                $Mensagem .= "Para gerar uma nova senha clique no link \"esqueci a minha senha\"";
                            }
                        } else {
                            if ($NumTentativas < 5) {
                                // Atualiza no Banco o número de tentativas #
                                $bancoDados->query("BEGIN TRANSACTION");
                                $sql = "UPDATE SFPC.TBPREFORNECEDOR ";
                                $sql .= "   SET APREFONTEN = 0, TPREFOULAT = '$DataAtual' ";
                                $sql .= " WHERE APREFOSEQU = $Sequencial";
                                $result = $bancoDados->query($sql);
                                if (PEAR::isError($result)) {
                                    $bancoDados->query("ROLLBACK");
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                }
                                $bancoDados->query("COMMIT");
                                $bancoDados->query("END TRANSACTION");

                                if ($DataExpSenha < date("Y-m-d") and $DataExpSenha != "") {
                                    // Redireciona para a página de Alteração de Senha #
                                    $Programa = urlencode("ConsInscritoSenha.php");
                                    $Url = "RotAlteracaoSenhaFornecedor.php?Sequencial=$Sequencial&TipoForn=I&Programa=$Programa";
                                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                                        $_SESSION['GetUrl'][] = $Url;
                                    }
                                    header("location: ".$Url);
                                    exit();
                                } else {
                                    // Redireciona para a página de Consulta #
                                    $Url = "ConsInscrito.php?Sequencial=$Sequencial";
                                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                                        $_SESSION['GetUrl'][] = $Url;
                                    }
                                    header("location: ".$Url);
                                    exit();
                                }
                            } else {
                                $Mens = 1;
                                $Tipo = 2;
                                $Codigo = "";
                                $Mensagem = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
                                $Mensagem .= "Para gerar uma nova senha clique no link \"esqueci a minha senha\"";
                            }
                        }
                    } else {
                        $Mens = 1;
                        $Mensagem = "Fornecedor Inscrito não Encontrado em Nossos Cadastros";
                    }
                }
                $bancoDados->disconnect();
            }
        }
    }

    if ($Mens == 1) {
        $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
    }

    $MarcaCPF = "";
    $MarcaCNPJ = "";

    if ($TipoCnpjCpf == "CPF" or $TipoCnpjCpf == "") {
        $MarcaCPF = "checked";
    } else {
        $MarcaCNPJ = "checked";
    }

    $tpl->VALOR_CPF_SELECTED = $MarcaCPF;
    $tpl->VALOR_CNPJ_SELECTED = $MarcaCNPJ;
    $tpl->VALOR_CPF_CNPJ = $CPF_CNPJ;
    $tpl->VALOR_SENHA = '';
    $tpl->VALOR_CODIGO = '';

    $tpl->URI_CAPTCHA = getUriCaptcha();

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
            processConfirmar();
            break;
        case 'Limpar':
            proccessPrincipal();
            break;
        case 'Principal':
        default:
            proccessPrincipal();
    }
}

frontController();
