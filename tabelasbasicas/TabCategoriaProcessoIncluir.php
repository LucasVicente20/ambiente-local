<?php
/*
Arquivo: TabCategoriaProcessoAlterar.php
Nome: Lucas André
Data: 27/04/2023
Tarefa: CR 282318
----------------------------------------------------------------------------
*/
// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica 				= $_POST['Critica'];
    $DescCategoriaProcesso 	= mb_convert_case($_POST['DescCategoriaProcesso'], MB_CASE_UPPER);
}

if ($Critica == 1) {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($DescCategoriaProcesso == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.DescCategoriaProcesso.focus();\" class=\"titulo2\">Cateogoria do Processo.</a>";
    }
    if ($Mens == 0) {
      
		// Verifica a Duplicidade da Categoria do Processo #
        $db = Conexao();
		
		$CodigoUsuario = $_SESSION['_cusupocodi_'];
		
        $sql = "SELECT COUNT(cpnccpcodi) FROM sfpc.tbpncpdominiocategoriaprocesso WHERE RTRIM(LTRIM(epnccpnome)) = '$DescCategoriaProcesso'";
        $result = $db->query($sql);

        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $QtdDesc = $Linha[0]; 
            if ($QtdDesc > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Lote.DescCategoriaProcesso.focus();\" class=\"titulo2\">Categoria do Processo já cadastrada.</a>";
            } else {
                // Recupera a última Categoria do Processo e incrementa mais um #
                $sql = "SELECT MAX(cpnccpcodi) FROM sfpc.tbpncpdominiocategoriaprocesso";
                $result = $db->query($sql);
                if (db::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $Codigo = $Linha[0] + 1;
                            
                    // Insere Categoria do Processo #
                    $Data = date("Y-m-d H:i:s");
                    $db->query("BEGIN TRANSACTION");
                    $sql = "INSERT INTO sfpc.tbpncpdominiocategoriaprocesso ( ";
                    $sql .= "cpnccpcodi, cusupocodi, epnccpnome, tpnccpulat";
                    $sql .= ") VALUES ( ";
                    $sql .= "$Codigo, $CodigoUsuario, '$DescCategoriaProcesso', '$Data')";
                    $result = $db->query($sql);
                    if (db::isError($result)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                                
                        $Mens = 1;
                        $Tipo = 1;
                        $Mensagem = "Categoria do Processo incluída com sucesso!";
                                
                        // Limpando Variáveis #
                        $DescCategoriaProcesso = "";                                
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
        <!--
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="TabCategoriaProcessoIncluir.php" method="post" name="DescCategoriaProcesso">
            <br> <br> <br> <br> <br>
            <table cellpadding="3" border="0">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal">
                        <font class="titulo2"> |</font> <a href="../index.php">
                        <font color="#000000">
                        Página Principal </font></a> > Tabelas > PNCP > Contratos > Categoria do Processo > Incluir
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
                            <?php ExibeMens($Mensagem,$Tipo,1); ?>
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
                        <table border="0" cellspacing="0" cellpadding="3">
                            <tr>
                                <td class="textonormal">
                                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                        <tr>
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - CATEGORIA DO PROCESSO</td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal">
                                                <p align="justify">Para incluir uma nova Categoria do Processo, insira a mesma no campo abaixo e clique em "Incluir".</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="textonormal" border="0" align="left" class="caixa">
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Categoria do Processo:  </td>
                                                        <td class="textonormal">
                                                            <input type="text" name="DescCategoriaProcesso" value="<?php echo $DescCategoriaProcesso; ?>" size="45" maxlength="60" class="textonormal">
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
    document.Lote.DescCategoriaProcesso.focus();
    //-->
</script>