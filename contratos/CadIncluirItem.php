<?php
/**
 * Portal de Compras
 * 
 * Programa: CadIncluirItem.php
 * Autores:  Roberta Costa e Altamiro Pedrosa
 * Data:     05/08/2005
 * Objetivo: Programa de Inclusão de Itens da Requisição de Material
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     29/08/2006
 * Objetivo: Opção de pesquisa de material com descrição "iniciada por"
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     06/09/2006
 * Objetivo: Retirada da palavra "SELECT" de variável que vai para POST
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Wagner Barros
 * Data:     02/10/2006
 * Objetivo: Exibir o código reduzido do material ao lado da descrição
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     06/10/2006
 * Objetivo: Correção para situação específica que gerava e-mail para o analista
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     07/12/2006
 * Objetivo: Padronização da pesquisa por descrição, exigindo no mínimo 2 letras para proceder
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     18/02/2008
 * Objetivo: Alteração para não permitir a entrada de materiais inativos através da entrada de nota fiscal e permitir apenas a saída dos materiais inativos em estoque
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     25/02/2008
 * Objetivo: Alteração para não permitir a entrada de materiais inativos através do saldo inicial estoque, entrada por alteração de nota fiscal
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     04/03/2008
 * Objetivo: Alteração para não permitir a exibição de grupos, classes, subclasses e materiais inativos ao pesquisar por família
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     11/03/2008
 * Objetivo: Correção do nome do programa de origem: "CadSolicitaçãoCompraIncluir" por "CadSolicitacaoCompraIncluir" para não permitir a inclusão de materiais inativos
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     17/03/2008
 * Objetivo: Alteração para permitir a entrada de materiais inaitvos ao realizar a inclusão de materiais quando alterar a nota fiscal, pois há uma critica no
 *           programa CadNotaFiscalMaterialManterIncluir.php, para verificar a validade dos materiais inativos, onde estes só podem ser incluidos caso a
 *           data de inativação seja maior do que a data de criação do empenho
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     11/04/2008
 * Objetivo: Correção para não enviar a situação do material para o programa CadRequisicaoMaterialIncluir
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     05/05/2008
 * Objetivo: Correção para exibir materiais para a saida de requisição, independente da situação do grupo, classe, subclasse e do próprio material
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     05/07/2011
 * Objetivo: Adicionado o programa: "CadSolicitacaoCompraIncluir" para utilizar a tela de itens
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     05/07/2011
 * Objetivo: Alterado para incluir itens de serviço para a solicitação de compra e contratação
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     16/04/2018
 * Objetivo: Tarefa Redmine 147625
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:	 24/10/2018
 * Objetivo: Tarefa Redmine 73662
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     22/11/2018
 * Objetivo: Tarefa Redmine 207184
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/11/2018
 * Objetivo: Tarefa Redmine 207183
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     18/11/2018
 * Objetivo: Tarefa Redmine 200950
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     11/06/2019
 * Objetivo: Tarefa Redmine 218516
 * ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 */

header("Content-Type: text/html; charset=UTF-8", true);

// Acesso ao arquivo de funções
include "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('../estoques/CadItemDetalhe.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
    $Repeticoes                = $_POST['Repeticoes'];
    
    // Pesquisa direta
    $OpcaoPesquisaMaterial    = $_POST['OpcaoPesquisaMaterial'];
    $OpcaoPesquisaSubClasse   = $_POST['OpcaoPesquisaSubClasse'];    
    $OpcaoPesquisaServico     = $_POST['OpcaoPesquisaServico'];    
    $SubclasseDescricaoDireta = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
    $MaterialDescricaoDireta  = strtoupper2(trim($_POST['MaterialDescricaoDireta']));    
    $ServicoDescricaoDireta   = strtoupper2(trim($_POST['ServicoDescricaoDireta']));
    
    $PesqApenas = $_POST['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
    $Zerados    = $_POST['Zerados']; // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
    $sqlpost    = str_replace('\\', '', $_POST['sqlgeral']); // Criado para resolver demora para cadastrar itens do cadastro de materiais. No "Insere" era executado um select que retornava todos os materiais da DLC, com esta variável, é executado na inclusão o mesmo select da pesquisa
} else {
    $ProgramaOrigem = $_GET['ProgramaOrigem'];
    $Almoxarifado   = $_GET['Almoxarifado'];
    $Grupo          = $_GET['Grupo'];
    $Classe         = $_GET['Classe'];
    $Subclasse      = $_GET['Subclasse']; // Null - Considerar para Serviço
    $PesqApenas     = $_GET['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
    $Zerados        = $_GET['Zerados']; // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
}
// Identifica o Programa para Erro de Banco de Dados
$ErroPrograma = __FILE__;

// Ano da Requisição Ano Atual
$AnoRequisicao = date("Y");

if ($TipoGrupo == null || is_null($TipoGrupo)) {
    $TipoGrupo = "M";
}

if (isset($TipoMaterial)) {
    $SubclasseDescricaoDireta = '';
    $MaterialDescricaoDireta  = '';
    $ServicoDescricaoDireta   = '';
}

$listaProgramasLiberados = array(
    'CadSolicitacaoCompraIncluirManterExcluir',
    'CadRegistroPrecoIntencaoManter',
    'CadRegistroPrecoIntencaoIncluir',
    'CadAtaRegistroPrecoExternaSelecionarIncluir',
    'cadMigracaoAtaAlterar',
    'CadAtaRegistroPrecoExternaIncluir',
    'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar',
    'CadAtaRegistroPrecoExternaSelecionarAlterar',
    'ConsHistoricoPesquisarGeral',
    'CadContratoAntigoIncluir',
    'CadContratoManter'
);

if (in_array($ProgramaOrigem, $listaProgramasLiberados)) {
    $TextoServico = "/serviço"; // Utilizado para colocar na tela a descrição /Serviço ou /SERVIÇO quando a tela de inclusão de itens for chamado pelas telas de Solicitação de Compras
    
    if ($ServicoDescricaoDireta != "" && $SubclasseDescricaoDireta == "" && $MaterialDescricaoDireta == "") {
        $TipoGrupo = "S";
    }
} else {
    $TextoServico = "";
}

// Monta o sql para montagem dinâmica da grade a partir da pesquisa
$sql = "SELECT DISTINCT (GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, GRU.FGRUMSTIPO, ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
    $sql .= "MAT.CMATEPSEQU, MAT.EMATEPDESC, MAT.CMATEPSITU, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM, MAT.FMATEPGENE ";
} else {
    $sql .= "SERV.CSERVPSEQU, SERV.ESERVPDESC, SERV.CSERVPSITU ";
}

// Verifica o Tipo de Pesquisa para definir o relacionamento #
$from  = "FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
$from .= "INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
    // PARA MATERIAL
    $from .= "INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
    $from .= "INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
    $from .= "INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI ";
} else {
    // PARA SERVIÇO
    $from .= "INNER JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
}

if ($TipoPesquisa != 0) {
    $from .= "INNER JOIN SFPC.TBARMAZENAMENTOMATERIAL ITEM ON MAT.CMATEPSEQU = ITEM.CMATEPSEQU ";

    if ($Zerados == 'N') {
        $from .= "AND ITEM.AARMATQTDE > 0 ";
    }

    $from .= "INNER JOIN SFPC.TBLOCALIZACAOMATERIAL LOC ON LOC.CLOCMACODI = ITEM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
}

$where = "WHERE 1 = 1 "; // Artificio utilizado para colocar o 'AND' na clausula WHERE sem se preocupar.
                      
// Para a inclusão/alteração de uma nota fiscal se o material estiver inativo,
// verificar se a data de emissão do empenho < data de atualização do material.
// Logo, devemos considerar que os materiais inativos não podem entrar mais através da entrada de nota fiscal.
// Porém se o item for inativo e a data de emissão do empenho >= data de alteração do material este item pode
// entrar no estoque por meio da entrada por nota fiscal.
// Está verificação é feita no programa CadNotaFiscalMaterialIncluir.php, CadNotaFiscalMaterialManterIncluir e CadNotaFiscalMaterialManterExcluir!

if ($ProgramaOrigem == 'CadInventarioInicialContagem' || $ProgramaOrigem == 'CadInventarioPeriodicoIncluirItem' || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadMaterialAlterar' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir' || $ProgramaOrigem == 'cadMigracaoAtaAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
    if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
        // PARA MATERIAL
        $where .= " AND (MAT.CMATEPSITU <> 'I' AND SUB.FSUBCLSITU <> 'I' ";
    } else {
        // PARA SERVIÇO
        $where .= " AND (SERV.CSERVPSITU <> 'I' ";
    }

    // Concatenando com o material ou serviço
    $where .= " AND CLA.FCLAMSSITU <> 'I' AND GRU.FGRUMSSITU <> 'I') ";
}

// Verifica se o Tipo de Material foi escolhido #
if ($TipoGrupo == "M" and $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "") {
    $where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

// Verifica se o Grupo foi escolhido #
if ($Grupo != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    $where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

// Verifica se a Classe foi escolhida #
if ($Grupo != "" and $Classe != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    $where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

// Verifica se a SubClasse foi escolhida #
if ($Subclasse != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    $where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

// Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if ($SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    
    $where .= " AND ( ";
    $where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%" . strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia)) . "%' ";
    $where .= "     )";
}

// Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if ($SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    if ($OpcaoPesquisaSubClasse == 0) {
        if (SoNumeros($SubclasseDescricaoDireta)) {
            $where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
        }
    } elseif ($OpcaoPesquisaSubClasse == 1) {
        $where .= "  AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%" . strtoupper2(RetiraAcentos($SubclasseDescricaoDireta)) . "%' ";
        
        $where .= " AND ( ";
        $where .= "    TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%" . strtoupper2(RetiraAcentos($SubclasseDescricaoDireta)) . "%' ";
        $where .= "     )";
    } else {
        $where .= "   AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '" . strtoupper2(RetiraAcentos($SubclasseDescricaoDireta)) . "%' ";
    }
}

// Se foi digitado algo na caixa de texto do material em pesquisa direta #
if ($MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
    if ($OpcaoPesquisaMaterial == 0) {
        if (SoNumeros($MaterialDescricaoDireta)) {
            $where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
        }
    } elseif ($OpcaoPesquisaMaterial == 1) {
        $where .= " AND ( ";
        $where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%" . strtoupper2(RetiraAcentos($MaterialDescricaoDireta)) . "%' ";
        $where .= "     )";
    } else {
        $where .= "  AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '" . strtoupper2(RetiraAcentos($MaterialDescricaoDireta)) . "%' ";
    }
}

// Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
if ($ServicoDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $MaterialDescricaoDireta == "") {
    if ($OpcaoPesquisaServico == 0) {
        if (SoNumeros($ServicoDescricaoDireta)) {
            $where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
        }
    } elseif ($OpcaoPesquisaServico == 1) {
        $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%" . strtoupper2(RetiraAcentos($ServicoDescricaoDireta)) . "%' ";
    } else {
        $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '" . strtoupper2(RetiraAcentos($ServicoDescricaoDireta)) . "%' ";
    }
}

if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
    $order = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";
} else {
    $order = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, SERV.ESERVPDESC ";
}

// Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql . $from . $where . $order;

// Verifica se o botão Incluir foi clicado #
if ($Botao == "Incluir") {
    // Limpa o array de itens #
    unset($_SESSION['item']);
    
    if ($sqlpost) {
        $db = Conexao();
        $res = $db->query("SELECT " . $sqlpost);
        
        if (db::isError($res)) {
            $CodErroEmail = $res->getCode();
            $DescErroEmail = $res->getMessage();
            $db->disconnect();
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: SELECT $sqlpost\n\n$DescErroEmail ($CodErroEmail)");
        } else {
            while ($row = $res->fetchRow()) {
                $TipoGrupoBanco = $row[4];
                $CodRedMaterialServicoBanco = $row[5]; // $MaterialSequencia
                $DescricaoMaterialServicoBanco = RetiraAcentos($row[6]) . $SimboloConcatenacaoDesc . str_replace("\"", "”", $row[6]); // $MaterialDescricao
                $UndMedidaSigla = $row[10];
                $SituacaoMaterial = $row[7]; // Situação: Ativo (A) ou Inativo (I).
                $checkmaterial = "chkMaterial";
                $checkmaterial .= $CodRedMaterialServicoBanco;
                $check_material = $_POST[$checkmaterial];
                $RepeticoesPost = 'Repeticoes'.$CodRedMaterialServicoBanco;
                $RepeticoesPost = $_POST[$RepeticoesPost];
                $n = 0;

                if ($check_material != "") {
                    if ($ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaIncluir'){
                        //Apenas para tela CadAtaRegistroPrecoExternaSelecionarAlterar e CadAtaRegistroPrecoExternaIncluir                       
                        $material_generico = (isset($row[12]) && $row[12] == 'S') ? 'S' : 'N';
                    
                        $_SESSION['item'][count($_SESSION['item'])] = $DescricaoMaterialServicoBanco . $SimboloConcatenacaoArray . $CodRedMaterialServicoBanco . $SimboloConcatenacaoArray . $UndMedidaSigla . $SimboloConcatenacaoArray . $row[4] . $SimboloConcatenacaoArray . $material_generico . $SimboloConcatenacaoArray;                        
                    } else if ($ProgramaOrigem == 'CadRequisicaoMaterialIncluir' || $ProgramaOrigem == 'CadRequisicaoMaterialAlterarIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarAlterar' || $ProgramaOrigem == 'cadMigracaoAtaAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar') {
                        // A situação do material/serviço e o tipo do grupo (Material ou Serviço) não devem ser enviados para os programas abaixo, pois no campo quantidade fica com o valor da situação do material(A - Ativo e I - Inativo) ou o tipo do grupo.                    
                        $_SESSION['item'][count($_SESSION['item'])] = $DescricaoMaterialServicoBanco . $SimboloConcatenacaoArray . $CodRedMaterialServicoBanco . $SimboloConcatenacaoArray . $UndMedidaSigla . $SimboloConcatenacaoArray;
                    
                    } else  {
                        // O tipo do grupo (Material ou Serviço) deve ser enviado para os programas abaixo, pois estes utilizaram a inclusão de materias e serviços ativosW                    
                        if ($ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter" || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir' || $ProgramaOrigem == 'cadMigracaoAtaAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar') {
                            if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                if ($RepeticoesPost == '' || empty($RepeticoesPost)) {
                                    $RepeticoesPost = 1;
                                }                                        
                                
                                while ($n < $RepeticoesPost) {
                                    $n++;
                                    $_SESSION['item'][count($_SESSION['item'])] = $DescricaoMaterialServicoBanco . $SimboloConcatenacaoArray . $CodRedMaterialServicoBanco . $SimboloConcatenacaoArray . $UndMedidaSigla . $SimboloConcatenacaoArray . $TipoGrupoBanco . $SimboloConcatenacaoArray;
                                }
                            } else {
                                $_SESSION['item'][count($_SESSION['item'])] = $DescricaoMaterialServicoBanco . $SimboloConcatenacaoArray . $CodRedMaterialServicoBanco . $SimboloConcatenacaoArray . $UndMedidaSigla . $SimboloConcatenacaoArray . $TipoGrupoBanco . $SimboloConcatenacaoArray;
                            }
                        } else {
                            $_SESSION['item'][count($_SESSION['item'])] = $DescricaoMaterialServicoBanco . $SimboloConcatenacaoArray . $CodRedMaterialServicoBanco . $SimboloConcatenacaoArray . $UndMedidaSigla . $SimboloConcatenacaoArray . $SituacaoMaterial . $SimboloConcatenacaoArray . $TipoGrupoBanco . $SimboloConcatenacaoArray;
                        }
                    }
                }
            }
            
            echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
            echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
            echo "<script>self.close()</script>";
        }
    }
}

// Critica dos Campos #
if ($Botao == "Validar") {
    $Mens = 0;
    $Mensagem = "Informe: ";
    
    if ($SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirItem.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
    } elseif ($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta) < 2) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirItem.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
    }
    
    if ($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta)) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirItem.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
    } elseif ($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta) < 2) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadIncluirItem.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
    }
}
?>
<html>
<head>
    <title>Portal de Compras - Incluir Itens</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <script language="javascript" type="">
        <!--
            function checktodos(){
	            document.CadIncluirItem.Subclasse.value = '';
	            document.CadIncluirItem.submit();
            }
            
            function enviar(valor){
	            document.CadIncluirItem.Botao.value = valor;
	            document.CadIncluirItem.submit();
            }
            
            function validapesquisa(){
	            if ((document.CadIncluirItem.MaterialDescricaoDireta.value != '') || (document.CadIncluirItem.ServicoDescricaoDireta.value != '') || (document.CadIncluirItem.SubclasseDescricaoDireta.value != '')) { // ABACO
		            if (document.CadIncluirItem.Grupo) {
		    	        document.CadIncluirItem.Grupo.value = '';
		            }

		            if (document.CadIncluirItem.Classe) {
		    	        document.CadIncluirItem.Classe.value = '';
		            }

		            document.CadIncluirItem.Botao.value = 'Validar';
	            }

	            if (document.CadIncluirItem.Subclasse) {
		            if (document.CadIncluirItem.SubclasseDescricaoFamilia.value != "") {
		    	        document.CadIncluirItem.Subclasse.value = '';
		            }
	            }

	            document.CadIncluirItem.submit();
            }

            function AbreJanela(url,largura,altura) {
	            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
            }

            function voltar(){
	            self.close();
            }

            function remeter(){
	            document.CadIncluirItem.submit();
            }
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
    <form action="CadIncluirItem.php" method="post" name="CadIncluirItem">
        <table cellpadding="0" border="0" summary="">
            <!-- Erro -->
            <tr>
                <td align="left" colspan="5">
				    <?php if ($Mens != 0) { ExibeMens($Mensagem, $Tipo, 1);	} ?>
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
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
										    INCLUIR - SELEÇÃO DE MATERIAL<?php echo strtoupper2($TextoServico); ?>
									    </td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal" colspan="5">
                                            <p align="justify">
											Para incluir um material<?php echo $TextoServico; ?> selecione o item de pesquisa desejado,
											preencha o argumento da pesquisa e clique no botão "Pesquisar".
											Depois, clique no material<?php echo $TextoServico; ?> desejado e clique no botão "Incluir".
											Para voltar para a tela anterior clique no botão "Voltar".
										    </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <table border="0" width="100%" summary="">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Tipo de Pesquisa</td>
                                                    <td class="textonormal" height="20">
                                                        <select name="TipoPesquisa" class="textonormal">
												        <?php
                                                        if ($PesqApenas == 'C') {
                                                            if ($TextoServico != "") {
                                                                $TextoServico = substr($TextoServico, 0, 1) . ucfirst(substr($TextoServico, 1));
                                                            }
                                                            echo "<option value=\"0\">Cadastro de Material" . $TextoServico . "</option>";
                                                        } elseif ($PesqApenas == 'E') {
                                                            echo "<option value=\"1\">Itens em Estoque</option>";
                                                        } else { ?>
														    <option value="0" <?php if( $TipoPesquisa == 0 ){ echo "selected"; } ?>>Cadastro de Material</option>
                                                                <?php if( $TipoPesquisa == 1 or $TipoPesquisa == "" ){ echo "selected"; } ?>
                                                                >Itens em Estoque
                                                            </option> -->
												        <?php } ?>
													</select></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="5">PESQUISA DIRETA</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <table border="0" width="100%" summary="">
                                            <?php
                                                if ($PesqApenas != 'S') {
                                                    ?>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
                                                        <td class="textonormal" colspan="5">
                                                            <select name="OpcaoPesquisaSubClasse" class="textonormal">
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="SubclasseDescricaoDireta" value="<?php echo $SubclasseDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItem.ServicoDescricaoDireta.value = '';document.CadIncluirItem.MaterialDescricaoDireta.value = '';document.CadIncluirItem.TipoGrupo.value = 'M';">
                                                            <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                //}
                                                //if ($PesqApenas != 'S') {
                                                    ?>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Material</td>
                                                        <td class="textonormal" colspan="5">
                                                            <select name="OpcaoPesquisaMaterial" class="textonormal">
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="MaterialDescricaoDireta" value="<?php echo $MaterialDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItem.ServicoDescricaoDireta.value = '';document.CadIncluirItem.SubclasseDescricaoDireta.value = '';document.CadIncluirItem.TipoGrupo.value = 'M';">
                                                            <a href="javascript:validapesquisa();">
                                                                <img src="../midia/lupa.gif" border="0">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
											    <?php
                                                $listaProgramasLiberados = array(
                                                    'CadSolicitacaoCompraIncluirManterExcluir',
                                                    'CadRegistroPrecoIntencaoManter',
                                                    'CadRegistroPrecoIntencaoIncluir',
                                                    'cadMigracaoAtaAlterar',
                                                    'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar',
                                                    'CadAtaRegistroPrecoExternaIncluir',
                                                    'CadAtaRegistroPrecoExternaSelecionarAlterar',
                                                    'ConsHistoricoPesquisarGeral',
                                                    'CadContratoAntigoIncluir',
                                                    'CadContratoManter'
                                                );
                                                if (in_array($ProgramaOrigem, $listaProgramasLiberados) or ($PesqApenas == 'S')) { // ABACO
                                                    ?>
												    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>
                                                        <td class="textonormal" colspan="2">
                                                            <select name="OpcaoPesquisaServico" class="textonormal">
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="ServicoDescricaoDireta" value="<?php echo $ServicoDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItem.MaterialDescricaoDireta.value = '';document.CadIncluirItem.SubclasseDescricaoDireta.value = '';document.CadIncluirItem.TipoGrupo.value = 'S';">
                                                            <a href="javascript:remeter();"><img src="../midia/lupa.gif" border="0" alt="0"></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
										    </table>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($PesqApenas != 'S') {
                                        ?>
                                        <tr>
                                            <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="5">PESQUISA POR FAMILIA - MATERIAL<?php echo strtoupper2($TextoServico); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
                                                    <tr>
                                                        <td colspan="5">
                                                            <table class="textonormal" border="0" width="100%" summary="">
									    			        <?php
                                                            if ($ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == 'CadContratoManter' || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'ConsHistoricoPesquisarGeral') {
                                                            ?>
									    					<tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="34%">Tipo de Grupo</td>
                                                                    <td class="textonormal">
                                                                        <input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadIncluirItem.MaterialDescricaoDireta.value='';document.CadIncluirItem.SubclasseDescricaoDireta.value='';document.CadIncluirItem.ServicoDescricaoDireta.value='';document.CadIncluirItem.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?>> Material
                                                                        <input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadIncluirItem.MaterialDescricaoDireta.value='';document.CadIncluirItem.SubclasseDescricaoDireta.value='';document.CadIncluirItem.ServicoDescricaoDireta.value='';document.CadIncluirItem.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?>> Serviço
                                                                    </td>
                                                                </tr>
									    			        <?php } ?>
									    			        <?php if ($TipoGrupo == "M") { ?>
									    					<tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
                                                                    <td class="textonormal">
                                                                        <input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadIncluirItem.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?>/>Consumo
                                                                        <input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadIncluirItem.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?>/>Permanente
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
                                                                            // Mostra os grupos cadastrados #
                                                                            if (($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S") {                    
                                                                                $sql = "SELECT CGRUMSCODI,EGRUMSDESC
									    								    		    FROM SFPC.TBGRUPOMATERIALSERVICO
									    								    		    WHERE FGRUMSTIPO = '$TipoGrupo' AND FGRUMSSITU = 'A' ";
                    
                                                                                    if ($TipoGrupo == "M" and $TipoMaterial != "") {
                                                                                        $sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
                                                                                    }

                                                                                $sql .= " ORDER BY EGRUMSDESC ";
                    
                                                                                $res = $db->query($sql);
                                                                                
                                                                                if (db::isError($res)) {
                                                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                                } else {
                                                                                    while ($Linha = $res->fetchRow()) {
                                                                                        $Descricao = substr($Linha[1], 0, 75);

                                                                                        if ($Linha[0] == $Grupo) {
                                                                                            echo "<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
                                                                                        } else {
                                                                                            echo "<option value=\"$Linha[0]\">$Descricao</option>\n";
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
                                                                if ($Grupo != "") {
                                                                ?>
									    					    <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7">Classe</td>
                                                                    <td class="textonormal">
                                                                        <select name="Classe" class="textonormal" onChange="javascript:remeter();">
                                                                            <option value="">Selecione uma Classe...</option>
									    									<?php
                                                                            if ($Grupo != "") {
                                                                                $db = Conexao();

                                                                                $sql = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
                                                                                $sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
                                                                                $sql .= " WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $Grupo AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' ";
                                                                                $sql .= " ORDER BY ECLAMSDESC";
                    
                                                                                $res = $db->query($sql);
                                                                                
                                                                                if (db::isError($res)) {
                                                                                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                                } else {
                                                                                    while ($Linha = $res->fetchRow()) {
                                                                                        $Descricao = substr($Linha[1], 0, 75);
                                                                                        
                                                                                        if ($Linha[0] == $Classe) {
                                                                                            echo "<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
                                                                                        } else {
                                                                                            echo "<option value=\"$Linha[0]\">$Descricao</option>\n";
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
                                                                if ($Grupo != "" and $Classe != "" and $TipoGrupo == "M") { // Apenas para Material
                                                                ?>
									    					    <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
                                                                    <td class="textonormal">
                                                                        <select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
                                                                            <option value="">Selecione uma Subclasse...</option>
									    								    <?
                                                                            $db = Conexao();
                                                                            
                                                                            $sql = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                                                                            $sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                                                                            $sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                                                                            $sql .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                                                                            $sql .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                                                                            $sql .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                                                                            $sql .= "   AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe' ";
                                                                            $sql .= "    ORDER BY ESUBCLDESC ";
                                                                            
                                                                            $result = $db->query($sql);
                
                                                                            if (db::isError($result)) {
                                                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                            } else {
                                                                                while ($Linha = $result->fetchRow()) {
                                                                                    $Descricao = substr($Linha[1], 0, 75);
                                                                                    
                                                                                    if ($Linha[0] == $Subclasse) {
                                                                                        echo "<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
                                                                                    } else {
                                                                                        echo "<option value=\"$Linha[0]\">$Descricao</option>\n";
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                        <input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
                                                                        <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
                                                                        <input type="checkbox" name="chkSubclasse" onClick="javascript:checktodos();" value="T" <?php if($ChkSubclasse == "T") { echo ("checked"); } ?>>Todas
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
                                    }
                                    ?>
								    <?php
                                    if ($MaterialDescricaoDireta != "") {
                                        if ($OpcaoPesquisaMaterial == 0) {
                                            if (! SoNumeros($MaterialDescricaoDireta)) {
                                                $sqlgeral = "";
                                            }
                                        }
                                    }
                                    if ($SubclasseDescricaoDireta != "") {
                                        if ($OpcaoPesquisaSubClasse == 0) {
                                            if (! SoNumeros($SubclasseDescricaoDireta)) {
                                                $sqlgeral = "";
                                            }
                                        }
                                    }        
                                    if ($sqlgeral != "" and $Mens == 0) {
                                        if ((($MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") or ($Subclasse != "") or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse == "T")) /* Validação para Material */ or ($ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and $Classe != 0))) /* Validação para Serviço */ {
                                            $db = Conexao();
                                            $res = $db->query($sqlgeral);
                
                                            if (db::isError($res)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlgeral");
                                            } else {
                                                $qtdres = $res->numRows();
                                                echo "<tr>\n";
                                                echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
                                                echo "</tr>\n";
                    
                                                if ($qtdres > 0) {
                                                    $TipoMaterialAntes  = "";
                                                    $GrupoAntes         = "";
                                                    $ClasseAntes        = "";
                                                    $SubClasseAntes     = "";
                                                    $SubClasseSequAntes = "";
                                                    $irow               = 1;
                                                    
                                                    while ($row = $res->fetchRow()) {
                                                        $GrupoCodigo = $row[0];
                                                        $GrupoDescricao = $row[1];
                                                        $ClasseCodigo = $row[2];
                                                        $ClasseDescricao = $row[3];
                                                        $TipoGrupoBanco = $row[4];
                                                        $CodRedMaterialServicoBanco = $row[5]; // $MaterialSequencia
                                                        $DescricaoMaterialServicoBanco = $row[6]; // $MaterialDescricao
                            
                                                        if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
                                                            // PARA MATERIAL
                                                            $SubClasseSequ = $row[8];
                                                            $SubClasseDescricao = $row[9];
                                                            $UndMedidaSigla = $row[10];
                                                            $TipoMaterialCodigo = $row[11];
                                                        }                            
                            
                                                        if ($TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo) { // PARA MATERIAL
                                                            echo "<tr>\n";
                                                            echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"5\" align=\"center\">";
                                                            
                                                            if ($TipoMaterialCodigo == "C") {
                                                                echo "CONSUMO";
                                                            } else {
                                                                echo "PERMANENTE";
                                                            }
                                                            
                                                            echo "  </td>\n";
                                                            echo "</tr>\n";
                                                        }
                            
                                                        if ($GrupoAntes != $GrupoDescricao) {
                                                            if ($ClasseAntes != $ClasseDescricao) {
                                                                echo "<tr>\n";
                                                                echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                                echo "</tr>\n";
                                                            }
                                                        } else {
                                                            if ($ClasseAntes != $ClasseDescricao) {
                                                                echo "<tr>\n";
                                                                echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                                echo "</tr>\n";
                                                            }
                                                        }
                            
                                                        // COLOCAR DESCRIÇÃO MATERIAL OU SERVIÇO E AJUSTAR DAS VARIAVEIS DE MATERIAL E SERVIÇO AQUI
                                                        if ($TipoGrupoBanco == "M") {
                                                            $Descricao = "Material";
                                                        } else {
                                                            $Descricao = "Serviço";
                                                        }
                            
                                                        if ($ClasseAntes != $ClasseDescricao) {
                                                            echo "<tr>\n";

                                                            if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"18%\">SUBCLASSE</td>\n";
                                                            }
                                
                                                            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DESCRIÇÃO DO " . strtoupper2($Descricao) . "</td>\n";
                                                            echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\">CÓD.RED.</td>\n";
                                
                                                            if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">UNIDADE</td>\n";
                                                            }

                                                            if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                                echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">Nº REPETIÇÕES</td>\n";
                                                            }

                                                            echo "</tr>\n";
                                                        }
                                                        echo "<tr>\n";
                                                        
                                                        if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) { // PARA MATERIAL
                                                            $flg = "S";
                                                            echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                            echo "    $SubClasseDescricao";
                                                            echo "  </td>\n";
                                                        }
                            
                                                        if ($flg == "S") { // Apenas para material
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
                                                            echo "		<input type=\"checkbox\" name=\"chkMaterial$CodRedMaterialServicoBanco\" value=\"$CodRedMaterialServicoBanco\">";
                                                            $Url = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco";
                                                            echo "		<a href=\"javascript:AbreJanela('$Url',700,340);\"><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
                                                            echo "	</td>\n";
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"11%\">\n";
                                                            echo "		$CodRedMaterialServicoBanco";
                                                            echo "	</td>\n";
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                            echo "		$UndMedidaSigla";
                                                            echo "	</td>\n";
                                                            
                                                            
                                                            $flg = "";
                                                        } else {
                                                            if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                                echo "		&nbsp;";
                                                                echo "	</td>\n";
                                                            }
                                                            
                                                            // Ocorre para Material e Serviço
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
                                                            echo "		<input type=\"checkbox\" name=\"chkMaterial$CodRedMaterialServicoBanco\" value=\"$CodRedMaterialServicoBanco\">";
                                                            $Url = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco";
                                                            echo "		<a href=\"javascript:AbreJanela('$Url',700,340);\"><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
                                                            echo "	</td>\n";
                                                            echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"11%\">\n";
                                                            echo "		$CodRedMaterialServicoBanco";
                                                            echo "  </td>\n";
                                                            
                                                            if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                                echo "		$UndMedidaSigla";
                                                                echo "	</td>\n";
                                                            }
                                                        }
                                                        
                                                        if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                            $Repeticoes = 1;
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                            echo "		<input type=\"text\" name=\"Repeticoes$CodRedMaterialServicoBanco\" value=\"$Repeticoes\" size=\"10\" maxlength=\"10\" class=\"textonormal\" onFocus=\"javascript:document.CadIncluirItem.Repeticoes$CodRedMaterialServicoBanco.value\">";
                                                            echo "	</td>\n";
                                                        }
                            
                                                        if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                            $_SESSION['GetUrl'][] = $Url;
                                                        }
                                                        echo "</tr>\n";
                                                        
                                                        $TipoMaterialAntes  = $TipoMaterialCodigo;
                                                        $GrupoAntes         = $GrupoDescricao;
                                                        $ClasseAntes        = $ClasseDescricao;
                                                        $SubClasseAntes     = $SubClasseDescricao;
                                                        $SubClasseSequAntes = $SubClasseSequ;
                                                    }
                                                    $db->disconnect();
                                                } else {
                                                    echo "<tr>\n";
                                                    echo "	<td valign=\"top\" colspan=\"5\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
                                                    echo "		Pesquisa sem Ocorrências.\n";
                                                    echo "	</td>\n";
                                                    echo "</tr>\n";
                                                }
                                            }
                                        }
                                    }
                                    ?>
								    <tr>
                                        <td colspan="5" align="right">
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
    </script>
</body>
</html>