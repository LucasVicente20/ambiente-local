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
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile IT
 * Data: 18/06/2015 - CR82766
 * Versão: v1.20.0-21-g0bb0451
 * -------------------------------------------------------------------------
 */
require_once dirname(__FILE__).'/../vendor/autoload.php';
session_start();

$requisicaoMetodoPost = ($_SERVER["REQUEST_METHOD"] == "POST");
$requisicaoComAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

function SalvaSenhaNova($cusupocodi, $cgrempcodi, $senhaNovoHash, $db) {
    $sql = "UPDATE SFPC.TBUSUARIOPORTAL SET  EUSUPOSEN2 = '".$senhaNovoHash."' WHERE CGREMPCODI = ".$cgrempcodi." AND CUSUPOCODI = ".$cusupocodi;
    $db->query($sql);
}

if ($requisicaoMetodoPost && $requisicaoComAjax) {
    $Critica = filter_var($_POST['Critica'], FILTER_SANITIZE_NUMBER_INT);
    $Login = filter_var($_POST['Login'], FILTER_SANITIZE_STRING);
    $Senha = filter_var($_POST['Senha'], FILTER_SANITIZE_STRING);
    $senhaDigitada = str_replace($_POST['Hash'], "", base64_decode($Senha));
    $Confirmacao = strtoupper2(filter_var($_POST['Confirmacao'], FILTER_SANITIZE_STRING));

    $Mens = 0;

    if ($Critica == 1) {
        $db = Conexao();
        if (! $db) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "Banco de Dados n&atilde;o Disponível no Momento.<br>Tente Novamente Mais Tarde.<br>Obrigado!";
        } else {
            $sql = "SELECT CGREMPCODI, CUSUPOCODI, EUSUPOSEN2 FROM SFPC.TBUSUARIOPORTAL WHERE EUSUPOLOGI = '$Login'";
            $result = $db->query($sql);

            if (PEAR::isError($result)) {
                $CodErroEmail = $result->getCode();
                $DescErroEmail = $result->getMessage();
                ExibeErroBD("index.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }

            $Rows = $result->numRows();
            while ($cols = $result->fetchRow()) {
                $Linha[$Rows] = "$cols[0]_$cols[1]_$cols[2]";
            }

            $Linha = explode("_", $Linha[$Rows]);
            $eusuarsenh = $Linha[2];
			if (empty($eusuarsenh)) {
                $senhaNovoHash = hash('sha512',$senhaDigitada);
                SalvaSenhaNova($Linha[1], $Linha[0], $senhaNovoHash, $db);
                $eusuarsenh = $senhaNovoHash;
            }
            $SenhaCript = @crypt($Senha, "P");
            // Critica aos Campos de Login e Código #
            if ((strtoupper2($Confirmacao) != $_SESSION['_Combinacao_']) || ($Confirmacao == "")) {
                TiraSeguranca();

                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }

                $Mens = 1;
                $Tipo = 2;
                $Mod = 1;
                $Mensagem .= "<a href=\"javascript:document.FormLogin.Confirmacao.focus();\" class=\"titulo2\">Código Inválido</a>";
            } else {
                if ($Rows == 0 || $Login == "") {
                    TiraSeguranca();
                    $Mens = 1;
                    $Tipo = 2;
                    $Mod = 1;
                    $Mensagem .= "<a href=\"javascript:document.FormLogin.Login.focus();\" class=\"titulo2\">Login ou Senha Inv&aacute;lidos</a>";
                } else {
                    $Mensagem = "Informe: ";

                    if (@crypt($senhaDigitada, "P") != $eusuarsenh || $senhaDigitada == "") {
						if (hash('sha512',$senhaDigitada) != $eusuarsenh || $senhaDigitada == "") {
							TiraSeguranca();

							if ($Mens == 1) {
								$Mensagem .= ", ";
							}

							$Mens = 1;
							$Tipo = 2;
							$Mod = 1;
							$Mensagem .= "<a href=\"javascript:document.FormLogin.Senha.focus();\" class=\"titulo2\">Senha V&aacute;lida</a>";
						}
                    }
                }
            }

            // Login e Campos Válidos #
            if ($Mens == 0) {
                $Tipo = 1;
                $Mensagem = "sucesso";

                $_SESSION['_cgrempcodi_'] = $Linha[0];
                $_SESSION['_cusupocodi_'] = $Linha[1];
                $_SESSION['_eusupologi_'] = $Login;
                $_SESSION['_eacepocami_'] = array();

                // Pesquisa Perfil do Usuário #
                $sql = "SELECT A.CPERFICODI, B.FPERFICORP FROM SFPC.TBUSUARIOPERFIL A, SFPC.TBPERFIL B ";
                $sql .= " WHERE A.CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
                $sql .= " AND A.CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
                $sql .= " AND A.CPERFICODI = B.CPERFICODI";
                $sql .= " AND B.FPERFISITU <> 'I'";
                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    $CodErroEmail = $result->getCode();
                    $DescErroEmail = $result->getMessage();
                    ExibeErroBD("index.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }

                $Rows = $result->numRows();
                if ($Rows == 1) {
                    $Linha = $result->fetchRow();
                    $_SESSION['_cperficodi_'] = $Linha[0];
                    $_SESSION['_fperficorp_'] = $Linha[1];
                } else {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mod = 1;
                    $Mensagem = "Usuário sem perfil definido";
                    TiraSeguranca();
                }
            }

            $db->disconnect();
        }
    } else {
        if (! isset($_SESSION['_cgrempcodi_'])) {
            TiraSeguranca();
        }
    }

    $_SESSION['tipoErroLogin'] = $Tipo;
    $_SESSION['textoMensagemLogin'] = $Mensagem;
    $_SESSION['emailLogin'] = $Login;
    $_SESSION['senhaLogin'] = $senha;

    echo json_encode(array(
        'tipo' => $Tipo,
        'mensagem' => $Mensagem,
    ));
}
