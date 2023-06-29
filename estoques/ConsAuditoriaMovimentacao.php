<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAuditoriaMovimentacao.php
# Autor:    Álvaro Faria
# Data:     15/02/2006
# Objetivo: Programa de checagem
#------------------------------------
# Alterado: Rossana Lira/Rodrigo Melo
# Data:     07/11/2007 - Acréscimo das movimentações 32, 33 e 34. Toda vez que for criada
#           uma nova movimentação incluir nos arrays abaixo de acordo com o tipo de movmentação
#           (entrada ou saída)
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
#------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();

Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/ConsAuditoriaMovimentacao.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Almoxarifado    	= $_POST['Almoxarifado'];
		$Material 	    	= $_POST['Material'];
		$Botao            = $_POST['Botao'];
}

if( $Botao == "Limpar" ){
		header("location: ConsAuditoriaMovimentacao.php");
		exit;
}

?>

<?php
# Carrega o layout padrão #
layout();
?>

<script language="javascript" type="">
<!--
function enviar(valor){
	document.ConsAuditoriaMovimentacao.Botao.value=valor;
	document.ConsAuditoriaMovimentacao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsAuditoriaMovimentacao.php" method="post" name="ConsAuditoriaMovimentacao">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Auditoria
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan=2>
	           AUDITORIA DE MOVIMENTAÇÕES
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan=2>
             <p align="justify">
               Este programa visa checar se as movimentações de material estão coerentes com o estoque.
             </p>
          </td>
        </tr>
				<tr>
          <td class="textonormal" bgcolor="#DCEDF7">Almoxarifado</td>
          <td class="textonormal">
          	<?php
        		# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
						$db   = Conexao();
						# Se o usuário logado for o ADMIN, não busca pelo Órgão
						if( ($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){
          			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A";
								if ($Almoxarifado) {
										$sql   .= " WHERE A.FALMPOSITU = 'A'";
								}
						} else {
          			$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
								$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
								$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
								if ($Almoxarifado) {
										$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
								}
								$sql .= "   AND B.CORGLICODI = ";
					    	$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
					    	$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
						    $sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')) ";
            }
						$sql .= " ORDER BY A.EALMPODESC ";
        		$res  = $db->query($sql);
						if( PEAR::isError($res) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Rows = $res->numRows();
								if( $Rows > 1 ){
										echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
	                  echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
	                  if ($Almoxarifado == "T") {
	                  		echo "	<option value=\"T\" selected>Todos os Almoxarifados</option>\n";
	                  }else{
	                			echo "	<option value=\"T\">Todos os Almoxarifados</option>\n";
	                	}
										for( $i=0;$i< $Rows; $i++ ){
												$Linha = $res->fetchRow();
												$DescAlmoxarifado = $Linha[1];
      	   	      			if( $Linha[0] == $Almoxarifado ){
      	   	      					echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado ($Linha[0])</option>\n";
          	      			}else{
          	      					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado ($Linha[0])</option>\n";
          	      			}
	                	}
	                	echo "</select>\n";
	                	$CarregaAlmoxarifado = "";
	              }else{
	            			echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
        	   	  		echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
	            	}
            }
     			 	$db->disconnect();
            ?>
          </td>
			  </tr>

				<tr>
          <td class="textonormal" bgcolor="#DCEDF7">Material</td>
          <td class="textonormal">
          	<input type="text" name="Material" size=10>
          	<a href="javascript:enviar();"><img src="../midia/lupa.gif" border="0"></a><br>
          </td>
			  </tr>

<?php
if ($Almoxarifado) {

		echo "<tr>\n";
    echo " <td colspan=2>\n";
    echo "  <table border=0>\n";

		$Entrada = array(1,2,3,5,6,7,9,10,11,18,19,21,26,28,29,31,32,33);
		$Saida   = array(4,8,12,13,14,15,16,17,20,22,23,24,25,27,30,34,37);

		# Traz todos os almoxarifados #
		$db = Conexao();
		$sqlalm  = "SELECT CALMPOCODI, EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sqlalm .= " WHERE FALMPOSITU = 'A' ";
		if ($Almoxarifado != 'T') $sqlalm .= "   AND CALMPOCODI = $Almoxarifado ";
		$sqlalm .= " ORDER BY EALMPODESC ";
		$resalm  = $db->query($sqlalm);
		if( PEAR::isError($resalm) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlalm");
		}else{
				$Rowsalm = $resalm->numRows();
				for($i=0;$i<$Rowsalm;$i++){
						$Linhaalm     = $resalm->fetchRow();
						$AlmoxarifadoSelect = $Linhaalm[0];
						$AlmoxDesc    = $Linhaalm[1];
						echo "<tr><td align=\"center\" class=\"textonormal\" colspan=\"3\">$AlmoxDesc (Código: $AlmoxarifadoSelect)</td></tr>\n\n";
						# Traz todas as movimentações do almoxarifado em curso no loop #
						?>
		<tr>
			<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" width="4%" height="18">Material</td>
		  <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" width="10%" height="18">Estoque</td>
		  <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" width="86%" height="18">Movimentações</td>
		</tr>
						<?php
						$sqlmov  = "SELECT A.AMOVMAANOM, A.CMOVMACODI, A.CTIPMVCODI, A.AMOVMAQTDM, "; // Geral
						$sqlmov .= "       B.ETIPMVDESC, A.DMOVMAMOVI, A.CMOVMACODT, A.TMOVMAULAT, "; // Movimentação
						$sqlmov .= "       A.AENTNFANOE, A.CENTNFCODI, E.AENTNFNOTA, E.AENTNFSERI, "; // Nota Fiscal
						$sqlmov .= "       D.CREQMASEQU, D.CREQMACODI, D.AREQMAANOR, ";               // Requisição
						$sqlmov .= "       A.CMATEPSEQU, C.EMATEPDESC, F.AARMATQTDE  ";               // Material
						$sqlmov .= "  FROM SFPC.TBLOCALIZACAOMATERIAL G, ";
						$sqlmov .= "       SFPC.TBARMAZENAMENTOMATERIAL F, ";
						$sqlmov .= "       SFPC.TBMATERIALPORTAL C, ";
						$sqlmov .= "       SFPC.TBTIPOMOVIMENTACAO B, ";
						$sqlmov .= "       SFPC.TBMOVIMENTACAOMATERIAL A ";
						$sqlmov .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL  E ON (A.CALMPOCODI = E.CALMPOCODI AND A.AENTNFANOE = E.AENTNFANOE AND A.CENTNFCODI = E.CENTNFCODI) ";
						$sqlmov .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL D ON (A.CREQMASEQU = D.CREQMASEQU) ";
						$sqlmov .= " WHERE A.CALMPOCODI = $AlmoxarifadoSelect ";
						$sqlmov .= "   AND A.CTIPMVCODI = B.CTIPMVCODI ";
						$sqlmov .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";                          // Apresentar só as movimentações ativas
						$sqlmov .= "   AND A.CMATEPSEQU = C.CMATEPSEQU ";
						$sqlmov .= "   AND A.CMATEPSEQU = F.CMATEPSEQU ";
						$sqlmov .= "   AND F.CLOCMACODI = G.CLOCMACODI ";
						$sqlmov .= "   AND G.CALMPOCODI = A.CALMPOCODI ";
						if ($Material) {$sqlmov .= "   AND A.CMATEPSEQU = $Material ";}
						$sqlmov .= " ORDER BY A.CMATEPSEQU, A.TMOVMAULAT ";
						$resmov  = $db->query($sqlmov);
						if( PEAR::isError($resmov) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
						}else{
								$Rowsmov = $resmov->numRows();
								if ($Rowsmov > 0) {
		               for($j=0;$j<=$Rowsmov;$j++){
												$Linhamov         = $resmov->fetchRow();
												# Geral
												$AnoMovimentacao  = $Linhamov[0];
												$MovimentacaoCod  = $Linhamov[1];
												$TipoMovimentacao = $Linhamov[2];
												$Quantidade       = $Linhamov[3];
												# Movimentação pura
												$DescMovimentacao = $Linhamov[4];
												$Data		          = databarra($Linhamov[5]);
												$NumeroDaMov      = $Linhamov[6];
												$TimeStamp    		= substr($Linhamov[7],0,19);
												# Nota Fiscal
												$NotaAno      		= $Linhamov[8];
												$NotaCodigo   		= $Linhamov[9];
												$NotaNumero   		= $Linhamov[10];
												$NotaSerie    		= $Linhamov[11];
												# Requisição
												$RequisicaoSeq    = $Linhamov[12];
												$Requisicao	      = $Linhamov[13];
												$AnoRequisicao    = $Linhamov[14];
												$Material         = $Linhamov[15];
												$MaterialDesc     = $Linhamov[16];
												$EstoqueAtual     = $Linhamov[17];

												if ($Material != $MaterialUlt) {
														$MaterialUlt = $Material;
														if ($entrou) {
																if (round($EstoqueAnterior,4) == round($Somatorio,4)) {
																		if ($Somatorio >= 0) {
																				echo " = <font color=green>".converte_valor(sprintf("%01.2f",str_replace(",",".",$Somatorio)))."</font>";
																		}else{
																				echo " = <font color=green>".str_replace('.',',',sprintf("%01.2f",str_replace(",",".",$Somatorio)))."</font>";
																		}
																}else{
																		if ($Somatorio >= 0) {
																				echo " = <font color=red>".converte_valor(sprintf("%01.2f",str_replace(",",".",$Somatorio)))."</font>";
																		}else{
																				echo " = <font color=red>".str_replace('.',',',sprintf("%01.2f",str_replace(",",".",$Somatorio)))."</font>";
																		}
																		if ($Problema) {
																				$Problema .= "<BR><a href=\"#$AlmoxarifadoSelect-$MaterialAnterior\"><font color=#000000>$MaterialAnterior (".converte_valor(sprintf("%01.2f",str_replace(",",".",$EstoqueAnterior)))." <> ".str_replace('.',',',sprintf("%01.2f",str_replace(",",".",$Somatorio))).")</font></a>";
																		}else{
																				$Problema = "<a href=\"#$AlmoxarifadoSelect-$MaterialAnterior\"><font color=#000000>$MaterialAnterior (".converte_valor(sprintf("%01.2f",str_replace(",",".",$EstoqueAnterior)))." <> ".str_replace('.',',',sprintf("%01.2f",str_replace(",",".",$Somatorio))).")</font></a>";
																		}
																}
																echo "</td>\n";
																echo "</tr>\n\n";
														}

														if(in_array($TipoMovimentacao,$Entrada)){
																$Somatorio = $Quantidade;
														}else{
																$Somatorio = 0 - $Quantidade;
														}

														$entrou = 1;
														echo "<tr bgcolor=\"#B5EDFF\">\n";
														if ($Material) echo "	<td align=\"right\" class=\"textonormal\"><a name=\"$AlmoxarifadoSelect-$Material\">$Material <img src=../midia/ponto.gif alt=\"$MaterialDesc\" title=\"$MaterialDesc\"></a></td>\n";
														if ($Material) echo "	<td align=\"right\" class=\"textonormal\"><font color=green>".converte_valor(sprintf("%01.2f",str_replace(",",".",$EstoqueAtual)))."</font></td>\n";
														$EstoqueAnterior  = $EstoqueAtual;
														$MaterialAnterior = $Material;
														if ($Material) {
																if(in_array($TipoMovimentacao,$Entrada)){
																		echo "<td align=\"right\" class=\"textonormal\"><img src=../midia/mais.gif alt=\"$DescMovimentacao - Em $TimeStamp\" title=\"$DescMovimentacao - Em $TimeStamp\"> ".converte_valor(sprintf("%01.2f",str_replace(",",".",$Quantidade)));
																}else{
																		echo "<td align=\"right\" class=\"textonormal\"><img src=../midia/menos.gif alt=\"$DescMovimentacao - Em $TimeStamp\" title=\"$DescMovimentacao - Em $TimeStamp\"> ".converte_valor(sprintf("%01.2f",str_replace(",",".",$Quantidade)));
																}
														}
												} else {
														if(in_array($TipoMovimentacao,$Entrada)){
																echo " <img src=../midia/mais.gif alt=\"$DescMovimentacao - Em $TimeStamp\" title=\"$DescMovimentacao - Em $TimeStamp\"> ".converte_valor(sprintf("%01.2f",str_replace(",",".",$Quantidade)));
																if ($TipoMovimentacao == 5) { // Se for inventário, reinicia cálculo, ignorando carga inicial
																		$Somatorio = $Quantidade;
																}else{
																		$Somatorio = $Somatorio + $Quantidade;
																}
														}else{
																echo " <img src=../midia/menos.gif alt=\"$DescMovimentacao - Em $TimeStamp\" title=\"$DescMovimentacao - Em $TimeStamp\"> ".converte_valor(sprintf("%01.2f",str_replace(",",".",$Quantidade)));
																if ($TipoMovimentacao == 5) { // Se for inventário, reinicia cálculo, ignorando carga inicial
																		$Somatorio = $Quantidade;
																}else{
																		$Somatorio = $Somatorio - $Quantidade;
																}
														}
												}
										}
								}
						}
						if ($Problema) {
								echo "<tr><td class=\"textonormal\" colspan=\"3\">Problemas no $AlmoxDesc (Código: $AlmoxarifadoSelect):";
								echo "<BR>$Problema.";
								echo "</td></tr>\n";
								echo "<tr><td colspan=3><hr></td></tr>\n\n";
						}else{
								echo "<tr><td class=\"textonormal\" colspan=\"3\">Nenhum problema no $AlmoxDesc (Código: $AlmoxarifadoSelect).";
								echo "</td></tr>\n";
								echo "<tr><td colspan=3><hr></td></tr>\n\n";
						}
						$Problema = null;
						$entrou   = null;
				}
		}
		$db->disconnect();

echo "								</td>";
echo "              </tr>";
echo "            </table>";
echo "          </td>";
echo "        </tr>";
}
?>
        <tr>
          <td align="right" colspan=2>
          	<input type="button" value="Limpar"  class="botao" onclick="javascript:enviar('Limpar')">
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
