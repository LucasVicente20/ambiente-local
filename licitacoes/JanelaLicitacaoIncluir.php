<?php
/**
 * -------------------------------------------------------------------------
 * Portal da DGCO
 * Programa: CadLicitacaoIncluir.php
 * Autor:    Gladstone Barbosa
 * Data:     09/02/2012
 * Objetivo: Incluir Licitacoes de Compra do sistema
 * -------------------------------------------------------------------------
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * -------------------------------------------------------------------------
 * Autor: Igor Duarte
 * Objetivo: 17/12/2012 - alterações da CR #19749 - retirar validações de dotação
 * e bloqueio; permitir duplicação de itens na inserção;
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 12/06/2014, 17/09/2014 [CR123141]: REDMINE 23
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 12/01/2015
 * Objetivo: [CR redmine 233] Sistema não exibe mensagem de campos obrigatórios não informados e,
 * ao informá-los, não exibe mensagem de sucesso
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 07/05/2015
 * Objetivo: [CR redmine 73630] Criar filtro de pesquisa na inclusão e na alteração de licitação
 * -------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * --------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data: 02/12/2015
 * Objetivo: Bug 112482 - Licitações - campo licitação com tratamento diferenciado - ME/EPP/MEI
 * com problemas na Inclusão e Manutenção de Licitação
 * ---------------------------------------------------------------------------
 * Autor: Pitang Agile TI
 * Objetivo: Requisito 135854: Licitação Fase Incluir (#441)
 * Data : 28/06/2016
 * ---------------------------------------------------------------------------
 * Autor: Pitang Agile TI
 * Objetivo: Requisito 135923: Inclusão de Licitação (#442)
 * Data : 30/06/2016
 * ---------------------------------------------------------------------------
 * Autor: Pitang Agile TI
 * Objetivo: Requisito 135923: Inclusão de Licitação (#442)
 * Data : 30/06/2016
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data: 06/06/2018
 * Objetivo: 115579 - Incluir SCC ou Incluir Licitação - problema de gravação,
 * caracter estranho (as descrições detalhadas dos itens ainda estavam com problema)
 * --------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data: 30/08/2018
 * Objetivo: 202448 - C
 * --------------------------------------------------------------------------
 */


$programa = "JanelaLicitacaoIncluir.php";

// Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';
// Acesso ao arquivo de funções complementares de licitação #
//require_once './funcoesComplementaresLicitacao.php';
// Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');

// Abrindo Conexão
$db = Conexao();
$dbOracle = ConexaoOracle();

// Situações da SCC para pesquisa
$situacoes_ids = '(1,6,8)';// alterado na CR 202448
$situacoes = array(
    1 => 'EM CADASTRAMENTO',
    6 => 'EM ANÁLISE',
    7 => 'PARA ENCAMINHAMENTO'
);

// incluindo funcoes de ajuda
require_once "funcoesTramitacao.php";

/**
 */
class VerificaGrupoOrgao
{

    /**
     * [existeGrupoOrgaoCadastrado description]
     *
     * @param [type] $dao
     *            [description]
     * @param [type] $cgrempcodi
     *            [description]
     * @param [type] $corglicodi
     *            [description]
     * @return [type] [description]
     */
    public static function existeGrupoOrgaoCadastrado($dao, $cgrempcodi, $corglicodi)
    {
        $res = $dao->query('SELECT * FROM sfpc.tbgrupoorgao WHERE cgrempcodi = ? AND corglicodi = ?', array(
            $cgrempcodi,
            $corglicodi
        ));

        return $res->numRows();
    }
}

// definindo data-hora atual
$DataAtual = date("Y-m-d H:i:s");
$intCodUsuo = $_SESSION['_cusupocodi_'];
// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $DataIni = $_POST['DataIni'];
    $Orgao = $_POST['Orgao'];
    $Situacao = $_POST['Situacao'];
    $DataFim = $_POST['DataFim'];
    $tipoSelecao = $_POST['tipoSelecao'];
    $idSolicitacao = $_POST['idSolicitacao'];
    $numSolicitacao = $_POST['numSolicitacao'];
    $programaOrigem = $_POST['ProgramaOrigem'];

    if (isset($Botao, $Orgao, $Situacao, $DataIni, $DataFim)) {
        $_SESSION['Botao'] = $Botao;
        $_SESSION['Orgao'] = $Orgao;
        $_SESSION['Situacao'] = $Situacao;
        $_SESSION['DataIni'] = $DataIni;
        $_SESSION['DataFim'] = $DataFim;
    }

    $intGrupoComissao = $_POST['intGrupoComissao'];

    $CodigoDaComissao = $_POST['CodigoDaComissao'];
    $NumeroDoProcesso = $_POST['NumeroDoProcesso'];
    $AnoDoExercicio = $_POST['AnoDoExercicio'];
    $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $FlagRegistroPreco = $_POST['RegistroPreco'];
    $Licitacao = $_POST['Licitacao'];
    $AnoDaLicitacao = $_POST['AnoDaLicitacao'];
    $DataLicitacao = $_POST['DataLicitacao'];
    $HoraLicitacao = $_POST['HoraLicitacao'];
    $OrgaoLicitante = $_POST['OrgaoLicitante'];
    $CodigoOrgaoLicitante = $_POST['CodigoOrgaoLicitante'];
    $NCaracteresObjeto = $_POST['NCaracteresObjeto'];
    $Objeto = strtoupper($_POST['Objeto']);
    $ValorTotalEstimado = $_POST['valorTotalEstimado'];
    $validacaoFornecedor = $_POST['validacaoFornecedor'];

    if (is_null($_POST['validacaoFornecedor'])) {
        $validacaoFornecedor = "N";
    }
    $Bloqueio = $_POST['Bloqueio'];
    $GeraContrato = $_POST['GeraContrato'];
    $TratamentoDiferenciado = $_POST['TratamentoDiferenciado'];
    $CodSolicitacaoPesquisaDireta = $_POST['CodSolicitacaoPesquisaDireta'];

    // Valores dos intens
    $arrDescDetalhada = $_POST['descricaodetalhada'];
    $arrOrdem = $_POST['ordem'];
    $arrQuantidadeItem = $_POST['quantidadeItem'];
    $arrValorEstimadoItem = $_POST["valorEstimadoItem"];
    $arrValorTotalItem = $_POST['valorTotalItem'];
    $arrQuantidadeExercicioItem = $_POST['quantidadeExercicioItem'];
    $arrValorExercicioItem = $_POST['valorExercicioItem'];
    $arrTipoItens = $_POST['tipoItem'];
    $arrCodMaterialServico = $_POST['codRedItem'];
    $arrDotacaoBloqueio = $_POST['dotacaoBloqueio'];
    $ValorTrpItem = $_POST['ValorTrpItem'];
    $descdetmat = $_POST['descdetmat'];
    $descdetserv = $_POST['descdetserv'];

    $intCodUsuario = $_SESSION['_cusupocodi_'];
    $perfilCorporativo = $_SESSION['_fperficorp_'];
    $GrupoUsuario = $_SESSION['_cgrempcodi_'];

    $tratamentoDiferenciado = $_POST['TratamentoDiferenciado'];

    if ($DataIni != "") {
        $DataIni = FormataData($DataIni);
    }
    if ($DataFim != "") {
        $DataFim = FormataData($DataFim);
    }

    if ($DataLicitacao != "") {
        $DataLicitacao = FormataData($DataLicitacao);
    }

    $LicitacaoTipo = $_POST['LicitacaoTipoSelecionado'];


} else {
    $programaOrigem = $_GET['ProgramaOrigem'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens = $_GET['Mens'];
    $Tipo = $_GET['Tipo'];
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($_SESSION["carregarSelecionarDoSession"]) {
    $Botao = $_SESSION['Botao'];
    $Orgao = $_SESSION['Orgao'];
    $Situacao = $_SESSION['Situacao'];
    $DataIni = $_SESSION['DataIni'];
    $DataFim = $_SESSION['DataFim'];
    $_SESSION["carregarSelecionarDoSession"] = false;
}


if ($Botao == "Limpar") {
    header("location: " . $programa);
    exit();
}


if ($Botao == "Selecionar") {
    if (isset($idSolicitacao) && $idSolicitacao != "") {

        $arrLinhas = listarIndividual($situacoes_ids, "TODOS", "", "", $idSolicitacao, true, true);

        $_SESSION['sccTramitacao'] = $idSolicitacao .'-'.$numSolicitacao[$idSolicitacao].'-'. $arrLinhas[0]['ObjetoSolicitacao'];
        $_SESSION['sccProcessoLic'] = $arrLinhas[0]['numProcesso'].'-'.$arrLinhas[0]['anoProcesso'].'-'.$arrLinhas[0]['codComissaoAlt'];

        echo "<script>opener.document.$programaOrigem.Botao.value=1</script>";
        echo "<script>opener.document.$programaOrigem.submit()</script>";
        echo "<script>self.close()</script>";

    } else {
        adicionarMensagem("<a href='javascript:void(0);' class='titulo2'>Selecione uma solicitação</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        $Botao = "PesquisaGeral";
    }
}

if ($Botao == "PesquisaGeral") {
    // Critica dos Campos #
    $pesquisa = true;
    $CodSolicitacaoPesquisaDireta = "";
    $MensErro = ValidaPeriodo($DataIni, $DataFim, $Mens, "formulario");
    if ($MensErro != "") {
        adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>$MensErro</a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
        $pesquisa = false;
    } else {
        if ($DataIni == "") {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document." . $Programa . ".DataIni.focus();\" class=\"titulo2\">Data Inicial inválida.</a><br>";
            $pesquisa = false;
        }
        if ($DataFim == "") {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document." . $Programa . ".DataFim.focus();\" class=\"titulo2\">Data Final inválida.</a><br>";
            $pesquisa = false;
        }
        if ((DataInvertida($DataIni) > DataAtual()) && $Mens == 0) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document." . $Programa . ".DataIni.focus();\" class=\"titulo2\">Data Inicial maior que a Data Atual</a>";
            $pesquisa = false;
        }
    }

    if ($Orgao == "") {
        $Mens = 1;
        $Tipo = 2;
        if ($Mensagem != "") {
            $Mensagem .= "e  informe o ";
        } else {
            $Mensagem .= "Informe: ";
        }
        $Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Órgão</a>";
        $pesquisa = false;
    }

    if ($pesquisa) {
        $arrLinhas = listarIndividualLicitacaoIncluir($situacoes_ids, $Orgao, $DataIni, $DataFim, $strSolicitacao, true, false);
        //$arrLinhasGrupo = listarGrupo($situacoes_ids, $Orgao, $DataIni, $DataFim, $strSolicitacao, "", true);
        $arrLinhasGrupo = array();
        $acao = "Pesquisar";
    }
}

if ($Botao == "PesquisaDireta") {
    $acao = "";
    if ($CodSolicitacaoPesquisaDireta != "") {
        if (isNumeroSCCValido($CodSolicitacaoPesquisaDireta)) {
            $strSolicitacao = getSequencialSolicitacaoCompra($db, $CodSolicitacaoPesquisaDireta);

            if ($strSolicitacao != null) {
                $arrLinhas = listarIndividual($situacoes_ids, "TODOS", "", "", $strSolicitacao, true, true);

                if (count($arrLinhas) > 0) {

                    $_SESSION['sccTramitacao'] = $arrLinhas[0]['SeqSolicitacao'].'-'.$CodSolicitacaoPesquisaDireta.'-'. $arrLinhas[0]['ObjetoSolicitacao'];
                    $_SESSION['sccProcessoLic'] = $arrLinhas[0]['numProcesso'].'-'.$arrLinhas[0]['anoProcesso'].'-'.$arrLinhas[0]['codComissaoAlt'];

                    echo "<script>opener.document.$programaOrigem.Botao.value=1</script>";
                    echo "<script>opener.document.$programaOrigem.submit()</script>";
                    echo "<script>self.close()</script>";
                }

            } else {
                $msg = "Código de solicitação inválido";
            }
        } else {
            $msg = "Código de solicitação inválido";
        }
    } else {
        $msg = "Informe: Número da SCC";
    }

    if ($acao != "Incluir") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.CodSolicitacaoPesquisaDireta.focus();\" class=\"titulo2\">$msg</a>";
    }
}

// auxilia a exibição da tela de inclusao - exibe a tela -> sim ou não
if ($acao == "") {
    $acao = "Pesquisar";
}
?>
<html>
<?php
// Carrega o layout padrão #
$_REQUEST['window'] = 1;
layout();
?>
<script type="text/javascript">

    function enviar(valor){

        if(valor=="InserirLicitacao"){
            var limiteCompra = moeda2float(document.getElementById('limiteCompra').value);
            //var valorTotal = moeda2float(document.getElementById('ValorTotalEstimado').value) ;
            var valorTotal = document.getElementById('ValorTotalEstimado').value ;
            //var valorTotal = document.getElementById('ValorTotalEstimado');

            valorTotal=valorTotal.toString();

            if(valorTotal.indexOf(',') > -1){
                if(valorTotal.indexOf('.') > -1){
                    valorTotal = valorTotal.replace('.','');
                }

                valorTotal = valorTotal.replace(',','.');
                valorTotal = parseFloat(valorTotal);
            }
            else{
                valorTotal = parseFloat(valorTotal);
            }

            //alert(valorTotal);
            //alert(limiteCompra);
            if(valorTotal>limiteCompra&&limiteCompra>0){
                if(!window.confirm("A soma dos valores dos itens ultrapassam o valor limite da modalidade, Deseja Continuar ?")){
                    return false;
                }
            }
        }
        document.formulario.Botao.value = valor;
        document.formulario.submit();
    }
    function AbreJanela(url,largura,altura){
        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
    }
    function CaracteresObjeto(text,campo){
        input = document.getElementById(campo);
        input.value = text.value.length;
    }
    function AtualizarValorTotal(linha){

        //Pegando a quantidade do item na linha que foi alterada
        quantidadeItem = moeda2float(document.getElementById('quantidadeItem['+linha+']').value);
        //Pegando Valor do item na linha que foi alterada
        valorEstimadoItem = moeda2float(document.getElementById('valorEstimadoItem['+linha+']').value);
        //Pegando Valor da quantidade do exercicio
        quantidadeExercicio = moeda2float(document.getElementById('quantidadeExercicioItem['+linha+']').value);

        //Calculando o valor total
        valorTotalItem = quantidadeItem * valorEstimadoItem;
        //att o valor total do item na linha alterada no span e no imput
        document.getElementById('spanValorTotalItem['+linha+']').innerHTML = converte_valor(valorTotalItem);
        document.getElementById('valorTotalItem['+linha+']').value = float2moeda(valorTotalItem);
        quantidadeTotalItens = document.getElementById('quantidadeTotalItens').value;
        //# SO FACO SE TIVER EXERCÍCIO
        if(document.getElementById('RegistroPreco').value!="S"){
            if(isNaN(quantidadeExercicio)){
                quantidadeExercicio = 0;
                document.getElementById('quantidadeExercicioItem['+linha+']').value = 0.00;
            }

            if(isAgrupamento=="S"){
                valorExercicioItem = valorEstimadoItem * quantidadeExercicio;
            }else{
                valorExercicioItem =  moeda2float(document.getElementById('valorExercicioItem['+linha+']').value);
            }
            //calculando o valor do exercicio

            document.getElementById('spanValorExercicioItem['+linha+']').innerHTML = float2moeda(valorExercicioItem);
            document.getElementById('valorExercicioItem['+linha+']').value = float2moeda(valorExercicioItem);
            document.getElementById('spanValorDemaisExercicioItem['+linha+']').innerHTML = float2moeda(valorTotalItem-valorExercicioItem);
        }

        var total = 0;
        //Calculando total geral , lendo todas as linhas
        for(linha= 1; linha<= quantidadeTotalItens; linha++){
            total += moeda2float(document.getElementById('valorTotalItem['+linha+']').value);

        }

        document.getElementById('labelValorTotalEstimado').innerHTML = float2moeda(total);
        document.getElementById('ValorTotalEstimado').value = float2moeda(total);
        //alert(valorTotalItem-valorExercicioItem);

    }

</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript">
    $(document).ready(function(){
        $(".detalhar").live("click", function() {
            var seq = $(this).attr("id");
            var valAtual = $(this).html();
            if(valAtual=="+"){
                $(this).html("-");
                $(".opdetalhe."+seq).show();
            }else{
                $(this).html("+");
                $(".opdetalhe."+seq).hide();
            }
        });
    });
</script>
<form action="<?=$programa?>" method="post" name="formulario">
    <input type="hidden" name="Botao" id="Botao" value="" />
    <input type="hidden" name="ProgramaOrigem" value="<?php echo  $programaOrigem; ?>" />
    <?php
        if (($CodigoOrgaoLicitante != "") && ($ModalidadeCodigo != "") && (count($arrCodMaterialServico) > 0)) {
            $totalLimite = converte_valor_estoques(calculaLimiteCompra($CodigoOrgaoLicitante, $ModalidadeCodigo, $arrCodMaterialServico, $arrTipoItens));
        } else {
            $totalLimite = 0;
        }
    ?>
    <input type="hidden" id="limiteCompra" name="limiteCompra" value="<?php echo $totalLimite; ?>" />
    <table width="100%" cellpadding="3" border="0" summary="">
        <?php if ($Mens == 1) { ?>
        <tr>
            <td align="left" colspan="2">
                <?php ExibeMens($Mensagem, $Tipo, 1); ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td class="textonormal">
                <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                            INCLUIR - NÚMERO PROTOCOLO PARA PROCESSOS LICITATÓRIOS
                        </td>
                    </tr>
                    <?php if ($acao != "Pesquisar") { // TODO remover  if ?>
                        <tr>
                            <td colspan="4" class="textonormal">
                                <p align="justify">Preencha os campos para incluir uma nova
                                    licitação. Caso queira trocar as solicitações clique na lupa.</p>
                            </td>
                        </tr>
                    <?php
                        }
                    ?>
                    <?php if ($acao == "Pesquisar") { ?>
                    <tr>
                        <td align="left" valign="middle" colspan="4">
                            Para pesquisar uma SCC individual, digite o número da SCC simples ou agrupada e clique na lupa.
                            <br /> Para pesquisar de formar geral digite as informações a baixo e clique no botão Pesquisar.
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                            PESQUISAR DIRETA - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1">
                            SCC*
                        </td>
                        <td aling="left" colspan="3">
                            <?php
                                $plotarCodigoSolicitacaoPesquisa = '';
                                if (isset($CodSolicitacaoPesquisaDireta)) {
                                    $plotarCodigoSolicitacaoPesquisa = $CodSolicitacaoPesquisaDireta;
                                }
                            ?>
                            <input type="text" value="<?=$plotarCodigoSolicitacaoPesquisa; ?>" name="CodSolicitacaoPesquisaDireta" maxlength="14" class="solicitacao" />
                            <input type="button" name="PesquisaDireta" value="Confirmar" class="botao" onClick="javascript:enviar('PesquisaDireta')">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                            PESQUISAR GERAL - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <table border="0" width="100%" summary="">
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">
                                        Órgão*
                                    </td>
                                    <td class="textonormal">
                                        <select name="Orgao" class="textonormal">
                                            <option value="">Selecione um Órgao...</option>
                                            <?php
                                                $plotar = '';
                                                if ($Orgao == "TODOS") {
                                                    $plotar = "selected='selected'";
                                                }
                                            ?>
                                            <option <?=$plotar; ?> value="TODOS">Todos</option>
                                            <?php
                                                $sql = "
                                                SELECT ORG.CORGLICODI, ORG.EORGLIDESC
                                                FROM  SFPC.TBORGAOLICITANTE ORG
                                                WHERE ORG.FORGLISITU = 'A'
                                                AND ORG.CORGLICODI IN (SELECT distinct(SOL.CORGLICODI) FROM SFPC.TBSOLICITACAOCOMPRA SOL )
                                                ORDER BY ORG.EORGLIDESC";

                                                $res = $db->query($sql);
                                                if (PEAR::isError($res)) {
                                                    $CodErroEmail = $res->getCode();
                                                    $DescErroEmail = $res->getMessage();
                                                    var_export($DescErroEmail);
                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                                } else {
                                                    while ($Linha = $res->fetchRow()) {
                                                        if ($Linha[0] == $Orgao) {
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
                                    <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">
                                        Período*
                                    </td>
                                    <td class="textonormal">
                                        <?php
                                            $DataMes = DataMes();
                                            if ($DataIni == "") {
                                                $DataIni = $DataMes[0];
                                            }
                                            if ($DataFim == "") {
                                                $DataFim = $DataMes[1];
                                            }
                                            $URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
                                            $URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
                                        ?>
                                        <input type="text" name="DataIni" size="10" maxlength="10" value="<?=$DataIni;?>" class="textonormal">
                                        <a href="javascript:janela('<?=$URLIni; ?>','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="" /></a>
                                            &nbsp;&nbsp; <input type="text" name="DataFim" size="10" maxlength="10" value="<?=$DataFim; ?>" class="textonormal" />
                                        <a href="javascript:janela('<?=$URLFim; ?>','Calendario',220,170,1,0)">
                                            <img src="../midia/calendario.gif" border="0" alt="" />
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal" align="right" colspan="4">
                            <input type="button" name="PesquisaGeral" value="Pesquisar" class="botao" onClick="javascript:enviar('PesquisaGeral')">
                            <input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
                        </td>
                    </tr>
                </table>
                <table width="100%" border="1" summary="" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF">
                    <?php
                    if ($pesquisa) {
                        ?>
                        <tr>
                            <td style="border-top: 0px;" align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">
                                RESULTADO DA PESQUISA
                            </td>
                        </tr>
                        <?php
                        $QtdRegistros = count($arrLinhas);
                        $QtdRegistrosGrupo = count($arrLinhasGrupo);
                        if ($QtdRegistros > 0 || $QtdRegistrosGrupo > 0) {
                            $DescricaoOrgao = "";
                            $DescricaoCentroCusto = "";
                            $codigoSolicitacao = 0;
                            if ($QtdRegistros > 0) {
                                foreach ($arrLinhas as $linhas) {
                                    if ($codigoSolicitacao != $linhas['SeqSolicitacao']) {
                                        $codigoSolicitacao = $linhas['SeqSolicitacao'];
                                        ?>
                                        <!-- INÍCIO SOLICITAÇÃO INDIVIDUAL -->
                                        <?php
                                        if ($DescricaoOrgao != $linhas['DescOrgao']) {
                                            ?>
                                            <tr class="linhaorgao">
                                                <td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3">
                                                    <?php echo $linhas['DescOrgao']; ?>
                                                </td>
                                            </tr>
                                            <?php
                                            $DescricaoOrgao = $linhas['DescOrgao'];
                                        }

                                        if ($DescricaoCentroCusto != $linhas['DescCentroCusto']) {
                                            ?>
                                            <tr class="linhacentro">
                                                <td align="center" bgcolor="#DDECF9" colspan="5" class="titulo3">
                                                    <?php echo $linhas['DescCentroCusto']; ?>
                                                </td>
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
                                        $programaSelecao = $GLOBALS["DNS_SISTEMA"] . "compras/ConsAcompSolicitacaoCompra.php";
                                        $Url = $programaSelecao . "?SeqSolicitacao=" . $linhas['SeqSolicitacao'] . "&programa=" . $programa;
                                        $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']);
                                        ?>
                                        <tr class="linhasol">
                                            <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                                <input type="hidden" name="numSolicitacao[<?php echo $linhas['SeqSolicitacao']; ?>]" value="<?php echo $strSolicitacaoCodigo; ?>" />
                                                <input type="radio" class="idSolicitacao soli" name="idSolicitacao" value="<?php echo $linhas['SeqSolicitacao']; ?>" />
                                                <a href="<?php echo $Url; ?>">
                                                    <font color="#000000"><?php echo $strSolicitacaoCodigo; ?></font>
                                                </a>
                                                <span style="cursor: pointer; margin-left: 5px; margin-right: 10px;" id="<?php echo $linhas['SeqSolicitacao']; ?>" class="detalhar" onclick="">+</span>
                                            </td>
                                            <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                                <?php echo $linhas['DetaCentroCusto']; ?>
                                            </td>
                                            <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                                <?php echo $linhas['DataSolicitacao']; ?>
                                            </td>
                                            <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                                <?php echo $linhas['DescSolicitacao']; ?>
                                            </td>
                                        </tr>
                                        <!-- FIM SOLICITAÇÃO INDIVIDUAL -->
                                        <?php
                                            exibeDetalhamento($linhas['SeqSolicitacao']);
                                    } // Fim do Foreach Individual
                                }
                            }
                            $contagemGrupo = 0;
                            $DescricaoOrgao = "";
                            if ($QtdRegistrosGrupo > 0) {
                                foreach ($arrLinhasGrupo as $linhas) {
                                    if ($DescricaoOrgao != $linhas['DescOrgao'] & $linhas['FlagGrupo'] == "S") {
                                        ?>
                                        <tr class="linhaorgao">
                                            <td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3">
                                                <?php echo $linhas['DescOrgao']; ?>
                                            </td>
                                        </tr>
                                        <!-- INÍCIO SOLICITAÇÕES AGRUPADAS -->
                                        <?php
                                    }
                                    if ($linhas['FlagGrupo'] == "S") {
                                        $contagemGrupo ++;
                                        ?>
                                        <tr>
                                            <td align="left" bgcolor="#BFDAF2" colspan="5" class="titulo3">
                                                <input type="radio" class="idSolicitacao" name="idSolicitacao" value="<?php echo $linhas['CodGrupo'] . '-G'; ?>" />
                                                <?php echo $contagemGrupo; ?> - Agrupamento - DATA: <?php echo $linhas['DataAgrupamento']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
                                            <td colspan="2" class="titulo3" bgcolor="#F7F7F7">ORGÃO</td>
                                            <td class="titulo3" bgcolor="#F7F7F7">DATA</td>
                                        </tr>
                                        <?php
                                        $DescricaoOrgao = $linhas['DescOrgao'];
                                    }
                                    $programaSelecao = $GLOBALS["DNS_SISTEMA"] . "compras/ConsAcompSolicitacaoCompra.php";
                                    $Url = $programaSelecao . "?SeqSolicitacao=" . $linhas['SeqSolicitacao'] . "&programa=" . $programa;
                                    $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']);
                                    ?>
                                    <tr>
                                        <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                            <a href="<?php echo $Url; ?>"> <font color="#000000">
                                                    <?php echo $strSolicitacaoCodigo; ?></font>
                                            </a>
                                            <span style="cursor: pointer; margin-left: 5px; margin-right: 10px;" id="<?php echo $linhas['SeqSolicitacao']; ?>" class="detalhar" onclick="">+</span>
                                        </td>
                                        <td colspan="2" valign="top" bgcolor="#F7F7F7" class="textonormal">
                                            <?php echo $linhas['DescOrgao']; ?>
                                        </td>
                                        <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                            <?php echo $linhas['DataSolicitacao']; ?>
                                        </td>
                                    </tr>
                                    <!-- FIM SOLICITAÇÕES AGRUPADAS -->
                                    <?php
                                    exibeDetalhamento($linhas['SeqSolicitacao']);
                                } // Fim do Foreach Grupo
                            }
                            ?>
                            <tr>
                                <td class="textonormal" align="right" colspan="4">
                                    <input type="button" name="Selecionar" value="Selecionar SCC" class="botao" onClick="javascript:enviar('Selecionar')">
                                </td>
                            </tr>
                            <?php

                        } else {
                            ?>
                            <tr>
                                <td align="left" colspan="4" class="textonormal">
                                    Pesquisa sem Ocorrências.
                                </td>
                            </tr>
                            <?php

                        } // Fim do if QtdRegistros
                    } // Fim do if boolean pesquisar
                    }

                    /*
                     * Formulário de Inclusão
                     */
                    if ($acao == "Incluir") { // se selecionar não for verdadeiro, então foi requisitado uma pesquisa direta
                        if ($arrLinhas != 0) {
                            if (isset($idSolicitacao)) {
                                $aux = explode("-", $idSolicitacao);
                                $FlagTipo = $aux[1];
                            }

                            $DescricaoOrgao = "";
                            $DescricaoCentroCusto = "";
                            $strSolicitacaoCodigo = "";
                            $AnoExercicio = date('Y');

                            // Dados da primeira licitaçao que é a que tem a flag S
                            $strComissaoLicitacao = $arrLinhas[0]['DescComissaoLici'];
                            $intCodComissaoLicitacao = $arrLinhas[0]['CodComissaoLici'];
                            $intTipoRegistroPreco = $arrLinhas[0]['TipoRegistroPreco'];
                            $strCodComissaoLici = $arrLinhas[0]['CodComissaoLici'];
                            if($strCodComissaoLici){

                                // Com o numero da comissão pego o numero do grupo da comissão
                                $sql = "SELECT CGREMPCODI FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $intCodComissaoLicitacao ";
                                $res = $db->query($sql);

                                if (PEAR::isError($res)) {
                                    $CodErroEmail = $res->getCode();
                                    $DescErroEmail = $res->getMessage();
                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                } else {
                                    $intGrupoComissao = resultValorUnico(executarSQL($db, $sql));
                                }

                                // basta apenas uma SCC ter o flag igual a 'S', para o campo ser preenchido com 'Sim'
                                $strGeraContrato = "N";
                                foreach ($arrLinhas as $linha) {
                                    if ($linha['FlagGeraContrato'] == "S") {
                                        $strGeraContrato = "S";
                                    }
                                }

                                // Verificando se é agrupamento
                                if (count($arrLinhas) > 1 && $FlagTipo == "G") {
                                    $isAgrupamento = true;
                                }

                                // Verifica o máximo número de Processo Licitatório #
                                $sql = "SELECT MAX(CLICPOPROC) FROM SFPC.TBLICITACAOPORTAL ";
                                $sql .= " WHERE ALICPOANOP = $AnoExercicio AND CGREMPCODI = " . $intGrupoComissao . " ";
                                $sql .= "   AND CCOMLICODI = $strCodComissaoLici ";
                                $result = $db->query($sql);

                                if (PEAR::isError($result)) {
                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                } else {
                                    $Linha = $result->fetchRow();
                                    if ($Linha[0] == "" or $Linha[0] == 0) {
                                        $Processo = 1;
                                    } else {
                                        $Processo = $Linha[0] + 1;
                                    }
                                }

                                // Verifica o máximo número da Licitação #
                                if ($ModalidadeCodigo != "") {
                                    $sql = "SELECT MAX(CLICPOCODL) FROM SFPC.TBLICITACAOPORTAL ";
                                    $sql .= " WHERE ALICPOANOL = $AnoExercicio AND CGREMPCODI = " . $intGrupoComissao . " ";
                                    $sql .= "   AND CCOMLICODI = $strCodComissaoLici AND CMODLICODI = $ModalidadeCodigo";

                                    $result = $db->query($sql);
                                    if (PEAR::isError($result)) {
                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                    } else {
                                        $Linha = $result->fetchRow();
                                        if ($Linha[0] == "" or $Linha[0] == 0) {
                                            $Licitacao = 1;
                                        } else {
                                            $Licitacao = $Linha[0] + 1;
                                        }
                                    }
                                } else {
                                    $Licitacao = 0;
                                }
                            }
                            //$retorno = RecSolicitacoesBKS($arrLinhas, $intTipoRegistroPreco);
                            // ARR DE ITENS ESTA NA POSICAO 0
                            $listaIntens = $retorno[0];
                            // VALOR TOTAL DOS ITENS ESTA NA POSICAO 1
                            $valorTotal = $retorno[1];

                            if ($intTipoRegistroPreco == 'S') {
                                $RegistroPreco = "Sim";
                            } else {
                                $RegistroPreco = "Não";
                            }

                            ?>
                            <input type="hidden" name="idSolicitacao"
                                   value="<?php

                                   echo $idSolicitacao;
                                   ?>" />
                            <input type="hidden" name="intGrupoComissao"
                                   value="<?php

                                   echo $intGrupoComissao;
                                   ?>" />
                            <input type="hidden" name="isAgrupamento"
                                   value="<?php

                                   echo ($isAgrupamento) ? "S" : "N";
                                   ?>" />
                        <?php
                        if ($pesquisa) {
                        ?>
                            <tr>
                                <td align="left" valign="middle" colspan="4">Para pesquisar uma
                                    SCC individual, digite o número da SCC simples ou agrupada e
                                    clique na lupa.<br /> Para pesquisar de formar geral digite as
                                    informações a baixo e clique no botão Pesquisar.
                                </td>
                            </tr>
                        <?php

                        }
                        ?>
                            <tr>
                                <td>
                                    <table border="0" width="100%" summary="">
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Solicitação de Compra/Contratação-SCC*:</td>
                                            <td align="left" class="textonormal" colspan="1"><select
                                                    style="width: 200px;" multiple="multiple">
                                                    <?php

                                                    foreach ($arrLinhas as $linhaLici) {
                                                        ?>
                                                        <option
                                                            value="<?php

                                                            echo $linhaLici['SeqSolicitacao'];
                                                            ?>"><?php

                                                            echo getNumeroSolicitacaoCompra($db, $linhaLici['SeqSolicitacao']);
                                                            ?></option>
                                                        <?php

                                                    }
                                                    ?>
                                                </select> &nbsp;&nbsp;&nbsp;&nbsp;<img
                                                    title="Trocar Solicitações"
                                                    onclick="javascript:enviar('PesquisarGeral')"
                                                    src="../midia/lupa.gif"></td>
                                            <td align="left" class="textonormal" colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Comissão*</td>
                                            <td align="left" class="textonormal" colspan="3"><label
                                                    style="width: 500px;"><?php

                                                    echo $strComissaoLicitacao;
                                                    ?></label> <input type="hidden"
                                                                      name="CodigoDaComissao"
                                                                      value="<?php

                                                                      echo $intCodComissaoLicitacao;
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Processo</td>
                                            <td align="left" class="textonormal" colspan="3"><label><?php

                                                    echo substr($Processo + 10000, 1);
                                                    ?></label> <input type="hidden"
                                                                      name="NumeroDoProcesso"
                                                                      value="<?php

                                                                      echo substr($Processo + 10000, 1);
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Ano</td>
                                            <td align="left" class="textonormal" colspan="3"><label><?php

                                                    echo $AnoExercicio;
                                                    ?></label> <input type="hidden"
                                                                      name="AnoDoExercicio"
                                                                      value="<?php

                                                                      echo $AnoExercicio;
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Modalidade*</td>
                                            <td align="left" class="textonormal" colspan="3"><select
                                                    style="width: 500px;" name="ModalidadeCodigo"
                                                    class="textonormal" onChange="javascript:enviar('Modulo');">
                                                    <option value="">Selecione uma Modalidade..</option>
                                                    <?php
                                                    $sql = "SELECT CMODLICODI, EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO ORDER BY AMODLIORDE";
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
                                                    ?>
                                                </select></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Registro de Preço</td>
                                            <td align="left" class="textonormal" colspan="3"><input
                                                    type="hidden"
                                                    value="<?php

                                                    echo $intTipoRegistroPreco;
                                                    ?>"
                                                    id="RegistroPreco" name="RegistroPreco" />
                                                <?php
                                                echo $RegistroPreco;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Licitação</td>
                                            <td align="left" class="textonormal" colspan="3"><label><?php

                                                    echo substr($Licitacao + 10000, 1);
                                                    ?></label> <input type="hidden" name="Licitacao"
                                                                      value="<?php

                                                                      echo substr($Licitacao + 10000, 1);
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Ano da Licitação</td>
                                            <td align="left" class="textonormal" colspan="3"><label><?php

                                                    echo date('Y');
                                                    ?></label> <input type="hidden"
                                                                      name="AnoDaLicitacao"
                                                                      value="<?php

                                                                      echo date('Y');
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Data de Abertura*</td>
                                            <td align="left" class="textonormal" colspan="3">
                                                <?php
                                                $DataMes = DataMes();
                                                $URLLicitacao = "../calendario.php?Formulario=formulario&Campo=DataLicitacao";
                                                ?>
                                                <input type="text"
                                                       name="DataLicitacao" size="10" maxlength="10"
                                                       value="<?php

                                                       echo $DataLicitacao;
                                                       ?>"
                                                       class="textonormal"> <a
                                                    href="javascript:janela('<?php echo $URLLicitacao ?>','Calendario',220,170,1,0)"><img
                                                        src="../midia/calendario.gif" border="0" alt=""></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Hora de Abertura*</td>
                                            <td align="left" class="textonormal" colspan="3"><input
                                                    type="text" style="width: 60px;" class="hora"
                                                    name="HoraLicitacao" maxlength="5"
                                                    value="<?php

                                                    if ($HoraLicitacao != "") {
                                                        echo $HoraLicitacao;
                                                    }
                                                    ?>" /> hh:mm</td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Orgão Licitante*</td>
                                            <td align="left" class="textonormal" colspan="3"><input
                                                    type="text" readonly="readonly" style="width: 500px;"
                                                    name="orgaoLicitante"
                                                    value="<?php

                                                    echo $arrLinhas[0]['DescOrgao'];
                                                    ?>" /> <input type="hidden"
                                                                  name="CodigoOrgaoLicitante"
                                                                  value="<?php

                                                                  echo $arrLinhas[0]['CodOrgao'];
                                                                  ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Objeto*</td>
                                            <td align="left" class="textonormal" colspan="3">
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <?php
                                                            if ($FlagTipo == "I") {
                                                                $qtdCaracteres = strlen($arrLinhas[0]['ObjetoSolicitacao']);
                                                            } else {
                                                                $qtdCaracteres = strlen($Objeto);
                                                                ?>
                                                                <label
                                                                    class="textonormal">máximo de 200 caracteres</label> <input
                                                                    type="text" name="NCaracteresObjeto" disalbed
                                                                    id="NCaracteresObjeto" size="3" readonly
                                                                    value="<?php

                                                                    echo $qtdCaracteres;
                                                                    ?>"
                                                                    class="textonormal">
                                                                <?php

                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <?php

                                                            if ($FlagTipo == "I") {
                                                                ?>
                                                                <label
                                                                    class="textonormal"><?php

                                                                    echo $arrLinhas[0]['ObjetoSolicitacao'];
                                                                    ?></label> <input type="hidden" name="Objeto"
                                                                                      id="Objeto"
                                                                                      value="<?php

                                                                                      echo $arrLinhas[0]['ObjetoSolicitacao'];
                                                                                      ?>" />
                                                                <?php

                                                            } else {
                                                                ?>
                                                                <textarea
                                                                    maxlength="200" name="Objeto" id="Objeto"
                                                                    OnKeyUp="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                                    OnBlur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                                    OnSelect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                                    rows="3" cols="59"><?php

                                                                    echo $Objeto;
                                                                    ?></textarea>
                                                                <?php

                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Valor Total Estimado*</td>
                                            <td align="left" class="textonormal" colspan="3"><label
                                                    id="labelValorTotalEstimado" style="width: 150px;"><?php

                                                    echo converte_valor_estoques($valorTotal);
                                                    ?></label> <input type="hidden"
                                                                      id="ValorTotalEstimado" name="valorTotalEstimado"
                                                                      value="<?php

                                                                      echo $valorTotal;
                                                                      ?>" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Necessidade de apresentação de demonstrações
                                                contábeis*</td>
                                            <td align="left" class="textonormal" colspan="3"><select
                                                    name="validacaoFornecedor">
                                                    <option
                                                        <?php

                                                        if ($validacaoFornecedor == "S") {
                                                            echo "selected='selected'";
                                                        }
                                                        ?>
                                                        value="S">SIM</option>
                                                    <option
                                                        <?php

                                                        if ($validacaoFornecedor != "S") {
                                                            echo "selected='selected'";
                                                        }
                                                        ?>
                                                        value="N">NÃO</option>
                                                </select></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Bloqueio ou Dotação Orçamentária Geral</td>
                                            <td align="left" class="textonormal" colspan="3"><select
                                                    multiple="multiple" style="width: 350px;"
                                                    name="selectDotBloq" id="selectDotBloq">
                                                    <?php
                                                    /**
                                                     * INFORMAÇÃO SOBRE DOTAÇÃO/BLOQUEIO DA LICITAÇÃO
                                                     */
                                                    // Se so tenho uma unica dotacao ou bloqueio para todos os itens imprimo o valor
                                                    $arrDotacaoBloqueio = array();

                                                    if (! empty($listaIntens)) {
                                                        if ($intTipoRegistroPreco != 'S') {
                                                            foreach ($listaIntens as $Itens) {
                                                                if (! is_array($Itens['DOTACAOBLOQUEIOS']) || count($Itens['DOTACAOBLOQUEIOS']) < 1) {
                                                                    assercao(false, "Existem itens sem bloqueio");
                                                                }
                                                                $arr = array_unique($Itens['DOTACAOBLOQUEIOS']);
                                                                foreach ($arr as $strDotacaoiten) {
                                                                    $arrDotacaoBloqueio[] = $strDotacaoiten;
                                                                }
                                                            }
                                                        }

                                                        $dotBloq = array();

                                                        $arrDotacaoBloqueio = array_unique($arrDotacaoBloqueio);
                                                        if (count($arrDotacaoBloqueio) >= 1) {
                                                            foreach ($arrDotacaoBloqueio as $strDotacaoBloqueio) {
                                                                // echo $strDotacaoBloqueio;
                                                                $dotBloq[] = $strDotacaoBloqueio;
                                                                echo "<option value=\"$strDotacaoBloqueio\">$strDotacaoBloqueio</option>\n";
                                                            }
                                                        }
                                                    }
                                                    /**
                                                     * FINAL DA INFORMAÇÃO SOBRE DOTAÇÃO/BLOQUEIO DA LICITAÇÃO
                                                     */
                                                    ?>
                                                </select>
                                                <?php
                                                // var_dump($dotBloq);
                                                $ordem1 = 0;
                                                // echo $strDotacaoBloqueio;
                                                foreach ($dotBloq as $strDotBloq) {
                                                    $ordem1 ++;
                                                    ?>
                                                    <input
                                                        name="dotacaoBloqueio[<?php

                                                        echo $ordem1;
                                                        ?>]"
                                                        type="hidden"
                                                        value="<?php

                                                        echo $strDotBloq;
                                                        ?>" />
                                                    <?php

                                                }
                                                ?>
                                                <label style="width: 150px;"> </label>
                                                <input type="hidden" name="Bloqueio" value="" /></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Gera Contrato</td>
                                            <td align="left" class="textonormal" colspan="3"><select
                                                    name="GeraContrato">
                                                    <option
                                                        <?php

                                                        if ($strGeraContrato == "S") {
                                                            echo "selected='selected'";
                                                        }
                                                        ?>
                                                        value="S">SIM</option>
                                                    <option
                                                        <?php

                                                        if ($strGeraContrato != "S") {
                                                            echo "selected='selected'";
                                                        }
                                                        ?>
                                                        value="N">NÃO</option>
                                                </select></td>
                                        </tr>
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal"
                                                colspan="1">Tratamento diferenciado ME/EPP/MEI</td>
                                            <td align="left" class="textonormal" colspan="3">
                                                <?php
                                                $colecaoTratamento = array(
                                                    'N' => 'NÃO',
                                                    'E' => 'EXCLUSIVA',
                                                    'C' => 'COTA RESERVADA',
                                                    'S' => 'SUBCONTRATAÇÃO',
                                                    'M'  => 'COTA RESERVADA/EXCLUSIVA'
                                                );
                                                $plotar = '';
                                                foreach ($colecaoTratamento as $key => $value) {
                                                    $selecionado = '';
                                                    if ($tratamentoDiferenciado == $key) {
                                                        $selecionado = 'selected="selected"';
                                                    }
                                                    $plotar .= '<option ' . $selecionado . ' value="' . $key . '">' . $value . '</option>';
                                                }
                                                ?>
                                                <select
                                                    name="TratamentoDiferenciado"><?=$plotar;
                                                    ?></select>
                                            </td>
                                        </tr>
                                        <!-- Tipo licitação -->
                                        <tr>
                                            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Tipo de Licitação*</td>
                                            <td>

                                                <select name="LicitacaoTipoSelecionado" class="textonormal">
                                                    <option value="P" <?php echo ($LicitacaoTipo  != 'O' ? 'selected' : '');?>>MENOR PREÇO</option>
                                                    <option value="O" <?php echo ($LicitacaoTipo  == 'O' ? 'selected' : '');?>>MAIOR OFERTA</option>
                                                </select>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td align="center" bgcolor="#75ADE6" class="titulo3"
                                                colspan="4">ITENS DA SOLICITAÇÃO</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php

                        $estilotd = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
                        $estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
                        ?>
                            <tr>
                                <td style="background-color: #F1F1F1;" colspan="4">
                                    <table width="1400px" bordercolor="#75ADE6" border="1"
                                           cellspacing="" bgcolor="bfdaf2" class="textonormal">
                                        <!-- Dados dos itens Título  -->
                                        <tr class="linhainfo">
                                            <th id="ord">ORD</th>
                                            <th id="desc">DESCRIÇÃO</th>
                                            <th id="tipo">TIPO</th>
                                            <th id="codred">CÓD.RED</th>
                                            <th id="unidade">UNIDADE</th>
                                            <?php

                                            foreach ($listaIntens as $ItensDesc) :
                                                ?>
                                                <?php

                                                if (! empty($ItensDesc['DescDet'])) :
                                                    ?>
                                                    <th id="descdet">DESC DETALHADA <?php

                                                        echo(checarTipoItensSolic($strSolicitacao) ? "SERVIÇO" : "MATERIAL");
                                                        ?></th>
                                                    <?php

                                                    $exibeTd = true;
                                                    break;

                                                endif;
                                                ?>
                                            <?php
                                            endforeach
                                            ;
                                            ?>
                                            <th id="valor_trp">VALOR TRP</th>
                                            <th id="quantidade">QUANTIDADE</th>
                                            <th id="valor_estimado">VALOR ESTIMADO</th>
                                            <th id="valor_total_estimado">VALOR TOTAL ESTIMADO</th>
                                            <?php

                                            if (1 == 2) {
                                                ?>
                                                <td
                                                    <?php

                                                    echo $estilotd;
                                                    ?>>QUANTIDADE NO EXERCÍCIO</td>
                                                <td
                                                    <?php

                                                    echo $estilotd;
                                                    ?>>VALOR DO EXERCÍCIO</td>
                                                <td
                                                    <?php

                                                    echo $estilotd;
                                                    ?>>VALOR DEMAIS EXERCÍCIOS</td>
                                                <?php

                                            }
                                            ?>
                                            <!-- <td <?php

                                            echo $estilotd;
                                            ?>><?php

                                            if ($intTipoRegistroPreco == "N") {
                                                echo "BLOQUEIOS";
                                            } else {
                                                echo "DOTAÇÃO";
                                            }
                                            ?></td> -->
                                        </tr>
                                        <!-- FIM Dados dos itens Título  -->
                                        <?php

                                        $ordem = 0;
                                        $intQuantidadeItens = count($listaIntens);

                                        foreach ($listaIntens as $Itens) {
                                            $ordem ++;

                                            ?>
                                            <!-- Dados dos itens Resultado  -->
                                            <tr class="linha_itens_resultado">
                                                <td id="ord"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo $ordem;
                                                    ?>
                                                    <input type="hidden"
                                                           name="ordem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           value="<?php

                                                           echo $ordem;
                                                           ?>" /> <input type="hidden"
                                                                         name="codRedItem[<?php

                                                                         echo $ordem;
                                                                         ?>]"
                                                                         value="<?php

                                                                         echo $Itens['codRed'];
                                                                         ?>" /> <input type="hidden"
                                                                                       name="tipoItem[<?php

                                                                                       echo $ordem;
                                                                                       ?>]"
                                                                                       value="<?php

                                                                                       echo $Itens['Tipo'];
                                                                                       ?>" />
                                                </td>
                                                <td headers="desc"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo $Itens['Descricao'];
                                                    ?></td>
                                                <td headers="tipo"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo $Itens['Tipo'];
                                                    ?></td>
                                                <td headers="codred"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo $Itens['codRed'];
                                                    ?></td>
                                                <td headers="unidade"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo $Itens['Unid'];
                                                    ?></td>
                                                <?php

                                                if ($exibeTd) :
                                                    ?>
                                                    <?php

                                                    if (! empty($Itens['DescDet'])) {
                                                        ?>
                                                        <td headers="descdet"
                                                            <?php

                                                            echo $estiloClasstd;
                                                            ?>>&nbsp; <textarea disabled cols="30"
                                                                                rows="2"><?php

                                                                echo $Itens['DescDet'];
                                                                ?></textarea> <input hidden
                                                                                     name="descricaodetalhada[<?php

                                                                                     echo $ordem;
                                                                                     ?>]"
                                                                                     value="<?php

                                                                                     echo $Itens['DescDet'];
                                                                                     ?>" />
                                                        </td>

                                                        <?php

                                                    } else {
                                                        ?>
                                                        <td headers="descdet"
                                                            <?php

                                                            echo $estiloClasstd;
                                                            ?>>-</td>

                                                        <?php

                                                    }
                                                    ?>

                                                <?php
                                                endif;
                                                ?>
                                                <td headers="valor_trp"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;&nbsp;
                                                    <?php
                                                    if ($Itens['Tipo'] == "CADUM") {
                                                        $valorTrp = calculaValorTrp($Itens['codRed']);
                                                        if (empty($valorTrp)) {
                                                            echo "-";
                                                        } else {
                                                            echo converte_valor_estoques($valorTrp);
                                                        }
                                                    } else {
                                                        $valorTrp = "";
                                                        echo "-"; // Não tem
                                                    }
                                                    ?>
                                                    &nbsp;&nbsp;
                                                    <input
                                                        name="ValorTrpItem[<?php

                                                        echo $ordem;
                                                        ?>]"
                                                        value="<?php

                                                        echo $valorTrp;
                                                        ?>"
                                                        type="hidden" />
                                                </td>
                                                <td headers="quantidade"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;<?php

                                                    echo converte_valor_estoques($Itens['Quantidade']);
                                                    ?>
                                                    <input class="dinheiro4casas"
                                                           name="quantidadeItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           id="quantidadeItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           value="<?php

                                                           echo converte_valor_estoques($Itens['Quantidade']);
                                                           ?>"
                                                           type="hidden" />
                                                </td>
                                                <td headers="valor_estimado"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp;
                                                    <?php
                                                    // Se é individual mostra o valor sem pode ediar //AQUI
                                                    if (! $isAgrupamento) {
                                                        $valorEstimado = $Itens['ValorEstimado'];
                                                        if (isset($arrValorEstimadoItem[$ordem]) && $arrValorEstimadoItem[$ordem] != "") {
                                                            $valorEstimado = moeda2float($arrValorEstimadoItem[$ordem]);
                                                        }
                                                        ?>
                                                        <span
                                                            id="spanValorEstimadoItem[<?php

                                                            echo $ordem;
                                                            ?>]"><?php echo converte_valor_estoques($valorEstimado)?></span>
                                                        <input type="hidden" size="16" class="dinheiro4casas"
                                                               maxlength="16"
                                                               onKeyUp="AtualizarValorTotal('<?php

                                                               echo $ordem;
                                                               ?>');"
                                                               id="valorEstimadoItem[<?php

                                                               echo $ordem;
                                                               ?>]"
                                                               name="valorEstimadoItem[<?php

                                                               echo $ordem;
                                                               ?>]"
                                                               value="<?php echo converte_valor_estoques($valorEstimado)?>" />
                                                        <?php

                                                    } else {
                                                        // Se nao tenho valor trp $valorEstimado é zero más pode ser editado

                                                        if (empty($valorTrp)) {
                                                            $valorEstimado = 0;
                                                        } else {
                                                            $valorEstimado = $valorTrp;
                                                        }

                                                        if (isset($arrValorEstimadoItem[$ordem]) && $arrValorEstimadoItem[$ordem] != "") {
                                                            $valorEstimado = moeda2float($arrValorEstimadoItem[$ordem]);
                                                        }

                                                        ?>
                                                        <span style="display: none;"
                                                              id="spanValorEstimadoItem[<?php

                                                              echo $ordem;
                                                              ?>]">&nbsp;<?php echo converte_valor_estoques($valorEstimado)?>&nbsp;</span>
                                                        <input type="text" size="16" class="dinheiro4casas"
                                                               maxlength="16"
                                                               onKeyUp="AtualizarValorTotal('<?php

                                                               echo $ordem;
                                                               ?>');"
                                                               name="valorEstimadoItem[<?php

                                                               echo $ordem;
                                                               ?>]"
                                                               id="valorEstimadoItem[<?php

                                                               echo $ordem;
                                                               ?>]"
                                                               value="<?php echo converte_valor_estoques($valorEstimado)?>" />
                                                        <?php

                                                    }
                                                    $valorTotalItem = $valorEstimado * $Itens['Quantidade'];
                                                    ?>

                                                </td>
                                                <td headers="valor_total_estimado"
                                                    <?php

                                                    echo $estiloClasstd;
                                                    ?>>&nbsp; <span
                                                        id="spanValorTotalItem[<?php

                                                        echo $ordem;
                                                        ?>]"><?php

                                                        echo converte_valor_estoques($valorTotalItem);
                                                        ?></span> <input type="hidden"
                                                                         name="valorTotalItem[<?php

                                                                         echo $ordem;
                                                                         ?>]"
                                                                         id="valorTotalItem[<?php

                                                                         echo $ordem;
                                                                         ?>]"
                                                                         value="<?php

                                                                         echo converte_valor_estoques($valorTotalItem);
                                                                         ?>" />
                                                </td>
                                                <?php

                                                if (1 == 2) {
                                                    ?>
                                                    <td
                                                        <?php

                                                        echo $estiloClasstd;
                                                        ?>>&nbsp;
                                                        <?php
                                                        // Se for nao for agrupamento exibo o valor da tabela
                                                        if (! $isAgrupamento) {
                                                            ?>
                                                            <input type="hidden"
                                                                   name="quantidadeExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   id="quantidadeExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   value="<?php

                                                                   echo converte_valor_estoques($Itens['QtdExercicio']);
                                                                   ?>" /> <span><?php

                                                                echo converte_valor_estoques($Itens['QtdExercicio']);
                                                                ?></span>
                                                            <?php

                                                        } else {
                                                            ?>
                                                            <input class="dinheiro4casas"
                                                                   onKeyUp="AtualizarValorTotal('<?php

                                                                   echo $ordem;
                                                                   ?>');"
                                                                   type="text"
                                                                   name="quantidadeExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   id="quantidadeExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   value="<?php

                                                                   echo converte_valor_estoques($Itens['QtdExercicio']);
                                                                   ?>" />
                                                            <?php

                                                        }
                                                        ?>
                                                    </td>
                                                    <td
                                                        <?php

                                                        echo $estiloClasstd;
                                                        ?>>&nbsp;
                                                        <?php
                                                        // Se for nao for agrupamento exibo o valor da tabela
                                                        if (! $isAgrupamento) {
                                                            $valorNoExercicioItem = $Itens['ValorExercicio'];
                                                            ?>
                                                            <input type="hidden"
                                                                   name="valorExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   id="valorExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   value="<?php

                                                                   echo converte_valor_estoques($valorNoExercicioItem);
                                                                   ?>" /> <span
                                                                id="spanValorExercicioItem[<?php

                                                                echo $ordem;
                                                                ?>]"><?php

                                                                echo converte_valor_estoques($valorNoExercicioItem);
                                                                ?></span>
                                                            <?php

                                                        } else {
                                                            $valorNoExercicioItem = $Itens['QtdExercicio'] * $valorEstimado;
                                                            ?>
                                                            <input type="hidden"
                                                                   name="valorExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   id="valorExercicioItem[<?php

                                                                   echo $ordem;
                                                                   ?>]"
                                                                   value="<?php

                                                                   echo converte_valor_estoques($valorNoExercicioItem);
                                                                   ?>" /> <span
                                                                id="spanValorExercicioItem[<?php

                                                                echo $ordem;
                                                                ?>]"><?php

                                                                echo converte_valor_estoques($valorNoExercicioItem);
                                                                ?></span>
                                                            <?php

                                                        }
                                                        ?>
                                                    </td>
                                                    <td
                                                        <?php

                                                        echo $estiloClasstd;
                                                        ?>>&nbsp; <span
                                                            id="spanValorDemaisExercicioItem[<?php

                                                            echo $ordem;
                                                            ?>]"><?php

                                                            echo converte_valor_estoques($valorTotalItem - $valorNoExercicioItem);
                                                            ?></span>
                                                    </td>
                                                    <?php

                                                } else {
                                                    ?>
                                                    <input type="hidden"
                                                           name="quantidadeExercicioItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           id="quantidadeExercicioItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           value="<?php

                                                           echo converte_valor_estoques($Itens['QtdExercicio']);
                                                           ?>" />
                                                    <input type="hidden"
                                                           name="valorExercicioItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           id="valorExercicioItem[<?php

                                                           echo $ordem;
                                                           ?>]"
                                                           value="<?php

                                                           echo converte_valor_estoques($Itens['ValorExercicio']);
                                                           ?>" />
                                                    <?php

                                                }
                                                ?>
                                            </tr>
                                            <!-- FIM Dados dos itens Resultado  -->
                                            <?php

                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" align="right" colspan="4"><input
                                        type="hidden" id="quantidadeTotalItens"
                                        value="<?php

                                        echo $ordem;
                                        ?>" /> <input type="button" name="Incluir"
                                                      value="Incluir" class="botao"
                                                      onClick="javascript:enviar('InserirLicitacao')"></td>
                            </tr>
                            <script type="text/javascript">
                                AtualizarValorTotal('1');
                            </script>
                            <?php

                        }
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
