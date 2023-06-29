<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------
# Alterado: Eliakim Ramos
# data: 27/11/2019
# Objetivo: Tarefa Redmine 225675
#---------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}
//kim
if($_SESSION['_fperficorp_'] != "S"){

    Seguranca();
}

/**
 */
class CadRegistroPrecoIntencaoVisualizarConsolidacao extends ClaRegistroPrecoIntencao
{

    /**
     */
    const MENSAGEM_INTENCAO_RESPOSTA_ATIVA = 'Intenção não pode ser excluída, pois já possui resposta ativa';

    const MENSAGEM_INTENCAO_EXCLUIDA_SUCESSO = 'Intenção excluída com sucesso';

    const MENSAGEM_INTENCAO_EXCLUIDA_ERRO = 'Falha ao excluir intenção';

    const INTENCAO_SEM_DADOS_CONSOLIDADOS = 'Não existem dados consolidados para a intenção selecionada';

    private $intencao;

    /**
     * [sqlSelectIntencaoLocal description].
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     *
     * @return [type] [description]
     */
    protected function sqlSelectIntencaoLocal($sequencialIntencao, $anoIntencao)
    {
        $sql = ' SELECT DISTINCT a.cintrpsequ,  a.cintrpsano, a.tintrpdlim, a.xintrpobje, ';
        $sql .= '        a.xintrpobse, a.fintrpsitu, a.tintrpdcad, a.cusupocodi, a.tintrpulat ';
        $sql .= ' FROM ';
        $sql .= '        sfpc.tbintencaoregistropreco a ';
        $sql .= ' WHERE ';
        $sql .= "		a.cintrpsequ = $sequencialIntencao AND a.cintrpsano = $anoIntencao ";

        return $sql;
    }

    public function documentosIntencao($cintrpsequ, $cintrpsano) {
        $sql = " SELECT cintrpsequ, encode(iintraarqu, 'base64') as iintraarqu, cintrpsequ, cintrpsano, eintranome 
                 FROM SFPC.TBINTENCAOREGISTROPRECOANEXO WHERE cintrpsequ = $cintrpsequ AND cintrpsano = $cintrpsano";
        $database = ClaDatabasePostgresql::getConexao();
        $res = &$database->getAll($sql, array(), DB_FETCHMODE_OBJECT);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     * [proccessPrincipal description].
     *
     * @param [type] $variablesGlobals
     *            [description]
     *
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $this->configInitial();

        if (isset($_POST['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $_POST['NumeroIntencao'];
        }

        if (isset($_POST['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $_POST['DataInicioCadastro'];
        }

        if (isset($_POST['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $_POST['DataFimCadastro'];
        }

        if (! $this->hasRespostas()) {
            $this->getTemplate()->block('BLOCO_NAO_RESPOSTAS_INTENCAO');
            $this->getTemplate()->block('BLOCO_BOTAO_VOLTAR');

            return;
        }
        $this->collectorListTableIntencaoItem();
    }

    /**
     * [sqlSelectRespostaIntencao description].
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     * @param [type] $situacaoResposta
     *            [description]
     *
     * @return [type] [description]
     */
    public static function sqlSelectRespostaIntencao($sequencialIntencao, $anoIntencao, $situacaoResposta = null)
    {
        $sql = ' SELECT cintrpsequ, cintrpsano, corglicodi, frinrpsitu, trinrpdcad, cusupocodi, trinrpulat ';
        $sql .= ' FROM sfpc.tbrespostaintencaorp ';
        $sql .= ' WHERE ';
        $sql .= " cintrpsequ = $sequencialIntencao ";
        $sql .= " AND cintrpsano = $anoIntencao ";

        if (! is_null($situacaoResposta)) {
            $sql .= " AND frinrpsitu = '$situacaoResposta' ";
        }

        return $sql;
    }

    /**
     */
    private function proccessRetirar()
    {
        if (isset($this->variables['post']['CheckItem'])) {
            foreach ($this->variables['post']['CheckItem'] as $value) {
                $value = $value - 1;
                unset($_SESSION['intencaoItem'][$value]);
            }
        }

        $aux = new ArrayObject();
        foreach ($_SESSION['intencaoItem'] as $value) {
            $aux[] = $value;
        }
        unset($_SESSION['intencaoItem']);
        $_SESSION['intencaoItem'] = $aux;
        unset($aux);
    }

    /**
     * [filtroPesquisaValido description].
     *
     * @return [type] [description]
     */
    private function filtroPesquisaValido()
    {
        $retorna = true;

        $dataInicio = $this->variables['post']['DataInicioCadastro'];
        $dataFim = $this->variables['post']['DataFimCadastro'];

        if (! empty($dataInicio) && ! ClaHelper::validationData($dataInicio)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de início não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        if (! empty($dataFim) && ! ClaHelper::validationData($dataFim)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Data de fim não é válida';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        return $retorna;
    }

    /**
     * [listarOrgaosIntencao description].
     *
     * @return [type] [description]
     */
    private function listarOrgaosIntencao()
    {
        $intencao = current($this->intencao);
        $ordem = 'nome';
        $sql = $this->sqlSelectOrgaosIntencao($intencao->cintrpsequ, $intencao->cintrpsano, $ordem);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($resultado);

        foreach ($resultado as $row) {
            $this->getTemplate()->ORGAO_INTENCAO = $row->eorglidesc;
            $this->getTemplate()->block('BLOCO_ORGAO_INTENCAO');
        }
    }

    public function listarDocumentos($Linha) {
        // Documentos
        $documentos = $this->documentosIntencao($Linha->cintrpsequ, $Linha->cintrpsano);
        if(!empty($documentos)) {
            foreach ($documentos as $key => $documento) {
                $documentoDecodificado = base64_decode($documento->iintraarqu);
                $this->getTemplate()->VALOR_DOCUMENTO_KEY = 'documento'.$documento->cintrasequ.'arquivo'.$key;
                $this->getTemplate()->VALOR_DOCUMENTO_NOME = $documento->eintranome;
                $this->getTemplate()->HEX_DOCUMENTO  = $documentoDecodificado;
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
            $this->getTemplate()->block('BLOCO_DOCUMENTOS');
        }
    }

    /**
     * Lista os órgãos da intenção.
     */
    private function listarOrgaosPorResposta()
    {
        $chaveIntencao = $this->getChaveIntencao();
        $database = ClaDatabasePostgresql::getConexao();
        $ordem = 'situacao';
        $sql = $this->sqlSelectOrgaosIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao'], $ordem);
        $resultado = executarSQL($database, $sql);

        $situacaoRespostas = array();
        $row = null;
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $situacaoRespostas[] = $row->frinrpsitu;
            $this->definirBlocoOrgaoPorResposta($row);
        }

        $this->exibirTabelaBloco(array_unique($situacaoRespostas));
    }

    /**
     * Exibe a tabela HTML que agrupa os órgãos por situação da resposta.
     *
     * @param array $situacoesFiltradas
     */
    private function exibirTabelaBloco($situacoesFiltradas)
    {
        if (in_array('A', $situacoesFiltradas)) {
            $this->getTemplate()->block('BLOCO_INTENCAO_INFORMADA');
        }

        if (in_array('I', $situacoesFiltradas)) {
            $this->getTemplate()->block('BLOCO_INTENCAO_RASCUNHO');
        }

        if (in_array(null, $situacoesFiltradas)) {
            $this->getTemplate()->block('BLOCO_INTENCAO_SEM_PREENCHER');
        }
    }

    /**
     * Insere o órgão no bloco apropriado, de acordo com a situação da resposta.
     *
     * @param unknown $orgao
     */
    private function definirBlocoOrgaoPorResposta($orgao)
    {
        $situacao = $orgao->frinrpsitu;

        switch ($situacao) {
            case 'A':
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_INFORMADA = $orgao->eorglidesc;
                $dataFormatada = ClaHelper::converterDataBancoParaBr($orgao->trinrpulat);
                $this->getTemplate()->VALOR_DATA_ULTIMA_ALTERACAO_INTENCAO_INFORMADA = $dataFormatada;
                $this->getTemplate()->block('BLOCO_ITEM_INTENCAO_INFORMADA');
                break;
            case 'I':
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_RASCUNHO = $orgao->eorglidesc;
                $dataFormatada = ClaHelper::converterDataBancoParaBr($orgao->trinrpulat);
                $this->getTemplate()->VALOR_DATA_ULTIMA_ALTERACAO_INTENCAO_RASCUNHO = $dataFormatada;
                $this->getTemplate()->block('BLOCO_ITEM_INTENCAO_RASCUNHO');
                break;
            default:
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_SEM_PREENCHER = $orgao->eorglidesc;
                $this->getTemplate()->block('BLOCO_ITEM_INTENCAO_SEM_PREENCHER');
        }
    }

    /**
     * [sqlSelectOrgaosIntencao description].
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     * @param string $ordem
     *            [description]
     *
     * @return [type] [description]
     */
    private function sqlSelectOrgaosIntencao($sequencialIntencao, $anoIntencao, $ordem = 'nome', $situacao = null)
    {
        $orderBy = ' b.eorglidesc ';

        $sql = ' SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi, b.eorglidesc ';
        $sql .= ' FROM sfpc.tbintencaorporgao a ';
        $sql .= ' 	   INNER JOIN sfpc.tborgaolicitante b ON a.corglicodi = b.corglicodi ';
        $sql .= ' WHERE ';
        $sql .= "      a.cintrpsequ = $sequencialIntencao ";
        $sql .= "      AND a.cintrpsano = $anoIntencao ";

        if(!is_null($situacao)) {
            $sql .= "      AND a.finrpositu = '$situacao' ";
        }

        $sql .= " ORDER BY $orderBy ";

        return $sql;
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @param unknown $sequencialItemIntencao
     */
    private function listarOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao)
    {
        $database = Conexao();
        $sql = $this->sqlSelectOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao);
        $resultado = executarSQL($database, $sql);
        $orgao = null;
        while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->VALOR_ORGAO_ITEM = $orgao->eorglidesc;
            $this->getTemplate()->VALOR_QUANTIDADE_ITEM_ORGAO = converte_valor_estoques($orgao->airirpqtpr);

            $this->getTemplate()->block('BLOCO_ORGAO_ITEM');
        }
    }

    /**
     *
     * @param unknown $sequencialIntencao
     * @param unknown $anoIntencao
     * @param unknown $sequencialItemIntencao
     *
     * @return string
     */
    private function sqlSelectOrgaosPorItem($sequencialIntencao, $anoIntencao, $sequencialItemIntencao)
    {
        $sql = "
            SELECT O.EORGLIDESC, sum(I.AIRIRPQTPR) AS AIRIRPQTPR
            FROM
                SFPC.TBITEMRESPOSTAINTENCAORP I
            INNER JOIN SFPC.TBORGAOLICITANTE O ON I.CORGLICODI = O.CORGLICODI
            INNER JOIN SFPC.TBRESPOSTAINTENCAORP R
            ON R.CORGLICODI = I.CORGLICODI
            AND R.cintrpsequ = I.cintrpsequ
            AND R.cintrpsano = I.cintrpsano
            and R.FRINRPSITU = 'A'
            WHERE
                I.CINTRPSEQU =  %d
                AND I.CINTRPSANO = %d
                AND I.CITIRPSEQU = %d
            GROUP BY O.EORGLIDESC
            ORDER BY O.EORGLIDESC
        ";

        return sprintf($sql, $sequencialIntencao, $anoIntencao, $sequencialItemIntencao);
    }

    /**
     * [listarItensIntencao description].
     *
     * @return [type] [description]
     */
    private function listarItensIntencao()
    {
        $intencao = current($this->intencao);
        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlSelectItensIntencao($intencao->cintrpsequ, $intencao->cintrpsano);

        $resultado = executarSQL($database, $sql);

        $totalEstimado = 0;
        $item = null;
        while ($resultado->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itemIntencao = $this->unificarChavesItem($item);

            $TipoGrupo = 'M';

            if($itemIntencao->tipo == "CADUS"){
                $TipoGrupo = 'S';
            }

            $this->getTemplate()->VALOR_TIPO_GRUPO = $TipoGrupo;
            $this->getTemplate()->VALOR_ITEM_CODIGO_MATERIAL_SERVICO = $itemIntencao->sequencialItem;

            $this->getTemplate()->VALOR_ITEM_ORDEM                  = $itemIntencao->ordem;
            $this->getTemplate()->VALOR_ITEM_DESCRICAO              = $itemIntencao->descricao;
            $this->getTemplate()->VALOR_ITEM_DESCRICAO_DETALHADA    = empty($itemIntencao->descricaoDetalhada) ? '---' : $itemIntencao->descricaoDetalhada;
            $this->getTemplate()->VALOR_ITEM_TIPO                   = $itemIntencao->tipo;
            $this->getTemplate()->VALOR_ITEM_CODIGO_REDUZIDO        = $itemIntencao->sequencialItem;
            $this->getTemplate()->VALOR_ITEM_TRP_UNITARIO           = converte_valor_estoques($itemIntencao->valorTrpUnitario);
            $this->getTemplate()->VALOR_ITEM_ESTIMADO_UNITARIO      = converte_valor_estoques($itemIntencao->valorUnitario);
            $this->getTemplate()->VALOR_ITEM_QUANTIDADE_CONSOLIDADA = converte_valor_estoques($itemIntencao->quantidadeConsolidada);

            $this->getTemplate()->block('BLOCO_ITEM');
            $this->listarOrgaosPorItem($intencao->cintrpsequ, $intencao->cintrpsano, $itemIntencao->sequencialItemIntencao);
            $this->getTemplate()->block('BLOCO_ITEM_INTENCAO');

            $totalPorItem = $itemIntencao->valorUnitario * $itemIntencao->quantidadeConsolidada;
            $totalEstimado = $totalEstimado + $totalPorItem;
        }

        if ($resultado->numRows() > 0) {
            $this->getTemplate()->block('BLOCO_ITENS_INTENCAO');
            $this->getTemplate()->VALOR_TOTAL_ESTIMADO = converte_valor_estoques($totalEstimado);
        } else {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr(self::INTENCAO_SEM_DADOS_CONSOLIDADOS, 1, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $this->getTemplate()->block('BLOCO_BOTAO_VOLTAR');
        }
    }

    /**
     * [unificarChavesItem description].
     *
     * @param [type] $item
     *            [description]
     *
     * @return [type] [description]
     */
    private function unificarChavesItem($item)
    {
        $novoItem = array();

        // Atributos em comum
        $novoItem['sequencialItemIntencao'] = $item->citirpsequ;
        $novoItem['sequencialIntencao']     = $item->cintrpsequ;
        $novoItem['anoIntencao']            = $item->cintrpsano;
        $novoItem['quantidadeConsolidada']  = $item->qtdconsolidada;
        $novoItem['ordem']                  = $item->aitirporde;
        $novoItem['valorUnitario']          = $item->vitirpvues;

        // Serviço
        $novoItem['tipo']               = 'CADUS';
        $novoItem['generico']           = false;
        $novoItem['descricao']          = $item->eservpdesc;
        $novoItem['descricaoDetalhada'] = $item->eitirpdescse;
        $novoItem['sequencialItem']     = $item->cservpsequ;
        $novoItem['valorTrpUnitario']   = '---';

        // Material
        if (isset($item->cmatepsequ)) {
            $novoItem['tipo']               = 'CADUM';
            $novoItem['generico']           = ($item->fmatepgene == 'S') ? true : false;
            $novoItem['descricao']          = $item->ematepdesc;
            $novoItem['descricaoDetalhada'] = $item->eitirpdescmat;
            $novoItem['sequencialItem']     = $item->cmatepsequ;
            $novoItem['valorTrpUnitario']   = Service::getValorEstimadoTRP($item->cmatepsequ, 'CADUM');
        }

        return (object) $novoItem;
    }

    /**
     *
     * @param integer $sequencialIntencao
     * @param integer $anoIntencao
     *
     * @return string
     */
    private function sqlSelectItensIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = "
            SELECT DISTINCT
                    I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, SUM(R.AIRIRPQTPR) AS QTDCONSOLIDADA,
                    I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC, I.CSERVPSEQU, I.EITIRPDESCSE,
                    I.AITIRPORDE, I.VITIRPVUES, M.FMATEPGENE

            FROM SFPC.TBITEMRESPOSTAINTENCAORP R

                 INNER JOIN SFPC.TBITEMINTENCAOREGISTROPRECO I
                         ON R.citirpsequ = I.citirpsequ and I.cintrpsequ = R.cintrpsequ and I.cintrpsano = R.cintrpsano

                 INNER JOIN SFPC.TBRESPOSTAINTENCAORP RI
                         ON RI.corglicodi = R.corglicodi AND RI.CINTRPSEQU = R.CINTRPSEQU AND RI.CINTRPSANO = R.CINTRPSANO AND RI.frinrpsitu = 'A'

                 LEFT JOIN SFPC.TBMATERIALPORTAL M
                              ON I.CMATEPSEQU = M.CMATEPSEQU

                 LEFT JOIN SFPC.TBSERVICOPORTAL S
                              ON I.CSERVPSEQU = S.CSERVPSEQU
            WHERE
                R.CINTRPSEQU = %d
                AND R.CINTRPSANO = %d
            GROUP BY
                I.CITIRPSEQU, I.CINTRPSEQU, I.CINTRPSANO, I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC,
                I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, M.FMATEPGENE
            ORDER BY
                I.AITIRPORDE
        ";

        // print_r($sequencialIntencao);
        // echo '<br>';
        // print_r($anoIntencao);
        // echo '<br>';
        // print_r($sql);die;

        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    private function hasRespostas()
    {
        $existeRespostas = false;

        $sql = '
            SELECT iirp.cintrpsequ
                   , iirp.cintrpsano
                   , iirp.citirpsequ
                   , SUM(irirp.airirpqtpr)
              FROM sfpc.tbitemintencaoregistropreco iirp
                   INNER JOIN sfpc.tbitemrespostaintencaorp irirp
                           ON irirp.cintrpsequ = iirp.cintrpsequ
                              AND irirp.cintrpsano = iirp.cintrpsano
                              AND irirp.citirpsequ = iirp.citirpsequ
             WHERE iirp.cintrpsequ = %d
                   AND iirp.cintrpsano = %d
          GROUP BY iirp.cintrpsequ
                   , iirp.cintrpsano
                   , iirp.citirpsequ
        ';
        $intencao = current($this->intencao);
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $intencao->cintrpsequ, $intencao->cintrpsano));

        ClaDatabasePostgresql::hasError($res);

        if (count($res) > 0) {
            $existeRespostas = true;
        }

        return $existeRespostas;
    }

    /**
     * Configuration Initial.
     */
    private function configInitial()
    {
        $this->loadIntencao();

        if (is_null($this->intencao)) {
            return;
        }

        $this->listarOrgaosIntencao();
        $intencao = current($this->intencao);
        $this->listarDocumentos($intencao);

        $numeroIntencao = substr($intencao->cintrpsequ + 10000, 1) . '/' . $intencao->cintrpsano;
        $this->getTemplate()->VALOR_NUMERO_INTENCAO = $numeroIntencao;
        $dataFormatada = ClaHelper::converterDataBancoParaBr($intencao->tintrpdcad);
        $this->getTemplate()->VALOR_DATA_CADASTRAMENTO_INTENCAO = $dataFormatada;
        $dataFormatada = ClaHelper::converterDataBancoParaBr($intencao->tintrpdlim);
        $this->getTemplate()->VALOR_DATA_LIMITE_INTENCAO = $dataFormatada;
        $this->getTemplate()->VALOR_OBJETO_INTENCAO = $intencao->xintrpobje;
        $this->getTemplate()->VALOR_OBSERVACAO_INTENCAO = $intencao->xintrpobse;
        $this->getTemplate()->VALOR_SITUACAO_ATUAL_INTENCAO = $intencao->fintrpsitu;

        if (! $this->hasRespostas()) {
            return;
        }

        $this->listarItensIntencao();
    }

    /**
     *
     * @return multitype:string Ambigous <NULL, unknown> NULL
     */
    private function getChaveIntencao()
    {
        $chaveIntencao = array(
            'sequencialIntencao' => '',
            'anoIntencao' => ''
        );

        $numeroIntencao = empty($_GET['numero']) ? $_POST['NumeroIntencaoAcessada'] : $_GET['numero'];

        if (! empty($numeroIntencao)) {
            $numeroIntencao = explode('/', $numeroIntencao);
            $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != '') ? $numeroIntencao[0] : null;
            $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != '') ? $numeroIntencao[1] : null;

            $chaveIntencao['sequencialIntencao'] = $sequencialIntencao;
            $chaveIntencao['anoIntencao'] = $anoIntencao;
        }

        return $chaveIntencao;
    }

    /**
     * Resgata do banco de dados a intenção que possua o número recebido
     * via GET.
     *
     * @return \Pitang\CadRegistroPrecoIntencaoManter\Object
     */
    private function loadIntencao()
    {
        $chaveIntencao = $this->getChaveIntencao();

        if (! empty($chaveIntencao['sequencialIntencao']) && ! empty($chaveIntencao['anoIntencao'])) {
            // @todo altarar a conexao
            $sql = $this->sqlSelectIntencaolocal($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
            $resultSet = ClaDatabasePostgresql::executarSQL($sql);

            ClaDatabasePostgresql::hasError($resultSet);

            $this->intencao = $resultSet;
        }

        if (is_null($this->intencao)) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Intenção não selecionada', 2, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
        }
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem'].
     */
    private function collectorSessionItem()
    {
        if (isset($_SESSION['item'])) {
            $countItem = count($_SESSION['item']);
            for ($i = 0; $i < $countItem; ++ $i) {
                $_SESSION['intencaoItem'][] = $_SESSION['item'][$i];
            }
        }
        // cleaning for news itens
        unset($_SESSION['item']);
    }

    /**
     */
    private function collectorListTableIntencaoItem()
    {
        $countItem = isset($_SESSION['intencaoItem']) ? count($_SESSION['intencaoItem']) : 0;

        if ($countItem > 0) {
            // $item = null;
            for ($i = 0; $i < $countItem; ++ $i) {
                if ($this->getTemplate()->exists('VALOR_CODIGO_REDUZIDO') && $this->getTemplate()->VALOR_TIPO == 'CADUM') {
                    $this->getTemplate()->VALOR_ESTIMADO_TRP = converte_valor_estoques(calcularValorTrp(Conexao(), 1, $this->getTemplate()->VALOR_CODIGO_REDUZIDO));
                    if (! $this->isMaterialGenerico($this->getTemplate()->VALOR_CODIGO_REDUZIDO)) {
                        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '---';
                        $this->getTemplate()->block('BLOCO_SEM_DESCRICAO_DETALHADA');
                    } else {
                        $this->getTemplate()->block('BLOCO_TEXTAREA_DESCRICAO_DETALHADA');
                    }
                }
                // $this->getTemplate()->block('BLOCO_ITEM_INTENCAO');
            }

            // $this->getTemplate()->block('BLOCO_ITENS_INTENCAO');
        }
    }

    /**
     *
     * @param int $codigo
     * @param string $tipo
     *
     * @return string
     */
    private function getValorEstimadoTRP($codigo, $tipo)
    {
        $database = ClaDatabasePostgresql::getConexao();

        if ('CADUM' == $tipo) {
            return converte_valor_estoques(calcularValorTrp($database, 2, $codigo));
        }

        return '---';
    }

    /**
     *
     * @return unknown
     */
    private function insertIntencaoDB($database)
    {
        $this->collectorRegistroPrecoIntencaoEntity();
        $res = $database->autoExecute('sfpc.tbintencaoregistropreco', $this->variables['entityIntencao'], DB_AUTOQUERY_INSERT);

        if (PEAR::isError($res)) {
            die($res->getMessage());
        }

        return $res;
    }

    /**
     */
    private function proccessIncluir()
    {
        if ($this->validationForm() && $this->repositoryIntencaoIncluir()) {
            $this->clearListIntencaoItem();
            unset($this->variables['post']);
        }

        $this->configInitial();
    }

    /**
     * Cleaning array intencaoItem session.
     */
    private function clearListIntencaoItem()
    {
        unset($_SESSION['intencaoItem']);
    }

    /**
     * Validation data form.
     *
     * @return bool
     */
    private function validationForm()
    {
        $intencaoError = $this->validationIntencao();
        $intencaoOrgaoError = $this->validationIntencaoOrgaos();
        // $this->validationIntencaoItem();

        if (! $intencaoError || ! $intencaoOrgaoError) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validationIntencaoOrgaos()
    {
        if (count($this->variables['post']['Orgaos']) < 1) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Órgãos não selecionado';
            $this->getTemplate()->block('BLOCO_ERRO', true);

            return false;
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    private function validationIntencao()
    {
        $retorna = true;
        $dataLimite = $this->variables['post']['DataLimite'];
        if (! isset($dataLimite) || empty($dataLimite)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'DataLimite não informado';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        $objeto = $this->variables['post']['Objeto'];
        if (! isset($objeto) || empty($objeto)) {
            $this->getTemplate()->MENSAGEM_ERRO = 'Objeto não informado';
            $this->getTemplate()->block('BLOCO_ERRO', true);
            $retorna = false;
        }

        return $retorna;
    }

    /**
     *
     * @return bool
     */
    private function repositoryIntencaoIncluir()
    {
        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);

        $intencaoId = $this->insertIntencaoDB($database);

        $database->commit();
        $database->disconnect();

        if ($intencaoId > 0) {
            return true;
        }

        return false;
    }

    /**
     */
    private function collectorRegistroPrecoIntencaoEntity()
    {
        $dataLimite = new DateTime($this->variables['post']['DataLimite']);
        $this->variables['entityIntencao']['cintrpsequ'] = 1;
        $ano = (int) $dataLimite->format('Y');
        $this->variables['entityIntencao']['cintrpsano'] = $ano;
        $this->variables['entityIntencao']['tintrpdlim'] = (string) $dataLimite->format('Y-m-d H:i:s');
        $this->variables['entityIntencao']['xintrpobje'] = (string) $this->variables['post']['Objeto'];
        $this->variables['entityIntencao']['xintrpobse'] = (string) $this->variables['post']['Observacao'];
        $this->variables['entityIntencao']['fintrpsitu'] = 'I';
        $this->variables['entityIntencao']['tintrpdcad'] = (string) date('Y-m-d H:i:s');
        $this->variables['entityIntencao']['cusupocodi'] = (int) $_SESSION['_cusupocodi_'];
        $this->variables['entityIntencao']['tintrpulat'] = (string) date('Y-m-d H:i:s');
    }

    /**
     */
    private function collectorRegistroPrecoIntencaoOrgaosEntity()
    {
        $this->variables['entityIntencaoOrgaos']['cintrpsequ'] = null;
        $this->variables['entityIntencaoOrgaos']['cintrpsano'] = null;
        $this->variables['entityIntencaoOrgaos']['corglicodi'] = null;
        $this->variables['entityIntencaoOrgaos']['cusupocodi'] = null;
        $this->variables['entityIntencaoOrgaos']['tinrpoulat'] = null;
    }

    /**
     */
    private function collectorRegistroPrecoIntencaoItem()
    {
        $this->variables['entityIntencaoItem']['cintrpsequ'] = null;
        $this->variables['entityIntencaoItem']['cintrpsano'] = null;
        $this->variables['entityIntencaoItem']['citirpsequ'] = null;
        $this->variables['entityIntencaoItem']['cmatepsequ'] = null;
        $this->variables['entityIntencaoItem']['cservpsequ'] = null;
        $this->variables['entityIntencaoItem']['aitirporde'] = null;
        $this->variables['entityIntencaoItem']['vitirpvues'] = null;
        $this->variables['entityIntencaoItem']['eitirpmarc'] = null;
        $this->variables['entityIntencaoItem']['eitirpmode'] = null;
        $this->variables['entityIntencaoItem']['eitirpdescmat'] = null;
        $this->variables['entityIntencaoItem']['eitirpdescse'] = null;
        $this->variables['entityIntencaoItem']['tintrpdcad'] = null;
        $this->variables['entityIntencaoItem']['cusupocodi'] = null;
        $this->variables['entityIntencaoItem']['titirpulat'] = null;
    }

    public static function sqlSelectOrgaoLicitanteIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = ' SELECT CORGLICODI ';
        $sql .= ' FROM SFPC.TBINTENCAORPORGAO ';
        $sql .= ' WHERE ';
        $sql .= " CINTRPSEQU = $sequencialIntencao";
        $sql .= " AND CINTRPSANO = $anoIntencao ";
        $sql .= ' ORDER BY CORGLICODI ';

        return $sql;
    }

    private function getOrgaoIntencao()
    {
        $chaveIntencao = $this->getChaveIntencao();

        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlSelectOrgaoLicitanteIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
        $resultado = $database->getCol($sql);

        return $resultado;
    }

    /**
     * [buildSelectOrgao description].
     *
     * @return [type] [description]
     */
    private function buildSelectOrgao()
    {
        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlSelectOrgaoLicitanteAtivo();
        $res = executarSQL($database, $sql);
        $row = null;
        $this->variables['atual'] = array();

        $orgaosIntencao = $this->getOrgaoIntencao();
        if (is_array($orgaosIntencao)) {
            $this->variables['atual'] = $orgaosIntencao;
        }

        while ($res->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->VALOR_ITEM_ORGAO = $row->corglicodi;
            $this->getTemplate()->ITEM_ORGAO = $row->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if (in_array($this->getTemplate()->VALOR_ITEM_ORGAO, $this->variables['atual'])) {
                $this->getTemplate()->ITEM_ORGAO_SELECIONADO = 'selected';
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável ITEM_ORGAO_SELECIONADO
                $this->getTemplate()->clear('ITEM_ORGAO_SELECIONADO');
            }
            $this->getTemplate()->block('BLOCO_ITEM_ORGAO');
        }
    }

    /**
     * [sqlSelectItemIntencao description].
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     *
     * @return string
     */
    public static function sqlSelectItemIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = ' SELECT I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC, ';
        $sql .= ' 		 I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, I.EITIRPMARC, I.EITIRPMODE, M.FMATEPGENE ';
        $sql .= ' FROM SFPC.TBITEMINTENCAOREGISTROPRECO I ';
        $sql .= ' 	   LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU ';
        $sql .= ' 	   LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU ';
        $sql .= " WHERE I.CINTRPSEQU = $sequencialIntencao AND I.CINTRPSANO = $anoIntencao ";
        $sql .= ' 		AND I.CMATEPSEQU IS NOT NULL OR I.CSERVPSEQU IS NOT NULL ';
        $sql .= ' ORDER BY I.AITIRPORDE ';

        return $sql;
    }

    private function processVoltar()
    {
        $chaveIntencao = $this->getChaveIntencao();

        $uri = 'CadRegistroPrecoIntencaoAcompanharVisualizar.php?numero=';
        $uri .= $chaveIntencao['sequencialIntencao'] . '/' . $chaveIntencao['anoIntencao'];

        header('Location: ' . $uri);
        exit();
    }

    private function processImprimirResumo()
    {
        $pdf = new PdfPrecoIntencaoConsolidacaoResumo();
        $pdf->setChaveIntencao($this->getChaveIntencao());
        $pdf->gerarRelatorio();
    }

    private function processVisualizarConsolidacao()
    {
        $chaveIntencao = $this->getChaveIntencao();

        $uri = 'CadRegistroPrecoIntencaoVisualizarConsolidacao.php?numero=';
        $uri .= $chaveIntencao['sequencialIntencao'] . '/' . $chaveIntencao['anoIntencao'];

        header('Location: ' . $uri);
        exit();
    }

    private function processImprimirCompleto()
    {
        $pdf = new PdfVisualizarConsolidacaoCompleta();
        $pdf->setChaveIntencao($this->getChaveIntencao());
        $pdf->gerarRelatorio();
    }

    /**
     * [frontController description].
     *
     * @return [type] [description]
     */
    protected function frontController()
    {
        //$botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';
        // Kim
        if(isset($_POST['Botao'])){
            $botao = $_POST['Botao'];
        }elseif(isset($_GET['Botao'])){
            $botao = $_GET['Botao'];
        }else{
            $botao = 'Principal';
        }
        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'VisualizarConsolidacao':
                $this->processVisualizarConsolidacao();
                break;
            case 'ImprimirResumo':
                $this->processImprimirResumo();
                break;
            case 'ImprimirCompleto':
                $this->processImprimirCompleto();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
                break;
        }
    }

    /**
     * [__construct description].
     *
     * @param ArrayObject $session
     *            [description]
     */
    public function __construct(array $variablesGlobals)
    {
        /*
         * Settings
         */
        $template = new TemplatePaginaPadrao('templates/CadRegistroPrecoIntencaoVisualizarConsolidacao.html', 'Registro de Preço > Intenção > Acompanhar');

        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        /*
         * Front Controller for action
         */
        $this->frontController();
    }

    /**
     * Bootstrap application.
     */
    public static function bootstrap()
    {
        $arrayGlobals = parent::setup();

        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'POST') {
            $arrayGlobals['post'] = filter_input_array(INPUT_POST, self::filterSanitizePOST());
        }

        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
            $arrayGlobals['get'] = filter_input_array(INPUT_GET, self::filterSanitizeGET());
        }
        // Adiciona páginas no MenuAcesso #
        AddMenuAcesso('/estoques/CadIncluirItem.php');
        AddMenuAcesso('/estoques/CadItemDetalhe.php');

        $app = new self($arrayGlobals);
        echo $app->run();

        unset($app, $arrayGlobals);
    }

    /**
     * Filter Sanitize input data POST.
     *
     * @return array [description]
     */
    public static function filterSanitizePOST()
    {
        return array(
            'Botao' => FILTER_SANITIZE_STRING,
            'NumeroIntencao' => FILTER_SANITIZE_STRING,
            'NumeroIntencaoAcessada' => FILTER_SANITIZE_STRING,
            'SituacaoAtualIntencao' => FILTER_SANITIZE_STRING,
            'DataInicioCadastro' => FILTER_SANITIZE_STRING,
            'DataFimCadastro' => FILTER_SANITIZE_STRING,
            'Objeto' => FILTER_SANITIZE_STRING,
            'Orgaos' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ObservacaoIntencao' => FILTER_SANITIZE_STRING,
            'intencaoItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'DataLimite' => FILTER_SANITIZE_STRING,
            'Objeto' => FILTER_SANITIZE_STRING,
            'Observacao' => FILTER_SANITIZE_STRING,
            'Item' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'Material' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'CheckItem' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'Descricao' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'DescricaoDetalhada' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'Tipo' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'CodigoReduzido' => array(
                'filter' => FILTER_SANITIZE_NUMBER_INT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ValorEstimadoTRP' => array(
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ValorUnitarioEstimado' => array(
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ValorMarca' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'ValorModelo' => array(
                'filter' => FILTER_SANITIZE_STRING,
                'flags' => FILTER_REQUIRE_ARRAY
            )
        );
    }

    /**
     * Filter Sanitize input data GET.
     *
     * @return array [description]
     */
    public static function filterSanitizeGET()
    {
        return array(
            'Botao' => FILTER_SANITIZE_STRING,
            'numero' => FILTER_SANITIZE_STRING
        );
    }
}
/*
 */
CadRegistroPrecoIntencaoVisualizarConsolidacao::bootstrap();
