<?php
#----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMovimentacaoConfirmar.php
# Autor:    Álvaro Faria
# Data:     26/05/2006
# Objetivo: Programa para incluir uma movimentação de entrada ou saída dos itens estocados
#---------------------------------------------
# Alterado: Álvaro Faria
# Data:     20/06/2006 - Na troca, não exibir quantidade em estoque do outro almoxarifado
# Alterado: Álvaro Faria
# Data:     18/07/2006 - Adição de campos de Ano e Almoxarifado no select de retorno
#           do número sequencial da movimentação de Saída por Empréstimo (linha 2036)
# Alterado: Álvaro Faria
# Data:     01/08/2006 - Custo para acerto de inventário e correções diversas de querys,
#           principalmente com relação a flag de inatividade da tabela de movimentação
#           Implantação do Centro de Custo Patrimonio
# Alterado: Álvaro Faria
# Data:     17/08/2006
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
# Alterado: Álvaro Faria
# Data:     05/12/2006 - Correção de Empréstimo/Troca/Doação entre Almoxarifados que passa
#                        a receber o valor do material do Almoxarifado Secundário se este
#                        tiver na tabela de Armazenamento o valor zerado
# Alterado: Álvaro Faria
# Data:     13/12/2006 - Alteração para pegar a RPA da tabela de Almoxarifado, não mais na tabela de CC
# Alterado: Álvaro Faria
# Data:     03/01/2007 - Suporte a materiais didáticos, fardamento e limpeza
# Alterado: Álvaro Faria
# Data:     31/01/2006 - Suporte aos tipos 26 e 27, com nova funcionalidade e descrição
# Alterado: Carlos Abreu
# Data:     07/03/2007 - Ajuste na apresentacao da descricao do material para apresentar quando material não presente em nenhum almoxarifado
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Inclusao de bloqueio para evitar movimentacoes anteriores a ultimo inventario - Não colocado em produção até a próxima data
# Alterado: Álvaro Faria
# Data:     18/12/2007 - Inclusao de bloqueio para evitar movimentacoes anteriores ao último inventario com exceção ao tipo 13
# Alterado: Álvaro Faria
# Data:     21/12/2007 - Impedimento da realização de movimentações de entrada (6/9/11/29/31/26) que correspondem a saídas acontecidas no período antes do último inventário
#                      - Alteração para trabalhar com o valor do próprio em movimentações entre almoxarifados, quando o almoxarifado já tiver o material
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado.
# Alterado: Rodrigo Melo
# Data:     24/04/2008 - Ajuste nas movimentações para chamar a rotina de lançamento contábil.
# Alterado: Rodrigo Melo
# Data:     04/06/2008 - Ajuste na movimentação 19 para substituir na query "SELECT A.DREQMADATA" por "SELECT MAX(A.DREQMADATA)", pois estava trazendo dois registros nos casos em que havia um acerto da requisição.
# Alterado: Rodrigo Melo
# Data:      09/07/2008 - Alteração para permitir que as movimentações sejam realizadas apenas pelo estoque real e não pelo estoque total e para as movimentações 2, 21 e 22 também permitir alterar o estoque virtual (caso seja necessário).
# Alterado: Ariston Cordeiro
# Data:      05/12/2008 - Alteração para obrigar qur uma requisição atendida por nota fiscal virtual seja atendida por completo
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# Alterado: Ariston Cordeiro
# Data:     13/08/2009 - 	CR2699- Corrigindo saída por devolução interna para requisições atendidas por notas fiscais virtuais:
#																	* Calcular o valor médio após a saída por alteração de nota fiscal
#																	* Deletar o item da Nota fiscal virtual quando for feita a devolução interna
#																	* Apenas deletar a nota fiscal virtual quando o último ítem for cancelado
# Alterado: Ariston Cordeiro
# Data:     28/08/2009 - CR2699- Correção de valor médio no cancelamento de nota fiscal virtual (em devolução interna)
# Alterado: Ariston Cordeiro
# Data:     26/10/2009 - CR2699- Na devolução interna de NF virtuais, ao checar o cancelamento de movimentações (para cancelamento de NF), era feito checagem para todos materiais da NF. agora é feito a checagem apenas das movimentações do material em que é feito a devolução interna.
#------------------------------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

#Acesso a rotina de lançamento custo/contábil
include "../oracle/estoques/RotLancamentoCustoContabil.php";


# Executa o controle de segurança #
session_start();
Seguranca();

AddMenuAcesso( '/estoques/RelAuxilioCancelamentoNotaPdf.php' );


$ProgramaDestino = "CadMovimentacaoConfirmar.php";

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$AnoMovimentacao     = $_POST['AnoMovimentacao'];
		$Material            = $_POST['Material'];
		$QtdEstoque          = $_POST['QtdEstoque'];
		$QtdEstoqueReal      = $_POST['QtdEstoqueReal'];
		$QtdEstoqueVirtual   = $_POST['QtdEstoqueVirtual'];
		$TipoMovimentacao    = $_POST['TipoMovimentacao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$Localizacao         = $_POST['Localizacao'];
		$Movimentacao        = $_POST['Movimentacao'];
		$QtdMovimentada      = str_replace(",",".",$_POST['QtdMovimentada']);
		$MovNumero           = $_POST['MovNumero'];
		$Matricula           = $_POST['Matricula'];
		$Responsavel         = RetiraAcentos(strtoupper2(trim($_POST['Responsavel'])));
		$Observacao          = RetiraAcentos(strtoupper2(trim($_POST['Observacao'])));
		$NCaracteresO        = $_POST['NCaracteresO'];
		$SeqRequisicao       = $_POST['SeqRequisicao'];
		$Situacao            = $_POST['Situacao'];
		$EstoqueVirtual      = $_POST['EstoqueVirtual'];
		$FornecedorCod       = $_POST['FornecedorCod'];
		$NumeroNota          = $_POST['NumeroNota'];
		$SerieNota           = $_POST['SerieNota'];
		$TipoPesquisa        = $_POST['TipoPesquisa']; // Itens em estoque ou cadastro de material
		$PedeValor           = $_POST['PedeValor'];    // Se não estiver no estoque, variavel fica setada para pedir valor na página
		$Valor               = str_replace(",",".",$_POST['Valor']);
		$SeqMovimentacao     = $_POST['SeqMovimentacao'];
		$AnoAtualizar        = $_POST['AnoAtualizar'];
		$AlmoxSec            = $_POST['AlmoxSec'];
		$MovimentacaoSecun   = $_POST['MovimentacaoSecun'];
		$MatriculaSecun      = $_POST['MatriculaSecun'];
		$ResponsavelSecun    = RetiraAcentos(strtoupper2(trim($_POST['ResponsavelSecun'])));
		$CodReduzMat2        = $_POST['CodReduzMat2'];
		$QuantMat2           = str_replace(",",".",$_POST['QuantMat2']);
}else{
		$AnoMovimentacao     = $_GET['AnoMovimentacao'];
		$Material            = $_GET['CodigoReduzido'];
		$TipoMovimentacao    = $_GET['TipoMovimentacao'];
		$Almoxarifado        = $_GET['Almoxarifado'];
		$Localizacao         = $_GET['Localizacao'];
		$Movimentacao        = $_GET['Movimentacao'];
		$QtdMovimentada      = str_replace(",",".",$_GET['QtdMovimentada']);
		$MovNumero           = $_GET['MovNumero'];
		$SeqRequisicao       = $_GET['SeqRequisicao'];
		$Situacao            = $_GET['Situacao'];
		$EstoqueVirtual      = $_GET['EstoqueVirtual'];
		$FornecedorCod       = $_GET['FornecedorCod'];
		$NumeroNota          = $_GET['NumeroNota'];
		$SerieNota           = $_GET['SerieNota'];
		$TipoPesquisa        = $_GET['TipoPesquisa']; // Itens em estoque ou cadastro de material
		$Valor               = str_replace(",",".",$_GET['Valor']);
		$SeqMovimentacao     = $_GET['SeqMovimentacao'];
		$AnoAtualizar        = $_GET['AnoAtualizar'];
		$AlmoxSec            = $_GET['AlmoxSec'];
		$MovimentacaoSecun   = $_GET['MovimentacaoSecun'];
		$MatriculaSecun      = $_GET['MatriculaSecun'];
		$ResponsavelSecun    = RetiraAcentos(strtoupper2(trim(urldecode($_GET['ResponsavelSecun']))));
		$CodReduzMat2        = $_GET['CodReduzMat2'];
		$QuantMat2           = str_replace(",",".",$_GET['QuantMat2']);
}

# Função que é chamada sempre que há um sucesso na gravação de movimentações. Ela grava em SESSION informações que depois podem ser checadas no caso de repetição da inclusão, seja por F5 ou não, impedindo inclusão se as informações forem iguais #
function GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao){
		$_SESSION['Almoxarifado']    = $Almoxarifado;
		$_SESSION['AnoMovimentacao'] = $AnoMovimentacao;
		$_SESSION['Movimentacao']    = $Movimentacao;
		$_SESSION['Material']        = $Material;
		$_SESSION['QtdMovimentada']  = $QtdMovimentada;
		$_SESSION['GrupoEmp']        = $GrupoEmp;
		$_SESSION['Usuario']         = $Usuario;
		$_SESSION['DataGravacao']    = $DataGravacao;
}

# Função que é chamada para verificar se já houve recebimento das movimentações (12,13,15,30) pelas movimentações (9,6,11,29), evitando recebimento simultâneo #
function ChecaNaoExistenciaFlag($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db){
		$sql  = "SELECT FMOVMACORR FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= " WHERE CALMPOCODI = $AlmoxSec ";
		$sql .= "   AND AMOVMAANOM = $AnoAtualizar ";
		$sql .= "   AND CMOVMACODI = $SeqMovimentacao ";
		$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
		}else{
				$ChkFlagArray = $res->fetchRow();
				$ChkFlag      = $ChkFlagArray[0];
				if($ChkFlag == 'S'){
						return false;
				}else{ // Flag insexistente, Bloqueia movimentação Origem (12,13,15,30) e retorna TRUE.
						$sql  = "SELECT * ";
						$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sql .= " WHERE CALMPOCODI = $AlmoxSec ";
						$sql .= "   AND AMOVMAANOM = $AnoAtualizar ";
						$sql .= "   AND CMOVMACODI = $SeqMovimentacao ";
						$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
						$sql .= "   FOR UPDATE ";
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
						}else{
								return true;
						}
				}
		}
}

# Função que é chamada para verificar o não cancelamento da movimentação de origem (12,13,15,30). Retorna True se ainda existir, False, se foi cancelada pelo Almoxarifado de Origem (31). É usada para as movimentações 9, 6, 11, 29 e especialmente pela 31, para checar se houve cancelamento simultaneo #
function ChecaNaoExistenciaCanc($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db){
		$sql  = " SELECT AMOVMAQTDM ";
		$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= " WHERE CTIPMVCODI = 31 ";
		$sql .= "   AND CALMPOCOD1 = $AlmoxSec ";
		$sql .= "   AND AMOVMAANO1 = $AnoAtualizar ";
		$sql .= "   AND CMOVMACOD1 = $SeqMovimentacao ";
		$sql .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				$db->query("ROLLBACK");
				$db->query("END TRANSACTION");
				$db->disconnect();
		}else{
				$MovExiste = $res->numRows();
				if($MovExiste == 0) { // Não existe movimentação de cancelamento. Retorna TRUE.
						return true;
				}else{
						return false;
				}
		}
}

# Função para checar existência em estoque e buscar o valor do material. Retorna o valor e bloqueia material se existir, dá mensagem de erro se não tiver valor e se não foi digitado #
# Função não usada pelas movimentações entre almoxarifados 6,9,11,13,29 #
function PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db){
		Global $Mens;
		Global $Tipo;
		Global $Virgula;
		Global $Mensagem;
		if ( (!$Valor) and ($PedeValor != 1) ){
				# Resgata o Valor Médio na tabela de Armazenamento do almoxarifado atual #
				$sqlValor  = " SELECT VARMATUMED ";
				$sqlValor .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL ";
				$sqlValor .= " WHERE CMATEPSEQU = $Material ";
				$sqlValor .= "   AND CLOCMACODI = $Localizacao ";
				$sqlValor .= "   FOR UPDATE ";
				$resValor  = $db->query($sqlValor);
				if( db::isError($resValor) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlValor");
						$db->query("ROLLBACK");
						$db->query("END TRANSACTION");
						$db->disconnect();
				}else{
						$Linha = $resValor->fetchRow();
						$Valor = $Linha[0];
						if( $Valor == 0 or !$Valor ) {
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 2;
								$Mensagem  = "Inclusão da Movimentação não poderá ser efetuada, pois o Material não tem Valor Unitário";
						}else{
								return $Valor;
						}
				}
		}else{
				return $Valor;
		}
}

# ********  CARREGANDO VARIÁVEIS SIMPLES - INÍCIO ******** #

$MetodoDeChamada = $_SERVER['REQUEST_METHOD'];
$GrupoEmp        = $_SESSION['_cgrempcodi_'];
$Usuario         = $_SESSION['_cusupocodi_'];
$Det             = 77;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma    = __FILE__;
$NomePrograma = "CadMovimentacaoConfirmar.php";

if(!$QtdEstoque){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		# Sql para pegar a quantidade em estoque e armazenar em variável para POST #
		$sql  = " SELECT AARMATQTDE, AARMATESTR, AARMATVIRT ";
		$sql .= "   FROM SFPC.TBARMAZENAMENTOMATERIAL ";
		$sql .= "  WHERE CLOCMACODI = $Localizacao ";
		$sql .= "    AND CMATEPSEQU = $Material ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha      = $res->fetchRow();
				$QtdEstoque = $Linha[0];
				$QtdEstoqueReal = $Linha[1];
				$QtdEstoqueVirtual = $Linha[2];
		}
		$db->disconnect();
}

if( ($TipoPesquisa == 0) and ($Movimentacao == 10 or $Movimentacao == 32) ){
		$db = Conexao();
		# Verifica se o Item já faz parte da localização #
		$sqlItem  = "SELECT CMATEPSEQU ";
		$sqlItem .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL ";
		$sqlItem .= " WHERE CMATEPSEQU = $Material ";
		$sqlItem .= "   AND CLOCMACODI = $Localizacao ";
		$resItem  = $db->query($sqlItem);
		if( db::isError($resItem) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItem");
		}else{
				$Rows = $resItem->numRows();
				if($Rows == 0){
					$PedeValor = 1;
				}
		}
}

# ********  CARREGANDO VARIÁVEIS SIMPLES - FIM ******** #


# ********  CARREGANDO VARIÁVEIS ESPECÍFICAS - INICIO ******** #

# ** SETA TIPO DO MATERIAL PARA AS MOVs QUE GERAM CUSTO - INÍCIO ** #
# ****** MATERIAL PRIMÁRIO - INICIO ***** #
# Se Devolução Interna (2), Empréstimo (6), Devolução de empréstimo (9), Entrada doação Externa (10), Troca (11), Saída por Obsoletismo (14), Avaria (16), Prazo de Validade (17), Entrada Acerto Dev. Int (21), Saída Acerto Dev. Int (22), ou Furto (23), Saída doação Externa (24), Saída por Acerto Inventário (25), Entrada para Acerto de Cancelamento de Nota (26), Saída para Acerto de Cancelamento de Nota (27), Entrada por Acerto de Inventário (28) ou Entrada doação entre Almox (29)
if(  $MetodoDeChamada == "POST" and ( ($Movimentacao == 2) or ($Movimentacao == 6) or ($Movimentacao == 9) or ($Movimentacao == 10) or ($Movimentacao == 11) or ($Movimentacao == 14) or ($Movimentacao == 16) or ($Movimentacao == 17) or ($Movimentacao == 21) or ($Movimentacao == 22) or ($Movimentacao == 23) or ($Movimentacao == 24) or ($Movimentacao == 25) or ($Movimentacao == 26) or ($Movimentacao == 27) or ($Movimentacao == 28) or ($Movimentacao == 29) or ($Movimentacao == 32) or ($Movimentacao == 37) )  ){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		$sqlTipMat  = "SELECT C.FGRUMSTIPC, C.FGRUMSTIPM "; //MUDAR PARA OBTER a coluna C.FGRUMSTIPM
		$sqlTipMat .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
		$sqlTipMat .= "       SFPC.TBGRUPOMATERIALSERVICO C ";
		$sqlTipMat .= " WHERE A.CMATEPSEQU = $Material ";
		$sqlTipMat .= "   AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
		$sqlTipMat .= "   AND B.CGRUMSCODI = C.CGRUMSCODI ";
		$resTipMat  = $db->query($sqlTipMat);
		if( db::isError($resTipMat) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTipMat");
		}else{
				$LinhaTipMat  = $resTipMat->fetchRow();
				$TipoMaterial = $LinhaTipMat[0];
        $TipoMaterialTESTE = $LinhaTipMat[1]; //Irá substituir a variavel $TipoMaterial
				# Seta o Centro de Custo do Local de saída do material (Almoxarifado ou Patrimônio)
				if($TipoMaterial == 'P'){
						$CC = 800; # Patrimônio
				}else{
						$CC = 799; # Almoxarifado
				}
		}
		$db->disconnect();
}
# ****** MATERIAL PRIMÁRIO - FIM ***** #
# *** MATERIAL SECUNDÁRIO - INICIO *** #
# Se Troca (11) #
if($MetodoDeChamada == "POST" and $Movimentacao == 11){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		# Descobrindo o local secundário #
		$sqlLocal = "SELECT CLOCMACODI FROM SFPC.TBLOCALIZACAOMATERIAL WHERE CALMPOCODI = $AlmoxSec";
		$resLocal = $db->query($sqlLocal);
		if( db::isError($resLocal) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlLocal");
		}else{
				$LinhaLocal = $resLocal->fetchRow();
				$LocalSecun = $LinhaLocal[0];
		}
		# Descobrindo Valor do item que será usado na movimentação de saída e no armazenado do almoxarifado secundário, se este já não tiver o item #
		$sqlValor  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
		$sqlValor .= " WHERE CMATEPSEQU = $CodReduzMat2 AND CLOCMACODI = $Localizacao ";
		$resValor  = $db->query($sqlValor);
		if( db::isError($resValor) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlValor");
		}
		$LinhaValor = $resValor->fetchRow();
		$ValorMat2  = $LinhaValor[0];

		$sqlTipMatSecu  = "SELECT C.FGRUMSTIPC, C.FGRUMSTIPM ";
		$sqlTipMatSecu .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
		$sqlTipMatSecu .= "       SFPC.TBGRUPOMATERIALSERVICO C ";
		$sqlTipMatSecu .= " WHERE A.CMATEPSEQU = $CodReduzMat2 ";
		$sqlTipMatSecu .= "   AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
		$sqlTipMatSecu .= "   AND B.CGRUMSCODI = C.CGRUMSCODI ";
		$resTipMatSecu  = $db->query($sqlTipMatSecu);
		if( db::isError($resTipMatSecu) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTipMatSecu");
		}else{
				$LinhaTipMatSecu = $resTipMatSecu->fetchRow();
				$TipoMaterial2   = $LinhaTipMatSecu[0];
        $TipoMaterialTESTE2 = $LinhaTipMatSecu[1];
		}
		$db->disconnect();
}
# *** MATERIAL SECUNDÁRIO - FIM *** #
# ** SETA TIPO DO MATERIAL PARA AS MOVs QUE GERAM CUSTO - FIM ** #


# RESGATA DADOS PARA CUSTO - INÍCIO #
# Se Devolução Interna (2), Entrada por Acerto da Devolução Interna (21) e Saída por Acerto da Devolução Interna (22), RESGATA VALORES DE ÓRGÃO, UNIDADE, RPA, Centro de Custo e Detalhamento #
if(  $MetodoDeChamada == "POST" and ( ($Movimentacao == 2) or ($Movimentacao == 21) or ($Movimentacao == 22) )  ){
		# Resgata os dados na tabela de centro de custo #
		# Abre a conexão com banco de dados #
		$db = Conexao();
		$sql  = "SELECT A.CCENPOCORG, A.CCENPOUNID, A.CCENPONRPA, A.CCENPOCENT, A.CCENPODETA ";
		$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBREQUISICAOMATERIAL B ";
		$sql .= " WHERE A.CCENPOSEQU = B.CCENPOSEQU AND B.CREQMASEQU = $SeqRequisicao ";
		$sql .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha        = $res->fetchRow();
				$Orgao        = $Linha[0];
				$Unidade      = $Linha[1];
				$RPA          = $Linha[2];
				$CentroCusto  = $Linha[3];
				$Detalhamento = $Linha[4];
		}
		$db->disconnect();
}
# Se Empréstimo (6), Devolução de empréstimo (9), Entrada doação Externa (10), Troca (11), Saída por Obsoletismo (14), Avaria (16), Prazo de Validade (17), Furto (23), Saída doação Externa (24), Saída por Acerto Inventário (25), Entrada por Acerto de Canc. de Nota Fiscal (26), Saída por Acerto de Canc. de Nota Fiscal (27), Entrada por Acerto de Inventário (28) ou Entrada doação entre Almox (29)
if(  $MetodoDeChamada == "POST" and ( ($Movimentacao == 6) or ($Movimentacao == 9) or ($Movimentacao == 10) or ($Movimentacao == 11) or ($Movimentacao == 14) or ($Movimentacao == 16) or ($Movimentacao == 17) or ($Movimentacao == 23) or ($Movimentacao == 24) or ($Movimentacao == 25) or ($Movimentacao == 26) or ($Movimentacao == 27) or ($Movimentacao == 28) or ($Movimentacao == 29) or ($Movimentacao == 32) or ($Movimentacao == 37) )  ){
		# Resgata valores de órgão, unidade, rpa #
		# Abre a conexão com banco de dados #
		$db = Conexao();
		$sqlOUR  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
		$sqlOUR .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
		$sqlOUR .= " WHERE A.CORGLICODI = B.CORGLICODI ";
		$sqlOUR .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
		$sqlOUR .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
		$sqlOUR .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$resOUR  = $db->query($sqlOUR);
		if( db::isError($resOUR) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlOUR");
		}else{
				$rows = $resOUR->numRows();
				if($rows > 0){
						$LinhaOUR     = $resOUR->fetchRow();
						$Orgao        = $LinhaOUR[0];
						$Unidade      = $LinhaOUR[1];
						$RPA          = $LinhaOUR[2];
						$CentroCusto  = $CC;
						$Detalhamento = $Det;
				}else{
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem = "Falta Cadastrar o Centro de Custo $CC/$Det, Contatar o Responsável pelo Cadastramento de Centros de Custo";
						$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=$Mens&Tipo=$Tipo";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						$db->disconnect();
						exit;
				}
		}
		$db->disconnect();
}
# Se Empréstimo (6), Devolução de empréstimo (9), Troca (11) ou Doação entre Almox (29)
if(  $MetodoDeChamada == "POST" and ( ($Movimentacao == 6) or ($Movimentacao == 9) or ($Movimentacao == 11) or ($Movimentacao == 29) )  ){
		# Resgata valores de órgão, unidade, rpa - secundarios #
		# Abre a conexão com banco de dados #
		$db = Conexao();
		$sqlOUR  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
		$sqlOUR .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
		$sqlOUR .= " WHERE A.CORGLICODI = B.CORGLICODI ";
		$sqlOUR .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
		$sqlOUR .= "   AND B.CALMPOCODI = $AlmoxSec AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
		$sqlOUR .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$resOUR  = $db->query($sqlOUR);
		if( db::isError($resOUR) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlOUR");
		}else{
				$rows = $resOUR->numRows();
				if($rows > 0){
						$LinhaOUR	= $resOUR->fetchRow();
						$OrgaoSecun   = $LinhaOUR[0];
						$UnidadeSecun = $LinhaOUR[1];
						$RPASecun     = $LinhaOUR[2];
				}else{
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem = "Falta Cadastrar o Centro de Custo $CC/$Det do Almoxarifado Secundário, Contatar o Responsável pelo Cadastramento de Centros de Custo";
						$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=$Mens&Tipo=$Tipo";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
		$db->disconnect();
}
# RESGATA DADOS DO ALMOXARIFADO PARA O CUSTO - FIM #

# Se Saída por Devolução de Empréstimo #
if($Movimentacao == 13){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		# Descobre as chaves da movimentação de Entrada por Empréstimo e a quantidade emprestada #
		$sqlNrMovOri  = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, AMOVMAQTDM FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sqlNrMovOri .= " WHERE CMOVMACODI = $SeqMovimentacao ";
		$sqlNrMovOri .= "   AND CTIPMVCODI = 6 ";
		$sqlNrMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
		$resNrMovOri  = $db->query($sqlNrMovOri);
		if( db::isError($resNrMovOri) ){
				$Mens = 1;
				$db->disconnect();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlNrMovOri");
		}else{
				$LinhaNrMovOri = $resNrMovOri->fetchRow();
				$MovAlmoxOri   = $LinhaNrMovOri[0];
				$MovAnoOri     = $LinhaNrMovOri[1];
				$MovNrOri      = $LinhaNrMovOri[2];
				$QuantEmprest  = $LinhaNrMovOri[3];
				# Descobre quanto já foi devolvido para este empréstimo #
				$sqlDev  = "SELECT A.AMOVMAQTDM, A.CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL A"; // 13 - Devolução de Empréstimo
				$sqlDev .= " WHERE A.CTIPMVCODI = 13 ";
				$sqlDev .= "   AND A.CALMPOCOD1 = $MovAlmoxOri ";
				$sqlDev .= "   AND A.AMOVMAANO1 = $MovAnoOri   ";
				$sqlDev .= "   AND A.CMOVMACOD1 = $MovNrOri    ";
				$sqlDev .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
				$sqlDev .= " UNION ALL ";
				$sqlDev .= "SELECT B.AMOVMAQTDM, B.CTIPMVCODI FROM SFPC.TBMOVIMENTACAOMATERIAL B"; // 31 - Cancelamento de Devolução
				$sqlDev .= " INNER JOIN SFPC.TBMOVIMENTACAOMATERIAL C ";
				$sqlDev .= "    ON B.CTIPMVCODI = 31 AND B.CALMPOCOD1 = C.CALMPOCODI AND B.AMOVMAANO1 = C.AMOVMAANOM AND B.CMOVMACOD1 = C.CMOVMACODI ";
				$sqlDev .= "   AND C.CTIPMVCODI = 13 AND C.CALMPOCOD1 = $MovAlmoxOri AND C.AMOVMAANO1 = $MovAnoOri AND C.CMOVMACOD1 = $MovNrOri ";
				$sqlDev .= " WHERE (B.FMOVMASITU IS NULL OR B.FMOVMASITU = 'A') ";
				$sqlDev .= "   AND (C.FMOVMASITU IS NULL OR C.FMOVMASITU = 'A') ";
				$resDev  = $db->query($sqlDev);
				if( db::isError($resDev) ){
						$Mens = 1;
						$db->disconnect();
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDev");
				}else{
						while($Devolucao = $resDev->fetchRow()){
								$QtdDevCanc     = $Devolucao[0];
								$TipoDevCanc    = $Devolucao[1];
								if($TipoDevCanc == 13){ // Empréstimo
										$QtdDevolvida = $QtdDevolvida + $QtdDevCanc;
								}else{                  // Cancelamento de Movimentação
										$QtdDevolvida = $QtdDevolvida - $QtdDevCanc;
								}
						}
				}
		}
		if(!$_POST['QtdMovimentada']) $QtdMovimentada = $QuantEmprest - $QtdDevolvida; // Aparece já preenchido a quantidade do movimento com o que falta para devolver de um empréstimo
}

# ACERTO DE DEVOLUÇÃO INTERNA (21 e 22) - VERIFICAÇÕES - INÍCIO #
if($Movimentacao == 21 or $Movimentacao == 22){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		# Seleciona o Codigo da Nota Fiscal #
		$sql1  = "SELECT CENTNFCODI, AENTNFANOE ";
		$sql1 .= "  FROM SFPC.TBENTRADANOTAFISCAL ";
		$sql1 .= " WHERE AENTNFNOTA = $NumeroNota AND AENTNFSERI = '$SerieNota' ";
		$sql1 .= "   AND ( (AFORCRSEQU = $FornecedorCod) OR (CFORESCODI = $FornecedorCod) ) ";
		$sql1 .= "   AND CALMPOCODI = $Almoxarifado";
		$res1  = $db->query($sql1);
		if( db::isError($res1) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql1");
		}else{
				$LocalSeqNotaRes = $res1->fetchRow();
				$NumeroSeqNota   = $LocalSeqNotaRes[0];
				$AnoNota         = $LocalSeqNotaRes[1];
				# Verifica se já não há a movimentação para o Cancelamento da Nota Cadastrada
				$sqlmovi  = "SELECT COUNT(*) ";
				$sqlmovi .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
				$sqlmovi .= " WHERE CALMPOCODI = $Almoxarifado ";
				$sqlmovi .= "   AND AENTNFANOE = $AnoNota ";
				$sqlmovi .= "   AND CENTNFCODI = $NumeroSeqNota ";
				$sqlmovi .= "   AND CTIPMVCODI = $Movimentacao ";  // Pode ser 21 ou 22
				$sqlmovi .= "   AND CMATEPSEQU = $Material ";      // Para o material corrente
				$sqlmovi .= "   AND CREQMASEQU = $SeqRequisicao "; // Requisição corrente
				$sqlmovi .= "	  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
				$resmovi  = $db->query($sqlmovi);
				if( db::isError($resmovi) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovi");
				}else{
						$VeriMovi     = $resmovi->fetchRow();
						$VerificaMovi = $VeriMovi[0];
						if ($VerificaMovi > 0){
								# No caso de já está cadastrada esta movimentação e o usuário não foi    #
								# imediatamente cancelar a nota, fez uma nova devolução interna,         #
								# não será possível fazer novo acerto, pois está crítica aparecerá.      #
								# Será necessário inativar a movimentação de acerto, corrigir o estoque  #
								# com a quantidade desta movimentação e pedir para o usuário fazer o     #
								# acerto novamente. Caso esteja tudo Ok, o usuário pode mexer na nota.   #
								$Mensagem = "Uma movimentação de Acerto de Devolução Interna para o Cancelamento da Nota Fiscal $NumeroNota/$SerieNota já foi cadastrada para este Material";
								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								# Verifica se o item faz parte da nota fiscal #
								$sql2    = "SELECT CMATEPSEQU, TITENFULAT ";
								$sql2   .= "  FROM SFPC.TBITEMNOTAFISCAL ";
								$sql2   .= " WHERE CALMPOCODI = $Almoxarifado ";
								$sql2   .= "   AND AENTNFANOE = $AnoNota ";
								$sql2   .= "   AND CENTNFCODI = $NumeroSeqNota ";
								$sql2   .= "   AND CMATEPSEQU = $Material ";
								$res2    = $db->query($sql2);
								$NumReg2 = $res2->numRows();
								if(db::isError($res2)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql2");
								}else{
										if($NumReg2 == 0){
												$Mensagem = "Este material não se encontra na Nota Fiscal selecionada";
												$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}else{
												# Verifica se houve Movimentações (2 - Entrada por Devolução Interna, 21 - Entrada por Acerto da Devolução Interna, 22 - Saída por Acerto da Devolução Interna) para a Nota Fiscal corrente #
												if($TipoMovimentacao == "S"){
														$LocalLinha    = $res2->fetchRow();
														$LocalDataHora = $LocalLinha[1];
														$sql3        = "SELECT CMATEPSEQU, SUM(AMOVMAQTDM) ";
														$sql3       .= " FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql3       .= " WHERE CALMPOCODI = $Almoxarifado ";
														$sql3       .= "   AND CMATEPSEQU = $Material ";
														$sql3       .= "   AND CTIPMVCODI IN (2,21,22) "; // Entrada por Devolução Interna, Entrada por Acerto de Devolução Interna
														$sql3       .= "   AND CREQMASEQU = $SeqRequisicao ";
														$sql3       .= "   AND TMOVMAULAT > '$LocalDataHora'";
														$sql3       .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sql3       .= " GROUP BY CMATEPSEQU";
														$res3        = $db->query($sql3);
														$NumReg3     = $res3->numRows();
														$LocalQtdRes = $res3->fetchRow();
														$LocalQtd    = $LocalQtdRes[1];
														if( db::isError($res3) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql3");
														}else{
																if( $NumReg3 == 0 ) {
																		$Mensagem = "Não existem Movimentações do tipo Entrada por Devolução Interna ou Entrada por Acerto da Devolução Interna, deste material, após a inclusão da Nota Fiscal selecionada";
																		$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
																}else{
																		$QtdMovimentada = $LocalQtd;
																}
														}
												}else{
														$LocalLinha    = $res2->fetchRow();
														$LocalDataHora = $LocalLinha[1];
														$sql3          = "SELECT CMATEPSEQU, SUM(AMOVMAQTDM) ";
														$sql3         .= " FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql3         .= " WHERE CALMPOCODI = $Almoxarifado AND CMATEPSEQU = $Material ";
														$sql3         .= "   AND CTIPMVCODI = 22 "; // Saída por Acerto de Devolução Interna
														$sql3         .= "   AND TMOVMAULAT > '$LocalDataHora'";
														$sql3         .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
														$sql3         .= " GROUP BY CMATEPSEQU";
														$res3          = $db->query($sql3);
														$NumReg3       = $res3->numRows();
														$LocalQtdRes   = $res3->fetchRow();
														$LocalQtd      = $LocalQtdRes[1];
														if( db::isError($res3) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql3");
														}else{
																if($NumReg3 == 0){
																		$Mensagem = "Não existem Movimentações do tipo Saída por Acerto da Devolução Interna, deste material, após a inclusão da Nota Fiscal selecionada";
																		$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
																}else{
																		$QtdMovimentada = $LocalQtd;
																}
														}
												}
										}
								}
						}
				}
		}
		$db->disconnect();
}
# ACERTO DE DEVOLUÇÃO INTERNA (21 e 22) - VERIFICAÇÕES - FIM #

# CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO (26 e 27) - VERIFICAÇÕES - INÍCIO #
if($Movimentacao == 26 or $Movimentacao == 27){
		# Abre a conexão com banco de dados #
		$db = Conexao();
		# Verifica quantidade da movimentação de tipo 10 / 14 / 16 / 17 / 23 / 24, para travar quantidade #
		if($TipoMovimentacao == "S"){
				$sql    = "SELECT AMOVMAQTDM ";
				$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
				$sql   .= " WHERE AMOVMAANOM = $AnoAtualizar ";
				$sql   .= "   AND CALMPOCODI = $Almoxarifado ";
				$sql   .= "   AND CMOVMACODI = $SeqMovimentacao ";
				$sql   .= "   AND CMATEPSEQU = $Material ";
				$sql   .= "   AND CTIPMVCODI in (10,26,32) "; // Entrada Doação Externa
				$sql   .= "   AND FMOVMACORR IS NULL ";
				$sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
				$res    = $db->query($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$NumReg = $res->numRows();
						if($NumReg == 0){
								$Mensagem = "Uma Saída para Cancelamento de Movimentacão sem Retorno já foi cadastrada para esta movimentação";
								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								$LocalQtdRes    = $res->fetchRow();
								$QtdMovimentada = $LocalQtdRes[0];
						}
				}
		}else{
				$sql    = "SELECT AMOVMAQTDM ";
				$sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
				$sql   .= " WHERE AMOVMAANOM = $AnoAtualizar ";
				$sql   .= "   AND CALMPOCODI = $Almoxarifado ";
				$sql   .= "   AND CMOVMACODI = $SeqMovimentacao ";
				$sql   .= "   AND CMATEPSEQU = $Material ";
				$sql   .= "   AND CTIPMVCODI IN (14,16,17,23,24,27,37) ";
				$sql   .= "   AND FMOVMACORR IS NULL ";
				$sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
				$res    = $db->query($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$NumReg = $res->numRows();
						if($NumReg == 0){
								$Mensagem = "Uma Entrada para Cancelamento de Movimentacão sem Retorno já foi cadastrada para esta movimentação";
								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								$LocalQtdRes    = $res->fetchRow();
								$QtdMovimentada = $LocalQtdRes[0];
						}
				}
		}
		$db->disconnect();
}
# CANCELAMENTO DE MOVIMENTACÃO SEM RETORNO (26 e 27) - VERIFICAÇÕES - FIM #

# ********  CARREGANDO VARIÁVEIS ESPECÍFICAS - FIM ******** #


# *********** EXECUTANDO POSSÍVEIS SITUAÇÕES - INÍCIO ************ #
if($Botao == "Voltar"){
		header("location: CadMovimentacaoIncluir.php");
		exit;
}

# Checa se a data da requisição é anterior ao fechamento do último inventário #
if($SeqRequisicao){
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
		$sql .= "   AND B.TINVCOFECH < (SELECT MAX(A.DREQMADATA) ";
		$sql .= "                         FROM SFPC.TBREQUISICAOMATERIAL A ";
		$sql .= "                        INNER JOIN SFPC.TBSITUACAOREQUISICAO B ";
		$sql .= "                           ON A.CREQMASEQU = B.CREQMASEQU ";
		$sql .= "                          AND B.CTIPSRCODI IN (3,4) ";
		$sql .= "                        WHERE A.CREQMASEQU = $SeqRequisicao)";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $res->fetchRow();
				if($Linha[0]==0){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "Movimentação não pode ser realizada pois Requisição possui referência a período anterior ao último inventário do almoxarifado";
						$Botao = "";
				}
		}
		$db->disconnect();
}elseif($SeqMovimentacao and ($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 29 or $Movimentacao == 31 or $Movimentacao == 26)){
		# Impede a realização de movimentações de entrada (6/9/11/29/31/26) que correspondem a saídas acontecidas no período antes do último inventário #
		$db   = Conexao();
		$sql  = "SELECT COUNT(B.TINVCOFECH) ";
		$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM B ";
		$sql .= " INNER JOIN SFPC.TBLOCALIZACAOMATERIAL C ";
		$sql .= "    ON B.CLOCMACODI = C.CLOCMACODI ";
		# A movimentação 26 e 31 é a única que não trabalha com outro almoxarifado #
		if($Movimentacao == 26 or $Movimentacao == 31){
				$sql .= "   AND C.CALMPOCODI = $Almoxarifado ";
		}else{
				$sql .= "   AND C.CALMPOCODI = $AlmoxSec ";
		}
		$sql .= " WHERE (B.AINVCOANOB,B.AINVCOSEQU) = ";
		$sql .= "       (SELECT MAX(A.AINVCOANOB),MAX(A.AINVCOSEQU) ";
		$sql .= "          FROM SFPC.TBINVENTARIOCONTAGEM A ";
		$sql .= "         WHERE A.AINVCOANOB = ";
		$sql .= "               (SELECT MAX(AINVCOANOB) ";
		$sql .= "                  FROM SFPC.TBINVENTARIOCONTAGEM  ";
		$sql .= "                 WHERE A.AINVCOANOB = AINVCOANOB ) ";
		$sql .= "           AND A.CLOCMACODI = B.CLOCMACODI) ";
		$sql .= "   AND B.TINVCOFECH < (SELECT A.DMOVMAMOVI ";
		$sql .= "                         FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
		$sql .= "                        WHERE A.CALMPOCODI = C.CALMPOCODI ";
		$sql .= "                          AND A.AMOVMAANOM = $AnoAtualizar ";
		$sql .= "                          AND A.CMOVMACODI = $SeqMovimentacao) ";
		$res  = $db->query($sql);
		if(db::isError($res)){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $res->fetchRow();
				if($Linha[0]==0){
						$Mens     = 1;
						$Tipo     = 2;
						$Mensagem = "Movimentação não pode ser realizada pois possui referência a período anterior ao último inventário do almoxarifado";
						$Botao = "";
				}
		}
		$db->disconnect();
}

if($Botao == "Incluir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";

		# *******************  VERIFICAÇÕES GERAIS - INICIO ******************** #
		# Verifica a quantidade digitada #
		if( $QtdMovimentada == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade</a>";
		}else{
				if(!SoNumVirg(str_replace(".",",",$QtdMovimentada))){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Válida</a>";
				}else{
						if( $QtdMovimentada <= 0 ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Válida</a>";
						}
				}
		}
		if ($PedeValor == 1){ // Se PedeValor for igual a 1, significa que o item não está no estoque e o valor não é conhecido
					if( ($Valor == "") or ($Valor == 0) ){ // Se o valor não foi digitado, pede para ser
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Valor.focus();\" class=\"titulo2\">Valor Unitário</a>";
				}
		}

		# Verifica se a quantidade em estoque é maior que a quantidade removida #
		if ($QtdEstoque) {
				if($TipoMovimentacao == "S"){
						$QtdFinal = $QtdEstoque - $QtdMovimentada;

            if($EstoqueVirtual == 'S'){
              $QtdFinalVirtual = $QtdEstoqueVirtual - $QtdMovimentada;
            } else {
              $QtdFinalReal = $QtdEstoqueReal - $QtdMovimentada;
            }




						if($QtdFinal < 0 && ($QtdFinalVirtual < 0 || $QtdFinalReal < 0)){
								# Movimentações com valor travado, não pedem Quantidade movimentada menor, apenas exibe erro
								if($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 21 or $Movimentacao == 22 or $Movimentacao == 26 or $Movimentacao == 27 or $Movimentacao == 29 or $Movimentacao == 31){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens       = 1;
										$Tipo       = 2;
										$Virgula    = 2;
										$Qtdvirgula = str_replace('.',',',$QtdEstoque);
										$QtdvirgulaVirtual = str_replace('.',',',$QtdEstoqueVirtual);
										$QtdvirgulaReal = str_replace('.',',',$QtdEstoqueReal);

                    if($EstoqueVirtual == 'S'){
                      $Mensagem   = "A Quantidade em Estoque Virtual ($QtdvirgulaVirtual) não permite proceder a movimentação</a>";
                    } else {
                      $Mensagem   = "A Quantidade em Estoque Real ($QtdvirgulaReal) não permite proceder a movimentação</a>";
                    }

										//$Mensagem   = "A Quantidade em Estoque ($Qtdvirgula) não permite proceder a movimentação</a>";
								}else{
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens       = 1;
										$Tipo       = 2;
										$Virgula    = 2;
										$Qtdvirgula = str_replace('.',',',$QtdEstoque);
                    $QtdvirgulaVirtual = str_replace('.',',',$QtdEstoqueVirtual);
										$QtdvirgulaReal = str_replace('.',',',$QtdEstoqueReal);

                    if($EstoqueVirtual == 'S'){
                      $Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual à Quantidade em Estoque Virtual ($QtdvirgulaVirtual)</a>";
                    } else {
                      $Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual à Quantidade em Estoque Real ($QtdvirgulaReal)</a>";
                    }

										//$Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual à Quantidade em Estoque ($Qtdvirgula)</a>";
								}
						}
				}elseif($TipoMovimentacao == "E"){
						$QtdFinal = $QtdEstoque + $QtdMovimentada;

						if($EstoqueVirtual == 'S'){
              $QtdFinalVirtual = $QtdEstoqueVirtual + $QtdMovimentada;
            } else {
              $QtdFinalReal = $QtdEstoqueReal + $QtdMovimentada;
            }
				}
		}

		# Se Saída por Devolução de Empréstimo #
		if($Movimentacao == 13){
				if($QtdDevolvida > 0){
						if($QuantEmprest < $QtdDevolvida + $QtdMovimentada){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$QtdMovimentadaMax        = $QuantEmprest - $QtdDevolvida;
								$QtdMovimentadaMaxVirgula = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdMovimentadaMax)));
								$QuantEmprestVirgula      = converte_quant(sprintf("%01.2f",str_replace(",",".",$QuantEmprest)));
								$QtdDevolvidaVirgula      = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdDevolvida)));
								$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada Máxima de $QtdMovimentadaMaxVirgula (a Soma desta com a Quantidade já Devolvida ($QtdDevolvidaVirgula) não pode superar a Quantidade Emprestada ($QuantEmprestVirgula))</a>";
						}
				}else{
						if($QuantEmprest < $QtdMovimentada){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$QuantEmprestVirgula = converte_quant(sprintf("%01.2f",str_replace(",",".",$QuantEmprest)));
								$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada Menor ou Igual à Quantidade Emprestada ($QuantEmprestVirgula)</a>";
						}
				}
		}

		if($Matricula == ""){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Matricula.focus();\" class=\"titulo2\">Matrícula</a>";
		}elseif( !SoNumeros($Matricula) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Matricula.focus();\" class=\"titulo2\">Matrícula Válida</a>";
		}
		if($Responsavel == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Responsavel.focus();\" class=\"titulo2\">Responsável</a>";
		}elseif(!NomeSobrenome($Responsavel) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.Responsavel.focus();\" class=\"titulo2\">Nome e Sobrenome do Responsável</a>";
		}
		if($NCaracteresO > "200"){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Observação menor que 200 caracteres";
		}
		# *******************  VERIFICAÇÕES GERAIS - FIM ******************** #


		# ***************  VERIFICAÇÕES ESPECÍFICAS - INICIO **************** #

		# Se for uma troca, haverá saída do material 2 no momento da movimentação de entrada (11) do material 1. Aqui ele checa se há estoque disponível no almoxarifado para esta saída #
		if($Movimentacao == 11 and !$Mens){
				# Abre a conexão com banco de dados #
				$db = Conexao();
				$sqlEstoque  = " SELECT AARMATQTDE ";
				$sqlEstoque .= "   FROM SFPC.TBARMAZENAMENTOMATERIAL ";
				$sqlEstoque .= "  WHERE CMATEPSEQU = $CodReduzMat2 ";
				$sqlEstoque .= "    AND CLOCMACODI = $Localizacao ";
				$resEstoque  = $db->query($sqlEstoque);
				if( db::isError($resEstoque) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlEstoque");
				}else{
						$LinhaEstoque    = $resEstoque->fetchRow();
						$Estoque = $LinhaEstoque[0];
						if($Estoque < $QuantMat2){ // Se o estoque for menor que a quantidade solicitada pelo Almoxarifado Secundário, exibe mensagem de erro
								$Mens       = 1;
								$Tipo       = 2;
								$Estoquevirgula = str_replace('.',',',$Estoque);
								$Mensagem   = "A Quantidade em Estoque do Material Solicitado ($Estoquevirgula) não permite proceder a movimentação</a>";
						}
				}
				$db->disconnect();
		}

		# Se Saída por Empréstimo, Saída por Troca ou Saída por Doação entre Almoxarifados #
		if($Movimentacao == 12 or $Movimentacao == 15 or $Movimentacao == 30){
				if($AlmoxSec == ''){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.AlmoxSec.focus();\" class=\"titulo2\">Almoxarifado de Destino</a>";
				}
		}

		# Se Saída por Troca entre Almoxarifados #
		if($Movimentacao == 15){
				# Verifica se foi especificado o código reduzido do material para troca #
				if(!$CodReduzMat2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.CodReduzMat2.focus();\" class=\"titulo2\">Código Reduzido do Material a ser Recebido</a>";
				}elseif($Material == $CodReduzMat2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.CodReduzMat2.focus();\" class=\"titulo2\">Material Diferente do Ofertado</a>";
				}else{
						if($AlmoxSec){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# Verifica se existe o material que se pretende pegar no outro almoxarifado
								$sqlPegar  = " SELECT A.AARMATQTDE ";
								$sqlPegar .= "   FROM SFPC.TBARMAZENAMENTOMATERIAL A, SFPC.TBLOCALIZACAOMATERIAL B ";
								$sqlPegar .= "  WHERE A.CMATEPSEQU = $CodReduzMat2 ";
								$sqlPegar .= "    AND A.CLOCMACODI = B.CLOCMACODI ";
								$sqlPegar .= "    AND B.CALMPOCODI = $AlmoxSec ";
								$resPegar  = $db->query($sqlPegar);
								if( db::isError($resPegar) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPegar");
								}else{
										$LinhaPegar      = $resPegar->fetchRow();
										$QtdPegarNoOutro = $LinhaPegar[0];
										if($QtdPegarNoOutro <= 0){
												if($Mens == 1){ $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.CodReduzMat2.focus();\" class=\"titulo2\">Material em estoque no Almoxarifado Destino</a>";
										}elseif($QtdPegarNoOutro < $QuantMat2){
												if($Mens == 1){ $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$QtdPegarNoOutroExibe = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdPegarNoOutro)));
												$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoConfirmar.CodReduzMat2.focus();\" class=\"titulo2\">Material com Estoque suficiente no Almoxarifado Destino para esta Troca</a>"; //($QtdPegarNoOutroExibe)
										}else{
												# Checa se os materiais são do mesmo tipo (Consumo / Permanente), se diferentes, não permite troca #
												$sqlTipMat  = "SELECT C.FGRUMSTIPM ";
												$sqlTipMat .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
												$sqlTipMat .= "       SFPC.TBGRUPOMATERIALSERVICO C ";
												$sqlTipMat .= " WHERE A.CMATEPSEQU = $Material ";
												$sqlTipMat .= "   AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
												$sqlTipMat .= "   AND B.CGRUMSCODI = C.CGRUMSCODI ";
												$resTipMat  = $db->query($sqlTipMat);
												if( db::isError($resTipMat) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTipMat");
												}else{
														$LinhaTipMat    = $resTipMat->fetchRow();
														$TipoMaterial   = $LinhaTipMat[0];
														$sqlTipMatSecu  = "SELECT C.FGRUMSTIPM ";
														$sqlTipMatSecu .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
														$sqlTipMatSecu .= "       SFPC.TBGRUPOMATERIALSERVICO C ";
														$sqlTipMatSecu .= " WHERE A.CMATEPSEQU = $CodReduzMat2 ";
														$sqlTipMatSecu .= "   AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
														$sqlTipMatSecu .= "   AND B.CGRUMSCODI = C.CGRUMSCODI ";
														$resTipMatSecu  = $db->query($sqlTipMatSecu);
														if(db::isError($resTipMatSecu)){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTipMatSecu");
														}else{
																$LinhaTipMatSecu  = $resTipMatSecu->fetchRow();
																$TipoMaterial2    = $LinhaTipMatSecu[0];
																if($TipoMaterial != $TipoMaterial2 ){
																		$Mensagem = "Não poderá ser executada a Troca de Materiais com Tipos Diferentes (Consumo/Permanente)";
																		$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
																		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
																}
														}
												}
										}
								}
								$db->disconnect();
						}
				}
		}

		# Se Devolução Interna ou Acerto de Requisição #
		if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20){
				# Abre a conexão com banco de dados #
				$db = Conexao();
				# Verifica as quantidades do material na requisição #
				$sql  = " SELECT AITEMRQTAT, AITEMRQTSO ";
				$sql .= "   FROM SFPC.TBITEMREQUISICAO ";
				$sql .= "  WHERE CMATEPSEQU = $Material ";
				$sql .= "    AND CREQMASEQU = $SeqRequisicao ";
				$res  = $db->query($sql);
				if( db::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha         = $res->fetchRow();
						$QtdAtendida   = $Linha[0];
						$QtdSolicitada = $Linha[1];
				}
				# Entrada para Acerto de Requisição #
				if($Movimentacao == 19){
						if($QtdMovimentada > $QtdAtendida){
								if($Mens == 1){ $Mensagem .= ", "; }
								$Mens       = 1;
								$Tipo       = 2;
								$Virgula    = 2;
								$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdAtendida));
								$Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual a Quantidade Atendida ($QtdVirgula)</a>";
						}
				}
				# Saída para Acerto de Requisição #
				if($Movimentacao == 20){
						# Verifica se a Requisição foi atendida totalmente #
						if($QtdAtendida == $QtdSolicitada){
								if($Mens == 1){ $Mensagem .= ", "; }
								$Mens       = 1;
								$Tipo       = 2;
								$Virgula    = 2;
								$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdSolicitada));
								$Mensagem   = "Esta Movimentação de Saída não poderá ser executada, pois, a Quantidade Solicitada foi Atendida Totalmente";
						}else{
								# Verifica se a Qtd Movimentada + Atendida não ultrapassa a Solicitada #
								if( ( $QtdAtendida + $QtdMovimentada ) > $QtdSolicitada  ){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens       = 1;
										$Tipo       = 2;
										$Virgula    = 2;
										$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdSolicitada));
										$Mensagem  .= "Quantidade Movimentada mais Quantidade Atendida menor ou igual a Quantidade Solicitada ($QtdVirgula)";
								}
						}
				}
				# Entrada por Devolução Interna #
				if($Movimentacao == 2){
						# Verifica a se já houve Movimentações e pega a quantidade do material na requisição #
						$sql  = " SELECT AMOVMAQTDM, CTIPMVCODI ";
						$sql .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sql .= "  WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoMovimentacao ";
						$sql .= "    AND CTIPMVCODI IN (2,21,22) AND CMATEPSEQU = $Material ";
						$sql .= "    AND CREQMASEQU = $SeqRequisicao ";
						$sql .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
						$res  = $db->query($sql);
						if( db::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								while($Linha = $res->fetchRow()){
										if( ($Linha[1] == 2) or ($Linha[1] == 21) ){
												$QtdMovimentacao = $QtdMovimentacao - $Linha[0];
										}else{
												$QtdMovimentacao = $QtdMovimentacao + $Linha[0];
										}
								}
								if($QtdMovimentacao > 0){
										if($QtdMovimentacao == $QtdAtendida ){
												if($Mens == 1){ $Mensagem .= ", "; }
												$Mens       = 1;
												$Tipo       = 2;
												$Virgula    = 2;
												$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdMovimentacao));
												$QtdReqVirg = str_replace('.',',',$QtdAtendida);
												$Mensagem   = "Movimentação Cancelada! A Quantidade Atendida é de $QtdReqVirg e já foram devolvidos $QtdVirgula deste Material";
										}elseif( $QtdMovimentada > ($QtdAtendida - $QtdMovimentacao) ){
												if($Mens == 1){ $Mensagem .= ", "; }
												$Mens       = 1;
												$Tipo       = 2;
												$Virgula    = 2;
												$QtdVirgula = str_replace('.',',',sprintf("%01.2f",($QtdAtendida - $QtdMovimentacao)));
												$Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual a Quantidade Atendida da Requisição ($QtdVirgula)</a>";
										}
								}else{
										if($QtdMovimentada > $QtdAtendida ){
												if($Mens == 1){ $Mensagem .= ", "; }
												$Mens       = 1;
												$Tipo       = 2;
												$Virgula    = 2;
												$QtdVirgula = str_replace('.',',',$QtdAtendida);
												$Mensagem  .= "<a href=\"javascript:document.CadMovimentacaoConfirmar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada menor ou igual a Quantidade Atendida da Requisição ($QtdVirgula)</a>";
										}
								}
						}
				}
				$db->disconnect();
		}
		# ***************  VERIFICAÇÕES ESPECÍFICAS - FIM **************** #



		# **************  INCLUSÕES E ATUALIZAÇÕES - INÍCIO ************** #
		if($Mens == 0){
				# Evita duplicidade de gravação teclando F5 com checagem de tempo #
				$DataGravacao = date("Y-m-d H:i:s");
				if($_SESSION['DataGravacao']){
						$DataGravacaoSession = str_replace(":","-",str_replace(" ","-",$_SESSION['DataGravacao']));
						$DGS = split("-",$DataGravacaoSession);
						$MomentoSession = (86400*$DGS[2]) + (3600*$DGS[3]) + (60*$DGS[4]) + ($DGS[5]); // Dia, Hora, Minuto, Segundo --> Segundos
						$DataGravacaoVariavel = str_replace(":","-",str_replace(" ","-",$DataGravacao));
						$DGV = split("-",$DataGravacaoVariavel);
						$MomentoVariavel = (86400*$DGV[2]) + (3600*$DGV[3]) + (60*$DGV[4]) + ($DGV[5]); // Dia, Hora, Minuto, Segundo --> Segundos
				}
				if(     ($_SESSION['Almoxarifado']        == $Almoxarifado)
						AND ($_SESSION['AnoMovimentacao']     == $AnoMovimentacao)
						AND ($_SESSION['Movimentacao']        == $Movimentacao)
						AND ($_SESSION['Material']            == $Material)
						AND ($_SESSION['QtdMovimentada']      == $QtdMovimentada)
						AND ($_SESSION['GrupoEmp']            == $GrupoEmp)
						AND ($_SESSION['Usuario']             == $Usuario)
						AND ($_SESSION['DataGravacao'])
						AND ($MomentoVariavel >= $MomentoSession)
						AND ($MomentoVariavel < $MomentoSession + 120)) { // Não permite alterações no banco, se uma movimetação equivalente tiver sido realizada até 2 minutos antes
								# Envia a Mensagem de Sucesso/Duplicidade #
								$Mensagem = urlencode("Movimentação Incluída com Sucesso ou Bloqueio de Tentativa de Duplicidade de Movimentação");
								$Url = "CadMovimentacaoIncluir.php?Tipo=1&Mens=1&Mensagem=$Mensagem";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
				}else{
						# Abre a conexão com banco de dados #
						$db = Conexao();
						# Pega o Código Sequencial do Movimento no Ano para o Almoxarifado Atual #
						$sqlMovAnoSequ  = "SELECT MAX(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
						$sqlMovAnoSequ .= " WHERE CALMPOCODI = $Almoxarifado AND AMOVMAANOM = $AnoMovimentacao ";
						$resMovAnoSequ  = $db->query($sqlMovAnoSequ);
						if(db::isError($resMovAnoSequ)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovAnoSequ");
						}else{
								$Linha      = $resMovAnoSequ->fetchRow();
								$MovAnoSequ = $Linha[0] + 1;
						}
						$db->disconnect();

						if($Movimentacao != 2 and $Movimentacao != 19 and $Movimentacao != 20 and $Movimentacao != 21 and $Movimentacao != 22){
								$SeqRequisicao = "NULL";
						}

						# ********* INÍCIO DO BLOCO DE EXECUÇÃO DAS SITUAÇÕES DE MOVIMENTO ********* #

						# CASO SEJA DEVOLUÇÃO INTERNA (2) ou Acerto de Requisição (19 e 20) - Gera Custo só o tipo 2 #
						if($MetodoDeChamada == "POST" and ($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20)){
              # Abre a conexão com banco de dados #
							if($Movimentacao == 2 and $EstoqueVirtual == 'S'){
								$db = Conexao();

								# pega a nota fiscal virtual
								$sqlMov  = "
									SELECT aentnfanoe, centnfcodi, calmpocodi
									FROM SFPC.tbmovimentacaomaterial
									WHERE
										CREQMASEQU = $SeqRequisicao AND
										CMATEPSEQU = $Material
								";
								$resMov = $db->query($sqlMov);

								if( db::isError($resMov) ){
										$db->disconnect();
										EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlMov, $resMov);
										exit(0);
								}
								$Linha = $resMov->fetchRow();
								$NFAno = $Linha[0];
								if( ($Linha[0]=='') or ($Linha[0]==null) ){
										$db->disconnect();
										EmailErro($NomePrograma, __FILE__, __LINE__, "Nenhuma nota fiscal virtual foi encontrada. Porém esta requisição baixada foi marcada como atendida por estoque virtual. Verificar porque isto está ocorrendo.\n\nSQL: ".$sqlMov);
										exit(0);
								}
								$NFCod = $Linha[1];
								$NFAlmoxarifado = $Linha[2];
								$NFMovAnoSequ = $MovAnoSequ +1; // sequencial do ano

								# pega itens da nota fiscal
								$sql  = "
									select cmatepsequ
									from sfpc.tbitemnotafiscal
									where
										calmpocodi = ".$NFAlmoxarifado."
										and centnfcodi = ".$NFCod."
										and aentnfanoe = ".$NFAno."
								";
								$res = $db->query($sql);

								if( db::isError($res) ){
										$db->disconnect();
										EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
										exit(0);
								}
								$Rows = $res->numRows();

								# Classes de cancelamento de movimentações
								require_once("./ClaCancelamentoNota.php");
								Banco::guardarSessao($db); // guardar sessão para uso nas classes

								# para cada item da nota fiscal, verificar cancelamento de movimentações
								//for($k=0;$k<$Rows;$k++){

									$Linha = $res->fetchRow();
									//$NFMaterial = $Linha[0];

									$NFMaterial = $Material;

									//verificar tambem para os outros materiais da NF
									$movimentacoes = new MovimentacoesCancelamentoNota($NFAlmoxarifado,$NFAno,$NFCod,$NFMaterial);
									$movimentacoes->ocultarCanceladasNotaFiscal();

									# ocultar movimentação de criação da requisição (a que se deseja cancelar, para não contar como movimentação a ser cancelada)
									$movArray = $movimentacoes->getMovimentacoes();
									foreach($movArray as $movimentacao){
										if(
											(!is_null($movimentacao->getRequisicao()))
											and ($movimentacao->getTipo() == 4)
											and ($movimentacao->getRequisicao()->getSequencial() == $SeqRequisicao )
										){
											$movimentacao->setOcultar(true);
										}
									}


									/*### teste da checagem das movimentações ###
									echo "Teste de verificação de movimentações não canceladas.<br/>";
									echo "A verificação passa apenas se apenas houver movimentações ocultos.<br/><br/>";
									foreach($movArray as $movimentacao){
										echo "[";
										echo "cod.mov.: ".$movimentacao->getCodigo()."";
										echo "| tipo: ".$movimentacao->getTipo()." ";
										if($movimentacao->getOcultar()){
											echo "| OCULTADO ";
										}else{
										}
										echo "]<br/>";
									}
									exit;
									###########################################*/


									if($movimentacoes->notaFiscalAntesDoInventario()){
										$Mensagem = "Impossível adicionar movimentação para esta requisição, pois ela foi atendida por uma Nota Fiscal virtual que foi criada antes do último inventário. Como a Nota Fiscal virtual não pode ser cancelada esta requisição vinculada também não";
										$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
									}else if($movimentacoes->getNoMovimentacoesNaoOcultas()!=0){
										//verificar se há movimentações não canceladas
										$Mens       = 1;
										$Tipo       = 2;
										$Virgula    = 2;

										$UrlRelatorio = "RelAuxilioCancelamentoNotaPdf.php?Almoxarifado=".$NFAlmoxarifado."&Material=".$NFMaterial."&NotaFiscal=".$NFCod."&AnoNota=".$NFAno."&Procedimento=M&".mktime();
										$MensagemRelatorio = " Utilize o relatório de <a href=\"$UrlRelatorio\">Auxílio para Cancelamento de Nota Fiscal</a> para identificar estas movimentações";
										$Mensagem = "O Cancelamento não pode ser feito. Esta requisição de material foi atendida por uma Nota Fiscal virtual em que o material código ".$NFMaterial." possui movimentações não canceladas após a criação da Nota Fiscal. ".$MensagemRelatorio;
										//$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
										//if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										//header("location: ".$Url);
										//exit;
									}
								//}

								$db->disconnect();
							}

							if($Mens==0){
								$db = Conexao();
	              # INICIANDO TRANSAÇÃO #
	              $db->query("BEGIN TRANSACTION");
	              $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
	              if($Mens==0){
	                # INSERINDO MOVIMENTO #

									$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
									$sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
									$sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
									$sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
									$sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
									$sqlInsert .= ") VALUES ( ";
									$sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
									$sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
									$sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
									$sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
									$sqlInsert .= ");";
									$resInsert  = $db->query($sqlInsert);
	                if( db::isError($resInsert) ){
	                    $db->query("ROLLBACK");
	                    $db->query("END TRANSACTION");
	                    $db->disconnect();
	                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
	                }else{
										if($Movimentacao == 2 and $EstoqueVirtual == 'S'){

											# Pega o Máximo código do Movimento do Material do Tipo - SAÍDA POR ALTERAÇÃO DE NOTA FISCAL #
											# para calcular o novo código
											$sqltipo  = "
												SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL
												WHERE CALMPOCODI = $NFAlmoxarifado AND AMOVMAANOM = ".date("Y")."
													AND CTIPMVCODI = 8
											"; // Apresentar só as movimentações ativas
											$restipo  = $db->query($sqltipo);
											if( db::isError($restipo) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqltipo, $restipo);
													exit(0);
											}

											$LinhaTipo     = $restipo->fetchRow();

											$MovimentoTipo = $LinhaTipo [0] + 1;

											# Verifica o numero de itens da nota fiscal

											$sql  = "
												SELECT COUNT(*)
												FROM SFPC.TBITEMNOTAFISCAL
												WHERE
													CALMPOCODI = $NFAlmoxarifado
													AND AENTNFANOE = $NFAno
													AND CENTNFCODI = $NFCod
											";
											$res  = $db->query($sql);
											if( db::isError($res) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
													exit(0);
											}

											$linha = $res->fetchRow();
											$NFNoItens = $linha[0];


											if($NFNoItens<2){ // apenas cancelar a nota se nenhum item resta
												# Atualiza a flag de cancelamento da nota fiscal do item #
												$sqlnf  = "
													UPDATE SFPC.TBENTRADANOTAFISCAL
														SET FENTNFCANC = 'S'
														WHERE CALMPOCODI = $NFAlmoxarifado
															AND AENTNFANOE = $NFAno
															AND CENTNFCODI = $NFCod
												";
												$resnf  = $db->query($sqlnf);
												if( db::isError($resnf) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlnf, $resnf);
														exit(0);
												}
											}else{ // há mais de 1 item na nota fiscal, portanto, apenas deve deletar o item da nota
												# Deleta item de nota fiscal
												$sqlnf  = "
													DELETE
													FROM SFPC.TBITEMNOTAFISCAL
													WHERE
														CALMPOCODI = $NFAlmoxarifado
														AND AENTNFANOE = $NFAno
														AND CENTNFCODI = $NFCod
														AND CMATEPSEQU = $Material

												";
												$resnf  = $db->query($sqlnf);
												if( db::isError($resnf) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlnf, $resnf);
														exit(0);
												}
											}

											#Pegando valor e quantidade do estoque para recalcular o valor médio
											$sql  = "
												select aarmatqtde, varmatumed, varmatultc
												from sfpc.tbarmazenamentomaterial
												where
													clocmacodi = $Localizacao
													and cmatepsequ = $Material
											";
											$res  = $db->query($sql);
											if( db::isError($res) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
													exit(0);
											}
											$linha = $res->fetchRow();

											$itemEstoqueQtde = $linha[0];
											$itemEstoqueValor = $linha[1];
											$itemEstoqueValorUnitario = $linha[2];

											# Retorna o valor médio do item na nota fiscal

											$sql  = "
												SELECT VITENFUNIT
												FROM SFPC.TBITEMNOTAFISCAL
												WHERE
													CALMPOCODI = $NFAlmoxarifado
													AND AENTNFANOE = $NFAno
													AND CENTNFCODI = $NFCod
													AND CMATEPSEQU = $Material
											";
											$res  = $db->query($sql);
											if( db::isError($res) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
													exit(0);
											}

											$linha = $res->fetchRow();
											$itemNFValor = $linha[0];






											# Resgata o ultimo valor do item na ultima nota fiscal, antes da nota fiscal sendo cancelada #
											$sqlultvalnot  = "
												SELECT DISTINCT(VITENFUNIT) FROM SFPC.TBITEMNOTAFISCAL
												WHERE
													CMATEPSEQU = $Material AND CALMPOCODI = $NFAlmoxarifado
													AND AENTNFANOE = $NFAno
													AND CENTNFCODI = (
														SELECT MAX(A.CENTNFCODI)
														FROM SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B
														WHERE
															A.CENTNFCODI <> $NFCod
															AND A.CMATEPSEQU = $Material
															AND A.CALMPOCODI = $NFAlmoxarifado AND A.AENTNFANOE = $NFAno
															AND A.CALMPOCODI = B.CALMPOCODI
															AND A.AENTNFANOE = B.AENTNFANOE
															AND A.CENTNFCODI = B.CENTNFCODI
															AND ( B.FENTNFCANC = 'N' or B.FENTNFCANC IS NULL )
													)
											";

											/*$db->query("ROLLBACK");
											$db->query("END TRANSACTION");
											$db->disconnect();
											echo "[".$NFCod."]";
											exit;*/


											$res  = $db->query($sqlultvalnot);

											if( db::isError($res) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $res);
													exit(0);
											}

											$Linha   = $res->fetchRow();
											$ValorUnitarioUlt = $Linha[0];
											if( is_null($ValorUnitarioUlt) ){
													$ValorUnitarioUlt = 0;
											}



											/*$db->query("ROLLBACK");
											$db->query("END TRANSACTION");
											$db->disconnect();
											echo "$itemEstoqueQtde <br/>";
											$itemEstoqueQtde += $QtdMovimentada;
											echo "$itemEstoqueQtde";
											exit;*/
											if($itemEstoqueQtde!=0){

												$itemEstoqueQtdeMenosReq = $itemEstoqueQtde+$QtdMovimentada; //o que está no almoxarifado mais o cancelamento da requisição

												$itemNFQtde = $QtdMovimentada;

												$itemEstoqueValorTotal = $itemEstoqueQtdeMenosReq * $itemEstoqueValor;
												$itemNFValorTotal = $itemNFQtde * $itemNFValor;

												$valorMedioNovo = ($itemEstoqueValorTotal - $itemNFValorTotal) / ($itemEstoqueQtdeMenosReq - $itemNFQtde);

												$valorMedioNovo = round($valorMedioNovo,4);

												if($ValorUnitarioUlt == 0){
													$ValorUnitarioUlt = $valorMedioNovo;
												}
											} else{
												$valorMedioNovo = $itemEstoqueValor;
												$ValorUnitarioUlt = $itemEstoqueValorUnitario;
											}

											$sql  = "
												INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL (
													CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI,
													CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM,
													VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT,
													CMOVMACODT, AMOVMAMATR, NMOVMARESP, AENTNFANOE, CENTNFCODI
												) VALUES (
													$NFAlmoxarifado, ".date("Y").", $NFMovAnoSequ, '".date('Y-m-d')."',
													8, NULL, $Material, $QtdMovimentada,
													$ValorUnitarioUlt, $valorMedioNovo, ".$GrupoEmp.", ".$Usuario.", '".date("Y-m-d H:i:s")."',
													$MovimentoTipo, NULL, NULL, $NFAno, $NFCod
												)
											";
											$resInsert  = $db->query($sql);
											if( db::isError($resInsert) ){
													$db->query("ROLLBACK");
													$db->query("END TRANSACTION");
													$db->disconnect();
													//print_r($resInsert);
													EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sql, $resInsert);
													exit(0);
											}

											# Calculando valor médio anterior. (Estoque 0 não é necessário recálculo, pois o valor médio do estoque ficará da próxima nota fiscal)
											if($itemEstoqueQtde!=0){

												# Cria movimentação ALTERAÇÃO DE NOTA FISCAL cancelando o item da nota fiscal virtual

												# Altera o valor médio para o novo #
												$sqlUpdate  = "
													UPDATE SFPC.TBARMAZENAMENTOMATERIAL
													SET
														CGREMPCODI = $GrupoEmp,
														CUSUPOCODI = $Usuario,
														TARMATULAT = '$DataGravacao',
														VARMATUMED = $valorMedioNovo,
														VARMATULTC = $ValorUnitarioUlt
													WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao
												";
												$resUpdate  = $db->query($sqlUpdate);

												if( db::isError($resUpdate) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														EmailErroSQL($NomePrograma, __FILE__, __LINE__, "Erro de SQL", $sqlUpdate, $resUpdate);
														exit(0);
												}

											}


										}else{ //movimentacao de requisicao de estoque virtual será anulado pela movimentação de Nota Fiscal. Portanto, não necessita atualizar o SFPC.TBARMAZENAMENTOMATERIAL para requisições atendidas por nota fiscal virtual

											# Altera a quantidade de acordo com a Movimentação #
											$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
											$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";

											/*
												if($Movimentacao == 2 && $EstoqueVirtual == 'S'){
												$sqlUpdate .= "       AARMATVIRT = $QtdFinalVirtual, ";
											} else {
												$sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
											}*/

											$sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
											$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
											$sqlUpdate .= "       CUSUPOCODI = $Usuario, TARMATULAT = '$DataGravacao' ";
											$sqlUpdate .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
											$resUpdate  = $db->query($sqlUpdate);
										}
	                  if( (!($Movimentacao == 2 and $EstoqueVirtual == 'S')) and db::isError($resUpdate) ){
												//só a sql do SFPC.TBARMAZENAMENTOMATERIAL acima deve entrar aqui
	                      $db->query("ROLLBACK");
	                      $db->query("END TRANSACTION");
	                      $db->disconnect();
	                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
	                  }else{
	                    # Alterando a quantidade antendida na requisição #
	                    $sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
	                    # Se a quantidade movimentada é maior  #
	                    if($Movimentacao == 20){ // Saída Para Acerto de Requisição
	                        $sql .= "   SET AITEMRQTAT = AITEMRQTAT + $QtdMovimentada, ";
	                    }else{                     // Entrada Para Acerto de Requisição e Devolução Interna
	                        $sql .= "   SET AITEMRQTAT = AITEMRQTAT - $QtdMovimentada, ";
	                    }
	                    $sql .= "       CGREMPCODI = $GrupoEmp, ";
	                    $sql .= "       CUSUPOCODI = $Usuario, TITEMRULAT = '$DataGravacao' ";
	                    $sql .= " WHERE CMATEPSEQU = $Material AND CREQMASEQU = $SeqRequisicao ";
	                    $res  = $db->query($sql);
	                    if(db::isError($res)){
	                        $db->query("ROLLBACK");
	                        $db->query("END TRANSACTION");
	                        $db->disconnect();
	                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	                    }else{
	                        # Para acerto de requisição, setar o tipo de atendimento, total ou parcial #
	                        if($Movimentacao == 19 or $Movimentacao == 20){
	                            # Verifica as quantidades atendida e solicitada #
	                            $sqlqtd  = " SELECT AITEMRQTAT, AITEMRQTSO FROM SFPC.TBITEMREQUISICAO ";
	                            $sqlqtd .= "  WHERE CREQMASEQU = $SeqRequisicao AND CMATEPSEQU = $Material ";
	                            $resqtd  = $db->query($sqlqtd);
	                            if(db::isError($resqtd)){
	                                $db->query("ROLLBACK");
	                                $db->query("END TRANSACTION");
	                                $db->disconnect();
	                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlqtd");
	                            }else{
	                                $Linha  = $resqtd->fetchRow();
	                                $QtdAte = $Linha[0];
	                                $QtdSol = $Linha[1];
	                                if($Situacao == 3 and $QtdAte < $QtdSol){ //Situacao = 3 - Atendimento Total
	                                    # Modifica a situação da requisição para Atendimento Parcial #
	                                    $sqlsit  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO( ";
	                                    $sqlsit .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, ";
	                                    $sqlsit .= "CUSUPOCODI, ESITREMOTI, TSITREULAT ";
	                                    $sqlsit .= ") VALUES ( ";
	                                    $sqlsit .= "$SeqRequisicao, 4, '".$DataGravacao."', $GrupoEmp, ";
	                                    $sqlsit .= "$Usuario, NULL, '".$DataGravacao."' ";
	                                    $sqlsit .= ")";
	                                    $ressit  = $db->query($sqlsit);
	                                    if( db::isError($ressit) ){
	                                        $RollBack = 1;
	                                        $db->query("ROLLBACK");
	                                        $db->query("END TRANSACTION");
	                                        $db->disconnect();
	                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsit");
	                                    }
	                                }elseif($Situacao == 4 and $QtdAte == $QtdSol){ //Situacao = 4 - Atendimento Parcial
	                                    # Modifica a situação da requisição para Atendimento Total #
	                                    $sqlsit  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO( ";
	                                    $sqlsit .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, ";
	                                    $sqlsit .= "CUSUPOCODI, ESITREMOTI, TSITREULAT ";
	                                    $sqlsit .= ") VALUES ( ";
	                                    $sqlsit .= "$SeqRequisicao, 3 , '".$DataGravacao."', $GrupoEmp, ";
	                                    $sqlsit .= "$Usuario, NULL, '".$DataGravacao."' ";
	                                    $sqlsit .= ")";
	                                    $ressit  = $db->query($sqlsit);
	                                    if(db::isError($ressit)){
	                                        $RollBack = 1;
	                                        $db->query("ROLLBACK");
	                                        $db->query("END TRANSACTION");
	                                        $db->disconnect();
	                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsit");
	                                    }
	                                }
	                            }
	                            if($RollBack != 1){
	                                # Fechando a transação e a conexão #
	                                $db->query("COMMIT");
	                                GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
	                                $db->query("END TRANSACTION");
	                                $db->disconnect();
	                                # EXIBINDO MENSAGEM #
	                                $Mensagem = "Movimentação Inserida com Sucesso";
	                                $Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
	                                if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	                                header("location: ".$Url);
	                                exit;
	                            }
	                        # Para devolução interna, fazer movimentação de custo no Oracle #
	                      }elseif($Movimentacao == 2){
	                        # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
	                        $dbora = ConexaoOracle();

	                        # Evita que Rollback não funcione #
	                        $dbora->autoCommit(false);

	                        # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
	                        $dbora->query("BEGIN TRANSACTION");

	                        $TimeStamp            = $DataGravacao;
	                        $DiaBaixa             = date("d");
	                        $MesBaixa             = date("m");
	                        $AnoBaixa             = date("Y");

	                        //Obtendo os Sub-elementos de despesa
	                        $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
	                        $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
	                        $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
	                        $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
	                        $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
	                        $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
	                        $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
	                        $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
	                        $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
	                        $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
	                        $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
	                        $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
	                        $sql .= "       MAT.CMATEPSEQU = $Material ";

	                        $res  = $db->query($sql);

	                        if( db::isError($res) ){
	                            $RollBack = 1;
	                            $db->query("ROLLBACK");
	                            $db->query("END TRANSACTION");
	                            $db->disconnect();
	                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	                        } else {

	                          #Preparando os dados para o lançamento de custo
	                          $SubElementosDespesa = array();
	                          $ValoresSubelementos = array();

	                          #Preparando os dados para o lançamento de contábil
	                          $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
	                          $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

	                          $Linha = $res->fetchRow();
	                          $CGRUSEELE1 = $Linha[0];
	                          $CGRUSEELE2 = $Linha[1];
	                          $CGRUSEELE3 = $Linha[2];
	                          $CGRUSEELE4 = $Linha[3];
	                          $CGRUSESUBE = $Linha[4];

	                          //TESTE
	                          if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
	                            $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
	                            $ValorSubElemento = $QtdMovimentada*$Valor;

	                            if(!in_array($Subelemento, $SubElementosDespesa)){
	                              $indice = count($SubElementosDespesa);
	                              $SubElementosDespesa[$indice] = $Subelemento;
	                              $ValoresSubelementos[$indice] = $ValorSubElemento;
	                            } else {
	                              $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
	                              $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
	                            }
	                          } else {
	                            # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
	                            $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
	                            $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
	                            if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	                            RedirecionaPost($Url);
	                            exit;
	                          }
	                          //FIM TESTE

	                          #Preparando os dados para o lançamento contábil

	                          //TESTE 2
	                          $ValorContabilTESTE = $QtdMovimentada*$Valor;
	                          $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

	                          if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
	                            $indice = count($EspecificacoesContabeis);
	                            $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
	                            $ValoresContabeis[$indice] = $ValorContabilTESTE;
	                          } else {
	                            $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
	                            $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
	                          }
	                          //FIM TESTE 2

	                          //ORIGINAL
	                          #Preparando os dados para o lançamento contábil
	                          // $ValorContabil = $QtdMovimentada*$Valor;
	                          // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
	                          //ORIGINAL

	                          $ConfirmarInclusao = true;

	                          //ORIGINAL
	                          // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
	                           // $Movimentacao, $TipoMaterialTESTE,
	                           // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
	                           // $Matricula, $Responsavel,
	                           // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
	                           // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
	                           // $SubElementosDespesa, $ValoresSubelementos);
	                          //ORIGINAL

	                          //TESTE 2
	                          // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
	                            // $Movimentacao, $TipoMaterialTESTE,
	                            // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
	                            // $Matricula, $Responsavel,
	                            // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
	                            // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
	                            // $SubElementosDespesa, $ValoresSubelementos,
	                            // $EspecificacoesContabeis, $ValoresContabeis);
	                          //FIM TESTE2

	                          //TESTE 3
	                          GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
	                                 $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
	                                 $EspecificacoesContabeis, $ValoresContabeis,
	                                 $SubElementosDespesa, $ValoresSubelementos,
	                                 $Matricula, $Responsavel,
	                                 $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
	                                 $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
	                          //FIM TESTE 3

	                          exit;
	                        }
	                      }
	                    }
	                  }
	                }
	              }
              }


						# CASO SEJA ENTRADA POR EMPRÉSTIMO ENTRE ÓRGÃOS - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 6){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              # VERIFICANDO Se O Material Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
              $sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
              $sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
              $sqlChkExist .= "   FOR UPDATE ";
              $resChkExist  = $db->query($sqlChkExist);
              if( db::isError($resChkExist) ){
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
              }else{
                $QtdChkExist = $resChkExist->numRows();
                $LinhaChkExist = $resChkExist->fetchRow();
                if($QtdChkExist == 0 or $LinhaChkExist[0] == 0){
                    $ValorMat1Arma = $Valor;            // Se Não Existe, ou se o valor estiver zerado, o valor médio na tabela de movimentação e na de armazenamento recebe o valor do Almoxarifado Secundário
                }else{
                    $ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
                }
                if(!ChecaNaoExistenciaCanc($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                    $Mens      = 1;
                    $Tipo      = 2;
                    $Mensagem  = "O Material não pode mais ser recebido, pois a Movimentação de Saída foi Cancelada pelo outro Almoxarifado";
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                }else{
                  if(!ChecaNaoExistenciaFlag($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                      $Mens      = 1;
                      $Tipo      = 2;
                      $Mensagem  = "O Material já foi recebido, provavelmente por outro usuário usando o sistema simultaneamente";
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                  }else{
                    # INSERINDO MOVIMENTO #
                    $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                    $sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                    $sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                    $sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                    $sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP,  ";
                    $sqlInsert .= " CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";             // ALMOX SECUNDÁRIO
                    $sqlInsert .= ") VALUES ( ";
                    $sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                    $sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                    $sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
                    $sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
                    $sqlInsert .= " $AlmoxSec, $AnoAtualizar, $SeqMovimentacao, '$Observacao' ";
                    $sqlInsert .= ")";
                    $resInsert  = $db->query($sqlInsert);
                    if( db::isError($resInsert) ){
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                    }else{
                      # ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM - 12 #
                      $sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S', AMOVMAANO1 = $AnoAtualizar, CMOVMACOD1 = $MovAnoSequ ";
                      $sqlMovOri .= " WHERE CALMPOCODI = $AlmoxSec ";
                      $sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
                      $sqlMovOri .= "   AND CMOVMACODI = $SeqMovimentacao";
                      $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $resMovOri  = $db->query($sqlMovOri);
                      if( db::isError($resMovOri) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
                      }else{
                        # Descobrindo o sequencial da movimentação de origem #
                        $sqlmovseq  = "SELECT CMOVMACODT FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                        $sqlmovseq .= " WHERE CALMPOCODI = $AlmoxSec ";
                        $sqlmovseq .= "   AND AMOVMAANOM = $AnoAtualizar ";
                        $sqlmovseq .= "   AND CMOVMACODI = $SeqMovimentacao ";
                        $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                        $resmovseq  = $db->query($sqlmovseq);
                        if( db::isError($resmovseq) ){
                            $db->query("ROLLBACK");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovseq");
                        }else{
                          $LinhaSeqMov  = $resmovseq->fetchRow();
                          $MovNumeroSec = $LinhaSeqMov[0];
                          if($QtdChkExist == 0){ // Se não há o material 1 no estoque do almoxarifado, Insere, se há, Atualiza a quantidade
                              # INSERINDO ESTOQUE #
                              $sqlInsert  = " INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                              $sqlInsert .= " CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
                              $sqlInsert .= " AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
                              $sqlInsert .= " AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
                              $sqlInsert .= " CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                              $sqlInsert .= " ) VALUES ( ";
                              $sqlInsert .= " $Material, $Localizacao, $QtdMovimentada, NULL, "; //Adicionando para Estoque real
                              $sqlInsert .= " NULL, $QtdMovimentada, NULL, NULL, ";
                              $sqlInsert .= " NULL, NULL, $Valor, $Valor, ";
                              $sqlInsert .= " $GrupoEmp, $Usuario, '$DataGravacao' )";
                              $resInsert  = $db->query($sqlInsert);
                              if( db::isError($resInsert) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                              }
                          }else{
                              # ATUALIZANDO ESTOQUE #
                              $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET VARMATUMED = $ValorMat1Arma, VARMATULTC = $ValorMat1Arma, AARMATQTDE = AARMATQTDE + $QtdMovimentada, AARMATESTR = AARMATESTR + $QtdMovimentada ";
                              $sqlUpdate .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                              $resUpdate  = $db->query($sqlUpdate);
                              if( db::isError($resUpdate) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                              }
                          }

                          if($RollBack != 1){
                            # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                            $dbora = ConexaoOracle();

                            # Evita que Rollback não funcione #
                            $dbora->autoCommit(false);
                            # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                            $dbora->query("BEGIN TRANSACTION");

                            $TimeStamp                   = $DataGravacao;
                            $DiaBaixa                    = date("d");
                            $MesBaixa                    = date("m");
                            $AnoBaixa                    = date("Y");

                            //Obtendo os Sub-elementos de despesa
                            $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                            $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                            $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                            $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                            $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                            $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                            $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                            $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                            $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                            $sql .= "       MAT.CMATEPSEQU = $Material ";

                            $res  = $db->query($sql);

                            if( db::isError($res) ){
                                $RollBack = 1;
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            } else {
                              //Preparando parâmetros para o lançamento de custo
                              $SubElementosDespesa = array();
                              $ValoresSubelementosSaida = array();
                              $ValoresSubelementosEntrada = array();

                              #Preparando os dados para o lançamento de contábil
                              $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                              $ValoresContabeisSaida = array();  //valores contábeis de saída conforme o tipo do material (Consumo ou Permanente)
                              $ValoresContabeisEntrada = array();  //valores contábeis de entrada conforme o tipo do material (Consumo ou Permanente)

                              $Linha = $res->fetchRow();
                              $CGRUSEELE1 = $Linha[0];
                              $CGRUSEELE2 = $Linha[1];
                              $CGRUSEELE3 = $Linha[2];
                              $CGRUSEELE4 = $Linha[3];
                              $CGRUSESUBE = $Linha[4];

                              //TESTE
                              if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                                $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                                $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                                $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                                if(!in_array($Subelemento, $SubElementosDespesa)){
                                  $indice = count($SubElementosDespesa);
                                  $SubElementosDespesa[$indice] = $Subelemento;
                                  $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                                } else {
                                  $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                  $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                                }
                              } else {
                                # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                                $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                                $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                                if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                RedirecionaPost($Url);
                                exit;
                              }
                              //FIM TESTE

                              //ORIGINAL
                              // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                              // $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                              // $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                              // $indice = count($SubElementosDespesa);
                              // if(!in_array($Subelemento, $SubElementosDespesa)){
                                // $SubElementosDespesa[$indice] = $Subelemento;

                                // $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                              // } else {
                                // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.

                                // $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                              // }
                              //ORIGINAL


                              //INICIO TESTE

                              //Preparando parâmetros para o lançamento contábil
                              // $ValorContabilSaida = $QtdMovimentada*$Valor;
                              // $ValorContabilSaida = sprintf("%01.2f",round($ValorContabilSaida,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim

                              // $ValorContabilEntrada = $QtdMovimentada*$ValorMat1Arma;
                              // $ValorContabilEntrada = sprintf("%01.2f",round($ValorContabilEntrada,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim

                              //TESTE 2
                              $ValorContabilTESTESaida = $QtdMovimentada*$Valor;
                              $ValorContabilTESTESaida = sprintf("%01.2f",round($ValorContabilTESTESaida,2));

                              $ValorContabilTESTEEntrada = $QtdMovimentada*$ValorMat1Arma;
                              $ValorContabilTESTEEntrada = sprintf("%01.2f",round($ValorContabilTESTEEntrada,2));

                              if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                                $indice = count($EspecificacoesContabeis);
                                $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                                $ValoresContabeisSaida[$indice] = $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indice] = $ValorContabilTESTEEntrada;
                              } else {
                                $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                                $ValoresContabeisSaida[$indExist] = $ValoresContabeisSaida[$indExist] + $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indExist] = $ValoresContabeisEntrada[$indExist] + $ValorContabilTESTEEntrada;
                              }
                              //FIM TESTE 2

                              $ConfirmarInclusao = false;

                              //Para as movimentações conjuntas (6,9,11,29 e 12,13,15,30) Primeiro é gerado a movimentação de saída (12,13,15,30) e após isso a movimentação de entrada (6,9,11,29)
                              //Gera Lançamento contabil para a movimentação 12 -  SAÍDA POR EMPRÉSTIMO ENTRE ÓRGÃOS

                              //ORIGINAL
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaida,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida);
                              //ORIGINAL

                              //TESTE 2
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida,
                               // $EspecificacoesContabeis, $ValoresContabeisSaida);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                     $MovimentacaoSecun, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisSaida,
                                     $SubElementosDespesa, $ValoresSubelementosSaida,
                                     $MatriculaSecun, $ResponsavelSecun,
                                     $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              $ConfirmarInclusao = true; //Confirmar a inclusão após inserir a 1ª movimentação para as movimentações conjuntas (6,9,11,29 e 12,13,15,30)

                              //Gera Lançamento contabil para a movimentação 6 -  ENTRADA POR EMPRÉSTIMO ENTRE ÓRGÃOS

                              //ORIGINAL
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntrada,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada);
                              //ORIGINAL

                              //TESTE 2
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada,
                               // $EspecificacoesContabeis, $ValoresContabeisEntrada);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                     $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisEntrada,
                                     $SubElementosDespesa, $ValoresSubelementosEntrada,
                                     $Matricula, $Responsavel,
                                     $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              //FIM TESTE
                              exit;
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }


						# CASO SEJA ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ÓRGÃOS - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 9){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              # VERIFICANDO Se O Material Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
              $sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
              $sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
              $sqlChkExist .= "   FOR UPDATE ";
              $resChkExist  = $db->query($sqlChkExist);
              if( db::isError($resChkExist) ){
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
              }else{
                $QtdChkExist = $resChkExist->numRows();
                $LinhaChkExist = $resChkExist->fetchRow();
                if($QtdChkExist == 0 or $LinhaChkExist[0] == 0){
                    $ValorMat1Arma = $Valor;            // Se Não Existe, o valor médio na tabela de movimentação recebe o valor da movimentação correspodente (Mov. 13 - Saida por devolução de emprestimo entre órgãos)
                }else{
                    $ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
                }
                if(!ChecaNaoExistenciaCanc($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                    $Mens      = 1;
                    $Tipo      = 2;
                    $Mensagem  = "O Material não pode mais ser recebido, pois a Movimentação de Saída foi Cancelada pelo outro Almoxarifado";
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                }else{
                  if(!ChecaNaoExistenciaFlag($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                      $Mens      = 1;
                      $Tipo      = 2;
                      $Mensagem  = "O Material já foi recebido, provavelmente por outro usuário usando o sistema simultaneamente";
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                  }else{
                    # INSERINDO MOVIMENTO #
                    $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                    $sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                    $sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                    $sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                    $sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP,  ";
                    $sqlInsert .= " CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";            // ALMOX SECUNDÁRIO
                    $sqlInsert .= ") VALUES ( ";
                    $sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                    $sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                    $sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
                    $sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
                    $sqlInsert .= " $AlmoxSec, $AnoAtualizar, $SeqMovimentacao, '$Observacao' "; // VALOR DO ALMOX SECUNDÁRIO
                    $sqlInsert .= ")";
                    $resInsert  = $db->query($sqlInsert);
                    if( db::isError($resInsert) ){
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                    }else{
                      # ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM (13) #
                      $sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' "; // Seta Flag de correspondência
                      $sqlMovOri .= " WHERE CALMPOCODI = $AlmoxSec ";
                      $sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
                      $sqlMovOri .= "   AND CMOVMACODI = $SeqMovimentacao";
                      $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $resMovOri  = $db->query($sqlMovOri);
                      if( db::isError($resMovOri) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
                      }else{
                        # Descobrindo o sequencial da movimentação de origem #
                        $sqlmovseq  = "SELECT CMOVMACODT FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                        $sqlmovseq .= " WHERE	CALMPOCODI = $AlmoxSec ";
                        $sqlmovseq .= "   AND AMOVMAANOM = $AnoAtualizar ";
                        $sqlmovseq .= "   AND CMOVMACODI = $SeqMovimentacao ";
                        $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                        $resmovseq  = $db->query($sqlmovseq);
                        if( db::isError($resmovseq) ){
                            $db->query("ROLLBACK");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovseq");
                        }else{
                          $LinhaSeqMov  = $resmovseq->fetchRow();
                          $MovNumeroSec = $LinhaSeqMov[0];
                          if($QtdChkExist == 0){ // Se não há o material 1 no estoque do almoxarifado, Insere, se há, Atualiza a quantidade
                              # INSERINDO ESTOQUE #
                              $sqlInsert  = " INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                              $sqlInsert .= " CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
                              $sqlInsert .= " AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
                              $sqlInsert .= " AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
                              $sqlInsert .= " CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                              $sqlInsert .= " ) VALUES ( ";
                              $sqlInsert .= " $Material, $Localizacao, $QtdMovimentada, NULL, ";
                              $sqlInsert .= " NULL, $QtdMovimentada, NULL, NULL, ";
                              $sqlInsert .= " NULL, NULL, $Valor, $Valor, ";
                              $sqlInsert .= " $GrupoEmp, $Usuario, '$DataGravacao' )";
                              $resInsert  = $db->query($sqlInsert);
                              if( db::isError($resInsert) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                              }
                          }else{
                              # ATUALIZANDO ESTOQUE #
                              $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET VARMATUMED = $ValorMat1Arma, VARMATULTC = $ValorMat1Arma, AARMATQTDE = AARMATQTDE + $QtdMovimentada, AARMATESTR = AARMATESTR + $QtdMovimentada ";
                              $sqlUpdate .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                              $resUpdate  = $db->query($sqlUpdate);
                              if( db::isError($resUpdate) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                              }
                          }
                          if($RollBack != 1){
                            # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                            $dbora = ConexaoOracle();

                            # Evita que Rollback não funcione #
                            $dbora->autoCommit(false);
                            # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                            $dbora->query("BEGIN TRANSACTION");

                            $TimeStamp                   = $DataGravacao;
                            $DiaBaixa                    = date("d");
                            $MesBaixa                    = date("m");
                            $AnoBaixa                    = date("Y");

                            //Obtendo os Sub-elementos de despesa
                            $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                            $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                            $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                            $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                            $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                            $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                            $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                            $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                            $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                            $sql .= "       MAT.CMATEPSEQU = $Material ";

                            $res  = $db->query($sql);

                            if( db::isError($res) ){
                                $RollBack = 1;
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            } else {
                              //Preparando parâmetros para o lançamento de custo
                              $SubElementosDespesa = array();
                              $ValoresSubelementosSaida = array();
                              $ValoresSubelementosEntrada = array();

                              #Preparando os dados para o lançamento de contábil
                              $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                              $ValoresContabeisSaida = array();  //valores contábeis de saída conforme o tipo do material (Consumo ou Permanente)
                              $ValoresContabeisEntrada = array();  //valores contábeis de entrada conforme o tipo do material (Consumo ou Permanente)

                              $Linha = $res->fetchRow();
                              $CGRUSEELE1 = $Linha[0];
                              $CGRUSEELE2 = $Linha[1];
                              $CGRUSEELE3 = $Linha[2];
                              $CGRUSEELE4 = $Linha[3];
                              $CGRUSESUBE = $Linha[4];

                              //TESTE
                              if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                                $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                                $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                                $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                                if(!in_array($Subelemento, $SubElementosDespesa)){
                                  $indice = count($SubElementosDespesa);
                                  $SubElementosDespesa[$indice] = $Subelemento;
                                  $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                                } else {
                                  $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                  $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                                }
                              } else {
                                # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                                $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                                $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                                if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                RedirecionaPost($Url);
                                exit;
                              }
                              //FIM TESTE

                              //ORIGINAL
                              // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                              // $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                              // $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                              // $indice = count($SubElementosDespesa);
                              // if(!in_array($Subelemento, $SubElementosDespesa)){
                                // $SubElementosDespesa[$indice] = $Subelemento;

                                // $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                              // } else {
                                // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.

                                // $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                              // }
                              //ORIGINAL

                              //INICIO TESTE

                              //ORIGINAL
                              // $ValorContabilSaida = $QtdMovimentada*$Valor;
                              // $ValorContabilSaida = sprintf("%01.2f",round($ValorContabilSaida,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim

                              // $ValorContabilEntrada = $QtdMovimentada*$ValorMat1Arma;
                              // $ValorContabilEntrada = sprintf("%01.2f",round($ValorContabilEntrada,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                              //ORIGINAL

                              //TESTE 2
                              $ValorContabilTESTESaida = $QtdMovimentada*$Valor;
                              $ValorContabilTESTESaida = sprintf("%01.2f",round($ValorContabilTESTESaida,2));

                              $ValorContabilTESTEEntrada = $QtdMovimentada*$ValorMat1Arma;
                              $ValorContabilTESTEEntrada = sprintf("%01.2f",round($ValorContabilTESTEEntrada,2));

                              if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                                $indice = count($EspecificacoesContabeis);
                                $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                                $ValoresContabeisSaida[$indice] = $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indice] = $ValorContabilTESTEEntrada;
                              } else {
                                $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                                $ValoresContabeisSaida[$indExist] = $ValoresContabeisSaida[$indExist] + $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indExist] = $ValoresContabeisEntrada[$indExist] + $ValorContabilTESTEEntrada;
                              }
                              //FIM TESTE 2

                              $ConfirmarInclusao = false;

                              //Para as movimentações conjuntas (6,9,11,29 e 12,13,15,30) Primeiro é gerado a movimentação de saída (12,13,15,30) e após isso a movimentação de entrada (6,9,11,29)
                              //Gera Lançamento contabil para a movimentação 13 - SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO

                              //ORIGINAL
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaida,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida);
                              //ORIGINAL

                              //TESTE 2
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida,
                               // $EspecificacoesContabeis, $ValoresContabeisSaida);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                     $MovimentacaoSecun, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisSaida,
                                     $SubElementosDespesa, $ValoresSubelementosSaida,
                                     $MatriculaSecun, $ResponsavelSecun,
                                     $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              $ConfirmarInclusao = true; //Confirmar a inclusão após inserir a 1ª movimentação para as movimentações conjuntas (6,9,11,29 e 12,13,15,30)

                              //Gera Lançamento contabil para a movimentação 9 - ENTRADA POR DEVOLUÇÃO DE EMPRÉSTIMO

                              //ORIGINAL
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntrada,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada);
                              //ORIGINAL

                              //TESTE
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada,
                               // $EspecificacoesContabeis, $ValoresContabeisEntrada);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                     $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisEntrada,
                                     $SubElementosDespesa, $ValoresSubelementosEntrada,
                                     $Matricula, $Responsavel,
                                     $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              //FIM TESTE
                              exit;
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }


						# CASO SEJA ENTRADA POR DOAÇÃO EXTERNA - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and ($Movimentacao == 10 or $Movimentacao == 32)){

                # Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								$Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);

								if($Mens==0){
										# INSERINDO MOVIMENTO #
										$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
										$sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
										$sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
										$sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
										$sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
										$sqlInsert .= ") VALUES ( ";
										$sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
										$sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
										$sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
										$sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
										$sqlInsert .= ")";
										$resInsert  = $db->query($sqlInsert);
										if( db::isError($resInsert) ){
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
										}else{

                      # TESTA SE O MATERIAL JÁ EXISTE EM SFPC.TBARMAZENAMENTOMATERIAL #
                      $sqlTestando  = "SELECT CMATEPSEQU FROM SFPC.TBARMAZENAMENTOMATERIAL ";
                      $sqlTestando .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                      $resTestando  = $db->query($sqlTestando);
                      if( db::isError($resTestando) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlTestando");
                      }else{
                        $QtdTestando = $resTestando->numRows();
                        if($QtdTestando == 0){
                            # INSERINDO ESTOQUE - Caso seja um item que ainda não está no estoque #
                            $sqlInsert  = "INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                            $sqlInsert .= "CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
                            $sqlInsert .= "AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
                            $sqlInsert .= "AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
                            $sqlInsert .= "CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                            $sqlInsert .= ") VALUES ( ";
                            $sqlInsert .= "$Material, $Localizacao, $QtdMovimentada, NULL, ";
                            $sqlInsert .= "NULL, $QtdMovimentada, NULL, NULL, ";
                            $sqlInsert .= "NULL, NULL, $Valor, $Valor, ";
                            $sqlInsert .= "$GrupoEmp, $Usuario, '$DataGravacao' )";
                            $resInsert  = $db->query($sqlInsert);
                            if( db::isError($resInsert) ){
                                $RollBack = 1;
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                            }
                        }else{
                            # ATUALIZANDO ESTOQUE - Caso o item já esteja no estoque #
                            $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                            $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                            $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                            $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                            $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                            $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                            $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                            $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                            $resUpdate  = $db->query($sqlUpdate);
                            if( db::isError($resUpdate) ){
                                $RollBack = 1;
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                            }
                        }
                        if($RollBack != 1){
                          # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                          $dbora = ConexaoOracle();

                          # Evita que Rollback não funcione #
                          $dbora->autoCommit(false);
                          # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                          $dbora->query("BEGIN TRANSACTION");

                          $TimeStamp            = $DataGravacao;
                          $DiaBaixa             = date("d");
                          $MesBaixa             = date("m");
                          $AnoBaixa             = date("Y");


                          //INICIO TESTE

                          //Obtendo os Sub-elementos de despesa
                          $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                          $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                          $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                          $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                          $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                          $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                          $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                          $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                          $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                          $sql .= "       MAT.CMATEPSEQU = $Material ";

                          $res  = $db->query($sql);

                          if( db::isError($res) ){
                              $RollBack = 1;
                              $db->query("ROLLBACK");
                              $db->query("END TRANSACTION");
                              $db->disconnect();
                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          } else {

                            #Preparando parâmetros para o lançamento de custo
                            $SubElementosDespesa = array();
                            $ValoresSubelementos = array();

                            #Preparando os dados para o lançamento de contábil
                            $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                            $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                            $Linha = $res->fetchRow();
                            $CGRUSEELE1 = $Linha[0];
                            $CGRUSEELE2 = $Linha[1];
                            $CGRUSEELE3 = $Linha[2];
                            $CGRUSEELE4 = $Linha[3];
                            $CGRUSESUBE = $Linha[4];

                            //TESTE
                            if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                              $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                              $ValorSubElemento = $QtdMovimentada*$Valor;

                              if(!in_array($Subelemento, $SubElementosDespesa)){
                                $indice = count($SubElementosDespesa);
                                $SubElementosDespesa[$indice] = $Subelemento;
                                $ValoresSubelementos[$indice] = $ValorSubElemento;
                              } else {
                                $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                              $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                              }
                            } else {
                              # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                              $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                              $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                              if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                              RedirecionaPost($Url);
                              exit;
                            }
                            //FIM TESTE

                            //ORIGINAL
                            // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                            // $ValorSubElemento = $QtdMovimentada*$Valor;

                            // $indice = count($SubElementosDespesa);
                            // if(!in_array($Subelemento, $SubElementosDespesa)){
                              // $SubElementosDespesa[$indice] = $Subelemento;
                              // $ValoresSubelementos[$indice] = $ValorSubElemento;
                            // } else {
                              // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                              // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                            // }
                            //ORIGINAL

                            //TESTE 2
                            $ValorContabilTESTE = $QtdMovimentada*$Valor;
                            $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                            if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                              $indice = count($EspecificacoesContabeis);
                              $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                              $ValoresContabeis[$indice] = $ValorContabilTESTE;
                            } else {
                              $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                              $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                            }
                            //FIM TESTE 2

                            //ORIGINAL
                            #Preparando parâmetros para o lançamento contábil
                            // $ValorContabil = $QtdMovimentada*$Valor;
                            // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                            //ORIGINAL

                            $ConfirmarInclusao = true;

                            //ORIGINAL
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             // $Movimentacao, $TipoMaterialTESTE,
                             // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                             // $Matricula, $Responsavel,
                             // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                             // $SubElementosDespesa, $ValoresSubelementos);
                             //ORIGINAL

                            //TESTE2
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                              // $Movimentacao, $TipoMaterialTESTE,
                              // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                              // $Matricula, $Responsavel,
                              // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                              // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                              // $SubElementosDespesa, $ValoresSubelementos,
                              // $EspecificacoesContabeis, $ValoresContabeis);
                            //FIM TESTE2

                            //TESTE 3
                            GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                   $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                   $EspecificacoesContabeis, $ValoresContabeis,
                                   $SubElementosDespesa, $ValoresSubelementos,
                                   $Matricula, $Responsavel,
                                   $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                   $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                            //FIM TESTE 3

                            //FIM TESTE
                            exit;

                          }
                        }
                      }
										}
								}


						# CASO SEJA ENTRADA POR TROCA - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 11){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # DESCOBRE O CÓDIGO SEQUENCIAL DO MOVIMENTO NO ANO PARA O ALMOXARIFADO SECUNDÁRIO #
              $sqlMovAnoSequSec  = "SELECT MAX(CMOVMACODI) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
              $sqlMovAnoSequSec .= " WHERE CALMPOCODI = $AlmoxSec AND AMOVMAANOM = $AnoMovimentacao ";
              $resMovAnoSequSec  = $db->query($sqlMovAnoSequSec);
              if( db::isError($resMovAnoSequSec) ){
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovAnoSequSec");
              }else{
                $LinhaSec      = $resMovAnoSequSec->fetchRow();
                $MovAnoSequSec = $LinhaSec[0] + 1;
                # DESCOBRE CÓDIGO DA MOVIMENTAÇÃO POR MOVIMENTAÇÃO ($MovNumeroMat1Almox, $MovNumeroMat2Almox, $MovNumeroMat2AlmoxSec, $MovNumeroMat1AlmoxSec) #
                # $MovNumeroMat1Almox <-- Entrada (11) Material 1 Almoxarifado Atual
                $sqlMovNr  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                $sqlMovNr .= "WHERE CALMPOCODI = $Almoxarifado ";
                $sqlMovNr .= "  AND AMOVMAANOM = $AnoMovimentacao ";
                $sqlMovNr .= "  AND CTIPMVCODI = 11 ";
                $resMovNr  = $db->query($sqlMovNr);
                if( db::isError($resMovNr) ){
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovNr");
                }else{
                  $LinhaMovNr         = $resMovNr->fetchRow();
                  $MovNumeroMat1Almox = $LinhaMovNr[0] + 1;
                  # $MovNumeroMat2Almox <-- Saída (15) Material 2 Almoxarifado Atual
                  $sqlMovNr  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                  $sqlMovNr .= "WHERE CALMPOCODI = $Almoxarifado ";
                  $sqlMovNr .= "  AND AMOVMAANOM = $AnoMovimentacao ";
                  $sqlMovNr .= "  AND CTIPMVCODI = 15 ";
                  $resMovNr  = $db->query($sqlMovNr);
                  if( db::isError($resMovNr) ){
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovNr");
                  }else{
                    $LinhaMovNr         = $resMovNr->fetchRow();
                    $MovNumeroMat2Almox = $LinhaMovNr[0] + 1;
                    # $MovNumeroMat2AlmoxSec <-- Entrada (11) Material 2 Almoxarifado Secundário
                    $sqlMovNr  = "SELECT MAX(CMOVMACODT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                    $sqlMovNr .= "WHERE CALMPOCODI = $AlmoxSec ";
                    $sqlMovNr .= "  AND AMOVMAANOM = $AnoMovimentacao ";
                    $sqlMovNr .= "  AND CTIPMVCODI = 11 ";
                    $resMovNr  = $db->query($sqlMovNr);
                    if( db::isError($resMovNr) ){
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovNr");
                    }else{
                      $LinhaMovNr             = $resMovNr->fetchRow();
                      $MovNumeroMat2AlmoxSec	= $LinhaMovNr[0]+1;
                      # $MovNumeroMat1AlmoxSec <-- Saída (15) Material 1 Almoxarifado Secundário - Descobre pela movimentação já gravada pelo almoxarifado secundário. Aproveita o Select e descobre também o Código do Usuário, o Grupo, a Matrícula e o Responsável de quem fez a Saída por Troca
                      $sqlMovNr  = "SELECT CMOVMACODT, CUSUPOCODI, CGREMPCODI, AMOVMAMATR, NMOVMARESP ";
                      $sqlMovNr .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                      $sqlMovNr .= " WHERE CALMPOCODI = $AlmoxSec ";
                      $sqlMovNr .= "	 AND AMOVMAANOM = $AnoAtualizar ";
                      $sqlMovNr .= "   AND CMOVMACODI = $SeqMovimentacao ";
                      $sqlMovNr .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $resMovNr  = $db->query($sqlMovNr);
                      if( db::isError($resMovNr) ){
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovNr");
                      }else{
                        $LinhaSeqMov           = $resMovNr->fetchRow();
                        $MovNumeroMat1AlmoxSec = $LinhaSeqMov[0];
                        $UsuarioSec            = $LinhaSeqMov[1];
                        $GrupoEmpSec           = $LinhaSeqMov[2];
                        $MatriculaSec          = $LinhaSeqMov[3];
                        $ResponsavelSec        = $LinhaSeqMov[4];
                        # INICIANDO TRANSAÇÃO #
                        $db->query("BEGIN TRANSACTION");
                        # Descobre O Valor Do Material Na Movimentacao (trava movimentação) #
                        $sqlValor  = "SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                        $sqlValor .= "WHERE CALMPOCODI = $AlmoxSec ";
                        $sqlValor .= "  AND AMOVMAANOM = $AnoAtualizar ";
                        $sqlValor .= "  AND CMOVMACODI = $SeqMovimentacao ";
                        $sqlValor .= "  AND CMATEPSEQU = $Material ";
                        $sqlValor .= "  AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                        $sqlValor .= "  FOR UPDATE ";
                        $resValor  = $db->query($sqlValor);
                        if( db::isError($resValor) ){
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlValor");
                        }else{
                          $Linha = $resValor->fetchRow();
                          $Valor = $Linha[0];
                          # VERIFICANDO Se O Material 1 Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
                          $sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
                          $sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                          $sqlChkExist .= "   FOR UPDATE ";
                          $resChkExist  = $db->query($sqlChkExist);
                          if( db::isError($resChkExist) ){
                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
                          }else{
                            $QtdChkExist   = $resChkExist->numRows();
                            $LinhaChkExist = $resChkExist->fetchRow();
                            if($QtdChkExist == 0 or $LinhaChkExist[0] == 0){
                                $ValorMat1Arma = $Valor;            // Se Não Existe, o valor médio na tabela de movimentação recebe o valor do Almoxarifado Secundário
                            }else{
                                $ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
                            }
                            if(!ChecaNaoExistenciaCanc($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                                $Mens      = 1;
                                $Tipo      = 2;
                                $Mensagem  = "O Material não pode mais ser recebido, pois a Movimentação de Saída foi Cancelada pelo outro Almoxarifado";
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                            }else{
                              if(!ChecaNaoExistenciaFlag($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                                  $Mens      = 1;
                                  $Tipo      = 2;
                                  $Mensagem  = "O Material já foi recebido, provavelmente por outro usuário usando o sistema simultaneamente";
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                              }else{
                                # INSERE MOVIMENTO 1 - Entrada Material 1 - Almoxarifado #
                                $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                                $sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                                $sqlInsert .= " CTIPMVCODI, CMATEPSEQU, AMOVMAQTDM, ";
                                $sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                                $sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP, CALMPOCOD1, ";
                                $sqlInsert .= " CMATEPSEQ1, AMOVMAQCOR, AMOVMAANO1, CMOVMACOD1, ";
                                $sqlInsert .= " FMOVMACORR, EMOVMAOBSE ";
                                $sqlInsert .= ") VALUES ( ";
                                $sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                                $sqlInsert .= " 11, $Material, $QtdMovimentada, ";
                                $sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
                                $sqlInsert .= " $MovNumeroMat1Almox, $Matricula, '$Responsavel', $AlmoxSec, ";
                                $sqlInsert .= " $CodReduzMat2, $QuantMat2, $AnoAtualizar, $SeqMovimentacao, 'S',";
                                $sqlInsert .= "'$Observacao' ";
                                $sqlInsert .= ")";
                                $resInsert  = $db->query($sqlInsert);
                                if( db::isError($resInsert) ){
                                    $db->query("ROLLBACK");
                                    $db->query("END TRANSACTION");
                                    $db->disconnect();
                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                                }else{
                                  if($QtdChkExist == 0){ // Se não há o material 1 no estoque do almoxarifado, Insere, se há, Atualiza a quantidade
                                      # INSERE ESTOQUE - Entrada Material 1 - Almoxarifado #
                                      $sqlInsert  = " INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                                      $sqlInsert .= " CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATESTR, ";
                                      $sqlInsert .= " VARMATUMED, VARMATULTC, ";
                                      $sqlInsert .= " CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                                      $sqlInsert .= " ) VALUES ( ";
                                      $sqlInsert .= " $Material, $Localizacao, $QtdMovimentada, $QtdMovimentada, ";
                                      $sqlInsert .= " $Valor, $Valor, ";
                                      $sqlInsert .= " $GrupoEmp, $Usuario, '$DataGravacao' )";
                                      $resInsert  = $db->query($sqlInsert);
                                      if( db::isError($resInsert) ){
                                          $RollBack = 1;
                                          $db->query("ROLLBACK");
                                          $db->query("END TRANSACTION");
                                          $db->disconnect();
                                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                                      }
                                  }else{
                                      # ATUALIZA ESTOQUE - Só Quantidade - Entrada Material 1 - Almoxarifado #
                                      $sqlUpdate  =	"UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET VARMATUMED = $ValorMat1Arma, VARMATULTC = $ValorMat1Arma, AARMATQTDE = AARMATQTDE + $QtdMovimentada, AARMATESTR = AARMATESTR + $QtdMovimentada ";
                                      $sqlUpdate .=	" WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                                      $resUpdate  = $db->query($sqlUpdate);
                                      if( db::isError($resUpdate) ){
                                          $RollBack = 1;
                                          $db->query("ROLLBACK");
                                          $db->query("END TRANSACTION");
                                          $db->disconnect();
                                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                                      }
                                  }

                                  if($RollBack != 1){
                                    # INSERE MOVIMENTO 2 - Saída Material 2 - Almoxarifado #
                                    $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                                    $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                                    $sqlInsert .= "CTIPMVCODI, CMATEPSEQU, AMOVMAQTDM, ";
                                    $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                                    $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, CALMPOCOD1, ";
                                    $sqlInsert .= "CMATEPSEQ1, AMOVMAQCOR, AMOVMAANO1, CMOVMACOD1, ";
                                    $sqlInsert .= "FMOVMACORR, EMOVMAOBSE ";
                                    $sqlInsert .= ") VALUES ( ";
                                    $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, ".($MovAnoSequ + 1).", '".date("Y-m-d")."', ";
                                    $sqlInsert .= "15, $CodReduzMat2, $QuantMat2, ";
                                    $sqlInsert .= "$ValorMat2, $ValorMat2, $GrupoEmp, $Usuario, '$DataGravacao', ";
                                    $sqlInsert .= "$MovNumeroMat2Almox, $Matricula, '$Responsavel', $AlmoxSec, ";
                                    $sqlInsert .= "$Material, $QtdMovimentada, $AnoAtualizar, $SeqMovimentacao, ";
                                    $sqlInsert .= "'S', '$Observacao' );";
                                    $resInsert  = $db->query($sqlInsert);
                                    if( db::isError($resInsert) ){
                                        $db->query("ROLLBACK");
                                        $db->query("END TRANSACTION");
                                        $db->disconnect();
                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                                    }else{
                                      # ATUALIZA ESTOQUE - Saída Material 2 #
                                      $sqlUpdate   = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET AARMATQTDE = AARMATQTDE - $QuantMat2, AARMATESTR = AARMATESTR - $QuantMat2 ";
                                      $sqlUpdate  .= " WHERE CMATEPSEQU = $CodReduzMat2 AND CLOCMACODI = $Localizacao ";
                                      $resUpdate  = $db->query($sqlUpdate);
                                      if( db::isError($resUpdate) ){
                                          $db->query("ROLLBACK");
                                          $db->query("END TRANSACTION");
                                          $db->disconnect();
                                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                                      }else{
                                        # VERIFICA Se O Material 2 Existe - Almoxarifado Secundário #
                                        $sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
                                        $sqlChkExist .= " WHERE CMATEPSEQU = $CodReduzMat2 AND CLOCMACODI = $LocalSecun ";
                                        $resChkExist  = $db->query($sqlChkExist);
                                        if( db::isError($resChkExist) ){
                                            $db->query("ROLLBACK");
                                            $db->query("END TRANSACTION");
                                            $db->disconnect();
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
                                        }else{
                                          $QtdChkExist   = $resChkExist->numRows();
                                          $LinhaChkExist = $resChkExist->fetchRow();
                                          if($QtdChkExist == 0) {
                                              $ValorMat2Arma = $ValorMat2;       // Se Não Existe, o valor médio na tabela de movimentação recebe o valor do Almoxarifado Atual
                                          }else{
                                              $ValorMat2Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Secundário
                                          }
                                          # INSERE MOVIMENTO 3 - Entrada Material 2 - Almoxarifado Secundário #
                                          $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                                          $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                                          $sqlInsert .= "CTIPMVCODI, CMATEPSEQU, AMOVMAQTDM, ";
                                          $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                                          $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, CALMPOCOD1, ";
                                          $sqlInsert .= "CMATEPSEQ1, AMOVMAQCOR, AMOVMAANO1, CMOVMACOD1, ";
                                          $sqlInsert .= "FMOVMACORR, EMOVMAOBSE ";
                                          $sqlInsert .= ") VALUES ( ";
                                          $sqlInsert .= "$AlmoxSec, $AnoMovimentacao, $MovAnoSequSec, '".date("Y-m-d")."', ";
                                          $sqlInsert .= "11, $CodReduzMat2, $QuantMat2, ";
                                          $sqlInsert .= "$ValorMat2Arma, $ValorMat2Arma, $GrupoEmpSec, $UsuarioSec, '$DataGravacao', ";
                                          $sqlInsert .= "$MovNumeroMat2AlmoxSec, $MatriculaSec, '$ResponsavelSec', $Almoxarifado, ";
                                          $sqlInsert .= "$Material, $QtdMovimentada, $AnoAtualizar, ".($MovAnoSequ + 1).", ";
                                          $sqlInsert .= "'S', '$Observacao' )";
                                          $resInsert  = $db->query($sqlInsert);
                                          if( db::isError($resInsert) ){
                                              $db->query("ROLLBACK");
                                              $db->query("END TRANSACTION");
                                              $db->disconnect();
                                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                                          }else{
                                            if($QtdChkExist == 0){ // Se não há o material 2 no estoque do almoxarifado secundário, Insere, se há, Atualiza a quantidade
                                                # INSERE ESTOQUE - Entrada Material 2 - Almoxarifado Secundário #
                                                $sqlInsert  = " INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                                                $sqlInsert .= " CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATESTR, ";
                                                $sqlInsert .= " VARMATUMED, VARMATULTC, ";
                                                $sqlInsert .= " CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                                                $sqlInsert .= " ) VALUES ( ";
                                                $sqlInsert .= " $CodReduzMat2, $LocalSecun, $QuantMat2, $QuantMat2, ";
                                                $sqlInsert .= " $ValorMat2, $ValorMat2, ";
                                                $sqlInsert .= " $GrupoEmpSec, $UsuarioSec, '$DataGravacao' )";
                                                $resInsert  = $db->query($sqlInsert);
                                                if( db::isError($resInsert) ){
                                                    $RollBack = 1;
                                                    $db->query("ROLLBACK");
                                                    $db->query("END TRANSACTION");
                                                    $db->disconnect();
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                                                }
                                            }else{
                                                # ATUALIZA ESTOQUE - Só Quantidade - Entrada Material 2 - Almoxarifado Secundário #
                                                $sqlUpdate  =	"UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET VARMATUMED = $ValorMat2Arma, VARMATULTC = $ValorMat2Arma, AARMATQTDE = AARMATQTDE + $QuantMat2, AARMATESTR = AARMATESTR + $QuantMat2 ";
                                                $sqlUpdate .=	" WHERE CMATEPSEQU = $CodReduzMat2 AND CLOCMACODI = $LocalSecun ";
                                                $resUpdate  = $db->query($sqlUpdate);
                                                if( db::isError($resUpdate) ){
                                                    $RollBack = 1;
                                                    $db->query("ROLLBACK");
                                                    $db->query("END TRANSACTION");
                                                    $db->disconnect();
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                                                }
                                            }

                                            if($RollBack != 1){
                                              # ATUALIZA MOVIMENTACAO SECUNDÁRIA #
                                              $sqlUpdate  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S', CMOVMACOD1 = $MovAnoSequ, AMOVMAANO1 = ".date("Y")." ";
                                              $sqlUpdate .= " WHERE	CALMPOCODI = $AlmoxSec ";
                                              $sqlUpdate .= "   AND AMOVMAANOM = $AnoAtualizar ";
                                              $sqlUpdate .= "		AND	CMOVMACODI = $SeqMovimentacao ";
                                              $sqlUpdate .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                                              $resUpdate  = $db->query($sqlUpdate);
                                              if( db::isError($resUpdate) ){
                                                  $db->query("ROLLBACK");
                                                  $db->query("END TRANSACTION");
                                                  $db->disconnect();
                                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                                              }else{
                                                # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                                                $dbora = ConexaoOracle();

                                                # Evita que Rollback não funcione #
                                                $dbora->autoCommit(false);
                                                # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                                                $dbora->query("BEGIN TRANSACTION");

                                                $TimeStamp             = $DataGravacao;
                                                $DiaBaixa              = date("d");
                                                $MesBaixa              = date("m");
                                                $AnoBaixa              = date("Y");

                                                //Obtendo os Sub-elementos de despesa
                                                $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                                                $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE, MAT.CMATEPSEQU ";
                                                $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                                                $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                                                $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                                                $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                                                $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                                                $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                                                $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                                                $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                                                $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                                                $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                                                $sql .= "       MAT.CMATEPSEQU IN ($Material,$CodReduzMat2) ";

                                                $res  = $db->query($sql);

                                                if( db::isError($res) ){
                                                    $RollBack = 1;
                                                    $db->query("ROLLBACK");
                                                    $db->query("END TRANSACTION");
                                                    $db->disconnect();
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {

                                                  /*
                                                                                                                     OBS: Verificar a movimentação 11 no programa CadMovimentacaoIncluir.php no momento em que este programa(CadMovimentacaoConfirmar.php) é chamado,
                                                                                                                              a fim de um melhor esclarecimento acerca dos códigos Reduzidos dos materiais ($Material e $CodReduzMat2), bem como suas quantidades e
                                                                                                                              valores unitários para confirmar esta movimentação.
                                                                                                                  */

                                                  #Preparando parâmetros para o lançamento de custo
                                                  $SubElementosDespesaMatA = array(); // Sub-elemento do Material A
                                                  $ValoresSubelementosSaidaAlmoxarifado1 = array(); //Valor do material A no Almoxarifado 1, com base no seu sub-elemento.
                                                  $ValoresSubelementosEntradaAlmoxarifado1 = array(); //Valor do material B no Almoxarifado 1, com base no seu sub-elemento.

                                                  $SubElementosDespesaMatB = array(); // Sub-elemento do Material B
                                                  $ValoresSubelementosSaidaAlmoxarifado2 = array(); //Valor do material B no Almoxarifado 2, com base no seu sub-elemento.
                                                  $ValoresSubelementosEntradaAlmoxarifado2 = array(); //Valor do material A no Almoxarifado 2, com base no seu sub-elemento.

                                                  #Preparando os dados para o lançamento de contábil
                                                  $EspecificacoesContabeisMatA = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente para o material A
                                                  $ValoresContabeisSaidaAlmoxarifado1 = array();  //valores contábeis de saída conforme o tipo do material (Consumo ou Permanente) no Almoxarifado 1 com base no tipo do material (especificação contábil)
                                                  $ValoresContabeisEntradaAlmoxarifado1 = array();  //valores contábeis de entrada  conforme o tipo do material (Consumo ou Permanente) no Almoxarifado 1 com base no tipo do material (especificação contábil)

                                                  $EspecificacoesContabeisMatB = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente para o material B
                                                  $ValoresContabeisSaidaAlmoxarifado2 = array();  //valores contábeis de saída conforme o tipo do material (Consumo ou Permanente) no Almoxarifado 2 com base no tipo do material (especificação contábil)
                                                  $ValoresContabeisEntradaAlmoxarifado2 = array();  //valores contábeis de entrada  conforme o tipo do material (Consumo ou Permanente) no Almoxarifado 2 com base no tipo do material (especificação contábil)

                                                  while ($Linha = $res->fetchRow()){

                                                    $CGRUSEELE1 = $Linha[0];
                                                    $CGRUSEELE2 = $Linha[1];
                                                    $CGRUSEELE3 = $Linha[2];
                                                    $CGRUSEELE4 = $Linha[3];
                                                    $CGRUSESUBE = $Linha[4];
                                                    $CodMaterialIntegrado = $Linha[5];

                                                    if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                                                      $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                                                      if($CodMaterialIntegrado == $Material){ //CodMaterialIntegrado == Material A ($Material)
                                                        //O Material A sai do Almoxarifado 1 e entra no Almoxarifado 2

                                                        //OBS: A $QuantMat2 refere-se a quantidade do material A
                                                        $ValorSubElementoSaidaAlmoxarifado1 = $QuantMat2*$ValorMat2;
                                                        $ValorSubElementoEntradaAlmoxarifado2 = $QuantMat2*$ValorMat2Arma;

                                                        if(!in_array($Subelemento, $SubElementosDespesaMatA)){
                                                          $indice = count($SubElementosDespesaMatA);
                                                          $SubElementosDespesaMatA[$indice] = $Subelemento;

                                                          $ValoresSubelementosSaidaAlmoxarifado1[$indice] = $ValorSubElementoSaidaAlmoxarifado1;
                                                          $ValoresSubelementosEntradaAlmoxarifado2[$indice] = $ValorSubElementoEntradaAlmoxarifado2;
                                                        } else {
                                                          $indExist = array_search ($Subelemento, $SubElementosDespesaMatA); //Equivale ao indExist: indice existente.

                                                          $ValoresSubelementosSaidaAlmoxarifado1[$indExist] = $ValoresSubelementosSaidaAlmoxarifado1[$indExist] + $ValorSubElementoSaidaAlmoxarifado1;
                                                          $ValoresSubelementosEntradaAlmoxarifado2[$indExist] = $ValoresSubelementosEntradaAlmoxarifado2[$indExist] + $ValorSubElementoEntradaAlmoxarifado2; //TESTE
                                                        }

                                                        //Preparando dados para o lançamento contábil
                                                        $ValorContabilTESTESaidaAlmoxarifado1 = $QuantMat2*$ValorMat2;
                                                        $ValorContabilTESTEEntradaAlmoxarifado2 = $QuantMat2*$ValorMat2Arma;

                                                        if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeisMatA)){
                                                          $indice = count($EspecificacoesContabeisMatA);
                                                          $EspecificacoesContabeisMatA[$indice] = $TipoMaterialTESTE;
                                                          $ValoresContabeisSaidaAlmoxarifado1[$indice] = $ValorContabilTESTESaidaAlmoxarifado1;
                                                          $ValoresContabeisEntradaAlmoxarifado2[$indice] = $ValorContabilTESTEEntradaAlmoxarifado2;
                                                        } else {
                                                          $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeisMatA); //Equivale ao indExist: indice existente.
                                                          $ValoresContabeisSaidaAlmoxarifado1[$indExist] = $ValoresContabeisSaidaAlmoxarifado1[$indExist] + $ValorContabilTESTESaidaAlmoxarifado1;
                                                          $ValoresContabeisEntradaAlmoxarifado2[$indExist] = $ValoresContabeisEntradaAlmoxarifado2[$indExist] + $ValorContabilTESTEEntradaAlmoxarifado2;
                                                        }
                                                      } else { //CodMaterialIntegrado == Material B ($CodReduzMat2)
                                                        //O Material B sai do Almoxarifado 2 e entra no Almoxarifado 1

                                                        //OBS: A $QtdMovimentada refere-se a quantidade do material B
                                                        $ValorSubElementoSaidaAlmoxarifado2 = $QtdMovimentada*$Valor;
                                                        $ValorSubElementoEntradaAlmoxarifado1 = $QtdMovimentada*$ValorMat1Arma;

                                                        if(!in_array($Subelemento, $SubElementosDespesaMatB)){
                                                          $indice = count($SubElementosDespesaMatB);
                                                          $SubElementosDespesaMatB[$indice] = $Subelemento;

                                                          $ValoresSubelementosSaidaAlmoxarifado2[$indice] = $ValorSubElementoSaidaAlmoxarifado2;
                                                          $ValoresSubelementosEntradaAlmoxarifado1[$indice] = $ValorSubElementoEntradaAlmoxarifado1; //TESTE

                                                        } else {
                                                          $indExist = array_search ($Subelemento, $SubElementosDespesaMatB); //Equivale ao indExist: indice existente.

                                                          $ValoresSubelementosSaidaAlmoxarifado2[$indExist] = $ValoresSubelementosSaidaAlmoxarifado2[$indExist] + $ValorSubElementoSaidaAlmoxarifado2;
                                                          $ValoresSubelementosEntradaAlmoxarifado1[$indExist] = $ValoresSubelementosEntradaAlmoxarifado1[$indExist] + $ValorSubElementoEntradaAlmoxarifado1; //TESTE
                                                        }

                                                        //Preparando dados para o lançamento contábil
                                                        $ValorContabilTESTESaidaAlmoxarifado2 = $QtdMovimentada*$Valor;
                                                        $ValorContabilTESTEEntradaAlmoxarifado1 = $QtdMovimentada*$ValorMat1Arma;

                                                        if(!in_array($TipoMaterialTESTE2, $EspecificacoesContabeisMatB)){
                                                          $indice = count($EspecificacoesContabeisMatB);
                                                          $EspecificacoesContabeisMatB[$indice] = $TipoMaterialTESTE2;
                                                          $ValoresContabeisSaidaAlmoxarifado2[$indice] = $ValorContabilTESTESaidaAlmoxarifado2;
                                                          $ValoresContabeisEntradaAlmoxarifado1[$indice] = $ValorContabilTESTEEntradaAlmoxarifado1;
                                                        } else {
                                                          $indExist = array_search ($TipoMaterialTESTE2, $EspecificacoesContabeisMatB); //Equivale ao indExist: indice existente.
                                                          $ValoresContabeisSaidaAlmoxarifado2[$indExist] = $ValoresContabeisSaidaAlmoxarifado2[$indExist] + $ValorContabilTESTESaidaAlmoxarifado2;
                                                          $ValoresContabeisEntradaAlmoxarifado1[$indExist] = $ValoresContabeisEntradaAlmoxarifado1[$indExist] + $ValorContabilTESTEEntradaAlmoxarifado1;
                                                        }
                                                        //FIM TESTE 2
                                                      }
                                                    } else {
                                                      # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                                      								$Mensagem = urlencode("O grupo do Material (Cod. Red: $CodMaterialIntegrado) não possui integração com Sub-elemento(s)");
                                      								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
                                      								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                      								header("location: ".$Url);
                                      								exit;
                                                    }
                                                    //FIM AQUI - ALTERAR
                                                  }
                                                  //FIM NOVO TESTE

                                                  //INICIO TESTE

                                                  #Preparando parâmetros para o lançamento contábil

                                                  //A movimentação de troca é realizada entre dois almoxarifados (almoxarifado 1 e almoxarifado 2), ou seja,
                                                  //O almoxarifado 1 quer trocar o material A pelo material B do almoxarifado 2.
                                                  //Estes realizam as seguintes ações:
                                                  //1 - SAÍDA DO MATERIAL A ($CodReduzMat2) DO ALMOXARIFADO 1 ($Almoxarifado)
                                                  //2 - ENTRADA DO MATERIAL B ($Material) NO ALMOXARIFADO 1 ($Almoxarifado)
                                                  //3 - SAÍDA DO MATERIAL B ($Material) DO ALMOXARIFADO 2 ($AlmoxSec)
                                                  //4 - ENTRADA DO MATERIAL A ($CodReduzMat2) NO ALMOXARIFADO 2 ($AlmoxSec)

                                                  $ConfirmarInclusao = false;

                                                   //Para as movimentações conjuntas (6,9,11,29 e 12,13,15,30) Primeiro é gerado a movimentação de saída (12,13,15,30) e após isso a movimentação de entrada (6,9,11,29)

                                                  //Gera Lançamento contabil para o almoxarifado 1
                                                  //Gera Lançamento contabil para a movimentação 15 -  SAÍDA POR TROCA (Saída do Material A do Almoxarifado 1)

                                                  //ORIGINAL
                                                  // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                   // $MovimentacaoSecun, $TipoMaterialTESTE,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaidaAlmoxarifado2,
                                                   // $Matricula, $Responsavel,
                                                   // $SeqRequisicao, $Almoxarifado, $MovNumeroMat2Almox, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatB, $ValoresSubelementosSaidaAlmoxarifado2);
                                                  //FIM ORIGINAL

                                                  //TESTE 2
                                                  // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                   // $MovimentacaoSecun, $TipoMaterialTESTE,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaidaAlmoxarifado1,
                                                   // $Matricula, $Responsavel,
                                                   // $SeqRequisicao, $Almoxarifado, $MovNumeroMat2Almox, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatA, $ValoresSubelementosSaidaAlmoxarifado1,
                                                   // $EspecificacoesContabeisMatA, $ValoresContabeisSaidaAlmoxarifado1);
                                                  //FIM TESTE 2

                                                  //TESTE 3
                                                  GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                         $MovimentacaoSecun, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                                         $EspecificacoesContabeisMatA, $ValoresContabeisSaidaAlmoxarifado1,
                                                         $SubElementosDespesaMatA, $ValoresSubelementosSaidaAlmoxarifado1,
                                                         $Matricula, $Responsavel,
                                                         $SeqRequisicao, $Almoxarifado, $MovNumeroMat2Almox, $AnoMovimentacao,
                                                         $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                                                  //FIM TESTE 3

                                                  //Gera Lançamento contabil para a movimentação 11 -  ENTRADA POR TROCA (Entrada do Material B do Almoxarifado 1)

                                                  //ORIGINAL
                                                  // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                   // $Movimentacao, $TipoMaterialTESTE2,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntradaAlmoxarifado2,
                                                   // $Matricula, $Responsavel,
                                                   // $SeqRequisicao, $Almoxarifado, $MovNumeroMat1Almox, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatB, $ValoresSubelementosEntradaAlmoxarifado1);
                                                  //FIM ORIGINAL

                                                  //TESTE 2
                                                  // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                   // $Movimentacao, $TipoMaterialTESTE2,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntradaAlmoxarifado1,
                                                   // $Matricula, $Responsavel,
                                                   // $SeqRequisicao, $Almoxarifado, $MovNumeroMat1Almox, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatB, $ValoresSubelementosEntradaAlmoxarifado1,
                                                   // $EspecificacoesContabeisMatB, $ValoresContabeisEntradaAlmoxarifado1);
                                                  //FIM TESTE 2

                                                  //TESTE 3
                                                  GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                                         $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                                         $EspecificacoesContabeisMatB, $ValoresContabeisEntradaAlmoxarifado1,
                                                         $SubElementosDespesaMatB, $ValoresSubelementosEntradaAlmoxarifado1,
                                                         $Matricula, $Responsavel,
                                                         $SeqRequisicao, $Almoxarifado, $MovNumeroMat1Almox, $AnoMovimentacao,
                                                         $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                                                  //FIM TESTE 3

                                                  //Gera Lançamento contabil para o almoxarifado 2
                                                  //Gera Lançamento contabil para a movimentação 15 -  SAÍDA POR TROCA (Saída do Material B do Almoxarifado 2)

                                                  //ORIGINAL
                                                  // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                   // $MovimentacaoSecun, $TipoMaterialTESTE2,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaidaAlmoxarifado1,
                                                   // $MatriculaSecun, $ResponsavelSecun,
                                                   // $SeqRequisicao, $AlmoxSec, $MovNumeroMat2AlmoxSec, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatA, $ValoresSubelementosSaidaAlmoxarifado1);
                                                  //FIM ORIGINAL

                                                  //TESTE 2
                                                  // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                   // $MovimentacaoSecun, $TipoMaterialTESTE2,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaidaAlmoxarifado2,
                                                   // $MatriculaSecun, $ResponsavelSecun,
                                                   // $SeqRequisicao, $AlmoxSec, $MovNumeroMat1AlmoxSec, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatB, $ValoresSubelementosSaidaAlmoxarifado2,
                                                   // $EspecificacoesContabeisMatB, $ValoresContabeisSaidaAlmoxarifado2);
                                                  //FIM TESTE 2

                                                  //TESTE 3
                                                  GerarLancamentoCustoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                         $MovimentacaoSecun, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                                         $EspecificacoesContabeisMatB, $ValoresContabeisSaidaAlmoxarifado2,
                                                         $SubElementosDespesaMatB, $ValoresSubelementosSaidaAlmoxarifado2,
                                                         $MatriculaSecun, $ResponsavelSecun,
                                                         $SeqRequisicao, $AlmoxSec, $MovNumeroMat1AlmoxSec, $AnoMovimentacao,
                                                         $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                                                  //FIM TESTE 3

                                                  $ConfirmarInclusao = true; //Confirmar a inclusão após inserir a 1ª movimentação para as movimentações conjuntas (6,9,11,29 e 12,13,15,30)

                                                  //Gera Lançamento contabil para a movimentação 11 -  ENTRADA POR TROCA (Entrada do Material A do Almoxarifado 2)

                                                  //ORIGINAL
                                                  // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                   // $Movimentacao, $TipoMaterialTESTE,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntradaAlmoxarifado1,
                                                   // $MatriculaSecun, $ResponsavelSecun,
                                                   // $SeqRequisicao, $AlmoxSec, $MovNumeroMat1AlmoxSec, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatB, $ValoresSubelementosEntradaAlmoxarifado1);
                                                  //FIM ORIGINAL

                                                  //TESTE 2
                                                  // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                   // $Movimentacao, $TipoMaterialTESTE,
                                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntradaAlmoxarifado2,
                                                   // $MatriculaSecun, $ResponsavelSecun,
                                                   // $SeqRequisicao, $AlmoxSec, $MovNumeroMat2AlmoxSec, $AnoMovimentacao,
                                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                                   // $SubElementosDespesaMatA, $ValoresSubelementosEntradaAlmoxarifado2,
                                                   // $EspecificacoesContabeisMatA, $ValoresContabeisEntradaAlmoxarifado2);
                                                  //FIM TESTE 2

                                                  //TESTE 3
                                                  GerarLancamentoCustoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                                         $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                                         $EspecificacoesContabeisMatA, $ValoresContabeisEntradaAlmoxarifado2,
                                                         $SubElementosDespesaMatA, $ValoresSubelementosEntradaAlmoxarifado2,
                                                         $MatriculaSecun, $ResponsavelSecun,
                                                         $SeqRequisicao, $AlmoxSec, $MovNumeroMat2AlmoxSec, $AnoMovimentacao,
                                                         $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                                                  //FIM TESTE 3

                                                  //FIM TESTE

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
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }


						# CASO SEJA SAÍDA POR EMPRÉSTIMO ENTRE ÓRGÃOS #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 12){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								$Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
								if($Mens==0){
										# INSERINDO MOVIMENTO #
										$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
										$sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
										$sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
										$sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
										$sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP,  ";
										$sqlInsert .= " CALMPOCOD1, EMOVMAOBSE ";    // ALMOX SECUNDÁRIO
										$sqlInsert .= ") VALUES ( ";
										$sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
										$sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
										$sqlInsert .= " $Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
										$sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
										$sqlInsert .= " $AlmoxSec, '$Observacao' ";
										$sqlInsert .= ")";
										$resInsert  = $db->query($sqlInsert);
										if( db::isError($resInsert) ){
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
										}else{
												# ATUALIZANDO ESTOQUE #
												$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
												$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                        $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
												$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
												$sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
												$sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
												$sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
												$sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
												$resUpdate  = $db->query($sqlUpdate);
												if( db::isError($resUpdate) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
												}else{
														# Fechando a transação e a conexão #
														$db->query("COMMIT");
														GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
														$db->query("END TRANSACTION");
														$db->disconnect();
														# EXIBINDO MENSAGEM #
														$Mensagem = "Movimentação Inserida com Sucesso";
														$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}
										}
								}


						# CASO SEJA SAÍDA POR DEVOLUÇÃO DE EMPRÉSTIMO ENTRE ÓRGÃOS #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 13){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								# Descobrindo o número sequencial da movimentação De Saída por Empréstimo (12), Para grava-lo no CMOVMACOD1 da movimentação 13, para posteriormente chegar a movimentação 6, no caso de um Cancelamento (31) - trava movimentação para evitar alteração em acesso simutâneo #
								$sqlNrMovOri  = "SELECT CMOVMACOD1, AMOVMAANO1 FROM SFPC.TBMOVIMENTACAOMATERIAL "; // Esta movimentação é do tipo 12
								$sqlNrMovOri .= " WHERE CALMPOCODI = $Almoxarifado ";
								$sqlNrMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
								$sqlNrMovOri .= "   AND CMOVMACODI = $SeqMovimentacao ";                           // Esta movimentação é do tipo 6
								$sqlNrMovOri .= "   AND CTIPMVCODI = 6 ";
								$sqlNrMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
								$sqlNrMovOri .= "   FOR UPDATE ";
								$resNrMovOri  = $db->query($sqlNrMovOri);
								if( db::isError($resNrMovOri) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlNrMovOri");
								}else{
										$LinhaNrMovOri = $resNrMovOri->fetchRow();
										$MovNrOri      = $LinhaNrMovOri[0];                                            // Número da movimentação de tipo 12
										$MovAnoOri     = $LinhaNrMovOri[1];
										# VERIFICANDO Se O Material Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
										$sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
										$sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
										$sqlChkExist .= "   FOR UPDATE ";
										$resChkExist  = $db->query($sqlChkExist);
										if( db::isError($resChkExist) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
										}else{
												$QtdChkExist = $resChkExist->numRows();
												if ($QtdChkExist == 0) {
														$ValorMat1Arma = $Valor;            // Se Não Existe, o valor médio na tabela de movimentação recebe o valor da movimentação anterior, istó é, a movimentação 6 - Entrada por empréstimo entre órgãos
												}else{
														$LinhaChkExist = $resChkExist->fetchRow();
														$ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
												}
												# INSERINDO MOVIMENTO #
												$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
												$sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
												$sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
												$sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
												$sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP, ";
												$sqlInsert .= " CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";
												$sqlInsert .= ") VALUES ( ";
												$sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
												$sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
												$sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
												$sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
												$sqlInsert .= " $AlmoxSec, $MovAnoOri, $MovNrOri, '$Observacao' ";
												$sqlInsert .= ")";
												$resInsert  = $db->query($sqlInsert);
												if( db::isError($resInsert) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
												}else{
														# ATUALIZANDO ESTOQUE #
														$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
														$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                            $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
														$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
														$sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
														$sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
														$sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
														$sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
														$resUpdate  = $db->query($sqlUpdate);
														if( db::isError($resUpdate) ){
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
														}else{
																# ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM - Movimentação 6 - Entrada por empréstimo - Caso o empréstimo tenha sido totalmente devolvido #
																$sqlMovOri	= "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' ";
																$sqlMovOri .= " WHERE CALMPOCODI = $Almoxarifado ";
																$sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
																$sqlMovOri .= "   AND CMOVMACODI = $SeqMovimentacao ";
																$sqlMovOri .= "   AND AMOVMAQTDM = $QtdDevolvida + $QtdMovimentada"; // Se a soma do que foi devolvido anteriormente com o que está sendo devolvido agora der o total do empréstimo, setará a Flag de correspondência na Movimentação de Empréstimo
																$sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																$resMovOri  = $db->query($sqlMovOri);
																if( db::isError($resMovOri) ){
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
																}else{
																		# Fechando a transação e a conexão #
																		$db->query("COMMIT");
																		GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		# EXIBINDO MENSAGEM #
																		$Mensagem = "Movimentação Inserida com Sucesso";
																		$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;
																}
														}
												}
										}
								}


						# CASO SEJA SAÍDA POR OBSOLETISMO, AVARIA, FURTO OU PRAZO DE VALIDADE - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and ($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 37)){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
              if($Mens==0){
                # INSERINDO MOVIMENTO #
                $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                $sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
                $sqlInsert .= ") VALUES ( ";
                $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                $sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                $sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                $sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
                $sqlInsert .= ");";
                $resInsert  = $db->query($sqlInsert);
                if( db::isError($resInsert) ){
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                }else{
                  # ATUALIZANDO ESTOQUE #
                  $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                  $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                  $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                  $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                  $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                  $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                  $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                  $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                  $resUpdate  = $db->query($sqlUpdate);
                  if( db::isError($resUpdate) ){
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                  }else{
                    # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                    $dbora = ConexaoOracle();

                    # Evita que Rollback não funcione #
                    $dbora->autoCommit(false);
                    # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                    $dbora->query("BEGIN TRANSACTION");

                    $TimeStamp            = $DataGravacao;
                    $DiaBaixa             = date("d");
                    $MesBaixa             = date("m");
                    $AnoBaixa             = date("Y");

                    //Obtendo os Sub-elementos de despesa
                    $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                    $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                    $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                    $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                    $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                    $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                    $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                    $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                    $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                    $sql .= "       MAT.CMATEPSEQU = $Material ";

                    $res  = $db->query($sql);

                    if( db::isError($res) ){
                        $RollBack = 1;
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                      #Preparando os dados para o lançamento de custo
                      $SubElementosDespesa = array();
                      $ValoresSubelementos = array();

                      #Preparando os dados para o lançamento de contábil
                      $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                      $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                      $Linha = $res->fetchRow();
                      $CGRUSEELE1 = $Linha[0];
                      $CGRUSEELE2 = $Linha[1];
                      $CGRUSEELE3 = $Linha[2];
                      $CGRUSEELE4 = $Linha[3];
                      $CGRUSESUBE = $Linha[4];

                      //TESTE
                      if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                        $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                        $ValorSubElemento = $QtdMovimentada*$Valor;

                        if(!in_array($Subelemento, $SubElementosDespesa)){
                          $indice = count($SubElementosDespesa);
                          $SubElementosDespesa[$indice] = $Subelemento;
                          $ValoresSubelementos[$indice] = $ValorSubElemento;
                        } else {
                          $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                          $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                        }
                      } else {
                        # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                        $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                        $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                        if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                        RedirecionaPost($Url);
                        exit;
                      }
                      //FIM TESTE

                      //ORIGINAL
                      // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                      // $ValorSubElemento = $QtdMovimentada*$Valor;


                      // $indice = count($SubElementosDespesa);
                      // if(!in_array($Subelemento, $SubElementosDespesa)){
                        // $SubElementosDespesa[$indice] = $Subelemento;
                        // $ValoresSubelementos[$indice] = $ValorSubElemento;
                      // } else {
                        // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                      // }
                      //ORIGINAL

                      //INICIO TESTE

                      //TESTE 2
                      $ValorContabilTESTE = $QtdMovimentada*$Valor;
                      $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                      if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                        $indice = count($EspecificacoesContabeis);
                        $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                        $ValoresContabeis[$indice] = $ValorContabilTESTE;
                      } else {
                        $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                        $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                      }
                      //FIM TESTE 2

                      //ORIGINAL
                      #Preparando os dados para o lançamento contábil
                      // $ValorContabil = $QtdMovimentada*$Valor;
                      // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                      //FIM ORIGINAL

                      $ConfirmarInclusao = true;

                      //ORIGINAL
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                       // $Movimentacao, $TipoMaterialTESTE,
                       // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                       // $Matricula, $Responsavel,
                       // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                       // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                       // $SubElementosDespesa, $ValoresSubelementos);
                       //ORIGINAL

                      //TESTE2
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                        // $Movimentacao, $TipoMaterialTESTE,
                        // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                        // $Matricula, $Responsavel,
                        // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                        // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                        // $SubElementosDespesa, $ValoresSubelementos,
                        // $EspecificacoesContabeis, $ValoresContabeis);
                      //FIM TESTE2

                      //TESTE 3
                      GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                             $EspecificacoesContabeis, $ValoresContabeis,
                             $SubElementosDespesa, $ValoresSubelementos,
                             $Matricula, $Responsavel,
                             $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                      //FIM TESTE 3

                      //FIM TESTE
                      exit;
                    }
                  }
                }
              }


						# CASO SEJA SAÍDA POR TROCA #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 15){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								$Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
								if($Mens==0){
										# INSERINDO MOVIMENTO #
										$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
										$sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
										$sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
										$sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
										$sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, CALMPOCOD1, ";
										$sqlInsert .= "CMATEPSEQ1, AMOVMAQCOR, EMOVMAOBSE ";
										$sqlInsert .= ") VALUES ( ";
										$sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
										$sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
										$sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
										$sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', $AlmoxSec, ";
										$sqlInsert .= "$CodReduzMat2, $QuantMat2, '$Observacao' ";
										$sqlInsert .= ");";
										$resInsert  = $db->query($sqlInsert);
										if( db::isError($resInsert) ){
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
										}else{
												# ATUALIZANDO ESTOQUE #
												$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
												$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                        $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
												$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
												$sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
												$sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
												$sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
												$sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
												$resUpdate  = $db->query($sqlUpdate);
												if( db::isError($resUpdate) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
												}else{
														# Fechando a transação e a conexão #
														$db->query("COMMIT");
														GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
														$db->query("END TRANSACTION");
														$db->disconnect();
														# EXIBINDO MENSAGEM #
														$Mensagem = "Movimentação Inserida com Sucesso";
														$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}
										}
								}


            //ALTERAR PARA ESTOQUE VIRTUAL
						# CASO SEJA ENTRADA/SAÍDA POR ACERTO DE DEVOLUÇÃO INTERNA PARA CANCELAMENTO DE NOTA FISCAL - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and ($Movimentacao == 21 or $Movimentacao == 22)){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								$Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
								if($Mens==0){
                  # INSERINDO MOVIMENTO #
                  $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                  $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                  $sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                  $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                  $sqlInsert .= "AENTNFANOE, CENTNFCODI, ";
                  $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
                  $sqlInsert .= ") VALUES ( ";
                  $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                  $sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                  $sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                  $sqlInsert .= "$AnoNota, $NumeroSeqNota, ";
                  $sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
                  $sqlInsert .= ");";
                  $resInsert  = $db->query($sqlInsert);
                  if( db::isError($resInsert) ){
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                  }else{
                    # ATUALIZANDO MOVIMENTAÇÃO 22 ou 2 #
                    $sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' ";
                    $sqlMovOri .= " WHERE CALMPOCODI = $Almoxarifado ";
                    $sqlMovOri .= "   AND AMOVMAANOM = $AnoMovimentacao ";
                    $sqlMovOri .= "   AND CREQMASEQU = $SeqRequisicao";
                    $sqlMovOri .= "   AND CMATEPSEQU = $Material";
                    $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                    $sqlMovOri .= "   AND FMOVMACORR IS NULL ";
                    $resMovOri  = $db->query($sqlMovOri);
                    if( db::isError($resMovOri) ){
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
                    }else{
                      # ATUALIZANDO ESTOQUE #
                      $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                      $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";

                      if($EstoqueVirtual == 'S'){
                        $sqlUpdate .= "       AARMATVIRT = $QtdFinalVirtual, ";
                      } else {
                        $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                      }

                      $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                      $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                      $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                      $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                      $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                      $resUpdate  = $db->query($sqlUpdate);
                      if( db::isError($resUpdate) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                      }else{
                        # Ajustando o atendimento #
                        $sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
                        # Se a quantidade movimentada é maior  #
                        if($Movimentacao == 21){ // Saída Por Acerto da Devolução Interna (21)
                            $sql .= "   SET AITEMRQTAT = AITEMRQTAT - $QtdMovimentada, ";
                        }else{                     // Entrada Por Acerto da Devolução Interna (22)
                            $sql .= "   SET AITEMRQTAT = AITEMRQTAT + $QtdMovimentada, ";
                        }
                        $sql .= "       CGREMPCODI = $GrupoEmp, ";
                        $sql .= "       CUSUPOCODI = $Usuario, TITEMRULAT = '$DataGravacao' ";
                        $sql .= " WHERE CMATEPSEQU = $Material AND CREQMASEQU = $SeqRequisicao ";
                        $res  = $db->query($sql);
                        if( db::isError($res) ){
                            $db->query("ROLLBACK");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                        }else{
                          # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                          $dbora = ConexaoOracle();

                          # Evita que Rollback não funcione #
                          $dbora->autoCommit(false);

                         # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                          $dbora->query("BEGIN TRANSACTION");

                          $TimeStamp            = $DataGravacao;
                          $DiaBaixa             = date("d");
                          $MesBaixa             = date("m");
                          $AnoBaixa             = date("Y");

                          //Obtendo os Sub-elementos de despesa
                          $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                          $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                          $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                          $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                          $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                          $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                          $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                          $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                          $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                          $sql .= "       MAT.CMATEPSEQU = $Material ";

                          $res  = $db->query($sql);

                          if( db::isError($res) ){
                              $RollBack = 1;
                              $db->query("ROLLBACK");
                              $db->query("END TRANSACTION");
                              $db->disconnect();
                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          } else {
                            #Preparando parâmetros para o lançamento de custo
                            $SubElementosDespesa = array();
                            $ValoresSubelementos = array();

                            #Preparando os dados para o lançamento de contábil
                            $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                            $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                            $Linha = $res->fetchRow();
                            $CGRUSEELE1 = $Linha[0];
                            $CGRUSEELE2 = $Linha[1];
                            $CGRUSEELE3 = $Linha[2];
                            $CGRUSEELE4 = $Linha[3];
                            $CGRUSESUBE = $Linha[4];

                            //TESTE
                            if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                              $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                              $ValorSubElemento = $QtdMovimentada*$Valor;

                              if(!in_array($Subelemento, $SubElementosDespesa)){
                                $indice = count($SubElementosDespesa);
                                $SubElementosDespesa[$indice] = $Subelemento;
                                $ValoresSubelementos[$indice] = $ValorSubElemento;
                              } else {
                                $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                              }
                            } else {
                              # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                              $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                              $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                              if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                              RedirecionaPost($Url);
                              exit;
                            }
                            //FIM TESTE

                            //ORIGINAL
                            // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                            // $ValorSubElemento = $QtdMovimentada*$Valor;


                            // $indice = count($SubElementosDespesa);
                            // if(!in_array($Subelemento, $SubElementosDespesa)){
                              // $SubElementosDespesa[$indice] = $Subelemento;
                              // $ValoresSubelementos[$indice] = $ValorSubElemento;
                            // } else {
                              // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                              // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                            // }
                            //ORIGINAL


                            //INICIO TESTE

                            //TESTE 2
                            $ValorContabilTESTE = $QtdMovimentada*$Valor;
                            $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                            if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                              $indice = count($EspecificacoesContabeis);
                              $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                              $ValoresContabeis[$indice] = $ValorContabilTESTE;
                            } else {
                              $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                              $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                            }
                            //FIM TESTE 2

                            //ORIGINAL
                            #Preparando parâmetros para o lançamento contábil
                            // $ValorContabil = $QtdMovimentada*$Valor;
                            // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                            //ORIGINAL

                            $ConfirmarInclusao = true;

                            //ORIGINAL
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             // $Movimentacao, $TipoMaterialTESTE,
                             // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                             // $Matricula, $Responsavel,
                             // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                             // $SubElementosDespesa, $ValoresSubelementos);
                            //ORIGINAL

                            //TESTE2
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                              // $Movimentacao, $TipoMaterialTESTE,
                              // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                              // $Matricula, $Responsavel,
                              // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                              // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                              // $SubElementosDespesa, $ValoresSubelementos,
                              // $EspecificacoesContabeis, $ValoresContabeis);
                            //FIM TESTE2

                            //TESTE 3
                            GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                   $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                   $EspecificacoesContabeis, $ValoresContabeis,
                                   $SubElementosDespesa, $ValoresSubelementos,
                                   $Matricula, $Responsavel,
                                   $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                   $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                            //FIM TESTE 3

                            //FIM TESTE
                            exit;
                          }
                        }
                      }
                    }
                  }
								}


						# CASO SEJA SAÍDA POR DOAÇÃO EXTERNA - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 24){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
              if($Mens==0){
                # INSERINDO MOVIMENTO #
                $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                $sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
                $sqlInsert .= ") VALUES ( ";
                $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                $sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                $sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                $sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
                $sqlInsert .= ");";
                $resInsert  = $db->query($sqlInsert);
                if( db::isError($resInsert) ){
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                }else{
                  # ATUALIZANDO ESTOQUE #
                  $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                  $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                  $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                  $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                  $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                  $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                  $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                  $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                  $resUpdate  = $db->query($sqlUpdate);
                  if( db::isError($resUpdate) ){
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                  }else{
                    # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                    $dbora = ConexaoOracle();

                    # Evita que Rollback não funcione #
                    $dbora->autoCommit(false);
                    # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                    $dbora->query("BEGIN TRANSACTION");

                    $TimeStamp            = $DataGravacao;
                    $DiaBaixa             = date("d");
                    $MesBaixa             = date("m");
                    $AnoBaixa             = date("Y");

                    //Obtendo os Sub-elementos de despesa
                    $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                    $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                    $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                    $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                    $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                    $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                    $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                    $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                    $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                    $sql .= "       MAT.CMATEPSEQU = $Material ";

                    $res  = $db->query($sql);

                    if( db::isError($res) ){
                        $RollBack = 1;
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                      #Preparando Parâmetros para o lançamento contábil
                      $SubElementosDespesa = array();
                      $ValoresSubelementos = array();

                      #Preparando os dados para o lançamento de contábil
                      $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                      $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                      $Linha = $res->fetchRow();
                      $CGRUSEELE1 = $Linha[0];
                      $CGRUSEELE2 = $Linha[1];
                      $CGRUSEELE3 = $Linha[2];
                      $CGRUSEELE4 = $Linha[3];
                      $CGRUSESUBE = $Linha[4];

                      //TESTE
                      if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                        $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                        $ValorSubElemento = $QtdMovimentada*$Valor;

                        if(!in_array($Subelemento, $SubElementosDespesa)){
                          $indice = count($SubElementosDespesa);
                          $SubElementosDespesa[$indice] = $Subelemento;
                          $ValoresSubelementos[$indice] = $ValorSubElemento;
                        } else {
                          $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                          $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                        }
                      } else {
                        # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                        $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                        $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                        if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                        RedirecionaPost($Url);
                        exit;
                      }
                      //FIM TESTE

                      //ORIGINAL
                      // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                      // $ValorSubElemento = $QtdMovimentada*$Valor;


                      // $indice = count($SubElementosDespesa);
                      // if(!in_array($Subelemento, $SubElementosDespesa)){
                        // $SubElementosDespesa[$indice] = $Subelemento;
                        // $ValoresSubelementos[$indice] = $ValorSubElemento;
                      // } else {
                        // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                      // }
                      //ORIGINAL

                      //INICIO TESTE

                      //ORIGINAL

                      //TESTE 2
                      $ValorContabilTESTE = $QtdMovimentada*$Valor;
                      $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                      if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                        $indice = count($EspecificacoesContabeis);
                        $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                        $ValoresContabeis[$indice] = $ValorContabilTESTE;
                      } else {
                        $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                        $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                      }
                      //FIM TESTE 2

                      #Preparando Parâmetros para o lançamento contábil
                      // $ValorContabil = $QtdMovimentada*$Valor;
                      // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                      //ORIGINAL

                      $ConfirmarInclusao = true;

                      //ORIGINAL
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                       // $Movimentacao, $TipoMaterialTESTE,
                       // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                       // $Matricula, $Responsavel,
                       // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                       // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                       // $SubElementosDespesa, $ValoresSubelementos);
                      //ORIGINAL

                      //TESTE2
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                        // $Movimentacao, $TipoMaterialTESTE,
                        // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                        // $Matricula, $Responsavel,
                        // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                        // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                        // $SubElementosDespesa, $ValoresSubelementos,
                        // $EspecificacoesContabeis, $ValoresContabeis);
                      //FIM TESTE2

                      //TESTE 3
                      GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                             $EspecificacoesContabeis, $ValoresContabeis,
                             $SubElementosDespesa, $ValoresSubelementos,
                             $Matricula, $Responsavel,
                             $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                      //FIM TESTE 3

                      //FIM TESTE
                      exit;
                    }
                  }
                }
              }


						# CASO SEJA SAÍDA POR ACERTO INVENTÁRIO #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 25){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
              if($Mens==0){
                # INSERINDO MOVIMENTO #
                $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                $sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
                $sqlInsert .= ") VALUES ( ";
                $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                $sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                $sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                $sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
                $sqlInsert .= ");";
                $resInsert  = $db->query($sqlInsert);
                if(db::isError($resInsert)){
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                }else{
                  # ATUALIZANDO ESTOQUE #
                  $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                  $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                  $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                  $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                  $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                  $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                  $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                  $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                  $resUpdate  = $db->query($sqlUpdate);
                  if( db::isError($resUpdate) ){
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                  }else{
                    # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                    $dbora = ConexaoOracle();

                    # Evita que Rollback não funcione #
                    $dbora->autoCommit(false);

                    # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                    $dbora->query("BEGIN TRANSACTION");

                    $TimeStamp            = $DataGravacao;
                    $DiaBaixa             = date("d");
                    $MesBaixa             = date("m");
                    $AnoBaixa             = date("Y");

                    //Obtendo os Sub-elementos de despesa
                    $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                    $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                    $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                    $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                    $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                    $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                    $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                    $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                    $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                    $sql .= "       MAT.CMATEPSEQU = $Material ";

                    $res  = $db->query($sql);

                    if( db::isError($res) ){
                        $RollBack = 1;
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                      #Preparando os parâmetros para o lançamento de custo
                      $SubElementosDespesa = array();
                      $ValoresSubelementos = array();

                      #Preparando os dados para o lançamento de contábil
                      $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                      $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                      $Linha = $res->fetchRow();
                      $CGRUSEELE1 = $Linha[0];
                      $CGRUSEELE2 = $Linha[1];
                      $CGRUSEELE3 = $Linha[2];
                      $CGRUSEELE4 = $Linha[3];
                      $CGRUSESUBE = $Linha[4];

                      //TESTE
                      if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                        $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                        $ValorSubElemento = $QtdMovimentada*$Valor;

                        if(!in_array($Subelemento, $SubElementosDespesa)){
                          $indice = count($SubElementosDespesa);
                          $SubElementosDespesa[$indice] = $Subelemento;
                          $ValoresSubelementos[$indice] = $ValorSubElemento;
                        } else {
                          $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                        }
                      } else {
                        # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                        $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                        $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                        if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                        RedirecionaPost($Url);
                        exit;
                      }
                      //FIM TESTE

                      //ORIGINAL
                      // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                      // $ValorSubElemento = $QtdMovimentada*$Valor;


                      // $indice = count($SubElementosDespesa);
                      // if(!in_array($Subelemento, $SubElementosDespesa)){
                        // $SubElementosDespesa[$indice] = $Subelemento;
                        // $ValoresSubelementos[$indice] = $ValorSubElemento;
                      // } else {
                        // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                      // }
                      //ORIGINAL


                      //INICIO TESTE

                      //TESTE 2
                      $ValorContabilTESTE = $QtdMovimentada*$Valor;
                      $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                      if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                        $indice = count($EspecificacoesContabeis);
                        $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                        $ValoresContabeis[$indice] = $ValorContabilTESTE;
                      } else {
                        $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                        $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                      }
                      //FIM TESTE 2

                      //ORIGINAL
                      #Preparando os parâmetros para o lançamento contábil
                      // $ValorContabil = $QtdMovimentada*$Valor;
                      // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                      //ORIGINAL


                      $ConfirmarInclusao = true;

                      //ORIGINAL
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                       // $Movimentacao, $TipoMaterialTESTE,
                       // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                       // $Matricula, $Responsavel,
                       // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                       // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                       // $SubElementosDespesa, $ValoresSubelementos);
                      //ORIGINAL

                      //TESTE2
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                        // $Movimentacao, $TipoMaterialTESTE,
                        // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                        // $Matricula, $Responsavel,
                        // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                        // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                        // $SubElementosDespesa, $ValoresSubelementos,
                        // $EspecificacoesContabeis, $ValoresContabeis);
                      //FIM TESTE2

                      //TESTE 3
                      GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                             $EspecificacoesContabeis, $ValoresContabeis,
                             $SubElementosDespesa, $ValoresSubelementos,
                             $Matricula, $Responsavel,
                             $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                      //FIM TESTE 3

                      //FIM TESTE
                      exit;
                    }
                  }
                }
              }


						# CASO SEJA ENTRADA/SAÍDA POR CANCELAMENTO DE MOVIMENTAÇÃO SEM RETORNO  - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and ($Movimentacao == 26 or $Movimentacao == 27)){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              # Verifica a continuidade da existência da movimentação sem inversa, e bloqueia #
              # para outro usuário simutâneo não criar uma movimentação inversa um pouco antes. #
              # Caso outro usuário já tenha criado tal movimentação, a flag de correspondência #
              # já foi setada, exibindo a mensagem abaixo, ou ele ainda não commitou, mas já #
              # bloqueou, fazendo este select abaixo dá erro #
              $sql    = "SELECT CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, CTIPMVCODI ";
              $sql   .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL ";
              $sql   .= " WHERE AMOVMAANOM = $AnoAtualizar ";
              $sql   .= "   AND CALMPOCODI = $Almoxarifado ";
              $sql   .= "   AND CMOVMACODI = $SeqMovimentacao ";
              $sql   .= "   AND CMATEPSEQU = $Material ";
              $sql   .= "   AND FMOVMACORR IS NULL ";
              $sql   .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
              $sql   .= "   FOR UPDATE ";
              $res    = $db->query($sql);
              if(db::isError($res)){
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
              }else{
                $NumReg = $res->numRows();
                if($NumReg == 0){
                    $Mensagem = "Uma Saída para Cancelamento de Movimentacão sem Retorno já foi cadastrada para esta movimentação, provavelmente por um usuário usando o sistema simutaneamente";
                    $Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2&Troca=2";
                    if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                    header("location: ".$Url);
                    exit;
                }else{
                  $MovCorrespondente = $res->fetchRow();
                  $MovCorrespondenteAlm = $MovCorrespondente[0];
                  $MovCorrespondenteAno = $MovCorrespondente[1];
                  $MovCorrespondenteCod = $MovCorrespondente[2];
                  $MovCorrespondenteTip = $MovCorrespondente[3];
                  # REMOVE FLAG DE CORRESPONDENTE DA MOVIMENTACAO INICIALMENTE CANCELADA #
                  if ($MovCorrespondenteTip==26 or $MovCorrespondenteTip==27){
                      $sqlMovCor  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL ";
                      $sqlMovCor .= "   SET FMOVMACORR = NULL ";
                      $sqlMovCor .= " WHERE CALMPOCODI = $MovCorrespondenteAlm ";
                      $sqlMovCor .= "   AND AMOVMAANOM = $MovCorrespondenteAno ";
                      $sqlMovCor .= "   AND CMOVMACODI = $MovCorrespondenteCod";
                      $sqlMovCor .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $sqlMovCor .= "   AND FMOVMACORR = 'S' ";
                      $resMovCor  = $db->query($sqlMovCor);
                      if( db::isError($resMovCor) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovCor");
                      }
                  }

                  $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
                  if($Mens==0){
                    # INSERINDO MOVIMENTO #
                    $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                    $sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                    $sqlInsert .= " CTIPMVCODI, CMATEPSEQU, AMOVMAQTDM, ";
                    $sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                    $sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP, ";
                    $sqlInsert .= " CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";
                    if ($MovCorrespondenteTip==26 or $MovCorrespondenteTip==27){
                        $sqlInsert .= ", FMOVMACORR ";
                    }
                    $sqlInsert .= ") VALUES ( ";
                    $sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                    $sqlInsert .= " $Movimentacao, $Material, $QtdMovimentada, ";
                    $sqlInsert .= " $Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                    $sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
                    $sqlInsert .= " $Almoxarifado, $AnoAtualizar, $SeqMovimentacao, '$Observacao' ";
                    if ($MovCorrespondenteTip==26 or $MovCorrespondenteTip==27){
                        $sqlInsert .= ", 'S' ";
                    }
                    $sqlInsert .= ")";
                    $resInsert  = $db->query($sqlInsert);
                    if( db::isError($resInsert) ){
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                    }else{
                      # ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM - 10, 14, 16, 17, 23 ou 24 #
                      $sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' ";
                      if ($MovCorrespondenteTip==26 or $MovCorrespondenteTip==27){
                          $sqlMovOri .= ", CALMPOCOD1 = $Almoxarifado ";
                          $sqlMovOri .= ", AMOVMAANO1 = $AnoMovimentacao ";
                          $sqlMovOri .= ", CMOVMACOD1 = $MovAnoSequ ";
                      }
                      $sqlMovOri .= " WHERE CALMPOCODI = $Almoxarifado ";
                      $sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
                      $sqlMovOri .= "   AND CMOVMACODI = $SeqMovimentacao";
                      $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $sqlMovOri .= "   AND FMOVMACORR IS NULL ";
                      $resMovOri  = $db->query($sqlMovOri);
                      if( db::isError($resMovOri) ){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
                      }else{
                        # ATUALIZANDO ESTOQUE #
                        $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                        $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                        $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                        $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                        $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                        $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                        $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                        $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                        $resUpdate  = $db->query($sqlUpdate);
                        if( db::isError($resUpdate) ){
                            $db->query("ROLLBACK");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                        }else{
                          # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                          $dbora = ConexaoOracle();

                          # Evita que Rollback não funcione #
                          $dbora->autoCommit(false);
                          # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                          $dbora->query("BEGIN TRANSACTION");

                          $TimeStamp            = $DataGravacao;
                          $DiaBaixa             = date("d");
                          $MesBaixa             = date("m");
                          $AnoBaixa             = date("Y");

                          //Obtendo os Sub-elementos de despesa
                          $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                          $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                          $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                          $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                          $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                          $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                          $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                          $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                          $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                          $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                          $sql .= "       MAT.CMATEPSEQU = $Material ";

                          $res  = $db->query($sql);

                          if( db::isError($res) ){
                              $RollBack = 1;
                              $db->query("ROLLBACK");
                              $db->query("END TRANSACTION");
                              $db->disconnect();
                              ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          } else {
                            #Preparando os parâmetros para o lançamento de custo.
                            $SubElementosDespesa = array();
                            $ValoresSubelementos = array();

                            #Preparando os dados para o lançamento de contábil
                            $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                            $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                            $Linha = $res->fetchRow();
                            $CGRUSEELE1 = $Linha[0];
                            $CGRUSEELE2 = $Linha[1];
                            $CGRUSEELE3 = $Linha[2];
                            $CGRUSEELE4 = $Linha[3];
                            $CGRUSESUBE = $Linha[4];

                            //TESTE
                            if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                              $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                              $ValorSubElemento = $QtdMovimentada*$Valor;

                              if(!in_array($Subelemento, $SubElementosDespesa)){
                                $indice = count($SubElementosDespesa);
                                $SubElementosDespesa[$indice] = $Subelemento;
                                $ValoresSubelementos[$indice] = $ValorSubElemento;
                              } else {
                                $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                              }
                            } else {
                              # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                              $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                              $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                              if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                              RedirecionaPost($Url);
                              exit;
                            }
                            //FIM TESTE

                            //ORIGINAL
                            // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                            // $ValorSubElemento = $QtdMovimentada*$Valor;


                            // $indice = count($SubElementosDespesa);
                            // if(!in_array($Subelemento, $SubElementosDespesa)){
                              // $SubElementosDespesa[$indice] = $Subelemento;
                              // $ValoresSubelementos[$indice] = $ValorSubElemento;
                            // } else {
                              // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                              // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                            // }
                            //ORIGINAL

                            //INICIO TESTE

                            //TESTE 2
                            $ValorContabilTESTE = $QtdMovimentada*$Valor;
                            $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                            if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                              $indice = count($EspecificacoesContabeis);
                              $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                              $ValoresContabeis[$indice] = $ValorContabilTESTE;
                            } else {
                              $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                              $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                            }
                            //FIM TESTE 2

                            //ORIGINAL
                            #Preparando os parâmetros para o lançamento contábil
                            // $ValorContabil = $QtdMovimentada*$Valor;
                            // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                            //ORIGINAL

                            $ConfirmarInclusao = true;

                            //ORIGINAL
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             // $Movimentacao, $TipoMaterialTESTE,
                             // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                             // $Matricula, $Responsavel,
                             // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                             // $SubElementosDespesa, $ValoresSubelementos); //NOVO TESTE
                            //ORIGINAL

                            //TESTE2
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                              // $Movimentacao, $TipoMaterialTESTE,
                              // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                              // $Matricula, $Responsavel,
                              // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                              // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                              // $SubElementosDespesa, $ValoresSubelementos,
                              // $EspecificacoesContabeis, $ValoresContabeis);
                            //FIM TESTE2

                            //TESTE 3
                            GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                   $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                   $EspecificacoesContabeis, $ValoresContabeis,
                                   $SubElementosDespesa, $ValoresSubelementos,
                                   $Matricula, $Responsavel,
                                   $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                   $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                            //FIM TESTE 3

                            //FIM TESTE
                            exit;
                          }
                        }
                      }
                    }
                  }
                }
              }


						# CASO SEJA ENTRADA POR ACERTO INVENTÁRIO #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 28){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              $Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
              if($Mens==0){
                # INSERINDO MOVIMENTO #
                $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                $sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                $sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                $sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                $sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP, EMOVMAOBSE ";
                $sqlInsert .= ") VALUES ( ";
                $sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                $sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                $sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
                $sqlInsert .= "$MovNumero, $Matricula, '$Responsavel', '$Observacao' ";
                $sqlInsert .= ");";
                $resInsert  = $db->query($sqlInsert);
                if(db::isError($resInsert)){
                    $CodErro  = $resInsert->getCode();
                    $DescErro = $resInsert->getMessage();
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert - $DescErro ($CodErro)");
                }else{
                  # ATUALIZANDO ESTOQUE #
                  $sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
                  $sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                  $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
                  $sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
                  $sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
                  $sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
                  $sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
                  $sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
                  $resUpdate  = $db->query($sqlUpdate);
                  if( db::isError($resUpdate) ){
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                  }else{
                    # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                    $dbora = ConexaoOracle();

                    # Evita que Rollback não funcione #
                    $dbora->autoCommit(false);
                    # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                    $dbora->query("BEGIN TRANSACTION");

                    $TimeStamp            = $DataGravacao;
                    $DiaBaixa             = date("d");
                    $MesBaixa             = date("m");
                    $AnoBaixa             = date("Y");

                    //Obtendo os Sub-elementos de despesa
                    $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                    $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                    $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                    $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                    $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                    $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                    $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                    $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                    $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                    $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                    $sql .= "       MAT.CMATEPSEQU = $Material ";

                    $res  = $db->query($sql);

                    if( db::isError($res) ){
                        $RollBack = 1;
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                      #Preparando os parâmetros para o lançamento de custo
                      $SubElementosDespesa = array();
                      $ValoresSubelementos = array();

                      #Preparando os dados para o lançamento de contábil
                      $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                      $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

                      $Linha = $res->fetchRow();
                      $CGRUSEELE1 = $Linha[0];
                      $CGRUSEELE2 = $Linha[1];
                      $CGRUSEELE3 = $Linha[2];
                      $CGRUSEELE4 = $Linha[3];
                      $CGRUSESUBE = $Linha[4];

                      //TESTE
                      if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                        $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                        $ValorSubElemento = $QtdMovimentada*$Valor;

                        if(!in_array($Subelemento, $SubElementosDespesa)){
                          $indice = count($SubElementosDespesa);
                          $SubElementosDespesa[$indice] = $Subelemento;
                          $ValoresSubelementos[$indice] = $ValorSubElemento;
                        } else {
                          $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                          $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                        }
                      } else {
                        # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                        $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                        $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                        if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                        RedirecionaPost($Url);
                        exit;
                      }
                      //FIM TESTE

                      //ORIGINAL
                      // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                      // $ValorSubElemento = $QtdMovimentada*$Valor;


                      // $indice = count($SubElementosDespesa);
                      // if(!in_array($Subelemento, $SubElementosDespesa)){
                        // $SubElementosDespesa[$indice] = $Subelemento;
                        // $ValoresSubelementos[$indice] = $ValorSubElemento;
                      // } else {
                        // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        // $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                      // }
                      //ORIGINAL

                      //INICIO TESTE

                      //TESTE 2
                      $ValorContabilTESTE = $QtdMovimentada*$Valor;
                      $ValorContabilTESTE = sprintf("%01.2f",round($ValorContabilTESTE,2));

                      if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                        $indice = count($EspecificacoesContabeis);
                        $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                        $ValoresContabeis[$indice] = $ValorContabilTESTE;
                      } else {
                        $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                        $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                      }
                      //FIM TESTE 2

                      //ORIGINAL
                      #Preparando os dados para o lançamento contábil
                      // $ValorContabil = $QtdMovimentada*$Valor;
                      // $ValorContabil = sprintf("%01.2f",round($ValorContabil,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                      //ORIGINAL

                      $ConfirmarInclusao = true;

                      //ORIGINAL
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                       // $Movimentacao, $TipoMaterialTESTE,
                       // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                       // $Matricula, $Responsavel,
                       // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                       // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                       // $SubElementosDespesa, $ValoresSubelementos);
                      //ORIGINAL

                      //TESTE2
                      // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                        // $Movimentacao, $TipoMaterialTESTE,
                        // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                        // $Matricula, $Responsavel,
                        // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                        // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                        // $SubElementosDespesa, $ValoresSubelementos,
                        // $EspecificacoesContabeis, $ValoresContabeis);
                      //FIM TESTE2

                      //TESTE 3
                      GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                             $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                             $EspecificacoesContabeis, $ValoresContabeis,
                             $SubElementosDespesa, $ValoresSubelementos,
                             $Matricula, $Responsavel,
                             $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                             $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                      //FIM TESTE 3

                      //FIM TESTE
                      exit;
                    }
                  }
                }
              }

						# CASO SEJA ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS - Gera Custo #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 29){
              # Abre a conexão com banco de dados #
              $db = Conexao();
              # INICIANDO TRANSAÇÃO #
              $db->query("BEGIN TRANSACTION");
              # VERIFICANDO Se O Material Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
              $sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
              $sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
              $sqlChkExist .= "   FOR UPDATE ";
              $resChkExist  = $db->query($sqlChkExist);
              if( db::isError($resChkExist) ){
                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
              }else{
                $QtdChkExist   = $resChkExist->numRows();
                $LinhaChkExist = $resChkExist->fetchRow();
                if($QtdChkExist == 0 or $LinhaChkExist[0] == 0){
                    $ValorMat1Arma = $Valor;            // Se Não Existe, o valor médio na tabela de movimentação recebe o valor do Almoxarifado Secundário
                }else{
                    $ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
                }
                if(!ChecaNaoExistenciaCanc($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                    $Mens      = 1;
                    $Tipo      = 2;
                    $Mensagem  = "O Material não pode mais ser recebido, pois a Movimentação de Saída foi Cancelada pelo outro Almoxarifado";
                    $db->query("ROLLBACK");
                    $db->query("END TRANSACTION");
                    $db->disconnect();
                }else{
                  if(!ChecaNaoExistenciaFlag($AlmoxSec, $AnoAtualizar, $SeqMovimentacao, $db)){
                      $Mens      = 1;
                      $Tipo      = 2;
                      $Mensagem  = "O Material já foi recebido, provavelmente por outro usuário usando o sistema simultaneamente";
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                  }else{
                    # INSERINDO MOVIMENTO #
                    $sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
                    $sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
                    $sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
                    $sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
                    $sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP,  ";
                    $sqlInsert .= " CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";            // ALMOX SECUNDÁRIO
                    $sqlInsert .= ") VALUES ( ";
                    $sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
                    $sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
                    $sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
                    $sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', ";
                    $sqlInsert .= " $AlmoxSec, $AnoAtualizar, $SeqMovimentacao, '$Observacao' "; // VALOR DO ALMOX SECUNDÁRIO
                    $sqlInsert .= ")";
                    $resInsert  = $db->query($sqlInsert);
                    if(db::isError($resInsert)){
                        $db->query("ROLLBACK");
                        $db->query("END TRANSACTION");
                        $db->disconnect();
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                    }else{
                      # ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM #
                      $sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S', CMOVMACOD1 = $MovAnoSequ ";
                      $sqlMovOri .=	" WHERE CALMPOCODI = $AlmoxSec ";
                      $sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
                      $sqlMovOri .=	"	  AND CMOVMACODI = $SeqMovimentacao ";
                      $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                      $resMovOri	= $db->query($sqlMovOri);
                      if(db::isError($resMovOri)){
                          $db->query("ROLLBACK");
                          $db->query("END TRANSACTION");
                          $db->disconnect();
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
                      }else{
                        # Descobrindo o sequencial da movimentação de origem #
                        $sqlmovseq  = "SELECT CMOVMACODT FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                        $sqlmovseq .= " WHERE	CALMPOCODI = $AlmoxSec ";
                        $sqlmovseq .= "	  AND AMOVMAANOM = $AnoAtualizar ";
                        $sqlmovseq .= "   AND CMOVMACODI = $SeqMovimentacao ";
                        $sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
                        $resmovseq  = $db->query($sqlmovseq);
                        if(db::isError($resmovseq)){
                            $db->query("ROLLBACK");
                            $db->query("END TRANSACTION");
                            $db->disconnect();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmovseq");
                        }else{
                          $LinhaSeqMov  = $resmovseq->fetchRow();
                          $MovNumeroSec = $LinhaSeqMov[0];
                          if($QtdChkExist == 0){ // Se não há o material 1 no estoque do almoxarifado, Insere, se há, Atualiza a quantidade
                              # INSERINDO ESTOQUE #
                              $sqlInsert  = " INSERT INTO SFPC.TBARMAZENAMENTOMATERIAL ( ";
                              $sqlInsert .= " CMATEPSEQU, CLOCMACODI, AARMATQTDE, AARMATMAXI, ";
                              $sqlInsert .= " AARMATESTS, AARMATESTR, AARMATVIRT, AARMATESTC, ";
                              $sqlInsert .= " AARMATNIVR, AARMATPONT, VARMATUMED, VARMATULTC, ";
                              $sqlInsert .= " CGREMPCODI, CUSUPOCODI, TARMATULAT ";
                              $sqlInsert .= " ) VALUES ( ";
                              $sqlInsert .= " $Material, $Localizacao, $QtdMovimentada, NULL, ";
                              $sqlInsert .= " NULL, NULL, NULL, NULL, ";
                              $sqlInsert .= " NULL, NULL, $Valor, $Valor, ";
                              $sqlInsert .= " $GrupoEmp, $Usuario, '$DataGravacao' )";
                              $resInsert  = $db->query($sqlInsert);
                              if( db::isError($resInsert) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
                              }
                          }else{
                              # ATUALIZANDO ESTOQUE #
                              $sqlUpdate  =	"UPDATE SFPC.TBARMAZENAMENTOMATERIAL SET VARMATUMED = $ValorMat1Arma, VARMATULTC = $ValorMat1Arma, AARMATQTDE = AARMATQTDE + $QtdMovimentada ";
                              $sqlUpdate .=	" WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
                              $resUpdate  = $db->query($sqlUpdate);
                              if( db::isError($resUpdate) ){
                                  $RollBack = 1;
                                  $db->query("ROLLBACK");
                                  $db->query("END TRANSACTION");
                                  $db->disconnect();
                                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
                              }
                          }

                          if($RollBack != 1){
                            # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                            $dbora = ConexaoOracle();

                            # Evita que Rollback não funcione #
                            $dbora->autoCommit(false);

                            # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                            $dbora->query("BEGIN TRANSACTION");

                            $TimeStamp                   = $DataGravacao;
                            $DiaBaixa                    = date("d");
                            $MesBaixa                    = date("m");
                            $AnoBaixa                    = date("Y");

                            //Obtendo os Sub-elementos de despesa
                            $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                            $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                            $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                            $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                            $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                            $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                            $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                            $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                            $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                            $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                            $sql .= "       MAT.CMATEPSEQU = $Material ";

                            $res  = $db->query($sql);

                            if( db::isError($res) ){
                                $RollBack = 1;
                                $db->query("ROLLBACK");
                                $db->query("END TRANSACTION");
                                $db->disconnect();
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                            } else {
                              #Preparando parâmetros para o lançamento de custo
                              $SubElementosDespesa = array();
                              $ValoresSubelementosSaida = array();
                              $ValoresSubelementosEntrada = array();

                              #Preparando os dados para o lançamento de contábil
                              $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
                              $ValoresContabeisSaida = array();  //valores contábeis de saída conforme o tipo do material (Consumo ou Permanente)
                              $ValoresContabeisEntrada = array();  //valores contábeis de entrada conforme o tipo do material (Consumo ou Permanente)

                              $Linha = $res->fetchRow();
                              $CGRUSEELE1 = $Linha[0];
                              $CGRUSEELE2 = $Linha[1];
                              $CGRUSEELE3 = $Linha[2];
                              $CGRUSEELE4 = $Linha[3];
                              $CGRUSESUBE = $Linha[4];

                              //TESTE
                              if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                                $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                                $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                                $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                                if(!in_array($Subelemento, $SubElementosDespesa)){
                                  $indice = count($SubElementosDespesa);
                                  $SubElementosDespesa[$indice] = $Subelemento;
                                  $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                                } else {
                                  $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                                  $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                  $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                                }
                              } else {
                                # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                                $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                                $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                                if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                                RedirecionaPost($Url);
                                exit;
                              }
                              //FIM TESTE

                              //ORIGINAL
                              // $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";

                              // $ValorSubElementoSaida      = $QtdMovimentada*$Valor;
                              // $ValorSubElementoEntrada    = $QtdMovimentada*$ValorMat1Arma;

                              // $indice = count($SubElementosDespesa);
                              // if(!in_array($Subelemento, $SubElementosDespesa)){
                                // $SubElementosDespesa[$indice] = $Subelemento;

                                // $ValoresSubelementosSaida[$indice] = $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indice] = $ValorSubElementoEntrada;
                              // } else {
                                // $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.

                                // $ValoresSubelementosSaida[$indExist] = $ValoresSubelementosSaida[$indExist] + $ValorSubElementoSaida;
                                // $ValoresSubelementosEntrada[$indExist] = $ValoresSubelementosEntrada[$indExist] + $ValorSubElementoEntrada;
                              // }
                              //ORIGINAL


                              //INICIO TESTE
                              #Preparando parâmetros para o lançamento contábil

                              //ORIGINAL
                              // $ValorContabilSaida = $QtdMovimentada*$Valor;
                              // $ValorContabilSaida = sprintf("%01.2f",round($ValorContabilSaida,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim

                              // $ValorContabilEntrada = $QtdMovimentada*$ValorMat1Arma;
                              // $ValorContabilEntrada = sprintf("%01.2f",round($ValorContabilEntrada,2));    // Com duas casas após a vírgula, pois a tabela do oracle ainda trabalha assim
                              //ORIGINAL

                              //TESTE 2
                              $ValorContabilTESTESaida = $QtdMovimentada*$Valor;
                              $ValorContabilTESTESaida = sprintf("%01.2f",round($ValorContabilTESTESaida,2));

                              $ValorContabilTESTEEntrada = $QtdMovimentada*$ValorMat1Arma;
                              $ValorContabilTESTEEntrada = sprintf("%01.2f",round($ValorContabilTESTEEntrada,2));

                              if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                                $indice = count($EspecificacoesContabeis);
                                $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                                $ValoresContabeisSaida[$indice] = $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indice] = $ValorContabilTESTEEntrada;
                              } else {
                                $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                                $ValoresContabeisSaida[$indExist] = $ValoresContabeisSaida[$indExist] + $ValorContabilTESTESaida;
                                $ValoresContabeisEntrada[$indExist] = $ValoresContabeisEntrada[$indExist] + $ValorContabilTESTEEntrada;
                              }
                              //FIM TESTE 2

                              $ConfirmarInclusao = false;

                               //Para as movimentações conjuntas (6,9,11,29 e 12,13,15,30) Primeiro é gerado a movimentação de saída (12,13,15,30) e após isso a movimentação de entrada (6,9,11,29)
                              //Gera Lançamento contabil para a movimentação 30 -  Saída POR DOAÇÃO ENTRE ALMOXARIFADOS

                              //ORIGINAL
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilSaida,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida);
                              //ORIGINAL

                              //TESTE 2
                              // GerarLancamentoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                               // $MovimentacaoSecun, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $MatriculaSecun, $ResponsavelSecun,
                               // $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosSaida,
                               // $EspecificacoesContabeis, $ValoresContabeisSaida);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($OrgaoSecun, $RPASecun, $UnidadeSecun, $CentroCusto, $Detalhamento,
                                     $MovimentacaoSecun, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisSaida,
                                     $SubElementosDespesa, $ValoresSubelementosSaida,
                                     $MatriculaSecun, $ResponsavelSecun,
                                     $SeqRequisicao, $AlmoxSec, $MovNumeroSec, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              $ConfirmarInclusao = true; //Confirmar a inclusão após inserir a 1ª movimentação para as movimentações conjuntas (6,9,11,29 e 12,13,15,30)

                              //Gera Lançamento contabil para a movimentação 29 -  ENTRADA POR DOAÇÃO ENTRE ALMOXARIFADOS

                              //ORIGINAL
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabilEntrada,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada);
                              //ORIGINAL

                              //TESTE 2
                              // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                               // $Movimentacao, $TipoMaterialTESTE,
                               // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                               // $Matricula, $Responsavel,
                               // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                               // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                               // $SubElementosDespesa, $ValoresSubelementosEntrada,
                               // $EspecificacoesContabeis, $ValoresContabeisEntrada);
                              //FIM TESTE 2

                              //TESTE 3
                              GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                     $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                     $EspecificacoesContabeis, $ValoresContabeisEntrada,
                                     $SubElementosDespesa, $ValoresSubelementosEntrada,
                                     $Matricula, $Responsavel,
                                     $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                     $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                              //FIM TESTE 3

                              //FIM TESTE

                              exit;
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }


						# CASO SEJA SAÍDA POR DOAÇÃO ENTRE ALMOXARIFADOS #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 30){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								$Valor = PegaValor($Material, $Localizacao, $Valor, $PedeValor, $db);
								if($Mens==0){
										# INSERINDO MOVIMENTO #
										$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
										$sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
										$sqlInsert .= "CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
										$sqlInsert .= "VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
										$sqlInsert .= "CMOVMACODT, AMOVMAMATR, NMOVMARESP,  ";
										$sqlInsert .= "CALMPOCOD1, EMOVMAOBSE ";     // ALMOX SECUNDÁRIO
										$sqlInsert .= ") VALUES ( ";
										$sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
										$sqlInsert .= "$Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
										$sqlInsert .= "$Valor, $Valor, $GrupoEmp, $Usuario, '$DataGravacao', ";
										$sqlInsert .= "$MovNumero, $Matricula, '$Responsavel' ";
										$sqlInsert .= ", $AlmoxSec, '$Observacao' "; // VALOR DO ALMOX SECUNDÁRIO
										$sqlInsert .= ");";
										$resInsert  = $db->query($sqlInsert);
										if( db::isError($resInsert) ){
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
										}else{
												# ATUALIZANDO ESTOQUE #
												$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
												$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                        $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
												$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
												$sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
												$sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
												$sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
												$sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
												$resUpdate  = $db->query($sqlUpdate);
												if( db::isError($resUpdate) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
												}else{
														# Fechando a transação e a conexão #
														$db->query("COMMIT");
														GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
														$db->query("END TRANSACTION");
														$db->disconnect();
														# EXIBINDO MENSAGEM #
														$Mensagem = "Movimentação Inserida com Sucesso";
														$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}
										}
								}


						# CASO SEJA ENTRADA POR CANCELAMENTO DE MOVIMENTAÇÃO #
						}elseif($MetodoDeChamada == "POST" and $Movimentacao == 31){
								# Abre a conexão com banco de dados #
								$db = Conexao();
								# INICIANDO TRANSAÇÃO #
								$db->query("BEGIN TRANSACTION");
								# VERIFICANDO Se O Material Existe (trava se existir, para o valor não ser alterado por movimentação simutânea) - Almoxarifado #
								$sqlChkExist  = "SELECT VARMATUMED FROM SFPC.TBARMAZENAMENTOMATERIAL ";
								$sqlChkExist .= " WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao ";
								$sqlChkExist .= "   FOR UPDATE ";
								$resChkExist  = $db->query($sqlChkExist);
								if( db::isError($resChkExist) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlChkExist");
								}else{
										$QtdChkExist = $resChkExist->numRows();
										if ($QtdChkExist == 0) {
												$ValorMat1Arma = $Valor;            // Se Não Existe, o valor médio na tabela de movimentação recebe o valor da movimentação anterior que está sendo cancelada por esta movimentação
										}else{
												$LinhaChkExist = $resChkExist->fetchRow();
												$ValorMat1Arma = $LinhaChkExist[0]; // Se Existe, o valor médio na tabela de movimentação recebe o valor do armazenamento do Almoxarifado Atual
										}
										if(!ChecaNaoExistenciaCanc($Almoxarifado, $AnoAtualizar, $SeqMovimentacao, $db)){ // Checa se houve cancelamento simutâneo
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem  = "Esta Movimentação já foi Cancelada, provavelmente por outro usuário usando o sistema simultaneamente";
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
										}else{
												if(!ChecaNaoExistenciaFlag($Almoxarifado, $AnoAtualizar, $SeqMovimentacao, $db)){
														$Mens      = 1;
														$Tipo      = 2;
														$Mensagem  = "O Material já foi recebido pelo outro Almoxarifado. Esta movimentação não pode mais ser cancelada";
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
												}else{
														# INSERINDO MOVIMENTO #
														$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
														$sqlInsert .= " CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, ";
														$sqlInsert .= " CTIPMVCODI, CREQMASEQU, CMATEPSEQU, AMOVMAQTDM, ";
														$sqlInsert .= " VMOVMAVALO, VMOVMAUMED, CGREMPCODI, CUSUPOCODI, TMOVMAULAT, ";
														$sqlInsert .= " CMOVMACODT, AMOVMAMATR, NMOVMARESP, CALMPOCOD1, ";
														$sqlInsert .= " AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";
														$sqlInsert .= ") VALUES ( ";
														$sqlInsert .= " $Almoxarifado, $AnoMovimentacao, $MovAnoSequ, '".date("Y-m-d")."', ";
														$sqlInsert .= " $Movimentacao, $SeqRequisicao, $Material, $QtdMovimentada, ";
														$sqlInsert .= " $ValorMat1Arma, $ValorMat1Arma, $GrupoEmp, $Usuario, '$DataGravacao', ";
														$sqlInsert .= " $MovNumero, $Matricula, '$Responsavel', $Almoxarifado, ";
														$sqlInsert .= " $AnoAtualizar, $SeqMovimentacao, '$Observacao' ";
														$sqlInsert .= ")";
														$resInsert  = $db->query($sqlInsert);
														if(db::isError($resInsert)){
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
														}else{
																# ATUALIZANDO MOVIMENTAÇÃO DE ORIGEM #
																$sqlMovOri  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' ";
																$sqlMovOri .=	" WHERE CALMPOCODI = $Almoxarifado ";
																$sqlMovOri .= "   AND AMOVMAANOM = $AnoAtualizar ";
																$sqlMovOri .=	"	  AND CMOVMACODI = $SeqMovimentacao ";
																$sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																$resMovOri	= $db->query($sqlMovOri);
																if(db::isError($resMovOri)){
																		$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
																}else{
																		# Verifica se a movimentação que está sendo cancelada é uma Saída por Devolução de Empréstimo #
																		$sqlDevEmp  = "SELECT CTIPMVCODI, CALMPOCOD1, AMOVMAANO1, CMOVMACOD1 FROM SFPC.TBMOVIMENTACAOMATERIAL "; // Campos com final 1, são os campos da movimentação 12
																		$sqlDevEmp .=	" WHERE CALMPOCODI = $Almoxarifado ";
																		$sqlDevEmp .= "   AND AMOVMAANOM = $AnoAtualizar ";
																		$sqlDevEmp .=	"	  AND CMOVMACODI = $SeqMovimentacao ";
																		$sqlDevEmp .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																		$resDevEmp  = $db->query($sqlDevEmp);
																		if(db::isError($resDevEmp)){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDevEmp");
																		}else{
																				$MovCanc      = $resDevEmp->fetchRow();
																				$TipMvCanc    = $MovCanc[0];
																				$AlmSecMvCanc = $MovCanc[1];
																				$AnoSecMvCanc = $MovCanc[2];
																				$MovSecMvCanc = $MovCanc[3];
																				if ($TipMvCanc == 13) {
																						# Se for, além de marcar a correspondência da movimentação 13(antes), desmarca a correspondência da movimentação 6
																						$sqlMovOri  =	"UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'N' ";
																						$sqlMovOri .=	" WHERE CALMPOCOD1 = $AlmSecMvCanc ";
																						$sqlMovOri .= "   AND AMOVMAANO1 = $AnoSecMvCanc ";
																						$sqlMovOri .=	"	  AND CMOVMACOD1 = $MovSecMvCanc ";
																						$sqlMovOri .=	"	  AND CTIPMVCODI = 6 ";
																						$sqlMovOri .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																						$resMovOri	= $db->query($sqlMovOri);
																						if( db::isError($resMovOri) ){
																								$RollBack = 1;
																								$db->query("ROLLBACK");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMovOri");
																						}
																				}
																				if($RollBack != 1){
																						# ATUALIZANDO ESTOQUE #
																						$sqlUpdate  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
																						$sqlUpdate .= "   SET AARMATQTDE = $QtdFinal, ";
                                            $sqlUpdate .= "       AARMATESTR = $QtdFinalReal, ";
																						$sqlUpdate .= "       CGREMPCODI = $GrupoEmp, ";
																						$sqlUpdate .= "       CUSUPOCODI = $Usuario, ";
																						$sqlUpdate .= "       TARMATULAT = '$DataGravacao' ";
																						$sqlUpdate .= " WHERE CMATEPSEQU = $Material ";
																						$sqlUpdate .= "   AND CLOCMACODI = $Localizacao ";
																						$resUpdate  = $db->query($sqlUpdate);
																						if( db::isError($resUpdate) ){
																								$db->query("ROLLBACK");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlUpdate");
																						}else{
																								# Fechando a transação e a conexão #
																								$db->query("COMMIT");
																								GravaSessionChkF5($Almoxarifado, $AnoMovimentacao, $Movimentacao, $Material, $QtdMovimentada, $GrupoEmp, $Usuario, $DataGravacao);
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								# EXIBINDO MENSAGEM #
																								$Mensagem = "Movimentação Inserida com Sucesso";
																								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
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
						}else{
								# EXIBINDO MENSAGEM DE ERRO - Pois a movimentação existe no banco mas ainda não foi prevista por este programa #
								$Mensagem = "Esta Movimentação não poderá ser Inserida pois ainda não foi prevista";
								$Url = "CadMovimentacaoIncluir.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}


						# FIM DO BLOCO DE EXECUÇÃO DAS NOVAS SITUAÇÕES DE MOVIMENTO #

						# ***************  INCLUSÕES E ATUALIZAÇÕES - FIM **************** #

				} // Else da Checagem de F5
		} // if($Mens == 0)
} // if($Botao == "Incluir")
?>

<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
		document.CadMovimentacaoConfirmar.Botao.value = valor;
		document.CadMovimentacaoConfirmar.submit();
}
<?php MenuAcesso(); ?>
function ncaracteresO(valor){
	document.CadMovimentacaoConfirmar.NCaracteresO.value = '' +  document.CadMovimentacaoConfirmar.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadMovimentacaoConfirmar.NCaracteresO.focus();
	}
}
//-->
</script>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMovimentacaoConfirmar.php" method="post" name="CadMovimentacaoConfirmar">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Incluir
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro ou Sucesso -->
	<?php if ( ($Mens == 1) ) {?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro ou Sucesso -->

	<!-- Corpo -->
	<tr>
	<td width="150"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INCLUIR - MOVIMENTAÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para Incluir a Movimentação do Material, confira os dados abaixo, digite a quantidade a ser movimentada e clique no botão "Incluir". Para voltar à tela anterior clique no botão "Voltar".
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
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL";
												$sql .= " WHERE CALMPOCODI = $Almoxarifado AND FALMPOSITU = 'A'";
												$res  = $db->query($sql);
												if( db::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização</td>
											<td class="textonormal">
												<?php
												# Mostra a Localização de Acordo com o Almoxarifado #
												$db   = Conexao();
												$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
												$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
												$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
												$sql .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														if( $Linha[0] == "E" ){
																$Equipamento = "ESTANTE";
														}if( $Linha[0] == "A" ){
																$Equipamento = "ARMÁRIO";
														}if( $Linha[0] == "P" ){
																$Equipamento = "PALETE";
														}
														$DescArea = $Linha[4];
														echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
														echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Tipo de Movimentação</td>
											<td class="textonormal">
												<?php
												# Mostra o tipo de Movimentação #
												if( $TipoMovimentacao == "E" ){
														echo "ENTRADA";
												}elseif( $TipoMovimentacao == "S" ){
														echo "SAÍDA";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação</td>
											<td class="textonormal">
												<?php
												$db     = Conexao();
												# Mostra a Descrição da Movimentação #
												$sql    = "SELECT CTIPMVCODI, ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
												$sql   .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' AND CTIPMVCODI = $Movimentacao";
												$result = $db->query($sql);
												if( db::isError($result) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														echo "$Linha[1]";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Movimentação</td>
											<td class="textonormal">
											<?php echo "$AnoMovimentacao / $MovNumero"; ?>
											</td>
										</tr>

										<?php
										if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20){
										?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7" height="20">Número/Ano da Requisição</td>
													<td class="textonormal" colspan="2">
														<?php
														$db   = Conexao();
														# Mostra os dados da Requisição #
														$sql  = "  SELECT A.CREQMACODI, A.AREQMAANOR, B.CTIPSRCODI ";
														$sql .= "    FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B ";
														$sql .= "   WHERE A.CREQMASEQU = $SeqRequisicao AND A.CREQMASEQU = B.CREQMASEQU ";
														$sql .= "   AND B.TSITREULAT IN ";
														$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT";
														$sql .= "           WHERE SIT.CREQMASEQU = A.CREQMASEQU) ";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha            = $res->fetchRow();
																$Requisicao       = $Linha[0];
																$AnoRequisicao    = $Linha[1];
																$Situacao         = $Linha[2];
																echo substr($Requisicao+100000,1)."/".$AnoRequisicao;
														}
														$db->disconnect();
														?>
													</td>
												</tr>
										<?php
										}
										?>

										<?php
										$db   = Conexao();
										# Mostra o material e a unidade #

										if($TipoPesquisa <> 0){
												$sql  = "  SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
												$sql .= "    FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND, ";
												$sql .= "         SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBLOCALIZACAOMATERIAL LOC  ";
												$sql .= "   WHERE MAT.CMATEPSEQU = $Material ";
												$sql .= "     AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CMATEPSEQU = ARM.CMATEPSEQU  ";
												$sql .= "     AND ARM.CLOCMACODI = LOC.CLOCMACODI   ";
												$sql .= " AND LOC.CALMPOCODI = $Almoxarifado ";
												$sql .= "ORDER BY MAT.EMATEPDESC  ";
										} else {
												$sql  = "  SELECT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
												$sql .= "    FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND ";
												$sql .= "   WHERE MAT.CMATEPSEQU = $Material ";
												$sql .= "     AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
												$sql .= "ORDER BY MAT.EMATEPDESC  ";
										}
										$res  = $db->query($sql);
										if( db::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $res->fetchRow();
												$DescMaterial = $Linha[1];
												$UnidSigl     = $Linha[2];
										}
										$db->disconnect();
										?>

										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Material</td>
											<td class="textonormal"><?php echo $DescMaterial; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Unidade</td>
											<td class="textonormal"><?php echo $UnidSigl; ?></td>
										</tr>

										<?php
										if($Movimentacao == 21 or $Movimentacao == 22){
										?>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Número / Série da Nota</td>
													<td class="textonormal"><?php echo $NumeroNota; ?> / <?php echo $SerieNota; ?>
													</td>
												</tr>
										<?php
										}
										?>

										<?php
										# Exibe a quantidade travada para as movimentações 6, 9, 11, 13, 21, 22, 26, 27, 29 e 31
										if($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 21 or $Movimentacao == 22 or $Movimentacao == 26 or $Movimentacao == 27 or $Movimentacao == 29 or $Movimentacao == 31){
												$QuantExibe = converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdMovimentada)));
												echo "
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' height='20'>Quantidade</td>
													<td class='textonormal'><input type='hidden' name='QtdMovimentada' value='$QtdMovimentada'>$QuantExibe</td>
												</tr>";
										# Exibe o texto "Quantidade ofertada" para facilitar o entendimento da quantidade referenciada na movimentação de troca
										}elseif($Movimentacao == 15){
												echo "
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' height='20'>Quantidade Ofertada*</td>
													<td class='textonormal'><input type='text' name='QtdMovimentada' maxlength=10 value='";
													if ($QtdMovimentada) echo str_replace('.',',',sprintf("%01.2f",$QtdMovimentada));
													echo "' class='textonormal'></td>
												</tr>";
										# Exibe o texto padrão "Quantidade" para todas as outras movimentações
										}else{
												echo "
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' height='20'>Quantidade*</td>";
												if(($Movimentacao == 2) and ($EstoqueVirtual=='S')){
													# Cancelamento de baixa de requisição atendida por estoque virtual
													# O cancelamento da movimentação deve ser total. Portanto a quantidade deve ser a máxima
													$db   = Conexao();
													$sql  = " SELECT AITEMRQTAT, AITEMRQTSO ";
													$sql .= "   FROM SFPC.TBITEMREQUISICAO ";
													$sql .= "  WHERE CMATEPSEQU = $Material ";
													$sql .= "    AND CREQMASEQU = $SeqRequisicao ";
													$res  = $db->query($sql);
													if( db::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Linha = $res->fetchRow();
															$Quantidade = str_replace('.',',',sprintf("%01.2f",$Linha[0]));
													}
													$db->disconnect();
													echo "<td><input type='hidden' name='QtdMovimentada' value='".$Quantidade."'/>".$Quantidade."</td>";
												}else{
													echo"<td class='textonormal'>
														<input type='text' name='QtdMovimentada' maxlength=10 value='";
													if ($QtdMovimentada) echo str_replace('.',',',sprintf("%01.2f",$QtdMovimentada));
													echo "' class='textonormal'>
													</td>";
												}
												echo "</tr>";
										}

										# MOSTRA O(S) ALMOXARIFADO(S) DIFERENTES DO LOGADO #
										if($Movimentacao == 12 or $Movimentacao == 15 or $Movimentacao == 30){
												$db = Conexao();
												$sqlAlmo  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
												$sqlAlmo .= "FROM SFPC.TBALMOXARIFADOPORTAL A  ";
												$sqlAlmo .= "WHERE A.CALMPOCODI <> '$Almoxarifado'  ";
												$sqlAlmo .= "ORDER BY A.EALMPODESC  ";
												$resAlmo  = $db->query($sqlAlmo);
												if( db::isError($resAlmo) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAlmo");
												}else{
														while($LinhaAlmo = $resAlmo->fetchRow()){
																if($AlmoxSec == $LinhaAlmo[0]){
																		$SelectOption	.= "<option value='$LinhaAlmo[0]' selected>$LinhaAlmo[1]</option>\n";
																}else{
																		$SelectOption	.= "<option value='$LinhaAlmo[0]'>$LinhaAlmo[1]</option>\n";
																}
														}
												}
												echo "
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' height='20'>Almoxarifado de Destino*</td>
													<td class='textonormal'>
														<select name='AlmoxSec' class='textonormal' >
															<option value='' selected>Selecione um Almoxarifado...</option>
															$SelectOption
														</select>
													</td>
												</tr>";
											$db->disconnect();
									}

									# Entrada por Devolução de Empréstimo, Entrada por Troca, Saída por Devolução de Empréstimo, Entrada por Doação entre Almoxarifados
									# MOSTRA O ALMOXARIFADO SECUNDÁRIO #
									if($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 13 or $Movimentacao == 29){
											$db = Conexao();
											$sqlAlmo  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
											$sqlAlmo .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
											$sqlAlmo .= " WHERE A.CALMPOCODI = $AlmoxSec ";
											$sqlAlmo .= " ORDER BY A.EALMPODESC  ";
											$resAlmo  = $db->query($sqlAlmo);
											if( db::isError($resAlmo) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAlmo");
											}else{
												$LinhaAlmo = $resAlmo->fetchRow();
												echo "
												<tr>
													<td class='textonormal' bgcolor='#DCEDF7' height='20'>Almoxarifado de Origem</td>
													<td class='textonormal'>$LinhaAlmo[1]</td>
												</tr>";
											}
											$db->disconnect();
									}

									if($PedeValor == 1){
											echo "
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Valor Unitário*</td>
												<td class='textonormal'>
												<input type=\"text\" name=\"Valor\" size=\"10\" maxlength=\"10\" value=\"";
												if ($Valor) echo str_replace('.',',',sprintf("%01.4f",$Valor));
												echo "\" class=\"textonormal\">";
												echo "
												</td>
											</tr>";
									}

									# Saída por Troca #
									if($Movimentacao == 15){
											echo "
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Código Reduzido do Material a ser Recebido*</td>
												<td class='textonormal' colspan='2'><input type='text' name='CodReduzMat2' size='10' maxlength='10' class='textonormal' value='$CodReduzMat2'>
												</td>
											</tr>
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Quantidade a ser Recebida*</td>
												<td class='textonormal' colspan='2'><input type='text' name='QuantMat2' maxlength='10' class='textonormal' value='$QuantMat2'>
												</td>
											</tr>";
									}

									# Entrada por Troca #
									if($Movimentacao == 11){
											$db = Conexao();
											$sqlDesc  = "SELECT A.EMATEPDESC, B.EUNIDMSIGL ";
											$sqlDesc .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBUNIDADEDEMEDIDA B ";
											$sqlDesc .= " WHERE A.CUNIDMCODI = B.CUNIDMCODI ";
											$sqlDesc .= "   AND A.CMATEPSEQU = $CodReduzMat2 ";
											$resDesc  = $db->query($sqlDesc);
											if( db::isError($resDesc) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDesc");
											}else{
													$LinhaDesc = $resDesc->fetchRow();
													$DescMat2  = $LinhaDesc[0];
													$DescSig2  = $LinhaDesc[1];
											}
											$QuantMat2Exibe = converte_quant(sprintf("%01.2f",str_replace(",",".",$QuantMat2)));
											echo "
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Material p/ Troca</td>
												<td class='textonormal' colspan='2'>$DescMat2</td>
											</tr>
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Unidade</td>
												<td class='textonormal' colspan='2'>$DescSig2</td>
											</tr>
											<tr>
												<td class='textonormal' bgcolor='#DCEDF7' height='20'>Quantidade Solicitada</td>
												<td class='textonormal' colspan='2'>$QuantMat2Exibe</td>
											</tr>";
											$db->disconnect();
									}
									?>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Matrícula do Responsável pela Autorização da Movimentação*</td>
										<td class="textonormal">
											<input type="text" size="9" maxlength="9" name="Matricula" value="<?php echo $Matricula; ?>" class="textonormal">
										</td>
									</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Responsável*</td>
										<td class="textonormal">
											<input type="text" size="60" maxlength="100" name="Responsavel" value="<?php echo $Responsavel; ?>" class="textonormal">
										</td>
									</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
										<td class="textonormal">
											<font class="textonormal">máximo de 200 caracteres</font>
											<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO ?>" class="textonormal"><br>
											<textarea name="Observacao" cols="50" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
										</td>
									</tr>
								</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
									<input type="hidden" name="EstoqueVirtual" value="<?php echo $EstoqueVirtual; ?>">
									<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="AnoMovimentacao" value="<?php echo $AnoMovimentacao ?>">
									<input type="hidden" name="Material" value="<?php echo $Material ?>">
									<input type="hidden" name="TipoMovimentacao" value="<?php echo $TipoMovimentacao ?>">
									<input type="hidden" name="Movimentacao" value="<?php echo $Movimentacao ?>">
									<input type="hidden" name="MovNumero" value="<?php echo $MovNumero ?>">
									<input type="hidden" name="QtdEstoque" value="<?php echo $QtdEstoque ?>">
									<input type="hidden" name="QtdEstoqueReal" value="<?php echo $QtdEstoqueReal ?>">
									<input type="hidden" name="QtdEstoqueVirtual" value="<?php echo $QtdEstoqueVirtual ?>">
									<input type="hidden" name="SeqMovimentacao" value="<?php echo $SeqMovimentacao ?>">
									<input type="hidden" name="AnoAtualizar" value="<?php echo $AnoAtualizar ?>">
									<input type="hidden" name="TipoPesquisa" value="<?php echo $TipoPesquisa ?>">
									<input type="hidden" name="PedeValor" value="<?php echo $PedeValor?>">
									<input type="hidden" name="FornecedorCod" value="<?php echo $FornecedorCod?>">
									<input type="hidden" name="NumeroNota" value="<?php echo $NumeroNota?>">
									<input type="hidden" name="SerieNota" value="<?php echo $SerieNota?>">
									<?php
									if($Movimentacao == 6 or $Movimentacao == 9 or $Movimentacao == 11 or $Movimentacao == 13 or $Movimentacao == 29 or $Movimentacao == 31){
											echo "<input type=\"hidden\" name=\"AlmoxSec\" value=\"$AlmoxSec\">\n";
											echo "<input type=\"hidden\" name=\"Valor\" value=\"$Valor\">\n";
											if($Movimentacao == 11){
													echo "<input type=\"hidden\" name=\"CodReduzMat2\" value=\"$CodReduzMat2\">\n";
													echo "<input type=\"hidden\" name=\"QuantMat2\" value=\"$QuantMat2\">\n";
											}
									}
									?>
									<input type="hidden" name="MovimentacaoSecun" value="<?php echo $MovimentacaoSecun ?>">
									<input type="hidden" name="MatriculaSecun" value="<?php echo $MatriculaSecun ?>">
									<input type="hidden" name="ResponsavelSecun" value="<?php echo $ResponsavelSecun ?>">
									<input type="button" name="Incluir" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
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
