<?php
/**
 * Portal de Compras
 * 
 * Programa: ConsDocsFornecedor.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * Data:     21/01/2019
 * Objetivo: Consulta de documentos do fornecedor
 * --------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     08/08/2019
 * Objetivo: Tarefa Redmine 222011
 * --------------------------------------------------------------------------
 * Alterado: Lucas André e Daniel Augusto
 * Data:		16/05/2023
 * Objetivo: Tarefa Redmine 282898
 * -----------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Daniel Augusto
 * Data:		16/05/2023
 * Objetivo: Tarefa Redmine 282903
 * -----------------------------------------------------------------------------------------------------------------------------------------------

 */

// Acesso ao arquivo de funções
include "../funcoes.php";
require_once( "funcoesDocumento.php");

// Executa o controle de segurança
session_start();
Seguranca();

// Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao       = $_POST['Botao'];
	$docSituacao = $_POST['docSituacao'];
	$docInicio 	 = $_POST['docInicio'];
	$docFim 	 = $_POST['docFim'];
} else {
	$Botao       = $_GET['Botao'];
	$docSituacao = $_GET['docSituacao'];
	$docInicio 	 = $_GET['docInicio'];
	$docFim 	 = $_GET['docFim'];
	$Mens      	 = $_GET['Mens'];
	$Mensagem  	 = $_GET['Mensagem'];
	$Tipo	  	 = $_GET['Tipo'];
	$Desvio    	 = $_GET['Desvio'];
}

// Atribuir $_SESSION['AcompFornecedorDesvio'] com o desvio de chamada
if ($Desvio=="CadRenovacaoCadastroIncluir") {
   $_SESSION['AcompFornecedorDesvio'] = "CadRenovacaoCadastroIncluir";
} elseif ($Desvio=="CadAnaliseCertidaoFornecedor") { 
   $_SESSION['AcompFornecedorDesvio'] = "CadAnaliseCertidaoFornecedor";
} else {
	$_SESSION['AcompFornecedorDesvio'] = "";
}

// Identifica o programa para erro de banco de dados
$ErroPrograma = __FILE__;

if ($Botao == "Limpar") {
	header("location: ConsDocsFornecedor.php");
	exit;
}

if ($Botao == "Pesquisar") {
	$validar        = true;
	$DataEntradaIni = $docInicio;
	$DataEntradaFim = $docFim;

    // verifica se a data de anexação é válida
    if ($DataEntradaIni != "" && $DataEntradaFim != "") {
        $DataEntradaIniCheck = explode("-",$DataEntradaIni);
		


        if (!checkdate($DataEntradaIniCheck[2],$DataEntradaIniCheck[1],$DataEntradaIniCheck[0])) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

			$validar = false;
			$Mens    = 1;
            $Tipo    = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Data da Anexação Inicial inválida</a>";
        }

        $DataEntradaFimCheck = explode("-",$DataEntradaFim);

        if (!checkdate($DataEntradaFimCheck[2],$DataEntradaFimCheck[1],$DataEntradaFimCheck[0])) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

			$validar = false;
			$Mens    = 1;
            $Tipo    = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaFim.focus();\" class=\"titulo2\">Data da Anexação Final inválida</a>";
        }

        $dateTimeIni = strtotime($DataEntradaIniCheck[2].'-'.$DataEntradaIniCheck[1].'-'.$DataEntradaIniCheck[0]);
        $dateTimeFim = strtotime($DataEntradaFimCheck[2].'-'.$DataEntradaFimCheck[1].'-'.$DataEntradaFimCheck[0]);

        if ($dateTimeIni > $dateTimeFim) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $validar = false;
			$Mens    = 1;
            $Tipo    = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Data da Anexação Inicial deve ser menor que a final</a>";
        }
    } else {
		if ((empty($DataEntradaIni) && $DataEntradaFim != "") || ($DataEntradaIni != "" && empty($DataEntradaFim))) {
			$validar = false;
            $Mens    = 1;
            $Tipo    = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Você deve preencher as duas datas para realizar a pesquisa</a>";
		}
	}

	$db = Conexao();

	if ($validar) {
		$sql  = "SELECT	DOC.CFDOCUSEQU, DOC.APREFOSEQU, DOC.AFORCRSEQU, DOC.AFDOCUANOA, DOC.CFDOCTCODI, DOC.EFDOCUNOME, ";
		$sql .= "		DOC.FFDOCUFORN, DOC.TFDOCUANEX, DOC.FFDOCUSITU, DOC.CUSUPOCODI, DOC.TFDOCTULAT, T.EFDOCTDESC, ";
		$sql .= "		(SELECT	H.TFDOCHULAT ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS DATAHORAULTIMAALT, ";
		$sql .= "		U.EUSUPORESP, ";
		$sql .= "		(SELECT S.EFDOCSDESC ";
		$sql .= "		 FROM	SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
		$sql .= "		 		JOIN SFPC.TBFORNECEDORDOCUMENTOSITUACAO S ON S.CFDOCSCODI = H.CFDOCSCODI ";
		$sql .= "		 WHERE	H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
		$sql .= "		 ORDER BY H.TFDOCHULAT DESC LIMIT 1) AS SITUACAO_NOME, ";
		$sql .= "		T.FFDOCTOBRI ";
		$sql .= "FROM	SFPC.TBFORNECEDORDOCUMENTO DOC ";
		$sql .= "		JOIN SFPC.TBFORNECEDORDOCUMENTOTIPO T ON T.CFDOCTCODI = DOC.CFDOCTCODI ";
		$sql .= "		JOIN SFPC.TBUSUARIOPORTAL U ON DOC.CUSUPOCODI = U.CUSUPOCODI ";
		$sql .= "WHERE	1 = 1 ";

		if ($docSituacao && $docSituacao != '-') {
			$sql .= " AND " . $docSituacao . " = (SELECT	S.CFDOCSCODI ";
			$sql .= "							  FROM		SFPC.TBFORNECEDORDOCUMENTOHISTORICO H ";
			$sql .= "										JOIN SFPC.TBFORNECEDORDOCUMENTOSITUACAO S ON S.CFDOCSCODI = H.CFDOCSCODI ";
			$sql .= "							  WHERE		H.CFDOCUSEQU = DOC.CFDOCUSEQU ";
			$sql .= "							  ORDER BY H.TFDOCHULAT DESC LIMIT 1) ";
		}

		if ($docInicio) {
			$sql .= " AND DOC.TFDOCUANEX >= '" . date_transform($docInicio) . "' ";
		}

		if ($docFim) {
			$sql .= " AND DOC.TFDOCUANEX <= '" . date_transform($docFim) . "' ";
		}

		$sql .= "	AND FFDOCUSITU = 'A' ";
		$sql .= "ORDER BY TFDOCTULAT DESC ";

		$resultDocumentos = $db->query($sql);
        $arrFornecedores    = getFornecedores();
		$arrPreFornecedores = getPreFornecedores();
	}
}

$sql  = "SELECT	CFDOCSCODI, EFDOCSDESC ";
$sql .= "FROM	SFPC.TBFORNECEDORDOCUMENTOSITUACAO ";
$sql .= "WHERE	FFDOCSSITU = 'A' ";
$sql .= "ORDER BY CFDOCSCODI ASC ";
	
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
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
		document.CadAcompFornecedor.Botao.value=valor;
		document.CadAcompFornecedor.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsDocsFornecedor.php" method="post" name="CadAcompFornecedor">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
  			<!-- Caminho -->
  			<tr>
    			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    			<td align="left" class="textonormal">
      				<font class="titulo2">|</font>
					<?php
					if ($Desvio=="CadRenovacaoCadastroIncluir") {
						?>
      					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Registro de Renovação
						<?php
					} elseif ($Desvio=="CadAnaliseCertidaoFornecedor") {
						?>
      					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Analisar Certidões
						<?php
					} else {
						?>
       					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Consulta de Documentos
						<?php
					}
					?>
    			</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
	  				<td width="100"></td>
	  				<td align="left">
						<?php
						if ($Mens == 1) {
							ExibeMens($Mensagem,$Tipo,$Virgula);
						}
						?>
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
	        					<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" colspan="5" class="titulo3">
										CONSULTA DE DOCUMENTOS
										</td>
									</tr>
									<tr>
	    	      						<td class="textonormal" colspan="5">
	      	    							<p align="justify">
						  					Selecione o item de pesquisa desejado, preencha o argumento da pesquisa e clique no botão "Pesquisar". <br>
						  					Para visualizar os documentos clique no link do Fornecedor. <br>
						  					Para limpar a pesquisa, clique no botão "Limpar".
	          	   							</p>
	          							</td>
									</tr>
									<tr>
										<td colspan="5">
											<table class="textonormal" border="0" align="left" >
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Situação dos Documentos</td>
													<td class="textonormal" >
														<select name="docSituacao" class="textonormal">
															<option <?php ($docSituacao == '-') ? 'selected' : ''; ?> value="-">TODAS</option>
															<?php
                                                			while ($linha = $result->fetchRow()) {
                                                    			?>
                                                    			<option value="<?php echo $linha[0]; ?>"><?php echo $linha[1]; ?></option>
                                                    			<?php
															}
															?>
														</select>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Período da Anexação</td>
													<td class="textonormal" >
													<?php
                                                        $DataMes = DataMes();

                                                        if ($DataIni == "" || is_null($DataIni)) {
                                                            //$DataIni = $DataMes[0];
                                                        	$DataIni = "";
                                                        }

                                                        if ($DataFim == "" || is_null($DataFim)) {
                                                            //$DataFim = $DataMes[1];
                                                            $DataFim = "";
                                                        }

                                                        $URLIni = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataIni";
                                                        $URLFim = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataFim";
                                                        ?>

                                                        <input id="docInicio" class="textonormal" type="date"
                                                        name="docInicio" size="10"
                                                        maxlength="10" value="<?php echo $docInicio; ?>">
                                                                    
                                                        &nbsp;a&nbsp;
                                                        <input id="docFim" class="textonormal" type="date"
                                                        name="docFim" size="10"
                                                        maxlength="10" value="<?php echo $docFim; ?>">
													</td>
												</tr>
											</table>   
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right" colspan="5">
											<input type="button" value="Pesquisar" onclick="javascript:enviar('Pesquisar');" class="botao">
											<input type="button" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao">
											<input type="hidden" name="Botao" value="" />
										</td>
									</tr>
	          		    			<input type="hidden" name="Desvio" value="<?php echo $Desvio  ;?>" >
						 			<?php
									if ($resultDocumentos) {

										if ($resultDocumentos->numRows() > 0) {

											$htmlOptionsAnosAnexacao = '';
											$htmlDocumentosAnexados  = '';
											$htmlDocumentosAnexados .='	<tr bgcolor="#75ADE6">
																			<td class="titulo3">RAZÃO SOCIAL/NOME</td>
																			<td class="titulo3">CPF/CNPJ</td>
																			<td class="titulo3">TIPO DO DOCUMENTO</td>
																			<td class="titulo3">DATA ANEXAÇÃO</td>
																			<td class="titulo3">SITUAÇÃO</td>
																		</tr>';

											$arr = array();
											$anos = array();
											$arrDocs = array();
									
											$resultAno = $resultDocumentos;

											while($linha = $resultAno->fetchRow()) {
												$arrDocs[] = $linha;
											}

											// formata data de validade da CHF
											$arrValidadeCHF = explode("/", $DataValidadeCHF);
											$DataValidadeCHFFormatada = $arrValidadeCHF[2]."-".$arrValidadeCHF[1]."-".$arrValidadeCHF[0];                                        
											$mostrarAviso = 0;

											foreach ($arrDocs as $linha) {
												if ($linha[2]) {
													$arrFornecedor = getDadosFornecedor($linha[2], $arrFornecedores,2);
													$arquivoRed = 'ConsAcompFornecedor';
												} else {
													$arrFornecedor = getDadosFornecedor($linha[1], $arrPreFornecedores,1);

													if ($arrFornecedor[3] == 'cred') {
														$arquivoRed = 'ConsAcompFornecedor';
													} else {
														$arquivoRed = 'ConsInscrito';
													}
												}

												$Sequencial     = $arrFornecedor[0];
												$NomeFornecedor = $arrFornecedor[1];

												$htmlDocumentosAnexados .='	<tr>
																				<td class="textonormal" hint="'.$linha[2].'-'.$linha[1].'"><a href="'.$arquivoRed.'.php?Sequencial='.$Sequencial.'&Retorno=ConsDocsFornecedor&docSituacao='.$docSituacao.'&docInicio='.$docInicio.'&docFim='.$docFim.'">'.$NomeFornecedor.'</a></td>
																				<td class="textonormal">'.$arrFornecedor[2].'</td>
																				<td class="textonormal">'.$linha[11].'</td>
																				<td class="textonormal">'.formatarDataHora($linha[7]).'</td>
																				<td class="textonormal">'.$linha[14].'</td>
																			</tr>';

												$arrDataAnexacaoSemHoras = explode(" ", $linha[8]);
												$dataAnexacaoFormatada = $arrDataAnexacaoSemHoras[0];

												if ((strtotime($dataAnexacaoFormatada) < strtotime($DataValidadeCHFFormatada)) && ($linha[20]=='S')) {
													$mostrarAviso++;
													$arrAvisoDocs[] = $linha[14];
												}
											}

											echo $htmlDocumentosAnexados;
										} else {
											$htmlDocumentosAnexados .='	<tr>
																			<td class="textonormal" align="center">A consulta não encontrou registros.</td>
																		</tr>';
											echo $htmlDocumentosAnexados;
										}
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