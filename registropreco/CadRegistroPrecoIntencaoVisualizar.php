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

class CadRegistroPrecoIntencaoVisualizar extends ClaRegistroPrecoIntencao
{

    /**
     */
    private function setupProccessMain()
    {
        if (! isset($this->variables['get']['numero'])) {
            throw new InvalidArgumentException('Intencao not found');
        }
    }

    protected function sqlConsultarIntencao($numero, $ano)
    {
        $sql = '
        SELECT 
            i.tintrpdcad,
            i.tintrpdlim,
            i.xintrpobje,
            i.xintrpobse
        FROM sfpc.tbintencaoregistropreco i
        WHERE i.cintrpsano = '. $ano . '
        AND i.cintrpsequ = ' . $numero;

        return $sql;
    }

    protected function sqlConsultarOrgaosIntencao($numero, $ano)
    {
        $sql = '
        SELECT 
            o.eorglidesc
        FROM sfpc.tbintencaorporgao i
            INNER JOIN sfpc.tborgaolicitante o ON i.corglicodi = o.corglicodi
        WHERE i.cintrpsequ = '. $numero . '
        AND i.cintrpsano = ' . $ano;

        return $sql;
    }

    protected function sqlConsultarItensIntencao($numero, $ano)
    {
        $sql = '
        SELECT i.cintrpsequ, i.cintrpsano, i.citirpsequ,
               i.cmatepsequ, m.ematepdesc, i.eitirpdescmat,
               s.eservpdesc, i.cservpsequ, i.eitirpdescse,
               i.aitirporde, i.vitirpvues, m.fmatepgene
        FROM sfpc.tbitemintencaoregistropreco i
               full outer JOIN sfpc.tbmaterialportal m ON i.cmatepsequ = m.cmatepsequ
               full outer JOIN sfpc.tbservicoportal s ON i.cservpsequ = s.cservpsequ
        WHERE i.cintrpsequ = ' . $numero . '
               AND i.cintrpsano = ' . $ano . '
        ORDER BY i.aitirporde';

        return $sql;
    }

    protected function getIntencaoPorNumeroAno($intencao)
    {
        $numero = $intencao[0];
        $ano = $intencao[1];

        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlConsultarIntencao($numero, $ano);
        $resultSet = executarSQL($database, $sql);
        $intencao = $resultSet->fetchRow(DB_FETCHMODE_OBJECT);

        return $intencao;
    }

    protected function getOrgaosIntencao($intencao)
    {
        $numero = $intencao[0];
        $ano = $intencao[1];

        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlConsultarOrgaosIntencao($numero, $ano);
        $resultSet = executarSQL($database, $sql);

        $orgaos = array();
        while ($resultSet->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            array_push($orgaos, $row->eorglidesc);
        }

        return $orgaos;
    }

    protected function getItens($intencao)
    {
        $numero = $intencao[0];
        $ano = $intencao[1];

        $database = ClaDatabasePostgresql::getConexao();
        $sql = $this->sqlConsultarItensIntencao($numero, $ano);
        $resultSet = executarSQL($database, $sql);

        $itens = array();
        while ($resultSet->fetchInto($row, DB_FETCHMODE_OBJECT)) {
            array_push($itens, $row);
        }

        return $itens;
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
     */
    private function proccessMain()
    {
        $this->setupProccessMain();
        
        $intencao = explode('/', $this->variables['get']['numero']);
        $dados = $this->getIntencaoPorNumeroAno($intencao);

        $this->getTemplate()->VALOR_NUMERO_INTENCAO =  $_GET['numero'];
        $this->getTemplate()->VALOR_DATA_CADASTRAMENTO_INTENCAO =  ClaHelper::converterDataBancoParaBr($dados->tintrpdcad);
        $this->getTemplate()->VALOR_DATA_LIMITE_INTENCAO =  ClaHelper::converterDataBancoParaBr($dados->tintrpdlim);
        $this->getTemplate()->VALOR_OBJETO_INTENCAO = $dados->xintrpobje;
        $this->getTemplate()->VALOR_OBSERVACAO_INTENCAO = $dados->xintrpobse;

        $this->plotarBlocoOrgaos($intencao);
        $this->plotarBlocoItens($intencao);
        $this->plotarDocumentos($intencao);
    }

    private function plotarBlocoOrgaos($intencao)
    {
        $orgaos = $this->getOrgaosIntencao($intencao);

        $descOrgaos = "";
        foreach($orgaos as $orgao){
            $descOrgaos .= $orgao;

            if ($orgao != end($orgaos)){
                $descOrgaos .= "<br />";
            }
        }

        $this->getTemplate()->ORGAOS = $descOrgaos;
    }

    private function plotarBlocoItens($intencao)
    {
        $itens = $this->getItens($intencao);

        foreach($itens as $item) {
            $this->getTemplate()->VALOR_ITEM = $item->aitirporde;
            $this->getTemplate()->VALOR_UNITARIO_ESTIMADO = converte_valor_estoques($item->vitirpvues);
            $this->getTemplate()->VALOR_TIPO = 'CADUM';
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $item->cmatepsequ;
            $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($item->ematepdesc);

            if (isset($item->cservpsequ)) {
                $this->getTemplate()->VALOR_TIPO = 'CADUS';
                $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $item->cservpsequ;
                $this->getTemplate()->VALOR_DESCRICAO = strtoupper2($item->eservpdesc);
                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($item->eitirpdescse);
                $this->getTemplate()->VALOR_ESTIMADO_TRP = "---";
            } else {
                if ($item->fmatepgene == 'S') {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = strtoupper2($item->eitirpdescmat);
                } else {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = '---';
                }

                
                $valorTRP = calcularValorTrp(Conexao(), 2, $this->getTemplate()->VALOR_CODIGO_REDUZIDO);
                $this->getTemplate()->VALOR_ESTIMADO_TRP = converte_valor_estoques($valorTRP);
            }
            $this->getTemplate()->block("BLOCO_LISTAGEM_ITEM");
        }
    }

    public function plotarDocumentos($Linha) {
        // Documentos
        $documentos = $this->documentosIntencao($Linha[0], $Linha[1]);
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

    private function proccessImprimir()
    {
        $dadosPost = explode('/', $this->variables['post']['numero']);

        $intencao['sequencialIntencao'] = $dadosPost[0];
        $intencao['anoIntencao'] = $dadosPost[1];
        
        $pdf = new PdfPrecoIntencaoVisualizar();
        $pdf->setChaveIntencao($intencao);
        $pdf->gerarRelatorio();
    }

    /**
     * [frontController description].
     *
     * @return [type] [description]
     */
    protected function frontController()
    {
        if(isset($this->variables['get'])){
            $this->proccessMain();
        } else if (isset($this->variables['post'])){
            $this->proccessImprimir();
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
        $template = new TemplatePortal('templates/CadRegistroPrecoIntencaoVisualizar.html');
        
        $this->setTemplate($template);
        $this->variables = $variablesGlobals;
        /*
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
            'numero' => FILTER_SANITIZE_STRING,
        );
    }

    /**
     *
     * @return array
     */
    protected static function filterSanitizeGET()
    {
        return array(
            'numero' => FILTER_SANITIZE_STRING,
        );
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
        $app = new CadRegistroPrecoIntencaoVisualizar($arrayGlobals);
        echo $app->run();
        
        unset($app, $arrayGlobals);
    }
}
/*
 * DO REMOVE IT'S BLOCK
 */
CadRegistroPrecoIntencaoVisualizar::bootstrap();
