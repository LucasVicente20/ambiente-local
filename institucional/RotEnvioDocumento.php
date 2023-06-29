<?php
/**
 * Portal da DGCO
 * Programa: RotEnvioDocumento.php
 * Objetivo: Programa de Envio de Documento do Portal e DGLC
 * Autor: Ariston
 * Data: 05/10/2008
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
 * @package   Institucional
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 *
 * HISTORICO DE ALTERAÇÔES
 * -----------------------------------------------------------------------
 *  Alterado: Ariston Cordeiro
 *  Data: 22/01/2008 - Remover caractere "º" dos nomes
 *  -----------------------------------------------------------------------
 *  Alterado: Ariston Cordeiro
 *  Data: 23/01/2008 - Remoção de novos caracteres no tratamento do nome do arquivo
 *  -----------------------------------------------------------------------
 *  Alterado: Ariston Cordeiro
 *  Data: 09/06/2010 - Movendo tratamento do nome do arquivo para funções (função tratarNomeArquivo()) para que outros programas possam utilizá-lo.
 *  -----------------------------------------------------------------------
 *  Alterado: Pitang Agile TI
 *  Data:     04/07/2016
 *  Objetivo: Requisito 136739: Cartilhas, Guias e Manuais - Nova funcionalidade internet e intranet (#446)
 *  -----------------------------------------------------------------------
 *  Alterado: Lucas Baracho
 *  Data:     17/02/2023
 *  Objetivo: Tarefa Redmine 279282
 *  -----------------------------------------------------------------------
 *  Alterado: Lucas Vicente
 *  Data:     15/03/2023
 *  Objetivo: Tarefa Redmine 280392 
 *  -----------------------------------------------------------------------
 */

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao       = $_POST['Botao'];
    $Titulo      = $_POST['Titulo'];
    $Descricao   = $_POST['Descricao'];
    $NomeArquivo = $_POST['NomeArquivo'];
    $Lei         = $_POST['Lei'];
} else {
    $Critica  = $_GET['Critica'];
    $Mensagem = $_GET['Mensagem'];
    $Mens     = $_GET['Mens'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "RotEnvioDocumento.php";

$TAMANHO_MAXIMO_ARQUIVO = 5242880; /* 5MB */

$Mens = 0;
$Tipo = 0;
$Mensagem .= "Informe: ";

// Verificar se os dados do post foram descartados no servidor (Normalmente devido ao arquivo ser maior que o máximo de post permitido)
if (($_SERVER['REQUEST_METHOD'] == "POST") and (count($_POST) == 0)) {
    $Mens      = 1;
    $Tipo      = 2;
    $Kbytes    = $TAMANHO_MAXIMO_ARQUIVO / 1024;
    $Mensagem .= "Arquivo com tamanho máximo menor que $Kbytes KB";
}

if ($_POST['Botao'] == "Enviar") {
    if (!is_uploaded_file($_FILES['NomeArquivo']['tmp_name'])) {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Nenhum arquivo enviado";
    } elseif ($_FILES['NomeArquivo']['size'] == 0) {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Arquivo com Tamanho diferente de 0 Kb";
    }

    if (($_FILES['NomeArquivo']['size'] > $TAMANHO_MAXIMO_ARQUIVO)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens       = 1;
        $Tipo       = 2;
        $Kbytes     = $TAMANHO_MAXIMO_ARQUIVO / 1024;
        $Kbytes     = (int) $Kbytes;
        $Mensagem .= "Arquivo com tamanho máximo menor que $Kbytes KB";
    }

    if ($Descricao == "" or is_null($Descricao)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Descrição ";
    }

    if (strlen($Descricao) > 200) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Descrição deve conter no máximo 200 caracteres";
    }

    if ($Titulo == "" or is_null($Titulo)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Título";
    }

    if ($Lei == "" or is_null($Lei)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }

        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Legislação de Compras relacionada";
    }

    if ($Mens == 0) {
        $db = Conexao();
        $db->query("BEGIN TRANSACTION");

        $sql = "SELECT MAX(CDOCPOCODI) FROM SFPC.TBDOCUMENTACAOPORTAL ";

        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            $db->query("ROLLBACK");
            EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $res);
            exit(0);
        }

        $linha = $res->fetchRow();
        $CDOCPOCODI = $linha[0] + 1;

        $ArquivoNome = $_FILES['NomeArquivo']['name'];

        $ArquivoDestinoNome = "DOC_" . $CDOCPOCODI . "_" . tratarNomeArquivo($ArquivoNome);
        $ArquivoDestino = $GLOBALS["CAMINHO_UPLOADS"] . "institucional/" . $ArquivoDestinoNome;

        if (is_null($linha[0])) {
            $CDOCPOCODI = 1;
        }

        if (file_exists($ArquivoDestino)) {
            unlink($ArquivoDestino);
        }

        if (@move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $ArquivoDestino)) {
            $sql = "INSERT INTO SFPC.TBDOCUMENTACAOPORTAL
					(CDOCPOCODI, EDOCPOARQU, EDOCPOTITU, EDOCPODESC, CDOCPOUSAL,
					 CDOCPOGRAL, TDOCPOULAT, EDOCPOARQS, FDOCPOTIPO, FDOCPONLEG)
                    VALUES
                    ($CDOCPOCODI, '$ArquivoNome', '$Titulo', '$Descricao', " . $_SESSION['_cgrempcodi_'] . ",
					 " . $_SESSION['_cusupocodi_'] . ", '" . date("Y-m-d H:i:s") . "', '" . $ArquivoDestinoNome . "', 'L', '" . $Lei . "') ";

            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                $db->query("ROLLBACK");
                EmailErroSQL("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $res);
                exit(0);
            }

            $db->query("COMMIT");
            $db->query("END");
            $db->disconnect();

            $Mens = 1;
            $Tipo = 1;
            $Mensagem = "Documento Carregado com Sucesso";

            $Titulo      = "";
            $Descricao   = "";
            $NomeArquivo = "";
            $Lei         = "";
        } else {
            $db->query("ROLLBACK");
            EmailErro("Erro de SQL em " . $NomePrograma, __FILE__, __LINE__, "Upload de arquivo foi recebido pelo servidor, mas não pôde ser gravado no diretório correto. Verifique se o diretório permite gravação de arquivos e se o sepaço de disco não está cheio.\n\nNome do arquivo a ser gravado: " . $ArquivoDestino);
            exit(0);
        }
    }
}
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script type="text/javascript">
    <!--
    function enviar(valor) {
	    document.formRotEnvioDocumento.Botao.value=valor;
	    document.formRotEnvioDocumento.submit();
    }

    function janela(pageToLoad, winName, width, height, center) {
	    xposition=0;
	    yposition=0;

	    if ((parseInt(navigator.appVersion) >= 4 ) && (center)){
		    xposition = (screen.width - width) / 2;
		    yposition = (screen.height - height) / 2;
	    }

        args = "width=" + width + ","
	            + "height=" + height + ","
	            + "location=0,"
	            + "menubar=0,"
	            + "resizable=0,"
	            + "scrollbars=0,"
	            + "status=0,"
	            + "titlebar=no,"
	            + "toolbar=0,"
	            + "hotkeys=0,"
	            + "z-lock=1," //Netscape Only
	            + "screenx=" + xposition + "," //Netscape Only
	            + "screeny=" + yposition + "," //Netscape Only
	            + "left=" + xposition + "," //Internet Explore Only
	            + "top=" + yposition; //Internet Explore Only
	
        window.open( pageToLoad,winName,args );
    }

    function CaracteresDescricao(valor) {
	    formRotEnvioDocumento.NCaracteresDescricao.value = '' +  formRotEnvioDocumento.Descricao.value.length;

        if (formRotEnvioDocumento.Descricao.value.length >= 200) {
		    formRotEnvioDocumento.NCaracteresDescricao.value = 200;
		    var texto = formRotEnvioDocumento.Descricao.value;
		    formRotEnvioDocumento.Descricao.value = formRotEnvioDocumento.Descricao.value.substr(0,200);
	    }
    }
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script type="text/javascript" src="../menu.js"></script>
	<script type="text/javascript">Init();</script>
	<form enctype="multipart/form-data" id="formRotEnvioDocumento" name="formRotEnvioDocumento" action="RotEnvioDocumento.php" method="post">
		<br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
                    <br>
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a>
                    > Institucional > Legislação > Envio de Documentação
                </td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
	        <?php
            if ($Mens == 1) {
                ?>
	            <tr>
				    <td width="100"></td>
				    <td align="left" colspan="2">
                        <?php ExibeMens($Mensagem,$Tipo,1); ?>
                    </td>
			    </tr>
	            <?php
            }
            ?>
	        <!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">ENVIO DE DOCUMENTOS</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
                                                Selecione o documento desejado. O documento deve ser menor que 5Mb e de um dos tipos:
                                                .zip, .pdf, .rtf, .doc, .xls, odp, odt, sdw, ppt ou .txt
                                            </p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left" summary="">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7"height="20">Arquivo<span style="color: red;">*</span></td>
													<td class="textonormal">
                                                        <input type="file" name="NomeArquivo" class="textonormal" size="50" value="<?php $NomeArquivo?>"/>
                                                    </td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Título<span style="color: red;">*</span></td>
													<td class="textonormal">
                                                        <input type="text" name="Titulo" class="textonormal" size="50" maxlength="99" value="<?php $Titulo?>"/>
                                                    </td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição<span style="color: red;">*</span></td>
													<td class="textonormal">
                                                        <font class="textonormal">máximo de 200 caracteres</font>
                                                        <input type="text" name="NCaracteresDescricao" disabled readonly size="3" value="0" OnFocus="javascript:document.formRotEnvioCartilhaGuiaManual.Descricao.focus();" class="textonormal">
                                                        <br>
                                                        <textarea name="Descricao" id="Descricao" class="textonormal" cols="39" rows="3" OnKeyUp="javascript:CaracteresDescricao(1)" OnBlur="javascript:CaracteresDescricao(0)" 
                                                        OnSelect="javascript:CaracteresDescricao(1)"><?php $Descricao?></textarea>
													</td>
												</tr>
                                                <tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Legislação de Compras<span style="color: red;">*</span></td>
                                                    <td class="textonormal" bgcolor="#FFFFFF">
                                                        <select name="Lei" class="textonormal">
                                                            <option value="">Selecione...</option>
                                                            <option value="N">Lei 8.666/1993</option>
                                                            <option value="S">Lei 14.133/2021</option>
                                                        </select>
                                                    </td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right">
                                            <input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Enviar');">
                                            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
                                            <input type="hidden" name="Botao" value="">
                                        </td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- Fim do Corpo -->
		</table>
	</form>
</body>
</html>