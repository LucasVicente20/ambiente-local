<?php
/**
 * Portal de Compras
 * 
 * Programa: TabTramitacaoAgenteManter.php
 * Autor:    Pitang Agile TI - Caio Coutinho
 * Data:     23/07/2018
 * Objetivo: Tarefa Redmine 199103
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     10/08/2018
 * Objetivo: Tarefa Redmine 200550
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/03/2019
 * Objetivo: Tarefa Redmine 213437
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/05/2019
 * Objetivo: Tarefa Redmine 216899
 * -----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 217242
 * -----------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "./funcoesTramitacao.php";
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

$db = Conexao();

# Grupos
$grupos = getGrupos($db);

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
    $botao            = $_POST['Botao'];
    $agenteAtual      = $_POST['agenteAtual'];
    $descricaoAtual   = $_POST['agenteDescricao'];
    $externoAtual     = $_POST['agenteExterno'];
    $situacaoAtual    = $_POST['agenteSituacao'];
    $inicialAtual     = $_POST['agenteInicial'];
    $comissaoLicAtual = $_POST['comissaoLicInicial'];
    $agenteAlt        = $_POST['agenteAlt'];
    $grupoAtual       = !empty($_POST['agenteGrupo']) ? $_POST['agenteGrupo'] : $_SESSION['_cgrempcodi_'];
    $usuariosAtual    = !empty($_POST['agenteUsuarios']) ? $_POST['agenteUsuarios'] : array();
} else {
    $Mens        = $_GET['Mens'];
    $Tipo        = $_GET['Tipo'];
    $Mens        = $_GET['Mens'];
    $Mensagem    = $_GET['Mensagem'];
    $agenteAtual = $_GET['agente'];

    $agente = getAgenteById($db, $agenteAtual);

    if (!empty($agente)) {
        $descricaoAtual   = $agente[2];
        $externoAtual     = $agente[3];
        $situacaoAtual    = $agente[6];
        $grupoAtual       = $agente[1];
        $usuariosAtual    = $agente['usuarios'];
        $inicialAtual     = $agente[5];
        $comissaoLicAtual = $agente[7];
        $agenteAlt        = $agente[8];
    } else {
        $Url = 'TabTramitacaoAgentePesquisar.php';
        header("location: " . $Url);
        exit();
    }
}

# Usuários
$users = getUsuarios($db, $grupoAtual);

if ($botao == 'SelecionarGrupo') {
    if ($grupoAtual != '') {        
        $users = getUsuarios($db, $grupoAtual);
    } else {
        $grupoAtual = $_SESSION['_cgrempcodi_'];
    }


} elseif ($botao == 'Manter') {
    $validar      = true;
    $countInicial = 0;

    if ($descricaoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputDescricao\").focus();' class='titulo2'>Descrição</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    }

    if ($grupoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputGrupo\").focus();' class='titulo2'>Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    } else {
        $sqlountInicial = "SELECT COUNT(*) FROM SFPC.TBTRAMITACAOAGENTE WHERE CTAGENSEQU <> " . $agenteAtual . " AND CGREMPCODI = ".$grupoAtual." AND FTAGENINIC = 'S'";

        $countInicial = resultValorUnico(executarTransacao($db, $sqlountInicial));
    }

    if ($inicialAtual == 'S' && $countInicial > 0) {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputInicial\").focus();' class='titulo2'>Agente Inicial</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    }

    if (empty($usuariosAtual)) {
        adicionarMensagem("Usuários Responsáveis", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    }

    if ($agenteAlt == 'S') {
        $sqlAgente = "SELECT COUNT(*) FROM SFPC.TBTRAMITACAOAGENTE WHERE CTAGENSEQU <> " . $agenteAtual . " AND CGREMPCODI = " . $grupoAtual . " AND FTAGENALTE = 'S' ";

        $countAgente = resultValorUnico(executarTransacao($db, $sqlAgente));

        if ($countAgente > 0) {
            adicionarMensagem("Este agente já possui agente alternativo", $GLOBALS['TIPO_MENSAGEM_ERRO']);

            $validar = false;
        }
    }

    if ($validar) {
        $tipo = $externoAtual == 'S' ? 'E' : 'I';

        $sqlUpdate  = "UPDATE   SFPC.TBTRAMITACAOAGENTE ";
        $sqlUpdate .= "SET      CGREMPCODI = " . $grupoAtual . ", ";
        $sqlUpdate .= "         ETAGENDESC = '" . $descricaoAtual . "' , "; 
        $sqlUpdate .= "         FTAGENTIPO = '" . $tipo . "', ";
        $sqlUpdate .= "         CUSUPOCODI = " . $_SESSION['_cusupocodi_']. ", ";
        $sqlUpdate .= "         TTAGENULAT = '" . date('Y-m-d h:i:s') . "', ";
        $sqlUpdate .= "         FTAGENCOMIS = '" . $comissaoLicAtual . "', ";
        $sqlUpdate .= "         FTAGENINIC = '" . $inicialAtual . "', ";
        $sqlUpdate .= "         FTAGENALTE = '" . $agenteAlt . "'";
        $sqlUpdate .= "WHERE    CTAGENSEQU = " . $agenteAtual;

        executarTransacao($db, $sqlUpdate);

        // Delete agentes usuarios
        $sqlDelete = "DELETE FROM SFPC.TBTRAMITACAOAGENTEUSUARIO WHERE CTAGENSEQU = " . $agenteAtual;

        executarTransacao($db, $sqlDelete);

        foreach ($usuariosAtual as $key => $value) {
            $sql  = "INSERT INTO SFPC.TBTRAMITACAOAGENTEUSUARIO ( ";
            $sql .= "CTAGENSEQU, CUSUPOCODI, CUSUPOCOD1, TAGENUULAT ";
            $sql .= ") VALUES ( ";
            $sql .= $agenteAtual . ", " . $value . ", " . $_SESSION['_cusupocodi_'] . ", '" . date('Y-m-d h:i:s') ."') ";

            executarTransacao($db, $sql);
        }

        finalizarTransacao($db);

        $Mensagem = "Agentes Atualizados com sucesso";

        header('Location: TabTramitacaoAgentePesquisar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    }
} else if($botao == 'Excluir') {
    $numTramitacoes = count(verificaTramitacaoAgente($agenteAtual));

    if ($numTramitacoes <= 0) {
        // Delete agentes usuarios
        $sqlDelete = "DELETE FROM SFPC.TBTRAMITACAOAGENTEUSUARIO WHERE CTAGENSEQU = " . $agenteAtual;

        executarTransacao($db, $sqlDelete);

        // Delete agentes
        $sqlDelete = "DELETE FROM SFPC.TBTRAMITACAOAGENTE WHERE CTAGENSEQU = " . $agenteAtual;

        executarTransacao($db, $sqlDelete);
        finalizarTransacao($db);

        $Mensagem = "Agentes excluído com Sucesso";

        header('Location: TabTramitacaoAgentePesquisar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    } else {
        if ($GLOBALS['Mens'] == 1) {
            $GLOBALS['Mensagem'] .= ", ";
        }

        $GLOBALS['Mens']      = 1;
        $GLOBALS['Tipo']      = $GLOBALS['TIPO_MENSAGEM_ERRO'];
        $GLOBALS['Mensagem'] .= "Exclusão cancelada!<br> Agente relacionado com ($numTramitacoes) tramitação(ções)";
    }
} elseif ($botao == 'Voltar') {
    header('Location: TabTramitacaoAgentePesquisar.php');
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
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script language="JavaScript" src="../jquery.select-list-actions.js"></script>
    <script language="javascript" type="">
        <?php MenuAcesso(); ?>

        function enviar(valor) {
            if (valor == 'Excluir') {
                confirmar = confirm('Deseja excluir este agente?');
                
                if (!confirmar) {
                    return false;
                }
            }

            document.TabTramitacaoAgenteManter.Botao.value=valor;
            document.TabTramitacaoAgenteManter.submit();
        }

        function CaracteresObjeto(text,campo) {
            campo.value = text.value.length;
        }

        jQuery(document).ready(function(){
            jQuery(".capturarValorAcaoGrupo").change(function() {
                var acao = $(this).attr('data-acao');

                document.TabTramitacaoAgenteManter.Botao.value = acao;
                document.TabTramitacaoAgenteManter.submit();
            });
            
            jQuery('#btnRight').click(function(e) {
                jQuery('select').moveToListAndDelete('#sourceListId', '#destinationListId');
                e.preventDefault();
            });

            jQuery('#btnLeft').click(function(e) {
                jQuery('select').moveToListAndDelete('#destinationListId', '#sourceListId');
                e.preventDefault();
            });
        });
    </script>
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabTramitacaoAgenteManter.php" method="post" name="TabTramitacaoAgenteManter">
        <input type="hidden" name="agenteAtual" value="<?php echo $agenteAtual?>" />
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
                    > Tabelas > Licitações > Tramitação > Agente > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
            <?php
            if ($Mens == 1) {
                ?>
                <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2">
                        <?php ExibeMens($Mensagem,$Tipo,1); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
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
                                            <p align="justify">Para alterar um agente, informe os dados abaixo e clique no botão "Alterar".  Os itens obrigatórios estão com *.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Descrição </td>
                                                    <td class="textonormal">
                                                        <textarea id="inputDescricao" name="agenteDescricao" cols="50" rows="4" onkeyup="javascript:CaracteresObjeto(this,TabTramitacaoAgenteManter.NCaracteresObjeto)" onblur="javascript:CaracteresObjeto(this,TabTramitacaoAgenteManter.NCaracteresObjeto)" onselect="javascript:CaracteresObjeto(this,TabTramitacaoAgenteManter.NCaracteresObjeto)" class="textonormal"><?php echo (!empty($descricaoAtual)) ? $descricaoAtual : ''; ?></textarea>
                                                        <br/>
                                                        <font class="textonormal">máximo de 200 caracteres</font>
                                                        <input type="text" id="NCaracteresObjeto" name="NCaracteresObjeto" readonly="" size="3" value="0" class="textonormal"><br>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Externo*</td>
                                                    <td class="textonormal">
                                                        <select name="agenteExterno" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($externoAtual == 'I') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($externoAtual == 'E') ? 'selected' : ''; ?> value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                                    <td class="textonormal">
                                                        <select name="agenteSituacao" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($situacaoAtual == 'A') ? 'selected' : ''; ?> value="A">ATIVO</option> 
                                                            <option <?php echo ($situacaoAtual == 'I') ? 'selected' : ''; ?> value="I">INATIVO</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
                                                    <td class="textonormal">
                                                        <select id="inputGrupo" name="agenteGrupo" data-acao="SelecionarGrupo" class="textonormal capturarValorAcaoGrupo">
                                                            <option value="">Selecione um grupo...</option>
                                                            <?php
                                                            # Mostra os grupos #
                                                            foreach ($grupos as $key => $value) {
                                                                ?>
                                                                <option <?php echo ($grupoAtual == $key) ? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Agente Inicial</td>
                                                    <td class="textonormal">
                                                        <select id="inputInicial" name="agenteInicial" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($inicialAtual == 'N') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($inicialAtual == 'S') ? 'selected' : ''; ?> value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação</td>
                                                    <td class="textonormal">
                                                        <select id="inputInicial" name="comissaoLicInicial" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($comissaoLicAtual == 'N') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($comissaoLicAtual == 'S') ? 'selected' : ''; ?> value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Agente alternativo ao agente externo<br/>(utilizado em Incluir Especial e Manter Especial)</td>
                                                    <td class="textonormal">
                                                        <select id="inputInicial" name="agenteAlt" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($agenteAlt == 'N') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($agenteAlt == 'S') ? 'selected' : ''; ?> value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Usuários Responsáveis</td>
                                                    <td class="textonormal">
                                                        <div style="float:left; display:block; margin-top:5px">
                                                            <label style="float:left">Usuários ainda não selecionados</label>
                                                            <select id="sourceListId" multiple name="Orgaos_1[]" class="textonormal" size="10" style="width: 235px; float: left; clear:both">
                                                                <?php  # Mostra os grupos #
                                                                foreach ($users as $key => $value) {
                                                                    if (!in_array($key, $usuariosAtual)) {
                                                                        ?>
                                                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                        <div style="float: left; display:block; height:100%; margin: 0 20px">
                                                            <br /> <br /> <br /> <br />
                                                            <input type='button' id='btnRight' value='>' class="btn btn-default" />
                                                            <br /> <br />
                                                            <input type='button' id='btnLeft' value='<' class="btn btn-default" />
                                                            <br /> <br /> <br /> <br />
                                                        </div>
                                                        <div style="float:left; display:block; margin-top:5px">
                                                            <label style="float:left">Usuários selecionados</label>
                                                            <select id="destinationListId" multiple name="agenteUsuarios[]" class="textonormal" size="10" style="width: 235px; float: left; clear:both">
                                                                <?php
                                                                # Mostra os grupos #
                                                                foreach ($users as $key => $value) {
                                                                    if (in_array($key, $usuariosAtual)) {
                                                                        ?>
                                                                        <option selected value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" align="right">
                                            <input type="button" value="Manter" class="botao" onclick="javascript:enviar('Manter');">
                                            <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
                                            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
                                            <input type="hidden" name="Botao" value="" />
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
    <script>
        CaracteresObjeto(document.getElementById('inputDescricao'),TabTramitacaoAgenteManter.NCaracteresObjeto);
        //-->
    </script>
</body>
</html>