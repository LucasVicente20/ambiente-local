<?php

#-------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     17/07/2014 [CR123141]: REDMINE 23 (P4)
#-------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:     27/08/2014 [CR123141]: REDMINE 23 (P4)
#-------------------------------------------------------------------------
# Alterado: Daniel Semblano <daniel.semblano@pitang.com>
# Data:     17/09/2014 [CR123141]: REDMINE 23
#-------------------------------------------------------------------------

require_once "../compras/funcoesCompras.php";
# Abrindo Conexão
if (!isset($db)) {
    $db = Conexao();
}
if (!isset($dbOracle)) {
    $dbOracle = ConexaoOracle();
}

#Calcula Valor TRP
function calculaValorTrp($intSeqMaterial)
{
    $db            = $GLOBALS["db"];

    return calcularValorTrp($db, TIPO_COMPRA_LICITACAO, $intSeqMaterial);
}

#Calcula Limite de Compra
function calculaLimiteCompra($intOrgaoLicitante, $intModalidade, $arrItens, $arrTipos)
{
    assercao(!is_null($intOrgaoLicitante), "Orgão Licitante não informado");
    assercao(!is_null($intModalidade), "Modalidade não informada");
    assercao(!is_null($arrItens), "Arr de itens não informado");
    assercao(!is_null($arrTipos), "Arr de Tipos não indormados");

    $db            = $GLOBALS["db"];
    //verificar se o órgão licitante é da administração direta ou indireta
    $sql = "SELECT FORGLITIPO FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $intOrgaoLicitante";
    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $strTipoAdmistracao  = resultValorUnico(executarSQL($db, $sql));
    }

    $boolTipoObra = false;
    //verificar se as SCC são do tipo obras , se apenas uma for consulto em obras
    if (count($arrItens)>0) {
        foreach ($arrItens as $key => $item) {
            if ($arrTipos[$key] != "CADUM") {
                $sql = " SELECT COUNT(GRUPO.CGRUMSCODI) FROM SFPC.TBGRUPOSUBELEMENTODESPESA GRUPO , SFPC.TBSERVICOPORTAL SER
							 WHERE GRUPO.CGRUSEELE1 = 4
							 AND   GRUPO.CGRUSEELE2 = 4
							 AND   GRUPO.CGRUSEELE3 = 90
							 AND   GRUPO.CGRUSEELE4 = 51
							 AND   GRUPO.CGRUMSCODI = SER.CGRUMSCODI
							 AND   SER.CSERVPSEQU = $item ";
            } else {
                $sql = "SELECT COUNT(GRUPO.CGRUMSCODI)
						FROM SFPC.TBGRUPOSUBELEMENTODESPESA GRUPO ,
						SFPC.TBCLASSEMATERIALSERVICO CLA,
						SFPC.TBSUBCLASSEMATERIAL SUB,
					    SFPC.TBMATERIALPORTAL MAT
							WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI
							AND SUB.CCLAMSCODI = CLA.CCLAMSCODI
							AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU
							AND GRUPO.CGRUMSCODI = CLA.CGRUMSCODI
							AND GRUPO.CGRUSEELE1 = 4
							AND GRUPO.CGRUSEELE2 = 4
							AND GRUPO.CGRUSEELE3= 90
							AND GRUPO.CGRUSEELE4=51
							AND MAT.CMATEPSEQU = $item ";
            }

            $qtdGrupo = resultValorUnico(executarSQL($db, $sql));
            if ($qtdGrupo>0) {
                $boolTipoObra = true;
            }
        }
    }

    $sql = " select VLICOMOBRA , VLICOMSERV FROM SFPC.TBLIMITECOMPRA where cmodlicodi = $intModalidade and flicomtipo = '$strTipoAdmistracao'";

    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $LinhaValor = $result->fetchRow();
    }

    if ($boolTipoObra) {
        if (is_null($LinhaValor[0])) {
            return "0";
        } else {
            return $LinhaValor[0];
        }
    } else {
        if (is_null($LinhaValor[1])) {
            return "0";
        } else {
            return $LinhaValor[1];
        }
    }
}

# LISTA SOLICITAÇÕES INDIVIDUAL
function listarIndividual($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo = true, $filtrarPelaComissaoUsuario = false)
{
    $arrLinhas = array();

    $db            = $GLOBALS["db"];
    // 	$Situacao 		= $GLOBALS["Situacao"];
    // 	$Orgao 			= $GLOBALS["Orgao"];
    // 	$DataIni  		= $GLOBALS["DataIni"];
    // 	$DataFim  		= $GLOBALS["DataFim"];
    // 	$strSolicitacao = $GLOBALS["strSolicitacao"];

    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario    = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res  = $db->query($sqlComiss);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }
    //removendo o filtro para testes
    //unset($comissaoLicitacao);


     $sql = "SELECT
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT, ITEM.EITESCDESCMAT, ITEM.EITESCDESCSE
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
	JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
    JOIN SFPC.TBITEMSOLICITACAOCOMPRA AS ITEM
            ON SOL.CSOLCOSEQU = ITEM.CSOLCOSEQU
	WHERE
		SOL.CTPCOMCODI = 2
		";

    if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao)>0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }
    //Filtrando Pela Situação
    if ($Situacao != ""&SoNumeros($Situacao)) {
        $sql .= " AND SSO.CSITSOCODI = $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql .= " AND ORG.CORGLICODI = ".$Orgao;//SOL
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
        $SeqSolicitacao = "";
        $DataSolicitacao = "";
        $CodSituacao = "";
        $CodComissaoLici = "";
        $TipoRegistroPreco = "";
        $FlagGeraContrato = "";

        while ($Linha = $res->fetchRow()) {
            /* OLD - [CR123141]: REDMINE 23 (P4)
             if (
                ($SeqSolicitacao != $Linha[0]) &&
                ($DataSolicitacao != $Linha[1]) &&
                ($CodSituacao != $Linha[4]) &&
                ($CodComissaoLici != $Linha[8]) &&
                ($TipoRegistroPreco != $Linha[10]) &&
                ($FlagGeraContrato != $Linha[12])

            )
             */

            if (
                ($SeqSolicitacao != $Linha[0]) &&
                ($DataSolicitacao != $Linha[1]) &&
                ($CodSituacao != $Linha[4]) &&
                ($CodComissaoLici != $Linha[8]) &&
                (($TipoRegistroPreco != $Linha[10]) || ($FlagGeraContrato != $Linha[12]))

            ) {
                $linhaRetorno['SeqSolicitacao']    = $Linha[0];             // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['DataSolicitacao']    = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['CodOrgao']            = $Linha[2];             // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
                $linhaRetorno['DescOrgao']            = $Linha[3];             // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
                $linhaRetorno['CodSituacao']        = $Linha[4];             // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
                $linhaRetorno['DescSolicitacao']    = $Linha[5];             // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
                $linhaRetorno['DescCentroCusto']    = $Linha[6];             // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['DetaCentroCusto']    = $Linha[7];             // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['CodComissaoLici']    = $Linha[8];             // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['DescComissaoLici']    = $Linha[9];             // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['TipoRegistroPreco']    = $Linha[10];             // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
                $linhaRetorno['ObjetoSolicitacao']    = $Linha[11];             // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['FlagGeraContrato']    = $Linha[12];             // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */
                $linhaRetorno['DescDetaMat']        = $Linha[13];            // ITEM.EITESCDESCMAT Descrição detalhada de Material
                $linhaRetorno['DescDetaServ']        = $Linha[14];            // ITEM.EITESCDESCSE  Descrição detalhada de Serviço

                $SeqSolicitacao = $Linha[0];
                $DataSolicitacao = $Linha[1];
                $CodSituacao = $Linha[4];
                $CodComissaoLici = $Linha[8];
                $TipoRegistroPreco = $Linha[10];
                $FlagGeraContrato = $Linha[12];

                $arrLinhas[] = $linhaRetorno;
            }
        }
    }

    return $arrLinhas;
}

function listarIndividualLicitacaoIncluir($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao, $boolFiltrarGrupo = true, $filtrarPelaComissaoUsuario = false)
{
    $arrLinhas = array();
    $db = $GLOBALS["db"];

    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res = $db->query($sqlComiss);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }

    $sql = "SELECT DISTINCT
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT, ITEM.EITESCDESCMAT, ITEM.EITESCDESCSE
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
	JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
    JOIN SFPC.TBITEMSOLICITACAOCOMPRA AS ITEM
            ON SOL.CSOLCOSEQU = ITEM.CSOLCOSEQU
	WHERE
		SOL.CTPCOMCODI = 2
		";

    if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao) > 0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }
    //Filtrando Pela Situação
    if ($Situacao != "" & SoNumeros($Situacao)) {
        $sql .= " AND SSO.CSITSOCODI = $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql .= " AND ORG.CORGLICODI = ".$Orgao; //SOL
    }

    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql .= " AND SOL.CSOLCOSEQU = $strSolicitacao ";
    }
    if (isset($boolFiltrarGrupo) & $boolFiltrarGrupo) {
        $sql .= " AND SOL.CSOLCOSEQU NOT IN (SELECT CSOLCOSEQU FROM SFPC.TBAGRUPASOLICITACAO)";
    }
    $sql .= " ORDER BY SOL.CSOLCOSEQU ASC";

    $res = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao'] = $Linha[0];     // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['DataSolicitacao'] = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['CodOrgao'] = $Linha[2];     // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
                $linhaRetorno['DescOrgao'] = $Linha[3];     // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
                $linhaRetorno['CodSituacao'] = $Linha[4];    // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
                $linhaRetorno['DescSolicitacao'] = $Linha[5];    // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
                $linhaRetorno['DescCentroCusto'] = $Linha[6];    // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['DetaCentroCusto'] = $Linha[7];    // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
                $linhaRetorno['CodComissaoLici'] = $Linha[8];    // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['DescComissaoLici'] = $Linha[9];    // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
                $linhaRetorno['TipoRegistroPreco'] = $Linha[10];     // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
                $linhaRetorno['ObjetoSolicitacao'] = $Linha[11];     // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
                $linhaRetorno['FlagGeraContrato'] = $Linha[12];     // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */
                $linhaRetorno['DescDetaMat'] = $Linha[13];            // ITEM.EITESCDESCMAT Descrição detalhada de Material
                $linhaRetorno['DescDetaServ'] = $Linha[14];            // ITEM.EITESCDESCSE  Descrição detalhada de Serviço
                $arrLinhas[] = $linhaRetorno;
        }
    }

    return $arrLinhas;
}

function listarGrupo($Situacao, $Orgao, $DataIni, $DataFim, $strSolicitacao = "", $strCodGrupo = "", $filtrarPelaComissaoUsuario = false)
{
    $arrLinhasGrupo    = array();
    $db = $GLOBALS["db"];

    //Procurando comissão de licitação do usuario logado
    //Procurando as comissão de licitação do usuario logado
    $intCodUsuario    = $_SESSION['_cusupocodi_'];
    $arrComissaoLicitacao = array();
    $sqlComiss = "SELECT CCOMLICODI FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $intCodUsuario";
    $res  = $db->query($sqlComiss);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $arrComissaoLicitacao[] = $Linha[0];
        }
    }
    //removendo o filtro para testes
    //unset($comissaoLicitacao);


    $sql1 = "SELECT
		DISTINCT (AGR.CAGSOLSEQU) AS GRUPO
	FROM
		SFPC.TBAGRUPASOLICITACAO AS AGR
	JOIN
		SFPC.TBSOLICITACAOCOMPRA AS SOL
			ON AGR.CSOLCOSEQU = SOL.CSOLCOSEQU
	JOIN
		SFPC.TBSITUACAOSOLICITACAO AS SSO
			ON SOL.CSITSOCODI = SSO.CSITSOCODI
	JOIN
		SFPC.TBORGAOLICITANTE AS ORG
			ON SOL.CORGLICODI = ORG.CORGLICODI
	WHERE
		SOL.CTPCOMCODI = 2
		";
    if ($filtrarPelaComissaoUsuario) {
        if (count($arrComissaoLicitacao)>0) {
            $strComissao = implode(",", $arrComissaoLicitacao);
            $sql .= " AND SOL.CCOMLICOD1 in($strComissao) ";
        }
    }
    //Filtrando Pela Situação
    if ($Situacao != ""&SoNumeros($Situacao)) {
        $sql1 .= " AND SSO.CSITSOCODI = $Situacao ";
    }
    //Filtrando Pelo orgao
    if ($Orgao != "TODOS") {
        $sql1 .= " AND ORG.CORGLICODI = ".$Orgao;//SOL
    }
    //Filtrando Pela data
    if ($DataIni != "" and $DataFim != "") {
        $sql1 .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
    }
    //Filtrando pelo código do grupo
    if (isset($strCodGrupo) & is_numeric($strCodGrupo)) {
        $sql1 .= " AND AGR.CAGSOLSEQU = $strCodGrupo ";
    }
    //Filtrando pelo código do grupo
    if (isset($strSolicitacao) & is_numeric($strSolicitacao)) {
        $sql1 .= " AND AGR.CSOLCOSEQU = $strSolicitacao ";
    }

    $sql = "SELECT
		SOL.CSOLCOSEQU, SOL.TSOLCODATA, SOL.CORGLICODI,
		ORG.EORGLIDESC, SOL.CSITSOCODI, SSO.ESITSONOME,
		CEN.ECENPODESC, CEN.ECENPODETA, GRU.CAGSOLSEQU,
		GRU.FAGSOLFLAG, GRU.TAGSOLULAT, COM.CCOMLICODI,
		COM.ECOMLIDESC, SOL.FSOLCORGPR, SOL.ESOLCOOBJE,
		SOL.FSOLCOCONT
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
	JOIN
		SFPC.TBAGRUPASOLICITACAO AS GRU
			ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU
	JOIN
		SFPC.TBCOMISSAOLICITACAO AS COM
			ON SOL.CCOMLICOD1 = COM.CCOMLICODI
	WHERE
		GRU.CAGSOLSEQU IN ($sql1)";

    $sql .= " ORDER BY GRU.CAGSOLSEQU, GRU.FAGSOLFLAG DESC, ORG.EORGLIDESC, SOL.CSOLCOSEQU DESC";

    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['SeqSolicitacao']    = $Linha[0];             // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['DataSolicitacao']    = DataBarra($Linha[1]);  // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['CodOrgao']            = $Linha[2];             // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
            $linhaRetorno['DescOrgao']            = $Linha[3];             // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
            $linhaRetorno['CodSituacao']        = $Linha[4];             // SOL.CSITSOCODI, /* CÓDIGO SITUAÇÃO ATUAL DA SOLICITAÇÃO */
            $linhaRetorno['DescSolicitacao']    = $Linha[5];             // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
            $linhaRetorno['DescCentroCusto']    = $Linha[6];             // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['DetaCentroCusto']    = $Linha[7];             // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
            $linhaRetorno['CodGrupo']            = $Linha[8];             // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
            $linhaRetorno['FlagGrupo']            = $Linha[9];             // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
            $linhaRetorno['DataAgrupamento']    = DataBarra($Linha[10]); // GRU.TAGSOLULAT, /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */
            $linhaRetorno['CodComissaoLici']    = $Linha[11];             // COM.CCOMLICODI, /* CÓDIGO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['DescComissaoLici']    = $Linha[12];             // COM.ECOMLIDESC, /* DESCRIÇÃO DA COMISSÃO DE LICITAÇÃO */
            $linhaRetorno['TipoRegistroPreco']    = $Linha[13];             // SOL.FSOLCORGPR, /* Tipo de Compra Registro de Preço (S - Sim ou N - Não) */
            $linhaRetorno['ObjetoSolicitacao']    = $Linha[14];             // SOL.ESOLCOOBJE, /* OBJETO DA SOLICITAÇÃO DE COMPRA */
            $linhaRetorno['FlagGeraContrato']    = $Linha[15];             // SOL.FSOLCOCONT, /* Flag Gera Contrato (S - Sim ou N - Não) */

            $arrLinhasGrupo[] = $linhaRetorno;
        }
    }

    return $arrLinhasGrupo;
}

# RETORNA O DETALHAMENTO DAS SOLICITAÇÕES
function infoDetalhamento($SeqSolicitacao)
{
    $arrInfo = array();

    $db = $GLOBALS["db"];
    $sql = "SELECT
		ITEM.CITESCSEQU, 	ITEM.CMATEPSEQU, ITEM.CSERVPSEQU,
		ITEM.AITESCORDE, 	ITEM.AITESCQTSO, ITEM.VITESCUNIT,
		ITEM.VITESCVEXE, 	MAT.EMATEPDESC,  SERV.ESERVPDESC,
		ITEM.EITESCDESCSE, 	MAT.CUNIDMCODI,  ITEM.AITESCQTEX,
		ITEM.EITESCDESCMAT , ITEM.EITESCDESCSE, UNIDADE.EUNIDMSIGL
	FROM
		SFPC.TBITEMSOLICITACAOCOMPRA ITEM
	LEFT JOIN
		SFPC.TBMATERIALPORTAL MAT ON (MAT.CMATEPSEQU = ITEM.CMATEPSEQU)
	LEFT JOIN
		SFPC.TBSERVICOPORTAL SERV ON (SERV.CSERVPSEQU = ITEM.CSERVPSEQU)
	LEFT JOIN
		SFPC.TBUNIDADEDEMEDIDA UNIDADE ON (MAT.CUNIDMCODI = UNIDADE.CUNIDMCODI)
	WHERE
            ITEM.CSOLCOSEQU = $SeqSolicitacao ORDER BY ITEM.CITESCSEQU ASC";

    $res  = $db->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        while ($Linha = $res->fetchRow()) {
            $linhaRetorno['CodSeqItens']    = $Linha[0];        //ITEM.CITESCSEQU - Código sequencial dos itens da solicitação de compras
            $linhaRetorno['CodMaterial']    = $Linha[1];        //ITEM.CMATEPSEQU - Código do Material
            $linhaRetorno['CodServPortal']    = $Linha[2];        //ITEM.CSERVPSEQU - Código do Servico Portal
            $linhaRetorno['OrdItemSoli']    = $Linha[3];        //ITEM.AITESCORDE - Ordem do item na solicitação de compras
            $linhaRetorno['QtdItemSoli']    = $Linha[4];        //ITEM.AITESCQTSO - Quantidade do item na solicitação de compras
            $linhaRetorno['VlrUnitItem']    = $Linha[5];        //ITEM.VITESCUNIT - Valor unitário do item (estimado / Cotado / da Ata)
            $linhaRetorno['VlrItemSoli']    = $Linha[6];        //ITEM.VITESCVEXE  - Valor no exercício do item na solicitação de compras
            $linhaRetorno['DescMaterial']    = $Linha[7];        //MAT.EMATEPDESC - Descricao do material
            $linhaRetorno['DescServico']    = $Linha[8];        //SERV.ESERVPDESC - Descricao do servico
            $linhaRetorno['DescDetaServ']    = $Linha[9];        //ITEM.EITESCDESCSE - Descrição detalhada do item de Serviço
            $linhaRetorno['QtdExercicio']    = $Linha[11];        //ITEM.AITESCQTEX - Quantidade no Exercício
            $linhaRetorno['DescDetaMat']    = $Linha[12];        //ITEM.EITESCDESCMAT - Descrição detalhada do item de Material
            $linhaRetorno['DescDetaServ']    = $Linha[13];        //ITEM.EITESCDESCSE - Descrição detalhada do item de Serviço
            $linhaRetorno['Unidade']        = $Linha[14];        //MAT.CUNIDMCODI - Unidade

            //$linhaRetorno['DescServicoDetalhado']	= $Linha[12]; 		//ITEM.AITESCQTEX - Descrição detalhada do serviço

            #Se é material
            if ($linhaRetorno['CodMaterial'] != "") {
                $linhaRetorno['DescDet']    = $Linha[12];        //ITEM.EITESCDESCMAT - Descrição detalhada do material
                $linhaRetorno['Tipo'] = "CADUM";
            } else {
                $linhaRetorno['CodMaterial'] = $linhaRetorno['CodServPortal'];
//				$linhaRetorno['DescDet'] = $linhaRetorno['DescServico']."-".$linhaRetorno['DescServicoDetalhado'];
                $linhaRetorno['DescDet']    = $Linha[9];        //ITEM.EITESCDESCSE - Descrição detalhada do serviço
                $linhaRetorno['Tipo'] = "CADUS";
                $linhaRetorno['ValorTrp'] = '-';
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
		<tr style="display:none;" class="opdetalhe <?php echo $SeqSolicitacao;
    ?>">
			<td style="background-color:#F1F1F1;" colspan="4">
				<table bordercolor="#75ADE6" border="1" bgcolor="bfdaf2" width="100%" class="textonormal">
					<tr>
						<td class="textoabason" align="center" bgcolor="#DCEDF7">ORD</td>
						<td class="textoabason" align="center" bgcolor="#DCEDF7">DESCRIÇÃO</td>
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
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['OrdItemSoli'];
        ?></td>
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['DescMaterial'];
        ?></td>
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['Tipo'];
        ?></td>
						<td	class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo $itens['CodMaterial'];
        ?></td>
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_quant($itens['QtdItemSoli']);
        ?></td>
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['VlrUnitItem']);
        ?></td>
						<td class="textonormal" align="center" bgcolor="#bfdaf2">&nbsp;<?php echo converte_valor($itens['QtdItemSoli']*$itens['VlrUnitItem']);
        ?></td>
					</tr>
					<?php
    }
    ?>
				</table>
			</td>
		</tr>
	<!-- FIM DO DETALHAMENTO DA SOLICITAÇÃO -->
<?php

}
//Essa funcao recebe um array de solicitacoes com os dados necessários e retornará na posicao 0 um array de
//itens agrupados e na posicao 1 o valor total
function RecSolicitacoes($arrLinhas, $boolPreco = "")
{
    $dbOracle = $GLOBALS["dbOracle"];
    $db = $GLOBALS["db"];

    foreach ($arrLinhas as $linhaSoli2) {
        $resultado = infoDetalhamento($linhaSoli2['SeqSolicitacao']);

        foreach ($resultado as $aux) {
            //Pegando lista de DOTACAO E BLOQUEIO
            $valorDotacaoBloqueio = null;
            $valorDotacaoBloqueio = array();

            if ($boolPreco != "") {
                if ($boolPreco == "S") {
                    //Faco a busca pelos campos de Dotação
                     $sql = " SELECT AITCDOUNIDOEXER, CITCDOUNIDOORGA, CITCDOUNIDOCODI, CITCDOTIPA, AITCDOORDT, CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
							 FROM SFPC.TBITEMDOTACAOORCAMENT WHERE CITESCSEQU = ".$aux['CodSeqItens']." AND CSOLCOSEQU = ".$linhaSoli2['SeqSolicitacao'];

                    $res  = $db->query($sql);
                    if (PEAR::isError($res)) {
                        $CodErroEmail  = $res->getCode();
                        $DescErroEmail = $res->getMessage();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    } else {
                        $valorTotalDisponivel = 0;
                        while ($linha = $res->fetchRow()) {
                            $dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle, $linha[0], $linha[1], $linha[2], $linha[3], $linha[4], $linha[5], $linha[6], $linha[7], $linha[8], $linha[9]);
                            $valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
                            $valorTotalDisponivel += $dotacaoArray["valorDisponivel"];
                        }
                    }
                } else {
                    //Faco a busca pelos campos de Bloqueio
                     $sql = " SELECT AITCBLNBLOQ , AITCBLANOB
							  FROM  SFPC.TBITEMBLOQUEIOORCAMENT WHERE CITESCSEQU =  ".$aux['CodSeqItens']." AND csolcosequ = ".$linhaSoli2['SeqSolicitacao'];
                    $res  = $db->query($sql);
                    if (PEAR::isError($res)) {
                        $CodErroEmail  = $res->getCode();
                        $DescErroEmail = $res->getMessage();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    } else {
                        while ($linha = $res->fetchRow()) {
                            $dotacaoArray = getDadosBloqueioFromChave($dbOracle, $linha[1], $linha[0]);
                            $valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
                        }
                    }
                }
            }

            $valorTotal += ($aux['QtdItemSoli'] * $aux['VlrUnitItem']);
            if ($aux['CodMaterial'] == "") {
                $aux['CodMaterial']  = $aux['CodServPortal'];
                $aux['DescMaterial'] = $aux['DescServico'];
            }

            if (isset($listaItens[$aux['CodMaterial']]['codRed'])) {
                $listaItens[$aux['CodMaterial']]['Quantidade'] += $aux['QtdItemSoli'];
                $listaItens[$aux['CodMaterial']]['DOTACAOBLOQUEIOS'] + $valorDotacaoBloqueio;
            } else {
                $listaItens[$aux['CodMaterial']]['Cod']                = $aux['OrdItemSoli'];
                $listaItens[$aux['CodMaterial']]['codRed']                = $aux['CodMaterial'];
                $listaItens[$aux['CodMaterial']]['Descricao']            = $aux['DescMaterial'];
                $listaItens[$aux['CodMaterial']]['Tipo']                = $aux['Tipo'];
                $listaItens[$aux['CodMaterial']]['Quantidade']            = $aux['QtdItemSoli'];
                $listaItens[$aux['CodMaterial']]['Unid']                = $aux['Unidade'];
                $listaItens[$aux['CodMaterial']]['ValorEstimado']        = $aux['VlrUnitItem'];
                $listaItens[$aux['CodMaterial']]['QtdExercicio']        = $aux['QtdExercicio'];
                $listaItens[$aux['CodMaterial']]['ValorExercicio']        = $aux['VlrItemSoli'];
                $listaItens[$aux['CodMaterial']]['DOTACAOBLOQUEIOS']    = $valorDotacaoBloqueio;
            }
        }
    }

    $retorno[0] = $listaItens;
    $retorno[1] = $valorTotal;

    return $retorno;
}

function RecSolicitacoesBKS($arrLinhas, $boolPreco = "")
{
    $dbOracle = $GLOBALS["dbOracle"];
    $db = $GLOBALS["db"];

    foreach ($arrLinhas as $linhaSoli2) {
        $resultado = infoDetalhamento($linhaSoli2['SeqSolicitacao']);
        $i = 0;

        foreach ($resultado as $aux) {
            //Pegando lista de DOTACAO E BLOQUEIO
            $valorDotacaoBloqueio = null;
            $valorDotacaoBloqueio = array();

            if ($boolPreco != "") {
                if ($boolPreco == "S") {
                    //Faco a busca pelos campos de Dotação
                    $sql = " SELECT AITCDOUNIDOEXER, CITCDOUNIDOORGA, CITCDOUNIDOCODI, CITCDOTIPA, AITCDOORDT, CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
							 FROM SFPC.TBITEMDOTACAOORCAMENT WHERE CITESCSEQU = ".$aux['CodSeqItens']." AND CSOLCOSEQU = ".$linhaSoli2['SeqSolicitacao'];
                    $res  = $db->query($sql);
                    if (PEAR::isError($res)) {
                        $CodErroEmail  = $res->getCode();
                        $DescErroEmail = $res->getMessage();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    } else {
                        $valorTotalDisponivel = 0;
                        while ($linha = $res->fetchRow()) {
                            $dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle, $linha[0], $linha[1], $linha[2], $linha[3], $linha[4], $linha[5], $linha[6], $linha[7], $linha[8], $linha[9]);
                            $valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
                            $valorTotalDisponivel += $dotacaoArray["valorDisponivel"];
                        }
                    }
                } else {
                    //Faco a busca pelos campos de Bloqueio
                    $sql = " SELECT AITCBLNBLOQ , AITCBLANOB
										FROM  SFPC.TBITEMBLOQUEIOORCAMENT WHERE CITESCSEQU =  ".$aux['CodSeqItens']." AND csolcosequ = ".$linhaSoli2['SeqSolicitacao'];
                    $res  = $db->query($sql);
                    if (PEAR::isError($res)) {
                        $CodErroEmail  = $res->getCode();
                        $DescErroEmail = $res->getMessage();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                    } else {
                        while ($linha = $res->fetchRow()) {
                            $dotacaoArray = getDadosBloqueioFromChave($dbOracle, $linha[1], $linha[0]);
                            $valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
                        }
                    }
                }
            }

            $valorTotal += ($aux['QtdItemSoli'] * $aux['VlrUnitItem']);

            if ($aux['CodServPortal'] != "") {
                $aux['DescMaterial'] = $aux['DescServico'];
            }

            if (count($arrLinhas) > 1) {
                if (isset($listaItens[$aux['CodMaterial']]['codRed'])) {
                    $listaItens[$aux['CodMaterial']]['Quantidade'] += $aux['QtdItemSoli'];
                    $listaItens[$aux['CodMaterial']]['DOTACAOBLOQUEIOS'] + $valorDotacaoBloqueio;
                } else {
                    $listaItens[$aux['CodMaterial']]['Cod']                = $aux['OrdItemSoli'];
                    $listaItens[$aux['CodMaterial']]['codRed']                = $aux['CodMaterial'];
                    $listaItens[$aux['CodMaterial']]['Descricao']            = $aux['DescMaterial'];
                    $listaItens[$aux['CodMaterial']]['Tipo']                = $aux['Tipo'];
                    $listaItens[$aux['CodMaterial']]['Quantidade']            = $aux['QtdItemSoli'];
                    $listaItens[$aux['CodMaterial']]['Unid']                = $aux['Unidade'];
                    $listaItens[$aux['CodMaterial']]['DescDet']                = $aux['DescDet'];
                    $listaItens[$aux['CodMaterial']]['ValorEstimado']        = $aux['VlrUnitItem'];
                    $listaItens[$aux['CodMaterial']]['QtdExercicio']        = $aux['QtdExercicio'];
                    $listaItens[$aux['CodMaterial']]['ValorExercicio']        = $aux['VlrItemSoli'];
                    $listaItens[$aux['CodMaterial']]['DOTACAOBLOQUEIOS']    = $valorDotacaoBloqueio;
                }
            } else {
                $listaItens[$i]['Cod']                = $aux['OrdItemSoli'];
                $listaItens[$i]['codRed']            = $aux['CodMaterial'];
                $listaItens[$i]['Descricao']        = $aux['DescMaterial'];
                $listaItens[$i]['Tipo']                = $aux['Tipo'];
                $listaItens[$i]['Quantidade']        = $aux['QtdItemSoli'];
                $listaItens[$i]['Unid']                = $aux['Unidade'];
                $listaItens[$i]['DescDet']            = $aux['DescDet'];
                $listaItens[$i]['ValorEstimado']    = $aux['VlrUnitItem'];
                $listaItens[$i]['QtdExercicio']    = $aux['QtdExercicio'];
                $listaItens[$i]['ValorExercicio']    = $aux['VlrItemSoli'];
                $listaItens[$i]['DOTACAOBLOQUEIOS'] = $valorDotacaoBloqueio;

                $i++;
            }
        }
    }
    $retorno[0] = $listaItens;
    $retorno[1] = $valorTotal;

    return $retorno;
}

function validarReservaOrcamentariaBKS($db, $dbOracle, $tipoReserva, $arrayReservas, $arrayItens, $nomeCampo)
{
    $respostaComoMensagem = true;

    assercao(!is_null($db), "Variável do banco de dados não foi inicializada");
    assercao(!is_null($dbOracle), "Variável do banco de dados Oracle não foi inicializada");
    assercao(!is_null($nomeCampo), "Parâmetro 'nomeCampo' requerido");
    assercao(!is_null($tipoReserva), "Parâmetro 'tipoReserva' requerido");
    //assercao(!is_null($arrayReservas), "Parâmetro 'arrayReservas' requerido");
    assercao(is_null($arrayReservas) or is_array($arrayReservas), "Parâmetro 'arrayReservas' deve ser um array");
    assercao(!is_null($arrayItens), "Parâmetro 'arrayItens' requerido");
    assercao(is_array($arrayItens), "Parâmetro 'arrayItens' deve ser um array");

    $valorTotalScc = 0;
    $bloqueiosValoresTotais = 0;
    $arrayPos = -1;
    if (count($arrayReservas) == 0 or is_null($arrayReservas)) {
        erroUsuario($respostaComoMensagem, $nomeCampo, 'Pelo menos um bloqueio ou dotação');
    } else {
        foreach ($arrayReservas as $reserva) {
            $arrayPos ++;
            if ($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_DOTACAO) {
                $reservaArray = getDadosDotacaoOrcamentaria($dbOracle, $reserva);
                $bloqueiosValoresTotais += $reservaArray['valorDisponivel'];
            } elseif ($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO) {
                $reservaArray = getDadosBloqueio($dbOracle, $reserva);
                $bloqueiosValoresTotais += $reservaArray['valorTotal'];
                # Bloqueio é homologado?
                if (!is_null($reservaArray) and $reservaArray['homologado'] == 'S') {
                    erroUsuario($respostaComoMensagem, $nomeCampo, 'Bloqueio '.$reserva.' é homologado e não pode ser usado');
                }
            } else {
                assercao(false, "Tipo de reserva desconhecida");
            }
            if (is_null($reservaArray)) {
                erroUsuario($respostaComoMensagem, $nomeCampo, "Bloqueio/Dotação '".$reserva."' não existe");
            }
        }
    }

    foreach ($arrayItens as $itemSCC) {
        $arrayPos++;
        assercao(!is_null($itemSCC['posicao']), "Variável 'posicao' está faltando no item na posição do array '".$arrayPos."'");
        //assercao(!is_null($itemSCC['codigo']), "Variável 'codigo' do item requerida em ".$tipoItem." ord ".$itemSCC['posicaoItem']."");
        //assercao(!is_null($itemSCC['tipo']), "Variável 'tipo' do item requerida em ".$tipoItem." ord ".$itemSCC['posicaoItem']."");
        assercao(!is_null($itemSCC['quantidadeItem']), "Variável 'quantidadeItem' do item requerida em ".$tipoItem." em posição ".$itemSCC['posicao']."");
        assercao(!is_null($itemSCC['valorItem']), "Variável 'valorItem' do item requerida em ".$tipoItem." ord ".$itemSCC['posicao']."");

        $valorTotalScc += $itemSCC['quantidadeItem'] * $itemSCC['valorItem'];
    }

    if ($tipoReserva == TIPO_RESERVA_ORCAMENTARIA_BLOQUEIO) {
        if ($bloqueiosValoresTotais<$valorTotalScc) {
            erroUsuario($respostaComoMensagem, $nomeCampo, 'Valor total da Solicitação é maior que a soma de todos os bloqueios');
        }
    }
}

function checarTipoItensSolic($strSolicitacao)
{
    $db = $GLOBALS["db"];
    $sql = "SELECT ITEM.CMATEPSEQU, ITEM.CSERVPSEQU
            FROM SFPC.TBITEMSOLICITACAOCOMPRA AS ITEM
            WHERE ITEM.CSOLCOSEQU = $strSolicitacao ";
    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Linha = $res->fetchRow();
        // Se for vazio, é serviço
        if (empty($Linha[0])) {
            return true;
        }
    }
}

function checarTipoItensLic($strSolicitacao)
{
    $db = $GLOBALS["db"];
    $sql = "SELECT ITEM.CMATEPSEQU, ITEM.CSERVPSEQU
            FROM SFPC.TBITEMLICITACAOPORTAL AS ITEM
            WHERE ITEM.citelpsequ = $strSolicitacao ";
    $res  = $db->query($sql);
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Linha = $res->fetchRow();
        // Se for vazio, é serviço
        if (empty($Linha[0])) {
            return true;
        }
    }
}

?>
