<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsRegistroPrecoDetalhes.php
# Autor:    Rossana Lira
# Data:     19/03/07
# Objetivo: Programa de Detalhamento Processo Licitatório Tipo Registro Preço
#-----------------
# Autor:    Ariston Cordeiro
# Alterado: 19/03/07 - Modificado botão voltar para ser igual ao botão voltar do navegador web
#------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

// 220038--

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                = $_POST['Botao'];
    $Critica              = $_POST['Critica'];
} else {
    $GrupoCodigo          = $_GET['GrupoCodigo'];
    $Processo             = $_GET['Processo'];
    $ProcessoAno          = $_GET['ProcessoAno'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
//		$Fase							    = $_GET['Fase'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsRegistroPrecoDetalhes.php" method="post" name="RegistroPreco">
<br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif"></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Registro de Preço > Consulta
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
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					CONSULTA DE REGISTRO DE PREÇOS - DETALHAMENTO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" colspan="4">
	      	    		<p align="justify">
	        	    		Para visualizar os documentos, clique no item desejado. Para visualizar a tela de pesquisa, clique no botão "Voltar".
	          	   	</p>
	          		</td>
							</tr>
							<tr>
		  	        <?php
                                # Resgata as informações da licitação #
                                $db     = Conexao();
                                $sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
                                $sql   .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
                                $sql   .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
                                $sql   .= "       D.VLICPOTGES ";
                                $sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
                                $sql   .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
                                $sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
                                $sql   .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
                                $sql   .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
                                $sql   .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
                                $sql   .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
                                $result = $db->query($sql);
                                if (PEAR::isError($result)) {
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                } else {
                                    $Rows = $result->numRows();
                                    while ($Linha = $result->fetchRow()) {
                                        $GrupoDesc             = $Linha[0];
                                        $ModalidadeDesc        = $Linha[1];
                                        $ComissaoDesc          = $Linha[2];
                                        $OrgaoLicitacao        = $Linha[4];
                                        $ObjetoLicitacao       = $Linha[3];
                                        $Licitacao             = substr($Linha[6] + 10000, 1);
                                        $AnoLicitacao          = $Linha[7];
                                        $LicitacaoDtAbertura   = substr($Linha[5], 8, 2) ."/". substr($Linha[5], 5, 2) ."/". substr($Linha[5], 0, 4);
                                        $LicitacaoHoraAbertura = substr($Linha[5], 11, 5);
                                        if ($Linha[8] == "S") {
                                            $RegistroPreco         = "SIM";
                                        } else {
                                            $RegistroPreco         = "NÃO";
                                        }
                                        $ModalidadeCodigo = $Linha[9];
                                        $ValorEstimado         = converte_valor($Linha[10]);
                                        $ValorHomologado       = converte_valor($Linha[11]);
                                        $TotalGeralEstimado    = converte_valor($Linha[12]);
                                    }
                                }
                                echo "			<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\">\n";
                         echo "$GrupoDesc<br><br>$ModalidadeDesc<br><br>$ComissaoDesc<br>";
                         echo "			</td>\n";
                                $Processo = substr($Processo+10000, 1);
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">PROCESSO</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Processo/$ProcessoAno</td>\n";
                                echo "			</tr>\n";
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">LICITAÇÃO</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Licitacao/$AnoLicitacao</td>\n";
                                echo "			</tr>\n";
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">REGISTRO DE PREÇO";
                                if ($ModalidadeCodigo == 3) {
                                    echo "/PERMISSÃO REMUNERADA DE USO";
                                }
                                echo "				</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$RegistroPreco</td>\n";
                                echo "			</tr>\n";
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">OBJETO</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ObjetoLicitacao</td>\n";
                                echo "			</tr>\n";
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">DATA/HORA DE ABERTURA</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$LicitacaoDtAbertura $LicitacaoHoraAbertura h</b></td>\n";
                                echo "			</tr>\n";
                                echo "			<tr>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">ÓRGÃO LICITANTE</td>\n";
                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$OrgaoLicitacao</td>\n";
                                echo "			</tr>\n";
                                if ($ValorHomologado != "0,00") {
                                    if ($ValorEstimado == "") {
                                        $ValorEstimado = "NÃO INFORMADO";
                                    }
                                    echo "			<tr>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR ESTIMADO</td>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorEstimado</td>\n";
                                    echo "			</tr>\n";
                                }
                                if ($TotalGeralEstimado != "0,00") {
                                    echo "			<tr>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">TOTAL GERAL ESTIMADO<br>(Itens que Lograram Êxito)</td>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$TotalGeralEstimado</td>\n";
                                    echo "			</tr>\n";
                                }
                                if ($ValorHomologado != "0,00") {
                                    echo "			<tr>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR HOMOLOGADO<br>(Itens que Lograram Êxito)</td>\n";
                                    echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorHomologado</td>\n";
                                    echo "			</tr>\n";
                                }

                    # Pega as Fases da Licitação #
                    $sql    = "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
                    $sql   .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
                    $sql   .= "       C.EATASFNOME ";
                    $sql   .= "  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
                    $sql   .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
                    $sql   .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
                    $sql   .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
                    $sql   .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
                    $sql   .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
                    $sql   .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI = 13 "; //Só a fase de Homologação
                    $sql   .= " ORDER BY A.AFASESORDE";
                    $result = $db->query($sql);
                            if (PEAR::isError($result)) {
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            }
                            $Rows = $result->numRows();
                            if ($Rows > 0) {
                                echo "<tr>\n";
                                echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\"> ATA DE REGISTRO DE PREÇO - DOCUMENTO(S)</td>\n";
                                echo "</tr>\n"; ?>
									<tr>
										<td class="textonormal" colspan="4"><br>
											<?php
                                            if ($Mens2 == 1) {
                                                ExibeMens($Mensagem, $Tipo);
                                            }
                                            # Pega a(s) ata(s) de registro de preços - documentos #
                                            $sql  = "SELECT CATARPCODI, EATARPNOME ";
                                $sql .= "  FROM SFPC.TBATAREGISTROPRECO";
                                $sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                                $sql .= "   AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = $GrupoCodigo ";
                                $result = $db->query($sql);
                                if (PEAR::isError($result)) {
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                } else {
                                    $Rows = $result->numRows();
                                    resetArquivoAcesso();
                                    while ($cols = $result->fetchRow()) {
                                        $cont++;
                                        $dados[$cont-1] = "$cols[0];$cols[1];$cols[2]";
                                    }
                                                    # Mostra os Documentos relacionados com a Licitação #
                                                    if ($Rows > 0) {
                                                        for ($Row = 0 ; $Row < $Rows ; $Row++) {
                                                            $Linha = explode(";", $dados[$Row]);
                                                            $ArqUpload = "registropreco/ATAREGISTROPRECO".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$Linha[0];
                                                            $Arq = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;

                                                            if (file_exists($Arq)) {
                                                                $tamanho = filesize($Arq)/1024;
                                                                addArquivoAcesso($ArqUpload);
                                                                $Url = "ConsRegistroPrecoDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
                                                                if (!in_array($Url, $_SESSION['GetUrl'])) {
                                                                    $_SESSION['GetUrl'][] = $Url;
                                                                }
                                                                echo "<a href=\"$Url\" target=\"_blank\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\"> $Linha[1]</a> - ";
                                                                printf("%01.1f", $tamanho);
                                                                echo " k <br>";
                                                            } else {
                                                                echo "<img src=\"../midia/disquete.gif\" border=\"0\"> $Linha[1] - <b>Arquivo não armazenado</b>";
                                                            }
                                                            if ($Linha[2] != "") {
                                                                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Obs.: $Linha[2]";
                                                            }
                                                            echo "<br>\n";
                                                        }
                                                    } else {
                                                        echo "<font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n";
                                                    }
                                } ?>
								</td>
							</tr>
							<?php

                            }

                  ?>
							<tr>
								</form>
								<form method="post" action="ConsRegistroPrecoResultado.php">
	    	      	<td class="textonormal" colspan="4" align="right">
	    	      		<input type="hidden" name="ComissaoCodigo" value="<?=$ComissaoCodigo;?>">
	    	      		<input type="hidden" name="OrgaoLicitanteCodigo" value="<?=$OrgaoLicitanteCodigo;?>">
	    	      		<input type="hidden" name="ModalidadeCodigo" value="<?=$ModalidadeCodigo;?>">
	    	      		<input type="hidden" name="GrupoCodigo" value="<?=$GrupoCodigo;?>">
	    	      		<input type="hidden" name="Objeto" value="<?=$Objeto;?>">
	          	  	<?php/*<input type="submit" name="Voltar" value="Voltar" class="botao">*/?>
	          	  	<input type="button" name="Voltar" value="Voltar" class="botao" onClick="history.go(-1);return true;" />
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
