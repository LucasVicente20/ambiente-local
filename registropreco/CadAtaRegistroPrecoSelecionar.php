<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtaRegistroPrecoSelecionar.php
# Objetivo: Programa de Seleção de Atas
# Autor:    Rossana Lira
# Data:     21/03/07
# Alterado: Carlos Abreu
# Data:     03/09/2007 - Ajuste para destacar quando processo licitatorio possuir ata de registro de preco cadastrada
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/registropreco/CadAtaRegistroPrecoManter.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica                       = $_POST['Critica'];
    $LicitacaoAno                  = $_POST['LicitacaoAno'];
    $GrupoProcessoAnoComissaoOrgao = $_POST['GrupoProcessoAnoComissaoOrgao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CasAtaRegistroPrecoSelecionar.php";

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);
if ($LicitacaoAno == '') {
    $LicitacaoAno = '2007';
}

if ($Critica == 1) {
    $Mens     = 0;
    $Mensagem = "Informe: ";
    if ($GrupoProcessoAnoComissaoOrgao == "") {
        $Mens = 1;
        $Tipo = 2;
        $Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.AtaRegistroPreco.AtaRegistroPrecoCodigo.focus();\" class=\"titulo2\">Selecione um Processo Licitatório</a>";
    } else {
        $NProcessoAnoComissao = explode("_", $GrupoProcessoAnoComissaoOrgao);
        $Grupo                = $NProcessoAnoComissao[0];
        $Processo             = substr($NProcessoAnoComissao[1] + 10000, 1);
        $ProcessoAno          = $NProcessoAnoComissao[2];
        $ComissaoCodigo       = $NProcessoAnoComissao[3];
        $OrgaoLicitante       = $NProcessoAnoComissao[4];
        $Url = "CadAtaRegistroPrecoManter.php?Grupo=$Grupo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitante=$OrgaoLicitante";
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header("location: ".$Url);
        exit();
    }
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
<form action="CadAtaRegistroPrecoSelecionar.php" method="post" name="AtaRegistroPreco">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro Preço > Ata Interna
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
	           MANTER - ATAS DE REGISTRO DE PREÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para incluir/excluir um Ata cadastrada, selecione o ano, o Processo Licitatório e clique no botão "Selecionar".<br>
             Só serão exibidas os Processos Licitatórios que são do tipo Registro de Preço e que passaram pela fase de homologação.<br>
             O Processo Licitatório que apresentar um '*' ao lado indica que possui Ata de Registro de Preço cadastrada.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
            	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Ano</td>
								<td class="textonormal">
				  	      <select name="LicitacaoAno" class="textonormal" onChange="javascript:document.AtaRegistroPreco.Critica.value=0;document.AtaRegistroPreco.submit();">
										<?php
                                    $db     = Conexao();
                                        $sql    = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
                                        $sql   .= "  FROM SFPC.TBLICITACAOPORTAL ";
                                        $sql   .= " WHERE TO_CHAR(TLICPODHAB,'YYYY') <= '".date('Y')."' ";
                                        $sql   .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
                                        $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        }
                                        while ($Linha = $result->fetchRow()) {
                                            if ($Linha[0] == $LicitacaoAno) {
                                                echo "<option value=\"$Linha[0]\" selected>$Linha[0]</option>\n";
                                            } else {
                                                echo "<option value=\"$Linha[0]\">$Linha[0]</option>\n";
                                            }
                                        }
                                     $db->disconnect();
                                        ?>
								  </select>
							  </td>
            	</tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                <td class="textonormal">
                  <select name="GrupoProcessoAnoComissaoOrgao" value="" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório...</option>
                  	<?php
                                        $db     = Conexao();
                                        $sql    = "SELECT DISTINCT A.CGREMPCODI, A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, A.CORGLICODI,  ";
                                        $sql   .= "       B.ECOMLIDESC, C.EGREMPDESC, D.CFASESCODI  ";
                                        $sql   .= ",(SELECT COUNT(*) FROM SFPC.TBATAREGISTROPRECO WHERE CLICPOPROC = A.CLICPOPROC AND ALICPOANOP = A.ALICPOANOP AND CCOMLICODI = A.CCOMLICODI AND CGREMPCODI = A.CGREMPCODI)  ";
                                        $sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBFASELICITACAO D ";
                                        $sql   .= " WHERE A.CCOMLICODI = B.CCOMLICODI  AND  TO_CHAR(A.TLICPODHAB,'YYYY') = '$LicitacaoAno' AND A.FLICPOREGP = 'S' ";
                                        $sql   .= "  AND  A.CGREMPCODI = C.CGREMPCODI  AND  A.CLICPOPROC = D.CLICPOPROC AND A.ALICPOANOP = D.ALICPOANOP ";
                                        $sql   .= "  AND  A.CGREMPCODI = D.CGREMPCODI  AND  A.CCOMLICODI = D.CCOMLICODI ";
                                        $sql   .= "  AND  D.CFASESCODI = 13  ";     #FASE DE HOMOLOGAÇÃO
                                        $sql   .= " ORDER BY C.EGREMPDESC ASC, B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
                                        $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            while ($Linha = $result->fetchRow()) {
                                                if ($Linha[0] != $GrupoCodigoAnt) {
                                                    $GrupoCodigoAnt = $Linha[0];
                                                    echo "<option value=\"\">$Linha[6]</option>\n" ;
                                                }
                                                if ($Linha[3] != $ComissaoCodigoAnt) {
                                                    $ComissaoCodigoAnt = $Linha[3];
                                                    echo "<option value=\"\">&nbsp;&nbsp;$Linha[5]</option>\n" ;
                                                }
                                                $NProcesso = substr($Linha[1] + 10000, 1);
                                                if ($Linha[8]>0) {
                                                    echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[2] *</option>\n" ;
                                                } else {
                                                    echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[2]</option>\n" ;
                                                }
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
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Selecionar" class="botao">
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
<script language="javascript" type="">
<!--
document.AtaRegistroPreco.GrupoProcessoAnoComissaoOrgao.focus();
//-->
</script>
