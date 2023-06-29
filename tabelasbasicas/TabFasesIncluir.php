<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: TabFasesIncluir.php
// Autor: Rossana Lira
// Data: 23/04/03
// Objetivo: Programa de Inclusão de Fases
// OBS.: Tabulação 2 espaços
// -------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica = $_POST['Critica'];
    $FasesDescricao = strtoupper2(trim($_POST['FasesDescricao']));
    $Ordem = strtoupper2(trim($_POST['Ordem']));
}

if ($Critica == 1) {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($FasesDescricao == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Fases.FasesDescricao.focus();\" class=\"titulo2\">Fase</a>";
    }
    if ($Ordem == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Fases.Ordem.focus();\" class=\"titulo2\">Ordem de Exibição</a>";
    } else {
        if (! SoNumeros($Ordem)) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.Fases.Ordem.focus();\" class=\"titulo2\">Ordem de Exibição Inválida</a>";
        }
    }
    if ($Mens == 0) {
        // Verifica a Duplicidade de Fases #
        $db = Conexao();
        $sql = "SELECT COUNT(CFASESCODI) FROM SFPC.TBFASES WHERE RTRIM(LTRIM(EFASESDESC)) = '$FasesDescricao' ";
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Fases.FasesDescricao.focus();\" class=\"titulo2\"> Fase Já Cadastrada</a>";
            } else {
                // Verifica a Duplicidade da Ordem #
                $sql = "SELECT COUNT(CFASESCODI) FROM SFPC.TBFASES WHERE AFASESORDE = $Ordem";
                $result = $db->query($sql);
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $Qtd = $Linha[0];
                    if ($Qtd > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.Fases.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Já Cadastrada</a>";
                    } else {
                        // Recupera a última Fase e incrementa mais um #
                        $sql = "SELECT MAX(CFASESCODI) FROM SFPC.TBFASES";
                        $result = $db->query($sql);
                        if (PEAR::isError($result)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $Linha = $result->fetchRow();
                            $Codigo = $Linha[0] + 1;
                            
                            // Insere Fases #
                            $Data = date("Y-m-d H:i:s");
                            $db->query("BEGIN TRANSACTION");
                            $sql = "INSERT INTO SFPC.TBFASES ( ";
                            $sql .= "CFASESCODI, EFASESDESC, AFASESORDE, TFASESULAT ";
                            $sql .= ") VALUES ( ";
                            $sql .= "$Codigo, '$FasesDescricao', $Ordem, '$Data')";
                            $result = $db->query($sql);
                            if (PEAR::isError($result)) {
                                $db->query("ROLLBACK");
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $db->query("COMMIT");
                                $db->query("END TRANSACTION");
                                
                                $Mens = 1;
                                $Tipo = 1;
                                $Mensagem = "Fase Incluída com Sucesso";
                                
                                // Limpando Variáveis #
                                $FasesDescricao = "";
                                $Ordem = "";
                            }
                        }
                    }
                }
            }
        }
        $db->disconnect();
    }
}
?>
<html>
<?php
// Carrega o layout padrão
layout();
?>
<script
    language="javascript"
    type=""
>
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link
    rel="stylesheet"
    type="text/css"
    href="../estilo.css"
>
<body
    background="../midia/bg.gif"
    marginwidth="0"
    marginheight="0"
>
    <script
        language="JavaScript"
        src="../menu.js"
    ></script>
    <script language="JavaScript">Init();</script>
    <form
        action="TabFasesIncluir.php"
        method="post"
        name="Fases"
    >
        <br> <br> <br> <br> <br>
        <table
            cellpadding="3"
            border="0"
        >
            <!-- Caminho -->
            <tr>
                <td width="100"><img
                    border="0"
                    src="../midia/linha.gif"
                    alt=""
                ></td>
                <td
                    align="left"
                    class="textonormal"
                ><font class="titulo2">|</font> <a href="../index.php"><font
                        color="#000000"
                    >Página Principal</font></a> > Tabelas > Fases > Incluir</td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
                <td width="100"></td>
                <td
                    align="left"
                    colspan="2"
                ><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
	<?php } ?>
	<!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table
                        border="0"
                        cellspacing="0"
                        cellpadding="3"
                    >
                        <tr>
                            <td class="textonormal">
                                <table
                                    border="1"
                                    cellpadding="3"
                                    cellspacing="0"
                                    bordercolor="#75ADE6"
                                    summary=""
                                    class="textonormal"
                                    bgcolor="#FFFFFF"
                                >
                                    <tr>
                                        <td
                                            align="center"
                                            bgcolor="#75ADE6"
                                            valign="middle"
                                            class="titulo3"
                                        >INCLUIR - FASES</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir uma
                                                nova fase, informe os dados
                                                abaixo e clique no botão
                                                "Incluir". Os itens obrigatórios
                                                estão com *.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table
                                                class="textonormal"
                                                border="0"
                                                align="left"
                                                class="caixa"
                                            >
                                                <tr>
                                                    <td
                                                        class="textonormal"
                                                        bgcolor="#DCEDF7"
                                                    >Fase*</td>
                                                    <td class="textonormal"><input
                                                            type="text"
                                                            name="FasesDescricao"
                                                            value="<?php echo $FasesDescricao; ?>"
                                                            size="45"
                                                            maxlength="60"
                                                            class="textonormal"
                                                        > <input
                                                            type="hidden"
                                                            name="Critica"
                                                            value="1"
                                                        ></td>
                                                </tr>
                                                <tr>
                                                    <td
                                                        class="textonormal"
                                                        bgcolor="#DCEDF7"
                                                    >Ordem de Exibição*</td>
                                                    <td class="textonormal"><input
                                                            type="text"
                                                            name="Ordem"
                                                            size="3"
                                                            value="<?php echo $Ordem; ?>"
                                                            maxlength="3"
                                                            class="textonormal"
                                                        ></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            class="textonormal"
                                            align="right"
                                        ><input
                                                type="submit"
                                                name="Incluir"
                                                value="Incluir"
                                                class="botao"
                                            ></td>
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
<script
    language="javascript"
    type=""
>
<!--
document.Fases.FasesDescricao.focus();
//-->
</script>
