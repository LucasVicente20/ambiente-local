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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.30.4
 */
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------
# Alterado: Lucas Vicente
# data: 16/09/2022
# Objetivo: Tarefa Redmine 268892
#---------------------------------------------------------------------

// 220038--

if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

class CadRegistroPrecoIntencaoAcompanharVisualizar extends ClaRegistroPrecoIntencao
{
    const MENSAGEM_INTENCAO_RESPOSTA_ATIVA = 'Intenção não pode ser excluída, pois já possui resposta ativa';

    const MENSAGEM_INTENCAO_EXCLUIDA_SUCESSO = 'Intenção excluída com sucesso';

    const MENSAGEM_INTENCAO_EXCLUIDA_ERRO = 'Falha ao excluir intenção';

    /**
     * [$intencao description]
     *
     * @var unknown
     */
    private $intencao;

    /**
     * [sqlSelectIntencao description]
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     * @return [type] [description]
     */
    protected function sqlSelectIntencaoLocal($sequencialIntencao, $anoIntencao)
    {
        $sql = " SELECT DISTINCT a.cintrpsequ,  a.cintrpsano, a.tintrpdlim, a.xintrpobje, ";
        $sql .= "        a.xintrpobse, a.fintrpsitu, a.tintrpdcad, a.cusupocodi, a.tintrpulat ";
        $sql .= " FROM ";
        $sql .= "        sfpc.tbintencaoregistropreco a ";
        $sql .= " WHERE ";
        $sql .= "		a.cintrpsequ = $sequencialIntencao AND a.cintrpsano = $anoIntencao ";
        
        return $sql;
    }

    /**
     * [proccessPrincipal description]
     *
     * @param [type] $variablesGlobals
     *            [description]
     * @return [type] [description]
     */
    private function proccessPrincipal()
    {
        $this->configInitial();
        
        if (isset($this->variables['post']['NumeroIntencao'])) {
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $this->variables['post']['NumeroIntencao'];
        }
        
        if (isset($this->variables['post']['DataInicioCadastro'])) {
            $this->getTemplate()->VALOR_DATA_INICIO_CADASTRO = $this->variables['post']['DataInicioCadastro'];
        }
        
        if (isset($this->variables['post']['DataFimCadastro'])) {
            $this->getTemplate()->VALOR_DATA_FIM_CADASTRO = $this->variables['post']['DataFimCadastro'];
        }
        
        // $this->collectorListTableIntencaoItem();
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
     * [filtroPesquisaValido description]
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
     * [listarOrgaosIntencao description]
     *
     * @return [type] [description]
     */
    private function listarOrgaosIntencao()
    {
        $chaveIntencao = $this->getChaveIntencao();
        $ordem = 'nome';
        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlSelectOrgaosIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao'], $ordem);
        $resultado = executarSQL($database, $sql);
        $row = null;
        
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $this->getTemplate()->ORGAO_INTENCAO = $row->eorglidesc;
            $this->getTemplate()->block("BLOCO_ORGAO_INTENCAO");
        }
    }

    /**
     * Lista os órgãos da intenção
     *
     */
    function ConsultaJustificativa($codigoOrgao, $anoIntencao){
        $database = ClaDatabasePostgresql::getConexao();
        $chaveIntencao = $this->getChaveIntencao();
        $sql = "SELECT xrinrpjust FROM sfpc.tbrespostaintencaorp ";
        $sql .= " WHERE cintrpsequ = ".$chaveIntencao['sequencialIntencao'];
        $sql .= " AND corglicodi = ".$codigoOrgao;
        $sql .= " AND frinrpsitu = 'A'";
        $sql .= " AND cintrpsano = ".$anoIntencao;
        $resultado = executarSQL($database, $sql);
        
        $justificativaRespostas = array();
        $row = null;
        while ($resultado->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            $this->PlotaJustificativa($row);
        }
        //print_r($row);die;
             
    }

    function PlotaJustificativa($justificativa){
        $this->getTemplate()->VALOR_JUSTIFICATIVA = $justificativa->xrinrpjust;
       // $this->getTemplate()->block("BLOCO_ITEM_INTENCAO_INFORMADA");
    }

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

    public function listarDocumentosIntencao($Linha) {
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
     * Exibe a tabela HTML que agrupa os órgãos por situação da resposta
     *
     * @param array $situacoesFiltradas
     */
    private function exibirTabelaBloco($situacoesFiltradas)
    {
        if (in_array('A', $situacoesFiltradas)) {
             $this->getTemplate()->block("BLOCO_INTENCAO_INFORMADA");
        }
        
        if (in_array('I', $situacoesFiltradas)) {
            $this->getTemplate()->block("BLOCO_INTENCAO_RASCUNHO");
        }
        
        if (in_array(null, $situacoesFiltradas)) {
            $this->getTemplate()->block("BLOCO_INTENCAO_SEM_PREENCHER");
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
        $this->getTemplate()->ORGAO_INTENCAO_RESPOSTA = $orgao->corglicodi;
        switch ($situacao) {
            case 'A':
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_INFORMADA = $orgao->eorglidesc;
                $dataFormatada = ClaHelper::converterDataBancoParaBr($orgao->trinrpulat);
                $this->getTemplate()->VALOR_DATA_ULTIMA_ALTERACAO_INTENCAO_INFORMADA = $dataFormatada;
                $this->ConsultaJustificativa($orgao->corglicodi, $orgao->cintrpsano);
                $this->getTemplate()->block("BLOCO_ITEM_INTENCAO_INFORMADA");
                
               
                break;
            case 'I':
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_RASCUNHO = $orgao->eorglidesc;
                $dataFormatada = ClaHelper::converterDataBancoParaBr($orgao->trinrpulat);
                $this->getTemplate()->VALOR_DATA_ULTIMA_ALTERACAO_INTENCAO_RASCUNHO = $dataFormatada;
                $this->getTemplate()->block("BLOCO_ITEM_INTENCAO_RASCUNHO");
                break;
            default:
                $this->getTemplate()->VALOR_ORGAO_INTENCAO_SEM_PREENCHER = $orgao->eorglidesc;
                $this->getTemplate()->block("BLOCO_ITEM_INTENCAO_SEM_PREENCHER");
        }
    }

    /**
     * [sqlSelectOrgaosIntencao description]
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     * @param string $ordem
     *            [description]
     * @return [type] [description]
     */
    private function sqlSelectOrgaosIntencao($sequencialIntencao, $anoIntencao, $ordem = 'nome', $situacao = null)
    {
        $orderBy = " b.eorglidesc ";
        if ($ordem == 'situacao') {
            $orderBy = " c.frinrpsitu ";
        }
        
        $sql = " SELECT a.cintrpsequ, a.cintrpsano, a.corglicodi, b.eorglidesc, c.frinrpsitu, c.trinrpulat ";
        $sql .= " FROM sfpc.tbintencaorporgao a ";
        $sql .= " INNER JOIN sfpc.tborgaolicitante b ON a.corglicodi = b.corglicodi ";
        $sql .= " LEFT OUTER JOIN sfpc.tbrespostaintencaorp c ON a.corglicodi = c.corglicodi ";
        $sql .= " AND c.cintrpsequ = $sequencialIntencao AND c.cintrpsano = $anoIntencao ";
        $sql .= " WHERE ";
        $sql .= "      a.cintrpsequ = $sequencialIntencao ";
        $sql .= "      AND a.cintrpsano = $anoIntencao ";

        if(!is_null($situacao)) {
            $sql .= "      AND a.finrpositu = '$situacao' ";
        }

        $sql .= " ORDER BY $orderBy ";
        
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
     * Configuration Initial
     */
    private function configInitial()
    {
        $TAMANHO_MAXIMO_LINHA = 69;
        $this->loadIntencao();
        
        if (! is_null($this->intencao)) {
            $this->listarOrgaosIntencao();
            $this->listarOrgaosPorResposta();
            $this->listarDocumentosIntencao($this->intencao);
            
            $textoObservaocao = '';
            
            $tamanhoObservaocao = strlen($this->intencao->xintrpobse);
            
            if ($tamanhoObservaocao > $TAMANHO_MAXIMO_LINHA) {
                $numeroLinha = $tamanhoObservaocao / $TAMANHO_MAXIMO_LINHA;
                $numeroLinha = ceil($numeroLinha);
                
                $posicaoAtual = 0;
                for ($i = 0; $i < $numeroLinha - 1; $i ++) {
                    $textoObservaocao .= substr($this->intencao->xintrpobse, $posicaoAtual, $TAMANHO_MAXIMO_LINHA) . '<br/>';
                    $posicaoAtual += $TAMANHO_MAXIMO_LINHA;
                }
                $textoObservaocao .= substr($this->intencao->xintrpobse, $posicaoAtual);
            } else {
                $textoObservaocao = $this->intencao->xintrpobse;
            }
            
            $numeroIntencao = substr($this->intencao->cintrpsequ + 10000, 1) . '/' . $this->intencao->cintrpsano;
            $this->getTemplate()->VALOR_NUMERO_INTENCAO = $numeroIntencao;
            $dataFormatada = ClaHelper::converterDataBancoParaBr($this->intencao->tintrpdcad);
            $this->getTemplate()->VALOR_DATA_CADASTRAMENTO_INTENCAO = $dataFormatada;
            $dataFormatada = ClaHelper::converterDataBancoParaBr($this->intencao->tintrpdlim);
            $this->getTemplate()->VALOR_DATA_LIMITE_INTENCAO = $dataFormatada;
            $this->getTemplate()->VALOR_OBJETO_INTENCAO = $this->intencao->xintrpobje;
            $this->getTemplate()->VALOR_OBSERVACAO_INTENCAO = $textoObservaocao;
            $this->getTemplate()->VALOR_SITUACAO_ATUAL_INTENCAO = $this->intencao->fintrpsitu;
            
            // $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
        }
    }

    /**
     * [getChaveIntencao description]
     *
     * @return [type] [description]
     */
    private function getChaveIntencao()
    {
        $chaveIntencao = array(
            'sequencialIntencao' => '',
            'anoIntencao' => ''
        );
        $numeroIntencao = $this->variables['get']['numero'];
        
        if (empty($numeroIntencao)) {
            $numeroIntencao = $this->variables['post']['NumeroIntencaoAcessada'];
        }
        
        if (! empty($numeroIntencao)) {
            $numeroIntencao = explode('/', $numeroIntencao);
            $sequencialIntencao = (isset($numeroIntencao[0]) && $numeroIntencao[0] != "") ? $numeroIntencao[0] : null;
            $anoIntencao = (isset($numeroIntencao[1]) && $numeroIntencao[1] != "") ? $numeroIntencao[1] : null;
            
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
            $database = ClaDatabasePostgresql::getConexao();
            $sql = $this->sqlSelectIntencaoLocal($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
            $resultSet = executarSQL($database, $sql);
            
            $this->intencao = $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
        }
        
        if (is_null($this->intencao)) {
            $this->getTemplate()->MENSAGEM_ERRO = ExibeMensStr('Intenção não selecionada', 2, 1);
            $this->getTemplate()->block('BLOCO_ERRO', true);
        }
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem']
     */
    private function collectorSessionItem()
    {
        if (isset($_SESSION['item'])) {
            $countItem = count($_SESSION['item']);
            for ($i = 0; $i < $countItem; $i ++) {
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
        $countItem = count($_SESSION['intencaoItem']);
        if ($countItem > 0) {
            for ($i = 0; $i < $countItem; $i ++) {
                $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM');
            }
            
            $this->getTemplate()->block('BLOCO_HEADER_LISTAGEM_ITEM');
        }
    }

    /**
     *
     * @param integer $codigo
     * @param string $tipo
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
        
        ClaDatabasePostgresql::hasError($res);
        
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
     * Cleaning array intencaoItem session
     */
    private function clearListIntencaoItem()
    {
        unset($_SESSION['intencaoItem']);
    }

    /**
     * Validation data form
     *
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
        $sql = " SELECT CORGLICODI ";
        $sql .= " FROM SFPC.TBINTENCAORPORGAO ";
        $sql .= " WHERE ";
        $sql .= " CINTRPSEQU = $sequencialIntencao";
        $sql .= " AND CINTRPSANO = $anoIntencao ";
        $sql .= " ORDER BY CORGLICODI ";
        
        return $sql;
    }

    private function getOrgaoIntencao()
    {
        $chaveIntencao = $this->getChaveIntencao();
        
        $database = ClaDatabasePostgresql::getConexao();
        $sql = self::sqlSelectOrgaoLicitanteIntencao($chaveIntencao['sequencialIntencao'], $chaveIntencao['anoIntencao']);
        $resultado = $database->getCol($sql);
        
        return $resultado;
    }

    /**
     * [buildSelectOrgao description]
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
                $this->getTemplate()->ITEM_ORGAO_SELECIONADO = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável ITEM_ORGAO_SELECIONADO
                $this->getTemplate()->clear("ITEM_ORGAO_SELECIONADO");
            }
            $this->getTemplate()->block("BLOCO_ITEM_ORGAO");
        }
    }

    /**
     * [sqlSelectItemIntencao description]
     *
     * @param int $sequencialIntencao
     * @param int $anoIntencao
     * @return string
     */
    public static function sqlSelectItemIntencao($sequencialIntencao, $anoIntencao)
    {
        $sql = "
            SELECT I.CMATEPSEQU, M.EMATEPDESC, I.EITIRPDESCMAT, S.ESERVPDESC,
                I.CSERVPSEQU, I.EITIRPDESCSE, I.AITIRPORDE, I.VITIRPVUES, I.EITIRPMARC, I.EITIRPMODE, M.FMATEPGENE
            FROM SFPC.TBITEMINTENCAOREGISTROPRECO I
            LEFT OUTER JOIN SFPC.TBMATERIALPORTAL M ON I.CMATEPSEQU = M.CMATEPSEQU AND I.CMATEPSEQU IS NOT NULL
            LEFT OUTER JOIN SFPC.TBSERVICOPORTAL S ON I.CSERVPSEQU = S.CSERVPSEQU AND I.CSERVPSEQU IS NOT NULL
            WHERE I.CINTRPSEQU = %d AND I.CINTRPSANO = %d
            ORDER BY I.AITIRPORDE
        ";
        
        return sprintf($sql, $sequencialIntencao, $anoIntencao);
    }

    private function processVoltar()
    {
        // Flag que indica o botão voltar
        $_SESSION['voltarPesquisa'] = true;
        
        header('location: CadRegistroPrecoIntencaoAcompanhar.php');
    }

    private function processVisualizarConsolidacao()
    {
        $chaveIntencao = $this->getChaveIntencao();
        
        $uri = 'CadRegistroPrecoIntencaoVisualizarConsolidacao.php?' . http_build_query(array(
            'numero' => $chaveIntencao['sequencialIntencao'] . '/' . $chaveIntencao['anoIntencao']
        ));
        header('Location: ' . $uri);
        exit();
    }

    private function processVisualizarIntecao()
    {
        $chaveIntencao = $this->getChaveIntencao();

        // $uri = 'CadRegistroPrecoIntencaoVisualizar?SeqSolicitacao='. $chaveIntencao['sequencialIntencao'] .'/' . $chaveIntencao['anoIntencao'];
        $uri = 'CadRegistroPrecoIntencaoVisualizar.php?' . http_build_query(array(
            'numero' => $chaveIntencao['sequencialIntencao'] . '/' . $chaveIntencao['anoIntencao']
        ));
        header('Location: ' . $uri);
        exit();
    }

    private function processImprimir()
    {
        $pdf = new PdfAcompanharVisualizar();
        $pdf->ImprimirJustificativa(false);
        $pdf->setChaveIntencao($this->getChaveIntencao());
        $pdf->gerarRelatorio('IntençãoAcompanharVisualizar_');
    }
    private function processImprimirJustificativa()
    {
        $pdf = new PdfAcompanharVisualizar();
        $pdf->setChaveIntencao($this->getChaveIntencao());
        $pdf->ImprimirJustificativa(true);
        $pdf->gerarRelatorio('IntençãoAcompanharVisualizar_');
    }

    /**
     * [frontController description]
     *
     * @return [type] [description]
     */
    protected function frontController()
    {
        $botao = isset($_POST['Botao']) ? $_POST['Botao'] : 'Principal';
        
        switch ($botao) {
            case 'Voltar':
                $this->processVoltar();
                break;
            case 'VisualizarConsolidacao':
                $this->processVisualizarConsolidacao();
                break;
            case 'visualizarIntencao':
                $this->processVisualizarIntecao();
                break;
            case 'Imprimir':
                $this->processImprimir();
                break;
            case 'ImprimirJustificativa':
                $this->processImprimirJustificativa();
                break;
            case 'Principal':
            default:
                $this->proccessPrincipal();
        }
    }

    /**
     * [__construct description]
     *
     * @param ArrayObject $session
     *            [description]
     */
    public function __construct(array $variablesGlobals)
    {
        /**
         * Settings
         */
        $template = new TemplatePaginaPadrao("templates/CadRegistroPrecoIntencaoAcompanharVisualizar.html", "Registro de Preço > Intenção > Acompanhar");
        
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        /**
         * Front Controller for action
         */
        $this->frontController();
    }

    /**
     *
     * @return array
     */
    protected static function filterSanitizePOST()
    {
        return array(
            'Botao' => FILTER_SANITIZE_STRING,
            'RespostaItemQuantidade' => array(
                'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
                'flags' => FILTER_REQUIRE_ARRAY
            ),
            'NumeroIntencao' => FILTER_SANITIZE_STRING,
            'DataInicioCadastro' => FILTER_SANITIZE_STRING,
            'DataFimCadastro' => FILTER_SANITIZE_STRING,
            'NumeroIntencaoAcessada' => FILTER_SANITIZE_STRING
        );
    }

    /**
     *
     * @return array
     */
    protected static function filterSanitizeGET()
    {
        return array(
            'numero' => FILTER_SANITIZE_STRING
        );
    }

    /**
     * Bootstrap application
     */
    public static function bootstrap()
    {
        $arrayGlobals = parent::setup();
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == "POST") {
            $arrayGlobals['post'] = filter_input_array(INPUT_POST, self::filterSanitizePOST());
        }
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
            $arrayGlobals['get'] = filter_input_array(INPUT_GET, self::filterSanitizeGET());
        }
        
        // Adiciona páginas no MenuAcesso #
        AddMenuAcesso('/estoques/CadIncluirItem.php');
        AddMenuAcesso('/estoques/CadItemDetalhe.php');
        $app = new CadRegistroPrecoIntencaoAcompanharVisualizar($arrayGlobals);
        echo $app->run();
        
        unset($app, $arrayGlobals);
    }
}
/**
 * DO REMOVE IT'S BLOCK
 */
CadRegistroPrecoIntencaoAcompanharVisualizar::bootstrap();
