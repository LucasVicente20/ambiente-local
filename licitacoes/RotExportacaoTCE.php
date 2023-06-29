<?php
require_once("../compras/funcoesCompras.php");
require_once('../funcoes.php');

//include($GLOBALS["CAMINHO_PDF"].'fpdi.php');
$caminhoFPDI = str_replace("phpmailer", "fpdi", $GLOBALS["CAMINHO_EMAIL"]);
//print_r(scandir($caminhoFPDI));

$caminhoFPDI .= "fpdi.php";

require_once($caminhoFPDI);

define(USER_TCE, "03031608453");
define(PASS_TCE, "03031608453");
define(NULO, "NULO");

# Executa o controle de segurança#
session_start();
//Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RotExportacaoTCE.php' );

if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$Mensagem	= urldecode($_GET['Mensagem']);
	$Mens		= $_GET['Mens'];
	$Tipo		= $_GET['Tipo'];
	$Critica	= $_GET['Critica'];
}

/**
 * Função auxiliar para formatar data
 * obs: A data poderá vir no formato 'yyyy/mm/dd' ou 'yyyy/mm/dd hh:mm:ss'
 *
 * @param String $data
 */
function formatarData($data){
	if($data != null){
		if(strlen(trim($data)) > 10){
			$data = explode(" ", $data);
			$data = $data[0];
		}
	
		$data = explode("-", $data);
		$data = $data[2]."/".$data[1]."/".$data[0];
		
		return $data;
	}
	else{
		return "";
	}
}

/**
 * Função que faz uso da biblioteca FPDI para concatenação de PDF's
 * 
 * @param PDF $arquivos			- arquivos que serão concatenados
 * @param String $outputpath	- endereço onde será salvo e o nome do arquivo final após a concatenação
 */
function concat($arquivos, $outputpath){

	$fpdi = new FPDI();

	foreach($arquivos as $arq){
		
		$count = $fpdi->setSourceFile($arq);
		for($i=1; $i<=$count; $i++){
			$template 	= $fpdi->importPage($i);
			$size 		= $fpdi->getTemplateSize($template);

			$fpdi->AddPage('P', array($size['w'], $size['h']));
			$fpdi->useTemplate($template);
		}
	}
	//'F' para salvar; 'I' para exibir no browser; 'D' para download
	$fpdi->Output($outputpath, 'D');

}

/**
 * Método para criar o XML da licitacação de acordo com padrão passado pelo TCE.
 * As verificações de quantidade de itens, se não é um item do tipo obra serão feitos pelos
 * programas que chamarão essa função, conforme estabelecido no documento da 12ª entrega de EMPREL,
 * feita pela BankSystem
 * 
 * Essa função será utilizada no programa de fases e alterar licitação. Também poderá ser chamado
 * dentro da função exportarDadosTCE
 * 
 * @param int $numeroLic 	- número da licitação (CLICPOPROC)
 * @param int $anoLic 		- ano da licitação (ALICPOANOP)
 * @param int $comissaoLic 	- código da comissão da licitação (CCOMLICODI)
 * @param int $grupoLic 	- código do grupo da licitação (CGREMPCODI)
 * @param int $orgaoLic		- código do órgão da licitação (CORGLICODI)
 * @param int $fase			- código da fase de licitação (CFASESCODI)
 * @param int $modalidade	- código da modalidade
 * 
 * @return String $arquivoXml 	- arquivo XML contendo os dados da licitação
 */
function criarXML($numeroLic, $anoLic, $comissaoLic, $grupoLic, $orgaoLic, $fase, $modalidade=null){
	
	$db = Conexao();

	/*
	 * Arrays com os códigos de tipos de materiais 
	 */
	$array_CodConsumo = array("3.3.90.30", "4.4.90.52", "4.4.90.30", "3.3.90.35");
	$array_CodServico = array("3.3.90.37", "3.3.90.39", "4.4.90.35", "4.4.90.39","3.3.90.33", "4.4.90.33", "3.3.90.36");

	/*
	 * Array com os códigos de obras
	 */
	$arrayObras = resultValorUnico(executarSQL($db, "SELECT epargesubo FROM sfpc.tbparametrosgerais"));
	$arrayObras = str_replace(" ", "", $arrayObras);
	$arrayObras = explode(",",$arrayObras);
	
	/*
	 * Informações do Processo
	 */
	$numProcAnoTCE			= str_pad($numeroLic, 5 ,"00000", STR_PAD_LEFT); //número do processo no ano
	$anoProcTCE				= $anoLic; //ano do processo licitatório
	$portariaComissao 		= str_pad($comissaoLic, 5, "00000", STR_PAD_LEFT); //portaria de designação da comissão de licitação
	$anoPortariaComissao	= date("Y"); //ano da portaria de designação da comissão de licitação
	$caracteristicaObj		= 7; //característica do objeto (lotes)
	$modoFornecimento		= 1; //modo de fornecimento
	$criterioJulgamento		= 1; //critério de julgamento
	$zonaTerritorialObra	= 1; // Zona territorial da obra ou serviço de engenharia (Recife)
	$nomeNivel				= "ÚNICO"; //nome nível
	$codigoNivel			= 1; //código nível

	/*
	 * Informações dos lotes
	 */
	$unidadeMedidaLote		= 35; // Unidade de medida do lote (un)
	$fonteReferenciaLote	= 13; //fonte de referência do lote
	$codReferenciaLote		= "0000000"; //código de referência do lote
	$niveis					= 1; //níveis
	
	/*
	 * Informações da fase de homologação - fornecedor
	 */
	$condicaoHomologacao	= 2; //condição
	$municipioInscMunicipal	= "P120"; //município da inscrição municipal
	$numeroInscMunicipal	= 99; //número da inscrição municipal
	$ufInscEstadual			= "PE"; //UF da inscrição estadual
	$ufInscMunicipal		= "PE"; //UF da inscrição municipal
	$percentualDesconto		= 001; //percentual de desconto
	$valorTotalPregao			= 0;	
	
	/*
	 * Informações que serão nulas no xml
	 */
	$numProcAdmAno 			= "NULO"; //número do processo administrativo no ano
	$anoProcAdm				= "NULO"; //ano do processo administrativo
	$tipoIntervencao		= "NULO"; //tipo de intervenção
	$tipoIntervencaoOutros	= "NULO"; //tipo de intervenção - outros 
	$outroTipoIntervencao	= "NULO"; //outro tipo de intervenção
	$regimeExecucao			= "NULO"; //regime de execução
	$fundamentacaoLegal		= "NULO"; //fundamentação legal
	$dataReferenciaLote		= "NULO"; //data de referência do lote
	$bdiLote				= "NULO"; //bdi do lote
	$dataReferencia			= "NULO"; //data referência
	$bdi					= "NULO"; //bdi
	$justificativa			= "NULO"; //justificativa

	$inversaoFases = ($modalidade == 5 || $modalidade == 14)?"01":"NULO"; //inversão de fases
		
	switch($modalidade){
		case 1:
			$modalidadeTCE = "05";
			break;
		case 2:
			$modalidadeTCE = "04";
			break;
		case 3:
			$modalidadeTCE = "01";
			break;
		case 4:
			$modalidadeTCE = "07";
			break;
		case 5:
			$modalidadeTCE = "09";
			break;
		case 14:
			$modalidadeTCE = "08";
			break;
	}
		
	/*
	 * Consulta para gerar o nome do xml
	 */
	$sqlTitulo 	= "SELECT CCENPOCORG, CCENPOUNID FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = $orgaoLic LIMIT 1";
	$resTitulo 	= resultLinhaUnica(executarSQL($db, $sqlTitulo));
	
	$sqlOrgDesc	= "SELECT EORGLIDESC FROM SFPC.TBORGAOLICITANTE WHERE CORGLICODI = $orgaoLic LIMIT 1";
	$orgaoDesc	= resultValorUnico(executarSQL($db, $sqlOrgDesc));
	
	$codOrgPCR	= str_pad($resTitulo[0], 2, "000", STR_PAD_LEFT);
	$codUndPCR	= str_pad($resTitulo[1], 2, "000", STR_PAD_LEFT);
	$codComPCR	= str_pad($comissaoLic, 2, "000", STR_PAD_LEFT);
	$codNPLPCR	= str_pad($numeroLic, 4, "0000", STR_PAD_LEFT);

	/*
	 * consulta que irá retornar linha única contendo 
	 * dados do processo de licitação.
	 */
	$sqlLic = "SELECT		LIC.CLICPOCODL, LIC.ALICPOANOL, LIC.FLICPOREGP, LIC.TLICPODHAB, ILP.CITELPNUML, LIC.XLICPOOBJE 
				FROM		SFPC.TBLICITACAOPORTAL LIC
							JOIN SFPC.TBITEMLICITACAOPORTAL ILP ON ( LIC.CLICPOPROC =  ILP.CLICPOPROC AND LIC.ALICPOANOP = ILP.ALICPOANOP
							AND LIC.CGREMPCODI = ILP.CGREMPCODI AND LIC.CCOMLICODI = ILP.CCOMLICODI AND LIC.CORGLICODI = ILP.CORGLICODI)
				WHERE		LIC.CLICPOPROC = $numeroLic AND LIC.ALICPOANOP = $anoLic AND LIC.CGREMPCODI = $grupoLic 
							AND LIC.CCOMLICODI = $comissaoLic AND LIC.CORGLICODI = $orgaoLic
				ORDER BY 	ILP.CITELPNUML DESC 
				LIMIT 		1";
	
	$resultLic = resultLinhaUnica(executarSQL($db, $sqlLic));
	
	if (PEAR::isError($resultLic)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLic");
	}
	else{
		$numeroModalidade 	= str_pad($resultLic[0], 5, "00000", STR_PAD_LEFT); //número da modalidade no ano
		$anoModalidade		= $resultLic[1]; 				//ano da modalidade do processo licitatório
		$registroPreco		= ($resultLic[2] == 'S')?1:0;	//registro de preço
		$dataSessaoAbertura	= formatarData($resultLic[3]);	//data da sessão de abertura
		$loteUnico			= ($resultLic[4] == 1)?1:0;		//lote único
		$enderecoObra		= $resultLic[5]; 				//endereço da obra ou serviço de engenharia;
	}
	
	/*
	* Verificando se a licitação possui itens válidos (não são do tipo obras)
	*/
	$flag = false;
	
	$sqlItemLic = "SELECT DISTINCT CMATEPSEQU, CSERVPSEQU
				   FROM 	SFPC.TBITEMLICITACAOPORTAL 
				   WHERE 	(CMATEPSEQU IS NOT NULL OR CSERVPSEQU IS NOT NULL) 
							AND CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic
							AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic ";

	$resultItemLic = $db->query($sqlItemLic);

	if($resultItemLic->numRows() > 0){
		while($LinhaItem = $resultItemLic->fetchRow()){

			if($LinhaItem[0] != null){
				$gSubSql = "SELECT 	DISTINCT GRU.CGRUSEELE1 ,GRU.CGRUSEELE2 ,GRU.CGRUSEELE3 ,GRU.CGRUSEELE4 ,GRU.CGRUSESUBE
							FROM 	SFPC.TBGRUPOSUBELEMENTODESPESA GRU
									JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.CGRUMSCODI = GRU.CGRUMSCODI
									JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU
							WHERE	MAT.CMATEPSEQU = $LinhaItem[0] LIMIT 	1";
			}
			else{
				$gSubSql = "SELECT 	DISTINCT GRU.CGRUSEELE1 ,GRU.CGRUSEELE2 ,GRU.CGRUSEELE3 ,GRU.CGRUSEELE4 ,GRU.CGRUSESUBE
							FROM 	SFPC.TBGRUPOSUBELEMENTODESPESA GRU
									JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CGRUMSCODI = GRU.CGRUMSCODI
							WHERE	SERV.CSERVPSEQU = $LinhaItem[1]	LIMIT 	1";
			}
				
			$gSubResult = $db->query($gSubSql);
			$gSub = $gSubResult->fetchRow();
			$gSub = $gSub[0].".".$gSub[1].".".$gSub[2].".".$gSub[3].".".$gSub[4];
				
			if(in_array($gSub, $arrayObras)){
				$flag = true;
				break;
			}
		}//fim do while de itens
		
		if($flag){
			//TODO tratamento para criar arquivo caso ele tenha itens inválidos
			//gerar mensagem/tratar o erro quando existirem arquivos do tipo obras
// 			exit;
		}
	}

	$sqlSCC = "SELECT	COUNT(*) FROM 	SFPC.TBSOLICITACAOLICITACAOPORTAL SLP
			   WHERE	CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic	";
	
	$numSCC = resultValorUnico(executarSQL($db, $sqlSCC));
	
	if($numSCC == 0){ //CASO A LICITACAO NÃO TENHA SCC
		$sqlLB = "SELECT	CLICBLELE1 ,CLICBLELE2 ,CLICBLELE3 ,CLICBLELE4 ,TUNIDOEXER ,CUNIDOORGA ,CUNIDOCODI ,CLICBLTIPA , ALICBLORDT ,CLICBLFONT
				  FROM		SFPC.TBLICITACAOBLOQUEIOORCAMENT
				  WHERE		CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic
				  LIMIT 	1";	
	
		$resultLB = $db->query($sqlLB);
		$linhaLB = $resultLB->fetchRow();
	
		$numEltoDesp = $linhaLB[0].".".$linhaLB[1].".".$linhaLB[2].".".$linhaLB[3];
		
		if($registroPreco == 0){
			$dotacao  = $linhaLB[4].".".$linhaLB[5].".".$linhaLB[6].".".$linhaLB[7].".";//DOTACAO ORCAMENTARIA
			$dotacao .= $linhaLB[8].".".$numEltoDesp.".".$linhaLB[9];//DOTACAO ORCAMENTARIA
		}
	}//fim do if
	else{//CASO A LICITACAO TENHA SCC'S ASSOCIADAS
		$flagSCC = true;
		$dbOracle = ConexaoOracle();
	
		$sqlLB = "SELECT	AITLBLNBLOQ ,AITLBLANOB	FROM	SFPC.TBITEMLICITACAOBLOQUEIO
				  WHERE		CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic
				  LIMIT 1";
	
		$resultLB = $db->query($sqlLB);

		if($resultLB->numRows() > 0){
			$linhaLB = $resultLB->fetchRow();
				
			$bloqueio = getDadosBloqueioFromChave($dbOracle, $linhaLB[1], $linhaLB[0]);
			$numEltoDesp = $bloqueio['elemento1'].".".$bloqueio['elemento2'].".".$bloqueio['elemento3'].".".$bloqueio['elemento4'];
			// Fecha a conexão com o banco
			$dbOracle->disconnect();
			
			if($registroPreco == 0){
				$dotacao  = $bloqueio["ano"].".".$bloqueio["orgao"].".".$bloqueio["unidade"].".".$bloqueio["tipoProjetoAtividade"].".";//DOTACAO ORCAMENTARIA
				$dotacao .= $bloqueio["projetoAtividade"].".".$numEltoDesp.".".$bloqueio["fonte"];//DOTACAO ORCAMENTARIA
			}
		}
	}//fim do else
	
	if($registroPreco == 1){
		$sqlRegPre = "SELECT	AITLDOUNIDOEXER ,CITLDOUNIDOORGA ,CITLDOUNIDOCODI ,CITLDOTIPA ,AITLDOORDT ,CITLDOELE1 ,CITLDOELE2 ,CITLDOELE3 ,CITLDOELE4 ,CITLDOFONT
					  FROM		SFPC.TBITEMLICITACAODOTACAO
					  WHERE 	CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic 
								AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic 
					  LIMIT 1";

		$resultRegPre = $db->query($sqlRegPre);
		$linhaRegPre  = $resultRegPre->fetchRow();
	
		$dotacao  = $linhaRegPre[0].".".$linhaRegPre[1].".".$linhaRegPre[2].".".$linhaRegPre[3].".".$linhaRegPre[4].".";//DOTACAO ORCAMENTARIA
		$dotacao .= $linhaRegPre[5].".".$linhaRegPre[6].".".$linhaRegPre[7].".".$linhaRegPre[8].".".$linhaRegPre[9];//DOTACAO ORCAMENTARIA
	}
	
	if(in_array($numEltoDesp, $array_CodConsumo)){
		$codigoDescObj 	= 1099;//CODIGO/DESCRICAO DO OBJETO
		$tipoOrcamento 	= 2;//TIPO DE ORCAMENTO
		$naturezaObj 	= 1;//NATUREZA DO OBJETO
	}
	elseif(in_array($numEltoDesp, $array_CodServico)){
		$codigoDescObj 	= 2099;//CODIGO/DESCRICAO DO OBJETO
		$tipoOrcamento 	= 4;//TIPO DE ORCAMENTO
		$naturezaObj	= 11;//NATUREZA DO OBJETO
	}
	else{
		$codigoDescObj 	= 4099;//CODIGO/DESCRICAO DO OBJETO
		$tipoOrcamento 	= "NULO";//TIPO DE ORCAMENTO
		$naturezaObj 	= 1;//NATUREZA DO OBJETO
	}
	
	$sqlDataDivulgacao = "SELECT	TFASELDATA	FROM	SFPC.TBFASELICITACAO
				  		  WHERE		CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic 
									AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic AND CFASESCODI = 2";
	
	$dataDivulgacao = resultValorUnico(executarSQL($db,$sqlDataDivulgacao)); 
	$dataDivulgacao = formatarData($dataDivulgacao); //data de divulgação do edital & data de elaboração do orçamento - formatada & data publicacao/divulgacao
	
	/*
	 *Informações sobre os lotes
	 */
	$sqlLotes = "SELECT 	ILP.CITELPNUML, SUM(ILP.AITELPQTSO), SUM(ILP.VITELPUNIT), to_char(ILP.TITELPULAT, 'dd/mm/YYYY')
				 FROM 	 	SFPC.TBITEMLICITACAOPORTAL ILP
				 WHERE 	 	ILP.CLICPOPROC = $numeroLic	AND ILP.ALICPOANOP = $anoLic AND ILP.CGREMPCODI = $grupoLic
							AND ILP.CCOMLICODI = $comissaoLic AND ILP.CORGLICODI = $orgaoLic
				 GROUP BY	ILP.CITELPNUML, ILP.TITELPULAT
				 ORDER BY	ILP.CITELPNUML";
	$resultLotes = $db->query($sqlLotes);

	$lotesLicitacao 		= array();
	$licitantesVencedores 	= array();

	if($resultLotes->numRows() > 0){
		while($infoLote = $resultLotes->fetchRow()){ 
			/*
			 * Informações sobre os itens de cada lote
			 */
			$sqlItensLote = "SELECT ILP.CMATEPSEQU, ILP.CSERVPSEQU, ILP.AITELPQTSO, ILP.VITELPUNIT, MAT.EMATEPDESC, --4
									SERV.ESERVPDESC, UNID.EUNIDMSIGL, ILP.FITELPLOGR, ILP.AFORCRSEQU, ILP.CITELPSEQU, --9
									ILP.AITELPQTSO , ILP.VITELPVLOG, to_char(ILP.TITELPULAT, 'dd/mm/YYYY') --11
							 FROM 	SFPC.TBITEMLICITACAOPORTAL ILP
									LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CMATEPSEQU = ILP.CMATEPSEQU
									LEFT JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CSERVPSEQU = ILP.CSERVPSEQU
									LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UNID ON MAT.CUNIDMCODI = UNID.CUNIDMCODI
							 WHERE	ILP.CLICPOPROC = $numeroLic	AND ILP.ALICPOANOP = $anoLic AND ILP.CGREMPCODI = $grupoLic
									AND ILP.CCOMLICODI = $comissaoLic AND ILP.CORGLICODI = $orgaoLic AND ILP.CITELPNUML = $infoLote[0]
									AND (ILP.CMATEPSEQU IS NOT NULL OR ILP.CSERVPSEQU IS NOT NULL)";
			$resultItensLote = $db->query($sqlItensLote);
			
			$arrayItens 	= array();
			$flagFornecedor = false;

			if($resultItensLote->numRows() > 0){
				while($itensLote = $resultItensLote->fetchRow()){
					$arrayItens["quantidadeItem"]		= $itensLote[2];
					$arrayItens["precoUnitarioItem"]	= $itensLote[3];
					
					if($itensLote[0] == 0 || $itensLote[0] == null){
						$arrayItens["descricaoItem"] 		= $itensLote[5];
						$arrayItens["unidadeItem"] 			= "UN";
						$arrayItens["fonteReferenciaItem"] 	= $itensLote[1]."-DUS";
						$arrayItens["codigoReferenciaItem"]	= $itensLote[1];
						$arrayItens["dataReferencia"]		= $itensLote[12];
					}
					else{
						$arrayItens["descricaoItem"] 		= $itensLote[4];
						$arrayItens["unidadeItem"] 			= $itensLote[6];
						$arrayItens["fonteReferenciaItem"] 	= $itensLote[0]."-DUM";
						$arrayItens["codigoReferenciaItem"]	= $itensLote[0];
						$arrayItens["dataReferencia"]		= $itensLote[12];
					}
					
					$arrayItens["sequencialItem"] 	= $itensLote[9];
					$arrayItens["qteItem"] 			= $itensLote[10]; 
					$arrayItens["valorItem"] 		= $infoLote[11];

					$codFornecedor = '';
					if($itensLote[7] == "S"){
						$flagFornecedor = true;
						if (isset($itensLote[8])) {
							$codFornecedor = "AND ILP.AFORCRSEQU = $itensLote[8]";
						}
					}
				}//fim do while

				if($fase == 13 && $flagFornecedor){//informações do licitante habilitado de cada lote, caso a fase do licitação seja de homologação
					$sqlLicitanteHabilitado = "SELECT DISTINCT ILP.AFORCRSEQU, FORN.AFORCRCCPF, FORN.AFORCRCCGC, FORN.NFORCRRAZS, --3
														SUM(ILP.VITELPVLOG * ILP.AITELPQTSO) AS VALOR_PROPOSTA, FORN.AFORCRINES --5
											   FROM 	SFPC.TBITEMLICITACAOPORTAL ILP JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = ILP.AFORCRSEQU 
											   WHERE	ILP.CLICPOPROC = $numeroLic	AND ILP.ALICPOANOP = $anoLic AND ILP.CGREMPCODI = $grupoLic
														AND ILP.CCOMLICODI = $comissaoLic AND ILP.CORGLICODI = $orgaoLic AND ILP.CITELPNUML = $infoLote[0]
														$codFornecedor AND ILP.FITELPLOGR = 'S' AND ILP.VITELPVLOG > 0
											   GROUP BY ILP.AFORCRSEQU, FORN.AFORCRCCPF, FORN.AFORCRCCGC, FORN.NFORCRRAZS, FORN.AFORCRINES";

					
					$fornecedorHabilitado = resultObjetoUnico(executarSQL($db,$sqlLicitanteHabilitado));

					if (!empty($fornecedorHabilitado)) {
						if($fornecedorHabilitado->aforcrccpf == null){ 
							$numeroDocForn	= FormataCpfCnpj($fornecedorHabilitado->aforcrccgc); //num documento
							$tipoDocumento	= 2; //tipo de documento cpf/cnpj
						}
						else{ 
							$numeroDocForn 	= FormataCpfCnpj($fornecedorHabilitado->aforcrccpf); //num documento
							$tipoDocumento	= 1; //tipo de documento cpf/cnpj
						}
						
						$numeroInscEstadual = ($fornecedorHabilitado->aforcrines != null)?$fornecedorHabilitado->aforcrines:99; //número de inscrição estadual
						
						$sqlRamoAtividade 	= "SELECT 	GMS.EGRUMSDESC 
											   FROM 	SFPC.TBGRUPOMATERIALSERVICO GMS JOIN SFPC.TBGRUPOFORNECEDOR GRF ON GRF.CGRUMSCODI = GMS.CGRUMSCODI
											   WHERE	GRF.AFORCRSEQU = $fornecedorHabilitado->aforcrsequ LIMIT 1";

						$resultRamo		= resultValorUnico(executarSQL($db, $sqlRamoAtividade));
						$ramoAtividade	= ($resultRamo != null)?$resultRamo:"NENHUM RAMO CADASTRADO"; //ramo de atividade
	
						$licitanteHabilitado = array("propostaValida" => "S", "valorPropostaLote" => $fornecedorHabilitado->valor_proposta, 
													 "numeroDocForn" => $numeroDocForn, "numeroInscEstadual" => $numeroInscEstadual,
													 "tipoDocumento" => $tipoDocumento, "ramoAtividade" => $ramoAtividade, "razaoSocial" => $fornecedorHabilitado->nforcrrazs);
					}

					if($licitantesVencedores[$numeroDocForn] == null){						
						$licitantesVencedores[$numeroDocForn] = $licitanteHabilitado;
					}
				}
				else{
					$licitanteHabilitado = array("propostaValida" => "NULO", "valorPropostaLote" => "NULO", "numeroDocForn" => "NULO");
				}		
			}//fim do if para verificar se existem itens no lote de uma licitacao;

			$lotesLicitacao[$infoLote[0]] = array("sequencialLote" => $infoLote[0], "descricaoLote" =>$infoLote[0], 
									  			  "quantidadeLote" => $infoLote[1], "precoUnitarioLote" => $infoLote[2], 
												  "itens" => $arrayItens, "licitanteHabilitado" => $licitanteHabilitado,
												  "dataReferencia" => $infoLote[3]);
		}//fim do while	
	}//fim do if que verifica se existem lotes em uma licitacao
	
	if($fase == 13){
		$sqlDataJulgamento = "SELECT TFASELDATA	FROM	SFPC.TBFASELICITACAO
			  				  WHERE	 CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic AND 
									 CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic AND CFASESCODI = 5";
		
		$dataJulgamento	= resultValorUnico(executarSQL($db, $sqlDataJulgamento));
		$dataJulgamento = ($dataJulgamento != null)?formatarData($dataJulgamento):$dataDivulgacao; //data de publicação/divulgação (fase de julgamento)
		
		$sqlDataHomologacao = "SELECT 	TFASELDATA FROM	SFPC.TBFASELICITACAO
					  		   WHERE	CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic
										AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic AND CFASESCODI = 13";
		
		$dataHomologacao = resultValorUnico(executarSQL($db, $sqlDataHomologacao));
		$dataHomologacao = formatarData($dataHomologacao); //data de publicação/divulgação (fase de adjudicação/homologacao)
	}
	
	$sqlSituacao = "SELECT	CFASESCODI, MAX(TFASELULAT) AS DATA FROM SFPC.TBFASELICITACAO
					WHERE	CLICPOPROC = $numeroLic AND ALICPOANOP = $anoLic AND CGREMPCODI = $grupoLic
							AND CCOMLICODI = $comissaoLic AND CORGLICODI = $orgaoLic 
					GROUP BY CFASESCODI ORDER BY DATA DESC LIMIT 1 ";
	
	$faseLic = resultValorUnico(executarSQL($db, $sqlSituacao));
	
	switch($faseLic){
		case 2:
			$situacaoLic = 7; //situação da licitação
			$estagioLic	 = 3; //estágio da licitação
			break;
		case 11:
			$situacaoLic = 4;
			$estagioLic	 = 3;
			break;
		case 12:
			$situacaoLic = 3;
			$estagioLic	 = 3;
			break;
		case 13:
			$situacaoLic = 9;
			$estagioLic	 = 7;
			break;
	}
	
	$unidadeGestoraTemp = str_pad($orgaoLic, 6, "000000", STR_PAD_LEFT);
	$unidadeGestora 	= substr($unidadeGestoraTemp, 0, 3).".".substr($unidadeGestoraTemp, 3);  //unidade gestora
	$unidadeGestora = "120.001";
	
	// Nome do arquivo XML
	$tituloXML 	= "PL_".$codOrgPCR.$codUndPCR."_".$orgaoDesc."_".$codComPCR."_".$codNPLPCR."_".$anoLic.".xml";
	// Caminho que o arquivo XML será salvo
	
	/*
	 * TODO
	 * Caminho apenas para teste
	 * Descomentar ao passar para produção
	 */
	// $diretorio = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos";'
	$diretorio = "exportacao_TCE/documentos"; // APAGAR

	$nomeXML = $diretorio."/".$tituloXML;

	if(!is_dir($diretorio)){
		mkdir($diretorio, 0777, true);
	}

	/**
	* Apenas para teste
	*/
//  	header("Content-Type: text/html/force-download");
//  	header("Content-Disposition: attachment; filename='" . $tituloXML . "'");
	
//  	$oXMLout = new XMLWriter();
	
//  	$oXMLout->openURI('php://output');
	/**
	 * Apenas para teste
	 */

	//criando arquivo xml - resultado a ser exportado
	$oXMLout = new XMLWriter();
	// $oXMLout->openMemory();
	$oXMLout->openURI($nomeXML);

	$oXMLout->setIndent(true);
	
	$oXMLout->startDocument('1.0', 'UTF-8');
	
	$oXMLout->startElement("processo");
		/*
		 * Início instauracao
		 * Revisão: 03/12/2013
		 */
		$oXMLout->startElement("instauracao");
			$oXMLout->writeElement("processo-numero", $numProcAnoTCE);
			$oXMLout->writeElement("processo-ano", $anoProcTCE);
// 			$oXMLout->writeElement("processo-adm-numero", "NULO"); // Sempre NULO
// 			$oXMLout->writeElement("processo-adm-ano", "NULO"); // Sempre NULO
			$oXMLout->writeElement("processo-modalidade", $modalidadeTCE);
			$oXMLout->writeElement("modalidade-numero", $numeroModalidade);
			$oXMLout->writeElement("modalidade-ano", $anoModalidade);
			$oXMLout->writeElement("classificacao-objeto", $codigoDescObj);
			$oXMLout->writeElement("processo-natureza-objeto", $naturezaObj);
			$oXMLout->writeElement("processo-portaria-designa-numero", $portariaComissao);
			$oXMLout->writeElement("processo-portaria-designa-ano", $anoPortariaComissao);
			$oXMLout->writeElement("processo-caracteristica", 7);
// 			$oXMLout->writeElement("processo-tipo-intervencao", "NULO"); // Sempre NULO
			$oXMLout->writeElement("sistema-registro-preco", $registroPreco);
// 			$oXMLout->writeElement("processo-outro-tipo-intervencao", "NULO"); // Sempre NULO
		// Fim instauracao
		$oXMLout->endElement();
		
		/*
		 * Início edital
		 * Revisão:
		 */
		$oXMLout->startElement("edital");
// 			$oXMLout->writeElement("data-emissao", $dataDivulgacao);
			$oXMLout->writeElement("data-divulgacao-publicacao", $dataDivulgacao);
			// Verifica se existe dotação
			if(!empty($dotacao)){
				$oXMLout->writeElement("dotacao-orcamentaria", $dotacao);
			} else {
				$oXMLout->writeElement("dotacao-orcamentaria", "");
			}
			
// 			$oXMLout->writeElement("regime-execucao", "NULO"); // Sempre Nulo
			$oXMLout->writeElement("modo-fornecimento", $modoFornecimento);
			$oXMLout->writeElement("criterio-julgamento", $criterioJulgamento);
			$oXMLout->writeElement("data-sessao-abertura", $dataSessaoAbertura);
			// Verifica se existe inversão de fases
			if($inversaoFases != "NULO"){
				$oXMLout->writeElement("inversao-fases", $inversaoFases);
			} else {
				$oXMLout->writeElement("inversao-fases", 0);
			}
			
// 			$oXMLout->writeElement("fundamentacao-legal", "NULO"); // Sempre Nulos
			$oXMLout->writeElement("lote-unico", $loteUnico);
			
			/*
			 * Início orcamento
			 */
			$oXMLout->startElement("orcamento");
				
				if($tipoOrcamento != "NULO"){
					$oXMLout->writeElement("tipo-orcamento", $tipoOrcamento);
				} else {
// 					$oXMLout->writeElement("tipo-orcamento", $tipoOrcamento);
				}
				
				$oXMLout->writeElement("data-elaboracao-orcamento", $dataDivulgacao);
				$oXMLout->writeElement("zona-territorial-obraservico", $zonaTerritorialObra);

				if(!empty($enderecoObra)){
					$oXMLout->writeElement("endereco-obraservico", substr($enderecoObra, 0, 100));
				} else {
// 					$oXMLout->writeElement("endereco-obraservico", "NULO"); // Sempre Nulo
				}
				
// 				$oXMLout->writeElement("detalhamento-objeto", "NULO"); // FALTA
			
				/*
				 * Início niveis
				 * Revisão: 03/12/2013
				 */
				$oXMLout->startElement("niveis");
					/*
					 * Início nivel
					 * Revisão: 03/12/2013
					 */
					$oXMLout->startElement("nivel");
						$oXMLout->writeElement("nome", $nomeNivel);
						$oXMLout->writeElement("codigo-nivel", $codigoNivel);
					// Fim nivel
					$oXMLout->endElement();
				// Fim niveis
				$oXMLout->endElement();				
				
				/*
				 * Início lotes
				 */
				$oXMLout->startElement("lotes");
				
				foreach ($lotesLicitacao as $loteAtual){
					/*
					 * Início lote
					 * Revisão: 
					 */
					$oXMLout->startElement("lote");
						$oXMLout->writeElement("descricao-detalhada", $loteAtual['itens']["descricaoItem"]);
						$oXMLout->writeElement("unidade-medida", $unidadeMedidaLote);
						$oXMLout->writeElement("quantidade", $loteAtual["quantidadeLote"]);
						$oXMLout->writeElement("preco-unitario-estimado", $loteAtual["precoUnitarioLote"]);
						$oXMLout->writeElement("data-referencia", $loteAtual["dataReferencia"]);
// 						$oXMLout->writeElement("bdi", "NULO"); // Sempre Nulo
						$oXMLout->writeElement("fonte-referencia-precos", $fonteReferenciaLote);
						$oXMLout->writeElement("codigo-referencia", $codReferenciaLote);
						
						/*
						 * Início niveis
						 * Revisão: 03/12/2013
						 */
// 						$oXMLout->startElement("nivel");
// 							$oXMLout->writeElement("nome", $nomeNivel);
// 							$oXMLout->writeElement("codigo-nivel", $codigoNivel);
// 						// Fim nivel
// 						$oXMLout->endElement();
						
						/*
						 * Início itens
						 * Revisão: 03/12/2013
						 */
						$oXMLout->startElement("itens");
						
							/*
							 * Início item
							 * Revisão: 03/12/2013 (_FALTA_)
							 */
							$unidadeMedida = '';
							if ($loteAtual['itens']["unidadeItem"] == 'UN') {
								$unidadeMedida = 35;
							}
							$oXMLout->startElement("item");
								$oXMLout->writeElement("descricao-detalhada", $loteAtual['itens']["descricaoItem"]);
								$oXMLout->writeElement("unidade-medida", $unidadeMedida);
								$oXMLout->writeElement("quantidade", $loteAtual['itens']["quantidadeItem"]);
								$oXMLout->writeElement("preco-unitario-estimado", $loteAtual['itens']["precoUnitarioItem"]);
								$oXMLout->writeElement("data-referencia", $loteAtual['itens']["dataReferencia"]);
// 								$oXMLout->writeElement("bdi", "NULL"); // Sempre NULL
								$oXMLout->writeElement("fonte-referencia-precos", $loteAtual['itens']["fonteReferenciaItem"]);
								$oXMLout->writeElement("codigo-referencia", $loteAtual['itens']["codigoReferenciaItem"]);
								/*
								 * Início niveis
								 * Revisão: 03/12/2013
								 */
								$oXMLout->startElement("niveis");
									$oXMLout->startElement("nivel");
										$oXMLout->writeElement("nome", $nomeNivel);
										$oXMLout->writeElement("codigo-nivel", $codigoNivel);
									// Fim nivel
									$oXMLout->endElement();
								$oXMLout->endElement();
								
								if($fase==13){
									/*
									 * Início licitante-habilitado
									* Revisão: 03/12/2013
									*/
									$oXMLout->startElement("licitante-habilitado");
									$oXMLout->writeElement("numero-documento", $loteAtual["licitanteHabilitado"]["numeroDocForn"]);
									if ($loteAtual["licitanteHabilitado"]["propostaValida"] == 'S') {
										$oXMLout->writeElement("proposta-valida", 1);
									} else {
										$oXMLout->writeElement("proposta-valida", 0);
									}
									$oXMLout->writeElement("valor-proposta", $loteAtual["licitanteHabilitado"]["valorPropostaLote"]);
									// Fim licitate-habilitado
									$oXMLout->endElement();
								} else {
									$oXMLout->writeElement("licitante-habilitado", "a"); // Sempre Nulo
								}
								
							// Fim item
							$oXMLout->endElement();
						// Fim itens
						$oXMLout->endElement();
						
					// Fim lote
					$oXMLout->endElement();
					}					
				// Fim lotes
				$oXMLout->endElement();
			// Fim orcamento
			$oXMLout->endElement();
		// Fim edital			
		$oXMLout->endElement();

		if($fase == 13){

		$oXMLout->startElement("licitantes");

			foreach($licitantesVencedores as $licitante){
			$oXMLout->startElement("licitante");
			
				$oXMLout->writeElement("numero-documento", $licitante["numeroDocForn"]);
				$oXMLout->writeElement("condicao", $condicaoHomologacao);
				$oXMLout->writeElement("municipioInscricaoMunicipal", $municipioInscMunicipal);
				$oXMLout->writeElement("numeroInscricaoEstadual", $licitante["numeroInscEstadual"]);
				$oXMLout->writeElement("numeroInscricaoMunicipal", $numeroInscMunicipal);
				$oXMLout->writeElement("tipoDocumento", $licitante["tipoDocumento"]);
				$oXMLout->writeElement("ramoAtividade", $licitante["ramoAtividade"]);
				$oXMLout->writeElement("razaoSocial", $licitante["razaoSocial"]);
				$oXMLout->writeElement("UFInscricaoEstadual", $ufInscEstadual);
				$oXMLout->writeElement("UFInscricaoMunicipal", $ufInscMunicipal);
			
			$oXMLout->endElement();
			}
		$oXMLout->endElement();
		
		$oXMLout->startElement("habilitacao");
			$oXMLout->writeElement("data-divulgacao-publicacao", $dataDivulgacao);

			foreach($licitantesVencedores as $licitanteHabilitacao){
			$oXMLout->startElement("licitante-habilitacao");
				$oXMLout->writeElement("numero-documento", $licitanteHabilitacao["numeroDocForn"]);
				$oXMLout->writeElement("habilitado", '01');
				
			$oXMLout->endElement();
			}
		$oXMLout->endElement();
		
		$oXMLout->startElement("julgamento");
			$oXMLout->writeElement("data-divulgacao-publicacao", $dataDivulgacao);
		$oXMLout->endElement();
		
		/*
		 * Início adjudicacao-homologacao
		 * Revisão: 03/12/2013
		 */
		$oXMLout->startElement("adjudicacao-homologacao");
			$oXMLout->writeElement("data-publicacao", $dataHomologacao);
			$oXMLout->writeElement("justificativa", "NULO"); // Sempre NULO
			// Verifica se a licitação possui vencedores
			// Caso possua vencedores, o valor deve ser 00
			// Caso não possua, o valor deve ser 01
			if (isset($licitantesVencedores) && !empty($licitantesVencedores)) {
				$oXMLout->writeElement("fracassada", "00");
			} else {
				$oXMLout->writeElement("fracassada", "01");
			}

			if (isset($licitantesVencedores) && !empty($licitantesVencedores)) {
				// Calcula a quantidade de itens dos vencedores
				$quantidadeItem = 0;
				// Verifica se a quantidade de itens não está vazia
				if (isset($arrayItens["quantidadeItem"]) && !empty($arrayItens["quantidadeItem"])) {
					$quantidadeItem = $arrayItens["quantidadeItem"];
				}
				
				// Calcula o valor total do prgão
				$precoUnitarioItem = 0;
				// Verifica se o preço unitário do item não está vazia
				if (isset($arrayItens["precoUnitarioItem"]) && !empty($arrayItens["precoUnitarioItem"])) {
					$precoUnitarioItem = $arrayItens["precoUnitarioItem"];
					
					$valorTotalItem = $quantidadeItem * $precoUnitarioItem;
				}
				$valorTotalPregao = $valorTotalPregao + $valorTotalItem;
	
				/*
				 * Início vencedores
				 * Revisão: 03/12/2013
				 */
				$oXMLout->startElement("vencedores");
				$cpfCnpjFornecedor = '';
				foreach ($licitantesVencedores as $vencedor){
					// Verifica se o cpf/cnpj do fornecedor não está vazio
					if (!empty($licitantesVencedores)) {
						$cpfCnpjFornecedor = key($licitantesVencedores);
					}

				   /*
					* Início vencedor
					* Revisão: 03/12/2013
					*/
					$oXMLout->startElement("vencedor");
						$oXMLout->writeElement("cpf-cnpj-vencedor", $cpfCnpjFornecedor);
// 						$oXMLout->writeElement("bdi", "NULO"); // Sempre Nulo
						$oXMLout->writeElement("quantidade", $quantidadeItem);
						$oXMLout->writeElement("preco-unitario", $precoUnitarioItem);
						$oXMLout->writeElement("percentual-desconto", $percentualDesconto);
// 						if (isset($valorTotalPregao) && !empty($valorTotalPregao)) {
// 							$oXMLout->writeElement("preco-total-pregao", $valorTotalPregao);
// 						}
						//FALTA DEFINIR ESSE ITEM NA ESPECIFICAçÂO
						/*
						 * Início proposta-item
						 * Revisão: 03/12/2013
						 */
						$oXMLout->startElement("proposta-item");
							$oXMLout->writeElement("preco-unitario", $precoUnitarioItem);
						// Fim proposta-item
						$oXMLout->endElement();
					// Fim vencedor
					$oXMLout->endElement();
				}
				// Fim vencedores
				$oXMLout->endElement();

			} else {
// 				$oXMLout->writeElement("vencedores", "NULO"); // Sempre Nulo
			}

		// Fim adjudicacao-homologacao
		$oXMLout->endElement();
		}
		
		
		$oXMLout->writeElement("estagio-licitacao", $estagioLic);
		$oXMLout->writeElement("situacao-licitacao", $situacaoLic);
		$oXMLout->writeElement("unidade-gestora", $unidadeGestora);
	$oXMLout->endElement();//final de <processo>
	$oXMLout->endDocument();
	$oXMLout->flush();
	
	print $oXMLout->outputMemory();
	
	//fechando as conexões com os bancos
	$db->disconnect();

	
}//final da função que cria o xml

/**
 * Método que irá exportar uma licitação com suas informações no formato XML pré-definido.
 * Todas as validações relativas à licitação devem ser feitas pelos programas que invocam
 * esse método.
 * 
 * @param int $numeroLic 	- número da licitação (CLICPOPROC)
 * @param int $anoLic 		- ano da licitação (ALICPOANOP)
 * @param int $comissaoLic 	- código da comissão da licitação (CCOMLICODI)
 * @param int $grupoLic 	- código do grupo da licitação (CGREMPCODI)
 * @param int $orgaoLic		- código do órgão da licitação (CORGLICODI)
 * @param int $fase			- código da fase de licitação (CFASESCODI)
 * @param int $modalidade	- código da modalidade
 */
function exportarDadosTCE($numeroLic, $anoLic, $comissaoLic, $grupoLic, $orgaoLic, $fase, $modaldiade=null){

	// Cria o XML
	criarXML($numeroLic, $anoLic, $comissaoLic, $grupoLic, $orgaoLic, $fase, $modaldiade);

	// Inicializa a conexão
	$db = Conexao();

	/*criação do pdf do edital da licitação*/
	/*
	* TODO
	* Caminho apenas para teste
	* Descomentar ao passar para produção
	*/
	// $caminhoDiretorioEdital = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/exportacao_TCE/documentos";
	$caminhoDiretorioEdital = "exportacao_TCE/documentos/";

	if(!is_dir($caminhoDiretorioEdital)){
		mkdir($caminhoDiretorioEdital, 0777, true);
	}
	
	$sqlTitulo 	= "SELECT CCENPOCORG, CCENPOUNID FROM SFPC.TBCENTROCUSTOPORTAL WHERE CORGLICODI = $orgaoLic LIMIT 1";
	$resTitulo 	= resultLinhaUnica(executarSQL($db, $sqlTitulo));	
	$codOrgPCR	= str_pad($resTitulo[0], 2, "000", STR_PAD_LEFT);
	$codUndPCR	= str_pad($resTitulo[1], 2, "000", STR_PAD_LEFT);
	
	$tituloEditais 		= "DOC".$_SESSION['_cgrempcodi_']."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic."_".$comissaoLic."_".$orgaoLic."_";
	$tituloEditalTCE	= "EDITAL_E_OUTROS_".$codOrgPCR."_".$codUndPCR."_".$comissaoLic."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic.".pdf";
	
	if (isset($_SESSION['_cgrempcodi_']) && !empty($_SESSION['_cgrempcodi_'])) {
		$cgrempcodiVal = $_SESSION['_cgrempcodi_'];
	} else {
		$cgrempcodiVal = '1';
	}
	
	
	if((isset($numeroLic) && !empty($numeroLic)) && (isset($anoLic) && !empty($anoLic)) && (isset($cgrempcodiVal) && !empty($cgrempcodiVal)) && (isset($comissaoLic) && !empty($comissaoLic)) && (isset($orgaoLic) && !empty($orgaoLic))) {
		/*verificando os editais da licitação que estão no formato pdf*/
		$sqlPdfEdital = "select distinct cdoclicodi, edoclinome
				   		 from 	sfpc.tbdocumentolicitacao 
				   		 where 	edoclinome ilike '%.pdf' and clicpoproc = $numeroLic and alicpoanop = $anoLic and fdocliexcl <> 'S' and
								cgrempcodi = ".$cgrempcodiVal." and ccomlicodi = $comissaoLic and corglicodi = $orgaoLic 	
				   		 order by cdoclicodi asc";
		
		$resultPdfEdital = $db->query($sqlPdfEdital);
	} else {
		$sqlPdfEdital = false;
	}
		
	$arrEditalArq = array();
	//resetArquivoAcesso();

	if ($sqlPdfEdital != false) {
		/*jogando os arquivos em um array para concatenação dos mesmos*/
		if($resultPdfEdital->numRows() > 0){
			while($pdfEdital = $resultPdfEdital->fetchRow()){
				/*
				 * TODO 
				 * Caminho usado apenas para teste
				 * Descomentar antes de ir para produção
				 * 
				 * $arrEditalArq = array($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE1_0001_2013_2_39_1_1", $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/ATASFASE1_0001_2013_2_39_1_1");
				 */
				if (file_exists($GLOBALS["CAMINHO_UPLOADS"]."licitacoes/".$tituloEditais.$pdfEdital[0])) {
					$arrEditalArq[] = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/".$tituloEditais.$pdfEdital[0];
				}
				// $arrEditalArq[] = "exportacao_TCE/documentos/".$tituloEditais.$pdfEdital[0];
			}
		}
	}
	
	if(!empty($arrEditalArq)){
		concat($arrEditalArq, $caminhoDiretorioEdital.$tituloEditalTCE);
	}
	/*fim da criação do arquivo com os editais da licitação*/

	// Ata de habilitação
	$tituloAtaHabTCE = '';
	// Ata de julgamento
	$tituloAtaJulgTCE = '';
	
	/*criação dos pdfs da atas da licitação*/
	if($fase == 13){
		
		$tituloAtaHabTCE 	= "ATAFASEHABILITACAO_".$codOrgPCR."_".$codUndPCR."_".$comissaoLic."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic.".pdf";
		$tituloAtaJulgTCE 	= "ATAFASEJULGAMENTO_".$codOrgPCR."_".$codUndPCR."_".$comissaoLic."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic.".pdf";
		$tituloAtaAdjuTCE 	= "ATAFASEHOMOLOGACAO_".$codOrgPCR."_".$codUndPCR."_".$comissaoLic."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic.".pdf";
		
		$tituloAtas = "ATASFASE".$_SESSION['_cgrempcodi_']."_".str_pad($numeroLic, 4, "0000", STR_PAD_LEFT)."_".$anoLic."_".$comissaoLic."_".$orgaoLic."_";
		
		$sqlPdfAtaHab = "select distinct catasfcodi, eatasfnome from 	sfpc.tbatasfase 
					  	 where 	eatasfnome ilike '%.pdf' and cfasescodi = 4 and clicpoproc = $numeroLic and alicpoanop = $anoLic and 
								cgrempcodi = ".$_SESSION['_cgrempcodi_']." and ccomlicodi = $comissaoLic and corglicodi = $orgaoLic and fatasfexcl <> 'S'
					   	 order by catasfcodi asc";
		$resultPdfAtaHab  = $db->query($sqlPdfAtaHab);
		
		$arrayAtasHab = array();
		
		if($resultPdfAtaHab->numRows() > 0){
			while($pdfAtaHab = $resultPdfAtaHab->fetchRow()){
				/*
				* TODO
				* Caminho apenas para teste
				* Descomentar ao passar para produção
				*/
				// $arrayAtasHab[] = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/".$tituloAtas."4_".$pdfAtaHab[0];
				$arrayAtasHab[] = "exportacao_TCE/documentos/".$tituloAtas."4_".$pdfAtaHab[0];
			}
		}
		
		$sqlPdfAtaJulg = "select distinct catasfcodi, eatasfnome from 	sfpc.tbatasfase 
						  where eatasfnome ilike '%.pdf' and cfasescodi = 8 and clicpoproc = $numeroLic and alicpoanop = $anoLic and 
								cgrempcodi = ".$_SESSION['_cgrempcodi_']." and ccomlicodi = $comissaoLic and corglicodi = $orgaoLic and fatasfexcl <> 'S'
						  order by catasfcodi asc";
		$resultPdfAtaJulg  = $db->query($sqlPdfAtaJulg);
				
		$arrayAtasJulg = array();
		
		if($resultPdfAtaJulg->numRows() > 0){
			while($pdfAtaJulg = $resultPdfAtaJulg->fetchRow()){
				/*
				* TODO
				* Caminho apenas para teste
				* Descomentar ao passar para produção
				*/
				// $arrayAtasJulg[] = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/".$tituloAtas."8_".$pdfAtaJulg[0];
				$arrayAtasJulg[] = "exportacao_TCE/documentos/".$tituloAtas."8_".$pdfAtaJulg[0];
			}
		}
		
		$sqlPdfAtaAdj = "select distinct catasfcodi, eatasfnome from 	sfpc.tbatasfase 
						 where 	eatasfnome ilike '%.pdf' and cfasescodi = 13 and clicpoproc = $numeroLic and alicpoanop = $anoLic and 
								cgrempcodi = ".$_SESSION['_cgrempcodi_']." and ccomlicodi = $comissaoLic and corglicodi = $orgaoLic and fatasfexcl <> 'S'
						 order by catasfcodi asc";
		$resultPdfAtaAdj  = $db->query($sqlPdfAtaAdj);
		
		$arrayAtasAdj = array();
		
		if($resultPdfAtaAdj->numRows() > 0){
			while($pdfAtaAdj = $resultPdfAtaAdj->fetchRow()){
				/*
				* TODO
				* Caminho apenas para teste
				* Descomentar ao passar para produção
				*/
				$arrayAtasAdj[] = $GLOBALS["CAMINHO_UPLOADS"]."licitacoes/".$tituloAtas."13_".$pdfAtaAdj[0];
				// $arrayAtasAdj[] = "exportacao_TCE/documentos/".$tituloAtas."13_".$pdfAtaAdj[0];
			}
		}

		if(!empty($arrayAtasAdj)){
			concat($arrEditalAdj, $caminhoDiretorioEdital.$tituloAtaAdjuTCE);
		}
		
		if(!empty($arrayAtasHab)){
			concat($arrEditalHab, $caminhoDiretorioEdital.$tituloAtaHabTCE);
		}
		
		if(!empty($arrayAtasJulg)){
			concat($arrEditalJulg, $caminhoDiretorioEdital.$tituloAtaJulgTCE);
		}

	}

	sendSoap('00000000000', '123456', $unidadeGestora, $nomeXML, $caminhoDiretorioEdital.$tituloEditalTCE, $tituloAtaHabTCE, $tituloAtaJulgTCE);
	
}

/*
* TODO
* Corrigir envio SOAP
*/
function sendSoap($cpf = '', $senha = '', $unidade = '', $arquivoXML = '', $anexoEdital = '', $ataHabilitacao = '', $ataJulgamento = '') {

// 	$auth->username = '03031608453';
// 	$auth->password = '03031608453';
	
	$client = new SoapClient('http://www2.tce.pe.gov.br:7070/liconService/LiconImportacaoService?wsdl');
	
	$function = 'importaProcessoLicitatorio';
	$arguments= array('importaProcessoLicitatorio' => array(
		'cpf' => '03031608453', // $cpf
		'senhaCripto' => '03031608453', // $senha
		'unidadeGestora' => '120.001', //$unidade, // 120.001
		'arquivoXml' => $arquivoXML,
		'anexoEdital' => '',
		'ataHabilitacao' => '',
		'ataJulgamento' => '',
	));
	$options = array(
		'location' => 'http://www2.tce.pe.gov.br:7070/liconService/LiconImportacaoService'
	);
	
	$result = $client->__soapCall($function, $arguments, $options);

	echo 'Response: ';
	print_r($result);
	
	die('oi');

}

// Verifica se os dados do processo foram passados como parâmetro
if (isset($_POST['LicitacaoProcesso'])) {
	// Armazena o valor dos dados da licitação
	$dadosLicitacao = $_POST['LicitacaoProcesso'];
	// Separa os dados da licitação 
	$dadosLicitacaoArray = explode('_', $dadosLicitacao);

	$cfasescodi = $dadosLicitacaoArray[5]; // Código da Fase do Processo Licitatório
	$clicpoproc = $dadosLicitacaoArray[0]; // Código do Processo Licitatório
	$alicpoanop = $dadosLicitacaoArray[1]; // Ano do Processo Licitatório
	$cgrempcodi = $dadosLicitacaoArray[3]; // Código do Grupo
	$ccomlicodi = $dadosLicitacaoArray[2]; // Código da Comissão
	$corglicodi = $dadosLicitacaoArray[4]; // Código do Órgão Licitante
	$cmodlicodi = $dadosLicitacaoArray[6]; // Código da Modalidade
	
	// criarXML($clicpoproc, $alicpoanop, $ccomlicodi, $cgrempcodi, $corglicodi, $cfasescodi, $cmodlicodi);
	exportarDadosTCE($clicpoproc, $alicpoanop, $ccomlicodi, $cgrempcodi, $corglicodi, $cfasescodi, $cmodlicodi);

} else {
	
}

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
<form action="CadTCEXML.php" method="post" name="Documento">
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
             	Dados exportados com sucesso!
             </p>
          </td>
        </tr>
        <tr>
          <td class="textonormal" align="right">
             <input type="submit" value="Voltar" class="botao">
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