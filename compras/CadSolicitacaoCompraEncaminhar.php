<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSolicitacaoCompraEncaminhar.php
# Autor:    Gladstone Barbosa
# Data:     09/02/2012
# Objetivo: Encaminhar Solicitacoes de Compra do sistema
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: José Francisco <jose.francisco@pitang.com>
# Data:     30/05/2014 	- [CR121776]: REDMINE 14 (P4)
# -------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     04/11/2014 - Ajusta exibição das SCC para encaminhamento
# -------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     11/11/2014 - Ajusta validação de encaminhamento para 
#					     usuários com o perfil corporativo poderem 
#						 encaminhar para qualquer comissão
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     09/07/2018
# Objetivo: Tarefa Redmine 194741
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     08/08/2018
# Objetivo: Tarefa Redmine 200747
# -------------------------------------------------------------------------

$programa = "CadSolicitacaoCompraEncaminhar.php";

# Acesso ao arquivo de funções #
require_once '../compras/funcoesCompras.php';

# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');

/**
 * [$db description]
 * @var [type]
 */
$db = Conexao();
/**
 * [$intCodUsuario description]
 * @var [type]
 */
$intCodUsuario = $_SESSION['_cusupocodi_'];
/**
 * [$perfilCorporativo description]
 * @var [type]
 */
$perfilCorporativo = $_SESSION['_fperficorp_'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Lista solicitações individual
function listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo = true) {
    $arrLinhas = array();

    $db = $GLOBALS["db"];

    $sql = "SELECT  SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
                    ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
                    CEN.ECENPODESC, CEN.ECENPODETA
            FROM    SFPC.TBSOLICITACAOCOMPRA AS SOL
                    JOIN SFPC.TBORGAOLICITANTE AS ORG ON SOL.CORGLICODI = ORG.CORGLICODI
                    JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO ON SOL.CSITSOCODI = SSO.CSITSOCODI
                    JOIN SFPC.TBCENTROCUSTOPORTAL AS CEN ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
            WHERE   SOL.CTPCOMCODI = 2 ";
    # Filtrando pela situação #
    if ($Situacao != "" & SoNumeros($Situacao)) {
        $sql .= "   AND SSO.CSITSOCODI = $Situacao ";
    }
    
    # Filtrando pelo órgão #
    if ($Orgao != "TODOS") {
        $sql .= "   AND ORG.CORGLICODI = " . $Orgao;
    }

    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql .= "   AND DATE(SOL.TSOLCODATA) >= '" . DataInvertida($DataIni) . "'
                    AND DATE(SOL.TSOLCODATA) <= '" . DataInvertida($DataFim) . "' ";
    }
    
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql .= "   AND SOL.CSOLCOSEQU = $strSolicitacao ";
    }

    if (isset($boolFiltrarGrupo) & $boolFiltrarGrupo) {
        $sql .= "   AND SOL.CSOLCOSEQU NOT IN (SELECT CSOLCOSEQU FROM SFPC.TBAGRUPASOLICITACAO)";
    }

    $sql .= " ORDER BY ORG.EORGLIDESC ASC, CEN.ECENPODESC, CEN.ECENPODETA, SOL.CSOLCOSEQU, SOL.ASOLCOANOS DESC ";

    $res = $db->query($sql);
    
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao']  = $Linha[0];            // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao'] = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao']        = $Linha[2];            // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao']       = $Linha[3];            // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao']     = $Linha[4];            // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao'] = $Linha[5];            // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto'] = $Linha[6];            // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto'] = $Linha[7];            // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */

            $arrLinhas[] = $linhaRetorno;
        }
    }
    return $arrLinhas;
}

function listarGrupo($Situacao, $Orgao, $DataIni, $DataFim, $CodGrupo) {
    $arrLinhasGrupo = array();
    
    $db = $GLOBALS["db"];

    $sql1 = "   SELECT  DISTINCT (AGR.CAGSOLSEQU) AS GRUPO
                FROM    SFPC.TBAGRUPASOLICITACAO AS AGR
                        JOIN SFPC.TBSOLICITACAOCOMPRA AS SOL ON AGR.CSOLCOSEQU = SOL.CSOLCOSEQU
                        JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO ON SOL.CSITSOCODI = SSO.CSITSOCODI
                        JOIN SFPC.TBORGAOLICITANTE AS ORG ON SOL.CORGLICODI = ORG.CORGLICODI
                WHERE   SOL.CTPCOMCODI = 2";
    # Filtrando pela situação #
    if ($Situacao != "" & SoNumeros($Situacao)) {
        $sql1 .= "      AND SSO.CSITSOCODI = $Situacao ";
    }
    # Filtrando pelo órgão #
    if ($Orgao != "TODOS") {
        $sql1 .= "      AND ORG.CORGLICODI = " . $Orgao;
    }

    # Filtrando pela data #
    if ($DataIni != "" and $DataFim != "") {
        $sql1 .= "      AND DATE(SOL.TSOLCODATA)  >= '" . DataInvertida($DataIni) . "'
                        AND DATE(SOL.TSOLCODATA)  <= '" . DataInvertida($DataFim) . "' ";
    }
    
    # Filtrando pelo código do grupo #
    if (isset($CodGrupo) & is_numeric($CodGrupo)) {
        $sql1 .= "      AND AGR.CAGSOLSEQU = $CodGrupo ";
    }

    $sql1 .= "          AND  AGR.FAGSOLFLAG = 'S'";

    $sql = "SELECT  SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
                    ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
                    CEN.ECENPODESC, CEN.ECENPODETA, GRU.CAGSOLSEQU,
                    GRU.FAGSOLFLAG, GRU.TAGSOLULAT
            FROM    SFPC.TBSOLICITACAOCOMPRA AS SOL
                    JOIN SFPC.TBORGAOLICITANTE AS ORG ON SOL.CORGLICODI = ORG.CORGLICODI
                    JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO ON SOL.CSITSOCODI = SSO.CSITSOCODI
                    JOIN SFPC.TBCENTROCUSTOPORTAL AS CEN ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
                    JOIN SFPC.TBAGRUPASOLICITACAO AS GRU ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU
            WHERE   GRU.CAGSOLSEQU IN ($sql1)
            ORDER BY GRU.CAGSOLSEQU, GRU.FAGSOLFLAG DESC, ORG.EORGLIDESC, SOL.CSOLCOSEQU DESC";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao']  = $Linha[0];            // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao'] = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao']        = $Linha[2];            // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao']       = $Linha[3];            // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao']     = $Linha[4];            // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao'] = $Linha[5];            // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto'] = $Linha[6];            // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto'] = $Linha[7];            // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['CodGrupo']        = $Linha[8];            // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
            $linhaRetorno['FlagGrupo']       = $Linha[9];            // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
            $linhaRetorno['DataAgrupamento'] = DataBarra($Linha[10]); // GRU.TAGSOLULAT  /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */

            $arrLinhasGrupo[] = $linhaRetorno;
        }
    }
    return $arrLinhasGrupo;
}

# Retorna o detalhamento das solicitações #
function infoDetalhamento($SeqSolicitacao) {
    $arrInfo = array();

    $db = $GLOBALS["db"];

    $sql = "SELECT  I.CITESCSEQU, I.CMATEPSEQU, I.CSERVPSEQU,
                    I.AITESCORDE, I.AITESCQTSO, I.VITESCUNIT,
                    I.VITESCVEXE, M.EMATEPDESC, S.ESERVPDESC,
                    I.EITESCDESCMAT, I.EITESCDESCSE
            FROM    SFPC.TBITEMSOLICITACAOCOMPRA I
                    LEFT JOIN SFPC.TBMATERIALPORTAL M ON (M.CMATEPSEQU = I.CMATEPSEQU)
                    LEFT JOIN SFPC.TBSERVICOPORTAL S ON (S.CSERVPSEQU = I.CSERVPSEQU)
            WHERE   I.CSOLCOSEQU = $SeqSolicitacao";

    $res = $db->query($sql);
    
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['CodSeqItens']     = $Linha[0];        //I.CITESCSEQU - Código sequencial dos itens da solicitação de compras
            $linhaRetorno['CodMaterial']     = $Linha[1];        //I.CMATEPSEQU - Código do Material
            $linhaRetorno['CodServPortal']   = $Linha[2];        //I.CSERVPSEQU - Código do Servico Portal
            $linhaRetorno['OrdItemSoli']     = $Linha[3];        //I.AITESCORDE - Ordem do item na solicitação de compras
            $linhaRetorno['QtdItemSoli']     = $Linha[4];        //I.AITESCQTSO - Quantidade do item na solicitação de compras
            $linhaRetorno['VlrUnitItem']     = $Linha[5];        //I.VITESCUNIT - Valor unitário do item (estimado / Cotado / da Ata)
            $linhaRetorno['VlrItemSoli']     = $Linha[6];        //I.VITESCVEXE - Valor no exercício do item na solicitação de compras
            $linhaRetorno['DescMaterial']    = $Linha[7];        //M.EMATEPDESC -
            $linhaRetorno['DescServico']     = $Linha[8];        //S.ESERVPDESC - Descricao do servico
            $linhaRetorno['DescDetMaterial'] = $Linha[9];        //I.EITESCDESCMAT - Desc Detalhada Material
            $linhaRetorno['DescDetServico']  = $Linha[10];       //I.EITESCDESCSE - Descricao Detalhada servico

            if ($linhaRetorno['CodMaterial'] != "") {
                $linhaRetorno['Tipo'] = "CADUM";
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

# Verifica se órgão escolhido no select Orgao é gerenciador ou não - [CR121593]: REDMINE 07 (P1) #
function verificaOrgaoGerenciador($Orgao) {
    $db = $GLOBALS["db"];

    $data = & $db->getAll(" SELECT  DISTINCT SOL.CORGLICODI
                            FROM    SFPC.TBSOLICITACAOCOMPRA AS SOL
                                    JOIN SFPC.TBORGAOLICITANTE AS ORG ON SOL.CORGLICODI = ORG.CORGLICODI
                                    JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO ON SOL.CSITSOCODI = SSO.CSITSOCODI
                                    JOIN SFPC.TBCENTROCUSTOPORTAL AS CEN ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
                                    JOIN SFPC.TBAGRUPASOLICITACAO AS GRU ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU
                            WHERE   GRU.FAGSOLFLAG = 'S'
                            ORDER BY SOL.CORGLICODI ASC", array(), DB_FETCHMODE_ORDERED);

    if (PEAR::isError($data)) {
        $CodErroEmail = $data->getCode();
        $DescErroEmail = $data->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        # Deixa o array multidimensional dos órgãos gerenciadores para um dimensional #
        $final_array = array();
        foreach ($data as $val) {
            foreach ($val as $val2) {
                $final_array[] = $val2;
            }
        }
        # Verifica no array de órgãos gerenciadores se o órgão escolhido é gerenciador #
        if ((in_array($Orgao, $final_array)) || ($Orgao == 'TODOS')) {
            return true;
        }
        return false;
    }
}

function exibeDetalhamento($SeqSolicitacao) { ?>
    <!-- INÍCIO DO DETALHAMENTO DA SOLICITAÇÃO -->
    <tr style="display:none;" class="opdetalhe <?php echo $SeqSolicitacao; ?>">
        <td style="background-color:#F1F1F1;" colspan="4">
            <table bordercolor="#75ADE6" border="1" bgcolor="bfdaf2" width="100%" class="textonormal">
                <tr>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO</td>
                        <?php   foreach ((infoDetalhamento($SeqSolicitacao)) as $ItensDesc): ?>
                        <?php   if (!empty($ItensDesc['DescDetMaterial'])): ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO DETALHADA</td>
                        <?php   $exibeTd = true;
                                break;
                                endif;
                                endforeach; ?>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">TIPO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">CÓD.RED</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">QUANTIDADE</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR ESTIMADO</td>
                    <td class="textoabason" align="center" bgcolor="#DCEDF7">VALOR TOTAL</td>
                </tr>
                <?php   $arrayDetalhamento = infoDetalhamento($SeqSolicitacao);
                        foreach ($arrayDetalhamento as $itens) { ?>
                <tr>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['OrdItemSoli']; ?></td>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescMaterial']; ?></td>
                        <?php if ($exibeTd) : ?>
                        <?php if (!empty($itens['DescDetMaterial'])): ?>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescDetMaterial']; ?></td>
                        <?php else: ?>
                    <td headers="descdet" align="center">-</td>
                        <?php endif; ?>
                        <?php endif; ?>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['Tipo']; ?></td>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['CodMaterial']; ?></td>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_quant($itens['QtdItemSoli']); ?></td>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['VlrUnitItem']); ?></td>
                    <td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['QtdItemSoli'] * $itens['VlrUnitItem']); ?></td>
                </tr>
                <?php   } ?>      
            </table>
        </td>
    </tr>
    <!-- FIM DO DETALHAMENTO DA SOLICITAÇÃO --> <?php
}

$Orgao = '';

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao                        = filter_input(INPUT_POST, 'Botao');
    $DataIni                      = filter_input(INPUT_POST, 'DataIni');
    $Orgao                        = filter_input(INPUT_POST, 'Orgao');
    $Situacao                     = filter_input(INPUT_POST, 'Situacao');
    $DataFim                      = filter_input(INPUT_POST, 'DataFim');
    $idSolicitacao                = $_POST['idSolicitacao'];
    $tipoSelecao                  = $_POST['tipoSelecao'];
    $ComissaoLicitacao            = $_POST['ComissaoLicitacao'];
    $CodSolicitacaoPesquisaDireta = $_POST['CodSolicitacaoPesquisaDireta'];
    $orgaoSelected                = $_POST['orgaoSelected'][$idSolicitacao];
    $SeqSolicitacao               = filter_input(INPUT_POST, 'SeqSolicitacao', FILTER_SANITIZE_NUMBER_INT);

    if (empty($idSolicitacao) && !empty($SeqSolicitacao)) {
        $idSolicitacao = $SeqSolicitacao;
    }

    if ($DataIni != "") {
        $DataIni = FormataData($DataIni);
    }

    if ($DataFim != "") {
        $DataFim = FormataData($DataFim);
    }

    if (isset($Botao, $Orgao, $Situacao, $DataIni, $DataFim)) {
        $_SESSION['Botao']    = $Botao;
        $_SESSION['Orgao']    = $Orgao;
        $_SESSION['Situacao'] = $Situacao;
        $_SESSION['DataIni']  = $DataIni;
        $_SESSION['DataFim']  = $DataFim;
    }
} else {
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens = $_GET['Mens'];
    $Tipo = $_GET['Tipo'];

    $_SESSION['Orgao']    = '';
    $_SESSION['Situacao'] = '';
    $_SESSION['DataIni']  = '';
    $_SESSION['DataFim']  = '';
}

if ($Botao == 'Voltar') {
    $_SESSION["carregarSelecionarDoSession"] = true;
} elseif ($Botao == "Imprimir") {
    $Url = "RelAcompanhamentoSCCPdf.php?Solicitacao=" . $idSolicitacao;
    header("location: " . $Url);
    exit;
}

if ($_SESSION["carregarSelecionarDoSession"]) {
    $Botao    = $_SESSION['Botao'];
    $Orgao    = $_SESSION['Orgao'];
    $Situacao = $_SESSION['Situacao'];
    $DataIni  = $_SESSION['DataIni'];
    $DataFim  = $_SESSION['DataFim'];
    $_SESSION["carregarSelecionarDoSession"] = false;
}

if ($Botao == "Limpar") {
    header("location: " . $programa);
    exit;
}

# Aauxilia na mudança de exibição dos botões e do select de comissão de licitação #
$EncaminharPara = false;

# Auxilia na chamada do metodo para consultar as solicitações #
$pesquisa = false;

# auxilia o botao confirmarEncaminhamento para confirmar o encaminhamento #
$Confirmado = false;

if ($Botao == "EncaminharPara") {
    if (isset($idSolicitacao)) {
        $aux = explode("-", $idSolicitacao);
        $strSolicitacao = $aux[0];
        $FlagTipo = $aux[1];
        $EncaminharPara = true;
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Selecione uma solicitação para encaminhar </a>";
        $EncaminharPara = false;
    }
    $pesquisa = true;
}

if ($Botao == "confirmarEncaminhamento") { # Alterar solicitação para: "encaminhada" #
    if (isset($idSolicitacao)) {
        $aux = explode("-", $idSolicitacao);
        $strSolicitacao = $aux[0];
        $FlagTipo = $aux[1];
        $usuarioPerfilCorporativo = $_SESSION['_fperficorp_'];
        $Confirmado = true;
        
        if ($FlagTipo != 'G' || ($FlagTipo == 'G' && $usuarioPerfilCorporativo != 'S')) {
	        if ($ComissaoLicitacao != "") {
	            # Verificando existe o grupo da comissao escolhida com o orgao da solicitação selecionada #
	            $sql = "SELECT cgrempcodi from sfpc.tbcomissaolicitacao where ccomlicodi = $ComissaoLicitacao ";
	
	            $res = $db->query($sql);
                
                if (PEAR::isError($res)) {
	                $CodErroEmail = $res->getCode();
	                $DescErroEmail = $res->getMessage();
	                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
	            } else {
	                $intGrupoComissao = resultValorUnico(executarSQL($db, $sql));
	            }
                # Alterado para resolver temporariamente o problema de encaminhar uma SCC para comissão com outro grupo associado (CR 200747) #                
                if ($usuarioPerfilCorporativo != 'S') {
                    # Pegando o grupo da comissão #
	                $sql = "SELECT COUNT(*) FROM sfpc.tbgrupoorgao WHERE cgrempcodi = $intGrupoComissao AND corglicodi = $orgaoSelected ";
	
	                if (PEAR::isError($res)) {
	                    $CodErroEmail = $res->getCode();
	                    $DescErroEmail = $res->getMessage();
	                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
	                } else {
	                    $intQuantidadeGrupoOrgao = resultValorUnico(executarSQL($db, $sql));
                        
                        if ($intQuantidadeGrupoOrgao <= 0) {
	                        $Confirmado = false;
	                        $Mens = 1;
	                        $Tipo = 2;
	                        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">O grupo da comissão selecionado não está relacionado com o orgão da solicitação.</a>";
	                        $EncaminharPara = true;
	                        $pesquisa = true;
	                    }
                    }
                }
	        } else {
	            $Confirmado = false;
	            $Mens = 1;
	            $Tipo = 2;
	            $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Selecione uma Comissão de Licitação </a>";
	            $EncaminharPara = true;
	            $pesquisa = true;
	        }
		}
    } else {
        $Confirmado = false;
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Nenhuma solicitação ou Grupo selecionado</a>";
        $EncaminharPara = true;
        $pesquisa = true;
    }
}

if ($Confirmado) {
    if ($FlagTipo == "I") {
        $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                SET     CSITSOCODI = 8,
                        CUSUPOCODI = $intCodUsuario,
                        TSOLCOULAT = now(),
                        CCOMLICOD1 = $ComissaoLicitacao
                WHERE   CSOLCOSEQU = $strSolicitacao";

        $res = executarTransacao($db, $sql);
        
        if (PEAR::isError($res)) {
            $CodErroEmail = $res->getCode();
            $DescErroEmail = $res->getMessage();
            var_export($DescErroEmail);
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        }

        $sql = "INSERT  INTO SFPC.TBHISTSITUACAOSOLICITACAO
                        (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI)
                VALUES  ($strSolicitacao, now(), 8, $intCodUsuario)";

        $res = executarTransacao($db, $sql);
        
        if (PEAR::isError($res)) {
            $CodErroEmail = $res->getCode();
            $DescErroEmail = $res->getMessage();
            var_export($DescErroEmail);
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        }
    } elseif ($FlagTipo == "G") {

        $sql = "SELECT  DISTINCT (AGR.CSOLCOSEQU) AS SOLICITACAO
                FROM    SFPC.TBAGRUPASOLICITACAO AS AGR
                JOIN    SFPC.TBSOLICITACAOCOMPRA AS SOL ON AGR.CSOLCOSEQU = SOL.CSOLCOSEQU
                WHERE   AGR.CAGSOLSEQU = $strSolicitacao";

        $res = $db->query($sql);
        
        if (PEAR::isError($res)) {
            $CodErroEmail = $res->getCode();
            $DescErroEmail = $res->getMessage();
            var_export($DescErroEmail);
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        } else {
            while ($Linha = $res->fetchRow()) {
                $strSolicitacao = $Linha[0];

                $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                        SET     CSITSOCODI = 8,
                                CUSUPOCODI = $intCodUsuario,
                                TSOLCOULAT = now(),
                                CCOMLICOD1 = $ComissaoLicitacao
                        WHERE   CSOLCOSEQU = $strSolicitacao";

                $resultado = executarTransacao($db, $sql);
                
                if (PEAR::isError($resultado)) {
                    $CodErroEmail = $resultado->getCode();
                    $DescErroEmail = $resultado->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }

                $sql = "INSERT  INTO SFPC.TBHISTSITUACAOSOLICITACAO
                                (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI)
                        VALUES  ($strSolicitacao, now(), 8, $intCodUsuario)";

                $resultado = executarTransacao($db, $sql);
                
                if (PEAR::isError($resultado)) {
                    $CodErroEmail = $resultado->getCode();
                    $DescErroEmail = $resultado->getMessage();
                    var_export($DescErroEmail);
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }
            }
        }
    }
    finalizarTransacao($db);

    $Mens = 1;
    $Tipo = 1;
    $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Solicitação Encaminhada com Sucesso </a>";
    
    $strSolicitacao = "";
    $FlagTipo = "";
    $EncaminharPara = false;
    $pesquisa = false;
}

if ($Botao == "VoltarParaEncaminhamento") { # Voltar solicitação para: "para encaminhamento" #
    if (isset($idSolicitacao)) {
        $aux = explode("-", $idSolicitacao);
        $strSolicitacao = $aux[0];
        $FlagTipo = $aux[1];

        if ($FlagTipo == "I") {
            $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                    SET     CSITSOCODI = 7,
                            CUSUPOCODI = $intCodUsuario,
                            TSOLCOULAT = now()
                    WHERE   CSOLCOSEQU = $strSolicitacao";

            $res = executarTransacao($db, $sql);
            
            if (PEAR::isError($res)) {
                $CodErroEmail = $res->getCode();
                $DescErroEmail = $res->getMessage();
                var_export($DescErroEmail);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }

            $sql = "INSERT  INTO SFPC.TBHISTSITUACAOSOLICITACAO
                            (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI, THSITSULAT)
                    VALUES  ($strSolicitacao, now(), 7, $intCodUsuario, now())";

            $res = executarTransacao($db, $sql);
            
            if (PEAR::isError($res)) {
                $CodErroEmail = $res->getCode();
                $DescErroEmail = $res->getMessage();
                var_export($DescErroEmail);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }
        } elseif ($FlagTipo == "G") {
            $sql = "SELECT  DISTINCT (AGR.CSOLCOSEQU) AS SOLICITACAO
                    FROM    SFPC.TBAGRUPASOLICITACAO AS AGR
                            JOIN SFPC.TBSOLICITACAOCOMPRA AS SOL ON AGR.CSOLCOSEQU = SOL.CSOLCOSEQU
                    WHERE   AGR.CAGSOLSEQU = $strSolicitacao";

            $res = $db->query($sql);

            if (PEAR::isError($res)) {
                $CodErroEmail = $res->getCode();
                $DescErroEmail = $res->getMessage();
                var_export($DescErroEmail);
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            } else {
                while ($Linha = $res->fetchRow()) {
                    $strSolicitacao = $Linha[0];
                    $sql = "UPDATE  SFPC.TBSOLICITACAOCOMPRA
                            SET     CSITSOCODI = 7,
                                    CUSUPOCODI = $intCodUsuario,
                                    TSOLCOULAT = now()
                            WHERE   CSOLCOSEQU = $strSolicitacao";

                    $resultado = executarTransacao($db, $sql);
                    
                    if (PEAR::isError($resultado)) {
                        $CodErroEmail = $resultado->getCode();
                        $DescErroEmail = $resultado->getMessage();
                        var_export($DescErroEmail);
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    }

                    $sql = "INSERT  INTO SFPC.TBHISTSITUACAOSOLICITACAO
                                    (CSOLCOSEQU, THSITSDATA, CSITSOCODI, CUSUPOCODI, THSITSULAT)
                            VALUES  ($strSolicitacao, now(), 7, $intCodUsuario, now())";

                    $resultado = executarTransacao($db, $sql);
                    
                    if (PEAR::isError($resultado)) {
                        $CodErroEmail = $resultado->getCode();
                        $DescErroEmail = $resultado->getMessage();
                        var_export($DescErroEmail);
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    }
                }
            }
        }
        finalizarTransacao($db);

        $Mens = 1;
        $Tipo = 1;
        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Solicitação alterada para 'Para Encaminhamento'.</a>";
        
        $strSolicitacao = "";
        $FlagTipo = "";
        $pesquisa = false;
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Selecione uma solicitação para voltar situação de 'Para Encaminhamento' </a>";
        
        $pesquisa = true;
    }
}

if ($Botao == "Pesquisar" || $Botao == "Voltar") {
    $pesquisa = true;
}

if ($pesquisa) {
    # Critica dos Campos #
    $MensErro = ValidaPeriodo($DataIni, $DataFim, $Mens, "formulario");
    
    if ($MensErro != "") {
        adicionarMensagem("<a href='javascript:formulario.Justificativa.focus();' class='titulo2'>$MensErro</a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
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
            $Mensagem .= "<a href=\"javascript:document." . $Programa . ".DataIni.focus();\" class=\"titulo2\">Data Inicial maior que a Data Atual</a><br>";
            $pesquisa = false;
        }
    }

    if ($Situacao == "") {
        $Mens = 1;
        $Tipo = 2;
        if ($Mensagem != "") {
            $Mensagem .= "Informe: ";
        } else {
            $Mensagem .= "Informe: ";
        }

        $Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Situação</a><br>";
        $pesquisa = false;
    }

    if ($Orgao == "") {
        $Mens = 1;
        $Tipo = 2;
        if ($Mensagem != "") {
            $Mensagem .= "Informe: ";
        } else {
            $Mensagem .= "Informe: ";
        }
        $Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Orgão</a>";
        $pesquisa = false;
    }

    if ($pesquisa) {
        if ($FlagTipo != "G") {
            $arrLinhas = listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao);
        }
        if ($perfilCorporativo == "S" & $FlagTipo != "I") {
            $arrLinhasGrupo = listarGrupo($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao);
        }
    }
}
?>
<html>
<?php   # Carrega o layout padrão #
        layout(); ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <!--
        function enviar(valor)
        {
        var validacao = true;
        if (valor == "VoltarParaEncaminhamento") {
        var radiositens = document.getElementsByName('idSolicitacao');
        var ischecked = false;
        for (var i = 0; i < radiositens.length; i++) {
        if (radiositens[i].checked) {
        ischecked  = true;
        }
        }
        if (ischecked) {
        var validacao = confirm("Deseja confirmar a operação e alterar a situação da solicitação para “em encaminhamento”?");
        } else {
        validacao = true;
        }
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
            $(document).ready(function() {
                $(".detalhar").live("click", function() {
                    var seq = $(this).attr("id");
                    var valAtual = $(this).html();
                    if (valAtual == "+") {
                        $(this).html("-");
                        $(".opdetalhe." + seq).show();
                    } else {
                        $(this).html("+");
                        $(".opdetalhe." + seq).hide();
                    }
                });
            });
        </script>
        <form action="<?= $programa ?>" method="post" name="formulario">
            <br><br><br><br><br>
            <table width="100%" cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Solicitação > Encaminhar
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
                <?php if ($Mens == 1) { ?>
                <tr>
                    <td width="150"></td>
                    <td align="left" colspan="2"><?php ExibeMens($Mensagem, $Tipo, 1); ?></td>
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
                                ENCAMINHAR - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)</td>
                            </tr>
                        <tr>
                            <td align="left" valign="middle" colspan="4">Preencha os dados abaixo e clique no botão pesquisar para listar as solicitações.</td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão*</td>
                                        <td class="textonormal">
                                            <select name="Orgao" class="textonormal">
                                                <option value="">Selecione um Órgao...</option>
                                                <?php   if ($perfilCorporativo == "S") { ?>
                                                <option <?php   if ($Orgao == "TODOS") {
                                                echo "selected='selected'";
                                                                } ?> value="TODOS">Todos</option>
                                                <?php   }
                                                        $sql = "SELECT  ORG.CORGLICODI, ORG.EORGLIDESC
                                                                FROM    SFPC.TBORGAOLICITANTE ORG
                                                                WHERE   ORG.FORGLISITU = 'A'"; # Todos os órgãos ativos #
                                                        # Se o perfil não for corporativo ele só mostra as licitações da comisao do usuario logado #
                                                        if ($perfilCorporativo != "S") {
                                                            $sql .= " AND ORG.CORGLICODI IN ( SELECT CORGLICODI FROM sfpc.tbgrupoorgao WHERE cgrempcodi = " . $_SESSION["_cgrempcodi_"] . " ) ";
                                                        }

                                                        $sql .= " ORDER BY ORG.EORGLIDESC";

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
                                                        } ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
                                        <td class="textonormal">
                                            <select name="Situacao" class="textonormal">
                                                <?php   # Select Situação: Para Encaminhamento, Encaminhada (7 ou 8) #
                                                        $sql = "SELECT  CSITSOCODI, ESITSONOME
                                                                FROM    SFPC.TBSITUACAOSOLICITACAO
                                                                WHERE   CSITSOCODI = 7 OR CSITSOCODI = 8
                                                                ORDER BY ESITSONOME desc";

                                                        $res = $db->query($sql);

                                                        if (PEAR::isError($res)) {
                                                            $CodErroEmail = $res->getCode();
                                                            $DescErroEmail = $res->getMessage();
                                                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                                        } else {
                                                            while ($Linha = $res->fetchRow()) {
                                                                $selected = "selected = 'selected'" ? ( $Situacao == $Linha[0]) : "";
                                                                echo "<option $selected value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            }
                                                        }
                                                        $_SESSION['Situacao'] = $Situacao; ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
                                        <td class="textonormal">
                                            <?php   # Período Data #
                                                    $DataMes = DataMes();
                                                    
                                                    if ($DataIni == "") {
                                                        $DataIni = $DataMes[0];
                                                    }
                                                    
                                                    if ($DataFim == "") {
                                                        $DataFim = $DataMes[1];
                                                    }
                                                    
                                                    $URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
                                                    $URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim"; ?>
                                            <input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni; ?>" class="textonormal">
                                            <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                            &nbsp;a&nbsp;
                                            <input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim; ?>" class="textonormal">
                                            <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal" align="right" colspan="4">
                                <input type="button" name="PesquisaGeral" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
                                <input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
                                <input type="hidden" name="Botao" value="">
                            </td>
                        </tr>
                    </table>
                    <table width="100%" border="1" summary="" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF">
                      <?php if ($pesquisa) { ?>
                            <tr>
                                <td style="border-top: 0px;" align="center" bgcolor="#75ADE6" colspan="4" class="titulo3">RESULTADO DA PESQUISA</td>
                            </tr>
                        <?php   $QtdRegistros      = count($arrLinhas);
                                $QtdRegistrosGrupo = count($arrLinhasGrupo);

                                if ($QtdRegistros > 0 || ($QtdRegistrosGrupo > 0 && (verificaOrgaoGerenciador($Orgao)))) {
                                    # Verifica tambem se órgão escolhido é gerenciador ou 'TODOS' #
                                    $DescricaoOrgao = "";
                                    $DescricaoCentroCusto = "";

                                    if ($QtdRegistros > 0) {
                                        foreach ($arrLinhas as $linhas) { ?>
                                        <!-- INÍCIO SOLICITAÇÃO INDIVIDUAL -->
                                        <?php
                                        if ($DescricaoOrgao != $linhas['DescOrgao']) { ?>
                                            <tr class="linhaorgao">
                                                <td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3"><?php echo $linhas['DescOrgao']; ?></td>
                                            </tr>
                                            <?php   $DescricaoOrgao = $linhas['DescOrgao'];
                                        }
                                        if ($DescricaoCentroCusto != $linhas['DescCentroCusto']) { ?>
                                            <tr class="linhacentro">
                                                <td align="center" bgcolor="#DDECF9" colspan="5" class="titulo3"><?php echo $linhas['DescCentroCusto']; ?></td>
                                            </tr>
                                            <tr class="linhainfo">
                                                <td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
                                                <td class="titulo3" bgcolor="#F7F7F7">DETALHAMENTO</td>
                                                <td class="titulo3" bgcolor="#F7F7F7">DATA</td>
                                                <td class="titulo3" bgcolor="#F7F7F7">SITUAÇÃO</td>
                                            </tr>
                                        <?php   $DescricaoCentroCusto = $linhas['DescCentroCusto'];
                                        }
                                        
                                        $programaSelecao = "ConsAcompSolicitacaoCompra.php";
                                        $Url = $programaSelecao . "?SeqSolicitacao=" . $linhas['SeqSolicitacao'] . "&programa=" . $programa;
                                        $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']);
                                        $checked = !empty($strSolicitacao) ? "checked='checked'" : ""; ?>
                                <tr class="linhasol">
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                        <input type="hidden" name="orgaoSelected[<?php echo $linhas['SeqSolicitacao'] . '-I'; ?>]" value="<?php echo $linhas['CodOrgao']; ?>" />
                                        <input <?php echo $checked; ?> type="radio" class="idSolicitacao soli" name="idSolicitacao" value="<?php echo $linhas['SeqSolicitacao'] . '-I'; ?>" />
                                            <a href="<?php echo $Url; ?>">
                                                <font color="#000000"><?php echo $strSolicitacaoCodigo; ?></font>
                                            </a>
                                        <span style="cursor:pointer;margin-left:5px;margin-right:10px;" id="<?php echo $linhas['SeqSolicitacao']; ?>" class="detalhar" onclick="">+</span>
                                    </td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DetaCentroCusto']; ?></td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DataSolicitacao']; ?></td>
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DescSolicitacao']; ?></td>
                                </tr>
                                <!-- FIM SOLICITAÇÃO INDIVIDUAL -->
                                <?php   # Exibe a tabela de detalhamento de cada solicitação
                                        exibeDetalhamento($linhas['SeqSolicitacao']);
                                        } # Fim do Foreach Individual #
                                    }

                                    if ($QtdRegistrosGrupo > 0) {
                                        $contagemGrupo = 0;
                                        $DescricaoOrgao = "";
                                        
                                        foreach ($arrLinhasGrupo as $linhas) {
                                            if ($DescricaoOrgao != $linhas['DescOrgao'] & $linhas['FlagGrupo'] == "S") { ?>
                                    
                                <tr class="linhaorgao">
                                    <td align="center" bgcolor="#BFDAF2" colspan="5" class="titulo3"><?php echo $linhas['DescOrgao']; ?></td>
                                </tr>
                                
                                <!-- INÍCIO SOLICITAÇÕES AGRUPADAS -->
                                <?php   }

                                        if ($linhas['FlagGrupo'] == "S") {
                                            $contagemGrupo++;
                                            $checked = "checked='checked'" ? ($strSolicitacao != "") : ""; ?>
                                    <td align="left" bgcolor="#BFDAF2" colspan="5" class="titulo3">
                                        <input <?php echo $checked; ?> type="radio" class="idSolicitacao" name="idSolicitacao" value="<?php echo $linhas['CodGrupo'] . '-G'; ?>" />
                                            <?php //echo $contagemGrupo;?>Agrupamento - DATA: <?php echo $linhas['DataAgrupamento']; ?>
                                        <input type="hidden" name="orgaoSelected[<?php echo $linhas['CodGrupo'] . '-G'; ?>]" value="<?php echo $linhas['CodOrgao']; ?>" />
                                    </td>
                                    <tr class="linhainfo">
                                        <td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
                                        <td class="titulo3" bgcolor="#F7F7F7">DETALHAMENTO</td>
                                        <td class="titulo3" bgcolor="#F7F7F7">DATA</td>
                                        <td class="titulo3" bgcolor="#F7F7F7">SITUAÇÃO</td>
                                    </tr>
                                    <?php   $DescricaoOrgao = $linhas['DescOrgao'];
                                        }

                                        $programaSelecao = "ConsAcompSolicitacaoCompra.php";
                                        $Url = $programaSelecao . "?SeqSolicitacao=" . $linhas['SeqSolicitacao'] . "&programa=" . $programa;
                                        $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $linhas['SeqSolicitacao']); ?>

                                <!-- Onde aparece as solicitações agrupadas -->
                                <tr>
                                    <!-- Número da SCC -->
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal">
                                        <a href="<?php echo $Url; ?>">
                                            <font color="#000000"><?php echo $strSolicitacaoCodigo; ?></font>
                                        </a>
                                        <span style="cursor:pointer;margin-left:5px;margin-right:10px;" id="<?php echo $linhas['SeqSolicitacao']; ?>" class="detalhar" onclick="">+</span>
                                    </td>
                                    <!-- Descrição do órgão -->
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DescOrgao']; ?></td>
                                    <!-- Data -->
                                    <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?php echo $linhas['DataSolicitacao']; ?></td>
                                    <td class="textonormal" valign="top" bgcolor="#F7F7F7"><?php echo $linhas['DescSolicitacao']; ?></td>
                                </tr>
                                <!-- FIM SOLICITAÇÕES AGRUPADAS -->
                                <?php   # Exibe a tabela de detalhamento de cada solicitação #
                                        exibeDetalhamento($linhas['SeqSolicitacao']);
                                        }//Fim do Foreach Grupo
                                    }  ?>
                                <!-- Linha de Botões -->
                                <?php   if ($Situacao == 7) {
                                            if ($EncaminharPara) { ?>
                                <!-- Botão Encaminhar para  -->
                                <tr>
                                    <td align="left" valign="middle" colspan="4">
                                        <label style="float:left; display:block;" bgcolor="#BFDAF2" class="textonormal">Encaminhar para:*</label>
                                            <select style="float:left;" name="ComissaoLicitacao" class="textonormal">
                                                <option value="">Selecione uma comissão de licitação...</option>
                                                    <?php   $sql = "SELECT  CCOMLICODI, ECOMLIDESC
                                                                    FROM    SFPC.TBCOMISSAOLICITACAO
                                                                    WHERE   FCOMLISTAT = 'A' ";
                                                    
                                                    # Se o perfil não for corporativo ele só mostra as licitações da comisao do usuario logado
                                                    if ($perfilCorporativo != "S") {
                                                            $sql .= " AND CCOMLICODI IN ( SELECT ccomlicodi FROM sfpc.tbusuariocomis WHERE cgrempcodi = " . $_SESSION["_cgrempcodi_"] . " ) ";
                                                    }

                                                    $sql .= " ORDER BY ECOMLIDESC ASC";

                                                    $res = $db->query($sql);

                                                    if (PEAR::isError($res)) {
                                                        $CodErroEmail = $res->getCode();
                                                        $DescErroEmail = $res->getMessage();
                                                        var_export($DescErroEmail);
                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                                                    } else {
                                                        while ($Linha = $res->fetchRow()) {
                                                            if ($Linha[0] == $Comissao) {
                                                                echo "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            } else {
                                                                echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
                                                            }
                                                        }
                                                    } ?>
                                            </select>
                                    </td>
                                </tr>
                                <?php       }
                                        } ?>
                                <tr>
                                    <td class="textonormal" align="right" colspan="4">
                                        <?php   if ($Situacao == 7) {
                                                    if ($EncaminharPara) { ?>
                                                        <input type="button" name="confirmarEncaminhamento" value="Confirmar Encaminhamento" class="botao" onclick="javascript:enviar('confirmarEncaminhamento')"/>
                                                        <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Pesquisar')">
                                        <?php   } else { ?>
                                                    <input type="button" name="EncaminharPara" value="Encaminhar Para" class="botao" onclick="javascript:enviar('EncaminharPara')"/>
                                        <?php   }
                                            } elseif ($Situacao == 8) {
                                                if (!$EncaminharPara) { ?>
                                                    <input type="button" name="VoltarParaEncaminhamento" value="Voltar Situação 'Para Encaminhamento'" class="botao" onclick="javascript:enviar('VoltarParaEncaminhamento')"/>
                                        <?php   }
                                            } ?>
                                    </td>
                                </tr>
                        <?php   } else { ?>
                                <tr>
                                    <td align="left" colspan="4" class="textonormal">Pesquisa sem Ocorrências.</td>
                                </tr>
                        <?php   } //Fim do if QtdRegistros
                            } //Fim do if boolean pesquisar ?>
                    </table>
                </td>
            </tr>
            <!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect(); ?>
</html>
