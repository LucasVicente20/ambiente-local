<?php
/**
 * Portal de Compras
 * 
 * Programa: ConsRegistroPrecoResultado.php
 * ---------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/10/2018
 * Objetivo: Tarefa Redmine 199575
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     10/12/2018
 * Objetivo: Tarefa Redmine 208026
 * ---------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Ernesto Ferreira
 * Data:     11/04/2019
 * Objetivo: Tarefa Redmine 214714
 * ---------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     26/07/2019
 * Objetivo: Tarefa Redmine 221144
 * ---------------------------------------------------------------------------
 * Alterado: Marcello Albuquerque
 * Data:     26/05/2021
 * Objetivo: Tarefa Redmine 248866
 * |Madson| Reenvio do arquivo para produção 23/11/2021
 * ---------------------------------------------------------------------------
 *  Alterado: Lucas Baracho
 * Data:     26/07/2019
 * Objetivo: Tarefa Redmine 221144
 * ---------------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:     20/06/2022
 * Objetivo: CR 244837
 * ---------------------------------------------------------------------------
 */

require_once("../funcoes.php");
require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/ConsRegistroPrecoResultado.html");

$arrayResultado = array();
$arrayBusca     = array();

$objeto            = strtoupper2($_POST["ItemObjeto"]);
$objetoComAcento = $objeto;
$objeto            = RetiraAcentos($objeto);


// $objeto = preg_replace('/[áàãâä]/ui', 'A', $objeto);
// $objeto = preg_replace('/[áàãâäÁÀÃÂÄ]/ui', 'A', $objeto);
// $objeto = preg_replace('/[éèêëÉÈÊË]/ui', 'E', $objeto);
// $objeto = preg_replace('/[íìîïÍÌÎÏ]/ui', 'I', $objeto);
// $objeto = preg_replace('/[óòõôöÓÒÕÔÖ]/ui', 'O', $objeto);
// $objeto = preg_replace('/[úùûüÚÙÛÜ]/ui', 'U', $objeto);
// $objeto = preg_replace('/[Çç]/ui', 'C', $objeto);
if(!ctype_print($objeto)){
    $objeto = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $objeto);
}

$comissao          = $_POST["ItemComissao"];
$orgao             = $_POST["ItemOrgao"];
$numeroAta         = $_POST["numeroAta"];
$processo          = $_POST["processo"];
$orgapParticipante = $_POST["orgapParticipante"];
$tipoItemLicitacao = $_POST["tipoItemLicitacao"];
$itemInput         = $_POST["itemInput"];
$tipoGrupo         = $_POST["tipoGrupo"];
$valorGrupoInput   = $_POST["valorGrupoInput"];
$pesqFornecedor    = $_POST["pesqFornecedor"];
$cnpj              = $_POST["cnpj"];
$cpf               = $_POST["cpf"];
$pesqVigentes      = $_POST["pesqVigentes"];
$grupoMaterial     = $_POST["grupoMaterial"];
$grupoServico      = $_POST["grupoServico"];
$botao             = $_POST["botao"];
$rsocial           = strtoupper($_POST["rsocial"]);
$tprsocial         = $_POST["tprsocial"];


if ($botao != 'Voltar') {
    $arrayBusca['objeto']            = $objeto;
    $arrayBusca['comissao']          = $comissao;
    $arrayBusca['orgao']             = $orgao;
    $arrayBusca['numeroAta']         = $numeroAta;
    $arrayBusca['processo']          = $processo;
    $arrayBusca['orgapParticipante'] = $orgapParticipante;
    $arrayBusca['tipoItemLicitacao'] = $tipoItemLicitacao;
    $arrayBusca['itemInput']         = $itemInput;
    $arrayBusca['tipoGrupo']         = $tipoGrupo;
    $arrayBusca['valorGrupoInput']   = $valorGrupoInput;  
    $arrayBusca['pesqFornecedor']    = $pesqFornecedor;    
    $arrayBusca['cnpj']              = $cnpj;  
    $arrayBusca['cpf']               = $cpf;          
    $arrayBusca['pesqVigentes']      = $pesqVigentes;
    $arrayBusca['grupoMaterial']     = $grupoMaterial;  
    $arrayBusca['grupoServico']      = $grupoServico;
    //$arrayBusca['razaoSocial']       = $rsocial;



    $_SESSION['busca_ata_registrodepreco'] = $arrayBusca;
} else {
    $arrayBusca = $_SESSION['busca_ata_registrodepreco'];

    $objeto            = $arrayBusca["ItemObjeto"];
    $comissao          = $arrayBusca["ItemComissao"];
    $orgao             = $arrayBusca["ItemOrgao"];
    $numeroAta         = $arrayBusca["numeroAta"];
    $processo          = $arrayBusca["processo"];
    $orgapParticipante = $arrayBusca["orgapParticipante"];
    $tipoItemLicitacao = $arrayBusca["tipoItemLicitacao"];
    $itemInput         = $arrayBusca["itemInput"];
    $tipoGrupo         = $arrayBusca["tipoGrupo"];
    $valorGrupoInput   = $arrayBusca["valorGrupoInput"];
    $pesqFornecedor    = $arrayBusca["pesqFornecedor"];
    $cnpj              = $arrayBusca["cnpj"];
    $cpf               = $arrayBusca["cpf"];
    $pesqVigentes      = $arrayBusca["pesqVigentes"];
    $grupoMaterial     = $arrayBusca["grupoMaterial"];
    $grupoServico      = $arrayBusca["grupoServico"];
    //$rsocial           = $arrayBusca["razaoSocial"];
}
$tpl->ITEM_OBJETO   = $objeto;
$tpl->ITEM_COMISSAO = $comissao;
$tpl->ITEM_ORGAO    = $orgao;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                = $_POST['Botao'];
	$Critica              = $_POST['Critica'];
	$OrgaoLicitanteCodigo = $orgao;
	$ComissaoCodigo       = $comissao;
	$ModalidadeCodigo     = '';
	$Objeto               = strtoupper2($objeto);
	$LicitacaoAno         = '';
} else {
	$Objeto               = strtoupper2($objeto);
	$OrgaoLicitanteCodigo = $orgao;
	$ComissaoCodigo       = $comissao;
	$ModalidadeCodigo     = '';
	$LicitacaoAno         = '';
}

$db   = Conexao();
$Data = date("Y-m-d");

function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta) {
    $sql  = "SELECT A.CARPINCODN, A.EARPINOBJE, A.AARPINANON, A.AARPINPZVG, A.TARPINDINI, A.CGREMPCODI, A.CUSUPOCODI, ";
    $sql .= "       F.NFORCRRAZS, D.EDOCLINOME, A.CORGLICODI, A.CARPNOSEQU, A.ALICPOANOP, S.CSOLCOSEQU, A.AARPINANON, ";
    $sql .= "       A.CARPNOSEQ1, F.NFORCRRAZS, F.AFORCRCCGC, F.AFORCRCCPF, F.EFORCRLOGR, F.AFORCRNUME, F.EFORCRBAIR, ";
    $sql .= "       F.NFORCRCIDA, F.CFORCRESTA, FA.NFORCRRAZS AS razaoFornecedorAtual, FA.AFORCRCCGC AS cgcFornecedorAtual, ";
    $sql .= "       FA.AFORCRCCPF AS cpfFornecedorAtual, FA.EFORCRLOGR AS logradouroFornecedorAtual, ";
    $sql .= "       FA.AFORCRNUME AS numeroEnderecoFornecedorAtual, FA.EFORCRBAIR AS bairroFornecedorAtual, FA.NFORCRCIDA AS cidadeFornecedorAtual, ";
    $sql .= "       FA.CFORCRESTA AS estadoFornecedorAtual ";
    $sql .= "FROM   SFPC.TBATAREGISTROPRECOINTERNA A ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBSOLICITACAOLICITACAOPORTAL S ON (S.CLICPOPROC = A.CLICPOPROC ";
    $sql .= "                                                               AND S.ALICPOANOP = A.ALICPOANOP ";
    $sql .= "                                                               AND S.CCOMLICODI = A.CCOMLICODI ";
    $sql .= "                                                               AND S.CORGLICODI = A.CORGLICODI) ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO F ON F.AFORCRSEQU = A.AFORCRSEQU ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBFORNECEDORCREDENCIADO FA ON FA.AFORCRSEQU = (SELECT AFA.AFORCRSEQU FROM SFPC.TBATAREGISTROPRECOINTERNA AFA WHERE AFA.CARPNOSEQU = A.CARPNOSEQ1) ";
    $sql .= "       LEFT OUTER JOIN SFPC.TBDOCUMENTOLICITACAO D ON D.CLICPOPROC = A.CLICPOPROC ";
    $sql .= "                                                      AND D.CLICPOPROC = " . $processo;
    $sql .= "                                                      AND D.CORGLICODI = " . $orgao;
    $sql .= "                                                      AND D.ALICPOANOP = " . $ano;
    $sql .= " WHERE A.CARPNOSEQU = " . $chaveAta;

    return $sql;
}


$sql  = "SELECT DISTINCT (ARPI.TARPINDINI + (ARPI.AARPINPZVG || ' MONTH')::INTERVAL) AS VIGENCIA, ";
$sql .= "       GE.EGREMPDESC, CL.ECOMLIDESC, ARPI.CLICPOPROC, ARPI.ALICPOANOP, ARPN.CARPNOSEQU, ";
$sql .= "       ARPN.CARPNOSEQU, ARPI.EARPINOBJE, ARPI.TARPINDINI, OL.EORGLIDESC, ARPI.CGREMPCODI, ";
$sql .= "       ARPI.CCOMLICODI, ARPI.CORGLICODI, ARPI.CUSUPOCODI, ARPI.AARPINANON, ARPI.CARPINCODN, ";
$sql .= "       ARPN.CARPNOTIAT, FC.NFORCRRAZS, FC.AFORCRCCGC, FC.AFORCRCCPF, GE.EGREMPDESC ";
$sql .= "FROM   SFPC.TBATAREGISTROPRECONOVA ARPN INNER JOIN SFPC.TBATAREGISTROPRECOINTERNA ARPI ON ARPI.CARPNOSEQU = ARPN.CARPNOSEQU ";
$sql .= "       LEFT JOIN SFPC.TBPARTICIPANTEATARP PARP ON PARP.CARPNOSEQU = ARPN.CARPNOSEQU ";
$sql .= "       LEFT JOIN SFPC.TBFORNECEDORCREDENCIADO FC ON FC.AFORCRSEQU = ARPI.AFORCRSEQU ";
$sql .= "       LEFT JOIN SFPC.TBITEMATAREGISTROPRECONOVA IARPN ON IARPN.CARPNOSEQU = ARPN.CARPNOSEQU ";

    if ($tipoGrupo == 'M' || ($tipoItemLicitacao == 'M' && !empty($itemInput))) {
        $sql .= " JOIN SFPC.TBMATERIALPORTAL MP ON MP.CMATEPSEQU = IARPN.CMATEPSEQU ";
        $sql .= " JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON SCM.CSUBCLSEQU = MP.CSUBCLSEQU";
    
        if (!empty($grupoMaterial)) {
            $sql .= " AND SCM.CGRUMSCODI = " . $grupoMaterial;
        }
    }

    if ($tipoGrupo == 'S' || ($tipoItemLicitacao == 'S' && !empty($itemInput))) {
        $sql .= " JOIN SFPC.TBSERVICOPORTAL SP ON SP.CSERVPSEQU = IARPN.CSERVPSEQU ";
    
        if (!empty($grupoServico)) {
            $sql .= " AND SP.CGRUMSCODI = " . $grupoServico;
        }
    }

    if (!empty($numeroAta)) {
        $sql .= "   JOIN SFPC.TBCENTROCUSTOPORTAL centroCusto ON centroCusto.CORGLICODI = ARPI.CORGLICODI ";
    }

// Comissão
$sql .= "       JOIN SFPC.TBCOMISSAOLICITACAO CL ON CL.CCOMLICODI = ARPI.CCOMLICODI ";

// Órgão licitante
$sql .= "       JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = ARPI.CORGLICODI ";

// Grupo
$sql .= "       LEFT JOIN SFPC.TBGRUPOEMPRESA GE ON ARPI.CGREMPCODI = GE.CGREMPCODI ";

$sql .= "WHERE  1 = 1 ";
$sql .= "       AND ARPN.CARPNOTIAT = 'I' ";
$sql .= "       AND ARPI.FARPINSITU = 'A' ";

    if (!empty($numeroAta)) {
        $ccenpocorg = ltrim(substr($numeroAta, 0,2), "0");
        $ccenpounid = ltrim(substr($numeroAta, 2,2), "0");
        $carpincodn = ltrim(substr($numeroAta, 5,4), "0");
        $aarpinanon = substr($numeroAta, 10,4);

        $sql .= " AND ARPI.CORGLICODI = (SELECT centroCusto.corglicodi ";
        $sql .= "                        FROM   SFPC.TBCENTROCUSTOPORTAL centroCusto ";
        $sql .= "                        WHERE  centroCusto.CCENPOCORG =  " . $ccenpocorg;
        $sql .= "                        AND centroCusto.CCENPOUNID =  " . $ccenpounid;
        $sql .= "                        LIMIT 1) ";
        $sql .= " AND ARPI.CARPINCODN = " . $carpincodn;
        $sql .= " AND ARPI.AARPINANON = " . $aarpinanon;
    }

    if(!empty($rsocial)){
        if($tprsocial == "iniciando"){
            $sql .= " AND FC.NFORCRRAZS LIKE '".$rsocial."%'";
        }else if($tprsocial == "contendo"){
            $sql .= " AND FC.NFORCRRAZS LIKE '%".$rsocial."%'";
        }
    }
        
            
    if (!empty($comissao)) {
        $sql .= " AND ARPI.CCOMLICODI = " . $comissao;
    }
    
        
    if (!empty($processo)) {
        $processoArray = explode('/', $processo);

        $sql .= " AND ARPI.CLICPOPROC = " . $processoArray[0];
        $sql .= " AND ARPI.ALICPOANOP = " . $processoArray[1];
    }

    if (!empty($orgapParticipante)) {
        $sql .= " AND PARP.CORGLICODI = " . $orgapParticipante;
    }

    if (!empty($orgao)) {
        $sql .= " AND ARPI.CORGLICODI = " . $orgao;
    }

    if (!empty($objeto)) {
        $sql .= " AND (ARPI.EARPINOBJE ILIKE '%".$objeto."%' OR ARPI.EARPINOBJE ILIKE '%".$objetoComAcento."%')";
    }
    

    // Vigentes
    if (isset($pesqVigentes) && $pesqVigentes == '1') {
        $sql .= " AND NOW() BETWEEN ARPI.TARPINDINI AND ARPI.TARPINDINI + (ARPI.AARPINPZVG::TEXT || 'MONTH'):: INTERVAL ";
    }

    // Fornecedor
    if (!empty($cnpj) && $pesqFornecedor == 0) {
        $cnpj = str_replace('-','',str_replace('/','',str_replace('.','', $cnpj)));

        $sql .= " AND FC.AFORCRCCGC = '" . $cnpj."' ";
    } elseif (!empty($cpf) && $pesqFornecedor == 1) {
        $cpf = str_replace('-','',str_replace('.','', $cpf));

        $sql .= " AND FC.AFORCRCCPF = '" . $cpf."' ";
    }

    //Item
    if (!empty($itemInput) && !empty($tipoItemLicitacao)) {
        $encoding = 'UTF-8';

        if ($tipoItemLicitacao == 'M') {
            $sql .= " AND MP.EMATEPDESC LIKE '%" . mb_strtoupper($itemInput, $encoding)."%'";
        } elseif ($tipoItemLicitacao == 'S') {
            $sql .= " AND SP.ESERVPDESC LIKE '%" . mb_strtoupper($itemInput, $encoding)."%'";
        }
    }

$sql .= " ORDER BY GE.EGREMPDESC, CL.ECOMLIDESC, ARPI.ALICPOANOP DESC, ARPI.CLICPOPROC ASC, ARPI.AARPINANON DESC, ARPI.CARPINCODN ASC ";

$divEscondida = '<div style="display:none;">';
$divEscondida .= $sql;
$divEscondida .= '</div>';

$result = $db->query($sql);


while ($cols = $result->fetchRow()) {  
	
    $divEscondida .= '<div style="display:none;">';
    $divEscondida .= implode($cols);
    $divEscondida .= '</div>';

    array_push($arrayResultado, $cols);
}



if (count($arrayResultado) === 0) {
	$tpl->exibirMensagemFeedback("Nenhuma ocorrência foi encontrada", "1");
}

$ultimaModalidadePlotada = "";
$ultimaComissaoPlotada   = "";
$ultimoGrupoPlotado      = "";

for ($i = 0; $i < count($arrayResultado) ; $i++) {
	if ($arrayResultado[$i][1] != '' && $ultimoGrupoPlotado != $arrayResultado[$i][1]) {
		$tpl->GRUPO_DESCRICAO = $arrayResultado[$i][1];
		$ultimoGrupoPlotado = $arrayResultado[$i][1];

        $tpl->block("BLOCO_GRUPO");
		$ultimaModalidadePlotada = "";
	}

	if ($ultimaComissaoPlotada != $arrayResultado[$i][2]) {
		$tpl->COMISSAO_DESCRICAO = $arrayResultado[$i][2];
		$tpl->block("BLOCO_COMISSAO");
	}

	if ($ultimaComissaoPlotada != $arrayResultado[$i][2]) {
	    $tpl->block("BLOCO_CABECALHO");
		$ultimaComissaoPlotada = $arrayResultado[$i][2];
    }

    // Número da ata
    $dto = array();

    $sqlConsultaAta = sqlAtaPorchave($arrayResultado[$i][14], $arrayResultado[$i][3], $arrayResultado[$i][12], $arrayResultado[$i][5]);

    $divEscondida .= '<div style="display:none;">';
    $divEscondida .= $sqlConsultaAta;
    $divEscondida .= '</div>';

    $resultado = executarSQL($db, $sqlConsultaAta);
    $resultado->fetchInto($consultaAta, DB_FETCHMODE_OBJECT);

    $sql  = "SELECT CCP.CCENPOCORG, CCP.CCENPOUNID, CCP.CORGLICODI ";
    $sql .= "FROM   SFPC.TBCENTROCUSTOPORTAL CCP ";
    $sql .= "WHERE  1 = 1 ";

        if ($consultaAta->corglicodi != null || $consultaAta->corglicodi != "") {
            $sql .= " AND CCP.CORGLICODI = " . $consultaAta->corglicodi;
        }
    
    $divEscondida .= '<div style="display:none;">';
    $divEscondida .= $sql;
    $divEscondida .= '</div>';

    $res = executarSQL($db, $sql);

    $itens = array();
    $item = null;

    while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
        $itens[] = $item;
    }

    $object = current($itens);

    $numeroAta  = $object->ccenpocorg . str_pad($object->ccenpounid, 2, '0', STR_PAD_LEFT);
    $numeroAta .= "." . str_pad($arrayResultado[$i][15], 4, "0", STR_PAD_LEFT) . "/" . $arrayResultado[$i][14];

    // Vigência
    $mes = substr($arrayResultado[$i][0], 5, 2);
    $dia = substr($arrayResultado[$i][0], 8, 2);
    $ano = substr($arrayResultado[$i][0], 0, 4);

    $tpl->CODIGO_ATA         = $arrayResultado[$i][5];
    $tpl->CODIGO_GRUPO       = $arrayResultado[$i][10];
    $tpl->CODIGO_PROCESSO    = $arrayResultado[$i][3];
    $tpl->ANO_PROCESSO       = $arrayResultado[$i][4];
    $tpl->CODIGO_COMISSAO    = $arrayResultado[$i][11];
    $tpl->CODIGO_ORGAO       = $arrayResultado[$i][12];
    $tpl->PROCESSO           = str_pad($arrayResultado[$i][3], 3, "0", STR_PAD_LEFT).'/'.$arrayResultado[$i][4];
    $tpl->NUMERO_ATA         = $numeroAta;
    $tpl->OBJETO             = $arrayResultado[$i][7];
    $tpl->VIGENCIA           = $dia . '/' . $mes . '/' . $ano;
    $tpl->ORGAO_LICITANTE    = $arrayResultado[$i][9];

    if ($i + 1 < count($arrayResultado)) {
	    if ($ultimaComissaoPlotada != $arrayResultado[$i+1][2]) {
		    $tpl->block("BLOCO_SEPARATOR");
	    }
    }

    $tpl->block("BLOCO_VALORES");
    $tpl->block("BLOCO_CORPO");
}

echo $divEscondida;

$tpl->show();