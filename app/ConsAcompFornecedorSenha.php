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
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data:     03/07/2015
 * Objetivo: CR81055 - Fornecedores - Acompanhamento - senha
 * Versão:   v1.22.0-7-gf5c082c
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81057
 * Versão:   v1.22.0-8-g375a774
 */
if (! require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao("templates/ConsAcompFornecedorSenha.html", "ConsAcompFornecedorSenha");

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/ConsAcompFornecedorSenha.php');
AddMenuAcesso('/fornecedores/RotAlteracaoSenhaFornecedor.php');
AddMenuAcesso('/fornecedores/ConsAcompFornecedor.php');
AddMenuAcesso('/fornecedores/CadRenovacaoCadastroIncluir.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $CPF_CNPJ = trim($_POST['CPF_CNPJ']);
    $certidaoEstrang = trim($_POST['CERTIDAO_ESTRANG']);
    $Senha = filter_var($_POST['Senha'], FILTER_SANITIZE_STRING);
    $senhaDigitada = str_replace($_POST['Hash'], "", base64_decode($Senha));
    $Codigo = $_POST['Codigo'];
    $TipoCnpjCpf = $_POST['TipoCnpjCpf'];
} else {
    $Programa = $_GET['Programa'];
    $Mens = $_GET['Mens'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Tipo = $_GET['Tipo'];
}

// $Desvio = $_REQUEST['Desvio'];

if ($TipoCnpjCpf == 'CNPJ') {
    $tpl->CHECKED_CNPJ = 'CHECKED';
} else {
    $tpl->CHECKED_CPF = 'CHECKED';
}
// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Limpar") {
    header("Location: ConsAcompFornecedorSenha.php");
    exit();
} elseif ($Botao == "Confirmar") {
    $Mens = 0;
    $Mensagem = "Informe: ";
    $Qtd = strlen(removeSimbolos($CPF_CNPJ));
    if ($TipoCnpjCpf == "CPF") {
        if ((($Qtd > 11) and ($Qtd > 0)) or (($Qtd < 11) and ($Qtd > 0))) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
        } elseif ($CPF_CNPJ == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
        } else {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $cpfcnpj = valida_CPF(removeSimbolos($CPF_CNPJ));
            if ($cpfcnpj === false) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
            }
        }
    } elseif ($TipoCnpjCpf == "CNPJ") {
        if (($Qtd != 14) and ($Qtd != 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
        } elseif ($CPF_CNPJ == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
        } else {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $cpfcnpj = valida_CNPJ(removeSimbolos($CPF_CNPJ));
            if ($cpfcnpj === false) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
            }
        }
    }

    if ($cpfcnpj === true) {
        $tpl->CPF_CNPJ = $CPF_CNPJ;
        // Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #

        // $Senha = trim($Senha);
        if ($Senha == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.Senha.focus();\" class=\"titulo2\">Senha</a>";
        }
        if ($Codigo == "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.Codigo.focus();\" class=\"titulo2\">Código</a>";
        } else {
            if (strtoupper2($Codigo) != $_SESSION['_Combinacao_']) {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Codigo = "";
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.Codigo.focus();\" class=\"titulo2\">C&oacute;digo V&aacute;lido</a>";
            }
        }
    } else {
        $Codigo = "";
    }

    if ($Mens == 0) {
        $DataAtual = date("Y-m-d H:i:s");
        $db = Conexao();
        $sqlfor = "SELECT NFORCRSENH, AFORCRSEQU, DFORCREXPS, AFORCRNTEN FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";

        if ($TipoCnpjCpf == "CPF") {
            $sqlfor .= "AFORCRCCPF = '".removeSimbolos($CPF_CNPJ)."'";
        } elseif ($TipoCnpjCpf == "CNPJ") {
            $sqlfor .= "AFORCRCCGC = '".removeSimbolos($CPF_CNPJ)."'";
        }else{
            $sqlfor .= "aforcridfe  = '".removeSimbolos($certidaoEstrang)."'";
        }
        $resfor = $db->query($sqlfor);
        if (PEAR::isError($resfor)) {
            $Mensagem = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
        } else {
            $rowfor = $resfor->numRows();
            if ($rowfor != 0) {
                $fornecedor = $resfor->fetchRow();
                $SenhaFor = $fornecedor[0];
                $Sequencial = $fornecedor[1];
                $DataExpSenha = $fornecedor[2];
                $NumTentativas = $fornecedor[3];

                if ($SenhaFor != crypt($senhaDigitada, "P")) {
                    if ($NumTentativas < 5) {
                        $NumTentativas ++;
                        if ($Mens == 1) {
                            $Mensagem .= ", ";
                        }
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem .= "<a href=\"javascript:document.ConsAcompFornecedorSenha.Senha.focus();\" class=\"titulo2\">Senha Válida</a>";

                        $Codigo = "";

                        // Atualiza no Banco o número de tentativas #
                        $db->query("BEGIN TRANSACTION");
                        $sql = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
                        $sql .= "   SET AFORCRNTEN = $NumTentativas, TFORCRULAT = '$DataAtual'";
                        $sql .= " WHERE AFORCRSEQU = $Sequencial";
                        $result = $db->query($sql);
                        if (PEAR::isError($result)) {
                            $db->query("ROLLBACK");
                            $Mensagem = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        }
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                    } else {
                        $Mens = 1;
                        $Tipo = 2;
                        $Codigo = "";
                        $Mensagem = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
                        $Mensagem .= 'Para gerar uma nova senha clique no link “esqueci a minha senha” ';
                    }
                } else {
                    if ($NumTentativas < 5) {
                        // Atualiza no Banco o número de tentativas #
                        $db->query("BEGIN TRANSACTION");
                        $sql = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
                        $sql .= "   SET AFORCRNTEN = 0, TFORCRULAT = '$DataAtual'";
                        $sql .= " WHERE AFORCRSEQU = $Sequencial";
                        $result = $db->query($sql);
                        if (PEAR::isError($result)) {
                            $db->query("ROLLBACK");
                            $Mensagem = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        }
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                        if ($DataExpSenha < date("Y-m-d") and $DataExpSenha != "") {
                            // Redireciona para a página de Alteração de Senha #
                            $Programa = urlencode("ConsAcompFornecedorSenha.php");
                            $Url = "RotAlteracaoSenhaFornecedor.php?Sequencial=$Sequencial&TipoForn=F&Programa=$Programa";
                        } else {
                            // Redireciona para a página de Acompanhamento #
                            $Url = "ConsAcompFornecedor.php?Sequencial=$Sequencial";
                        }
                        if (! in_array($Url, $_SESSION['GetUrl'])) {
                            $_SESSION['GetUrl'][] = $Url;
                        }
                        header("Location: ".$Url);
                        exit();
                    } else {
                        $Mens = 1;
                        $Tipo = 2;
                        $Codigo = "";
                        $Mensagem = "O número máximo de tentativas para o login foi excedido e a senha foi cancelada. ";
                        $Mensagem .= 'Para gerar uma nova senha clique no link “esqueci a minha senha”';
                    }
                }
            } else {
                // Verifica se o Fornecedor Já foi Inscrito #
                $db = Conexao();
                $sqlpre = "SELECT NPREFOSENH, APREFOSEQU, DPREFOEXPS, APREFONTEN FROM SFPC.TBPREFORNECEDOR WHERE ";
                if ($TipoCnpjCpf == "CPF") {
                    $sqlpre .= "APREFOCCPF = '".removeSimbolos($CPF_CNPJ)."'";
                } elseif ($TipoCnpjCpf == "CNPJ") {
                    $sqlpre .= "APREFOCCGC = '".removeSimbolos($CPF_CNPJ)."'";
                }
                $respre = $db->query($sqlpre);
                if (PEAR::isError($respre)) {
                    $Mensagem = ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
                } else {
                    $rowpre = $respre->numRows();
                    if ($rowpre != 0) {
                        $Mens = 1;
                        $Tipo = 1;
                        // $Virgula = 2;
                        $Mensagem = "Fornecedor Inscrito, selecione no menu a opção Fornecedores/Inscrição/Consulta para a verificação dos dados cadastrais";
                    } else {
                        $Mens = 1;
                        $Tipo = 1;
                        $Mensagem = "Fornecedor não Encontrado em Nossos Cadastros";
                    }
                }
            }
        }
        $db->disconnect();
    }
}

if ($Mens != 0 || ! empty($Mensagem)) {
    $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
}
$tpl->URI_CAPTCHA = getUriCaptcha();
$tpl->show();
