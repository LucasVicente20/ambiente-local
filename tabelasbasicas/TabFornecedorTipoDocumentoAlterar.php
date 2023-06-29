<?php
# -------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabFornecedorTipoDocumentoAlterar.php
# Autor:    Lucas Baracho
# Data:     16/08/2018
# Objetivo: Tarefa Redmine 201304
# -------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     12/11/2018
# Objetivo: Criar coluna "Ordem" (sem tarefa no Redmine)
# -------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFornecedorTipoDocumentoExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabFornecedorTipoDocumentoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao       = $_POST['Botao'];
    $Critica     = $_POST['Critica'];
    $Codigo      = $_POST['Codigo'];
    $Descricao   = strtoupper2(trim($_POST['Descricao']));
    $Situacao    = trim($_POST['Situacao']);
    $ordem       = trim($_POST['Ordem']);
    $obrigatorio = trim($_POST['Obrigatorio']);
} else {
    $Codigo = $_GET['Codigo'];
}

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    $Url = "TabFornecedorTipoDocumentoExcluir.php?Codigo=$Codigo";
    
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
} else if ($Botao == "Voltar") {
        header("location: TabFornecedorTipoDocumentoSelecionar.php");
        exit();
    } else {
        if ($Critica == 1) {
            # Critica dos Campos #
            $Mens = 0;
            $Mensagem = "Informe: ";
            
            if ($Descricao == "") {
                $Mens      = 1;
                $Tipo      = 2;
                $Mensagem .= "<a href=\"javascript:document.TipoDocumento.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
            }

            if ($ordem == "") {
                if ($Mens == 1) {
                    $Mensagem .= " e ";
                }

                $Mens      = 1;
                $Tipo      = 2;
                $Mensagem .= "<a href=\"javascript:document.TipoDocumento.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
            }

            if ($Mens == 0) {
                # Verifica a duplicidade #
                $db = Conexao();
                
                $sql = "SELECT  COUNT(CFDOCTCODI)
                        FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO
                        WHERE   RTRIM(LTRIM(EFDOCTDESC)) = '$Descricao'
                                AND CFDOCTCODI <> $Codigo ";
                
                $result = $db->query($sql);

                $sq2 = "SELECT  COUNT(CFDOCTCODI)
                        FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO
                        WHERE   AFDOCTORDE = $ordem
                                AND CFDOCTCODI <> $Codigo ";

                $resul2 = $db->query($sq2);
                
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } elseif (PEAR::isError($resul2)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sq2");
                } else {
                    $Linha = $result->fetchRow();
                    $Qtd = $Linha[0];

                    $Linh2 = $resul2->fetchRow();
                    $qtd2 = $Linh2[0];
                    
                    if ($Qtd > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.TipoDocumento.Descricao.focus();\" class=\"titulo2\">Documento já cadastrado</a>";
                    } elseif ($qtd2 > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.TipoDocumento.Ordem.focus();\" class=\"titulo2\">Ordem já cadastrada</a>";
                    } else {
                        # Atualiza ações #
                        $codUsuario = $_SESSION['_cusupocodi_'];
                        $Data = date("Y-m-d H:i:s");
                        
                        $db->query("BEGIN TRANSACTION");
                        
                        $sql = "UPDATE  SFPC.TBFORNECEDORDOCUMENTOTIPO
                                SET     EFDOCTDESC = '$Descricao',
                                        FFDOCTSITU = '$Situacao',
                                        AFDOCTORDE = $ordem,
                                        FFDOCTOBRI = '$obrigatorio',
                                        CUSUPOCODI = $codUsuario,
                                        TFDOCTULAT = '$Data'
                                WHERE   CFDOCTCODI = $Codigo";
                                
                        $result = $db->query($sql);
                                
                        if (PEAR::isError($result)) {
                            $db->query("ROLLBACK");
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        } else {
                            $db->query("COMMIT");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                                    
                            # Envia mensagem para página selecionar #
                            $Mensagem = urlencode("Tipo alterado com sucesso!");
                            $Url = "TabFornecedorTipoDocumentoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
                            
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

if ($Critica == 0) {
    $db = Conexao();
    
    $sql = "SELECT  CFDOCTCODI, EFDOCTDESC, FFDOCTSITU, AFDOCTORDE, FFDOCTOBRI
            FROM    SFPC.TBFORNECEDORDOCUMENTOTIPO
            WHERE   CFDOCTCODI = $Codigo";
    
    $result = $db->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $Descricao   = $Linha[1];
            $Situacao    = $Linha[2];
            $ordem       = $Linha[3];
            $obrigatorio = $Linha[4];
        }
    }
    $db->disconnect();
}

?>
<html>
    <?php 
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" type="">
        <!--
            function enviar(valor){
	            document.TipoDocumento.Botao.value=valor;
	            document.TipoDocumento.submit();
            }
            <?php  MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="TabFornecedorTipoDocumentoAlterar.php" method="post" name="TipoDocumento">
            <br> <br> <br> <br> <br>
                <table cellpadding="3" border="0">
                    <!-- Caminho -->
                    <tr>
                        <td width="150">
                            <img border="0" src="../midia/linha.gif" alt="">
                        </td>
                        <td align="left" class="textonormal">
                            <font class="titulo2">|</font> <a href="../index.php">
                            <font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > Documentos > Tipo Documento > Manter
                        </td>
                    </tr>
                    <!-- Fim do Caminho-->
                    <!-- Erro -->
	                <?php  if ( $Mens == 1 ) {?>
                    <tr>
                        <td width="150"></td>
                        <td align="left" colspan="2">
                            <?php  ExibeMens($Mensagem,$Tipo,1); ?>
                        </td>
                    </tr>
	                <?php  } ?>
	                <!-- Fim do Erro -->
                    <!-- Corpo -->
                    <tr>
                        <td width="150"></td>
                        <td class="textonormal">
                            <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">MANTER - TIPO DE DOCUMENTO</td>
                                </tr>
                                <tr>
                                    <td class="textonormal">
                                        <p align="justify">Para atualizar o tipo, preencha os dados abaixo e clique no botão "Alterar". Para apaga-lo, clique no botão "Excluir".</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
                                                <td class="textonormal">
                                                    <input type="text" name="Descricao" size="40" maxlength="60" value="<?php  echo $Descricao; ?>" class="textonormal"> 
                                                    <input type="hidden" name="Critica" value="1">
                                                    <input type="hidden" name="Codigo" value="<?php  echo $Codigo; ?>">
                                                </td>
                                            </tr>                          
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                                <td class="textonormal">
                                                    <?php   
                                                        if($Situacao == "A") {
                                                            $descSituacao = "ATIVO";
                                                        } else {
                                                            $descSituacao = "INATIVO";
                                                        }
	                                                ?>
	                                                <select name="Situacao" value="<?php  echo $descSituacao; ?>" class="textonormal">
	        	                                        <option value="A" <?php  if ( $Situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                                                        <option value="I" <?php  if ( $Situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
                                                    </select>                
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Ordem de exibição*</td>
                                                <td class="textonormal">
                                                    <input type="text" name="Ordem" size="3" maxlength="4" value="<?php  echo $ordem; ?>" class="textonormal"> 
                                                    <input type="hidden" name="Critica" value="1">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Documento obrigatório</td>
                                                <td class="textonormal">
                                                    <?php   
                                                        if($obrigatorio == "S") {
                                                            $descObri = "SIM";
                                                        } else {
                                                            $descObri = "NÃO";
                                                        }
	                                                ?>
	                                                <select name="Obrigatorio" value="<?php  echo $descObri; ?>" class="textonormal">
                                                        <option value="N" <?php  if ( $obrigatorio <> "S" ) { echo "selected"; }?>>NÃO</option>
	        	                                        <option value="S" <?php  if ( $obrigatorio == "S" ) { echo "selected"; }?>>SIM</option>
                                                    </select>                
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" align="right">
                                        <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
                                        <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
                                        <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
                                        <input type="hidden" name="Botao" value="">
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
        document.TipoDocumento.Descricao.focus();
    //-->
</script>
