<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraAnalisar.php
# Autor:    Gladstone Barbosa
# Data:     06/02/2012
# Objetivo: Analisar Solicitacoes de Compra do sistema
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: José Francisco <jose.francisco@pitang.com>
# Data:     30/05/2014 	- [CR121776]: REDMINE 14 (P4)

$programa = "CadSolicitacaoCompraAnalisar.php";

# Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';

# Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');

# Abrindo a Conexão
$db = Conexao();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# LISTA SOLICITAÇÕES INDIVIDUAL
function listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo=true) {
    $arrLinhas = array();

    $db 			= $GLOBALS["db"];

    $sql = "SELECT
        SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
        ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
        CEN.ECENPODESC, CEN.ECENPODETA
    FROM
        SFPC.TBSOLICITACAOCOMPRA AS SOL
    JOIN
        SFPC.TBORGAOLICITANTE AS ORG
            ON SOL.CORGLICODI = ORG.CORGLICODI
    JOIN
        SFPC.TBSITUACAOSOLICITACAO AS SSO
            ON SOL.CSITSOCODI = SSO.CSITSOCODI
    JOIN
        SFPC.TBCENTROCUSTOPORTAL AS CEN
            ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
    WHERE
        SOL.CTPCOMCODI = 2";
    //Filtrando Pela Situação
    if ($Situacao != "" & SoNumeros($Situacao)) {
        $sql .= " AND SSO.CSITSOCODI = $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql .= " AND ORG.CORGLICODI = ".$Orgao ;//SOL
    }
    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql .= " AND SOL.CSOLCOSEQU = $strSolicitacao ";
    }
    if (isset($boolFiltrarGrupo)&$boolFiltrarGrupo) {
        $sql .= " AND SOL.CSOLCOSEQU NOT IN (SELECT CSOLCOSEQU FROM SFPC.TBAGRUPASOLICITACAO)";
    }
    $sql .= " ORDER BY ORG.EORGLIDESC ASC, CEN.ECENPODESC, CEN.ECENPODETA, SOL.CSOLCOSEQU, SOL.ASOLCOANOS DESC ";

    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ( $Linha = $res->fetchRow() ) {

            $linhaRetorno['SeqSolicitacao']  = $Linha[0]; 			 // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao'] = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao'] 		 = $Linha[2]; 			 // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao'] 		 = $Linha[3]; 			 // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao'] 	 = $Linha[4];			 // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao'] = $Linha[5];			 // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto'] = $Linha[6];			 // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto'] = $Linha[7];			 // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */

            $arrLinhas[] = $linhaRetorno;
        }
    }

    return $arrLinhas;
}


# RETORNA O DETALHAMENTO DAS SOLICITAÇÕES
function infoDetalhamento($SeqSolicitacao) {
    $arrInfo = array();

    $db = $GLOBALS["db"];
    $sql = "SELECT
        I.CITESCSEQU, I.CMATEPSEQU, I.CSERVPSEQU,
        I.AITESCORDE, I.AITESCQTSO, I.VITESCUNIT,
        I.VITESCVEXE, M.EMATEPDESC, S.ESERVPDESC,
        I.EITESCDESCMAT, I.EITESCDESCSE
    FROM
        SFPC.TBITEMSOLICITACAOCOMPRA I
    LEFT JOIN
        SFPC.TBMATERIALPORTAL M ON (M.CMATEPSEQU = I.CMATEPSEQU)
    LEFT JOIN
        SFPC.TBSERVICOPORTAL S ON (S.CSERVPSEQU = I.CSERVPSEQU)
    WHERE I.CSOLCOSEQU = $SeqSolicitacao";

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['CodSeqItens'] = $Linha[0];        //I.CITESCSEQU - Código sequencial dos itens da solicitação de compras
            $linhaRetorno['CodMaterial'] = $Linha[1];        //I.CMATEPSEQU - Código do Material
            $linhaRetorno['CodServPortal'] = $Linha[2];        //I.CSERVPSEQU - Código do Servico Portal
            $linhaRetorno['OrdItemSoli'] = $Linha[3];        //I.AITESCORDE - Ordem do item na solicitação de compras
            $linhaRetorno['QtdItemSoli'] = $Linha[4];        //I.AITESCQTSO - Quantidade do item na solicitação de compras
            $linhaRetorno['VlrUnitItem'] = $Linha[5];        //I.VITESCUNIT - Valor unitário do item (estimado / Cotado / da Ata)
            $linhaRetorno['VlrItemSoli'] = $Linha[6];        //I.VITESCVEXE - Valor no exercício do item na solicitação de compras
            $linhaRetorno['DescMaterial'] = $Linha[7];        //M.EMATEPDESC -
            $linhaRetorno['DescServico'] = $Linha[8];        //S.ESERVPDESC - Descricao do servico
            $linhaRetorno['DescDetMaterial'] = $Linha[9];        //I.EITESCDESCMAT - Desc Detalhada Material
            $linhaRetorno['DescDetServico'] = $Linha[10];        //I.EITESCDESCSE - Descricao Detalhada servico

            if ($linhaRetorno['CodMaterial'] != "") {
                $linhaRetorno['Tipo'] = "CADUM";
                //$linhaRetorno['DescDetalhada']
            } else {
                $linhaRetorno['CodMaterial'] = $linhaRetorno['CodServPortal'];
                $linhaRetorno['DescMaterial'] = $linhaRetorno['DescServico'];
                $linhaRetorno['DescDetMaterial'] = $linhaRetorno['DescDetServico'];

                $linhaRetorno['Tipo'] = "CADUS";
            }

            $arrInfo[] = $linhaRetorno;
        }
    }

    return $arrInfo;
}

function exibeDetalhamento($SeqSolicitacao)
{
    ?>
    <!-- INÍCIO DO DETALHAMENTO DA SOLICITAÇÃO -->
        <tr style="display:none;" class="opdetalhe <?php echo $SeqSolicitacao;?>">
            <td style="background-color:#F1F1F1;" colspan="4">
                <table bordercolor="#75ADE6" border="1" bgcolor="bfdaf2" width="100%" class="textonormal">
                    <tr>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO</td>

                        <?php foreach ((infoDetalhamento($SeqSolicitacao)) as $ItensDesc): ?>
                            <?php if (!empty($ItensDesc['DescDetMaterial'])): ?>
                                <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO DETALHADA</td>
                                <?php $exibeTd = true;
                                break;
                            endif; ?>
                        <?php endforeach; ?>

                        <td class="textoabason" align="center" bgcolor="#DCEDF7">TIPO</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">CÓD.RED</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">QUANTIDADE</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR ESTIMADO</td>
                        <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR TOTAL</td>
                    </tr>
                    <?php
                    $arrayDetalhamento = infoDetalhamento($SeqSolicitacao);
                    foreach ($arrayDetalhamento as $itens) {
                    ?>
                    <tr>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['OrdItemSoli'];?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescMaterial'];?></td>

                        <?php if ($exibeTd) : ?>
                            <?php if (!empty($itens['DescDetMaterial'])): ?>
                                <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescDetMaterial']; ?></td>
                            <?php else: ?>
                                <td headers="descdet" align="center">-</td>
                            <?php endif; ?>
                        <?php endif; ?>

                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['Tipo'];?></td>
                        <td	class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['CodMaterial'];?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_quant($itens['QtdItemSoli']);?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['VlrUnitItem']);?></td>
                        <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['QtdItemSoli']*$itens['VlrUnitItem']);?></td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
    <!-- FIM DO DETALHAMENTO DA SOLICITAÇÃO -->
<?php
}

$Orgao = '';

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao          = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);
    $DataIni        = filter_input(INPUT_POST, 'DataIni');
    $Orgao	    = filter_input(INPUT_POST, 'Orgao');
    $Situacao       = filter_input(INPUT_POST, 'Situacao');
    $DataFim        = filter_input(INPUT_POST, 'DataFim');
    $Observacao     = filter_input(INPUT_POST, 'observacao');
    $idSolicitacao  = filter_input(INPUT_POST, 'idSolicitacao');
    $solicitacao    = filter_input(INPUT_POST, 'Solicitacao');
    
    if (empty($idSolicitacao) && !empty($solicitacao)) {
        $idSolicitacao = $solicitacao;
    }
    $intCodUsuario  = $_SESSION['_cusupocodi_'];

    if ($DataIni != "") {
        $DataIni = FormataData($DataIni);
    }
    if ($DataFim != "") {
        $DataFim = FormataData($DataFim);
    }

    if (isset($Botao, $Orgao, $Situacao, $DataIni, $DataFim)) {
        $_SESSION['Botao'] = $Botao;
        $_SESSION['Orgao'] = $Orgao;
        $_SESSION['Situacao'] = $Situacao;
        $_SESSION['DataIni'] = $DataIni;
        $_SESSION['DataFim'] = $DataFim;
    }

} else {
    $Mensagem     = urldecode($_GET['Mensagem']);
    $Mens         = $_GET['Mens'];
    $Tipo         = $_GET['Tipo'];

    $_SESSION['Orgao'] = '';
    $_SESSION['Situacao'] = '';
    $_SESSION['DataIni'] = '';
    $_SESSION['DataFim'] = '';
}

if ($Botao == 'Voltar') {
    $_SESSION["carregarSelecionarDoSession"] = true;
} elseif ($Botao == "Imprimir") {
    $Url = "RelAcompanhamentoSCCPdf.php?Solicitacao=" . $idSolicitacao;
    header("location: " . $Url);
    exit;
}

if ($_SESSION["carregarSelecionarDoSession"]) {
    $Botao        = $_SESSION['Botao'];
    $Orgao	  = $_SESSION['Orgao'];
    $Situacao     = $_SESSION['Situacao'];
    $DataIni      = $_SESSION['DataIni'];
    $DataFim      = $_SESSION['DataFim'];
    $_SESSION["carregarSelecionarDoSession"]=false;
}



if ($Botao == "Limpar") {
    header("location: ".$programa);
    exit;
}

// variável para auxiliar o trecho do Botao Analizar para exibição da mensagem de erro
$boolBotaoAnalisar = true;
// auxilia na mudança de exibição dos botões
$analisar = false;
// auxilia na chamada do metodo para consultar as solicitações
$pesquisa = false;

if ($Botao == "Analisar") {
    if (isset($idSolicitacao)) {
        $strSolicitacao = $idSolicitacao;
        $analisar = true;
        $boolBotaoAnalisar = true;
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Selecione a solicitação que deseja Analisar</a>";
        $analisar = false;
        $boolBotaoAnalisar = false;
    }
    $pesquisa = true;
}

if ($Botao == "Pesquisar"  || $Botao == "Voltar") {
    $pesquisa = true;
}

if ($Botao == "Observacao") {
    //INSERE UMA OBSERVAÇÃO
    $Mens = 0;
    if (strlen($Observacao)>300) {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.observacao.focus();\" class=\"titulo2\">Observação não pode ter mais que 30000 caracteres. </a><br>";
    }

    if (trim($Observacao)=="") {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.observacao.focus();\" class=\"titulo2\">Observação não pode ser vazio. </a><br>";
    }
    if ($Mens==0) {
        if (isset($idSolicitacao)) {

            $sql = "SELECT CSOLCOSEQU FROM
            SFPC.TBHISTSITUACAOSOLICITACAO
            WHERE CSOLCOSEQU = $idSolicitacao AND CSITSOCODI = $Situacao";

            $res = executarSQL($db, $sql) ;

            if ( PEAR::isError($res) ) {
                $CodErroEmail  = $res->getCode();
                $DescErroEmail = $res->getMessage();
                var_export($DescErroEmail);
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            } else {
                $qtd = $res->numRows();
                $Observacao = strtoupper2($Observacao);
                if ($qtd == 0) {
                    $sql = "INSERT INTO
                    SFPC.TBHISTSITUACAOSOLICITACAO
                    (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI, THSITSULAT , XHSITSOBSE)
                    VALUES
                    ($idSolicitacao, now(), $Situacao, $intCodUsuario, now() , '$Observacao')";
                } else {
                    $sql = "UPDATE
                    SFPC.TBHISTSITUACAOSOLICITACAO
                    SET XHSITSOBSE='$Observacao', CUSUPOCODI=$intCodUsuario, THSITSULAT=now()
                    WHERE CSOLCOSEQU = $idSolicitacao AND CSITSOCODI = $Situacao";
                }

                $resultado = executarTransacao($db, $sql);
                if ( PEAR::isError($resultado) ) {
                    $CodErroEmail  = $resultado->getCode();
                    $DescErroEmail = $resultado->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }
            }

            finalizarTransacao($db);

            $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Observação Atualizada com Sucesso </a>";
            $Mens      = 1;
            $Tipo      = 1;
        } else {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Selecione a solicitação que deseja incluir a 'Observação de Analise' </a>";
        }
        $pesquisa = false;
    } else {
        $strSolicitacao = $idSolicitacao;
        $analisar = true;
        $boolBotaoAnalisar = false;
        $pesquisa = true;
    }

}
if ($pesquisa) {
    # Critica dos Campos #

    # se $boolBotaoAnalisar for falso, significa que foi acionado o botão analizar sem ter selecionado alguma solicitação.
    if ($boolBotaoAnalisar) {
        $Mens     = 0;
        $Mensagem = "Informe: ";
    }

    $MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"formulario");

    if ($MensErro != "") {
        adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>$MensErro</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        $pesquisa = false;
    } else {

        if ($DataIni=="") {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial inválida.</a><br>";
            $pesquisa = false;
        }
        if ($DataFim=="") {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataFim.focus();\" class=\"titulo2\">Data Final inválida.</a><br>";
            $pesquisa = false;
        }

        if ( (DataInvertida($DataIni) > DataAtual()) && $Mens ==0 ) {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial maior que a Data Atual</a>";
            $pesquisa = false;
        }

    }

    if ($Situacao == "") {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Situação </a>";
        $pesquisa = false;
    }

    if ($Orgao == "") {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Orgão </a>";
        $pesquisa = false;
    }

    if ($pesquisa) {
        $arrLinhas = listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao);
    }

}

if ($Botao == "Encaminhamento") { //ALTERA PARA: "PARA EMCAMINHAMENTO"
    if ( isset($idSolicitacao) ) {

        $sql = "UPDATE
        SFPC.TBSOLICITACAOCOMPRA
        SET CSITSOCODI = 7, CUSUPOCODI = $intCodUsuario, TSOLCOULAT = now()
        WHERE CSOLCOSEQU = $idSolicitacao";

        $res = executarTransacao($db, $sql);
        if ( PEAR::isError($res) ) {
            $CodErroEmail  = $res->getCode();
            $DescErroEmail = $res->getMessage();
            var_export($DescErroEmail);
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        }

        $sql = "INSERT INTO
        SFPC.TBHISTSITUACAOSOLICITACAO
        (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI, THSITSULAT)
        VALUES
        ($idSolicitacao, now(), 7, $intCodUsuario, now())";

        $res = executarTransacao($db, $sql);
        if ( PEAR::isError($res) ) {
            $CodErroEmail  = $res->getCode();
            $DescErroEmail = $res->getMessage();
            var_export($DescErroEmail);
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        }

        finalizarTransacao($db);

        $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Solicitação Alterada com Sucesso </a>";
        $Mens     = 1;
        $Tipo     = 1;
    } else {
        $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Selecione a solicitação que deseja 'Voltar para Analise'  </a>";
        $Mens     = 1;
        $Tipo     = 2;
    }
    $pesquisa = false;
}
if ($Botao == "VoltarSituacaodeAnalise") { //ALTERA PARA: "EM ANÁLISE"

    $sql = "UPDATE
    SFPC.TBSOLICITACAOCOMPRA
    SET CSITSOCODI = 6, CUSUPOCODI = $intCodUsuario, TSOLCOULAT = now()
    WHERE CSOLCOSEQU = $idSolicitacao";

    $res = executarTransacao($db, $sql);
    if ( PEAR::isError($res) ) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        var_export($DescErroEmail);
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    }

    $sql = "INSERT INTO
    SFPC.TBHISTSITUACAOSOLICITACAO
    (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI, THSITSULAT)
    VALUES
    ($idSolicitacao, now(), 6, $intCodUsuario, now())";

    $res = executarTransacao($db, $sql);
    if ( PEAR::isError($res) ) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        var_export($DescErroEmail);
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    }

    finalizarTransacao($db);

    $Mensagem .= "<a href=\"javascript:document.onload();\" class=\"titulo2\">Solicitação Alterada com Sucesso </a>";
    $Mens     = 1;
    $Tipo     = 1;

    $pesquisa = false;
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor)
{
    var validacao = true;
    if (valor == "VoltarSituacaodeAnalise") {
        var validacao = confirm("Deseja confirmar a operação e alterar a situação da solicitação para “em análise”?");

    }

    if (validacao) {
        document.formulario.Botao.value = valor;
        document.formulario.submit();
    }
}
function AbreJanela(url,largura,altura)
{
    window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<script language="JavaScript">
$(document).ready(function () {
    //No click do botão detalhar
    $(".detalhar").live("click", function () {
        //Pega o atributu ID que é a sequencia da solicitacao
        var seq = $(this).attr("id");
        //Ver a string dele (+ ou -)
        var valAtual = $(this).html();
        //Se for + mostra todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
        if (valAtual=="+") {
                $(this).html("-");
                $(".opdetalhe."+seq).show();
        //Se for - esconde todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
        } else {
                $(this).html("+");
                $(".opdetalhe."+seq).hide();
        }
    });
});
</script>
<form action="<?=$programa?>" method="post" name="formulario">
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
    <!-- Caminho -->
    <tr>
        <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
        <td align="left" class="textonormal" colspan="2">
            <font class="titulo2">|</font>
            <a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Solicitação > Analisar
        </td>
    </tr>
    <!-- Fim do Caminho-->

    <!-- Erro -->
    <?php

    if ($Mens == 1) {?>
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
                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                         ANALISAR - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO
                    </td>
                </tr>
                <tr>
                    <td align="left" valign="middle" colspan="4">
                         Preencha os dados abaixo e clique no botão pesquisar para listar as solicitações.
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <table border="0" width="100%" summary="">
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão*</td>
                                <td class="textonormal">
                                    <select name="Orgao" class="textonormal">
                                        <option value="">Selecione um Órgao...</option>
                                        <option <?php if (($Orgao=="TODOS") || ($_SESSION['Orgao'] == "TODOS")) {
                                            echo "selected='selected'";}
                                        ?> value="TODOS">Todos</option>
                                        <?php

                                        $sql  = "SELECT ORG.CORGLICODI , ORG.EORGLIDESC ";
                                        $sql .= "  FROM  SFPC.TBORGAOLICITANTE ORG ";
                                        $sql .= "  WHERE ORG.FORGLISITU = 'A' AND ORG.FORGLITIPO = 'D' ";
                                        $sql .= " ORDER BY ORG.EORGLIDESC";

                                        $res = $db->query($sql);

                                        if ( PEAR::isError($res) ) {
                                                $CodErroEmail  = $res->getCode();
                                                $DescErroEmail = $res->getMessage();
                                                var_export($DescErroEmail);
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                        } else {
                                            while ( $Linha = $res->fetchRow() ) {
                                                if ($Linha[0]==$Orgao) {
                                                    echo "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                } else {
                                                    echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
                                <td class="textonormal">
                                    <select name="Situacao" class="textonormal">
                                        <?php
                                        $sql  = "SELECT
                                            CSITSOCODI,
                                            ESITSONOME
                                        FROM
                                            SFPC.TBSITUACAOSOLICITACAO
                                        WHERE
                                            CSITSOCODI = 7 OR
                                            CSITSOCODI = 6
                                        ORDER BY ESITSONOME";

                                        $res = $db->query($sql);

                                        if ( PEAR::isError($res) ) {
                                                $CodErroEmail  = $res->getCode();
                                                $DescErroEmail = $res->getMessage();
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                        } else {
                                                while ( $Linha = $res->fetchRow() ) {
                                                    if ($Situacao == $Linha[0]) {
                                                        $selected = "selected = 'selected'";
                                                    } else {
                                                        $selected = "";
                                                    }
                                                    echo "<option $selected value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                }
                                        }
                                        $_SESSION['Situacao'] = $Situacao;
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
                                <td class="textonormal">
                                    <?php
                                    $DataMes = DataMes();
                                    if ($DataIni == "") { $DataIni = $DataMes[0]; }
                                    if ($DataFim == "") { $DataFim = $DataMes[1]; }
                                    $URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
                                    $URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
                                    ?>
                                    <input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
                                    <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                    &nbsp;a&nbsp;
                                    <input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
                                    <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="textonormal" align="right" colspan="4">
                        <input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
                        <input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
                        <input type="hidden" name="Botao" value="">
                    </td>
                </tr>
            </table>
            <table width="100%" border="1" summary="" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF">
                <?php
                if ($pesquisa) {
                ?>
                    <tr>
                        <td style="border-top: 0px;" align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">RESULTADO DA PESQUISA</td>
                    </tr>
                    <?php
                    $QtdRegistros = count($arrLinhas);
                    if ($QtdRegistros > 0) {
                        $DescricaoOrgao = "";
                        $DescricaoCentroCusto = "";
                        foreach ($arrLinhas as $linhas) {
                    ?>
                            <!-- INÍCIO SOLICITAÇÃO INDIVIDUAL -->
                            <?php
                                if ($DescricaoOrgao != $linhas['DescOrgao']) {
                            ?>
                                <tr class="linhaorgao">
                                    <td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3"><?php echo $linhas['DescOrgao'];?></td>
                                </tr>
                            <?php
                                    $DescricaoOrgao = $linhas['DescOrgao'];
                                }

                                if ($DescricaoCentroCusto !=  $linhas['DescCentroCusto']) {
                            ?>
                                <tr class="linhacentro">
                                    <td align="center" bgcolor="#DDECF9" colspan="5" class="titulo3"><?php echo $linhas['DescCentroCusto'];?></td>
                                </tr>
                                <tr class="linhainfo">
                                    <td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
                                    <td class="titulo3" bgcolor="#F7F7F7">DETALHAMENTO</td>
                                    <td class="titulo3" bgcolor="#F7F7F7">DATA</td>
                                    <td class="titulo3" bgcolor="#F7F7F7">SITUAÇÃO</td>
                                </tr>
                            <?php
                                    $DescricaoCentroCusto = $linhas['DescCentroCusto'];
                                }
                                $programaSelecao = "ConsAcompSolicitacaoCompra.php";
                                $Url = $programaSelecao."?SeqSolicitacao=".$linhas['SeqSolicitacao']."&programa=".$programa;
                                $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']);

                                if ($strSolicitacao != "") {
                                    $checked = "checked='checked'";
                                } else {
                                    $checked = "";
                                }
                            ?>
                                <tr class="linhasol">
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                        <input <?php echo $checked;?> type="radio" class="idSolicitacao soli" name="idSolicitacao" value="<?php echo $linhas['SeqSolicitacao'];?>" />
                                        <a href="<?php echo $Url;?>">
                                            <font color="#000000"><?php echo $strSolicitacaoCodigo;?></font>
                                        </a>
                                        <span style="cursor:pointer;margin-left:5px;margin-right:10px;" id="<?php echo $linhas['SeqSolicitacao'];?>" class="detalhar" onclick="">+</span>
                                    </td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DetaCentroCusto'];?></td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DataSolicitacao'];?></td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DescSolicitacao'];?></td>
                                </tr>
                            <!-- FIM SOLICITAÇÃO INDIVIDUAL -->
                    <?php
                            // Exibe a tabela de detalhamento de cada solicitação
                            exibeDetalhamento($linhas['SeqSolicitacao']);
                        }//Fim do Foreach Individual
                    ?>
                        <tr>
                            <td class="textonormal" align="right" colspan="4">
                            <?php
                            if ($analisar) {
                                if ($Situacao == 6) {
                            ?>
                                    <label style="float:left;">Observação de Análise: 3000 caracteres</label>
                                    <?php
                                    $obs = "";
                                    $sql = "SELECT XHSITSOBSE FROM
                                    SFPC.TBHISTSITUACAOSOLICITACAO
                                    WHERE CSOLCOSEQU = $idSolicitacao AND CSITSOCODI = $Situacao";
                                    $res = executarSQL($db, $sql) ;
                                    if ( PEAR::isError($res) ) {
                                        $CodErroEmail  = $res->getCode();
                                        $DescErroEmail = $res->getMessage();
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                    } else {
                                        while ( $Linha = $res->fetchRow() ) {
                                            $obs = $Linha[0];
                                        }
                                    }

                                    ?>
                                    <textarea style="width:100%" rows="5" cols="2" maxlength="3000" name="observacao"><?php echo $obs;?></textarea>
                                    <br>
                                    <input type="button" name="Incluir_Observacao_Analise" value="Incluir Observação de Analise" class="botao" onClick="javascript:enviar('Observacao')" />
                                    <input type="button" name="Para_Encaminhamento" value="Para Encaminhamento" class="botao" onClick="javascript:enviar('Encaminhamento')" />
                            <?php
                                } elseif ($Situacao == 7) {
                            ?>
                                    <input type="button" name="Voltar_Situacao_Analise" value="Voltar Situação de Análise" class="botao" onClick="javascript:enviar('VoltarSituacaodeAnalise')" />
                            <?php
                                }
                            ?>
                                <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Pesquisar')" />
                            <?php
                            } else {
                            ?>

                                <input type="button" name="Analisar" value="Analisar" class="botao" onClick="javascript:enviar('Analisar')" />

                            <?php
                            }
                            ?>
                            </td>
                        </tr>
                    <?php
                    } else {
                    ?>
                        <tr>
                            <td valign="top" colspan="4" class="textonormal" bgcolor="FFFFFF">
                            Pesquisa sem Ocorrências.
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </table>
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
<?php $db->disconnect(); ?>
</html>
