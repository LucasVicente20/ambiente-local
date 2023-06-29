<?php
/**
 * Portal de Compras
 * 
 * Programa: funcoesTramitacao.php
 * Autor:    Pitang Agile TI - Caio Coutinho
 * Data:     10/08/2018
 * Objetivo: Tarefa Redmine 200550
 * ------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     14/03/2019
 * Objetivo: Tarefa Redmine 212678
 * ------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     27/03/2019
 * Objetivo: Tarefa Redmine 213437
 * ------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     29/05/2019
 * Objetivo: Tarefa Redmine 217242
 * ------------------------------------------------------------------------------------
 */

function getGrupos($db, $grupo = null) {
    $grupos = array();
    $sql    = " SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA  ";
    if($_SESSION['_fperficorp_'] == 'S') {
        $sql    .= " WHERE CGREMPCODI <> 0";
    } else {
        $sql    .= " WHERE CGREMPCODI = " . $_SESSION['_cgrempcodi_'];
    }
    $sql    .= " ORDER BY EGREMPDESC ASC";
    $result = $db->query($sql);

    if (PEAR::isError($result)){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }else{
        while( $Linha = $result->fetchRow() ){
            $grupos[$Linha[0]] = $Linha[1];
        }
    }

    return $grupos;
}

function getUsuarios($db, $grupo = null) {
    $users  = array();
    $sql    = "  SELECT CUSUPOCODI, EUSUPORESP FROM SFPC.TBUSUARIOPORTAL  ";

    if(!is_null($grupo)) {
        $sql    .= " WHERE CGREMPCODI = " . $grupo;
    } else {
        $sql    .= " WHERE CGREMPCODI <> 0 ";
    }
    $sql    .= " ORDER BY EUSUPORESP ASC";

    $result = $db->query($sql);

    if (PEAR::isError($result)){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }else{
        while( $Linha = $result->fetchRow() ){
            $users[$Linha[0]] = $Linha[1];
        }
    }

    return $users;
}

function getByGrupos($db, $grupo = null) {
    $grupos = array();
    $sql    =  " SELECT G.CGREMPCODI, G. EGREMPDESC, TA.CTAGENSEQU, TA.ETAGENDESC  FROM SFPC.TBGRUPOEMPRESA G";
    $sql    .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTE TA ON ";
    $sql    .= "  TA.CGREMPCODI = G.CGREMPCODI ";

    if(is_null($grupo)) {
        $sql    .= " WHERE G.CGREMPCODI <> 0 ";
    } else {
        $sql    .= " WHERE G.CGREMPCODI = " . $grupo;
        $sql    .= " AND TA.CGREMPCODI = " . $grupo;

    }

    $sql    .= " ORDER BY G.EGREMPDESC ASC, TA.ETAGENDESC ASC";
    $result = $db->query($sql);

    if (PEAR::isError($result)){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    }else{
        while( $Linha = $result->fetchRow() ){
            $array = array();
            $array['grupo'] = $Linha[0];

            if(!empty($Linha[2])) {
                $array['descricao'] = $Linha[3];
                $array['agente'] = $Linha[2];
            }

            $grupos[$Linha[1]][] = $array;
        }
    }

    return $grupos;
}

function getAgenteById($db, $agente) {
    $array = array();
    $ids   = array();

    if (!empty($agente)) {
        $sql  = "SELECT TA.CTAGENSEQU, TA.CGREMPCODI, TA.ETAGENDESC, TA.FTAGENTIPO, TAU.CUSUPOCODI, ";
        $sql .= "       TA.FTAGENINIC, TA.FTAGENSITU, TA.FTAGENCOMIS, TA.FTAGENALTE ";
        $sql .= "FROM   SFPC.TBTRAMITACAOAGENTE TA ";
        $sql .= "       LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON TAU.CTAGENSEQU = TA.CTAGENSEQU ";
        $sql .= "WHERE  TA.FTAGENSITU = 'A' ";
        $sql .= "       AND TA.CTAGENSEQU = " . $agente;

        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            while ($Linha = $result->fetchRow()) {
                $array = $Linha;

                unset($array[4]);

                $ids[] = $Linha[4];
            }
        }
    }

    $array['usuarios'] = $ids;

    return $array;
}

function getAgentes($db, $grupo = null) {
    $array = array();

    $sql = " SELECT TA.CTAGENSEQU, TA.ETAGENDESC, TA.FTAGENTIPO ";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA ";
    $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
    $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
    $sql .= " WHERE TA.FTAGENSITU = 'A'  ";

    if(!is_null($grupo)) {
        $sql .= "   AND TA.CGREMPCODI = " . $grupo;
    }

    $sql .= " GROUP BY TA.CTAGENSEQU, TA.FTAGENTIPO, TA.ETAGENDESC
              ORDER BY TA.ETAGENDESC ASC ";
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function getResponsaveisAgente($db, $agente) {
    $array = array();

    $sql = " SELECT DISTINCT(TA.CTAGENSEQU), TAU.CUSUPOCODI, UP.EUSUPORESP ";
    $sql .= " FROM SFPC.TBTRAMITACAOAGENTE TA ";
    $sql .= " LEFT JOIN SFPC.TBTRAMITACAOAGENTEUSUARIO TAU ON ";
    $sql .= "    TAU.CTAGENSEQU = TA.CTAGENSEQU ";
    $sql .= " LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON ";
    $sql .= "    UP.CUSUPOCODI = TAU.CUSUPOCODI ";
    $sql .= " WHERE TA.FTAGENSITU = 'A'  ";
    $sql .= "   AND TA.CTAGENSEQU = " . $agente;

    $sql .= " ORDER BY UP.EUSUPORESP ASC ";
    //print_r($sql);exit;
    $result = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

function protocoloPesquisarAgentes($db, $params) {
    $arraySituacoesConcluidas = getIdFasesConcluidas($db);
    $arraySituacoesEmAndamento = getIdFasesEmAndamento($db);
    $array = array();

    $sql = " SELECT TP.cprotcsequ, TA.etagendesc, UP.eusuporesp, TP.cprotcnump, TP.aprotcanop FROM sfpc.tbtramitacaoprotocolo TP ";
    $sql .= " LEFT JOIN sfpc.tbtramitacaolicitacao TL ON ";
    $sql .= "   TL.cprotcsequ = TP.cprotcsequ ";
    $sql .= " LEFT JOIN sfpc.tbtramitacaoagente TA ON ";
    $sql .= "   TA.ctagensequ = TL.ctagensequ ";
    $sql .= " RIGHT JOIN sfpc.tbtramitacaoagenteusuario TAU ON ";
    $sql .= "   TAU.ctagensequ = TA.ctagensequ";
    $sql .= " RIGHT JOIN sfpc.tbusuarioportal UP ON ";
    $sql .= "   UP.cusupocodi = TAU.cusupocodi ";
    $sql .= '  LEFT JOIN SFPC.TBFASELICITACAO FL ON '; 
    $sql .= '       FL.CLICPOPROC = TP.CLICPOPROC ';
    $sql .= '       AND FL.ALICPOANOP = TP.ALICPOANOP ';
    $sql .= '       AND FL.CGREMPCODI = TP.CGREMPCODI ';
    $sql .= '       AND FL.CCOMLICODI = TP.CCOMLICODI ';
    $sql .= '       AND FL.CORGLICODI = TP.CORGLICODI ';
    $sql .= " WHERE 1 = 1 ";
    
    // Agente
    if(!empty($params['agente'])) {
        $sql .= "   AND TA.CTAGENSEQU = " . $params['agente']; 
        $sql .= "   AND TL.CTAGENSEQU = " . $params['agente'];        
    }

    // Responsável
    if(!empty($params['responsavel'])) {
        $sql .= "   AND TAU.cusupocodi = " . $params['responsavel'];     
    }

    // Datas
    if(!empty($params['dataInicio']) && !empty($params['dataFim'])) {
        $sql .= " AND TP.dprotcentr BETWEEN '" . DataInvertida($params['dataInicio']) . "' AND '" . DataInvertida($params['dataFim']) . "'";
    } else if(!empty($params['dataInicio'])) {
        $sql .= " AND TP.dprotcentr = '" . DataInvertida($params['dataInicio']) . "'";
    } else if(!empty($params['dataFim'])) {
        $sql .= " AND TP.dprotcentr = '" . DataInvertida($params['dataFim']) . "'";
    }

    // Situação
    if (!empty($params['situacao'])) {
        if ($params['situacao'] == 'concluidas') {
            $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
            $sql   .= " AND FL.CFASESCODI IN ($strIdConcluidas) ";
        } elseif ($params['situacao'] == 'andamento') {
            $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
            $sql   .= " AND FL.CFASESCODI IN ($strIdAndamento) ";
        }
    }

    $sql .= " ORDER BY UP.EUSUPORESP ASC ";
    
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;    
}

function getTramitacaoPassos($protocolo) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT TAG.ETAGENDESC, UP.EUSUPORESP, TA.ETACAODESC, TL.TTRAMLENTR, TA.ATACAOPRAZ, TL.TTRAMLSAID,
            TL.XTRAMLOBSE, TA.ATACAOORDE
        FROM SFPC.TBTRAMITACAOLICITACAO TL
        LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON 
            UP.CUSUPOCODI = TL.CUSUPOCODI 
        LEFT JOIN SFPC.TBTRAMITACAOPROTOCOLO TP ON 
            TP.CPROTCSEQU = TL.CPROTCSEQU
        LEFT JOIN SFPC.TBTRAMITACAOACAO TA ON 
            TA.CTACAOSEQU = TL.CTACAOSEQU
        LEFT JOIN SFPC.TBTRAMITACAOAGENTE TAG ON 
            TAG.CTAGENSEQU = TL.CTAGENSEQU ';
    $sql .= "   WHERE TL.CPROTCSEQU = " . $protocolo;
    $sql .= "   ORDER BY TL.TTRAMLULAT DESC";  
    //print_r($sql);exit;
    $res  = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

/**
 * Retornar os dias não trabalhados
 *
 * @param $entrada date pt-br
 * @param $saida date pt-br
 *
 * @return array
 */
function getDiasNaoTrabalhados($entrada, $saida) {
    $db = $GLOBALS["db"];
    $sql = '  SELECT ATDIANDIAT, ATDIANMEST, ATDIANANOT FROM SFPC.TBTRAMITACAODIASNAOTRABALHADOS WHERE 1 = 1 ';
    $res  = $db->query($sql);

    if(!isError($res)) {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = str_pad($Linha[0], 2, "0", STR_PAD_LEFT).'/'.str_pad($Linha[1], 2, "0", STR_PAD_LEFT).'/'.$Linha[2];
        }

        return $array;
    }
}

/**
 * Calcular a data de estimada da tramitação
 *
 * @param $entrada
 * @param $prazo
 *
 * @return date
 */
function calcularTramitacaoSaida($entrada, $prazo) {
    $estimado = SomaDia($entrada, $prazo);
    $diasUteis = diasUteis($entrada, $estimado);
    $adicional = $prazo - $diasUteis;
    $diasNaoTrabalhados = getDiasNaoTrabalhados($entrada, $estimado);

    for ($i=0; $i < $prazo; $i++) {
        if(in_array(SomaDia($entrada, $i), $diasNaoTrabalhados)) {
            $adicional++;
        }
    }

    return SomaDia($entrada, ($prazo + $adicional));
}

/**
 * Somar dias
 *
 * @param $data
 * @param int $dias
 *
 * @return false|string
 */
function SomaDia($data, $dias = 1){
    $ano = substr($data, 6,4);
    $mes = substr($data, 3,2);
    $dia = substr($data, 0,2);
    return   date("d/m/Y", mktime(0, 0, 0, $mes, $dia+$dias, $ano));
}

/**
 * Converter data para Timestamp
 *
 * @param $data
 * @return false|int
 */
function dataToTimestamp($data){
    $ano = substr($data, 6,4);
    $mes = substr($data, 3,2);
    $dia = substr($data, 0,2);
    return mktime(0, 0, 0, $mes, $dia, $ano);
}

/**
 * Calcular dias entre intervalo de datas
 *
 * @param $xDataInicial
 * @param $xDataFinal
 *
 * @return float|int
 */
function calculaDias($xDataInicial, $xDataFinal){
    $time1 = dataToTimestamp($xDataInicial);
    $time2 = dataToTimestamp($xDataFinal);
    
    $tMaior = $time1>$time2 ? $time1 : $time2;
    $tMenor = $time1<$time2 ? $time1 : $time2;

    $diff = $tMaior-$tMenor;
    $numDias = $diff/86400; //86400 é o número de segundos que 1 dia possui
    return $numDias;
}

/**
 * Calcular dias úteis entre datas
 *
 * @param $yDataInicial
 * @param $yDataFinal
 *
 * @return int
 */
function diasUteis($yDataInicial,$yDataFinal){

    $diaFDS = 0; //dias não úteis(Sábado=6 Domingo=0)
    $calculoDias = CalculaDias($yDataInicial, $yDataFinal); //número de dias entre a data inicial e a final
    $diasUteis = 0;

    while($yDataInicial!=$yDataFinal){
        $diaSemana = date("w", dataToTimestamp($yDataInicial));
        if($diaSemana==0 || $diaSemana==6){
            //se SABADO OU DOMINGO, SOMA 01
            $diaFDS++;
        }
        $yDataInicial = SomaDia($yDataInicial); //dia + 1
    }

    return $calculoDias - $diaFDS;
}

/**
 * Verificar se o Agente está sendo referenciado em alguma tramitacao
 *
 * @param $res
 *
 * @return bool
 */

function verificaTramitacaoAgente($codAgente){
    $db = $GLOBALS["db"];
    $sql = '  select * from sfpc.tbtramitacaolicitacao where ctagensequ ='. $codAgente;
    //print_r($sql);exit;
    $res  = $db->query($sql);

    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        $array = array();
        while ($Linha = $res->fetchRow()) {
            $array[] = $Linha;
        }
    }

    return $array;
}

/**
 * Verificar erro nas consultas
 *
 * @param $res
 *
 * @return bool
 */
function isError($res) {
    if (PEAR::isError($res)) {
        $CodErroEmail  = $res->getCode();
        $DescErroEmail = $res->getMessage();
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
    }

    return false;
}

function getGruposAcao($db, $grupo = null) {
    $grupos = array();
    
    $sql = "SELECT G.CGREMPCODI, G.EGREMPDESC, TA.CTACAOSEQU, TA.ETACAODESC FROM SFPC.TBGRUPOEMPRESA G LEFT JOIN SFPC.TBTRAMITACAOACAO TA ON TA.CGREMPCODI = G.CGREMPCODI ";

    if (is_null($grupo)) {
        $sql    .= " WHERE G.CGREMPCODI <> 0 ";
    } else {
        $sql    .= " WHERE G.CGREMPCODI = " . $grupo;
        $sql    .= " AND TA.CGREMPCODI = " . $grupo;

    }
    $sql    .= " ORDER BY G.EGREMPDESC ASC, TA.ETACAODESC ASC";
    
    $result = $db->query($sql);

    if (PEAR::isError($result)){
        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $array = array();
            $array['grupo'] = $Linha[0];

            if (!empty($Linha[2])) {
                $array['acao'] = $Linha[2];
                $array['descricao'] = $Linha[3];
            }
            $grupos[$Linha[1]][] = $array;
        }
    }
    return $grupos;
}
