<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadTramitacaoProtocoloPesquisarEspecial.php
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/07/2018
# Objetivo:	Tarefa Redmine 199435
#-------------------------------------------------------------------------
# arquivo geral de funcoes
require_once("../funcoes.php");

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
    $botao                   = $_POST['Botao'];
    $grupoAtual              = $_POST['tramitacaoGrupo'];
    $orgaoAtual              = $_POST['tramitacaoOrgao'];
    $numeroProtocoloAtual    = $_POST['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual       = $_POST['tramitacaoAnoProtocolo'];
    $dataEntradaAtualInicio  = $_POST['tramitacaoDataEntradaInicio'];
    $dataEntradaAtualFim     = $_POST['tramitacaoDataEntradaFim'];
} else {
    $Critica  = $_GET['Critica'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
    //dados de retorno
    $botao                   = $_GET['botao'];
    $grupoAtual              = $_GET['tramitacaoGrupo'];
    $orgaoAtual              = $_GET['tramitacaoOrgao'];
    $numeroProtocoloAtual    = $_GET['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual       = $_GET['tramitacaoAnoProtocolo'];
    $dataEntradaAtualInicio  = $_GET['tramitacaoDataEntradaInicio'];
    $dataEntradaAtualFim     = $_GET['tramitacaoDataEntradaFim'];

    unset($_SESSION['Arquivos_Upload']);
    unset($_SESSION['sccTramitacao']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadTramitacaoProtocoloPesquisarEspecial.php";

$dados = array();

//se for realizada a exclusão de uma tramitação
if($_GET['Exclusao']==1){
    adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>O protocolo foi excluído com sucesso</a>", 1);
}

if($botao == 'SelecionarGrupo') {
    if($grupoAtual != '') {
       // $numeroProtocoloAtual = getNumeroProtocolo($grupoAtual);
    }

}else if($botao == 'Pesquisar') {
    $validar = true;

    if(!$_SESSION['_fperficorp_'] == 'S'){
        if(!isset($grupoAtual)){
            $grupoAtual = $_SESSION['_cgrempcodi_'] ;
        }
    }

    $buscar = array(
       'protocolo'    => $numeroProtocoloAtual,
       'anoProtocolo' => $anoProtocoloAtual,
       'grupo'        => $grupoAtual,
       'orgao'        => $orgaoAtual,
       'dataInicio'   => $dataEntradaAtualInicio,
       'dataFim'      => $dataEntradaAtualFim
    );

    if(!empty($buscar['protocolo'])){
        if(!SoNumeros($buscar['protocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocolo\").focus();' class='titulo2'>Protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }
    if(!empty($buscar['anoProtocolo'])){
        if(!SoNumeros($buscar['anoProtocolo'])) {
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputProtocoloAno\").focus();' class='titulo2'>Ano do Protocolo deve ser numérico</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }
    }

    if(!empty($buscar['dataInicio']) && ValidaData($buscar['dataInicio'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputEntrada\").focus();' class='titulo2'>Data de Inicio</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }

    if(!empty($buscar['dataFim']) && ValidaData($buscar['dataFim'])) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Fim</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
        $validar = false;
    }
    //verfica se data inicial é menor que a final
    if(!empty($buscar['dataInicio'])  && !empty($buscar['dataFim']) ) {
        
        $ArrDataIni= explode("/",$buscar['dataInicio']);
        $ArrDataFim = explode("/",$buscar['dataFim']);

        $dateTimeIni = strtotime($ArrDataIni[2].'-'.$ArrDataIni[1].'-'.$ArrDataIni[0]);
        $dateTimeFim= strtotime($ArrDataFim[2].'-'.$ArrDataFim[1].'-'.$ArrDataFim[0]);
         
        if ($dateTimeIni > $dateTimeFim){
            adicionarMensagem("<a href='javascript:document.getElementById(\"inputFim\").focus();' class='titulo2'>Data Inicial maior que data final</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);
            $validar = false;
        }


    }




    if($validar) {
        $dados = protocoloPesquisar($buscar);

    }
} else if($botao == 'Limpar') {
    header('Location: CadTramitacaoProtocoloPesquisarEspecial.php');
}

# Critica dos Campos #
if( $Critica == 1 ){
    $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissao == "" ) {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Licitacao.LicitacaoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    }else{
        $NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissao);
        $Processo             = substr($NProcessoAnoComissao[0] + 10000,1);
        $ProcessoAno          = $NProcessoAnoComissao[1];
        $ComissaoCodigo       = $NProcessoAnoComissao[2];
        $novaTela 			  = $NProcessoAnoComissao[3];
        if($novaTela=="1"){
            $Url = "CadLicitacaoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }else{
            $Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
        }
        if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
        header("location: ".$Url);
        exit;
    }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
    <?php MenuAcesso(); ?>

    function enviar(valor){

        document.CadTramitacaoProtocoloPesquisar.Botao.value=valor;
        document.CadTramitacaoProtocoloPesquisar.submit();
    }

    function CaracteresObjeto(text,campo){
        input = document.getElementById(campo);
        input.value = text.value.length;
    }
    function AbreJanela(url,largura,altura) {
        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
    }

</script>
<script language="JavaScript">Init();</script>
<form action="CadTramitacaoProtocoloPesquisarEspecial.php" method="POST" name="CadTramitacaoProtocoloPesquisar">
    <br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Número Protocolo > Manter Especial
            </td>
        </tr>
        <!-- Fim do Caminho-->

        <!-- Erro -->
        <?php if ( $Mens == 1 ) {?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
        <?php } ?>
        <!-- Fim do Erro -->

        <!-- Corpo -->
        <tr>
            <td width="150"></td>
            <td class="textonormal">
                <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                            MANTER ESPECIAL - NÚMERO PROTOCOLO PARA PROCESSOS LICITATÓRIOS
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal">
                            <p align="justify">
                                Preencha os dados abaixo para efetuar a pesquisa e clique no número do Protocolo do Processo Licitatório desejado.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="0" summary="" width="100%">
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Número/Ano do Protocolo do <br> Processo Liciatório</td>
                                    <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                    <td class="textonormal">
                                        <input id="inputProtocolo" size="3" maxlength="4" type="text" value="<?php echo !empty($numeroProtocoloAtual) ? $numeroProtocoloAtual : ''; ?>" name="tramitacaoNumeroProtocolo" class="textonormal" />
                                        &nbsp;&nbsp;/&nbsp;&nbsp;
                                        <input id="inputProtocoloAno" size="3" maxlength="4" type="text" value="<?php echo !empty($anoProtocoloAtual) ? $anoProtocoloAtual : ''; ?>" name="tramitacaoAnoProtocolo" class="textonormal" />
                                    </td>
                                </tr>
                                <?php if($_SESSION['_fperficorp_'] == 'S') { ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Grupo</td>
                                        <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                        <td class="textonormal">
                                            <select name="tramitacaoGrupo" id="inputGrupo" class="textonormal capturarValorAcaoGrupo" data-acao="SelecionarGrupo">
                                                <option value="">Selecione o grupo...</option>
                                                <?php
                                                $cgrempcodi = ($_SESSION['_fperficorp_'] != 'S') ? $_SESSION['_cgrempcodi_'] : null;
                                                $grupos = getGrupos($cgrempcodi);
                                                while ($grupo = $grupos->fetchRow()){
                                                    ?>
                                                    <option <?php 

                                                        if(isset($grupoAtual)){
                                                            if( $grupoAtual == $grupo[0]){
                                                                echo 'selected';
                                                            }
                                                        }else{
                                                            if( $grupo[0] == 1 ){
                                                                echo 'selected';
                                                                $grupoAtual = 1;
                                                            }
                                                        }
                                                        ?> value="<?php echo $grupo[0]; ?>"><?php echo $grupo[1]; ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão Demandante </td>
                                    <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                    <td class="textonormal">
                                        <select id="inputOrgao" name="tramitacaoOrgao" class="textonormal">
                                            <option value="">Selecione o órgão...</option>
                                            <?php
                                            $orgaos = null;
                                                // TODO verificar órgãos do usuário logado
                                                if($_SESSION['_fperficorp_']=='S'){
                                                    if(!empty($grupoAtual)){
                                                        $orgaos = getOrgaos($grupoAtual);
                                                    }
                                                }else{
                                                    $orgaos = getOrgaos($_SESSION['_cgrempcodi_']);
                                                }
                                                if($orgaos != null){
                                                    while ($orgao = $orgaos->fetchRow()) {
                                                    ?>
                                                        <option <?php echo (isset($orgaoAtual) && $orgaoAtual == $orgao[0]) ? 'selected' : ''?> value="<?php echo $orgao[0]; ?>"><?php echo $orgao[1]; ?></option>
                                                    <?php
                                                    }
                                                }
                                                ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada </td>
                                    <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                                    <td class="textonormal">
                                        <input id="inputEntrada" size="8" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaAtualInicio)) ? $dataEntradaAtualInicio : ''; ?>" name="tramitacaoDataEntradaInicio" class="textonormal" />
                                        <a style="text-decoration: none;" href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloPesquisar&Campo=tramitacaoDataEntradaInicio','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="">
                                        </a>
                                        &nbsp;&nbsp;&nbsp;a&nbsp;&nbsp;&nbsp;
                                        <input id="inputFim" size="8" type="text" maxlength="10" value="<?php echo (!empty($dataEntradaAtualFim)) ? $dataEntradaAtualFim : ''; ?>" name="tramitacaoDataEntradaFim" class="textonormal" />
                                        <a href="javascript:janela('../calendario.php?Formulario=CadTramitacaoProtocoloPesquisar&Campo=tramitacaoDataEntradaFim','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal" align="right" border="0">
                            <input type="submit" value="Pesquisar" onclick="javascript:enviar('Pesquisar');" class="botao">
                            <input type="submit" value="Limpar" onclick="javascript:enviar('Limpar');" class="botao">
                            <input type="hidden" name="Botao" value="" />
                        </td>
                    </tr>
                    <?php if(!empty($dados)) {?>
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">RESULTADO DA PESQUISA</td>
                    </tr>
                    <tr>
                        <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                            <tr>
                                <td align="center" bgcolor="#DCEDF7" class="titulo3">NÚMERO DO <br>PROTOCOLO</td>
                                <td align="center" bgcolor="#DCEDF7" class="titulo3">DATA ENTRADA</td>
                                <td align="center" bgcolor="#DCEDF7" class="titulo3">ÓRGÃO DEMANDANTE</td>
                                <td align="center" bgcolor="#DCEDF7" class="titulo3">OBJETO</td>
                            </tr>
                            <?php foreach ($dados as $key => $value) {?>
                            <tr>
                                <td align="center" class="textonormal">
                                    <?php  
                                        $url = '&tramitacaoGrupo='.$grupoAtual;
                                        $url .= '&tramitacaoOrgao='.$orgaoAtual;
                                        $url .= '&tramitacaoNumeroProtocolo='.$numeroProtocoloAtual;
                                        $url .= '&tramitacaoAnoProtocolo='.$anoProtocoloAtual;
                                        $url .= '&tramitacaoDataEntradaInicio='.$dataEntradaAtualInicio;
                                        $url .= '&tramitacaoDataEntradaFim='.$dataEntradaAtualFim;
                                    
                                    ?>
                                    <a href="CadTramitacaoProtocoloManterEspecial.php?protocolo=<?php echo $value[0].$url; ?>">
                                        <?php echo str_pad($value[1], 4, "0", STR_PAD_LEFT); ?>/<?php echo $value[5]; ?>
                                    </a>
                                </td>
                                <td align="center" class="textonormal"><?php echo substr($value[3], 8, 2) . "/" . substr($value[3], 5, 2) . "/" . substr($value[3], 0, 4); ?></td>
                                <td align="center" class="textonormal"><?php echo $value[2]; ?></td>
                                <td align="left" class="textonormal"><?php echo $value[4]; ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </tr>
                    <?php }else{ 
                        if($botao == 'Pesquisar'){?>
                            <table width="100%"  cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" style="border:2px solid #75ADE6;border-top:0px solid #000;">
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">RESULTADO DA PESQUISA</td>
                                </tr>
                                <tr>
                                    <td class="textonormal">
                                        Pesquisa sem ocorrências.
                                    </td>
                                </tr>
                            </table>
                    <?php }
                        } ?>    
                </table>
            </td>
        </tr>
        <!-- Fim do Corpo -->
    </table>
</form>
</body>
<script>
    $(document).ready(function() {
        $(".capturarValorAcaoGrupo").change(function() {
            var acao  = $(this).attr('data-acao');
            document.CadTramitacaoProtocoloPesquisar.Botao.value = acao;
            document.CadTramitacaoProtocoloPesquisar.submit();
        });
        //if($("#inputOrgao").val() == 0 && $("#inputOrgao").child && $(".capturarValorAcaoGrupo").val != 0){
       ///     $(".capturarValorAcaoGrupo").change();
       // }
    });
</script>
</html>
