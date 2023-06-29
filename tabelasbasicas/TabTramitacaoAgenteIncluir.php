<?php
/**
 * Portal de Compras
 * 
 * Programa: TabTramitacaoAgenteIncluir.php
 * Autor:    Pitang Agile TI - Caio Coutinho
 * Data:     23/07/2018
 * Objetivo: Tarefa Redmine 199103
 * --------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     10/08/2018
 * Objetivo: Tarefa Redmine 200550
 * --------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/03/2019
 * Objetivo: Tarefa Redmine 213437
 * --------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 217242
 * --------------------------------------------------------------------------------------
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

# Usuários
$users = getUsuarios($db);

$usuariosAtual = array();
# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD']  == "POST") {
    $botao            = $_POST['Botao'];
    $descricaoAtual   = $_POST['agenteDescricao'];
    $externoAtual     = $_POST['agenteExterno'];
    $situacaoAtual    = $_POST['agenteSituacao'];
    $grupoAtual       = $_POST['agenteGrupo'];
    $inicialAtual     = $_POST['agenteInicial'];
    $comissaoLicAtual = $_POST['comissaoLicInicial'];
    $agenteAlt        = $_POST['agenteAlt'];
    $usuariosAtual    = !empty($_POST['agenteUsuarios']) ? $_POST['agenteUsuarios'] : array();
} else {
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
    $Mens     = $_GET['Mens'];
    $Mensagem = $_GET['Mensagem'];
}

if ($botao == 'SelecionarGrupo') {
    if ($grupoAtual != '') {
        $users = getUsuarios($db, $grupoAtual);
    }
} elseif ($botao == 'Incluir') {
    $validar = true;
    $countInicial = 0;

    if ($descricaoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputDescricao\").focus();' class='titulo2'>Descrição</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    }

    if ($grupoAtual == '') {
        adicionarMensagem("<a href='javascript:document.getElementById(\"inputGrupo\").focus();' class='titulo2'>Grupo</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

        $validar = false;
    } else {
        $sqlountInicial = "SELECT COUNT(*) FROM SFPC.TBTRAMITACAOAGENTE WHERE CGREMPCODI = ".$grupoAtual." AND FTAGENINIC = 'S' ";

        $countInicial = resultValorUnico(executarTransacao($db, $sqlountInicial));
    }

    if ($inicialAtual == 'S' && $countInicial > 0) {
        if ($GLOBALS['Mens'] == 1) {
            $GLOBALS['Mensagem'] .= ", ";
        }

        $GLOBALS['Mens']      = 1;
        $GLOBALS['Tipo']      = $GLOBALS['TIPO_MENSAGEM_ERRO'];
        $GLOBALS['Mensagem'] .= "<a href='javascript:document.getElementById(\"inputInicial\").focus();' class='titulo2'>Já existe agente inicial cadastrado para este grupo</a>";

        $validar = false;
    }

    if ($agenteAlt == 'S') {
        $sqlAgente = "SELECT COUNT(*) FROM SFPC.TBTRAMITACAOAGENTE WHERE CGREMPCODI = " . $grupoAtual . " AND FTAGENALTE = 'S' ";

        $countAgente = resultValorUnico(executarTransacao($db, $sqlAgente));

        if ($countAgente > 0) {
            adicionarMensagem("<a href='javascript:document.getElementByID(\"inputAgente\"),focus();' class='titulo2'>Este grupo já possui agente alternativo cadastrado</a>", $GLOBALS['TIPO_MENSAGEM_ERRO']);

            $validar = false;
        }
    }

    if ($validar) {
        $sql = "SELECT MAX(CTAGENSEQU) FROM SFPC.TBTRAMITACAOAGENTE WHERE 1 = 1 ";

        $sequencial = resultValorUnico(executarTransacao($db, $sql)) + 1;

        $tipo = $externoAtual == 'S' ? 'E' : 'I';

        $sqlInsert  = "INSERT INTO SFPC.TBTRAMITACAOAGENTE ( ";
        $sqlInsert .= "CTAGENSEQU, CGREMPCODI, ETAGENDESC, FTAGENTIPO, CUSUPOCODI, TTAGENULAT, FTAGENINIC, FTAGENSITU, FTAGENCOMIS, FTAGENALTE ";
        $sqlInsert .= ") VALUES ( ";
        $sqlInsert .= $sequencial . ", " . $grupoAtual . ", '" . strtoupper2($descricaoAtual) . "', '" . $tipo . "', " . $_SESSION['_cusupocodi_'] . ", '" . date('Y-m-d h:i:s') . "', '" . $inicialAtual . "', '" . $situacaoAtual . "', '" . $comissaoLicAtual . "', '" . $agenteAlt . "') ";

        executarTransacao($db, $sqlInsert);

        // Buscar o last inserted id
        if (!empty($usuariosAtual)) {
            $sql = "SELECT CTAGENSEQU FROM SFPC.TBTRAMITACAOAGENTE WHERE 1 = 1 ORDER BY CTAGENSEQU DESC LIMIT 1";

            $sequencial = resultValorUnico(executarTransacao($db, $sql));

            foreach ($usuariosAtual as $key => $value) {
                $sql  = "INSERT INTO SFPC.TBTRAMITACAOAGENTEUSUARIO ( ";
                $sql .= "CTAGENSEQU, CUSUPOCODI, CUSUPOCOD1, TAGENUULAT ";
                $sql .= ") VALUES ( ";
                $sql .= $sequencial . ", " . $value . ", " . $_SESSION['_cusupocodi_'] . ", '" . date('Y-m-d h:i:s') . "') ";

                executarTransacao($db, $sql);
            }
        }

        finalizarTransacao($db);

        $Mensagem = "Agente Cadastrado com Sucesso";

        header('Location: TabTramitacaoAgenteIncluir.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
    }
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
            document.TabTramitacaoAgenteIncluir.Botao.value=valor;
            document.TabTramitacaoAgenteIncluir.submit();
        }

        function CaracteresObjeto(text,campo) {
            input = document.getElementById(campo);
            input.value = text.value.length;
        }

        function enviar(valor){
            document.TabTramitacaoAgenteIncluir.Botao.value=valor;
            document.TabTramitacaoAgenteIncluir.submit();
        }

        jQuery(document).ready(function(){
            jQuery(".capturarValorAcaoGrupo").change(function() {
                var acao  = $(this).attr('data-acao');
                document.TabTramitacaoAgenteIncluir.Botao.value = acao;
                document.TabTramitacaoAgenteIncluir.submit();
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
    <form action="TabTramitacaoAgenteIncluir.php" method="post" name="TabTramitacaoAgenteIncluir">
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
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - AGENTE</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir um novo agente, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Descrição </td>
                                                    <td class="textonormal">
                                                        <font class="textonormal">máximo de 200 caracteres</font>
                                                        <input type="text" id="NCaracteresObjeto" disabled name="NCaracteresObjeto" readonly="" size="3" value="0" class="textonormal"><br>
                                                        <textarea id="inputDescricao" name="agenteDescricao" cols="50" rows="4" onkeyup="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" onblur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" onselect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" class="textonormal"><?php echo (!empty($descricaoAtual)) ? $descricaoAtual : ''; ?></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Externo*</td>
                                                    <td class="textonormal">
                                                        <select name="agenteExterno" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($externoAtual == 'N') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($externoAtual == 'S') ? 'selected' : ''; ?> value="S">SIM</option>
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
                                                        <select id="inputComissao" name="comissaoLicInicial" size="1" value="A" class="textonormal">
                                                            <option <?php echo ($comissaoLicAtual == 'N') ? 'selected' : ''; ?> value="N">NÃO</option>
                                                            <option <?php echo ($comissaoLicAtual == 'S') ? 'selected' : ''; ?> value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Agente alternativo ao agente externo<br/>(utilizado em Incluir Especial e Manter Especial)</td>
                                                    <td class="textonormal">
                                                        <select id="inputAgente" name="agenteAlt" size="1" value="A" class="textonormal">
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
                                                                <?php  # Mostra os grupos #
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
                                            <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
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
</body>
</html>