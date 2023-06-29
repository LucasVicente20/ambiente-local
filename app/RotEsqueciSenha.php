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
 * @version Git: $Id:$
 */

if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}
$tpl = new TemplateAppPadrao("templates/RotEsqueciSenha.html", "Geracao");

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica       = $_POST['Critica'];
    $CPF           = $_POST['CPF'];
    $Botao         = $_POST['Botao'];
    
    $tpl->CPF = $CPF;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RotEsqueciSenha.php";

# Critica dos Campos #
if ($Critica == 1) {
	
	//Desvio do FluxoCriaSenha
	if ($Botao == "retornar") {
		header("Location: home.php");
		exit;
	}

    //	$Mens     = 0;
    //	$Mensagem = "Informe: ";
    if ($CPF == "" || !valida_CPFNovo($CPF)) {
        //    $Mens = 1; $Tipo = 2; $Troca = 1;
        //  $Mensagem .= "<a href=\"javascript: document.Geracao.CPF.focus();\" class=\"titulo2\">CPF</a>";
        adicionarMensagem("<a href=\"javascript:document.Geracao.CPF.focus();\" class=\"titulo2\">CPF válido</a>", TIPO_MENSAGEM_ERRO);

        // }
        //  if( !valida_CPF($CPF) ){
        //	$Mens = 1; $Tipo = 2; $Troca = 1;
        //	$Mensagem .= "<a href=\"javascript: document.Geracao.CPF.focus();\" class=\"titulo2\">CPF válido</a>";
    } else {
        //if( !valida_CPF($CPF)){
        //	adicionarMensagem("<a href=\"javascript:document.Geracao.CPF.focus();\" class=\"titulo2\">CPF Válido</a>", TIPO_MENSAGEM_ERRO);
        //	}else{

        # Busca E-mail do Usuário e Valida CPF #
        $db     = Conexao();
        $sql    = "SELECT EUSUPOLOGI, EUSUPOMAIL FROM SFPC.TBUSUARIOPORTAL WHERE AUSUPOCCPF = '$CPF' ";
        $result = $db->query($sql);
        $num_rows = $result->numRows();

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }
        // Se número de linhas vier vazio é porque CPF informado não existe
        if (empty($num_rows)) {
            $Mensagem = "CPF não encontrado";
            $Mens = 1;
            $Tipo = 1;
        } else {
            while ($Linha = $result->fetchRow()) {
                $Login  = $Linha[0];
                $Email  = $Linha[1];
            }

            # Cria na nova senha e criptografa #
            $Senha      = CriaSenha();
            $SenhaCript = hash('sha512',$Senha);

            # Atualiza a senha do Usuário #
            $Data   = date("Y-m-d H:i:s");
            $sql    = "UPDATE SFPC.TBUSUARIOPORTAL SET EUSUPOSEN2 = '$SenhaCript', ";
            $sql   .= "TUSUPOULAT = '$Data' WHERE AUSUPOCCPF = '$CPF' ";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                # Envia a senha pelo e-mail do usuário #
                $envioSmtp = EnviaEmail("$Email", "Senha Temporária p/Acesso ao Portal de Compras", "\t Login: $Login\n\t Senha: $Senha", "from: portalcompras@recife.pe.gov.br");
                $Mens = 1;
                $Tipo = 1;
                if (!$envioSmtp) {
                    $Mensagem = "
                        Envio de email falhou! O servidor de email pode estar apresentando problemas no momento. Tenta mais tarde
                        ou contacte o administrador do sistema";
                } else {
                    $Mensagem ="Senha Gerada com Sucesso. Uma senha temporária foi enviada para o e-mail do usuário";
                }
            }
            $db->disconnect();
        }
    }

    $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
}

$tpl->show();
