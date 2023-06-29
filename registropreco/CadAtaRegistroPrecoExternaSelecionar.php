<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoExternaSelecionar.php
# Autor:    Carlos Abreu
# Data:     27/06/2007
# Objetivo: Programa de Seleção de Atas
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoExternaManter.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $AtaRegistroPrecoCod = $_GET['AtaRegistroPrecoCod'];
    $Tipo                = $_GET['Tipo'];
    $Mens                = $_GET['Mens'];
    $Mensagem            = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CasAtaRegistroPrecoExternaSelecionar.php";

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);

if ($AtaRegistroPrecoCod != "") {
    $_SESSION['AtaRegistroPrecoCod'] = $AtaRegistroPrecoCod;
    $Url = "CadAtaRegistroPrecoExternaManter.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit();
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Ata Externa
    </td>
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
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" class="textonormal" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - ATAS DE REGISTRO DE PREÇO EXTERNA
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para incluir/excluir um Ata cadastrada, selecione o ano, o Processo Licitatório e clique no botão "Selecionar".<br>
             Só serão exibidas os Processos Licitatórios que são do tipo Registro de Preço e que passaram pela fase de homologação.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
            <tr>
                <td class="textonormal">
                	<?php
                    $db     = Conexao();
                    $sql    = "SELECT CARPETCODI, EARPETTITU ";
                    $sql   .= "  FROM SFPC.TBATAREGISTROPRECOEXTERNATIT ";
                    $sql   .= " ORDER BY EARPETTITU ";
                    $result = $db->query($sql);
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        while ($Linha = $result->fetchRow()) {
                            $Url = "CadAtaRegistroPrecoExternaSelecionar.php?AtaRegistroPrecoCod=$Linha[0]";
                            if (!in_array($Url, $_SESSION['GetUrl'])) {
                                $_SESSION['GetUrl'][] = $Url;
                            }
                            echo "<a href=\"$Url\" class=\"textonormal\"><u>$Linha[1]</u></a><br><br>\n" ;
                        }
                    }
                    $db->disconnect();
                    ?>
                  </select>
                </td>
                <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</body>
</html>
<script language="javascript" type="">
<!--
document.AtaRegistroPreco.GrupoProcessoAnoComissaoOrgao.focus();
//-->
</script>
