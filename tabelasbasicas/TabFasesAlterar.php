<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: TabFasesAlterar.php
// Autor: Rossana Lira
// Data: 23/04/03
// Objetivo: Programa de Alteração da Fases
// OBS.: Tabulação 2 espaços
// -------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include '../funcoes.php';

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFasesExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabFasesSelecionar.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
    $FasesCodigo = $_POST['FasesCodigo'];
    $FasesDescricao = strtoupper2(trim($_POST['FasesDescricao']));
    $Ordem = trim($_POST['Ordem']);
} else {
    $FasesCodigo = $_GET['FasesCodigo'];
}

// Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    $Url = "TabFasesExcluir.php?FasesCodigo=$FasesCodigo";
    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
} else 
    if ($Botao == "Voltar") {
        header("location: TabFasesSelecionar.php");
        exit();
    } else {
        if ($Critica == 1) {
            // Critica dos Campos #
            $Mens = 0;
            $Mensagem = "Informe: ";
            if ($FasesDescricao == "") {
                $Critica = 1;
                $LerTabela = 0;
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.Fases.FasesDescricao.focus();\" class=\"titulo2\">Fases</a>";
            }
            if ($Ordem == "") {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Critica = 1;
                $LerTabela = 0;
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.Fases.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
            } else {
                if (! SoNumeros($Ordem)) {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem = "<a href=\"javascript:document.Fases.Ordem.focus();\" class=\"titulo2\"> Ordem de Exibição Inválida</a>";
                }
            }
            if ($Mens == 0) {
                // Verifica a Duplicidade de Fases #
                $db = Conexao();
                $sql = "SELECT COUNT(CFASESCODI) FROM SFPC.TBFASES WHERE RTRIM(LTRIM(EFASESDESC)) = '$FasesDescricao' AND CFASESCODI <> $FasesCodigo";
                $result = $db->query($sql);
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $Qtd = $Linha[0];
                    if ($Qtd > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.Fases.FasesDescricao.focus();\" class=\"titulo2\"> Fases Já Cadastrada</a>";
                    } else {
                        // Verifica a Duplicidade da Ordem #
                        $sql = "SELECT COUNT(CFASESCODI) FROM SFPC.TBFASES WHERE AFASESORDE = $Ordem AND CFASESCODI <> $FasesCodigo";
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
                                // Atualiza Fases #
                                $Data = date("Y-m-d H:i:s");
                                $db->query("BEGIN TRANSACTION");
                                $sql = "UPDATE SFPC.TBFASES ";
                                $sql .= "   SET EFASESDESC = '$FasesDescricao', AFASESORDE = $Ordem, ";
                                $sql .= "       TFASESULAT = '$Data' ";
                                $sql .= " WHERE CFASESCODI = $FasesCodigo";
                                $result = $db->query($sql);
                                if (PEAR::isError($result)) {
                                    $db->query("ROLLBACK");
                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                } else {
                                    $db->query("COMMIT");
                                    $db->query("END TRANSACTION");
                                    $db->disconnect();
                                    
                                    // Envia mensagem para página selecionar #
                                    $Mensagem = urlencode("Fase Alterada com Sucesso");
                                    $Url = "TabFasesSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
                                    if (! in_array($Url, $_SESSION['GetUrl'])) {
                                        $_SESSION['GetUrl'][] = $Url;
                                    }
                                    header("location: " . $Url);
                                    exit();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

if ($Critica == 0) {
    $db = Conexao();
    $sql = "SELECT EFASESDESC,AFASESORDE,CFASESCODI FROM SFPC.TBFASES WHERE CFASESCODI = $FasesCodigo";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $FasesDescricao = $Linha[0];
            $Ordem = $Linha[1];
            $FasesCodigo = $Linha[2];
        }
    }
    $db->disconnect();
}
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script
    language="javascript"
    type=""
>
<!--
function enviar(valor){
	document.Fases.Botao.value=valor;
	document.Fases.submit();
}
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
        action="TabFasesAlterar.php"
        method="post"
        name="Fases"
    >
        <br>
        <br>
        <br>
        <br>
        <br>
        <table
            cellpadding="3"
            border="0"
        >
            <!-- Caminho -->
            <tr>
                <td width="150"><img
                    border="0"
                    src="../midia/linha.gif"
                    alt=""
                ></td>
                <td
                    align="left"
                    class="textonormal"
                ><font class="titulo2">|</font> <a href="../index.php"><font
                        color="#000000"
                    >Página Principal</font></a> > Tabelas > Fases > Manter</td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
                <td width="150"></td>
                <td
                    align="left"
                    colspan="2"
                ><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
	<?php } ?>
	<!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal">
                    <table
                        width="100%"
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
                            >MANTER - FASES</td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">Para atualizar a Fase,
                                    preencha os dados abaixo e clique no botão
                                    "Alterar". Para apagar a Fase clique no
                                    botão "Excluir".</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td
                                            class="textonormal"
                                            bgcolor="#DCEDF7"
                                        >Fases*</td>
                                        <td class="textonormal"><input
                                                type="text"
                                                name="FasesDescricao"
                                                size="40"
                                                maxlength="60"
                                                value="<?php echo $FasesDescricao; ?>"
                                                class="textonormal"
                                            > <input
                                                type="hidden"
                                                name="Critica"
                                                value="1"
                                            > <input
                                                type="hidden"
                                                name="FasesCodigo"
                                                value="<?php echo $FasesCodigo; ?>"
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
                                    type="button"
                                    value="Alterar"
                                    class="botao"
                                    onclick="javascript:enviar('Alterar');"
                                > <input
                                    type="button"
                                    value="Excluir"
                                    class="botao"
                                    onclick="javascript:enviar('Excluir');"
                                > <input
                                    type="button"
                                    value="Voltar"
                                    class="botao"
                                    onclick="javascript:enviar('Voltar')"
                                > <input
                                    type="hidden"
                                    name="Botao"
                                    value=""
                                ></td>
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
