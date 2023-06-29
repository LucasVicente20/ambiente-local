<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoDocumentos.php
# Autor:    Rossana Lira
# Data:     19/03/07
# Objetivo: Programa de Visualiação de Documentos
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsAvisosDownload.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $Critica              = $_POST['Critica'];
    $Mensagem             = $_POST['Mensagem'];
    $Mens                 = $_POST['Mens'];
    $Tipo                 = $_POST['Tipo'];
    $Objeto               = $_POST['Objeto'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo       = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
    $GrupoCodigo          = $_POST['GrupoCodigo'];
    $LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
    $LicitacaoAno         = $_POST['LicitacaoAno'];
} else {
    $Acesso     = $_GET['Acesso'];
    if ($Acesso == "INTERNET") {
        TiraSeguranca();
    } else {
        Seguranca();
    }
}

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsAvisosResultado.php');

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsRegistroPrecoDocumentos.php";

if ($Botao != "Voltar") {
    $db     = Conexao();
    $sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.CLICPOCODL, ";
    $sql   .= "       D.ALICPOANOL, D.XLICPOOBJE, E.EORGLIDESC, D.TLICPODHAB ";
    $sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
    $sql   .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
    $sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI    AND D.CGREMPCODI = $GrupoCodigo  ";
    $sql   .= "   AND D.CMODLICODI = B.CMODLICODI    AND C.CCOMLICODI = D.CCOMLICODI ";
    $sql   .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.CLICPOPROC = $LicitacaoProcesso ";
    $sql   .= "   AND D.ALICPOANOP = $LicitacaoAno   AND E.CORGLICODI = D.CORGLICODI ";
    $sql   .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\nIP: ".$_SERVER["REMOTE_ADDR"]);
    } else {
        $Rows = $result->numRows();
        while ($Linha = $result->fetchRow()) {
            $GrupoDescricao          = $Linha[0];
            $ModalidadeDescricao     = $Linha[1];
            $ComissaoDescricao       = $Linha[2];
            $NLicitacao              = substr($Linha[3] + 10000, 1);
            $LicitacaoAno            = $Linha[4];
            $ObjetoLicitacao         = $Linha[5];
            $OrgaoLicitanteDescricao = $Linha[6];
            $LicitacaoDtAbertura     = substr($Linha[7], 8, 2) ."/". substr($Linha[7], 5, 2) ."/". substr($Linha[7], 0, 4);
            $LicitacaoHoraAbertura   = substr($Linha[7], 11, 5);
        }
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
function enviar(valor){
	document.Avisos.Botao.value=valor;
	document.Avisos.submit();
}
<?php
echo "function AbreDocumentos(Objeto,OrgaoLicitanteCodigo,ComissaoCodigo,ModalidadeCodigo,GrupoCodigo,LicitacaoProcesso,LicitacaoAno,DocumentoCodigo){\n";
echo "	document.Avisos.Objeto.value=Objeto;";
echo "	document.Avisos.OrgaoLicitanteCodigo.value=OrgaoLicitanteCodigo;";
echo "	document.Avisos.ComissaoCodigo.value=ComissaoCodigo;";
echo "	document.Avisos.ModalidadeCodigo.value=ModalidadeCodigo;";
echo "	document.Avisos.GrupoCodigo.value=GrupoCodigo;";
echo "	document.Avisos.LicitacaoProcesso.value=LicitacaoProcesso;";
echo "	document.Avisos.LicitacaoAno.value=LicitacaoAno;";
echo "	document.Avisos.DocumentoCodigo.value=DocumentoCodigo;";
echo "	document.Avisos.submit();";
echo "}\n";
MenuAcesso();
?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAvisosDownload.php" method="post" name="Avisos">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Avisos
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ($Mens == 1) {
    ?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ($Mens == 1) {
    ExibeMens($Mensagem, $Tipo, 1);
} ?></td>
	</tr>
	<?php 
} ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					AVISOS DE LICITAÇÕES - DOWNLOAD DE DOCUMENTOS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Selecione o documento desejado.
	          	   	</p>
	          		</td>
	  	      	</tr>
	  	      	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="center" summary="">
	      	      		<tr>
	        	      		<td class="textonegrito" bgcolor="#DCEDF7" colspan="5">
	        	      			<?php echo "$GrupoDescricao > $ModalidadeDescricao > $ComissaoDescricao<br>"; ?>
	        	      		</td>
	        	      	</tr>
	        	      	<tr>
											<td valign="top" bgcolor="#F7F7F7" class="textonegrito">PROCESSO</td>
											<td valign="top" bgcolor="#F7F7F7" class="textonegrito">LICITAÇÃO</td>
											<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBJETO</td>
											<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DATA/HORA<br>ABERTURA</td>
											<td valign="top" bgcolor="#F7F7F7" class="textonegrito">ÓRGÃO LICITANTE</td>
										</tr>
										<?php
                                        $LicitacaoProcesso = substr($LicitacaoProcesso+10000, 1);
                                        echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoProcesso/$LicitacaoAno</td>\n";
                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$LicitacaoAno</td>\n";
                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$ObjetoLicitacao</td>\n";
                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</b></td>\n";
                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$OrgaoLicitanteDescricao</td></tr>\n";
                                        ?>
										<tr>
											<td class="textonormal" colspan="5">
												<?php
                                                if ($Mens2 == 1) {
                                                    ExibeMens($Mensagem, $Tipo);
                                                }
                                                $sql  = "SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE ";
                                                $sql .= "  FROM SFPC.TBDOCUMENTOLICITACAO ";
                                                $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
                                                $sql .= "   AND CCOMLICODI = $ComissaoCodigo    AND CGREMPCODI = $GrupoCodigo";
                                                $result = $db->query($sql);
                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\nIP: ".$_SERVER["REMOTE_ADDR"]);
                                                } else {
                                                    $Rows = $result->numRows();
                                                    if ($Rows > 0) {
                                                        echo "<br><font class=\"textonegrito\">Documentos Relacionados</font><br><br>\n";
                                                        while ($Linha = $result->fetchRow()) {
                                                            $Arq = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/"."DOC".$GrupoCodigo."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$Linha[0];
                                                            if (file_exists($Arq)) {
                                                                if (is_file($Arq)) {
                                                                    $tamanho = filesize($Arq)/1024;
                                                                    $Objeto  = urlencode($Objeto);
                                                                    echo "<a href=\"#\" onclick=\"AbreDocumentos('$Objeto', '$OrgaoLicitanteCodigo','$ComissaoCodigo','$ModalidadeCodigo','$GrupoCodigo','$LicitacaoProcesso','$LicitacaoAno','$Linha[0]');\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\" alt=\"\"> $Linha[1]</a> - ";
                                                                    printf("%01.1f", $tamanho);
                                                                    echo " k";
                                                                    if ($Linha[2] != "") {
                                                                        echo " - $Linha[2]";
                                                                    }
                                                                    echo "<br>\n";
                                                                }
                                                            } else {
                                                                echo "<br><font class=\"textonegrito\">O arquivo $Arq não existe</font><br>&nbsp;\n";
                                                            }
                                                        }
                                                    } else {
                                                        echo "<br><font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
                                                    }
                                                }
                                                ?>
												<br>
											</td>
										</tr>
	        	      </table>
	        	      <input type="hidden" name="Objeto" value="<?echo $Objeto?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
									<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
									<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
									<input type="hidden" name="Botao" value="">
									<input type="hidden" name="GrupoCodigo" value="<?=$GrupoCodigo;?>">
									<input type="hidden" name="LicitacaoProcesso" value="<?=$LicitacaoProcesso;?>">
									<input type="hidden" name="LicitacaoAno" value="<?=$LicitacaoAno;?>">
									<input type="hidden" name="DocumentoCodigo" value="<?=$DocumentoCodigo;?>">
									</form>
	        	    </td>
		        	</tr>
		        	<tr>
		        		<form action="ConsAvisosResultado.php" method="post">
	    	      	<td class="textonormal" align="right">
									<input type="hidden" name="Objeto" value="<?echo $Objeto?>">
									<input type="hidden" name="OrgaoLicitanteCodigo" value="<?echo $OrgaoLicitanteCodigo?>">
									<input type="hidden" name="ComissaoCodigo" value="<?echo $ComissaoCodigo?>">
									<input type="hidden" name="ModalidadeCodigo" value="<?echo $ModalidadeCodigo?>">
									<input type="hidden" name="Botao" value="">
									<input type="hidden" name="GrupoCodigo" value="<?=$GrupoCodigo;?>">
									<input type="hidden" name="LicitacaoProcesso" value="<?=$LicitacaoProcesso;?>">
									<input type="hidden" name="LicitacaoAno" value="<?=$LicitacaoAno;?>">
									<input type="hidden" name="DocumentoCodigo" value="<?=$DocumentoCodigo;?>">
									<input type="submit" name="Voltar" value="Voltar" class="botao">
		          	</td>
		          	</form>
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
