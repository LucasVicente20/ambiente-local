<?php
#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsAcompGerencialInformacoesImpressao.php
# Autor:    Igor Duarte
# Data:     02/07/2013
# Objetivo: Programa de Consulta Acompanhamento Gerencial de Informações com geração de planilha eletrônica
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------

function formatarNumSCC($num1, $num2, $num3, $num4){
	$num1 = str_pad($num1, 2, "0000", STR_PAD_LEFT);
	$num2 = str_pad($num2, 2, "0000", STR_PAD_LEFT);
	$num3 = str_pad($num3, 4, "0000", STR_PAD_LEFT);
	$num4 = str_pad($num4, 4, "0000", STR_PAD_LEFT);

	return "$num1$num2.$num3.$num4";
}

function data_diff($data1){
	$start = new DateTime($data1);
	$end = new DateTime();

	return abs(round(($end->format('U') - $start->format('U')) / (60*60*24)));
}

function data_diff2($data1, $data2){
	$start = new DateTime($data1);
	$end = new DateTime($data2);

	return abs(round(($end->format('U') - $start->format('U')) / (60*60*24)));
}

# Acesso ao arquivo de funções #
require_once("../compras/funcoesCompras.php");
require_once("funcoesComplementaresLicitacao.php");
require_once("../fornecedores/funcoesFornecedores.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/ConsAcompGerencialInformacoesImpressao.php' );
AddMenuAcesso( '/licitacoes/ConsAcompGerencialInformacoesPesquisar.php' );

// Definimos o nome do arquivo que será exportado
$arquivo = 'AcompanhamentoGerencialdeInformacoes.xls';

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$Critica          	= $_POST['Critica'];
	$Botao            	= $_POST['Botao'];
	$Grupo				= $_POST['Grupo'];
	$Comissao			= $_POST['Comissao'];
	$Modalidade			= $_POST['Modalidade'];
	$Fase				= $_POST['Fase'];
	$Ordenacao			= $_POST['Ordenacao'];
}
else{
	$Grupo				= $_GET['Grupo'];
	$Comissao			= $_GET['Comissao'];
	$Modalidade			= $_GET['Modalidade'];
	$Fase             	= $_GET['Fase'];
	$Ano              	= $_GET['Ano'];
	$Ordenacao			= $_GET['Ordenacao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAcompGerencialInformacoesResultado.php";

# Redireciona dados para ConsAcompGerencialInformacoesPesquisar.php #
if( $Botao == "Pesquisa" ){
	$Url = "ConsAcompGerencialInformacoesPesquisar.php";
	if (!in_array($Url,$_SESSION['GetUrl'])){
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit();
}

$Mens = 0;
if( $Mens == 0 ) {
	$db   = Conexao();

	$select = "SELECT DISTINCT
					LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CGREMPCODI, LIC.CORGLICODI, LIC.CCOMLICODI, --CHAVE DA LICITACAO 0 1 2 3 4
					LIC.CLICPOCODL, LIC.ALICPOANOL, --LICITACAO/ANO 5 6
					ORG.EORGLIDESC, --ORGAO 7
					COM.ECOMLIDESC, --COMISSAO 8
					MOD.EMODLIDESC, --MODALIDADE 9
					LIC.XLICPOOBJE, --OBJETO 10
					LIC.FLICPOREGP, --REGISTRO DE PRECO 11
					LIC.TLICPOULAT, USU.EUSUPORESP, --CADASTRAMENTO/ALTERACAO LICITACAO: DATA & USUARIO 12 13
					LIC.TLICPODHAB, --ABERTURA: DATA & HORA 14
					(SELECT SUM(ILP.AITELPQTSO * ILP.VITELPUNIT) 
					 FROM SFPC.TBITEMLICITACAOPORTAL ILP
					 WHERE ILP.CLICPOPROC = LIC.CLICPOPROC AND ILP.ALICPOANOP = LIC.ALICPOANOP 
					       AND ILP.CGREMPCODI = LIC.CGREMPCODI AND ILP.CCOMLICODI = LIC.CCOMLICODI 
					       AND ILP.CORGLICODI = LIC.CORGLICODI ) AS VALORESTIMADO, -- VALOR ESTIMADO 15
					(SELECT SUM(ILC.AITELPQTSO * ILC.VITELPVLOG ) 
					 FROM SFPC.TBITEMLICITACAOPORTAL ILC
					 WHERE ILC.CMOTNLSEQU IS NULL AND ILC.CLICPOPROC = LIC.CLICPOPROC 
					 	   AND ILC.ALICPOANOP = LIC.ALICPOANOP  AND ILC.CGREMPCODI = LIC.CGREMPCODI 
					 	   AND ILC.CCOMLICODI = LIC.CCOMLICODI  AND ILC.CORGLICODI = LIC.CORGLICODI ) AS VALORHOMOLOGADO --VALOR HOMOLOGADO 16      
				";

	$from = "FROM
				SFPC.TBLICITACAOPORTAL LIC
				JOIN SFPC.TBORGAOLICITANTE ORG ON LIC.CORGLICODI = ORG.CORGLICODI
				JOIN SFPC.TBCOMISSAOLICITACAO COM ON LIC.CCOMLICODI = COM.CCOMLICODI
				JOIN SFPC.TBUSUARIOPORTAL USU ON LIC.CUSUPOCODI = USU.CUSUPOCODI
				JOIN SFPC.TBMODALIDADELICITACAO MOD ON MOD.CMODLICODI = LIC.CMODLICODI
				";

	$where = "WHERE
				(LIC.ALICPOANOP = $Ano)";

	if(!empty($Comissao)){
		$where .= "	AND (LIC.CCOMLICODI = $Comissao)";
	}
	if(!empty($Grupo)){
		$where .= "	AND (LIC.CGREMPCODI = $Grupo)";
	}
	if(!empty($Modalidade)){
		$where .= "	AND (LIC.CMODLICODI = $Modalidade)";
	}

	$tipoOrdenacao = ($Ordenacao == 1)?"ORG.EORGLIDESC ASC, ":"COM.ECOMLIDESC ASC, ";

	$order = "
			  ORDER BY
			$tipoOrdenacao LIC.CLICPOPROC, LIC.ALICPOANOP, LIC.CGREMPCODI, LIC.CORGLICODI, LIC.CCOMLICODI, ORG.EORGLIDESC ASC, COM.ECOMLIDESC ASC
			 ";

	$consulta = $select.$from.$where.$order;

	//var_dump($consulta); die();

	if( $ModalidadeCodigo != "" ) {
		$sql .= " AND a.CMODLICODI = $ModalidadeCodigo ";
	}

	if( $ComissaoCodigo != "" ){
		$sql .= " AND a.CCOMLICODI = $ComissaoCodigo ";
	}

	if( $ModalidadeCodigo != "" ){
		$sql .= " AND a.CMODLICODI = $ModalidadeCodigo ";
	}

	if( $GrupoCodigo != "" ){
		$sql .= " AND a.CGREMPCODI = $GrupoCodigo ";
	}

	$result = $db->query($consulta);

	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $consulta");
	}
	else{
		$TotalProcessos = $result->numRows();
	}

	$GrupoDescricao = "";

	if( $TotalProcessos != 0){
		$html  = '<table border=\"1\">'; 
		$html .= '<tr>';
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">";
		
		if($Ordenacao == 1){
			$html .= "ÓRGÃO DEMANDANTE </td>";
		} 
		else { 
			$html .= "COMISSÃO DE LICITAÇÃO </td>";
		}
		
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"5\">SCC</td>";
	    $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">";
	    
	    if($Ordenacao == 1){ 
	    	$html .= "COMISSÃO DE LICITAÇÃO</td>"; 
	    }
	    else { 
	    	$html .= "ÓRGÃO DEMANDANTE</td>"; 
	   	}
		
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">PROCESSO/ANO</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">OBJETO</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">DATA FASE INTERNA</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">MODALIDADE</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">LICITAÇÃO/ANO</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">REGISTRO DE PREÇO</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">DOTAÇÃO ORÇAMENTÁRIA / BLOQUEIO</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">VALOR ESTIMADO (R$)</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"2\">CADASTRAMENTO/ALTERAÇÃO LICITAÇÃO</td>";
	    $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"2\">FASE PUBLICAÇÃO</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"2\">ABERTURA</td>";
	    $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">DIAS NA CPL</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"3\">HISTÓRICO</td>";
        $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"3\">LICITANTE VENCEDORA</td>";
	    $html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" rowspan=\"2\">VALOR HOMOLOGADO (R$)</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"center\" colspan=\"2\">ECONOMIA</td></tr>";
								        		
		$html .= '<tr>
					<td bgcolor="#DCEDF7" class="titulo3" align="center">NÚMERO</td>
		        	<td bgcolor="#DCEDF7" class="titulo3" align="center">DATA CADASTRAMENTO</td>
        			<td bgcolor="#DCEDF7" class="titulo3" align="center">DATA ANÁLISE</td>
        			<td bgcolor="#DCEDF7" class="titulo3" align="center">DIAS ANÁLISE</td>
        			<td bgcolor="#DCEDF7" class="titulo3" align="center">OBSERVAÇÃO</td>
        			<td bgcolor="#DCEDF7" class="titulo3" align="center">DATA</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">USUÁRIO</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DATA</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DETALHE</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DATA</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">HORA</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">FASE</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DATA</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DETALHE</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">DENOMINAÇÃO</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">CNPJ</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">EPP/MICRO</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">VALOR (R$)</td>
			        <td bgcolor="#DCEDF7" class="titulo3" align="center">%</td>
			      </tr>';
									        	
        $compTemp = 0;
        $totalOrgaoComissao = 0;
        $imprimirTotal = false;
        	
        while($Linha = $result->fetchRow()){
        	
        	if($TotalProcessos == 0){
        		//Envia mensagem para página selecionar #
        		$Mensagem = urlencode("Nenhuma ocorrência foi encontrada");
        		$Url = "ConsAcompGerencialInformacoesPesquisar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
        	
        		if (!in_array($Url,$_SESSION['GetUrl'])){
        			$_SESSION['GetUrl'][] = $Url;
        		}
        		header("location: ".$Url);
        		exit();
        	}
        	
			if($Ordenacao == 1){
        		if($compTemp == 0){
        			$compTemp = $Linha[3];
        		}
        		else{
        			if($compTemp != $Linha[3]){
        				$compTemp = $Linha[3];
        				$imprimirTotal = true;
        			}
        		}
        	}
        	else{
        		if($compTemp == 0){
        			$compTemp = $Linha[4];
        		}
        		else{
        			if($compTemp != $Linha[4]){
        				$compTemp = $Linha[4];
        				$imprimirTotal = true;
        			}
        		}	
        	}
        	
        	if($imprimirTotal && $totalOrgaoComissao > 0){
        		$imprimirTotal = false;				
        		
        		$html .= "<tr>";
				$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"2\">TOTAL PROCESSOS</td>";
				$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"29\">".$totalOrgaoComissao."</td></tr>";
				
				$totalOrgaoComissao = 0;
			}
        	
			$totalOrgaoComissao++;
			
			$db = Conexao();
			$dbOracle = ConexaoOracle();
							        			
			//Dados referentes as fases da licitação
			$sqlFasePublicacao = "SELECT	FASE.TFASELDATA, FASE.EFASELDETA
								  FROM 		SFPC.TBFASELICITACAO FASE
							      WHERE 	FASE.CFASESCODI = 2 AND FASE.CLICPOPROC = $Linha[0] 
							      			AND FASE.ALICPOANOP = $Linha[1] AND FASE.CGREMPCODI = $Linha[2] 
							        		AND FASE.CCOMLICODI = $Linha[4] AND FASE.CORGLICODI = $Linha[3] 
							      ORDER BY FASE.TFASELDATA DESC LIMIT 1";
							        			
			$sqlDiasCPL = "SELECT 	FASE.TFASELDATA
						   FROM 	SFPC.TBFASELICITACAO FASE
						   WHERE 	FASE.CFASESCODI = 1 AND FASE.CLICPOPROC = $Linha[0] 
									AND FASE.ALICPOANOP = $Linha[1] AND FASE.CGREMPCODI = $Linha[2] 
							        AND FASE.CCOMLICODI = $Linha[4] AND FASE.CORGLICODI = $Linha[3] 
						   ORDER BY FASE.TFASELDATA DESC LIMIT 1"; 
							        			
        	$sqlHistoricoFases = "SELECT 	FASE.TFASELDATA, FASE.EFASELDETA, FA.EFASESDESC, FASE.CFASESCODI 
        						  FROM 		SFPC.TBFASELICITACAO FASE JOIN SFPC.TBFASES FA ON FA.CFASESCODI = FASE.CFASESCODI
        						  WHERE 	FASE.CLICPOPROC = $Linha[0]  AND FASE.ALICPOANOP = $Linha[1] AND FASE.CGREMPCODI = $Linha[2] 
        									AND FASE.CCOMLICODI = $Linha[4] AND FASE.CORGLICODI = $Linha[3] 
								  ORDER BY 	FASE.TFASELDATA DESC";
        			
        	$resultHistoricoFases 	= $db->query($sqlHistoricoFases);
        	$resultFasePublicacao 	= resultLinhaUnica(executarSQL($db, $sqlFasePublicacao));
        	$resultDiasCPL 			= resultValorUnico(executarSQL($db, $sqlDiasCPL));
							        			
        	$numSCC = "	SELECT 	COUNT(SOLC.CSOLCOSEQU)
        			   	FROM 	SFPC.TBSOLICITACAOLICITACAOPORTAL SOLC 
        				WHERE	SOLC.CLICPOPROC = $Linha[0]  AND SOLC.ALICPOANOP = $Linha[1]  
        						AND SOLC.CGREMPCODI = $Linha[2] AND SOLC.CCOMLICODI = $Linha[4] 
        					    AND SOLC.CORGLICODI = $Linha[3]";
        	
        	$qteSCC = resultValorUnico(executarSQL($db, $numSCC));
        			
        	if($qteSCC != null & $qteSCC > 0){

        		//informações da SCC
        		$sqlSCC = "SELECT	SOLC.CSOLCOSEQU, CET.CCENPOCORG, CET.CCENPOUNID, SOLC.CSOLCOCODI, SOLC.ASOLCOANOS, SOLC.TSOLCODATA
        					FROM	SFPC.TBSOLICITACAOCOMPRA SOLC
        							JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL SOLP ON SOLC.CSOLCOSEQU = SOLP.CSOLCOSEQU
        							JOIN SFPC.TBCENTROCUSTOPORTAL CET ON CET.CCENPOSEQU = SOLC.CCENPOSEQU
        					WHERE 	SOLP.CLICPOPROC = $Linha[0] AND SOLP.ALICPOANOP = $Linha[1] AND SOLP.CGREMPCODI = $Linha[2]  
        		    				AND SOLP.CCOMLICODI = $Linha[4] AND SOLP.CORGLICODI = $Linha[3]
        		    		ORDER BY SOLC.CSOLCOSEQU ASC, CET.CCENPOCORG DESC, CET.CCENPOUNID DESC";
        			
        		//var_dump($sqlSCC);
        		$resultSCC = $db->query($sqlSCC);
							        	
        		$dadosSCC = array();
        	
        		if($resultSCC->numRows() > 0){
        			while($linhaSCC = $resultSCC->fetchRow()){
        				$sqlSCC2 = "SELECT 	HIST.THSITSDATA, HIST.XHSITSOBSE, HIST.CSOLCOSEQU
        							FROM	SFPC.TBHISTSITUACAOSOLICITACAO HIST 
        			        		WHERE	HIST.CSITSOCODI = 6
        			        				AND HIST.CSOLCOSEQU = $linhaSCC[0]
        							ORDER BY HIST.CSOLCOSEQU ASC, HIST.THSITSDATA DESC LIMIT 1"; 
        	
        				$sqlSCC3 = "SELECT	HIST.THSITSDATA, HIST.CSOLCOSEQU
        			   	        	FROM 	SFPC.TBHISTSITUACAOSOLICITACAO HIST 
        			   				WHERE 	HIST.CSITSOCODI = 8
        			   	        			AND HIST.CSOLCOSEQU = $linhaSCC[0]
        							ORDER BY HIST.CSOLCOSEQU ASC, HIST.THSITSDATA DESC LIMIT 1"; 
        	
        				$resultSCC2 = resultLinhaUnica(executarSQL($db, $sqlSCC2));
        				$resultSCC3 = resultLinhaUnica(executarSQL($db, $sqlSCC3));
        	
        				$dataCadastramentoSCC1 = explode(" ", $linhaSCC[5]);
		        		$dataCadastramentoSCC2 = explode("-", $dataCadastramentoSCC1[0]);
		        		$dataCadastramentoSCC2 = $dataCadastramentoSCC2[2]."/".$dataCadastramentoSCC2[1]."/".$dataCadastramentoSCC2[0];
		        	
		        		$dataAnaliseSCC1 = explode(" ", $resultSCC2[0]);
		        		$dataAnaliseSCC2 = explode("-", $dataAnaliseSCC1[0]);
		        		$dataAnaliseSCC2 = $dataAnaliseSCC2[2]."/".$dataAnaliseSCC2[1]."/".$dataAnaliseSCC2[0];
		        	
		        		$diasAnaliseSCC1 = explode(" ", $resultSCC3[0]);
		        	
		        		//var_dump($linhaSCC[0]); var_dump($dataCadastramentoSCC2); var_dump($dataAnaliseSCC2); var_dump($resultSCC3[0]);
		        	
		        		$dadosSCC[] = array("Numero" => formatarNumSCC($linhaSCC[1], $linhaSCC[2], $linhaSCC[3], $linhaSCC[4]),
        									"DataCadastramento" => $dataCadastramentoSCC2,
        									"DataAnalise" => (is_null($resultSCC2) || empty($resultSCC2) || !isset($resultSCC2))?"":$dataAnaliseSCC2,
        									"DiasAnalise" => (is_null($resultSCC2) || empty($resultSCC2) || !isset($resultSCC2))?"":((data_diff2($dataAnaliseSCC1[0], $diasAnaliseSCC1[0]) == 0)?1:data_diff2($dataAnaliseSCC1[0], $diasAnaliseSCC1[0])),
        									"Observacao" => (is_null($resultSCC2) || empty($resultSCC2) || !isset($resultSCC2))?"":$resultSCC2[1]); 
		        		}
		        	}
		        }

		        //DOTACAO-BLOQUIEO
				if($Linha[11]=="S"){
					//Faco a busca pelos campos de Dotação AITCDOUNIDOEXER CITCDOUNIDOORGA CITCDOUNIDOCODI CITCDOTIPA AITCDOORDT  CITCDOELE1, CITCDOELE2, CITCDOELE3, CITCDOELE4, CITCDOFONT
					$sqlDotacao = " SELECT aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt, citldoele1, citldoele2, citldoele3, citldoele4, citldofont
									FROM SFPC.tbitemlicitacaodotacao WHERE 
							        	clicpoproc = $Linha[0]
							        	AND alicpoanop = $Linha[1] 
							        	AND cgrempcodi = $Linha[2] 
							        	AND ccomlicodi = $Linha[4] 
							        	AND corglicodi = $Linha[3] ";
							        				
        			$resDotcao  = $db->query($sqlDotacao);
        			if( PEAR::isError($resDotcao) ){
        				$CodErroEmail  = $resDotcao->getCode();
        				$DescErroEmail = $resDotcao->getMessage();
        				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        			}
        			else{
        				$valorDotacaoBloqueio = array();
        				while ( $linhaDotacao = $resDotcao->fetchRow() ){
        					$dotacaoArray = getDadosDotacaoOrcamentariaFromChave($dbOracle,$linhaDotacao[0],$linhaDotacao[1],$linhaDotacao[2],$linhaDotacao[3],$linhaDotacao[4],$linhaDotacao[5],$linhaDotacao[6],$linhaDotacao[7],$linhaDotacao[8],$linhaDotacao[9]);
        					$valorDotacaoBloqueio[] = $dotacaoArray["dotacao"];
        				}
        			}
        		}
        		else{
        			//Faco a busca pelos campos de Bloqueio
        			$sqlDotacao = " SELECT AITLBLNBLOQ , AITLBLANOB
        							  		FROM  SFPC.TBITEMLICITACAOBLOQUEIO 
        							  		WHERE clicpoproc = $Linha[0]
        							  			AND alicpoanop = $Linha[1]
        							 			AND cgrempcodi = $Linha[2] 
        							  			AND ccomlicodi = $Linha[4] 
        							 			AND corglicodi = $Linha[3] ";
        				
        			$resDotacao  = $db->query($sqlDotacao);
        			if( PEAR::isError($resDotacao) ){
        				$CodErroEmail  = $resDotacao->getCode();
        				$DescErroEmail = $resDotacao->getMessage();
        				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
        			}
        			else{
        				$valorDotacaoBloqueio = array();
        				while ( $linhaDotacao = $resDotacao->fetchRow() ){
        					$dotacaoArray = getDadosBloqueioFromChave($dbOracle,$linhaDotacao[1],$linhaDotacao[0]);
        					$valorDotacaoBloqueio[] = $dotacaoArray["bloqueio"];
        				}
        			}
        		}

        		if(count($valorDotacaoBloqueio) > 1){
        			//var_dump($valorDotacaoBloqueio); die;
        			$valorDotacaoBloqueio = array_unique($valorDotacaoBloqueio);
        		}
        		//var_dump($valorDotacaoBloqueio);
        			
        		//informações do fornecedor licitante vencedor
        		$sqlLicitante = " SELECT DISTINCT
        										ITEM.CITELPNUML, FORN.NFORCRRAZS, FORN.AFORCRCCGC, FORN.FFORCRMEPP
        									FROM 
        										SFPC.TBITEMLICITACAOPORTAL ITEM
        										JOIN SFPC.TBFORNECEDORCREDENCIADO FORN ON FORN.AFORCRSEQU = ITEM.AFORCRSEQU
        									WHERE
        										ITEM.CMOTNLSEQU IS NULL
        										AND ITEM.CLICPOPROC = $Linha[0]
        										AND ITEM.ALICPOANOP = $Linha[1]
        										AND ITEM.CGREMPCODI = $Linha[2]
        										AND ITEM.CCOMLICODI = $Linha[4]
        										AND ITEM.CORGLICODI = $Linha[3]
        									ORDER BY
        										ITEM.CITELPNUML ASC ";
        			
        		$resultLicitante = $db->query($sqlLicitante);
        			
        		//Dados básicos
        		$abertura = explode(" ", $Linha[14]);
        		$dataAbertura = explode("-",$abertura[0]);
        		$dataAbertura = $dataAbertura[2]."/".$dataAbertura[1]."/".$dataAbertura[0];
        			
        		$dataFaseInterna = explode("-",$resultDiasCPL);
        		$dataFaseInterna = $dataFaseInterna[2]."/".$dataFaseInterna[1]."/".$dataFaseInterna[0];
        			
        		if(!is_null($resultFasePublicacao) && isset($resultFasePublicacao) && !empty($resultFasePublicacao)){
        			$dataFasePublicacao = explode("-", $resultFasePublicacao[0]);
        			$dataFasePublicacao = $dataFasePublicacao[2]."/".$dataFasePublicacao[1]."/".$dataFasePublicacao[0];
        			$detalheFasePublicacao = $resultFasePublicacao[1];
        		}
        		else{
        			$detalheFasePublicacao = "";
        			$dataFasePublicacao = "";
        		}
							        			
        		if(is_null($resultDiasCPL) || empty($resultDiasCPL) || !isset($resultDiasCPL)){
        			$diasCPL = "FASE INTERNA NÃO CADASTRADA";
        		}
        		else{
        			$diasCPL = data_diff($resultDiasCPL);
        		}
        			
        		$cadLicData = explode(" ", $Linha[12]);
        		$cadLicData = explode("-", $cadLicData[0]);
        		$cadLicData = $cadLicData[2]."/".$cadLicData[1]."/".$cadLicData[0];
        			
        		$economiaValor = $Linha[15]-$Linha[16];
        		
        		if($Linha[16] > 0){
        			$economiaValor = $Linha[15]-$Linha[16];
        			$economiaPorcentagem = ($Linha[15]-$Linha[16])*100/$Linha[15];
        		}
        		else{
        			$economiaValor = "0,00";
        			$economiaPorcentagem = "0,00";
        		}
        		
        		$processo = str_pad($Linha[0], 4, "0000", STR_PAD_LEFT);
        		$licitacao = str_pad($Linha[5], 4, "0000", STR_PAD_LEFT);
        		
        		$dadosBasicos = array(array("Orgao"				=> $Linha[7],
        										"Comissao" 				=> $Linha[8],
        										"ProcessoAno" 			=> $processo."/".$Linha[1],
        										"Objeto" 				=> $Linha[10],
        										"DataFaseInterna" 		=> (is_null($resultDiasCPL) || empty($resultDiasCPL))?"":$dataFaseInterna,
        										"Modalidade" 			=> $Linha[9],
        										"LicitacaoAno" 			=> $licitacao."/".$Linha[6],
        										"RegistroPreco" 		=> ($Linha[11] == 'S')?"SIM":"NÃO",
        										"DotacaoBloqueio" 		=> (isset($valorDotacaoBloqueio) && !empty($valorDotacaoBloqueio) && count($valorDotacaoBloqueio) > 1)?implode(" - ", $valorDotacaoBloqueio):((is_null($valorDotacaoBloqueio[0])?"":$valorDotacaoBloqueio[0]." &nbsp")),
        										"ValorEstimado" 		=> converte_valor_estoques($Linha[15]),
        										"CadLicUsuario" 		=> $Linha[13],
        										"CadLicData" 			=> $cadLicData,
        										"FasePublData" 			=> $dataFasePublicacao,
        										"FasePublDetalhe" 		=> $detalheFasePublicacao,
        										"AberturaData" 			=> $dataAbertura,
        										"AberturaHora" 			=> $abertura[1],
        										"DiasCPL" 				=> $diasCPL,
        										"ValorHomologado" 		=> converte_valor_estoques($Linha[16]),
        										"EconomiaValor" 		=> converte_valor_estoques($economiaValor),
        										"EconomiaPorcentagem" 	=> number_format($economiaPorcentagem, 2, ',','')
        		));
							        			
		        //informações sobre o historicos de fases
		        $historicoFases = array();
		        $flagFases = false;
		        $flagFases2 = false;
		        
		        if(($resultHistoricoFases->numRows()) > 0){
		        	while($linhaHistorico = $resultHistoricoFases->fetchRow()){
		        		$dataHistorico = explode("-", $linhaHistorico[0]);
		        		$dataHistorico = $dataHistorico[2]."/".$dataHistorico[1]."/".$dataHistorico[0];
		        	
		        		$historicoFases[] = array("Fase" => $linhaHistorico[2], "Data" => $dataHistorico, "Detalhe" => $linhaHistorico[1]);
		        		
		        		if($Fase == 1 && ($linhaHistorico[3] == 13 || $linhaHistorico[3] == 18)){
		        			//var_dump($sqlHistoricoFases); die;
		        			$flagFases = true;
		        			break;
		        		}
		        		
		        		if ($Fase == 2 && ($linhaHistorico[3] == 13 || $linhaHistorico[3] == 18) && !$flagFases2){
		        			$flagFases2 = true;
		        		}
		        	}
		        }
		        
		        if($Fase == 2 && !$flagFases2){
		        	$flagFases2 = false;
		        	$TotalProcessos--;
		        	$totalOrgaoComissao--;
		        	continue;
		        }
		        if($flagFases){
		        	$flagFases = false;
		        	$TotalProcessos--;
		        	$totalOrgaoComissao--;
		        	continue;
		        }
		        			
		        //dados licitante
		        $dadosLicitante = array();
		         
		        if($resultLicitante->numRows() > 0){
		        	while($linhaLicitante = $resultLicitante->fetchRow()){
		        		$dadosLicitante[] = array("Denominacao" => "LOTE ".$linhaLicitante[0]." - EMPRESA ".$linhaLicitante[1],
        										  "Cnpj"=> FormataCNPJ($linhaLicitante[2]),
		        									"EppMicro"=> getDescPorteEmpresa($linhaLicitante[3])  ) ;
//        										  "EppMicro"=> ($linhaLicitante[3] == "S")?"SIM":"NÃO");
		        	}
		        }
							        		
				$indMax = max(count($dadosLicitante), count($dadosSCC), count($historicoFases));
							        		
				if($Ordenacao == 1){
					$primeiro = $dadosBasicos[0]["Orgao"];
				}
				else{
					$primeiro = $dadosBasicos[0]["Comissao"];
				}
							        		
				$html .= "<tr><td rowspan=\"".$indMax."\"align=\"center\">".$primeiro."&nbsp</td>";
							        		
				for($i = 0; $i < $indMax; $i++){	
					if($Ordenacao == 1){
						$terceiro = $dadosBasicos[$i]["Comissao"];
					}
					else{
						$terceiro = $dadosBasicos[$i]["Orgao"];
					}

        			$html .= "<td align=\"center\">".$dadosSCC[$i]["Numero"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosSCC[$i]["DataCadastramento"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosSCC[$i]["DataAnalise"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosSCC[$i]["DiasAnalise"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosSCC[$i]["Observacao"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$terceiro."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["ProcessoAno"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["Objeto"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["DataFaseInterna"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["Modalidade"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["LicitacaoAno"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["RegistroPreco"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["DotacaoBloqueio"]."&nbsp</td>";
        			$html .= "<td align=\"right\">".$dadosBasicos[$i]["ValorEstimado"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["CadLicData"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["CadLicUsuario"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["FasePublData"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["FasePublDetalhe"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["AberturaData"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["AberturaHora"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosBasicos[$i]["DiasCPL"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$historicoFases[$i]["Fase"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$historicoFases[$i]["Data"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$historicoFases[$i]["Detalhe"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosLicitante[$i]["Denominacao"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosLicitante[$i]["Cnpj"]."&nbsp</td>";
        			$html .= "<td align=\"center\">".$dadosLicitante[$i]["EppMicro"]."&nbsp</td>";
        			$html .= "<td align=\"right\">".$dadosBasicos[$i]["ValorHomologado"]."&nbsp</td>";
        			$html .= "<td align=\"right\">".$dadosBasicos[$i]["EconomiaValor"]."&nbsp</td>";							        			
        			$html .= "<td align=\"right\">".$dadosBasicos[$i]["EconomiaPorcentagem"]."&nbsp</td></tr>";

				}
			}
		
		if($totalOrgaoComissao > 0){
			$html .= "<tr><td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"2\">TOTAL PROCESSOS</td>";
			$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"29\">".$totalOrgaoComissao."</td></tr>";
		}
		
		$html .= "<tr><td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"2\">TOTAL GERAL</td>";
		$html .= "<td bgcolor=\"#DCEDF7\" class=\"titulo3\" align=\"left\" colspan=\"29\">".$TotalProcessos."</td></tr>";
		
		$html = mb_convert_encoding($html, 'UTF-16LE', 'UTF-8');
		$html = "\xFF\xFE" . $html;
		
		// Configurações header para forçar o download
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Pragma: no-cache");
		header ("Content-type: application/x-msexcel ");
		//header('Content-type: application/x-msdownload; charset=utf-16');
		header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
		header ("Content-Description: PHP Generated Data" );
		
		// Envia o conteúdo do arquivo
		echo $html;
		exit;
	}
	else{
		# Envia mensagem para página selecionar #
		$Mensagem = urlencode("Nenhuma ocorrência foi encontrada");
		$Url = "ConsAcompGerencialInformacoesPesquisar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
		
		if (!in_array($Url,$_SESSION['GetUrl'])){ 
			$_SESSION['GetUrl'][] = $Url; 
		}
		header("location: ".$Url);
		exit();
	}
	
	$db->disconnect();
	$dbOracle->disconnect();
}
?>