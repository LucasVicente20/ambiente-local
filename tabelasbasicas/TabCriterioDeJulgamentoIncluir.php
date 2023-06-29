<?php

/*
Arquivo: TabCriterioDeJulgamentoAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 
Tarefa: CR 276712

*/

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica 				= $_POST['Critica'];
    $DescCriterioDeJulgamento 	= strtoupper2(trim($_POST['DescCriterioDeJulgamento']));
}

if ($Critica == 1) {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($DescCriterioDeJulgamento == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.CriterioDeJulgamento.focus();\" class=\"titulo2\">Criterio De Julgamento</a>";
    } if ($Mens == 0) {
      
		// Verifica a Duplicidade do Critério de Julgamento #
        $db = Conexao();
		
		$CodigoUsuario = $_SESSION['_cusupocodi_'];
		
		
        $sql = "SELECT COUNT(ccrjulcodi) FROM SFPC.tbcriteriojulgamento WHERE RTRIM(LTRIM(ecrjulnome)) = '$DescCriterioDeJulgamento' ";
        $result = $db->query($sql);
        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Lote.CriterioDeJulgamento.focus();\" class=\"titulo2\">Criterio De Julgamento já cadastrado.</a>";
            } else  {
                        // Recupera o ultimo Critério de Julgamento e incrementa mais um #
                        $sql = "SELECT MAX(ccrjulcodi) FROM SFPC.tbcriteriojulgamento";
                        $result = $db->query($sql);
                        if (db::isError($result)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $Linha = $result->fetchRow();
                            $Codigo = $Linha[0] + 1;
                            
                            // Insere Critério de Julgamento #
                            $Data = date("Y-m-d H:i:s");
                            $db->query("BEGIN TRANSACTION");
                            $sql = "INSERT INTO SFPC.tbcriteriojulgamento ( ";
                            $sql .= "ccrjulcodi, cusupocodi, ecrjulnome, tcrjululat ";
                            $sql .= ") VALUES ( ";
                            $sql .= "$Codigo, $CodigoUsuario, '$DescCriterioDeJulgamento', '$Data')";
                            $result = $db->query($sql);
                            if (db::isError($result)) {
                                $db->query("ROLLBACK");
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $db->query("COMMIT");
                                $db->query("END TRANSACTION");
                                
                                $Mens = 1;
                                $Tipo = 1;
                                $Mensagem = "Criterio De Julgamento incluído com sucesso!";
                                
                                // Limpando Variáveis #
                                $DescCriterioDeJulgamento = "";
                                
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
        action="TabCriterioDeJulgamentoIncluir.php"
        method="post"
        name="DescCriterioDeJulgamento"
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
                    >Página Principal</font></a> > Tabelas > Licitações > Criterio De Julgamento > Incluir</td>
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
                                        >INCLUIR - CRITERIO DE JULGAMENTO</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir um
                                                novo Criterio De Julgamento, insira a mesma no campo abaixo e clique em "Incluir".</p>
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
                                                    >Situação do Criterio De Julgamento:  </td>
                                                    <td class="textonormal"><input
                                                            type="text"
                                                            name="DescCriterioDeJulgamento"
                                                            value="<?php echo $DescCriterioDeJulgamento; ?>"
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
document.Lote.DescCriterioDeJulgamento.focus();
//-->
</script>
