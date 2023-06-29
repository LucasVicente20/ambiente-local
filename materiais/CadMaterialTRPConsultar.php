<?php
/**
 * Portal de Compras
 * 
 * Programa: CadMaterialTRPConsultar.php
 * Autor:    Igor Duarte
 * Data:     03/08/2012
 * Objetivo: Programa de consulta de preços TRP
 * -------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     10/04/2015
 * Objetivo: CR 302
 * -------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     10/06/2015
 * Objetivo: Requisito 73653 - Materiais > TRP - Diversas funcionalidades
 * -------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     14/07/2015
 * Objetivo: Requisito 73664 - Materiais > TRP > Consultar
 * -------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     02/02/2018
 * Objetivo: Tarefa Redmine 186455
 * -------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     04/04/2019
 * Objetivo: Tarefa Redmine 218140
 * -------------------------------------------------------------------------------
 * 
 * 
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 *                             ############################      PROGRAMA DESCONTINUADO      ############################
 * 
 * 
 */

header("location: ../index.php");
exit;







































































































require_once "../licitacoes/funcoesComplementaresLicitacao.php";

// Acesso ao arquivo de funções #
require_once "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
// Criar o submenu TRP no menu Material#
AddMenuAcesso('/materiais/CadMaterialTRPConsultarDetalhe.php');

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
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
	<form action="CadMaterialTRPConsultar.php" method="post"
		name="CadMaterialTRPConsultar">
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
	<?php

if ($Mens == 1) {
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

}
?>
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
												bgcolor="#bfdaf2">
												<tr>
													<td align="center" bgcolor="#75ADE6" valign="middle"
														class="titulo3" colspan="6">TABELA REFERENCIAL DE PREÇOS
														DE MATERIAIS - TRP-REC</td>
												</tr>
												<tr>
													<td class="textonormal" colspan="6">
														<p align="justify">Os Preços Estimados acima referem-se a
															coleta de preços realizada em Processos Licitatórios
															promovidos pela Administração Municipal e em Atas de
															Registros de Preços de outros Órgãos Públicos. Quaisquer
															dúvidas sobre os preços estimados, deverá ser consultada
															a Gerência de Relações Comerciais, através do telefone
															3355-8229.</p>
													</td>
												</tr>
												<tr>
										<?php
        $db = Conexao();

        $range = resultValorUnico(executarSQL($db, "SELECT VPARGEPERL FROM SFPC.TBPARAMETROSGERAIS"));

//         $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
//         $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

        $sql = "
SELECT DISTINCT ON (A.VTRPREVALO)
                GRUM.FGRUMSTIPM,
                MAT.EMATEPDESC ,
                REFP.CMATEPSEQU, 
                UNID.EUNIDMSIGL ,
                A.VTRPREVALO AS media_VTRPREVALO,
                REFP.CTRPREULAT ,
                REFP.CLICPOPROC ,
                REFP.CPESQMSEQU ,
                B.VITELPUNIT ,
                MAX(CTRPREULAT)

FROM SFPC.TBTABELAREFERENCIALPRECOS REFP JOIN SFPC.TBMATERIALPORTAL MAT
          ON REFP.CMATEPSEQU = MAT.CMATEPSEQU JOIN SFPC.TBSUBCLASSEMATERIAL SUBM
          ON MAT.CSUBCLSEQU = SUBM.CSUBCLSEQU JOIN SFPC.TBUNIDADEDEMEDIDA UNID
          ON MAT.CUNIDMCODI = UNID.CUNIDMCODI JOIN SFPC.TBGRUPOMATERIALSERVICO GRUM
          ON SUBM.CGRUMSCODI = GRUM.CGRUMSCODI LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP
          ON(
                ILP.CLICPOPROC = REFP.CLICPOPROC
            AND ILP.ALICPOANOP = REFP.ALICPOANOP
            AND ILP.CGREMPCODI = REFP.CGREMPCODI
            AND ILP.CCOMLICODI = REFP.CCOMLICODI
            AND ILP.CORGLICODI = REFP.CORGLICODI
            AND ILP.CITELPSEQU = REFP.CITELPSEQU
            AND ILP.CMATEPSEQU = REFP.CMATEPSEQU)

    INNER JOIN (SELECT DISTINCT cmatepsequ, AVG(VTRPREVALO) AS VTRPREVALO 
                    FROM sfpc.tbtabelareferencialprecos GROUP BY cmatepsequ) A 
            ON A.cmatepsequ = REFP.CMATEPSEQU AND A.VTRPREVALO <> 0

    INNER JOIN (SELECT DISTINCT cmatepsequ, AVG(VITELPUNIT) AS VITELPUNIT 
                    FROM SFPC.TBITEMLICITACAOPORTAL ILP GROUP BY cmatepsequ ) B 
            ON B.cmatepsequ = REFP.CMATEPSEQU

    WHERE 1 = 1

        AND (ILP.CLICPOPROC IS NOT NULL
                  OR REFP.CPESQMSEQU IS NOT NULL)
        AND( REFP.FTRPREVALI <> 'E'
                  OR REFP.FTRPREVALI IS NULL)

GROUP BY 
GRUM.FGRUMSTIPM , 
MAT.EMATEPDESC ,
REFP.CMATEPSEQU ,
UNID.EUNIDMSIGL ,
A.VTRPREVALO ,
REFP.CTRPREULAT ,
REFP.CLICPOPROC ,
REFP.CPESQMSEQU ,
B.VITELPUNIT";


        
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            if ($result->numRows() > 0) {
                $TotalItens = 0;
                $TotalGeral = 0;
                $TipoMaterial = '';
                while ($Linha = $result->fetchRow()) {
                	
                    if ($TipoMaterial != $Linha[0]) {
                        $TipoMaterial = $Linha[0];
                        if ($TotalItens > 0) {
                            echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL DE ITENS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">$TotalItens</td></tr>";
                            $TotalItens = 0;
                        }
                        switch ($TipoMaterial) {
                            case 'C':
                                echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">MATERIAL DE CONSUMO</td></tr>";
                                echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DESCRIÇÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >CÓDIGO CADUM</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >UNID.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >MÉDIA TRP</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >ÚLTIMO PREÇO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DATA DE REFERÊNCIA</td></tr>\n";
                                break;
                            case 'P':
                                echo "<tr><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">MATERIAL DE PERMANENTE</td></tr>";
                                echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DESCRIÇÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >CÓDIGO CADUM</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >UNID.</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >MÉDIA TRP</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >ÚLTIMO PREÇO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >DATA DE REFERÊNCIA</td></tr>\n";
                                break;
                        }
                    }

                    $Valor = converte_valor_estoques($Linha[4]);

                    if ($Linha[6] != null) {
                        $Data = explode("-", $Linha[5]);
                        $Data1 = explode(" ", $Data[2]);
                        $Data[2] = $Data1[0];
                    } else {
                        $sqlconsulta = "SELECT T.dpesqmrefe, T.CPESQMVALO FROM sfpc.tbpesquisaprecomercado T WHERE T.CMATEPSEQU  =  $Linha[2] ORDER BY T.dpesqmrefe DESC LIMIT 1";
                        $testePM = $db->query($sqlconsulta);
                        $LinhaPM = $testePM->fetchRow();
                        $dataPM = $LinhaPM[0];
                        $Data = explode("-", $dataPM);
                        $Valor = converte_valor_estoques($LinhaPM[1]);
                    }

                    $Data = $Data[2]."/".$Data[1]."/".$Data[0];

                    $Url = "CadMaterialTRPConsultarDetalhe.php?Material=".$Linha[2];

                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                        $_SESSION['GetUrl'][] = $Url;
                    }

                    /*
                     * função para retirar acentos, presente no arquivo funcoes.php
                     * RetiraAcentos($str);
                     * upper_acento($str);
                     */
                    $descricao = strtoupper($Linha[1]);
                    // CR 73653 - Materiais > TRP - Diversas funcionalidades
                    $media = calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $Linha[2]);
                    //die(var_dump($media));
                     
                    $mediaTRP = converte_valor_estoques($media);
                    if (($Linha[6] != null) || ($Linha[6] != "")) {         
                    	
                    	$valor1 = $media - (($media * $range) / 100);
                    	$valor2 = $media + (($media * $range) / 100);
                    	
                    	if (((($Linha [9] <= $valor2) && ($Linha [9] >= $valor1))) || ((($Linha [9] < $valor1) || ($Linha [9] > $valor2)) && ($Linha [8] == 'A'))) {
                    		echo "<tr><td><a href=\"$Url\" class=\"textonormal\"><u>" . $descricao . "</u></a></td><td align=\"right\">" . $Linha [2] . "</td><td align=\"right\">" . $Linha [3] . "</td><td align=\"right\">" . $mediaTRP . "</td><td align=\"right\">" . $Valor . "</td><td align=\"right\">" . $Data . "</td></tr>\n";
                    		$TotalItens ++;
                    		$TotalGeral ++;
                    	} else {
                    		echo "<tr><td><a href=\"$Url\" class=\"textonormal\"><u>" . $descricao . "</u></a></td><td align=\"right\">" . $Linha [2] . "</td><td align=\"right\">" . $Linha [3] . "</td><td align=\"right\">" . " 0,0000 " . "</td><td align=\"right\">" . $Valor . "</td><td align=\"right\">" . $Data . "</td></tr>\n";
                    		$TotalItens ++;
                    		$TotalGeral ++;
                    	}
                    } else {
                        echo "<tr><td><a href=\"$Url\" class=\"textonormal\"><u>".$descricao."</u></a></td><td align=\"right\">".$Linha[2]."</td><td align=\"right\">".$Linha[3]."</td><td align=\"right\">".$mediaTRP."</td><td align=\"right\">".$Valor."</td><td align=\"right\">".$Data."</td></tr>\n";
                        $TotalItens++;
                        $TotalGeral++;
                    }
                    
                }
                if ($TotalItens > 0) {
                    echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL DE ITENS</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">$TotalItens</td></tr>";
                    echo "<tr><td align=\"right\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\">TOTAL GERAL</td><td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">$TotalGeral</td></tr>";
                    $TotalItens = 0;
                }
            } else {
                echo "<tr><td colspan=\"6\">Nenhum Preço Armazenado</td></tr>";
            }
        }
        $db->disconnect();
        ?>
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
