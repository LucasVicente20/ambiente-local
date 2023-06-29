<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaIncluirDoc.php
# Autor:    Carlos Abreu
# Data:     27/06/2007
# Alterado: Rodrigo Melo
# Data:     21/01/2009 	- Fazendo alterações no programa para se adequar
#                         ao modelo de dados e disponibilizar a funcionalidade
#                         para Ata de Registro de Preço Externa.
# Objetivo: Programa de Inclusão/Exclusão das Atas de Registro de Preço
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaIncluir.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica             = $_POST['Critica'];
    $Botao               = $_POST['Botao'];
    $QuantArquivos       = $_POST['QuantArquivos'];
    $AtaRegistroPrecoCod = $_POST['AtaRegistroPrecoCod'];
    $AtaRegistroPrecoDocumento = $_POST['AtaRegistroPrecoDocumento'];
} else {
    $Mens                = $_GET['Mens'];
    $Tipo                = $_GET['Tipo'];
    $Mensagem            = urldecode($_GET['Mensagem']);
    $AtaRegistroPrecoCod = $_SESSION['AtaRegistroPrecoCod'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoExternaIncluirDoc.php";

# Redireciona para a página de excluir #
if ($Botao == "IncluirAta") {
    if ($QuantArquivos > 0) {
        $Mensagem = "Ata de Registro de Preço Externa Incluída com sucesso e contendo $QuantArquivos documento(s) anexado(s)";
    } else {
        $Mensagem = "Ata de Registro de Preço Externa Incluída com sucesso e sem documento anexado";
    }
    $Url = "CadAtaRegistroPrecoExternaIncluir.php?Tipo=1&Mens=1&Mensagem=".urlencode($Mensagem);
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit();
} elseif ($Botao == "Excluir") {
    if ($QuantArquivos > 0) {
        $db = Conexao();
        $db->query("BEGIN TRANSACTION");
        $Erro = 0;
        for ($Row = 0 ; $Row < $QuantArquivos ; $Row++) {
            if ($AtaRegistroPrecoDocumento[$Row] != "") {
                $sql    = "DELETE FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
                $sql   .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
                $sql   .= "   AND CARPEDCODI = ".$AtaRegistroPrecoDocumento[$Row];
                $result = $db->query($sql);
                if (PEAR::isError($result)) {
                    $Erro = 1;
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."registropreco/ATAREGISTROPRECOEXTERNA_".$AtaRegistroPrecoCod."_".$AtaRegistroPrecoDocumento[$Row];
                    if (file_exists($Arquivo)) {
                        if (unlink($Arquivo)) {
                            $Mens = 1;
                            $Tipo = 1;
                            $Mensagem = "Atas(s) Excluída(s) com Sucesso";
                        } else {
                            $Mens = 1;
                            $Tipo = 2;
                            $Erro = 1;
                            $Mensagem = "Erro na Exclusão do Arquivo";
                        }
                    } else {
                        $Mens = 1;
                        $Tipo = 1;
                        $Mensagem = "Atas(s) Excluída(s) com Sucesso";
                    }
                }
            }
        }
        if ($Erro==0) {
            $db->query("COMMIT");
            $db->query("END TRANSACTION");
        } else {
            $db->query("ROLLBACK");
        }
        $db->disconnect();
    }
} elseif ($Botao == "Incluir") {
    # Critica dos Campos #
    if ($Critica == 1) {
        $ArquivoNome = $_FILES['NomeArquivo']['name'];
        $ArquivoNomeTratado = RetiraAcentos($ArquivoNome);
        if (!eregi("\.zip$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.pdf$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.rtf$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.doc$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.xls$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.txt$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.sdw$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.jpg$", $_FILES['NomeArquivo']['name']) &&
        !eregi("\.bmp$", $_FILES['NomeArquivo']['name'])) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "Desculpe, selecione somente arquivos com a extensão .zip, .jpg, .bmp, .pdf, .rtf, .doc, .xls, .txt ou .sdw";
        }
        $Tamanho = 5242880; /* 5MB */
        if (($_FILES['NomeArquivo']['size'] > $Tamanho) ||
        ($_FILES['NomeArquivo']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Kbytes = $Tamanho/1024;
            $Kbytes = (int) $Kbytes;
            $Mensagem .= "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }
        $AtaRegistroPrecoDescricao = strtoupper2(trim($AtaRegistroPrecoDescricao));
        if (strlen($AtaRegistroPrecoDescricao) > 200) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Observação das Atas com até 200 Caracteres ( atualmente com ". strlen($AtaRegistroPrecoDescricao) ." )";
        }
        $Tam = strlen($_FILES['NomeArquivo']['name']);
        if (strlen($_FILES['NomeArquivo']['name']) > 100) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Nome do Arquivo com até 100 Caracateres ( atualmente com ".strlen($_FILES['NomeArquivo']['name'])." )";
        }
        if ($Mens == 0) {
            $db     = Conexao();
            $sql    = "SELECT MAX(CARPEDCODI) FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
            $sql   .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $result->fetchRow();
                $AtaRegistroPrecoDocumentoCod = $Linha[0] + 1;

                # Insere na tabela de Atas do Registro de Preço #
                $db->query("BEGIN TRANSACTION");
                $sql       = "INSERT INTO SFPC.TBATAREGISTROPRECOEXTERNADOC( ";
                $sql      .= "CARPETCODI, CARPEDCODI, DARPEDDATA, EARPEDNOME, TARPEDULAT, CGREMPCODI, CUSUPOCODI, EARPEDNOMS ";
                $sql      .= ") VALUES ( ";
                $sql      .= "$AtaRegistroPrecoCod, $AtaRegistroPrecoDocumentoCod, '".date("Y-m-d")."', '".$ArquivoNome."', '".date("Y-m-d H:i:s")."', ";
                $sql      .= $_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$ArquivoNomeTratado' )";
                $result   = $db->query($sql);
                if (PEAR::isError($result)) {
                    $db->query("ROLLBACK");
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."registropreco/ATAREGISTROPRECOEXTERNA_".$AtaRegistroPrecoCod."_".$AtaRegistroPrecoDocumentoCod;
                    if (file_exists($Arquivo)) {
                        unlink($Arquivo);
                    }
                    if (@move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $Arquivo)) {
                        $Mens              = 1;
                        $Tipo              = 1;
                        $Mensagem          = "Ata Carregada com Sucesso";
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                    } else {
                        $Mens     = 1;
                        $Tipo     = 2;
                        $Mensagem = "Erro no Carregamento do Arquivo";
                        $db->query("ROLLBACK");
                    }
                }
            }
            $db->disconnect();
        }
    }
} elseif ($Botao == "ExcluirAta") {
    $db = Conexao();
    $db->query("BEGIN TRANSACTION");
    $Erro = 0;
    $sql  = "SELECT CARPEDCODI ";
    $sql .= "  FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
    $sql .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        $Erro = 1;
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."registropreco/ATAREGISTROPRECOEXTERNA_".$AtaRegistroPrecoCod."_".$Linha[0];
            if (file_exists($Arquivo)) {
                if (!unlink($Arquivo)) {
                    $Erro = 1;
                }
            }
        }
        $sql  = "DELETE FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
        $sql .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            $Erro = 1;
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $sql  = "DELETE FROM SFPC.TBATAREGISTROPRECOEXTERNATIT ";
            $sql .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                $Erro = 1;
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            }
        }
    }
    if ($Erro==0) {
        $db->query("COMMIT");
        $db->query("END TRANSACTION");
        $Mensagem = "Ata de Registro de Preço Externa Excluída com Sucesso";
        //$Url = "CadAtaRegistroPrecoExternaSelecionar.php?Tipo=1&Mens=1&Mensagem=".urlencode($Mensagem);
        $Url = "CadAtaRegistroPrecoExternaIncluir.php";
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        $db->disconnect();
        header("location: ".$Url);
        exit();
    } else {
        $db->query("ROLLBACK");
    }
    $db->disconnect();
}

# Busca descrição da comissão #
$db     = Conexao();
$sql    = "SELECT EARPETTITU FROM SFPC.TBATAREGISTROPRECOEXTERNATIT WHERE CARPETCODI = $AtaRegistroPrecoCod";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
    $Linha = $result->fetchRow();
    $Titulo = $Linha[0];
}
$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.AtaRegistroPreco.Botao.value=valor;
	document.AtaRegistroPreco.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form enctype="multipart/form-data" action="CadAtaRegistroPrecoExternaIncluirDoc.php" method="post" name="AtaRegistroPreco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
<!-- Caminho -->
<tr>
<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
<td align="left" class="textonormal" colspan="2">
<font class="titulo2">|</font>
<a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Ata Externa</td>
</tr>
<!-- Fim do Caminho-->

<!-- Erro -->
<?php if ($Mens == 1) {
    ?>
<tr>
<td width="150"></td>
<td align="left" colspan="2"><?php ExibeMens($Mensagem, $Tipo, 1); ?></td>
</tr>
<?php 
} ?>
<!-- Fim do Erro -->

<!-- Corpo -->
<tr>
<td width="150"></td>
<td class="textonormal"><br>
<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
<tr>
<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
INCLUIR - ATA DE REGISTRO DE PREÇO EXTERNA
</td>
</tr>
<tr>
<td class="textonormal">
<p align="justify">
Para incluir um Documento, localize o arquivo e clique no botão "Incluir Documento". Para apagar o(s) Documento(s), selecione-o(s) e clique no botão "Excluir Documento".
</p>
</td>
</tr>
<tr>
<td>
<table border="0" summary="" width="100%">
<tr>
<td class="textonormal" bgcolor="#DCEDF7" height="20">Titulo </td>
<td class="textonormal"><?php echo $Titulo; ?></td>
</tr>
<tr>
					<td class="textonormal" colspan="2">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#bfdaf2" WIDTH="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
									ANEXAÇÃO DE DOCUMENTO(S)
								</td>
							</tr>
							<tr>
				            	<td class="textonormal" height="20">Arquivo </td>
								<td class="textonormal">
									<input type="file" name="NomeArquivo" class="textonormal">
									<input type="hidden" name="AtaRegistroPrecoCod" value="<?echo $AtaRegistroPrecoCod?>">
									<input type="hidden" name="Critica" value="1">
								</td>
				            </tr>
							<?php
                            $db     = Conexao();
                            $sql    = "SELECT CARPEDCODI, EARPEDNOME, DARPEDDATA ";
                            $sql   .= "  FROM SFPC.TBATAREGISTROPRECOEXTERNADOC ";
                            $sql   .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
                            $result = $db->query($sql);
                            if (PEAR::isError($result)) {
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            } else {
                                $Rows = $result->numRows();
                                if ($Rows > 0) {
                                    while ($Linha = $result->fetchRow()) {
                                        $cont++;
                                        $row  = $cont-1;
                                        $Data = substr($Linha[2], 8, 2)."/".substr($Linha[2], 5, 2)."/".substr($Linha[2], 0, 4);
                                        echo "<tr>\n";
                                        echo "	<td class=\"textonormal\" colspan=\"2\"><input type=checkbox name=\"AtaRegistroPrecoDocumento[$row]\" value=\"".$Linha[0]."\"> $Linha[1] - $Data</td> \n";
                                        echo "</tr>\n";
                                    }
                                } else {
                                    echo "<tr>\n";
                                    echo "	<td class=\"textonormal\" height=\"20\" colspan=\"2\">\n";
                                    echo "		Nenhum Documento Cadastrado!\n";
                                    echo "	</td>\n";
                                    echo "</tr>\n";
                                }
                            }
                            $db->disconnect();
                            ?>

							<tr>
								<td align="center" valign="middle" class="titulo3" colspan="2">
									<input type="button" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir');">
									<input type="button" value="Excluir Documento" class="botao" onclick="javascript:enviar('Excluir');">
								</td>
							</tr>
						</table>
					</td>
	            </tr>
</table>
<input type="hidden" name="QuantArquivos" value="<?echo $Rows?>">
</td>
</tr>
<tr>
<td class="textonormal" align="right">
<input type="button" value="Incluir Ata" class="botao" onclick="javascript:enviar('IncluirAta');">
<input type="button" value="Excluir Ata" class="botao" onclick="javascript:enviar('ExcluirAta');">
<input type="hidden" name="Botao" value="">
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
