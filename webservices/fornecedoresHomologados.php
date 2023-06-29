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
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    GIT: v1.39.0
 */
if (! @require_once dirname(__FILE__) . '/../funcoes.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

/**
 * Pitang Webservice REST
 */
class Pitang_Webservices_Fornecedores_Homologados
{

    /**
     *
     * @var string
     */
    const TOKEN = '4f82294dd18d0273b1efebcdf75c1343';

    /**
     * Pesquisar pelo o código do item (material ou serviço)
     *
     * @var integer
     */
    private $codigo;

    /**
     * Limit da consulta SQL
     *
     * @var integer
     */
    private $limit;

    /**
     * Offset da consulta SQL
     *
     * @var integer
     */
    private $offset;

    /**
     * Setar o offset da consulta SQL
     *
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $limitValidado = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($limitValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de limit informado não é um valor de inteiro", 1);
        }

        $this->limit = $limitValidado;

        return $this;
    }

    /**
     * Pegar o valor de offset da consulta SQL
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     *
     * @param integer $offset
     */
    public function setOffSet($offset)
    {
        $offsetValidado = filter_var($offset, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($offsetValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de offset informado não é um valor de inteiro", 1);
        }

        $this->offset = $offsetValidado;

        return $this;
    }

    /**
     */
    public function getOffSet()
    {
        return $this->offset;
    }

    /**
     *
     * @param integer $codigo
     */
    public function setCodigo($codigo)
    {
        $codigoValidado = filter_var($codigo, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($codigoValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor do código informado não é um valor de inteiro", 1);
        }
        $this->codigo = $codigoValidado;

        return $this;
    }

    /**
     * Retorna o valor do codigo ou serviço(cadum e cadus)
     *
     * @return integer
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     *
     * @param
     *            [type]
     */
    public function __construct()
    {
        $this->limit = 25;
        $this->offset = 0;
    }

    /**
     * Pesquisa no banco de dados o valor de acordo com o parâmetro informado
     * material (inteiro) ou material (inteiro)
     *
     * @return mixed - a new DB_result object for queries that return results
     *         (such as SELECT queries), DB_OK for queries that manipulate data
     *         (such as INSERT queries) or a DB_Error object on failure
     */
    public function consultarMaterial()
    {
        if (! isset($this->codigo)) {
            return;
        }

        // $sql = "
        //   SELECT A.CMATEPSEQU,A.CALMPOCODI,D.EALMPODESC,  A.VITENFUNIT, C. NFORCRFANT,C.NFORCRRAZS,
        //          C.AFORCRCCGC, C.AFORCRCCPF, C.AFORCRCDDD, C.AFORCRTELS, C.NFORCRMAIL, A.TITENFULAT
        //     FROM SFPC.TBITEMNOTAFISCAL A,
        //          SFPC.TBENTRADANOTAFISCAL B,
        //          SFPC.TBFORNECEDORCREDENCIADO C,
        //          SFPC.TBALMOXARIFADOPORTAL D
        //    WHERE A.CMATEPSEQU = " . $this->codigo . "
        //          AND A.CENTNFCODI = B.CENTNFCODI
        //          AND A.CALMPOCODI = B.CALMPOCODI
        //          AND A.AENTNFANOE = B.AENTNFANOE
        //          AND B.AENTNFANOE >= " . date('Y') . "
        //          AND B.AFORCRSEQU = C.AFORCRSEQU
        //          AND A.CALMPOCODI = D.CALMPOCODI
        // ";
        //
        $sql = "
        SELECT A.CMATEPSEQU,A.CALMPOCODI,D.EALMPODESC,  A.VITENFUNIT, C.AFORCRSEQU , C. NFORCRFANT,C.NFORCRRAZS,  C.AFORCRCCGC, C.AFORCRCCPF, C.AFORCRCDDD, C.AFORCRTELS, C.NFORCRMAIL, C.FFORCRMEPP , A.TITENFULAT , F.EUNIDMSIGL
        FROM   SFPC.TBITEMNOTAFISCAL A, SFPC.TBENTRADANOTAFISCAL B, SFPC.TBFORNECEDORCREDENCIADO C,      SFPC.TBALMOXARIFADOPORTAL D, SFPC.TBMATERIALPORTAL E, SFPC.TBUNIDADEDEMEDIDA F
        WHERE  A.CMATEPSEQU = " . $this->codigo . "
        AND    A.CENTNFCODI = B.CENTNFCODI
        AND    A.CALMPOCODI = B.CALMPOCODI
        AND    A.AENTNFANOE = B.AENTNFANOE
        AND    B.AENTNFANOE >= 2014
        AND    B.AFORCRSEQU = C.AFORCRSEQU
        AND    A.CALMPOCODI = D.CALMPOCODI
        AND    A.CMATEPSEQU = E.CMATEPSEQU
        AND    E.CUNIDMCODI = F.CUNIDMCODI
        ";
        $rs = executarSQL(Conexao(), $sql);
        $lista = array();
        while ($row = $rs->fetchRow()) {
            $lista[] = $row;
        }

        return $lista;
    }

    /**
     * Pesquisa no banco de dados o valor de acordo com o parâmetro informado
     * codigo (inteiro) ou descricao (string)
     *
     * @return mixed - a new DB_result object for queries that return results
     *         (such as SELECT queries), DB_OK for queries that manipulate data
     *         (such as INSERT queries) or a DB_Error object on failure
     */
    public function consultarServico()
    {
        if (! isset($this->codigo)) {
            return;
        }

        $sql = "
            SELECT A.CSERVPSEQU, A.VITELPVLOG, B. NFORCRFANT, B.NFORCRRAZS,
                   B.AFORCRCCGC, B.AFORCRCCPF, B.AFORCRCDDD, B.AFORCRTELS,
                   B.NFORCRMAIL, A.TITELPULAT
              FROM SFPC.TBITEMLICITACAOPORTAL A,
                   SFPC.TBFORNECEDORCREDENCIADO B
             WHERE A.CSERVPSEQU = " . $this->codigo . "
                   AND A.VITELPVLOG <> 0
                   AND A.ALICPOANOP >= " . date('Y') . "
                   AND A.AFORCRSEQU = B.AFORCRSEQU
        ";

        $rs = executarSQL(Conexao(), $sql);
        $lista = array();
        while ($row = $rs->fetchRow()) {
            $lista[] = $row;
        }

        return $lista;
    }
}

// Seguranca();

header('Access-Control-Allow-Orgin: *');
header('Access-Control-Allow-Methods: *');
header('Allow: POST, GET');
header('Content-Type: application/json; charset=utf-8');

$metodoHttp = $_SERVER['REQUEST_METHOD'];
$data = array();

switch ($metodoHttp) {
    case 'POST':
    case 'GET':
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        if (! isset($headers['apikey'])) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'API Key nao foi informado';
            echo json_encode($data);
            exit();
        }

        if ($headers['apikey'] != Pitang_Webservices_Fornecedores_Homologados::TOKEN) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'API Key nao e uma chave valida';
            echo json_encode($data);
            return false;
        }

        if (! isset($_REQUEST['codigo'])) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'Parametro codigo nao informado';
            echo json_encode($data);
            return false;
        }

        $webservice = new Pitang_Webservices_Fornecedores_Homologados();

        if (isset($_REQUEST['codigo'])) {
            $webservice->setCodigo($_REQUEST['codigo']);
        }

        if (isset($_REQUEST['limit'])) {
            $webservice->setLimit($_REQUEST['limit']);
        }

        if (isset($_REQUEST['offset'])) {
            $webservice->setOffSet($_REQUEST['offset']);
        }
        try {
            $data['material'] = $webservice->consultarMaterial();
            $data['servico'] = $webservice->consultarServico();
            echo json_encode($data);
        } catch (Exception $e) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= $e->getMessage();
            echo json_encode($data);
        }
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        $data['erro'] .= 'A requisicao deve ser do tipo POST';
        echo json_encode($data);
        break;
}

