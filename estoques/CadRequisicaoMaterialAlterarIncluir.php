<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Autor: Roberta Costa
 * Data:  12/09/2005
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Marcus Thiago
 * Data:     12/01/2006
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     24/11/2006
 * Objetivo: Padronização das variáveis de requisição
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     12/08/2008
 * Objetivo: Tratamento de erro quando um item for especificado com valor 0
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     24/04/2009
 * Objetivo: Alterando a alteração de requisição de material para que seja possível incluir e excluir itens
 *           na mesma funcionalidade
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     14/03/2010
 * Objetivo: Adicionando checagem se a requisição é do tipo 'Requisitante', e se a situação está como 'em análise',
 *           para evitar erro em que o usuário recarrega a página e altera uma requisição já atendida.
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     13/04/2018
 * Objetivo: Tarefa Redmine 189924
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     15/05/2018
 * Objetivo: Tarefa Redmine 194174
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/08/2018
 * Objetivo: Tarefa Redmine 201742
 * ------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/01/2023
 * Objetivo: Tarefa Redmine 278009
 * ------------------------------------------------------------------------------------------------------------------
 */

// Acesso ao arquivo de funções
include "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/estoques/CadRequisicaoConfirmarBaixa.php');
AddMenuAcesso('/estoques/CadRequisicaoMaterialSelecionar.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadIncluirCentroCusto.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          = $_POST['Botao'];
	$InicioPrograma = $_POST['InicioPrograma'];
	$GrupoEmp       = $_POST['GrupoEmp'];
	$Usuario        = $_POST['Usuario'];
	$Almoxarifado   = $_POST['Almoxarifado'];
	$Orgao			= $_POST['Orgao'];
	$CentroCusto    = $_POST['CentroCusto'];
	$SeqRequisicao  = $_POST['SeqRequisicao'];
	$AnoRequisicao  = $_POST['AnoRequisicao'];
	$Requisicao     = $_POST['Requisicao'];
	$DataRequisicao = $_POST['DataRequisicao'];
	$NCaracteresO   = $_POST['NCaracteresO']; // conta numero de caracteres no campo observação
	$Observacao     = strtoupper2($_POST['Observacao']); // conta numero de caracteres no campo observação
	
	if ($DataRequisicao != "") {
		$DataRequisicao = FormataData($DataRequisicao);
	}
	
	$Situacao       = $_POST['Situacao'];
	$DescSituacao   = $_POST['DescSituacao'];
	$DataSituacao   = $_POST['DataSituacao'];
	$CheckItem      = $_POST['CheckItem'];
	$Material       = $_POST['Material'];
	$DescMaterial   = $_POST['DescMaterial'];
	$Unidade        = $_POST['Unidade'];
	$QtdSolicitada  = $_POST['QtdSolicitada'];
	$TipoItem       = $_POST['TipoItem'];
	$Montou         = $_POST['Montou'];
	
	for ($i = 0; $i < count($DescMaterial); $i++) {
		$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdSolicitada[$i].$SimboloConcatenacaoArray.$TipoItem[$i];
	}
} else {
	$SeqRequisicao = $_GET['SeqRequisicao'];
	$AnoRequisicao = $_GET['AnoRequisicao'];
	$Almoxarifado  = $_GET['Almoxarifado'];
	$Mens          = $_GET['Mens'];
	$Tipo          = $_GET['Tipo'];
	$Mensagem      = $_GET['Mensagem'];
}

//var_dump($DataRequisicao);exit;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Voltar") {
	header("location: CadRequisicaoMaterialSelecionar.php");
	exit;
} elseif ($Botao == "Alterar" or $Botao == "Baixou") {
	# Crítica aos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";

	unset($_SESSION['item']);

	$db = Conexao();

	# Pega os dados do Orgão antigo #
	$sql  = "SELECT A.CORGLICODI ";
	$sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
	$sql .= "WHERE  A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU ";
	$sql .= "       AND C.CREQMASEQU = $SeqRequisicao ";

	if ($_SESSION['_fperficorp_'] != 'S') {
		$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
	}

	$sql .= " ORDER BY B.EORGLIDESC, A.ECENPODESC ";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();
		$Orgao = $Linha[0];
	}		

	//Verifica se trocou de orgão de Centro de custo
	$sql  = "SELECT A.CORGLICODI ";
	$sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
	$sql .= "WHERE  A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$RowsCC    = $result->numRows();
		$Linha     = $result->fetchRow();
		$NovoOrgao = $Linha[0];
	}

	// questiona se houve mudança no orgão do centro de custo
	if ($Orgao != $NovoOrgao) {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Não é permitido alterar o órgão do centro de custo da requisição";
	}

	if ($DataRequisicao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição</a>";
	} else {
		$DataValida = ValidaData($DataRequisicao);
		
		if ($DataValida != "") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição Válida</a>";
		} else {
			if (DataInvertida($DataRequisicao) > date("Y-m-d")) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição menor que a atual</a>";
			}
		}
	}
	
	if (count($ItemRequisicao) == 0) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Pelo menos um Item para a Requisição de Material";
	}
	
	if (count($QtdSolicitada) != 0) {
		$Posicao = "";
		$Existe  = "";
		
		for ($i = 0; $i < count($QtdSolicitada); $i++) {
			if ($QtdSolicitada[$i] == "" and $Existe == "") {
				$Existe  = "S";
				$Posicao = $i;
			} elseif ($QtdSolicitada[$i] == 0) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Posicao   = ($i * 7) + 7;
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Solicitada deve ser maior que 0</a>";
			}
		}
		
		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Posicao   = ( $Posicao * 7 ) + 7;
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Solicitada</a>";
		}
		
		if ($Existe == "") {
			for ($i = 0; $i < count($QtdSolicitada); $i++) {
				if (str_replace(",",".",$QtdSolicitada[$i]) < 0 and $Existe == "") {
					$Existe  = "S";
					$Posicao = $i;
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Posicao   = ( $Posicao * 7 ) + 7;
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Solicitada Válida</a>";
			}
			
			if ($Existe == "") {
				for ($k = 0; $k < count($QtdSolicitada); $k++) {
					if ((!SoNumVirg($QtdSolicitada[$k])) and ($Existe == "")) {
						$Existe  = "S";
						$Posicao = $k;
					}
				}
				
				if ($Existe == "") {
					for ($j = 0; $j < count($QtdSolicitada); $j++) {
						if ((!Decimal($QtdSolicitada[$j])) and $Existe == "") {
							$Existe  = "S";
							$Posicao = $j;
						}
					}
				}
				
				if ($Existe == "S") {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}
					$Posicao   = ( $Posicao * 7 ) + 7;
					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialAlterarIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Solicitada Válida</a>";
				}
			}
		}
	}

	$sqlSitu = "SELECT MAX(CTIPSRCODI) FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = $SeqRequisicao ";

	$resSitu = $db->query($sqlSitu);
		
	if (PEAR::isError($resSitu)) {
		$db->query("ROLLBACK");
		$db->disconnect();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSitu");
		exit(0);
	}

	$LinhaSitu = $resSitu->fetchRow();
	$SituacaoRequisicao = $LinhaSitu[0];
	
	# Verificando se requisição foi atendida
	if ($Mens == 0) {
		$db = Conexao();
		$sql = "SELECT SR.CTIPSRCODI, RM.FREQMATIPO
				FROM SFPC.TBREQUISICAOMATERIAL RM, SFPC.TBSITUACAOREQUISICAO SR
				WHERE RM.CREQMASEQU = " . $SeqRequisicao . "
					AND RM.CREQMASEQU = SR.CREQMASEQU
					AND SR.TSITREULAT = (
						SELECT MAX(TSITREULAT)
						FROM SFPC.TBSITUACAOREQUISICAO
						WHERE CREQMASEQU = " . $SeqRequisicao . ")";
			
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			$Rollback = 1;
			$db->query("ROLLBACK");
			$db->disconnect();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			exit(0);
		}
		
		$Linha = $res->fetchRow();
		$tipoSituacaoRequisicao = $Linha[0];
		$tipoRequisicao         = $Linha[1];
		
		// Baixada = 5, Cancelada(Excluída) = 6
		if ($tipoSituacaoRequisicao >= 5) {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Requisição não pode mais ser alterada pois foi excluída ou baixada.";
		} elseif ($tipoRequisicao !='R') {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "Requisição não pode ser alterada, pois não é do tipo 'Requisitante'";
		}
		
		// verifica se o centro de custo ainda é o mesmo
		$db->disconnect();

	}

	if ($Mens == 0) {
		# Possui novos itens na requisição #
		if ($_SESSION['_cgrempcodi_'] != 0) {
			if ($Mens == 0) {
				# Atualiza a Requisição #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");

				$sql  = "UPDATE SFPC.TBREQUISICAOMATERIAL ";
				$sql .= "   SET CCENPOSEQU = $CentroCusto, DREQMADATA = '" . DataInvertida($DataRequisicao) . "', ";
				$sql .= "       CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . ", ";
				$sql .= "       CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ", ";
				$sql .= "       TREQMAULAT = '" . date("Y-m-d H:i:s") . "', EREQMAOBSE = '$Observacao'"; // incluido o campo observação
				$sql .= " WHERE CREQMASEQU = $SeqRequisicao ";
				
				$res = $db->query($sql);
				
				if (PEAR::isError($res)) {
					$Rollback = 1;
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					if($SituacaoRequisicao == 1) {
						# Apaga os itens da Requisição #
						$sql = "DELETE FROM SFPC.TBITEMREQUISICAO WHERE CREQMASEQU = $SeqRequisicao ";
						
						$res = $db->query($sql);
					
						if (PEAR::isError($res)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							# Insere os Novos Itens na Requisição de Material #
							for ($i=0;$i< count($ItemRequisicao);$i++) {
								$Ordem++;

								if ($QtdSolicitada[$i] != 0) {
									$QtdSol = str_replace(",",".",$QtdSolicitada[$i]);
								}

								# Insere o(s) Novo(s) Iten(s) na Requisição de Material #
								$sql  = "INSERT INTO SFPC.TBITEMREQUISICAO ( ";
								$sql .= "CREQMASEQU, CMATEPSEQU, AITEMRORDE, AITEMRQTSO, ";
								$sql .= "AITEMRQTAP, AITEMRQTAT, AITEMRQTCA, CGREMPCODI,";
								$sql .= "CUSUPOCODI, TITEMRULAT ";
								$sql .= ") VALUES ( ";
								$sql .= "$SeqRequisicao, $Material[$i], $Ordem, $QtdSol, ";
								$sql .= "NULL, 0, NULL, ".$_SESSION['_cgrempcodi_'].", ";
								$sql .= "".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."' )";
							
								$result = $db->query($sql);
							
								if (PEAR::isError($result)) {
									$Rollback = 1;
									$db->query("ROLLBACK");
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
							}
						}
					}
				}
				
				if ($Mens == 0) {
					# Pega os materiais para ordenar #
					$sql  = "SELECT B.CMATEPSEQU, C.EMATEPDESC, B.AITEMRQTSO, B.AITEMRQTAT  ";
					$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C ";
					$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $SeqRequisicao ";
					$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
					$sql .= " ORDER BY C.EMATEPDESC ";
					
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$Rows = $res->numRows();
						for ($j=0;$j<$Rows;$j++) {
							$Linha             = $res->fetchRow();
							$MaterialOrdem[$j] = RetiraAcentos($Linha[1]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[1]).$SimboloConcatenacaoArray.$Linha[0].$SimboloConcatenacaoArray.$Linha[2].$SimboloConcatenacaoArray.$Linha[3];
						}
					}
					
					sort($MaterialOrdem);
					
					for ($j=0;$j<count($MaterialOrdem);$j++) {
						$Dados       = explode($SimboloConcatenacaoArray,$MaterialOrdem[$j]);
						$SeqMaterial = $Dados[1];
						$QtdSol      = $Dados[2];
						$NovaOrdem++;
						
						# Atualiza as ordens dos intens #
						$sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
						$sql .= "   SET AITEMRORDE = $NovaOrdem, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
						$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TITEMRULAT = '".date("Y-m-d H:i:s")."' ";
						$sql .= " WHERE CMATEPSEQU = $SeqMaterial AND CREQMASEQU = $SeqRequisicao";
						
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							$Rollback = 1;
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
					}

					if ($Rollback == "") {
						$db->query("COMMIT");
					}
					
					$db->query("END TRANSACTION");
					$db->disconnect();

					# Limpando as variáveis #
					unset($ItemRequisicao);
					unset($_SESSION['item']);

					# Redireciona para a tela de pesquisa #
					$Mens     = 1;
					$Tipo     = 1;
					$Mensagem = urlencode("Requisição ".substr($Requisicao+100000,1)."/$AnoRequisicao Alterada com Sucesso");
					$Url = "CadRequisicaoMaterialSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
					
					if (!in_array($Url,$_SESSION['GetUrl'])) {
						$_SESSION['GetUrl'][] = $Url;
					}
					
					header("location: ".$Url);
					exit;
				}
			}
		} else {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "O Usuário do grupo INTERNET não pode fazer Alteração em Requisição de Material";
		}
	}
} elseif ($Botao == "Retirar") {
	if (count($ItemRequisicao) != 0) {
		for ($i=0; $i< count($ItemRequisicao); $i++) {
			if ($CheckItem[$i] == "") {
				$Qtd++;
				$CheckItem[$i]          = "";
				$ItemRequisicao[$Qtd-1] = $ItemRequisicao[$i];
				$DescMaterial[$Qtd-1]   = $DescMaterial[$i];
				$Material[$Qtd-1]       = $Material[$i];
				$Unidade[$Qtd-1]        = $Unidade[$i];
				$QtdSolicitada[$Qtd-1]  = $QtdSolicitada[$i];
				$TipoItem[$Qtd-1]       = $TipoItem[$i];
			}
		}
		
		if (count($ItemRequisicao) >= 1) {
			$ItemRequisicao = array_slice($ItemRequisicao,0,$Qtd);
			$DescMaterial   = array_slice($DescMaterial,0,$Qtd);
			$Material       = array_slice($Material,0,$Qtd);
			$Unidade        = array_slice($Unidade,0,$Qtd);
			$QtdSolicitada  = array_slice($QtdSolicitada,0,$Qtd);
			$TipoItem       = array_slice($TipoItem,0,$Qtd);
		} else {
			unset($ItemRequisicao);
			unset($Material);
			unset($DescMaterial);
			unset($Unidade);
			unset($QtdSolicitada);
			unset($TipoItem);
		}
	}
	unset($_SESSION['item']);
}

if ($Botao == "" and $Montou == "" ) {
	# Verifica se é a primeira vez que entra no programa #
	if ($InicioPrograma == "") {
		unset($_SESSION['item']);
	}

	if ($_SESSION['_cgrempcodi_'] != 0) {
		# Verifica se o Usuário está ligado a algum centro de Custo #
		$db   = Conexao();
		$sql  = "SELECT USUCEN.CUSUPOCODI ";
		$sql .= "  FROM SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, ";
		$sql .= "       SFPC.TBGRUPOEMPRESA GRUEMP, SFPC.TBORGAOLICITANTE ORGSOL, SFPC.TBUSUARIOPORTAL USUPOR ";
		$sql .= " WHERE USUCEN.CGREMPCODI <> 0 AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU AND USUCEN.FUSUCCTIPO IN ('T','R') ";
		$sql .= "   AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI AND CENCUS.CORGLICODI = ORGSOL.CORGLICODI ";
		$sql .= "   AND USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI AND USUCEN.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
		$sql .= "   AND USUCEN.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
		
		if ($_SESSION['_fperficorp_'] != 'S') {
			$sql .= "   AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		}
		
		$sql .= " ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Rows = $res->numRows();
			
			if ($Rows == 0) {
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "O Usuário não está ligado a nenhum Centro de Custo";
			}
		}

		if ($CentroCusto == "") {
			# Pega os dados do Centro de Custo #
			$sql 	  = "SELECT A.CCENPOSEQU ";
			$sql 	 .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
			$sql 	 .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU ";
			$sql 	 .= "   AND C.CREQMASEQU = $SeqRequisicao ";
			
			if ($_SESSION['_fperficorp_'] != 'S') {
				$sql	 .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
			}
			
			$sql   .= " ORDER BY B.EORGLIDESC, A.ECENPODESC ";
			
			$result = $db->query($sql);
			
			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$Linha       = $result->fetchRow();
				$CentroCusto = $Linha[0];
			}
		}

		# Pega os dados da Última Situação da Requisicao #
		$sql  = "SELECT A.TSITREULAT, B.ETIPSRDESC, B.CTIPSRCODI ";
		$sql .= "  FROM SFPC.TBSITUACAOREQUISICAO A, SFPC.TBTIPOSITUACAOREQUISICAO B ";
		$sql .= " WHERE A.CREQMASEQU = $SeqRequisicao AND A.CTIPSRCODI = B.CTIPSRCODI ";
		$sql .= "   AND A.TSITREULAT =  ";
		$sql .= "      ( SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO ";
		$sql .= "         WHERE CREQMASEQU = $SeqRequisicao ) ";
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Linha        = $res->fetchRow();
			$DataSituacao = DataBarra($Linha[0]);
			$DescSituacao = $Linha[1];
			$Situacao     = $Linha[2];
		}

		# Pega os dados da Requisição de Material de acordo com o Sequencial #
		$sql  = "SELECT A.CREQMACODI, A.CGREMPCODI, A.CUSUPOCODI, B.AITEMRQTSO, ";
		$sql .= "       B.AITEMRQTAT, B.AITEMRORDE, C.CMATEPSEQU, C.EMATEPDESC, ";
		$sql .= "       D.EUNIDMSIGL, A.DREQMADATA, A.CCENPOSEQU ";
		$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B, SFPC.TBMATERIALPORTAL C, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA D  ";
		$sql .= " WHERE A.AREQMAANOR = $AnoRequisicao AND A.CREQMASEQU = $SeqRequisicao ";
		$sql .= "   AND A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
		$sql .= " ORDER BY B.AITEMRORDE ";
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			$Rows = $res->numRows();
			for ($i=0;$i<$Rows;$i++) {
				$Linha              = $res->fetchRow();
				$Requisicao         = $Linha[0];
				$GrupoEmp           = $Linha[1];
				$Usuario            = $Linha[2];
				$QtdSolicitada[$i]  = str_replace(".",",",$Linha[3]);
				$Material[$i]       = $Linha[6];
				$DescMaterial[$i]   = RetiraAcentos($Linha[7]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[7]);
				$Unidade[$i]        = $Linha[8];
				$DataRequisicao     = DataBarra($Linha[9]);
				$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$QtdSolicitada[$i].$SimboloConcatenacaoArray."B";
			}
			$Montou = "S";
		}
		$db->disconnect();
	} else {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "O Usuário do grupo INTERNET não pode fazer Alteração em Requisição de Material";
	}
}

# Monta o array de itens da requisição de material #
if (count($_SESSION['item']) != 0) {
	sort($_SESSION['item']);
	if ($ItemRequisicao == "") {
		for ($i=0; $i<count($_SESSION['item']); $i++) {
			$ItemRequisicao[count($ItemRequisicao)] = $_SESSION['item'][$i];
		}
	} else {
		for ($i=0; $i<count($ItemRequisicao); $i++) {
			$DadosItem            = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
			$SequencialItem[$i]   = $DadosItem[1];
		}
		
		for ($i=0; $i<count($_SESSION['item']); $i++) {
			$DadosSessao          = explode($SimboloConcatenacaoArray,$_SESSION['item'][$i]);
			$SequencialSessao[$i] = $DadosSessao[1];
			
			if (!in_array($SequencialSessao[$i],$SequencialItem)) {
				$ItemRequisicao[count($ItemRequisicao)] = $_SESSION['item'][$i];
			}
		}
	}
	unset($_SESSION['item']);
}

?>
<html>
	<?php
		# Carrega o layout padrão #
		layout();
	?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
		<!--
			function enviar(valor) {
				document.CadRequisicaoMaterialAlterarIncluir.Botao.value = valor;
				document.CadRequisicaoMaterialAlterarIncluir.submit();
			}

			function AbreJanela(url,largura,altura) {
				window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
			}

			function AbreJanelaItem(url,largura,altura) {
				window.open(url,'paginaitem','status=no,scrollbars=yes,left=40,top=120,width='+largura+',height='+altura);
			}

			function ncaracteresO(valor) {
				document.CadRequisicaoMaterialAlterarIncluir.NCaracteresO.value = '' +  document.CadRequisicaoMaterialAlterarIncluir.Observacao.value.length;

				if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
					document.CadRequisicaoMaterialAlterarIncluir.NCaracteresO.focus();
				}
			}

		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadRequisicaoMaterialAlterarIncluir.php" method="post" name="CadRequisicaoMaterialAlterarIncluir">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Manter
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php	if ($Mens == 1) { ?>
			<tr>
				<td width="100"></td>
				<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
			</tr>
			<?php	} ?>
			<!-- Fim do Erro --> 
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											MANTER - REQUISIÇÃO DE MATERIAL
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Para alterar uma Requisição de Material cadastrada, preencha os dados abaixo e clique no botão "Alterar". Os itens obrigatórios estão com *.
											</p>
										</td>
									</tr>
									<tr>
										<td>
											<table class="textonormal" border="0" align="left" width="100%" summary="">
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Operação</td>
													<td class="textonormal"><?php echo "INCLUSÃO DE ITEM(NS)"; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
													<td class="textonormal">
														<?php	$db   = Conexao();
																$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
																$res  = $db->query($sql);
																if (PEAR::isError($res)) {
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																} else {
																	$Linha = $res->fetchRow();
																	echo "$Linha[0]<br>";
																	echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																}
																$db->disconnect();
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo*</td>
													<td class="textonormal">
														<?php	if ($_SESSION['_cgrempcodi_'] != 0) {
																	# Pega os dados do Centro de Custo #
																	$db     = Conexao();

																	# Pega o tipo do usuário "
																	$sqlTipo  = "SELECT FUSUCCTIPO ";
																	$sqlTipo .= "  FROM SFPC.TBUSUARIOCENTROCUSTO ";
																	$sqlTipo .= " WHERE CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND FUSUCCTIPO IN ('T','R')";
																	
																	$resTipo  = $db->query($sqlTipo);
																	
																	if (PEAR::isError($resTipo)) {
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTipo");
																	} else {
																		$Rows = $resTipo->numRows();
																		if ($Rows != 0) {
																			$LinhaTipo   = $resTipo->fetchRow();
																			$TipoUsuario = $LinhaTipo[0];
																		}
																	}
																	
																	$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CCENPONRPA, A.ECENPODETA, A.CORGLICODI ";
																	$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																	$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
																	if ($_SESSION['_fperficorp_'] != 'S') {
																		$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																	}
																	
																	$result = $db->query($sql);
																	
																	if (PEAR::isError($result)) {
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
																		$RowsCC          = $result->numRows();
																		$Linha           = $result->fetchRow();
																		$DescCentroCusto = $Linha[0];
																		$DescOrgao       = $Linha[1];
																		$RPA             = $Linha[2];
																		$Detalhamento    = $Linha[3];
																		$Orgao			 = $Linha[4];
																
																		$Url = "CadIncluirCentroCusto.php?ProgramaOrigem=CadRequisicaoMaterialAlterarIncluir&TipoUsuario=$TipoUsuario";
																		if (!in_array($Url,$_SESSION['GetUrl'])) {
																			$_SESSION['GetUrl'][] = $Url;
																		}
																		echo "<a href=\"javascript:AbreJanela('$Url',700,370);\"><img src=\"../midia/lupa.gif\" border=\"0\"></a><br>\n";
																		echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																		echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																		echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																		echo $Detalhamento;
																	}
																}
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Requisição</td>
													<td class="textonormal"><?php echo substr($Requisicao+100000,1)."/".$AnoRequisicao; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário Requisitante</td>
													<td class="textonormal">
														<?php	if ($_SESSION['_cgrempcodi_'] != 0) {
																	# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
																	$sql  = "SELECT USU.EUSUPOLOGI, USU.EUSUPORESP ";
																	$sql .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
																	$sql .= " WHERE SIT.CREQMASEQU = $SeqRequisicao AND SIT.CTIPSRCODI = 1 ";
																	$sql .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
																	
																	$result = $db->query($sql);
																	
																	if (PEAR::isError($result)) {
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
																		$Linha = $result->fetchRow();
																		$Login = strtoupper2($Linha[0]);
																		$Nome  = $Linha[1];
																		echo $Nome;
																	}
																	
																	$db->disconnect();
																}
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Requisição*</td>
													<td class="textonormal">
														<?php $URL = "../calendario.php?Formulario=CadRequisicaoMaterialAlterarIncluir&Campo=DataRequisicao";?>
														<input type="text" name="DataRequisicao" size="10" maxlength="10" value="<?php echo $DataRequisicao; ?>" class="textonormal">
														<a href="javascript:janela('<?php echo $URL; ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
														<font class="textonormal">dd/mm/aaaa</font>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
													<td class="textonormal"><?php echo $DescSituacao; ?></td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação</td>
													<td class="textonormal"><?php echo $DataSituacao; ?></td>
												</tr>
												<?php // Inclui o campo Observação ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
													<td class="textonormal">
														<font class="textonormal">máximo de 200 caracteres</font>
														<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO; ?>" class="textonormal"><br>
														<?php	# Mostra o conteúdo da descrição #
																$db   = Conexao();
																
																$sql  = "SELECT EREQMAOBSE FROM SFPC.TBREQUISICAOMATERIAL REQMAT ";
																$sql .= "WHERE REQMAT.CREQMASEQU = $SeqRequisicao ";
																
																$result = $db->query($sql);
																
																if (PEAR::isError($result)) {
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																} else {
																	$Linha = $result->fetchRow();
																	$Observacao = $Linha[0];
																}
																$db->disconnect();
														?>
														<textarea name="Observacao" cols="50" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
													</td>
												</tr>
												<tr>
													<td class="textonormal" colspan="4">
														<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
															<tr>
																<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
																	ITENS DA REQUISIÇÃO
																</td>
															</tr>
															<?php	if (count($ItemRequisicao) != 0) {
																		sort($ItemRequisicao);
																	}
																	for ($i=0;$i< count($ItemRequisicao);$i++) {
																		$Dados             = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
																		$DescMaterial[$i]  = $Dados[0];
																		$Material[$i]      = $Dados[1];
																		$Unidade[$i]       = $Dados[2];
																		$QtdSolicitada[$i] = $Dados[3];
																		$TipoItem[$i]      = $Dados[4];
																		
																		if ($i == 0) {
																			echo "		<tr>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">ORDEM</td>\n";
																			echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">UNIDADE</td>\n";
																			echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\" align=\"center\">QUANTIDADE</td>\n";
																			echo "		</tr>\n";
																		}
															?>
															<tr>
																<td class="textonormal" align="center" width="5%">
																<?php	echo $i+1; ?>
																	<input type="hidden" name="ItemRequisicao[<?php echo $i; ?>]" value="<?php echo $ItemRequisicao[$i]; ?>">
																	<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
																	<input type="hidden" name="TipoItem[<?php echo $i; ?>]" value="<?php echo $TipoItem[$i]; ?>" size="3">
																</td>
																<td class="textonormal" width="80%">
																	<input type="checkbox" name="CheckItem[<?php echo $i; ?>]" value="<?php echo $i; ?>">
																	<?php	$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialAlterarIncluir&Material=$Material[$i]";
																		if (!in_array($Url,$_SESSION['GetUrl'])) {
																			$_SESSION['GetUrl'][] = $Url;
																		}
																	?>
																	<a href="javascript:AbreJanela('<?php $Url;?>',730,370);">
																		<font color="#000000">
																			<?php	$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																					echo $Descricao[1];
																			?>
																		</font>
																	</a>
																	<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
																</td>
																<td class="textonormal" width="5%" align="center">
																	<?php echo $Unidade[$i]; ?>
																	<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
																</td>
																<td class="textonormal" align="right" width="10%">
																	<input type="text" name="QtdSolicitada[<?php echo $i; ?>]" size="11" maxlength="11" value="<?php echo $QtdSolicitada[$i]; ?>" class="textonormal">
																</td>
															</tr>
															<?php	} ?>
															<tr>
																<td class="textonormal" colspan="5" align="center">
																	<?php	$Url = "CadIncluirItem.php?ProgramaOrigem=CadRequisicaoMaterialAlterarIncluir&PesqApenas=E&Zerados=N&Almoxarifado=$Almoxarifado";
																		if (!in_array($Url,$_SESSION['GetUrl'])) {
																			$_SESSION['GetUrl'][] = $Url;
																		}
																	?>
																	<input type="button" name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('<?php echo $Url; ?>',730,350);">
																	<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" align="right">
											<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
											<input type="hidden" name="Orgao" value="<?php echo $Orgao; ?>">
											<input type="hidden" name="Montou" value="<?php echo $Montou; ?>">
											<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
											<input type="hidden" name="InicioPrograma" value="1">
											<input type="hidden" name="DescSituacao" value="<?php echo $DescSituacao; ?>">
											<input type="hidden" name="DataSituacao" value="<?php echo $DataSituacao; ?>">
											<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
											<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
											<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
											<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
											<input type="hidden" name="AnoRequisicao" value="<?php echo $AnoRequisicao; ?>">
											<input type="button" name="Alterar" value="Alterar Requisição" class="botao" onClick="javascript:enviar('Alterar');">
											<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
											<input type="hidden" name="Botao" value="">
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