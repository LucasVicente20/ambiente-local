<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPerfilAcessoManter.php
# Autor   : Luciano Mauro
# Data    : 02/04/2003
# Objetivo: Manutenção do Perfil/Acesso
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabPerfilAcessoManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$Critica          = $_POST['Critica'];
		$PerfilCodigo     = $_POST['PerfilCodigo'];
		$PerfilCodigoDesc = $_POST['PerfilCodigoDesc'];
		$Titulo           = $_POST['Titulo'];
		$AcessoCodigo     = $_POST['AcessoCodigo'];
}else{
		$PerfilCodigo    = $_POST['PerfilCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPerfilAcessoManter.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "TabPerfilAcessoManter.php?Critica=";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}else{
		if( $Critica == "" ) {
				$Critica = 1;
				$Titulo  = "Selecionar";
		}elseif( $Critica == 1 ){
				if( $PerfilCodigo == "") {
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "Selecione um Perfil";
				}else{
						$db     = Conexao();
						$sql    = "SELECT CACEPOCODI FROM SFPC.TBPERFILACESSO WHERE CPERFICODI = $PerfilCodigo";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								while( $Linha = $result->fetchRow() ){
									$Row++;
									$AcessoCodigo[$Row-1] = $Linha[0];
								}
						}
						$db->disconnect();
						$Critica = 2;
						$Titulo  = "Manter";
				}
		}elseif( $Critica = 2 ){
				$db     = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql    = "DELETE FROM SFPC.TBPERFILACESSO WHERE CPERFICODI = $PerfilCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						for ( $L = 0; $L < count($AcessoCodigo); $L++ ) {
							$db     = Conexao();
							$Data   = date("Y-m-d H:i:s");
							$sql    = "INSERT INTO SFPC.TBPERFILACESSO (";
							$sql   .= "CPERFICODI, CACEPOCODI, TPERFAULAT ";
							$sql   .= ") VALUES (";
							$sql   .= "$PerfilCodigo, $AcessoCodigo[$L], '$Data')";
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
									$db->query("ROLLBACK");
							    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
						}
				}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();
				$Mens         = 1;
				$Tipo         = 1;
				$Critica      = 1;
				$PerfilCodigo = "";
				$Mensagem     = "Perfil/Acesso Alterado com Sucesso";
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
function UnCheck(Numero,Pai,QtdFilhos) {
	if (document.Perfil.elements[Pai].checked == 0) {
		document.Perfil.elements[Numero].checked = 0;
		if ( QtdFilhos != 0 ) {
			for( I = Pai + 1; I <= Pai + QtdFilhos; I++ ) { document.Perfil.elements[I].checked = 0; }
		}
	} else {
		if (document.Perfil.elements[Numero].checked == 0) {
			for( I = Numero + 1; I <= Numero + QtdFilhos; I++ ) { document.Perfil.elements[I].checked = 0; }
		}
	}
}
function enviar(valor){
	document.Perfil.Botao.value=valor;
	document.Perfil.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabPerfilAcessoManter.php" method="post" name="Perfil">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Perfil > Perfil/Acesso
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) { ?>
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
			<tr>
				<td class="textonormal">
					<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
					<tr>
						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
							<?php echo strtoupper2($Titulo); ?> - PERFIL/ACESSO
						</td>
					</tr>
					<tr>
						<td class="textonormal" >
							<p align="justify">
							<?php
							if( $Critica == 1 ) {
									echo "Para Incluir um novo Perfil/Acesso, selecione um Perfil na lista abaixo e clique no botão \"Selecionar\".";
							}else{
									echo "Marque/desmarque o(s) acesso(s) desejado(s), e clique no botão \"Manter\". Pelo menos um acesso deverá ser selecionado para o perfil.";
							}
							?>
							</p>
						</td>
					</tr>
					<tr>
						<td>
							<table class="textonormal" border="0" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="25%" height="20">Perfil</td>
								<td class="textonormal">
									<?php
									if( $Critica == 1 ){
											echo "<select name=\"PerfilCodigo\" value=\"\" OnChange=\"javascript:PerfilDesc();\" class=\"textonormal\">\n";
											echo "<option value=\"\">Selecione um Perfil...</option>\n";
											$db     = Conexao();
											$sql    = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ORDER BY EPERFIDESC";
											$result = $db->query($sql);
											if( PEAR::isError($result) ){
											    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__." \nSql: $sql");
											}
											while( $Linha = $result->fetchRow() ){
													if ($Linha[0] == $PerfilCodigo) {
															echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
													} else {
															echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
													}
											}
											$db->disconnect();
											echo "</select>\n";
											echo "<input type=\"hidden\" name=\"PerfilCodigoDesc\" value=\"$PerfilCodigoDesc\">\n";
									}else{
											echo "<input type=\"hidden\" name=\"PerfilCodigo\" value=\"$PerfilCodigo\">\n";
											echo "<input type=\"hidden\" name=\"PerfilCodigoDesc\" value=\"$PerfilCodigoDesc\">\n";
											echo $PerfilCodigoDesc;
									}
									?>
									<input type="hidden" name="Critica" value="<?php echo $Critica ?>">
									<input type="hidden" name="Titulo" value="<?php echo $Titulo ?>">
								</td>
							</tr>
							<?php if( $Critica == "2" ){ ?>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" valign="top">Acesso </td>
								<td colspan="2" class="textonormal">
									<center><table border="0" width="100%" cellpadding="0" cellspacing="0" summary="">
										<?php
			            	$db     = Conexao();
										$sql    = "SELECT CACEPOCODI, EACEPODESC, CACEPOCPAI FROM SFPC.TBACESSOPORTAL ";
										$sql   .= "WHERE CACEPOCODI = CACEPOCPAI ORDER BY AACEPOORDE";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$L = 0; $Numero = 4; $QtdFilhos = 0;
												$Rows = $result->numRows();
												for( $i=0;$i < $Rows;$i++ ){
														$Linha = $result->fetchRow();
														if( $Linha[0] == $Linha[2] ){
																$QtdFilhos = AcessoFilho_Qtd($Linha[0],1);
																if( FindArray($Linha[0],$AcessoCodigo) ){
																		$CheckBox[$L] = "<tr><td class=\"textonormal\">\n<input type=\"checkbox\" checked name=\"AcessoCodigo[]\" OnClick=\"UnCheck($Numero,$Numero,$QtdFilhos)\" value=\"$Linha[0]\"> $Linha[1]\n</td></tr>\n";
																}else{
																		$CheckBox[$L] = "<tr><td class=\"textonormal\">\n<input type=\"checkbox\" name=\"AcessoCodigo[]\" OnClick=\"UnCheck($Numero,$Numero,$QtdFilhos)\" value=\"$Linha[0]\"> $Linha[1]\n</td></tr>\n";
																}
														}
														$L++; $Numero++;
														$a = $Numero - 1;
														$b = $L - 1;
														AcessoFilho_CheckBox($Linha[0],1,$AcessoCodigo,$Numero - 1,$L - 1);
												}
										}
										$db->disconnect();
										for ($L = 0;$L < count($CheckBox);$L++) { echo $CheckBox[$L]; }
										?>
									</table></center>
								</td>
							</tr>
							<?php } ?>
							</table>
						</td>
					</tr>
					<tr>
						<td class="textonormal" align="right">
							<?php
							if( $Critica == 2 ){
									echo "<input type=\"button\" value=\"Manter\" class=\"botao\" onclick=\"javascript:enviar('Manter');\">\n";
									echo "<input type=\"button\" value=\"Voltar\" class=\"botao\" onclick=\"javascript:enviar('Voltar');\">\n";
									echo "<input type=\"hidden\" name=\"Botao\" value=\"\"></td>\n";
							}else{
									echo "<input type=\"button\" value=\"Selecionar\" class=\"botao\" onclick=\"javascript:enviar('Manter');\">\n";
									echo "<input type=\"hidden\" name=\"Botao\" value=\"\">\n";
							}
							?>
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
<?php
if ( $Critica == 1 ) {
	echo "document.Perfil.PerfilCodigo.focus();\n";
	echo "function PerfilDesc() {\n";
  echo "document.Perfil.PerfilCodigoDesc.value = document.Perfil.PerfilCodigo.options[document.Perfil.PerfilCodigo.selectedIndex].text;\n";
	echo "}\n";
}
?>
</script>
