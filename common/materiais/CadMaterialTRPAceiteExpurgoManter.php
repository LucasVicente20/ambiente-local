<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadMaterialTRPAceiteExpurgoManter.php
// Objetivo: Programa de Gerenciamento de preços de licitação da TRP
// possibilitando alterar os status de validação dos preços realizados na licitação
// Autor: Igor Duarte
// Data: 06/09/2012
// -------------------------------------------------------------------------
// Atualizado: Pitang Agile TI
// Data: 26/06/2015
// Objetivo: CR73652
// Versão: v1.21.0-12-gaa2f15b
// -------------------------------------------------------------------------
// Atualizado: Pitang Agile TI
// Data: 20/07/2015
// Objetivo: CR73664
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 14/03/2016
// Objetivo: Bug 126514 - Aceite / Expurgo Manter
// Versão: v1.36.3
// -------------------------------------------------------------------------

require_once ("../licitacoes/funcoesComplementaresLicitacao.php");

// Acesso ao arquivo de funções
require_once "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/materiais/CadMaterialTRPAceiteExpurgoManterDetalhe.php');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $Mensagem = urldecode($_GET['Mensagem']);

    $Mens    = $_GET['Mens'];
    $Tipo    = $_GET['Tipo'];
    $Critica = $_GET['Critica'];
    
    unset($_SESSION['DataHomolIni'], $_SESSION['DataHomolFim'], $_SESSION['tipovalidacao']);
}

if (empty($_REQUEST['DataHomolIni'])) {
    $dataHomolIni = $_SESSION['DataHomolIni'];
    $dataHomolFim = $_SESSION['DataHomolFim'];
    $tipo         = $_SESSION['tipovalidacao'];
} else {
    $dataHomolIni = $_REQUEST['DataHomolIni'];
    $dataHomolFim = $_REQUEST['DataHomolFim'];
    $tipo         = $_REQUEST['tipovalidacao'];

    $_SESSION['DataHomolIni']  = $dataHomolIni;
    $_SESSION['DataHomolFim']  = $dataHomolFim;
    $_SESSION['tipovalidacao'] = $tipo;
}

if (empty($dataHomolIni) && empty($dataHomolFim)) {
    header("Location: CadMaterialTRPAceiteExpurgoManterSelecionar.php");
    exit();
}

$partes_da_data = explode('/', $dataHomolIni);
$dataHomolIni = $partes_da_data[2] . '-' . $partes_da_data[1] . '-' . $partes_da_data[0];

$partes_da_data = explode('/', $dataHomolFim);
$dataHomolFim = $partes_da_data[2] . '-' . $partes_da_data[1] . '-' . $partes_da_data[0];

$tipoValidacao = " IS NOT NULL";

if ($tipo == "A") {
    $tipoValidacao = " = 'A'";
}

if ($tipo == "E") {
    $tipoValidacao = " = 'E'";
}

if ($_REQUEST['Botao'] == 'Voltar') {
    header("location: ");
    exit();
}

// Identifica o Programa para Erro de Banco de Dados
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
	<script type="text/javascript">
        function enviar () {
	        window.location = "CadMaterialTRPAceiteExpurgoManterSelecionar.php";
	    }
    </script>
	<form action="CadMaterialTRPAceiteExpurgoManter.php" method="get" name="CadMaterialTRPAceiteExpurgo">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="" width="100%">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a>
                    > Materiais/Serviços > TRP > Aceite/Expurgo > Manter
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
			            <?php
                        if ($Mens == 1) {
                            ExibeMens($Mensagem,$Tipo,1);
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
					<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="" width="100%">
						<tr>
							<td class="textonormal">
								<table width="100%" border="0" cellspacing="0" cellpadding="0" summary="">
									<tr>
										<td class="textonormal">
											<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#bfdaf2">
												<tr>
													<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">MANTER ACEITE/EXPURGO PREÇOS TRP</td>
												</tr>
												<tr>
											        <?php
                                                    $db = Conexao();

                                                    $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
                                                    $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

                                                    $flagbreak = false;

                                                    $sql1  = "SELECT DISTINCT";
                                                    $sql1 .= " TRP.CLICPOPROC ,TRP.ALICPOANOP ,TRP.CGREMPCODI ,TRP.CCOMLICODI ,TRP.CORGLICODI ";
                                                    $sql1 .= ",ORG.EORGLIDESC ,LIP.VLICPOVALH ,FASE.TFASELDATA, CL.ecomlidesc ";
                                                    $sql1 .= " FROM";
                                                    $sql1 .= " SFPC.TBTABELAREFERENCIALPRECOS TRP";
                                                    $sql1 .= " JOIN SFPC.TBORGAOLICITANTE ORG ON ORG.CORGLICODI = TRP.CORGLICODI";
                                                    $sql1 .= " JOIN SFPC.TBLICITACAOPORTAL LIP ON (LIP.CLICPOPROC = TRP.CLICPOPROC";
                                                    $sql1 .= " AND LIP.ALICPOANOP = TRP.ALICPOANOP AND LIP.CGREMPCODI = TRP.CGREMPCODI";
                                                    $sql1 .= " AND LIP.CCOMLICODI = TRP.CCOMLICODI AND LIP.CORGLICODI = TRP.CORGLICODI)";
                                                    $sql1 .= " JOIN SFPC.TBFASELICITACAO FASE ON (FASE.CLICPOPROC = TRP.CLICPOPROC";
                                                    $sql1 .= " AND FASE.ALICPOANOP = TRP.ALICPOANOP AND FASE.CGREMPCODI = TRP.CGREMPCODI";
                                                    $sql1 .= " AND FASE.CCOMLICODI = TRP.CCOMLICODI AND FASE.CORGLICODI = TRP.CORGLICODI)";
                                                    $sql1 .= " JOIN SFPC.tbcomissaolicitacao CL ON(TRP.CCOMLICODI = CL.CCOMLICODI) ";
                                                    $sql1 .= " WHERE";
                                                    $sql1 .= " FASE.CFASESCODI = 13";
                                                    $sql1 .= " AND TRP.CLICPOPROC IS NOT NULL";
                                                    $sql1 .= " AND TRP.FTRPREVALI $tipoValidacao";
                                                    $sql1 .= " AND FASE.TFASELDATA BETWEEN '$dataHomolIni' AND '$dataHomolFim'";
                                                    $sql1 .= " ORDER BY";
                                                    $sql1 .= " CL.ecomlidesc, TRP.CLICPOPROC, TRP.ALICPOANOP, ORG.EORGLIDESC ASC";
        
                                                    $result1 = $db->query($sql1);
        
        if (PEAR::isError($result1)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql1");
        } else { // ELSE 1
            
            if ($result1->numRows() <= 0) {
                echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" >NENHUM PROCESSO LICITATÓRIO ENCONTRADO</td></tr>\n";
            } else {
                echo "<tr><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >COMISSÃO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >PROCESSO LICITATÓRIO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >ÓRGÃO LICITANTE</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >VALOR HOMOLOGADO</td><td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"left\" >DATA DE HOMOLOGAÇÃO</td></tr>\n";
                
                while ($Linha = $result1->fetchRow()) { // WHILE 1
                    
                    /*
                     * DADOS QUE SERÃO IMPRESSOS CASO O PROCESSO
                     * TENHA UM OU MAIS ITENS QUE PRECISEM SER VALIDADOS
                     */
                    if ($Linha[0] < 10) {
                        $pl = "000";
                    } elseif (($Linha[0] < 100) && ($Linha[0] >= 10)) {
                        $pl = "00";
                    } elseif (($Linha[0] < 1000) && ($Linha[0] >= 100)) {
                        $pl = "0";
                    } else {
                        $pl = "";
                    }
                    
                    $pl .= $Linha[0] . "/" . $Linha[1]; // Nº DO PROCESSO/ANO DO PROCESSO
                    $orgao = $Linha[5]; // ORGAO SOLICITANTE
                    $valH = converte_valor_estoques($Linha[6]); // VALOR HOMOLOGADO
                    
                    $dataH = explode("-", $Linha[7]);
                    $dataH = $dataH[2] . "/" . $dataH[1] . "/" . $dataH[0]; // DATA DA HOMOLOGAÇÃO
                    
                    $Url = "CadMaterialTRPAceiteExpurgoManterDetalhe.php?Processo=$Linha[0]&ProcessoAno=$Linha[1]&Grupo=$Linha[2]&Comissao=$Linha[3]&Orgao=$Linha[4]";
                    echo "<tr><td align=\"left\">" . $Linha[8] . "</td><td><a href=\"$Url\" class=\"textonormal\"><u>" . $pl . "</u></a></td><td align=\"left\">" . $orgao . "</td><td align=\"right\">" . $valH . "</td><td align=\"right\">" . $dataH . "</td></tr>\n";
                } // FINAL DO WHILE 1
            }
        } // FINAL ELSE 1
        $db->disconnect();
        ?>
										</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td colspan="11" align="right"><br> <input type="button"
											name="Voltar" value="Voltar" class="botao"
											onClick="javascript:enviar('Voltar')"></td>
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