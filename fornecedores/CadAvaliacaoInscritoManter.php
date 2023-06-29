<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAvaliacaoInscritoManter.php
# Objetivo: Programa de Avaliação dos Inscritos - Manter
# Autor:    Rossana Lira
# Data:     17/06/04
# Alterado: Carlos Abreu
# Data:     14/05/2007 - Corrigir direcionamento de página
# OBS.:     Tabulação 2 espaços
# Alterado: Rossana Lira
# Data:     25/05/2007  - Programa foi refeito para ficar melhor estruturado
#                       - Receber comissão e data análise documentação
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
# Ver parte de liberação e as outras situações - e se limpa dados
# Alterado: Ariston Cordeiro
# Data:     09/06/2008 - Novo campo Email 2
# Autor:    Everton Lino
# Data:     28/07/2010 - Adicionada uma função de tratar caracteres
# Alterado: Ariston Cordeiro
# Data:     03/08/2010 - Adicionando sócios de fornecedores
# Alterado: Rodrigo Melo
# Data:     25/05/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
# Alterado: Rodrigo Melo
# Data:     03/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     09/11/2018 - Tarefa Redmine: 206612 - Correção do problema relativo a aspas no nome fantasia
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "../funcoes.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadInscritoAlterar.php' );
AddMenuAcesso( '/oracle/fornecedores/RotDebitoCredorConsulta.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$ProgramaSelecao = $_POST['ProgramaSelecao'];
		$Critica	 = $_POST['Critica'];
		$Botao		= $_POST['Botao'];
		$CNPJ		= $_POST['CNPJ'];
		$CPF		= $_POST['CPF'];
		$NovaSituacao    = $_POST['NovaSituacao'];
		$Comissao	  = $_POST['Comissao'];
		$DataAnaliseDoc  = $_POST['DataAnaliseDoc'];
		$Motivo		= $_POST['Motivo'];
		$Sequencial	= $_POST['Sequencial'];
		$NCaracteres     = $_POST['NCaracteres'];
}else{
		$Botao		= $_GET['Botao'];
		$Irregularidade	 = $_GET['Irregularidade'];
		$Sequencial      = $_GET['Sequencial'];
		$NovaSituacao		 = $_GET['NovaSituacao'];
		$Critica         = $_GET['Critica'];
		$ProgramaSelecao = urldecode($_GET['ProgramaSelecao']);
		$CPF_CNPJ        = $_GET['CPF_CNPJ'];
		if( strlen($CPF_CNPJ) == 14 ){
				$CNPJ = $CPF_CNPJ;
		}elseif( strlen($CPF_CNPJ) == 11 ){
				$CPF = $CPF_CNPJ;
		}
		$Mens     			 = $_GET['Mens'];
		$Tipo			     	 = $_GET['Tipo'];
		$Mensagem     	 = urldecode($_GET['Mensagem']);
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Inicialização de variáveis de erro
if(is_null($Mens)){
	$Mens			= 0;
	$Tipo			= 0;
	$Mensagem = "Informe: ";
}


if( (is_null($Sequencial)) ){
		header("location: /portalcompras/fornecedores/CadAvaliacaoInscritoSelecionar.php");
		exit;
}

$db = Conexao();
if( $Botao == "ManterInscrito" ){
		# Verifica se o Fornecedor está cadastrado em Fornecedor Credenciado #
	  $sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO ";
		if( $CNPJ != 0 ){
	  		$sql .= "WHERE AFORCRCCGC = '$CNPJ' ";
		}else{
	  		$sql .= "WHERE AFORCRCCPF = '$CPF' ";
		}
	  $result = $db->query($sql);
	  if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Qtd = $result->fetchRow();
				if( $Qtd[0] > 0 ){
						$Mensagem  = "<a href=\"javascript:document.Avaliacao.NovaSituacao.focus();\" class=\"titulo2\">Este Fornecedor não pode ser avaliado pois já é um Fornecedor Cadastrado</a>";
						$Url = "CadAvaliacaoInscritoManter.php?Mens=1&Tipo=2&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}else{
						# Permite a alteração dos dados cadastrais do fornecedor inscrito #
						LimparSessao();
						$Url = "CadInscritoAlterar.php?ProgramaSelecao=".urlencode($ProgramaSelecao)."&Sequencial=$Sequencial&Critica=&Botao=";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
}elseif( $Botao == "Voltar" ){
		header("location: $ProgramaSelecao");
		exit;
}

# Entrando pela primeira vez no programa e não passou pela rotina de débito
if ($Critica == "" and $Irregularidade == "") {
	$Irregularidade = "";

	# Busca os Dados da Tabela de Inscritos de Acordo com o código
	$sql  = " SELECT PRE.APREFOSEQU, PRE.APREFOCCGC, PRE.APREFOCCPF, PRE.NPREFORAZS,";
	$sql .= " PRE.DPREFOGERA, PRE.EPREFOMOTI, SIT.CPREFSCODI, SIT.EPREFSDESC ";
	$sql .= " FROM SFPC.TBPREFORNECEDOR PRE, SFPC.TBPREFORNTIPOSITUACAO SIT ";
 	$sql .= " WHERE APREFOSEQU = $Sequencial AND PRE.CPREFSCODI = SIT.CPREFSCODI";

	$result = $db->query($sql);

	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}

	$Linha = $result->fetchRow();
	$CNPJ			             = $Linha[1];
	$CPF			             = $Linha[2];
	$_SESSION['Razao']           = $Linha[3];
	$_SESSION['DataInscricao']   = DataBarra($Linha[4]);
	$Motivo				         = strtoupper2($Linha[5]);
	$_SESSION['CodSituacaoAnt']  = $Linha[6];
	$_SESSION['DescSituacaoAnt'] = $Linha[7];

	$NCaracteres = strlen($Motivo);

	# Verifica a Situação Tributária do Inscrito #
	$NomePrograma = urlencode("CadAvaliacaoInscritoManter.php");

	# Faz verificação de débito do credor
	if ($ProgramaSelecao != "CadAvaliacaoInscritoSelecionarLib.php") {
		if ($CNPJ != 0) {
			$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&ProgramaSelecao=".urlencode($ProgramaSelecao)."&TipoDoc=1&CPF_CNPJ=$CNPJ&Sequencial=$Sequencial&NovaSituacao=$NovaSituacao&Botao=$Botao";

			if (!in_array("/oracle/".$Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = "/oracle/".$Url;
			}

			//Redireciona($Url);
			//exit;
		} else {
			$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&ProgramaSelecao=".urlencode($ProgramaSelecao)."&TipoDoc=2&CPF_CNPJ=$CPF&Sequencial=$Sequencial&NovaSituacao=$NovaSituacao&Botao=$Botao";

			if (!in_array("/oracle/".$Url,$_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = "/oracle/".$Url;
			}

			//Redireciona($Url);
			//exit;
		}
	}
} else {
	if ($Critica == 1) {
	  	$Motivo = strtoupper2(trim($Motivo));

		if (($NovaSituacao	 == "")) {
			$Mens 		= 1;
			$Tipo       = 2;
			$Mensagem  .= "<a href=\"javascript:document.Avaliacao.NovaSituacao.focus();\" class=\"titulo2\">Nova Situação</a>";
		}

		# Nova Situação do Fornecedor Inscrito = Pendente ou Indeferido ou Excluído#
		if (($NovaSituacao == 3 or $NovaSituacao == 4 or $NovaSituacao == 5) and $Motivo == "") {
			$Mens 		= 1;
			$Tipo       = 2;
			$Mensagem  .= "<a href=\"javascript:document.Avaliacao.Motivo.focus();\" class=\"titulo2\">Motivo</a>";
		}

		$DataAnaliseDocInv = substr($DataAnaliseDoc,6,4)."-".substr($DataAnaliseDoc,3,2)."-".substr($DataAnaliseDoc,0,2);
		
		# Nova Situação do Fornecedor Inscrito = Aprovado ou Pendente ou Indeferido #
		if (($NovaSituacao== 2) or ($NovaSituacao== 3) or ($NovaSituacao== 4)) {
			if ($Comissao == "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens 		= 1;
				$Tipo       = 2;
				$Mensagem  .= "<a href=\"javascript:document.Avaliacao.Comissao.focus();\" class=\"titulo2\">Comissão Responsável pela Análise</a>";
			}

			if ($DataAnaliseDoc == "") {
				if ($Mens == 1) {
					$Mensagem .= ", ";
				}

				$Mens 		= 1;
				$Tipo       = 2;
				$Mensagem  .= "<a href=\"javascript:document.Avaliacao.DataAnaliseDoc.focus();\" class=\"titulo2\">Data da Análise da Documentação</a>";
			} else {
				if ($DataAnaliseDocInv > date("Y-m-d")) {
					if ($Mens == 1) {
						$Mensagem .= ", ";
					}

					$Mens      = 1;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.Avaliacao.DataAnaliseDoc.focus();\" class=\"titulo2\">Data da Análise menor que Data Atual</a>";
				} else {
					$DataValida = ValidaData($DataAnaliseDoc);
					
					if ($DataValida != "") {
						if ($Mens == 1) {
							$Mensagem .= ", ";
						}

						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Avaliacao.DataAnaliseDoc.focus();\" class=\"titulo2\">Data da Análise Válida</a>";
					}
				}
			}
		}

		if ($Mens == 0) {
			$Grupo		 = $_SESSION['_cgrempcodi_'];
			$Usuario	 = $_SESSION['_cusupocodi_'];
			$DataAtual = date("Y-m-d H:i:s");

			# Inscrito aprovado, mas com irregularidade
			if (($NovaSituacao == 2) and ($Irregularidade == "S" ) and ($ProgramaSelecao != "CadAvaliacaoInscritoSelecionarLib.php")) {
				$Mens 		= 1;
				$Tipo       = 2;
				$Mensagem   = "<a href=\"javascript:document.Avaliacao.NovaSituacao.focus();\" class=\"titulo2\">Este Fornecedor possui alguma irregularidade com a Prefeitura</a>";
			} else {
				# Se nova situação é excluído

				if ($NovaSituacao == 5 ) {
					# Grava o Fornecedor Inscrito com situação de excluído e apaga o restante dos dados #
					$db->query("BEGIN TRANSACTION");

					$sql  = "UPDATE SFPC.TBPREFORNECEDOR ";
					$sql .= "   SET NPREFOSENH = NULL, NPREFOFANT = NULL, CCEPPOCODI = NULL, ";
					$sql .= "       EPREFOLOGR = NULL, APREFONUME = NULL, EPREFOCOMP = NULL, ";
					$sql .= "       EPREFOBAIR = NULL, NPREFOCIDA = NULL, CPREFOESTA = NULL, ";
					$sql .= "       APREFOCDDD = NULL, APREFOTELS = NULL, APREFONFAX = NULL, ";
					$sql .= "       NPREFOMAIL = NULL, NPREFOMAI2 = NULL, NPREFOCONT = NULL, NPREFOCARG = NULL, ";
					$sql .= "       APREFODDDC = NULL, APREFOTELC = NULL, APREFOREGJ = NULL, ";
					$sql .= "       DPREFOREGJ = NULL, APREFOINES = NULL, APREFOINME = NULL, ";
					$sql .= "       APREFOINSM = NULL, VPREFOCAPS = NULL, VPREFOCAPI = NULL, ";
					$sql .= "       VPREFOPATL = NULL, VPREFOINLC = NULL, VPREFOINLG = NULL, ";
					$sql .= "       APREFOENTR = NULL, NPREFOENTP = NULL, DPREFOVIGE = NULL, ";
					$sql .= "       CPREFSCODI = '$NovaSituacao', EPREFOMOTI = '$Motivo', CGREMPCODI = $Grupo, ";
					$sql .= "       CUSUPOCODI = $Usuario, TPREFOULAT = '$DataAtual' ";
					$sql .= " WHERE APREFOSEQU = $Sequencial ";

					$result = $db->query($sql);
								
					if (PEAR::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						# Apaga grupos do Fornecedor #
						$db->query("BEGIN TRANSACTION");

						$sql  = "DELETE FROM SFPC.TBGRUPOPREFORNECEDOR";
						$sql .= " WHERE APREFOSEQU = $Sequencial";

						$result = $db->query($sql);
						
						if (PEAR::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}

						# Apaga Conta Bancária do Fornecedor #
						$sql  = "DELETE FROM SFPC.TBPREFORNCONTABANCARIA ";
						$sql .= " WHERE APREFOSEQU = $Sequencial";

						$result = $db->query($sql);
										
						if (PEAR::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}

						# Apaga Certidões do Fornecedor #
						$sql  = "DELETE FROM SFPC.TBPREFORNCERTIDAO ";
						$sql .= " WHERE APREFOSEQU = $Sequencial";

						$result = $db->query($sql);
						
						if (PEAR::isError($result)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}

						$db->query("COMMIT");
						$db->query("END TRANSACTION");

						$Critica  = 0;
						$Mensagem = "Avaliação foi Informada com Sucesso. Alguns dados do Fornecedor Inscrito foram Excluídos";
						$Url = "$ProgramaSelecao?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";

						if (!in_array($Url,$_SESSION['GetUrl'])) {
							$_SESSION['GetUrl'][] = $Url;
						}

						header("location: ".$Url);
						exit;
					}
				} else {
					# Se nova situação é diferente de excluído
					# Verifica se o Fornecedor está cadastrado em Fornecedor Credenciado #
					$sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO ";
					
					if ($CNPJ != 0) {
						$sql .= "WHERE AFORCRCCGC = '$CNPJ' ";
					} else {
						$sql .= "WHERE AFORCRCCPF = '$CPF' ";
					}

					$result = $db->query($sql);

					if (PEAR::isError($result)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$Qtd = $result->fetchRow();

						if ($Qtd[0] > 0) {
							$Mens 	   = 1;
							$Tipo      = 2;
							$Mensagem  = "<a href=\"javascript:document.Avaliacao.NovaSituacao.focus();\" class=\"titulo2\">Este Fornecedor não pode ser avaliado pois já é um Fornecedor Cadastrado</a>";
						} else {
							# Grava o Fornecedor Inscrito como aprovado na tabela de fornecedor credenciado #
							if ($NovaSituacao == 2) {
								$db->query("BEGIN TRANSACTION");

								# Busca os Dados da Tabela de Inscritos de Acordo com o código #
								$sql = "SELECT	APREFOCCGC, APREFOCCPF, APREFOIDEN, NPREFOORGU, NPREFOSENH,
												NPREFORAZS, NPREFOFANT, CCEPPOCODI, CCELOCCODI, EPREFOLOGR,
												APREFONUME, EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA,
												APREFOCDDD, APREFOTELS, APREFONFAX, NPREFOMAIL, APREFOCPFC,
												NPREFOCONT, NPREFOCARG, APREFODDDC, APREFOTELC, APREFOREGJ,
												DPREFOREGJ, APREFOINES, APREFOINME, APREFOINSM, VPREFOCAPS,
												VPREFOCAPI, VPREFOPATL, VPREFOINLC, VPREFOINLG, DPREFOULTB,
												DPREFOCNFC, APREFOENTR, NPREFOENTP, DPREFOVIGE, APREFOENTT,
												DPREFOGERA, DPREFOEXPS, FPREFOMEPP, VPREFOINDI, VPREFOINSO,
												NPREFOMAI2, FPREFOTIPO
										FROM	SFPC.TBPREFORNECEDOR
										WHERE	APREFOSEQU = $Sequencial ";

								$result = $db->query($sql);
								
								if (PEAR::isError($result)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
									$Linha = $result->fetchRow();

									$CNPJ			  = $Linha[0];
									$CPF			  = $Linha[1];
									$MicroEmpresa	  = $Linha[42];
									$Identidade 	  = $Linha[2];
									$OrgaoUF    	  = $Linha[3];
									$Senha			  = $Linha[4];
									$Razao       	  = $Linha[5];
									$NomeFantasia	  = $Linha[6];
									$CEP			  = $Linha[7];
									$Localidade		  = $Linha[8];
									$Logradouro 	  = $Linha[9];
									$Numero    		  = $Linha[10];
									$Complemento	  = $Linha[11];
									$Bairro   	 	  = $Linha[12];
									$Cidade 		  = $Linha[13];
									$UF       		  = $Linha[14];
									$DDD       		  = $Linha[15];
									$Telefone	 	  = $Linha[16];
									$Fax      	   	  = $Linha[17];
									$Email  		  = $Linha[18];
									$Email2  		  = $Linha[45];
									$CPFContato 	  = $Linha[19];
									$NomeContato 	  = $Linha[20];
									$CargoContato 	  = $Linha[21];
									$DDDContato 	  = $Linha[22];
									$TelefoneContato  = $Linha[23];
									$RegistroJunta	  = $Linha[24];
									$DataRegistro	  = $Linha[25];
									$InscEstadual	  = $Linha[26];
									$InscMercantil	  = $Linha[27];
									$InscOMunic		  = $Linha[28];
									$CapSocial		  = $Linha[29];
									$CapIntegralizado = $Linha[30];
									$Patrimonio		  = $Linha[31];
									$IndLiqCorrente	  = $Linha[32];
									$IndLiqGeral	  = $Linha[33];
									$IndEndividamento = $Linha[43];
									$IndSolvencia     = $Linha[44];
									$DataBalanco	  = $Linha[34];
									$DataNegativa	  = $Linha[35];
									$RegistroEntidade = $Linha[36];
									$NomeEntidade	  = $Linha[37];
									$DataVigencia	  = $Linha[38];
									$RegistroTecnico  = $Linha[39];
									$DataInscricao	  = $Linha[40];
									$DataExpiracao	  = $Linha[41];
									$HabilitacaoTipo  = $Linha[46];

									# Busca dos dados da tabela de certidão obrigatória de inscritos #
									$sqlCertOb  = "SELECT A.CTIPCECODI, A.DPREFCVALI, B.FTIPCEOBRI ";
									$sqlCertOb .= "  FROM SFPC.TBPREFORNCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
									$sqlCertOb .= " WHERE A.APREFOSEQU = $Sequencial ";
									$sqlCertOb .= "   AND A.CTIPCECODI = B.CTIPCECODI AND B.FTIPCEOBRI = 'S' ";
									$sqlCertOb .= "	ORDER BY 1";

									$resCertOb  = $db->query($sqlCertOb);
									
									if (PEAR::isError($resCertOb)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCertOb");
									} else {
										$Rows = $resCertOb->numRows();

										if ($Rows != 0) {
											for ($i=0; $i<$Rows;$i++) {
												$Linha = $resCertOb->fetchRow();
												$CertidaoObrigatoria[$i] = $Linha[0];
												$DataCertidaoOb[$i]		 = $Linha[1];
											}
										}
									}

									# Busca dos dados da tabela de certidão Complementar de inscritos #
									$sqlCertOp  = "SELECT A.CTIPCECODI, A.DPREFCVALI, B.FTIPCEOBRI ";
									$sqlCertOp .= "  FROM SFPC.TBPREFORNCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
									$sqlCertOp .= " WHERE A.APREFOSEQU = $Sequencial ";
									$sqlCertOp .= "   AND A.CTIPCECODI = B.CTIPCECODI AND B.FTIPCEOBRI = 'N'";
									$sqlCertOp .= " ORDER BY 1";

									$resCertOp = $db->query($sqlCertOp);

									if (PEAR::isError($resCertOp)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCertOp");
									} else {
										$Rows = $resCertOp->numRows();

										if ($Rows != 0) {
											for ($i=0; $i<$Rows;$i++) {
												$Linha                    = $resCertOp->fetchRow();
												$CertidaoComplementar[$i] = $Linha[0];
												$DataCertidaoOp[$i]	      = $Linha[1];
											}
										}
									}

									# Busca os Dados da Tabela de Conta Bancária de acordo com o Sequencial do inscrito #
									$sqlBan  = "SELECT CPRECOBANC, CPRECOAGEN, CPRECOCONT FROM SFPC.TBPREFORNCONTABANCARIA ";
									$sqlBan .= "WHERE APREFOSEQU = $Sequencial";

									$resBan = $db->query($sqlBan);

									if (PEAR::isError($resBan)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlBan");
									} else {
										$numBan	= $resBan->numRows();

										for ($i=0;$i<$numBan;$i++) {
											$Linha = $resBan->fetchRow();

											if ($i == 0) {
												$Banco1			= $Linha[0];
												$Agencia1		= $Linha[1];
												$ContaCorrente1	= $Linha[2];
											} elseif ($i == 1) {
												$Banco2			= $Linha[0];
												$Agencia2		= $Linha[1];
												$ContaCorrente2	= $Linha[2];
											}
										}
									}

									# Busca dos dados da tabela de autorização específica #
									$sqlAuto  = "SELECT NPREFANOMA, APREFANUMA, DPREFAVIGE ";
									$sqlAuto .= "  FROM SFPC.TBPREFORNAUTORIZACAOESPECIFICA ";
									$sqlAuto .= " WHERE APREFOSEQU = $Sequencial ";
									$sqlAuto .= " ORDER BY NPREFANOMA, APREFANUMA";

									$resAuto = $db->query($sqlAuto);

									if (PEAR::isError($resAuto)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAuto");
									} else {
										$Rows = $resAuto->numRows();

										if ($Rows != 0) {
											for ($i=0; $i<$Rows;$i++) {
												$Linha = $resAuto->fetchRow();
												$AutorizacaoNome[$i]     = $Linha[0];
												$AutorizacaoRegistro[$i] = $Linha[1];
												$AutorizacaoData[$i]     = $Linha[2];
											}
										}
									}

									# Busca dos dados da tabela de grupos de materiais de inscritos #
									$sqlCm  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
									$sqlCm .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
									$sqlCm .= " WHERE A.APREFOSEQU = $Sequencial ";
									$sqlCm .= "   AND A.CGRUMSCODI = B.CGRUMSCODI AND B.FGRUMSTIPO = 'M' ";
									$sqlCm .= " ORDER BY 1,3";

									$resCm = $db->query($sqlCm);

									if (PEAR::isError($resCm)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCm");
									} else {
										$Rows = $resCm->numRows();

										if ($Rows != 0) {
											for ($i=0; $i<$Rows;$i++) {
												$Linha	= $resCm->fetchRow();
												$Materiais[$i] = "M#".$Linha[1];
											}
										}
									}

									# Mostra os grupos de serviços já cadastrados do Inscrito #
									$sqlCs  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
									$sqlCs .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
									$sqlCs .= " WHERE A.APREFOSEQU = $Sequencial ";
									$sqlCs .= "   AND A.CGRUMSCODI = B.CGRUMSCODI AND B.FGRUMSTIPO = 'S' ";
									$sqlCs .= " ORDER BY 1,3";

									$resCs = $db->query($sqlCs);

									if (PEAR::isError($resCs)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCs");
									} else {
										$Rows = $resCs->numRows();

										if ($Rows != 0) {
											for ($i=0; $i<$Rows;$i++) {
												$Linha = $resCs->fetchRow();
												$Servicos[$i] = "M#".$Linha[1];
											}
										}
									}

									# Coloca o NULL nos campos não obrigatórios que não foram preenchidos #
									# Atribuindo Valor NULL - Formulário A #

									if ($CNPJ == "") { $CNPJAtual = "NULL"; } else {$CNPJAtual = "'" . $CNPJ . "'"; }
									if ($CPF  == "") { $CPFAtual  = "NULL"; }else{ $CPFAtual        = "'".$CPF."'"; }
																    if( $MicroEmpresa     == "" ){ $MicroEmpresa     = "NULL"; }else{ $MicroEmpresa    = "'".$MicroEmpresa."'"; }
																    if( $Identidade       == "" ){ $Identidade       = "NULL"; }else{ $Identidade      = "'".$Identidade."'"; }
																    if( $OrgaoUF          == "" ){ $OrgaoUF          = "NULL"; }else{ $OrgaoUF         = "'".$OrgaoUF."'"; }

																    if( $NomeFantasia     == "" ){ $NomeFantasia     = "NULL"; }else{ $NomeFantasia    = "".$NomeFantasia.""; }
														 		 		if( $CEP              == "" ){ $CEP              = "NULL"; }else{ $Localidade      = "NULL"; }
																    if( $DDD              == "" ){ $DDD              = "NULL"; }
																    if( $Numero           == "" ){ $Numero           = "NULL"; }
																    if( $Complemento      == "" ){ $Complemento      = "NULL"; }else{ $Complemento     = "'".$Complemento."'"; }
																    if( $Telefone         == "" ){ $Telefone         = "NULL"; }else{ $Telefone        = "'".$Telefone."'"; }
																    if( $Email == "" ){ $Email = "NULL"; }else{ $Email = "'".$Email."'"; }
																    if( $Email2 == "" ){ $Email2 = "NULL"; }else{ $Email2 = "'".$Email2."'"; }
																    if( $Fax              == "" ){ $Fax              = "NULL"; }else{ $Fax             = "'".$Fax."'"; }
																    if( $CPFContato       == "" ){ $CPFContato       = "NULL"; }else{ $CPFContato      = "'".$CPFContato."'"; }
																    if( $NomeContato      == "" ){ $NomeContato      = "NULL"; }else{ $NomeContato     = "'".addslashes($NomeContato)."'"; }
																    if( $CargoContato     == "" ){ $CargoContato     = "NULL"; }else{ $CargoContato    = "'".addslashes($CargoContato)."'"; }
																    if( $DDDContato       == "" ){ $DDDContato       = "NULL"; }
																    if( $TelefoneContato  == "" ){ $TelefoneContato  = "NULL"; }else{ $TelefoneContato = "'".$TelefoneContato."'"; }
																    # Atribuindo Valor NULL - Formulário B #
																    if( $InscMercantil    == "" ){ $InscMercantil    = "NULL"; }
																    if( $InscEstadual     == "" ){ $InscEstadual     = "NULL"; }
																    if( $InscOMunic       == "" ){ $InscOMunic       = "NULL"; }
																    # Atribuindo Valor NULL - Formulário C #
																    if( $CapSocial        == "" ){ $CapSocial        = "NULL"; }
																    if( $CapIntegralizado == "" ){ $CapIntegralizado = "NULL"; }
																    if( $Patrimonio       == "" ){ $Patrimonio       = "NULL"; }
																		if( $IndLiqCorrente   == "" ){ $IndLiqCorrente   = "NULL"; }
																    if( $IndLiqGeral      == "" ){ $IndLiqGeral      = "NULL"; }
																    if( $IndEndividamento == "" ){ $IndEndividamento      = "NULL"; }
																    if( $IndSolvencia     == "" ){ $IndSolvencia         = "NULL"; }
																		if( $DataBalanco      == "" ){ $DataBalanco      = "NULL"; }else{ $DataBalanco     = "'".$DataBalanco."'"; }
																		if( $DataNegativa     == "" ){ $DataNegativa     = "NULL"; }else{ $DataNegativa    = "'".$DataNegativa."'"; }
																		# Atribuindo Valor NULL - Formulário D #
																		if( $NomeEntidade     == "" ){ $NomeEntidade     = "NULL"; }else{ $NomeEntidade    = "'".$NomeEntidade."'"; }
																    if( $RegistroEntidade == "" ){ $RegistroEntidade = "NULL"; }
																    if( $DataVigencia     == "" ){ $DataVigencia     = "NULL"; }else{ $DataVigencia    = "'".$DataVigencia."'"; }
																		if( $RegistroTecnico  == "" ){ $RegistroTecnico  = "NULL"; }
																		if( $DataExpiracao    == "" ){ $DataExpiracao    = "NULL"; }else{ $DataExpiracao     = "'".$DataExpiracao."'"; }
																		if( $RegistroJunta    == "" or $RegistroJunta    == NULL or $RegistroJunta    == 0 ){ $RegistroJunta    = "NULL"; }else{ $RegistroJunta     = "'".$RegistroJunta."'"; }
																		if( $DataRegistro    == "" or $DataRegistro    == NULL or $DataRegistro    == 0 ){ $DataRegistro    = "NULL"; }else{ $DataRegistro    = "'".$DataRegistro."'"; }
																		#Comissão responsável pela análise
																		if( $Comissao == "" ){
																				$ComissaoAnalise = "NULL";
																		} else {
																				$ComissaoAnalise = $Comissao;
																		}
																		#Atualiza a data da análise
																		if( $DataAnaliseDoc == "" ){
																				$DataAnalise = "NULL";
																		} else {
																				$DataAnalise = $DataAnaliseDoc;
																		}

																		# Recupera a último Sequencial e incrementa mais um #
																	  $sqlfor = "SELECT MAX(AFORCRSEQU) AS Maximo FROM SFPC.TBFORNECEDORCREDENCIADO";
																	  $resfor = $db->query($sqlfor);
																	  if( PEAR::isError($resfor) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
																		}
																		$Maximo  			  = $resfor->fetchRow();
																		$SequencialForn = $Maximo[0] + 1;
																		$DataAtual      = date("Y-m-d H:i:s");

																		# Grava os Dados do Fornecedor Inscrito na Tabela de Fornecedor Credenciado #
																		$sql    = "
																			INSERT INTO
																			SFPC.TBFORNECEDORCREDENCIADO (
																				AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN,
																				NFORCRORGU, NFORCRSENH, NFORCRRAZS, NFORCRFANT, CCEPPOCODI,
																				CCELOCCODI, EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR,
																				NFORCRCIDA, CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX,
																				NFORCRMAIL, AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC,
																				AFORCRTELC, AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME,
																				AFORCRINSM, VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC,
																				VFORCRINLG, DFORCRULTB, DFORCRCNFC, AFORCRENTR, NFORCRENTP,
																				DFORCRVIGE, AFORCRENTT, DFORCRGERA, CGREMPCODI, CUSUPOCODI,
																				FFORCRCUMP, AFORCRNTEN, DFORCREXPS, TFORCRULAT, CCOMLICODI,
																				DFORCRANAL, FFORCRMEPP, VFORCRINDI, VFORCRINSO, NFORCRMAI2,
																				FFORCRTIPO

																			) VALUES ( ";
																		$sql   .= "$SequencialForn, $Sequencial, $CNPJAtual, $CPFAtual, $Identidade, ";
																		$sql   .= "$OrgaoUF, '$Senha', '".addslashes($Razao)."', '".addslashes($NomeFantasia)."', $CEP, "; //".htmlSQLchars($Razao)."
																		$sql   .= "$Localidade, '$Logradouro', $Numero, $Complemento, '$Bairro', ";
																		$sql   .= "'$Cidade', '$UF', $DDD, $Telefone, $Fax, ";
																		$sql   .= "$Email, $CPFContato, $NomeContato, $CargoContato, $DDDContato, ";
																		$sql   .= "$TelefoneContato, $RegistroJunta, $DataRegistro, $InscEstadual, $InscMercantil, ";
																		$sql   .= "$InscOMunic, $CapSocial, $CapIntegralizado, $Patrimonio, $IndLiqCorrente, ";
																		$sql   .= "$IndLiqGeral, $DataBalanco, $DataNegativa, $RegistroEntidade, $NomeEntidade, ";
																		$sql   .= "$DataVigencia, $RegistroTecnico, '".substr($DataAtual,0,10)."', $Grupo, $Usuario, ";
																		$sql   .= "'S', 0, $DataExpiracao, '$DataAtual', $Comissao, ";
																		$sql   .= "'$DataAnaliseDocInv', $MicroEmpresa, $IndEndividamento, $IndSolvencia, $Email2, ";
																		$sql   .= "'$HabilitacaoTipo' )";

																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																		    $db->query("ROLLBACK");
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$DataCHF = $DataAnalise;
																				$DataCHF = date("Y-m-d");
																				# Grava o CHF do fornecedor na tabela SFPC.TBFORNECEDORCHF com validade de um ano #
																				$sql    		 = "INSERT INTO SFPC.TBFORNECEDORCHF ( ";
																				$sql  			.= "AFORCRSEQU, CUSUPOCODI, CGREMPCODI, DFORCHGERA, DFORCHVALI, TFORCHULAT ";
																				$sql   			.= " ) VALUES ( ";
																				$sql  			.= "$SequencialForn, $Usuario, $Grupo, '".date("Y-m-d")."', '$DataCHF', '$DataAtual')  ";

																				$result 		 = $db->query($sql);
																				
																				if ( PEAR::isError($result) ){
																						$db->query("ROLLBACK");
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						# Grava o Fornecedor Inscrito com situação de aprovado e apaga o restante dos dados #
																						$sql    = "UPDATE SFPC.TBPREFORNECEDOR ";
																						$sql   .= "   SET NPREFOSENH = NULL, NPREFOFANT = NULL, CCEPPOCODI = NULL, ";
																						$sql   .= "       EPREFOLOGR = NULL, APREFONUME = NULL, EPREFOCOMP = NULL, ";
																						$sql   .= "       EPREFOBAIR = NULL, NPREFOCIDA = NULL, CPREFOESTA = NULL, ";
																						$sql   .= "       APREFOCDDD = NULL, APREFOTELS = NULL, APREFONFAX = NULL, ";
																						$sql   .= "       NPREFOMAIL = NULL, NPREFOCONT = NULL, NPREFOCARG = NULL, ";
																						$sql   .= "       APREFODDDC = NULL, APREFOTELC = NULL, APREFOREGJ = NULL, ";
																						$sql   .= "       DPREFOREGJ = NULL, APREFOINES = NULL, APREFOINME = NULL, ";
																						$sql   .= "       APREFOINSM = NULL, VPREFOCAPS = NULL, VPREFOCAPI = NULL, ";
																						$sql   .= "       VPREFOPATL = NULL, VPREFOINLC = NULL, VPREFOINLG = NULL, ";
																						$sql   .= "       APREFOENTR = NULL, NPREFOENTP = NULL, DPREFOVIGE = NULL, ";
																						$sql   .= "       CPREFSCODI = '$NovaSituacao', EPREFOMOTI = '$Motivo',  ";
																						$sql   .= "       CGREMPCODI = $Grupo, CUSUPOCODI = $Usuario, TPREFOULAT ='$DataAtual', ";
																						$sql   .= "       CCOMLICODI = $Comissao, DPREFOANAL = '$DataAnaliseDocInv',";
																						$sql   .= "       FPREFOMEPP = NULL, VPREFOINDI = NULL ";
																						$sql   .= " WHERE APREFOSEQU = $Sequencial";
																						$result = $db->query($sql);
																						if( PEAR::isError($result) ){
																								$db->query("ROLLBACK");
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}else{
																								# Inserindo Contas Bancárias #
																								if( $Banco1 != "" and $Agencia1 != "" and $ContaCorrente1 != "" ){
																										$sql    = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
																										$sql   .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
																										$sql   .= ") VALUES ( ";
																										$sql   .= "$SequencialForn, '$Banco1', '$Agencia1', '$ContaCorrente1', '$DataAtual' )";
																										$result = $db->query($sql);
																										if( PEAR::isError($result) ){
																												$db->query("ROLLBACK");
																												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																										}else{
																												if( $Banco2 != "" and $Agencia2 != "" and $ContaCorrente2 != "" ){
																														$sql    = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
																														$sql   .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
																														$sql   .= ") VALUES ( ";
																														$sql   .= "$SequencialForn, '$Banco2', '$Agencia2', '$ContaCorrente2', '$DataAtual' )";
																														$result = $db->query($sql);
																														if( PEAR::isError($result) ){
																																$db->query("ROLLBACK");
																																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																														}
																												}
																										}
																								}

																								# Inserindo sócios do fornecedor a partir dos sócios pré-fornecedor #
																								$sql    = "
																									insert into SFPC.TBsociofornecedor
																									(aforcrsequ, asoforcada, nsofornome, fsofortcad, tsoforulat)
																									(
																										select
																											'".$SequencialForn."', asoprecada, nsoprenome, fsopretcad, '".$DataAtual."'
																											from SFPC.TBsocioprefornecedor
																											where aprefosequ = '".$Sequencial."'
																									)
																								";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}



																								# Inserindo Certidões Obrigatórias #
																								if( count($CertidaoObrigatoria) != 0 ){
																										for( $i=0; $i<count($CertidaoObrigatoria); $i++ ){
																												$sql    = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
																												$sql   .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
																												$sql   .= ") VALUES ( ";
																												$sql   .= "$SequencialForn, $CertidaoObrigatoria[$i], '$DataCertidaoOb[$i]', '$DataAtual' )";
																												$result = $db->query($sql);
																												if( PEAR::isError($result) ){
																														$db->query("ROLLBACK");
																														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												}
																										}
																								}
																								# Inserindo Certidões Complementares #
																								if( count($CertidaoComplementar) != 0 ){
																										for( $i=0; $i<count($CertidaoComplementar); $i++ ){
																												$sql    = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
																												$sql   .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
																												$sql   .= ") VALUES ( ";
																												$sql   .= "$SequencialForn, $CertidaoComplementar[$i], '$DataCertidaoOp[$i]', '$DataAtual' )";
																												$result = $db->query($sql);
																												if( PEAR::isError($result) ){
																														$db->query("ROLLBACK");
																														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												}
																										}
																								}
																								# Inserindo Autorização #
																								if( count($AutorizacaoNome) != 0 ){
																										for( $i=0; $i< count($AutorizacaoNome); $i++ ){
																												$sql    = "INSERT INTO SFPC.TBFORNAUTORIZACAOESPECIFICA ( ";
																												$sql   .= "AFORCRSEQU, NFORAENOMA, AFORAENUMA, DFORAEVIGE, TFORAEULAT ";
																												$sql   .= ") VALUES ( ";
																												$sql   .= "$SequencialForn, '$AutorizacaoNome[$i]', $AutorizacaoRegistro[$i], '$AutorizacaoData[$i]', '$DataAtual' ) ";
																												$result = $db->query($sql);
																												if( PEAR::isError($result) ){
																														$db->query("ROLLBACK");
																				    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												}
																										}
																								}
																								# Incluindo Grupos de Fornecimento #
																								if( count($Materiais) != 0 ){
																										for( $i=0; $i<count($Materiais); $i++ ){
																												$GrupoMaterial = explode("#",$Materiais[$i]);
																												$sql    = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
																												$sql   .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
																												$sql   .= ") VALUES ( ";
																												$sql   .= "$GrupoMaterial[1], $SequencialForn, '$DataAtual' )";
																												$result = $db->query($sql);
																												if( PEAR::isError($result) ){
																														$db->query("ROLLBACK");
																														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												}
																										}
																								}
																								if( count($Servicos) != 0 ){
																										for( $i=0; $i<count($Servicos); $i++ ){
																												$GrupoServico = explode("#",$Servicos[$i]);
																												$sql    = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
																												$sql   .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
																												$sql   .= ") VALUES ( ";
																												$sql   .= "$GrupoServico[1],  $SequencialForn, '$DataAtual' )";
																												$result = $db->query($sql);
																												if( PEAR::isError($result) ){
																														$db->query("ROLLBACK");
																														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																												}
																										}
																								}
																								# Incluindo Situação do Fornecedor como Cadastrado #
																								$sql    = "INSERT INTO SFPC.TBFORNSITUACAO ( AFORCRSEQU, CFORTSCODI, EFORSIMOTI, ";
																								$sql   .= "CGREMPCODI, CUSUPOCODI, DFORSISITU, TFORSIULAT ) VALUES ( $SequencialForn, ";
																								$sql   .= " 1, NULL, $Grupo, $Usuario, '$DataAtual','$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}else{
																										$db->query("COMMIT");
																										$db->query("END TRANSACTION");
																							      $Mensagem = "Avaliação do Fornecedor Inscrito foi Informada com Sucesso";
																							      $Url = "$ProgramaSelecao?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
																										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																							      header("location: ".$Url);
																							      exit;
																						    }
																						}
																				}
																		}
																}
														} else {
												  			# Grava o Fornecedor Inscrito com situação pendente e indeferido #
																$db->query("BEGIN TRANSACTION");
																# Atualiza tabela de Pré-Fornecedor #
																$sql    = "UPDATE SFPC.TBPREFORNECEDOR ";
																$sql   .= "    SET CPREFSCODI = '$NovaSituacao', EPREFOMOTI = '$Motivo', CGREMPCODI = $Grupo,";
																$sql   .= "        CUSUPOCODI = $Usuario,  TPREFOULAT = '$DataAtual', ";
																$sql   .= "        CCOMLICODI = $Comissao, DPREFOANAL = '$DataAnaliseDocInv' ";
																$sql   .= "  WHERE APREFOSEQU = $Sequencial";
																$result = $db->query($sql);
																if( PEAR::isError($result) ){
																		$db->query("ROLLBACK");
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		$db->query("COMMIT");
																		$db->query("END TRANSACTION");
																		$Critica  = 0;
															      $Mensagem = urlencode("Avaliação do Fornecedor Inscrito foi Informada com Sucesso");
															      $Url = "$ProgramaSelecao?Mensagem=$Mensagem&Mens=1&Tipo=1";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
														    }
											    	}
												}
										}
								}
						}
				}
		}
}



#saída de parte do programa

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Avaliacao.Botao.value=valor;
	document.Avaliacao.submit();
}
function ncaracteres(valor){
	document.Avaliacao.NCaracteres.value = '' +  document.Avaliacao.Motivo.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.Avaliacao.NCaracteres.focus();
	}
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=no,left=270,top=150,width='+largura+',height='+altura);
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAvaliacaoInscritoManter.php" method="post" name="Avaliacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Inscrição >
      <?php if( $ProgramaSelecao == "CadAvaliacaoInscritoSelecionarLib.php" ){ echo "Liberação da Avaliação"; }else{ echo "Avaliação"; } ?>
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					      <?php
					      if( $ProgramaSelecao == "CadAvaliacaoInscritoSelecionarLib.php" ){
					      		echo "AVALIAÇÃO DAS INSCRIÇÕES DE FORNECEDORES - LIBERAÇÃO";
								}else{
		    						echo "AVALIAÇÃO DAS INSCRIÇÕES DE FORNECEDORES";
								}
								?>
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Informe um nova situação para o fornecedor inscrito e clique no botão "Confirmar".
	        	    		Para visualizar/alterar os dados da inscrição do fornecedor clique no botão "Manter Inscrito".<br>
	        	    		Para voltar para a tela de Pesquisa, clique no botão "Voltar".
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%" summary="">
			             	<tr>
			             		<?php if( $CNPJ != 0 ){ ?>
			             		<td class="textonormal" bgcolor="#DCEDF7" height="20">CNPJ</td>
			      	    		<td class="textonormal">
									<a href="<?php echo 'ConsInscrito.php?Sequencial='.$Sequencial.'&Retorno=CadAvaliacaoInscritoManter' ?>">
			      	    			<?php echo (substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2)); ?>
								 	</a>
								</td>
											<tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Razão Social</td>
				      	    		<td class="textonormal"><?php echo $_SESSION['Razao']; ?></td>
				        	  	</tr>
		              		<?php }else{ ?>
			             		<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF</td>
			      	    		<td class="textonormal">
			      	    			<?php echo (substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2)); ?>
			      	    		</td>
											<tr>
				              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Nome</td>
				      	    		<td class="textonormal"><?php echo mb_htmlentities($_SESSION['Razao']); ?></td>
				        	  	</tr>
		              		<?php } ?>
			        	  	</tr>
										<tr>
			              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Data Inscrição</td>
		      	    			<td class="textonormal">
		      	    				<?php echo $_SESSION['DataInscricao']; ?>
												<input type="hidden" name="CNPJ" value="<?php echo $CNPJ; ?>">
												<input type="hidden" name="CPF" value="<?php echo $CPF; ?>">
											</td>
			        	  	</tr>
										<tr>
			              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação Anterior</td>
			      	    		<td class="textonormal"><?php echo $_SESSION['DescSituacaoAnt']; ?></td>
			        	  	</tr>
	      	      		<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nova Situação</td>
				              <td class="textonormal">
					           		<select name="NovaSituacao" value="<?php echo $NovaSituacao; ?>" onChange="javascript:Critica.value=0;document.Avaliacao.submit();" class="textonormal">
											  <option value="">Selecione Nova Situação...</option>
								    			<?php
													$sql    = "SELECT CPREFSCODI, EPREFSDESC FROM SFPC.TBPREFORNTIPOSITUACAO ";
													$sql   .= "WHERE CPREFSCODI <> ". $_SESSION['CodSituacaoAnt'];
													$sql   .= " AND  CPREFSCODI <> 1 ";
													$sql   .= " ORDER BY EPREFSDESC";
											    $result = $db->query($sql);
													if( PEAR::isError($result) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
														  if( $Linha[0] == $NovaSituacao){
													?>
													<option value="<?php echo $Linha[0]; ?>" selected><?php echo $Linha[1]; ?></option>
													<?php 	}else{ ?>
													<option value="<?php echo $Linha[0]; ?>"><?php echo $Linha[1]; ?></option>
													<?php 	}
												  }
											   	?>
												</select>
	          	    		</td>
		          	    </tr>
										<?php # Situação do fornecedor igual a Pendente, Indeferido ou Excluído #
										if (( $NovaSituacao== 3 ) or ( $NovaSituacao== 4 ) or ( $NovaSituacao== 5 ) ) { ?>
        	    				<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Motivo*</td>
												<td class="textonormal">
													máximo de 200 caracteres
													<input class="textonormal" type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>"><br>
													<textarea class="textonormal" name="Motivo" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)"><?php echo $Motivo ?></textarea>
												</td>
											</tr>
										<?php } ?>
	      	      		<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Comissão de Licitação Responsável pela Análise da Documentação</td>
				              <td class="textonormal">
					           		<select name="Comissao" class="textonormal">
											  <option value="">Selecione a Comissão...</option>
								    			<?php
								    			$Grupo   = $_SESSION['_cgrempcodi_'];
													$sql    = "SELECT CCOMLICODI, ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
													if ($Grupo <> 0) {
															$sql   .= "WHERE  CGREMPCODI = $Grupo";
															$sql   .= " AND   FCOMLISTAT = 'A' ORDER BY ECOMLIDESC";
													}	else {
															$sql   .= " WHERE FCOMLISTAT = 'A' ORDER BY ECOMLIDESC";
													}
											    $result = $db->query($sql);
													if( PEAR::isError($result) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
														  if( $Linha[0] == $Comissao ){
													?>
													<option value="<?php echo $Linha[0]; ?>" selected><?php echo $Linha[1]; ?></option>
													<?php 	}else{ ?>
													<option value="<?php echo $Linha[0]; ?>"><?php echo $Linha[1]; ?></option>
													<?php 	}
												  }
											   	?>
												</select>
	          	    		</td>
		          	    </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Data da Análise da Documentação </td>
				              <td class="textonormal">
	              				<?php $URL = "../calendario.php?Formulario=Avaliacao&Campo=DataAnaliseDoc" ?>
						          	<input type="text" class="textonormal" name="DataAnaliseDoc" size="10" maxlength="10" value="<?php echo $DataAnaliseDoc;?>" >
												<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
				           	</tr>
		          	 	</table>
				      	</td>
        			</tr>
      	      <tr>
    	      		<td align="right">
  								<input type="hidden" name="Irregularidade" value="<?php echo $Irregularidade; ?>">
									<input type="hidden" name="ProgramaSelecao" value="<?php echo $ProgramaSelecao; ?>">
									<input type="hidden" name="Critica" value="1">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial?>">
		      				<input type="button" name="Confirmar" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
  	      				<input type="button" name="Manter" value="Manter Inscrito" class="botao" onclick="javascript:enviar('ManterInscrito');">
      	      		<input type="button" name="Voltar" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
<?php $db->disconnect(); ?>
