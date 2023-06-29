
<?php
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     01/02/2019
# Objetivo: Tarefa Redmine 209921
# ------------------------------------------------------------------------------

?>

<html>
<?php
# Carrega o layout padrão
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function ncaracteres(valor){
	document.FaseLicitacao.NCaracteres.value = document.FaseLicitacao.FaseLicitacaoDetalhe.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.FaseLicitacao.NCaracteres.focus();
	}
}
function enviar(valor){
    if(valor == "Confirmado") {
        var fase = document.FaseLicitacao.FaseCodigoDesc_aux.value;
        if(fase == "2_PUBLICAÇÃO") {
            document.getElementById('aguarde').style.display = "table";
        }
    }

	document.FaseLicitacao.Botao.value=valor;
	document.FaseLicitacao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadFaseLicitacaoIncluir.php" method="post" name="FaseLicitacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
    <!-- Caminho -->
    <tr>
        <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
        <td align="left" class="textonormal" colspan="2">
            <font class="titulo2">|</font>
            <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Fase Licitação > Incluir
        </td>
    </tr>
    <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
        <td width="100"></td>
	    <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
        <td width="100"></td>
		<td class="textonormal">
            <table  border="0" cellspacing="0" cellpadding="3" summary="">
                <tr>
	      	        <td class="textonormal">
	        	        <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
                            <tr>
                                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                    INCLUIR - FASE LICITAÇÃO
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal">
                                    <p align="justify">
                                        Para incluir uma nova Fase de Licitação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.<br>
                                        <?php if( $FaseCodigo == 13 ){ ?>
                                        O Total Geral Estimado (itens que lograram êxito) é obtido através do somatório do produto do preço unitário dos itens que lograram êxito pelo seus respectivos quantitativos.
                                        <?php } ?>
                                    </p>
	          		            </td>
		        	        </tr>
		        	        <tr>
	  	        	            <td>
	    	      		            <table class="textonormal" border="0" width="100%" summary="">
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7" width="30%">Processo* </td>
                                            <td class="textonormal">
                                                <select name="ProcessoAnoComissaoOrgao_aux" class="textonormal" disabled   >
                                                    <option value="">Selecione um Processo Licitatório...</option>
                                                    <?php
                                                        # Mostra as licitações cadastradas  #
                                                        $db     = Conexao();
                                                        $sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, ";
                                                        $sql   .= "       C.EGREMPDESC, A.CORGLICODI, A.CMODLICODI, A.FLICPOREGP ";
                                                        $sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, ";
                                                        $sql   .= "       SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D ";
                                                        $sql   .= "  WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
                                                        $sql   .= "    AND D.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
                                                        $sql   .= "    AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
                                                        $sql   .= "  ORDER BY B.ECOMLIDESC ASC, A.CGREMPCODI ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
                                                        $result = $db->query($sql);
                                                        if( PEAR::isError($result) ){
                                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                        } else {
                                                            $ComissaoCodigoAnt = "";
                                                            while( $Linha = $result->fetchRow() ){
                                                                    if( $Linha[2] != $ComissaoCodigoAnt ){
                                                                        $ComissaoCodigoAnt = $Linha[2];
                                                                        echo "<option value=\"\">$Linha[3]</option>\n" ;
                                                                    }
                                                                    $NProcesso = substr($Linha[0] + 10000,1);
                                                                    if( $ProcessoAnoComissaoOrgao == "$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]_$Linha[7]" ){
                                                                        echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]_$Linha[7]\" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n";
                                                                    }else{
                                                                        echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]_$Linha[7]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n";
                                                                    }
                                                            }
                                                        }
                                                    ?>
			                                    </select>
											</td>
										</tr>
										<tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Fase* </td>
                                            <td class="textonormal">
												<select name="FaseCodigoDesc_aux" class="textonormal" onChange="javascript:enviar('Fase'); " disabled   >
                                                    <option value="">Selecione uma Fase...</option>
                                                    <?php
                                                    $sql    = "SELECT CFASESCODI,EFASESDESC,AFASESORDE FROM SFPC.TBFASES ORDER BY AFASESORDE";
                                                    $result = $db->query($sql);
                                                    if( PEAR::isError($result) ){
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                    } else {
                                                        while( $Linha = $result->fetchRow() ){
                                                            if( "$Linha[0]_$Linha[1]" == $FaseCodigoDesc ){
                                                                echo "<option value=\"$Linha[0]_$Linha[1]\" selected>$Linha[1]</option>\n" ;
                                                            }else{
                                                                echo "<option value=\"$Linha[0]_$Linha[1]\">$Linha[1]</option>\n" ;
                                                            }
                                                        }
                                                    }
                                                    $db->disconnect();
                                                    ?>
												</select>
											</td>
										</tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Data da Fase* </td>
                                            <td class="textonormal">
                                                <?php //$URL = "../calendario.php?Formulario=FaseLicitacao&Campo=DataFase";?>
												<input type="text" name="DataFase" size="10" maxlength="10" value="<?php echo $DataFase ?>" class="textonormal"   readonly   >
												<!--  <a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> -->
												<font class="textonormal">dd/mm/aaaa</font>
					      			        </td>
				                        </tr>
				           	            <tr>
                                            <?php if ( $FaseCodigo=="11" || $FaseCodigo=="12" || $FaseCodigo=="17" ) { ?>
                                            <td class="textonormal" bgcolor="#DCEDF7">Detalhe*</td>
                                            <?php } else { ?>
                                            <td class="textonormal" bgcolor="#DCEDF7">Detalhe</td>
                                            <?php } ?> 				              <td class="textonormal">
                                                <font class="textonormal">máximo de 1000 caracteres</font>
                                                <input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres; ?>" class="textonormal"  readonly   ><br>
                                                                <textarea name="FaseLicitacaoDetalhe" cols="45" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(0)" class="textonormal"  readonly  ><?php echo $FaseLicitacaoDetalhe ?></textarea>
                                            </td>
                                        </tr>
				                        <?php if( $FaseCodigo == 13 ){ ?>
                                        <tr>
			                                <td class="textonormal" bgcolor="#DCEDF7">Total Geral Estimado*<br>(Itens que Lograram Êxito)</td>
				                            <td class="textonormal">
				                                <input type="text" name="TotalGeralEstimado" size="17" maxlength="16" value="<?php echo $TotalGeralEstimado; ?>  " class="textonormal"  readonly   >
						                    </td>
				                        </tr>
				           	            <tr>
				                            <td class="textonormal" bgcolor="#DCEDF7">Valor Homologado*<br>(Itens que Lograram Êxito)</td>
				                            <td class="textonormal">
				                                <input type="text" name="ValorHomologado" size="17" maxlength="16" value="<?php echo $ValorHomologado; ?>" class="textonormal"  readonly  >
						                    </td>
				                        </tr>
					                    <?php } ?>
	      	      	                </table>
		          	            </td>
		        	        </tr>
                            <tr id="aguarde" style="display: none; width: 100%;">
                                <td style="font-size: 12pt; font-weight: bold; color: #75ade6">Aguarde...</td>
                            </tr>
	  	      	            <tr>
   	  	  			            <td class="textonormal" align="right">
						            <input type="hidden" name="Critica" value="1">
                                    <input type="hidden" name="ProcessoAnoComissaoOrgao" value="<?php echo $ProcessoAnoComissaoOrgao?>">
                                    <input type="hidden" name="FaseCodigoDesc" value="<?php echo $FaseCodigoDesc?>">
                                    <input type="hidden" name="Processo" value="<?php echo $Processo?>">
                                    <input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno?>">
                                    <input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo?>">
                                    <input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo?>">
                                    <input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo; ?>">
                                    <input type="hidden" name="RegistroPreco" value="<?php echo $RegistroPreco; ?>">
                                    <input type="hidden" name="QtdBloqueios" value="<?php echo $QtdBloqueios; ?>">
                                    <input type="hidden" name="Homologacao" value="<?php echo $Homologacao; ?>">
                                    <input type="hidden" name="FaseCodigo" value="<?php echo $FaseCodigo; ?>">
                                    <input type="button" value="Confirmar Inclusão" class="botao" onclick="javascript:enviar('Confirmado')">
                                    <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
<script language="javascript" type="">
<!--
document.FaseLicitacao.ProcessoAnoComissaoOrgao.focus();
if( document.FaseLicitacao.Homologacao.value == 'S' && document.FaseLicitacao.FaseCodigo.value == 13 ){
	<?php
	$Url = "CadFaseLicitacaoConfirmar.php?ProgramaOrigem=FaseLicitacao&ValorHomologado=$ValorHomologado&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	?>
	window.open('<?=$Url;?>','pagina','status=no,scrollbars=no,left=270,top=150,width=375,height=220');
}
//-->
</script>
