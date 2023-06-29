<?php
# -----------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     08/01/2019
# Objetivo: Tarefa Redmine 208656
# -----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';
require_once "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadConsultaSofin.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao    	= $_POST['Botao'];
    $ano        = $_POST['ano'];
    $orgao      = $_POST['orgao'];
    $unidade    = $_POST['unidade'];
    $destinacao = $_POST['destinacao'];
    $sequencial = $_POST['sequencial'];
}
$Mensagem = "";
$Mens     = 0;
$Tipo     = 0;

if ($Botao=="Pesquisar") {
    if (is_null($ano) || ($ano=="")) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadConsultaSofin.ano.focus();\" class=\"titulo2\">Ano do Bloqueio</a>";
    }

    if (is_null($orgao) || ($orgao=="")) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadConsultaSofin.orgao.focus();\" class=\"titulo2\">Órgão do Bloqueio</a>";
    }

    if (is_null($unidade) || ($unidade=="")) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadConsultaSofin.unidade.focus();\" class=\"titulo2\">Unidade do Bloqueio</a>";
    }

    if (is_null($destinacao) || ($destinacao=="")) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadConsultaSofin.destinacao.focus();\" class=\"titulo2\">Destinação do Bloqueio</a>";
    }

    if (is_null($sequencial) || ($sequencial=="")) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadConsultaSofin.sequencial.focus();\" class=\"titulo2\">Seqüencial</a>";
    }

    if ($Mens==0) {
        $dbOracle = ConexaoOracle();
        $BloqueioTodos = '';

        $BloqueioTodos .= sprintf('%04s', $ano);
        $BloqueioTodos .= '.' . sprintf('%02s', $orgao);
        $BloqueioTodos .= '.' . sprintf('%02s', $unidade);
        $BloqueioTodos .= '.' . sprintf('%01s', $destinacao);
        $BloqueioTodos .= '.' . sprintf('%04s', $sequencial);
        $BloqueioTodosData = getDadosBloqueio($dbOracle, $BloqueioTodos);
    }
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
    <!--
    function enviar(valor){
        document.CadConsultaSofin.Botao.value = valor;
        document.CadConsultaSofin.submit();
    }
    function AbreJanela(url,largura,altura) {
        window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
    }
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadConsultaSofin.php" method="post" name="CadConsultaSofin">
    <br/><br/><br/><br/><br/>
    <table cellpadding="3" border="0" width="100%" summary="" width="650px">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Consultar Bloqueio
            </td>
        </tr>
        <!-- Fim do Caminho-->
        <!-- Erro -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" colspan="1">
                <?php if ($Mens != 0) { ExibeMens($Mensagem,$Tipo,1); }?>
            </td>
        </tr>
        <!-- Fim do Erro -->
        <!-- Corpo -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td class="textonormal">
                <table border="0" cellspacing="0" cellpadding="3" summary="">
                    <tr>
                        <td class="textonormal">
                            <table width="650px" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                <input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="1">
                                        CONSULTAR BLOQUEIO
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" colspan="1">
                                        <p align="justify">
                                            Para ver um Bloqueio, preencha os campos Ano, Órgão, Unidade, Destinação e Sequêncial.
                                            Depois, clique no botão "Pesquisar".<br/>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1">
                                        <table border="0" width="100%" summary="">
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Ano*</td>
                                                <td class="textonormal" height="20">
                                                    <input type=text name="ano" class="textonormal" value="<?php echo $ano; ?>" size="4" maxlength="4">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Órgão*</td>
                                                <td class="textonormal" height="20">
                                                    <input type=text name="orgao" class="textonormal" value="<?php echo $orgao; ?>" size="2" maxlength="2">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Unidade*</td>
                                                <td class="textonormal" height="20">
                                                    <input type=text name="unidade" class="textonormal" value="<?php echo $unidade; ?>" size="2" maxlength="2">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Destinação*</td>
                                                <td class="textonormal" height="20">
                                                    <input type=text name="destinacao" class="textonormal" value="<?php echo $destinacao; ?>" size="3" maxlength="3">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Sequencial*</td>
                                                <td class="textonormal" height="20">
                                                    <input type=text name="sequencial" class="textonormal" value="<?php echo $sequencial; ?>" size="5" maxlength="5">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="right">
                                        <input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
                                        <input type="button" value="Limpar" class="botao" onclick="javascript:enviar('');">
                                        <input type="hidden" name="Botao" value="">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <?php if (($Botao=="Pesquisar") && ($Mens == 0)) { ?>
                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" width="656px">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="10">
                                RESULTADO DA PESQUISA
                            </td>
                        </tr>
                        <?php
                            if (empty($BloqueioTodosData)) {
                                ?>
                                <tr>
                                    <td>Nenhum Bloqueio encontrado.</td>
                                </tr>
                                <?php
                            } else {
                        ?>
                        <tr>
                            <td align="center" bgcolor="#F7F7F7" valign="middle" class="text" colspan="10">
                                <b>Bloqueio</b>
                            </td>
                        </tr>
                        <tr>
                            <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Ano</td>
                            <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Órgão</td>
                            <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Unidade</td>
                            <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Destinação</td>
                            <td class="titulo3" bgcolor="#F7F7F7" width="1" align="center">Sequencial</td>
                            <td class="titulo3" bgcolor="#F7F7F7" width="20%" align="center">Valor do Bloqueio</td>
                        </tr>
                        <tr>
                            <td class="texto" align="center"><?=$ano?></td>
                            <td class="texto" align="center"><?=$orgao?></td>
                            <td class="texto" align="center"><?=$unidade?></td>
                            <td class="texto" align="center"><?=$destinacao?></td>
                            <td class="texto" align="center"><?=$sequencial?></td>
                            <td class="texto" align="center"><?= 'R$ ' . converte_valor_estoques($BloqueioTodosData['valorTotal']); ?></td>
                        </tr>
                    </table>
                    <?php
                        $dbOracle->disconnect();
                    }
                }
            ?>
            </td>
        </tr>
        <!-- Fim do Corpo -->
    </table>
</form>
</body>
</html>