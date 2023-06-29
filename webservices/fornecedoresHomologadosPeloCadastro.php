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
 * @version    GIT: v1.39.01
 */
if (! @require_once dirname(__FILE__) . '/../vendor/autoload.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

require_once ("../fornecedores/funcoesFornecedores.php");

/**
 * Pitang Webservice REST.
 */
class Pitang_Webservices_fornecedoresHomologadosPeloCadastro extends Pitang_Webservices
{

    /**
     * Pesquisar pelo o cadastro CPF ou CNPJ.
     *
     * @var int
     */
    private $cadastro;

    private $mensagem;

    /**
     */
    private function sqlConsultaSituacao()
    {
        $sql = '
            SELECT fc.*,
                   fs.*,
                   fts.*
              FROM sfpc.tbfornecedorcredenciado fc
                   INNER JOIN sfpc.tbfornsituacao fs
                           ON fs.aforcrsequ = fc.aforcrsequ
                   INNER JOIN sfpc.tbfornecedortiposituacao fts
                           ON fts.cfortscodi = fs.cfortscodi
             WHERE 1 = 1
        ';
        
        $tamanhoDigito = strlen($this->getCadastro());
        if ($tamanhoDigito == 11) {
            $sql .= " AND fc.aforcrccpf LIKE '%s' ";
        } else {
            $sql .= " AND fc.aforcrccgc LIKE '%s' ";
        }
        $sql = sprintf($sql, $this->getCadastro());
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    private function consultarContribuinteOracle()
    {
        $sql = '
                SELECT *
                  FROM SFCI.TBCONTRIBUINTE
                  WHERE ACONTBDOCU = %s
            ';
        
        $contribuinte = ClaDatabaseOracle::executarSQL(sprintf($sql, $this->getCadastro()));
        ClaDatabaseOracle::hasError($contribuinte);
        
        return $contribuinte;
    }

    /**
     *
     * @param unknown $contribuinte            
     */
    private function consultarMercantil($contribuinte)
    {
        $sql = '
                SELECT AMERCTINSC,
                       CSITUMCODI
                  FROM SFCM.TBMERCANTIL
                 WHERE ACONTBSEQU = %d
            ';
        $contribuinte = current($contribuinte);
        $mercantil = ClaDatabaseOracle::executarSQL(sprintf($sql, $contribuinte->ACONTBSEQU));
        ClaDatabaseOracle::hasError($mercantil);
        
        return $mercantil;
    }

    /**
     *
     * @param array $colecaoFornecedor            
     */
    private function consultarMensagem($colecaoFornecedor)
    {
        $fornecedor = current($colecaoFornecedor);
        
        $sql = "SELECT DFORCHGERA, DFORCHVALI FROM SFPC.TBFORNECEDORCHF WHERE AFORCRSEQU = %d";
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $fornecedor->aforcrsequ));
        ClaDatabasePostgresql::hasError($res);
        
        $res = current($res);
        
        if (DataInvertida($res->dforchvali) < date("Y-m-d")) {
            $this->mensagem .= " CHF fora do prazo de validade.";
        }
        
        $sql = "
            SELECT dforcrultb,
                   dforcrcnfc
			  FROM SFPC.TBFORNECEDORCREDENCIADO FORN
			       LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM
                                ON FORN.CCOMLICODI = COM.CCOMLICODI
             WHERE AFORCRSEQU = %d";
        
        $res = ClaDatabasePostgresql::executarSQL(sprintf($sql, $fornecedor->aforcrsequ));
        ClaDatabasePostgresql::hasError($res);
        
        $res = current($res);
        // Verifica também se a data de balanço anual está no prazo #
        
        if ($res->dforcrultb < prazoUltimoBalanço()->format('Y-m-d')) {
            $this->mensagem .= " Data de Validade do Balanço expirada.";
        }
        
        $data_menos_prazo = prazoCertidaoNegDeFalencia()->format('Y-m-d');
        if ($res->dforcrcnfc < $data_menos_prazo) {
            $this->mensagem .= " Data de Certidão Negativa expirada.";
        }
        
        return $this->getMensagem();
    }

    /**
     */
    private function consultarSituacao($colecaoFornecedor)
    {
        $fornecedor = current($colecaoFornecedor);
        $sql = '
            SELECT A.CTIPCECODI,
                   A.ETIPCEDESC,
                   B.DFORCEVALI
             FROM SFPC.TBTIPOCERTIDAO A,
                  SFPC.TBFORNECEDORCERTIDAO B
            WHERE A.CTIPCECODI = B.CTIPCECODI
                  AND B.AFORCRSEQU = ' . $fornecedor->aforcrsequ . '
                  ORDER BY B.DFORCEVALI desc
        ';
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        $mensagem = 'HABILITADO';
        if ($fornecedor->fforcrtipo == 'L') {
            foreach ($res as $linha) {
                if ($linha->dforcevali < date('Y-m-d')) {
                    $mensagem = 'INABILITADO';
                }
            }
        }
        
        if ($mensagem == 'INABILITADO') {
            $this->mensagem = 'Fornecedor inabilitado Certidão(ões) fora do prazo de validade.';
        }
        
        return $fornecedor->efortsdesc . ' ' . $mensagem;
    }

    /**
     *
     * @param int $cadastro            
     */
    public function setCadastro($cadastro)
    {
        $cadastroValidado = filter_var($cadastro, FILTER_SANITIZE_NUMBER_INT);
        $this->cadastro = $cadastroValidado;
        
        return $this;
    }

    /**
     */
    public function getCadastro()
    {
        return $this->cadastro;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Pitang_Webservices::frontController()
     */
    public function frontController()
    {
        $data = null;
        if (isset($_REQUEST['cadastro'])) {
            $this->setCadastro($_REQUEST['cadastro']);
        }
        
        if (isset($_REQUEST['limit'])) {
            $this->setLimit($_REQUEST['limit']);
        }
        
        if (isset($_REQUEST['offset'])) {
            $this->setOffSet($_REQUEST['offset']);
        }
        try {
            $colecaoFornecedor = $this->sqlConsultaSituacao();
            if (count($colecaoFornecedor) == 0) {
                $data['situacaoCadastro'] = 'Cadastro informado não existe ou é inválido';
            } else {
                $data['situacaoCadastro'] = $this->consultarSituacao($colecaoFornecedor);
                $data['mensagemCadastro'] = $this->consultarMensagem($colecaoFornecedor);
            }
            echo json_encode($data);
        } catch (Exception $e) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= $e->getMessage();
            echo json_encode($data);
        }
    }

    /**
     *
     * @return the unknown_type
     */
    public function getMensagem()
    {
        return $this->mensagem;
    }

    /**
     *
     * @param unknown_type $mensagem            
     */
    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
        return $this;
    }
}

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
        
        if ($headers['apikey'] != Pitang_Webservices_fornecedoresHomologadosPeloCadastro::TOKEN) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'API Key não é uma chave valida';
            echo json_encode($data);
            
            return false;
        }
        
        if (! isset($_REQUEST['cadastro'])) {
            header('HTTP/1.1 400 Bad Request');
            $data['erro'] .= 'Parametro cadastro nao informado';
            echo json_encode($data);
            
            return false;
        }
        
        $webservice = new Pitang_Webservices_fornecedoresHomologadosPeloCadastro();
        $webservice->run();
        break;
    default:
        header('HTTP/1.1 405 Method Not Allowed');
        $data['erro'] .= 'A requisição deve ser do tipo POST';
        echo json_encode($data);
        break;
}
