<?php
# ------------------------------------------------------------------
# Prefeitura do Recife
# Portal de Compras
# Programa: ConsDetalheMovNF.php
# Autor:    Lucas Baracho
# Data:     22/11/2018
# Objetivo: Tarefa Redmine 119262
# ------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/ConsEmpenho.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $botao = $_POST['Botao'];
} else {
    $ano        = $_GET['Ano'];
    $orgao      = $_GET['Orgao'];
    $unidade    = $_GET['Unidade'];
    $sequencial = $_GET['Sequencial'];
    $parcela    = $_GET['Parcela'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($botao == "Voltar") {
    $enviar = "S";
}

?>
<html>
<head>
    <title>Portal de Compras - Detalhes da Movimentação da Nota Fiscal</title>
    <script language="javascript" type="">
        function enviar(valor) {
            document.ConsDetalheMovNF.botao.value = valor;
            document.ConsDetalheMovNF.submit();
        }
        function AbreJanela(url,largura,altura) {
		    window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
	    }
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="..;/midia/bg.jpg" marginwidth="0" marginheight="0">
    <form action="ConsDetalheMovNF.php" method="post" name="ConsDetalheMovNF.php">
        <table cellpadding="0" border="0" summary="">
            <!-- Erro -->
            <tr>
                <td align="left" colspan="2">
                    <?php if ($Mens != 0) {
                        ExibeMens($Mensagem, $Tipo, 1);
                    }
                    ?>
                </td>
            </tr>
            <!-- Fim do erro -->
            <!-- Corpo -->
            <tr>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" summary="" width "100%">
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" class="textonormal" bgcolor="#FFFFFF" summary="">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="3">
                                            DETALHAMENTO DA MOVIMENTAÇÃO DA NOTA FISCAL
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" colspan="5">
                                            <p align="justify">
                                                Para fechar a janela clique no no botão "Voltar".
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							                    &nbsp;&nbsp;
                                            </p>
                                        </td>
                                    </tr>
                                    <?php
                                    # Carrega os dados #
                                    $db = Conexao();

                                    $sql = "SELECT  ENF.AENTNFNOTA, ENF.AENTNFSERI, ENF.DENTNFENTR, AP.EALMPODESC, ENF.CALMPOCODI, ENF.CENTNFCODI
                                            FROM    SFPC.TBENTRADANOTAFISCAL ENF
                                                    LEFT JOIN SFPC.TBALMOXARIFADOPORTAL AP ON ENF.CALMPOCODI = AP.CALMPOCODI
                                            WHERE   ENF.CENTNFCODI IN (SELECT CENTNFCODI FROM SFPC.TBNOTAFISCALEMPENHO WHERE ANFEMPANEM = $ano AND CNFEMPOREM = $orgao AND CNFEMPUNEM = $unidade AND CNFEMPSEEM = $sequencial)
                                                    AND ENF.AENTNFANOE = $ano
                                                    AND ENF.CALMPOCODI IN (SELECT CALMPOCODI FROM SFPC.TBNOTAFISCALEMPENHO WHERE ANFEMPANEM = $ano AND CNFEMPOREM = $orgao AND CNFEMPUNEM = $unidade AND CNFEMPSEEM = $sequencial) ";
                                                
                                            if (!empty($parcela)) {
                                                $sql .= "AND ENF.CENTNFPAEM = $parcela ";
                                            }
                                    
                                    $res = $db->query($sql);

                                    if (db::isError($res)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\Sql: $sql");
                                    }                                                                     
                                    ?>
                                    <tr>
                                        <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Número/Série da Nota Fiscal</td>
                                        <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Data de entrada</td>
                                        <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Almoxarifado</td>
                                    </tr>
                                    <?php
                                    while ($Linha = $res->fetchRow()) {
                                    ?>
                                        <tr>
                                            <td class="texto" bgcolor="#F7F7F7" align="center"><a href="javascript:AbreJanela('ConsNotaFiscalMaterial.php?NotaFiscal=<?=$Linha[5]?>&AnoNota=<?=$ano?>&Almoxarifado=<?=$Linha[4]?>&Orgao=<?=$orgao?>',700,350);"><?php echo $Linha[0]; echo "/"; echo $Linha[1]; ?></a></td>
                                            <td class="texto" bgcolor="#F7F7F7" align="center"><?=substr($Linha[2], 8, 2) . '/' . substr($Linha[2], 5, 2) . '/' . substr($Linha[2], 0, 4); ?></td>
                                            <td class="texto" bgcolor="#F7F7F7" align="center"><?=$Linha[3]; ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    <tr>
			            				<td colspan="5" align="right">
		              						<input type="hidden" name="Enviar" value="<?php echo $enviar; ?>">
						       				<input type="button" value="Voltar" class="botao" onclick="javascript:self.close();">
											<input type="hidden" name="Botao" value="">
										</td>
		            				</tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Fim do corpo -->
        </table>
    </form>
</body>
</html>