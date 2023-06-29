<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaExcluir.php
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
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaManter.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica             = $_POST['Critica'];
    $Botao               = $_POST['Botao'];
    $AtaRegistroPrecoCod = $_POST['AtaRegistroPrecoCod'];
    $AtaRegistroPrecoDocumento = $_POST['AtaRegistroPrecoDocumento'];
    $Titulo = $_POST['Titulo'];
} else {
    $AtaRegistroPrecoCod = $_SESSION['AtaRegistroPrecoCod'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoExternaExcluir.php";

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
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
        $Url = "CadAtaRegistroPrecoExternaSelecionar.php?Tipo=1&Mens=1&Mensagem=".urlencode($Mensagem);
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header("location: ".$Url);
        exit();
    } else {
        $db->query("ROLLBACK");
    }
    $db->disconnect();
} elseif ($Botao == "Voltar") {
    header("location: CadAtaRegistroPrecoExternaManter.php");
    exit();
}

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
<form enctype="multipart/form-data" action="CadAtaRegistroPrecoExternaExcluir.php" method="post" name="AtaRegistroPreco">
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
	        	EXCLUIR - ATA DE REGISTRO DE PREÇO EXTERNA
          </td>
        </tr>
        <tr>
          <td class="textonormal">
		         <p align="justify">
		         Para excluir a Ata de Registro de Preço Externa e seus documentos clique no botão "Excluir Ata".
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
                                        echo "	<td class=\"textonormal\" colspan=\"2\">$Linha[1] - $Data</td> \n";
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
						</table>
					</td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="hidden" name="AtaRegistroPrecoCod" value="<?=$AtaRegistroPrecoCod?>">
			<input type="button" value="Excluir Ata" class="botao" onclick="javascript:enviar('Excluir');">
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
