<?php
# -----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadIncluirItemRegistroPreco.php
# Autor:    Roberta Costa/Altamiro Pedrosa
# Data:     05/08/2005
# OBS.:     Tabulação 2 espaços
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     06/09/2006 - Retirada da palavra "SELECT" de variável que vai para POST
# -----------------------------------------------------------------------------
# Alterado: Wagner Barros
# Data:     02/10/2006 - Exibir o código reduzido do material ao lado da descrição
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     06/10/2006 - Correção para situação específica que gerava e-mail para o analista
# -----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     07/12/2006 - Padronização da pesquisa por descrição, exigindo no mínimo 2 letras para proceder
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     18/02/2008 - Alteração para não permitir a entrada de materiais inativos através da entrada de nota fiscal e
#                                  permitir apenas a saída dos materiais inativos em estoque.
# Data:     25/02/2008 - Alteração para não permitir a entrada de materiais inativos através do saldo inicial estoque, entrada por alteração de nota fiscal.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      04/03/2008 - Alteração para não permitir a exibição de grupos, classes, subclasses e materiais inativos ao pesquisar por família.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/03/2008 - Correção do nome do programa de origem: "CadSolicitaçãoCompraIncluir" por "CadSolicitacaoCompraIncluir" para não permitir a inclusão de materiais inativos.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      17/03/2008 - Alteração para permitir a entrada de materiais inaitvos ao realizar a inclusão de materiais quando alterar a nota fiscal, pois há uma critica no
#                                  programa CadNotaFiscalMaterialManterIncluir.php, para verificar a validade dos materiais inativos, onde estes só podem ser incluidos caso a
#                                  data de inativação seja maior do que a data de criação do empenho.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      11/04/2008 - Correção para não enviar a situação do material para o programa CadRequisicaoMaterialIncluir.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      05/05/2008 - Correção para exibir materiais para a saida de requisição, independente da situação do grupo, classe, subclasse e do próprio material.
# -----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:      05/07/2011 - Adicionado o programa: "CadSolicitacaoCompraIncluir" para utilizar a tela de itens.
# -----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:      05/07/2011 - Alterado para incluir itens de serviço para a solicitação de compra e contratação.
# Objetivo: Programa de Inclusão de Itens da Requisição de Material
# -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     01/11/2018
# Objetivo: Tarefa Redmine 73662
# -----------------------------------------------------------------------------

// 220038--

header("Content-Type: text/html; charset=UTF-8",true);

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST") {
    $ProgramaOrigem            = $_POST['ProgramaOrigem'];
	$Botao                     = $_POST['Botao'];
	$Almoxarifado              = $_POST['Almoxarifado'];
	$TipoPesquisa              = $_POST['TipoPesquisa'];
	$TipoMaterial              = $_POST['TipoMaterial'];
	$TipoGrupo                 = $_POST['TipoGrupo'];
	$Grupo                     = $_POST['Grupo'];
	$Classe                    = $_POST['Classe'];
	$Subclasse                 = $_POST['Subclasse'];
	$SubclasseDescricaoFamilia = strtoupper2(trim($_POST['$SubclasseDescricaoFamilia']));
	$ChkSubclasse              = $_POST['chkSubclasse'];

	# Pesquisa direta #
	$OpcaoPesquisaMaterial     = $_POST['OpcaoPesquisaMaterial'];
	$OpcaoPesquisaSubClasse    = $_POST['OpcaoPesquisaSubClasse'];

	$OpcaoPesquisaServico      = $_POST['OpcaoPesquisaServico'];

	$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
	$MaterialDescricaoDireta   = strtoupper2(trim($_POST['MaterialDescricaoDireta']));

	$ServicoDescricaoDireta    = strtoupper2(trim($_POST['ServicoDescricaoDireta']));

	$PesqApenas                = $_POST['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
	$Zerados                   = $_POST['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
	$sqlpost                   = str_replace('\\','',$_POST['sqlgeral']); // Criado para resolver demora para cadastrar itens do cadastro de materiais. No "Insere" era executado um select que retornava todos os materiais da DLC, com esta variável, é executado na inclusão o mesmo select da pesquisa
} else {
	$ProgramaOrigem            = $_GET['ProgramaOrigem'];
	$Almoxarifado              = $_GET['Almoxarifado'];
	$Grupo                     = $_GET['Grupo'];
	$Classe                    = $_GET['Classe'];
	$Subclasse                 = $_GET['Subclasse'];  // Null - Considerar para Serviço
	$PesqApenas                = $_GET['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
	$Zerados                   = $_GET['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Requisição Ano Atual #
$AnoRequisicao = date("Y");

if ($TipoGrupo == null || is_null($TipoGrupo)){
	$TipoGrupo = "M";
}

// [CUSTOMIZACAO] - 23/10 - CR 63 - A seleção de itens está com problemas.
if (isset($TipoMaterial)) {
    $SubclasseDescricaoDireta  = '';
	$MaterialDescricaoDireta   = '';
	$ServicoDescricaoDireta    = '';
}
// [CUSTOMIZACAO]

// @DEBUG: $ProgramaOrigem == CadMigracaoAtaInternaAlterar
if($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir'
    || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter'
    || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir'
    || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir'
	|| $ProgramaOrigem == 'cadMigracaoAtaAlterar'
	|| $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar'
){

	$TextoServico = "/serviço"; //Utilizado para colocar na tela a descrição /Serviço ou /SERVIÇO quando a tela de inclusão de itens for chamado pelas telas de Solicitação de Compras

	if ($ServicoDescricaoDireta != "" && $SubclasseDescricaoDireta == "" && $MaterialDescricaoDireta == "" ) {
		$TipoGrupo = "S";
	}
} else {
	$TextoServico = "";
}

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #

if (isset($_GET['Processo']) === true) {
	$_SESSION['_processo'] = $_GET['Processo'];
} else if(isset($_GET['processo']) === true) {
    $_SESSION['_processo'] = $_GET['processo'];
}

$sql  = "SELECT ";
$sql .= "DISTINCT(GRU.CGRUMSCODI), ";	// 0
$sql .= "GRU.EGRUMSDESC, ";				// 1
$sql .= "CLA.CCLAMSCODI, ";				// 2
$sql .= "CLA.ECLAMSDESC, ";				// 3
$sql .= "GRU.FGRUMSTIPO, ";				// 4
if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
	$sql .= "MAT.CMATEPSEQU, ";			// 5
	$sql .= "MAT.EMATEPDESC, ";			// 6
	$sql .= "MAT.CMATEPSITU, ";			// 7
} else {
	$sql .= "SERV.CSERVPSEQU, ";		// 5
	$sql .= "SERV.ESERVPDESC, ";		// 6
	$sql .= "SERV.CSERVPSITU, ";		// 7
}
$sql .= "SUB.CSUBCLSEQU, ";				// 8
$sql .= "SUB.ESUBCLDESC, ";				// 9
$sql .= "UND.EUNIDMSIGL, ";				// 10
$sql .= "GRU.FGRUMSTIPM, ";				// 11
$sql .= "MAT.CUNIDMCODI, ";				// 12
$sql .= "UND.EUNIDMDESC ";				// 13
if (isset($_SESSION['_processo']) === true) {
	$sql .= ", IAR.CARPNOSEQU ";		// 14
	$sql .= ", IAR.CITARPSEQU ";		// 15
	$sql .= ", IAR.AITARPORDE ";		// 16
	$sql .= ", IAR.CITARPNUML ";		// 17
	$sql .= ", IAR.eitarpmarc ";		// 18
	$sql .= ", IAR.eitarpmode ";		// 19
	$sql .= ", IAR.eitarpdescmat ";		// 20
	$sql .= ", IAR.eitarpdescse ";		// 21
	$sql .= ", IAR.fitarpsitu ";		// 22
	$sql .= ", IAR.fitarpincl ";		// 23
	$sql .= ", IAR.fitarpexcl ";		// 24
	$sql .= ", IAR.titarpincl ";		// 25
	$sql .= ", IAR.aitarpqtor ";		// 26
	$sql .= ", IAR.aitarpqtat ";		// 27
	$sql .= ", IAR.vitarpvori ";		// 28
	$sql .= ", IAR.vitarpvatu ";		// 29
}

# Verifica o Tipo de Pesquisa para definir o relacionamento #

$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
$from   .= " INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A'";

if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
	//PARA MATERIAL
	$from   .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
	$from   .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
	$from   .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI ";
} else {
	//PARA SERVIÇO
	$from   .= " INNER JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
}

if ($TipoPesquisa != 0){
    $from   .= " INNER JOIN SFPC.TBARMAZENAMENTOMATERIAL ITEM ON MAT.CMATEPSEQU = ITEM.CMATEPSEQU ";

    if($Zerados == 'N'){
			$from .= " AND ITEM.AARMATQTDE > 0 ";
	}

	$from   .= " INNER JOIN SFPC.TBLOCALIZACAOMATERIAL LOC ON LOC.CLOCMACODI = ITEM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
}

if (isset($_SESSION['_processo']) === true) {
    // é pra pegar de material e ou serviço
	$from   .= " INNER JOIN SFPC.TBITEMATAREGISTROPRECONOVA IAR ON IAR.CMATEPSEQU = MAT.CMATEPSEQU ";
}

$where  = " WHERE 1 = 1 "; //Artificio utilizado para colocar o 'AND' na clausula WHERE sem se preocupar.

// Para a inclusão/alteração de uma nota fiscal se o material estiver inativo,
// verificar se a data de emissão do empenho < data de atualização do material.
// Logo, devemos considerar que os materiais inativos não podem entrar mais através da entrada de nota fiscal.
// Porém se o item for inativo e a data de emissão do empenho >= data de alteração do material este item pode
// entrar no estoque por meio da entrada por nota fiscal.
// Está verificação é feita no programa CadNotaFiscalMaterialIncluir.php, CadNotaFiscalMaterialManterIncluir e CadNotaFiscalMaterialManterExcluir!

if ($ProgramaOrigem == 'CadInventarioInicialContagem'
    || $ProgramaOrigem == 'CadInventarioPeriodicoIncluirItem'
    || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir'
    || $ProgramaOrigem == 'CadMigracaoAtaExternaAlterar.php'
    || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter'
    || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir'
    || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir'
	|| $ProgramaOrigem == 'cadMigracaoAtaAlterar'
	|| $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar'
){
    if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
      //PARA MATERIAL
      $where .= " AND (MAT.CMATEPSITU <> 'I' AND SUB.FSUBCLSITU <> 'I' ";
    } else {
      //PARA SERVIÇO
      $where .= " AND (SERV.CSERVPSITU <> 'I' ";
    }

    //Concatenando com o material ou serviço
    $where .= " AND CLA.FCLAMSSITU <> 'I' AND GRU.FGRUMSSITU <> 'I') ";
}

if (isset($_SESSION['_processo']) === true) {
	$processo = $_SESSION['_processo'];
	//$where .= " AND IAR.CARPNOSEQU = $processo ";
}

# Verifica se o Tipo de Material foi escolhido #
if($TipoGrupo == "M" and $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == ""){
		$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if($Grupo != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
		$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if($Grupo != "" and $Classe != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
		$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if($Subclasse != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
		$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if($SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){

		$where .= " AND ( ";
		$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' ";
		$where .= "     )";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if($SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
	if($OpcaoPesquisaSubClasse == 0){
		if( SoNumeros($SubclasseDescricaoDireta) ) {
			$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
		}
	} else if($OpcaoPesquisaSubClasse == 1) {
		$where .= "  AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";

		$where .= " AND ( ";
		$where .= "    TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		$where .= "     )";
	} else {
		$where .= "   AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	}
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if($MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "" ) {
	if($OpcaoPesquisaMaterial == 0 ){
			if(SoNumeros($MaterialDescricaoDireta)) {
					$where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
			}
	}elseif($OpcaoPesquisaMaterial == 1){
			$where .= " AND ( ";
				$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
			$where .= "     )";
	}else{
			$where .= "  AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
	}
}


# Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
if($ServicoDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $MaterialDescricaoDireta == "" ){
	if($OpcaoPesquisaServico == 0 ){
			if(SoNumeros($ServicoDescricaoDireta)) {
					$where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
			}
	}elseif($OpcaoPesquisaServico == 1){
			$where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";

	}else{
			$where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";
	}
}


if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
	//PARA MATERIAL
	$order  = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";
} else {
	//PARA SERVIÇO
	$order  = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, SERV.ESERVPDESC ";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;

//FIM NOVA CONSULTA

# Verifica se o botão Incluir foi clicado #
if($Botao == "Incluir"){

	# Limpa o array de itens #
	unset($_SESSION['item']);
	if($sqlpost){
		$db  = Conexao();
		$res = $db->query("SELECT ".$sqlpost);
		if( PEAR::isError($res) ){
			$CodErroEmail  = $res->getCode();
			$DescErroEmail = $res->getMessage();
			$db->disconnect();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: SELECT $sqlpost\n\n$DescErroEmail ($CodErroEmail)");
		} else {
            $contador = 0;
			while($row = $res->fetchRow()){
				$novoItem = array();

				$TipoGrupoBanco     			= $row[4];
				$CodRedMaterialServicoBanco 	= $row[5]; // $MaterialSequencia
				$DescricaoMaterialServicoBanco 	= RetiraAcentos($row[6]).$SimboloConcatenacaoDesc.str_replace("\"","”",$row[6]); // $MaterialDescricao
				$UndMedidaSigla     			= $row[10];
    			$SituacaoMaterial				= $row[7]; #Situação: Ativo (A) ou Inativo (I).
                $itens                          = $_POST['itens'];

                $itemSelecionado    = $itens[$contador]['chkMaterial'];
                $valorUnitario      = $itens[$contador]['valorUnitario'];
                $marca              = $itens[$contador]['marca'];
                $modelo             = $itens[$contador]['modelo'];

				$novoItem = array(
					'cgrumscodi' 	=> $row[0],  // Código do Grupo de Material e Serviço
					'egrumsdesc' 	=> $row[1],  // Descrição do Grupo de Material e Serviço
					'cclamscodi' 	=> $row[2],  // Código da Classe de Material e Serviço
					'eclamsdesc' 	=> $row[3],  // Descrição da Classe de Material e Serviço
					'fgrumstipo' 	=> $row[4],  // Tipo do Grupo (M- Material ou S-Serviço)
					'csubclsequ' 	=> $row[8],  // Código da Subclasse de Material e Serviço
					'esubcldesc' 	=> $row[9],  // Descrição da Subclasse de Material
					'eunidmsigl' 	=> $row[10], // Sigla da Unidade
					'fgrumstipm' 	=> $row[11], // Tipo de Material (C-Consumo ou P-Permanente)
					'cunidmcodi' 	=> $row[12], //
					'eunidmdesc' 	=> $row[13], //
					'carpnosequ' 	=> $row[14], // Código sequencial da ata de registro de preço
					'citarpsequ' 	=> $row[15], // Código Sequencial dos Itens da Ata de Registro de Preço
					'aitarporde' 	=> $row[16], // Ordem do item
					'citarpnuml' 	=> $row[17], // Número do Lote
					'eitarpmarc' 	=> $row[18], // Marca do Item
					'eitarpmode' 	=> $row[19], // Modelo do Item
					'eitarpdescmat' => $row[20], // Descrição detalhada do item de Material
					'eitarpdescse' 	=> $row[21], // Descrição detalhada do item de serviço
					'fitarpsitu' 	=> $row[22], // Situação do Item da Ata (A- Ativa / I - Inativa)
					'fitarpincl' 	=> $row[23], // Indica se o item foi incluído diretamente na ata de registro de preço (S- Sim / N - Não)
					'fitarpexcl' 	=> $row[24], // Indica se o o item foi excluído da ata de registro de preço (S- Sim / N - Não)
					'titarpincl' 	=> $row[25], // Data/Hora da Inclusão
					'valorUnita' 	=> $valorUnitario,
                    'marca'      	=> $marca,
                    'modelo'     	=> $modelo,
					'aitarpqtor'    => $row[26],
					'aitarpqtat'    => $row[27],
					'vitarpvori'    => $row[28],
                    'vitarpvatu'    => $_REQUEST['valorUnitario']
				);

				if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
					$novoItem['cmatepsequ'] = $row[5];
					$novoItem['ematepdesc'] = $row[6];
					$novoItem['cmatepsitu'] = $row[7];
				} else {
					$novoItem['cservpsequ'] = $row[5];
					$novoItem['eservpdesc'] = $row[6];
					$novoItem['cservpsitu'] = $row[7];
				}

                if ($itemSelecionado != "") {
                   $_SESSION['item'][count($_SESSION['item'])] = $novoItem;
                }
                $contador++;
            }

            echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
			echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
			echo "<script>self.close()</script>";
		}
	}
}


# Critica dos Campos #
if($Botao == "Validar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
		if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
		}elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}
?>
<html>
<head>
<title>Portal Compras - Incluir Itens</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadIncluirItemRegistroPreco.Subclasse.value = '';
	document.CadIncluirItemRegistroPreco.submit();
}
function enviar(valor){

	// console.log('enviar');
	// console.log(valor);
	// console.log(document.CadIncluirItemRegistroPreco);

	document.CadIncluirItemRegistroPreco.Botao.value = valor;
	document.CadIncluirItemRegistroPreco.submit();
}
function validapesquisa(){
	if( ( document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.value != '' ) || ( document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadIncluirItemRegistroPreco.Grupo){
			document.CadIncluirItemRegistroPreco.Grupo.value = '';
		}
		if(document.CadIncluirItemRegistroPreco.Classe){
			document.CadIncluirItemRegistroPreco.Classe.value = '';
		}
		document.CadIncluirItemRegistroPreco.Botao.value = 'Validar';
	}
	if(document.CadIncluirItemRegistroPreco.Subclasse){
		if(document.CadIncluirItemRegistroPreco.SubclasseDescricaoFamilia.value != "") {
			document.CadIncluirItemRegistroPreco.Subclasse.value = '';
		}
	}
	document.CadIncluirItemRegistroPreco.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadIncluirItemRegistroPreco.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadIncluirItemRegistroPreco.php" method="post" name="CadIncluirItemRegistroPreco">
	<table cellpadding="0" border="0" summary="">
		<!-- Erro -->
		<tr>
			<td align="left" colspan="7">
				<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,1); }?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td class="textonormal">
				<table border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
						<td class="textonormal">
							<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
								<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
								<input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
								<tr>
									<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
										INCLUIR - SELEÇÃO DE MATERIAL<?php echo strtoupper2($TextoServico); ?>
									</td>
								</tr>
								<tr>
									<td class="textonormal" colspan="7">
										<p align="justify">
											Para incluir um material<?php echo $TextoServico; ?> selecione o item de pesquisa desejado,
											preencha o argumento da pesquisa e clique no botão "Pesquisar".
											Depois, clique no material<?php echo $TextoServico; ?> desejado e clique no botão "Incluir".
											Para voltar para a tela anterior clique no botão "Voltar".
										</p>
									</td>
								</tr>
								<tr>
									<td colspan="7">
										<table border="0" width="100%" summary="">
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Tipo de Pesquisa</td>
												<td class="textonormal" height="20">
													<select name="TipoPesquisa" class="textonormal">
														<?php
														if ($PesqApenas == 'C') {
															if($TextoServico != ""){
																$TextoServico = substr($TextoServico,0,1) . ucfirst(substr($TextoServico,1));
															}
															echo "<option value=\"0\">Cadastro de Material". $TextoServico . "</option>";
														} else if($PesqApenas == 'E'){
																echo "<option value=\"1\">Itens em Estoque</option>";
														?>
														<?php } else { ?>
															<option value="0" <?php if( $TipoPesquisa == 0 ){ echo "selected"; } ?>>Cadastro de Material</option>
															<option value="1" <?php if( $TipoPesquisa == 1 or $TipoPesquisa == "" ){ echo "selected"; } ?>>Itens em Estoque</option>
														<?php } ?>
													</select>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="7">PESQUISA DIRETA</td>
								</tr>
								<tr>
									<td colspan="7">
										<table border="0" width="100%" summary="">
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
												<td class="textonormal" colspan="7">
													<select name="OpcaoPesquisaSubClasse" class="textonormal">
														<option value="0">Código Reduzido</option>
														<option value="1">Descrição contendo</option>
														<option value="2">Descrição iniciada por</option>
													</select>
													<input type="text" name="SubclasseDescricaoDireta" value="<?php echo $SubclasseDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemRegistroPreco.ServicoDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.TipoGrupo.value = 'M';">
													<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7">Material</td>
												<td class="textonormal" colspan="7">
													<select name="OpcaoPesquisaMaterial" class="textonormal">
														<option value="0">Código Reduzido</option>
														<option value="1">Descrição contendo</option>
														<option value="2">Descrição iniciada por</option>
														</select>
													<input type="text" name="MaterialDescricaoDireta" value="<?php echo $MaterialDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemRegistroPreco.ServicoDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.TipoGrupo.value = 'M';">
													<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
												</td>
											</tr>
                                            <tr>
									        	<td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>
									            <td class="textonormal" colspan="2">
									            	<select name="OpcaoPesquisaServico" class="textonormal">
										          		<option value="0">Código Reduzido</option>
										          		<option value="1">Descrição contendo</option>
										          		<option value="2">Descrição iniciada por</option>
									            	</select>
								         		<input type="text" name="ServicoDescricaoDireta" value="<?php echo $ServicoDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.value = '';document.CadIncluirItemRegistroPreco.TipoGrupo.value = 'S';">
								           	    <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
									            </td>
									        </tr>
								        </table>
									</td>
								</tr>
								<tr>
									<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="7">PESQUISA POR FAMILIA - MATERIAL<?php echo strtoupper2($TextoServico); ?></td>
								</tr>
								<tr>
									<td colspan="7">
										<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr>
												<td colspan="7">
													<table class="textonormal" border="0" width="100%" summary="">
												    <?php if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir'){ ?>
															<tr>
													          <td class="textonormal" bgcolor="#DCEDF7" width="34%">Tipo de Grupo</td>
													          <td class="textonormal">

													          	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.ServicoDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> > Material
													          	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadIncluirItemRegistroPreco.MaterialDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.SubclasseDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.ServicoDescricaoDireta.value='';document.CadIncluirItemRegistroPreco.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
													          </td>
													        </tr>
												         <?php } ?>


												        <?php if ($TipoGrupo == "M") { ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
															<td class="textonormal">
																<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadIncluirItemRegistroPreco.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> /> Consumo
																<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadIncluirItemRegistroPreco.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?> /> Permanente
															</td>
														</tr>
														<?php } ?>

														<?php if( $TipoGrupo != "" ){ ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
															<td class="textonormal">
																<select name="Grupo" onChange="javascript:remeter();" class="textonormal">
																	<option value="">Selecione um Grupo...</option>
																	<?php
																	$db = Conexao();
											                	  	# Mostra os grupos cadastrados #
																	if(($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S"){

																		$sql  = "
																			SELECT
																				CGRUMSCODI,EGRUMSDESC
																			FROM SFPC.TBGRUPOMATERIALSERVICO
																			WHERE
																				FGRUMSTIPO = '$TipoGrupo' AND FGRUMSSITU = 'A'
																				";

																		if($TipoGrupo == "M" and $TipoMaterial != ""){
																			$sql  .= " AND FGRUMSTIPM = '$TipoMaterial' ";
																		}

																		$sql  .= " ORDER BY EGRUMSDESC";

												                		$res  = $db->query($sql);
												                		if( PEAR::isError($res) ){
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		} else {
																			while( $Linha = $res->fetchRow() ){
													          	      			$Descricao = substr($Linha[1],0,75);
													          	      			if( $Linha[0] == $Grupo ){
																		    		echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																      	      	}else{
																		    	    echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																      	      	}
															      	      	}
														              	}
													  	            }
											      	            	?>
																</select>
															</td>
														</tr>
														<?php
														}
														if($Grupo != "") { ?>
														<tr>
															<td class="textonormal" bgcolor="#DCEDF7">Classe </td>
															<td class="textonormal">
																<select name="Classe" class="textonormal" onChange="javascript:remeter();">
																	<option value="">Selecione uma Classe...</option>
																		<?php
																		if($Grupo != ""){
																			$db   = Conexao();

																			$sql  = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
									                                        $sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
									                                        $sql .= " WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $Grupo AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' ";
									                                        $sql .= " ORDER BY ECLAMSDESC";

																			$res  = $db->query($sql);
																			if( PEAR::isError($res) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			} else {
																				while( $Linha = $res->fetchRow() ){
																					$Descricao = substr($Linha[1],0,75);
																					if($Linha[0] == $Classe){
																						echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																					} else {
																						echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																					}
																				}
																			}
																			$db->disconnect();
																		}
																		?>
																</select>
															</td>
														</tr>
														<?php
														}
														if($Grupo != "" and $Classe != "" and $TipoGrupo == "M"){ //Apenas para Material
														?>

														<tr>
															<td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
															<td class="textonormal">
																<select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
																	<option value="">Selecione uma Subclasse...</option>
																	<?
																	$db = Conexao();
																	$sql   = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
								                                    $sql  .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
								                                    $sql  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
								                                    $sql  .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
								                                    $sql  .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
								                                    $sql  .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
								                                    $sql  .= "   AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe' ";
								                                    $sql  .= "    ORDER BY ESUBCLDESC ";
																	$result = $db->query($sql);

																	if( PEAR::isError($result) ){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																		while($Linha = $result->fetchRow()) {
																			$Descricao   = substr($Linha[1],0,75);
																			if($Linha[0] == $Subclasse ) { ?>
																				<option value="<?= $Linha[0] ?>" selected><?= $Descricao ?></option>
																			<?php } else { ?>
																				<option value="<?= $Linha[0] ?>"><?= $Descricao ?></option>
																			<?php }
																		}
																	}
																	?>
																</select>
																<input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
																<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
																<input type="checkbox" name="chkSubclasse" onClick="javascript:checktodos();" value="T" <?php if($ChkSubclasse == "T") { echo ("checked"); } ?> >Todas
															</td>
														</tr>
														<?php } ?>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>

								<?php
								if($MaterialDescricaoDireta != "") {
									if($OpcaoPesquisaMaterial == 0) {
										if( !SoNumeros($MaterialDescricaoDireta) ) {
											$sqlgeral = "";
										}
									}
								}
								if($SubclasseDescricaoDireta != "" ) {
									if($OpcaoPesquisaSubClasse == 0) {
										if( !SoNumeros($SubclasseDescricaoDireta) ) {
											$sqlgeral = "";
										}
									}
								}

								if($sqlgeral != "" and $Mens == 0){
									if( ( ( $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "" ) or ($Subclasse != "") or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse == "T") )//Validação para Material
						   				or ( $ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and $Classe != 0) ) //Validação para Serviço
									) {
										$db     = Conexao();
										$res    = $db->query($sqlgeral);
										if( PEAR::isError($res) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
										} else {
											$qtdres = $res->numRows();
											?>
											<tr>
												<td align="center" bgcolor="#75ADE6" colspan="7" class="titulo3">RESULTADO DA PESQUISA</td>
											</tr>
											<?php
												if($qtdres > 0){
													$TipoMaterialAntes  = "";
													$GrupoAntes         = "";
													$ClasseAntes        = "";
													$SubClasseAntes     = "";
													$SubClasseSequAntes = "";
													$contador           = 0;
													while( $row = $res->fetchRow() ) {
														$GrupoCodigo        			= $row[0];
														$GrupoDescricao     			= $row[1];
														$ClasseCodigo       			= $row[2];
														$ClasseDescricao    			= $row[3];
														$TipoGrupoBanco     			= $row[4];
														$CodRedMaterialServicoBanco 	= $row[5];
														$DescricaoMaterialServicoBanco 	= $row[6];

														if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
															$SubClasseSequ      = $row[8];
															$SubClasseDescricao = $row[9];
															$UndMedidaSigla     = $row[10];
															$TipoMaterialCodigo = $row[11];
														}

														if( $TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo ) {
														?>
															<tr>
																<td class="textoabason" bgcolor="#BFDAF2" colspan="7" align="center">
																	<?php if($TipoMaterialCodigo == "C"){ echo "CONSUMO"; } else { echo "PERMANENTE"; } ?>
																</td>
															</tr>
														<?php
														}

														if($GrupoAntes != $GrupoDescricao) {
															if($ClasseAntes != $ClasseDescricao) { ?>
																<tr>
																	<td class="textoabason" bgcolor="#DDECF9" colspan="7" align="center"><?= $GrupoDescricao ."/". $ClasseDescricao ?></td>
																</tr>
															<?php }
														} else {
															if($ClasseAntes != $ClasseDescricao) { ?>
																<tr>
																	<td class="textoabason" bgcolor="#DDECF9" colspan="7" align="center"><?= $GrupoDescricao ."/". $ClasseDescricao ?></td>
																</tr>
															<?php }
														}

														//COLOCAR DESCRIÇÃO MATERIAL OU SERVIÇO E AJUSTAR DAS VARIAVEIS DE MATERIAL E SERVIÇO AQUI
														if($TipoGrupoBanco == "M"){
															$Descricao = "Material";
														} else {
															$Descricao = "Serviço";
														}

														if($ClasseAntes != $ClasseDescricao) { ?>
															<tr>
																<?php if($TipoGrupoBanco == "M"){ ?>
																	<td class="titulo3" bgcolor="#F7F7F7" width="18%">SUBCLASSE</td>
																<?php } ?>
																<td class="titulo3" bgcolor="#F7F7F7" width="60%">DESCRIÇÃO DO <?= strtoupper2($Descricao) ?></td>
																<td class="titulo3" bgcolor="#F7F7F7" width="11%">CÓD.RED.</td>
																<?php if($TipoGrupoBanco == "M"){ ?>
																	<td class="titulo3" bgcolor="#F7F7F7" width="11%" align="center">UNIDADE</td>
																	<td class="titulo3" bgcolor="#F7F7F7" width="18%">MARCA</td>
                                                        			<td class="titulo3" bgcolor="#F7F7F7" width="18%">MODELO</td>
    															<?php } ?>
                                                    			<td class="titulo3" bgcolor="#F7F7F7" width="18%">VALOR UNITÁRIO</td>
															</tr>
														<?php } ?>

														<tr>
															<?php
															if( $TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) {
																$flg = "S";
															?>
																<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="18%">
																	<?= $SubClasseDescricao ?>
																</td>
															<?php }
															if($flg == "S") { ?>
																<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="60%">
																	<input type="checkbox" name="itens[<?php echo $contador ?>][chkMaterial]" value="<?= $CodRedMaterialServicoBanco ?>">
																	<?php $Url = "CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco"; ?>
																	<a href="javascript:AbreJanela('<?= $Url ?>',700,340);"><font color="#000000"><?= $DescricaoMaterialServicoBanco ?></font></a>
																</td>
																<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="11%">
																	<?= $CodRedMaterialServicoBanco ?>
																</td>
																<td valign="top" bgcolor="#F7F7F7" class="textonormal" align="center"  width="11%">
																	<?= $UndMedidaSigla ?>
																</td>
																<?php
																$flg = "";
																} else {
																	if($TipoGrupoBanco == "M") { ?>
																		<td valign="top" bgcolor="#F7F7F7" class="textonormal" align="center" width="18%">&nbsp;</td>
																	<?php } ?>
																	<!-- Ocorre para Material e Serviço -->
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="60%">
																		<input type="checkbox" name="itens[<?php echo $contador ?>][chkMaterial]" value="<?= $CodRedMaterialServicoBanco ?>">
																		<?php $Url = "CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco"; ?>
																		<a href="javascript:AbreJanela('<?= $Url ?>',700,340);"><font color="#000000"><?= $DescricaoMaterialServicoBanco ?></font></a>
																	</td>
																	<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="11%">

	                            										<?= $CodRedMaterialServicoBanco ?>
																	</td>

																	<?php if($TipoGrupoBanco == "M") { ?>
																		<td valign="top" bgcolor="#F7F7F7" class="textonormal" align="center"  width="11%">
																			<?= $UndMedidaSigla ?>
																		</td>
																	<?php }
																}

																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																?>
	                                                        	<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="11%">
																	<input type='text' name='itens[<?php echo $contador ?>][marca]' class="dinheiro4casas">
                                                            	<?php if($TipoGrupoBanco == "M") { ?>
	                                                            	<td valign="top" bgcolor="#F7F7F7" class="textonormal" width="11%">
																		<input type='text' name='itens[<?php echo $contador ?>][modelo]'>
	                                                                <td valign="top" bgcolor="#F7F7F7" class="textonormal" width="11%">
																		<input type='text' name='itens[<?php echo $contador ?>][valorUnitario]'>
                                                            	<?php } ?>
														</tr>
														<?php
															$TipoMaterialAntes  = $TipoMaterialCodigo;
															$GrupoAntes         = $GrupoDescricao;
															$ClasseAntes        = $ClasseDescricao;
															$SubClasseAntes     = $SubClasseDescricao;
															$SubClasseSequAntes = $SubClasseSequ;

                                                        $contador++;
													}//end while
														$db->disconnect();
												} else { ?>
													<tr>
														<td valign="top" colspan="7" class="textonormal" bgcolor="FFFFFF">
															Pesquisa sem Ocorrências.
														</td>
													</tr>
												<?php }
											}
										}
									}
								?>
								<tr>
									<td colspan="7" align="right">
										<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
										<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
										<input type="hidden" name="Botao" value="">
										<input type="hidden" name="PesqApenas" value="<?php echo $PesqApenas; ?>">
										<input type="hidden" name="Zerados" value="<?php echo $Zerados; ?>">
										<input type="hidden" name="sqlgeral" value="<?php echo substr($sqlgeral,7); ?>">
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
<script language="javascript" type="">
	window.focus();
//-->
</script>
</body>
</html>
