<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMovimentacaoIncluir.php
# Autor:    Filipe Cavalcanti
# Data:     27/09/2005
# Objetivo: Programa para incluir uma movimentação de entrada ou saída dos itens estocados
#------------------------
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     05/04/2006
# Alterado: Álvaro Faria
# Data:     26/05/2006 (Novas movimentações)
# Alterado: Álvaro Faria
# Data:     01/08/2006
# Alterado: Álvaro Faria
# Data:     22/08/2006 - Proibição da inclusão de movimentações de tipo 25 e 28
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Álvaro Faria
# Data:     30/08/2006
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
# Alterado: Álvaro Faria
# Data:     05/12/2006 - Correção de tabelas HTML para apresentação do Código reduzido
#                        Padronização do label de código reduzido
#                        Identação
# Alterado: Carlos Abreu
# Data:     15/12/2006 - Filtro no carregamento dos almoxarifados para bloquear quando Sob Inventário
# Alterado: Carlos Abreu
# Data:     27/12/2006 - Filtro no carregamento dos almoxarifados para liberar Almox. Educação quando Sob Inventário
# Alterado: Álvaro Faria
# Data:     03/01/2006 - Não exibição na lista de movimentações das movimentações de geração de inventário (33 e 34)
# Alterado: Álvaro Faria
# Data:     24/01/2006 - Permitir seleção do ano da requisição para as movimentações de Devolução Interna e Acerto de Requisição
# Alterado: Álvaro Faria
# Data:     31/01/2006 - Suporte aos tipos 26 e 27, com nova funcionalidade e descrição
# Alterado: Rossana Lira
# Data:     06/02/2007 - Incluir no in os tipos 26 na saída e 27 na entrada
# Alterado: Carlos Abreu
# Data:     02/03/2007 - Ajuste para aceitar novas movimentações e ajustes na pesquisa de materiais para movimentacao (32)
# Alterado: Carlos Abreu
# Data:     15/03/2007 - Caso perfil nao tenha visão corporativa remove a movimentacao acerto de inventario da lista de movimentações disponíveis
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Álvaro Faria
# Data:     20/12/2007 - Correção do select de almoxarifado para bloquear almoxarifados em inventário ou no período de inventário
# Alterado: Álvaro Faria
# Data:     21/12/2007 - Alteração na movimentação 9 para trabalhar com valor médio
# Alterado: Rodrigo Melo
# Data:     09/01/2008 - Correção do select de almoxarifado, pois o mesmo não está liberando os almoxarifados a realizarem as
#                                 movimentações após a realização do inventário.
# Alterado: Rodrigo Melo
# Data:     18/02/2008 - Alteração para não permitir a entrada de materiais inativos através da movimentação entrada por doação externa e
#                                  permitir apenas a saída dos materiais inativos em estoque.
# Alterado: Rodrigo Melo
# Data:     25/02/2008 - Alteração para não permitir a entrada de materiais inativos através das seguintes movimentações:
#                                  entrada por acerto de inventário (28) e saída por acerto de inventário (25).
# Alterado: Rodrigo Melo
# Data:      08/07/2008 - Alteração para permitir que as movimentações sejam realizadas apenas pelo estoque real e não pelo estoque total.

# Alterado: Ariston Cordeiro
# Data:      10/09/2008 - Removendo acesso a SFPC.TBFORNECEDORESTOQUE. (tabela foi movida para SFPC.TBFORNECEDORCREDENCIADO)
# Alterado: Ariston Cordeiro
# Data:      10/09/2008 - Removendo acesso a SFPC.TBFORNECEDORESTOQUE. (tabela foi movida para SFPC.TBFORNECEDORCREDENCIADO)
# Alterado: Ariston Cordeiro
# Data:      05/12/2008 - Alterar movimentações relacionadas a requisições e notas fiscais para não poderem alterar notas fiscais virtuais
#													ou requisições atendidas por esta.
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# Alterado: Ariston Cordeiro
# Data:     14/08/2009 - CR 2899- Correção de bug que ocorre quando a requisição foi atendida por uma Nota Fiscal virtual e possui um ítem
#                        cancelado por "Devolução Interna". Devido a movimentacao de devolução interna não especificar a nota, programa
#                        detectava incorretamente que não havia nota fiscal virtual correspondente.
#----------------

# OBS.:     Tabulação 2 espaços
# AUTOR:  Marcello Albuquerque
# DATA:   05/05/2021
# CR:     245963
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadMovimentacaoConfirmar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Localizacao         = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Orgao               = $_POST['Orgao'];
		$DescMaterial        = $_POST['DescMaterial'];
		$TipoMovimentacao    = $_POST['TipoMovimentacao'];
		$Movimentacao        = $_POST['Movimentacao'];
		$ProxMovimentacao    = $_POST['ProxMovimentacao'];
		$Opcao               = $_POST['Opcao'];
		$Requisicao          = $_POST['Requisicao'];
		$AnoRequisicao       = $_POST['AnoRequisicao'];
		$CNPJ_CPF            = $_POST['CNPJ_CPF'];
		if($_POST['CnpjCpf'] != ""){
				if($CNPJ_CPF == 2){
						$CnpjCpf  = substr("00000000000".$_POST['CnpjCpf'],-11); // CPF
				}else{
						$CnpjCpf  = substr("00000000000000".$_POST['CnpjCpf'],-14); // CNPJ
				}
		}else{
				$CnpjCpf = $_POST['CnpjCpf'];
		}
		$RazaoSocial         = $_POST['RazaoSocial'];
		$NumeroNota          = $_POST['NumeroNota'];
		$SerieNota           = strtoupper2($_POST['SerieNota']);
		$TipoPesquisa        = $_POST['TipoPesquisa'];
}else{
		$Almoxarifado        = $_GET['Almoxarifado'];
		$Mens                = $_GET['Mens'];
		$Tipo                = $_GET['Tipo'];
		$Troca               = $_GET['Troca'];
		$Mensagem            = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Movimentação sempre o Ano Atual para uma nova movimentação #
$AnoMovimentacao = date("Y");
if(!$AnoRequisicao) $AnoRequisicao = $AnoMovimentacao;

if(!$Troca) $Troca = 1; // Padrão que pode ser mudado durante o programa ou vir por Get. Desta forma (1) converte última vírgua da mensagem de erro por "e"

if($Botao == "Limpar"){
		header("location: CadMovimentacaoIncluir.php");
		exit;
}elseif($Botao == "Verificar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($CNPJ_CPF == ""){
				$RazaoSocial = null;
				if($Mens == 1){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "A opção CNPJ ou CPF";
		}else{
				if($CNPJ_CPF == 1){ $TipoDocumento = "CNPJ"; }else{ $TipoDocumento = "CPF"; }
				if($CnpjCpf == ""){
						$RazaoSocial = null;
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
				}else{
						if($CNPJ_CPF == 1){
								$valida_cnpj = valida_CNPJ($CnpjCpf);
								if($valida_cnpj === false){
										$RazaoSocial = null;
										if( $Mens == 1 ){ $Mensagem.=", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
								}
						}else{
								$valida_cpf = valida_CPF($CnpjCpf);
								if($valida_cpf === false){
										$RazaoSocial = null;
										if( $Mens == 1 ){ $Mensagem.=", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
								}
						}
				}
				if( ( $CNPJ_CPF == 1 and $valida_cnpj === true ) or ( $CNPJ_CPF == 2 and $valida_cpf === true )  ){
						# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
						$db   = Conexao();
						$sql  = "SELECT NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sql .= " WHERE ";
						if($CNPJ_CPF == 1){
								$sql .= " AFORCRCCGC = '$CnpjCpf' ";
						}else{
								$sql .= " AFORCRCCPF = '$CnpjCpf' ";
						}
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$rows = $res->numRows();
								if($rows > 0){
										$linha       = $res->fetchRow();
										$RazaoSocial = $linha[0];
								}else{
								/*  # SFPC.TBfornecedorestoque não existe mais!
										# Verifica se o Fornecedor de Estoque já está cadastrado #
										$db   = Conexao();
										$sql  = "SELECT EFORESRAZS FROM SFPC.TBFORNECEDORESTOQUE ";
										$sql .= "	WHERE ";
										if( $CNPJ_CPF == 1 ){
												$sql .= "	AFORESCCGC = '$CnpjCpf' ";
										}else{
												$sql .= "	AFORESCCPF = '$CnpjCpf' ";
										}
										$res  = $db->query($sql);
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$rows = $res->numRows();
												if($rows > 0){
														$linha       = $res->fetchRow();
														$RazaoSocial = $linha[0];
												}else{
								*/
														if($Mens == 1){ $Mensagem.=", "; }
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor Não Cadastrado";
												/*}
										}
										$db->disconnect();*/
								}
						}
				}
		}
}elseif($Botao == "Validar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($TipoMovimentacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}
		if($Movimentacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Movimentacao.focus();\" class=\"titulo2\">Movimentação</a>";
		}
		# Caso seja Devolução Interna ou Acerto para Requisição #
		if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 or $Movimentacao == 21 or $Movimentacao == 22){
				if($Requisicao == ""){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Requisicao.focus();\" class=\"titulo2\">Número da Requisição</a>";
				}elseif(!SoNumeros($Requisicao)){
					if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Requisicao.focus();\" class=\"titulo2\">Número da Requisição Válido</a>";
				}else{

						# Verifica se existe a Requisição para os casos de Requisicao Baixada e Atendida #
						$db   = Conexao();
						$sql  = "  SELECT DISTINCT A.CREQMASEQU ";
						//$sql .= "  ,(CASE WHEN ( (CENTNFCODI IS NOT NULL) AND (AENTNFANOE IS NOT NULL) ) THEN 'S' ELSE 'N' END) AS VIRTUAL ";
						$sql .= "    FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B, SFPC.TBMOVIMENTACAOMATERIAL C ";
						$sql .= "   WHERE A.AREQMAANOR = $AnoRequisicao ";
						$sql .= "     AND A.CREQMACODI = $Requisicao AND A.CREQMASEQU = B.CREQMASEQU AND B.CREQMASEQU = C.CREQMASEQU ";
						# Só busca pelo Orgão da requisição se o usuário logado não for o ADMIN, sendo, traz as requisições de qualquer Órgão
						if($_SESSION['_cgrempcodi_'] != 0){ $sql .= " AND A.CORGLICODI = $Orgao "; }
						if($Movimentacao == 2 or $Movimentacao == 21 or $Movimentacao == 22){
								# Só as requisições BAIXADAS #
								$sql .= "     AND B.CTIPSRCODI = 5 ";
						}else{
								# Só as requisições ATENDIDAS(Total e Parcial) #
								$sql .= "     AND B.CTIPSRCODI IN(3,4) ";
						}
						$sql .= "   AND B.TSITREULAT IN ";
						$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT";
						$sql .= "           WHERE SIT.CREQMASEQU = A.CREQMASEQU) ";

            $res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha         = $res->fetchRow();
								$SeqRequisicao = $Linha[0];


								if($SeqRequisicao == ""){
									$EstoqueVirtual = "N";
                  if($Mens == 1){ $Mensagem .= ", "; }
                  $Mens      = 1;
                  $Tipo      = 2;
                  $Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Requisicao.focus();\" class=\"titulo2\">";
                  if($Movimentacao == 2 or $Movimentacao == 21 or $Movimentacao == 22){
                      $Mensagem .= "Requisição Baixada</a>";
                  }else{
                      $Mensagem .= "Requisição Atendida</a>";
                  }
								}else{
									# Verifica se requisição foi atendida por uma nota fiscal virtual #
									$db   = Conexao();
									$sql  = "
										select distinct CENTNFCODI
										from SFPC.TBmovimentacaomaterial
										where
											calmpocodi = $Almoxarifado
											and amovmaanom = $AnoRequisicao
											and creqmasequ = $SeqRequisicao
											and ctipmvcodi = 4
									";

			            $res  = $db->query($sql);
									if( db::isError($res) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											exit(0);
									}
									$Linha         = $res->fetchRow();
									if(!is_null($Linha[0])){
	                	$EstoqueVirtual = "S";
									}else{
	                	$EstoqueVirtual = "N";
									}
								}

                if($EstoqueVirtual == 'S' && ($Movimentacao == 19 || $Movimentacao == 20 || $Movimentacao == 21 || $Movimentacao == 22 )){
                  if($Mens == 1){ $Mensagem .= ", "; }
                  $Mens      = 1;
                  $Tipo      = 2;
                  $Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.Requisicao.focus();\" class=\"titulo2\">";
                  $Mensagem .= "Requisição atendida por estoque real</a>";
                }
						}
						$db->disconnect();
				}
				if($AnoRequisicao == ""){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.AnoRequisicao.focus();\" class=\"titulo2\">Ano da Requisição</a>";
				}elseif(!SoNumeros($AnoRequisicao) or $AnoRequisicao < 2006){
					if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.AnoRequisicao.focus();\" class=\"titulo2\">Ano da Requisição Válido</a>";
				}
		}
		# Caso seja movimentações de Acerto de devolução Interna para Cancelamento de Nota Fiscal, exige número e série da nota se ainda não digitados #
		if($Movimentacao == 21 or $Movimentacao == 22){
				if($NumeroNota == ""){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$FaltaNrSe = 1;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota</a>";
				}else{
						if( !SoNumeros($NumeroNota) ){
							if($Mens == 1){ $Mensagem .= ", "; }
							$Mens      = 1;
							$Tipo      = 2;
							$FaltaNrSe = 1;
							$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.NumeroNota.focus();\" class=\"titulo2\">Número da Nota Válido</a>";
						}
				}
				if($SerieNota == ""){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$FaltaNrSe = 1;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.SerieNota.focus();\" class=\"titulo2\">Série da Nota</a>";
				}
				if($CNPJ_CPF == ""){
						$RazaoSocial = null;
						$FaltaForn   = 1;
						if($Mens == 1){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "A opção CNPJ ou CPF";
				}else{
						if($CNPJ_CPF == 1){ $TipoDocumento = "CNPJ"; }else{ $TipoDocumento = "CPF"; }
						if($CnpjCpf == ""){
								$RazaoSocial = null;
								$FaltaForn   = 1;
								if($Mens == 1){ $Mensagem.=", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
						}else{
								if($CNPJ_CPF == 1){
										$valida_cnpj = valida_CNPJ($CnpjCpf);
										if($valida_cnpj === false){
												$RazaoSocial = null;
												$FaltaForn   = 1;
												if($Mens == 1){ $Mensagem.=", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
										}
								}else{
										$valida_cpf = valida_CPF($CnpjCpf);
										if($valida_cpf === false){
												$RazaoSocial = null;
												$FaltaForn   = 1;
												if($Mens == 1){ $Mensagem.=", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
										}
								}
						}
						if( ($CNPJ_CPF == 1 and $valida_cnpj === true ) or ( $CNPJ_CPF == 2 and $valida_cpf === true ) ){
								# Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
								$db   = Conexao();
								$sql  = "SELECT AFORCRSEQU, NFORCRRAZS FROM SFPC.TBFORNECEDORCREDENCIADO ";
								$sql .= " WHERE ";
								if($CNPJ_CPF == 1){
										$sql .= " AFORCRCCGC = '$CnpjCpf' ";
								}else{
										$sql .= " AFORCRCCPF = '$CnpjCpf' ";
								}
								$res  = $db->query($sql);
								if( db::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$rows = $res->numRows();
										if($rows > 0){
												$linha       = $res->fetchRow();
												$FornecedorCod = $linha[0];
												$RazaoSocial   = $linha[1];
										}else{/* # SFPC.TBfornecedorestoque não existe mais!
												# Verifica se o Fornecedor de Estoque já está cadastrado #
												$db   = Conexao();
												$sql  = "SELECT CFORESCODI, EFORESRAZS FROM SFPC.TBFORNECEDORESTOQUE ";
												$sql .= "	WHERE ";
												if($CNPJ_CPF == 1){
														$sql .= "	AFORESCCGC = '$CnpjCpf' ";
												}else{
														$sql .= "	AFORESCCPF = '$CnpjCpf' ";
												}
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$rows = $res->numRows();
														if($rows > 0){
																$linha       = $res->fetchRow();
																$FornecedorCod = $linha[0];
																$RazaoSocial   = $linha[1];
														}else{*/
																$RazaoSocial   = null;
																$FaltaForn     = 1;
																if($Mens == 1){ $Mensagem.=", "; }
																$Mens     = 1;
																$Tipo     = 1;
																$Mensagem = "Fornecedor Não Cadastrado";
														/*}
												}
												$db->disconnect();*/
										}
								}
						}
				}
				if(!$FaltaNrSe and !$FaltaForn){
						# Verifica se a Nota Fiscal não foi Cancelada #
						$db   = Conexao();
						$sqlCancel   = "SELECT AENTNFNOTA, AENTNFSERI, FENTNFCANC ";
						$sqlCancel  .= "  FROM SFPC.TBENTRADANOTAFISCAL ";
						$sqlCancel  .= " WHERE AENTNFNOTA = $NumeroNota AND AENTNFSERI = '$SerieNota' ";
						$sqlCancel  .= "   AND ( (AFORCRSEQU = $FornecedorCod) OR (CFORESCODI = $FornecedorCod) )";
						$sqlCancel  .= "   AND CALMPOCODI = $Almoxarifado ";
						$sqlCancel  .= " ORDER BY TENTNFULAT DESC"; // Para pegar a nota mais recente
						$res  = $db->query($sqlCancel);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCancel");
						}else{
								$NumRows = $res->numRows();
								$Linha   = $res->fetchRow();
								if($NumRows == "0"){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.NumeroNota.focus();\" class=\"titulo2\">Um Número de Nota Fiscal Válido</a>";
								}else{
										if($Linha[2] == 'S'){
										if($Mens == 1){ $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.NumeroNota.focus();\" class=\"titulo2\">Um Número de Nota Fiscal não Cancelada</a>";
										}
								}
						}
						$db->disconnect();
				}
		}
		if($DescMaterial == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.DescMaterial.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if($DescMaterial != "" and $Opcao == 0 and ( ! SoNumeros($DescMaterial) ) ){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.DescMaterial.focus();\" class=\"titulo2\">Código Reduzido do Material Válido</a>";
				}elseif($DescMaterial != "" and ($Opcao == 1 or $Opcao == 2) and strlen($DescMaterial)< 2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoIncluir.DescMaterial.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
				}elseif($DescMaterial != ""){
						$sqlgeral  = "SELECT ";
						if($TipoPesquisa == 0){
								$sqlgeral .= " DISTINCT MAT.CMATEPSEQU,";
						}else{
								$sqlgeral .= " MAT.CMATEPSEQU,";
						}
						$sqlgeral .= " MAT.EMATEPDESC, UND.EUNIDMSIGL ";
						# Se a Movimentacao for relacionada com Requisição #
						if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 or $Movimentacao == 21 or $Movimentacao == 22){
								$sqlgeral .= " ,REQ.CREQMASEQU, SIT.CTIPSRCODI ";
						}
						if($Movimentacao == 26 or $Movimentacao == 27){
								$sqlgeral .= ", TIP.ETIPMVDESC, MOV.DMOVMAMOVI, MOV.AMOVMAQTDM, MOV.AMOVMAANOM , MOV.CMOVMACODI, MOV.CMOVMACODT";
						}

            $sqlgeral .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND ";


            # Alteração do dia 18/02/2008, não permitir apenas a entrada de materiais ativos quando a movimentação for 10 - ENTRADA POR DOAÇÃO EXTERNA
            #Alteração do dia 25/02/2008, não permitir a entrada por acerto de inventário (28) e saída por acerto de inventário (25)
            #Utilizado para fazer o Join (junções) entre o material e sua subclasse, classe e grupo para então impedir o material que for inativo ou ainda os
            #materiais que fazem parte de subclasses, classes ou grupo inativos.
            if($Movimentacao == 10 || $Movimentacao == 28 || $Movimentacao == 25){
              $sqlgeral .= "  , SFPC.TBSUBCLASSEMATERIAL AS SUB, ";
              $sqlgeral .= "       SFPC.TBCLASSEMATERIALSERVICO AS CLA, SFPC.TBGRUPOMATERIALSERVICO AS GRU ";
            }

						if($TipoPesquisa == 1 or $TipoPesquisa == ""){
								$sqlgeral .= "       , SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC ";
						}

						# Se a Movimentacao for relacionada com Requisição #
						if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 or $Movimentacao == 21 or $Movimentacao == 22){
								$sqlgeral .= "       , SFPC.TBREQUISICAOMATERIAL REQ, SFPC.TBITEMREQUISICAO ITE, ";
								$sqlgeral .= "         SFPC.TBSITUACAOREQUISICAO SIT, SFPC.TBTIPOSITUACAOREQUISICAO TIP ";
						}
						# Se a Movimentacao for dos tipos 26 ou 27 #
						if($Movimentacao == 26 or $Movimentacao == 27){
								$sqlgeral .= "       , SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP ";
						}
						$sqlgeral .= " WHERE ";
						# Se foi digitado algo na caixa de texto do material em pesquisa direta #
						if($DescMaterial != ""){
								if($Opcao == 0){
										if( SoNumeros($DescMaterial ) ){
												$sqlgeral .= " MAT.CMATEPSEQU = $DescMaterial ";
										}
								}elseif($Opcao == 1){
										$sqlgeral .= " ( ";
										$sqlgeral .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($DescMaterial))."%' OR ";
										$sqlgeral .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($DescMaterial))."%' ";
										$sqlgeral .= "  )";
								}else{
										$sqlgeral .= " TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($DescMaterial))."%' ";
								}
								$sqlgeral .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
								if($TipoPesquisa == 1 or $TipoPesquisa == ""){
										$sqlgeral .= "   AND MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
										$sqlgeral .= "   AND ARM.CLOCMACODI = LOC.CLOCMACODI ";
										$sqlgeral .= "   AND LOC.CLOCMACODI = $Localizacao ";
								}
								# Se a Movimentacao for relacionada com Requisição #
								if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 or $Movimentacao == 21 or $Movimentacao == 22){
										# Só busca pelo Orgão da requisição se o usuário logado não for o ADMIN, sendo, traz as requisições de qualquer Órgão
										if($_SESSION['_cgrempcodi_'] != 0){ $sqlgeral .= " AND REQ.CORGLICODI = $Orgao "; }
										$sqlgeral .= "   AND REQ.AREQMAANOR = $AnoRequisicao ";
										$sqlgeral .= "   AND REQ.CREQMACODI = $Requisicao AND REQ.CREQMASEQU = ITE.CREQMASEQU ";
										$sqlgeral .= "   AND MAT.CMATEPSEQU = ITE.CMATEPSEQU  AND REQ.CREQMASEQU = SIT.CREQMASEQU ";
										$sqlgeral .= "   AND SIT.CTIPSRCODI = TIP.CTIPSRCODI ";
										$sqlgeral .= "   AND REQ.CALMPOCODI = $Almoxarifado ";
										# Se a Movimentacao for Baixada #
										if($Movimentacao == 2 or $Movimentacao == 21 or $Movimentacao == 22){
												$sqlgeral .= " AND TIP.CTIPSRCODI = 5 "; // Apenas BAIXADA
										}else{
												$sqlgeral .= " AND TIP.CTIPSRCODI IN(3,4) "; // Apenas ATENDIDAS
										}
										$sqlgeral .= "   AND SIT.TSITREULAT IN ";
										$sqlgeral .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SI";
										$sqlgeral .= "         WHERE SI.CREQMASEQU = REQ.CREQMASEQU) ";
								}
								# Se a Movimentacao for dos tipo 26 ou 27 #
								if($Movimentacao == 26 or $Movimentacao == 27){
										$sqlgeral .= "   AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
										$sqlgeral .= "   AND MOV.FMOVMACORR IS NULL ";
										$sqlgeral .= "   AND MOV.CALMPOCODI = $Almoxarifado ";
										$sqlgeral .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
										$sqlgeral .= "   AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
										if($Movimentacao == 26){
												$sqlgeral .= "   AND MOV.CTIPMVCODI IN (14,16,17,23,24,27,37) "; // Saídas - Obsoletismo (14), Avaria (16), Vencimento (17), Furto (23), Doação externa (24)
										}elseif($Movimentacao == 27){
												$sqlgeral .= "   AND MOV.CTIPMVCODI IN (10,26,32) ";                // Entrada - Doação externa (10), ENTRADA POR CANCELAMENTO DE MOVIMENTACAO (26), ENTRADA DE MATERIAL DE ILUMINACAO RECUPERADO (32)
										}
								}
								if($TipoMovimentacao == 'S'){
                  if($EstoqueVirtual == 'S'){ //Usado nas Saídas para acerto da requisição (20) e Saídas por acerto da devolução interna p/ acerto cancelamento da Nota Fiscal (22)
                    $sqlgeral .= " AND ARM.AARMATVIRT > 0";
                  } else {
                    $sqlgeral .= " AND ARM.AARMATESTR > 0";
                  }
								}

                # Alteração do dia 18/02/2008, não permitir apenas a entrada de materiais ativos quando a movimentação for 10 - ENTRADA POR DOAÇÃO EXTERNA
                #Alteração do dia 25/02/2008, não permitir a entrada por acerto de inventário (28) e saída por acerto de inventário (25)
                if($Movimentacao == 10 || $Movimentacao == 28 || $Movimentacao == 25){

                  //Join (junções) entre o material e sua subclasse, classe e grupo.
                  $sqlgeral .= "   AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                  $sqlgeral .= "   AND (SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI) ";
                  $sqlgeral .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";

                  //Verifica se o material, subclasse, classe ou grupo do material não é inativo
                  $sqlgeral .= " AND ( MAT.CMATEPSITU <> 'I' AND SUB.FSUBCLSITU <> 'I' ";
                  $sqlgeral .= "       AND CLA.FCLAMSSITU <> 'I' AND GRU.FGRUMSSITU <> 'I') ";
                }

								if($Movimentacao == 26 or $Movimentacao == 27){
										$sqlgeral .= " ORDER BY TIP.ETIPMVDESC, MOV.DMOVMAMOVI, MAT.EMATEPDESC ";
								}else{
										$sqlgeral .= " ORDER BY MAT.EMATEPDESC ";
								}
						}
				}
		}
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.CadMovimentacaoIncluir.Botao.value = valor;
	document.CadMovimentacaoIncluir.submit();
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMovimentacaoIncluir.php" method="post" name="CadMovimentacaoIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
			<td align="left" class="textonormal" colspan="8">
				<font class="titulo2">|</font>
				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Incluir
			</td>
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?
	if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="8"><?php ExibeMens($Mensagem,$Tipo,$Troca); ?></td>
	</tr>
	<?
	}
	?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td colspan="8" align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - MOVIMENTAÇÃO</td>
							</tr>
							<tr>
								<td colspan="8" class="textonormal">
									<p align="justify">
										Para Incluir a Movimentação do Material, preencha os campos abaixo e clique no Material desejado.
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="8" >
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db   = Conexao();
												# Se o usuário logado for o ADMIN, não busca pelo Órgão
												if($_SESSION['_cgrempcodi_'] == 0){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A";
														if($Almoxarifado){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT DISTINCT A.CALMPOCODI, A.EALMPODESC, B.CORGLICODI ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B , SFPC.TBLOCALIZACAOMATERIAL C ";
														#$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH IS NULL OR A.FINVCOFECH = 'N') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
														$sql   .= "  LEFT OUTER JOIN (SELECT * FROM SFPC.TBINVENTARIOCONTAGEM WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) IN ( SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU FROM SFPC.TBINVENTARIOCONTAGEM A WHERE (A.FINVCOFECH = 'S') AND (A.CLOCMACODI, A.AINVCOANOB) IN ( SELECT CLOCMACODI, MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM GROUP BY CLOCMACODI) GROUP BY A.CLOCMACODI, A.AINVCOANOB ) ) AS D";
														$sql   .= "    ON C.CLOCMACODI = D.CLOCMACODI ";
														$sql   .= " WHERE A.CALMPOCODI = C.CALMPOCODI AND A.CALMPOCODI = B.CALMPOCODI ";
														if($Almoxarifado){
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI in ";
														$sql .= "       (SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END) ";

														# Trecho com relação a data de fechamento #
														/*
														$sql .= "   AND CASE WHEN ('".date("Y-m-d")."'>='".$InventarioDataInicial."') THEN ";
														# Para que inventário seja feito no período determinado, sem passar da data final definida, descomentar a linha abaixo e comentar a posterior #
														# $sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' AND D.TINVCOFECH <= '".$InventarioDataFinal."' ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) AND D.TINVCOFECH >= '".$InventarioDataInicial."' ";
														$sql .= "       ELSE ";
														$sql .= "            (A.FALMPOINVE = 'N' OR A.FALMPOINVE IS NULL) ";
														$sql .= "        END ";
														*/
														# Trecho com relação a data de hoje #
														$sql .= "   AND ( ";
														$sql .= "        TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') < TO_DATE('".$InventarioDataInicial."','YYYY-MM-DD') ";
														$sql .= "        OR TO_DATE('".date('Y-m-d')."','YYYY-MM-DD') > TO_DATE('".$InventarioDataFinal."','YYYY-MM-DD') ";
														$sql .= "   ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
                        $res  = $db->query($sql);

												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha        = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																$Orgao        = $Linha[2];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif($Rows > 1){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0; $i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if($Linha[0] == $Almoxarifado){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
																if(!$Almoxarifado){
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}
														}else{
																echo "ALMOXARIFADO NÃO CADASTRADO, INATIVO OU SOB INVENTÁRIO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<?php if($Almoxarifado != ""){ ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
													<td class="textonormal">
														<?php
														$db = Conexao();
														if($Localizacao != ""){
																# Mostra a Descrição de Acordo com o Almoxarifado #
																$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
																$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
																$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
																$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
																$res  = $db->query($sql);
																if( db::isError($res) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		$Linha = $res->fetchRow();
																		if($Linha[0] == "E"){
																				$Equipamento = "ESTANTE";
																		}elseif($Linha[0] == "A"){
																				$Equipamento = "ARMÁRIO";
																		}elseif($Linha[0] == "P"){
																				$Equipamento = "PALETE";
																		}
																		$DescArea = $Linha[4];
																		echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																		echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																}
														}else{
																# Mostra as Localizações de acordo com o Almoxarifado #
																$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
																$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
																$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
																$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
																$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
																$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
																$res  = $db->query($sql);
																if( db::isError($res) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		$Rows = $res->numRows();
																		if($Rows == 0){
																				echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																				echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																		}else{
																				if($Rows == 1){
																						$Linha = $res->fetchRow();
																						if($Linha[1] == "E"){
																								$Equipamento = "ESTANTE";
																						}elseif($Linha[1] == "A"){
																								$Equipamento = "ARMÁRIO";
																						}elseif($Linha[1] == "P"){
																								$Equipamento = "PALETE";
																						}
																						echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																						$Localizacao = $Linha[0];
																						echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																				}else{
																						if($Rows == 1){
																								$Linha = $res->fetchRow();
																								if($Linha[1] == "E"){
																										$Equipamento = "ESTANTE";
																								}elseif($Linha[1] == "A"){
																										$Equipamento = "ARMÁRIO";
																								}elseif($Linha[1] == "P"){
																										$Equipamento = "PALETE";
																								}
																								echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																								$Localizacao = $Linha[0];
																								echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																						}else{
																								echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																								echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																								$EquipamentoAntes = "";
																								$DescAreaAntes    = "";
																								for($i=0; $i< $Rows; $i++){
																										$Linha = $res->fetchRow();
																										$CodEquipamento = $Linha[2];
																										if($Linha[1] == "E"){
																												$Equipamento = "ESTANTE";
																										}elseif($Linha[1] == "A"){
																												$Equipamento = "ARMÁRIO";
																										}if($Linha[1] == "P"){
																												$Equipamento = "PALETE";
																										}
																										$NumeroEquip = $Linha[2];
																										$Prateleira  = $Linha[3];
																										$Coluna      = $Linha[4];
																										$DescArea    = $Linha[5];
																										if($DescAreaAntes != $DescArea){
																												echo"<option value=\"\">$DescArea</option>\n";
																												$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																										}
																										if($CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento){
																												echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																										}
																										if($Localizacao == $Linha[0]){
																												echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																										}else{
																												echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																										}
																										$DescAreaAntes       = $DescArea;
																										$CodEquipamentoAntes = $CodEquipamento;
																										$EquipamentoAntes    = $Equipamento;
																								}
																								echo "</select>\n";
																								$CarregaLocalizacao = "";
																						}
																				}
																		}
																}
														}
														$db->disconnect();
														?>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
													<td class="textonormal">
														<select name="TipoMovimentacao" class="textonormal" onChange="submit();">
															<option value="">Selecione o Tipo de Movimentação...</option>
															<option value="E" <?php if($TipoMovimentacao == "E"){ echo "selected"; }?>>ENTRADA</option>
															<option value="S" <?php if($TipoMovimentacao == "S"){ echo "selected"; }?>>SAÍDA</option>
														</select>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação*2</td>
													<td class="textonormal">
														<?php
														$MovimentacaoOK = 0;
														$db       = Conexao();

														# Pega o perfil do usuário ativo - Para liberar acesso a Acerto de Inventário para o Gestor Almoxarifado 2 #
														$sqlperf  = "SELECT B.CPERFICODI FROM SFPC.TBUSUARIOPERFIL A, SFPC.TBPERFIL B ";
														$sqlperf .= " WHERE A.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
														$sqlperf .= "   AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
														$sqlperf .= "   AND A.CPERFICODI = B.CPERFICODI ";
														$resperf  = $db->query($sqlperf);
														////($resperf);die;
														if( db::isError($resperf) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlperf");
														}else{
																$PerfArray  = $resperf->fetchRow();
																$PerfilCorp = $PerfArray[0];
														}

														# Pega os tipos das movimentações #
														$sql  = "SELECT CTIPMVCODI, ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
														$sql .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' AND CTIPMVCODI NOT IN(3,4,5,7,8,18,33,34";

														# CASO PERFIL NAO TENHA VISÃO CORPORATIVA REMOVE A MOVIMENTACAO ACERTO DE INVENTARIO
														if ($PerfilCorp!=2){
															$sql .= ",25,28"; //ORIGINAL -> REMOVER COMENTARIO, apenas para TESTE
														}

														$sql .= ")";
														$sql .= " ORDER BY ETIPMVDESC ";
														$result   = $db->query($sql);
														if( db::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $result->numRows();
																echo "<select name=\"Movimentacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione uma Movimentacao...</option>\n";
																for($i=0; $i< $Rows; $i++){
																		$Linha = $result->fetchRow();
																		if($Movimentacao == $Linha[0]){
																				echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>";
																				$MovimentacaoOK = 1;
																		}else{
																				echo "<option value=\"$Linha[0]\">$Linha[1]</option>";
																		}
																}
																if ($MovimentacaoOK != 1) $Movimentacao = null;
																echo "</select>";
														}
														$db->disconnect();
														?>
													</td>
												</tr>

												<?php
												if( ($Movimentacao) and ($MovimentacaoOK == 1) ){
												?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Movimentação*</td>
													<td class="textonormal">
														<?php
																$db = Conexao();
																# Carrega o próximo número de movimentação que será incluído # #
																$sql  = "SELECT MAX(CMOVMACODT) ";
																$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL  ";
																$sql .= " WHERE CTIPMVCODI = '$Movimentacao' ";
																$sql .= "   AND CALMPOCODI = '$Almoxarifado' ";
																$sql .= "   AND AMOVMAANOM = '$AnoMovimentacao' ";
																$res  = $db->query($sql);
																if( db::isError($res) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																}else{
																		$Linha = $res->fetchRow();
																		$ProxMovimentacao = $Linha[0] + 1;
																}
																$db->disconnect();
																echo "$AnoMovimentacao / $ProxMovimentacao<BR>";
																echo "<input type=\"hidden\" name=\"ProxMovimentacao\" value=\"$ProxMovimentacao\">";
																?>
															</td>
														</tr>
												<?php
												}else{
														$ProxMovimentacao = "";
												}
												?>
												<!-- Se a Movimentacao for Baixada ou atendida -->
												<?php if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 or $Movimentacao == 21 or $Movimentacao == 22){ ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Número/Ano da Requisição*</td>
													<td class="textonormal" colspan="2">
														<input type="text" name="Requisicao" size="10" maxlength="9" class="textonormal" value="<?php echo $Requisicao; ?>"> /
														<input type="text" name="AnoRequisicao" size="4" maxlength="4" class="textonormal" value="<?php echo $AnoRequisicao; ?>">
													</td>
												</tr>
												<?php } ?>
												<?php if($Movimentacao == 21 or $Movimentacao == 22){ ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" width="30%">
														<input type="radio" name="CNPJ_CPF" value="1" <?php if($CNPJ_CPF == 1){ echo "checked"; }?>>CNPJ*
														<input type="radio" name="CNPJ_CPF" value="2" <?php if($CNPJ_CPF == 2){ echo "checked"; }?>>CPF*
													</td>
													<td class="textonormal">
														<input type="text" name="CnpjCpf" size="15" maxlength="14" value="<?php echo $CnpjCpf; ?>" class="textonormal">
														<a href="javascript:enviar('Verificar');"><img src="../midia/lupa.gif" border="0"></a>
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
													<td class="textonormal">
														<font class="textonormal"><?php echo $RazaoSocial; ?></font>
														<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
													</td>
												</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Número / Série da Nota*</td>
													<td class="textonormal" colspan="2">
														<input type="text" name="NumeroNota" size="10" maxlength="10" class="textonormal" value="<?php echo $NumeroNota; ?>"> /
														<input type="text" name="SerieNota" size="10" maxlength="8" class="textonormal" value="<?php echo $SerieNota; ?>">
													</td>
												</tr>
												<?php } ?>

												<?php if( ($Movimentacao) and ($Movimentacao != 6) and ($Movimentacao != 9) and ($Movimentacao != 11) and ($Movimentacao != 13) and ($Movimentacao != 29) and ($Movimentacao != 31) ){ ?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Material*</td>
													<td class="textonormal" colspan="2">
													<?
													if($Movimentacao == 10 or $Movimentacao == 32){
															echo "
															<select name='TipoPesquisa' class='textonormal'>
																<option value='0' ";
																if($TipoPesquisa == 0){ echo 'selected'; }
																echo ">Cadastro de Material</option>
																<option value='1' ";
																if($TipoPesquisa == 1 or $TipoPesquisa == ''){ echo 'selected'; }
																echo ">Itens em Estoque</option>
															</select>";
													}
													?>
														<select name="Opcao" class="textonormal">
															<option value="0" <?php if($Opcao == 0 or $Opcao == ""){ echo "selected"; }?>>Código Reduzido</option>
															<option value="1" <?php if($Opcao == 1){ echo "selected"; }?>>Descrição contendo</option>
															<option value="2" <?php if($Opcao == 2){ echo "selected"; }?>>Descrição iniciada por</option>
														</select>
														<BR>
														<input type="text" name="DescMaterial" size="10" maxlength="10" class="textonormal" value="<?php echo $DescMaterial; ?>">
														<a href="javascript:enviar('Validar');"><img src="../midia/lupa.gif" border="0"></a>
													</td>
												</tr>
												<?php } ?>
											</table>
										</td>
									</tr>
									<tr>
										<td class="textonormal" colspan="8" align="right">
											<input type="hidden" name="Orgao" value="<?php echo $Orgao;?>">
											<input type="hidden" name="Botao">
											<input align="right" type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
										</td>
									</tr>
									<?php
									# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ORGÃOS #
									If($Movimentacao == 6){
											$db        = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, ";
											$sqlEmpre .= "       A.VMOVMAVALO, A.CALMPOCODI, B.EUNIDMSIGL, ";
											$sqlEmpre .= "       C.EMATEPDESC, D.EALMPODESC, A.CMOVMACODI, ";
											$sqlEmpre .= "       A.AMOVMAANOM, A.CTIPMVCODI, ";
											$sqlEmpre	.= "       A.AMOVMAMATR, A.NMOVMARESP  "; // MATRICULA E NOME DO RESPONSAVEL PELA MOVIMENTAÇÃO
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU ";
											$sqlEmpre .= "   AND C.CUNIDMCODI = B.CUNIDMCODI ";
											$sqlEmpre .= "   AND D.CALMPOCODI = A.CALMPOCODI ";
											$sqlEmpre .= "   AND A.CALMPOCOD1 = $Almoxarifado ";
											$sqlEmpre .= "   AND A.CTIPMVCODI = 12 ";
											$sqlEmpre .= "   AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";
											$res       = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS DE EMPRÉSTIMO</td>\n";
													echo "</tr>\n";
													if($QtdRes > 0){
															echo "<tr>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOXARIFADO ORIGEM</td>\n";
															echo "</tr>\n";
															while( $rowEmpre = $res->fetchRow() ){
																	$CodigoReduzido    = $rowEmpre[1];
																	$QtdMovimentada    = $rowEmpre[2];
																	$Valor             = $rowEmpre[3];
																	$AlmoxSec          = $rowEmpre[4];
																	$MaterialDescricao = $rowEmpre[6];
																	$AlmoxSecDesc      = $rowEmpre[7];
																	$SeqMovimentacao   = $rowEmpre[8];
																	$AnoAtualizar      = $rowEmpre[9];
																	$MovimentacaoSecun = $rowEmpre[10];
																	$MatriculaSecun    = $rowEmpre[11];
																	$ResponsavelSecun  = $rowEmpre[12];
																	$ResponsavelSecun  = urlencode($ResponsavelSecun);
																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&AlmoxSec=$AlmoxSec&MovimentacaoSecun=$MovimentacaoSecun&MatriculaSecun=$MatriculaSecun&ResponsavelSecun=$ResponsavelSecun";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxSecDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens emprestados por outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}

									# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
									}elseif($Movimentacao == 9){
										  # Esta movimentação trabalha com valor médio, pois precisa resgatar o valor original do almoxarifado para devolução #
											$db        = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, ";
											$sqlEmpre .= "       A.VMOVMAUMED, A.CALMPOCODI, B.EUNIDMSIGL, ";
											$sqlEmpre .= "       C.EMATEPDESC, D.EALMPODESC, A.CMOVMACODI, ";
											$sqlEmpre .= "       A.AMOVMAANOM, A.CTIPMVCODI, A.AMOVMAMATR, ";
											$sqlEmpre .= "       A.NMOVMARESP "; // MATRICULA E NOME DO RESPONSAVEL PELA MOVIMENTAÇÃO
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU ";
											$sqlEmpre .= "   AND C.CUNIDMCODI = B.CUNIDMCODI ";
											$sqlEmpre .= "   AND D.CALMPOCODI = A.CALMPOCODI ";
											$sqlEmpre .= "   AND A.CALMPOCOD1 = $Almoxarifado ";
											$sqlEmpre .= "   AND A.CTIPMVCODI = 13 ";
											$sqlEmpre .= "   AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";
											$res  = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS DE EMPRÉSTIMO</td>\n";
													echo "</tr>\n";
													if($QtdRes > 0){
															echo "<tr>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOXARIFADO ORIGEM</td>\n";
															echo "</tr>\n";
															while( $rowEmpre = $res->fetchRow() ){
																	$CodigoReduzido    = $rowEmpre[1];
																	$QtdMovimentada    = $rowEmpre[2];
																	$Valor             = $rowEmpre[3];
																	$AlmoxSec          = $rowEmpre[4];
																	$MaterialDescricao = $rowEmpre[6];
																	$AlmoxSecDesc      = $rowEmpre[7];
																	$SeqMovimentacao   = $rowEmpre[8];
																	$AnoAtualizar      = $rowEmpre[9];
																	$MovimentacaoSecun = $rowEmpre[10];
																	$MatriculaSecun    = $rowEmpre[11];
																	$ResponsavelSecun  = $rowEmpre[12];
																	$ResponsavelSecun  = urlencode($ResponsavelSecun);
																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&AlmoxSec=$AlmoxSec&MovimentacaoSecun=$MovimentacaoSecun&MatriculaSecun=$MatriculaSecun&ResponsavelSecun=$ResponsavelSecun";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxSecDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens devolvidos por outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}

									# CASO SEJA ENTRADA POR TROCA #
									}elseif($Movimentacao == 11){
											$db     = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, A.VMOVMAVALO, ";
											$sqlEmpre .= "       A.CALMPOCODI, B.EUNIDMSIGL, C.EMATEPDESC, D.EALMPODESC, ";
											$sqlEmpre .= "       A.CMOVMACODI, A.AMOVMAANOM, A.CTIPMVCODI, ";
											$sqlEmpre .= "       A.AMOVMAMATR, A.NMOVMARESP, "; // MATRICULA E NOME DO RESPONSAVEL PELA MOVIMENTAÇÃO
											$sqlEmpre .= "       A.CMATEPSEQ1, A.AMOVMAQCOR ";
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU AND C.CUNIDMCODI = B.CUNIDMCODI AND ";
											$sqlEmpre .= "       D.CALMPOCODI = A.CALMPOCODI AND A.CALMPOCOD1 = $Almoxarifado AND ";
											$sqlEmpre .= "       A.CTIPMVCODI = 15 AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";
											$res  = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS PARA TROCA</td>\n";
													echo "</tr>\n";
													if($QtdRes > 0){
															echo "<tr>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOXARIFADO ORIGEM</td>\n";
															echo "</tr>\n";
															while( $rowEmpre = $res->fetchRow() ){
																	$CodigoReduzido    = $rowEmpre[1];
																	$QtdMovimentada    = $rowEmpre[2];
																	$Valor             = $rowEmpre[3];
																	$AlmoxSec          = $rowEmpre[4];
																	$MaterialDescricao = $rowEmpre[6];
																	$AlmoxSecDesc      = $rowEmpre[7];
																	$SeqMovimentacao   = $rowEmpre[8];
																	$AnoAtualizar      = $rowEmpre[9];
																	$MovimentacaoSecun = $rowEmpre[10];
																	$MatriculaSecun    = $rowEmpre[11];
																	$ResponsavelSecun  = $rowEmpre[12];
																	$ResponsavelSecun  = urlencode($ResponsavelSecun);
																	$CodReduzMat2      = $rowEmpre[13];
																	$QuantMat2         = $rowEmpre[14];
																	$QuantMat2         = str_replace(".",",",$QuantMat2);
																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&AlmoxSec=$AlmoxSec&MovimentacaoSecun=$MovimentacaoSecun&MatriculaSecun=$MatriculaSecun&ResponsavelSecun=$ResponsavelSecun&CodReduzMat2=$CodReduzMat2&QuantMat2=$QuantMat2";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxSecDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens de troca de outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}

									# CASO SEJA SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ORGÃOS #
									}elseif($Movimentacao == 13){
											$db        = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, ";
											$sqlEmpre .= "       A.VMOVMAVALO, A.CALMPOCOD1, B.EUNIDMSIGL, ";
											$sqlEmpre .= "       C.EMATEPDESC, D.EALMPODESC, A.CMOVMACODI, ";
											$sqlEmpre .= "       A.AMOVMAANOM, A.CTIPMVCODI ";
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU ";
											$sqlEmpre .= "   AND C.CUNIDMCODI = B.CUNIDMCODI ";
											$sqlEmpre .= "   AND D.CALMPOCODI = A.CALMPOCOD1 ";
											$sqlEmpre .= "   AND A.CALMPOCODI = $Almoxarifado ";
											$sqlEmpre .= "   AND A.CTIPMVCODI = 6 ";
											$sqlEmpre .= "   AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";
											$res       = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS DE EMPRÉSTIMO</td>\n";
													echo "</tr>\n";
													if($QtdRes > 0){
															echo "<tr>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOX DESTINO</td>\n";
															echo "</tr>\n";
															while( $rowEmpre = $res->fetchRow() ){
																	$CodigoReduzido    = $rowEmpre[1];
																	$QtdMovimentada    = $rowEmpre[2];
																	$Valor             = $rowEmpre[3];
																	$AlmoxSec          = $rowEmpre[4];
																	$MaterialDescricao = $rowEmpre[6];
																	$AlmoxSecDesc      = $rowEmpre[7];
																	$SeqMovimentacao   = $rowEmpre[8];
																	$AnoAtualizar      = $rowEmpre[9];
																	$MovimentacaoSecun = $rowEmpre[10];
																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&AlmoxSec=$AlmoxSec&MovimentacaoSecun=$MovimentacaoSecun";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxSecDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens emprestados por outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}

									# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS #
									}elseif($Movimentacao == 29){
											$db     = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, A.VMOVMAVALO, ";
											$sqlEmpre .= "       A.CALMPOCODI, B.EUNIDMSIGL, C.EMATEPDESC, D.EALMPODESC, ";
											$sqlEmpre .= "       A.CMOVMACODI, A.AMOVMAANOM, A.CTIPMVCODI, ";
											$sqlEmpre .= "       A.AMOVMAMATR, A.NMOVMARESP "; // MATRICULA E NOME DO RESPONSAVEL PELA MOVIMENTAÇÃO
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU AND C.CUNIDMCODI = B.CUNIDMCODI AND ";
											$sqlEmpre .= "       D.CALMPOCODI = A.CALMPOCODI AND A.CALMPOCOD1 = $Almoxarifado AND ";
											$sqlEmpre .= "       A.CTIPMVCODI = 30 AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";
											$res  = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS DE DOAÇÃO</td>\n";
													echo "</tr>\n";
													if($QtdRes > 0){
															echo "<tr>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOXARIFADO ORIGEM</td>\n";
															echo "</tr>\n";
															while( $rowEmpre = $res->fetchRow() ){
																	$CodigoReduzido    = $rowEmpre[1];
																	$QtdMovimentada    = $rowEmpre[2];
																	$Valor             = $rowEmpre[3];
																	$AlmoxSec          = $rowEmpre[4];
																	$MaterialDescricao = $rowEmpre[6];
																	$AlmoxSecDesc      = $rowEmpre[7];
																	$SeqMovimentacao   = $rowEmpre[8];
																	$AnoAtualizar      = $rowEmpre[9];
																	$MovimentacaoSecun = $rowEmpre[10];
																	$MatriculaSecun    = $rowEmpre[11];
																	$ResponsavelSecun  = $rowEmpre[12];
																	$ResponsavelSecun  = urlencode($ResponsavelSecun);
																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&AlmoxSec=$AlmoxSec&MovimentacaoSecun=$MovimentacaoSecun&MatriculaSecun=$MatriculaSecun&ResponsavelSecun=$ResponsavelSecun";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxSecDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens doados por outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}

									# CASO SEJA ENTRADA POR CANCELAMENTO DE MOVIMENTAÇÃO #
									}elseif($Movimentacao == 31){
											$db        = Conexao();
											$sqlEmpre  = "SELECT A.CALMPOCODI, A.CMATEPSEQU, A.AMOVMAQTDM, ";
											$sqlEmpre .= "       A.VMOVMAVALO, A.CALMPOCOD1, B.EUNIDMSIGL, ";
											$sqlEmpre .= "       C.EMATEPDESC, D.EALMPODESC, A.CMOVMACODI, ";
											$sqlEmpre .= "       A.AMOVMAANOM, A.CTIPMVCODI, A.AMOVMAMATR, ";
											$sqlEmpre .= "       A.NMOVMARESP, E.ETIPMVDESC ";
											$sqlEmpre .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBUNIDADEDEMEDIDA B, ";
											$sqlEmpre .= "       SFPC.TBMATERIALPORTAL C, SFPC.TBALMOXARIFADOPORTAL D, SFPC.TBTIPOMOVIMENTACAO E ";
											$sqlEmpre .= " WHERE A.CMATEPSEQU = C.CMATEPSEQU ";
											$sqlEmpre .= "   AND C.CUNIDMCODI = B.CUNIDMCODI ";
											$sqlEmpre .= "   AND D.CALMPOCODI = A.CALMPOCOD1 ";
											$sqlEmpre .= "   AND A.CTIPMVCODI = E.CTIPMVCODI ";
											$sqlEmpre .= "   AND A.CALMPOCODI = $Almoxarifado ";
											$sqlEmpre .= "   AND (A.CTIPMVCODI = 12 or A.CTIPMVCODI = 13 or A.CTIPMVCODI = 15 or A.CTIPMVCODI = 30) "; // Empréstimo (12), Devolução (13), Troca (15), Doação (30)
											$sqlEmpre .= "   AND (A.FMOVMACORR IS NULL OR A.FMOVMACORR = 'N')";                                        // Movimentações que ainda não foram concluídas
											$sqlEmpre .= " ORDER BY E.ETIPMVDESC, C.EMATEPDESC ";
											$res  = $db->query($sqlEmpre);
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEmpre");
											}else{
													$QtdRes = $res->numRows();
													if($QtdRes > 0){
															while( $rowEmpre        = $res->fetchRow() ){
																	$CodigoReduzido     = $rowEmpre[1];
																	$QtdMovimentada     = $rowEmpre[2];
																	$Valor              = $rowEmpre[3];
																	$AlmoxDestino       = $rowEmpre[4];
																	$MaterialDescricao  = $rowEmpre[6];
																	$AlmoxDestinoDesc   = $rowEmpre[7];
																	$SeqMovimentacao    = $rowEmpre[8];
																	$AnoAtualizar       = $rowEmpre[9];
																	$DescMovimentacao   = $rowEmpre[13];

																	if($DescMovimentacao != $DescMovimentacaoAnt){
																			$DescMovimentacaoAnt = $DescMovimentacao;
																			echo "<tr>\n";
																			echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">$DescMovimentacao</td>\n";
																			echo "</tr>\n";

																			echo "<tr>\n";
																			echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
																			echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
																			echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\" align=\"center\">ALMOXARIFADO DESTINO</td>\n";
																			echo "</tr>\n";
																	}

																	echo "<tr>\n";
																	$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa&Valor=$Valor&QtdMovimentada=$QtdMovimentada&SeqMovimentacao=$SeqMovimentacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar";
																	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' width='50%'><a href='$Url'><font color='#000000'>$MaterialDescricao</font></a></td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='10%'>$CodigoReduzido</td>";
																	echo "	<td valign='top' bgcolor='#F7F7F7' class='textonormal' align='center' width='40%'>$AlmoxDestinoDesc</td>";
																	echo "</tr>";
															}
													}else{
															echo "<tr>\n";
															echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">ITENS DE MOVIMENTAÇÃO</td>\n";
															echo "</tr>\n";
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"5\" >\n";
															echo "		Não existem itens doados, emprestados ou para trocas por outro(s) almoxarifado(s).\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}
									}else{
											if($DescMaterial != ""){
													if($Opcao == 0){
															if( !SoNumeros($DescMaterial) ){ $sqlgeral = ""; }
													}
											}
											if( ($TipoMovimentacao == "") || ($Almoxarifado == 0) || ($Movimentacao == 0) ){
													$sqlgeral = "";
											}
											if($sqlgeral != ""){
													if($Mens == 0 and $DescMaterial != ""){
															$db     = Conexao();
															$res    = $db->query($sqlgeral);
															if( db::isError($res) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
															}else{
																	$qtdres = $res->numRows();
																	echo "<tr>\n";
																	echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"8\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
																	echo "</tr>\n";
																	if($qtdres > 0){
																			if($Movimentacao != 26 and $Movimentacao != 27){
																					echo "<tr>\n";
																					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
																					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
																					echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
																					echo "</tr>\n";

																					while( $row = $res->fetchRow() ){
																							$CodigoReduzido    = $row[0];
																							$MaterialDescricao = $row[1];
																							$UndMedidaSigla    = $row[2];
																							$SeqRequisicao     = $row[3];
																							$Situacao          = $row[4];
																							echo "<tr>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																							if( ($Movimentacao == 2) or ($Movimentacao == 19) or ($Movimentacao == 20) ){ // Movimentações que envolvem requisição, envia o número da requisição
																									$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&AnoMovimentacao=$AnoMovimentacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&SeqRequisicao=$SeqRequisicao&Situacao=$Situacao&EstoqueVirtual=$EstoqueVirtual";
																									echo "	<a href=\"$Url\"><input type=\"hidden\" name=\"Material\" value=\"$row[0]\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
																							}elseif( ($Movimentacao == 21) or ($Movimentacao == 22) ){                     // Se for movimentações para cancelamento de nota, envia para a página seguinte o número e série da nota, e no caso de acerto de Devolução Interna, envia também o número da Requisição
																									$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&AnoMovimentacao=$AnoMovimentacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&FornecedorCod=$FornecedorCod&NumeroNota=$NumeroNota&SerieNota=$SerieNota&SeqRequisicao=$SeqRequisicao&TipoPesquisa=$TipoPesquisa&EstoqueVirtual=$EstoqueVirtual";
																									echo "	<a href=\"$Url\"><input type=\"hidden\" name=\"Material\" value=\"$row[0]\"><font color=\"#000000\">$MaterialDescricao</font></a>";
																							}else{
																									$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&AnoMovimentacao=$AnoMovimentacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao&TipoPesquisa=$TipoPesquisa";
																									echo "	<a href=\"$Url\"><input type=\"hidden\" name=\"Material\" value=\"$row[0]\"><font color=\"#000000\">$MaterialDescricao</font></a>";
																							}
																							echo "	</td>";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo "		$CodigoReduzido";
																							echo "	</td>\n";
																							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																							echo "	</td>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo "		$UndMedidaSigla";
																							echo "	</td>\n";
																							echo "</tr>\n";
																					}
																			}else{ // Entrada/Saída Por Cancelamento de Movimentação sem Retorno

																					while( $row = $res->fetchRow() ){
																							$Data              = $row[4];
																							$MaterialDescricao = $row[1];
																							$CodigoReduzido    = $row[0];
																							$UndMedidaSigla    = $row[2];
																							$MovDesc           = $row[3];
																							$QtdMovimentada    = $row[5];
																							$AnoAtualizar      = $row[6];
																							$SeqMovimentacao   = $row[7];
																							$CodMovT           = $row[8];
																							if($MovDesc != $MovimentacaoExibida) {
																									$MovimentacaoExibida = $MovDesc;
																									echo "<tr>\n";
																									echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"6\" class=\"titulo3\">".$MovDesc."</td>\n";
																									echo "</tr>\n";
																									echo "<tr>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"CENTER\">COD.MOV.</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"CENTER\">DATA</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
																									echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
																									echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNID.</td>\n";
																									echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">QUANT.</td>\n";
																									echo "</tr>\n";
																							}
																							echo "<tr>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo $CodMovT;
																							echo "	</td>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo DataBarra($Data);
																							echo "	</td>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"50%\">\n";
																							$Url = "CadMovimentacaoConfirmar.php?CodigoReduzido=$CodigoReduzido&TipoMovimentacao=$TipoMovimentacao&Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&AnoMovimentacao=$AnoMovimentacao&AnoAtualizar=$AnoAtualizar&SeqMovimentacao=$SeqMovimentacao&Movimentacao=$Movimentacao&MovNumero=$ProxMovimentacao";
																							echo "	<a href=\"$Url\"><input type=\"hidden\" name=\"Material\" value=\"$row[0]\"><font color=\"#000000\">$MaterialDescricao</font></a>";
																							echo "	</td>";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo "		$CodigoReduzido";
																							echo "	</td>\n";
																							echo "	</td>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo "		$UndMedidaSigla";
																							echo "	</td>\n";
																							echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																							echo "		$QtdMovimentada";
																							echo "	</td>\n";
																							echo "</tr>\n";
																							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																					}
																			}
																	}else{
																			echo "<tr>\n";
																			echo "	<td class=\"textonormal\" colspan=\"8\" >\n";
																			echo "		Pesquisa sem Ocorrências.\n";
																			echo "	</td>\n";
																			echo "</tr>\n";
																	}
															}
															$db->disconnect();
													}
											}
									}
							}
							?>
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
