<?php
/**
 * Portal de Compras
 * 
 * Programa: CadTramitacaoEntrada.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * Data:     24/07/2018
 * Objetivo: Tarefa Redmine 199436
 * ------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     28/03/2019
 * Objetivo: Tarefa Redmine 213538
 * ------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/05/2019
 * Objetivo: Tarefa Redmine 216897
 * ------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/05/2019
 * Objetivo: Tarefa Redmine 218517
 * ------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     10/10/2022
 * Objetivo: Tarefa Redmine 206442
 * ------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
# Acesso ao arquivo de funções #
require_once 'funcoesTramitacao.php';
//require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica        = $_POST['Critica'];
    $botao          = $_POST['botao'];
    $numProtocolo   = $_POST['numProtocolo'];
    $anoProtocolo   = $_POST['anoProtocolo'];
    $orgao          = $_POST['orgao'];
    $objeto         = strtoupper2($_POST['objeto']);
    $numeroci       = strtoupper2($_POST['numeroci']);
    $numeroOficio   = strtoupper2($_POST['numeroOficio']);
    $numeroScc      = $_POST['numeroScc'];
    $proLicitatorio = $_POST['proLicitatorio'];
    $acao           = $_POST['acao'];
    $origem         = $_POST['origem'];
    $DataEntradaIni    = $_POST['DataEntradaIni'];
    $DataEntradaFim    = $_POST['DataEntradaFim'];
    $idProtocolo      = $_POST['idProtocolo'];
}else{
    $Critica        = $_GET['Critica'];
    $botao          = $_GET['botao'];
    $numProtocolo   = $_GET['numProtocolo'];
    $anoProtocolo   = $_GET['anoProtocolo'];
    $orgao          = $_GET['orgao'];
    $objeto         = $_GET['objeto'];
    $numeroci       = $_GET['numeroci'];
    $numeroOficio   = $_GET['numeroOficio'];
    $numeroScc      = $_GET['numeroScc'];
    $proLicitatorio = $_GET['proLicitatorio'];
    $acao           = $_GET['acao'];
    $origem         = $_GET['origem'];
    $DataEntradaIni = $_GET['DataEntradaIni'];
    $DataEntradaFim = $_GET['DataEntradaFim'];
    $inseriu        = $_GET['inseriu'];
}   


if($botao == 'Limpar'){
    $Critica = '';
    $botao  = '';

    $numProtocolo   = '';
    $anoProtocolo   = '';
    $objeto         = '';
    $numeroci       = '';
    $numeroOficio   = '';
    $numeroScc      = '';
    $orgao           = 0;
    $acao            = 0;
    $origem          = 0;
    $proLicitatorio  = 0;
}


//Retorna os Dados dos 
$htmlOrgao = '';
$sql = "SELECT OL.CORGLICODI, OL.EORGLIDESC 
        FROM SFPC.TBORGAOLICITANTE OL 
        LEFT JOIN SFPC.TBGRUPOORGAO GOR ON OL.CORGLICODI = GOR.CORGLICODI 
        WHERE FORGLISITU = 'A' 
        AND GOR.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
        ORDER BY OL.EORGLIDESC ASC";
$db = Conexao();
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {

    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] == $orgao) {
            $htmlOrgao.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
            $orgaoDesc = $Linha[1];
        } else {
            $htmlOrgao.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
        }
    }

}

//Retorna os Dados das Ações
$htmlAcao = '';
$sql = "SELECT CTACAOSEQU, ETACAODESC FROM SFPC.TBTRAMITACAOACAO WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ORDER BY atacaoorde ASC ";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {

    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] == $acao) {
            $htmlAcao.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
            $acaoDesc = $Linha[1];
        } else {
            $htmlAcao.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
        }
    }

}

//Retorna os Dados dos Agentes Origem
$htmlAgenteOrigem = '';

$sql = "SELECT CTAGENSEQU, ETAGENDESC FROM SFPC.TBTRAMITACAOAGENTE WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ORDER BY ETAGENDESC ASC";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    while ($Linha = $result->fetchRow()) {
        if ($Linha[0] == $origem) {
            $htmlAgenteOrigem.= "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
            $origemDesc = $Linha[1];
        } else {
            $htmlAgenteOrigem.= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
        }
    }
}



if ($Critica == 1) {
    # Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    //Verifica se o ano do protocolo e o ano estão preenchidos
    if ($numProtocolo != "" ) {

        if ($anoProtocolo != "" ) {

            if (!SoNumeros($numProtocolo)) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Entrada.numProtocolo.focus();\" class=\"titulo2\">Número do Protocolo inválido</a>";
            }
        }else{

            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.numProtocolo.focus();\" class=\"titulo2\">Ano do Protocolo</a>";
        }
    }

    if ($anoProtocolo != "" ) {

        if ($numProtocolo != "" ) {

            if (!SoNumeros($anoProtocolo)) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Entrada.numProtocolo.focus();\" class=\"titulo2\">Ano do Protocolo inválido</a>";
            }
        }else{

            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.numProtocolo.focus();\" class=\"titulo2\">Número do Protocolo</a>";
        }
    }

        
    // verifica se a data de entrada é válida
    if ($DataEntradaIni != "" && $DataEntradaFim != "") {

        $DataEntradaIniCheck = explode("/",$DataEntradaIni);

        if(!checkdate($DataEntradaIniCheck[1],$DataEntradaIniCheck[0],$DataEntradaIniCheck[2])){
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Data da Entrada Inicial inválida</a>";
        }

        $DataEntradaFimCheck = explode("/",$DataEntradaFim);

        if(!checkdate($DataEntradaFimCheck[1],$DataEntradaFimCheck[0],$DataEntradaFimCheck[2])){
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaFim.focus();\" class=\"titulo2\">Data da Entrada Final inválida</a>";
        }


        $dateTimeIni = strtotime($DataEntradaIniCheck[2].'-'.$DataEntradaIniCheck[1].'-'.$DataEntradaIniCheck[0]);
        $dateTimeFim= strtotime($DataEntradaFimCheck[2].'-'.$DataEntradaFimCheck[1].'-'.$DataEntradaFimCheck[0]);
         
        if ($dateTimeIni > $dateTimeFim){
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }

            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Data da Entrada Inicial deve ser menor que a final</a>";
        }


    }

    if($Mens == 0){
        if($botao == 'Imprimir'){

            $Url = "RelTramitacaoEntradaPdf.php?";
            $Url .= "numProtocolo=".$numProtocolo;
            $Url .= "&anoProtocolo=".$anoProtocolo;
            $Url .= "&orgao=".$orgao;
            $Url .= "&objeto=".$objeto;
            $Url .= "&numeroci=".$numeroci;
            $Url .= "&numeroOficio=".$numeroOficio;
            $Url .= "&numeroScc=".$numeroScc;
            $Url .= "&proLicitatorio=".$proLicitatorio;
            $Url .= "&acao=".$acao;
            $Url .= "&origem=".$origem;
            $Url .= "&DataEntradaIni=".$DataEntradaIni;
            $Url .= "&DataEntradaFim=".$DataEntradaFim;
            $Url .= "&t=".mktime();
            if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
            header("location: ".$Url);
            exit();
        }

        if($botao == 'Selecionar'){

            if($idProtocolo != ""){

                unset($_SESSION['Arquivos_Upload']);

                $Url = "CadTramitacaoEntradaEnvio.php?";
                $Url .= "numTramitacao=".$idProtocolo;
                $Url .= "&numProtocolo=".$numProtocolo;
                $Url .= "&anoProtocolo=".$anoProtocolo;
                $Url .= "&orgao=".$orgao;
                $Url .= "&objeto=".$objeto;
                $Url .= "&numeroci=".$numeroci;
                $Url .= "&numeroOficio=".$numeroOficio;
                $Url .= "&numeroScc=".$numeroScc;
                $Url .= "&proLicitatorio=".$proLicitatorio;
                $Url .= "&acao=".$acao;
                $Url .= "&origem=".$origem;
                $Url .= "&DataEntradaIni=".$DataEntradaIni;
                $Url .= "&DataEntradaFim=".$DataEntradaFim;
                $Url .= "&t=".mktime();
                if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                header("location: ".$Url);
                exit();

            }else{

                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
    
                $Mens = 1;
                $Tipo = 2;
                $Mensagem .= "<a href=\"#\" class=\"titulo2\">Selecione um Protocolo </a>";
            }


        }

    }

}

if($_GET['retornoVazioPdf'] == 1){
    # Critica dos Campos #
    $Mens = 1;
    $Tipo = 1;
    $Mensagem = "A pesquisa não retornou nenhum dado 
                impossibilitando a geração do PDF.<br>
                Tente novamente";
}


if($inseriu==1){
    $Mens = 0;
    $Tipo = 1;
    $Mensagem .= "<a href=\"javascript:document.Entrada.DataEntradaIni.focus();\" class=\"titulo2\">Tramitação efetuada com sucesso</a>";
}

?>
<html>
<style>

body{
    font-size: 8pt;
}

.titulo_resultado{
    background-color: #DCEDF7
}

.tamanho_campo{
    width:100%;
}
</style>    
<?php
# Carrega o layout padrão @
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css?t=<?php echo date('dmYhis');?>">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadTramitacaoEntrada.php" method="post" name="Entrada">
        <input type="hidden" name="botao" value="">
        <br> <br> <br> <br> <br><br><br><br><br>
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
                    > Licitações > Tramitação > Entrada
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	        <?php if ( $Mens == 1 || $inseriu==1 ) {?>
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
                    <table border="0" cellspacing="0" cellpadding="3" >
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width: 100%;">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">TRAMITAR – PROCESSOS LICITATÓRIOS</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Preencha os dados abaixo para efetuar a pesquisa e depois clique no botão 'Pesquisar'. Para proceder a tramitação do Protocolo, selecione o protocolo desejado e clique no botão 'Selecionar'. </p>
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número/Ano do Protocolo do Processo Licitatório</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="numProtocolo" value="<?php echo $numProtocolo; ?>" size="3" maxlength="4" class="textonormal">&nbsp;/&nbsp;                                                   
                                                        <input type="text" name="anoProtocolo" value="<?php echo $anoProtocolo; ?>" size="3" maxlength="4" class="textonormal">
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão demandante</td>
                                                    <td class="textonormal">
                                                        <select name="orgao" id="orgao"  class="tamanho_campo textonormal">
                                                            <option value="0">Selecione um órgão...</option>
                                                            <?php echo $htmlOrgao ?>
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="objeto" value="<?php echo $objeto; ?>" size="45" maxlength="100" class="textonormal tamanho_campo"> 
                                                        
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número CI</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="numeroci" value="<?php echo $numeroci; ?>" maxlength="30" class="textonormal">
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número Ofício</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="numeroOficio" value="<?php echo $numeroOficio; ?>"  maxlength="30" class="textonormal">
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Número da SCC</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="numeroScc" class="solicitacao" value="<?php echo $numeroScc; ?>" size="12" maxlength="9" class="textonormal">
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório</td>
                                                    <td class="textonormal">
                                                    <select name="proLicitatorio" id="proLicitatorio" value="" class="textonormal">
                                                        <option value="0">Selecione um Processo Licitatório...</option>
                                                        <?php
                                                            $db     = Conexao();
                                                            $sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, A.CGREMPCODI , A.CORGLICODI , B.ECOMLIDESC ";
                                                            $sql   .= "FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBUSUARIOCOMIS D ";
                                                            $sql   .= "WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
                                                            $sql   .= "AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
                                                            $sql   .= "AND D.CCOMLICODI = A.CCOMLICODI ";
                                                            $sql   .= "AND A.CGREMPCODI = D.CGREMPCODI ";
                                                            $sql   .= "AND A.CCOMLICODI = B.CCOMLICODI ";
                                                            $sql   .= "AND MAKE_DATE(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '2 YEARS' "; //CR 206442 MAKE_DATE
                                                            $sql   .= "ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
                                                            //print_r($sql);exit;
                                                            $result = $db->query($sql);
                                                            if( PEAR::isError($result) ){
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            }else{
                                                                $string = "";
                                                                    while( $Linha = $result->fetchRow() ){
                                                                        
                                                                        //Sql que verifica se a licitacao é do tipo novo ou antigo
                                                                        $sql = "SELECT COUNT(*) AS QUANTIDADE FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CLICPOPROC = $Linha[0] AND  ALICPOANOP = $Linha[1] AND  CGREMPCODI  = $Linha[3] AND CCOMLICODI = $Linha[2] AND CORGLICODI = $Linha[4] ";
                                                                        $qtdSolicitacoes = resultValorUnico(executarSQL($db, $sql));
                                                                        if($qtdSolicitacoes>0){
                                                                            $novaTela = 1;
                                                                        }else{
                                                                            $novaTela = 0;
                                                                        }

                                                                        if( $Linha[2] != $ComissaoCodigoAnt ){
                                                                                $ComissaoCodigoAnt = $Linha[2];
                                                                                $htmlText .= "<option value=\"\">$Linha[5]</option>\n" ;
                                                                        }
                                                                        $strComp = "$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]";
                                                                        $NProcesso = substr($Linha[0] + 10000,1);
                                                                        

                                                                        if($strComp == $proLicitatorio){
                                                                            $htmlText .= "<option value ='$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]' selected>&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
                                                                        }else{
                                                                            $htmlText .= "<option value ='$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]'>&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
                                                                        }
                                                                        //echo "<td>".$string." - ".$proLicitatorio."</td>";
                                                                    }
                                                                    echo $htmlText;
                                                            }
                                                            

                                                            $db->disconnect();
                                                            ?>
                                                    </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                                    <td class="textonormal">
                                                        <select name="acao" class="tamanho_campo textonormal" >
                                                            <option value="0">Selecione uma ação...</option>
                                                            <?php echo $htmlAcao ?>
                                                            
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7"> Agente Origem</td>
                                                    <td class="textonormal">
                                                        <select name="origem" class="tamanho_campo textonormal">
                                                            <option value="0">Selecione um agente origem...</option>
                                                            <?php echo $htmlAgenteOrigem ?>
                                                        </select>
                                                    </td>
                                                    
                                                </tr>

                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" width="30%">Período de Entrada do Protocolo</td>
                                                    <td name="DataEntrada"  class="textonormal">
                                                        <input type="text" value="<?php if($DataEntradaIni!=""){echo $DataEntradaIni;}else{echo '';} ?>" name="DataEntradaIni" id="DataEntradaIni" size="10" maxlength="10" value="" class="textonormal">
                                                        <a href="javascript:janela('../calendario.php?Formulario=Entrada&Campo=DataEntradaIni','Calendario',220,170,1,0)">
                                                            <img src="../midia/calendario.gif" border="0" alt=""></a>
                                                        &nbsp;&nbsp;&nbsp;a&nbsp;&nbsp;&nbsp;
                                                        <input type="text" value="<?php if($DataEntradaFim!=""){echo $DataEntradaFim;}else{echo '';}?>" name="DataEntradaFim" id="DataEntradaFim" size="10" maxlength="10" value="" class="textonormal">
                                                        <a href="javascript:janela('../calendario.php?Formulario=Entrada&Campo=DataEntradaFim','Calendario',220,170,1,0)">
                                                            <img src="../midia/calendario.gif" border="0" alt=""></a>
                                                    </td>

                                                </tr>


                                                <input type="hidden" name="Critica" value="1"> 

                                            </table>
                                        </td>
                                    </tr>
                                    <tr colspan='13'>
                                        <td class="textonormal" align="right">
                                            <input type="submit" name="Pesquisar" value="Pesquisar" class="botao">
                                            <input type="submit" name="Limpar" value="Limpar" class="botao" onclick="document.Entrada.botao.value = 'Limpar'">
                                        </td>
                                            
                                    </tr>
  
                                </table>
                                
                            </td>
                        </tr>
                        <tr>
                            <td>
                        <?php 
if ($Critica == 1) {
   
    if ($Mens == 0) {

        //Coloca os dados da pesquisa na session para retorno das páginas envolvidas
        $_REQUEST['rotina'] = 'Entrada';
        $_SESSION['origemPesquisa'] = $_REQUEST;

        $db = Conexao();
        $sql = "SELECT cprotcsequ, cgrempcod1, cprotcnump, aprotcanop, corglicod1, 
        xprotcobje, eprotcnuci, eprotcnuof, csolcosequ, prot.clicpoproc, 
        prot.alicpoanop, prot.cgrempcodi, prot.ccomlicodi, prot.corglicodi, TPROTCENTR, 
        vprotcvale, xprotcobse, prot.cusupocodi, cusupocod1, tprotculat, 
        org.eorglidesc, 
            (   select f.efasesdesc from sfpc.tbfaselicitacao fase 
                join sfpc.tbfases f on f.cfasescodi = fase.cfasescodi 
                where fase.clicpoproc = prot.clicpoproc and fase.alicpoanop = prot.alicpoanop 
                and fase.ccomlicodi= prot.ccomlicodi and fase.corglicodi = prot.corglicodi 
                and fase.cgrempcodi = prot.cgrempcodi
                order by fase.tfaselulat DESC
                limit 1
            ) as fase_licitacao,
            (select acao.etacaodesc from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc, acao.ttacaoulat desc
                limit 1)as acaoDesc,

            (select agente.etagendesc from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbtramitacaoagente agente on agente.ctagensequ = tram.ctagensequ
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc 
                limit 1)as agenteOrigemDesc,

            (select tram.ctagensequ from sfpc.tbtramitacaolicitacao tram
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc
                limit 1)as codAgenteOrigem,

            (select usu.eusuporesp from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc
                limit 1)as tramUsuDesc,

            (select tram.ttramlentr from sfpc.tbtramitacaolicitacao tram
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc
                limit 1)as datahoraEntradaAcao,

            (select tram.ttramlsaid from sfpc.tbtramitacaolicitacao tram
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlsaid desc
                limit 1)as datahoraSaidaAcao,
				
				com.ecomlidesc,

            (select acao.ftacaotusu from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbtramitacaoacao acao on acao.ctacaosequ = tram.ctacaosequ
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc, acao.ttacaoulat desc
                limit 1)as acaoParaTodosUsu,

            (select usu.cusupocodi from sfpc.tbtramitacaolicitacao tram
                join sfpc.tbusuarioportal usu on usu.cusupocodi = tram.cusupocodi
                where prot.cprotcsequ = tram.cprotcsequ
                order by tram.ttramlentr desc
                limit 1)as tramUsuCod,

            (select agente.FTAGENTIPO from sfpc.tbtramitacaolicitacao tram 
                join sfpc.tbtramitacaoagente agente on agente.ctagensequ = tram.ctagensequ 
                where prot.cprotcsequ = tram.cprotcsequ 
                order by tram.ttramlentr desc 
                limit 1)as agentetipo

				
        FROM sfpc.tbtramitacaoprotocolo prot
        LEFT JOIN sfpc.tborgaolicitante org on org.corglicodi = prot.corglicod1
        LEFT JOIN sfpc.tbcomissaolicitacao com on com.ccomlicodi = prot.ccomlicodi 
        WHERE 1 = 1 ";

        $comissoesUsuario = getComissoesUsuario($db, $_SESSION['_cusupocodi_']);
        $listaComissoesUsuario = listarResultado($comissoesUsuario);
        


        if (count($comissoesUsuario)){
            //Caso tenha comissão associada ao usuario 
            //Lista todos os protocolos que tenham a comissão(ões) encontrada
            $sql .= " AND prot.cprotcsequ in (select tram.cprotcsequ from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoacao ac on tram.ctacaosequ = ac.ctacaosequ 
            join sfpc.tbtramitacaoagente agen on tram.ctagensequ = agen.ctagensequ
            where tram.ttramlsaid is NULL 
                AND (tram.ccomlicodi in (".$listaComissoesUsuario.")) OR
                tram.ctagensequ in (select agusu.ctagensequ
                from sfpc.tbtramitacaoagenteusuario agusu
                where agusu.cusupocodi = ".$_SESSION['_cusupocodi_'].")

            AND ((ac.ftacaotusu = 'S' ) OR tram.cusupocodi = ".$_SESSION['_cusupocodi_'].") AND tram.ttramlsaid is NULL
            and (agen.ftagencomis <> 'S' OR agen.ftagencomis is null)
            )";


        }else{
            //Caso tenha agente associado ao usuário
            $sql .= " AND prot.cprotcsequ in (select tram.cprotcsequ from sfpc.tbtramitacaolicitacao tram
            join sfpc.tbtramitacaoacao ac on tram.ctacaosequ = ac.ctacaosequ 
            join sfpc.tbtramitacaoagente agen on tram.ctagensequ = agen.ctagensequ
            where tram.ctagensequ in (select agusu.ctagensequ
                                      from sfpc.tbtramitacaoagenteusuario agusu
                                      where agusu.cusupocodi = ".$_SESSION['_cusupocodi_'].")
                                      AND ((ac.ftacaotusu = 'S') OR tram.cusupocodi = ".$_SESSION['_cusupocodi_'].") AND tram.ttramlsaid is NULL
                                      and (agen.ftagencomis <> 'S' or agen.ftagencomis is null)
            ) ";


        }


        $sql .= " 
                AND (select tram.ttramlsaid from sfpc.tbtramitacaolicitacao tram 
                where prot.cprotcsequ = tram.cprotcsequ 
                order by tram.ttramlsaid desc 
                limit 1) IS NULL AND 1=1 ";

        // Numero/ano de protocolo licitatório
        // Processo Licitatorio
        if($proLicitatorio != 0 ){ // prot.clicpoproc, prot.alicpoanop, prot.cgrempcodi
            $arrProLicitatorio = explode("_",$proLicitatorio);
            $sql .= " AND prot.clicpoproc = $arrProLicitatorio[0] ";
            $sql .= " AND prot.alicpoanop = $arrProLicitatorio[1] ";
            $sql .= " AND prot.ccomlicodi = $arrProLicitatorio[2] ";
            $sql .= " AND prot.cgrempcodi = $arrProLicitatorio[3] ";
            $sql .= " AND prot.corglicodi = $arrProLicitatorio[4] ";

        }  

        if($numProtocolo != ""){
            $sql .= " AND cprotcnump = ".$numProtocolo." AND aprotcanop =".$anoProtocolo." ";
        }

        // Órgão
        if($orgao != 0){
            $sql .= " AND prot.corglicod1 = ".$orgao." ";
        }

        // Objeto
        if($objeto != ""){
            $sql .= " AND xprotcobje like '%".strtoUpper2($objeto)."%' ";
        }

        // Numero CI
        if($numeroci != ""){ 
            $sql .= " AND eprotcnuci like '%".strtoUpper2($numeroci)."%' ";
        }

        // Numero Oficio
        if($numeroOficio != ""){
            $sql .= " AND eprotcnuof like '%".strtoUpper2($numeroOficio)."%' ";
        }

        // Número da SCC
        if($numeroscc != ""){ 
            $sql .= " AND csolcosequ = ".$numeroscc." ";
        }
  

        // Ação
        if($acao != 0){
            $sql .= " AND (select tram.ctacaosequ 
                           from sfpc.tbtramitacaolicitacao tram
                           where prot.cprotcsequ = tram.cprotcsequ
                           limit 1) = ".$acao." ";
        }      

        // Agente de Origem
        if($origem != 0){
            $sql .= " AND  (select tram.ctagensequ from sfpc.tbtramitacaolicitacao tram
                            where prot.cprotcsequ = tram.cprotcsequ
                            limit 1) = ".$origem." ";

        }    

        // Data Entrada
        if($DataEntradaIni != "" && $DataEntradaFim != ""){
            $DataEntradaIniFormatada = explode("/",$DataEntradaIni);
            $DataEntradaIniFormatada = $DataEntradaIniFormatada[2]."-".$DataEntradaIniFormatada[1] ."-".$DataEntradaIniFormatada[0];
            
            $DataEntradaFimFormatada = explode("/",$DataEntradaFim);
            $DataEntradaFimFormatada = $DataEntradaFimFormatada[2]."-".$DataEntradaFimFormatada[1] ."-".$DataEntradaFimFormatada[0];
            $sql .= " AND TPROTCENTR between '".$DataEntradaIniFormatada."' AND '".$DataEntradaFimFormatada."' ";
        }    

        $sql .= 'ORDER BY aprotcanop desc, cprotcnump desc ';
        //print($sql);
        //die();
        $resultadoPesquisa = $db->query($sql);
        
        if (PEAR::isError($resultadoPesquisa)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
?>    
            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" style="width:100%">
                <?php if($resultadoPesquisa->numRows() > 0){ ?>
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan='13'>RESULTADO DA PESQUISA</td>
                    </tr>
                    <tr class="linhainfo">
                        <th id="thord" rowspan="2"  class="titulo_resultado" >NÚMERO/ANO <br>DO PROTOCOLO PROCESSO<br> LICITATÓRIO</th>
                        <th id="thorgao" rowspan="2" class="titulo_resultado">ÓRGÃO DEMANDANTE</th>
                        <th id="thobj" rowspan="2" class="titulo_resultado">OBJETO</th>
                        <th id="thnumci" rowspan="2" class="titulo_resultado">NÚMERO CI</th>
                        <th id="thnumoficio" rowspan="2" class="titulo_resultado">NÚMERO OFÍCIO</th>
                        <th id="thnumscc" rowspan="2" class="titulo_resultado">NÚMERO DA SCC</th>
                        <th id="thproclicitatorio" rowspan="2" class="titulo_resultado" >PROCESSO LICITATÓRIO</th>
                        <th id="thfase" rowspan="2" class="titulo_resultado">FASE ATUAL</th>
                        <th id="thdataentrada" rowspan="2" class="titulo_resultado">DATA/HORA ENTRADA PROTOCOLO</th>
                        <th id="thultimopasso" class="titulo_resultado" colspan="4">ÚLTIMO PASSO</th>
                    </tr>
                    <tr class="linhainfo">
                        <th id="thultAcao" class="titulo_resultado" >AÇÃO</th>
                        <th id="thultAgenteOrig" class="titulo_resultado" >AGENTE ORIGEM</th>
                        <th id="thultUsuario" class="titulo_resultado" >USUÁRIO RESPONSÁVEL</th>
                        <th id="thultDataHoraTram" class="titulo_resultado" >DATA/HORA ENTRADA TRAMITAÇÃO</th>
                    </tr>

        <?php      
               


                $htmlResultado = ''; 
                while ($Linha = $resultadoPesquisa->fetchRow()) {

                    // montagem do Url para a tela de detalhe do protocolo
                    $urlDetalhe = "CadTramitacaoDetalhe.php?protsequ=".$Linha[0]."&numprotocolo=".$Linha[2]."&anoprotocolo=".$Linha[3]."&numprotocoloRetorno=".$numProtocolo."&anoprotocoloRetorno=".$anoProtocolo;
                    $urlDetalhe .= "&orgao=".$orgao;
                    $urlDetalhe .= "&objeto=".$objeto;
                    $urlDetalhe .= "&numeroci=".$numeroci;
                    $urlDetalhe .= "&numeroOficio=".$numeroOficio;
                    $urlDetalhe .= "&numeroScc=".$numeroScc;
                    $urlDetalhe .= "&proLicitatorio=".$proLicitatorio;
                    $urlDetalhe .= "&acao=".$acao;
                    $urlDetalhe .= "&origem=".$origem;

                    if($DataEntradaIni!=""){
                        $urlDetalhe .= "&DataIni=".$DataEntradaIni;
                    }else{
                        $urlDetalhe .= "&DataIni=".$dataMes[0];
                    } 
                    if($DataEntradaFim!=""){
                        $urlDetalhe .= "&DataFim=".$DataEntradaFim;
                    }else{
                        $urlDetalhe .= "&DataFim=".$dataMes[1];
                    }

                    $urlDetalhe .= "&retornoEntrada=1";


                    $htmlResultado .= "<tr>";
                    $htmlResultado .= "<td align='center'><input type='radio' class='idSolicitacao soli' name='idProtocolo' id='idProtocolo' value='".$Linha[0]."'>";//
                    $htmlResultado .= "<a href='".$urlDetalhe."'>".str_pad($Linha[2], 4, "0", STR_PAD_LEFT)."/".$Linha[3]."</a></td>";// Protocolo / Ano
                    $htmlResultado .= "<td align='center'>".$Linha[20]."</td>";//órgão
                    $htmlResultado .= "<td >".$Linha[5]."</td>";//objeto
                    $htmlResultado .= "<td align='center'>".$Linha[6]."</td>";//num. CI
                    $htmlResultado .= "<td align='center'>".$Linha[7]."</td>";//num oficio

                    $processo = "-";
                    $fase = "-";

                    if(!empty($Linha[8])){ 
                            //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
                            
                            $arrFase = getFaseLicitacaoScc($Linha[8]);
                            $arrProcesso = getProcessoScc($Linha[8]);

                            if(!empty($arrFase)){
                                $arrFase = $arrFase[0];
                            }
                            if(!empty($arrProcesso)){
                                $arrProcesso = $arrProcesso[0];
                            }

                            //var_dump($arrProcesso);
                        
                            if(!empty($arrProcesso[0])) {
                                $processo = "<td align='center'><a href='ConsHistoricoDetalhes.php?LicitacaoProcessoDet=".str_pad($arrProcesso[0], 4, '0', STR_PAD_LEFT)."&LicitacaoAnoDet=".$arrProcesso[1]."&ComissaoCodigoDet=".$arrProcesso[2]."&GrupoCodigoDet=".$arrProcesso[3]."&OrgaoLicitanteCodigoDet=".$arrProcesso[4]."&origemTramitacao=1'>".str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5]."</a></td>";
                                $fase = $arrFase[1];
                            }else{
                                $processo = "<td align='center'>-</td>";
                                $fase = "-";
                            }

                    }else{
                        //verifica se existe processo cadastrado 
                        if(!empty($Linha[9])) {
                            $processo =  "<td align='center'><a href='ConsHistoricoDetalhes.php?LicitacaoProcessoDet=".str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."&LicitacaoAnoDet=".$Linha[10]."&ComissaoCodigoDet=".$Linha[12]."&GrupoCodigoDet=".$Linha[11]."&OrgaoLicitanteCodigoDet=".$Linha[13]."&origemTramitacao=1'>".str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."/".$Linha[10]. " - ".$Linha[28]."</a></td>";//Código do processo licitatório
                            $fase = $Linha[21];
                        }else{
                            $processo = "<td align='center'>-</td>";
                            $fase = "-";
                        }

                    }



                    if(!is_null($Linha[8])){

                        $htmlResultado .= "<td align='center'><a href='../compras/ConsAcompSolicitacaoCompra.php?SeqSolicitacao=".$Linha[8]."&origemTramitacao=1'>".getNumeroSolicitacaoCompra($db, $Linha[8])."</a></td>";//Numero SCC
                    }else{
                        $htmlResultado .= "<td align='center'></td>";//Numero SCC
                    }
                    

                    $htmlResultado .= $processo;

                    //if($Linha[9]>0){
                    //    $htmlResultado .= "<td align='center'><a href='ConsHistoricoDetalhes.php?LicitacaoProcessoDet=".str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."&LicitacaoAnoDet=".$Linha[10]."&ComissaoCodigoDet=".$Linha[12]."&GrupoCodigoDet=".$Linha[11]."&OrgaoLicitanteCodigoDet=".$Linha[13]."&origemTramitacao=1'>".str_pad($Linha[9], 4, "0", STR_PAD_LEFT)."/".$Linha[10]. " - ".$Linha[28]."</a></td>";//Código do processo licitatório
                    //}else{
                    //    $htmlResultado .= "<td></td>";
                    //}
                    
                    


                    $htmlResultado .= "<td align='center'>".$fase."</td>";//fase licitacao
                    $htmlResultado .= "<td align='center'>".date('d/m/Y H:i:s',strtotime($Linha[19]))."</td>";//  
                    //Dados da Ação                                      
                    $htmlResultado .= "<td align='center'>".$Linha[22]."</td>";//
                    $htmlResultado .= "<td align='center' class='apresentaHintAgente' id ='".$Linha[24]."' >".$Linha[23]."</td>";//agente
                    //Usuario
                    $usuarioDesc = '';
                    if($Linha[29]=='S'){
                                                    
                        if($Linha[30] <= 0 ){
                            $usuarioDesc = $Linha[23];
                        }else{
                            $usuarioDesc = $Linha[25];
                        }
                    }else{
                        if($Linha[30] <= 0){

                            if($Linha[31]=='I'){
                                $usuarioDesc = $Linha[23]; // ETAGENDESC
                            }else{
                                $usuarioDesc = 'ÓRGÃO EXTERNO';
                            }

                        }else{
                            $usuarioDesc = $Linha[25];
                        }
                    }
                    //$teste = 'Opa';
                    $htmlResultado .= "<td align='center'>".$usuarioDesc."</td>";


                    $htmlResultado .= "<td align='center'>".date('d/m/Y H:i:s',strtotime($Linha[26]))."</td>";//
                    $htmlResultado .= "</tr>";
                    

                }
            
                

                    echo $htmlResultado;
                //echo "Nenhum registro foi encontrado com os dados da pesquisa solicitada.";
        ?> 

            <tr >
                <td class="textonormal" align="right" colspan='13'>
                    <input type="submit" name="Selecionar" value="Enviar" class="botao" onclick="document.Entrada.botao.value = 'Selecionar'">
                    <input type="submit" name="Imprimir" value="Imprimir" class="botao" onclick="document.Entrada.botao.value = 'Imprimir'">
                    <input type="submit" name="Limpar" value="Limpar" class="botao" onclick="document.Entrada.botao.value = 'Limpar'">
                </td>
                    
            </tr>

            <?php       
            }else{// fim do if se houver dados
            ?>    
                <tr>
                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" style="width:100%;">Nenhum registro encontrado com os dados da pesquisa.</td>
                </tr>
            <?php
            }
            ?>
        </table>
        <div id='hintAgente' class='hint' style='display: none;'>Usuários do Agente:</div>
        <?php
            
        }
        
    }
}



?>                  
                    </td>
                    </tr>
                    </table>      
                    


                </td>
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>


<?php 

$usuariosAgentes = getUsuariosAgentes(Conexao());
$usuariosPorAgente = array();
foreach($usuariosAgentes as $usuario){

    $usuariosPorAgente[$usuario[0]][] = $usuario[2];

}

?>

</body>
<script language="javascript" type="">

var usuariosAgentes = <?php echo json_encode($usuariosPorAgente) ?>;
if(usuariosAgentes){
  $( ".apresentaHintAgente" ).mouseover(function() {
      var e = e ||  window.event;
      
      text = "Usuários do Agente:<br>";
      var i;
      for (i = 0; i < usuariosAgentes[this.id].length; i++) { 
          if(usuariosAgentes[this.id][i] == null){
            text += "Nenhum usuário associado.";
          }else{
            text += "<b> - "+ usuariosAgentes[this.id][i] + "</b><br>";
          }
      }

      $('#hintAgente').css({'top':e.pageY-70,'left':e.pageX-300, 'padding':'5px', 'font-size': '12px'});
      $('#hintAgente').html(text);
      $('#hintAgente').show();

  }).mouseout(function() {

      $('#hintAgente').hide();

  });
}
</script>

</html>