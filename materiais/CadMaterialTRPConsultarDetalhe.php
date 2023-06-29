<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadMaterialTRPConsultarDetalhe.php
// Objetivo: Programa de Detalhamento da Consulta de TRP
// Autor: Igor Duarte
// Data: 09/08/2012
// ----------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 10/06/2015
// Objetivo: Requisito 73653 - Materiais > TRP - Diversas funcionalidades
// Versão: v1.19.0-12-g1c65a68
// ----------------------------------------------------------------------------
// Alterado: Lucas Baracho
// Data:	 09/07/2018
// Objetivo: Tarefa Redmine 165579
// ----------------------------------------------------------------------------
require_once "../licitacoes/funcoesComplementaresLicitacao.php";

// Acesso ao arquivo de funções #
require_once "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialTRPConsultar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao            	= $_POST['Botao'];
		//$Grupo            	= $_POST['Grupo'];
		//$Classe           	= $_POST['Classe'];
		//$Subclasse        	= $_POST['Subclasse'];
		$Material         	= $_POST['Material'];
		$CodCADUM			= $_POST['CodCADUM'];
		$DescSimples	  	= $_POST['DescSimples'];
		$UndMedida     		= $_POST['UndMedida'];
		$DescCompleta    	= $_POST['DescCompleta'];
		$MediaTRP			= $_POST['MediaTRP'];

}else{
		$Material         = $_GET['Material'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
    $Url = "CadMaterialTRPConsultar.php";
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit();
}
if ($Botao == "") {
    // Pega os dados do Material de acordo com o código #
    $db = Conexao();

    $meses = resultValorUnico(executarSQL($db, "select QPARGEVLI from SFPC.TBPARAMETROSGERAIS"));

    $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
    $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

    $sql = "SELECT	DISTINCT
							MAT.EMATEPDESC, MAT.EMATEPCOMP, UND.EUNIDMDESC, REFP.CMATEPSEQU, MAT.FMATEPSUST
				FROM
							SFPC.TBUNIDADEDEMEDIDA UND, SFPC.TBMATERIALPORTAL MAT, SFPC.TBTABELAREFERENCIALPRECOS REFP
				WHERE
							REFP.CMATEPSEQU = MAT.CMATEPSEQU
							AND MAT.CUNIDMCODI = UND.CUNIDMCODI
							AND REFP.CMATEPSEQU = $Material";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha = $res->fetchRow();

        $DescSimples		= $Linha[0];
        $DescCompleta		= $Linha[1];
        $UndMedida			= $Linha[2];
		$CodCADUM			= $Linha[3];
		$itemsustentavel	= $Linha[4];
        $media				= calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $CodCADUM);
        $MediaTRP			= converte_valor_estoques($media);
    }

    $db->disconnect();
}
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadMaterialTRPConsultarDetalhe.php" method="post"
		name="CadMaterialTRPConsultarDetalhe">
		<br> <br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Materiais > TRP >
					Consultar</td>
			</tr>
			<!-- Fim do Caminho-->

			<!-- Erro -->
	<?php if ($Mens == 1) {
    ?>
	<tr>
				<td width="100"></td>
				<td align="left" colspan="2">
			<?php if ($Mens == 1) {
    ExibeMens($Mensagem, $Tipo, 1);
}
    ?>
		</td>
			</tr>
	<?php
} ?>
	<!-- Fim do Erro -->

			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff"
						summary="">
						<tr>
							<td class="textonormal">
								<table border="0" cellspacing="0" cellpadding="0" summary="">
									<tr>
										<td class="textonormal">
											<table border="1" cellpadding="3" cellspacing="0"
												bordercolor="#75ADE6" summary="" class="textonormal"
												bgcolor="#FFFFFF">
												<tr>
													<td align="center" bgcolor="#75ADE6" valign="middle"
														class="titulo3" colspan="9">TABELA REFERENCIAL DE PREÇOS
														DE MATERIAIS - TRP-REC</td>
												</tr>
												<tr>
													<td colspan="9">
														<table border="0" cellpadding="0" cellspacing="0"
															bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td colspan="2">
																	<table class="textonormal" border="0" width="100%"
																		summary="">
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Código CADUM</td>
																			<td class="textonormal"><?php echo $CodCADUM; ?></td>
																		</tr>
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Descrição do Material</td>
																			<td class="textonormal"><?php echo $DescSimples;?></td>
																		</tr>
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Unidade de Medida</td>
																			<td class="textonormal"><?php echo $UndMedida;?></td>
																		</tr>
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Descrição Completa</td>
																			<td class="textonormal"><?php echo $DescCompleta;?></td>
																		</tr>
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Item Sustentável</td>
																			<td class="textonormal"><?php if ($itemsustentavel == 'S'){echo "SIM";} else {echo "NÃO";}?></td>
																		</tr>
																		<tr>
																			<td class="textonormal" bgcolor="#DCEDF7" height="20"
																				align="left">Média do Preço TRP</td>
																			<td class="textonormal"><?php echo $MediaTRP." - Para esta média foram utilizados preços válidos existentes dos últimos ".$meses." dias";?></td>
																		</tr>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#75ADE6" align="center">DATA
														REFERÊNCIA</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">LICITAÇÃO/PESQUISA
														DE MERCADO</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">ÓRGÃO</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">LOTE</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">ORDEM</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">FORNECEDOR</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">MARCA</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">MODELO</td>
													<td class="textonormal" bgcolor="#75ADE6" align="center">PREÇO
														UNITÁRIO</td>
												</tr>
												<tr>
										<?php
        $db = Conexao();

        $sql = "SELECT
														REFP.VTRPREVALO, REFP.CPESQMSEQU, REFP.CLICPOPROC, REFP.ALICPOANOP,
														REFP.CGREMPCODI, REFP.CCOMLICODI, REFP.CORGLICODI, REFP.CITELPSEQU, REFP.CTRPREULAT
												 FROM
												 		SFPC.TBTABELAREFERENCIALPRECOS REFP
														JOIN SFPC.TBMATERIALPORTAL MAT ON REFP.CMATEPSEQU = MAT.CMATEPSEQU
														JOIN SFPC.TBSUBCLASSEMATERIAL  SUBM ON MAT.CSUBCLSEQU = SUBM.CSUBCLSEQU
														JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
														JOIN SFPC.TBGRUPOMATERIALSERVICO GRUM ON SUBM.CGRUMSCODI = GRUM.CGRUMSCODI
												 WHERE
												 		REFP.CMATEPSEQU = $Material
														AND(REFP.CLICPOPROC IS NOT NULL
															OR REFP.CPESQMSEQU IS NOT NULL)
														--AND(REFP.FTRPREVALI = 'A' OR REFP.FTRPREVALI IS NULL)";

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            $i = 0;
            $itens;

            while ($Linha = $result->fetchRow()) {
                if ($Linha[2] != null) { // verificar se é uma licitação (CLICPOPROC <> NULL)
                    $sql = "SELECT 	ILP.EITELPMARC, ILP.EITELPMODE, FORN.AFORCRCCGC, FORN.NFORCRRAZS,
																	ORGL.EORGLIDESC, ILP.CITELPNUML, ILP.AITELPORDE
															FROM 	SFPC.TBITEMLICITACAOPORTAL ILP, SFPC.TBFORNECEDORCREDENCIADO FORN,
																	SFPC.TBORGAOLICITANTE ORGL
															WHERE	ILP.CLICPOPROC = $Linha[2]
																	AND ILP.ALICPOANOP = $Linha[3]
																	AND ILP.CGREMPCODI = $Linha[4]
																	AND ILP.CCOMLICODI = $Linha[5]
																	AND ILP.CORGLICODI = $Linha[6]
																    AND ILP.CITELPSEQU = $Linha[7]
																	AND ILP.AFORCRSEQU  = FORN.AFORCRSEQU
																	AND ORGL.CORGLICODI =  ILP.CORGLICODI
															ORDER BY
																	ILP.CITELPNUML, ILP.AITELPORDE, FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.EITELPMARC, ILP.EITELPMODE";

                    $retorno = $db->query($sql);
                    $retorno = $retorno->fetchRow();

                    $Data = explode("-", $Linha[8]);
                    $Data1 = explode(" ", $Data[2]);
                    $Data[2] = $Data1[0];

                    $DataC = "$Data[0]"."$Data[1]"."$Data[2]";
                    $itens[$i]["DataC"] = $DataC;

                    $Data = $Data[2]."/".$Data[1]."/".$Data[0];

                    $comissao = resultValorUnico(executarSQL($db, "select ECOMLIDESC from SFPC.TBCOMISSAOLICITACAO where CCOMLICODI = $Linha[5]"));
                    $orgao = resultValorUnico(executarSQL($db, "select EORGLIDESC from SFPC.TBORGAOLICITANTE where CORGLICODI = $Linha[6]"));

                    $itens[$i]["Data"] = $Data; // data referência
                    $itens[$i]["L/P"] = "PL ".$Linha[2]."/".$Linha[3]." - ".$comissao." - ".$orgao; // licitação
                    $itens[$i]["Orgao"] = $orgao; // órgão
                    $itens[$i]["Forn"] = FormataCNPJ($retorno[2])." ".$retorno[3]; // fornecedor
                    $itens[$i]["Marca"] = $retorno[0]; // marca
                    $itens[$i]["Modelo"] = $retorno[1]; // modelo
                    $itens[$i]["PU"] = converte_valor_estoques($Linha[0]); // preço unitário
                    $itens[$i]["Lote"] = $retorno[5]; // ORDEM
                    $itens[$i]["Ordem"] = $retorno[6]; // LOTE
                } else { // verificar se é uma pesquisa de preço de mercado (CPESQMSEQU <> NULL)
                    $sql = "SELECT  PESQM.DPESQMREFE, PESQM.EPESQMOBSE, PESQM.CPESQMCNPJ,
																	PESQM.NPESQMRAZS, PESQM.CPESQMMARC, PESQM.CPESQMMODE,
																	FORN.AFORCRCCGC, FORN.NFORCRRAZS
															FROM 	SFPC.TBPESQUISAPRECOMERCADO PESQM
																	LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = PESQM.AFORCRSEQU
															WHERE 	PESQM.CPESQMSEQU = $Linha[1]
																	AND PESQM.CMATEPSEQU = $Material
															ORDER BY
																	PESQM.CPESQMCNPJ, PESQM.NPESQMRAZS, PESQM.CPESQMMARC, PESQM.CPESQMMODE";

                    $retorno1 = $db->query($sql);
                    $retorno1 = $retorno1->fetchRow();

                    $Data = explode("-", $retorno1[0]);

                    $DataC = "$Data[0]"."$Data[1]"."$Data[2]";
                    $itens[$i]["DataC"] = $DataC;

                    $Data = $Data[2]."/".$Data[1]."/".$Data[0];

                    $itens[$i]["Data"] = $Data; // data referência
                    $itens[$i]["L/P"] = $retorno1[1]; // pesquisa de preço de mercado
                    $itens[$i]["Orgao"] = "-"; // órgão

                    if ($retorno1[2] != null) {
                        $itens[$i]["Forn"] = FormataCNPJ($retorno1[2])." ".$retorno1[3]; // fornecedor
                    } else {
                        $itens[$i]["Forn"] = FormataCNPJ($retorno1[6])." ".$retorno1[7]; // fornecedor
                    }

                    $itens[$i]["Marca"] = $retorno1[4]; // marca
                    $itens[$i]["Modelo"] = $retorno1[5]; // modelo
                    $itens[$i]["PU"] = converte_valor_estoques($Linha[0]); // preço unitário
                    $itens[$i]["Lote"] = ""; // ORDEM
                    $itens[$i]["Ordem"] = ""; // LOTE
                }

                $i ++;
            }

            // Ordenação do array a ser exibido na tela
            foreach ($itens as $c => $key) {
                $sort_data[] = $key['DataC'];
            }

            array_multisort($sort_data, SORT_DESC, $itens);

            $controle = 0;
            while ($controle < $i) {
                echo "<tr><td align=\"center\">&nbsp;".$itens[$controle]["Data"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["L/P"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Orgao"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Lote"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Ordem"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Forn"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Marca"]."</td>";
                echo "<td align=\"center\">&nbsp;".$itens[$controle]["Modelo"]."</td>";
                echo "<td align=\"right\">&nbsp;".$itens[$controle]["PU"]."</td></tr>\n";

                $controle ++;
            }
        }

        $db->disconnect();
        ?>
										</tr>
												<tr>
													<td colspan="9" align="right"><input type="submit"
														value="Voltar" class="botao"> <input type="hidden"
														name="Botao" value="Voltar"></td>
												</tr>
											</table>
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
