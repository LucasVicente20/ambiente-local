<?php
# ------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoMaterialIncluir.php
# Objetivo: Programa de Inclusão de Requisição de Material
# Autor:    Roberta Costa
# Data:     09/06/2005
# OBS.:     Tabulação 2 espaços
# ------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     05/05/2006
# ------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     12/07/2006 - Loop de tentativa de gravação enquanto o erro da
#           query for de chave duplicada, pega o max de novo e tenta
#           inserir a requisição até conseguir.
# ------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Padronização das variáveis de requisição
# ------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     13/12/2006 - Apresentar o código reduzido na lista de itens da requisição
# ------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     27/12/2006 - Filtro no carregamento dos almoxarifados para liberar Almox. Educação quando Sob Inventário
# ------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# ------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     13/06/2007 - Preencher data de requisicao com a data atual quando for vazia
# ------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     20/12/2007 - Correção do select de almoxarifado para bloquear almoxarifados em inventário ou no período de inventário
# ------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     09/01/2008 - Correção do select de almoxarifado, pois o mesmo não está liberando os almoxarifados a realizarem as
#                                 movimentações após a realização do inventário.
# ------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     04/02/2011 - #1730 RedMine- Proibir requisições com data de requisição menor ou igual a data de fechamento de inventário, pois geram problemas quando precisa ser canceladas (pois sistema bloqueia devolução interna nestes casos).
# ------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data:		19/11/2014 - CR 213 - Alterar as funcionalidades "Incluir / Manter Nota Fiscal" e "Incluir / Manter / Atender Requisição" para liberar movimentações dos usuários dos órgãos nos períodos cadastrados na nova funcionalidade de liberação de movimentação.
# ------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Ernesto Ferreira
# Data:     08/06/2018
# Objetivo: CR 194174 - Permitir alteração do centro de custo
# ------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/09/2018
# Objetivo: Tarefa Redmine 204157
# ------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     26/12/2018
# Objetivo: Tarefa Redmine 208722
# ------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/estoques/CadIncluirItem.php');
AddMenuAcesso('/estoques/CadItemDetalhe.php');
AddMenuAcesso('/estoques/CadIncluirCentroCusto.php');

$Troca = 1; // Padrão que pode ser mudado durante o programa. Desta forma converte última vírgua da mensagem de erro por "e"

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao               = $_POST['Botao'];
	$InicioPrograma      = $_POST['InicioPrograma'];
	$TipoUsuario         = $_POST['TipoUsuario'];
	$Almoxarifado        = $_POST['Almoxarifado'];
	$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
	$CentroCusto         = $_POST['CentroCusto'];
	$OrgaoUsuario        = $_POST['OrgaoUsuario'];
	$DataRequisicao      = $_POST['DataRequisicao'];
	$Observacao          = strtoupper2($_POST['Observacao']);
	$NCaracteresO        = $_POST['NCaracteresO'];
	
	if ($DataRequisicao != "") {
		$DataRequisicao = FormataData($DataRequisicao);
	}
	
	$TipoRequisicao      = $_POST['TipoRequisicao'];
	$CheckItem           = $_POST['CheckItem'];
	$TipoPesquisa        = $_POST['TipoPesquisa'];
	$Material            = $_POST['Material'];
	$DescMaterial        = $_POST['DescMaterial'];
	$Unidade             = $_POST['Unidade'];
	$Quantidade          = $_POST['Quantidade'];
	
	for ($i=0;$i<count($DescMaterial);$i++) {
		$ItemRequisicao[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Quantidade[$i];
	}
	
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Requisição Ano Atual #
$AnoRequisicao = date("Y");
$DataAtual     = date("Y-m-d");

if (is_null($DataRequisicao)) {
	$DataRequisicao = date("d/m/Y");
}

# Crítica dos campos #
if ($Botao == "Incluir") {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if (($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N')) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Almoxarifado";
	} elseif ($Almoxarifado == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
	}
	
	if ($CentroCusto == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Centro de Custo";
	}
	
	if ($DataRequisicao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição</a>";
	} else {
		$DataValida = ValidaData($DataRequisicao);
		
		if ($DataValida != "" ) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição Válida</a>";
		} else {
			if (DataInvertida($DataRequisicao) > $DataAtual) {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição menor ou igual a atual</a>";
			}
		}
	}
	
	if (strlen($Observacao) > "200") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "Observação deve ser de até 200 caracteres";
	}
	
	# Se não escolheu nenhum item #
	if (count($Quantidade) == 0) {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}

		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "Pelo menos um Item";
	}
	
	if (count($Quantidade) != 0) {
		# Verifica se existe alguma quantidade igual a branco ou zero #
		$Existe = "";
		
		for ($i=0;$i<count($Quantidade);$i++) {
			if ((str_replace(",",".",$Quantidade[$i]) == 0 or $Quantidade[$i] == "") and $Existe == "") {
				$Existe  = "S";
				$Posicao = $i;
			}
		}
		
		if ($Existe == "S") {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Posicao   = ($Posicao * 6) + 10;
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade</a>";
		}

		# Verifica se as quantidades só são numeros e decimais #
		if ($Existe == "") {
			$Posicao = "";
			
			for ($k=0;$k<count($Quantidade);$k++) {
				if ((!SoNumVirg($Quantidade[$k])) and ($Existe == "")) {
					$Existe  = "S";
					$Posicao = $k;
				}
			}
			
			if ($Existe == "") {
				for ($j=0;$j<count($Quantidade);$j++) {
					$Teste = Decimal($Quantidade[$j]);
					
					if ((!Decimal($Quantidade[$j])) and $Existe == "") {
						$Existe  = "S";
						$Posicao = $j;
					}
				}
			}
			
			if ($Existe == "S") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Posicao   = ($Posicao * 6) + 10;
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Válida</a>";
			}
		}
	}
	
	if ($Mens == 0) {
		$db = Conexao();
		
		$sql = "SELECT	MAX(I.TINVCOFECH)
				FROM	SFPC.TBINVENTARIOCONTAGEM I, SFPC.TBLOCALIZACAOMATERIAL L
				WHERE	I.CLOCMACODI = L.CLOCMACODI
						AND I.FINVCOFECH = 'S'
						AND CALMPOCODI = ".$Almoxarifado." ";
		
		$res = $db->query($sql);
		
		if (PEAR::isError($res)) {
			$db->disconnect();
			EmailErroSQL('Erro em SQL', __FILE__, __LINE__, 'Erro em SQL', $sql, $res);
		}

		$Linha = $res->fetchRow();
		$DataUltimoInventario = $Linha[0];
		$DataUltimoInventario = FormataData(DataBarra($DataUltimoInventario));
		
		$db->disconnect();
		
		if (DataInvertida($DataRequisicao) <= DataInvertida($DataUltimoInventario)) {
			if ($Mens == 1) {
				$Mensagem .= ", ";
			}

			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadRequisicaoMaterialIncluir.DataRequisicao.focus();\" class=\"titulo2\">Data da Requisição deve ser maior que a data do último inventário (".$DataUltimoInventario.")</a>";
		}
	}
	
	if ($Mens == 0) {
		if ($_SESSION['_cgrempcodi_'] != 0) {
			$DataGravacao = date("Y-m-d H:i:s");

			if ($_SESSION['DataGravacao']) {
				$DataGravacaoSession  = str_replace(":","-",str_replace(" ","-",$_SESSION['DataGravacao']));
				$DGS                  = explode("-",$DataGravacaoSession);
				$MomentoSession       = (86400*$DGS[2]) + (3600*$DGS[3]) + (60*$DGS[4]) + ($DGS[5]); // Dia, Hora, Minuto, Segundo --> Segundos
				$DataGravacaoVariavel = str_replace(":","-",str_replace(" ","-",$DataGravacao));
				$DGV                  = explode("-",$DataGravacaoVariavel);
				$MomentoVariavel      = (86400*$DGV[2]) + (3600*$DGV[3]) + (60*$DGV[4]) + ($DGV[5]); // Dia, Hora, Minuto, Segundo --> Segundos
			}

			# Evita duplicidade de gravação teclando F5 #
			if (($_SESSION['OrgaoUsuario'] == $OrgaoUsuario)
				AND ($_SESSION['CentroCusto'] == $CentroCusto)
				AND ($_SESSION['DataRequisicao'] == $DataRequisicao)
				AND ($_SESSION['Material'] == $Material)
				AND ($_SESSION['Quantidade'] == $Quantidade)
				AND ($_SESSION['DataGravacao'])
				AND ($MomentoVariavel >= $MomentoSession)
				AND ($MomentoVariavel < $MomentoSession + 300)) { // Não permite alterações no banco, se uma movimetação equivalente tiver sido realizada até 10 minutos antes
							
				# Limpa os campos #
				unset($ItemRequisicao);
				unset($_SESSION['item']);
				$OrgaoUsuario   = "";
				$CentroCusto    = "";
				$DataRequisicao = "";
				$TipoRequisicao = "";
				$CheckItem      = "";
				$Material       = "";
				$DescMaterial   = "";
				$Unidade        = "";
				$Quantidade     = "";
				$InicioPrograma = "";
				$Observacao	    = "";

				# Envia a Mensagem de Sucesso/Duplicidade #
				$Mens      = 1;
				$Tipo      = 1;
				$Troca     = 2;
				$Mensagem  = "Requisição Incluída com Sucesso, Número da Requisição: ".substr($_SESSION['Requisicao']+100000,1)."/".$_SESSION['AnoRequisicao']. ", ou Houve Bloqueio de Tentativa de Duplicidade, Não Gerando Nova Requisição";
			} else {
				$CodErro = -3; // Para provocar a primeira entrada
				
				while ($CodErro == -3) { // Enquanto o erro for de chave duplicada, faz tudo de novo, pega o max e tenta inserir
					$CodErro        = null; // Seta null em CodErro, para só voltar a ser -3 se houver outra chave duplicada na próxima tentativa
					$ErroGravaBanco = null;
					
					# Recupera o último Código da Requisição e incrementa mais um #
					$db = Conexao();
								
					$sql = "SELECT MAX(CREQMACODI) FROM SFPC.TBREQUISICAOMATERIAL WHERE AREQMAANOR = $AnoRequisicao AND CORGLICODI = $OrgaoUsuario";
					
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						$ErroGravaBanco = 1;
						$CodErroEmail   = $res->getCode();
						$DescErroEmail  = $res->getMessage();
						
						$db->disconnect();
						
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
					} else {
						$Linha = $res->fetchRow();
						$Requisicao = $Linha[0] + 1;
						
						$db->query("BEGIN TRANSACTION");
						
						# Insere a requisição #
						$sql = "INSERT INTO SFPC.TBREQUISICAOMATERIAL (CREQMASEQU, CORGLICODI, AREQMAANOR, CREQMACODI, CCENPOSEQU, CGREMPCODI, CUSUPOCODI, FREQMATIPO, EREQMAOBSE, DREQMADATA, TREQMAULAT, CALMPOCODI)
								VALUES (nextval('SFPC.TBrequisicaomaterial_creqmasequ_seq'), $OrgaoUsuario, $AnoRequisicao, $Requisicao, $CentroCusto, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", 'R', '$Observacao', '".DataInvertida($DataRequisicao)."', '".$DataGravacao."', $Almoxarifado) ";
						
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							$ErroGravaBanco = 1;
							$CodErroEmail   = $res->getCode();
							$DescErroEmail  = $res->getMessage();
							$CodErro        = $CodErroEmail;
							
							$db->query("ROLLBACK");
							$db->query("END TRANSACTION");
							$db->disconnect();
													
							if ($CodErro != -3) { // Outro erro, diferente de chave duplicada, exibe mensagem de erro e envia e-mail para o analista
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
							}
						}
					}
				}
				
				if ($ErroGravaBanco != 1) {
					# Recupera o Sequencial da Requisição estabelecido pelo nextval do Insert acima #
					$sql = "SELECT	CREQMASEQU
							FROM	SFPC.TBREQUISICAOMATERIAL
							WHERE	CALMPOCODI = $Almoxarifado
									AND CORGLICODI = $OrgaoUsuario
									AND AREQMAANOR = $AnoRequisicao
									AND CREQMACODI = $Requisicao
									AND CCENPOSEQU = $CentroCusto
									AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']."
									AND CUSUPOCODI = ".$_SESSION['_cusupocodi_']."
									AND FREQMATIPO = 'R'
									AND DREQMADATA = '".DataInvertida($DataRequisicao)."'
									AND TREQMAULAT = '".$DataGravacao."' ";
					
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						$CodErroEmail  = $res->getCode();
						$DescErroEmail = $res->getMessage();
						
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
						
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
					} else {
						$Linha         = $res->fetchRow();
						$SeqRequisicao = $Linha[0];
						
						# Código do tipo da situação 1 (EM ANÁLISE) #
						$sql = "INSERT INTO SFPC.TBSITUACAOREQUISICAO (CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, CUSUPOCODI, TSITREULAT)
								VALUES ($SeqRequisicao, 1 , '".$DataGravacao."', ".$_SESSION['_cgrempcodi_']." ,".$_SESSION['_cusupocodi_'].", '".$DataGravacao."' )";
						
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							$CodErroEmail  = $res->getCode();
							$DescErroEmail = $res->getMessage();
							
							$db->query("ROLLBACK");
							$db->query("END TRANSACTION");
							$db->disconnect();
							
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						} else {
							# Insere Itens de Requisição de Material #
							$Carregou = "";
							
							for ($i=0; $i< count($ItemRequisicao) and !$ErrosNoFor; $i++) {
								$QtdSol = str_replace(",",".",$Quantidade[$i]);
								
								if ($QtdSol != 0) {
									$Ordem = $i + 1;
									
									$sql = "INSERT INTO SFPC.TBITEMREQUISICAO (CREQMASEQU, CMATEPSEQU, AITEMRORDE, AITEMRQTSO, AITEMRQTAP, AITEMRQTAT, AITEMRQTCA, CGREMPCODI, CUSUPOCODI, TITEMRULAT)
											VALUES ($SeqRequisicao, $Material[$i], $Ordem, $QtdSol, NULL, 0, NULL, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].",  '".$DataGravacao."') ";
									
									$result = $db->query($sql);
									
									if (PEAR::isError($result)) {
										$CodErroEmail  = $result->getCode();
										$DescErroEmail = $result->getMessage();
										$ErrosNoFor    = 1;
										$Carregou      = "N";
										
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
									} else {
										$Carregou = "S";
									}
								}
							}
							
							if ($Carregou == "S") {
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();

								$_SESSION['OrgaoUsuario']   = $OrgaoUsuario;
								$_SESSION['CentroCusto']    = $CentroCusto;
								$_SESSION['DataRequisicao'] = $DataRequisicao;
								$_SESSION['Material']       = $Material;
								$_SESSION['Quantidade']     = $Quantidade;
								$_SESSION['DataGravacao']   = $DataGravacao;
								$_SESSION['Requisicao']     = $Requisicao;
								$_SESSION['AnoRequisicao']  = $AnoRequisicao;

								# Limpa os campos #
								unset($ItemRequisicao);
								unset($_SESSION['item']);
								$OrgaoUsuario   = "";
								$CentroCusto    = "";
								$DataRequisicao = "";
								$TipoRequisicao = "";
								$CheckItem      = "";
								$Material       = "";
								$DescMaterial   = "";
								$Unidade        = "";
								$Quantidade     = "";
								$InicioPrograma = "";
								$Observacao     = "";

								# Envia a Mensagem de Sucesso #
								$Mens      = 1;
								$Tipo      = 1;
								$Mensagem  = "Requisição Incluída com Sucesso!<br>";
								$Mensagem .= "Número da Requisição Gerado foi: ".substr($Requisicao+100000,1)."/$AnoRequisicao";
							}
						}
					}
				}
			}
		} else {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "O Usuário do grupo INTERNET não pode fazer Requisição de Material";
		}
	}
} elseif ($Botao == "Retirar") {
	if (count($ItemRequisicao) != 0) {
		for ($i=0; $i< count($ItemRequisicao); $i++) {
			if ($CheckItem[$i] == "") {
				$Qtd++;
				$CheckItem[$i]          = "";
				$ItemRequisicao[$Qtd-1] = $ItemRequisicao[$i];
				$Material[$Qtd-1]       = $Material[$i];
				$DescMaterial[$Qtd-1]   = $DescMaterial[$i];
				$Unidade[$Qtd-1]        = $Unidade[$i];
				$Quantidade[$Qtd-1]     = $Quantidade[$i];
			}
		}
		
		if (count($ItemRequisicao) > 1) {
			$ItemRequisicao = array_slice($ItemRequisicao,0,$Qtd);
			$Material       = array_slice($Material,0,$Qtd);
			$DescMaterial   = array_slice($DescMaterial,0,$Qtd);
			$Unidade        = array_slice($Unidade,0,$Qtd);
			$Quantidade     = array_slice($Quantidade,0,$Qtd);
		} else {
			unset($ItemRequisicao);
			unset($Material);
			unset($DescMaterial);
			unset($Unidade);
			unset($Quantidade);
		}
	}
	unset($_SESSION['item']);
}

if ($Botao == "") {
	# Verifica se é a primeira vez que entra no programa #
	if ($InicioPrograma == "") {
		unset($_SESSION['item']);
	}

	if ($_SESSION['_cgrempcodi_'] != 0) {
		# Verifica se o Usuário está ligado a algum centro de Custo #
		$db = Conexao();

		$Grupo   = $_SESSION['_cgrempcodi_'];
		$Usuario = $_SESSION['_cusupocodi_'];

		$sql = "SELECT	USUCEN.CUSUPOCODI
				FROM	SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS, SFPC.TBGRUPOEMPRESA GRUEMP, SFPC.TBORGAOLICITANTE ORGSOL, SFPC.TBUSUARIOPORTAL USUPOR
				WHERE	USUCEN.CGREMPCODI <> 0
						AND USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
						AND USUCEN.FUSUCCTIPO IN ('T','R')
						AND USUCEN.CGREMPCODI = GRUEMP.CGREMPCODI
						AND CENCUS.CORGLICODI = ORGSOL.CORGLICODI
						AND USUCEN.CUSUPOCODI = USUPOR.CUSUPOCODI
						AND USUCEN.CGREMPCODI = $Grupo
						AND USUCEN.CUSUPOCODI = $Usuario
						AND CENCUS.FCENPOSITU <> 'I'
		ORDER BY GRUEMP.EGREMPDESC, ORGSOL.EORGLIDESC, CENCUS.ECENPODESC, USUPOR.EUSUPORESP ";
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			$CodErroEmail  = $res->getCode();
			$DescErroEmail = $res->getMessage();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		} else {
			$Rows = $res->numRows();
			
			if ($Rows == 0) {
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "O Usuário não está ligado a nenhum Centro de Custo";
			}
		}

		# Carrega o Tipo do Usuário e Orgão Solicitante do GrupoEmpresa/Usuário Logado #
		$sql = "SELECT	USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
				FROM	SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
				WHERE	USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
						AND USUCEN.FUSUCCTIPO IN ('T','R')
						AND ((USUCEN.CUSUPOCODI = $Usuario AND USUCEN.CGREMPCODI = $Grupo) OR (USUCEN.CUSUPOCOD1 = $Usuario AND USUCEN.CGREMPCOD1 = $Grupo AND '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS )) 
						AND USUCEN.FUSUCCTIPO = 'T'
						AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		
		$res  = $db->query($sql);
		
		if (PEAR::isError($res)) {
			$CodErroEmail  = $res->getCode();
			$DescErroEmail = $res->getMessage();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		} else {
			$Rows = $res->numRows();
			
			if ($Rows != 0) {
				$Linha        = $res->fetchRow();
				$TipoUsuario  = $Linha[0];
				$OrgaoUsuario = $Linha[1];
				
				if ($TipoUsuario == "R") {
					$DescUsuario = "REQUISITANTE";
				} elseif ($TipoUsuario == "A") {
					$DescUsuario = "APROVADOR";
				} else {
					$DescUsuario = "ATENDIMENTO";
				}
			} else {
				$sql = "SELECT	USUCEN.FUSUCCTIPO, CENCUS.CORGLICODI
						FROM	SFPC.TBUSUARIOCENTROCUSTO USUCEN, SFPC.TBCENTROCUSTOPORTAL CENCUS
						WHERE	USUCEN.CCENPOSEQU = CENCUS.CCENPOSEQU
								AND USUCEN.FUSUCCTIPO IN ('T','R')
								AND ((USUCEN.CUSUPOCODI = $Usuario AND USUCEN.CGREMPCODI = $Grupo) OR (USUCEN.CUSUPOCOD1 = $Usuario AND USUCEN.CGREMPCOD1 = $Grupo AND '$DataAtual' BETWEEN DUSUCCINIS AND DUSUCCFIMS))
								AND USUCEN.FUSUCCTIPO <> 'T'
								AND CENCUS.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
				
				$res = $db->query($sql);
				
				if (PEAR::isError($res)) {
					$CodErroEmail  = $res->getCode();
					$DescErroEmail = $res->getMessage();
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
				} else {
					$Rows = $res->numRows();
					
					if ($Rows != 0) {
						$Linha        = $res->fetchRow();
						$TipoUsuario  = $Linha[0];
						$OrgaoUsuario = $Linha[1];
						
						if ($TipoUsuario == "R") {
							$DescUsuario = "Requisitante";
						} elseif ($TipoUsuario == "A") {
							$DescUsuario = "Aprovador";
						} else {
							$DescUsuario = "Atendimento";
						}
					}
				}
			}
		}
		$db->disconnect();
	} else {
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "O Usuário do grupo INTERNET não pode fazer Requisição de Material";
	}
}

# Monta o array de itens da requisição de material #
if (count($_SESSION['item']) != 0) {
	sort($_SESSION['item']);
	
	if ($ItemRequisicao == "") {
		for($i=0; $i<count($_SESSION['item']); $i++){
			$ItemRequisicao[count($ItemRequisicao)] = $_SESSION['item'][$i];
		}
	} else {
		for ($i=0; $i<count($ItemRequisicao); $i++) {
			$DadosItem          = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
			$SequencialItem[$i] = $DadosItem[1];
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
	<?php layout(); ?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
		function enviar(valor) {
			document.CadRequisicaoMaterialIncluir.Botao.value = valor;
			document.CadRequisicaoMaterialIncluir.submit();
		}

		function AbreJanela(url,largura,altura) {
			window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
		}

		function AbreJanelaItem(url,largura,altura) {
			console.log('aqui.')
			window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
		}

		<?php MenuAcesso(); ?>

		function ncaracteresO(valor) {
			document.CadRequisicaoMaterialIncluir.NCaracteresO.value = '' +  document.CadRequisicaoMaterialIncluir.Observacao.value.length;

			if (navigator.appName == 'Netscape' && valor) { //Netscape Only
				document.CadRequisicaoMaterialIncluir.NCaracteresO.focus();
			}
		}
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadRequisicaoMaterialIncluir.php" method="post" name="CadRequisicaoMaterialIncluir">
			<br><br><br><br><br>
			<table cellpadding="3" border="0" summary="">
				<!-- Caminho -->
				<tr>
					<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
					<td align="left" class="textonormal" colspan="2">
						<font class="titulo2">|</font>
						<a href="../index.php">
							<font color="#000000">Página Principal</font>
						</a> > Estoques > Requisição > Incluir
					</td>
				</tr>
				<!-- Fim do Caminho-->
				<!-- Erro -->
				<?php
				if ($Mens == 1) {
					?>
					<tr>
						<td width="100"></td>
						<td align="left" colspan="2">
							<?php ExibeMens($Mensagem, $Tipo, $Troca); ?>
						</td>
					</tr>
					<?php
				}
				?>
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
												INCLUIR - REQUISIÇÃO DE MATERIAL
											</td>
										</tr>
										<tr>
											<td class="textonormal">
												<p align="justify">
													Para incluir uma nova Requisição de Material, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table class="textonormal" border="0" align="left" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Ano</td>
														<td class="textonormal"><?php echo date("Y"); ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
														<td class="textonormal">
															<?php
															# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado e órgão #
															$db = Conexao();

															if ($_SESSION['_cgrempcodi_'] == 0) {
																$sql = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";

																if ($Almoxarifado) {
																	$sql .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
																}
															} else {
																$sql = "SELECT A.CALMPOCODI, A.EALMPODESC ";
																$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B , SFPC.TBLOCALIZACAOMATERIAL C ";
																$sql .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH = 'S') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
																$sql .= "    ON C.CLOCMACODI = D.CLOCMACODI ";
																$sql .= " WHERE A.CALMPOCODI = C.CALMPOCODI AND A.CALMPOCODI = B.CALMPOCODI ";

																if ($Almoxarifado){
																	$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
																}

																$sql .= "   AND B.CORGLICODI in ";
																$sql .= "       (SELECT DISTINCT  CEN.CORGLICODI  ";
																$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
																$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END) ";
																$sql .= "   AND (( TRUE ";
																$sql .= "   AND ( ";
																$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
																$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD')";
																$sql .= "   )";
																$sql .= "   )";

																$dataAtual = date('Y-m-d') . ' 23:59:59';

																$sql .= "				OR B.CORGLICODI IN ( ";
																$sql .= " SELECT CORGLICODI ";
																$sql .= " FROM SFPC.TBLIBERACAOMOVIMENTACAO ";
																$sql .= " WHERE ";
																$sql .= " 	TLIBMODINI BETWEEN '" . $InventarioDataInicial . "' AND '" . $dataAtual . "' ";
																$sql .= " 	AND ";
																$sql .= " 	TLIBMODFIN BETWEEN '" . $dataAtual . "' AND '" . $InventarioDataFinal . "' ";
																$sql .= " GROUP BY CORGLICODI ";
																$sql .= " ORDER BY CORGLICODI ";
																$sql .= " ) ";
																$sql .= "   ) ";
															}

															$sql .= " ORDER BY A.EALMPODESC ";

															$res  = $db->query($sql);

															if (PEAR::isError($res)) {
																$CodErroEmail  = $res->getCode();
																$DescErroEmail = $res->getMessage();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
															} else {
																$Rows = $res->numRows();

																if ($Rows == 1) {
																	$Linha = $res->fetchRow();
																	$Almoxarifado = $Linha[0];
																			
																	echo "$Linha[1]<br>";
																	echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																	echo $DescAlmoxarifado;
																} elseif ($Rows > 1) {
																	echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																	echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																			
																	for ($i=0;$i< $Rows; $i++) {
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																				
																		if ($Linha[0] == $Almoxarifado) {
																			echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		} else {
																			echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																	}

																	echo "</select>\n";
																	$CarregaAlmoxarifado = "";
																} else {
																	echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
																	echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																}
															}

															$db->disconnect();
															?>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Centro de Custo*</td>
														<td class="textonormal">
															<?php
															# Exibe os Centro de Custo #
															$db = Conexao();

															if (($_SESSION['_cgrempcodi_'] != 0) and ($TipoUsuario == "R")) {
																$sqlCC    = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, ";
																$sqlCC   .= "       B.CORGLICODI, B.EORGLIDESC ";
																$sqlCC   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																$sqlCC   .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
																$sqlCC   .= "   AND A.CORGLICODI = B.CORGLICODI  ";
																$sqlCC   .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																$sqlCC   .= "   AND A.CCENPOSEQU IN  ";
																$sqlCC   .= "        ( SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ";
																$sqlCC   .= "       WHERE USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R'))";
																$sqlCC   .= "       ORDER BY B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA	";
															} else {
																$sqlCC    = "SELECT A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA,";
																$sqlCC   .= "       D.CORGLICODI, D.EORGLIDESC";
																$sqlCC   .= "  FROM SFPC.TBCENTROCUSTOPORTAL A,  SFPC.TBGRUPOORGAO B, ";
																$sqlCC   .= "       SFPC.TBGRUPOEMPRESA C, SFPC.TBORGAOLICITANTE D ";
																$sqlCC   .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
																$sqlCC   .= "   AND A.CORGLICODI = B.CORGLICODI AND C.CGREMPCODI = B.CGREMPCODI ";
																$sqlCC   .= "   AND B.CORGLICODI = D.CORGLICODI ";
																$sqlCC	 .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos

																if ($TipoUsuario == "T") {
																	$sqlCC .= " AND C.CGREMPCODI = ".$_SESSION['_cgrempcodi_']."";
																}

																$sqlCC   .= " ORDER BY D.EORGLIDESC,A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA";
															}
																	
															$resCC = $db->query($sqlCC);
																	
															if (PEAR::isError($resCC)) {
																$CodErroEmail  = $resCC->getCode();
																$DescErroEmail = $resCC->getMessage();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC\n\n$DescErroEmail ($CodErroEmail)");
															} else {
																$RowsCC = $resCC->numRows();
																
																if ($RowsCC == 0) {
																	echo "Nenhum Centro de Custo cadastrado";
																} elseif ($RowsCC == 1) {
																	$Linha           = $resCC->fetchRow();
																	$CentroCusto     = $Linha[0];
																	$DescCentroCusto = $Linha[1];
																	$RPA             = $Linha[2];
																	$Detalhamento    = $Linha[3];
																	$Orgao           = $Linha[4];
																	$DescOrgao       = $Linha[5];
																			
																	echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																	echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																	echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																	echo $Detalhamento;
																} else {
																	$Url = "CadIncluirCentroCusto.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&TipoUsuario=$TipoUsuario";
																		
																	if (!in_array($Url,$_SESSION['GetUrl'])) {
																		$_SESSION['GetUrl'][] = $Url;
																	}

																	echo "<a href=\"javascript:AbreJanela('$Url',700,370);\"><img src=\"../midia/lupa.gif\" border=\"0\"></a><br>\n";
																		
																	if ($CentroCusto != "") {
																		# Carrega os dados do Centro de Custo selecionado #
																		$sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA ";
																		$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
																		$sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";
																		$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																				
																		$res  = $db->query($sql);
																				
																		if (PEAR::isError($res)) {
																			$CodErroEmail  = $res->getCode();
																			$DescErroEmail = $res->getMessage();
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																		} else {
																			while ($Linha = $res->fetchRow()) {
																				$DescCentroCusto = $Linha[0];
																				$DescOrgao       = $Linha[1];
																				$Orgao           = $Linha[2];
																				$RPA             = $Linha[3];
																				$Detalhamento    = $Linha[4];
																			}

																			echo $DescOrgao."<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																			echo "RPA ".$RPA."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																			echo $DescCentroCusto."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																			echo $Detalhamento;
																		}
																	}
																}
															}

															$db->disconnect();
															?>
														</td>
														<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data da Requisição*</td>
														<td class="textonormal">
															<?php $URL = "../calendario.php?Formulario=CadRequisicaoMaterialIncluir&Campo=DataRequisicao"; ?>
															<input type="text" name="DataRequisicao" size="10" maxlength="10" value="<?php echo $DataRequisicao; ?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)">
																<img src="../midia/calendario.gif" border="0" alt="">
															</a>
															<font class="textonormal">dd/mm/aaaa</font>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
														<td class="textonormal">
															<font class="textonormal">máximo de 200 caracteres</font>
															<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO; ?>" class="textonormal"><br>
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
																<?php
																$contaItemRequisicao = (is_null($ItemRequisicao)) ? $ItemRequisicao = 0: count($ItemRequisicao);

																if ($contaItemRequisicao != 0 ){
																	sort($ItemRequisicao);
																}
																		
																for ($i=0;$i< $contaItemRequisicao;$i++) {
																	$Dados = explode($SimboloConcatenacaoArray,$ItemRequisicao[$i]);
																	$DescMaterial[$i] = $Dados[0];
																	$Material[$i]     = $Dados[1];
																	$Unidade[$i]      = $Dados[2];
																	$Quantidade[$i]   = $Dados[3];
																			
																	if ($i == 0) {
																		echo "		<tr>\n";
																		echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">ORDEM</td>\n";
																		echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"70%\">DESCRIÇÃO DO MATERIAL</td>\n";
																		echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\">CÓD.RED.</td>\n";
																		echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\">UNIDADE</td>\n";
																		echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"10%\" align=\"center\">QUANTIDADE</td>\n";
																		echo "		</tr>\n";
																	}
																	?>
																	<tr>
																		<td class="textonormal" align="center" width="5%">
																			<?php echo $i+1; ?>
																			<input type="hidden" name="ItemRequisicao[<?php echo $i; ?>]" value="<?php echo $ItemRequisicao[$i]; ?>">
																			<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
																		</td>
																		<td class="textonormal" width="70%">
																			<input type="checkbox" name="CheckItem[<?php echo $i; ?>]" value="<?php echo $i; ?>">
																			<?php
																			$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=$Material[$i]";

																			if (!in_array($Url,$_SESSION['GetUrl'])) {
																				$_SESSION['GetUrl'][] = $Url;
																			}
																			?>
																			<a href="javascript:AbreJanela('<?php=$Url;?>',700,370);">
																				<font color="#000000">
																					<?php
																					$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);

																					echo $Descricao[1];
																					?>
																				</font>
																			</a>
																			<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
																		</td>
																		<td class="textonormal" width="5%" align="center">
																			<?php echo $Material[$i];?>
																		</td>
																		<td class="textonormal" width="10%" align="center">
																			<?php echo $Unidade[$i];?>
																			<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
																		</td>
																		<td class="textonormal" align="center" width="10%">
																			<?php
																			if ($Quantidade[$i] == "") {
																				$Quantidade[$i] = 0;
																			}
																			?>
																			<input type="text" name="Quantidade[<?php echo $i; ?>]" size="11" maxlength="11" value="<?php echo $Quantidade[$i]; ?>" class="textonormal">
																		</td>
																	</tr>
																	<?php
																}
																?>
																<tr>
																	<td class="textonormal" colspan="5" align="center">
																		<?php
																		if ($Almoxarifado) {
																			$Url = "CadIncluirItem.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&PesqApenas=E&Zerados=N&Almoxarifado=$Almoxarifado";

																			echo "<input type=\"button\" name=\"IncluirItem\" value=\"Incluir Item\" class=\"botao\" onclick=\"javascript:AbreJanelaItem('$Url',700,350);\">";
																			echo "<input type=\"button\" name=\"Retirar\" value=\"Retirar Item\" class=\"botao\" onClick=\"javascript:enviar('Retirar');\">";
																		} else {
																			$Url = "CadIncluirItem.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&PesqApenas=E&Zerados=N&Almoxarifado=$Almoxarifado";
																					
																			echo "<input type=\"button\" name=\"IncluirItem\" value=\"Incluir Item\" class=\"botao\" onclick=\"javascript:AbreJanelaItem('$Url',700,350);\" disabled>";
																			echo "<input type=\"button\" name=\"Retirar\" value=\"Retirar Item\" class=\"botao\" onClick=\"javascript:enviar('Retirar');\" disabled>";
																		}
																				
																		if (!in_array($Url,$_SESSION['GetUrl'])) {
																			$_SESSION['GetUrl'][] = $Url;
																		}
																		?>
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
												<input type="hidden" name="InicioPrograma" value="1">
												<input type="hidden" name="TipoUsuario" value="<?php echo $TipoUsuario; ?>">
												<input type="hidden" name="OrgaoUsuario" value="<?php echo $OrgaoUsuario; ?>">
												<input type="button" name="Incluir" value="Incluir Requisição" class="botao" onClick="javascript:enviar('Incluir');">
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