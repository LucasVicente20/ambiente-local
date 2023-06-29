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
 * @version    GIT: v1.30.6
 */
if (! @require_once dirname(__FILE__) . '/../vendor/autoload.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

/**
 * Pitang Webservice REST
 */
class Pitang_Webservices_Item
{

    /**
     *
     * @var string
     */
    const TOKEN = '4f82294dd18d0273b1efebcdf75c1343';

    /**
     * Pesquisar pelo o código do item (material/servico)
     *
     * @var integer
     */
    private $codigo;

    /**
     * Pesquisar pela a descrição do item (material/servico)
     *
     * @var string
     */
    private $descricao;

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
     * @param string $descricao            
     */
    public function setDescricao($descricao)
    {
        if (filter_var($descricao, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de descricao informado não é uma string", 1);
        }
        $descricaoValidada = filter_var($descricao);
        $this->descricao = strtoupper2($descricaoValidada);
        
        return $this;
    }

    /**
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     *
     * @param integer $codigo            
     */
    public function setCodigo($codigo)
    {
        $codigoValidado = filter_var($codigo, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($codigoValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de codigo informado não é um valor de inteiro", 1);
        }
        $this->codigo = $codigoValidado;
        
        return $this;
    }

    /**
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
     * codigo (inteiro) ou descricao (string)
     *
     * @return mixed - a new DB_result object for queries that return results
     *         (such as SELECT queries), DB_OK for queries that manipulate data
     *         (such as INSERT queries) or a DB_Error object on failure
     */
    public function consultarMaterial()
    {
        $sql = '
            SELECT
                material.cmatepsequ AS CADUM,
                material.ematepdesc AS descricao
            FROM sfpc.tbmaterialportal material
        ';
        
        $sql .= "WHERE material.cmatepsitu = 'A' ";
        
        if (isset($this->descricao)) {
            $sql .= "
                AND material.ematepdesc LIKE '" . $this->descricao . "%' ";
        } elseif (isset($this->codigo)) {
            $sql .= '
                AND material.cmatepsequ = ' . $this->codigo . ' ';
        }
        
        $sql .= '
            ORDER BY material.ematepdesc LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
        // echo $sql;
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
        $sql = '
            SELECT
                servico.cservpsequ AS CADUS,
                servico.eservpdesc AS descricao
            FROM sfpc.tbservicoportal servico ';
        
        $sql .= "WHERE servico.cservpsitu = 'A' ";
        if (isset($this->descricao)) {
            $sql .= "
            AND servico.eservpdesc LIKE '" . $this->descricao . "%' ";
        } elseif (isset($this->codigo)) {
            $sql .= '
            AND servico.cservpsequ = ' . $this->codigo . ' ';
        }
        
        $sql .= '
            ORDER BY servico.eservpdesc LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
        // echo $sql;
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
            $data['erro'] .= 'API Key não foi informado';
            echo json_encode($data);
            exit();
        }
        
        if ($headers['apikey'] != Pitang_Webservices_Item::TOKEN) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'API Key não é uma chave valida';
            echo json_encode($data);
            return false;
        }
        
        if (! isset($_REQUEST['descricao']) && ! isset($_REQUEST['codigo'])) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'Parametros descricao ou codigo nao informado';
            echo json_encode($data);
            return false;
        }
        
        if (isset($_REQUEST['descricao']) && isset($_REQUEST['codigo'])) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'Parametros descricao ou codigo deve ser informado um por vez';
            echo json_encode($data);
            return false;
        }
        
        $webservice = new Pitang_Webservices_Item();
        
        if (isset($_REQUEST['descricao'])) {
            $webservice->setDescricao($_REQUEST['descricao']);
        }
        
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
            $data['headers'] = array(
                "Codigo" => "inteiro 4 digitos - Codigo CADUM ou CADUS",
                "Descricao" => "varchar até 255 caracteres - Descricao do Material ou Servico"
            );
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
        $data['erro'] .= 'A requisição deve ser do tipo POST';
        echo json_encode($data);
        break;
}

