<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialTRPHistoricoDetalhe.php
# Objetivo: Programa de Detalhamento do Historico de preços TRP de um material
# Autor:    Igor Duarte
# Data:     11/09/2012
#----------------------------------------------------------------------------
# Alterado: Igor Duarte
# Data:     30/10/2012 - inclusão dos campos do nº de lote e ordem do item
#----------------------------------------------------------------------------
# Alterado: Igor Duarte
# Data:     30/10/2012 - exibição do campo origem de preço somente quando o tipo de preço for licitação
#----------------------------------------------------------------------------
# Alterado: Pitang Agile TI
# Data:     30/06/2015
# Objetivo: [CR Redime 73656] - Formato americano em preços do histórico TRP
# Versão:   v1.21.0-16-g300d38d
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     24/08/2018
# Objetivo: Tarefa Redmine 179140
#----------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     24/08/2018
# Objetivo: Tarefa Redmine 201997
#----------------------------------------------------------------------------

require_once "../licitacoes/funcoesComplementaresLicitacao.php";
require_once "../compras/funcoesCompras.php";

// Acesso ao arquivo de funções
require_once "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/materiais/CadMaterialTRPHistorico.php');
AddMenuAcesso('/compras/RotDadosFornecedor.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao          = $_POST['Botao'];
    $Material       = $_POST['Material'];
    $CodCADUM       = $_POST['CodCADUM'];
    $DescSimples    = $_POST['DescSimples'];
    $UndMedida      = $_POST['UndMedida'];
    $DescCompleta   = $_POST['DescCompleta'];
    $DataIni        = $_POST['DataIni'];
    $DataFim        = $_POST['DataFim'];
    $TipoPreco      = $_POST['TipoPreco'];
    $OrigemPreco    = $_POST['OrigemPreco'];
    $Orgao          = $_POST['Orgao'];
    $CnpjFornecedor = $_POST['CnpjFornecedor'];
    $MediaPesquisa  = $_POST['MediaPesquisa'];
    $MediaTRP       = $_POST['MediaTRP'];
    $CheckExpurgo   = $_POST['CheckExpurgo'];
    if ($DataIni != "") {
        $DataIni = FormataData($DataIni);
    }

    if ($DataFim != "") {
        $DataFim = FormataData($DataFim);
    }
} else {
    $Material = $_GET['Material'];
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
}

// Identifica o Programa para Erro de Banco de Dados
$ErroPrograma = __FILE__;

if (empty($DataIni)) {
    $DataIni = '01/01/1900';
}

if (empty($DataFim)) {
    $DataFim = '12/12/2200';
}

$dataTIni = explode("/", $DataIni);
$dataTIni = $dataTIni[2]."-".$dataTIni[1]."-".$dataTIni[0];

$dataTFim = explode("/", $DataFim);
$dataTFim = $dataTFim[2]."-".$dataTFim[1]."-".$dataTFim[0];

// LICITAÇÃO: LICITAÇÃO & PESQUISA DE MERCADO
$select = "SELECT DISTINCT TRP.CPESQMSEQU, TRP.CMATEPSEQU, TRP.CSOLCOSEQU ";

$from = " FROM SFPC.TBTABELAREFERENCIALPRECOS TRP JOIN SFPC.TBUSUARIOPORTAL USUP ON TRP.CUSUPOCODI = USUP.CUSUPOCODI ";

$where = " WHERE TRP.CMATEPSEQU = ".$Material."  ";

$order = " ORDER BY ";

// COMPRA DIRETA
$select1 = "SELECT  DISTINCT TRP.CSOLCOSEQU, TRP.CTRPREULAT, CCP.CCENPOCORG, CCP.CCENPOUNID, SOLC.CSOLCOCODI, SOLC.ASOLCOANOS, 
                             FORN.AFORCRCCGC, FORN.NFORCRRAZS, ISC.EITESCMARC, ISC.EITESCMODE, TRP.VTRPREVALO, TRP.FTRPREVALI, 
                             TRP.ETRPREJUST, USUP.EUSUPORESP, TRP.DTRPREVALI, ISC.AITESCORDE ";

$from1 = " FROM  SFPC.TBTABELAREFERENCIALPRECOS TRP
                JOIN SFPC.TBUSUARIOPORTAL USUP ON TRP.CUSUPOCODI = USUP.CUSUPOCODI
                LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ISC ON ISC.CSOLCOSEQU = TRP.CSOLCOSEQU AND ISC.CITESCSEQU = TRP.CITESCSEQU
                LEFT JOIN SFPC.TBSOLICITACAOCOMPRA SOLC ON TRP.CSOLCOSEQU = SOLC.CSOLCOSEQU
                JOIN SFPC.TBCENTROCUSTOPORTAL CCP ON CCP.CCENPOSEQU = SOLC.CCENPOSEQU
                JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON ISC.AFORCRSEQU = FORN.AFORCRSEQU ";

$where1 = " WHERE    TRP.CMATEPSEQU = ".$Material."
                    AND TRP.CTRPREULAT BETWEEN '".$dataTIni."' AND '".$dataTFim."' ";

$order1 = " ORDER BY TRP.CTRPREULAT ,CCP.CCENPOCORG ,CCP.CCENPOUNID ,SOLC.CSOLCOCODI ,SOLC.ASOLCOANOS ,FORN.AFORCRCCGC ,FORN.NFORCRRAZS ";

if ($Botao == "Limpar") {
    $Botao          = "";
    $DataIni        = null;
    $DataFim        = null;
    $TipoPreco      = null;
    $OrigemPreco    = null;
    $Orgao          = null;
    $CnpjFornecedor = null;
    $MediaPesquisa  = null;
    $CheckExpurgo   = null;
} elseif ($Botao == "Voltar") {
    $Url = "CadMaterialTRPHistorico.php";

    if (! in_array($Url, $_SESSION['GetUrl'])) {
        $_SESSION['GetUrl'][] = $Url;
    }
    header("location: ".$Url);
    exit();
} elseif ($Botao == "Pesquisar") {
    $CnpjFornecedor = removeSimbolos($CnpjFornecedor);

    $d3 = ValidaData(FormataData($DataIni));
    $d4 = ValidaData(FormataData($DataFim));

    $CL = false;

    if ($Orgao == "" || $Orgao == null) {
        $Botao = "";
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript: document.CadMaterialTRPHistoricoDetalhe.Orgao.focus();\" class=\"titulo2\">Selecione um Órgão</a>";
    } else {
        if (! empty($d3)) {
            $Botao = "";
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript: document.CadMaterialTRPHistoricoDetalhe.DataIni.focus();\" class=\"titulo2\">$d3</a>";
        } elseif (! empty($d4)) {
            $Botao = "";
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript: document.CadMaterialTRPHistoricoDetalhe.DataFim.focus();\" class=\"titulo2\">$d4</a>";
        } elseif ($dataTIni > $dataTFim) {
            $Botao = "";
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript: document.CadMaterialTRPHistoricoDetalhe.DataIni.focus();\" class=\"titulo2\">Informe: a data final deve ser maior que data inicial</a>";
        } else {
            // Verificando se o tipo de preço é compra direta
            if ($TipoPreco == 'C' || $TipoPreco == 'T') {
                if (($CnpjFornecedor != null) && ($CnpjFornecedor != "")) {
                    $where1 .= "    AND FORN.AFORCRCCGC = '".$CnpjFornecedor."' ";
                }
            }

            // Verificando se o tipo de preço é licitação
            if ($TipoPreco == 'L' || $TipoPreco == 'T') {
                $where .= " AND TRP.CSOLCOSEQU IS NULL  ";

                // Se origem é do tipo licitaçao
                if ($OrigemPreco == 'L') {
                    $select .= " , TRP.CTRPREULAT, TRP.CLICPOPROC, TRP.ALICPOANOP, FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.EITELPMARC, ILP.EITELPMODE ";

                    $from .= " LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP ON TRP.CLICPOPROC = ILP.CLICPOPROC AND TRP.ALICPOANOP = ILP.ALICPOANOP AND TRP.CGREMPCODI = ILP.CGREMPCODI AND TRP.CCOMLICODI = ILP.CCOMLICODI AND TRP.CORGLICODI = ILP.CORGLICODI AND TRP.CITELPSEQU = ILP.CITELPSEQU
                               LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON ILP.AFORCRSEQU = FORN.AFORCRSEQU
                               LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON ILP.CCOMLICODI = CL.CCOMLICODI ";

                    $where .= " AND TRP.CLICPOPROC IS NOT NULL
                                AND TRP.CTRPREULAT BETWEEN '".$dataTIni."' AND '".$dataTFim."'  ";

                    if (($CnpjFornecedor != null) && ($CnpjFornecedor != "")) {
                        $where .= " AND FORN.AFORCRCCGC = '".$CnpjFornecedor."' ";
                    }

                    $order .= " TRP.CTRPREULAT ,TRP.CLICPOPROC ,TRP.ALICPOANOP ,ILP.CITELPNUML ,ILP.AITELPORDE ,FORN.AFORCRCCGC ";
                } elseif ($OrigemPreco == 'P') {
                    // Se origem é do tipo pesquisa de mercado
                    $select .= " , PPM.DPESQMREFE, PPM.EPESQMOBSE, PPM.CPESQMCNPJ, PPM.NPESQMRAZS, PPM.CPESQMMARC, PPM.CPESQMMODE ";

                    $from .= " LEFT JOIN SFPC.TBPESQUISAPRECOMERCADO PPM ON TRP.CPESQMSEQU = PPM.CPESQMSEQU ";

                    $where .= " AND TRP.CPESQMSEQU IS NOT NULL AND PPM.DPESQMREFE BETWEEN '".$dataTIni."' AND '".$dataTFim."' ";

                    if (($CnpjFornecedor != null) && ($CnpjFornecedor != "")) {
                        $where .= " AND PPM.CPESQMCNPJ = '".$CnpjFornecedor."'  ";
                    }

                    $order .= " PPM.DPESQMREFE ,PPM.EPESQMOBSE, PPM.CPESQMCNPJ ";
                } else {
                    $CL = true;

                    $select .= " ,PPM.DPESQMREFE, PPM.EPESQMOBSE, PPM.CPESQMCNPJ, PPM.NPESQMRAZS, PPM.CPESQMMARC, PPM.CPESQMMODE, TRP.CTRPREULAT, TRP.CLICPOPROC, TRP.ALICPOANOP, FORN.AFORCRCCGC, FORN.NFORCRRAZS, ILP.EITELPMARC, ILP.EITELPMODE ";

                    $from .= " LEFT JOIN SFPC.TBITEMLICITACAOPORTAL ILP ON TRP.CLICPOPROC = ILP.CLICPOPROC AND TRP.ALICPOANOP = ILP.ALICPOANOP AND TRP.CGREMPCODI = ILP.CGREMPCODI AND TRP.CCOMLICODI = ILP.CCOMLICODI AND TRP.CORGLICODI = ILP.CORGLICODI AND TRP.CITELPSEQU = ILP.CITELPSEQU
                               LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON ILP.AFORCRSEQU = FORN.AFORCRSEQU
                               LEFT JOIN SFPC.TBPESQUISAPRECOMERCADO PPM ON TRP.CPESQMSEQU = PPM.CPESQMSEQU
                               LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL ON ILP.CCOMLICODI = CL.CCOMLICODI ";

                    $order .= " ILP.CITELPNUML ,ILP.AITELPORDE ";

                    if (($CnpjFornecedor != null) && ($CnpjFornecedor != "")) {
                        $where .= " AND ((TRP.CLICPOPROC IS NOT NULL AND TRP.CTRPREULAT BETWEEN '".$dataTIni."' AND '".$dataTFim."' AND FORN.AFORCRCCGC = '".$CnpjFornecedor."') OR (TRP.CPESQMSEQU IS NOT NULL AND PPM.DPESQMREFE BETWEEN '".$dataTIni."' AND '".$dataTFim."' AND PPM.CPESQMCNPJ = '".$CnpjFornecedor."')) ";
                    } else {
                        $where .= " AND ((TRP.CLICPOPROC IS NOT NULL AND TRP.CTRPREULAT BETWEEN '".$dataTIni."' AND '".$dataTFim."') OR (TRP.CPESQMSEQU IS NOT NULL AND PPM.DPESQMREFE BETWEEN '".$dataTIni."' AND '".$dataTFim."')) ";
                    }
                }
            }

            // Verificando se algum órgão específico foi selecionado
            if ($Orgao != "TODOS" && $Orgao != "" && $Orgao != null) {
                $where  .= " AND TRP.CORGLICODI = ".$Orgao." ";
                $where1 .= " AND TRP.CORGLICODI = ".$Orgao." ";
            }

            // Verificando se itens expurgados entram na pesquisa
            if ($CheckExpurgo != 'S') {
                $where .= " AND (TRP.FTRPREVALI <> 'E' OR TRP.FTRPREVALI IS NULL)   ";
            }

            $select .= " , TRP.VTRPREVALO, TRP.FTRPREVALI, TRP.ETRPREJUST, USUP.EUSUPORESP, TRP.DTRPREVALI ";

            if (($TipoPreco != "C") && ($OrigemPreco != "P")) {
                $select .= " , ILP.CITELPNUML, ILP.AITELPORDE ";
            }

            if ($CL) {
                $select .= " , CL.ECOMLIDESC ";
            }

            $consulta  = $select.$from.$where.$order;
            $consulta1 = $select1.$from1.$where1.$order1;
        }
    }
}

if ($Botao == "") {
    // Pega os dados do Material de acordo com o código
    $db = Conexao();

    $dataMinimaValidaTrp = prazoValidadeTrp($db, TIPO_COMPRA_LICITACAO)->format('Y-m-d');
    $dataMinimaValidaPesquisaMercado = prazoValidadePesquisaMercado()->format('Y-m-d');

    $sql = "SELECT  MAT.EMATEPDESC, UNID.EUNIDMDESC, MAT.EMATEPCOMP
            FROM    SFPC.TBTABELAREFERENCIALPRECOS TRP
                    JOIN SFPC.TBMATERIALPORTAL MAT ON TRP.CMATEPSEQU = MAT.CMATEPSEQU
                    JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
            WHERE   TRP.CMATEPSEQU = $Material ";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        $MediaTRP = calculaValorTrp($Material);
        $MediaTRP = converte_valor_estoques($MediaTRP);

        if ($res->numRows() > 0) {
            $Linha = $res->fetchRow();

            $DescSimples  = $Linha[0];
            $DescCompleta = $Linha[2];
            $UndMedida    = $Linha[1];
            $CodCADUM     = $Material;
        } else {
            $Mensagem = urlencode("Este material não possui nenhum preço cadastrado");
            
            $Url = "CadMaterialTRPHistorico.php?Mensagem=$Mensagem&Mens=1&Tipo=2&Critica=0";

            if (! in_array($Url, $_SESSION['GetUrl'])) {
                $_SESSION['GetUrl'][] = $Url;
            }
            header("location: $Url");
            exit();
        }
    }
    $db->disconnect();
}

?>
<html>
    <?php
        // Carrega o layout padrão
        layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script type="text/javascript">
        function AbreJanela(url,largura,altura){
            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
        }
        function CaracteresObjeto(text,campo){
            input = document.getElementById(campo);
            input.value = text.value.length;
        }
        function enviar(valor){
            document.CadMaterialTRPHistoricoDetalhe.Botao.value = valor;
            document.CadMaterialTRPHistoricoDetalhe.submit();
        }
        function remeter(){
            document.CadMaterialTRPHistoricoDetalhe.submit();
        }
        function validaFornecedor(nomeCampoCpfCnpj,nomeCampoResposta){
            cpfCnpj = limpaCPFCNPJ(document.getElementById(nomeCampoCpfCnpj).value);
            carregamentoDinamico("<?php echo $GLOBALS["DNS_SISTEMA"];?>compras/RotDadosFornecedor.php","CPFCNPJ="+cpfCnpj,nomeCampoResposta);
            document.getElementById(nomeCampoCpfCnpj).value = formataCpfCnpj(cpfCnpj);
        }
        <?php MenuAcesso(); ?>
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="CadMaterialTRPHistoricoDetalhe.php" method="post" name="CadMaterialTRPHistoricoDetalhe">
            <br> <br> <br> <br> <br>
            <table width="100%" cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font>
                        <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                        </a> > Materiais/Serviços > TRP > Histórico
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
                <?php if ($Mens == 1) { ?>
                <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2">
                        <?php   if ($Mens == 1) { ExibeMens($Mensagem, $Tipo, 1); } ?>
                    </td>
                </tr>
                <?php } ?>
                <!-- Fim do Erro -->
                <!-- Corpo -->
                <tr>
                    <td width="100"></td>
                    <td class="textonormal">
                        <table width="100%" border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
                            <tr>
                                <td class="textonormal">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" summary="">
                                        <tr>
                                            <td class="textonormal">
                                                <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                                    <tr>
                                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="13">HISTÓRICO DE PREÇOS TRP</td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" bgcolor="#DCEDF7" valign="middle" class="titulo3" colspan="13">PESQUISA</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="13">
                                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
                                                                <tr>
                                                                    <td colspan="13">
                                                                        <table width="100%" class="textonormal" border="0" width="100%" summary="">
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Código CADUM</td>
                                                                                <td class="textonormal"><?php echo $CodCADUM; ?></td>
                                                                                <input type="hidden" name="CodCADUM" value="<?php echo $CodCADUM;?>">
                                                                                <input type="hidden" name="Material" value="<?php echo $Material;?>">
                                                                                <input type="hidden" name="MediaTRP" value="<?php echo $MediaTRP;?>">
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Descrição do Material</td>
                                                                                <td class="textonormal"><?php echo $DescSimples;?></td>
                                                                                <input type="hidden" name="DescSimples" value="<?php echo $DescSimples;?>">
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Unidade</td>
                                                                                <td class="textonormal"><?php echo $UndMedida;?></td>
                                                                                <input type="hidden" name="UndMedida" value="<?php echo $UndMedida;?>">
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Descrição Completa</td>
                                                                                <td class="textonormal"><?php echo $DescCompleta;?></td>
                                                                                <input type="hidden" name="DescCompleta" value="<?php echo $DescCompleta;?>">
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Período</td>
                                                                                <td class="textonormal">
                                                                                    <?php   $DataMes = DataMes();
                                                                                            if ($DataIni == "") {
                                                                                                $DataIni = $DataMes[0];
                                                                                            }
                                                                                            if ($DataFim == "") {
                                                                                                $DataFim = $DataMes[1];
                                                                                            }
                                                                                            if ($DataIni == '01/01/1900') {
                                                                                                $DataIni = '';
                                                                                            }
                                                                                            if ($DataFim == '12/12/2200') {
                                                                                                $DataFim = '';
                                                                                            }
                                                                                            $URLIni = "../calendario.php?Formulario=CadMaterialTRPHistoricoDetalhe&Campo=DataIni";
                                                                                            $URLFim = "../calendario.php?Formulario=CadMaterialTRPHistoricoDetalhe&Campo=DataFim";
                                                                                    ?>
                                                                                    <input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni; ?>" class="textonormal">
                                                                                        <a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                                    &nbsp;A&nbsp;
                                                                                    <input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim; ?>" class="textonormal">
                                                                                        <a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Tipo de Preço TRP</td>
                                                                                <td>
                                                                                    <select name="TipoPreco" onChange="javascript:remeter();" class="textonormal">
                                                                                        <option value="T" <?php if ($TipoPreco == 'T') { echo "selected"; }?>>TODOS</option>
                                                                                        <option value="C" <?php if ($TipoPreco == 'C') { echo "selected"; }?>>COMPRA DIRETA</option>
                                                                                        <option value="L" <?php if ($TipoPreco == 'L') { echo "selected"; }?>>LICITAÇÃO</option>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <?php if ($TipoPreco == 'L') { ?>
                                                                                <tr>
                                                                                    <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Origem do Preço</td>
                                                                                    <td>
                                                                                        <select name="OrigemPreco" class="textonormal">
                                                                                            <option value="L" <?php if ($OrigemPreco == 'L') { echo "selected"; } ?>>LICITAÇÃO</option>
                                                                                            <option value="P" <?php if ($OrigemPreco == 'P') { echo "selected"; } ?>>PESQUISA MERCADO</option>
                                                                                            <option value="T" <?php if ($OrigemPreco == 'T') { echo "selected"; } ?>>TODOS</option>
                                                                                        </select>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php } else { $OrigemPreco = ""; } ?>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Órgão</td>
                                                                                <td class="textonormal">
                                                                                    <select name="Orgao" class="textonormal">
                                                                                        <option <?php if ($Orgao == "TODOS") { echo "selected='selected'"; }?> value="TODOS">Todos</option>
                                                                                        <?php   $db = Conexao();
                                                                                                $sql = "SELECT  ORG.CORGLICODI, ORG.EORGLIDESC
                                                                                                        FROM    SFPC.TBORGAOLICITANTE ORG
                                                                                                        WHERE   ORG.FORGLISITU = 'A'
                                                                                                                AND ORG.CORGLICODI IN (SELECT distinct(SOL.CORGLICODI) FROM SFPC.TBSOLICITACAOCOMPRA SOL)
                                                                                                        ORDER BY ORG.EORGLIDESC";

                                                                                                $res = $db->query($sql);

                                                                                                if (PEAR::isError($res)) {
                                                                                                    $CodErroEmail = $res->getCode();
                                                                                                    $DescErroEmail = $res->getMessage();
                                                                                                    var_export($DescErroEmail);
                                                                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
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
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">CNPJ Fornecedor</td>
                                                                                <td>
                                                                                    <input name="CnpjFornecedor" id="CnpjFornecedor" size="18" maxlength="18" value="<?php FormataCNPJ($CnpjFornecedor);?>" type="text" onChange="validaFornecedor('CnpjFornecedor','spanCpfCnpj');" />
                                                                                    <span id="spanCpfCnpj">
                                                                                        <?php   if ((! is_null($CnpjFornecedor)) || ($CnpjFornecedor != "")) {
                                                                                                    $CnpjFornecedor = removeSimbolos($CnpjFornecedor);
                                                                                                }
                                                                                        ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Média de Pesquisa</td>
                                                                                <td>
                                                                                    <select name="MediaPesquisa" class="textonormal">
                                                                                        <option value="N" <?php if ($MediaPesquisa == "N") { echo "selected='selected'"; } ?>>NÃO</option>
                                                                                        <option value="S" <?php if ($MediaPesquisa == "S") { echo "selected='selected'"; } ?>>SIM</option>
                                                                                    </select>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Incluir Preços Expurgados</td>
                                                                                <td>
                                                                                    <input type="checkbox" name="CheckExpurgo" value="S" <?php if ($CheckExpurgo == "S") { echo("checked"); } ?>>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="13" align="right">
                                                            <input type="submit" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
                                                            <input type="submit" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
                                                            <input type="reset" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
                                                            <input type="hidden" name="Botao" value="teste">
                                                        </td>
                                                    </tr>
                                                    <?php   if ($Botao == "Pesquisar" && ($Orgao != "" && $Orgao != null)) {
                                                                $db = Conexao();

                                                                $MPS = 0;
                                                                $i = 0;
                                                                $itens = array();

                                                                if (($TipoPreco == 'T') || ($TipoPreco == 'L')) {
                                                                    $result = $db->query($consulta);

                                                                    if (PEAR::isError($result)) {
                                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $consulta");
                                                                    } else {
                                                                        while ($Linha = $result->fetchRow()) {
                                                                            if (isset($Linha[23]) && ! empty($Linha[23])) {
                                                                                $descricaoComissao = " (".$Linha[23].")";
                                                                            } else {
                                                                                $descricaoComissao = "";
                                                                            }

                                                                            if (($TipoPreco == 'T') || ($TipoPreco == 'L' && $OrigemPreco == 'T')) {
                                                                                if ($Linha[2] != null || $Linha[2] != "") {
                                                                                    $itens[$i]["Tipo"] = "COMPRA DIRETA";
                                                                                } else {
                                                                                    $itens[$i]["Tipo"] = "LICITAÇÃO";
                                                                                }

                                                                                $itens[$i]["PrecoUnit"] = $Linha[16];
                                                                                $MPS += $itens[$i]["PrecoUnit"];

                                                                                if ($Linha[17] == 'E') {
                                                                                    $itens[$i]["Expurg"] = "SIM";
                                                                                    $itens[$i]["Justf"] = $Linha[18];
                                                                                    $itens[$i]["User"] = $Linha[19];
                                                                                    $dataAux2 = explode("-", $Linha[20]);
                                                                                    $itens[$i]["DataAlt"] = $dataAux2[2]."/".$dataAux2[1]."/".$dataAux2[0];
                                                                                } else {
                                                                                    $itens[$i]["Expurg"] = "NÃO";
                                                                                    $itens[$i]["Justf"] = "";
                                                                                    $itens[$i]["User"] = "";
                                                                                    $itens[$i]["DataAlt"] = "";
                                                                                }
                                                                        
                                                                                if ($Linha[0] != null) { // Caso o item seja uma pesquisa de mercado
                                                                                    $dataAux2 = explode("-", $Linha[3]);
                                                                                    $itens[$i]["DataC"] = $Linha[3];
                                                                                    $itens[$i]["DataRef"] = $dataAux2[2]."/".$dataAux2[1]."/".$dataAux2[0];
                                                                                    $itens[$i]["Origem"] = "PM  ".$Linha[4];

                                                                                    if (empty($Linha[5])) {
                                                                                        $sqlforn = "SELECT  FORN.AFORCRCCGC ,FORN.NFORCRRAZS
                                                                                                    FROM    SFPC.TBFORNECEDORCREDENCIADO FORN
                                                                                                    WHERE   FORN.AFORCRSEQU IN (SELECT  AFORCRSEQU
                                                                                                                                FROM    SFPC.TBPESQUISAPRECOMERCADO
                                                                                                                                WHERE   CPESQMSEQU IN (SELECT  CPESQMSEQU
                                                                                                                                                       FROM    SFPC.TBTABELAREFERENCIALPRECOS
                                                                                                                                                       WHERE   CMATEPSEQU = $Material
                                                                                                                                                               AND CPESQMSEQU IS NOT NULL))";

                                                                                        $retforn = $db->query($sqlforn);
                                                                                        $lforn = $retforn->fetchRow();
                                                                                        $itens[$i]["Forn"] = FormataCNPJ($lforn[0])." - ".$lforn[1];
                                                                                    } else {
                                                                                        $itens[$i]["Forn"] = FormataCNPJ($Linha[5])." - ".$Linha[6];
                                                                                    }

                                                                                    $itens[$i]["Marca"] = $Linha[7];
                                                                                    $itens[$i]["Modelo"] = $Linha[8];
                                                                                    $itens[$i]["Ordem"] = "";
                                                                                    $itens[$i]["Lote"] = "";
                                                                                } else {
                                                                                    $dataAux = explode(" ", $Linha[9]);
                                                                                    $itens[$i]["DataC"] = $dataAux[0];                                                                              
                                                                                    $dataAux = explode("-", $dataAux[0]);
                                                                                    $itens[$i]["DataRef"] = $dataAux[2]."/".$dataAux[1]."/".$dataAux[0];
                                                                                    $itens[$i]["Origem"] = "PL  ".$Linha[10]."/".$Linha[11].$descricaoComissao;
                                                                                    $itens[$i]["Forn"] = FormataCNPJ($Linha[12])." - ".$Linha[13];
                                                                                    $itens[$i]["Marca"] = $Linha[14];
                                                                                    $itens[$i]["Modelo"] = $Linha[15];
                                                                                    $itens[$i]["Ordem"] = $Linha[22];
                                                                                    $itens[$i]["Lote"] = $Linha[21];
                                                                                }
                                                                            }

                                                                            if (($TipoPreco == 'L') && ($OrigemPreco == 'L')) {
                                                                                if ($Linha[2] != null || $Linha[2] != "") {
                                                                                    $itens[$i]["Tipo"] = "COMPRA DIRETA";
                                                                                } else {
                                                                                    $itens[$i]["Tipo"] = "LICITAÇÃO";
                                                                                }

                                                                                $itens[$i]["PrecoUnit"] = $Linha[10];
                                                                                $MPS += $itens[$i]["PrecoUnit"];
                                                                                
                                                                                if ($Linha[11] == 'E') {
                                                                                    $itens[$i]["Expurg"] = "SIM";
                                                                                    $itens[$i]["Justf"] = $Linha[12];
                                                                                    $itens[$i]["User"] = $Linha[13];
                                                                                    $dataAux2 = explode("-", $Linha[14]);
                                                                                    $itens[$i]["DataAlt"] = $dataAux2[2]."/".$dataAux2[1]."/".$dataAux2[0];
                                                                                } else {
                                                                                    $itens[$i]["Expurg"] = "NÃO";
                                                                                    $itens[$i]["Justf"] = "";
                                                                                    $itens[$i]["User"] = "";
                                                                                    $itens[$i]["DataAlt"] = "";
                                                                                }

                                                                                $dataAux = explode(" ", $Linha[3]);
                                                                                $itens[$i]["DataC"] = $dataAux[0];
                                                                                $dataAux = explode("-", $dataAux[0]);
                                                                                $itens[$i]["DataRef"] = $dataAux[2]."/".$dataAux[1]."/".$dataAux[0];
                                                                                $itens[$i]["Origem"] = "PL  ".$Linha[4]."/".$Linha[5];
                                                                                $itens[$i]["Forn"] = FormataCNPJ($Linha[6])." - ".$Linha[7];
                                                                                $itens[$i]["Marca"] = $Linha[8];
                                                                                $itens[$i]["Modelo"] = $Linha[9];
                                                                                $itens[$i]["Ordem"] = $Linha[16];
                                                                                $itens[$i]["Lote"] = $Linha[15];
                                                                            }

                                                                            if (($TipoPreco == 'L') && ($OrigemPreco == 'P')) { // Se tipo de preço = licitação e origem = pesquisa de mercado
                                                                                if ($Linha[2] != null || $Linha[2] != "") {
                                                                                    $itens[$i]["Tipo"] = "COMPRA DIRETA";
                                                                                } else {
                                                                                    $itens[$i]["Tipo"] = "LICITAÇÃO";
                                                                                }

                                                                                $itens[$i]["PrecoUnit"] = $Linha[9];
                                                                                $MPS += $itens[$i]["PrecoUnit"];

                                                                                if ($Linha[10] == 'E') {
                                                                                    $itens[$i]["Expurg"] = "SIM";
                                                                                    $itens[$i]["Justf"] = $Linha[11];
                                                                                    $itens[$i]["User"] = $Linha[12];
                                                                                    $dataAux2 = explode("-", $Linha[13]);
                                                                                    $itens[$i]["DataAlt"] = $dataAux2[2]."/".$dataAux2[1]."/".$dataAux2[0];
                                                                                } else {
                                                                                    $itens[$i]["Expurg"] = "NÃO";
                                                                                    $itens[$i]["Justf"] = "";
                                                                                    $itens[$i]["User"] = "";
                                                                                    $itens[$i]["DataAlt"] = "";
                                                                                }

                                                                                $itens[$i]["DataC"] = $Linha[3];
                                                                                $dataAux = explode("-", $Linha[3]);
                                                                                $itens[$i]["DataRef"] = $dataAux[2]."/".$dataAux[1]."/".$dataAux[0];
                                                                                $itens[$i]["Origem"] = "PM  ".$Linha[4];

                                                                                if (empty($Linha[5])) {
                                                                                    $sqlforn = "SELECT  FORN.AFORCRCCGC ,FORN.NFORCRRAZS
                                                                                                FROM    SFPC.TBFORNECEDORCREDENCIADO FORN
                                                                                                WHERE   FORN.AFORCRSEQU = (SELECT   AFORCRSEQU
                                                                                                                           FROM     SFPC.TBPESQUISAPRECOMERCADO
                                                                                                                           WHERE    CPESQMSEQU IN (SELECT   CPESQMSEQU
                                                                                                                                                   FROM     SFPC.TBTABELAREFERENCIALPRECOS
                                                                                                                                                   WHERE    CMATEPSEQU = $Material
                                                                                                                                                            AND CPESQMSEQU IS NOT NULL))";

                                                                                    $retforn = $db->query($sqlforn);
                                                                                    $lforn = $retforn->fetchRow();
                                                                                    $itens[$i]["Forn"] = FormataCNPJ($lforn[0])." - ".$lforn[1];
                                                                                } else {
                                                                                    $itens[$i]["Forn"] = FormataCNPJ($Linha[5])." - ".$Linha[6];
                                                                                }

                                                                                $itens[$i]["Marca"] = $Linha[7];
                                                                                $itens[$i]["Modelo"] = $Linha[8];
                                                                                $itens[$i]["Lote"] = "";
                                                                                $itens[$i]["Ordem"] = "";
                                                                            }
                                                                            $i ++;
                                                                        }
                                                                    }
                                                                }

                                                                if ($TipoPreco == 'C' || $TipoPreco == 'T') { // Se tipo de preço = compra direta ou todos
                                                                    $result1 = $db->query($consulta1);

                                                                    if ($TipoPreco == 'C') {
                                                                        $MediaTRP = calcularValorTrp($db, TIPO_COMPRA_DIRETA, $Material);
                                                                        $MediaTRP = converte_valor_estoques($MediaTRP);
                                                                    }

                                                                    if (PEAR::isError($result)) {
                                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $consulta1");
                                                                    } else {
                                                                        while ($Linha1 = $result1->fetchRow()) {
                                                                            if ($Linha1[0] != null || $Linha1[0] != "") {
                                                                                $itens[$i]["Tipo"] = "COMPRA DIRETA";
                                                                            } else {
                                                                                $itens[$i]["Tipo"] = "LICITAÇÃO";
                                                                            }

                                                                            $dataAux = explode(" ", $Linha1[1]);
                                                                            $itens[$i]["DataC"] = $dataAux[0];
                                                                            $dataAux = explode("-", $dataAux[0]);
                                                                            $itens[$i]["DataRef"] = $dataAux[2]."/".$dataAux[1]."/".$dataAux[0];
                                                                            $temp = "SCC ";
                                                                            $urlScc = "../compras/ConsAcompSolicitacaoCompra.php?SeqSolicitacao=".$Linha1[0]."&programa=window";
                                                                            
                                                                            if ($Linha1[2] < 10) {
                                                                                $temp .= "0".$Linha1[2];
                                                                            } else {
                                                                                $temp .= $Linha1[2];
                                                                            }

                                                                            if ($Linha1[3] < 10) {
                                                                                $temp .= "0".$Linha1[3].".";
                                                                            } else {
                                                                                $temp .= $Linha1[3].".";
                                                                            }

                                                                            if ($Linha1[4] < 10) {
                                                                                $temp .= "000".$Linha1[4].".";
                                                                            } elseif (($Linha1[4] < 100) && ($Linha1[4] >= 10)) {
                                                                                $temp .= "00".$Linha1[4].".";
                                                                            } elseif (($Linha1[4] < 1000) && ($Linha1[4] >= 100)) {
                                                                                $temp .= "0".$Linha1[4].".";
                                                                            } else {
                                                                                $temp .= $Linha1[4].".";
                                                                            }

                                                                            $temp .= $Linha1[5];
                                                                            
                                                                            $itens[$i]["Origem"] = "<a href=\"javascript:AbreJanela('$urlScc');\">".$temp."</a>";
                                                                            $itens[$i]["Lote"] = "";
                                                                            $itens[$i]["Ordem"] = $Linha1[15];
                                                                            $itens[$i]["Forn"] = FormataCNPJ($Linha1[6])." - ".$Linha1[7];
                                                                            $itens[$i]["Marca"] = $Linha1[8];
                                                                            $itens[$i]["Modelo"] = $Linha1[9];
                                                                            $itens[$i]["PrecoUnit"] = $Linha1[10];
                                                                            $MPS += $itens[$i]["PrecoUnit"];

                                                                            if ($Linha1[11] == 'E') {
                                                                                $itens[$i]["Expurg"] = "SIM";
                                                                                $itens[$i]["Justf"] = $Linha1[12];
                                                                                $itens[$i]["User"] = $Linha1[13];
                                                                                $dataAux2 = explode("-", $Linha1[14]);
                                                                                $itens[$i]["DataAlt"] = $dataAux2[2]."/".$dataAux2[1]."/".$dataAux2[0];
                                                                            } else {
                                                                                $itens[$i]["Expurg"] = "NÃO";
                                                                                $itens[$i]["Justf"] = "";
                                                                                $itens[$i]["User"] = "";
                                                                                $itens[$i]["DataAlt"] = "";
                                                                            }   
                                                                            $i ++;
                                                                        } // final do while
                                                                    } // final do else
                                                                }

                                                                if (count($itens)) {
                                                    ?>
                                                    <tr>
                                                        <td align="center" bgcolor="#DCEDF7" valign="middle" class="titulo3" colspan="13">RESULTADO DA PESQUISA</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="13">
                                                            <table width="100%" class="textonormal" border="0" width="100%" summary="">
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Média do Preço TRP para Licitação</td>
                                                                    <td class="textonormal"><?php $MediaTRP;?></td>
                                                                </tr>
                                                                <?php if ($MediaPesquisa == 'S') { ?>
                                                                <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" width="20%" height="20" align="center">Média da Pesquisa</td>
                                                                    <td class="textonormal"><?php echo converte_valor_estoques($MPS/count($itens)); ?></td>
                                                                </tr>
                                                                <?php } ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">TIPO DE PREÇO TRP</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">DATA REFERÊNCIA</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">ORIGEM DO PREÇO</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">LOTE</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">ORDEM</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">FORNECEDOR</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">MARCA</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">MODELO</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">PREÇO UNITÁRIO</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">EXPURGADO</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">JUSTIFICATIVA</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">USUÁRIO ALTERAÇÃO</td>
                                                        <td class="textonormal" bgcolor="#75ADE6" align="center">DATA ALTERAÇÃO</td>
                                                    </tr>
                                                    <tr>
                                                        <?php       // Ordenação do array a ser exibido na tela
                                                                    foreach ($itens as $c => $key) {
                                                                        $sort_data[] = $key['DataC'];
                                                                    }

                                                                    array_multisort($sort_data, SORT_ASC, $itens);
                                                                    $controle = 0;
                                                            
                                                                    while ($controle < $i) {
                                                                        echo "<tr><td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Tipo"]."</td>"; // TIPO DE PREÇO
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["DataRef"]."</td>"; // DATA REFERENCIA
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Origem"]."</td>";
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Lote"]."</td>"; // LOTE
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Ordem"]."</td>"; // ORDEM
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Forn"]."</td>"; // FORNECEDOR [CNPJ/RAZÃO SOCIAL]
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Marca"]."</td>"; // MARCA
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Modelo"]."</td>"; // MODELO
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"right\">&nbsp;".converte_valor_estoques($itens[$controle]["PrecoUnit"])."</td>"; // PREÇO UNITÁRIO
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Expurg"]."</td>"; // EXPURGADO
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["Justf"]."</td>"; // JUSTIFICATIVA
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["User"]."</td>"; // USUARIO ALTERAÇÃO
                                                                        echo "<td bgcolor=\"#BFDAF2\" align=\"center\">&nbsp;".$itens[$controle]["DataAlt"]."</td></tr>\n"; // DATA ALTERAÇÃO
                                                                        $controle ++;
                                                                    }
                                                                } else {
                                                        ?>
                                                        <tr>
                                                            <tr>
                                                                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="13">NENHUM RESULTADO ENCONTRADO PARA ESTA PESQUISA</td>
                                                            </tr>
                                                            <?php }
                                                               $db->disconnect();
                                                            ?>
                                                        </tr>
                                                        <?php } ?>
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
