<?php
/*
Arquivo: TabFornecedorNaturezaJuridicaAlterar.php
Nome: Lucas André e Lucas Vicente
Data: 29/11/2022
Tarefa: CR 275539
----------------------------------------------------------------------------
Arquivo: TabFornecedorNaturezaJuridicaIncluir.php
Nome: Lucas André
Data: 26/04/2023
Tarefa: CR 282152
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
    $DescNaturezaJuridica 	= mb_convert_case($_POST['DescNaturezaJuridica'], MB_CASE_UPPER);
    $CodNaturezaJuridica    = $_POST['CodNaturezaJuridica'];
}

if ($Critica == 1) {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($DescNaturezaJuridica == "" AND $CodNaturezaJuridica == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Natureza Jurídica e Código da Natureza Juridica</a>";
    } else if ($CodNaturezaJuridica == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Código da Natureza Jurídica</a>";
    } else if ($DescNaturezaJuridica == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Natureza Jurídica</a>";
    } if ($Mens == 0) {
      
		// Verifica a Duplicidade de Natureza Jurídica #
        $db = Conexao();
		
		$CodigoUsuario = $_SESSION['_cusupocodi_'];
		
        $sql = "SELECT COUNT(cfornjsequ) FROM SFPC.tbfornecedortiponaturezajuridica WHERE RTRIM(LTRIM(efornjtpnj)) = '$DescNaturezaJuridica'";
        $result = $db->query($sql);

        if (db::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $QtdDesc = $Linha[0]; 
            if ($QtdDesc > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Natureza Jurídica já cadastrada.</a>";
            } else {
                $sql2 = "SELECT  count(afornjcodi) FROM SFPC.tbfornecedortiponaturezajuridica WHERE afornjcodi = $CodNaturezaJuridica ";
                $result2 = $db->query($sql2);
                if (db::isError($result2)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha2 = $result2->fetchRow();
                    $QtdCod = $Linha2[0];
                    if($QtdCod > 0){
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem .= "<a href=\"javascript:document.Lote.NaturezaJuridica.focus();\" class=\"titulo2\">Código da Natureza Jurídica já cadastrado.</a>";
                    } else {
                        // Recupera a última Natureza Jurídica e incrementa mais um #
                        $sql = "SELECT MAX(cfornjsequ) FROM SFPC.tbfornecedortiponaturezajuridica";
                        $result = $db->query($sql);
                        if (db::isError($result)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $Linha = $result->fetchRow();
                            $Codigo = $Linha[0] + 1;
                            
                            // Insere Natureza Jurídica #
                            $Data = date("Y-m-d H:i:s");
                            $db->query("BEGIN TRANSACTION");
                            $sql = "INSERT INTO SFPC.tbfornecedortiponaturezajuridica ( ";
                            $sql .= "cfornjsequ, cusupocodi, efornjtpnj, tfornjulat, afornjcodi ";
                            $sql .= ") VALUES ( ";
                            $sql .= "$Codigo, $CodigoUsuario, '$DescNaturezaJuridica', '$Data', '$CodNaturezaJuridica')";
                            $result = $db->query($sql);
                            if (db::isError($result)) {
                                $db->query("ROLLBACK");
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $db->query("COMMIT");
                                $db->query("END TRANSACTION");
                                
                                $Mens = 1;
                                $Tipo = 1;
                                $Mensagem = "Natureza Jurídica incluída com sucesso!";
                                
                                // Limpando Variáveis #
                                $DescNaturezaJuridica = "";
                                $CodNaturezaJuridica  = "";
                                
                            }
                        }
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
        <form action="TabFornecedorNaturezaJuridicaIncluir.php" method="post" name="DescNaturezaJuridica">
            <br> <br> <br> <br> <br>
            <table cellpadding="3" border="0">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal">
                        <font class="titulo2"> |</font> <a href="../index.php">
                        <font color="#000000">
                        Página Principal </font></a> > Tabelas > Fornecedores > Natureza Jurídica > Incluir
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
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - NATUREZA JURÍDICA</td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal">
                                                <p align="justify">Para incluir uma nova natureza jurídica, insira a mesma no campo abaixo e clique em "Incluir".</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="textonormal" border="0" align="left" class="caixa">
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Código da Natureza Jurídica:  </td>
                                                        <td class="textonormal">
                                                            <input type="text" name="CodNaturezaJuridica" value="<?php echo $CodNaturezaJuridica; ?>" size="45" maxlength="60" class="textonormal">
                                                            <input type="hidden" name="Critica" value="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Natureza Jurídica:  </td>
                                                        <td class="textonormal">
                                                            <input type="text" name="DescNaturezaJuridica" value="<?php echo $DescNaturezaJuridica; ?>" size="45" maxlength="60" class="textonormal">
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
    document.Lote.DescNaturezaJuridica.focus();
    document.Lote.CodNaturezaJuridica.focus();
    //-->
</script>