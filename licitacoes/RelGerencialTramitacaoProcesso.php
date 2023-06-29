<?php
/**
 * Portal de Compras
 * 
 * Programa: RelGerencialTramitacaoProcesso.php
 * Autor:    Pitang Agile TI - Ernesto Ferreira
 * ----------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     19/06/2019
 * Objetivo: Tarefa Redmine 218203
 * ----------------------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     15/01/2021
 * Objetivo: Tarefa Redmine 223277
 * ----------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
    $botao                  = $_POST['Botao'];
    $codModalidade          = $_POST['codModalidade'];
    $codComissao            = $_POST['codComissao'];
    $codProcesso            = $_POST['codProcesso'];
    $anoProcesso            = $_POST['anoProcesso'];
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
    $codComissao            = $_GET['codComissao'];
    $codProcesso            = $_GET['codProcesso'];
    $anoProcesso            = $_GET['anoProcesso'];
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

if ($botao == 'Pesquisar' || $botao == 'Planilha' || $codComissao && $botao != 'Imprimir') {
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
        'codcomissao'    => $codComissao
    );

    if ($validar) {
        $dados        = relatorioGerencialTramitacao($buscar, 'relMonitoramento');
        $arrProcessos = getProcessosRelGerencialTramitacao($buscar, '');
        $arrAcoes     = getAcoesRelGerencial(null, $buscar);
        
        $htmlAcoesTit = '';
        $totalAcoes = 6;

        // Adicionar último passo de cada protocolo
        if (!empty($dados)) {
            $_REQUEST['rotina'] = 'Monitoramento';
            $_SESSION['origemPesquisa'] = $_REQUEST;
        }
    }

    if ($botao == 'Planilha') {
        $dadosExtras = array(
            'codModalidade'          => $codModalidade,
            'codComissao'            => $codComissao,
            'codProcesso'            => $codProcesso,
            'anoProcesso'            => $anoProcesso
        );
        gerarCsvRelGeralTramitacaoProcesso($dados, $arrProcessos, $arrAcoes,'AÇÃO', $dadosExtras);
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
        'codmodalidade'  => $codModalidade,
        'codcomissao'    => $codComissao,
        'codprocesso'    => $codProcesso,
        'anoprocesso'    => $anoProcesso,
        'relatorio'      => 'processo'
    );

    if ($validar) {
        $_SESSION['buscar'] = $buscar;
        header('Location: RelGerencialTramitacaoProcessoPdf.php');
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

        function enviaForm(codProcesso, anoProcesso) {
            document.RelTramitacaoGerencial.Botao.value = '';
        }

        function voltar() {
            document.RelTramitacaoGerencial.action = 'RelGerencialTramitacaoComissao.php';
            document.RelTramitacaoGerencial.Botao.value = 'Pesquisar';
            document.RelTramitacaoGerencial.submit();
        }

        function enviar(valor) {
            preencherDados();
            document.RelTramitacaoGerencial.Botao.value=valor;
            document.RelTramitacaoGerencial.submit();
        }

        function preencherDados() {
            $('#grupoDescricao').val($('#inputGrupo option:selected').text());
            $('#orgaoDescricao').val($('#inputOrgao option:selected').text());
            $('#comissaoDescricao').val($('#inputComissao option:selected').text());
            $('#acaoDescricao').val($('#inputAcao option:selected').text());
            $('#agenteDescricao').val($('#inputAgenteDestino option:selected').text());
        }

        function CaracteresObjeto(text,campo) {
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

            $('#numeroScc').mask('9999.9999/9999');  
            $('#inputEntradaInicio').mask('99/99/9999');
            $('#inputEntradaFim').mask('99/99/9999');
        });
    </script>
    <script language="JavaScript">Init();</script>
    <form action="RelGerencialTramitacaoProcesso.php" method="POST" name="RelTramitacaoGerencial" enctype="multipart/form-data" >
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
                                COMISSÃO: <?php echo $dados[0][16]; ?>
                                <br>
                                PROCESSO: <?php echo str_pad($dados[0][17], 4, "0", STR_PAD_LEFT)."/".$dados[0][18]; ?>
                                <br>
                            </td>
                        </tr>
                        <?php
                        if (!empty($dados)) {
                            echo '
                                <tr>
                                    <td rowspan="2" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">AÇÃO</td>
                                    <td colspan="'.$totalAcoes.'" class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">MÉDIA EM DIAS POR AÇÃO</td>
                                </tr>
                                <tr>';
                            echo '  
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">AGENTE</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">USUÁRIO RESPONSÁVEL</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>PREVISTO<br>DIAS</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">PRAZO<br>REALIZADO<br>DIAS</td>
                                    <td class="titulo3" bgcolor="#DCEDF7" class="textonormal" align="center">ATRASO</td>
                                </tr>';

                            $mediaRealizado = 0;
                            $mediaPrevisto = 0;
        
                            foreach ($arrAcoes as $objAcao) {
                                $arrMedia = getMediaDiasAcaoProcessoDetalhes($objAcao[1], $codModalidade , $codComissao, $codProcesso, $anoProcesso, $dados);

                                if ($arrMedia) {
                                    $mediaRealizado = $mediaRealizado + $arrMedia[3];
                                    $mediaPrevisto = $mediaPrevisto + $arrMedia[2];

                                    echo '<tr>';                                       
                                    ?>
                                    <td><?php echo $objAcao[0] ?></td>
                                    <td class='apresentaHintAgente' id ='<?php echo $arrMedia[4]; ?>'><?php echo $arrMedia[0] ?></td>
                                    <td><?php echo $arrMedia[1] ?></td>
                                    <td align="center">
                                        <?php
                                        if (!is_int($arrMedia[2])) {
                                            echo number_format($arrMedia[2], 2, ',', '');
                                        } else {
                                            echo $arrMedia[2];
                                        }         
                                        ?>
                                    </td>
                                    <td align="center">
                                        <?php
                                        if (!is_int($arrMedia[3])) {
                                            echo number_format($arrMedia[3], 2, ',', '');
                                        } else {
                                            echo $arrMedia[3];
                                        }
                                        ?>
                                    </td>
                                    <td align="center">
                                        <?php 
                                        $atraso = $arrMedia[3] - $arrMedia[2];

                                        if ($atraso > 0) {
                                            if (!is_int($atraso)) {
                                                echo number_format($atraso, 2, ',', '');
                                            } else {
                                                echo $atraso ;
                                            }
                                        } else {
                                            echo '0';
                                        }
                                        ?>
                                    </td>
                            <?php
                            } 
                        }
                        ?>
                        <tr>
                            <td align="right" colspan="<?php echo ($totalAcoes-3) ?>">TOTAL</td>
                            <td align="center">
                                <?php
                                if (!is_int($mediaPrevisto)) {
                                    echo number_format($mediaPrevisto, 2, ',', '');
                                } else {
                                    echo $mediaPrevisto;
                                }
                                ?>
                            </td>
                            <td align="center">
                                <?php  
                                if (!is_int($mediaRealizado)) {
                                    echo number_format($mediaRealizado, 2, ',', '');
                                } else {
                                    echo $mediaRealizado;
                                }
                                ?>
                            </td>
                            <td align="center">
                                <?php
                                $atraso = $mediaRealizado - $mediaPrevisto;

                                if ($atraso > 0) {
                                    if (!is_int($atraso)) {
                                        echo number_format($atraso, 2, ',', '');
                                    } else {
                                        echo $atraso ;
                                    }
                                } else {
                                    echo '0';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="<?php echo $totalAcoes ?>" class="textonormal" align="right">
                                <input type="button" value="Gerar Planilha" onclick="javascript:enviar('Planilha');" class="botao">
                                <input type="button" value="Imprimir" onclick="javascript:enviar('Imprimir');" class="botao">
                                <input type="button" value="Voltar" onclick="javascript:voltar();" class="botao">
                                <input type="hidden" name="codModalidade"  value="<?php echo $codModalidade ?>">
                                <input type="hidden" name="codComissao" value="<?php echo $codComissao ?>">
                                <input type="hidden" name="codProcesso" value="<?php echo $codProcesso ?>">
                                <input type="hidden" name="anoProcesso" value="<?php echo $anoProcesso ?>">
                                <input type="hidden" name="tramitacaoOrgao"  value="<?php echo $orgaoAtual?>">   
                                <input type="hidden" name="tramitacaoGrupo"  value="<?php echo $grupoAtual?>">
                                <input type="hidden" name="inputModalidade"  value="<?php echo $modalidade?>"> 
                                <input type="hidden" name="tramitacaoComissaoLicitacao"  value="<?php echo $comissaoLicitacaoAtual?>"> 
                                <input type="hidden" name="inputProcesso"  value="<?php echo $processoNumeroAtual?>">
                                <input type="hidden" name="tramitacaoDataEntradaInicio"  value="<?php echo $dataEntradaInicioAtual?>"> 
                                <input type="hidden" name="tramitacaoDataEntradaFim"  value="<?php echo $dataEntradaFimAtual?>"> 
                                <input type="hidden" name="tramitacaoAcao"  value="<?php echo $acaoAtual?>"> 
                                <input type="hidden" name="tramitacaoSituacao"  value="<?php echo $situacaoAtual?>"> 
                                <input type="hidden" name="tramitacaoAtraso"  value="<?php echo $atrasoAtual?>"> 
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
        <div id='hintAgente' class='hint textonormal' style='display: none;'>Usuários do Agente:</div>
    </form>
    <?php 
    $usuariosAgentes = getUsuariosAgentes(Conexao());
    $usuariosPorAgente = array();

    foreach ($usuariosAgentes as $usuario) {
        $usuariosPorAgente[$usuario[0]][] = $usuario[2];
    }
    ?>
    <script language="javascript" type="">
        var usuariosAgentes = <?php echo json_encode($usuariosPorAgente) ?>;
        if (usuariosAgentes) {
            $( ".apresentaHintAgente" ).mouseover(function() {
                var e = e ||  window.event;
                text = "Usuários do Agente:<br>";
                var i;

                for (i = 0; i < usuariosAgentes[this.id].length; i++) { 
                    if (usuariosAgentes[this.id][i] == null) {
                        text += "Nenhum usuário associado.";
                    } else {
                        text += "<b> - "+ usuariosAgentes[this.id][i] + "</b><br>";
                    }
                }

                $('#hintAgente').css({'top':e.pageY-80,'left':e.pageX-100, 'padding':'5px', 'font-size': '12px'});
                $('#hintAgente').html(text);
                $('#hintAgente').show();
            }).mouseout(function() {
                $('#hintAgente').hide();
            });
        }
    </script>
</body>
</html>