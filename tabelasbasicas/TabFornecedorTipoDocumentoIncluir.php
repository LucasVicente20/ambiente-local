<?php
# -------------------------------------------------------------------------------------------------
# Portal de Compras
# Programa: TabFornecedorTipoDocumentoIncluir.php
# Autor:    Lucas Baracho
# Data:     15/08/2018
# Objetivo: Tarefa Redmine 201304
# -------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     12/11/2018
# Objetivo: Criar coluna "Ordem" (sem tarefa no Redmine)
# -------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções
include "../funcoes.php";

# Executa o controle de segurança
session_start();
Seguranca();

# Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $critica     = $_POST['Critica'];
    $descricao   = strtoupper2(trim($_POST['Descricao']));
    $situacao    = $_POST['Situacao'];
    $ordem       = trim($_POST['Ordem']);
    $obrigatorio = $_POST['Obrigatorio'];
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

    if ($ordem == "") {
        if ($Mens == 1) {
            $Mensagem .= " e ";
        }
        
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.TipoDocumento.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
    }
    
    if ($Mens == 0) {
        # Verifica duplicidade da descrição
        $db = Conexao();

        $sql = "SELECT  COUNT(CFDOCTCODI)
                FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO
                WHERE   RTRIM(LTRIM(EFDOCTDESC)) = '$descricao' ";
    
        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $linha = $result->fetchRow();
        
            $qtd = $linha[0];

            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.TipoDocumento.Descricao.focus();\" class=\"titulo2\">Descrição já cadastrada</a>";
            } else {
                # Verifica duplicidade da ordem
                $sq2 = "SELECT  COUNT(CFDOCTCODI)
                        FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO
                        WHERE   AFDOCTORDE = $ordem ";
            
                $resul2 = $db->query($sq2);

                if (PEAR::isError($resul2)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sq2");
                } else {
                    $linh2 = $resul2->fetchRow();

                    $qtd2 = $linh2[0];

                    if ($qtd2 > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.TipoDocumento.Ordem.focus();\" class=\"titulo2\">Ordem já cadastrada</a>";
                    } else {
                        # Recupera o último código cadastrado e incrementa mais um
                        $sql = "SELECT  MAX(CFDOCTCODI)
                                FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO";
            
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
                
                            $sql = "INSERT  INTO SFPC.TBFORNECEDORDOCUMENTOTIPO (CFDOCTCODI, EFDOCTDESC, FFDOCTSITU, AFDOCTORDE, FFDOCTOBRI, CUSUPOCODI, TFDOCTULAT)
                                    VALUES  ($codigo, '$descricao', '$situacao', $ordem, '$obrigatorio', $usuario, '$data')";
                
                            //print_r($sql);exit;
                            $result = $db->query($sql);
                
                            if (PEAR::isError($result)) {
                                $db->query("ROLLBACK");
                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                            } else {
                                $db->query("COMMIT");
                                $db->query("END TRANSACTION");
                    
                                $Mens     = 1;
                                $Tipo     = 1;
                                $Mensagem = "Tipo de documento incluído com sucesso!";
                    
                                # Limpando variáveis
                                $descricao   = "";
                                $situacao    = "";
                                $ordem       = "";
                                $obrigatorio = "";
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
        <form action="TabFornecedorTipoDocumentoIncluir.php" method="post" name="Documento">
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
                        > Tabelas > Fornecedores > Documentos > Tipo Documento > Incluir
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
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - TIPO DE DOCUMENTO</td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal">
                                                <p align="justify">Para incluir um novo tipo do documento, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.</p>
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
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Ordem de exibição</td>
                                                        <td class="textonormal">
                                                            <input type="text" name="Ordem" value="<?php  echo $ordem; ?>" size="3" maxlength="4" class="textonormal">
                                                            <input type="hidden" name="Critica" value="1">
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Documento obrigatório</td>
                                                        <td class="textonormal">
                                                            <select name="Obrigatorio" size="1" value="N" class="textonormal">
                                                                <option value="N">NÃO</option>
                                                                <option value="S">SIM</option>
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
        document.TipoDocumento.Documento.focus();
    //-->
</script>