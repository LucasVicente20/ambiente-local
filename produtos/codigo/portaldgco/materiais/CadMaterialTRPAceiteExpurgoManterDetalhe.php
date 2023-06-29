<?php
// ----------------------------------------------------------------------------
// Portal da DGCO
// Programa: CadMaterialTRPAceiteExpurgoManterDetalhe.php
// Objetivo: Programa de Gerenciamento de preços de licitação da TRP
// possibilitando alterar os status de validação dos preços realizados na licitação
// Autor: Igor Duarte
// Data: 06/09/2012
// Alterado: Igor Duarte
// Data: 01/11/2012 - inclusão das colunas lote e ordem
// ----------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 07/07/2015
// Objetivo: CR Redmine 73652 - Materiais > TRP > Aceite Expurgo – Manter
// Versão: v1.23.0-6-ga19f938
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data: 14/03/2016
// Objetivo: Bug 126514 - Aceite / Expurgo Manter
// ---------------------------------------------------------------------------
require_once "../licitacoes/funcoesComplementaresLicitacao.php";

# Acesso ao arquivo de funções #
require_once "../funcoes.php";

# Executa o controle de segurança  #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/materiais/CadMaterialTRPAceiteExpurgoManter.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao              = $_POST['Botao'];
    $Processo           = $_POST['Processo'];
    $ProcessoAno        = $_POST['ProcessoAno'];
    $Orgao              = $_POST['Orgao'];
    $OrgaoDesc          = $_POST['OrgaoDesc'];
    $ValorH             = $_POST['ValorH'];
    $DataH              = $_POST['DataH'];
    $Grupo              = $_POST['Grupo'];
    $Comissao           = $_POST['Comissao'];

    $arrValid           = $_POST['arrValid'];
    $arrJustf           = $_POST['arrJustf'];

    $intCodUsuario      = $_SESSION['_cusupocodi_'];
} else {
    $Processo           = $_GET['Processo'];
    $ProcessoAno        = $_GET['ProcessoAno'];
    $Grupo              = $_GET['Grupo'];
    $Comissao           = $_GET['Comissao'];
    $Orgao              = $_GET['Orgao'];

    $Mensagem           = urldecode($_GET['Mensagem']);
    $Mens               = $_GET['Mens'];
    $Tipo               = $_GET['Tipo'];
    $Critica            = $_GET['Critica'];

    $intCodUsuario      = $_SESSION['_cusupocodi_'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
    $Url = "CadMaterialTRPAceiteExpurgoManter.php";

    if (!in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }

    header("Location: ".$Url);
    exit;
} elseif ($Botao == "Salvar") {
    $erro = false;

    $dataAtual = date('Y-m-d');

    $db   = Conexao();

    $sql  = "";

    foreach ($arrValid as $arrVInd => $arrVCont) {
        foreach ($arrVCont as $codMat => $valid) {
            if (($valid == 'A') && (($arrJustf[$arrVInd][$codMat] == null) || ($arrJustf[$arrVInd][$codMat] == ""))) {
                //CASO SEJA 'ACEITE' E A JUSTIFICATIVA SEJA VAZIA OU NULA
                $erro = true;
                break;
            } else {
                // CR73652
                $justf = strtoupper($arrJustf[$arrVInd][$codMat]);

                if (($valid == "") || ($valid == null)) {
                    $valid = 'NULL';

                    if (!$erro2) {
                        $erro2 = true;
                    }
                } else {
                    $valid = "'".$valid."'";
                }

                $sql = "UPDATE
                                SFPC.TBTABELAREFERENCIALPRECOS
                            SET
                                FTRPREVALI  = $valid
                            ";

                if ((($justf == "") || ($justf == null))) {
                    $sql .= ",ETRPREJUST = NULL
                                ";
                } else {
                    $sql .= ",ETRPREJUST = '".$justf."'
                                ";
                }

                $sql .=  "   ,CUSUPOCODI = $intCodUsuario
                                ,DTRPREVALI = '".$dataAtual."'
                             WHERE
                                CLICPOPROC      = $Processo
                                AND ALICPOANOP  = $ProcessoAno
                                AND CGREMPCODI  = $Grupo
                                AND CCOMLICODI  = $Comissao
                                AND CORGLICODI  = $Orgao
                                AND CMATEPSEQU  = $arrVInd
                                AND CITELPSEQU  = $codMat";

                $res  = executarTransacao($db, $sql);

                if (PEAR::isError($res)) {
                    cancelarTransacao($db);
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    $transacao = false;
                    die("aqui");
                }
            }
        }
    }

    if ($erro) {
        //GERAR MSG DE ERRO
        //O CAMPO JUSTIFICATIVA DEVE SER PREENCHIDO CASO A VALIDAÇÃO SEJA DO TIPO ACEITE
        $Mens = 1;
        $Mensagem = "O campo JUSTIFICATIVA deve estar preenchido caso a validação seja do tipo ACEITE";
        $Tipo = 0;
        $Botao = '';
    } else {
        $db->query("COMMIT");
        $db->query("END TRANSACTION");

        $db->disconnect();
        $Mensagem = urlencode("Atualização de Preços TRP realizada com sucesso");
        $Url = "CadMaterialTRPAceiteExpurgoManter.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
        if (!in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header("Location: $Url");
        exit;
    }
}

if ($Botao == "") {
    # Pega os dados da Licitação de acordo com os códigos passados #
        $db   = Conexao();

    $sql = "SELECT DISTINCT
                        ORG.EORGLIDESC ,LIP.VLICPOVALH ,FASE.TFASELDATA
                FROM
                        SFPC.TBTABELAREFERENCIALPRECOS TRP
                        JOIN SFPC.TBORGAOLICITANTE ORG ON ORG.CORGLICODI = TRP.CORGLICODI
                        JOIN SFPC.TBLICITACAOPORTAL LIP ON (LIP.CLICPOPROC = TRP.CLICPOPROC
                                                            AND LIP.ALICPOANOP = TRP.ALICPOANOP AND LIP.CGREMPCODI = TRP.CGREMPCODI
                                                            AND LIP.CCOMLICODI = TRP.CCOMLICODI AND LIP.CORGLICODI = TRP.CORGLICODI)
                        JOIN SFPC.TBFASELICITACAO FASE ON (FASE.CLICPOPROC = TRP.CLICPOPROC
                                                            AND FASE.ALICPOANOP = TRP.ALICPOANOP AND FASE.CGREMPCODI = TRP.CGREMPCODI
                                                            AND FASE.CCOMLICODI = TRP.CCOMLICODI AND FASE.CORGLICODI = TRP.CORGLICODI)
                WHERE
                        FASE.CFASESCODI = 13
                        AND TRP.CLICPOPROC IS NOT NULL
                        AND TRP.FTRPREVALI IS NOT NULL
                        AND TRP.CLICPOPROC = $Processo
                        AND TRP.ALICPOANOP = $ProcessoAno
                        AND TRP.CGREMPCODI = $Grupo
                        AND TRP.CCOMLICODI = $Comissao
                        AND TRP.CORGLICODI = $Orgao";

    $res  = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $Linha      = $res->fetchRow();

        $OrgaoDesc  = $Linha[0];
        $ValorH     = $Linha[1];

        $ValorH     = converte_valor_estoques($ValorH);

        $DataH      = explode("-", $Linha[2]);
        $DataH      = $DataH[2]."/".$DataH[1]."/".$DataH[0];
    }

    $db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>

<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script type="text/javascript">

    function enviar(valor){
        var boolValidar = false;

        if(valor=="Salvar"){

            //Validando se existe um item com validacao == ""
            $(".arrValid").each(function(){
                $(this).each(function(){
                    if($(this).val()==""){
                        boolValidar = true;
                    }
                });
            });
            if(boolValidar){
                if(!window.confirm("Existe(m) item(s) sem validação, deseja salvar?")){
                    return false;
                }
            }
        }
        document.CadMaterialTRPAceiteExpurgoManterDetalhe.Botao.value = valor;
        document.CadMaterialTRPAceiteExpurgoManterDetalhe.submit();

    }

    function AbreJanela(url,largura,altura) {
        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
    }

<?php MenuAcesso(); ?>
</script>

<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialTRPAceiteExpurgoManterDetalhe.php" method="post" name="CadMaterialTRPAceiteExpurgoManterDetalhe">
<br /><br /><br /><br /><br />
<table width="100%" cellpadding="3" border="0" summary="">
    <!-- Caminho -->
    <tr>
        <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
        <td align="left" class="textonormal" colspan="2">
            <font class="titulo2">|</font>
            <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > TRP > Aceite/Expurgo > Manter
        </td>
    </tr>
    <!-- Fim do Caminho-->

    <!-- Erro -->
    <tr>
        <td width="100"></td>
        <td align="left" colspan="2">
        <?php if ($Mens == 1) {
            ExibeMens($Mensagem, $Tipo, 1);
        }
        ?>
        </td>
    </tr>

    <!-- Fim do Erro -->

    <!-- Corpo -->

    <tr>
        <td width="100"></td>
        <td class="textonormal">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
                <tr>
                    <td class="textonormal">
                        <table border="0" cellspacing="0" cellpadding="0" summary="">
                            <tr>
                                <td class="textonormal">
                                    <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                        <tr>
                                            <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="11">
                                                MANTER ACEITE/EXPURGO PREÇOS TRP
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="11">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
                                                    <tr>
                                                        <td colspan="2">
                                                            <table class="textonormal" border="0" width="100%" summary="">
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20" width="15%" align="left">PROCESSO LICITATÓRIO: </td>
                                                                    <input type="hidden" name="Processo" value="<?php echo $Processo;?>">
                                                                    <td class="textonormal" width="85%" ><?php echo $Processo;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20" width="15%" align="left">ANO: </td>
                                                                    <input type="hidden" name="ProcessoAno" value="<?php echo $ProcessoAno;?>">
                                                                    <td class="textonormal" width="85%" ><?php echo $ProcessoAno;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20" width="15%" align="left">ÓRGÃO LICITANTE: </td>
                                                                    <input type="hidden" name="Orgao" value="<?php echo $Orgao;?>">
                                                                    <td class="textonormal" width="85%" ><?php echo $OrgaoDesc;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" width="15%" height="20" align="left">VALOR HOMOLOGADO: </td>
                                                                    <input type="hidden" name="Comissao" value="<?php echo $Comissao;?>">
                                                                    <td class="textonormal" width="85%" ><?php echo $ValorH;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" width="15%" height="20" align="left">DATA DA HOMOLOGAÇÃO: </td>
                                                                    <input type="hidden" name="Grupo" value="<?php echo $Grupo;?>">
                                                                    <td class="textonormal" width="85%" ><?php echo $DataH;?></td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >LOTE</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >ORDEM</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >CÓDIGO CADUM</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >DESCRIÇÃO DO MATERIAL</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >MARCA</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >MODELO</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >QTD.</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >VALOR</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >MÉDIA</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >VALIDAÇÃO</td>
                                            <td class="textonormal" bgcolor="#75ADE6" align="center" >JUSTIFICATIVA</td>
                                        </tr>
                                        <tr>
                                        <?php
                                        $db   = Conexao();

                                        $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
                                        $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

                                        $range = resultValorUnico(executarSQL($db, "SELECT VPARGEPERL FROM SFPC.TBPARAMETROSGERAIS"));

                                        $sql  = "SELECT DISTINCT
                                                    MAT.EMATEPCOMP ,ILP.EITELPMARC ,ILP.EITELPMODE ,ILP.AITELPQTSO ,ILP.VITELPVLOG ,ILP.CMATEPSEQU
                                                    ,ILP.CITELPSEQU ,ILP.CITELPNUML ,ILP.AITELPORDE, TRP.FTRPREVALI, TRP.ETRPREJUST
                                                 FROM
                                                    SFPC.TBITEMLICITACAOPORTAL ILP
                                                    JOIN SFPC.TBMATERIALPORTAL  MAT ON MAT.CMATEPSEQU = ILP.CMATEPSEQU
                                                    JOIN SFPC.TBTABELAREFERENCIALPRECOS TRP ON (ILP.CLICPOPROC = TRP.CLICPOPROC
                                                                                                AND ILP.ALICPOANOP = TRP.ALICPOANOP AND ILP.CGREMPCODI = TRP.CGREMPCODI
                                                                                                AND ILP.CCOMLICODI = TRP.CCOMLICODI AND ILP.CORGLICODI = TRP.CORGLICODI
                                                                                                AND ILP.CMATEPSEQU = TRP.CMATEPSEQU)
                                                 WHERE
                                                    TRP.CLICPOPROC      = $Processo
                                                    AND TRP.ALICPOANOP  = $ProcessoAno
                                                    AND TRP.CGREMPCODI  = $Grupo
                                                    AND TRP.CCOMLICODI  = $Comissao
                                                    AND TRP.CORGLICODI  = $Orgao
                                                    AND TRP.FTRPREVALI IS NOT NULL
                                                 ORDER BY
                                                    ILP.CITELPNUML ASC ,ILP.AITELPORDE ASC ,MAT.EMATEPCOMP ASC";

                                        $result = $db->query($sql);

                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            $i = 0;
                                            $itens;

                                            while ($Linha = $result->fetchRow()) {
                                                $media =  calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $Linha[5]);
                                                //imprimir dados
                                                echo "<tr><td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".$Linha[7]."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".$Linha[8]."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".$Linha[5]."</td>";
                                                $UrlPopup = "../estoques/CadItemDetalhe.php?Material=$Linha[5]&TipoGrupo=$TipoGrupoBanco&ProgramaOrigem=ConsMaterialSelecionar";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;<a href=\"javascript:AbreJanela('$UrlPopup',700,340);\"><font color=\"#000000\">$Linha[0]</font></a></td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".$Linha[1]."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".$Linha[2]."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".converte_valor_estoques($Linha[3])."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".converte_valor_estoques($Linha[4])."</td>";
                                                echo "<td bgcolor=\"#DCEDF7\" align=\"center\">&nbsp;".converte_valor_estoques($media)."</td>";

                                                // Select de validação
                                                $selectVazio = '';
                                                $selectAceite = '';
                                                $selectExpurgo = '';
                                                // Verifica se o valor da validação é igual a E (Expurgo)
                                                if ($Linha[9] == 'A') {
                                                    $selectAceite = 'selected="selected"';
                                                // Senão, verifica se o valor da validação é igual a A (Aceite)
                                                } elseif ($Linha[9] == 'E') {
                                                    $selectExpurgo = 'selected="selected"';
                                                } else {
                                                    $selectVazio = 'selected="selected"';
                                                }
                                                ?>
                                                <td bgcolor="#DCEDF7">
                                                    <select class="arrValid" id="arrValid[<?php echo $Linha[5];
                                                ?>][<?php echo $Linha[6];
                                                ?>]" name="arrValid[<?php echo $Linha[5];
                                                ?>][<?php echo $Linha[6];
                                                ?>]" >
                                                        <option value="A" <?php echo $selectAceite ?>>ACEITE</option>
                                                        <option value="E" <?php echo $selectExpurgo ?>>EXPURGO</option>
                                                    </select>
                                                </td>
                                                <td bgcolor="#DCEDF7">
                                                    <input type="text" value="<?php echo $Linha[10];
                                                ?>" id="arrJustf[<?php echo $Linha[5];
                                                ?>][<?php echo $Linha[6];
                                                ?>]" name="arrJustf[<?php echo $Linha[5];
                                                ?>][<?php echo $Linha[6];
                                                ?>]" class="textonormal" maxlength=200 />
                                                </td>
                                            </tr>
                                        <?php
                                                $i++;
                                            }
                                        }

                                        $db->disconnect();
                                        ?>
                                        </tr>
                                        <tr>
                                            <td colspan="11" align="right">
                                                <input type="button" name="Salvar" value="Salvar" class="botao" onClick="javascript:enviar('Salvar')">
                                                <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
                                                <input type="hidden" name="Botao" value="">
                                            </td>
                                        </tr>
                                    </table>
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
