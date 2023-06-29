<?php
/**
 * Portal de Compras
 * 
 * Programa: TabSituacaoDFDIncluir.php
 * Autor: Diógenes Dantas
 * Data: 16/11/2022
 * Objetivo: Programa de inclusão de situação do DFD
 * Tarefa Redmine: 275120
 * -------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/11/2022
 * Tarefa:   CR 275683
 * -------------------------------------------------------------------
 */

include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/tabelasbasicas/TabSituacaoDFDSelecionar.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $critica = $_POST['Critica'];
    $situacaoDFD = strtoupper2(trim($_POST['SituacaoDFD']));
}

if ($critica == 1) {
    // Critica dos Campos #
    $mens = 0;
    $mensagem = "Atenção: ";

    if ($situacaoDFD == "") {
        $mens = 1;
        $tipo = 2;
        $mensagem .= "<a href=\"javascript:document.Lote.SituacaoDFD.focus();\" class=\"titulo2\">Informe um nome para incluir</a>";
    } if ($mens == 0) {
		// Verifica a Duplicidade de situação #
        $db = Conexao();
		
		$codigoUsuario = $_SESSION['_cusupocodi_'];
		
        $sql = "SELECT COUNT(cplsitcodi) 
                FROM SFPC.tbplanejamentosituacaodfd 
                WHERE RTRIM(LTRIM(eplsitnome)) = '$situacaoDFD' ";
        
        $result = $db->query($sql);
        
        if (db::isError($result)) {
            ExibeErroBD("$erroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $linha = $result->fetchRow();
            $qtd = $linha[0];
            if ($qtd > 0) {
                $mens = 1;
                $tipo = 2;
                $mensagem = "<a href=\"javascript:document.Lote.SituacaoDFD.focus();\" class=\"titulo2\">Situação do DFD já cadastrada</a>";
            } else {
                // Recupera a última situação e incrementa mais um #
                $sql = "SELECT MAX(cplsitcodi) 
                        FROM SFPC.tbplanejamentosituacaodfd";

                $result = $db->query($sql);

                if (db::isError($result)) {
                    ExibeErroBD("$erroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $linha = $result->fetchRow();
                    $codigo = $linha[0] + 1;

                    // Insere situação #
                    $db->query("BEGIN TRANSACTION");
                    $sql  = "INSERT INTO SFPC.tbplanejamentosituacaodfd ( ";
                    $sql .= "cplsitcodi, eplsitnome, cusupocodi, tplsitulat";
                    $sql .= ") VALUES ( ";
                    $sql .= "$codigo, '$situacaoDFD', $codigoUsuario, now())";

                    $result = $db->query($sql);

                    if (db::isError($result)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$erroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");

                        $mens = 1;
                        $tipo = 1;
                        $mensagem = "Situação do DFD incluída com sucesso";

                        // Limpando Variáveis #
                        $situacaoDFD = "";
                    }
                }
            }
        }
    }
}
?>

<html>
<?php
// Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabSituacaoDFDIncluir.php" method="post" name="SituacaoDFD">
        <br> <br> <br> <br> <br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal"><font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font>
                    </a> > Tabelas > Planejamento > Situação DFD > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
            <?php
            if ($mens == 1) {
                ?>
                <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2"><?php ExibeMens($mensagem,$tipo,1); ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3">
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                            INCLUIR - SITUAÇÃO DO DFD
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para cadastrar a situação de uma DFD, insira no campo abaixo a descrição da mesma e clique em 'Incluir'.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Situação:</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="SituacaoDFD"value="<?php echo $situacaoDFD; ?>" size="45" maxlength="60" class="textonormal">
                                                        <input type="hidden" name="Critica" value="1">
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" align="right">
                                            <input type="submit" name="Incluir" value="Incluir" class="botao">
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
<script language="javascript" type="">
<!--
document.Planejamento.SituacaoDFD.focus();
//-->
</script>