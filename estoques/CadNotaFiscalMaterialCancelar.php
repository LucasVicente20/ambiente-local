<?php
# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadNotaFiscalMaterialCancelar.php
# Objetivo: Programa de Exclusão de Itens na Nota Fiscal a partir da Pesquisa
# Autor:    Altamiro Pedrosa
# Data:     13/09/2005
# OBS.:     Tabulação 2 espaços
#           Deixar os comentário pra validação de empenho obrigatório
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     16/02/2006 - Regras para exclusão de itens
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/05/2006 - Regras de exclusão de itens para as novas movimentações
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     08/06/2006 - Passagem do parâmetro ano da nota para a pesquisa de movimentações
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/06/2006 - Bloqueio de duplo cancelamento
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     27/07/2006 - Exibir mais de um empenho
# -------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     02/02/2007 - Relatório de auxílio para Cancelamento de Nota Fiscal
# -------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Inclusao de bloqueio para evitar movimentacoes anteriores a ultimo inventario
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/02/2008 - Alteração para permitir que a data do inventário do almoxarifado seja colocado a data da última atualização ao invés da data de emissão ao cancelar a nota fiscal.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      07/07/2008 - Alteração para inserir no campo estoque virtual na tabela de armazenamento de material e flag para identificar uma nota fiscal Virtual.
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      07/07/2008 - Alteração para inserir no campo estoque virtual na tabela de armazenamento de material e flag para identificar uma nota fiscal Virtual.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      10/09/2008 - Remoção de acesso a SFPC.TBFORNECEDORESTOQUE (Pois seus dados serão migrados para SFPC.TBFORNECEDORCREDENCIADO)
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      13/11/2008 - Correção para obter o valor da última movimentação para permitir desfazer movimentações, como por exemplo: Cancelamento de nota fiscal
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      27/11/2008 - Corrigindo Programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos.
#                                  Foi convecionado que um empenho com parcela igual a zero não será um subempenho, mas um empenho pois a chave primária da tabela foi
#                                  alterada para incluir a parcela do subempenho. Desta forma quando a parcela tiver o valor 0 será um empenho e se tiver um valor
#                                  diferente de 0 será um subempenho.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      10/09/2008 - Remoção de acesso a SFPC.TBFORNECEDORESTOQUE (Pois seus dados serão migrados para SFPC.TBFORNECEDORCREDENCIADO)
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      13/11/2008 - Correção para obter o valor da última movimentação para permitir desfazer movimentações, como por exemplo: Cancelamento de nota fiscal
# -------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      27/11/2008 - Corrigindo Programa para permitir entrada de empenhos com sequenciais diferentes, ou seja, diferentes subempenhos.
#                                  Foi convecionado que um empenho com parcela igual a zero não será um subempenho, mas um empenho pois a chave primária da tabela foi
#                                  alterada para incluir a parcela do subempenho. Desta forma quando a parcela tiver o valor 0 será um empenho e se tiver um valor
#                                  diferente de 0 será um subempenho.
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      01/12/2008	- Não permitir exclusão de notas fiscais virtuais
#												- Redirecionar para página de pesquisa caso os dados do request http sejam perdidos
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     08/09/2009 - Mudando variáveis GET do RelAuxilioCancelamentoNotaPdf.php. Ao invés de enviar o Ulat e
#           período, agora é enviado a nota fiscal a ser cancelada. Necessário para modificações no relatório
# -------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     10/12/2009 - Na parte de fazer o cancelamento da NF, quando há um erro imprevisto no banco, a ferramenta
#						enviava um email de erro mas informava ao usuário que o cancelamento foi bem sucedido.
#						Pior, em alguns pontos não era dado um ROLLBACK e depois era dado um COMMIT. Corrigindo estes erros.
# -------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );
AddMenuAcesso( '/estoques/RelAuxilioCancelamentoNotaPdf.php' );
AddMenuAcesso( '/estoques/CadNotaFiscalMaterialCancelarSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$Localizacao    = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$NotaFiscal     = $_POST['NotaFiscal'];
		$AnoNota        = $_POST['AnoNota'];
		$CNPJ_CPF       = $_POST['CNPJ_CPF'];
		if( $_POST['CnpjCpf'] != "" ){
				if( $CNPJ_CPF == 1 ){
						$CnpjCpf	= substr("00000000000000".$_POST['CnpjCpf'],-14);
				}else{
						$CnpjCpf	= substr("00000000000".$_POST['CnpjCpf'],-11);
				}
		}else{
				$CnpjCpf	= $_POST['CnpjCpf'];
		}
		$NumeroNota        = $_POST['NumeroNota'];
		$SerieNota         = $_POST['SerieNota'];
		$DataEntrada       = $_POST['DataEntrada'];
		$DataEmissao       = $_POST['DataEmissao'];
    $DataUltimaAlteracao = $_POST['DataUltimaAlteracao'];
		$ValorNota         = $_POST['ValorNota'];
		$ValNota           = $_POST['ValNota'];
		$AnoEmpenho        = $_POST['AnoEmpenho'];
		$OrgaoEmpenho      = $_POST['OrgaoEmpenho'];
		$UnidadeEmpenho    = $_POST['UnidadeEmpenho'];
		$SequencialEmpenho = $_POST['SequencialEmpenho'];
		$ParcelaEmpenho    = $_POST['ParcelaEmpenho'];
		$Material          = $_POST['Material'];
		$DescMaterial      = $_POST['DescMaterial'];
		$Unidade           = $_POST['Unidade'];
		$Quantidade        = $_POST['Quantidade'];
		$ValorUnitario     = $_POST['ValorUnitario'];
		$ValorTotal        = $_POST['ValorTotal'];
		$RazaoSocial   		 = $_POST['RazaoSocial'];
		$DataHora          = $_POST['DataHora'];
		$Empenhos          = $_POST['Empenhos'];
    $EstoqueVirtual    = $_POST['EstoqueVirtual'];
		for( $i=0;$i<count($DescMaterial);$i++ ){
				$ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$DataHora[$i];
		}
}else{
		$NotaFiscal        = $_GET['NotaFiscal'];
		$AnoNota           = $_GET['AnoNota'];
		$Almoxarifado      = $_GET['Almoxarifado'];
}

# Caso os dados do request http sejam perdidos, redirecionar para procura
if(is_null($Almoxarifado)){
		header("location: /portalcompras/estoques/CadNotaFiscalMaterialCancelarSelecionar.php");
		exit;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano do Exercicio #
$AnoExercicio = date("Y");
$DataAtual    = date("Y-m-d");

if($Botao == "Voltar"){
		header("location: CadNotaFiscalMaterialCancelarSelecionar.php");
		exit;
}
if($Botao == ""){

		# Pega os dados da Entrada por NF de acordo com o Sequencial #
		$db   = Conexao();
		$sql  = "SELECT A.AENTNFNOTA, A.AENTNFSERI, A.DENTNFENTR, A.DENTNFEMIS, ";
		$sql .= "       A.VENTNFTOTA, B.AITENFQTDE, B.VITENFUNIT, ";
		$sql .= "       C.CMATEPSEQU, C.EMATEPDESC, D.EUNIDMSIGL, A.AFORCRSEQU, ";
		$sql .= "       A.CFORESCODI, B.TITENFULAT, A.TENTNFULAT, A.FENTNFVIRT ";
		$sql .= "  FROM SFPC.TBENTRADANOTAFISCAL A, SFPC.TBITEMNOTAFISCAL B, SFPC.TBMATERIALPORTAL C, ";
		$sql .= "       SFPC.TBUNIDADEDEMEDIDA D ";
		$sql .= " WHERE A.CENTNFCODI = B.CENTNFCODI AND B.CMATEPSEQU = C.CMATEPSEQU ";
		$sql .= "   AND A.CALMPOCODI = B.CALMPOCODI AND A.CENTNFCODI = B.CENTNFCODI ";
		$sql .= "   AND A.AENTNFANOE = B.AENTNFANOE AND A.CALMPOCODI = $Almoxarifado ";
		$sql .= "   AND A.CENTNFCODI = $NotaFiscal  AND A.AENTNFANOE = $AnoNota ";
		$sql .= "   AND C.CUNIDMCODI = D.CUNIDMCODI ";
		$sql .= " ORDER BY A.AENTNFNOTA, C.EMATEPDESC ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				for($i=0; $i<$Rows; $i++){
						$Linha              = $res->fetchRow();
						$NumeroNota         = $Linha[0];
						$SerieNota          = $Linha[1];
						$DataEntrada        = DataBarra($Linha[2]);
						$DataEmissao        = DataBarra($Linha[3]);
						$ValNota            = str_replace(",",".",$Linha[4]);
						$Quantidade[$i]     = str_replace(",",".",$Linha[5]);
						$ValorUnitario[$i]  = str_replace(",",".",$Linha[6]);
						$Material[$i]       = $Linha[7];
						$DescMaterial[$i]   = RetiraAcentos($Linha[8]).$SimboloConcatenacaoDesc.str_replace("\"","”",$Linha[8]);
						$Unidade[$i]        = $Linha[9];
						$FornecedorSequ     = $Linha[10];
						$FornecedorCodi     = $Linha[11];
						$DataHora[$i]       = $Linha[12];
						$ItemNotaFiscal[$i] = $DescMaterial[$i].$SimboloConcatenacaoArray.$Material[$i].$SimboloConcatenacaoArray.$Unidade[$i].$SimboloConcatenacaoArray.$Quantidade[$i].$SimboloConcatenacaoArray.$ValorUnitario[$i].$SimboloConcatenacaoArray.$DataHora[$i];
            $DataUltimaAlteracao = $Linha[13];
            $EstoqueVirtual    = $Linha[14];
				}

				# Recupera dados dos empenhos #
				$sqlemp  = "SELECT ANFEMPANEM, CNFEMPOREM, CNFEMPUNEM, ";
				$sqlemp .= "       CNFEMPSEEM, CNFEMPPAEM ";
				$sqlemp .= "  FROM SFPC.TBNOTAFISCALEMPENHO ";
				$sqlemp .= " WHERE CALMPOCODI = $Almoxarifado ";
				$sqlemp .= "   AND AENTNFANOE = $AnoNota ";
				$sqlemp .= "   AND CENTNFCODI = $NotaFiscal ";
				$resemp  = $db->query($sqlemp);
				if(PEAR::isError($resemp)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlemp");
				}else{
						while($LinhaEmp = $resemp->fetchRow()){
								$AnoEmp        = $LinhaEmp[0];
								$OrgaoEmp      = $LinhaEmp[1];
								$UnidadeEmp    = $LinhaEmp[2];
								$SequencialEmp = $LinhaEmp[3];
								$ParcelaEmp    = $LinhaEmp[4];


								$Empenhos[] = "$AnoEmp.$OrgaoEmp.$UnidadeEmp.$SequencialEmp.$ParcelaEmp";


						}
				}

				if( $FornecedorSequ != "" ){
						# Verifica se o Fornecedor de Estoque é Credenciado #
						$sqlforn  = "SELECT NFORCRRAZS, AFORCRCCGC, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sqlforn .= " WHERE AFORCRSEQU = $FornecedorSequ ";
						$resforn  = $db->query($sqlforn);
						if( PEAR::isError($resforn) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
						}else{
								$Linhaforn   = $resforn->fetchRow();
								$RazaoSocial = $Linhaforn[0];
								if( $Linhaforn[1] != "" ){
										$CnpjCpf  = $Linhaforn[1];
										$CNPJ_CPF = 1;
								}else{
										$CnpjCpf  = $Linhaforn[2];
										$CNPJ_CPF = 2;
								}
						}
				}else{/*
						# Verifica se o Fornecedor de Estoque já está cadastrado #
						$sqlforn  = "SELECT EFORESRAZS, AFORESCCGC, AFORESCCPF FROM SFPC.TBFORNECEDORESTOQUE ";
						$sqlforn .= "	WHERE CFORESCODI = $FornecedorCodi ";
						$resforn  = $db->query($sqlforn);
						if( PEAR::isError($resforn) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlforn");
						}else{
								$Linhaforn   = $resforn->fetchRow();
								$RazaoSocial = $Linhaforn[0];
								$CnpjCpf     = $Linhaforn[1];
								if( $Linhaforn[1] != "" ){
										$CnpjCpf  = $Linhaforn[1];
										$CNPJ_CPF = 1;
								}else{
										$CnpjCpf  = $Linhaforn[2];
										$CNPJ_CPF = 2;
								}
						}*/
						EmailErro(__FILE__."- Fornecedor não encontrado.", __FILE__, __LINE__, "Fornecedor informado não foi encontrado em SFPC.TBFORNECEDORCREDENCIADO.\n\nSequencial do fornecedor informado: '".$FornecedorSequ."'\n\nVerificar se o dado informado pelo usuário foi correto ou se o caso de fornecedor inexistente deve ser verificado.");
				}
		}
		$db->disconnect();
}

# Critica para caso tente realizar operacao com data anterior a data de inventario
$db   = Conexao();
$sql  = "SELECT COUNT(B.TINVCOFECH) ";
$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM B ";
$sql .= " INNER JOIN SFPC.TBLOCALIZACAOMATERIAL C ";
$sql .= "    ON B.CLOCMACODI = C.CLOCMACODI ";
$sql .= "   AND C.CALMPOCODI = $Almoxarifado ";
$sql .= " WHERE (B.AINVCOANOB,B.AINVCOSEQU) = ";
$sql .= "       (SELECT MAX(A.AINVCOANOB),MAX(A.AINVCOSEQU) ";
$sql .= "          FROM SFPC.TBINVENTARIOCONTAGEM A ";
$sql .= "         WHERE A.AINVCOANOB = ";
$sql .= "               (SELECT MAX(AINVCOANOB) ";
$sql .= "                  FROM SFPC.TBINVENTARIOCONTAGEM  ";
$sql .= "                 WHERE A.AINVCOANOB = AINVCOANOB ) ";
$sql .= "           AND A.CLOCMACODI = B.CLOCMACODI) ";
//$DataCritica = explode("/",$DataEmissao);
//$sql .= "   AND B.TINVCOFECH < '".$DataCritica[2]."-".$DataCritica[1]."-".$DataCritica[0]."'";
$sql .= "   AND B.TINVCOFECH < '".$DataUltimaAlteracao."'";
$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $res->fetchRow();
		if ($Linha[0]==0){
			$Mens     = 1;
			$Tipo     = 2;
			$Mensagem = "Nota Fiscal não pode ser cancelada pois possui referência a período anterior ao último inventário do almoxarifado";
			$Botao = "";
		}
}
$db->disconnect();

if ($EstoqueVirtual=="S"){
	$Mens     = 1;
	$Tipo     = 1;
	$Mensagem = "Notas Fiscais virtuais não podem ser canceladas manualmente. Para cancelá-las, cancele a requisição que é atendida pela Nota";
	$Botao = "";
}


if($Botao == "Cancelar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Almoxarifado == "" ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif ($Localizacao == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadNotaFiscalCancelar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if( $Mens == 0 ) {
				if( $_SESSION['_cgrempcodi_'] != 0 ){
						# Verifica se a Nota já foi cancelada anteriormente. Para evitar o problema ocorrido com o Almoxarifado da Emprel em Maio de 2006, no qual uma nota foi cancelada duas vezes, provocando problemas na contabilidade.
						$db = Conexao();
						$sql  = "SELECT FENTNFCANC FROM SFPC.TBENTRADANOTAFISCAL ";
						$sql .= " WHERE CALMPOCODI = $Almoxarifado ";
						$sql .= "   AND AENTNFANOE = $AnoNota ";
						$sql .= "   AND CENTNFCODI = $NotaFiscal ";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $res->fetchRow();
								$Canc  = $Linha[0];
								if($Canc == 'S'){
										# Redireciona para a tela de Seleção #
										$Mensagem = "Nota Fiscal Cancelada com Sucesso";
										$Url = "CadNotaFiscalMaterialCancelarSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
										if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("Location: ".$Url);
										exit;
								}else{
										# Verifica se com a retirada do item a quantidade ficara negativa #
										$db->query("BEGIN TRANSACTION");
										if(count($ItemNotaFiscal) != 0){
												sort($ItemNotaFiscal);
												for($i=0; $i< count($ItemNotaFiscal); $i++){
														$Dados             = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
														$DescMaterial[$i]  = $Dados[0];
														$Material[$i]  		 = $Dados[1];
														$Unidade[$i]  		 = $Dados[2];
														$Quantidade[$i]    = str_replace(",",".",$Dados[3]);
														$ValorUnitario[$i] = str_replace(",",".",$Dados[4]);
														$DataHora[$i]      = $Dados[5]; // TimeStamp da inclusão do item da nota
														$ValorTotal[$i]    = str_replace(",",".",($Quantidade[$i]*$ValorUnitario[$i]));

														# Resgata os dados em armazenamentomaterial do material corrente e trava campos para evitar alterações concorrentes #
														$sqlarmat  = "SELECT AARMATQTDE FROM SFPC.TBARMAZENAMENTOMATERIAL ";
														$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
														$sqlarmat .= "   FOR UPDATE ";
														$resarmat  = $db->query($sqlarmat);
														if( PEAR::isError($resarmat) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
														}else{
																$Linhaarmat 	 = $resarmat->fetchRow();
																$QuantidadeEst = str_replace(",",".",$Linhaarmat[0]);
														}

														# Verifica se com a retirada a quantidade e o valor ficaram negativos #
														$QuantidadeVerifica = ($QuantidadeEst - $Quantidade[$i]);
														if ( $QuantidadeVerifica < 0)  {
																$Existe  = "S";
																$Posicao = $i;
														}

														# Verifica se não há movimentação posterior ao cadastramento da nota a ser cancelada para o item em andamento no loop #
														$sqlmov  = "SELECT A.CALMPOCODI, A.AMOVMAANOM, A.CMOVMACODI, A.CTIPMVCODI, A.AMOVMAQTDM, "; // Geral
														$sqlmov .= "       B.ETIPMVDESC, A.DMOVMAMOVI, A.CMOVMACODT, ";                             // Movimentação
														$sqlmov .= "       A.AENTNFANOE, A.CENTNFCODI, E.AENTNFNOTA, E.AENTNFSERI, ";               // Nota Fiscal
														$sqlmov .= "       D.CREQMASEQU, D.CREQMACODI, D.AREQMAANOR ";                              // Requisição
														$sqlmov .= "  FROM SFPC.TBTIPOMOVIMENTACAO B, ";
														$sqlmov .= "       SFPC.TBMOVIMENTACAOMATERIAL A ";
														$sqlmov .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL  E ON (A.CALMPOCODI = E.CALMPOCODI AND A.AENTNFANOE = E.AENTNFANOE AND A.CENTNFCODI = E.CENTNFCODI) ";
														$sqlmov .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL D ON (A.CREQMASEQU = D.CREQMASEQU) ";
														$sqlmov .= " WHERE A.CALMPOCODI = $Almoxarifado ";
														$sqlmov .= "   AND A.CMATEPSEQU = $Material[$i] ";
														$sqlmov .= "   AND A.TMOVMAULAT > '$DataHora[$i]' ";                                        // TimeStamp da NotaFiscal - procura movimentações criadas após o cadastro da nota a ser cancelada
														$sqlmov .= "   AND A.CTIPMVCODI = B.CTIPMVCODI ";
														$sqlmov .= "   AND A.CTIPMVCODI NOT IN (1,5) ";                                             // Saldo Inicial e Inventário
														$sqlmov .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";                          // Apresentar só as movimentações ativas
														$sqlmov .= "   AND ((A.CTIPMVCODI <> 3 AND A.CTIPMVCODI <> 7)"; 														// Trazer todas as movimentações, menos 3 - Entrada por nota fiscal e 7 - Entrada por alteração de nota fiscal
														$sqlmov .= "    OR ((A.CTIPMVCODI = 3 OR A.CTIPMVCODI = 7) ";   														// ou, se o tipo for 1 e 7,
														$sqlmov .= "   AND A.CENTNFCODI <> $NotaFiscal AND A.AENTNFANOE = $AnoNota))";              // não trazendo a própria nota fiscal


                            // echo "$sqlmov";
                            // exit;

                            $resmov  = $db->query($sqlmov);
														if( PEAR::isError($resmov) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmov");
														}else{
																$Rowsmov = $resmov->numRows();
																for($k=0;$k<$Rowsmov;$k++){
																		$Linhamov         = $resmov->fetchRow();
																		# Geral
																		$Almoxarifado   	= $Linhamov[0];
																		$AnoMovimentacao  = $Linhamov[1];
																		$MovimentacaoCod  = $Linhamov[2];
																		$TipoMovimentacao = $Linhamov[3];
																		$QuantidadeChk    = $Linhamov[4];
																		# Movimentação pura
																		$DescMovimentacao = $Linhamov[5];
																		$Data		          = databarra($Linhamov[6]);
																		$NumeroDaMov      = $Linhamov[7];
																		# Nota Fiscal
																		if($Linhamov[8]) { $NotaAno    = $Linhamov[8]; }else{ $NotaAno    = "NULL"; }
																		if($Linhamov[9]) { $NotaCodigo = $Linhamov[9]; }else{ $NotaCodigo = "NULL"; }
																		if($Linhamov[10]){ $NotaNumero = $Linhamov[10];}else{ $NotaNumero = "NULL"; }
																		if($Linhamov[11]){ $NotaSerie  = $Linhamov[11];}else{ $NotaSerie  = "NULL"; }
																		# Requisição
																		if($Linhamov[12]){ $RequisicaoSeq = $Linhamov[12]; }else{ $RequisicaoSeq = "NULL"; }
																		if($Linhamov[13]){ $Requisicao    = $Linhamov[13]; }else{ $Requisicao    = "NULL"; }
																		if($Linhamov[14]){ $AnoRequisicao = $Linhamov[14]; }else{ $AnoRequisicao = "NULL"; }

																		$CodMovArray = array(); // Inicia array que será usado em algumas movimentações

																		# Verifica se houve devolução para cada tipo de movimentação - INÍCIO #

																		# NOTA FISCAL: ENTRADAS : 3 - Nota, 7 - Alteração --> SAÍDA: 8 - Alteração
																		if ( ($TipoMovimentacao == 3) or ($TipoMovimentacao == 7) ) {
																				$sqlnota  = "SELECT A.AMOVMAQTDM ";
																				$sqlnota .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlnota .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL B ON (A.CALMPOCODI = B.CALMPOCODI AND A.AENTNFANOE = B.AENTNFANOE AND A.CENTNFCODI = B.CENTNFCODI AND ( (A.CALMPOCODI <> $Almoxarifado) or (A.AENTNFANOE <> $AnoNota) or (A.CENTNFCODI <> $NotaFiscal) ) ) "; // Para não checar a própria nota fiscal que está sendo cancelada no momento
																				$sqlnota .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlnota .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlnota .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlnota .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlnota .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlnota .= "   AND A.CTIPMVCODI = 8 "; 					                  // Só traz movimentações de saída por alteração de nota fiscal
																				$sqlnota .= "   AND A.AENTNFANOE = $NotaAno ";                      // Chave da nota para trazer o cancelamento da nota correspondente
																				$sqlnota .= "   AND A.CENTNFCODI = $NotaCodigo ";                   // Chave da nota para trazer o cancelamento da nota correspondente
																				$sqlnota .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resnota  = $db->query($sqlnota);
																				if( PEAR::isError($resnota) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnota");
																				}else{
																						$Rowsnota = $resnota->numRows();
																						for ($j=0;$j<$Rowsnota;$j++ ){
																								$Linhanota      = $resnota->fetchRow();
																								$QuantidadeNota = $Linhanota[0];
																								$QuantidadeChk  = $QuantidadeChk - $QuantidadeNota;
																						}
																				}
																				If($QuantidadeChk > 0){
																						if($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a movimentação na Nota Fiscal: ".$NotaNumero."/".$NotaSerie;
																						$ItemNaMens = 1;
																				}
																		}

																		# NOTA FISCAL: SAÍDA: 8 - Alteração --> ENTRADAS: 3 - Nota, 7 - Alteração
																		if($TipoMovimentacao == 8){
																				$sqlnota  = "SELECT A.AMOVMAQTDM ";
																				$sqlnota .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlnota .= "  LEFT OUTER JOIN SFPC.TBENTRADANOTAFISCAL B ON (A.CALMPOCODI = B.CALMPOCODI AND A.AENTNFANOE = B.AENTNFANOE AND A.CENTNFCODI = B.CENTNFCODI AND ( (A.CALMPOCODI <> $Almoxarifado) or (A.AENTNFANOE <> $AnoNota) or (A.CENTNFCODI <> $NotaFiscal) ) ) "; // Para não checar a própria nota fiscal que está sendo cancelada no momento
																				$sqlnota .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlnota .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlnota .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlnota .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlnota .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlnota .= "   AND A.CTIPMVCODI IN (3,7) "; 		                    // Só traz movimentações de saída por alteração de nota fiscal
																				$sqlnota .= "   AND A.AENTNFANOE = $NotaAno ";                      // Chave da nota para trazer o cancelamento da nota correspondente
																				$sqlnota .= "   AND A.CENTNFCODI = $NotaCodigo ";                   // Chave da nota para trazer o cancelamento da nota correspondente
																				$sqlnota .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resnota  = $db->query($sqlnota);
																				if( PEAR::isError($resnota) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnota");
																				}else{
																						$Rowsnota = $resnota->numRows();
																						for ($j=0;$j<$Rowsnota;$j++){
																								$Linhanota      = $resnota->fetchRow();
																								$QuantidadeNota = $Linhanota[0];
																								$QuantidadeChk  = $QuantidadeChk - $QuantidadeNota;
																						}
																				}
																				If($QuantidadeChk > 0){
																						if ($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a movimentação na Nota Fiscal: ".$NotaNumero."/".$NotaSerie;
																						$ItemNaMens = 1;
																				}
																		}

																		# REQUISIÇÃO: SAÍDAS: 4 - Atendimento Requisição, 20 - Acerto Requisição, 22 - Acerto Devolução Interna --> ENTRADAS: 2 - Devolução Interna, 18 - Cancelamento, 19 - Acerto Req, 21 - Acerto Devolução Interna
																		if( ($TipoMovimentacao == 4) or ($TipoMovimentacao == 20) or ($TipoMovimentacao == 22) ) {
																				$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																				$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlrequ .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlrequ .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																				$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlrequ .= "   AND A.CTIPMVCODI IN (4,20,22, 2,18,19,21) ";        // Só traz movimentações de entrada (2,18,19,21) devolvendo saídas para atender requisições (4) e outras de saída (20,22) que podem "incrementar o problema"
																				$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resrequ  = $db->query($sqlrequ);
																				if( PEAR::isError($resrequ) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																				}else{
																						$Rowsrequ = $resrequ->numRows();
																						for($j=0;$j<$Rowsrequ;$j++){
																								$Linharequ      = $resrequ->fetchRow();
																								$QuantidadeRequ = $Linharequ[0];
																								$TipoMovRequ    = $Linharequ[1];
																								if ( ($TipoMovRequ == 4) or ($TipoMovRequ == 20) or ($TipoMovRequ == 22) ) { // Se for também saída, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeRequ;
																								}else{                                                                       // Se for entradas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeRequ;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if ($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a movimentação na Requisição: ".$Requisicao."/".$AnoRequisicao;
																						$ItemNaMens = 1;
																				}
																		}

																		# REQUISIÇÃO: ENTRADAS: 2 - Devolução Interna, 19 - Acerto da Requisição, 21 - Acerto Devolução Interna --> SAÍDAS: 4 - Saída por Requisição, 20 - Acerto Requisição, 22 - Acerto Devolução Interna / ENTRADA: 18 - Cancelamento Requisição
																		if( ($TipoMovimentacao == 2) or ($TipoMovimentacao == 19) or ($TipoMovimentacao == 21) ) {
																				$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																				$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlrequ .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlrequ .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																				$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlrequ .= "   AND A.CTIPMVCODI IN (2,19,21, 4,20,22, 18) ";       // Só traz movimentações de saída devolvendo entradas (22) e outras de entrada (2,18,19,21) que podem incrementar o problema. Se achar uma movimentação do Tipo 18 - Cancelamento Requisição, armazena o saldo na variável de compensação para posterior modificação do estoque e calculo do valor médio
																				$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$sqlrequ .= " ORDER BY A.TMOVMAULAT ";
																				$resrequ  = $db->query($sqlrequ);
																				if( PEAR::isError($resrequ) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																				}else{
																						$Rowsrequ = $resrequ->numRows();
																						for($j=0;$j<$Rowsrequ;$j++){
																								$Linharequ      = $resrequ->fetchRow();
																								$QuantidadeRequ = $Linharequ[0];
																								$TipoMovRequ    = $Linharequ[1];
																								if( ($TipoMovRequ == 2) or ($TipoMovRequ == 19) or ($TipoMovRequ == 21) ) { // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeRequ;
																								}elseif($TipoMovRequ == 18){
																										$LiberaPorCancelamento = 1;
																								}else{																																			// Se forem saídas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeRequ;
																								}
																						}
																				}
																				If( ($QuantidadeChk > 0) and (!$LiberaPorCancelamento) ) {
																						if($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a movimentação na Requisição: ".$Requisicao."/".$AnoRequisicao;
																						$ItemNaMens = 1;
																				}
																				$LiberaPorCancelamento = null;
																		}

																		# REQUISIÇÃO: ENTRADA: 18 - Cancelamento de Requisição --> EXCEÇÃO - Não há uma movimentação que Cancele esta movimentação.
																		#                                                          Neste momento o sistema vai deixar passar, mas armazena a quantidade
																		#                                                          numa variável de compensação para subtrair a quantidade em estoque
																		#                                                          para cálculo do preço médio, no cancelamento da nota, caso a requisição
																		#                                                          que está sendo Cancelada tenha sido feita antes da entrada da nota
																		if($TipoMovimentacao == 18){
																				$sqlrequ  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlrequ .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlrequ .= "  LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL B ON (A.CREQMASEQU = B.CREQMASEQU) ";
																				$sqlrequ .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlrequ .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlrequ .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlrequ .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlrequ .= "   AND A.CREQMASEQU = $RequisicaoSeq ";                // Chave da requisição para trazer o cancelamento da requisição correspondente
																				$sqlrequ .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlrequ .= "   AND A.CTIPMVCODI IN (4,20, 19) ";                   // Só traz movimentações de saída (4,20) devolvendo entrada por cancelamento de requisição (18) e outras entradas que incrementam o problema (19)
																				$sqlrequ .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resrequ  = $db->query($sqlrequ);
																				if( PEAR::isError($resrequ) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																				}else{
																						$Rowsrequ = $resrequ->numRows();
																						if ($Rowsrequ > 0) {
																								for($j=0;$j<$Rowsrequ;$j++){
																										$Linharequ      = $resrequ->fetchRow();
																										$QuantidadeRequ = $Linharequ[0];
																										$TipoMovRequ    = $Linharequ[1];
																										if(!$QuantComp[$i]) $QuantComp[$i] = $QuantidadeChk;
																										if($TipoMovRequ == 19) {                  // Se for também entrada, aumenta a Compensação
																												$QuantComp[$i] = $QuantComp[$i] + $QuantidadeRequ;
																										}else{                   									 // Se forem saídas, diminui Compensação"
																												$QuantComp[$i] = $QuantComp[$i] - $QuantidadeRequ;
																										}
																								}
																						}else{
																								$QuantComp[$i] = $QuantidadeChk;               // Compensação para material vigente do array na íntegra caso não haja nenhuma movimentação que incremente ou decremente o problema
																						}
																				}
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 6 - Empréstimo --> SAÍDA: 13 - Devolução Empréstimo
																		if($TipoMovimentacao == 6){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND ( ( A.CTIPMVCODI = 13 ";                        // Só traz movimentações de saída devolvendo empréstimo
																				$sqlmovi .= "        AND A.CMOVMACOD1 = (SELECT CMOVMACOD1 FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMOVMACODI = $MovimentacaoCod AND CTIPMVCODI = 6) ) ";
																				$sqlmovi .= "    OR (A.CTIPMVCODI = 31) ) ";
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0; $j<$Rowsmovi; $j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																								if( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 13 - SAÍDA por Devolução Empréstimo, já armazenada anteriormente no array
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                              // Se forem saídas, "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																										$CodMovArray[count($CodMovArray)] = $CodMovSec;
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a Movimentação: ".$NumeroDaMov." - ".$DescMovimentacao." realizada em ".$Data;
																						$ItemNaMens = 1;
																				}
																				$CodMovArray = array();
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 13 - Devolução Empréstimo --> ENTRADA: 6 - Empréstimo, 31 - Cancelamento de Movimentação
																		if($TipoMovimentacao == 13){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND (A.CTIPMVCODI IN (13, 6) ";                     // Só traz movimentações de entrada confirmando empréstimo e a de saída que aumenta o problema
																				$sqlmovi .= "        OR ((A.CTIPMVCODI = 31) AND (A.CMOVMACOD1 = $MovimentacaoCod AND A.AMOVMAANO1 = $AnoMovimentacao AND A.CALMPOCOD1 = $Almoxarifado)) ) ";
																				//$sqlmovi .= "   AND (   (A.CTIPMVCODI = 13 AND (A.AMOVMAANO1, A.CALMPOCOD1, A.CMOVMACOD1) = (SELECT B.AMOVMAANOM, B.CALMPOCODI, B.CMOVMACODI FROM SFPC.TBMOVIMENTACAOMATERIAL B WHERE B.AMOVMAANO1 = A.AMOVMAANOM AND B.CALMPOCOD1 = A.CALMPOCODI AND B.CMOVMACODI = A.CMOVMACOD1 AND B.CTIPMVCODI = 12) ) "; // Saída que aumenta o problema
																				//$sqlmovi .= "        OR (A.CTIPMVCODI = 6  AND (A.AMOVMAANO1, A.CALMPOCOD1, A.CMOVMACOD1) = (SELECT B.AMOVMAANOM, B.CALMPOCODI, B.CMOVMACODI FROM SFPC.TBMOVIMENTACAOMATERIAL B WHERE B.AMOVMAANO1 = A.AMOVMAANOM AND B.CALMPOCOD1 = A.CALMPOCODI AND B.CMOVMACODI = A.CMOVMACOD1 AND B.CTIPMVCODI = 12) ) "; // Movimentações de entrada confirmando empréstimo
																				//$sqlmovi .= "        OR (A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod AND A.AMOVMAANO1 = $AnoMovimentacao AND A.CALMPOCOD1 = $Almoxarifado)  ) ";
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if ($TipoMovRequ == 13){                                        // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{																											    // Se forem saídas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if(!$MensMov13){
																								if($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov13 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 12 - Empréstimo --> ENTRADA: 9 - Devolução de Empréstimo, 31 - Cancelamento de Movimentação
																		if ($TipoMovimentacao == 12) {
																				$sqlmovi  = "SELECT A.AMOVMAQTDM ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				#$sqlmovi .= "   AND ( (A.CTIPMVCODI = 9  AND A.CMOVMACODI = (SELECT CMOVMACOD1 FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMOVMACODI = $MovimentacaoCod AND CTIPMVCODI = 13) ) "; // Só traz movimentações de entrada confirmando devolução de empréstimo

                                        //TESTE
                                        $sqlmovi .= "   AND ( (A.CTIPMVCODI = 9  AND A.CMOVMACOD1 = (SELECT CMOVMACODI FROM SFPC.TBMOVIMENTACAOMATERIAL WHERE CMOVMACOD1 = $MovimentacaoCod AND CTIPMVCODI = 13) ) "; // Só traz movimentações de entrada confirmando devolução de empréstimo
																				//FIM TESTE

                                        $sqlmovi .= "    OR   (A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod) ) ";                                                                                            // Só traz movimentações de entrada cancelando devolução de empréstimo
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas

                                        //echo $sqlmovi;
                                        //exit;

                                        $resmovi  = $db->query($sqlmovi);

																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$QuantidadeChk  = $QuantidadeChk - $QuantidadeMovi;
																						}
																				}
																				If($QuantidadeChk > 0){
																						if($ItemNaMens) $MensagemMov .= ", ";
																						$MensagemMov .= "a Movimentação: ".$NumeroDaMov." - ".$DescMovimentacao." realizada em ".$Data; // Mensagem específica
																						$ItemNaMens = 1;
																				}
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 9 - Devolução de Empréstimo --> SAÍDA: 12 - Empréstimo
																		if($TipoMovimentacao == 9){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (9,31, 12) ";                   // Só traz movimentações de saída confirmando empréstimo, e outras entradas que aumentam o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlrequ");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																								if($TipoMovRequ == 9){                                                   // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 12 - SAÍDA por Empréstimo, já armazenada anteriormente no array
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                                   // Se forem saídas (12), "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																										$CodMovArray[count($CodMovArray)] = $CodMovSec;
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				if($QuantidadeChk > 0){
																						if(!$MensMov9){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao;          // Mensagem genérica
																								$MensMov9 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				$CodMovArray = array();
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 10 - Doação Externa --> SAÍDA: 27 - Cancelamento para NF
																		if($TipoMovimentacao == 10){
																				/*
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (10, 26, 27) ";                            // Só traz movimentações de saídas devolvendo doações
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				*/
																				$sqlmovi  = "SELECT AMOVMAQTDM, CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																				$sqlmovi .= " WHERE CALMPOCOD1 = $Almoxarifado ";
																				$sqlmovi .= "   AND AMOVMAANO1 = $AnoMovimentacao ";
																				$sqlmovi .= "   AND CMOVMACOD1 = $MovimentacaoCod ";
																				$sqlmovi .= "   AND TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND CTIPMVCODI IN ( 27 )";
																				$resmovi = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						/*
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 10 or $TipoMovRequ == 27 ){                                     // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                      // Se for saída, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																						*/
																						if ($Rowsmovi==0){
																								if(!$MensMov10){
																										if ($ItemNaMens) $MensagemMov .= ", ";
																										$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																										$MensMov10 = 1;
																								}
																								$ItemNaMens = 1;
																						}
																				}
																				/*
																				If($QuantidadeChk > 0){
																						if(!$MensMov10){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov10 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				*/
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 11 - TROCA --> SAÍDA: 15 - TROCA
																		if( $TipoMovimentacao == 11 ){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (11,31, 15) ";                  // Só traz movimentações de saída (15) devolvendo entradas por troca (11), e outras de entrada por troca que aumentam o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$sqlmovi .= " ORDER BY A.TMOVMAULAT ";
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																								if($TipoMovRequ == 11){                                                  // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 15 - SAÍDA por Troca, já armazenada anteriormente no array
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{																											             // Se forem saídas (15), "diminui o problema", e armazena movimentação em array para verificar se depois não é cancelada
																										$CodMovArray[count($CodMovArray)] = $CodMovSec;
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				if($QuantidadeChk > 0) {
																						if(!$MensMov11){
																								if($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov11 = 1;
																						}
																					$ItemNaMens = 1;
																				}
																				$CodMovArray[] = null;
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 15 - TROCA --> ENTRADA: 11 - TROCA, 31 - CANCELAMENTO DE MOVIMENTAÇÃO
																		if( ($TipoMovimentacao == 15) ){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND ((A.CTIPMVCODI IN (15, 11)) ";                  // Só traz movimentações de entrada (11) devolvendo saídas por troca (15), e outras saídas por troca que aumentam o problema
																				$sqlmovi .= "    OR ((A.CTIPMVCODI = 31) AND (A.CMOVMACOD1 = $MovimentacaoCod))) "; // Ou movimentações de cancelamento, específicas da 15 - saída por troca
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 15) {                                        // Se for também saída, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                          // Se for entradas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if(!$MensMov15){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov15 = 1;
																						}
																					$ItemNaMens = 1;
																				}
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 14 - Obsoletismo, 16 - Avaria, 17 - Vencimento, 23 - Furto, 24 - Doação Externa --> ENTRADA: 26 - Cancelamento para NF
																		if( ($TipoMovimentacao == 14) or ($TipoMovimentacao == 16) or ($TipoMovimentacao == 17) or ($TipoMovimentacao == 23) or ($TipoMovimentacao == 24)or ($TipoMovimentacao == 37) ){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI = $MovimentacaoCod ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND ( ";
																				$sqlmovi .= "       SELECT COUNT(*) ";
																				$sqlmovi .= "         FROM SFPC.TBMOVIMENTACAOMATERIAL B ";
																				$sqlmovi .= "        WHERE B.CALMPOCOD1 = A.CALMPOCODI ";
																				$sqlmovi .= "          AND B.AMOVMAANO1 = A.AMOVMAANOM ";
																				$sqlmovi .= "          AND B.CMOVMACOD1 = A.CMOVMACODI ";
																				$sqlmovi .= "          AND B.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "       ) > 0 ";
																				/*
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (14,16,17,23,24,26) ";         // Só traz movimentações de entrada confirmando movimentações de saída diversas
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				*/
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						/*
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								echo $QuantidadeChk;
																								if($TipoMovRequ == 14 or $TipoMovRequ == 16 or $TipoMovRequ == 17 or $TipoMovRequ == 23 or $TipoMovRequ == 24){ // Se for também saída, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                                                                          // Se for entrada, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																								echo $QuantidadeChk;
																						}
																						*/
																						if ($Rowsmovi == 0){
																								$variavel = "MensMov".$TipoMovimentacao;
																								if(!$$variavel){ // Variável variável. Dependendo de $TipoMovimentacao, pode ser a variável $MensMov14, $MensMov16, $MensMov17 e etc
																										if ($ItemNaMens) $MensagemMov .= ", ";
																										$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																										$$variavel = 1;
																								}
																								$ItemNaMens = 1;
																						}
																				}
																				/*
																				If($QuantidadeChk > 0){
																						$variavel = "MensMov".$TipoMovimentacao;
																						if(!$$variavel){ // Variável variável. Dependendo de $TipoMovimentacao, pode ser a variável $MensMov14, $MensMov16, $MensMov17 e etc
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$$variavel = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				*/
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 26 - Cancelamento para NF --> SAÍDA: 14 - Obsoletismo, 16 - Avaria, 17 - Vencimento, 23 - Furto, 24 - Doação Externa, 27 - Cancelamento de movimentação
																		if($TipoMovimentacao == 26){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI = $MovimentacaoCod ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND ( ";
																				$sqlmovi .= "       SELECT COUNT(*) ";
																				$sqlmovi .= "         FROM SFPC.TBMOVIMENTACAOMATERIAL B ";
																				$sqlmovi .= "        WHERE B.CALMPOCODI = A.CALMPOCOD1 ";
																				$sqlmovi .= "          AND B.AMOVMAANOM = A.AMOVMAANO1 ";
																				$sqlmovi .= "          AND B.CMOVMACODI = A.CMOVMACOD1 ";
																				$sqlmovi .= "          AND B.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "       ) > 0 ";
																				/*
																				$sqlmovi  = "SELECT AMOVMAQTDM, CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																				$sqlmovi .= " WHERE CALMPOCOD1 = $Almoxarifado ";
																				$sqlmovi .= "   AND AMOVMAANO1 = $AnoMovimentacao ";
																				$sqlmovi .= "   AND CMOVMACOD1 = $MovimentacaoCod ";
																				$sqlmovi .= "   AND TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND CTIPMVCODI IN ( 27 ) ";
																				*/
																				/*
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (26, 14,16,17,23,24,27) ";      // Só traz movimentações de saída (14,16,17,23,24,27) confirmando a movimentação de entrada (26) e movimentações de entrada (26) que pioram o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				*/
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						/*
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 26){                                     // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                      // Se for saída, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																						*/
																						if ($Rowsmovi == 0){
																								if(!$MensMov26){
																										if ($ItemNaMens) $MensagemMov .= ", ";
																										$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																										$MensMov26 = 1;
																								}
																								$ItemNaMens = 1;
																						}
																				}
																				/*
																				If($QuantidadeChk > 0){
																						if(!$MensMov26){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov26 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				*/
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 27 - Cancelamento para NF --> ENTRADA: 10 - Doação Externa, 26 - Cancelamento de Movimentação
																		if($TipoMovimentacao == 27){
																				/*
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (27, 10,26) ";                            // Só traz movimentações de entrada confirmando Doação Externa, e outras saídas que aumentam o problema
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (26, 10,14,16,17,23,24,27) ";                            // Só traz movimentações de entrada confirmando Doação Externa, e outras saídas que aumentam o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				*/
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI = $MovimentacaoCod ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND ( ";
																				$sqlmovi .= "       SELECT COUNT(*) ";
																				$sqlmovi .= "         FROM SFPC.TBMOVIMENTACAOMATERIAL B ";
																				$sqlmovi .= "        WHERE B.CALMPOCODI = A.CALMPOCOD1 ";
																				$sqlmovi .= "          AND B.AMOVMAANOM = A.AMOVMAANO1 ";
																				$sqlmovi .= "          AND B.CMOVMACODI = A.CMOVMACOD1 ";
																				$sqlmovi .= "          AND B.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "       ) > 0 ";
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						/*
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 27 or $TipoMovRequ == 14 or $TipoMovRequ == 16 or $TipoMovRequ == 17 or $TipoMovRequ == 23 or $TipoMovRequ == 24){                                     // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                      // Se for saída, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																						*/
																						if ($Rowsmovi == 0){
																								if(!$MensMov27){
																										if ($ItemNaMens) $MensagemMov .= ", ";
																										$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																										$MensMov27 = 1;
																								}
																								$ItemNaMens = 1;
																						}
																				}
																				/*
																				If($QuantidadeChk > 0){
																						if(!$MensMov27){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov27 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				*/
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 29 - Doação Entre Almoxarifados --> SAÍDA: 30 - Doação Entre Almoxarifados
																		if($TipoMovimentacao == 29){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI, A.CALMPOCOD1, A.AMOVMAANO1, A.CMOVMACOD1 ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (29,31, 30) ";                  // Só traz movimentações de Saída e outras Entradas que aumentam o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								$CodMovSec      = $Linhamovi[2].$Linhamovi[3].$Linhamovi[4];
																								if($TipoMovRequ == 29) {                                                 // Se for também entrada, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}elseif( ($TipoMovRequ == 31) and (in_array($CodMovSec,$CodMovArray)) ){ // Se for também entrada, "aumenta o problema", mas só se for cancelamento de 30 - SAÍDA por Doação Entre Almoxarifados, já armazenada anteriormente no array
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                                   // Se for 30 - SAÍDA por Doação Entre Almoxarifados diminui o problema, e armazena movimentação em array para verificar se depois não é cancelada
																										$CodMovArray[count($CodMovArray)] = $CodMovSec;
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if(!$MensMov29){
																								if($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao;          // Mensagem genérica
																								$MensMov29 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																				$CodMovArray = array();
																		}

																		# MOVIMENTAÇÃO: SAÍDA: 30 - Doação Entre Almoxarifados --> ENTRADA: 29 - Doação Entre Almoxarifados, 31 - Cancelamento de Movimentação
																		if($TipoMovimentacao == 30){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (30, 29) OR (A.CTIPMVCODI = 31 AND A.CMOVMACOD1 = $MovimentacaoCod) "; // Só traz movimentações de Entrada, e outras Saídas que aumentam o problema. A de entrada por cancelamento (31) tem que ser específica da Saída por Doação gerada
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 30) {                                     // Se for também saída, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                       // Se for entradas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if(!$MensMov30){
																								if($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov30 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																		}

																		# MOVIMENTAÇÃO: ENTRADA: 31 - Cancelamento de Movimentação --> Saídas: 12 - Empréstimo entre órgãos, 13 - Devolução de Empréstimo, 15 - Troca, 30 - Doação entre almoxarifados
																		if($TipoMovimentacao == 31){
																				$sqlmovi  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI ";
																				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
																				$sqlmovi .= " WHERE A.CALMPOCODI = $Almoxarifado ";
																				$sqlmovi .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
																				$sqlmovi .= "   AND A.CMOVMACODI <> $MovimentacaoCod ";             // Para não trazer a própria movimentação
																				$sqlmovi .= "   AND A.CMATEPSEQU = $Material[$i] ";
																				$sqlmovi .= "   AND A.TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND A.CTIPMVCODI IN (31, 12,13,15,30) ";            // Só traz movimentações de Saída, e outras Entrada que aumentam o problema
																				$sqlmovi .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																				$resmovi  = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						for($j=0;$j<$Rowsmovi;$j++){
																								$Linhamovi      = $resmovi->fetchRow();
																								$QuantidadeMovi = $Linhamovi[0];
																								$TipoMovRequ    = $Linhamovi[1];
																								if($TipoMovRequ == 31) {                                     // Se for também saída, "aumenta o problema"
																										$QuantidadeChk = $QuantidadeChk + $QuantidadeMovi;
																								}else{                                                       // Se for entradas, "diminui o problema"
																										$QuantidadeChk = $QuantidadeChk - $QuantidadeMovi;
																								}
																						}
																				}
																				If($QuantidadeChk > 0){
																						if(!$MensMov31){
																								if ($ItemNaMens) $MensagemMov .= ", ";
																								$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																								$MensMov31 = 1;
																						}
																						$ItemNaMens = 1;
																				}
																		}


																		# MOVIMENTAÇÃO: ENTRADA: 32 - Entrada de Material de Iluminação Recuperado --> SAÍDA: 27 - Cancelamento para NF
																		if($TipoMovimentacao == 32){
																				$sqlmovi  = "SELECT AMOVMAQTDM, CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																				$sqlmovi .= " WHERE CALMPOCOD1 = $Almoxarifado ";
																				$sqlmovi .= "   AND AMOVMAANO1 = $AnoMovimentacao ";
																				$sqlmovi .= "   AND CMOVMACOD1 = $MovimentacaoCod ";
																				$sqlmovi .= "   AND TMOVMAULAT >= '$DataHora[$i]' ";              // TimeStamp do item da Nota Fiscal corrente no loop
																				$sqlmovi .= "   AND CTIPMVCODI IN ( 27 )";
																				$resmovi = $db->query($sqlmovi);
																				if( PEAR::isError($resmovi) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
																				}else{
																						$Rowsmovi = $resmovi->numRows();
																						if ($Rowsmovi==0){
																								if(!$MensMov32){
																										if ($ItemNaMens) $MensagemMov .= ", ";
																										$MensagemMov .= "a(s) Movimentação(ões) de ".$DescMovimentacao; // Mensagem genérica
																										$MensMov32 = 1;
																								}
																								$ItemNaMens = 1;
																						}
																				}
																		}

																		# Verifica se houve devolução para cada tipo de movimentação - FIM #

																}
														}
														If( ($MaterialTestado != $Material[$i]) and ($ItemNaMens) ){
																If ($MensagemMovimentacao) $MensagemMovimentacao .= ". ";
																$DescricaoMat = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																$Virgula = 2;
																if(strrpos($MensagemMov, ",") != 0 ) { $MensagemMov = substr_replace($MensagemMov, " e ", strrpos($MensagemMov, ",")) . substr($MensagemMov,(strrpos($MensagemMov, ",")+1)); }
																$DataIni = DataBarra($DataHora[$i]);
																$DataFim = DataBarra(date("Y-m-d"));
																//$Url = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Material=$Material[$i]&DataIni=$DataIni&DataFim=$DataFim&Ulat=".urlencode($DataHora[$i])."&Procedimento=M&".mktime();
																$Url = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=$Almoxarifado&Localizacao=$Localizacao&Material=$Material[$i]&NotaFiscal=".$NotaFiscal."&AnoNota=".$AnoNota."&Procedimento=M&".mktime();
																if(!$MensagemMovimentacao){
																		$MensagemMovimentacao = "Esta Nota Fiscal não pode ser cancelada, pois o item $DescricaoMat[1] está presente em movimentações posteriores a sua entrada no sistema. Deverá(ão) ser desfeita(s) $MensagemMov.";
																		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		$MensagemMovimentacao .= " Utilize o relatório de <a href=\"$Url\">Auxílio para Cancelamento de Nota Fiscal</a> para identificar estas movimentações";
																}else{
																		$MensagemMovimentacao .= "<BR><BR>Esta Nota Fiscal não pode ser cancelada, pois o item $DescricaoMat[1] está presente em movimentações posteriores a sua entrada no sistema. Deverá(ão) ser desfeita(s) $MensagemMov.";
																		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		$MensagemMovimentacao .= " Utilize o relatório de <a href=\"$Url\">Auxílio para Cancelamento de Nota Fiscal</a> para identificar estas movimentações";
																}
																$MaterialTestado != $Material[$i];
																$MensagemMov    = null;
																$ItemNaMens     = null;
																# Seta Null nas variáveis de repetição de mensagens genéricas, para que possam aparecer novamente para novos materiais
																$MensMov9       = null;
																$MensMov10      = null;
																$MensMov11      = null;
																$MensMov13      = null;
																$MensMov14      = null;
																$MensMov15      = null;
																$MensMov16      = null;
																$MensMov17      = null;
																$MensMov23      = null;
																$MensMov24      = null;
																$MensMov26      = null;
																$MensMov27      = null;
																$MensMov29      = null;
																$MensMov30      = null;
																$MensMov31      = null;
																$MensMov32      = null;
																$MensMov37      = null;
																$ItemNaMensagem = 1;
														}
												}
										}
										if($Existe == "S" and is_null($ItemNaMensagem) ){
												$Mens      = 1;
												$Tipo      = 2;
												$Virgula   = 2;
												$DescMat   = explode($SimboloConcatenacaoDesc,$DescMaterial[$Posicao]);
												$Mensagem = "O Cancelamento da Nota Fiscal não poderá ser efetuado, pois o item $DescMat[1] irá ficar com quantidade negativa";
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
										}elseif ($ItemNaMensagem){
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem = $MensagemMovimentacao;
										}else{
												# Atualiza a flag de cancelmento da nota fiscal #
												$sqlnf  = "UPDATE SFPC.TBENTRADANOTAFISCAL ";
												$sqlnf .= "   SET FENTNFCANC = 'S'";
												$sqlnf .= " WHERE CALMPOCODI = $Almoxarifado ";
												$sqlnf .= "   AND AENTNFANOE = $AnoNota  ";
												$sqlnf .= "   AND CENTNFCODI = $NotaFiscal ";
												$resnf  = $db->query($sqlnf);
												if( PEAR::isError($resnf) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlnf");
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														exit(0);
												}else{
														for($i=0; $i< count($ItemNotaFiscal); $i++){
																# Resgata o ultimo valor do item na ultima nota fiscal(nf) #
																$sqlultvalnot  = "SELECT DISTINCT(VITENFUNIT) FROM SFPC.TBITEMNOTAFISCAL ";
																$sqlultvalnot .= " WHERE CMATEPSEQU = $Material[$i] AND CALMPOCODI = $Almoxarifado ";
																$sqlultvalnot .= "   AND AENTNFANOE = $AnoNota ";
																$sqlultvalnot .= "   AND CENTNFCODI = ( SELECT MAX(A.CENTNFCODI) FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B ";
																$sqlultvalnot .= "                       WHERE A.CENTNFCODI <> $NotaFiscal AND A.CMATEPSEQU = $Material[$i] ";
																$sqlultvalnot .= "                         AND A.CALMPOCODI = $Almoxarifado AND A.AENTNFANOE = $AnoNota ";
																$sqlultvalnot .= "                         AND A.CALMPOCODI = B.CALMPOCODI ";
																$sqlultvalnot .= "                         AND A.AENTNFANOE = B.AENTNFANOE ";
																$sqlultvalnot .= "                         AND A.CENTNFCODI = B.CENTNFCODI ";
																$sqlultvalnot .= "                         AND B.FENTNFCANC <> 'S' )";
																$resultvalnot  = $db->query($sqlultvalnot);
																if( PEAR::isError($resultvalnot) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlultvalnot");
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		exit(0);
																}else{
																		$Linhaultvalnot   = $resultvalnot->fetchRow();
																		$ValorUnitarioUlt = $Linhaultvalnot[0];
																		if( $ValorUnitarioUlt == "" ){
																				$ValorUnitarioUlt = 0;
																		}
																}

																# Resgata os dados em armazenamentomaterial do material corrente #
																$sqlarmat  = "SELECT AARMATQTDE,VARMATUMED, AARMATESTR, AARMATVIRT FROM SFPC.TBARMAZENAMENTOMATERIAL ";
																$sqlarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																$resarmat  = $db->query($sqlarmat);
																if( PEAR::isError($resarmat) ){
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlarmat");
																		exit(0);
																}else{
                                  $Linhaarmat 			= $resarmat->fetchRow();
                                  $QuantidadeEst    = str_replace(",",".",$Linhaarmat[0]);
                                  if ($QuantComp[$i]) { // Exceção - Se houver compensação gerada pela movimentação de ENTRADA POR CANCELAMENTO DE REQUISIÇÃO, subtrai da quantidade armazenada, para efetuar cálculo de valor médio
                                      $QuantidadeEst = $QuantidadeEst - $QuantComp[$i];
                                  }
                                  $ValorUnitarioEst = str_replace(",",".",$Linhaarmat[1]);
                                  $decQtdEstoqueReal   = str_replace(",",".",$Linhaarmat[2]);
                                  $decQtdEstoqueVirtual   = str_replace(",",".",$Linhaarmat[3]);
                                  $ValorTotalEst    = ($QuantidadeEst * $ValorUnitarioEst);

                                  if ($decQtdEstoqueReal == null || $decQtdEstoqueReal == '') {
                                    $decQtdEstoqueReal = 0;
                                  }

                                  if ($decQtdEstoqueVirtual == null || $decQtdEstoqueVirtual == '') {
                                    $decQtdEstoqueVirtual = 0;
                                  }
																}

																# Calcula o valor medio a partir do totalizador do estoque atual e o totalizador do item da nota #
																$VerificaQuantidadeMedio = ($QuantidadeEst - $Quantidade[$i]);
																if ($VerificaQuantidadeMedio == 0) { // No caso do estoque depender desta nota para não ficar zerado, não dá para calcular o valor médio, então este é zero
																		$sql  = "select distinct vmovmaumed from sfpc.tbmovimentacaomaterial where calmpocodi = $Almoxarifado ";
																		$sql .= "and cmatepsequ = $Material[$i] ";
																		$sql .= "and (vmovmaumed is not null and vmovmaumed > 0) ";
																		$sql .= "and tmovmaulat = (select max(tmovmaulat) from sfpc.tbmovimentacaomaterial where calmpocodi = $Almoxarifado and cmatepsequ = $Material[$i]) ";
																		$res = $db->query($sql);

																		if( PEAR::isError($res) ){
																			$db->query("ROLLBACK");
																			$db->query("END TRANSACTION");
																			$db->disconnect();
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			exit(0);
                                    }else{
                                      $Linha = $res->fetchRow();
                                      if($res->numRows()<1){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				EmailErroSistema("Falha no Cancelamento de Nota Fiscal", "Valor médio anterior não pôde ser calculado. Verifique se o valor médio da última movimentação (para o material e o almoxarifado) é negativo. Caso 'sim', o valor médio está incorreto e deve ser recalculado manualmente. \n\n calmpocodi=".$Almoxarifado.",\n cmatepsequ=".$Material[$i].",\n centnfcodi=".$NotaFiscal.",\n aentnfanoe=".$AnoNota);
                                      	exit(0);
                                      }
                                      $decValorMedio = $Linha[0];
																		}
																}else{
																	$ValorMedio    = ( $ValorTotalEst - $ValorTotal[$i] ) / ( $QuantidadeEst - $Quantidade[$i] );
																	$decValorMedio = str_replace(",",".",$ValorMedio);
																}

																# Se não existir nota p/ aquele item, o ultimo valor assume o valor medio #
																if ($ValorUnitarioUlt == 0){
																		$ValorUnitarioUlt = $decValorMedio;
																}

																# Atualiza o valor unitário medio e o ultimo valor de compra de cada item #
																$sqlupdarmat  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
																$sqlupdarmat .= "   SET AARMATQTDE = ($QuantidadeEst - $Quantidade[$i]), ";

                                if($EstoqueVirtual == 'S'){
                                  $sqlupdarmat .= "       AARMATVIRT = ($decQtdEstoqueVirtual - $Quantidade[$i]), ";
                                } else {
                                  $sqlupdarmat .= "       AARMATESTR = ($decQtdEstoqueReal - $Quantidade[$i]), ";
                                }

																$sqlupdarmat .= "       VARMATUMED = $decValorMedio, ";
																$sqlupdarmat .= "       VARMATULTC = $ValorUnitarioUlt, CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																$sqlupdarmat .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TARMATULAT = '".date("Y-m-d H:i:s")."'";
																$sqlupdarmat .= " WHERE CMATEPSEQU = $Material[$i] AND CLOCMACODI = $Localizacao ";
																$resupdarmat  = $db->query($sqlupdarmat);
																if( PEAR::isError($resupdarmat) ){
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlupdarmat");
																		exit(0);
																}else{
																		# Pega o máximo valor do movimento de material #
																		$sqlmaxmov  = "SELECT MAX(CMOVMACODI) AS CODIGO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
																		$sqlmaxmov .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoExercicio ";
																		//$sqlmaxmov .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																		$resmaxmov  = $db->query($sqlmaxmov);
																		if( PEAR::isError($resmaxmov) ){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmaxmov");
																				exit(0);
																		}else{
																				$Linhamaxmov = $resmaxmov->fetchRow();
																				$Movimento   = $Linhamaxmov[0] + 1;
																		}

																		# Pega o Máximo valor do Movimento do Material do Tipo 8 - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																		$sqltipo  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL";
																		$sqltipo .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = ".date("Y")." ";
																		$sqltipo .= "   AND CTIPMVCODI = 8 ";
																		//$sqltipo .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
																		$restipo  = $db->query($sqltipo);
																		if( PEAR::isError($restipo) ){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltipo");
																				exit(0);
																		}else{
																				$LinhaTipo     = $restipo->fetchRow();
																				$TipoMovimento = $LinhaTipo[0] + 1;
																		}

																		# Insere na tabela de Movimentação de Material do Tipo 8 - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
																		$sqlmovmat  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL ( ";
																		$sqlmovmat .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
																		$sqlmovmat .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
																		$sqlmovmat .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
																		$sqlmovmat .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, AENTNFANOE, ";
																		$sqlmovmat .= "CENTNFCODI ";
																		$sqlmovmat .= ") VALUES ( ";
																		$sqlmovmat .= "$Almoxarifado, $AnoExercicio, $Movimento, '".date('Y-m-d')."', ";
																		$sqlmovmat .= "8, NULL, $Material[$i], $Quantidade[$i], ";
																		$sqlmovmat .= "$ValorUnitario[$i], $decValorMedio, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d H:i:s")."', ";
																		$sqlmovmat .= "$TipoMovimento, NULL, NULL, $AnoNota, $NotaFiscal )";
																		$resmovmat  = $db->query($sqlmovmat);
																		if( PEAR::isError($resmovmat) ){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovmat");
																				exit(0);
																		}
																}
														}
														$db->query("COMMIT");
														//$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();

														# Limpando os dados da nota #
														$TipoUsuario	     = "";
														$OrgaoUsuario	     = "";
														$CentroCusto	     = "";
														$NumeroNota        = "";
														$SerieNota         = "";
														$DataEmissao       = "";
														$CnpjCpf           = "";
														$RazaoSocial       = "";
														$DataEntrada       = "";
														$ValorNota         = "";
														$Localizacao       = "";
                            $EstoqueVirtual    = "";

														# Limpando variáveis de empenho #
														$CheckEmp       = "";
														unset($Empenhos);
														unset($_SESSION['Empenho']);

														# Limpando os dados do item da nota #
														$Material          = "";
														$DescMaterial      = "";
														$Unidade           = "";
														$Quantidade        = "";
														$ValorUnitario     = "";
														$ValorTotal        = "";
														$DataHora          = "";
														$InicioPrograma    = "";
														unset($ItemNotaFiscal);
														unset($_SESSION['item']);

														# Valores dos calculos #
														$ValInicial			   = 0;
														$QtdInicial 		   = 0;
														$SomaValorMedio    = 0;
														$SomaQtdMedio      = 0;
														$ValorMedio 		   = 0;
														$ValUnitarioItem   = 0;
														$QuantidadeItem    = 0;

														# Redireciona para a tela de Seleção #
														$Mensagem = urlencode("Nota Fiscal Cancelada com Sucesso");
														$Url = "CadNotaFiscalMaterialCancelarSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("Location: ".$Url);
														exit;
												}
										}
								}
						}
				}else{
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "O Usuário do grupo INTERNET não pode fazer cancelamento de Nota Fiscal";
				}
		}
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
function enviar(valor){
	document.CadNotaFiscalCancelar.Botao.value = valor;
	document.CadNotaFiscalCancelar.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadNotaFiscalMaterialCancelar.php" method="post" name="CadNotaFiscalCancelar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Nota Fiscal > Cancelar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									CANCELAR - NOTA FISCAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para o cancelamento da nota fiscal clique no botão "Cancelar Nota Fiscal", e para retornar a tela anterior clique no botão "Voltar".<br>
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]<br>";
														echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if($Almoxarifado != "" ){
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
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
																				}if($Linha[1] == "A"){
																						$Equipamento = "ARMÁRIO";
																				}if($Linha[1] == "P"){
																						$Equipamento = "PALETE";
																				}
																				echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																				$Localizacao = $Linha[0];
																				echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		}else{
																				$EquipamentoAntes = "";
																				$DescAreaAntes    = "";
																				echo "<select name=\"Localizacao\" class=\"textonormal\">\n";
																				echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																				for( $i=0;$i< $Rows; $i++ ){
																						$Linha = $res->fetchRow();
																						$CodEquipamento = $Linha[2];
																						if($Linha[1] == "E" ){
																								$Equipamento = "ESTANTE";
																						}if($Linha[1] == "A" ){
																								$Equipamento = "ARMÁRIO";
																						}if($Linha[1] == "P" ){
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
																						if($CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
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
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Nota</td>
											<td class="textonormal"><?php echo $NumeroNota; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Série da Nota</td>
											<td class="textonormal"><?php echo $SerieNota; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Emissão</td>
											<td class="textonormal"><?php echo $DataEmissao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">
												<?php if( $CNPJ_CPF == 1 ){ echo "CNPJ"; }else{ echo "CPF"; }?> do Fornecedor
											</td>
											<td class="textonormal">
												<?php if( $CNPJ_CPF == 1 ){ echo FormataCNPJ($CnpjCpf); }else{ echo FormataCPF($CnpjCpf); }?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Razão Social</td>
											<td class="textonormal"><?php echo $RazaoSocial; ?></td>
										</tr>
                    <tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
											<td class="textonormal">
											<?php
                        if($EstoqueVirtual == 'S'){
                          echo "Virtual";
                        } else {
                          echo "Normal";
                        }
											?>
                      </td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Entrada</td>
											<td class="textonormal"><?php echo $DataEntrada; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Valor Total da Nota</td>
											<td class="textonormal">
												<?php
												$ValorNota = 0;
												if( count($ItemNotaFiscal) != 0 ){ sort($ItemNotaFiscal); }
												for( $i=0; $i< count($ItemNotaFiscal); $i++ ){
														$Dados         = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
														$DescMaterial[$i]  = $Dados[0];
														$Material[$i]  		 = $Dados[1];
														$Unidade[$i]  		 = $Dados[2];
														$Quantidade[$i]    = str_replace(",",".",$Dados[3]);
														$ValorUnitario[$i] = str_replace(",",".",$Dados[4]);
														$DataHora[$i]      = $Dados[5];
														$decQuantidade     = str_replace(",",".",$Quantidade[$i]);
														$decValorUnitario  = str_replace(",",".",$ValorUnitario[$i]);
														$decValorTotal     = str_replace(",",".",($decQuantidade * $decValorUnitario));
														$ValorTotal[$i]    = str_replace(",",".",$decValorTotal);
														$ValorNota         = str_replace(",",".",($ValorNota + $ValorTotal[$i]));
												}
												echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorNota)));
												?>
												<input type="hidden" name="ValorNota" value="<?php if ($ValorNota == ""){ echo 0; }else{ echo converte_valor_estoques(sprintf('%01.4f',str_replace(",",".",$ValorNota))); } ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
															ITENS DA NOTA FISCAL
														</td>
													</tr>
													<?php
													if( count($ItemNotaFiscal) != 0 ){ sort($ItemNotaFiscal); }
													for( $i=0;$i< count($ItemNotaFiscal);$i++ ){
															$Dados             = explode($SimboloConcatenacaoArray,$ItemNotaFiscal[$i]);
															$DescMaterial[$i]  = $Dados[0];
															$Material[$i]      = $Dados[1];
															$Unidade[$i]       = $Dados[2];
															$Quantidade[$i]    = $Dados[3];
															$ValorUnitario[$i] = $Dados[4];
															$DataHora[$i]      = $Dados[5];

															# Variaveis para calculo de valores #
															$decQuantidade     = str_replace(",",".",$Quantidade[$i]);
															$decValorUnitario  = str_replace(",",".",$ValorUnitario[$i]);
															$decValorTotal     = str_replace(",",".",($decQuantidade * $decValorUnitario));
															$ValorTotal[$i]    = str_replace(",",".",$decValorTotal);
															if($i == 0){
																	echo "		<tr>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">QUANTIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR UNITÁRIO</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">VALOR TOTAL</td>\n";
																	echo "		</tr>\n";
															}
													?>
													<tr>
														<td class="textonormal" width="60%">
															<?php
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadNotaFiscalCancelar&Material=$Material[$i]";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?php $Url;?>',700,370);">
																<font color="#000000">
																	<?php
																	$Descricao = explode($SimboloConcatenacaoDesc,$DescMaterial[$i]);
																	echo $Descricao[1];
																	?>
																</font>
															</a>
															<input type="hidden" name="DataHora[<?php echo $i; ?>]" value="<?php echo $DataHora[$i]; ?>">
															<input type="hidden" name="ItemNotaFiscal[<?php echo $i; ?>]" value="<?php echo $ItemNotaFiscal[$i]; ?>">
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
															<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
														</td>
														<td class="textonormal" width="10%" align="center">
															<?php echo $Unidade[$i];?>
															<input type="hidden" name="Unidade[<?php echo $i; ?>]" value="<?php echo $Unidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $Quantidade[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$Quantidade[$i]))); } ?>
															<input type="hidden" name="Quantidade[<?php echo $i; ?>]" value="<?php echo $Quantidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $ValorUnitario[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnitario[$i]))); } ?>
															<input type="hidden" name="ValorUnitario[<?php echo $i; ?>]" value="<?php echo $ValorUnitario[$i]; ?>">
														</td>
														<td class="textonormal" align="right" width="10%">
															<?php if( $ValorTotal[$i] == "" ){ echo 0; }else{ echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorTotal[$i]))); } ?>
															<input type="hidden" name="ValorTotal[<?php echo $i; ?>]" value="<?php echo $ValorTotal[$i]; ?>">
														</td>
													</tr>
													<?php } ?>
												</table>
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="5">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
															EMPENHOS
														</td>
													</tr>
													<?php
													# Exibe os empenhos #
													for($i=0; $i< count($Empenhos); $i++){
															# Imprime o cabeçalho se for a primeira execução #
															if($i == 0){
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"25%\">Ano<input type=\"hidden\" name=\"Empenhos[$i]\" value=\"$Empenhos[$i]\"></td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Órgão</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"20%\">Unidade</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"30%\">Sequencial</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"15%\">Parcela</td>\n";
																	echo "		</tr>\n";
															}
															# separa Ano, Órgão, Unidade, Sequencial e Parcela #
															$Emp = explode(".",$Empenhos[$i]);
															$AnoEmp        = $Emp[0];
															$OrgaoEmp      = $Emp[1];
															$UnidadeEmp    = $Emp[2];
															$SequencialEmp = $Emp[3];
															$ParcelaEmp    = $Emp[4];
															?>
															<tr>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $AnoEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $OrgaoEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $UnidadeEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php echo $SequencialEmp;?>
																</td>
																<td class="textonormal" align="center" width="10%">
																	<?php if($ParcelaEmp != 0){ echo $ParcelaEmp; }else{ echo "&nbsp;"; } ?>
																</td>
															</tr>
													<?php } ?>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Totalizou" value="">
									<input type="hidden" name="ValNota" value="<?php echo $ValNota; ?>">
									<input type="hidden" name="AnoNota" value="<?php echo $AnoNota; ?>">
									<input type="hidden" name="NotaFiscal" value="<?php echo $NotaFiscal; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
									<input type="hidden" name="CnpjCpf" value="<?php echo $CnpjCpf; ?>">
									<input type="hidden" name="NumeroNota" value="<?php echo $NumeroNota; ?>">
									<input type="hidden" name="SerieNota" value="<?php echo $SerieNota; ?>">
                  <input type="hidden" name="EstoqueVirtual" value="<?php echo $EstoqueVirtual; ?>">
									<input type="hidden" name="DataEntrada" value="<?php echo $DataEntrada; ?>">
                  <input type="hidden" name="DataUltimaAlteracao" value="<?php echo $DataUltimaAlteracao; ?>">
									<input type="hidden" name="DataEmissao" value="<?php echo $DataEmissao; ?>">
									<input type="hidden" name="ValorNota" value="<?php echo $ValorNota; ?>">
									<input type="hidden" name="ValNota" value="<?php echo $ValNota; ?>">
									<input type="hidden" name="AnoEmpenho" value="<?php echo $AnoEmpenho; ?>">
									<input type="hidden" name="OrgaoEmpenho" value="<?php echo $OrgaoEmpenho; ?>">
									<input type="hidden" name="UnidadeEmpenho" value="<?php echo $UnidadeEmpenho; ?>">
									<input type="hidden" name="SequencialEmpenho" value="<?php echo $SequencialEmpenho; ?>">
									<input type="hidden" name="ParcelaEmpenho" value="<?php echo $ParcelaEmpenho; ?>">
									<input type="button" name="Cancelar" value="Cancelar Nota Fiscal" class="botao" onClick="javascript:enviar('Cancelar');">
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