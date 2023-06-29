<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: TabSituacaoFornecedorPregaoPresencialIncluir.php
// Autor: Lucas Baracho
// Data: 15/05/2017
// Objetivo: Programa de inclusão de situação do fornecedor / pregão presencial
// OBS.: Tabulação 2 espaços
// -------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica 				= $_POST['Critica'];
    $SituacaoFornecedor 	= strtoupper2(trim($_POST['SituacaoFornecedor']));
}

if ($Critica == 1) {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($SituacaoFornecedor == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.SituacaoFornecedor.focus();\" class=\"titulo2\">Situação</a>";
    } if ($Mens == 0) {
      
		// Verifica a Duplicidade de situação #
        $db = Conexao();
		
		$CodigoUsuario = $_SESSION['_cusupocodi_'];
		
		
        $sql = "SELECT COUNT(cpresfsequ) FROM SFPC.tbpregaopresencialsituacaofornecedor WHERE RTRIM(LTRIM(epresfnome)) = '$SituacaoFornecedor' ";
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Lote.SituacaoFornecedor.focus();\" class=\"titulo2\">Situação do fornecedor já cadastrada.</a>";
            } else  {
                        // Recupera a última situação e incrementa mais um #
                        $sql = "SELECT MAX(cpresfsequ) FROM SFPC.tbpregaopresencialsituacaofornecedor";
                        $result = $db->query($sql);
                        if (PEAR::isError($result)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $Linha = $result->fetchRow();
                            $Codigo = $Linha[0] + 1;
                            
                            // Insere situação #
                            $Data = date("Y-m-d H:i:s");
                            $db->query("BEGIN TRANSACTION");
                            $sql = "INSERT INTO SFPC.tbpregaopresencialsituacaofornecedor ( ";
                            $sql .= "cpresfsequ, cusupocodi, epresfnome, tpresfulat ";
                            $sql .= ") VALUES ( ";
                            $sql .= "$Codigo, $CodigoUsuario, '$SituacaoFornecedor', '$Data')";
                            $result = $db->query($sql);
                            if (PEAR::isError($result)) {
                                $db->query("ROLLBACK");
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $db->query("COMMIT");
                                $db->query("END TRANSACTION");
                                
                                $Mens = 1;
                                $Tipo = 1;
                                $Mensagem = "Situação incluída com sucesso!";
                                
                                // Limpando Variáveis #
                                $SituacaoFornecedor = "";
                                
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
        action="TabSituacaoFornecedorPregaoPresencialIncluir.php"
        method="post"
        name="SituacaoFornecedor"
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
                    >Página Principal</font></a> > Tabelas > Pregão Presencial > Situação Fornecedor > Incluir</td>
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
                                        >INCLUIR - SITUAÇÃO DO FORNECEDOR</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir uma
                                                nova situação, insira a mesma no campo abaixo e clique em "Incluir".</p>
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
                                                    >Situação do fornecedor:  </td>
                                                    <td class="textonormal"><input
                                                            type="text"
                                                            name="SituacaoFornecedor"
                                                            value="<?php echo $SituacaoFornecedor; ?>"
                                                            size="45"
                                                            maxlength="60"
                                                            class="textonormal"
                                                        > <input
                                                            type="hidden"
                                                            name="Critica"
                                                            value="1"
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
document.Lote.SituacaoFornecedor.focus();
//-->
</script>
