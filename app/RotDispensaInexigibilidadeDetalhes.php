<?php 
session_start();
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
 * CR236213
 * Autor: Eliakim Ramos de Souza
 * Tarefa: Dispensas/Inexigibilidades - Acréscimo texto DISPENSA 13/2020.
 * Data: 27/07/2020
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
 * CR236751
 * Autor: Eliakim Ramos de Souza
 * Tarefa: [INTERNET] Dispensas e Inexigibilidades - Exibição de novas situações oriundas do SOFIN
 * Data: 11/08/2020
 * ===================================================
 * Auto: Lucas Vicente
 * Tarefa: CR 275808
 * Data: 01/12/2022
 */


if (!@require_once dirname(__FILE__) . "/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao", 1);
}

$tpl = new TemplateAppPadrao("templates/RotDispensaInexigibilidadeDetalhes.html");


# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Numero                   = $_GET['Numero'];
	$Ano                      = $_GET['Ano'];
	$TipoDisIne               = $_GET['TipoDisIne'];
	$Select                   = $_GET['Select'];
	$Opcao                    = $_GET['Opcao'];
	$Botao                    = $_GET['Botao'];
	$ObjetoP                  = urldecode($_GET['ObjetoP']);
	$Objeto                   = urldecode($_GET['Objeto']);
	$Orgao                    = $_GET['Orgao'];
	$Unidade                  = $_GET['Unidade'];
	$DataIni  								= $_GET['DataIni'];
	$DataFim  								= $_GET['DataFim'];
	$OrgaoUnidadeP            = $_GET['OrgaoUnidadeP'];
	//($Numero);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Dados da licitação
# Abre a Conexão com Oracle #
$dbora = ConexaoOracle();
# Resgata os dados das Dispensas e Inexigibilidades #
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
if( db::isError($res) ){
	$dbora->disconnect();
	ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	exit;
}else{
	$Linha = $res->fetchRow();
	$TipoDisIne     = $Linha[1];
	$NumeroAno      = $Linha[2]."/".$Linha[0];
	$Exercicio      = $Linha[3];
	$Orgao          = (!empty($Linha[4]))?$Linha[4]:$Orgao;
	$Unidade        = (!empty($Linha[5]))?$Linha[5]:$Unidade;
	$DataPublicacao = substr($Linha[6],8,2) ."/". substr($Linha[6],5,2) ."/". substr($Linha[6],0,4);
	$DataVigencia   = substr($Linha[7],8,2) ."/". substr($Linha[7],5,2) ."/". substr($Linha[7],0,4);
	$ObjetoDetalhes = $Linha[8];
	$Lei            = $Linha[9];
	$Artigo         = $Linha[10];
	$Inciso         = $Linha[11];
	$DataLei        = substr($Linha[12],8,2) ."/". substr($Linha[12],5,2) ."/". substr($Linha[12],0,4);
	$valortabelaprocesso = $ValorTotal     = $Linha[13];
	$NumeroSequ     = (!empty($Linha[14]))?$Linha[14]:'null';
	$StatusDoProcesso =$Linha[15];
	# Define a descrição do tipo
	$Dispensa = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,28,29,30,39,40,41,42,43,44,45,46,51,52,56,57,58);
	$Inexigibilidade = array(54,55,60,61,62,63,64,66,67,71,72,73,74,75,76,90);
	if(in_array($TipoDisIne,$Dispensa)){
		$TipoDesc = "DISPENSA";
	}elseif(in_array($TipoDisIne,$Inexigibilidade)){
		$TipoDesc = "INEXIGIBILIDADE";
	}
}
$dbora->disconnect();
# Dados do órgão
//echo '<div style="display:none">'.$ValorTotal.'</div>';
$db      = Conexao(); // Conexão com Postgree
$sqlorg  = "SELECT CUNIDOORGA, CUNIDOCODI, EUNIDODESC ";
$sqlorg .= "  FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
$sqlorg .= " WHERE CUNIDOORGA = $Orgao AND CUNIDOCODI = $Unidade ";
//$sqlorg .= "   AND TUNIDOEXER = 2006 ";
$resorg  = $db->query($sqlorg);
if( db::isError($resorg) ){
	$db->disconnect();
	ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlorg");
	exit;
}else{
	$LinhaOrg  = $resorg->fetchRow();
	$OrgaoDesc = $LinhaOrg[2];
}
$db->disconnect;


$tpl->TIPO = $TipoDesc;
$tpl->ORGAO = $OrgaoDesc;
$tpl->NUMERO= $NumeroAno;

$marretaV = "";
// if($NumeroAno=='13/2020' && $DataPublicacao == "18/03/2020"){
// 	$marreta =' <strong>REVOGADA PARCIALMENTE</strong> ';
// 	$marretaV =' - <strong>REVOGADA PARCIALMENTE</strong>'; 
// }
$parcial = explode(" ",$StatusDoProcesso);
if(trim(strtoupper($StatusDoProcesso))=='CADASTRADA' || trim(strtoupper($StatusDoProcesso))=='CANCELADA' || trim(strtoupper($StatusDoProcesso))=='REVOGADA' || trim(strtoupper($StatusDoProcesso))=='REVOGADA TOTAL'){
	$tpl->OBJETO= "<s>".strtoupper($ObjetoDetalhes)."</s><br><strong>".$StatusDoProcesso." -  RESCISÃO CONTRATUAL</strong>";
	$tpl->CANCELADO ='display:none;';
}else if(trim(strtoupper($StatusDoProcesso))=='RETIFICAÇÃO' || trim(strtoupper($StatusDoProcesso))=='RETIFICADA'){
	$tpl->OBJETO= strtoupper($ObjetoDetalhes)."<br><strong>".$StatusDoProcesso."</strong>";
	$tpl->CANCELADO ='';
}else if(trim(strtoupper($StatusDoProcesso)) =="REVOGADA PARCIAL" || trim(strtoupper($StatusDoProcesso))=="REVOGADA PARCIAL" || trim(strtoupper($parcial[2])) == "PARCIAL"){
	$tpl->OBJETO= strtoupper($ObjetoDetalhes)."<br><strong>".$StatusDoProcesso."</strong>";
	$tpl->CANCELADO ='';
}else if(trim(strtoupper($StatusDoProcesso))=='SUSPENSA TCE' || trim(strtoupper($StatusDoProcesso))=='SUSPENSA ADM' || trim(strtoupper($StatusDoProcesso))=='SUSPENSA P. JUDICIÁRIO'){
	$tpl->OBJETO= strtoupper($ObjetoDetalhes)."<br><strong>".$StatusDoProcesso."</strong>";
	$tpl->CANCELADO ='';
}else{
	$tpl->OBJETO= strtoupper($ObjetoDetalhes);
	$tpl->CANCELADO ='';
}
$tpl->DATAPUBLICACAO= $DataPublicacao;
$tpl->DATAVIGENCIA= $DataVigencia;
$tpl->FUNDAMENTACAO= "Lei: ". $Lei.", Artigo: ". $Artigo.", Inciso: ". $Inciso.", Data da Lei: ". $DataLei;


# Resgata os fornecedores vencedores #
if ($Select == 1){
	$sql   = "SELECT  CRE.CTPCRECODI, CRE.ACREDONUME, CRE.ACREDOCGCC, CRE.ACREDOCPFF, CRE.NCREDONOME, ";
	$sql  .= "       NVL(ITE.QITLICITEM,0), NVL(ITE.QITLICADIT,0), NVL(ITE.VITLICUNIT,0), ITE.AITLICITEM ";
	$sql  .= "  FROM SFCO.TBCREDOR CRE, ";
	$sql  .= "       SFCO.TBITEMLICITACAO ITE ";
	$sql  .= " WHERE CRE.CTPCRECODI = ITE.CTPCRECODI ";
	$sql  .= "   AND CRE.ACREDONUME = ITE.ACREDONUME ";
	$sql  .= "   AND ITE.ALICITANOL = $Ano AND ITE.ALICITLICI = $Numero AND ITE.CTPLICCODI = $TipoDisIne ";
	$sql  .= " ORDER BY CRE.NCREDONOME ASC";
}elseif ($Select == 2) {
	$sql   = "SELECT  CRE.CTPCRECODI, CRE.ACREDONUME, CRE.ACREDOCGCC, CRE.ACREDOCPFF, CRE.NCREDONOME, ";
	$sql  .= "       NVL(ITE.QITPRDITEM,0), NVL(ITE.QITPRDADIT,0), NVL(ITE.VITPRDUNIT,0), ITE.AITPRDSEQU ";
	$sql  .= "  FROM SFCO.TBCREDOR CRE, ";
	$sql  .= "       SFCO.TBITEMPROCESSODISP ITE ";
	$sql  .= " WHERE CRE.CTPCRECODI = ITE.CTPCRECODI ";
	$sql  .= "   AND CRE.ACREDONUME = ITE.ACREDONUME ";
	$sql  .= "   AND ITE.DEXERCANOR = $Ano AND ITE.APRDISSEQU = $NumeroSequ ";
	$sql  .= "   AND ITE.CORGORCODI = $Orgao ";
	$sql  .= " ORDER BY CRE.NCREDONOME ASC";
}

// kim
$dbora = ConexaoOracle("us_portal", "portal#13", "dbemprel");
$res  = $dbora->query($sql);
if( db::isError($res) ){
	$dbora->disconnect();
	ExibeErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	exit;
}else{
	$cont = 0;
	while( $Linha = $res->fetchRow() ){
		$DispInexDeta[$cont] = "$Linha[0]æ$Linha[1]æ$Linha[2]æ$Linha[3]æ$Linha[4]æ$Linha[5]æ$Linha[6]æ$Linha[7]æ$Linha[8]";
		$QuantItem      = floatval($Linha[5]);
		$QuantAdit      = floatval($Linha[6]);
		$ValorItemUnit  = floatval($Linha[7]);
		$ValorTotal =0;
		$ValorTotal =  (floatval($QuantItem) + floatval($QuantAdit)) * floatval($ValorItemUnit);
		$ValorTotalDispInex +=  $ValorTotal;
		$cont++;
	}
//Kim cr#231739
if(trim(strtoupper($StatusDoProcesso))!='CANCELADA' || trim(strtoupper($StatusDoProcesso))!='REVOGADA'){
  $tpl->VALORTOTAL= converte_valor(sprintf("%01.2f",$ValorTotalDispInex));
}
//Kim cr#231739
$valorFornecedor = "";
$testearray = array();
for($i=0;$i<count($DispInexDeta);$i++ ){
	$Linha = explode("æ",$DispInexDeta[$i]);
	$CredorCod      = $Linha[0];
	$CredorNume     = $Linha[1];
	$CNPJ           = $Linha[2];
	$CPF            = $Linha[3];
	$FornecedorNome = $Linha[4];
	$QuantItem      = $Linha[5];
	$QuantAdit      = $Linha[6];
	$codItem      = $Linha[8];
	$ValorItemUnit  = $Linha[7];
	$ValorItem      = ($QuantItem + $QuantAdit) * $ValorItemUnit;

//Kim cr#231739
    if (  ( ($CredorCod == $CredorCodVerif) and ($CredorNume == $CredorNumeVerif) ) or ( ($CredorCodVerif == null) and ($CredorNumeVerif == null) )  ){
		$ValorItemTotal = $ValorItemTotal + $ValorItem;
	}else{
		$CredorCodVerif  = $CredorCod;
		$CredorNumeVerif = $CredorNume;
		if ($CPF) {
			$valorFornecedor.= "CPF: $CPF - \n";
		}elseif($CNPJ){
		$valorFornecedor.= "CNPJ: $CNPJ - \n";
			}
			$valorFornecedor.= "$FornecedorNome, \n";
			$valorFornecedor.= "Valor: R$ ".converte_valor(sprintf("%01.2f",$ValorItemTotal))."\n";
			$ValorItemTotal = 0;
	   }
	   //Kim cr#231739
	   $semDocumento =0;
	   if ($ValorItem != 0) {
				if ($CPF) {
						$testearray[$CPF][] = array('nome'=>$FornecedorNome,'documento'=>$CPF,'valoritem'=>$ValorItem);
				}elseif($CNPJ){
						$testearray[$CNPJ][] = array('nome'=>$FornecedorNome,'documento'=>$CNPJ,'valoritem'=>$ValorItem);
				}elseif($CredorCod == "47" || $CredorCod ="3"){
					$testearray[$semDocumento][] = array('nome'=>$FornecedorNome,'documento'=>"Fornecedor Internacional",'valoritem'=>$ValorItem);
					$semDocumento++;
				}
	   }
	   
	   if ($ValorTotalDispInex == 0) {
			if ($CPF) {
						$testearray[$CPF][] = array('nome'=>$FornecedorNome,'documento'=>$CPF,'valoritem'=>$ValorItem);
			}elseif($CNPJ){
						$testearray[$CNPJ][] = array('nome'=>$FornecedorNome,'documento'=>$CNPJ,'valoritem'=>$ValorItem);
				}
	   }
   }
//    //($$codItem );
//    echo "<-Codigo item <br>";
//    //($ValorItemUnit);
//    echo "<- valor initario do item<br>";
//    //($QuantItem);
//    echo "<- qtd item <br>";
//    //($QuantAdit);
//    echo "<- qtd item aditivo <br>";
//    //($ValorItem);
//    echo "<- valor geral do item <br>";
//    //($valortabelaprocesso);
//    echo "<- valor da tabela de processo <br>";
//    die;

   foreach($testearray as $tf){
	$docuemento=$nomeFornecedor ="";
   	$ValorItem=0;  
	for($i=0;$i<count($tf);$i++){
		
			if (strlen($tf[$i]['documento']) <=11) {
				$docuemento= "CPF: ".FormataCPF($tf[$i]['documento'])." - \n";
			}elseif(strlen($tf[$i]['documento']) >11 && strlen($tf[$i]['documento']) <= 14){
				$docuemento= "CNPJ: ".FormataCNPJ($tf[$i]['documento'])." - \n";
			}else{
				$docuemento= $tf[$i]['documento']." - \n";
			}
			$nomeFornecedor = $tf[$i]['nome'];
			$ValorItem += $tf[$i]['valoritem'];

	}
	   $valorFornecedor.=$docuemento.$nomeFornecedor."-"."Valor: R$ ".converte_valor(sprintf("%01.2f",$ValorItem)).$marretaV."<br>";
   }
}
$dbora->disconnect();
//Kim cr#231739
if(trim(strtoupper($StatusDoProcesso))!='CANCELADA' || trim(strtoupper($StatusDoProcesso))!='REVOGADA'){
	$tpl->FORNECEDORES= $valorFornecedor;
}

$valorfornecedor = $valorFornecedor;
$valor_total = converte_valor(sprintf("%01.2f",$ValorTotalDispInex));
$lei_instancia = "Lei: ". $Lei.", Artigo: ". $Artigo.", Inciso: ". $Inciso.", Data da Lei: ". $DataLei;
$_SESSION['fornecedor'];
$_SESSION['valortotal'];
$_SESSION['lei'];
$tpl->show();