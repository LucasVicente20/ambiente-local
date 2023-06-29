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
 * @version   GIT: v1.21.0-5-g5aebdcf
 * -----------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     21/09/2018
 * Objetivo: Tarefa Redmine 203923
 * -----------------------------------------------------------------------------------
 */
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 12/02/2019
# Objetivo: Tarefa Redmine 210654
#---------------------------------------------------------------------

// 220038--

if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}
Seguranca();

class JanelaExibirResposta extends ClaRegistroPrecoIntencao {
    /**
     * @throws \InvalidArgumentException
     */
    private function validationIntencao() {
        if (! isset($this->variables['get']['intencao'])) {
            throw new InvalidArgumentException('Intencao not found');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validationOrgao() {
        if (! isset($this->variables['get']['orgao'])) {
            throw new InvalidArgumentException('orgao not found');
        }
    }

    private function setupProccessMain() {
        if(!isset($this->variables['post'])){
            $this->validationIntencao();
            $this->validationOrgao();
        }
    }

    /**
     * [sqlSelectIntencao description].
     *
     * @param [type] $sequencialIntencao
     *            [description]
     * @param [type] $anoIntencao
     *            [description]
     *
     * @return [type] [description]
     */
    protected function sqlSelectIntencaoLocal($sequencialIntencao, $anoIntencao, $sequencialOrgao) {
        $sql = "SELECT  DISTINCT A.CINTRPSEQU, A.CINTRPSANO, A.TINTRPDLIM, A.XINTRPOBJE, A.XINTRPOBSE, A.FINTRPSITU,
                                 A.TINTRPDCAD, A.CUSUPOCODI, A.TINTRPULAT, RIRP.TRINRPULAT, OL.EORGLIDESC, UP.EUSUPORESP
                FROM    SFPC.TBINTENCAOREGISTROPRECO A
                        INNER JOIN SFPC.TBRESPOSTAINTENCAORP RIRP ON RIRP.CINTRPSEQU = A.CINTRPSEQU
                                                                     AND RIRP.CINTRPSANO = A.CINTRPSANO
                                                                     AND RIRP.CORGLICODI = $sequencialOrgao
                        INNER JOIN SFPC.TBORGAOLICITANTE OL ON OL.CORGLICODI = RIRP.CORGLICODI
                        LEFT JOIN SFPC.TBUSUARIOPORTAL UP ON RIRP.CUSUPOCODI = UP.CUSUPOCODI
                WHERE   A.CINTRPSEQU = $sequencialIntencao
                        AND A.CINTRPSANO = $anoIntencao ";
        
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
     * @param int $numeroIntencao
     */
    protected function getRespostaItencao($numeroIntencao) {
        $chaveIntencao = explode('/', $numeroIntencao);
        $database      = ClaDatabasePostgresql::getConexao();
        $getOrgao      = (int) $this->variables['get']['orgao'];
        
        $sql = $this->sqlSelectIntencaoLocal($chaveIntencao[0], $chaveIntencao[1], $getOrgao);
        
        $resultSet = executarSQL($database, $sql);
        
        return $resultSet->fetchRow(DB_FETCHMODE_OBJECT);
    }

    /**
     * @param \stdClass $respostaOrgao
     */
    private function setTemplateValuesIntencao($respostaOrgao) {
        $dataFormatada = ClaHelper::converterDataBancoParaBr($respostaOrgao->tintrpdcad);
        $this->getTemplate()->VALOR_RESPOSTA_DATA_CADASTRO = $dataFormatada;
        
        $dataFormatada = ClaHelper::converterDataBancoParaBr($respostaOrgao->tintrpdlim);
        $this->getTemplate()->VALOR_RESPOSTA_DATA_LIMITE = $dataFormatada;
        
        $this->getTemplate()->VALOR_RESPOSTA_OBJETO = strtoupper2($respostaOrgao->xintrpobje);
        $this->getTemplate()->VALOR_RESPOSTA_OBSERVACAO = strtoupper2($respostaOrgao->xintrpobse);
        $this->getTemplate()->VALOR_ORGAO_IRP_RESPONDIDA = strtoupper2($respostaOrgao->eorglidesc);
        
        $dataUltimaAlteracaoResposta = new DataHora($respostaOrgao->trinrpulat);
        $this->getTemplate()->VALOR_DATA_ULTIMA_ALTERACAO = $dataUltimaAlteracaoResposta->formata('d/m/Y H:i');

        $this->getTemplate()->VALOR_USUARIO_RESPONSAVEL = strtoupper2($respostaOrgao->eusuporesp);
        
        $situacao = $this->getSituacaoIntencao($respostaOrgao->cintrpsequ, $respostaOrgao->cintrpsano, $this->getGrupoCodigoSession());
        
        if ($situacao != 'RESPONDIDA') {
            $this->getTemplate()->block('BLOCO_SITUACAO_INTENCAO');
        }
    }

    /**
     * @param \stdClass $item
     */
    private function setDescricaoDetalhadaByItem($item) {
        $this->getTemplate()->VALOR_ITEM_DESCRICAO_DETALHADA = '---';
        
        if (! empty($item->eitirpdescmat)) {
            $this->getTemplate()->VALOR_ITEM_DESCRICAO_DETALHADA = $item->eitirpdescmat;
        }
        
        if (! empty($item->eitirpdescse)) {
            $this->getTemplate()->VALOR_ITEM_DESCRICAO_DETALHADA = $item->eitirpdescse;
        }
    }

    /**
     * @param array $itensList
     */
    private function setTemplateValuesIntencaoItens(array $itensList) {
        $displayItem = false;
        
        foreach ($itensList as $item) {
            $this->getTemplate()->VALOR_ITEM_ORD = $item->citirpsequ;
            $this->getTemplate()->VALOR_ITEM_UNITARIO_TRP = '<center> --- </center>';
            
            $TipoGrupo = 'M';
            
            if (! is_null($item->cmatepsequ)) {
                $this->getTemplate()->VALOR_ITEM_TIPO = 'CADUM';
                $this->getTemplate()->VALOR_ITEM_CODIGO_REDUZIDO = $item->cmatepsequ;
                
                $seqItem = $item->cmatepsequ;
                $this->getTemplate()->VALOR_ITEM_DESCRICAO = $item->ematepdesc;
                
                $valorTRP = calcularValorTrp(ClaDatabasePostgresql::getConexao(), 2, $item->cmatepsequ);
                $this->getTemplate()->VALOR_ITEM_UNITARIO_TRP = $valorTRP > 0 ? converte_valor_estoques($valorTRP) : '0,0000';
            }
            
            if (! is_null($item->cservpsequ)) {
                $TipoGrupo = 'S';
                $this->getTemplate()->VALOR_ITEM_TIPO = 'CADUS';
                $this->getTemplate()->VALOR_ITEM_CODIGO_REDUZIDO = $item->cservpsequ;
                
                $seqItem = $item->cservpsequ;
                $this->getTemplate()->VALOR_ITEM_DESCRICAO = $item->eservpdesc;
            }
            $this->getTemplate()->VALOR_TIPO_GRUPO = $TipoGrupo;
            $this->getTemplate()->VALOR_ITEM_CODIGO_MATERIAL_SERVICO = $seqItem;
            $this->getTemplate()->VALOR_ITEM_UNITARIO = converte_valor_estoques($item->vitirpvues);
            $this->setDescricaoDetalhadaByItem($item);
            $this->getTemplate()->VALOR_ITEM_QUANTIDADE_PREVISTA = converte_valor_estoques($item->airirpqtpr);
            $this->getTemplate()->block('BLOCO_TELA_RESPONDER_ITENS_ROW');
            
            $displayItem = true;
        }
        
        if ($displayItem) {
            $this->getTemplate()->block('BLOCO_TELA_RESPONDER_ITENS_HEADER');
        }
    }

    public function plotarDocumentos($Linha) {
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

    private function proccessMain() {
        $this->setupProccessMain();
        
        $getIntencao = $this->variables['get']['intencao'];
        $getOrgao    = (int) $this->variables['get']['orgao'];
        
        $this->getTemplate()->VALOR_RESPOSTA_NUMERO_INTENCAO = $getIntencao; // carregar os dados oriundo da tela que chamou
        
        $respostaOrgao = $this->getRespostaItencao($this->getTemplate()->VALOR_RESPOSTA_NUMERO_INTENCAO); // montar os dados da tela
        $this->getTemplate()->VALOR_ORGAO = $getOrgao;
        $this->setTemplateValuesIntencao($respostaOrgao);
        
        $itensList = $this->getItensRespostaByIntencaoAnoOrgao((int) $respostaOrgao->cintrpsequ, (int) $respostaOrgao->cintrpsano, $getOrgao); // get itens da resposta por intencao/ano/orgao

        $this->setTemplateValuesIntencaoItens($itensList);
        $this->plotarDocumentos($respostaOrgao);
    }

    private function proccessImprimir() {
        $dadosPost                           = explode('/', $this->variables['post']['intencao']);
        $numeroIntencao                      = new Negocio_ValorObjeto_IntencaoRegistroPreco($dadosPost[0], $dadosPost[1]);
        $chaveIntencao                       = array();
        $chaveIntencao['sequencialIntencao'] = $numeroIntencao->getCintrpsequ();
        $chaveIntencao['anoIntencao']        = $numeroIntencao->getCintrpsano();
        
        $pdf = new PdfPrecoIntencaoResponder();
        $pdf->setChaveIntencao($chaveIntencao);
        $pdf->setOrgaoLicitante($_REQUEST['orgao']);
        $pdf->setExibirUltimaResposta(true);
        $pdf->gerarRelatorio();
    }

    /**
     * [frontController description].
     * @return [type] [description]
     */
    protected function frontController() {
        $this->proccessMain();
    }

    /**
     * [__construct description].
     * @param ArrayObject $session
     *            [description]
     */
    public function __construct(array $variablesGlobals, $post) {
        if (!$post) {
            /*
            * Settings
            */
            $template = new TemplatePortal('templates/JanelaExibirResposta.html');
            $this->setTemplate($template);
            $this->variables = $variablesGlobals;
            /*
            * Front Controller for action
            */
            $this->frontController();
        } else {
            $this->variables = $variablesGlobals;
            $this->proccessImprimir();
        }
    }

    /**
     * @return array
     */
    protected static function filterSanitizePOST() {
        return array(
            'intencao' => FILTER_SANITIZE_STRING,
            'orgao' => FILTER_SANITIZE_STRING
        );
    }

    /**
     * @return array
     */
    protected static function filterSanitizeGET() {
        return array(
            'intencao' => FILTER_SANITIZE_STRING,
            'orgao' => FILTER_SANITIZE_STRING
        );
    }

    /**
     * Bootstrap application.
     */
    public static function bootstrap() {
        $arrayGlobals = parent::setup();
        $post         = false;
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'POST') {
            $arrayGlobals['post'] = filter_input_array(INPUT_POST, self::filterSanitizePOST());
            $post = true;
        }
        
        if ($arrayGlobals['server']['REQUEST_METHOD'] == 'GET') {
            $arrayGlobals['get'] = filter_input_array(INPUT_GET, self::filterSanitizeGET());
        }
        $app = new JanelaExibirResposta($arrayGlobals, $post);
        echo $app->run();
        unset($app, $arrayGlobals);
    }
}
/*
 * DO REMOVE IT'S BLOCK
 */
JanelaExibirResposta::bootstrap();