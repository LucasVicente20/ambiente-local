<?php
/**
 * Portal de Compras
 * 
 * Programa: RelGerencialTramitacaoModalidade.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * ---------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     19/06/2019
 * Objetivo: Tarefa Redmine 218203
 * ---------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $botao                  = $_POST['Botao'];
    $codModalidade          = $_POST['codModalidade'];
    $numeroProtocoloAtual   = $_POST['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual      = $_POST['tramitacaoAnoProtocolo'];
    $grupoAtual             = $_POST['tramitacaoGrupo'];
    $grupoDescricao         = $_POST['grupoDescricao'];
    $orgaoAtual             = $_POST['tramitacaoOrgao'];
    $orgaoDescricao         = $_POST['orgaoDescricao'];
    $objetoAtual            = strtoupper2($_POST['tramitacaoObjeto']);
    $numeroCIAtual          = strtoupper2($_POST['tramitacaoNumeroCI']);
    $numeroOficioAtual      = strtoupper2($_POST['tramitacaoNumeroOficio']);
    $numeroSccAtual         = $_POST['tramitacaoNumeroScc'];
    $comissaoLicitacaoAtual = $_POST['tramitacaoComissaoLicitacao'];
    $comissaoDescricao      = $_POST['comissaoDescricao'];
    $acaoAtual              = $_POST['tramitacaoAcao'];
    $acaoDescricao          = $_POST['acaoDescricao'];
    $agenteAtual            = $_POST['tramitacaoAgenteDestino'];
    $agenteDescricao        = $_POST['agenteDescricao'];
    $modalidade             = $_POST['inputModalidade'];
    $processoNumeroAtual    = $_POST['tramitacaoProcessoNumero'];
    $processoAnoAtual       = $_POST['tramitacaoProcessoAno'];
    $dataEntradaInicioAtual = $_POST['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual    = $_POST['tramitacaoDataEntradaFim'];
    $situacaoAtual          = $_POST['tramitacaoSituacao'];
    $ordemAtual             = $_POST['tramitacaoOrdem'];
    $atrasoAtual            = $_POST['tramitacaoAtraso'];
} else {
    $botao                  = $_GET['Botao'];
    $codModalidade          = $_GET['codModalidade'];
    $numeroProtocoloAtual   = $_GET['tramitacaoNumeroProtocolo'];
    $anoProtocoloAtual      = $_GET['tramitacaoAnoProtocolo'];
    $grupoAtual             = $_GET['tramitacaoGrupo'];
    $grupoDescricao         = $_GET['grupoDescricao'];
    $orgaoAtual             = $_GET['tramitacaoOrgao'];
    $orgaoDescricao         = $_GET['orgaoDescricao'];
    $objetoAtual            = strtoupper2($_GET['tramitacaoObjeto']);
    $numeroCIAtual          = strtoupper2($_GET['tramitacaoNumeroCI']);
    $numeroOficioAtual      = strtoupper2($_GET['tramitacaoNumeroOficio']);
    $numeroSccAtual         = $_GET['tramitacaoNumeroScc'];
    $comissaoLicitacaoAtual = $_GET['tramitacaoComissaoLicitacao'];
    $comissaoDescricao      = $_GET['comissaoDescricao'];
    $acaoAtual              = $_GET['tramitacaoAcao'];
    $acaoDescricao          = $_GET['acaoDescricao'];
    $agenteAtual            = $_GET['tramitacaoAgenteDestino'];
    $agenteDescricao        = $_GET['agenteDescricao'];    
    $modalidade             = $_GET['inputModalidade'];
    $processoNumeroAtual    = $_GET['tramitacaoProcessoNumero'];
    $processoAnoAtual       = $_GET['tramitacaoProcessoAno'];
    $dataEntradaInicioAtual = $_GET['tramitacaoDataEntradaInicio'];
    $dataEntradaFimAtual    = $_GET['tramitacaoDataEntradaFim'];
    $situacaoAtual          = $_GET['tramitacaoSituacao'];
    $ordemAtual             = $_GET['tramitacaoOrdem'];
    $atrasoAtual            = $_GET['tramitacaoAtraso'];

    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];

    unset($_SESSION['Arquivos_Upload']);
    unset($_SESSION['sccTramitacao']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelGerencialTramitacao.php";

$parametrosGerais = dadosParametrosGerais();
$tamanhoObjeto           = $parametrosGerais[0];
$tamanhoJustificativa    = $parametrosGerais[1];
$tamanhoDescricaoServico = strlen($parametrosGerais[2]);
$subElementosEspeciais   = explode(',', $parametrosGerais[3]);
$tamanhoArquivo          = $parametrosGerais[4];
$tamanhoNomeArquivo      = $parametrosGerais[5];
$extensoesArquivo        = $parametrosGerais[6];

if ($botao == '' || $botao == 'Planilha' || $codModalidade && $botao != 'Imprimir') {
    $validar = true;
    $protocolo = array();

    $buscar = array(
        'grupo'          => $grupoAtual,
        'grupoDesc'      => $grupoDescricao,
        'orgao'          => $orgaoAtual,
        'orgaoDesc'      => $orgaoDescricao,
        'objeto'         => $objetoAtual,
        'numeroCI'       => $numeroCIAtual,
        'numeroOficio'   => $numeroOficioAtual,
        'numeroScc'      => $numeroSccAtual,
        'comissao'       => $comissaoLicitacaoAtual,
        'comissaoDesc'   => $comissaoDescricao,
        'processoNumero' => $processoNumeroAtual,
        'processoAno'    => $processoAnoAtual,
        'dataInicio'     => $dataEntradaInicioAtual,
        'dataFim'        => $dataEntradaFimAtual,
        'situacao'       => $situacaoAtual,
        'acao'           => $acaoAtual,
        'acaoDesc'       => $acaoDescricao,
        'agente'         => $agenteAtual,
        'agenteDesc'     => $agenteDescricao,
        'ordem'          => $ordemAtual,
        'atraso'         => $atrasoAtual,
        'protocolo'      => $numeroProtocoloAtual,
        'anoProtocolo'   => $anoProtocoloAtual,
        'codmodalidade'  => $codModalidade,
    );

    if ($validar) {
        $dados = relatorioGerencialTramitacao($buscar, 'relMonitoramento');
        $arrComissoes = getComissoesRelGerencialTramitacao($buscar, '');
        $arrAcoes = getAcoesRelGerencial(null, $buscar);
        
        $htmlAcoesTit = '';
        $totalAcoes = 4;

        foreach ($arrAcoes as $objAcao) {
            $htmlAcoesTit .= '<td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">'.$objAcao[0].'</td>';
            $totalAcoes++;
        }

        // Adicionar último passo de cada protocolo
        if (!empty($dados)) {
            $_REQUEST['rotina'] = 'Monitoramento';
            $_SESSION['origemPesquisa'] = $_REQUEST;
        }
    }
    
    if ($botao == 'Planilha') {
        $dadosExtras = array('codModalidade' => $codModalidade);
        gerarCsvRelGeralTramitacao($dados, $arrComissoes, $arrAcoes, 'COMISSÃO', $dadosExtras);
    }
} elseif ($botao == 'Imprimir') {
    $validar = true;
    $buscar = array(
        'grupo'          => $grupoAtual,
        'grupoDesc'      => $grupoDescricao,
        'orgao'          => $orgaoAtual,
        'orgaoDesc'      => $orgaoDescricao,
        'objeto'         => $objetoAtual,
        'numeroCI'       => $numeroCIAtual,
        'numeroOficio'   => $numeroOficioAtual,
        'numeroScc'      => $numeroSccAtual,
        'comissao'       => $comissaoLicitacaoAtual,
        'comissaoDesc'   => $comissaoDescricao,
        'processoNumero' => $processoNumeroAtual,
        'processoAno'    => $processoAnoAtual,
        'dataInicio'     => $dataEntradaInicioAtual,
        'dataFim'        => $dataEntradaFimAtual,
        'situacao'       => $situacaoAtual,
        'acao'           => $acaoAtual,
        'acaoDesc'       => $acaoDescricao,
        'agente'         => $agenteAtual,
        'agenteDesc'     => $agenteDescricao,
        'ordem'          => $ordemAtual,
        'atraso'         => $atrasoAtual,
        'protocolo'      => $numeroProtocoloAtual,
        'anoProtocolo'   => $anoProtocoloAtual,
        'modalidade'     => $codModalidade,
        'relatorio'      => 'inicial'
    );
    
    if ($validar) {
        $_SESSION['buscar'] = $buscar;
        header('Location: RelGerencialTramitacaoModalidadePdf.php');
        exit();
    }
} elseif ($botao == 'Limpar') {
    header('Location: RelGerencialTramitacao.php');
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
        function enviaForm(comissao){
            document.RelTramitacaoGerencial.action = 'RelGerencialTramitacaoComissao.php';
            document.RelTramitacaoGerencial.codComissao.value = comissao;  
            document.RelTramitacaoGerencial.Botao.value = '';
            urldata = $('#RelTramitacaoGerencial').serialize();    
            //document.RelTramitacaoGerencial.submit();
            window.location.href = "RelGerencialTramitacaoComissao.php?"+urldata; 
        }

        function voltar(){
            document.RelTramitacaoGerencial.action = 'RelGerencialTramitacao.php';
            document.RelTramitacaoGerencial.Botao.value = 'Pesquisar';
            document.RelTramitacaoGerencial.submit();
        }

        function enviar(valor){
            preencherDados();
            document.RelTramitacaoGerencial.Botao.value=valor;
            document.RelTramitacaoGerencial.submit();
        }

        function preencherDados(){
            $('#grupoDescricao').val($('#inputGrupo option:selected').text());
            $('#orgaoDescricao').val($('#inputOrgao option:selected').text());
            $('#comissaoDescricao').val($('#inputComissao option:selected').text());
            $('#acaoDescricao').val($('#inputAcao option:selected').text());
            $('#agenteDescricao').val($('#inputAgenteDestino option:selected').text());
        }

        function CaracteresObjeto(text,campo){
            input = document.getElementById(campo);
            input.value = text.value.length;
        }

        function AbreJanela(url,largura,altura) {
            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
        }

        $(document).ready(function() {
            $(".capturarValorAcaoGrupo").change(function() {
                var acao  = $(this).attr('data-acao');
                document.RelTramitacaoGerencial.Botao.value = acao;
                document.RelTramitacaoGerencial.submit();
            });

            //$('#numeroAno').mask('9999/9999');  
            $('#numeroScc').mask('9999.9999/9999');  
            $('#inputEntradaInicio').mask('99/99/9999');
            $('#inputEntradaFim').mask('99/99/9999');
        });
    </script>
    <script language="JavaScript">Init();</script>
    <form action="RelGerencialTramitacaoModalidade.php" method="POST" id="RelTramitacaoGerencial" name="RelTramitacaoGerencial" enctype="multipart/form-data" >
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Tramitação > Relatórios > Relatório Gerencial de Tramitação
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
            <?php
            if ($Mens == 1) {
                ?>
                <tr>
                    <td width="150"></td>
                    <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
                </tr>
                <?php
            }
            ?>
            <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="150"></td>
                <td class="textonormal" >
                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                        <tr>
                            <td align="center" colspan="<?php echo $totalAcoes ?>" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                MODALIDADE: <?php echo $dados[0][23] ; ?>
                            </td>
                        </tr>
                        <?php
                        if (!empty($arrComissoes)) { 
                            echo '
                                <tr>
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">COMISSÃO</td>
                                    <td colspan="'.($totalAcoes-1).'" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">MÉDIA EM DIAS POR AÇÃO</td>
                                </tr>
                                <tr>';
                                    echo $htmlAcoesTit;
                                    echo '  <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>PREVISTO</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>REALIZADO</td>
                                            <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">ATRASO</td>
                                </tr>';

                                foreach ($arrComissoes as $value) { 
                                    $mediaRealizado = 0;
                                    $mediaPrevisto = 0;
                                    $htmlAcoesDados = '';

                                    foreach ($arrAcoes as $objAcao) {
                                        $arrMedia = getMediaDiasAcaoComissao($objAcao[1], $codModalidade ,$value[1], $dados);

                                        if (!is_int($arrMedia[0])) {
                                            $media = (int)$arrMedia[0];
                                        } else {
                                            $media = $arrMedia[0];
                                        }
    
                                        $mediaRealizado = $mediaRealizado + $arrMedia[0];
                                        $mediaPrevisto = $mediaPrevisto + $arrMedia[1];

                                        if ($media <= 0) {
                                            $media = '-';
                                        }

                                        $htmlAcoesDados .= '<td align="center">'.$media.'</td>';
                                    }

                                    if (($mediaRealizado+$mediaPrevisto) == 0) {
                                        continue;
                                    }
                                    ?>
                                    <tr>    
                                        <td align="center"><a href="javascript: enviaForm(<?php echo $value[1] ?>)"><?php echo $value[0]; //modalidade ?></a></td>
                                        <?php echo $htmlAcoesDados; ?>
                                        <td align="center">
                                            <?php 
                                            if(!is_int($mediaPrevisto)){
                                                echo (int)$mediaPrevisto;
                                            } else {
                                                echo $mediaPrevisto ;
                                            }
                                            ?>
                                        </td>
                                        <td align="center">
                                            <?php 
                                            if(!is_int($mediaRealizado)){
                                                echo (int)$mediaRealizado;
                                            } else {
                                                echo $mediaRealizado ;
                                            }
                                            ?>
                                        </td>
                                        <td align="center">
                                            <?php 
                                            $atraso = $mediaRealizado - $mediaPrevisto;

                                            if ($atraso > 0){
                                                if (!is_int($atraso)) {
                                                    echo (int)$atraso;
                                                } else {
                                                    echo $atraso ;
                                                }
                                            } else {
                                                echo '0';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="<?php echo $totalAcoes ?>" class="textonormal" align="right">
                                        <input type="button" value="Gerar Planilha" onclick="javascript:enviar('Planilha');" class="botao">
                                        <input type="button" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao">
                                        <input type="button" value="Voltar" onclick="javascript:voltar();" class="botao">
                                        <input type="hidden" name="codModalidade"  value="<?php echo $codModalidade ?>">
                                        <input type="hidden" name="codComissao"  value="">
                                        <input type="hidden" name="tramitacaoOrgao"  value="<?php echo $orgaoAtual?>">   
                                        <input type="hidden" name="tramitacaoGrupo"  value="<?php echo $grupoAtual?>">
                                        <input type="hidden" name="inputModalidade"  value="<?php echo $modalidade?>"> 
                                        <input type="hidden" name="tramitacaoComissaoLicitacao"  value="<?php echo $comissaoLicitacaoAtual?>"> 
                                        <input type="hidden" name="inputProcesso"  value="<?php echo $processoNumeroAtual?>">
                                        <input type="hidden" name="tramitacaoDataEntradaInicio"  value="<?php echo $dataEntradaInicioAtual?>"> 
                                        <input type="hidden" name="tramitacaoDataEntradaFim"  value="<?php echo $dataEntradaFimAtual?>"> 
                                        <input type="hidden" name="tramitacaoAcao"  value="<?php echo $acaoAtual?>"> 
                                        <input type="hidden" name="tramitacaoSituacao"  value="<?php echo $situacaoAtual?>"> 
                                        <input type="hidden" name="Botao" value="" />
                                    </td>
                                </tr>
                            </td>
                        </tr>
                        <?php
                        } elseif(empty($dados) && $botao == 'Pesquisar') {
                            ?>
                            <tr>
                                <td>
                                    Nenhuma Ocorrência Encontrada
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>            
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>
</body>
</html>