<?php
/**
 * Portal de Compras
 * 
 * Programa: CadLote.php
 * Autor:    Raphael Borborema
 * Data:     19/03/2012
 * Objetivo: Manutenção de lotes de itens da solicitação
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     27/05/2014
 * Objetivo: [CR123142]: REDMINE 22 (P5)
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     27/05/2014
 * Objetivo: [CR Redmine 74235] Checar porque versão de produção "deixou" de ter as CRs redmine 22 e 23
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     03/01/2019
 * Objetivo: Tarefa Redmine 208507
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     13/05/2019
 * Objetivo: Tarefa Redmine 216765
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     10/10/2022
 * Objetivo: Tarefa Redmine 206442
 * ------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     25/05/2023
 * Objetivo: Tarefa Redmine 276985
 * ------------------------------------------------------------------------------------------------------------------------
 */

$programa = "CadLote.php";

# Acesso ao arquivo de funções #
require_once "../compras/funcoesCompras.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Abrindo Conexão
$db = Conexao();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao             = $_POST['Botao'];
    $Licitacao         = $_POST['Licitacao'];
    $intCodUsuario     = $_SESSION['_cusupocodi_'];
    $perfilCorporativo = $_SESSION['_fperficorp_'];
    $GrupoUsuario      = $_SESSION['_cgrempcodi_'];

    $CLICPOPROC = $_POST['CLICPOPROC'];
    $ALICPOANOP = $_POST['ALICPOANOP'];
    $CCOMLICODI = $_POST['CCOMLICODI'];
    $CORGLICODI = $_POST['CORGLICODI'];

    //Itens
    $arrSequencial         = $_POST["arrSequencial"];
    $arrDescricao          = $_POST["arrDescricao"];
    $arrTipo               = $_POST["arrTipo"];
    $arrCodRed             = $_POST["arrCodRed"];
    $arrUnidade            = $_POST["arrUnidade"];
    $arrQuantidade         = $_POST["arrQuantidade"];
    $arrValorEstimado      = $_POST["arrValorEstimado"];
    $arrValorTotalEstimado = $_POST["arrValorTotalEstimado"];
    $arrLote               = $_POST["arrLote"];
    $TipoBeneficio         = $_POST["TratamentoDiferenciado"];
    $arrDescricaoDetalhada = filter_input(INPUT_POST, 'arrDescricaoDetalhada');
} else {
    $Mensagem = urldecode($_GET['Mensagem']);
    $Mens     = $_GET['Mens'];
    $Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Situação #
$LicitacaoStatus = "I";

if ($Botao == "SelecionarLicitacao") {
    $lote = false;
    unset($arrLote);
    
    if (isset($Licitacao) && $Licitacao != "" && !is_null($Licitacao)) {
        $lote = true;
        $Licitacao = explode("_", $Licitacao);
    } else {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.formulario.Licitacao.focus();\" class=\"titulo2\">Selecione uma licitação</a>";
    }
} elseif ($Botao == "ManterLotes") {
    $lote = true;
    $Licitacao = array();
    $Licitacao[0] = $CLICPOPROC;
    $Licitacao[1] = $ALICPOANOP;
    $Licitacao[2] = $CCOMLICODI;

    $count1 = 0;
    $i = 1;

    foreach ($arrLote as $key => $loteItem) {
        if ($loteItem == "") {
            $count1++;
        }
    }

    foreach ($arrLote as $key => $loteItem) {
        if ($loteItem == "") {
            $Mens      = 1;
            $Tipo      = 2;

            if ($i < $count1) {
                $Mensagem .= "<a href=\"javascript:document.getElementById('arrLote[$key]').focus();\" class=\"titulo2\">Item($key) do processo licitatório sem número do lote.</a><br>";
                $i++;
            } else {
                $Mensagem .= "<a href=\"javascript:document.getElementById('arrLote[$key]').focus();\" class=\"titulo2\">Item($key) do processo licitatório sem número do lote</a>";
            }
        }
    }

    if ($Mens == 0) {
        $arrLoteChecagem = array_unique($arrLote);
        sort($arrLoteChecagem);
        $existePosicao = array_search("1", $arrLoteChecagem);

        if ($existePosicao === false) {
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">O lote numero 1 não foi selecionado para nenhum item</a>";
        } else {
            foreach ($arrLoteChecagem as $key => $numLote) {
                if ((isset($arrLoteChecagem[$key+1])) && ($arrLoteChecagem[$key+1] > ($arrLoteChecagem[$key] + 1))) {
                    $Mens      = 1;
                    $Tipo      = 2;
                    $Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">Algum número de lote não foi associado a um item do processo licitatório</a>";
                }
            }
        }
    }

    if ($Mens == 1) {
        $Botao = "SelecionarLicitacao";
        $lote = true;
    }
} elseif ($Botao == "ConfirmarLotes") {
    $db = Conexao();
    $Licitacao = array();
    $Licitacao[0] = $CLICPOPROC;
    $Licitacao[1] = $ALICPOANOP;
    $Licitacao[2] = $CCOMLICODI;
    $sql  = "SELECT LIC.CCOMLICODI, COM.ECOMLIDESC, LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CMODLICODI, ";
    $sql .= "       MOD.EMODLIDESC, LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, ";
    $sql .= "       LIC.CORGLICODI, LIC.flicpovfor ";
    $sql .= "FROM    SFPC.TBLICITACAOPORTAL LIC ";
    $sql .= "       INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI ";
    $sql .= "       INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI ";
    $sql .= "WHERE   LIC.CLICPOPROC = $Licitacao[0] ";
    $sql .= "       AND LIC.ALICPOANOP = $Licitacao[1] ";
    $sql .= "       AND LIC.CCOMLICODI = $Licitacao[2] ";
    $sql .= "       AND LIC.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
    $sql .= "ORDER BY LIC.CCOMLICODI ASC ";

    $res = $db->query($sql);

    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        var_export($DescErroEmail);
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    } else {
        $Linha = $res->fetchRow();
    }
    if (!is_array($arrLote) || count($arrLote) <= 0) {
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">Nenhum Item</a>";
    }

    $Licitacao = array();
    $Licitacao[0] = $CLICPOPROC;
    $Licitacao[1] = $ALICPOANOP;
    $Licitacao[2] = $CCOMLICODI;

    $db->query("BEGIN TRANSACTION");
    $j = 0;
    foreach ($arrLote as $lote => $arrItens) {
        $i = 0;
        
        
        foreach ($arrItens as $item) {
            $i++;
            
           
            $sql  = "UPDATE SFPC.TBITEMLICITACAOPORTAL ";
            $sql .= "SET    AITELPORDE = $i, ";
            $sql .= "       CITELPNUML = $lote, ";
            $sql .= "       CUSUPOCODI = $intCodUsuario, ";
            if ($Linha[11] == "C"){

                if($_POST['arrTipoBeneficio'][$j] == 'RESERVADO MEI/EPP/ME'){
                    $CodigoBeneficio = 1;
                }
                if($_POST['arrTipoBeneficio'][$j] == 'SUBCONTRATAÇÃO MEI/EPP/ME'){
                    $CodigoBeneficio = 2;
                }             
                if($_POST['arrTipoBeneficio'][$j] == "AMPLA CONCORRÊNCIA"){
                    $CodigoBeneficio = 4;
                }
                $sql .= "   fitelptbe = $CodigoBeneficio, ";
                $j++;
            }
            $sql .= "       TITELPULAT = '".Date("Y-m-d h:i:s")."' ";
            $sql .= "WHERE   CLICPOPROC = $Licitacao[0] ";
            $sql .= "       AND CITELPSEQU = $item ";
            $sql .= "       AND ALICPOANOP = $Licitacao[1] ";
            $sql .= "       AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
            $sql .= "       AND CCOMLICODI = $Licitacao[2] ";
            $sql .= "       AND CORGLICODI = $CORGLICODI  \n\n ";
            
         

            $result = $db->query($sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                var_dump($db);
                cancelarTransacao($db);
            }
        }
        
    }
    
    $db->query("COMMIT");
    $db->query("END TRANSACTION");

    $Mensagem  = "Composição efetuada com sucesso";
    
    if ($_SESSION['_cperficodi_'] == 2) {
        $Mensagem .= ". Verificar o desdobramento desta alteração nos Módulos de Registro de Preços e Contratos";
    }
    
    $Mens = 1;
    $Tipo = 1;
    $lote = false;
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">

    function enviar(valor){
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
    <?php MenuAcesso(); ?>
    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="<?=$programa?>" method="post" name="formulario">
    <input type="hidden" name="Botao" id="Botao"value="">
    <br><br><br><br><br>
    <table width="100%" cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <tr>
            <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
            <td align="left" class="textonormal" colspan="2">
                <font class="titulo2">|</font>
                <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Lote
            </td>
        </tr>
        <!-- Fim do Caminho-->
        <!-- Erro -->
        <?php
        if ($Mens == 1) {
            ?>
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2"><?php ExibeMens($Mensagem, $Tipo, 1); ?></td>
            </tr>
            <?php
        }
        ?>
        <!-- Fim do Erro -->
        <!-- Corpo -->
        <tr>
            <td width="150"></td>
            <td class="textonormal">
                <table width="50%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
                            LOTE - LICITAÇÃO
                        </td>
                    </tr>
                    <?php
                    if (!$lote) {
                        ?>
                        <tr>
                            <td colspan="4">
                                <table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7" width="5%" height="20">Processo</td>
                                        <td class="textonormal">
                                            <select name="Licitacao" class="textonormal">
                                                <option value="">Selecione um Processo Licitatório...</option>
                                                <?php
                                                $sql  = "SELECT LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CCOMLICODI, COM.ECOMLIDESC ";
                                                $sql .= "FROM   SFPC.TBLICITACAOPORTAL LIC ";
                                                $sql .= "       INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI ";
                                                $sql .= "       INNER JOIN SFPC.TBUSUARIOCOMIS USU ON USU.CCOMLICODI = LIC.CCOMLICODI AND LIC.CGREMPCODI = USU.CGREMPCODI ";
                                                $sql .= "       INNER JOIN SFPC.TBFASELICITACAO FAS ON FAS.CLICPOPROC = LIC.CLICPOPROC AND FAS.ALICPOANOP = LIC.ALICPOANOP "; 
                                                $sql .= "       AND FAS.CGREMPCODI = LIC.CGREMPCODI AND FAS.CCOMLICODI = LIC.CCOMLICODI AND FAS.CORGLICODI = LIC.CORGLICODI ";
                                                $sql .= "       WHERE MAKE_DATE(LIC.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '3 YEARS' ";
                                                $sql .= "       AND USU.CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
                                                $sql .= "       AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
                                                $sql .= "       AND NOT EXISTS (SELECT 1 ";
                                                $sql .= "       FROM SFPC.TBFASELICITACAO FAS2 ";
                                                $sql .= "       WHERE FAS2.CLICPOPROC = FAS.CLICPOPROC ";
                                                $sql .= "       AND FAS2.ALICPOANOP = FAS.ALICPOANOP ";
                                                $sql .= "       AND FAS2.CGREMPCODI = FAS.CGREMPCODI ";
                                                $sql .= "       AND FAS2.CCOMLICODI = FAS.CCOMLICODI ";
                                                $sql .= "       AND FAS2.CORGLICODI = FAS.CORGLICODI ";

                                                    if ($_SESSION['_cperficodi_'] != 2) {
                                                        $sql .= "AND FAS2.CFASESCODI = 1 ";
                                                    }
                                                
                                                $sql .= "       AND FAS2.TFASELULAT > FAS.TFASELULAT ) ";
                                                $sql .= "       AND EXISTS ( SELECT 1";
                                                $sql .= "       FROM SFPC.TBSOLICITACAOLICITACAOPORTAL ";
                                                $sql .= "       WHERE CLICPOPROC = FAS.CLICPOPROC ";
                                                $sql .= "       AND ALICPOANOP = FAS.ALICPOANOP ";
                                                $sql .= "       AND CGREMPCODI = FAS.CGREMPCODI ";
                                                $sql .= "       AND CCOMLICODI = FAS.CCOMLICODI ";
                                                $sql .= "       AND CORGLICODI = FAS.CORGLICODI ) ";
                                                $sql .= "       ORDER BY COM.ECOMLIDESC ASC, LIC.ALICPOANOP DESC, LIC.CLICPOPROC DESC";
                                                                    
                                                $result = $db->query($sql);

                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    while ($Linha = $result->fetchRow()) {
                                                        if ($Linha[2] != $ComissaoCodigoAnt) {
                                                            $ComissaoCodigoAnt = $Linha[2];
                                                            echo "<option value=\"\">$Linha[3]</option>\n";
                                                        }
                                                        $NProcesso = substr($Linha[0] + 10000, 1);
                                                        echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]\">&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class="textonormal" align="right" colspan="4">
                                <input type="button" name="SelecionarLicitacao" value="Selecionar" class="botao" onClick="javascript:enviar('SelecionarLicitacao')">
                            </td>
                        </tr>
                        <?php
                    }
                    $reservado = false;
                    if ($lote) {
                        $db = Conexao();

                        $sql  = "SELECT LIC.CCOMLICODI, COM.ECOMLIDESC, LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CMODLICODI, ";
                        $sql .= "       MOD.EMODLIDESC, LIC.FLICPOREGP, LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.XLICPOOBJE, ";
                        $sql .= "       LIC.CORGLICODI, LIC.flicpovfor ";
                        $sql .= "FROM    SFPC.TBLICITACAOPORTAL LIC ";
                        $sql .= "       INNER JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI ";
                        $sql .= "       INNER JOIN SFPC.TBMODALIDADELICITACAO MOD ON LIC.CMODLICODI = MOD.CMODLICODI ";
                        $sql .= "WHERE   LIC.CLICPOPROC = $Licitacao[0] ";
                        $sql .= "       AND LIC.ALICPOANOP = $Licitacao[1] ";
                        $sql .= "       AND LIC.CCOMLICODI = $Licitacao[2] ";
                        $sql .= "       AND LIC.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
                        $sql .= "ORDER BY LIC.CCOMLICODI ASC ";

                        $res = $db->query($sql);

                        if (PEAR::isError($res)) {
                            $CodErroEmail  = $res->getCode();
                            $DescErroEmail = $res->getMessage();
                            var_export($DescErroEmail);
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                        } else {
                            $Linha = $res->fetchRow();
                            if($Linha[11] == 'C'){
                                $reservado = true;
                            }
                        }

                        // Buscando e carregando array com as solicitacoes da licitacao
                        $sqlSolicitacoes  = "SELECT CSOLCOSEQU, CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
                        $sqlSolicitacoes .= "       CORGLICODI ";
                        $sqlSolicitacoes .= "FROM   SFPC.TBSOLICITACAOLICITACAOPORTAL SOL ";
                        $sqlSolicitacoes .= "WHERE  SOL.CLICPOPROC = $Licitacao[0] ";
                        $sqlSolicitacoes .= "       AND SOL.ALICPOANOP = $Licitacao[1] ";
                        $sqlSolicitacoes .= "       AND SOL.CCOMLICODI = $Licitacao[2] ";
                        $sqlSolicitacoes .= "       AND SOL.cgrempcodi = " . $_SESSION['_cgrempcodi_'];

                        $resultSoli = $db->query($sqlSolicitacoes);

                        if (PEAR::isError($resultSoli)) {
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
                        }

                        while ($LinhaSoli = $resultSoli->fetchRow()) {
                            $arrSolicitacoes[] = $LinhaSoli[0];
                        }

                        /* BUSCANDO OS ITENS DA LICITACAO */
                        $sql  = "SELECT ITEM.CITELPSEQU, ITEM.CMATEPSEQU, ITEM.CSERVPSEQU, ITEM.AITELPORDE, ITEM.AITELPQTSO, ";
                        $sql .= "       ITEM.VITELPUNIT, ITEM.AITELPQTEX, ITEM.VITELPVEXE, MAT.EMATEPDESC, MAT.CUNIDMCODI, ";
                        $sql .= "       SERV.ESERVPDESC, ITEM.CITELPNUML, UNIDADE.EUNIDMSIGL, ITEM.EITELPDESCMAT, ITEM.EITELPDESCSE, ITEM.fitelptbe ";
                        $sql .= "FROM   SFPC.TBITEMLICITACAOPORTAL ITEM ";
                        $sql .= "       LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON (MAT.CMATEPSEQU = ITEM.CMATEPSEQU) ";
                        $sql .= "       LEFT JOIN SFPC.TBSERVICOPORTAL SERV ON (SERV.CSERVPSEQU = ITEM.CSERVPSEQU) ";
                        $sql .= "       LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UNIDADE ON (MAT.CUNIDMCODI = UNIDADE.CUNIDMCODI) ";
                        $sql .= "       WHERE  ITEM.CLICPOPROC = $Licitacao[0] ";
                        $sql .= "       AND ITEM.ALICPOANOP = $Licitacao[1] ";
                        $sql .= "       AND ITEM.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
                        $sql .= "       AND ITEM.CCOMLICODI = $Licitacao[2] ";
                        $sql .= "       AND ITEM.CORGLICODI = $Linha[10] ";
    
                        if ($Botao == "ManterLotes") {
                            $sql .= " ORDER BY ITEM.CITELPNUML , ITEM.AITELPORDE , ITEM.CITELPSEQU ";
                        } else {
                            $sql .= " ORDER BY ITEM.CITELPNUML , ITEM.AITELPORDE , ITEM.CITELPSEQU";
                        }
                        
                        $resILTmp = $db->query($sql);
                        $resBeneficio = $db->query($sql);
                        $CodBeneficio = array();
                        while ($LinhaBeneficio = $resBeneficio->fetchRow()) {
                            $CodBeneficio[] = $LinhaBeneficio[15]; 
                            
                        }
                     
                        $resItensLicitacao  = $db->query($sql);
                        $intQuantidadeItens = $resItensLicitacao->numRows();
                        ?>
                        <tr>
                            <td>
                                <input name="CLICPOPROC" type="hidden" value="<?php echo $Licitacao[0];?>"/>
                                <input name="ALICPOANOP"  type="hidden"  value="<?php echo $Licitacao[1]; ?>"/>
                                <input name="CCOMLICODI" type="hidden" value="<?php echo $Licitacao[2]; ?>"/>
                                <input name="CORGLICODI" type="hidden" value="<?php echo $Linha[10]; ?>"/>
                                <table border="0" width="100%" summary="">
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Comissão*</td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label style="width:500px;"><?php echo $Linha[1]; ?></label>
                                            <input type="hidden" name="CodigoDaComissao" value="<?php echo $Linha[0]; ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Processo</td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo substr($Linha[2] + 10000, 1); ?></label>
                                            <input type="hidden" name="NumeroDoProcesso" value="<?php echo substr($Linha[2] + 10000, 1); ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano</td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $Linha[3]; ?></label>
                                            <input type="hidden" name="AnoDoExercicio" value="<?php echo $Linha[3]; ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Modalidade*</td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label><?php echo $Linha[5]; ?></label>
                                            <input type="hidden" name="AnoDoExercicio" value="<?php echo $Linha[4]; ?>"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Registro de Preço</td>
                                        <td align="left" class="textonormal" colspan="3">
                                            <label>
                                            <?php
                                                if (isset($Linha[6]) && $Linha[6] != "") {
                                                    if ($Linha[6] == "S") {
                                                        echo "Sim";
                                                    } elseif ($Linha[6] == "N") {
                                                        echo "Não";
                                                    }
                                                } else {
                                                    echo "-";
                                                }
                                                ?>
                                            </label>
                                            <input type="hidden" value="<?php echo $Linha[6]; ?>" name="RegistroPreco" />
                                        </td>
                                    </tr>
                                    <tr>
                <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Licitação</td>
                <td align="left" class="textonormal" colspan="3" >
                    <label><?php echo substr($Linha[7] + 10000, 1); ?></label>
                </td>
            </tr>
            <tr>
                <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Ano da Licitação</td>
                <td align="left" class="textonormal" colspan="3" >
                    <label><?php echo $Linha[8]; ?></label>
                    <input type="hidden" name="AnoDaLicitacao" value="<?php echo $Linha[8]; ?>" />
                </td>
            </tr>
            <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >Objeto:</td>
            <td>
                <label class="textonormal" style="word-wrap:break-word;" ><?php echo $Linha[9]; ?></label>
            </td>
            <tr>
                <td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" >
                    Solicitação de Compra/Contratação-SCC*:
                </td>
                <td align="left" class="textonormal" colspan="3" >
                    <select style="width:200px;" multiple="multiple">
                        <?php
                            foreach ($arrSolicitacoes as $seqSoli) {
                                ?>
                            <option selected="selected" value="<?php echo $seqSoli;
                                ?>" ><?php echo getNumeroSolicitacaoCompra($db, $seqSoli);
                                ?></option>
                            <?php

                            }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </td>
</tr>
<?php

$estilotd = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
$estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
$ordemBenecifio = 0;
if ($Botao == "SelecionarLicitacao") { ?>
    <tr>
        <td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4" >ITENS DA SOLICITAÇÃO</td>
    </tr>
    <tr>
        <td style="background-color:#F1F1F1;" colspan="4">
            <table bordercolor="#75ADE6" border="1" cellspacing="0" bgcolor="bfdaf2" width="100%" class="textonormal">
                <?php
                    $ORDEM = 0;
                    $SOMATORIO = 0;
                    $exibeTd = false;
                    // checando se existe material/serviços
                    while ($arrI = $resILTmp->fetchRow()) {
                        if (!empty($arrI[13])) {
                            $exibeTd = true;
                            break;
                        }

                        if (!empty($arrI[2])) {
                            $exibeTd = true;
                        }
                    }

                ?>
                <tr class="linhainfo">
                    <td <?php echo $estilotd; ?>>ORD</td>
                    <td <?php echo $estilotd; ?>>DESCRIÇÃO MATERIAL/SERVIÇO</td>
                    <td <?php echo $estilotd; ?>>TIPO</td>
                    <td <?php echo $estilotd; ?>>CÓD.RED</td>
                    <td <?php echo $estilotd; ?>>UNIDADE</td>
                    <?php if ($exibeTd) : ?>
                        <td <?php echo $estilotd; ?>>DESCRIÇÃO DETALHADA</td>
                    <?php
                        endif;
                    ?>
                    <td <?php echo $estilotd;?>>QUANTIDADE</td>
                    <td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
                    <td <?php echo $estilotd; ?>>VALOR TOTAL</td>
                    <td <?php echo $estilotd; ?>>LOTE</td>
                    <td <?php if($reservado){ echo $estilotd; ?>>TIPO DE BENEFÍCIO</td><?php } ?>
                </tr>
                <?php
                    while ($listaIntens = $resItensLicitacao->fetchRow()) {
                        $ORDEM++;
                        
                        if ($listaIntens[1] != "") {
                            $TIPO         = "CADUM";
                            $DESCRICAO     = $listaIntens[8];
                            $CODRED         = $listaIntens[1];
                            $UNIDADE     = $listaIntens[12];
                            $DESCRICAODETALHADA = $listaIntens[13];
                        } else {
                            $TIPO        = "CADUS";
                            $DESCRICAO    = $listaIntens[10]." - ";
                            $CODRED    = $listaIntens[2];
                            $UNIDADE    = "";
                            $DESCRICAODETALHADA = $listaIntens[14];
                        }

                        $SENQUENCIAL = $listaIntens[0];
                        $QUANTIDADE  = $listaIntens[4];
                        $VALORUNIT     = $listaIntens[5];

                        $VALORESTIMADO         = $VALORUNIT;
                        $VALORTOTALESTIMADO  = $VALORESTIMADO * $QUANTIDADE;
                        $SOMATORIO            += $VALORTOTALESTIMADO;
                        $LOTE                 = $listaIntens[11];

                        if (isset($arrLote[$ORDEM])) {
                            $LOTE = $arrLote[$ORDEM];
                        }

                ?>

                <tr>
                    <td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $listaIntens[3]; ?>
                        <input name="arrSequencial[<?php echo $ORDEM; ?>]" value="<?php echo $SENQUENCIAL; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo $DESCRICAO; ?>
                        <input name="arrDescricao[<?php echo $ORDEM; ?>]" value="<?php echo $DESCRICAO; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo $TIPO; ?>
                        <input name="arrTipo[<?php echo $ORDEM; ?>]" value="<?php echo $TIPO; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo $CODRED; ?><input name="arrCodRed[<?php echo $ORDEM; ?>]" value="<?php echo $CODRED; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo $UNIDADE; ?>
                        <input name="arrUnidade[<?php echo $ORDEM; ?>]" value="<?php echo $UNIDADE; ?>" type="hidden" />
                    </td>
                    <?php if ($exibeTd || $TIPO == "CADUS") : ?>
                        <?php if (!empty($DESCRICAODETALHADA)) : ?>
                            <td <?php echo $estiloClasstd; ?>>&nbsp;
                                <?php echo $DESCRICAODETALHADA; ?>
                        <?php else : ?>
                            <td <?php echo $estiloClasstd; ?>>&nbsp;---
                        <?php
                        endif;
                    endif; ?>
                    <input name="arrDescricaoDetalhada[<?php echo $ORDEM; ?>]" value="<?php echo $DESCRICAODETALHADA; ?>" type="hidden" />
                    </td>

                    <td <?php echo $estiloClasstd;  ?>>&nbsp;
                        <?php echo converte_valor_estoques($QUANTIDADE); ?>
                        <input name="arrQuantidade[<?php echo $ORDEM; ?>]" value="<?php echo $QUANTIDADE; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo converte_valor_estoques($VALORESTIMADO); ?>
                        <input name="arrValorEstimado[<?php echo $ORDEM; ?>]" value="<?php echo $VALORESTIMADO; ?>"  type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <?php echo converte_valor_estoques($VALORTOTALESTIMADO); ?>
                        <input name="arrValorTotalEstimado[<?php echo $ORDEM; ?>]" value="<?php echo $VALORTOTALESTIMADO; ?>" type="hidden" />
                    </td>
                    <td <?php echo $estiloClasstd; ?>>&nbsp;
                        <select id="arrLote[<?php echo $ORDEM; ?>]" name="arrLote[<?php echo $ORDEM; ?>]" >
                        <?php
                            if ((is_numeric($LOTE)) && ($LOTE>$intQuantidadeItens)) { ?>
                                <option value="<?php echo $LOTE; ?>"><?php echo $LOTE;?></option>
                            <?php
                            } else {
                                ?>
                                <option value="">Selecione</option>
                            <?php
                            }
                            for ($i = 1; $i <= $intQuantidadeItens; $i++) { ?>
                                <option <?php if ($i == $LOTE) { echo "selected='selected'";} ?> value="<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </option>
                            <?php
                            } ?>
                        </select>
                    </td>
                                       
                </tr>
                <?php
                    }
                ?>
                <tr>
                    <td  <?php echo $estiloClasstd; ?>>&nbsp;</td>
                    <td  <?php echo $estiloClasstd; ?>>&nbsp;<b>TOTAL GERAL:</b></td>
                    <td  class="textonormal"  bgcolor="#F7F7F7" colspan="7"  align="right" >&nbsp;<b><?php echo converte_valor_estoques($SOMATORIO); ?></b></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php
        } elseif ($Botao == "ManterLotes") {
        $arrLoteUniq = array_unique($arrLote);
        sort($arrLoteUniq);
        $ORDEM = 0;
        $ordemCodBeneficio = 0;
        foreach ($arrLoteUniq as $lote) {
            $arrItens = array_keys($arrLote, $lote);
            var_dump($lote,);
            ?>
            <tr>
                <td align="center" bgcolor="#75ADE6" class="titulo3" colspan="4" ><span>Lote <?php echo $lote?>  </span> 
                <select align="right" style="margin-left: 100px;" name="TratamentoDiferenciado[<?php echo $lote; ?>]" id="TratamentoDiferenciado[<?php echo $lote; ?>]">
                      <option value="" <?php echo ($CodBeneficio[$ordemBenecifio]==1)?'selected':''; ?>>Selecione um Beneficio</option>
                      <option value="1" <?php echo ($CodBeneficio[$ordemBenecifio]==1)?'selected':''; ?>>RESERVADO MEI/EPP/ME</option>
                      <option value="2" <?php echo ($CodBeneficio[$ordemBenecifio]==2)?'selected':''; ?>>SUBCONTRATAÇÃO MEI/EPP/ME</option>
                      <option value="4" <?php echo ($CodBeneficio[$ordemBenecifio]==4)?'selected':''; ?>>AMPLA CONCORRÊNCIA</option>
                  </select></td>

    </tr>
    <tr>
        <td style="background-color:#F1F1F1;" colspan="4">
            <table cellspacing="0" bordercolor="#75ADE6" border="1"  bgcolor="bfdaf2" width="100%" class="textonormal">

                <tr class="linhainfo">
    <td <?php echo $estilotd;
            ?>>ORD</td>
                <td <?php echo $estilotd;
            ?>>DESCRIÇÃO MATERIAL/SERVIÇO</td>
                <td <?php echo $estilotd;
            ?>>TIPO</td>
                <td <?php echo $estilotd;
            ?>>CÓD.RED</td>
                <td <?php echo $estilotd;
            ?>>UNIDADE</td>
                <td <?php echo $estilotd;
            ?>>QUANTIDADE</td>
                <td <?php echo $estilotd;
            ?>>VALOR ESTIMADO</td>
                <td <?php echo $estilotd;
            ?>>VALOR TOTAL</td>
            <?php if($reservado){ ?>
                <td <?php echo $estilotd;
            ?>>TIPO DE BENEFÍCIO</td>
            <?php } ?>
                </tr>

                <?php
                $ORDEMITEM = 0;
                $valorSomatorio = 0;

                foreach ($arrItens as $item) {

                    $ORDEM++;
                    $ORDEMITEM++;
                    $valorSomatorio += $arrValorTotalEstimado[$item];
                    
                    ?>
                    <tr>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo $ORDEMITEM;
    ?> <input name="arrLote[<?php echo $lote;
    ?>][<?php echo $ORDEM;
    ?>]" value="<?php echo $arrSequencial[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo $arrDescricao[$item];
    ?><input name="arrDescricao[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrDescricao[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo $arrTipo[$item];
    ?><input name="arrTipo[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrTipo[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo $arrCodRed[$item];
    ?><input name="arrCodRed[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrCodRed[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo $arrUnidade[$item];
    ?><input name="arrUnidade[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrUnidade[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo converte_valor_estoques($arrQuantidade[$item]);
    ?><input name="arrQuantidade[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrQuantidade[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo converte_valor_estoques($arrValorEstimado[$item]);
    ?><input name="arrValorEstimado[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrValorEstimado[$item];
    ?>"  type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php echo converte_valor_estoques($arrValorTotalEstimado[$item]);
    ?><input name="arrValorTotalEstimado[<?php echo $ORDEM;
    ?>]" value="<?php echo $arrValorTotalEstimado[$item];
    ?>" type="hidden" /></td>
                    <td <?php echo $estiloClasstd;
                    ?>>&nbsp;<?php 
                    if($reservado){
                    if($TipoBeneficio[$ORDEM] == "1"){
                        $TipoBeneficioNome[$ORDEM] = 'RESERVADO MEI/EPP/ME';
                        print_r($TipoBeneficioNome[$ORDEM]);
                    }
                    if($TipoBeneficio[$ORDEM] == "2"){
                        $TipoBeneficioNome[$ORDEM] = 'SUBCONTRATAÇÃO MEI/EPP/ME';
                        print_r($TipoBeneficioNome[$ORDEM]);
                    }
                    if($TipoBeneficio[$ORDEM] == "4"){
                        $TipoBeneficioNome[$ORDEM] = 'AMPLA CONCORRÊNCIA';
                        print_r($TipoBeneficioNome[$ORDEM]);
                    }

                    ?><input name="arrTipoBeneficio[]" value="<?php echo $TipoBeneficioNome[$ORDEM];
                                ?>" type="hidden" /></td>
                    <?php } ?>
                </tr>
                <?php

                }
            ?>
                <tr>
                <td  <?php echo $estiloClasstd;
            ?>>&nbsp;</td>
                <td  <?php echo $estiloClasstd;
            ?>>&nbsp;<b>TOTAL LOTE <?php echo $lote?></b></td>
                <td  class="textonormal"  bgcolor="#F7F7F7" colspan="6"  align="right" >&nbsp;<b><?php echo converte_valor_estoques($valorSomatorio);
            ?></b></td>


                </tr>

                        </table>
                        </td>
                </tr>
                <?php

        }
    }
?>


<tr>
    <td class="textonormal" align="right" colspan="4">
    <?php if ($Botao == "SelecionarLicitacao") {
    ?>
                        <input type="button" name="ManterLotes" value="Manter Lote" class="botao" onClick="javascript:enviar('ManterLotes')">
                    <?php

} elseif ($Botao == "ManterLotes") {
    ?>
                        <input type="button" name="ManterLote" value="Confirmar Lote" class="botao" onClick="javascript:enviar('ConfirmarLotes')">
                    <?php

}
    ?>
        <input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
    </td>
</tr>
<?php

} ?>
            </table>
        </td>
    </tr>
    <!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect(); ?>
</html>
