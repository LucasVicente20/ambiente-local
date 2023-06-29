<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialEspecificacaoTecnicaManter.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Objetivo: Programa de Inclusão/Exclusão das Arquivos com Especificação Técnica
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     14/01/2019
# Objetivo: Tarefa Redmine 77809
# ------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/materiais/CadMaterialEspecificacaoTecnicaSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica          = $_POST['Critica'];
	$Botao            = $_POST['Botao'];
	$Arquivos         = $_POST['Arquivos'];
	$QuantArquivos    = $_POST['QuantArquivos'];
	$GrupoCodigo      = $_POST['GrupoCodigo'];
	$ClasseCodigo     = $_POST['ClasseCodigo'];
	$ArquivoDescricao = $_POST['ArquivoDescricao'];
    $ItemCodigo       = $_POST['ItemCodigo'];
    $TipoItem         = $_POST['TipoItem'];
} else {
	$GrupoCodigo  = $_GET['GrupoCodigo'];
	$ClasseCodigo = $_GET['ClasseCodigo'];
    $ItemCodigo   = $_GET['ItemCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadMaterialEspecificacaoTecnicaManter.php";

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
	if ($QuantArquivos > 0) {
		$db = Conexao();

		for ($Row = 0 ; $Row < $QuantArquivos ; $Row++) {
			if ($Arquivos[$Row] != "") {
				$db->query("BEGIN TRANSACTION");

				$sql  = "DELETE FROM SFPC.TBESPECIFICACAOTECNICA ";
				$sql .= " WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo ";
				$sql .= "   AND CESPTMCODI = " . $Arquivos[$Row];

				$result = $db->query($sql);

				if (PEAR::isError($result)) {
					$db->query("ROLLBACK");
    				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."materiais/ESPECIFICACAO_".$GrupoCodigo."_".$ClasseCodigo."_".$Arquivos[$Row];

					if (file_exists($Arquivo)) {
						if (unlink($Arquivo)) {
							$Mens = 1;
							$Tipo = 1;
							$Mensagem = "Especificação(ões) Excluída(s) com Sucesso";
						} else {
							$Mens = 1;
							$Tipo = 2;
							$Mensagem = "Erro na Exclusão do Arquivo";
						}
					} else {
						$Mens = 1;
						$Tipo = 1;
						$Mensagem = "Especificação(ões) Excluída(s) com Sucesso";
						$Arquivos = "";
					}

					$db->query("COMMIT");
					$db->query("END TRANSACTION");
				}
			}
		}

		$db->disconnect();
	}
} elseif ($Botao == "Voltar") {
	header("location: CadMaterialEspecificacaoTecnicaSelecionar.php");
	exit();
} else {
	if ($Critica == 1) {
		$Mensagem = "Informe: ";
		$_FILES['NomeArquivo']['name'] = RetiraAcentos($_FILES['NomeArquivo']['name']);
		$ArquivoDescricao = strtoupper(trim($ArquivoDescricao));

		if (strlen($ArquivoDescricao) > 200 or strlen($ArquivoDescricao) == 0) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Observação das Especificações com até 200 Caracteres ( atualmente com ". strlen($ArquivoDescricao) ." )";
		}

		$Tam = strlen($_FILES['NomeArquivo']['name']);

		if (strlen($_FILES['NomeArquivo']['name']) > 100) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Nome do Arquivo com até 100 Caracateres ( atualmente com ".strlen($_FILES['NomeArquivo']['name'])." )";
		}

		$Tamanho = 5242880; /* 5MB */

		if (($_FILES['NomeArquivo']['size'] > $Tamanho ) || ($_FILES['NomeArquivo']['size'] == 0)) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens = 1;
			$Tipo = 2;
			$Kbytes = $Tamanho/1024;
			$Kbytes = (int) $Kbytes;
			$Mensagem = "Este arquivo é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
		}

		if ($Mens == 0) {
			$db = Conexao();

			$sql  = "SELECT MAX(CESPTMCODI) FROM SFPC.TBESPECIFICACAOTECNICA ";
			$sql .= " WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo ";

			$result = $db->query($sql);

			if (PEAR::isError($result)) {
	    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$Linha = $result->fetchRow();
				$ArquivoCodigo = $Linha[0] + 1;

				$field = ($TipoItem == 'M') ? 'CMATEPSEQU' : 'CSERVPSEQU';

				# Insere na tabela de Atas do Registro de Preço #
				$db->query("BEGIN TRANSACTION");

				$sql  = "INSERT INTO SFPC.TBESPECIFICACAOTECNICA( ";
				$sql .= "       CGRUMSCODI, CCLAMSCODI, CESPTMCODI, EESPTMNOME, EESPTMOBSE, ";
				$sql .= "       CGREMPCODI, CUSUPOCODI, TESPTMULAT, $field ";
				$sql .= "       ) VALUES ( ";
				$sql .= "$GrupoCodigo, $ClasseCodigo, $ArquivoCodigo, '".$_FILES['NomeArquivo']['name']."', '$ArquivoDescricao', ";
				$sql .= "".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', $ItemCodigo )";

				$result   = $db->query($sql);

				if (PEAR::isError($result)) {
					$db->query("ROLLBACK");
    				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					$Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."materiais/ESPECIFICACAO_".$GrupoCodigo."_".$ClasseCodigo."_".$ArquivoCodigo;

					if (file_exists($Arquivo)) {
						unlink ($Arquivo);
					}

					if (@move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $Arquivo)) {
						$Mens             = 1;
						$Tipo             = 1;
						$Mensagem         = "Especificação Carregada com Sucesso";
						$ArquivoDescricao = "";
						$db->query("COMMIT");
					} else {
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "Erro no Carregamento do Arquivo";
						$db->query("ROLLBACK");
					}
				}

				$db->query("END TRANSACTION");
			}

			$db->disconnect();
		}
	}
}

$db = Conexao();

$sql = "SELECT EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Linha = $result->fetchRow();
	$GrupoDescricao = $Linha[0];
}

$sql = "SELECT ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo";

$result = $db->query($sql);

if (PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	$Linha = $result->fetchRow();
	$ClasseDescricao = $Linha[0];
}

if (!empty($ItemCodigo)) {
    $sql_grupo = "SELECT FGRUMSTIPO FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";

	$result_grupo = $db->query($sql_grupo);
    $result_grupo = resultValorUnico($result_grupo);

	$tipoTexto = ($result_grupo) == 'M' ? 'Material' : 'Serviço';

    if ($result_grupo == 'M') {
        $sql = "SELECT MP.EMATEPDESC FROM SFPC.TBMATERIALPORTAL MP WHERE MP.CMATEPSEQU = $ItemCodigo ";
    } else {
        $sql = "SELECT ESERVPDESC FROM SFPC.TBSERVICOPORTAL WHERE CSERVPSEQU = $ItemCodigo";
    }

    $result = $db->query($sql);
    $item = resultValorUnico($result);
}

$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.MaterialPrecoEspecificacaoTecnica.Botao.value=valor;
		document.MaterialPrecoEspecificacaoTecnica.submit();
	}

	function ncaracteres(valor) {
		document.MaterialPrecoEspecificacaoTecnica.NCaracteres.value = '' +  document.MaterialPrecoEspecificacaoTecnica.ArquivoDescricao.value.length;
	
		if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
			document.MaterialPrecoEspecificacaoTecnica.NCaracteres.focus();
		}
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form enctype="multipart/form-data" action="CadMaterialEspecificacaoTecnicaManter.php" method="post" name="MaterialPrecoEspecificacaoTecnica">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Especificação Técnica
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
  					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal"><br>
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        				<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	       						MANTER - ESPECIFICAÇÃO TÉCNICA
							</td>
						</tr>
        				<tr>
							<td class="textonormal">
								<p align="justify">
									Para incluir a Especificação Técnica, localize o arquivo e clique no botão "Incluir". Para apagar a(s) Especificação(ões) Técnica(s), selecione-a(s) e clique no botão "Excluir".
								</p>
							</td>
						</tr>
						<tr>
							<td>
								<table border="0" summary="">
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo </td>
										<td class="textonormal"><?php echo $GrupoDescricao; ?></td>
	            					</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe </td>
										<td class="textonormal"><?php echo $ClasseDescricao; ?></td>
	            					</tr>
                					<?php
									if(!empty($ItemCodigo)) {
										?>
                						<tr>
                    						<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo $tipoTexto; ?> </td>
                    						<td class="textonormal"><?php echo $item; ?></td>
                						</tr>
                						<?php
									}
									?>
	            					<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição </td>
										<td class="textonormal"><font class="textonormal">máximo de 200 caracteres</font>
											<input type="text" name="NCaracteres" disabled size="3" value="" class="textonormal"><br>
											<textarea name="ArquivoDescricao" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal">
												<?php echo $ArquivoDecricao;?>
											</textarea>
	            					</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Arquivo* </td>
										<td class="textonormal">
											<input type="file" name="NomeArquivo" class="textonormal">
											<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo?>">
											<input type="hidden" name="ClasseCodigo" value="<?php echo $ClasseCodigo?>">
                    						<input type="hidden" name="ItemCodigo" value="<?php echo $ItemCodigo ?>">
                    						<input type="hidden" name="TipoItem" value="<?php echo $result_grupo ?>">
											<input type="hidden" name="Critica" value="1">
										</td>
	            					</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" colspan="2">
											<table border="1" width="100%" summary="" valign="top" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6">
												<?php
												$db = Conexao();

												$sql  = "SELECT CESPTMCODI, EESPTMOBSE, TO_CHAR(TESPTMULAT,'DD/MM/YYYY'), EESPTMNOME ";
												$sql .= "  FROM SFPC.TBESPECIFICACAOTECNICA ";
												$sql .= " WHERE CGRUMSCODI = $GrupoCodigo AND CCLAMSCODI = $ClasseCodigo ";

												$result = $db->query($sql);
					
												if (PEAR::isError($result)) {
	  												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												} else {
													$Rows = $result->numRows();

													if ($Rows > 0) {
														echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"2\"><b>ESPECIFICAÇÕES TÉCNICAS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\"><b>ARQUIVO</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\"><b>DATA</td></tr>\n";

														while ($Linha = $result->fetchRow()) {
															$cont++;
															$row  = $cont-1;
								
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" valign=\"middle\"><input type=checkbox name=\"Arquivos[$row]\" value=\"".$Linha[0]."\"></td><td class=\"textonormal\">$Linha[1]</td><td class=\"textonormal\" valign=\"middle\">$Linha[3]</td><td class=\"textonormal\" valign=\"middle\">$Linha[2]<br> </td> \n";
															echo "</tr>\n";
														}
													} else {
														echo "<tr>\n";
														echo "	<td class=\"textonormal\" height=\"20\">\n";
														echo "		Nenhuma Especificação Cadastrada!\n";
														echo "	</td>\n";
														echo "</tr>\n";
													}
												}

												$db->disconnect();
												?>
											</table>
										</td>
									</tr>
								</table>
								<input type="hidden" name="QuantArquivos" value="<?php echo $Rows?>">
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right">
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