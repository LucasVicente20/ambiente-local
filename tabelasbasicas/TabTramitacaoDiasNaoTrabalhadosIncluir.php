<?php
# ------------------------------------------------------------------
# Portal de Compras
# Programa: TabTramitacaoDiasNaoTrabalhadosIncluir.php
# Autor:    Ernesto Ferreira
# Data:     20/07/2018
# Objetivo: Tarefa Redmine 199105
# ------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica        = $_POST['Critica'];
    $DianDescricao  = strtoupper2(trim($_POST['DianDescricao']));
    $dianDia          = trim($_POST['Dia']);
    $dianMes          = trim($_POST['Mes']);
    $dianAno          = trim($_POST['Ano']);
}

if ($Critica == 1) {
    # Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    if ($DianDescricao == "") {
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
        # Verifica a duplicidade de Dias não trabalhados #
        $db = Conexao();
        $sql = "SELECT COUNT(CTDIANSEQU) FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE ATDIANANOT = $dianAno AND ATDIANMEST = $dianMes AND ATDIANDIAT= $dianDia";
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $result->fetchRow();
            $Qtd = $Linha[0];
            
            if ($Qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.DiasNaoTrabalhados.DianDescricao.focus();\" class=\"titulo2\">Dia não trabalhado já cadastrado!</a>";
            } else {
                # Recupera a última ação e incrementa mais um #
                $sql = "SELECT MAX(CTDIANSEQU) FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS";
                $result = $db->query($sql);
                if (PEAR::isError($result)) {
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $Linha = $result->fetchRow();
                    $codigo = $Linha[0] + 1;
                    
                    # Insere ação #
                    $codUsuario = $_SESSION['_cusupocodi_'];
                    $data = date("Y-m-d H:i:s");
                    $db->query("BEGIN TRANSACTION");
                    $sql = "INSERT INTO SFPC.TBTRAMITACAODIASNAOTRABALHADOS ( ";
                    $sql .= "CTDIANSEQU, ETDIANDESC, ATDIANANOT, ATDIANMEST, ATDIANDIAT, CUSUPOCODI, TTDIANULAT ";
                    $sql .= ") VALUES ( ";
                    $sql .= "$codigo, '$DianDescricao', $dianAno, $dianMes, $dianDia, $codUsuario, '$data')";
                    $result = $db->query($sql);
                    if (PEAR::isError($result)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                        
                        $Mens       = 1;
                        $Tipo       = 1;
                        $Mensagem   = "Dia não trabalhado incluído com sucesso!";
                        
                        # Limpando Variáveis #
                        $DianDescricao  = "";
                        $dianAno          = "";
                        $dianMes          = "";
                        $dianDia       = "";
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
# Carrega o layout padrão @
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
    <form action="TabTramitacaoDiasNaoTrabalhadosIncluir.php" method="post" name="DiasNaoTrabalhados">
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
                    > Tabelas > Licitações > Tramitação > Dias não Trabalhados > Incluir
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
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - DIA NÃO TRABALHADO</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir um novo Dia não trabalhado, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.</p>
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
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
                                                        <input type="hidden" name="Critica" value="1">
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
                                                        <input type="hidden" name="Critica" value="1">
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
                                                        <textarea id="DianDescricao" name="DianDescricao" maxlength="100" cols="50" rows="4" onkeyup="javascript:CaracteresDianDescricao(1)" onblur="javascript:CaracteresDianDescricao(0)" onselect="javascript:CaracteresDianDescricao(1)" class="textonormal"><?php echo $DianDescricao; ?></textarea>
                                                        <script language="javascript" type="">
                                                        function CaracteresDianDescricao(valor){
                                                            DiasNaoTrabalhados.NCaracteres.value = '' +  DiasNaoTrabalhados.DianDescricao.value.length;
                                                        }
                                                        </script>
                                                        <!--<input type="text" name="DianDescricao" value="<?php echo $DianDescricao; ?>" size="45" maxlength="400" class="textonormal"> -->
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
<script
    language="javascript"
    type=""
>
<!--
document.DiasNaoTrabalhados.DianDescricao.focus();
//-->
</script>
