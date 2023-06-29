<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabFornecedorTipoSituacaoAlterar.php
# Autor:    Lucas Baracho
# Data:     16/08/2018
# Objetivo: Tarefa Redmine 201311
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabFornecedorTipoSituacaoExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabFornecedorTipoSituacaoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao         = $_POST['Botao'];
    $Critica       = $_POST['Critica'];
    $Codigo    = $_POST['Codigo'];
    $descricao = strtoupper2(trim($_POST['Descricao']));
    $situacao      = trim($_POST['Situacao']);
} else {
    $Codigo = $_GET['Codigo'];
}

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    //exit;
    $Url = "TabFornecedorTipoSituacaoExcluir.php?Codigo=$Codigo";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
} else
    if ($Botao == "Voltar") {
        header("location: TabFornecedorTipoSituacaoSelecionar.php");
        exit();
    } else {
        if ($Critica == 1) {
            # Critica dos Campos #
            $Mens = 0;
            $Mensagem = "Informe: ";
            
            if ($descricao == "") {
                $Mens      = 1;
                $Tipo      = 2;
                $Mensagem .= "<a href=\"javascript:document.TipoSituacao.Descricao.focus();\" class=\"titulo2\">Descrição</a>";
            }

            if ($Mens == 0) {
                # Verifica a duplicidade #
                $db = Conexao();
                $sql = "SELECT COUNT(CFDOCSCODI) FROM SFPC.TBFORNECEDORDOCUMENTOSITUACAO WHERE RTRIM(LTRIM(EFDOCSDESC)) = '$descricao' AND FFDOCSSITU = '$situacao' ";
                $result = $db->query($sql);
                
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $Qtd = $Linha[0];
                    if ($Qtd > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.TipoSituacao.Descricao.focus();\" class=\"titulo2\">Descrição já cadastrada</a>";
                    }  else {
                                # Atualiza ações #
                                $codUsuario = $_SESSION['_cusupocodi_'];
                                $Data = date("Y-m-d H:i:s");
                                $db->query("BEGIN TRANSACTION");
                                $sql  = "UPDATE SFPC.TBFORNECEDORDOCUMENTOSITUACAO ";
                                $sql .= "SET    EFDOCSDESC = '$descricao', FFDOCSSITU = '$situacao', ";
                                $sql .= "       CUSUPOCODI = $codUsuario, ";
                                $sql .= "       TFDOCTULAT = '$Data' ";
                                $sql .= " WHERE CFDOCSCODI = $Codigo";
                                
                                $result = $db->query($sql);
                                
                                if (PEAR::isError($result)) {
                                    $db->query("ROLLBACK");
                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                } else {
                                    $db->query("COMMIT");
                                    $db->query("END TRANSACTION");
                                    $db->disconnect();
                                    
                                    # Envia mensagem para página selecionar #
                                    $Mensagem = urlencode("Situação alterada com sucesso!");
                                    $Url = "TabFornecedorTipoSituacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
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
        //}
   // }
if ($Critica == 0) {
    $db = Conexao();
    $sql = "SELECT CFDOCSCODI, EFDOCSDESC, FFDOCSSITU FROM SFPC.TBFORNECEDORDOCUMENTOSITUACAO WHERE CFDOCSCODI = $Codigo";
    $result = $db->query($sql);
    
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $descricao         = $Linha[1];
            $situacao = $Linha[2];
            
        }
    }
    $db->disconnect();
}

/*
$db = Conexao();
$sql = "SELECT EGREMPDESC FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $grupo";
$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ". __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        $grupoDescricao = $Linha[0];
    }
}*/

?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TipoSituacao.Botao.value=valor;
	document.TipoSituacao.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabFornecedorTipoSituacaoAlterar.php" method="post" name="TipoSituacao">
    <br> <br> <br> <br> <br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="150">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font> <a href="../index.php">
                    <font color="#000000">Página Principal</font></a> > Tabelas > Fornecedores > Documentos > Tipo Situação > Manter
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
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">MANTER - TIPO DE SITUAÇÃO</td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">Para atualizar o tipo de situação, preencha os dados abaixo e clique no botão "Alterar". Para apaga-lo, clique no botão "Excluir".</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                    
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
                                        <td class="textonormal">
                                            <input type="text" name="Descricao" size="40" maxlength="60" value="<?php  echo $descricao; ?>" class="textonormal"> 
                                            <input type="hidden" name="Critica" value="1">
                                            <input type="hidden" name="Codigo" value="<?php  echo $Codigo; ?>">
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                        <td class="textonormal">
	                                        <?php   if($situacao == "A") {
                                                    $descSituacao = "ATIVO";
                                                } else {
                                                    $descSituacao = "INATIVO";
                                                }
	                                        ?>
	                                        <select name="Situacao" value="<?php  echo $descSituacao; ?>" class="textonormal">
	        	                                <option value="A" <?php  if ( $situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                                                <option value="I" <?php  if ( $situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
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
document.TipoSituacao.Descricao.focus();
//-->
</script>
