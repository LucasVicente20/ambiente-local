<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadMaterialTRPAceiteExpurgo.php
// Objetivo: Programa de Gerenciamento de preços de licitação da TRP
// possibilitando expurgar ou aceitar preços realizados na licitação
// Autor: Igor Duarte
// Data: 28/08/2012
//
// Alterado: Pitang Agile TI
// Data: 18/05/2015
// Objetivo: CR 73626 - Materiais > TRP > Aceite/ Expurgo - Incluir
// Versão: v1.16.1-94-gdf6b430
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 29/10/2015
// Objetivo: Bug 110622 - TRP - Aceite Expurgo Incluir
// Versão: v1.30.6
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 14/03/2016
// Objetivo: Bug 126514 - Aceite / Expurgo Manter
// Versão: v1.36.3
// -------------------------------------------------------------------------


require_once "../licitacoes/funcoesComplementaresLicitacao.php";

// Acesso ao arquivo de funções #
require_once "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
// Criar o submenu TRP no menu Material#
AddMenuAcesso('/materiais/CadMaterialTRPAceiteExpurgoDetalhe.php');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens = $_GET['Mens'];
    $Tipo = $_GET['Tipo'];
    $Critica = $_GET['Critica'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script type="text/javascript">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script type="text/javascript" src="../menu.js"></script>
	<script type="text/javascript">Init();</script>
	<form action="CadMaterialTRPAceiteExpurgo.php" method="get"
		name="CadMaterialTRPAceiteExpurgo">
		<br /> <br /> <br /> <br /> <br />
		<table width="100%" cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Materiais/Serviços >
					TRP > Aceite/Expurgo > Incluir</td>
			</tr>
			<!-- Fim do Caminho-->

			<!-- Erro -->
    <?php
    
    if ($Mens == 1) {
        ?>
    <tr>
				<td width="100"></td>
				<td align="left" colspan="2">
<?php
        if ($Mens == 1) {
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
					<table width="100%" border="0" cellspacing="0" cellpadding="3"
						bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table width="100%" border="0" cellspacing="0" cellpadding="0"
									summary="">
									<tr>
										<td class="textonormal">
											<table width="100%" border="1" cellpadding="3"
												cellspacing="0" bordercolor="#75ADE6" summary=""
												class="textonormal" bgcolor="#bfdaf2">
												<tr>
													<td align="center" bgcolor="#75ADE6" valign="middle"
														class="titulo3" colspan="5">INCLUIR ACEITE/EXPURGO PREÇOS
														TRP</td>
												</tr>
												<tr>
                                            <?php
                                            // VERSÃO 2
                                            $db = Conexao();
                                            
                                            $flagbreak = 0;
                                            
                                            $sql1 = "
                                                SELECT
                                                    trp.clicpoproc,
                                                    trp.alicpoanop,
                                                    trp.cgrempcodi,
                                                    trp.ccomlicodi,
                                                    trp.corglicodi,
                                                    processo.eorglidesc,
                                                    processo.vlicpovalh,
                                                    processo.tfaseldata,
                                                    processo.ecomlidesc,
                                                    processo.fitelplogr
                                                FROM
                                                    sfpc.tbtabelareferencialprecos trp INNER JOIN(
                                                        SELECT
                                                            lic.clicpoproc,
                                                            lic.alicpoanop,
                                                            lic.cgrempcodi,
                                                            lic.ccomlicodi,
                                                            lic.corglicodi,
                                                            lic.vlicpovalh,
                                                            faselic.tfaseldata,
                                                            org.eorglidesc,
                                                            com.ecomlidesc,
                                                            ilp.citelpsequ,
                                                            ilp.fitelplogr
                                                        FROM
                                                            sfpc.tblicitacaoportal lic
                                                            INNER JOIN sfpc.tbfaselicitacao faselic
                                                                ON faselic.clicpoproc = lic.clicpoproc
                                                            AND faselic.alicpoanop = lic.alicpoanop
                                                            AND faselic.cgrempcodi = lic.cgrempcodi
                                                            AND faselic.ccomlicodi = lic.ccomlicodi
                                                            AND faselic.corglicodi = lic.corglicodi
                                                            INNER JOIN sfpc.tborgaolicitante org
                                                                ON org.corglicodi = lic.corglicodi
                                                            INNER JOIN sfpc.tbcomissaolicitacao com
                                                                ON com.ccomlicodi = lic.ccomlicodi
                                                            INNER JOIN sfpc.tbitemlicitacaoportal ilp
                                                		        ON(
                                                			        lic.clicpoproc = ilp.clicpoproc
                                                			        AND lic.alicpoanop = ilp.alicpoanop
                                                			        AND lic.cgrempcodi = ilp.cgrempcodi
                                                			        AND lic.ccomlicodi = ilp.ccomlicodi
                                                			        AND lic.corglicodi = ilp.corglicodi
                                                			    )
                                                            INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CMATEPSEQU = ilp.CMATEPSEQU    
                                                        WHERE
                                                            faselic.cfasescodi = 13
                                                            AND lic.vlicpovalh IS NOT NULL
                                                            AND MAT.FMATEPNTRP <> 'S'
                                                    ) processo
                                                        ON processo.clicpoproc = trp.clicpoproc
                                                    AND processo.alicpoanop = trp.alicpoanop
                                                    AND processo.cgrempcodi = trp.cgrempcodi
                                                    AND processo.ccomlicodi = trp.ccomlicodi
                                                    AND processo.corglicodi = trp.corglicodi
                                                WHERE
                                                    trp.ftrprevali IS NULL
                                                    AND trp.clicpoproc IS NOT NULL
                                                    AND processo.citelpsequ = trp.citelpsequ
                                                    AND processo.fitelplogr LIKE 'S'
                                                GROUP BY
                                                    trp.clicpoproc,
                                                    trp.alicpoanop,
                                                    trp.cgrempcodi,
                                                    trp.ccomlicodi,
                                                    trp.corglicodi,
                                                    processo.eorglidesc,
                                                    processo.vlicpovalh,
                                                    processo.tfaseldata,
                                                    processo.ecomlidesc,
                                                    processo.fitelplogr
                                                ORDER BY
                                                    processo.ecomlidesc,
                                                    trp.alicpoanop,
                                                    trp.clicpoproc ASC
                                            ";
                                            $result1 = $db->query($sql1);
                                            
                                            $flagbreak2 = true;
                                            
                                            if (PEAR::isError($result1)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql1");
                                            } else {
                                                if ($result1->numRows() <= 0) {
                                                    $flagbreak2 = false;
                                                    echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >NENHUM PROCESSO LICITATÓRIO ENCONTRADO</td></tr>\n";
                                                } else {
                                                    // echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >PROCESSO LICITATÓRIO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >ÓRGÃO LICITANTE</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >VALOR HOMOLOGADO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >DATA DE HOMOLOGAÇÃO</td></tr>\n";
                                                    
                                                    $range = resultValorUnico(executarSQL($db, "SELECT VPARGEPERL FROM SFPC.TBPARAMETROSGERAIS"));
                                                    
                                                    $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
                                                    $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');
                                                    
                                                    $primeiraVez = true;
                                                    while ($Linha = $result1->fetchRow()) {
                                                        
                                                        $pl = str_pad($Linha[0], 4, '0', STR_PAD_LEFT) . "/" . $Linha[1]; // Nº DO PROCESSO/ANO DO PROCESSO
                                                        $orgao = $Linha[5]; // ORGAO SOLICITANTE
                                                        $valH = converte_valor_estoques($Linha[6]); // VALOR HOMOLOGADO
                                                        
                                                        $dataH = explode("-", $Linha[7]);
                                                        $dataH = $dataH[2] . "/" . $dataH[1] . "/" . $dataH[0]; // DATA DA HOMOLOGAÇÃO
                                                        
                                                        if ($primeiraVez) {
                                                            echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >COMISSÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >PROCESSO LICITATÓRIO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >ÓRGÃO LICITANTE</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >VALOR HOMOLOGADO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >DATA DE HOMOLOGAÇÃO</td></tr>\n";
                                                        }
                                                        $primeiraVez = false;
                                                        
                                                        $Url = "CadMaterialTRPAceiteExpurgoDetalhe.php?Processo=$Linha[0]&ProcessoAno=$Linha[1]&Grupo=$Linha[2]&Comissao=$Linha[3]&Orgao=$Linha[4]";
                                                        
                                                        if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                            $_SESSION['GetUrl'][] = $Url;
                                                        }
                                                        
                                                        echo "<tr><td align=\"left\">" . $Linha[8] . "</td><td><a href=\"$Url\" class=\"textonormal\"><u>" . $pl . "</u></a></td><td align=\"left\">" . $orgao . "</td><td align=\"right\">" . $valH . "</td><td align=\"right\">" . $dataH . "</td></tr>\n";
                                                    } // FINAL DO ELSE 2
                                                } // FINAL DO WHILE 1
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
