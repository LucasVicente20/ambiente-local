<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaIncluir.php
# Autor:    Carlos Abreu
# Data:     27/06/2007
# Alterado: Rodrigo Melo
# Data:     21/01/2009 	- Fazendo alterações no programa para se adequar
#                         ao modelo de dados e disponibilizar a funcionalidade
#                         para Ata de Registro de Preço Externa.
# Objetivo: Programa de Inclusão/Exclusão das Atas de Registro de Preço Externa
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaIncluirDoc.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica              = $_POST['Critica'];
    $Botao                = $_POST['Botao'];
    $Titulo               = $_POST['Titulo'];
} else {
    $Mens = $_GET['Mens'];
    $Tipo = $_GET['Tipo'];
    $Mensagem = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtaRegistroPrecoExternaIncluir.php";

if ($Botao == "Incluir") {
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
            $db     = Conexao();
            $sql    = "SELECT MAX(CARPETCODI) FROM SFPC.TBATAREGISTROPRECOEXTERNATIT ";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $result->fetchRow();
                $AtaRegistroPrecoCod = $Linha[0] + 1;

                # Insere na tabela de Atas do Registro de Preço Externa#
                $sql       = "INSERT INTO SFPC.TBATAREGISTROPRECOEXTERNATIT( ";
                $sql      .= "CARPETCODI, EARPETTITU, TARPETULAT, CGREMPCODI, CUSUPOCODI ";
                $sql      .= ") VALUES ( ";
                $sql      .= "$AtaRegistroPrecoCod, '$Titulo', '".date("Y-m-d H:i:s")."', ";
                $sql      .= $_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_']." )";
                $result   = $db->query($sql);
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    // redirecionar para tela de adicao de arquivos
                    //$Mensagem = "Titulo da Ata de Registro de Preço Externa Incluído com Sucesso";
                    $_SESSION['AtaRegistroPrecoCod'] = $AtaRegistroPrecoCod;
                    //$Url = "CadAtaRegistroPrecoExternaIncluirDoc.php?Tipo=1&Mens=1&Mensagem=".urlencode($Mensagem);
                    $Url = "CadAtaRegistroPrecoExternaIncluirDoc.php";
                    if (!in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }
                    $db->disconnect();
                    header("location: ".$Url);
                    exit();
                }
            }
            $db->disconnect();
        }
    }
}

# Busca descrição da comissão #
$db     = Conexao();
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
<form enctype="multipart/form-data" action="CadAtaRegistroPrecoExternaIncluir.php" method="post" name="AtaRegistroPreco">
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
		         Para incluir um titulo clique no botão "Incluir". Em seguida aparecera uma nova tela com a opção de enviar arquivos.
		         </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" height="20">Titulo </td>
	              <td class="textonormal"><font class="textonormal">máximo de 200 caracteres</font>
				<input type="text" name="NCaracteres" size="3" value="<?php echo $NCaracteres ?>" OnFocus="javascript:document.AtaRegistroPreco.Titulo.focus();" class="textonormal"><br>
				<textarea name="Titulo" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $Titulo; ?></textarea></td>
	            </tr>
			</table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
            <input type="hidden" name="Botao" value="">
            <input type="hidden" name="Critica" value="1">
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
