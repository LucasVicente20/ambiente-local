<?php
/**
 * Portal de Compras
 * Prefeitura do Recife
 * 
 * Programa: 	TabManuaisIncluir.php
 * Autor:		Marcos Túlio de Almeida Alves
 * Data:		28/07/2011
 * ------------------------------------------------------------------------
 * Alterado:	Lucas Baracho
 * Data:		08/01/2023
 * Objetivo:	Tarefa Redmine 277360
 * ------------------------------------------------------------------------
 * Alterado:	Lucas Baracho
 * Data:		16/01/2023
 * Objetivo:	Tarefa Redmine 277667
 * ------------------------------------------------------------------------
 * Alterado:	Lucas Baracho
 * Data:		21/01/2023
 * Objetivo:	Tarefa Redmine 277852
 * ------------------------------------------------------------------------
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
	$Botao          = $_POST['Botao'];
	$titulo		    = $_POST['titulo'];
	$descricao      = $_POST['descricao'];
	$NomeArquivo	= $_POST['NomeArquivo'];
} else {
	$Critica  = $_GET['Critica'];
	$Mensagem = $_GET['Mensagem'];
	$Mens     = $_GET['Mens'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
$NomePrograma = "TabManuaisIncluir.php";

$TAMANHO_MAXIMO_ARQUIVO = 5242880; /* 5MB */

$Mens      = 0;
$Tipo      = 0;
$Mensagem .= "Informe: ";

# Verificar se os dados do post foram descartados no servidor (Normalmente devido ao arquivo ser maior que o máximo de post permitido)
if (($_SERVER['REQUEST_METHOD'] == "POST") and (count($_POST)==0)) {
	$Mens      = 1;
	$Tipo      = 2;
	$Kbytes    = $TAMANHO_MAXIMO_ARQUIVO/1024;
	$Mensagem .= "Arquivo com tamanho máximo menor que $Kbytes KB";
}

if ($_POST['Botao']=="Enviar") {
	if (!is_uploaded_file($_FILES['NomeArquivo']['tmp_name'])) {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Nenhum arquivo enviado";
	} elseif ($_FILES['NomeArquivo']['size'] == 0) {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Arquivo com Tamanho diferente de 0 Kb";
	}

	if (($_FILES['NomeArquivo']['size'] > $TAMANHO_MAXIMO_ARQUIVO)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Kbytes    = $TAMANHO_MAXIMO_ARQUIVO/1024;
		$Kbytes    = (int) $Kbytes;
		$Mensagem .= "Arquivo com tamanho máximo menor que $Kbytes KB";
	}

	if ($descricao=="" or is_null($descricao)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Descrição";
	}

	if ($titulo=="" or is_null($titulo)) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Título";
	}

	if ($Mens == 0) {
		$db = Conexao();

		$db->query("BEGIN TRANSACTION");

		$sql = " SELECT MAX(CDOCMACODI) FROM SFPC.TBDOCUMENTOMANUALPORTAL ";

		$res = $db->query($sql);

		if (PEAR::isError($res)) {
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $res);
			exit(0);
		}

		$linha = $res->fetchRow();

		$cdocmacodi = $linha[0] + 1;

		$ArquivoNome = $_FILES['NomeArquivo']['name'];
		$ArquivoDestinoNome = "MAN_".$cdocmacodi."_".tratarNomeArquivo($ArquivoNome);
		$ArquivoDestino = $GLOBALS["CAMINHO_UPLOADS"]."institucional/".$ArquivoDestinoNome;

        if (is_null($linha[0])) {
			$cdocmacodi = 1;
		}

		if (file_exists($ArquivoDestino)) {
			unlink ($ArquivoDestino);
		}

		if (@move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $ArquivoDestino)) {
            $sql = "INSERT INTO SFPC.TBDOCUMENTOMANUALPORTAL (
						CDOCMACODI, EDOCMAARQU, EDOCMAARQS, EDOCMATITU, EDOCMADESC, CUSUPOCODI, TDOCMAULAT
					) VALUES (
						$cdocmacodi, '$ArquivoNome','$ArquivoDestinoNome' ,'$titulo', '$descricao', ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."'
					) ";

			$res = $db->query($sql);

			if (PEAR::isError($res)) {
				$db->query("ROLLBACK");
				EmailErroSQL("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "SQL falhou.", $sql, $res);
				exit(0);
			}

			$db->query("COMMIT");
			$db->query("END");
			$db->disconnect();

			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem = "Manual Carregado com Sucesso";

			$titulo		 = "";
			$descricao   = "";
			$NomeArquivo = "";
		} else {
			$db->query("ROLLBACK");
			EmailErro("Erro de SQL em ".$NomePrograma, __FILE__, __LINE__, "Upload de arquivo foi recebido pelo servidor, mas não pôde ser gravado no diretório correto. Verifique se o diretório permite gravação de arquivos e se o sepaço de disco não está cheio.\n\nNome do arquivo a ser gravado: ".$ArquivoDestino);
			exit(0);
		}
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
	window.open(pageToLoad,winName,args);
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form enctype="multipart/form-data" action="TabManuaisIncluir.php" method="post" name="Manuais">
		<br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br>
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Institucional > Manuais > Incluir
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
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											ENVIO DE MANUAIS
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
											Selecione o documento desejado. O documento deve ser menor que 5Mb e de um dos tipos: .zip, .pdf, .rtf, .doc, .xls, odp, odt, sdw, ppt ou .txt
											</p>
										</td>
									</tr>
									<tr>
										<td>
		    	      						<table class="textonormal" border="0" align="left" summary="">
				 	      						<tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7" height="20">Manual*</td>
   		                							<td class="textonormal">
														<input type="file" name="NomeArquivo" class="textonormal" size="50" value="<?=$NomeArquivo?>"/>
   		                							</td>
	            								</tr>
				 	      						<tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7" height="20">Título*</td>
   		                							<td class="textonormal">
														<input type="text" name="titulo" class="textonormal" size="50" value="<?=$titulo?>"/>
   		                							</td>
	            								</tr>
				 	      						<tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição*</td>
   		                							<td class="textonormal">
														<textarea name="descricao" class="textonormal" cols="39" rows="3"><?=$descricao?></textarea>
   		                							</td>
	            								</tr>
											</table>
										</td>
									</tr>
									<tr>
		   	        					<td class="textonormal" align="right">
             	    						<input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Enviar');">
	                						<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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