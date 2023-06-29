<?php
# ---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompLicitacaoResultado.php
# Autor:    Roberta Costa
# Data:     20/08/03
# Objetivo: Programa de Resultado do Relatório de Acompanhamento das
#						Licitações que já foram realizadas (data de abertura inferior
#           à data atual)
# OBS.:     Tabulação 2 espaços
# ---------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     19/09/2007 - Acrescido campo Valor
# ---------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelAcompLicitacaoImpressao.php' );
AddMenuAcesso( '/licitacoes/RelAcompLicitacaoPesquisar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica          = $_POST['Critica'];
		$Botao            = $_POST['Botao'];
		$GrupoCodigo      = $_POST['GrupoCodigo'];
		$ComissaoCodigo   = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo = $_POST['ModalidadeCodigo'];
		$Fase							= $_POST['Fase'];
}else{
		$GrupoCodigo      = $_GET['GrupoCodigo'];
		$ComissaoCodigo   = $_GET['ComissaoCodigo'];
		$ModalidadeCodigo = $_GET['ModalidadeCodigo'];
		$Fase             = $_GET['Fase'];
		$Ano              = $_GET['Ano'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelAcompLicitacaoResultado.php";

# Redireciona dados para RelAcompLicitacaoPesquisar.php #
if( $Botao == "Pesquisa" ){
  	header("location: RelAcompLicitacaoPesquisar.php");
  	exit();
}

$Mens = 0;
if( $Mens == 0 ) {
		$db   = Conexao();
		$sql  = "SELECT c.EGREMPDESC, e.EMODLIDESC, d.ECOMLIDESC, a.CLICPOPROC, a.ALICPOANOP, ";
		$sql .= "       a.CLICPOCODL, a.ALICPOANOL, a.XLICPOOBJE, a.TLICPODHAB, b.EORGLIDESC, ";
		$sql .= "       a.CGREMPCODI, a.CCOMLICODI, a.CORGLICODI, f.EFASELDETA, g.EFASESDESC, ";
		$sql .= "       CASE WHEN G.CFASESCODI <> 13 THEN A.VLICPOVALE ELSE A.VLICPOVALH END ";
		$sql .= "  FROM SFPC.TBLICITACAOPORTAL a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c, SFPC.TBCOMISSAOLICITACAO d, SFPC.TBMODALIDADELICITACAO e, ";
		$sql .= "       SFPC.TBFASELICITACAO f, SFPC.TBFASES g, ";
		$sql .= "       ( SELECT l.CLICPOPROC as Proc, l.ALICPOANOP as Ano, l.CGREMPCODI as Grupo, ";
		$sql .= "                l.CCOMLICODI as Comis, l.CORGLICODI as Orgao, MAX(o.AFASESORDE) as Maior ";
		$sql .= "           FROM SFPC.TBFASELICITACAO l, SFPC.TBFASES o ";
		$sql .= "          WHERE l.CFASESCODI = o.CFASESCODI ";
		$sql .= "          GROUP BY l.CLICPOPROC, l.ALICPOANOP, l.CGREMPCODI, l.CCOMLICODI, l.CORGLICODI ";
		$sql .= "       ) as om ";
		$sql .= " WHERE a.CORGLICODI = b.CORGLICODI AND a.CGREMPCODI = c.CGREMPCODI ";
		$sql .= "   AND a.CCOMLICODI = d.CCOMLICODI AND a.CMODLICODI = e.CMODLICODI ";
		$sql .= "   AND a.CLICPOPROC = f.CLICPOPROC AND a.ALICPOANOP = f.ALICPOANOP ";
		$sql .= "   AND a.CGREMPCODI = f.CGREMPCODI AND a.CCOMLICODI = f.CCOMLICODI ";
		$sql .= "   AND a.CORGLICODI = f.CORGLICODI AND f.CFASESCODI = g.CFASESCODI ";
		$sql .= "   AND a.CLICPOPROC = om.Proc AND a.ALICPOANOP  = om.Ano ";
		$sql .= "   AND a.CGREMPCODI = om.Grupo AND a.CCOMLICODI = om.Comis ";
		$sql .= "   AND a.CORGLICODI = om.Orgao AND g.AFASESORDE = om.Maior ";

		# Opção sem todas fases ou com a fase de Adjudicacao 99 - Processos em andamento #
		if( $Fase == 2 ){
				$sql  .= " AND g.AFASESORDE = 99 ";
		}elseif( $Fase == 1 ){
				$sql  .= " AND g.AFASESORDE < 99 ";
		}

		if( $ModalidadeCodigo != "" ) {
			  $sql .= " AND a.CMODLICODI = $ModalidadeCodigo ";
		}

		if( $ComissaoCodigo != "" ){ $sql .= " AND a.CCOMLICODI = $ComissaoCodigo "; }
		if( $ModalidadeCodigo != "" ){ $sql .= " AND a.CMODLICODI = $ModalidadeCodigo "; }
		if( $GrupoCodigo != "" ){ $sql .= " AND a.CGREMPCODI = $GrupoCodigo "; }
		$DataIni  = $Ano."-01-01 00:00:00";
		$DataFim  = $Ano."-12-31 23:59:59";
		if( $Ano != "" ){ $sql .= " AND a.TLICPODHAB >= '$DataIni' AND a.TLICPODHAB <= '$DataFim' "; }

		$sql .= "   ORDER BY c.EGREMPDESC, d.ECOMLIDESC, e.EMODLIDESC, a.ALICPOANOP, a.CLICPOPROC  ";

		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
		}
		$GrupoDescricao = "";
		if( $Rows != 0){ ?>
				<html>
				<?
				# Carrega o layout padrão #
				layout();
				?>
				<script language="javascript" type="">
				<!--
				function enviar(valor){
					document.Acomp.Botao.value=valor;
					document.Acomp.submit();
				}
				function janela( pageToLoad, winName, width, height, center) {
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
				<?php MenuAcesso(); ?>
				//-->
				</script>
				<link rel="stylesheet" type="text/css" href="../estilo.css">
				<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
				<script language="JavaScript" src="../menu.js"></script>
				<script language="JavaScript">Init();</script>
					<form action="RelAcompLicitacaoResultado.php" method="post" name="Acomp">
						<br><br><br><br>
						<table cellpadding="3" border="0" summary="">
					  <!-- Caminho -->
					  <tr>
					    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
					    <td align="left" class="textonormal" colspan="2"><br>
					      <font class="titulo2">|</font>
					      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatório > Acompanhamento das Licitações
					    </td>
					  </tr>
					  <!-- Fim do Caminho-->

						<!-- Erro -->
						<?php if ( $Mens == 1 ) { ?>
						<tr>
					  	<td width="100"></td>
					  	<td align="left" colspan="2">
								<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
				    	</td>
						</tr>
						<?php } ?>
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
					        	    	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="6" class="titulo3">
						    						RELATÓRIO DE ACOMPANHAMENTO DE LICITAÇÕES - RESULTADO
						          		</td>
						        		</tr>
					          		<tr>
						            	<td colspan="6" class="textonormal">
						        	    	Para emitir o relatório, clique no botão "Imprimir". Para executar uma nova pesquisa, clique no botão "Nova Pesquisa".
							          	</td>
							        	</tr>
						          	<tr>
						    	      	<td colspan="6" class="textonormal" align="right">
														<input type="hidden" name="ModalidadeCodigo" value= "<?php echo $ModalidadeCodigo; ?>">
    	        	  				  <input type="hidden" name="ComissaoCodigo" value= "<?php echo $ComissaoCodigo; ?>">
    	          					  <input type="hidden" name="GrupoCodigo" value= "<?php echo $GrupoCodigo; ?>">
    	          				  	<input type="hidden" name="Fase" value= "<?php echo $Fase; ?>">
    	          				  	<?
    	          				  	$url = "RelAcompLicitacaoImpressao.php?GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&Fase=$Fase&Ano=$Ano";
    	          				  	if (!in_array($url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $url; }
    	          				  	?>
					         				  <input type="button" value="Imprimir" class="botao" onclick="javascript :janela('<?php echo $url; ?>','PortalCompras',700,300,1)">
	                					<input type="button" name="Pesquisa" value="Nova Pesquisa" class="botao" onclick="javascript:enviar('Pesquisa');">
	  			                	<input type="hidden" name="Botao" value="">
				          				</td>
						        		</tr>
						        		<?
												while( $Linha = $result->fetchRow() ){
														 if( $GrupoDescricao != $Linha[0] ){
																 $GrupoDescricao = $Linha[0];
																 echo "<tr>\n";
																 echo "	<td align=\"center\" class=\"titulo3\" colspan=\"6\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td>\n";
																 echo "</tr>\n";
															 	 $ComissaoDescricao = "";
															 	 $ExibeCabecalho    = "S";
															}
															if( $ComissaoDescricao != $Linha[2] ){
																 	$ComissaoDescricao = $Linha[2];
															  	echo "<tr>\n";
															  	echo "	<td align=\"center\" class=\"titulo2\" colspan=\"6\">$ComissaoDescricao</td>\n";
															  	echo "</tr>\n";
															    $ModalidadeDescricao = "";
															    $ExibeCabecalho      = "S";
															}
															if( $ModalidadeDescricao != $Linha[1] ){
																	$ModalidadeDescricao = $Linha[1];
																	echo "<tr>\n";
																	echo "	<td align=\"center\" class=\"titulo3\" colspan=\"6\">$ModalidadeDescricao</td>\n";
																	echo "</tr>\n";
																	$ExibeCabecalho = "S";
															}
															if( $ExibeCabecalho == "S" ){
																	echo "<tr>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA<br>ABERTURA</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">FASE</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">VALOR</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">SITUAÇÃO</td>\n";
																	echo "</tr>\n";
																	$ExibeCabecalho = "N";
															}
															$NProcesso             = substr($Linha[3] + 10000,1);
															$NLicitacao            = substr($Linha[5] + 10000,1);
															$LicitacaoDtAbertura   = substr($Linha[8],8,2) ."/". substr($Linha[8],5,2) ."/". substr($Linha[8],0,4);
															$LicitacaoHoraAbertura = substr($Linha[8],11,5);
															echo "<tr>\n";
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\">$NProcesso/$Linha[4]<br><br><font class=\"textonegrito\">LICITAÇÃO </font>".$NLicitacao."/".$Linha[6]."</td>\n";
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\">$Linha[7]</td>\n";
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</td>\n";
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\">$Linha[14]</td>\n";
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\" align=\"right\">".converte_valor($Linha[15])."</td>\n";
															if( $Linha[13] == "" ){ $Linha[13]= "-"; }
															echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"20%\">$Linha[13]</td>\n";
															echo "</tr>\n";
															$OrgaoLicitante = $Linha[9];
															$Modalidade     = $Linha[1];
												}
										}else{
												# Envia mensagem para página selecionar #
												$Mensagem = urlencode("Nenhuma ocorrência foi encontrada");
												$Url = "RelAcompLicitacaoPesquisar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit();
										}
										?>
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
<?
	$db->disconnect();
}
?>
