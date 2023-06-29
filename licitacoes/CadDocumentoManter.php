<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadDocumentoManter.php
 * Autor:    Rossana Lira
 * Data:     22/04/2003
 * -------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     09/06/2010
 * Objetivo: Alterando tratamento do nome do arquivo para usar a funcao tratarNomeArquivo()
 *           para corrigir bug e centralizar o tratamento de nome de arquivo em uma única função
 * -------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     01/09/2010
 * Objetivo: Alteração para incluir as planilhas "RESULTADO.XLS", "ORÇAMENTO.XLS" OU "ORCAMENTO.XLS" da seguinte forma:
 *           RESULTADO_9999_99_99_99_9999.XLS e ORCAMENTO_9999_99_99_99_9999.XLS. CR: 5210
 * -------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     28/02/2011
 * Objetivo: Alteração para colocar justificativa ao excluir, e emails disparados na inclusão/exclusão com as justificativas.
 *           Alteração de exclusão para não deletar os documentos. Ao invés disso, eles serão arquivados (flag informando que eles foram excluídos).
 * -------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     23/03/2011
 * Objetivo: Não disparar emails a interessados caso a inclusão/exclusão for de arquivos ocultos (orçamento ou resultado)
 * -------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     07/04/2011
 * Objetivo: Não disparar emails a interessados que não querem participar da licitação
 * -------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     26/05/2011
 * Objetivo: Salvar emails na nova tabela de email
 * -------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     13/07/2011
 * Objetivo: Alteração para permitir apenas a inclusão de documentos com a extensão .pdf ou .xls para as planilhas do TCE (ORCAMENTO.xls / ORÇAMENTO.xls e RESULTADO.xls)
 * -------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     21/07/2011
 * Objetivo: Alteração para colocar crítica para não permitir o usuário colocar caracteres inválidos no nome do arquivo.
 * -------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     10/01/2023
 * Objetivo: Tarefa Redmine 277436
 * -------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     11/01/2023
 * Objetivo: Corrigir Time out da Pagina
 * -------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     20/04/2023
 * Objetivo: Cr 281706 
 * -------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     27/04/2023
 * Objetivo: Cr 282313 
 * -------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include '../funcoes.php';
# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/CadDocumentoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                = $_POST['Botao'];
	$Critica              = $_POST['Critica'];
	$LicitacaoProcesso    = $_POST['LicitacaoProcesso'];
	$LicitacaoAno         = $_POST['LicitacaoAno'];
	$ComissaoCodigo       = $_POST['ComissaoCodigo'];
	$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
	$QuantArquivos        = $_POST['QuantArquivos'];
	$DocumentoDescricao   = $_POST['DocumentoDescricao'];
	$Documentos           = $_POST['Documentos'];
	$NCaracteres          = $_POST['NCaracteres'];
} else {
	$LicitacaoProcesso    = $_GET['LicitacaoProcesso'];
	$LicitacaoAno         = $_GET['LicitacaoAno'];
	$ComissaoCodigo       = $_GET['ComissaoCodigo'];
	$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadDocumentoManter.php";

# licitacaoEnviaEmailsLicitantes- envia email a todos licitantes inscritos em uma licitação.
function licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $GrupoCodigo, $titulo, $corpo, $FlagConfirmaEnviarEmails, $FlagIniciouTransacao) {
	if ($FlagConfirmaEnviarEmails) {
		if (!$FlagIniciouTransacao or is_null($FlagIniciouTransacao)) {
			$db->query("BEGIN TRANSACTION");
		}

		# Pegando email de comissão
		$sql = "SELECT	ECOMLIMAIL
				FROM	SFPC.TBCOMISSAOLICITACAO
				WHERE	CGREMPCODI = " . $GrupoCodigo . "
						AND CCOMLICODI = $ComissaoCodigo ";

		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
		}

		$linha = $result->fetchRow();
		$emailComissao=$linha[0];

		# pegando listas de interessados do processo
		$sql = "SELECT	ELISOLNOME, ELISOLMAIL, CLISOLCODI
				FROM	SFPC.TBLISTASOLICITAN
				WHERE	CLICPOPROC = $LicitacaoProcesso
						AND ALICPOANOP = $LicitacaoAno
						AND CGREMPCODI = $GrupoCodigo
						AND CCOMLICODI = $ComissaoCodigo
						AND CORGLICODI = $OrgaoLicitanteCodigo
						AND FLISOLPART = 'S' ";

		$result = $db->query($sql);

		if (PEAR::isError($result)) {
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
		}

		# Salvando registro do Email
		$sql2 = "INSERT INTO SFPC.TBLICITACAOEMAIL (
					CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI,
					CORGLICODI, XLICEMTITL, XLICEMBODY, DLICEMULAT,
					FLICEMANEX
				) VALUES (
					$GrupoCodigo, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo,
					$OrgaoLicitanteCodigo, '$titulo', '$corpo', NOW(), 'N'
				) ";

		$result2 = $db->query($sql2);

		if (PEAR::isError($result2)) {
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
		}

		$sql2 = "SELECT LAST_VALUE FROM SFPC.TBLICITACAOEMAIL_CLICEMCODI_SEQU ";

		$result2 = $db->query($sql2);

		if (PEAR::isError($result2)) {
			$db->query("ROLLBACK");
			EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
		}

		$Linha2 = $result2->fetchRow();
		$codigoEmail = $Linha2[0];

		# enviando emails
		while ($Linha = $result->fetchRow()) {
			$nome    = $Linha[0];
			$email   = $Linha[1];
			$solCodi = $Linha[2];

			$sql2 = "INSERT INTO SFPC.TBLICITACAOEMAILSOLICITANTE (
						CGREMPCODI, CLICPOPROC, ALICPOANOP, CCOMLICODI,
						CORGLICODI, DLEMSOULAT, CLISOLCODI, CLICEMCODI
					) VALUES (
						$GrupoCodigo, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo,
						$OrgaoLicitanteCodigo, NOW(), $solCodi, $codigoEmail
					) ";

			$result2 = $db->query($sql2);

			if (PEAR::isError($result2)) {
				$db->query("ROLLBACK");
				EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql2, $result2);
			}

			//EnviaEmail($email, $titulo, $corpo, $emailComissao);
		}

		if (!$FlagIniciouTransacao or is_null($FlagIniciouTransacao)) {
			$db->query("BEGIN TRANSACTION");
		}
	}
}

if ($Botao == "Excluir") {
	if ($QuantArquivos <= 0) {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Arquivo(s) a ser(em) excluído(s)";
	} else {
		if (strlen($DocumentoDescricao) < 1) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Observação do(s) Documento(s) a ser(em) excluído(s)";
		}

		if ($Mens == 0) {
			$cntArquivosExcluidos=0;

			for ($Row = 0 ; $Row < $QuantArquivos ; $Row++) {
				if ($Documentos[$Row] != "") {
					$cntArquivosExcluidos++;

					$db = Conexao();
					$db->query("BEGIN TRANSACTION");

					$sql = "UPDATE	SFPC.TBDOCUMENTOLICITACAO
							SET		FDOCLIEXCL = 'S',
									EDOCLIOBSE = '$DocumentoDescricao',
									CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ",
									TDOCLIULAT = now()
							WHERE	CLICPOPROC = $LicitacaoProcesso
									AND ALICPOANOP = $LicitacaoAno
									AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "
									AND CCOMLICODI = $ComissaoCodigo
									AND CDOCLICODI = " . $Documentos[$Row] . " ";

					$result = $db->query($sql);

					if (PEAR::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$db->query("COMMIT");
						$db->query("END TRANSACTION");

						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Documento(s) Excluído(s) com Sucesso";
					}
				}
			}

			#### Enviando email a todos interessados ####
			if ($cntArquivosExcluidos > 0) {
				$strArquivos = "ARQUIVOS: \n";
				$contaArquivos = 0;

				# Pegando informações de todos documentos sendo excluídos
				for ($Row = 0 ; $Row < $QuantArquivos ; $Row++) {
					if ($Documentos[$Row] != "") {
						$sql = "SELECT	EDOCLINOME, EDOCLIOBSE
								FROM	SFPC.TBDOCUMENTOLICITACAO
								WHERE	CLICPOPROC = $LicitacaoProcesso
										AND ALICPOANOP = $LicitacaoAno
										AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . "
										AND CCOMLICODI = $ComissaoCodigo
										AND CDOCLICODI = ".$Documentos[$Row]."
										AND (NOT((EDOCLINOME ~* '^RESULTADO_') OR (EDOCLINOME ~* '^ORCAMENTO_'))) ";

						$result = $db->query($sql);

						if (PEAR::isError($result)) {
							EmailErroSQL("Erro de SQL", __FILE__, __LINE__, "Erro de SQL", $sql, $result);
						}

						$rows = $result->numRows();

						if ($rows > 0) {
							$contaArquivos ++;

							$Linha = $result->fetchRow();
							$arquivoNome       = $Linha[0];
							$arquivoObservacao = $Linha[1];

							$strArquivos .= $arquivoNome."\n";
						}
					}
				}

				if ($contaArquivos > 0) {
					$strArquivos .= "\nJUSTIFICATIVA:\n".$DocumentoDescricao."";
					$str="Comunicamos que houve exclusão do(s) seguinte(s) documento(s) no Portal de Compras, referente ao ano ".$LicitacaoAno." e processo ".$LicitacaoProcesso.":\n\n".$strArquivos;
					licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $_SESSION['_cgrempcodi_'], "Portal de Compras- Exclusão de documento(s) em licitação", $str, TRUE, TRUE);
				}

				$DocumentoDescricao = "";
			}
	  	}
	}
} elseif ($Botao == "Voltar") {
	header("location: CadDocumentoSelecionar.php");
	exit;
} elseif ($Botao == "Incluir") {
	# Critica dos Campos #
	if ($Critica == 1) {
		$_FILES['NomeArquivo']['name'] = tratarNomeArquivo($_FILES['NomeArquivo']['name']);

		if (!preg_match("/.pdf/", $_FILES['NomeArquivo']['name']) &&
			!((preg_match("^/ORCAMENTO/.XLS", strtoupper2($_FILES['NomeArquivo']['name']))) ||
			(preg_match("/^ORÇAMENTO/.XLS", strtoupper2($_FILES['NomeArquivo']['name']))) ||
			(preg_match("/^RESULTADO/.XLS", strtoupper2($_FILES['NomeArquivo']['name']))))
			) {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Selecione somente arquivos com a extensão .pdf ou .xls para as planilhas do TCE (ORCAMENTO.xls / ORÇAMENTO.xls e RESULTADO.xls)";
		} else {
			if (!(preg_match("/^[A-Z|a-z|0-9|\.|_|-]+\.(pdf|PDF|xls|XLS)/", $_FILES['NomeArquivo']['name']))) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Nome do arquivo possui um ou mais caracter(es) inválido(s). Favor alterar o nome do arquivo para conter apenas letras, números, underline (_) ou hífen (-)";
			}

			if (strlen($_FILES['NomeArquivo']['name']) > 100) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Nome do Arquivo com até 100 Caracateres ( atualmente com ".strlen($_FILES['NomeArquivo']['name'])." )";
			}

			$tamanhoArquivo = 30; 
			$Tamanho = $tamanhoArquivo * pow(10, 6);  // tamanho em MB

			if (($_FILES['NomeArquivo']['size'] > $Tamanho) || ($_FILES['NomeArquivo']['size'] == 0)) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Kbytes    = $tamanhoArquivo;
				$Kbytes    = (int) $Kbytes;
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes Mb";
			}
		}

		$DocumentoDescricao = strtoupper2(trim($DocumentoDescricao));

		if (strlen($DocumentoDescricao) > 200) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Observação do Documento com até 200 Caracteres ( atualmente com ". strlen($DocumentoDescricao) ." )";
		}

		if (strlen($DocumentoDescricao) < 1) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Observação do Documento incluído";
		}

		if ($Mens == 0) {
			$db = Conexao();

			$sql  = "SELECT MAX(CDOCLICODI) FROM SFPC.TBDOCUMENTOLICITACAO ";
			$sql .= "WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
			$sql .= "AND CCOMLICODI = $ComissaoCodigo AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";

			$result = $db->query($sql);

			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$arquivoOculto = false; //Informa se o arquivo deve ser oculto de usuários INTERNET

				if (($_FILES['NomeArquivo']['name'] != null) &&
					((preg_match("^ORCAMENTO\.XLS$", strtoupper2($_FILES['NomeArquivo']['name']))) ||
					(preg_match("^ORÇAMENTO\.XLS$", strtoupper2($_FILES['NomeArquivo']['name']))) ||
					(preg_match("^RESULTADO\.XLS$", strtoupper2($_FILES['NomeArquivo']['name']))))) {
					$arquivoOculto = true;

					$sql  = "SELECT TO_CHAR(ALICPOANOP,'FM9999')||'_'||TO_CHAR(CCENPOCORG, 'FM09')||'_'||TO_CHAR(CCENPOUNID, 'FM09')||'_'||TO_CHAR(CCOMLICODI, 'FM09')||'_'||TO_CHAR(CLICPOPROC, 'FM0999') ";
					$sql .= "FROM SFPC.TBLICITACAOPORTAL LIC";
					$sql .= " RIGHT OUTER JOIN (SELECT DISTINCT CORGLICODI, CCENPOUNID, CCENPOCORG FROM SFPC.TBCENTROCUSTOPORTAL) CEN ON CEN.CORGLICODI = LIC.CORGLICODI ";
					$sql .= "WHERE LIC.CLICPOPROC = $LicitacaoProcesso ";
					$sql .= "AND LIC.ALICPOANOP = $LicitacaoAno ";
					$sql .= "AND LIC.CCOMLICODI = $ComissaoCodigo ";
					$sql .= "AND LIC.CGREMPCODI = ".$_SESSION['_cgrempcodi_'];

					$result2 = $db->query($sql);
					$Linha = $result2->fetchRow();
					$CodDocumentoTCE = $Linha[0];

					$NomeArquivo = trim($_FILES['NomeArquivo']['name']);
					$NomeArquivo = str_replace('.', "_$CodDocumentoTCE.", $NomeArquivo);
					$NomeArquivo = strtoupper2($NomeArquivo);
				} else {
					$NomeArquivo = $_FILES['NomeArquivo']['name'];
				}

				$Linha = $result->fetchRow();
				$DocumentoCod = $Linha[0] + 1;
				$nomeArquivo = basename($_FILES['NomeArquivo']['name']);
				$extensao = substr($nomeArquivo, -4);
				$nomeArquivoNoServidor = "DOC".$_SESSION['_cgrempcodi_']."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocumentoCod.$extensao;
				$nomeArquivoUploads= "DOC".$_SESSION['_cgrempcodi_']."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocumentoCod.".pdf";
				# Insere na tabela de Documentos #
				$db->query("BEGIN TRANSACTION");

				$sql    = "INSERT INTO SFPC.TBDOCUMENTOLICITACAO ( ";
				$sql   .= "CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
				$sql   .= "CORGLICODI, CDOCLICODI, EDOCLINOME, TDOCLIDATA, ";
				$sql   .= "EDOCLIOBSE, CUSUPOCODI, TDOCLIULAT, fdocliexcl, EDOCLINOMS ";
				$sql   .= " ) VALUES ( ";
				$sql   .= "$LicitacaoProcesso, $LicitacaoAno, ".$_SESSION['_cgrempcodi_'].", $ComissaoCodigo, ";
				$sql   .= "$OrgaoLicitanteCodigo, $DocumentoCod, '".$NomeArquivo."','".date("Y-m-d")."', ";
				$sql   .= "'$DocumentoDescricao', ".$_SESSION['_cusupocodi_'].",'".date("Y-m-d H:i:s")."', 'N', '".$nomeArquivoNoServidor."' )";

				$result = $db->query($sql);

				if (PEAR::isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$tempName = $_FILES['NomeArquivo']['tmp_name'];
					$ArquivoDestino = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes2/".$nomeArquivoUploads;
					$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/DOC".$_SESSION['_cgrempcodi_']."_".$LicitacaoProcesso."_".$LicitacaoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$DocumentoCod;
			
					if (file_exists($Arquivo)) {
						unlink ($Arquivo);
					}
					if (file_exists($ArquivoDestino)) {
						unlink ($ArquivoDestino);
					}
				
					if (copy($tempName, $Arquivo)) {
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Documento Carregado com Sucesso";

						$str ="Comunicamos que houve inclusão do seguinte documento no Portal de Compras, referente ao ano ".$LicitacaoAno." e processo ".$LicitacaoProcesso.":\n\nARQUIVO:\n".$NomeArquivo."\n\nOBSERVAÇÃO:\n".$DocumentoDescricao;

						licitacaoEnviaEmailsLicitantes($db, $LicitacaoProcesso, $LicitacaoAno, $ComissaoCodigo, $OrgaoLicitanteCodigo, $_SESSION['_cgrempcodi_'], "Portal de Compras- Inclusão de documento em licitação", $str, !$arquivoOculto, TRUE);

						$db->query("COMMIT");
						$db->query("END TRANSACTION");

						$DocumentoDescricao = "";
					} else {
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "Erro no Carregamento do Arquivo";
						$db->query("ROLLBACK");
					}
					
					if (move_uploaded_file($tempName, $ArquivoDestino)) {
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Documento Carregado com Sucesso";
						$DocumentoDescricao = "";
					} else {
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem .= "Erro no Carregamento do Arquivo";
						$db->query("ROLLBACK");
					}
				}
			}
			
			$db->disconnect();
		}
	}
}

if ($Critica == "" and $ComissaoCodigo == "") {
	$Mensagem = urlencode("Verifique o tamanho do documento a ser enviado e tente novamente. O limite máximo é 5MB" );
	$Url = "CadDocumentoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";

	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
} else {
	# Pega o nome da Comissão #
	$db     = Conexao();

	$sql    = "SELECT A.ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO A WHERE A.CCOMLICODI = $ComissaoCodigo";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha             = $result->fetchRow();
		$ComissaoDescricao = $Linha[0];
	}

	$db->disconnect();
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
		document.Documento.Botao.value=valor;
		document.Documento.submit();
	}

	function ncaracteres(valor) {
		document.Documento.NCaracteres.value = '' +  document.Documento.DocumentoDescricao.value.length;

		if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
			document.Documento.NCaracteres.focus();
		}
	}

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form enctype="multipart/form-data" action="CadDocumentoManter.php" method="post" name="Documento">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Documento
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
  				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php echo ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
          					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           					MANTER - DOCUMENTO DE LICITAÇÃO
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal">
             					<p align="justify">
             						Para incluir o Documento, localize o arquivo, informe a observação e clique no botão "Incluir". Para apagar o(s) Documento(s), selecione-o(s), informe a observação e clique no botão "Excluir".<br/><br/>
             						<b>AVISO:</b> Ao incluir ou excluir arquivos, será enviado um email para todos os interessados do processo licitatório, com a observação informada.
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table border="0" summary="" width="100%">
              						<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
	              						<td class="textonormal"><?php echo $ComissaoDescricao; ?></td>
	            					</tr>
 									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo </td>
	              						<td class="textonormal"><?php echo $LicitacaoProcesso; ?></td>
	            					</tr>
	            					<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
	              						<td class="textonormal"><?php echo $LicitacaoAno; ?></td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Arquivo* </td>
	              						<td class="textonormal">
											<input type="file" name="NomeArquivo" class="textonormal">
										</td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" valign="top">Observação<br/>da inclusão ou<br/>Justificativa<br/>da exclusão*</td>
	              						<td class="textonormal">
	                						máximo de 200 caracteres
											<input type="text" name="NCaracteres" disabled size="3" value="<?php  echo $NCaracteres ?>" class="textonormal"><br>
	              							<textarea name="DocumentoDescricao" cols="40" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $DocumentoDescricao;?></textarea>
	              						</td>
	            					</tr>
									<tr>
	              						<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%" valign="top">Documentos Cadastrados </td>
										<td class="textonormal">
		              						<?php
											$db = Conexao();

											$sql = "SELECT	CDOCLICODI, EDOCLINOME, TDOCLIDATA, EDOCLIOBSE, FDOCLIEXCL, U.EUSUPORESP
													FROM  	SFPC.TBDOCUMENTOLICITACAO D, SFPC.TBUSUARIOPORTAL U
													WHERE	CLICPOPROC = $LicitacaoProcesso
															AND ALICPOANOP = $LicitacaoAno
															AND CCOMLICODI = $ComissaoCodigo
															AND d.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
															AND D.CUSUPOCODI = U.CUSUPOCODI
													ORDER BY TDOCLIDATA DESC, TDOCLIULAT DESC ";

											$result = $db->query($sql);

											if (PEAR::isError($result)) {
										    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											} else {
												while ($cols = $result->fetchRow()) {
													$Rows++;
													$dados[$Rows-1] = "$cols[0];$cols[1];$cols[2];$cols[3];$cols[4];$cols[5]";
												}

												if ($Rows > 0) {
													?>
													<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
														<tr>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTO</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">RESPONSÁVEL</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DATA<br/>CRIAÇÃO</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br/>JUSTIFICATIVA</td>
														</tr>
														<?php 
														for ($Row = 0 ; $Row < $Rows ; $Row++) {
															$Linha = explode(";",$dados[$Row]);
															$Data  = substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
															$itemCodigo = $Linha[0];
															$itemNome = $Linha[1];
															$itemObservacao = $Linha[3];
															$itemExcluido = $Linha[4];
															$itemAutor = $Linha[5];

															if ($itemExcluido == "S") {
																$itemNome="<s style='text-decoration:line-through;'>".$itemNome."</s> (excluído)";
															}
															?>
															<tr>
																<td class="textonormal"><input type=checkbox name="Documentos[<?php echo $Row?>]" value="<?php echo $itemCodigo?>" <?php if($itemExcluido == "S"){ echo "disabled"; } ?>></td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemNome?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemAutor?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $Data?>&nbsp;</td>
																<td class="textonormal" bgcolor="#F7F7F7"><?php echo $itemObservacao?>&nbsp;</td>
															</tr>
															<?php
														}
														?>
													</table>
													<?php
												} else {
													echo "Nenhum Documento Cadastrado!";
												}
											}

											$db->disconnect();
											?>
										</td>
	            					</tr>
            					</table>
								<input type="hidden" name="QuantArquivos" value="<?php echo $Rows?>">
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal" align="right">
								<input type="hidden" name="Critica" value="1">
								<input type="hidden" name="LicitacaoProcesso" value="<?php echo $LicitacaoProcesso?>">
								<input type="hidden" name="LicitacaoAno" value="<?php echo $LicitacaoAno?>">
								<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo?>">
								<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo?>">
            					<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
								<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            					<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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