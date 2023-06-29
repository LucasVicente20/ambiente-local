<?php
#-------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadCentroCustoUsuarioSelecionar.php
# Objetivo: Programa que seleciona o Usuário para o(s) Centro(s) de Custo
# Autor:    Roberta Costa
# Data:     09/06/2005
# Alterado: Marcus Thiago
# Data:     11/01/2006 - Exibição dos usuários requisitantes e atendentes
# Alterado: Álvaro Faria
# Data:     24/08/2006 - Não exibição dos Centros de Custo 62 e 77 para usuarios normais
#                        Retirada do combo box de Centros de Custo
# Alterado: Carlos Abreu
# Data:     23/04/2007 - Retirar exibição de Almoxarifado/Extra Atividade da lista de Centros de Custo
# Alterado: Carlos Abreu
# Data:     25/05/2007 - Permitir que Administradores DLC tenham visao geral
# Alterado: Rodrigo Melo
# Data:     15/06/2009 - Correção para aparecer o nome do centro de custo quando é o mesmo da RPA anterior - CR 814.
#                        E remoção do botão "Selecionar" pois não está fazendo nada, bem como do bloco de código que
#                        não está referenciando nenhum nome de botão válido.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadCentroCustoUsuarioManter.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao       = $_POST['Botao'];
		$CentroCusto = $_POST['CentroCusto'];
		$Todos       = $_POST['Todos'];
		$Orgao       = $_POST['Orgao'];
		$Descricao   = strtoupper2(trim($_POST['Descricao']));
}else{
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: CadCentroCustoUsuarioSelecionar.php");
		exit;
}elseif($Botao == "Pesquisar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($Descricao == "" and $Todos == "" and $Orgao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCentroCustoUsuarioSelecionar.Descricao.focus();\" class=\"titulo2\">Descrição do Centro de Custo</a>";
		}
}

# Pega a descrição do Perfil do usuário logado #
if($_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 and $_SESSION['_fperficorp_'] != 'S'){
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
	document.CadCentroCustoUsuarioSelecionar.Botao.value = valor;
	document.CadCentroCustoUsuarioSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoUsuarioSelecionar.php" method="post" name="CadCentroCustoUsuarioSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Centro de Custo > Centro de Custo/Usuário
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
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
						SELECIONAR - CENTRO DE CUSTO/USUÁRIO
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

							<?php if ($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S') { ?>
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
										$sqlorg .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
										$sqlorg .= " ORDER BY B.EORGLIDESC";
										$resorg = $db->query($sqlorg);
										if( PEAR::isError($resorg) ){
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
						<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
						<input type="hidden" name="Botao" value="">
					</td>
				</tr>
				<?php
				if($Botao == "Pesquisar" and $Mens == 0){
						# Seleciona os Centros de Custo de acordo com a palavra Pesquisada #
						$db  = Conexao();
						if ($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S') {
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, B.EORGLIDESC ";
								$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
								$sql .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
								$sql .= "   AND A.CORGLICODI = B.CORGLICODI ";
								$sql .= "   AND (A.CCENPODETA,A.CCENPOCENT) <> (77,799)";
								if($Todos == ""){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								if($Orgao != ""){
										$sql .= "   AND A.CORGLICODI = $Orgao ";
								}
								$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
								$sql .= " ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}else{
								$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
								$sql .= "       A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI ";
								$sql .= "       FROM SFPC.TBCENTROCUSTOPORTAL A";
								$sql .= " WHERE A.CORGLICODI = ";
								$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
								$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
								$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R')) ";
								if($Todos == ""){
										$sql .= "   AND A.ECENPODESC LIKE '$Descricao%' ";
								}
								$sql .= "   AND (A.CCENPODETA,A.CCENPOCENT) <> (77,799)";
								$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
								$sql .= " ORDER BY A.CCENPONRPA, A.ECENPODESC, A.ECENPODETA";
						}
						$res = $db->query($sql);
						if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								echo "<tr>\n";
								echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								echo "</tr>\n";
								if($rows > 0){
										$UnidadeAntes = "";
										$CentroAntes  = "";
										$RPAAntes     = "";

										for($i=0; $i<$rows; $i++){
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
												if($_SESSION['_cgrempcodi_'] != 0 and $_SESSION['_fperficorp_'] != 'S' and $Cont == 1){
														$sqluni  = "SELECT B.EORGLIDESC ";
														$sqluni .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
														$sqluni .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CORGLICODI = $Linha[6]";
														$sqluni .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
														$resuni  = $db->query($sqluni);
														if( PEAR::isError($resuni) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqluni");
																exit;
														}else{
																$Uni     = $resuni->fetchRow();
																$Unidade = $Uni[0];
														}
												}
												if($UnidadeAntes != $Unidade){
														echo "<tr>\n";
														echo "	<td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">$Unidade</td>\n";
														echo "</tr>\n";
												}
												if($RPAAntes != $RPA or $UnidadeAntes != $Unidade){
														echo "<tr>\n";
														echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">RPA $RPA </td>\n";
														echo "</tr>\n";
														echo "<tr>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">CENTRO DE CUSTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DETALHAMENTO</td>\n";
														echo "</tr>\n";
												}
												echo "<tr>\n";
												if($CentroAntes != $Centro or $UnidadeAntes != $Unidade or $RPAAntes != $RPA){
														echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">$Centro</td>\n";
												}else{
														echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"30%\">&nbsp;</td>\n";
												}

												# Mostra os usuários cadastrados #
												$sqlusuarios  = "SELECT DISTINCT(A.CUSUPOCODI), A.EUSUPORESP, B.CCENPOSEQU, B.FUSUCCTIPO";
												$sqlusuarios .= "  FROM SFPC.TBUSUARIOPORTAL A, SFPC.TBUSUARIOCENTROCUSTO B  ";
												$sqlusuarios .= " WHERE A.CUSUPOCODI = B.CUSUPOCODI AND B.CCENPOSEQU = $Sequencial AND B.FUSUCCTIPO IN ('T','R') ";
												$sqlusuarios .= "   AND A.CGREMPCODI <> 0 "; // Para retirar o grupo internet
												if ($_SESSION['_cgrempcodi_'] != 0 ) {
														$sqlusuarios .= "AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
												}
												$sqlusuarios .= "ORDER BY A.EUSUPORESP ";
												$resusuarios  = $db->query($sqlusuarios);
												if( PEAR::isError($resusuarios) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while($Linhausuarios = $resusuarios->fetchRow()){
																if($Linhausuarios[3] == 'T'){
																		$msgt = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;->&nbsp;ATENDIMENTO<br>";
																		$mostrat .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;$Linhausuarios[1]<br>";
																}elseif ($Linhausuarios[3] == 'R'){
																		$msgr = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;->&nbsp;REQUISITANTE<br>";
																		$mostrar .="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;$Linhausuarios[1]<br>";
																}
														}
												}
												# Termina a amostragem dos usuários cadastrados #

												# Exibir mensagem se exisitir usuários associados
												if($mostrat != '' or $mostrar != ''){
													$mensagem = "<br>&nbsp;&nbsp;&nbsp;USUÁRIOS VINCULADOS A ESTE CENTRO DE CUSTO:<br>";
												}else{
													$mensagem = "";
												}

												echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" width=\"60%\">\n";
												$Url = "CadCentroCustoUsuarioManter.php?CentroCusto=$Sequencial&TipoUsuario=R";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												echo "		<a href=\"$Url\"><font color=\"#000000\">$Detalhe</font></a>$mensagem$msgt$mostrat$msgr$mostrar\n";
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
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
