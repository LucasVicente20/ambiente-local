<?php
# ---------------------------------------------------------
# Portal de Compras
# Programa: TabFornecedorTipoSituacaoIncluir.php
# Autor:    Lucas Baracho
# Data:     15/08/2018
# Objetivo: Tarefa Redmine 201311
# ---------------------------------------------------------

# Acesso ao arquivo de funções
include "../funcoes.php";

# Executa o controle de segurança
session_start();
Seguranca();

# Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $critica   = $_POST['Critica'];
    $descricao = strtoupper2(trim($_POST['Descricao']));
    $situacao  = $_POST['Situacao'];
}

# Crítica dos campos
if ($critica == 1) {
    $Mens     = 0;
    $Mensagem = "Informe: ";

    if ($descricao == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.TipoDocumento.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
    }
    # Verifica duplicidade da descrição
    $db = Conexao();

    $sql = "SELECT  COUNT(CFDOCSCODI)
            FROM    SFPC.TBFORNECEDORDOCUMENTOSITUACAO
            WHERE   RTRIM(LTRIM(EFDOCSDESC)) = '$descricao' ";
    
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $linha = $result->fetchRow();
        
        $qtd = $linha[0];

        if ($qtd > 0) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.TipoSituacao.Descricao.focus();\" class=\"titulo2\">Situação já cadastrada</a>";
        } else {
            # Recupera o último código cadastrado e incrementa mais um
            $sql = "SELECT  MAX(CFDOCSCODI)
                    FROM    SFPC.TBFORNECEDORDOCUMENTOSITUACAO";
            
            $result = $db->query($sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $linha = $result->fetchRow();
                
                $codigo = $linha[0] + 1;

                # Insere a descrição
                $usuario = $_SESSION['_cusupocodi_'];
                $data    = date("Y-m-d H:i:s");

                $db->query("BEGIN TRANSACTION");

                $sql = "INSERT  INTO SFPC.TBFORNECEDORDOCUMENTOSITUACAO (CFDOCSCODI, EFDOCSDESC, FFDOCSSITU, CUSUPOCODI, TFDOCTULAT)
                        VALUES  ($codigo, '$descricao', '$situacao', $usuario, '$data')";
                
                $result = $db->query($sql);

                if (PEAR::isError($result)) {
                    $db->query("ROLLBACK");
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $db->query("COMMIT");
                    $db->query("END TRANSACTION");

                    $Mens     = 1;
                    $Tipo     = 1;
                    $Mensagem = "Situação incluída com sucesso!";

                    # Limpando variáveis
                    $descricao = "";
                }
            }
        }
    }
    $db->disconnect();
}
?>

<html>
    <?php 
    # Carrega o layout padrão
    layout();
    ?>
    <script language="javascript" type="">
        <!--
            <?php  MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="TabFornecedorTipoSituacaoIncluir.php" method="post" name="Situacao">
            <br> <br> <br> <br> <br>
            <table cellpadding="3" border="0">
                <!-- Caminho -->
                <tr>
                    <td width="100">
                        <img border="0" src="../midia/linha.gif" alt="">
                    </td>
                    <td align="left" class="textonormal">
                        <font class="titulo2">|</font>
                        <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                        </a>
                        > Tabelas > Fornecedores > Documentos > Tipo Situação > Incluir
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
	            <?php   if ( $Mens == 1 ) {?>
	            <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2">
                        <?php  ExibeMens($Mensagem,$Tipo,1); ?>
                    </td>
                </tr>
	            <?php   } ?>
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
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - TIPO DE SITUAÇÃO</td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal">
                                                <p align="justify">Para incluir um nov tipo de situação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="textonormal" border="0" align="left" class="caixa">
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
                                                        <td class="textonormal">
                                                            <input type="text" name="Descricao" value="<?php  echo $descricao; ?>" size="45" maxlength="400" class="textonormal"> 
                                                            <input type="hidden" name="Critica" value="1">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                                        <td class="textonormal">
                                                            <select name="Situacao" size="1" value="A" class="textonormal">
                                                                <option value="A">ATIVO</option>
                                                                <option value="I">INATIVO</option>
                                                            </select>
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
        document.TipoSituacao.Situacao.focus();
    //-->
</script>