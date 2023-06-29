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
 */
/**
 * ====================================================
 * CR234889
 * Autor: Eliakim Ramos de Souza
 * Tarefa: Uma despesa por dispensa em que houve vários credores, 
 * mas só aparece em nome de um com todo o valor atribuído ao conjunto.
 * Data: 19/06/2020
 * ===================================================
 * ====================================================
 * CR236348
 * Autor: Eliakim Ramos de Souza
 * Tarefa: Alterar a dispensa/inexigibilidade para exibir CREDOR INTERNACIONAL ao lado de CPF e CNPJ 
 * quando estes vierem zerados e o tipo de credor do SOFIN for igual a 47 e 3 exibir texto: 
 * CNPJ - credor internacional ou CPF - credor internacional antes das demais informações dos 
 * fornecedores vencedores.campo CTPCRECODI = 47 OU 3.
 * Data: 03/08/2020
 * ===================================================
 * * CR236751
 * Autor: Eliakim Ramos de Souza
 * Tarefa: [INTERNET] Dispensas e Inexigibilidades - Exibição de novas situações oriundas do SOFIN
 * Data: 11/08/2020
 * ===================================================
 * Alterado: João Madson
 * Data:     27/01/2021
 * Objetivo: CR #243182
 * -----------------------------------------------------------------------------
 * 
 */


if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao", 1);
}

$tpl = new TemplateAppPadrao("templates/RotExibeDispensaInexigibilidade.html");

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/oracle/licitacoes/RotDispensaInexigibilidadeDetalhes.php');
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
$ArrImun = array('IMUNIZAÇÃO', 'IMUNIZACAO', 'IMUNIZAÇAO', 'IMUNIZACÃO', 'IMUNIZACãO', 'IMUNIZAçãO', 'IMUNIZAçAO');

// 		$Botao                    	= $_POST['Botao'];
// 		$Opcao                    	= $_POST['Opcao'];
// 		$ObjetoP                   	= $_POST['ObjetoP'];
// 		$OrgaoUnidadeP              = $_POST['OrgaoUnidadeP'];
// 		$Orgao                    	= $_POST['Orgao'];
// 		$Unidade                    = $_POST['Unidade'];
// 		$DataIni                        = $_POST['DataIni'];
// 		$DataFim  		= $_POST['DataFim'];
// }
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
// $sqlK .= "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ITE.CORGORCODI, ITE.CUNDORCODI, to_char(LIC.DLICITHOML,'YYYY'),";                                     // Chaves - Ano, Tipo, Número da licitação
// $sqlK .= " to_char(LIC.DLICITHOML,'YYYY/MM/DD'), LIC.XLICITOBJE, UNI.NUNDORNOME, '1','S', 1 FROM SFCO.TBLICITACAO LIC, ";      // Código do órgão e Unidade do Órgão
// $sqlK .= " SFCO.TBITEMLICITACAO ITE, SPOD.TBUNIDADEORCAMENT UNI  WHERE LIC.ALICITANOL = ITE.ALICITANOL ";             // Data de Publicação, Ano de Publicacao e Objeto
// $sqlK .= " AND LIC.CTPLICCODI = ITE.CTPLICCODI AND LIC.ALICITLICI = ITE.ALICITLICI AND ITE.AITLICITEM = '1' ";
// $sqlK .= " AND to_char(LIC.DLICITHOML,'YYYYMMDD') >= 20200810 AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= 20201016 ";
// $sqlK .= " AND to_char(LIC.DLICITHOML,'YYYYMMDD') <= 20060220 AND UNI.CORGORCODI = ITE.CORGORCODI AND UNI.CUNDORCODI = ITE.CUNDORCODI ";
// $sqlK .= " AND UNI.DEXERCANOR = ITE.DEXERCANOR  AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,58,60,61,62,63,64,66,67,71,72,73,74,75,76,90)  ";
// $sqlK .= " UNION SELECT LIC.DEXERCANOR, LIC.CTPLICCODI, LIC.APRDISSEQ2, LIC.CORGORCODI, LIC.CUNDORCODI, to_char(LIC.DPRDISINIC,'YYYY'), ";
// $sqlK .= " to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), LIC.XPRDISOBJE, UNI.NUNDORNOME, '2', LIC.APRDISSITDI, LIC.VPRDISVALO  FROM SFCO.TBPROCESSODISPENSA LIC, ";
// $sqlK .= " SPOD.TBUNIDADEORCAMENT UNI  WHERE to_char(LIC.DPRDISINIC,'YYYYMMDD') >= 20200810 AND to_char(LIC.DPRDISINIC,'YYYYMMDD') <= 20201016 AND UNI.CORGORCODI = LIC.CORGORCODI AND UNI.CUNDORCODI = LIC.CUNDORCODI AND UNI.DEXERCANOR = LIC.DEXERCANOR  AND LIC.CTPLICCODI IN(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,54,55,56,57,58,60,61,62,63,64,66,67,71,72,73,74,75,76,90)  ORDER BY 6, 9, 1, 3 ";
// $res = $dbora->query($sqlK);
// if (PEAR::isError($res)) {
//     $dbora->disconnect();
//     ExibeErroBDRotinas("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlK");
//     exit;
// } else {
//     while ($cols = $res->fetchRow()) {
//            echo "<div>";
//             echo "Tipo dispensa =>".$cols[1];
//             echo "<br>";
//             echo "Numero Processo =>".$cols[2]."/".$cols[0];
//             echo "<br>";
//             echo "Objeto =>".$cols[7];
//             echo "<br>";
//             echo "Data Publicação =>".substr($cols[6], 8, 2) . "/" . substr($cols[6], 5, 2) . "/" . substr($cols[6], 0, 4);
//             echo "<br>";
//             echo "Status =>".$cols[10];
//             echo "<br>";
//             echo "Valor =>".$cols[11];
//             echo "</div><br>";
//     }
// }

// die;
# Resgata os dados das Dispensas e Inexigibilidades #
$sql .= "SELECT LIC.ALICITANOL, LIC.CTPLICCODI, LIC.ALICITLICI, ";                                      // Chaves - Ano, Tipo, Número da licitação
$sql .= "       ITE.CORGORCODI, ITE.CUNDORCODI, ";                                                      // Código do órgão e Unidade do Órgão
$sql .= "       to_char(LIC.DLICITHOML,'YYYY'), to_char(LIC.DLICITHOML,'YYYY/MM/DD'), LIC.XLICITOBJE, UNI.NUNDORNOME, '1','S', 1 "; //Ano de Publicacao, Data de Publicação e Objeto
$sql .= "   FROM SFCO.TBLICITACAO LIC, SFCO.TBITEMLICITACAO ITE, SPOD.TBUNIDADEORCAMENT UNI ";
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
$sql .= "       to_char(LIC.DPRDISINIC,'YYYY'), to_char(LIC.DPRDISINIC,'YYYY/MM/DD'), LIC.XPRDISOBJE, UNI.NUNDORNOME, '2', LIC.APRDISSITDI, LIC.VPRDISVALO ";             // Data de Publicação, Ano de Publicacao e Objeto
$sql .= "  FROM SFCO.TBPROCESSODISPENSA LIC, SPOD.TBUNIDADEORCAMENT UNI ";
$sql .= " WHERE to_char(LIC.DPRDISINIC,'YYYYMMDD') >= $DataIniConv ";
$sql .= "   AND to_char(LIC.DPRDISINIC,'YYYYMMDD') <= $DataFimConv ";
$sql .= "   AND UNI.CORGORCODI = LIC.CORGORCODI ";
$sql .= "   AND UNI.CUNDORCODI = LIC.CUNDORCODI ";
$sql .= "   AND UNI.DEXERCANOR = LIC.DEXERCANOR ";
$sql .= "   AND LIC.VPRDISVALO > 0.00 ";
$sql .= "   AND LIC.VPRDISVALO is not null ";
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
    if(in_array(strtoupper($ObjetoP), $ArrImun)){
        $sql .= " AND (LIC.XPRDISOBJE LIKE '%" . strtoupper2($ArrImun[0]) . "%' or LIC.XPRDISOBJE LIKE '%" . strtoupper2($ArrImun[1]) . "%' or LIC.XPRDISOBJE LIKE '%" . strtoupper2($ArrImun[2]) . "%' or LIC.XPRDISOBJE LIKE '%" . strtoupper2($ArrImun[3]) . "%' )";
    }else{
        $sql .= " AND LIC.XPRDISOBJE LIKE '%" . strtoupper2($ObjetoP) . "%' ";
    }
}
if ($Orgao) {
    $sql .= " AND LIC.CORGORCODI = $Orgao ";
    $sql .= " AND LIC.CUNDORCODI = $Unidade ";
}
$sql .= " ORDER BY 6, 9, 1, 3 ";
//  //($sql);
//  kim
 echo '<div style="display:none">'.$sql.'</div>';
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



if (count($dados) === 0) {
    //$tpl->CONSULTA = "Zero registros";
    $tpl->exibirMensagemFeedback("Nenhuma ocorrência foi encontrada", "1");
}

$ultimaModalidadePlotada = "";
$ultimaComissaoPlotada = "";
$ultimoGrupoPlotado = "";

for ($i = 0; $i < count($dados); $i++) {    
    $Linha = explode("æ", $dados[$i]);
    $TipoDisIne = $Linha[1];
    $NumeroAno = $Linha[2] . "/" . $Linha[0];
    $dataBanco = $Linha[6];
    $DataPublicacao = substr($Linha[6], 8, 2) . "/" . substr($Linha[6], 5, 2) . "/" . substr($Linha[6], 0, 4);
    $Objeto = $Linha[7];
    $OrgaoDesc = "";
    
    if (($Ano != substr($Linha[6], 0, 4)) or ( $DispOuInex != $Linha[9])) {
        $Ano = substr($Linha[6], 0, 4);
        $DispOuInex = $Linha[9];
        
        if ($DispOuInex == "D") {
            $TipoEscreve = "DISPENSA";
        } else {
            $TipoEscreve = "INEXIGIBILIDADE";
        }
    }

    $Orgao = "";
    $grupo = $TipoEscreve . " - " . $Ano;

    if ($ultimoGrupoPlotado != $grupo) {
        $tpl->GRUPO_DESCRICAO = $grupo;
        $ultimoGrupoPlotado = $grupo;
        $tpl->block("BLOCO_GRUPO");
    }

    $Orgao = $Linha[3];
    $Unidade = $Linha[4];
    $OrgaoDesc = $Linha[8];
	
    $Url = "RotDispensaInexigibilidadeDetalhes.php?Numero=$Linha[2]&Ano=$Linha[0]&TipoDisIne=$TipoDisIne&";
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
    if(trim(strtolower($Linha[11])) =="cancelada" || trim(strtoupper($Linha[11]))=='CANCELADA' || trim(strtoupper($Linha[11])) == "CADASTRADA"){
        $tpl->CANCELADA = 'style="display:none"';
    }//else if(empty($Linha[11]) && strtotime($dataBanco) > strtotime("2020-08-10")){
       // $tpl->CANCELADA = 'style="display:none"';
    //}//osmar
  //  else if(empty($Linha[11])){
     //   $tpl->CANCELADA = 'style="display:none"';
  //  }
    else if(empty($Linha[12]) || $Linha[12] == "0.00" || $Linha[12] == "0,00" || $Linha[12] == "0"){
        $tpl->CANCELADA = 'style="display:none"';
    }else{
        $tpl->CANCELADA ="";
    }
   
    // $marreta = "";
    // if($NumeroAno=='13/2020' && $DataPublicacao == "18/03/2020" ){
    //     $marreta =' <strong>REVOGADA PARCIALMENTE</strong> ';
    // }
    $parcial = explode(" ",$Linha[11]);
    if( trim(strtoupper($Linha[11]))=='REVOGADA' || trim(strtoupper($Linha[11]))=='REVOGADA TOTAL' || trim(strtoupper($Linha[11]))=='ANULADA'){
        $tpl->OBJETO = "<s>".strtoupper2($Objeto)."</s><br><strong>".$Linha[11]."</strong>";
    }else if(trim(strtoupper($Linha[11]))=='REVOGADA PARCIAL' || trim(strtoupper($parcial[2])) == "PARCIAL"){
        $tpl->OBJETO = strtoupper2($Objeto)."<br><strong>".$Linha[11]."</strong>";
    }else if(trim(strtoupper($Linha[11]))=='RETIFICAÇÃO' || trim(strtoupper($Linha[11]))=='RETIFICADA'){
        $tpl->OBJETO = strtoupper2($Objeto)."<br><strong>".$Linha[11]."</strong>";
    }else if(trim(strtoupper($Linha[11]))=='SUSPENSA TCE' || trim(strtoupper($Linha[11]))=='SUSPENSA ADM' || trim(strtoupper($Linha[11]))=='SUSPENSA P. JUDICIÁRIO'){
        $tpl->OBJETO = strtoupper2($Objeto)."<br><strong>".$Linha[11]."</strong>";
    }else if(trim(strtoupper($Linha[11]))=='RATIFICADA'){
        $tpl->OBJETO = strtoupper2($Objeto)."<br><strong>".$Linha[11]."</strong>";
    }else{
        $tpl->OBJETO = strtoupper2($Objeto);
    }
    $tpl->block("BLOCO_VALORES");
    $tpl->block("BLOCO_CORPO");
}
$tpl->show();
?>