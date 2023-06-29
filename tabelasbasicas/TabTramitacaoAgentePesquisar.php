<?php
# ------------------------------------------------------------------
# Portal de Compras
# Programa: TabTramitacaoAgenteIncluir.php
#
# Autor:    Caio Coutinho
# Data:     23/07/2018
# Objetivo: Tarefa Redmine 199103
# ------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

$db = Conexao();

# Grupos
$grupo = null;
if($_SESSION['_fperficorp_'] != 'S') {
    $grupo = $_SESSION['_cgrempcodi_'];
}

$grupos = getByGrupos($db, $grupo);

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
    $botao  = $_POST['Botao'];
    $agente = $_POST['agenteGrupo'];

} else {
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
    $Mens     = $_GET['Mens'];
    $Mensagem = $_GET['Mensagem'];
}

if ($botao == 'Selecionar') {
    if(!empty($agente)) {
        $Url = 'TabTramitacaoAgenteManter.php?agente=' . $agente;
        header("location: " . $Url);
        exit();
    } else {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputAgente\").focus();' class='titulo2'>Agente</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
    }
}

?>
<html>
<?php
# Carrega o layout padrão
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="JavaScript" src="../jquery.select-list-actions.js"></script>

<script language="javascript" type="">
    <?php MenuAcesso(); ?>
</script>
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabTramitacaoAgentePesquisar.php" method="post" name="TabTramitacaoAgentePesquisar">
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
                > Tabelas > Licitações > Tramitação > Agente > Manter
            </td>
        </tr>
        <!-- Fim do Caminho-->
        <!-- Erro -->
        <?php if ( $Mens == 1 ) {?>
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php ExibeMens($Mensagem,$Tipo,1); ?>
                </td>
            </tr>
        <?php } ?>
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
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">MANTER - AGENTE</td>
                                </tr>
                                <tr>
                                    <td class="textonormal">
                                        <p align="justify">Para manter um agente, selecione abaixo o agente desejado e clique no botão "Selecionar". Os itens obrigatórios estão com *.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <table class="textonormal" border="0" align="left" class="caixa">
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Agente*</td>
                                                <td class="textonormal">
                                                    <select id="inputAgente" name="agenteGrupo" class="textonormal" style="width: 540px;">
                                                        <option value="">Selecione um agente...</option>
                                                        <?php  # Mostra os grupos #
                                                        foreach ($grupos as $key => $value) {
                                                            if(!empty($value[0]['agente'])) {
                                                                ?>
                                                                <option disabled value=""><?php echo $key; ?></option>
                                                            <?php
                                                                foreach ($value as $key_ => $value_) {
                                                                    ?>
                                                                    <option value="<?php echo $value_['agente']; ?>"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;" . strtoupper($value_['descricao']); ?></option>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" align="right">
                                        <input type="submit" name="Botao" value="Selecionar" class="botao">
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