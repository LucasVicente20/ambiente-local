<?php
/**
 * Portal de Compras
 * Programa: CadIncluirItemPlanejamento.php
 * Autor: Diógenes Dantas | Madson Felix
 * Data: 21/11/2022
 * Objetivo: Programa para inclusão de item de planejamento
 * Tarefa Redmine: #275243
 * -------------------------------------------------------------------
 * Alterado: João Madson   
  * Data: 09/01/2023
  * Tarefa: #277372
 * -------------------------------------------------------------------
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
    $OpcaoPesquisaClasse   = $_POST['OpcaoPesquisaClasse'];    
    $OpcaoPesquisaServico     = $_POST['OpcaoPesquisaServico'];  
    $ClasseDescricaoDireta = strtoupper2(trim($_POST['ClasseDescricaoDireta']));
    $MaterialDescricaoDireta  = strtoupper2(trim($_POST['MaterialDescricaoDireta']));    
    $ServicoDescricaoDireta   = strtoupper2(trim($_POST['ServicoDescricaoDireta']));
    
    $PesqApenas = $_POST['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
    $Zerados    = $_POST['Zerados']; // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
    $sqlpost    = str_replace('\\', '', $_POST['sqlgeral']); // Criado para resolver demora para cadastrar itens do cadastro de materiais. No "Insere" era executado um select que retornava todos os materiais da DLC, com esta variável, é executado na inclusão o mesmo select da pesquisa
    $classePredefinida = $_POST['classePredefinida'];
} else {
    unset($_SESSION['dadosClassMatServ']);
    $TipoGrupo      = $_GET['TipoGrupo'];
    $ProgramaOrigem = $_GET['ProgramaOrigem'];
    $Almoxarifado   = $_GET['Almoxarifado'];
    $Grupo          = $_GET['Grupo'];
    $Classe         = $_GET['Classe'];
    $Subclasse      = $_GET['Subclasse']; // Null - Considerar para Serviço
    $PesqApenas     = $_GET['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
    $Zerados        = $_GET['Zerados']; // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
    $classePredefinida = $_GET['classePredefinida'];
    if(!empty($classePredefinida)){
        $Botao = "Validar";
    }
}
// Identifica o Programa para Erro de Banco de Dados
$ErroPrograma = __FILE__;

// Ano da Requisição Ano Atual
$AnoRequisicao = date("Y");

if ($TipoGrupo == null || is_null($TipoGrupo)) {
    $TipoGrupo = "C";
}

if (isset($TipoMaterial)) {
    $ClasseDescricaoDireta = '';
    $MaterialDescricaoDireta  = '';
    $ServicoDescricaoDireta   = '';
}

$listaProgramasLiberados = array(
    'CadIncluirDFD'
);

if (in_array($ProgramaOrigem, $listaProgramasLiberados)) {
    $TextoServico = "/SERVIÇO"; // Utilizado para colocar na tela a descrição /Serviço ou /SERVIÇO quando a tela de inclusão de itens for chamado pelas telas de Solicitação de Compras
    
    if ($ServicoDescricaoDireta != "" && $ClasseDescricaoDireta == "" && $MaterialDescricaoDireta == "") {
        $TipoGrupo = "S";
    }
} else {
    $TextoServico = "";
}
// var_dump($TipoGrupo);exit;
if($TipoGrupo == "C" || empty($TipoGrupo)){ //C igual pesquisa por Classe, M igual pesquisa por Material e S igual Pesquisa por Serviço
    $db = Conexao();
    unset($_SESSION['dadosClassMatServ']);
    for($i=0; $i<2; $i++){
        // Monta o sql para montagem dinâmica da grade a partir da pesquisa
        $sql = "SELECT DISTINCT (GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, GRU.FGRUMSTIPO, ";

        // if (($i == 0 or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
        if ($i == 0 ) {
            $sql .= "MAT.CMATEPSEQU, MAT.EMATEPDESC, MAT.CMATEPSITU, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM ";
        } else {
            $sql .= "SERV.CSERVPSEQU, SERV.ESERVPDESC, SERV.CSERVPSITU, 0, null, null, null ";
        }

        // Verifica o Tipo de Pesquisa para definir o relacionamento #
        $from  = "FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
        $from .= "INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";

        // if (($i == 0 or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
        if ($i == 0 ) {
            // PARA MATERIAL
            $from .= "INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
            $from .= "INNER JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
            $from .= "INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI ";
        } else {
            // PARA SERVIÇO
            $from .= "INNER JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
        }

        if ($TipoPesquisa != 0 && $i == 0) {
            $from .= "INNER JOIN SFPC.TBARMAZENAMENTOMATERIAL ITEM ON MAT.CMATEPSEQU = ITEM.CMATEPSEQU ";

            if ($Zerados == 'N') {
                $from .= "AND ITEM.AARMATQTDE > 0 ";
            }

            $from .= "INNER JOIN SFPC.TBLOCALIZACAOMATERIAL LOC ON LOC.CLOCMACODI = ITEM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
        }

        $where = "WHERE 1 = 1 "; // Artificio utilizado para colocar o 'AND' na clausula WHERE sem se preocupar.


        // if ($ProgramaOrigem == 'CadInventarioInicialContagem' || $ProgramaOrigem == 'CadInventarioPeriodicoIncluirItem' || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadMaterialAlterar' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir' || $ProgramaOrigem == 'cadMigracaoAtaAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
            // if (($i == 0 or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
            if ($i == 0) {
                // PARA MATERIAL
                $where .= " AND (MAT.CMATEPSITU <> 'I' AND SUB.FSUBCLSITU <> 'I' ";
            } else {
                // PARA SERVIÇO
                $where .= " AND (SERV.CSERVPSITU <> 'I' ";
            }

            // Concatenando com o material ou serviço
            $where .= " AND CLA.FCLAMSSITU <> 'I' AND GRU.FGRUMSSITU <> 'I') ";
        // }

        // Verifica se o Tipo de Material foi escolhido #
        if ($i == 0 and $TipoMaterial != "") {
            $where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
        }

        // Verifica se o Grupo foi escolhido #
        if ($Grupo != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
            $where .= " AND GRU.CGRUMSCODI = $Grupo ";
        }

        // Verifica se a Classe foi escolhida #
        if ($Grupo != "" and $Classe != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
            $where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
        }

        // Verifica se a SubClasse foi escolhida #
        if ($Subclasse != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
            $where .= " AND CLA.CSUBCLSEQU = $Subclasse ";
        }

        // Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
        if ($SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
            
            $where .= " AND ( ";
            $where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE('%" .$SubclasseDescricaoFamilia. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
            $where .= "     )";
        }
        //Se a classe tiver sido informada pela na tela de incluir DFD
        if(!empty($classePredefinida)){
            $where .= " AND CLA.CCLAMSCODI = $classePredefinida ";
        }
        
        if ($ClasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
            if ($OpcaoPesquisaClasse == 0) {
                if (SoNumeros($ClasseDescricaoDireta)) {
                    $where .= " AND CLA.CCLAMSCODI = $ClasseDescricaoDireta ";
                }
            } elseif ($OpcaoPesquisaClasse == 1) {
                $where .= "  AND TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE  TRANSLATE('%" .$ClasseDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
                
                $where .= " AND ( ";
                $where .= "    TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE  TRANSLATE('%" .$ClasseDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
                $where .= "     )";
            } else {
                $where .= "   AND TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE('" .$ClasseDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
            }
        }

        // // Se foi digitado algo na caixa de texto do material em pesquisa direta #
        // if ($MaterialDescricaoDireta != "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        //     if ($OpcaoPesquisaMaterial == 0) {
        //         if (SoNumeros($MaterialDescricaoDireta)) {
        //             $where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
        //         }
        //     } elseif ($OpcaoPesquisaMaterial == 1) {
        //         $where .= " AND ( ";
        //         $where .= "      TRANSLATE(MAT.EMATEPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE '%" . strtoupper2(RetiraAcentos($MaterialDescricaoDireta)) . "%' ";
        //         $where .= "     )";
        //     } else {
        //         $where .= "  AND TRANSLATE(MAT.EMATEPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE '" . strtoupper2(RetiraAcentos($MaterialDescricaoDireta)) . "%' ";
        //     }
        // }
        // var_dump($OpcaoPesquisaServico);
        // // Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
        // if ($ServicoDescricaoDireta != "" and $ClasseDescricaoDireta == "" and $MaterialDescricaoDireta == "") {
        //     if ($OpcaoPesquisaServico == 0) {
        //         if (SoNumeros($ServicoDescricaoDireta)) {
        //             $where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
        //         }
        //     } elseif ($OpcaoPesquisaServico == 1) {
        //         $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE '%" . strtoupper2(RetiraAcentos($ServicoDescricaoDireta)) . "%' ";
        //     } else {
        //         $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE '" . strtoupper2(RetiraAcentos($ServicoDescricaoDireta)) . "%' ";
        //     }
        // }

            $order = " ORDER BY ECLAMSDESC, EMATEPDESC, FGRUMSTIPM, EGRUMSDESC, ESUBCLDESC ";
        

        if($i==0){
            $sqlgeral = $sql . $from . $where ." union all ";
            // print_r($where);
        }
        if($i==1){
            $sqlgeral .= $sql . $from . $where . $order;
        }
    }   
        $resultado = executarSql($db, $sqlgeral);
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $_SESSION['dadosClassMatServ'][] = $retorno;
        }
        $sqlgeral = "";
}else{
    unset($_SESSION['dadosClassMatServ']);
    // Monta o sql para montagem dinâmica da grade a partir da pesquisa
    $sql = "SELECT DISTINCT (GRU.CGRUMSCODI), GRU.EGRUMSDESC, CLA.CCLAMSCODI, CLA.ECLAMSDESC, GRU.FGRUMSTIPO, ";

    if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
        $sql .= "MAT.CMATEPSEQU, MAT.EMATEPDESC, MAT.CMATEPSITU, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM, MAT.FMATEPGENE ";
    } else {
        $sql .= "SERV.CSERVPSEQU, SERV.ESERVPDESC, SERV.CSERVPSITU ";
    }

    // Verifica o Tipo de Pesquisa para definir o relacionamento #
    $from  = "FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
    $from .= "INNER JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";

    if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
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


    if ($ProgramaOrigem == 'CadInventarioInicialContagem' || $ProgramaOrigem == 'CadInventarioPeriodicoIncluirItem' || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadMaterialAlterar' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaSelecionarIncluir' || $ProgramaOrigem == 'cadMigracaoAtaAlterar' || $ProgramaOrigem == 'CadAtaRegistroPrecoExternaIncluir' || $ProgramaOrigem == 'CadAtaRegistroPrecoInternaManterEspecialAtasAlterar' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
        if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
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
    if ($TipoGrupo == "M" and $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "") {
        $where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
    }

    // Verifica se o Grupo foi escolhido #
    if ($Grupo != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        $where .= " AND GRU.CGRUMSCODI = $Grupo ";
    }

    // Verifica se a Classe foi escolhida #
    if ($Grupo != "" and $Classe != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        $where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
    }

    // Verifica se a SubClasse foi escolhida #
    if ($Subclasse != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        $where .= " AND CLA.CSUBCLSEQU = $Subclasse ";
    }

    // Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
    if ($SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        
        $where .= " AND ( ";
        $where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE ('%" .$SubclasseDescricaoFamilia . "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
        $where .= "     )";
    }
    //Se a classe tiver sido informada pela na tela de incluir DFD
    if(!empty($classePredefinida)){
        $where .= " AND CLA.CCLAMSCODI = $classePredefinida ";
    }

    if ($ClasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        if ($OpcaoPesquisaClasse == 0) {
            if (SoNumeros($ClasseDescricaoDireta)) {
                $where .= " AND CLA.CCLAMSCODI = $ClasseDescricaoDireta ";
            }
        } elseif ($OpcaoPesquisaClasse == 1) {
            $where .= "  AND TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE ('%" .$ClasseDescricaoDireta . "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
            
            $where .= " AND ( ";
            $where .= "    TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE ('%" .$ClasseDescricaoDireta . "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
            $where .= "     )";
        } else {
            $where .= "   AND TRANSLATE(CLA.ECLAMSDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE ('" .$ClasseDescricaoDireta . "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
        }
    }

    // Se foi digitado algo na caixa de texto do material em pesquisa direta #
    if ($MaterialDescricaoDireta != "" and $ClasseDescricaoDireta == "" and $ServicoDescricaoDireta == "") {
        if ($OpcaoPesquisaMaterial == 0) {
            if (SoNumeros($MaterialDescricaoDireta)) {
                $where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
            }
        } elseif ($OpcaoPesquisaMaterial == 1) {
            $where .= " AND ( ";
            $where .= "      TRANSLATE(MAT.EMATEPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE  TRANSLATE('%" .$MaterialDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
            $where .= "     )";
        } else {
            $where .= "  AND TRANSLATE(MAT.EMATEPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE( '" .$MaterialDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
        }
    }

    // Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
    if ($ServicoDescricaoDireta != "" and $ClasseDescricaoDireta == "" and $MaterialDescricaoDireta == "") {
        if ($OpcaoPesquisaServico == 0) {
            if (SoNumeros($ServicoDescricaoDireta)) {
                $where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
            }
        } elseif ($OpcaoPesquisaServico == 1) {
            $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE  TRANSLATE('%" .$ServicoDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
        } else {
            $where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ILIKE TRANSLATE( '" .$ServicoDescricaoDireta. "%','ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ','AAAaaaEEeeIIiiOOOoooUUuuCcNn') ";
        }
    }

    if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
        $order = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";
    } else {
        $order = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, SERV.ESERVPDESC ";
    }

    // Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
    $sqlgeral = $sql . $from . $where . $order;
}



// print_r($sqlgeral);die;
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
                $descClasse = $row[3];
                $codClasse = $row[2];
                $codGrupo = $row[0];
                $TipoGrupoBanco = $row[4];
                $CodRedMaterialServicoBanco = $row[5]; // $MaterialSequencia
                $DescricaoMaterialServicoBanco = $row[6]; // $MaterialDescricao
                $UndMedidaSigla = $row[10];
                $SituacaoMaterial = $row[7]; // Situação: Ativo (A) ou Inativo (I).
                $checkmaterial = "chkMaterial";
                $checkmaterial .= $CodRedMaterialServicoBanco;
                $check_material = $_POST[$checkmaterial];
                $RepeticoesPost = 'Repeticoes'.$CodRedMaterialServicoBanco;
                $RepeticoesPost = $_POST[$RepeticoesPost];
                $n = 0;

                if ($check_material != "") {
                    // O tipo do grupo (Material ou Serviço) deve ser enviado para os programas abaixo, pois estes utilizaram a inclusão de materias e serviços ativosW                    
                    $_SESSION['item'][] = array(
                        "DescricaoMaterialServicoBanco"  =>  $DescricaoMaterialServicoBanco,
                        "CodRedMaterialServicoBanco"     =>  $CodRedMaterialServicoBanco,
                        "UndMedidaSigla"                 =>  $UndMedidaSigla,
                        "SituacaoMaterial"               =>  $SituacaoMaterial,
                        "TipoGrupoBanco"                 =>  $TipoGrupoBanco,
                        "DescClasse"                     =>$descClasse,
                        "CodClasse"                      =>$codClasse,
                        "CodGrupo"                       =>$codGrupo
                    );
                }
            }
            
            echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
            echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
            echo "<script>self.close()</script>";
        }
    }
    if(!empty($_SESSION['dadosClassMatServ'])){
        foreach($_SESSION['dadosClassMatServ'] as $dados) {
            $descClasse = $dados->eclamsdesc;
            $codClasse = $dados->cclamscodi;
            $codGrupo = $dados->cgrumscodi;
            $TipoGrupoBanco = $dados->fgrumstipo;
            $CodRedMaterialServicoBanco = !empty($dados->cmatepsequ)? $dados->cmatepsequ:$dados->cservpsequ; // $MaterialSequencia
            $DescricaoMaterialServicoBanco = !empty($dados->ematepdesc)? $dados->ematepdesc:$dados->eservpdesc;  // $MaterialDescricao
            $UndMedidaSigla = $dados->eunidmsigl;
            $SituacaoMaterial = !empty($dados->cmatepsitu)? $dados->cmatepsitu:$dados->cservpsitu; // Situação: Ativo (A) ou Inativo (I).
            $checkmaterial = "chkMaterial";
            $checkmaterial .= $CodRedMaterialServicoBanco.$TipoGrupoBanco;
            $check_material = $_POST[$checkmaterial];
            $RepeticoesPost = 'Repeticoes'.$CodRedMaterialServicoBanco;
            $RepeticoesPost = $_POST[$RepeticoesPost];
            $n = 0;

            if ($check_material != "") {
                // O tipo do grupo (Material ou Serviço) deve ser enviado para os programas abaixo, pois estes utilizaram a inclusão de materias e serviços ativosW                    
                $_SESSION['item'][] = array(
                    "DescricaoMaterialServicoBanco"  =>  $DescricaoMaterialServicoBanco,
                    "CodRedMaterialServicoBanco"     =>  $CodRedMaterialServicoBanco,
                    "UndMedidaSigla"                 =>  $UndMedidaSigla,
                    "SituacaoMaterial"               =>  $SituacaoMaterial,
                    "TipoGrupoBanco"                 =>  $TipoGrupoBanco,
                    "DescClasse"                     =>$descClasse,
                    "CodClasse"                      =>$codClasse,
                    "CodGrupo"                       =>$codGrupo
                );
            }
        }
        
        echo "<script>opener.document.$ProgramaOrigem.InicioPrograma.value=1</script>";
        echo "<script>opener.document.$ProgramaOrigem.submit()</script>";
        echo "<script>self.close()</script>";
    }
}

// Critica dos Campos #
if ($Botao == "Validar") {
    $Mens = 0;
    $Mensagem = "Informe: ";
    if(empty($classePredefinida)){
        if ($ClasseDescricaoDireta != "" and $OpcaoPesquisaClasse == 0 and ! SoNumeros($ClasseDescricaoDireta)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Classe</a>";
        } elseif ($ClasseDescricaoDireta != "" and ($OpcaoPesquisaClasse == 1 or $OpcaoPesquisaClasse == 2) and strlen($ClasseDescricaoDireta) < 2) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
        }
        
        if ($MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
        } elseif ($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta) < 2) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
        }
    }
}
?>
<html>
<head>
    <title>Portal de Compras - Incluir Itens</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <script language="javascript">
            function checktodos(){
	            document.CadIncluirItemPlanejamento.Subclasse.value = '';
	            document.CadIncluirItemPlanejamento.submit();
            }
            
            function enviar(valor){
	            document.CadIncluirItemPlanejamento.Botao.value = valor;
	            document.CadIncluirItemPlanejamento.submit();
            }
            
            function validapesquisa(){
	            if ((document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.value != '') || (document.CadIncluirItemPlanejamento.ServicoDescricaoDireta.value != '') || (document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.value != '')) { // ABACO
		            if (document.CadIncluirItemPlanejamento.Grupo) {
		    	        document.CadIncluirItemPlanejamento.Grupo.value = '';
		            }

		            if (document.CadIncluirItemPlanejamento.Classe) {
		    	        document.CadIncluirItemPlanejamento.Classe.value = '';
		            }

		            document.CadIncluirItemPlanejamento.Botao.value = 'Validar';
	            }

	            if (document.CadIncluirItemPlanejamento.Subclasse) {
		            if (document.CadIncluirItemPlanejamento.SubclasseDescricaoFamilia.value != "") {
		    	        document.CadIncluirItemPlanejamento.Subclasse.value = '';
		            }
	            }

	            document.CadIncluirItemPlanejamento.submit();
            }

            function AbreJanela(url,largura,altura) {
	            window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
            }

            function voltar(){
	            self.close();
            }

            function remeter(){
	            document.CadIncluirItemPlanejamento.submit();
            }
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
    <form action="CadIncluirItemPlanejamento.php" method="post" name="CadIncluirItemPlanejamento">
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
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF" width="650px">
                                    <input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
                                    <input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
										    INCLUIR - SELEÇÃO DE MATERIAL<?php echo strtoupper2($TextoServico); ?>
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
                                                        <td class="textonormal" bgcolor="#DCEDF7" width="31%">Classe</td>
                                                        <td class="textonormal" colspan="5">
                                                            <select name="OpcaoPesquisaClasse" class="textonormal" <?php echo (!empty($classePredefinida)) ? "disabled": "";?>>
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="ClasseDescricaoDireta" value="<?php echo $ClasseDescricaoDireta; ?>" <?php echo (!empty($classePredefinida)) ? "disabled": "";?> size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemPlanejamento.ServicoDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.TipoGrupo.value = 'C';">
                                                            <a <?php echo (!empty($classePredefinida)) ? "": 'href="javascript:validapesquisa();"';?> ><img src="../midia/lupa.gif" border="0"></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                //}
                                                //if ($PesqApenas != 'S') {
                                                    ?>
                                                    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Material</td>
                                                        <td class="textonormal" colspan="5">
                                                            <select name="OpcaoPesquisaMaterial" class="textonormal" <?php echo (!empty($classePredefinida)) ? "disabled": "";?>>
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="MaterialDescricaoDireta" value="<?php echo $MaterialDescricaoDireta; ?>" <?php echo (!empty($classePredefinida)) ? "disabled": "";?> size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemPlanejamento.ServicoDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.TipoGrupo.value = 'M';">
                                                            <a <?php echo (!empty($classePredefinida)) ? "": 'href="javascript:validapesquisa();"';?>>
                                                                <img src="../midia/lupa.gif" border="0">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
											    <?php
                                                $listaProgramasLiberados = array(
                                                    'CadIncluirDFD'
                                                );
                                                if (in_array($ProgramaOrigem, $listaProgramasLiberados) or ($PesqApenas == 'S')) { // ABACO
                                                    ?>
												    <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" width="34%">Serviço</td>
                                                        <td class="textonormal" colspan="2">
                                                            <select name="OpcaoPesquisaServico" class="textonormal" <?php echo (!empty($classePredefinida)) ? "disabled": "";?>>
                                                                <option value="0">Código Reduzido</option>
                                                                <option value="1">Descrição contendo</option>
                                                                <option value="2">Descrição iniciada por</option>
                                                            </select>
                                                            <input type="text" name="ServicoDescricaoDireta" value="<?php echo $ServicoDescricaoDireta; ?>" <?php echo (!empty($classePredefinida)) ? "disabled": "";?> size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.value = '';document.CadIncluirItemPlanejamento.TipoGrupo.value = 'S';">
                                                            <a <?php echo (!empty($classePredefinida)) ? "": 'href="javascript:validapesquisa();"';?>><img src="../midia/lupa.gif" border="0" alt="0"></a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
										    </table>
                                        </td>
                                    </tr>
                                    <?php
                                    //if ($PesqApenas != 'S' && empty($classePredefinida) ) {
                                        ?>
                                        <!-- <tr>
                                            <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="5">PESQUISA POR FAMILIA - MATERIAL<?php echo strtoupper2($TextoServico); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">
                                                <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
                                                    <tr>
                                                        <td colspan="5">
                                                            <table class="textonormal" border="0" width="100%" summary=""> -->
									    			        <?php
                                                            //if ($ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == 'CadContratoManter' || $ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoManter' || $ProgramaOrigem == 'CadRegistroPrecoIntencaoIncluir' || $ProgramaOrigem == 'ConsHistoricoPesquisarGeral') {
                                                            ?>
									    					<!-- <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" width="34%">Tipo de Grupo</td>
                                                                    <td class="textonormal">
                                                                        <input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.value='';document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.value='';document.CadIncluirItemPlanejamento.ServicoDescricaoDireta.value='';document.CadIncluirItemPlanejamento.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?>> Material
                                                                        <input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadIncluirItemPlanejamento.MaterialDescricaoDireta.value='';document.CadIncluirItemPlanejamento.ClasseDescricaoDireta.value='';document.CadIncluirItemPlanejamento.ServicoDescricaoDireta.value='';document.CadIncluirItemPlanejamento.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?>> Serviço
                                                                    </td>
                                                                </tr> -->
									    			        <?php //} ?>
									    			        <?php //if ($TipoGrupo == "M") { ?>
									    					<!-- <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
                                                                    <td class="textonormal">
                                                                        <input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadIncluirItemPlanejamento.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?>/>Consumo
                                                                        <input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadIncluirItemPlanejamento.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?>/>Permanente
                                                                    </td>
                                                                </tr> -->
									    					<?php //} ?>
									    					<?php //if( $TipoGrupo != "" ){ ?>
									    					<!-- <tr>
                                                                <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
                                                                    <td class="textonormal">
                                                                        <select name="Grupo" onChange="javascript:remeter();" class="textonormal">
                                                                            <option value="">Selecione um Grupo...</option> -->
									    								    <?php
                                                                            // $db = Conexao();
                                                                            // // Mostra os grupos cadastrados #
                                                                            // if (($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S") {                    
                                                                            //     $sql = "SELECT CGRUMSCODI,EGRUMSDESC
									    								    // 		    FROM SFPC.TBGRUPOMATERIALSERVICO
									    								    // 		    WHERE FGRUMSTIPO = '$TipoGrupo' AND FGRUMSSITU = 'A' ";
                    
                                                                            //         if ($TipoGrupo == "M" and $TipoMaterial != "") {
                                                                            //             $sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
                                                                            //         }

                                                                            //     $sql .= " ORDER BY EGRUMSDESC ";
                    
                                                                            //     $res = $db->query($sql);
                                                                                
                                                                            //     if (db::isError($res)) {
                                                                            //         ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                            //     } else {
                                                                            //         while ($Linha = $res->fetchRow()) {
                                                                            //             $Descricao = substr($Linha[1], 0, 75);

                                                                            //             if ($Linha[0] == $Grupo) {
                                                                            //                 echo "<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
                                                                            //             } else {
                                                                            //                 echo "<option value=\"$Linha[0]\">$Descricao</option>\n";
                                                                            //             }
                                                                            //         }
                                                                            //     }
                                                                            // }
                                                                            ?>
                                                                        <!-- </select>
                                                                    </td>
                                                                </tr> -->
									    					    <?php
                                                                //}
                                                                //if ($Grupo != "" && empty($classePredefinida)) {
                                                                ?>
									    					    <!-- <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7">Classe</td>
                                                                    <td class="textonormal">
                                                                        <select name="Classe" class="textonormal" onChange="javascript:remeter();">
                                                                            <option value="">Selecione uma Classe...</option> -->
									    									<?php
                                                                            //if ($Grupo != "") {
                                                                            //     $db = Conexao();

                                                                            //     $sql = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
                                                                            //     $sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
                                                                            //     $sql .= " WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $Grupo AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' ";
                                                                            //     $sql .= " ORDER BY ECLAMSDESC";
                    
                                                                            //     $res = $db->query($sql);
                                                                                
                                                                            //     if (db::isError($res)) {
                                                                            //         ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                            //     } else {
                                                                            //         while ($Linha = $res->fetchRow()) {
                                                                            //             $Descricao = substr($Linha[1], 0, 75);
                                                                                        
                                                                            //             if ($Linha[0] == $Classe) {
                                                                            //                 echo "<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
                                                                            //             } else {
                                                                            //                 echo "<option value=\"$Linha[0]\">$Descricao</option>\n";
                                                                            //             }
                                                                            //         }
                                                                            //     }
                                                                            //     $db->disconnect();
                                                                            // }
                                                                            ?>
                                                                        <!-- </select>
                                                                    </td>
                                                                </tr> -->
									    					    <?php
                                                                //}
                                                                //if ($Grupo != "" and $Classe != "" and $TipoGrupo == "M") { // Apenas para Material
                                                                ?>
									    					    <!-- <tr>
                                                                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
                                                                    <td class="textonormal">
                                                                        <select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
                                                                            <option value="">Selecione uma Subclasse...</option> -->
									    								    <?php
                                                                            // $db = Conexao();
                                                                            
                                                                            // $sql = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                                                                            // $sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                                                                            // $sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                                                                            // $sql .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                                                                            // $sql .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                                                                            // $sql .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                                                                            // $sql .= "   AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe' ";
                                                                            // $sql .= "    ORDER BY ESUBCLDESC ";
                                                                            
                                                                            // $result = $db->query($sql);
                
                                                                            // if (db::isError($result)) {
                                                                            //     ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                            // } else {
                                                                            //     while ($Linha = $result->fetchRow()) {
                                                                            //         $Descricao = substr($Linha[1], 0, 75);
                                                                                    
                                                                            //         if ($Linha[0] == $Subclasse) {
                                                                            //             echo '<option value="'.$Linha[0].'" selected>'.$Descricao.'</option>';
                                                                            //         } else {
                                                                            //             echo '<option value="'.$Linha[0].'">'.$Descricao.'</option>';
                                                                            //         }
                                                                            //     }
                                                                            // }
                                                                            ?>
                                                                        <!-- </select> -->
                                                                        <!-- <input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
                                                                        <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
                                                                        <input type="checkbox" name="chkSubclasse" onClick="javascript:checktodos();" value="T" <?php if($ChkSubclasse == "T") { echo ("checked"); } ?>>Todas -->
                                                                    <!-- </td>
                                                                </tr> -->
									    					    <?php// } ?>
									    				    <!-- </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr> -->
                                    <?php
                                    //}
                                    ?>
								    <?php
                                    if ($MaterialDescricaoDireta != "") {
                                        if ($OpcaoPesquisaMaterial == 0) {
                                            if (! SoNumeros($MaterialDescricaoDireta)) {
                                                $sqlgeral = "";
                                            }
                                        }
                                    }
                                    if ($ClasseDescricaoDireta != "") {
                                        if ($OpcaoPesquisaClasse == 0) {
                                            if (! SoNumeros($ClasseDescricaoDireta)) {
                                                $sqlgeral = "";
                                            }
                                        }
                                    }        
                                    if ($sqlgeral != "" and $Mens == 0 and empty($_SESSION['dadosClassMatServ'])) {
                                        if ((($MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") or ($Subclasse != "") or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse == "T")) /* Validação para Material */ or ($ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and $Classe != 0)) or !empty($classePredefinida)) /* Validação para Serviço */ {
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
                                                    $chaveCabecalho = true;
                                                    while ($row = $res->fetchRow()) {
                                                        $GrupoCodigo = $row[0];
                                                        $GrupoDescricao = $row[1];
                                                        $ClasseCodigo = $row[2];
                                                        $ClasseDescricao = $row[3];
                                                        $TipoGrupoBanco = $row[4];
                                                        $CodRedMaterialServicoBanco = $row[5]; // $MaterialSequencia
                                                        $DescricaoMaterialServicoBanco = $row[6]; // $MaterialDescricao
                                                        
                                                        if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
                                                            // PARA MATERIAL
                                                            $SubClasseSequ = $row[8];
                                                            $SubClasseDescricao = $row[9];
                                                            $UndMedidaSigla = $row[10];
                                                            $TipoMaterialCodigo = $row[11];
                                                        }                            
                            
                                                        // if ($TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo) { // PARA MATERIAL
                                                        //     echo "<tr>\n";
                                                        //     echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"5\" align=\"center\">";
                                                            
                                                        //     if ($TipoMaterialCodigo == "C") {
                                                        //         echo "CONSUMO";
                                                        //     } else {
                                                        //         echo "PERMANENTE";
                                                        //     }
                                                            
                                                        //     echo "  </td>\n";
                                                        //     echo "</tr>\n";
                                                        // }
                            
                                                        // if ($GrupoAntes != $GrupoDescricao) {
                                                        //     if ($ClasseAntes != $ClasseDescricao) {
                                                        //         echo "<tr>\n";
                                                        //         echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                        //         echo "</tr>\n";
                                                        //     }
                                                        // } else {
                                                        //     if ($ClasseAntes != $ClasseDescricao) {
                                                        //         echo "<tr>\n";
                                                        //         echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                        //         echo "</tr>\n";
                                                        //     }
                                                        // }
                            
                                                        // COLOCAR DESCRIÇÃO MATERIAL OU SERVIÇO E AJUSTAR DAS VARIAVEIS DE MATERIAL E SERVIÇO AQUI
                                                        if ($TipoGrupoBanco == "M") {
                                                            $Descricao = "Material";
                                                        } else {
                                                            $Descricao = "Serviço";
                                                        }
                            
                                                        if ($chaveCabecalho == true) {
                                                            echo "<tr>\n";

                                                            // if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"18%\">CLASSE</td>\n";
                                                            // }
                                
                                                            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DESCRIÇÃO DO " . strtoupper2($Descricao) . "</td>\n";
                                                            echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\">CÓD.RED.</td>\n";
                                
                                                            if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">UNIDADE</td>\n";
                                                            }

                                                            if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                                echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">Nº REPETIÇÕES</td>\n";
                                                            }

                                                            echo "</tr>\n";
                                                            $chaveCabecalho = false;
                                                        }
                                                        echo "<tr>\n";
                                                        // Remover depois
                                                        // if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) { // PARA MATERIAL
                                                        //     $flg = "S";
                                                        //     echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                        //     echo "    $SubClasseDescricao";
                                                        //     echo "  </td>\n";
                                                        // } 
                                                        // if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) { // PARA MATERIAL
                                                            $flg = "S";
                                                            echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                            echo "    $ClasseDescricao";
                                                            echo "  </td>\n";
                                                        // }
                                                            
                                                        if ($TipoGrupoBanco == "M") { // Apenas para material
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
                                                                echo "		-";
                                                                echo "	</td>\n";
                                                            }
                                                        }
                                                        
                                                        if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                            $Repeticoes = 1;
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                            echo "		<input type=\"text\" name=\"Repeticoes$CodRedMaterialServicoBanco\" value=\"$Repeticoes\" size=\"10\" maxlength=\"10\" class=\"textonormal\" onFocus=\"javascript:document.CadIncluirItemPlanejamento.Repeticoes$CodRedMaterialServicoBanco.value\">";
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
                                    if ($sqlgeral == "" and $Mens == 0 and !empty($_SESSION['dadosClassMatServ'])) {
                                        if ((($MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") or ($Subclasse != "") or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse == "T")) /* Validação para Material */ or ($ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and $Classe != 0)) or !empty($classePredefinida)) /* Validação para Serviço */ {
                                                
                                                $qtdres = count($_SESSION['dadosClassMatServ']);

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
                                                    $chaveCabecalho = true;
                                                    $FlagM = false;
                                                    $FlagS = false;
                                                    for($i=0; $i<count($_SESSION['dadosClassMatServ']); $i++){
                                                        if($_SESSION['dadosClassMatServ'][$i]->fgrumstipo == "M"){
                                                            $FlagM = true;
                                                        }
                                                        if($_SESSION['dadosClassMatServ'][$i]->fgrumstipo == "S"){
                                                            $FlagS = true;
                                                        }
                                                    }
                                                    if($FlagM == true && $FlagS == true){
                                                        $BuscaDupla = true;
                                                    }
                                                    foreach($_SESSION['dadosClassMatServ'] as $dados) {
                                                        $GrupoCodigo = $dados->cgrumscodi;
                                                        $GrupoDescricao = $dados->egrumsdesc;
                                                        $ClasseCodigo = $dados->cclamscodi;
                                                        $ClasseDescricao = $dados->eclamsdesc;
                                                        $TipoGrupoBanco = $dados->fgrumstipo;
                                                        $CodRedMaterialServicoBanco = !empty($dados->cmatepsequ)? $dados->cmatepsequ:$dados->cservpsequ; // $MaterialSequencia
                                                        $DescricaoMaterialServicoBanco = !empty($dados->ematepdesc)? $dados->ematepdesc:$dados->eservpdesc; // $MaterialDescricao
    
                                                        if (($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $ClasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
                                                            // PARA MATERIAL
                                                            $SubClasseSequ = $dados->subclsequ;
                                                            $SubClasseDescricao = $dados->esubcldesc;
                                                            $UndMedidaSigla = $dados->eunidmsigl;
                                                            $TipoMaterialCodigo = $dados->fgrumstipm;
                                                        }                     
                            
                                                        // if ($TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo) { // PARA MATERIAL
                                                        //     echo "<tr>\n";
                                                        //     echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"5\" align=\"center\">";
                                                            
                                                        //     if ($TipoMaterialCodigo == "C") {
                                                        //         echo "CONSUMO";
                                                        //     } else {
                                                        //         echo "PERMANENTE";
                                                        //     }
                                                            
                                                        //     echo "  </td>\n";
                                                        //     echo "</tr>\n";
                                                        // }
                            
                                                        // if ($GrupoAntes != $GrupoDescricao) {
                                                        //     if ($ClasseAntes != $ClasseDescricao) {
                                                        //         echo "<tr>\n";
                                                        //         echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                        //         echo "</tr>\n";
                                                        //     }
                                                        // } else {
                                                        //     if ($ClasseAntes != $ClasseDescricao) {
                                                        //         echo "<tr>\n";
                                                        //         echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"5\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
                                                        //         echo "</tr>\n";
                                                        //     }
                                                        // }
                            
                                                        // COLOCAR DESCRIÇÃO MATERIAL OU SERVIÇO E AJUSTAR DAS VARIAVEIS DE MATERIAL E SERVIÇO AQUI
                                                        if ($TipoGrupoBanco == "M") {
                                                            $Descricao = "Material";
                                                        } else {
                                                            $Descricao = "Serviço";
                                                        }
                            
                                                        if ($chaveCabecalho == true) {
                                                            echo "<tr>\n";

                                                            // if ($TipoGrupoBanco == "M") { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"18%\">CLASSE</td>\n";
                                                            // }
                                
                                                            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"60%\">DESCRIÇÃO DO " . strtoupper2($Descricao) . "</td>\n";
                                                            echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\">CÓD.RED.</td>\n";
                                
                                                            if ($TipoGrupoBanco == "M" || $BuscaDupla == true) { // Apenas para Material
                                                                echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">UNIDADE</td>\n";
                                                            }

                                                            if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                                echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"center\">Nº REPETIÇÕES</td>\n";
                                                            }

                                                            echo "</tr>\n";
                                                            $chaveCabecalho = false;
                                                        }
                                                        echo "<tr>\n";
                                                        // Remover depois
                                                        // if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) { // PARA MATERIAL
                                                        //     $flg = "S";
                                                        //     echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                        //     echo "    $SubClasseDescricao";
                                                        //     echo "  </td>\n";
                                                        // } 
                                                        // if ($TipoGrupoBanco == "M" and ($SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ)) { // PARA MATERIAL
                                                            $flg = "S";
                                                            echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"18%\">\n";
                                                            echo "    $ClasseDescricao";
                                                            echo "  </td>\n";
                                                        // }
                                                        if ($TipoGrupoBanco == "M") { // Apenas para material
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
                                                            echo "		<input type=\"checkbox\" name='chkMaterial".$CodRedMaterialServicoBanco."".$TipoGrupoBanco."' value=\"$CodRedMaterialServicoBanco\">";
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
                                                            echo "		<input type=\"checkbox\" name='chkMaterial".$CodRedMaterialServicoBanco."".$TipoGrupoBanco."' value=\"$CodRedMaterialServicoBanco\">";
                                                            $Url = "../estoques/CadItemDetalhe.php?Material=$CodRedMaterialServicoBanco&TipoGrupo=$TipoGrupoBanco";
                                                            echo "		<a href=\"javascript:AbreJanela('$Url',700,340);\"><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
                                                            echo "	</td>\n";
                                                            echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"11%\">\n";
                                                            echo "		$CodRedMaterialServicoBanco";
                                                            echo "  </td>\n";
                                                            
                                                            if ($BuscaDupla == true) { // Apenas para Material
                                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                                echo "		-";
                                                                echo "	</td>\n";
                                                            }
                                                        }
                                                        
                                                        if ($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir' || $ProgramaOrigem == 'CadContratoAntigoIncluir' || $ProgramaOrigem == "CadContratoManter") {
                                                            $Repeticoes = 1;
                                                            echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"11%\">\n";
                                                            echo "		<input type=\"text\" name=\"Repeticoes$CodRedMaterialServicoBanco\" value=\"$Repeticoes\" size=\"10\" maxlength=\"10\" class=\"textonormal\" onFocus=\"javascript:document.CadIncluirItemPlanejamento.Repeticoes$CodRedMaterialServicoBanco.value\">";
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
                                    ?>
								    <tr>
                                        <td colspan="5" align="right">
                                            <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
                                            <input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
                                            <input type="hidden" name="Botao" value="">
                                            <input type="hidden" name="PesqApenas" value="<?php echo $PesqApenas; ?>">
                                            <input type="hidden" name="classePredefinida" value="<?php echo $classePredefinida; ?>">
                                            <input type="hidden" name="Zerados" value="<?php echo $Zerados; ?>">
                                            <input type="hidden" name="TipoGrupo" value="<?php echo !empty($TipoGrupo)? $TipoGrupo : "C"; ?>">
                                            <input type="hidden" name="RepeticoesPost" value="0">
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