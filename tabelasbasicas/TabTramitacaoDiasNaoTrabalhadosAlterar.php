<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabTramitacaoDiasNaoTrabalhadosAlterar.php
# Autor:    Ernesto Ferreira
# Data:     20/07/2018
# Objetivo: Tarefa Redmine 199105
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabTramitacaoDiasNaoTrabalhadosExcluir.php');
AddMenuAcesso('/tabelasbasicas/TabTramitacaoDiasNaoTrabalhadosSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao         = $_POST['Botao'];
    $Critica       = $_POST['Critica'];
    $dianCodigo    = $_POST['DianCodigo'];
    $dianDescricao = strtoupper2(trim($_POST['DianDescricao']));
    $dianAno         = trim($_POST['Ano']);
    $dianMes         = trim($_POST['Mes']);
    $dianDia      = trim($_POST['Dia']);

} else {
    $dianCodigo = $_GET['DianCodigo'];
}

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
    //exit;
    $Url = "TabTramitacaoDiasNaoTrabalhadosExcluir.php?DianCodigo=$dianCodigo";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
} else
    if ($Botao == "Voltar") {
        header("location: TabTramitacaoDiasNaoTrabalhadosSelecionar.php");
        exit();
    } else {
        if ($Critica == 1) {
            # Critica dos Campos #
            $Mens = 0;
            $Mensagem = "Informe: ";
            
            if ($dianDescricao == "") {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.DiasNaoTrabalhados.DianDescricao.focus();\" class=\"titulo2\">Descrição do Dia não trabalhado</a>";
            }
        
            if ($dianAno == "") {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.DiasNaoTrabalhados.Ano.focus();\" class=\"titulo2\">Ano</a>";
            } else {
                if (!SoNumeros($dianAno)) {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.Ano.focus();\" class=\"titulo2\">Ano inválido</a>";
                }
        
            }
        
            if ($dianMes == "") {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.DiasNaoTrabalhados.Mes.focus();\" class=\"titulo2\">Mẽs</a>";
            } else {
                if (!SoNumeros($dianMes)) {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.Mes.focus();\" class=\"titulo2\">Mês inválido</a>";
                }
            }
        
            if ($dianDia == "") {
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"javascript:document.DiasNaoTrabalhados.Dia.focus();\" class=\"titulo2\">Dia</a>";
            } else {
                if (!SoNumeros($dianDia)) {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.Dia.focus();\" class=\"titulo2\">Dia inválido</a>";
                }
            }
        
        
            if($dianDia != "" && $dianMes != "" && $dianAno != ""){
                if(!checkdate ( $dianMes , $dianDia , $dianAno )){
                    if ($Mens == 1) {
                        $Mensagem .= ", ";
                    }
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem .= "<a href=\"javascript:document.DiasNaoTrabalhados.Dia.focus();\" class=\"titulo2\">Data inválida</a>";
                }
        
        
        
            }

            if ($Mens == 0) {
                # Verifica a duplicidade de dias não trabalhados #
                $db = Conexao();
                $sql = "SELECT COUNT(CTDIANSEQU) FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE RTRIM(LTRIM(ETDIANDESC)) = '$dianDescricao' AND CTDIANSEQU <> $dianCodigo";
                $result = $db->query($sql);
                
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $Qtd = $Linha[0];
                    if ($Qtd > 0) {
                        $Mens = 1;
                        $Tipo = 2;
                        $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.DianDescricao.focus();\" class=\"titulo2\">Dia não trabalhado já cadastrado</a>";
                    } else {
                        # Verifica a Duplicidade da Ordem #
                        $sql = "SELECT COUNT(CTDIANSEQU) FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE ATDIANANOT = $dianAno AND ATDIANMEST = $dianMes AND ATDIANDIAT= $dianDia";
                        $result = $db->query($sql);

                            if ($Qtd > 0) {
                                $Mens = 1;
                                $Tipo = 2;
                                $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.Dia.focus();\" class=\"titulo2\">Dia não trabalhado já cadastrado para este grupo</a>";
                            } else {
                                # Atualiza ações #
                                $codUsuario = $_SESSION['_cusupocodi_'];
                                $Data = date("Y-m-d H:i:s");
                                $db->query("BEGIN TRANSACTION");
                                $sql  = "UPDATE SFPC.TBTRAMITACAODIASNAOTRABALHADOS ";
                                $sql .= "SET    ETDIANDESC = '$dianDescricao', ";
                                $sql .= "       ATDIANANOT = $dianAno," ;
                                $sql .= "       ATDIANDIAT = $dianDia, ";
                                $sql .= "       ATDIANMEST = $dianMes ";
  
                                $sql .= " WHERE CTDIANSEQU = $dianCodigo";
                               
                                $result = $db->query($sql);
                                
                                if (PEAR::isError($result)) {
                                    $db->query("ROLLBACK");
                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                } else {
                                    $db->query("COMMIT");
                                    $db->query("END TRANSACTION");
                                    $db->disconnect();
                                    
                                    # Envia mensagem para página selecionar #
                                    $Mensagem = urlencode("Dia não trabalhado alterado com sucesso!");
                                    $Url = "TabTramitacaoDiasNaoTrabalhadosSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
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
    }
if ($Critica == 0) {
    $db = Conexao();
    $sql = "SELECT CTDIANSEQU, ATDIANANOT, ATDIANMEST, ATDIANDIAT, ETDIANDESC FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE CTDIANSEQU = $dianCodigo";
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $dianCodigo     = $Linha[0];
            $dianAno        = $Linha[1];
            $dianMes        = $Linha[2];
            $dianDia        = $Linha[3];
            $dianDescricao  = $Linha[4];
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
	document.DiasNaoTrabalhados.Botao.value=valor;
	document.DiasNaoTrabalhados.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabTramitacaoDiasNaoTrabalhadosAlterar.php" method="post" id="DiasNaoTrabalhados" name="DiasNaoTrabalhados">
    <br> <br> <br> <br> <br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="150">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font> <a href="../index.php">
                    <font color="#000000">Página Principal</font></a> > Tabelas > Licitações > Tramitação > Dias não trabalhados > Manter
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	        <?php if ( $Mens == 1 ) {?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2">
                    <?php ExibeMens($Mensagem,$Tipo,1); ?>
                </td>
            </tr>
	        <?php } ?>
	        <!-- Fim do Erro -->
            <input type="hidden" name="DianCodigo" id="DianCodigo" value="<?php echo $dianCodigo ?>">
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal">
                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">MANTER - DIAS NÃO TRABALHADOS</td>
                        </tr>
                        <tr>
                            <td class="textonormal">
                                <p align="justify">Para atualizar o dia não trabalhado, preencha os dados abaixo e clique no botão "Alterar". Para apaga-lo, clique no botão "Excluir".</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Ano</td>
                                    <td class="textonormal">
                                    <select name="Ano" id="Ano" class="textonormal">
                                        <?php 
                                            $anoatual = date('Y');
                                            $anoSeguinte = $anoatual + 1;
                                            $txtAtual = '';
                                            $txtSeguinte = '';
                                            if($dianAno ==  $anoatual ){
                                                $txtAtual = ' selected';
                                            }
                                            if($dianAno ==  $anoSeguinte ){
                                                $txtSeguinte = ' selected';
                                            }
                                            echo "<option value='".$anoatual."' ".$txtAtual.">".$anoatual."</option>";
                                            echo "<option value='".$anoSeguinte."' ".$txtSeguinte.">".$anoSeguinte."</option>";
                                            ?>
                                        </select>
                                        <!--<input type="text" name="Ano" id="Ano" value="<?php echo $dianAno; ?>" size="3" maxlength="100" class="textonormal">-->
                                        
                                </tr>

                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Mês</td>
                                    <td class="textonormal">
                                    <select name="Mes" id="Mes" class="textonormal">
                                        <?php for($i=1; $i<13 ; $i++){
                                            $txtSelecionado = ' ';
                                            if($dianMes == $i ){
                                                $txtSelecionado = ' selected';
                                            }
                                            echo "<option value='".$i."' ".$txtSelecionado.">".$i."</option>";
                                        } ?>
                                        </select>
                                        <!--<input type="text" name="Mes" id="Mes" value="<?php echo $dianMes; ?>" size="3" maxlength="100" class="textonormal">-->
                                        
                                </tr>

                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Dia</td>
                                    <td class="textonormal">
                                        <select name="Dia" id="Dia" class="textonormal">
                                        <?php for($i=1; $i<32 ; $i++){
                                            $txtSelecionado = ' ';
                                            if($dianDia == $i ){
                                                $txtSelecionado = ' selected';
                                            }
                                            echo "<option value='".$i."' ".$txtSelecionado.">".$i."</option>";
                                        } ?>
                                        </select>
                                        <!--<input type="text" name="Dia" id="Dia" value="<?php echo $dianDia; ?>" size="3" maxlength="100" class="textonormal">-->
                                        <input type="hidden" name="Critica" value="1">
                                </tr> 





                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Descrição</td>
                                    <td class="textonormal">
                                    <font class="textonormal">máximo de 100 caracteres</font>
                                        <input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
                                        <textarea id="DianDescricao" name="DianDescricao" maxlength="100" cols="50" rows="4" onkeyup="javascript:CaracteresDianDescricao(1)" onblur="javascript:CaracteresDianDescricao(0)" onselect="javascript:CaracteresDianDescricao(1)" class="textonormal"><?php echo $dianDescricao; ?></textarea>
                                        <script language="javascript" type="">
                                        function CaracteresDianDescricao(valor){
                                            DiasNaoTrabalhados.NCaracteres.value = '' +  DiasNaoTrabalhados.DianDescricao.value.length;
                                        }
                                        </script>
                                        <!--<input type="text" name="DianDescricao" value="<?php echo $dianDescricao; ?>" size="45" maxlength="400" class="textonormal"> -->
                                    
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

document.DiasNaoTrabalhados.DianDescricao.focus();
CaracteresDianDescricao(0);
</script>
