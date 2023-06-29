<?php
/**
 * Portal de Compras
 * 
 * Programa: TabPeriodoDFDIncluir.php
 * Autor: Diógenes Dantas
 * Data: 14/12/2022
 * Objetivo: Programa para configuração e permissões e bloqueios no sistema.
 * Tarefa Redmine: 275881
 * -------------------------------------------------------------------
 * Alterado:    
 * Data:        
 * Tarefa:      
 * -------------------------------------------------------------------
 */

require_once "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/tabelasbasicas/TabPeriodoDFDSelecionar.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $critica = $_POST['Critica'];
    $selectAnoPCA = $_SESSION['dadosDaPagina']['selectAnoPCA'] = $_POST['selectAnoPCA'];
    $orgaoLicitanteCodigo = $_POST['areaReq'];
    $incluirDFD = $_POST['SelectIncluirDFD'];
    $alterarDFD = $_POST['SelectAlterarDFD'];
    $excluirDFD = $_POST['SelectExcluirDFD'];
    $configPeriodoDFD = $_POST['configPeriodoDFD'];
    $DataIni = $_POST['DataIni'];
    $DataFim = $_POST['DataFim'];

    if($DataIni!=NULL && $DataFim!=NULL){
        $DataIni = date('Y-m-d', strtotime(str_replace('/', '-', $DataIni)));
        $DataFim = date('Y-m-d', strtotime(str_replace('/', '-', $DataFim)));
    }
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Tratamento do ANO PCA
$anoAtual = date('Y');
$anoAnterior = date('Y', strtotime('-1 Year', strtotime($anoAtual)));
$anoPosterior = date('Y', strtotime('+1 Year', strtotime($anoAtual)));
$anoSelected0 = "";
$anoSelected1 = "";
$anoSelected2 = "";

if($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoAnterior) {
    $anoSelected0 = "selected" ;
}else if ($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoAtual) {
    $anoSelected1 = "selected" ;
}else if ($_SESSION['dadosDaPagina']['selectAnoPCA'] == $anoPosterior) {
    $anoSelected2 = "selected" ;
}

$optionsAno = '
    <option value="">Selecione o ano do PCA</option>
    <option value="'.$anoAnterior.'" '.$anoSelected0.'>'.$anoAnterior.'</option>
    <option value="'.$anoAtual.'" '.$anoSelected1.'>'.$anoAtual.'</option>
    <option value="'.$anoPosterior.'" '.$anoSelected2.'>'.$anoPosterior.'</option>
';

if ($critica == 1) {

    // Critica dos Campos #
    $mens = 0;
    $mensagem = "Atenção: ";
   if ($selectAnoPCA == "") {
        $mens = 1;
        $tipo = 2;
        $mensagem .= "<a href=\"javascript:document.Periodo.selectAnoPCA.focus();\" class=\"titulo2\">Ano PCA! </br></a>";
    }

   if ($orgaoLicitanteCodigo == "") {
        $mens = 1;
        $tipo = 2;
        $mensagem .= "<a href=\"javascript:document.Periodo.orgaoLicitanteCodigo.focus();\" class=\"titulo2\">Nome da Área Requisitante! </br></a>";
    }

   if (($incluirDFD!=NULL && $alterarDFD!=NULL && $excluirDFD!=NULL) || ($incluirDFD!=NULL && $alterarDFD!=NULL) || ($incluirDFD!=NULL && $excluirDFD!=NULL) || ($alterarDFD!=NULL && $excluirDFD!=NULL)) {
        $mens = 1;
        $tipo = 2;
        $mensagem .= "<a href=\"javascript:document.Periodo.incluirDFD.focus();\" class=\"titulo2\">Preencha apenas uma opção: Incluir ou Alterar ou Excluir! <br></a>";
    }

   if (($incluirDFD==NULL) && ($alterarDFD==NULL) && ($excluirDFD==NULL)){
        $mens = 1;
        $tipo = 2;
        $mensagem .= "<a href=\"javascript:document.Periodo.alterar.focus();\" class=\"titulo2\">Preencha uma opção: Incluir ou Alterar ou Excluir! </br></a>";
   }

   if ($DataIni == "") {
       $mens = 1;
       $tipo = 2;
       $mensagem .= "<a href=\"javascript:document.Periodo.DataIni.focus();\" class=\"titulo2\">Data de Inicio de Permissão/Bloqueio! </br></a>";
    }

   if ($DataFim == "") {
       $mens = 1;
       $tipo = 2;
       $mensagem .= "<a href=\"javascript:document.Periodo.DataFim.focus();\" class=\"titulo2\">Data de Fim de Permissão/Bloqueio!</a>";
    }

    if ($mens == 0) {

        $db = Conexao();
        $codigoUsuario = $_SESSION['_cusupocodi_'];

        
        if ($incluirDFD != NULL) {
            // Recupera a última configuração e incrementa mais um #
            $sqlMax = "SELECT MAX(cplconcodi) 
                FROM sfpc.tbplanejamentoconfiguracao";

            $result = $db->query($sqlMax);
            $linha = $result->fetchRow();
            $codigo = $linha[0] + 1;

            // Insere configuração
            $db->query("BEGIN TRANSACTION");

            $sql = "INSERT INTO sfpc.tbplanejamentoconfiguracao (
                    cplconcodi, aplconanop, corglicodi, cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi, cusupocodi, tplconulat)
                    VALUES ($codigo, $selectAnoPCA, $orgaoLicitanteCodigo, 1, $incluirDFD, '$DataIni', '$DataFim', $codigoUsuario, now())";

            $result = $db->query($sql);

            if (db::isError($result)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } 
        }

        if ($alterarDFD != NULL) {
            
            $sqlMax = "SELECT MAX(cplconcodi) 
            FROM sfpc.tbplanejamentoconfiguracao";

            $result = $db->query($sqlMax);
            $linha = $result->fetchRow();
            $codigo = $linha[0] + 1;

            // alterar configuração
            $db->query("BEGIN TRANSACTION");

            $sql = "INSERT INTO sfpc.tbplanejamentoconfiguracao (
                    cplconcodi, aplconanop, corglicodi, cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi, cusupocodi, tplconulat)
                    VALUES ($codigo, $selectAnoPCA, $orgaoLicitanteCodigo, 2, $alterarDFD, '$DataIni', '$DataFim', $codigoUsuario, now())";
            

            $result = $db->query($sql);

            if (db::isError($result)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            }
            
        }

        if ($excluirDFD != NULL) {
            
            $sqlMax = "SELECT MAX(cplconcodi) 
            FROM sfpc.tbplanejamentoconfiguracao";

            $result = $db->query($sqlMax);
            $linha = $result->fetchRow();
            $codigo = $linha[0] + 1;

            // excluir configuração
            $db->query("BEGIN TRANSACTION");

            $sql = "INSERT INTO sfpc.tbplanejamentoconfiguracao (
                    cplconcodi, aplconanop, corglicodi, cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi, cusupocodi, tplconulat)
                    VALUES ($codigo, $selectAnoPCA, $orgaoLicitanteCodigo, 3, $excluirDFD, '$DataIni', '$DataFim', $codigoUsuario, now())";
            

            $result = $db->query($sql);

            if (db::isError($result)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            }
            
        }

        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        $mens = 1;
        $tipo = 1;
        $mensagem = "Configuração do DFD incluída com sucesso!";

        // Limpando Variáveis #
        $selectAnoPCA = "";
        $orgaoLicitanteCodigo = "";
        $incluirDFD = "";
        $alterarDFD = "";
        $excluirDFD = "";
        $configPeriodoDFD = "";
        $DataIni = "";
        $DataFim = "";
        
    }
}
?>

<html>
    <?php
    // Carrega o layout padrão
    layout();
    ?>

    <script language="javascript" type="">

    <?php MenuAcesso(); ?>

    // Função para o botão limpar
    $("#limparConsulta").on('click', function(){
        $('#formConfigPeriodoDFD').trigger("reset")
    });

    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">

    <style>
        #labels{
            width: 250px;
            background-color:#DCEDF7;
        }
    </style>

    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="TabPeriodoDFDIncluir.php" method="post" id="formConfigPeriodoDFD" name="configPeriodoDFD">
            <br> <br> <br> <br> <br>
            <table cellpadding="3" border="0">

                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal"><font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font>
                        </a> > Tabelas > Planejamento > Situação DFD > Configurações
                    </td>
                </tr>
                <!-- Fim do Caminho-->

                <!-- Erro -->
                <?php if ( $mens == 1 ) { ?>
                <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2"><?php ExibeMens($mensagem,$tipo,1); ?></td>
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
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                                CONFIGURAÇÕES DO DFD
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal">
                                                <ul align="justify">
                                                    <li>Para incluir uma nova configuração do DFD, insira as informações nos campos abaixo e clique em "Salvar".</li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table class="textonormal" border="0" align="left" class="caixa">
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Ano PCA
                                                        </td>
                                                        <td>
                                                            <select name="selectAnoPCA" id="selectAnoPCA" style="width:210px;">
                                                            
                                                                <?php echo $optionsAno; ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Nome da Área Requisitante
                                                        </td>
                                                        <td>
                                                            <select name="areaReq" id="areaReq" style="width: auto;">
                                                                <option value="">Selecione a área requisitante</option>
                                                                <?php
                                                                //Tratamento do Órgão Licitante(Área Requisitante)
                                                                $db = Conexao();
                                                                $sql = "
                                                                SELECT corglicodi, eorglidesc
                                                                FROM sfpc.tborgaolicitante
                                                                WHERE forglisitu = 'A'
                                                                ORDER BY eorglidesc
                                                                ";

                                                                $result = $db->query($sql);
                                                                if (db::isError($result)) {
                                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                                } else {
                                                                    while ($Linha = $result->fetchRow()) {
                                                                        echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                                    }
                                                                }
                                                                $db->disconnect();
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Incluir DFD
                                                        </td>
                                                        <td>
                                                            <select name="SelectIncluirDFD" id="SelectIncluirDFD" style="width:210px;">
                                                                <option value="">Selecione permitir ou bloquear</option>
                                                                <option value="1">Permitir</option>
                                                                <option value="2">Bloquear</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Alterar DFD
                                                        </td>
                                                        <td>
                                                            <select name="SelectAlterarDFD" id="SelectAlterarDFD" style="width:210px;">
                                                                <option value="">Selecione permitir ou bloquear</option>
                                                                <option value="1">Permitir</option>
                                                                <option value="2">Bloquear</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" id="labels">
                                                            Excluir DFD
                                                        </td>
                                                        <td>
                                                            <select name="SelectExcluirDFD" id="SelectExcluirDFD" style="width:210px;">
                                                                <option value="">Selecione permitir ou bloquear</option>
                                                                <option value="1">Permitir</option>
                                                                <option value="2">Bloquear</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período de Permissão/Bloqueio</td>
                                                        <td class="textonormal">
                                                            <?php
                                                            $URLIni = "../calendario.php?Formulario=configPeriodoDFD&Campo=DataIni";
                                                            $URLFim = "../calendario.php?Formulario=configPeriodoDFD&Campo=DataFim";
                                                            ?>

                                                            <input class="data" id="DataIni" type="text" name="DataIni" size="10" maxlength="10" value="<?php echo !empty($_POST['DataIni'])?$_POST['DataIni']:'';?>">
                                                            <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                            &nbsp;a&nbsp;
                                                            <input class="data" id="DataFim" type="text" name="DataFim" size="10" maxlength="10" value="<?php echo !empty($_POST['DataFim'])?$_POST['DataFim']:'';?>">
                                                            <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" align="right">
                                                <input type="submit" name="Salvar" value="Salvar" class="botao">
                                                <input type="submit" name="limparConsulta" id="limparConsulta" value="Limpar" class="botao">
                                                <input type="hidden" name="Critica" value="1">
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
document.Periodo.configPeriodoDFD.focus();
//-->
</script>
