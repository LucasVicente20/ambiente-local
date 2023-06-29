<?php
require_once("../compras/funcoesCompras.php");
require_once('../funcoes.php');
//include($GLOBALS["CAMINHO_PDF"].'fpdi.php');

$caminhoFPDI = str_replace("phpmailer", "fpdi", $GLOBALS["CAMINHO_EMAIL"]);

//print_r(scandir($caminhoFPDI));

$caminhoFPDI .= "fpdi.php";

require_once($caminhoFPDI);

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

# Executa o controle de segurança#
session_start();
//Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadTCEXMLEnviar.php' );

if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Mensagem			= urldecode($_GET['Mensagem']);
	$Mens				= $_GET['Mens'];
	$Tipo				= $_GET['Tipo'];
	$Critica			= $_GET['Critica'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();
/*
$licitacoes = array();

$array1 = array("teste" => 1, "teste2" => 1);
$array2 = array("teste" => 1, "teste2" => 2);
$array3 = array("teste" => 1, "teste2" => 1);
$array4 = array($array1, $array2, $array3);

var_dump($array4);
$array4 = array_unique($array4);
var_dump($array1 == $array2);
var_dump($array1 == $array3);
var_dump($array4); die;

/*
var_dump(is_dir($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos/teste"));
var_dump(is_dir($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos")); die;
*/

$arrayObras = resultValorUnico(executarSQL($db, "SELECT epargesubo FROM sfpc.tbparametrosgerais"));
$arrayObras = str_replace(" ", "", $arrayObras);
$arrayObras = explode(",",$arrayObras);

/*---------------------------------------------------TESTE---------------------------------------------------------*/

//imprimindo conteúdo das pastas no diretório upload GLOBALS["CAMINHO_UPLOADS"]

// echo "<br><br>compras<br>";
// print_r(scandir($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/"));
// echo "<br><br>institucional<br>";
// print_r(scandir($GLOBALS["CAMINHO_UPLOADS"]."institucional/"));
// echo "<br><br>registro de preço<br>";
// print_r(scandir($GLOBALS["CAMINHO_UPLOADS"]."registropreco/"));

// echo "<br><br><br>"; die;
/*

echo "Licitação<br>";
$teste = readfile($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE1_0001_2013_2_39_1_1");
//var_dump($teste);
rename($teste, "teste.pdf");
resetArquivoAcesso();

$ArquivoNomeNoServidor = "licitacoes/ATASFASE1_0001_2013_2_39_1_1";
$NomeRealDoArquivo = "html5-web.pdf";

addArquivoAcesso($ArquivoNomeNoServidor); 
//addArquivoAcesso($NomeRealDoArquivo);

$sqlPdfAtas = "select distinct eatasfnome 
			   from 	sfpc.tbatasfase 
			   where 	eatasfnome ilike '%.pdf' and 
						cfasescodi = and clicpoproc = and alicpoanop = and 
						cgrempcodi = and ccomlicodi = and corglicodi = 	
			   order by eatasfnome asc";

$sqlPdfEdital = "select distinct edoclinome
		   		 from 	sfpc.tbdocumentolicitacao 
		   		 where 	eatasfnome ilike '%.pdf' and 
						clicpoproc = and alicpoanop = and 
						cgrempcodi = and ccomlicodi = and corglicodi = 	
		   		 order by edoclinome asc";


?>
<!DOCTYPE html>
<html>
	<body>
		<a href="../carregarArquivo.php?arq=<?=urlencode($ArquivoNomeNoServidor)?>&arq_nome=<?=urlencode($NomeRealDoArquivo)?>"><?="teste"?></a>
	</body>
</html>
<?php 
var_dump($_SESSION["arquivo"]);

/*$teste = "../carregarArquivo.php?arq".urlencode($ArquivoNomeNoServidor)."&arq_nome=".urlencode($NomeRealDoArquivo);
var_dump($teste);
die('aqui');
*/

//função para concatenar os arquivos PDF
function concat($arquivos, $outputpath){
	$fpdi = new FPDI;

	foreach($arquivos as $arq){
		$count = $fpdi->setSourceFile($arq);

		for($i=1; $i<=$count; $i++){
			$template 	= $fpdi->importPage($i);
			$size 		= $fpdi->getTemplateSize($template);
				
			$fpdi->AddPage('P', array($size['w'], $size['h']));
			$fpdi->useTemplate($template);
		}
	}
	$fpdi->Output($outputpath, 'D');//'F' para salvar; 'I' para exibir no browser; 'D' para download
	die;
}

//imprimindo/salvando/gravando o pdf resultado da concatenação
// try{
// 	$arqs = array($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE1_0001_2013_2_39_1_1", $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE1_0001_2013_2_39_1_1");

// 	if(!is_dir($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos")){
// 		mkdir($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos", 0777, true);
// 	}
	
// 	$arqOut = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/teste_bks3.pdf";
// 	concat($arqs, $arqOut);
// }
// catch(Exception $e){
// 	var_dump($e);
// }
//die;/**/
/*
//criando arquivo xml - resultado a ser exportado
$oXMLout = new XMLWriter();
$oXMLout->openMemory();
//$oXMLout->openURI($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/teste.xml");
$oXMLout->setIndent(true);

$oXMLout->startDocument('1.0', 'UTF-8');

$oXMLout->startElement("lote");//elto 1

$oXMLout->startElement("item1");//elto 1.1
$oXMLout->writeElement("quantity", 8);
$oXMLout->writeElement("price_per_quantity", 110);
$oXMLout->startElement("vendedor");//elto 1.1.1
$oXMLout->writeElement("nome", 'Joao');
$oXMLout->endElement();//fim elto 1.1.1
$oXMLout->endElement();//fim elto 1.1

$oXMLout->startElement("item2");//elto 1.2
$oXMLout->writeElement("quantity", 81);
$oXMLout->writeElement("price_per_quantity", 210);
$oXMLout->endElement();//fim elto 1.2
$oXMLout->endElement();//fim elto 1

$oXMLout->endDocument();
$oXMLout->flush();

print $oXMLout->outputMemory();
*/
/*---------------------------------------------------TESTE---------------------------------------------------------*/

//die;
/*VERIFICANDO TODOS OS PROCESSOS LICITATÓRIOS QUE ESTÃO NAS FASES PERMITIDAS*/
/*$sqlLic = "SELECT DISTINCT
				FASE.CFASESCODI ,LIP.CLICPOPROC ,LIP.ALICPOANOP ,LIP.CGREMPCODI 
				,LIP.CCOMLICODI ,LIP.CORGLICODI ,LIP.CMODLICODI ,LIP.CLICPOCODL
				,LIP.ALICPOANOL ,LIP.FLICPOREGP ,LIP.TLICPODHAB ,FASE.TFASELDATA, FASE.TFASELULAT
				,ORG.EORGLIDESC 
				,(SELECT CCENPOCORG FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = LIP.CORGLICODI LIMIT 1) AS CODORG
				,(SELECT CCENPOUNID FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = LIP.CORGLICODI LIMIT 1) AS CODUNID
			FROM
				SFPC.TBLICITACAOPORTAL LIP
				JOIN SFPC.TBFASELICITACAO FASE ON (FASE.CLICPOPROC = LIP.CLICPOPROC AND FASE.ALICPOANOP = LIP.ALICPOANOP 
								   AND FASE.CGREMPCODI = LIP.CGREMPCODI AND FASE.CCOMLICODI = LIP.CCOMLICODI 
								   AND FASE.CORGLICODI = LIP.CORGLICODI)
				JOIN SFPC.TBORGAOLICITANTE ORG ON ORG.CORGLICODI = LIP.CORGLICODI
			WHERE
				FASE.CFASESCODI IN (2,11,12,13)
				AND LIP.CMODLICODI IN (1,2,3,4,5,14)
				AND FASE.TFASELULAT IN (SELECT 	MAX(TFASELULAT) 
										FROM 	SFPC.TBFASELICITACAO 
										WHERE 	CLICPOPROC = LIP.CLICPOPROC AND ALICPOANOP = LIP.ALICPOANOP 
												AND CGREMPCODI = LIP.CGREMPCODI AND CCOMLICODI = LIP.CCOMLICODI AND CORGLICODI = LIP.CORGLICODI)
			ORDER BY
				LIP.ALICPOANOP DESC ,LIP.CLICPOPROC DESC";

$resultLic = $db->query($sqlLic);
*/
// echo '<pre>';
// var_dump($resultLic->fetchRow());
// die();
/*if (PEAR::isError($resultLic)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLic");
}
else{
	if($resultLic->numRows() < 1){
		//Exibir mensagem de erro
	}
	else{
		while($LinhaLic = $resultLic->fetchRow()){
			
			$clicpoproc = $LinhaLic[1];
			$alicpoanop = $LinhaLic[2];
			$cgrempcodi = $LinhaLic[3];
			$ccomlicodi = $LinhaLic[4];
			$corglicodi = $LinhaLic[5];
			
			$flag = false;
			
			/*VERIFICAÇÃO DOS ITENS DE CADA LICITAÇÃO*/
	/*		$sqlItem = "SELECT DISTINCT CMATEPSEQU, CSERVPSEQU
						FROM 	SFPC.TBITEMLICITACAOPORTAL 
						WHERE 	(CMATEPSEQU IS NOT NULL OR CSERVPSEQU IS NOT NULL) 
								AND CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
								AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi";
			
			$resultItem = $db->query($sqlItem);

			if($resultItem->numRows() >= 1){
				while($LinhaItem = $resultItem->fetchRow()){
					if($LinhaItem[0] != null){
						$gSubSql = "SELECT 	DISTINCT
										GRU.CGRUSEELE1 ,GRU.CGRUSEELE2 ,GRU.CGRUSEELE3 ,GRU.CGRUSEELE4 ,GRU.CGRUSESUBE
									FROM 	
										SFPC.TBGRUPOSUBELEMENTODESPESA GRU
										JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.CGRUMSCODI = GRU.CGRUMSCODI
										JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU
									WHERE	
										MAT.CMATEPSEQU = $LinhaItem[0]
									LIMIT 	1";
					}
					else{
						$gSubSql = "SELECT 	DISTINCT
										GRU.CGRUSEELE1 ,GRU.CGRUSEELE2 ,GRU.CGRUSEELE3 ,GRU.CGRUSEELE4 ,GRU.CGRUSESUBE
									FROM 	
										SFPC.TBGRUPOSUBELEMENTODESPESA GRU
										JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CGRUMSCODI = GRU.CGRUMSCODI
									WHERE	SERV.CSERVPSEQU = $LinhaItem[1]
									LIMIT 	1";
					}
					
					$gSubResult = $db->query($gSubSql);
					
					$gSub = $gSubResult->fetchRow();
					$gSub = $gSub[0].".".$gSub[1].".".$gSub[2].".".$gSub[3].".".$gSub[4];
					
					if(in_array($gSub, $arrayObras)){
						$flag = true;
						break;
					}	
				}//fim do while de itens

				if(!$flag){
					$codOrg  = ($LinhaLic[14] > 9)?$LinhaLic[14]:"0".$LinhaLic[14];
					$codUnid = ($LinhaLic[15] > 9)?$LinhaLic[15]:"0".$LinhaLic[15];
					$nomeOrg = RetiraAcentos($LinhaLic[13]);
					
					$LinhaLic['nomeArquivo'] = "PL_".$codOrg."".$codUnid."_".$nomeOrg."_".date("Y")."".date("m").".xml";
					$licitacoes[] = $LinhaLic;
				}
			}
		}//fim do while de licitações
	}
}//fim do else que verifica se há licitações válidas

$array_CodConsumo = array("3.3.90.30", "4.4.90.52", "4.4.90.30");
$array_CodServico = array("3.3.90.37", "3.3.90.39", "4.4.90.35", "4.4.90.39","3.3.90.33", "4.4.90.33");

foreach($licitacoes as $licitacao){
	$clicpoproc = $licitacao[1];
	$alicpoanop = $licitacao[2];
	$cgrempcodi = $licitacao[3];
	$ccomlicodi = $licitacao[4];
	$corglicodi = $licitacao[5];
	
	$flagSCC = false;
	
	switch($licitacao[0]){//SITUAÇÃO DA LICITAÇÃO
		case 2:
			$situacaoLic = 7;
			break;
		case 11:
			$situacaoLic = 4;
			break;
		case 12:
			$situacaoLic = 3;
			break;
		case 13:
			$situacaoLic = 9;
			break;
	}
	
	switch($licitacao[6]){//MODALIDADE DA LICITAÇÃO
		case 1:
			$modalidade = "05";
			break;
		case 2:
			$modalidade = "04";
			break;
		case 3:
			$modalidade = "01";
			break;
		case 4:
			$modalidade = "07";
			break;
		case 5:
			$modalidade = "09";
			break;
		case 14:
			$modalidade = "08";
			break;
	}
		
	if($licitacao[0] == 2 || $licitacao[0] == 11 || $licitacao[0] == 12){//ESTAGIO DA LICITACAO
		$estagioLic = 3;
	}
	elseif($licitacao[0] == 13){//ESTAGIO DA LICITACAO
		$estagioLic = 7;
	}
	
	if($licitacao[6] == 5 || $licitacao[6] == 14){//INVERSAO DE FASES
		$inversaoFases = "01";
	}
	else{//INVERSAO DE FASES
		$inversaoFases = "NULO";
	}
	
	/*ANTIGO
	$numProcAno  = ($licitacao[4]>=10)?$licitacao[4]:"0".$licitacao[4];
	$numProcAno .= ($licitacao[1]>99)?$licitacao[1]:(($licitacao[1]>=10 && $licitacao[1]<=99)?"0".$licitacao[1]:"00".$licitacao[1]);
	*/
	/*NOVO*/
/*	if($licitacao[1]>0 and $licitacao[1]<=9){//NUMERO DO PROCESSO NO ANO
		$numProcAno = "0000".$licitacao[1];
	}
	elseif($licitacao[1]>9 and $licitacao[1]<=99){//NUMERO DO PROCESSO NO ANO
		$numProcAno = "000".$licitacao[1];
	}
	elseif($licitacao[1]>99 and $licitacao[1]<=999){//NUMERO DO PROCESSO NO ANO
		$numProcAno = "00".$licitacao[1];
	}
	elseif($licitacao[1]>999 and $licitacao[1]<=9999){//NUMERO DO PROCESSO NO ANO
		$numProcAno = "0".$licitacao[1];
	}
	else{//NUMERO DO PROCESSO NO ANO
		$numProcAno = $licitacao[1];
	}
	
	$anoProcLic  = $licitacao[2];//ANO DO PROCESSO LICITATORIO
	
	/*ANTIGO
	$numProcAdm = $numProcAno;
	$anoProcAdm = $licitacao[2];
	*/
	/*NOVO*/
/*	$numProcAdm = "NULO";//NUMERO DO PROCESSO ADMINISTRATIVO NO ANO
	$anoProcAdm = "NULO";//ANO DO PROCESSO ADMINISTRATIVO 
	
	$numModalidade  = ($licitacao[4]>=10)?$licitacao[4]:"0".$licitacao[4];//NUMERO DA MODALIDADE
	$numModalidade .= ($licitacao[7]>99)?$licitacao[7]:(($licitacao[7]>=10 && $licitacao[7]<=99)?"0".$licitacao[7]:"00".$licitacao[7]);//NUMERO DA MODALIDADE
	$anoModalidade  = $licitacao[8];//ANO DA MODALIDADE DO PROCESSO LICITATORIO
	
	$portaria 		= $licitacao[4];//PORTARIA DE DESIGNAÇÃO
	$modoForn  		= 1;//MODO DE FORNECIMENTO
	$caractObj 		= 7;//CARACTERÍSTICA DO OBJETO
	$critJulgamento = 1;//CRITERIO DE JULGAMENTO
	$licFracassada  = "02";//LICITACAO FRACASSADA
	
	$dataSessaoAbertura = explode(" ",$licitacao[10]);
	$dataSessaoAbertura = explode("-", $dataSessaoAbertura[0]); //DATA DA SESSAO DE ABERTURA
	$dataSessaoAbertura = $dataSessaoAbertura[2]."/".$dataSessaoAbertura[1]."/".$dataSessaoAbertura[0];//DATA DA SESSAO DE ABERTURA
	
	$endObra 			= "NULO";//ENDERECO DA OBRA
	$regime  			= "NULO";//REGIME DE EXECUCAO
	$tipoIntervencao  	= "NULO";//TIPO DE INTERVENCAO
	$outraIntervencao 	= "NULO";//OUTRO TIPO DE INTERVENCAO
	$zonaTerritorial  	= "NULO";//ZONA TERRITORIAL DA OBRA
	
	$sqlSCC = "SELECT	
					COUNT(*)
				FROM
					SFPC.TBSOLICITACAOLICITACAOPORTAL SLP
				WHERE	
					CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
					AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi";
	
	$numSCC = resultValorUnico(executarSQL($db, $sqlSCC));
	
	if($numSCC == 0){//CASO A LICITACAO NÃO TENHA SCC
		$sqlLB = "SELECT
						CLICBLELE1 ,CLICBLELE2 ,CLICBLELE3 ,CLICBLELE4 ,TUNIDOEXER ,CUNIDOORGA ,CUNIDOCODI ,CLICBLTIPA , ALICBLORDT ,CLICBLFONT
					FROM
						SFPC.TBLICITACAOBLOQUEIOORCAMENT
					WHERE
						CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
						AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi
					LIMIT 	1";	
		
		$resultLB = $db->query($sqlLB);
		$linhaLB = $resultLB->fetchRow();
		
		$numEltoDesp = $linhaLB[0].".".$linhaLB[1].".".$linhaLB[2].".".$linhaLB[3];
		
		if(in_array($numEltoDesp, $array_CodConsumo)){
			$codigo = 1099;//CODIGO/DESCRICAO DO OBJETO
			$natureza = 1;//NATUREZA DO OBJETO
			$tipoOrcamento = 2;//TIPO DE ORCAMENTO
		}
		elseif(in_array($numEltoDesp, $array_CodServico)){
			$codigo = 2099;//CODIGO/DESCRICAO DO OBJETO
			$natureza = 11;//NATUREZA DO OBJETO
			$tipoOrcamento = 4;//TIPO DE ORCAMENTO
		}
		elseif($numEltoDesp == "3.3.90.35"){
			$codigo = 2071;//CODIGO/DESCRICAO DO OBJETO
			$natureza = 11;//NATUREZA DO OBJETO
			$tipoOrcamento = 4;//TIPO DE ORCAMENTO
		}
		else{
			$codigo = 4099;//CODIGO/DESCRICAO DO OBJETO
			$natureza = 11;//NATUREZA DO OBJETO
			$tipoOrcamento = "NULO";//TIPO DE ORCAMENTO
		}
		
		if($licitacao[9] != 'S'){
			//var_dump($sqlLB); die('aqui');
			
			$registroPre = 0;//REGISTRO DE PRECO
			$dotacao  = "LIC. BLOQUEIO:::: ".$linhaLB[4].".".$linhaLB[5].".".$linhaLB[6].".".$linhaLB[7].".";//DOTACAO ORCAMENTARIA
			$dotacao .= $linhaLB[8].".".$numEltoDesp.".".$linhaLB[9];//DOTACAO ORCAMENTARIA
			
			//$dotacao = "2";
		}
		
	}
	else{//CASO A LICITACAO TENHA SCC'S ASSOCIADAS
		$flagSCC = true;
		$dbOracle = ConexaoOracle();
		
		$sqlLB = "SELECT
						AITLBLNBLOQ ,AITLBLANOB
					FROM
						SFPC.TBITEMLICITACAOBLOQUEIO
					WHERE
						CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
						AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi
					LIMIT 1";
		
		$resultLB = $db->query($sqlLB);
		
		
		if($resultLB->numRows() > 0){
			$linhaLB = $resultLB->fetchRow();
			
			$bloqueio = getDadosBloqueioFromChave($dbOracle, $linhaLB[1], $linhaLB[0]);
			$numEltoDesp = $bloqueio['elemento1'].".".$bloqueio['elemento2'].".".$bloqueio['elemento3'].".".$bloqueio['elemento4'];
			
			if(in_array($numEltoDesp, $array_CodConsumo)){
				$codigo = 1099;//CODIGO/DESCRICAO DO OBJETO
				$natureza = 1;//NATUREZA DO OBJETO
				$tipoOrcamento = 2;//TIPO DE ORCAMENTO
			}
			elseif(in_array($numEltoDesp, $array_CodServico)){
				$codigo = 2099;//CODIGO/DESCRICAO DO OBJETO
				$natureza = 11;//NATUREZA DO OBJETO
				$tipoOrcamento = 4;//TIPO DE ORCAMENTO
			}
			elseif($numEltoDesp == "3.3.90.35"){
				$codigo = 2071;//CODIGO/DESCRICAO DO OBJETO
				$natureza = 11;//NATUREZA DO OBJETO
				$tipoOrcamento = 4;//TIPO DE ORCAMENTO
			}
			else{
				$codigo = 4099;//CODIGO/DESCRICAO DO OBJETO
				$natureza = 11;//NATUREZA DO OBJETO
				$tipoOrcamento = "NULO";//TIPO DE ORCAMENTO
			}
			
			if($licitacao[9] != 'S'){
				$registroPre = 0;//REGISTRO DE PRECO
				$dotacao = "BLOQUEIO:::  ".$bloqueio["ano"].".".$bloqueio["orgao"].".".$bloqueio["unidade"];//DOTACAO ORCAMENTARIA
				$dotacao .= ".".$bloqueio["tipoProjetoAtividade"].".".$bloqueio["projetoAtividade"];//DOTACAO ORCAMENTARIA
				//$dotacao  = $bloqueio["orgao"].".".$bloqueio["unidade"].".".$bloqueio["tipoProjetoAtividade"].".";
				$dotacao .= $numEltoDesp.".".$bloqueio["fonte"];//DOTACAO ORCAMENTARIA
					
				//$dotacao = "1";
			}			
			//var_dump($bloqueio); die('aqui');
		}
		$dbOracle->disconnect();
	}
	
	if($licitacao[9] == 'S'){
		$registroPre = 1;//REGISTRO DE PRECO
	
		$sqlRegPre = "SELECT
							AITLDOUNIDOEXER ,CITLDOUNIDOORGA ,CITLDOUNIDOCODI ,CITLDOTIPA ,AITLDOORDT 
							,CITLDOELE1 ,CITLDOELE2 ,CITLDOELE3 ,CITLDOELE4 ,CITLDOFONT
					  FROM
							SFPC.TBITEMLICITACAODOTACAO
					  WHERE 
							CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
							AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi 
						LIMIT 1";
	
		$resultRegPre = $db->query($sqlRegPre);
		$linhaRegPre = $resultRegPre->fetchRow();
		
		//var_dump($sqlRegPre); die;
	
		$dotacao  = "DOTACAO:::: ".$linhaRegPre[0].".".$linhaRegPre[1].".".$linhaRegPre[2].".".$linhaRegPre[3].".".$linhaRegPre[4].".";//DOTACAO ORCAMENTARIA
		$dotacao .= $linhaRegPre[5].".".$linhaRegPre[6].".".$linhaRegPre[7].".".$linhaRegPre[8].".".$linhaRegPre[9];//DOTACAO ORCAMENTARIA
	}
	
	
	$sqlEdital = "SELECT 
						TFASELDATA
				  FROM
						SFPC.TBFASELICITACAO
				  WHERE
						CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
						AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi AND CFASESCODI = 2";
	
	$dataTemp = resultValorUnico(executarSQL($db,$sqlEdital));
	
	if(empty($dataTemp)){
		$dataEditalResumido = "NULO";
	}
	else{
		$dataTemp = explode("-", $dataTemp);
		$dataEditalResumido	= $dataTemp[2]."/".$dataTemp[1]."/".$dataTemp[0];//DATA DE DIVULGACAO DE EDITAL RESUMIDO 
	}
	
	$dataElaboracaoOrc 	= $dataEditalResumido;//DATA DE ELABORACAO DO ORCAMENTO
	$dataPublicao 		= $dataEditalResumido;//DATA DE PUBLICACAO
	
	$sqlJulgamento = "SELECT
							TFASELDATA
					  FROM
							SFPC.TBFASELICITACAO
					  WHERE
							CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
							AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi AND CFASESCODI = 5";
	
	$resultJulg = $db->query($sqlJulgamento);
	
	//var_dump($sqlJulgamento);
	
	if($resultJulg->numRows() >= 1){
		$dataJulg 	= $resultJulg->fetchRow();
		$dataTemp1 	= $dataJulg[0];
		$dataTemp1  = explode("-",$dataTemp1);
		
		$dataJulgamentoPublicacao = $dataTemp1[2]."/".$dataTemp1[1]."/".$dataTemp1[0];//DATA DE PUBLICACAO DO JULGAMENTO
	}
	else{
		$dataJulgamentoPublicacao = "NULO";//DATA DE PUBLICACAO DO JULGAMENTO
	}
	
	$sqlAta = "SELECT
					TFASELDATA
			  FROM
					SFPC.TBFASELICITACAO
			  WHERE
					CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
					AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi AND CFASESCODI = 13";
	
	$resultAta = $db->query($sqlAta);
	
	if($resultAta->numRows() >= 1){
		$ataH = $resultAta->fetchRow();
		$dataTemp2 = explode("-", $ataH[0]);
		
		$ataHomologacao = $dataTemp2[2]."/".$dataTemp2[1]."/".$dataTemp2[0];//ATA DE PUBLICACAO DA HOMOLOGACAO
	}
	else{
		$ataHomologacao = "NULO";//ATA DE PUBLICACAO DA HOMOLOGACAO
	}
	
	/**
	 * $unidGestora = ; //AINDA NÃO DEFINIDO
	 * $edital
	*/
	
	/* Mensagens para verificação*/
	/*echo "-----------------------------------------------------------------------------------------------<br>";
	echo "nome_arquivo: ".$licitacao['nomeArquivo']."<br>org_desc: ".$licitacao[13]."<br>";
	echo "CLICPOPROC = $clicpoproc ALICPOANOP = $alicpoanop CGREMPCODI = $cgrempcodi CCOMLICODI = $ccomlicodi CORGLICODI = $corglicodi 
		  <br>fase: ".$licitacao[0]." - situação: ".$situacaoLic." - código: ".$codigo." - natureza: ".$natureza."<br>";
	echo "num_processo: ".$numProcAno."/".$anoProcLic." - num_processo_adm: ".$numProcAdm."/".$anoProcAdm." - modalidade: ".$modalidade."<br>";
	echo "num_elto_desp: ".$numEltoDesp."<br>reg_preco: ".$registroPre." - dotacao: ".$dotacao."<br>";
	echo "data_sessao_abertura: ".$dataSessaoAbertura."<br>data_orçamento: ".$dataElaboracaoOrc."<br>data_publicacao: ".$dataPublicao."<br>";
	echo "data_julgamento: ".$dataJulgamentoPublicacao."<br>ata_de_homologacao: ".$ataHomologacao."<br><br>";*/
	/* Mensagens para verificação*/
	/*
	if($licitacao[0] == 13){
			
		$sqlForn = "SELECT DISTINCT 
							AFORCRSEQU
					FROM 
							SFPC.TBITEMLICITACAOPORTAL
					WHERE
							CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
							AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi 
							AND AFORCRSEQU IS NOT NULL";
		
		$resultFornc = $db->query($sqlForn);
		
		if($resultFornc->numRows() > 0){
			
			//echo "-----------<br>FORNECEDOR(ES)<br>-----------<br>";
			
			while($fornecedor = $resultFornc->fetchRow()){
				
				$sqlForn1 = "SELECT
									FORN.AFORCRCCPF ,FORN.AFORCRCCGC ,FORN.NFORCRRAZS ,GMS.EGRUMSDESC
									,FORN.AFORCRINES ,FORN.AFORCRINME ,FORN.AFORCRINSM
							 FROM
									SFPC.TBFORNECEDORCREDENCIADO FORN
									JOIN SFPC.TBGRUPOFORNECEDOR GFORN ON GFORN.AFORCRSEQU = FORN.AFORCRSEQU
									JOIN SFPC.TBGRUPOMATERIALSERVICO GMS ON GMS.CGRUMSCODI = GFORN.CGRUMSCODI
							 WHERE
									FORN.AFORCRSEQU = $fornecedor[0]
							 LIMIT 1";
				
				$resultFornc1 = $db->query($sqlForn1);
				
				$infoForn = $resultFornc1->fetchRow();
				
				/*ANTIGO
				$numProcAnoForn = $numProcAno;*/
				/*NOVO*/
		/*		$numProcAnoForn	 = ($licitacao[4]>=10)?$licitacao[4]:"0".$licitacao[4];//NUMERO DO PROCESSO NO ANO
				$numProcAnoForn .= ($licitacao[1]>99)?$licitacao[1]:(($licitacao[1]>=10 && $licitacao[1]<=99)?"0".$licitacao[1]:"00".$licitacao[1]);//NUMERO DO PROCESSO NO ANO
				
				$anoProcLicForn  = $anoProcLic;//ANO DO PROCESSO LICITATORIO
				
				$insEstadual = (!empty($infoForn[4]))?$infoForn[4]:"NULO";
				
				if(empty($infoForn[0])){
					$tipoDocForn = 2;//TIPO DE DOCUMENTO
					$numDocForn = FormataCpfCnpj($infoForn[1]);//NUMERO DO DOCUMENTO
				}
				else{
					$tipoDocForn = 1;//TIPO DE DOCUMENTO
					$numDocForn = FormataCpfCnpj($infoForn[0]);//NUMERO DO DOCUMENTO
				}
				
				$razSocForn 	= $infoForn[2];//NOME/RAZAO SOCIAL
				$ramoAtividade 	= $infoForn[3];//RAMO ATIVIDADE
				$condicaoForn	= 2;//CONDICAO
				
				$fornUFEstadual  	= "NULO";//UF DA INSCRICAO ESTADUAL
				$fornUFMunicipal 	= "NULO";//UF DA INSCRICAO MUNICIPAL
				
				$fornNumEstadual 	= "NULO";//NUMERO DA INSCRICAO ESTADUAL
				$fornNumMunicipal	= "NULO";//NUMERO DA INSCRICAO MUNICIPAL
				$fornMunicipioInsc 	= "NULO";//MUNICIPIO DA INSCRICAO MUNICIPAL
				
				/*SE POSSUIR ITEM LOGRADO
				if(true){
					$fornHabilitacao 		= 1;//HABILITACAO
					$fornResultJulg			= "NULO";//RESULTADO JULGAMENTO
					$fornJustHomologacao	= "NULO";//JUSTIFICATIVA DA HOMOLOGACAO
					$fornFracassada			= "NULO";//FRACASSADA
					$precoAdjudicado		= 9999;
					$precoUnit				= 9999;
				}*/
				
				/*echo "tipo_doc: ".$tipoDocForn." - num_doc: ".$numDocForn."<br>";
				echo "razao_social: ".$razSocForn." - ramo: ".$ramoAtividade."<br>";
				echo "num_insc_estadual: ".$insEstadual." - num_insc_municipal: ".$insMunicipal."<br><br>";*/
			
		//	}
	//	}
//	}//final do if que imprime os fornecedores
	/*
	$sqlItemLic = "SELECT
						ILP.CITELPSEQU ,ILP.CMATEPSEQU ,ILP.CSERVPSEQU ,ILP.VITELPVLOG
						,ILP.AITELPQTSO	,FORN.AFORCRCCPF ,FORN.AFORCRCCGC ,UNID.EUNIDMSIGL
						,ILP.VITELPUNIT
					FROM
						SFPC.TBITEMLICITACAOPORTAL ILP
						LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON ILP.AFORCRSEQU = FORN.AFORCRSEQU
						LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CMATEPSEQU = ILP.CMATEPSEQU
						LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON UNID.CUNIDMCODI = MAT.CUNIDMCODI
					WHERE
						(ILP.CMATEPSEQU IS NOT NULL OR ILP.CSERVPSEQU IS NOT NULL)
						AND (ILP.VITELPVLOG > 0 OR ILP.AFORCRSEQU IS NOT NULL)
						AND ILP.CLICPOPROC = $clicpoproc AND ILP.ALICPOANOP = $alicpoanop AND ILP.CGREMPCODI = $cgrempcodi 
						AND ILP.CCOMLICODI = $ccomlicodi AND ILP.CORGLICODI = $corglicodi
					ORDER BY 
						ILP.CITELPSEQU";
	
	$resultIL = $db->query($sqlItemLic);
	
	//var_dump($sqlItemLic);
	
	if($resultIL->numRows() > 0){
		//echo "-----------<br>ITENS<br>-----------<br>";
		
		while($item = $resultIL->fetchRow()){
			
			$itemNumProc = ($licitacao[4]>=10)?$licitacao[4]:"0".$licitacao[4];//NUMERO DO PROCESSO NO ANO 
			$itemNumProc .= ($licitacao[1]>99)?$licitacao[1]:(($licitacao[1]>=10 && $licitacao[1]<=99)?"0".$licitacao[1]:"00".$licitacao[1]);//NUMERO DO PROCESSO NO ANO 
			//$itemNumProc = $numProcAno;
			
			$itemAno = $anoProcLic;//ANO DO PROCESSO LICITATORIO
			$itemSequencial = $item[0];//SEQUENCIAL DO ITEM
			
			if(empty($item[1])){
				$itemDescricao = resultValorUnico(executarSQL($db,"SELECT ESERVPDESC FROM SFPC.TBSERVICOPORTAL WHERE CSERVPSEQU = $item[2]"));//DESCRICAO DETALHADA DO ITEM
			}
			else{
				$itemDescricao = resultValorUnico(executarSQL($db,"SELECT EMATEPDESC FROM SFPC.TBMATERIALPORTAL WHERE CMATEPSEQU = $item[1]"));//DESCRICAO DETALHADA DO ITEM
			}
			
			$itemQto 			= $item[4];//QUANTIDADE
			$itemUnd 			= (!empty($item[1]))?$item[7]:"UN";//UNIDADE
			$itemPreco 			= $item[8];//PRECO ESTIMADO
			$itemFonte 			= 15;//FONTE DE REFERENCIA DE PRECOS
			$itemDataRef 		= $dataSessaoAbertura;//DATA DE REFERENCIA
			$itemAgrupamento 	= "NULO";//AGRUPAMENTO
			$itemPrecoAdju 		= $item[3];//PRECO ADJUDICADO
			
			if(empty($item[5]) && empty($item[6])){
				$itemCPFCNPJ = "NULO";//CPF/CNPJ DO FORNECEDOR
			}
			elseif(!empty($item[5])){
				$itemCPFCNPJ = FormataCpfCnpj($item[5]);//CPF/CNPJ DO FORNECEDOR
			}
			else{
				$itemCPFCNPJ = FormataCpfCnpj($item[6]);//CPF/CNPJ DO FORNECEDOR
			}
			
			if($flagSCC){
				
				$dbOracle = ConexaoOracle();
				
				$sqlLBI = "SELECT
								AITLBLNBLOQ ,AITLBLANOB
							FROM
								SFPC.TBITEMLICITACAOBLOQUEIO
							WHERE
								CLICPOPROC = $clicpoproc AND ALICPOANOP = $alicpoanop AND CGREMPCODI = $cgrempcodi 
								AND CCOMLICODI = $ccomlicodi AND CORGLICODI = $corglicodi AND CITELPSEQU = $item[0]
							LIMIT 1";
				
				$resultLBI = $db->query($sqlLB);
				
				if($resultLBI->numRows() > 0){
					$linhaLBI = $resultLBI->fetchRow();
						
					$bloqueio = getDadosBloqueioFromChave($dbOracle, $linhaLBI[1], $linhaLBI[0]);
					$numEltoDesp = $bloqueio['elemento1'].".".$bloqueio['elemento2'].".".$bloqueio['elemento3'].".".$bloqueio['elemento4'];
						
					if(in_array($numEltoDesp, $array_CodConsumo)){
						$itemCodRef = 1;//CODIGO REFERENCIA
					}
					elseif(in_array($numEltoDesp, $array_CodServico)){
						$itemCodRef = 11;//CODIGO REFERENCIA
					}
					elseif($numEltoDesp == "3.3.90.35"){
						$itemCodRef = 11;//CODIGO REFERENCIA
					}
					else{
						$itemCodRef = 11;//CODIGO REFERENCIA
					}
				}
				
				$dbOracle->disconnect();
			}
			
			$itemCodRef = (!empty($itemCodRef))?$itemCodRef:"NULO";//CODIGO REFERENCIA
			
			//echo "seq_item: ".$itemSequencial." - desc.: ".$itemDescricao."<br>unidade: ".$itemUnd." - qto: ".$itemQto."<br>";
			//echo "preço_unit: ".$itemPreco." - CPF/CNPJ: ".$itemCPFCNPJ." - cod_ref: ".$itemCodRef."<br><br>";
		}
	}
	
	//echo "-----------------------------------------------------------------------------------------------<br>";
//}//final do foreach
$db->disconnect();*/
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RotExportacaoTCE.php" method="post" name="Documento">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Exportação TCE
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
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
			<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
				EXPORTAÇÃO TCE
			</td>
		</tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Selecione um processo licitatório disponível para Exportação  e clique no botão "Exportar TCE".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <select name="LicitacaoProcesso" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório...</option>
                  	<?php
                  	// Realiza a conexão com o banco de dados
                  	$db = Conexao();
                  	/*VERIFICANDO TODOS OS PROCESSOS LICITATÓRIOS QUE ESTÃO NAS FASES PERMITIDAS*/
                  	$sqlLic = "SELECT DISTINCT
                  					FASE.CFASESCODI ,LIP.CLICPOPROC ,LIP.ALICPOANOP ,LIP.CGREMPCODI 
                  					,LIP.CCOMLICODI ,LIP.CORGLICODI ,LIP.CMODLICODI ,LIP.CLICPOCODL
                  					,LIP.ALICPOANOL ,LIP.FLICPOREGP ,LIP.TLICPODHAB ,FASE.TFASELDATA, FASE.TFASELULAT
                  					,ORG.EORGLIDESC 
                  					,(SELECT CCENPOCORG FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = LIP.CORGLICODI LIMIT 1) AS CODORG
                  					,(SELECT CCENPOUNID FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = LIP.CORGLICODI LIMIT 1) AS CODUNID
                  					, B.ECOMLIDESC
                  				FROM
                  					SFPC.TBCOMISSAOLICITACAO B, SFPC.TBLICITACAOPORTAL LIP
                  					JOIN SFPC.TBFASELICITACAO FASE ON (FASE.CLICPOPROC = LIP.CLICPOPROC AND FASE.ALICPOANOP = LIP.ALICPOANOP 
                  									   AND FASE.CGREMPCODI = LIP.CGREMPCODI AND FASE.CCOMLICODI = LIP.CCOMLICODI 
                  									   AND FASE.CORGLICODI = LIP.CORGLICODI)
                  					JOIN SFPC.TBORGAOLICITANTE ORG ON ORG.CORGLICODI = LIP.CORGLICODI
                  				WHERE
                  					FASE.CFASESCODI IN (2,11,12,13)
                  					AND LIP.CMODLICODI IN (1,2,3,4,5,14)
                  					AND LIP.CCOMLICODI = B.CCOMLICODI
                  					AND FASE.TFASELULAT IN (SELECT 	MAX(TFASELULAT) 
                  											FROM 	SFPC.TBFASELICITACAO 
                  											WHERE 	CLICPOPROC = LIP.CLICPOPROC AND ALICPOANOP = LIP.ALICPOANOP 
                  													AND CGREMPCODI = LIP.CGREMPCODI AND CCOMLICODI = LIP.CCOMLICODI AND CORGLICODI = LIP.CORGLICODI)
                  				ORDER BY
                  					LIP.ALICPOANOP DESC ,LIP.CLICPOPROC DESC LIMIT 80";
                  	// Resultado da query
                  	$resultLic = $db->query($sqlLic);

                  	if (PEAR::isError($resultLic)) {
                  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLic");
                  	} else {
                  		if ($resultLic->numRows() < 1) {
                  			//Exibir mensagem de erro
                  		} else {
                  			// Percorre o resultado da query
                  			while($LinhaLic = $resultLic->fetchRow()){

                  				$cfasescodi = $LinhaLic[0]; // Código da Fase do Processo Licitatório
                  				$clicpoproc = $LinhaLic[1]; // Código do Processo Licitatório
                  				$alicpoanop = $LinhaLic[2]; // Ano do Processo Licitatório
                  				$cgrempcodi = $LinhaLic[3]; // Código do Grupo
                  				$ccomlicodi = $LinhaLic[4]; // Código da Comissão
                  				$corglicodi = $LinhaLic[5]; // Código do Órgão Licitante
                  				$cmodlicodi = $LinhaLic[6]; // Código da Modalidade

                  				// Verifica se o código da comissão é igual ao anterior
                  				if( $ccomlicodi != $ComissaoCodigoAnt ){
                  					// Atualiza o valor do código da comissão anterior
                  					$ComissaoCodigoAnt = $ccomlicodi;
                  					// Imprime o option group
                  					echo "<optgroup label='$LinhaLic[16]'>";
                  				}
                  				
                  				// Armazena o valor do select
                  				$valorProcesso = $clicpoproc.'_'.$alicpoanop.'_'.$ccomlicodi.'_'.$cgrempcodi.'_'.$corglicodi.'_'.$cfasescodi.'_'.$cmodlicodi;
                  				$NProcesso = substr($clicpoproc + 10000,1);
                  				// Imprime o select
								echo '<option value="'.$valorProcesso.'">&nbsp;&nbsp;&nbsp;'.$NProcesso.'/'.$alicpoanop.'</option>\n' ;
                  				
                  			}
                  		}
                  	}
					?>
                  </select>
                  <input type="hidden" name="Critica" value="1">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="textonormal" align="right">
             <input type="submit" value="Selecionar" class="botao">
          </td>
        </tr>
      </table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
<script language="javascript" type="">
<!--
document.Documento.LicitacaoProcessoAnoComissaoOrgao.focus();
//-->
</script>
</body>
</html>
