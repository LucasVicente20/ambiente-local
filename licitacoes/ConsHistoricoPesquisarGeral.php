<?php
/**
 * Portal da DGCO
 *
 * Programa: ConsHistoricoPesquisar.php
 * @author Pitang - José Francisco <jose.francisco@pitang.com>
 * Data: 19/06/2014 - CR123143]: REDMINE 19 (P6)
 */
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     10/07/2018
# Objetivo: Tarefa Redmine 73631
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho  
# Data:     10/08/2018
# Objetivo: Tarefa Redmine 200957
#----------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data:     17/12/2018
# Objetivo: Tarefa Redmine 200950
# -----------------------------------------------------------------------------
# Alterado: Pitang Agile Ti - Caio Coutinho
# Data:     05/04/2019
# Objetivo: Tarefa Redmine 214033
# -----------------------------------------------------------------------------
# Alterado: João Madson
# Data:     22/01/21
# Objetivo: CR #243044 
# -----------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 15/05/2023
# Objetivo: Cr 282613
# -----------------------------------------------------------------------------

//Acesso ao arquivo de funções #
include '../funcoes.php';

//Executa o controle de segurança #
session_start();
Seguranca();

//Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsHistoricoPesquisarGeral.php');
AddMenuAcesso('/licitacoes/ConsHistoricoResultadoGeral.php');

$Botao = null;
//Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $adminDireta                        = (isset($_POST['adminDireta'])) ? true : false;
    $tipoEmpresa                        = (isset($_POST['tipoEmpresa'])) ? true : false;
    $_SESSION['Botao']                  = $Botao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);
    $_SESSION['Objeto']                 = $Objeto = filter_input(INPUT_POST, 'Objeto', FILTER_SANITIZE_STRING);
    $_SESSION['OrgaoLicitanteCodigo']   = $OrgaoLicitanteCodigo = filter_input(INPUT_POST, 'OrgaoLicitanteCodigo', FILTER_SANITIZE_NUMBER_INT);
    $_SESSION['ComissaoCodigo']         = $ComissaoCodigo = $_POST['ComissaoCodigo'];
    $_SESSION['ModalidadeCodigo']       = $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $_SESSION['TratamentoDiferenciado'] = $tratamentoDiferenciado = $_POST['TratamentoDiferenciado'];
    $_SESSION['licitacaoAno']           = $LicitacaoAno = $_POST['LicitacaoAno'];
    $_SESSION['TipoItemLicitacao']      = $TipoItemLicitacao = $_POST['TipoItemLicitacao'];
    $_SESSION['Item']                   = $Item = $_SESSION['item'];
    $_SESSION['adminDireta']            = $adminDireta;
    $_SESSION['tipoEmpresa']            = $tipoEmpresa;
    $_SESSION['processoNumero']         = $processoNumero = $_POST['processoNumero'];
    $_SESSION['processoAno']            = $processoAno = $_POST['processoAno'];
    $_SESSION['licitacaoSituacao']      = $licitacaoSituacao = filter_input(INPUT_POST, 'licitacaoSituacao', FILTER_SANITIZE_STRING);
    $_SESSION['legislacao']             = $legislacao = $_POST['legislacao'];
} else {
    $Mensagem = $_GET['Mensagem'];
    $Mens                   = $_GET['Mens'];
    $Tipo                   = $_GET['Tipo'];
    $Objeto                 = $_GET['Objeto'];
    $OrgaoLicitanteCodigo   = $_GET['OrgaoLicitanteCodigo'];
    $ComissaoCodigo         = $_GET['ComissaoCodigo'];
    $ModalidadeCodigo       = $_GET['ModalidadeCodigo'];
    $tratamentoDiferenciado = $_GET['TratamentoDiferenciado'];
    $LicitacaoAno           = $_GET['LicitacaoAno'];
    $TipoItemLicitacao      = $_GET['TipoItemLicitacao'];
    $Item                   = $_GET['Item'];
    $processoNumero         = $_GET['processoNumero'];
    $processoAno            = $_GET['processoAno'];

    $_SESSION['Objeto']                 = null;
    $_SESSION['OrgaoLicitanteCodigo']   = null;
    $_SESSION['ComissaoCodigo']         = null;
    $_SESSION['ModalidadeCodigo']       = null;
    $_SESSION['TratamentoDiferenciado'] = null;
    $_SESSION['RetornoPesquisa']        = null;
    $_SESSION['Pesquisar']              = null;
    $_SESSION['TipoItemLicitacao']      = null;
    $_SESSION['Item']      	            = null;
    $_SESSION['processoNumero']         = null;
    $_SESSION['processoAno']            = null;
}



# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsHistoricoPesquisarGeral.php";

if ($Botao == "Pesquisar") {
    $Url = "ConsHistoricoResultadoGeral.php";
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header("location: " . $Url);
    exit();
} elseif ($Botao == "Limpar") {
    $Url = "ConsHistoricoPesquisarGeral.php";
    unset($_SESSION['item']);
    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: " . $Url);
    exit();
}
?>
<html>
    <?php
    # Carrega o layout padrão #
    layout();
    ?>
    <script language="javascript" type="">

        function enviar(valor)
        {
            document.ConsHistoricoPesquisarGeral.Botao.value=valor;
            document.ConsHistoricoPesquisarGeral.submit();
        }

        <?php MenuAcesso(); ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">

    <style>
        .hidden {
            display: none!important;
            visibility: hidden;
        }

        .largura-460 {
            width: 460px;
        }

        .largura-316 {
            width: 316px;
        }
    </style>

    <body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="ConsHistoricoPesquisarGeral.php" method="post" name="ConsHistoricoPesquisarGeral">
            <br><br><br><br><br>
            <table cellpadding="3" border="0">
                <!-- Caminho -->
                <tr>
                <td width="100"><img border="0" src="../midia/linha.gif"></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Histórico
                </td>
                </tr>
                <!-- Fim do Caminho-->

                <!-- Erro -->
                <?php if ($Mens == 1) { ?>
                    <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2">
                        <?php
                        if ($Mens == 1) {
                            ExibeMens($Mensagem, $Tipo, 1);
                        }
                        ?>
                    </td>
                    </tr>
                <?php } ?>
                <!-- Fim do Erro -->

                <!-- Corpo -->
                <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
                        <tr>
                        <td class="textonormal">
                            <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
                                <tr>
                                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">HISTÓRICO DA LICITAÇÃO</td>
                                </tr>
                                <tr>
                                <td class="textonormal">
                                    <p align="justify">
                                        Para consultar o Histórico das Licitações, selecione o item de pesquisa e  clique no botão "Pesquisar".
                                    </p>
                                </td>
                                <tr>
                                <td>
                                    <table class="textonormal" border="0" align="left">
                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Objeto</td>
                                        <td class="textonormal">
                                            <input type="text" name="Objeto" size="45" maxlength="60" value="<?php echo $Objeto; ?>" class="textonormal largura-460">
                                        </td>
                                        </tr>

                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Administração direta</td>
                                        <td class="textonormal">
                                            <input type="checkbox" name="adminDireta" id="adminDireta">
                                        </td>
                                        </tr>

                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante</td>
                                        <td class="textonormal">
                                            <select name="OrgaoLicitanteCodigo" class="textonormal largura-460" id="orgaoLicitante">
                                                <option value="">Todos os Órgãos Licitantes...</option>
                                                <?php
                                                $db = Conexao();
                                                $sql = "SELECT CORGLICODI,EORGLIDESC,FORGLITIPO ";
                                                $sql .= "  FROM SFPC.TBORGAOLICITANTE ";
                                                $sql .= " ORDER BY EORGLIDESC";
                                                $result = $db->query($sql);

                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                }

                                                $option = '';

                                                while ($Linha = $result->fetchRow()) {
                                                    if ($Linha[0] == $OrgaoLicitanteCodigo) {
                                                        $option .= "<option value=\"$Linha[0]\" class=\"$Linha[2]\" selected>$Linha[1]</option>\n";
                                                    } else {
                                                        $option .= "<option value=\"$Linha[0]\" class=\"$Linha[2]\">$Linha[1]</option>\n";
                                                    }
                                                }
                                                $db->disconnect();
                                                echo $option;
                                                ?>
                                            </select>
                                        </td>
                                        </tr>
                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Legislação de Compra</td>
                                        <td class="textonormal">
                                        <input type="radio" id="lei8666" name="legislacao" value="8666">
                                        <label for="lei8666">Lei 8.666/1993</label><br>
                                        <input type="radio" id="lei14133" name="legislacao" value="14133">
                                        <label for="lei14133">Lei 14.133/2021</label>
                                        </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Comissão </td>
                                            <td class="textonormal">
                                                <select name="ComissaoCodigo" class="textonormal largura-460">
                                                    <option value="">Todas as Comissões...</option>
                                                    <?php
                                                        $db = Conexao();
                                                        $sql = "SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI ";
                                                        $sql .= "  FROM SFPC.TBCOMISSAOLICITACAO ";
                                                        $sql .= "ORDER BY CGREMPCODI,ECOMLIDESC";
                                                        $result = $db->query($sql);
                                                        if (PEAR::isError($result)) {
                                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                        } else {
                                                            $option = '';
                                                            while ($Linha = $result->fetchRow()) {
                                                                if ($Linha[0] == $ComissaoCodigo) {
                                                                    $option .= "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                                } else {
                                                                    $option .= "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                                }
                                                            }
                                                        }
                                                        $db->disconnect();
                                                        echo $option;
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Processo Licitatório </td>
                                            <td class="textonormal">
                                                <input id="inputProcesso" type="text" value="<?php echo (!empty($processoNumero)) ? $processoNumero : ''; ?>" size="3" maxlength="4"  name="processoNumero" class="textonormal" /> /
                                                <input id="inputProcessoAno" type="text" value="<?php echo (!empty($processoAno)) ? $processoAno : ''; ?>" size="3" maxlength="4" name="processoAno" class="textonormal" />
                                            </td>
                                        </tr>
                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
                                        <td class="textonormal">
                                            <select name="ModalidadeCodigo" class="textonormal largura-460">
                                                <option value="">Todas as Modalidades...</option>
                                                <?php
                                                    $db = Conexao();
                                                    $sql = "SELECT CMODLICODI, EMODLIDESC ";
                                                    $sql .= "FROM SFPC.TBMODALIDADELICITACAO ";
                                                    $sql .= "ORDER BY AMODLIORDE";
                                                    $result = $db->query($sql);
                                                    if (PEAR::isError($result)) {
                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                    } else {
                                                        while ($Linha = $result->fetchRow()) {
                                                            if ($Linha[0] == $ModalidadeCodigo) {
                                                                echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
                                                            } else {
                                                                echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            }
                                                        }
                                                    }
                                                    $db->disconnect();
                                                ?>
                                            </select>
                                        </td>
                                        </tr>

                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Tratamento diferenciado EPP/ME/MEI</td>
                                        <td class="textonormal">
                                            <select name="TratamentoDiferenciado" class="textonormal largura-460">
                                                <option value="" selected>Todas as situações</option>
                                                <option value="N">Não</option>
                                                <option value="E">Exclusivo</option>
                                                <option value="C">Cota Reservada</option>
                                                <option value="S">Subcontratação</option>
                                                <option value="M">Cota Reservada/Exclusiva</option>
                                                <option value="A">Ampla Concorrência/Exclusiva</option>
                                            </select>
                                        </td>
                                        </tr>

                                        <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                        <td class="textonormal">
                                            <select name="licitacaoSituacao" class="textonormal selectLicitacaoSituacao">
                                                <option value="todas">Todas</option>
                                                <option value="andamento" selected>Em andamento</option>
                                                <option value="concluídas">Concluídas</option>
                                            </select>

                                        <span class="hidden">
                                            Ano
                                            <select name="LicitacaoAno" class="textonormal" id="licitacaoAno">
                                                <option value="" selected></option>
                                                <?php
                                                    $db = Conexao();
                                                    $idFasesConcluidas = implode(', ', getIdFasesConcluidas($db));
                                                    $sql = "SELECT DISTINCT TO_CHAR(TLICPODHAB,'YYYY') ";
                                                    $sql .= " FROM SFPC.TBLICITACAOPORTAL LP ";
                                                    $sql .= " INNER JOIN SFPC.TBFASELICITACAO FL ";
                                                    $sql .= " ON LP.clicpoproc = FL.clicpoproc ";
                                                    $sql .= " and LP.alicpoanop = FL.alicpoanop ";
                                                    $sql .= " and LP.cgrempcodi = FL.cgrempcodi ";
                                                    $sql .= " and LP.ccomlicodi = FL.ccomlicodi ";
                                                    $sql .= " and LP.corglicodi = FL.corglicodi ";
                                                    $sql .= " and FL.cfasescodi  IN($idFasesConcluidas) ";
                                                    $sql .= " AND(
                                                                EXTRACT(
                                                                    YEAR
                                                                FROM
                                                                    TLICPODHAB
                                                                ) <= EXTRACT(
                                                                    YEAR
                                                                FROM
                                                                    CURRENT_DATE
                                                                )
                                                              ) ";
                                                    $sql .= " ORDER BY TO_CHAR(TLICPODHAB,'YYYY') DESC";
                                                    $result = $db->query($sql);

                                                    if (PEAR::isError($result)) {
                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                    }
                                                    //comentado por conta da cr #243044
                                                    // while ($Linha = $result->fetchRow()) {
                                                    //     echo "<option value=\"$Linha[0]\">$Linha[0]</option>\n";
                                                    // }

                                                    // Inicio de inserção do ano 2021 emergencial CR #243044  |Madson
                                                    $arrayAnoSituacao = array();
                                                    while ($Linha = $result->fetchRow()) {
                                                        array_push($arrayAnoSituacao, $Linha);
                                                    }
                                                    
                                                    if(!in_array('2023', $arrayAnoSituacao[0])){
                                                        $aux = $arrayAnoSituacao;
                                                        $tamanho = count($arrayAnoSituacao) + 1;
                                                        
                                                        $j=0;
                                                        for($i = 0; $i < $tamanho; $i++){
                                                            if($i == 0){
                                                                // $arrayAnoSituacao[0][0] = "2023";
                                                            }else{
                                                                $arrayAnoSituacao[$i][0] = $aux[$j][0];
                                                                $j++;
                                                            }
                                                        }
                                                    }
                                                    $tamanho =  !empty($tamanho)?$tamanho:count($arrayAnoSituacao);
                                                    for($k = 0; $k < $tamanho; $k++) {
                                                        $dado = $arrayAnoSituacao[$k][0];
                                                        echo "<option value=\"$dado\">$dado</option>\n";
                                                    }
                                                    //Fim da inserção

                                                    $db->disconnect();
                                                ?>
                                            </select>
                                        </span>
                                </td>
                                </tr>

                                <tr id="linhaTipoEmpresa">
                                <td class="textonormal" bgcolor="#DCEDF7">Microempresa, EPP ou MEI</td>
                                <td class="textonormal">
                                    <input type="checkbox" name="tipoEmpresa">
                                </td>
                                </tr>

                                <tr>
                                <td class="textonormal" bgcolor="#DCEDF7">Item</td>
                                <td class="textonormal">
                                    <a href="" style="text-decoration: none" onclick="javascript:AbreJanelaItem('../estoques/CadIncluirItem.php?ProgramaOrigem=ConsHistoricoPesquisarGeral&amp;PesqApenas=C', 700, 350);">
                                        <img src="../midia/lupa.gif" alt="">
                                    </a>
                                    <?php
                                        if(!empty($_SESSION['item'])) {
                                        $DadosSessao = explode($SimboloConcatenacaoArray, $_SESSION['item'][0]);

                                        $codigos = explode('#', $DadosSessao[1]);
                                        if(is_array($codigos) && count($codigos) == 2) {
                                            $ItemCodigo = $codigos[0];
                                            $codigoItemAta = $codigos[1];
                                        } else {
                                            $ItemCodigo = $DadosSessao[1];
                                            $codigoItemAta = '';
                                        }

                                        $db = Conexao();
                                        if($DadosSessao[4] == 'M') {
                                            $sql = ' SELECT m.ematepdesc, u.eunidmsigl FROM SFPC.TBmaterialportal m, SFPC.TBunidadedemedida u WHERE m.cmatepsequ = ' . $ItemCodigo . ' and u.cunidmcodi = m.cunidmcodi ';
                                        } else {
                                            $sql = ' SELECT m.eservpdesc FROM SFPC.TBservicoportal m WHERE m.cservpsequ = ' . $ItemCodigo . ' ';
                                        }

                                        $res = $db->query($sql);
                                        if (PEAR::isError($res)) {
                                            EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
                                        }
                                        $Linha = $res->fetchRow();
                                        $_SESSION['ItemName']   = $Linha[0];
                                        $_SESSION['ItemId']     = $DadosSessao[1];
                                        $_SESSION['ItemTipo']   = $DadosSessao[4];
                                        $db->disconnect();
                                    ?>
                                    <?php echo $Linha[0]; ?>
                                    <input type="hidden" name="ItemName" value="<?php echo $Linha[0]; ?>" size="50"  />
                                    <input type="hidden" name="Item" value="<?php echo $DadosSessao[1]; ?>" />
                                    <input type="hidden" name="TipoItemLicitacao" value="<?php echo $DadosSessao[4]; ?>" size="50" maxlength="60" />
                                    <?php } ?>
                                </td>
                                </tr>

                            </table>
                        </td>
                        </tr>
                        <tr>
                            <td class="textonormal" align="right">
                                <input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
                                <input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
                                <input type="hidden" name="Botao" value="">
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
<script language="javascript" type="text/javascript">
    <!--
    document.ConsHistoricoPesquisarGeral.Objeto.focus();
    //-->

    function AbreJanelaItem(url,largura,altura){
        window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }

    /**
     * Se a situação for alguma das concluídas o campo de ano será exibido como opcional.
     */
    var SituacaoLicitacao = (function() {
    	var ocultarAnoSituacaoConcluidaSelecionada = function () {
            /**
             * Verifica se a situação selecionada é concluída e caso seja habilita o campo ano.
             */
            $(".selectLicitacaoSituacao").on("change", function () {
                var situacaoSelecionada = $(this).val();
                verificarSituacaoSelecionada(situacaoSelecionada);
            });
        }, verificarSituacaoAoCarregar = function () {
            /**
             * Ao carregar a página, verifica se a situação selecionada é concluída.
             */
            var situacaoSelecionada = $(".selectLicitacaoSituacao option:selected").val();
            verificarSituacaoSelecionada(situacaoSelecionada);
        }, verificarSituacaoSelecionada = function (situacaoSelecionada) {
            var ocultarAno = (situacaoSelecionada != 'concluídas') ? true : false;
            var ocultarTipoEmpresa = (situacaoSelecionada == 'andamento') ? true : false;

            $("#licitacaoAno").parent("span").toggleClass("hidden", ocultarAno);
            $("#linhaTipoEmpresa").toggleClass("hidden", ocultarTipoEmpresa);
        };

        return {
            ajustarVisibilidadeCampoAno: function () {
                ocultarAnoSituacaoConcluidaSelecionada();
                verificarSituacaoAoCarregar();
            },
        };
    })();

    /**
     *
     */
    var FiltroAdministracaoDireta = (function() {
    	var filtarOrgao = function () {
            $("#adminDireta").on("change", function () {
                var adminDireta = $(this).is(":checked");
                executarFiltro(adminDireta);
            });
        }, executarFiltro = function (filtrar) {
            $("#orgaoLicitante option:first").attr('selected','selected');

            if (filtrar) {
                $("#orgaoLicitante option[class=I]").hide();
            } else {
                $("#orgaoLicitante option").show();
            }
        }, verificarAdmDiretaAoCarregar = function () {
            var adminDireta = $("#adminDireta").is(":checked");
            executarFiltro(adminDireta);
        };

        return {
            filtrar: function () {
        	   filtarOrgao();
               verificarAdmDiretaAoCarregar();
            },
        };
    })();

    $(document).ready(function() {
        SituacaoLicitacao.ajustarVisibilidadeCampoAno();
        FiltroAdministracaoDireta.filtrar();
    });
    //-->
</script>
</body>
</html>
