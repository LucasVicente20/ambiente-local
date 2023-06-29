<?php
/**
 * Portal de Compras
 * 
 * Programa: CadGestaoDocumentos.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * Data:	 13/11/2018
 * Objetivo: Programa de atualização de documentos do SICREF Digital
 * --------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/08/2019
 * Objetivo: Tarefa Redmine 221257
 * --------------------------------------------------------------------------------------------
 */

// Acesso ao arquivo de funções
require_once("funcoesFornecedores.php");
require_once("funcoesDocumento.php");

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/fornecedores/CadIncluirCertidaoComplementar.php');
AddMenuAcesso('/fornecedores/CadIncluirGrupos.php');
AddMenuAcesso('/fornecedores/CadIncluirAutorizacao.php');
AddMenuAcesso('/fornecedores/RotVerificaEmail.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorOcorrencias.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorExcluir.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorHistorico.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorSuspenso.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorSelecionar.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');
AddMenuAcesso('/oracle/fornecedores/RotConsultaInscricaoMercantil.php');


// Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Origem            = $_POST['Origem'];
	$Destino           = $_POST['Destino'];
	// unset($_SESSION['Botao']);
	$_SESSION['Botao'] = $_POST['Botao'];
	$_SESSION['situacaoDoc1'] = $_POST['situacaoDoc1'];

	if ($Origem == "E") {
		// Variáveis do Formulário E
		$_SESSION['DDocumento']   = $_POST['DDocumento'];
		$_SESSION['tipoDoc']      = $_POST['tipoDoc'];
		$_SESSION['tipoDocDesc']  = $_POST['tipoDocDesc'];
		$_SESSION['obsDocumento'] = $_POST['obsDocumento'];
		$_SESSION['pesqAnoDoc']   = $_POST['pesqAnoDoc'];
		$_SESSION['CodDownload']  = $_POST['CodDownload'];
	}
} else {
	$_SESSION['Sequencial'] 	 = $_GET['Sequencial'];
	$_SESSION['InscricaoValida'] = $_GET['InscricaoValida'];
	$_SESSION['Irregularidade']	 = $_GET['Irregularidade'];
	$Origem						 = $_GET['Origem'];
	$Destino					 = $_GET['Destino'];
}

// Identifica o Programa para Erro de Banco de Dados
$_SESSION['ErroPrograma'] = __FILE__;

// Redireciona o programa de acordo com o botão
if ($_SESSION['Botao'] == "Voltar") {
	$_SESSION['Botao'] = "";
	$Url = "CadDocumentoSelecionar.php?Sequencial=" . $_SESSION['Sequencial'] . "&Programa=C";

	if (!in_array($Url, $_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: " . $Url);
	exit;
}

// Carrega as variáveis dos formulários
// var_dump($_SESSION['Botao']);die;
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$db = Conexao();
	// Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor
	$sql  = "SELECT	AFORCRCCGC, AFORCRCCPF, AFORCRIDEN, NFORCRORGU, NFORCRRAZS, ";
	$sql .= "		NFORCRFANT, CCEPPOCODI, CCELOCCODI, EFORCRLOGR, AFORCRNUME, ";
	$sql .= "		EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, ";
	$sql .= "		AFORCRTELS, AFORCRNFAX, NFORCRMAIL, AFORCRCPFC, NFORCRCONT, ";
	$sql .= "		NFORCRCARG, AFORCRDDDC, AFORCRTELC, AFORCRREGJ, DFORCRREGJ, ";
	$sql .= "		AFORCRINES, AFORCRINME, AFORCRINSM, VFORCRCAPS, VFORCRCAPI, ";
	$sql .= "		VFORCRPATL, VFORCRINLC, VFORCRINLG, DFORCRULTB, DFORCRCNFC, ";
	$sql .= "		NFORCRENTP, AFORCRENTR, DFORCRVIGE, AFORCRENTT, DFORCRGERA, ";
	$sql .= "		FFORCRCUMP, TFORCRULAT, FORN.CCOMLICODI, DFORCRANAL, FFORCRMEPP, ";
	$sql .= "		VFORCRINDI, VFORCRINSO, NFORCRMAI2, FFORCRTIPO, DFORCRCONT, ";
	$sql .= "		APREFOSEQU ";
	$sql .= "FROM	SFPC.TBFORNECEDORCREDENCIADO FORN ";
	$sql .= "		LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI ";
	$sql .= "WHERE	AFORCRSEQU = " . $_SESSION['Sequencial'];

	$result = executarTransacao($db, $sql);

	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		
		// Variáveis Formulário A
		$_SESSION['CNPJ']		  = $Linha[0];
		$_SESSION['CPF']		  = $Linha[1];
		$_SESSION['MicroEmpresa'] = $Linha[44];
		$_SESSION['Identidade']	  = $Linha[2];
		$_SESSION['OrgaoUF']	  = $Linha[3];
		$_SESSION['RazaoSocial']  = $Linha[4];
		$_SESSION['NomeFantasia'] = $Linha[5];

		if ($_SESSION['CNPJ'] != '') {
			$_SESSION['CPF_CNPJ'] = $_SESSION['CNPJ'];
		} else {
			$_SESSION['CPF_CNPJ'] = $_SESSION['CPF'];
		}

		if (strlen($_SESSION['CPF_CNPJ']) == 11) {
			$_SESSION['TipoCnpjCpf'] = 'CPF';
		} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
			$_SESSION['TipoCnpjCpf'] = 'CNPJ';
		}

		// Variáveis Formulário E
		$_SESSION['SequencialPre']	= $Linha[50];

		$db = Conexao();

		$sql  = "SELECT	DOC.CFDOCUSEQU, DOC.APREFOSEQU, DOC.AFORCRSEQU, DOC.AFDOCUANOA, DOC.CFDOCTCODI, ";
		$sql .= "		DOC.EFDOCUNOME, DOC.IFDOCUARQU, DOC.FFDOCUFORN, DOC.TFDOCUANEX, DOC.FFDOCUSITU, ";
		$sql .= "		DOC.CUSUPOCODI, DOC.TFDOCTULAT, ";
		$sql .= "		(SELECT H.CFDOCSCODI ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS SITUACAO, ";
		$sql .= "		(SELECT H.EFDOCHOBSE ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS OBSERVACAO, ";
		$sql .= "		T.EFDOCTDESC, ";
		$sql .= "		(SELECT H.CUSUPOCODI ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS USUARIOULTIMAALT, ";
		$sql .= "		(SELECT U.EUSUPORESP ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "				JOIN SFPC.TBUSUARIOPORTAL U ON H.CUSUPOCODI = U.CUSUPOCODI ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS NOMEUSUULTIMAALT, ";
		$sql .= "		(SELECT H.TFDOCHULAT ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS DATAHORAULTIMAALT ";
		$sql .= "FROM	SFPC.TBFORNECEDORDOCUMENTO DOC ";
		$sql .= "		JOIN SFPC.TBFORNECEDORDOCUMENTOTIPO T ON T.CFDOCTCODI = DOC.CFDOCTCODI ";
		$sql .= "WHERE	AFORCRSEQU = " . $_SESSION['Sequencial'];
			
				if ($_SESSION['SequencialPre']) {
					$sql .= " OR APREFOSEQU = " . $_SESSION['SequencialPre'];
				}

		$sql .= "		AND FFDOCUSITU = 'A' ";
		$sql .= "ORDER BY TFDOCTULAT DESC ";

		$result = executarTransacao($db,$sql);

		if (PEAR::isError($result)) {
			ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
		} else {
			// unset($_SESSION['Arquivos_Upload']);

			while ($linha = $result->fetchRow()) {
				$nomeUsuAnex   = '';
				$nomeUsuUltAlt = '';

				if ($linha[7] == 'S') {
					if ($_SESSION['CPF_CNPJ'] != "") {
						if (strlen($_SESSION['CPF_CNPJ']) == 14) {
							$nomeUsuAnex = substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
						} else {
							$nomeUsuAnex = substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
						}
					}

					// Usuário que fez a última alteração
					if ($linha[15] > 0) {
						$nomeUsuUltAlt = 'PCR - ' . $linha[16];
					} else {
						$nomeUsuUltAlt = $nomeUsuAnex;
					}
				} else {
					$nomeUsuAnex = 'PCR - ' . $linha[18];

					// Usuário que fez a última alteração
					$nomeUsuUltAlt = 'PCR - ' . $linha[16];
				}

				$_SESSION['Arquivos_Upload']['nome'][]              = $linha[5];
				$_SESSION['Arquivos_Upload']['situacao'][]          = 'existente'; // situacao pode ser: novo, existente, cancelado e excluido
				$_SESSION['Arquivos_Upload']['codigo'][]            = $linha[0]; // como é um arquivo novo, ainda nao possui código
				$_SESSION['Arquivos_Upload']['tipoCod'][]           = $linha[4]; 
				$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $linha[14]; 
				$_SESSION['Arquivos_Upload']['observacao'][]        = $linha[13]; 
				$_SESSION['Arquivos_Upload']['conteudo'][]          = $linha[6]; 
				$_SESSION['Arquivos_Upload']['anoAnex'][]           = $linha[3];
				$_SESSION['Arquivos_Upload']['dataHora'][]          = formatarDataHora($linha[8]); 
				$_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][]  = $linha[15];
				$_SESSION['Arquivos_Upload']['usuarioUltAlt'][]     = $nomeUsuUltAlt;
				$_SESSION['Arquivos_Upload']['usuarioAnex'][]       = $nomeUsuAnex;
				$_SESSION['Arquivos_Upload']['externo'][]           = $linha[7];
				$_SESSION['Arquivos_Upload']['dataHoraUltAlt'][]    = formatarDataHora($linha[17]); 
				$_SESSION['Arquivos_Upload']['situacaoHist'][]      = $linha[12];
			}
		}
	}
} else {
	if ($Origem == "E" ) {
		$DDocumento = $_SESSION['DDocumento'];

		if ($_SESSION['Botao'] == "Limpar") {
			$_SESSION['Botao']        = "";
			$_SESSION['tipoDoc']      = 0;
			$_SESSION['obsDocumento'] = "";
		} elseif ($_SESSION['Botao'] == 'IncluirDocumento') {
			$Mens     = "";
			$Mensagem = "Informe: ";
			if ($_POST['tipoDoc'] == '0' ) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Tipo do documento deve ser preenchido";
			} else {
				$db = Conexao();

				$parametrosGerais = dadosParametrosGerais($db);
				$tamanhoArquivo     = $parametrosGerais[4];
				$tamanhoNomeArquivo = $parametrosGerais[5];
				$extensoesArquivo   = $parametrosGerais[6];

				if ($_FILES['Documentacao']['tmp_name']) {
					$_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);

					$extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';
					$extensoes = explode(',', strtolower2($extensoesArquivo));

					array_push($extensoes, '.zip', '.xlsm');

					$noExtensoes = count($extensoes);
					$isExtensaoValida = false;

					for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
						if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
							$isExtensaoValida = true;
						}
					}

					if (! $isExtensaoValida) {
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
					}

					if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
						if ($Mens == 1) {
							$Mensagem.= ', ';
						}

						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
					}

					$Tamanho = 5120 * 1000;

					if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
						if ($Mens  == 1) {
							$Mensagem .= ', ';
						}

						$Kbytes = $Tamanho;
						$Kbytes = (int) $Kbytes;

						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: 5 MB";
					}

					if ($Mens == '') {
						
						if (($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name'])) == false) {
							$Mens     = 1;
							$Tipo     = 2;
							$Mensagem = 'Caminho da Documentação Inválido';
						} else {
							$_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
							$_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
							$_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
							$_SESSION['Arquivos_Upload']['tipoCod'][] = $_POST['tipoDoc'];
							$_SESSION['Arquivos_Upload']['anoAnex'][] = $_POST['anoDoc'];
							$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc'];
							$_SESSION['Arquivos_Upload']['observacao'][] = strtoupper2($_POST['obsDocumento']);
							$_SESSION['Arquivos_Upload']['dataHora'][] = date('d/m/Y H:i');
							$_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][] = $_SESSION['_cusupocodi_'];
							$_SESSION['Arquivos_Upload']['usuarioUltAlt'][] = '';
							$_SESSION['Arquivos_Upload']['dataHoraUltAlt'][] = date('d/m/Y H:i');
							$_SESSION['Arquivos_Upload']['situacaoHist'][] = 2;
							$_SESSION['tipoDoc'] = 0;
							$_SESSION['obsDocumento'] = "";
						}
					}
					
				} else {
					$Mens     = 1;
					$Tipo     = 2;
					$Mensagem = 'Falta anexar o documento';
				}
			}
	} elseif ($_POST['Botao'] == 'RetirarDocumento') {
		if ($DDocumento) {
			foreach ($DDocumento as $valor) {
				if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
					$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				}
			}
		} else {
			$Mens     = 1;
			$Tipo     = 2;
			$Mensagem = 'Selecione um anexo para ser retirado';
		}
	} elseif ($_POST['Botao'] == 'PesquisaAnoDoc') {
		//Espaço para futuras críticas, caso existam.
	} elseif ($_POST['Botao'] == 'Download') {
		$docDown = $_SESSION['Arquivos_Upload'];

		$qtdup = count($docDown['conteudo']);
			
		for ($arqC = 0; $arqC < $qtdup; ++ $arqC) {
			if ($_SESSION['CodDownload'] == $arqC) {
				$arrNome = explode('.',$docDown['nome'][$arqC]);
				$extensao = $arrNome[1];

				$mimetype = 'application/octet-stream';
					
				header('Content-type: '.$mimetype);
				header('Content-Disposition: attachment; filename='.$docDown['nome'][$arqC] );
				header('Content-Transfer-Encoding: binary');
				header('Pragma: no-cache');

				echo pg_unescape_bytea($docDown['conteudo'][$arqC]);

				die();
			}
		}
	}
}

if ($Origem == "E") {
	var_dump($_SESSION['Arquivos_Upload']['nome']);	
		// DOCUMENTOS
		if (count($_SESSION['Arquivos_Upload']) != 0) {
			
			for ($i=0; $i<= count($_SESSION['Arquivos_Upload']); $i++) {	
				var_dump($_SESSION['Arquivos_Upload']['nome'][$i]);		
				if ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo') {
					// var_dump($_SESSION['Arquivos_Upload']['nome'][$i]);die;
					// fazer sql para trazer o sequencial
					$sql = "SELECT CFDOCUSEQU FROM SFPC.TBFORNECEDORDOCUMENTO WHERE 1 = 1 ORDER BY CFDOCUSEQU DESC LIMIT 1";

					$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;

					$anexo =  bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);

					$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
						(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
						VALUES(".$seqDocumento.", NULL, ".$_SESSION['Sequencial'].",".$_SESSION['Arquivos_Upload']['anoAnex'][$i].", ".$_SESSION['Arquivos_Upload']['tipoCod'][$i].", '".$_SESSION['Arquivos_Upload']['nome'][$i]."', decode('".$anexo."','hex'), 'N', now(), 'A', ".$_SESSION['_cusupocodi_'].", now());
						";
		
					$resultAnexo = executarTransacao($db, $sqlAnexo);
					print_r($resultAnexo);
					if (PEAR::isError($resultAnexo)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
					} else {
						// Insere a fase do documento
						$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$seqDocumento.", 2, '".$_SESSION['Arquivos_Upload']['observacao'][$i]."', now(), ".$_SESSION['_cusupocodi_'].", now()); ";
						$resultHist = executarTransacao($db,$sqlHist);
	
						if (PEAR::isError($resultHist)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
						}
					}
				} elseif ($_SESSION['Arquivos_Upload']['situacao'][$i] == 'excluido') {
					// Exclui todos os documentos antes de inserir os novos
					$sqlDelete  = 'Delete FROM SFPC.tbfornecedordocumento FD ';
					$sqlDelete .= ' where FD.cfdocusequ = '.$_SESSION['Arquivos_Upload']['codigo'][$i];

					$resultDel = executarTransacao($db,$sqlDelete);

					if (PEAR::isError($resultDel)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDelete");
					}	
				} elseif($_SESSION['Arquivos_Upload']['situacao'][$i] == 'existente') {
					if (($_POST['situacaoDoc'.$i] != $_SESSION['Arquivos_Upload']['situacaoDoc'][$i])) {
						//insere a fase do documento
						$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$_SESSION['Arquivos_Upload']['codigo'][$i].", '".$_POST['situacaoDoc'.$i]."', '".$_POST['obsDocumento'.$i]."', now(), ".$_SESSION['_cusupocodi_'].", now()); ";

						$resultHist = executarTransacao($db,$sqlHist);
	
						if (PEAR::isError($resultHist)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
						}
					}
				}
			}
			// die;
		}

		$db->query("COMMIT");

		//LimparSessao();
	

	$db->query("END TRANSACTION");
	$db->disconnect();
	}
}

?>
<html>
<?php
// Carrega o layout padrão
layout();
?>
<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
<script language="JavaScript" type="">
	<!--
	function Submete(Destino) {
		document.CadGestaoDocumentos.Destino.value = Destino;
		document.CadGestaoDocumentos.submit();
	}

	function enviar(valor) {
		document.CadGestaoDocumentos.Botao.value = valor;
		document.CadGestaoDocumentos.submit();
	}
	function enviarAlterar() {
		// document.CadGestaoDocumentos.Botao.value = valor;
		document.CadGestaoDocumentos.submit();
	}

	function baixarArquivo(cod) {
		document.CadGestaoDocumentos.CodDownload.value = cod;
		enviar('Download');
	}

	function remeter(valor) {
		document.CadGestaoDocumentos.Destino.value = 'E';
		document.CadGestaoDocumentos.Botao.value = valor;
		document.CadGestaoDocumentos.submit();
	}

	function preencheTipoDocDesc() {
		$('#tipoDocDesc').val($('#tipoDoc option:selected').text());
	}

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoDocumentos.php" method="post" name="CadGestaoDocumentos" enctype="multipart/form-data">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a> > Fornecedores > Inscrição > Atualização de Documentos
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<tr>
				<td width="100"></td>
				<td align="left" colspan="2">
					<?php
					if ($Mens != 0) {			
						ExibeMens($Mensagem, $Tipo, $Mens);
					}
					?>
				</td>
			</tr>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">ATUALIZAÇÃO DE DOCUMENTOS</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">Informe os dados abaixo e clique no botão "Atualizar".<br><br>Tamanho máximo para upload de arquivo: 5 MB.</p>
										</td>
									</tr>
									<tr>
										<td align="left">
											<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
												<tr bgcolor="#bfdaf2">
													<td colspan="4">
														<table class="textonormal" border="0" align="left" summary="">
															<tr>
																<td class="textonormal" height="20" width="40%">
																	<?php
																	if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
																		echo "CNPJ";
																	} else {
																		echo "CPF";
																	}
																	?>
																</td>
																<td class="textonormal">
																	<?php
																	if ($_SESSION['CPF_CNPJ'] != "") {
																		if (strlen($_SESSION['CPF_CNPJ']) == 14) {
																			echo substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
																		} else {
																			echo substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
																		}
																	}
																	?>
																</td>
															</tr>
															<tr>
																<td class="textonormal" height="20">
																	<?php
																	if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
																		echo "Razão Social*\n";
																	} else {
																		echo "Nome*\n";
																	}
																	?>
																</td>
																<td class="textonormal">
																	<?php echo $_SESSION['RazaoSocial'] ?>
																</td>
															</tr>
															<tr>
																<td class="textonormal">Nome Fantasia </td>
																<td class="textonormal">
																	<?php echo $_SESSION['NomeFantasia'] ?>
																</td>
															</tr>
															<tr>
																<td class="textonormal">Ano de anexação*</td>
																<td class="textonormal">
																	<select name="anoDoc" id="anoDoc" class="tamanho_campo textonormal" >
																		<?php
																		$arr = array();

																		for ($j = date(Y); $j > 2015; $j--) {
																			$arr[] = $j;
																		}
	
																		foreach ($arr as $value) {
																			if ($value == $_SESSION['pesqAnoDoc']) {
																				echo '<option value="'.$value.'" selected>'.$value.'</option>';
																			} else {
																				echo '<option value="'.$value.'">'.$value.'</option>';
																			}
																		}
																		?>
																	</select>
																</td>
															</tr>
															<tr>
																<td class="textonormal">Documento*<span style="font-style:italic; font-size:10px;"> (Tamanho máximo: 5 MB)</span></td>
																<td class="textonormal">
																	<input type="file" name="Documentacao" class="textonormal" />
																</td>
															</tr>
															<tr>
																<td class="textonormal">Tipo de Documento*</td>
																<td class="textonormal">
																	<select name="tipoDoc" id="tipoDoc" class="tamanho_campo textonormal" onchange="preencheTipoDocDesc()">
																		<option value="0">Selecione um tipo de documento...</option>
																		<?php
																		$db = Conexao();

																		$sql  = "SELECT	CFDOCTCODI, EFDOCTDESC, FFDOCTOBRI ";
																		$sql .= "FROM 	SFPC.TBFORNECEDORDOCUMENTOTIPO ";
																		$sql .= "WHERE	FFDOCTSITU = 'A' ";
																		$sql .= "ORDER BY AFDOCTORDE, EFDOCTDESC ";

																		$res = executarTransacao($db,$sql);

																		if (PEAR::isError($res)) {
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		} else {
																			while ($tipoDoc = $res->fetchRow()) {
																				$docObrigatorio = '';

																				if ($tipoDoc[2] == 'S') {
																					$docObrigatorio = ' (Obrigatório)';
																				}

																				if ($tipoDoc[0] == $_SESSION['tipoDoc']) {
																					?>
																					<option value="<?php echo $tipoDoc[0]; ?>" selected><?php echo $tipoDoc[1].$docObrigatorio; ?></option>
																					<?php
																				} else {
																					?>
																					<option value="<?php echo $tipoDoc[0]; ?>"><?php echo $tipoDoc[1].$docObrigatorio; ?></option>
																					<?php
																				}	
																			}
																		}
																		?>
																	</select>
																	<input type="hidden" id="tipoDocDesc" name="tipoDocDesc" value="<?php echo $_SESSION['tipoDocDesc'] ?>">
																</td>
															</tr>
															<tr>
																<td class="textonormal">Observação</td>
																<td class="textonormal">
																	<font class="textonormal">máximo de 500 caracteres</font>
																	<input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
																	<textarea id="obsDocumento" name="obsDocumento" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo $_SESSION['obsDocumento'] ?></textarea>
																	<script language="javascript" type="">
																		function CaracteresObservacao(valor) {
																			CadGestaoDocumentos.NCaracteres.value = '' +  CadGestaoDocumentos.obsDocumento.value.length;
																		}
																	</script>
																</td>
																<td valign="bottom">
																	<input type="button" value="Incluir Documento" class="botao" onclick="javascript:enviar('IncluirDocumento');">		
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<?php
												// Verifica a qtd de docs válidos
												$qtd_valida = 0;

												$DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

												for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
													if (($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente')) {
														$qtd_valida++;
													}
												}

												if ($qtd_valida > 0) {
													?>
													<tr>
					              						<td class="textonormal" colspan="4">
															<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
																<tr>
					              									<td bgcolor="#75ADE6" class="textoabasoff" colspan="9" align="center">DOCUMENTOS ANEXADOS</td>
					              								</tr>
																<tr>
																	<td class="textonormal" colspan="9">
																		Ano da anexação:
																		<select name="pesqAnoDoc" id="pesqAnoDoc" class="tamanho_campo textonormal" onChange="javascript:enviar('PesquisaAnoDoc');">
																			<?php
																			$arr  = array();
																			$anos = array();

																			$total_doc = count($_SESSION['Arquivos_Upload']['conteudo']);

																			for ($c = 0; $c < $total_doc; ++ $c) {
																				//$dataHora = $_SESSION['Arquivos_Upload']['dataHora'][$c];
																				//$arrDataHora = explode(' ',$dataHora);
																				//$arrData = explode('/',$arrDataHora[0]);
																				$anoDoDocumento = $_SESSION['Arquivos_Upload']['anoAnex'][$c];
																				$anos[] = $anoDoDocumento;
																			}

																			for ($j = date(Y); $j > 2000; $j--) {
																				if (in_array($j, $anos)) {
																					$arr[] = $j;
																				}
																			}

																			foreach ($arr as $value) {
																				if ($value == $_SESSION['pesqAnoDoc']) {
																					echo '<option value="'.$value.'" selected>'.$value.'</option>';
																				} else {
																					echo '<option value="'.$value.'">'.$value.'</option>';
																				}
																			}
																			?>
																		</select>
																	</td>
																</tr>  
        			          									<tr>
					              									<td bgcolor="#bfdaf2" align="center"></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Tipo do documento</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Nome</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Responsável anexação</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Data/Hora Anexação</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Situação</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Observação</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Responsável última alteração</b></td>
																	<td bgcolor="#bfdaf2" align="center"><b>Data/Hora última alteração</b></td>
					              								</tr>
																<?php
																$sql  = "SELECT	EUSUPORESP ";
																$sql .= "FROM 	SFPC.TBUSUARIOPORTAL ";
																$sql .= "WHERE	CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
																$sql .= "LIMIT 1";

																$nome_usuario = resultValorUnico(executarTransacao($db, $sql));
																
																$DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

																for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) { // abaco
																	if ($_SESSION['pesqAnoDoc']) {
																		$pesqAnoDoc = $_SESSION['pesqAnoDoc'];
																	} else {
																		$pesqAnoDoc = date('Y');
																	}

																	if (($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') && ($_SESSION['Arquivos_Upload']['anoAnex'][$Dcont] == $pesqAnoDoc)) {
																		?>
																		<tr>
																			<td align='center' width='5%' bgcolor="#ffffff"><input type='checkbox' name='DDocumento[<?php echo $Dcont?>]' value='<?= $Dcont?>'></td>
																			<td class='textonormal' bgcolor='#ffffff'>
																				<?= $_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][$Dcont] ?>
																			</td>
																			<!--nome-->
																			<td class='textonormal' bgcolor="#ffffff">
																				<a href='javascript: baixarArquivo(<?= $Dcont?>);'><?= $_SESSION['Arquivos_Upload']['nome'][$Dcont] ?></a>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<?php
																				if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente') {
																					if ($_SESSION['Arquivos_Upload']['externo'][$Dcont]=='S') {
																						echo $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont];
																					} else {
																						echo 'PCR - '.$nome_usuario;
																					}
																				} else {
																					if ($nome_usuario) {
																						echo 'PCR - '.$nome_usuario;
																					} else {
																						echo '-';
																					}
																				}
																				?>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<?= $_SESSION['Arquivos_Upload']['dataHora'][$Dcont] ?>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<select name="situacaoDoc<?= $Dcont?>" id="situacaoDoc<?= $Dcont?>" class="tamanho_campo textonormal" >
																					<?php
																					$db = Conexao();
																					$sql  = "SELECT	CFDOCSCODI, EFDOCSDESC ";
																					$sql .= "FROM 	SFPC.TBFORNECEDORDOCUMENTOSITUACAO ";
																					$sql .= "WHERE	FFDOCSSITU = 'A' ";
																					$sql .= "ORDER BY CFDOCSCODI ";

																					$res = executarTransacao($db, $sql);
																					if (PEAR::isError($res)) {
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					} else {
																						while ($arrsitu = $res->fetchRow()) {
																							$situacao =0;

																							if(!empty($_POST['situacaoDoc' . $Dcont])){
																								if($_SESSION['Arquivos_Upload']['situacaoHist'][$Dcont] == $_POST['situacaoDoc' . $Dcont]){
																									$situacao = $_SESSION['Arquivos_Upload']['situacaoHist'][$Dcont];
																								}else{	
																									if(!empty( $_SESSION['Arquivos_Upload']['situacaoHist'][$Dcont])){
																									   $situacao = $_POST['situacaoDoc' . $Dcont];
																									}else{
																										$situacao =2;
																									}
																								}
																							}else{
																								$situacao = $_SESSION['Arquivos_Upload']['situacaoHist'][$Dcont];
																							}
																							// if ($arrsitu[0] == $_POST['situacaoDoc' . $Dcont]) {
																							// 	?>
																							<!-- // 	<option value="<?php echo $arrsitu[0]; ?>" selected><?php echo $arrsitu[1]; ?>Eliakim</option> -->
																							// 	<?php
																							// } else {
																								v
																								?>
																								<option 
																												value="<?php echo $arrsitu[0]; ?>" 
																												<?php if($situacao == $arrsitu[0]){echo "selected";}?> 
																												<?php if(empty($situacao) && $arrsitu[0] == 2){echo "selected";}?>
																								>
																										<?php echo $arrsitu[1]; ?>
																								</option>
																								<?php
																							// }
																						}
																					}
																					?>
																				</select>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<input type='text' name='obsDocumento<?php echo $Dcont?>' id='obsDocumento<?php echo $Dcont?>' value='<?php echo $_SESSION['Arquivos_Upload']['observacao'][$Dcont] ?>'>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<?php
																				if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente') {
																					echo $_SESSION['Arquivos_Upload']['usuarioUltAlt'][$Dcont]; 
																				} else {
																					echo 'PCR - '.$nome_usuario;
																				}
																				?>
																			</td>
																			<td class='textonormal' bgcolor="#ffffff">
																				<?= $_SESSION['Arquivos_Upload']['dataHoraUltAlt'][$Dcont] ?>
																			</td>
																		</tr>
																		<?php
																		//$_SESSION['Arquivos_Upload'] =  $_SESSION['Arquivos_Upload']['nome'][$Dcont];
																		//addArquivoAcesso($_SESSION['Arquivos_Upload']);
																	}
																}
					              								?>
					              						<tr>
					              							<td class="textonormal" colspan="9" align="right">
																<input class="botao" type="button" value="Retirar Documento" onclick="javascript:enviar('RetirarDocumento');">
					              							</td>
					              						</tr>
					              				</table>
					              			</td>
					            		</tr>
										<?php
									}
									?>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="4" align="right">
								<input type="hidden" name="Critica" size="1" value="2">
								<input type="hidden" name="Origem" value="E">
								<input type="hidden" name="CodDownload" id="CodDownload" >
								<input type="hidden" name="Destino" value="E">
								<input type="hidden" name="EmailPopup" value="<?php echo $_SESSION['EmailPopup'];?>">
		            			<input type="button" value="Atualizar" class="botao" onclick="javascript:enviarAlterar();">
								<!--<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">-->
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
	<script language="JavaScript" type="">
		<!--
		document.CadGestaoDocumentos.CPF_CNPJ.focus()
		//-->
	</script>
</body>
</html>