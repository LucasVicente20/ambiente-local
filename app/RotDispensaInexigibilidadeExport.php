<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package Novo Layout
 * @author Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 * @version Git: $Id:$
 *========================================================
 * autor: Eliakim Ramos
 * Tarefa: CR#237104
 * Data: 20/08/2020
 *========================================================
 */

if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao", 1);
}

//CAIO MELQUIADES - CLASSES EXPORT
require(dirname(__FILE__).'/export/ExportaCSV.php');
require(dirname(__FILE__).'/export/ExportaXLS.php');
require(dirname(__FILE__).'/export/ExportaODS.php');

# Adiciona páginas no MenuAcesso #
//AddMenuAcesso('/oracle/licitacoes/RotDispensaInexigibilidadeDetalhes.php');
$dados = array();
# Variáveis com o global off #

if($_POST['DataIni'] != ''){
	$_SESSION['Opcao'] = $_POST['Opcao'];
	$_SESSION['OrgaoUnidadeP'] = $_POST['OrgaoUnidadeP'];
	$_SESSION['DataIni'] = $_POST['DataIni'];
	$_SESSION['DataFim'] = $_POST['DataFim'];
	$_SESSION['ObjetoP'] = $_POST['ObjetoP'];
}

$Botao = $_GET['Botao'];
$Opcao = $_POST['Opcao'];
$ObjetoP = urldecode($_POST['ObjetoP']);
$OrgaoUnidadeP = $_POST['OrgaoUnidadeP'];
$OrgaoUnidadeP = explode("-", $OrgaoUnidadeP);
$Orgao = $OrgaoUnidadeP[0];
$Unidade = $OrgaoUnidadeP[1];
$DataIni = $_POST['DataIni'];
$DataFim = $_POST['DataFim'];

//funcao pra pegar os dados da lei para a licitacao do tipo dispensa
function pegaleiDispensa($Ano, $TipoDisIne, $Orgao, $dbora){
	$sql  = "  SELECT    LIC.CLEIFENUME, LIC.CLEIARARTI, LIC.CLEIARINCI, to_char(LEI.DLEIFEDATA,'YYYY/MM/DD'), "; // Lei
	$sql  .= "            LIC.VPRDISVALO, LIC.APRDISSEQU, LIC.APRDISSITDI ";                                                        // Valor total da licitação
	$sql  .= "            FROM SFCO.TBPROCESSODISPENSA LIC, ";
	$sql  .= "            SPCS.TBLEI LEI ";
	$sql  .= "            WHERE LIC.CLEIFENUME = LEI.CLEIFENUME ";                                                       // chave Licitação/Lei
	$sql  .= "            AND LIC.DEXERCANOR = $Ano  AND LIC.CTPLICCODI = $TipoDisIne AND LIC.CORGORCODI = $Orgao "; 
    $resuldado = $dbora->query($sql);
    return $resuldado->fetchRow();  // Dados da Licitação
}

function getvalorEVencedor($Select, $Ano, $Numero, $Orgao, $TipoDisIne, $dbora){
    if ($Select == 1){
        $sql   = "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ";                                       // Chaves - Ano, Tipo, Número da licitação
        $sql  .= "       ITE.DEXERCANOR, ITE.CORGORCODI, ITE.CUNDORCODI, ";                                       // Exercício, Código do órgão e Unidade do Órgão
        $sql  .= "       to_char(LIC.DLICITHOML,'YYYY/MM/DD'), to_char(LIC.DLICITVIGE,'YYYY/MM/DD'), ";           // Data de Publicação e Vigência
        $sql  .= "       LIC.XLICITOBJE, ";                                                                       // Objeto
        $sql  .= "       LIC.CLEIFENUME, LIC.CLEIARARTI, LIC.CLEIARINCI, to_char(LEI.DLEIFEDATA,'YYYY/MM/DD'), "; // Lei
        $sql  .= "       LIC.VLICITLICI, '' ";                                                                    // Valor total da licitação
        $sql  .= "  FROM SFCO.TBLICITACAO LIC, ";
        $sql  .= "       SPCS.TBLEI LEI, ";
        $sql  .= "       SFCO.TBITEMLICITACAO ITE ";
        $sql  .= " WHERE LIC.CLEIFENUME = LEI.CLEIFENUME ";                                                       // chave Licitação/Lei
        $sql  .= "   AND LIC.ALICITANOL = ITE.ALICITANOL "; 							                                        // chave Licitação/Item
        $sql  .= "   AND LIC.CTPLICCODI = ITE.CTPLICCODI ";								                                        // chave Licitação/Item
        $sql  .= "   AND LIC.ALICITLICI = ITE.ALICITLICI "; 							                                        // chave Licitação/Item
        $sql  .= "   AND LIC.ALICITANOL = $Ano AND LIC.ALICITLICI = $Numero AND LIC.CTPLICCODI = $TipoDisIne ";   // Dados da Licitação
    }elseif($Select == 2) {
        $sql   = "SELECT LIC.DEXERCANOR, LIC.CTPLICCODI, LIC.APRDISSEQ2, ";                                       // Chaves - Ano, Tipo, Número da licitação
        $sql  .= "       LIC.DEXERCANOR, LIC.CORGORCODI, LIC.CUNDORCODI, ";                                       // Exercício, Código do órgão e Unidade do Órgão
        $sql  .= "       to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), to_char(LIC.DPRDISVIGE,'YYYY/MM/DD'), ";           // Data de Publicação e Vigência
        $sql  .= "       LIC.XPRDISOBJE, ";                                                                       // Objeto
        $sql  .= "       LIC.CLEIFENUME, LIC.CLEIARARTI, LIC.CLEIARINCI, to_char(LEI.DLEIFEDATA,'YYYY/MM/DD'), "; // Lei
        $sql  .= "       LIC.VPRDISVALO, LIC.APRDISSEQU, LIC.APRDISSITDI ";                                                        // Valor total da licitação
        $sql  .= "  FROM SFCO.TBPROCESSODISPENSA LIC, ";
        $sql  .= "       SPCS.TBLEI LEI ";
        $sql  .= " WHERE LIC.CLEIFENUME = LEI.CLEIFENUME ";                                                       // chave Licitação/Lei
        $sql  .= "   AND LIC.DEXERCANOR = $Ano AND LIC.APRDISSEQ2 = $Numero AND LIC.CTPLICCODI = $TipoDisIne AND LIC.CORGORCODI = $Orgao ";   // Dados da Licitação
    }
    
     $res  = $dbora->query($sql);
     $dadosQ = $res->fetchRow();
    
    $sql   = "SELECT  CRE.CTPCRECODI, CRE.ACREDONUME, CRE.ACREDOCGCC, CRE.ACREDOCPFF, CRE.NCREDONOME, ";
	$sql  .= "       NVL(ITE.QITPRDITEM,0), NVL(ITE.QITPRDADIT,0), NVL(ITE.VITPRDUNIT,0), ITE.AITPRDSEQU ";
	$sql  .= "  FROM SFCO.TBCREDOR CRE, ";
	$sql  .= "       SFCO.TBITEMPROCESSODISP ITE ";
	$sql  .= " WHERE CRE.CTPCRECODI = ITE.CTPCRECODI ";
	$sql  .= "   AND CRE.ACREDONUME = ITE.ACREDONUME ";
	$sql  .= "   AND ITE.DEXERCANOR = $Ano AND ITE.APRDISSEQU = $dadosQ[14] ";
	$sql  .= "   AND ITE.CORGORCODI = $Orgao ";
	$sql  .= " ORDER BY CRE.NCREDONOME ASC";   // Dados da Licitação
    $resuldado = $dbora->query($sql);
    return $resuldado;
}

//CAIO MELQUIADES - VARIAVEL QUE CONTROLA O TIPO DE ARQUIVO A SER EXPORTADO
$formatoExport = isset($_REQUEST ['FormatoExport']) ? $_REQUEST ['FormatoExport'] : 'csv';

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($_SESSION['DataIni'] != '' && $_POST['DataIni'] == ''){
	$Opcao =         $_SESSION['Opcao']; 
    $OrgaoUnidadeP = $_SESSION['OrgaoUnidadeP'];
    $OrgaoUnidadeP = explode("-", $OrgaoUnidadeP);
    $DataIni=        $_SESSION['DataIni'];
    $DataFim=	     $_SESSION['DataFim'];
	$ObjetoP =       $_SESSION['ObjetoP'];
}

$DataIniConv = DataInvertida($DataIni);          // Retorna aaaa-mm-dd
$DataFimConv = DataInvertida($DataFim);          // Retorna aaaa-mm-dd
$DataIniConv = str_replace("-", "", $DataIniConv); // Retorna aaaammdd
$DataFimConv = str_replace("-", "", $DataFimConv); // Retorna aaaammdd


# Email quando houver erro #
$Mail = "rossanalira@recife.pe.gov.br,alvarof@recife.pe.gov.br,abreu@recife.pe.gov.br";
$Assunto = "TESTE - Rotina de Exibição de Dispensa/Inexigibilidade";
$From = $GLOBALS["EMAIL_FROM"];


# Abre a Conexão com Oracle #
$dbora = ConexaoOracle();
# Resgata os dados das Dispensas e Inexigibilidades #
$sql .= "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ";                                      // Chaves - Ano, Tipo, Número da licitação
$sql .= "       ITE.CORGORCODI, ITE.CUNDORCODI, ";                                                      // Código do órgão e Unidade do Órgão
$sql .= "       to_char(LIC.DLICITHOML,'YYYY'), to_char(LIC.DLICITHOML,'YYYY/MM/DD'), LIC.XLICITOBJE, UNI.NUNDORNOME, '1', 'S' "; //Ano de Publicacao, Data de Publicação e Objeto
$sql .= "  FROM SFCO.TBLICITACAO LIC, SFCO.TBITEMLICITACAO ITE, SPOD.TBUNIDADEORCAMENT UNI ";
$sql .= " WHERE LIC.ALICITANOL = ITE.ALICITANOL ";
$sql .= "   AND LIC.CTPLICCODI = ITE.CTPLICCODI ";
$sql .= "   AND LIC.ALICITLICI = ITE.ALICITLICI ";
$sql .= "   AND ITE.AITLICITEM = '1' ";
$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') >= $DataIniConv ";
$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= $DataFimConv ";
$sql .= "   AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= 20060220 ";
$sql .= "   AND UNI.CORGORCODI = ITE.CORGORCODI ";
$sql .= "   AND UNI.CUNDORCODI = ITE.CUNDORCODI ";
$sql .= "   AND UNI.DEXERCANOR = ITE.DEXERCANOR ";
# Possíveis filtros da pesquisa
if ($Opcao) {
    if ($Opcao == 'D') {        // Dispensa
        $sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57,58) ";
    } elseif ($Opcao == 'I') { // Inexigibilidade
        $sql .= " AND LIC.CTPLICCODI IN(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
    }
} else {
    $sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,58,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
}
if ($ObjetoP) {
    $sql .= " AND LIC.XLICITOBJE LIKE '%" . strtoupper2($ObjetoP) . "%' ";
}
if ($Orgao) {
    $sql .= " AND ITE.CORGORCODI = $Orgao ";
    $sql .= " AND ITE.CUNDORCODI = $Unidade ";
}
$sql .= " UNION ";
$sql .= "SELECT LIC.DEXERCANOR, LIC.CTPLICCODI, LIC.APRDISSEQ2, ";                                     // Chaves - Ano, Tipo, Número da licitação
$sql .= "       LIC.CORGORCODI, LIC.CUNDORCODI, ";                                                     // Código do órgão e Unidade do Órgão
$sql .= "       to_char(LIC.DPRDISINIC,'YYYY'), to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), LIC.XPRDISOBJE, UNI.NUNDORNOME, '2' , LIC.APRDISSITDI";             // Data de Publicação, Ano de Publicacao e Objeto
$sql .= "  FROM SFCO.TBPROCESSODISPENSA LIC, SPOD.TBUNIDADEORCAMENT UNI ";
$sql .= " WHERE to_char(LIC.DPRDISINIC,'YYYYMMDD') >= $DataIniConv ";
$sql .= "   AND to_char(LIC.DPRDISINIC,'YYYYMMDD') <= $DataFimConv ";
$sql .= "   AND UNI.CORGORCODI = LIC.CORGORCODI ";
$sql .= "   AND UNI.CUNDORCODI = LIC.CUNDORCODI ";
$sql .= "   AND UNI.DEXERCANOR = LIC.DEXERCANOR ";
# Possíveis filtros da pesquisa
if ($Opcao) {
    if ($Opcao == 'D') {        // Dispensa
        $sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57,58) ";
    } elseif ($Opcao == 'I') { // Inexigibilidade
        $sql .= " AND LIC.CTPLICCODI IN(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
    }
} else {
    $sql .= " AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,58,60,61,62,63,64,66,67,71,72,73,74,75,76,90) ";
}
if ($ObjetoP) {
    $sql .= " AND LIC.XPRDISOBJE LIKE '%" . strtoupper2($ObjetoP) . "%' ";
}
if ($Orgao) {
    $sql .= " AND LIC.CORGORCODI = $Orgao ";
    $sql .= " AND LIC.CUNDORCODI = $Unidade ";
}
$sql .= " ORDER BY 6, 9, 1, 3 ";
 ////($sql);
//  kim
 //echo '<div style="display:none">'.$sql.'</div>';
$res = $dbora->query($sql);
if (PEAR::isError($res)) {
    $dbora->disconnect();
    ExibeErroBDRotinas("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    exit;
} else {
    $disp = array();
    $inex = array();
    while ($cols = $res->fetchRow()) {
        $cont++;
        $Dispensa = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 28, 29, 30, 39, 40, 41, 42, 43, 44, 45, 46, 51, 52, 56, 57,58);
        $Inexigibilidade = array(54, 55, 60, 61, 62, 63, 64, 66, 67, 71, 72, 73, 74, 75, 76, 90);
        if (in_array($cols[1], $Dispensa)) {
            $disp[$cont - 1] = "$cols[0]æ$cols[1]æ$cols[2]æ$cols[3]æ$cols[4]æ$cols[5]æ$cols[6]æ$cols[7]æ$cols[8]æDæ$cols[9]æ$cols[10]æ$cols[11]";
        } elseif (in_array($cols[1], $Inexigibilidade)) {
            $inex[$cont - 1] = "$cols[0]æ$cols[1]æ$cols[2]æ$cols[3]æ$cols[4]æ$cols[5]æ$cols[6]æ$cols[7]æ$cols[8]æIæ$cols[9]æ$cols[10]æ$cols[11]";
        }
    }
    $dados = array_merge($disp, $inex);
    
    $TipoDisIne = "";
}



/*if (count($dados) === 0) {
    //$tpl->CONSULTA = "Zero registros";
    $tpl->exibirMensagemFeedback("Nenhuma ocorrência foi encontrada", "1");
}*/

$ultimaModalidadePlotada = "";
$ultimaComissaoPlotada = "";
$ultimoGrupoPlotado = "";

$cabecalho = array('SITUACAO','DISPENSA-INEXIGIBILIDADE', 'UNIDADE GESTORA', 'NUMERO ANO','OBJETO', 'DATA PUBLICACAO','FUNDAMENTAÇÃO LEGAL', 'VALOR TOTAL', 'FORNECEDORES(ES) E VENCEDOR(ES)');
$linhas = array();

for ($i = 0; $i < count($dados); $i++) {
    $Linha = explode("æ", $dados[$i]);
    $TipoDisIne = $Linha[1];
    $NumeroAno = $Linha[2] . "/" . $Linha[0];
    $DataPublicacao = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
    $Objeto = $Linha[7];
    $OrgaoDesc = "";
    if (($Ano != substr($Linha[6], 0, 4)) or ( $DispOuInex != $Linha[9])) {
        $Ano = substr($Linha[6], 0, 4);
        $DispOuInex = $Linha[9];
        if ($DispOuInex == "D") {
            $TipoEscreve = "DISPENSA";
            $pegaleiDispensa = pegaleiDispensa($Linha[0], $Linha[1], $Linha[3], $dbora);
        } else {
            $TipoEscreve = "INEXIGIBILIDADE";
        }
    }
    $Orgao = "";
    $grupo = $TipoEscreve . " - " . $Ano;

    /*if ($ultimoGrupoPlotado != $grupo) {
        $tpl->GRUPO_DESCRICAO = $grupo;
        $ultimoGrupoPlotado = $grupo;
        $tpl->block("BLOCO_GRUPO");
    }*/

    $Orgao = $Linha[3];
    $Unidade = $Linha[4];
    $OrgaoDesc = $Linha[8];
    $status    = str_replace("RATIFICADA",'',$Linha[11]);
    $Fundamentacao = "";
    $valor =0;
    $vencedor = "";
    if(!empty($pegaleiDispensa)){
        $newDate = explode('/',$pegaleiDispensa[3]);
        $Fundamentacao = "Lei: ". $pegaleiDispensa[0].", Artigo: ". $pegaleiDispensa[1].", Inciso: ". $pegaleiDispensa[2].", Data da Lei: ".$newDate[2]."/".$newDate[1]."/".$newDate[0] ;
        $dadosComplementares = getvalorEVencedor($Linha[10],$Linha[0],$Linha[2],$Linha[3],$Linha[1],$dbora);
        if(!empty($dadosComplementares)){
            $cont = 0;
            while( $Linhak = $dadosComplementares->fetchRow()){
                $DispInexDeta[$cont] = "$Linhak[0]æ$Linhak[1]æ$Linhak[2]æ$Linhak[3]æ$Linhak[4]æ$Linhak[5]æ$Linhak[6]æ$Linhak[7]æ$Linhak[8]";
                $QuantItem      = floatval($Linhak[5]);
                $QuantAdit      = floatval($Linhak[6]);
                $ValorItemUnit  = floatval($Linhak[7]);
                $ValorTotal =0;
                $ValorTotal =  (floatval($QuantItem) + floatval($QuantAdit)) * floatval($ValorItemUnit);
                $valor +=  $ValorTotal;
                $valor = 'R$'.converte_valor($valor);
                $vencedor = $Linhak[4];
                $cont++;
            }
        }
    }
    //$cabecalho = array('DISPENSA-INEXIGIBILIDADE', 'UNIDADE GESTORA', 'NUMERO ANO','OBJETO', 'DATA PUBLICACAO');
    if(trim(strtolower($Linha[11])) !="cancelada" && trim(strtoupper($Linha[11]))!='CANCELADA' && trim(strtoupper($Linha[11])) != "CADASTRADA" ){
        if($formatoExport == "csv"){
            array_push($linhas, array(
                $status,
                $grupo, 
                utf8_decode($OrgaoDesc),
                $NumeroAno,
                utf8_decode(strtoupper2($Objeto)),
                $DataPublicacao,
                $Fundamentacao,
                $valor,
                $vencedor
            ));
        }else{
            array_push($linhas, array(
                $status,
                $grupo, 
                $OrgaoDesc,
                $NumeroAno,
                strtoupper2($Objeto),
                $DataPublicacao,
                $Fundamentacao,
                $valor,
                $vencedor
            ));
        }
    }
    /*$Url = "RotDispensaInexigibilidadeDetalhes.php?Numero=$Linha[2]&Ano=$Linha[0]&TipoDisIne=$TipoDisIne&";
    $Url .= "Select=$Linha[10]&Opcao=$Opcao&Objeto=" . urlencode($Objeto) . "&ObjetoP=" . urlencode($ObjetoP) . "&";
    $Url .= "Orgao=$Orgao&Unidade=$Unidade&";
    $Url .= "DataIni=$DataIni&DataFim=$DataFim&Botao=$Botao&+=$OrgaoUnidadeP";


    if ($ultimaModalidadePlotada != $OrgaoDesc) {
        $tpl->MODALIDADE_DESCRICAO = $OrgaoDesc;
        $tpl->block("BLOCO_MODALIDADE");
    }

    if ($ultimaModalidadePlotada != $OrgaoDesc) {
        $tpl->block("BLOCO_CABECALHO");
        $ultimaModalidadePlotada = $OrgaoDesc;
    }

    $tpl->NUMEROANO = "<a href=\"$Url\">$NumeroAno</a>";
    $tpl->DATA_PUBLICACAO = $DataPublicacao;
    $tpl->OBJETO = strtoupper2($Objeto);
    $tpl->block("BLOCO_VALORES");
    $tpl->block("BLOCO_CORPO");*/
}
// var_dump($linhas);
// die;
$nomeArquivo = 'pcr_portal_compras_dispensa_inexigibilidade';

        $export = null;

        switch($formatoExport){
            case 'xls':
                $nomeArquivo.= '.xls';
                $export = new ExportaXLS($nomeArquivo, $cabecalho, $linhas);
            break;
            case 'ods':
                $nomeArquivo.= '.ods';
                $export = new ExportaODS($nomeArquivo, $cabecalho, $linhas);
            break;
            case 'txt':
                    $nomeArquivo.= '.txt';
                    $export = new ExportaCSV($nomeArquivo, '|', $cabecalho, $linhas);
            break;  
            case 'csv':
            default:
                $nomeArquivo.= '.csv';
                $export = new ExportaCSV($nomeArquivo, ';', $cabecalho, $linhas);
        }
     echo "Download";
     $export->download();
//$tpl->show();
?>