<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaManter.php
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
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaSelecionar.php');
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaExcluir.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica             = $_POST['Critica'];
    $Botao               = $_POST['Botao'];
    $QuantArquivos       = $_POST['QuantArquivos'];
    $AtaRegistroPrecoCod = $_POST['AtaRegistroPrecoCod'];
    $AtaRegistroPrecoDocumento = $_POST['AtaRegistroPrecoDocumento'];
    $Titulo = $_POST['Titulo'];
} else {
    $AtaRegistroPrecoCod = $_SESSION['AtaRegistroPrecoCod'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoExternaManter.php";

# Redireciona para a página de excluir #
if ($Botao == "Manter") {
    # Critica dos Campos #
    if ($Critica == 1) {
        $Mensagem = "Informe: ";
        $Titulo = strtoupper2(trim($Titulo));
        if (strlen($Titulo) == 0) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Titulo";
        } elseif (strlen($Titulo) > 200) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "O Titulo com até 200 Caracteres ( atualmente com ". strlen($Titulo) ." )";
        }
        if ($Mens == 0) {
            $db   = Conexao();
            $sql  = "UPDATE SFPC.TBATAREGISTROPRECOEXTERNATIT ";
            $sql .= "   SET EARPETTITU = '$Titulo', TARPETULAT = '".date("Y-m-d H:i:s")."' ";
            $sql .= " WHERE CARPETCODI = $AtaRegistroPrecoCod ";
            $result   = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                // redirecionar para tela de adicao de arquivos
                $Mensagem = "Ata de Registro de Preço Externa Alterada com Sucesso";
                $Url = "CadAtaRegistroPrecoExternaSelecionar.php?Tipo=1&Mens=1&Mensagem=".urlencode($Mensagem);
                if (!in_array($Url, $_SESSION['GetUrl'])) {
                    $_SESSION['GetUrl'][] = $Url;
                }
                $db->disconnect();
                header("location: ".$Url);
                exit();
            }
            $db->disconnect();
        }
    }
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
} elseif ($Botao == "Voltar") {
    header("location: CadAtaRegistroPrecoExternaSelecionar.php");
    exit();
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
    header("location: CadAtaRegistroPrecoExternaExcluir.php");
    exit();
} else {
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
}
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
function ncaracteres(valor){
	document.AtaRegistroPreco.NCaracteres.value = '' +  document.AtaRegistroPreco.Titulo.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.AtaRegistroPreco.NCaracteres.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form enctype="multipart/form-data" action="CadAtaRegistroPrecoExternaManter.php" method="post" name="AtaRegistroPreco">
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
	        	MANTER - ATA DE REGISTRO DE PREÇO EXTERNA
          </td>
        </tr>
        <tr>
          <td class="textonormal">
		         <p align="justify">
		         Para incluir um Documento, localize o arquivo e clique no botão "Incluir Documento". Para apagar o(s) Documento(s), selecione-o(s) e clique no botão "Excluir Documento". Para alterar o Titulo clique no botão "Alterar Ata" e para excluir clique no botão "Excluir Ata".
		         </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="" width="100%">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Titulo </td>
	              <td class="textonormal"><font class="textonormal">máximo de 200 caracteres</font>
				<input type="text" name="NCaracteres" size="3" value="<?php echo $NCaracteres ?>" OnFocus="javascript:document.AtaRegistroPreco.Titulo.focus();" class="textonormal"><br>
				<textarea name="Titulo" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Titulo; ?></textarea></td>
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

			<input type="button" value="Alterar Ata" class="botao" onclick="javascript:enviar('Manter');">
			<input type="button" value="Excluir Ata" class="botao" onclick="javascript:enviar('ExcluirAta');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
