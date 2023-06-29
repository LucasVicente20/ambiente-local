<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabAcessoIncluir.php
# Autor:    Roberta Costa
# Data:     09/04/03
# Objetivo: Programa de Seleção de Acesso
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabAcessoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$HierarquiaCodigo = $_POST['HierarquiaCodigo'];
		$AcessoCodigo     = $_POST['AcessoCodigo'];
		$Critica          = $_POST['Critica'];
}else{
		$Critica          = $_GET['Critica'];
		$Mensagem         = urldecode($_GET['Mensagem']);
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabAcessoSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		if( $HierarquiaCodigo == ""){
			  $Mens     = 1;
			  $Tipo     = 2;
			  $Mensagem = "Selecione uma Hierarquia";
		}else{
				$db     = Conexao();
				$sql    = "SELECT CACEPOCPAI FROM SFPC.TBACESSOPORTAL WHERE CACEPOCODI = $AcessoCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: 44\nSql: $sql");
				}else{
						while( $Linha = $result->fetchRow() ){
						    $HieraraquiaCodigo = $Linha[0];
						}
				}
				$db->disconnect();
				$Url = "TabAcessoAlterar.php?AcessoCodigo=$AcessoCodigo&HierarquiaCodigo=$HieraraquiaCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
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
function AcessoCod(){
	document.Acesso.AcessoCodigo.value = document.Acesso.HierarquiaCodigo.options[document.Acesso.HierarquiaCodigo.selectedIndex].value;
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabAcessoSelecionar.php" method="post" name="Acesso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Acesso > Manter
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									MANTER - ACESSO
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para atualizar/excluir acesso já cadastrado, selecione um acesso e clique no botão "Selecionar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%">Acesso</td>
											<td class="textonormal">
		  				  	      <select name="HierarquiaCodigo" OnChange="javascript:AcessoCod();" class="textonormal">
													<option value="">Selecione um Acesso...</option>
													<?php
													$db     = Conexao();
													$sql    = "SELECT CACEPOCODI,EACEPODESC FROM SFPC.TBACESSOPORTAL WHERE ";
													$sql   .= "CACEPOCODI = CACEPOCPAI ORDER BY AACEPOORDE";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: 127\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
																	$Rows++;
																	$CodigoPai[$Rows-1] = "$Linha[0]_$Linha[1]";
															}
															for( $i=0;$i<$Rows;$i++ ){
																	$AcessoPai = explode("_",$CodigoPai[$i]);
																	if( $AcessoPai[0] == $HierarquiaCodigo ) {
																			echo "<option value=\"$AcessoPai[0]\" selected>$AcessoPai[1]</option>\n";
																	} else {
																			echo "<option value=\"$AcessoPai[0]\">$AcessoPai[1]</option>\n";
																	}
																	TipoFilho($AcessoPai[0],1,$HierarquiaCodigo);
															}
													}
													$db->disconnect();
													?>
													</option>
											  </select>
											  <input type="hidden" name="Critica" value="1">
											</td>
	  		              <td>
	  		              	<input type="hidden" name="AcessoCodigo" value="<?php echo $Linha[0];?>" size="2">
	  		              </td>
										</tr>
									</table>
							  </td>
						  </tr>
							<tr>
			 	        <td class="textonormal" align="right">
			          	<input type="submit" value="Selecionar" class="botao" name="Selecionar">
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
document.Acesso.HierarquiaCodigo.focus();
//-->
</script>
