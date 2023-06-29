<?php
/**
 * Portal de Compras
 * Prefeitura do Recife
 * 
 * Programa: TabManuaisConsultar.php
 * Autor:	 Ariston
 * Data:	 05/10/2008
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     09/11/2018
 * Objetivo: Tarefa Redmine 206748
 * -----------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/01/2023
 * Objetivo: Tarefa Redimine 277360
 * -----------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao     = $_POST['Botao'];
	$Titulo    = $_POST['Titulo'];
	$Descricao = $_POST['Descricao'];
} else {
	$Critica  = $_GET['Critica'];
	$Mensagem = $_GET['Mensagem'];
	$Mens     = $_GET['Mens'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "TabManuaisConsultar.php";

# Carrega os dados dos arquivos #
$db = Conexao();

$sql = "SELECT	DMP.EDOCMATITU, DMP.EDOCMADESC, DMP.EDOCMAARQS, DMP.TDOCMAULAT, DMP.CUSUPOCODI, UP.EUSUPORESP
		FROM	SFPC.TBDOCUMENTOMANUALPORTAL DMP
				LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON DMP.CUSUPOCODI = UP.CUSUPOCODI 
		ORDER BY DMP.TDOCMAULAT DESC";

$res = $db->query($sql);

if (PEAR::isError($res)) {
	EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $res);
	exit(0);
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.Manuais.Botao.value=valor;
		document.Manuais.submit();
	}
	
	function janela(pageToLoad, winName, width, height, center) {
		xposition=0;
		yposition=0;
		
		if ((parseInt(navigator.appVersion) >= 4 ) && (center)) {
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
	<form enctype="multipart/form-data" action="TabManuaisConsultar.php" method="post" name="Manuais">
		<br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br>
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a> > Institucional > Manuais > Consultar Manuais	
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="2">
						<?php ExibeMens($Mensagem, $Tipo, 1); ?>
					</td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="1000">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
											CONSULTA DE MANUAIS
										</td>
									</tr>
									<?php
									if ($res->numRows()>0) {
										?>
										<tr>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">
												TÍTULO
											</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">
												DESCRIÇÃO
											</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">
												DATA DE CADASTRO
											</td>
											<td align="center" class="titulo3" bgcolor="#F7F7F7">
												USUÁRIO RESPONSÁVEL
											</td>
										</tr>
										<?php
										$linha = 0;
										$itr   = 0;

										resetArquivoAcesso();
								
										while ($linha = $res->fetchRow()) {
										$itr++;
										$titulo        = $linha[0];
										$descricao     = $linha[1];
										$arquivo       = 'institucional/'.$linha[2];
										$dataHoraBanco = explode(' ', $linha[3]);
										$usuario       = $linha[5];
										
										addArquivoAcesso($arquivo);
										
										$dataBanco = explode('-', $dataHoraBanco[0]);
										$data = $dataBanco[2] . '/' . $dataBanco[1] . '/' . $dataBanco[0];

										?>
										<tr>
											<td valign="top" bgcolor="#F7F7F7" class="textonormal">
												<a href="../carregarArquivo.php?arq=<?=urlencode($arquivo)?>" target="_blank"><?=$titulo?></a>
											</td>
											<td valign="top" bgcolor="#F7F7F7" class="textonormal">
												<?=$descricao?>
											</td>
											<td valign="top" align="center" bgcolor="#F7F7F7" class="textonormal">
												<?=$data?>
											</td>
											<td valign="top" align="center" bgcolor="#F7F7F7" class="textonormal">
												<?=$usuario?>
											</td>
										</tr>						
										<?php
										}							
									} else {
									?>
									<tr>
										<td valign="top" bgcolor="#F7F7F7" class="textonormal" colspan="4" width="500">
											Nenhum manual encontrado.
										</td>
									</tr>
									<?php
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