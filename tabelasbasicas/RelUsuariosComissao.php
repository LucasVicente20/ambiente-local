<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: RelUsuariosComissao.php
 * Autor:    Lucas Vicente
 * Data:     06/09/2022
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica = $_POST['Critica'];
	$Botao   = $_POST['Botao'];
	$Opcao   = $_POST['Opcao'];
}

# Constói a Lista com a comissão dos usuários de produção #
$db  = Conexao();

$sql = "SELECT DISTINCT COMIS.ECOMLIDESC, COMIS.CCOMLICODI FROM SFPC.TBCOMISSAOLICITACAO COMIS";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}
?>
<html>
<?php
# Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.Lista.Botao.value=valor;
		document.Lista.submit();
	}

	function janela(pageToLoad, winName, width, height, center) {
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
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="RelUsuariosListagem.php" method="post" name="Lista">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuários > Lista de Usuários por Comissão
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
		<?php
		if ($Mens == 1) {
			?>
			<tr>
				<td width="100"></td>
				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
			</tr>
			<?php
		}
		?>
		<!-- Fim do Erro -->
		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table border="1" cellspacing="0" cellpadding="3" bgcolor="#ffffff" bordercolor="#75ADE6" class="textonormal" summary="">
					<tr>
						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="8">
							USUÁRIOS POR COMISSÃO DE LICITAÇÃO
						</td>
					</tr>
					<tr>
						<?php
						while($cols = $result->fetchRow()) {
							$comissaoLic = $cols[0];
							$codigo      = $cols[1];
							?>
							<tr>
								<td class="titulo3" align="center" colspan="8" widht="100px">
									<strong><?php echo $comissaoLic; ?></strong>
								</td>
								<?php
								if (1==1) {
									$sqlNome = "SELECT USU.EUSUPORESP FROM SFPC.TBUSUARIOCOMIS USUCOMIS
									INNER JOIN SFPC.TBUSUARIOPORTAL USU ON USU.CUSUPOCODI = USUCOMIS.CUSUPOCODI
									WHERE USUCOMIS.CCOMLICODI = " . $codigo;

									$sql2 = $db->query($sqlNome);
													
									if (PEAR::isError($sql2)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlNome");
									} else {
										while ($reg = $sql2->fetchRow()) {
											if ($nomeDesc != $reg[0]) {
												$nomeDesc = $reg[0]; 
											}
											?>
											<tr>
												<td widht="100px"><?php echo $nomeDesc; ?></td>
											</tr>
										<?php
										}
									}
								}
								?>
							</tr>
							<?php
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