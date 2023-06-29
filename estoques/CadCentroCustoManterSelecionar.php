<?php
#---------------------------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadCentroCustoManterSelecionar.php
# Autor:		Marcus Thiago
# Data:     19/01/2006
# Objetivo: Programa que seleciona o Centro de Custo para determinar a situação e/ou excluí-lo
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadIncluirCentroCusto.php' );
AddMenuAcesso( '/estoques/CadCentroCustoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao       = $_POST['Botao'];
		$CentroCusto = $_POST['CentroCusto'];
		$Todos       = $_POST['Todos'];
		$Orgao       = $_POST['Orgao'];
		$Descricao   = strtoupper2(trim($_POST['Descricao']));
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
    header("location: CadCentroCustoManterSelecionar.php");
    exit;
}elseif( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $CentroCusto == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadCentroCustoManterSelecionar.CentroCusto.focus();\" class=\"titulo2\">Centro de Custo</a>";
    }else{
    		$Url = "CadCentroCustoManter.php?CentroCusto=$CentroCusto";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit;
    }
}elseif( $Botao == "Pesquisar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $Descricao == "" and $Todos == "" and $Orgao == "" ){
    	  if( $Mens == 1 ){ $Mensagem .= ", "; }
      	$Mens      = 1;
      	$Tipo      = 2;
      	$Mensagem .= "<a href=\"javascript:document.CadCentroCustoManterSelecionar.Descricao.focus();\" class=\"titulo2\">Descrição do Centro de Custo</a>";
    }
}

# Pega a descrição do Perfil do usuário logado #
if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
		$db  = Conexao();
		$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
		$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
		$resultUsuario = $db->query($sqlusuario);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: 239\nSql: $sqlusuario");
		}else{
    		$PerfilUsuario = $resultUsuario->fetchRow();
    		$PerfilUsuarioDesc = $PerfilUsuario[1];
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
	document.CadCentroCustoManterSelecionar.Botao.value = valor;
	document.CadCentroCustoManterSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoManterSelecionar.php" method="post" name="CadCentroCustoManterSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Centro de Custo > Manter
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
	        		SELECIONAR - CENTRO DE CUSTO - MANTER
          </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="4">
            <p align="justify">
							Para selecionar um Centro de Custo selecione o item da lista e clique no botão "Selecionar".
							Para pesquisar um Centro de Custo preencha o argumento da pesquisa e clique na figura da Lupa.
							Depois, clique no Centro de Custo desejado que está marcado com um "*".<br>
							Para limpar a pesquisa clique no botão "Limpar".
            </p>
          </td>
        </tr>
      	<tr>
					<td colspan="4">
						<table border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
      				<tr>
        				<td class="textonormal" bgcolor="#DCEDF7" width="31%">Descrição Centro de Custo</td>
        				<td class="textonormal" colspan="2">
 	        				<input type="text" name="Descricao" size="30" maxlength="30" class="textonormal" value="<?php echo $Descricao; ?>">
           	      <a href="javascript:enviar('Pesquisar');"><img src="../midia/lupa.gif" border="0"></a>
           	      <input type="checkbox" name="Todos" value="1" onClick="javascript:enviar('Pesquisar');"> Todos
        				</td>
        			</tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Centro de Custo</td>
                <td class="textonormal">
 									<select name="CentroCusto" class="textonormal">
										<option value="">Selecione um Centro de Custo...</option>
										<?php
										$db = Conexao();
										if ($_SESSION['_cgrempcodi_'] == 0)  {
												$sqlsel  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
												$sqlsel .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI, B.EORGLIDESC  ";
												$sqlsel .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
												$sqlsel .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")." ";
												$sqlsel .= "   AND A.CORGLICODI = B.CORGLICODI ";
												$sqlsel .= " ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
										}else{
												$sqlsel  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
												$sqlsel .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI ";
												$sqlsel .= "       FROM SFPC.TBCENTROCUSTOPORTAL A";
												$sqlsel .= " WHERE A.CORGLICODI = ";
												$sqlsel .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
												$sqlsel .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
												$sqlsel .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
												$sqlsel .= " ORDER BY A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
										}
										$ressel = $db->query($sqlsel);
										print_r($ressel);
										if (PEAR::isError($ressel)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsel");
										}else{
												$UnidadeAntes = "";
												$CentroAntes  = "";
												$RPAAntes     = "";
												while( $Sel = $ressel->fetchRow() ){
														$Cont++;
														$Centro    = $Sel[3];
														$RPA       = $Sel[4];
														$Detalhe   = $Sel[5];
														if ($_SESSION['_cgrempcodi_'] == 0) {
																$Unidade = $Sel[7];
														}

														# Pega o nome da Unidade Orçamentária de acordo com o Órgão #
														if( $_SESSION['_cgrempcodi_'] != 0 and $Cont == 1 ){
																$sqluni  = "SELECT B.EORGLIDESC ";
																$sqluni .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																$sqluni .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CORGLICODI = $Sel[6]";
																$resuni  = $db->query($sqluni);
																if (PEAR::isError($resuni)) {
																    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqluni");
																    exit;
																}else{
																		$Uni     = $resuni->fetchRow();
																		$Unidade = $Uni[0];
																}
														}
														$Edentacao = "&nbsp;&nbsp;&nbsp;";
														if( $UnidadeAntes != $Unidade ){
																echo "<option value=\"\">$Unidade</option>\n";
														}
														if( $RPAAntes != $RPA ){
																echo "<option value=\"\">$Edentacao RPA $RPA -----------------------------------------------------------------------</option>\n";
														}
														if( $CentroAntes != $Centro ){
																echo "<option value=\"\">$Edentacao $Edentacao $Centro</option>\n";
														}
														echo "<option value=\"$Sel[0]\">$Edentacao $Edentacao $Edentacao * $Detalhe</option>\n";
														$UnidadeAntes = $Unidade;
														$CentroAntes  = $Centro;
														$RPAAntes     = $RPA;
												}
										}
										$db->disconnect();
										?>
									</select>
                </td>
              </tr>
              <?php if ($_SESSION['_cgrempcodi_'] == 0){ ?>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão</td>
                <td class="textonormal">
 									<select name="Orgao" class="textonormal" onChange="javascript:enviar('Pesquisar');">
										<option value="">Selecione um Órgao...</option>
										<?php
										$db      = Conexao();
										$sqlorg  = "SELECT DISTINCT A.CORGLICODI, B.EORGLIDESC  ";
										$sqlorg .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
										$sqlorg .= " WHERE A.CORGLICODI = B.CORGLICODI ";
										$sqlorg .= " ORDER BY B.EORGLIDESC";
										$resorg = $db->query($sqlorg);
										if (PEAR::isError($resorg)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlorg");
										}else{
												while( $Linha = $resorg->fetchRow() ){
														echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
												}
										}
										$db->disconnect();
										?>
									</select>
                </td>
              </tr>
              <?php } ?>
						</table>
					</td>
				</tr>
        <tr>
 	        <td class="textonormal" align="right" colspan="4">
   	      	<input type="button" name="Selecionar" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar')">
   	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
   	      	<input type="hidden" name="Botao" value="">
          </td>
        </tr>
		    <?php
		    if( $Botao == "Pesquisar" and $Mens == 0 ){
						# Seleciona os Centros de Custo de acordo com a palavra Pesquisada #
				    $db   = Conexao();
						if  ($_SESSION['_cgrempcodi_'] == 0) {
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, B.EORGLIDESC, A.FCENPOSITU "; //Acrescimo de A.FCENPOSITU
								$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
								$sql .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
								$sql .= "   AND A.CORGLICODI = B.CORGLICODI ";
								if( $Todos == "" ){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								if( $Orgao != "" ){
										$sql .= "   AND A.CORGLICODI = $Orgao ";
								}
								$sql .= " ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}else{
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI, A.FCENPOSITU "; //Acrescimo de A.FCENPOSITU
								$sql .= "       FROM SFPC.TBCENTROCUSTOPORTAL A";
								$sql .= " WHERE A.CORGLICODI = ";
								$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
								$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
								$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
								if( $Todos == "" ){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								$sql .= " ORDER BY A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}
						$res  = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								echo "<tr>\n";
								echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if( $rows > 0 ){
										$UnidadeAntes = "";
										$CentroAntes  = "";
										$RPAAntes     = "";
										
										for( $i=0;$i<$rows;$i++ ){
												$Cont++;
												$Linha      = $res->fetchRow();
												$Sequencial = $Linha[0];
												$Centro     = $Linha[3];
												$RPA        = $Linha[4];
												$Detalhe    = $Linha[5];
												$Situacao		=	$Linha[7];
												if ($_SESSION['_cgrempcodi_'] == 0) {
														$Unidade = $Linha[6];
												}

												# Pega o nome da Unidade Orçamentária de acordo com o Órgão #
												if( $_SESSION['_cgrempcodi_'] != 0 and $Cont == 1 ){
														$sqluni  = "SELECT B.EORGLIDESC ";
														$sqluni .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
														$sqluni .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CORGLICODI = $Linha[6]";
														$resuni  = $db->query($sqluni);
														if (PEAR::isError($resuni)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqluni");
														    exit;
														}else{
																$Uni     = $resuni->fetchRow();
																$Unidade = $Uni[0];
														}
												}
												if( $UnidadeAntes != $Unidade  ) {
	    											echo "<tr>\n";
	    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">$Unidade</td>\n";
  				    							echo "</tr>\n";
    										}
												if( $RPAAntes != $RPA or $UnidadeAntes != $Unidade ) {
						            		echo "<tr>\n";
  											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">RPA $RPA </td>\n";
  											    echo "</tr>\n";
    											  echo "<tr>\n";
      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">CENTRO DE CUSTO</td>\n";
      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DETALHAMENTO</td>\n";
      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\">SITUAÇÃO</td>\n";
      											echo "</tr>\n";
  											}
											  echo "<tr>\n";
  											if( $CentroAntes != $Centro or $UnidadeAntes != $Unidade ){
  													echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">$Centro</td>\n";
  											}else{
  													echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">&nbsp;</td>\n";
  											}

  											echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"50%\">\n";
  											$Url = "CadCentroCustoManter.php?CentroCusto=$Sequencial";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  											echo "		<a href=\"$Url\"><font color=\"#000000\">$Detalhe</font></a>\n";
  											echo "	</td>\n";

                			# Mostra situação Ativo - Inativo #
												if( $Linha[7] == A ){
														$Situacao = "ATIVO";
												}else{
														$Situacao = "INATIVO";
												}
  											echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"10%\">\n";
  											echo "		<font color=\"#000000\">$Situacao</font>\n";
		  									echo "	</td>\n";

  											echo "</tr>\n";

  											$mostrat = "";
  											$msgt = "";
  											$mostrar = "";
  											$msgr = "";

												$UnidadeAntes = $Unidade;
												$CentroAntes  = $Centro;
												$RPAAntes     = $RPA;
  									}
				        }else{
  									echo "<tr>\n";
  									echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
  									echo "		Pesquisa sem Ocorrências.\n";
  									echo "	</td>\n";
  									echo "</tr>\n";
								}
						}
						$db->disconnect();
				}
        ?>
				<?php
		    if( $Botao == "Exibir Usuarios/CC" and $Mens == 0 ){
						# Seleciona os Centros de Custo de acordo com a palavra Pesquisada #
				    $db   = Conexao();
						if  ($_SESSION['_cgrempcodi_'] == 0) {
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, B.EORGLIDESC ";
								$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
								$sql .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
								$sql .= "   AND A.CORGLICODI = B.CORGLICODI ";
								if( $Todos == "" ){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								if( $Orgao != "" ){
										$sql .= "   AND A.CORGLICODI = $Orgao ";
								}
								$sql .= " ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}else{
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI ";
								$sql .= "       FROM SFPC.TBCENTROCUSTOPORTAL A";
								$sql .= " WHERE A.CORGLICODI = ";
								$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
								$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
								$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
								if( $Todos == "" ){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								$sql .= " ORDER BY A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}
						$res  = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								echo "<tr>\n";
								echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if( $rows > 0 ){
										$UnidadeAntes = "";
										$CentroAntes  = "";
										$RPAAntes     = "";
										for( $i=0;$i<$rows;$i++ ){
												$Cont++;
												$Linha      = $res->fetchRow();
												$Sequencial = $Linha[0];
												$Centro     = $Linha[3];
												$RPA        = $Linha[4];
												$Detalhe    = $Linha[5];
												if ($_SESSION['_cgrempcodi_'] == 0) {
														$Unidade = $Linha[6];
												}

												# Pega o nome da Unidade Orçamentária de acordo com o Órgão #
												if( $_SESSION['_cgrempcodi_'] != 0 and $Cont == 1 ){
														$sqluni  = "SELECT B.EORGLIDESC ";
														$sqluni .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
														$sqluni .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CORGLICODI = $Linha[6]";
														$resuni  = $db->query($sqluni);
														if (PEAR::isError($resuni)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqluni");
														    exit;
														}else{
																$Uni     = $resuni->fetchRow();
																$Unidade = $Uni[0];
														}
												}
												if( $UnidadeAntes != $Unidade  ) {
	    											echo "<tr>\n";
	    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">$Unidade</td>\n";
  				    							echo "</tr>\n";
    										}
												if( $RPAAntes != $RPA or $UnidadeAntes != $Unidade ) {
						            		echo "<tr>\n";
  											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">RPA $RPA </td>\n";
  											    echo "</tr>\n";
    											  echo "<tr>\n";
      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">CENTRO DE CUSTO</td>\n";
      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DETALHAMENTO</td>\n";
      											echo "</tr>\n";
  											}
											  echo "<tr>\n";
  											if( $CentroAntes != $Centro or $UnidadeAntes != $Unidade ){
  													echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">$Centro</td>\n";
  											}else{
  													echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">&nbsp;</td>\n";
  											}
  											echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"60%\">\n";
  											echo "		<a href=\"CadCentroCustoManterSelecionar.php?CentroCusto=$Sequencial\"><font color=\"#000000\">$Detalhe</font></a>\n";
  											echo "	</td>\n";
  											echo "</tr>\n";
												$UnidadeAntes = $Unidade;
												$CentroAntes  = $Centro;
												$RPAAntes     = $RPA;
  									}
				        }else{
  									echo "<tr>\n";
  									echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
  									echo "		Pesquisa sem Ocorrências.\n";
  									echo "	</td>\n";
  									echo "</tr>\n";
								}
						}
						$db->disconnect();
				}
        ?>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
