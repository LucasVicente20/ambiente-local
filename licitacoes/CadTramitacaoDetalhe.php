<?php
/**
 * Portal de Compras
 * 
 * Programa: CadTramitacaoDetalhe.php
 * ---------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     25/07/2018
 * Objetivo: Tarefa Redmine 199435
 * ---------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     15/08/2018
 * Objetivo: Tarefa Redmine 199436
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/05/2019
 * Objetivo: Tarefa Redmine 217487
 * ---------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     03/10/2019 
 * Objetivo: Tarefa Redmine 223486
 * ---------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Executa o controle de segurança #

// informações referentes as páginas de tramitações para retorno após o uso
session_start();
$pesquisa = $_SESSION['origemPesquisa'];
$pesquisaAgente = $_SESSION['relTramitacaoAgente'];

Seguranca();

$db = Conexao();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
    $botao        = $_POST['Botao'];
    $Sequencial   = $_POST['protsequ'];
    $numprotocolo = $_POST['numprotocolo'];
    $anoprotocolo = $_POST['anoprotocolo'];

    //Dados para retornar a tela de pesquisa
    $numprotocoloRetorno   = $_POST['numprotocoloRetorno'];
    $anoprotocoloRetorno   = $_POST['anoprotocoloRetorno'];
    $orgaoRetorno          = $_POST['orgaoRetorno'];
    $objetoRetorno         = $_POST['objetoRetorno'];
    $numerociRetorno       = $_POST['numerociRetorno'];
    $numeroOficioRetorno   = $_POST['numeroOficioRetorno'];
    $numeroSccRetorno      = $_POST['numeroSccRetorno'];
    $proLicitatorioRetorno = $_POST['proLicitatorioRetorno'];
    $acaoRetorno           = $_POST['acaoRetorno'];
    $origemRetorno         = $_POST['origemRetorno'];
    $DataIniRetorno        = $_POST['DataIniRetorno'];
    $DataFimRetorno        = $_POST['DataFimRetorno'];
    $retornoEntrada        = $_POST['retornoEntrada'];
    $retornoSaida          = $_POST['retornoSaida'];
    $origemTramitacao      = $_POST['origemTramitacao'];
} else {
    $Critica  = $_GET['Critica'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
    $Sequencial   = $_GET['protsequ'];

    $numprotocolo          = $_GET['numprotocolo'];
    $anoprotocolo          = $_GET['anoprotocolo'];
    //dados para voltar
    $numprotocoloRetorno   = $_POST['numprotocoloRetorno'];
    $anoprotocoloRetorno   = $_POST['anoprotocoloRetorno'];
	$orgaoRetorno          = $_GET['orgao'];
	$objetoRetorno         = $_GET['objeto'];
	$numerociRetorno       = $_GET['numeroci'];
	$numeroOficioRetorno   = $_GET['numeroOficio'];
	$numeroSccRetorno      = $_GET['numeroScc'];
	$proLicitatorioRetorno = $_GET['proLicitatorio'];
	$acaoRetorno           = $_GET['acao'];
	$origemRetorno         = $_GET['origem'];
	$DataIniRetorno    = $_GET['DataIni'];
    $DataFimRetorno    = $_GET['DataFim'];
    $retornoEntrada    = $_GET['retornoEntrada'];
    $retornoSaida    = $_GET['retornoSaida'];
    $usuarioAlterado = $_GET['usuarioAlterado'];
    $tramitacaoExcluida = $_GET['tramitacaoExcluida'];
    $origemTramitacao = $_GET['origemTramitacao'];

    $download = $_GET['download'];
    $protDown = $_GET['protDown'];
    $licDown  = $_GET['licDown'];  
    $seqDown  = $_GET['seqDown'];

}

if(empty($Sequencial) || empty($numprotocolo) ||  empty($anoprotocolo)) {
    header('Location: CadTramitacaoEntrada.php');
    exit();
}

if($download == 1) {
    
    $docDown = getTramitacaoLicitacaoAnexos($licDown, $protDown, $seqDown);
    $arrNome = explode('.',$docDown['nome'][0]);
    $extensao = $arrNome[1];

    $mimetype = 'application/octet-stream';
    
    header( 'Content-type: '.$mimetype ); 
    header( 'Content-Disposition: attachment; filename='.$docDown['nome'][0] );   
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache');
    
    echo pg_unescape_bytea($docDown['conteudo'][0]);

    die();

}
//Download de protocolo  |MADSON|
if($download == 2) {
    $docDown = getProtocoloAnexosFull($Sequencial, $seqDown);
    $arrNome = explode('.',$docDown['nome'][0]);
    $extensao = $arrNome[1];

    $mimetype = 'application/octet-stream';
    
    header( 'Content-type: '.$mimetype ); 
    header( 'Content-Disposition: attachment; filename='.$docDown['nome'][0] );   
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache');
    
    echo pg_unescape_bytea($docDown['conteudo'][0]);

    die();

}


if($botao == 'Alterar') {
    $params = '?protsequ=' . $Sequencial;
    $params .= '&numprotocolo=' . $numprotocolo;
    $params .= '&anoprotocolo=' . $anoprotocolo;
    $params .= "&numProtocoloRetorno=".$numProtocoloRetorno;
    $params .= "&anoProtocoloRetorno=".$anoProtocoloRetorno;
    $params .= "&orgao=".$orgaoRetorno;
    $params .= "&objeto=".$objetoRetorno;
    $params .= "&numeroci=".$numerociRetorno;
    $params .= "&numeroOficio=".$numeroOficioRetorno;
    $params .= "&numeroScc=".$numeroSccRetorno;
    $params .= "&proLicitatorio=".$proLicitatorioRetorno;
    $params .= "&acao=".$acaoRetorno;
    $params .= "&origem=".$origemRetorno;
    $params .= "&DataIni=".$DataIniRetorno;
    $params .= "&DataFim=".$DataFimRetorno;
    $params .= "&retornoEntrada=".$retornoEntrada;
    $params .= "&retornoSaida=".$retornoSaida;
    $params .= '&botao=Alterar';
    header('Location: CadTramitacaoConfirmacao.php' . $params);
    exit();
}

if($botao == 'Excluir') {
    $params = '?protsequ=' . $Sequencial;
    $params .= '&numprotocolo=' . $numprotocolo;
    $params .= '&anoprotocolo=' . $anoprotocolo;
    //dados para retorno a pesquisa inicial
    $params .= "&numProtocoloRetorno=".$numProtocoloRetorno;
    $params .= "&anoProtocoloRetorno=".$anoProtocoloRetorno;
    $params .= "&orgao=".$orgaoRetorno;
    $params .= "&objeto=".$objetoRetorno;
    $params .= "&numeroci=".$numerociRetorno;
    $params .= "&numeroOficio=".$numeroOficioRetorno;
    $params .= "&numeroScc=".$numeroSccRetorno;
    $params .= "&proLicitatorio=".$proLicitatorioRetorno;
    $params .= "&acao=".$acaoRetorno;
    $params .= "&origem=".$origemRetorno;
    $params .= "&DataIni=".$DataIniRetorno;
    $params .= "&DataFim=".$DataFimRetorno;
    $params .= "&retornoEntrada=".$retornoEntrada;
    $params .= "&retornoSaida=".$retornoSaida;
    $params .= '&botao=Excluir';
    //$params .= '&anoprotocolo=' . $anoprotocolo;
    header('Location: CadTramitacaoConfirmacao.php' . $params);
    exit();
}

if($botao == 'Voltar') {


    if( isset($pesquisa) && $origemTramitacao== 2){

        unset($_SESSION['origemPesquisa']);

        $Url = "Location: RelTramitacaoMonitoramento.php?";
        $Url .= "tramitacaoNumeroProtocolo=".$pesquisa['tramitacaoNumeroProtocolo'];
        $Url .= "&tramitacaoAnoProtocolo=".$pesquisa['tramitacaoAnoProtocolo'];
        $Url .= "&tramitacaoGrupo=".$pesquisa['tramitacaoGrupo'];
        $Url .= "&tramitacaoOrgao=".$pesquisa['tramitacaoOrgao'];
        $Url .= "&tramitacaoObjeto=".$pesquisa['tramitacaoObjeto'];
        $Url .= "&tramitacaoNumeroCI=".$pesquisa['tramitacaoNumeroCI'];
        $Url .= "&tramitacaoNumeroOficio=".$pesquisa['tramitacaoNumeroOficio'];
        $Url .= "&tramitacaoNumeroScc=".$pesquisa['tramitacaoNumeroScc'];
        $Url .= "&tramitacaoComissaoLicitacao=".$pesquisa['tramitacaoComissaoLicitacao'];
        $Url .= "&tramitacaoAcao=".$pesquisa['tramitacaoAcao'];
        $Url .= "&tramitacaoAgenteDestino=".$pesquisa['tramitacaoAgenteDestino'];
        $Url .= "&tramitacaoProcessoNumero=".$pesquisa['tramitacaoProcessoNumero'];
        $Url .= "&tramitacaoProcessoAno=".$pesquisa['tramitacaoProcessoAno'];
        $Url .= "&tramitacaoDataEntradaInicio=".$pesquisa['tramitacaoDataEntradaInicio'];
        $Url .= "&tramitacaoDataEntradaFim=".$pesquisa['tramitacaoDataEntradaFim'];
        $Url .= "&tramitacaoSituacao=".$pesquisa['tramitacaoSituacao'];
        $Url .= "&tramitacaoOrdem=".$pesquisa['tramitacaoOrdem'];
        $Url .= "&tramitacaoAtraso=".$pesquisa['tramitacaoAtraso'];
        $Url .= "&Botao=Pesquisar";//&Critica=1";



    }else if(isset($pesquisaAgente) && $origemTramitacao== 3){


        unset($_SESSION['relTramitacaoAgente']);

        $Url = "Location: RelAcompTramitacaoAgenteSelecionar.php?";
        $Url .= "tramitacaoAgente=".$pesquisaAgente['agente'];
        $Url .= "&tramitacaoResponsavel=".$pesquisaAgente['responsavel'];
        $Url .= "&tramitacaoDataEntradaInicio=".$pesquisaAgente['dataInicio'];
        $Url .= "&tramitacaoDataEntradaFim=".$pesquisaAgente['dataFim'];
        $Url .= "&tramitacaoSituacao=".$pesquisaAgente['situacao'];
        $Url .= "&tramitacaoAtraso=".$pesquisaAgente['atraso'];
        $Url .= "&Botao=Pesquisar";


    }else{
        //Se vier de entrada ou saída

        if($retornoEntrada == 1){
            $Url = "Location: CadTramitacaoEntrada.php?";
            $tipoRetorno = 'Entrada';
        }else{
            $Url = "Location: CadTramitacaoSaida.php?";
            $tipoRetorno = 'Saida';    
        }

        $Url .= "numProtocolo=".$numProtocoloRetorno;
        $Url .= "&anoProtocolo=".$anoProtocoloRetorno;
        $Url .= "&numProtocoloRetorno=".$numProtocoloRetorno;
        $Url .= "&anoProtocoloRetorno=".$anoProtocoloRetorno;
        $Url .= "&orgao=".$orgaoRetorno;
        $Url .= "&objeto=".$objetoRetorno;
        $Url .= "&numeroci=".$numerociRetorno;
        $Url .= "&numeroOficio=".$numeroOficioRetorno;
        $Url .= "&numeroScc=".$numeroSccRetorno;
        $Url .= "&proLicitatorio=".$proLicitatorioRetorno;
        $Url .= "&acao=".$acaoRetorno;
        $Url .= "&origem=".$origemRetorno;
        $Url .= "&Data".$tipoRetorno."Ini=".$DataIniRetorno;
        $Url .= "&Data".$tipoRetorno."Fim=".$DataFimRetorno;
        $Url .= "&botao=Pesquisar&Critica=1";
    }
    $Url .= "&t=".mktime();
    header($Url);
    exit();
}


if($botao == 'Imprimir') {
    $params = '?protsequ=' . $Sequencial;
    $params .= '&numprotocolo=' . $numprotocolo;
    $params .= '&anoprotocolo=' . $anoprotocolo;
    header('Location: CadTramitacaoDetalhePdf.php' . $params);
    exit();
}

$protocolo  = getProtocoloDetalhe($Sequencial);
$processo   = getProcesso($Sequencial, $numprotocolo, $anoprotocolo);
$passos     = getTramitacaoPassos($Sequencial);
if($protocolo[12]){
    $nnumeroScc = getNumeroSolicitacaoCompra($db, $protocolo[12]);
}else{
    $nnumeroScc = '';
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


if($usuarioAlterado == 1){
    $Mens      = 1;
    $Tipo      = 1;
    $Mensagem .= "<a href=\"#\" class=\"titulo2\">Usuário alterado com sucesso</a>";
}

if($tramitacaoExcluida == 1){
    $Mens      = 1;
    $Tipo      = 1;
    $Mensagem .= "<a href=\"#\" class=\"titulo2\">Último passo excluído com sucesso</a>";
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
<script language="javascript" type="text/javascript">
    <?php MenuAcesso(); ?>

    function enviar(valor){
        document.CadTramitacaoDetalhe.Botao.value=valor;
        document.CadTramitacaoDetalhe.submit();
    }
</script>
<script language="JavaScript">Init();</script>
<form action="CadTramitacaoDetalhe.php" method="POST" name="CadTramitacaoDetalhe">
    <input type="hidden" value="<?php echo $Sequencial; ?>" name="protsequ" />
    <input type="hidden" value="<?php echo $numprotocolo; ?>" name="numprotocolo" />
    <input type="hidden" value="<?php echo $anoprotocolo; ?>" name="anoprotocolo" />
    <br><br><br><br><br><br>
    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <?php 
                if($retornoEntrada == 1){
                    $txtLocal = 'Tramitação > Entrada';
                }else{
                    if($origemTramitacao == 3){
                        $txtLocal = 'Relatórios > Tramitação > Por Agente';    
                    }else{
                        if($origemTramitacao == 2){
                            $txtLocal = 'Relatórios > Tramitação > Monitoramento';  
                        }else{
                            $txtLocal = 'Tramitação > Saída';
                        }  
                        
                    }
                    
                } 
                
                ?>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > <?php echo $txtLocal?> > Detalhe
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
                            DETALHE - PROCESSOS LICITATÓRIOS
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal">
                            <p align="justify" id="textoOrientacao">
                                <?php 
                                $textoInicial = 'Para visualizar outro protocolo, clique no botão "Voltar".';
                                if($retornoSaida){
                                    $textoInicial .= '<br>Para alterar o usuário responsável do agente de origem enviado, clique em "Alterar".<br>Para excluir o último passo que está em aberto, clique em "Excluir Último Passo"';
                                }
                                    echo $textoInicial; 

                                ?>
                             
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table border="0" summary="" width="100%">
                                <?php if($protocolo[1]){ ?>
                                    <tr>
                                        <td style="width: 30%" class="textonormal" bgcolor="#DCEDF7">Número do Protocolo </td>
                                        <td class="textonormal">
                                            <?php echo str_pad($protocolo[1], 4, "0", STR_PAD_LEFT); ?>/<?php echo $protocolo[5]; ?>
                                        </td>
                                    </tr>
                                <?php  } ?>

                                <?php  if($protocolo[2]){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Órgão Demandante </td>
                                        <td class="textonormal">
                                            <?php echo $protocolo[2]; ?>
                                        </td>
                                    </tr>
                                <?php  } ?>

                                <?php  if($protocolo[4]){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Objeto </td>
                                        <td class="textonormal">
                                            <font class="textonormal"><?php echo $protocolo[4]; ?></font>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if($protocolo[6]){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Número CI </td>
                                        <td class="textonormal">
                                            <?php echo $protocolo[6]; ?>
                                        </td>
                                    </tr>
                                <?php } ?> 
                                
                                <?php if($protocolo[7]){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Número Ofício </td>
                                        <td class="textonormal">
                                            <?php echo $protocolo[7]; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                    
                                <?php if($nnumeroScc){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Número da SCC </td>
                                        <td class="textonormal">
                                            <?php echo $nnumeroScc; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php 
                                
                                


                                        if(!empty($protocolo[12])){ 
                                            //APRESENTAR DADOS DO PROCESSO ASSOCIADO A SCC;
                                            
                                            $arrProcesso = getProcessoScc($protocolo[12]);


                                            if(!empty($arrProcesso)){
                                                $arrProcesso = $arrProcesso[0];
                                            }

                                            ////($arrProcesso);
                                        
                                            if(!empty($arrProcesso[0])) {
                                                $processo = str_pad($arrProcesso[0], 4, "0", STR_PAD_LEFT) . '/' . $arrProcesso[1]. ' - '. $arrProcesso[5];
                                            }

                                        }else{
                                            //verifica se existe processo cadastrado 
                                            if(!empty($protocolo[8])) {
                                                $processo = str_pad($protocolo[8], 4, "0", STR_PAD_LEFT) . '/' . $protocolo[9]. ' - '. $protocolo[11];

                                            }

                                        }
                                
                                if($processo){ 
                                    
                                    ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório </td>
                                        <td class="textonormal">
                                            <?php echo $processo ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Data/Hora cadastramento protocolo </td>
                                        <td class="textonormal">
                                            <?php
                                                $arrDataCad = explode("-",substr($protocolo[3],0,10));
                                                $HoraCad = substr($protocolo[3],11,5);
                                                $dataHoraCad = $arrDataCad[2]."/".$arrDataCad[1]."/".$arrDataCad[0]." ".$HoraCad ;
                                                echo $dataHoraCad . ' - ' . $protocolo[17] ;
                                            ?>
                                        </td>
                                </tr>
                                <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Data/Hora alteração protocolo </td>
                                        <td class="textonormal">
                                            <?php
                                                $arrAlteracao = explode("-",substr($protocolo[15],0,10));
                                                $HoraAlt = substr($protocolo[15],11,5);
                                                $dataHoraAlt = $arrAlteracao[2]."/".$arrAlteracao[1]."/".$arrAlteracao[0]." ".$HoraAlt ;
                                                echo $dataHoraAlt.' - '.$protocolo[14] ;
                                            ?>
                                        </td>
                                    </tr>
                                <tr>                                      
                                        <?php  //|MADSON|
                                            $dadosProtocolo = checaProtocolo($Sequencial);
                                            $protoCont = count($dadosProtocolo ['nome']);
                                            
                                            if($protoCont>0){
                                                if($protoCont == 1){
                                                echo '<td class="textonormal" bgcolor="#DCEDF7">Documento Anexo</td>';
                                            }else{
                                                echo '<td class="textonormal" bgcolor="#DCEDF7">Documentos Anexos</td>';
                                            }   
                                                echo "<td class='textonormal' >";
                                                for ($pCont = 0; $pCont < $protoCont; ++ $pCont) {
                                                    echo '<a href="#" onclick="enviarDownloadProtocolo('.$dadosProtocolo['seqAnexo'][$pCont].','.$dadosProtocolo['protocolo'][$pCont].')">'.$dadosProtocolo['nome'][$pCont].'</a><br>';
                                                }
                                            }
                                        ?>
                                    </td>
                                </tr> 
                            
                                <?php if($protocolo[18]){ ?>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Monitoramento </td>
                                        <td class="textonormal">
                                            <font class="textonormal"><?php echo $protocolo[18]; ?></font>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">TRAMITAÇÃO</td>
                    </tr>
                    <?php if(!empty($passos)) {

                        
                        $len = count($passos);
                        $i = 0;
                        $num = 0;
                        foreach ($passos as $key => $value) { 
                            
                            $documentos = getTramitacaoLicitacaoAnexos($value[11], $value[12]);


                            if($i == 0){
                                $ultimoPasso = true;
                                if($value[9]=='E'){

                                    //confere se vem de outras paginas
                                    $tipoAgente = $value[9];

                                }


                                
                            }else{
                                $ultimoPasso = false;
                            }    


                    ?>
                            <tr>
                                <td align="left" valign="middle" class="titulo3"><?php 
                                if($ultimoPasso){ 
                                    echo 'Último Passo';
                                }else{
                                    echo $len - $num . 'º Passo';
                                } 
                                ?></td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" summary="" width="100%" id="tabelaPasso" >
                                        <tr>
                                            <td style="width: 30%" class="textonormal" bgcolor="#DCEDF7">Agente</td>
                                            <td class='apresentaHintAgente textonormal' id ='<?php echo $value[18]; ?>'>
                                                <?php echo $value[0]; ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Usuário Responsável</td>
                                            <td class="textonormal">
                                                <?php 

                                                if($value[17]=='S'){ // FTACAOTUSU
                                                    
                                                    if($value[8] <= 0 ){//CUSUPOCODI
                                                        echo $value[0];// ETAGENDESC
                                                    }else{
                                                        echo $value[1];//EUSUPORESP
                                                    }
                                                }else{
                                                    if($value[8]=='0'){//CUSUPOCODI
                                                        if($value[9]=='I'){
                                                            echo $value[0]; // ETAGENDESC
                                                        }else{
                                                            echo 'ÓRGÃO EXTERNO';
                                                        }
                                                    }else{
                                                        echo $value[1];//EUSUPORESP
                                                    }
                                                }

                                                 
                                                ?>
                                                <?php if($ultimoPasso && ($value[10]==$_SESSION['_cusupocodi_']) && $retornoSaida == 1 && $value[17]!='S'){ ?>
                                                    <input type="submit" value="Alterar" onclick="javascript:enviar('Alterar');" class="botao">
                                                <?php } ?>
                                            </td>
                                            <td class="textonormal">

                                            </td>
                                        </tr>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Ação</td>
                                            <td class="textonormal">
                                                <?php echo $value[2]; ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <?php if($value[13]){ ?>                    
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Encaminhado para a Comissão</td>
                                            <td class="apresentaHintComissao textonormal"  id ='<?php echo $value[13]; ?>'>
                                                <?php echo $value[14]; ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <?php } ?>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Data de Entrada</td>
                                            <td class="textonormal">
                                                <?php 
                                                    $entradaHora = substr($value[3],11,5); 
                                                    echo substr($value[3],8,2).'/'.substr($value[3],5,2).'/'.substr($value[3],0,4)." ".$entradaHora; 
                                                ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <?php
                                            $now = date('Y-m-d');
                                            $saida = substr($value[5],0,10);
                                            $saidaHora = substr($value[5],11,5); 

                                            $arrEntrada = explode("-",substr($value[3],0,10));
                                            $dataHoraEntrada = $arrEntrada[2]."/".$arrEntrada[1]."/".$arrEntrada[0];
                                                 

                                            $previsto = calcularTramitacaoSaida($dataHoraEntrada, $value[4]);
                                            $arrPrevisto = explode("/",$previsto);
                                            $dataPrevista = $arrPrevisto[2]."-".$arrPrevisto[1]."-".$arrPrevisto[0];                                        

                                                    


                                        ?>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Prazo em dias</td>
                                            <td class="textonormal">
                                                <?php
                                                    echo $value[4]; 
                                                ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Data Prevista</td>
                                            <td class="textonormal">
                                                <?php echo $previsto; ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>

                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Realizado</td>
                                            <td class="textonormal">
                                                <?php
                                                    if($value[5]){
                                                        echo substr($value[5],8,2).'/'.substr($value[5],5,2).'/'.substr($value[5],0,4)." ".$saidaHora; 
                                                    }
                                                ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                    <?php

                                    $diffDias = 0;               

                                     if($saida){   
                                        if(strtotime($saida) > strtotime($dataPrevista)) { 

                                            //$diffDias = calculaDias2($dataPrevista, $saida);
                                            $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $saida);

                                            $dia = ($diffDias > 1) ? ' dias' : ' dia';
                                            
                                        ?>

                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Atraso</td>
                                            <td class="textonormal">
                                                <font color="red"><?php echo $diffDias . $dia; ?></font>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                    <?php 
                                        } 
                                    }else{
                                        $atual = date('Y-m-d');
                                        if(strtotime($atual) > strtotime($dataPrevista)) { 

                                            //$diffDias = calculaDias2($dataPrevista, $atual); 
                                            $diffDias = calcularTramitacaoDiasUteisAtraso($dataPrevista, $atual);

                                            $dia = ($diffDias > 1) ? ' dias' : ' dia';
                                            

                                            ?>

                                            <tr>
                                                <td class="textonormal" bgcolor="#DCEDF7">Atraso</td>
                                                <td class="textonormal">
                                                <font color="red"><?php echo $diffDias . $dia; ?></font>
                                                </td>
                                                <td class="textonormal"></td>
                                            </tr>
                                        <?php 
                                        } 
                                    }
                                    ?>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Observação</td>
                                            <td class="textonormal">
                                                <?php echo $value[6]; ?>
                                            </td>
                                            <td class="textonormal"></td>
                                        </tr>
                                        <?php
                                            
                                            $DTotal = count($documentos['conteudo']);
                                            if($DTotal>0){
                                             echo '<td rowspan="'.$DTotal.'" class="textonormal" bgcolor="#DCEDF7">Anexação de documentos</td>';
                                             echo "<td class='textonormal' >";
                                             for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
                                                echo '<a href="#" onclick="enviarDownload('.$documentos['codigo'][$Dcont].','.$documentos['protocolo'][$Dcont].','.$documentos['licitacao'][$Dcont].')">'.$documentos['nome'][$Dcont].'</a><br>';
                                             }
                                             echo '</td></tr>';
                                            }


                                           // //($ultimoPasso." - ".$value[10] ." - ". $_SESSION['_cusupocodi_']." - ".$retornoSaida);
                                            ?>
                                                

                                            
                                        
                                    </table>
                                </td>
                            </tr>
                            <?php

                        
                              if($ultimoPasso &&  $retornoSaida == 1){ 

                                //pegar as comissoes do usuario
                                $comissoesUsuario = getComissoesUsuario($db, $_SESSION['_cusupocodi_']);
                                $agentesUsuario = getAgentesUsuario($db, $_SESSION['_cusupocodi_']);

                                $existeReferenciaAgente = existeReferenciaAgente($agentesUsuario, $passos);
                                $existeReferenciaComissao = existeReferenciaComissao($comissoesUsuario, $passos);
                                if( ($value[10]==$_SESSION['_cusupocodi_']) || $existeReferenciaAgente || $existeReferenciaComissao){
                                ?>
                                            
                                            <tr style="border: 1px solid #75ADE6">
                                            
                                            <td class="textonormal excluir_ultimo_passo" align="right" colspan = '3' style="padding:2px;">
                                                <?php if($tipoAgente == 'E'){ 
                                                       echo '<script>
                                                        $(\'#textoOrientacao\').html(\'Para visualizar outro protocolo, clique no botão "Voltar".<br>Para alterar o usuário responsável do agente de origem enviado, clique em "Alterar". <br>Para excluir o último passo que está em aberto, clique em "Excluir Último Passo".<br> Para receber documento de um agente externo, clique em "Receber Documento".\');
                                                        </script> ';   
                                                ?> 

                                                    <input type="submit" value="Receber protocolo" onclick="javascript:AbreJanela('CadTramitacaoEntradaEnvio.php?numTramitacao=<?php echo $Sequencial ?>&window=1&receberProtocolo=1', 900, 500)" class="botao">
                                                <?php } ?>    
                                                <input type="submit" value="Excluir último passo" onclick="javascript:enviar('Excluir');" class="botao" >
                                            </td>
                                                
                                            </tr>        
                                    <?php 
                                    }
                                } ?>    
                            <?php
                            
                            $num++;
                            $i++;
                        }
                    }
                    ?>                    
                    <tr>
                        <td class="textonormal" align="right">
                            <input type="submit" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao">
                            <input type="submit" value="Voltar" onclick="javascript:enviar('Voltar');" class="botao">
                            <input type="hidden" name="Botao" value="" />

                            <!-- dados para retorno da tela -->
                            <input type="hidden" name="numProtocolo" value="<?php echo $numprotocolo ?>">
                            <input type="hidden" name="anoProtocolo" value="<?php echo $anoprotocolo ?>">
                            <input type="hidden" name="numProtocoloRetorno" value="<?php echo $numprotocoloRetorno ?>">
                            <input type="hidden" name="anoProtocoloRetorno" value="<?php echo $anoprotocoloRetorno ?>">
                            <input type="hidden" name="orgaoRetorno" value="<?php echo $orgaoRetorno ?>">
                            <input type="hidden" name="objetoRetorno" value="<?php echo $objetoRetorno ?>">
                            <input type="hidden" name="numerociRetorno" value="<?php echo $numerociRetorno ?>">
                            <input type="hidden" name="numeroOficioRetorno" value="<?php echo $numeroOficioRetorno ?>">
                            <input type="hidden" name="numeroSccRetorno" value="<?php echo $numeroSccRetorno ?>">
                            <input type="hidden" name="proLicitatorioRetorno" value="<?php echo $proLicitatorioRetorno ?>">
                            <input type="hidden" name="acaoRetorno" value="<?php echo $acaoRetorno ?>">
                            <input type="hidden" name="origemRetorno" value="<?php echo $origemRetorno ?>">
                            <input type="hidden" name="DataIniRetorno" value="<?php echo $DataIniRetorno ?>">
                            <input type="hidden" name="DataFimRetorno" value="<?php echo $DataFimRetorno ?>">  
                            <input type="hidden" name="retornoEntrada" value="<?php echo $retornoEntrada ?>">
                            <input type="hidden" name="retornoSaida" value="<?php echo $retornoSaida ?>">   
                            <input type="hidden" name="origemTramitacao" value="<?php echo $origemTramitacao ?>">   
                        </td>
                    </tr>
                </table>
                <div id='hintAgente' class='hint' style='display: none;'>Usuários do Agente:</div>
                <div id='hintComissao' class='hint' style='display: none;'>Usuários da Comissão:</div>
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

$usuariosComissao = getUsuariosComissao(Conexao());
$usuariosPorComissao = array();
foreach($usuariosComissao as $usuarioCom){

    $usuariosPorComissao[$usuarioCom[0]][] = $usuarioCom[2];

}

?>
</body>
<style>
    .excluir_ultimo_passo{
        border-collapse:collapse;
        margin-bottom: 0px;
        padding:0px;
        border-top:

    }
    #tabelaPasso{
        padding: 0px;

    }
</style>
<script>
    //|MADSON| Esta função copia a de licitação abaixo dela, os nomes de variáveis foram mantidos para evitar erros uma vez que não seriam problema.
    function enviarDownloadProtocolo(codigo, protocolo, licitacao){
        
<?php  
        // montagem do Url para a tela de detalhe do protocolo
        $urlDetalhe = "CadTramitacaoDetalhe.php?protsequ=".$Sequencial."&numprotocolo=".$numprotocolo."&anoprotocolo=".$anoprotocolo;
        $urlDetalhe .= "&orgao=".$orgao;
        $urlDetalhe .= "&objeto=".$objeto;
        $urlDetalhe .= "&numeroci=".$numeroci;
        $urlDetalhe .= "&numeroOficio=".$numeroOficio;
        $urlDetalhe .= "&numeroScc=".$numeroScc;
        $urlDetalhe .= "&proLicitatorio=".$proLicitatorio;
        $urlDetalhe .= "&acao=".$acao;
        $urlDetalhe .= "&origem=".$origem;

        if($DataEntradaIni!=""){
            $urlDetalhe .= "&DataIni=".$DataIniRetorno ;
        }else{
            $urlDetalhe .= "&DataIni=".$dataMes[0];
        } 
        if($DataEntradaFim!=""){
            $urlDetalhe .= "&DataFim=".$DataFimRetorno ;
        }else{
            $urlDetalhe .= "&DataFim=".$dataMes[1];
        }

        if($retornoEntrada == 1){
            $urlDetalhe .= "&retornoEntrada=1";  
        }else{
            $urlDetalhe .= "&retornoSaida=1"; 
        }
        
        $urlDetalhe .= "&download=2";        
        
        echo 'location.href ="'.$urlDetalhe.'&protDown="+protocolo+"&licDown="+licitacao+"&seqDown="+codigo';
    ?>
}
    function enviarDownload(codigo, protocolo, licitacao){

        <?php
                // montagem do Url para a tela de detalhe do protocolo
                $urlDetalhe = "CadTramitacaoDetalhe.php?protsequ=".$Sequencial."&numprotocolo=".$numprotocolo."&anoprotocolo=".$anoprotocolo;
                $urlDetalhe .= "&orgao=".$orgao;
                $urlDetalhe .= "&objeto=".$objeto;
                $urlDetalhe .= "&numeroci=".$numeroci;
                $urlDetalhe .= "&numeroOficio=".$numeroOficio;
                $urlDetalhe .= "&numeroScc=".$numeroScc;
                $urlDetalhe .= "&proLicitatorio=".$proLicitatorio;
                $urlDetalhe .= "&acao=".$acao;
                $urlDetalhe .= "&origem=".$origem;

                if($DataEntradaIni!=""){
                    $urlDetalhe .= "&DataIni=".$DataIniRetorno ;
                }else{
                    $urlDetalhe .= "&DataIni=".$dataMes[0];
                } 
                if($DataEntradaFim!=""){
                    $urlDetalhe .= "&DataFim=".$DataFimRetorno ;
                }else{
                    $urlDetalhe .= "&DataFim=".$dataMes[1];
                }

                if($retornoEntrada == 1){
                    $urlDetalhe .= "&retornoEntrada=1";  
                }else{
                    $urlDetalhe .= "&retornoSaida=1"; 
                }
                
                $urlDetalhe .= "&download=1";        
                
                echo 'location.href ="'.$urlDetalhe.'&protDown="+protocolo+"&licDown="+licitacao+"&seqDown="+codigo';
            ?>
    }

    function AbreJanela(url,largura,altura) {
        var new_window = window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
        
    }

    function atualizarPag(){

            <?php
                // montagem do Url para a tela de detalhe do protocolo
                $urlDetalhe = "CadTramitacaoDetalhe.php?protsequ=".$Sequencial."&numprotocolo=".$numprotocolo."&anoprotocolo=".$anoprotocolo;
                $urlDetalhe .= "&orgao=".$orgao;
                $urlDetalhe .= "&objeto=".$objeto;
                $urlDetalhe .= "&numeroci=".$numeroci;
                $urlDetalhe .= "&numeroOficio=".$numeroOficio;
                $urlDetalhe .= "&numeroScc=".$numeroScc;
                $urlDetalhe .= "&proLicitatorio=".$proLicitatorio;
                $urlDetalhe .= "&acao=".$acao;
                $urlDetalhe .= "&origem=".$origem;

                if($DataEntradaIni!=""){
                    $urlDetalhe .= "&DataIni=".$DataIniRetorno ;
                }else{
                    $urlDetalhe .= "&DataIni=".$dataMes[0];
                } 
                if($DataEntradaFim!=""){
                    $urlDetalhe .= "&DataFim=".$DataFimRetorno ;
                }else{
                    $urlDetalhe .= "&DataFim=".$dataMes[1];
                }

                if($retornoEntrada == 1){
                    $urlDetalhe .= "&retornoEntrada=1";  
                }else{
                    $urlDetalhe .= "&retornoSaida=1"; 
                }
                
                          
                
                echo 'location.href ="'.$urlDetalhe.'"';
            ?>

    }


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
      $('#hintAgente').css({'top':e.pageY-80,'left':e.pageX-400, 'padding':'5px', 'font-size': '12px'});
      $('#hintAgente').html(text);
      $('#hintAgente').show();

  }).mouseout(function() {

      $('#hintAgente').hide();

  });
}

var usuariosComissao = <?php echo json_encode($usuariosPorComissao) ?>;
if(usuariosComissao){
  $( ".apresentaHintComissao" ).mouseover(function() {
      var e = e ||  window.event;
      
      text = "Usuários da Comissão:<br>";
      var i;
      for (i = 0; i < usuariosComissao[this.id].length; i++) { 
        if(usuariosComissao[this.id][i] == null){
            text += "Nenhum usuário associado.";
          }else{
            text += "<b> - "+ usuariosComissao[this.id][i] + "</b><br>";
          }
      }

      $('#hintComissao').css({'top':e.pageY-80,'left':e.pageX-450, 'padding':'5px', 'font-size': '12px'});
      $('#hintComissao').html(text);
      $('#hintComissao').show();

  }).mouseout(function() {

      $('#hintComissao').hide();

  });
}

</script>
</html>
